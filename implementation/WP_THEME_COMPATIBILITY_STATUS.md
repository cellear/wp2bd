# WP2BD Theme Compatibility Layer - Implementation Status

**Date:** November 24, 2025
**Status:** Active Development
**Focus:** Enabling WordPress themes to run on Backdrop CMS with dynamic switching.

## 1. Overview
The goal is to allow users to select a WordPress theme via the Backdrop admin UI (`/admin/config/content/wp-content`) and have it render correctly. This requires a compatibility layer that handles:
1.  **Dynamic Asset Loading:** Loading the correct CSS and JS for the active theme.
2.  **Function Stubbing:** Providing WordPress API functions that themes rely on.
3.  **Theme Logic:** Handling WordPress-specific template tags and logic (headers, sidebars, etc.).

## 2. Recent Accomplishments

### Dynamic Theme Switching
*   **CSS Loading:** Moved from hardcoded `wp.info` entries to a dynamic system.
    *   **Mechanism:** `wp_content_settings_form_submit` generates `themes/wp/active-theme.css` containing an `@import` to the selected theme's stylesheet.
    *   **Benefit:** Allows switching themes without code changes or symlinks.
*   **JavaScript Loading:**
    *   Currently hardcoded in `wp.info` for Twenty Seventeen (parallax support).
    *   **Roadmap:** Needs to be made dynamic similar to CSS (e.g., generating an `active-theme.js` or dynamically building the info array).

### Stub Functions & API Support
*   **Header Image API:** Implemented generic `get_header_image()` and `get_custom_header()` in `stubs.php`.
    *   **Logic:** Detects header images in standard paths (`assets/images/`, `images/`, `img/`) to avoid theme-specific hardcoding.
*   **Body Classes:** Enhanced `get_body_class()` in `content-display.php`.
    *   Added `has-header-image` and `has-header-video` classes.
    *   Added `twentyseventeen-front-page` class (via JS) to support full-height headers.
*   **Stub Generator:** Created `scripts/generate_wp_stubs.php` to parse WordPress core and generate stubs.
    *   **Status:** Experimental. Produces syntax errors with complex signatures. Currently relying on manual stubs.

### Twenty Seventeen Restoration
*   **Header & Parallax:** Fully restored.
    *   Header image displays at full height (100vh) on front page.
    *   Parallax effect works (header shrinks on scroll).
    *   **Solution:** Combination of generic PHP detection and a helper script (`themes/wp/js/header-class.js`) to add necessary body classes that PHP missed due to timing issues.
*   **Sidebar:** Known issue - sidebar is not in the correct position.

## 3. Challenges & Workarounds

### Timing & Initialization
*   **Issue:** `get_header_image()` returns empty when `body_class()` is called in the template, likely because the theme path isn't fully initialized or the context is wrong.
*   **Workaround:** Created `themes/wp/js/header-class.js` to detect the header image in the DOM and add the `has-header-image` class to the `<body>` tag client-side.

### Asset Management
*   **Issue:** WordPress themes use `wp_enqueue_script` / `wp_enqueue_style`. These hooks are present but not fully integrated with Backdrop's asset pipeline.
*   **Workaround:**
    *   **CSS:** Generated `active-theme.css`.
    *   **JS:** Hardcoded in `wp.info` (temporary).

### Stub Generation
*   **Issue:** Regex-based parsing of WordPress functions is fragile. `auto-stubs.php` often contains syntax errors (duplicate parameters, invalid default values).
*   **Plan:** Move to a proper PHP parser (e.g., `nikic/php-parser`) or improve the regex logic to handle complex signatures safely.

## 4. Roadmap & Future Work

### Immediate Priorities
1.  **Dynamic JavaScript Loading:** Implement a system to load theme-specific JS files dynamically, replacing the hardcoded list in `wp.info`.
2.  **Sidebar Fix:** Debug and fix the sidebar positioning in Twenty Seventeen.
3.  **Other Themes:** Test and fix Twenty Sixteen, Twenty Twelve, etc. (currently not displaying correctly).

### Strategic Improvements
1.  **Telemetry System:** Implement the proposed Stub Telemetry System (`IMPLEMENTATION/IMPLEMENTATION-stub-telemetry-system.md`) to log which stubs are called. This will guide which functions need real implementations vs. empty stubs.
2.  **Robust Stub Generator:** Rewrite the generator to be reliable, allowing us to stub thousands of WP functions automatically.
3.  **Asset Bridge:** Build a better bridge between `wp_enqueue_scripts` and Backdrop's `drupal_add_js`/`drupal_add_css` to avoid workarounds.

## 5. Key Files
*   `modules/wp_content/wp_content.module`: Main module logic, settings form, CSS generation.
*   `themes/wp/functions/stubs.php`: Manual function stubs.
*   `themes/wp/functions/content-display.php`: Content rendering and body class logic.
*   `themes/wp/js/header-class.js`: Helper script for body classes.
*   `themes/wp/active-theme.css`: Generated CSS file (do not edit).
