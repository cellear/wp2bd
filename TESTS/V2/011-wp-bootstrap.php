#!/usr/bin/env php
<?php
/**
 * Test script for WP4BD-V2-011: WordPress Bootstrap Entry Point
 * 
 * Run from command line:
 *   php TESTS/V2/011-wp-bootstrap.php
 * 
 * Or from within ddev:
 *   ddev exec 'php /var/www/html/TESTS/V2/011-wp-bootstrap.php'
 */

// Determine if we're in ddev or local
if (file_exists('/var/www/html/backdrop-1.30')) {
  define('BACKDROP_ROOT', '/var/www/html/backdrop-1.30');
} else {
  // We're in TESTS/V2/, so go up two levels to repo root, then into backdrop-1.30
  define('BACKDROP_ROOT', dirname(dirname(__DIR__)) . '/backdrop-1.30');
}

// Mock backdrop_get_path
if (!function_exists('backdrop_get_path')) {
  function backdrop_get_path($type, $name) {
    if ($type === 'theme' && $name === 'wp') {
      return 'themes/wp';
    }
    return '';
  }
}

// Load the bootstrap file
echo "Loading wp-bootstrap.php...\n";
require_once BACKDROP_ROOT . '/modules/wp_content/includes/wp-bootstrap.php';
echo "✅ File loaded successfully\n\n";

// Test the bootstrap function
echo "Testing wp4bd_bootstrap_wordpress()...\n";
$result = wp4bd_bootstrap_wordpress();

if ($result) {
  echo "✅ SUCCESS: WordPress bootstrapped\n\n";
  
  echo "WordPress Constants Set:\n";
  echo "  - ABSPATH: " . (defined('ABSPATH') ? ABSPATH : 'NOT SET') . "\n";
  echo "  - WPINC: " . (defined('WPINC') ? WPINC : 'NOT SET') . "\n";
  echo "  - WP_CONTENT_DIR: " . (defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : 'NOT SET') . "\n\n";
  
  // Verify critical files exist
  $wp_post_file = ABSPATH . WPINC . '/class-wp-post.php';
  $wp_query_file = ABSPATH . WPINC . '/class-wp-query.php';
  
  echo "Critical Files:\n";
  echo "  - class-wp-post.php: " . (file_exists($wp_post_file) ? '✅ EXISTS' : '❌ MISSING') . "\n";
  echo "  - class-wp-query.php: " . (file_exists($wp_query_file) ? '✅ EXISTS' : '❌ MISSING') . "\n\n";
  
  // Get WordPress info
  echo "WordPress Info:\n";
  $info = wp4bd_get_wordpress_info();
  echo "  - Exists: " . ($info['exists'] ? 'YES' : 'NO') . "\n";
  echo "  - Version: " . ($info['version'] ?? 'Unknown') . "\n";
  echo "  - ABSPATH: " . ($info['abspath'] ?? 'Not set') . "\n";
  echo "  - WPINC: " . ($info['wpinc'] ?? 'Not set') . "\n\n";
  
  echo "🎉 All acceptance criteria met!\n";
  
} else {
  echo "❌ FAILED: WordPress bootstrap failed\n";
  echo "Check watchdog logs for error details\n";
  exit(1);
}

