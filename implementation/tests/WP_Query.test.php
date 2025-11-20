<?php
/**
 * Unit Tests for WP_Query and WP_Post Classes
 *
 * Tests the WordPress query compatibility layer for Backdrop CMS.
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include classes
require_once dirname(__DIR__) . '/classes/WP_Post.php';
require_once dirname(__DIR__) . '/classes/WP_Query.php';

/**
 * Test Suite for WP_Post and WP_Query
 */
class WP_Query_Test {

    private $test_count = 0;
    private $passed = 0;
    private $failed = 0;

    /**
     * Run all tests
     */
    public function run_all_tests() {
        echo "=== Running WP_Query Test Suite ===\n\n";

        $this->test_wp_post_from_node();
        $this->test_wp_query_constructor();
        $this->test_have_posts_with_content();
        $this->test_have_posts_empty();
        $this->test_the_post_sets_globals();
        $this->test_loop_iteration();
        $this->test_reset_postdata();
        $this->test_query_by_post_type();
        $this->test_pagination();

        echo "\n=== Test Results ===\n";
        echo "Total: {$this->test_count}\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";

        return ($this->failed === 0);
    }

    /**
     * Test 1: WP_Post::from_node() converts Backdrop node correctly
     */
    private function test_wp_post_from_node() {
        echo "Test 1: WP_Post::from_node() conversion\n";

        // Create mock Backdrop node
        $node = $this->create_mock_node(array(
            'nid' => 123,
            'uid' => 1,
            'type' => 'article',
            'title' => 'Test Post Title',
            'status' => 1,
            'created' => strtotime('2025-01-01 12:00:00'),
            'changed' => strtotime('2025-01-15 14:30:00'),
            'comment' => 2,
            'comment_count' => 5,
            'body' => array(
                'und' => array(
                    0 => array(
                        'value' => 'This is the post content.',
                        'summary' => 'This is the excerpt.'
                    )
                )
            ),
            'path' => array(
                'alias' => 'test-post-title'
            )
        ));

        $post = WP_Post::from_node($node);

        // Assertions
        $this->assert_equals($post->ID, 123, 'Post ID should match node nid');
        $this->assert_equals($post->post_author, 1, 'Post author should match node uid');
        $this->assert_equals($post->post_title, 'Test Post Title', 'Post title should match');
        $this->assert_equals($post->post_type, 'article', 'Post type should match');
        $this->assert_equals($post->post_status, 'publish', 'Published node should have publish status');
        $this->assert_equals($post->post_content, 'This is the post content.', 'Post content should match');
        $this->assert_equals($post->post_excerpt, 'This is the excerpt.', 'Post excerpt should match');
        $this->assert_equals($post->post_name, 'test-post-title', 'Post slug should match path alias');
        $this->assert_equals($post->comment_count, 5, 'Comment count should match');
        $this->assert_equals($post->comment_status, 'open', 'Comment status should be open');
        $this->assert_equals($post->filter, 'raw', 'Filter should be raw');

        echo "\n";
    }

    /**
     * Test 2: WP_Query constructor with arguments
     */
    private function test_wp_query_constructor() {
        echo "Test 2: WP_Query constructor with arguments\n";

        // Mock the node system
        $this->mock_backdrop_functions();

        $args = array(
            'post_type' => 'article',
            'posts_per_page' => 5,
            'orderby' => 'date',
            'order' => 'DESC'
        );

        $query = new WP_Query($args);

        $this->assert_equals($query->query_vars['post_type'], 'article', 'Post type should be set');
        $this->assert_equals($query->query_vars['posts_per_page'], 5, 'Posts per page should be set');
        $this->assert_equals($query->query_vars['orderby'], 'date', 'Order by should be set');
        $this->assert_true(is_array($query->posts), 'Posts should be an array');
        $this->assert_true($query->current_post === -1, 'Current post should be -1 before loop');

        echo "\n";
    }

    /**
     * Test 3: have_posts() returns true when posts exist
     */
    private function test_have_posts_with_content() {
        echo "Test 3: have_posts() with content\n";

        $query = new WP_Query();
        $query->posts = array(
            $this->create_mock_post(1, 'Post 1'),
            $this->create_mock_post(2, 'Post 2'),
            $this->create_mock_post(3, 'Post 3')
        );
        $query->post_count = 3;
        $query->current_post = -1;

        $this->assert_true($query->have_posts(), 'have_posts() should return true before loop');

        // Advance to first post
        $query->current_post = 0;
        $this->assert_true($query->have_posts(), 'have_posts() should return true at first post');

        // Advance to last post
        $query->current_post = 2;
        $this->assert_false($query->have_posts(), 'have_posts() should return false at last post');

        echo "\n";
    }

    /**
     * Test 4: have_posts() returns false when no posts
     */
    private function test_have_posts_empty() {
        echo "Test 4: have_posts() with empty query\n";

        $query = new WP_Query();
        $query->posts = array();
        $query->post_count = 0;
        $query->current_post = -1;

        $this->assert_false($query->have_posts(), 'have_posts() should return false with no posts');

        echo "\n";
    }

