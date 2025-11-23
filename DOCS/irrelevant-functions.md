# IRRELEVANT FUNCTIONS - Can Be Ignored or Logged Only

Functions that are NOT needed for theme compatibility layer. These can either be completely ignored or stubbed with simple logging.

---

## CATEGORY 1: WORDPRESS VERSION & COMPATIBILITY CHECKS

Functions only needed for WordPress version checking. Irrelevant in Backdrop context.

| Function | Usage Count | Why Irrelevant | Stub Action |
|----------|-------------|----------------|-------------|
| `version_compare()` | 1 | WP version check for back-compat | Return TRUE (assume compatible) |
| `switch_theme()` | 1 | Used in back-compat to revert theme | No-op (log warning) |
| `wp_die()` | 2 | Kills execution on version mismatch | No-op (log error) |

**Located in:** `inc/back-compat.php`

**Why Irrelevant:**
- These functions exist solely to prevent the theme from running on WordPress < 4.7
- In Backdrop, we control the environment - no version checking needed
- The entire `back-compat.php` file can be EXCLUDED from the compatibility layer

**Stub Implementation:**
```php
// Option 1: Don't implement at all
// Option 2: Log and return safe values
function wp_die($message, $title = '', $args = array()) {
  watchdog('wp2bd_ignored', 'wp_die() called: @msg', array('@msg' => $message), WATCHDOG_ERROR);
  // Don't actually die - just log
}
```

---

## CATEGORY 2: PINGBACK & TRACKBACK FUNCTIONS

Legacy blog-to-blog communication. Rarely used in modern sites.

| Function | Usage Count | Why Irrelevant | Stub Action |
|----------|-------------|----------------|-------------|
| `pings_open()` | 1 | Checks if pingbacks/trackbacks enabled | Return FALSE |
| `twentyseventeen_pingback_header()` | N/A | Outputs pingback URL header | No-op |

**Located in:** `functions.php` line 384-388

**Why Irrelevant:**
- Pingbacks are a WordPress-specific feature for blog cross-linking
- Largely obsolete (disabled by default in modern WordPress)
- Not a priority for Backdrop migration
- Can safely ignore without affecting visual display

**Stub Implementation:**
```php
function pings_open() {
  return FALSE; // Pingbacks disabled
}

// Don't implement twentyseventeen_pingback_header() at all
// Or create empty function:
function twentyseventeen_pingback_header() {
  // No-op
}
```

---

## CATEGORY 3: ADMIN-ONLY / CUSTOMIZER PREVIEW FUNCTIONS

Functions that only matter when viewing Customizer or admin area.

| Function | Usage Count | Why Irrelevant | Stub Action |
|----------|-------------|----------------|-------------|
| `is_customize_preview()` | 8+ | Checks if in WP Customizer | Return FALSE |
| `is_admin()` | 1 | Checks if in admin area | Return FALSE |
| `is_preview()` | 1 | Checks if previewing unpublished content | Return FALSE |

**Why Irrelevant:**
- Theme compatibility layer is for FRONT-END rendering only
- Admin/Customizer features not needed for basic PoC
- Can always return FALSE without affecting page display

**Stub Implementation:**
```php
function is_customize_preview() {
  return FALSE; // Never in customizer
}

function is_admin() {
  return FALSE; // Never in admin area
}

function is_preview() {
  return FALSE; // No preview mode
}
```

---

## CATEGORY 4: WORDPRESS STARTER CONTENT

Functions related to demo content for new WP installations.

| Function | Usage Count | Why Irrelevant | Stub Action |
|----------|-------------|----------------|-------------|
| `add_theme_support('starter-content', ...)` | 1 | Populates demo content on theme activation | Ignore |

**Located in:** `functions.php` lines 94-213

**Why Irrelevant:**
- Starter content is for NEW WordPress installations to show theme features
- Backdrop site will already have content
- This entire block of configuration can be ignored
- Only affects initial setup, not ongoing rendering

**Implementation:**
- Simply don't register starter content support
- Or register empty array: `add_theme_support('starter-content', array())`

---

## CATEGORY 5: WORDPRESS-SPECIFIC THEME SUPPORT FLAGS

Theme support declarations that don't map to Backdrop or aren't essential.

| Function | Usage | Why Irrelevant | Stub Action |
|----------|-------|----------------|-------------|
| `add_theme_support('automatic-feed-links')` | 1 | Auto-add RSS feed links | Ignore or manual implementation |
| `add_theme_support('customize-selective-refresh-widgets')` | 1 | Customizer feature | Ignore |
| `add_theme_support('wp-block-styles')` | Referenced | Gutenberg block editor | Ignore (pre-Gutenberg theme) |

**Why Irrelevant:**
- These are WordPress-specific features flags
- Most relate to editor, customizer, or admin features
- Don't affect front-end content rendering
- Can be safely ignored in initial PoC

