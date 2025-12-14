# WordPress Multi-Theme Analysis

## Making wp2bd Theme-Agnostic

 

**Date**: November 22, 2025

**Themes Analyzed**: Twenty Fourteen (2014), Twenty Fifteen (2015), Twenty Sixteen (2016), Twenty Seventeen (2017)

**Purpose**: Identify what's WordPress core vs theme-specific to enable seamless theme switching

 

---

 

## Executive Summary

 

After analyzing 4 WordPress default themes (2014-2017), I've identified clear patterns that allow us to create a **generic, theme-agnostic system**. The key insight: **WordPress core provides consistent hooks and functions**, while **themes control only the HTML wrapper structure and CSS classes**.

 

**Bottom Line**: We can create ONE Backdrop layout that adapts to ANY WordPress theme by:

1. Letting themes control their own wrapper divs (via their unmodified header.php/footer.php)

2. Using WordPress core functions that work across ALL themes

3. Stripping only the absolute minimum HTML (DOCTYPE, html, head, body tags)

4. Letting theme CSS do the heavy lifting

 

---

 

## Part 1: WordPress Core (Universal - Works Across ALL Themes)

 

These elements appear in **every** WordPress theme and are controlled by WordPress core, not individual themes:

 

### Core Hooks (Always Present)

```php

wp_head()          // Outputs CSS, JS, meta tags - called in <head>

wp_footer()        // Outputs footer JS - called before </body>

wp_body_open()     // Hook after <body> tag (WP 5.2+)

body_class()       // Generates body CSS classes

language_attributes()  // Outputs lang="..." for <html>

```

 

### Core Template Functions (Universal API)

```php

// Site Information

bloginfo('name')              // Site title

bloginfo('description')       // Tagline

bloginfo('charset')           // Character set

bloginfo('pingback_url')      // Pingback URL

get_bloginfo()               // Generic getter

 

// Template Loading

get_header()                  // Load header.php

get_footer()                  // Load footer.php

get_sidebar()                 // Load sidebar.php

get_template_part()           // Load partial templates

 

// Navigation

wp_nav_menu()                 // Output navigation menu

has_nav_menu()                // Check if menu exists

 

// Widgets/Sidebars

dynamic_sidebar('sidebar-1')  // Output widgets for a sidebar

is_active_sidebar('sidebar-1') // Check if sidebar has widgets

register_sidebar()            // Register widget area (in functions.php)

 

// Paths

get_template_directory()      // /path/to/theme

get_template_directory_uri()  // http://site.com/wp-content/themes/themename

get_stylesheet_directory()    // Same as template (for child themes)

get_stylesheet_directory_uri()

 

// Headers/Images

get_header_image()            // Get custom header image URL

header_image()                // Echo header image URL

the_custom_header_markup()    // Output header markup (2017)

 

// URLs

home_url('/')                 // Site home URL

esc_url()                     // Escape URL

```

 

### Core Content Loop (Universal)

```php

if (have_posts()) {

    while (have_posts()) {

        the_post();          // Set up post data

        the_title();         // Output post title

        the_content();       // Output post content

        the_excerpt();       // Output excerpt

        // etc.

    }

}

```

 

### Core Conditional Tags (Universal)

```php

is_front_page()    // Is this the front page?

is_home()          // Is this the blog home?

is_single()        // Is this a single post?

is_page()          // Is this a page?

is_archive()       // Is this an archive?

// ALL conditional tags work across themes

```

 

---

 

## Part 2: Theme-Specific Elements

 

These vary between themes and define each theme's unique appearance:

 

### 1. Wrapper Div Structure (HIGHLY Variable)

 

**Twenty Fourteen (2014):**

```html

<div id="page" class="hfeed site">

  <header id="masthead" class="site-header">...</header>

  <div id="main" class="site-main">

    <div id="main-content" class="main-content">

      <div id="primary" class="content-area">

        <div id="content" class="site-content">

```

 

**Twenty Fifteen (2015):**

