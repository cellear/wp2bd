# WordPress Theme Compatibility Layer

**Last Updated:** December 1, 2025  
**Status:** Four themes working (2014, 2015, 2016, 2017, plus Outlined)  
**Branch:** `december-4-themes-baseline`

---

## Overview

This document describes the architecture and fixes that enable classic WordPress themes (Twenty Fourteen through Twenty Seventeen, plus custom themes like Outlined) to render correctly in Backdrop CMS using the `wp` theme wrapper and `wp_content` module.

The goal is **theme-agnostic compatibility** — fixes should work across multiple themes rather than requiring theme-specific code paths.

---

## Architecture: Four-Block System

WordPress themes are rendered using four independent Backdrop blocks:

| Block | Renders | Contains |
|-------|---------|----------|
| `wordpress_header` | `header.php` | DOCTYPE, `<head>`, navigation, branding |
| `wordpress_content` | Main loop | Posts/pages via `have_posts()`/`the_post()` |
| `wordpress_sidebar` | `sidebar.php` | Widget areas |
| `wordpress_footer` | `footer.php` | Footer, closing tags |

These blocks are placed in the **WordPress layout** (`layouts/wordpress/`) which provides a flex container (`.l-main-content`) that wraps content and sidebar.

### Key Files

```
backdrop-1.30/
├── layouts/wordpress/
│   ├── layout--wordpress.tpl.php   # Layout template
│   ├── wordpress.css               # Flexbox layout styles
│   └── wordpress.info              # Layout definition
├── modules/wp_content/
│   └── wp_content.module           # Block rendering logic
└── themes/wp/
    └── functions/
        ├── conditionals.php        # is_single(), is_home(), etc.
        ├── stubs.php               # Fallback function implementations
        ├── utilities.php           # Core utilities (home_url, bloginfo, etc.)
        ├── template-loading.php    # get_header(), get_sidebar(), etc.
        └── post-metadata.php       # get_the_author(), the_date(), etc.
```

---

## Recent Fixes (December 2025)

### Problem 1: Duplicate Sidebars (Twenty Fifteen)

**Issue:** Twenty Fifteen's `header.php` calls `get_sidebar()` internally (the theme has a left-sidebar-in-header design). When we also rendered the sidebar block, the sidebar appeared twice.

**Solution:** 
1. Modified `get_sidebar()` in `template-loading.php` to set a global flag:
   ```php
   $GLOBALS['wp2bd_sidebar_rendered'] = TRUE;
   ```
2. Modified `_wp_content_render_sidebar()` in `wp_content.module` to check this flag and skip rendering if sidebar was already output.
3. Added explicit theme detection for themes known to include sidebar in header.

**Files Changed:**
- `themes/wp/functions/template-loading.php`
- `modules/wp_content/wp_content.module`

---

### Problem 2: Layout CSS Conflicts (All Themes)

**Issue:** Different themes use different HTML structures and CSS approaches:
- Twenty Fourteen: Left sidebar, expects `#secondary` before `#primary` in DOM
- Twenty Fifteen: Left sidebar integrated into header
- Twenty Sixteen: Right sidebar with floats
- Twenty Seventeen: Right sidebar with flexbox

Our layout wrapper was conflicting with theme-specific CSS.

**Solution:** Created `wordpress.css` with:
1. **Flexbox container** (`.l-main-content`) that works regardless of DOM order
2. **CSS `order` property** to visually reposition sidebar for left-sidebar themes
3. **Override rules** (`float: none !important`) to neutralize theme float-based layouts
4. **Theme-specific width adjustments** where needed

**Key CSS patterns:**
```css
/* Base flex layout */
.l-main-content {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
}

/* Content takes available space */
.l-main-content > #primary { flex: 1 1 0; }

/* Sidebar fixed width */
.l-main-content > #secondary { flex: 0 0 300px; }

/* Left-sidebar themes: reorder visually */
.l-main-content > div#secondary:not(.sidebar) { order: -1; }

/* Override theme floats */
.l-main-content > #primary,
.l-main-content > #secondary {
  float: none !important;
  margin-left: 0 !important;
}
```

**Files Changed:**
- `layouts/wordpress/wordpress.css` (NEW)
- `layouts/wordpress/wordpress.info`
- `layouts/wordpress/layout--wordpress.tpl.php`

---

### Problem 3: Missing WordPress Functions

**Issue:** Various themes called WordPress functions that weren't implemented, causing fatal errors.

**Functions Added:**

