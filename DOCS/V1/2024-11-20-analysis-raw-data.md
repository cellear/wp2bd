# Twenty Seventeen Theme - Complete Function Analysis
**Generated:** 2025-11-20
**Purpose:** Comprehensive catalog of all WordPress functions called and defined in Twenty Seventeen theme

---

## EXECUTIVE SUMMARY

- **Total PHP Files Analyzed:** 35
- **Total WordPress Core Functions Called:** 150+ unique functions
- **Total Theme Functions Defined:** 25 functions
- **Testing Framework:** Backdrop SimpleTest (BackdropUnitTestCase/BackdropWebTestCase)

---

## 1. THEME FUNCTIONS DEFINED (25 total)

### functions.php (15 functions)
1. `twentyseventeen_setup()` - Main theme setup
2. `twentyseventeen_content_width()` - Dynamic content width
3. `twentyseventeen_fonts_url()` - Google Fonts URL generator
4. `twentyseventeen_resource_hints()` - Preconnect hints
5. `twentyseventeen_widgets_init()` - Register widget areas
6. `twentyseventeen_excerpt_more()` - Customize excerpt link
7. `twentyseventeen_javascript_detection()` - JS detection script
8. `twentyseventeen_pingback_header()` - Pingback header
9. `twentyseventeen_colors_css_wrap()` - Custom color CSS wrapper
10. `twentyseventeen_scripts()` - Enqueue scripts/styles
11. `twentyseventeen_content_image_sizes_attr()` - Responsive image sizes
12. `twentyseventeen_header_image_tag()` - Header image filter
13. `twentyseventeen_post_thumbnail_sizes_attr()` - Thumbnail sizes
14. `twentyseventeen_front_page_template()` - Front page template filter
15. `twentyseventeen_widget_tag_cloud_args()` - Tag cloud customization

### inc/template-tags.php (7 functions)
1. `twentyseventeen_posted_on()` - Post metadata display
2. `twentyseventeen_time_link()` - Formatted time link
3. `twentyseventeen_entry_footer()` - Post footer meta
4. `twentyseventeen_edit_link()` - Accessibility-friendly edit link
5. `twentyseventeen_front_page_section()` - Front page panel display
6. `twentyseventeen_categorized_blog()` - Multi-category check
7. `twentyseventeen_category_transient_flusher()` - Cache flush

### inc/template-functions.php (3 functions)
1. `twentyseventeen_body_classes()` - Custom body classes
2. `twentyseventeen_panel_count()` - Count active panels
3. `twentyseventeen_is_frontpage()` - Front page check

### inc/customizer.php (9 functions)
1. `twentyseventeen_customize_register()` - Customizer setup
2. `twentyseventeen_sanitize_page_layout()` - Sanitize layout
3. `twentyseventeen_sanitize_colorscheme()` - Sanitize colors
4. `twentyseventeen_customize_partial_blogname()` - Site title partial
5. `twentyseventeen_customize_partial_blogdescription()` - Tagline partial
6. `twentyseventeen_is_static_front_page()` - Static front page check
7. `twentyseventeen_is_view_with_layout_option()` - Layout option check
8. `twentyseventeen_customize_preview_js()` - Preview JS enqueue
9. `twentyseventeen_panels_js()` - Panel controls JS

### inc/custom-header.php (3 functions)
1. `twentyseventeen_custom_header_setup()` - Custom header setup
2. `twentyseventeen_header_style()` - Header inline CSS
3. `twentyseventeen_video_controls()` - Video control customization

### inc/icon-functions.php (4 functions)
1. `twentyseventeen_include_svg_icons()` - SVG sprite loader
2. `twentyseventeen_get_svg()` - SVG icon retriever
3. `twentyseventeen_nav_menu_social_icons()` - Social menu icons
4. `twentyseventeen_dropdown_icon_to_menu_link()` - Dropdown icons

### inc/color-patterns.php (1 function)
1. `twentyseventeen_custom_colors_css()` - Custom color CSS generator

