# WordPress Global Variables Reference

**WP4BD-V2-030: Identify Critical WordPress Globals**

This document catalogs all critical WordPress global variables that need to be initialized for WordPress themes to function correctly in the WP4BD V2 architecture.

## Database & Query Globals

### `$wpdb`
- **Type:** `wpdb` object
- **Description:** WordPress database abstraction object. In WP4BD V2, this is replaced by our custom `wpdb` class in `db.php` drop-in that intercepts queries and maps them to Backdrop.
- **Initialized in:** `wp-includes/wp-db.php` (via `require_wp_db()`)
- **WP4BD Status:** ✅ Replaced by db.php drop-in (WP4BD-V2-020)

### `$wp_query`
- **Type:** `WP_Query` object
- **Description:** Main WordPress query object. Contains the current page's query results and state.
- **Initialized in:** `wp-settings.php` line 355
- **Usage:** Themes use this to check `is_single()`, `is_home()`, `have_posts()`, etc.
- **WP4BD Status:** ⏳ Needs initialization from Backdrop data (WP4BD-V2-031)

### `$wp_the_query`
- **Type:** `WP_Query` object
- **Description:** Original query object (backup of `$wp_query`). Used to restore query state after secondary queries.
- **Initialized in:** `wp-settings.php` line 347
- **Usage:** WordPress uses this to reset query state after custom queries
- **WP4BD Status:** ⏳ Needs initialization (WP4BD-V2-031)

## Post & Content Globals

### `$post`
- **Type:** `WP_Post` object
- **Description:** Current post object in the WordPress Loop. Set by `the_post()` during loop iteration.
- **Initialized in:** Set dynamically during loop
- **Usage:** Themes access `$post->ID`, `$post->post_title`, `$post->post_content`, etc.
- **WP4BD Status:** ⏳ Needs initialization from Backdrop node (WP4BD-V2-031)

### `$posts`
- **Type:** Array of `WP_Post` objects
- **Description:** Array of post objects for the current query. Populated by `WP_Query`.
- **Initialized in:** Set by `WP_Query->get_posts()`
- **Usage:** Themes iterate over this array in the Loop
- **WP4BD Status:** ⏳ Needs population from Backdrop nodes (WP4BD-V2-031)

## Rewrite & URL Globals

### `$wp_rewrite`
- **Type:** `WP_Rewrite` object
- **Description:** Handles WordPress rewrite rules for pretty permalinks.
- **Initialized in:** `wp-settings.php` line 362
- **Usage:** Used by `get_permalink()`, `get_category_link()`, etc.
- **WP4BD Status:** ⏳ Needs initialization (WP4BD-V2-031)

### `$wp`
- **Type:** `WP` object
- **Description:** Main WordPress object. Handles request parsing and routing.
- **Initialized in:** `wp-settings.php` line 369
- **Usage:** Used internally by WordPress for request handling
- **WP4BD Status:** ⏳ Needs initialization (WP4BD-V2-031)

## Post Types & Taxonomies

### `$wp_post_types`
- **Type:** Array
- **Description:** Registered post types (post, page, attachment, etc.). Populated by `register_post_type()`.
- **Initialized in:** `wp-includes/post.php` via `create_initial_post_types()`
- **Usage:** Themes check `post_type_exists()`, `get_post_types()`, etc.
- **WP4BD Status:** ⏳ Needs population from Backdrop node types (WP4BD-V2-031)

### `$wp_taxonomies`
- **Type:** Array
- **Description:** Registered taxonomies (category, post_tag, etc.). Populated by `register_taxonomy()`.
- **Initialized in:** `wp-includes/taxonomy.php` via `create_initial_taxonomies()`
- **Usage:** Themes check `taxonomy_exists()`, `get_taxonomies()`, etc.
- **WP4BD Status:** ⏳ Needs population from Backdrop vocabularies (WP4BD-V2-031)

## Theme Globals

### `$wp_theme`
- **Type:** `WP_Theme` object
- **Description:** Current active theme object. Contains theme metadata and paths.
- **Initialized in:** Set dynamically when theme is loaded
- **Usage:** Themes use `get_template_directory()`, `get_stylesheet_directory()`, etc.
- **WP4BD Status:** ⏳ Needs initialization from Backdrop theme system (WP4BD-V2-031)

## Hook System Globals

### `$wp_filter`
- **Type:** Array
- **Description:** Action and filter hooks registry. Contains all registered hooks and their callbacks.
- **Initialized in:** `wp-includes/plugin.php` (via `WP_Hook` class)
- **Usage:** Used by `add_action()`, `add_filter()`, `do_action()`, `apply_filters()`
- **WP4BD Status:** ⏳ Needs initialization (WP4BD-V2-031)

### `$wp_actions`
- **Type:** Array
- **Description:** Tracks which actions have been fired. Used for action counting and debugging.
- **Initialized in:** `wp-includes/plugin.php`
- **Usage:** Used internally by WordPress hook system
- **WP4BD Status:** ⏳ Needs initialization (WP4BD-V2-031)

### `$wp_current_filter`
- **Type:** Array
- **Description:** Stack of currently executing filters. Used to track nested filter execution.
- **Initialized in:** `wp-includes/plugin.php`
- **Usage:** Used internally by WordPress hook system
- **WP4BD Status:** ⏳ Needs initialization (WP4BD-V2-031)

