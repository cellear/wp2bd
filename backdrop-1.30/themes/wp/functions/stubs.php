<?php
/**
 * @file
 * Stub implementations for WordPress functions not yet fully implemented.
 *
 * These functions return safe default values to prevent fatal errors
 * when WordPress themes call them.
 */

// ============================================================================
// BODY CLASS
// ============================================================================

if (!function_exists('body_class')) {
  /**
   * Display the classes for the body element.
   */
  function body_class($class = '') {
    $classes = get_body_class($class);
    echo 'class="' . join(' ', $classes) . '"';
  }
}

if (!function_exists('get_body_class')) {
  /**
   * Retrieve the classes for the body element as an array.
   */
  function get_body_class($class = '') {
    global $wp_query, $post;

    $classes = array();

    // Add post type
    if ($post) {
      $classes[] = 'single';
      $classes[] = 'single-' . sanitize_html_class($post->post_type);
      $classes[] = 'postid-' . $post->ID;
    }

    // Add page type classes
    if (is_front_page()) $classes[] = 'home';
    if (is_home()) $classes[] = 'blog';
    if (is_page()) $classes[] = 'page';
    if (is_single()) $classes[] = 'single';
    if (is_archive()) $classes[] = 'archive';
    if (is_search()) $classes[] = 'search';
    if (is_404()) $classes[] = 'error404';

    // Add logged-in class (always false in Backdrop context for now)
    $classes[] = 'logged-out';

    // Add custom classes
    if (!empty($class)) {
      if (!is_array($class)) {
        $class = preg_split('#\s+#', $class);
      }
      $classes = array_merge($classes, $class);
    }

    // Allow filtering
    $classes = apply_filters('body_class', $classes, $class);

    return array_unique($classes);
  }
}

// ============================================================================
// TRANSLATION FUNCTIONS
// ============================================================================

if (!function_exists('__')) {
  /**
   * Retrieve the translation of $text.
   */
  function __($text, $domain = 'default') {
    // Stub: Just return the text as-is
    return $text;
  }
}

if (!function_exists('_e')) {
  /**
   * Display translated text.
   */
  function _e($text, $domain = 'default') {
    echo __($text, $domain);
  }
}

if (!function_exists('_x')) {
  /**
   * Retrieve translated string with gettext context.
   */
  function _x($text, $context, $domain = 'default') {
    // Stub: Just return the text
    return $text;
  }
}

if (!function_exists('_ex')) {
  /**
   * Display translated string with gettext context.
   */
  function _ex($text, $context, $domain = 'default') {
    echo _x($text, $context, $domain);
  }
}

if (!function_exists('esc_html__')) {
  /**
   * Retrieve the translation of $text and escapes it for safe use in HTML output.
   */
  function esc_html__($text, $domain = 'default') {
    return esc_html(__($text, $domain));
  }
}

if (!function_exists('esc_html_e')) {
  /**
   * Display translated text that has been escaped for safe use in HTML output.
   */
  function esc_html_e($text, $domain = 'default') {
    echo esc_html__($text, $domain);
  }
}

if (!function_exists('esc_attr__')) {
  /**
   * Retrieve the translation of $text and escapes it for safe use in an attribute.
   */
  function esc_attr__($text, $domain = 'default') {
    return esc_attr(__($text, $domain));
  }
}

if (!function_exists('esc_attr_e')) {
  /**
   * Display translated text that has been escaped for safe use in an attribute.
   */
  function esc_attr_e($text, $domain = 'default') {
    echo esc_attr__($text, $domain);
  }
}

if (!function_exists('esc_attr_x')) {
  /**
   * Translate string with gettext context, and escape it for safe use in an attribute.
   */
  function esc_attr_x($text, $context, $domain = 'default') {
    return esc_attr(_x($text, $context, $domain));
  }
}

if (!function_exists('esc_html_x')) {
  /**
   * Translate string with gettext context, and escape it for safe use in HTML output.
   */
  function esc_html_x($text, $context, $domain = 'default') {
    return esc_html(_x($text, $context, $domain));
  }
}

// ============================================================================
// SCRIPT & STYLE ENQUEUING
// ============================================================================

