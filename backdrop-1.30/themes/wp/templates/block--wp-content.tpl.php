<?php
/**
 * @file
 * Template for WordPress blocks - outputs raw content without wrapper divs.
 *
 * This template is used for wp_content module blocks (header, content, sidebar, footer).
 * It skips Backdrop's normal block wrappers to allow WordPress theme wrapper divs
 * to open in the header block and close in the footer block.
 */
?>
<?php print render($content); ?>
