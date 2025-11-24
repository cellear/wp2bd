# Intelligent WordPress Function Stub Telemetry System

## Problem Statement

Currently, WordPress theme integration fails with fatal "undefined function" errors, requiring reactive debugging (whack-a-mole). Each missing function must be discovered through error logs, manually stubbed, and eventually implemented.

## Proposed Solution

Build an **intelligent stub telemetry system** that:
1. **Prevents all fatal errors** by pre-generating stubs for all WordPress functions
2. **Tracks usage** of stubbed functions (call counts, arguments, context)
3. **Prioritizes implementation** based on actual usage data
4. **Enables AI-assisted implementation** on demand

---

## Architecture Overview

### Phase 1: Bulk Stub Generation

**Goal:** Generate stubs for all ~2000+ WordPress core functions

**Implementation:**
1. Parse WordPress core source files (`wp-includes/*.php`)
2. Extract function signatures using PHP tokenizer
3. Generate stub file with telemetry hooks
4. Include proper PHPDoc from WordPress source

**Output:** `themes/wp/functions/auto-stubs.php`

```php
/**
 * Auto-generated WordPress function stubs with telemetry.
 * DO NOT EDIT - regenerate with: ddev bee wp2bd:generate-stubs
 */

if (!function_exists('wp_get_attachment_url')) {
  /**
   * Retrieve the URL for an attachment.
   * @see https://developer.wordpress.org/reference/functions/wp_get_attachment_url/
   */
  function wp_get_attachment_url($attachment_id) {
    _wp2bd_log_stub_call(__FUNCTION__, func_get_args());
    return false;
  }
}

// ... 2000+ more functions
```

---

### Phase 2: Telemetry Infrastructure

**Database Schema:**

```sql
CREATE TABLE wp2bd_stub_calls (
  function_name VARCHAR(255) PRIMARY KEY,
  call_count INT DEFAULT 0,
  first_called INT,
  last_called INT,
  sample_args TEXT,
  sample_backtrace TEXT,
  status ENUM('stubbed', 'implementing', 'implemented', 'ignored') DEFAULT 'stubbed',
  priority INT DEFAULT 0
);
```

**Telemetry Logger:**

```php
function _wp2bd_log_stub_call($function_name, $args) {
  // Only log in development mode
  if (!config_get('wp2bd.telemetry_enabled', TRUE)) {
    return;
  }
  
  // Throttle logging (max once per function per page load)
  static $logged = [];
  if (isset($logged[$function_name])) {
    return;
  }
  $logged[$function_name] = TRUE;
  
  // Get backtrace to understand context
  $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
  
  // Log to database
  db_merge('wp2bd_stub_calls')
    ->key(['function_name' => $function_name])
    ->fields([
      'call_count' => 1,
      'last_called' => REQUEST_TIME,
      'sample_args' => json_encode($args),
      'sample_backtrace' => json_encode($backtrace),
    ])
    ->expression('call_count', 'call_count + 1')
    ->execute();
}
```

---

### Phase 3: Admin Dashboard

**Location:** `/admin/config/content/wp-content/stub-report`

**Features:**

#### 3.1 Usage Report Table
```
┌─────────────────────────┬───────┬──────────┬─────────────────────────┐
│ Function Name           │ Calls │ Status   │ Actions                 │
├─────────────────────────┼───────┼──────────┼─────────────────────────┤
│ wp_get_attachment_url   │  147  │ Stubbed  │ [Implement] [Ignore]    │
│ get_the_post_thumbnail  │   89  │ Stubbed  │ [Implement] [Ignore]    │
│ wp_nav_menu             │   12  │ Stubbed  │ [Implement] [Ignore]    │
│ is_sticky               │    5  │ Ignored  │ [Unignore]              │
│ get_post                │  234  │ ✓ Done   │ [View Code]             │
└─────────────────────────┴───────┴──────────┴─────────────────────────┘
```

#### 3.2 Function Detail Page
- WordPress documentation link
- Call count graph over time
- Sample arguments used
- Backtrace showing where it's called
- AI implementation suggestion

#### 3.3 Filters
- Show only: Stubbed / Implementing / Implemented / Ignored
- Sort by: Call count / Name / Last called
- Search by function name

---

### Phase 4: AI-Assisted Implementation

**Workflow:**

1. **User clicks "Implement" button**
2. **System fetches:**
   - WordPress function documentation
   - Function signature from WordPress source
   - Sample usage from telemetry (args, context)
3. **AI analyzes and generates:**
   - Backdrop-integrated implementation
   - Mapping strategy (e.g., `get_post()` → `node_load()`)
   - Test cases
