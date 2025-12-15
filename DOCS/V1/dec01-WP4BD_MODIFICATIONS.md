# WordPress 4.9 Modifications for WP4BD

This document tracks all modifications made to the WordPress 4.9 core files to enable integration with Backdrop CMS.

## Purpose

WP4BD runs WordPress themes on Backdrop CMS by:
1. Loading WordPress 4.9 core functions
2. Providing bridge implementations for functions that need Backdrop integration
3. Commenting out WordPress functions that conflict with our bridge implementations

## Philosophy

We aim to use **unmodified WordPress core** wherever possible. Modifications are only made when:
- A WordPress function needs to integrate with Backdrop's systems (e.g., rendering pipeline)
- PHP does not allow function redeclaration (no override mechanism exists)
- The WordPress implementation cannot work in the Backdrop context

## Modified Files

### 1. wordpress-4.9/wp-includes/general-template.php

**Date Modified:** 2025-12-08

**Functions Commented Out:**

#### `wp_head()` (line 2608-2616)
- **Reason:** We provide our own implementation in `backdrop-1.30/themes/wp/functions/hooks.php`
- **Why:** Our version captures hook output and integrates it with Backdrop's head rendering via `backdrop_add_html_head()`
- **Original Implementation:** Simply calls `do_action('wp_head')`
- **Our Implementation:** Calls `do_action('wp_head')`, captures output buffering, and injects into Backdrop's HTML head

#### `wp_footer()` (line 2624-2634)
- **Reason:** We provide our own implementation in `backdrop-1.30/themes/wp/functions/hooks.php`
- **Why:** Integrates with Backdrop's rendering system
- **Original Implementation:** Simply calls `do_action('wp_footer')`
- **Our Implementation:** Calls `do_action('wp_footer')` and integrates with Backdrop

#### `language_attributes()` (line 3608-3616)
- **Reason:** We provide our own implementation in `backdrop-1.30/themes/wp/functions/hooks.php`
- **Why:** Integrates with Backdrop's language and rendering system
- **Original Implementation:** Calls `get_language_attributes()` and echoes result
- **Our Implementation:** Provides proper HTML language attributes for Backdrop

### 2. wordpress-4.9/wp-includes/plugin.php

**Date Modified:** 2025-12-08

**Functions Commented Out:**

This file contains WordPress's hook/filter system. We replace these with our own simplified implementations that don't rely on WordPress's WP_Hook class.

#### `add_filter()` (line 106-113)
- **Reason:** We provide our own implementation in `backdrop-1.30/themes/wp/functions/hooks.php`
- **Why:** WordPress uses WP_Hook class which we don't load; our implementation uses simple arrays
- **Original Implementation:** Uses WP_Hook object for advanced priority/argument handling
- **Our Implementation:** Simpler array-based system that maintains WordPress semantics

#### `has_filter()` (line 131-139)
- **Reason:** We provide our own implementation
- **Why:** WordPress version depends on WP_Hook class methods
- **Our Implementation:** Checks our simple array structure

#### `apply_filters()` (line 176-208)
- **Reason:** We provide our own implementation
- **Why:** WordPress version uses WP_Hook class; we use simple callback execution
- **Our Implementation:** Iterates through registered callbacks and applies them

#### `remove_filter()` (line 271-283)
- **Reason:** We provide our own implementation
- **Why:** WordPress version depends on WP_Hook class
- **Our Implementation:** Removes callbacks from our array structure

#### `add_action()` (line 398-400)
- **Reason:** We provide our own implementation
- **Why:** WordPress version calls add_filter which depends on WP_Hook
- **Our Implementation:** Wraps our add_filter implementation

#### `do_action()` (line 421-456)
- **Reason:** We provide our own implementation
- **Why:** WordPress version uses WP_Hook class
- **Our Implementation:** Executes registered action callbacks with proper argument passing

#### `has_action()` (line 536-538)
- **Reason:** We provide our own implementation
- **Why:** WordPress version calls has_filter which depends on WP_Hook
- **Our Implementation:** Wraps our has_filter implementation

#### `remove_action()` (line 554-556)
- **Reason:** We provide our own implementation
- **Why:** WordPress version calls remove_filter which depends on WP_Hook
- **Our Implementation:** Wraps our remove_filter implementation

#### `current_filter()` (line 343-346)
- **Reason:** We provide our own implementation
- **Why:** Part of our custom hook system; maintains current filter stack
- **Our Implementation:** Returns current filter from $wp_current_filter global

#### `current_action()` (line 355-357)
- **Reason:** We provide our own implementation
- **Why:** Wraps current_filter for action context
- **Our Implementation:** Calls our current_filter implementation

#### `doing_filter()` (line 380-388)
- **Reason:** We provide our own implementation
- **Why:** Part of our custom hook system; checks filter stack
- **Our Implementation:** Checks if a filter is currently being processed

#### `doing_action()` (line 399-401)
- **Reason:** We provide our own implementation
- **Why:** Wraps doing_filter for action context
- **Our Implementation:** Calls our doing_filter implementation

#### `did_action()` (line 505-512)
- **Reason:** We provide our own implementation
- **Why:** Part of our custom hook system; tracks action execution count
- **Our Implementation:** Returns count from $wp_actions global

#### `_wp_filter_build_unique_id()` (line 962-998)
- **Reason:** We provide our own implementation
- **Why:** Internal helper function used by our custom hook system to generate unique callback IDs
- **Our Implementation:** Generates unique identifiers for callbacks (strings, closures, object methods)

