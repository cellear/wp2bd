# WordPress 4.9 I/O Operations Analysis
**Date:** December 17, 2025
**Context:** Theme-Only Architecture - Identifying files that need I/O protection
**Author:** Claude (AI Assistant)

## Executive Summary

During implementation of the theme-only architecture, we discovered that loading WordPress core files requires careful vetting to prevent unwanted I/O operations. This document catalogs WordPress 4.9 files that perform I/O and our protection strategy.

## Current I/O Protections

### ✅ Database Access - PROTECTED
**File:** `wpbrain/wp-content/db.php` (Custom Drop-in)
**Protection Method:** WordPress automatically loads this file instead of `wp-includes/wp-db.php`
**Implementation:** `wp-bootstrap.php` lines 89-104
**Status:** Working - all database calls intercepted

The `wpdb` class is replaced with our bridge that redirects to Backdrop's database layer.

### ✅ Options System - PROTECTED
**File:** `wp-includes/option.php` (Skipped)
**Protection Method:** We skip loading this file and provide our own `get_option()`
**Implementation:**
- Skip: `wp-bootstrap.php` line 214-215
- Bridge: `themes/wp/includes/wp-options-bridge.php`
**Status:** Working - options come from Backdrop config

## WordPress Files That Perform I/O

### Network/HTTP Operations

#### High Risk Files - DO NOT LOAD
1. **`wp-includes/http.php`**
   - Contains: `wp_remote_get()`, `wp_remote_post()`, `wp_remote_request()`
   - Risk: Makes HTTP requests to external servers
   - Used for: Plugin updates, remote API calls, feed fetching

2. **`wp-includes/class-http.php`** and related classes
   - `class-wp-http-curl.php` - cURL wrapper
   - `class-wp-http-streams.php` - PHP streams wrapper
   - `class-wp-http-*.php` - Various HTTP client implementations
   - Risk: Direct network access
   - Used for: All HTTP operations in WordPress

3. **`wp-includes/update.php`**
   - Contains: `wp_version_check()`, `wp_update_plugins()`, `wp_update_themes()`
   - Risk: Makes HTTP calls to wordpress.org
   - Used for: Checking for WordPress/plugin/theme updates

4. **`wp-includes/feed*.php` files**
   - `feed-atom.php`, `feed-rss.php`, etc.
   - Risk: May fetch remote feeds
   - Used for: RSS/Atom feed generation and consumption

### File System Operations

#### Medium Risk Files - Evaluate Before Loading
1. **`wp-includes/functions.php`** ⚠️ PARTIALLY DANGEROUS
   - **Safe functions:** `wp_filter_object_list()`, `wp_list_pluck()`, many utilities
   - **Dangerous functions:**
     - `wp_remote_fopen()` (line 930) - fetches remote URLs
     - `wp_mkdir_p()` (line 1589) - creates directories
     - File operations: `fopen()`, `fwrite()`, `unlink()` (lines 1806, 2170, 5489)
   - **Our approach:** Extract only safe functions, don't load entire file

2. **`wp-includes/cache.php`**
   - Contains: File-based caching functions
   - Risk: Writes to filesystem
   - Used for: WordPress object cache (when no persistent cache available)
   - **Our approach:** Stub cache functions (see wp-bootstrap.php lines 124-144)

### Database Operations

#### Protected - Safe to Load
All files that use `$wpdb` global are safe because we intercept the wpdb class:
- `wp-includes/post.php` - Uses `$wpdb->query()` (intercepted) ✅
- `wp-includes/taxonomy.php` - Uses `$wpdb->get_results()` (intercepted) ✅
- `wp-includes/user.php` - Uses `$wpdb->get_var()` (intercepted) ✅
- `wp-includes/meta.php` - Uses `$wpdb->get_col()` (intercepted) ✅
- `wp-includes/query.php` - Uses `$wpdb` (intercepted) ✅

Our `db.php` drop-in catches all database calls and redirects to Backdrop.

## Files Currently Loaded (Safe)

These files have been vetted and loaded in `wp-bootstrap.php`:

### Core Classes (Lines 181-184)
- `class-wp-post.php` - Data structures only ✅
- `class-wp-query.php` - Query builder (uses intercepted wpdb) ✅
- `class-wp-hook.php` - Hook system, no I/O ✅

