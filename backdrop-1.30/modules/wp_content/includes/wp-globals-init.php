<?php
/**
 * WordPress Globals Initialization for WP4BD V2.
 *
 * Populates critical WordPress globals so themes can run on Backdrop data.
 *
 * @package WP4BD
 * @subpackage V2-Architecture
 * @since WP4BD-V2-031
 */

/**
 * Initialize WordPress globals from Backdrop state.
 *
 * This is a minimal bootstrap that:
 * - Prepares query globals ($wp_query, $wp_the_query, $post, $posts)
 * - Initializes rewrite and request objects
 * - Seeds hook system globals
 * - Maps Backdrop content types and vocabularies to WordPress structures
 *
 * @param array $options
 *   Optional configuration overrides:
 *   - 'limit' => int Number of posts to load (default 5)
 *
 * @return array
 *   Summary of initialized globals for debugging.
 */
function wp4bd_init_wordpress_globals(array $options = array()) {
  $summary = array(
    'posts_loaded' => 0,
    'wp_query_initialized' => FALSE,
    'wp_the_query_initialized' => FALSE,
    'rewrite_initialized' => FALSE,
    'wp_initialized' => FALSE,
    'post_types_initialized' => FALSE,
    'taxonomies_initialized' => FALSE,
    'hook_globals_initialized' => FALSE,
    'pagenow' => NULL,
  );

  // ---------------------------------------------------------------------------
  // Load required WordPress classes (from wpbrain).
  // ---------------------------------------------------------------------------
  $wp_root = BACKDROP_ROOT . '/themes/wp/wpbrain/';

  // Ensure core constants exist for WordPress includes.
  if (!defined('ABSPATH')) {
    define('ABSPATH', $wp_root);
  }
  if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
  }
  if (!defined('OBJECT')) {
    define('OBJECT', 'OBJECT');
  }
  if (!defined('ARRAY_A')) {
    define('ARRAY_A', 'ARRAY_A');
  }
  if (!defined('ARRAY_N')) {
    define('ARRAY_N', 'ARRAY_N');
  }

  // Minimal stubs for core helpers that WordPress normally loads earlier.
  if (!function_exists('wp_cache_get')) {
    function wp_cache_get($key, $group = '', $force = false, &$found = null) {
      $found = false;
      return false;
    }
  }
  if (!function_exists('wp_cache_set')) {
    function wp_cache_set($key, $data, $group = '', $expire = 0) {
      return true;
    }
  }
  if (!function_exists('wp_cache_add')) {
    function wp_cache_add($key, $data, $group = '', $expire = 0) {
      return true;
    }
  }
  if (!function_exists('wp_cache_delete')) {
    function wp_cache_delete($key, $group = '') {
      return true;
    }
  }
  // Minimal $wpdb stub for option loading during init.
  if (!isset($GLOBALS['wpdb'])) {
    class WP4BD_WPDB_Stub {
      // Table name placeholders.
      public $options = 'wp_options';
      // Simple option store.
      public $option_store = array(
        'posts_per_page' => 10,
        'posts_per_rss' => 10,
        'comments_per_page' => 10,
        'page_on_front' => 0,
        'show_on_front' => 'posts',
      );
      public function suppress_errors($set = TRUE) {}
      public function get_results($query = '') { return array(); }
      public function get_row($query = '', $output = OBJECT, $y = 0) {
        // Very simple parser to extract option_name.
        if (preg_match("/option_name\\s*=\\s*'([^']+)'/i", $query, $m)) {
          $name = $m[1];
          $val = isset($this->option_store[$name]) ? $this->option_store[$name] : '';
          $row = (object) array('option_value' => $val);
          return $row;
        }
        return NULL;
      }
      public function prepare($query, $value = NULL) {
        // Simple replacement for %s placeholder.
        if (strpos($query, '%s') !== FALSE) {
          $safe = is_string($value) ? $value : '';
          return str_replace('%s', $safe, $query);
        }
        return $query;
      }
      public function flush() {}
    }
    $GLOBALS['wpdb'] = new WP4BD_WPDB_Stub();
  }
  if (!function_exists('wp_installing')) {
    function wp_installing() { return FALSE; }
  }

  // Minimal stub for WP_Tax_Query to satisfy WP_Query parsing.
  if (!class_exists('WP_Tax_Query')) {
    class WP_Tax_Query {
      public $queries = array();
      public function __construct($q = array()) {}
      public function get_sql($primary_table, $primary_id_column) {
        return array('join' => '', 'where' => '');
      }
    }
  }

  // Minimal stub for WP_Meta_Query to satisfy WP_Query meta handling.
  if (!class_exists('WP_Meta_Query')) {
    class WP_Meta_Query {
      public $queries = array();
      public function __construct($q = array()) {
        $this->queries = $q;
      }
      public function get_sql($type, $primary_table, $primary_id_column) {
        return array('join' => '', 'where' => '');
      }
      public function parse_query_vars($q) {
        $this->queries = $q;
      }
    }
  }

  if (!function_exists('get_post_type_object')) {
    function get_post_type_object($post_type) {
      return (object) array(
        'hierarchical' => FALSE,
        'name' => $post_type,
      );
    }
  }

  // WP_Post (custom) lives under the theme classes directory.
  $wp_post_class = BACKDROP_ROOT . '/themes/wp/classes/WP_Post.php';
  if (file_exists($wp_post_class)) {
    require_once $wp_post_class;
  }

  // Core WordPress classes.
  $maybe_classes = array(
    'WP_Query'   => $wp_root . 'wp-includes/class-wp-query.php',
    'WP_Rewrite' => $wp_root . 'wp-includes/class-wp-rewrite.php',
    'WP'         => $wp_root . 'wp-includes/class-wp.php',
  );
  foreach ($maybe_classes as $class_name => $class_file) {
    if (class_exists($class_name)) {
      continue;
    }
    if (file_exists($class_file)) {
      require_once $class_file;
    }
  }

  // Core WordPress functions needed by WP_Query (wp_parse_args, etc.).
  $maybe_functions = array(
    $wp_root . 'wp-includes/plugin.php',    // add_filter/apply_filters + WP_Hook
    $wp_root . 'wp-includes/functions.php', // wp_parse_args, etc.
  );
  foreach ($maybe_functions as $fn_file) {
    if (file_exists($fn_file)) {
      require_once $fn_file;
    }
  }

  // Minimal stubs for taxonomy/post-type helpers expected by WP_Query.
  if (!function_exists('get_taxonomies')) {
    function get_taxonomies($args = array(), $output = 'names') {
      return array();
    }
  }
  if (!function_exists('get_post_types')) {
    function get_post_types($args = array(), $output = 'names') {
      return array();
    }
  }
  if (!function_exists('wp_using_ext_object_cache')) {
    function wp_using_ext_object_cache() { return FALSE; }
  }
  if (!function_exists('is_admin')) {
    function is_admin() { return FALSE; }
  }
  if (!function_exists('is_network_admin')) {
    function is_network_admin() { return FALSE; }
  }
  if (!function_exists('is_user_admin')) {
    function is_user_admin() { return FALSE; }
  }
  if (!function_exists('sanitize_key')) {
    function sanitize_key($key) {
      $key = strtolower($key);
      $key = preg_replace('/[^a-z0-9_]/', '', $key);
      return $key;
    }
  }

  // ---------------------------------------------------------------------------
  // Helper: convert Backdrop node to WP_Post (with fallback).
  // ---------------------------------------------------------------------------
  $convert_node = function ($node) {
    if (class_exists('WP_Post') && method_exists('WP_Post', 'from_node')) {
      return WP_Post::from_node($node);
    }
    if (!is_object($node) || !isset($node->nid)) {
      return NULL;
    }
    $post = new stdClass();
    $post->ID = (int) $node->nid;
    $post->post_author = isset($node->uid) ? (int) $node->uid : 0;
    $post->post_title = isset($node->title) ? $node->title : '';
    $post->post_type = isset($node->type) ? $node->type : 'post';
    $post->post_status = (isset($node->status) && $node->status == 1) ? 'publish' : 'draft';
    $post->post_date = isset($node->created) ? date('Y-m-d H:i:s', $node->created) : '';
    $post->post_modified = isset($node->changed) ? date('Y-m-d H:i:s', $node->changed) : '';
    $post->post_content = '';
    $post->post_excerpt = '';
    return $post;
  };

  // ---------------------------------------------------------------------------
  // Build posts array from Backdrop nodes.
  // ---------------------------------------------------------------------------
  $limit = isset($options['limit']) ? (int) $options['limit'] : 5;
  $nodes = array();

  if (class_exists('EntityFieldQuery')) {
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', 'node')
      ->propertyCondition('status', 1)
      ->range(0, $limit);
    try {
      $result = $query->execute();
      if (isset($result['node'])) {
        $nids = array_keys($result['node']);
        if (function_exists('node_load_multiple')) {
          $nodes = node_load_multiple($nids);
        }
      }
    }
    catch (Exception $e) {
      // Fail silently; leave $nodes empty.
    }
  }

  // Convert nodes to WP_Post objects.
  $posts = array();
  if (!empty($nodes)) {
    foreach ($nodes as $node) {
      $wp_post = $convert_node($node);
      if ($wp_post) {
        $posts[] = $wp_post;
      }
    }
  }
  $summary['posts_loaded'] = count($posts);

  // ---------------------------------------------------------------------------
  // Initialize WP_Query globals ($wp_query, $wp_the_query) and post globals.
  // ---------------------------------------------------------------------------
  global $wp_query, $wp_the_query, $post, $posts_global;

  // WordPress uses $posts; avoid collision with local variable.
  $posts_global = $posts;
  $post = !empty($posts_global) ? $posts_global[0] : NULL;

  // Minimal WP_Query stand-in (avoid full WordPress query stack during init).
  $wp_query = (object) array(
    'posts' => $posts_global,
    'post_count' => count($posts_global),
    'current_post' => -1,
    'queried_object' => $post,
    'queried_object_id' => $post ? $post->ID : 0,
    'is_home' => TRUE,
    'is_singular' => ($post !== NULL),
    'is_single' => ($post !== NULL),
    'is_page' => FALSE,
  );
  $wp_the_query = $wp_query;
  $summary['wp_query_initialized'] = TRUE;
  $summary['wp_the_query_initialized'] = TRUE;

  // ---------------------------------------------------------------------------
  // Rewrite and request objects ($wp_rewrite, $wp).
  // ---------------------------------------------------------------------------
  global $wp_rewrite, $wp;
  if (class_exists('WP_Rewrite')) {
    $wp_rewrite = new WP_Rewrite();
    $summary['rewrite_initialized'] = TRUE;
  }
  else {
    $wp_rewrite = new stdClass();
  }

  if (class_exists('WP')) {
    $wp = new WP();
    $summary['wp_initialized'] = TRUE;
  }
  else {
    $wp = new stdClass();
  }

  // ---------------------------------------------------------------------------
  // Post types and taxonomies from Backdrop.
  // ---------------------------------------------------------------------------
  global $wp_post_types, $wp_taxonomies;
  $wp_post_types = array();
  $wp_taxonomies = array();

  if (function_exists('node_type_get_types')) {
    $types = node_type_get_types();
    foreach ($types as $type) {
      // Minimal structure; WordPress themes mostly check existence and labels.
      $wp_post_types[$type->type] = (object) array(
        'name' => $type->type,
        'label' => isset($type->name) ? $type->name : $type->type,
      );
    }
    $summary['post_types_initialized'] = TRUE;
  }

  if (function_exists('taxonomy_vocabulary_load_multiple')) {
    $vocs = taxonomy_vocabulary_load_multiple();
    foreach ($vocs as $voc) {
      $name = isset($voc->machine_name) ? $voc->machine_name : (isset($voc->name) ? $voc->name : 'taxonomy');
      $wp_taxonomies[$name] = (object) array(
        'name' => $name,
        'label' => isset($voc->name) ? $voc->name : $name,
      );
    }
    $summary['taxonomies_initialized'] = TRUE;
  }

  // ---------------------------------------------------------------------------
  // Theme object (placeholder)
  // ---------------------------------------------------------------------------
  global $wp_theme;
  $wp_theme = (object) array(
    'name' => $GLOBALS['theme_key'] ?? 'wp',
    'stylesheet' => 'wp',
    'template' => 'wp',
  );

  // ---------------------------------------------------------------------------
  // Hook system globals.
  // ---------------------------------------------------------------------------
  global $wp_filter, $wp_actions, $wp_current_filter;
  if (!isset($wp_filter) || !is_array($wp_filter)) {
    $wp_filter = array();
  }
  if (!isset($wp_actions) || !is_array($wp_actions)) {
    $wp_actions = array();
  }
  if (!isset($wp_current_filter) || !is_array($wp_current_filter)) {
    $wp_current_filter = array();
  }
  $summary['hook_globals_initialized'] = TRUE;

  // ---------------------------------------------------------------------------
  // Environment globals.
  // ---------------------------------------------------------------------------
  global $pagenow, $blog_id;
  $blog_id = 1; // Single-site

  // Derive $pagenow from Backdrop path.
  $path = function_exists('current_path') ? current_path() : '';
  if (strpos($path, 'node/') === 0) {
    $pagenow = 'single.php';
  }
  else {
    $pagenow = 'index.php';
  }
  $summary['pagenow'] = $pagenow;

  return $summary;
}