4. **User reviews:**
   - Side-by-side: WordPress docs vs. proposed implementation
   - Approve / Edit / Reject
5. **System commits:**
   - Moves function from `auto-stubs.php` to `implemented.php`
   - Updates database status
   - Clears relevant caches

**Example AI Implementation:**

```php
// User clicks "Implement" for wp_get_attachment_url()

// AI generates:
if (!function_exists('wp_get_attachment_url')) {
  /**
   * Retrieve the URL for an attachment.
   * 
   * IMPLEMENTATION NOTES:
   * - Maps to Backdrop's file_create_url()
   * - Attachment ID corresponds to Backdrop file ID
   * - Returns absolute URL to file
   */
  function wp_get_attachment_url($attachment_id) {
    $file = file_load($attachment_id);
    if (!$file) {
      return false;
    }
    return file_create_url($file->uri);
  }
}
```

---

## Implementation Plan

### Phase 1: Stub Generation (2-3 hours)
- [x] Create Bee command: `ddev bee wp2bd:generate-stubs`
- [ ] Parse WordPress core files
- [ ] Extract function signatures
- [ ] Generate `auto-stubs.php`
- [ ] Test with current themes

### Phase 2: Telemetry (2-3 hours)
- [ ] Create database schema
- [ ] Implement `_wp2bd_log_stub_call()`
- [ ] Add configuration option to enable/disable
- [ ] Test logging performance impact

### Phase 3: Admin UI (3-4 hours)
- [ ] Create admin menu item
- [ ] Build usage report table
- [ ] Implement filters and search
- [ ] Create function detail page
- [ ] Add status management (Ignore/Unignore)

### Phase 4: AI Integration (4-5 hours)
- [ ] Create "Implement" workflow
- [ ] Fetch WordPress documentation
- [ ] Build AI prompt template
- [ ] Implement review interface
- [ ] Auto-commit approved implementations

---

## File Structure

```
themes/wp/functions/
├── auto-stubs.php          # Auto-generated stubs (DO NOT EDIT)
├── implemented.php         # AI/manually implemented functions
├── stubs.php              # Manual stubs (legacy, to be migrated)
├── escaping.php           # Existing
├── hooks.php              # Existing
└── widgets.php            # Existing

modules/wp_content/
├── wp_content.module
├── wp_content.admin.inc   # Admin UI
└── wp_content.telemetry.inc  # Telemetry functions
```

---

## Benefits

### Immediate
- ✅ **Zero fatal errors** - all WordPress functions exist
- ✅ **Faster theme testing** - themes load immediately
- ✅ **Clear visibility** - know exactly what's missing

### Medium-term
- ✅ **Data-driven prioritization** - implement most-used functions first
- ✅ **Reduced debugging time** - no more whack-a-mole
- ✅ **Better documentation** - telemetry shows real usage patterns

### Long-term
- ✅ **AI-assisted development** - faster implementation
- ✅ **Community contributions** - clear TODO list for contributors
- ✅ **Automated testing** - telemetry identifies regression risks

---

## Configuration Options

```php
// config/wp2bd.settings.json
{
  "telemetry_enabled": true,
  "telemetry_sample_rate": 1.0,  // 0.0 - 1.0 (for high-traffic sites)
  "auto_stub_generation": true,
  "ai_implementation_enabled": true
}
```

---

## Success Metrics

- **Coverage:** % of WordPress functions stubbed (target: 100%)
- **Implementation Rate:** Functions implemented per week
- **Error Reduction:** Fatal errors before/after (target: 0)
- **Theme Compatibility:** # of themes that load without errors

---

## Next Steps

1. **Review this proposal** - approve architecture
2. **Phase 1 prototype** - generate initial stubs
3. **Test with current themes** - verify zero fatal errors
4. **Iterate** - refine based on real usage

---

## Open Questions

1. **Performance:** What's acceptable overhead for telemetry logging?
2. **Storage:** How long to retain telemetry data?
3. **AI Provider:** Which AI service for implementation generation?
4. **Versioning:** How to handle WordPress version updates?

---

## Estimated Timeline

- **Phase 1 (Stubs):** 1 day
- **Phase 2 (Telemetry):** 1 day  
- **Phase 3 (Admin UI):** 2 days
- **Phase 4 (AI Integration):** 2 days

**Total:** ~1 week for full implementation

---

## References

- [WordPress Function Reference](https://developer.wordpress.org/reference/functions/)
- [WordPress Core Source](https://github.com/WordPress/WordPress)
- [PHP Tokenizer](https://www.php.net/manual/en/book.tokenizer.php)
