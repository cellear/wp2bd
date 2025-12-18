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

// Debug mode - only enable if ?DEBUG parameter is present in URL
$debug_mode = isset($_GET['DEBUG']);
$debug_info = array();
if ($debug_mode) {
  $debug_info['test'] = 'Debug system is active';
}

// Collect debug info about Backdrop data
if ($debug_mode) {
  $debug_info['backdrop_variables'] = array(
    'wp_node_exists' => !empty($wp_node),
    'wp_node_type' => !empty($wp_node) ? (isset($wp_node->type) ? $wp_node->type : 'unknown') : 'none',
    'wp_node_nid' => !empty($wp_node) ? (isset($wp_node->nid) ? $wp_node->nid : 'unknown') : 'none',
    'wp_node_title' => !empty($wp_node) ? (isset($wp_node->title) ? $wp_node->title : 'no title') : 'none',
    'node_exists' => !empty($node),
    'variables_keys' => array_keys(get_defined_vars()),
  );
}

// Check if we're viewing a single node
if (!empty($wp_node)) {
  if ($debug_mode) {
    $debug_info['processing'] = 'Single node view detected';
  }

  // Single node view - convert to WP_Post
  $wp_post = wp4bd_node_to_post($wp_node);

  if ($debug_mode) {
    $debug_info['conversion'] = array(
      'wp4bd_node_to_post_returned' => !empty($wp_post),
      'wp_post_type' => !empty($wp_post) ? get_class($wp_post) : 'conversion failed',
      'wp_post_ID' => !empty($wp_post) ? $wp_post->ID : 'none',
      'wp_post_title' => !empty($wp_post) ? $wp_post->post_title : 'none',
      'wp_post_content_length' => !empty($wp_post) && !empty($wp_post->post_content) ? strlen($wp_post->post_content) : 0,
    );
  }

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

    // Set queried object (needed by get_body_class() and other template functions)
    $wp_query->queried_object = $wp_post;
    $wp_query->queried_object_id = $wp_post->ID;

    // Set global post
    $post = $wp_post;
    $GLOBALS['post'] = $post;
    $GLOBALS['wp_query'] = $wp_query;

    if ($debug_mode) {
      $debug_info['wordpress_globals'] = array(
        'global_post_set' => isset($GLOBALS['post']),
        'global_post_ID' => isset($GLOBALS['post']) ? $GLOBALS['post']->ID : 'not set',
        'wp_query_post_count' => $wp_query->post_count,
        'wp_query_is_single' => $wp_query->is_single,
      );
    }
  }
  else {
    if ($debug_mode) {
      $debug_info['error'] = 'wp4bd_node_to_post() returned NULL - conversion failed';
    }
  }
}
else {
  // Archive/listing view - load multiple nodes
  if ($debug_mode) {
    $debug_info['processing'] = 'Archive/listing view - loading all published posts';
    $debug_info['page_type'] = backdrop_is_front_page() ? 'front page' : 'other';
  }

  // Load all published post nodes from Backdrop
  $query = db_select('node', 'n')
    ->fields('n', array('nid'))
    ->condition('n.type', 'post', '=')
    ->condition('n.status', 1, '=')
    ->orderBy('n.created', 'DESC')
    ->range(0, 10);  // Load 10 most recent posts

  $nids = $query->execute()->fetchCol();

  if ($debug_mode) {
    $debug_info['backdrop_query'] = array(
      'found_nids' => $nids,
      'count' => count($nids),
    );
  }

  // Convert all nodes to WP_Post objects
  $wp_posts = array();
  $nodes = array();
  if (!empty($nids)) {
    $nodes = node_load_multiple($nids);
    foreach ($nodes as $node) {
      $wp_post = wp4bd_node_to_post($node);
      if ($wp_post) {
        $wp_posts[] = $wp_post;
      }
    }
  }

  if ($debug_mode) {
    $debug_info['conversion_results'] = array(
      'nodes_loaded' => count($nodes),
      'posts_converted' => count($wp_posts),
      'post_titles' => array_map(function($p) { return $p->post_title; }, $wp_posts),
    );
  }

  // Set up WordPress query for multiple posts
  $wp_query = new WP_Query();
  $wp_query->posts = $wp_posts;
  $wp_query->post_count = count($wp_posts);
  $wp_query->found_posts = count($wp_posts);
  $wp_query->max_num_pages = 1;
  $wp_query->is_single = false;
  $wp_query->is_singular = false;
  $wp_query->is_home = backdrop_is_front_page();
  $wp_query->is_front_page = backdrop_is_front_page();

  $GLOBALS['wp_query'] = $wp_query;

  if ($debug_mode) {
    $debug_info['wordpress_globals'] = array(
      'global_post_set' => isset($GLOBALS['post']),
      'wp_query_post_count' => $wp_query->post_count,
      'wp_query_is_home' => $wp_query->is_home,
      'wp_query_is_front_page' => $wp_query->is_front_page,
    );
  }
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
// Debug Output (if enabled)
// ============================================================================

if ($debug_mode && !empty($debug_info)) {
  // Build debug display
  $debug_html = '<div style="background: #f0f0f0; border: 3px solid #c00; padding: 20px; margin: 20px; font-family: monospace; font-size: 14px;">';
  $debug_html .= '<h2 style="margin-top: 0; color: #c00;">WP Theme Debug: Data Flow</h2>';

  foreach ($debug_info as $section => $data) {
    $debug_html .= '<div style="margin-bottom: 15px;">';
    $debug_html .= '<strong style="color: #060;">' . htmlspecialchars($section) . ':</strong><br>';

    if (is_array($data)) {
      $debug_html .= '<pre style="background: white; padding: 10px; margin: 5px 0; overflow-x: auto;">';
      $debug_html .= htmlspecialchars(print_r($data, TRUE));
      $debug_html .= '</pre>';
    }
    else {
      $debug_html .= '<div style="padding: 5px 10px; background: white; margin: 5px 0;">';
      $debug_html .= htmlspecialchars($data);
      $debug_html .= '</div>';
    }
    $debug_html .= '</div>';
  }

  $debug_html .= '</div>';

  // Insert debug output after <body> tag if possible
  if (preg_match('/<body[^>]*>/', $wordpress_output, $matches, PREG_OFFSET_CAPTURE)) {
    $body_pos = $matches[0][1] + strlen($matches[0][0]);
    $wordpress_output = substr_replace($wordpress_output, $debug_html, $body_pos, 0);
  }
  else {
    // If no body tag found, prepend it
    $wordpress_output = $debug_html . $wordpress_output;
  }
}

// ============================================================================
// Return to Backdrop
// ============================================================================

// Print the WordPress-generated HTML
// Backdrop will wrap this with its page structure (admin menu, etc.)
print $wordpress_output;
