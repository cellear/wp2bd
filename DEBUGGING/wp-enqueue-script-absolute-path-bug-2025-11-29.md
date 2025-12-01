# wp_enqueue_script() Absolute Path Bug Fix

**Date:** November 29, 2025  
**Branch:** `fix/wp-enqueue-script-absolute-path-bug`  
**File Changed:** `backdrop-1.30/themes/wp/functions/stubs.php`

## Bug Description

The `wp_enqueue_script()` function was passing absolute filesystem paths to `backdrop_add_js()`, which expects paths relative to the Backdrop root directory. This caused script loading to fail because:

1. `get_template_directory()` returns an absolute filesystem path (e.g., `/path/to/backdrop/themes/wp/wp-content/themes/twentyseventeen`)
2. The code concatenated this absolute path with the `$src` parameter to create a full absolute path
3. `backdrop_add_js()` expects paths relative to the Backdrop root (e.g., `themes/wp/wp-content/themes/twentyseventeen/assets/js/script.js`)
4. When these absolute paths were later converted to URLs by `file_create_url()`, they were incorrectly treated as root-relative URL paths rather than proper file references, breaking script loading

## Root Cause

The bug was in the `wp_enqueue_script()` function in `stubs.php` at line 214, where it directly used the absolute path from `get_template_directory()` without converting it to a relative path.

## Fix

The fix converts the absolute path returned by `get_template_directory()` to a relative path by:

1. Getting the absolute path from `get_template_directory()`
2. Removing the `BACKDROP_ROOT` prefix using `substr()`
3. Trimming any leading slashes with `ltrim()`
4. Appending the `$src` parameter to create the final relative path

This ensures that `backdrop_add_js()` receives paths in the format it expects (relative to Backdrop root), allowing scripts to load correctly.

## Code Changes

**Before:**
```php
// Relative path - prepend active theme directory
$path = get_template_directory() . '/' . $src;
```

**After:**
```php
// Relative path - prepend active theme directory
// backdrop_add_js() expects path relative to Backdrop root
// get_template_directory() returns absolute path, convert to relative
$template_dir = get_template_directory();
$backdrop_root = BACKDROP_ROOT;

// Convert absolute path to relative path from Backdrop root
$relative_template_dir = substr($template_dir, strlen($backdrop_root));
$relative_template_dir = ltrim($relative_template_dir, '/');
$path = $relative_template_dir . '/' . $src;
```

## Testing

After this fix, WordPress themes using `wp_enqueue_script()` should correctly load JavaScript files through Backdrop's asset system.

## Related

This bug was identified by an automated code review agent that detected the mismatch between absolute filesystem paths and Backdrop's expected relative paths.

