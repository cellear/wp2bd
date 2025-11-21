<?php
/**
 * Unit Tests for is_sticky() Conditional Function
 *
 * Tests the WordPress sticky post detection compatibility layer for Backdrop CMS.
 * Verifies that is_sticky() correctly identifies sticky/pinned posts.
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include required files
require_once dirname(__DIR__) . '/functions/conditionals.php';

/**
 * Test Suite for is_sticky()
 */
class IsStickyTest {

  private $test_count = 0;
  private $passed = 0;
  private $failed = 0;

  /**
   * Run all tests
   */
  public function run_all_tests() {
    echo "=== Running is_sticky() Test Suite ===\n\n";

    $this->test_sticky_with_backdrop_sticky_flag();
    $this->test_sticky_with_backdrop_promote_flag();
    $this->test_sticky_with_option_array();
    $this->test_sticky_with_post_meta();
    $this->test_not_sticky_regular_post();
    $this->test_sticky_with_post_id_parameter();
    $this->test_sticky_with_post_object_parameter();
    $this->test_sticky_current_post_global();
    $this->test_sticky_with_invalid_id();
    $this->test_sticky_with_zero_id();
    $this->test_sticky_wordpress_style_post();
    $this->test_sticky_both_flags_set();

    echo "\n=== Test Results ===\n";
    echo "Total: {$this->test_count}\n";
    echo "Passed: {$this->passed}\n";
    echo "Failed: {$this->failed}\n";

    return ($this->failed === 0);
  }

  /**
   * Test 1: Sticky with Backdrop sticky flag
   */
  private function test_sticky_with_backdrop_sticky_flag() {
    echo "Test 1: Backdrop sticky flag detection\n";

    global $wp_post;

    // Create post with sticky flag set
    $wp_post = new stdClass();
    $wp_post->nid = 100;
    $wp_post->type = 'post';
    $wp_post->sticky = 1;

    $result = is_sticky();
    $this->assert_true($result, 'is_sticky() should return true when node->sticky = 1');

    // Test with sticky = 0
    $wp_post->sticky = 0;
    $result = is_sticky();
    $this->assert_false($result, 'is_sticky() should return false when node->sticky = 0');

    // Clean up
    unset($wp_post);

    echo "\n";
  }

  /**
   * Test 2: Sticky with Backdrop promote flag
   */
  private function test_sticky_with_backdrop_promote_flag() {
    echo "Test 2: Backdrop promote flag detection\n";

    global $wp_post;

    // Create post with promote flag set
    $wp_post = new stdClass();
    $wp_post->nid = 101;
    $wp_post->type = 'post';
    $wp_post->promote = 1;
    $wp_post->sticky = 0;  // sticky is not set

    $result = is_sticky();
    $this->assert_true($result, 'is_sticky() should return true when node->promote = 1');

    // Test with promote = 0
    $wp_post->promote = 0;
    $result = is_sticky();
    $this->assert_false($result, 'is_sticky() should return false when node->promote = 0');

    // Clean up
    unset($wp_post);

    echo "\n";
  }

  /**
   * Test 3: Sticky with WordPress option array
   */
  private function test_sticky_with_option_array() {
    echo "Test 3: WordPress sticky_posts option detection\n";

    global $wp_post;

    // Create post
    $wp_post = new stdClass();
    $wp_post->nid = 102;
    $wp_post->type = 'post';

    // Mock get_option to return sticky posts array
    $this->mock_get_option(array(100, 102, 105));

    $result = is_sticky();
    $this->assert_true($result, 'is_sticky() should return true when ID is in sticky_posts option');

    // Test with ID not in sticky array
    $wp_post->nid = 200;
    $result = is_sticky();
    $this->assert_false($result, 'is_sticky() should return false when ID not in sticky_posts option');

    // Clean up
    unset($wp_post);
    $this->unmock_get_option();

    echo "\n";
  }

  /**
   * Test 4: Sticky with post meta
   */
  private function test_sticky_with_post_meta() {
    echo "Test 4: Post meta sticky flag detection\n";

    global $wp_post;

    // Create post
    $wp_post = new stdClass();
    $wp_post->nid = 103;
    $wp_post->type = 'post';

    // Mock get_post_meta to return sticky flag
    $this->mock_get_post_meta(103, '_sticky', '1');

    $result = is_sticky();
    $this->assert_true($result, 'is_sticky() should return true when _sticky post meta is "1"');

    // Test with meta = false
    $this->mock_get_post_meta(103, '_sticky', '0');
    $result = is_sticky();
    $this->assert_false($result, 'is_sticky() should return false when _sticky post meta is "0"');

    // Clean up
    unset($wp_post);
    $this->unmock_get_post_meta();

    echo "\n";
  }

