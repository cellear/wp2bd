<?php
/**
 * Unit Tests for Permalink Functions
 *
 * Tests for get_permalink() and the_permalink() functions.
 * Verifies WordPress compatibility and Backdrop integration.
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include the functions to test
require_once dirname(__DIR__) . '/functions/content-display.php';

/**
 * Test class for permalink functions
 */
class PermalinkFunctionsTest {

  /**
   * Set up before each test
   */
  public function setUp() {
    // Reset global post
    global $wp_post, $base_url;
    $wp_post = null;
    $base_url = 'http://example.com';
  }

  /**
   * Mock Backdrop's url() function for testing
   */
  private function mockBackdropFunctions() {
    if (!function_exists('url')) {
      function url($path, $options = array()) {
        global $base_url;
        $base = isset($base_url) ? $base_url : 'http://localhost';
        $absolute = isset($options['absolute']) && $options['absolute'];

        if ($absolute) {
          return rtrim($base, '/') . '/' . ltrim($path, '/');
        }
        return '/' . ltrim($path, '/');
      }
    }

    if (!function_exists('backdrop_get_path_alias')) {
      function backdrop_get_path_alias($path) {
        // Mock path alias - return alias for specific test cases
        if ($path === 'node/123') {
          return 'blog/my-first-post';
        }
        if ($path === 'node/456') {
          return 'about-us';
        }
        // Default: return the original path (no alias)
        return $path;
      }
    }

    if (!function_exists('apply_filters')) {
      function apply_filters($hook, $value, $post = null, $leavename = false) {
        // Simple mock that adds a query parameter for testing
        if ($hook === 'post_link' && strpos($value, '?') === false) {
          return $value . '?filtered=true';
        }
        return $value;
      }
    }

    if (!function_exists('esc_url')) {
      function esc_url($url) {
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
      }
    }
  }

  /**
   * Test 1: get_permalink() returns permalink for WordPress-style post object
   */
  public function test_get_permalink_with_wordpress_post_object() {
    global $wp_post;
    $this->mockBackdropFunctions();

    // Create WordPress-style post object
    $wp_post = (object) array(
      'ID' => 123,
      'post_title' => 'My First Post',
      'post_content' => 'This is test content.',
    );

    $result = get_permalink();

    // Should return URL with path alias
    assert(strpos($result, 'blog/my-first-post') !== false, 'Should return permalink with path alias');
    echo "✓ Test 1 passed: get_permalink() returns permalink for WordPress-style post object\n";
  }

  /**
   * Test 2: get_permalink() returns permalink for Backdrop-style node object
   */
  public function test_get_permalink_with_backdrop_node_object() {
    global $wp_post;
    $this->mockBackdropFunctions();

    // Create Backdrop-style node object
    $wp_post = (object) array(
      'nid' => 456,
      'title' => 'About Us',
      'type' => 'page',
    );

    $result = get_permalink();

    // Should return URL with path alias
    assert(strpos($result, 'about-us') !== false, 'Should return permalink with path alias for node');
    echo "✓ Test 2 passed: get_permalink() returns permalink for Backdrop-style node object\n";
  }

  /**
   * Test 3: get_permalink() returns false for invalid post
   */
  public function test_get_permalink_with_null_post() {
    global $wp_post;
    $this->mockBackdropFunctions();

    $wp_post = null;

    $result = get_permalink();

    assert($result === false, 'Should return false when no post exists');
    echo "✓ Test 3 passed: get_permalink() returns false for invalid post\n";
  }

  /**
   * Test 4: get_permalink() accepts post ID parameter
   */
  public function test_get_permalink_with_numeric_id() {
    $this->mockBackdropFunctions();

    // Pass numeric ID
    $result = get_permalink(789);

    // Should return URL for node/789 (no alias)
    assert(strpos($result, 'node/789') !== false, 'Should return permalink for numeric ID');
    echo "✓ Test 4 passed: get_permalink() accepts post ID parameter\n";
  }

  /**
   * Test 5: get_permalink() accepts post object parameter
   */
  public function test_get_permalink_with_post_parameter() {
    $this->mockBackdropFunctions();

    // Create a post object to pass as parameter
    $post = (object) array(
      'ID' => 999,
      'post_title' => 'Parameterized Post',
    );

    $result = get_permalink($post);

    // Should return URL for node/999
    assert(strpos($result, 'node/999') !== false, 'Should return permalink from passed post object');
    echo "✓ Test 5 passed: get_permalink() accepts post object parameter\n";
  }

  /**
   * Test 6: get_permalink() returns false for invalid ID
   */
  public function test_get_permalink_with_invalid_id() {
    $this->mockBackdropFunctions();

    // Pass invalid ID (0 or negative)
    $result1 = get_permalink(0);
    $result2 = get_permalink(-1);

    assert($result1 === false, 'Should return false for ID 0');
    assert($result2 === false, 'Should return false for negative ID');
    echo "✓ Test 6 passed: get_permalink() returns false for invalid ID\n";
  }

  /**
   * Test 7: get_permalink() applies 'post_link' filter
   */
  public function test_get_permalink_applies_filter() {
    global $wp_post;
    $this->mockBackdropFunctions();

    $wp_post = (object) array(
      'ID' => 555,
      'post_title' => 'Filtered Post',
    );

    $result = get_permalink();

    // Mock filter adds ?filtered=true
    assert(strpos($result, '?filtered=true') !== false, 'Should apply post_link filter');
    echo "✓ Test 7 passed: get_permalink() applies 'post_link' filter\n";
  }

