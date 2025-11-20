<?php
/**
 * Usage Examples for language_attributes()
 *
 * Demonstrates how to use language_attributes() in WordPress themes
 * and how it integrates with Backdrop CMS.
 *
 * @package WP2BD
 * @subpackage Examples
 */

// Include the functions
require_once dirname(__DIR__) . '/functions/content-display.php';

echo "=== Language Attributes Function - Usage Examples ===\n\n";

// Example 1: Default usage (English, LTR)
echo "Example 1: Default usage\n";
echo "-------------------------\n";
echo "Code: <html <?php language_attributes(); ?>>\n";
echo "Output: <html " . get_language_attributes() . ">\n\n";

// Example 2: With Backdrop English language
echo "Example 2: Backdrop English (LTR)\n";
echo "-------------------------\n";
$GLOBALS['language'] = (object) array(
  'langcode' => 'en',
  'direction' => 0,
);
echo "Code: <html <?php language_attributes(); ?>>\n";
echo "Output: <html " . get_language_attributes() . ">\n\n";

// Example 3: With Backdrop Arabic language (RTL)
echo "Example 3: Backdrop Arabic (RTL)\n";
echo "-------------------------\n";
$GLOBALS['language'] = (object) array(
  'langcode' => 'ar',
  'direction' => 1,
);
echo "Code: <html <?php language_attributes(); ?>>\n";
echo "Output: <html " . get_language_attributes() . ">\n\n";

// Example 4: With Backdrop Hebrew language (RTL)
echo "Example 4: Backdrop Hebrew (RTL)\n";
echo "-------------------------\n";
$GLOBALS['language'] = (object) array(
  'langcode' => 'he',
  'direction' => 1,
);
echo "Code: <html <?php language_attributes(); ?>>\n";
echo "Output: <html " . get_language_attributes() . ">\n\n";

// Example 5: XHTML doctype
echo "Example 5: XHTML doctype\n";
echo "-------------------------\n";
$GLOBALS['language'] = (object) array(
  'langcode' => 'fr',
  'direction' => 0,
);
echo "Code: <html <?php language_attributes('xhtml'); ?>>\n";
echo "Output: <html " . get_language_attributes('xhtml') . ">\n\n";

// Example 6: With filter applied
echo "Example 6: With custom filter\n";
echo "-------------------------\n";

// Mock filter function
if (!function_exists('apply_filters')) {
  function apply_filters($hook, $value, $doctype = null) {
    if ($hook === 'language_attributes') {
      // Example: Add custom data attribute
      return $value . ' data-theme="wp2bd"';
    }
    return $value;
  }
}

$GLOBALS['language'] = (object) array(
  'langcode' => 'en-US',
  'direction' => 0,
);
echo "Code: <html <?php language_attributes(); ?>>\n";
echo "Output: <html " . get_language_attributes() . ">\n\n";

echo "=== Complete HTML Example ===\n\n";
echo "<!DOCTYPE html>\n";
$GLOBALS['language'] = (object) array(
  'langcode' => 'en-US',
  'direction' => 0,
);
echo "<html " . get_language_attributes() . ">\n";
echo "<head>\n";
echo "  <meta charset=\"UTF-8\">\n";
echo "  <title>WP2BD Theme</title>\n";
echo "</head>\n";
echo "<body>\n";
echo "  <h1>WordPress to Backdrop Demo</h1>\n";
echo "</body>\n";
echo "</html>\n";
