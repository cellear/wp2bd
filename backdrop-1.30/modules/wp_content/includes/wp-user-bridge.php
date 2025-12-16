<?php
/**
 * WordPress User Bridge for WP4BD V2
 *
 * Provides conversion between Backdrop users and WordPress user objects.
 *
 * @package WP4BD
 * @subpackage V2-Architecture
 * @since WP4BD-V2-061
 */

/**
 * Convert a Backdrop user account to a WordPress user object.
 *
 * @param object $account Backdrop user account object
 * @return object|null WordPress-style user object or null on failure
 */
function wp4bd_backdrop_user_to_wp_user($account) {
  if (!is_object($account) || !isset($account->uid)) {
    return null;
  }

  // Create WordPress-style user object
  $wp_user = new stdClass();

  // Basic user properties
  $wp_user->ID = (int) $account->uid;
  $wp_user->user_login = isset($account->name) ? $account->name : '';
  $wp_user->user_email = isset($account->mail) ? $account->mail : '';
  $wp_user->user_registered = isset($account->created) ? date('Y-m-d H:i:s', $account->created) : '0000-00-00 00:00:00';

  // Display name - try various Backdrop fields
  if (isset($account->field_display_name) && is_array($account->field_display_name)) {
    $lang_key = defined('LANGUAGE_NONE') ? LANGUAGE_NONE : 'und';
    if (isset($account->field_display_name[$lang_key][0]['value'])) {
      $wp_user->display_name = $account->field_display_name[$lang_key][0]['value'];
    }
  }

  // Fallback to name if no display name
  if (empty($wp_user->display_name)) {
    $wp_user->display_name = $wp_user->user_login;
  }

  // User nicename (URL-friendly version)
  $wp_user->user_nicename = _wp4bd_sanitize_username($wp_user->display_name);

  // User status
  $wp_user->user_status = isset($account->status) ? (int) $account->status : 0;

  // Roles and capabilities - Backdrop uses different role system
  $wp_user->roles = _wp4bd_get_user_roles($account);
  $wp_user->caps = array();
  foreach ($wp_user->roles as $role) {
    $wp_user->caps[$role] = true;
  }

  // Additional WordPress user fields
  $wp_user->user_pass = ''; // Never expose password
  $wp_user->user_activation_key = '';
  $wp_user->user_url = '';

  // First and last name if available
  $wp_user->first_name = '';
  $wp_user->last_name = '';

  // Try to extract from name field if it's formatted as "First Last"
  if (!empty($wp_user->display_name) && strpos($wp_user->display_name, ' ') !== false) {
    $name_parts = explode(' ', $wp_user->display_name, 2);
    $wp_user->first_name = $name_parts[0];
    $wp_user->last_name = $name_parts[1];
  }

  // Description/bio
  $wp_user->description = '';
  if (isset($account->field_bio) && is_array($account->field_bio)) {
    $lang_key = defined('LANGUAGE_NONE') ? LANGUAGE_NONE : 'und';
    if (isset($account->field_bio[$lang_key][0]['value'])) {
      $wp_user->description = $account->field_bio[$lang_key][0]['value'];
    }
  }

  return $wp_user;
}

/**
 * Get user roles from Backdrop account.
 *
 * @param object $account
 * @return array Array of role names
 */
function _wp4bd_get_user_roles($account) {
  $roles = array('subscriber'); // Default role

  // Check if user has administrator role
  if (isset($account->roles) && is_array($account->roles)) {
    // Backdrop roles are stored as rid => role_name
    if (in_array('administrator', $account->roles) || isset($account->roles[3])) { // 3 is typically admin
      $roles = array('administrator');
    } elseif (in_array('editor', $account->roles) || isset($account->roles[4])) { // 4 is typically editor
      $roles = array('editor');
    } elseif (in_array('author', $account->roles) || isset($account->roles[5])) { // 5 is typically author
      $roles = array('author');
    }
  } elseif (isset($account->uid) && $account->uid == 1) {
    // User 1 is always administrator
    $roles = array('administrator');
  }

  return $roles;
}

/**
 * Sanitize username for nicename.
 *
 * @param string $username
 * @return string
 */
function _wp4bd_sanitize_username($username) {
  // Convert to lowercase
  $nicename = strtolower($username);

  // Replace spaces and special chars with hyphens
  $nicename = preg_replace('/[^a-z0-9]+/', '-', $nicename);

  // Remove leading/trailing hyphens
  $nicename = trim($nicename, '-');

  return $nicename;
}

/**
 * Convert multiple Backdrop users to WordPress user objects.
 *
 * @param array $accounts Array of Backdrop user account objects
 * @return array Array of WordPress user objects
 */
function wp4bd_backdrop_users_to_wp_users($accounts) {
  $wp_users = array();

  if (!is_array($accounts)) {
    return $wp_users;
  }

  foreach ($accounts as $account) {
    $wp_user = wp4bd_backdrop_user_to_wp_user($account);
    if ($wp_user) {
      $wp_users[] = $wp_user;
    }
  }

  return $wp_users;
}
