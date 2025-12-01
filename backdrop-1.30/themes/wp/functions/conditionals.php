<?php
/**
 * WordPress Conditional Functions for Backdrop
 *
 * Functions that determine what type of page is being displayed.
 * Critical for template logic and theme functionality.
 *
 * @package WP2BD
 * @subpackage Conditionals
 */

/**
 * Determines whether the query is for an existing single post.
 *
 * WordPress Behavior:
 * - Returns true if viewing a single post (not page, not attachment)
 * - Works within The Loop and on single post pages
 * - Can check for specific post by ID, slug, or array of IDs/slugs
 * - Returns false for pages, archives, home page
 *
 * Backdrop Mapping:
 * - Uses menu_get_object('node') to get current node
 * - Checks if node type is NOT 'page' (so 'post', 'article', custom types)
 * - Verifies we're viewing a single node, not a list
 * - Optionally checks if node matches specific criteria
 *
 * @since WordPress 1.5.0
 * @since WP2BD 1.0.0
 *
 * @global object $wp_post The current post object (WP2BD compatibility).
 *
 * @param int|string|array $post Optional. Post ID, post slug, or array of such to check against.
 * @return bool Whether the query is for an existing single post.
 */
function is_single( $post = '' ) {
  global $wp_post;

  // Check if we're on a single node page using Backdrop's menu system
  $node = null;

  // First try to get node from Backdrop's menu_get_object
  if ( function_exists( 'menu_get_object' ) ) {
    $node = menu_get_object( 'node' );
  }

  // Fallback to global $wp_post if available
  if ( ! $node && isset( $wp_post ) && is_object( $wp_post ) ) {
    $node = $wp_post;
  }

  // If no node found, we're not on a single post page
  if ( ! $node || ! is_object( $node ) ) {
    return false;
  }

  // Get the node type
  $node_type = '';
  if ( isset( $node->type ) ) {
    // Backdrop-style node
    $node_type = $node->type;
  } elseif ( isset( $node->post_type ) ) {
    // WordPress-style post
    $node_type = $node->post_type;
  }

  // is_single() returns false for pages
  if ( $node_type === 'page' ) {
    return false;
  }

  // If no node type found, can't determine if single
  if ( empty( $node_type ) ) {
    return false;
  }

  // If no specific post requested, return true (we're on a single post)
  if ( empty( $post ) ) {
    return true;
  }

  // Check if the current post matches the requested post
  return _wp2bd_check_post_match( $node, $post );
}

/**
 * Determines whether the query is for an existing single post of any post type.
 *
 * WordPress Behavior:
 * - Returns true if viewing ANY single post, page, or custom post type
 * - More inclusive than is_single() - includes pages
 * - Can check for specific post types
 * - Returns false for archives, home page, search results
 *
 * Backdrop Mapping:
 * - Uses menu_get_object('node') to get current node
 * - Checks if viewing a single node (any type including pages)
 * - Optionally filters by post type(s)
 * - Returns true for any single content view
 *
 * @since WordPress 1.5.0
 * @since WP2BD 1.0.0
 *
 * @global object $wp_post The current post object (WP2BD compatibility).
 *
 * @param string|array $post_types Optional. Post type or array of post types to check against.
 * @return bool Whether the query is for an existing single post of any post type.
 */
function is_singular( $post_types = '' ) {
  global $wp_post;

  // Check if we're on a single node page using Backdrop's menu system
  $node = null;

  // First try to get node from Backdrop's menu_get_object
  if ( function_exists( 'menu_get_object' ) ) {
    $node = menu_get_object( 'node' );
  }

  // Fallback to global $wp_post if available
  if ( ! $node && isset( $wp_post ) && is_object( $wp_post ) ) {
    $node = $wp_post;
  }

  // If no node found, we're not on a singular page
  if ( ! $node || ! is_object( $node ) ) {
    return false;
  }

  // If no specific post types requested, return true (we're on ANY singular page)
  if ( empty( $post_types ) ) {
    return true;
  }

  // Get the node type
  $node_type = '';
  if ( isset( $node->type ) ) {
    // Backdrop-style node
    $node_type = $node->type;
  } elseif ( isset( $node->post_type ) ) {
    // WordPress-style post
    $node_type = $node->post_type;
  }

  // If no node type found, can't match against requested types
  if ( empty( $node_type ) ) {
    return false;
  }

  // Convert post_types to array if string
  if ( ! is_array( $post_types ) ) {
    $post_types = array( $post_types );
  }

  // Check if current node type matches any of the requested types
  return in_array( $node_type, $post_types, true );
}

/**
 * Helper function to check if a node matches post criteria.
 *
 * Checks if a node matches a given post ID, slug, or array of IDs/slugs.
 *
 * @since WP2BD 1.0.0
 * @access private
 *
 * @param object       $node The node object to check.
 * @param int|string|array $post Post ID, slug, or array of such.
 * @return bool Whether the node matches the criteria.
 */
