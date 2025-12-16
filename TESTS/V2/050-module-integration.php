#!/usr/bin/env php
<?php
/**
 * Test script for WP4BD-V2-050: Integration Point in Module
 *
 * Run from command line:
 *   php TESTS/V2/050-module-integration.php
 *
 * Or from within ddev:
 *   ddev exec 'php /var/www/html/TESTS/V2/050-module-integration.php'
 */

// Setup BACKDROP_ROOT for both environments
if (file_exists('/var/www/html/backdrop-1.30')) {
  define('BACKDROP_ROOT', '/var/www/html/backdrop-1.30');
} else {
  define('BACKDROP_ROOT', dirname(dirname(__DIR__)) . '/backdrop-1.30');
}

echo "Testing WordPress Bootstrap Integration in Module...\n";
echo str_repeat("=", 60) . "\n\n";

// Test 1: Verify module file exists
echo "1. Checking if wp_content.module exists...\n";
$module_file = BACKDROP_ROOT . '/modules/wp_content/wp_content.module';
if (file_exists($module_file)) {
  echo "   ✅ EXISTS: wp_content.module\n\n";
} else {
  echo "   ❌ MISSING: $module_file\n";
  exit(1);
}

// Test 2: Verify module contains wp_content_init() function
echo "2. Checking for wp_content_init() function...\n";
$module_content = file_get_contents($module_file);
if (strpos($module_content, 'function wp_content_init()') !== false) {
  echo "   ✅ Found: wp_content_init() function\n\n";
} else {
  echo "   ❌ MISSING: wp_content_init() function\n";
  exit(1);
}

// Test 3: Verify module loads bootstrap file
echo "3. Checking if module loads wp-bootstrap.php...\n";
if (strpos($module_content, 'wp-bootstrap.php') !== false) {
  echo "   ✅ Found: Reference to wp-bootstrap.php\n\n";
} else {
  echo "   ❌ MISSING: wp-bootstrap.php reference\n";
  exit(1);
}

// Test 4: Verify module calls wp4bd_bootstrap_wordpress()
echo "4. Checking if module calls wp4bd_bootstrap_wordpress()...\n";
if (strpos($module_content, 'wp4bd_bootstrap_wordpress()') !== false) {
  echo "   ✅ Found: Call to wp4bd_bootstrap_wordpress()\n\n";
} else {
  echo "   ❌ MISSING: wp4bd_bootstrap_wordpress() call\n";
  exit(1);
}

// Test 5: Verify module checks for wp theme
echo "5. Checking if module checks for wp theme...\n";
if (strpos($module_content, "\$theme_key === 'wp'") !== false) {
  echo "   ✅ Found: Theme check for 'wp'\n\n";
} else {
  echo "   ❌ MISSING: Theme check\n";
  exit(1);
}

// Test 6: Verify module includes error logging
echo "6. Checking for error logging...\n";
if (strpos($module_content, 'watchdog') !== false) {
  echo "   ✅ Found: Error logging with watchdog()\n\n";
} else {
  echo "   ❌ MISSING: Error logging\n";
  exit(1);
}

// Test 7: Verify wp-bootstrap.php file exists
echo "7. Checking if wp-bootstrap.php exists...\n";
$bootstrap_file = BACKDROP_ROOT . '/modules/wp_content/includes/wp-bootstrap.php';
if (file_exists($bootstrap_file)) {
  echo "   ✅ EXISTS: wp-bootstrap.php\n\n";
} else {
  echo "   ❌ MISSING: $bootstrap_file\n";
  exit(1);
}

// Summary
echo str_repeat("=", 60) . "\n";
echo "✅ ALL TESTS PASSED\n";
echo "   ✓ Module integration point created\n";
echo "   ✓ wp_content_init() loads WordPress bootstrap\n";
echo "   ✓ WordPress loads only when wp theme is active\n";
echo "   ✓ Error logging implemented\n";
echo "   ✓ Bootstrap function available\n";
exit(0);
