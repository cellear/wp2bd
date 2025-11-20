<?php
/**
 * WP2BD Loop Functions Test Suite
 *
 * Comprehensive unit tests for WordPress Loop functions:
 * - have_posts()
 * - the_post()
 * - wp_reset_postdata()
 * - setup_postdata()
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include the loop functions
require_once dirname(__FILE__) . '/../functions/loop.php';

/**
 * Mock WP_Post class for testing
 */
class WP_Post {
    public $ID;
    public $post_author;
    public $post_date;
    public $post_date_gmt;
    public $post_content;
    public $post_title;
    public $post_excerpt;
    public $post_status;
    public $post_name;
    public $post_modified;
    public $post_modified_gmt;
    public $post_parent;
    public $post_type;
    public $comment_count;
    public $filter;

    /**
     * Create a mock post with given ID and title
     */
    public static function create($id, $title = 'Test Post', $content = 'Test content') {
        $post = new WP_Post();
        $post->ID = $id;
        $post->post_title = $title;
        $post->post_content = $content;
        $post->post_author = 1;
        $post->post_date = '2025-11-20 10:00:00';
        $post->post_date_gmt = '2025-11-20 10:00:00';
        $post->post_excerpt = '';
        $post->post_status = 'publish';
        $post->post_name = 'test-post-' . $id;
        $post->post_modified = '2025-11-20 10:00:00';
        $post->post_modified_gmt = '2025-11-20 10:00:00';
        $post->post_parent = 0;
        $post->post_type = 'post';
        $post->comment_count = 0;
        $post->filter = 'raw';
        return $post;
    }
}

/**
 * Mock WP_Query class for testing
 */
class WP_Query {
    public $posts = array();
    public $post_count = 0;
    public $current_post = -1;
    public $post;
    public $queried_object = null;
    public $queried_object_id = null;

    public function __construct($posts = array()) {
        if (!empty($posts)) {
            $this->posts = $posts;
            $this->post_count = count($posts);
        }
    }

    public function have_posts() {
        return ($this->current_post + 1) < $this->post_count;
    }

    public function the_post() {
        $this->current_post++;

        if ($this->current_post < $this->post_count) {
            $this->post = $this->posts[$this->current_post];

            global $post;
            $post = $this->post;

            setup_postdata($post);

            do_action('the_post', $post, $this);
        }
    }

    public function reset_postdata() {
        if ($this->current_post > -1) {
            $this->current_post = -1;
        }
    }
}

/**
 * Test Suite for Loop Functions
 */
class LoopFunctionsTest {

    private $tests_run = 0;
    private $tests_passed = 0;
    private $tests_failed = 0;

