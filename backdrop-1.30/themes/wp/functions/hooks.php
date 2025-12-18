<?php
/**
 * WordPress Hook System Implementation for WP2BD
 *
 * Implements the complete WordPress plugin API including:
 * - Action hooks (add_action, do_action, remove_action)
 * - Filter hooks (add_filter, apply_filters, remove_filter)
 * - Core template hooks (wp_head, wp_footer)
 *
 * The hook system allows themes and plugins to modify behavior at specific
 * points during execution without modifying core files.
 *
 * @package WP2BD
 * @subpackage Hooks
 * @version 1.0
 */

// Initialize WordPress hook globals
if (!isset($GLOBALS['wp_filter'])) {
    $GLOBALS['wp_filter'] = array();
}
if (!isset($GLOBALS['wp_actions'])) {
    $GLOBALS['wp_actions'] = array();
}
if (!isset($GLOBALS['wp_current_filter'])) {
    $GLOBALS['wp_current_filter'] = array();
}


// Initialize global hook storage
global $wp_filter, $wp_actions, $wp_current_filter;

if (!isset($wp_filter)) {
    $wp_filter = array();
}

if (!isset($wp_actions)) {
    $wp_actions = array();
}

if (!isset($wp_current_filter)) {
    $wp_current_filter = array();
}

/**
 * Add a callback function to a filter hook.
 *
 * WordPress filters allow you to modify data at specific points during
 * execution. Filters must return a value (modified or not).
 *
 * Example:
 *   function my_title_filter($title) {
 *       return strtoupper($title);
 *   }
 *   add_filter('the_title', 'my_title_filter');
 *
 * @param string   $hook          The name of the filter to hook into.
 * @param callable $callback      The callback function to execute.
 * @param int      $priority      Optional. Execution order (lower = earlier). Default 10.
 * @param int      $accepted_args Optional. Number of arguments callback accepts. Default 1.
 * @return bool Always returns true.
 */
function add_filter($hook, $callback, $priority = 10, $accepted_args = 1)
{

    // wp_filter is initialized globally above


    // Validate callback is callable
    if (!is_callable($callback)) {
        return false;
    }

    // Initialize hook array if not exists
    if (!isset($GLOBALS['wp_filter'][$hook])) {
        $GLOBALS['wp_filter'][$hook] = array();
    }

    // Initialize priority array if not exists
    if (!isset($GLOBALS['wp_filter'][$hook][$priority])) {
        $GLOBALS['wp_filter'][$hook][$priority] = array();
    }

    // Generate unique identifier for this callback
    $callback_id = _wp_filter_build_unique_id($hook, $callback, $priority);

    // Store callback with metadata
    $GLOBALS['wp_filter'][$hook][$priority][$callback_id] = array(
        'function' => $callback,
        'accepted_args' => $accepted_args
    );


    return true;
}

/**
 * Apply filters to a value.
 *
 * Calls all callback functions registered to the filter hook, passing the
 * value through each one and returning the final modified value.
 *
 * Example:
 *   $title = apply_filters('the_title', $title, $post_id);
 *
 * @param string $hook  The name of the filter hook.
 * @param mixed  $value The value to filter.
 * @param mixed  ...$args Additional arguments to pass to callbacks.
 * @return mixed The filtered value after all callbacks have been applied.
 */
function apply_filters($hook, $value, ...$args)
{
    global $wp_filter, $wp_current_filter;

    // Track current filter for nested calls
    $wp_current_filter[] = $hook;

    // If no filters registered, return original value
    if (!isset($wp_filter[$hook])) {
        array_pop($wp_current_filter);
        return $value;
    }

    // Sort by priority (ascending)
    ksort($wp_filter[$hook]);

    // Build argument array (value first, then additional args)
    $all_args = array_merge(array($value), $args);

    // Execute each callback at each priority level
    foreach ($wp_filter[$hook] as $priority => $callbacks) {
        foreach ($callbacks as $callback_id => $callback_data) {
            $callback = $callback_data['function'];
            $accepted_args = $callback_data['accepted_args'];

            // Slice arguments based on accepted_args
            $callback_args = array_slice($all_args, 0, $accepted_args);

            // Call the callback and update value
            $value = call_user_func_array($callback, $callback_args);

            // Update first element of all_args for next callback
            $all_args[0] = $value;
        }
    }

    // Remove from current filter stack
    array_pop($wp_current_filter);

    return $value;
}

