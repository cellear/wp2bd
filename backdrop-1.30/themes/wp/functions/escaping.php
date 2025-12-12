<?php
/**
 * WordPress Escaping and Sanitization Functions for WP2BD
 *
 * Critical security functions for preventing XSS attacks and ensuring safe output.
 * These functions must be used whenever outputting untrusted data to HTML.
 *
 * @package WP2BD
 * @subpackage Escaping
 * @since 1.0.0
 */

/**
 * Escape HTML entities for safe display in HTML context
 *
 * Converts special characters to HTML entities to prevent XSS attacks.
 * Use this function when outputting text content in HTML.
 *
 * Security: Prevents script injection by encoding <, >, &, ", and ' characters
 *
 * @since 1.0.0
 *
 * @param string $text The text to escape
 * @return string The escaped text, safe for HTML output
 */
function esc_html($text) {
    // Handle null and non-scalar values
    if (is_null($text)) {
        return '';
    }

    // Convert arrays/objects to string representation
    if (is_array($text) || is_object($text)) {
        $text = serialize($text);
    }

    // Ensure we have a string
    $text = (string) $text;

    // Handle empty strings
    if ($text === '') {
        return '';
    }

    // Escape HTML entities
    // ENT_QUOTES: Encode both double and single quotes
    // ENT_SUBSTITUTE: Replace invalid code unit sequences with Unicode replacement character
    // UTF-8: Specify encoding to prevent encoding-based attacks
    // Double-encoding is enabled by default (matches WordPress behavior)
    $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    return $escaped;
}

/**
 * Escape text for use in HTML attributes
 *
 * More restrictive than esc_html() - designed specifically for attribute context.
 * Use this when outputting dynamic values in HTML attributes like class, id, title, etc.
 *
 * Security: Prevents attribute-based XSS by encoding quotes and special characters
 *
 * @since 1.0.0
 *
 * @param string $text The text to escape for attribute use
 * @return string The escaped text, safe for use in HTML attributes
 */
function esc_attr($text) {
    // Handle null and non-scalar values
    if (is_null($text)) {
        return '';
    }

    // Convert arrays/objects to string representation
    if (is_array($text) || is_object($text)) {
        $text = serialize($text);
    }

    // Ensure we have a string
    $text = (string) $text;

    // Handle empty strings
    if ($text === '') {
        return '';
    }

    // Escape HTML entities - same as esc_html but in attribute context
    // Double-encoding is enabled by default (matches WordPress behavior)
    $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    // Additional safety: remove any potentially dangerous characters for attributes
    // Remove line breaks which can break attribute parsing
    $escaped = str_replace(["\r", "\n"], '', $escaped);

    return $escaped;
}

/**
 * Sanitize URL for safe output in HTML
 *
 * Validates the URL protocol and sanitizes the URL for display.
 * Rejects dangerous protocols like javascript: and data:
 *
 * Security: Critical for preventing XSS via href, src, and other URL attributes
 *
 * @since 1.0.0
 *
 * @param string      $url       The URL to sanitize
 * @param string[]    $protocols Optional. Array of allowed protocols. Default null (uses default protocols).
 * @param string      $_context  Optional. Context for sanitization. Default 'display'.
 * @return string The sanitized URL, or empty string if invalid
 */
function esc_url($url, $protocols = null, $_context = 'display') {
    // Handle null/empty
    if (is_null($url) || $url === '') {
        return '';
    }

    // Convert to string
    $url = (string) $url;

    // Trim whitespace
    $url = trim($url);

    if ($url === '') {
        return '';
    }

    // Default allowed protocols - comprehensive list for web safety
    if (is_null($protocols)) {
        $protocols = [
            'http',
            'https',
            'ftp',
            'ftps',
            'mailto',
            'news',
            'irc',
            'gopher',
            'nntp',
            'feed',
            'telnet',
        ];
    }

    // Strip any dangerous characters first
    // Remove null bytes and control characters
    $url = str_replace(["\0", "\r", "\n", "\t"], '', $url);

    // Decode URL encoding to catch obfuscated attacks like java%0script:
    // We'll check the decoded version for dangerous protocols
    $url_decoded = rawurldecode($url);
    $url_decoded = str_replace(["\0", "\r", "\n", "\t"], '', $url_decoded);

    // Handle relative URLs (no protocol)
    // Relative URLs are safe and should be allowed
    if (strpos($url_decoded, ':') === false ||
        preg_match('#^(/|\./)#', $url_decoded) ||
        preg_match('#^\?#', $url_decoded) ||
        preg_match('/^#/', $url_decoded)) {
        // Relative URL - encode special characters but allow
        return _esc_url_sanitize($url, $_context);
    }

    // Parse the URL to check protocol
    // Use decoded version to catch encoded attacks
    $parsed = @parse_url($url_decoded);

    if ($parsed === false) {
        // Invalid URL
        return '';
    }

    // Check if we have a scheme (protocol)
    if (!isset($parsed['scheme']) || $parsed['scheme'] === '') {
        // No scheme, treat as relative
        return _esc_url_sanitize($url, $_context);
    }

    $scheme = strtolower($parsed['scheme']);

    // Reject dangerous protocols
    $dangerous_protocols = ['javascript', 'data', 'vbscript', 'about', 'file'];
    if (in_array($scheme, $dangerous_protocols, true)) {
        return '';
    }

    // Check if protocol is in allowed list
    $allowed = false;
    foreach ($protocols as $protocol) {
        if (strtolower($protocol) === $scheme) {
            $allowed = true;
            break;
        }
    }

    if (!$allowed) {
        return '';
    }

    // Sanitize the URL
    return _esc_url_sanitize($url, $_context);
}