function _wp2bd_check_post_match( $node, $post ) {
  // Convert single value to array for uniform processing
  if ( ! is_array( $post ) ) {
    $post = array( $post );
  }

  // Get node ID
  $node_id = null;
  if ( isset( $node->nid ) ) {
    $node_id = (int) $node->nid;
  } elseif ( isset( $node->ID ) ) {
    $node_id = (int) $node->ID;
  }

  // Get node name/slug (for Backdrop this might be in URL alias or title)
  $node_name = '';
  if ( isset( $node->name ) ) {
    $node_name = $node->name;
  } elseif ( isset( $node->post_name ) ) {
    $node_name = $node->post_name;
  }

  // Check each criteria
  foreach ( $post as $check ) {
    // Check by ID (integer)
    if ( is_numeric( $check ) ) {
      if ( $node_id && (int) $check === $node_id ) {
        return true;
      }
    }
    // Check by slug/name (string)
    elseif ( is_string( $check ) ) {
      if ( $node_name && $check === $node_name ) {
        return true;
      }
    }
  }

  return false;
}

/**
 * Determines whether the query is for an existing single page.
 *
 * WordPress Behavior:
 * - Returns true if viewing a page (not a post, not an archive)
 * - Pages are hierarchical content not organized by date
 * - Can check for specific page by ID, title, slug, or array of such
 * - Returns false for posts, archives, home page
 *
 * Backdrop Mapping:
 * - Uses menu_get_object('node') to get current node
 * - Checks if node type is 'page'
 * - Verifies we're viewing a single node, not a list
 * - Optionally checks if node matches specific criteria
 *
 * @since WordPress 1.5.0
 * @since WP2BD 1.0.0
 *
 * @global object $wp_post The current post object (WP2BD compatibility).
 * @global WP_Query $wp_query WordPress Query object (if available).
 *
 * @param int|string|array $page Optional. Page ID, title, slug, or array of such to check against.
 * @return bool Whether the query is for an existing single page.
 *
 * @example
 * ```php
 * // Check if viewing any page
 * if (is_page()) {
 *     echo 'This is a page';
 * }
 *
 * // Check if viewing specific page by ID
 * if (is_page(42)) {
 *     echo 'This is page 42';
 * }
 *
 * // Check if viewing specific page by slug
 * if (is_page('about')) {
 *     echo 'This is the about page';
 * }
 *
 * // Check if viewing one of multiple pages
 * if (is_page(array('about', 'contact', 'team'))) {
 *     echo 'This is one of our info pages';
 * }
 * ```
 */
function is_page( $page = '' ) {
  global $wp_post, $wp_query;

  // Try WP_Query first if available (more reliable)
  if ( isset( $wp_query ) && is_object( $wp_query ) && method_exists( $wp_query, 'is_page' ) ) {
    return $wp_query->is_page( $page );
  }

  // Fallback to Backdrop direct checking
  // Check if we're on a single node page using Backdrop's menu system
  $node = null;

  // First try to get node from Backdrop's menu_get_object
  if ( function_exists( 'menu_get_object' ) ) {
    $node = menu_get_object( 'node' );
  }

  // Fallback to global $wp_post if available
  if ( ! $node && isset( $wp_post ) && is_object( $wp_post ) ) {
    $node = $wp_post;
  }

  // If no node found, we're not on a page
  if ( ! $node || ! is_object( $node ) ) {
    return false;
  }

  // Get the node type
  $node_type = '';
  if ( isset( $node->type ) ) {
    // Backdrop-style node
    $node_type = $node->type;
  } elseif ( isset( $node->post_type ) ) {
    // WordPress-style post
    $node_type = $node->post_type;
  }

  // is_page() returns true ONLY for pages
  if ( $node_type !== 'page' ) {
    return false;
  }

  // If no specific page requested, return true (we're on a page)
  if ( empty( $page ) ) {
    return true;
  }

  // Check if the current page matches the requested page
  return _wp2bd_check_page_match( $node, $page );
}

/**
 * Helper function to check if a node matches page criteria.
 *
 * Checks if a node matches a given page ID, title, slug, or array of such.
 * Similar to _wp2bd_check_post_match but also checks page title.
 *
 * @since WP2BD 1.0.0
 * @access private
 *
 * @param object       $node The node object to check.
 * @param int|string|array $page Page ID, title, slug, or array of such.
 * @return bool Whether the node matches the criteria.
 */
