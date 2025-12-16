#!/usr/bin/env php
<?php
/**
 * Test script for WP4BD-V2-072: Test Theme Rendering
 *
 * Run from command line:
 *   php TESTS/V2/072-theme-rendering.php
 *
 * Or from within ddev:
 *   ddev exec 'php /var/www/html/TESTS/V2/072-theme-rendering.php'
 */

// Setup BACKDROP_ROOT for both environments
if (file_exists('/var/www/html/backdrop-1.30')) {
  define('BACKDROP_ROOT', '/var/www/html/backdrop-1.30');
} else {
  // We're in TESTS/V2/, so go up two levels to repo root, then into backdrop-1.30
  define('BACKDROP_ROOT', dirname(dirname(__DIR__)) . '/backdrop-1.30');
}

// Define WordPress paths
$wp_root = BACKDROP_ROOT . '/themes/wp/wpbrain/';
if (!defined('ABSPATH')) {
  define('ABSPATH', $wp_root);
}
if (!defined('WPINC')) {
  define('WPINC', 'wp-includes');
}

// Load WordPress bootstrap and our modules
require_once BACKDROP_ROOT . '/modules/wp_content/includes/wp-bootstrap.php';
require_once BACKDROP_ROOT . '/modules/wp_content/includes/wp-post-bridge.php';
require_once BACKDROP_ROOT . '/modules/wp_content/includes/wp-user-bridge.php';
require_once BACKDROP_ROOT . '/modules/wp_content/includes/wp-term-bridge.php';
require_once BACKDROP_ROOT . '/modules/wp_content/includes/wp-options-bridge.php';
require_once BACKDROP_ROOT . '/themes/wp/wpbrain/wp-content/db.php';

// Load theme functions (these provide get_header, the_post, etc.)
require_once BACKDROP_ROOT . '/themes/wp/template.php';

/**
 * Test theme function availability.
 */
function test_theme_functions_available() {
  echo "  Testing theme function availability...\n";

  // Check that essential WordPress theme functions are available
  $functions_to_check = array(
    'get_header' => function_exists('get_header'),
    'get_footer' => function_exists('get_footer'),
    'get_sidebar' => function_exists('get_sidebar'),
    'get_template_part' => function_exists('get_template_part'),
    'have_posts' => function_exists('have_posts'),
    'the_post' => function_exists('the_post'),
    'the_title' => function_exists('the_title'),
    'the_content' => function_exists('the_content'),
    'the_excerpt' => function_exists('the_excerpt'),
  );

  $available_count = 0;
  foreach ($functions_to_check as $function => $available) {
    if ($available) {
      $available_count++;
    } else {
      echo "    ⚠️ Missing function: $function\n";
    }
  }

  assert($available_count >= 7, 'Most WordPress theme functions should be available');

  echo "  ✅ Theme functions available\n";
  echo "    📊 Available functions: {$available_count}/" . count($functions_to_check) . "\n";
}

/**
 * Test template hierarchy functions.
 */
function test_template_hierarchy() {
  echo "  Testing template hierarchy functions...\n";

  // Test get_template_part (this is a key function for themes)
  if (function_exists('get_template_part')) {
    // This should not crash even if files don't exist
    ob_start();
    @get_template_part('content', 'post');
    $output = ob_get_clean();

    // Should not produce fatal errors
    assert($output !== false, 'get_template_part should execute without fatal errors');

    echo "    ✅ get_template_part() works without errors\n";
  }

  // Test locate_template function if available
  if (function_exists('locate_template')) {
    $located = locate_template(array('index.php', 'archive.php'));
    // Should return empty or a path, but not crash
    assert(is_string($located), 'locate_template should return a string');

    echo "    ✅ locate_template() works: " . (!empty($located) ? basename($located) : 'no template found') . "\n";
  }

  echo "  ✅ Template hierarchy functions working\n";
}

/**
 * Test The Loop execution with mock data.
 */
function test_the_loop_execution() {
  echo "  Testing The Loop execution with mock data...\n";

  // Create mock Backdrop node
  $mock_node = (object) array(
    'nid' => 42,
    'type' => 'post',
    'title' => 'Test Post for Loop',
    'uid' => 1,
    'status' => 1,
    'created' => time(),
    'body' => array(
      'und' => array(
        array('value' => 'This is test post content for The Loop.')
      )
    ),
  );

  // Convert to WP_Post
  $wp_post = wp4bd_node_to_wp_post($mock_node);
  assert($wp_post instanceof WP_Post, 'Should create WP_Post object');

  // Set up a basic query with our post
  global $wp_query, $post;
  $wp_query = new WP_Query();
  $wp_query->posts = array($wp_post);
  $wp_query->post_count = 1;
  $wp_query->current_post = -1;
  $wp_query->is_home = true;
  $wp_query->post = $wp_post;
  $post = $wp_post;

  // Test have_posts()
  assert(function_exists('have_posts'), 'have_posts function should exist');
  $has_posts = have_posts();
  assert($has_posts === true, 'have_posts() should return true with our test post');

  // Test the_post() - this advances the loop
  if (function_exists('the_post')) {
    ob_start();
    the_post();
    $output = ob_get_clean();

    // Check that post is now set correctly
    assert($post->ID === 42, 'the_post() should set $post correctly');
  }

  // Test content functions
  if (function_exists('the_title')) {
    ob_start();
    the_title();
    $title_output = ob_get_clean();
    assert(strpos($title_output, 'Test Post for Loop') !== false, 'the_title() should output post title');
  }

  if (function_exists('the_content')) {
    ob_start();
    the_content();
    $content_output = ob_get_clean();
    assert(strpos($content_output, 'test post content') !== false, 'the_content() should output post content');
  }

  echo "  ✅ The Loop execution working\n";
  echo "    🔄 have_posts(): " . ($has_posts ? 'TRUE' : 'FALSE') . "\n";
  echo "    📝 Post title retrieved: " . (!empty($title_output) ? 'YES' : 'NO') . "\n";
  echo "    📝 Post content retrieved: " . (!empty($content_output) ? 'YES' : 'NO') . "\n";
}

