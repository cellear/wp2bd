<?php
/**
 * WordPress Database Drop-in for WP4BD V2
 *
 * This file replaces WordPress's default wpdb class to intercept all
 * database operations and redirect them to Backdrop's database layer.
 *
 * WordPress automatically loads this file if it exists in wp-content/,
 * preventing the default wp-includes/wp-db.php from being used.
 *
 * @package WP4BD
 * @subpackage V2-Architecture
 * @since WP4BD-V2-020
 */

// Define WordPress database constants (normally in wp-includes/wp-db.php)
if (!defined('EZSQL_VERSION')) {
  define('EZSQL_VERSION', 'WP1.25');
}
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

/**
 * WP4BD Database Bridge Class
 *
 * Replaces WordPress's wpdb class to intercept database calls and redirect
 * them to Backdrop's database API instead of making MySQL connections.
 *
 * This is Phase 1 (WP4BD-V2-020): Stub out all methods to prevent database access.
 * Phase 2 (WP4BD-V2-021): Add query mapping to Backdrop.
 * Phase 3 (WP4BD-V2-022): Add result transformation from Backdrop to WordPress objects.
 *
 * Note: This file is a WordPress "drop-in" - WordPress will load this INSTEAD of
 * wp-includes/wp-db.php, so we must implement the wpdb class from scratch.
 */
class wpdb {

  /**
   * Flag to track if we're using WP4BD bridge
   * @var bool
   */
  public $wp4bd_bridge_active = true;

  /**
   * Query log for debugging
   * @var array
   */
  public $wp4bd_query_log = array();

  /**
   * WordPress-expected properties
   */
  public $show_errors = false;
  public $suppress_errors = true;
  public $last_error = '';
  public $num_queries = 0;
  public $num_rows = 0;
  public $rows_affected = 0;
  public $insert_id = 0;
  public $last_query = '';
  public $ready = false;
  
  // Database connection properties
  public $dbuser;
  public $dbpassword;
  public $dbname;
  public $dbhost;
  public $dbh; // Database handle (will be null since we don't connect)
  
  // Table prefix
  public $prefix;
  public $base_prefix;
  
  // WordPress standard tables
  public $posts;
  public $users;
  public $usermeta;
  public $postmeta;
  public $comments;
  public $commentmeta;
  public $terms;
  public $term_taxonomy;
  public $term_relationships;
  public $termmeta;
  public $options;
  public $links;

  /**
   * Constructor - Prevents WordPress from connecting to database
   *
   * @param string $dbuser     MySQL database user (ignored)
   * @param string $dbpassword MySQL database password (ignored)
   * @param string $dbname     MySQL database name (ignored)
   * @param string $dbhost     MySQL database host (ignored)
   */
  public function __construct( $dbuser, $dbpassword, $dbname, $dbhost ) {
    // Set up database connection properties (but never actually connect)
    $this->dbuser = $dbuser;
    $this->dbpassword = $dbpassword;
    $this->dbname = $dbname;
    $this->dbhost = $dbhost;
    $this->dbh = null; // No database handle - we're not connecting
    
    // Tell WordPress we're "ready" even though we won't query
    $this->ready = true;
    
    // Set up table prefix from global (WordPress expects this)
    $this->prefix = isset($GLOBALS['table_prefix']) ? $GLOBALS['table_prefix'] : 'wp_';
    $this->base_prefix = $this->prefix;
    
    // Set standard WordPress table names
    $this->posts = $this->prefix . 'posts';
    $this->users = $this->prefix . 'users';
    $this->usermeta = $this->prefix . 'usermeta';
    $this->postmeta = $this->prefix . 'postmeta';
    $this->comments = $this->prefix . 'comments';
    $this->commentmeta = $this->prefix . 'commentmeta';
    $this->terms = $this->prefix . 'terms';
    $this->term_taxonomy = $this->prefix . 'term_taxonomy';
    $this->term_relationships = $this->prefix . 'term_relationships';
    $this->termmeta = $this->prefix . 'termmeta';
    $this->options = $this->prefix . 'options';
    $this->links = $this->prefix . 'links';
    
    // Log that bridge is active
    if (function_exists('watchdog')) {
      watchdog('wp4bd', 'WP4BD Database Bridge initialized (db.php drop-in active)', array(), WATCHDOG_INFO);
    }
  }

