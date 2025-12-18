<?php
/**
 * WP2BD Utilities Functions
 *
 * Essential WordPress utility functions for site URLs, blog information,
 * and theme directory access, mapped to Backdrop equivalents.
 *
 * Functions implemented:
 * - home_url()         - Get site home URL with optional path
 * - bloginfo()         - Display blog information
 * - get_bloginfo()     - Get blog information
 * - get_template_directory()     - Get current theme directory path
 * - get_template_directory_uri() - Get current theme directory URL
 *
 * @package WP2BD
 * @subpackage Functions
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('WP2BD_VERSION')) {
    define('WP2BD_VERSION', '1.0.0');
}

/**
 * Retrieve the home URL for the current site.
 *
 * Maps to Backdrop's global $base_url with scheme and path handling.
 *
 * WordPress function signature:
 * home_url( string $path = '', string|null $scheme = null )
 *
 * @param string      $path   Optional. Path relative to the home URL. Default empty.
 * @param string|null $scheme Optional. Scheme to give the home URL context. Accepts
 *                             'http', 'https', 'relative', or null. Default null.
 * @return string Home URL link with optional path appended.
 *
 * @example
 *   home_url()                    // Returns: 'http://example.com'
 *   home_url('/')                 // Returns: 'http://example.com/'
 *   home_url('/about')            // Returns: 'http://example.com/about'
 *   home_url('about')             // Returns: 'http://example.com/about'
 *   home_url('', 'https')         // Returns: 'https://example.com'
 *   home_url('/blog', 'relative') // Returns: '/blog'
 */
function home_url($path = '', $scheme = null) {
    global $base_url;

    // Get base URL - fallback to environment or construct from $_SERVER
    if (empty($base_url)) {
        // Try to get from Backdrop's url() function if available
        if (function_exists('url')) {
            $base_url = url('', array('absolute' => TRUE));
            // Remove trailing slash from url() result
            $base_url = rtrim($base_url, '/');
        } else {
            // Construct from $_SERVER if Backdrop not available
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $base_url = $protocol . '://' . $host;
        }
    }

    // Parse the base URL to get components
    $parsed = parse_url($base_url);
    $base_scheme = $parsed['scheme'] ?? 'http';
    $base_host = $parsed['host'] ?? 'localhost';
    $base_port = $parsed['port'] ?? null;
    $base_path = $parsed['path'] ?? '';

    // Handle scheme parameter
    if ($scheme === 'relative') {
        // Return only the path portion
        $url = $base_path;
    } else {
        // Determine which scheme to use
        if ($scheme === 'http' || $scheme === 'https') {
            $url_scheme = $scheme;
        } else {
            $url_scheme = $base_scheme;
        }

        // Build the URL
        $url = $url_scheme . '://' . $base_host;

        // Add port if non-standard
        if ($base_port &&
            !(($url_scheme === 'http' && $base_port == 80) ||
              ($url_scheme === 'https' && $base_port == 443))) {
            $url .= ':' . $base_port;
        }

        $url .= $base_path;
    }

    // Handle path parameter
    if (!empty($path)) {
        // Ensure path starts with /
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        // Remove trailing slash from base before appending
        $url = rtrim($url, '/');
        $url .= $path;
    }

    /**
     * Filters the home URL.
     *
     * @param string      $url    The complete home URL including scheme and path.
     * @param string      $path   Path relative to the home URL. Blank string if no path is specified.
     * @param string|null $scheme Scheme to give the home URL context. Accepts 'http', 'https', 'relative', or null.
     */
    if (function_exists('apply_filters')) {
        $url = apply_filters('home_url', $url, $path, $scheme);
    }

    return $url;
}

/**
 * Display or retrieve information about the blog.
 *
 * Maps to Backdrop's variable_get() and configuration system.
 *
 * WordPress function signature:
 * bloginfo( string $show = '' )
 *
 * @param string $show Optional. Site info to display. Default empty (site name).
 *
 * @return void Echoes the information.
 *
 * Supported $show values:
 * - 'name'                 - Site name (site_name variable)
 * - 'description'          - Site tagline/slogan (site_slogan variable)
 * - 'url'                  - Home URL
 * - 'wpurl'                - WordPress directory URL (same as home for WP2BD)
 * - 'stylesheet_directory' - Active theme CSS directory URL
 * - 'template_directory'   - Active theme directory URL
 * - 'charset'              - Character encoding (UTF-8)
 * - 'language'             - Language code (en)
 * - 'version'              - WordPress version (4.9 for compatibility)
 *
 * @example
 *   bloginfo('name')        // Echoes: 'My WordPress Site'
 *   bloginfo('description') // Echoes: 'Just another WordPress site'
 *   bloginfo('url')         // Echoes: 'http://example.com'
 */
function bloginfo($show = '') {
    echo get_bloginfo($show, 'display');
}

/**
 * Retrieve information about the blog.
 *
 * Maps to Backdrop's variable_get() and configuration system.
 *
 * WordPress function signature:
 * get_bloginfo( string $show = '', string $filter = 'raw' )
 *
 * @param string $show   Optional. Site info to retrieve. Default empty (site name).
 * @param string $filter Optional. How to filter what is retrieved. Default 'raw'.
 *                       Accepts 'raw', 'display'.
 * @return string The requested information.
 *
 * Supported $show values:
 * - 'name'                 - Site name (site_name variable)
 * - 'description'          - Site tagline/slogan (site_slogan variable)
 * - 'url'                  - Home URL
 * - 'wpurl'                - WordPress directory URL (same as home for WP2BD)
 * - 'stylesheet_directory' - Active theme CSS directory URL
 * - 'template_directory'   - Active theme directory URL
 * - 'charset'              - Character encoding (UTF-8)
 * - 'language'             - Language code (en)
 * - 'version'              - WordPress version (4.9 for compatibility)
 *
 * @example
 *   $site_name = get_bloginfo('name');        // Returns: 'My WordPress Site'
 *   $tagline = get_bloginfo('description');   // Returns: 'Just another WordPress site'
 *   $home = get_bloginfo('url');              // Returns: 'http://example.com'
 */
