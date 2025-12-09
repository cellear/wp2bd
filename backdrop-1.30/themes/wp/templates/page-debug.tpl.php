<?php
/**
 * @file
 * Debug-first WordPress rendering template
 *
 * This template shows data flow through the system with progressive detail levels.
 * Start with placeholder stages, then implement real data loading incrementally.
 */

// Load debug helper functions
require_once BACKDROP_ROOT . '/modules/wp_content/wp4bd_debug.inc';

// Initialize debugging
wp4bd_debug_init();

// ============================================================================
// STAGE 1: BACKDROP QUERY
// ============================================================================
wp4bd_debug_stage_start('Stage 1: Backdrop Query');

// Query promoted nodes from Backdrop database
$query = db_select('node', 'n')
  ->fields('n', ['nid', 'title', 'type', 'status', 'created', 'changed', 'uid', 'sticky'])
  ->condition('n.status', 1)  // Published only
  ->condition('n.promote', 1)  // Promoted to front page
  ->orderBy('n.sticky', 'DESC')
  ->orderBy('n.created', 'DESC')
  ->range(0, 10);  // Limit to 10 posts

wp4bd_debug_log('Stage 1: Backdrop Query', 'SQL Query', $query->__toString());

// Execute query and get node IDs
$nids = $query->execute()->fetchCol();
wp4bd_debug_log('Stage 1: Backdrop Query', 'Node IDs Found', $nids);

// Load full node objects
$nodes = node_load_multiple($nids);
wp4bd_debug_log('Stage 1: Backdrop Query', 'Loaded Nodes', $nodes);

// Log summary info
wp4bd_debug_log('Stage 1: Backdrop Query', 'Total Nodes', count($nodes));

wp4bd_debug_stage_end('Stage 1: Backdrop Query');

// ============================================================================
// STAGE 2: TRANSFORM TO WP_POST
// ============================================================================
wp4bd_debug_stage_start('Stage 2: Transform Backdrop → WordPress');

// Load real WordPress WP_Post class
// With ddev docroot set to 'backdrop-1.30', wordpress-4.9 is a sibling at project root
$project_root = dirname(BACKDROP_ROOT);
$wp_post_class_file = $project_root . '/wordpress-4.9/wp-includes/class-wp-post.php';
if (!class_exists('WP_Post') && file_exists($wp_post_class_file)) {
  require_once $wp_post_class_file;
  wp4bd_debug_log('Stage 2: Transform Backdrop → WordPress', 'WP_Post Class', 'Loaded from WordPress 4.9');
} else {
  wp4bd_debug_log('Stage 2: Transform Backdrop → WordPress', 'WP_Post Class', 'Already loaded or file not found at: ' . $wp_post_class_file);
}

// Transform each Backdrop node to WP_Post object
$posts = [];
$transform_errors = [];

foreach ($nodes as $node) {
  try {
    // Create a stdClass object with all WP_Post properties
    $post_data = new stdClass();

    // Map Backdrop node to WordPress post properties
    $post_data->ID = (int) $node->nid;
    $post_data->post_author = (string) $node->uid;
    $post_data->post_date = date('Y-m-d H:i:s', $node->created);
    $post_data->post_date_gmt = gmdate('Y-m-d H:i:s', $node->created);

    // Get node body content
    $post_content = '';
    if (isset($node->body) && !empty($node->body)) {
      // Handle both array and object formats
      if (is_array($node->body) && isset($node->body[LANGUAGE_NONE][0]['value'])) {
        $post_content = $node->body[LANGUAGE_NONE][0]['value'];
      } elseif (is_object($node->body) && isset($node->body->{LANGUAGE_NONE}[0]['value'])) {
        $post_content = $node->body->{LANGUAGE_NONE}[0]['value'];
      }
    }
    $post_data->post_content = $post_content;

    $post_data->post_title = isset($node->title) ? $node->title : '';
    $post_data->post_excerpt = ''; // Backdrop doesn't have built-in excerpt
    $post_data->post_status = ($node->status == 1) ? 'publish' : 'draft';
    $post_data->comment_status = ($node->comment == 2) ? 'open' : 'closed';
    $post_data->ping_status = 'closed'; // Backdrop doesn't have ping
    $post_data->post_password = '';

    // Use path alias as post_name, or generate from title
    $post_name = '';
    if (function_exists('backdrop_get_path_alias')) {
      $alias = backdrop_get_path_alias('node/' . $node->nid);
      if ($alias && $alias != 'node/' . $node->nid) {
        $post_name = basename($alias);
      }
    }
    if (empty($post_name)) {
      // Generate slug from title
      $post_name = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $node->title));
      $post_name = trim($post_name, '-');
    }
    $post_data->post_name = $post_name;

    $post_data->to_ping = '';
    $post_data->pinged = '';
    $post_data->post_modified = date('Y-m-d H:i:s', $node->changed);
    $post_data->post_modified_gmt = gmdate('Y-m-d H:i:s', $node->changed);
    $post_data->post_content_filtered = '';
    $post_data->post_parent = 0; // Backdrop nodes don't have hierarchy by default

    // Generate GUID (unique identifier)
    global $base_url;
    $post_data->guid = $base_url . '/node/' . $node->nid;

    $post_data->menu_order = 0;
    $post_data->post_type = 'post'; // Map all nodes to 'post' for now
    $post_data->post_mime_type = '';
    $post_data->comment_count = 0; // Would need to query comment table
    $post_data->filter = 'raw';

    // Create WP_Post object
    $wp_post = new WP_Post($post_data);
    $posts[] = $wp_post;

  } catch (Exception $e) {
    $transform_errors[] = "Node {$node->nid}: " . $e->getMessage();
  }
}

