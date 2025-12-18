<?php
/**
 * WP4BD Mock wpdb - prevents WordPress from querying the database.
 *
 * This stub satisfies WordPress's db.php override mechanism and ensures
 * no SQL is executed. All methods return empty results and log attempts.
 *
 * @package WP4BD
 */

// Define WordPress database query constants (from wp-includes/wp-db.php)
if (!defined('OBJECT')) {
  define('OBJECT', 'OBJECT');
}
if (!defined('object')) {
  define('object', 'OBJECT'); // Back compat
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

if (!class_exists('wpdb')) {
  class wpdb {
    public $ready = false;
    public $prefix = 'wp_';
    public $blogid = 1;
    public $base_prefix = 'wp_';
    public $charset = 'utf8';
    public $collate = '';
    public $insert_id = 0;
    public $last_error = '';
    public $num_queries = 0;
    public $last_query;
    public $last_result = array();
    public $tables = array(
      'posts',
      'postmeta',
      'terms',
      'term_taxonomy',
      'term_relationships',
      'users',
      'usermeta',
      'options',
      'comments',
      'commentmeta',
    );
    public $global_tables = array();
    public $ms_global_tables = array();

    public function __construct() {
      // Define table name properties (e.g., $this->posts => "wp_posts").
      foreach ($this->tables as $table) {
        $this->$table = $this->prefix . $table;
      }
    }

    private function log($method, $query = '') {
      // Silently track queries without logging to avoid log spam
      // Uncomment the watchdog line below if you need to debug database interception
      // if (function_exists('watchdog')) {
      //   watchdog('wpdb', '@method attempted: @query', [
      //     '@method' => $method,
      //     '@query' => $query
      //   ], WATCHDOG_DEBUG);
      // }
      $this->last_query = $query;
      $this->num_queries++;
    }

    public function query($query) {
      $this->log('query', $query);
      return false;
    }

    public function get_var($query = null, $x = 0, $y = 0) {
      $this->log('get_var', $query);
      return null;
    }

    public function get_row($query = null, $output = OBJECT, $y = 0) {
      $this->log('get_row', $query);
      return ($output === ARRAY_A) ? array() : (($output === ARRAY_N) ? array() : null);
    }

    public function get_col($query = null, $x = 0) {
      $this->log('get_col', $query);
      return array();
    }

    public function get_results($query = null, $output = OBJECT) {
      $this->log('get_results', $query);
      if ($output === ARRAY_A || $output === ARRAY_N) {
        return array();
      }
      return array();
    }

    public function escape($data) {
      return $data;
    }

    public function prepare($query, ...$args) {
      $this->log('prepare', $query);
      return $query;
    }

    public function insert($table, $data, $format = null) {
      $this->log('insert', "table={$table}");
      return false;
    }

    public function update($table, $data, $where, $format = null, $where_format = null) {
      $this->log('update', "table={$table}");
      return false;
    }

    public function delete($table, $where, $where_format = null) {
      $this->log('delete', "table={$table}");
      return false;
    }

    public function replace($table, $data, $format = null) {
      $this->log('replace', "table={$table}");
      return false;
    }

    public function flush() {
      $this->last_result = array();
      $this->last_query = '';
    }

    // Transaction-related stubs
    public function check_connection() { return true; }
    public function close() { return true; }

    // Helpers used in core to build table names/prefixes.
    public function get_blog_prefix($blog_id = null) {
      return $this->base_prefix;
    }
  }
}

global $wpdb;
$wpdb = new wpdb();