**Implementation:**
- Don't call these `add_theme_support()` variants
- Or create no-op stubs that log but don't error

---

## CATEGORY 6: ADVANCED CUSTOMIZER API

Complex Customizer integration functions. Not needed for PoC.

| Function | Usage Count | Why Irrelevant | Stub Action |
|----------|-------------|----------------|-------------|
| `$wp_customize->get_setting()` | 3 | Modify customizer settings | No-op |
| `$wp_customize->add_setting()` | 6+ | Register customizer options | No-op |
| `$wp_customize->add_control()` | 7+ | Add customizer controls | No-op |
| `$wp_customize->add_section()` | 1 | Add customizer section | No-op |
| `$wp_customize->selective_refresh->add_partial()` | 6+ | Selective refresh | No-op |
| `WP_Customize_Color_Control` | 1 | Color picker control | N/A |

**Located in:** `inc/customizer.php`

**Why Irrelevant:**
- WordPress Customizer is admin/preview feature
- Not needed for front-end theme rendering
- Backdrop has its own configuration/admin system
- Can defer to later phase or skip entirely

**Implementation:**
- Don't implement customizer.php functions at all
- Or create stub `$wp_customize` object that accepts calls but does nothing
```php
class WP_Customize_Manager_Stub {
  function get_setting($id) { return new stdClass(); }
  function add_setting($id, $args) { /* no-op */ }
  function add_control($id, $args) { /* no-op */ }
  function add_section($id, $args) { /* no-op */ }
}
```

---

## CATEGORY 7: RESOURCE HINTS & PERFORMANCE OPTIMIZATIONS

Functions for performance hints. Nice but not essential.

| Function | Usage Count | Why Irrelevant | Stub Action |
|----------|-------------|----------------|-------------|
| `twentyseventeen_resource_hints()` | N/A | Adds DNS prefetch for Google Fonts | Can ignore |
| Filter: `wp_resource_hints` | 1 | Filters resource hints | Can ignore |

**Located in:** `functions.php` lines 296-305

**Why Irrelevant:**
- Performance optimization, not functional requirement
- Page will render fine without DNS prefetch
- Can implement later if needed
- Low priority for PoC

**Implementation:**
- Don't hook the filter
- Fonts will still load, just without prefetch optimization

---

## CATEGORY 8: CONDITIONAL CONTENT LOADING

Functions that conditionally load features. Can ignore if features aren't implemented.

| Function | Usage Count | Why Irrelevant | Stub Action |
|----------|-------------|----------------|-------------|
| `wp_script_add_data()` | 1 | Add IE conditional comments to scripts | Ignore (modern browsers) |
| `wp_style_add_data()` | 2 | Add IE conditional comments to styles | Ignore (modern browsers) |

**Located in:** `functions.php`

**Why Irrelevant:**
- IE8/IE9 support (outdated browsers)
- Modern Backdrop sites don't need IE8 support
- Can safely ignore conditional loading

**Implementation:**
- Don't implement these functions
- IE-specific CSS/JS won't load (acceptable)

---

## CATEGORY 9: TAG CLOUD CUSTOMIZATION

Widget-specific customization. Only matters if widgets implemented.

| Function | Usage Count | Why Irrelevant | Stub Action |
|----------|-------------|----------------|-------------|
| `twentyseventeen_widget_tag_cloud_args()` | N/A | Modifies tag cloud widget styling | Ignore |
| Filter: `widget_tag_cloud_args` | 1 | Tag cloud filter | Ignore |

**Located in:** `functions.php` lines 553-560

**Why Irrelevant:**
- Only affects tag cloud widget appearance
- Widgets are "Nice-to-Have" category
- Tag cloud specifically is rarely used
- Won't affect page rendering

**Implementation:**
- Don't hook the filter
- If tag cloud implemented later, use default styling

---

## CATEGORY 10: SOCIAL LINKS ICONS

Social media menu integration. Enhancement, not core feature.

| Function | Usage Count | Why Irrelevant | Stub Action |
|----------|-------------|----------------|-------------|
| `twentyseventeen_social_links_icons()` | N/A | Returns array of social media platforms | Can ignore |
| `twentyseventeen_nav_menu_social_icons()` | N/A | Displays SVG icons in social menu | Can ignore |
| Filter: `twentyseventeen_social_links_icons` | 1 | Filter social icons array | Ignore |

**Located in:** `inc/icon-functions.php`

**Why Irrelevant:**
- Social links menu is optional feature
- Icons enhance appearance but aren't required
- Menu will work without icons, just show text
- Can implement later for polish

**Implementation:**
- Return empty array from `twentyseventeen_social_links_icons()`
- Social menu displays as regular text links

---

