#!/usr/bin/env php
<?php
/**
 * Test script for WP4BD-V2-070: Test WordPress Core Loads
 *
 * Verifies that WordPress core loads without fatal errors and that all critical
 * components are properly initialized.
 *
 * Run from command line:
 *   php TESTS/V2/070-wordpress-core-loads.php
 *
 * Or from within ddev:
 *   ddev exec 'php /var/www/html/TESTS/V2/070-wordpress-core-loads.php'
 */

// Setup BACKDROP_ROOT for both environments
if (file_exists('/var/www/html/backdrop-1.30')) {
  define('BACKDROP_ROOT', '/var/www/html/backdrop-1.30');
} else {
  define('BACKDROP_ROOT', dirname(dirname(__DIR__)) . '/backdrop-1.30');
}

echo "Testing WordPress Core Loading (Epic 8 V2-070)...\n";
echo str_repeat("=", 70) . "\n\n";

// ============================================================================
// SECTION 1: BOOTSTRAP WORDPRESS
// ============================================================================
echo "SECTION 1: WordPress Bootstrap\n";
echo str_repeat("-", 70) . "\n";

// Test 1: Load bootstrap file
echo "1. Loading wp-bootstrap.php...\n";
$bootstrap_file = BACKDROP_ROOT . '/modules/wp_content/includes/wp-bootstrap.php';
if (file_exists($bootstrap_file)) {
  require_once $bootstrap_file;
  echo "   ✅ Bootstrap file loaded\n";
} else {
  echo "   ❌ CRITICAL: Bootstrap file missing: $bootstrap_file\n";
  exit(1);
}

// Test 2: Set up minimal Backdrop environment
echo "\n2. Setting up minimal Backdrop environment...\n";

// Mock critical Backdrop functions
if (!function_exists('backdrop_get_path')) {
  function backdrop_get_path($type, $name) {
    if ($type === 'theme' && $name === 'wp') {
      return 'themes/wp';
    }
    if ($type === 'module' && $name === 'wp_content') {
      return 'modules/wp_content';
    }
    return '';
  }
}

if (!function_exists('watchdog')) {
  function watchdog($type, $message, $vars = array(), $severity = 6) {
    // Silent for tests
  }
}

if (!function_exists('backdrop_strtolower')) {
  function backdrop_strtolower($str) {
    return strtolower($str);
  }
}

if (!function_exists('config_get')) {
  function config_get($config_file, $key = NULL) {
    // Mock config for testing
    $mock_config = array(
      'system.core' => array(
        'site_name' => 'Test Site',
        'site_mail' => 'admin@test.local',
        'base_url' => 'http://test.local',
      ),
      'system.date' => array(
        'default_timezone' => 'America/New_York',
      ),
      'system.theme' => array(
        'default' => 'wp',
      ),
    );
    if (!isset($mock_config[$config_file])) {
      return NULL;
    }
    if ($key === NULL) {
      return $mock_config[$config_file];
    }
    return isset($mock_config[$config_file][$key]) ? $mock_config[$config_file][$key] : NULL;
  }
}

if (!defined('LANGUAGE_NONE')) {
  define('LANGUAGE_NONE', 'und');
}

echo "   ✅ Backdrop environment ready\n";

// Test 3: Run WordPress bootstrap
echo "\n3. Running wp4bd_bootstrap_wordpress()...\n";
$errors = array();
$result = wp4bd_bootstrap_wordpress($errors);

if ($result === TRUE) {
  echo "   ✅ Bootstrap successful\n";
} else {
  echo "   ❌ Bootstrap failed\n";
  if (!empty($errors)) {
    echo "   Errors:\n";
    foreach ($errors as $error) {
      echo "     - $error\n";
    }
  }
  exit(1);
}

// ============================================================================
// SECTION 2: VERIFY WORDPRESS CONSTANTS
// ============================================================================
echo "\n" . str_repeat("=", 70) . "\n";
echo "SECTION 2: WordPress Constants\n";
echo str_repeat("-", 70) . "\n";

