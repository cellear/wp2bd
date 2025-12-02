# WP4BD Development Handoff

**Author:** Claude (Anthropic's AI Assistant, Opus 4 model)  
**Date:** December 1-2, 2024  
**Session Context:** Cursor IDE pair programming with Luke McCormick

---

## Executive Summary

This document describes the architectural breakthrough achieved in getting WordPress themes to render correctly within Backdrop CMS. The key insight was to **let WordPress themes control their entire HTML structure** rather than trying to wrap WordPress output inside Backdrop's template system.

---

## The Problem We Solved

WordPress themes are designed to output complete HTML documents:
```
<!DOCTYPE html>
<html>
<head>...</head>
<body>
  [header.php content]
  [template content]
  [sidebar.php content]  
  [footer.php content]
</body>
</html>
```

Previously, WP4BD tried to:
1. Have Backdrop output its own `<!DOCTYPE><html><head><body>` structure
2. Render WordPress content as "blocks" inside that structure
3. Strip WordPress's HTML structure to avoid duplicates

This caused numerous issues:
- Duplicate/conflicting wrapper divs
- CSS layout problems (sidebars appearing in wrong places)
- Complex theme-specific workarounds needed
- Sidebars not rendering at all for some themes

---

## The Architectural Solution

### Core Principle: WordPress Owns the HTML

Instead of wrapping WordPress inside Backdrop, we now:

1. **Let WordPress output its complete HTML structure** via `header.php`, template files, and `footer.php`
2. **Inject Backdrop's assets** (CSS, JS, body classes) into WordPress's output using regex
3. **Bypass Backdrop's block/layout system** for WordPress page rendering

### Implementation Details

#### 1. Minimal `page.tpl.php`

The Backdrop theme's `page.tpl.php` now directly runs the WordPress template:

```php
// Load WordPress compatibility layer
require_once BACKDROP_ROOT . '/themes/wp/template.php';

// Set up query and run WordPress template
_wp_content_setup_query();
$template = _wp_content_get_template();
include $template;

// Inject Backdrop's assets into WordPress output
$output = preg_replace('/<\/head>/i', $backdrop_head . '</head>', $output);
$output = preg_replace('/<\/body>/i', $backdrop_footer . '</body>', $output);
```

#### 2. Template Loading Changes

Changed `require_once` to `require` for template files:
- `get_header()` - loads `header.php` 
- `get_footer()` - loads `footer.php`
- `get_sidebar()` - loads `sidebar.php` or `sidebar-{name}.php`

This is critical because WordPress expects these templates to execute on every page load, not just once.

#### 3. Asset Injection

Backdrop's CSS/JS are injected into WordPress's HTML:
- `backdrop_get_css()` and `backdrop_get_js()` inserted before `</head>`
- `backdrop_get_js('footer')` inserted before `</body>`
- Backdrop body classes merged with WordPress's `<body>` tag

---

## Functions Implemented This Session

### Query Functions
- `get_queried_object_id()` - Get ID of current queried object
- `get_queried_object()` - Get current queried object

### Post Navigation
- `get_adjacent_post()` - Get previous/next post from database
- `get_previous_post()` / `get_next_post()` - Convenience wrappers
- `previous_post_link()` / `next_post_link()` - Display navigation links
- `get_previous_post_link()` / `get_next_post_link()` / `get_adjacent_post_link()`

### Widget System Simplification
- Removed duplicate-rendering prevention from `dynamic_sidebar()` 
- This was causing widgets to not render when called from the new single-path architecture

---

## Validated Themes

All five "Twenty-Something" themes now work correctly:

| Theme | Sidebar Location | Special Notes |
|-------|-----------------|---------------|
| Twenty Thirteen | Footer (inside `#colophon`) | Uses absolute positioning |
| Twenty Fourteen | Left side | Calls `get_sidebar('content-bottom')` + `get_sidebar()` |
| Twenty Fifteen | Slides in from left | Sidebar in `header.php` |
| Twenty Sixteen | Right side | Multiple sidebar areas |
| Twenty Seventeen | Right side | Parallax header working |

---

## Key Files Modified

### Core Architecture
- `themes/wp/templates/page.tpl.php` - Now runs WordPress directly, injects Backdrop assets
- `modules/wp_content/wp_content.module` - `_wp_content_render_full_page()` updated (though now bypassed)

### Template Loading
- `themes/wp/functions/template-loading.php` - Changed `require_once` to `require` for header/footer/sidebar

### Widget System  
- `themes/wp/functions/widgets.php` - Simplified `dynamic_sidebar()`, removed duplicate prevention

### New Functions
- `themes/wp/functions/utilities.php` - Added query and navigation functions

---

## What Still Uses the Old Block System

The old block-based rendering (`wordpress_header`, `wordpress_content`, `wordpress_sidebar`, `wordpress_footer` blocks) still exists in `wp_content.module` but is **not used** when the `wp` theme is active. The new `page.tpl.php` bypasses blocks entirely.

These old blocks could be removed in a future cleanup, but leaving them doesn't cause harm.

---

## Known Limitations

1. **Single theme at a time** - Only one WordPress theme can be active
2. **Widget content is generated** - Widgets show Backdrop content (recent posts, categories, etc.) not WordPress widget configuration
3. **No WordPress admin** - Theme customization must be done via code or Backdrop admin

---

## For Future AI Assistants

### Understanding the Codebase

1. Read `/README.md` for project overview
2. Read this file for recent architectural decisions
3. Key directories:
   - `themes/wp/` - Backdrop theme that wraps WordPress themes
   - `themes/wp/functions/` - WordPress function implementations
   - `themes/wp/wp-content/themes/` - Actual WordPress themes
   - `modules/wp_content/` - Backdrop module for configuration

### Testing Changes

```bash
# Switch themes
ddev exec "drush config-set wp_content.settings active_theme twentyseventeen -y"

# Clear cache
ddev bee cc

# Test via curl
curl -s -k "https://wp4bd-test.ddev.site/" | grep -i "sidebar\|error"
```

### Common Issues

- **Missing sidebar**: Check if `is_active_sidebar()` returns true for that sidebar ID
- **Template not loading**: Ensure using `require` not `require_once`
- **Missing function**: Search WordPress documentation, implement in appropriate file under `functions/`

---

## Commit Message Suggestion

```
feat: New architecture - WordPress themes control full HTML structure

- WordPress themes now output complete HTML (DOCTYPE through </html>)
- Backdrop CSS/JS injected into WordPress output via regex
- page.tpl.php bypasses block system, runs WordPress directly
- Changed require_once to require for header/footer/sidebar templates
- Added get_adjacent_post(), post navigation link functions
- All 5 Twenty-Something themes now render correctly with sidebars

This is a significant architectural improvement that eliminates
theme-specific CSS workarounds and lets WordPress themes work
as designed.
```

---

## Acknowledgments

This work was done in collaboration with Luke McCormick, who provided the key insight that led to the breakthrough: "What if we just figured out what Backdrop wants to add... and insert that into whatever WordPress has?"

That simple question led to the architectural pivot that made everything work.

