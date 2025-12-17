<?php
/**
 * @file
 * WordPress I/O Function Stubs for WP4BD
 *
 * This file contains stubs for all WordPress functions that perform I/O operations
 * (network requests, file operations, database queries). These stubs prevent
 * WordPress from accessing external resources and ensure it only gets data
 * from Backdrop via bridge files.
 *
 * All I/O operations are logged but return empty/failure results.
 *
 * @package WP4BD
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

// Debug: log that I/O stubs have been loaded
if (function_exists('watchdog')) {
  watchdog('wp4bd_debug', 'I/O stubs file loaded and executing', array(), WATCHDOG_DEBUG);
}

// =============================================================================
// NETWORK REQUEST STUBS
// =============================================================================

if (!function_exists('wp_remote_get')) {
  /**
   * Stub for wp_remote_get - prevents network requests.
   */
  function wp_remote_get($url, $args = array()) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'wp_remote_get blocked: @url', array('@url' => $url), WATCHDOG_DEBUG);
    }
    return new WP_Error('http_request_failed', 'Network requests disabled in WP4BD');
  }
}

if (!function_exists('wp_remote_post')) {
  /**
   * Stub for wp_remote_post - prevents network requests.
   */
  function wp_remote_post($url, $args = array()) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'wp_remote_post blocked: @url', array('@url' => $url), WATCHDOG_DEBUG);
    }
    return new WP_Error('http_request_failed', 'Network requests disabled in WP4BD');
  }
}

if (!function_exists('wp_remote_head')) {
  /**
   * Stub for wp_remote_head - prevents network requests.
   */
  function wp_remote_head($url, $args = array()) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'wp_remote_head blocked: @url', array('@url' => $url), WATCHDOG_DEBUG);
    }
    return new WP_Error('http_request_failed', 'Network requests disabled in WP4BD');
  }
}

if (!function_exists('wp_remote_request')) {
  /**
   * Stub for wp_remote_request - prevents network requests.
   */
  function wp_remote_request($url, $args = array()) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'wp_remote_request blocked: @url', array('@url' => $url), WATCHDOG_DEBUG);
    }
    return new WP_Error('http_request_failed', 'Network requests disabled in WP4BD');
  }
}

if (!function_exists('wp_remote_fopen')) {
  /**
   * Stub for wp_remote_fopen - prevents network requests.
   */
  function wp_remote_fopen($uri) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'wp_remote_fopen blocked: @uri', array('@uri' => $uri), WATCHDOG_DEBUG);
    }
    return false;
  }
}

if (!function_exists('wp_safe_remote_get')) {
  /**
   * Stub for wp_safe_remote_get - prevents network requests.
   */
  function wp_safe_remote_get($url, $args = array()) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'wp_safe_remote_get blocked: @url', array('@url' => $url), WATCHDOG_DEBUG);
    }
    return new WP_Error('http_request_failed', 'Network requests disabled in WP4BD');
  }
}

if (!function_exists('wp_safe_remote_post')) {
  /**
   * Stub for wp_safe_remote_post - prevents network requests.
   */
  function wp_safe_remote_post($url, $args = array()) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'wp_safe_remote_post blocked: @url', array('@url' => $url), WATCHDOG_DEBUG);
    }
    return new WP_Error('http_request_failed', 'Network requests disabled in WP4BD');
  }
}

// HTTP API helper stubs
if (!function_exists('wp_remote_retrieve_body')) {
  function wp_remote_retrieve_body($response) {
    return '';
  }
}

if (!function_exists('wp_remote_retrieve_headers')) {
  function wp_remote_retrieve_headers($response) {
    return array();
  }
}

if (!function_exists('wp_remote_retrieve_header')) {
  function wp_remote_retrieve_header($response, $header) {
    return '';
  }
}

if (!function_exists('wp_remote_retrieve_response_code')) {
  function wp_remote_retrieve_response_code($response) {
    return 0;
  }
}

if (!function_exists('wp_remote_retrieve_response_message')) {
  function wp_remote_retrieve_response_message($response) {
    return '';
  }
}

if (!function_exists('wp_remote_retrieve_cookies')) {
  function wp_remote_retrieve_cookies($response) {
    return array();
  }
}

// =============================================================================
// FILE SYSTEM STUBS
// =============================================================================