### inc/back-compat.php (4 functions)
1. `twentyseventeen_switch_theme()` - Version check on activation
2. `twentyseventeen_upgrade_notice()` - Version error notice
3. `twentyseventeen_customize()` - Block customizer on old WP
4. `twentyseventeen_preview()` - Block preview on old WP

---

## 2. WORDPRESS CORE FUNCTIONS CALLED (Alphabetical)

### A
- `__()`  - Translation function - **CRITICAL** - Used 50+ times across all files
- `_e()` - Echo translation - **CRITICAL** - Used 20+ times
- `_nx()` - Plural contextual translation - Used 1 time (comments.php)
- `_x()` - Contextual translation - Used 10+ times
- `absint()` - Absolute integer sanitization - Used 5+ times
- `add_action()` - Register action hooks - **CRITICAL** - Used 20+ times
- `add_editor_style()` - Add editor stylesheet - Used 1 time
- `add_filter()` - Register filter hooks - **CRITICAL** - Used 15+ times
- `add_image_size()` - Register custom image size - Used 2 times
- `add_query_arg()` - Add query arguments to URL - Used 1 time
- `add_theme_support()` - Register theme features - **CRITICAL** - Used 10+ times
- `admin_url()` - Get admin area URL - Used 1 time
- `apply_filters()` - Apply filter hooks - **CRITICAL** - Used 10+ times
- `array_key_exists()` - PHP function used with WP - Used 2+ times

### B
- `bloginfo()` - Display blog info - **CRITICAL** - Used 5+ times
- `body_class()` - Body CSS classes - **CRITICAL** - Used 1 time (header.php)

### C
- `comment_form()` - Display comment form - **CRITICAL** - Used 1 time
- `comments_open()` - Check if comments open - **CRITICAL** - Used 3+ times
- `comments_template()` - Load comments template - Used 2+ times
- `current_user_can()` - Check user capabilities - Used 1 time

### D
- `delete_transient()` - Delete transient cache - Used 1 time
- `dynamic_sidebar()` - Display widgets - **CRITICAL** - Used 3+ times

### E
- `echo` - PHP output (used extensively with WP functions)
- `edit_post_link()` - Display edit link - Used 1 time
- `empty()` - PHP function used with WP - Common
- `esc_attr()` - Escape HTML attributes - **CRITICAL** - Used 15+ times
- `esc_attr_e()` - Escape and echo translated attribute - **CRITICAL** - Used 5+ times
- `esc_attr_x()` - Escape contextual translation for attributes - Used 1 time
- `esc_html()` - Escape HTML - **CRITICAL** - Used 5+ times
- `esc_url()` - Escape URLs - **CRITICAL** - Used 30+ times
- `esc_url_raw()` - Escape URL for database - Used 1 time

### F
- `file_exists()` - PHP function - Used 1 time
- `function_exists()` - PHP function - Used for override checks

