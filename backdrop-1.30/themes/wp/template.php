<?php
/**
 * @file
 * WordPress Theme Wrapper - template.php
 *
 * Bootstrap file that loads WordPress compatibility layer and delegates
 * rendering to a WordPress theme.
 */

// Define the active WordPress theme
if (!defined('WP2BD_ACTIVE_THEME')) {
  $active_theme = 'twentyseventeen'; // Default fallback
  try {
    if (function_exists('config')) {
      $config_theme = config('wp_content.settings')->get('active_theme');
      if (!empty($config_theme)) {
        $active_theme = $config_theme;
      }
    }
  } catch (Exception $e) {
    // Config might not be available yet
  }
  define('WP2BD_ACTIVE_THEME', $active_theme);
}

// Define paths
if (!defined('WP2BD_THEME_DIR')) {
  define('WP2BD_THEME_DIR', __DIR__);
  define('WP2BD_WP_THEMES_DIR', WP2BD_THEME_DIR . '/wp-content/themes');
  define('WP2BD_ACTIVE_THEME_DIR', WP2BD_WP_THEMES_DIR . '/' . WP2BD_ACTIVE_THEME);
}

// Define WordPress root paths so we can load core files early.
$project_root = dirname(BACKDROP_ROOT);
if (!defined('ABSPATH')) {
  define('ABSPATH', $project_root . '/wordpress-4.9/');
}
if (!defined('WPINC')) {
  define('WPINC', 'wp-includes');
}
if (!defined('WP_CONTENT_DIR')) {
  define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
}

// Initialize WordPress globals
global $wp_query, $wp_filter, $wp_actions, $wp_current_filter, $post, $wp_version, $pagenow;
$wp_filter = array();
$wp_actions = array();
$wp_current_filter = array();
$post = null;
$wp_version = '4.9'; // WordPress 4.9 compatibility
$GLOBALS['wp_version'] = '4.9';
$pagenow = 'index.php'; // Default to index.php for theme compatibility
$GLOBALS['pagenow'] = 'index.php';

// Load WordPress compatibility classes
require_once WP2BD_THEME_DIR . '/classes/WP_Post.php';
require_once WP2BD_THEME_DIR . '/classes/WP_Query.php';

// Load WordPress compatibility functions
require_once WP2BD_THEME_DIR . '/functions/hooks.php';
require_once WP2BD_THEME_DIR . '/functions/escaping.php';
require_once WP2BD_THEME_DIR . '/functions/i18n.php';
require_once WP2BD_THEME_DIR . '/functions/loop.php';
require_once WP2BD_THEME_DIR . '/functions/enqueue.php';
require_once WP2BD_THEME_DIR . '/functions/template-loading.php';
require_once WP2BD_THEME_DIR . '/functions/content-display.php';
require_once WP2BD_THEME_DIR . '/functions/conditionals.php';
require_once WP2BD_THEME_DIR . '/functions/utilities.php';
require_once WP2BD_THEME_DIR . '/functions/post-metadata.php';
// Note: stubs.php has been archived to _archive/ as of Dec 2024
// Functions should be properly implemented in the appropriate file above

// Override get_template_directory() to return WordPress theme directory
if (!function_exists('get_template_directory')) {
  function get_template_directory()
  {
    return WP2BD_ACTIVE_THEME_DIR;
  }
}

if (!function_exists('get_template_directory_uri')) {
  function get_template_directory_uri()
  {
    global $base_url;
    if (!$base_url) {
      $base_url = $GLOBALS['base_url'];
    }
    $theme_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', WP2BD_ACTIVE_THEME_DIR);
    return $base_url . $theme_path;
  }
}

if (!function_exists('get_stylesheet_directory')) {
  function get_stylesheet_directory()
  {
    return get_template_directory();
  }
}

if (!function_exists('get_stylesheet_directory_uri')) {
  function get_stylesheet_directory_uri()
  {
    return get_template_directory_uri();
  }
}

if (!function_exists('get_template')) {
  function get_template()
  {
    return WP2BD_ACTIVE_THEME;
  }
}

if (!function_exists('get_stylesheet')) {
  function get_stylesheet()
  {
    return WP2BD_ACTIVE_THEME;
  }
}

/**
 * Retrieve the path of a file in the parent theme.
 *
 * @param string $file Optional. File to search for in the parent theme.
 * @return string The path of the file.
 */
