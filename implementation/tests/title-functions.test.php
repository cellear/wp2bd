<?php
/**
 * Unit Tests for Title Display Functions
 *
 * Tests for get_the_title() and the_title() functions.
 * Verifies WordPress compatibility and Backdrop integration.
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include the functions to test
require_once dirname(__DIR__) . '/functions/content-display.php';

/**
 * Test class for title display functions
 */
class TitleFunctionsTest {

  /**
   * Set up before each test
   */
  public function setUp() {
    // Reset global post
    global $wp_post;
    $wp_post = null;
  }

  /**
   * Test 1: get_the_title() returns WordPress-style post title
   */
  public function test_get_the_title_with_wordpress_post_object() {
    global $wp_post;

    // Create WordPress-style post object
    $wp_post = (object) array(
      'ID' => 123,
      'post_title' => 'Hello WordPress World',
      'post_content' => 'This is test content.',
    );

    $result = get_the_title();

    assert($result === 'Hello WordPress World', 'Should return WordPress post title');
    echo "✓ Test 1 passed: get_the_title() returns WordPress-style post title\n";
  }

  /**
   * Test 2: get_the_title() returns Backdrop-style node title
   */
  public function test_get_the_title_with_backdrop_node_object() {
    global $wp_post;

    // Create Backdrop-style node object
    $wp_post = (object) array(
      'nid' => 456,
      'title' => 'Hello Backdrop World',
      'type' => 'article',
    );

    $result = get_the_title();

    assert($result === 'Hello Backdrop World', 'Should return Backdrop node title');
    echo "✓ Test 2 passed: get_the_title() returns Backdrop-style node title\n";
  }

  /**
   * Test 3: get_the_title() handles missing title gracefully
   */
  public function test_get_the_title_with_empty_title() {
    global $wp_post;

    // Create post with no title
    $wp_post = (object) array(
      'ID' => 789,
      'post_title' => '',
      'post_content' => 'Content without title.',
    );

    $result = get_the_title();

    assert($result === '', 'Should return empty string for missing title');
    echo "✓ Test 3 passed: get_the_title() handles missing title gracefully\n";
  }

  /**
   * Test 4: get_the_title() handles null post gracefully
   */
  public function test_get_the_title_with_null_post() {
    global $wp_post;
    $wp_post = null;

    $result = get_the_title();

    assert($result === '', 'Should return empty string when no post exists');
    echo "✓ Test 4 passed: get_the_title() handles null post gracefully\n";
  }

  /**
   * Test 5: get_the_title() accepts post parameter
   */
  public function test_get_the_title_with_post_parameter() {
    // Create a post object to pass as parameter
    $post = (object) array(
      'ID' => 999,
      'post_title' => 'Parameterized Post Title',
    );

    $result = get_the_title($post);

    assert($result === 'Parameterized Post Title', 'Should return title from passed post object');
    echo "✓ Test 5 passed: get_the_title() accepts post parameter\n";
  }

  /**
   * Test 6: the_title() outputs title with before/after wrappers
   */
  public function test_the_title_with_wrappers() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 111,
      'post_title' => 'Wrapped Title',
    );

    // Capture output
    ob_start();
    the_title('<h1>', '</h1>');
    $output = ob_get_clean();

    assert($output === '<h1>Wrapped Title</h1>', 'Should wrap title in before/after markup');
    echo "✓ Test 6 passed: the_title() outputs title with before/after wrappers\n";
  }

  /**
   * Test 7: the_title() with echo=false returns instead of echoing
   */
  public function test_the_title_return_mode() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 222,
      'post_title' => 'Returned Title',
    );

    // Capture output to ensure nothing is echoed
    ob_start();
    $result = the_title('<h2>', '</h2>', false);
    $output = ob_get_clean();

    assert($output === '', 'Should not echo when echo=false');
    assert($result === '<h2>Returned Title</h2>', 'Should return wrapped title when echo=false');
    echo "✓ Test 7 passed: the_title() with echo=false returns instead of echoing\n";
  }

  /**
   * Test 8: the_title() handles empty title gracefully
   */
  public function test_the_title_with_empty_title() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 333,
      'post_title' => '',
    );

    // Capture output
    ob_start();
    the_title('<div>', '</div>');
    $output = ob_get_clean();

    assert($output === '', 'Should not output wrappers when title is empty');
    echo "✓ Test 8 passed: the_title() handles empty title gracefully\n";
  }

  /**
   * Test 9: get_the_title() applies 'the_title' filter
   */
  public function test_get_the_title_applies_filter() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 444,
      'post_title' => 'Original Title',
    );

    // Mock apply_filters function if it doesn't exist
    if (!function_exists('apply_filters')) {
      function apply_filters($hook, $value, $post_id = 0) {
        // Simple mock that uppercases the title for testing
        if ($hook === 'the_title') {
          return strtoupper($value);
        }
        return $value;
      }
    }

    $result = get_the_title();

    assert($result === 'ORIGINAL TITLE', 'Should apply the_title filter');
    echo "✓ Test 9 passed: get_the_title() applies 'the_title' filter\n";
  }

  /**
   * Test 10: get_the_title() with numeric ID parameter
   */
  public function test_get_the_title_with_numeric_id() {
    // Pass numeric ID (basic implementation returns empty for now)
    $result = get_the_title(555);

    // In basic implementation, this returns empty string
    // Full implementation would load the post from database
    assert($result === '', 'Should handle numeric ID parameter');
    echo "✓ Test 10 passed: get_the_title() with numeric ID parameter\n";
  }

  /**
   * Run all tests
   */
  public function runAllTests() {
    echo "\n=== Running Title Functions Tests ===\n\n";

    $this->setUp();
    $this->test_get_the_title_with_wordpress_post_object();

    $this->setUp();
    $this->test_get_the_title_with_backdrop_node_object();

    $this->setUp();
    $this->test_get_the_title_with_empty_title();

    $this->setUp();
    $this->test_get_the_title_with_null_post();

    $this->setUp();
    $this->test_get_the_title_with_post_parameter();

    $this->setUp();
    $this->test_the_title_with_wrappers();

    $this->setUp();
    $this->test_the_title_return_mode();

    $this->setUp();
    $this->test_the_title_with_empty_title();

    $this->setUp();
    $this->test_get_the_title_applies_filter();

    $this->setUp();
    $this->test_get_the_title_with_numeric_id();

    echo "\n=== All Tests Passed! ===\n\n";
  }
}

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli' && isset($argv[0]) && basename($argv[0]) === basename(__FILE__)) {
  $tester = new TitleFunctionsTest();
  $tester->runAllTests();
}