// Log results
wp4bd_debug_log('Stage 2: Transform Backdrop → WordPress', 'Total Nodes Input', count($nodes));
wp4bd_debug_log('Stage 2: Transform Backdrop → WordPress', 'WP_Post Objects Created', count($posts));
wp4bd_debug_log('Stage 2: Transform Backdrop → WordPress', 'Transformation Errors', count($transform_errors));

if (!empty($transform_errors)) {
  wp4bd_debug_log('Stage 2: Transform Backdrop → WordPress', 'Error Details', $transform_errors);
}

// Log sample post data (first post)
if (!empty($posts)) {
  $sample = $posts[0];
  wp4bd_debug_log('Stage 2: Transform Backdrop → WordPress', 'Sample Post', $sample);
  wp4bd_debug_log('Stage 2: Transform Backdrop → WordPress', 'Sample: ID', $sample->ID);
  wp4bd_debug_log('Stage 2: Transform Backdrop → WordPress', 'Sample: Title', $sample->post_title);
  wp4bd_debug_log('Stage 2: Transform Backdrop → WordPress', 'Sample: Content Length', strlen($sample->post_content) . ' characters');
  wp4bd_debug_log('Stage 2: Transform Backdrop → WordPress', 'Sample: Post Date', $sample->post_date);
  wp4bd_debug_log('Stage 2: Transform Backdrop → WordPress', 'Sample: Post Type', $sample->post_type);
}

wp4bd_debug_stage_end('Stage 2: Transform Backdrop → WordPress');

// ============================================================================
// STAGE 3: POPULATE WP_QUERY
// ============================================================================
wp4bd_debug_stage_start('Stage 3: Create & Populate WP_Query');

// Load real WordPress WP_Query class
// With ddev docroot set to 'backdrop-1.30', wordpress-4.9 is a sibling at project root
$project_root = dirname(BACKDROP_ROOT);
$wp_query_class_file = $project_root . '/wordpress-4.9/wp-includes/class-wp-query.php';
if (!class_exists('WP_Query') && file_exists($wp_query_class_file)) {
  require_once $wp_query_class_file;
  wp4bd_debug_log('Stage 3: Create & Populate WP_Query', 'WP_Query Class', 'Loaded from WordPress 4.9');
} else {
  wp4bd_debug_log('Stage 3: Create & Populate WP_Query', 'WP_Query Class', 'Already loaded or file not found at: ' . $wp_query_class_file);
}

// Create a new WP_Query instance
$wp_query = new WP_Query();

// Populate with posts from Stage 2
$wp_query->posts = $posts;
$wp_query->post_count = count($posts);
$wp_query->found_posts = count($posts);
$wp_query->max_num_pages = 1; // Single page for now

// Set initial loop state
$wp_query->current_post = -1;
$wp_query->in_the_loop = false;
$wp_query->post = null;

// Set query flags based on current page
// For now, assume this is the home page (blog listing)
$wp_query->is_home = true;
$wp_query->is_posts_page = true;
$wp_query->is_single = false;
$wp_query->is_page = false;
$wp_query->is_archive = false;
$wp_query->is_404 = false;
$wp_query->is_search = false;
$wp_query->is_category = false;
$wp_query->is_tag = false;
$wp_query->is_tax = false;
$wp_query->is_author = false;
$wp_query->is_date = false;
$wp_query->is_year = false;
$wp_query->is_month = false;
$wp_query->is_day = false;
$wp_query->is_time = false;
$wp_query->is_attachment = false;
$wp_query->is_singular = false;
$wp_query->is_feed = false;
$wp_query->is_comment_feed = false;
$wp_query->is_trackback = false;
$wp_query->is_preview = false;
$wp_query->is_paged = false;
$wp_query->is_admin = false;
$wp_query->is_robots = false;
$wp_query->is_post_type_archive = false;
$wp_query->is_embed = false;

