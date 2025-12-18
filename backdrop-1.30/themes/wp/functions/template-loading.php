<?php
/**
 * Template Loading Functions for WP2BD
 *
 * Maps WordPress template loading functions to Backdrop's theme system.
 *
 * @package WP2BD
 * @subpackage Template_Loading
 */

/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * @param string|array $template_names Template file(s) to search for, in order.
 * @param bool $load If true the template file will be loaded if it is found.
 * @param bool $require_once Whether to require_once or require. Default true.
 * @return string The template filename if one is located.
 */
if (!function_exists('locate_template')) {
  function locate_template($template_names, $load = false, $require_once = true) {
    $located = '';

    if (!is_array($template_names)) {
      $template_names = array($template_names);
    }

    $theme_dir = get_template_directory();

    foreach ($template_names as $template_name) {
      if (!$template_name) {
        continue;
      }

      $file = $theme_dir . '/' . $template_name;

      if (file_exists($file)) {
        $located = $file;
        break;
      }
    }

    if ($load && '' !== $located) {
      if ($require_once) {
        require_once($located);
      } else {
        require($located);
      }
    }

    return $located;
  }
}

/**
 * Load header template.
 *
 * Includes the header template for a theme or if a name is specified then a
 * specialized header will be included. If the theme contains no header.php
 * file then no header will be included.
 *
 * The template is included using require_once, so the template will only be
 * included once per page.
 *
 * WordPress Behavior:
 * - Searches for header-{$name}.php first, then header.php
 * - Checks child theme before parent theme
 * - Fires 'get_header' action hook before including template
 * - Passes $name to the action hook
 *
 * Backdrop Mapping:
 * - Uses global $theme to determine active theme
 * - Uses backdrop_get_path('theme', $theme) to locate theme directory
 * - For child themes, checks active theme first, then base theme
 * - Maintains WordPress naming convention (header.php, header-{$name}.php)
 *
 * @since WP2BD 1.0.0
 *
 * @param string|null $name Optional. Name of the specific header file. Default null.
 *                          If specified, looks for header-{$name}.php.
 * @return bool True if header was found and loaded, false otherwise.
 */
function get_header($name = null) {
    // Fire the 'get_header' action hook before including template
    // This allows plugins/themes to hook in before header is loaded
    do_action('get_header', $name);

    // Get the WordPress theme directory (not Backdrop theme!)
    $theme_dir = get_template_directory();

    // Build list of template files to check
    $templates = array();

    // If a name was specified, check for header-{$name}.php first
    if (null !== $name) {
        $templates[] = "header-{$name}.php";
    }

    // Always check for header.php as fallback
    $templates[] = 'header.php';

    // Search for template in WordPress theme directory
    foreach ($templates as $template) {
        $template_file = $theme_dir . '/' . $template;

        if (file_exists($template_file)) {
            try {
                // Check if we're in Backdrop context (HTML/head already output)
                // In Backdrop, we only want the header content, not the full HTML structure
                if (defined('BACKDROP_ROOT')) {
                    // Start output buffering to capture and modify the header output
                    ob_start();
                    require $template_file;
                    $header_content = ob_get_clean();

                    // Remove HTML document structure that conflicts with Backdrop's page template
                    // Use a more comprehensive approach to strip nested HTML structure
                    $header_content = preg_replace('#<!DOCTYPE[^>]*>#i', '', $header_content);
                    $header_content = preg_replace('#<html[^>]*>.*?</html>#is', '', $header_content);
                    $header_content = preg_replace('#<head[^>]*>.*?</head>#is', '', $header_content);
                    $header_content = preg_replace('#<body[^>]*>#i', '', $header_content);
                    $header_content = preg_replace('#</body>#i', '', $header_content);
                    $header_content = preg_replace('#</html>#i', '', $header_content);

                    // For header.php, extract only the header content and remove content containers
                    // WordPress header templates often include content structure that we handle separately
                    if (preg_match('#(.*)</header>#is', $header_content, $matches)) {
                        $header_content = $matches[1] . '</header>';
                        // Remove any content containers that might be after the header
                        $header_content = preg_replace('#</header>.*?<div[^>]*id="content[^"]*"[^>]*>.*?</div>#is', '</header>', $header_content);
                        $header_content = preg_replace('#</header>.*?<div[^>]*class="site-content[^"]*"[^>]*>.*?</div>#is', '</header>', $header_content);
                    }

                    echo $header_content;
                } else {
                    // Normal WordPress context
                    require $template_file;
                }
            } catch (Error $e) {
                echo "<!-- Header error: " . htmlspecialchars($e->getMessage()) . " -->\n";
                watchdog('wp_content', 'Header error: @error', array('@error' => $e->getMessage()), WATCHDOG_ERROR);
            }
            return true;
        }
    }

    // No header template found
    echo "<!-- No header template found -->\n";
    return false;
}

