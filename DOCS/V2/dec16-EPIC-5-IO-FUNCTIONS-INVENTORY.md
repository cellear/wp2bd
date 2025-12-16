# Epic 5: External I/O Functions Inventory

**Story:** WP4BD-V2-040
**Date:** December 16, 2025
**Status:** Documentation Complete
**Strategy:** Full lockdown - all external I/O disabled or redirected to Backdrop

---

## Overview

This document identifies all WordPress functions in wpbrain/ that perform external input/output operations. These functions will be modified to either:
1. **Stub out** - Return empty/false to disable functionality
2. **Redirect to Backdrop** - Use Backdrop's equivalent APIs
3. **Comment out** - Disable completely with clear documentation

All modifications will be tracked via git, with clear commit messages explaining each change.

---

## Category 1: HTTP/Remote Communication

**File:** `wp-includes/http.php`
**Purpose:** External HTTP requests, API calls, remote content fetching

### Functions to Disable (Stub Out):

| Function | Line | Purpose | Modification |
|----------|------|---------|--------------|
| `wp_remote_request()` | 151 | Generic HTTP request | Return WP_Error |
| `wp_remote_get()` | 168 | HTTP GET request | Return WP_Error |
| `wp_remote_post()` | 185 | HTTP POST request | Return WP_Error |
| `wp_remote_head()` | 202 | HTTP HEAD request | Return WP_Error |

### Support Functions (Can Keep - Parse Responses):

| Function | Line | Purpose | Action |
|----------|------|---------|--------|
| `wp_remote_retrieve_headers()` | 218 | Extract headers from response | Keep (harmless) |
| `wp_remote_retrieve_header()` | 235 | Get single header | Keep (harmless) |
| `wp_remote_retrieve_response_code()` | 257 | Get response code | Keep (harmless) |
| `wp_remote_retrieve_response_message()` | 274 | Get response message | Keep (harmless) |
| `wp_remote_retrieve_body()` | 289 | Get response body | Keep (harmless) |
| `wp_remote_retrieve_cookies()` | 304 | Get response cookies | Keep (harmless) |
| `wp_remote_retrieve_cookie()` | 321 | Get single cookie | Keep (harmless) |
| `wp_remote_retrieve_cookie_value()` | 346 | Get cookie value | Keep (harmless) |

**File:** `wp-includes/functions.php`
| Function | Line | Purpose | Modification |
|----------|------|---------|--------------|
| `wp_remote_fopen()` | 930 | Fetch remote file via HTTP | Return false |

### HTTP Classes to Disable:

**Files to modify:**
- `wp-includes/class-http.php` - Main HTTP class
- `wp-includes/class-wp-http-curl.php` - cURL transport
- `wp-includes/class-wp-http-streams.php` - Streams transport
- `wp-includes/class-wp-http-proxy.php` - Proxy support
- `wp-includes/class-wp-http-cookie.php` - Cookie handling
- `wp-includes/class-wp-http-encoding.php` - Response encoding
- `wp-includes/class-wp-http-response.php` - Response handling
- `wp-includes/class-wp-http-requests-hooks.php` - Request hooks
- `wp-includes/class-wp-http-requests-response.php` - Response wrapper
- `wp-includes/class-wp-http-ixr-client.php` - XML-RPC client
- `wp-includes/class-wp-simplepie-file.php` - RSS/Atom fetching

**Modification Strategy:** Comment out transport mechanisms (cURL, streams) in class-http.php constructor so no actual requests can be made.

---

## Category 2: Cron/Background Processing

**File:** `wp-includes/cron.php`
**Purpose:** Background task scheduling and execution

### Functions to Disable (Stub Out):

| Function | Line | Purpose | Modification |
|----------|------|---------|--------------|
| `wp_schedule_single_event()` | 27 | Schedule one-time event | Return false |
| `wp_schedule_event()` | 88 | Schedule recurring event | Return false |
| `wp_unschedule_event()` | 177 | Remove scheduled event | Return false |
| `wp_unschedule_hook()` | 233 | Remove all events for hook | Return 0 |
| `spawn_cron()` | 275 | Trigger cron via HTTP request | Return false (CRITICAL) |
| `wp_cron()` | 371 | Execute scheduled events | Return null (disable) |

**File:** `wp-includes/ms-functions.php`
| Function | Line | Purpose | Modification |
|----------|------|---------|--------------|
| `wp_schedule_update_network_counts()` | 2294 | Schedule multisite stat updates | Return false |

**File:** `wp-includes/update.php`
| Function | Line | Purpose | Modification |
|----------|------|---------|--------------|
| `wp_schedule_update_checks()` | 709 | Schedule plugin/theme update checks | Return false |

**Critical Note:** `spawn_cron()` makes an HTTP request to the site itself to trigger background processing. Must be fully disabled.

---

## Category 3: Update/Upgrade System

**File:** `wp-includes/update.php`
**Purpose:** Check for and download WordPress core, plugin, and theme updates

### Functions to Disable (Stub Out):

