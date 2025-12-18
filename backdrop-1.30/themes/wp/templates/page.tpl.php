echo "<!-- PHP is working -->";
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
<?php } ?>
