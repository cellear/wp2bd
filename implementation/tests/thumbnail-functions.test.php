<?php
/**
 * Unit Tests for Post Thumbnail Functions
 *
 * Tests for has_post_thumbnail(), get_the_post_thumbnail(), and the_post_thumbnail().
 * Verifies WordPress compatibility and Backdrop image field integration.
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include the functions to test
require_once dirname(__DIR__) . '/functions/content-display.php';

/**
 * Test class for post thumbnail functions
 */
class ThumbnailFunctionsTest {

  /**
   * Set up before each test
   */
  public function setUp() {
    // Reset global post
    global $wp_post;
    $wp_post = null;
  }

  /**
   * Test 1: has_post_thumbnail() returns false when no image field exists
   */
  public function test_has_post_thumbnail_no_image() {
    global $wp_post;

    // Create post object without image field
    $wp_post = (object) array(
      'ID' => 123,
      'post_title' => 'Post Without Image',
      'post_content' => 'This post has no featured image.',
    );

    $result = has_post_thumbnail();

    assert($result === false, 'Should return false when no image field exists');
    echo "✓ Test 1 passed: has_post_thumbnail() returns false when no image field exists\n";
  }

  /**
   * Test 2: has_post_thumbnail() returns true with Backdrop Field API structure
   */
  public function test_has_post_thumbnail_with_backdrop_field_api() {
    global $wp_post;

    // Create Backdrop-style node with image field (Field API structure)
    $wp_post = (object) array(
      'nid' => 456,
      'title' => 'Post With Image',
      'field_image' => array(
        'und' => array(
          0 => array(
            'fid' => 789,
            'uri' => 'public://images/sample.jpg',
            'alt' => 'Sample image',
            'title' => 'Sample Title',
            'width' => 800,
            'height' => 600,
          ),
        ),
      ),
    );

    $result = has_post_thumbnail();

    assert($result === true, 'Should return true with Backdrop Field API image structure');
    echo "✓ Test 2 passed: has_post_thumbnail() returns true with Backdrop Field API structure\n";
  }

  /**
   * Test 3: has_post_thumbnail() returns true with simplified array structure
   */
  public function test_has_post_thumbnail_with_simplified_array() {
    global $wp_post;

    // Create node with simplified image field structure
    $wp_post = (object) array(
      'ID' => 111,
      'post_title' => 'Post With Simplified Image',
      'field_image' => array(
        0 => array(
          'fid' => 222,
          'uri' => 'public://images/test.png',
          'alt' => 'Test image',
        ),
      ),
    );

    $result = has_post_thumbnail();

    assert($result === true, 'Should return true with simplified array structure');
    echo "✓ Test 3 passed: has_post_thumbnail() returns true with simplified array structure\n";
  }

  /**
   * Test 4: has_post_thumbnail() handles post parameter
   */
  public function test_has_post_thumbnail_with_parameter() {
    // Create a post object to pass as parameter
    $post = (object) array(
      'ID' => 333,
      'field_image' => array(
        'und' => array(
          0 => array(
            'uri' => 'public://images/param.jpg',
          ),
        ),
      ),
    );

    $result = has_post_thumbnail($post);

    assert($result === true, 'Should accept post parameter and detect thumbnail');
    echo "✓ Test 4 passed: has_post_thumbnail() handles post parameter\n";
  }

  /**
   * Test 5: get_the_post_thumbnail() returns empty string when no thumbnail
   */
  public function test_get_the_post_thumbnail_no_image() {
    global $wp_post;

    // Create post without thumbnail
    $wp_post = (object) array(
      'ID' => 444,
      'post_title' => 'No Thumbnail Post',
    );

    $result = get_the_post_thumbnail();

    assert($result === '', 'Should return empty string when no thumbnail exists');
    echo "✓ Test 5 passed: get_the_post_thumbnail() returns empty string when no thumbnail\n";
  }

  /**
   * Test 6: get_the_post_thumbnail() generates HTML with default size
   */
  public function test_get_the_post_thumbnail_default_size() {
    global $wp_post;

    // Create post with image
    $wp_post = (object) array(
      'ID' => 555,
      'post_title' => 'Post With Thumbnail',
      'field_image' => array(
        'und' => array(
          0 => array(
            'fid' => 666,
            'uri' => 'public://images/featured.jpg',
            'alt' => 'Featured image',
            'width' => 800,
            'height' => 600,
          ),
        ),
      ),
    );

    $html = get_the_post_thumbnail();

    // Verify HTML structure
    assert(strpos($html, '<img') === 0, 'Should start with <img tag');
    assert(strpos($html, 'alt="Featured image"') !== false, 'Should include alt text');
    assert(strpos($html, 'class=') !== false, 'Should include class attribute');
    assert(strpos($html, 'wp-post-image') !== false, 'Should include wp-post-image class');
    assert(strpos($html, 'loading="lazy"') !== false, 'Should include lazy loading');

    echo "✓ Test 6 passed: get_the_post_thumbnail() generates HTML with default size\n";
  }

