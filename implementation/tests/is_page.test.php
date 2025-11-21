<?php
/**
 * WP2BD is_page() Function Test Suite
 *
 * Comprehensive unit tests for the is_page() conditional function:
 * - is_page() - Check if viewing a page (not post)
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include the conditionals functions
require_once dirname(__FILE__) . '/../functions/conditionals.php';
require_once dirname(__FILE__) . '/../classes/WP_Query.php';
require_once dirname(__FILE__) . '/../classes/WP_Post.php';

/**
 * Test Suite for is_page() Function
 */
class IsPageTest {

    private $tests_run = 0;
    private $tests_passed = 0;
    private $tests_failed = 0;

    /**
     * Run all tests
     */
    public function run() {
        echo "\n=== WP2BD is_page() Function Test Suite ===\n\n";

        // Core functionality tests
        $this->testIsPageWithPageQuery();
        $this->testIsPageWithPostQuery();
        $this->testIsPageNoQuery();
        $this->testIsPageWithPageId();
        $this->testIsPageWithPageSlug();
        $this->testIsPageWithPageTitle();
        $this->testIsPageWithArray();
        $this->testIsPageWithNonMatchingId();
        $this->testIsPageWithNonMatchingSlug();
        $this->testIsPageEmptyQuery();

        // Edge case tests
        $this->testIsPageWithStringId();
        $this->testIsPageWithNumericString();
        $this->testIsPageMixedArray();
        $this->testIsPageCaseSensitiveTitle();
        $this->testIsPageWithZeroId();
        $this->testIsPageWithNegativeId();
        $this->testIsPageWithEmptyString();

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
     * Test 1: is_page() returns true when viewing a page
     */
    public function testIsPageWithPageQuery() {
        echo "Test 1: is_page() with page query\n";

        global $wp_query;

        // Create a page post
        $page_post = new WP_Post();
        $page_post->ID = 42;
        $page_post->post_title = 'About Us';
        $page_post->post_name = 'about';
        $page_post->post_type = 'page';
        $page_post->post_status = 'publish';

        // Create query with page_id
        $wp_query = new WP_Query(array('page_id' => 42));
        // Manually set up the query result for testing
        $wp_query->posts = array($page_post);
        $wp_query->post_count = 1;
        $wp_query->is_page = true;
        $wp_query->queried_object = $page_post;

        $this->assert(is_page() === true, 'is_page() returns true for page query');
        $this->assert(is_page('') === true, 'is_page("") returns true for page query');

        echo "\n";
    }

    /**
     * Test 2: is_page() returns false when viewing a post
     */
    public function testIsPageWithPostQuery() {
        echo "Test 2: is_page() with post query\n";

        global $wp_query;

        // Create a post (not page)
        $post = new WP_Post();
        $post->ID = 123;
        $post->post_title = 'Blog Post';
        $post->post_name = 'blog-post';
        $post->post_type = 'post';
        $post->post_status = 'publish';

        // Create query with p (post ID)
        $wp_query = new WP_Query(array('p' => 123));
        $wp_query->posts = array($post);
        $wp_query->post_count = 1;
        $wp_query->is_single = true;
        $wp_query->is_page = false;
        $wp_query->queried_object = $post;

        $this->assert(is_page() === false, 'is_page() returns false for post query');

        echo "\n";
    }

    /**
     * Test 3: is_page() returns false with no query
     */
    public function testIsPageNoQuery() {
        echo "Test 3: is_page() with no query\n";

        global $wp_query;
        $wp_query = null;

        $this->assert(is_page() === false, 'is_page() returns false when $wp_query is null');

        echo "\n";
    }

    /**
     * Test 4: is_page() with specific page ID
     */
    public function testIsPageWithPageId() {
        echo "Test 4: is_page() with specific page ID\n";

        global $wp_query;

        $page_post = new WP_Post();
        $page_post->ID = 42;
        $page_post->post_title = 'About Us';
        $page_post->post_name = 'about';
        $page_post->post_type = 'page';

        $wp_query = new WP_Query();
        $wp_query->posts = array($page_post);
        $wp_query->post_count = 1;
        $wp_query->is_page = true;
        $wp_query->queried_object = $page_post;

        $this->assert(is_page(42) === true, 'is_page(42) returns true for page with ID 42');
        $this->assert(is_page(99) === false, 'is_page(99) returns false for page with ID 42');

        echo "\n";
    }

    /**
     * Test 5: is_page() with page slug
     */
    public function testIsPageWithPageSlug() {
        echo "Test 5: is_page() with page slug\n";

        global $wp_query;

        $page_post = new WP_Post();
        $page_post->ID = 42;
        $page_post->post_title = 'About Us';
        $page_post->post_name = 'about';
        $page_post->post_type = 'page';

        $wp_query = new WP_Query();
        $wp_query->posts = array($page_post);
        $wp_query->post_count = 1;
        $wp_query->is_page = true;
        $wp_query->queried_object = $page_post;

        $this->assert(is_page('about') === true, 'is_page("about") returns true for page with slug "about"');
        $this->assert(is_page('contact') === false, 'is_page("contact") returns false for page with slug "about"');

        echo "\n";
    }

    /**
     * Test 6: is_page() with page title
     */
    public function testIsPageWithPageTitle() {
        echo "Test 6: is_page() with page title\n";

        global $wp_query;

        $page_post = new WP_Post();
        $page_post->ID = 42;
        $page_post->post_title = 'About Us';
        $page_post->post_name = 'about';
        $page_post->post_type = 'page';

        $wp_query = new WP_Query();
        $wp_query->posts = array($page_post);
        $wp_query->post_count = 1;
        $wp_query->is_page = true;
        $wp_query->queried_object = $page_post;

        $this->assert(is_page('About Us') === true, 'is_page("About Us") returns true for page with title "About Us"');
        $this->assert(is_page('Contact Us') === false, 'is_page("Contact Us") returns false for different title');

        echo "\n";
    }

    /**
     * Test 7: is_page() with array of page identifiers
     */
    public function testIsPageWithArray() {
        echo "Test 7: is_page() with array of identifiers\n";

        global $wp_query;

        $page_post = new WP_Post();
        $page_post->ID = 42;
        $page_post->post_title = 'About Us';
        $page_post->post_name = 'about';
        $page_post->post_type = 'page';

        $wp_query = new WP_Query();
        $wp_query->posts = array($page_post);
        $wp_query->post_count = 1;
        $wp_query->is_page = true;
        $wp_query->queried_object = $page_post;

        $this->assert(is_page(array(42, 'contact', 'team')) === true, 'is_page() matches ID in array');
        $this->assert(is_page(array('about', 'contact', 'team')) === true, 'is_page() matches slug in array');
        $this->assert(is_page(array('About Us', 'Contact', 'Team')) === true, 'is_page() matches title in array');
        $this->assert(is_page(array(99, 'contact', 'team')) === false, 'is_page() returns false when no match in array');

        echo "\n";
    }

    /**
     * Test 8: is_page() with non-matching ID
     */
    public function testIsPageWithNonMatchingId() {
        echo "Test 8: is_page() with non-matching ID\n";

        global $wp_query;

        $page_post = new WP_Post();
        $page_post->ID = 42;
        $page_post->post_title = 'About Us';
        $page_post->post_name = 'about';
        $page_post->post_type = 'page';

        $wp_query = new WP_Query();
        $wp_query->posts = array($page_post);
        $wp_query->post_count = 1;
        $wp_query->is_page = true;
        $wp_query->queried_object = $page_post;

        $this->assert(is_page(999) === false, 'is_page(999) returns false for page with ID 42');

        echo "\n";
    }

    /**
     * Test 9: is_page() with non-matching slug
     */
    public function testIsPageWithNonMatchingSlug() {
        echo "Test 9: is_page() with non-matching slug\n";

        global $wp_query;

        $page_post = new WP_Post();
        $page_post->ID = 42;
        $page_post->post_title = 'About Us';
        $page_post->post_name = 'about';
        $page_post->post_type = 'page';

        $wp_query = new WP_Query();
        $wp_query->posts = array($page_post);
        $wp_query->post_count = 1;
        $wp_query->is_page = true;
        $wp_query->queried_object = $page_post;

        $this->assert(is_page('nonexistent') === false, 'is_page("nonexistent") returns false');

        echo "\n";
    }

    /**
     * Test 10: is_page() with empty WP_Query
     */
    public function testIsPageEmptyQuery() {
        echo "Test 10: is_page() with empty query\n";

        global $wp_query;
        $wp_query = new WP_Query();

        $this->assert(is_page() === false, 'is_page() returns false for empty query');

        echo "\n";
    }

    /**
     * Test 11: is_page() with string ID
     */
    public function testIsPageWithStringId() {
        echo "Test 11: is_page() with string ID\n";

        global $wp_query;

        $page_post = new WP_Post();
        $page_post->ID = 42;
        $page_post->post_title = 'About Us';
        $page_post->post_name = 'about';
        $page_post->post_type = 'page';

        $wp_query = new WP_Query();
        $wp_query->posts = array($page_post);
        $wp_query->post_count = 1;
        $wp_query->is_page = true;
        $wp_query->queried_object = $page_post;

        $this->assert(is_page('42') === true, 'is_page("42") returns true when ID is numeric string');

        echo "\n";
    }

    /**
     * Test 12: is_page() with numeric string
     */
    public function testIsPageWithNumericString() {
        echo "Test 12: is_page() with numeric string vs actual number\n";

        global $wp_query;

        $page_post = new WP_Post();
        $page_post->ID = 42;
        $page_post->post_title = 'About Us';
        $page_post->post_name = 'about';
        $page_post->post_type = 'page';

        $wp_query = new WP_Query();
        $wp_query->posts = array($page_post);
        $wp_query->post_count = 1;
        $wp_query->is_page = true;
        $wp_query->queried_object = $page_post;

        $this->assert(is_page('42') === true, 'is_page("42") works with string');
        $this->assert(is_page(42) === true, 'is_page(42) works with integer');

        echo "\n";
    }

    /**
     * Test 13: is_page() with mixed array
     */
    public function testIsPageMixedArray() {
        echo "Test 13: is_page() with mixed type array\n";

        global $wp_query;

        $page_post = new WP_Post();
        $page_post->ID = 42;
        $page_post->post_title = 'About Us';
        $page_post->post_name = 'about';
        $page_post->post_type = 'page';

        $wp_query = new WP_Query();
        $wp_query->posts = array($page_post);
        $wp_query->post_count = 1;
        $wp_query->is_page = true;
        $wp_query->queried_object = $page_post;

        $this->assert(is_page(array(42, 'contact', 99, 'team')) === true, 'is_page() handles mixed int/string array');

        echo "\n";
    }

    /**
     * Test 14: is_page() title matching is case-sensitive
     */
    public function testIsPageCaseSensitiveTitle() {
        echo "Test 14: is_page() case sensitivity\n";

        global $wp_query;

        $page_post = new WP_Post();
        $page_post->ID = 42;
        $page_post->post_title = 'About Us';
        $page_post->post_name = 'about';
        $page_post->post_type = 'page';

        $wp_query = new WP_Query();
        $wp_query->posts = array($page_post);
        $wp_query->post_count = 1;
        $wp_query->is_page = true;
        $wp_query->queried_object = $page_post;

        $this->assert(is_page('About Us') === true, 'is_page() matches exact case');
        $this->assert(is_page('about us') === false, 'is_page() is case-sensitive for title');
        $this->assert(is_page('ABOUT US') === false, 'is_page() is case-sensitive for title (uppercase)');

        echo "\n";
    }

    /**
     * Test 15: is_page() with zero ID
     */
    public function testIsPageWithZeroId() {
        echo "Test 15: is_page() with zero ID\n";

        global $wp_query;

        $page_post = new WP_Post();
        $page_post->ID = 0;
        $page_post->post_title = 'Test';
        $page_post->post_name = 'test';
        $page_post->post_type = 'page';

        $wp_query = new WP_Query();
        $wp_query->posts = array($page_post);
        $wp_query->post_count = 1;
        $wp_query->is_page = true;
        $wp_query->queried_object = $page_post;

        $this->assert(is_page(0) === true, 'is_page(0) works with zero ID');

        echo "\n";
    }

    /**
     * Test 16: is_page() with negative ID (edge case)
     */
    public function testIsPageWithNegativeId() {
        echo "Test 16: is_page() with negative ID\n";

        global $wp_query;

        $page_post = new WP_Post();
        $page_post->ID = 42;
        $page_post->post_title = 'About';
        $page_post->post_name = 'about';
        $page_post->post_type = 'page';

        $wp_query = new WP_Query();
        $wp_query->posts = array($page_post);
        $wp_query->post_count = 1;
        $wp_query->is_page = true;
        $wp_query->queried_object = $page_post;

        $this->assert(is_page(-1) === false, 'is_page(-1) returns false for negative ID');

        echo "\n";
    }

    /**
     * Test 17: is_page() with empty string parameter
     */
    public function testIsPageWithEmptyString() {
        echo "Test 17: is_page() with empty string\n";

        global $wp_query;

        $page_post = new WP_Post();
        $page_post->ID = 42;
        $page_post->post_title = 'About';
        $page_post->post_name = 'about';
        $page_post->post_type = 'page';

        $wp_query = new WP_Query();
        $wp_query->posts = array($page_post);
        $wp_query->post_count = 1;
        $wp_query->is_page = true;
        $wp_query->queried_object = $page_post;

        $this->assert(is_page('') === true, 'is_page("") returns true for any page');

        echo "\n";
    }
}

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli') {
    $test = new IsPageTest();
    $success = $test->run();
    exit($success ? 0 : 1);
}
