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
function get_template_directory() {
    static $template_dir = null;

    // Cache the result
    if ($template_dir !== null) {
        return $template_dir;
    }

    // Try Backdrop's path_to_theme() function first
    if (function_exists('path_to_theme')) {
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

    // Fallback: Use hardcoded path for Twenty Seventeen
    if ($template_dir === null) {
        $template_dir = '/home/user/wp2bd/wordpress-4.9/wp-content/themes/twentyseventeen';

        // Alternative: Try to detect from __FILE__ or environment
        if (!is_dir($template_dir) && defined('WP2BD_THEME_PATH')) {
            $template_dir = WP2BD_THEME_PATH;
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

/**
 * Retrieve stylesheet directory path for current theme.
 *
 * For child themes, this returns the child theme directory.
 * For parent themes, this is the same as get_template_directory().
 *
 * @return string Path to stylesheet directory (no trailing slash).
 */
function get_stylesheet_directory() {
    // In WP2BD, we don't support child themes yet, so return template directory
    return get_template_directory();
}

/**
 * Retrieve stylesheet directory URI for current theme.
 *
 * For child themes, this returns the child theme URI.
 * For parent themes, this is the same as get_template_directory_uri().
 *
 * @return string URI to stylesheet directory (no trailing slash).
 */
function get_stylesheet_directory_uri() {
    // In WP2BD, we don't support child themes yet, so return template directory URI
    return get_template_directory_uri();
}

/**
 * Retrieve the name of the current theme.
 *
 * @return string Template name (directory name).
 */
function get_template() {
    return basename(get_template_directory());
}

/**
 * Retrieve the name of the current stylesheet (theme).
 *
 * @return string Stylesheet name (directory name).
 */
function get_stylesheet() {
    return basename(get_stylesheet_directory());
}
