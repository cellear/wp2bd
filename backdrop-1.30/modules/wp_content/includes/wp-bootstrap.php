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
    $wpbrain_path = '';
    if (function_exists('backdrop_get_path')) {
      $theme_path = backdrop_get_path('theme', 'wp');
      if (!empty($theme_path)) {
        $wpbrain_path = BACKDROP_ROOT . '/' . $theme_path . '/wpbrain';
      }
    }

    // Fallback for test environments or when backdrop_get_path isn't available
    if (empty($wpbrain_path) && defined('BACKDROP_ROOT')) {
      $wpbrain_path = BACKDROP_ROOT . '/themes/wp/wpbrain';
    }

    if (empty($wpbrain_path)) {
      $errors[] = 'Could not determine WordPress core path';
      return FALSE;
    }

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

    // Verify database interception drop-in exists (Epic 3: WP4BD-V2-020)
    $db_dropin_path = ABSPATH . 'wp-content/db.php';
    if (!file_exists($db_dropin_path)) {
      $errors[] = "Database interception drop-in not found: {$db_dropin_path} (required for Epic 3)";
      return FALSE;
    }

    // Load WordPress core files directly (bypassing wp-load.php complexity)
    // We need the essential WordPress classes and functions for theme rendering

    // Load our Backdrop-specific config first
    $wp_config_bd_path = ABSPATH . 'wp-config-bd.php';
    if (file_exists($wp_config_bd_path)) {
      try {
        require_once $wp_config_bd_path;
      } catch (Exception $e) {
        $errors[] = 'Failed to load wp-config-bd.php: ' . $e->getMessage();
        return FALSE;
      } catch (Error $e) {
        $errors[] = 'Fatal error loading wp-config-bd.php: ' . $e->getMessage();
        return FALSE;
      }
    } else {
      $errors[] = "wp-config-bd.php not found: {$wp_config_bd_path}";
      return FALSE;
    }

    // Load essential WordPress core files directly
    $essential_files = array(
      ABSPATH . WPINC . '/load.php',
      ABSPATH . WPINC . '/default-constants.php',
      ABSPATH . WPINC . '/version.php',
      ABSPATH . WPINC . '/class-wp-post.php',
      ABSPATH . WPINC . '/class-wp-query.php',
      ABSPATH . WPINC . '/functions.php',
      ABSPATH . WPINC . '/plugin.php',
      ABSPATH . WPINC . '/theme.php',
      ABSPATH . WPINC . '/template.php',
      ABSPATH . WPINC . '/template-loader.php',
    );

    foreach ($essential_files as $file) {
      if (file_exists($file)) {
        try {
          require_once $file;
        } catch (Exception $e) {
          $errors[] = 'Failed to load WordPress core file: ' . basename($file) . ' - ' . $e->getMessage();
          return FALSE;
        } catch (Error $e) {
          $errors[] = 'Fatal error loading WordPress core file: ' . basename($file) . ' - ' . $e->getMessage();
          return FALSE;
        }
      } else {
        $errors[] = 'WordPress core file not found: ' . $file;
        return FALSE;
      }
    }

    // Success - WordPress core is loaded and constants are set
    // Database interception will be handled in Epic 3 via db.php drop-in
    // WordPress globals will be populated in Epic 4 via wp-globals-init.php

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

