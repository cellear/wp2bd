<?php
/**
 * @file
 * WordPress Options/Settings Bridge for WP4BD V2
 *
 * Epic 7 V2-063: Maps Backdrop configuration to WordPress options.
 *
 * This bridge enables WordPress themes and plugins to access Backdrop's
 * configuration through WordPress's standard get_option() function.
 */

/**
 * Get a WordPress option value from Backdrop configuration.
 *
 * Maps common WordPress option names to their Backdrop config equivalents.
 * This allows WordPress themes to call get_option('blogname') and receive
 * Backdrop's site_name value.
 *
 * @param string $option
 *   WordPress option name (e.g., 'blogname', 'blogdescription').
 * @param mixed $default
 *   Default value to return if option is not found.
 *
 * @return mixed
 *   The option value from Backdrop config, or $default if not found.
 */
function wp4bd_get_option($option, $default = FALSE) {
  // Map WordPress option names to Backdrop config paths
  $option_map = array(
    // Site identity
    'blogname' => array('config' => 'system.core', 'key' => 'site_name'),
    'blogdescription' => array('config' => 'system.core', 'key' => 'site_slogan'),
    'siteurl' => array('config' => 'system.core', 'key' => 'base_url'),
    'home' => array('config' => 'system.core', 'key' => 'base_url'),

    // Admin email
    'admin_email' => array('config' => 'system.core', 'key' => 'site_mail'),

    // Date and time
    'date_format' => array('config' => 'system.date', 'key' => 'date_format_short'),
    'time_format' => array('config' => 'system.date', 'key' => 'date_format_short'),

    // Default post/page settings
    'default_comment_status' => array('value' => 'open'), // Static value
    'default_ping_status' => array('value' => 'open'),
    'default_pingback_flag' => array('value' => 0),

    // Permalinks
    'permalink_structure' => array('value' => '/%year%/%monthnum%/%day%/%postname%/'),

    // Timezone
    'timezone_string' => array('config' => 'system.date', 'key' => 'default_timezone'),

    // Language
    'WPLANG' => array('config' => 'system.core', 'key' => 'language_default'),

    // Theme settings (will need to be dynamic based on active theme)
    'template' => array('config' => 'system.theme', 'key' => 'default'),
    'stylesheet' => array('config' => 'system.theme', 'key' => 'default'),

    // Posts per page
    'posts_per_page' => array('config' => 'system.core', 'key' => 'default_nodes_main'),

    // Site status
    'blog_public' => array('value' => 1), // Always public in our setup
  );

  // Check if we have a mapping for this option
  if (isset($option_map[$option])) {
    $mapping = $option_map[$option];

    // If it's a static value, return it
    if (isset($mapping['value'])) {
      return $mapping['value'];
    }

    // If it's a config mapping, get the value from Backdrop
    if (isset($mapping['config']) && isset($mapping['key'])) {
      $value = config_get($mapping['config'], $mapping['key']);

      // Return the value if found, otherwise return default
      if ($value !== NULL) {
        return $value;
      }
    }
  }

  // Special handling for common options that may not be in config
  switch ($option) {
    case 'blog_charset':
      return 'UTF-8';

    case 'gmt_offset':
      // Calculate GMT offset from timezone
      $timezone = config_get('system.date', 'default_timezone');
      if ($timezone) {
        try {
          $tz = new DateTimeZone($timezone);
          $dt = new DateTime('now', $tz);
          return $dt->getOffset() / 3600; // Convert seconds to hours
        }
        catch (Exception $e) {
          return 0;
        }
      }
      return 0;

    case 'page_on_front':
      // Get the front page node ID from Backdrop
      $frontpage = config_get('system.core', 'site_frontpage');
      if ($frontpage && $frontpage !== 'node') {
        // Extract node ID from path like "node/1"
        if (preg_match('/node\/(\d+)/', $frontpage, $matches)) {
          return (int) $matches[1];
        }
      }
      return 0;

    case 'show_on_front':
      // 'posts' or 'page'
      $frontpage = config_get('system.core', 'site_frontpage');
      return ($frontpage && $frontpage !== 'node') ? 'page' : 'posts';
  }

  // Option not found, return default
  return $default;
}

/**
 * Get multiple WordPress options at once.
 *
 * @param array $options
 *   Array of option names to retrieve.
 *
 * @return array
 *   Array of option values keyed by option name.
 */
function wp4bd_get_options($options) {
  $values = array();

  if (!is_array($options)) {
    return $values;
  }

  foreach ($options as $option) {
    $values[$option] = wp4bd_get_option($option);
  }

  return $values;
}

/**
 * Check if a WordPress option exists in Backdrop config.
 *
 * @param string $option
 *   WordPress option name.
 *
 * @return bool
 *   TRUE if option exists, FALSE otherwise.
 */
function wp4bd_has_option($option) {
  $value = wp4bd_get_option($option, '__NOT_FOUND__');
  return $value !== '__NOT_FOUND__';
}

/**
 * Get Backdrop config value using WordPress option syntax.
 *
 * This is a helper for custom mappings not in the standard option_map.
 *
 * @param string $config_file
 *   Backdrop config file name (e.g., 'system.core').
 * @param string $key
 *   Config key within the file.
 * @param mixed $default
 *   Default value if not found.
 *
 * @return mixed
 *   The config value or default.
 */
function wp4bd_config_to_option($config_file, $key, $default = FALSE) {
  $value = config_get($config_file, $key);
  return ($value !== NULL) ? $value : $default;
}

/**
 * Get theme modification (theme_mod) from Backdrop theme settings.
 *
 * WordPress themes use theme_mod values for customization. This function
 * maps them to Backdrop's theme settings.
 *
 * @param string $name
 *   Theme mod name.
 * @param mixed $default
 *   Default value.
 *
 * @return mixed
 *   Theme mod value or default.
 */
function wp4bd_get_theme_mod($name, $default = FALSE) {
  // Get active theme
  $theme = config_get('system.theme', 'default');

  if (!$theme) {
    return $default;
  }

  // Try to get from theme settings
  $value = config_get('theme.settings.' . $theme, $name);

  if ($value !== NULL) {
    return $value;
  }

  // Common theme mod mappings
  $theme_mod_map = array(
    'header_textcolor' => array('key' => 'header_textcolor'),
    'background_color' => array('key' => 'background_color'),
    'header_image' => array('key' => 'header_image'),
    'custom_logo' => array('key' => 'logo_path'),
  );

  if (isset($theme_mod_map[$name])) {
    $mapping = $theme_mod_map[$name];
    $value = config_get('theme.settings.' . $theme, $mapping['key']);
    if ($value !== NULL) {
      return $value;
    }
  }

  return $default;
}