function get_bloginfo($show = '', $filter = 'raw') {
    // Default to 'name' if empty
    if (empty($show)) {
        $show = 'name';
    }

    $output = '';

    switch ($show) {
        case 'name':
            // Map to Backdrop's site_name variable
            if (function_exists('variable_get')) {
                $output = variable_get('site_name', 'WordPress Site');
            } else {
                $output = 'WordPress Site';
            }
            break;

        case 'description':
            // Map to Backdrop's site_slogan variable
            if (function_exists('variable_get')) {
                $output = variable_get('site_slogan', 'Just another WordPress site');
            } else {
                $output = 'Just another WordPress site';
            }
            break;

        case 'url':
        case 'home':
            $output = home_url();
            break;

        case 'wpurl':
        case 'siteurl':
            // In WP2BD, WordPress directory is same as home
            $output = home_url();
            break;

        case 'stylesheet_directory':
            // Return the stylesheet directory URL (same as template for most themes)
            $output = get_template_directory_uri();
            break;

        case 'template_directory':
        case 'template_url':
            $output = get_template_directory_uri();
            break;

        case 'stylesheet_url':
            $output = get_template_directory_uri() . '/style.css';
            break;

        case 'charset':
            $output = 'UTF-8';
            break;

        case 'language':
            // Map to Backdrop's language settings if available
            if (function_exists('variable_get')) {
                $output = variable_get('language_default', 'en');
            } else {
                $output = 'en';
            }
            break;

        case 'version':
            // Return WordPress 4.9 for compatibility
            $output = '4.9';
            break;

        case 'text_direction':
        case 'html_type':
        case 'atom_version':
        case 'rdf_version':
        case 'rss_version':
        case 'pingback_url':
        case 'admin_email':
            // Additional properties that may be requested
            $defaults = array(
                'text_direction' => 'ltr',
                'html_type'      => 'text/html',
                'atom_version'   => '1.0',
                'rdf_version'    => '1.0',
                'rss_version'    => '2.0',
                'pingback_url'   => home_url('/xmlrpc.php'),
                'admin_email'    => 'admin@example.com',
            );
            $output = $defaults[$show] ?? '';
            break;

        default:
            $output = '';
            break;
    }

    // Apply filters based on filter parameter
    if ($filter === 'display' && function_exists('apply_filters')) {
        $output = apply_filters('bloginfo', $output, $show);
    } elseif (function_exists('apply_filters')) {
        $output = apply_filters('bloginfo_raw', $output, $show);
    }

    // Apply specific filter for the property
    if (function_exists('apply_filters')) {
        $output = apply_filters('bloginfo_' . $show, $output, $show);
    }

    return $output;
}

/**
 * Retrieve template directory path for current theme.
 *
 * Maps to Backdrop's backdrop_get_path() or path_to_theme().
 *
 * WordPress function signature:
 * get_template_directory()
 *
 * @return string Path to active theme directory (no trailing slash).
 *
 * @example
 *   $theme_dir = get_template_directory();
 *   // Returns: '/var/www/html/wp-content/themes/twentyseventeen'
 *
 *   require get_template_directory() . '/inc/template-functions.php';
 */
if (!function_exists('get_template_directory')) {
function get_template_directory() {
    static $template_dir = null;

    // Cache the result
    if ($template_dir !== null) {
        return $template_dir;
    }

    // FIRST: Check if WP2BD WordPress theme directory is defined (highest priority!)
    if (defined('WP2BD_ACTIVE_THEME_DIR')) {
        $template_dir = WP2BD_ACTIVE_THEME_DIR;
    }
    // SECOND: Try path constant
    elseif (defined('WP2BD_THEME_PATH')) {
        $template_dir = WP2BD_THEME_PATH;
    }
    // Only fallback to Backdrop theme if WP2BD constants not set
    // (This should NOT happen when wp_content module is active)
    elseif (function_exists('path_to_theme')) {
        $relative_path = path_to_theme();
        // Convert to absolute path
        if (defined('BACKDROP_ROOT')) {
            $template_dir = BACKDROP_ROOT . '/' . $relative_path;
        } elseif (defined('DRUPAL_ROOT')) {
            $template_dir = DRUPAL_ROOT . '/' . $relative_path;
        } else {
            // Use current working directory as base
            $template_dir = $_SERVER['DOCUMENT_ROOT'] . '/' . $relative_path;
        }
    }
    // Try backdrop_get_path() with theme name
    elseif (function_exists('backdrop_get_path')) {
        global $theme_key;
        if (!empty($theme_key)) {
            $relative_path = backdrop_get_path('theme', $theme_key);
            if (defined('BACKDROP_ROOT')) {
                $template_dir = BACKDROP_ROOT . '/' . $relative_path;
            } else {
                $template_dir = $_SERVER['DOCUMENT_ROOT'] . '/' . $relative_path;
            }
        }
    }

    // Fallback: Use wpbrain path (Epic 8 architecture)
    if ($template_dir === null) {
        if (defined('BACKDROP_ROOT')) {
            $template_dir = BACKDROP_ROOT . '/themes/wp/wpbrain/wp-content/themes/twentyseventeen';
        } else {
            $template_dir = '/var/www/html/backdrop-1.30/themes/wp/wpbrain/wp-content/themes/twentyseventeen';
        }
    }

    // Remove trailing slash if present
    $template_dir = rtrim($template_dir, '/');

    /**
     * Filters the template directory path.
     *
     * @param string $template_dir The path to the template directory.
     */
    if (function_exists('apply_filters')) {
        $template_dir = apply_filters('template_directory', $template_dir);
    }

    return $template_dir;
}
}

/**
 * Retrieve template directory URI for current theme.
 *
 * Returns the URL to the active theme directory.
 *
 * WordPress function signature:
 * get_template_directory_uri()
 *
 * @return string URI to active theme directory (no trailing slash).
 *
 * @example
 *   $theme_uri = get_template_directory_uri();
 *   // Returns: 'http://example.com/wp-content/themes/twentyseventeen'
 *
 *   echo '<link rel="stylesheet" href="' . get_template_directory_uri() . '/style.css">';
 */
if (!function_exists('get_template_directory_uri')) {
function get_template_directory_uri() {
    static $template_uri = null;

    // Cache the result
    if ($template_uri !== null) {
        return $template_uri;
    }

    // Get the absolute path
    $template_dir = get_template_directory();

    // Convert filesystem path to URL
    // Common document root patterns
    $doc_root = $_SERVER['DOCUMENT_ROOT'] ?? '/var/www/html';
    $doc_root = rtrim($doc_root, '/');

    // Check if template_dir starts with document root
    if (strpos($template_dir, $doc_root) === 0) {
        // Extract the relative path
        $relative_path = substr($template_dir, strlen($doc_root));
        $template_uri = home_url($relative_path);
    } else {
        // Fallback: Try to extract from known WordPress structure
        // Look for /wp-content/themes/ pattern
        if (preg_match('#(/wp-content/themes/[^/]+)$#', $template_dir, $matches)) {
            $template_uri = home_url($matches[1]);
        }
        // Look for /themes/ pattern (Backdrop style)
        elseif (preg_match('#(/themes/[^/]+)$#', $template_dir, $matches)) {
            $template_uri = home_url($matches[1]);
        }
        // Last resort: construct manually
        else {
            $theme_name = basename($template_dir);
            $template_uri = home_url('/wp-content/themes/' . $theme_name);
        }
    }

    /**
     * Filters the template directory URI.
     *
     * @param string $template_uri     The URI to the template directory.
     * @param string $template_dir     The path to the template directory.
     */
    if (function_exists('apply_filters')) {
        $template_uri = apply_filters('template_directory_uri', $template_uri, $template_dir);
    }

    return $template_uri;
}
}

/**
 * Retrieve stylesheet directory path for current theme.
 *
 * For child themes, this returns the child theme directory.
 * For parent themes, this is the same as get_template_directory().
 *
 * @return string Path to stylesheet directory (no trailing slash).
 */
if (!function_exists('get_stylesheet_directory')) {
function get_stylesheet_directory() {
    // In WP2BD, we don't support child themes yet, so return template directory
    return get_template_directory();
}
}

/**
 * Retrieve stylesheet directory URI for current theme.
 *
 * For child themes, this returns the child theme URI.
 * For parent themes, this is the same as get_template_directory_uri().
 *
 * @return string URI to stylesheet directory (no trailing slash).
 */