if (!function_exists('wp_handle_upload')) {
  /**
   * Stub for wp_handle_upload - prevents file uploads.
   */
  function wp_handle_upload($file, $overrides = array()) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'wp_handle_upload blocked', array(), WATCHDOG_DEBUG);
    }
    return array('error' => 'File uploads disabled in WP4BD');
  }
}

if (!function_exists('wp_handle_sideload')) {
  /**
   * Stub for wp_handle_sideload - prevents file uploads.
   */
  function wp_handle_sideload($file, $overrides = array()) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'wp_handle_sideload blocked', array(), WATCHDOG_DEBUG);
    }
    return array('error' => 'File uploads disabled in WP4BD');
  }
}

if (!function_exists('download_url')) {
  /**
   * Stub for download_url - prevents downloading files.
   */
  function download_url($url, $timeout = 300) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'download_url blocked: @url', array('@url' => $url), WATCHDOG_DEBUG);
    }
    return new WP_Error('download_error', 'File downloads disabled in WP4BD');
  }
}

if (!function_exists('wp_upload_dir')) {
  /**
   * Stub for wp_upload_dir - returns dummy upload directory info.
   */
  function wp_upload_dir($time = null) {
    $upload_dir = wp_normalize_path(ABSPATH . '../uploads');
    $upload_url = home_url('/uploads');

    if ($time) {
      $upload_dir = $upload_dir . '/' . $time;
      $upload_url = $upload_url . '/' . $time;
    }

    return array(
      'path' => $upload_dir,
      'url' => $upload_url,
      'subdir' => $time ? '/' . $time : '',
      'basedir' => wp_normalize_path(ABSPATH . '../uploads'),
      'baseurl' => home_url('/uploads'),
      'error' => false,
    );
  }
}

if (!function_exists('wp_is_writable')) {
  /**
   * Stub for wp_is_writable - always returns false.
   */
  function wp_is_writable($path) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'wp_is_writable blocked: @path', array('@path' => $path), WATCHDOG_DEBUG);
    }
    return false;
  }
}

if (!function_exists('win_is_writable')) {
  /**
   * Stub for win_is_writable - always returns false.
   */
  function win_is_writable($path) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'win_is_writable blocked: @path', array('@path' => $path), WATCHDOG_DEBUG);
    }
    return false;
  }
}

if (!function_exists('get_filesystem_method')) {
  /**
   * Stub for get_filesystem_method - returns 'direct' but filesystem operations are blocked.
   */
  function get_filesystem_method($args = array(), $context = false) {
    return 'direct';
  }
}

if (!function_exists('WP_Filesystem')) {
  /**
   * Stub for WP_Filesystem - prevents filesystem operations.
   */
  function WP_Filesystem($args = false, $context = false) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'WP_Filesystem blocked', array(), WATCHDOG_DEBUG);
    }
    return false;
  }
}

if (!function_exists('request_filesystem_credentials')) {
  /**
   * Stub for request_filesystem_credentials - prevents filesystem operations.
   */
  function request_filesystem_credentials($form_post, $type = '', $error = false, $context = false, $extra_fields = null) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'request_filesystem_credentials blocked', array(), WATCHDOG_DEBUG);
    }
    return false;
  }
}

// =============================================================================
// EMAIL STUBS
// =============================================================================

if (!function_exists('wp_mail')) {
  /**
   * Stub for wp_mail - prevents sending emails.
   */
  function wp_mail($to, $subject, $message, $headers = '', $attachments = array()) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'wp_mail blocked: @to', array('@to' => is_array($to) ? implode(', ', $to) : $to), WATCHDOG_DEBUG);
    }
    return false;
  }
}

// =============================================================================
// CACHE STUBS
// =============================================================================

if (!function_exists('wp_cache_get')) {
  /**
   * Stub for wp_cache_get - Backdrop handles caching.
   */
  function wp_cache_get($key, $group = '', &$found = null) {
    $found = false;
    return false;
  }
}

if (!function_exists('wp_cache_set')) {
  /**
   * Stub for wp_cache_set - Backdrop handles caching.
   */
  function wp_cache_set($key, $data, $group = '', $expire = 0) {
    return true;
  }
}

if (!function_exists('wp_cache_add')) {
  /**
   * Stub for wp_cache_add - Backdrop handles caching.
   */
  function wp_cache_add($key, $data, $group = '', $expire = 0) {
    return true;
  }
}

