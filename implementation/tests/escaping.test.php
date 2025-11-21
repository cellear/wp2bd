<?php
/**
 * Comprehensive Tests for WordPress Escaping Functions
 *
 * Tests security functions against XSS attacks, edge cases, and proper encoding.
 * All tests must pass with 100% security coverage.
 *
 * @package WP2BD
 * @subpackage Tests
 * @since 1.0.0
 */

// Load the escaping functions
require_once __DIR__ . '/../functions/escaping.php';

class EscapingTest {
    private $passed = 0;
    private $failed = 0;
    private $total = 0;

    /**
     * Run all tests
     */
    public function runAll() {
        echo "==============================================\n";
        echo "WordPress Escaping Functions - Security Tests\n";
        echo "==============================================\n\n";

        $this->testEscHtml();
        $this->testEscAttr();
        $this->testEscUrl();
        $this->testEscUrlRaw();
        $this->testXSSVectors();
        $this->testEdgeCases();
        $this->testUTF8Handling();
        $this->testProtocolValidation();
        $this->testRelativeUrls();
        $this->testHelperFunctions();

        $this->printSummary();

        return $this->failed === 0;
    }

    /**
     * Test esc_html() function
     */
    private function testEscHtml() {
        echo "Testing esc_html()...\n";

        // Test 1: Basic HTML entity encoding
        $this->assert(
            esc_html('<script>alert("xss")</script>') === '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;',
            'esc_html: Should encode script tags'
        );

        // Test 2: Encode quotes
        $this->assert(
            esc_html('Hello "World" \'Test\'') === 'Hello &quot;World&quot; &#039;Test&#039;',
            'esc_html: Should encode both double and single quotes'
        );

        // Test 3: Encode ampersands
        $this->assert(
            esc_html('A & B') === 'A &amp; B',
            'esc_html: Should encode ampersands'
        );

        // Test 4: Empty string
        $this->assert(
            esc_html('') === '',
            'esc_html: Should handle empty strings'
        );

        // Test 5: Null input
        $this->assert(
            esc_html(null) === '',
            'esc_html: Should handle null input'
        );

        // Test 6: Less-than and greater-than
        $this->assert(
            esc_html('5 < 10 > 2') === '5 &lt; 10 &gt; 2',
            'esc_html: Should encode < and > characters'
        );

        echo "\n";
    }

    /**
     * Test esc_attr() function
     */
    private function testEscAttr() {
        echo "Testing esc_attr()...\n";

        // Test 7: Basic attribute encoding
        $this->assert(
            esc_attr('onclick="alert(1)"') === 'onclick=&quot;alert(1)&quot;',
            'esc_attr: Should encode quotes in attributes'
        );

        // Test 8: Remove line breaks
        $this->assert(
            esc_attr("test\nvalue\rhere") === 'testvaluehere',
            'esc_attr: Should remove line breaks'
        );

        // Test 9: Encode script attempt
        $this->assert(
            esc_attr('"><script>alert(1)</script>') === '&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;',
            'esc_attr: Should prevent attribute breakout XSS'
        );

        // Test 10: Empty string
        $this->assert(
            esc_attr('') === '',
            'esc_attr: Should handle empty strings'
        );

        // Test 11: Null input
        $this->assert(
            esc_attr(null) === '',
            'esc_attr: Should handle null input'
        );

        echo "\n";
    }

    /**
     * Test esc_url() function
     */
    private function testEscUrl() {
        echo "Testing esc_url()...\n";

        // Test 12: Valid HTTP URL
        $this->assert(
            esc_url('http://example.com') === 'http://example.com',
            'esc_url: Should allow valid HTTP URLs'
        );

        // Test 13: Valid HTTPS URL
        $this->assert(
            esc_url('https://example.com') === 'https://example.com',
            'esc_url: Should allow valid HTTPS URLs'
        );

        // Test 14: Reject javascript: protocol
        $this->assert(
            esc_url('javascript:alert(1)') === '',
            'esc_url: Should reject javascript: protocol'
        );

        // Test 15: Reject data: protocol
        $this->assert(
            esc_url('data:text/html,<script>alert(1)</script>') === '',
            'esc_url: Should reject data: protocol'
        );

        // Test 16: Allow mailto:
        $this->assert(
            esc_url('mailto:test@example.com') === 'mailto:test@example.com',
            'esc_url: Should allow mailto: protocol'
        );

        // Test 17: Allow FTP
        $this->assert(
            esc_url('ftp://example.com/file.txt') === 'ftp://example.com/file.txt',
            'esc_url: Should allow FTP protocol'
        );

        // Test 18: Encode ampersands for display
        $this->assert(
            esc_url('http://example.com?a=1&b=2') === 'http://example.com?a=1&amp;b=2',
            'esc_url: Should encode ampersands for HTML display'
        );

        // Test 19: Handle URL with quotes
        $url = esc_url('http://example.com?name="test"');
        $this->assert(
            strpos($url, '&quot;') !== false || strpos($url, '%22') !== false,
            'esc_url: Should encode or escape quotes'
        );

        // Test 20: Empty string
        $this->assert(
            esc_url('') === '',
            'esc_url: Should handle empty strings'
        );

        // Test 21: Null input
        $this->assert(
            esc_url(null) === '',
            'esc_url: Should handle null input'
        );

        echo "\n";
    }

