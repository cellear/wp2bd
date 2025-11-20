<?php
/**
 * Unit Tests for Post ID Functions
 *
 * Tests for get_the_ID() and the_ID() functions.
 * Verifies WordPress compatibility and Backdrop integration.
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include the functions to test
require_once dirname(__DIR__) . '/functions/content-display.php';

/**
 * Test class for post ID functions
 */
class IDFunctionsTest {

  /**
   * Set up before each test
   */
  public function setUp() {
    // Reset global post
    global $wp_post;
    $wp_post = null;
  }

  /**
   * Test 1: get_the_ID() returns WordPress-style post ID
   */
  public function test_get_the_ID_with_wordpress_post_object() {
    global $wp_post;

    // Create WordPress-style post object
    $wp_post = (object) array(
      'ID' => 123,
      'post_title' => 'Test Post',
      'post_content' => 'This is test content.',
    );

    $result = get_the_ID();

    assert($result === 123, 'Should return WordPress post ID as integer');
    assert(is_int($result), 'Should return integer type');
    echo "✓ Test 1 passed: get_the_ID() returns WordPress-style post ID\n";
  }

  /**
   * Test 2: get_the_ID() returns Backdrop-style node ID (nid)
   */
  public function test_get_the_ID_with_backdrop_node_object() {
    global $wp_post;

    // Create Backdrop-style node object
    $wp_post = (object) array(
      'nid' => 456,
      'title' => 'Test Node',
      'type' => 'article',
    );

    $result = get_the_ID();

    assert($result === 456, 'Should return Backdrop node ID (nid) as integer');
    assert(is_int($result), 'Should return integer type');
    echo "✓ Test 2 passed: get_the_ID() returns Backdrop-style node ID (nid)\n";
  }

  /**
   * Test 3: get_the_ID() handles null post gracefully
   */
  public function test_get_the_ID_with_null_post() {
    global $wp_post;
    $wp_post = null;

    $result = get_the_ID();

    assert($result === false, 'Should return false when no post exists');
    echo "✓ Test 3 passed: get_the_ID() handles null post gracefully\n";
  }

  /**
   * Test 4: get_the_ID() handles missing global post
   */
  public function test_get_the_ID_with_unset_post() {
    global $wp_post;
    unset($wp_post);

    $result = get_the_ID();

    assert($result === false, 'Should return false when global post is not set');
    echo "✓ Test 4 passed: get_the_ID() handles missing global post\n";
  }

  /**
   * Test 5: get_the_ID() accepts post object parameter
   */
  public function test_get_the_ID_with_post_parameter() {
    // Create a post object to pass as parameter
    $post = (object) array(
      'ID' => 789,
      'post_title' => 'Parameterized Post',
    );

    $result = get_the_ID($post);

    assert($result === 789, 'Should return ID from passed post object');
    echo "✓ Test 5 passed: get_the_ID() accepts post object parameter\n";
  }

  /**
   * Test 6: get_the_ID() handles numeric ID parameter
   */
  public function test_get_the_ID_with_numeric_parameter() {
    $result = get_the_ID(999);

    assert($result === 999, 'Should return numeric ID when passed as parameter');
    assert(is_int($result), 'Should return integer type');
    echo "✓ Test 6 passed: get_the_ID() handles numeric ID parameter\n";
  }

  /**
   * Test 7: get_the_ID() handles string numeric ID
   */
  public function test_get_the_ID_with_string_numeric() {
    $result = get_the_ID('555');

    assert($result === 555, 'Should convert string numeric to integer');
    assert(is_int($result), 'Should return integer type');
    echo "✓ Test 7 passed: get_the_ID() handles string numeric ID\n";
  }

  /**
   * Test 8: get_the_ID() handles post object without ID or nid
   */
  public function test_get_the_ID_with_invalid_post_object() {
    global $wp_post;

    // Create post object without ID or nid property
    $wp_post = (object) array(
      'title' => 'Invalid Post',
      'content' => 'No ID property',
    );

    $result = get_the_ID();

    assert($result === false, 'Should return false when post has no ID or nid');
    echo "✓ Test 8 passed: get_the_ID() handles post object without ID or nid\n";
  }