if (!function_exists('get_stylesheet_directory_uri')) {
function get_stylesheet_directory_uri() {
    // In WP2BD, we don't support child themes yet, so return template directory URI
    return get_template_directory_uri();
}
}

/**
 * Retrieve the name of the current theme.
 *
 * @return string Template name (directory name).
 */
if (!function_exists('get_template')) {
function get_template() {
    return basename(get_template_directory());
}
}

/**
 * Retrieve the name of the current stylesheet (theme).
 *
 * @return string Stylesheet name (directory name).
 */
if (!function_exists('get_stylesheet')) {
function get_stylesheet() {
    return basename(get_stylesheet_directory());
}
}

/**
 * Display or retrieve page title for all areas of blog.
 *
 * WordPress Behavior:
 * - Generates appropriate title based on current page context
 * - Supports separator and separator location
 * - Can display or return the title
 *
 * Backdrop Mapping:
 * - Uses backdrop_get_title() for current page title
 * - Uses backdrop_set_title() context
 * - Falls back to site name for home page
 *
 * @since WordPress 1.0.0
 * @since WP2BD 1.0.0
 *
 * @param string $sep         Optional. How to separate the various items within the page title.
 *                            Default '&raquo;'.
 * @param bool   $display     Optional. Whether to display or retrieve title. Default true.
 * @param string $seplocation Optional. Direction to display title, 'right' or 'left'.
 * @return string|null String on retrieve, null when displaying.
 */
if (!function_exists('wp_title')) {
function wp_title($sep = '&raquo;', $display = true, $seplocation = '') {
    global $wp_query, $post;

    $title = '';

    // Try to get the page title from Backdrop
    if (function_exists('backdrop_get_title')) {
        $title = backdrop_get_title();
    }

    // If we have a post object, use its title
    if (empty($title) && isset($post) && is_object($post) && !empty($post->post_title)) {
        $title = $post->post_title;
    }

    // For single posts/pages
    if (empty($title) && function_exists('is_single') && (is_single() || is_page())) {
        if (function_exists('single_post_title')) {
            $title = single_post_title('', false);
        }
    }

    // Apply separator if title exists
    if (!empty($title)) {
        $prefix = '';
        if (!empty($sep)) {
            $prefix = " $sep ";
        }

        if ('right' === $seplocation) {
            $title = $title . $prefix;
        } else {
            $title = $prefix . $title;
        }
    }

    // Apply filter
    if (function_exists('apply_filters')) {
        $title = apply_filters('wp_title', $title, $sep, $seplocation);
    }

    if ($display) {
        echo $title;
        return null;
    }

    return $title;
}
}

/**
 * Retrieves the names of the taxonomies that are registered for the given object type.
 *
 * WordPress Behavior:
 * - Returns array of taxonomy names for a given object type (post type)
 * - Common examples: 'post' returns ['category', 'post_tag']
 * - 'page' typically has no taxonomies by default
 * - Custom post types return their registered taxonomies
 *
 * Backdrop Mapping:
 * - Maps post types to known WordPress taxonomy relationships
 * - 'post' and 'article' return ['category', 'post_tag'] 
 * - 'page' returns empty array
 * - Other types may have custom mappings
 *
 * @since WordPress 2.0.0
 * @since WP2BD 1.0.0
 *
 * @param string|string[] $object_type Name of the object type string or array of the object types.
 * @param string          $output      Optional. The type of output to return in the array. Accepts
 *                                     either 'names' or 'objects'. Default 'names'.
 * @return string[]|WP_Taxonomy[] The names of all taxonomies of `$object_type`.
 */
if (!function_exists('get_object_taxonomies')) {
    function get_object_taxonomies($object_type, $output = 'names') {
        // Define default taxonomy mappings for common post types
        static $taxonomy_map = array(
            'post' => array('category', 'post_tag'),
            'article' => array('category', 'post_tag'), // Backdrop equivalent
            'page' => array(),
            'attachment' => array(),
        );
        
        // Handle array of object types
        if (is_array($object_type)) {
            $taxonomies = array();
            foreach ($object_type as $type) {
                $type_taxonomies = get_object_taxonomies($type, $output);
                $taxonomies = array_merge($taxonomies, $type_taxonomies);
            }
            return array_unique($taxonomies);
        }
        
        // Get taxonomies for single object type
        $object_type = (string) $object_type;
        
        // Check our mapping
        if (isset($taxonomy_map[$object_type])) {
            return $taxonomy_map[$object_type];
        }
        
        // For custom post types in Backdrop, try to get from the entity
        if (function_exists('field_info_instances')) {
            // Check if there are taxonomy reference fields
            $instances = field_info_instances('node', $object_type);
            $taxonomies = array();
            
            if ($instances) {
                foreach ($instances as $field_name => $instance) {
                    $field = field_info_field($field_name);
                    if ($field && $field['type'] === 'taxonomy_term_reference') {
                        // Get the vocabulary from the field settings
                        if (!empty($field['settings']['allowed_values'][0]['vocabulary'])) {
                            $taxonomies[] = $field['settings']['allowed_values'][0]['vocabulary'];
                        }
                    }
                }
            }
            
            return $taxonomies;
        }
        
        // Default: return 'category' for posts, empty for others
        if (in_array($object_type, array('post', 'article', 'blog'))) {
            return array('category', 'post_tag');
        }
        
        return array();
    }
}

// ============================================================================
// ESSENTIAL UTILITY FUNCTIONS
// Migrated from stubs.php - December 2024
// ============================================================================

if (!function_exists('add_query_arg')) {
  /**
   * Add or modify query arguments in a URL.
   *
   * @return string URL with query arguments.
   */
  function add_query_arg() {
    $args = func_get_args();
    if (isset($args[0])) {
      if (is_array($args[0])) {
        $base = isset($args[1]) ? $args[1] : '';
        $params = $args[0];
      } else {
        $key = $args[0];
        $value = isset($args[1]) ? $args[1] : '';
        $base = isset($args[2]) ? $args[2] : '';
        $params = array($key => $value);
      }

      if (empty($base)) {
        $base = home_url('/');
      }

      $url_parts = parse_url($base);
      if (!$url_parts) {
        return $base;
      }
      
      $query = isset($url_parts['query']) ? $url_parts['query'] : '';
      parse_str($query, $query_params);
      $query_params = array_merge($query_params, $params);

      $url = '';
      if (isset($url_parts['scheme'])) {
        $url .= $url_parts['scheme'] . '://';
      }
      if (isset($url_parts['host'])) {
        $url .= $url_parts['host'];
      }
      if (isset($url_parts['port'])) {
        $url .= ':' . $url_parts['port'];
      }
      if (isset($url_parts['path'])) {
        $url .= $url_parts['path'];
      }
      if (!empty($query_params)) {
        $url .= '?' . http_build_query($query_params);
      }
      if (isset($url_parts['fragment'])) {
        $url .= '#' . $url_parts['fragment'];
      }
      return $url;
    }
    return home_url('/');
  }
}

