#!/usr/bin/env php
<?php
/**
 * Test script for WP4BD-V2-040: Identify External I/O Functions
 *
 * Run from command line:
 *   php TESTS/V2/040-io-functions-inventory.php
 *
 * Or from within ddev:
 *   ddev exec 'php /var/www/html/TESTS/V2/040-io-functions-inventory.php'
 */

// Setup paths
if (file_exists('/var/www/html')) {
  define('REPO_ROOT', '/var/www/html');
} else {
  define('REPO_ROOT', dirname(dirname(__DIR__)));
}

echo "Testing I/O Functions Inventory Documentation...\n";
echo str_repeat("=", 60) . "\n\n";

// Test 1: Verify inventory documentation exists
echo "1. Checking if inventory documentation exists...\n";
$doc_file = REPO_ROOT . '/DOCS/V2/dec16-EPIC-5-IO-FUNCTIONS-INVENTORY.md';
if (file_exists($doc_file)) {
  echo "   ✅ EXISTS: dec16-EPIC-5-IO-FUNCTIONS-INVENTORY.md\n\n";
} else {
  echo "   ❌ MISSING: $doc_file\n";
  exit(1);
}

// Test 2: Verify documentation contains expected sections
echo "2. Verifying documentation contains required sections...\n";
$content = file_get_contents($doc_file);

$required_sections = [
  'HTTP/Remote Communication' => 'Category 1: HTTP',
  'Cron/Background Processing' => 'Category 2: Cron',
  'Update/Upgrade System' => 'Category 3: Update',
  'File System Operations' => 'Category 4: File System',
];

$all_found = true;
foreach ($required_sections as $name => $pattern) {
  if (strpos($content, $pattern) !== false) {
    echo "   ✅ Found: $name\n";
  } else {
    echo "   ❌ MISSING: $name\n";
    $all_found = false;
  }
}
echo "\n";

if (!$all_found) {
  echo "❌ FAILED: Documentation missing required sections\n";
  exit(1);
}

// Test 3: Verify HTTP functions are documented
echo "3. Checking HTTP functions are documented...\n";
$http_functions = ['wp_remote_request', 'wp_remote_get', 'wp_remote_post', 'wp_remote_head'];
foreach ($http_functions as $func) {
  if (strpos($content, $func) !== false) {
    echo "   ✅ Documented: $func()\n";
  } else {
    echo "   ❌ MISSING: $func()\n";
    $all_found = false;
  }
}
echo "\n";

// Test 4: Verify cron functions are documented
echo "4. Checking cron functions are documented...\n";
$cron_functions = ['wp_schedule_single_event', 'wp_schedule_event', 'spawn_cron', 'wp_cron'];
foreach ($cron_functions as $func) {
  if (strpos($content, $func) !== false) {
    echo "   ✅ Documented: $func()\n";
  } else {
    echo "   ❌ MISSING: $func()\n";
    $all_found = false;
  }
}
echo "\n";

// Test 5: Verify update functions are documented
echo "5. Checking update functions are documented...\n";
$update_functions = ['wp_update_plugins', 'wp_update_themes'];
foreach ($update_functions as $func) {
  if (strpos($content, $func) !== false) {
    echo "   ✅ Documented: $func()\n";
  } else {
    echo "   ❌ MISSING: $func()\n";
    $all_found = false;
  }
}
echo "\n";

// Test 6: Verify file path functions are documented
echo "6. Checking file path functions are documented...\n";
if (strpos($content, 'wp_upload_dir') !== false) {
  echo "   ✅ Documented: wp_upload_dir()\n\n";
} else {
  echo "   ❌ MISSING: wp_upload_dir()\n";
  $all_found = false;
}

if ($all_found) {
  echo str_repeat("=", 60) . "\n";
  echo "✅ ALL TESTS PASSED\n";
  echo "   I/O functions inventory is complete and documented\n";
  exit(0);
} else {
  echo str_repeat("=", 60) . "\n";
  echo "❌ SOME TESTS FAILED\n";
  exit(1);
}