// Set query vars
$wp_query->query = array();
$wp_query->query_vars = array(
  'posts_per_page' => 10,
  'paged' => 1,
  'orderby' => 'date',
  'order' => 'DESC',
);

// Set queried object (null for home page)
$wp_query->queried_object = null;
$wp_query->queried_object_id = null;

// Set as global query
global $wp_the_query;
$wp_the_query = $wp_query;

// Log results
wp4bd_debug_log('Stage 3: Create & Populate WP_Query', 'WP_Query Created', 'Success');
wp4bd_debug_log('Stage 3: Create & Populate WP_Query', 'Posts Count', $wp_query->post_count);
wp4bd_debug_log('Stage 3: Create & Populate WP_Query', 'Found Posts', $wp_query->found_posts);
wp4bd_debug_log('Stage 3: Create & Populate WP_Query', 'Max Num Pages', $wp_query->max_num_pages);
wp4bd_debug_log('Stage 3: Create & Populate WP_Query', 'Query Flags', array(
  'is_home' => $wp_query->is_home,
  'is_single' => $wp_query->is_single,
  'is_page' => $wp_query->is_page,
  'is_archive' => $wp_query->is_archive,
  'is_404' => $wp_query->is_404,
));
wp4bd_debug_log('Stage 3: Create & Populate WP_Query', 'WP_Query Object', $wp_query);

wp4bd_debug_stage_end('Stage 3: Create & Populate WP_Query');

// ============================================================================
// STAGE 4: LOAD WORDPRESS CORE
// ============================================================================
wp4bd_debug_stage_start('Stage 4: Load WordPress Core Files');

// Define WordPress constants
// With ddev docroot set to 'backdrop-1.30', BACKDROP_ROOT points to the Backdrop installation
// and wordpress-4.9 is a sibling directory at the project root level.
// Go up one level from BACKDROP_ROOT to reach project root where wordpress-4.9 is located.
$project_root = dirname(BACKDROP_ROOT);  // Go up 1 level from backdrop-1.30 to project root
if (!defined('ABSPATH')) {
  define('ABSPATH', $project_root . '/wordpress-4.9/');
}

// Log the path calculation for debugging
wp4bd_debug_log('Stage 4: Load WordPress Core Files', '__DIR__', __DIR__);
wp4bd_debug_log('Stage 4: Load WordPress Core Files', 'BACKDROP_ROOT', BACKDROP_ROOT);
wp4bd_debug_log('Stage 4: Load WordPress Core Files', 'Calculated Project Root', $project_root);
wp4bd_debug_log('Stage 4: Load WordPress Core Files', 'Project Root Exists?', is_dir($project_root) ? 'YES' : 'NO');
wp4bd_debug_log('Stage 4: Load WordPress Core Files', 'WordPress Dir Exists?', is_dir($project_root . '/wordpress-4.9') ? 'YES' : 'NO');

if (!defined('WPINC')) {
  define('WPINC', 'wp-includes');
}
if (!defined('WP_CONTENT_DIR')) {
  define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
}

wp4bd_debug_log('Stage 4: Load WordPress Core Files', 'ABSPATH', ABSPATH);
wp4bd_debug_log('Stage 4: Load WordPress Core Files', 'ABSPATH Exists?', is_dir(ABSPATH) ? 'YES' : 'NO');
wp4bd_debug_log('Stage 4: Load WordPress Core Files', 'WPINC', WPINC);
wp4bd_debug_log('Stage 4: Load WordPress Core Files', 'WPINC Path', ABSPATH . WPINC);
wp4bd_debug_log('Stage 4: Load WordPress Core Files', 'WPINC Exists?', is_dir(ABSPATH . WPINC) ? 'YES' : 'NO');
wp4bd_debug_log('Stage 4: Load WordPress Core Files', 'WP_CONTENT_DIR', WP_CONTENT_DIR);

// Core WordPress files needed for The Loop and theme functionality
$wp_core_files = [
  'query.php',           // Query functions (have_posts, the_post, etc.)
  'post.php',            // Post functions (get_post, etc.)
  'post-template.php',   // Template tags (the_title, the_content, etc.)
  'general-template.php',// General template tags
  'link-template.php',   // Link template tags (get_permalink, etc.)
  'formatting.php',      // Formatting functions (wpautop, etc.)
  'plugin.php',          // Plugin API (add_action, add_filter, etc.)
  'l10n.php',            // Localization functions (__(), _e(), etc.)
  'kses.php',            // HTML filtering
  'load.php',            // Load functions
  'functions.wp-styles.php',  // Style enqueue functions (wp_enqueue_style, etc.)
  'functions.wp-scripts.php', // Script enqueue functions (wp_enqueue_script, etc.)
];

