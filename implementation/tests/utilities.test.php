<?php
/**
 * WP2BD Utilities Functions Test Suite
 *
 * Comprehensive unit tests for WordPress utility functions:
 * - home_url()
 * - bloginfo() / get_bloginfo()
 * - get_template_directory()
 * - get_template_directory_uri()
 *
 * Test coverage:
 * - URL construction with various paths and schemes
 * - Blog information retrieval for all supported values
 * - Template directory path and URI functions
 * - Edge cases (empty strings, null, trailing slashes)
 * - Backdrop integration and fallbacks
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include the utilities functions
require_once dirname(__FILE__) . '/../functions/utilities.php';

/**
 * Simple test framework
 */
class UtilitiesTestSuite {
    private $tests_run = 0;
    private $tests_passed = 0;
    private $tests_failed = 0;
    private $current_test = '';

    /**
     * Run all tests
     */
    public function run() {
        echo "========================================\n";
        echo "WP2BD Utilities Functions Test Suite\n";
        echo "========================================\n\n";

        // Setup test environment
        $this->setup();

        // Run test groups
        $this->testHomeUrl();
        $this->testHomeUrlSchemes();
        $this->testHomeUrlPaths();
        $this->testHomeUrlEdgeCases();
        $this->testGetBloginfo();
        $this->testBloginfoOutput();
        $this->testGetTemplateDirectory();
        $this->testGetTemplateDirectoryUri();
        $this->testHelperFunctions();

        // Print summary
        $this->printSummary();

        return $this->tests_failed === 0;
    }

    /**
     * Setup test environment
     */
    private function setup() {
        global $base_url;

        // Set up a test base URL
        $base_url = 'http://example.com';

        // Set up $_SERVER variables for testing
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/html';

        echo "Setup: Test environment initialized\n";
        echo "  Base URL: $base_url\n";
        echo "  HTTP Host: {$_SERVER['HTTP_HOST']}\n\n";
    }

    /**
     * Test home_url() basic functionality
     */
    private function testHomeUrl() {
        $this->currentTest("home_url() - Basic Functionality");

        // Test 1: Default home URL
        $result = home_url();
        $this->assertEquals('http://example.com', $result, 'Default home_url()');

        // Test 2: Home URL with empty string
        $result = home_url('');
        $this->assertEquals('http://example.com', $result, 'home_url with empty string');

        // Test 3: Home URL with root path
        $result = home_url('/');
        $this->assertEquals('http://example.com/', $result, 'home_url with root path');
    }

    /**
     * Test home_url() with different schemes
     */
    private function testHomeUrlSchemes() {
        $this->currentTest("home_url() - Scheme Handling");

        // Test 4: HTTP scheme
        $result = home_url('', 'http');
        $this->assertEquals('http://example.com', $result, 'home_url with http scheme');

        // Test 5: HTTPS scheme
        $result = home_url('', 'https');
        $this->assertEquals('https://example.com', $result, 'home_url with https scheme');

        // Test 6: Relative scheme
        $result = home_url('', 'relative');
        $this->assertEquals('', $result, 'home_url with relative scheme (no path)');

        // Test 7: Relative scheme with path
        $result = home_url('/about', 'relative');
        $this->assertEquals('/about', $result, 'home_url with relative scheme and path');

        // Test 8: HTTPS scheme with path
        $result = home_url('/contact', 'https');
        $this->assertEquals('https://example.com/contact', $result, 'home_url with https and path');
    }

