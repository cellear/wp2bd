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

// Load WordPress bootstrap and term bridge
require_once BACKDROP_ROOT . '/modules/wp_content/includes/wp-bootstrap.php';
require_once BACKDROP_ROOT . '/modules/wp_content/includes/wp-term-bridge.php';

/**
 * Test basic term conversion.
 */
function test_basic_term_conversion() {
  echo "  Testing basic term conversion...\n";

  // Create a mock Backdrop term
  $mock_term = (object) array(
    'tid' => 42,
    'name' => 'Technology',
    'machine_name' => 'technology',
    'vocabulary_machine_name' => 'categories',
    'description' => array(
      'und' => array(
        array('value' => 'Articles about technology')
      )
    ),
    'weight' => 5,
    'parent' => 0,
    'count' => 15,
  );

  $wp_term = wp4bd_backdrop_term_to_wp_term($mock_term);

  assert(is_object($wp_term), 'Result should be an object');
  assert($wp_term->term_id === 42, 'Term ID should match tid');
  assert($wp_term->name === 'Technology', 'Name should match');
  assert($wp_term->slug === 'technology', 'Slug should match machine_name');
  assert($wp_term->taxonomy === 'category', 'Taxonomy should be mapped from categories');
  assert($wp_term->description === 'Articles about technology', 'Description should be extracted');
  assert($wp_term->term_order === 5, 'Term order should match weight');
  assert($wp_term->count === 15, 'Count should match');

  echo "  ✅ Basic term properties verified\n";
}

/**
 * Test term with custom vocabulary.
 */
function test_custom_vocabulary_term() {
  echo "  Testing custom vocabulary term...\n";

  $mock_term = (object) array(
    'tid' => 100,
    'name' => 'Breaking News',
    'machine_name' => 'breaking-news',
    'vocabulary_machine_name' => 'news_types',
  );

  $wp_term = wp4bd_backdrop_term_to_wp_term($mock_term);

  assert($wp_term->taxonomy === 'backdrop_news_types', 'Custom vocabulary should be prefixed');

  echo "  ✅ Custom vocabulary mapping verified\n";
}

/**
 * Test term without vocabulary info.
 */
function test_term_without_vocabulary() {
  echo "  Testing term without vocabulary...\n";

  $mock_term = (object) array(
    'tid' => 200,
    'name' => 'General Topic',
  );

  $wp_term = wp4bd_backdrop_term_to_wp_term($mock_term);

  assert($wp_term->taxonomy === 'category', 'Default taxonomy should be category');

  echo "  ✅ Default taxonomy handling verified\n";
}

/**
 * Test invalid term handling.
 */
function test_invalid_term() {
  echo "  Testing invalid term handling...\n";

  $invalid_term = "not an object";
  $result = wp4bd_backdrop_term_to_wp_term($invalid_term);
  assert($result === null, 'Invalid term should return null');

  $empty_object = (object) array();
  $result = wp4bd_backdrop_term_to_wp_term($empty_object);
  assert($result === null, 'Term without tid should return null');

  echo "  ✅ Invalid term handling verified\n";
}

/**
 * Test batch term conversion.
 */
function test_batch_term_conversion() {
  echo "  Testing batch term conversion...\n";

  $terms = array(
    (object) array('tid' => 1, 'name' => 'Term 1', 'vocabulary_machine_name' => 'tags'),
    (object) array('tid' => 2, 'name' => 'Term 2', 'vocabulary_machine_name' => 'categories'),
    (object) array('tid' => 3, 'name' => 'Term 3'),
  );

  $wp_terms = wp4bd_backdrop_terms_to_wp_terms($terms);

  assert(is_array($wp_terms), 'Result should be an array');
  assert(count($wp_terms) === 3, 'Should have 3 terms');

  foreach ($wp_terms as $i => $wp_term) {
    assert(is_object($wp_term), "Term $i should be an object");
    assert($wp_term->term_id === ($i + 1), "Term $i ID should match");
  }

  echo "  ✅ Batch term conversion verified\n";
}

/**
 * Test vocabulary to taxonomy conversion.
 */
function test_vocabulary_to_taxonomy() {
  echo "  Testing vocabulary to taxonomy conversion...\n";

  $mock_vocab = (object) array(
    'machine_name' => 'article_types',
    'name' => 'Article Types',
    'description' => 'Different types of articles',
    'hierarchy' => 1,
  );

  $wp_taxonomy = wp4bd_backdrop_vocabulary_to_wp_taxonomy($mock_vocab);

  assert(is_object($wp_taxonomy), 'Result should be an object');
  assert($wp_taxonomy->name === 'backdrop_article_types', 'Name should be prefixed');
  assert($wp_taxonomy->label === 'Article Types', 'Label should match name');
  assert($wp_taxonomy->hierarchical === true, 'Should be hierarchical');
  assert($wp_taxonomy->public === true, 'Should be public');

  echo "  ✅ Vocabulary to taxonomy conversion verified\n";
}

/**
 * Test slug sanitization.
 */
function test_slug_sanitization() {
  echo "  Testing slug sanitization...\n";

  // Test various inputs
  assert(_wp4bd_sanitize_term_slug('Hello World') === 'hello-world', 'Spaces should become hyphens');
  assert(_wp4bd_sanitize_term_slug('Tech & Innovation!') === 'tech-innovation', 'Special chars should be removed');
  assert(_wp4bd_sanitize_term_slug('  Leading/Trailing  ') === 'leading-trailing', 'Leading/trailing spaces and chars removed');

  echo "  ✅ Slug sanitization verified\n";
}

// Run tests
try {
  test_basic_term_conversion();
  test_custom_vocabulary_term();
  test_term_without_vocabulary();
  test_invalid_term();
  test_batch_term_conversion();
  test_vocabulary_to_taxonomy();
  test_slug_sanitization();
  echo "\n🎉 All V2-062 tests passed!\n";
} catch (Exception $e) {
  echo "❌ V2-062 Test failed: " . $e->getMessage() . "\n";
  exit(1);
} catch (Error $e) {
  echo "❌ V2-062 Fatal error: " . $e->getMessage() . "\n";
  exit(1);
}
