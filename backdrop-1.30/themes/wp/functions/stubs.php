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
  function body_class($class = '')
  {
    $classes = get_body_class($class);
    echo 'class="' . join(' ', $classes) . '"';
  }
}

if (!function_exists('get_body_class')) {
  /**
   * Retrieve the classes for the body element as an array.
   */
  function get_body_class($class = '')
  {
    global $wp_query, $post;

    $classes = array();

    // Add post type
    if ($post) {
      $classes[] = 'single';
      $classes[] = 'single-' . sanitize_html_class($post->post_type);
      $classes[] = 'postid-' . $post->ID;
    }

    // Add page type classes
    if (is_front_page())
      $classes[] = 'home';
    if (is_home())
      $classes[] = 'blog';
    if (is_page())
      $classes[] = 'page';
    if (is_single())
      $classes[] = 'single';
    if (is_archive())
      $classes[] = 'archive';
    if (is_search())
      $classes[] = 'search';
    if (is_404())
      $classes[] = 'error404';

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
  function __($text, $domain = 'default')
  {
    // Stub: Just return the text as-is
    return $text;
  }
}

if (!function_exists('_e')) {
  /**
   * Display translated text.
   */
  function _e($text, $domain = 'default')
  {
    echo __($text, $domain);
  }
}

if (!function_exists('_x')) {
  /**
   * Retrieve translated string with gettext context.
   */
  function _x($text, $context, $domain = 'default')
  {
    // Stub: Just return the text
    return $text;
  }
}

if (!function_exists('_ex')) {
  /**
   * Display translated string with gettext context.
   */
  function _ex($text, $context, $domain = 'default')
  {
    echo _x($text, $context, $domain);
  }
}

if (!function_exists('esc_html__')) {
  /**
   * Retrieve the translation of $text and escapes it for safe use in HTML output.
   */
  function esc_html__($text, $domain = 'default')
  {
    return esc_html(__($text, $domain));
  }
}

if (!function_exists('esc_html_e')) {
  /**
   * Display translated text that has been escaped for safe use in HTML output.
   */
  function esc_html_e($text, $domain = 'default')
  {
    echo esc_html__($text, $domain);
  }
}

if (!function_exists('esc_attr__')) {
  /**
   * Retrieve the translation of $text and escapes it for safe use in an attribute.
   */
  function esc_attr__($text, $domain = 'default')
  {
    return esc_attr(__($text, $domain));
  }
}

if (!function_exists('esc_attr_e')) {
  /**
   * Display translated text that has been escaped for safe use in an attribute.
   */
  function esc_attr_e($text, $domain = 'default')
  {
    echo esc_attr__($text, $domain);
  }
}

if (!function_exists('esc_attr_x')) {
  /**
   * Translate string with gettext context, and escape it for safe use in an attribute.
   */
  function esc_attr_x($text, $context, $domain = 'default')
  {
    return esc_attr(_x($text, $context, $domain));
  }
}

if (!function_exists('esc_html_x')) {
  /**
   * Translate string with gettext context, and escape it for safe use in HTML output.
   */
  function esc_html_x($text, $context, $domain = 'default')
  {
    return esc_html(_x($text, $context, $domain));
  }
}

// ============================================================================
// TITLE & META
// ============================================================================

if (!function_exists('wp_title')) {
  /**
   * Display or retrieve page title for all areas of blog.
   *
   * @param string $sep Optional separator.
   * @param bool $display Whether to display or retrieve title.
   * @param string $seplocation Where to place the separator ('left' or 'right').
   * @return string|void String if $display is false, void otherwise.
   */
  function wp_title($sep = '&raquo;', $display = true, $seplocation = '')
  {
    global $wp_query, $post;

    $title = '';
    $site_name = get_bloginfo('name');

    // Get page title based on context
    if (is_single() || is_page()) {
      if ($post) {
        $title = get_the_title($post->ID);
      }
    } elseif (is_home() || is_front_page()) {
      $title = $site_name;
      $description = get_bloginfo('description');
      if ($description) {
        $title .= ' ' . $sep . ' ' . $description;
      }
      if ($display) {
        echo $title;
      }
      return $title;
    } elseif (is_archive()) {
      $title = 'Archives';
    } elseif (is_search()) {
      $title = 'Search Results';
    } elseif (is_404()) {
      $title = 'Page Not Found';
    }

    // Build full title with separator
    if ($title) {
      if ($seplocation == 'right') {
        $full_title = $title . ' ' . $sep . ' ' . $site_name;
      } else {
        $full_title = $site_name . ' ' . $sep . ' ' . $title;
      }
    } else {
      $full_title = $site_name;
    }

    if ($display) {
      echo $full_title;
    }
    return $full_title;
  }
}

// ============================================================================
// SCRIPT & STYLE ENQUEUING
// ============================================================================

if (!function_exists('wp_enqueue_script')) {
  /**
   * Enqueue a script.
   */
  function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false)
  {
    global $wp_scripts;
    if (!isset($wp_scripts)) {
      $wp_scripts = array('header' => array(), 'footer' => array());
    }

    $location = $in_footer ? 'footer' : 'header';
    $wp_scripts[$location][$handle] = array(
      'src' => $src,
      'deps' => $deps,
      'ver' => $ver,
    );

    // INTEGRATION: Add to Backdrop's JS system
    if (!empty($src)) {
      // Add version query string if specified
      if ($ver) {
        $src .= '?' . (is_string($ver) ? 'ver=' . $ver : 'ver=' . time());
      }

      // Log for debugging
      if (function_exists('watchdog')) {
        watchdog('wp_content', 'Enqueuing JS: @src', array('@src' => $src), WATCHDOG_DEBUG);
      }

      backdrop_add_js($src, array('type' => 'external', 'scope' => $location));
    }
  }
}

