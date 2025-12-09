<?php
/**
 * WP_Query Class
 *
 * Mimics WordPress's WP_Query for querying Backdrop nodes.
 * Handles The Loop state machine and maps WordPress query arguments
 * to Backdrop's EntityFieldQuery system.
 *
 * @package WP2BD
 * @subpackage Classes
 */

// Ensure WP_Post class is available
require_once dirname(__FILE__) . '/WP_Post.php';

class WP_Query {
    /**
     * Array of post objects
     * @var array
     */
    public $posts = array();

    /**
     * Total number of posts in current query
     * @var int
     */
    public $post_count = 0;

    /**
     * Current post index (-1 = before loop)
     * @var int
     */
    public $current_post = -1;

    /**
     * Current post object (reference to posts array element)
     * @var WP_Post|null
     */
    public $post;

    /**
     * The main queried object (node, term, user, etc.)
     * @var object|null
     */
    public $queried_object = null;

    /**
     * ID of the queried object
     * @var int|null
     */
    public $queried_object_id = null;

    /**
     * Query arguments passed to constructor
     * @var array
     */
    public $query_vars = array();

    /**
     * Total number of posts matching query (before pagination)
     * @var int
     */
    public $found_posts = 0;

    /**
     * Maximum number of pages
     * @var int
     */
    public $max_num_pages = 0;

    /**
     * Whether this is a single post query
     * @var bool
     */
    public $is_single = false;

    /**
     * Whether this is a page query
     * @var bool
     */
    public $is_page = false;

    /**
     * Whether this is an archive
     * @var bool
     */
    public $is_archive = false;

    /**
     * Whether this is the home page
     * @var bool
     */
    public $is_home = false;

    /**
     * Whether this is the front page
     * @var bool
     */
    public $is_front_page = false;

    /**
     * Whether results were found
     * @var bool
     */
    public $is_404 = false;

    /**
     * Whether this is a singular post (single post or page)
     * @var bool
     */
    public $is_singular = false;

    /**
     * Error message if query failed
     * @var string
     */
    public $error = '';

    /**
     * Debug counters for loop calls
     * @var int
     */
    public $debug_have_posts_calls = 0;
    public $debug_the_post_calls = 0;

    /**
     * Constructor - Execute query based on arguments
     *
     * @param array|string $args Query arguments
     */
    public function __construct($args = array()) {
        // Parse arguments
        if (is_string($args)) {
            parse_str($args, $args);
        }

        // Set defaults
        $defaults = array(
            'post_type' => 'post',
            'posts_per_page' => 10,
            'paged' => 1,
            'offset' => 0,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish',
            'p' => 0,                    // Single post ID
            'page_id' => 0,              // Single page ID
            'name' => '',                // Post slug
            'category_name' => '',       // Category slug
            'tag' => '',                 // Tag slug
            'author' => 0,               // Author ID
            'author_name' => '',         // Author slug
            's' => '',                   // Search query
            'meta_key' => '',            // Meta key
            'meta_value' => '',          // Meta value
            'meta_compare' => '=',       // Meta comparison operator
            'suppress_filters' => false,  // Allow filters to modify query
        );

        $this->query_vars = array_merge($defaults, $args);

        // Execute the query
        $this->query();
    }

    /**
     * Execute the query
     *
     * @return void
     */
    private function query() {
        try {
            // Handle single post/page queries
            if ($this->query_vars['p'] > 0) {
                $this->query_single_post($this->query_vars['p']);
                $this->is_single = true;
                return;
            }

            if ($this->query_vars['page_id'] > 0) {
                $this->query_single_post($this->query_vars['page_id']);
                $this->is_page = true;
                return;
            }

            if (!empty($this->query_vars['name'])) {
                $this->query_by_slug($this->query_vars['name']);
                $this->is_single = true;
                return;
            }

            // Build standard query using EntityFieldQuery
            $this->query_posts();

        } catch (Exception $e) {
            $this->error = $e->getMessage();
            $this->posts = array();
            $this->post_count = 0;
            $this->is_404 = true;
        }
    }

