# WordPress Theme Wrapper for Backdrop CMS

This Backdrop theme serves as a compatibility layer that allows WordPress themes to run on Backdrop CMS 1.30.

## Structure

```
wp/
├── wp.info                      # Backdrop theme declaration
├── template.php                 # Bootstrap and compatibility layer loader
├── page.tpl.php                 # Main page template (delegates to WP theme)
├── README.md                    # This file
│
├── classes/                     # WordPress core classes
│   ├── WP_Post.php             # WordPress post object
│   └── WP_Query.php            # WordPress query system
│
├── functions/                   # WordPress compatibility functions
│   ├── hooks.php               # Action/filter system (add_action, apply_filters, etc.)
│   ├── escaping.php            # Security functions (esc_html, esc_url, etc.)
│   ├── loop.php                # The Loop (have_posts, the_post, etc.)
│   ├── template-loading.php    # Template functions (get_header, get_footer, etc.)
│   ├── content-display.php     # Content functions (the_title, the_content, etc.)
│   ├── conditionals.php        # Conditional tags (is_page, is_single, etc.)
│   ├── utilities.php           # Utility functions (home_url, bloginfo, etc.)
│   ├── post-metadata.php       # Metadata functions (get_the_date, get_the_author, etc.)
│   └── stubs.php               # Stub implementations for remaining functions
│
└── wp-content/
    └── themes/
        └── twentyseventeen/    # WordPress theme (unchanged)
```

## Currently Active Theme

**Hard-coded:** Twenty Seventeen

The active WordPress theme is defined in `template.php`:
```php
define('WP2BD_ACTIVE_THEME', 'twentyseventeen');
```

## How It Works

1. **Backdrop activates the "WordPress Theme Wrapper" theme**
   - User selects "wp" theme in Backdrop admin

2. **template.php bootstraps the compatibility layer**
   - Loads all WordPress classes and functions
   - Initializes WordPress globals ($wp_query, $wp_filter, etc.)
   - Loads WordPress theme's functions.php

3. **page.tpl.php handles all page requests**
   - Sets up WordPress environment
   - Determines correct WordPress template (index.php, single.php, page.php, etc.)
   - Includes the WordPress template file
   - WordPress template renders using our compatibility functions

4. **WordPress theme renders normally**
   - Calls WordPress functions like the_title(), the_content(), etc.
   - Functions map to Backdrop data (nodes → posts)
   - Output is identical to WordPress

## Implemented WordPress Functions

### ✅ Fully Implemented (50+ functions)

**Core Classes:**
- WP_Post (with from_node() converter)
- WP_Query (maps to EntityFieldQuery)

**The Loop:**
- have_posts(), the_post(), wp_reset_postdata(), setup_postdata()

**Template Loading:**
- get_header(), get_footer(), get_sidebar(), get_template_part()

**Content Display:**
- the_title(), get_the_title()
- the_permalink(), get_permalink()
- the_ID(), get_the_ID()
- the_content(), the_excerpt()
- post_class(), get_post_class()
- language_attributes()

**Hook System:**
- add_action(), do_action()
- add_filter(), apply_filters()
- remove_action(), remove_filter()
- wp_head(), wp_footer()
- 7 helper functions (has_action, did_action, etc.)

**Security/Escaping:**
- esc_html(), esc_attr(), esc_url(), esc_url_raw()
- esc_js(), esc_textarea()
- sanitize_text_field()

**Conditional Tags:**
- is_page(), is_single(), is_home(), is_front_page()
- is_archive(), is_search(), is_sticky(), is_404(), is_singular()

**Utilities:**
- home_url(), bloginfo(), get_bloginfo()
- get_template_directory(), get_template_directory_uri()
- get_stylesheet_directory(), get_stylesheet_directory_uri()
- get_template(), get_stylesheet()

**Post Metadata:**
- get_post_type(), get_post_format()
- get_the_date(), get_the_time()
- get_the_author(), get_the_author_meta()

### 🟡 Stubbed (Safe Defaults)

**Translation:**
- __(), _e(), _x(), esc_html__(), esc_attr__()

**Scripts/Styles:**
- wp_enqueue_script(), wp_enqueue_style()
- wp_register_script(), wp_register_style()

**Navigation:**
- has_nav_menu(), wp_nav_menu()

**Comments:**
- comments_open(), get_comments_number(), comments_template()

**Pagination:**
- the_posts_pagination(), the_post_navigation(), wp_link_pages()

**Taxonomy:**
- get_the_category(), the_category()
- get_the_tags(), the_tags()

**Theme Support:**
- add_theme_support(), get_theme_mod()

**Miscellaneous:**
- body_class(), get_option(), is_customize_preview()

## Adding More WordPress Themes

To add another WordPress theme:

1. Copy the theme to `wp-content/themes/`
2. Change the active theme in `template.php`:
   ```php
   define('WP2BD_ACTIVE_THEME', 'your-theme-name');
   ```

## Testing

To test the theme:

1. **Enable the theme in Backdrop:**
   ```bash
   cd /home/user/wp2bd/backdrop-1.30
   # Via Backdrop admin UI: Appearance → Enable "WordPress Theme Wrapper"
   ```

2. **Create sample content:**
   - Create some "Post" nodes in Backdrop
   - View them on the front-end

3. **Check the output:**
   - WordPress theme should render using Backdrop nodes as data source

## Known Limitations

- Script/style enqueuing is stubbed (CSS/JS must be manually linked)
- Navigation menus return placeholders
- Comments are disabled
- Pagination shows placeholders
- Taxonomy (categories/tags) returns empty
- Translation functions pass through English text

## Future Enhancements

Priority enhancements:

1. **P1:** Implement script/style enqueuing for proper asset loading
2. **P1:** Implement taxonomy functions (categories, tags)
3. **P1:** Implement navigation menu system
4. **P1:** Implement pagination functions
5. **P2:** Add theme selector UI
6. **P2:** Implement comment system
7. **P2:** Implement translation/i18n support

## Development

All development happens in `/home/user/wp2bd/implementation/` and gets copied here.

To update the compatibility layer:
```bash
cp -r /home/user/wp2bd/implementation/classes /home/user/wp2bd/backdrop-1.30/themes/wp/
cp -r /home/user/wp2bd/implementation/functions /home/user/wp2bd/backdrop-1.30/themes/wp/
```

## Credits

- **WordPress Compatibility Layer:** WP2BD Project
- **Twenty Seventeen Theme:** WordPress.org
- **Backdrop CMS:** backdrop.org

## License

WordPress compatibility layer: GPL-2.0+
Twenty Seventeen theme: GPL-2.0+
