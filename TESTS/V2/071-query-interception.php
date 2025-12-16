#!/usr/bin/env php
<?php
/**
 * Test script for WP4BD-V2-071: Test Query Interception
 *
 * Run from command line:
 *   php TESTS/V2/071-query-interception.php
 *
 * Or from within ddev:
 *   ddev exec 'php /var/www/html/TESTS/V2/071-query-interception.php'
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

// Load WordPress bootstrap and db.php drop-in
require_once BACKDROP_ROOT . '/modules/wp_content/includes/wp-bootstrap.php';
require_once BACKDROP_ROOT . '/themes/wp/wpbrain/wp-content/db.php';

/**
 * Test basic query interception.
 */
function test_basic_query_interception() {
  echo "  Testing basic query interception...\n";

  $wpdb = new wpdb('test', 'test', 'test', 'localhost');

  // Test that queries are intercepted and return expected values
  $result = $wpdb->query('SELECT * FROM wp_posts');
  assert($result === false, 'SELECT query should return false (intercepted)');

  $results = $wpdb->get_results('SELECT * FROM wp_users');
  assert(is_array($results) && empty($results), 'get_results should return empty array');

  $var = $wpdb->get_var('SELECT COUNT(*) FROM wp_posts');
  assert($var === null, 'get_var should return null');

  $row = $wpdb->get_row('SELECT * FROM wp_posts LIMIT 1');
  assert($row === null, 'get_row should return null');

  echo "  ✅ Basic query interception working\n";
  echo "    🚫 query() returns: " . ($result === false ? 'FALSE' : 'UNEXPECTED') . "\n";
  echo "    📭 get_results() returns: " . (empty($results) ? 'EMPTY ARRAY' : 'UNEXPECTED') . "\n";
  echo "    ❓ get_var() returns: " . ($var === null ? 'NULL' : 'UNEXPECTED') . "\n";
  echo "    📄 get_row() returns: " . ($row === null ? 'NULL' : 'UNEXPECTED') . "\n";
}

/**
 * Test write operation interception.
 */
function test_write_operation_interception() {
  echo "  Testing write operation interception...\n";

  $wpdb = new wpdb('test', 'test', 'test', 'localhost');

  // Test INSERT, UPDATE, DELETE operations
  $insert_result = $wpdb->insert('wp_posts', array('post_title' => 'Test'));
  assert($insert_result === false, 'INSERT should return false (intercepted)');

  $update_result = $wpdb->update('wp_posts', array('post_title' => 'Updated'), array('ID' => 1));
  assert($update_result === false, 'UPDATE should return false (intercepted)');

  $delete_result = $wpdb->delete('wp_posts', array('ID' => 1));
  assert($delete_result === false, 'DELETE should return false (intercepted)');

  echo "  ✅ Write operations properly intercepted\n";
  echo "    ✏️ INSERT returns: " . ($insert_result === false ? 'FALSE' : 'UNEXPECTED') . "\n";
  echo "    🔄 UPDATE returns: " . ($update_result === false ? 'FALSE' : 'UNEXPECTED') . "\n";
  echo "    🗑️ DELETE returns: " . ($delete_result === false ? 'FALSE' : 'UNEXPECTED') . "\n";
}

/**
 * Test prepare method.
 */
function test_prepare_method() {
  echo "  Testing prepare method...\n";

  $wpdb = new wpdb('test', 'test', 'test', 'localhost');

  // Test prepared statements
  $prepared = $wpdb->prepare('SELECT * FROM wp_posts WHERE ID = %d', 123);
  assert($prepared === 'SELECT * FROM wp_posts WHERE ID = 123', 'prepare should return prepared query string');

  echo "  ✅ Prepare method working correctly\n";
  echo "    🛡️ prepare() returns: '{$prepared}'\n";
}

/**
 * Test table name properties.
 */
