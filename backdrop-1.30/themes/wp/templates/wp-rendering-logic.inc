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

// Load functions.php now that plugin.php loads first in wp-bootstrap.php
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
$debug_stages = array();  // New: Track stages

if ($debug_mode) {
  $debug_info['test'] = 'Debug system is active';

  // Stage 1: Show that rendering has started
  $debug_stages[] = array(
    'stage' => 'RENDERING STARTED',
    'description' => 'wp-rendering-logic.inc included',
    'data' => array(
      'WordPress hooks loaded' => function_exists('add_action'),
      'Time' => date('H:i:s'),
    ),
  );
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

    // Stage 2: Backdrop data received (single node)
    $debug_stages[] = array(
      'stage' => 'BACKDROP DATA: Single Node',
      'description' => 'Backdrop provided a single node to render',
      'data' => array(
        'Node ID' => $wp_node->nid,
        'Node Type' => $wp_node->type,
        'Node Title' => $wp_node->title,
        'Node Created' => date('Y-m-d H:i:s', $wp_node->created),
        'Node Status' => $wp_node->status ? 'Published' : 'Unpublished',
        'Has Body' => !empty($wp_node->body),
        'Body Length' => !empty($wp_node->body) ? strlen($wp_node->body['und'][0]['value']) : 0,
      ),
    );
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
    if ($debug_mode) {
      // Stage 3: WP_Post object created
      $debug_stages[] = array(
        'stage' => 'WP_POST CREATED',
        'description' => 'Backdrop node converted to WordPress WP_Post object',
        'data' => array(
          'WP_Post ID' => $wp_post->ID,
          'WP_Post Title' => $wp_post->post_title,
          'WP_Post Type' => $wp_post->post_type,
          'WP_Post Status' => $wp_post->post_status,
          'WP_Post Date' => $wp_post->post_date,
          'Content Length' => strlen($wp_post->post_content),
          'Excerpt Length' => strlen($wp_post->post_excerpt),
        ),
      );
    }

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
      // Stage 4: WP_Query initialized
      $debug_stages[] = array(
        'stage' => 'WP_QUERY INITIALIZED (Single)',
        'description' => 'WordPress query object set up for single post view',
        'data' => array(
          'post_count' => $wp_query->post_count,
          'found_posts' => $wp_query->found_posts,
          'is_single' => $wp_query->is_single,
          'is_singular' => $wp_query->is_singular,
          'queried_object_id' => $wp_query->queried_object_id,
          '$GLOBALS[post] set' => isset($GLOBALS['post']),
          '$GLOBALS[wp_query] set' => isset($GLOBALS['wp_query']),
        ),
      );
    }

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

    // Stage 2: Backdrop querying for posts
    $debug_stages[] = array(
      'stage' => 'BACKDROP DATA: Archive Query',
      'description' => 'Querying Backdrop database for published posts',
      'data' => array(
        'Query type' => 'Archive/Listing',
        'Page type' => backdrop_is_front_page() ? 'FRONT PAGE' : 'Other',
        'Node type' => 'post',
        'Status' => 'Published (1)',
        'Order by' => 'created DESC',
        'Limit' => 10,
      ),
    );
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

    // Stage 3: Show each Backdrop node loaded
    foreach ($nids as $idx => $nid) {
      $debug_stages[] = array(
        'stage' => 'BACKDROP NODE #' . ($idx + 1),
        'description' => 'Backdrop node loaded from database',
        'data' => array(
          'Node ID (nid)' => $nid,
          'Position in query' => $idx + 1,
        ),
      );
    }
  }

  // Convert all nodes to WP_Post objects
  $wp_posts = array();
  $nodes = array();
  if (!empty($nids)) {
    $nodes = node_load_multiple($nids);

    foreach ($nodes as $node) {
      if ($debug_mode) {
        // Show full node data before conversion
        $debug_stages[] = array(
          'stage' => 'BACKDROP NODE FULL DATA',
          'description' => 'Node #' . $node->nid . ': ' . $node->title,
          'data' => array(
            'NID' => $node->nid,
            'Type' => $node->type,
            'Title' => $node->title,
            'Status' => $node->status ? 'Published' : 'Unpublished',
            'Created' => date('Y-m-d H:i:s', $node->created),
            'Changed' => date('Y-m-d H:i:s', $node->changed),
            'Author UID' => $node->uid,
            'Body exists' => !empty($node->body),
            'Body length' => !empty($node->body) ? strlen($node->body['und'][0]['value']) : 0,
          ),
        );
      }

      $wp_post = wp4bd_node_to_post($node);
      if ($wp_post) {
        $wp_posts[] = $wp_post;

        if ($debug_mode) {
          // Show WP_Post after conversion
          $debug_stages[] = array(
            'stage' => 'WP_POST CONVERTED',
            'description' => 'Node #' . $node->nid . ' → WP_Post #' . $wp_post->ID,
            'data' => array(
              'WP_Post ID' => $wp_post->ID,
              'WP_Post Title' => $wp_post->post_title,
              'WP_Post Type' => $wp_post->post_type,
              'WP_Post Status' => $wp_post->post_status,
              'WP_Post Date' => $wp_post->post_date,
              'WP_Post Author' => $wp_post->post_author,
              'Content length' => strlen($wp_post->post_content),
              'Excerpt length' => strlen($wp_post->post_excerpt),
            ),
          );
        }
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
    // Stage: WP_Query initialized for archive
    $debug_stages[] = array(
      'stage' => 'WP_QUERY INITIALIZED (Archive)',
      'description' => 'WordPress query object set up for multiple posts',
      'data' => array(
        'post_count' => $wp_query->post_count,
        'found_posts' => $wp_query->found_posts,
        'max_num_pages' => $wp_query->max_num_pages,
        'is_single' => $wp_query->is_single ? 'YES' : 'NO',
        'is_home' => $wp_query->is_home ? 'YES' : 'NO',
        'is_front_page' => $wp_query->is_front_page ? 'YES' : 'NO',
        '$GLOBALS[wp_query] set' => isset($GLOBALS['wp_query']) ? 'YES' : 'NO',
        'Posts in query' => count($wp_query->posts),
      ),
    );
  }

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
// Track CSS Files Before Template Rendering
// ============================================================================

if ($debug_mode) {
  // Stage: CSS Files Enqueued
  // This runs BEFORE wp_enqueue_scripts fires (which happens in header.php)
  // We'll add another stage after wp_head to show what CSS was actually queued
  $debug_stages[] = array(
    'stage' => 'PRE-TEMPLATE CSS CHECK',
    'description' => 'Checking CSS state before template rendering',
    'data' => array(
      'wp_styles exists' => isset($GLOBALS['wp_styles']) ? 'YES' : 'NO',
      'wp_enqueue_style exists' => function_exists('wp_enqueue_style') ? 'YES' : 'NO',
      'Note' => 'CSS will be enqueued during header.php execution',
    ),
  );
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

  // Track CSS files after wp_head completes
  if (!function_exists('wp4bd_track_css_after_head')) {
    function wp4bd_track_css_after_head() {
      global $wp_styles, $debug_stages;

      if (!isset($wp_styles)) {
        return;
      }

      $css_files = array();
      if (!empty($wp_styles->done)) {
        foreach ($wp_styles->done as $handle) {
          if (isset($wp_styles->registered[$handle])) {
            $obj = $wp_styles->registered[$handle];
            if ($obj->src) {
              $href = $wp_styles->_css_href($obj->src, $obj->ver, $handle);
              $media = isset($obj->args) ? $obj->args : 'all';
              $css_files[] = array(
                'handle' => $handle,
                'src' => $obj->src,
                'href' => $href,
                'ver' => $obj->ver,
                'media' => $media,
              );
            }
          }
        }
      }

      // Add stage showing all CSS files
      $debug_stages[] = array(
        'stage' => 'CSS FILES ENQUEUED',
        'description' => 'CSS files queued by WordPress theme after wp_head',
        'data' => array(
          'Total CSS files' => count($css_files),
          'Files' => $css_files,
        ),
      );
    }
    // Run this at the very end of wp_head, after manual styles print
    add_action('wp_head', 'wp4bd_track_css_after_head', 1000);
  }

  // Track The Loop iterations
  if (!function_exists('wp4bd_track_loop_start')) {
    function wp4bd_track_loop_start() {
      global $debug_stages;
      $debug_stages[] = array(
        'stage' => 'THE LOOP: START',
        'description' => 'have_posts() called - The Loop beginning',
        'data' => array(
          'Time' => date('H:i:s.u'),
        ),
      );
    }
  }

  if (!function_exists('wp4bd_track_loop_post')) {
    function wp4bd_track_loop_post($post) {
      global $debug_stages, $wp_query;
      $debug_stages[] = array(
        'stage' => 'THE LOOP: POST #' . ($wp_query->current_post + 1),
        'description' => 'the_post() called - Setting up post #' . $post->ID,
        'data' => array(
          'Post ID' => $post->ID,
          'Post Title' => $post->post_title,
          'Post Type' => $post->post_type,
          'Loop iteration' => $wp_query->current_post + 1,
          'Total posts' => $wp_query->post_count,
        ),
      );
      return $post;
    }
    add_filter('the_post', 'wp4bd_track_loop_post');
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

      // Stage: Header execution
      $debug_stages[] = array(
        'stage' => 'HEADER EXECUTION',
        'description' => 'Including WordPress theme header.php',
        'data' => array(
          'Header file' => $header_file,
          'File exists' => 'YES',
          'Template directory' => $template_dir,
          'Theme' => defined('WP2BD_ACTIVE_THEME') ? WP2BD_ACTIVE_THEME : 'unknown',
          'Time' => date('H:i:s'),
        ),
      );
    }
    include $header_file;
  }

  if ($debug_mode) {
    // Stage: Main template execution
    $debug_stages[] = array(
      'stage' => 'TEMPLATE EXECUTION',
      'description' => 'Including main WordPress template file',
      'data' => array(
        'Template file' => $template_file,
        'Template name' => basename($template_file),
        'Is single' => !empty($wp_post) ? 'YES' : 'NO',
        'Is archive' => empty($wp_post) ? 'YES' : 'NO',
        'Time' => date('H:i:s'),
      ),
    );
  }

  // Include the main template (but it will call get_header/footer which we skip)
  // Actually, let's just include it normally and see what happens
  include $template_file;

  // Include footer.php
  $footer_file = $template_dir . '/footer.php';
  if (file_exists($footer_file)) {
    if ($debug_mode) {
      echo "<!-- DEBUG: Including footer.php from {$footer_file} -->\n";

      // Stage: Footer execution
      $debug_stages[] = array(
        'stage' => 'FOOTER EXECUTION',
        'description' => 'Including WordPress theme footer.php',
        'data' => array(
          'Footer file' => $footer_file,
          'File exists' => 'YES',
          'Time' => date('H:i:s'),
        ),
      );
    }
    include $footer_file;
  }

  if ($debug_mode) {
    echo "<!-- DEBUG: Finished including all template parts -->\n";

    // Stage: Template rendering complete
    $debug_stages[] = array(
      'stage' => 'TEMPLATE RENDERING COMPLETE',
      'description' => 'All WordPress template files have been included',
      'data' => array(
        'Header included' => file_exists($header_file) ? 'YES' : 'NO',
        'Template included' => 'YES',
        'Footer included' => file_exists($footer_file) ? 'YES' : 'NO',
        'Time' => date('H:i:s'),
      ),
    );
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

if ($debug_mode && !empty($debug_stages)) {
  // Add final stage showing output capture complete
  $debug_stages[] = array(
    'stage' => 'OUTPUT CAPTURED',
    'description' => 'WordPress output captured and ready to return to Backdrop',
    'data' => array(
      'Output length (bytes)' => strlen($wordpress_output),
      'Output length (KB)' => round(strlen($wordpress_output) / 1024, 2),
      'Has HTML' => strpos($wordpress_output, '<html') !== false ? 'YES' : 'NO',
      'Has body tag' => strpos($wordpress_output, '<body') !== false ? 'YES' : 'NO',
      'Time' => date('H:i:s'),
    ),
  );

  // Build debug display with stage-by-stage breakdown
  $debug_html = '<div style="background: #f0f0f0; border: 3px solid #c00; padding: 20px; margin: 20px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif; font-size: 14px; line-height: 1.4;">';
  $debug_html .= '<h1 style="margin-top: 0; color: #c00; font-size: 24px; border-bottom: 3px solid #c00; padding-bottom: 10px;">WP4BD Debug: Data Flow Through System</h1>';
  $debug_html .= '<p style="color: #666; margin-bottom: 20px;">This shows each stage of data transformation from Backdrop to WordPress and back.</p>';

  $stage_num = 0;
  foreach ($debug_stages as $stage_info) {
    $stage_num++;

    // Stage header
    $debug_html .= '<div style="background: white; border-left: 5px solid #0066cc; margin-bottom: 20px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
    $debug_html .= '<h3 style="margin: 0 0 5px 0; color: #0066cc; font-size: 16px; font-weight: bold;">';
    $debug_html .= 'Stage ' . $stage_num . ': ' . htmlspecialchars($stage_info['stage']);
    $debug_html .= '</h3>';
    $debug_html .= '<p style="margin: 0 0 15px 0; color: #666; font-size: 13px; font-style: italic;">';
    $debug_html .= htmlspecialchars($stage_info['description']);
    $debug_html .= '</p>';

    // Stage data as table
    if (!empty($stage_info['data'])) {
      $debug_html .= '<table style="width: 100%; border-collapse: collapse; font-size: 13px;">';

      foreach ($stage_info['data'] as $key => $value) {
        $debug_html .= '<tr style="border-bottom: 1px solid #eee;">';
        $debug_html .= '<td style="padding: 8px; font-weight: bold; color: #333; width: 200px; vertical-align: top;">';
        $debug_html .= htmlspecialchars($key);
        $debug_html .= '</td>';
        $debug_html .= '<td style="padding: 8px; color: #666; vertical-align: top;">';

        // Format value based on type
        if (is_array($value)) {
          // Special handling for arrays
          if ($key === 'Files') {
            // CSS files - show each file as a row
            $debug_html .= '<table style="width: 100%; border: 1px solid #ddd; margin: 5px 0;">';
            $debug_html .= '<tr style="background: #f9f9f9; font-weight: bold;">';
            $debug_html .= '<td style="padding: 5px;">Handle</td>';
            $debug_html .= '<td style="padding: 5px;">Source</td>';
            $debug_html .= '<td style="padding: 5px;">Media</td>';
            $debug_html .= '<td style="padding: 5px;">Version</td>';
            $debug_html .= '</tr>';
            foreach ($value as $file) {
              $debug_html .= '<tr>';
              $debug_html .= '<td style="padding: 5px; border-top: 1px solid #eee;">' . htmlspecialchars($file['handle']) . '</td>';
              $debug_html .= '<td style="padding: 5px; border-top: 1px solid #eee; font-size: 11px; word-break: break-all;">' . htmlspecialchars($file['src']) . '</td>';
              $debug_html .= '<td style="padding: 5px; border-top: 1px solid #eee;">' . htmlspecialchars($file['media']) . '</td>';
              $debug_html .= '<td style="padding: 5px; border-top: 1px solid #eee;">' . htmlspecialchars($file['ver']) . '</td>';
              $debug_html .= '</tr>';
            }
            $debug_html .= '</table>';
          } else {
            // Other arrays - show as formatted list
            $debug_html .= '<pre style="background: #f9f9f9; padding: 8px; margin: 0; overflow-x: auto; font-size: 12px; border: 1px solid #ddd;">';
            $debug_html .= htmlspecialchars(print_r($value, TRUE));
            $debug_html .= '</pre>';
          }
        } elseif (is_bool($value)) {
          $debug_html .= '<span style="font-weight: bold; color: ' . ($value ? '#060' : '#c00') . ';">';
          $debug_html .= $value ? 'TRUE' : 'FALSE';
          $debug_html .= '</span>';
        } else {
          $debug_html .= htmlspecialchars((string)$value);
        }

        $debug_html .= '</td>';
        $debug_html .= '</tr>';
      }

      $debug_html .= '</table>';
    }

    $debug_html .= '</div>';
  }

  // Summary footer
  $debug_html .= '<div style="background: #fff; border: 2px solid #060; padding: 15px; margin-top: 20px;">';
  $debug_html .= '<h3 style="margin: 0 0 10px 0; color: #060;">Summary</h3>';
  $debug_html .= '<p style="margin: 0; color: #333;">Total stages: ' . $stage_num . '</p>';
  $debug_html .= '<p style="margin: 5px 0 0 0; color: #666; font-size: 12px;">Data successfully flowed from Backdrop → WordPress → Rendered HTML → Back to Backdrop</p>';
  $debug_html .= '</div>';

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
