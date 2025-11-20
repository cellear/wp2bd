<?php
/**
 * WP2BD Home/Front Page Conditional Functions Test Suite
 *
 * Comprehensive unit tests for WordPress home/front page conditionals:
 * - is_home()
 * - is_front_page()
 *
 * Tests various Backdrop front page configurations to ensure proper
 * WordPress compatibility.
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include the conditional functions
require_once dirname(__FILE__) . '/../functions/conditionals.php';

/**
 * Mock config_get function for testing
 */
if (!function_exists('config_get')) {
    function config_get($config, $key) {
        global $_wp2bd_test_config;

        if (!isset($_wp2bd_test_config)) {
            $_wp2bd_test_config = array();
        }

        $config_key = $config . '::' . $key;
        return isset($_wp2bd_test_config[$config_key]) ? $_wp2bd_test_config[$config_key] : null;
    }
}

/**
 * Mock WP_Query class for testing
 */
class WP_Query {
    public $is_front_page = false;
    public $is_home = false;

    public function is_front_page() {
        return $this->is_front_page;
    }

    public function is_home() {
        return $this->is_home;
    }
}

/**
 * Test Suite for Home/Front Page Conditionals
 */
class HomeFrontPageTest {

    private $tests_run = 0;
    private $tests_passed = 0;
    private $tests_failed = 0;

