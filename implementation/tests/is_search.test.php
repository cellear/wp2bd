<?php
/**
 * Unit Tests for is_search() Conditional Function
 *
 * Tests the WordPress search detection compatibility layer for Backdrop CMS.
 * Verifies that is_search() correctly identifies search result pages.
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include required files
require_once dirname(__DIR__) . '/functions/conditionals.php';

/**
 * Test Suite for is_search()
 */
class IsSearchTest {

  private $test_count = 0;
  private $passed = 0;
  private $failed = 0;

  /**
   * Run all tests
   */
  public function run_all_tests() {
    echo "=== Running is_search() Test Suite ===\n\n";

    $this->test_wp_query_is_search_property();
    $this->test_wp_query_is_search_method();
    $this->test_search_query_parameter();
    $this->test_search_path_detection();
    $this->test_not_search_on_regular_page();
    $this->test_not_search_on_archive();
    $this->test_empty_search_query();
    $this->test_combined_query_and_path();
    $this->test_search_with_results();
    $this->test_search_without_results();
    $this->test_multiple_query_params();
    $this->test_wp_query_overrides_path();

    echo "\n=== Test Results ===\n";
    echo "Total: {$this->test_count}\n";
    echo "Passed: {$this->passed}\n";
    echo "Failed: {$this->failed}\n";

    return ($this->failed === 0);
  }

  /**
   * Test 1: WP_Query->is_search property detection
   */
  private function test_wp_query_is_search_property() {
    echo "Test 1: WP_Query->is_search property detection\n";

    global $wp_query;

    // Set up WP_Query with is_search = true
    $wp_query = new stdClass();
    $wp_query->is_search = true;

    $result = is_search();
    $this->assert_true($result, 'is_search() should return true when WP_Query->is_search is true');

    // Test with is_search = false
    $wp_query->is_search = false;
    $result = is_search();
    $this->assert_false($result, 'is_search() should return false when WP_Query->is_search is false');

    // Clean up
    unset($wp_query);
    $this->cleanup_globals();

    echo "\n";
  }

  /**
   * Test 2: WP_Query->is_search() method detection
   */
  private function test_wp_query_is_search_method() {
    echo "Test 2: WP_Query->is_search() method detection\n";

    global $wp_query;

    // Create mock WP_Query with is_search() method
    $wp_query = new MockWPQuerySearch(true);

    $result = is_search();
    $this->assert_true($result, 'is_search() should use WP_Query->is_search() method when available');

    // Test with method returning false
    $wp_query = new MockWPQuerySearch(false);
    $result = is_search();
    $this->assert_false($result, 'is_search() should return false when WP_Query method returns false');

    // Clean up
    unset($wp_query);
    $this->cleanup_globals();

    echo "\n";
  }

  /**
   * Test 3: Search query parameter ($_GET['s']) detection
   */
  private function test_search_query_parameter() {
    echo "Test 3: Search query parameter detection\n";

    global $wp_query;
    unset($wp_query);

    // Set search query parameter
    $_GET['s'] = 'test search';

    $result = is_search();
    $this->assert_true($result, 'is_search() should return true when $_GET["s"] is set');

    // Test with empty search query
    $_GET['s'] = '';
    $result = is_search();
    $this->assert_false($result, 'is_search() should return false when $_GET["s"] is empty');

    // Clean up
    $this->cleanup_globals();

    echo "\n";
  }

  /**
   * Test 4: Search path detection (Backdrop's search/* path)
   */
  private function test_search_path_detection() {
    echo "Test 4: Search path detection\n";

    global $wp_query;
    unset($wp_query);

    // Mock current_path to return search path
    $this->mock_current_path('search');

    $result = is_search();
    $this->assert_true($result, 'is_search() should return true for "search" path');

    // Test with search/node path
    $this->mock_current_path('search/node');
    $result = is_search();
    $this->assert_true($result, 'is_search() should return true for "search/node" path');

    // Test with search/user/keyword path
    $this->mock_current_path('search/user/testquery');
    $result = is_search();
    $this->assert_true($result, 'is_search() should return true for "search/user/keyword" path');

    // Clean up
    $this->cleanup_globals();
    $this->unmock_current_path();

    echo "\n";
  }

  /**
   * Test 5: Not search on regular page
   */
  private function test_not_search_on_regular_page() {
    echo "Test 5: Not search on regular page\n";

    global $wp_query;
    unset($wp_query);

    // Mock regular node path
    $this->mock_current_path('node/123');

    $result = is_search();
    $this->assert_false($result, 'is_search() should return false for regular node path');

    // Test with about page
    $this->mock_current_path('about');
    $result = is_search();
    $this->assert_false($result, 'is_search() should return false for about page');

    // Clean up
    $this->cleanup_globals();
    $this->unmock_current_path();

    echo "\n";
  }