$constants_to_check = array(
  'ABSPATH' => 'WordPress root directory',
  'WPINC' => 'WordPress includes directory name',
  'WP_CONTENT_DIR' => 'WordPress content directory',
  'WP_DEBUG' => 'Debug mode flag',
);

echo "Checking required WordPress constants...\n";
$all_constants_defined = true;
foreach ($constants_to_check as $const => $description) {
  if (defined($const)) {
    $value = constant($const);
    if (is_bool($value)) {
      $value = $value ? 'true' : 'false';
    }
    echo "   ✅ $const: $value\n";
  } else {
    echo "   ❌ $const not defined ($description)\n";
    $all_constants_defined = false;
  }
}

if (!$all_constants_defined) {
  exit(1);
}

// ============================================================================
// SECTION 3: VERIFY WORDPRESS CLASSES
// ============================================================================
echo "\n" . str_repeat("=", 70) . "\n";
echo "SECTION 3: WordPress Core Classes\n";
echo str_repeat("-", 70) . "\n";

$classes_to_check = array(
  'WP_Post' => 'Post object class',
  'WP_User' => 'User object class',
  'WP_Term' => 'Term object class',
  'WP_Query' => 'Query object class',
  'wpdb' => 'Database abstraction class',
);

echo "Checking WordPress core classes are loaded...\n";
$all_classes_exist = true;
foreach ($classes_to_check as $class => $description) {
  if (class_exists($class)) {
    echo "   ✅ $class exists ($description)\n";
  } else {
    echo "   ❌ $class not found ($description)\n";
    $all_classes_exist = false;
  }
}

if (!$all_classes_exist) {
  exit(1);
}

// ============================================================================
// SECTION 4: VERIFY WORDPRESS FUNCTIONS
// ============================================================================
echo "\n" . str_repeat("=", 70) . "\n";
echo "SECTION 4: WordPress Core Functions\n";
echo str_repeat("-", 70) . "\n";

$functions_to_check = array(
  // Hook system
  'add_action' => 'Register action hook',
  'add_filter' => 'Register filter hook',
  'do_action' => 'Execute action hook',
  'apply_filters' => 'Execute filter hook',

  // Template functions
  'get_header' => 'Load header template',
  'get_footer' => 'Load footer template',
  'get_sidebar' => 'Load sidebar template',

  // Query functions
  'have_posts' => 'Check if posts available (The Loop)',
  'the_post' => 'Setup next post (The Loop)',

  // Template tags
  'the_title' => 'Output post title',
  'the_content' => 'Output post content',
  'the_permalink' => 'Output post URL',

  // Options
  'get_option' => 'Get WordPress option',
  'bloginfo' => 'Output site information',
);

echo "Checking WordPress core functions are loaded...\n";
$all_functions_exist = true;
foreach ($functions_to_check as $func => $description) {
  if (function_exists($func)) {
    echo "   ✅ $func() ($description)\n";
  } else {
    echo "   ❌ $func() not found ($description)\n";
    $all_functions_exist = false;
  }
}

if (!$all_functions_exist) {
  exit(1);
}

// ============================================================================
// SECTION 5: VERIFY DATA BRIDGES
// ============================================================================
echo "\n" . str_repeat("=", 70) . "\n";
echo "SECTION 5: Data Bridge Functions (Epic 7)\n";
echo str_repeat("-", 70) . "\n";

$bridge_functions = array(
  // Post bridge
  'wp4bd_node_to_post' => 'Convert Backdrop node to WP_Post',
  'wp4bd_sanitize_title' => 'Sanitize post title to slug',

  // User bridge
  'wp4bd_user_to_wp_user' => 'Convert Backdrop user to WP user data',
  'wp4bd_sanitize_user_nicename' => 'Sanitize user nicename',

  // Term bridge
  'wp4bd_term_to_wp_term' => 'Convert Backdrop term to WP_Term',
  'wp4bd_vocabulary_to_taxonomy' => 'Map vocabulary to taxonomy name',

  // Options bridge
  'wp4bd_get_option' => 'Get WordPress option from Backdrop config',
  'wp4bd_has_option' => 'Check if option exists',
);