if (!function_exists('wp_cache_delete')) {
  /**
   * Stub for wp_cache_delete - Backdrop handles caching.
   */
  function wp_cache_delete($key, $group = '') {
    return true;
  }
}

if (!function_exists('wp_cache_flush')) {
  /**
   * Stub for wp_cache_flush - Backdrop handles caching.
   */
  function wp_cache_flush() {
    return true;
  }
}

// =============================================================================
// OPTION STUBS
// =============================================================================

if (!function_exists('get_option')) {
  /**
   * Stub for get_option - options come from Backdrop via bridge.
   */
  function get_option($option, $default = false) {
    // This should be implemented by wp-options-bridge.php
    // For now, return default
    return $default;
  }
}

if (!function_exists('update_option')) {
  /**
   * Stub for update_option - options are read-only in WP4BD.
   */
  function update_option($option, $value, $autoload = null) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'update_option blocked: @option', array('@option' => $option), WATCHDOG_DEBUG);
    }
    return false;
  }
}

if (!function_exists('add_option')) {
  /**
   * Stub for add_option - options are read-only in WP4BD.
   */
  function add_option($option, $value = '', $deprecated = '', $autoload = 'yes') {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'add_option blocked: @option', array('@option' => $option), WATCHDOG_DEBUG);
    }
    return false;
  }
}

if (!function_exists('delete_option')) {
  /**
   * Stub for delete_option - options are read-only in WP4BD.
   */
  function delete_option($option) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'delete_option blocked: @option', array('@option' => $option), WATCHDOG_DEBUG);
    }
    return false;
  }
}

// =============================================================================
// TRANSIENT STUBS
// =============================================================================

if (!function_exists('get_transient')) {
  /**
   * Stub for get_transient - transients disabled in WP4BD.
   */
  function get_transient($transient) {
    return false;
  }
}

if (!function_exists('set_transient')) {
  /**
   * Stub for set_transient - transients disabled in WP4BD.
   */
  function set_transient($transient, $value, $expiration = 0) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'set_transient blocked: @transient', array('@transient' => $transient), WATCHDOG_DEBUG);
    }
    return false;
  }
}

if (!function_exists('delete_transient')) {
  /**
   * Stub for delete_transient - transients disabled in WP4BD.
   */
  function delete_transient($transient) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'delete_transient blocked: @transient', array('@transient' => $transient), WATCHDOG_DEBUG);
    }
    return false;
  }
}

// =============================================================================
// CRON STUBS
// =============================================================================

if (!function_exists('wp_schedule_event')) {
  /**
   * Stub for wp_schedule_event - cron disabled in WP4BD.
   */
  function wp_schedule_event($timestamp, $recurrence, $hook, $args = array()) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'wp_schedule_event blocked: @hook', array('@hook' => $hook), WATCHDOG_DEBUG);
    }
    return false;
  }
}

if (!function_exists('wp_unschedule_event')) {
  /**
   * Stub for wp_unschedule_event - cron disabled in WP4BD.
   */
  function wp_unschedule_event($timestamp, $hook, $args = array()) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'wp_unschedule_event blocked: @hook', array('@hook' => $hook), WATCHDOG_DEBUG);
    }
    return false;
  }
}

if (!function_exists('wp_clear_scheduled_hook')) {
  /**
   * Stub for wp_clear_scheduled_hook - cron disabled in WP4BD.
   */
  function wp_clear_scheduled_hook($hook, $args = array()) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'wp_clear_scheduled_hook blocked: @hook', array('@hook' => $hook), WATCHDOG_DEBUG);
    }
    return false;
  }
}

if (!function_exists('wp_next_scheduled')) {
  /**
   * Stub for wp_next_scheduled - cron disabled in WP4BD.
   */
  function wp_next_scheduled($hook, $args = array()) {
    return false;
  }
}

if (!function_exists('wp_schedule_single_event')) {
  /**
   * Stub for wp_schedule_single_event - cron disabled in WP4BD.
   */
  function wp_schedule_single_event($timestamp, $hook, $args = array()) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'wp_schedule_single_event blocked: @hook', array('@hook' => $hook), WATCHDOG_DEBUG);
    }
    return false;
  }
}

// =============================================================================
// HTTP API CLASS STUBS
// =============================================================================

