<?php
/**
 * Unit Tests for is_archive() Conditional Function
 *
 * Tests the WordPress archive detection compatibility layer for Backdrop CMS.
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include required files
require_once dirname(__DIR__) . '/functions/conditionals.php';
require_once dirname(__DIR__) . '/classes/WP_Post.php';
require_once dirname(__DIR__) . '/classes/WP_Query.php';

/**
 * Test Suite for is_archive()
 */
class IsArchiveTest {

    private $test_count = 0;
    private $passed = 0;
    private $failed = 0;

    /**
     * Run all tests
     */
    public function run_all_tests() {
        echo "=== Running is_archive() Test Suite ===\n\n";

        $this->test_wp_query_archive_property();
        $this->test_taxonomy_term_detection();
        $this->test_not_singular();
        $this->test_not_search();
        $this->test_not_404();
        $this->test_user_author_page();
        $this->test_archive_path_patterns();
        $this->test_menu_router_callbacks();
        $this->test_wp_query_method();

        echo "\n=== Test Results ===\n";
        echo "Total: {$this->test_count}\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";

        return ($this->failed === 0);
    }

    /**
     * Test 1: is_archive() returns true when WP_Query->is_archive is set
     */
    private function test_wp_query_archive_property() {
        echo "Test 1: WP_Query->is_archive property detection\n";

        global $wp_query;

        // Set up WP_Query with is_archive = true
        $wp_query = new stdClass();
        $wp_query->is_archive = true;

        $result = is_archive();
        $this->assert_true($result, 'is_archive() should return true when WP_Query->is_archive is true');

        // Test with is_archive = false
        $wp_query->is_archive = false;
        $result = is_archive();
        $this->assert_false($result, 'is_archive() should return false when WP_Query->is_archive is false');

        // Clean up
        unset($wp_query);

        echo "\n";
    }

    /**
     * Test 2: is_archive() detects taxonomy term pages
     */
    private function test_taxonomy_term_detection() {
        echo "Test 2: Taxonomy term page detection\n";

        global $wp_query;
        unset($wp_query);

        // Mock menu_get_object to return a taxonomy term
        $this->mock_menu_get_object_taxonomy();

        $result = is_archive();
        $this->assert_true($result, 'is_archive() should return true for taxonomy term pages');

        // Clean up mocks
        $this->unmock_menu_get_object();

        echo "\n";
    }

    /**
     * Test 3: is_archive() returns false for singular content
     */
    private function test_not_singular() {
        echo "Test 3: Not an archive when viewing singular content\n";

        global $wp_query, $wp_post;
        unset($wp_query);

        // Mock a single node/post context
        $this->mock_single_node_context();

        $result = is_archive();
        $this->assert_false($result, 'is_archive() should return false for single post/page');

        // Clean up
        $this->unmock_single_node_context();
        unset($wp_post);

        echo "\n";
    }

    /**
     * Test 4: is_archive() returns false for search results
     */
    private function test_not_search() {
        echo "Test 4: Not an archive when viewing search results\n";

        global $wp_query;

        // Set up search context
        $wp_query = new stdClass();
        $wp_query->is_search = true;
        $wp_query->is_archive = false;

        $result = is_archive();
        $this->assert_false($result, 'is_archive() should return false for search results');

        // Clean up
        unset($wp_query);

        echo "\n";
    }

    /**
     * Test 5: is_archive() returns false for 404 pages
     */
    private function test_not_404() {
        echo "Test 5: Not an archive when viewing 404 page\n";

        global $wp_query;
        unset($wp_query);

        // Mock 404 context
        $this->mock_backdrop_404();

        $result = is_archive();
        $this->assert_false($result, 'is_archive() should return false for 404 pages');

        // Clean up
        $this->unmock_backdrop_404();

        echo "\n";
    }

    /**
     * Test 6: is_archive() detects user/author pages
     */
    private function test_user_author_page() {
        echo "Test 6: User/author page detection\n";

        global $wp_query;
        unset($wp_query);

        // Mock Backdrop arg() to return user path
        $this->mock_backdrop_arg_user();

        $result = is_archive();
        $this->assert_true($result, 'is_archive() should return true for user/author pages');

        // Clean up
        $this->unmock_backdrop_arg();

        echo "\n";
    }

