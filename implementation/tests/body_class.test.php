<?php
/**
 * Unit Tests for body_class() Function
 *
 * Tests for body_class() and get_body_class() functions.
 * Verifies WordPress compatibility and Backdrop integration.
 * Tests dynamic CSS class generation for different page contexts.
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include the functions to test
require_once dirname(__DIR__) . '/functions/content-display.php';
require_once dirname(__DIR__) . '/functions/loop.php';

/**
 * Test class for body_class functions
 */
class BodyClassTest {

  /**
   * Set up before each test
   */
  public function setUp() {
    // Reset global query
    global $wp_query, $user;
    $wp_query = null;
    $user = null;
  }

  /**
   * Test 1: Home page (front page) classes
   */
  public function test_body_class_front_page() {
    global $wp_query;

    // Mock WP_Query for front page
    $wp_query = (object) array(
      'is_front_page' => true,
      'is_home' => false,
      'is_singular' => false,
      'is_single' => false,
      'is_page' => false,
      'is_archive' => false,
      'is_search' => false,
      'is_404' => false,
      'posts' => array(),
    );

    $classes = get_body_class();

    assert(in_array('home', $classes), 'Should include "home" class on front page');
    assert(!in_array('blog', $classes), 'Should not include "blog" class on front page');
    assert(!in_array('error404', $classes), 'Should not include "error404" class');

    echo "✓ Test 1 passed: Home page (front page) classes\n";
  }

  /**
   * Test 2: Blog home page classes
   */
  public function test_body_class_blog_home() {
    global $wp_query;

    // Mock WP_Query for blog home
    $wp_query = (object) array(
      'is_front_page' => false,
      'is_home' => true,
      'is_singular' => false,
      'is_single' => false,
      'is_page' => false,
      'is_archive' => false,
      'is_search' => false,
      'is_404' => false,
      'posts' => array(),
    );

    $classes = get_body_class();

    assert(in_array('blog', $classes), 'Should include "blog" class on blog home');
    assert(!in_array('home', $classes), 'Should not include "home" class (only for front page)');

    echo "✓ Test 2 passed: Blog home page classes\n";
  }

  /**
   * Test 3: Single post classes
   */
  public function test_body_class_single_post() {
    global $wp_query;

    // Mock WP_Query for single post
    $wp_query = (object) array(
      'is_front_page' => false,
      'is_home' => false,
      'is_singular' => true,
      'is_single' => true,
      'is_page' => false,
      'is_archive' => false,
      'is_search' => false,
      'is_404' => false,
      'posts' => array(),
    );

    // Mock post object
    $post = (object) array(
      'ID' => 123,
      'post_type' => 'post',
      'post_title' => 'Test Post',
    );

    // Mock methods for get_queried_object
    $wp_query->get_queried_object_id = function() { return 123; };
    $wp_query->get_queried_object = function() use ($post) { return $post; };

    // Make methods work
    $wp_query = new class($wp_query, $post) {
      private $data;
      private $post;

      public function __construct($data, $post) {
        $this->data = $data;
        $this->post = $post;
        foreach (get_object_vars($data) as $key => $value) {
          if (!is_callable($value)) {
            $this->$key = $value;
          }
        }
      }

      public function get_queried_object_id() {
        return 123;
      }

      public function get_queried_object() {
        return $this->post;
      }

      public function get($key) {
        return isset($this->data->$key) ? $this->data->$key : null;
      }
    };

    $classes = get_body_class();

    assert(in_array('single', $classes), 'Should include "single" class');
    assert(in_array('single-post', $classes), 'Should include "single-post" class');
    assert(in_array('postid-123', $classes), 'Should include "postid-123" class');
    assert(in_array('post-template-default', $classes), 'Should include "post-template-default" class');

    echo "✓ Test 3 passed: Single post classes\n";
  }