function _wp2bd_check_page_match( $node, $page ) {
  // Convert single value to array for uniform processing
  if ( ! is_array( $page ) ) {
    $page = array( $page );
  }

  // Get node ID
  $node_id = null;
  if ( isset( $node->nid ) ) {
    $node_id = (int) $node->nid;
  } elseif ( isset( $node->ID ) ) {
    $node_id = (int) $node->ID;
  }

  // Get node name/slug
  $node_name = '';
  if ( isset( $node->name ) ) {
    $node_name = $node->name;
  } elseif ( isset( $node->post_name ) ) {
    $node_name = $node->post_name;
  }

  // Get node title
  $node_title = '';
  if ( isset( $node->title ) ) {
    $node_title = $node->title;
  } elseif ( isset( $node->post_title ) ) {
    $node_title = $node->post_title;
  }

  // Check each criteria
  foreach ( $page as $check ) {
    // Convert to string for comparison
    $check = (string) $check;

    // Check by ID (integer)
    if ( is_numeric( $check ) ) {
      if ( $node_id && (int) $check === $node_id ) {
        return true;
      }
    }

    // Check by slug/name (string)
    if ( $node_name && $check === $node_name ) {
      return true;
    }

    // Check by title (string)
    if ( $node_title && $check === $node_title ) {
      return true;
    }

    // Check by path (if contains slashes)
    if ( strpos( $check, '/' ) !== false ) {
      // Try to resolve path to node ID using Backdrop's path system
      if ( function_exists( 'backdrop_lookup_path' ) ) {
        $internal_path = backdrop_lookup_path( 'source', $check );
        if ( $internal_path && preg_match( '/^node\/(\d+)$/', $internal_path, $matches ) ) {
          if ( $node_id && (int) $matches[1] === $node_id ) {
            return true;
          }
        }
      }
    }
  }

  return false;
}

/**
 * Determines whether the query is for an existing archive page.
 *
 * WordPress Behavior:
 * - Returns true for ANY archive listing (category, tag, taxonomy, date, author, post type)
 * - Returns false for single posts, pages, search results, 404, home/front page
 * - More inclusive than specific archive checks (is_category, is_tag, etc.)
 * - Uses WP_Query->is_archive property which is set when viewing any archive
 *
 * Backdrop Mapping:
 * - Check if viewing a taxonomy term page (category/tag equivalent)
 * - Check if WP_Query->is_archive is set (for programmatic queries)
 * - Check if viewing a user/author listing page
 * - NOT true for single node views, search, or 404
 * - Uses menu_get_object('taxonomy_term') to detect taxonomy term pages
 * - Uses arg() to detect user profile pages with listing context
 *
 * @since WordPress 1.5.0
 * @since WP2BD 1.0.0
 *
 * @global WP_Query $wp_query WordPress Query object (if available).
 *
 * @return bool Whether the query is for an existing archive page.
 *
 * @example
 * ```php
 * // Check if viewing any archive
 * if (is_archive()) {
 *     echo 'This is an archive page';
 * }
 *
 * // Typical usage in templates
 * if (is_archive()) {
 *     the_archive_title('<h1>', '</h1>');
 *     the_archive_description();
 * }
 *
 * // Differentiate from other page types
 * if (is_archive() && !is_search()) {
 *     echo 'This is an archive but not search results';
 * }
 * ```
 */
function is_archive() {
  global $wp_query;

  // First check WP_Query if available (most reliable method)
  if ( isset( $wp_query ) && is_object( $wp_query ) ) {
    // Check the is_archive property
    if ( isset( $wp_query->is_archive ) && $wp_query->is_archive ) {
      return true;
    }

    // Also check if WP_Query has is_archive() method
    if ( method_exists( $wp_query, 'is_archive' ) ) {
      return $wp_query->is_archive();
    }
  }

  // Backdrop-specific detection methods
  // Method 1: Check if we're on a taxonomy term page
  if ( function_exists( 'menu_get_object' ) ) {
    $term = menu_get_object( 'taxonomy_term' );
    if ( $term && is_object( $term ) ) {
      // We're viewing a taxonomy term page (category, tag, etc.)
      return true;
    }
  }

  // Method 2: Check if we're on a user/author listing page
  // In Backdrop, user profile pages might show lists of content
  if ( function_exists( 'arg' ) ) {
    $args = arg();

    // Check for user/uid pattern (e.g., user/1)
    if ( isset( $args[0] ) && $args[0] === 'user' && isset( $args[1] ) && is_numeric( $args[1] ) ) {
      // Could be a user profile with content listing
      // Additional check: make sure we're not on a single node
      if ( ! function_exists( 'menu_get_object' ) || ! menu_get_object( 'node' ) ) {
        return true;
      }
    }
  }

  // Method 3: Check current path for archive-like patterns
  if ( function_exists( 'current_path' ) ) {
    $path = current_path();

    // Common archive patterns in Backdrop/Drupal
    // - taxonomy/term/* (taxonomy term pages)
    // - blog/* (blog listing)
    // - archive/* (date archives if implemented)
    if ( preg_match( '#^(taxonomy/term/\d+|blog|archive)#', $path ) ) {
      return true;
    }
  }

  // Method 4: Check via Backdrop's menu router item
  if ( function_exists( 'menu_get_item' ) ) {
    $router_item = menu_get_item();

    if ( isset( $router_item['page_callback'] ) ) {
      $callback = $router_item['page_callback'];

      // Taxonomy term page callback
      if ( $callback === 'taxonomy_term_page' ) {
        return true;
      }

      // Views page callback (common for archives in Backdrop)
      if ( $callback === 'views_page' ) {
        return true;
      }
    }
  }

  // If we're on a single post or page, this is NOT an archive
  if ( is_singular() ) {
    return false;
  }

  // Search results are NOT archives
  if ( is_search() ) {
    return false;
  }

  // 404 pages are NOT archives
  if ( function_exists( 'backdrop_get_http_header' ) ) {
    $status = backdrop_get_http_header( 'status' );
    if ( $status && strpos( $status, '404' ) !== false ) {
      return false;
    }
  }

  // Default to false
  return false;
}

