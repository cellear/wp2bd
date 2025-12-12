# WP4BD Request Lifecycle - Complete Diagram

## Overview

This document shows how a page request flows through the WP4BD (WordPress for Backdrop) system, from initial HTTP request to final HTML output.

---

## High-Level Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                         HTTP REQUEST                            │
│                    GET /blog/my-post                            │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                      BACKDROP CMS                               │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 1. Routing & Bootstrap                                    │  │
│  │    - index.php                                            │  │
│  │    - backdrop_bootstrap(BACKDROP_BOOTSTRAP_FULL)         │  │
│  │    - menu_execute_active_handler()                       │  │
│  └────────────────────────┬─────────────────────────────────┘  │
│                           ▼                                     │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 2. Content Loading                                        │  │
│  │    - node_load($nid)                                      │  │
│  │    - Load all fields, metadata                           │  │
│  │    - Load taxonomy terms, author                         │  │
│  └────────────────────────┬─────────────────────────────────┘  │
│                           ▼                                     │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 3. WordPress Compatibility Layer Trigger                  │  │
│  │    - wp_content_node_view() hook                         │  │
│  │    - Detect: "Should this use WP theme?"                 │  │
│  └────────────────────────┬─────────────────────────────────┘  │
└────────────────────────────┼─────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│              WP4BD COMPATIBILITY LAYER (Bridge)                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 4. Data Transformation: Backdrop → WordPress              │  │
│  │                                                           │  │
│  │    Backdrop Node          →    WordPress Data            │  │
│  │    ─────────────────           ─────────────────         │  │
│  │    $node->nid            →     $post->ID                 │  │
│  │    $node->title          →     $post->post_title         │  │
│  │    $node->body           →     $post->post_content       │  │
│  │    $node->created        →     $post->post_date          │  │
│  │    $node->field_image    →     post_meta['_thumbnail']   │  │
│  │    $node->uid            →     $post->post_author        │  │
│  │                                                           │  │
│  │    Function: _backdrop_node_to_wp_post($node)            │  │
│  └────────────────────────┬─────────────────────────────────┘  │
│                           ▼                                     │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 5. WordPress Environment Setup                            │  │
│  │                                                           │  │
│  │    // Define WordPress constants                         │  │
│  │    define('ABSPATH', '/path/to/wordpress/');             │  │
│  │    define('WPINC', 'wp-includes');                       │  │
│  │    define('WP_CONTENT_DIR', '/path/to/wp-content');      │  │
│  │                                                           │  │
│  │    // Set up WordPress globals                           │  │
│  │    global $wp_query, $wp_the_query, $post, $wpdb;        │  │
│  │                                                           │  │
│  │    // Create WP_Post object                              │  │
│  │    $wp_post = new WP_Post($transformed_data);            │  │
│  │                                                           │  │
│  │    // Populate WP_Query                                  │  │
│  │    $wp_query = new WP_Query();                           │  │
│  │    $wp_query->posts = array($wp_post);                   │  │
│  │    $wp_query->post_count = 1;                            │  │
│  │    $wp_query->is_single = true;                          │  │
│  │    $wp_query->is_singular = true;                        │  │
│  │    $wp_query->queried_object = $wp_post;                 │  │
│  │    $wp_query->queried_object_id = $wp_post->ID;          │  │
│  │                                                           │  │
│  │    $wp_the_query = $wp_query;  // Backup                 │  │
│  │    $post = $wp_post;           // Current post           │  │
│  │                                                           │  │
│  │    Function: _wp4bd_setup_wordpress_environment($node)   │  │
│  └────────────────────────┬─────────────────────────────────┘  │
│                           ▼                                     │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 6. Pre-Populate WordPress Caches                         │  │
│  │                                                           │  │
│  │    // Options cache (from Backdrop config)               │  │
│  │    $alloptions = array(                                  │  │
│  │      'siteurl' => 'http://example.com',                  │  │
│  │      'home' => 'http://example.com',                     │  │
│  │      'blogname' => config_get('site_name'),              │  │
│  │      'stylesheet' => 'twentyseventeen',                  │  │
│  │      'template' => 'twentyseventeen',                    │  │
│  │      'posts_per_page' => 10,                             │  │
│  │      // ... all theme options                            │  │
│  │    );                                                     │  │
│  │    wp_cache_add('alloptions', $alloptions, 'options');   │  │
│  │                                                           │  │
│  │    // Post metadata cache                                │  │
│  │    $post_meta = array(                                   │  │
│  │      '_thumbnail_id' => '123',                           │  │
│  │      'custom_field' => 'value',                          │  │
│  │    );                                                     │  │
│  │    wp_cache_add($post_id, $post_meta, 'post_meta');     │  │
│  │                                                           │  │
│  │    Function: _wp4bd_populate_caches($node)               │  │
│  └────────────────────────┬─────────────────────────────────┘  │
│                           ▼                                     │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 7. Mock Database Layer                                    │  │
│  │                                                           │  │
│  │    // Load custom db.php drop-in                         │  │
│  │    require WP_CONTENT_DIR . '/db.php';                   │  │
│  │                                                           │  │
│  │    // This creates a no-op wpdb that prevents            │  │
│  │    // WordPress from making any real database queries    │  │
│  │    global $wpdb;                                         │  │
│  │    $wpdb->prefix = 'wp_';  // Must be set                │  │
│  └────────────────────────┬─────────────────────────────────┘  │
└────────────────────────────┼─────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                    WORDPRESS RENDERING ENGINE                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 8. WordPress Bootstrap (Minimal)                          │  │
│  │                                                           │  │
│  │    require ABSPATH . 'wp-includes/load.php';             │  │
│  │    require ABSPATH . 'wp-includes/formatting.php';       │  │
│  │    require ABSPATH . 'wp-includes/plugin.php';           │  │
│  │    require ABSPATH . 'wp-includes/theme.php';            │  │
│  │    require ABSPATH . 'wp-includes/post.php';             │  │
│  │    require ABSPATH . 'wp-includes/query.php';            │  │
│  │    require ABSPATH . 'wp-includes/link-template.php';    │  │
│  │    require ABSPATH . 'wp-includes/general-template.php'; │  │
│  │    require ABSPATH . 'wp-includes/post-template.php';    │  │
│  │    // ... only files needed for theme rendering          │  │
│  │                                                           │  │
│  │    Variables populated:                                  │  │
│  │    - $wp_filter (hooks system)                           │  │
│  │    - $wp_actions (action counters)                       │  │
│  └────────────────────────┬─────────────────────────────────┘  │
│                           ▼                                     │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 9. Load Theme functions.php                               │  │
│  │                                                           │  │
│  │    $theme_dir = WP_CONTENT_DIR . '/themes/twentyseventeen';│
│  │    require $theme_dir . '/functions.php';                │  │
│  │                                                           │  │
│  │    This file typically:                                  │  │
│  │    - Calls add_theme_support()                           │  │
│  │    - Calls add_action('wp_enqueue_scripts', ...)         │  │
│  │    - Registers nav menus, sidebars                       │  │
│  │    - Defines theme constants                             │  │
│  │                                                           │  │
│  │    Variables affected:                                   │  │
│  │    - $_wp_theme_features (theme support flags)           │  │
│  │    - $wp_filter (more hooks registered)                  │  │
│  └────────────────────────┬─────────────────────────────────┘  │
│                           ▼                                     │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 10. Determine Template File                               │  │
│  │                                                           │  │
│  │     WordPress template hierarchy:                        │  │
│  │                                                           │  │
│  │     is_single() → single-{post_type}.php                 │  │
│  │                → single.php                               │  │
│  │                → singular.php                             │  │
│  │                → index.php (fallback)                     │  │
│  │                                                           │  │
│  │     is_page()   → page-{slug}.php                        │  │
│  │                → page-{id}.php                            │  │
│  │                → page.php                                 │  │
│  │                → singular.php                             │  │
│  │                → index.php                                │  │
│  │                                                           │  │
│  │     is_home()   → home.php                               │  │
│  │                → index.php                                │  │
│  │                                                           │  │
│  │     For our example (single post):                       │  │
│  │     $template = $theme_dir . '/single.php';              │  │
│  │                                                           │  │
│  │     Function: get_query_template('single')               │  │
│  └────────────────────────┬─────────────────────────────────┘  │
│                           ▼                                     │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 11. Start Output Buffering                                │  │
│  │                                                           │  │
│  │     ob_start();  // Capture ALL WordPress output         │  │
│  └────────────────────────┬─────────────────────────────────┘  │
│                           ▼                                     │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 12. Execute Template File (single.php)                    │  │
│  │                                                           │  │
│  │     require $template;                                   │  │
│  │                                                           │  │
│  │     Example single.php execution flow:                   │  │
│  │     ┌────────────────────────────────────────────────┐   │  │
│  │     │ <?php get_header(); ?>                         │───┼──┼─┐
│  │     │                                                 │   │  │ │
│  │     │ <main id="main" class="site-main">             │   │  │ │
│  │     │   <?php                                         │   │  │ │
│  │     │   while (have_posts()) :                       │───┼──┼─┤
│  │     │     the_post();                                │───┼──┼─┤
│  │     │     get_template_part('content', 'single');    │───┼──┼─┤
│  │     │   endwhile;                                     │   │  │ │
│  │     │   ?>                                            │   │  │ │
│  │     │ </main>                                         │   │  │ │
│  │     │                                                 │   │  │ │
│  │     │ <?php get_sidebar(); ?>                        │───┼──┼─┤
│  │     │ <?php get_footer(); ?>                         │───┼──┼─┤
│  │     └────────────────────────────────────────────────┘   │  │ │
│  └──────────────────────────────────────────────────────────┘  │ │
│                                                                 │ │
│  ┌──────────────────────────────────────────────────────────┐  │ │
│  │ 13. Template Function Calls (Detailed)                   │  │ │
│  └──────────────────────────────────────────────────────────┘  │ │
│                                                                 │ │
└─────────────────────────────────────────────────────────────────┘ │
                                                                    │
  ┌─────────────────────────────────────────────────────────────┐ │
  │              TEMPLATE FUNCTION EXECUTION                    │ │
  └─────────────────────────────────────────────────────────────┘ │
                                                                    │
  ┌─────────────────────────────────────────────────────────────┐ │
  │ get_header()                                              │◄┘
  │ ────────────                                                │
  │   1. do_action('get_header', null)                          │
  │   2. locate_template('header.php')                          │
  │   3. require $theme_dir/header.php                          │
  │                                                             │
  │   header.php typically contains:                            │
  │   ┌─────────────────────────────────────────────────────┐   │
  │   │ <!DOCTYPE html>                                     │   │
  │   │ <html <?php language_attributes(); ?>>              │   │
  │   │ <head>                                              │   │
  │   │   <meta charset="<?php bloginfo('charset'); ?>">    │   │
  │   │   <?php wp_head(); ?>                             │◄──┼─┐ │
  │   │ </head>                                             │   │ │ │
  │   │ <body <?php body_class(); ?>>                       │   │ │ │
  │   │   <div id="page" class="site">                      │   │ │ │
  │   │     <header id="masthead">                          │   │ │ │
  │   │       <h1><?php bloginfo('name'); ?></h1>           │   │ │ │
  │   │       <?php wp_nav_menu(...); ?>                    │   │ │ │
  │   │     </header>                                       │   │ │ │
  │   └─────────────────────────────────────────────────────┘   │ │ │
  │                                                             │ │ │
  │   Outputs: HTML header, navigation                         │ │ │
  └─────────────────────────────────────────────────────────────┘ │ │
                                                                  │ │
  ┌─────────────────────────────────────────────────────────────┐ │ │
  │ wp_head()                                                 │◄┘ │
  │ ─────────                                                   │   │
  │   1. do_action('wp_head')                                   │   │
  │   2. Hooks execute in priority order:                       │   │
  │      - wp_enqueue_scripts action                            │   │
  │      - wp_print_styles() → output <link> tags               │   │
  │      - wp_print_head_scripts() → output <script> tags       │   │
  │                                                             │   │
  │   Variables used:                                           │   │
  │   - $wp_styles (WP_Styles object)                           │   │
  │   - $wp_scripts (WP_Scripts object)                         │   │
  │                                                             │   │
  │   Outputs: <link> and <script> tags                         │   │
  └─────────────────────────────────────────────────────────────┘   │
                                                                    │
  ┌─────────────────────────────────────────────────────────────┐   │
  │ have_posts()                                              │◄──┘
  │ ────────────                                                │
  │   global $wp_query;                                         │
  │   return $wp_query->have_posts();                           │
  │                                                             │
  │   Inside WP_Query::have_posts():                            │
  │     if ($this->current_post + 1 < $this->post_count) {      │
  │       return true;                                          │
  │     }                                                       │
  │                                                             │
  │   First call: current_post=-1, post_count=1 → TRUE         │
  │   Second call: current_post=0, post_count=1 → FALSE        │
  │                                                             │
  │   Returns: boolean                                          │
  └─────────────────────────────────────────────────────────────┘
                                                                    │
  ┌─────────────────────────────────────────────────────────────┐   │
  │ the_post()                                                │◄──┘
  │ ──────────                                                  │
  │   global $wp_query;                                         │
  │   $wp_query->the_post();                                    │
  │                                                             │
  │   Inside WP_Query::the_post():                              │
  │     $this->current_post++;                                  │
  │     $this->post = $this->posts[$this->current_post];        │
  │     setup_postdata($this->post);                            │
  │                                                             │
  │   setup_postdata() sets these globals:                      │
  │     global $post, $id, $authordata, $currentday, ...;       │
  │     $id = $post->ID;                                        │
  │     $authordata = get_userdata($post->post_author);         │
  │     $page = 1;                                              │
  │     $pages = explode('<!--nextpage-->', $post->post_content);│
  │     $multipage = count($pages) > 1;                         │
  │     $more = 1;                                              │
  │                                                             │
  │   Changes state: Advances loop, sets $post global           │
  └─────────────────────────────────────────────────────────────┘
                                                                    │
  ┌─────────────────────────────────────────────────────────────┐   │
  │ get_template_part('content', 'single')                    │◄──┘
  │ ──────────────────────────────────────────                  │
  │   1. Build template names array:                            │
  │      ['content-single.php', 'content.php']                  │
  │   2. locate_template($templates)                            │
  │   3. require $theme_dir/content-single.php                  │
  │                                                             │
  │   content-single.php typically contains:                    │
  │   ┌─────────────────────────────────────────────────────┐   │
  │   │ <article id="post-<?php the_ID(); ?>"               │   │
  │   │          class="<?php post_class(); ?>">            │   │
  │   │   <header class="entry-header">                     │   │
  │   │     <h1><?php the_title(); ?></h1>                  │   │
  │   │     <div class="entry-meta">                        │   │
  │   │       <?php the_date(); ?>                          │   │
  │   │       <?php the_author(); ?>                        │   │
  │   │     </div>                                          │   │
  │   │   </header>                                         │   │
  │   │                                                     │   │
  │   │   <?php if (has_post_thumbnail()) : ?>              │   │
  │   │     <?php the_post_thumbnail(); ?>                  │   │
  │   │   <?php endif; ?>                                   │   │
  │   │                                                     │   │
  │   │   <div class="entry-content">                       │   │
  │   │     <?php the_content(); ?>                         │   │
  │   │   </div>                                            │   │
  │   │                                                     │   │
  │   │   <footer class="entry-footer">                     │   │
  │   │     <?php the_tags(); ?>                            │   │
  │   │     <?php the_category(); ?>                        │   │
  │   │   </footer>                                         │   │
  │   │ </article>                                          │   │
  │   └─────────────────────────────────────────────────────┘   │
  │                                                             │
  │   Outputs: Article HTML with post content                  │
  └─────────────────────────────────────────────────────────────┘
                                                                    │
  ┌─────────────────────────────────────────────────────────────┐   │
  │ Template Tag Functions (called from content-single.php)     │   │
  │ ───────────────────────────────────────────────────────────│   │
  │                                                             │   │
  │ the_ID()                                                    │   │
  │   echo get_the_ID();                                        │   │
  │   → global $post; return $post->ID;                         │   │
  │   Outputs: "123"                                            │   │
  │                                                             │   │
  │ post_class()                                                │   │
  │   → get_post_class() → array of CSS classes                 │   │
  │   Outputs: "post-123 post type-post status-publish ..."     │   │
  │                                                             │   │
  │ the_title()                                                 │   │
  │   echo get_the_title();                                     │   │
  │   → global $post; return $post->post_title;                 │   │
  │   Outputs: "My Awesome Blog Post"                           │   │
  │                                                             │   │
  │ the_content()                                               │   │
  │   $content = get_the_content();                             │   │
  │   $content = apply_filters('the_content', $content);        │   │
  │   echo $content;                                            │   │
  │   → global $post; return $post->post_content;               │   │
  │   Outputs: "<p>This is my post content...</p>"              │   │
  │                                                             │   │
  │ has_post_thumbnail()                                        │   │
  │   → get_post_thumbnail_id($post->ID)                        │   │
  │   → get_post_meta($post->ID, '_thumbnail_id', true)         │   │
  │   → wp_cache_get($post->ID, 'post_meta')                    │   │
  │   Returns: boolean                                          │   │
  │                                                             │   │
  │ the_post_thumbnail()                                        │   │
  │   → get_the_post_thumbnail()                                │   │
  │   → wp_get_attachment_image($thumbnail_id, 'post-thumbnail')│   │
  │   Outputs: "<img src='...' alt='...' />"                    │   │
  │                                                             │   │
  │ bloginfo('name')                                            │   │
  │   echo get_bloginfo('name');                                │   │
  │   → get_option('blogname')                                  │   │
  │   → wp_cache_get('alloptions', 'options')                   │   │
  │   Outputs: "My Awesome Site"                                │   │
  │                                                             │   │
  └─────────────────────────────────────────────────────────────┘   │
                                                                    │
  ┌─────────────────────────────────────────────────────────────┐   │
  │ get_sidebar()                                             │◄──┘
  │ ─────────────                                               │
  │   1. do_action('get_sidebar', null)                         │
  │   2. locate_template('sidebar.php')                         │
  │   3. require $theme_dir/sidebar.php                         │
  │                                                             │
  │   sidebar.php typically contains:                           │
  │   ┌─────────────────────────────────────────────────────┐   │
  │   │ <aside id="secondary" class="widget-area">          │   │
  │   │   <?php if (is_active_sidebar('sidebar-1')) : ?>    │   │
  │   │     <?php dynamic_sidebar('sidebar-1'); ?>          │   │
  │   │   <?php endif; ?>                                   │   │
  │   │ </aside>                                            │   │
  │   └─────────────────────────────────────────────────────┘   │
  │                                                             │
  │   Outputs: Sidebar HTML with widgets                        │
  └─────────────────────────────────────────────────────────────┘
                                                                    │
  ┌─────────────────────────────────────────────────────────────┐   │
  │ get_footer()                                              │◄──┘
  │ ────────────                                                │
  │   1. do_action('get_footer', null)                          │
  │   2. locate_template('footer.php')                          │
  │   3. require $theme_dir/footer.php                          │
  │                                                             │
  │   footer.php typically contains:                            │
  │   ┌─────────────────────────────────────────────────────┐   │
  │   │     </div><!-- #page -->                            │   │
  │   │     <footer id="colophon" class="site-footer">      │   │
  │   │       <div class="site-info">                       │   │
  │   │         © <?php bloginfo('name'); ?>                │   │
  │   │       </div>                                        │   │
  │   │     </footer>                                       │   │
  │   │     <?php wp_footer(); ?>                         │◄─┼─┐ │
  │   │   </body>                                           │   │ │ │
  │   │ </html>                                             │   │ │ │
  │   └─────────────────────────────────────────────────────┘   │ │ │
  │                                                             │ │ │
  │   Outputs: Footer HTML, closing tags                        │ │ │
  └─────────────────────────────────────────────────────────────┘ │ │
                                                                  │ │
  ┌─────────────────────────────────────────────────────────────┐ │ │
  │ wp_footer()                                               │◄┘ │
  │ ───────────                                                 │   │
  │   1. do_action('wp_footer')                                 │   │
  │   2. Hooks execute:                                         │   │
  │      - wp_print_footer_scripts() → output <script> tags     │   │
  │      - Other footer actions                                 │   │
  │                                                             │   │
  │   Outputs: Footer scripts                                   │   │
  └─────────────────────────────────────────────────────────────┘   │
                                                                    │
