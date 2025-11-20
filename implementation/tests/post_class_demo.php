<?php
/**
 * Demonstration of post_class() Function
 *
 * Shows practical usage examples of the post_class() function
 * in WordPress theme templates.
 *
 * @package WP2BD
 * @subpackage Examples
 */

// Include the functions
require_once dirname(__DIR__) . '/functions/content-display.php';

echo "=== post_class() Function Demonstration ===\n\n";

// Example 1: Basic blog post
echo "Example 1: Basic Blog Post\n";
echo "----------------------------\n";
$wp_post = (object) array(
  'ID' => 42,
  'post_type' => 'post',
  'post_status' => 'publish',
  'post_title' => 'Getting Started with PHP',
  'categories' => array(
    (object) array('term_id' => 1, 'slug' => 'tutorials'),
    (object) array('term_id' => 2, 'slug' => 'programming'),
  ),
  'tags' => array(
    (object) array('term_id' => 10, 'slug' => 'php'),
    (object) array('term_id' => 11, 'slug' => 'beginner'),
  ),
);

echo "Template code: <article <?php post_class(); ?>>\n";
echo "Renders as:    <article ";
post_class();
echo ">\n\n";

// Example 2: Page with custom classes
echo "Example 2: Page with Custom Classes\n";
echo "------------------------------------\n";
$wp_post = (object) array(
  'ID' => 15,
  'post_type' => 'page',
  'post_status' => 'publish',
  'post_title' => 'About Us',
);

echo "Template code: <article <?php post_class('full-width featured'); ?>>\n";
echo "Renders as:    <article ";
post_class('full-width featured');
echo ">\n\n";

// Example 3: Backdrop node
echo "Example 3: Backdrop Node (Article)\n";
echo "-----------------------------------\n";
$wp_post = (object) array(
  'nid' => 100,
  'type' => 'article',
  'status' => 1,
  'title' => 'Backdrop CMS Tutorial',
  'categories' => array(
    array('id' => 5, 'slug' => 'cms'),
  ),
);

echo "Template code: <article <?php post_class(); ?>>\n";
echo "Renders as:    <article ";
post_class();
echo ">\n\n";

// Example 4: Draft post
echo "Example 4: Draft Post\n";
echo "---------------------\n";
$wp_post = (object) array(
  'ID' => 99,
  'post_type' => 'post',
  'post_status' => 'draft',
  'post_title' => 'Work in Progress',
);

echo "Template code: <article <?php post_class(); ?>>\n";
echo "Renders as:    <article ";
post_class();
echo ">\n\n";

// Example 5: Custom post type
echo "Example 5: Custom Post Type (Product)\n";
echo "--------------------------------------\n";
$wp_post = (object) array(
  'ID' => 250,
  'post_type' => 'product',
  'post_status' => 'publish',
  'post_title' => 'Amazing Widget',
  'categories' => array(
    (object) array('term_id' => 20, 'slug' => 'electronics'),
  ),
);

echo "Template code: <article <?php post_class('product-card'); ?>>\n";
echo "Renders as:    <article ";
post_class('product-card');
echo ">\n\n";

// Example 6: Get classes as array for programmatic use
echo "Example 6: Getting Classes as Array\n";
echo "------------------------------------\n";
$wp_post = (object) array(
  'ID' => 500,
  'post_type' => 'post',
  'post_status' => 'publish',
  'tags' => array(
    (object) array('term_id' => 30, 'slug' => 'important'),
  ),
);

$classes = get_post_class();
echo "PHP code: \$classes = get_post_class();\n";
echo "Result:   " . print_r($classes, true) . "\n";

// Example 7: Real-world template usage
echo "Example 7: Real-World Template Usage\n";
echo "-------------------------------------\n";
echo "In a WordPress/WP2BD theme template (e.g., content.php):\n\n";
echo "<?php\n";
echo "// The Loop\n";
echo "while (have_posts()) {\n";
echo "  the_post();\n";
echo "?>\n";
echo "  <article <?php post_class(); ?>>\n";
echo "    <header class=\"entry-header\">\n";
echo "      <h2><?php the_title(); ?></h2>\n";
echo "    </header>\n";
echo "    <div class=\"entry-content\">\n";
echo "      <?php the_content(); ?>\n";
echo "    </div>\n";
echo "  </article>\n";
echo "<?php\n";
echo "}\n";
echo "?>\n\n";

echo "This ensures each post has semantic, targetable CSS classes for styling!\n\n";

echo "=== End of Demonstration ===\n";
