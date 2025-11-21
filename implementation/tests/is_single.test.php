<?php
/**
 * Unit Tests for Conditional Functions
 *
 * Tests for is_single() and is_singular() functions.
 * Verifies WordPress compatibility and Backdrop integration.
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include the functions to test
require_once dirname(__DIR__) . '/functions/conditionals.php';

/**
 * Test class for conditional functions
 */
class ConditionalFunctionsTest {

  /**
   * Set up before each test
   */
  public function setUp() {
    // Reset global post
    global $wp_post;
    $wp_post = null;

    // Mock menu_get_object to return null by default
    if (!function_exists('menu_get_object')) {
      function menu_get_object($type = 'node') {
        global $mock_menu_object;
        return isset($mock_menu_object) ? $mock_menu_object : null;
      }
    }

    // Reset mock
    global $mock_menu_object;
    $mock_menu_object = null;
  }

  // ========================================
  // is_single() Tests
  // ========================================

  /**
   * Test 1: is_single() returns true for article/post node
   */
  public function test_is_single_returns_true_for_post() {
    global $wp_post;

    // Create a post-type node (Backdrop-style)
    $wp_post = (object) array(
      'nid' => 123,
      'type' => 'post',
      'title' => 'Test Post',
    );

    $result = is_single();

    assert($result === true, 'is_single() should return true for post type');
    echo "✓ Test 1 passed: is_single() returns true for post node\n";
  }

  /**
   * Test 2: is_single() returns true for article type
   */
  public function test_is_single_returns_true_for_article() {
    global $wp_post;

    // Create an article-type node (common Backdrop content type)
    $wp_post = (object) array(
      'nid' => 456,
      'type' => 'article',
      'title' => 'Test Article',
    );

    $result = is_single();

    assert($result === true, 'is_single() should return true for article type');
    echo "✓ Test 2 passed: is_single() returns true for article node\n";
  }

  /**
   * Test 3: is_single() returns false for page node
   */
  public function test_is_single_returns_false_for_page() {
    global $wp_post;

    // Create a page-type node
    $wp_post = (object) array(
      'nid' => 789,
      'type' => 'page',
      'title' => 'Test Page',
    );

    $result = is_single();

    assert($result === false, 'is_single() should return false for page type');
    echo "✓ Test 3 passed: is_single() returns false for page node\n";
  }

  /**
   * Test 4: is_single() returns false when no node is set
   */
  public function test_is_single_returns_false_with_no_node() {
    global $wp_post;
    $wp_post = null;

    $result = is_single();

    assert($result === false, 'is_single() should return false when no node exists');
    echo "✓ Test 4 passed: is_single() returns false with no node\n";
  }

