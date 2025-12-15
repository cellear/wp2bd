#!/usr/bin/env php
<?php
/**
 * Test script for WP4BD-V2-031: Initialize WordPress Globals.
 *
 * Run from command line:
 *   php TESTS/V2/031-init-globals.php
 *
 * Or from within ddev:
 *   ddev exec 'php /var/www/html/TESTS/V2/031-init-globals.php'
 */

// Setup BACKDROP_ROOT for both environments.
if (file_exists('/var/www/html/backdrop-1.30')) {
  define('BACKDROP_ROOT', '/var/www/html/backdrop-1.30');
} else {
  // We're in TESTS/V2/, so go up two levels to repo root, then into backdrop-1.30.
  define('BACKDROP_ROOT', dirname(dirname(__DIR__)) . '/backdrop-1.30');
}

// Load globals init function.
$init_file = BACKDROP_ROOT . '/modules/wp_content/includes/wp-globals-init.php';
if (!file_exists($init_file)) {
  echo "❌ Missing init file: $init_file\n";
  exit(1);
}
require_once $init_file;

echo "Testing WordPress Globals Initialization...\n";
echo str_repeat("=", 60) . "\n\n";

// Run initialization.
$summary = wp4bd_init_wordpress_globals();

// Helper to check existence.
$check = function ($name) {
  return isset($GLOBALS[$name]);
};

// 1) Query globals.
echo "1. Query Globals\n";
echo "   \$wp_query: " . ($check('wp_query') ? "✅ set" : "❌ missing") . "\n";
echo "   \$wp_the_query: " . ($check('wp_the_query') ? "✅ set" : "❌ missing") . "\n";
echo "   \$post: " . ($check('post') ? "✅ set" : "⚠️  null") . "\n";
echo "   \$posts: " . ($check('posts') ? "✅ set" : "⚠️  null") . "\n\n";

// 2) Rewrite and request.
echo "2. Rewrite & Request\n";
echo "   \$wp_rewrite: " . ($check('wp_rewrite') ? "✅ set" : "❌ missing") . "\n";
echo "   \$wp: " . ($check('wp') ? "✅ set" : "❌ missing") . "\n\n";

// 3) Post types and taxonomies.
echo "3. Post Types & Taxonomies\n";
echo "   \$wp_post_types: " . ($check('wp_post_types') ? "✅ set" : "❌ missing") . "\n";
echo "   \$wp_taxonomies: " . ($check('wp_taxonomies') ? "✅ set" : "❌ missing") . "\n\n";

// 4) Hooks.
echo "4. Hook System Globals\n";
echo "   \$wp_filter: " . ($check('wp_filter') ? "✅ set" : "❌ missing") . "\n";
echo "   \$wp_actions: " . ($check('wp_actions') ? "✅ set" : "❌ missing") . "\n";
echo "   \$wp_current_filter: " . ($check('wp_current_filter') ? "✅ set" : "❌ missing") . "\n\n";

// 5) Theme.
echo "5. Theme Global\n";
echo "   \$wp_theme: " . ($check('wp_theme') ? "✅ set" : "❌ missing") . "\n\n";

// 6) Environment.
echo "6. Environment Globals\n";
echo "   \$pagenow: " . ($check('pagenow') ? "✅ set ({$GLOBALS['pagenow']})" : "❌ missing") . "\n";
echo "   \$blog_id: " . ($check('blog_id') ? "✅ set ({$GLOBALS['blog_id']})" : "❌ missing") . "\n\n";

// Summary.
echo "Posts loaded: {$summary['posts_loaded']}\n";
echo "wp_query initialized: " . ($summary['wp_query_initialized'] ? "✅" : "❌") . "\n";
echo "wp_the_query initialized: " . ($summary['wp_the_query_initialized'] ? "✅" : "❌") . "\n";
echo "rewrite initialized: " . ($summary['rewrite_initialized'] ? "✅" : "❌") . "\n";
echo "wp initialized: " . ($summary['wp_initialized'] ? "✅" : "❌") . "\n";
echo "post types initialized: " . ($summary['post_types_initialized'] ? "✅" : "⚠️  skipped") . "\n";
echo "taxonomies initialized: " . ($summary['taxonomies_initialized'] ? "✅" : "⚠️  skipped") . "\n";
echo "hook globals initialized: " . ($summary['hook_globals_initialized'] ? "✅" : "❌") . "\n";
echo "pagenow: {$summary['pagenow']}\n\n";

echo str_repeat("=", 60) . "\n";
echo "🎉 WP4BD-V2-031: Globals initialized (test complete)\n";
echo str_repeat("=", 60) . "\n";

exit(0);

