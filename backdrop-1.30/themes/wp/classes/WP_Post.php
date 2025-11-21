<?php
/**
 * WP_Post Class
 *
 * Mimics WordPress's WP_Post object structure.
 * Converts Backdrop nodes to WordPress post objects for theme compatibility.
 *
 * @package WP2BD
 * @subpackage Classes
 */

class WP_Post {
    /**
     * Post ID
     * @var int
     */
    public $ID;

    /**
     * Author ID
     * @var int
     */
    public $post_author;

    /**
     * Published date (local time)
     * @var string Format: Y-m-d H:i:s
     */
    public $post_date;

    /**
     * Published date (GMT)
     * @var string Format: Y-m-d H:i:s
     */
    public $post_date_gmt;

    /**
     * Post content (main body)
     * @var string
     */
    public $post_content;

    /**
     * Post title
     * @var string
     */
    public $post_title;

    /**
     * Post excerpt/summary
     * @var string
     */
    public $post_excerpt;

    /**
     * Post status (publish, draft, etc.)
     * @var string
     */
    public $post_status;

    /**
     * Post slug/name
     * @var string
     */
    public $post_name;

    /**
     * Last modified date (local time)
     * @var string Format: Y-m-d H:i:s
     */
    public $post_modified;

    /**
     * Last modified date (GMT)
     * @var string Format: Y-m-d H:i:s
     */
    public $post_modified_gmt;

    /**
     * Parent post ID (0 for none)
     * @var int
     */
    public $post_parent;

    /**
     * Post type (maps to Backdrop content type)
     * @var string
     */
    public $post_type;

    /**
     * Comment count
     * @var int
     */
    public $comment_count;

    /**
     * Filter context
     * @var string
     */
    public $filter;

    /**
     * GUID - Globally Unique Identifier
     * @var string
     */
    public $guid;

    /**
     * Menu order
     * @var int
     */
    public $menu_order;

    /**
     * Post mime type
     * @var string
     */
    public $post_mime_type;

    /**
     * Comment status (open, closed)
     * @var string
     */
    public $comment_status;

    /**
     * Ping status
     * @var string
     */
    public $ping_status;

    /**
     * Constructor
     *
     * @param object|array $data Optional post data to initialize
     */
    public function __construct($data = null) {
        if ($data) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    /**
     * Convert a Backdrop node to a WP_Post object
     *
     * @param object $node Loaded Backdrop node object
     * @return WP_Post
     */
    public static function from_node($node) {
        if (!is_object($node) || !isset($node->nid)) {
            return null;
        }

        // Ensure node has required properties (especially 'type' bundle property)
        // If node doesn't have 'type', try to load it fully
        if (!isset($node->type) && function_exists('node_load')) {
            $full_node = node_load($node->nid);
            if ($full_node && isset($full_node->type)) {
                $node = $full_node;
            } else {
                // Fallback: set a default type
                $node->type = 'page';
            }
        } elseif (!isset($node->type)) {
            // If we can't load it, set a default
            $node->type = 'page';
        }

        $post = new WP_Post();

        // Basic properties
        $post->ID = (int) $node->nid;
        $post->post_author = isset($node->uid) ? (int) $node->uid : 0;
        $post->post_title = isset($node->title) ? $node->title : '';
        $post->post_type = isset($node->type) ? $node->type : 'post';
        $post->post_parent = 0; // Backdrop nodes don't have parent relationships by default
        $post->menu_order = 0;
        $post->filter = 'raw';

        // Dates
        if (isset($node->created)) {
            $post->post_date = date('Y-m-d H:i:s', $node->created);
            $post->post_date_gmt = gmdate('Y-m-d H:i:s', $node->created);
        } else {
            $post->post_date = '';
            $post->post_date_gmt = '';
        }

        if (isset($node->changed)) {
            $post->post_modified = date('Y-m-d H:i:s', $node->changed);
            $post->post_modified_gmt = gmdate('Y-m-d H:i:s', $node->changed);
        } else {
            $post->post_modified = '';
            $post->post_modified_gmt = '';
        }

        // Status
        $post->post_status = (isset($node->status) && $node->status == 1) ? 'publish' : 'draft';

        // Extract body content
        // Backdrop stores body in $node->body[LANGUAGE_NONE][0]
        $post->post_content = '';
        $post->post_excerpt = '';

        if (isset($node->body) && is_array($node->body)) {
            // Check for LANGUAGE_NONE constant or use 'und'
            $lang_key = defined('LANGUAGE_NONE') ? LANGUAGE_NONE : 'und';

            if (isset($node->body[$lang_key][0]['value'])) {
                $post->post_content = $node->body[$lang_key][0]['value'];
            }

            // Extract summary/excerpt if available
            if (isset($node->body[$lang_key][0]['summary'])) {
                $post->post_excerpt = $node->body[$lang_key][0]['summary'];
            }
        }

        // Post name/slug - try to get from path alias
        $post->post_name = '';
        if (isset($node->path) && is_array($node->path) && isset($node->path['alias'])) {
            $post->post_name = $node->path['alias'];
        } elseif (function_exists('backdrop_get_path_alias')) {
            $alias = backdrop_get_path_alias('node/' . $node->nid);
            if ($alias != 'node/' . $node->nid) {
                $post->post_name = $alias;
            }
        }

        // If no alias, create slug from title
        if (empty($post->post_name) && !empty($post->post_title)) {
            $post->post_name = self::sanitize_title($post->post_title);
        }

        // Comment count
        $post->comment_count = isset($node->comment_count) ? (int) $node->comment_count : 0;

        // Comment status
        if (isset($node->comment)) {
            // Backdrop comment values: 0 = hidden, 1 = closed, 2 = open
            $post->comment_status = ($node->comment == 2) ? 'open' : 'closed';
        } else {
            $post->comment_status = 'closed';
        }

        // Ping status - Backdrop doesn't have pings, default to closed
        $post->ping_status = 'closed';

        // GUID - construct from node URL
        if (function_exists('url')) {
            $post->guid = url('node/' . $node->nid, array('absolute' => TRUE));
        } else {
            // Fallback for testing or when url() is not available
            $base_url = isset($GLOBALS['base_url']) ? $GLOBALS['base_url'] : 'http://example.com';
            $post->guid = $base_url . '/node/' . $node->nid;
        }

        // Post mime type - only relevant for attachments
        $post->post_mime_type = '';

        return $post;
    }

    /**
     * Simple title sanitization for slug creation
     *
     * @param string $title
     * @return string
     */
    private static function sanitize_title($title) {
        // Convert to lowercase
        $slug = strtolower($title);

        // Replace spaces and special chars with hyphens
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        // Remove leading/trailing hyphens
        $slug = trim($slug, '-');

        return $slug;
    }

    /**
     * Magic getter for dynamic properties
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        // Check if property exists
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        // Return null for undefined properties (WordPress behavior)
        return null;
    }

    /**
     * Magic isset for dynamic properties
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name) {
        return property_exists($this, $name) && isset($this->$name);
    }

    /**
     * Convert post object to array
     *
     * @return array
     */
    public function to_array() {
        $vars = get_object_vars($this);
        return $vars;
    }
}
