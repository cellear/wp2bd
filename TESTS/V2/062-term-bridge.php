#!/usr/bin/env php
<?php
/**
 * Test script for WP4BD-V2-062: WordPress Term/Taxonomy Bridge
 *
 * Run from command line:
 *   php TESTS/V2/062-term-bridge.php
 *
 * Or from within ddev:
 *   ddev exec 'php /var/www/html/TESTS/V2/062-term-bridge.php'
 */

// Setup BACKDROP_ROOT for both environments
if (file_exists('/var/www/html/backdrop-1.30')) {
  define('BACKDROP_ROOT', '/var/www/html/backdrop-1.30');
} else {
  define('BACKDROP_ROOT', dirname(dirname(__DIR__)) . '/backdrop-1.30');
}

echo "Testing WordPress Term/Taxonomy Bridge...\n";
echo str_repeat("=", 60) . "\n\n";

// Test 1: Load bridge file
echo "1. Loading wp-term-bridge.php...\n";
$bridge_file = BACKDROP_ROOT . '/modules/wp_content/includes/wp-term-bridge.php';
if (file_exists($bridge_file)) {
  require_once $bridge_file;
  echo "   ✅ Loaded: wp-term-bridge.php\n\n";
} else {
  echo "   ❌ MISSING: $bridge_file\n";
  exit(1);
}

// Test 2: Load WordPress WP_Term class
echo "2. Loading WordPress WP_Term class...\n";
$wp_term_file = BACKDROP_ROOT . '/themes/wp/wpbrain/wp-includes/class-wp-term.php';
if (file_exists($wp_term_file)) {
  require_once $wp_term_file;
  echo "   ✅ Loaded: WordPress WP_Term class\n\n";
} else {
  echo "   ❌ MISSING: $wp_term_file\n";
  exit(1);
}

// Test 3: Mock Backdrop functions
echo "3. Setting up test environment...\n";
if (!function_exists('backdrop_strtolower')) {
  function backdrop_strtolower($str) {
    return strtolower($str);
  }
}
echo "   ✅ Test environment ready\n\n";