  /**
   * Test 4: Static page classes
   */
  public function test_body_class_static_page() {
    global $wp_query;

    // Mock WP_Query for page
    $wp_query = (object) array(
      'is_front_page' => false,
      'is_home' => false,
      'is_singular' => true,
      'is_single' => false,
      'is_page' => true,
      'is_archive' => false,
      'is_search' => false,
      'is_404' => false,
      'posts' => array(),
    );

    // Mock page object
    $page = (object) array(
      'ID' => 456,
      'post_type' => 'page',
      'post_title' => 'About Us',
      'post_parent' => 0,
    );

    // Make WP_Query with methods
    $wp_query = new class($wp_query, $page) {
      private $data;
      private $page;

      public function __construct($data, $page) {
        $this->data = $data;
        $this->page = $page;
        foreach (get_object_vars($data) as $key => $value) {
          if (!is_callable($value)) {
            $this->$key = $value;
          }
        }
      }

      public function get_queried_object_id() {
        return 456;
      }

      public function get_queried_object() {
        return $this->page;
      }

      public function get($key) {
        return isset($this->data->$key) ? $this->data->$key : null;
      }
    };

    $classes = get_body_class();

    assert(in_array('page', $classes), 'Should include "page" class');
    assert(in_array('page-id-456', $classes), 'Should include "page-id-456" class');
    assert(in_array('page-template-default', $classes), 'Should include "page-template-default" class');
    assert(!in_array('single', $classes), 'Should not include "single" class for pages');

    echo "✓ Test 4 passed: Static page classes\n";
  }

  /**
   * Test 5: Archive page classes
   */
  public function test_body_class_archive() {
    global $wp_query;

    // Mock WP_Query for archive
    $wp_query = (object) array(
      'is_front_page' => false,
      'is_home' => false,
      'is_singular' => false,
      'is_single' => false,
      'is_page' => false,
      'is_archive' => true,
      'is_search' => false,
      'is_404' => false,
      'posts' => array((object)['ID' => 1], (object)['ID' => 2]),
    );

    $classes = get_body_class();

    assert(in_array('archive', $classes), 'Should include "archive" class');
    assert(!in_array('single', $classes), 'Should not include "single" class');
    assert(!in_array('page', $classes), 'Should not include "page" class');

    echo "✓ Test 5 passed: Archive page classes\n";
  }

  /**
   * Test 6: Search results with results
   */
  public function test_body_class_search_with_results() {
    global $wp_query;

    // Mock WP_Query for search with results
    $wp_query = (object) array(
      'is_front_page' => false,
      'is_home' => false,
      'is_singular' => false,
      'is_single' => false,
      'is_page' => false,
      'is_archive' => false,
      'is_search' => true,
      'is_404' => false,
      'posts' => array((object)['ID' => 1], (object)['ID' => 2], (object)['ID' => 3]),
    );

    $classes = get_body_class();

    assert(in_array('search', $classes), 'Should include "search" class');
    assert(in_array('search-results', $classes), 'Should include "search-results" class when results found');
    assert(!in_array('search-no-results', $classes), 'Should not include "search-no-results" class');

    echo "✓ Test 6 passed: Search results with results\n";
  }

  /**
   * Test 7: Search results with no results
   */
  public function test_body_class_search_no_results() {
    global $wp_query;

    // Mock WP_Query for search with no results
    $wp_query = (object) array(
      'is_front_page' => false,
      'is_home' => false,
      'is_singular' => false,
      'is_single' => false,
      'is_page' => false,
      'is_archive' => false,
      'is_search' => true,
      'is_404' => false,
      'posts' => array(), // Empty results
    );

    $classes = get_body_class();

    assert(in_array('search', $classes), 'Should include "search" class');
    assert(in_array('search-no-results', $classes), 'Should include "search-no-results" class when no results');
    assert(!in_array('search-results', $classes), 'Should not include "search-results" class');

    echo "✓ Test 7 passed: Search results with no results\n";
  }

  /**
   * Test 8: 404 error page
   */
  public function test_body_class_404() {
    global $wp_query;

    // Mock WP_Query for 404
    $wp_query = (object) array(
      'is_front_page' => false,
      'is_home' => false,
      'is_singular' => false,
      'is_single' => false,
      'is_page' => false,
      'is_archive' => false,
      'is_search' => false,
      'is_404' => true,
      'posts' => array(),
    );

    $classes = get_body_class();

    assert(in_array('error404', $classes), 'Should include "error404" class');
    assert(!in_array('home', $classes), 'Should not include "home" class');
    assert(!in_array('single', $classes), 'Should not include "single" class');

    echo "✓ Test 8 passed: 404 error page\n";
  }