  /**
   * Test 8: get_permalink() generates absolute URLs
   */
  public function test_get_permalink_generates_absolute_url() {
    global $wp_post, $base_url;
    $this->mockBackdropFunctions();

    $base_url = 'http://mysite.com';
    $wp_post = (object) array(
      'ID' => 321,
      'post_title' => 'Absolute URL Test',
    );

    $result = get_permalink();

    // Should include the domain
    assert(strpos($result, 'http://') === 0, 'Should start with http://');
    assert(strpos($result, 'mysite.com') !== false, 'Should include domain from base_url');
    echo "✓ Test 8 passed: get_permalink() generates absolute URLs\n";
  }

  /**
   * Test 9: the_permalink() echoes permalink
   */
  public function test_the_permalink_outputs_url() {
    global $wp_post;
    $this->mockBackdropFunctions();

    $wp_post = (object) array(
      'ID' => 222,
      'post_title' => 'Echo Test Post',
    );

    // Capture output
    ob_start();
    the_permalink();
    $output = ob_get_clean();

    // Should output the permalink URL
    assert(!empty($output), 'Should echo output');
    assert(strpos($output, 'node/222') !== false, 'Should echo permalink URL');
    echo "✓ Test 9 passed: the_permalink() echoes permalink\n";
  }

  /**
   * Test 10: the_permalink() escapes URL for safe output
   */
  public function test_the_permalink_escapes_url() {
    global $wp_post;
    $this->mockBackdropFunctions();

    $wp_post = (object) array(
      'ID' => 333,
      'post_title' => 'Escape Test',
    );

    // Capture output
    ob_start();
    the_permalink();
    $output = ob_get_clean();

    // Should not contain unescaped characters
    // Our mock esc_url() uses htmlspecialchars
    assert(strpos($output, '<') === false, 'Should not contain unescaped < characters');
    echo "✓ Test 10 passed: the_permalink() escapes URL for safe output\n";
  }

  /**
   * Test 11: the_permalink() accepts post parameter
   */
  public function test_the_permalink_with_post_parameter() {
    $this->mockBackdropFunctions();

    $post = (object) array(
      'ID' => 777,
      'post_title' => 'Param Test',
    );

    // Capture output
    ob_start();
    the_permalink($post);
    $output = ob_get_clean();

    // Should output permalink for passed post
    assert(strpos($output, 'node/777') !== false, 'Should echo permalink from passed post');
    echo "✓ Test 11 passed: the_permalink() accepts post parameter\n";
  }

  /**
   * Test 12: the_permalink() handles null post gracefully
   */
  public function test_the_permalink_with_null_post() {
    global $wp_post;
    $this->mockBackdropFunctions();

    $wp_post = null;

    // Capture output
    ob_start();
    the_permalink();
    $output = ob_get_clean();

    // Should not output anything for null post
    assert($output === '', 'Should not echo anything when post is null');
    echo "✓ Test 12 passed: the_permalink() handles null post gracefully\n";
  }

  /**
   * Test 13: get_permalink() handles path without alias
   */
  public function test_get_permalink_without_alias() {
    global $wp_post;
    $this->mockBackdropFunctions();

    // Use an ID that doesn't have an alias in our mock
    $wp_post = (object) array(
      'ID' => 999,
      'post_title' => 'No Alias Post',
    );

    $result = get_permalink();

    // Should return node/999 (no alias)
    assert(strpos($result, 'node/999') !== false, 'Should return node/ID when no alias exists');
    echo "✓ Test 13 passed: get_permalink() handles path without alias\n";
  }

  /**
   * Test 14: get_permalink() handles object without ID or nid
   */
  public function test_get_permalink_with_invalid_object() {
    $this->mockBackdropFunctions();

    // Create object without ID or nid
    $post = (object) array(
      'title' => 'Invalid Object',
      'content' => 'No ID property',
    );

    $result = get_permalink($post);

    assert($result === false, 'Should return false for object without ID or nid');
    echo "✓ Test 14 passed: get_permalink() handles object without ID or nid\n";
  }

  /**
   * Test 15: get_permalink() works with string ID
   */
  public function test_get_permalink_with_string_id() {
    $this->mockBackdropFunctions();

    // Pass ID as string (should be cast to int)
    $result = get_permalink('123');

    // Should convert string to int and return permalink
    assert(strpos($result, 'blog/my-first-post') !== false, 'Should handle string ID parameter');
    echo "✓ Test 15 passed: get_permalink() works with string ID\n";
  }

  /**
   * Run all tests
   */
  public function runAllTests() {
    echo "\n=== Running Permalink Functions Tests ===\n\n";

    $this->setUp();
    $this->test_get_permalink_with_wordpress_post_object();

    $this->setUp();
    $this->test_get_permalink_with_backdrop_node_object();

    $this->setUp();
    $this->test_get_permalink_with_null_post();

    $this->setUp();
    $this->test_get_permalink_with_numeric_id();

    $this->setUp();
    $this->test_get_permalink_with_post_parameter();

    $this->setUp();
    $this->test_get_permalink_with_invalid_id();

    $this->setUp();
    $this->test_get_permalink_applies_filter();

    $this->setUp();
    $this->test_get_permalink_generates_absolute_url();

    $this->setUp();
    $this->test_the_permalink_outputs_url();

    $this->setUp();
    $this->test_the_permalink_escapes_url();

    $this->setUp();
    $this->test_the_permalink_with_post_parameter();

    $this->setUp();
    $this->test_the_permalink_with_null_post();

    $this->setUp();
    $this->test_get_permalink_without_alias();

    $this->setUp();
    $this->test_get_permalink_with_invalid_object();

    $this->setUp();
    $this->test_get_permalink_with_string_id();

    echo "\n=== All Permalink Tests Passed! ===\n\n";
  }
}

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli' && isset($argv[0]) && basename($argv[0]) === basename(__FILE__)) {
  $tester = new PermalinkFunctionsTest();
  $tester->runAllTests();
}
