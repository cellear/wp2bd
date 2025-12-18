<?php
/**
 * @file
 * WordPress Theme Wrapper - page.tpl.php
 *
 * Theme-Only Architecture:
 * This template receives Backdrop data and uses WordPress to render it.
 *
 * Flow:
 * 1. Backdrop provides $node (or list of nodes)
 * 2. We convert to WordPress objects using bridges
 * 3. WordPress theme renders with its templates
 * 4. We capture output and return to Backdrop
 */

// ============================================================================
// Check WordPress Bootstrap
// ============================================================================

$wordpress_ready = (
  function_exists('have_posts') &&
  function_exists('the_post') &&
  class_exists('WP_Post') &&
  class_exists('WP_Query') &&
  function_exists('wp4bd_node_to_post')
);

if (!$wordpress_ready) {
  // WordPress didn't bootstrap - show error
  print '<div class="error">';
  print '<h2>WordPress Bootstrap Failed</h2>';
  print '<p>The WordPress rendering engine could not initialize.</p>';
  print '</div>';
  return;
}

// ============================================================================
// Render WordPress Content
// ============================================================================

// Include shared rendering logic
// This sets $wordpress_output variable with the rendered WordPress theme HTML
require_once dirname(__FILE__) . '/wp-rendering-logic.inc';


// ============================================================================
// Return to Backdrop
// ============================================================================

// Print the WordPress-generated HTML
// Backdrop will wrap this with its page structure (admin menu, etc.)
print $wordpress_output;