    /**
     * Test 5: the_post() sets up global variables
     */
    private function test_the_post_sets_globals() {
        echo "Test 5: the_post() sets globals\n";

        $query = new WP_Query();
        $query->posts = array(
            $this->create_mock_post(456, 'Global Test Post')
        );
        $query->post_count = 1;
        $query->current_post = -1;

        // Call the_post()
        $query->the_post();

        $this->assert_equals($query->current_post, 0, 'Current post index should be 0');
        $this->assert_not_null($query->post, 'Query post should be set');
        $this->assert_equals($query->post->ID, 456, 'Query post ID should be 456');

        // Check global using $GLOBALS array
        $this->assert_not_null($GLOBALS['post'], 'Global $post should be set');
        $this->assert_equals($GLOBALS['post']->ID, 456, 'Global post ID should be 456');
        $this->assert_equals($GLOBALS['id'], 456, 'Global $id should be 456');

        echo "\n";
    }

    /**
     * Test 6: Complete loop iteration
     */
    private function test_loop_iteration() {
        echo "Test 6: Complete loop iteration\n";

        $query = new WP_Query();
        $query->posts = array(
            $this->create_mock_post(1, 'First Post'),
            $this->create_mock_post(2, 'Second Post'),
            $this->create_mock_post(3, 'Third Post')
        );
        $query->post_count = 3;
        $query->current_post = -1;

        $titles = array();
        $iterations = 0;

        while ($query->have_posts()) {
            $query->the_post();
            // Use $GLOBALS to access the global post
            $titles[] = $GLOBALS['post']->post_title;
            $iterations++;

            // Safety check to prevent infinite loop
            if ($iterations > 10) {
                $this->assert_true(false, 'Loop should not exceed expected iterations');
                break;
            }
        }

        $this->assert_equals(count($titles), 3, 'Should iterate through 3 posts');
        $this->assert_equals($titles[0], 'First Post', 'First post title should match');
        $this->assert_equals($titles[1], 'Second Post', 'Second post title should match');
        $this->assert_equals($titles[2], 'Third Post', 'Third post title should match');
        $this->assert_false($query->have_posts(), 'have_posts() should be false after loop');

        echo "\n";
    }

    /**
     * Test 7: reset_postdata() restores original state
     */
    private function test_reset_postdata() {
        echo "Test 7: reset_postdata() restores state\n";

        $query = new WP_Query();
        $query->posts = array(
            $this->create_mock_post(100, 'First Post'),
            $this->create_mock_post(200, 'Second Post')
        );
        $query->post_count = 2;
        $query->current_post = -1;

        // Advance to second post
        $query->the_post();
        $query->the_post();

        $this->assert_equals($query->current_post, 1, 'Should be at second post');
        $this->assert_equals($GLOBALS['post']->ID, 200, 'Global post should be second post');

        // Reset
        $query->reset_postdata();

        $this->assert_equals($query->current_post, -1, 'Current post should reset to -1');
        $this->assert_equals($GLOBALS['post']->ID, 100, 'Global post should reset to first post');

        echo "\n";
    }

    /**
     * Test 8: Query by specific post type
     */
    private function test_query_by_post_type() {
        echo "Test 8: Query by post type\n";

        $args = array(
            'post_type' => 'page',
            'posts_per_page' => 10
        );

        $query = new WP_Query($args);

        $this->assert_equals($query->query_vars['post_type'], 'page', 'Query should filter by page type');

        echo "\n";
    }

    /**
     * Test 9: Pagination parameters
     */
    private function test_pagination() {
        echo "Test 9: Pagination parameters\n";

        $args = array(
            'posts_per_page' => 5,
            'paged' => 2
        );

        $query = new WP_Query($args);

        $this->assert_equals($query->query_vars['posts_per_page'], 5, 'Posts per page should be 5');
        $this->assert_equals($query->query_vars['paged'], 2, 'Page should be 2');

        echo "\n";
    }

    // ===== Helper Methods =====

    /**
     * Create a mock Backdrop node
     */
    private function create_mock_node($data) {
        $defaults = array(
            'nid' => 0,
            'uid' => 0,
            'type' => 'post',
            'title' => 'Untitled',
            'status' => 1,
            'created' => time(),
            'changed' => time(),
            'comment' => 0,
            'comment_count' => 0,
            'body' => array(
                'und' => array(
                    0 => array(
                        'value' => '',
                        'summary' => ''
                    )
                )
            )
        );

        return (object) array_merge($defaults, $data);
    }