if (!function_exists('wp_enqueue_script')) {
  /**
   * Enqueue a script.
   */
  function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) {
    // Stub: Log but don't actually enqueue
    // In a real implementation, this would add scripts to wp_head or wp_footer
    static $enqueued_scripts = array();
    $enqueued_scripts[$handle] = array(
      'src' => $src,
      'deps' => $deps,
      'ver' => $ver,
      'in_footer' => $in_footer,
    );
  }
}

if (!function_exists('wp_enqueue_style')) {
  /**
   * Enqueue a CSS stylesheet.
   */
  function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all') {
    // Stub: Log but don't actually enqueue
    static $enqueued_styles = array();
    $enqueued_styles[$handle] = array(
      'src' => $src,
      'deps' => $deps,
      'ver' => $ver,
      'media' => $media,
    );
  }
}

if (!function_exists('wp_register_script')) {
  /**
   * Register a script.
   */
  function wp_register_script($handle, $src, $deps = array(), $ver = false, $in_footer = false) {
    // Stub: Just accept it
    return true;
  }
}

if (!function_exists('wp_register_style')) {
  /**
   * Register a CSS stylesheet.
   */
  function wp_register_style($handle, $src, $deps = array(), $ver = false, $media = 'all') {
    // Stub: Just accept it
    return true;
  }
}

if (!function_exists('wp_localize_script')) {
  /**
   * Localize a script.
   */
  function wp_localize_script($handle, $object_name, $l10n) {
    // Stub: Just accept it
    return true;
  }
}

if (!function_exists('wp_add_inline_style')) {
  /**
   * Add inline CSS.
   */
  function wp_add_inline_style($handle, $data) {
    // Stub: Just accept it
    return true;
  }
}

// ============================================================================
// NAVIGATION MENUS
// ============================================================================

if (!function_exists('has_nav_menu')) {
  /**
   * Check whether a navigation menu location has a menu assigned to it.
   */
  function has_nav_menu($location) {
    // Stub: Return false (no menus registered)
    return false;
  }
}

if (!function_exists('wp_nav_menu')) {
  /**
   * Display navigation menu.
   */
  function wp_nav_menu($args = array()) {
    // Stub: Display a simple placeholder
    echo '<nav class="navigation"><ul><li><a href="' . esc_url(home_url('/')) . '">Home</a></li></ul></nav>';
  }
}

if (!function_exists('register_nav_menu')) {
  /**
   * Register a navigation menu location.
   */
  function register_nav_menu($location, $description) {
    // Stub: Just accept it
    return true;
  }
}

if (!function_exists('register_nav_menus')) {
  /**
   * Register multiple navigation menu locations.
   */
  function register_nav_menus($locations = array()) {
    // Stub: Just accept it
    return true;
  }
}

// ============================================================================
// COMMENT FUNCTIONS
// ============================================================================

if (!function_exists('comments_open')) {
  /**
   * Check whether comments are open for a post.
   */
  function comments_open($post_id = null) {
    // Stub: Return false (comments disabled)
    return false;
  }
}

if (!function_exists('get_comments_number')) {
  /**
   * Retrieve the number of comments a post has.
   */
  function get_comments_number($post_id = 0) {
    // Stub: Return 0
    return 0;
  }
}

if (!function_exists('comments_template')) {
  /**
   * Load the comment template.
   */
  function comments_template($file = '/comments.php', $separate_comments = false) {
    // Stub: Do nothing (no comments)
    return;
  }
}

if (!function_exists('wp_list_comments')) {
  /**
   * Display a list of comments.
   */
  function wp_list_comments($args = array(), $comments = null) {
    // Stub: Display nothing
    return;
  }
}

if (!function_exists('comment_form')) {
  /**
   * Output a complete commenting form.
   */
  function comment_form($args = array(), $post_id = null) {
    // Stub: Display nothing
    return;
  }
}

// ============================================================================
// PAGINATION
// ============================================================================

if (!function_exists('the_posts_pagination')) {
  /**
   * Display pagination links for posts.
   */
  function the_posts_pagination($args = array()) {
    // Stub: Display simple pagination
    echo '<nav class="navigation pagination"><div class="nav-links"><!-- Pagination placeholder --></div></nav>';
  }
}

