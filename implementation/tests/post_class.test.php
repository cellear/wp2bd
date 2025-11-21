<?php
/**
 * Unit Tests for post_class() Functions
 *
 * Tests for post_class() and get_post_class() functions.
 * Verifies WordPress compatibility and Backdrop integration.
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include the functions to test
require_once dirname(__DIR__) . '/functions/content-display.php';

/**
 * Test class for post_class functions
 */
class PostClassTest {

  /**
   * Set up before each test
   */
  public function setUp() {
    // Reset global post
    global $wp_post;
    $wp_post = null;
  }

  /**
   * Test 1: get_post_class() returns basic post classes
   */
  public function test_get_post_class_basic() {
    global $wp_post;

    // Create WordPress-style post object
    $wp_post = (object) array(
      'ID' => 123,
      'post_type' => 'post',
      'post_status' => 'publish',
      'post_title' => 'Test Post',
      'post_content' => 'This is test content.',
    );

    $classes = get_post_class();

    // Check required classes
    assert(in_array('post-123', $classes), 'Should include post-{ID} class');
    assert(in_array('post', $classes), 'Should include post type class');
    assert(in_array('type-post', $classes), 'Should include type-{post_type} class');
    assert(in_array('status-publish', $classes), 'Should include status-{status} class');
    assert(in_array('format-standard', $classes), 'Should include format-standard class');
    assert(in_array('hentry', $classes), 'Should include hentry class');

    echo "✓ Test 1 passed: get_post_class() returns basic post classes\n";
  }