### G
- `get_author_posts_url()` - Author archive URL - Used 1 time
- `get_bloginfo()` - Get blog info - Used 2+ times
- `get_categories()` - Get category list - Used 1 time
- `get_comments_number()` - Get comment count - **CRITICAL** - Used 5+ times
- `get_edit_post_link()` - Get edit post link - Used 2+ times
- `get_footer()` - Load footer template - **CRITICAL** - Used 18 times (all main templates)
- `get_header()` - Load header template - **CRITICAL** - Used 18 times (all main templates)
- `get_header_image_tag()` - Get header image HTML - Used via filter
- `get_header_textcolor()` - Get header text color - Used 2 times
- `get_media_embedded_in_content()` - Extract media from content - Used 2 times
- `get_option()` - Get WordPress option - **CRITICAL** - Used 5+ times
- `get_parent_theme_file_path()` - Get theme file path - Used 10+ times
- `get_permalink()` - Get post permalink - **CRITICAL** - Used 20+ times
- `get_post()` - Get post object - Used 1 time
- `get_post_format()` - Get post format - **CRITICAL** - Used 10+ times
- `get_post_gallery()` - Get gallery from post - Used 4 times
- `get_post_thumbnail_id()` - Get thumbnail ID - Used 2+ times
- `get_post_type()` - Get post type - **CRITICAL** - Used 10+ times
- `get_queried_object_id()` - Get queried object ID - Used 2 times
- `get_search_form()` - Display search form - Used 3+ times
- `get_search_query()` - Get search query - Used 2+ times
- `get_sidebar()` - Load sidebar template - **CRITICAL** - Used 8+ times
- `get_stylesheet_uri()` - Get stylesheet URI - Used 1 time
- `get_template_directory()` - Get template directory path - Used 1 time
- `get_template_part()` - Load template part - **CRITICAL** - Used 30+ times
- `get_the_archive_description()` - Get archive description - Used 1 time
- `get_the_archive_title()` - Get archive title - Used 1 time
- `get_the_author()` - Get post author - Used 1 time
- `get_the_author_meta()` - Get author meta - Used 1 time
- `get_the_category_list()` - Get category list HTML - Used 1 time
- `get_the_content()` - Get post content (raw) - Used 2 times
- `get_the_date()` - Get formatted date - Used 2+ times
- `get_the_ID()` - Get current post ID - **CRITICAL** - Used 20+ times
- `get_the_modified_date()` - Get modified date - Used 2 times
- `get_the_modified_time()` - Get modified time - Used 1 time
- `get_the_post_thumbnail()` - Get thumbnail HTML - **CRITICAL** - Used 15+ times
- `get_the_tag_list()` - Get tag list HTML - Used 1 time
- `get_the_time()` - Get post time - Used 1 time
- `get_the_title()` - Get post title - **CRITICAL** - Used 20+ times
- `get_theme_file_uri()` - Get theme file URI - Used 10+ times
- `get_theme_mod()` - Get theme modification - **CRITICAL** - Used 10+ times
- `get_theme_support()` - Get theme support args - Used 1 time
- `get_transient()` - Get transient cache - Used 1 time

### H
- `has_custom_header()` - Check for custom header - Used 1 time
- `has_header_image()` - Check for header image - Used 1 time
- `has_nav_menu()` - Check if menu exists - **CRITICAL** - Used 5+ times
- `has_post_thumbnail()` - Check for featured image - **CRITICAL** - Used 15+ times
- `have_comments()` - Check if post has comments - Used 1 time
- `have_posts()` - Check if posts available - **CRITICAL** - Used 30+ times (The Loop)
- `home_url()` - Get home URL - **CRITICAL** - Used 5+ times

### I
- `implode()` - PHP function - Used 1 time
- `in_array()` - PHP function - Used 1 time
- `is_a()` - PHP function - Used 1 time
- `is_active_sidebar()` - Check if sidebar has widgets - **CRITICAL** - Used 8+ times
- `is_admin()` - Check if admin area - Used 1 time
- `is_archive()` - Check if archive page - **CRITICAL** - Used 5+ times
- `is_customize_preview()` - Check if in customizer - Used 8+ times
- `is_front_page()` - Check if front page - **CRITICAL** - Used 15+ times
- `is_home()` - Check if blog home - **CRITICAL** - Used 20+ times
- `is_multi_author()` - Check for multiple authors - Used 1 time
- `is_page()` - Check if page - **CRITICAL** - Used 10+ times
- `is_preview()` - Check if preview - Used 1 time
- `is_search()` - Check if search - Used 2+ times
- `is_single()` - Check if single post - **CRITICAL** - Used 30+ times
- `is_singular()` - Check if singular - Used 2+ times
- `is_sticky()` - Check if sticky post - Used 4+ times
- `is_wp_error()` - Check for WP_Error - Used 1 time
- `isset()` - PHP function - Common

### L
- `language_attributes()` - HTML language attributes - **CRITICAL** - Used 1 time (header.php)
- `load_theme_textdomain()` - Load translations - **CRITICAL** - Used 1 time

### N
- `number_format_i18n()` - Format numbers by locale - Used 1 time

### P
- `pings_open()` - Check if pingbacks open - Used 1 time
- `post_class()` - Post CSS classes - **CRITICAL** - Used 18+ times
- `post_password_required()` - Check for password - Used 1 time
- `post_type_supports()` - Check post type support - Used 1 time
- `printf()` - PHP formatted output - **CRITICAL** - Used 10+ times