## Page & Environment Globals

### `$pagenow`
- **Type:** String
- **Description:** Current page filename (e.g., 'index.php', 'single.php', 'page.php').
- **Initialized in:** `wp-includes/vars.php` line 23-47
- **Usage:** Used by WordPress to determine current page context
- **WP4BD Status:** ⏳ Needs initialization from Backdrop path (WP4BD-V2-031)

### `$blog_id`
- **Type:** Integer
- **Description:** Current site/blog ID. Always 1 in single-site installations.
- **Initialized in:** `wp-settings.php` line 38 (defaults to 1)
- **Usage:** Used in multisite contexts
- **WP4BD Status:** ✅ Can default to 1 (single-site)

## Browser Detection Globals

These are set in `wp-includes/vars.php` for browser/device detection:

- `$is_lynx` - Lynx browser
- `$is_gecko` - Gecko-based browsers (Firefox)
- `$is_winIE` - Windows Internet Explorer
- `$is_macIE` - Mac Internet Explorer
- `$is_opera` - Opera browser
- `$is_NS4` - Netscape 4
- `$is_safari` - Safari browser
- `$is_chrome` - Chrome browser
- `$is_iphone` - iPhone device
- `$is_IE` - Any Internet Explorer
- `$is_edge` - Microsoft Edge

**WP4BD Status:** ⏳ Can be initialized from `$_SERVER['HTTP_USER_AGENT']` (WP4BD-V2-031)

## Server Detection Globals

- `$is_apache` - Apache web server
- `$is_nginx` - Nginx web server
- `$is_IIS` - Microsoft IIS
- `$is_iis7` - IIS 7.x or greater

**WP4BD Status:** ⏳ Can be initialized from `$_SERVER['SERVER_SOFTWARE']` (WP4BD-V2-031)

## Other Important Globals

### `$wp_embed`
- **Type:** `WP_Embed` object
- **Description:** Handles oEmbed functionality for embedding external content.
- **Initialized in:** `wp-settings.php` line 243
- **WP4BD Status:** ⏳ Needs initialization (WP4BD-V2-031)

### `$wp_plugin_paths`
- **Type:** Array
- **Description:** Registered plugin directory paths.
- **Initialized in:** `wp-settings.php` line 256
- **WP4BD Status:** ⏳ Can be empty array (no plugins in V2)

### `$wp_version`
- **Type:** String
- **Description:** WordPress version number (e.g., '4.9').
- **Initialized in:** `wp-includes/version.php`
- **WP4BD Status:** ✅ Set to '4.9' (WP4BD-V2-010)

### `$wp_db_version`
- **Type:** Integer
- **Description:** WordPress database schema version.
- **Initialized in:** `wp-includes/version.php`
- **WP4BD Status:** ✅ Set from version.php

### `$table_prefix`
- **Type:** String
- **Description:** Database table prefix (default: 'wp_').
- **Initialized in:** `wp-config.php` or `wp-config-bd.php`
- **WP4BD Status:** ✅ Set in wp-config-bd.php (WP4BD-V2-012)

## Summary by Priority

### Critical (Must Initialize)
1. **`$wp_query`** - Main query object (themes depend on this)
2. **`$wp_the_query`** - Original query backup
3. **`$post`** - Current post in loop
4. **`$posts`** - Array of posts for current query
5. **`$wp_post_types`** - Registered post types
6. **`$wp_taxonomies`** - Registered taxonomies
7. **`$wp_theme`** - Current theme object
8. **`$wp_filter`** - Hook system registry
9. **`$pagenow`** - Current page context

### Important (Should Initialize)
10. **`$wp_rewrite`** - Permalink handling
11. **`$wp`** - Main WordPress object
12. **`$wp_actions`** - Action tracking
13. **`$wp_current_filter`** - Filter stack

### Optional (Can Initialize Later)
14. **`$wp_embed`** - oEmbed functionality
15. Browser detection globals (`$is_chrome`, etc.)
16. Server detection globals (`$is_apache`, etc.)

## Implementation Notes

- **Database globals:** `$wpdb` is already handled by our db.php drop-in (WP4BD-V2-020)
- **Query globals:** `$wp_query` and `$wp_the_query` need to be populated from Backdrop's current page/node
- **Post globals:** `$post` and `$posts` need to be populated from Backdrop nodes (using WP_Post::from_node())
- **Taxonomy globals:** `$wp_post_types` and `$wp_taxonomies` need to be populated from Backdrop content types and vocabularies
- **Hook globals:** `$wp_filter`, `$wp_actions`, `$wp_current_filter` need to be initialized for WordPress hook system to work
- **Theme globals:** `$wp_theme` needs to be set to current Backdrop theme wrapped as WordPress theme

## Related Files

- `wp-settings.php` - Main WordPress initialization file
- `wp-includes/vars.php` - Sets page and environment globals
- `wp-includes/plugin.php` - Sets hook system globals
- `wp-includes/post.php` - Sets post type globals
- `wp-includes/taxonomy.php` - Sets taxonomy globals

## Next Steps

**WP4BD-V2-031:** Create `wp4bd_init_wordpress_globals()` function to initialize all these globals from Backdrop data.