function test_table_name_properties() {
  echo "  Testing table name properties...\n";

  $wpdb = new wpdb('test', 'test', 'test', 'localhost');

  // Check that table properties are set
  assert(isset($wpdb->posts), 'posts table property should be set');
  assert(isset($wpdb->postmeta), 'postmeta table property should be set');
  assert(isset($wpdb->users), 'users table property should be set');
  assert(isset($wpdb->usermeta), 'usermeta table property should be set');
  assert(isset($wpdb->terms), 'terms table property should be set');
  assert(isset($wpdb->term_taxonomy), 'term_taxonomy table property should be set');
  assert(isset($wpdb->term_relationships), 'term_relationships table property should be set');

  // Check that they have the wp_ prefix
  assert(strpos($wpdb->posts, 'wp_') === 0, 'posts table should have wp_ prefix');
  assert(strpos($wpdb->users, 'wp_') === 0, 'users table should have wp_ prefix');

  echo "  ✅ Table name properties set correctly\n";
  echo "    📋 posts table: {$wpdb->posts}\n";
  echo "    📋 users table: {$wpdb->users}\n";
  echo "    📋 terms table: {$wpdb->terms}\n";
}

/**
 * Test that no actual database connections are made.
 */
function test_no_database_connections() {
  echo "  Testing that no database connections are made...\n";

  // This test is tricky in isolation, but we can verify that:
  // 1. No mysqli/mysql connections are attempted
  // 2. Our wpdb class doesn't try to connect
  // 3. All operations return the expected "intercepted" values

  $wpdb = new wpdb('fake_host', 'fake_user', 'fake_pass', 'fake_db');

  // These should all work without throwing connection errors
  $result1 = $wpdb->query('SELECT 1');
  $result2 = $wpdb->get_results('SELECT * FROM fake_table');
  $result3 = $wpdb->get_var('SELECT COUNT(*) FROM fake_table');

  // Verify they return expected intercepted values
  assert($result1 === false, 'Query should be intercepted');
  assert(is_array($result2) && empty($result2), 'get_results should return empty array');
  assert($result3 === null, 'get_var should return null');

  echo "  ✅ No database connections attempted\n";
  echo "    🚫 All operations intercepted without connection errors\n";
}

/**
 * Test query pattern recognition (basic).
 */
function test_query_pattern_recognition() {
  echo "  Testing basic query pattern recognition...\n";

  $wpdb = new wpdb('test', 'test', 'test', 'localhost');

  // Test different query patterns that should all be intercepted
  $patterns = array(
    'SELECT * FROM wp_posts WHERE ID = 1',
    'INSERT INTO wp_users (name) VALUES ("test")',
    'UPDATE wp_posts SET title = "new" WHERE ID = 1',
    'DELETE FROM wp_terms WHERE tid = 5',
    'SHOW TABLES LIKE "wp_%"',
    'DESCRIBE wp_posts',
  );

  foreach ($patterns as $sql) {
    $result = $wpdb->query($sql);
    assert($result === false, "Query '$sql' should be intercepted");
  }

  echo "  ✅ All query patterns intercepted\n";
  echo "    🔍 Tested " . count($patterns) . " different query patterns\n";
}

/**
 * Test wpdb object properties.
 */
function test_wpdb_properties() {
  echo "  Testing wpdb object properties...\n";

  $wpdb = new wpdb('test', 'test', 'test', 'localhost');

  // Check required wpdb properties
  assert(isset($wpdb->prefix), 'prefix property should be set');
  assert($wpdb->prefix === 'wp_', 'prefix should be wp_');

  assert(isset($wpdb->last_error), 'last_error property should exist');
  assert(isset($wpdb->num_rows), 'num_rows property should exist');
  assert(isset($wpdb->insert_id), 'insert_id property should exist');

  echo "  ✅ wpdb object properties set correctly\n";
  echo "    🔧 prefix: {$wpdb->prefix}\n";
  echo "    🔧 last_error: " . (isset($wpdb->last_error) ? 'SET' : 'NOT SET') . "\n";
  echo "    🔧 num_rows: " . (isset($wpdb->num_rows) ? 'SET' : 'NOT SET') . "\n";
  echo "    🔧 insert_id: " . (isset($wpdb->insert_id) ? 'SET' : 'NOT SET') . "\n";
}

// Run tests
try {
  test_basic_query_interception();
  test_write_operation_interception();
  test_prepare_method();
  test_table_name_properties();
  test_no_database_connections();
  test_query_pattern_recognition();
  test_wpdb_properties();
  echo "\n🎉 All V2-071 tests passed - Query interception working perfectly!\n";
} catch (Exception $e) {
  echo "❌ V2-071 Test failed: " . $e->getMessage() . "\n";
  exit(1);
} catch (Error $e) {
  echo "❌ V2-071 Fatal error: " . $e->getMessage() . "\n";
  exit(1);
}