// Test 4: Verify bridge functions exist
echo "4. Verifying bridge functions exist...\n";
$required_functions = array(
  'wp4bd_term_to_wp_term',
  'wp4bd_vocabulary_to_taxonomy',
  'wp4bd_sanitize_term_slug',
  'wp4bd_terms_to_wp_terms',
  'wp4bd_field_to_taxonomy',
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

// Test 5: Create mock Backdrop taxonomy term
echo "5. Creating mock Backdrop taxonomy term...\n";
$mock_term = (object) array(
  'tid' => 42,
  'name' => 'Web Development',
  'description' => 'Articles about web development and programming',
  'vocabulary' => 'tags',
  'parent' => array(0), // No parent
  'weight' => 0,
);
echo "   ✅ Mock term created (tid: {$mock_term->tid})\n\n";

// Test 6: Convert term to WP_Term
echo "6. Converting Backdrop term to WordPress WP_Term...\n";
$wp_term = wp4bd_term_to_wp_term($mock_term);
if ($wp_term instanceof WP_Term) {
  echo "   ✅ Conversion successful - returned WP_Term object\n\n";
} else {
  echo "   ❌ Conversion failed - did not return WP_Term object\n";
  var_dump($wp_term);
  exit(1);
}

// Test 7: Verify WP_Term properties
echo "7. Verifying WP_Term properties...\n";
$properties_correct = true;

// term_id mapping
if ($wp_term->term_id === 42) {
  echo "   ✅ Term ID correctly mapped (42)\n";
} else {
  echo "   ❌ Term ID incorrect: {$wp_term->term_id}\n";
  $properties_correct = false;
}

// Name mapping
if ($wp_term->name === 'Web Development') {
  echo "   ✅ Name correctly mapped\n";
} else {
  echo "   ❌ Name incorrect: {$wp_term->name}\n";
  $properties_correct = false;
}

// Slug generation
if ($wp_term->slug === 'web-development') {
  echo "   ✅ Slug correctly generated (web-development)\n";
} else {
  echo "   ❌ Slug incorrect: {$wp_term->slug}\n";
  $properties_correct = false;
}

// Description mapping
if (strpos($wp_term->description, 'web development') !== false) {
  echo "   ✅ Description correctly mapped\n";
} else {
  echo "   ❌ Description incorrect or missing\n";
  $properties_correct = false;
}

// Taxonomy mapping (tags → post_tag)
if ($wp_term->taxonomy === 'post_tag') {
  echo "   ✅ Taxonomy correctly mapped (tags → post_tag)\n";
} else {
  echo "   ❌ Taxonomy incorrect: {$wp_term->taxonomy}\n";
  $properties_correct = false;
}

// Parent mapping
if ($wp_term->parent === 0) {
  echo "   ✅ Parent correctly mapped (0 = no parent)\n";
} else {
  echo "   ❌ Parent incorrect: {$wp_term->parent}\n";
  $properties_correct = false;
}

if (!$properties_correct) {
  exit(1);
}
echo "\n";

// Test 8: Test vocabulary-to-taxonomy mappings
echo "8. Testing vocabulary-to-taxonomy mappings...\n";
$vocabulary_mappings = array(
  'tags' => 'post_tag',
  'categories' => 'category',
  'category' => 'category',
  'custom_vocab' => 'custom_vocab', // Should pass through
);
$mappings_correct = true;
foreach ($vocabulary_mappings as $input => $expected) {
  $result = wp4bd_vocabulary_to_taxonomy($input);
  if ($result === $expected) {
    echo "   ✅ '$input' → '$result'\n";
  } else {
    echo "   ❌ '$input' → '$result' (expected '$expected')\n";
    $mappings_correct = false;
  }
}
if (!$mappings_correct) {
  exit(1);
}
echo "\n";

// Test 9: Test slug sanitization
echo "9. Testing term slug sanitization...\n";
$test_cases = array(
  'Web Development' => 'web-development',
  'Special@#$%Characters' => 'specialcharacters',
  'Multiple   Spaces' => 'multiple-spaces',
);
$sanitization_correct = true;
foreach ($test_cases as $input => $expected) {
  $result = wp4bd_sanitize_term_slug($input);
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

// Test 10: Test hierarchical terms
echo "10. Testing hierarchical term conversion...\n";
$parent_term = (object) array(
  'tid' => 10,
  'name' => 'Parent Category',
  'vocabulary' => 'categories',
  'parent' => array(0),
);
$child_term = (object) array(
  'tid' => 20,
  'name' => 'Child Category',
  'vocabulary' => 'categories',
  'parent' => array(10), // Parent is tid 10
);
$wp_parent = wp4bd_term_to_wp_term($parent_term);
$wp_child = wp4bd_term_to_wp_term($child_term);

if ($wp_parent->parent === 0 && $wp_child->parent === 10) {
  echo "   ✅ Hierarchical relationships preserved\n";
  echo "      Parent (tid 10) has parent: {$wp_parent->parent}\n";
  echo "      Child (tid 20) has parent: {$wp_child->parent}\n";
} else {
  echo "   ❌ Hierarchical relationships not preserved\n";
  exit(1);
}
echo "\n";

// Test 11: Test field-to-taxonomy helper
echo "11. Testing field-to-taxonomy helper...\n";
$field_mappings = array(
  'field_tags' => 'post_tag',
  'field_categories' => 'category',
  'field_custom' => 'post_tag', // Default
);
$field_mappings_correct = true;
foreach ($field_mappings as $input => $expected) {
  $result = wp4bd_field_to_taxonomy($input);
  if ($result === $expected) {
    echo "   ✅ '$input' → '$result'\n";
  } else {
    echo "   ❌ '$input' → '$result' (expected '$expected')\n";
    $field_mappings_correct = false;
  }
}
if (!$field_mappings_correct) {
  exit(1);
}
echo "\n";

// Test 12: Test NULL handling
echo "12. Testing NULL/invalid input handling...\n";
$null_result = wp4bd_term_to_wp_term(NULL);
if ($null_result === NULL) {
  echo "   ✅ NULL input handled correctly\n";
} else {
  echo "   ❌ NULL input not handled correctly\n";
  exit(1);
}

$invalid_result = wp4bd_term_to_wp_term((object) array());
if ($invalid_result === NULL) {
  echo "   ✅ Invalid term handled correctly\n";
} else {
  echo "   ❌ Invalid term not handled correctly\n";
  exit(1);
}
echo "\n";

echo str_repeat("=", 60) . "\n";
echo "✅ ALL TESTS PASSED - Term Bridge Working!\n";
echo str_repeat("=", 60) . "\n";
exit(0);