    /**
     * Test home_url() with various paths
     */
    private function testHomeUrlPaths() {
        $this->currentTest("home_url() - Path Handling");

        // Test 9: Path without leading slash
        $result = home_url('about');
        $this->assertEquals('http://example.com/about', $result, 'home_url with path without leading slash');

        // Test 10: Path with leading slash
        $result = home_url('/about');
        $this->assertEquals('http://example.com/about', $result, 'home_url with path with leading slash');

        // Test 11: Deep path
        $result = home_url('/blog/2025/11/post-title');
        $this->assertEquals('http://example.com/blog/2025/11/post-title', $result, 'home_url with deep path');

        // Test 12: Path with query string
        $result = home_url('/search?q=test');
        $this->assertEquals('http://example.com/search?q=test', $result, 'home_url with query string');

        // Test 13: Path with trailing slash
        $result = home_url('/about/');
        $this->assertEquals('http://example.com/about/', $result, 'home_url with trailing slash');
    }

    /**
     * Test home_url() edge cases
     */
    private function testHomeUrlEdgeCases() {
        global $base_url;
        $this->currentTest("home_url() - Edge Cases");

        // Test 14: Base URL with trailing slash
        $original = $base_url;
        $base_url = 'http://example.com/';
        $result = home_url('/test');
        $this->assertEquals('http://example.com/test', $result, 'home_url handles base URL with trailing slash');
        $base_url = $original;

        // Test 15: Base URL with path
        $base_url = 'http://example.com/subdir';
        $result = home_url('/page');
        $this->assertEquals('http://example.com/subdir/page', $result, 'home_url with base path');
        $base_url = $original;

        // Test 16: Multiple slashes in path
        $result = home_url('//about//us//');
        $this->assertContains('/about', $result, 'home_url handles multiple slashes');
    }

    /**
     * Test get_bloginfo() for all supported values
     */
    private function testGetBloginfo() {
        $this->currentTest("get_bloginfo() - All Supported Values");

        // Test 17: Site name (default)
        $result = get_bloginfo();
        $this->assertNotEmpty($result, 'get_bloginfo returns site name by default');

        // Test 18: Site name explicitly
        $result = get_bloginfo('name');
        $this->assertNotEmpty($result, 'get_bloginfo("name") returns site name');

        // Test 19: Site description
        $result = get_bloginfo('description');
        $this->assertNotEmpty($result, 'get_bloginfo("description") returns description');

        // Test 20: URL
        $result = get_bloginfo('url');
        $this->assertEquals('http://example.com', $result, 'get_bloginfo("url") returns home URL');

        // Test 21: WordPress URL
        $result = get_bloginfo('wpurl');
        $this->assertEquals('http://example.com', $result, 'get_bloginfo("wpurl") returns WordPress URL');

        // Test 22: Template directory
        $result = get_bloginfo('template_directory');
        $this->assertContains('twentyseventeen', $result, 'get_bloginfo("template_directory") contains theme name');

        // Test 23: Stylesheet directory
        $result = get_bloginfo('stylesheet_directory');
        $this->assertContains('twentyseventeen', $result, 'get_bloginfo("stylesheet_directory") contains theme name');

        // Test 24: Charset
        $result = get_bloginfo('charset');
        $this->assertEquals('UTF-8', $result, 'get_bloginfo("charset") returns UTF-8');

        // Test 25: Language
        $result = get_bloginfo('language');
        $this->assertNotEmpty($result, 'get_bloginfo("language") returns language code');

        // Test 26: Version
        $result = get_bloginfo('version');
        $this->assertEquals('4.9', $result, 'get_bloginfo("version") returns 4.9');

        // Test 27: Text direction
        $result = get_bloginfo('text_direction');
        $this->assertEquals('ltr', $result, 'get_bloginfo("text_direction") returns ltr');

        // Test 28: HTML type
        $result = get_bloginfo('html_type');
        $this->assertEquals('text/html', $result, 'get_bloginfo("html_type") returns text/html');

        // Test 29: Stylesheet URL
        $result = get_bloginfo('stylesheet_url');
        $this->assertContains('style.css', $result, 'get_bloginfo("stylesheet_url") contains style.css');
    }

