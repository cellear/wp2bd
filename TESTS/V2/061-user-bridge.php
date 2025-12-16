#!/usr/bin/env php
<?php
/**
 * Test script for WP4BD-V2-061: WordPress User Bridge
 *
 * Run from command line:
 *   php TESTS/V2/061-user-bridge.php
 *
 * Or from within ddev:
 *   ddev exec 'php /var/www/html/TESTS/V2/061-user-bridge.php'
 */

// Setup BACKDROP_ROOT for both environments
if (file_exists('/var/www/html/backdrop-1.30')) {
  define('BACKDROP_ROOT', '/var/www/html/backdrop-1.30');
} else {
  define('BACKDROP_ROOT', dirname(dirname(__DIR__)) . '/backdrop-1.30');
}

echo "Testing WordPress User Bridge...\n";
echo str_repeat("=", 60) . "\n\n";

// Test 1: Load bridge file
echo "1. Loading wp-user-bridge.php...\n";
$bridge_file = BACKDROP_ROOT . '/modules/wp_content/includes/wp-user-bridge.php';
if (file_exists($bridge_file)) {
  require_once $bridge_file;
  echo "   ✅ Loaded: wp-user-bridge.php\n\n";
} else {
  echo "   ❌ MISSING: $bridge_file\n";
  exit(1);
}

// Test 2: Mock Backdrop functions
echo "2. Setting up test environment...\n";
if (!function_exists('backdrop_strtolower')) {
  function backdrop_strtolower($str) {
    return strtolower($str);
  }
}
echo "   ✅ Test environment ready\n\n";

// Test 3: Verify bridge functions exist
echo "3. Verifying bridge functions exist...\n";
$required_functions = array(
  'wp4bd_user_to_wp_user',
  'wp4bd_sanitize_user_nicename',
  'wp4bd_users_to_wp_users',
);
$all_exist = true;
foreach ($required_functions as $func) {
  if (function_exists($func)) {
    echo "   ✅ Function exists: $func\n";
  } else {
    echo "   ❌ MISSING: $func\n";
    $all_exist = false;
  }
}
if (!$all_exist) {
  exit(1);
}
echo "\n";

// Test 4: Create mock Backdrop user account (administrator)
echo "4. Creating mock Backdrop administrator account...\n";
$mock_admin = (object) array(
  'uid' => 1,
  'name' => 'admin',
  'mail' => 'admin@example.com',
  'created' => time() - 86400,
  'access' => time(),
  'login' => time(),
  'status' => 1,
  'roles' => array('authenticated', 'administrator'),
  'realname' => 'Site Administrator',
);
echo "   ✅ Mock admin created (uid: {$mock_admin->uid})\n\n";

// Test 5: Convert admin to WP user
echo "5. Converting Backdrop administrator to WordPress user...\n";
$wp_admin = wp4bd_user_to_wp_user($mock_admin);
if ($wp_admin instanceof stdClass) {
  echo "   ✅ Conversion successful - returned user object\n\n";
} else {
  echo "   ❌ Conversion failed - did not return user object\n";
  var_dump($wp_admin);
  exit(1);
}

// Test 6: Verify admin user properties
echo "6. Verifying administrator user properties...\n";
$properties_correct = true;

// ID mapping
if ($wp_admin->ID === 1) {
  echo "   ✅ ID correctly mapped (1)\n";
} else {
  echo "   ❌ ID incorrect: {$wp_admin->ID}\n";
  $properties_correct = false;
}

// Login mapping
if ($wp_admin->user_login === 'admin') {
  echo "   ✅ Login correctly mapped (admin)\n";
} else {
  echo "   ❌ Login incorrect: {$wp_admin->user_login}\n";
  $properties_correct = false;
}

// Email mapping
if ($wp_admin->user_email === 'admin@example.com') {
  echo "   ✅ Email correctly mapped\n";
} else {
  echo "   ❌ Email incorrect: {$wp_admin->user_email}\n";
  $properties_correct = false;
}