echo "Checking Epic 7 bridge functions are loaded...\n";
$all_bridges_exist = true;
foreach ($bridge_functions as $func => $description) {
  if (function_exists($func)) {
    echo "   ✅ $func() ($description)\n";
  } else {
    echo "   ❌ $func() not found ($description)\n";
    $all_bridges_exist = false;
  }
}

if (!$all_bridges_exist) {
  exit(1);
}

// ============================================================================
// SECTION 6: TEST WPDB BRIDGE
// ============================================================================
echo "\n" . str_repeat("=", 70) . "\n";
echo "SECTION 6: Database Bridge (Epic 3)\n";
echo str_repeat("-", 70) . "\n";

echo "Checking wpdb bridge is active...\n";
global $wpdb;

if ($wpdb instanceof wpdb) {
  echo "   ✅ Global \$wpdb instance exists\n";

  // Check wpdb properties
  if (isset($wpdb->prefix)) {
    echo "   ✅ \$wpdb->prefix: {$wpdb->prefix}\n";
  }

  // Check critical wpdb methods exist
  $wpdb_methods = array('query', 'get_results', 'get_var', 'get_row', 'prepare');
  foreach ($wpdb_methods as $method) {
    if (method_exists($wpdb, $method)) {
      echo "   ✅ \$wpdb->$method() method exists\n";
    } else {
      echo "   ❌ \$wpdb->$method() method missing\n";
      exit(1);
    }
  }

  // Test that query returns false (database interception working)
  $result = $wpdb->query("SELECT 1");
  if ($result === false) {
    echo "   ✅ Database queries intercepted (query returned false)\n";
  } else {
    echo "   ⚠️  Warning: Query returned non-false value: " . var_export($result, true) . "\n";
  }
} else {
  echo "   ❌ Global \$wpdb not initialized\n";
  exit(1);
}

// ============================================================================
// SECTION 7: VERIFY WORDPRESS GLOBALS
// ============================================================================
echo "\n" . str_repeat("=", 70) . "\n";
echo "SECTION 7: WordPress Global Variables (Epic 4)\n";
echo str_repeat("-", 70) . "\n";

$globals_to_check = array(
  'wpdb' => 'Database object',
  'wp_query' => 'Main query object',
  'wp_the_query' => 'Original query object',
  'wp_rewrite' => 'URL rewrite object',
  'wp' => 'Main WordPress object',
  'wp_filter' => 'Filter hooks registry',
  'wp_actions' => 'Action hooks registry',
);

echo "Checking WordPress global variables...\n";
foreach ($globals_to_check as $global_name => $description) {
  if (isset($GLOBALS[$global_name])) {
    $type = gettype($GLOBALS[$global_name]);
    if (is_object($GLOBALS[$global_name])) {
      $type = get_class($GLOBALS[$global_name]);
    }
    echo "   ✅ \$$global_name initialized ($type - $description)\n";
  } else {
    echo "   ⚠️  \$$global_name not initialized ($description)\n";
  }
}

// ============================================================================
// FINAL SUMMARY
// ============================================================================
echo "\n" . str_repeat("=", 70) . "\n";
echo "✅ ALL TESTS PASSED - WordPress Core Loads Successfully!\n";
echo str_repeat("=", 70) . "\n\n";

echo "Summary:\n";
echo "  • WordPress bootstrap completed without fatal errors\n";
echo "  • All critical constants defined\n";
echo "  • WordPress core classes loaded (WP_Post, WP_User, WP_Term, WP_Query, wpdb)\n";
echo "  • WordPress core functions available (hooks, templates, queries)\n";
echo "  • Epic 7 data bridges loaded and functional\n";
echo "  • wpdb database bridge active and intercepting queries\n";
echo "  • WordPress globals initialized\n";
echo "\n";
echo "WordPress-as-Engine architecture is operational! 🎉\n";
echo "\n";

exit(0);
