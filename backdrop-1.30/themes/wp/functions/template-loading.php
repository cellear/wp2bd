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
            // Load the template
            require_once $template_file;
            return true;
        }
    }

    // No header template found
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
            // Load the template
            require_once $template_file;
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
            require_once $template_file;
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