if (!function_exists('wp_parse_args')) {
  /**
   * Merge user defined arguments into defaults array.
   *
   * @param string|array|object $args     Value to merge with $defaults.
   * @param array               $defaults Array that serves as the defaults.
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
   * Return the absolute integer value.
   *
   * @param mixed $maybeint Data to convert to non-negative integer.
   * @return int Non-negative integer.
   */
  function absint($maybeint) {
    return abs(intval($maybeint));
  }
}

if (!function_exists('get_option')) {
  /**
   * Retrieves an option value.
   *
   * @param string $option  Name of the option to retrieve.
   * @param mixed  $default Default value if option not found.
   * @return mixed Value of the option.
   */
  function get_option($option, $default = false) {
    // Try to get from Backdrop config
    if (function_exists('config_get')) {
      $value = config_get('wp2bd.settings', $option);
      if ($value !== NULL) {
        return $value;
      }
    }
    
    // Common WordPress options with sensible defaults
    $defaults = array(
      'blogname' => function_exists('config_get') ? config_get('system.core', 'site_name') : 'Site',
      'blogdescription' => function_exists('config_get') ? config_get('system.core', 'site_slogan') : '',
      'date_format' => 'F j, Y',
      'time_format' => 'g:i a',
      'posts_per_page' => 10,
      'thumbnail_size_w' => 150,
      'thumbnail_size_h' => 150,
      'medium_size_w' => 300,
      'medium_size_h' => 300,
      'large_size_w' => 1024,
      'large_size_h' => 1024,
    );
    
    if (isset($defaults[$option])) {
      return $defaults[$option];
    }
    
    return $default;
  }
}

if (!function_exists('is_admin')) {
  /**
   * Whether the current request is for an administrative interface.
   *
   * @return bool True if admin screen, false otherwise.
   */
  function is_admin() {
    // Check if we're in Backdrop's admin area
    if (function_exists('path_is_admin') && function_exists('current_path')) {
      return path_is_admin(current_path());
    }
    return false;
  }
}

if (!function_exists('add_theme_support')) {
  /**
   * Register theme support for a given feature.
   *
   * @param string $feature The feature being added.
   * @param mixed  $args    Optional extra arguments.
   * @return void|bool
   */
  function add_theme_support($feature, $args = array()) {
    global $_wp_theme_features;
    if (!isset($_wp_theme_features)) {
      $_wp_theme_features = array();
    }
    $_wp_theme_features[$feature] = $args ? $args : true;
    return true;
  }
}

if (!function_exists('current_theme_supports')) {
  /**
   * Check if a theme supports a given feature.
   *
   * @param string $feature The feature to check.
   * @return bool True if supported, false otherwise.
   */
  function current_theme_supports($feature) {
    global $_wp_theme_features;
    return isset($_wp_theme_features[$feature]);
  }
}

if (!function_exists('get_theme_support')) {
  /**
   * Get the arguments for a theme feature.
   *
   * @param string $feature The feature to get.
   * @return mixed The feature arguments, or false if not supported.
   */
  function get_theme_support($feature) {
    global $_wp_theme_features;
    if (isset($_wp_theme_features[$feature])) {
      return $_wp_theme_features[$feature];
    }
    return false;
  }
}

if (!function_exists('get_theme_mod')) {
  /**
   * Retrieve theme modification value.
   *
   * @param string $name    Theme modification name.
   * @param mixed  $default Default value if not found.
   * @return mixed Theme modification value.
   */
  function get_theme_mod($name, $default = false) {
    // Try to get from Backdrop config
    if (function_exists('config_get')) {
      $value = config_get('wp2bd.theme_mods', $name);
      if ($value !== NULL) {
        return $value;
      }
    }
    return $default;
  }
}

if (!function_exists('set_theme_mod')) {
  /**
   * Update theme modification value.
   *
   * @param string $name  Theme modification name.
   * @param mixed  $value Theme modification value.
   */
  function set_theme_mod($name, $value) {
    if (function_exists('config_set')) {
      config_set('wp2bd.theme_mods', $name, $value);
    }
  }
}

if (!function_exists('has_nav_menu')) {
  /**
   * Determines whether a registered nav menu location has a menu assigned.
   *
   * @param string $location Menu location identifier.
   * @return bool True if menu is assigned, false otherwise.
   */
  function has_nav_menu($location) {
    // Stub - always return false for now
    return false;
  }
}

if (!function_exists('wp_nav_menu')) {
  /**
   * Displays a navigation menu.
   *
   * @param array $args Menu arguments.
   * @return void|string|false Menu output or false.
   */
  function wp_nav_menu($args = array()) {
    $defaults = array(
      'echo' => true,
      'fallback_cb' => false,
    );
    $args = wp_parse_args($args, $defaults);
    
    // Stub - output empty menu container
    $output = '<!-- wp_nav_menu placeholder -->';
    
    if ($args['echo']) {
      echo $output;
      return;
    }
    return $output;
  }
}

if (!function_exists('register_nav_menu')) {
  /**
   * Register a navigation menu location for a theme.
   *
   * @param string $location    Menu location identifier.
   * @param string $description Menu location description.
   */
  function register_nav_menu($location, $description) {
    register_nav_menus(array($location => $description));
  }
}

if (!function_exists('register_nav_menus')) {
  /**
   * Register navigation menu locations for a theme.
   *
   * @param array $locations Associative array of menu location identifiers and descriptions.
   */
  function register_nav_menus($locations = array()) {
    global $_wp_registered_nav_menus;
    if (!isset($_wp_registered_nav_menus)) {
      $_wp_registered_nav_menus = array();
    }
    $_wp_registered_nav_menus = array_merge($_wp_registered_nav_menus, $locations);
  }
}

if (!function_exists('register_sidebar')) {
  /**
   * Build the definition for a single sidebar.
   *
   * @param array|string $args Sidebar configuration.
   * @return string Sidebar ID.
   */
  function register_sidebar($args = array()) {
    global $wp_registered_sidebars;
    if (!isset($wp_registered_sidebars)) {
      $wp_registered_sidebars = array();
    }
    
    $defaults = array(
      'name' => '',
      'id' => '',
      'description' => '',
      'class' => '',
      'before_widget' => '<div class="widget">',
      'after_widget' => '</div>',
      'before_title' => '<h2 class="widget-title">',
      'after_title' => '</h2>',
    );
    
    $args = wp_parse_args($args, $defaults);
    
    if (empty($args['id'])) {
      $args['id'] = 'sidebar-' . count($wp_registered_sidebars);
    }
    
    $wp_registered_sidebars[$args['id']] = $args;
    return $args['id'];
  }
}

if (!function_exists('is_customize_preview')) {
  /**
   * Whether the site is being previewed in the Customizer.
   *
   * @return bool True if in customizer preview, false otherwise.
   */
  function is_customize_preview() {
    return false;
  }
}

if (!function_exists('get_stylesheet_uri')) {
  /**
   * Retrieve stylesheet URI for the current theme.
   *
   * @return string Stylesheet URI.
   */
  function get_stylesheet_uri() {
    return get_stylesheet_directory_uri() . '/style.css';
  }
}

if (!function_exists('wp_script_add_data')) {
  /**
   * Add extra data to a registered script.
   *
   * @param string $handle Script handle.
   * @param string $key    Data key.
   * @param mixed  $value  Data value.
   * @return bool True on success, false otherwise.
   */
  function wp_script_add_data($handle, $key, $value) {
    // Stub - not fully implemented
    return true;
  }
}

