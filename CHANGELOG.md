# WP2BD Changelog

## [1123d] 2025-11-23 - VERSION-TRACKING: Add CHANGELOG and visible version indicator
Added CHANGELOG.md and footer version indicator to track what's deployed. Tag format: `[date+letter] AREA: description`

## [1123c] 2025-11-23 - COMPAT-FIX: Add $pagenow global to WordPress compatibility layer
Fixed PHP warnings about undefined $pagenow variable in Twenty Fourteen theme functions.

## [1123b] 2025-11-23 - THEME-DEFAULT: Change default WordPress theme to Twenty Sixteen
Updated default theme from Twenty Seventeen to Twenty Sixteen to avoid distracting parallax header.

## [1123a] 2025-11-23 - ADMIN-UI: Add admin interface for WordPress theme switching
Created settings page at admin/config/content/wp-content with dropdown selector for all installed themes.

## [1122a] 2025-11-22 - MULTI-THEME: Merge multi-theme refactor into main
Merged origin/claude/multi-theme-refactor-014RDG3PJ9phase2 containing Phases 2-4 of multi-theme support.