if (!class_exists('WP_Http')) {
  /**
   * Stub class for WP_Http - prevents HTTP requests.
   */
  class WP_Http {
    public function get($url, $args = array()) {
      if (function_exists('watchdog')) {
        watchdog('wp4bd_io', 'WP_Http::get blocked: @url', array('@url' => $url), WATCHDOG_DEBUG);
      }
      return new WP_Error('http_request_failed', 'HTTP requests disabled in WP4BD');
    }

    public function post($url, $args = array()) {
      if (function_exists('watchdog')) {
        watchdog('wp4bd_io', 'WP_Http::post blocked: @url', array('@url' => $url), WATCHDOG_DEBUG);
      }
      return new WP_Error('http_request_failed', 'HTTP requests disabled in WP4BD');
    }

    public function request($url, $args = array()) {
      if (function_exists('watchdog')) {
        watchdog('wp4bd_io', 'WP_Http::request blocked: @url', array('@url' => $url), WATCHDOG_DEBUG);
      }
      return new WP_Error('http_request_failed', 'HTTP requests disabled in WP4BD');
    }

    public function head($url, $args = array()) {
      if (function_exists('watchdog')) {
        watchdog('wp4bd_io', 'WP_Http::head blocked: @url', array('@url' => $url), WATCHDOG_DEBUG);
      }
      return new WP_Error('http_request_failed', 'HTTP requests disabled in WP4BD');
    }
  }
}

// =============================================================================
// FILESYSTEM API CLASS STUBS
// =============================================================================

if (!class_exists('WP_Filesystem_Base')) {
  /**
   * Stub class for WP_Filesystem_Base - prevents filesystem operations.
   */
  class WP_Filesystem_Base {
    public function __construct($arg) {}
    public function connect() { return false; }
    public function get_contents($file) { return false; }
    public function put_contents($file, $contents, $mode = false) { return false; }
    public function exists($file) { return false; }
    public function is_file($file) { return false; }
    public function is_dir($path) { return false; }
    public function dirlist($path, $include_hidden = true, $recursive = false) { return false; }
    public function mkdir($path, $chmod = false, $chown = false, $chgrp = false) { return false; }
    public function rmdir($path, $recursive = false) { return false; }
    public function delete($file, $recursive = false, $type = false) { return false; }
    public function chmod($file, $mode = false, $recursive = false) { return false; }
    public function chown($file, $owner, $recursive = false) { return false; }
    public function chgrp($file, $group, $recursive = false) { return false; }
    public function chdir($dir) { return false; }
    public function cwd() { return false; }
    public function size($file) { return false; }
    public function atime($file) { return false; }
    public function mtime($file) { return false; }
    public function touch($file, $time = 0, $atime = 0) { return false; }
    public function copy($source, $destination, $overwrite = false, $mode = false) { return false; }
    public function move($source, $destination, $overwrite = false) { return false; }
  }
}

if (!class_exists('WP_Filesystem_Direct')) {
  /**
   * Stub class for WP_Filesystem_Direct - prevents filesystem operations.
   */
  class WP_Filesystem_Direct extends WP_Filesystem_Base {
    public function __construct($arg) {
      if (function_exists('watchdog')) {
        watchdog('wp4bd_io', 'WP_Filesystem_Direct instantiated - filesystem operations blocked', array(), WATCHDOG_DEBUG);
      }
    }
  }
}

// =============================================================================
// UPDATE API STUBS
// =============================================================================

if (!function_exists('wp_version_check')) {
  /**
   * Stub for wp_version_check - prevents update checks.
   */
  function wp_version_check() {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'wp_version_check blocked', array(), WATCHDOG_DEBUG);
    }
  }
}

if (!function_exists('wp_update_plugins')) {
  /**
   * Stub for wp_update_plugins - prevents plugin updates.
   */
  function wp_update_plugins() {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'wp_update_plugins blocked', array(), WATCHDOG_DEBUG);
    }
  }
}

if (!function_exists('wp_update_themes')) {
  /**
   * Stub for wp_update_themes - prevents theme updates.
   */
  function wp_update_themes() {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'wp_update_themes blocked', array(), WATCHDOG_DEBUG);
    }
  }
}

// =============================================================================
// XML-RPC STUBS
// =============================================================================

if (!function_exists('xmlrpc_getposttitle')) {
  /**
   * Stub for xmlrpc_getposttitle - XML-RPC disabled.
   */
  function xmlrpc_getposttitle($content) {
    return $content;
  }
}

