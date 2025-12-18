<?php
/**
 * @file
 * WordPress Theme Wrapper - template.php
 *
 * Bootstrap file that loads WordPress compatibility layer and delegates
 * rendering to a WordPress theme.
 */

function wp_render_wordpress_content() {
  // Get current Backdrop node
  $node = menu_get_object();

  // For home page, show multiple posts
  if (!$node && backdrop_is_front_page()) {
    return wp_render_home_page_posts();
  }

  // For single post pages
  if ($node) {
    return wp_render_single_post($node);
  }

  return '<div class="no-content"><p>No content available for this page.</p></div>';
}

function wp_render_home_page_posts() {
  // Load multiple published nodes for the home page
  $query = new EntityFieldQuery();
  $query->entityCondition('entity_type', 'node')
        ->propertyCondition('status', 1) // Published
        ->propertyOrderBy('created', 'DESC')
        ->range(0, 10); // Show up to 10 posts
  $result = $query->execute();

  if (empty($result['node'])) {
    return '<div class="no-content"><p>No posts available.</p></div>';
  }

  $nids = array_keys($result['node']);
  $nodes = node_load_multiple($nids);

  // Set up WordPress query for home page
  global $wp_query, $post;
  $wp_posts = array();

  foreach ($nodes as $node) {
    $wp_posts[] = WP_Post::from_node($node);
  }

  $wp_query = new WP_Query();
  $wp_query->posts = $wp_posts;
  $wp_query->post_count = count($wp_posts);
  $wp_query->current_post = -1;
  $wp_query->is_home = true;
  $wp_query->is_archive = false;
  $wp_query->queried_object = null;

  $GLOBALS['wp_query'] = $wp_query;

  // Render posts in a loop
  ob_start();

  if (have_posts()) {
    while (have_posts()) {
      the_post();
      ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <header class="entry-header">
          <h2 class="entry-title">
            <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
          </h2>
          <div class="entry-meta">
            <span class="posted-on">Posted on <?php echo get_the_date(); ?></span>
            <span class="byline"> by <span class="author vcard"><?php the_author(); ?></span></span>
          </div>
        </header>

        <div class="entry-content">
          <?php the_excerpt(); ?>
        </div>
      </article>
      <?php
    }
  }

  $content = ob_get_clean();
  return $content;
}

function wp_render_single_post($node) {
  // Convert Backdrop node to WordPress post
  $wp_post = WP_Post::from_node($node);
  if (!$wp_post) {
    return '<div class="no-content"><p>Unable to load content for this page.</p></div>';
  }

  // Set up WordPress query
  global $wp_query, $post;
  $post = $wp_post;
  $wp_query = new WP_Query(array(
    'p' => $node->nid,
    'post_type' => 'post',
  ));
  $wp_query->posts = array($post);
  $wp_query->post_count = 1;
  $wp_query->current_post = -1;
  $wp_query->is_single = true;
  $wp_query->is_singular = true;
  $wp_query->queried_object = $post;
  $wp_query->queried_object_id = $post->ID;

  $GLOBALS['wp_query'] = $wp_query;
  $GLOBALS['post'] = $post;

  // Render WordPress content
  ob_start();
  ?>
  <article id="post-<?php the_ID(); ?>" class="post type-post status-publish format-standard hentry">
    <header class="entry-header">
      <h1 class="entry-title"><?php the_title(); ?></h1>
      <div class="entry-meta">
        <span class="posted-on">Posted on <?php echo get_the_date('F j, Y', $wp_post); ?></span>
        <span class="byline"> by <span class="author vcard"><?php echo get_the_author_meta('display_name', $wp_post->post_author); ?></span></span>
      </div>
    </header>

    <div class="entry-content">
      <?php the_content(); ?>
    </div>
  </article>
  <?php
  $content = ob_get_clean();

  return $content;
}

// Load WordPress compatibility classes early
require_once __DIR__ . '/classes/WP_Post.php';
require_once __DIR__ . '/classes/WP_Query.php';

// Load essential template functions early
require_once __DIR__ . '/functions/utilities.php';
require_once __DIR__ . '/functions/hooks.php';

// Set WordPress globals early
if (!isset($GLOBALS['wp_version'])) {
  $GLOBALS['wp_version'] = '4.9';
}
if (!isset($GLOBALS['pagenow'])) {
  $GLOBALS['pagenow'] = 'index.php';
}

// Determine active theme early
$active_theme = 'twentyseventeen'; // Default fallback

// Check for theme setting (Backdrop config system)
if (function_exists('config_get')) {
  try {
    $theme_config = config_get('system.theme.wp');
    if (isset($theme_config['wp_active_theme']) && !empty($theme_config['wp_active_theme'])) {
      $active_theme = $theme_config['wp_active_theme'];
    }
  } catch (Exception $e) {
    // Config might not exist yet
  }
}

