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
    // Check if this is an error page or maintenance page
    if (!isset($page) || !is_array($page)) {
      // For error/maintenance pages, just show the content
      print wp_render_wordpress_content();
    } else {
      // Normal page with regions
    ?>
    <div class="page-wrapper">
      <header id="header" role="banner" class="header">
        <?php print render($page['header']); ?>
      </header>

      <div class="main-wrapper">
        <div id="content" class="content" role="main">
          <?php print wp_render_wordpress_content(); ?>
        </div>

        <?php if (isset($page['sidebar']) && $page['sidebar']): ?>
          <aside id="sidebar" class="sidebar" role="complementary">
            <?php print render($page['sidebar']); ?>
          </aside>
        <?php endif; ?>
      </div>
    </div>
    <?php 
      // Render page bottom (includes admin bar for users with permissions)
      if (isset($page_bottom)) {
        print $page_bottom;
      }
    } ?>
    <?php print backdrop_get_js('footer'); ?>
  </body>
</html>