$loaded_files = [];
$failed_files = [];
$total_size = 0;

foreach ($wp_core_files as $file) {
  $filepath = ABSPATH . WPINC . '/' . $file;

  // Skip files that would redeclare symbols we already provide.
  if ($file === 'plugin.php' && function_exists('add_filter')) {
    $loaded_files[$file] = 'skipped (hooks already loaded)';
    wp4bd_debug_log('Stage 4: Load WordPress Core Files', "Skipped: $file", 'add_filter already defined');
    continue;
  }
  if ($file === 'post.php' && function_exists('get_post')) {
    $loaded_files[$file] = 'skipped (get_post already loaded)';
    wp4bd_debug_log('Stage 4: Load WordPress Core Files', "Skipped: $file", 'get_post already defined');
    continue;
  }
  if ($file === 'query.php' && (class_exists('WP_Query') || function_exists('have_posts'))) {
    $loaded_files[$file] = 'skipped (query functions already loaded)';
    wp4bd_debug_log('Stage 4: Load WordPress Core Files', "Skipped: $file", 'WP_Query/have_posts already defined');
    continue;
  }
  if ($file === 'general-template.php' && function_exists('wp_head')) {
    $loaded_files[$file] = 'skipped (wp_head/wp_footer already loaded)';
    wp4bd_debug_log('Stage 4: Load WordPress Core Files', "Skipped: $file", 'general-template hooks already defined');
    continue;
  }
  if ($file === 'post-template.php' && function_exists('the_content')) {
    $loaded_files[$file] = 'skipped (template tags already loaded)';
    wp4bd_debug_log('Stage 4: Load WordPress Core Files', "Skipped: $file", 'post-template tags already defined');
    continue;
  }
  if ($file === 'link-template.php' && function_exists('the_permalink')) {
    $loaded_files[$file] = 'skipped (link-template already loaded)';
    wp4bd_debug_log('Stage 4: Load WordPress Core Files', "Skipped: $file", 'the_permalink already defined');
    continue;
  }
  if ($file === 'formatting.php' && function_exists('sanitize_html_class')) {
    $loaded_files[$file] = 'skipped (formatting already loaded)';
    wp4bd_debug_log('Stage 4: Load WordPress Core Files', "Skipped: $file", 'formatting helpers already defined');
    continue;
  }
  if ($file === 'l10n.php' && function_exists('__')) {
    $loaded_files[$file] = 'skipped (l10n already loaded)';
    wp4bd_debug_log('Stage 4: Load WordPress Core Files', "Skipped: $file", '__ already defined');
    continue;
  }
  if ($file === 'load.php') {
    $loaded_files[$file] = 'skipped (load.php conflicts with Backdrop bootstrap)';
    wp4bd_debug_log('Stage 4: Load WordPress Core Files', "Skipped: $file", 'timer_start already defined');
    continue;
  }
  if ($file === 'functions.wp-styles.php' && function_exists('wp_print_styles')) {
    $loaded_files[$file] = 'skipped (wp-styles already loaded)';
    wp4bd_debug_log('Stage 4: Load WordPress Core Files', "Skipped: $file", 'wp_print_styles already defined');
    continue;
  }
  if ($file === 'functions.wp-scripts.php' && function_exists('wp_print_scripts')) {
    $loaded_files[$file] = 'skipped (wp-scripts already loaded)';
    wp4bd_debug_log('Stage 4: Load WordPress Core Files', "Skipped: $file", 'wp_print_scripts already defined');
    continue;
  }

  if (file_exists($filepath)) {
    try {
      require_once $filepath;
      $size = filesize($filepath);
      $total_size += $size;
      $loaded_files[$file] = $size;
      wp4bd_debug_log('Stage 4: Load WordPress Core Files', "Loaded: $file", round($size / 1024, 2) . ' KB');
    } catch (Exception $e) {
      $failed_files[$file] = $e->getMessage();
      wp4bd_debug_log('Stage 4: Load WordPress Core Files', "Failed: $file", $e->getMessage());
    }
  } else {
    $failed_files[$file] = 'File not found';
    wp4bd_debug_log('Stage 4: Load WordPress Core Files', "Missing: $file", 'File not found');
  }
}

// Log summary
wp4bd_debug_log('Stage 4: Load WordPress Core Files', 'Total Files Loaded', count($loaded_files));
wp4bd_debug_log('Stage 4: Load WordPress Core Files', 'Total Size', round($total_size / 1024, 2) . ' KB');
wp4bd_debug_log('Stage 4: Load WordPress Core Files', 'Failed Files', count($failed_files));

if (!empty($failed_files)) {
  wp4bd_debug_log('Stage 4: Load WordPress Core Files', 'Failed Details', $failed_files);
}

