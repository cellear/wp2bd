<?php
/**
 * @file
 * WordPress Theme Wrapper - page.tpl.php
 *
 * Theme-Only Architecture:
 * This template receives Backdrop data and uses WordPress to render it.
 *
 * Flow:
 * 1. Backdrop provides $node (or list of nodes)
 * 2. We convert to WordPress objects using bridges
 * 3. WordPress theme renders with its templates
 * 4. We capture output and return to Backdrop
 */

// ============================================================================
// Check WordPress Bootstrap
// ============================================================================

$wordpress_ready = (
  function_exists('have_posts') &&
  function_exists('the_post') &&
  class_exists('WP_Post') &&
  class_exists('WP_Query') &&
  function_exists('wp4bd_node_to_post')
);

if (!$wordpress_ready) {
  // WordPress didn't bootstrap - show error
  print '<div class="error">';
  print '<h2>WordPress Bootstrap Failed</h2>';
  print '<p>The WordPress rendering engine could not initialize.</p>';
  print '</div>';
  return;
}

// ============================================================================
// Convert Backdrop Data to WordPress Objects
// ============================================================================

global $wp_query, $post;

// Check if we're viewing a single node
if (!empty($wp_node)) {
  // Single node view - convert to WP_Post
  $wp_post = wp4bd_node_to_post($wp_node);

  if ($wp_post) {
    // Set up WordPress query for single post
    $wp_query = new WP_Query();
    $wp_query->posts = array($wp_post);
    $wp_query->post_count = 1;
    $wp_query->found_posts = 1;
    $wp_query->max_num_pages = 1;
    $wp_query->is_single = true;
    $wp_query->is_singular = true;
    $wp_query->is_home = false;
    $wp_query->is_front_page = false;

    // Set global post
    $post = $wp_post;
    $GLOBALS['post'] = $post;
    $GLOBALS['wp_query'] = $wp_query;
  }
}
else {
  // Archive/listing view - load multiple nodes
  // For now, create empty query (can be enhanced later for listing pages)
  $wp_query = new WP_Query(array('post__in' => array(0)));
  $wp_query->is_home = backdrop_is_front_page();
  $wp_query->is_front_page = backdrop_is_front_page();
  $GLOBALS['wp_query'] = $wp_query;
}

// ============================================================================
// Render WordPress Theme Output
// ============================================================================

// Start output buffering to capture WordPress's echo statements
ob_start();

// Determine which WordPress template to load
// WordPress themes have different templates: index.php, single.php, page.php, etc.
$template_file = null;

if (!empty($wp_post)) {
  // Single post/page view
  $template_dir = get_template_directory();

  // Try to find the appropriate template file
  if ($wp_post->post_type === 'page') {
    // Page template
    if (file_exists($template_dir . '/page.php')) {
      $template_file = $template_dir . '/page.php';
    }
  }
  else {
    // Post template
    if (file_exists($template_dir . '/single.php')) {
      $template_file = $template_dir . '/single.php';
    }
  }

  // Fallback to index.php
  if (!$template_file && file_exists($template_dir . '/index.php')) {
    $template_file = $template_dir . '/index.php';
  }
}
else {
  // Listing/archive view - use index.php
  $template_dir = get_template_directory();
  if (file_exists($template_dir . '/index.php')) {
    $template_file = $template_dir . '/index.php';
  }
}

// Load the WordPress theme template
if ($template_file && file_exists($template_file)) {
  // Set up WordPress loop globals
  $wp_query->current_post = -1;

  // Include the WordPress template
  // This will call get_header(), the_post(), the_content(), get_footer(), etc.
  // All of which echo their output
  include $template_file;
}
else {
  // No template found - basic fallback
  echo '<div class="wordpress-fallback">';
  echo '<p>WordPress template not found.</p>';
  echo '</div>';
}

// Capture all the output WordPress echo'd
$wordpress_output = ob_get_clean();

// ============================================================================
// Return to Backdrop
// ============================================================================

// Print the WordPress-generated HTML
// Backdrop will wrap this with its page structure (admin menu, etc.)
print $wordpress_output;