  /**
   * Test 5: Not sticky - regular post
   */
  private function test_not_sticky_regular_post() {
    echo "Test 5: Regular non-sticky post\n";

    global $wp_post;

    // Create regular post with no sticky flags
    $wp_post = new stdClass();
    $wp_post->nid = 104;
    $wp_post->type = 'post';
    $wp_post->sticky = 0;
    $wp_post->promote = 0;

    $result = is_sticky();
    $this->assert_false($result, 'is_sticky() should return false for regular post');

    // Clean up
    unset($wp_post);

    echo "\n";
  }

  /**
   * Test 6: Sticky with post ID parameter
   */
  private function test_sticky_with_post_id_parameter() {
    echo "Test 6: Checking sticky with post ID parameter\n";

    global $wp_post;
    unset($wp_post);

    // Mock node_load to return sticky post
    $this->mock_node_load(105, array(
      'nid' => 105,
      'type' => 'post',
      'sticky' => 1,
    ));

    $result = is_sticky(105);
    $this->assert_true($result, 'is_sticky(ID) should return true for sticky post ID');

    // Test with non-sticky post
    $this->mock_node_load(106, array(
      'nid' => 106,
      'type' => 'post',
      'sticky' => 0,
      'promote' => 0,
    ));

    $result = is_sticky(106);
    $this->assert_false($result, 'is_sticky(ID) should return false for non-sticky post ID');

    // Clean up
    $this->unmock_node_load();

    echo "\n";
  }

  /**
   * Test 7: Sticky with post object parameter
   */
  private function test_sticky_with_post_object_parameter() {
    echo "Test 7: Checking sticky with post object parameter\n";

    global $wp_post;
    unset($wp_post);

    // Create sticky post object
    $post = new stdClass();
    $post->nid = 107;
    $post->type = 'post';
    $post->sticky = 1;

    $result = is_sticky($post);
    $this->assert_true($result, 'is_sticky($post_object) should return true for sticky post object');

    // Test with non-sticky object
    $post->sticky = 0;
    $result = is_sticky($post);
    $this->assert_false($result, 'is_sticky($post_object) should return false for non-sticky object');

    echo "\n";
  }

  /**
   * Test 8: Sticky with current post global
   */
  private function test_sticky_current_post_global() {
    echo "Test 8: Using global post when no parameter provided\n";

    global $wp_post;

    // Set global post as sticky
    $wp_post = new stdClass();
    $wp_post->nid = 108;
    $wp_post->type = 'post';
    $wp_post->sticky = 1;

    $result = is_sticky();
    $this->assert_true($result, 'is_sticky() should use global $wp_post when no param');

    // Set global post as non-sticky
    $wp_post->sticky = 0;
    $result = is_sticky();
    $this->assert_false($result, 'is_sticky() should return false for non-sticky global post');

    // Clean up
    unset($wp_post);

    echo "\n";
  }

  /**
   * Test 9: Sticky with invalid/missing post ID
   */
  private function test_sticky_with_invalid_id() {
    echo "Test 9: Handling invalid/missing post ID\n";

    global $wp_post;
    unset($wp_post);

    // Mock node_load to return null (post not found)
    $this->mock_node_load(999, null);

    $result = is_sticky(999);
    $this->assert_false($result, 'is_sticky() should return false when post not found');

    // Test with no post at all
    $result = is_sticky(null);
    $this->assert_false($result, 'is_sticky(null) should return false when no post');

    // Clean up
    $this->unmock_node_load();

    echo "\n";
  }

  /**
   * Test 10: Sticky with zero or negative ID
   */
  private function test_sticky_with_zero_id() {
    echo "Test 10: Handling zero or invalid ID\n";

    global $wp_post;

    // Create post with ID = 0
    $wp_post = new stdClass();
    $wp_post->nid = 0;
    $wp_post->sticky = 1;

    $result = is_sticky();
    $this->assert_false($result, 'is_sticky() should return false for ID = 0');

    // Clean up
    unset($wp_post);

    echo "\n";
  }

