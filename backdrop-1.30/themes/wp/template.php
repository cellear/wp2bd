<?php
/**
 * @file
 * WordPress Theme Wrapper - template.php
 *
 * Bootstrap file that loads WordPress compatibility layer and delegates
 * rendering to a WordPress theme.
 */

// Define the active WordPress theme (hard-coded for now)
define('WP2BD_ACTIVE_THEME', 'twentyseventeen');

// Define paths
define('WP2BD_THEME_DIR', __DIR__);
define('WP2BD_WP_THEMES_DIR', WP2BD_THEME_DIR . '/wp-content/themes');
define('WP2BD_ACTIVE_THEME_DIR', WP2BD_WP_THEMES_DIR . '/' . WP2BD_ACTIVE_THEME);

// Initialize WordPress globals
global $wp_query, $wp_filter, $wp_actions, $wp_current_filter, $post;
$wp_filter = array();
$wp_actions = array();
$wp_current_filter = array();
$post = null;

// Load WordPress compatibility classes
require_once WP2BD_THEME_DIR . '/classes/WP_Post.php';
require_once WP2BD_THEME_DIR . '/classes/WP_Query.php';

// Load WordPress compatibility functions
require_once WP2BD_THEME_DIR . '/functions/hooks.php';
require_once WP2BD_THEME_DIR . '/functions/escaping.php';
require_once WP2BD_THEME_DIR . '/functions/loop.php';
require_once WP2BD_THEME_DIR . '/functions/template-loading.php';
require_once WP2BD_THEME_DIR . '/functions/content-display.php';
require_once WP2BD_THEME_DIR . '/functions/conditionals.php';
require_once WP2BD_THEME_DIR . '/functions/utilities.php';
require_once WP2BD_THEME_DIR . '/functions/post-metadata.php';
require_once WP2BD_THEME_DIR . '/functions/stubs.php';

// Override get_template_directory() to return WordPress theme directory
function get_template_directory() {
  return WP2BD_ACTIVE_THEME_DIR;
}

function get_template_directory_uri() {
  global $base_url;
  if (!$base_url) {
    $base_url = $GLOBALS['base_url'];
  }
  $theme_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', WP2BD_ACTIVE_THEME_DIR);
  return $base_url . $theme_path;
}

function get_stylesheet_directory() {
  return get_template_directory();
}

function get_stylesheet_directory_uri() {
  return get_template_directory_uri();
}

function get_template() {
  return WP2BD_ACTIVE_THEME;
}

function get_stylesheet() {
  return WP2BD_ACTIVE_THEME;
}

/**
 * Implements hook_preprocess_page().
 *
 * Initialize WordPress query for the current page.
 */
function wp_preprocess_page(&$variables) {
  global $wp_query, $post;

  // Get current Backdrop node if viewing a node
  $node = menu_get_object();

  if ($node) {
    // Convert Backdrop node to WordPress post
    $post = WP_Post::from_node($node);

    // Create a simple WP_Query with this post
    $wp_query = new WP_Query(array(
      'p' => $node->nid,
      'post_type' => 'post',
    ));

    // Manually set up the query state
    $wp_query->posts = array($post);
    $wp_query->post_count = 1;
    $wp_query->current_post = -1;
    $wp_query->is_single = true;
    $wp_query->is_singular = true;
    $wp_query->queried_object = $post;
    $wp_query->queried_object_id = $post->ID;
  } else {
    // Create empty query for non-node pages
    $wp_query = new WP_Query(array('post__in' => array(0)));
  }
}

/**
 * Load WordPress theme's functions.php if it exists
 */
$functions_file = WP2BD_ACTIVE_THEME_DIR . '/functions.php';
if (file_exists($functions_file)) {
  require_once $functions_file;
}

/**
 * Implements template_preprocess_html().
 *
 * Set up HTML attributes using WordPress functions.
 */
function wp_preprocess_html(&$variables) {
  // WordPress themes expect <html> tag to have language attributes
  $variables['html_attributes'] = language_attributes(false);
}
