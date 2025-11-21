<?php
/**
 * Unit Tests for WP_Post Class
 *
 * Tests the WP_Post class, specifically the from_node() method that converts
 * Backdrop nodes to WordPress post objects.
 *
 * @package WP2BD
 * @subpackage Tests
 * @since 1.0.0
 */

// Define LANGUAGE_NONE constant if not already defined (Backdrop constant)
if (!defined('LANGUAGE_NONE')) {
    define('LANGUAGE_NONE', 'und');
}

// Include the WP_Post class
require_once __DIR__ . '/../classes/WP_Post.php';

/**
 * WP_Post Test Class
 *
 * PHPUnit-style test class for WP_Post functionality.
 * Can be adapted for Backdrop's SimpleTest or PHPUnit.
 */
class WP_Post_Test {

    /**
     * Test 1: Convert a basic Backdrop node to WP_Post
     *
     * Verifies that basic node properties are correctly mapped to post properties.
     */
    public function test_from_node_basic_conversion() {
        // Create a mock Backdrop node
        $node = $this->create_mock_node(array(
            'nid' => 123,
            'uid' => 5,
            'type' => 'article',
            'title' => 'Test Article Title',
            'status' => 1,
            'created' => strtotime('2025-01-15 10:30:00'),
            'changed' => strtotime('2025-01-20 15:45:00'),
        ));

        // Convert to WP_Post
        $post = WP_Post::from_node($node);

        // Assertions
        assert($post !== null, 'from_node should return a WP_Post object');
        assert($post->ID === 123, 'ID should match node nid');
        assert($post->post_author === 5, 'post_author should match node uid');
        assert($post->post_type === 'article', 'post_type should match node type');
        assert($post->post_title === 'Test Article Title', 'post_title should match node title');
        assert($post->post_status === 'publish', 'post_status should be "publish" when node status is 1');
        assert($post->post_date === '2025-01-15 10:30:00', 'post_date should match created timestamp');
        assert($post->post_modified === '2025-01-20 15:45:00', 'post_modified should match changed timestamp');
        assert($post->post_parent === 0, 'post_parent should be 0 for Backdrop nodes');
        assert($post->filter === 'raw', 'filter should be "raw"');

        echo "✓ Test 1 passed: Basic node conversion\n";
    }

    /**
     * Test 2: Convert node with body content and excerpt
     *
     * Verifies that body field data is correctly extracted.
     */
    public function test_from_node_with_body_content() {
        // Create node with body field
        $node = $this->create_mock_node(array(
            'nid' => 456,
            'uid' => 1,
            'type' => 'page',
            'title' => 'Test Page',
            'status' => 1,
            'created' => time(),
            'changed' => time(),
        ));

        // Add body field (Backdrop field structure)
        $node->body = array(
            LANGUAGE_NONE => array(
                0 => array(
                    'value' => '<p>This is the full body content with <strong>HTML</strong> tags.</p>',
                    'summary' => 'This is the excerpt or summary.',
                    'format' => 'filtered_html',
                ),
            ),
        );

        // Convert to WP_Post
        $post = WP_Post::from_node($node);

        // Assertions
        assert($post !== null, 'from_node should return a WP_Post object');
        assert($post->post_content === '<p>This is the full body content with <strong>HTML</strong> tags.</p>',
               'post_content should match body value');
        assert($post->post_excerpt === 'This is the excerpt or summary.',
               'post_excerpt should match body summary');

        echo "✓ Test 2 passed: Node with body content and excerpt\n";
    }

    /**
     * Test 3: Handle node with missing or empty body field
     *
     * Tests edge case where body field is not present or empty.
     */
    public function test_from_node_missing_body() {
        // Create node without body field
        $node = $this->create_mock_node(array(
            'nid' => 789,
            'uid' => 2,
            'type' => 'article',
            'title' => 'Article Without Body',
            'status' => 1,
            'created' => time(),
            'changed' => time(),
        ));
        // No body field added

        // Convert to WP_Post
        $post = WP_Post::from_node($node);

        // Assertions
        assert($post !== null, 'from_node should return a WP_Post object even without body');
        assert($post->post_content === '', 'post_content should be empty string when body is missing');
        assert($post->post_excerpt === '', 'post_excerpt should be empty string when body is missing');
        assert($post->post_title === 'Article Without Body', 'post_title should still be set');

        echo "✓ Test 3 passed: Node with missing body field\n";
    }

    /**
     * Test 4: Handle invalid or null input
     *
     * Tests error handling for invalid node objects.
     */
    public function test_from_node_invalid_input() {
        // Test with null
        $post1 = WP_Post::from_node(null);
        assert($post1 === null, 'from_node should return null for null input');

        // Test with non-object
        $post2 = WP_Post::from_node('not an object');
        assert($post2 === null, 'from_node should return null for non-object input');

        // Test with object missing nid
        $invalid_node = new stdClass();
        $invalid_node->title = 'Missing NID';
        $post3 = WP_Post::from_node($invalid_node);
        assert($post3 === null, 'from_node should return null for node missing nid');

        echo "✓ Test 4 passed: Invalid input handling\n";
    }