/**
 * Load footer template.
 *
 * Includes the footer template for a theme or if a name is specified then a
 * specialized footer will be included. If the theme contains no footer.php
 * file then no footer will be included.
 *
 * The template is included using require_once, so the template will only be
 * included once per page.
 *
 * WordPress Behavior:
 * - Searches for footer-{$name}.php first, then footer.php
 * - Checks child theme before parent theme
 * - Fires 'get_footer' action hook before including template
 * - Passes $name to the action hook
 *
 * Backdrop Mapping:
 * - Uses global $theme to determine active theme
 * - Uses backdrop_get_path('theme', $theme) to locate theme directory
 * - For child themes, checks active theme first, then base theme
 * - Maintains WordPress naming convention (footer.php, footer-{$name}.php)
 *
 * @since WP2BD 1.0.0
 *
 * @param string|null $name Optional. Name of the specific footer file. Default null.
 *                          If specified, looks for footer-{$name}.php.
 * @return bool True if footer was found and loaded, false otherwise.
 */
function get_footer($name = null) {
    // Fire the 'get_footer' action hook before including template
    // This allows plugins/themes to hook in before footer is loaded
    do_action('get_footer', $name);

    // Get the WordPress theme directory (not Backdrop theme!)
    $theme_dir = get_template_directory();

    // Build list of template files to check
    $templates = array();

    // If a name was specified, check for footer-{$name}.php first
    if (null !== $name) {
        $templates[] = "footer-{$name}.php";
    }

    // Always check for footer.php as fallback
    $templates[] = 'footer.php';

    // Search for template in WordPress theme directory
    foreach ($templates as $template) {
        $template_file = $theme_dir . '/' . $template;

        if (file_exists($template_file)) {
            try {
                // Check if we're in Backdrop context (HTML/head already output)
                // In Backdrop, we only want the footer content, not the full HTML structure
                if (defined('BACKDROP_ROOT')) {
                    // Start output buffering to capture and modify the footer output
                    ob_start();
                    require $template_file;
                    $footer_content = ob_get_clean();

                    // Remove HTML document structure that conflicts with Backdrop's page template
                    $footer_content = preg_replace('#<!DOCTYPE[^>]*>#i', '', $footer_content);
                    $footer_content = preg_replace('#<html[^>]*>.*?</html>#is', '', $footer_content);
                    $footer_content = preg_replace('#<head[^>]*>.*?</head>#is', '', $footer_content);
                    $footer_content = preg_replace('#<body[^>]*>#i', '', $footer_content);
                    $footer_content = preg_replace('#</body>#i', '', $footer_content);
                    $footer_content = preg_replace('#</html>#i', '', $footer_content);

                    echo $footer_content;
                } else {
                    // Normal WordPress context
                    require $template_file;
                }
            } catch (Error $e) {
                echo "<!-- Footer error: " . htmlspecialchars($e->getMessage()) . " -->\n";
                watchdog('wp_content', 'Footer error: @error', array('@error' => $e->getMessage()), WATCHDOG_ERROR);
            }
            return true;
        }
    }

    // No footer template found
    return false;
}

/**
 * Load sidebar template.
 *
 * Includes the sidebar template for a theme or if a name is specified then a
 * specialized sidebar will be included.
 *
 * @since WP2BD 1.0.0
 *
 * @param string|null $name Optional. Name of the specific sidebar file. Default null.
 * @return bool True if sidebar was found and loaded, false otherwise.
 */
function get_sidebar($name = null) {
    do_action('get_sidebar', $name);

    // Set flag to indicate sidebar was rendered via get_sidebar()
    // This helps prevent duplicate rendering in the sidebar block
    $GLOBALS['wp2bd_sidebar_rendered'] = TRUE;

    // Get the WordPress theme directory (not Backdrop theme!)
    $theme_dir = get_template_directory();

    $templates = array();
    if (null !== $name) {
        $templates[] = "sidebar-{$name}.php";
    }
    $templates[] = 'sidebar.php';

    foreach ($templates as $template) {
        $template_file = $theme_dir . '/' . $template;
        if (file_exists($template_file)) {
            try {
                // Check if we're in Backdrop context (HTML/head already output)
                // In Backdrop, we only want the sidebar content, not the full HTML structure
                if (defined('BACKDROP_ROOT')) {
                    // Start output buffering to capture and modify the sidebar output
                    ob_start();
                    require $template_file;
                    $sidebar_content = ob_get_clean();

                    // Remove HTML document structure that conflicts with Backdrop's page template
                    $sidebar_content = preg_replace('#<!DOCTYPE[^>]*>#i', '', $sidebar_content);
                    $sidebar_content = preg_replace('#<html[^>]*>.*?</html>#is', '', $sidebar_content);
                    $sidebar_content = preg_replace('#<head[^>]*>.*?</head>#is', '', $sidebar_content);
                    $sidebar_content = preg_replace('#<body[^>]*>#i', '', $sidebar_content);
                    $sidebar_content = preg_replace('#</body>#i', '', $sidebar_content);
                    $sidebar_content = preg_replace('#</html>#i', '', $sidebar_content);

                    echo $sidebar_content;
                } else {
                    // Normal WordPress context
                    require $template_file;
                }
            } catch (Error $e) {
                echo "<!-- Sidebar error: " . htmlspecialchars($e->getMessage()) . " -->\n";
                watchdog('wp_content', 'Sidebar error: @error', array('@error' => $e->getMessage()), WATCHDOG_ERROR);
            }
            return true;
        }
    }

    return false;
}

