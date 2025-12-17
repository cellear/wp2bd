<?php
/**
 * @file
 * WordPress Theme Wrapper - template.php
 *
 * Theme-Only Architecture:
 * - No wp_content module needed
 * - All WordPress functionality in this theme
 * - WordPress bootstraps here and renders in page.tpl.php
 */

// ============================================================================
// STEP 1: Define Theme Constants
// ============================================================================

// Get active WordPress theme from config (not Backdrop theme!)
if (!defined('WP2BD_ACTIVE_THEME')) {
  $active_theme = 'twentyseventeen'; // Default fallback

  // Try to get from theme-specific config
  try {
    if (function_exists('config')) {
      $config = config('wp.settings');
      if ($config) {
        $config_theme = $config->get('active_theme');
        if (!empty($config_theme)) {
          $active_theme = $config_theme;
        }
      }
    }
  } catch (Exception $e) {
    // Config not available, use default
  }

  define('WP2BD_ACTIVE_THEME', $active_theme);
}

// Define theme paths
if (!defined('WP2BD_THEME_DIR')) {
  define('WP2BD_THEME_DIR', __DIR__);
  define('WP2BD_WP_THEMES_DIR', WP2BD_THEME_DIR . '/wp-content/themes');
  define('WP2BD_ACTIVE_THEME_DIR', WP2BD_WP_THEMES_DIR . '/' . WP2BD_ACTIVE_THEME);
}

// ============================================================================
// STEP 2: Bootstrap WordPress
// ============================================================================

$bootstrap_file = WP2BD_THEME_DIR . '/includes/wp-bootstrap.php';
if (file_exists($bootstrap_file)) {
  require_once $bootstrap_file;

  // Bootstrap WordPress core (loads wpbrain, sets up db interception, loads bridges)
  if (function_exists('wp4bd_bootstrap_wordpress')) {
    $bootstrap_success = wp4bd_bootstrap_wordpress();

    if (!$bootstrap_success) {
      // Log error but continue - page.tpl.php will handle gracefully
      if (function_exists('watchdog')) {
        watchdog('wp_theme', 'WordPress bootstrap failed', array(), WATCHDOG_ERROR);
      }
    }
  }
} else {
  // Bootstrap file missing
  if (function_exists('watchdog')) {
    watchdog('wp_theme', 'WordPress bootstrap file not found: @file',
      array('@file' => $bootstrap_file), WATCHDOG_ERROR);
  }
}

// ============================================================================
// STEP 3: Load WordPress Theme's functions.php
// ============================================================================

// Load the active WordPress theme's functions.php
// This is where the theme registers its hooks, enqueues scripts, etc.
$theme_functions = WP2BD_ACTIVE_THEME_DIR . '/functions.php';
if (file_exists($theme_functions)) {
  require_once $theme_functions;
}

// ============================================================================
// Theme Preprocessing Functions (Backdrop theme layer)
// ============================================================================

/**
 * Implements template_preprocess_page().
 *
 * Set up variables for page.tpl.php
 */
function wp_preprocess_page(&$variables) {
  // Make node available if viewing a node
  if (!empty($variables['node'])) {
    $variables['wp_node'] = $variables['node'];
  }
}
