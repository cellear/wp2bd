<?php
/**
 * @file
 * Shared WordPress Rendering Logic
 *
 * This file contains the core logic for converting Backdrop data to WordPress
 * objects and rendering the WordPress theme output.
 *
 * Used by:
 * - page.tpl.php (full page template)
 * - wp-page-content.tpl.php (block template)
 *
 * Output Variables:
 * - $wordpress_output: The rendered WordPress theme HTML
 */

// ============================================================================
// Load WordPress Theme Functions
// ============================================================================

// Load the active WordPress theme's functions.php
// This is done here (not in template.php) to ensure WordPress is fully bootstrapped
// CRITICAL: We MUST verify that WordPress hooks system is loaded before including theme files
$wordpress_hooks_ready = (
  function_exists('add_action') &&
  function_exists('add_filter') &&
  function_exists('do_action') &&
  function_exists('apply_filters')
);

if (!defined('WP_THEME_FUNCTIONS_LOADED') && $wordpress_hooks_ready) {
  if (defined('WP2BD_ACTIVE_THEME_DIR')) {
    $theme_functions = WP2BD_ACTIVE_THEME_DIR . '/functions.php';
    if (file_exists($theme_functions)) {
      require_once $theme_functions;
      define('WP_THEME_FUNCTIONS_LOADED', true);
    }

    // Manually load theme's include files
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
  }
} elseif (!$wordpress_hooks_ready) {
  // WordPress not ready - this should never happen since page.tpl.php checks for it
  error_log('WP Theme: Cannot load functions.php - WordPress hook system not ready');
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

// Debug template loading if enabled
if ($debug_mode) {
  $debug_info['template_functions'] = array(
    'get_header_exists' => function_exists('get_header'),
    'get_footer_exists' => function_exists('get_footer'),
    'locate_template_exists' => function_exists('locate_template'),
    'load_template_exists' => function_exists('load_template'),
    'get_template_directory_exists' => function_exists('get_template_directory'),
    'TEMPLATEPATH_defined' => defined('TEMPLATEPATH'),
    'TEMPLATEPATH_value' => defined('TEMPLATEPATH') ? TEMPLATEPATH : 'not defined',
  );

  // Check if header.php exists
  if (defined('TEMPLATEPATH')) {
    $header_path = TEMPLATEPATH . '/header.php';
    $debug_info['header_file'] = array(
      'path' => $header_path,
      'exists' => file_exists($header_path),
      'readable' => file_exists($header_path) ? is_readable($header_path) : false,
    );
  }
}

// Start output buffering to capture WordPress's echo statements
ob_start();

// Add debug tracers for style/script enqueuing
if ($debug_mode) {
  // Trace get_header() calls
  if (!function_exists('wp4bd_trace_get_header')) {
    function wp4bd_trace_get_header($name = null) {
      global $wp4bd_header_called;
      $wp4bd_header_called = true;
      echo "<!-- DEBUG: get_header() was called with name=" . var_export($name, true) . " -->\n";
    }
    add_action('get_header', 'wp4bd_trace_get_header', 1);
  }

  // Trace wp_head action
  if (!function_exists('wp4bd_trace_wp_head')) {
    function wp4bd_trace_wp_head() {
      echo "<!-- DEBUG: wp_head action fired -->\n";
    }
    add_action('wp_head', 'wp4bd_trace_wp_head', 0);
  }

  // Trace wp_enqueue_scripts action
  if (!function_exists('wp4bd_trace_enqueue_scripts')) {
    function wp4bd_trace_enqueue_scripts() {
      echo "<!-- DEBUG: wp_enqueue_scripts action fired -->\n";
    }
    add_action('wp_enqueue_scripts', 'wp4bd_trace_enqueue_scripts', 999);
  }

  // Trace when wp_print_styles function is called (not the action)
  if (!function_exists('wp4bd_trace_wp_head_priority_7')) {
    function wp4bd_trace_wp_head_priority_7() {
      echo "<!-- DEBUG: wp_head priority 7 (before wp_print_styles at 8) -->\n";
    }
    add_action('wp_head', 'wp4bd_trace_wp_head_priority_7', 7);
  }

  if (!function_exists('wp4bd_trace_wp_head_priority_9')) {
    function wp4bd_trace_wp_head_priority_9() {
      echo "<!-- DEBUG: wp_head priority 9 (after wp_print_styles at 8) -->\n";
    }
    add_action('wp_head', 'wp4bd_trace_wp_head_priority_9', 9);
  }

  // Trace wp_print_styles action
  if (!function_exists('wp4bd_trace_print_styles')) {
    function wp4bd_trace_print_styles() {
      global $wp_styles;
      echo "<!-- DEBUG: wp_print_styles() called -->\n";
      if (isset($wp_styles)) {
        echo "<!-- DEBUG: wp_styles object exists, class: " . get_class($wp_styles) . " -->\n";
        echo "<!-- DEBUG: wp_styles queue count: " . count($wp_styles->queue) . " -->\n";
        if (!empty($wp_styles->queue)) {
          echo "<!-- DEBUG: Queued styles: " . implode(', ', $wp_styles->queue) . " -->\n";
        }
        if (!empty($wp_styles->registered)) {
          echo "<!-- DEBUG: Registered styles count: " . count($wp_styles->registered) . " -->\n";
        }
      } else {
        echo "<!-- DEBUG: wp_styles global does NOT exist -->\n";
      }
    }
    add_action('wp_print_styles', 'wp4bd_trace_print_styles', 1);
  }

  // Trace after wp_print_styles to see what was output
  if (!function_exists('wp4bd_trace_after_print_styles')) {
    function wp4bd_trace_after_print_styles() {
      global $wp_styles;
      echo "<!-- DEBUG: After wp_print_styles hook -->\n";
      echo "<!-- DEBUG: wp_styles->done array: " . implode(', ', $wp_styles->done) . " -->\n";
      if (isset($wp_styles->registered['twentyseventeen-style'])) {
        $obj = $wp_styles->registered['twentyseventeen-style'];
        echo "<!-- DEBUG: twentyseventeen-style src: " . ($obj->src ? $obj->src : 'EMPTY') . " -->\n";
        echo "<!-- DEBUG: twentyseventeen-style ver: " . $obj->ver . " -->\n";
        echo "<!-- DEBUG: wp_styles->base_url: " . $wp_styles->base_url . " -->\n";

        // Check _css_href result
        if (method_exists($wp_styles, '_css_href')) {
          $href = $wp_styles->_css_href($obj->src, $obj->ver, 'twentyseventeen-style');
          echo "<!-- DEBUG: _css_href result: " . ($href ? $href : 'EMPTY/FALSE') . " -->\n";
        }
      }
    }
    add_action('wp_print_styles', 'wp4bd_trace_after_print_styles', 999);
  }

  // Trace the actual style_loader_tag filter to see if tags are being generated
  if (!function_exists('wp4bd_trace_style_tag')) {
    function wp4bd_trace_style_tag($html, $handle) {
      echo "<!-- DEBUG: style_loader_tag for {$handle}: " . esc_html($html) . " -->\n";
      return $html;
    }
    add_filter('style_loader_tag', 'wp4bd_trace_style_tag', 999, 2);
  }
}

// WORKAROUND: Manually output styles during wp_head since WP_Styles echoes are being buffered
// This runs OUTSIDE the debug-only block because it's needed for production too
if (!function_exists('wp4bd_manual_print_styles')) {
  function wp4bd_manual_print_styles() {
    global $wp_styles;

    if (!isset($wp_styles) || empty($wp_styles->done)) {
      return;
    }

    // Manually output each done style
    foreach ($wp_styles->done as $handle) {
      if (isset($wp_styles->registered[$handle])) {
        $obj = $wp_styles->registered[$handle];

        // Skip if no src (inline styles only)
        if (!$obj->src) {
          continue;
        }

        // Build href
        $href = $wp_styles->_css_href($obj->src, $obj->ver, $handle);
        if (!$href) {
          continue;
        }

        // Get media
        $media = isset($obj->args) ? esc_attr($obj->args) : 'all';

        // Output the link tag
        $rel = isset($obj->extra['alt']) && $obj->extra['alt'] ? 'alternate stylesheet' : 'stylesheet';
        $title = isset($obj->extra['title']) ? "title='" . esc_attr($obj->extra['title']) . "'" : '';

        // Check for conditional comments
        if (isset($obj->extra['conditional']) && $obj->extra['conditional']) {
          echo "<!--[if {$obj->extra['conditional']}]>\n";
        }

        echo "<link rel='{$rel}' id='{$handle}-css' {$title} href='{$href}' type='text/css' media='{$media}' />\n";

        if (isset($obj->extra['conditional']) && $obj->extra['conditional']) {
          echo "<![endif]-->\n";
        }
      }
    }
  }

  // Only add action if the function exists (i.e., WordPress is bootstrapped)
  if (function_exists('add_action')) {
    add_action('wp_head', 'wp4bd_manual_print_styles', 999);
  }
}

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

  if ($debug_mode) {
    echo "<!-- DEBUG: About to manually include header/template/footer -->\n";
    echo "<!-- DEBUG: Template file: {$template_file} -->\n";
  }

  // MANUAL APPROACH: Include header, template, footer directly
  // This bypasses get_header()/get_footer() to avoid any issues with those functions
  $template_dir = get_template_directory();

  // Include header.php
  $header_file = $template_dir . '/header.php';
  if (file_exists($header_file)) {
    if ($debug_mode) {
      echo "<!-- DEBUG: Including header.php from {$header_file} -->\n";
    }
    include $header_file;
  }

  // Include the main template (but it will call get_header/footer which we skip)
  // Actually, let's just include it normally and see what happens
  include $template_file;

  // Include footer.php
  $footer_file = $template_dir . '/footer.php';
  if (file_exists($footer_file)) {
    if ($debug_mode) {
      echo "<!-- DEBUG: Including footer.php from {$footer_file} -->\n";
    }
    include $footer_file;
  }

  if ($debug_mode) {
    echo "<!-- DEBUG: Finished including all template parts -->\n";
  }
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
