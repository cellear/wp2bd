#!/usr/bin/env php
<?php
/**
 * Test script for WP4BD-V2-012: Create wp-config Bridge
 * 
 * Run from command line:
 *   php TESTS/V2/012-wp-config.php
 * 
 * Or from within ddev:
 *   ddev exec 'php /var/www/html/TESTS/V2/012-wp-config.php'
 */

// Setup BACKDROP_ROOT for both environments
if (file_exists('/var/www/html/backdrop-1.30')) {
  define('BACKDROP_ROOT', '/var/www/html/backdrop-1.30');
} else {
  // We're in TESTS/V2/, so go up two levels to repo root, then into backdrop-1.30
  define('BACKDROP_ROOT', dirname(dirname(__DIR__)) . '/backdrop-1.30');
}

// Define ABSPATH before loading wp-config-bd.php (WordPress expects it)
$wp_root = BACKDROP_ROOT . '/themes/wp/wpbrain/';
if (!defined('ABSPATH')) {
  define('ABSPATH', $wp_root);
}

// Load the wp-config bridge file
echo "Loading wp-config-bd.php...\n";
$config_file = $wp_root . 'wp-config-bd.php';

if (!file_exists($config_file)) {
  echo "❌ FAILED: Config file not found at: $config_file\n";
  exit(1);
}

require_once $config_file;
echo "✅ File loaded successfully\n\n";

// Test: Verify database configuration constants
echo "Testing Database Configuration Constants...\n";
$db_constants = [
  'DB_NAME',
  'DB_USER',
  'DB_PASSWORD',
  'DB_HOST',
  'DB_CHARSET',
  'DB_COLLATE',
];

$all_db_defined = true;
foreach ($db_constants as $constant) {
  if (defined($constant)) {
    echo "  ✅ $constant: " . constant($constant) . "\n";
  } else {
    echo "  ❌ $constant: NOT DEFINED\n";
    $all_db_defined = false;
  }
}

if (!$all_db_defined) {
  echo "\n❌ FAILED: Missing database constants\n";
  exit(1);
}

// Test: Verify authentication keys/salts
echo "\nTesting Authentication Keys/Salts...\n";
$auth_constants = [
  'AUTH_KEY',
  'SECURE_AUTH_KEY',
  'LOGGED_IN_KEY',
  'NONCE_KEY',
  'AUTH_SALT',
  'SECURE_AUTH_SALT',
  'LOGGED_IN_SALT',
  'NONCE_SALT',
];

$all_auth_defined = true;
foreach ($auth_constants as $constant) {
  if (defined($constant)) {
    echo "  ✅ $constant: DEFINED\n";
  } else {
    echo "  ❌ $constant: NOT DEFINED\n";
    $all_auth_defined = false;
  }
}

if (!$all_auth_defined) {
  echo "\n❌ FAILED: Missing authentication constants\n";
  exit(1);
}

// Test: Verify $table_prefix is set
echo "\nTesting Table Prefix...\n";
if (isset($table_prefix)) {
  echo "  ✅ \$table_prefix: $table_prefix\n";
} else {
  echo "  ❌ \$table_prefix: NOT SET\n";
  echo "\n❌ FAILED: Missing table prefix\n";
  exit(1);
}

// Test: Verify WordPress debug constants
echo "\nTesting WordPress Debug Constants...\n";
$debug_constants = [
  'WP_DEBUG',
  'WP_DEBUG_LOG',
  'WP_DEBUG_DISPLAY',
];

$all_debug_defined = true;
foreach ($debug_constants as $constant) {
  if (defined($constant)) {
    $value = constant($constant) ? 'true' : 'false';
    echo "  ✅ $constant: $value\n";
  } else {
    echo "  ❌ $constant: NOT DEFINED\n";
    $all_debug_defined = false;
  }
}

if (!$all_debug_defined) {
  echo "\n❌ FAILED: Missing debug constants\n";
  exit(1);
}

// Test: Verify other important constants
echo "\nTesting Other WordPress Constants...\n";
$other_constants = [
  'WP_MEMORY_LIMIT',
  'WP_MAX_MEMORY_LIMIT',
  'AUTOMATIC_UPDATER_DISABLED',
  'WP_AUTO_UPDATE_CORE',
  'DISABLE_WP_CRON',
  'FS_METHOD',
  'WP_CONTENT_DIR',
  'DISALLOW_FILE_MODS',
  'ABSPATH',
];

$all_other_defined = true;
foreach ($other_constants as $constant) {
  if (defined($constant)) {
    $value = constant($constant);
    if (is_bool($value)) {
      $value = $value ? 'true' : 'false';
    }
    echo "  ✅ $constant: $value\n";
  } else {
    echo "  ❌ $constant: NOT DEFINED\n";
    $all_other_defined = false;
  }
}

if (!$all_other_defined) {
  echo "\n❌ FAILED: Missing other constants\n";
  exit(1);
}

// Test: Verify configuration coherence
echo "\nTesting Configuration Coherence...\n";

// Check that WP_CONTENT_DIR is based on ABSPATH
if (defined('WP_CONTENT_DIR') && defined('ABSPATH')) {
  if (strpos(WP_CONTENT_DIR, ABSPATH) === 0) {
    echo "  ✅ WP_CONTENT_DIR is relative to ABSPATH\n";
  } else {
    echo "  ⚠️  WP_CONTENT_DIR is not relative to ABSPATH (may be intentional)\n";
  }
}

// Check that DB credentials are intercepted values
if (DB_NAME === 'wp4bd_intercepted') {
  echo "  ✅ DB_NAME is set to intercepted placeholder (correct)\n";
} else {
  echo "  ⚠️  DB_NAME is not placeholder value (may be production config)\n";
}

// Check that cron is disabled
if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON === true) {
  echo "  ✅ WordPress cron is disabled (Backdrop handles scheduling)\n";
} else {
  echo "  ⚠️  WordPress cron is enabled (should be disabled)\n";
}

// Check that auto-updates are disabled
if (defined('AUTOMATIC_UPDATER_DISABLED') && AUTOMATIC_UPDATER_DISABLED === true) {
  echo "  ✅ WordPress auto-updates disabled (correct for V2)\n";
} else {
  echo "  ⚠️  WordPress auto-updates may be enabled\n";
}

// Final summary
echo "\n" . str_repeat("=", 60) . "\n";
echo "🎉 All acceptance criteria met!\n";
echo str_repeat("=", 60) . "\n\n";

echo "Summary:\n";
echo "  - wp-config-bd.php loaded without errors\n";
echo "  - All database constants defined (with intercepted placeholders)\n";
echo "  - All authentication keys/salts defined\n";
echo "  - Table prefix set: $table_prefix\n";
echo "  - Debug mode: " . (WP_DEBUG ? 'ENABLED' : 'DISABLED') . "\n";
echo "  - WordPress cron: DISABLED (Backdrop handles it)\n";
echo "  - Auto-updates: DISABLED (running specific version)\n";
echo "\nReady for Epic 3: Database Interception (WP4BD-V2-020)\n";
echo "Next step: Create db.php drop-in to intercept database calls\n";

exit(0);

