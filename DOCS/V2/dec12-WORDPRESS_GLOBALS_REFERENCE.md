# WordPress 4.9 Global Variables & Data Structures Reference

## Complete Guide for Pre-Populating WordPress Rendering Engine

**Last Updated:** 2025-12-02
**WordPress Version:** 4.9
**Purpose:** Reference for building a WordPress rendering engine with pre-populated Backdrop data

---

## Table of Contents

1. [Essential Globals (MUST HAVE)](#essential-globals-must-have)
2. [WP_Query Properties](#wp_query-properties)
3. [WP_Post Properties](#wp_post-properties)
4. [WP_User Properties](#wp_user-properties)
5. [Optional Globals (Nice to Have)](#optional-globals-nice-to-have)
6. [WordPress Constants](#wordpress-constants)
7. [WordPress Options (Database Settings)](#wordpress-options-database-settings)
8. [Hook System Globals](#hook-system-globals)
9. [Template Globals](#template-globals)
10. [Minimum Viable Set](#minimum-viable-set)

---

## Essential Globals (MUST HAVE)

These globals are critical for themes to render properly.

### 1. `$wp_query` (WP_Query instance)
**Type:** `WP_Query` object
**Purpose:** Main query object - controls The Loop and conditional tags
**Initialized in:** `wp-settings.php` line 347-355

```php
$GLOBALS['wp_the_query'] = new WP_Query();
$GLOBALS['wp_query'] = $GLOBALS['wp_the_query'];
```

**Critical for:**
- `have_posts()`, `the_post()`, `wp_reset_postdata()`
- All conditional tags: `is_single()`, `is_page()`, `is_home()`, etc.
- Loop iteration

### 2. `$wp_the_query` (WP_Query instance)
**Type:** `WP_Query` object
**Purpose:** Backup of original query (used in `wp_reset_query()`)
**Relationship:** Initially same instance as `$wp_query`

### 3. `$post` (WP_Post instance)
**Type:** `WP_Post` object
**Purpose:** Current post in The Loop
**Set by:** `WP_Query::the_post()` via `setup_postdata()`

**Critical for:**
- `the_title()`, `the_content()`, `the_excerpt()`
- `get_the_ID()`, `get_permalink()`
- Template part context

### 4. `$posts` (array of WP_Post objects)
**Type:** `array`
**Purpose:** All posts for current query
**Set by:** `WP_Query::get_posts()`
**Made available to templates via:** `load_template()` extracts `$wp_query->query_vars`

### 5. `$wpdb` (wpdb instance)
**Type:** `wpdb` object
**Purpose:** Database abstraction layer
**Initialized in:** `wp-settings.php` line 105-106

```php
global $wpdb;
require_wp_db();
```

**Properties needed:**
- `$wpdb->prefix` - Table prefix (e.g., 'wp_')
- `$wpdb->posts` - Posts table name
- `$wpdb->postmeta` - Post meta table name
- `$wpdb->users` - Users table name
- `$wpdb->usermeta` - User meta table name

### 6. `$wp_rewrite` (WP_Rewrite instance)
**Type:** `WP_Rewrite` object
**Purpose:** URL rewriting/permalink structure
**Initialized in:** `wp-settings.php` line 362

```php
$GLOBALS['wp_rewrite'] = new WP_Rewrite();
```

**Key properties:**
- `permalink_structure` - Permalink pattern
- `use_trailing_slashes` - Whether to add trailing slashes
- `index` - 'index.php'
- `front` - Static portion of permalink structure

### 7. `$wp` (WP instance)
**Type:** `WP` object
**Purpose:** Main WordPress environment object
**Initialized in:** `wp-settings.php` line 369

```php
$GLOBALS['wp'] = new WP();
```

**Key properties:**
- `query_vars` - Parsed query variables
- `query_string` - Original query string
- `request` - Request path
- `matched_rule` - Matched rewrite rule
- `matched_query` - Matched rewrite query

---

## WP_Query Properties

Complete property list for `WP_Query` class (`/wp-includes/class-wp-query.php`)

### Query Variables
```php
public $query;              // Query vars set by user (array)
public $query_vars = array(); // Query vars after parsing (array)
```

### Query Objects
```php
public $tax_query;          // WP_Tax_Query object
public $meta_query = false; // WP_Meta_Query object
public $date_query = false; // WP_Date_Query object
```

### Queried Object Data
```php
public $queried_object;     // Current queried object (post/term/author)
public $queried_object_id;  // ID of queried object (int)
```

### Posts Data
```php
public $request;            // SQL query string
public $posts;              // Array of WP_Post objects
public $post_count = 0;     // Number of posts (int)
public $post;               // Current WP_Post object
```

### Loop Control
```php
public $current_post = -1;  // Current post index in loop (int)
public $in_the_loop = false; // Whether in the loop (bool)
```

### Pagination
```php
public $found_posts = 0;    // Total posts matching query (int)
public $max_num_pages = 0;  // Total pages (int)
```

### Comments
```php
public $comments;           // Array of comment objects
public $comment_count = 0;  // Number of comments (int)
public $current_comment = -1; // Current comment index (int)
public $comment;            // Current comment ID (int)
public $max_num_comment_pages = 0; // Total comment pages (int)
```

### Conditional Flags (Boolean Properties)
**CRITICAL:** These control all `is_*()` conditional tags

```php
// Post Type Conditionals
public $is_single = false;      // Single post
public $is_page = false;        // Single page
public $is_attachment = false;  // Attachment page
public $is_singular = false;    // Any single post/page/attachment

// Archive Conditionals
public $is_archive = false;     // Any archive
public $is_post_type_archive = false; // Custom post type archive
public $is_author = false;      // Author archive
public $is_category = false;    // Category archive
public $is_tag = false;         // Tag archive
public $is_tax = false;         // Custom taxonomy archive

// Date Archive Conditionals
public $is_date = false;        // Date archive
public $is_year = false;        // Year archive
public $is_month = false;       // Month archive
public $is_day = false;         // Day archive
public $is_time = false;        // Time archive

// Special Pages
public $is_home = false;        // Blog homepage
public $is_front_page = false;  // Site front page
public $is_search = false;      // Search results
public $is_404 = false;         // 404 page
public $is_paged = false;       // Paged result (page 2+)
public $is_admin = false;       // Admin page

// Feed & Special
public $is_feed = false;        // Feed request
public $is_comment_feed = false; // Comment feed
public $is_trackback = false;   // Trackback
public $is_preview = false;     // Post preview
public $is_robots = false;      // Robots.txt
public $is_embed = false;       // Embedded post
```

### Example: Setting up WP_Query for a Single Post

```php
$wp_query = new WP_Query();

// Set posts
$wp_query->posts = array($wp_post_object);
$wp_query->post_count = 1;
$wp_query->found_posts = 1;
$wp_query->max_num_pages = 1;
$wp_query->current_post = -1;

// Set queried object
$wp_query->queried_object = $wp_post_object;
$wp_query->queried_object_id = $wp_post_object->ID;

// Set conditional flags
$wp_query->is_single = true;
$wp_query->is_singular = true;

// Set query vars
$wp_query->query_vars = array(
    'p' => $post_id,
    'post_type' => 'post',
);
```

---

## WP_Post Properties

Complete property list for `WP_Post` class (`/wp-includes/class-wp-post.php`)

All properties are **public** and must be set:

```php
// Core Identifiers
public $ID;                 // Post ID (int)
public $post_author = 0;    // Author ID (string for compatibility)
public $guid = '';          // Globally unique identifier (string)

// Content
public $post_title = '';           // Post title (string)
public $post_content = '';         // Post content (string)
public $post_excerpt = '';         // Post excerpt (string)
public $post_content_filtered = ''; // Filtered content (rarely used)

// Post Meta
public $post_name = '';            // Post slug (string)
public $post_status = 'publish';   // Post status (string)
public $post_type = 'post';        // Post type (string)
public $post_mime_type = '';       // MIME type for attachments (string)

// Dates (MySQL datetime format: 'YYYY-MM-DD HH:MM:SS')
public $post_date = '0000-00-00 00:00:00';       // Local time
public $post_date_gmt = '0000-00-00 00:00:00';   // GMT time
public $post_modified = '0000-00-00 00:00:00';   // Local modified time
public $post_modified_gmt = '0000-00-00 00:00:00'; // GMT modified time

// Hierarchy
public $post_parent = 0;    // Parent post ID (int)
public $menu_order = 0;     // Order for menu/pages (int)

// Comments & Pings
public $comment_status = 'open';   // 'open' or 'closed' (string)
public $ping_status = 'open';      // 'open' or 'closed' (string)
public $comment_count = 0;         // Number of comments (string for compatibility)
public $to_ping = '';              // URLs to ping (string)
public $pinged = '';               // URLs already pinged (string)

// Security
public $post_password = '';  // Password protection (string)

// Internal
public $filter;             // Sanitization level (string)
```

### Magic Properties (computed via `__get()`)
These don't need to be set directly:

```php
$post->page_template  // Returns get_post_meta($ID, '_wp_page_template', true)
$post->post_category  // Returns array of category term IDs
$post->tags_input     // Returns array of tag names
$post->ancestors      // Returns get_post_ancestors($post)
```

### Example: Creating a WP_Post Object

```php
$post_data = new stdClass();
$post_data->ID = 123;
$post_data->post_author = '1';
$post_data->post_date = '2025-12-01 10:30:00';
$post_data->post_date_gmt = '2025-12-01 15:30:00';
$post_data->post_content = 'This is the post content.';
$post_data->post_title = 'Sample Post Title';
$post_data->post_excerpt = 'This is the excerpt.';
$post_data->post_status = 'publish';
$post_data->comment_status = 'open';
$post_data->ping_status = 'open';
$post_data->post_password = '';
$post_data->post_name = 'sample-post-title';
$post_data->to_ping = '';
$post_data->pinged = '';
$post_data->post_modified = '2025-12-01 10:30:00';
$post_data->post_modified_gmt = '2025-12-01 15:30:00';
$post_data->post_content_filtered = '';
$post_data->post_parent = 0;
$post_data->guid = 'http://example.com/?p=123';
$post_data->menu_order = 0;
$post_data->post_type = 'post';
$post_data->post_mime_type = '';
$post_data->comment_count = '0';
$post_data->filter = 'raw';

$post = new WP_Post($post_data);
```

---

## WP_User Properties

Complete property list for `WP_User` class (`/wp-includes/class-wp-user.php`)

```php
// Core Data
public $data;       // stdClass containing user data from database
public $ID = 0;     // User ID (int)

// Capabilities
public $caps = array();       // Individual capabilities (array)
public $cap_key;              // Capability meta key (string)
public $roles = array();      // User roles (array)
public $allcaps = array();    // All capabilities (array)

// Internal
public $filter = null;        // Filter context (string)
private $site_id = 0;         // Site ID for capabilities (int)
```

### User Data Object Properties (`$user->data`)
These are accessed via magic `__get()`:

```php
$user->ID               // User ID (int)
$user->user_login       // Username (string)
$user->user_pass        // Password hash (string)
$user->user_nicename    // URL-friendly username (string)
$user->user_email       // Email address (string)
$user->user_url         // Website URL (string)
$user->user_registered  // Registration date (datetime string)
$user->user_activation_key  // Activation key (string)
$user->user_status      // User status (int)
$user->display_name     // Display name (string)
```

### User Meta Properties (via `get_user_meta()`)
```php
$user->nickname         // Nickname
$user->first_name       // First name
$user->last_name        // Last name
$user->description      // Bio/description
$user->locale           // User locale
$user->rich_editing     // Visual editor enabled
$user->syntax_highlighting // Syntax highlighting
```

### Example: Creating a WP_User Object

```php
$user_data = new stdClass();
$user_data->ID = 1;
$user_data->user_login = 'admin';
$user_data->user_pass = '$P$Bhashed_password_here';
$user_data->user_nicename = 'admin';
$user_data->user_email = 'admin@example.com';
$user_data->user_url = '';
$user_data->user_registered = '2025-01-01 00:00:00';
$user_data->user_activation_key = '';
$user_data->user_status = 0;
$user_data->display_name = 'Administrator';

$user = new WP_User($user_data);
```

---

## Optional Globals (Nice to Have)

These globals are used by various WordPress functions but may not be critical for basic rendering.

### Environment & Browser Detection
**Defined in:** `wp-includes/vars.php`

```php
global $pagenow;        // Current page filename (e.g., 'index.php')

// Browser detection (all boolean)
global $is_lynx, $is_gecko, $is_winIE, $is_macIE, $is_opera;
global $is_NS4, $is_safari, $is_chrome, $is_iphone, $is_IE, $is_edge;

// Server detection (all boolean)
global $is_apache, $is_IIS, $is_iis7, $is_nginx;
```

**Typical values for rendering engine:**
```php
$pagenow = 'index.php';  // Or whatever makes sense for your context
$is_apache = true;       // Assume Apache for compatibility
// All browser flags = false unless you need specific detection
```

### Widget & Sidebar Globals
**Defined in:** `wp-includes/widgets.php`

```php
global $wp_registered_sidebars;
global $wp_registered_widgets;
global $wp_registered_widget_controls;
global $wp_registered_widget_updates;
```

**Initialized in:** `wp-settings.php` line 376
```php
$GLOBALS['wp_widget_factory'] = new WP_Widget_Factory();
```

### Hook System Globals
**Defined in:** `wp-includes/plugin.php`

```php
global $wp_filter;          // Array of WP_Hook objects
global $wp_actions;         // Array tracking action execution counts
global $wp_current_filter;  // Stack of currently executing filters
```

### Role & Capability Globals
**Initialized in:** `wp-settings.php` line 383

```php
$GLOBALS['wp_roles'] = new WP_Roles();
```

### Locale Globals
**Initialized in:** `wp-settings.php` lines 409-419

```php
$GLOBALS['wp_locale'] = new WP_Locale();
$GLOBALS['wp_locale_switcher'] = new WP_Locale_Switcher();
```

### Embed & Media Globals
**Initialized in:** `wp-settings.php` line 243

```php
$GLOBALS['wp_embed'] = new WP_Embed();
```

### Template Globals (Set by `setup_postdata()`)
**Set in:** `WP_Query::setup_postdata()` (line 3955)

These are set when `the_post()` is called:

```php
global $id;              // Current post ID (int)
global $authordata;      // WP_User object for post author
global $currentday;      // Current post day (string, format: 'd.m.y')
global $currentmonth;    // Current post month (string, format: 'm')
global $page;            // Current page of paginated post (int)
global $pages;           // Array of post content pages (array)
global $multipage;       // Whether post has multiple pages (int, 0 or 1)
global $more;            // Whether to show full content or excerpt (int, 0 or 1)
global $numpages;        // Number of pages in post (int)
```

### Version & Core Globals
**Defined in:** `wp-includes/version.php` (loaded line 28-29)

```php
global $wp_version;              // WordPress version (e.g., '4.9.25')
global $wp_db_version;           // Database version
global $tinymce_version;         // TinyMCE version
global $required_php_version;    // Required PHP version
global $required_mysql_version;  // Required MySQL version
global $wp_local_package;        // Localization package
```

### Multisite Globals
**Defined in:** `wp-includes/ms-settings.php` (if multisite)

```php
global $current_site;    // Current site object
global $current_blog;    // Current blog object
global $domain;          // Current domain
global $path;            // Current path
global $site_id;         // Current site ID
global $public;          // Whether site is public
global $blog_id;         // Current blog ID (default: 1 for single site)
```

### Comment Globals
**Set by:** `load_template()` extracts from `$wp_query->query_vars`

```php
global $comment;         // Current comment object
global $user_ID;         // Current user ID (note: uppercase ID)
```

### KSES (HTML Filtering) Globals
**Defined in:** `wp-includes/kses.php`

```php
global $allowedposttags;      // Allowed HTML tags for posts
global $allowedtags;          // Allowed HTML tags for comments
global $allowedentitynames;   // Allowed HTML entities
```

---

## WordPress Constants

These constants must be defined for WordPress to function.

### Critical Path Constants
**Defined in:** Various initialization files

```php
// Core Paths (MUST define before loading WordPress)
define('ABSPATH', '/path/to/wordpress/');  // Trailing slash required
define('WPINC', 'wp-includes');            // wp-includes directory name

// Content Paths (auto-defined if not set)
define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
define('WP_CONTENT_URL', 'http://example.com/wp-content');
define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');

// Theme Paths (set by wp_templating_constants())
define('TEMPLATEPATH', '/path/to/theme/template');
define('STYLESHEETPATH', '/path/to/theme/stylesheet');
```

### Debug & Development Constants
**Defined in:** `wp-includes/default-constants.php`

```php
define('WP_DEBUG', false);          // Enable debug mode
define('WP_DEBUG_DISPLAY', true);   // Display errors
define('WP_DEBUG_LOG', false);      // Log errors to debug.log
define('SCRIPT_DEBUG', false);      // Use unminified scripts
define('WP_CACHE', false);          // Enable caching
```

### Memory Constants
**Defined in:** `wp-includes/default-constants.php`

```php
define('WP_MEMORY_LIMIT', '40M');      // Regular memory limit
define('WP_MAX_MEMORY_LIMIT', '256M'); // Admin memory limit
```

### Data Size Constants
**Defined in:** `wp-includes/default-constants.php`

```php
define('KB_IN_BYTES', 1024);
define('MB_IN_BYTES', 1024 * KB_IN_BYTES);
define('GB_IN_BYTES', 1024 * MB_IN_BYTES);
define('TB_IN_BYTES', 1024 * GB_IN_BYTES);
```

### Multisite Constants

```php
define('MULTISITE', false);         // Whether multisite is enabled
define('SUBDOMAIN_INSTALL', false); // Subdomain vs subdirectory multisite
```

### Other Important Constants

```php
define('WP_POST_REVISIONS', true);  // Enable post revisions
define('AUTOSAVE_INTERVAL', 60);    // Autosave interval in seconds
define('WP_CRON_LOCK_TIMEOUT', 60); // Cron lock timeout
```

---

## WordPress Options (Database Settings)

These are typically retrieved via `get_option()` and affect theme rendering.

### Theme Options
```php
get_option('stylesheet')        // Active child theme directory name
get_option('template')          // Active parent theme directory name
get_option('stylesheet_root')   // Path to stylesheet theme root
get_option('template_root')     // Path to template theme root
get_option('current_theme')     // Theme name (deprecated but still used)
```

### Site Options
```php
get_option('siteurl')           // WordPress installation URL
get_option('home')              // Site front-end URL
get_option('blogname')          // Site title
get_option('blogdescription')   // Site tagline
get_option('admin_email')       // Admin email address
```

### Reading Settings
```php
get_option('show_on_front')     // 'posts' or 'page'
get_option('page_on_front')     // Front page ID (if show_on_front = 'page')
get_option('page_for_posts')    // Posts page ID (if show_on_front = 'page')
get_option('posts_per_page')    // Number of posts per page (default: 10)
get_option('posts_per_rss')     // Number of posts in RSS feed
```

### Permalink Settings
```php
get_option('permalink_structure')  // Permalink structure pattern
get_option('category_base')        // Category permalink base
get_option('tag_base')             // Tag permalink base
```

### Discussion Settings
```php
get_option('thread_comments')      // Enable threaded comments
get_option('comments_per_page')    // Comments per page
get_option('default_comments_page') // 'newest' or 'oldest'
get_option('comment_order')        // 'asc' or 'desc'
```

### Date/Time Settings
```php
get_option('date_format')       // PHP date format string
get_option('time_format')       // PHP time format string
get_option('timezone_string')   // Timezone identifier (e.g., 'America/New_York')
get_option('gmt_offset')        // GMT offset in hours
get_option('start_of_week')     // 0 (Sunday) through 6 (Saturday)
```

### Theme Mods (Theme-Specific Settings)
Stored as serialized array in option: `theme_mods_{$stylesheet}`

```php
get_theme_mod('custom_logo')           // Custom logo attachment ID
get_theme_mod('header_image')          // Header image URL
get_theme_mod('header_textcolor')      // Header text color
get_theme_mod('background_color')      // Background color
get_theme_mod('background_image')      // Background image URL
// + theme-specific mods
```

### Example: Essential Options for Rendering

```php
// Set these in your rendering engine initialization
$options = array(
    'siteurl' => 'http://example.com',
    'home' => 'http://example.com',
    'blogname' => 'My Site',
    'blogdescription' => 'Just another WordPress site',
    'stylesheet' => 'twentyseventeen',
    'template' => 'twentyseventeen',
    'show_on_front' => 'posts',  // or 'page'
    'posts_per_page' => 10,
    'permalink_structure' => '/%postname%/',
    'date_format' => 'F j, Y',
    'time_format' => 'g:i a',
    'timezone_string' => 'America/New_York',
);

// Mock get_option() to return these values
```

---

## Hook System Globals

Understanding the hook system is critical for theme compatibility.

### Hook Arrays
**Defined in:** `wp-includes/plugin.php` lines 27-40

```php
global $wp_filter;          // Array of WP_Hook objects indexed by hook name
global $wp_actions;         // Array tracking how many times each action ran
global $wp_current_filter;  // Stack of currently executing filters/actions
```

### Structure of `$wp_filter`

```php
$wp_filter = array(
    'hook_name' => WP_Hook object,
    'another_hook' => WP_Hook object,
    // ...
);
```

### Structure of `$wp_actions`

```php
$wp_actions = array(
    'init' => 1,           // 'init' action has run once
    'wp_head' => 1,        // 'wp_head' action has run once
    'the_post' => 5,       // 'the_post' action has run 5 times
    // ...
);
```

### Structure of `$wp_current_filter`

```php
// Stack of currently executing filters
$wp_current_filter = array(
    'the_title',           // Currently filtering the_title
    'sanitize_title',      // Which called sanitize_title
    // ...
);
```

### Initialization

```php
if ($wp_filter) {
    $wp_filter = WP_Hook::build_preinitialized_hooks($wp_filter);
} else {
    $wp_filter = array();
}

if (!isset($wp_actions)) {
    $wp_actions = array();
}

if (!isset($wp_current_filter)) {
    $wp_current_filter = array();
}
```

---

## Template Globals

These globals are made available to template files via `load_template()`.

### Extracted from `load_template()`
**File:** `wp-includes/template.php` line 677

```php
global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite,
       $wpdb, $wp_version, $wp, $id, $comment, $user_ID;
```

**Additionally extracted:** All variables from `$wp_query->query_vars`

### Query Variables Available in Templates

After `extract($wp_query->query_vars, EXTR_SKIP)`, templates have access to:

```php
// Common query vars
$p              // Post ID
$page_id        // Page ID
$name           // Post slug
$pagename       // Page slug
$category_name  // Category slug
$tag            // Tag slug
$author_name    // Author username
$s              // Search query
$year           // Year
$monthnum       // Month number
$day            // Day
$paged          // Current page number

// And many more from WP class public/private query vars
```

### Setup by `setup_postdata()`
**File:** `wp-includes/class-wp-query.php` lines 3955-4041

When `the_post()` is called, these globals are set:

```php
global $id;              // Post ID
global $authordata;      // WP_User object for author
global $currentday;      // Post day (format: 'd.m.y')
global $currentmonth;    // Post month (format: 'm')
global $page;            // Current page of paginated post
global $pages;           // Array of post content pages
global $multipage;       // 0 or 1 (whether post has multiple pages)
global $more;            // 0 or 1 (show full content or excerpt)
global $numpages;        // Number of pages in post
```

**Also sets global `$post`:**
```php
global $post;
$post = $wp_query->post;  // Current WP_Post object
```

---

## Minimum Viable Set

**What is the ABSOLUTE MINIMUM to render a simple blog post?**

### 1. Required Globals

```php
// Main query object
global $wp_query;
$wp_query = new WP_Query();

// Backup query object
global $wp_the_query;
$wp_the_query = $wp_query;

// Current post (set by the_post())
global $post;
$post = null; // Will be set when the_post() is called
```

### 2. Required Constants

```php
define('ABSPATH', '/path/to/wordpress/root/');
define('WPINC', 'wp-includes');
define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
define('WP_DEBUG', false);
```

### 3. Populate WP_Query with Single Post

```php
// Create a post object
$post_data = (object) array(
    'ID' => 1,
    'post_author' => '1',
    'post_date' => '2025-12-01 10:00:00',
    'post_date_gmt' => '2025-12-01 15:00:00',
    'post_content' => '<p>This is the post content.</p>',
    'post_title' => 'Hello World',
    'post_excerpt' => 'This is the excerpt.',
    'post_status' => 'publish',
    'comment_status' => 'open',
    'ping_status' => 'open',
    'post_password' => '',
    'post_name' => 'hello-world',
    'to_ping' => '',
    'pinged' => '',
    'post_modified' => '2025-12-01 10:00:00',
    'post_modified_gmt' => '2025-12-01 15:00:00',
    'post_content_filtered' => '',
    'post_parent' => 0,
    'guid' => 'http://example.com/?p=1',
    'menu_order' => 0,
    'post_type' => 'post',
    'post_mime_type' => '',
    'comment_count' => '0',
    'filter' => 'raw',
);

$post_object = new WP_Post($post_data);

// Populate WP_Query
$wp_query->posts = array($post_object);
$wp_query->post_count = 1;
$wp_query->found_posts = 1;
$wp_query->max_num_pages = 1;
$wp_query->current_post = -1;

// Set queried object
$wp_query->queried_object = $post_object;
$wp_query->queried_object_id = 1;

// Set conditional flags for single post
$wp_query->is_single = true;
$wp_query->is_singular = true;

// Set query vars
$wp_query->query_vars = array(
    'p' => 1,
    'post_type' => 'post',
);
```

### 4. Mock Essential Functions

At minimum, mock these:

```php
// Options
function get_option($option, $default = false) {
    $options = array(
        'siteurl' => 'http://example.com',
        'home' => 'http://example.com',
        'blogname' => 'My Site',
        'stylesheet' => 'twentyseventeen',
        'template' => 'twentyseventeen',
    );
    return isset($options[$option]) ? $options[$option] : $default;
}

// Hooks (minimal)
function add_action($tag, $callback, $priority = 10, $accepted_args = 1) {
    // Store if needed
}

function do_action($tag, ...$args) {
    // Execute stored callbacks
}

function apply_filters($tag, $value, ...$args) {
    return $value; // Or execute stored filters
}
```

### 5. Run The Loop

```php
if ($wp_query->have_posts()) {
    while ($wp_query->have_posts()) {
        $wp_query->the_post();

        // At this point:
        // - global $post is set
        // - global $id, $authordata, $pages, etc. are set
        // - You can call the_title(), the_content(), etc.

        echo '<h1>' . get_the_title() . '</h1>';
        echo '<div>' . get_the_content() . '</div>';
    }
}
```

### 6. That's It!

With just:
- `$wp_query` populated with one post
- `$wp_the_query` as backup
- Essential constants defined
- Basic option mocking

You can render a simple blog post through The Loop.

---

## Advanced: Rendering Different Page Types

### Blog Homepage (is_home)

```php
$wp_query->is_home = true;
$wp_query->posts = array($post1, $post2, $post3); // Multiple posts
$wp_query->post_count = 3;
$wp_query->query_vars = array(
    'posts_per_page' => 10,
    'paged' => 1,
);
```

### Single Page (is_page)

```php
$page_post->post_type = 'page';

$wp_query->is_page = true;
$wp_query->is_singular = true;
$wp_query->posts = array($page_post);
$wp_query->queried_object = $page_post;
$wp_query->queried_object_id = $page_post->ID;
```

### Category Archive (is_category)

```php
$category_term = (object) array(
    'term_id' => 5,
    'name' => 'News',
    'slug' => 'news',
    'taxonomy' => 'category',
);

$wp_query->is_category = true;
$wp_query->is_archive = true;
$wp_query->posts = array($post1, $post2);
$wp_query->queried_object = $category_term;
$wp_query->queried_object_id = 5;
$wp_query->query_vars = array(
    'category_name' => 'news',
);
```

### Search Results (is_search)

```php
$wp_query->is_search = true;
$wp_query->posts = array($result1, $result2);
$wp_query->query_vars = array(
    's' => 'search query',
);
```

### 404 Page (is_404)

```php
$wp_query->is_404 = true;
$wp_query->posts = array();
$wp_query->post_count = 0;
```

---

## Implementation Checklist

### Phase 1: Absolute Minimum
- [ ] Create `$wp_query` instance
- [ ] Create `$wp_the_query` backup
- [ ] Populate `WP_Post` objects from Backdrop nodes
- [ ] Set `$wp_query->posts` array
- [ ] Set conditional flags (`is_single`, `is_page`, etc.)
- [ ] Define `ABSPATH`, `WPINC` constants
- [ ] Mock `get_option()` for theme name

### Phase 2: Enhanced Rendering
- [ ] Populate `$authordata` with `WP_User` objects
- [ ] Set template globals (`$id`, `$pages`, `$more`, etc.)
- [ ] Implement hook system (`$wp_filter`, `$wp_actions`)
- [ ] Mock `get_option()` for all theme options
- [ ] Set `$wpdb` with proper table names
- [ ] Define all path constants

### Phase 3: Full Compatibility
- [ ] Populate `$wp_rewrite` with permalink structure
- [ ] Set `$wp` object with query vars
- [ ] Initialize `$wp_roles`, `$wp_locale`
- [ ] Populate widget/sidebar globals
- [ ] Set browser/server detection globals
- [ ] Implement post meta, user meta systems
- [ ] Support taxonomy/term objects for archives

---

## Quick Reference: Most Common Globals by Use Case

### The Loop
- `$wp_query` - Main query object
- `$post` - Current post object
- `$id` - Current post ID

### Conditional Tags
- `$wp_query` - All `is_*()` flags

### Theme Functions
- `get_option('stylesheet')` - Theme directory
- `get_option('template')` - Parent theme directory
- `STYLESHEETPATH` - Full path to theme
- `TEMPLATEPATH` - Full path to parent theme

### Permalinks & URLs
- `$wp_rewrite` - Permalink structure
- `get_option('home')` - Site URL
- `get_option('siteurl')` - WordPress URL

### User/Author Data
- `$authordata` - Current post author (WP_User)
- `$user_ID` - Current logged-in user ID

### Comments
- `$comment` - Current comment object
- `get_option('thread_comments')` - Threading enabled

### Pagination
- `$wp_query->max_num_pages` - Total pages
- `$page` - Current page within post
- `$pages` - Array of post pages
- `$more` - Show full content flag

---

## Notes for Implementation

1. **Start Simple:** Begin with the minimum viable set and add globals as needed based on theme requirements.

2. **Hook System is Critical:** Many themes rely heavily on hooks. Implement at least basic `add_action()`, `do_action()`, `add_filter()`, `apply_filters()`.

3. **Options Matter:** Themes frequently call `get_option()`. Mock it with sensible defaults.

4. **Don't Over-Populate:** Only populate what you need. WordPress itself only populates globals as needed.

5. **Test with Multiple Themes:** Twenty Fourteen through Twenty Seventeen have different requirements.

6. **Watch setup_postdata():** This function is where most template globals get set. Study it carefully.

7. **Conditional Flags are Boolean:** All `is_*` properties on WP_Query must be explicitly set to `true` or `false`.

8. **Dates Use MySQL Format:** Always use 'YYYY-MM-DD HH:MM:SS' format for date fields.

9. **Post Author is String:** `WP_Post::$post_author` should be a string (for backward compatibility), even though it's a numeric ID.

10. **Comment Count is String:** `WP_Post::$comment_count` should also be a string.

---

## References

- WordPress 4.9 Source: `/Users/lukemccormick/.claude-worktrees/WP4BD/admiring-clarke/wordpress-4.9/`
- Key Files Analyzed:
  - `wp-settings.php` - Global initialization
  - `wp-includes/class-wp-query.php` - WP_Query class
  - `wp-includes/class-wp-post.php` - WP_Post class
  - `wp-includes/class-wp-user.php` - WP_User class
  - `wp-includes/query.php` - Query helper functions
  - `wp-includes/template.php` - Template loading
  - `wp-includes/vars.php` - Environment variables
  - `wp-includes/plugin.php` - Hook system
  - `wp-includes/theme.php` - Theme functions
  - `wp-includes/default-constants.php` - Constant definitions

---

**End of Reference Document**