┌─────────────────────────────────────────────────────────────────┐ │
│                    WORDPRESS RENDERING ENGINE                   │ │
│                         (continued)                             │ │
│  ┌──────────────────────────────────────────────────────────┐  │ │
│  │ 14. Capture Output                                        │◄─┘
│  │                                                           │  │
│  │     $rendered_html = ob_get_clean();                     │  │
│  │                                                           │  │
│  │     At this point we have complete HTML:                 │  │
│  │     ┌────────────────────────────────────────────────┐   │  │
│  │     │ <!DOCTYPE html>                                │   │  │
│  │     │ <html lang="en-US">                            │   │  │
│  │     │ <head>                                         │   │  │
│  │     │   <meta charset="UTF-8">                       │   │  │
│  │     │   <title>My Post - My Site</title>             │   │  │
│  │     │   <link rel="stylesheet" href="style.css">     │   │  │
│  │     │   <script src="script.js"></script>            │   │  │
│  │     │ </head>                                        │   │  │
│  │     │ <body class="post-template-default single">    │   │  │
│  │     │   <div id="page" class="site">                 │   │  │
│  │     │     <header id="masthead">                     │   │  │
│  │     │       <h1>My Awesome Site</h1>                 │   │  │
│  │     │       <nav>...</nav>                           │   │  │
│  │     │     </header>                                  │   │  │
│  │     │     <main id="main" class="site-main">         │   │  │
│  │     │       <article id="post-123">                  │   │  │
│  │     │         <h1>My Awesome Blog Post</h1>          │   │  │
│  │     │         <div class="entry-content">            │   │  │
│  │     │           <p>This is my post content...</p>    │   │  │
│  │     │         </div>                                 │   │  │
│  │     │       </article>                               │   │  │
│  │     │     </main>                                    │   │  │
│  │     │     <aside id="secondary">...</aside>          │   │  │
│  │     │     <footer id="colophon">                     │   │  │
│  │     │       © My Awesome Site                        │   │  │
│  │     │     </footer>                                  │   │  │
│  │     │   </div>                                       │   │  │
│  │     │   <script src="footer.js"></script>            │   │  │
│  │     │ </body>                                        │   │  │
│  │     │ </html>                                        │   │  │
│  │     └────────────────────────────────────────────────┘   │  │
│  │                                                           │  │
│  │     Return: $rendered_html                                │  │
│  └────────────────────────┬─────────────────────────────────┘  │
└────────────────────────────┼─────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│              WP4BD COMPATIBILITY LAYER (Bridge)                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 15. Post-Processing (Optional)                            │  │
│  │                                                           │  │
│  │     // Fix URLs if needed                                │  │
│  │     $html = str_replace(                                 │  │
│  │       'href="/wp-content/',                              │  │
│  │       'href="/sites/default/files/',                     │  │
│  │       $rendered_html                                     │  │
│  │     );                                                    │  │
│  │                                                           │  │
│  │     // Inject Backdrop admin toolbar                     │  │
│  │     // Add tracking scripts                              │  │
│  │     // etc.                                              │  │
│  │                                                           │  │
│  └────────────────────────┬─────────────────────────────────┘  │
└────────────────────────────┼─────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                      BACKDROP CMS                               │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 16. Return to Backdrop                                    │  │
│  │                                                           │  │
│  │     return array(                                        │  │
│  │       '#markup' => $rendered_html,                       │  │
│  │       '#printed' => TRUE,  // Don't wrap in theme       │  │
│  │     );                                                    │  │
│  │                                                           │  │
│  └────────────────────────┬─────────────────────────────────┘  │
└────────────────────────────┼─────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                         HTTP RESPONSE                           │
│                    Complete HTML Page                           │
└─────────────────────────────────────────────────────────────────┘
```

---

## Key Variables State Changes Throughout Lifecycle

### At Bootstrap (Before WordPress)

```php
// Backdrop has loaded:
$node = [Node object with all Backdrop data]
```

### After Data Transformation (Step 4)

```php
$wp_post = WP_Post {
  ID: 123,
  post_title: "My Awesome Blog Post",
  post_content: "<p>This is my post content...</p>",
  post_date: "2025-12-01 10:30:00",
  post_author: "1",
  post_type: "post",
  post_status: "publish",
  // ... 15+ more properties
}
```

### After Environment Setup (Step 5)

```php
// Global state fully populated:
$GLOBALS['wp_query'] = WP_Query {
  posts: [$wp_post],
  post_count: 1,
  current_post: -1,
  is_single: true,
  is_singular: true,
  queried_object: $wp_post,
  queried_object_id: 123,
  // ... 50+ more properties
}