```html

<div id="page" class="hfeed site">

  <div id="sidebar" class="sidebar">  <!-- SIDEBAR FIRST! -->

    <header id="masthead" class="site-header">...</header>

    <div id="secondary" class="secondary"><!-- widgets --></div>

  </div>

  <div id="content" class="site-content">

```

 

**Twenty Sixteen (2016):**

```html

<div id="page" class="site">

  <div class="site-inner">

    <header id="masthead" class="site-header">...</header>

    <div id="content" class="site-content">

      <div id="primary" class="content-area">

```

 

**Twenty Seventeen (2017):**

```html

<div id="page" class="site">

  <header id="masthead" class="site-header">

    <div class="custom-header">

      <div class="custom-header-media">...</div>

    </div>

  </header>

  <div class="site-content-contain">  <!-- PARALLAX WRAPPER -->

    <div id="content" class="site-content">

      <div class="wrap">

```

 

**Key Observation**: Wrapper structure is ENTIRELY theme-specific. No two themes use the same nesting pattern.

 

### 2. Sidebar/Widget Areas (Varies)

 

**Sidebar IDs** (theme-specific):

- 2014: `sidebar-1`, `sidebar-2`, `sidebar-3` (primary, content, footer)

- 2015: `sidebar-1` (main sidebar)

- 2016: `sidebar-1`, `sidebar-content-bottom`

- 2017: `sidebar-1`, `sidebar-2`, `sidebar-3` (blog, footer1, footer2)

 

**Widget Wrappers** (theme-specific):

```php

// Twenty Fourteen

'before_widget' => '<aside id="%1$s" class="widget %2$s">',

'after_widget'  => '</aside>',

'before_title'  => '<h1 class="widget-title">',

'after_title'   => '</h1>',

 

// Twenty Seventeen

'before_widget' => '<section id="%1$s" class="widget %2$s">',

'after_widget'  => '</section>',

'before_title'  => '<h2 class="widget-title">',

'after_title'   => '</h2>',

```

 

**Sidebar Placement**:

- 2014: Left sidebar (dark)

- 2015: Left sidebar (in header wrapper!)

- 2016: Right sidebar

- 2017: Right sidebar

 

---

 

## Part 3: Critical Pattern Recognition

 

### The Template File Pattern (Universal)

 

ALL themes follow this structure:

 

**header.php**:

1. DOCTYPE, html, head tags

2. `wp_head()` in `<head>`

3. `<body <?php body_class(); ?>>`

4. Opening wrapper divs (theme-specific)

5. `<header id="masthead">` with branding

6. Opens content wrapper divs

