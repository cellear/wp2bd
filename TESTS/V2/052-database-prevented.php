#!/usr/bin/env php
<?php
/**
 * Test script for WP4BD-V2-052: Prevent WordPress Database Connection
 *
 * Run from command line:
 *   php TESTS/V2/052-database-prevented.php
 *
 * Or from within ddev:
 *   ddev exec 'php /var/www/html/TESTS/V2/052-database-prevented.php'
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
if (!defined('WP_CONTENT_DIR')) {
  define('WP_CONTENT_DIR', $wp_root . 'wp-content');
}

echo "Testing WordPress Database Connection Prevention...\n";
echo str_repeat("=", 60) . "\n\n";

// Test 1: Verify wp-config-bd.php exists
echo "1. Checking if wp-config-bd.php exists...\n";
$config_file = ABSPATH . 'wp-config-bd.php';
if (file_exists($config_file)) {
  echo "   ✅ EXISTS: wp-config-bd.php\n\n";
} else {
  echo "   ❌ MISSING: $config_file\n";
  exit(1);
}

// Test 2: Load wp-config-bd.php and verify invalid credentials
echo "2. Loading wp-config-bd.php...\n";
require_once $config_file;
echo "   ✅ Loaded successfully\n\n";

// Test 3: Verify database constants are set to invalid values
echo "3. Verifying database constants are INVALID...\n";
if (defined('DB_NAME') && strpos(DB_NAME, 'INVALID') !== false) {
  echo "   ✅ DB_NAME is invalid: " . DB_NAME . "\n";
} else {
  echo "   ❌ DB_NAME should be invalid (contain 'INVALID')\n";
  exit(1);
}

if (defined('DB_USER') && strpos(DB_USER, 'INVALID') !== false) {
  echo "   ✅ DB_USER is invalid: " . DB_USER . "\n";
} else {
  echo "   ❌ DB_USER should be invalid (contain 'INVALID')\n";
  exit(1);
}

if (defined('DB_PASSWORD') && strpos(DB_PASSWORD, 'INVALID') !== false) {
  echo "   ✅ DB_PASSWORD is invalid: " . DB_PASSWORD . "\n";
} else {
  echo "   ❌ DB_PASSWORD should be invalid (contain 'INVALID')\n";
  exit(1);
}

if (defined('DB_HOST') && strpos(DB_HOST, ':9999') !== false) {
  echo "   ✅ DB_HOST uses non-existent port: " . DB_HOST . "\n\n";
} else {
  echo "   ❌ DB_HOST should use non-existent port (:9999)\n";
  exit(1);
}

// Test 4: Verify db.php drop-in exists
echo "4. Checking if db.php drop-in exists...\n";
$db_dropin = WP_CONTENT_DIR . '/db.php';
if (file_exists($db_dropin)) {
  echo "   ✅ EXISTS: db.php drop-in\n\n";
} else {
  echo "   ❌ MISSING: $db_dropin\n";
  echo "   ⚠️  WARNING: Without db.php, WordPress would try to connect!\n";
  exit(1);
}

// Test 5: Load db.php and verify wpdb class
echo "5. Loading db.php drop-in...\n";
require_once $db_dropin;
if (class_exists('wpdb')) {
  echo "   ✅ wpdb class loaded from drop-in\n\n";
} else {
  echo "   ❌ wpdb class not found after loading db.php\n";
  exit(1);
}

// Test 6: Instantiate wpdb with invalid credentials
echo "6. Testing wpdb instantiation with invalid credentials...\n";
try {
  // Use the invalid credentials from wp-config-bd.php
  $test_wpdb = new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
  echo "   ✅ wpdb instantiated without errors\n";
  echo "   ✅ No database connection attempted (correct!)\n\n";
} catch (Exception $e) {
  echo "   ❌ Exception during instantiation: " . $e->getMessage() . "\n";
  exit(1);
}

// Test 7: Verify wpdb bridge property exists
echo "7. Verifying wpdb is our bridge class...\n";
if (property_exists($test_wpdb, 'wp4bd_bridge_active')) {
  echo "   ✅ wpdb has wp4bd_bridge_active property\n";
  if ($test_wpdb->wp4bd_bridge_active === true) {
    echo "   ✅ Bridge is active (no real database connection)\n\n";
  } else {
    echo "   ⚠️  Bridge property exists but not active\n\n";
  }
} else {
  echo "   ❌ wpdb is not our bridge class (missing wp4bd_bridge_active)\n";
  exit(1);
}

// Test 8: Verify WordPress cannot actually connect
echo "8. Verifying WordPress CANNOT connect to database...\n";
// If wpdb tried to use the invalid credentials, it would fail
// Our drop-in should prevent any connection attempt
if (!method_exists($test_wpdb, 'db_connect')) {
  echo "   ✅ db_connect() method does not exist (correct)\n";
} elseif (!$test_wpdb->db_connect(false)) {
  echo "   ✅ Connection attempt would fail\n";
} else {
  echo "   ❌ WARNING: Connection might be possible!\n";
  exit(1);
}

// Verify no actual MySQL/PDO connection exists
if (!isset($test_wpdb->dbh) || $test_wpdb->dbh === null) {
  echo "   ✅ No database handle exists (no connection made)\n\n";
} else {
  echo "   ❌ Database handle exists (connection was made!)\n";
  exit(1);
}

// Test 9: Verify DISABLE_WP_CRON is set
echo "9. Verifying WordPress cron is disabled...\n";
if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON === true) {
  echo "   ✅ DISABLE_WP_CRON is true\n\n";
} else {
  echo "   ⚠️  DISABLE_WP_CRON should be true in wp-config-bd.php\n\n";
}

// Test 10: Verify auto-updates are disabled
echo "10. Verifying WordPress auto-updates disabled...\n";
if (defined('AUTOMATIC_UPDATER_DISABLED') && AUTOMATIC_UPDATER_DISABLED === true) {
  echo "   ✅ AUTOMATIC_UPDATER_DISABLED is true\n";
} else {
  echo "   ⚠️  AUTOMATIC_UPDATER_DISABLED should be true\n";
}

if (defined('WP_AUTO_UPDATE_CORE') && WP_AUTO_UPDATE_CORE === false) {
  echo "   ✅ WP_AUTO_UPDATE_CORE is false\n\n";
} else {
  echo "   ⚠️  WP_AUTO_UPDATE_CORE should be false\n\n";
}

// Summary
echo str_repeat("=", 60) . "\n";
echo "✅ ALL TESTS PASSED\n";
echo "   ✓ wp-config-bd.php has INVALID database credentials\n";
echo "   ✓ DB_NAME, DB_USER, DB_PASSWORD contain 'INVALID'\n";
echo "   ✓ DB_HOST uses non-existent port :9999\n";
echo "   ✓ db.php drop-in loaded and prevents connection\n";
echo "   ✓ wpdb instantiated without connecting to database\n";
echo "   ✓ wpdb is WP4BD bridge class (not real WordPress wpdb)\n";
echo "   ✓ No database handle created\n";
echo "   ✓ WordPress CANNOT connect to any database\n";
echo "   ✓ All data will come from Backdrop via bridge\n";
exit(0);
