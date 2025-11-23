## [1123j] 2025-11-23 - THEME-HARDCODE: Change hardcoded theme to twentyfifteen for testing

## [1123i] 2025-11-23 - VERSION-TRACKING: Auto-read version from CHANGELOG for footer indicator

## [1123h] 2025-11-23 - COMPAT-FIX: Add get_object_taxonomies() to complete home page rendering

## [1123g] 2025-11-23 - COMPAT-FIX: Fix home page rendering - add locate_template(), is_attachment(), fix template paths for older themes

## [1123f] 2025-11-23 - THEME-HARDCODE: Hardcode theme to twentyfourteen for testing (reverted dynamic theme loading)

## [1123e] 2025-11-23 - THEME-SWITCHING: Fix theme switching to actually work - removed hardcoded theme, added missing functions (wp_title, is_paged, $pagenow)

## [1123d] 2025-11-23 - VERSION-TRACKING: Add CHANGELOG and visible version indicator

## [1123c] 2025-11-23 - COMPAT-FIX: Add $pagenow global to WordPress compatibility layer
Fixed PHP warnings about undefined $pagenow variable in Twenty Fourteen theme functions.

## [1123b] 2025-11-23 - THEME-DEFAULT: Change default WordPress theme to Twenty Sixteen
Updated default theme from Twenty Seventeen to Twenty Sixteen to avoid distracting parallax header.

## [1123a] 2025-11-23 - ADMIN-UI: Add admin interface for WordPress theme switching
Created settings page at admin/config/content/wp-content with dropdown selector for all installed themes.

## [1122a] 2025-11-22 - MULTI-THEME: Merge multi-theme refactor into main
Merged origin/claude/multi-theme-refactor-014RDG3PJ9phase2 containing Phases 2-4 of multi-theme support.
