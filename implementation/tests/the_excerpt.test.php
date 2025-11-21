<?php
/**
 * Unit Tests for Excerpt Display Functions
 *
 * Tests for get_the_excerpt(), the_excerpt(), wp_trim_excerpt(),
 * wp_trim_words(), and wp_strip_all_tags() functions.
 * Verifies WordPress compatibility and Backdrop integration.
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include the functions to test
require_once dirname(__DIR__) . '/functions/content-display.php';

/**
 * Test class for excerpt display functions
 */
class ExcerptFunctionsTest {

  /**
   * Set up before each test
   */
  public function setUp() {
    // Reset global post
    global $wp_post;
    $wp_post = null;
  }

  /**
   * Test 1: get_the_excerpt() returns custom excerpt when post_excerpt is set
   */
  public function test_get_the_excerpt_with_custom_excerpt() {
    global $wp_post;

    // Create WordPress-style post object with custom excerpt
    $wp_post = (object) array(
      'ID' => 123,
      'post_title' => 'Test Post',
      'post_excerpt' => 'This is a custom excerpt.',
      'post_content' => 'This is the full content of the post.',
    );

    $result = get_the_excerpt();

    assert($result === 'This is a custom excerpt.', 'Should return custom excerpt when post_excerpt is set');
    echo "✓ Test 1 passed: get_the_excerpt() returns custom excerpt\n";
  }

  /**
   * Test 2: get_the_excerpt() returns empty string when no excerpt and no filter
   */
  public function test_get_the_excerpt_with_no_excerpt() {
    global $wp_post;

    // Create post without excerpt
    $wp_post = (object) array(
      'ID' => 456,
      'post_title' => 'Test Post',
      'post_excerpt' => '',
      'post_content' => 'This is the full content.',
    );

    // Without apply_filters, should return empty string
    $result = get_the_excerpt();

    assert($result === '', 'Should return empty string when no excerpt and no filter');
    echo "✓ Test 2 passed: get_the_excerpt() returns empty string without filter\n";
  }

  /**
   * Test 3: get_the_excerpt() with Backdrop-style node object
   */
  public function test_get_the_excerpt_with_backdrop_node() {
    global $wp_post;

    // Create Backdrop-style node object with body summary
    $wp_post = (object) array(
      'nid' => 789,
      'title' => 'Test Node',
      'body' => array(
        'summary' => 'Backdrop body summary text.',
        'value' => 'Full body content here.',
      ),
    );

    $result = get_the_excerpt();

    assert($result === 'Backdrop body summary text.', 'Should return Backdrop body summary');
    echo "✓ Test 3 passed: get_the_excerpt() works with Backdrop node\n";
  }

  /**
   * Test 4: get_the_excerpt() handles missing post gracefully
   */
  public function test_get_the_excerpt_with_null_post() {
    global $wp_post;
    $wp_post = null;

    $result = get_the_excerpt();

    assert($result === '', 'Should return empty string when no post exists');
    echo "✓ Test 4 passed: get_the_excerpt() handles null post gracefully\n";
  }