### R
- `register_default_headers()` - Register default headers - Used 1 time
- `register_nav_menus()` - Register menu locations - **CRITICAL** - Used 1 time
- `register_sidebar()` - Register widget area - **CRITICAL** - Used 3 times
- `require` - PHP include - Used 5+ times
- `require_once()` - PHP include once - Used 7+ times

### S
- `set_query_var()` - Set query variable - Used 1 time
- `set_transient()` - Set transient cache - Used 1 time
- `setup_postdata()` - Setup post data - Used 1 time
- `single_post_title()` - Display single post title - Used 1 time
- `sprintf()` - PHP format string - **CRITICAL** - Used 20+ times
- `str_replace()` - PHP string replace - Used 2+ times
- `strpos()` - PHP string position - Used 3+ times
- `switch_theme()` - Switch active theme - Used 1 time (back-compat)

### T
- `the_archive_description()` - Display archive description - Used 1 time
- `the_archive_title()` - Display archive title - Used 1 time
- `the_comments_pagination()` - Display comment pagination - Used 1 time
- `the_content()` - Display post content - **CRITICAL** - Used 30+ times
- `the_custom_header_markup()` - Display custom header - Used 1 time
- `the_custom_logo()` - Display custom logo - Used 1 time
- `the_excerpt()` - Display post excerpt - Used 1 time
- `the_ID()` - Display post ID - Used 18+ times
- `the_permalink()` - Display permalink - **CRITICAL** - Used 10+ times
- `the_post()` - Setup current post - **CRITICAL** - Used 20+ times (The Loop)
- `the_post_navigation()` - Display post navigation - Used 1 time
- `the_post_thumbnail()` - Display featured image - **CRITICAL** - Used 15+ times
- `the_posts_pagination()` - Display posts pagination - **CRITICAL** - Used 5+ times
- `the_title()` - Display post title - **CRITICAL** - Used 30+ times

### U
- `uniqid()` - PHP unique ID - Used 2+ times
- `unset()` - PHP unset - Used 1 time
- `urlencode()` - PHP URL encode - Used 2 times

### V
- `version_compare()` - Compare versions - Used 1 time (back-compat)

### W
- `wp_die()` - Die with message - Used 2 times (back-compat)
- `wp_enqueue_script()` - Enqueue JavaScript - **CRITICAL** - Used 10+ times
- `wp_enqueue_style()` - Enqueue CSS - **CRITICAL** - Used 5+ times
- `wp_footer()` - Footer hook - **CRITICAL** - Used 1 time (footer.php)
- `wp_get_attachment_image_attributes()` - Get image attributes - Used via filter
- `wp_get_attachment_image_src()` - Get image source - Used 2+ times
- `wp_head()` - Header hook - **CRITICAL** - Used 1 time (header.php)
- `wp_link_pages()` - Paginate post content - **CRITICAL** - Used 10+ times
- `wp_list_comments()` - Display comments list - Used 1 time
- `wp_localize_script()` - Localize script data - Used 1 time
- `wp_nav_menu()` - Display navigation menu - **CRITICAL** - Used 2+ times
- `wp_parse_args()` - Parse arguments array - Used 1 time
- `wp_reset_postdata()` - Reset post data - **CRITICAL** - Used 3+ times
- `wp_script_add_data()` - Add script data - Used 1 time
- `wp_style_add_data()` - Add style data - Used 2 times
- `wp_style_is()` - Check if style queued - Used 1 time

---

## 3. GLOBAL VARIABLES USED

- `$post` - WordPress global post object - Used 5+ times
- `$wp_query` - Main query object - Referenced in documentation
- `$GLOBALS['wp_version']` - WordPress version - Used 3 times (back-compat)
- `$GLOBALS['content_width']` - Content width - Used 3 times
- `$_GET['activated']` - Activation parameter - Used 1 time
- `$_GET['preview']` - Preview parameter - Used 1 time
- `$twentyseventeencounter` - Theme panel counter - Used 2+ times
- `$wp_customize` - Customizer manager - Used extensively in customizer.php

---

## 4. WORDPRESS HOOKS REGISTERED