if (!function_exists('wp_enqueue_style')) {
  /**
   * WP_Styles class stub.
   */
  if (!class_exists('WP_Styles')) {
    class WP_Styles
    {
      public $registered = array();
      public $queue = array();

      public function add($handle, $src, $deps = array(), $ver = false, $media = 'all')
      {
        $this->registered[$handle] = array(
          'src' => $src,
          'deps' => $deps,
          'ver' => $ver,
          'media' => $media,
        );
        $this->queue[] = $handle;
        return true;
      }

      public function add_data($handle, $key, $value)
      {
        if (isset($this->registered[$handle])) {
          $this->registered[$handle][$key] = $value;
        }
        return true;
      }

      public function do_items()
      {
        // Stub for printing items
      }
    }
  }

  /**
   * Initialize $wp_styles global.
   */
  function wp_styles()
  {
    global $wp_styles;
    if (!isset($wp_styles) || !is_object($wp_styles)) {
      $wp_styles = new WP_Styles();
    }
    return $wp_styles;
  }

  /**
   * Enqueue a CSS stylesheet.
   */
  function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all')
  {
    $wp_styles = wp_styles();

    // If src is provided, register it
    if ($src) {
      $wp_styles->add($handle, $src, $deps, $ver, $media);
    }

    // INTEGRATION: Add to Backdrop's CSS system
    if (!empty($src)) {
      // Add version query string if specified
      if ($ver) {
        $src .= '?' . (is_string($ver) ? 'ver=' . $ver : 'ver=' . time());
      }

      // Log for debugging
      if (function_exists('watchdog')) {
        watchdog('wp_content', 'Enqueuing CSS: @src', array('@src' => $src), WATCHDOG_DEBUG);
      }

      backdrop_add_css($src, array('type' => 'external', 'media' => $media, 'group' => CSS_THEME, 'weight' => 100));
    }
  }

  /**
   * Print enqueued styles.
   */
  function wp_print_styles()
  {
    $wp_styles = wp_styles();

    foreach ($wp_styles->registered as $handle => $style) {
      $src = $style['src'];
      $ver = $style['ver'];
      $media = isset($style['media']) ? $style['media'] : 'all';

      // Add version query string if specified
      if ($ver) {
        $src .= '?' . (is_string($ver) ? 'ver=' . $ver : 'ver=' . time());
      }

      echo '<link rel="stylesheet" href="' . esc_url($src) . '" media="' . esc_attr($media) . '" />' . "\n";
    }
  }
}

if (!function_exists('wp_register_script')) {
  /**
   * Register a script.
   */
  function wp_register_script($handle, $src, $deps = array(), $ver = false, $in_footer = false)
  {
    // Stub: Just accept it
    return true;
  }
}