/**
 * Determines whether the query is for a search.
 *
 * WordPress Behavior:
 * - Returns true when viewing search results
 * - Returns false for all other page types
 *
 * Backdrop Mapping:
 * - Check if WP_Query->is_search is set
 * - Check for search path patterns
 * - Check for search query parameters
 *
 * @since WordPress 1.5.0
 * @since WP2BD 1.0.0
 *
 * @global WP_Query $wp_query WordPress Query object (if available).
 *
 * @return bool Whether the query is for a search.
 */
function is_search() {
  global $wp_query;

  // Check WP_Query if available
  if ( isset( $wp_query ) && is_object( $wp_query ) ) {
    if ( isset( $wp_query->is_search ) && $wp_query->is_search ) {
      return true;
    }

    if ( method_exists( $wp_query, 'is_search' ) ) {
      return $wp_query->is_search();
    }
  }

  // Check for search path in Backdrop
  if ( function_exists( 'current_path' ) ) {
    $path = current_path();
    if ( strpos( $path, 'search' ) === 0 ) {
      return true;
    }
  }

  // Check for search query parameters
  if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) {
    return true;
  }

  return false;
}

/**
 * Determines whether the query is for the front page of the site.
 *
 * WordPress Behavior:
 * - Returns true if viewing the site's main URL/front page
 * - True when front page is set to show posts (default behavior)
 * - True when viewing a static page set as the front page
 * - Works with WordPress Reading Settings: 'page_on_front' option
 * - Only works after the query has been parsed
 *
 * Backdrop Mapping:
 * - Uses Backdrop's current path from $_GET['q']
 * - Compares against config_get('system.core', 'site_frontpage')
 * - Backdrop stores front page as a path (e.g., 'node', 'home', '<front>')
 * - Checks if current path matches the configured front page path
 * - Mimics Backdrop's backdrop_is_front_page() behavior
 *
 * @since WordPress 2.5.0
 * @since WP2BD 1.0.0
 *
 * @global WP_Query $wp_query WordPress Query object (if available).
 *
 * @return bool Whether the query is for the front page of the site.
 *
 * @example
 * ```php
 * // Check if viewing the front page
 * if (is_front_page()) {
 *     echo 'Welcome to our homepage!';
 * }
 *
 * // Different header for front page
 * if (is_front_page()) {
 *     get_header('home');
 * } else {
 *     get_header();
 * }
 *
 * // Combine with other conditionals
 * if (is_front_page() && is_home()) {
 *     // Front page displays blog posts
 * }
 * ```
 */
function is_front_page() {
  global $wp_query;

  // Try WP_Query first if available (more reliable for WordPress compatibility)
  if ( isset( $wp_query ) && is_object( $wp_query ) && method_exists( $wp_query, 'is_front_page' ) ) {
    return $wp_query->is_front_page();
  }

  // Fallback to Backdrop's front page detection
  if ( ! function_exists( 'config_get' ) ) {
    // If no Backdrop config system available, can't determine
    return false;
  }

  // Get the configured front page path
  $front_page_path = config_get( 'system.core', 'site_frontpage' );

  // Default to 'node' if not configured
  if ( empty( $front_page_path ) ) {
    $front_page_path = 'node';
  }

  // Get current path from Backdrop's $_GET['q']
  $current_path = isset( $_GET['q'] ) ? $_GET['q'] : '';

  // Special handling for '<front>' marker
  if ( $current_path === '<front>' || $front_page_path === '<front>' ) {
    return true;
  }

  // Empty current path means we're on the front page
  if ( empty( $current_path ) ) {
    return true;
  }

  // Compare current path with configured front page path
  return $current_path === $front_page_path;
}

