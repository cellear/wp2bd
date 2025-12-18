<?php
/**
 * @file
 * WordPress Page Block Content Template
 *
 * This template renders WordPress theme output for use in layout blocks.
 * It uses the same logic as page.tpl.php but outputs only the WordPress
 * content without full page structure.
 */

// Include the shared rendering logic
$rendering_file = dirname(__FILE__) . '/wp-rendering-logic.inc';
if (file_exists($rendering_file)) {
  include $rendering_file;

  // Print the WordPress output
  if (!empty($wordpress_output)) {
    print $wordpress_output;
  }
} else {
  print '<div class="error">WordPress rendering logic not found.</div>';
}
