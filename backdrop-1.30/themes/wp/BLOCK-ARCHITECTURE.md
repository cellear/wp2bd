# Block-Based Architecture for WordPress Theme Integration

**Date:** November 21, 2025
**Branch:** `claude/code-review-014RDG3PJ9qUmMQrzSMWrw5v`
**Status:** Implemented - Ready for testing

---

## Summary

This document explains the new block-based architecture for integrating WordPress themes with Backdrop CMS. This approach replaces the previous attempt to use `page.tpl.php` (which doesn't work in Backdrop) with a proper Backdrop Block system.

---

## Why Blocks Instead of page.tpl.php?

### The Problem

The initial implementation tried to use `page.tpl.php` to render WordPress theme output, similar to how Drupal 7 works. However:

1. **Backdrop removed `page.tpl.php`** - This file was removed in favor of the Layout Manager system
2. **Backdrop uses Layouts** - Page structure is defined by layouts, not page templates
3. **Blocks are components** - Content is rendered through blocks placed in layout regions

### The Solution

Create a custom Backdrop module (`wp_content`) that:

1. Provides blocks containing WordPress rendering logic
2. Blocks get placed in layout regions (like "content")
3. Blocks access the current node via Backdrop's context system
4. Blocks convert Backdrop data to WordPress format and delegate to WordPress templates

---

## Architecture Overview

```
Backdrop Request
    ↓
Layout Manager (determines page structure)
    ↓
Layout Region (e.g., "content" region)
    ↓
WordPress Content Block (wp_content module)
    ↓
Block receives node from context
    ↓
Convert node → WP_Post
    ↓
Populate $wp_query global
    ↓
WordPress loop (have_posts(), the_post())
    ↓
Delegate to WordPress template (single.php, index.php, etc.)
    ↓
Return rendered HTML to Backdrop
```

---

## Components

### 1. wp_content Module

**Location:** `/modules/wp_content/`

**Purpose:** Provides Backdrop blocks that render WordPress theme output

**Files:**
- `wp_content.info` - Module definition
- `wp_content.module` - Main module code
- `README.md` - Documentation

**Hooks Implemented:**
- `hook_init()` - Loads WordPress compatibility layer
- `hook_block_info()` - Defines available blocks
- `hook_block_view()` - Renders block content

### 2. wp Theme (Compatibility Layer)

**Location:** `/themes/wp/`

**Purpose:** Contains WordPress compatibility classes and functions

**Key Components:**
- `classes/WP_Post.php` - Converts Backdrop nodes to WordPress post objects
- `classes/WP_Query.php` - Fake WordPress query object
- `functions/` - WordPress function stubs (loop.php, content-display.php, etc.)
- `wp-content/themes/twentyseventeen/` - Actual WordPress theme files

**Status:** Still used by the module, but no longer acts as the active Backdrop theme

### 3. WordPress Theme Files

**Location:** `/themes/wp/wp-content/themes/twentyseventeen/`

**Purpose:** Original WordPress theme (Twenty Seventeen)

**Rendering:** Delegated to by the wp_content block (not yet fully enabled)

---

## How Blocks Access Node Data

In Backdrop, blocks don't use `menu_get_object()` to get the current node. Instead:

### 1. Block Definition (hook_block_info)

```php
function wp_content_block_info() {
  $blocks['wordpress_content'] = array(
    'info' => t('WordPress Content'),
    'description' => t('Renders node content using a WordPress theme.'),
    // This tells Backdrop to pass the node to the block
    'required contexts' => array(
      'node' => 'node',
    ),
  );
  return $blocks;
}
```

### 2. Block Rendering (hook_block_view)

```php
function wp_content_block_view($delta = '', $settings = array(), $contexts = array()) {
  $block = array();

  if ($delta == 'wordpress_content') {
    // Node is available in $contexts['node']
    if (isset($contexts['node'])) {
      $node = $contexts['node'];

      // Convert to WordPress post
      $post = WP_Post::from_node($node);

      // Set up $wp_query
      // ... etc

      $block['content'] = /* rendered HTML */;
    }
  }

  return $block;
}
```

---

## Blocks Provided

### 1. WordPress Content Block

**Machine name:** `wordpress_content`

**Purpose:** Render a single Backdrop node as a WordPress post

**Context required:** Node (passed by Backdrop Layout system)

**Use cases:**
- Individual node pages (`node/123`)
- Single posts
- Single pages

**Rendering flow:**
1. Receives node from `$contexts['node']`
2. Fully loads node with `node_load()`
3. Converts to `WP_Post` via `WP_Post::from_node()`
4. Creates `WP_Query` with single post
5. Sets `$wp_query->is_single = true`
6. Renders using WordPress loop
7. (Future) Delegates to `single.php` or `page.php`

### 2. WordPress Archive/Home Block

**Machine name:** `wordpress_archive`

**Purpose:** Render multiple Backdrop nodes as WordPress posts

**Context required:** None

**Use cases:**
- Home page (shows promoted nodes)
- Archive pages
- Taxonomy listings
- Search results (future)

**Rendering flow:**
1. Queries Backdrop database for published nodes
2. For front page: only promoted nodes
3. Converts each node to `WP_Post`
4. Creates `WP_Query` with multiple posts
5. Sets `$wp_query->is_home = true` or `is_archive = true`
6. Renders using WordPress loop
7. (Future) Delegates to `index.php`, `home.php`, or `archive.php`

---

## Installation & Setup

### Step 1: Enable the Module

```bash
# Via Drush/Bee
bee en wp_content -y

# Or via admin UI
# Navigate to: admin/modules
# Find "WordPress Content" in the WP2BD package
# Check the box and click "Enable"
```

### Step 2: Add Blocks to Layouts

1. Go to **Structure > Layouts** (`admin/structure/layouts`)
2. Click **Edit** on your default layout (or create a new one)
3. In the **Content** region, click **Add block**
4. Search for "WordPress"
5. Add blocks:
   - **WordPress Content** - for node pages
   - **WordPress Archive/Home** - for home page

### Step 3: Configure Block Visibility (Optional)

- Set "WordPress Content" to show only on: `node/*`
- Set "WordPress Archive/Home" to show only on: `<front>`

### Step 4: Clear Caches

```bash
bee cc all
```

---

## Comparison: Old vs New Approach

### Old Approach (Didn't Work)

```
Backdrop Request
    ↓
wp theme is active theme
    ↓
wp_preprocess_page() prepares variables
    ↓
page.tpl.php renders output
    ↓
❌ PROBLEM: page.tpl.php doesn't work in Backdrop!
```

### New Approach (Correct)

```
Backdrop Request
    ↓
Any Backdrop theme (basis_contrib, bartik, etc.)
    ↓
Layout Manager determines structure
    ↓
Layout content region renders blocks
    ↓
wp_content module block renders WordPress output
    ↓
✅ Works with Backdrop's architecture!
```

---

## Key Differences from Drupal 7

| Concept | Drupal 7 | Backdrop CMS |
|---------|----------|--------------|
| Page structure | `page.tpl.php` | Layout Manager |
| Theme system | Themes render pages | Themes + Layouts + Blocks |
| Content access | `menu_get_object()` in preprocess | Context system in blocks |
| Page rendering | Theme controls everything | Blocks render content, layouts arrange them |
| WordPress integration | Could use theme | Must use module + blocks |

---

## Current Implementation Status

### Implemented ✅

- [x] wp_content module structure
- [x] hook_init() loads WordPress compatibility layer
- [x] hook_block_info() defines two blocks
- [x] hook_block_view() renders content
- [x] Single node rendering
- [x] Archive/home rendering
- [x] Node to WP_Post conversion
- [x] WP_Query population
- [x] WordPress loop integration
- [x] Basic HTML output

### Debug Mode 🔍

- Green debug box shows content preview
- Direct HTML output instead of template delegation
- Visible for troubleshooting

### Not Yet Implemented ❌

- [ ] WordPress template hierarchy delegation
- [ ] Header/footer integration (`wp_head()`, `wp_footer()`)
- [ ] Sidebar rendering
- [ ] Widget support
- [ ] Menu integration
- [ ] Enqueue scripts/styles properly

---

## Next Steps

1. **Test the module**
   - Enable wp_content module
   - Add blocks to layouts
   - Visit node pages and home page
   - Verify content renders

2. **Enable template delegation**
   - Uncomment template hierarchy code
   - Test with Twenty Seventeen templates
   - Debug any template loading issues

3. **Remove debug output**
   - Once rendering works, remove green debug box
   - Clean up verbose logging

4. **Add header/footer**
   - Integrate `wp_head()` and `wp_footer()`
   - May need wrapper template or layout

5. **Test with more content**
   - Different node types
   - More WordPress themes
   - Edge cases

---

## Example Block Placement

### Single Node Page

```
┌─────────────────────────────────────┐
│           Header Region             │
├─────────────────────────────────────┤
│                                     │
│  Content Region:                    │
│  ┌───────────────────────────────┐  │
│  │ WordPress Content Block       │  │
│  │                               │  │
│  │ Renders node as WP post       │  │
│  │ using single.php or page.php  │  │
│  └───────────────────────────────┘  │
│                                     │
├─────────────────────────────────────┤
│           Footer Region             │
└─────────────────────────────────────┘
```

### Home Page

```
┌─────────────────────────────────────┐
│           Header Region             │
├─────────────────────────────────────┤
│                                     │
│  Content Region:                    │
│  ┌───────────────────────────────┐  │
│  │ WordPress Archive/Home Block  │  │
│  │                               │  │
│  │ Renders multiple nodes as     │  │
│  │ posts using index.php         │  │
│  └───────────────────────────────┘  │
│                                     │
├─────────────────────────────────────┤
│           Footer Region             │
└─────────────────────────────────────┘
```

---

## Troubleshooting

### Block doesn't appear

**Check:**
- Module is enabled (`bee pm-list --type=module --status=enabled | grep wp_content`)
- Block is added to layout at `admin/structure/layouts`
- Block visibility conditions are correct

### "No posts found"

**Check:**
- WordPress compatibility layer is loaded
- `$wp_query` is populated
- `have_posts()` returns true
- Check Backdrop dblog for errors

### "Missing bundle property"

**This is handled** - Module reloads nodes with `node_load()` and has fallback logic

### Node data missing

**Check:**
- Block is receiving node from context
- `isset($contexts['node'])` is true
- Node is being fully loaded

---

## Files Changed/Created

### New Files

1. `/modules/wp_content/wp_content.info`
2. `/modules/wp_content/wp_content.module`
3. `/modules/wp_content/README.md`
4. `/themes/wp/BLOCK-ARCHITECTURE.md` (this file)

### Modified Files

None yet - the wp theme remains unchanged. Its code is now used by the wp_content module instead of being the active theme.

---

## References

- [Backdrop Blocks API](https://docs.backdropcms.org/documentation/working-with-blocks)
- [Backdrop Layout System](https://docs.backdropcms.org/documentation/layouts)
- [Backdrop Context System](https://docs.backdropcms.org/documentation/context-system)
- [html.tpl.php removal change record](https://docs.backdropcms.org/change-records/htmltplphp-removed)

---

## Credits

**Architecture designed by:** Claude (Anthropic AI)
**Guidance from:** User (Backdrop expert)
**Inspiration:** Backdrop contrib theme_template, layout_test module

---

## License

GPL v2 or later
