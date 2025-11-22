# Debugging and Integration Changes

**Date:** November 20, 2025  
**Branch:** `claude/debugging-integration`  
**Status:** In Progress - Content rendering working, debugging enabled

---

## Summary

This document describes the changes made to get the WordPress Twenty Seventeen theme rendering content from Backdrop nodes. The primary focus was on fixing function redeclaration errors, ensuring proper global variable handling, and implementing the WordPress Loop to display Backdrop content.

---

## Changes Made

### 1. Fixed Function Redeclaration Errors

**Problem:** Multiple files were declaring the same WordPress functions, causing fatal errors.

**Files Modified:**
- `themes/wp/classes/WP_Query.php` - Removed duplicate `do_action()`, `setup_postdata()`, and `mysql2date()` stubs
- `themes/wp/functions/loop.php` - Removed duplicate `do_action()` and `get_post()` stubs
- `themes/wp/functions/content-display.php` - Removed duplicate conditional function stubs (`is_single`, `is_singular`, `is_page`, etc.)
- `themes/wp/functions/utilities.php` - Wrapped utility functions with `function_exists()` checks to allow overrides

**Solution:** Kept full implementations in their primary locations and removed stub duplicates. Used `function_exists()` checks where appropriate to allow theme overrides.

---

### 2. Fixed Global Variable Mismatch

**Problem:** Template tag functions (`get_the_title()`, `get_the_ID()`, `get_the_content()`) were looking for `$wp_post` global, but `setup_postdata()` was only setting `$post`.

**Files Modified:**
- `themes/wp/functions/loop.php` - Modified `setup_postdata()` to set both `$GLOBALS['post']` and `$GLOBALS['wp_post']`
- `themes/wp/functions/content-display.php` - Updated `get_the_title()`, `get_the_ID()`, `get_the_content()`, and `get_permalink()` to check both `$post` and `$wp_post` globals

**Solution:** Made functions check both WordPress standard `$post` and WP2BD `$wp_post` globals for compatibility.

---

### 3. Implemented Content Rendering in page.tpl.php

**Problem:** The WordPress theme wasn't receiving any content to display.

**Files Modified:**
- `themes/wp/page.tpl.php` - Added direct content rendering using WordPress loop functions

**Solution:** Implemented a direct content rendering approach that:
1. Uses `have_posts()` and `the_post()` to iterate through posts
2. Builds HTML content using `get_the_title()`, `get_permalink()`, and `get_the_content()`
3. Outputs visible debugging information showing content length and preview
4. Temporarily bypasses WordPress template hierarchy to verify data flow

**Current State:** Content is successfully rendering on both home page (multiple posts) and single node pages.

---

### 4. Fixed Node Bundle Property Error

**Problem:** `EntityMalformedException: Missing bundle property on entity of type node` when converting nodes to WordPress posts.

**Files Modified:**
- `themes/wp/template.php` - Added node reloading logic to ensure `type` property exists
- `themes/wp/classes/WP_Post.php` - Added bundle property check in `from_node()` method

**Solution:** 
- Always reload nodes using `node_load()` to ensure all properties including `type` are present
- Added database fallback query if `type` is still missing
- Set default `type = 'page'` as last resort

**Status:** Partially fixed - still investigating why `menu_get_object()` sometimes returns nodes without `type` property.

---

### 5. Fixed Dynamic Property Deprecation Warning

**Problem:** PHP 8.2+ deprecation warning: `Creation of dynamic property WP_Query::$is_singular is deprecated`

**Files Modified:**
- `themes/wp/classes/WP_Query.php` - Added `public $is_singular = false;` property declaration

**Solution:** Declared `is_singular` as a public property in the `WP_Query` class definition.

---

### 6. Enhanced Query Initialization

**Problem:** `WP_Query` constructor was executing queries automatically, interfering with manual post population.

**Files Modified:**
- `themes/wp/template.php` - Modified query initialization to:
  - Use invalid query parameters to create empty query
  - Manually override `posts`, `post_count`, and other properties
  - Properly set query flags (`is_home`, `is_archive`, `is_single`, etc.)

**Solution:** Create `WP_Query` with invalid IDs, then immediately override with manually loaded posts.

---

## Current Functionality

### Working ✅
- Home page displays multiple promoted nodes as posts
- Single node pages display individual posts
- WordPress loop functions (`have_posts()`, `the_post()`) work correctly
- Template tags (`get_the_title()`, `get_the_content()`, `get_permalink()`) return correct data
- Content is being rendered with proper HTML structure

### Partially Working ⚠️
- Node bundle property: Most cases work, but `/test` page still throws error occasionally
- WordPress template hierarchy: Currently bypassed in favor of direct output

### Not Yet Implemented ❌
- Full WordPress template hierarchy integration (index.php, single.php, etc.)
- Pagination
- Archive pages
- Search functionality
- 404 handling

---

## Debugging Output

Currently enabled in `page.tpl.php`:
- Visible green debug box showing:
  - Content length in characters
  - First 1000 characters of content preview
- Direct content output using WordPress loop

**Note:** Debugging will remain enabled until explicitly requested to be removed.

---

## Files Changed

1. `themes/wp/classes/WP_Query.php` - Added `is_singular` property, removed duplicate functions
2. `themes/wp/classes/WP_Post.php` - Added bundle property validation
3. `themes/wp/functions/loop.php` - Fixed global variable handling, removed duplicates
4. `themes/wp/functions/content-display.php` - Fixed global variable handling, removed duplicates
5. `themes/wp/functions/utilities.php` - Added `function_exists()` checks
6. `themes/wp/template.php` - Enhanced node loading, query initialization
7. `themes/wp/page.tpl.php` - Added content rendering and debugging

---

## Next Steps (Planned)

1. **Fix remaining bundle property issue** - Investigate why `menu_get_object()` sometimes returns incomplete nodes
2. **Integrate WordPress template hierarchy** - Enable proper template selection (index.php, single.php, etc.)
3. **Remove debugging output** - Once everything is working
4. **Add error handling** - Better handling of edge cases
5. **Test with more content types** - Ensure compatibility with different node types
6. **Implement pagination** - For archive/home pages with many posts

---

## Testing

Test URLs:
- Home page: `https://wp4bd-test2.ddev.site/` - Should show multiple posts
- Single node: `https://wp4bd-test2.ddev.site/test` - Should show single post (currently errors)

Test commands:
```bash
# Clear cache
ddev bee cc all

# Fetch pages
wget https://wp4bd-test2.ddev.site/ -O HTML/test-home.html
wget https://wp4bd-test2.ddev.site/test -O HTML/test-single.html

# Check logs
ddev bee dblog
```

---

## Notes

- All changes maintain backward compatibility with existing WordPress theme code
- Debugging output is intentionally verbose to aid troubleshooting
- Some PHP 8.2+ deprecation warnings remain (non-critical)
- The compatibility layer is working but needs refinement for production use

