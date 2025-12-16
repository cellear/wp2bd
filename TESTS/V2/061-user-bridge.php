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

// Load WordPress bootstrap and user bridge
require_once BACKDROP_ROOT . '/modules/wp_content/includes/wp-bootstrap.php';
require_once BACKDROP_ROOT . '/modules/wp_content/includes/wp-user-bridge.php';

/**
 * Test basic user conversion.
 */
function test_basic_user_conversion() {
  echo "  Testing basic user conversion...\n";

  // Create a mock Backdrop user account
  $mock_account = (object) array(
    'uid' => 5,
    'name' => 'johndoe',
    'mail' => 'john@example.com',
    'created' => strtotime('2024-01-01 12:00:00'),
    'status' => 1,
    'roles' => array(
      2 => 'authenticated user',
      4 => 'editor', // Backdrop role
    ),
    'field_display_name' => array(
      'und' => array(
        array('value' => 'John Doe')
      )
    ),
  );

  $wp_user = wp4bd_backdrop_user_to_wp_user($mock_account);

  assert(is_object($wp_user), 'Result should be an object');
  assert($wp_user->ID === 5, 'ID should match uid');
  assert($wp_user->user_login === 'johndoe', 'Login should match name');
  assert($wp_user->user_email === 'john@example.com', 'Email should match mail');
  assert($wp_user->display_name === 'John Doe', 'Display name should be extracted');
  assert($wp_user->user_nicename === 'john-doe', 'Nicename should be sanitized');
  assert($wp_user->user_status === 1, 'Status should match');
  assert(in_array('editor', $wp_user->roles), 'Should have editor role');
  assert($wp_user->first_name === 'John', 'First name should be extracted');
  assert($wp_user->last_name === 'Doe', 'Last name should be extracted');

  echo "  ✅ Basic user properties verified\n";
  echo "    👤 Converted user: ID={$wp_user->ID}, Login='{$wp_user->user_login}', Display='{$wp_user->display_name}', Email='{$wp_user->user_email}', Roles=[" . implode(',', $wp_user->roles) . "]\n";
}

/**
 * Test user without display name field.
 */
function test_user_without_display_name() {
  echo "  Testing user without display name...\n";

  $mock_account = (object) array(
    'uid' => 10,
    'name' => 'janedoe',
    'mail' => 'jane@example.com',
    'created' => time(),
    'status' => 1,
  );

  $wp_user = wp4bd_backdrop_user_to_wp_user($mock_account);

  assert($wp_user->display_name === 'janedoe', 'Display name should fallback to login');
  assert($wp_user->user_nicename === 'janedoe', 'Nicename should match login');
  assert($wp_user->first_name === '', 'First name should be empty');
  assert($wp_user->last_name === '', 'Last name should be empty');

  echo "  ✅ Fallback display name handling verified\n";
}

/**
 * Test administrator user (uid 1).
 */
function test_admin_user() {
  echo "  Testing administrator user...\n";

  $mock_account = (object) array(
    'uid' => 1,
    'name' => 'admin',
    'mail' => 'admin@example.com',
    'created' => time(),
    'status' => 1,
  );

  $wp_user = wp4bd_backdrop_user_to_wp_user($mock_account);

  assert(in_array('administrator', $wp_user->roles), 'User 1 should be administrator');

  echo "  ✅ Administrator role assignment verified\n";
}

/**
 * Test invalid user handling.
 */
function test_invalid_user() {
  echo "  Testing invalid user handling...\n";

  $invalid_user = "not an object";
  $result = wp4bd_backdrop_user_to_wp_user($invalid_user);
  assert($result === null, 'Invalid user should return null');

  $empty_object = (object) array();
  $result = wp4bd_backdrop_user_to_wp_user($empty_object);
  assert($result === null, 'User without uid should return null');

  echo "  ✅ Invalid user handling verified\n";
}

/**
 * Test batch user conversion.
 */
function test_batch_user_conversion() {
  echo "  Testing batch user conversion...\n";

  $accounts = array(
    (object) array('uid' => 1, 'name' => 'admin', 'mail' => 'admin@test.com'),
    (object) array('uid' => 2, 'name' => 'user1', 'mail' => 'user1@test.com'),
    (object) array('uid' => 3, 'name' => 'user2', 'mail' => 'user2@test.com'),
  );

  $wp_users = wp4bd_backdrop_users_to_wp_users($accounts);

  assert(is_array($wp_users), 'Result should be an array');
  assert(count($wp_users) === 3, 'Should have 3 users');

  echo "  👥 Batch conversion created " . count($wp_users) . " WordPress users:\n";
  foreach ($wp_users as $i => $wp_user) {
    assert(is_object($wp_user), "User $i should be an object");
    assert($wp_user->ID === ($i + 1), "User $i ID should match");
    echo "    👤 User " . ($i + 1) . ": ID={$wp_user->ID}, Login='{$wp_user->user_login}', Display='{$wp_user->display_name}', Roles=[" . implode(',', $wp_user->roles) . "]\n";
  }

  echo "  ✅ Batch user conversion verified\n";
}

/**
 * Test role mapping.
 */
function test_role_mapping() {
  echo "  Testing role mapping...\n";

  // Test subscriber (default)
  $subscriber = (object) array('uid' => 100, 'name' => 'sub', 'roles' => array(2 => 'authenticated user'));
  $wp_user = wp4bd_backdrop_user_to_wp_user($subscriber);
  assert(in_array('subscriber', $wp_user->roles), 'Default role should be subscriber');

  // Test author role
  $author = (object) array('uid' => 101, 'name' => 'author', 'roles' => array(5 => 'author'));
  $wp_user = wp4bd_backdrop_user_to_wp_user($author);
  assert(in_array('author', $wp_user->roles), 'Author role should be mapped');

  echo "  ✅ Role mapping verified\n";
}

// Run tests
try {
  test_basic_user_conversion();
  test_user_without_display_name();
  test_admin_user();
  test_invalid_user();
  test_batch_user_conversion();
  test_role_mapping();
  echo "\n🎉 All V2-061 tests passed!\n";
} catch (Exception $e) {
  echo "❌ V2-061 Test failed: " . $e->getMessage() . "\n";
  exit(1);
} catch (Error $e) {
  echo "❌ V2-061 Fatal error: " . $e->getMessage() . "\n";
  exit(1);
}
