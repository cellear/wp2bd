<?php
/**
 * Unit Tests for is_404() Conditional Function
 *
 * Tests the WordPress 404 error detection compatibility layer for Backdrop CMS.
 * Verifies that is_404() correctly identifies 404 (Not Found) error pages.
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include required files
require_once dirname(__DIR__) . '/functions/conditionals.php';

/**
 * Test Suite for is_404()
 */
class Is404Test {

  private $test_count = 0;
  private $passed = 0;
  private $failed = 0;

  /**
   * Run all tests
   */
  public function run_all_tests() {
    echo "=== Running is_404() Test Suite ===\n\n";

    $this->test_wp_query_is_404_property();
    $this->test_wp_query_is_404_method();
    $this->test_backdrop_http_status_header();
    $this->test_not_404_on_valid_page();
    $this->test_not_404_on_front_page();
    $this->test_not_404_on_search();
    $this->test_not_404_on_archive();
    $this->test_404_with_no_content();
    $this->test_404_with_invalid_menu_item();
    $this->test_not_404_on_admin_paths();
    $this->test_404_with_access_denied();
    $this->test_wp_query_overrides_detection();

    echo "\n=== Test Results ===\n";
    echo "Total: {$this->test_count}\n";
    echo "Passed: {$this->passed}\n";
    echo "Failed: {$this->failed}\n";

    return ($this->failed === 0);
  }

  /**
   * Test 1: WP_Query->is_404 property detection
   */
  private function test_wp_query_is_404_property() {
    echo "Test 1: WP_Query->is_404 property detection\n";

    global $wp_query;

    // Set up WP_Query with is_404 = true
    $wp_query = new stdClass();
    $wp_query->is_404 = true;

    $result = is_404();
    $this->assert_true($result, 'is_404() should return true when WP_Query->is_404 is true');

    // Test with is_404 = false
    $wp_query->is_404 = false;
    $result = is_404();
    $this->assert_false($result, 'is_404() should return false when WP_Query->is_404 is false');

    // Clean up
    unset($wp_query);
    $this->cleanup_globals();

    echo "\n";
  }

  /**
   * Test 2: WP_Query->is_404() method detection
   */
  private function test_wp_query_is_404_method() {
    echo "Test 2: WP_Query->is_404() method detection\n";

    global $wp_query;

    // Create mock WP_Query with is_404() method
    $wp_query = new MockWPQuery404(true);

    $result = is_404();
    $this->assert_true($result, 'is_404() should use WP_Query->is_404() method when available');

    // Test with method returning false
    $wp_query = new MockWPQuery404(false);
    $result = is_404();
    $this->assert_false($result, 'is_404() should return false when WP_Query method returns false');

    // Clean up
    unset($wp_query);
    $this->cleanup_globals();

    echo "\n";
  }

  /**
   * Test 3: Backdrop HTTP status header detection
   */
  private function test_backdrop_http_status_header() {
    echo "Test 3: Backdrop HTTP status header detection\n";

    global $wp_query;
    unset($wp_query);

    // Mock backdrop_get_http_header to return 404
    $this->mock_backdrop_http_header('404 Not Found');

    $result = is_404();
    $this->assert_true($result, 'is_404() should return true when HTTP status is "404 Not Found"');

    // Test with just "404"
    $this->mock_backdrop_http_header('404');
    $result = is_404();
    $this->assert_true($result, 'is_404() should return true when HTTP status is "404"');

    // Test with 200 OK
    $this->mock_backdrop_http_header('200 OK');
    $result = is_404();
    $this->assert_false($result, 'is_404() should return false when HTTP status is "200 OK"');

    // Clean up
    $this->cleanup_globals();
    $this->unmock_backdrop_http_header();

    echo "\n";
  }

  /**
   * Test 4: Not 404 on valid page
   */
  private function test_not_404_on_valid_page() {
    echo "Test 4: Not 404 on valid page\n";

    global $wp_query;
    unset($wp_query);

    // Mock valid node
    $this->mock_menu_get_object('node', array(
      'nid' => 123,
      'type' => 'page',
      'title' => 'Valid Page',
    ));

    $result = is_404();
    $this->assert_false($result, 'is_404() should return false when viewing valid page');

    // Clean up
    $this->cleanup_globals();
    $this->unmock_menu_get_object();

    echo "\n";
  }

  /**
   * Test 5: Not 404 on front page
   */
  private function test_not_404_on_front_page() {
    echo "Test 5: Not 404 on front page\n";

    global $wp_query;
    unset($wp_query);

    // Mock front page (no node, but is_front_page() returns true)
    $this->mock_menu_get_object('node', null);
    $this->mock_config('system.core', 'site_frontpage', 'node');
    $_GET['q'] = '';

    $result = is_404();
    $this->assert_false($result, 'is_404() should return false on front page even with no node');

    // Clean up
    $this->cleanup_globals();
    $this->unmock_menu_get_object();
    $this->unmock_config();

    echo "\n";
  }

