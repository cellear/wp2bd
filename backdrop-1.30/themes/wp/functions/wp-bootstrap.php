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

    // Step 1: Load I/O stubs FIRST - prevents all network, file, and external I/O operations
    $io_stubs_file = dirname(__FILE__) . '/io-stubs.php';
    if (file_exists($io_stubs_file)) {
      require_once $io_stubs_file;
    } else {
      $errors[] = "I/O stubs file not found: {$io_stubs_file}";
      return FALSE;
    }

    // Step 2: Set table prefix (WordPress expects this global)
    if (!isset($GLOBALS['table_prefix'])) {
      $GLOBALS['table_prefix'] = 'wp_';
    }

    // Step 3: Load db.php drop-in SECOND (Epic 3) - prevents WordPress database connection
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

    // Step 3: Load critical WordPress core files
    // Order matters - load dependencies first

    // Load version and constants
    require_once ABSPATH . WPINC . '/version.php';
    require_once ABSPATH . WPINC . '/compat.php';

    // Load ONLY the essential WordPress classes - avoid loading functions that conflict
    require_once ABSPATH . WPINC . '/class-wp-post.php';
    require_once ABSPATH . WPINC . '/class-wp-query.php';

    // Provide minimal function stubs for WordPress themes to work
    if (!function_exists('add_action')) {
      function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
        return true;
      }
    }
    if (!function_exists('add_filter')) {
      function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
        return true;
      }
    }
    if (!function_exists('do_action')) {
      function do_action($tag, ...$args) {
        return;
      }
    }
    if (!function_exists('apply_filters')) {
      function apply_filters($tag, $value, ...$args) {
        return $value;
      }
    }

    // Minimal template functions needed by themes
    if (!function_exists('get_the_title')) {
      function get_the_title($post = 0) {
        global $post;
        if ($post && isset($post->post_title)) {
          return $post->post_title;
        }
        return '';
      }
    }

    if (!function_exists('get_the_content')) {
      function get_the_content($more_link_text = null, $strip_teaser = false) {
        global $post;
        if ($post && isset($post->post_content)) {
          return $post->post_content;
        }
        return '';
      }
    }

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