    /**
     * Test 5: Convert draft node (unpublished)
     *
     * Verifies that unpublished nodes get correct status.
     */
    public function test_from_node_draft_status() {
        // Create unpublished node (status = 0)
        $node = $this->create_mock_node(array(
            'nid' => 999,
            'uid' => 3,
            'type' => 'article',
            'title' => 'Draft Article',
            'status' => 0,  // Unpublished
            'created' => time(),
            'changed' => time(),
        ));

        // Convert to WP_Post
        $post = WP_Post::from_node($node);

        // Assertions
        assert($post !== null, 'from_node should return a WP_Post object');
        assert($post->post_status === 'draft', 'post_status should be "draft" when node status is 0');

        echo "✓ Test 5 passed: Draft node status conversion\n";
    }

    /**
     * Test 6: Node with path alias for post slug
     *
     * Tests that URL path aliases are correctly mapped to post_name.
     */
    public function test_from_node_with_path_alias() {
        // Create node with path alias
        $node = $this->create_mock_node(array(
            'nid' => 111,
            'uid' => 1,
            'type' => 'article',
            'title' => 'Article with Custom URL',
            'status' => 1,
            'created' => time(),
            'changed' => time(),
        ));

        // Add path alias
        $node->path = array(
            'alias' => 'blog/my-custom-url',
            'pid' => 5,
        );

        // Convert to WP_Post
        $post = WP_Post::from_node($node);

        // Assertions
        assert($post !== null, 'from_node should return a WP_Post object');
        assert($post->post_name === 'blog/my-custom-url',
               'post_name should match path alias');

        echo "✓ Test 6 passed: Node with path alias\n";
    }

    /**
     * Test 7: Node with comment count and comment status
     *
     * Verifies comment-related fields are correctly mapped.
     */
    public function test_from_node_comment_fields() {
        // Create node with comments enabled
        $node = $this->create_mock_node(array(
            'nid' => 222,
            'uid' => 1,
            'type' => 'article',
            'title' => 'Article with Comments',
            'status' => 1,
            'created' => time(),
            'changed' => time(),
        ));

        // Add comment data (Backdrop: 2 = open, 1 = closed, 0 = hidden)
        $node->comment = 2;  // Open
        $node->comment_count = 15;

        // Convert to WP_Post
        $post = WP_Post::from_node($node);

        // Assertions
        assert($post !== null, 'from_node should return a WP_Post object');
        assert($post->comment_count === 15, 'comment_count should match node comment_count');
        assert($post->comment_status === 'open', 'comment_status should be "open" when node comment is 2');

        // Test closed comments
        $node->comment = 1;  // Closed
        $post2 = WP_Post::from_node($node);
        assert($post2->comment_status === 'closed', 'comment_status should be "closed" when node comment is 1');

        echo "✓ Test 7 passed: Comment fields conversion\n";
    }

    /**
     * Test 8: Node with default/missing values
     *
     * Tests that sensible defaults are used for missing optional fields.
     */
    public function test_from_node_with_defaults() {
        // Create minimal node with only required fields
        $node = new stdClass();
        $node->nid = 333;
        $node->type = 'page';
        // Missing: uid, title, status, dates, body, etc.

        // Convert to WP_Post
        $post = WP_Post::from_node($node);

        // Assertions
        assert($post !== null, 'from_node should return a WP_Post object with minimal data');
        assert($post->ID === 333, 'ID should be set');
        assert($post->post_author === 0, 'post_author should default to 0 when uid is missing');
        assert($post->post_title === '', 'post_title should be empty string when missing');
        assert($post->post_type === 'page', 'post_type should be set');
        assert($post->comment_count === 0, 'comment_count should default to 0');
        assert($post->comment_status === 'closed', 'comment_status should default to closed');

        echo "✓ Test 8 passed: Node with default values\n";
    }

    /**
     * Helper: Create a mock Backdrop node object
     *
     * @param array $properties Node properties to set
     * @return stdClass Mock node object
     */
    private function create_mock_node($properties = array()) {
        $node = new stdClass();

        // Set default values
        $defaults = array(
            'nid' => 1,
            'uid' => 1,
            'type' => 'article',
            'title' => 'Test Node',
            'status' => 1,
            'created' => time(),
            'changed' => time(),
        );

        // Merge with provided properties
        $properties = array_merge($defaults, $properties);

        // Set properties on node
        foreach ($properties as $key => $value) {
            $node->$key = $value;
        }

        return $node;
    }

    /**
     * Run all tests
     */
    public function run_all_tests() {
        echo "\n=== Running WP_Post Unit Tests ===\n\n";

        try {
            $this->test_from_node_basic_conversion();
            $this->test_from_node_with_body_content();
            $this->test_from_node_missing_body();
            $this->test_from_node_invalid_input();
            $this->test_from_node_draft_status();
            $this->test_from_node_with_path_alias();
            $this->test_from_node_comment_fields();
            $this->test_from_node_with_defaults();

            echo "\n=== All tests passed! ===\n\n";
            return true;

        } catch (AssertionError $e) {
            echo "\n✗ Test failed: " . $e->getMessage() . "\n";
            echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
            return false;
        } catch (Exception $e) {
            echo "\n✗ Unexpected error: " . $e->getMessage() . "\n\n";
            return false;
        }
    }
}

// If running this file directly, execute the tests
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $test = new WP_Post_Test();
    $success = $test->run_all_tests();
    exit($success ? 0 : 1);
}