  /**
   * Intercept and log query attempts
   *
   * @param string $query SQL query
   * @return false Always returns false to prevent actual database access
   */
  public function query( $query ) {
    $this->num_queries++;
    
    // Log the query attempt for debugging
    $this->_log_query($query, 'query()');
    
    // WP4BD-V2-020: Return false (no actual query execution yet)
    // WP4BD-V2-021 will add Backdrop query mapping here
    $this->last_error = 'WP4BD: Database queries intercepted (not executed in V2-020)';
    
    return false;
  }

  /**
   * Get results from database
   *
   * @param string $query SQL query
   * @param string $output Type of output (OBJECT, ARRAY_A, ARRAY_N)
   * @return array Empty array (WP4BD-V2-021 will populate with Backdrop data)
   */
  public function get_results( $query = null, $output = OBJECT ) {
    if ($query) {
      $this->_log_query($query, 'get_results()');
    }
    
    // WP4BD-V2-020: Return empty array
    // WP4BD-V2-021: Map to Backdrop queries
    // WP4BD-V2-022: Transform Backdrop results to WordPress objects
    $this->num_rows = 0;
    
    return array();
  }

  /**
   * Get single variable from database
   *
   * @param string $query SQL query
   * @param int $x Column offset
   * @param int $y Row offset
   * @return null Always returns null (WP4BD-V2-021 will return Backdrop data)
   */
  public function get_var( $query = null, $x = 0, $y = 0 ) {
    if ($query) {
      $this->_log_query($query, 'get_var()');
    }
    
    // WP4BD-V2-020: Return null
    // WP4BD-V2-021: Return mapped Backdrop value
    return null;
  }

  /**
   * Get single row from database
   *
   * @param string $query SQL query
   * @param string $output Type of output
   * @param int $y Row offset
   * @return null Always returns null (WP4BD-V2-021 will return Backdrop data)
   */
  public function get_row( $query = null, $output = OBJECT, $y = 0 ) {
    if ($query) {
      $this->_log_query($query, 'get_row()');
    }
    
    // WP4BD-V2-020: Return null
    // WP4BD-V2-021: Return mapped Backdrop row
    // WP4BD-V2-022: Transform to WordPress object format
    $this->num_rows = 0;
    
    return null;
  }

  /**
   * Get single column from database
   *
   * @param string $query SQL query
   * @param int $x Column offset
   * @return array Empty array (WP4BD-V2-021 will return Backdrop data)
   */
  public function get_col( $query = null, $x = 0 ) {
    if ($query) {
      $this->_log_query($query, 'get_col()');
    }
    
    // WP4BD-V2-020: Return empty array
    // WP4BD-V2-021: Return mapped Backdrop column
    $this->num_rows = 0;
    
    return array();
  }

  /**
   * Insert row into table
   *
   * @param string $table Table name
   * @param array $data Data to insert
   * @param array $format Data format
   * @return false Always returns false (read-only in V2)
   */
  public function insert( $table, $data, $format = null ) {
    $this->_log_query("INSERT INTO $table: " . print_r($data, true), 'insert()');
    
    // WP4BD V2 is read-only - WordPress cannot write data
    $this->last_error = 'WP4BD: WordPress cannot write data (read-only in V2)';
    
    return false;
  }

  /**
   * Update rows in table
   *
   * @param string $table Table name
   * @param array $data Data to update
   * @param array $where Where clause
   * @param array $format Data format
   * @param array $where_format Where format
   * @return false Always returns false (read-only in V2)
   */
  public function update( $table, $data, $where, $format = null, $where_format = null ) {
    $this->_log_query("UPDATE $table: " . print_r($data, true), 'update()');
    
    // WP4BD V2 is read-only - WordPress cannot write data
    $this->last_error = 'WP4BD: WordPress cannot write data (read-only in V2)';
    
    return false;
  }