  /**
   * Test 6: Not 404 on search page
   */
  private function test_not_404_on_search() {
    echo "Test 6: Not 404 on search page\n";

    global $wp_query;
    unset($wp_query);

    // Mock search context
    $this->mock_menu_get_object('node', null);
    $_GET['s'] = 'search query';

    $result = is_404();
    $this->assert_false($result, 'is_404() should return false on search results page');

    // Clean up
    $this->cleanup_globals();
    $this->unmock_menu_get_object();

    echo "\n";
  }

  /**
   * Test 7: Not 404 on archive page
   */
  private function test_not_404_on_archive() {
    echo "Test 7: Not 404 on archive page\n";

    global $wp_query;
    unset($wp_query);

    // Mock taxonomy term (archive)
    $this->mock_menu_get_object('taxonomy_term', array(
      'tid' => 5,
      'name' => 'Test Category',
    ));

    $result = is_404();
    $this->assert_false($result, 'is_404() should return false on archive page');

    // Clean up
    $this->cleanup_globals();
    $this->unmock_menu_get_object();

    echo "\n";
  }

  /**
   * Test 8: 404 with no content and no special page
   */
  private function test_404_with_no_content() {
    echo "Test 8: 404 with no content and valid path\n";

    global $wp_query;
    unset($wp_query);

    // Mock no content scenario
    $this->mock_menu_get_object('node', null);
    $this->mock_menu_get_object('taxonomy_term', null);
    $this->mock_current_path('invalid-path');
    $this->mock_menu_get_item(null);  // No valid menu item

    $result = is_404();
    $this->assert_true($result, 'is_404() should return true with no content and invalid path');

    // Clean up
    $this->cleanup_globals();
    $this->unmock_menu_get_object();
    $this->unmock_current_path();
    $this->unmock_menu_get_item();

    echo "\n";
  }

  /**
   * Test 9: 404 with invalid menu item
   */
  private function test_404_with_invalid_menu_item() {
    echo "Test 9: 404 with invalid menu item\n";

    global $wp_query;
    unset($wp_query);

    // Mock menu system returning invalid item
    $this->mock_menu_get_object('node', null);
    $this->mock_current_path('some-path');
    $this->mock_menu_get_item(array());  // Empty array, no page_callback

    $result = is_404();
    $this->assert_true($result, 'is_404() should return true when menu item has no page_callback');

    // Clean up
    $this->cleanup_globals();
    $this->unmock_menu_get_object();
    $this->unmock_current_path();
    $this->unmock_menu_get_item();

    echo "\n";
  }

  /**
   * Test 10: Not 404 on admin paths
   */
  private function test_not_404_on_admin_paths() {
    echo "Test 10: Not 404 on admin/special paths\n";

    global $wp_query;
    unset($wp_query);

    // Mock admin path
    $this->mock_menu_get_object('node', null);
    $this->mock_arg(array('admin', 'config'));

    $result = is_404();
    $this->assert_false($result, 'is_404() should return false for admin paths');

    // Test user path
    $this->mock_arg(array('user', '1'));
    $result = is_404();
    $this->assert_false($result, 'is_404() should return false for user paths');

    // Clean up
    $this->cleanup_globals();
    $this->unmock_menu_get_object();
    $this->unmock_arg();

    echo "\n";
  }

  /**
   * Test 11: 404 with access denied
   */
  private function test_404_with_access_denied() {
    echo "Test 11: 404 with access denied\n";

    global $wp_query;
    unset($wp_query);

    // Mock menu item with access = false
    $this->mock_menu_get_object('node', null);
    $this->mock_current_path('restricted-page');
    $this->mock_menu_get_item(array(
      'page_callback' => 'node_page_view',
      'access' => false,
    ));

    $result = is_404();
    $this->assert_true($result, 'is_404() should return true when access is denied');

    // Clean up
    $this->cleanup_globals();
    $this->unmock_menu_get_object();
    $this->unmock_current_path();
    $this->unmock_menu_get_item();

    echo "\n";
  }

  /**
   * Test 12: WP_Query overrides all detection
   */
  private function test_wp_query_overrides_detection() {
    echo "Test 12: WP_Query overrides other detection methods\n";

    global $wp_query;

    // Set up scenario that would normally NOT be 404
    $this->mock_menu_get_object('node', array(
      'nid' => 123,
      'type' => 'page',
    ));

    // But WP_Query says it's 404
    $wp_query = new stdClass();
    $wp_query->is_404 = true;

    $result = is_404();
    $this->assert_true($result, 'is_404() should prioritize WP_Query over other checks');

    // Clean up
    unset($wp_query);
    $this->cleanup_globals();
    $this->unmock_menu_get_object();

    echo "\n";
  }