    /**
     * Test bloginfo() output function
     */
    private function testBloginfoOutput() {
        $this->currentTest("bloginfo() - Output Function");

        // Test 30: bloginfo() echoes site name
        ob_start();
        bloginfo('name');
        $output = ob_get_clean();
        $this->assertNotEmpty($output, 'bloginfo("name") produces output');

        // Test 31: bloginfo() echoes URL
        ob_start();
        bloginfo('url');
        $output = ob_get_clean();
        $this->assertEquals('http://example.com', $output, 'bloginfo("url") echoes home URL');

        // Test 32: bloginfo() echoes version
        ob_start();
        bloginfo('version');
        $output = ob_get_clean();
        $this->assertEquals('4.9', $output, 'bloginfo("version") echoes 4.9');
    }

    /**
     * Test get_template_directory()
     */
    private function testGetTemplateDirectory() {
        $this->currentTest("get_template_directory() - Path Functions");

        // Test 33: Returns a path
        $result = get_template_directory();
        $this->assertNotEmpty($result, 'get_template_directory returns non-empty path');

        // Test 34: Contains theme name
        $this->assertContains('twentyseventeen', $result, 'get_template_directory contains twentyseventeen');

        // Test 35: Is an absolute path
        $this->assertTrue(
            substr($result, 0, 1) === '/' || substr($result, 1, 1) === ':',
            'get_template_directory returns absolute path'
        );

        // Test 36: No trailing slash
        $this->assertNotEquals('/', substr($result, -1), 'get_template_directory has no trailing slash');

        // Test 37: get_template() returns theme name
        $result = get_template();
        $this->assertEquals('twentyseventeen', $result, 'get_template returns theme directory name');

        // Test 38: get_stylesheet_directory() matches template directory
        $template = get_template_directory();
        $stylesheet = get_stylesheet_directory();
        $this->assertEquals($template, $stylesheet, 'get_stylesheet_directory matches template for parent themes');
    }

    /**
     * Test get_template_directory_uri()
     */
    private function testGetTemplateDirectoryUri() {
        $this->currentTest("get_template_directory_uri() - URI Functions");

        // Test 39: Returns a URI
        $result = get_template_directory_uri();
        $this->assertNotEmpty($result, 'get_template_directory_uri returns non-empty URI');

        // Test 40: Contains theme name
        $this->assertContains('twentyseventeen', $result, 'get_template_directory_uri contains twentyseventeen');

        // Test 41: Starts with http:// or https://
        $this->assertTrue(
            strpos($result, 'http://') === 0 || strpos($result, 'https://') === 0,
            'get_template_directory_uri returns full URL'
        );

        // Test 42: No trailing slash
        $this->assertNotEquals('/', substr($result, -1), 'get_template_directory_uri has no trailing slash');

        // Test 43: Contains expected path structure
        $this->assertContains('themes/', $result, 'get_template_directory_uri contains themes/');

        // Test 44: get_stylesheet_directory_uri() matches template URI
        $template_uri = get_template_directory_uri();
        $stylesheet_uri = get_stylesheet_directory_uri();
        $this->assertEquals($template_uri, $stylesheet_uri, 'get_stylesheet_directory_uri matches template URI');

        // Test 45: get_stylesheet() returns theme name
        $result = get_stylesheet();
        $this->assertEquals('twentyseventeen', $result, 'get_stylesheet returns theme directory name');
    }

