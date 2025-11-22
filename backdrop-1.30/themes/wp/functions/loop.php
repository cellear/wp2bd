<?php
/**
 * WP2BD Loop Functions
 *
 * Implements WordPress's "The Loop" - the core iteration mechanism for displaying posts/content.
 * These functions provide the state machine that allows templates to iterate through posts.
 *
 * @package WP2BD
 * @subpackage Loop
 * @priority P0 (CRITICAL - Required for ANY page render)
 */

// Define WordPress constants if not already defined
if (!defined('OBJECT')) {
    define('OBJECT', 'OBJECT');
}
if (!defined('OBJECT_K')) {
    define('OBJECT_K', 'OBJECT_K');
}
if (!defined('ARRAY_A')) {
    define('ARRAY_A', 'ARRAY_A');
}
if (!defined('ARRAY_N')) {
    define('ARRAY_N', 'ARRAY_N');
}

/**
 * Determines whether current WordPress query has posts to loop over.
 *
 * This is a global function wrapper around WP_Query::have_posts(). It checks
 * the global $wp_query object to determine if more posts remain in the current query.
 *
 * WordPress Behavior:
 * - Checks if more posts exist by comparing current_post + 1 < post_count
 * - Returns false if $wp_query is not set
 * - Used as condition in while loops: while (have_posts()) { the_post(); }
 *
 * @global WP_Query $wp_query WordPress Query object.
 * @return bool True if posts are available in the loop, false otherwise.
 */
function have_posts() {
    global $wp_query;

    if (!isset($wp_query)) {
        return false;
    }

    return $wp_query->have_posts();
}

/**
 * Iterate the post index in the loop and set up global post data.
 *
 * This is a global function wrapper around WP_Query::the_post(). It:
 * - Increments the current post counter
 * - Sets the global $post variable
 * - Calls setup_postdata() to populate template tag globals
 * - Fires the 'the_post' action hook
 *
 * WordPress Behavior:
 * - Must be called within a loop (after have_posts() returns true)
 * - Populates numerous globals used by template tags
 * - Should be followed by template tag calls (the_title(), the_content(), etc.)
 *
 * @global WP_Query $wp_query WordPress Query object.
 * @global WP_Post  $post     Global post object.
 * @return void
 */
function the_post() {
    global $wp_query;

    if (!isset($wp_query)) {
        return;
    }

    $wp_query->the_post();
}

/**
 * After looping through a separate query, this function restores the global $post
 * variable to the current post in the main query.
 *
 * This is critical when using custom WP_Query loops. After a custom query completes,
 * wp_reset_postdata() ensures that template tags like the_title() reference the
 * original main query post, not the last post from the custom query.
 *
 * WordPress Behavior:
 * - Restores global $post to the current post in the main $wp_query
 * - Calls setup_postdata() to repopulate template tag globals
 * - Does nothing if main query has no current post
 *
 * Example Usage:
 * <code>
 * $custom_query = new WP_Query('cat=5');
 * while ($custom_query->have_posts()) {
 *     $custom_query->the_post();
 *     // Display custom query post
 * }
 * wp_reset_postdata(); // Reset back to main query
 * </code>
 *
 * @global WP_Query $wp_query WordPress Query object.
 * @global WP_Post  $post     Global post object.
 * @return void
 */
function wp_reset_postdata() {
    global $wp_query, $post;

    if (!isset($wp_query)) {
        return;
    }

    // Reset to current post in main query
    if (isset($wp_query->post)) {
        $post = $wp_query->post;
        setup_postdata($post);
    }
}

/**
 * Set up global post data for the current post in the loop.
 *
 * This function populates all the global variables that template tags depend on.
 * It's called automatically by the_post() but can also be called manually when
 * working with post objects outside the main loop.
 *
 * Globals Populated:
 * - $id: Current post ID
 * - $authordata: Author user object
 * - $pages: Array of content pages (split by <!--nextpage-->)
 * - $page: Current page number (for multi-page posts)
 * - $numpages: Total number of pages
 * - $multipage: Boolean indicating if post has multiple pages
 * - $more: Boolean controlling "Read more" link display
 * - $currentday: Day of post (for date-based grouping)
 * - $currentmonth: Month of post (for date-based grouping)
 *
 * Multi-page Content:
 * WordPress allows content to be split across multiple pages using <!--nextpage-->
 * tags. This function detects those tags and splits content into $pages array.
 *
 * WordPress Behavior:
 * - Normalizes nextpage tags (removes surrounding whitespace)
 * - Sets $multipage = true if more than one page
 * - Preserves existing $page and $more values if already set
 * - Loads author data via get_userdata()
 *
 * @global int    $id            Current post ID.
 * @global object $authordata    Author user object.
 * @global string $currentday    Day of current post.
 * @global string $currentmonth  Month of current post.
 * @global int    $page          Current page number of a post.
 * @global array  $pages         Array of post content pages.
 * @global int    $numpages      Total number of pages in the post.
 * @global int    $multipage     Boolean indicating multi-page post.
 * @global int    $more          Boolean controlling "more" link display.
 * @global WP_Post $post         Global post object.
 *
 * @param WP_Post|object|int $post WP_Post object or post ID.
 * @return bool True on success, false on failure.
 */