    /**
     * Query a single post by ID
     *
     * @param int $post_id
     * @return void
     */
    private function query_single_post($post_id) {
        if (!function_exists('node_load')) {
            $this->error = 'Backdrop node_load() function not available';
            return;
        }

        $node = node_load($post_id);

        if ($node && $node->status == 1) {
            $post = WP_Post::from_node($node);
            if ($post) {
                $this->posts = array($post);
                $this->post_count = 1;
                $this->found_posts = 1;
                $this->max_num_pages = 1;
                $this->queried_object = $node;
                $this->queried_object_id = $post_id;
            }
        } else {
            $this->is_404 = true;
        }
    }

    /**
     * Query a single post by slug
     *
     * @param string $slug
     * @return void
     */
    private function query_by_slug($slug) {
        if (!function_exists('db_select')) {
            $this->error = 'Backdrop database functions not available';
            return;
        }

        // Try to find node by path alias
        if (function_exists('backdrop_lookup_path')) {
            $path = backdrop_lookup_path('source', $slug);
            if ($path && preg_match('/^node\/(\d+)$/', $path, $matches)) {
                $this->query_single_post($matches[1]);
                return;
            }
        }

        // If not found by alias, search by title (less ideal but fallback)
        $query = db_select('node', 'n')
            ->fields('n', array('nid'))
            ->condition('n.title', $slug)
            ->condition('n.status', 1)
            ->range(0, 1);

        $result = $query->execute()->fetchField();

        if ($result) {
            $this->query_single_post($result);
        } else {
            $this->is_404 = true;
        }
    }

    /**
     * Query posts using EntityFieldQuery
     *
     * @return void
     */
    private function query_posts() {
        if (!class_exists('EntityFieldQuery')) {
            $this->error = 'Backdrop EntityFieldQuery not available';
            return;
        }

        $query = new EntityFieldQuery();
        $query->entityCondition('entity_type', 'node');

        // Post type (content type in Backdrop)
        if (!empty($this->query_vars['post_type']) && $this->query_vars['post_type'] != 'any') {
            if (is_array($this->query_vars['post_type'])) {
                $query->entityCondition('bundle', $this->query_vars['post_type'], 'IN');
            } else {
                $query->entityCondition('bundle', $this->query_vars['post_type']);
            }
        }

        // Post status
        if ($this->query_vars['post_status'] == 'publish') {
            $query->propertyCondition('status', 1);
        } elseif ($this->query_vars['post_status'] == 'draft') {
            $query->propertyCondition('status', 0);
        } elseif ($this->query_vars['post_status'] == 'any') {
            // No status filter
        }

        // Author
        if (!empty($this->query_vars['author'])) {
            $query->propertyCondition('uid', $this->query_vars['author']);
        } elseif (!empty($this->query_vars['author_name'])) {
            // Look up user ID by name
            if (function_exists('user_load_by_name')) {
                $user = user_load_by_name($this->query_vars['author_name']);
                if ($user) {
                    $query->propertyCondition('uid', $user->uid);
                }
            }
        }

        // Search
        if (!empty($this->query_vars['s'])) {
            $search_term = '%' . db_like($this->query_vars['s']) . '%';
            $query->propertyCondition('title', $search_term, 'LIKE');
        }

        // Meta query (field query in Backdrop)
        if (!empty($this->query_vars['meta_key']) && !empty($this->query_vars['meta_value'])) {
            // Note: This is simplified - real implementation would need field type detection
            $field_name = $this->query_vars['meta_key'];
            $operator = $this->query_vars['meta_compare'];
            $value = $this->query_vars['meta_value'];

            // Try to add field condition
            try {
                $query->fieldCondition($field_name, 'value', $value, $operator);
            } catch (Exception $e) {
                // Field might not exist, log but continue
            }
        }

        // Ordering
        $order_direction = strtoupper($this->query_vars['order']) == 'ASC' ? 'ASC' : 'DESC';

        switch ($this->query_vars['orderby']) {
            case 'date':
                $query->propertyOrderBy('created', $order_direction);
                break;
            case 'modified':
                $query->propertyOrderBy('changed', $order_direction);
                break;
            case 'title':
                $query->propertyOrderBy('title', $order_direction);
                break;
            case 'author':
                $query->propertyOrderBy('uid', $order_direction);
                break;
            case 'ID':
                $query->propertyOrderBy('nid', $order_direction);
                break;
            default:
                // Default to created date
                $query->propertyOrderBy('created', $order_direction);
        }

        // Pagination
        $posts_per_page = (int) $this->query_vars['posts_per_page'];
        $paged = max(1, (int) $this->query_vars['paged']);
        $offset = (int) $this->query_vars['offset'];

        if ($posts_per_page > 0) {
            // Calculate offset from page number
            if ($paged > 1) {
                $offset = ($paged - 1) * $posts_per_page;
            }

            $query->range($offset, $posts_per_page);
        } elseif ($posts_per_page == -1) {
            // No limit - get all posts (be careful with this!)
            // Don't set range
        }

        // Execute query
        try {
            $result = $query->execute();

            if (isset($result['node'])) {
                $nids = array_keys($result['node']);

                // Load nodes
                if (function_exists('node_load_multiple')) {
                    $nodes = node_load_multiple($nids);

                    // Convert nodes to WP_Post objects
                    foreach ($nodes as $node) {
                        $post = WP_Post::from_node($node);
                        if ($post) {
                            $this->posts[] = $post;
                        }
                    }

                    $this->post_count = count($this->posts);
                    $this->found_posts = $this->post_count; // Note: In real WP, this would be total before pagination

                    // Calculate max pages
                    if ($posts_per_page > 0) {
                        $this->max_num_pages = ceil($this->found_posts / $posts_per_page);
                    } else {
                        $this->max_num_pages = 1;
                    }

                    // Set archive flag if we're listing posts
                    if ($this->post_count > 1) {
                        $this->is_archive = true;
                    }
                }
            } else {
                // No results
                $this->post_count = 0;
                $this->found_posts = 0;
                $this->max_num_pages = 0;
            }

        } catch (Exception $e) {
            $this->error = 'Query execution failed: ' . $e->getMessage();
            $this->posts = array();
            $this->post_count = 0;
        }
    }