// Verify key functions are available
$key_functions = ['have_posts', 'the_post', 'the_title', 'the_content', 'get_permalink'];
$available_functions = [];
$missing_functions = [];

foreach ($key_functions as $func) {
  if (function_exists($func)) {
    $available_functions[] = $func;
  } else {
    $missing_functions[] = $func;
  }
}

wp4bd_debug_log('Stage 4: Load WordPress Core Files', 'Available Functions', $available_functions);
if (!empty($missing_functions)) {
  wp4bd_debug_log('Stage 4: Load WordPress Core Files', 'Missing Functions', $missing_functions);
}

wp4bd_debug_stage_end('Stage 4: Load WordPress Core Files');

// ============================================================================
// STAGE 5: TEST THE LOOP
// ============================================================================
wp4bd_debug_stage_start('Stage 5: Test The Loop');

// Set the global $post variable (required by WordPress template tags)
global $post;

// Test 1: Check if have_posts() works
$has_posts = have_posts();
wp4bd_debug_log('Stage 5: Test The Loop', 'have_posts() result', $has_posts ? 'true' : 'false');
wp4bd_debug_log('Stage 5: Test The Loop', 'Initial current_post', $wp_query->current_post);
wp4bd_debug_log('Stage 5: Test The Loop', 'Initial $post', isset($post) ? get_class($post) : 'null');

// Test 2: Iterate through first 3 posts (or all if fewer)
$loop_iterations = [];
$max_iterations = min(3, $wp_query->post_count);

for ($i = 0; $i < $max_iterations; $i++) {
  if (have_posts()) {
    // Call the_post() to advance the loop
    the_post();

    // Capture current state
    $iteration = [
      'iteration' => $i + 1,
      'current_post' => $wp_query->current_post,
      'post_ID' => $post->ID,
      'post_title' => $post->post_title,
      'in_the_loop' => $wp_query->in_the_loop,
      'post_type' => $post->post_type,
      'post_date' => $post->post_date,
    ];

    $loop_iterations[] = $iteration;

    // Log this iteration
    wp4bd_debug_log('Stage 5: Test The Loop', "Iteration " . ($i + 1), $iteration);
  }
}

// Log summary of iterations
wp4bd_debug_log('Stage 5: Test The Loop', 'Total Iterations', count($loop_iterations));

// Test 3: Verify loop exhaustion
$has_more_posts = have_posts();
wp4bd_debug_log('Stage 5: Test The Loop', 'have_posts() after loop', $has_more_posts ? 'true' : 'false');
wp4bd_debug_log('Stage 5: Test The Loop', 'Final current_post', $wp_query->current_post);

// Test 4: Test wp_reset_postdata()
if (function_exists('wp_reset_postdata')) {
  $post_before_reset = $post;
  wp_reset_postdata();
  $post_after_reset = $post;

  wp4bd_debug_log('Stage 5: Test The Loop', 'wp_reset_postdata()', 'Called');
  wp4bd_debug_log('Stage 5: Test The Loop', 'Post before reset', $post_before_reset ? $post_before_reset->ID : 'null');
  wp4bd_debug_log('Stage 5: Test The Loop', 'Post after reset', $post_after_reset ? $post_after_reset->ID : 'null');
} else {
  wp4bd_debug_log('Stage 5: Test The Loop', 'wp_reset_postdata()', 'Function not available');
}

// Test 5: Verify $wp_query state
wp4bd_debug_log('Stage 5: Test The Loop', 'Final $wp_query state', [
  'post_count' => $wp_query->post_count,
  'current_post' => $wp_query->current_post,
  'in_the_loop' => $wp_query->in_the_loop,
  'found_posts' => $wp_query->found_posts,
]);

// Test 6: Test template tags work
if (count($loop_iterations) > 0) {
  // Reset to first post to test template tags
  $wp_query->current_post = -1;
  the_post();

  $template_tag_tests = [];

  // Test the_title (with output buffering)
  ob_start();
  the_title();
  $title_output = ob_get_clean();
  $template_tag_tests['the_title()'] = $title_output;

  // Test get_the_title
  if (function_exists('get_the_title')) {
    $template_tag_tests['get_the_title()'] = get_the_title();
  }

  // Test get_permalink
  if (function_exists('get_permalink')) {
    $template_tag_tests['get_permalink()'] = get_permalink();
  }

  wp4bd_debug_log('Stage 5: Test The Loop', 'Template Tag Tests', $template_tag_tests);
}

wp4bd_debug_stage_end('Stage 5: Test The Loop');

// ============================================================================
// STAGE 6: LOAD THEME functions.php & FIRE HOOKS
// ============================================================================
wp4bd_debug_stage_start('Stage 6: Theme functions.php & Hooks');

