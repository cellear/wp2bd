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

// Load WordPress bootstrap and post bridge
require_once BACKDROP_ROOT . '/modules/wp_content/includes/wp-bootstrap.php';
require_once BACKDROP_ROOT . '/modules/wp_content/includes/wp-post-bridge.php';

/**
 * Test that wp4bd_node_to_wp_post converts Backdrop nodes to WP_Post objects.
 */
function test_node_to_wp_post_conversion() {
  echo "  Testing node to WP_Post conversion...\n";

  // Create a mock Backdrop node
  $mock_node = (object) array(
    'nid' => 123,
    'type' => 'article',
    'title' => 'Test Article',
    'uid' => 1,
    'status' => 1,
    'created' => strtotime('2024-01-15 10:00:00'),
    'changed' => strtotime('2024-01-15 11:00:00'),
    'body' => array(
      'und' => array(
        array(
          'value' => 'This is the article content.',
          'summary' => 'This is the excerpt.',
        )
      )
    ),
    'comment' => 2, // Open comments
    'comment_count' => 5,
  );

  $wp_post = wp4bd_node_to_wp_post($mock_node);

  assert($wp_post instanceof WP_Post, 'Result should be a WP_Post instance');
  assert($wp_post->ID === 123, 'ID should match node nid');
  assert($wp_post->post_title === 'Test Article', 'Title should match node title');
  assert($wp_post->post_type === 'article', 'Post type should match node type');
  assert($wp_post->post_author === '1', 'Author should match node uid');
  assert($wp_post->post_status === 'publish', 'Status should be publish for status=1');
  assert($wp_post->comment_status === 'open', 'Comment status should be open for comment=2');
  assert($wp_post->comment_count === '5', 'Comment count should match');
  assert($wp_post->post_content === 'This is the article content.', 'Content should be extracted');
  assert($wp_post->post_excerpt === 'This is the excerpt.', 'Excerpt should be extracted');

  // Check dates
  assert($wp_post->post_date === '2024-01-15 10:00:00', 'Post date should be formatted correctly');
  assert($wp_post->post_modified === '2024-01-15 11:00:00', 'Modified date should be formatted correctly');

  echo "  ✅ Basic conversion properties verified\n";
}

/**
 * Test conversion of node without body content.
 */
function test_node_without_body() {
  echo "  Testing node without body content...\n";

  $mock_node = (object) array(
    'nid' => 456,
    'type' => 'page',
    'title' => 'Simple Page',
    'uid' => 2,
    'status' => 1,
    'created' => time(),
  );

  $wp_post = wp4bd_node_to_wp_post($mock_node);

  assert($wp_post instanceof WP_Post, 'Result should be a WP_Post instance');
  assert($wp_post->ID === 456, 'ID should match');
  assert($wp_post->post_title === 'Simple Page', 'Title should match');
  assert($wp_post->post_content === '', 'Content should be empty');
  assert($wp_post->post_excerpt === '', 'Excerpt should be empty');

  echo "  ✅ Empty content handling verified\n";
}

/**
 * Test conversion of invalid node.
 */
function test_invalid_node() {
  echo "  Testing invalid node handling...\n";

  $invalid_node = "not an object";
  $result = wp4bd_node_to_wp_post($invalid_node);
  assert($result === null, 'Invalid node should return null');

  $empty_object = (object) array();
  $result = wp4bd_node_to_wp_post($empty_object);
  assert($result === null, 'Node without nid should return null');

  echo "  ✅ Invalid node handling verified\n";
}

/**
 * Test batch conversion of multiple nodes.
 */
function test_batch_conversion() {
  echo "  Testing batch conversion...\n";

  $nodes = array(
    (object) array('nid' => 1, 'title' => 'Post 1', 'type' => 'post'),
    (object) array('nid' => 2, 'title' => 'Post 2', 'type' => 'post'),
    (object) array('nid' => 3, 'title' => 'Post 3', 'type' => 'post'),
  );

  $wp_posts = wp4bd_nodes_to_wp_posts($nodes);

  assert(is_array($wp_posts), 'Result should be an array');
  assert(count($wp_posts) === 3, 'Should have 3 posts');

  foreach ($wp_posts as $i => $wp_post) {
    assert($wp_post instanceof WP_Post, "Post $i should be WP_Post instance");
    assert($wp_post->ID === ($i + 1), "Post $i ID should match");
  }

  echo "  ✅ Batch conversion verified\n";
}

/**
 * Test GUID generation.
 */
function test_guid_generation() {
  echo "  Testing GUID generation...\n";

  $mock_node = (object) array(
    'nid' => 789,
    'title' => 'GUID Test',
  );

  $wp_post = wp4bd_node_to_wp_post($mock_node);

  // GUID should contain node path
  assert(strpos($wp_post->guid, 'node/789') !== false, 'GUID should contain node path');

  echo "  ✅ GUID generation verified\n";
}

// Run tests
try {
  test_node_to_wp_post_conversion();
  test_node_without_body();
  test_invalid_node();
  test_batch_conversion();
  test_guid_generation();
  echo "\n🎉 All V2-060 tests passed!\n";
} catch (Exception $e) {
  echo "❌ V2-060 Test failed: " . $e->getMessage() . "\n";
  exit(1);
} catch (Error $e) {
  echo "❌ V2-060 Fatal error: " . $e->getMessage() . "\n";
  exit(1);
}