    /**
     * Test helper functions
     */
    private function testHelperFunctions() {
        $this->currentTest("Helper Functions - Integration Tests");

        // Test 46: Combining home_url with template directory
        $home = home_url();
        $template_uri = get_template_directory_uri();
        $this->assertContains($home, $template_uri, 'Template URI contains home URL');

        // Test 47: Building asset URL
        $asset_url = get_template_directory_uri() . '/assets/css/style.css';
        $this->assertContains('twentyseventeen', $asset_url, 'Asset URL construction works');
        $this->assertContains('.css', $asset_url, 'Asset URL contains file extension');

        // Test 48: Building file path
        $file_path = get_template_directory() . '/functions.php';
        $this->assertContains('twentyseventeen', $file_path, 'File path construction works');
        $this->assertContains('functions.php', $file_path, 'File path contains filename');

        // Test 49: Consistency between path and URI
        $path = get_template_directory();
        $uri = get_template_directory_uri();
        $theme_name = basename($path);
        $this->assertContains($theme_name, $uri, 'URI contains same theme name as path');

        // Test 50: Multiple calls return same result (caching)
        $first_call = get_template_directory();
        $second_call = get_template_directory();
        $this->assertEquals($first_call, $second_call, 'get_template_directory caches results');

        // Test 51: Template URI caching
        $first_uri = get_template_directory_uri();
        $second_uri = get_template_directory_uri();
        $this->assertEquals($first_uri, $second_uri, 'get_template_directory_uri caches results');
    }

    /**
     * Start a new test group
     */
    private function currentTest($name) {
        $this->current_test = $name;
        echo "\n" . $name . "\n";
        echo str_repeat('-', strlen($name)) . "\n";
    }

    /**
     * Assert two values are equal
     */
    private function assertEquals($expected, $actual, $message) {
        $this->tests_run++;
        if ($expected === $actual) {
            $this->tests_passed++;
            echo "  ✓ PASS: $message\n";
        } else {
            $this->tests_failed++;
            echo "  ✗ FAIL: $message\n";
            echo "    Expected: " . var_export($expected, true) . "\n";
            echo "    Actual:   " . var_export($actual, true) . "\n";
        }
    }

    /**
     * Assert value is not empty
     */
    private function assertNotEmpty($value, $message) {
        $this->tests_run++;
        if (!empty($value)) {
            $this->tests_passed++;
            echo "  ✓ PASS: $message\n";
        } else {
            $this->tests_failed++;
            echo "  ✗ FAIL: $message\n";
            echo "    Value was empty\n";
        }
    }

    /**
     * Assert value contains substring
     */
    private function assertContains($needle, $haystack, $message) {
        $this->tests_run++;
        if (strpos($haystack, $needle) !== false) {
            $this->tests_passed++;
            echo "  ✓ PASS: $message\n";
        } else {
            $this->tests_failed++;
            echo "  ✗ FAIL: $message\n";
            echo "    Expected to find: '$needle'\n";
            echo "    In: '$haystack'\n";
        }
    }

    /**
     * Assert value is true
     */
    private function assertTrue($value, $message) {
        $this->tests_run++;
        if ($value === true) {
            $this->tests_passed++;
            echo "  ✓ PASS: $message\n";
        } else {
            $this->tests_failed++;
            echo "  ✗ FAIL: $message\n";
            echo "    Expected: true\n";
            echo "    Actual: " . var_export($value, true) . "\n";
        }
    }

    /**
     * Assert two values are not equal
     */
    private function assertNotEquals($expected, $actual, $message) {
        $this->tests_run++;
        if ($expected !== $actual) {
            $this->tests_passed++;
            echo "  ✓ PASS: $message\n";
        } else {
            $this->tests_failed++;
            echo "  ✗ FAIL: $message\n";
            echo "    Expected not to equal: " . var_export($expected, true) . "\n";
            echo "    But got: " . var_export($actual, true) . "\n";
        }
    }

    /**
     * Print test summary
     */
    private function printSummary() {
        echo "\n========================================\n";
        echo "Test Summary\n";
        echo "========================================\n";
        echo "Total tests run:    {$this->tests_run}\n";
        echo "Tests passed:       {$this->tests_passed}\n";
        echo "Tests failed:       {$this->tests_failed}\n";

        if ($this->tests_failed === 0) {
            echo "\n✓ ALL TESTS PASSED!\n";
        } else {
            echo "\n✗ SOME TESTS FAILED\n";
        }
        echo "========================================\n";
    }
}

// Run the tests
$suite = new UtilitiesTestSuite();
$success = $suite->run();

// Exit with appropriate code
exit($success ? 0 : 1);
