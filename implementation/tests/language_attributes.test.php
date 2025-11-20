<?php
/**
 * Unit Tests for Language Attributes Functions
 *
 * Tests for get_language_attributes() and language_attributes() functions.
 * Verifies WordPress compatibility and Backdrop integration.
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include the functions to test
require_once dirname(__DIR__) . '/functions/content-display.php';

/**
 * Test class for language attributes functions
 */
class LanguageAttributesTest {

  /**
   * Set up before each test
   */
  public function setUp() {
    // Reset global language
    global $language;
    $language = null;
  }

  /**
   * Test 1: get_language_attributes() returns default language attributes
   */
  public function test_get_language_attributes_default() {
    $result = get_language_attributes();

    assert(strpos($result, 'lang="en-US"') !== false, 'Should include default lang attribute');
    echo "✓ Test 1 passed: get_language_attributes() returns default language attributes\n";
  }

  /**
   * Test 2: get_language_attributes() with Backdrop language object (LTR)
   */
  public function test_get_language_attributes_backdrop_ltr() {
    global $language;

    // Create Backdrop-style language object
    $language = (object) array(
      'langcode' => 'en',
      'direction' => 0, // LANGUAGE_LTR
    );

    $result = get_language_attributes();

    assert(strpos($result, 'lang="en"') !== false, 'Should include lang attribute from Backdrop');
    assert(strpos($result, 'dir=') === false, 'Should not include dir attribute for LTR (default)');
    echo "✓ Test 2 passed: get_language_attributes() with Backdrop language object (LTR)\n";
  }

  /**
   * Test 3: get_language_attributes() with Backdrop language object (RTL)
   */
  public function test_get_language_attributes_backdrop_rtl() {
    global $language;

    // Create Backdrop-style language object with RTL
    $language = (object) array(
      'langcode' => 'ar',
      'direction' => 1, // LANGUAGE_RTL
    );

    $result = get_language_attributes();

    assert(strpos($result, 'lang="ar"') !== false, 'Should include lang attribute');
    assert(strpos($result, 'dir="rtl"') !== false, 'Should include RTL dir attribute');
    echo "✓ Test 3 passed: get_language_attributes() with Backdrop language object (RTL)\n";
  }

  /**
   * Test 4: get_language_attributes() with alternative language property
   */
  public function test_get_language_attributes_alt_property() {
    global $language;

    // Create Backdrop-style language object using 'language' property
    $language = (object) array(
      'language' => 'fr',
      'direction' => 0,
    );

    $result = get_language_attributes();

    assert(strpos($result, 'lang="fr"') !== false, 'Should include lang attribute from language property');
    echo "✓ Test 4 passed: get_language_attributes() with alternative language property\n";
  }

  /**
   * Test 5: get_language_attributes() with get_bloginfo() mock
   */
  public function test_get_language_attributes_bloginfo() {
    // Mock get_bloginfo function
    if (!function_exists('get_bloginfo')) {
      function get_bloginfo($show) {
        if ($show === 'language') {
          return 'es-ES';
        }
        return '';
      }
    }

    $result = get_language_attributes();

    assert(strpos($result, 'lang="es-ES"') !== false, 'Should include lang attribute from get_bloginfo');
    echo "✓ Test 5 passed: get_language_attributes() with get_bloginfo() mock\n";
  }

  /**
   * Test 6: get_language_attributes() HTML5 doctype (default)
   */
  public function test_get_language_attributes_html5() {
    $result = get_language_attributes('html');

    assert(strpos($result, 'lang=') !== false, 'Should include lang attribute for HTML5');
    assert(strpos($result, 'xml:lang=') === false, 'Should not include xml:lang for HTML5');
    echo "✓ Test 6 passed: get_language_attributes() HTML5 doctype (default)\n";
  }

  /**
   * Test 7: get_language_attributes() XHTML doctype
   */
  public function test_get_language_attributes_xhtml() {
    global $language;
    $language = (object) array(
      'langcode' => 'de',
      'direction' => 0,
    );

    $result = get_language_attributes('xhtml');

    assert(strpos($result, 'xml:lang="de"') !== false, 'Should include xml:lang attribute for XHTML');
    echo "✓ Test 7 passed: get_language_attributes() XHTML doctype\n";
  }

  /**
   * Test 8: language_attributes() echoes output
   */
  public function test_language_attributes_echo() {
    global $language;
    $language = (object) array(
      'langcode' => 'ja',
      'direction' => 0,
    );

    // Capture output
    ob_start();
    language_attributes();
    $output = ob_get_clean();

    assert(strpos($output, 'lang="ja"') !== false, 'Should echo lang attribute');
    echo "✓ Test 8 passed: language_attributes() echoes output\n";
  }

  /**
   * Test 9: get_language_attributes() applies 'language_attributes' filter
   */
  public function test_get_language_attributes_applies_filter() {
    global $language;
    $language = (object) array(
      'langcode' => 'it',
      'direction' => 0,
    );

    // Mock apply_filters function to modify output
    if (!function_exists('apply_filters')) {
      function apply_filters($hook, $value, $doctype = null) {
        if ($hook === 'language_attributes') {
          // Add a custom class attribute via filter
          return $value . ' class="custom-html"';
        }
        return $value;
      }
    }

    $result = get_language_attributes();

    assert(strpos($result, 'class="custom-html"') !== false, 'Should apply language_attributes filter');
    echo "✓ Test 9 passed: get_language_attributes() applies 'language_attributes' filter\n";
  }

