<?php
/**
 * @file
 * Template for the Generic WordPress Layout.
 *
 * This layout works with ANY WordPress theme by letting themes control their
 * own wrapper structure via header.php and footer.php.
 *
 * IMPORTANT: This layout is theme-agnostic. It does NOT add theme-specific
 * wrapper divs. Instead, it trusts WordPress themes to provide their own
 * structure through their template files.
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
<div class="layout--wordpress <?php print implode(' ', $classes); ?>"<?php print backdrop_attributes($attributes); ?>>

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

  <!-- WordPress Header Block: Outputs theme header with all wrapper divs -->
  <?php if (!empty($content['header'])): ?>
    <?php print $content['header']; ?>
  <?php endif; ?>

  <!-- Wrap div contains both content and sidebar for two-column layout -->
  <div class="wrap">
    <!-- WordPress Content Block: Outputs main content area -->
    <?php if (!empty($content['content'])): ?>
      <?php print $content['content']; ?>
    <?php endif; ?>

    <!-- WordPress Sidebar Block: Outputs theme sidebar -->
    <?php if (!empty($content['sidebar'])): ?>
      <?php print $content['sidebar']; ?>
    <?php endif; ?>
  </div><!-- .wrap -->

  <!-- WordPress Footer Block: Outputs footer and closes all theme divs -->
  <?php if (!empty($content['footer'])): ?>
    <?php print $content['footer']; ?>
  <?php endif; ?>

</div><!-- /.layout--wordpress -->
