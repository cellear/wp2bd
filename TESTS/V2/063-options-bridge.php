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
  define('BACKDROP_ROOT', dirname(dirname(__DIR__)) . '/backdrop-1.30');
}

echo "Testing WordPress Options/Settings Bridge...\n";
echo str_repeat("=", 60) . "\n\n";

// Test 1: Load bridge file
echo "1. Loading wp-options-bridge.php...\n";
$bridge_file = BACKDROP_ROOT . '/modules/wp_content/includes/wp-options-bridge.php';
if (file_exists($bridge_file)) {
  require_once $bridge_file;
  echo "   ✅ Loaded: wp-options-bridge.php\n\n";
} else {
  echo "   ❌ MISSING: $bridge_file\n";
  exit(1);
}

// Test 2: Mock Backdrop config_get function
echo "2. Setting up test environment with mock config...\n";
$mock_config = array(
  'system.core' => array(
    'site_name' => 'Test Site Name',
    'site_slogan' => 'A test slogan for the site',
    'site_mail' => 'admin@testsite.com',
    'base_url' => 'http://testsite.local',
    'language_default' => 'en',
  ),
  'system.date' => array(
    'default_timezone' => 'America/New_York',
    'date_format_short' => 'm/d/Y',
  ),
  'system.theme' => array(
    'default' => 'wp',
  ),
);

if (!function_exists('config_get')) {
  function config_get($config_file, $key = NULL) {
    global $mock_config;
    if (!isset($mock_config[$config_file])) {
      return NULL;
    }
    if ($key === NULL) {
      return $mock_config[$config_file];
    }
    return isset($mock_config[$config_file][$key]) ? $mock_config[$config_file][$key] : NULL;
  }
}
echo "   ✅ Test environment ready with mock config\n\n";