/**
 * Determines whether the query is for the blog posts index page.
 *
 * WordPress Behavior:
 * - Returns true if viewing the blog posts index (main blog page)
 * - True when front page is set to show latest posts (default)
 * - True when viewing a separate page designated as the "Posts page"
 * - Works with WordPress Reading Settings: 'page_for_posts' option
 * - Different from is_front_page() - focuses on blog content, not site home
 * - Only works after the query has been parsed
 *
 * Backdrop Mapping:
 * - Checks if viewing the blog/posts listing page
 * - True if on front page AND front page shows blog content
 * - True if on a dedicated posts/blog listing page
 * - Backdrop typically uses 'node' path for default blog listing
 * - Can be configured via WP2BD settings for custom blog page
 *
 * Common Scenarios:
 * - Front page shows posts: is_home() = true, is_front_page() = true
 * - Front page is static, separate posts page: is_home() = true on posts page only
 * - Viewing a single post: is_home() = false
 * - Viewing a page: is_home() = false
 *
 * @since WordPress 1.5.0
 * @since WP2BD 1.0.0
 *
 * @global WP_Query $wp_query WordPress Query object (if available).
 *
 * @return bool Whether the query is for the blog posts index page.
 *
 * @example
 * ```php
 * // Show blog header only on blog index
 * if (is_home() && !is_front_page()) {
 *     echo '<h1>Blog</h1>';
 * }
 *
 * // Check if showing blog posts
 * if (is_home()) {
 *     echo 'Displaying latest posts';
 * }
 *
 * // Different template for blog index
 * if (is_home()) {
 *     get_template_part('template-parts/content', 'blog');
 * }
 * ```
 */
function is_paged() {
  global $wp_query;

  // Check if we're on page 2+ of paginated content
  if (isset($wp_query) && is_object($wp_query) && method_exists($wp_query, 'is_paged')) {
    return $wp_query->is_paged();
  }

  // Fallback: check for page parameter in URL
  $paged = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  if ($paged > 1) {
    return true;
  }

  // Check arg() for pager
  if (function_exists('arg')) {
    $args = explode('/', $_GET['q']);
    if (in_array('page', $args)) {
      return true;
    }
  }

  return false;
}

function is_attachment() {
  global $post;

  // Check if current post is an attachment
  if (isset($post) && is_object($post)) {
    return isset($post->post_type) && $post->post_type === 'attachment';
  }

  return false;
}

function is_home() {
  global $wp_query;

  // Try WP_Query first if available (more reliable for WordPress compatibility)
  if ( isset( $wp_query ) && is_object( $wp_query ) && method_exists( $wp_query, 'is_home' ) ) {
    return $wp_query->is_home();
  }

  // Fallback to Backdrop-based detection
  if ( ! function_exists( 'config_get' ) ) {
    // If no Backdrop config system available, can't determine
    return false;
  }

  // Get current path from Backdrop
  $current_path = isset( $_GET['q'] ) ? $_GET['q'] : '';

  // Get configured paths
  $front_page_path = config_get( 'system.core', 'site_frontpage' );
  $posts_page_path = null;

  // Check for WP2BD-specific posts page configuration
  if ( function_exists( 'config_get' ) ) {
    $posts_page_path = config_get( 'wp2bd.settings', 'page_for_posts' );
  }

  // Default front page to 'node' if not configured
  if ( empty( $front_page_path ) ) {
    $front_page_path = 'node';
  }

  // Empty current path means root/front page
  if ( empty( $current_path ) ) {
    $current_path = $front_page_path;
  }

  // Scenario 1: We're on a dedicated posts page (if configured)
  if ( ! empty( $posts_page_path ) && $current_path === $posts_page_path ) {
    return true;
  }

  // Scenario 2: We're on front page AND front page shows blog content
  // Check if current path matches front page
  $is_front = ( $current_path === $front_page_path ) ||
              ( $current_path === '<front>' ) ||
              ( $front_page_path === '<front>' && empty( $current_path ) );

  if ( ! $is_front ) {
    return false;
  }

  // Now determine if front page shows blog content
  // In Backdrop, 'node' is the default blog/content listing
  // Also check for common blog paths
  $blog_paths = array( 'node', 'blog', 'posts', 'articles' );

  // If front page is set to a blog-type path, it's showing blog content
  if ( in_array( $front_page_path, $blog_paths, true ) ) {
    return true;
  }

  // If front page is set to a specific node (e.g., 'node/123'), it's NOT showing blog index
  if ( preg_match( '/^node\/\d+$/', $front_page_path ) ) {
    return false;
  }

  // If front page is set to a custom path that's not a node, likely NOT blog
  // But if it's explicitly the root and no posts page is configured, assume it's the blog
  if ( $front_page_path === 'node' || empty( $front_page_path ) ) {
    return true;
  }

  // Default: if on front page but can't determine type, assume NOT blog
  return false;
}

/**
 * Helper function to get the current Backdrop path.
 *
 * Gets the current path from Backdrop's routing system.
 * Uses $_GET['q'] which Backdrop sets via backdrop_path_initialize().
 *
 * @since WP2BD 1.0.0
 * @access private
 *
 * @return string The current path, or empty string if on root.
 */
function _wp2bd_get_current_path() {
  return isset( $_GET['q'] ) ? $_GET['q'] : '';
}