/**
 * Remove a callback from a filter hook.
 *
 * Removes a previously added filter callback. The callback and priority
 * must match exactly what was used when adding the filter.
 *
 * Example:
 *   remove_filter('the_title', 'my_title_filter', 10);
 *
 * @param string   $hook     The filter hook name.
 * @param callable $callback The callback to remove.
 * @param int      $priority Optional. The priority level. Default 10.
 * @return bool True if callback was removed, false otherwise.
 */
function remove_filter($hook, $callback, $priority = 10)
{
    global $wp_filter;

    // Check if hook exists
    if (!isset($wp_filter[$hook])) {
        return false;
    }

    // Check if priority exists
    if (!isset($wp_filter[$hook][$priority])) {
        return false;
    }

    // Generate callback ID
    $callback_id = _wp_filter_build_unique_id($hook, $callback, $priority);

    // Check if callback exists and remove it
    if (isset($wp_filter[$hook][$priority][$callback_id])) {
        unset($wp_filter[$hook][$priority][$callback_id]);

        // Clean up empty arrays
        if (empty($wp_filter[$hook][$priority])) {
            unset($wp_filter[$hook][$priority]);
        }

        if (empty($wp_filter[$hook])) {
            unset($wp_filter[$hook]);
        }

        return true;
    }

    return false;
}

/**
 * Add a callback function to an action hook.
 *
 * Actions are hooks that execute code at specific points without returning
 * a value. They're used for side effects like outputting HTML or modifying
 * database records.
 *
 * Note: add_action() is actually just an alias for add_filter() since both
 * use the same internal storage mechanism.
 *
 * Example:
 *   function my_header_action() {
 *       echo '<meta name="theme" content="My Theme">';
 *   }
 *   add_action('wp_head', 'my_header_action');
 *
 * @param string   $hook          The name of the action to hook into.
 * @param callable $callback      The callback function to execute.
 * @param int      $priority      Optional. Execution order (lower = earlier). Default 10.
 * @param int      $accepted_args Optional. Number of arguments callback accepts. Default 1.
 * @return bool Always returns true.
 */
function add_action($hook, $callback, $priority = 10, $accepted_args = 1)
{
    return add_filter($hook, $callback, $priority, $accepted_args);
}

/**
 * Execute all callbacks registered to an action hook.
 *
 * Fires all functions hooked to the specified action. Unlike apply_filters(),
 * do_action() does not return a value - it's used purely for side effects.
 *
 * Example:
 *   do_action('wp_head');
 *   do_action('save_post', $post_id, $post, $update);
 *
 * @param string $hook The name of the action to execute.
 * @param mixed  ...$args Optional arguments to pass to callbacks.
 * @return void
 */
function do_action($hook, ...$args)
{

    // Track how many times this action has fired
    if (!isset($wp_actions[$hook])) {
        $wp_actions[$hook] = 1;
    } else {
        $wp_actions[$hook]++;
    }

    // Track current action for nested calls
    $GLOBALS['wp_current_filter'][] = $hook;

    // Sort by priority (ascending) and execute callbacks
    if (isset($GLOBALS['wp_filter'][$hook]) && is_array($GLOBALS['wp_filter'][$hook])) {
        ksort($GLOBALS['wp_filter'][$hook]);

        // Execute each callback at each priority level
        foreach ($GLOBALS['wp_filter'][$hook] as $priority => $callbacks) {
            foreach ($callbacks as $callback_id => $callback_data) {
                $callback = $callback_data['function'];
                $accepted_args = $callback_data['accepted_args'];

                // Slice arguments based on accepted_args
                $callback_args = array_slice($args, 0, $accepted_args);

                // Call the callback (ignore return value for actions)
                call_user_func_array($callback, $callback_args);
            }
        }
    }

    // Remove from current action stack
    array_pop($GLOBALS['wp_current_filter']);
}

