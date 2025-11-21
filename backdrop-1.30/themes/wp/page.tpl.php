<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="profile" href="http://gmpg.org/xfn/11">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site">
  <?php
  /**
   * Delegate to WordPress theme's template hierarchy
   *
   * For now, we'll use index.php as the main template.
   * Later we can implement proper template hierarchy
   * (single.php, page.php, archive.php, etc.)
   */

  // Determine which WordPress template to use
  $wp_template = 'index.php';

  if (is_single()) {
    if (file_exists(get_template_directory() . '/single.php')) {
      $wp_template = 'single.php';
    }
  } elseif (is_page()) {
    if (file_exists(get_template_directory() . '/page.php')) {
      $wp_template = 'page.php';
    }
  } elseif (is_home() || is_front_page()) {
    if (file_exists(get_template_directory() . '/home.php')) {
      $wp_template = 'home.php';
    } elseif (file_exists(get_template_directory() . '/front-page.php')) {
      $wp_template = 'front-page.php';
    }
  } elseif (is_archive()) {
    if (file_exists(get_template_directory() . '/archive.php')) {
      $wp_template = 'archive.php';
    }
  } elseif (is_search()) {
    if (file_exists(get_template_directory() . '/search.php')) {
      $wp_template = 'search.php';
    }
  } elseif (is_404()) {
    if (file_exists(get_template_directory() . '/404.php')) {
      $wp_template = '404.php';
    }
  }

  // Include the WordPress template
  $template_path = get_template_directory() . '/' . $wp_template;
  if (file_exists($template_path)) {
    include $template_path;
  } else {
    echo '<p>WordPress template not found: ' . esc_html($wp_template) . '</p>';
    echo '<p>Looking in: ' . esc_html(get_template_directory()) . '</p>';
  }
  ?>
</div>

<?php wp_footer(); ?>

</body>
</html>