/**
 * Retrieves the post type of the current post or a given post.
 *
 * WordPress Behavior:
 * - Returns the post type string ('post', 'page', custom post type, etc.)
 * - Returns false if the post does not exist
 * - Uses global $post if no parameter provided
 * - Accepts post ID, post object, or null
 *
 * Backdrop Mapping:
 * - Maps to $node->type in Backdrop nodes
 * - Maps to $post->post_type in WP_Post objects
 * - Returns 'post' as default for blog nodes
 * - Returns 'page' for page content type
 * - Returns false if no post/node found
 *
 * @since WordPress 2.1.0
 * @since WP2BD 1.0.0
 *
 * @global object $wp_post The current post object (WP2BD compatibility).
 *
 * @param int|WP_Post|object|null $post Optional. Post ID or post object. Default is global $post.
 * @return string|false Post type on success, false on failure.
 *
 * @example
 * ```php
 * // Get current post type
 * $type = get_post_type();
 * if ( $type === 'post' ) {
 *     echo 'This is a blog post';
 * }
 *
 * // Get post type by ID
 * $type = get_post_type( 42 );
 *
 * // Get post type from object
 * $type = get_post_type( $post );
 *
 * // Use in conditionals
 * if ( get_post_type() === 'page' ) {
 *     // Page-specific code
 * }
 * ```
 */
function get_post_type( $post = null ) {
  global $wp_post;

  // If no post provided, use global $wp_post
  if ( null === $post ) {
    $post = isset( $wp_post ) ? $wp_post : null;
  }

  // If we still don't have a post, return false
  if ( ! $post ) {
    return false;
  }

  // If post is an integer (ID), try to load the post
  if ( is_numeric( $post ) ) {
    $post_id = (int) $post;

    // Try to load from Backdrop if available
    if ( function_exists( 'node_load' ) ) {
      $node = node_load( $post_id );
      if ( $node && is_object( $node ) ) {
        $post = $node;
      } else {
        return false;
      }
    } else {
      // Can't load post by ID without node_load
      return false;
    }
  }

  // Now we should have a post object - extract the type
  if ( ! is_object( $post ) ) {
    return false;
  }

  // Check for WordPress-style post_type property
  if ( isset( $post->post_type ) && ! empty( $post->post_type ) ) {
    return $post->post_type;
  }

  // Check for Backdrop-style type property
  if ( isset( $post->type ) && ! empty( $post->type ) ) {
    return $post->type;
  }

  // No type found
  return false;
}

/**
 * Determines whether a post is sticky.
 *
 * WordPress Behavior:
 * - Returns true if the post is marked as "sticky" (pinned to the top)
 * - Sticky posts are stored in an array in the options table
 * - Takes optional post ID parameter, defaults to current post
 * - Sticky posts typically appear at the top of the blog index
 * - Does not affect page display, only post listings
 *
 * Backdrop Mapping:
 * - Maps to Backdrop's "promote to front page" flag ($node->promote)
 * - Also checks Backdrop's $node->sticky flag (similar concept)
 * - Returns true if either promote or sticky flag is set to 1
 * - Handles both WordPress-style and Backdrop-style node objects
 * - WordPress sticky_posts option maps to individual node properties
 *
 * @since WordPress 2.7.0
 * @since WP2BD 1.0.0
 *
 * @global object $wp_post The current post object (WP2BD compatibility).
 *
 * @param int|WP_Post|object $post_id Optional. Post ID or post object. Default is global $post.
 * @return bool Whether the post is sticky.
 *
 * @example
 * ```php
 * // Check if current post is sticky
 * if (is_sticky()) {
 *     echo 'This post is pinned!';
 * }
 *
 * // Check if specific post is sticky
 * if (is_sticky(42)) {
 *     echo 'Post 42 is sticky';
 * }
 * ```
 */