// Display name mapping (from realname)
if ($wp_admin->display_name === 'Site Administrator') {
  echo "   ✅ Display name correctly mapped from realname\n";
} else {
  echo "   ❌ Display name incorrect: {$wp_admin->display_name}\n";
  $properties_correct = false;
}

// User level mapping (admin = 10)
if ($wp_admin->user_level === 10) {
  echo "   ✅ User level correctly mapped (10 for administrator)\n";
} else {
  echo "   ❌ User level incorrect: {$wp_admin->user_level}\n";
  $properties_correct = false;
}

if (!$properties_correct) {
  exit(1);
}
echo "\n";

// Test 7: Create mock editor user
echo "7. Testing editor role mapping...\n";
$mock_editor = (object) array(
  'uid' => 2,
  'name' => 'editor',
  'mail' => 'editor@example.com',
  'roles' => array('authenticated', 'editor'),
);
$wp_editor = wp4bd_user_to_wp_user($mock_editor);
if ($wp_editor->user_level === 7) {
  echo "   ✅ Editor user level correctly mapped (7)\n";
} else {
  echo "   ❌ Editor user level incorrect: {$wp_editor->user_level}\n";
  exit(1);
}
echo "\n";

// Test 8: Test display name fallback logic
echo "8. Testing display name fallback logic...\n";

// User with first/last name fields
$mock_user_names = (object) array(
  'uid' => 3,
  'name' => 'jdoe',
  'mail' => 'jdoe@example.com',
  'field_first_name' => array('und' => array(0 => array('value' => 'John'))),
  'field_last_name' => array('und' => array(0 => array('value' => 'Doe'))),
  'roles' => array('authenticated'),
);
$wp_user_names = wp4bd_user_to_wp_user($mock_user_names);
if (strpos($wp_user_names->display_name, 'John') !== false &&
    strpos($wp_user_names->display_name, 'Doe') !== false) {
  echo "   ✅ Display name from first/last name: {$wp_user_names->display_name}\n";
} else {
  echo "   ❌ Display name not built from first/last name: {$wp_user_names->display_name}\n";
  exit(1);
}

// User with only username (fallback)
$mock_user_simple = (object) array(
  'uid' => 4,
  'name' => 'testuser',
  'mail' => 'test@example.com',
  'roles' => array('authenticated'),
);
$wp_user_simple = wp4bd_user_to_wp_user($mock_user_simple);
if ($wp_user_simple->display_name === 'testuser') {
  echo "   ✅ Display name fallback to username: {$wp_user_simple->display_name}\n";
} else {
  echo "   ❌ Display name fallback failed: {$wp_user_simple->display_name}\n";
  exit(1);
}
echo "\n";

// Test 9: Test nicename sanitization
echo "9. Testing nicename sanitization...\n";
$test_cases = array(
  'John Doe' => 'john-doe',
  'user@example.com' => 'userexamplecom',
  'Test_User' => 'test_user',
);
$sanitization_correct = true;
foreach ($test_cases as $input => $expected) {
  $result = wp4bd_sanitize_user_nicename($input);
  if ($result === $expected) {
    echo "   ✅ '$input' → '$result'\n";
  } else {
    echo "   ❌ '$input' → '$result' (expected '$expected')\n";
    $sanitization_correct = false;
  }
}
if (!$sanitization_correct) {
  exit(1);
}
echo "\n";

// Test 10: Test NULL handling
echo "10. Testing NULL/invalid input handling...\n";
$null_result = wp4bd_user_to_wp_user(NULL);
if ($null_result === NULL) {
  echo "   ✅ NULL input handled correctly\n";
} else {
  echo "   ❌ NULL input not handled correctly\n";
  exit(1);
}

$invalid_result = wp4bd_user_to_wp_user((object) array());
if ($invalid_result === NULL) {
  echo "   ✅ Invalid user handled correctly\n";
} else {
  echo "   ❌ Invalid user not handled correctly\n";
  exit(1);
}
echo "\n";

echo str_repeat("=", 60) . "\n";
echo "✅ ALL TESTS PASSED - User Bridge Working!\n";
echo str_repeat("=", 60) . "\n";
exit(0);
