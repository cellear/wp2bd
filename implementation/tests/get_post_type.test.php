<?php
/**
 * WP2BD get_post_type() Function Test Suite
 *
 * Comprehensive unit tests for the get_post_type() function:
 * - get_post_type() - Get the post type of a post
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include the conditionals functions
require_once dirname(__FILE__) . '/../functions/conditionals.php';
require_once dirname(__FILE__) . '/../classes/WP_Post.php';

/**
 * Test Suite for get_post_type() Function
 */
class GetPostTypeTest {

    private $tests_run = 0;
    private $tests_passed = 0;
    private $tests_failed = 0;

    /**
     * Run all tests
     */
    public function run() {
        echo "\n=== WP2BD get_post_type() Function Test Suite ===\n\n";

        // Core functionality tests
        $this->testGetPostTypeFromGlobal();
        $this->testGetPostTypeFromWPPost();
        $this->testGetPostTypeFromBackdropNode();
        $this->testGetPostTypeWithPostObject();
        $this->testGetPostTypeWithPageType();
        $this->testGetPostTypeWithCustomType();
        $this->testGetPostTypeNoGlobal();
        $this->testGetPostTypeNullParameter();
        $this->testGetPostTypeWithInvalidObject();
        $this->testGetPostTypeWithEmptyObject();

        // Edge case tests
        $this->testGetPostTypeWithStringObject();
        $this->testGetPostTypeWithArrayParameter();
        $this->testGetPostTypeWithBooleanParameter();
        $this->testGetPostTypeWithZeroId();
        $this->testGetPostTypeWithNegativeId();
        $this->testGetPostTypeWithEmptyPostType();
        $this->testGetPostTypeWithWhitespacePostType();
        $this->testGetPostTypeMixedPropertiesWPFirst();
        $this->testGetPostTypeMixedPropertiesBackdropFirst();

        // Summary
        echo "\n=== Test Summary ===\n";
        echo "Tests Run: {$this->tests_run}\n";
        echo "Passed: {$this->tests_passed}\n";
        echo "Failed: {$this->tests_failed}\n";

        return $this->tests_failed === 0;
    }

    /**
     * Assert helper
     */
    private function assert($condition, $message) {
        $this->tests_run++;
        if ($condition) {
            $this->tests_passed++;
            echo "✓ PASS: {$message}\n";
        } else {
            $this->tests_failed++;
            echo "✗ FAIL: {$message}\n";
        }
    }

    /**
     * Test 1: get_post_type() returns type from global $wp_post
     */
    public function testGetPostTypeFromGlobal() {
        echo "Test 1: get_post_type() from global \$wp_post\n";

        global $wp_post;

        // Create a post
        $wp_post = new WP_Post();
        $wp_post->ID = 123;
        $wp_post->post_type = 'post';

        $result = get_post_type();
        $this->assert($result === 'post', 'get_post_type() returns "post" from global');

        echo "\n";
    }

    /**
     * Test 2: get_post_type() returns type from WP_Post object
     */
    public function testGetPostTypeFromWPPost() {
        echo "Test 2: get_post_type() from WP_Post object\n";

        global $wp_post;
        $wp_post = null;

        $post = new WP_Post();
        $post->ID = 42;
        $post->post_type = 'page';

        $result = get_post_type($post);
        $this->assert($result === 'page', 'get_post_type($post) returns "page"');

        echo "\n";
    }

    /**
     * Test 3: get_post_type() returns type from Backdrop node object
     */
    public function testGetPostTypeFromBackdropNode() {
        echo "Test 3: get_post_type() from Backdrop node\n";

        global $wp_post;
        $wp_post = null;

        // Simulate a Backdrop node
        $node = new stdClass();
        $node->nid = 15;
        $node->type = 'article';

        $result = get_post_type($node);
        $this->assert($result === 'article', 'get_post_type($node) returns "article"');

        echo "\n";
    }

    /**
     * Test 4: get_post_type() with explicit post object parameter
     */
    public function testGetPostTypeWithPostObject() {
        echo "Test 4: get_post_type() with explicit post object\n";

        global $wp_post;
        $wp_post = null;

        $post1 = new WP_Post();
        $post1->ID = 1;
        $post1->post_type = 'post';

        $post2 = new WP_Post();
        $post2->ID = 2;
        $post2->post_type = 'page';

        $this->assert(get_post_type($post1) === 'post', 'get_post_type($post1) returns "post"');
        $this->assert(get_post_type($post2) === 'page', 'get_post_type($post2) returns "page"');

        echo "\n";
    }

    /**
     * Test 5: get_post_type() with page type
     */
    public function testGetPostTypeWithPageType() {
        echo "Test 5: get_post_type() with page type\n";

        global $wp_post;

        $wp_post = new WP_Post();
        $wp_post->ID = 99;
        $wp_post->post_type = 'page';

        $result = get_post_type();
        $this->assert($result === 'page', 'get_post_type() returns "page" for page type');

        echo "\n";
    }

    /**
     * Test 6: get_post_type() with custom post type
     */
    public function testGetPostTypeWithCustomType() {
        echo "Test 6: get_post_type() with custom post type\n";

        global $wp_post;

        $wp_post = new WP_Post();
        $wp_post->ID = 200;
        $wp_post->post_type = 'product';

        $result = get_post_type();
        $this->assert($result === 'product', 'get_post_type() returns "product" for custom type');

        // Test another custom type
        $wp_post->post_type = 'event';
        $result = get_post_type();
        $this->assert($result === 'event', 'get_post_type() returns "event" for custom type');

        echo "\n";
    }

