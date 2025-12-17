<?php
/**
 * @file
 * WordPress Bootstrap Entry Point for WP4BD V2
 *
 * Initializes WordPress core with Backdrop-specific configuration.
 * This provides a controlled entry point to load WordPress without
 * allowing database connections or external I/O.
 *
 * Strategy:
 * - Set WordPress constants pointing to wpbrain/ directory
 * - Load WordPress class definitions and constants
 * - DO NOT load wp-settings.php (connects to database)
 * - Database interception will be handled via db.php drop-in (Epic 3)
 *
 * @see DOCS/V2/2025-01-15-WORDPRESS-CORE-INTEGRATION-PLAN.md
 */

/**
 * Bootstrap WordPress core with Backdrop-specific configuration.
 *
 * This function initializes WordPress in "headless" mode - loading core
 * classes and functions but preventing database access and external I/O.
 *
 * @return bool
 *   TRUE if WordPress bootstrapped successfully, FALSE on error.
 */
function wp4bd_bootstrap_wordpress() {
  // Track errors
  $errors = array();

  try {
    // Determine wpbrain path (relative to Backdrop theme)
    $theme_path = backdrop_get_path('theme', 'wp');
    if (empty($theme_path)) {
      $errors[] = 'WordPress theme (wp) not found';
      return FALSE;
    }

    $wpbrain_path = BACKDROP_ROOT . '/' . $theme_path . '/wpbrain';

    // Verify wpbrain directory exists
    if (!is_dir($wpbrain_path)) {
      $errors[] = "WordPress core directory not found: {$wpbrain_path}";
      return FALSE;
    }

    // Define WordPress constants
    // ABSPATH must end with trailing slash
    if (!defined('ABSPATH')) {
      define('ABSPATH', $wpbrain_path . '/');
    }

    if (!defined('WPINC')) {
      define('WPINC', 'wp-includes');
    }

    if (!defined('WP_CONTENT_DIR')) {
      define('WP_CONTENT_DIR', $wpbrain_path . '/wp-content');
    }

    if (!defined('WP_CONTENT_URL')) {
      // URL to WordPress content directory (for accessing assets)
      // This needs to match the theme path accessible via web
      $theme_url = url('', array('absolute' => TRUE)) . 'themes/wp/wpbrain/wp-content';
      define('WP_CONTENT_URL', $theme_url);
    }

    if (!defined('STYLESHEETPATH')) {
      // Path to active theme's stylesheet directory
      define('STYLESHEETPATH', WP_CONTENT_DIR . '/themes/' . WP2BD_ACTIVE_THEME);
    }

    if (!defined('TEMPLATEPATH')) {
      // Path to active theme's template directory (same as STYLESHEETPATH for non-child themes)
      define('TEMPLATEPATH', WP_CONTENT_DIR . '/themes/' . WP2BD_ACTIVE_THEME);
    }

    if (!defined('WP_LANG_DIR')) {
      // Path to languages directory
      define('WP_LANG_DIR', WP_CONTENT_DIR . '/languages');
    }

    // Verify critical WordPress files exist
    $wp_includes_path = ABSPATH . WPINC;
    if (!is_dir($wp_includes_path)) {
      $errors[] = "WordPress wp-includes directory not found: {$wp_includes_path}";
      return FALSE;
    }

    // Check for critical WordPress class files
    $critical_files = array(
      ABSPATH . WPINC . '/class-wp-post.php',
      ABSPATH . WPINC . '/class-wp-query.php',
    );

    foreach ($critical_files as $file) {
      if (!file_exists($file)) {
        $errors[] = "Critical WordPress file not found: {$file}";
        return FALSE;
      }
    }

    // WP4BD V2-051: Load WordPress core files in correct sequence

    // Step 1: Set table prefix (WordPress expects this global)
    if (!isset($GLOBALS['table_prefix'])) {
      $GLOBALS['table_prefix'] = 'wp_';
    }

    // Step 2: Load db.php drop-in FIRST (Epic 3) - prevents WordPress database connection
    // IMPORTANT: Check the ACTUAL path where db.php should be, not just WP_CONTENT_DIR
    // because WP_CONTENT_DIR might have been defined elsewhere with a different value
    $expected_db_dropin = $wpbrain_path . '/wp-content/db.php';

    if (file_exists($expected_db_dropin)) {
      require_once $expected_db_dropin;
      // Verify wpdb class is now loaded from our drop-in
      if (!class_exists('wpdb')) {
        $errors[] = 'db.php drop-in loaded but wpdb class not found';
        return FALSE;
      }
    } else {
      $errors[] = "db.php drop-in not found at: {$expected_db_dropin} - WordPress would try to connect to database!";
      return FALSE;
    }

    // Step 3: Load WordPress core via wp-settings.php
    // This loads all WordPress functions and classes
    // Our db.php drop-in prevents database connection

    // First define essential constants/stubs that wp-settings expects

    // Define essential WordPress functions that conflict with Backdrop or are needed early
    if (!function_exists('wp_installing')) {
      /**
       * Check whether WordPress is in installation mode.
       * @return bool False - we're not installing WordPress
       */
      function wp_installing() {
        return FALSE;
      }
    }

    if (!function_exists('is_admin')) {
      /**
       * Check if we're in WordPress admin area.
       * @return bool False - we're only doing front-end rendering
       */
      function is_admin() {
        return FALSE;
      }
    }

    if (!function_exists('is_multisite')) {
      /**
       * Check if WordPress is in multisite mode.
       * @return bool False - we're not using multisite
       */
      function is_multisite() {
        return FALSE;
      }
    }

    if (!function_exists('is_admin_bar_showing')) {
      /**
       * Check if WordPress admin bar should be shown.
       * @return bool False - we don't show the WP admin bar (Backdrop has its own)
       */
      function is_admin_bar_showing() {
        return FALSE;
      }
    }

    if (!function_exists('is_ssl')) {
      /**
       * Check if SSL is being used.
       * @return bool True if SSL, false otherwise
       */
      function is_ssl() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
      }
    }

    // TODO: TEMPORARY - Theme functions not loading correctly
    // This should be in theme's inc/template-functions.php
    if (!function_exists('twentyseventeen_is_frontpage')) {
      /**
       * Check if we're on the front page for Twenty Seventeen theme.
       * @return bool
       */
      function twentyseventeen_is_frontpage() {
        return (function_exists('is_front_page') && is_front_page() && !is_home());
      }
    }

    if (!function_exists('is_user_logged_in')) {
      /**
       * Check if current user is logged in.
       * @return bool True if user is logged in (via Backdrop)
       */
      function is_user_logged_in() {
        global $user;
        return !empty($user->uid);
      }
    }

    // Stub cache functions - we use Backdrop's caching instead
    if (!function_exists('wp_cache_get')) {
      function wp_cache_get($key, $group = '', &$found = null) {
        $found = FALSE;
        return FALSE;
      }
    }
    if (!function_exists('wp_cache_set')) {
      function wp_cache_set($key, $data, $group = '', $expire = 0) {
        return TRUE;
      }
    }
    if (!function_exists('wp_cache_add')) {
      function wp_cache_add($key, $data, $group = '', $expire = 0) {
        return TRUE;
      }
    }
    if (!function_exists('wp_cache_delete')) {
      function wp_cache_delete($key, $group = '') {
        return TRUE;
      }
    }
    if (!function_exists('wp_using_ext_object_cache')) {
      /**
       * Check if external object cache is being used.
       * @return bool False - we use Backdrop's caching
       */
      function wp_using_ext_object_cache() {
        return FALSE;
      }
    }
    if (!function_exists('wp_cache_add_non_persistent_groups')) {
      /**
       * Mark cache groups as non-persistent (don't save to permanent cache).
       * @param string|array $groups Cache group(s) to mark as non-persistent
       */
      function wp_cache_add_non_persistent_groups($groups) {
        // No-op - we use Backdrop's caching
      }
    }

    // WordPress utility functions
    if (!function_exists('wp_parse_args')) {
      /**
       * Merge user defined arguments into defaults array.
       * @param string|array|object $args Value to merge with $defaults.
       * @param array $defaults Array that serves as the defaults.
       * @return array Merged user defined values with defaults.
       */
      function wp_parse_args($args, $defaults = '') {
        if (is_object($args)) {
          $r = get_object_vars($args);
        } elseif (is_array($args)) {
          $r =& $args;
        } else {
          parse_str($args, $r);
        }

        if (is_array($defaults)) {
          return array_merge($defaults, $r);
        }
        return $r;
      }
    }

    if (!function_exists('absint')) {
      /**
       * Convert a value to non-negative integer.
       * @param mixed $maybeint Data to be converted to non-negative integer.
       * @return int Non-negative integer.
       */
      function absint($maybeint) {
        return abs(intval($maybeint));
      }
    }

    // Utility functions from functions.php (safe subset - no I/O)
    // Note: We do NOT load full functions.php as it contains dangerous I/O operations
    // (wp_remote_fopen, file operations, etc.) See DOCS/dec17-CLAUDE-FUNCTIONS-ANALYSIS.md

    if (!function_exists('mysql2date')) {
      /**
       * Convert MySQL datetime to PHP date format.
       * @param string $format PHP date format
       * @param string $date MySQL datetime string
       * @param bool $translate Whether to translate (use date_i18n)
       * @return string|int|false Formatted date or false on error
       */
      function mysql2date($format, $date, $translate = TRUE) {
        if (empty($date)) {
          return FALSE;
        }

        if ('G' == $format) {
          return strtotime($date . ' +0000');
        }

        $i = strtotime($date);

        if ('U' == $format) {
          return $i;
        }

        if ($translate && function_exists('date_i18n')) {
          return date_i18n($format, $i);
        }
        else {
          return date($format, $i);
        }
      }
    }

    if (!function_exists('_cleanup_header_comment')) {
      /**
       * Strip close comment and close php tags from file headers.
       * @param string $str Header comment to clean up
       * @return string Cleaned up header comment
       */
      function _cleanup_header_comment($str) {
        return trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $str));
      }
    }

    if (!function_exists('wp_allowed_protocols')) {
      /**
       * Retrieve list of allowed URL protocols.
       * @return array Array of allowed protocols
       */
      function wp_allowed_protocols() {
        static $protocols = array();

        if (empty($protocols)) {
          $protocols = array('http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp', 'webcal', 'urn');
        }

        if (function_exists('did_action') && !did_action('wp_loaded')) {
          if (function_exists('apply_filters')) {
            $protocols = array_unique((array) apply_filters('kses_allowed_protocols', $protocols));
          }
        }

        return $protocols;
      }
    }

    if (!function_exists('get_file_data')) {
      /**
       * Retrieve metadata from a file (theme/plugin headers).
       * @param string $file Path to the file
       * @param array $default_headers List of headers to look for
       * @param string $context Optional. Context for extra headers filter
       * @return array Array of file header values
       */
      function get_file_data($file, $default_headers, $context = '') {
        // Read first 8KB of file
        $fp = fopen($file, 'r');
        $file_data = fread($fp, 8192);
        fclose($fp);

        // Normalize line endings
        $file_data = str_replace("\r", "\n", $file_data);

        // Allow plugins to add extra headers
        if ($context && function_exists('apply_filters')) {
          $extra_headers = apply_filters("extra_{$context}_headers", array());
          if ($extra_headers) {
            $extra_headers = array_combine($extra_headers, $extra_headers);
            $all_headers = array_merge($extra_headers, (array) $default_headers);
          }
          else {
            $all_headers = $default_headers;
          }
        }
        else {
          $all_headers = $default_headers;
        }

        // Extract header values
        foreach ($all_headers as $field => $regex) {
          if (preg_match('/^[ \t\/*#@]*' . preg_quote($regex, '/') . ':(.*)$/mi', $file_data, $match) && $match[1]) {
            $all_headers[$field] = _cleanup_header_comment($match[1]);
          }
          else {
            $all_headers[$field] = '';
          }
        }

        return $all_headers;
      }
    }

    if (!function_exists('wp_filter_object_list')) {
      /**
       * Filter a list of objects, based on a set of key => value arguments.
       * Safe to include - uses WP_List_Util class (pure array manipulation, no I/O)
       *
       * @param array $list An array of objects to filter.
       * @param array $args Optional. An array of key => value arguments to match.
       * @param string $operator Optional. The logical operation ('and', 'or', 'not').
       * @param bool|string $field Optional. A field from the object to place instead of entire object.
       * @return array Array of found values.
       */
      function wp_filter_object_list($list, $args = array(), $operator = 'and', $field = false) {
        if (!is_array($list)) {
          return array();
        }

        $util = new WP_List_Util($list);
        $util->filter($args, $operator);

        if ($field) {
          $util->pluck($field);
        }

        return $util->get_output();
      }
    }

    if (!function_exists('wp_list_filter')) {
      /**
       * Filters a list of objects, based on a set of key => value arguments.
       *
       * @param array $list An array of objects to filter.
       * @param array $args Optional. An array of key => value arguments to match.
       * @param string $operator Optional. The logical operation ('AND', 'OR', 'NOT').
       * @return array Array of found values.
       */
      function wp_list_filter($list, $args = array(), $operator = 'AND') {
        if (!is_array($list)) {
          return array();
        }

        $util = new WP_List_Util($list);
        return $util->filter($args, $operator);
      }
    }

    if (!function_exists('wp_list_pluck')) {
      /**
       * Pluck a certain field out of each object in a list.
       *
       * @param array $list List of objects or arrays.
       * @param int|string $field Field from the object to place instead of entire object.
       * @param int|string $index_key Optional. Field to use as keys for the new array.
       * @return array Array of found values.
       */
      function wp_list_pluck($list, $field, $index_key = null) {
        if (!is_array($list)) {
          return array();
        }

        $util = new WP_List_Util($list);
        return $util->pluck($field, $index_key);
      }
    }

    // Load class definitions
    require_once ABSPATH . WPINC . '/class-wp-post.php';
    require_once ABSPATH . WPINC . '/class-wp-query.php';
    require_once ABSPATH . WPINC . '/class-wp-hook.php';
    require_once ABSPATH . WPINC . '/class-wp-list-util.php';
    require_once ABSPATH . WPINC . '/class-wp-tax-query.php';
    require_once ABSPATH . WPINC . '/class-wp-meta-query.php';
    require_once ABSPATH . WPINC . '/class-wp-user.php';
    require_once ABSPATH . WPINC . '/class-wp-theme.php';  // Note: reads local template files

    // Load plugin system (needed for hooks)
    require_once ABSPATH . WPINC . '/plugin.php';

    // Load formatting and escaping
    require_once ABSPATH . WPINC . '/formatting.php';
    require_once ABSPATH . WPINC . '/kses.php';

    // Load post functions
    require_once ABSPATH . WPINC . '/post.php';
    require_once ABSPATH . WPINC . '/query.php';

    // Load user functions
    require_once ABSPATH . WPINC . '/user.php';

    // Load taxonomy functions
    require_once ABSPATH . WPINC . '/taxonomy.php';

    // Load navigation menu functions
    require_once ABSPATH . WPINC . '/nav-menu.php';

    // Load template functions
    require_once ABSPATH . WPINC . '/post-template.php';
    require_once ABSPATH . WPINC . '/general-template.php';
    require_once ABSPATH . WPINC . '/link-template.php';
    require_once ABSPATH . WPINC . '/author-template.php';
    require_once ABSPATH . WPINC . '/category-template.php';

    // Load theme support
    require_once ABSPATH . WPINC . '/theme.php';
    require_once ABSPATH . WPINC . '/template.php';

    // Load internationalization
    require_once ABSPATH . WPINC . '/pomo/translations.php';
    require_once ABSPATH . WPINC . '/l10n.php';

    // DO NOT load WordPress's option.php - we provide our own get_option() via wp-options-bridge.php
    // require_once ABSPATH . WPINC . '/option.php';

    // Step 4: Initialize $wpdb global using our drop-in class
    global $wpdb;
    if (!isset($wpdb)) {
      // Create wpdb instance with fake credentials (db.php drop-in won't actually connect)
      $wpdb = new wpdb('backdrop_user', 'no_password_needed', 'backdrop_db', 'localhost');
    }

    // Step 5: Load globals initialization from Epic 4
    $globals_init_file = dirname(__FILE__) . '/wp-globals-init.php';
    if (file_exists($globals_init_file)) {
      require_once $globals_init_file;
    }

    // Step 6: Load data bridge files from Epic 7
    $post_bridge_file = dirname(__FILE__) . '/wp-post-bridge.php';
    if (file_exists($post_bridge_file)) {
      require_once $post_bridge_file;
    }

    $user_bridge_file = dirname(__FILE__) . '/wp-user-bridge.php';
    if (file_exists($user_bridge_file)) {
      require_once $user_bridge_file;
    }

    // Pluggable functions (from pluggable.php) - stub safely without loading that file
    // Note: We do NOT load pluggable.php as it contains wp_mail() which sends email (I/O)
    if (!function_exists('get_userdata')) {
      /**
       * Retrieve user info by user ID.
       * @param int $user_id User ID
       * @return WP_User|false WP_User object on success, false on failure.
       */
      function get_userdata($user_id) {
        return get_user_by('id', $user_id);
      }
    }

    if (!function_exists('get_user_by')) {
      /**
       * Retrieve user info by a given field.
       * @param string $field The field to retrieve the user with. id | ID | slug | email | login.
       * @param int|string $value A value for $field.
       * @return WP_User|false WP_User object on success, false on failure.
       */
      function get_user_by($field, $value) {
        $userdata = WP_User::get_data_by($field, $value);

        if (!$userdata) {
          return FALSE;
        }

        $user = new WP_User();
        $user->init($userdata);

        return $user;
      }
    }

    $term_bridge_file = dirname(__FILE__) . '/wp-term-bridge.php';
    if (file_exists($term_bridge_file)) {
      require_once $term_bridge_file;
    }

    $options_bridge_file = dirname(__FILE__) . '/wp-options-bridge.php';
    if (file_exists($options_bridge_file)) {
      require_once $options_bridge_file;
    }

    // Success - WordPress core loaded, database intercepted, ready for rendering
    return TRUE;

  } catch (Exception $e) {
    $errors[] = 'Exception during WordPress bootstrap: ' . $e->getMessage();
    return FALSE;
  } catch (Error $e) {
    $errors[] = 'Error during WordPress bootstrap: ' . $e->getMessage();
    return FALSE;
  } finally {
    // Log any errors
    if (!empty($errors)) {
      if (function_exists('watchdog')) {
        foreach ($errors as $error) {
          watchdog('wp4bd', 'WordPress bootstrap error: @error', array('@error' => $error), WATCHDOG_ERROR);
        }
      }
    }
  }
}

/**
 * Get WordPress core information.
 *
 * Returns information about the WordPress core installation for debugging.
 *
 * @return array
 *   Array with keys:
 *   - abspath: WordPress ABSPATH constant
 *   - wpinc: WordPress WPINC constant
 *   - wp_content_dir: WordPress WP_CONTENT_DIR constant
 *   - exists: Whether WordPress core files exist
 *   - version: WordPress version (if available)
 */
function wp4bd_get_wordpress_info() {
  $info = array(
    'abspath' => defined('ABSPATH') ? ABSPATH : NULL,
    'wpinc' => defined('WPINC') ? WPINC : NULL,
    'wp_content_dir' => defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : NULL,
    'exists' => FALSE,
    'version' => NULL,
  );

  // Check if WordPress exists
  if (defined('ABSPATH') && is_dir(ABSPATH)) {
    $info['exists'] = TRUE;

    // Try to get WordPress version
    $version_file = ABSPATH . WPINC . '/version.php';
    if (file_exists($version_file)) {
      // WordPress version.php sets $wp_version global
      include $version_file;
      if (isset($wp_version)) {
        $info['version'] = $wp_version;
      }
    }
  }

  return $info;
}