function is_sticky( $post_id = null ) {
  global $wp_post;

  // Get the post object
  $post = null;

  // Handle different input types
  if ( null === $post_id ) {
    // No parameter provided - use global post
    $post = isset( $wp_post ) ? $wp_post : null;
  } elseif ( is_numeric( $post_id ) ) {
    // Post ID provided - try to load it
    // In Backdrop environment, try to load the node
    if ( function_exists( 'node_load' ) ) {
      $post = node_load( (int) $post_id );
    } else {
      // Fallback: create minimal object with just the ID
      // This allows checking against sticky_posts option if available
      $post = (object) array( 'ID' => (int) $post_id, 'nid' => (int) $post_id );
    }
  } elseif ( is_object( $post_id ) ) {
    // Post object provided directly
    $post = $post_id;
  }

  // If no post found, return false
  if ( ! $post || ! is_object( $post ) ) {
    return false;
  }

  // Get post ID from the object (handle both WP and Backdrop styles)
  $id = null;
  if ( isset( $post->ID ) ) {
    $id = (int) $post->ID;
  } elseif ( isset( $post->nid ) ) {
    $id = (int) $post->nid;
  }

  // If no valid ID found, return false
  if ( ! $id || $id <= 0 ) {
    return false;
  }

  // Method 1: Check WordPress-style sticky_posts option
  // This maintains compatibility with WordPress data
  if ( function_exists( 'get_option' ) ) {
    $sticky_posts = get_option( 'sticky_posts' );
    if ( is_array( $sticky_posts ) && in_array( $id, $sticky_posts, true ) ) {
      return true;
    }
  }

  // Method 2: Check Backdrop node properties
  // Backdrop has two relevant flags:
  // - $node->promote: Promoted to front page (closest to WP sticky)
  // - $node->sticky: Sticky at top of lists

  // Check Backdrop's sticky flag (most direct equivalent)
  if ( isset( $post->sticky ) && $post->sticky == 1 ) {
    return true;
  }

  // Check Backdrop's promote flag (alternate mapping)
  if ( isset( $post->promote ) && $post->promote == 1 ) {
    return true;
  }

  // Method 3: Check WordPress-style post meta
  // Some implementations might store sticky status in post meta
  if ( function_exists( 'get_post_meta' ) ) {
    $is_sticky_meta = get_post_meta( $id, '_sticky', true );
    if ( $is_sticky_meta === '1' || $is_sticky_meta === 1 || $is_sticky_meta === true ) {
      return true;
    }
  }

  // Not sticky
  return false;
}

/**
 * Determines whether the query resulted in a 404 (Not Found) error.
 *
 * WordPress Behavior:
 * - Returns true when the requested content was not found (404 error)
 * - Returns false for all other page types (posts, pages, archives, etc.)
 * - Set when WordPress cannot find matching content for the request
 * - Commonly used in templates to display custom 404 pages
 *
 * Backdrop Mapping:
 * - Checks WP_Query->is_404 flag (most reliable for programmatic queries)
 * - Checks Backdrop's HTTP status header for "404" response
 * - Uses backdrop_get_http_header('status') to detect 404 responses
 * - Returns true if no content found via menu_get_object
 * - Compatible with Backdrop's MENU_NOT_FOUND status
 *
 * @since WordPress 1.5.0
 * @since WP2BD 1.0.0
 *
 * @global WP_Query $wp_query WordPress Query object (if available).
 *
 * @return bool Whether the query is for a 404 error page.
 *
 * @example
 * ```php
 * // Display custom 404 content
 * if (is_404()) {
 *     echo '<h1>Page Not Found</h1>';
 *     echo '<p>The page you requested does not exist.</p>';
 * }
 *
 * // Redirect 404s to homepage
 * if (is_404()) {
 *     wp_redirect(home_url());
 *     exit;
 * }
 *
 * // Track 404 errors
 * if (is_404()) {
 *     error_log('404 Error: ' . $_SERVER['REQUEST_URI']);
 * }
 *
 * // Load different template for 404
 * if (is_404()) {
 *     get_template_part('template-parts/content', 'none');
 * }
 * ```
 */
function is_404() {
  global $wp_query;

  // Method 1: Check WP_Query if available (most reliable for WordPress compatibility)
  if ( isset( $wp_query ) && is_object( $wp_query ) ) {
    // Check the is_404 property
    if ( isset( $wp_query->is_404 ) && $wp_query->is_404 ) {
      return true;
    }

    // Check if WP_Query has is_404() method
    if ( method_exists( $wp_query, 'is_404' ) ) {
      return $wp_query->is_404();
    }
  }

  // Method 2: Check Backdrop's HTTP status header
  // Backdrop sets HTTP status via backdrop_deliver_html_page() and backdrop_set_http_header()
  if ( function_exists( 'backdrop_get_http_header' ) ) {
    $status = backdrop_get_http_header( 'status' );

    // Check if status contains "404"
    // Common formats: "404 Not Found", "404", etc.
    if ( $status && strpos( $status, '404' ) !== false ) {
      return true;
    }
  }

  // Method 3: Check if we have any content loaded
  // If menu_get_object returns null for both node and taxonomy_term,
  // and we're not on a special page (front, search, etc.), it might be a 404
  if ( function_exists( 'menu_get_object' ) ) {
    $node = menu_get_object( 'node' );
    $term = menu_get_object( 'taxonomy_term' );

    // If no object found, check if we're on a valid special page
    if ( ! $node && ! $term ) {
      // Not a 404 if we're on front page
      if ( is_front_page() ) {
        return false;
      }

      // Not a 404 if we're on search page
      if ( is_search() ) {
        return false;
      }

      // Not a 404 if we're on archive/listing page
      // (archives can be empty but aren't 404s)
      if ( is_archive() ) {
        return false;
      }

      // Check for admin pages or special paths
      if ( function_exists( 'arg' ) ) {
        $args = arg();
        if ( isset( $args[0] ) ) {
          $first_arg = $args[0];

          // Common valid paths that aren't 404s
          $valid_paths = array( 'admin', 'user', 'node', 'taxonomy', 'search', 'blog' );
          if ( in_array( $first_arg, $valid_paths, true ) ) {
            // These are handled by their respective systems, not 404s
            return false;
          }
        }
      }

      // No content and not a special page - likely a 404
      // But we need to be careful not to false-positive
      // Only return true if we have additional evidence

      // Check current path
      if ( function_exists( 'current_path' ) ) {
        $path = current_path();

        // Empty path is front page, not 404
        if ( empty( $path ) ) {
          return false;
        }

        // If we have a path but no content, it could be 404
        // But we still need to check menu system
        if ( function_exists( 'menu_get_item' ) ) {
          $router_item = menu_get_item();

          // If menu system returned no valid item, it's a 404
          if ( ! $router_item || ! isset( $router_item['page_callback'] ) ) {
            return true;
          }

          // If menu system explicitly says not found
          if ( isset( $router_item['access'] ) && $router_item['access'] === false ) {
            return true;
          }
        }
      }
    }
  }

  // Method 4: Check $_GET['q'] against known 404 patterns
  // Some Backdrop sites might not set HTTP headers properly
  if ( isset( $_GET['q'] ) ) {
    $path = $_GET['q'];

    // If menu_get_item shows MENU_NOT_FOUND constant (= 4 in Backdrop/Drupal)
    if ( function_exists( 'menu_get_item' ) ) {
      $item = menu_get_item();
      if ( isset( $item['access_callback'] ) && $item['access_callback'] === false ) {
        return true;
      }
    }
  }

  // Default: not a 404
  return false;
}

