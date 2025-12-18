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

      // Call the active theme's scripts function
      $theme_scripts_function = str_replace('-', '', WP2BD_ACTIVE_THEME) . '_scripts';
      if (function_exists($theme_scripts_function)) {
        print "<!-- Calling $theme_scripts_function -->\n";
        call_user_func($theme_scripts_function);
        print "<!-- $theme_scripts_function completed -->\n";
      } else {
        print "<!-- $theme_scripts_function function not found -->\n";
      }

      // Print WordPress styles directly as link tags
      if (function_exists('wp_print_styles')) {
        wp_print_styles();
      }

      // First, let WordPress core process the script queue to register scripts
      // We need this to happen so that localized data gets stored properly

      // Now manually output localized script data for WordPress scripts
      // The WordPress core stores localized data in $wp_scripts->registered[$handle]->extra['data']
      global $wp_scripts;
      if (isset($wp_scripts) && isset($wp_scripts->registered) && is_array($wp_scripts->registered)) {
        print "<!-- Checking registered scripts for localized data -->\n";
        foreach ($wp_scripts->registered as $handle => $script) {
          if (isset($script->extra) && isset($script->extra['data']) && !empty($script->extra['data'])) {
            print "<!-- Found localized data for script: $handle -->\n";
            echo "<script type='text/javascript'>\n";
            echo "/* <![CDATA[ */\n";
            echo $script->extra['data'] . "\n";
            echo "/* ]]> */\n";
            echo "</script>\n";
          }
        }
      } else {
        print "<!-- wp_scripts registered data not available -->\n";
      }
    ?>
    <?php print backdrop_get_css(); ?>
    <?php print backdrop_get_js(); ?>
  </head>
  <body <?php
    // Check if WordPress theme has template parts
    $has_wordpress_header = function_exists('get_header') && !empty(locate_template('header.php'));
    $has_wordpress_sidebar = function_exists('get_sidebar') && !empty(locate_template('sidebar.php'));
    $has_wordpress_footer = function_exists('get_footer') && !empty(locate_template('footer.php'));

    // Check if sidebar should be shown (has widgets)
    $sidebar_active = function_exists('is_active_sidebar') && is_active_sidebar('sidebar-1');
    $show_sidebar = $has_wordpress_sidebar && $sidebar_active;

    // Build body classes
    $body_classes = array();
    if (function_exists('get_body_class')) {
      $body_classes = get_body_class();
    } elseif (isset($classes) && is_array($classes)) {
      $body_classes = $classes;
    }

    // Add has-sidebar class if sidebar should be shown
    if ($show_sidebar) {
      $body_classes[] = 'has-sidebar';
    }

    echo 'class="' . implode(' ', $body_classes) . '"';
  ?><?php print backdrop_attributes($body_attributes); ?>>
    <?php


    /* Use WordPress template parts when available for proper theme integration */
    if ($has_wordpress_header) {
      get_header();
    } else {
      // Fallback header
      echo '<header id="masthead" class="site-header" role="banner"><div class="site-branding"><h1 class="site-title">WordPress Site</h1></div></header>';
    }
    ?>

    <div class="site-content-contain">
      <div id="content" class="site-content">

        <div class="wrap">
          <div id="primary" class="content-area" role="main">
            <?php
            // If $page is a string (rendered content), use it; otherwise render WordPress content
            if (isset($page) && is_string($page)) {
              print $page;
            } else {
              print wp_render_wordpress_content();
            }
            ?>
          </div><!-- #primary -->

          <?php if ($show_sidebar && !isset($GLOBALS['wp2bd_sidebar_rendered'])): ?>
            <?php get_sidebar(); ?>
          <?php endif; ?>

        </div><!-- .wrap -->

      </div><!-- #content -->
    </div><!-- .site-content-contain -->

    <?php
    if ($has_wordpress_footer) {
      get_footer();
    } else {
      // Fallback footer
      echo '<footer id="colophon" class="site-footer" role="contentinfo"><div class="site-info">Powered by WordPress</div></footer>';
    }

    // Render page bottom (includes admin bar for users with permissions)
    if (isset($page_bottom)) {
      print $page_bottom;
    }

    // Output theme-specific localized data
    // This provides the JavaScript variables that theme scripts need
    $theme_data = array();
    $theme_var_name = '';

    switch (WP2BD_ACTIVE_THEME) {
      case 'twentyseventeen':
        $theme_var_name = 'twentyseventeenScreenReaderText';
        $theme_data = array(
          'quote' => '<svg class="icon icon-quote-right" aria-hidden="true" role="img"> <use href="#icon-quote-right" xlink:href="#icon-quote-right"></use> </svg>'
        );
        break;
      case 'twentysixteen':
        $theme_var_name = 'screenReaderText';
        $theme_data = array(
          'expand' => 'expand child menu',
          'collapse' => 'collapse child menu',
        );
        break;
      // Add other themes as needed
    }

    if (!empty($theme_var_name) && !empty($theme_data)) {
      echo "<script type='text/javascript'>\n";
      echo "/* <![CDATA[ */\n";
      echo "var $theme_var_name = " . json_encode($theme_data) . ";\n";
      echo "/* ]]> */\n";
      echo "</script>\n";
    }

    // Output WordPress footer scripts
    if (function_exists('wp_print_scripts')) {
      wp_print_scripts(true); // Footer scripts
    }
    ?>
    <?php print backdrop_get_js('footer'); ?>
  </body>
</html>