  // ===== Mock Helper Methods =====

  /**
   * Mock backdrop_get_http_header
   */
  private function mock_backdrop_http_header($status) {
    global $__mock_http_status;

    $__mock_http_status = $status;

    if (!function_exists('backdrop_get_http_header')) {
      eval('
        function backdrop_get_http_header($name) {
          global $__mock_http_status;
          if ($name === "status" && isset($__mock_http_status)) {
            return $__mock_http_status;
          }
          return null;
        }
      ');
    }
  }

  /**
   * Unmock backdrop_get_http_header
   */
  private function unmock_backdrop_http_header() {
    global $__mock_http_status;
    unset($__mock_http_status);
  }

  /**
   * Mock menu_get_object
   */
  private function mock_menu_get_object($type, $data) {
    global $__mock_menu_objects;

    if (!isset($__mock_menu_objects)) {
      $__mock_menu_objects = array();
    }

    $__mock_menu_objects[$type] = $data;

    if (!function_exists('menu_get_object')) {
      eval('
        function menu_get_object($type = "node") {
          global $__mock_menu_objects;
          if (isset($__mock_menu_objects[$type])) {
            $data = $__mock_menu_objects[$type];
            return $data === null ? null : (object) $data;
          }
          return null;
        }
      ');
    }
  }

  /**
   * Unmock menu_get_object
   */
  private function unmock_menu_get_object() {
    global $__mock_menu_objects;
    unset($__mock_menu_objects);
  }

  /**
   * Mock current_path
   */
  private function mock_current_path($path) {
    global $__mock_current_path;

    $__mock_current_path = $path;

    if (!function_exists('current_path')) {
      eval('
        function current_path() {
          global $__mock_current_path;
          return isset($__mock_current_path) ? $__mock_current_path : "";
        }
      ');
    }
  }

  /**
   * Unmock current_path
   */
  private function unmock_current_path() {
    global $__mock_current_path;
    unset($__mock_current_path);
  }

  /**
   * Mock menu_get_item
   */
  private function mock_menu_get_item($item) {
    global $__mock_menu_item;

    $__mock_menu_item = $item;

    if (!function_exists('menu_get_item')) {
      eval('
        function menu_get_item() {
          global $__mock_menu_item;
          return $__mock_menu_item;
        }
      ');
    }
  }

  /**
   * Unmock menu_get_item
   */
  private function unmock_menu_get_item() {
    global $__mock_menu_item;
    unset($__mock_menu_item);
  }

  /**
   * Mock arg()
   */
  private function mock_arg($args) {
    global $__mock_args;

    $__mock_args = $args;

    if (!function_exists('arg')) {
      eval('
        function arg($index = null) {
          global $__mock_args;
          if ($index === null) {
            return isset($__mock_args) ? $__mock_args : array();
          }
          return isset($__mock_args[$index]) ? $__mock_args[$index] : null;
        }
      ');
    }
  }

  /**
   * Unmock arg()
   */
  private function unmock_arg() {
    global $__mock_args;
    unset($__mock_args);
  }

  /**
   * Mock config_get
   */
  private function mock_config($config, $key, $value) {
    global $__mock_config;

    if (!isset($__mock_config)) {
      $__mock_config = array();
    }

    $config_key = $config . '::' . $key;
    $__mock_config[$config_key] = $value;

    if (!function_exists('config_get')) {
      eval('
        function config_get($config, $key) {
          global $__mock_config;
          $config_key = $config . "::" . $key;
          return isset($__mock_config[$config_key]) ? $__mock_config[$config_key] : null;
        }
      ');
    }
  }

  /**
   * Unmock config_get
   */
  private function unmock_config() {
    global $__mock_config;
    unset($__mock_config);
  }

  /**
   * Clean up global variables
   */
  private function cleanup_globals() {
    if (isset($_GET['q'])) {
      unset($_GET['q']);
    }
    if (isset($_GET['s'])) {
      unset($_GET['s']);
    }
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

/**
 * Mock WP_Query class with is_404() method
 */
class MockWPQuery404 {
  public $is_404 = false;

  public function __construct($is_404 = false) {
    $this->is_404 = $is_404;
  }

  public function is_404() {
    return $this->is_404;
  }
}

// ===== Main Test Runner =====

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli' && isset($argv[0]) && basename($argv[0]) === basename(__FILE__)) {
  $test = new Is404Test();
  $success = $test->run_all_tests();

  // Exit with appropriate code
  exit($success ? 0 : 1);
}
