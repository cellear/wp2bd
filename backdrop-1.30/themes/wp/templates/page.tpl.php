<?php
/**
 * @file
 * Backdrop page template with WordPress content integration.
 *
 * This template uses Backdrop's region system and integrates WordPress content.
 * The admin bar is automatically added via admin_bar_preprocess_page() hook
 * and rendered in $page_bottom.
 */
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