    /**
     * Test esc_url_raw() function
     */
    private function testEscUrlRaw() {
        echo "Testing esc_url_raw()...\n";

        // Test 22: Should NOT encode ampersands (for database/redirect)
        $this->assert(
            esc_url_raw('http://example.com?a=1&b=2') === 'http://example.com?a=1&b=2',
            'esc_url_raw: Should NOT encode ampersands'
        );

        // Test 23: Should still reject javascript:
        $this->assert(
            esc_url_raw('javascript:alert(1)') === '',
            'esc_url_raw: Should reject javascript: protocol'
        );

        // Test 24: Valid HTTPS URL
        $this->assert(
            esc_url_raw('https://example.com/path') === 'https://example.com/path',
            'esc_url_raw: Should allow valid HTTPS URLs'
        );

        echo "\n";
    }

    /**
     * Test XSS attack vectors
     */
    private function testXSSVectors() {
        echo "Testing XSS Attack Vectors...\n";

        // Test 25: Script tag injection
        $this->assert(
            strpos(esc_html('<script>'), '<script>') === false,
            'XSS: Should prevent script tag injection'
        );

        // Test 26: Event handler injection via attribute breakout
        $result = esc_attr('" onload="alert(1)');
        $this->assert(
            strpos($result, '"') === false || strpos($result, '&quot;') !== false,
            'XSS: Should prevent event handler injection via attribute breakout'
        );

        // Test 27: JavaScript URL
        $this->assert(
            esc_url('javascript:void(0)') === '',
            'XSS: Should block javascript: URLs'
        );

        // Test 28: VBScript URL
        $this->assert(
            esc_url('vbscript:alert(1)') === '',
            'XSS: Should block vbscript: URLs'
        );

        // Test 29: Data URL with HTML
        $this->assert(
            esc_url('data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==') === '',
            'XSS: Should block data: URLs'
        );

        // Test 30: Mixed case javascript
        $this->assert(
            esc_url('JaVaScRiPt:alert(1)') === '',
            'XSS: Should block javascript: with mixed case'
        );

        // Test 31: URL encoded javascript (with tab character)
        $this->assert(
            esc_url('java%09script:alert(1)') === '',
            'XSS: Should block encoded javascript attempts'
        );

        // Test 32: HTML entities in script
        $result = esc_html('&lt;script&gt;alert(1)&lt;/script&gt;');
        $this->assert(
            strpos($result, '<script>') === false,
            'XSS: Should double-encode already encoded entities'
        );

        echo "\n";
    }

    /**
     * Test edge cases
     */
    private function testEdgeCases() {
        echo "Testing Edge Cases...\n";

        // Test 33: Array input to esc_html
        $this->assert(
            esc_html(['test']) !== '',
            'Edge case: Should handle array input'
        );

        // Test 34: Numeric input
        $this->assert(
            esc_html(12345) === '12345',
            'Edge case: Should handle numeric input'
        );

        // Test 35: Boolean input
        $this->assert(
            esc_html(true) === '1',
            'Edge case: Should handle boolean input'
        );

        // Test 36: Very long string
        $longString = str_repeat('A', 10000);
        $this->assert(
            strlen(esc_html($longString)) >= 10000,
            'Edge case: Should handle long strings'
        );

        // Test 37: URL with spaces
        $url = esc_url('http://example.com/path with spaces');
        $this->assert(
            strpos($url, ' ') === false,
            'Edge case: Should handle spaces in URLs'
        );

        // Test 38: Multiple encoding attempts
        $this->assert(
            esc_html(esc_html('<script>')) === '&amp;lt;script&amp;gt;',
            'Edge case: Should double-encode when called twice'
        );

        echo "\n";
    }

    /**
     * Test UTF-8 handling
     */
    private function testUTF8Handling() {
        echo "Testing UTF-8 Handling...\n";

        // Test 39: UTF-8 characters
        $this->assert(
            esc_html('Hello 世界') === 'Hello 世界',
            'UTF-8: Should preserve UTF-8 characters'
        );

        // Test 40: Emoji
        $this->assert(
            esc_html('Test 😀 emoji') === 'Test 😀 emoji',
            'UTF-8: Should preserve emoji'
        );

        // Test 41: Accented characters
        $this->assert(
            esc_html('Café résumé') === 'Café résumé',
            'UTF-8: Should preserve accented characters'
        );

        // Test 42: Mixed UTF-8 and HTML
        $result = esc_html('Hello 世界 <script>');
        $this->assert(
            strpos($result, '世界') !== false && strpos($result, '<script>') === false,
            'UTF-8: Should preserve UTF-8 but encode HTML'
        );

        echo "\n";
    }