| Function | Line | Purpose | Modification |
|----------|------|---------|--------------|
| `wp_update_plugins()` | 223 | Check for plugin updates via HTTP | Comment out body, return null |
| `wp_update_themes()` | 397 | Check for theme updates via HTTP | Comment out body, return null |

**Note:** This file likely contains other update-related functions. Need to review entire file and disable anything that makes external requests.

---

## Category 4: File System Operations

**File:** `wp-includes/functions.php`
**Purpose:** File and directory manipulation

### Functions to Evaluate:

| Function | Line | Purpose | Decision |
|----------|------|---------|----------|
| `wp_mkdir_p()` | 1589 | Create directory recursively | **KEEP** - Safe, used for uploads |
| `wp_upload_dir()` | 1871 | Get upload directory paths | **MODIFY** - Map to Backdrop paths |

### File Operations in Other Files:

Need to search for:
- `file_put_contents()` usage
- `fwrite()` / `fopen()` for writing
- Any file download functions
- Zip/unzip operations
- File copy operations

**Files to Check:**
- `wp-admin/includes/file.php` (if we ever load admin)
- `wp-includes/class-wp-filesystem-*.php` (file system abstraction classes)

---

## Category 5: Mail Functions

**Search needed for:**
- `wp_mail()` - Email sending
- `mail()` - PHP mail function

**Decision:** Likely need to disable or redirect to Backdrop's mail system.

---

## Category 6: External Content/Feed Fetching

**File:** `wp-includes/class-wp-simplepie-file.php`
**Purpose:** Fetch RSS/Atom feeds

**Modification:** Comment out the file fetching mechanism, return empty feeds.

**Related Files:**
- `wp-includes/feed.php` - Feed template functions (may be safe)
- `wp-includes/class-simplepie.php` - Feed parsing (safe if no remote fetch)

---

## Category 7: Multisite Network Communication

**File:** `wp-includes/ms-functions.php`

Functions that might make cross-site requests in multisite environment:
- Network count updates
- Cross-site user queries
- Blog registration/activation

**Decision:** Review entire file for network I/O operations. Not critical for V2 (we're not doing multisite).

---

## Summary of Files Requiring Modification

### High Priority (Core I/O):
1. ✅ `wp-includes/http.php` - HTTP functions
2. ✅ `wp-includes/class-http.php` - HTTP transport classes
3. ✅ `wp-includes/cron.php` - Cron scheduling
4. ✅ `wp-includes/update.php` - Update checking
5. ⏳ `wp-includes/functions.php` - wp_upload_dir() only

### Medium Priority (Supporting):
6. `wp-includes/class-wp-http-*.php` - All HTTP classes (11 files)
7. `wp-includes/class-wp-simplepie-file.php` - Feed fetching

### Low Priority (Review Needed):
8. `wp-includes/ms-functions.php` - Multisite functions
9. Mail-related files (TBD - need to locate)
10. File operation classes (if we need admin features)

---

## Next Steps (V2-041)

1. Start with http.php - stub out the 4 main HTTP functions
2. Disable cron.php - stub out all scheduling functions
3. Disable update.php - comment out update check functions
4. Modify class-http.php to prevent transport initialization
5. Test that no external requests are possible

---

## Testing Strategy

After modifications, verify:
1. No HTTP requests leave the server (test with network monitor)
2. No cron jobs spawn (check process list)
3. No update checks occur (monitor log files)
4. File uploads work with Backdrop paths (after V2-042)
5. WordPress themes still render correctly

---

## Documentation Standards

For each modification in wpbrain/:
- Use standard PHP block comments
- Include reason for change
- Include date and story number (V2-041)
- Show what was removed (via git diff)
- Clear breadcrumbs for future maintainers

Example:
```php
/**
 * WP4BD V2-041 Modification (2025-12-16)
 *
 * Original wp_remote_get() disabled to prevent external HTTP requests.
 * WordPress themes should not make external API calls in WP4BD architecture.
 * All data comes from Backdrop, no external communication needed.
 *
 * Original function commented out below - see git history for full code.
 */
function wp_remote_get($url, $args = array()) {
    // Log the attempt if debugging enabled
    if (defined('WP4BD_DEBUG') && WP4BD_DEBUG) {
        error_log('WP4BD: Blocked HTTP GET request to ' . $url);
    }

    // Return WP_Error to indicate request failed
    return new WP_Error(
        'http_request_disabled',
        'External HTTP requests are disabled in WP4BD',
        array('url' => $url)
    );
}
```

---

## Git Commit Strategy

Each category should be a separate commit:
1. "Epic 5 V2-040: Add I/O functions inventory documentation"
2. "Epic 5 V2-041: Disable HTTP request functions in http.php"
3. "Epic 5 V2-041: Disable cron scheduling in cron.php"
4. "Epic 5 V2-041: Disable update checks in update.php"
5. "Epic 5 V2-041: Disable HTTP transport classes"
6. "Epic 5 V2-042: Map wp_upload_dir() to Backdrop file paths"

This allows easy rollback of individual changes if needed.

---

**End of V2-040 Documentation**