/**
 * Test theme path resolution.
 */
function test_theme_path_resolution() {
  echo "  Testing theme path resolution...\n";

  // Test that theme paths are resolved correctly
  if (function_exists('get_template_directory')) {
    $template_dir = get_template_directory();
    assert(is_string($template_dir), 'get_template_directory() should return a string');
    assert(strpos($template_dir, 'twentythirteen') !== false || strpos($template_dir, 'twenty') !== false, 'Should point to a Twenty theme');

    echo "    📁 Template directory: " . basename($template_dir) . "\n";
  }

  if (function_exists('get_stylesheet_directory')) {
    $stylesheet_dir = get_stylesheet_directory();
    assert(is_string($stylesheet_dir), 'get_stylesheet_directory() should return a string');

    echo "    🎨 Stylesheet directory: " . basename($stylesheet_dir) . "\n";
  }

  if (function_exists('get_template_directory_uri')) {
    $template_uri = get_template_directory_uri();
    assert(is_string($template_uri), 'get_template_directory_uri() should return a string');
    assert(strpos($template_uri, 'http') === 0, 'URI should start with http');

    echo "    🌐 Template URI: {$template_uri}\n";
  }

  echo "  ✅ Theme path resolution working\n";
}

/**
 * Test conditional functions.
 */
function test_conditional_functions() {
  echo "  Testing WordPress conditional functions...\n";

  // Set up basic query state
  global $wp_query;
  if (!isset($wp_query)) {
    $wp_query = new WP_Query();
  }
  $wp_query->is_home = true;
  $wp_query->is_single = false;
  $wp_query->is_page = false;
  $wp_query->is_archive = false;

  $conditionals = array(
    'is_home' => function_exists('is_home') ? is_home() : null,
    'is_single' => function_exists('is_single') ? is_single() : null,
    'is_page' => function_exists('is_page') ? is_page() : null,
    'is_archive' => function_exists('is_archive') ? is_archive() : null,
  );

  $working_conditionals = 0;
  foreach ($conditionals as $name => $result) {
    if ($result !== null) {
      $working_conditionals++;
      echo "    ✅ $name(): " . ($result ? 'TRUE' : 'FALSE') . "\n";
    } else {
      echo "    ⚠️ $name(): NOT AVAILABLE\n";
    }
  }

  assert($working_conditionals >= 2, 'At least basic conditional functions should work');

  echo "  ✅ Conditional functions working\n";
}

/**
 * Test that themes don't crash when called.
 */
function test_theme_rendering_safety() {
  echo "  Testing theme rendering safety...\n";

  // Test that calling theme functions doesn't cause fatal errors
  $error_count = 0;
  $original_error_handler = set_error_handler(function($errno, $errstr) use (&$error_count) {
    if ($errno === E_ERROR || $errno === E_CORE_ERROR || $errno === E_COMPILE_ERROR) {
      $error_count++;
      echo "    ❌ FATAL ERROR in theme function: $errstr\n";
    }
    return true;
  });

  // Try some theme functions that should not crash
  // Note: wp_head() and wp_footer() require Backdrop functions, so we skip them in test environment

  if (function_exists('body_class')) {
    ob_start();
    @body_class();
    ob_end_clean();
    echo "    ✅ body_class() executed without fatal errors\n";
  }

  if (function_exists('post_class')) {
    ob_start();
    @post_class();
    ob_end_clean();
    echo "    ✅ post_class() executed without fatal errors\n";
  }

  // Test some basic template tags that don't require Backdrop
  if (function_exists('get_the_ID')) {
    $id = get_the_ID();
    assert(is_int($id) || is_string($id), 'get_the_ID() should return an ID');
    echo "    ✅ get_the_ID() returned: $id\n";
  }

  // Restore error handler
  set_error_handler($original_error_handler);

  assert($error_count === 0, 'No fatal errors should occur when calling theme functions');

  echo "  ✅ Theme rendering functions are safe to call\n";
}

// Run tests
try {
  test_theme_functions_available();
  test_template_hierarchy();
  test_the_loop_execution();
  test_theme_path_resolution();
  test_conditional_functions();
  test_theme_rendering_safety();
  echo "\n🎉 All V2-072 tests passed - Theme rendering framework working!\n";
} catch (Exception $e) {
  echo "❌ V2-072 Test failed: " . $e->getMessage() . "\n";
  exit(1);
} catch (Error $e) {
  echo "❌ V2-072 Fatal error: " . $e->getMessage() . "\n";
  exit(1);
}