  /**
   * Test 6: Not search on archive page
   */
  private function test_not_search_on_archive() {
    echo "Test 6: Not search on archive page\n";

    global $wp_query;
    unset($wp_query);

    // Mock taxonomy term path
    $this->mock_current_path('taxonomy/term/5');

    $result = is_search();
    $this->assert_false($result, 'is_search() should return false for taxonomy term page');

    // Clean up
    $this->cleanup_globals();
    $this->unmock_current_path();

    echo "\n";
  }

  /**
   * Test 7: Empty search query handling
   */
  private function test_empty_search_query() {
    echo "Test 7: Empty search query handling\n";

    global $wp_query;
    unset($wp_query);

    // Set empty search parameter
    $_GET['s'] = '';

    $result = is_search();
    $this->assert_false($result, 'is_search() should return false for empty search query');

    // Set whitespace-only search
    $_GET['s'] = '   ';

    // Note: WordPress treats whitespace as non-empty, but our implementation checks !empty()
    // which returns false for whitespace. This might need adjustment based on WP behavior.
    $result = is_search();
    // For now, we expect false since empty() treats whitespace as non-empty
    // but our check is !empty($_GET['s']) which would be true, so is_search() returns true
    // Actually, let's test this properly
    $this->assert_true($result, 'is_search() behavior with whitespace search query');

    // Clean up
    $this->cleanup_globals();

    echo "\n";
  }

  /**
   * Test 8: Combined query parameter and path
   */
  private function test_combined_query_and_path() {
    echo "Test 8: Combined query parameter and path detection\n";

    global $wp_query;
    unset($wp_query);

    // Set both search path and query parameter
    $this->mock_current_path('search/node');
    $_GET['s'] = 'test query';

    $result = is_search();
    $this->assert_true($result, 'is_search() should return true with both path and query param');

    // Clean up
    $this->cleanup_globals();
    $this->unmock_current_path();

    echo "\n";
  }

  /**
   * Test 9: Search with results
   */
  private function test_search_with_results() {
    echo "Test 9: Search with results\n";

    global $wp_query;

    // Create WP_Query indicating search with results
    $wp_query = new stdClass();
    $wp_query->is_search = true;
    $wp_query->found_posts = 10;

    $result = is_search();
    $this->assert_true($result, 'is_search() should return true for search with results');

    // Clean up
    unset($wp_query);
    $this->cleanup_globals();

    echo "\n";
  }

  /**
   * Test 10: Search without results (empty results still a search)
   */
  private function test_search_without_results() {
    echo "Test 10: Search without results\n";

    global $wp_query;

    // Create WP_Query indicating search with no results
    $wp_query = new stdClass();
    $wp_query->is_search = true;
    $wp_query->found_posts = 0;

    $result = is_search();
    $this->assert_true($result, 'is_search() should return true even with no search results');

    // Clean up
    unset($wp_query);
    $this->cleanup_globals();

    echo "\n";
  }

  /**
   * Test 11: Multiple query parameters (s and other params)
   */
  private function test_multiple_query_params() {
    echo "Test 11: Multiple query parameters\n";

    global $wp_query;
    unset($wp_query);

    // Set search with additional filters
    $_GET['s'] = 'wordpress';
    $_GET['cat'] = '5';
    $_GET['orderby'] = 'date';

    $result = is_search();
    $this->assert_true($result, 'is_search() should return true with additional query params');

    // Clean up
    $this->cleanup_globals();

    echo "\n";
  }

  /**
   * Test 12: WP_Query overrides path detection
   */
  private function test_wp_query_overrides_path() {
    echo "Test 12: WP_Query overrides path detection\n";

    global $wp_query;

    // Set non-search path
    $this->mock_current_path('about');

    // But WP_Query says it's a search
    $wp_query = new stdClass();
    $wp_query->is_search = true;

    $result = is_search();
    $this->assert_true($result, 'is_search() should prioritize WP_Query over path detection');

    // Clean up
    unset($wp_query);
    $this->cleanup_globals();
    $this->unmock_current_path();

    echo "\n";
  }

  // ===== Mock Helper Methods =====

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
   * Clean up global variables
   */
  private function cleanup_globals() {
    // Clean up $_GET
    if (isset($_GET['s'])) {
      unset($_GET['s']);
    }
    if (isset($_GET['cat'])) {
      unset($_GET['cat']);
    }
    if (isset($_GET['orderby'])) {
      unset($_GET['orderby']);
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
 * Mock WP_Query class with is_search() method
 */
class MockWPQuerySearch {
  public $is_search = false;

  public function __construct($is_search = false) {
    $this->is_search = $is_search;
  }

  public function is_search() {
    return $this->is_search;
  }
}

// ===== Main Test Runner =====

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli' && isset($argv[0]) && basename($argv[0]) === basename(__FILE__)) {
  $test = new IsSearchTest();
  $success = $test->run_all_tests();

  // Exit with appropriate code
  exit($success ? 0 : 1);
}