  /**
   * Test 5: the_excerpt() echoes the excerpt
   */
  public function test_the_excerpt_echoes_content() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 111,
      'post_title' => 'Test Post',
      'post_excerpt' => 'Excerpt to be echoed.',
    );

    // Capture output
    ob_start();
    the_excerpt();
    $output = ob_get_clean();

    assert($output === 'Excerpt to be echoed.', 'Should echo the excerpt');
    echo "✓ Test 5 passed: the_excerpt() echoes content\n";
  }

  /**
   * Test 6: wp_trim_excerpt() auto-generates excerpt from content
   */
  public function test_wp_trim_excerpt_auto_generates() {
    global $wp_post;

    // Create post with content but no excerpt
    $wp_post = (object) array(
      'ID' => 222,
      'post_title' => 'Test Post',
      'post_excerpt' => '',
      'post_content' => 'This is a long piece of content that will be automatically trimmed to create an excerpt. It has many words and should be cut off at the appropriate length.',
    );

    // Call wp_trim_excerpt with empty string to trigger auto-generation
    $result = wp_trim_excerpt('');

    // Should return trimmed content
    assert(strpos($result, 'This is a long piece of content') !== false, 'Should contain beginning of content');
    assert(strlen($result) < strlen($wp_post->post_content), 'Should be shorter than original content');
    echo "✓ Test 6 passed: wp_trim_excerpt() auto-generates from content\n";
  }

  /**
   * Test 7: wp_trim_excerpt() preserves custom excerpt
   */
  public function test_wp_trim_excerpt_preserves_custom() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 333,
      'post_title' => 'Test Post',
      'post_excerpt' => '',
      'post_content' => 'Long content here.',
    );

    $custom_excerpt = 'My custom excerpt text.';
    $result = wp_trim_excerpt($custom_excerpt);

    assert($result === $custom_excerpt, 'Should preserve custom excerpt when provided');
    echo "✓ Test 7 passed: wp_trim_excerpt() preserves custom excerpt\n";
  }

  /**
   * Test 8: wp_trim_words() trims to specified word count
   */
  public function test_wp_trim_words_limits_words() {
    $text = 'One two three four five six seven eight nine ten eleven twelve';
    $result = wp_trim_words($text, 5, '...');

    assert($result === 'One two three four five...', 'Should trim to 5 words and add ellipsis');
    echo "✓ Test 8 passed: wp_trim_words() trims to specified word count\n";
  }

  /**
   * Test 9: wp_trim_words() does not add ellipsis if under limit
   */
  public function test_wp_trim_words_no_ellipsis_when_short() {
    $text = 'One two three';
    $result = wp_trim_words($text, 10, '...');

    assert($result === 'One two three', 'Should not add ellipsis when text is shorter than limit');
    echo "✓ Test 9 passed: wp_trim_words() no ellipsis when under limit\n";
  }

  /**
   * Test 10: wp_trim_words() strips HTML tags
   */
  public function test_wp_trim_words_strips_html() {
    $text = '<p>One <strong>two</strong> three <em>four</em> five</p>';
    $result = wp_trim_words($text, 3, '...');

    assert($result === 'One two three...', 'Should strip HTML tags');
    assert(strpos($result, '<') === false, 'Should not contain any HTML tags');
    echo "✓ Test 10 passed: wp_trim_words() strips HTML tags\n";
  }

  /**
   * Test 11: wp_strip_all_tags() removes all HTML tags
   */
  public function test_wp_strip_all_tags_basic() {
    $text = '<p>Hello <strong>World</strong>!</p>';
    $result = wp_strip_all_tags($text);

    assert($result === 'Hello World!', 'Should strip all HTML tags');
    echo "✓ Test 11 passed: wp_strip_all_tags() removes basic tags\n";
  }

  /**
   * Test 12: wp_strip_all_tags() removes script and style tags with content
   */
  public function test_wp_strip_all_tags_removes_scripts() {
    $text = '<p>Hello</p><script>alert("bad");</script><p>World</p>';
    $result = wp_strip_all_tags($text);

    assert($result === 'HelloWorld', 'Should remove script tags and their content');
    assert(strpos($result, 'alert') === false, 'Should not contain script content');
    echo "✓ Test 12 passed: wp_strip_all_tags() removes scripts\n";
  }

  /**
   * Test 13: wp_strip_all_tags() with remove_breaks option
   */
  public function test_wp_strip_all_tags_remove_breaks() {
    $text = "Hello\n\nWorld\t\tTest";
    $result = wp_strip_all_tags($text, true);

    assert($result === 'Hello World Test', 'Should normalize whitespace when remove_breaks is true');
    echo "✓ Test 13 passed: wp_strip_all_tags() removes breaks\n";
  }

  /**
   * Test 14: Auto-generated excerpt uses default 55 words
   */
  public function test_excerpt_default_word_count() {
    global $wp_post;

    // Create content with exactly 60 words
    $words = array();
    for ($i = 1; $i <= 60; $i++) {
      $words[] = "word{$i}";
    }
    $content = implode(' ', $words);

    $wp_post = (object) array(
      'ID' => 444,
      'post_excerpt' => '',
      'post_content' => $content,
    );

    $result = wp_trim_excerpt('');

    // Count words in result (excluding the ellipsis)
    $result_without_ellipsis = str_replace('&hellip;', '', $result);
    $result_words = preg_split("/[\n\r\t ]+/", trim($result_without_ellipsis), -1, PREG_SPLIT_NO_EMPTY);

    assert(count($result_words) === 55, 'Should trim to exactly 55 words by default');
    assert(strpos($result, '&hellip;') !== false, 'Should add ellipsis when trimmed');
    echo "✓ Test 14 passed: Auto-generated excerpt uses 55 words\n";
  }

  /**
   * Test 15: get_the_excerpt() applies 'get_the_excerpt' filter
   */
  public function test_get_the_excerpt_applies_filter() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 555,
      'post_excerpt' => 'Original excerpt',
      'post_content' => 'Some content',
    );

    // Mock apply_filters to uppercase the excerpt
    if (!function_exists('apply_filters')) {
      function apply_filters($hook, $value, $post = null) {
        if ($hook === 'get_the_excerpt') {
          return strtoupper($value);
        }
        return $value;
      }
    }

    $result = get_the_excerpt();

    assert($result === 'ORIGINAL EXCERPT', 'Should apply get_the_excerpt filter');
    echo "✓ Test 15 passed: get_the_excerpt() applies filter\n";
  }

  /**
   * Test 16: Excerpt from content with HTML tags
   */
  public function test_excerpt_strips_html_from_content() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 666,
      'post_excerpt' => '',
      'post_content' => '<p>This is <strong>HTML</strong> content with <em>tags</em> that should be <a href="#">removed</a>.</p>',
    );

    $result = wp_trim_excerpt('');

    assert(strpos($result, '<') === false, 'Should not contain HTML tags');
    assert(strpos($result, 'This is HTML content') !== false, 'Should contain the text without tags');
    echo "✓ Test 16 passed: Excerpt strips HTML from content\n";
  }

  /**
   * Test 17: Excerpt from Backdrop node body value
   */
  public function test_excerpt_from_backdrop_body_value() {
    global $wp_post;

    $wp_post = (object) array(
      'nid' => 777,
      'title' => 'Backdrop Node',
      'body' => array(
        'value' => 'This is the Backdrop body value content that should be used for auto-generating an excerpt.',
      ),
    );

    $result = wp_trim_excerpt('');

    assert(strpos($result, 'This is the Backdrop body value') !== false, 'Should generate excerpt from Backdrop body value');
    echo "✓ Test 17 passed: Excerpt from Backdrop body value\n";
  }

  /**
   * Test 18: wp_trim_words() with custom "more" text
   */
  public function test_wp_trim_words_custom_more() {
    $text = 'One two three four five six seven eight';
    $result = wp_trim_words($text, 3, ' [Read More]');

    assert($result === 'One two three [Read More]', 'Should use custom "more" text');
    echo "✓ Test 18 passed: wp_trim_words() uses custom more text\n";
  }

  /**
   * Run all tests
   */
  public function runAllTests() {
    echo "\n=== Running Excerpt Functions Tests ===\n\n";

    $this->setUp();
    $this->test_get_the_excerpt_with_custom_excerpt();

    $this->setUp();
    $this->test_get_the_excerpt_with_no_excerpt();

    $this->setUp();
    $this->test_get_the_excerpt_with_backdrop_node();

    $this->setUp();
    $this->test_get_the_excerpt_with_null_post();

    $this->setUp();
    $this->test_the_excerpt_echoes_content();

    $this->setUp();
    $this->test_wp_trim_excerpt_auto_generates();

    $this->setUp();
    $this->test_wp_trim_excerpt_preserves_custom();

    $this->setUp();
    $this->test_wp_trim_words_limits_words();

    $this->setUp();
    $this->test_wp_trim_words_no_ellipsis_when_short();

    $this->setUp();
    $this->test_wp_trim_words_strips_html();

    $this->setUp();
    $this->test_wp_strip_all_tags_basic();

    $this->setUp();
    $this->test_wp_strip_all_tags_removes_scripts();

    $this->setUp();
    $this->test_wp_strip_all_tags_remove_breaks();

    $this->setUp();
    $this->test_excerpt_default_word_count();

    $this->setUp();
    $this->test_get_the_excerpt_applies_filter();

    $this->setUp();
    $this->test_excerpt_strips_html_from_content();

    $this->setUp();
    $this->test_excerpt_from_backdrop_body_value();

    $this->setUp();
    $this->test_wp_trim_words_custom_more();

    echo "\n=== All 18 Tests Passed! ===\n\n";
  }
}

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli' && isset($argv[0]) && basename($argv[0]) === basename(__FILE__)) {
  $tester = new ExcerptFunctionsTest();
  $tester->runAllTests();
}