  /**
   * Test 9: Custom classes parameter
   */
  public function test_body_class_custom_classes() {
    global $wp_query;

    // Mock simple WP_Query
    $wp_query = (object) array(
      'is_front_page' => true,
      'is_home' => false,
      'is_singular' => false,
      'is_single' => false,
      'is_page' => false,
      'is_archive' => false,
      'is_search' => false,
      'is_404' => false,
      'posts' => array(),
    );

    // Test with string of classes
    $classes = get_body_class('custom-class another-class');

    assert(in_array('custom-class', $classes), 'Should include custom class from string');
    assert(in_array('another-class', $classes), 'Should include second custom class from string');
    assert(in_array('home', $classes), 'Should still include default "home" class');

    // Test with array of classes
    $classes = get_body_class(array('array-class-1', 'array-class-2'));

    assert(in_array('array-class-1', $classes), 'Should include custom class from array');
    assert(in_array('array-class-2', $classes), 'Should include second custom class from array');

    echo "✓ Test 9 passed: Custom classes parameter\n";
  }

  /**
   * Test 10: Logged-in user classes
   */
  public function test_body_class_logged_in() {
    global $wp_query, $user;

    // Mock WP_Query
    $wp_query = (object) array(
      'is_front_page' => true,
      'is_home' => false,
      'is_singular' => false,
      'is_single' => false,
      'is_page' => false,
      'is_archive' => false,
      'is_search' => false,
      'is_404' => false,
      'posts' => array(),
    );

    // Mock logged-in user (Backdrop style)
    $user = (object) array(
      'uid' => 1,
      'name' => 'admin',
    );

    $classes = get_body_class();

    assert(in_array('logged-in', $classes), 'Should include "logged-in" class for authenticated user');
    assert(in_array('admin-bar', $classes), 'Should include "admin-bar" class for admin user (uid 1)');

    echo "✓ Test 10 passed: Logged-in user classes\n";
  }

  /**
   * Test 11: body_class() function output
   */
  public function test_body_class_function_output() {
    global $wp_query;

    // Mock WP_Query for front page
    $wp_query = (object) array(
      'is_front_page' => true,
      'is_home' => false,
      'is_singular' => false,
      'is_single' => false,
      'is_page' => false,
      'is_archive' => false,
      'is_search' => false,
      'is_404' => false,
      'posts' => array(),
    );

    // Capture output
    ob_start();
    body_class('extra-class');
    $output = ob_get_clean();

    // Verify output format
    assert(strpos($output, 'class="') === 0, 'Should start with class="');
    assert(substr($output, -1) === '"', 'Should end with "');
    assert(strpos($output, 'home') !== false, 'Should contain "home" class');
    assert(strpos($output, 'extra-class') !== false, 'Should contain custom "extra-class"');

    // Verify classes are space-separated
    $class_attr = substr($output, 7, -1); // Remove class=" and trailing "
    $classes = explode(' ', $class_attr);
    assert(count($classes) >= 2, 'Should have at least 2 classes');

    echo "✓ Test 11 passed: body_class() function output\n";
  }

  /**
   * Test 12: Unique classes (no duplicates)
   */
  public function test_body_class_unique_classes() {
    global $wp_query;

    // Mock WP_Query
    $wp_query = (object) array(
      'is_front_page' => true,
      'is_home' => false,
      'is_singular' => false,
      'is_single' => false,
      'is_page' => false,
      'is_archive' => false,
      'is_search' => false,
      'is_404' => false,
      'posts' => array(),
    );

    // Add duplicate classes
    $classes = get_body_class('home duplicate-class duplicate-class');

    // Count occurrences of each class
    $class_counts = array_count_values($classes);

    foreach ($class_counts as $class => $count) {
      assert($count === 1, "Class '$class' should appear only once, but appears $count times");
    }

    echo "✓ Test 12 passed: Unique classes (no duplicates)\n";
  }

  /**
   * Run all tests
   */
  public function runAllTests() {
    echo "\n=== Running body_class() Tests ===\n\n";

    $this->setUp();
    $this->test_body_class_front_page();

    $this->setUp();
    $this->test_body_class_blog_home();

    $this->setUp();
    $this->test_body_class_single_post();

    $this->setUp();
    $this->test_body_class_static_page();

    $this->setUp();
    $this->test_body_class_archive();

    $this->setUp();
    $this->test_body_class_search_with_results();

    $this->setUp();
    $this->test_body_class_search_no_results();

    $this->setUp();
    $this->test_body_class_404();

    $this->setUp();
    $this->test_body_class_custom_classes();

    $this->setUp();
    $this->test_body_class_logged_in();

    $this->setUp();
    $this->test_body_class_function_output();

    $this->setUp();
    $this->test_body_class_unique_classes();

    echo "\n=== All 12 Tests Passed! ===\n\n";
  }
}

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli' && isset($argv[0]) && basename($argv[0]) === basename(__FILE__)) {
  $tester = new BodyClassTest();
  $tester->runAllTests();
}