  /**
   * Test 11: Sticky with WordPress-style post object
   */
  private function test_sticky_wordpress_style_post() {
    echo "Test 11: WordPress-style post object\n";

    global $wp_post;

    // Create WordPress-style post
    $wp_post = new stdClass();
    $wp_post->ID = 109;
    $wp_post->post_type = 'post';

    // Mock get_option for WordPress sticky posts
    $this->mock_get_option(array(109, 110));

    $result = is_sticky();
    $this->assert_true($result, 'is_sticky() should work with WordPress-style post object');

    // Clean up
    unset($wp_post);
    $this->unmock_get_option();

    echo "\n";
  }

  /**
   * Test 12: Both sticky and promote flags set
   */
  private function test_sticky_both_flags_set() {
    echo "Test 12: Both sticky and promote flags set\n";

    global $wp_post;

    // Create post with both flags
    $wp_post = new stdClass();
    $wp_post->nid = 110;
    $wp_post->type = 'post';
    $wp_post->sticky = 1;
    $wp_post->promote = 1;

    $result = is_sticky();
    $this->assert_true($result, 'is_sticky() should return true when both flags are set');

    // Clean up
    unset($wp_post);

    echo "\n";
  }

  // ===== Mock Helper Methods =====

  /**
   * Mock get_option
   */
  private function mock_get_option($sticky_posts) {
    global $__mock_sticky_posts;

    $__mock_sticky_posts = $sticky_posts;

    if (!function_exists('get_option')) {
      eval('
        function get_option($option, $default = false) {
          global $__mock_sticky_posts;
          if ($option === "sticky_posts" && isset($__mock_sticky_posts)) {
            return $__mock_sticky_posts;
          }
          return $default;
        }
      ');
    }
  }

  /**
   * Unmock get_option
   */
  private function unmock_get_option() {
    global $__mock_sticky_posts;
    unset($__mock_sticky_posts);
  }

  /**
   * Mock get_post_meta
   */
  private function mock_get_post_meta($post_id, $key, $value) {
    global $__mock_post_meta;

    $__mock_post_meta = array(
      'post_id' => $post_id,
      'key' => $key,
      'value' => $value,
    );

    if (!function_exists('get_post_meta')) {
      eval('
        function get_post_meta($post_id, $key = "", $single = false) {
          global $__mock_post_meta;
          if (isset($__mock_post_meta) &&
              $__mock_post_meta["post_id"] == $post_id &&
              $__mock_post_meta["key"] === $key) {
            return $__mock_post_meta["value"];
          }
          return "";
        }
      ');
    }
  }

  /**
   * Unmock get_post_meta
   */
  private function unmock_get_post_meta() {
    global $__mock_post_meta;
    unset($__mock_post_meta);
  }

  /**
   * Mock node_load
   */
  private function mock_node_load($nid, $node_data) {
    global $__mock_node_load_data;

    $__mock_node_load_data[$nid] = $node_data;

    if (!function_exists('node_load')) {
      eval('
        function node_load($nid) {
          global $__mock_node_load_data;
          if (isset($__mock_node_load_data[$nid])) {
            $data = $__mock_node_load_data[$nid];
            return $data === null ? false : (object) $data;
          }
          return false;
        }
      ');
    }
  }

  /**
   * Unmock node_load
   */
  private function unmock_node_load() {
    global $__mock_node_load_data;
    unset($__mock_node_load_data);
  }

  // ===== Assertion Methods =====

  /**
   * Assert true
   */
  private function assert_true($condition, $message = '') {
    $this->test_count++;

    if ($condition === true) {
      $this->passed++;
      echo "  ✓ PASS: $message\n";
      return true;
    } else {
      $this->failed++;
      echo "  ✗ FAIL: $message\n";
      echo "    Expected: true\n";
      echo "    Actual: " . var_export($condition, true) . "\n";
      return false;
    }
  }

  /**
   * Assert false
   */
  private function assert_false($condition, $message = '') {
    $this->test_count++;

    if ($condition === false) {
      $this->passed++;
      echo "  ✓ PASS: $message\n";
      return true;
    } else {
      $this->failed++;
      echo "  ✗ FAIL: $message\n";
      echo "    Expected: false\n";
      echo "    Actual: " . var_export($condition, true) . "\n";
      return false;
    }
  }
}

// ===== Main Test Runner =====

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli' && isset($argv[0]) && basename($argv[0]) === basename(__FILE__)) {
  $test = new IsStickyTest();
  $success = $test->run_all_tests();

  // Exit with appropriate code
  exit($success ? 0 : 1);
}