### Action Hooks (by file):

**functions.php:**
- `after_setup_theme` → twentyseventeen_setup
- `template_redirect` → twentyseventeen_content_width
- `widgets_init` → twentyseventeen_widgets_init
- `wp_head` → twentyseventeen_javascript_detection
- `wp_head` → twentyseventeen_pingback_header
- `wp_head` → twentyseventeen_colors_css_wrap
- `wp_enqueue_scripts` → twentyseventeen_scripts

**inc/template-tags.php:**
- `edit_category` → twentyseventeen_category_transient_flusher
- `save_post` → twentyseventeen_category_transient_flusher

**inc/template-functions.php:**
- (No actions, only filters)

**inc/customizer.php:**
- `customize_register` → twentyseventeen_customize_register
- `customize_preview_init` → twentyseventeen_customize_preview_js
- `customize_controls_enqueue_scripts` → twentyseventeen_panels_js

**inc/custom-header.php:**
- `after_setup_theme` → twentyseventeen_custom_header_setup

**inc/icon-functions.php:**
- `wp_footer` → twentyseventeen_include_svg_icons

**inc/back-compat.php:**
- `after_switch_theme` → twentyseventeen_switch_theme
- `admin_notices` → twentyseventeen_upgrade_notice
- `load-customize.php` → twentyseventeen_customize
- `template_redirect` → twentyseventeen_preview

### Filter Hooks (by file):

**functions.php:**
- `wp_resource_hints` → twentyseventeen_resource_hints
- `excerpt_more` → twentyseventeen_excerpt_more
- `wp_calculate_image_sizes` → twentyseventeen_content_image_sizes_attr
- `get_header_image_tag` → twentyseventeen_header_image_tag
- `wp_get_attachment_image_attributes` → twentyseventeen_post_thumbnail_sizes_attr
- `frontpage_template` → twentyseventeen_front_page_template
- `widget_tag_cloud_args` → twentyseventeen_widget_tag_cloud_args

**inc/template-functions.php:**
- `body_class` → twentyseventeen_body_classes

**inc/custom-header.php:**
- `header_video_settings` → twentyseventeen_video_controls

**inc/icon-functions.php:**
- `walker_nav_menu_start_el` → twentyseventeen_nav_menu_social_icons
- `nav_menu_item_title` → twentyseventeen_dropdown_icon_to_menu_link

### Custom Filter Hooks Created (for extensibility):

- `twentyseventeen_starter_content` (functions.php)
- `twentyseventeen_content_width` (functions.php)
- `twentyseventeen_custom_colors_saturation` (color-patterns.php)
- `twentyseventeen_custom_colors_css` (color-patterns.php)
- `twentyseventeen_custom_header_args` (custom-header.php)
- `twentyseventeen_front_page_sections` (template-functions.php, customizer.php)
- `twentyseventeen_social_links_icons` (icon-functions.php)

---

## 5. CONSTANTS USED

- `WP_DEFAULT_THEME` - Default theme fallback (back-compat.php)
- `DOING_AUTOSAVE` - Autosave check (template-tags.php)
- `DATE_W3C` - Date format constant (template-tags.php)
- `ABSPATH` - Not directly used but referenced in comments

---

## 6. WORDPRESS CLASSES INSTANTIATED

- `WP_Customize_Color_Control` - Customizer color picker (customizer.php)
- `WP_Customize_Manager` - Via $wp_customize parameter (customizer.php)
- `WP_Customize_Partial` - Selective refresh (referenced in template-tags.php)
- `WP_Query` - Custom post queries (content-front-page-panels.php)

---

## 7. TEXT DOMAINS

All translation functions use: `'twentyseventeen'`

---

## 8. WIDGET AREAS (SIDEBARS) REGISTERED

1. **sidebar-1** - Main sidebar (Blog Sidebar)
2. **sidebar-2** - Footer widget area 1
3. **sidebar-3** - Footer widget area 2

---

## 9. MENU LOCATIONS REGISTERED

1. **top** - Top navigation menu
2. **social** - Social links menu

---

## 10. IMAGE SIZES REGISTERED

