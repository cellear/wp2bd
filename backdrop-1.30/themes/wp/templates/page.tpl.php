<?php
/**
 * @file
 * Simple page template for WordPress content rendering.
 *
 * This template provides a basic HTML structure for WordPress content.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php print backdrop_get_title(); ?> | <?php print config_get('system.core', 'site_name'); ?></title>
  <?php print backdrop_get_css(); ?>
  <?php print backdrop_get_js(); ?>
</head>
<body class="wordpress-theme">
  <div id="page">
    <header id="header" role="banner">
      <div class="site-branding">
        <h1 class="site-title">
          <a href="<?php print url(); ?>" rel="home"><?php print config_get('system.core', 'site_name'); ?></a>
        </h1>
        <?php $slogan = config_get('system.core', 'site_slogan'); ?>
        <?php if ($slogan): ?>
          <p class="site-description"><?php print $slogan; ?></p>
        <?php endif; ?>
      </div>
    </header>

    <div id="main">
      <div id="content" role="main">
        <?php print wp_render_wordpress_content(); ?>
      </div>
    </div>

    <footer id="footer" role="contentinfo">
      <div class="site-info">
        <p>&copy; <?php print date('Y'); ?> <?php print $site_name; ?> | Powered by WordPress-as-Engine Architecture</p>
      </div>
    </footer>
  </div>
</body>
</html>