if (!function_exists('get_parent_theme_file_path')) {
  function get_parent_theme_file_path($file = '')
  {
    $path = get_template_directory();
    if (!empty($file)) {
      $path .= '/' . ltrim($file, '/');
    }
    return $path;
  }
}

/**
 * Retrieve the URL of a file in the theme.
 *
 * @param string $file Optional. File to search for in the theme.
 * @return string The URL of the file.
 */
if (!function_exists('get_theme_file_uri')) {
  function get_theme_file_uri($file = '')
  {
    $uri = get_template_directory_uri();
    if (!empty($file)) {
      $uri .= '/' . ltrim($file, '/');
    }
    return $uri;
  }
}

/**
 * Implements hook_preprocess_page().
 *
 * Initialize WordPress query for the current page.
 */
function wp_preprocess_page(&$variables)
{
  global $wp_query, $post;

  // Get current Backdrop node if viewing a node
  $node = menu_get_object();

  if ($node) {
    // Always reload the node fully to ensure it has all properties including 'type'
    // menu_get_object() might return a partially loaded node
    if (function_exists('node_load') && isset($node->nid)) {
      $full_node = node_load($node->nid);
      if ($full_node) {
        $node = $full_node;
      }
    }

    // Double-check: ensure node has type property (bundle) before converting
    // This prevents "Missing bundle property" errors
    if (!isset($node->type)) {
      // Try to get it from the node table directly if available
      if (isset($node->nid) && function_exists('db_query')) {
        $type_result = db_query('SELECT type FROM {node} WHERE nid = :nid', array(':nid' => $node->nid))->fetchField();
        if ($type_result) {
          $node->type = $type_result;
        } else {
          $node->type = 'page'; // Fallback
        }
      } else {
        $node->type = 'page'; // Fallback
      }
    }

    // Convert Backdrop node to WordPress post
    $post = WP_Post::from_node($node);

    // Create a simple WP_Query with this post
    $wp_query = new WP_Query(array(
      'p' => $node->nid,
      'post_type' => 'post',
    ));

    // Manually set up the query state
    $wp_query->posts = array($post);
    $wp_query->post_count = 1;
    $wp_query->current_post = -1; // Start before first post (WordPress loop convention)
    $wp_query->is_single = true;
    $wp_query->is_singular = true;
    $wp_query->queried_object = $post;
    $wp_query->queried_object_id = $post->ID;

    // Ensure the query object is properly initialized
    if (!isset($wp_query->post)) {
      $wp_query->post = null;
    }

    // Store in GLOBALS to ensure it persists across all scopes
    $GLOBALS['wp_query'] = $wp_query;
    $GLOBALS['post'] = null; // Reset global post until loop starts
  } else {
    // For home/archive pages, load published nodes
    // On home page, Backdrop shows promoted nodes (like node_page_default())

    // Check if we're on the front page
    $is_front = isset($variables['is_front']) && $variables['is_front'];

    // Try to load published nodes
    if (function_exists('node_load_multiple')) {
      // Use db_select like Backdrop's node_page_default() does
      if (function_exists('db_select')) {
        $site_config = config('system.core');
        $select = db_select('node', 'n')
          ->fields('n', array('nid', 'sticky', 'created'))
          ->condition('n.status', 1) // Published only
          ->orderBy('n.sticky', 'DESC')
          ->orderBy('n.created', 'DESC')
          ->addTag('node_access');

        // On front page, only show promoted nodes (like Backdrop does)
        if ($is_front) {
          $select->condition('n.promote', 1);
          $limit = $site_config->get('default_nodes_main') ?: 10;
        } else {
          // On other archive pages, show all published
          $limit = 10;
        }

        $select->range(0, $limit);
        $nids = $select->execute()->fetchCol();

        if (!empty($nids)) {
          $nodes = node_load_multiple($nids);

          // Convert to WP_Post objects
          $posts = array();
          foreach ($nodes as $node) {
            // Ensure node has type property (bundle) before converting
            if (!isset($node->type)) {
              // Try to reload if missing
              if (function_exists('node_load')) {
                $full_node = node_load($node->nid);
                if ($full_node && isset($full_node->type)) {
                  $node = $full_node;
                } else {
                  $node->type = 'page'; // Fallback
                }
              } else {
                $node->type = 'page'; // Fallback
              }
            }

            $wp_post = WP_Post::from_node($node);
            if ($wp_post) {
              $posts[] = $wp_post;
            }
          }

          // Create WP_Query object - use a query that returns empty, then override
          $wp_query = new WP_Query(array('p' => 0, 'post__in' => array(-999))); // Invalid IDs to return empty

          // Immediately override with our manually loaded posts
          $wp_query->posts = $posts;
          $wp_query->post_count = count($posts);
          $wp_query->current_post = -1; // Start before first post (WordPress loop convention)
          $wp_query->found_posts = count($posts);
          $wp_query->max_num_pages = 1;
          $wp_query->is_home = $is_front;
          $wp_query->is_archive = (count($posts) > 0);
          $wp_query->is_404 = false;
          $wp_query->is_single = false;
          $wp_query->is_page = false;
          $wp_query->is_singular = false;

          // Ensure the query object is properly initialized
          if (!isset($wp_query->post)) {
            $wp_query->post = null;
          }

          // Store in GLOBALS to ensure it persists across all scopes
          $GLOBALS['wp_query'] = $wp_query;
          $GLOBALS['post'] = null; // Reset global post until loop starts
        } else {
          $wp_query = new WP_Query(array('p' => 0, 'post__in' => array(-999)));
          $GLOBALS['wp_query'] = $wp_query;
        }
      } elseif (class_exists('EntityFieldQuery')) {
        // Fallback to EntityFieldQuery
        $query = new EntityFieldQuery();
        $query->entityCondition('entity_type', 'node');
        $query->propertyCondition('status', 1); // Published only
        $query->propertyOrderBy('created', 'DESC');
        $query->range(0, 10);

        try {
          $result = $query->execute();

          if (isset($result['node'])) {
            $nids = array_keys($result['node']);
            $nodes = node_load_multiple($nids);

            // Convert to WP_Post objects
            $posts = array();
            foreach ($nodes as $node) {
              // Ensure node has type property (bundle) before converting
              if (!isset($node->type)) {
                // Try to reload if missing
                if (function_exists('node_load')) {
                  $full_node = node_load($node->nid);
                  if ($full_node && isset($full_node->type)) {
                    $node = $full_node;
                  } else {
                    $node->type = 'page'; // Fallback
                  }
                } else {
                  $node->type = 'page'; // Fallback
                }
              }

              $wp_post = WP_Post::from_node($node);
              if ($wp_post) {
                $posts[] = $wp_post;
              }
            }

            // Create WP_Query object
            $wp_query = new WP_Query(array('p' => 0, 'post__in' => array(-999)));

            // Override with our manually loaded posts
            $wp_query->posts = $posts;
            $wp_query->post_count = count($posts);
            $wp_query->current_post = -1;
            $wp_query->found_posts = count($posts);
            $wp_query->max_num_pages = 1;
            $wp_query->is_home = $is_front;
            $wp_query->is_archive = (count($posts) > 0);
            $wp_query->is_404 = false;
            $wp_query->is_single = false;
            $wp_query->is_page = false;
            $wp_query->is_singular = false;

            if (!isset($wp_query->post)) {
              $wp_query->post = null;
            }

            $GLOBALS['wp_query'] = $wp_query;
            $GLOBALS['post'] = null;
          } else {
            $wp_query = new WP_Query(array('p' => 0, 'post__in' => array(-999)));
            $GLOBALS['wp_query'] = $wp_query;
          }
        } catch (Exception $e) {
          $wp_query = new WP_Query(array('p' => 0, 'post__in' => array(-999)));
          $GLOBALS['wp_query'] = $wp_query;
        }
      } else {
        $wp_query = new WP_Query(array('p' => 0, 'post__in' => array(-999)));
        $GLOBALS['wp_query'] = $wp_query;
      }
    } else {
      $wp_query = new WP_Query(array('p' => 0, 'post__in' => array(-999)));
      $GLOBALS['wp_query'] = $wp_query;
    }
  }

  // Body classes are now handled in wp_content.module
}

/**
 * Load WordPress theme's functions.php if it exists
 */
$functions_file = WP2BD_ACTIVE_THEME_DIR . '/functions.php';
if (file_exists($functions_file)) {
  require_once $functions_file;
}

/**
 * Implements template_preprocess_html().
 *
 * Set up HTML attributes using WordPress functions.
 */
function wp_preprocess_html(&$variables)
{
  // WordPress themes expect <html> tag to have language attributes
  $variables['html_attributes'] = language_attributes(false);

  // CSS is now loaded via wp_enqueue_scripts -> stubs.php
}