if (!function_exists('wp_print_scripts')) {
  /**
   * Print enqueued scripts.
   */
  function wp_print_scripts($in_footer = false)
  {
    global $wp_scripts;
    if (!isset($wp_scripts)) {
      return;
    }

    $location = $in_footer ? 'footer' : 'header';
    if (!isset($wp_scripts[$location])) {
      return;
    }

    foreach ($wp_scripts[$location] as $handle => $script) {
      $src = $script['src'];
      $ver = $script['ver'];

      // Add version query string if specified
      if ($ver) {
        $src .= '?' . (is_string($ver) ? 'ver=' . $ver : 'ver=' . time());
      }

      echo '<script type="text/javascript" src="' . esc_url($src) . '"></script>' . "\n";
    }
  }
}

if (!function_exists('wp_print_styles')) {
  /**
   * Print enqueued styles.
   */
  function wp_print_styles()
  {
    global $wp_styles;
    if (!isset($wp_styles)) {
      return;
    }

    foreach ($wp_styles as $handle => $style) {
      $src = $style['src'];
      $ver = $style['ver'];
      $media = isset($style['media']) ? $style['media'] : 'all';

      // Add version query string if specified
      if ($ver) {
        $src .= '?' . (is_string($ver) ? 'ver=' . $ver : 'ver=' . time());
      }

      echo '<link rel="stylesheet" href="' . esc_url($src) . '" media="' . esc_attr($media) . '" />' . "\n";
    }
  }
}

if (!function_exists('wp_register_style')) {
  /**
   * Register a CSS stylesheet.
   */
  function wp_register_style($handle, $src, $deps = array(), $ver = false, $media = 'all')
  {
    // Stub: Just accept it
    return true;
  }
}

if (!function_exists('wp_localize_script')) {
  /**
   * Localize a script.
   */
  function wp_localize_script($handle, $object_name, $l10n)
  {
    // Stub: Just accept it
    return true;
  }
}