if (!function_exists('wp_localize_script')) {
  /**
   * Localize a script with data.
   *
   * @param string $handle      Script handle.
   * @param string $object_name JavaScript object name.
   * @param array  $l10n        Data to localize.
   * @return bool True on success, false otherwise.
   */
  function wp_localize_script($handle, $object_name, $l10n) {
    // Stub - not fully implemented
    return true;
  }
}

if (!function_exists('comments_open')) {
  /**
   * Determines whether the current post is open for comments.
   *
   * @param int|WP_Post $post_id Post ID or post object.
   * @return bool True if comments are open.
   */
  function comments_open($post_id = null) {
    // Stub - return false to hide comment forms
    return false;
  }
}

if (!function_exists('pings_open')) {
  /**
   * Determines whether the current post is open for pings.
   *
   * @param int|WP_Post $post_id Post ID or post object.
   * @return bool True if pings are open.
   */
  function pings_open($post_id = null) {
    return false;
  }
}

if (!function_exists('get_header_textcolor')) {
  /**
   * Retrieve header text color.
   *
   * @return string Header text color hex code.
   */
  function get_header_textcolor() {
    return get_theme_mod('header_textcolor', '000000');
  }
}

if (!function_exists('display_header_text')) {
  /**
   * Whether to display the header text.
   *
   * @return bool True if header text should be displayed.
   */
  function display_header_text() {
    return true;
  }
}

if (!function_exists('has_custom_header')) {
  /**
   * Check whether a custom header is set.
   *
   * @return bool True if custom header is set.
   */
  function has_custom_header() {
    return has_header_image();
  }
}

if (!function_exists('has_header_image')) {
  /**
   * Check whether a header image is set.
   *
   * @return bool True if header image is set.
   */
  function has_header_image() {
    $header_image = get_header_image();
    return !empty($header_image);
  }
}

if (!function_exists('get_header_image')) {
  /**
   * Retrieve header image URL.
   *
   * @return string|false Header image URL or false if none.
   */
  function get_header_image() {
    // First check theme mod
    $header = get_theme_mod('header_image', '');
    if (!empty($header)) {
      return $header;
    }
    
    // Check for theme's default header image
    $theme_dir = get_template_directory();
    $theme_uri = get_template_directory_uri();
    
    // Common default header image locations
    $default_headers = array(
      '/assets/images/header.jpg',  // Twenty Seventeen
      '/images/headers/default.jpg',
      '/images/header.jpg',
    );
    
    foreach ($default_headers as $path) {
      if (file_exists($theme_dir . $path)) {
        return $theme_uri . $path;
      }
    }
    
    return false;
  }
}

if (!function_exists('get_custom_header')) {
  /**
   * Get the custom header object.
   *
   * @return object Custom header object.
   */
  function get_custom_header() {
    $header = new stdClass();
    $header->url = get_header_image();
    $header->width = get_theme_mod('header_image_width', 0);
    $header->height = get_theme_mod('header_image_height', 0);
    return $header;
  }
}

if (!function_exists('get_custom_logo')) {
  /**
   * Returns a custom logo HTML.
   *
   * @param int $blog_id Optional blog ID.
   * @return string Custom logo HTML.
   */
  function get_custom_logo($blog_id = 0) {
    return '';
  }
}

if (!function_exists('has_custom_logo')) {
  /**
   * Determines whether the site has a custom logo.
   *
   * @param int $blog_id Optional blog ID.
   * @return bool True if custom logo is set.
   */
  function has_custom_logo($blog_id = 0) {
    return false;
  }
}

if (!function_exists('the_custom_logo')) {
  /**
   * Displays the custom logo.
   *
   * @param int $blog_id Optional blog ID.
   */
  function the_custom_logo($blog_id = 0) {
    echo get_custom_logo($blog_id);
  }
}

if (!function_exists('wp_style_add_data')) {
  /**
   * Add extra data to a registered stylesheet.
   *
   * @param string $handle Style handle.
   * @param string $key    Data key.
   * @param mixed  $value  Data value.
   * @return bool True on success.
   */
  function wp_style_add_data($handle, $key, $value) {
    return true;
  }
}

if (!function_exists('wp_style_is')) {
  /**
   * Check whether a CSS stylesheet is registered or enqueued.
   *
   * @param string $handle Style handle.
   * @param string $list   Status to check.
   * @return bool True if in list.
   */
  function wp_style_is($handle, $list = 'enqueued') {
    return false;
  }
}

if (!function_exists('wp_script_is')) {
  /**
   * Check whether a script is registered or enqueued.
   *
   * @param string $handle Script handle.
   * @param string $list   Status to check.
   * @return bool True if in list.
   */
  function wp_script_is($handle, $list = 'enqueued') {
    return false;
  }
}

if (!function_exists('wp_add_inline_style')) {
  /**
   * Add inline CSS to a registered stylesheet.
   *
   * @param string $handle Style handle.
   * @param string $data   CSS to add.
   * @return bool True on success.
   */
  function wp_add_inline_style($handle, $data) {
    return true;
  }
}

if (!function_exists('is_preview')) {
  /**
   * Whether the current page is a preview.
   *
   * @return bool True if preview.
   */
  function is_preview() {
    return false;
  }
}

if (!function_exists('post_password_required')) {
  /**
   * Whether a post requires a password and hasn't been provided.
   *
   * @param int|WP_Post $post Post ID or object.
   * @return bool True if password required.
   */
  function post_password_required($post = null) {
    return false;
  }
}

if (!function_exists('post_type_supports')) {
  /**
   * Check if a post type supports a feature.
   *
   * @param string $post_type Post type.
   * @param string $feature   Feature to check.
   * @return bool True if supported.
   */
  function post_type_supports($post_type, $feature) {
    // Most post types support these by default
    $default_supports = array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments');
    return in_array($feature, $default_supports);
  }
}

if (!function_exists('get_comments_number')) {
  /**
   * Retrieve the number of comments on a post.
   *
   * @param int|WP_Post $post_id Post ID or object.
   * @return int Number of comments.
   */
  function get_comments_number($post_id = 0) {
    return 0;
  }
}

if (!function_exists('comments_template')) {
  /**
   * Load the comments template.
   *
   * @param string $file              Comments template file.
   * @param bool   $separate_comments Whether to separate by type.
   */
  function comments_template($file = '/comments.php', $separate_comments = false) {
    // Stub - don't load comments for now
  }
}

if (!function_exists('get_author_posts_url')) {
  /**
   * Retrieve the URL to the author page for the user.
   *
   * @param int    $author_id       Author ID.
   * @param string $author_nicename Optional. Author nicename.
   * @return string The URL to the author's page.
   */
  function get_author_posts_url($author_id, $author_nicename = '') {
    global $wp_rewrite;
    
    // In Backdrop, link to user profile
    if (function_exists('url')) {
      return url('user/' . $author_id);
    }
    
    return home_url('/author/' . ($author_nicename ? $author_nicename : $author_id));
  }
}