// Test 3: Verify bridge functions exist
echo "3. Verifying bridge functions exist...\n";
$required_functions = array(
  'wp4bd_get_option',
  'wp4bd_get_options',
  'wp4bd_has_option',
  'wp4bd_config_to_option',
  'wp4bd_get_theme_mod',
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

// Test 4: Test basic option mappings
echo "4. Testing basic WordPress option mappings...\n";
$option_tests = array(
  'blogname' => 'Test Site Name',
  'blogdescription' => 'A test slogan for the site',
  'admin_email' => 'admin@testsite.com',
  'siteurl' => 'http://testsite.local',
  'home' => 'http://testsite.local',
);
$mappings_correct = true;
foreach ($option_tests as $option => $expected) {
  $result = wp4bd_get_option($option);
  if ($result === $expected) {
    echo "   ✅ $option: $result\n";
  } else {
    echo "   ❌ $option: got '$result', expected '$expected'\n";
    $mappings_correct = false;
  }
}
if (!$mappings_correct) {
  exit(1);
}
echo "\n";

// Test 5: Test static value options
echo "5. Testing static value options...\n";
$static_tests = array(
  'default_comment_status' => 'open',
  'default_ping_status' => 'open',
  'default_pingback_flag' => 0,
  'blog_public' => 1,
  'blog_charset' => 'UTF-8',
);
$static_correct = true;
foreach ($static_tests as $option => $expected) {
  $result = wp4bd_get_option($option);
  if ($result === $expected) {
    echo "   ✅ $option: $result\n";
  } else {
    echo "   ❌ $option: got '$result', expected '$expected'\n";
    $static_correct = false;
  }
}
if (!$static_correct) {
  exit(1);
}
echo "\n";

// Test 6: Test timezone handling
echo "6. Testing timezone conversion...\n";
$gmt_offset = wp4bd_get_option('gmt_offset');
if (is_numeric($gmt_offset)) {
  echo "   ✅ GMT offset calculated: $gmt_offset hours\n";
  echo "      (America/New_York timezone)\n";
} else {
  echo "   ❌ GMT offset not calculated correctly\n";
  exit(1);
}
echo "\n";

// Test 7: Test default value handling
echo "7. Testing default value handling...\n";
$default_test = wp4bd_get_option('nonexistent_option', 'my_default');
if ($default_test === 'my_default') {
  echo "   ✅ Default value returned for nonexistent option\n";
} else {
  echo "   ❌ Default value not returned: $default_test\n";
  exit(1);
}
echo "\n";

// Test 8: Test wp4bd_has_option function
echo "8. Testing option existence check...\n";
$has_blogname = wp4bd_has_option('blogname');
$has_fake = wp4bd_has_option('totally_fake_option_xyz');
if ($has_blogname === true && $has_fake === false) {
  echo "   ✅ Option existence check working\n";
  echo "      blogname exists: true\n";
  echo "      totally_fake_option_xyz exists: false\n";
} else {
  echo "   ❌ Option existence check failed\n";
  echo "      blogname: " . var_export($has_blogname, true) . "\n";
  echo "      fake option: " . var_export($has_fake, true) . "\n";
  exit(1);
}
echo "\n";

// Test 9: Test wp4bd_get_options batch function
echo "9. Testing batch option retrieval...\n";
$options = wp4bd_get_options(array('blogname', 'admin_email', 'blog_charset'));
if (is_array($options) && count($options) === 3) {
  echo "   ✅ Batch retrieval successful\n";
  echo "      blogname: {$options['blogname']}\n";
  echo "      admin_email: {$options['admin_email']}\n";
  echo "      blog_charset: {$options['blog_charset']}\n";

  if ($options['blogname'] === 'Test Site Name' &&
      $options['admin_email'] === 'admin@testsite.com' &&
      $options['blog_charset'] === 'UTF-8') {
    echo "   ✅ All batch values correct\n";
  } else {
    echo "   ❌ Some batch values incorrect\n";
    exit(1);
  }
} else {
  echo "   ❌ Batch retrieval failed\n";
  var_dump($options);
  exit(1);
}
echo "\n";

// Test 10: Test wp4bd_config_to_option direct mapping
echo "10. Testing direct config-to-option helper...\n";
$direct_value = wp4bd_config_to_option('system.core', 'site_name', 'fallback');
if ($direct_value === 'Test Site Name') {
  echo "   ✅ Direct config mapping: $direct_value\n";
} else {
  echo "   ❌ Direct config mapping failed: $direct_value\n";
  exit(1);
}

$missing_value = wp4bd_config_to_option('fake.config', 'fake_key', 'my_fallback');
if ($missing_value === 'my_fallback') {
  echo "   ✅ Missing config returns fallback: $missing_value\n";
} else {
  echo "   ❌ Missing config fallback failed: $missing_value\n";
  exit(1);
}
echo "\n";

// Test 11: Test language/locale mapping
echo "11. Testing language option mapping...\n";
$lang = wp4bd_get_option('WPLANG');
if ($lang === 'en') {
  echo "   ✅ Language option (WPLANG): $lang\n";
} else {
  echo "   ❌ Language option incorrect: $lang\n";
  exit(1);
}
echo "\n";

// Test 12: Test theme option mapping
echo "12. Testing theme option mapping...\n";
$template = wp4bd_get_option('template');
$stylesheet = wp4bd_get_option('stylesheet');
if ($template === 'wp' && $stylesheet === 'wp') {
  echo "   ✅ Theme options mapped correctly\n";
  echo "      template: $template\n";
  echo "      stylesheet: $stylesheet\n";
} else {
  echo "   ❌ Theme options incorrect\n";
  echo "      template: $template\n";
  echo "      stylesheet: $stylesheet\n";
  exit(1);
}
echo "\n";

// Test 13: Test permalink structure
echo "13. Testing permalink structure option...\n";
$permalink = wp4bd_get_option('permalink_structure');
if (!empty($permalink)) {
  echo "   ✅ Permalink structure: $permalink\n";
} else {
  echo "   ❌ Permalink structure empty or missing\n";
  exit(1);
}
echo "\n";

echo str_repeat("=", 60) . "\n";
echo "✅ ALL TESTS PASSED - Options Bridge Working!\n";
echo str_repeat("=", 60) . "\n";
exit(0);