    /**
     * Run all tests
     */
    public function run() {
        echo "\n=== WP2BD Loop Functions Test Suite ===\n\n";

        // Core functionality tests
        $this->testHavePostsWithContent();
        $this->testHavePostsEmpty();
        $this->testHavePostsNoQuery();
        $this->testThePostSetsGlobals();
        $this->testLoopIteration();
        $this->testResetPostdata();

        // Edge case tests
        $this->testMultiPageContent();
        $this->testMultiPageContentNormalization();
        $this->testSetupPostdataWithInvalidInput();
        $this->testSetupPostdataWithPostID();
        $this->testEmptyContent();
        $this->testThePostWithoutQuery();
        $this->testNestedLoopsWithoutReset();
        $this->testPostWithoutAuthor();
        $this->testPostWithoutDate();

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
     * Test 1: have_posts() with content returns true
     */
    public function testHavePostsWithContent() {
        echo "Test 1: have_posts() with content\n";

        global $wp_query;
        $wp_query = new WP_Query(array(
            WP_Post::create(1),
            WP_Post::create(2),
            WP_Post::create(3),
        ));

        $this->assert(have_posts() === true, 'have_posts() returns true with posts');
        $this->assert($wp_query->current_post === -1, 'current_post starts at -1');

        echo "\n";
    }

    /**
     * Test 2: have_posts() with empty query returns false
     */
    public function testHavePostsEmpty() {
        echo "Test 2: have_posts() with empty query\n";

        global $wp_query;
        $wp_query = new WP_Query();

        $this->assert(have_posts() === false, 'have_posts() returns false with no posts');

        echo "\n";
    }

    /**
     * Test 3: have_posts() with no query object returns false
     */
    public function testHavePostsNoQuery() {
        echo "Test 3: have_posts() with no query object\n";

        global $wp_query;
        $wp_query = null;

        $this->assert(have_posts() === false, 'have_posts() returns false when $wp_query is null');

        echo "\n";
    }

    /**
     * Test 4: the_post() sets up global variables correctly
     */
    public function testThePostSetsGlobals() {
        echo "Test 4: the_post() sets globals\n";

        global $wp_query, $post, $id;

        $wp_query = new WP_Query(array(
            WP_Post::create(123, 'Test Title', 'Test Content'),
        ));

        the_post();

        $this->assert($post !== null, 'Global $post is set');
        $this->assert($post->ID === 123, 'Post ID is correct');
        $this->assert($post->post_title === 'Test Title', 'Post title is correct');
        $this->assert($id === 123, 'Global $id is set correctly');
        $this->assert($wp_query->current_post === 0, 'current_post incremented to 0');

        echo "\n";
    }

    /**
     * Test 5: Complete loop iteration
     */
    public function testLoopIteration() {
        echo "Test 5: Loop iteration\n";

        global $wp_query, $post;

        $wp_query = new WP_Query(array(
            WP_Post::create(1, 'Post 1'),
            WP_Post::create(2, 'Post 2'),
            WP_Post::create(3, 'Post 3'),
        ));

        $titles = array();
        $iterations = 0;

        while (have_posts()) {
            the_post();
            $titles[] = $post->post_title;
            $iterations++;

            // Prevent infinite loop
            if ($iterations > 10) {
                break;
            }
        }

        $this->assert(count($titles) === 3, 'Loop executed 3 times');
        $this->assert($titles[0] === 'Post 1', 'First post title correct');
        $this->assert($titles[1] === 'Post 2', 'Second post title correct');
        $this->assert($titles[2] === 'Post 3', 'Third post title correct');
        $this->assert(have_posts() === false, 'have_posts() is false after loop');

        echo "\n";
    }

    /**
     * Test 6: wp_reset_postdata() restores original post
     */
    public function testResetPostdata() {
        echo "Test 6: wp_reset_postdata()\n";

        global $wp_query, $post, $id;

        // Set up main query with post 1
        $wp_query = new WP_Query(array(
            WP_Post::create(1, 'Main Post'),
        ));
        the_post();

        $this->assert($post->ID === 1, 'Main post ID is 1');

        // Manually change global $post (simulating custom query)
        $post = WP_Post::create(999, 'Custom Post');
        setup_postdata($post);

        $this->assert($post->ID === 999, 'Post changed to custom post');
        $this->assert($id === 999, 'Global $id reflects custom post');

        // Reset should restore main query post
        wp_reset_postdata();

        $this->assert($post->ID === 1, 'Post reset to main query post');
        $this->assert($id === 1, 'Global $id reset to main query');

        echo "\n";
    }

    /**
     * Test 7: Multi-page content splitting
     */
    public function testMultiPageContent() {
        echo "Test 7: Multi-page content splitting\n";

        global $pages, $numpages, $multipage, $page, $more;

        $content = 'Page 1 content<!--nextpage-->Page 2 content<!--nextpage-->Page 3 content';
        $post = WP_Post::create(1, 'Multi-page Post', $content);

        setup_postdata($post);

        $this->assert($numpages === 3, 'Content split into 3 pages');
        $this->assert($multipage === true, 'multipage flag is true');
        $this->assert(count($pages) === 3, 'pages array has 3 elements');
        $this->assert($pages[0] === 'Page 1 content', 'First page content correct');
        $this->assert($pages[1] === 'Page 2 content', 'Second page content correct');
        $this->assert($pages[2] === 'Page 3 content', 'Third page content correct');
        $this->assert($page === 1, 'Default page is 1');
        $this->assert($more === 1, 'Default more is 1');

        echo "\n";
    }

    /**
     * Test 8: Multi-page content with whitespace normalization
     */
    public function testMultiPageContentNormalization() {
        echo "Test 8: Multi-page content normalization\n";

        global $pages, $numpages;

        // Test various whitespace patterns around nextpage tag
        $content = "Page 1\n<!--nextpage-->\nPage 2\n<!--nextpage-->Page 3<!--nextpage-->\nPage 4";
        $post = WP_Post::create(1, 'Test', $content);

        setup_postdata($post);

        $this->assert($numpages === 4, 'Content split into 4 pages despite whitespace');
        $this->assert(strpos($pages[0], '<!--nextpage-->') === false, 'Nextpage tags removed from content');

        echo "\n";
    }

    /**
     * Test 9: setup_postdata() with invalid input
     */
    public function testSetupPostdataWithInvalidInput() {
        echo "Test 9: setup_postdata() with invalid input\n";

        // Test with null
        $result = setup_postdata(null);
        $this->assert($result === false, 'Returns false for null input');

        // Test with string
        $result = setup_postdata('invalid');
        $this->assert($result === false, 'Returns false for string input');

        // Test with array
        $result = setup_postdata(array('ID' => 1));
        $this->assert($result === false, 'Returns false for array input');

        // Test with object without ID
        $obj = new stdClass();
        $result = setup_postdata($obj);
        $this->assert($result === false, 'Returns false for object without ID');

        echo "\n";
    }

    /**
     * Test 10: setup_postdata() with post ID (numeric)
     */
    public function testSetupPostdataWithPostID() {
        echo "Test 10: setup_postdata() with post ID\n";

        global $post;

        // First set a global post
        $post = WP_Post::create(42, 'Test');

        // Call setup_postdata with just the ID
        $result = setup_postdata(42);

        // Since get_post() returns null for IDs in our implementation,
        // this should return false
        $this->assert($result === false, 'Returns false when get_post() returns null');

        echo "\n";
    }

    /**
     * Test 11: Empty content handling
     */
    public function testEmptyContent() {
        echo "Test 11: Empty content handling\n";

        global $pages, $numpages, $multipage;

        $post = WP_Post::create(1, 'Empty Post', '');
        setup_postdata($post);

        $this->assert($numpages === 1, 'Empty content creates 1 page');
        $this->assert($multipage === false, 'multipage is false for empty content');
        $this->assert(count($pages) === 1, 'pages array has 1 element');
        $this->assert($pages[0] === '', 'Page content is empty string');

        echo "\n";
    }

    /**
     * Test 12: the_post() without query
     */
    public function testThePostWithoutQuery() {
        echo "Test 12: the_post() without query\n";

        global $wp_query, $post;

        $wp_query = null;
        $post = null;

        // Should not fatal error
        the_post();

        $this->assert($post === null, 'Post remains null when no query');

        echo "\n";
    }

    /**
     * Test 13: Nested loops without reset (demonstrates corruption)
     */
    public function testNestedLoopsWithoutReset() {
        echo "Test 13: Nested loops without reset\n";

        global $wp_query, $post;

        // Main query
        $main_query = new WP_Query(array(
            WP_Post::create(1, 'Main 1'),
            WP_Post::create(2, 'Main 2'),
        ));
        $wp_query = $main_query;

        // Start main loop
        the_post();
        $first_post_id = $post->ID;
        $this->assert($first_post_id === 1, 'First main post is ID 1');

        // Custom nested query
        $custom_query = new WP_Query(array(
            WP_Post::create(99, 'Custom'),
        ));
        $wp_query = $custom_query;

        the_post();
        $this->assert($post->ID === 99, 'Custom query post is ID 99');

        // Without reset, global $post is still ID 99
        $wp_query = $main_query;
        $this->assert($post->ID === 99, 'Without reset, post remains ID 99 (corrupted)');

        // With reset, it's restored
        wp_reset_postdata();
        $this->assert($post->ID === 1, 'After reset, post is back to ID 1');

        echo "\n";
    }

    /**
     * Test 14: Post without author
     */
    public function testPostWithoutAuthor() {
        echo "Test 14: Post without author\n";

        global $authordata, $id;

        $post = WP_Post::create(1, 'Test');
        $post->post_author = null;

        $result = setup_postdata($post);

        $this->assert($result === true, 'setup_postdata succeeds without author');
        $this->assert(isset($authordata), 'authordata is set (even if minimal)');
        $this->assert($id === 1, 'Post ID still set correctly');

        echo "\n";
    }

    /**
     * Test 15: Post without date
     */
    public function testPostWithoutDate() {
        echo "Test 15: Post without date\n";

        global $currentday, $currentmonth;

        $post = WP_Post::create(1, 'Test');
        $post->post_date = null;

        $result = setup_postdata($post);

        $this->assert($result === true, 'setup_postdata succeeds without date');
        $this->assert($currentday === '', 'currentday is empty string');
        $this->assert($currentmonth === '', 'currentmonth is empty string');

        echo "\n";
    }
}

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli') {
    $test = new LoopFunctionsTest();
    $success = $test->run();
    exit($success ? 0 : 1);
}