$GLOBALS['wp_the_query'] = $wp_query;  // Backup
$GLOBALS['post'] = null;  // Set later by the_post()
$GLOBALS['wpdb'] = [MockWPDB object]
```

### After Cache Population (Step 6)

```php
// WordPress object cache populated:
wp_cache: {
  'options': {
    'alloptions': [
      'siteurl' => 'http://example.com',
      'blogname' => 'My Awesome Site',
      'stylesheet' => 'twentyseventeen',
      // ... 50+ options
    ]
  },
  'post_meta': {
    123: [
      '_thumbnail_id' => '456',
      'custom_field' => 'value'
    ]
  }
}
```

### During The Loop (Step 13)

```php
// Before have_posts():
$wp_query->current_post = -1
$GLOBALS['post'] = null

// After the_post() is called:
$wp_query->current_post = 0
$wp_query->in_the_loop = true
$GLOBALS['post'] = $wp_post
$GLOBALS['id'] = 123
$GLOBALS['authordata'] = [WP_User object]
$GLOBALS['page'] = 1
$GLOBALS['pages'] = ["<p>This is my post content...</p>"]
$GLOBALS['multipage'] = 0
$GLOBALS['more'] = 1
```

### During Template Tag Execution

```php
// the_title() reads from:
$GLOBALS['post']->post_title

