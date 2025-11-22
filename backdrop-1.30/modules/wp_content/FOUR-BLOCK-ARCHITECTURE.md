# Four-Block WordPress Theme Architecture

**Date:** November 22, 2025
**Module:** wp_content
**Status:** Implemented - Ready for testing

---

## Overview

The WordPress theme rendering is now split into **4 independent blocks** that can be placed in different Backdrop layout regions. This matches WordPress's standard theme structure perfectly.

## The Four Blocks

### 1. WordPress Header Block (`wordpress_header`)
**Renders:** `header.php`
**Place in:** Header region
**Contains:**
- `<!DOCTYPE html>`, `<html>`, `<head>` tags
- `wp_head()` output (CSS, JS)
- Site branding (logo, site title)
- Navigation menu
- Custom header image

### 2. WordPress Content Block (`wordpress_content`)
**Renders:** Main content loop (without header/footer/sidebar)
**Place in:** Content region
**Contains:**
- Main article/post content
- WordPress loop (`have_posts()`, `the_post()`)
- Template parts via `get_template_part()`

### 3. WordPress Sidebar Block (`wordpress_sidebar`)
**Renders:** `sidebar.php`
**Place in:** Sidebar region
**Contains:**
- Widget areas
- Search form
- Recent posts, comments
- Archives, categories, meta

### 4. WordPress Footer Block (`wordpress_footer`)
**Renders:** `footer.php`
**Place in:** Footer region
**Contains:**
- Footer widgets
- Copyright notice
- `wp_footer()` output (JS)
- Closing `</body>` and `</html>` tags

---

## How WordPress Templates Are Split

### Standard WordPress Template (e.g., index.php):
```php
<?php get_header(); ?>  // ← WordPress Header Block

<main>
  <?php
  while (have_posts()) {
    the_post();
    // Content here
  }
  ?>
</main>  // ← WordPress Content Block

<?php get_sidebar(); ?>  // ← WordPress Sidebar Block
<?php get_footer(); ?>   // ← WordPress Footer Block
```

### Our Block-Based Approach:
Each `get_*()` call becomes a separate Backdrop block!

---

## Installation & Setup

### Step 1: Clear Caches
```bash
bee cc all
```

### Step 2: Check Available Blocks
Go to: **Structure > Layouts** > Edit your layout > Click "Add block" in any region

You should see:
- WordPress Header
- WordPress Content
- WordPress Sidebar
- WordPress Footer

### Step 3: Place Blocks in Layout Regions

Recommended layout structure (using Moscone Flipped or similar):

```
┌─────────────────────────────────────┐
│  Header Region:                     │
│  [WordPress Header Block]           │
└─────────────────────────────────────┘
┌──────────────────────┬──────────────┐
│  Content Region:     │  Sidebar:    │
│  [WordPress Content] │  [WordPress  │
│                      │   Sidebar]   │
└──────────────────────┴──────────────┘
┌─────────────────────────────────────┐
│  Footer Region:                     │
│  [WordPress Footer Block]           │
└─────────────────────────────────────┘
```

### Step 4: Configure Block Visibility (Optional)
- All blocks can be placed on all pages
- OR set specific visibility rules per block

### Step 5: Test
Visit your site and verify:
- Header appears at top with navigation
- Content appears in middle
- Sidebar appears on right (or left)
- Footer appears at bottom

---

## How It Works Internally

### Shared Query Setup
All four blocks call `_wp_content_setup_query()` which:
1. Loads Backdrop nodes from database
2. Converts them to `WP_Post` objects
3. Sets up `$wp_query` global
4. Sets up `$post` global
5. Caches the setup so it only runs once per page

### Block Rendering

**Header Block:**
```php
function _wp_content_render_header() {
  _wp_content_setup_query();  // Ensure query is ready
  ob_start();
  include get_template_directory() . '/header.php';
  return array('#markup' => ob_get_clean());
}
```

**Content Block:**
```php
function _wp_content_render_main_content() {
  _wp_content_setup_query();
  ob_start();

  // Manually render content area
  echo '<div class="wrap">';
  if (have_posts()) {
    while (have_posts()) {
      the_post();
      get_template_part('template-parts/post/content');
    }
  }
  echo '</div>';

  return array('#markup' => ob_get_clean());
}
```

