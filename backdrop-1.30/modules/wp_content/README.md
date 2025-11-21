# WordPress Content Block Module

**Version:** 1.x-1.0.0
**Package:** WP2BD

## Overview

This module provides Backdrop blocks that render node content using WordPress themes. It works in conjunction with the `wp` theme's WordPress compatibility layer to enable WordPress themes to run on Backdrop CMS.

## Architecture

Unlike the Drupal 7 approach which used `page.tpl.php`, Backdrop CMS uses a **Layout Manager** system where content is rendered through **Blocks** placed in layout regions. This module provides the blocks that contain the WordPress rendering logic.

### How It Works

1. **Initialization** (`wp_content_init()`)
   - Loads WordPress compatibility layer from `/themes/wp/`
   - Initializes WordPress globals (`$wp_query`, `$post`, etc.)
   - Loads WP_Post and WP_Query classes
   - Loads WordPress function compatibility stubs

2. **Block Definition** (`wp_content_block_info()`)
   - **WordPress Content** - For single node pages (requires 'node' context)
   - **WordPress Archive/Home** - For home and archive pages (no context required)

3. **Block Rendering** (`wp_content_block_view()`)
   - Gets Backdrop node from block context
   - Converts node to `WP_Post` object
   - Sets up `$wp_query` global with fake WordPress data
   - Delegates rendering to WordPress template hierarchy

## Installation

1. **Enable the module:**
   ```bash
   drush en wp_content -y
   # or
   bee en wp_content -y
   # or via admin UI at admin/modules
   ```

2. **Add blocks to layouts:**
   - Go to **Structure > Layouts** (`admin/structure/layouts`)
   - Edit your default layout (or create a custom one)
   - In the **Content** region, click "Add block"
   - Choose:
     - **WordPress Content** for node pages
     - **WordPress Archive/Home** for listing pages
   - Save the layout

3. **Configure block visibility** (optional):
   - Set the "WordPress Content" block to show only on node pages
   - Set the "WordPress Archive/Home" block to show only on front page

## Blocks Provided

### WordPress Content Block

- **Machine name:** `wordpress_content`
- **Purpose:** Renders a single Backdrop node using WordPress theme
- **Context required:** Node
- **Use on:** Individual node pages (node/123)

### WordPress Archive/Home Block

- **Machine name:** `wordpress_archive`
- **Purpose:** Renders multiple Backdrop nodes as WordPress posts
- **Context required:** None
- **Use on:** Home page, archive pages, taxonomy listings

## Rendering Process

### Single Node

1. Block receives node from Backdrop Layout context
2. Node is fully loaded with `node_load()` to ensure all properties exist
3. Node is converted to `WP_Post` object via `WP_Post::from_node()`
4. `WP_Query` is populated with single post
5. WordPress loop functions (`have_posts()`, `the_post()`) work with this data
6. WordPress template hierarchy determines which template to use
7. Template output is captured and returned as block content

### Archive/Home

1. Block queries Backdrop database for published nodes
2. For front page: only promoted nodes (respects `default_nodes_main` setting)
3. For archives: all published nodes
4. Each node is converted to `WP_Post` object
5. `WP_Query` is populated with multiple posts
6. WordPress loop iterates through posts
7. Archive template is used for rendering

## Current Implementation Status

### Working ✅
- Module structure and hooks
- Block definitions
- WordPress compatibility layer loading
- Node to WP_Post conversion
- WP_Query setup with fake data
- WordPress loop functions
- Basic content rendering

### Debug Mode 🔍
- Currently outputs green debug box showing content preview
- Will be removed once template delegation is fully working

### Not Yet Implemented ❌
- WordPress template hierarchy delegation (commented out)
- WordPress header/footer integration
- Sidebar rendering
- Widget support

## Files

```
modules/wp_content/
├── wp_content.info         # Module definition
├── wp_content.module       # Main module code
└── README.md              # This file
```

## Dependencies

- **layout** - Backdrop core module for Layout Manager
- **wp theme** - Must exist at `/themes/wp/` with WordPress compatibility layer

## WordPress Compatibility Layer

This module depends on the `wp` theme which contains:

- `/themes/wp/classes/` - WP_Post, WP_Query classes
- `/themes/wp/functions/` - WordPress function stubs
- `/themes/wp/wp-content/themes/twentyseventeen/` - WordPress theme files

## Development

### Adding Support for More WordPress Templates

Edit `_wp_content_delegate_to_template()` to enable the commented-out template hierarchy logic:

```php
// Determine which WordPress template to use
$wp_template = 'index.php';

if (is_single()) {
  if (file_exists(get_template_directory() . '/single.php')) {
    $wp_template = 'single.php';
  }
}
// ... etc
```

### Removing Debug Output

Remove the green debug box code in `_wp_content_delegate_to_template()`:

```php
// Remove these lines:
echo '<div style="background: #0f0; ...">...';
```

### Adding More Blocks

Add new entries to `wp_content_block_info()` and handle them in `wp_content_block_view()`.

## Troubleshooting

### "Missing bundle property on entity of type node"

The module reloads nodes with `node_load()` and has fallback logic to query the database for the `type` property. If you still see this error, check that:
- The node exists in the database
- The node table has a valid `type` value

### "No posts found"

Check that:
- `$wp_query` is being populated correctly
- WordPress compatibility functions are loaded
- `have_posts()` function exists and works

### Block not appearing

Check that:
- Module is enabled (`drush en wp_content -y`)
- Block is added to layout at `admin/structure/layouts`
- Block visibility conditions are correct
- Layout is set as default or active for the path

## Future Enhancements

1. **Full template delegation** - Complete WordPress template hierarchy
2. **Admin UI** - Select WordPress theme from admin panel
3. **Multiple theme support** - Switch between WordPress themes
4. **Sidebar blocks** - Render WordPress sidebars as Backdrop blocks
5. **Widget integration** - Support WordPress widgets in Backdrop
6. **Menu integration** - Map Backdrop menus to WordPress menus

## License

GPL v2 or later

## Support

This module is part of the WP2BD (WordPress to Backdrop) compatibility project.
