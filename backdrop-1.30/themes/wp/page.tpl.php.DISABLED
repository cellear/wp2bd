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
  // DEBUG: Build content using WordPress loop
  $content = '';
  
  if (have_posts()) {
    $content .= '<div class="wp-posts-list">';
    while (have_posts()) {
      the_post();
      $content .= '<article class="post">';
      $content .= '<h2><a href="' . get_permalink() . '">' . get_the_title() . '</a></h2>';
      $content .= '<div class="entry-content">' . get_the_content() . '</div>';
      $content .= '</article>';
    }
    $content .= '</div>';
  } else {
    $content .= '<p>No posts found.</p>';
  }
  
  // Debug: Print $content visibly
  echo '<div style="background: #0f0; padding: 10px; margin: 10px; border: 2px solid #0a0;">';
  echo '<strong>CONTENT DEBUG:</strong><br>';
  echo 'Content length: ' . strlen($content) . ' characters<br>';
  echo 'Content preview (first 1000 chars):<br>';
  echo '<pre>' . htmlspecialchars(substr($content, 0, 1000)) . '</pre>';
  echo '</div>';
  
  // Output the content
  echo $content;
  
  /**
   * Delegate to WordPress theme's template hierarchy
   *
   * Determine which WordPress template to use based on the current page context.
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

  // TODO: Once direct output works, delegate to WordPress template
  /*
  // Include the WordPress template
  $template_path = get_template_directory() . '/' . $wp_template;
  if (file_exists($template_path)) {
    include $template_path;
  } else {
    echo '<p>WordPress template not found: ' . esc_html($wp_template) . '</p>';
    echo '<p>Looking in: ' . esc_html(get_template_directory()) . '</p>';
  }
  */
  ?>
</div>

<?php wp_footer(); ?>

</body>
</html>