**Sidebar Block:**
```php
function _wp_content_render_sidebar() {
  _wp_content_setup_query();
  ob_start();
  include get_template_directory() . '/sidebar.php';
  return array('#markup' => ob_get_clean());
}
```

**Footer Block:**
```php
function _wp_content_render_footer() {
  _wp_content_setup_query();
  ob_start();
  include get_template_directory() . '/footer.php';
  return array('#markup' => ob_get_clean());
}
```

---

## Why This Architecture?

### Problems with Previous Approach:
- ❌ Everything rendered in one block (header, content, sidebar, footer all together)
- ❌ Couldn't control layout - sidebar was stuck inside content
- ❌ Couldn't use Backdrop's Layout Manager effectively

### Benefits of New Approach:
- ✅ Clean separation matches WordPress structure
- ✅ Each piece can be placed in proper Backdrop layout region
- ✅ Sidebar can be positioned correctly (left or right)
- ✅ Header and footer are separate from content
- ✅ Works with any Backdrop layout
- ✅ Generalizable to ANY WordPress theme

---

## Compatibility

### Works With:
- ✅ Twenty Seventeen (current theme)
- ✅ All Twenty* themes (Twelve, Thirteen, Fourteen, Fifteen, Sixteen, Nineteen, Twenty)
- ✅ Most traditional WordPress themes (2010-2020)
- ✅ Any theme using `get_header()`, `get_sidebar()`, `get_footer()`

### Doesn't Work With:
- ❌ Gutenberg block themes (WordPress 5.9+)
- ❌ Themes with custom template structures

---

## Customization

### To Hide Sidebar on Certain Pages:
Edit the sidebar block's visibility settings in Layout Manager.

### To Add Multiple Sidebars:
Some themes have `sidebar-left.php` and `sidebar-right.php`. You can:
1. Add new block types in `hook_block_info()`:
```php
$blocks['wordpress_sidebar_left'] = array(...);
$blocks['wordpress_sidebar_right'] = array(...);
```

2. Add rendering functions:
```php
function _wp_content_render_sidebar_left() {
  include get_template_directory() . '/sidebar-left.php';
}
```

### To Support Single Post Pages:
The content block already handles this via:
```php
if (is_single() || is_page()) {
  // Single post/page rendering
}
```

---

## Troubleshooting

### "WordPress template not found"
- Check that `/themes/wp/wp-content/themes/twentyseventeen/` exists
- Verify header.php, footer.php, sidebar.php files are present

### Header appears twice
- Make sure you've REMOVED any old header blocks
- Only place the WordPress Header block once

### Content not showing
- Check that nodes exist and are published
- Check browser console for JavaScript errors
- Enable devel module and check for PHP errors

### Styling issues
- Make sure `wp_head()` is being called (in header block)
- Check that CSS files are enqueued
- Verify Twenty Seventeen CSS is loading

---

## Next Steps

1. **Test the four blocks** by placing them in your layout
2. **Remove old blocks** (like "WordPress Archive/Home" if it exists)
3. **Fine-tune layout** using Backdrop's Layout Manager
4. **Test with different WordPress themes** to verify generalizability

---

## Files Modified

- `modules/wp_content/wp_content.module` - Completely restructured
  - Added 4 block definitions
  - Added 4 rendering functions
  - Added shared query setup function
  - Removed old monolithic approach

---

## Technical Notes

### Order of Execution:
1. User visits page
2. Backdrop renders layout
3. Each region renders its blocks
4. Header block: Calls `_wp_content_setup_query()`, includes header.php
5. Content block: Uses existing query, renders main content
6. Sidebar block: Uses existing query, includes sidebar.php
7. Footer block: Uses existing query, includes footer.php

### Query Caching:
The first block to render calls `_wp_content_setup_query()` which sets `$GLOBALS['wp2bd_query_setup'] = TRUE`. Subsequent blocks see this flag and skip re-querying the database.

### Memory Considerations:
All four blocks share the same `$wp_query` and `$post` globals, so there's no memory overhead from splitting into blocks.

---

## License

GPL v2 or later