    /**
     * Test 7: is_archive() detects archive path patterns
     */
    private function test_archive_path_patterns() {
        echo "Test 7: Archive path pattern detection\n";

        global $wp_query;
        unset($wp_query);

        // Test taxonomy/term path
        $this->mock_current_path('taxonomy/term/5');
        $result = is_archive();
        $this->assert_true($result, 'is_archive() should return true for taxonomy/term/* paths');

        // Test blog path
        $this->mock_current_path('blog');
        $result = is_archive();
        $this->assert_true($result, 'is_archive() should return true for blog paths');

        // Test archive path
        $this->mock_current_path('archive/2025/01');
        $result = is_archive();
        $this->assert_true($result, 'is_archive() should return true for archive/* paths');

        // Test non-archive path
        $this->mock_current_path('node/123');
        $result = is_archive();
        $this->assert_false($result, 'is_archive() should return false for node/* paths');

        // Clean up
        $this->unmock_current_path();

        echo "\n";
    }

    /**
     * Test 8: is_archive() detects menu router callbacks
     */
    private function test_menu_router_callbacks() {
        echo "Test 8: Menu router callback detection\n";

        global $wp_query;
        unset($wp_query);

        // Test taxonomy_term_page callback
        $this->mock_menu_get_item('taxonomy_term_page');
        $result = is_archive();
        $this->assert_true($result, 'is_archive() should return true for taxonomy_term_page callback');

        // Test views_page callback
        $this->mock_menu_get_item('views_page');
        $result = is_archive();
        $this->assert_true($result, 'is_archive() should return true for views_page callback');

        // Test non-archive callback
        $this->mock_menu_get_item('node_page_view');
        $result = is_archive();
        $this->assert_false($result, 'is_archive() should return false for node view callbacks');

        // Clean up
        $this->unmock_menu_get_item();

        echo "\n";
    }

    /**
     * Test 9: is_archive() uses WP_Query method when available
     */
    private function test_wp_query_method() {
        echo "Test 9: WP_Query->is_archive() method detection\n";

        global $wp_query;

        // Create a mock WP_Query with is_archive() method
        $wp_query = new MockWPQueryArchive();

        $result = is_archive();
        $this->assert_true($result, 'is_archive() should use WP_Query->is_archive() method when available');

        // Clean up
        unset($wp_query);

        echo "\n";
    }

    // ===== Mock Helper Methods =====