    /**
     * Check if more posts are available in the loop
     *
     * @return bool
     */
    public function have_posts() {
        $this->debug_have_posts_calls++;
        $has_posts = ($this->current_post + 1 < $this->post_count);
        
        if ($has_posts) {
            if (function_exists('wp4bd_debug_get_level') && wp4bd_debug_get_level() >= 4) {
                wp4bd_debug_log('Loop Debug', 'WP_Query::have_posts', [
                    'current_post' => $this->current_post,
                    'post_count' => $this->post_count,
                    'post_ids' => array_map(function($p){ return $p->ID ?? null; }, (array) $this->posts),
                    'call_count' => $this->debug_have_posts_calls,
                ]);
            }
            return true;
        } elseif ($this->current_post + 1 == $this->post_count && $this->post_count > 0) {
            // End of loop - rewind for potential second loop
            // This matches WordPress behavior
            do_action('loop_end', $this);
        }

        return false;
    }

    /**
     * Set up the next post and iterate current post index
     *
     * @return void
     */
    public function the_post() {
        global $post;

        // Increment counter
        $this->current_post++;

        // Set current post
        if (isset($this->posts[$this->current_post])) {
            $this->post = $this->posts[$this->current_post];

            // Set global post (using direct assignment instead of reference)
            $post = $this->post;
            $GLOBALS['post'] = $this->post;

            // Setup post data
            setup_postdata($this->post);

            // Fire action hook
            do_action('the_post', $this->post, $this);

            // Fire loop_start on first post
            if ($this->current_post == 0) {
                do_action('loop_start', $this);
            }

            if (function_exists('wp4bd_debug_get_level') && wp4bd_debug_get_level() >= 4) {
                wp4bd_debug_log('Loop Debug', 'WP_Query::the_post', [
                    'current_post' => $this->current_post,
                    'post_id' => $this->post->ID ?? null,
                    'post_title' => $this->post->post_title ?? null,
                    'call_count' => $this->debug_the_post_calls + 1,
                ]);
            }
            $this->debug_the_post_calls++;
        }
    }

    /**
     * Reset post data to beginning of loop
     *
     * @return void
     */
    public function reset_postdata() {
        if ($this->current_post > -1 && $this->post_count > 0) {
            $this->current_post = -1;
            global $post;

            // Reset to first post if available
            if (isset($this->posts[0])) {
                $this->post = $this->posts[0];
                $post = $this->post;
                $GLOBALS['post'] = $this->post;
                setup_postdata($this->post);
            }
        }
    }