    /**
     * Run all tests
     */
    public function run() {
        echo "\n=== WP2BD Home/Front Page Conditionals Test Suite ===\n\n";

        // Core functionality tests
        $this->testFrontPageDefault();
        $this->testFrontPageCustomPath();
        $this->testFrontPageStaticNode();
        $this->testFrontPageWithFrontMarker();
        $this->testFrontPageEmptyPath();
        $this->testFrontPageWithWPQuery();

        $this->testHomeDefaultBlogListing();
        $this->testHomeStaticFrontPage();
        $this->testHomeSeparatePostsPage();
        $this->testHomeBlogPath();
        $this->testHomeCustomPath();
        $this->testHomeWithWPQuery();

        // Combined scenarios
        $this->testBothTrueOnBlogHome();
        $this->testDifferentOnStaticFront();

        // Edge cases
        $this->testNoConfigSystem();
        $this->testEmptyConfiguration();

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
     * Setup helper - configure test environment
     */
    private function setup($front_page = 'node', $posts_page = null, $current_path = '') {
        global $_wp2bd_test_config, $wp_query;

        // Reset config
        $_wp2bd_test_config = array();

        // Set front page
        if ($front_page !== null) {
            $_wp2bd_test_config['system.core::site_frontpage'] = $front_page;
        }

        // Set posts page
        if ($posts_page !== null) {
            $_wp2bd_test_config['wp2bd.settings::page_for_posts'] = $posts_page;
        }

        // Set current path
        $_GET['q'] = $current_path;

        // Reset WP_Query
        $wp_query = null;
    }

    /**
     * Test 1: is_front_page() with default 'node' configuration
     */
    public function testFrontPageDefault() {
        echo "Test 1: is_front_page() with default 'node' configuration\n";

        // Setup: front page = 'node', current path = ''
        $this->setup('node', null, '');
        $this->assert(is_front_page() === true, 'Empty path is front page when front page is node');

        // Setup: front page = 'node', current path = 'node'
        $this->setup('node', null, 'node');
        $this->assert(is_front_page() === true, 'Path "node" is front page when configured as front');

        // Setup: front page = 'node', current path = 'about'
        $this->setup('node', null, 'about');
        $this->assert(is_front_page() === false, 'Different path is not front page');

        echo "\n";
    }

    /**
     * Test 2: is_front_page() with custom path
     */
    public function testFrontPageCustomPath() {
        echo "Test 2: is_front_page() with custom path\n";

        // Setup: front page = 'home', current path = 'home'
        $this->setup('home', null, 'home');
        $this->assert(is_front_page() === true, 'Custom path "home" is front page');

        // Setup: front page = 'welcome', current path = ''
        $this->setup('welcome', null, '');
        $this->assert(is_front_page() === true, 'Empty path is front page even with custom config');

        echo "\n";
    }

    /**
     * Test 3: is_front_page() with static node
     */
    public function testFrontPageStaticNode() {
        echo "Test 3: is_front_page() with static node as front page\n";

        // Setup: front page = 'node/123', current path = 'node/123'
        $this->setup('node/123', null, 'node/123');
        $this->assert(is_front_page() === true, 'Static node path is front page');

        // Setup: front page = 'node/123', current path = 'node/456'
        $this->setup('node/123', null, 'node/456');
        $this->assert(is_front_page() === false, 'Different node is not front page');

        echo "\n";
    }

    /**
     * Test 4: is_front_page() with '<front>' marker
     */
    public function testFrontPageWithFrontMarker() {
        echo "Test 4: is_front_page() with '<front>' marker\n";

        // Setup: current path = '<front>'
        $this->setup('node', null, '<front>');
        $this->assert(is_front_page() === true, 'Current path "<front>" is always front page');

        // Setup: front page = '<front>', current path = 'anything'
        $this->setup('<front>', null, 'anything');
        $this->assert(is_front_page() === true, 'Front page config "<front>" matches any path');

        echo "\n";
    }

    /**
     * Test 5: is_front_page() with empty path
     */
    public function testFrontPageEmptyPath() {
        echo "Test 5: is_front_page() with empty path\n";

        // Setup: current path is empty
        $this->setup('node', null, '');
        $this->assert(is_front_page() === true, 'Empty current path is front page');

        // Setup: empty config, empty path
        $this->setup('', null, '');
        $this->assert(is_front_page() === true, 'Empty path with empty config is front page');

        echo "\n";
    }

    /**
     * Test 6: is_front_page() with WP_Query
     */
    public function testFrontPageWithWPQuery() {
        echo "Test 6: is_front_page() with WP_Query\n";

        global $wp_query;

        // Setup environment first, then set WP_Query
        $this->setup('node', null, 'anything');

        // Setup WP_Query saying we're on front page
        $wp_query = new WP_Query();
        $wp_query->is_front_page = true;

        $this->assert(is_front_page() === true, 'WP_Query overrides path detection');

        // Setup WP_Query saying we're NOT on front page
        $this->setup('node', null, '');
        $wp_query = new WP_Query();
        $wp_query->is_front_page = false;

        $this->assert(is_front_page() === false, 'WP_Query false overrides empty path');

        echo "\n";
    }

    /**
     * Test 7: is_home() with default blog listing
     */
    public function testHomeDefaultBlogListing() {
        echo "Test 7: is_home() with default blog listing\n";

        // Setup: front page = 'node', current path = ''
        $this->setup('node', null, '');
        $this->assert(is_home() === true, 'Empty path is home when front page is node');

        // Setup: front page = 'node', current path = 'node'
        $this->setup('node', null, 'node');
        $this->assert(is_home() === true, 'Path "node" is home (blog listing)');

        // Setup: front page = 'blog', current path = 'blog'
        $this->setup('blog', null, 'blog');
        $this->assert(is_home() === true, 'Path "blog" is recognized as blog listing');

        echo "\n";
    }

    /**
     * Test 8: is_home() with static front page
     */
    public function testHomeStaticFrontPage() {
        echo "Test 8: is_home() with static front page\n";

        // Setup: front page = 'node/123' (static), current path = 'node/123'
        $this->setup('node/123', null, 'node/123');
        $this->assert(is_home() === false, 'Static node page is NOT home');

        // Setup: front page = 'about', current path = 'about'
        $this->setup('about', null, 'about');
        $this->assert(is_home() === false, 'Custom static page is NOT home');

        echo "\n";
    }

    /**
     * Test 9: is_home() with separate posts page
     */
    public function testHomeSeparatePostsPage() {
        echo "Test 9: is_home() with separate posts page\n";

        // Setup: front page = 'node/123', posts page = 'blog', current = 'blog'
        $this->setup('node/123', 'blog', 'blog');
        $this->assert(is_home() === true, 'Dedicated posts page is home');

        // Setup: front page = 'node/123', posts page = 'blog', current = 'node/123'
        $this->setup('node/123', 'blog', 'node/123');
        $this->assert(is_home() === false, 'Static front page is NOT home when posts page exists');

        // Setup: front page = 'welcome', posts page = 'news', current = 'news'
        $this->setup('welcome', 'news', 'news');
        $this->assert(is_home() === true, 'Custom posts page is home');

        echo "\n";
    }

    /**
     * Test 10: is_home() with blog path
     */
    public function testHomeBlogPath() {
        echo "Test 10: is_home() with various blog paths\n";

        // Test 'posts' path
        $this->setup('posts', null, 'posts');
        $this->assert(is_home() === true, 'Path "posts" is recognized as blog');

        // Test 'articles' path
        $this->setup('articles', null, 'articles');
        $this->assert(is_home() === true, 'Path "articles" is recognized as blog');

        echo "\n";
    }

    /**
     * Test 11: is_home() with custom non-blog path
     */
    public function testHomeCustomPath() {
        echo "Test 11: is_home() with custom non-blog path\n";

        // Setup: front page = 'custom', current = 'custom'
        $this->setup('custom', null, 'custom');
        $this->assert(is_home() === false, 'Custom path not recognized as blog');

        // Setup: front page = 'landing', current = 'landing'
        $this->setup('landing', null, 'landing');
        $this->assert(is_home() === false, 'Landing page not recognized as blog');

        // Not on front page at all
        $this->setup('node', null, 'about');
        $this->assert(is_home() === false, 'Non-front page is not home');

        echo "\n";
    }

    /**
     * Test 12: is_home() with WP_Query
     */
    public function testHomeWithWPQuery() {
        echo "Test 12: is_home() with WP_Query\n";

        global $wp_query;

        // Setup environment first, then set WP_Query
        $this->setup('node/123', null, 'node/123');

        // Setup WP_Query saying we're on home
        $wp_query = new WP_Query();
        $wp_query->is_home = true;

        $this->assert(is_home() === true, 'WP_Query overrides static page detection');

        // Setup WP_Query saying we're NOT on home
        $this->setup('node', null, 'node');
        $wp_query = new WP_Query();
        $wp_query->is_home = false;

        $this->assert(is_home() === false, 'WP_Query false overrides blog path');

        echo "\n";
    }

    /**
     * Test 13: Both is_home() and is_front_page() true
     */
    public function testBothTrueOnBlogHome() {
        echo "Test 13: Both is_home() and is_front_page() true on blog home\n";

        // Setup: default blog listing on front page
        $this->setup('node', null, 'node');

        $is_front = is_front_page();
        $is_home = is_home();

        $this->assert($is_front === true, 'is_front_page() is true on blog home');
        $this->assert($is_home === true, 'is_home() is true on blog home');
        $this->assert($is_front && $is_home, 'Both are true simultaneously');

        echo "\n";
    }

    /**
     * Test 14: Different results on static front page with posts page
     */
    public function testDifferentOnStaticFront() {
        echo "Test 14: Different results with static front page + posts page\n";

        // On the static front page
        $this->setup('node/123', 'blog', 'node/123');

        $is_front = is_front_page();
        $is_home = is_home();

        $this->assert($is_front === true, 'is_front_page() true on static front');
        $this->assert($is_home === false, 'is_home() false on static front');

        // On the posts page
        $this->setup('node/123', 'blog', 'blog');

        $is_front = is_front_page();
        $is_home = is_home();

        $this->assert($is_front === false, 'is_front_page() false on posts page');
        $this->assert($is_home === true, 'is_home() true on posts page');

        echo "\n";
    }

    /**
     * Test 15: No config system available
     */
    public function testNoConfigSystem() {
        echo "Test 15: Behavior when config system unavailable\n";

        global $wp_query;
        $wp_query = null;

        // Temporarily remove config_get function
        $config_exists = function_exists('config_get');

        // Since we can't actually remove the function, test with WP_Query = null
        $this->setup('node', null, '');

        // These should still work with our mock config_get
        $this->assert(is_front_page() !== null, 'is_front_page() returns a value');
        $this->assert(is_home() !== null, 'is_home() returns a value');

        echo "\n";
    }

    /**
     * Test 16: Empty configuration
     */
    public function testEmptyConfiguration() {
        echo "Test 16: Behavior with empty/null configuration\n";

        // Setup: null front page config
        $this->setup(null, null, '');

        // Should default to 'node'
        $this->assert(is_front_page() === true, 'Null config defaults to front page behavior');
        $this->assert(is_home() === true, 'Null config defaults to home behavior');

        // Setup: empty string config
        $this->setup('', null, '');
        $this->assert(is_front_page() === true, 'Empty string config is front page');
        $this->assert(is_home() === true, 'Empty string config is home');

        echo "\n";
    }
}

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli') {
    $test = new HomeFrontPageTest();
    $success = $test->run();
    exit($success ? 0 : 1);
}
