<?php
/**
 * @file
 * WP4BD V2 Production Template
 *
 * This template renders WordPress themes using Backdrop data through the
 * WordPress-as-Engine architecture.
 *
 * Architecture Overview:
 * - Backdrop handles all data storage and routing
 * - WordPress 4.9 loads as a rendering engine
 * - Data bridges convert Backdrop data to WordPress objects
 * - WordPress themes render normally with Backdrop data
 *
 * Debug Mode:
 * - Add ?wp4bd_debug=2 to URL to see debug output
 * - Levels: 1=basic, 2=detailed, 3=verbose, 4=everything
 *
 * Completed Implementation:
 * - Epic 1: Debug Infrastructure ✅
 * - Epic 2: WordPress Core Setup ✅
 * - Epic 3: Database Interception ✅
 * - Epic 4: WordPress Globals ✅
 * - Epic 5: External I/O Interception ✅
 * - Epic 6: Bootstrap Integration ✅
 * - Epic 7: Data Structure Bridges ✅
 * - Epic 8: Testing & Validation ✅
 */

// ============================================================================
// INITIALIZE DEBUG MODE
// ============================================================================
$wp4bd_debug_mode = FALSE;
$wp4bd_debug_level = 0;

if (isset($_GET['wp4bd_debug'])) {
  $wp4bd_debug_mode = TRUE;
  $wp4bd_debug_level = max(1, min(4, (int) $_GET['wp4bd_debug']));
}

// ============================================================================
// BOOTSTRAP WORDPRESS
// ============================================================================
$wp4bd_bootstrap_success = FALSE;
$wp4bd_bootstrap_errors = array();

try {
  // Load WordPress bootstrap
  $bootstrap_file = BACKDROP_ROOT . '/modules/wp_content/includes/wp-bootstrap.php';
  if (file_exists($bootstrap_file)) {
    require_once $bootstrap_file;

    // Bootstrap WordPress
    $wp4bd_bootstrap_success = wp4bd_bootstrap_wordpress($wp4bd_bootstrap_errors);

    if (!$wp4bd_bootstrap_success && $wp4bd_debug_mode) {
      echo '<div style="margin: 20px; padding: 20px; background: #f8d7da; border-left: 4px solid #dc3545;">';
      echo '<h3>⚠️ WordPress Bootstrap Failed</h3>';
      if (!empty($wp4bd_bootstrap_errors)) {
        echo '<ul>';
        foreach ($wp4bd_bootstrap_errors as $error) {
          echo '<li>' . htmlspecialchars($error) . '</li>';
        }
        echo '</ul>';
      }
      echo '</div>';
    }
  } else {
    $wp4bd_bootstrap_errors[] = 'Bootstrap file not found: ' . $bootstrap_file;
  }
} catch (Exception $e) {
  $wp4bd_bootstrap_errors[] = 'Exception during bootstrap: ' . $e->getMessage();
}

// ============================================================================
// DEBUG OUTPUT (if enabled)
// ============================================================================
if ($wp4bd_debug_mode && $wp4bd_bootstrap_success) {
  // Load debug helper functions
  $debug_helper = BACKDROP_ROOT . '/modules/wp_content/wp4bd_debug.inc';
  if (file_exists($debug_helper)) {
    require_once $debug_helper;
  }

  // Load full debug template
  $debug_template = BACKDROP_ROOT . '/themes/wp/templates/page-debug-epics-1-7.tpl.php';
  if (file_exists($debug_template)) {
    include $debug_template;
    return; // Stop here if showing debug
  }
}