// the_content() reads from:
$GLOBALS['post']->post_content

// bloginfo('name') reads from:
wp_cache_get('alloptions', 'options')['blogname']

// has_post_thumbnail() reads from:
wp_cache_get(123, 'post_meta')['_thumbnail_id']
```

---

## Function Call Sequence (Chronological)

```
1.  node_load($nid)                           [Backdrop]
2.  _backdrop_node_to_wp_post($node)          [WP4BD Bridge]
3.  _wp4bd_setup_wordpress_environment()      [WP4BD Bridge]
4.  new WP_Query()                            [WordPress Core]
5.  new WP_Post($data)                        [WordPress Core]
6.  wp_cache_add(...)                         [WordPress Core]
7.  require 'wp-includes/load.php'            [WordPress Core]
8.  require 'wp-includes/theme.php'           [WordPress Core]
9.  require $theme_dir/functions.php          [WordPress Theme]
10. add_theme_support(...)                    [WordPress Theme]
11. add_action('wp_enqueue_scripts', ...)     [WordPress Theme]
12. get_query_template('single')              [WordPress Core]
13. ob_start()                                [PHP]
14. require $theme_dir/single.php             [WordPress Theme]
15.   get_header()                            [WordPress Core]
16.     do_action('get_header')               [WordPress Core]
17.     require $theme_dir/header.php         [WordPress Theme]
18.       language_attributes()               [WordPress Core]
19.       bloginfo('charset')                 [WordPress Core]
20.       wp_head()                           [WordPress Core]
21.         do_action('wp_head')              [WordPress Core]
22.         wp_print_styles()                 [WordPress Core]
23.         wp_print_head_scripts()           [WordPress Core]
24.       body_class()                        [WordPress Core]
25.       bloginfo('name')                    [WordPress Core]
26.       wp_nav_menu(...)                    [WordPress Core]
27.   have_posts()                            [WordPress Core]
28.   the_post()                              [WordPress Core]
29.     setup_postdata($post)                 [WordPress Core]
30.   get_template_part('content', 'single')  [WordPress Core]
31.     require $theme_dir/content-single.php [WordPress Theme]
32.       the_ID()                            [WordPress Core]
33.       post_class()                        [WordPress Core]
34.       the_title()                         [WordPress Core]
35.       the_date()                          [WordPress Core]
36.       the_author()                        [WordPress Core]
37.       has_post_thumbnail()                [WordPress Core]
38.       the_post_thumbnail()                [WordPress Core]
39.       the_content()                       [WordPress Core]
40.         apply_filters('the_content', ...) [WordPress Core]
41.       the_tags()                          [WordPress Core]
42.       the_category()                      [WordPress Core]
43.   get_sidebar()                           [WordPress Core]
44.     do_action('get_sidebar')              [WordPress Core]
45.     require $theme_dir/sidebar.php        [WordPress Theme]
46.       is_active_sidebar(...)              [WordPress Core]
47.       dynamic_sidebar(...)                [WordPress Core]
48.   get_footer()                            [WordPress Core]
49.     do_action('get_footer')               [WordPress Core]
50.     require $theme_dir/footer.php         [WordPress Theme]
51.       wp_footer()                         [WordPress Core]
52.         do_action('wp_footer')            [WordPress Core]
53.         wp_print_footer_scripts()         [WordPress Core]
54. ob_get_clean()                            [PHP]
55. return $rendered_html                     [WP4BD Bridge]
```

---

## Critical Integration Points

### 1. **Backdrop → WordPress Data Mapping**

```php
function _backdrop_node_to_wp_post($node) {
  return new WP_Post((object) array(
    'ID' => $node->nid,
    'post_title' => $node->title,
    'post_content' => $node->body['und'][0]['value'],
    'post_excerpt' => text_summary($node->body['und'][0]['value']),
    'post_name' => $node->path['alias'] ?? "node/{$node->nid}",
    'post_type' => $node->type === 'page' ? 'page' : 'post',
    'post_status' => $node->status ? 'publish' : 'draft',
    'post_author' => (string) $node->uid,
    'post_date' => date('Y-m-d H:i:s', $node->created),
    'post_date_gmt' => gmdate('Y-m-d H:i:s', $node->created),
    'post_modified' => date('Y-m-d H:i:s', $node->changed),
    'post_modified_gmt' => gmdate('Y-m-d H:i:s', $node->changed),
    'post_parent' => 0,
    'guid' => url("node/{$node->nid}", array('absolute' => TRUE)),
    'menu_order' => 0,
    'comment_status' => $node->comment ?? 2 ? 'open' : 'closed',
    'ping_status' => 'closed',
    'comment_count' => '0',
    'post_password' => '',
    'filter' => 'raw',
  ));
}
```

### 2. **Database Isolation**

WordPress never queries the database because:
1. Mock `wpdb` class returns empty results
2. All data pre-populated in object cache
3. All globals set before WordPress loads

### 3. **Output Capture**

```php
ob_start();
require $template_file;
$html = ob_get_clean();
```

This captures ALL echo/print statements from WordPress and theme.

---

## Performance Considerations

### Potential Bottlenecks

1. **WordPress Bootstrap** - Loading 20+ WordPress core files
   - *Mitigation:* Use opcode cache (OPcache)

2. **Hook System** - Processing do_action/apply_filters
   - *Mitigation:* Limit registered hooks

3. **File I/O** - Multiple template file requires
   - *Mitigation:* APC/OpCache will cache compiled files

### Caching Strategy

```php
// Cache the final HTML output
$cache_key = "wp4bd:node:{$node->nid}:{$node->changed}";
$cached = cache_get($cache_key);

if ($cached) {
  return $cached->data;
}

// Render via WordPress
$html = _wp4bd_render_with_wordpress($node);

// Cache for 1 hour
cache_set($cache_key, $html, 'cache', time() + 3600);

return $html;
```

---

## Next Steps

This lifecycle diagram shows that the WP4BD approach is **highly feasible**:

1. ✅ Clear separation between Backdrop (data) and WordPress (presentation)
2. ✅ Well-defined integration points
3. ✅ Minimal state needed to be set up
4. ✅ Standard WordPress themes work without modification

**Ready to implement!**
