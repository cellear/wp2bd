<?php
/**
 * WordPress Script and Style Enqueue System
 * 
 * Implements wp_enqueue_style(), wp_enqueue_script(), and related functions
 * for registering and outputting stylesheets and scripts.
 */

// Global arrays to store registered and enqueued assets
global $wp_styles, $wp_scripts;
$wp_styles = array();
$wp_scripts = array();

/**
 * Register a CSS stylesheet.
 */
if (!function_exists('wp_register_style')) {
    function wp_register_style($handle, $src, $deps = array(), $ver = false, $media = 'all') {
        global $wp_styles;
        
        $wp_styles[$handle] = array(
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
            'media' => $media,
            'registered' => true,
            'enqueued' => false,
        );
    }
}

/**
 * Enqueue a CSS stylesheet.
 */
if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all') {
        global $wp_styles;

        // If not registered yet, register it
        if (!isset($wp_styles[$handle])) {
            wp_register_style($handle, $src, $deps, $ver, $media);
        }

        // Mark as enqueued
        $wp_styles[$handle]['enqueued'] = true;

        // DEBUG
        echo "<!-- wp_enqueue_style('$handle', '$src') called -->\n";
    }
}

/**
 * Print all enqueued styles (called by wp_head()).
 */
if (!function_exists('wp_print_styles')) {
    function wp_print_styles() {
        global $wp_styles;

        // DEBUG
        echo "<!-- wp_print_styles() called. Styles registered: " . (empty($wp_styles) ? "NONE" : count($wp_styles)) . " -->\n";

        if (empty($wp_styles)) {
            return;
        }

        foreach ($wp_styles as $handle => $style) {
            echo "<!-- Checking style '$handle': enqueued=" . ($style['enqueued'] ? 'YES' : 'NO') . " -->\n";
            if ($style['enqueued']) {
                $href = $style['src'];

                // Add version parameter if specified
                if ($style['ver']) {
                    $href .= (strpos($href, '?') === false ? '?' : '&') . 'ver=' . $style['ver'];
                }

                echo "<link rel='stylesheet' id='" . esc_attr($handle) . "' href='" . esc_attr($href) . "' type='text/css' media='" . esc_attr($style['media']) . "' />\n";
            }
        }
    }
}

/**
 * Register a JavaScript file.
 */
if (!function_exists('wp_register_script')) {
    function wp_register_script($handle, $src, $deps = array(), $ver = false, $in_footer = false) {
        global $wp_scripts;
        
        $wp_scripts[$handle] = array(
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
            'in_footer' => $in_footer,
            'registered' => true,
            'enqueued' => false,
        );
    }
}

/**
 * Enqueue a JavaScript file.
 */
if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) {
        global $wp_scripts;
        
        // If not registered yet, register it
        if (!isset($wp_scripts[$handle])) {
            wp_register_script($handle, $src, $deps, $ver, $in_footer);
        }
        
        // Mark as enqueued
        $wp_scripts[$handle]['enqueued'] = true;
    }
}

/**
 * Print all enqueued scripts (called by wp_head() or wp_footer()).
 */
if (!function_exists('wp_print_scripts')) {
    function wp_print_scripts($in_footer = false) {
        global $wp_scripts;
        
        if (empty($wp_scripts)) {
            return;
        }
        
        foreach ($wp_scripts as $handle => $script) {
            // Only print scripts meant for this location
            if ($script['enqueued'] && ($script['in_footer'] == $in_footer)) {
                $src = $script['src'];
                
                // Add version parameter if specified
                if ($script['ver']) {
                    $src .= (strpos($src, '?') === false ? '?' : '&') . 'ver=' . $script['ver'];
                }
                
                echo "<script type='text/javascript' src='" . esc_attr($src) . "'></script>\n";
            }
        }
    }
}