// Determine active theme and paths
$theme_stage = 'Stage 6: Theme functions.php & Hooks';
$active_theme = defined('WP2BD_ACTIVE_THEME') ? WP2BD_ACTIVE_THEME : null;

if (!$active_theme && function_exists('config')) {
  try {
    $cfg_theme = config('wp_content.settings')->get('active_theme');
    if (!empty($cfg_theme)) {
      $active_theme = $cfg_theme;
    }
  } catch (Exception $e) {
    wp4bd_debug_log($theme_stage, 'Theme Config Error', $e->getMessage());
  }
}

if (empty($active_theme)) {
  $active_theme = 'twentysixteen'; // fallback
}

// Derive theme directories
$theme_dir = defined('WP2BD_ACTIVE_THEME_DIR') ? WP2BD_ACTIVE_THEME_DIR : (BACKDROP_ROOT . '/themes/wp/wp-content/themes/' . $active_theme);
$functions_php = $theme_dir . '/functions.php';

wp4bd_debug_log($theme_stage, 'Active Theme', $active_theme);
wp4bd_debug_log($theme_stage, 'Theme Dir', $theme_dir);
wp4bd_debug_log($theme_stage, 'functions.php Path', $functions_php);

// Load theme functions.php safely
$functions_loaded = false;
if (file_exists($functions_php)) {
  try {
    require_once $functions_php;
    $functions_loaded = true;
    wp4bd_debug_log($theme_stage, 'functions.php', 'Loaded');
  } catch (Throwable $e) {
    wp4bd_debug_log($theme_stage, 'functions.php Error', $e->getMessage());
  }
} else {
  wp4bd_debug_log($theme_stage, 'functions.php', 'Missing');
}

// Fire hooks expected after theme setup
if ($functions_loaded) {
  try {
    do_action('after_setup_theme');
    wp4bd_debug_log($theme_stage, 'after_setup_theme', 'Fired');
  } catch (Throwable $e) {
    wp4bd_debug_log($theme_stage, 'after_setup_theme Error', $e->getMessage());
  }

  try {
    do_action('wp_enqueue_scripts');
    wp4bd_debug_log($theme_stage, 'wp_enqueue_scripts', 'Fired');
  } catch (Throwable $e) {
    wp4bd_debug_log($theme_stage, 'wp_enqueue_scripts Error', $e->getMessage());
  }
}

// Log registered hooks
if (isset($GLOBALS['wp_filter']) && is_array($GLOBALS['wp_filter'])) {
  $hook_summary = [];
  foreach ($GLOBALS['wp_filter'] as $hook_name => $priorities) {
    $count = 0;
    foreach ($priorities as $callbacks) {
      $count += count($callbacks);
    }
    $hook_summary[$hook_name] = $count;
  }
  wp4bd_debug_log($theme_stage, 'Registered Hooks', $hook_summary);
}

wp4bd_debug_stage_end('Stage 6: Theme functions.php & Hooks');

// ============================================================================
// STAGE 7: TEMPLATE HIERARCHY SELECTION
// ============================================================================
wp4bd_debug_stage_start('Stage 7: Template Hierarchy');

$stage7 = 'Stage 7: Template Hierarchy';

// Determine active theme and template dir
$active_theme = defined('WP2BD_ACTIVE_THEME') ? WP2BD_ACTIVE_THEME : null;
if (!$active_theme && function_exists('config')) {
  try {
    $cfg_theme = config('wp_content.settings')->get('active_theme');
    if (!empty($cfg_theme)) {
      $active_theme = $cfg_theme;
    }
  } catch (Exception $e) {
    wp4bd_debug_log($stage7, 'Theme Config Error', $e->getMessage());
  }
}
if (empty($active_theme)) {
  $active_theme = 'twentysixteen'; // fallback
}

$theme_dir = defined('WP2BD_ACTIVE_THEME_DIR')
  ? WP2BD_ACTIVE_THEME_DIR
  : (BACKDROP_ROOT . '/themes/wp/wp-content/themes/' . $active_theme);

// Read query conditionals
$conditionals = array(
  'is_home'   => isset($wp_query->is_home) ? (bool) $wp_query->is_home : false,
  'is_single' => isset($wp_query->is_single) ? (bool) $wp_query->is_single : false,
  'is_page'   => isset($wp_query->is_page) ? (bool) $wp_query->is_page : false,
  'is_archive'=> isset($wp_query->is_archive) ? (bool) $wp_query->is_archive : false,
  'is_404'    => isset($wp_query->is_404) ? (bool) $wp_query->is_404 : false,
  'is_search' => isset($wp_query->is_search) ? (bool) $wp_query->is_search : false,
);
wp4bd_debug_log($stage7, 'Conditionals', $conditionals);