#### WP_Hook Class Loading (line 25-38)
- **Reason:** We don't use the WP_Hook class at all
- **Why:** WordPress 4.7+ uses WP_Hook objects for the hook system; we use simple arrays
- **Original Implementation:** Loads `class-wp-hook.php` and initializes hooks as WP_Hook objects
- **Our Implementation:** Commented out the require and WP_Hook initialization; use simple array instead

## Functions We DON'T Modify

The following WordPress functions are used as-is from WordPress 4.9:

### Template Tags
- `the_title()`, `the_content()`, `the_excerpt()` - Content display
- `the_permalink()`, `get_permalink()` - URL generation
- `the_author()`, `get_the_author()` - Author information

### Enqueue System
- `wp_enqueue_style()`, `wp_register_style()` - Style management
- `wp_enqueue_script()`, `wp_register_script()` - Script management

### Localization
- `__()`, `_e()`, `_n()` - Translation functions

### Formatting
- `wpautop()`, `esc_html()`, `esc_attr()`, `esc_url()` - Output formatting

### Conditionals
Most conditionals from WordPress are used, though some are defined by us:
- **From WordPress:** Most query conditionals
- **From WP4BD:** `is_admin()`, `is_multisite()`, `is_ssl()` - defined in template.php

## Our Bridge Implementations

These functions are implemented in `/backdrop-1.30/themes/wp/functions/hooks.php`:

### Hook System
- `add_action()` - Register action callbacks
- `do_action()` - Execute action callbacks
- `has_action()` - Check if action has callbacks
- `remove_action()` - Remove action callbacks
- `did_action()` - Count how many times an action was executed
- `current_action()` - Get the name of the currently executing action
- `doing_action()` - Check if a specific action is currently being processed

### Filter System
- `add_filter()` - Register filter callbacks
- `apply_filters()` - Apply filters to values
- `has_filter()` - Check if filter has callbacks
- `remove_filter()` - Remove filter callbacks
- `current_filter()` - Get the name of the currently executing filter
- `doing_filter()` - Check if a specific filter is currently being processed

### Template Integration
- `wp_head()` - Head rendering integration with Backdrop
- `wp_footer()` - Footer rendering integration with Backdrop
- `language_attributes()` - HTML language attributes

## Classes

We provide these classes to bridge Backdrop ↔ WordPress:

### WP_Post (backdrop-1.30/themes/wp/classes/WP_Post.php)
- Converts Backdrop nodes to WordPress post objects
- Method: `WP_Post::from_node($node)`

### WP_Query (backdrop-1.30/themes/wp/classes/WP_Query.php)
- Provides WordPress query interface
- Populated with posts converted from Backdrop nodes
- Maintains WordPress loop state (`have_posts()`, `the_post()`)

## WordPress Files We Don't Load

Some WordPress core files are intentionally NOT loaded to avoid conflicts or unnecessary dependencies:

- `wp-includes/load.php` - Conflicts with Backdrop's `timer_start()` function
- `wp-includes/post.php` - We have `get_post()` in our WP_Query class
- `wp-includes/query.php` - We have our own WP_Query implementation
- `wp-includes/option.php` - Requires WordPress database; we provide stub implementations instead
- `wp-includes/class-wp-hook.php` - We use simple array-based hooks instead of WP_Hook objects

## Stub Functions We Provide

These functions are defined in `backdrop-1.30/themes/wp/template.php` as simple stubs because we don't use WordPress's database or certain subsystems:

### Option Functions (WordPress Database)
- `get_option($option, $default)` - Always returns default (or false for 'WPLANG')
- `get_site_option($option, $default)` - Always returns default
- `wp_installing()` - Always returns false

These stubs allow WordPress localization (l10n.php) and other core files to load without requiring a WordPress database.

## Automation

**Future Work:** Create a setup script that automatically applies these modifications during installation:

```bash
# Proposed script: bin/patch-wordpress.sh
# 1. Verify WordPress 4.9 checksum
# 2. Apply modifications
# 3. Mark files as modified
# 4. Generate modification report
```

## Maintenance

When updating WordPress (if ever needed):

1. **DO NOT** directly replace modified files
2. Review this document for required modifications
3. Apply modifications to new WordPress version
4. Test thoroughly
5. Update this document with new file locations/line numbers

## Verification

To verify modifications are in place:

```bash
# Check general-template.php modifications (wp_head, wp_footer, language_attributes)
grep -A 2 "WP4BD MODIFICATION" wordpress-4.9/wp-includes/general-template.php

# Check plugin.php modifications (hook system functions)
grep -c "WP4BD MODIFICATION" wordpress-4.9/wp-includes/plugin.php
# Should return 14 (for add_filter, has_filter, apply_filters, remove_filter,
#                      add_action, do_action, has_action, remove_action,
#                      current_filter, current_action, doing_filter, doing_action, did_action,
#                      _wp_filter_build_unique_id)

# Verify functions are commented out
grep -A 1 "^/\*$" wordpress-4.9/wp-includes/plugin.php | grep "^function"
# Should show commented function declarations
```

## Related Documentation

- `/DOCS/ARCHITECTURE.md` - Overall system architecture
- `/DOCS/HOOKS.md` - Hook system documentation
- `/implementation/` - Function implementation details
- `/CLAUDE.MD` - AI assistant context and project overview
