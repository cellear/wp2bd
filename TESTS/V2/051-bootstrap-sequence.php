<?php
/**
 * Test Epic 6: V2-051 Bootstrap Sequence Implementation
 *
 * Tests the correct WordPress bootstrap sequence after Backdrop FULL phase.
 *
 * @package WP4BD
 * @subpackage Tests
 */

require_once __DIR__ . '/../bootstrap.php';

/**
 * Test that bootstrap sequence executes in correct order.
 */
function test_bootstrap_sequence_order() {
  // 1. Backdrop bootstrap completes (simulated)
  // 2. Check if wp theme active (simulated)
  $GLOBALS['theme_key'] = 'wp';
  $_GET['q'] = 'node';

  // 3. Define WordPress constants (this should happen in wp4bd_bootstrap_wordpress)
  // 4. Include wp49brain/wp-load.php (this should happen in wp4bd_bootstrap_wordpress)
  $bootstrap_result = wp4bd_bootstrap_wordpress();

  assert($bootstrap_result === TRUE, 'wp4bd_bootstrap_wordpress should return TRUE');

  // Check that wp-load.php was included (constants should be set)
  assert(defined('ABSPATH'), 'ABSPATH should be defined after wp-load.php');
  assert(defined('WPINC'), 'WPINC should be defined after wp-load.php');

  // 5. WordPress initializes but wpdb is replaced (check db.php drop-in)
  assert(class_exists('wpdb'), 'wpdb class should be available');

  // 6. Bridge functions populate WordPress globals (test wp4bd_init_wordpress_globals)
  wp4bd_init_wordpress_globals();

  global $wp_post_types, $wp_taxonomies, $wp_theme;
  assert(isset($wp_post_types), '$wp_post_types should be set');
  assert(isset($wp_taxonomies), '$wp_taxonomies should be set');

  // 7. WordPress theme rendering proceeds normally (would be tested in rendering tests)

  echo "✅ V2-051: Bootstrap sequence order - PASSED\n";
}

/**
 * Test that wp-load.php is properly included.
 */
function test_wp_load_inclusion() {
  $bootstrap_result = wp4bd_bootstrap_wordpress();

  assert($bootstrap_result === TRUE, 'Bootstrap should succeed');

  // Check that WordPress core functions are available
  assert(function_exists('wp_parse_url'), 'WordPress core functions should be available after wp-load.php');
  assert(function_exists('wp_json_encode'), 'WordPress core functions should be available after wp-load.php');

  // Check that WordPress constants are properly set
  assert(ABSPATH === BACKDROP_ROOT . '/themes/wp/wpbrain/', 'ABSPATH should point to wpbrain directory');

  echo "✅ V2-051: wp-load.php inclusion - PASSED\n";
}

/**
 * Test that WordPress initializes without database connection errors.
 */
function test_wordpress_initialization_without_db() {
  $bootstrap_result = wp4bd_bootstrap_wordpress();

  assert($bootstrap_result === TRUE, 'Bootstrap should succeed without database');

  // WordPress should initialize but not connect to database
  // The db.php drop-in should prevent any actual database connections
  assert(class_exists('WP_Post'), 'WP_Post class should be loaded');
  assert(class_exists('WP_Query'), 'WP_Query class should be loaded');

  // wpdb should be our intercepted version
  global $wpdb;
  if (isset($wpdb)) {
    assert(is_object($wpdb), '$wpdb should be an object');
    assert(get_class($wpdb) === 'wpdb', '$wpdb should be wpdb class');
  }

  echo "✅ V2-051: WordPress initialization without DB - PASSED\n";
}

// Run tests
try {
  test_bootstrap_sequence_order();
  test_wp_load_inclusion();
  test_wordpress_initialization_without_db();
  echo "\n🎉 All V2-051 tests passed!\n";
} catch (Exception $e) {
  echo "❌ V2-051 Test failed: " . $e->getMessage() . "\n";
  exit(1);
}