// Fallback: check for a simple theme selector (for testing)
$theme_changed = false;
if (isset($_GET['wp_theme']) && !empty($_GET['wp_theme'])) {
  $requested_theme = preg_replace('/[^a-zA-Z0-9-_]/', '', $_GET['wp_theme']);
  $theme_path = __DIR__ . '/wp-content/themes/' . $requested_theme;
  if (function_exists('watchdog')) {
    watchdog('wp4bd_debug', 'Theme switch requested: @theme (path exists: @exists)', array('@theme' => $requested_theme, '@exists' => is_dir($theme_path) ? 'yes' : 'no'), WATCHDOG_DEBUG);
  }
  if (is_dir($theme_path) && file_exists($theme_path . '/style.css') && $requested_theme !== $active_theme) {
    $active_theme = $requested_theme;
    $theme_changed = true;
    if (function_exists('watchdog')) {
      watchdog('wp4bd_debug', 'Theme switched to: @theme', array('@theme' => $active_theme), WATCHDOG_DEBUG);
    }
  }
}

// Set final theme constants
define('WP2BD_ACTIVE_THEME', $active_theme);
define('WP2BD_ACTIVE_THEME_DIR', __DIR__ . '/wp-content/themes/' . $active_theme);

// Define theme directory constants early
$wp_themes_dir = __DIR__ . '/wp-content/themes';
$active_theme_dir = $wp_themes_dir . '/' . $active_theme;
define('WP2BD_WP_THEMES_DIR', $wp_themes_dir);
define('WP2BD_ACTIVE_THEME', $active_theme);
define('WP2BD_ACTIVE_THEME_DIR', $active_theme_dir);

// Load the theme's functions.php
$functions_file = __DIR__ . '/wp-content/themes/' . $active_theme . '/functions.php';
if (file_exists($functions_file)) {
  require_once $functions_file;
}

// Load essential WordPress functions early
require_once __DIR__ . '/functions/post-metadata.php';

// Theme already determined and defined above

// Define theme directory constant
if (!defined('WP2BD_THEME_DIR')) {
  define('WP2BD_THEME_DIR', __DIR__);
}

// WordPress Compatibility Mode
// We use compatibility shims instead of loading WordPress core to avoid conflicts with Backdrop
$wordpress_bootstrapped = false;

if (!$wordpress_bootstrapped) {
  // FALLBACK: LEGACY MODE (Epics 1-7): Use compatibility shims
  // Define WordPress root paths so we can load core files early.
  if (!defined('ABSPATH')) {
    define('ABSPATH', BACKDROP_ROOT . '/themes/wp/wpbrain/');
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

  // Load WordPress compatibility shim classes
  require_once WP2BD_THEME_DIR . '/classes/WP_Post.php';
  require_once WP2BD_THEME_DIR . '/classes/WP_Query.php';

  // Load WordPress compatibility functions (only in legacy mode)
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
  require_once WP2BD_THEME_DIR . '/functions/widgets.php';
  // Note: stubs.php has been archived to _archive/ as of Dec 2024
  // Functions should be properly implemented in the appropriate file above
}

// Debug template suggestion removed; debug view should be activated manually.

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

  // Add Backdrop messages for display
  $variables['messages'] = theme('status_messages');

  // Body classes are now handled in wp_content.module
}

/**
 * WordPress theme's functions.php is now loaded early at the top of this file
 * to ensure enqueueing works properly during wp_head()
 */

/**
 * Implements hook_block_info().
 *
 * Define blocks provided by the WordPress theme.
 */
function wp_block_info() {
  $blocks = array();

  $blocks['wp_content'] = array(
    'info' => t('WordPress Content'),
    'description' => t('Displays WordPress theme content for the current page.'),
    'cache' => DRUPAL_NO_CACHE,
  );

  return $blocks;
}

/**
 * Implements hook_block_view().
 *
 * Render blocks provided by the WordPress theme.
 */
function wp_block_view($delta = '') {
  $block = array();

  switch ($delta) {
    case 'wp_content':
      // Render WordPress content using the WordPress theme system
      $block['subject'] = NULL; // No title
      $block['content'] = wp_render_wordpress_content();
      break;
  }

  return $block;
}

/**
 * Render WordPress content for the current page.
 *
 * This function handles the WordPress theme rendering logic.
 */

/**
 * Implements template_preprocess_html().
 *
 * Set up HTML attributes using WordPress functions and ensure WordPress assets load.
 * This hook runs before page.tpl.php is rendered, allowing us to inject WordPress
 * CSS/JS into the head section.
 */
function wp_preprocess_html(&$variables)
{
  // WordPress themes expect <html> tag to have language attributes
  $variables['html_attributes'] = language_attributes(false);

  // Fire wp_enqueue_scripts to allow WordPress themes to enqueue CSS/JS
  if (function_exists('do_action')) {
    do_action('wp_enqueue_scripts');
  }

  // Also manually call twentyseventeen_scripts if it exists
  if (function_exists('twentyseventeen_scripts')) {
    twentyseventeen_scripts();
  }

  // Add WordPress head content to the head section
  if (function_exists('wp_head')) {
    ob_start();
    wp_head();
    $wp_head = ob_get_clean();
    // Append to existing head content
    $variables['head'] .= $wp_head;
  }

  // Add WordPress footer scripts to page_bottom
  if (function_exists('wp_footer')) {
    ob_start();
    wp_footer();
    $wp_footer = ob_get_clean();
    // Append to page_bottom
    if (!isset($variables['page_bottom'])) {
      $variables['page_bottom'] = '';
    }
    $variables['page_bottom'] .= $wp_footer;
  }
}