7. **STOPS** (doesn't close divs)

 

**footer.php**:

1. Closes content wrapper divs

2. `<footer id="colophon" class="site-footer">`

3. Footer content/widgets

4. Closes `</footer>`

5. Closes all wrapper divs opened in header

6. `<?php wp_footer(); ?>`

7. `</body></html>`

 

**sidebar.php**:

1. Conditional check: `if (is_active_sidebar('sidebar-1'))`

2. Opening sidebar wrapper

3. `<?php dynamic_sidebar('sidebar-1'); ?>`

4. Closing sidebar wrapper

 

---

 

## Part 4: Stripping Strategy Analysis

 

### Current Approach (Twenty Seventeen Specific) ⚠️

 

Our current code strips:

```php

// In _wp_content_render_header():

preg_replace('/^.*?<body[^>]*>/is', '', $output);

preg_replace('/<div\s+id="page"\s+class="site">\s*/is', '', $output);

preg_replace('/<div\s+class="site-content-contain">\s*<div\s+id="content"\s+class="site-content">\s*$/is', '', $output);

```

 

**Problem**: These regexes are **hardcoded for Twenty Seventeen's structure**. They'll fail on:

- 2015's `<div id="sidebar" class="sidebar">` structure

- 2016's `<div class="site-inner">` wrapper

- 2014's different div nesting

 

### Recommended Approach (Theme-Agnostic) ✅

 

**MINIMAL STRIPPING** - Only remove what Backdrop must control:

 

```php

function _wp_content_render_header() {

    // ... existing ob_start() and include logic ...

 

    $output = ob_get_clean();

 

    // Strip only the HTML page structure that Backdrop controls

    // Remove everything from start through opening <body> tag

    $output = preg_replace('/^.*?<body[^>]*>/is', '', $output);

 

    // Remove closing </body> and </html> if present

    $output = preg_replace('/<\/body>\s*<\/html>\s*$/is', '', $output);

 

    // IMPORTANT: DO NOT strip any <div> wrappers!

    // Let the theme control its own structure

 

    return array('#markup' => $output);

}

 

function _wp_content_render_footer() {

    // ... existing ob_start() and include logic ...

 

    $output = ob_get_clean();

 

    // Strip only closing </body></html> tags

    $output = preg_replace('/<\/body>\s*<\/html>\s*$/is', '', $output);

 

    // IMPORTANT: DO NOT strip closing </div> tags!

    // Let the theme close what it opened

 

    return array('#markup' => $output);

}

```

 

**Why This Works**:

1. Themes open their wrapper divs in header.php

2. Themes close their wrapper divs in footer.php

3. Backdrop just needs to prevent duplicate `<html>`/`<body>` tags

4. Theme CSS targets theme-specific wrapper classes

5. No brittle regex patterns to maintain

 

---

 

## Part 5: Layout Strategy

 

### Recommended: Minimal Backdrop Layout

 

Create ONE simple layout that just provides basic structure:

 

```php

// layout--wordpress-generic.tpl.php

<div class="layout--wordpress-generic">

  <?php if ($messages): ?>

    <div class="l-messages"><?php print $messages; ?></div>

  <?php endif; ?>

 

  <?php if ($tabs): ?>

    <nav class="tabs"><?php print $tabs; ?></nav>

  <?php endif; ?>

 

  <!-- WordPress Header block outputs full header with its wrapper divs -->

  <?php if ($content['header']): ?>

    <?php print $content['header']; ?>

  <?php endif; ?>

 

  <!-- WordPress Content block outputs main content -->

  <?php if ($content['content']): ?>

    <?php print $content['content']; ?>

  <?php endif; ?>

 

  <!-- WordPress Sidebar block outputs sidebar -->

  <?php if ($content['sidebar']): ?>

    <?php print $content['sidebar']; ?>

  <?php endif; ?>

 

  <!-- WordPress Footer block outputs footer and closes all divs -->

  <?php if ($content['footer']): ?>

    <?php print $content['footer']; ?>

  <?php endif; ?>

</div>

```

 

**Advantages**:

- Works with ANY WordPress theme

- No theme-specific logic

- Themes control their own structure

- Easy to maintain

 

**How it works**:

1. Header block outputs: `<div id="page"><header>...</header><div class="content-wrapper">`

2. Content block outputs: `<div id="primary"><main>...`

3. Sidebar block outputs: `<aside>...</aside>`

4. Footer block outputs: `</div><!-- content-wrapper --><footer>...</footer></div><!-- #page -->`

 

---

 

## Part 6: Widget System

 

### The Problem

 

When Backdrop calls `dynamic_sidebar('sidebar-1')`:

- WordPress looks for widgets in `wp_options` table

- No widgets exist (we're in Backdrop, not WordPress)

- Returns empty output

 

### Recommended Solution: Dynamic Generation

 

Generate widgets dynamically from Backdrop content:

 

```php

function dynamic_sidebar($sidebar_id) {

    $sidebar_config = _wp2bd_get_sidebar_config($sidebar_id);

    $widgets = _wp2bd_generate_widgets_from_backdrop($sidebar_id);

 

    foreach ($widgets as $widget) {

        echo $widget['before_widget'];

        if ($widget['title']) {

            echo $widget['before_title'] . $widget['title'] . $widget['after_title'];

        }

        echo $widget['content'];

        echo $widget['after_widget'];

    }

}

 

function _wp2bd_render_recent_posts_widget($config) {

    // Get recent Backdrop nodes

    $nids = db_select('node', 'n')

        ->fields('n', array('nid'))

        ->condition('status', 1)

        ->orderBy('created', 'DESC')

        ->range(0, 5)

        ->execute()

        ->fetchCol();

 

    $nodes = node_load_multiple($nids);

 

    ob_start();

    echo $config['before_title'] . 'Recent Posts' . $config['after_title'];

    echo '<ul>';

    foreach ($nodes as $node) {

        $url = url('node/' . $node->nid);

        echo '<li><a href="' . $url . '">' . check_plain($node->title) . '</a></li>';

    }

    echo '</ul>';

    return ob_get_clean();

}

```

 

---

 

## Part 7: Implementation Phases

 

### Phase 1: Simplify Stripping (High Priority) ⚡

 

**Changes to `wp_content.module`**:

- Remove all theme-specific regex patterns

- Keep ONLY DOCTYPE/html/head/body stripping

- Let themes control wrapper divs

 

**Result**: Header/footer rendering works with ANY theme

 

### Phase 2: Generic Layout (High Priority) ⚡

 

**New file**: `layouts/wordpress/layout--wordpress.tpl.php`

- Minimal wrapper

- Just outputs the 4 blocks (header, content, sidebar, footer)

- No theme-specific logic

 

**Result**: One layout for all themes

 

### Phase 3: Dynamic Theme Switching (Medium Priority)

 

**Add config**: `modules/wp_content/config/wp_content.settings.json`

```json

{

  "active_theme": "twentyseventeen",

  "available_themes": ["twentyfourteen", "twentyfifteen", ...]

}

```

 

**Result**: Switch themes via config, no code changes

 

### Phase 4: Dynamic Widgets (Medium Priority)

 

**New file**: `themes/wp/functions/widgets.php`

- Override `dynamic_sidebar()`

- Generate widgets from Backdrop data

- Use theme-specific HTML wrappers

 

**Result**: Real widgets with Backdrop data

 

---

 

## Part 8: Key Takeaways

 

### What We Learned

 

1. **WordPress Core is Remarkably Consistent**

   - Same hooks in all themes: `wp_head()`, `wp_footer()`, `body_class()`

   - Same template functions work everywhere

   - Same template structure: header.php, footer.php, sidebar.php

 

2. **Themes Only Control Presentation**

   - Wrapper div structure

   - CSS classes

   - HTML element choices

   - Widget wrapper tags

 

3. **The Template Hierarchy is Universal**

   - All themes follow: get_header() → loop → get_sidebar() → get_footer()

 

4. **Widgets are Theme-Independent**

   - Core widget types work everywhere

   - Only wrapper HTML changes per theme

 

### Critical Success Factors

 

1. ✅ **Don't strip theme wrapper divs** - Themes need control

2. ✅ **Only strip HTML/HEAD/BODY** - Backdrop must control document

3. ✅ **Let theme CSS do the work** - Don't add conflicting CSS

4. ✅ **Use WordPress core functions** - They work across all themes

5. ✅ **Generate widgets dynamically** - Pull from Backdrop, wrap in theme HTML

6. ✅ **Use generic layout** - Don't hard-code theme-specific structure

 

### The Key Insight

 

**WordPress themes are self-contained presentation layers that rely on core's universal API.**

 

Because WordPress core provides consistent hooks and functions, and themes merely wrap that functionality in different HTML/CSS, we can:

- Load ANY theme's files

- Call the same WordPress functions

- Get theme-appropriate output

- Without theme-specific code in wp2bd

 

---

 

## Conclusion

 

**TL;DR**: Strip less, trust themes more, use WordPress core's universal API, and ANY theme will work.

 

By following WordPress core's patterns and avoiding theme-specific assumptions, we can create a truly universal compatibility layer that works with thousands of WordPress themes without modification.

 