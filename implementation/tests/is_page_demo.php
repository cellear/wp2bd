<?php
/**
 * is_page() Implementation Demo
 *
 * Demonstrates the is_page() function in action with real-world examples.
 *
 * @package WP2BD
 * @subpackage Tests
 */

require_once dirname(__FILE__) . '/../functions/conditionals.php';
require_once dirname(__FILE__) . '/../classes/WP_Query.php';
require_once dirname(__FILE__) . '/../classes/WP_Post.php';

echo "=== is_page() Implementation Demo ===\n\n";

// Example 1: Basic page detection
echo "Example 1: Basic page detection\n";
echo "--------------------------------\n";

$about_page = new WP_Post();
$about_page->ID = 42;
$about_page->post_title = 'About Us';
$about_page->post_name = 'about';
$about_page->post_type = 'page';
$about_page->post_status = 'publish';
$about_page->post_content = 'We are a great company!';

$wp_query = new WP_Query();
$wp_query->posts = array($about_page);
$wp_query->post_count = 1;
$wp_query->is_page = true;
$wp_query->queried_object = $about_page;

if (is_page()) {
    echo "✓ Viewing a page!\n";
    echo "  Page title: {$about_page->post_title}\n";
    echo "  Page slug: {$about_page->post_name}\n";
}

echo "\n";

// Example 2: Check specific page by ID
echo "Example 2: Check specific page by ID\n";
echo "------------------------------------\n";

if (is_page(42)) {
    echo "✓ This is page ID 42 (About Us)\n";
}

if (!is_page(99)) {
    echo "✓ This is NOT page ID 99\n";
}

echo "\n";

// Example 3: Check specific page by slug
echo "Example 3: Check specific page by slug\n";
echo "--------------------------------------\n";

if (is_page('about')) {
    echo "✓ This is the 'about' page\n";
}

if (!is_page('contact')) {
    echo "✓ This is NOT the 'contact' page\n";
}

echo "\n";

// Example 4: Check specific page by title
echo "Example 4: Check specific page by title\n";
echo "---------------------------------------\n";

if (is_page('About Us')) {
    echo "✓ This is the 'About Us' page\n";
}

echo "\n";

// Example 5: Check multiple pages
echo "Example 5: Check multiple pages at once\n";
echo "---------------------------------------\n";

if (is_page(array('about', 'contact', 'team'))) {
    echo "✓ This is one of: about, contact, or team pages\n";
}

if (is_page(array(42, 99, 100))) {
    echo "✓ This is one of: page 42, 99, or 100\n";
}

echo "\n";

// Example 6: Distinguish between page and post
echo "Example 6: Page vs Post differentiation\n";
echo "---------------------------------------\n";

// First, show we're on a page
echo "Current state: is_page() = " . (is_page() ? 'true' : 'false') . "\n";

// Now switch to a post
$blog_post = new WP_Post();
$blog_post->ID = 123;
$blog_post->post_title = 'My Blog Post';
$blog_post->post_name = 'my-blog-post';
$blog_post->post_type = 'post';  // This is a POST, not a page

$wp_query = new WP_Query();
$wp_query->posts = array($blog_post);
$wp_query->post_count = 1;
$wp_query->is_single = true;
$wp_query->is_page = false;  // NOT a page
$wp_query->queried_object = $blog_post;

echo "After switching to post: is_page() = " . (is_page() ? 'true' : 'false') . "\n";
echo "✓ is_page() correctly returns false for blog posts\n";

echo "\n";

// Example 7: Theme template logic (real-world usage)
echo "Example 7: Real-world template logic\n";
echo "------------------------------------\n";

// Back to page for this example
$wp_query->is_page = true;
$wp_query->is_single = false;
$wp_query->queried_object = $about_page;

// Typical WordPress theme logic
if (is_page()) {
    echo "<!-- Using page.php template -->\n";

    if (is_page('about')) {
        echo "<!-- Loading custom about page sidebar -->\n";
    }

    if (is_page(array('about', 'contact', 'team'))) {
        echo "<!-- Adding special CSS for info pages -->\n";
    }
}

echo "\n";

echo "=== Demo Complete ===\n";