/**
 * Determines whether the query is for a feed.
 *
 * WordPress Behavior:
 * - Returns true when viewing an RSS/Atom feed
 * - Returns false for regular HTML pages
 * - Used to conditionally output feed-specific content
 *
 * Backdrop Mapping:
 * - Backdrop uses different paths for feeds (e.g., /rss.xml, /feed)
 * - Check current path for feed-related patterns
 * - Check for feed query parameters
 * - Always returns false in web page context (feeds handled separately)
 *
 * @since WordPress 1.5.0
 * @since WP2BD 1.0.0
 *
 * @param string|array $feeds Optional. Feed type or array of feed types to check.
 * @return bool Whether the query is for a feed.
 */
function is_feed($feeds = '') {
  // In the context of rendering WordPress themes in Backdrop,
  // we are always rendering HTML pages, not feeds.
  // Feeds in Backdrop are handled by separate paths/modules.
  
  // Check for feed-related paths
  if (function_exists('current_path')) {
    $path = current_path();
    $feed_patterns = array('rss.xml', 'feed', 'atom.xml', 'rss', 'atom');
    
    foreach ($feed_patterns as $pattern) {
      if (strpos($path, $pattern) !== false) {
        return true;
      }
    }
  }
  
  // Check for feed parameter in URL
  if (isset($_GET['feed']) && !empty($_GET['feed'])) {
    return true;
  }
  
  // Default: not a feed (we're rendering HTML)
  return false;
}

/**
 * Determines whether the current post uses a specific page template.
 *
 * WordPress Behavior:
 * - Checks if the current page is using a specific page template file
 * - Takes optional template parameter to check for specific template
 * - Returns true if viewing a page with matching template
 * - Returns false for non-page content or no matching template
 *
 * Backdrop Mapping:
 * - In WP2BD context, we don't have page templates like WordPress
 * - Always returns false unless we implement template tracking
 *
 * @since WordPress 2.5.0
 * @since WP2BD 1.0.0
 *
 * @param string|string[] $template The specific template filename or array of filenames to check for.
 * @return bool Whether the current page uses the given template.
 */
function is_page_template($template = '') {
    global $wp_post;
    
    // If not on a page, return false
    if (!is_page()) {
        return false;
    }
    
    // Get the current post
    $post = null;
    if (isset($wp_post) && is_object($wp_post)) {
        $post = $wp_post;
    } elseif (function_exists('menu_get_object')) {
        $post = menu_get_object('node');
    }
    
    if (!$post) {
        return false;
    }
    
    // Get page template from post meta
    $page_template = '';
    if (function_exists('get_post_meta')) {
        $page_template = get_post_meta(
            isset($post->ID) ? $post->ID : (isset($post->nid) ? $post->nid : 0),
            '_wp_page_template',
            true
        );
    }
    
    // If no specific template requested, check if any template is set
    if (empty($template)) {
        return !empty($page_template) && $page_template !== 'default';
    }
    
    // Normalize template parameter to array
    if (!is_array($template)) {
        $template = array($template);
    }
    
    // Check if current template matches any of the requested templates
    foreach ($template as $t) {
        // Handle both 'template.php' and 'templates/template.php' formats
        if ($page_template === $t) {
            return true;
        }
        // Also check if template matches basename
        if (basename($page_template) === basename($t)) {
            return true;
        }
    }
    
    return false;
}