/**
 * Sanitize URL for database storage or redirects (without display encoding)
 *
 * Similar to esc_url() but skips HTML entity encoding.
 * Use this when storing URLs in database or for HTTP redirects.
 *
 * Security: Validates protocols but doesn't apply display transformations
 *
 * @since 1.0.0
 *
 * @param string   $url       The URL to sanitize
 * @param string[] $protocols Optional. Array of allowed protocols. Default null.
 * @return string The sanitized URL for database/redirect use
 */
function esc_url_raw($url, $protocols = null) {
    // Use esc_url with 'db' context to skip display encoding
    return esc_url($url, $protocols, 'db');
}

/**
 * Internal helper to sanitize URL based on context
 *
 * @access private
 * @since 1.0.0
 *
 * @param string $url     The URL to sanitize
 * @param string $context The context: 'display' or 'db'
 * @return string The sanitized URL
 */
function _esc_url_sanitize($url, $context = 'display') {
    // Remove any remaining dangerous characters
    $url = str_replace(' ', '%20', $url);

    // For display context, encode HTML entities to prevent XSS
    if ($context === 'display') {
        // Encode ampersands for HTML display
        $url = str_replace('&', '&amp;', $url);

        // Encode quotes
        $url = str_replace('"', '&quot;', $url);
        $url = str_replace("'", '&#039;', $url);

        // Encode less-than/greater-than
        $url = str_replace('<', '&lt;', $url);
        $url = str_replace('>', '&gt;', $url);
    }

    return $url;
}

/**
 * Escape a URL for use in a redirect
 *
 * Wrapper around esc_url_raw() for semantic clarity in redirect contexts.
 *
 * @since 1.0.0
 *
 * @param string   $url       The URL to escape for redirect
 * @param string[] $protocols Optional. Array of allowed protocols.
 * @return string The escaped URL
 */
function esc_url_redirect($url, $protocols = null) {
    return esc_url_raw($url, $protocols);
}

/**
 * Escape JavaScript string for inline JavaScript
 *
 * Escapes text for safe use in JavaScript strings.
 * Use when embedding dynamic data in <script> tags.
 *
 * @since 1.0.0
 *
 * @param string $text The text to escape
 * @return string The escaped text, safe for JavaScript strings
 */
function esc_js($text) {
    // Handle null
    if (is_null($text)) {
        return '';
    }

    $text = (string) $text;

    // Escape backslashes first
    $text = str_replace('\\', '\\\\', $text);

    // Escape quotes
    $text = str_replace("'", "\\'", $text);
    $text = str_replace('"', '\\"', $text);

    // Escape newlines
    $text = str_replace("\r", '\\r', $text);
    $text = str_replace("\n", '\\n', $text);

    // Escape HTML entities to prevent breaking out of script context
    $text = str_replace('</', '<\\/', $text);

    return $text;
}

/**
 * Escape text for use in textarea
 *
 * Escapes text for safe display in textarea elements.
 *
 * @since 1.0.0
 *
 * @param string $text The text to escape
 * @return string The escaped text
 */
function esc_textarea($text) {
    // Textarea uses HTML context but preserves whitespace
    return esc_html($text);
}

/**
 * Sanitize text field input
 *
 * Removes tags and encodes special characters.
 * Use for sanitizing user input from text fields.
 *
 * @since 1.0.0
 *
 * @param string $text The text to sanitize
 * @return string The sanitized text
 */
function sanitize_text_field($text) {
    if (is_null($text)) {
        return '';
    }

    $text = (string) $text;

    // Strip all HTML tags
    $text = strip_tags($text);

    // Remove any remaining control characters
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text);

    // Normalize whitespace
    $text = preg_replace('/\s+/', ' ', $text);

    // Trim
    $text = trim($text);

    return $text;
}