if (!function_exists('xmlrpc_getpostcategory')) {
  /**
   * Stub for xmlrpc_getpostcategory - XML-RPC disabled.
   */
  function xmlrpc_getpostcategory($content) {
    return array();
  }
}

if (!function_exists('xmlrpc_removepostdata')) {
  /**
   * Stub for xmlrpc_removepostdata - XML-RPC disabled.
   */
  function xmlrpc_removepostdata($content) {
    return $content;
  }
}

// =============================================================================
// DATABASE STUBS (already handled by db.php, but adding for completeness)
// =============================================================================

if (!function_exists('dbDelta')) {
  /**
   * Stub for dbDelta - database schema changes disabled.
   */
  function dbDelta($queries = '', $execute = true) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'dbDelta blocked', array(), WATCHDOG_DEBUG);
    }
    return array();
  }
}

if (!function_exists('maybe_create_table')) {
  /**
   * Stub for maybe_create_table - table creation disabled.
   */
  function maybe_create_table($table_name, $create_ddl) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'maybe_create_table blocked: @table', array('@table' => $table_name), WATCHDOG_DEBUG);
    }
    return false;
  }
}

if (!function_exists('maybe_add_column')) {
  /**
   * Stub for maybe_add_column - column addition disabled.
   */
  function maybe_add_column($table_name, $column_name, $create_ddl) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_io', 'maybe_add_column blocked: @table.@column', array('@table' => $table_name, '@column' => $column_name), WATCHDOG_DEBUG);
    }
    return false;
  }
}

// =============================================================================
// ESSENTIAL WORDPRESS FUNCTIONS (needed early by themes)
// =============================================================================

// Define essential WordPress globals
if (!isset($GLOBALS['wp_filter'])) {
  $GLOBALS['wp_filter'] = array();
}
if (!isset($GLOBALS['wp_actions'])) {
  $GLOBALS['wp_actions'] = array();
}
if (!isset($GLOBALS['wp_current_filter'])) {
  $GLOBALS['wp_current_filter'] = array();
}

// I/O stubs should only contain I/O-related functions
// General WordPress functions are loaded by WordPress core

// =============================================================================
// LOGGING STUBS (WordPress error logging)
// =============================================================================

if (!function_exists('error_log')) {
  /**
   * Override error_log to use Backdrop's watchdog.
   */
  function error_log($message, $message_type = 0, $destination = null, $extra_headers = null) {
    if (function_exists('watchdog')) {
      watchdog('wp4bd_error', 'WordPress error_log: @message', array('@message' => $message), WATCHDOG_ERROR);
    }
    return true;
  }
}

// =============================================================================
// UTILITY FUNCTIONS
// =============================================================================

if (!function_exists('wp_normalize_path')) {
  /**
   * Normalize a filesystem path.
   * This is safe to keep as-is since it doesn't perform I/O.
   */
  function wp_normalize_path($path) {
    $path = str_replace('\\', '/', $path);
    $path = preg_replace('|/+|', '/', $path);
    return $path;
  }
}

if (!function_exists('wp_is_stream')) {
  /**
   * Check if a path is a stream URL.
   * This is safe to keep as-is.
   */
  function wp_is_stream($path) {
    $scheme_separator = strpos($path, '://');
    if (false === $scheme_separator) {
      return false;
    }
    $stream = substr($path, 0, $scheme_separator);
    return in_array($stream, stream_get_wrappers(), true);
  }
}

// =============================================================================
// BACKDROP INTEGRATION HELPERS
// =============================================================================

/**
 * Check if we're in WP4BD environment.
 */
function wp4bd_is_active() {
  return defined('WP2BD_ACTIVE_THEME') || module_exists('wp_content');
}

/**
 * Log I/O blocking events with context.
 */
function wp4bd_log_io_block($function_name, $context = array()) {
  if (function_exists('watchdog')) {
    $message = 'WP4BD I/O blocked: @function';
    $variables = array('@function' => $function_name);
    if (!empty($context)) {
      $message .= ' - ' . implode(', ', array_map(function($k, $v) { return "$k: $v"; }, array_keys($context), $context));
      $variables = array_merge($variables, $context);
    }
    watchdog('wp4bd_io', $message, $variables, WATCHDOG_DEBUG);
  }
}
