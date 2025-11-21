<?php
/**
 * Unit Tests for Content Display Functions
 *
 * Tests for get_the_content() and the_content() functions.
 * Verifies WordPress compatibility, content filtering, <!--more--> tag handling,
 * and multi-page post support.
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include the functions to test
require_once dirname(__DIR__) . '/functions/content-display.php';

/**
 * Test class for content display functions
 */
class TheContentTest {

  /**
   * Set up before each test
   */
  public function setUp() {
    // Reset all global variables
    global $wp_post, $page, $more, $preview, $pages, $multipage;
    $wp_post = null;
    $page = 1;
    $more = 0;
    $preview = false;
    $pages = null;
    $multipage = 0;
  }

  /**
   * Test 1: get_the_content() returns basic post content
   */
  public function test_get_the_content_basic() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 1,
      'post_title' => 'Test Post',
      'post_content' => 'This is the post content.',
    );

    $result = get_the_content();

    assert($result === 'This is the post content.', 'Should return basic post content');
    echo "✓ Test 1 passed: get_the_content() returns basic post content\n";
  }

  /**
   * Test 2: get_the_content() handles <!--more--> tag in list view ($more = 0)
   */
  public function test_get_the_content_more_tag_list_view() {
    global $wp_post, $more, $base_url;

    // Set up for list view
    $more = 0;
    $base_url = 'http://example.com';

    $wp_post = (object) array(
      'ID' => 2,
      'post_title' => 'Test Post with More Tag',
      'post_content' => 'This is the teaser.<!--more-->This is the rest of the content.',
    );

    $result = get_the_content();

    // Should contain teaser
    assert(strpos($result, 'This is the teaser.') !== false, 'Should contain teaser text');
    // Should NOT contain full content
    assert(strpos($result, 'This is the rest of the content.') === false, 'Should not contain full content in list view');
    // Should contain "Read More" link
    assert(strpos($result, 'more-link') !== false, 'Should contain more-link class');
    assert(strpos($result, '#more-2') !== false, 'Should contain anchor to more tag');

    echo "✓ Test 2 passed: get_the_content() handles <!--more--> tag in list view\n";
  }

  /**
   * Test 3: get_the_content() handles <!--more--> tag in single view ($more = 1)
   */
  public function test_get_the_content_more_tag_single_view() {
    global $wp_post, $more;

    // Set up for single post view
    $more = 1;

    $wp_post = (object) array(
      'ID' => 3,
      'post_title' => 'Test Post Single View',
      'post_content' => 'This is the teaser.<!--more-->This is the rest of the content.',
    );

    $result = get_the_content();

    // Should contain both teaser and full content
    assert(strpos($result, 'This is the teaser.') !== false, 'Should contain teaser text');
    assert(strpos($result, 'This is the rest of the content.') !== false, 'Should contain full content in single view');
    // Should contain more anchor span
    assert(strpos($result, '<span id="more-3"></span>') !== false, 'Should contain more anchor span');
    // Should NOT contain "Read More" link
    assert(strpos($result, 'more-link') === false, 'Should not contain more-link in single view');

    echo "✓ Test 3 passed: get_the_content() handles <!--more--> tag in single view\n";
  }

  /**
   * Test 4: get_the_content() handles custom more link text
   */
  public function test_get_the_content_custom_more_link() {
    global $wp_post, $more, $base_url;

    $more = 0;
    $base_url = 'http://example.com';

    $wp_post = (object) array(
      'ID' => 4,
      'post_title' => 'Custom More Link',
      'post_content' => 'Teaser content here.<!--more-->Full content here.',
    );

    $result = get_the_content('Read the full article');

    // Should contain custom more link text
    assert(strpos($result, 'Read the full article') !== false, 'Should contain custom more link text');

    echo "✓ Test 4 passed: get_the_content() handles custom more link text\n";
  }

  /**
   * Test 5: get_the_content() handles <!--more Custom Text--> inline text
   */
  public function test_get_the_content_more_tag_with_custom_text() {
    global $wp_post, $more, $base_url;

    $more = 0;
    $base_url = 'http://example.com';

    $wp_post = (object) array(
      'ID' => 5,
      'post_title' => 'Inline Custom More Text',
      'post_content' => 'Teaser text.<!--more Continue reading about this topic...-->Full text.',
    );

    $result = get_the_content();

    // Should use the custom text from the more tag itself
    assert(strpos($result, 'Continue reading about this topic...') !== false, 'Should use inline custom more text');

    echo "✓ Test 5 passed: get_the_content() handles <!--more--> tag with inline custom text\n";
  }

  /**
   * Test 6: get_the_content() handles multi-page posts with <!--nextpage-->
   */
  public function test_get_the_content_multipage_first_page() {
    global $wp_post, $page, $pages, $multipage;

    // Reset pages array for clean test
    $pages = null;
    $page = 1;

    $wp_post = (object) array(
      'ID' => 6,
      'post_title' => 'Multi-Page Post',
      'post_content' => 'Page 1 content.<!--nextpage-->Page 2 content.<!--nextpage-->Page 3 content.',
    );

    $result = get_the_content();

    // Should only contain page 1 content
    assert(strpos($result, 'Page 1 content.') !== false, 'Should contain page 1 content');
    assert(strpos($result, 'Page 2 content.') === false, 'Should not contain page 2 content on page 1');
    assert(strpos($result, 'Page 3 content.') === false, 'Should not contain page 3 content on page 1');

    // Check that pages array was set up correctly
    assert(is_array($pages), 'Should set up pages array');
    assert(count($pages) === 3, 'Should have 3 pages');
    assert($multipage === 1, 'Should set multipage flag');

    echo "✓ Test 6 passed: get_the_content() handles multi-page posts (page 1)\n";
  }

  /**
   * Test 7: get_the_content() handles multi-page posts - page 2
   */
  public function test_get_the_content_multipage_second_page() {
    global $wp_post, $page, $pages, $multipage;

    // Set up for page 2
    $pages = null;
    $page = 2;

    $wp_post = (object) array(
      'ID' => 7,
      'post_title' => 'Multi-Page Post',
      'post_content' => 'Page 1 content.<!--nextpage-->Page 2 content.<!--nextpage-->Page 3 content.',
    );

    $result = get_the_content();

    // Should only contain page 2 content
    assert(strpos($result, 'Page 1 content.') === false, 'Should not contain page 1 content on page 2');
    assert(strpos($result, 'Page 2 content.') !== false, 'Should contain page 2 content');
    assert(strpos($result, 'Page 3 content.') === false, 'Should not contain page 3 content on page 2');

    echo "✓ Test 7 passed: get_the_content() handles multi-page posts (page 2)\n";
  }

  /**
   * Test 8: get_the_content() handles invalid page number (too high)
   */
  public function test_get_the_content_invalid_page_number() {
    global $wp_post, $page, $pages;

    // Set up for page 99 (doesn't exist)
    $pages = null;
    $page = 99;

    $wp_post = (object) array(
      'ID' => 8,
      'post_title' => 'Multi-Page Post',
      'post_content' => 'Page 1 content.<!--nextpage-->Page 2 content.',
    );

    $result = get_the_content();

    // Should return the last available page (page 2)
    assert(strpos($result, 'Page 2 content.') !== false, 'Should return last available page when requested page is too high');

    echo "✓ Test 8 passed: get_the_content() handles invalid page number\n";
  }

  /**
   * Test 9: get_the_content() with strip_teaser parameter
   */
  public function test_get_the_content_strip_teaser() {
    global $wp_post, $more;

    // Set up for single post view with strip_teaser
    $more = 1;

    $wp_post = (object) array(
      'ID' => 9,
      'post_title' => 'Strip Teaser Test',
      'post_content' => 'This is the teaser.<!--more-->This is the main content.',
    );

    $result = get_the_content(null, true);

    // Should NOT contain teaser in single view with strip_teaser = true
    assert(strpos($result, 'This is the teaser.') === false, 'Should not contain teaser when strip_teaser is true');
    // Should contain main content
    assert(strpos($result, 'This is the main content.') !== false, 'Should contain main content');

    echo "✓ Test 9 passed: get_the_content() handles strip_teaser parameter\n";
  }

  /**
   * Test 10: get_the_content() handles <!--noteaser--> tag
   */
  public function test_get_the_content_noteaser_tag() {
    global $wp_post, $more;

    $more = 1;

    $wp_post = (object) array(
      'ID' => 10,
      'post_title' => 'No Teaser Tag Test',
      'post_content' => 'This is the teaser.<!--more--><!--noteaser-->This is the main content.',
    );

    $result = get_the_content();

    // Should NOT contain teaser when noteaser tag is present
    assert(strpos($result, 'This is the teaser.') === false, 'Should not contain teaser when noteaser tag is present');
    // Should contain main content
    assert(strpos($result, 'This is the main content.') !== false, 'Should contain main content');

    echo "✓ Test 10 passed: get_the_content() handles <!--noteaser--> tag\n";
  }

  /**
   * Test 11: the_content() applies 'the_content' filter
   */
  public function test_the_content_applies_filter() {
    global $wp_post;

    // Set up mock filter
    if (!function_exists('apply_filters')) {
      function apply_filters($hook, $value) {
        if ($hook === 'the_content') {
          return strtoupper($value);
        }
        return $value;
      }
    }

    $wp_post = (object) array(
      'ID' => 11,
      'post_title' => 'Filter Test',
      'post_content' => 'lowercase content',
    );

    ob_start();
    the_content();
    $result = ob_get_clean();

    // Should be uppercase (filtered)
    assert(strpos($result, 'LOWERCASE CONTENT') !== false, 'Should apply the_content filter');

    echo "✓ Test 11 passed: the_content() applies 'the_content' filter\n";
  }

  /**
   * Test 12: the_content() escapes ]]> for CDATA sections
   */
  public function test_the_content_escapes_cdata() {
    global $wp_post;

    $wp_post = (object) array(
      'ID' => 12,
      'post_title' => 'CDATA Test',
      'post_content' => 'Content with ]]> CDATA marker.',
    );

    ob_start();
    the_content();
    $result = ob_get_clean();

    // Should escape ]]> to ]]&gt;
    assert(strpos($result, ']]&gt;') !== false, 'Should escape ]]> for XML safety');
    assert(strpos($result, ']]>') === false, 'Should not contain unescaped ]]>');

    echo "✓ Test 12 passed: the_content() escapes ]]> for CDATA sections\n";
  }

  /**
   * Test 13: get_the_content() handles Backdrop-style node body (string)
   */
  public function test_get_the_content_backdrop_node_body_string() {
    global $wp_post;

    // Backdrop node with body as string
    $wp_post = (object) array(
      'nid' => 13,
      'title' => 'Backdrop Node',
      'body' => 'Backdrop node body content.',
    );

    $result = get_the_content();

    assert($result === 'Backdrop node body content.', 'Should handle Backdrop node with body as string');
    echo "✓ Test 13 passed: get_the_content() handles Backdrop-style node body (string)\n";
  }

  /**
   * Test 14: get_the_content() handles Backdrop-style node body (array)
   */
  public function test_get_the_content_backdrop_node_body_array() {
    global $wp_post;

    // Backdrop node with body as Field API array
    $wp_post = (object) array(
      'nid' => 14,
      'title' => 'Backdrop Node with Field',
      'body' => array(
        'und' => array(
          0 => array(
            'value' => 'Backdrop field API body content.',
            'format' => 'filtered_html',
          ),
        ),
      ),
    );

    $result = get_the_content();

    assert($result === 'Backdrop field API body content.', 'Should handle Backdrop node with Field API body array');
    echo "✓ Test 14 passed: get_the_content() handles Backdrop-style node body (Field API array)\n";
  }

  /**
   * Test 15: get_the_content() handles empty/missing post gracefully
   */
  public function test_get_the_content_missing_post() {
    global $wp_post;

    $wp_post = null;

    $result = get_the_content();

    assert($result === '', 'Should return empty string when no post exists');
    echo "✓ Test 15 passed: get_the_content() handles missing post gracefully\n";
  }

  /**
   * Test 16: Multi-page post with <!--more--> tag on first page
   */
  public function test_get_the_content_multipage_with_more_tag() {
    global $wp_post, $page, $more, $pages, $base_url;

    $pages = null;
    $page = 1;
    $more = 0;
    $base_url = 'http://example.com';

    $wp_post = (object) array(
      'ID' => 16,
      'post_title' => 'Multi-Page with More',
      'post_content' => 'Teaser on page 1.<!--more-->Rest of page 1.<!--nextpage-->Page 2 content.',
    );

    $result = get_the_content();

    // Should only show teaser with more link (because $more = 0)
    assert(strpos($result, 'Teaser on page 1.') !== false, 'Should contain teaser');
    assert(strpos($result, 'Rest of page 1.') === false, 'Should not show content after more tag when $more = 0');
    assert(strpos($result, 'more-link') !== false, 'Should contain more link');

    echo "✓ Test 16 passed: get_the_content() handles multi-page post with <!--more--> tag\n";
  }

  /**
   * Run all tests
   */
  public function runAll() {
    echo "\n=== Running the_content() Tests ===\n\n";

    $this->setUp();
    $this->test_get_the_content_basic();

    $this->setUp();
    $this->test_get_the_content_more_tag_list_view();

    $this->setUp();
    $this->test_get_the_content_more_tag_single_view();

    $this->setUp();
    $this->test_get_the_content_custom_more_link();

    $this->setUp();
    $this->test_get_the_content_more_tag_with_custom_text();

    $this->setUp();
    $this->test_get_the_content_multipage_first_page();

    $this->setUp();
    $this->test_get_the_content_multipage_second_page();

    $this->setUp();
    $this->test_get_the_content_invalid_page_number();

    $this->setUp();
    $this->test_get_the_content_strip_teaser();

    $this->setUp();
    $this->test_get_the_content_noteaser_tag();

    $this->setUp();
    $this->test_the_content_applies_filter();

    $this->setUp();
    $this->test_the_content_escapes_cdata();

    $this->setUp();
    $this->test_get_the_content_backdrop_node_body_string();

    $this->setUp();
    $this->test_get_the_content_backdrop_node_body_array();

    $this->setUp();
    $this->test_get_the_content_missing_post();

    $this->setUp();
    $this->test_get_the_content_multipage_with_more_tag();

    echo "\n=== All Tests Passed! ===\n";
  }
}

// Mock functions for testing if they don't exist
if (!function_exists('url')) {
  function url($path, $options = array()) {
    global $base_url;
    $base = isset($base_url) ? $base_url : 'http://localhost';
    return rtrim($base, '/') . '/' . ltrim($path, '/');
  }
}

if (!function_exists('backdrop_get_path_alias')) {
  function backdrop_get_path_alias($path) {
    return $path; // Simple mock - just return the original path
  }
}

// Run the tests
$tester = new TheContentTest();
$tester->runAll();