  /**
   * Test 9: the_ID() echoes post ID
   */
  public function test_the_ID_echoes_post_id() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 111,
      'post_title' => 'Test Post for Echo',
    );

    // Capture output
    ob_start();
    the_ID();
    $output = ob_get_clean();

    assert($output === '111', 'Should echo the post ID');
    echo "✓ Test 9 passed: the_ID() echoes post ID\n";
  }

  /**
   * Test 10: the_ID() with Backdrop node echoes nid
   */
  public function test_the_ID_echoes_backdrop_nid() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 222,
      'title' => 'Test Node for Echo',
    );

    // Capture output
    ob_start();
    the_ID();
    $output = ob_get_clean();

    assert($output === '222', 'Should echo the Backdrop node ID (nid)');
    echo "✓ Test 10 passed: the_ID() with Backdrop node echoes nid\n";
  }

  /**
   * Test 11: the_ID() handles missing post gracefully
   */
  public function test_the_ID_with_null_post() {
    global $wp_post;
    $wp_post = null;

    // Capture output
    ob_start();
    the_ID();
    $output = ob_get_clean();

    assert($output === '', 'Should not echo anything when post is null');
    echo "✓ Test 11 passed: the_ID() handles missing post gracefully\n";
  }

  /**
   * Test 12: the_ID() with post parameter
   */
  public function test_the_ID_with_post_parameter() {
    $post = (object) array(
      'ID' => 333,
      'post_title' => 'Parameter Test',
    );

    // Capture output
    ob_start();
    the_ID($post);
    $output = ob_get_clean();

    assert($output === '333', 'Should echo ID from passed post parameter');
    echo "✓ Test 12 passed: the_ID() with post parameter\n";
  }

  /**
   * Test 13: get_the_ID() handles zero ID
   */
  public function test_get_the_ID_with_zero_id() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 0,
      'post_title' => 'Zero ID Post',
    );

    $result = get_the_ID();

    assert($result === 0, 'Should return 0 as a valid ID');
    assert(is_int($result), 'Should return integer type');
    echo "✓ Test 13 passed: get_the_ID() handles zero ID\n";
  }

  /**
   * Test 14: get_the_ID() prioritizes ID over nid
   */
  public function test_get_the_ID_prioritizes_ID_over_nid() {
    global $wp_post;

    // Create object with both ID and nid (edge case)
    $wp_post = (object) array(
      'ID' => 100,
      'nid' => 200,
      'title' => 'Dual ID Post',
    );

    $result = get_the_ID();

    assert($result === 100, 'Should prioritize ID over nid when both exist');
    echo "✓ Test 14 passed: get_the_ID() prioritizes ID over nid\n";
  }

  /**
   * Test 15: the_ID() outputs integer (not string)
   */
  public function test_the_ID_outputs_as_string() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 444,
      'post_title' => 'Type Test',
    );

    // Capture output
    ob_start();
    the_ID();
    $output = ob_get_clean();

    // PHP echo will convert to string, so we check the string output
    assert($output === '444', 'Should echo ID as string representation');
    assert(is_string($output), 'Echo output should be string type');
    echo "✓ Test 15 passed: the_ID() outputs integer as string\n";
  }

  /**
   * Run all tests
   */
  public function runAllTests() {
    echo "\n=== Running Post ID Functions Tests ===\n\n";

    $this->setUp();
    $this->test_get_the_ID_with_wordpress_post_object();

    $this->setUp();
    $this->test_get_the_ID_with_backdrop_node_object();

    $this->setUp();
    $this->test_get_the_ID_with_null_post();

    $this->setUp();
    $this->test_get_the_ID_with_unset_post();

    $this->setUp();
    $this->test_get_the_ID_with_post_parameter();

    $this->setUp();
    $this->test_get_the_ID_with_numeric_parameter();

    $this->setUp();
    $this->test_get_the_ID_with_string_numeric();

    $this->setUp();
    $this->test_get_the_ID_with_invalid_post_object();

    $this->setUp();
    $this->test_the_ID_echoes_post_id();

    $this->setUp();
    $this->test_the_ID_echoes_backdrop_nid();

    $this->setUp();
    $this->test_the_ID_with_null_post();

    $this->setUp();
    $this->test_the_ID_with_post_parameter();

    $this->setUp();
    $this->test_get_the_ID_with_zero_id();

    $this->setUp();
    $this->test_get_the_ID_prioritizes_ID_over_nid();

    $this->setUp();
    $this->test_the_ID_outputs_as_string();

    echo "\n=== All 15 Tests Passed! ===\n\n";
  }
}

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli' && isset($argv[0]) && basename($argv[0]) === basename(__FILE__)) {
  $tester = new IDFunctionsTest();
  $tester->runAllTests();
}
