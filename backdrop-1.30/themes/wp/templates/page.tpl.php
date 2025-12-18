<?php
/**
 * @file
 * Backdrop page template with WordPress content integration.
 *
 * This template provides the full HTML structure and integrates WordPress
 * content. WordPress CSS/JS are loaded via wp_preprocess_html() hook.
 */
?>
<!DOCTYPE html>
<html<?php print backdrop_attributes($html_attributes); ?>>
  <head>
    <?php print backdrop_get_html_head(); ?>
    <?php if (isset($head)): ?>
      <?php print $head; ?>
    <?php endif; ?>
    <title><?php print $head_title; ?></title>
    <?php
      // Fire wp_enqueue_scripts to allow WordPress themes to enqueue CSS/JS
      if (function_exists('do_action')) {
        do_action('wp_enqueue_scripts');
      }

      // Also manually call twentyseventeen_scripts if it exists
      if (function_exists('twentyseventeen_scripts')) {
        twentyseventeen_scripts();
      }

      // Print WordPress styles directly as link tags
      if (function_exists('wp_print_styles')) {
        wp_print_styles();
      }
    ?>
    <?php print backdrop_get_css(); ?>
    <?php print backdrop_get_js(); ?>
  </head>
  <body <?php if (function_exists('body_class')) { body_class(); } else { echo 'class="' . implode(' ', $classes) . '"'; } ?><?php print backdrop_attributes($body_attributes); ?>>
    <?php
    // Check if WordPress theme has template parts
    $has_wordpress_header = function_exists('get_header') && !empty(locate_template('header.php'));
    $has_wordpress_sidebar = function_exists('get_sidebar') && !empty(locate_template('sidebar.php'));
    $has_wordpress_footer = function_exists('get_footer') && !empty(locate_template('footer.php'));

    // Use WordPress template parts if available
    if ($has_wordpress_header) {
      // Include WordPress header
      get_header();
    } else {
      // Fallback wrapper
    ?>
    <div class="page-wrapper">
    <?php } ?>

      <div class="main-wrapper">
        <div id="content" class="content" role="main">
          <?php
          // If $page is a string (rendered content), use it; otherwise render WordPress content
          if (isset($page) && is_string($page)) {
            print $page;
          } else {
            print wp_render_wordpress_content();
          }
          ?>
        </div>

        <?php if ($has_wordpress_sidebar): ?>
          <?php get_sidebar(); ?>
        <?php endif; ?>
      </div>

    <?php
    if ($has_wordpress_footer) {
      // Include WordPress footer
      get_footer();
    } else {
      // Close wrapper if we opened it
    ?>
    </div>
    <?php
    }

    // Render page bottom (includes admin bar for users with permissions)
    if (isset($page_bottom)) {
      print $page_bottom;
    }
    ?>
    <?php print backdrop_get_js('footer'); ?>
  </body>
</html>