if (!function_exists('the_post_navigation')) {
  /**
   * Display navigation to next/previous post.
   */
  function the_post_navigation($args = array()) {
    // Stub: Display simple next/prev
    echo '<nav class="navigation post-navigation"><div class="nav-links"><!-- Post navigation placeholder --></div></nav>';
  }
}

if (!function_exists('wp_link_pages')) {
  /**
   * Display page-links for paginated posts (<!--nextpage-->).
   */
  function wp_link_pages($args = '') {
    // Stub: This is complex, just return empty for now
    // The actual implementation in content-display.php handles <!--nextpage-->
    return;
  }
}

// ============================================================================
// TAXONOMY FUNCTIONS
// ============================================================================

if (!function_exists('get_the_category')) {
  /**
   * Retrieve post categories.
   */
  function get_the_category($post_id = false) {
    // Stub: Return empty array
    return array();
  }
}

if (!function_exists('the_category')) {
  /**
   * Display post categories.
   */
  function the_category($separator = '', $parents = '', $post_id = false) {
    // Stub: Display nothing
    return;
  }
}

if (!function_exists('get_the_tags')) {
  /**
   * Retrieve post tags.
   */
  function get_the_tags($post_id = 0) {
    // Stub: Return empty array
    return array();
  }
}

if (!function_exists('the_tags')) {
  /**
   * Display post tags.
   */
  function the_tags($before = null, $sep = ', ', $after = '') {
    // Stub: Display nothing
    return;
  }
}

if (!function_exists('get_the_category_list')) {
  /**
   * Retrieve category list for a post in either HTML list or custom format.
   */
  function get_the_category_list($separator = '', $parents = '', $post_id = false) {
    // Stub: Return empty string
    return '';
  }
}

if (!function_exists('get_the_tag_list')) {
  /**
   * Retrieve tag list for a post.
   */
  function get_the_tag_list($before = '', $sep = '', $after = '', $id = 0) {
    // Stub: Return empty string
    return '';
  }
}

// ============================================================================
// THEME SUPPORT
// ============================================================================

if (!function_exists('add_theme_support')) {
  /**
   * Register theme support for a given feature.
   */
  function add_theme_support($feature) {
    // Stub: Just accept it
    static $theme_support = array();
    $theme_support[$feature] = true;
    return true;
  }
}

if (!function_exists('current_theme_supports')) {
  /**
   * Check if current theme supports a feature.
   */
  function current_theme_supports($feature) {
    // Stub: Return false
    return false;
  }
}

// ============================================================================
// CUSTOMIZER
// ============================================================================

if (!function_exists('get_theme_mod')) {
  /**
   * Retrieve theme modification value.
   */
  function get_theme_mod($name, $default = false) {
    // Stub: Return default value
    return $default;
  }
}

if (!function_exists('set_theme_mod')) {
  /**
   * Update theme modification value.
   */
  function set_theme_mod($name, $value) {
    // Stub: Just accept it
    return true;
  }
}

// ============================================================================
// MISCELLANEOUS
// ============================================================================

if (!function_exists('is_customize_preview')) {
  /**
   * Check if we're in customizer preview mode.
   */
  function is_customize_preview() {
    // Stub: Always false
    return false;
  }
}

if (!function_exists('get_option')) {
  /**
   * Retrieve an option value based on option name.
   */
  function get_option($option, $default = false) {
    // Stub: Use Backdrop's variable_get
    if (function_exists('variable_get')) {
      return variable_get($option, $default);
    }
    return $default;
  }
}

if (!function_exists('update_option')) {
  /**
   * Update the value of an option.
   */
  function update_option($option, $value) {
    // Stub: Use Backdrop's variable_set
    if (function_exists('variable_set')) {
      variable_set($option, $value);
      return true;
    }
    return false;
  }
}

if (!function_exists('sanitize_html_class')) {
  /**
   * Sanitize a string to be used as an HTML class.
   */
  function sanitize_html_class($class, $fallback = '') {
    // Strip out any %-encoded octets
    $sanitized = preg_replace('|%[a-fA-F0-9][a-fA-F0-9]|', '', $class);

    // Limit to A-Z, a-z, 0-9, '_', '-'
    $sanitized = preg_replace('/[^A-Za-z0-9_-]/', '', $sanitized);

    if ('' === $sanitized && $fallback) {
      return sanitize_html_class($fallback);
    }

    return $sanitized;
  }
}
