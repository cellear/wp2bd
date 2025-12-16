<?php
/**
 * @file
 * WordPress User Bridge for WP4BD V2
 *
 * Converts Backdrop user accounts to WordPress user object format.
 *
 * @see WP4BD-V2-061
 * @package WP4BD
 * @subpackage Data-Bridges
 */

/**
 * Convert a Backdrop user account to WordPress user data format.
 *
 * Creates a stdClass object with WordPress user properties populated
 * from Backdrop user data. This is used for author functions in themes.
 *
 * Note: This creates a data object, not a full WP_User instance with
 * capabilities. For most theme functions (get_the_author(), etc.) the
 * data object is sufficient.
 *
 * @param object $account
 *   A fully loaded Backdrop user account object.
 *
 * @return stdClass|null
 *   A WordPress-compatible user data object, or NULL if invalid.
 */
function wp4bd_user_to_wp_user($account) {
  // Validate input
  if (!is_object($account) || !isset($account->uid)) {
    return NULL;
  }

  // Create user data object
  $user = new stdClass();

  // ========================================================================
  // BASIC PROPERTIES
  // ========================================================================

  // User ID
  $user->ID = (int) $account->uid;

  // User login name
  $user->user_login = isset($account->name) ? $account->name : 'user_' . $user->ID;

  // User email
  $user->user_email = isset($account->mail) ? $account->mail : '';

  // User URL/website
  $user->user_url = isset($account->homepage) ? $account->homepage : '';

  // User registered date
  if (isset($account->created)) {
    $user->user_registered = date('Y-m-d H:i:s', $account->created);
  }
  else {
    $user->user_registered = '0000-00-00 00:00:00';
  }

  // User activation key (not used in Backdrop)
  $user->user_activation_key = '';

  // User status (0 = active, 1 = blocked in Backdrop)
  // WordPress doesn't use this the same way, so we map blocked users to status 1
  $user->user_status = isset($account->status) && $account->status == 0 ? 1 : 0;

  // ========================================================================
  // DISPLAY NAME AND NICENAME
  // ========================================================================

  // Display name - try to build from name fields or fall back to username
  $display_name = '';

  // Check if Backdrop has a "realname" module field
  if (isset($account->realname) && !empty($account->realname)) {
    $display_name = $account->realname;
  }
  // Or build from first/last name if available
  elseif (isset($account->field_first_name) || isset($account->field_last_name)) {
    $first = '';
    $last = '';

    if (isset($account->field_first_name)) {
      $lang_key = defined('LANGUAGE_NONE') ? LANGUAGE_NONE : 'und';
      if (isset($account->field_first_name[$lang_key][0]['value'])) {
        $first = $account->field_first_name[$lang_key][0]['value'];
      }
    }

    if (isset($account->field_last_name)) {
      $lang_key = defined('LANGUAGE_NONE') ? LANGUAGE_NONE : 'und';
      if (isset($account->field_last_name[$lang_key][0]['value'])) {
        $last = $account->field_last_name[$lang_key][0]['value'];
      }
    }

    $display_name = trim($first . ' ' . $last);
  }

  // Fall back to username
  if (empty($display_name)) {
    $display_name = $user->user_login;
  }

  $user->display_name = $display_name;

  // Nicename (URL-safe version of username)
  $user->user_nicename = wp4bd_sanitize_user_nicename($user->user_login);

  // Nickname (same as display name for simplicity)
  $user->nickname = $display_name;

  // ========================================================================
  // NAME FIELDS
  // ========================================================================

  // First and last name
  $user->first_name = '';
  $user->last_name = '';

  if (isset($account->field_first_name)) {
    $lang_key = defined('LANGUAGE_NONE') ? LANGUAGE_NONE : 'und';
    if (isset($account->field_first_name[$lang_key][0]['value'])) {
      $user->first_name = $account->field_first_name[$lang_key][0]['value'];
    }
  }

  if (isset($account->field_last_name)) {
    $lang_key = defined('LANGUAGE_NONE') ? LANGUAGE_NONE : 'und';
    if (isset($account->field_last_name[$lang_key][0]['value'])) {
      $user->last_name = $account->field_last_name[$lang_key][0]['value'];
    }
  }

  // ========================================================================
  // DESCRIPTION/BIO
  // ========================================================================

  // User description/bio
  $user->description = '';
  $user->user_description = '';

  // Check for common bio field names in Backdrop
  if (isset($account->field_bio)) {
    $lang_key = defined('LANGUAGE_NONE') ? LANGUAGE_NONE : 'und';
    if (isset($account->field_bio[$lang_key][0]['value'])) {
      $user->description = $account->field_bio[$lang_key][0]['value'];
      $user->user_description = $user->description;
    }
  }
  elseif (isset($account->field_about)) {
    $lang_key = defined('LANGUAGE_NONE') ? LANGUAGE_NONE : 'und';
    if (isset($account->field_about[$lang_key][0]['value'])) {
      $user->description = $account->field_about[$lang_key][0]['value'];
      $user->user_description = $user->description;
    }
  }

  // ========================================================================
  // WORDPRESS-SPECIFIC FIELDS (DEFAULTS)
  // ========================================================================

  // User level (deprecated in WordPress but some themes check it)
  // Map Backdrop roles to simple levels:
  // - anonymous: 0
  // - authenticated: 1
  // - editor/admin: 10
  $user->user_level = 1; // Default for authenticated users

  if (isset($account->roles) && is_array($account->roles)) {
    if (in_array('administrator', $account->roles)) {
      $user->user_level = 10;
    }
    elseif (in_array('editor', $account->roles)) {
      $user->user_level = 7;
    }
  }

  // Rich editing preference (default to enabled)
  $user->rich_editing = 'true';

  // Syntax highlighting preference (default to enabled)
  $user->syntax_highlighting = 'true';

  // User locale (language)
  $user->locale = '';

  // Spam status (not used in Backdrop)
  $user->spam = 0;

  // Deleted status (not used in Backdrop)
  $user->deleted = 0;

  return $user;
}

/**
 * Sanitize a username to create a URL-safe nicename.
 *
 * @param string $username
 *   The username to sanitize.
 *
 * @return string
 *   URL-safe nicename.
 */
function wp4bd_sanitize_user_nicename($username) {
  // Convert to lowercase
  $nicename = strtolower($username);

  // Replace spaces and special characters with hyphens
  $nicename = preg_replace('/[^a-z0-9]+/', '-', $nicename);

  // Remove leading/trailing hyphens
  $nicename = trim($nicename, '-');

  return $nicename;
}

/**
 * Convert multiple Backdrop users to WordPress user objects.
 *
 * @param array $accounts
 *   Array of Backdrop user account objects.
 *
 * @return array
 *   Array of WordPress user data objects.
 */
function wp4bd_users_to_wp_users(array $accounts) {
  $users = array();

  foreach ($accounts as $account) {
    $user = wp4bd_user_to_wp_user($account);
    if ($user) {
      $users[] = $user;
    }
  }

  return $users;
}
