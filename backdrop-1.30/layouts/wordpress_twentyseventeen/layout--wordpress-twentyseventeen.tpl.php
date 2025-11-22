<?php
/**
 * @file
 * Template for the WordPress Twenty Seventeen layout.
 *
 * This layout provides WordPress-compatible HTML structure for the Twenty Seventeen theme,
 * including the critical .site-content-contain div that creates the parallax effect.
 *
 * IMPORTANT: This layout works in conjunction with wp_content module's header/footer
 * rendering functions which strip out these wrapper divs from WordPress templates.
 * This layout adds them back in the correct places so all blocks are properly wrapped.
 *
 * Variables:
 * - $title: The page title, for use in the actual HTML content.
 * - $messages: Status and error messages. Should be displayed prominently.
 * - $tabs: Tabs linking to any sub-pages beneath the current page.
 * - $action_links: Array of actions local to the page.
 * - $classes: Array of CSS classes to be added to the layout wrapper.
 * - $attributes: Array of additional HTML attributes to be added to the layout wrapper.
 * - $content: An array of content, each item in the array is keyed to one region:
 *   - $content['header'] - WordPress Header block
 *   - $content['content'] - WordPress Content block
 *   - $content['sidebar'] - WordPress Sidebar block
 *   - $content['footer'] - WordPress Footer block
 */
?>
<div class="layout--wordpress-twentyseventeen <?php print implode(' ', $classes); ?>"<?php print backdrop_attributes($attributes); ?>>
  <!-- WordPress #page wrapper - stripped from header.php, added here so all blocks are inside it -->
  <div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#content"><?php print t('Skip to content'); ?></a>

    <?php if ($content['header']): ?>
      <!-- WordPress header content (from header.php, with DOCTYPE/html/head/body/page divs stripped) -->
      <?php print $content['header']; ?>
    <?php endif; ?>

    <?php if ($messages): ?>
      <div class="l-messages" role="status" aria-label="<?php print t('Status messages'); ?>">
        <?php print $messages; ?>
      </div>
    <?php endif; ?>

    <?php if ($tabs): ?>
      <nav class="tabs" role="tablist" aria-label="<?php print t('Admin content navigation tabs.'); ?>">
        <?php print $tabs; ?>
      </nav>
    <?php endif; ?>

    <?php print $action_links; ?>

    <!-- This is the CRITICAL div that creates the parallax effect -->
    <!-- It has background-color: #fff and position: relative in Twenty Seventeen's CSS -->
    <!-- As you scroll, this white div slides over the fixed header image -->
    <div class="site-content-contain">
      <div id="content" class="site-content">

        <?php if ($title && !backdrop_is_front_page()): ?>
          <div class="wrap">
            <header class="entry-header">
              <h1 class="entry-title"><?php print $title; ?></h1>
            </header>
          </div>
        <?php endif; ?>

        <div class="wrap">
          <?php if (!empty($content['content']) && !empty($content['sidebar'])): ?>
            <!-- Two column layout: content + sidebar -->
            <div id="primary" class="content-area">
              <?php print $content['content']; ?>
            </div><!-- #primary -->

            <?php print $content['sidebar']; ?>

          <?php elseif (!empty($content['content'])): ?>
            <!-- Full width content (no sidebar) -->
            <div id="primary" class="content-area">
              <?php print $content['content']; ?>
            </div><!-- #primary -->

          <?php endif; ?>
        </div><!-- .wrap -->

      </div><!-- #content -->

      <?php if ($content['footer']): ?>
        <!-- WordPress footer content (from footer.php, with closing divs and body/html stripped) -->
        <?php print $content['footer']; ?>
      <?php endif; ?>

    </div><!-- .site-content-contain -->
  </div><!-- #page -->
</div><!-- /.layout--wordpress-twentyseventeen -->
