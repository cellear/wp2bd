<?php
/**
 * Usage Example for Excerpt Functions
 *
 * Demonstrates how the_excerpt() and related functions work
 * in real-world WordPress theme templates.
 *
 * @package WP2BD
 * @subpackage Examples
 */

// Include the functions
require_once dirname(__DIR__) . '/functions/content-display.php';

echo "=== Excerpt Functions Usage Examples ===\n\n";

// Example 1: Custom Excerpt
echo "--- Example 1: Custom Excerpt ---\n";
global $wp_post;
$wp_post = (object) array(
  'ID' => 1,
  'post_title' => 'My First Post',
  'post_excerpt' => 'This is my custom excerpt that I wrote specifically for this post.',
  'post_content' => 'This is the full content of my first post with lots of detail...',
);

echo "Title: " . get_the_title() . "\n";
echo "Excerpt: ";
the_excerpt();
echo "\n\n";

// Example 2: Auto-Generated Excerpt
echo "--- Example 2: Auto-Generated Excerpt (No Custom Excerpt) ---\n";

// Hook wp_trim_excerpt to the get_the_excerpt filter
// This is normally done by WordPress core, but we'll do it manually for the example
if (!function_exists('apply_filters')) {
  function apply_filters($hook, $value, $arg2 = null) {
    if ($hook === 'get_the_excerpt' && empty($value)) {
      return wp_trim_excerpt($value);
    }
    return $value;
  }
}

$wp_post = (object) array(
  'ID' => 2,
  'post_title' => 'Post Without Excerpt',
  'post_excerpt' => '', // No custom excerpt
  'post_content' => '<p>This is a <strong>long post</strong> with lots of content. It contains multiple sentences and paragraphs. The excerpt function should automatically generate a summary from this content by taking the first 55 words and adding an ellipsis at the end. Let me add more words to make sure we exceed the 55 word limit. Here are some more words to pad out the content. And even more words to ensure we definitely go over the limit. This should be enough now.</p>',
);

echo "Title: " . get_the_title() . "\n";
echo "Excerpt: ";
the_excerpt();
echo "\n\n";

// Example 3: Backdrop-Style Node
echo "--- Example 3: Backdrop-Style Node with Body Summary ---\n";
$wp_post = (object) array(
  'nid' => 3,
  'title' => 'Backdrop Article',
  'body' => array(
    'summary' => 'This is the Backdrop body summary field.',
    'value' => 'This is the full Backdrop body content that would be displayed on the node page.',
  ),
);

echo "Title: " . get_the_title() . "\n";
echo "Excerpt: " . get_the_excerpt() . "\n\n";

// Example 4: Using wp_trim_words() directly
echo "--- Example 4: Using wp_trim_words() Directly ---\n";
$long_text = "WordPress is a free and open-source content management system written in PHP and paired with a MySQL or MariaDB database. Features include a plugin architecture and a template system, referred to within WordPress as Themes.";

echo "Original: " . $long_text . "\n";
echo "Trimmed (10 words): " . wp_trim_words($long_text, 10, '...') . "\n";
echo "Trimmed (20 words): " . wp_trim_words($long_text, 20, ' [more]') . "\n\n";

// Example 5: Using wp_strip_all_tags()
echo "--- Example 5: Using wp_strip_all_tags() ---\n";
$html_content = "<p>This is <strong>bold</strong> and <em>italic</em> text.</p><script>alert('evil');</script><p>More content.</p>";

echo "Original: " . $html_content . "\n";
echo "Stripped: " . wp_strip_all_tags($html_content) . "\n\n";

echo "=== End of Examples ===\n";