// Build a minimal hierarchy (simplified)
$candidates = array();
if ($conditionals['is_home']) {
  $candidates[] = 'home.php';
}
if ($conditionals['is_single']) {
  $candidates[] = 'single.php';
}
if ($conditionals['is_page']) {
  $candidates[] = 'page.php';
}
if ($conditionals['is_archive']) {
  $candidates[] = 'archive.php';
}
if ($conditionals['is_search']) {
  $candidates[] = 'search.php';
}
if ($conditionals['is_404']) {
  $candidates[] = '404.php';
}
// Fallbacks
$candidates[] = 'index.php';

// Find first existing template
$chosen = null;
$chosen_path = null;
$missing = array();
foreach ($candidates as $tpl) {
  $full = $theme_dir . '/' . $tpl;
  if (file_exists($full)) {
    $chosen = $tpl;
    $chosen_path = $full;
    break;
  } else {
    $missing[] = $tpl;
  }
}

wp4bd_debug_log($stage7, 'Template Candidates', $candidates);

if ($chosen) {
  wp4bd_debug_log($stage7, 'Using Template', $chosen);
  wp4bd_debug_log($stage7, 'Template Path', $chosen_path);
} else {
  wp4bd_debug_log($stage7, 'Template Missing', $missing);
}

wp4bd_debug_stage_end('Stage 7: Template Hierarchy');

// ============================================================================
// STAGE 8: CAPTURE TEMPLATE OUTPUT (BUFFER ONLY)
// ============================================================================
wp4bd_debug_stage_start('Stage 8: Capture Template Output');

$stage8 = 'Stage 8: Capture Template Output';

$captured_html = '';
$captured_len = 0;

if (!empty($chosen_path) && file_exists($chosen_path)) {
  try {
    // Rewind loop to start before including template.
    if (isset($wp_query) && method_exists($wp_query, 'rewind_posts')) {
      $wp_query->rewind_posts();
      $GLOBALS['post'] = null;
      wp4bd_debug_log($stage8, 'Loop Rewound', 'Reset current_post to start');
    }

    // Ensure globals point to our query before include
    $GLOBALS['wp_query'] = $wp_query;
    $GLOBALS['wp_the_query'] = $wp_query;

    // Reset debug counters so template calls are counted from zero
    if (isset($wp_query)) {
      $wp_query->debug_have_posts_calls = 0;
      $wp_query->debug_the_post_calls = 0;
    }

    // Log loop state before include
    if (isset($wp_query)) {
      $pre_state = [
        'post_count' => $wp_query->post_count,
        'current_post' => $wp_query->current_post,
        'posts_array_count' => is_array($wp_query->posts) ? count($wp_query->posts) : 0,
      ];
      if (!empty($wp_query->posts)) {
        $pre_state['first_post'] = [
          'ID' => $wp_query->posts[0]->ID ?? null,
          'post_title' => $wp_query->posts[0]->post_title ?? null,
        ];
      }
      wp4bd_debug_log($stage8, 'Pre-Include Loop State', $pre_state);
    }

    ob_start();
    include $chosen_path;
    $captured_html = ob_get_clean();
    $captured_len = strlen($captured_html);
    wp4bd_debug_log($stage8, 'Template Included', $chosen_path);
    wp4bd_debug_log($stage8, 'Captured HTML Length', $captured_len . ' characters');
    // At debug level 4, also show the captured HTML (escaped) for inspection.
    if (wp4bd_debug_get_level() >= 4 && $captured_html !== '') {
      wp4bd_debug_log($stage8, 'Captured HTML', $captured_html);
    }
  } catch (Throwable $e) {
    // Ensure buffer is cleaned if include threw
    if (ob_get_level() > 0) {
      ob_end_clean();
    }
    wp4bd_debug_log($stage8, 'Template Include Error', $e->getMessage());
  }
} else {
  wp4bd_debug_log($stage8, 'Template Include Skipped', 'No template selected or file missing');
}

// Do NOT print the captured HTML yet.

wp4bd_debug_stage_end('Stage 8: Capture Template Output');

// Log loop state after include to see if template consumed posts
if (isset($wp_query)) {
  $post_include_state = [
    'post_count' => $wp_query->post_count,
    'current_post' => $wp_query->current_post,
    'posts_array_count' => is_array($wp_query->posts) ? count($wp_query->posts) : 0,
  ];
  if (property_exists($wp_query, 'debug_have_posts_calls')) {
    $post_include_state['have_posts_calls'] = $wp_query->debug_have_posts_calls;
  }
  if (property_exists($wp_query, 'debug_the_post_calls')) {
    $post_include_state['the_post_calls'] = $wp_query->debug_the_post_calls;
  }
  wp4bd_debug_log($stage8, 'Post-Include Loop State', $post_include_state);
}