### Plugin System (Line 187)
- `plugin.php` - Hook functions (`add_action`, `do_action`, etc.) ✅

### Formatting (Lines 190-191)
- `formatting.php` - String manipulation functions ✅
- `kses.php` - HTML sanitization ✅

### Post Functions (Lines 194-195)
- `post.php` - Post functions (uses intercepted wpdb) ✅
- `query.php` - Query functions (uses intercepted wpdb) ✅

### Taxonomy (Line 198)
- `taxonomy.php` - Taxonomy functions (uses intercepted wpdb) ✅
  - **Note:** Requires `wp_filter_object_list()` which we must provide safely

### Template Functions (Lines 201-205)
- `post-template.php` - Template tags for posts ✅
- `general-template.php` - General template tags ✅
- `link-template.php` - Link/URL generation ✅
- `author-template.php` - Author template tags ✅
- `category-template.php` - Category template tags ✅

### Theme Support (Lines 208-209)
- `theme.php` - Theme functionality ✅
- `template.php` - Template loading ✅

### Internationalization (Line 212)
- `l10n.php` - Translation functions ✅

## Current Issue: wp_filter_object_list()

**Problem:** `taxonomy.php` (line 174) calls `wp_filter_object_list()` which lives in `functions.php`

**Challenge:** `functions.php` contains 5,799 lines with many dangerous I/O operations mixed with safe utility functions.

**Solutions:**
1. ❌ Load entire `functions.php` - TOO DANGEROUS (contains wp_remote_fopen, file ops)
2. ✅ **Extract safe functions** - Copy just wp_filter_object_list() and dependencies
3. ✅ **Load WP_List_Util class** - wp_filter_object_list() depends on this class
4. ✅ **Stub in bootstrap** - Define the function directly in wp-bootstrap.php

## Recommended Approach

### For wp_filter_object_list():
1. Load `class-wp-list-util.php` (already verified safe - pure array manipulation)
2. Stub `wp_filter_object_list()` and related functions in wp-bootstrap.php
3. Do NOT load full functions.php

### For Future WordPress Files:
Before loading any new WordPress file:
1. **Search for I/O operations:**
   ```bash
   grep -n "file_get_contents\|fopen\|fwrite\|curl_\|wp_remote_\|file_put_contents\|unlink\|mkdir" [file]
   ```
2. **Check for database access** - If uses `$wpdb`, it's safe (we intercept)
3. **Look for HTTP calls** - `wp_remote_*`, `curl_*`, `file_get_contents('http')`
4. **Verify no file system writes** - `fwrite`, `file_put_contents`, `mkdir`, `unlink`

## Files to NEVER Load

### Absolutely Forbidden
- `wp-includes/http.php` - HTTP client
- `wp-includes/class-http*.php` - HTTP implementations
- `wp-includes/update.php` - Update checker (calls home)
- `wp-admin/**` - Admin interface (not needed for rendering)

### Load Only If Modified
- `wp-includes/functions.php` - Extract safe functions only
- `wp-includes/cache.php` - Stub cache functions (already done)
- `wp-includes/option.php` - Use our bridge instead (already done)

## Testing I/O Protection

To verify no unexpected I/O:
1. Monitor network during page render: `tcpdump -i any port 80 or port 443`
2. Watch filesystem: `fs_usage -w -f filesys php`
3. Check database queries: Enable query logging in Backdrop
4. Test in isolated environment (Docker/DDEV without network)

## References

- Epic 3: Database Interception (`db.php` drop-in)
- Epic 7: Data Bridges (options, posts, users, terms)
- Epic 8: Template Functions (theme-only architecture)
- wp-bootstrap.php: Lines 82-215 (bootstrap sequence)

## Next Steps

1. ✅ Verify `class-wp-list-util.php` is safe (DONE - pure array manipulation)
2. ⏳ Stub `wp_filter_object_list()` in wp-bootstrap.php without loading functions.php
3. ⏳ Document this pattern for future function needs
4. ⏳ Create comprehensive I/O protection test suite

---
**Last Updated:** December 17, 2025
**Status:** In Progress - Implementing wp_filter_object_list() safely