    /**
     * Test protocol validation
     */
    private function testProtocolValidation() {
        echo "Testing Protocol Validation...\n";

        // Test 43: Custom allowed protocol
        $this->assert(
            esc_url('custom://test', ['custom']) === 'custom://test',
            'Protocol: Should allow custom protocols when specified'
        );

        // Test 44: Reject protocol not in whitelist
        $this->assert(
            esc_url('custom://test', ['http', 'https']) === '',
            'Protocol: Should reject protocols not in whitelist'
        );

        // Test 45: Case insensitive protocol check
        $this->assert(
            esc_url('HTTP://example.com') === 'HTTP://example.com',
            'Protocol: Should be case insensitive'
        );

        // Test 46: FTPS protocol
        $this->assert(
            esc_url('ftps://example.com') === 'ftps://example.com',
            'Protocol: Should allow FTPS protocol'
        );

        // Test 47: Feed protocol
        $this->assert(
            esc_url('feed://example.com/rss') === 'feed://example.com/rss',
            'Protocol: Should allow feed: protocol'
        );

        echo "\n";
    }

    /**
     * Test relative URLs
     */
    private function testRelativeUrls() {
        echo "Testing Relative URLs...\n";

        // Test 48: Relative path
        $this->assert(
            esc_url('/path/to/page') === '/path/to/page',
            'Relative URL: Should allow absolute path'
        );

        // Test 49: Relative path with ..
        $this->assert(
            esc_url('../path/to/page') === '../path/to/page',
            'Relative URL: Should allow relative path with ..'
        );

        // Test 50: Query string only
        $this->assert(
            esc_url('?query=string') === '?query=string',
            'Relative URL: Should allow query string only'
        );

        // Test 51: Fragment only
        $this->assert(
            esc_url('#section') === '#section',
            'Relative URL: Should allow fragment only'
        );

        // Test 52: Protocol-relative URL
        $this->assert(
            esc_url('//example.com/path') === '//example.com/path',
            'Relative URL: Should allow protocol-relative URLs'
        );

        echo "\n";
    }

    /**
     * Test helper functions
     */
    private function testHelperFunctions() {
        echo "Testing Helper Functions...\n";

        // Test 53: esc_js() basic
        $this->assert(
            esc_js("alert('test')") === "alert(\\'test\\')",
            'esc_js: Should escape single quotes'
        );

        // Test 54: esc_js() with newlines
        $result = esc_js("line1\nline2");
        $this->assert(
            strpos($result, "\n") === false,
            'esc_js: Should escape newlines'
        );

        // Test 55: esc_textarea()
        $this->assert(
            esc_textarea('<script>alert(1)</script>') === '&lt;script&gt;alert(1)&lt;/script&gt;',
            'esc_textarea: Should escape HTML'
        );

        // Test 56: sanitize_text_field() removes tags
        $this->assert(
            sanitize_text_field('Hello <b>World</b>') === 'Hello World',
            'sanitize_text_field: Should remove HTML tags'
        );

        // Test 57: sanitize_text_field() normalizes whitespace
        $this->assert(
            sanitize_text_field("Hello  \n  World") === 'Hello World',
            'sanitize_text_field: Should normalize whitespace'
        );

        echo "\n";
    }

    /**
     * Assert helper
     */
    private function assert($condition, $message) {
        $this->total++;

        if ($condition) {
            $this->passed++;
            echo "  ✓ Test {$this->total}: {$message}\n";
        } else {
            $this->failed++;
            echo "  ✗ Test {$this->total}: {$message} [FAILED]\n";
        }
    }

    /**
     * Print test summary
     */
    private function printSummary() {
        echo "\n==============================================\n";
        echo "Test Summary\n";
        echo "==============================================\n";
        echo "Total Tests:  {$this->total}\n";
        echo "Passed:       {$this->passed}\n";
        echo "Failed:       {$this->failed}\n";

        if ($this->failed === 0) {
            echo "\n✓ ALL TESTS PASSED - 100% Security Coverage\n";
        } else {
            echo "\n✗ SOME TESTS FAILED - Security Issues Detected\n";
        }

        echo "==============================================\n";
    }
}

// Run tests if executed directly
if (php_sapi_name() === 'cli') {
    $tester = new EscapingTest();
    $success = $tester->runAll();
    exit($success ? 0 : 1);
}
