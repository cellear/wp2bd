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

// Get active WordPress theme from theme settings (not Backdrop theme!)
if (!defined('WP2BD_ACTIVE_THEME')) {
  $active_theme = 'twentyseventeen'; // Default fallback

  // Try to get from theme settings
  if (function_exists('theme_get_setting')) {
    $theme_setting = theme_get_setting('active_theme', 'wp');
    if (!empty($theme_setting)) {
      $active_theme = $theme_setting;
    }
  }

  define('WP2BD_ACTIVE_THEME', $active_theme);
}

// Define theme paths
if (!defined('WP2BD_THEME_DIR')) {
  define('WP2BD_THEME_DIR', __DIR__);
  define('WP2BD_WP_THEMES_DIR', WP2BD_THEME_DIR . '/wpbrain/wp-content/themes');
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
// NOTE: Functions.php loading moved to wp-rendering-logic.inc
// This prevents errors during cache clear and non-page requests where
// WordPress isn't bootstrapped. The theme's functions.php will be loaded
// when the page is actually rendered, after WordPress bootstrap completes.

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
  // If node not in variables, check if we're on a node page
  elseif (arg(0) == 'node' && is_numeric(arg(1))) {
    $node = node_load(arg(1));
    if ($node) {
      $variables['wp_node'] = $node;
    }
  }
}