  /**
   * Delete rows from table
   *
   * @param string $table Table name
   * @param array $where Where clause
   * @param array $where_format Where format
   * @return false Always returns false (read-only in V2)
   */
  public function delete( $table, $where, $where_format = null ) {
    $this->_log_query("DELETE FROM $table: " . print_r($where, true), 'delete()');
    
    // WP4BD V2 is read-only - WordPress cannot write data
    $this->last_error = 'WP4BD: WordPress cannot write data (read-only in V2)';
    
    return false;
  }

  /**
   * Prepare SQL query with placeholders
   *
   * @param string $query Query with placeholders (%s, %d, %f)
   * @param mixed ...$args Values to replace placeholders
   * @return string Prepared query (but never executed)
   */
  public function prepare( $query, ...$args ) {
    // Simple implementation - WordPress's prepare handles %s, %d, %f placeholders
    if (empty($args)) {
      return $query;
    }
    
    // Flatten array if first argument is an array
    if (is_array($args[0])) {
      $args = $args[0];
    }
    
    // Replace placeholders
    $query = str_replace("'%s'", '%s', $query); // Strip quotes from string placeholders
    $query = str_replace('"%s"', '%s', $query);
    
    foreach ($args as $arg) {
      // Find next placeholder
      if (strpos($query, '%s') !== false) {
        $query = $this->_str_replace_once('%s', "'" . $this->_real_escape($arg) . "'", $query);
      }
      elseif (strpos($query, '%d') !== false) {
        $query = $this->_str_replace_once('%d', intval($arg), $query);
      }
      elseif (strpos($query, '%f') !== false) {
        $query = $this->_str_replace_once('%f', floatval($arg), $query);
      }
    }
    
    return $query;
  }

  /**
   * Replace only the first occurrence of a string
   *
   * @param string $search Search string
   * @param string $replace Replacement string
   * @param string $subject Subject string
   * @return string Modified string
   */
  private function _str_replace_once( $search, $replace, $subject ) {
    $pos = strpos($subject, $search);
    if ($pos !== false) {
      return substr_replace($subject, $replace, $pos, strlen($search));
    }
    return $subject;
  }

  /**
   * Escape string for SQL (even though we don't execute SQL)
   *
   * @param string $data String to escape
   * @return string Escaped string
   */
  public function _real_escape( $data ) {
    // Even though we're not executing SQL, WordPress expects this method
    // Use Backdrop's database escaping
    if (function_exists('db_escape_string')) {
      return db_escape_string($data);
    }
    
    // Fallback to PHP's escaping
    return addslashes($data);
  }

  /**
   * Log intercepted queries for debugging
   *
   * @param string $query SQL query
   * @param string $method Method that intercepted the query
   */
  private function _log_query( $query, $method ) {
    // Store in internal log
    $this->wp4bd_query_log[] = array(
      'query' => $query,
      'method' => $method,
      'timestamp' => microtime(true),
    );
    
    // Log to Backdrop watchdog if available (but only at higher debug levels)
    if (function_exists('watchdog') && isset($_GET['wp4bd_debug']) && $_GET['wp4bd_debug'] >= 4) {
      watchdog('wp4bd_query', 'Intercepted @method: @query', array(
        '@method' => $method,
        '@query' => substr($query, 0, 200), // Truncate long queries
      ), WATCHDOG_DEBUG);
    }
  }

  /**
   * Get query log for debugging
   *
   * @return array Array of intercepted queries
   */
  public function get_query_log() {
    return $this->wp4bd_query_log;
  }
}

// WordPress expects a global $wpdb variable
// This will be initialized by WordPress with our custom class above
// DO NOT initialize it here - let WordPress do it via wp-settings.php
