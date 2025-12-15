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
   * @return array Results from Backdrop (transformed to WordPress format)
   */
  public function get_results( $query = null, $output = OBJECT ) {
    if ($query) {
      $this->_log_query($query, 'get_results()');
      $this->last_query = $query;
    }
    
    // WP4BD-V2-021: Map WordPress SQL to Backdrop queries
    $results = $this->_map_query_to_backdrop($query);
    
    // Set num_rows for WordPress compatibility
    $this->num_rows = is_array($results) ? count($results) : 0;
    
    // WP4BD-V2-022: Transform results based on output format
    return $this->_transform_results($results, $output, $query);
  }

  /**
   * Get single variable from database
   *
   * @param string $query SQL query
   * @param int $x Column offset
   * @param int $y Row offset
   * @return mixed Single value from query result
   */
  public function get_var( $query = null, $x = 0, $y = 0 ) {
    if ($query) {
      $this->_log_query($query, 'get_var()');
      $this->last_query = $query;
    }
    
    // WP4BD-V2-021: Map query to Backdrop
    $results = $this->_map_query_to_backdrop($query);
    
    if (empty($results)) {
      return null;
    }
    
    // Get the first row
    $row = $results[$y] ?? $results[0] ?? null;
    if (!$row) {
      return null;
    }
    
    // Convert to array if it's an object
    $row_array = is_object($row) ? (array)$row : $row;
    $values = array_values($row_array);
    
    // Return the specified column
    return $values[$x] ?? null;
  }

  /**
   * Get single row from database
   *
   * @param string $query SQL query
   * @param string $output Type of output
   * @param int $y Row offset
   * @return mixed Single row from query result (transformed to WordPress format)
   */
  public function get_row( $query = null, $output = OBJECT, $y = 0 ) {
    if ($query) {
      $this->_log_query($query, 'get_row()');
      $this->last_query = $query;
    }
    
    // WP4BD-V2-021: Map query to Backdrop
    $results = $this->_map_query_to_backdrop($query);
    
    if (empty($results)) {
      $this->num_rows = 0;
      return null;
    }
    
    $this->num_rows = count($results);
    
    // Get the specified row
    $row = $results[$y] ?? $results[0] ?? null;
    
    // WP4BD-V2-022: Transform based on output format
    $transformed = $this->_transform_results(array($row), $output, $query);
    return !empty($transformed) ? $transformed[0] : null;
  }

  /**
   * Get single column from database
   *
   * @param string $query SQL query
   * @param int $x Column offset
   * @return array Column values from query result
   */
  public function get_col( $query = null, $x = 0 ) {
    if ($query) {
      $this->_log_query($query, 'get_col()');
      $this->last_query = $query;
    }
    
    // WP4BD-V2-021: Map query to Backdrop
    $results = $this->_map_query_to_backdrop($query);
    
    if (empty($results)) {
      $this->num_rows = 0;
      return array();
    }
    
    $this->num_rows = count($results);
    
    // Extract the specified column from all rows
    $column = array();
    foreach ($results as $row) {
      $row_array = is_object($row) ? (array)$row : $row;
      $values = array_values($row_array);
      if (isset($values[$x])) {
        $column[] = $values[$x];
      }
    }
    
    return $column;
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
   * Map WordPress SQL queries to Backdrop database API
   *
   * WP4BD-V2-021: Core query mapping logic
   *
   * @param string $query WordPress SQL query
   * @return array Results from Backdrop (raw - transformation happens in V2-022)
   */
  private function _map_query_to_backdrop( $query ) {
    if (empty($query)) {
      return array();
    }
    
    // Normalize query for parsing
    $query = trim($query);
    $query_lower = strtolower($query);
    
    // Detect query type
    if (strpos($query_lower, 'select') === 0) {
      return $this->_handle_select_query($query, $query_lower);
    }
    
    // For non-SELECT queries, return empty (read-only mode)
    return array();
  }

  /**
   * Handle SELECT queries - map to Backdrop
   *
   * @param string $query Original query
   * @param string $query_lower Lowercase query for parsing
   * @return array Results from Backdrop
   */
  private function _handle_select_query( $query, $query_lower ) {
    // Detect which table is being queried
    if (strpos($query_lower, 'from ' . $this->posts) !== false || 
        strpos($query_lower, 'from `' . $this->posts . '`') !== false) {
      return $this->_query_posts($query, $query_lower);
    }
    
    if (strpos($query_lower, 'from ' . $this->users) !== false ||
        strpos($query_lower, 'from `' . $this->users . '`') !== false) {
      return $this->_query_users($query, $query_lower);
    }
    
    if (strpos($query_lower, 'from ' . $this->options) !== false ||
        strpos($query_lower, 'from `' . $this->options . '`') !== false) {
      return $this->_query_options($query, $query_lower);
    }
    
    // Default: return empty for unhandled tables
    return array();
  }

  /**
   * Query wp_posts table - map to Backdrop nodes
   *
   * @param string $query Original query
   * @param string $query_lower Lowercase query
   * @return array Array of Backdrop nodes (stdClass objects)
   */
  private function _query_posts( $query, $query_lower ) {
    // Parse common WHERE clauses
    $conditions = array();
    
    // Extract ID if specified
    if (preg_match('/where.*\bid\s*=\s*(\d+)/i', $query, $matches)) {
      $conditions['id'] = $matches[1];
    }
    
    // Extract post_type if specified
    if (preg_match('/post_type\s*=\s*[\'"]([^\'"]+)[\'"]/i', $query, $matches)) {
      $conditions['type'] = $matches[1];
    }
    
    // Extract post_status if specified
    if (preg_match('/post_status\s*=\s*[\'"]([^\'"]+)[\'"]/i', $query, $matches)) {
      $conditions['status'] = ($matches[1] === 'publish') ? 1 : 0;
    }
    
    // Extract LIMIT if specified
    $limit = null;
    if (preg_match('/limit\s+(\d+)(?:\s+offset\s+(\d+))?/i', $query, $matches)) {
      $limit = array(
        'limit' => (int)$matches[1],
        'offset' => isset($matches[2]) ? (int)$matches[2] : 0,
      );
    }
    
    // Query Backdrop nodes
    return $this->_fetch_backdrop_nodes($conditions, $limit);
  }

  /**
   * Fetch nodes from Backdrop
   *
   * @param array $conditions Query conditions
   * @param array|null $limit Limit and offset
   * @return array Array of node objects
   */
  private function _fetch_backdrop_nodes( $conditions, $limit = null ) {
    // Check if Backdrop functions are available
    if (!function_exists('node_load_multiple')) {
      return array();
    }
    
    // If querying by specific ID, use node_load
    if (isset($conditions['id'])) {
      $node = node_load($conditions['id']);
      return $node ? array($node) : array();
    }
    
    // Build entity query
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', 'node');
    
    // Add type condition if specified
    if (isset($conditions['type'])) {
      $query->entityCondition('bundle', $conditions['type']);
    }
    
    // Add status condition if specified
    if (isset($conditions['status'])) {
      $query->propertyCondition('status', $conditions['status']);
    }
    
    // Add limit if specified
    if ($limit) {
      $query->range($limit['offset'], $limit['limit']);
    }
    
    // Execute query
    try {
      $result = $query->execute();
      
      if (isset($result['node'])) {
        $nids = array_keys($result['node']);
        $nodes = node_load_multiple($nids);
        return array_values($nodes);
      }
    } catch (Exception $e) {
      if (function_exists('watchdog')) {
        watchdog('wp4bd', 'Error fetching nodes: @error', array('@error' => $e->getMessage()), WATCHDOG_ERROR);
      }
    }
    
    return array();
  }

  /**
   * Query wp_users table - map to Backdrop users
   *
   * @param string $query Original query
   * @param string $query_lower Lowercase query
   * @return array Array of Backdrop user objects
   */
  private function _query_users( $query, $query_lower ) {
    // Simple user query - just return empty for now
    // Full implementation in future iterations
    if (function_exists('user_load_multiple')) {
      // Load all users (limited)
      $users = user_load_multiple(array(), array(), 10);
      return array_values($users);
    }
    
    return array();
  }

  /**
   * Query wp_options table - map to Backdrop config/variables
   *
   * @param string $query Original query
   * @param string $query_lower Lowercase query
   * @return array Array of option objects
   */
  private function _query_options( $query, $query_lower ) {
    // Extract option_name if specified
    if (preg_match('/option_name\s*=\s*[\'"]([^\'"]+)[\'"]/i', $query, $matches)) {
      $option_name = $matches[1];
      
      // Try to get from Backdrop config/variables
      if (function_exists('config_get')) {
        $value = config_get('system.core', $option_name);
        if ($value !== null) {
          return array((object)array(
            'option_id' => 1,
            'option_name' => $option_name,
            'option_value' => $value,
            'autoload' => 'yes',
          ));
        }
      }
    }
    
    return array();
  }

  /**
   * Transform Backdrop results to WordPress object format
   *
   * WP4BD-V2-022: Core transformation logic
   *
   * @param array $results Raw Backdrop objects
   * @param string $output Output format (OBJECT, ARRAY_A, ARRAY_N)
   * @param string $query Original query (for context)
   * @return array Transformed results
   */
  private function _transform_results( $results, $output, $query = '' ) {
    if (empty($results)) {
      return array();
    }
    
    $transformed = array();
    $query_lower = strtolower($query);
    
    foreach ($results as $result) {
      if (!is_object($result) && !is_array($result)) {
        continue;
      }
      
      // Detect what type of object this is based on query context
      if (strpos($query_lower, 'from ' . $this->posts) !== false ||
          strpos($query_lower, 'from `' . $this->posts . '`') !== false) {
        // This is a node/post
        $transformed_item = $this->_backdrop_node_to_wp_post($result);
      }
      elseif (strpos($query_lower, 'from ' . $this->users) !== false ||
              strpos($query_lower, 'from `' . $this->users . '`') !== false) {
        // This is a user
        $transformed_item = $this->_backdrop_user_to_wp_user($result);
      }
      elseif (strpos($query_lower, 'from ' . $this->options) !== false ||
              strpos($query_lower, 'from `' . $this->options . '`') !== false) {
        // This is an option
        $transformed_item = $this->_backdrop_config_to_wp_option($result);
      }
      else {
        // Unknown type - return as-is
        $transformed_item = $result;
      }
      
      // Convert to requested output format
      if ($transformed_item) {
        $transformed[] = $this->_format_output($transformed_item, $output);
      }
    }
    
    return $transformed;
  }

  /**
   * Convert Backdrop node to WordPress WP_Post object
   *
   * WP4BD-V2-022: Reuse existing WP_Post::from_node() method
   *
   * @param object $node Backdrop node object
   * @return WP_Post|null WordPress post object
   */
  private function _backdrop_node_to_wp_post( $node ) {
    // Check if WP_Post class exists and has from_node method
    if (class_exists('WP_Post') && method_exists('WP_Post', 'from_node')) {
      return WP_Post::from_node($node);
    }
    
    // Fallback: create basic WP_Post-like object
    if (!is_object($node) || !isset($node->nid)) {
      return null;
    }
    
    $post = new stdClass();
    $post->ID = (int)$node->nid;
    $post->post_author = isset($node->uid) ? (int)$node->uid : 0;
    $post->post_title = isset($node->title) ? $node->title : '';
    $post->post_type = isset($node->type) ? $node->type : 'post';
    $post->post_status = (isset($node->status) && $node->status == 1) ? 'publish' : 'draft';
    $post->post_date = isset($node->created) ? date('Y-m-d H:i:s', $node->created) : '';
    $post->post_modified = isset($node->changed) ? date('Y-m-d H:i:s', $node->changed) : '';
    $post->post_content = '';
    $post->post_excerpt = '';
    
    return $post;
  }

  /**
   * Convert Backdrop user to WordPress user object
   *
   * WP4BD-V2-022
   *
   * @param object $account Backdrop user object
   * @return object|null WordPress user object
   */
  private function _backdrop_user_to_wp_user( $account ) {
    if (!is_object($account) || !isset($account->uid)) {
      return null;
    }
    
    $user = new stdClass();
    $user->ID = (int)$account->uid;
    $user->user_login = isset($account->name) ? $account->name : '';
    $user->user_email = isset($account->mail) ? $account->mail : '';
    $user->display_name = isset($account->name) ? $account->name : '';
    $user->user_registered = isset($account->created) ? date('Y-m-d H:i:s', $account->created) : '';
    $user->user_status = isset($account->status) ? (int)$account->status : 0;
    // Simple sanitize_title helper if WordPress function not available
    $nicename = isset($account->name) ? $account->name : '';
    if (!function_exists('sanitize_title')) {
      $nicename = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $nicename));
      $nicename = trim($nicename, '-');
    } else {
      $nicename = sanitize_title($nicename);
    }
    $user->user_nicename = $nicename;
    
    return $user;
  }

  /**
   * Convert Backdrop config/variable to WordPress option object
   *
   * WP4BD-V2-022
   *
   * @param object|array $config Backdrop config or option data
   * @return object|null WordPress option object
   */
  private function _backdrop_config_to_wp_option( $config ) {
    // If it's already in WordPress format (from our query_options method), return as-is
    if (is_object($config) && isset($config->option_name)) {
      return $config;
    }
    
    // Otherwise, create a basic option object
    $option = new stdClass();
    $option->option_id = 1;
    $option->option_name = '';
    $option->option_value = '';
    $option->autoload = 'yes';
    
    if (is_object($config)) {
      // Try to extract name and value from config object
      $option->option_name = isset($config->name) ? $config->name : '';
      $option->option_value = isset($config->value) ? $config->value : '';
    }
    elseif (is_array($config)) {
      $option->option_name = isset($config['name']) ? $config['name'] : '';
      $option->option_value = isset($config['value']) ? $config['value'] : '';
    }
    
    return $option;
  }

  /**
   * Format output according to WordPress output type
   *
   * @param object $item Transformed item
   * @param string $output Output format (OBJECT, ARRAY_A, ARRAY_N)
   * @return mixed Formatted output
   */
  private function _format_output( $item, $output ) {
    if ($output === OBJECT || $output === 'OBJECT') {
      return $item;
    }
    
    if ($output === ARRAY_A || $output === 'ARRAY_A') {
      return (array)$item;
    }
    
    if ($output === ARRAY_N || $output === 'ARRAY_N') {
      return array_values((array)$item);
    }
    
    // Default to object
    return $item;
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