// ============================================================================
// CHECK BOOTSTRAP SUCCESS
// ============================================================================
if (!$wp4bd_bootstrap_success) {
  ?>
  <!DOCTYPE html>
  <html>
  <head>
    <meta charset="utf-8">
    <title>WordPress Bootstrap Error</title>
    <style>
      body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 40px; }
      .error-box { background: #f8d7da; border-left: 4px solid #dc3545; padding: 20px; margin: 20px 0; }
      .help { background: #d1ecf1; border-left: 4px solid #0c5460; padding: 20px; margin: 20px 0; }
      code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
  </head>
  <body>
    <h1>⚠️ WordPress Bootstrap Error</h1>
    <div class="error-box">
      <p>WordPress failed to initialize. This site uses the WordPress-as-Engine architecture where WordPress 4.9 renders Backdrop CMS data.</p>
      <?php if (!empty($wp4bd_bootstrap_errors)): ?>
        <h3>Errors:</h3>
        <ul>
          <?php foreach ($wp4bd_bootstrap_errors as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
    <div class="help">
      <h3>Troubleshooting:</h3>
      <ul>
        <li>Check that the <code>wp_content</code> module is enabled</li>
        <li>Verify WordPress files exist at <code>backdrop-1.30/themes/wp/wpbrain/</code></li>
        <li>Check file permissions on the WordPress directory</li>
        <li>Add <code>?wp4bd_debug=2</code> to the URL for detailed debug information</li>
      </ul>
    </div>
  </body>
  </html>
  <?php
  return;
}

// ============================================================================
// PREPARE CONTENT FOR THE LOOP
// ============================================================================

// Get current Backdrop node (Backdrop sets this for us)
global $node;

// Initialize WordPress globals
global $wp_query, $wp_the_query, $post, $posts;

// Convert Backdrop node to WP_Post if available
$wp_posts = array();
if (isset($node) && is_object($node)) {
  if (function_exists('wp4bd_node_to_post')) {
    $wp_post = wp4bd_node_to_post($node);
    if ($wp_post instanceof WP_Post) {
      $wp_posts[] = $wp_post;
    }
  }
}

// Set up WordPress query with our posts
if (!empty($wp_posts)) {
  // Create a mock WP_Query
  if (class_exists('WP_Query')) {
    $wp_query = new WP_Query();
    $wp_query->posts = $wp_posts;
    $wp_query->post_count = count($wp_posts);
    $wp_query->found_posts = count($wp_posts);
    $wp_query->max_num_pages = 1;
    $wp_query->is_single = true;
    $wp_query->is_singular = true;
    $wp_query->is_page = false;
    $wp_query->is_home = false;
    $wp_query->is_archive = false;
    $wp_query->current_post = -1;

    // Set the main query
    $wp_the_query = $wp_query;
    $posts = $wp_posts;
  }
}

// ============================================================================
// RENDER WORDPRESS THEME
// ============================================================================
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php wp_title('|', true, 'right'); ?> <?php bloginfo('name'); ?></title>

  <?php
  // WordPress themes expect wp_head() to be called
  // This loads theme styles, scripts, and meta tags
  if (function_exists('wp_head')) {
    wp_head();
  }
  ?>

  <!-- Backdrop Assets -->
  <?php print $styles; ?>
  <?php print $scripts; ?>

  <style>
    /* Basic styling if theme doesn't load */
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; }
    .site-header { background: #f8f9fa; padding: 20px; border-bottom: 1px solid #dee2e6; }
    .site-title { margin: 0; font-size: 2em; }
    .site-description { margin: 5px 0 0 0; color: #6c757d; }
    .site-content { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
    .entry-header { margin-bottom: 30px; }
    .entry-title { font-size: 2.5em; margin: 0 0 10px 0; }
    .entry-meta { color: #6c757d; font-size: 0.9em; }
    .entry-content { font-size: 1.1em; }
    .entry-content p { margin: 1em 0; }
    .site-footer { background: #f8f9fa; padding: 20px; border-top: 1px solid #dee2e6; text-align: center; margin-top: 40px; }
    .debug-link { position: fixed; bottom: 20px; right: 20px; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .debug-link:hover { background: #0056b3; }
  </style>
</head>

<body <?php body_class(); ?>>

<!-- Site Header -->
<header class="site-header">
  <h1 class="site-title">
    <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
      <?php bloginfo('name'); ?>
    </a>
  </h1>
  <?php
  $description = get_bloginfo('description', 'display');
  if ($description || is_customize_preview()) :
    ?>
    <p class="site-description"><?php echo $description; ?></p>
  <?php endif; ?>
</header>

<!-- Main Content -->
<main class="site-content">

  <?php if (have_posts()) : ?>

    <?php
    // The Loop
    while (have_posts()) : the_post();
      ?>

      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <header class="entry-header">
          <h1 class="entry-title"><?php the_title(); ?></h1>
          <div class="entry-meta">
            Posted on <?php the_date(); ?> by <?php the_author(); ?>
            <?php if (has_category()) : ?>
              | Categories: <?php the_category(', '); ?>
            <?php endif; ?>
            <?php if (has_tag()) : ?>
              | Tags: <?php the_tags('', ', ', ''); ?>
            <?php endif; ?>
          </div>
        </header>

        <div class="entry-content">
          <?php the_content(); ?>
        </div>

        <?php if (comments_open() || get_comments_number()) : ?>
          <div class="entry-comments">
            <!-- Comments would be rendered here -->
            <p><em>Comments: <?php comments_number('0', '1', '%'); ?></em></p>
          </div>
        <?php endif; ?>

      </article>

    <?php endwhile; ?>

  <?php else : ?>

    <article class="no-content">
      <header class="entry-header">
        <h1>No Content Available</h1>
      </header>
      <div class="entry-content">
        <p>There is no content to display on this page.</p>
        <?php if ($wp4bd_debug_mode): ?>
          <p><strong>Debug Info:</strong></p>
          <ul>
            <li>Backdrop $node: <?php echo isset($node) ? 'exists' : 'not set'; ?></li>
            <li>WordPress $posts: <?php echo empty($wp_posts) ? 'empty' : count($wp_posts) . ' post(s)'; ?></li>
            <li>wp4bd_node_to_post: <?php echo function_exists('wp4bd_node_to_post') ? 'available' : 'not found'; ?></li>
          </ul>
        <?php endif; ?>
      </div>
    </article>

  <?php endif; ?>

  <?php
  // Reset postdata after The Loop
  wp_reset_postdata();
  ?>

</main>

<!-- Site Footer -->
<footer class="site-footer">
  <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?> | Powered by WordPress-as-Engine Architecture</p>
  <p><small>WordPress <?php bloginfo('version'); ?> + Backdrop CMS</small></p>
  <?php
  if (function_exists('wp_footer')) {
    wp_footer();
  }
  ?>
</footer>

<!-- Debug Link -->
<?php if (!$wp4bd_debug_mode): ?>
  <a href="?wp4bd_debug=2" class="debug-link">🔍 Debug</a>
<?php endif; ?>

<!-- Backdrop Messages -->
<?php if ($messages): ?>
  <div class="backdrop-messages" style="position: fixed; top: 20px; right: 20px; max-width: 400px; z-index: 9999;">
    <?php print $messages; ?>
  </div>
<?php endif; ?>

</body>
</html>
<?php

// ============================================================================
// PRODUCTION TEMPLATE END
// ============================================================================
