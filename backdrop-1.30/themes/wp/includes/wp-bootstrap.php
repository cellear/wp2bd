<?php
/**
 * @file
 * WordPress Bootstrap Entry Point for WP4BD V2
 *
 * Initializes WordPress core with Backdrop-specific configuration.
 * This provides a controlled entry point to load WordPress without
 * allowing database connections or external I/O.
 *
 * Strategy:
 * - Set WordPress constants pointing to wpbrain/ directory
 * - Load WordPress class definitions and constants
 * - DO NOT load wp-settings.php (connects to database)
 * - Database interception will be handled via db.php drop-in (Epic 3)
 *
 * @see DOCS/V2/2025-01-15-WORDPRESS-CORE-INTEGRATION-PLAN.md
 */

/**
 * Bootstrap WordPress core with Backdrop-specific configuration.
 *
 * This function initializes WordPress in "headless" mode - loading core
 * classes and functions but preventing database access and external I/O.
 *
 * @return bool
 *   TRUE if WordPress bootstrapped successfully, FALSE on error.
 */
function wp4bd_bootstrap_wordpress() {
  // Track errors
  $errors = array();

  try {
    // Determine wpbrain path (relative to Backdrop theme)
    $theme_path = backdrop_get_path('theme', 'wp');
    if (empty($theme_path)) {
      $errors[] = 'WordPress theme (wp) not found';
      return FALSE;
    }

    $wpbrain_path = BACKDROP_ROOT . '/' . $theme_path . '/wpbrain';

    // Verify wpbrain directory exists
    if (!is_dir($wpbrain_path)) {
      $errors[] = "WordPress core directory not found: {$wpbrain_path}";
      return FALSE;
    }

    // Define WordPress constants
    // ABSPATH must end with trailing slash
    if (!defined('ABSPATH')) {
      define('ABSPATH', $wpbrain_path . '/');
    }

    if (!defined('WPINC')) {
      define('WPINC', 'wp-includes');
    }

    if (!defined('WP_CONTENT_DIR')) {
      define('WP_CONTENT_DIR', $wpbrain_path . '/wp-content');
    }

    if (!defined('WP_CONTENT_URL')) {
      // URL to WordPress content directory (for accessing assets)
      // This needs to match the theme path accessible via web
      $theme_url = url('', array('absolute' => TRUE)) . 'themes/wp/wpbrain/wp-content';
      define('WP_CONTENT_URL', $theme_url);
    }

    if (!defined('STYLESHEETPATH')) {
      // Path to active theme's stylesheet directory
      define('STYLESHEETPATH', WP_CONTENT_DIR . '/themes/' . WP2BD_ACTIVE_THEME);
    }

    if (!defined('TEMPLATEPATH')) {
      // Path to active theme's template directory (same as STYLESHEETPATH for non-child themes)
      define('TEMPLATEPATH', WP_CONTENT_DIR . '/themes/' . WP2BD_ACTIVE_THEME);
    }

    if (!defined('WP_LANG_DIR')) {
      // Path to languages directory
      define('WP_LANG_DIR', WP_CONTENT_DIR . '/languages');
    }

    if (!defined('AUTOSAVE_INTERVAL')) {
      // Autosave interval in seconds (WordPress default: 60)
      define('AUTOSAVE_INTERVAL', 60);
    }

    // Verify critical WordPress files exist
    $wp_includes_path = ABSPATH . WPINC;
    if (!is_dir($wp_includes_path)) {
      $errors[] = "WordPress wp-includes directory not found: {$wp_includes_path}";
      return FALSE;
    }

    // Check for critical WordPress class files
    $critical_files = array(
      ABSPATH . WPINC . '/class-wp-post.php',
      ABSPATH . WPINC . '/class-wp-query.php',
    );

    foreach ($critical_files as $file) {
      if (!file_exists($file)) {
        $errors[] = "Critical WordPress file not found: {$file}";
        return FALSE;
      }
    }

    // WP4BD V2-051: Load WordPress core files in correct sequence

    // Step 1: Set table prefix (WordPress expects this global)
    if (!isset($GLOBALS['table_prefix'])) {
      $GLOBALS['table_prefix'] = 'wp_';
    }

    // Set WordPress version global (themes check this)
    if (!isset($GLOBALS['wp_version'])) {
      $GLOBALS['wp_version'] = '4.9.0';
    }

    // Initialize shortcode_tags global (used by wptexturize)
    if (!isset($GLOBALS['shortcode_tags'])) {
      $GLOBALS['shortcode_tags'] = array();
    }

    // Define WP_Rewrite stub class if not already defined
    if (!class_exists('WP_Rewrite')) {
      class WP_Rewrite {
        public $author_base = 'author';
        public $author_structure = '';
        public $permalink_structure = '';
        public $use_trailing_slashes = true;

        public function get_author_permastruct() {
          return '';
        }

        public function using_permalinks() {
          return !empty($this->permalink_structure);
        }
      }
    }

    // Define WP_Error stub class if not already defined
    if (!class_exists('WP_Error')) {
      class WP_Error {
        public $errors = array();
        public $error_data = array();

        public function __construct($code = '', $message = '', $data = '') {
          if (empty($code)) {
            return;
          }
          $this->errors[$code][] = $message;
          if (!empty($data)) {
            $this->error_data[$code] = $data;
          }
        }

        public function get_error_codes() {
          return array_keys($this->errors);
        }

        public function get_error_code() {
          $codes = $this->get_error_codes();
          return empty($codes) ? '' : $codes[0];
        }

        public function get_error_messages($code = '') {
          if (empty($code)) {
            $all_messages = array();
            foreach ((array) $this->errors as $code => $messages) {
              $all_messages = array_merge($all_messages, $messages);
            }
            return $all_messages;
          }
          return isset($this->errors[$code]) ? $this->errors[$code] : array();
        }

        public function get_error_message($code = '') {
          if (empty($code)) {
            $code = $this->get_error_code();
          }
          $messages = $this->get_error_messages($code);
          return empty($messages) ? '' : $messages[0];
        }

        public function get_error_data($code = '') {
          if (empty($code)) {
            $code = $this->get_error_code();
          }
          return isset($this->error_data[$code]) ? $this->error_data[$code] : null;
        }

        public function add($code, $message, $data = '') {
          $this->errors[$code][] = $message;
          if (!empty($data)) {
            $this->error_data[$code] = $data;
          }
        }
      }
    }

    if (!function_exists('is_wp_error')) {
      /**
       * Check whether variable is a WordPress Error.
       * @param mixed $thing Variable to check.
       * @return bool True if $thing is an object of the WP_Error class.
       */
      function is_wp_error($thing) {
        return ($thing instanceof WP_Error);
      }
    }

    // Initialize wp_rewrite global (used for permalink structure)
    if (!isset($GLOBALS['wp_rewrite'])) {
      $GLOBALS['wp_rewrite'] = new WP_Rewrite();
    }

    // Step 2: Load db.php drop-in FIRST (Epic 3) - prevents WordPress database connection
    // IMPORTANT: Check the ACTUAL path where db.php should be, not just WP_CONTENT_DIR
    // because WP_CONTENT_DIR might have been defined elsewhere with a different value
    $expected_db_dropin = $wpbrain_path . '/wp-content/db.php';

    if (file_exists($expected_db_dropin)) {
      require_once $expected_db_dropin;
      // Verify wpdb class is now loaded from our drop-in
      if (!class_exists('wpdb')) {
        $errors[] = 'db.php drop-in loaded but wpdb class not found';
        return FALSE;
      }
    } else {
      $errors[] = "db.php drop-in not found at: {$expected_db_dropin} - WordPress would try to connect to database!";
      return FALSE;
    }

    // Step 3: Load WordPress core via wp-settings.php
    // This loads all WordPress functions and classes
    // Our db.php drop-in prevents database connection

    // First define essential constants/stubs that wp-settings expects

    // Define essential WordPress functions that conflict with Backdrop or are needed early
    if (!function_exists('wp_installing')) {
      /**
       * Check whether WordPress is in installation mode.
       * @return bool False - we're not installing WordPress
       */
      function wp_installing() {
        return FALSE;
      }
    }

    if (!function_exists('is_admin')) {
      /**
       * Check if we're in WordPress admin area.
       * @return bool False - we're only doing front-end rendering
       */
      function is_admin() {
        return FALSE;
      }
    }

    if (!function_exists('is_multisite')) {
      /**
       * Check if WordPress is in multisite mode.
       * @return bool False - we're not using multisite
       */
      function is_multisite() {
        return FALSE;
      }
    }

    if (!function_exists('get_current_blog_id')) {
      /**
       * Get current blog ID (multisite function).
       * @return int Always 1 for single site
       */
      function get_current_blog_id() {
        return 1;
      }
    }

    if (!function_exists('get_site_option')) {
      /**
       * Get site option (multisite function).
       * For single site, just use get_option.
       * @param string $option Option name
       * @param mixed $default Default value
       * @return mixed Option value
       */
      function get_site_option($option, $default = FALSE) {
        return get_option($option, $default);
      }
    }

    if (!function_exists('is_admin_bar_showing')) {
      /**
       * Check if WordPress admin bar should be shown.
       * @return bool False - we don't show the WP admin bar (Backdrop has its own)
       */
      function is_admin_bar_showing() {
        return FALSE;
      }
    }

    if (!function_exists('is_ssl')) {
      /**
       * Check if SSL is being used.
       * @return bool True if SSL, false otherwise
       */
      function is_ssl() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
      }
    }

    if (!function_exists('force_ssl_admin')) {
      /**
       * Check if SSL should be forced for admin.
       * @return bool False - we don't force SSL
       */
      function force_ssl_admin() {
        return FALSE;
      }
    }

    // TODO: TEMPORARY - Theme functions not loading correctly
    // This should be in theme's inc/template-functions.php
    if (!function_exists('is_user_logged_in')) {
      /**
       * Check if current user is logged in.
       * @return bool True if user is logged in (via Backdrop)
       */
      function is_user_logged_in() {
        global $user;
        return !empty($user->uid);
      }
    }

    // Stub cache functions - we use Backdrop's caching instead
    if (!function_exists('wp_cache_get')) {
      function wp_cache_get($key, $group = '', &$found = null) {
        $found = FALSE;
        return FALSE;
      }
    }
    if (!function_exists('wp_cache_set')) {
      function wp_cache_set($key, $data, $group = '', $expire = 0) {
        return TRUE;
      }
    }
    if (!function_exists('wp_cache_add')) {
      function wp_cache_add($key, $data, $group = '', $expire = 0) {
        return TRUE;
      }
    }
    if (!function_exists('wp_cache_delete')) {
      function wp_cache_delete($key, $group = '') {
        return TRUE;
      }
    }
    if (!function_exists('wp_using_ext_object_cache')) {
      /**
       * Check if external object cache is being used.
       * @return bool False - we use Backdrop's caching
       */
      function wp_using_ext_object_cache() {
        return FALSE;
      }
    }
    if (!function_exists('wp_cache_add_non_persistent_groups')) {
      /**
       * Mark cache groups as non-persistent (don't save to permanent cache).
       * @param string|array $groups Cache group(s) to mark as non-persistent
       */
      function wp_cache_add_non_persistent_groups($groups) {
        // No-op - we use Backdrop's caching
      }
    }

    // WordPress utility functions
    // Note: Most utility functions now loaded from functions.php

    if (!function_exists('wp_parse_args')) {
      /**
       * Merge user defined arguments into defaults array.
       * @param string|array|object $args Value to merge with $defaults.
       * @param array $defaults Array that serves as the defaults.
       * @return array Merged user defined values with defaults.
       */
      function wp_parse_args($args, $defaults = '') {
        if (is_object($args)) {
          $r = get_object_vars($args);
        } elseif (is_array($args)) {
          $r =& $args;
        } else {
          parse_str($args, $r);
        }

        if (is_array($defaults)) {
          return array_merge($defaults, $r);
        }
        return $r;
      }
    }

    if (!function_exists('absint')) {
      /**
       * Convert a value to non-negative integer.
       * @param mixed $maybeint Data to be converted to non-negative integer.
       * @return int Non-negative integer.
       */
      function absint($maybeint) {
        return abs(intval($maybeint));
      }
    }

    if (!function_exists('wp_json_encode')) {
      /**
       * Encode data to JSON with WordPress-specific handling.
       * @param mixed $data Data to encode
       * @param int $options Optional json_encode options
       * @param int $depth Optional maximum depth
       * @return string|false JSON string or false on failure
       */
      function wp_json_encode($data, $options = 0, $depth = 512) {
        return json_encode($data, $options, $depth);
      }
    }

    if (!function_exists('mysql2date')) {
      /**
       * Convert MySQL datetime to PHP date format.
       * @param string $format PHP date format
       * @param string $date MySQL datetime string
       * @param bool $translate Whether to translate (use date_i18n)
       * @return string|int|false Formatted date or false on error
       */
      function mysql2date($format, $date, $translate = TRUE) {
        if (empty($date)) {
          return FALSE;
        }

        if ('G' == $format) {
          return strtotime($date . ' +0000');
        }

        $i = strtotime($date);

        if ('U' == $format) {
          return $i;
        }

        if ($translate && function_exists('date_i18n')) {
          return date_i18n($format, $i);
        }
        else {
          return date($format, $i);
        }
      }
    }

    if (!function_exists('add_query_arg')) {
      /**
       * Add or modify query string parameters in a URL.
       * Simplified stub - supports common use cases.
       * @param string|array $key Parameter name or array of parameters
       * @param string $value Parameter value (if $key is string)
       * @param string|bool $url URL to modify (FALSE = current URL)
       * @return string Modified URL
       */
      function add_query_arg($key, $value = '', $url = FALSE) {
        if (is_array($key)) {
          // First arg is array of params, second arg is URL
          $params = $key;
          $url = ($value !== FALSE && $value !== '') ? $value : $_SERVER['REQUEST_URI'];
        }
        else {
          // Standard: key, value, url
          $params = array($key => $value);
          if (func_num_args() > 2) {
            $url = func_get_arg(2);
          }
          if ($url === FALSE || $url === '') {
            $url = $_SERVER['REQUEST_URI'];
          }
        }

        // Parse URL
        $parsed = parse_url($url);
        $query_string = isset($parsed['query']) ? $parsed['query'] : '';

        // Parse existing query
        parse_str($query_string, $existing);

        // Merge parameters
        $merged = array_merge($existing, $params);

        // Build new query string
        $new_query = http_build_query($merged);

        // Rebuild URL
        $result = '';
        if (isset($parsed['scheme'])) {
          $result .= $parsed['scheme'] . '://';
        }
        if (isset($parsed['host'])) {
          $result .= $parsed['host'];
        }
        if (isset($parsed['port'])) {
          $result .= ':' . $parsed['port'];
        }
        if (isset($parsed['path'])) {
          $result .= $parsed['path'];
        }
        if ($new_query) {
          $result .= '?' . $new_query;
        }
        if (isset($parsed['fragment'])) {
          $result .= '#' . $parsed['fragment'];
        }

        return $result;
      }
    }

    if (!function_exists('remove_query_arg')) {
      /**
       * Remove query string parameters from a URL.
       * @param string|array $key Parameter name(s) to remove
       * @param string|bool $url URL to modify (FALSE = current URL)
       * @return string Modified URL
       */
      function remove_query_arg($key, $url = FALSE) {
        if ($url === FALSE) {
          $url = $_SERVER['REQUEST_URI'];
        }

        $keys = is_array($key) ? $key : array($key);

        // Parse URL
        $parsed = parse_url($url);
        $query_string = isset($parsed['query']) ? $parsed['query'] : '';

        // Parse existing query
        parse_str($query_string, $existing);

        // Remove specified keys
        foreach ($keys as $k) {
          unset($existing[$k]);
        }

        // Build new query string
        $new_query = http_build_query($existing);

        // Rebuild URL
        $result = '';
        if (isset($parsed['scheme'])) {
          $result .= $parsed['scheme'] . '://';
        }
        if (isset($parsed['host'])) {
          $result .= $parsed['host'];
        }
        if (isset($parsed['port'])) {
          $result .= ':' . $parsed['port'];
        }
        if (isset($parsed['path'])) {
          $result .= $parsed['path'];
        }
        if ($new_query) {
          $result .= '?' . $new_query;
        }
        if (isset($parsed['fragment'])) {
          $result .= '#' . $parsed['fragment'];
        }

        return $result;
      }
    }

    // Utility functions from functions.php (safe subset - no I/O)
    // Note: We do NOT load full functions.php as it contains dangerous I/O operations
    // (wp_remote_fopen, file operations, etc.) See DOCS/dec17-CLAUDE-FUNCTIONS-ANALYSIS.md

    if (!function_exists('_cleanup_header_comment')) {
      /**
       * Strip close comment and close php tags from file headers.
       * @param string $str Header comment to clean up
       * @return string Cleaned up header comment
       */
      function _cleanup_header_comment($str) {
        return trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $str));
      }
    }

    if (!function_exists('wp_allowed_protocols')) {
      /**
       * Retrieve list of allowed URL protocols.
       * @return array Array of allowed protocols
       */
      function wp_allowed_protocols() {
        static $protocols = array();

        if (empty($protocols)) {
          $protocols = array('http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp', 'webcal', 'urn');
        }

        if (function_exists('did_action') && !did_action('wp_loaded')) {
          if (function_exists('apply_filters')) {
            $protocols = array_unique((array) apply_filters('kses_allowed_protocols', $protocols));
          }
        }

        return $protocols;
      }
    }

    if (!function_exists('wp_guess_url')) {
      /**
       * Guess the site URL (used by script-loader).
       * @return string Site URL
       */
      function wp_guess_url() {
        // We know the site URL - it's the Backdrop site URL
        return url('', array('absolute' => TRUE));
      }
    }

    if (!function_exists('_wp_translate_php_url_constant_to_key')) {
      /**
       * Translate PHP_URL_* constant to array key name.
       * @param int $constant PHP_URL_* constant
       * @return string|false Key name or false
       */
      function _wp_translate_php_url_constant_to_key($constant) {
        $translation = array(
          PHP_URL_SCHEME   => 'scheme',
          PHP_URL_HOST     => 'host',
          PHP_URL_PORT     => 'port',
          PHP_URL_USER     => 'user',
          PHP_URL_PASS     => 'pass',
          PHP_URL_PATH     => 'path',
          PHP_URL_QUERY    => 'query',
          PHP_URL_FRAGMENT => 'fragment',
        );

        return isset($translation[$constant]) ? $translation[$constant] : FALSE;
      }
    }

    if (!function_exists('_get_component_from_parsed_url_array')) {
      /**
       * Get specific component from parsed URL array.
       * @param array $url_parts Parsed URL array
       * @param int $component Component to retrieve
       * @return mixed Component value or array
       */
      function _get_component_from_parsed_url_array($url_parts, $component = -1) {
        if (-1 === $component) {
          return $url_parts;
        }

        $key = _wp_translate_php_url_constant_to_key($component);
        if (FALSE !== $key && is_array($url_parts) && isset($url_parts[$key])) {
          return $url_parts[$key];
        }
        else {
          return NULL;
        }
      }
    }

    if (!function_exists('wp_parse_url')) {
      /**
       * Parse URL with support for schemeless URLs.
       * @param string $url URL to parse
       * @param int $component Optional component to retrieve
       * @return mixed Parsed URL array or component
       */
      function wp_parse_url($url, $component = -1) {
        $to_unset = array();
        $url = strval($url);

        if ('//' === substr($url, 0, 2)) {
          $to_unset[] = 'scheme';
          $url = 'placeholder:' . $url;
        }
        elseif ('/' === substr($url, 0, 1)) {
          $to_unset[] = 'scheme';
          $to_unset[] = 'host';
          $url = 'placeholder://placeholder' . $url;
        }

        $parts = @parse_url($url);

        if (FALSE === $parts) {
          return $parts;
        }

        // Remove placeholder values
        foreach ($to_unset as $key) {
          unset($parts[$key]);
        }

        return _get_component_from_parsed_url_array($parts, $component);
      }
    }

    if (!function_exists('mbstring_binary_safe_encoding')) {
      /**
       * Set mbstring encoding to binary-safe for WordPress operations.
       * @param bool $reset Whether to reset encoding
       */
      function mbstring_binary_safe_encoding($reset = FALSE) {
        // No-op for now - we assume binary-safe encoding
      }
    }

    if (!function_exists('reset_mbstring_encoding')) {
      /**
       * Reset mbstring encoding after binary-safe operations.
       */
      function reset_mbstring_encoding() {
        // No-op for now
      }
    }

    if (!function_exists('wp_load_alloptions')) {
      /**
       * Load all WordPress options (autoload options from database).
       * In our bridge, we return empty array since we use get_option() bridge.
       * @return array Empty array (options accessed via get_option bridge)
       */
      function wp_load_alloptions() {
        // Return empty array - all options accessed through get_option() bridge
        return array();
      }
    }

    if (!function_exists('current_user_can')) {
      /**
       * Check if current user has a capability.
       * For read-only display, we return FALSE (no permissions).
       * @param string $capability Capability to check
       * @return bool Always FALSE (no permissions for read-only display)
       */
      function current_user_can($capability) {
        // For read-only display, user has no edit capabilities
        return FALSE;
      }
    }

    if (!function_exists('get_transient')) {
      /**
       * Get the value of a transient (temporary cached data).
       * For now, always return FALSE (no cache).
       * @param string $transient Transient name
       * @return mixed FALSE (no cached value)
       */
      function get_transient($transient) {
        // No caching for now - always return FALSE
        return FALSE;
      }
    }

    if (!function_exists('set_transient')) {
      /**
       * Set/update the value of a transient.
       * For now, this is a no-op.
       * @param string $transient Transient name
       * @param mixed $value Transient value
       * @param int $expiration Time until expiration in seconds
       * @return bool TRUE on success
       */
      function set_transient($transient, $value, $expiration = 0) {
        // No caching for now - no-op
        return TRUE;
      }
    }

    if (!function_exists('delete_transient')) {
      /**
       * Delete a transient.
       * For now, this is a no-op.
       * @param string $transient Transient name
       * @return bool TRUE on success
       */
      function delete_transient($transient) {
        // No caching for now - no-op
        return TRUE;
      }
    }

    if (!function_exists('is_active_sidebar')) {
      /**
       * Check if a sidebar has widgets.
       * For now, always return FALSE (no widgets).
       * @param string|int $index Sidebar index/name/ID
       * @return bool FALSE (no active widgets)
       */
      function is_active_sidebar($index) {
        // No widgets for now
        return FALSE;
      }
    }

    if (!function_exists('comments_open')) {
      /**
       * Check if comments are open for a post.
       * For read-only display, always return FALSE.
       * @param int|WP_Post $post_id Post ID or object
       * @return bool FALSE (comments closed for read-only)
       */
      function comments_open($post_id = NULL) {
        // Comments closed for read-only display
        return FALSE;
      }
    }

    if (!function_exists('pings_open')) {
      /**
       * Check if pings/trackbacks are open for a post.
       * For read-only display, always return FALSE.
       * @param int|WP_Post $post_id Post ID or object
       * @return bool FALSE (pings closed for read-only)
       */
      function pings_open($post_id = NULL) {
        // Pings closed for read-only display
        return FALSE;
      }
    }

    if (!function_exists('get_metadata')) {
      /**
       * Retrieve metadata for an object.
       * Stub for now - returns FALSE (no metadata).
       * TODO: Bridge to Backdrop's field system
       * @param string $meta_type Type of object metadata is for (post, comment, user)
       * @param int $object_id ID of the object metadata is for
       * @param string $meta_key Optional. Metadata key
       * @param bool $single Optional. Return single value
       * @return mixed FALSE for now (no metadata)
       */
      function get_metadata($meta_type, $object_id, $meta_key = '', $single = FALSE) {
        // Stub: return FALSE (no metadata)
        // TODO: Bridge to Backdrop fields when needed
        return FALSE;
      }
    }

    if (!function_exists('has_post_thumbnail')) {
      /**
       * Check if post has a featured image (post thumbnail).
       * For now, return FALSE (no thumbnails).
       * TODO: Bridge to Backdrop image fields
       * @param int|WP_Post $post Optional. Post ID or object
       * @return bool FALSE (no featured images for now)
       */
      function has_post_thumbnail($post = NULL) {
        // Stub: no featured images for now
        return FALSE;
      }
    }

    if (!function_exists('get_post_thumbnail_id')) {
      /**
       * Get the ID of the post thumbnail (featured image).
       * For now, return FALSE (no thumbnails).
       * @param int|WP_Post $post Optional. Post ID or object
       * @return int|false FALSE (no thumbnails)
       */
      function get_post_thumbnail_id($post = NULL) {
        // Stub: no featured images for now
        return FALSE;
      }
    }

    if (!function_exists('get_post_format')) {
      /**
       * Retrieve the post format (e.g., 'aside', 'gallery', 'video').
       * For now, return FALSE (standard post format).
       * @param int|WP_Post $post Optional. Post ID or object
       * @return string|false FALSE for standard format
       */
      function get_post_format($post = NULL) {
        // Stub: standard format only for now
        return FALSE;
      }
    }

    if (!function_exists('get_the_post_thumbnail')) {
      /**
       * Retrieve the post thumbnail (featured image) HTML.
       * @param int|WP_Post|null $post Post ID or post object.
       * @param string|array $size Optional. Image size. Default 'post-thumbnail'.
       * @param string|array $attr Optional. Query string or array of attributes.
       * @return string Post thumbnail HTML or empty string.
       */
      function get_the_post_thumbnail($post = NULL, $size = 'post-thumbnail', $attr = '') {
        // Stub: no featured images for now
        return '';
      }
    }

    if (!function_exists('wp_make_content_images_responsive')) {
      /**
       * Filter content to add responsive srcset to images.
       * @param string $content HTML content
       * @return string Filtered content
       */
      function wp_make_content_images_responsive($content) {
        // Stub: just return content unchanged
        return $content;
      }
    }

    if (!function_exists('do_shortcode')) {
      /**
       * Search content for shortcodes and filter shortcodes through their hooks.
       * @param string $content Content to search for shortcodes.
       * @param bool $ignore_html Optional. When true, shortcodes inside HTML elements will be skipped.
       * @return string Content with shortcodes filtered out.
       */
      function do_shortcode($content, $ignore_html = FALSE) {
        // Stub: no shortcode support for now, just return content unchanged
        return $content;
      }
    }

    if (!function_exists('get_categories')) {
      /**
       * Retrieve list of category objects.
       * @param string|array $args Optional. Arguments to retrieve categories.
       * @return array List of category objects.
       */
      function get_categories($args = '') {
        // Stub: return empty array (no categories for now)
        // In the future, we could map to Backdrop taxonomy
        return array();
      }
    }

    if (!function_exists('get_comments_number')) {
      /**
       * Retrieve the number of comments a post has.
       * @param int|WP_Post $post Optional. Post ID or WP_Post object. Default is global $post.
       * @return string|int The number of comments as a numeric string or integer.
       */
      function get_comments_number($post = 0) {
        // Stub: return 0 (no comments for now)
        // In the future, we could map to Backdrop comments
        return '0';
      }
    }

    if (!function_exists('get_avatar')) {
      /**
       * Retrieve the avatar `<img>` tag for a user, email address, MD5 hash, comment, or post.
       * @param mixed $id_or_email User ID, email, comment object, or post object.
       * @param int $size Optional. Avatar size in pixels. Default 96.
       * @param string $default Optional. Default avatar URL.
       * @param string $alt Optional. Alt text.
       * @param array $args Optional. Extra arguments.
       * @return string|false Avatar img tag or false on failure.
       */
      function get_avatar($id_or_email, $size = 96, $default = '', $alt = '', $args = null) {
        // Stub: return empty string (no avatars for now)
        // In the future, we could use Gravatar or Backdrop's user picture field
        return '';
      }
    }

    if (!function_exists('_close_comments_for_old_posts')) {
      /**
       * Close comments on old posts on the fly, without updating the database.
       * @param bool $open Comments open status.
       * @param int $post_id Post ID.
       * @return bool Comments open status.
       */
      function _close_comments_for_old_posts($open, $post_id) {
        // Stub: just return the current open status
        return $open;
      }
    }

    // Note: get_post_type_object() is defined in post.php which we load later

    if (!function_exists('get_file_data')) {
      /**
       * Retrieve metadata from a file (theme/plugin headers).
       * @param string $file Path to the file
       * @param array $default_headers List of headers to look for
       * @param string $context Optional. Context for extra headers filter
       * @return array Array of file header values
       */
      function get_file_data($file, $default_headers, $context = '') {
        // Read first 8KB of file
        $fp = fopen($file, 'r');
        $file_data = fread($fp, 8192);
        fclose($fp);

        // Normalize line endings
        $file_data = str_replace("\r", "\n", $file_data);

        // Allow plugins to add extra headers
        if ($context && function_exists('apply_filters')) {
          $extra_headers = apply_filters("extra_{$context}_headers", array());
          if ($extra_headers) {
            $extra_headers = array_combine($extra_headers, $extra_headers);
            $all_headers = array_merge($extra_headers, (array) $default_headers);
          }
          else {
            $all_headers = $default_headers;
          }
        }
        else {
          $all_headers = $default_headers;
        }

        // Extract header values
        foreach ($all_headers as $field => $regex) {
          if (preg_match('/^[ \t\/*#@]*' . preg_quote($regex, '/') . ':(.*)$/mi', $file_data, $match) && $match[1]) {
            $all_headers[$field] = _cleanup_header_comment($match[1]);
          }
          else {
            $all_headers[$field] = '';
          }
        }

        return $all_headers;
      }
    }

    if (!function_exists('wp_filter_object_list')) {
      /**
       * Filter a list of objects, based on a set of key => value arguments.
       * Safe to include - uses WP_List_Util class (pure array manipulation, no I/O)
       *
       * @param array $list An array of objects to filter.
       * @param array $args Optional. An array of key => value arguments to match.
       * @param string $operator Optional. The logical operation ('and', 'or', 'not').
       * @param bool|string $field Optional. A field from the object to place instead of entire object.
       * @return array Array of found values.
       */
      function wp_filter_object_list($list, $args = array(), $operator = 'and', $field = false) {
        if (!is_array($list)) {
          return array();
        }

        $util = new WP_List_Util($list);
        $util->filter($args, $operator);

        if ($field) {
          $util->pluck($field);
        }

        return $util->get_output();
      }
    }

    if (!function_exists('wp_list_filter')) {
      /**
       * Filters a list of objects, based on a set of key => value arguments.
       *
       * @param array $list An array of objects to filter.
       * @param array $args Optional. An array of key => value arguments to match.
       * @param string $operator Optional. The logical operation ('AND', 'OR', 'NOT').
       * @return array Array of found values.
       */
      function wp_list_filter($list, $args = array(), $operator = 'AND') {
        if (!is_array($list)) {
          return array();
        }

        $util = new WP_List_Util($list);
        return $util->filter($args, $operator);
      }
    }

    if (!function_exists('wp_list_pluck')) {
      /**
       * Pluck a certain field out of each object in a list.
       *
       * @param array $list List of objects or arrays.
       * @param int|string $field Field from the object to place instead of entire object.
       * @param int|string $index_key Optional. Field to use as keys for the new array.
       * @return array Array of found values.
       */
      function wp_list_pluck($list, $field, $index_key = null) {
        if (!is_array($list)) {
          return array();
        }

        $util = new WP_List_Util($list);
        return $util->pluck($field, $index_key);
      }
    }

    // Load plugin system FIRST (needed for hooks)
    // CRITICAL: This must load before ANY classes that might trigger theme loading
    require_once ABSPATH . WPINC . '/plugin.php';

    // Load class definitions
    require_once ABSPATH . WPINC . '/class-wp-post.php';
    require_once ABSPATH . WPINC . '/class-wp-query.php';
    require_once ABSPATH . WPINC . '/class-wp-hook.php';
    require_once ABSPATH . WPINC . '/class-wp-list-util.php';
    require_once ABSPATH . WPINC . '/class-wp-tax-query.php';
    require_once ABSPATH . WPINC . '/class-wp-meta-query.php';
    require_once ABSPATH . WPINC . '/class-wp-user.php';
    require_once ABSPATH . WPINC . '/class-wp-theme.php';  // Note: reads local template files

    // Note: script/style classes loaded by script-loader.php below

    // Load formatting and escaping
    require_once ABSPATH . WPINC . '/formatting.php';
    require_once ABSPATH . WPINC . '/kses.php';

    // Load post functions
    require_once ABSPATH . WPINC . '/post.php';
    require_once ABSPATH . WPINC . '/query.php';

    // Load user functions
    require_once ABSPATH . WPINC . '/user.php';

    // Load taxonomy functions
    require_once ABSPATH . WPINC . '/taxonomy.php';

    // Load navigation menu functions
    require_once ABSPATH . WPINC . '/nav-menu.php';

    // Load template functions
    require_once ABSPATH . WPINC . '/post-template.php';
    require_once ABSPATH . WPINC . '/general-template.php';
    require_once ABSPATH . WPINC . '/link-template.php';
    require_once ABSPATH . WPINC . '/author-template.php';
    require_once ABSPATH . WPINC . '/category-template.php';

    // Load theme support
    require_once ABSPATH . WPINC . '/theme.php';
    require_once ABSPATH . WPINC . '/template.php';

    // Load internationalization
    require_once ABSPATH . WPINC . '/pomo/translations.php';
    require_once ABSPATH . WPINC . '/l10n.php';

    // Load script and style enqueue system
    // Note: script-loader.php loads functions.wp-styles.php and functions.wp-scripts.php
    require_once ABSPATH . WPINC . '/script-loader.php';  // wp_enqueue_scripts(), wp_print_styles(), etc.

    // Load default WordPress hooks/filters (sets up wp_head hooks for enqueueing)
    require_once ABSPATH . WPINC . '/default-filters.php';

    // Remove hooks for WordPress features we don't need in read-only display
    // These would cause errors since we don't load their supporting files
    // NOTE: This is intentional - we're removing admin/API features we don't want
    // Priorities must match those in default-filters.php
    remove_action('wp_head', 'wp_post_preview_js', 1);           // Admin preview (priority 1)
    remove_action('wp_head', 'rest_output_link_wp_head', 10);     // REST API discovery (priority 10)
    remove_action('wp_head', 'wp_oembed_add_discovery_links', 10); // oEmbed discovery (priority 10)
    remove_action('wp_head', 'wp_oembed_add_host_js', 10);        // oEmbed JavaScript (priority 10)
    remove_action('wp_footer', 'wp_admin_bar_render', 1000);      // Admin toolbar
    remove_action('wp_head', 'wp_generator', 10);                 // WordPress version meta tag (security)

    // DO NOT load functions.php - it conflicts with our stubs
    // require_once ABSPATH . WPINC . '/functions.php';

    // DO NOT load WordPress's option.php - we provide our own get_option() via wp-options-bridge.php
    // require_once ABSPATH . WPINC . '/option.php';

    // Step 4: Initialize $wpdb global using our drop-in class
    global $wpdb;
    if (!isset($wpdb)) {
      // Create wpdb instance with fake credentials (db.php drop-in won't actually connect)
      $wpdb = new wpdb('backdrop_user', 'no_password_needed', 'backdrop_db', 'localhost');
    }

    // Step 5: Load globals initialization from Epic 4
    $globals_init_file = dirname(__FILE__) . '/wp-globals-init.php';
    if (file_exists($globals_init_file)) {
      require_once $globals_init_file;
    }

    // Step 6: Load data bridge files from Epic 7
    $post_bridge_file = dirname(__FILE__) . '/wp-post-bridge.php';
    if (file_exists($post_bridge_file)) {
      require_once $post_bridge_file;
    }

    $user_bridge_file = dirname(__FILE__) . '/wp-user-bridge.php';
    if (file_exists($user_bridge_file)) {
      require_once $user_bridge_file;
    }

    // Pluggable functions (from pluggable.php) - stub safely without loading that file
    // Note: We do NOT load pluggable.php as it contains wp_mail() which sends email (I/O)
    if (!function_exists('get_userdata')) {
      /**
       * Retrieve user info by user ID.
       * @param int $user_id User ID
       * @return WP_User|false WP_User object on success, false on failure.
       */
      function get_userdata($user_id) {
        return get_user_by('id', $user_id);
      }
    }

    if (!function_exists('get_user_by')) {
      /**
       * Retrieve user info by a given field.
       * @param string $field The field to retrieve the user with. id | ID | slug | email | login.
       * @param int|string $value A value for $field.
       * @return WP_User|false WP_User object on success, false on failure.
       */
      function get_user_by($field, $value) {
        $userdata = WP_User::get_data_by($field, $value);

        if (!$userdata) {
          return FALSE;
        }

        $user = new WP_User();
        $user->init($userdata);

        return $user;
      }
    }

    $term_bridge_file = dirname(__FILE__) . '/wp-term-bridge.php';
    if (file_exists($term_bridge_file)) {
      require_once $term_bridge_file;
    }

    $options_bridge_file = dirname(__FILE__) . '/wp-options-bridge.php';
    if (file_exists($options_bridge_file)) {
      require_once $options_bridge_file;
    }

    // Success - WordPress core loaded, database intercepted, ready for rendering
    return TRUE;

  } catch (Exception $e) {
    $errors[] = 'Exception during WordPress bootstrap: ' . $e->getMessage();
    return FALSE;
  } catch (Error $e) {
    $errors[] = 'Error during WordPress bootstrap: ' . $e->getMessage();
    return FALSE;
  } finally {
    // Log any errors
    if (!empty($errors)) {
      if (function_exists('watchdog')) {
        foreach ($errors as $error) {
          watchdog('wp4bd', 'WordPress bootstrap error: @error', array('@error' => $error), WATCHDOG_ERROR);
        }
      }
    }
  }
}

/**
 * Get WordPress core information.
 *
 * Returns information about the WordPress core installation for debugging.
 *
 * @return array
 *   Array with keys:
 *   - abspath: WordPress ABSPATH constant
 *   - wpinc: WordPress WPINC constant
 *   - wp_content_dir: WordPress WP_CONTENT_DIR constant
 *   - exists: Whether WordPress core files exist
 *   - version: WordPress version (if available)
 */
function wp4bd_get_wordpress_info() {
  $info = array(
    'abspath' => defined('ABSPATH') ? ABSPATH : NULL,
    'wpinc' => defined('WPINC') ? WPINC : NULL,
    'wp_content_dir' => defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : NULL,
    'exists' => FALSE,
    'version' => NULL,
  );

  // Check if WordPress exists
  if (defined('ABSPATH') && is_dir(ABSPATH)) {
    $info['exists'] = TRUE;

    // Try to get WordPress version
    $version_file = ABSPATH . WPINC . '/version.php';
    if (file_exists($version_file)) {
      // WordPress version.php sets $wp_version global
      include $version_file;
      if (isset($wp_version)) {
        $info['version'] = $wp_version;
      }
    }
  }

  return $info;
}