| Function | File | Purpose |
|----------|------|---------|
| `is_feed()` | `conditionals.php` | Check if rendering RSS feed |
| `is_page_template()` | `conditionals.php` | Check for page template |
| `the_author()` | `post-metadata.php` | Display post author |
| `get_object_taxonomies()` | `utilities.php` | Get taxonomies for post type |
| `wp_title()` | `utilities.php` | Generate page title |
| `display_header_text()` | `stubs.php` | Check header text visibility |
| `get_query_var()` | `stubs.php` | Get query variable |
| `set_query_var()` | `stubs.php` | Set query variable |
| `get_queried_object()` | `stubs.php` | Get current queried object |
| `_n()` | `stubs.php` | Plural translation |
| `_nx()` | `stubs.php` | Plural translation with context |

**Pattern for Adding Functions:**
1. Check if function is called by theme templates
2. Determine if it can be implemented using Backdrop facilities
3. If yes → implement in appropriate function file
4. If no → add stub in `stubs.php` that returns safe default

---

## Function Organization Philosophy

Functions are organized by **implementation quality**, not by WordPress category:

| File | Contains | Quality |
|------|----------|---------|
| `conditionals.php` | `is_*()` functions | Fully implemented with Backdrop logic |
| `utilities.php` | Core utilities | Fully implemented |
| `post-metadata.php` | Post/author data | Fully implemented |
| `template-loading.php` | Template includes | Fully implemented |
| `content-display.php` | Content output | Fully implemented |
| `stubs.php` | Fallbacks | Minimal stubs, returns safe defaults |

**Stubs are a last resort.** They exist to prevent fatal errors, not to provide functionality. When a stub is frequently called, it should be promoted to a real implementation.

---

## Theme-Specific Notes

### Twenty Fourteen (2014)
- **Layout:** Left sidebar
- **Key feature:** `#secondary` has no `.sidebar` class
- **Fix:** CSS targets `div#secondary:not(.sidebar)` with `order: -1`

### Twenty Fifteen (2015)
- **Layout:** Left sidebar integrated into header
- **Key feature:** `header.php` calls `get_sidebar()` directly
- **Fix:** Sidebar block detects this and skips duplicate rendering

### Twenty Sixteen (2016)
- **Layout:** Right sidebar
- **Key feature:** Uses `aside#secondary.sidebar`
- **Fix:** Standard flexbox layout works

### Twenty Seventeen (2017)
- **Layout:** Right sidebar (optional)
- **Key feature:** Has its own flexbox CSS
- **Fix:** Our CSS complements rather than conflicts

### Outlined (Custom)
- **Layout:** Right sidebar
- **Key feature:** Simple theme without template-parts
- **Fix:** Fallback content rendering in `wp_content.module`

---

## For AI Assistants

When working on theme compatibility:

1. **Check for fatal errors first** — Look in Backdrop's watchdog log for "FATAL" messages
2. **Identify the missing function** — The error will show the function name and file
3. **Search existing implementations** — `grep -r "function funcname" themes/wp/functions/`
4. **Check WordPress source** — Original implementations are in `wordpress-4.9/wp-includes/`
5. **Implement or stub** — Prefer real implementations; stubs are fallbacks

**Common failure patterns:**
- Missing conditional function → Add to `conditionals.php`
- Missing template tag → Add to `post-metadata.php` or `content-display.php`
- Missing utility → Add to `utilities.php`
- Unknown/rare function → Add stub to `stubs.php`

**Testing approach:**
1. Clear Backdrop caches: `bee cc all` or via admin UI
2. Load the homepage
3. Check for errors in watchdog log
4. If layout issues, inspect HTML structure in browser dev tools
5. Compare against original WordPress theme output if available

---

## Files in This Commit

```
layouts/wordpress/layout--wordpress.tpl.php  # Layout structure
layouts/wordpress/wordpress.css              # Flexbox layout (NEW)
layouts/wordpress/wordpress.info             # Registers CSS
modules/wp_content/wp_content.module         # Sidebar duplicate detection
scripts/generate_stubs_from_docs.php         # Documentation comments
themes/wp/functions/conditionals.php         # is_feed(), is_page_template()
themes/wp/functions/post-metadata.php        # the_author()
themes/wp/functions/stubs.php                # Multiple new stubs
themes/wp/functions/template-loading.php     # Sidebar flag
themes/wp/functions/utilities.php            # get_object_taxonomies(), wp_title()
```

---

## Next Steps

1. **Test additional Twenty* themes** (2010-2013, 2018-2020)
2. **Refactor stubs** — Move frequently-used stubs to proper implementations
3. **Document theme requirements** — Create compatibility matrix
4. **Consider theme analysis tool** — Scan new themes for function requirements