1. **twentyseventeen-featured-image** - 2000x1200 (featured images)
2. **twentyseventeen-thumbnail-avatar** - 100x100 (avatars)

---

## 11. ASSET FILES REFERENCED

### JavaScript:
- `/assets/js/customize-preview.js`
- `/assets/js/customize-controls.js`
- `/assets/js/html5.js` (IE8 shiv)
- `/assets/js/skip-link-focus-fix.js`
- `/assets/js/navigation.js`
- `/assets/js/global.js`
- `/assets/js/jquery.scrollTo.js`

### CSS:
- `/assets/css/ie9.css`
- `/assets/css/ie8.css`
- `/assets/css/colors-dark.css`
- `style.css` (main stylesheet)
- Google Fonts (Libre Franklin)

### Images:
- `/assets/images/header.jpg` (default header)
- `/assets/images/svg-icons.svg` (SVG sprite)

---

## 12. SUPPORTED POST FORMATS

- aside
- image
- video
- quote
- link
- gallery
- audio

---

## 13. SOCIAL MEDIA PLATFORMS SUPPORTED (39)

behance, codepen, deviantart, digg, docker, dribbble, dropbox, facebook, flickr, foursquare, google-plus, github, instagram, linkedin, email, medium, pinterest, periscope, pocket, reddit, skype, slideshare, snapchat, soundcloud, spotify, stumbleupon, tumblr, twitch, twitter, vimeo, vine, vk, wordpress, yelp, youtube

---

## 14. TEMPLATE HIERARCHY

### Main Templates:
- index.php
- single.php
- page.php
- archive.php
- search.php
- 404.php
- front-page.php

### Template Parts (Structural):
- header.php
- footer.php
- sidebar.php
- searchform.php
- comments.php

### Template Parts (Modular):
- template-parts/footer/footer-widgets.php
- template-parts/footer/site-info.php
- template-parts/header/header-image.php
- template-parts/header/site-branding.php
- template-parts/navigation/navigation-top.php
- template-parts/page/content-page.php
- template-parts/page/content-front-page.php
- template-parts/page/content-front-page-panels.php
- template-parts/post/content.php
- template-parts/post/content-excerpt.php
- template-parts/post/content-none.php
- template-parts/post/content-audio.php
- template-parts/post/content-video.php
- template-parts/post/content-gallery.php
- template-parts/post/content-image.php

---

## 15. KEY PATTERNS & ARCHITECTURE

### The WordPress Loop Pattern:
Used in nearly every template file:
```php
if ( have_posts() ) :
    while ( have_posts() ) : the_post();
        // Display post content
    endwhile;
else :
    // No posts found
endif;
```

### Template Part Loading Pattern:
```php
get_template_part( 'template-parts/post/content', get_post_format() );
```

### Conditional Tag Usage:
Extensive use of:
- `is_single()` vs `is_page()` vs `is_home()`
- `is_front_page()` vs `is_home()`
- `is_archive()`, `is_search()`, `is_404()`
- `is_active_sidebar()`, `has_post_thumbnail()`

### Security/Escaping Pattern:
- All URLs escaped with `esc_url()`
- All attributes escaped with `esc_attr()`
- All HTML escaped with `esc_html()`
- Translation functions used for all user-facing text

### Transient Caching:
- Category count cached with `twentyseventeen_categories` transient
- Flushed on `edit_category` and `save_post` hooks

---

## 16. CUSTOMIZER SETTINGS

1. **blogname** - Site title (modified for selective refresh)
2. **blogdescription** - Site tagline (modified for selective refresh)
3. **header_textcolor** - Header text color (modified for selective refresh)
4. **colorscheme** - Color scheme (light/dark/custom)
5. **colorscheme_hue** - Custom color hue (0-360)
6. **page_layout** - Page layout (one-column/two-column)
7. **panel_1** through **panel_4** - Front page panels

---

## NEXT STEPS

1. ✅ Complete function analysis
2. ⏭️ Classify functions into Critical/Nice-to-Have/Irrelevant
3. ⏭️ Create implementation specifications for each critical function
4. ⏭️ Group functions into work packages for parallel implementation