    /**
     * Create a mock WP_Post object
     */
    private function create_mock_post($id, $title = 'Test Post') {
        $post = new WP_Post();
        $post->ID = $id;
        $post->post_title = $title;
        $post->post_author = 1;
        $post->post_date = date('Y-m-d H:i:s');
        $post->post_date_gmt = gmdate('Y-m-d H:i:s');
        $post->post_content = 'Test content for post ' . $id;
        $post->post_excerpt = 'Test excerpt';
        $post->post_status = 'publish';
        $post->post_name = strtolower(str_replace(' ', '-', $title));
        $post->post_modified = date('Y-m-d H:i:s');
        $post->post_modified_gmt = gmdate('Y-m-d H:i:s');
        $post->post_parent = 0;
        $post->post_type = 'post';
        $post->comment_count = 0;
        $post->comment_status = 'open';
        $post->ping_status = 'closed';
        $post->filter = 'raw';
        $post->guid = 'http://example.com/?p=' . $id;
        $post->menu_order = 0;
        $post->post_mime_type = '';

        return $post;
    }

    /**
     * Mock Backdrop functions for testing
     */
    private function mock_backdrop_functions() {
        // Define LANGUAGE_NONE if not defined
        if (!defined('LANGUAGE_NONE')) {
            define('LANGUAGE_NONE', 'und');
        }

        // Mock EntityFieldQuery if it doesn't exist
        if (!class_exists('EntityFieldQuery')) {
            eval('
                class EntityFieldQuery {
                    public function entityCondition($name, $value, $operator = NULL) { return $this; }
                    public function propertyCondition($name, $value, $operator = NULL) { return $this; }
                    public function fieldCondition($name, $column, $value, $operator = NULL) { return $this; }
                    public function propertyOrderBy($name, $direction = "ASC") { return $this; }
                    public function range($start = NULL, $length = NULL) { return $this; }
                    public function execute() { return array(); }
                }
            ');
        }
    }

    /**
     * Assert equals
     */
    private function assert_equals($actual, $expected, $message = '') {
        $this->test_count++;

        if ($actual === $expected) {
            $this->passed++;
            echo "  ✓ PASS: $message\n";
            return true;
        } else {
            $this->failed++;
            echo "  ✗ FAIL: $message\n";
            echo "    Expected: " . var_export($expected, true) . "\n";
            echo "    Actual: " . var_export($actual, true) . "\n";
            return false;
        }
    }

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

    /**
     * Assert not null
     */
    private function assert_not_null($value, $message = '') {
        $this->test_count++;

        if ($value !== null) {
            $this->passed++;
            echo "  ✓ PASS: $message\n";
            return true;
        } else {
            $this->failed++;
            echo "  ✗ FAIL: $message\n";
            echo "    Expected: not null\n";
            echo "    Actual: null\n";
            return false;
        }
    }
}

// ===== Main Test Runner =====

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli' && isset($argv[0]) && basename($argv[0]) === basename(__FILE__)) {
    $test = new WP_Query_Test();
    $success = $test->run_all_tests();

    // Exit with appropriate code
    exit($success ? 0 : 1);
}

// ===== Additional Integration Tests =====

/**
 * Integration test: Nested loops with reset
 *
 * This test demonstrates nested query loops with proper postdata reset
 */
function test_nested_loops_with_reset() {
    echo "\n=== Integration Test: Nested Loops ===\n\n";

    global $post;

    // Create main query
    $main_query = new WP_Query();
    $main_query->posts = array(
        create_simple_post(1, 'Outer Post 1'),
        create_simple_post(2, 'Outer Post 2')
    );
    $main_query->post_count = 2;
    $main_query->current_post = -1;

    $outer_titles = array();

    while ($main_query->have_posts()) {
        $main_query->the_post();
        $outer_titles[] = $post->post_title;
        $outer_id = $post->ID;

        echo "Outer loop: {$post->post_title} (ID: {$post->ID})\n";

        // Create nested custom query
        $custom_query = new WP_Query();
        $custom_query->posts = array(
            create_simple_post(10, 'Inner Post 1'),
            create_simple_post(11, 'Inner Post 2')
        );
        $custom_query->post_count = 2;
        $custom_query->current_post = -1;

        while ($custom_query->have_posts()) {
            $custom_query->the_post();
            echo "  Inner loop: {$post->post_title} (ID: {$post->ID})\n";
        }

        // Reset to main query
        $main_query->reset_postdata();

        // Verify we're back in main query
        if ($post->ID === $outer_id) {
            echo "  ✓ Successfully reset to outer post\n";
        } else {
            echo "  ✗ Failed to reset to outer post\n";
        }
    }

    echo "\nOuter loop processed: " . implode(', ', $outer_titles) . "\n";
}

/**
 * Helper for integration test
 */
function create_simple_post($id, $title) {
    $post = new WP_Post();
    $post->ID = $id;
    $post->post_title = $title;
    $post->post_content = "Content for $title";
    $post->post_author = 1;
    $post->post_type = 'post';
    $post->post_status = 'publish';

    return $post;
}

// Uncomment to run integration test
// test_nested_loops_with_reset();
