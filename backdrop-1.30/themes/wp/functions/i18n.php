<?php
/**
 * WordPress Internationalization (i18n) Functions for WP2BD
 *
 * These functions provide WordPress-compatible translation wrappers.
 * Currently they pass through text unchanged - full translation support
 * could be added by integrating with Backdrop's t() function.
 *
 * @package WP2BD
 * @subpackage i18n
 * @since 1.0.0
 */

if (!function_exists('__')) {
  /**
   * Retrieve the translation of $text.
   *
   * @param string $text   Text to translate.
   * @param string $domain Text domain. Default 'default'.
   * @return string Translated text.
   */
  function __($text, $domain = 'default') {
    // Pass through - could integrate with Backdrop's t() in future
    return $text;
  }
}

if (!function_exists('_e')) {
  /**
   * Display translated text.
   *
   * @param string $text   Text to translate.
   * @param string $domain Text domain. Default 'default'.
   */
  function _e($text, $domain = 'default') {
    echo __($text, $domain);
  }
}

if (!function_exists('_x')) {
  /**
   * Retrieve translated string with gettext context.
   *
   * @param string $text    Text to translate.
   * @param string $context Context information for translators.
   * @param string $domain  Text domain. Default 'default'.
   * @return string Translated text.
   */
  function _x($text, $context, $domain = 'default') {
    return $text;
  }
}

if (!function_exists('_ex')) {
  /**
   * Display translated string with gettext context.
   *
   * @param string $text    Text to translate.
   * @param string $context Context information for translators.
   * @param string $domain  Text domain. Default 'default'.
   */
  function _ex($text, $context, $domain = 'default') {
    echo _x($text, $context, $domain);
  }
}

if (!function_exists('_n')) {
  /**
   * Translates and retrieves the singular or plural form based on the supplied number.
   *
   * @param string $single The text for singular.
   * @param string $plural The text for plural.
   * @param int    $number The number to compare against.
   * @param string $domain Text domain. Default 'default'.
   * @return string The singular or plural form.
   */
  function _n($single, $plural, $number, $domain = 'default') {
    return ($number == 1) ? $single : $plural;
  }
}

if (!function_exists('_nx')) {
  /**
   * Translates and retrieves the singular or plural form with gettext context.
   *
   * @param string $single  The text for singular.
   * @param string $plural  The text for plural.
   * @param int    $number  The number to compare against.
   * @param string $context Context information for translators.
   * @param string $domain  Text domain. Default 'default'.
   * @return string The singular or plural form.
   */
  function _nx($single, $plural, $number, $context, $domain = 'default') {
    return ($number == 1) ? $single : $plural;
  }
}

if (!function_exists('esc_html__')) {
  /**
   * Retrieve the translation of $text and escape for safe use in HTML.
   *
   * @param string $text   Text to translate.
   * @param string $domain Text domain. Default 'default'.
   * @return string Translated and escaped text.
   */
  function esc_html__($text, $domain = 'default') {
    return esc_html(__($text, $domain));
  }
}

if (!function_exists('esc_html_e')) {
  /**
   * Display translated text escaped for safe use in HTML.
   *
   * @param string $text   Text to translate.
   * @param string $domain Text domain. Default 'default'.
   */
  function esc_html_e($text, $domain = 'default') {
    echo esc_html__($text, $domain);
  }
}

if (!function_exists('esc_html_x')) {
  /**
   * Translate string with context and escape for HTML.
   *
   * @param string $text    Text to translate.
   * @param string $context Context information for translators.
   * @param string $domain  Text domain. Default 'default'.
   * @return string Translated and escaped text.
   */
  function esc_html_x($text, $context, $domain = 'default') {
    return esc_html(_x($text, $context, $domain));
  }
}

if (!function_exists('esc_attr__')) {
  /**
   * Retrieve the translation of $text and escape for safe use in an attribute.
   *
   * @param string $text   Text to translate.
   * @param string $domain Text domain. Default 'default'.
   * @return string Translated and escaped text.
   */
  function esc_attr__($text, $domain = 'default') {
    return esc_attr(__($text, $domain));
  }
}

if (!function_exists('esc_attr_e')) {
  /**
   * Display translated text escaped for safe use in an attribute.
   *
   * @param string $text   Text to translate.
   * @param string $domain Text domain. Default 'default'.
   */
  function esc_attr_e($text, $domain = 'default') {
    echo esc_attr__($text, $domain);
  }
}

if (!function_exists('esc_attr_x')) {
  /**
   * Translate string with context and escape for attribute.
   *
   * @param string $text    Text to translate.
   * @param string $context Context information for translators.
   * @param string $domain  Text domain. Default 'default'.
   * @return string Translated and escaped text.
   */
  function esc_attr_x($text, $context, $domain = 'default') {
    return esc_attr(_x($text, $context, $domain));
  }
}

if (!function_exists('load_theme_textdomain')) {
  /**
   * Load a theme's translated strings.
   *
   * Stub - always returns true. Actual translation loading not implemented.
   *
   * @param string $domain Text domain.
   * @param string $path   Path to .mo files.
   * @return bool Always true.
   */
  function load_theme_textdomain($domain, $path = false) {
    return true;
  }
}

if (!function_exists('number_format_i18n')) {
  /**
   * Convert float number to format based on the locale.
   *
   * @param float $number   The number to convert.
   * @param int   $decimals Precision of the number of decimal places.
   * @return string Converted number in string format.
   */
  function number_format_i18n($number, $decimals = 0) {
    return number_format($number, $decimals);
  }
}