/**
 * Remove a callback from an action hook.
 *
 * Removes a previously added action callback. The callback and priority
 * must match exactly what was used when adding the action.
 *
 * Note: remove_action() is actually just an alias for remove_filter() since
 * both use the same internal storage mechanism.
 *
 * Example:
 *   remove_action('wp_head', 'my_header_action', 10);
 *
 * @param string   $hook     The action hook name.
 * @param callable $callback The callback to remove.
 * @param int      $priority Optional. The priority level. Default 10.
 * @return bool True if callback was removed, false otherwise.
 */
function remove_action($hook, $callback, $priority = 10)
{
    return remove_filter($hook, $callback, $priority);
}

/**
 * Fire the 'wp_head' action hook.
 *
 * This hook is called in the <head> section of HTML and allows themes and
 * plugins to inject additional meta tags, scripts, styles, and other content.
 *
 * This is one of the most important hooks in WordPress themes and should be
 * called in header.php just before the closing </head> tag.
 *
 * Example usage in theme header.php:
 *   <head>
 *       <meta charset="<?php bloginfo('charset'); ?>">
 *       <?php wp_head(); ?>
 *   </head>
 *
 * Common uses:
 * - Enqueue styles and scripts
 * - Add meta tags (SEO, social media)
 * - Add analytics tracking code
 * - Custom theme/plugin functionality
 *
 * @return void
 */
function wp_head()
{
    /**
     * Fires in the <head> section of the HTML document.
     *
     * This hook allows themes and plugins to inject content into the
     * <head> section, such as:
     * - Custom meta tags
     * - Inline styles or scripts
     * - Analytics tracking codes
     * - SEO optimization tags
     *
     * @since WordPress 1.5.0
     */

    // Buffer output to capture meta tags, inline styles, etc.
    ob_start();

    // Fire wp_enqueue_scripts before wp_head (WordPress standard)
    if (function_exists('watchdog')) {
    }
    do_action('wp_enqueue_scripts');

    do_action('wp_head');

    // Note: wp_print_styles and wp_print_scripts are NOT called here anymore.
    // They are handled by wp_enqueue_style/script calling drupal_add_css/js directly.

    $output = ob_get_clean();

    // Inject captured content into Backdrop's head
    if (!empty($output)) {
        $element = array(
            '#type' => 'markup',
            '#markup' => $output,
        );
        backdrop_add_html_head($element, 'wp_head_output');
    }
}

/**
 * Fire the 'wp_footer' action hook.
 *
 * This hook is called just before the closing </body> tag and allows themes
 * and plugins to inject additional scripts and content at the end of the page.
 *
 * This is an essential hook in WordPress themes and should be called in
 * footer.php just before the closing </body> tag.
 *
 * Example usage in theme footer.php:
 *   <?php wp_footer(); ?>
 *   </body>
 *   </html>
 *
 * Common uses:
 * - Enqueue deferred JavaScript
 * - Add tracking scripts (analytics, pixels)
 * - Render hidden elements (modals, SVG sprites)
 * - Output JSON-LD structured data
 *
 * @return void
 */
function wp_footer()
{
    /**
     * Fires just before the closing </body> tag in the HTML document.
     *
     * This hook allows themes and plugins to inject content at the end
     * of the page body, such as:
     * - Deferred JavaScript
     * - Analytics and tracking codes
     * - SVG icon sprites
     * - Accessibility overlays
     * - Chat widgets
     *
     * @since WordPress 1.5.1
     */
    do_action('wp_footer');

    // Note: wp_print_scripts is NOT called here anymore.
    // Handled by wp_enqueue_script calling drupal_add_js directly.
}