  /**
   * Test 5: is_single() checks specific post by ID
   */
  public function test_is_single_with_matching_post_id() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 100,
      'type' => 'post',
      'title' => 'Specific Post',
    );

    $result = is_single(100);

    assert($result === true, 'is_single(100) should return true when viewing post 100');
    echo "✓ Test 5 passed: is_single() with matching post ID\n";
  }

  /**
   * Test 6: is_single() returns false for non-matching post ID
   */
  public function test_is_single_with_non_matching_post_id() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 100,
      'type' => 'post',
      'title' => 'Specific Post',
    );

    $result = is_single(999);

    assert($result === false, 'is_single(999) should return false when viewing post 100');
    echo "✓ Test 6 passed: is_single() with non-matching post ID\n";
  }

  /**
   * Test 7: is_single() checks specific post by slug
   */
  public function test_is_single_with_matching_slug() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 200,
      'type' => 'post',
      'title' => 'My Post',
      'post_name' => 'my-post',
    );

    $result = is_single('my-post');

    assert($result === true, 'is_single("my-post") should return true for matching slug');
    echo "✓ Test 7 passed: is_single() with matching post slug\n";
  }

  /**
   * Test 8: is_single() with array of post IDs
   */
  public function test_is_single_with_array_of_ids() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 150,
      'type' => 'post',
      'title' => 'Test Post',
    );

    $result = is_single(array(100, 150, 200));

    assert($result === true, 'is_single() should return true when ID is in array');
    echo "✓ Test 8 passed: is_single() with array of IDs (one matches)\n";
  }

  /**
   * Test 9: is_single() with array of slugs
   */
  public function test_is_single_with_array_of_slugs() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 300,
      'type' => 'post',
      'title' => 'Featured Post',
      'post_name' => 'featured-post',
    );

    $result = is_single(array('news-post', 'featured-post', 'about-post'));

    assert($result === true, 'is_single() should return true when slug is in array');
    echo "✓ Test 9 passed: is_single() with array of slugs (one matches)\n";
  }

  /**
   * Test 10: is_single() works with WordPress-style post object
   */
  public function test_is_single_with_wordpress_post_object() {
    global $wp_post;

    // WordPress-style post object
    $wp_post = (object) array(
      'ID' => 500,
      'post_type' => 'post',
      'post_title' => 'WordPress Post',
    );

    $result = is_single();

    assert($result === true, 'is_single() should work with WordPress-style post object');
    echo "✓ Test 10 passed: is_single() with WordPress post object\n";
  }

  /**
   * Test 11: is_single() returns false for WordPress-style page
   */
  public function test_is_single_with_wordpress_page_object() {
    global $wp_post;

    // WordPress-style page object
    $wp_post = (object) array(
      'ID' => 600,
      'post_type' => 'page',
      'post_title' => 'WordPress Page',
    );

    $result = is_single();

    assert($result === false, 'is_single() should return false for WordPress-style page');
    echo "✓ Test 11 passed: is_single() returns false for WordPress page\n";
  }

  /**
   * Test 12: is_single() with custom post type (should return true)
   */
  public function test_is_single_with_custom_post_type() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 700,
      'type' => 'portfolio',
      'title' => 'Portfolio Item',
    );

    $result = is_single();

    assert($result === true, 'is_single() should return true for custom post types');
    echo "✓ Test 12 passed: is_single() returns true for custom post type\n";
  }

  // ========================================
  // is_singular() Tests
  // ========================================

  /**
   * Test 13: is_singular() returns true for any single post
   */
  public function test_is_singular_returns_true_for_post() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 123,
      'type' => 'post',
      'title' => 'Test Post',
    );

    $result = is_singular();

    assert($result === true, 'is_singular() should return true for post');
    echo "✓ Test 13 passed: is_singular() returns true for post\n";
  }

  /**
   * Test 14: is_singular() returns true for page
   */
  public function test_is_singular_returns_true_for_page() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 789,
      'type' => 'page',
      'title' => 'Test Page',
    );

    $result = is_singular();

    assert($result === true, 'is_singular() should return true for page');
    echo "✓ Test 14 passed: is_singular() returns true for page\n";
  }

  /**
   * Test 15: is_singular() returns false when no node is set
   */
  public function test_is_singular_returns_false_with_no_node() {
    global $wp_post;
    $wp_post = null;

    $result = is_singular();

    assert($result === false, 'is_singular() should return false when no node exists');
    echo "✓ Test 15 passed: is_singular() returns false with no node\n";
  }

  /**
   * Test 16: is_singular() with specific post type match
   */
  public function test_is_singular_with_matching_post_type() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 100,
      'type' => 'article',
      'title' => 'Test Article',
    );

    $result = is_singular('article');

    assert($result === true, 'is_singular("article") should return true for article type');
    echo "✓ Test 16 passed: is_singular() with matching post type\n";
  }

  /**
   * Test 17: is_singular() with non-matching post type
   */
  public function test_is_singular_with_non_matching_post_type() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 100,
      'type' => 'article',
      'title' => 'Test Article',
    );

    $result = is_singular('page');

    assert($result === false, 'is_singular("page") should return false for article type');
    echo "✓ Test 17 passed: is_singular() with non-matching post type\n";
  }

  /**
   * Test 18: is_singular() with array of post types
   */
  public function test_is_singular_with_array_of_post_types() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 200,
      'type' => 'portfolio',
      'title' => 'Portfolio Item',
    );

    $result = is_singular(array('post', 'page', 'portfolio'));

    assert($result === true, 'is_singular() should return true when type is in array');
    echo "✓ Test 18 passed: is_singular() with array of post types (one matches)\n";
  }

  /**
   * Test 19: is_singular() with array - no match
   */
  public function test_is_singular_with_array_no_match() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 200,
      'type' => 'portfolio',
      'title' => 'Portfolio Item',
    );

    $result = is_singular(array('post', 'page', 'article'));

    assert($result === false, 'is_singular() should return false when type not in array');
    echo "✓ Test 19 passed: is_singular() with array of post types (none match)\n";
  }

  /**
   * Test 20: is_singular() works with WordPress-style post object
   */
  public function test_is_singular_with_wordpress_post_object() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 500,
      'post_type' => 'post',
      'post_title' => 'WordPress Post',
    );

    $result = is_singular();

    assert($result === true, 'is_singular() should work with WordPress-style post object');
    echo "✓ Test 20 passed: is_singular() with WordPress post object\n";
  }

  /**
   * Test 21: is_singular() with WordPress page object
   */
  public function test_is_singular_with_wordpress_page_object() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 600,
      'post_type' => 'page',
      'post_title' => 'WordPress Page',
    );

    $result = is_singular();

    assert($result === true, 'is_singular() should return true for WordPress-style page');
    echo "✓ Test 21 passed: is_singular() with WordPress page object\n";
  }

  /**
   * Test 22: is_singular() checks page post type specifically
   */
  public function test_is_singular_with_page_post_type() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 700,
      'type' => 'page',
      'title' => 'About Page',
    );

    $result = is_singular('page');

    assert($result === true, 'is_singular("page") should return true for page type');
    echo "✓ Test 22 passed: is_singular() matches page post type\n";
  }

  /**
   * Test 23: is_single() with string numeric ID
   */
  public function test_is_single_with_string_numeric_id() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 888,
      'type' => 'post',
      'title' => 'Test Post',
    );

    $result = is_single('888');

    assert($result === true, 'is_single() should handle string numeric IDs');
    echo "✓ Test 23 passed: is_single() with string numeric ID\n";
  }

  /**
   * Test 24: is_single() returns false when checking page ID against page type
   */
  public function test_is_single_with_page_id_returns_false() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 999,
      'type' => 'page',
      'title' => 'Page Title',
    );

    // Even though the ID matches, is_single() should return false for pages
    $result = is_single(999);

    assert($result === false, 'is_single(ID) should return false even with matching page ID');
    echo "✓ Test 24 passed: is_single() returns false for matching page ID\n";
  }

  /**
   * Test 25: is_singular() with empty post types argument
   */
  public function test_is_singular_with_empty_post_types() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 111,
      'type' => 'custom_type',
      'title' => 'Custom Content',
    );

    $result = is_singular('');

    assert($result === true, 'is_singular("") should return true for any singular page');
    echo "✓ Test 25 passed: is_singular() with empty post types\n";
  }

  /**
   * Test 26: is_single() with Backdrop name property
   */
  public function test_is_single_with_backdrop_name_property() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 222,
      'type' => 'post',
      'title' => 'Named Post',
      'name' => 'named-post',
    );

    $result = is_single('named-post');

    assert($result === true, 'is_single() should check Backdrop name property');
    echo "✓ Test 26 passed: is_single() with Backdrop name property\n";
  }

  /**
   * Test 27: Helper function works with mixed array
   */
  public function test_is_single_with_mixed_id_slug_array() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 333,
      'type' => 'post',
      'title' => 'Mixed Test',
      'post_name' => 'mixed-test',
    );

    $result = is_single(array(100, 'other-slug', 333, 'another'));

    assert($result === true, 'is_single() should handle mixed ID/slug arrays');
    echo "✓ Test 27 passed: is_single() with mixed ID/slug array\n";
  }

  /**
   * Test 28: is_single() returns false for node without type
   */
  public function test_is_single_with_node_missing_type() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 444,
      'title' => 'No Type Node',
    );

    $result = is_single();

    assert($result === false, 'is_single() should return false for node without type');
    echo "✓ Test 28 passed: is_single() returns false for node missing type\n";
  }

  /**
   * Test 29: is_singular() returns false for node without type when checking specific type
   */
  public function test_is_singular_with_node_missing_type() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 555,
      'title' => 'No Type Node',
    );

    $result = is_singular('post');

    assert($result === false, 'is_singular() should return false for node without type');
    echo "✓ Test 29 passed: is_singular() returns false for node missing type\n";
  }

  /**
   * Test 30: is_singular() returns true for node without type when no type specified
   */
  public function test_is_singular_with_node_missing_type_no_filter() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 666,
      'title' => 'No Type Node',
    );

    $result = is_singular();

    // When no type filter is provided, just having a node object means it's singular
    assert($result === true, 'is_singular() should return true when no type check needed');
    echo "✓ Test 30 passed: is_singular() returns true for any node when no type specified\n";
  }

  /**
   * Run all tests
   */
  public function runAllTests() {
    echo "\n=== Running Conditional Functions Tests ===\n\n";

    // is_single() tests
    $this->setUp();
    $this->test_is_single_returns_true_for_post();

    $this->setUp();
    $this->test_is_single_returns_true_for_article();

    $this->setUp();
    $this->test_is_single_returns_false_for_page();

    $this->setUp();
    $this->test_is_single_returns_false_with_no_node();

    $this->setUp();
    $this->test_is_single_with_matching_post_id();

    $this->setUp();
    $this->test_is_single_with_non_matching_post_id();

    $this->setUp();
    $this->test_is_single_with_matching_slug();

    $this->setUp();
    $this->test_is_single_with_array_of_ids();

    $this->setUp();
    $this->test_is_single_with_array_of_slugs();

    $this->setUp();
    $this->test_is_single_with_wordpress_post_object();

    $this->setUp();
    $this->test_is_single_with_wordpress_page_object();

    $this->setUp();
    $this->test_is_single_with_custom_post_type();

    // is_singular() tests
    $this->setUp();
    $this->test_is_singular_returns_true_for_post();

    $this->setUp();
    $this->test_is_singular_returns_true_for_page();

    $this->setUp();
    $this->test_is_singular_returns_false_with_no_node();

    $this->setUp();
    $this->test_is_singular_with_matching_post_type();

    $this->setUp();
    $this->test_is_singular_with_non_matching_post_type();

    $this->setUp();
    $this->test_is_singular_with_array_of_post_types();

    $this->setUp();
    $this->test_is_singular_with_array_no_match();

    $this->setUp();
    $this->test_is_singular_with_wordpress_post_object();

    $this->setUp();
    $this->test_is_singular_with_wordpress_page_object();

    $this->setUp();
    $this->test_is_singular_with_page_post_type();

    // Additional edge case tests
    $this->setUp();
    $this->test_is_single_with_string_numeric_id();

    $this->setUp();
    $this->test_is_single_with_page_id_returns_false();

    $this->setUp();
    $this->test_is_singular_with_empty_post_types();

    $this->setUp();
    $this->test_is_single_with_backdrop_name_property();

    $this->setUp();
    $this->test_is_single_with_mixed_id_slug_array();

    $this->setUp();
    $this->test_is_single_with_node_missing_type();

    $this->setUp();
    $this->test_is_singular_with_node_missing_type();

    $this->setUp();
    $this->test_is_singular_with_node_missing_type_no_filter();

    echo "\n=== All 30 Tests Passed! ===\n\n";
  }
}

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli' && isset($argv[0]) && basename($argv[0]) === basename(__FILE__)) {
  $tester = new ConditionalFunctionsTest();
  $tester->runAllTests();
}