if (!function_exists('wp_add_inline_style')) {
  /**
   * Add inline CSS.
   */
  function wp_add_inline_style($handle, $data)
  {
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
  function has_nav_menu($location)
  {
    // Stub: Return false (no menus registered)
    return false;
  }
}

if (!function_exists('wp_nav_menu')) {
  /**
   * Display navigation menu.
   */
  function wp_nav_menu($args = array())
  {
    // Stub: Display a simple placeholder
    echo '<nav class="navigation"><ul><li><a href="' . esc_url(home_url('/')) . '">Home</a></li></ul></nav>';
  }
}

if (!function_exists('register_nav_menu')) {
  /**
   * Register a navigation menu location.
   */
  function register_nav_menu($location, $description)
  {
    // Stub: Just accept it
    return true;
  }
}

if (!function_exists('register_nav_menus')) {
  /**
   * Register multiple navigation menu locations.
   */
  function register_nav_menus($locations = array())
  {
    // Stub: Just accept it
    return true;
  }
}

// ============================================================================
// SIDEBAR FUNCTIONS
// ============================================================================

if (!function_exists('register_sidebar')) {
  /**
   * Register a sidebar.
   */
  function register_sidebar($args = array())
  {
    // Stub: Just accept it
    static $sidebars = array();
    $id = isset($args['id']) ? $args['id'] : 'sidebar-' . count($sidebars);
    $sidebars[$id] = $args;
    return true;
  }
}

// Widget functions (is_active_sidebar, dynamic_sidebar) are now defined in widgets.php
// Removed old stubs to use the real implementations

// ============================================================================
// COMMENT FUNCTIONS
// ============================================================================

if (!function_exists('comments_open')) {
  /**
   * Check whether comments are open for a post.
   */
  function comments_open($post_id = null)
  {
    // Stub: Return false (comments disabled)
    return false;
  }
}

if (!function_exists('pings_open')) {
  /**
   * Check whether pings are open for a post.
   */
  function pings_open($post_id = null)
  {
    // Stub: Return false (pings disabled)
    return false;
  }
}

if (!function_exists('get_comments_number')) {
  /**
   * Retrieve the number of comments a post has.
   */
  function get_comments_number($post_id = 0)
  {
    // Stub: Return 0
    return 0;
  }
}

if (!function_exists('comments_template')) {
  /**
   * Load the comment template.
   */
  function comments_template($file = '/comments.php', $separate_comments = false)
  {
    // Stub: Do nothing (no comments)
    return;
  }
}

if (!function_exists('wp_list_comments')) {
  /**
   * Display a list of comments.
   */
  function wp_list_comments($args = array(), $comments = null)
  {
    // Stub: Display nothing
    return;
  }
}

if (!function_exists('comment_form')) {
  /**
   * Output a complete commenting form.
   */
  function comment_form($args = array(), $post_id = null)
  {
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
  function the_posts_pagination($args = array())
  {
    // Stub: Display simple pagination
    echo '<nav class="navigation pagination"><div class="nav-links"><!-- Pagination placeholder --></div></nav>';
  }
}

if (!function_exists('the_post_navigation')) {
  /**
   * Display navigation to next/previous post.
   */
  function the_post_navigation($args = array())
  {
    // Stub: Display simple next/prev
    echo '<nav class="navigation post-navigation"><div class="nav-links"><!-- Post navigation placeholder --></div></nav>';
  }
}

if (!function_exists('wp_link_pages')) {
  /**
   * Display page-links for paginated posts (<!--nextpage-->).
   */
  function wp_link_pages($args = '')
  {
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
  function get_the_category($post_id = false)
  {
    // Stub: Return empty array
    return array();
  }
}

if (!function_exists('the_category')) {
  /**
   * Display post categories.
   */
  function the_category($separator = '', $parents = '', $post_id = false)
  {
    // Stub: Display nothing
    return;
  }
}

if (!function_exists('get_the_tags')) {
  /**
   * Retrieve post tags.
   */
  function get_the_tags($post_id = 0)
  {
    // Stub: Return empty array
    return array();
  }
}

if (!function_exists('the_tags')) {
  /**
   * Display post tags.
   */
  function the_tags($before = null, $sep = ', ', $after = '')
  {
    // Stub: Display nothing
    return;
  }
}

if (!function_exists('get_the_category_list')) {
  /**
   * Retrieve category list for a post in either HTML list or custom format.
   */
  function get_the_category_list($separator = '', $parents = '', $post_id = false)
  {
    // Stub: Return empty string
    return '';
  }
}

if (!function_exists('get_the_tag_list')) {
  /**
   * Retrieve tag list for a post.
   */
  function get_the_tag_list($before = '', $sep = '', $after = '', $id = 0)
  {
    // Stub: Return empty string
    return '';
  }
}

if (!function_exists('get_object_taxonomies')) {
  /**
   * Return the names or objects of the taxonomies registered for the requested object or object type.
   */
  function get_object_taxonomies($object, $output = 'names')
  {
    // Stub: Return empty array for now
    // In full implementation, would return array of taxonomy names/objects for the object type
    return array();
  }
}

// ============================================================================
// THEME SUPPORT
// ============================================================================

if (!function_exists('add_theme_support')) {
  /**
   * Register theme support for a given feature.
   */
  function add_theme_support($feature)
  {
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
  function current_theme_supports($feature)
  {
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
  function get_theme_mod($name, $default = false)
  {
    // Stub: Use Backdrop's config API if available
    if (function_exists('config_get')) {
      $key = 'theme_mod_' . $name;
      $value = config_get('system.core', $key);
      return ($value !== NULL) ? $value : $default;
    }
    return $default;
  }
}

if (!function_exists('get_theme_support')) {
  /**
   * Check if current theme supports a feature.
   */
  function get_theme_support($feature)
  {
    // Stub: Return false
    return false;
  }
}

if (!function_exists('get_stylesheet_uri')) {
  /**
   * Retrieve stylesheet URI.
   */
  function get_stylesheet_uri()
  {
    return get_stylesheet_directory_uri() . '/style.css';
  }
}

if (!function_exists('get_stylesheet_directory_uri')) {
  /**
   * Retrieve stylesheet directory URI.
   */
  function get_stylesheet_directory_uri()
  {
    return get_template_directory_uri();
  }
}

if (!function_exists('get_template_directory_uri')) {
  /**
   * Retrieve template directory URI.
   */
  function get_template_directory_uri()
  {
    // Use the global theme path if available
    if (defined('WP2BD_THEME_PATH')) {
      return base_path() . WP2BD_THEME_PATH;
    }
    // Fallback
    return base_path() . 'themes/wp/wp-content/themes/' . (defined('WP2BD_ACTIVE_THEME') ? WP2BD_ACTIVE_THEME : 'twentysixteen');
  }
}

// ============================================================================
// POST THUMBNAILS
// ============================================================================

if (!function_exists('has_post_thumbnail')) {
  /**
   * Check if post has an image attached.
   */
  function has_post_thumbnail($post_id = null)
  {
    // Stub: Return false
    return false;
  }
}

if (!function_exists('the_post_thumbnail')) {
  /**
   * Display the post thumbnail.
   */
  function the_post_thumbnail($size = 'post-thumbnail', $attr = '')
  {
    // Stub: Display nothing
    return;
  }
}

if (!function_exists('get_the_post_thumbnail')) {
  /**
   * Retrieve the post thumbnail.
   */
  function get_the_post_thumbnail($post_id = null, $size = 'post-thumbnail', $attr = '')
  {
    // Stub: Return empty string
    return '';
  }
}

if (!function_exists('get_post_thumbnail_id')) {
  /**
   * Retrieve the post thumbnail ID.
   */
  function get_post_thumbnail_id($post_id = null)
  {
    // Stub: Return 0
    return 0;
  }
}

// ============================================================================
// USER FUNCTIONS
// ============================================================================

if (!function_exists('get_avatar')) {
  /**
   * Retrieve the avatar for a user who provided their email address or user ID.
   */
  function get_avatar($id_or_email, $size = 96, $default = '', $alt = '', $args = null)
  {
    // Stub: Return a generic avatar image or empty string
    // For now, return a simple placeholder image tag
    $url = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim(is_string($id_or_email) ? $id_or_email : ''))) . '?s=' . $size . '&d=mm';
    return '<img alt="' . esc_attr($alt) . '" src="' . esc_url($url) . '" class="avatar avatar-' . $size . ' photo" height="' . $size . '" width="' . $size . '" />';
  }
}

// ============================================================================
// POST CLASS FUNCTION
// ============================================================================

if (!function_exists('wp_attachment_is_image')) {
  /**
   * Check if the attachment is an image.
   */
  function wp_attachment_is_image($post = null)
  {
    // Stub: Return false for now
    return false;
  }
}

if (!function_exists('post_class')) {
  function post_class($class = '', $post_id = null)
  {
    global $post;

    $classes = array();

    if ($class) {
      if (!is_array($class)) {
        $class = preg_split('#\s+#', $class);
      }
      $classes = array_merge($classes, $class);
    }

    // Add basic post classes
    if ($post) {
      $classes[] = 'post-' . $post->ID;
      $classes[] = 'type-' . $post->post_type;
    }

    $classes = apply_filters('post_class', $classes, $class, $post_id);

    if (!empty($classes)) {
      echo 'class="' . esc_attr(join(' ', $classes)) . '"';
    }
  }
}

// ============================================================================
// TWENTY SEVENTEEN SPECIFIC STUBS
// ============================================================================

if (!function_exists('twentyseventeen_edit_link')) {
  function twentyseventeen_edit_link($post_id = null)
  {
    // Stub - would show edit link for logged in users
    return;
  }
}

if (!function_exists('is_page_template')) {
  /**
   * Check if page template is used.
   */
  function is_page_template($template = '')
  {
    // Stub: Return false
    return false;
  }
}

if (!class_exists('Walker')) {
  class Walker
  {
    public $tree_type;
    public $db_fields;
    public function walk($elements, $max_depth)
    {
      return '';
    }
    public function paged_walk($elements, $max_depth, $page_num, $per_page)
    {
      return '';
    }
  }
}

if (!class_exists('Walker_Page')) {
  class Walker_Page extends Walker
  {
    public function start_lvl(&$output, $depth = 0, $args = array())
    {
    }
    public function end_lvl(&$output, $depth = 0, $args = array())
    {
    }
    public function start_el(&$output, $page, $depth = 0, $args = array(), $current_page = 0)
    {
    }
    public function end_el(&$output, $page, $depth = 0, $args = array())
    {
    }
  }
}

if (!class_exists('WP_Widget')) {
  class WP_Widget
  {
    public $id_base;
    public $name;
    public $widget_options;
    public $control_options;
    public $number = false;
    public $id = false;
    public $updated = false;

    public function __construct($id_base, $name, $widget_options = array(), $control_options = array())
    {
    }
    public function widget($args, $instance)
    {
    }
    public function update($new_instance, $old_instance)
    {
      return $new_instance;
    }
    public function form($instance)
    {
      return 'noform';
    }
  }
}

if (!class_exists('WP_Theme')) {
  class WP_Theme
  {
    public $name;
    public $version;
    public $template;
    public $stylesheet;

    public function __construct($theme_dir = null, $theme_root = null)
    {
      $this->name = defined('WP2BD_ACTIVE_THEME') ? WP2BD_ACTIVE_THEME : 'twentysixteen';
      $this->version = '1.0';
      $this->template = $this->name;
      $this->stylesheet = $this->name;
    }

    public function get($header)
    {
      if ($header === 'Name')
        return $this->name;
      if ($header === 'Version')
        return $this->version;
      if ($header === 'Template')
        return $this->template;
      return '';
    }

    public function parent()
    {
      return false; // No parent theme
    }

    public function get_stylesheet()
    {
      return $this->stylesheet;
    }

    public function get_template()
    {
      return $this->template;
    }
  }
}

if (!function_exists('wp_get_theme')) {
  /**
   * Gets a WP_Theme object for a theme.
   */
  function wp_get_theme($stylesheet = null, $theme_root = null)
  {
    return new WP_Theme($stylesheet, $theme_root);
  }
}

if (!function_exists('is_child_theme')) {
  /**
   * Whether a child theme is in use.
   */
  function is_child_theme()
  {
    return false; // No child themes in our setup
  }
}

if (!function_exists('wp_parse_args')) {
  /**
   * Merge user defined arguments into defaults array.
   */
  function wp_parse_args($args, $defaults = '')
  {
    if (is_object($args)) {
      $r = get_object_vars($args);
    } elseif (is_array($args)) {
      $r = &$args;
    } else {
      parse_str($args, $r);
    }

    if (is_array($defaults)) {
      return array_merge($defaults, $r);
    }
    return $r;
  }
}

if (!function_exists('sanitize_key')) {
  /**
   * Sanitizes a string key.
   */
  function sanitize_key($key)
  {
    $key = strtolower($key);
    $key = preg_replace('/[^a-z0-9_\-]/', '', $key);
    return $key;
  }
}

if (!function_exists('absint')) {
  /**
   * Convert a value to non-negative integer.
   */
  function absint($maybeint)
  {
    return abs(intval($maybeint));
  }
}

if (!function_exists('wp_unslash')) {
  /**
   * Remove slashes from a string or array of strings.
   */
  function wp_unslash($value)
  {
    return stripslashes_deep($value);
  }
}

if (!function_exists('stripslashes_deep')) {
  /**
   * Navigates through an array and removes slashes from the values.
   */
  function stripslashes_deep($value)
  {
    if (is_array($value)) {
      $value = array_map('stripslashes_deep', $value);
    } elseif (is_object($value)) {
      $vars = get_object_vars($value);
      foreach ($vars as $key => $data) {
        $value->{$key} = stripslashes_deep($data);
      }
    } elseif (is_string($value)) {
      $value = stripslashes($value);
    }
    return $value;
  }
}

if (!function_exists('wp_slash')) {
  /**
   * Add slashes to a string or array of strings.
   */
  function wp_slash($value)
  {
    if (is_array($value)) {
      $value = array_map('wp_slash', $value);
    } elseif (is_string($value)) {
      $value = addslashes($value);
    }
    return $value;
  }
}

// ============================================================================
// URL UTILITY FUNCTIONS
// ============================================================================

if (!function_exists('add_query_arg')) {
  /**
   * Retrieve a modified URL query string.
   */
  function add_query_arg()
  {
    $args = func_get_args();
    if (is_array($args[0])) {
      if (count($args) < 2 || false === $args[1]) {
        $uri = $_SERVER['REQUEST_URI'];
      } else {
        $uri = $args[1];
      }
    } else {
      if (count($args) < 3 || false === $args[2]) {
        $uri = $_SERVER['REQUEST_URI'];
      } else {
        $uri = $args[2];
      }
    }

    $frag = strstr($uri, '#');
    if ($frag) {
      $uri = substr($uri, 0, -strlen($frag));
    } else {
      $frag = '';
    }

    if (0 === stripos($uri, 'http://') || 0 === stripos($uri, 'https://')) {
      $protocol = stripos($uri, 'https://') === 0 ? 'https://' : 'http://';
      $uri = substr($uri, strlen($protocol));
      $parts = explode('?', $uri, 2);
      if (isset($parts[1])) {
        $base = $parts[0] . '?';
        $query = $parts[1];
      } else {
        $base = $parts[0] . '?';
        $query = '';
      }
    } elseif (strpos($uri, '?') !== false) {
      list($base, $query) = explode('?', $uri, 2);
      $base .= '?';
    } else {
      $base = $uri . '?';
      $query = '';
    }

    parse_str($query, $qs);

    if (is_array($args[0])) {
      $kayvees = $args[0];
      $qs = array_merge($qs, $kayvees);
    } else {
      $qs[$args[0]] = $args[1];
    }

    foreach ($qs as $k => $v) {
      if ($v === false) {
        unset($qs[$k]);
      }
    }

    $ret = http_build_query($qs, '', '&');
    $ret = trim($ret, '?');
    $ret = preg_replace('#=(&|$)#', '$1', $ret);
    $ret = $base . $ret . $frag;
    $ret = rtrim($ret, '?');
    return (isset($protocol) ? $protocol : '') . $ret;
  }
}

if (!function_exists('remove_query_arg')) {
  /**
   * Removes an item or items from a query string.
   */
  function remove_query_arg($key, $query = false)
  {
    if (is_array($key)) {
      foreach ($key as $k) {
        $query = add_query_arg($k, false, $query);
      }
      return $query;
    }
    return add_query_arg($key, false, $query);
  }
}

// ============================================================================
// BULK STUBS FOR TWENTY TWELVE COMPATIBILITY
// ============================================================================

if (!function_exists('comment_class')) {
  function comment_class($class = '', $comment_id = null, $post_id = null, $echo = true)
  {
    if ($echo)
      echo 'class="comment"';
    else
      return 'class="comment"';
  }
}
if (!function_exists('comment_text')) {
  function comment_text($comment_ID = 0, $args = array())
  {
    echo '';
  }
}
if (!function_exists('have_comments')) {
  function have_comments()
  {
    return false;
  }
}
if (!function_exists('wp_get_current_commenter')) {
  function wp_get_current_commenter()
  {
    return array('comment_author' => '', 'comment_author_email' => '', 'comment_author_url' => '');
  }
}
if (!function_exists('comment_author_link')) {
  function comment_author_link($comment_ID = 0)
  {
    echo 'Author';
  }
}
if (!function_exists('get_comment_author_link')) {
  function get_comment_author_link($comment_ID = 0)
  {
    return 'Author';
  }
}
if (!function_exists('comment_reply_link')) {
  function comment_reply_link($args = array(), $comment = null, $post = null)
  {
    return '';
  }
}
if (!function_exists('comments_popup_link')) {
  function comments_popup_link($zero = false, $one = false, $more = false, $css_class = '', $none = false)
  {
    return;
  }
}
if (!function_exists('edit_comment_link')) {
  function edit_comment_link($text = null, $before = '', $after = '')
  {
    return;
  }
}
if (!function_exists('get_comment_date')) {
  function get_comment_date($format = '', $comment_ID = 0)
  {
    return '';
  }
}
if (!function_exists('get_comment_time')) {
  function get_comment_time($format = '', $gmt = false, $translate = true)
  {
    return '';
  }
}
if (!function_exists('get_comment_link')) {
  function get_comment_link($comment = null, $args = array())
  {
    return '#';
  }
}
if (!function_exists('get_comment_pages_count')) {
  function get_comment_pages_count($comments = null, $per_page = null, $threaded = null)
  {
    return 0;
  }
}
if (!function_exists('next_comments_link')) {
  function next_comments_link($label = '', $max_page = 0)
  {
    return;
  }
}
if (!function_exists('previous_comments_link')) {
  function previous_comments_link($label = '')
  {
    return;
  }
}

if (!function_exists('is_day')) {
  function is_day()
  {
    return false;
  }
}
if (!function_exists('is_month')) {
  function is_month()
  {
    return false;
  }
}
if (!function_exists('is_year')) {
  function is_year()
  {
    return false;
  }
}
if (!function_exists('get_the_date')) {
  function get_the_date($format = '', $post = null)
  {
    return date($format ?: 'F j, Y');
  }
}
if (!function_exists('get_the_time')) {
  function get_the_time($format = '', $post = null)
  {
    return date($format ?: 'g:i a');
  }
}

if (!function_exists('is_attachment')) {
  function is_attachment()
  {
    return false;
  }
}
if (!function_exists('wp_get_attachment_image')) {
  function wp_get_attachment_image($attachment_id, $size = 'thumbnail', $icon = false, $attr = '')
  {
    return '';
  }
}
if (!function_exists('wp_get_attachment_url')) {
  function wp_get_attachment_url($attachment_id)
  {
    return '';
  }
}
if (!function_exists('get_attachment_link')) {
  function get_attachment_link($attachment_id = null)
  {
    return '';
  }
}
if (!function_exists('next_image_link')) {
  function next_image_link($size = 'thumbnail', $text = false)
  {
    return;
  }
}
if (!function_exists('previous_image_link')) {
  function previous_image_link($size = 'thumbnail', $text = false)
  {
    return;
  }
}

if (!function_exists('next_post_link')) {
  function next_post_link($format = '%link', $link = '%title', $in_same_term = false, $excluded_terms = '', $taxonomy = 'category')
  {
    return;
  }
}
if (!function_exists('previous_post_link')) {
  function previous_post_link($format = '%link', $link = '%title', $in_same_term = false, $excluded_terms = '', $taxonomy = 'category')
  {
    return;
  }
}
if (!function_exists('next_posts_link')) {
  function next_posts_link($label = null, $max_page = 0)
  {
    return;
  }
}
if (!function_exists('previous_posts_link')) {
  function previous_posts_link($label = null)
  {
    return;
  }
}
if (!function_exists('wp_page_menu')) {
  function wp_page_menu($args = array())
  {
    return;
  }
}

if (!function_exists('the_author')) {
  function the_author()
  {
    echo 'Author';
  }
}
if (!function_exists('get_the_author')) {
  function get_the_author()
  {
    return 'Author';
  }
}
if (!function_exists('get_the_author_meta')) {
  function get_the_author_meta($field = '', $user_id = false)
  {
    return '';
  }
}
if (!function_exists('the_author_meta')) {
  function the_author_meta($field = '', $user_id = false)
  {
    echo '';
  }
}

if (!function_exists('single_cat_title')) {
  function single_cat_title($prefix = '', $display = true)
  {
    if ($display)
      echo '';
    else
      return '';
  }
}
if (!function_exists('single_tag_title')) {
  function single_tag_title($prefix = '', $display = true)
  {
    if ($display)
      echo '';
    else
      return '';
  }
}
if (!function_exists('tag_description')) {
  function tag_description($tag_id = 0)
  {
    return '';
  }
}
if (!function_exists('category_description')) {
  function category_description($category_id = 0)
  {
    return '';
  }
}
if (!function_exists('display_header_text')) {
  function display_header_text()
  {
    return true;
  }
}
if (!function_exists('header_image')) {
  function header_image()
  {
    return '';
  }
}
if (!function_exists('the_header_image_tag')) {
  function the_header_image_tag($attr = array())
  {
    return;
  }
}
if (!function_exists('register_block_pattern')) {
  function register_block_pattern($pattern_name, $pattern_properties)
  {
    return true;
  }
}
if (!function_exists('register_block_pattern_category')) {
  function register_block_pattern_category($category_name, $category_properties)
  {
    return true;
  }
}
if (!function_exists('set_post_thumbnail_size')) {
  function set_post_thumbnail_size($width = 0, $height = 0, $crop = false)
  {
    return;
  }
}
if (!function_exists('the_title_attribute')) {
  function the_title_attribute($args = '')
  {
    echo '';
  }
}
if (!function_exists('the_privacy_policy_link')) {
  function the_privacy_policy_link($before = '', $after = '')
  {
    return;
  }
}
if (!function_exists('get_children')) {
  function get_children($args = '', $output = OBJECT)
  {
    return array();
  }
}
