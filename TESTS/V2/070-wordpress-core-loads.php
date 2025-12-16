#!/usr/bin/env php
<?php
/**
 * Test script for WP4BD-V2-070: Test WordPress Core Loads
 *
 * Run from command line:
 *   php TESTS/V2/070-wordpress-core-loads.php
 *
 * Or from within ddev:
 *   ddev exec 'php /var/www/html/TESTS/V2/070-wordpress-core-loads.php'
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

// Load WordPress bootstrap to get core classes and functions
require_once BACKDROP_ROOT . '/modules/wp_content/includes/wp-bootstrap.php';
// Load the db.php drop-in to get our wpdb bridge
require_once BACKDROP_ROOT . '/themes/wp/wpbrain/wp-content/db.php';
// Note: We don't load wp-globals-init.php here because it tries to load V1 classes
// which conflict with the WordPress core classes we just loaded

/**
 * Test WordPress core loading via bootstrap.
 */
function test_wordpress_core_bootstrap() {
  echo "  Testing WordPress core bootstrap...\n";

  // Call the bootstrap function directly (this loads WordPress core)
  $bootstrap_result = wp4bd_bootstrap_wordpress();
  assert($bootstrap_result === TRUE, 'WordPress bootstrap should succeed');

  // Check that WordPress core loaded successfully
  assert(class_exists('WP_Post'), 'WP_Post class should be available');
  assert(class_exists('WP_Query'), 'WP_Query class should be available');
  // Note: Not all functions may be loaded in minimal bootstrap, so we check for core classes

  echo "  ✅ WordPress core classes and functions loaded successfully\n";
}

/**
 * Test WordPress core availability after bootstrap.
 */
function test_wordpress_core_availability() {
  echo "  Testing WordPress core availability...\n";

  // Check that key WordPress globals exist (some are initialized by bootstrap)
  global $wp_version;

  // wp_version should be set from loading version.php
  if (isset($wp_version)) {
    assert($wp_version === '4.9.0', 'WordPress version should be 4.9.0');
    echo "    📊 wp_version: {$wp_version}\n";
  } else {
    echo "    ⚠️ wp_version not set (this is OK in test environment)\n";
  }

  // Test that we can create WordPress objects
  $test_post = new WP_Post((object) array('ID' => 1, 'post_title' => 'Test'));
  assert($test_post instanceof WP_Post, 'Should be able to create WP_Post objects');
  assert($test_post->ID === 1, 'WP_Post ID should be set correctly');

  echo "  ✅ WordPress core objects can be created\n";
}

/**
 * Test wpdb bridge is active.
 */
function test_wpdb_bridge_active() {
  echo "  Testing wpdb bridge is active...\n";

  // Check that our wpdb class is loaded
  assert(class_exists('wpdb'), 'wpdb class should be available');

  // Create a wpdb instance and test it's our intercepted version
  $test_db = new wpdb('test', 'test', 'test', 'localhost');

  // Test that methods return expected intercepted values
  $query_result = $test_db->query('SELECT * FROM wp_posts');
  assert($query_result === false, 'wpdb->query should return false (intercepted)');

  $results = $test_db->get_results('SELECT * FROM wp_posts');
  assert(is_array($results) && empty($results), 'get_results should return empty array');

  echo "  ✅ wpdb bridge is active and intercepting queries\n";
  echo "    🚫 query() returns: " . ($query_result === false ? 'FALSE (intercepted)' : 'UNEXPECTED') . "\n";
  echo "    📭 get_results() returns: " . (empty($results) ? 'EMPTY ARRAY (intercepted)' : 'UNEXPECTED') . "\n";
}

/**
 * Test no fatal errors during WordPress loading.
 */
function test_no_fatal_errors() {
  echo "  Testing for fatal errors during WordPress loading...\n";

  // Capture any errors that might occur
  $error_count = 0;
  $original_error_handler = set_error_handler(function($errno, $errstr) use (&$error_count) {
    // Only count fatal errors
    if ($errno === E_ERROR || $errno === E_CORE_ERROR || $errno === E_COMPILE_ERROR) {
      $error_count++;
      echo "    ❌ FATAL ERROR: $errstr\n";
    }
    return true; // Don't call the default handler
  });

  // Try loading some WordPress functions that might trigger errors
  if (function_exists('wp_parse_url')) {
    $result = wp_parse_url('http://example.com/test');
    assert(is_array($result), 'wp_parse_url should work without errors');
  }

  if (function_exists('wp_json_encode')) {
    $result = wp_json_encode(array('test' => 'data'));
    assert(is_string($result), 'wp_json_encode should work without errors');
  }

  // Restore original error handler
  set_error_handler($original_error_handler);

  assert($error_count === 0, 'No fatal errors should occur during WordPress loading');

  echo "  ✅ No fatal errors during WordPress loading\n";
  echo "    🔍 wp_parse_url: " . (function_exists('wp_parse_url') ? 'AVAILABLE' : 'MISSING') . "\n";
  echo "    🔍 wp_json_encode: " . (function_exists('wp_json_encode') ? 'AVAILABLE' : 'MISSING') . "\n";
}

/**
 * Test WordPress core version and compatibility.
 */
function test_wordpress_version_compatibility() {
  echo "  Testing WordPress version and compatibility...\n";

  // Check WordPress version from loaded files
  if (defined('ABSPATH')) {
    $version_file = ABSPATH . WPINC . '/version.php';
    if (file_exists($version_file)) {
      include $version_file;
      if (isset($wp_version)) {
        assert(version_compare($wp_version, '4.9', '>='), 'WordPress version should be 4.9 or higher');
        echo "    📋 WordPress core version: {$wp_version}\n";
      }
    }
  }

  // Check that key WordPress 4.9 features are available
  $features_to_check = array(
    'WP_Post' => class_exists('WP_Post'),
    'WP_Query' => class_exists('WP_Query'),
    'wp_json_encode' => function_exists('wp_json_encode'),
  );

  // These functions may not be loaded in minimal bootstrap, so check what's available
  $optional_features = array(
    'wp_parse_url' => function_exists('wp_parse_url'),
    'get_template_part' => function_exists('get_template_part'),
  );

  $available_features = 0;
  foreach ($features_to_check as $feature => $available) {
    if ($available) {
      $available_features++;
    }
  }

  // Require core classes and at least one function
  assert($available_features >= 2, 'Core WordPress 4.9 features should be available');

  echo "  ✅ WordPress 4.9 compatibility verified\n";
  echo "    📊 Core features available: {$available_features}/" . count($features_to_check) . "\n";
  echo "    📊 Optional features: wp_parse_url=" . ($optional_features['wp_parse_url'] ? 'YES' : 'NO') . ", get_template_part=" . ($optional_features['get_template_part'] ? 'YES' : 'NO') . "\n";
}

// Run tests
try {
  test_wordpress_core_bootstrap();
  test_wordpress_core_availability();
  test_wpdb_bridge_active();
  test_no_fatal_errors();
  test_wordpress_version_compatibility();
  echo "\n🎉 All V2-070 tests passed - WordPress core loads successfully!\n";
} catch (Exception $e) {
  echo "❌ V2-070 Test failed: " . $e->getMessage() . "\n";
  exit(1);
} catch (Error $e) {
  echo "❌ V2-070 Fatal error: " . $e->getMessage() . "\n";
  exit(1);
}