  /**
   * Test 7: get_the_post_thumbnail() supports different image sizes
   */
  public function test_get_the_post_thumbnail_custom_size() {
    global $wp_post;

    // Create post with image
    $wp_post = (object) array(
      'ID' => 777,
      'field_image' => array(
        'und' => array(
          0 => array(
            'uri' => 'public://images/thumbnail-test.jpg',
            'alt' => 'Thumbnail test',
          ),
        ),
      ),
    );

    // Test thumbnail size
    $html_thumb = get_the_post_thumbnail(null, 'thumbnail');
    assert(strpos($html_thumb, 'attachment-thumbnail') !== false, 'Should include thumbnail size class');

    // Test large size
    $html_large = get_the_post_thumbnail(null, 'large');
    assert(strpos($html_large, 'attachment-large') !== false, 'Should include large size class');

    // Test full size
    $html_full = get_the_post_thumbnail(null, 'full');
    assert(strpos($html_full, 'attachment-full') !== false, 'Should include full size class');

    echo "✓ Test 7 passed: get_the_post_thumbnail() supports different image sizes\n";
  }

  /**
   * Test 8: get_the_post_thumbnail() merges custom attributes
   */
  public function test_get_the_post_thumbnail_custom_attributes() {
    global $wp_post;

    // Create post with image
    $wp_post = (object) array(
      'ID' => 888,
      'field_image' => array(
        'und' => array(
          0 => array(
            'uri' => 'public://images/custom.jpg',
            'alt' => 'Custom image',
          ),
        ),
      ),
    );

    // Test with array attributes
    $custom_attr = array(
      'id' => 'my-featured-image',
      'data-test' => 'value',
    );
    $html = get_the_post_thumbnail(null, 'medium', $custom_attr);

    assert(strpos($html, 'id="my-featured-image"') !== false, 'Should include custom id attribute');
    assert(strpos($html, 'data-test="value"') !== false, 'Should include custom data attribute');

    echo "✓ Test 8 passed: get_the_post_thumbnail() merges custom attributes\n";
  }

  /**
   * Test 9: the_post_thumbnail() echoes HTML output
   */
  public function test_the_post_thumbnail_echoes_output() {
    global $wp_post;

    // Create post with image
    $wp_post = (object) array(
      'ID' => 999,
      'field_image' => array(
        'und' => array(
          0 => array(
            'uri' => 'public://images/echo-test.jpg',
            'alt' => 'Echo test',
          ),
        ),
      ),
    );

    // Capture output
    ob_start();
    the_post_thumbnail();
    $output = ob_get_clean();

    assert(strpos($output, '<img') === 0, 'Should echo img tag');
    assert(strpos($output, 'alt="Echo test"') !== false, 'Should include alt text in output');

    echo "✓ Test 9 passed: the_post_thumbnail() echoes HTML output\n";
  }

  /**
   * Test 10: the_post_thumbnail() supports size parameter
   */
  public function test_the_post_thumbnail_with_size() {
    global $wp_post;

    // Create post with image
    $wp_post = (object) array(
      'ID' => 1010,
      'field_image' => array(
        'und' => array(
          0 => array(
            'uri' => 'public://images/size-test.jpg',
            'alt' => 'Size test',
          ),
        ),
      ),
    );

    // Capture output with thumbnail size
    ob_start();
    the_post_thumbnail('thumbnail');
    $output = ob_get_clean();

    assert(strpos($output, 'attachment-thumbnail') !== false, 'Should apply thumbnail size class');

    echo "✓ Test 10 passed: the_post_thumbnail() supports size parameter\n";
  }

  /**
   * Test 11: Helper function _wp2bd_get_thumbnail_data() extracts data correctly
   */
  public function test_get_thumbnail_data_helper() {
    // Create post with full image data
    $post = (object) array(
      'ID' => 1111,
      'field_image' => array(
        'und' => array(
          0 => array(
            'fid' => 1212,
            'uri' => 'public://images/helper-test.jpg',
            'alt' => 'Helper test alt',
            'title' => 'Helper test title',
            'width' => 1024,
            'height' => 768,
          ),
        ),
      ),
    );

    $data = _wp2bd_get_thumbnail_data($post);

    assert($data !== false, 'Should return image data array');
    assert($data['uri'] === 'public://images/helper-test.jpg', 'Should extract URI');
    assert($data['fid'] === 1212, 'Should extract file ID');
    assert($data['alt'] === 'Helper test alt', 'Should extract alt text');
    assert($data['title'] === 'Helper test title', 'Should extract title');
    assert($data['width'] === 1024, 'Should extract width');
    assert($data['height'] === 768, 'Should extract height');

    echo "✓ Test 11 passed: _wp2bd_get_thumbnail_data() extracts data correctly\n";
  }

