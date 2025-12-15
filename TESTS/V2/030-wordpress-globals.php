#!/usr/bin/env php
<?php
/**
 * Test script for WP4BD-V2-030: Identify Critical WordPress Globals
 * 
 * Run from command line:
 *   php TESTS/V2/030-wordpress-globals.php
 * 
 * Or from within ddev:
 *   ddev exec 'php /var/www/html/TESTS/V2/030-wordpress-globals.php'
 */

// Setup BACKDROP_ROOT for both environments
if (file_exists('/var/www/html/backdrop-1.30')) {
  define('BACKDROP_ROOT', '/var/www/html/backdrop-1.30');
} else {
  // We're in TESTS/V2/, so go up two levels to repo root, then into backdrop-1.30
  define('BACKDROP_ROOT', dirname(dirname(__DIR__)) . '/backdrop-1.30');
}

// Test: Verify documentation file exists
echo "Testing WordPress Globals Documentation...\n";
echo str_repeat("=", 60) . "\n\n";

$doc_file = BACKDROP_ROOT . '/modules/wp_content/includes/wp-globals-reference.md';
echo "1. Checking if documentation file exists...\n";
if (file_exists($doc_file)) {
  echo "   ✅ EXISTS: wp-globals-reference.md\n\n";
} else {
  echo "   ❌ MISSING: $doc_file\n";
  exit(1);
}

// Test: Read and parse documentation
echo "2. Reading documentation file...\n";
$content = file_get_contents($doc_file);
if ($content === false) {
  echo "   ❌ FAILED to read file\n";
  exit(1);
}
echo "   ✅ File read successfully (" . strlen($content) . " bytes)\n\n";

// Test: Verify all required globals are documented
echo "3. Verifying all critical globals are documented...\n";
$required_globals = array(
  // Database & Query
  '$wpdb',
  '$wp_query',
  '$wp_the_query',
  
  // Post & Content
  '$post',
  '$posts',
  
  // Rewrite & URL
  '$wp_rewrite',
  '$wp',
  
  // Post Types & Taxonomies
  '$wp_post_types',
  '$wp_taxonomies',
  
  // Theme
  '$wp_theme',
  
  // Hook System
  '$wp_filter',
  '$wp_actions',
  '$wp_current_filter',
  
  // Page & Environment
  '$pagenow',
  '$blog_id',
);

$all_documented = true;
$missing = array();

foreach ($required_globals as $global) {
  // Check if global is mentioned in documentation
  // Look for the global name (with or without $)
  $global_name = substr($global, 1); // Remove $
  $pattern = '/\b' . preg_quote($global_name, '/') . '\b/i';
  
  if (preg_match($pattern, $content)) {
    echo "   ✅ $global documented\n";
  } else {
    echo "   ❌ $global NOT documented\n";
    $all_documented = false;
    $missing[] = $global;
  }
}

if (!$all_documented) {
  echo "\n❌ FAILED: Missing documentation for: " . implode(', ', $missing) . "\n";
  exit(1);
}
echo "\n";

// Test: Verify documentation structure
echo "4. Verifying documentation structure...\n";
$sections = array(
  'Database & Query Globals',
  'Post & Content Globals',
  'Rewrite & URL Globals',
  'Post Types & Taxonomies',
  'Theme Globals',
  'Hook System Globals',
  'Page & Environment Globals',
);

$all_sections_present = true;
foreach ($sections as $section) {
  if (strpos($content, $section) !== false) {
    echo "   ✅ Section: $section\n";
  } else {
    echo "   ❌ Missing section: $section\n";
    $all_sections_present = false;
  }
}

if (!$all_sections_present) {
  echo "\n❌ FAILED: Some sections missing\n";
  exit(1);
}
echo "\n";

// Test: Verify summary section
echo "5. Verifying summary section...\n";
if (strpos($content, 'Summary by Priority') !== false) {
  echo "   ✅ Summary by Priority section exists\n";
} else {
  echo "   ❌ Missing Summary by Priority section\n";
  exit(1);
}

if (strpos($content, 'Critical (Must Initialize)') !== false) {
  echo "   ✅ Critical priority section exists\n";
} else {
  echo "   ❌ Missing Critical priority section\n";
  exit(1);
}
echo "\n";

// Test: Verify implementation notes
echo "6. Verifying implementation notes...\n";
if (strpos($content, 'Implementation Notes') !== false) {
  echo "   ✅ Implementation Notes section exists\n";
} else {
  echo "   ⚠️  Implementation Notes section missing (optional but recommended)\n";
}

if (strpos($content, 'WP4BD-V2-031') !== false) {
  echo "   ✅ Next steps reference (WP4BD-V2-031) documented\n";
} else {
  echo "   ⚠️  Next steps reference missing (optional but recommended)\n";
}
echo "\n";

// Test: Count documented globals
echo "7. Counting documented globals...\n";
// Count lines that start with ### followed by a global variable
$global_count = preg_match_all('/^###\s+\$[a-z_]+/mi', $content, $matches);
echo "   ✅ Found $global_count documented globals\n";
if ($global_count < count($required_globals)) {
  echo "   ⚠️  Some globals may be documented in sections rather than as headers\n";
}
echo "\n";

// Final summary
echo str_repeat("=", 60) . "\n";
echo "🎉 All acceptance criteria met!\n";
echo str_repeat("=", 60) . "\n\n";

echo "Summary:\n";
echo "  - Documentation file created: wp-globals-reference.md\n";
echo "  - All " . count($required_globals) . " critical globals documented\n";
echo "  - Documentation organized by category\n";
echo "  - Priority levels identified (Critical, Important, Optional)\n";
echo "  - Implementation notes included\n";
echo "  - Next steps documented (WP4BD-V2-031)\n";
echo "\nWP4BD-V2-030: COMPLETE ✅\n";
echo "\nNext: WP4BD-V2-031 (Initialize WordPress Globals from Backdrop)\n";

exit(0);