// ============================================================================
// RENDER DEBUG OUTPUT
// ============================================================================

print wp4bd_debug_render();

?>

<!-- Help Text -->
<div style="margin: 20px; padding: 20px; background: #eef5ff; color: #0f2a45; border-left: 4px solid #4a9eff;">
  <h3 style="color: #0f2a45;">🎛️ Debug Level Controls</h3>
  <p>Add <code style="background: #dce9fb; color: #0f2a45; padding: 2px 6px;">?wp4bd_debug=N</code> to URL to change debug level:</p>
  <ul>
    <li><a href="?wp4bd_debug=1" style="color: #0056b3;">Level 1</a> - Flow Tracking (timing only)</li>
    <li><a href="?wp4bd_debug=2" style="color: #0056b3;">Level 2</a> - Data Counts (default)</li>
    <li><a href="?wp4bd_debug=3" style="color: #0056b3;">Level 3</a> - Data Samples (titles, IDs)</li>
    <li><a href="?wp4bd_debug=4" style="color: #0056b3;">Level 4</a> - Full Data Dump</li>
  </ul>

  <h3 style="color: #0f2a45;">✅ Current Status</h3>
  <ul>
    <li>✅ <strong>WP4BD-001:</strong> Debug helper functions created</li>
    <li>✅ <strong>WP4BD-002:</strong> Debug template created</li>
    <li>✅ <strong>WP4BD-003:</strong> Stage 1 - Backdrop Query</li>
    <li>✅ <strong>WP4BD-004:</strong> Stage 2 - Transform to WP_Post</li>
    <li>✅ <strong>WP4BD-005:</strong> Stage 3 - Populate WP_Query</li>
    <li>✅ <strong>WP4BD-006:</strong> Stage 4 - Load WordPress Core</li>
    <li>✅ <strong>WP4BD-007:</strong> Stage 5 - Test The Loop</li>
    <li>✅ <strong>WP4BD-009:</strong> Stage 6 - Theme functions.php & Hooks (you are here!)</li>
  </ul>

  <h3 style="color: #0f2a45;">🎉 What You're Seeing</h3>
  <p><strong>All 5 stages are complete!</strong> The WordPress Loop is working with real Backdrop data:</p>
  <ul>
    <li>Stage 1: Query Backdrop nodes ✓</li>
    <li>Stage 2: Transform to WP_Post objects ✓</li>
    <li>Stage 3: Populate WP_Query ✓</li>
    <li>Stage 4: Load WordPress core ✓</li>
    <li>Stage 5: Test The Loop ✓</li>
  </ul>

  <h3 style="color: #0f2a45;">📋 What's Working</h3>
  <ol>
    <li><code style="background: #eef2fa; color: #0f2a45; padding: 2px 6px;">have_posts()</code> correctly checks for posts</li>
    <li><code style="background: #eef2fa; color: #0f2a45; padding: 2px 6px;">the_post()</code> advances through the loop</li>
    <li>Template tags (<code style="background: #eef2fa; color: #0f2a45; padding: 2px 6px;">the_title()</code>, <code style="background: #eef2fa; color: #0f2a45; padding: 2px 6px;">get_permalink()</code>) work</li>
    <li>Global <code style="background: #eef2fa; color: #0f2a45; padding: 2px 6px;">$post</code> variable is set correctly</li>
    <li><code style="background: #eef2fa; color: #0f2a45; padding: 2px 6px;">wp_reset_postdata()</code> resets the loop</li>
  </ol>

  <h3 style="color: #0f2a45;">🔧 Implementation Progress</h3>
  <p><strong>Epic 1: Debug Infrastructure</strong> - ✅ Complete! (2/2 tickets done)</p>
  <p><strong>Epic 2: Data Loading</strong> - ✅ Complete! (3/3 tickets done)</p>
  <p><strong>Epic 3: WordPress Integration</strong> - ✅ Complete! (2/2 tickets done)</p>
  <p><strong>🎊 Core WordPress Loop is now functional!</strong></p>
</div>

<?php
// Show some environment info for debugging
?>
<div style="margin: 20px; padding: 20px; background: #f7f7f7; color: #222; border-left: 4px solid #ccc;">
  <h3 style="color: #222;">🔍 Environment Info</h3>
  <ul>
    <li><strong>Backdrop Root:</strong> <?php print BACKDROP_ROOT; ?></li>
    <li><strong>Current Path:</strong> <?php print current_path(); ?></li>
    <li><strong>Theme:</strong> <?php print $GLOBALS['theme_key']; ?></li>
    <li><strong>Debug Level:</strong> <?php print wp4bd_debug_get_level(); ?></li>
    <li><strong>PHP Version:</strong> <?php print PHP_VERSION; ?></li>
  </ul>
</div>