/**
 * Generate a unique identifier for a callback.
 *
 * This internal function creates a unique ID for each callback based on its
 * type (function, method, or closure) so we can track and remove specific
 * callbacks later.
 *
 * @internal
 * @param string   $hook     The hook name.
 * @param callable $callback The callback function.
 * @param int      $priority The priority level.
 * @return string Unique identifier for this callback.
 */
function _wp_filter_build_unique_id($hook, $callback, $priority)
{
    if (is_string($callback)) {
        // Simple function name
        return $callback;
    }

    if (is_object($callback)) {
        // Closure or invokable object
        return spl_object_hash($callback);
    }

    if (is_array($callback)) {
        // Object method: array($object, 'method')
        // Static method: array('ClassName', 'method')
        if (is_object($callback[0])) {
            // Object method
            return spl_object_hash($callback[0]) . '::' . $callback[1];
        } else {
            // Static method
            return $callback[0] . '::' . $callback[1];
        }
    }

    // Fallback - should not reach here if callback is valid
    return md5(serialize($callback));
}

/**
 * Check if any filter has been registered for a hook.
 *
 * @param string        $hook             The filter hook name.
 * @param callable|bool $callback_to_check Optional. Specific callback to check. Default false.
 * @return bool|int False if no callbacks registered, or priority of callback if checking specific one.
 */
function has_filter($hook, $callback_to_check = false)
{
    global $wp_filter;

    if (!isset($wp_filter[$hook])) {
        return false;
    }

    if ($callback_to_check === false) {
        // Just checking if hook has any callbacks
        return true;
    }

    // Check for specific callback
    $callback_id = _wp_filter_build_unique_id($hook, $callback_to_check, 0);

    foreach ($wp_filter[$hook] as $priority => $callbacks) {
        if (isset($callbacks[$callback_id])) {
            return $priority;
        }
    }

    return false;
}

/**
 * Check if any action has been registered for a hook.
 *
 * @param string        $hook             The action hook name.
 * @param callable|bool $callback_to_check Optional. Specific callback to check. Default false.
 * @return bool|int False if no callbacks registered, or priority of callback if checking specific one.
 */
function has_action($hook, $callback_to_check = false)
{
    return has_filter($hook, $callback_to_check);
}

/**
 * Retrieve the number of times an action has been fired.
 *
 * @param string $hook The action hook name.
 * @return int The number of times this action has fired.
 */
function did_action($hook)
{
    global $wp_actions;

    if (!isset($wp_actions[$hook])) {
        return 0;
    }

    return $wp_actions[$hook];
}

/**
 * Retrieve the name of the current filter or action.
 *
 * @return string|false The current filter/action name, or false if none.
 */
function current_filter()
{
    global $wp_current_filter;

    if (empty($wp_current_filter)) {
        return false;
    }

    return end($wp_current_filter);
}

/**
 * Retrieve the name of the current action.
 *
 * @return string|false The current action name, or false if none.
 */
function current_action()
{
    return current_filter();
}

/**
 * Check if a filter is currently being processed.
 *
 * @param string|null $hook Optional. Filter to check. Default null (checks if any filter is running).
 * @return bool True if the filter is currently being processed.
 */
function doing_filter($hook = null)
{
    global $wp_current_filter;

    if ($hook === null) {
        return !empty($wp_current_filter);
    }

    return in_array($hook, $wp_current_filter);
}

/**
 * Check if an action is currently being processed.
 *
 * @param string|null $hook Optional. Action to check. Default null (checks if any action is running).
 * @return bool True if the action is currently being processed.
 */
function doing_action($hook = null)
{
    return doing_filter($hook);
}
