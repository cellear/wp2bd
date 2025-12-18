<?php
/**
 * WordPress Script and Style Enqueue System
 * 
 * Implements wp_enqueue_style(), wp_enqueue_script(), and related functions
 * for registering and outputting stylesheets and scripts.
 */

/**
 * Simple WP_Styles class to mimic WordPress behavior.
 * Themes like Twenty Twelve call $wp_styles->add_data().
 */
if (!class_exists('WP_Styles')) {
    class WP_Styles {
        public $registered = array();
        public $queue = array();
        public $done = array();
        
        public function add($handle, $src, $deps = array(), $ver = false, $media = 'all') {
            $this->registered[$handle] = (object) array(
                'handle' => $handle,
                'src' => $src,
                'deps' => $deps,
                'ver' => $ver,
                'args' => $media,
                'extra' => array(),
            );
        }
        
        public function add_data($handle, $key, $value) {
            if (isset($this->registered[$handle])) {
                $this->registered[$handle]->extra[$key] = $value;
                return true;
            }
            return false;
        }
        
        public function enqueue($handle) {
            if (!in_array($handle, $this->queue)) {
                $this->queue[] = $handle;
            }
        }
        
        public function dequeue($handle) {
            $this->queue = array_diff($this->queue, array($handle));
        }
    }
}

/**
 * Simple WP_Scripts class to mimic WordPress behavior.
 */
if (!class_exists('WP_Scripts')) {
    class WP_Scripts {
        public $registered = array();
        public $queue = array();
        public $done = array();
        public $localize = array();
        
        public function add($handle, $src, $deps = array(), $ver = false, $in_footer = false) {
            $this->registered[$handle] = (object) array(
                'handle' => $handle,
                'src' => $src,
                'deps' => $deps,
                'ver' => $ver,
                'args' => $in_footer,
                'extra' => array(),
            );
        }
        
        public function add_data($handle, $key, $value) {
            if (isset($this->registered[$handle])) {
                $this->registered[$handle]->extra[$key] = $value;
                return true;
            }
            return false;
        }
        
        public function localize($handle, $object_name, $l10n) {
            $this->localize[$handle] = array(
                'name' => $object_name,
                'data' => $l10n,
            );
            return true;
        }
        
        public function enqueue($handle) {
            if (!in_array($handle, $this->queue)) {
                $this->queue[] = $handle;
            }
        }
        
        public function dequeue($handle) {
            $this->queue = array_diff($this->queue, array($handle));
        }
    }
}

// Global objects to store registered and enqueued assets
global $wp_styles, $wp_scripts;
if (!isset($wp_styles) || !($wp_styles instanceof WP_Styles)) {
    $wp_styles = new WP_Styles();
}
if (!isset($wp_scripts) || !($wp_scripts instanceof WP_Scripts)) {
    $wp_scripts = new WP_Scripts();
}

/**
 * Register a CSS stylesheet.
 */
if (!function_exists('wp_register_style')) {
    function wp_register_style($handle, $src, $deps = array(), $ver = false, $media = 'all') {
        global $wp_styles;
        $wp_styles->add($handle, $src, $deps, $ver, $media);
    }
}

/**
 * Enqueue a CSS stylesheet.
 */
if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all') {
        global $wp_styles;

        if (function_exists('watchdog')) {
          watchdog('wp4bd_debug', 'wp_enqueue_style called: @handle - @src', array('@handle' => $handle, '@src' => $src), WATCHDOG_DEBUG);
        }

        // If not registered yet, register it
        if (!isset($wp_styles->registered[$handle])) {
            wp_register_style($handle, $src, $deps, $ver, $media);
        }

        // Mark as enqueued
        $wp_styles->enqueue($handle);

        // Add to Backdrop's CSS system
        if (function_exists('backdrop_add_css') && !empty($src)) {
            $options = array();
            if ($media !== 'all') {
                $options['media'] = $media;
            }
            // If src is a full URL (starts with http:// or https://), mark as external
            if (preg_match('#^https?://#', $src)) {
                $options['type'] = 'external';
            }
            backdrop_add_css($src, $options);
        }
    }
}

/**
 * Print all enqueued styles (called by wp_head()).
 */
if (!function_exists('wp_print_styles')) {
    function wp_print_styles() {
        global $wp_styles;
        
        // Ensure wp_styles is initialized
        if (!isset($wp_styles) || !($wp_styles instanceof WP_Styles)) {
            $wp_styles = new WP_Styles();
        }

        if (empty($wp_styles->queue)) {
            return;
        }

        foreach ($wp_styles->queue as $handle) {
            if (!isset($wp_styles->registered[$handle])) {
                continue;
            }
            $style = $wp_styles->registered[$handle];
            $href = $style->src;

            // Add version parameter if specified
            if ($style->ver) {
                $href .= (strpos($href, '?') === false ? '?' : '&') . 'ver=' . $style->ver;
            }

            $media = isset($style->args) ? $style->args : 'all';
            echo "<link rel='stylesheet' id='" . esc_attr($handle) . "-css' href='" . esc_attr($href) . "' type='text/css' media='" . esc_attr($media) . "' />\n";
        }
    }
}

/**
 * Register a JavaScript file.
 */
if (!function_exists('wp_register_script')) {
    function wp_register_script($handle, $src, $deps = array(), $ver = false, $in_footer = false) {
        global $wp_scripts;
        $wp_scripts->add($handle, $src, $deps, $ver, $in_footer);
    }
}

/**
 * Enqueue a JavaScript file.
 */
if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) {
        global $wp_scripts;
        
        // If not registered yet, register it
        if (!isset($wp_scripts->registered[$handle])) {
            wp_register_script($handle, $src, $deps, $ver, $in_footer);
        }
        
        // Mark as enqueued
        $wp_scripts->enqueue($handle);

        // Add to Backdrop's JS system
        if (function_exists('backdrop_add_js') && !empty($src)) {
            $options = array();
            if ($in_footer) {
                $options['scope'] = 'footer';
            }
            backdrop_add_js($src, $options);
        }
    }
}

/**
 * Print all enqueued scripts (called by wp_head() or wp_footer()).
 */
if (!function_exists('wp_print_scripts')) {
    function wp_print_scripts($in_footer = false) {
        global $wp_scripts;
        
        if (empty($wp_scripts->queue)) {
            return;
        }
        
        foreach ($wp_scripts->queue as $handle) {
            if (!isset($wp_scripts->registered[$handle])) {
                continue;
            }
            $script = $wp_scripts->registered[$handle];
            
            // Only print scripts meant for this location
            $script_in_footer = isset($script->args) ? $script->args : false;
            if ($script_in_footer == $in_footer) {
                $src = $script->src;
                
                // Add version parameter if specified
                if ($script->ver) {
                    $src .= (strpos($src, '?') === false ? '?' : '&') . 'ver=' . $script->ver;
                }
                
                // Print localized data if any
                if (isset($wp_scripts->localize[$handle])) {
                    $l10n = $wp_scripts->localize[$handle];
                    echo "<script type='text/javascript'>\n";
                    echo "/* <![CDATA[ */\n";
                    echo "var " . $l10n['name'] . " = " . json_encode($l10n['data']) . ";\n";
                    echo "/* ]]> */\n";
                    echo "</script>\n";
                }
                
                echo "<script type='text/javascript' src='" . esc_attr($src) . "'></script>\n";
            }
        }
    }
}

/**
 * Localize a script.
 */
if (!function_exists('wp_localize_script')) {
    function wp_localize_script($handle, $object_name, $l10n) {
        global $wp_scripts;
        return $wp_scripts->localize($handle, $object_name, $l10n);
    }
}
