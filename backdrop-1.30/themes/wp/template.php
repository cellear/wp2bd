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

// Load the active WordPress theme's functions.php
// This is where the theme registers its hooks, enqueues scripts, etc.
$theme_functions = WP2BD_ACTIVE_THEME_DIR . '/functions.php';
if (file_exists($theme_functions)) {
  require_once $theme_functions;
}

// Manually load theme's include files in case functions.php didn't load them
// (Twenty Seventeen includes these at the end of functions.php)
$theme_includes = array(
  '/inc/custom-header.php',
  '/inc/template-tags.php',
  '/inc/template-functions.php',
  '/inc/customizer.php',
  '/inc/icon-functions.php',
);

foreach ($theme_includes as $inc_file) {
  $inc_path = WP2BD_ACTIVE_THEME_DIR . $inc_file;
  if (file_exists($inc_path)) {
    require_once $inc_path;
  }
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
  // If node not in variables, check if we're on a node page
  elseif (arg(0) == 'node' && is_numeric(arg(1))) {
    $node = node_load(arg(1));
    if ($node) {
      $variables['wp_node'] = $node;
    }
  }
}