  /**
   * Test 10: get_language_attributes() handles Hebrew (RTL language)
   */
  public function test_get_language_attributes_hebrew_rtl() {
    global $language;

    // Create language object for Hebrew
    $language = (object) array(
      'langcode' => 'he',
      'direction' => 1, // RTL
    );

    $result = get_language_attributes();

    assert(strpos($result, 'dir="rtl"') !== false, 'Should include RTL for Hebrew');
    assert(strpos($result, 'lang="he"') !== false, 'Should include Hebrew language code');

    // Check order: dir should come before lang for proper HTML structure
    $dir_pos = strpos($result, 'dir="rtl"');
    $lang_pos = strpos($result, 'lang="he"');
    assert($dir_pos < $lang_pos, 'dir attribute should come before lang attribute');

    echo "✓ Test 10 passed: get_language_attributes() handles Hebrew (RTL language)\n";
  }

  /**
   * Test 11: get_language_attributes() escapes language code
   */
  public function test_get_language_attributes_escapes_lang() {
    global $language;

    // Create language object with potentially unsafe characters
    $language = (object) array(
      'langcode' => 'en"onclick="alert(1)"',
      'direction' => 0,
    );

    $result = get_language_attributes();

    // The esc_attr function should escape the quotes
    assert(strpos($result, 'onclick=') === false, 'Should escape malicious code');
    assert(strpos($result, '&quot;') !== false || strpos($result, '&#') !== false, 'Should contain escaped quotes');
    echo "✓ Test 11 passed: get_language_attributes() escapes language code\n";
  }

  /**
   * Test 12: get_language_attributes() with is_rtl() function
   */
  public function test_get_language_attributes_is_rtl_function() {
    // Reset globals
    global $language;
    $language = null;

    // Mock is_rtl() function
    if (!function_exists('is_rtl')) {
      function is_rtl() {
        return true;
      }
    }

    $result = get_language_attributes();

    assert(strpos($result, 'dir="rtl"') !== false, 'Should detect RTL via is_rtl() function');
    echo "✓ Test 12 passed: get_language_attributes() with is_rtl() function\n";
  }

  /**
   * Test 13: language_attributes() integration test
   */
  public function test_language_attributes_integration() {
    global $language;

    // Simulate a typical Backdrop setup
    $language = (object) array(
      'langcode' => 'en-GB',
      'name' => 'English (British)',
      'direction' => 0,
    );

    // Capture output
    ob_start();
    language_attributes();
    $output = ob_get_clean();

    // Verify complete output
    assert(strpos($output, 'lang="en-GB"') !== false, 'Should contain British English language code');
    assert(!empty($output), 'Should produce non-empty output');
    echo "✓ Test 13 passed: language_attributes() integration test\n";
  }

  /**
   * Test 14: get_language_attributes() with empty language object
   */
  public function test_get_language_attributes_empty_language() {
    global $language;

    // Create empty language object
    $language = (object) array();

    $result = get_language_attributes();

    // Should fall back to default
    assert(strpos($result, 'lang=') !== false, 'Should include lang attribute even with empty object');
    echo "✓ Test 14 passed: get_language_attributes() with empty language object\n";
  }

  /**
   * Test 15: get_language_attributes() complete RTL example (Arabic)
   */
  public function test_get_language_attributes_complete_rtl() {
    global $language;

    // Complete RTL setup (Arabic)
    $language = (object) array(
      'langcode' => 'ar-SA',
      'direction' => 1,
    );

    ob_start();
    language_attributes();
    $output = ob_get_clean();

    // Expected output: dir="rtl" lang="ar-SA"
    assert(strpos($output, 'dir="rtl"') !== false, 'Should have RTL direction');
    assert(strpos($output, 'lang="ar-SA"') !== false, 'Should have Arabic (Saudi Arabia) language');

    // Verify it would work in actual HTML
    $html = '<html ' . $output . '>';
    assert(strpos($html, '<html dir="rtl" lang="ar-SA">') !== false, 'Should produce valid HTML tag');

    echo "✓ Test 15 passed: get_language_attributes() complete RTL example (Arabic)\n";
  }

  /**
   * Run all tests
   */
  public function runAllTests() {
    echo "\n=== Running Language Attributes Functions Tests ===\n\n";

    $this->setUp();
    $this->test_get_language_attributes_default();

    $this->setUp();
    $this->test_get_language_attributes_backdrop_ltr();

    $this->setUp();
    $this->test_get_language_attributes_backdrop_rtl();

    $this->setUp();
    $this->test_get_language_attributes_alt_property();

    $this->setUp();
    $this->test_get_language_attributes_bloginfo();

    $this->setUp();
    $this->test_get_language_attributes_html5();

    $this->setUp();
    $this->test_get_language_attributes_xhtml();

    $this->setUp();
    $this->test_language_attributes_echo();

    $this->setUp();
    $this->test_get_language_attributes_applies_filter();

    $this->setUp();
    $this->test_get_language_attributes_hebrew_rtl();

    $this->setUp();
    $this->test_get_language_attributes_escapes_lang();

    $this->setUp();
    $this->test_get_language_attributes_is_rtl_function();

    $this->setUp();
    $this->test_language_attributes_integration();

    $this->setUp();
    $this->test_get_language_attributes_empty_language();

    $this->setUp();
    $this->test_get_language_attributes_complete_rtl();

    echo "\n=== All Tests Passed! ===\n\n";
  }
}

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli' && isset($argv[0]) && basename($argv[0]) === basename(__FILE__)) {
  $tester = new LanguageAttributesTest();
  $tester->runAllTests();
}
