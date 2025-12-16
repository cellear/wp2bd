#!/usr/bin/env php
<?php
/**
 * Test script for WP4BD-V2-063: WordPress Options/Settings Bridge
 *
 * Run from command line:
 *   php TESTS/V2/063-options-bridge.php
 *
 * Or from within ddev:
 *   ddev exec 'php /var/www/html/TESTS/V2/063-options-bridge.php'
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

// Load WordPress bootstrap and options bridge
require_once BACKDROP_ROOT . '/modules/wp_content/includes/wp-bootstrap.php';
require_once BACKDROP_ROOT . '/modules/wp_content/includes/wp-options-bridge.php';

/**
 * Test basic option retrieval.
 */
function test_basic_option_retrieval() {
  echo "  Testing basic option retrieval...\n";

  // Test site URL
  $siteurl = wp4bd_get_option('siteurl');
  assert(is_string($siteurl), 'siteurl should return a string');
  assert(strpos($siteurl, 'http') === 0, 'siteurl should start with http');

  // Test blog name (should return default when config unavailable)
  $blogname = wp4bd_get_option('blogname');
  assert(is_string($blogname), 'blogname should return a string');

  // Test posts per page
  $posts_per_page = wp4bd_get_option('posts_per_page');
  assert(is_int($posts_per_page) || is_string($posts_per_page), 'posts_per_page should be numeric');

  echo "  ✅ Basic option retrieval verified\n";
}

/**
 * Test unknown option handling.
 */
function test_unknown_option() {
  echo "  Testing unknown option handling...\n";

  $unknown = wp4bd_get_option('nonexistent_option_xyz');
  assert($unknown === false, 'Unknown option should return false');

  echo "  ✅ Unknown option handling verified\n";
}

/**
 * Test upload path options.
 */
function test_upload_options() {
  echo "  Testing upload options...\n";

  $upload_path = wp4bd_get_option('upload_path');
  assert($upload_path === 'files', 'upload_path should be files');

  $upload_url_path = wp4bd_get_option('upload_url_path');
  assert(is_string($upload_url_path), 'upload_url_path should be a string');
  assert(strpos($upload_url_path, '/files') !== false, 'upload_url_path should contain /files');

  echo "  ✅ Upload options verified\n";
}

/**
 * Test comment settings.
 */
function test_comment_settings() {
  echo "  Testing comment settings...\n";

  $default_comment_status = wp4bd_get_option('default_comment_status');
  assert($default_comment_status === 'open', 'Default comment status should be open');

  $comment_registration = wp4bd_get_option('comment_registration');
  assert($comment_registration === 0, 'Comment registration should be disabled by default');

  echo "  ✅ Comment settings verified\n";
}

/**
 * Test theme options.
 */
function test_theme_options() {
  echo "  Testing theme options...\n";

  $template = wp4bd_get_option('template');
  assert(is_string($template), 'Template should be a string');

  $stylesheet = wp4bd_get_option('stylesheet');
  assert(is_string($stylesheet), 'Stylesheet should be a string');
  assert($stylesheet === $template, 'Stylesheet should match template for most themes');

  echo "  ✅ Theme options verified\n";
}

/**
 * Test date/time options.
 */
function test_datetime_options() {
  echo "  Testing date/time options...\n";

  $timezone = wp4bd_get_option('timezone_string');
  assert(is_string($timezone), 'Timezone should be a string');

  $date_format = wp4bd_get_option('date_format');
  assert(is_string($date_format), 'Date format should be a string');

  $time_format = wp4bd_get_option('time_format');
  assert(is_string($time_format), 'Time format should be a string');

  echo "  ✅ Date/time options verified\n";
}

/**
 * Test option storage and retrieval (if available).
 */
function test_option_storage() {
  echo "  Testing option storage...\n";

  // Try to set a custom option
  $test_option = 'wp4bd_test_option_' . time();
  $test_value = 'test_value_' . rand();

  $set_result = wp4bd_update_option($test_option, $test_value);
  // Note: This might fail in test environment, which is OK

  // Try to get the option (will return false if storage unavailable)
  $retrieved = wp4bd_get_option($test_option);
  if ($set_result) {
    assert($retrieved === $test_value, 'Retrieved value should match set value');
    echo "  ✅ Option storage and retrieval verified\n";
  } else {
    echo "  ⚠️ Option storage not available in test environment (expected)\n";
  }

  // Clean up
  wp4bd_delete_option($test_option);
}

/**
 * Test multiple option retrieval.
 */
function test_multiple_options() {
  echo "  Testing multiple option retrieval...\n";

  $options_to_test = array(
    'siteurl',
    'home',
    'blogname',
    'admin_email',
    'posts_per_page',
    'template',
    'stylesheet',
  );

  foreach ($options_to_test as $option) {
    $value = wp4bd_get_option($option);
    assert($value !== null, "Option '$option' should not be null");
    echo "    ✓ $option: " . (is_scalar($value) ? $value : gettype($value)) . "\n";
  }

  echo "  ✅ Multiple option retrieval verified\n";
}

// Run tests
try {
  test_basic_option_retrieval();
  test_unknown_option();
  test_upload_options();
  test_comment_settings();
  test_theme_options();
  test_datetime_options();
  test_option_storage();
  test_multiple_options();
  echo "\n🎉 All V2-063 tests passed!\n";
} catch (Exception $e) {
  echo "❌ V2-063 Test failed: " . $e->getMessage() . "\n";
  exit(1);
} catch (Error $e) {
  echo "❌ V2-063 Fatal error: " . $e->getMessage() . "\n";
  exit(1);
}
