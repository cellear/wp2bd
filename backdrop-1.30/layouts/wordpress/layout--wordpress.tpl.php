<?php
/**
 * @file
 * Template for the WordPress Layout.
 *
 * This layout runs WordPress themes exactly as WordPress does:
 * - The theme's template file (index.php, single.php, etc.) controls everything
 * - Template calls get_header(), outputs content, get_sidebar(), get_footer()
 * - NO wrapper divs that could break theme CSS
 *
 * Variables:
 * - $content['page'] - The WordPress Page block (runs the theme's template)
 * - $messages: Status and error messages
 * - $tabs: Admin tabs
 * - $action_links: Admin action links
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

  <?php 
  // Single block that runs the entire WordPress template
  // This is how WordPress works - one template controls header, content, sidebar, footer
  if (!empty($content['page'])): 
    print $content['page']; 
  endif; 
  ?>

</div>
