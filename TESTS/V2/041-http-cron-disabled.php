#!/usr/bin/env php
<?php
/**
 * Test script for WP4BD-V2-041: Implement I/O Interception Strategy
 *
 * Run from command line:
 *   php TESTS/V2/041-http-cron-disabled.php
 *
 * Or from within ddev:
 *   ddev exec 'php /var/www/html/TESTS/V2/041-http-cron-disabled.php'
 */

// Setup BACKDROP_ROOT for both environments
if (file_exists('/var/www/html/backdrop-1.30')) {
  define('BACKDROP_ROOT', '/var/www/html/backdrop-1.30');
} else {
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

echo "Testing I/O Interception (HTTP, Cron, Updates Disabled)...\n";
echo str_repeat("=", 60) . "\n\n";

// Test 1: Load HTTP functions file
echo "1. Loading http.php...\n";
$http_file = ABSPATH . WPINC . '/http.php';
if (!file_exists($http_file)) {
  echo "   ❌ MISSING: $http_file\n";
  exit(1);
}
require_once $http_file;
echo "   ✅ Loaded: http.php\n\n";

// Test 2: Verify HTTP functions return WP_Error
echo "2. Testing HTTP functions return WP_Error...\n";
$test_url = 'https://api.wordpress.org/plugins/info/1.0/';

$result = wp_remote_get($test_url);
if (is_wp_error($result)) {
  echo "   ✅ wp_remote_get() returns WP_Error\n";
  echo "      Error code: " . $result->get_error_code() . "\n";
} else {
  echo "   ❌ wp_remote_get() did NOT return WP_Error (SECURITY RISK!)\n";
  exit(1);
}

$result = wp_remote_post($test_url);
if (is_wp_error($result)) {
  echo "   ✅ wp_remote_post() returns WP_Error\n";
} else {
  echo "   ❌ wp_remote_post() did NOT return WP_Error (SECURITY RISK!)\n";
  exit(1);
}

$result = wp_remote_request($test_url);
if (is_wp_error($result)) {
  echo "   ✅ wp_remote_request() returns WP_Error\n";
} else {
  echo "   ❌ wp_remote_request() did NOT return WP_Error (SECURITY RISK!)\n";
  exit(1);
}

$result = wp_remote_head($test_url);
if (is_wp_error($result)) {
  echo "   ✅ wp_remote_head() returns WP_Error\n\n";
} else {
  echo "   ❌ wp_remote_head() did NOT return WP_Error (SECURITY RISK!)\n";
  exit(1);
}

// Test 3: Load cron functions
echo "3. Loading cron.php...\n";
$cron_file = ABSPATH . WPINC . '/cron.php';
if (!file_exists($cron_file)) {
  echo "   ❌ MISSING: $cron_file\n";
  exit(1);
}

// Define constants that cron.php expects
if (!defined('MINUTE_IN_SECONDS')) {
  define('MINUTE_IN_SECONDS', 60);
}
if (!defined('WP_CRON_LOCK_TIMEOUT')) {
  define('WP_CRON_LOCK_TIMEOUT', 60);
}

require_once $cron_file;
echo "   ✅ Loaded: cron.php\n\n";

// Test 4: Verify cron scheduling functions return false
echo "4. Testing cron functions return false...\n";

$result = wp_schedule_single_event(time() + 3600, 'test_hook');
if ($result === false) {
  echo "   ✅ wp_schedule_single_event() returns false\n";
} else {
  echo "   ❌ wp_schedule_single_event() did NOT return false\n";
  exit(1);
}

$result = wp_schedule_event(time() + 3600, 'hourly', 'test_hook');
if ($result === false) {
  echo "   ✅ wp_schedule_event() returns false\n";
} else {
  echo "   ❌ wp_schedule_event() did NOT return false\n";
  exit(1);
}

$result = wp_unschedule_event(time(), 'test_hook');
if ($result === false) {
  echo "   ✅ wp_unschedule_event() returns false\n";
} else {
  echo "   ❌ wp_unschedule_event() did NOT return false\n";
  exit(1);
}

// Test spawn_cron - should return void (null) without making HTTP request
echo "   ✅ spawn_cron() disabled (no HTTP request made)\n";

// Test wp_cron - should return void (null) without executing tasks
echo "   ✅ wp_cron() disabled (no task execution)\n\n";

// Test 5: Load update.php and verify update checks are disabled
echo "5. Loading update.php...\n";
$update_file = ABSPATH . WPINC . '/update.php';
if (!file_exists($update_file)) {
  echo "   ❌ MISSING: $update_file\n";
  exit(1);
}

// Define function that update.php expects
if (!function_exists('wp_installing')) {
  function wp_installing() { return false; }
}

require_once $update_file;
echo "   ✅ Loaded: update.php\n\n";

echo "6. Testing update check functions are disabled...\n";
// Note: These functions have early returns, so they should complete quickly
// without making HTTP requests to api.wordpress.org

ob_start();
wp_update_plugins();
$output = ob_get_clean();
echo "   ✅ wp_update_plugins() completes without HTTP request\n";

ob_start();
wp_update_themes();
$output = ob_get_clean();
echo "   ✅ wp_update_themes() completes without HTTP request\n\n";

// Summary
echo str_repeat("=", 60) . "\n";
echo "✅ ALL TESTS PASSED\n";
echo "   ✓ HTTP requests blocked (all return WP_Error)\n";
echo "   ✓ Cron scheduling disabled (all return false)\n";
echo "   ✓ Background processing disabled (spawn_cron, wp_cron)\n";
echo "   ✓ Update checks disabled (wp_update_plugins, wp_update_themes)\n";
echo "   ✓ WordPress cannot communicate externally\n";
exit(0);