/**
 * Load a template part into a template.
 *
 * Makes it easy for a theme to reuse sections of code and allows for
 * template specialization through name parameter.
 *
 * @since WP2BD 1.0.0
 *
 * @param string      $slug The slug name for the generic template.
 * @param string|null $name Optional. The name of the specialized template.
 * @return bool True if template part was found and loaded, false otherwise.
 */
function get_template_part($slug, $name = null) {
    do_action("get_template_part_{$slug}", $slug, $name);

    // Get the WordPress theme directory (not Backdrop theme!)
    $theme_dir = get_template_directory();

    $templates = array();
    $name = (string) $name;
    if ('' !== $name) {
        $templates[] = "{$slug}-{$name}.php";
    }
    $templates[] = "{$slug}.php";

    foreach ($templates as $template) {
        $template_file = $theme_dir . '/' . $template;
        if (file_exists($template_file)) {
            // Wrap in try-catch to prevent crashes
            try {
                require $template_file;
                return true;
            } catch (Exception $e) {
                // Log error but don't crash
                $error_msg = $e->getMessage();
                $error_trace = $e->getTraceAsString();
                echo '<!-- get_template_part(' . htmlspecialchars($slug) . ', ' . htmlspecialchars($name) . ') EXCEPTION: ' . htmlspecialchars($error_msg) . ' -->';
                if (function_exists('watchdog')) {
                    watchdog('wp_content', 'Template part @template failed: @error<br>Trace: @trace', array(
                        '@template' => $template,
                        '@error' => $error_msg,
                        '@trace' => $error_trace,
                    ), WATCHDOG_ERROR);
                }
                return false;
            } catch (Error $e) {
                // Catch PHP 7+ fatal errors
                $error_msg = $e->getMessage();
                $error_trace = $e->getTraceAsString();
                echo '<!-- get_template_part(' . htmlspecialchars($slug) . ', ' . htmlspecialchars($name) . ') FATAL ERROR: ' . htmlspecialchars($error_msg) . ' -->';
                if (function_exists('watchdog')) {
                    watchdog('wp_content', 'Template part @template FATAL: @error<br>Trace: @trace', array(
                        '@template' => $template,
                        '@error' => $error_msg,
                        '@trace' => $error_trace,
                    ), WATCHDOG_ERROR);
                }
                return false;
            }
        }
    }

    return false;
}

/**
 * Helper function to get theme info from Backdrop's theme system.
 *
 * @internal
 * @param string $theme_name Theme name to get info for.
 * @return array Theme info array or empty array if not found.
 */
function _wp2bd_get_theme_info($theme_name) {
    static $cache = array();

    if (isset($cache[$theme_name])) {
        return $cache[$theme_name];
    }

    // Get theme data using Backdrop's system
    $themes = list_themes();

    if (isset($themes[$theme_name])) {
        $theme_info = $themes[$theme_name]->info;
        $cache[$theme_name] = $theme_info;
        return $theme_info;
    }

    $cache[$theme_name] = array();
    return array();
}

/**
 * Load header template.
 *
 * Includes the header template for a theme or if a name is specified then a
 * specialised header will be included.
 *
 * For the parameter, if the file is called "header-special.php" then specify
 * "special".
 *
 * @param string $name The name of the specialised header.
 */
if (!function_exists('get_header')) {
  function get_header($name = null) {
    $templates = array();
    $name = (string) $name;
    if ('' !== $name) {
      $templates[] = "header-{$name}.php";
    }

    $templates[] = 'header.php';

    locate_template($templates, true);
  }
}

/**
 * Load footer template.
 *
 * Includes the footer template for a theme or if a name is specified then a
 * specialised footer will be included.
 *
 * For the parameter, if the file is called "footer-special.php" then specify
 * "special".
 *
 * @param string $name The name of the specialised footer.
 */
if (!function_exists('get_footer')) {
  function get_footer($name = null) {
    $templates = array();
    $name = (string) $name;
    if ('' !== $name) {
      $templates[] = "footer-{$name}.php";
    }

    $templates[] = 'footer.php';

    locate_template($templates, true);
  }
}

/**
 * Load sidebar template.
 *
 * Includes the sidebar template for a theme or if a name is specified then a
 * specialised sidebar will be included.
 *
 * For the parameter, if the file is called "sidebar-special.php" then specify
 * "special".
 *
 * @param string $name The name of the specialised sidebar.
 */
if (!function_exists('get_sidebar')) {
  function get_sidebar($name = null) {
    $templates = array();
    $name = (string) $name;
    if ('' !== $name) {
      $templates[] = "sidebar-{$name}.php";
    }

    $templates[] = 'sidebar.php';

    locate_template($templates, true);
  }
}