  /**
   * Test 2: get_post_class() with custom classes (string)
   */
  public function test_get_post_class_custom_classes_string() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 100,
      'post_type' => 'post',
      'post_status' => 'publish',
    );

    $classes = get_post_class('custom-class another-class');

    assert(in_array('custom-class', $classes), 'Should include first custom class');
    assert(in_array('another-class', $classes), 'Should include second custom class');
    assert(in_array('post-100', $classes), 'Should still include standard classes');

    echo "✓ Test 2 passed: get_post_class() with custom classes (string)\n";
  }

  /**
   * Test 3: get_post_class() with custom classes (array)
   */
  public function test_get_post_class_custom_classes_array() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 200,
      'post_type' => 'page',
      'post_status' => 'publish',
    );

    $classes = get_post_class(array('custom-1', 'custom-2', 'custom-3'));

    assert(in_array('custom-1', $classes), 'Should include first custom class');
    assert(in_array('custom-2', $classes), 'Should include second custom class');
    assert(in_array('custom-3', $classes), 'Should include third custom class');
    assert(in_array('type-page', $classes), 'Should include type-page');

    echo "✓ Test 3 passed: get_post_class() with custom classes (array)\n";
  }

  /**
   * Test 4: get_post_class() with categories
   */
  public function test_get_post_class_with_categories() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 300,
      'post_type' => 'post',
      'post_status' => 'publish',
      'categories' => array(
        (object) array('term_id' => 1, 'slug' => 'news'),
        (object) array('term_id' => 2, 'slug' => 'technology'),
      ),
    );

    $classes = get_post_class();

    assert(in_array('category-news', $classes), 'Should include category-news class');
    assert(in_array('category-technology', $classes), 'Should include category-technology class');

    echo "✓ Test 4 passed: get_post_class() with categories\n";
  }

  /**
   * Test 5: get_post_class() with tags
   */
  public function test_get_post_class_with_tags() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 400,
      'post_type' => 'post',
      'post_status' => 'publish',
      'tags' => array(
        (object) array('term_id' => 10, 'slug' => 'php'),
        (object) array('term_id' => 11, 'slug' => 'wordpress'),
      ),
    );

    $classes = get_post_class();

    assert(in_array('tag-php', $classes), 'Should include tag-php class');
    assert(in_array('tag-wordpress', $classes), 'Should include tag-wordpress class');

    echo "✓ Test 5 passed: get_post_class() with tags\n";
  }

  /**
   * Test 6: get_post_class() with categories and tags
   */
  public function test_get_post_class_with_categories_and_tags() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 500,
      'post_type' => 'post',
      'post_status' => 'publish',
      'categories' => array(
        (object) array('term_id' => 1, 'slug' => 'tutorials'),
      ),
      'tags' => array(
        (object) array('term_id' => 20, 'slug' => 'beginner'),
        (object) array('term_id' => 21, 'slug' => 'advanced'),
      ),
    );

    $classes = get_post_class();

    assert(in_array('category-tutorials', $classes), 'Should include category class');
    assert(in_array('tag-beginner', $classes), 'Should include first tag class');
    assert(in_array('tag-advanced', $classes), 'Should include second tag class');

    echo "✓ Test 6 passed: get_post_class() with categories and tags\n";
  }

  /**
   * Test 7: get_post_class() with different post types
   */
  public function test_get_post_class_different_post_types() {
    // Test page type
    $page_post = (object) array(
      'ID' => 600,
      'post_type' => 'page',
      'post_status' => 'publish',
    );

    $classes = get_post_class('', $page_post);
    assert(in_array('type-page', $classes), 'Should include type-page');
    assert(in_array('page', $classes), 'Should include page class');

    // Test custom post type
    $custom_post = (object) array(
      'ID' => 700,
      'post_type' => 'product',
      'post_status' => 'publish',
    );

    $classes = get_post_class('', $custom_post);
    assert(in_array('type-product', $classes), 'Should include type-product');
    assert(in_array('product', $classes), 'Should include product class');

    echo "✓ Test 7 passed: get_post_class() with different post types\n";
  }

  /**
   * Test 8: get_post_class() with different statuses
   */
  public function test_get_post_class_different_statuses() {
    // Test draft status
    $draft_post = (object) array(
      'ID' => 800,
      'post_type' => 'post',
      'post_status' => 'draft',
    );

    $classes = get_post_class('', $draft_post);
    assert(in_array('status-draft', $classes), 'Should include status-draft');

    // Test pending status
    $pending_post = (object) array(
      'ID' => 900,
      'post_type' => 'post',
      'post_status' => 'pending',
    );

    $classes = get_post_class('', $pending_post);
    assert(in_array('status-pending', $classes), 'Should include status-pending');

    echo "✓ Test 8 passed: get_post_class() with different statuses\n";
  }

  /**
   * Test 9: get_post_class() with Backdrop node object
   */
  public function test_get_post_class_backdrop_node() {
    global $wp_post;

    // Create Backdrop-style node object
    $wp_post = (object) array(
      'nid' => 1000,
      'type' => 'article',
      'status' => 1, // Published in Backdrop
    );

    $classes = get_post_class();

    assert(in_array('post-1000', $classes), 'Should include post-{nid}');
    assert(in_array('type-article', $classes), 'Should include type-article');
    assert(in_array('status-publish', $classes), 'Should map Backdrop status to publish');
    assert(in_array('article', $classes), 'Should include article class');

    echo "✓ Test 9 passed: get_post_class() with Backdrop node object\n";
  }

  /**
   * Test 10: get_post_class() with no post returns empty (except custom classes)
   */
  public function test_get_post_class_no_post() {
    global $wp_post;
    $wp_post = null;

    $classes = get_post_class();
    assert(empty($classes), 'Should return empty array with no post');

    // But should return custom classes
    $classes = get_post_class('custom-class');
    assert(count($classes) === 1, 'Should return only custom class');
    assert(in_array('custom-class', $classes), 'Should include custom class');

    echo "✓ Test 10 passed: get_post_class() with no post\n";
  }

  /**
   * Test 11: post_class() echoes class attribute
   */
  public function test_post_class_echoes_attribute() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 1100,
      'post_type' => 'post',
      'post_status' => 'publish',
    );

    // Capture output
    ob_start();
    post_class();
    $output = ob_get_clean();

    assert(strpos($output, 'class="') === 0, 'Should start with class="');
    assert(substr($output, -1) === '"', 'Should end with "');
    assert(strpos($output, 'post-1100') !== false, 'Should include post ID in output');
    assert(strpos($output, 'hentry') !== false, 'Should include hentry in output');

    echo "✓ Test 11 passed: post_class() echoes class attribute\n";
  }

  /**
   * Test 12: post_class() with custom classes
   */
  public function test_post_class_with_custom_classes() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 1200,
      'post_type' => 'post',
      'post_status' => 'publish',
    );

    // Capture output
    ob_start();
    post_class('my-custom-class');
    $output = ob_get_clean();

    assert(strpos($output, 'my-custom-class') !== false, 'Should include custom class');
    assert(strpos($output, 'post-1200') !== false, 'Should include standard classes');

    echo "✓ Test 12 passed: post_class() with custom classes\n";
  }

  /**
   * Test 13: sanitize_html_class() sanitizes class names
   */
  public function test_sanitize_html_class() {
    // Test basic sanitization
    $result = sanitize_html_class('valid-class-name');
    assert($result === 'valid-class-name', 'Should pass valid class name');

    // Test removal of invalid characters
    $result = sanitize_html_class('invalid class!@#$%');
    assert($result === 'invalidclass', 'Should remove invalid characters');

    // Test fallback when result is empty
    $result = sanitize_html_class('!@#$', 'fallback');
    assert($result === 'fallback', 'Should return fallback for empty result');

    // Test fallback when starts with number
    $result = sanitize_html_class('123class', 'fallback');
    assert($result === 'fallback', 'Should return fallback when starts with number');

    // Test fallback when starts with hyphen
    $result = sanitize_html_class('-class', 'fallback');
    assert($result === 'fallback', 'Should return fallback when starts with hyphen');

    // Test underscore is allowed
    $result = sanitize_html_class('valid_class_name');
    assert($result === 'valid_class_name', 'Should allow underscores');

    echo "✓ Test 13 passed: sanitize_html_class() sanitizes class names\n";
  }

  /**
   * Test 14: get_post_class() returns unique classes
   */
  public function test_get_post_class_unique() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 1300,
      'post_type' => 'post',
      'post_status' => 'publish',
    );

    // Add duplicate custom class
    $classes = get_post_class('post hentry');

    // Count occurrences of 'post' and 'hentry'
    $post_count = 0;
    $hentry_count = 0;
    foreach ($classes as $class) {
      if ($class === 'post') $post_count++;
      if ($class === 'hentry') $hentry_count++;
    }

    assert($post_count === 1, 'Should have only one "post" class');
    assert($hentry_count === 1, 'Should have only one "hentry" class');

    echo "✓ Test 14 passed: get_post_class() returns unique classes\n";
  }

  /**
   * Test 15: get_post_class() with post parameter
   */
  public function test_get_post_class_with_post_parameter() {
    // Don't set global post
    global $wp_post;
    $wp_post = null;

    // Create post object to pass as parameter
    $post = (object) array(
      'ID' => 1400,
      'post_type' => 'post',
      'post_status' => 'publish',
    );

    $classes = get_post_class('', $post);

    assert(in_array('post-1400', $classes), 'Should use passed post object');
    assert(in_array('type-post', $classes), 'Should include post type from parameter');

    echo "✓ Test 15 passed: get_post_class() with post parameter\n";
  }

  /**
   * Test 16: get_post_class() with numeric post ID parameter
   */
  public function test_get_post_class_with_numeric_id() {
    global $wp_post;
    $wp_post = null;

    $classes = get_post_class('', 1500);

    assert(in_array('post-1500', $classes), 'Should create post with numeric ID');
    // Note: Other properties won't be available since we don't load from DB

    echo "✓ Test 16 passed: get_post_class() with numeric post ID parameter\n";
  }

  /**
   * Test 17: get_post_class() with categories as arrays
   */
  public function test_get_post_class_categories_as_arrays() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 1600,
      'post_type' => 'post',
      'post_status' => 'publish',
      'categories' => array(
        array('term_id' => 5, 'slug' => 'reviews'),
        array('term_id' => 6, 'slug' => 'howto'),
      ),
    );

    $classes = get_post_class();

    assert(in_array('category-reviews', $classes), 'Should handle array-format categories');
    assert(in_array('category-howto', $classes), 'Should include second category');

    echo "✓ Test 17 passed: get_post_class() with categories as arrays\n";
  }

  /**
   * Test 18: get_post_class() escapes special characters
   */
  public function test_get_post_class_escapes_special_chars() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 1700,
      'post_type' => 'post',
      'post_status' => 'publish',
      'categories' => array(
        (object) array('term_id' => 7, 'slug' => 'tips&tricks'),
      ),
    );

    $classes = get_post_class();

    // Check that special characters are removed
    $found_invalid = false;
    foreach ($classes as $class) {
      if (strpos($class, '&') !== false) {
        $found_invalid = true;
      }
    }

    assert(!$found_invalid, 'Should not include special characters like &');
    assert(in_array('category-tipstricks', $classes), 'Should sanitize category slug');

    echo "✓ Test 18 passed: get_post_class() escapes special characters\n";
  }

  /**
   * Test 19: Backdrop unpublished node gets draft status
   */
  public function test_backdrop_unpublished_status() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 1800,
      'type' => 'page',
      'status' => 0, // Unpublished in Backdrop
    );

    $classes = get_post_class();

    assert(in_array('status-draft', $classes), 'Should map Backdrop unpublished to draft');

    echo "✓ Test 19 passed: Backdrop unpublished node gets draft status\n";
  }

  /**
   * Test 20: Classes are properly separated by spaces
   */
  public function test_post_class_space_separation() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 1900,
      'post_type' => 'post',
      'post_status' => 'publish',
    );

    ob_start();
    post_class();
    $output = ob_get_clean();

    // Remove the class=" and trailing "
    $class_string = substr($output, 7, -1);

    // Split by space and verify no empty strings
    $classes = explode(' ', $class_string);
    $has_empty = false;
    foreach ($classes as $class) {
      if ($class === '') {
        $has_empty = true;
      }
    }

    assert(!$has_empty, 'Should not have empty class names');
    assert(count($classes) >= 5, 'Should have multiple classes');

    echo "✓ Test 20 passed: Classes are properly separated by spaces\n";
  }

  /**
   * Run all tests
   */
  public function runAllTests() {
    echo "\n=== Running post_class() Functions Tests ===\n\n";

    $this->setUp();
    $this->test_get_post_class_basic();

    $this->setUp();
    $this->test_get_post_class_custom_classes_string();

    $this->setUp();
    $this->test_get_post_class_custom_classes_array();

    $this->setUp();
    $this->test_get_post_class_with_categories();

    $this->setUp();
    $this->test_get_post_class_with_tags();

    $this->setUp();
    $this->test_get_post_class_with_categories_and_tags();

    $this->setUp();
    $this->test_get_post_class_different_post_types();

    $this->setUp();
    $this->test_get_post_class_different_statuses();

    $this->setUp();
    $this->test_get_post_class_backdrop_node();

    $this->setUp();
    $this->test_get_post_class_no_post();

    $this->setUp();
    $this->test_post_class_echoes_attribute();

    $this->setUp();
    $this->test_post_class_with_custom_classes();

    $this->setUp();
    $this->test_sanitize_html_class();

    $this->setUp();
    $this->test_get_post_class_unique();

    $this->setUp();
    $this->test_get_post_class_with_post_parameter();

    $this->setUp();
    $this->test_get_post_class_with_numeric_id();

    $this->setUp();
    $this->test_get_post_class_categories_as_arrays();

    $this->setUp();
    $this->test_get_post_class_escapes_special_chars();

    $this->setUp();
    $this->test_backdrop_unpublished_status();

    $this->setUp();
    $this->test_post_class_space_separation();

    echo "\n=== All 20 Tests Passed! ===\n\n";
  }
}

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli' && isset($argv[0]) && basename($argv[0]) === basename(__FILE__)) {
  $tester = new PostClassTest();
  $tester->runAllTests();
}