if (!function_exists('is_multi_author')) {
  /**
   * Determines whether the site has more than one author with published posts.
   *
   * @return bool True if more than one author, false otherwise.
   */
  function is_multi_author() {
    // For simplicity, assume multi-author sites
    // A more complete implementation would query the database
    return true;
  }
}

if (!function_exists('get_the_category_list')) {
  /**
   * Retrieve category list for a post in HTML format.
   *
   * @param string $separator Optional separator between categories.
   * @param string $parents   How to display parents.
   * @param int    $post_id   Post ID.
   * @return string HTML list of categories.
   */
  function get_the_category_list($separator = '', $parents = '', $post_id = false) {
    return '';  // Stub - taxonomy support needed
  }
}

if (!function_exists('get_the_tag_list')) {
  /**
   * Retrieve the tags for a post in HTML format.
   *
   * @param string $before  Before tags.
   * @param string $sep     Separator.
   * @param string $after   After tags.
   * @param int    $post_id Post ID.
   * @return string|false|WP_Error HTML tags list or false/WP_Error.
   */
  function get_the_tag_list($before = '', $sep = '', $after = '', $post_id = 0) {
    return '';  // Stub - taxonomy support needed
  }
}

if (!function_exists('has_tag')) {
  /**
   * Check if the current post has any tags.
   *
   * @param string|int|array $tag     Optional. Tag ID, name, slug, or array of IDs.
   * @param int              $post_id Optional. Post ID. Defaults to current post.
   * @return bool True if the post has tags, false otherwise.
   */
  function has_tag($tag = '', $post_id = false) {
    return has_term($tag, 'post_tag', $post_id);
  }
}

if (!function_exists('the_tags')) {
  /**
   * Display the tags for a post.
   *
   * @param string $before Before tags.
   * @param string $sep    Separator.
   * @param string $after  After tags.
   */
  function the_tags($before = null, $sep = ', ', $after = '') {
    echo get_the_tag_list($before, $sep, $after);
  }
}

if (!function_exists('get_the_category')) {
  /**
   * Retrieve post categories.
   *
   * @param int $post_id Post ID.
   * @return array Array of category objects.
   */
  function get_the_category($post_id = false) {
    return array();  // Stub - taxonomy support needed
  }
}

if (!function_exists('get_categories')) {
  /**
   * Retrieve list of categories.
   *
   * @param array $args Arguments.
   * @return array List of category objects.
   */
  function get_categories($args = array()) {
    return array();  // Stub - taxonomy support needed
  }
}

if (!function_exists('get_search_form')) {
  /**
   * Display search form.
   *
   * @param bool $echo Whether to echo or return.
   * @return string|void Search form HTML.
   */
  function get_search_form($echo = true) {
    $form = '<form role="search" method="get" class="search-form" action="' . esc_url(home_url('/')) . '">
      <label>
        <span class="screen-reader-text">' . __('Search for:') . '</span>
        <input type="search" class="search-field" placeholder="' . esc_attr__('Search &hellip;') . '" value="" name="s" />
      </label>
      <input type="submit" class="search-submit" value="' . esc_attr__('Search') . '" />
    </form>';
    
    if ($echo) {
      echo $form;
      return;
    }
    return $form;
  }
}

if (!function_exists('get_search_query')) {
  /**
   * Retrieve the search query.
   *
   * @param bool $escaped Whether to escape.
   * @return string Search query.
   */
  function get_search_query($escaped = true) {
    $query = isset($_GET['s']) ? $_GET['s'] : '';
    return $escaped ? esc_attr($query) : $query;
  }
}

if (!function_exists('get_background_image')) {
  /**
   * Retrieve background image URL.
   *
   * @return string Background image URL or empty string.
   */
  function get_background_image() {
    return get_theme_mod('background_image', '');
  }
}

if (!function_exists('get_background_color')) {
  /**
   * Retrieve background color.
   *
   * @return string Background color hex without #.
   */
  function get_background_color() {
    return get_theme_mod('background_color', 'ffffff');
  }
}

if (!function_exists('wp_get_recent_posts')) {
  /**
   * Retrieve a number of recent posts.
   *
   * @param array  $args   Arguments.
   * @param string $output Output type (ARRAY_A, ARRAY_N, or OBJECT).
   * @return array List of posts.
   */
  function wp_get_recent_posts($args = array(), $output = ARRAY_A) {
    $defaults = array(
      'numberposts' => 10,
      'post_type' => 'post',
      'post_status' => 'publish',
    );
    $args = wp_parse_args($args, $defaults);
    
    // Load from Backdrop
    if (function_exists('db_select')) {
      $query = db_select('node', 'n')
        ->fields('n', array('nid'))
        ->condition('n.status', 1)
        ->orderBy('n.created', 'DESC')
        ->range(0, $args['numberposts']);
      
      $nids = $query->execute()->fetchCol();
      
      if (!empty($nids) && function_exists('node_load_multiple')) {
        $nodes = node_load_multiple($nids);
        $posts = array();
        
        foreach ($nodes as $node) {
          // Convert to array format (default for wp_get_recent_posts)
          $post_array = array(
            'ID' => $node->nid,
            'post_title' => isset($node->title) ? $node->title : '',
            'post_date' => date('Y-m-d H:i:s', isset($node->created) ? $node->created : time()),
            'post_content' => '',
            'post_excerpt' => '',
            'post_status' => 'publish',
            'post_type' => isset($node->type) ? $node->type : 'post',
          );
          
          // Get body content if available
          if (isset($node->body) && is_array($node->body)) {
            $lang = isset($node->language) ? $node->language : 'und';
            if (isset($node->body[$lang][0]['value'])) {
              $post_array['post_content'] = $node->body[$lang][0]['value'];
            } elseif (isset($node->body['und'][0]['value'])) {
              $post_array['post_content'] = $node->body['und'][0]['value'];
            }
          }
          
          if ($output === OBJECT) {
            $posts[] = (object) $post_array;
          } else {
            $posts[] = $post_array;
          }
        }
        
        return $posts;
      }
    }
    
    return array();
  }
}

