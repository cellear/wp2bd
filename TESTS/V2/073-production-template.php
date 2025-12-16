#!/usr/bin/env php
<?php
/**
 * Test script for WP4BD-V2-073: Create Production Template
 *
 * Run from command line:
 *   php TESTS/V2/073-production-template.php
 *
 * Or from within ddev:
 *   ddev exec 'php /var/www/html/TESTS/V2/073-production-template.php'
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

// Load WordPress bootstrap and debug functions
require_once BACKDROP_ROOT . '/modules/wp_content/includes/wp-bootstrap.php';
require_once BACKDROP_ROOT . '/modules/wp_content/wp4bd_debug.inc';

/**
 * Test debug level detection.
 */
function test_debug_level_detection() {
  echo "  Testing debug level detection...\n";

  // Test no debug parameter
  unset($_GET['wp4bd_debug']);
  wp4bd_debug_init();
  $level = wp4bd_debug_get_level();
  assert($level === 0, 'No debug parameter should result in level 0');
  echo "    🔍 No debug param: Level $level\n";

  // Test debug level 1
  $_GET['wp4bd_debug'] = '1';
  wp4bd_debug_init();
  $level = wp4bd_debug_get_level();
  assert($level === 1, 'Debug parameter 1 should result in level 1');
  echo "    🔍 Debug param '1': Level $level\n";

  // Test debug level 4
  $_GET['wp4bd_debug'] = '4';
  wp4bd_debug_init();
  $level = wp4bd_debug_get_level();
  assert($level === 4, 'Debug parameter 4 should result in level 4');
  echo "    🔍 Debug param '4': Level $level\n";

  // Test invalid debug parameter
  $_GET['wp4bd_debug'] = 'invalid';
  wp4bd_debug_init();
  $level = wp4bd_debug_get_level();
  assert($level === 0, 'Invalid debug parameter should result in level 0');
  echo "    🔍 Invalid param: Level $level\n";

  echo "  ✅ Debug level detection working\n";
}

/**
 * Test debug infrastructure functions.
 */
function test_debug_infrastructure() {
  echo "  Testing debug infrastructure...\n";

  $_GET['wp4bd_debug'] = '2';
  wp4bd_debug_init();

  // Test stage timing
  wp4bd_debug_stage_start('Test Stage');
  wp4bd_debug_log('Test Stage', 'Test Key', 'Test Value');
  wp4bd_debug_stage_end('Test Stage');

  // Test debug render (should not be empty in debug mode)
  $debug_output = wp4bd_debug_render();
  assert(!empty($debug_output), 'Debug render should return content in debug mode');
  assert(strpos($debug_output, 'Test Stage') !== false, 'Debug output should contain our test stage');
  assert(strpos($debug_output, 'Test Value') !== false, 'Debug output should contain our test value');

  echo "  ✅ Debug infrastructure working\n";
  echo "    📊 Debug output length: " . strlen($debug_output) . " characters\n";
}

/**
 * Test production template structure.
 */
function test_production_template_structure() {
  echo "  Testing production template structure...\n";

  // Test that the template file exists
  $template_file = BACKDROP_ROOT . '/themes/wp/templates/page.tpl.php';
  assert(file_exists($template_file), 'Production template file should exist');

  // Read the template content
  $template_content = file_get_contents($template_file);
  assert(!empty($template_content), 'Template should have content');

  // Check that it contains key elements
  assert(strpos($template_content, '_wp_content_render_full_page()') !== false, 'Should call WordPress rendering function');
  assert(strpos($template_content, 'wp4bd_debug_get_level()') !== false, 'Should check debug level');
  assert(strpos($template_content, 'show_debug') !== false, 'Should have debug conditional logic');

  // Check for production vs debug content
  assert(strpos($template_content, 'WP4BD V2 Production Template') !== false, 'Should identify as production template');
  assert(strpos($template_content, 'WordPress theme rendered above') !== false, 'Should mention theme rendering');

  echo "  ✅ Production template structure correct\n";
  echo "    📄 Template file size: " . strlen($template_content) . " characters\n";
}

/**
 * Test template conditional logic.
 */
function test_template_conditional_logic() {
  echo "  Testing template conditional logic...\n";

  // Test production mode (no debug)
  unset($_GET['wp4bd_debug']);
  wp4bd_debug_init();
  $debug_level = wp4bd_debug_get_level();
  $show_debug = ($debug_level > 0);

  assert($show_debug === false, 'Should not show debug when no debug parameter');
  echo "    🚫 Production mode: Debug hidden (level: $debug_level)\n";

  // Test debug mode
  $_GET['wp4bd_debug'] = '3';
  wp4bd_debug_init();
  $debug_level = wp4bd_debug_get_level();
  $show_debug = ($debug_level > 0);

  assert($show_debug === true, 'Should show debug when debug parameter present');
  echo "    ✅ Debug mode: Debug shown (level: $debug_level)\n";

  echo "  ✅ Template conditional logic working\n";
}

/**
 * Test error handling in template.
 */
function test_template_error_handling() {
  echo "  Testing template error handling...\n";

  // The template should handle cases where functions might not be available
  // Test that wp4bd_debug_init() doesn't crash
  wp4bd_debug_init();
  assert(function_exists('wp4bd_debug_get_level'), 'Debug functions should be available');

  // Test that debug functions handle missing data gracefully
  $debug_output = wp4bd_debug_render();
  assert(is_string($debug_output), 'Debug render should return a string even with no data');

  echo "  ✅ Template error handling working\n";
}

// Run tests
try {
  test_debug_level_detection();
  test_debug_infrastructure();
  test_production_template_structure();
  test_template_conditional_logic();
  test_template_error_handling();
  echo "\n🎉 All V2-073 tests passed - Production template ready!\n";
} catch (Exception $e) {
  echo "❌ V2-073 Test failed: " . $e->getMessage() . "\n";
  exit(1);
} catch (Error $e) {
  echo "❌ V2-073 Fatal error: " . $e->getMessage() . "\n";
  exit(1);
}
