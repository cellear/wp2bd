<?php
/**
 * WordPress Options/Settings Bridge for WP4BD V2
 *
 * Provides mapping between Backdrop configuration and WordPress options.
 *
 * @package WP4BD
 * @subpackage V2-Architecture
 * @since WP4BD-V2-063
 */

/**
 * Get a WordPress option value, mapped from Backdrop configuration.
 *
 * @param string $option_name WordPress option name
 * @return mixed Option value or false if not found
 */
function wp4bd_get_option($option_name) {
  // Map common WordPress options to Backdrop equivalents
  $option_map = array(
    // Site information
    'siteurl' => '_wp4bd_get_site_url',
    'home' => '_wp4bd_get_home_url',
    'blogname' => '_wp4bd_get_site_name',
    'blogdescription' => '_wp4bd_get_site_slogan',

    // Admin settings
    'admin_email' => '_wp4bd_get_admin_email',

    // Date/time settings
    'timezone_string' => '_wp4bd_get_timezone',
    'date_format' => '_wp4bd_get_date_format',
    'time_format' => '_wp4bd_get_time_format',

    // Post settings
    'posts_per_page' => '_wp4bd_get_posts_per_page',

    // Comment settings
    'default_comment_status' => '_wp4bd_get_default_comment_status',
    'comment_registration' => '_wp4bd_get_comment_registration',

    // Theme settings
    'template' => '_wp4bd_get_template',
    'stylesheet' => '_wp4bd_get_stylesheet',

    // Upload settings
    'upload_path' => '_wp4bd_get_upload_path',
    'upload_url_path' => '_wp4bd_get_upload_url_path',
  );

  // Check if we have a direct mapping
  if (isset($option_map[$option_name])) {
    $function_name = $option_map[$option_name];
    if (function_exists($function_name)) {
      return call_user_func($function_name);
    }
  }

  // Check for custom options stored in Backdrop variables
  $backdrop_value = _wp4bd_get_backdrop_variable($option_name);
  if ($backdrop_value !== null) {
    return $backdrop_value;
  }

  // Return false for unknown options (WordPress default)
  return false;
}

/**
 * Get site URL.
 */
function _wp4bd_get_site_url() {
  if (function_exists('url')) {
    return url('', array('absolute' => TRUE));
  }
  $base_url = isset($GLOBALS['base_url']) ? $GLOBALS['base_url'] : 'http://example.com';
  return $base_url;
}

/**
 * Get home URL (same as site URL in most cases).
 */
function _wp4bd_get_home_url() {
  return _wp4bd_get_site_url();
}

/**
 * Get site name from Backdrop config.
 */
function _wp4bd_get_site_name() {
  if (function_exists('config_get')) {
    $config = config('system.core');
    return $config->get('site_name') ?: 'Backdrop Site';
  }
  return 'Backdrop Site';
}

/**
 * Get site slogan/description.
 */
function _wp4bd_get_site_slogan() {
  if (function_exists('config_get')) {
    $config = config('system.core');
    return $config->get('site_slogan') ?: '';
  }
  return '';
}

/**
 * Get admin email.
 */
function _wp4bd_get_admin_email() {
  if (function_exists('config_get')) {
    $config = config('system.core');
    return $config->get('site_mail') ?: 'admin@example.com';
  }
  return 'admin@example.com';
}

/**
 * Get timezone.
 */
function _wp4bd_get_timezone() {
  if (function_exists('config_get')) {
    $config = config('system.core');
    $timezone = $config->get('date_default_timezone');
    if ($timezone) {
      return $timezone;
    }
  }
  return 'UTC';
}

/**
 * Get date format.
 */
function _wp4bd_get_date_format() {
  // Backdrop uses different date formats, default to WordPress default
  return 'F j, Y';
}

/**
 * Get time format.
 */
function _wp4bd_get_time_format() {
  // Default to WordPress format
  return 'g:i a';
}

/**
 * Get posts per page.
 */
function _wp4bd_get_posts_per_page() {
  if (function_exists('config_get')) {
    $config = config('system.core');
    return $config->get('default_nodes_main') ?: 10;
  }
  return 10;
}

/**
 * Get default comment status.
 */
function _wp4bd_get_default_comment_status() {
  // Default to open comments
  return 'open';
}

/**
 * Get comment registration setting.
 */
function _wp4bd_get_comment_registration() {
  // Default to no registration required
  return 0;
}

/**
 * Get current template name.
 */
function _wp4bd_get_template() {
  // This would be the active WordPress theme, but for now return a default
  return 'twentysixteen';
}

/**
 * Get current stylesheet name (same as template for most themes).
 */
function _wp4bd_get_stylesheet() {
  return _wp4bd_get_template();
}

/**
 * Get upload path.
 */
function _wp4bd_get_upload_path() {
  // Map to Backdrop's files directory
  return 'files';
}

/**
 * Get upload URL path.
 */
function _wp4bd_get_upload_url_path() {
  $base_url = _wp4bd_get_site_url();
  return $base_url . '/files';
}

/**
 * Get a Backdrop variable that might correspond to a WordPress option.
 *
 * @param string $option_name
 * @return mixed|null
 */
function _wp4bd_get_backdrop_variable($option_name) {
  // Check if it's stored as a Backdrop variable
  if (function_exists('variable_get')) {
    return variable_get('wp4bd_option_' . $option_name, null);
  }

  // Check in config
  if (function_exists('config_get')) {
    $config = config('wp_content.settings');
    return $config->get('wp_option_' . $option_name, null);
  }

  return null;
}

/**
 * Set a WordPress option (store in Backdrop).
 *
 * @param string $option_name
 * @param mixed $value
 * @return bool
 */
function wp4bd_update_option($option_name, $value) {
  if (function_exists('variable_set')) {
    variable_set('wp4bd_option_' . $option_name, $value);
    return true;
  }

  if (function_exists('config_get')) {
    $config = config('wp_content.settings');
    $config->set('wp_option_' . $option_name, $value);
    $config->save();
    return true;
  }

  return false;
}

/**
 * Delete a WordPress option.
 *
 * @param string $option_name
 * @return bool
 */
function wp4bd_delete_option($option_name) {
  if (function_exists('variable_del')) {
    variable_del('wp4bd_option_' . $option_name);
    return true;
  }

  if (function_exists('config_get')) {
    $config = config('wp_content.settings');
    $config->set('wp_option_' . $option_name, null);
    $config->save();
    return true;
  }

  return false;
}