## CATEGORY 11: DROPDOWN MENU ICONS

Adds dropdown arrows to menu items. Visual enhancement only.

| Function | Usage Count | Why Irrelevant | Stub Action |
|----------|-------------|----------------|-------------|
| `twentyseventeen_dropdown_icon_to_menu_link()` | N/A | Adds dropdown icon to parent menu items | Can ignore |
| Filter: `nav_menu_item_title` | 1 | Filters menu item title | Ignore |

**Located in:** `inc/icon-functions.php`

**Why Irrelevant:**
- Cosmetic enhancement (arrow icons)
- Menus function without icons
- CSS can handle dropdown indicators
- Low priority

**Implementation:**
- Don't hook the filter
- Menus display without dropdown arrows (acceptable)

---

## CATEGORY 12: JAVASCRIPT DETECTION

Client-side JavaScript detection. Minor enhancement.

| Function | Usage Count | Why Irrelevant | Stub Action |
|----------|-------------|----------------|-------------|
| `twentyseventeen_javascript_detection()` | N/A | Replaces 'no-js' class with 'js' | Can ignore initially |

**Located in:** `functions.php` lines 376-378

**Why Irrelevant:**
- Progressive enhancement technique
- Just swaps CSS class based on JS availability
- Theme works without this
- Can add later for progressive enhancement

**Implementation:**
- Don't hook to `wp_head`
- Or implement simple version
- Body class remains 'no-js' (acceptable for PoC)

---

## CATEGORY 13: FRONT PAGE TEMPLATE FILTERING

Specific front page logic. Can simplify.

| Function | Usage Count | Why Irrelevant | Stub Action |
|----------|-------------|----------------|-------------|
| `twentyseventeen_front_page_template()` | N/A | Prevents front-page.php on blog index | Can ignore |
| Filter: `frontpage_template` | 1 | Filter front page template | Ignore |

**Located in:** `functions.php` lines 539-541

**Why Irrelevant:**
- Edge case handling for WordPress template hierarchy
- Prevents confusion between static front page and blog index
- Backdrop has simpler front page handling
- Can use standard template logic

**Implementation:**
- Don't hook the filter
- Use Backdrop's front page handling

---

## CATEGORY 14: PASSWORD PROTECTED POSTS

Password protection feature. Uncommon use case.

| Function | Usage Count | Why Irrelevant | Stub Action |
|----------|-------------|----------------|-------------|
| `post_password_required()` | 1 | Check if post requires password | Return FALSE |

**Located in:** `comments.php`

**Why Irrelevant:**
- Password-protected posts are rare
- WordPress-specific feature
- Not in PoC scope
- Can always return FALSE (no password required)

**Stub Implementation:**
```php
function post_password_required($post = null) {
  return FALSE; // No password protection in Backdrop
}
```

---

## CATEGORY 15: WORDPRESS QUERY VARIABLE MANIPULATION

Low-level query modification. Can use simpler approach.

| Function | Usage Count | Why Irrelevant | Stub Action |
|----------|-------------|----------------|-------------|
| `set_query_var()` | 1 | Set WordPress query variable | Can ignore |

**Located in:** `inc/template-tags.php`

**Why Irrelevant:**
- Used for passing data to template parts
- Can use Backdrop's variables or global scope instead
- WordPress-specific mechanism
- Not essential for template rendering

**Implementation:**
- Use Backdrop's approach (pass variables directly or use globals)

---

## CATEGORY 16: MULTI-AUTHOR BLOG DETECTION

Specific feature detection. Low value.

| Function | Usage Count | Why Irrelevant | Stub Action |
|----------|-------------|----------------|-------------|
| `is_multi_author()` | 1 | Check if blog has multiple authors | Return FALSE |

**Located in:** `inc/template-functions.php`

**Why Irrelevant:**
- Only used to add 'group-blog' body class
- Cosmetic distinction
- Not critical for functionality
- Can always return FALSE (single author)

**Stub Implementation:**
```php
function is_multi_author() {
  return FALSE; // Treat as single-author blog
}
```

---

## CATEGORY 17: POST TYPE SUPPORT CHECKING

Feature support checking. Can simplify.

| Function | Usage Count | Why Irrelevant | Stub Action |
|----------|-------------|----------------|-------------|
| `post_type_supports()` | 1 | Check if post type supports feature | Return TRUE |

**Located in:** `comments.php`

**Why Irrelevant:**
- Used to check if post type supports comments
- Can assume all node types support comments
- Or check more directly with Backdrop's node type settings

**Stub Implementation:**
```php
function post_type_supports($post_type, $feature) {
  // Assume all types support all features
  watchdog('wp2bd_ignored', 'post_type_supports() called');
  return TRUE;
}
```

---

## CATEGORY 18: ERROR DETECTION

