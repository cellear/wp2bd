<?php
/**
 * Test Epic 6: V2-052 Prevent WordPress Database Connection
 *
 * Tests that WordPress cannot connect to database during bootstrap.
 *
 * @package WP4BD
 * @subpackage Tests
 */

require_once __DIR__ . '/../bootstrap.php';

/**
 * Test that db.php drop-in exists and is properly loaded.
 */
function test_db_dropin_exists_and_loaded() {
  // Check that db.php drop-in exists
  $db_dropin_path = BACKDROP_ROOT . '/themes/wp/wpbrain/wp-content/db.php';
  assert(file_exists($db_dropin_path), 'db.php drop-in should exist');

  // After bootstrap, wpdb class should be our intercepted version
  $bootstrap_result = wp4bd_bootstrap_wordpress();
  assert($bootstrap_result === TRUE, 'Bootstrap should succeed');

  assert(class_exists('wpdb'), 'wpdb class should be available');

  // Test that wpdb methods return expected "intercepted" values
  $test_db = new wpdb('test', 'test', 'test', 'localhost');

  // query() should return false (intercepted)
  $result = $test_db->query('SELECT * FROM wp_posts');
  assert($result === false, 'wpdb->query() should return false (intercepted)');

  // get_results() should return empty array
  $results = $test_db->get_results('SELECT * FROM wp_posts');
  assert(is_array($results) && empty($results), 'get_results() should return empty array');

  echo "✅ V2-052: db.php drop-in exists and loaded - PASSED\n";
}

/**
 * Test that wp-config-bd.php has dummy credentials.
 */
function test_wp_config_dummy_credentials() {
  // Check that wp-config-bd.php exists
  $config_path = BACKDROP_ROOT . '/themes/wp/wpbrain/wp-config-bd.php';
  assert(file_exists($config_path), 'wp-config-bd.php should exist');

  // The config file should define dummy DB constants
  assert(defined('DB_NAME'), 'DB_NAME should be defined in wp-config-bd.php');
  assert(DB_NAME === 'wp4bd_intercepted', 'DB_NAME should be dummy value');

  assert(defined('DB_USER'), 'DB_USER should be defined in wp-config-bd.php');
  assert(DB_USER === 'wp4bd_intercepted', 'DB_USER should be dummy value');

  assert(defined('DB_PASSWORD'), 'DB_PASSWORD should be defined in wp-config-bd.php');
  assert(DB_PASSWORD === 'wp4bd_intercepted', 'DB_PASSWORD should be dummy value');

  echo "✅ V2-052: wp-config-bd.php dummy credentials - PASSED\n";
}

/**
 * Test that WordPress bootstrap completes without database errors.
 */
function test_bootstrap_without_db_errors() {
  // Capture any potential errors/warnings
  $error_reporting = error_reporting();
  error_reporting(E_ALL);
  $errors = array();

  set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$errors) {
    $errors[] = $errstr;
  });

  // Bootstrap WordPress
  $bootstrap_result = wp4bd_bootstrap_wordpress();

  // Restore error handler
  restore_error_handler();
  error_reporting($error_reporting);

  assert($bootstrap_result === TRUE, 'Bootstrap should succeed');

  // Check that no database connection errors occurred
  $db_errors = array_filter($errors, function($error) {
    return strpos($error, 'database') !== false ||
           strpos($error, 'mysql') !== false ||
           strpos($error, 'connection') !== false;
  });

  assert(empty($db_errors), 'No database connection errors should occur during bootstrap. Errors: ' . implode(', ', $db_errors));

  echo "✅ V2-052: Bootstrap without database errors - PASSED\n";
}

/**
 * Test that wpdb bridge is active before WordPress tries to connect.
 */
function test_wpdb_bridge_active_before_connection() {
  // Bootstrap should load db.php drop-in before WordPress tries to initialize wpdb
  $bootstrap_result = wp4bd_bootstrap_wordpress();
  assert($bootstrap_result === TRUE, 'Bootstrap should succeed');

  // The global $wpdb should be our intercepted version if WordPress tried to initialize it
  global $wpdb;
  if (isset($wpdb)) {
    assert(get_class($wpdb) === 'wpdb', '$wpdb should be our wpdb class');
  }

  // Test that we can create a wpdb instance without connecting to database
  $test_db = new wpdb('dummy', 'dummy', 'dummy', 'localhost');
  assert(is_object($test_db), 'Should be able to create wpdb instance');

  // Test that table names are set (required for WordPress)
  assert(isset($test_db->posts), 'wpdb->posts should be set');
  assert(isset($test_db->postmeta), 'wpdb->postmeta should be set');
  assert(isset($test_db->users), 'wpdb->users should be set');

  echo "✅ V2-052: wpdb bridge active before connection - PASSED\n";
}

// Run tests
try {
  test_db_dropin_exists_and_loaded();
  test_wp_config_dummy_credentials();
  test_bootstrap_without_db_errors();
  test_wpdb_bridge_active_before_connection();
  echo "\n🎉 All V2-052 tests passed!\n";
} catch (Exception $e) {
  echo "❌ V2-052 Test failed: " . $e->getMessage() . "\n";
  exit(1);
}