  /**
   * Test 12: has_post_thumbnail() returns false for null post
   */
  public function test_has_post_thumbnail_null_post() {
    global $wp_post;
    $wp_post = null;

    $result = has_post_thumbnail();

    assert($result === false, 'Should return false when post is null');
    echo "✓ Test 12 passed: has_post_thumbnail() returns false for null post\n";
  }

  /**
   * Test 13: get_the_post_thumbnail() handles string URI format
   */
  public function test_get_the_post_thumbnail_string_uri() {
    global $wp_post;

    // Create post with simple string URI (legacy format)
    $wp_post = (object) array(
      'ID' => 1313,
      'field_image' => 'public://images/string-uri.jpg',
    );

    $html = get_the_post_thumbnail();

    assert(strpos($html, '<img') === 0, 'Should generate img tag from string URI');
    assert(strlen($html) > 10, 'Should generate non-empty HTML');

    echo "✓ Test 13 passed: get_the_post_thumbnail() handles string URI format\n";
  }

  /**
   * Test 14: Helper function _wp2bd_get_image_style_width() returns correct widths
   */
  public function test_get_image_style_width() {
    $thumbnail_width = _wp2bd_get_image_style_width('thumbnail');
    $medium_width = _wp2bd_get_image_style_width('medium');
    $large_width = _wp2bd_get_image_style_width('large');

    assert($thumbnail_width === 100, 'Thumbnail width should be 100');
    assert($medium_width === 220, 'Medium width should be 220');
    assert($large_width === 480, 'Large width should be 480');

    echo "✓ Test 14 passed: _wp2bd_get_image_style_width() returns correct widths\n";
  }

  /**
   * Test 15: get_the_post_thumbnail() generates proper srcset attribute
   */
  public function test_get_the_post_thumbnail_srcset() {
    global $wp_post;

    // Mock image_style_url function for testing
    if (!function_exists('image_style_url')) {
      function image_style_url($style, $uri) {
        return 'http://example.com/files/styles/' . $style . '/' . basename($uri);
      }
    }

    // Create post with image
    $wp_post = (object) array(
      'ID' => 1515,
      'field_image' => array(
        'und' => array(
          0 => array(
            'uri' => 'public://images/srcset-test.jpg',
            'alt' => 'Srcset test',
          ),
        ),
      ),
    );

    $html = get_the_post_thumbnail(null, 'medium');

    // Check for srcset attribute
    assert(strpos($html, 'srcset=') !== false, 'Should include srcset attribute');
    assert(strpos($html, 'sizes=') !== false, 'Should include sizes attribute');

    echo "✓ Test 15 passed: get_the_post_thumbnail() generates proper srcset attribute\n";
  }

  /**
   * Run all tests
   */
  public function runAllTests() {
    echo "\n=== Running Post Thumbnail Functions Tests ===\n\n";

    $this->setUp();
    $this->test_has_post_thumbnail_no_image();

    $this->setUp();
    $this->test_has_post_thumbnail_with_backdrop_field_api();

    $this->setUp();
    $this->test_has_post_thumbnail_with_simplified_array();

    $this->setUp();
    $this->test_has_post_thumbnail_with_parameter();

    $this->setUp();
    $this->test_get_the_post_thumbnail_no_image();

    $this->setUp();
    $this->test_get_the_post_thumbnail_default_size();

    $this->setUp();
    $this->test_get_the_post_thumbnail_custom_size();

    $this->setUp();
    $this->test_get_the_post_thumbnail_custom_attributes();

    $this->setUp();
    $this->test_the_post_thumbnail_echoes_output();

    $this->setUp();
    $this->test_the_post_thumbnail_with_size();

    $this->setUp();
    $this->test_get_thumbnail_data_helper();

    $this->setUp();
    $this->test_has_post_thumbnail_null_post();

    $this->setUp();
    $this->test_get_the_post_thumbnail_string_uri();

    $this->setUp();
    $this->test_get_image_style_width();

    $this->setUp();
    $this->test_get_the_post_thumbnail_srcset();

    echo "\n=== All Tests Passed! ===\n";
    echo "Total: 15 tests\n\n";
  }
}

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli' && isset($argv[0]) && basename($argv[0]) === basename(__FILE__)) {
  $tester = new ThumbnailFunctionsTest();
  $tester->runAllTests();
}
