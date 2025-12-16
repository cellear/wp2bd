#!/usr/bin/env php
<?php
/**
 * Test script for WP4BD-V2-060: WordPress Post Object Bridge
 *
 * Run from command line:
 *   php TESTS/V2/060-post-bridge.php
 *
 * Or from within ddev:
 *   ddev exec 'php /var/www/html/TESTS/V2/060-post-bridge.php'
 */

// Setup BACKDROP_ROOT for both environments
if (file_exists('/var/www/html/backdrop-1.30')) {
  define('BACKDROP_ROOT', '/var/www/html/backdrop-1.30');
} else {
  define('BACKDROP_ROOT', dirname(dirname(__DIR__)) . '/backdrop-1.30');
}

echo "Testing WordPress Post Object Bridge...\n";
echo str_repeat("=", 60) . "\n\n";

// Test 1: Load bridge file
echo "1. Loading wp-post-bridge.php...\n";
$bridge_file = BACKDROP_ROOT . '/modules/wp_content/includes/wp-post-bridge.php';
if (file_exists($bridge_file)) {
  require_once $bridge_file;
  echo "   ✅ Loaded: wp-post-bridge.php\n\n";
} else {
  echo "   ❌ MISSING: $bridge_file\n";
  exit(1);
}

// Test 2: Load WordPress WP_Post class
echo "2. Loading WordPress WP_Post class...\n";
$wp_post_file = BACKDROP_ROOT . '/themes/wp/wpbrain/wp-includes/class-wp-post.php';
if (file_exists($wp_post_file)) {
  require_once $wp_post_file;
  echo "   ✅ Loaded: WordPress WP_Post class\n\n";
} else {
  echo "   ❌ MISSING: $wp_post_file\n";
  exit(1);
}

// Test 3: Mock Backdrop functions needed for bridge
echo "3. Setting up test environment...\n";
if (!defined('LANGUAGE_NONE')) {
  define('LANGUAGE_NONE', 'und');
}
if (!function_exists('backdrop_strtolower')) {
  function backdrop_strtolower($str) {
    return strtolower($str);
  }
}
echo "   ✅ Test environment ready\n\n";

// Test 4: Verify bridge functions exist
echo "4. Verifying bridge functions exist...\n";
$required_functions = array(
  'wp4bd_node_to_post',
  'wp4bd_sanitize_title',
  'wp4bd_nodes_to_posts',
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

// Test 5: Create mock Backdrop node
echo "5. Creating mock Backdrop node...\n";
$mock_node = (object) array(
  'nid' => 123,
  'uid' => 1,
  'title' => 'Test Article Title',
  'type' => 'article',
  'status' => 1,
  'created' => time() - 3600,
  'changed' => time(),
  'body' => array(
    LANGUAGE_NONE => array(
      0 => array(
        'value' => '<p>This is the test article body content.</p>',
        'summary' => 'This is a test summary.',
      ),
    ),
  ),
);
echo "   ✅ Mock node created (nid: {$mock_node->nid})\n\n";

// Test 6: Convert node to WP_Post
echo "6. Converting Backdrop node to WordPress WP_Post...\n";
$wp_post = wp4bd_node_to_post($mock_node);
if ($wp_post instanceof WP_Post) {
  echo "   ✅ Conversion successful - returned WP_Post object\n\n";
} else {
  echo "   ❌ Conversion failed - did not return WP_Post object\n";
  var_dump($wp_post);
  exit(1);
}

// Test 7: Verify all WP_Post properties are set
echo "7. Verifying WP_Post properties...\n";
$properties_correct = true;

// ID mapping
if ($wp_post->ID === 123) {
  echo "   ✅ ID correctly mapped (123)\n";
} else {
  echo "   ❌ ID incorrect: {$wp_post->ID}\n";
  $properties_correct = false;
}

// Author mapping
if ($wp_post->post_author === 1) {
  echo "   ✅ Author correctly mapped (1)\n";
} else {
  echo "   ❌ Author incorrect: {$wp_post->post_author}\n";
  $properties_correct = false;
}

// Title mapping
if ($wp_post->post_title === 'Test Article Title') {
  echo "   ✅ Title correctly mapped\n";
} else {
  echo "   ❌ Title incorrect: {$wp_post->post_title}\n";
  $properties_correct = false;
}

// Content mapping
if (strpos($wp_post->post_content, 'test article body content') !== false) {
  echo "   ✅ Content correctly mapped\n";
} else {
  echo "   ❌ Content incorrect or missing\n";
  $properties_correct = false;
}

// Excerpt mapping
if (strpos($wp_post->post_excerpt, 'test summary') !== false) {
  echo "   ✅ Excerpt correctly mapped\n";
} else {
  echo "   ❌ Excerpt incorrect or missing\n";
  $properties_correct = false;
}

// Status mapping
if ($wp_post->post_status === 'publish') {
  echo "   ✅ Status correctly mapped (publish)\n";
} else {
  echo "   ❌ Status incorrect: {$wp_post->post_status}\n";
  $properties_correct = false;
}

// Type mapping
if ($wp_post->post_type === 'article') {
  echo "   ✅ Type correctly mapped (article)\n";
} else {
  echo "   ❌ Type incorrect: {$wp_post->post_type}\n";
  $properties_correct = false;
}

if (!$properties_correct) {
  exit(1);
}
echo "\n";

// Test 8: Test sanitize_title helper
echo "8. Testing title sanitization...\n";
$test_cases = array(
  'Hello World' => 'hello-world',
  'Test With  Spaces' => 'test-with-spaces',
  'Special@#$%Characters' => 'specialcharacters',
);
$sanitization_correct = true;
foreach ($test_cases as $input => $expected) {
  $result = wp4bd_sanitize_title($input);
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

// Test 9: Test batch conversion
echo "9. Testing batch node conversion...\n";
$nodes = array($mock_node);
$posts = wp4bd_nodes_to_posts($nodes);
if (is_array($posts) && count($posts) === 1) {
  echo "   ✅ Batch conversion successful (1 node → 1 post)\n";
} else {
  echo "   ❌ Batch conversion failed\n";
  exit(1);
}
echo "\n";

// Test 10: Test NULL handling
echo "10. Testing NULL/invalid input handling...\n";
$null_result = wp4bd_node_to_post(NULL);
if ($null_result === NULL) {
  echo "   ✅ NULL input handled correctly\n";
} else {
  echo "   ❌ NULL input not handled correctly\n";
  exit(1);
}

$invalid_result = wp4bd_node_to_post((object) array());
if ($invalid_result === NULL) {
  echo "   ✅ Invalid node handled correctly\n";
} else {
  echo "   ❌ Invalid node not handled correctly\n";
  exit(1);
}
echo "\n";

echo str_repeat("=", 60) . "\n";
echo "✅ ALL TESTS PASSED - Post Bridge Working!\n";
echo str_repeat("=", 60) . "\n";
exit(0);
