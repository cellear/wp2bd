<?php
/**
 * @file
 * Page template for WordPress theme integration.
 *
 * This template bypasses Backdrop's normal block/layout system and directly
 * renders the WordPress theme. WordPress themes provide their own complete
 * HTML structure (DOCTYPE, html, head, body) via header.php/footer.php.
 * 
 * Backdrop's CSS, JS, and other assets are INJECTED into the WordPress output.
 */

// Load WordPress compatibility layer if not already loaded
$theme_path = BACKDROP_ROOT . '/themes/wp/template.php';
if (!function_exists('get_sidebar') && file_exists($theme_path)) {
  require_once $theme_path;
}

// Set up WordPress query globals
if (function_exists('_wp_content_setup_query')) {
  _wp_content_setup_query();
}

// Fire wp_enqueue_scripts so theme can register its CSS/JS
if (!isset($GLOBALS['wp2bd_scripts_enqueued'])) {
  do_action('wp_enqueue_scripts');
  $GLOBALS['wp2bd_scripts_enqueued'] = TRUE;
}

// Capture WordPress theme output
ob_start();

// Determine which template to use (index.php, single.php, etc.)
$template = _wp_content_get_template();

if ($template && file_exists($template)) {
  try {
    include $template;
  } catch (Exception $e) {
    echo '<!-- WordPress template error: ' . htmlspecialchars($e->getMessage()) . ' -->';
    watchdog('wp_content', 'Template error: @error', array('@error' => $e->getMessage()), WATCHDOG_ERROR);
  } catch (Error $e) {
    echo '<!-- WordPress template error: ' . htmlspecialchars($e->getMessage()) . ' -->';
    watchdog('wp_content', 'Template error: @error', array('@error' => $e->getMessage()), WATCHDOG_ERROR);
  }
} else {
  echo '<!-- WordPress template not found -->';
}

$output = ob_get_clean();

// Inject Backdrop's assets into WordPress's HTML structure

// 1. Inject Backdrop's head content before </head>
$backdrop_head = backdrop_get_html_head() . "\n" . backdrop_get_css() . "\n" . backdrop_get_js();
$output = preg_replace('/<\/head>/i', $backdrop_head . "\n</head>", $output, 1);

// 2. Add Backdrop's body classes to the <body> tag
$backdrop_body_classes = implode(' ', $classes);
if (preg_match('/<body([^>]*)class="([^"]*)"/', $output)) {
  $output = preg_replace('/<body([^>]*)class="([^"]*)"/', '<body$1class="$2 ' . $backdrop_body_classes . '"', $output, 1);
} elseif (preg_match('/<body/', $output)) {
  $output = preg_replace('/<body/', '<body class="' . $backdrop_body_classes . '"', $output, 1);
}

// 3. Inject footer JS before </body>
$backdrop_footer = backdrop_get_js('footer');
if (!empty($page_bottom)) {
  $backdrop_footer .= $page_bottom;
}
$output = preg_replace('/<\/body>/i', $backdrop_footer . "\n</body>", $output, 1);

// Output the complete page
print $output;