    /**
     * Rewind posts - reset to beginning
     *
     * @return void
     */
    public function rewind_posts() {
        $this->current_post = -1;
        if ($this->post_count > 0) {
            $this->post = null;
        }
    }

    /**
     * Get the queried object
     *
     * @return object|null
     */
    public function get_queried_object() {
        if ($this->queried_object) {
            return $this->queried_object;
        }

        // If we have posts, use the first one
        if ($this->post_count > 0 && isset($this->posts[0])) {
            return $this->posts[0];
        }

        return null;
    }

    /**
     * Determines whether the query is for an existing single page.
     *
     * WordPress Behavior:
     * - Returns false if $this->is_page flag is not set
     * - If $page parameter is empty, returns true if is_page flag is set
     * - If $page parameter is provided, checks if the current page matches by:
     *   - Page ID (integer)
     *   - Page title (string)
     *   - Page slug/name (string)
     *   - Page path (string with slashes)
     *
     * @param int|string|array $page Optional. Page ID, title, slug, or path. Default empty.
     * @return bool Whether the query is for an existing single page.
     */
    public function is_page($page = '') {
        // First check if this is a page query at all
        if (!$this->is_page) {
            return false;
        }

        // If no specific page requested, return true
        if (empty($page)) {
            return true;
        }

        // Get the queried page object
        $page_obj = $this->get_queried_object();

        if (!$page_obj) {
            return false;
        }

        // Convert $page to array for uniform processing
        $page = array_map('strval', (array) $page);

        // Check by ID
        if (in_array((string) $page_obj->ID, $page, true)) {
            return true;
        }

        // Check by title
        if (isset($page_obj->post_title) && in_array($page_obj->post_title, $page, true)) {
            return true;
        }

        // Check by slug/name
        if (isset($page_obj->post_name) && in_array($page_obj->post_name, $page, true)) {
            return true;
        }

        // Check by path (if contains slashes)
        foreach ($page as $pagepath) {
            if (strpos($pagepath, '/') !== false) {
                // Try to resolve path using Backdrop's path system
                if (function_exists('backdrop_lookup_path')) {
                    $internal_path = backdrop_lookup_path('source', $pagepath);
                    if ($internal_path && preg_match('/^node\/(\d+)$/', $internal_path, $matches)) {
                        if ((int) $matches[1] === $page_obj->ID) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get a query variable
     *
     * @param string $var Variable name
     * @param mixed $default Default value
     * @return mixed
     */
    public function get($var, $default = '') {
        if (isset($this->query_vars[$var])) {
            return $this->query_vars[$var];
        }

        return $default;
    }

    /**
     * Set a query variable
     *
     * @param string $var Variable name
     * @param mixed $value Value
     * @return void
     */
    public function set($var, $value) {
        $this->query_vars[$var] = $value;
    }
}

/**
 * Stub for db_like if not in Backdrop context
 *
 * @param string $string
 * @return string
 */
if (!function_exists('db_like')) {
    function db_like($string) {
        return addslashes($string);
    }
}

if (!function_exists('get_post')) {
    /**
     * Get post object
     *
     * @param int|WP_Post|null $post Post ID or object
     * @param string $output Optional. OBJECT, ARRAY_A, or ARRAY_N. Default OBJECT.
     * @return WP_Post|array|null
     */
    function get_post($post = null, $output = OBJECT) {
        if ($post instanceof WP_Post) {
            // Handle output format if needed
            if ($output === OBJECT) {
                return $post;
            }
            // For now, only support OBJECT output
            return $post;
        }

        if (is_numeric($post) && function_exists('node_load')) {
            $node = node_load($post);
            if ($node) {
                $wp_post = WP_Post::from_node($node);
                // Handle output format if needed
                if ($output === OBJECT) {
                    return $wp_post;
                }
                // For now, only support OBJECT output
                return $wp_post;
            }
        }

        // Return global post if no argument
        if ($post === null) {
            global $post;
            return $post;
        }

        return null;
    }
}