    /**
     * Test 7: get_post_type() returns false when no global post
     */
    public function testGetPostTypeNoGlobal() {
        echo "Test 7: get_post_type() with no global post\n";

        global $wp_post;
        $wp_post = null;

        $result = get_post_type();
        $this->assert($result === false, 'get_post_type() returns false when no global post');

        echo "\n";
    }

    /**
     * Test 8: get_post_type() with null parameter
     */
    public function testGetPostTypeNullParameter() {
        echo "Test 8: get_post_type() with null parameter\n";

        global $wp_post;

        $wp_post = new WP_Post();
        $wp_post->ID = 123;
        $wp_post->post_type = 'post';

        $result = get_post_type(null);
        $this->assert($result === 'post', 'get_post_type(null) uses global post');

        echo "\n";
    }

    /**
     * Test 9: get_post_type() with invalid object
     */
    public function testGetPostTypeWithInvalidObject() {
        echo "Test 9: get_post_type() with invalid object\n";

        global $wp_post;
        $wp_post = null;

        $invalid = new stdClass();
        // Object with no type or post_type property

        $result = get_post_type($invalid);
        $this->assert($result === false, 'get_post_type() returns false for object without type');

        echo "\n";
    }

    /**
     * Test 10: get_post_type() with empty object
     */
    public function testGetPostTypeWithEmptyObject() {
        echo "Test 10: get_post_type() with empty object\n";

        global $wp_post;
        $wp_post = null;

        $empty = new stdClass();

        $result = get_post_type($empty);
        $this->assert($result === false, 'get_post_type() returns false for empty object');

        echo "\n";
    }

    /**
     * Test 11: get_post_type() with string parameter (non-object)
     */
    public function testGetPostTypeWithStringObject() {
        echo "Test 11: get_post_type() with string parameter\n";

        global $wp_post;
        $wp_post = null;

        $result = get_post_type('not-an-object');
        $this->assert($result === false, 'get_post_type("string") returns false');

        echo "\n";
    }

    /**
     * Test 12: get_post_type() with array parameter
     */
    public function testGetPostTypeWithArrayParameter() {
        echo "Test 12: get_post_type() with array parameter\n";

        global $wp_post;
        $wp_post = null;

        $result = get_post_type(array('post_type' => 'post'));
        $this->assert($result === false, 'get_post_type(array) returns false');

        echo "\n";
    }

    /**
     * Test 13: get_post_type() with boolean parameter
     */
    public function testGetPostTypeWithBooleanParameter() {
        echo "Test 13: get_post_type() with boolean parameter\n";

        global $wp_post;
        $wp_post = null;

        $result = get_post_type(true);
        $this->assert($result === false, 'get_post_type(true) returns false');

        $result = get_post_type(false);
        $this->assert($result === false, 'get_post_type(false) returns false');

        echo "\n";
    }

    /**
     * Test 14: get_post_type() with zero ID
     */
    public function testGetPostTypeWithZeroId() {
        echo "Test 14: get_post_type() with zero ID\n";

        global $wp_post;
        $wp_post = null;

        // When passing ID, function would try node_load which isn't available in tests
        // This tests the numeric ID path
        $result = get_post_type(0);
        $this->assert($result === false, 'get_post_type(0) returns false (no node_load available)');

        echo "\n";
    }

    /**
     * Test 15: get_post_type() with negative ID
     */
    public function testGetPostTypeWithNegativeId() {
        echo "Test 15: get_post_type() with negative ID\n";

        global $wp_post;
        $wp_post = null;

        $result = get_post_type(-1);
        $this->assert($result === false, 'get_post_type(-1) returns false');

        echo "\n";
    }

    /**
     * Test 16: get_post_type() with empty post_type property
     */
    public function testGetPostTypeWithEmptyPostType() {
        echo "Test 16: get_post_type() with empty post_type\n";

        global $wp_post;
        $wp_post = null;

        $post = new WP_Post();
        $post->ID = 123;
        $post->post_type = '';

        $result = get_post_type($post);
        $this->assert($result === false, 'get_post_type() returns false for empty post_type');

        echo "\n";
    }

    /**
     * Test 17: get_post_type() with whitespace-only post_type
     */
    public function testGetPostTypeWithWhitespacePostType() {
        echo "Test 17: get_post_type() with whitespace post_type\n";

        global $wp_post;
        $wp_post = null;

        $post = new WP_Post();
        $post->ID = 123;
        $post->post_type = '   ';

        $result = get_post_type($post);
        // Note: In the implementation, empty() will return true for '   ' after trim?
        // Actually empty() returns false for '   ', so it would return '   '
        // Let's test what actually happens
        $this->assert($result === '   ', 'get_post_type() returns whitespace as-is (not trimmed)');

        echo "\n";
    }

    /**
     * Test 18: get_post_type() prefers post_type over type
     */
    public function testGetPostTypeMixedPropertiesWPFirst() {
        echo "Test 18: get_post_type() with both post_type and type (WP first)\n";

        global $wp_post;
        $wp_post = null;

        $post = new stdClass();
        $post->post_type = 'post';
        $post->type = 'article';

        $result = get_post_type($post);
        $this->assert($result === 'post', 'get_post_type() prefers post_type over type');

        echo "\n";
    }

    /**
     * Test 19: get_post_type() falls back to type if post_type is empty
     */
    public function testGetPostTypeMixedPropertiesBackdropFirst() {
        echo "Test 19: get_post_type() falls back to type property\n";

        global $wp_post;
        $wp_post = null;

        $node = new stdClass();
        $node->nid = 42;
        $node->type = 'page';
        // No post_type property

        $result = get_post_type($node);
        $this->assert($result === 'page', 'get_post_type() uses type when post_type not present');

        echo "\n";
    }
}

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli') {
    $test = new GetPostTypeTest();
    $success = $test->run();
    exit($success ? 0 : 1);
}