if (!function_exists('wp_get_archives')) {
  /**
   * Display archive links.
   *
   * @param array $args Arguments.
   * @return string|void Archive links HTML.
   */
  function wp_get_archives($args = array()) {
    $defaults = array(
      'type' => 'monthly',
      'limit' => '',
      'format' => 'html',
      'before' => '',
      'after' => '',
      'show_post_count' => false,
      'echo' => 1,
    );
    $args = wp_parse_args($args, $defaults);
    
    $output = '';
    
    // Get monthly archives from Backdrop
    if (function_exists('db_query')) {
      $result = db_query("
        SELECT 
          YEAR(FROM_UNIXTIME(created)) as year,
          MONTH(FROM_UNIXTIME(created)) as month,
          COUNT(*) as posts
        FROM {node}
        WHERE status = 1
        GROUP BY YEAR(FROM_UNIXTIME(created)), MONTH(FROM_UNIXTIME(created))
        ORDER BY year DESC, month DESC
        LIMIT 12
      ");
      
      foreach ($result as $row) {
        $url = home_url('/' . $row->year . '/' . sprintf('%02d', $row->month) . '/');
        $text = date('F Y', mktime(0, 0, 0, $row->month, 1, $row->year));
        
        if ($args['format'] === 'html') {
          $output .= '<li>' . $args['before'] . '<a href="' . esc_url($url) . '">' . $text . '</a>';
          if ($args['show_post_count']) {
            $output .= ' (' . $row->posts . ')';
          }
          $output .= $args['after'] . '</li>';
        }
      }
    }
    
    if ($args['echo']) {
      echo $output;
    }
    return $output;
  }
}

if (!function_exists('get_posts')) {
  /**
   * Retrieve list of posts.
   *
   * @param array $args Arguments.
   * @return array List of posts.
   */
  function get_posts($args = null) {
    global $wp_query;
    
    $defaults = array(
      'numberposts' => 5,
      'post_type' => 'post',
      'post_status' => 'publish',
      'orderby' => 'date',
      'order' => 'DESC',
    );
    $args = wp_parse_args($args, $defaults);
    
    // Try to load posts from Backdrop
    if (function_exists('db_select')) {
      $query = db_select('node', 'n')
        ->fields('n', array('nid'))
        ->condition('n.status', 1)
        ->orderBy('n.created', 'DESC')
        ->range(0, $args['numberposts']);
      
      $nids = $query->execute()->fetchCol();
      
      if (!empty($nids) && function_exists('node_load_multiple')) {
        $nodes = node_load_multiple($nids);
        $posts = array();
        
        foreach ($nodes as $node) {
          if (!isset($node->type)) {
            $node->type = 'post';
          }
          if (class_exists('WP_Post')) {
            $posts[] = WP_Post::from_node($node);
          } else {
            $posts[] = $node;
          }
        }
        
        return $posts;
      }
    }
    
    return array();
  }
}

if (!function_exists('get_the_terms')) {
  /**
   * Retrieve the terms of the taxonomy that are attached to the post.
   *
   * @param int    $post_id Post ID.
   * @param string $taxonomy Taxonomy name.
   * @return array|WP_Error Array of term objects, or WP_Error on failure.
   */
  function get_the_terms($post_id, $taxonomy) {
    // Convert WordPress taxonomy names to Backdrop vocabulary names
    $vocabulary_map = array(
      'category' => 'categories',
      'post_tag' => 'tags',
    );

    $vocabulary = isset($vocabulary_map[$taxonomy]) ? $vocabulary_map[$taxonomy] : $taxonomy;

    // Get terms using Backdrop's taxonomy API
    if (function_exists('taxonomy_select_nodes')) {
      $tids = taxonomy_select_nodes($post_id, false);
      if (!empty($tids)) {
        $terms = taxonomy_term_load_multiple($tids);
        // Convert Backdrop terms to WordPress-like term objects
        $wp_terms = array();
        foreach ($terms as $term) {
          if (isset($term->vocabulary) && $term->vocabulary == $vocabulary) {
            $wp_term = new stdClass();
            $wp_term->term_id = $term->tid;
            $wp_term->name = $term->name;
            $wp_term->slug = $term->name; // Backdrop doesn't have slugs, use name
            $wp_term->taxonomy = $taxonomy;
            $wp_terms[] = $wp_term;
          }
        }
        return $wp_terms;
      }
    }

    return array(); // No terms found
  }
}

if (!function_exists('has_term')) {
  /**
   * Check if the current post has any of given terms.
   *
   * @param string|int|array $term     Term to check for.
   * @param string           $taxonomy Taxonomy name.
   * @param int              $post_id  Optional. Post ID. Defaults to current post.
   * @return bool True if the post has terms, false otherwise.
   */
  function has_term($term = '', $taxonomy = '', $post_id = false) {
    global $wp_post;

    // If no post ID provided, use global post
    if (!$post_id) {
      $post_id = isset($wp_post) && isset($wp_post->ID) ? $wp_post->ID : null;
      if (!$post_id && isset($wp_post) && isset($wp_post->nid)) {
        $post_id = $wp_post->nid;
      }
    }

    if (!$post_id) {
      return false;
    }

    // Get terms for this post
    $terms = get_the_terms($post_id, $taxonomy);

    if (is_wp_error($terms) || empty($terms)) {
      return false;
    }

    // If no specific term requested, just check if any terms exist
    if (empty($term)) {
      return !empty($terms);
    }

    // Check if specific term exists
    if (is_array($term)) {
      foreach ($term as $t) {
        foreach ($terms as $post_term) {
          if ($post_term->term_id == $t || $post_term->name == $t || $post_term->slug == $t) {
            return true;
          }
        }
      }
      return false;
    } else {
      foreach ($terms as $post_term) {
        if ($post_term->term_id == $term || $post_term->name == $term || $post_term->slug == $term) {
          return true;
        }
      }
      return false;
    }
  }
}

if (!function_exists('has_category')) {
  /**
   * Check if the current post has any categories.
   *
   * @param string|int|array $category Optional. Category ID, name, slug, or array of IDs.
   * @param int              $post_id  Optional. Post ID. Defaults to current post.
   * @return bool True if the post has categories, false otherwise.
   */
  function has_category($category = '', $post_id = false) {
    return has_term($category, 'category', $post_id);
  }
}

if (!function_exists('the_category')) {
  /**
   * Display the category list for a post.
   *
   * @param string $separator Separator.
   * @param string $parents   How to display parents.
   * @param int    $post_id   Post ID.
   */
  function the_category($separator = '', $parents = '', $post_id = false) {
    echo get_the_category_list($separator, $parents, $post_id);
  }
}

if (!function_exists('wp_link_pages')) {
  /**
   * Display page-links for paginated posts.
   *
   * @param array $args Arguments.
   * @return string Page links HTML.
   */
  function wp_link_pages($args = array()) {
    $defaults = array(
      'before' => '<p class="post-nav-links">',
      'after' => '</p>',
      'echo' => true,
    );
    $args = wp_parse_args($args, $defaults);
    
    // Stub - multi-page posts not fully supported
    $output = '';
    
    if ($args['echo']) {
      echo $output;
    }
    return $output;
  }
}

if (!function_exists('the_posts_pagination')) {
  /**
   * Display a paginated navigation to next/previous set of posts.
   *
   * @param array $args Arguments.
   */
  function the_posts_pagination($args = array()) {
    // Stub - pagination not fully implemented
    echo '<!-- posts pagination placeholder -->';
  }
}

if (!function_exists('the_post_navigation')) {
  /**
   * Display navigation to next/previous post.
   *
   * @param array $args Arguments.
   */
  function the_post_navigation($args = array()) {
    // Stub - post navigation not fully implemented
    echo '<!-- post navigation placeholder -->';
  }
}

if (!function_exists('edit_post_link')) {
  /**
   * Display edit post link for post.
   *
   * @param string $text   Link text.
   * @param string $before Before link.
   * @param string $after  After link.
   * @param int    $id     Post ID.
   * @param string $class  Link class.
   */
  function edit_post_link($text = null, $before = '', $after = '', $id = 0, $class = 'post-edit-link') {
    // Stub - don't show edit links for now
  }
}

if (!function_exists('get_edit_post_link')) {
  /**
   * Retrieve edit post link for post.
   *
   * @param int    $id      Post ID.
   * @param string $context Context.
   * @return string|null Edit post URL or null.
   */
  function get_edit_post_link($id = 0, $context = 'display') {
    return null;  // Stub
  }
}

if (!function_exists('the_custom_header_markup')) {
  /**
   * Display the custom header markup.
   */
  function the_custom_header_markup() {
    $header_image = get_header_image();
    
    if ($header_image) {
      $header = get_custom_header();
      $width = !empty($header->width) ? $header->width : 2000;
      $height = !empty($header->height) ? $header->height : 1200;
      $alt = get_bloginfo('name');
      
      echo '<div id="wp-custom-header" class="wp-custom-header">';
      echo '<img src="' . esc_url($header_image) . '" width="' . esc_attr($width) . '" height="' . esc_attr($height) . '" alt="' . esc_attr($alt) . '">';
      echo '</div>';
    }
  }
}

if (!function_exists('get_the_modified_time')) {
  /**
   * Retrieve the time at which the post was last modified.
   *
   * @param string $format  Optional. Time format.
   * @param bool   $gmt     Optional. Use GMT.
   * @param int    $post_id Optional. Post ID.
   * @param bool   $translate Optional. Translate.
   * @return string|int|false Modified time.
   */
  function get_the_modified_time($format = '', $gmt = false, $post_id = 0, $translate = true) {
    global $wp_post;
    
    $post = $wp_post;
    if ($post_id) {
      $post = get_post($post_id);
    }
    
    if (!$post) {
      return false;
    }
    
    // Get modified date
    $modified = '';
    if (isset($post->post_modified)) {
      $modified = $post->post_modified;
    } elseif (isset($post->changed)) {
      // Backdrop uses 'changed' timestamp
      $modified = date('Y-m-d H:i:s', $post->changed);
    }
    
    if (empty($modified)) {
      return false;
    }
    
    if (empty($format)) {
      $format = get_option('time_format');
    }
    
    return date($format, strtotime($modified));
  }
}

if (!function_exists('get_the_modified_date')) {
  /**
   * Retrieve the date on which the post was last modified.
   *
   * @param string $format  Optional. Date format.
   * @param int    $post_id Optional. Post ID.
   * @return string|int|false Modified date.
   */
  function get_the_modified_date($format = '', $post_id = 0) {
    global $wp_post;
    
    $post = $wp_post;
    if ($post_id) {
      $post = get_post($post_id);
    }
    
    if (!$post) {
      return false;
    }
    
    // Get modified date
    $modified = '';
    if (isset($post->post_modified)) {
      $modified = $post->post_modified;
    } elseif (isset($post->changed)) {
      // Backdrop uses 'changed' timestamp
      $modified = date('Y-m-d H:i:s', $post->changed);
    }
    
    if (empty($modified)) {
      return false;
    }
    
    if (empty($format)) {
      $format = get_option('date_format');
    }
    
    return date($format, strtotime($modified));
  }
}

if (!function_exists('get_transient')) {
  /**
   * Retrieve transient value.
   *
   * @param string $transient Transient name.
   * @return mixed Transient value or false.
   */
  function get_transient($transient) {
    // Use Backdrop's cache if available
    if (function_exists('cache_get')) {
      $cache = cache_get('wp_transient_' . $transient, 'cache');
      if ($cache && isset($cache->data)) {
        return $cache->data;
      }
    }
    return false;
  }
}

if (!function_exists('set_transient')) {
  /**
   * Set transient value.
   *
   * @param string $transient  Transient name.
   * @param mixed  $value      Transient value.
   * @param int    $expiration Expiration in seconds.
   * @return bool True on success.
   */
  function set_transient($transient, $value, $expiration = 0) {
    if (function_exists('cache_set')) {
      $expire = $expiration ? REQUEST_TIME + $expiration : CACHE_PERMANENT;
      cache_set('wp_transient_' . $transient, $value, 'cache', $expire);
      return true;
    }
    return false;
  }
}

if (!function_exists('delete_transient')) {
  /**
   * Delete transient value.
   *
   * @param string $transient Transient name.
   * @return bool True on success.
   */
  function delete_transient($transient) {
    if (function_exists('cache_clear_all')) {
      cache_clear_all('wp_transient_' . $transient, 'cache');
      return true;
    }
    return false;
  }
}

if (!function_exists('get_avatar')) {
  /**
   * Retrieve the avatar HTML.
   *
   * @param mixed $id_or_email User ID, email, or comment object.
   * @param int   $size        Avatar size.
   * @param string $default    Default avatar URL.
   * @param string $alt        Alt text.
   * @param array  $args       Extra arguments.
   * @return string|false Avatar HTML or false.
   */
  function get_avatar($id_or_email, $size = 96, $default = '', $alt = '', $args = null) {
    // Return Gravatar placeholder
    $email_hash = '';
    if (is_numeric($id_or_email)) {
      // User ID
      if (function_exists('user_load')) {
        $user = user_load($id_or_email);
        if ($user && isset($user->mail)) {
          $email_hash = md5(strtolower(trim($user->mail)));
        }
      }
    } elseif (is_string($id_or_email)) {
      $email_hash = md5(strtolower(trim($id_or_email)));
    }
    
    if (empty($email_hash)) {
      return '';
    }
    
    $url = 'https://www.gravatar.com/avatar/' . $email_hash . '?s=' . $size . '&d=mm';
    return '<img alt="' . esc_attr($alt) . '" src="' . esc_url($url) . '" class="avatar avatar-' . $size . '" height="' . $size . '" width="' . $size . '" />';
  }
}

/**
 * Determine if an attachment is an image.
 *
 * @param int $post_id Attachment ID.
 * @return bool True if image, false otherwise.
 */
if (!function_exists('wp_attachment_is_image')) {
  function wp_attachment_is_image($post_id = 0) {
    // For WP4BD, we'll return false for now as we don't have attachment support
    // This prevents errors in theme scripts that check for featured images
    return false;
  }
}

// -----------------------------------------------------------------------------
// Query helpers (WordPress global wrappers)
// -----------------------------------------------------------------------------

if (!function_exists('get_queried_object')) {
  function get_queried_object() {
    global $wp_query;
    if (isset($wp_query) && method_exists($wp_query, 'get_queried_object')) {
      return $wp_query->get_queried_object();
    }
    return null;
  }
}

if (!function_exists('get_queried_object_id')) {
  function get_queried_object_id() {
    global $wp_query;
    if (isset($wp_query) && property_exists($wp_query, 'queried_object_id')) {
      return $wp_query->queried_object_id;
    }
    $obj = get_queried_object();
    return ($obj && isset($obj->ID)) ? $obj->ID : 0;
  }
}

/**
 * Retrieves the path of a file in the theme.
 *
 * @param string $file Optional. File to return the path for in the theme directory.
 * @return string The path of the file.
 */
if (!function_exists('get_theme_file_path')) {
  function get_theme_file_path($file = '') {
    $file = ltrim($file, '/');
    return get_template_directory() . ($file ? '/' . $file : '');
  }
}

/**
 * Retrieves the path of a file in the parent theme.
 *
 * @param string $file Optional. File to return the path for in the template directory.
 * @return string The path of the file.
 */
if (!function_exists('get_parent_theme_file_path')) {
  function get_parent_theme_file_path($file = '') {
    $file = ltrim($file, '/');
    return get_theme_file_path($file);
  }
}