    /**
     * Mock menu_get_object to return taxonomy term
     */
    private function mock_menu_get_object_taxonomy() {
        global $__mock_menu_get_object_type, $__mock_menu_get_object_data;

        $__mock_menu_get_object_type = 'taxonomy_term';
        $__mock_menu_get_object_data = new stdClass();
        $__mock_menu_get_object_data->tid = 5;
        $__mock_menu_get_object_data->name = "Test Category";
        $__mock_menu_get_object_data->vocabulary_machine_name = "tags";

        if (!function_exists('menu_get_object')) {
            eval('
                function menu_get_object($type = "node") {
                    global $__mock_menu_get_object_type, $__mock_menu_get_object_data;
                    if ($type === $__mock_menu_get_object_type) {
                        return $__mock_menu_get_object_data;
                    }
                    return NULL;
                }
            ');
        }
    }

    /**
     * Unmock menu_get_object
     */
    private function unmock_menu_get_object() {
        global $__mock_menu_get_object_type, $__mock_menu_get_object_data;
        unset($__mock_menu_get_object_type);
        unset($__mock_menu_get_object_data);
    }

    /**
     * Mock single node context
     */
    private function mock_single_node_context() {
        global $wp_post, $__mock_menu_get_object_type, $__mock_menu_get_object_data;

        $wp_post = new stdClass();
        $wp_post->ID = 123;
        $wp_post->post_type = 'post';
        $wp_post->post_title = 'Test Post';

        $__mock_menu_get_object_type = 'node';
        $__mock_menu_get_object_data = $wp_post;

        if (!function_exists('menu_get_object')) {
            eval('
                function menu_get_object($type = "node") {
                    global $__mock_menu_get_object_type, $__mock_menu_get_object_data;
                    if ($type === $__mock_menu_get_object_type) {
                        return $__mock_menu_get_object_data;
                    }
                    return NULL;
                }
            ');
        }
    }

    /**
     * Unmock single node context
     */
    private function unmock_single_node_context() {
        // Context cleared by unsetting globals
    }

    /**
     * Mock Backdrop 404 context
     */
    private function mock_backdrop_404() {
        global $__mock_http_status, $__mock_menu_get_object_type, $__mock_menu_get_object_data;

        // Clear any previous menu mock
        unset($__mock_menu_get_object_type);
        unset($__mock_menu_get_object_data);

        $__mock_http_status = "404 Not Found";

        if (!function_exists('backdrop_get_http_header')) {
            eval('
                function backdrop_get_http_header($name) {
                    global $__mock_http_status;
                    if ($name === "status" && isset($__mock_http_status)) {
                        return $__mock_http_status;
                    }
                    return NULL;
                }
            ');
        }
    }

    /**
     * Unmock Backdrop 404
     */
    private function unmock_backdrop_404() {
        global $__mock_http_status;
        unset($__mock_http_status);
    }

    /**
     * Mock Backdrop arg() for user path
     */
    private function mock_backdrop_arg_user() {
        global $__mock_arg_data, $__mock_menu_get_object_type, $__mock_menu_get_object_data;
        global $__mock_current_path, $__mock_menu_callback;

        // Clear any previous mocks that might interfere
        unset($__mock_menu_get_object_type);
        unset($__mock_menu_get_object_data);
        unset($__mock_current_path);
        unset($__mock_menu_callback);

        $__mock_arg_data = array("user", "42");

        if (!function_exists('arg')) {
            eval('
                function arg($index = NULL, $path = NULL) {
                    global $__mock_arg_data;
                    if ($index === NULL) {
                        return isset($__mock_arg_data) ? $__mock_arg_data : array();
                    }
                    return isset($__mock_arg_data[$index]) ? $__mock_arg_data[$index] : NULL;
                }
            ');
        }
    }

    /**
     * Unmock Backdrop arg()
     */
    private function unmock_backdrop_arg() {
        global $__mock_arg_data;
        unset($__mock_arg_data);
    }

    /**
     * Mock current_path
     */
    private function mock_current_path($path) {
        global $__mock_current_path, $__mock_menu_get_object_type, $__mock_menu_get_object_data;

        // Clear any previous menu mock
        unset($__mock_menu_get_object_type);
        unset($__mock_menu_get_object_data);

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
    private function mock_menu_get_item($callback) {
        global $__mock_menu_callback, $__mock_menu_get_object_type, $__mock_menu_get_object_data;

        // Clear any previous menu object mock
        unset($__mock_menu_get_object_type);
        unset($__mock_menu_get_object_data);

        $__mock_menu_callback = $callback;

        if (!function_exists('menu_get_item')) {
            eval('
                function menu_get_item() {
                    global $__mock_menu_callback;
                    return isset($__mock_menu_callback) ? array(
                        "page_callback" => $__mock_menu_callback
                    ) : array();
                }
            ');
        }
    }

    /**
     * Unmock menu_get_item
     */
    private function unmock_menu_get_item() {
        global $__mock_menu_callback;
        unset($__mock_menu_callback);
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
 * Mock WP_Query class with is_archive() method
 */
class MockWPQueryArchive {
    public $is_archive = true;

    public function is_archive() {
        return $this->is_archive;
    }
}

// ===== Main Test Runner =====

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli' && isset($argv[0]) && basename($argv[0]) === basename(__FILE__)) {
    $test = new IsArchiveTest();
    $success = $test->run_all_tests();

    // Exit with appropriate code
    exit($success ? 0 : 1);
}
