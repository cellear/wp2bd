<?php
/**
 * Test Epic 6: V2-050 Integration Point in Module
 *
 * Tests that wp_content module loads WordPress core after Backdrop bootstrap FULL phase.
 *
 * @package WP4BD
 * @subpackage Tests
 */

require_once __DIR__ . '/../bootstrap.php';

/**
 * Test that WordPress core loads after Backdrop bootstrap FULL phase.
 */
function test_wp_content_bootstrap_full_integration() {
  // Simulate Backdrop bootstrap FULL phase
  $GLOBALS['theme_key'] = 'wp';
  $_GET['q'] = 'node'; // Non-admin path

  // Call the bootstrap full hook
  wp_content_bootstrap_full();

  // Check that WordPress constants are defined
  assert(defined('ABSPATH'), 'ABSPATH should be defined after bootstrap');
  assert(defined('WPINC'), 'WPINC should be defined after bootstrap');
  assert(defined('WP_CONTENT_DIR'), 'WP_CONTENT_DIR should be defined after bootstrap');

  // Check that WordPress core files are loaded
  assert(class_exists('WP_Post'), 'WP_Post class should be available');
  assert(class_exists('WP_Query'), 'WP_Query class should be available');

  // Check that WordPress globals are initialized
  global $wp_query, $wp_filter, $wp_actions;
  assert(isset($wp_filter), '$wp_filter should be initialized');
  assert(isset($wp_actions), '$wp_actions should be initialized');

  echo "✅ V2-050: Integration Point in Module - PASSED\n";
}

/**
 * Test that WordPress only loads when wp theme is active.
 */
function test_wp_content_only_loads_for_wp_theme() {
  // Test with non-wp theme
  $original_theme = $GLOBALS['theme_key'];
  $GLOBALS['theme_key'] = 'bartik';

  wp_content_bootstrap_full();

  // WordPress should not be loaded
  assert(!defined('ABSPATH'), 'WordPress should not load for non-wp themes');

  // Restore theme
  $GLOBALS['theme_key'] = $original_theme;

  echo "✅ V2-050: Theme-specific loading - PASSED\n";
}

/**
 * Test that WordPress doesn't load on admin pages.
 */
function test_wp_content_skips_admin_pages() {
  $original_theme = $GLOBALS['theme_key'];
  $original_path = $_GET['q'];

  $GLOBALS['theme_key'] = 'wp';
  $_GET['q'] = 'admin/config'; // Admin path

  wp_content_bootstrap_full();

  // WordPress should not be loaded on admin pages
  assert(!defined('ABSPATH'), 'WordPress should not load on admin pages');

  // Restore
  $GLOBALS['theme_key'] = $original_theme;
  $_GET['q'] = $original_path;

  echo "✅ V2-050: Admin page skip - PASSED\n";
}

// Run tests
try {
  test_wp_content_bootstrap_full_integration();
  test_wp_content_only_loads_for_wp_theme();
  test_wp_content_skips_admin_pages();
  echo "\n🎉 All V2-050 tests passed!\n";
} catch (Exception $e) {
  echo "❌ V2-050 Test failed: " . $e->getMessage() . "\n";
  exit(1);
}