function setup_postdata($post) {
    global $id, $authordata, $currentday, $currentmonth, $page, $pages;
    global $multipage, $more, $numpages;

    // Handle post ID passed instead of object
    if (is_numeric($post)) {
        $post = get_post($post);
    }

    // Validate we have a post object
    if (!is_object($post) || !isset($post->ID)) {
        return false;
    }

    // Ensure global $post is set (WordPress standard)
    $GLOBALS['post'] = $post;
    // Also set $wp_post for WP2BD compatibility
    $GLOBALS['wp_post'] = $post;

    // Set up post ID global
    $id = (int) $post->ID;

    // Set up author data
    // Note: get_userdata() would need to be implemented to load Backdrop user
    // For now, we'll create a basic implementation that works with the structure
    if (function_exists('get_userdata') && isset($post->post_author)) {
        $authordata = get_userdata($post->post_author);
    } else {
        // Fallback: create minimal author data structure
        $authordata = new stdClass();
        $authordata->ID = isset($post->post_author) ? $post->post_author : 0;
    }

    // Set up date-based grouping variables
    if (isset($post->post_date)) {
        $currentday = mysql2date('d.m.y', $post->post_date, false);
        $currentmonth = mysql2date('m', $post->post_date, false);
    } else {
        $currentday = '';
        $currentmonth = '';
    }

    // Set up post content variables
    $content = isset($post->post_content) ? $post->post_content : '';

    // Check for multi-page content (<!--nextpage--> tag)
    // WordPress allows content to be split across multiple pages
    if (strpos($content, '<!--nextpage-->') !== false) {
        // Normalize nextpage tags by removing surrounding whitespace variations
        $content = str_replace("\n<!--nextpage-->\n", '<!--nextpage-->', $content);
        $content = str_replace("\n<!--nextpage-->", '<!--nextpage-->', $content);
        $content = str_replace("<!--nextpage-->\n", '<!--nextpage-->', $content);

        // Split content into pages
        $pages = explode('<!--nextpage-->', $content);
    } else {
        // Single page content
        $pages = array($content);
    }

    // Count pages and set multipage flag
    $numpages = count($pages);
    $multipage = ($numpages > 1);

    // Initialize page number if not already set
    // Preserve existing value (may be set by query string ?page=2)
    if (!isset($page)) {
        $page = 1;
    }

    // Ensure page is within valid range
    if ($page < 1) {
        $page = 1;
    } elseif ($page > $numpages) {
        $page = $numpages;
    }

    // Initialize "more" flag if not already set
    // Controls whether to show full content or excerpt/more link
    // $more = 0: Show excerpt with "Read more" link
    // $more = 1: Show full content
    if (!isset($more)) {
        $more = 1;
    }

    return true;
}

/**
 * Helper function to convert MySQL datetime to formatted date.
 *
 * WordPress uses this for date formatting. We provide a basic implementation
 * that works with the MySQL datetime format used by WordPress/Backdrop.
 *
 * @param string $format Date format string.
 * @param string $date   MySQL datetime string (Y-m-d H:i:s).
 * @param bool   $translate Whether to translate (unused in basic implementation).
 * @return string Formatted date string.
 */
function mysql2date($format, $date, $translate = true) {
    if (empty($date)) {
        return '';
    }

    // Convert MySQL datetime to Unix timestamp
    $timestamp = strtotime($date);

    if ($timestamp === false) {
        return '';
    }

    // Format and return
    return date($format, $timestamp);
}

// get_post() is implemented in WP_Query.php - no need for duplicate here

/**
 * Retrieve user info by user ID.
 *
 * Stub implementation for author data retrieval.
 * Full WP2BD implementation would load Backdrop user account.
 *
 * @param int $user_id User ID.
 * @return object|false User object on success, false on failure.
 */
function get_userdata($user_id) {
    // In full implementation, load Backdrop user
    // For now, return basic structure
    if (function_exists('user_load')) {
        $user = user_load($user_id);
        if ($user) {
            // Convert Backdrop user to WordPress-style object
            $userdata = new stdClass();
            $userdata->ID = $user->uid;
            $userdata->user_login = $user->name;
            $userdata->user_email = isset($user->mail) ? $user->mail : '';
            $userdata->display_name = isset($user->name) ? $user->name : '';
            return $userdata;
        }
    }

    return false;
}

// do_action() is implemented in hooks.php - no need for duplicate here