WordPress-specific error handling.

| Function | Usage Count | Why Irrelevant | Stub Action |
|----------|-------------|----------------|-------------|
| `is_wp_error()` | 1 | Check if value is WP_Error object | Return FALSE |

**Located in:** `inc/template-tags.php`

**Why Irrelevant:**
- WordPress uses `WP_Error` objects for error handling
- Backdrop uses different error handling
- Can assume no WP_Error objects exist in Backdrop

**Stub Implementation:**
```php
function is_wp_error($thing) {
  return FALSE; // No WP_Error objects in Backdrop
}
```

---

## CATEGORY 19: VIDEO/AUDIO HEADER CONTROLS

Custom header video feature. Advanced, low priority.

| Function | Usage Count | Why Irrelevant | Stub Action |
|----------|-------------|----------------|-------------|
| `twentyseventeen_video_controls()` | N/A | Customizes video header play/pause text | Can ignore |
| Filter: `header_video_settings` | 1 | Filter video controls | Ignore |

**Located in:** `inc/custom-header.php`

**Why Irrelevant:**
- Video headers are advanced feature
- Requires custom header support (also "Nice-to-Have")
- Cosmetic customization of video controls
- Very low priority

**Implementation:**
- Don't hook filter
- If video headers implemented, use default controls

---

## FILES THAT CAN BE COMPLETELY EXCLUDED

These entire files can be skipped in the initial compatibility layer:

### 1. `inc/back-compat.php` ✂️ SKIP ENTIRELY
- **Purpose:** WordPress version checking
- **Why Skip:** Not applicable to Backdrop
- **Impact:** None - version checking not needed

### 2. Most of `inc/customizer.php` ✂️ SKIP MOST OF IT
- **Purpose:** WordPress Customizer integration
- **Why Skip:** Admin/preview feature, not front-end rendering
- **Impact:** Theme settings won't be customizable (can hardcode defaults)
- **Keep:** `get_theme_mod()` function (implement simple version)

### 3. Most of `inc/custom-header.php` ✂️ DEFER TO LATER
- **Purpose:** Custom header image/video feature
- **Why Skip:** Enhancement, not core functionality
- **Impact:** No custom headers initially (acceptable)

### 4. Most of `inc/icon-functions.php` ✂️ PARTIAL SKIP
- **Keep:** `twentyseventeen_get_svg()` - used for icons
- **Skip:** Social menu integration, dropdown icons (enhancements)

### 5. `inc/color-patterns.php` ✂️ CAN SIMPLIFY
- **Purpose:** Generates custom color CSS
- **Why Skip Initially:** Theme works with default colors
- **Impact:** No color customization (can hardcode light/dark schemes)
- **Alternative:** Include pre-generated CSS for light & dark themes

---

## SUMMARY: IGNORE SAFELY

### Complete No-Op (Don't Implement)
- `switch_theme()`, `wp_die()` (back-compat)
- `pings_open()`, `twentyseventeen_pingback_header()` (pingbacks)
- `wp_script_add_data()`, `wp_style_add_data()` (IE conditionals)
- All Customizer API methods
- `twentyseventeen_resource_hints()` (performance hint)
- Social icons, dropdown icons (cosmetic)
- `is_wp_error()` (WP-specific errors)

### Stub with Return FALSE (Minimal Implementation)
- `is_customize_preview()` → FALSE
- `is_admin()` → FALSE
- `is_preview()` → FALSE
- `post_password_required()` → FALSE
- `is_multi_author()` → FALSE

### Stub with Return TRUE (Accept All)
- `post_type_supports()` → TRUE

### Entire Files to Skip
- `inc/back-compat.php` - 100% WordPress version checking
- Most of `inc/customizer.php` - Admin features
- Most of `inc/custom-header.php` - Advanced feature

---

## LOGGING STRATEGY FOR IGNORED FUNCTIONS

For ignored functions that might be called, create logging stubs:

```php
/**
 * WordPress compatibility: IGNORED FUNCTION
 * This function is not needed for Backdrop compatibility.
 */
function {function_name}({$params}) {
  // Only log in debug mode to avoid log spam
  if (variable_get('wp2bd_debug', FALSE)) {
    watchdog('wp2bd_ignored', '@func called (ignored)',
      array('@func' => __FUNCTION__),
      WATCHDOG_DEBUG
    );
  }
  return {$safe_default};
}
```

---

**TOTAL IRRELEVANT FUNCTIONS:** ~40 functions/features
**FILES TO EXCLUDE:** 1-2 complete files
**STUB TIME:** 2-3 hours (create simple no-op stubs with logging)
**IMPLEMENTATION TIME:** 0 hours (by definition, these are ignored)

**PRIORITY:** Lowest - Only stub if errors occur, otherwise don't implement at all
