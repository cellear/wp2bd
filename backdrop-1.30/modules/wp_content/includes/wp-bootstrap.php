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

    // Verify database interception drop-in exists (Epic 3: WP4BD-V2-020)
    $db_dropin_path = ABSPATH . 'wp-content/db.php';
    if (!file_exists($db_dropin_path)) {
      $errors[] = "Database interception drop-in not found: {$db_dropin_path} (required for Epic 3)";
      return FALSE;
    }

    // Load WordPress core files (but not wp-settings.php which connects to DB)
    // We need to load wp-load.php which includes the necessary WordPress core
    $wp_load_path = ABSPATH . 'wp-load.php';
    if (file_exists($wp_load_path)) {
      // Temporarily disable any database connections by setting a flag
      if (!defined('WP4BD_SKIP_DB_CONNECTION')) {
        define('WP4BD_SKIP_DB_CONNECTION', TRUE);
      }

      try {
        require_once $wp_load_path;
      } catch (Exception $e) {
        $errors[] = 'Failed to load wp-load.php: ' . $e->getMessage();
        return FALSE;
      } catch (Error $e) {
        $errors[] = 'Fatal error loading wp-load.php: ' . $e->getMessage();
        return FALSE;
      }
    } else {
      $errors[] = "wp-load.php not found: {$wp_load_path}";
      return FALSE;
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

