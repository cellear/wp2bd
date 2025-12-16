#!/usr/bin/env php
<?php
/**
 * Test script for WP4BD-V2-042: File Path Mapping
 *
 * Run from command line:
 *   php TESTS/V2/042-file-path-mapping.php
 *
 * Or from within ddev:
 *   ddev exec 'php /var/www/html/TESTS/V2/042-file-path-mapping.php'
 */

// Setup BACKDROP_ROOT for both environments
if (file_exists('/var/www/html/backdrop-1.30')) {
  define('BACKDROP_ROOT', '/var/www/html/backdrop-1.30');
  $GLOBALS['base_url'] = 'https://wp4bd.ddev.site';
} else {
  define('BACKDROP_ROOT', dirname(dirname(__DIR__)) . '/backdrop-1.30');
  $GLOBALS['base_url'] = 'http://localhost:8080';
}

// Define WordPress paths
$wp_root = BACKDROP_ROOT . '/themes/wp/wpbrain/';
if (!defined('ABSPATH')) {
  define('ABSPATH', $wp_root);
}
if (!defined('WPINC')) {
  define('WPINC', 'wp-includes');
}

echo "Testing File Path Mapping (wp_upload_dir)...\n";
echo str_repeat("=", 60) . "\n\n";

// Test 1: Load functions.php
echo "1. Loading functions.php...\n";
$functions_file = ABSPATH . WPINC . '/functions.php';
if (!file_exists($functions_file)) {
  echo "   ❌ MISSING: $functions_file\n";
  exit(1);
}
require_once $functions_file;
echo "   ✅ Loaded: functions.php\n\n";

// Test 2: Call wp_upload_dir() without arguments
echo "2. Testing wp_upload_dir() returns Backdrop paths...\n";
$upload_info = wp_upload_dir(null, false); // Don't create directories in test

if (!is_array($upload_info)) {
  echo "   ❌ wp_upload_dir() did not return an array\n";
  exit(1);
}
echo "   ✅ wp_upload_dir() returns array\n\n";

// Test 3: Verify required keys exist
echo "3. Verifying required keys in returned array...\n";
$required_keys = ['path', 'url', 'subdir', 'basedir', 'baseurl', 'error'];
$all_keys_found = true;
foreach ($required_keys as $key) {
  if (array_key_exists($key, $upload_info)) {
    echo "   ✅ Key exists: $key\n";
  } else {
    echo "   ❌ MISSING key: $key\n";
    $all_keys_found = false;
  }
}
echo "\n";

if (!$all_keys_found) {
  echo "❌ FAILED: Missing required keys\n";
  exit(1);
}

// Test 4: Verify paths point to Backdrop's files directory
echo "4. Verifying paths point to Backdrop's files/ directory...\n";

$expected_basedir = BACKDROP_ROOT . '/files';
if ($upload_info['basedir'] === $expected_basedir) {
  echo "   ✅ basedir: {$upload_info['basedir']}\n";
} else {
  echo "   ❌ basedir incorrect\n";
  echo "      Expected: $expected_basedir\n";
  echo "      Got: {$upload_info['basedir']}\n";
  exit(1);
}

$expected_baseurl = $GLOBALS['base_url'] . '/files';
if ($upload_info['baseurl'] === $expected_baseurl) {
  echo "   ✅ baseurl: {$upload_info['baseurl']}\n";
} else {
  echo "   ❌ baseurl incorrect\n";
  echo "      Expected: $expected_baseurl\n";
  echo "      Got: {$upload_info['baseurl']}\n";
  exit(1);
}

if ($upload_info['path'] === $expected_basedir) {
  echo "   ✅ path: {$upload_info['path']}\n";
} else {
  echo "   ❌ path incorrect (expected same as basedir when no time specified)\n";
  exit(1);
}

if ($upload_info['url'] === $expected_baseurl) {
  echo "   ✅ url: {$upload_info['url']}\n";
} else {
  echo "   ❌ url incorrect (expected same as baseurl when no time specified)\n";
  exit(1);
}

if ($upload_info['subdir'] === '') {
  echo "   ✅ subdir: empty (no time specified)\n";
} else {
  echo "   ❌ subdir should be empty when no time specified\n";
  exit(1);
}

if ($upload_info['error'] === false) {
  echo "   ✅ error: false (no errors)\n\n";
} else {
  echo "   ❌ error should be false\n";
  exit(1);
}

// Test 5: Test with timestamp (should add year/month subdirectory)
echo "5. Testing wp_upload_dir() with timestamp...\n";
$test_time = strtotime('2025-12-16'); // December 2025
$upload_info_with_time = wp_upload_dir($test_time, false);

$expected_subdir = '/2025/12';
if ($upload_info_with_time['subdir'] === $expected_subdir) {
  echo "   ✅ subdir with time: {$upload_info_with_time['subdir']}\n";
} else {
  echo "   ❌ subdir incorrect with time\n";
  echo "      Expected: $expected_subdir\n";
  echo "      Got: {$upload_info_with_time['subdir']}\n";
  exit(1);
}

$expected_path_with_time = $expected_basedir . '/2025/12';
if ($upload_info_with_time['path'] === $expected_path_with_time) {
  echo "   ✅ path with time: {$upload_info_with_time['path']}\n";
} else {
  echo "   ❌ path incorrect with time\n";
  exit(1);
}

$expected_url_with_time = $expected_baseurl . '/2025/12';
if ($upload_info_with_time['url'] === $expected_url_with_time) {
  echo "   ✅ url with time: {$upload_info_with_time['url']}\n\n";
} else {
  echo "   ❌ url incorrect with time\n";
  exit(1);
}

// Test 6: Verify this is NOT pointing to WordPress paths
echo "6. Verifying paths do NOT point to WordPress directories...\n";
if (strpos($upload_info['basedir'], 'wp-content') === false) {
  echo "   ✅ Does not use wp-content/uploads path\n";
} else {
  echo "   ❌ Still using wp-content/uploads (not mapped!)\n";
  exit(1);
}

if (strpos($upload_info['basedir'], 'wpbrain') === false) {
  echo "   ✅ Does not use wpbrain path\n\n";
} else {
  echo "   ❌ Still using wpbrain path (not mapped!)\n";
  exit(1);
}

// Summary
echo str_repeat("=", 60) . "\n";
echo "✅ ALL TESTS PASSED\n";
echo "   ✓ wp_upload_dir() returns Backdrop files/ directory\n";
echo "   ✓ All required keys present (path, url, subdir, basedir, baseurl, error)\n";
echo "   ✓ Subdirectories created correctly with timestamps\n";
echo "   ✓ WordPress upload paths successfully mapped to Backdrop\n";
exit(0);
