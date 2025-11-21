# NICE-TO-HAVE FUNCTIONS - Can Be Stubbed Initially

Functions that enhance the theme but aren't required for basic page rendering. These can be stubbed with logging to allow the theme to load, then implemented incrementally.

---

## CATEGORY 1: TRANSLATION/I18N FUNCTIONS

WordPress internationalization functions. Can stub to return English text initially.

| Function | Usage Count | Stub Complexity | Notes |
|----------|-------------|-----------------|-------|
| `__()` | 50+ | **TRIVIAL** | Return first parameter |
| `_e()` | 20+ | **TRIVIAL** | Echo first parameter |
| `_x()` | 10+ | **TRIVIAL** | Return first parameter (ignore context) |
| `_nx()` | 1 | **TRIVIAL** | Return singular/plural based on count |
| `esc_attr_e()` | 5+ | **SIMPLE** | Echo escaped first parameter |
| `esc_attr_x()` | 1 | **SIMPLE** | Return escaped first parameter |
| `load_theme_textdomain()` | 1 | **TRIVIAL** | No-op (log call) |

**Stub Implementation:**
```php
function __($text, $domain = 'default') {
  // TODO: Implement translation
  watchdog('wp2bd', 'Translation requested: @text', array('@text' => $text));
  return $text;
}

function _e($text, $domain = 'default') {
  echo __($text, $domain);
}
```

**Future Implementation:**
- Use Backdrop's `t()` function
- Map text domain to Backdrop translation system
- Or keep stubs if English-only site

---

## CATEGORY 2: COMMENTS SYSTEM

Comment functionality. Can stub initially if comments aren't needed for PoC.

| Function | Usage Count | Stub Complexity | Priority |
|----------|-------------|-----------------|----------|
| `comments_open()` | 3+ | **TRIVIAL** | P3 |
| `get_comments_number()` | 5+ | **TRIVIAL** | P3 |
| `comments_template()` | 2+ | **SIMPLE** | P3 |
| `comment_form()` | 1 | **SIMPLE** | P3 |
| `wp_list_comments()` | 1 | **MODERATE** | P4 |
| `the_comments_pagination()` | 1 | **MODERATE** | P4 |
| `have_comments()` | 1 | **TRIVIAL** | P3 |

**Stub Implementation:**
```php
function comments_open() {
  // TODO: Check if comments enabled
  watchdog('wp2bd', 'comments_open() called');
  return FALSE; // Disable comments for now
}

function get_comments_number() {
  watchdog('wp2bd', 'get_comments_number() called');
  return 0;
}

function comments_template() {
  watchdog('wp2bd', 'comments_template() called - comments disabled');
  // Display: "Comments are disabled"
}
```

**Future Implementation:**
- Map to Backdrop's comment system
- `comments_open()` → Check `$node->comment` status
- `get_comments_number()` → Query comment table
- Display Backdrop comment form/list

---

## CATEGORY 3: NAVIGATION MENUS

Menu system. Can stub initially - sidebar nav less critical than content display.

| Function | Usage Count | Stub Complexity | Priority |
|----------|-------------|-----------------|----------|
| `register_nav_menus()` | 1 | **SIMPLE** | P3 |
| `has_nav_menu()` | 5+ | **TRIVIAL** | P3 |
| `wp_nav_menu()` | 2+ | **MODERATE** | P3 |

**Stub Implementation:**
```php
function has_nav_menu($location) {
  watchdog('wp2bd', 'has_nav_menu(@loc) called', array('@loc' => $location));
  return FALSE; // No menus initially
}

function wp_nav_menu($args = array()) {
  watchdog('wp2bd', 'wp_nav_menu() called');
  // Display simple fallback: "Menu: {location}"
  echo '<div class="menu-stub">Navigation menu placeholder</div>';
}
```

**Future Implementation:**
- Map to Backdrop's menu system
- `register_nav_menus()` → Register menu locations in config
- `wp_nav_menu()` → Render Backdrop menu with `menu_tree()`

---

## CATEGORY 4: WIDGETS & SIDEBARS

Widget areas. Can stub to show "No widgets" message.

| Function | Usage Count | Stub Complexity | Priority |
|----------|-------------|-----------------|----------|
| `register_sidebar()` | 3 | **SIMPLE** | P3 |
| `is_active_sidebar()` | 8+ | **TRIVIAL** | P3 |
| `dynamic_sidebar()` | 3+ | **SIMPLE** | P3 |

**Stub Implementation:**
```php
function is_active_sidebar($index) {
  watchdog('wp2bd', 'is_active_sidebar(@id) called', array('@id' => $index));
  return FALSE; // No widgets initially
}

function dynamic_sidebar($index) {
  watchdog('wp2bd', 'dynamic_sidebar(@id) called', array('@id' => $index));
  // Display placeholder
  echo '<aside class="widget-stub">Sidebar placeholder</aside>';
}
```

**Future Implementation:**
- Map to Backdrop's block system
- `register_sidebar()` → Define block regions
- `is_active_sidebar()` → Check if region has blocks
- `dynamic_sidebar()` → Render blocks in region

---

## CATEGORY 5: THEME CUSTOMIZER

WordPress Customizer API. Not essential for PoC - can stub entirely.

| Function | Usage Count | Stub Complexity | Priority |
|----------|-------------|-----------------|----------|
| `get_theme_mod()` | 10+ | **SIMPLE** | P3 |
| `is_customize_preview()` | 8+ | **TRIVIAL** | P4 |
| `$wp_customize->*` methods | Many | **COMPLEX** | P4 |

**Stub Implementation:**
```php
function get_theme_mod($name, $default = false) {
  watchdog('wp2bd', 'get_theme_mod(@name) called', array('@name' => $name));
  // Return defaults for known mods
  $defaults = array(
    'colorscheme' => 'light',
    'colorscheme_hue' => 250,
    'page_layout' => 'two-column',
  );
  return isset($defaults[$name]) ? $defaults[$name] : $default;
}

function is_customize_preview() {
  return FALSE; // Never in customizer
}
```

**Future Implementation:**
- Map to Backdrop's configuration system
- `get_theme_mod()` → `config_get('twentyseventeen.settings', $name)`
- Create Backdrop admin form for theme settings

---

## CATEGORY 6: CUSTOM HEADER & LOGO

Custom header features. Can stub - header image nice but not essential.

| Function | Usage Count | Stub Complexity | Priority |
|----------|-------------|-----------------|----------|
| `the_custom_header_markup()` | 1 | **SIMPLE** | P3 |
| `has_custom_header()` | 1 | **TRIVIAL** | P4 |
| `get_header_textcolor()` | 2 | **SIMPLE** | P4 |
| `the_custom_logo()` | 1 | **SIMPLE** | P3 |
| `has_header_image()` | 1 | **TRIVIAL** | P4 |

**Stub Implementation:**
```php
function has_custom_header() {
  return FALSE;
}

function the_custom_header_markup() {
  watchdog('wp2bd', 'the_custom_header_markup() called - no header set');
  // Output nothing or placeholder
}

function the_custom_logo() {
  watchdog('wp2bd', 'the_custom_logo() called - no logo set');
  // Could display site name as text logo
}
```

**Future Implementation:**
- Map to Backdrop's image upload fields in theme settings
- Store header image/logo in configuration
- Generate markup matching WordPress structure

---

## CATEGORY 7: ENQUEUE SYSTEM

Script/style enqueueing. Can simplify initially - just include files directly.

| Function | Usage Count | Stub Complexity | Priority |
|----------|-------------|-----------------|----------|
| `wp_enqueue_script()` | 10+ | **MODERATE** | P2 |
| `wp_enqueue_style()` | 5+ | **MODERATE** | P2 |
| `wp_script_add_data()` | 1 | **SIMPLE** | P4 |
| `wp_style_add_data()` | 2 | **SIMPLE** | P4 |
| `wp_localize_script()` | 1 | **MODERATE** | P3 |
| `wp_style_is()` | 1 | **TRIVIAL** | P4 |

**Stub Implementation (Simple):**
```php
function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all') {
  watchdog('wp2bd', 'Enqueue style: @handle', array('@handle' => $handle));
  // Collect in global array for wp_head() to output
  global $wp_styles;
  $wp_styles[$handle] = array('src' => $src, 'media' => $media);
}

function wp_head() {
  do_action('wp_head');
  // Output collected styles
  global $wp_styles;
  foreach ($wp_styles as $handle => $style) {
    echo '<link rel="stylesheet" href="' . esc_url($style['src']) . '" media="' . esc_attr($style['media']) . '">' . "\n";
  }
}
```

**Future Implementation:**
- Full dependency resolution
- Conditional enqueueing (footer vs header)
- Script localization support

---

## CATEGORY 8: SEARCH FUNCTIONS

Search functionality. Can stub if search not needed for initial PoC.

| Function | Usage Count | Stub Complexity | Priority |
|----------|-------------|-----------------|----------|
| `get_search_form()` | 3+ | **SIMPLE** | P3 |
| `get_search_query()` | 2+ | **TRIVIAL** | P3 |
| `is_search()` | 2+ | **TRIVIAL** | P3 |

**Stub Implementation:**
```php
function is_search() {
  // Check if current path is search page
  return (arg(0) == 'search');
}

function get_search_query() {
  return isset($_GET['s']) ? check_plain($_GET['s']) : '';
}

function get_search_form() {
  echo '<form role="search" method="get" action="' . url('search') . '">';
  echo '<input type="search" name="s" value="' . esc_attr(get_search_query()) . '">';
  echo '<button type="submit">Search</button>';
  echo '</form>';
}
```

**Future Implementation:**
- Integrate with Backdrop's search module
- Support advanced search features

---

## CATEGORY 9: ADVANCED POST DATA

Post metadata that's nice to have but not essential for basic display.

| Function | Usage Count | Stub Complexity | Priority |
|----------|-------------|-----------------|----------|
| `get_the_category_list()` | 1 | **MODERATE** | P2 |
| `get_the_tag_list()` | 1 | **MODERATE** | P2 |
| `get_the_modified_date()` | 2 | **SIMPLE** | P3 |
| `get_the_modified_time()` | 1 | **SIMPLE** | P3 |
| `single_post_title()` | 1 | **SIMPLE** | P3 |
| `the_archive_title()` | 1 | **MODERATE** | P2 |
| `the_archive_description()` | 1 | **MODERATE** | P3 |

**Stub Implementation:**
```php
function get_the_category_list($separator = '') {
  watchdog('wp2bd', 'get_the_category_list() called');
  // Return empty or "Uncategorized"
  return '<a href="#">Uncategorized</a>';
}

function get_the_modified_date($format = '') {
  global $post;
  if (!$format) $format = get_option('date_format');
  return date($format, $post->post_modified_timestamp);
}
```

**Future Implementation:**
- Map to Backdrop taxonomy system
- Query terms attached to node
- Generate proper category/tag links

---

## CATEGORY 10: IMAGE & MEDIA FUNCTIONS

Advanced image handling. Basic thumbnails covered in Critical, these are enhancements.

| Function | Usage Count | Stub Complexity | Priority |
|----------|-------------|-----------------|----------|
| `add_image_size()` | 2 | **SIMPLE** | P3 |
| `get_post_thumbnail_id()` | 2+ | **SIMPLE** | P2 |
| `wp_get_attachment_image_src()` | 2+ | **MODERATE** | P2 |
| `get_media_embedded_in_content()` | 2 | **COMPLEX** | P4 |
| `get_post_gallery()` | 4 | **MODERATE** | P3 |

**Stub Implementation:**
```php
function get_post_thumbnail_id($post_id = null) {
  global $post;
  $node = $post_id ? node_load($post_id) : node_load($post->ID);
  // Return field image FID if exists
  if (isset($node->field_image[LANGUAGE_NONE][0]['fid'])) {
    return $node->field_image[LANGUAGE_NONE][0]['fid'];
  }
  return 0;
}

function get_media_embedded_in_content($content, $types = array('audio', 'video')) {
  watchdog('wp2bd', 'get_media_embedded_in_content() called - stub returning empty');
  return array(); // No embedded media initially
}
```

**Future Implementation:**
- Integrate with Backdrop's image and media modules
- Support responsive images
- Extract embedded media from content

---

## CATEGORY 11: ADVANCED CONDITIONALS

Conditionals that aren't essential for basic rendering.

| Function | Usage Count | Stub Complexity | Priority |
|----------|-------------|-----------------|----------|
| `is_multi_author()` | 1 | **SIMPLE** | P4 |
| `is_sticky()` | 4+ | **SIMPLE** | P3 |
| `is_active_sidebar()` | 8+ | **TRIVIAL** | P3 |
| `post_password_required()` | 1 | **SIMPLE** | P4 |
| `current_user_can()` | 1 | **MODERATE** | P3 |
| `post_type_supports()` | 1 | **SIMPLE** | P3 |

**Stub Implementation:**
```php
function is_sticky() {
  global $post;
  // Check if post has 'sticky' flag
  // Could map to Backdrop's 'promote to front page'
  watchdog('wp2bd', 'is_sticky() called');
  return FALSE; // No sticky posts initially
}

function current_user_can($capability) {
  global $user;
  // Map WP capabilities to Backdrop permissions
  $map = array(
    'publish_posts' => 'create article content',
    'edit_post' => 'edit own article content',
  );
  $permission = isset($map[$capability]) ? $map[$capability] : $capability;
  return user_access($permission);
}
```

---

## CATEGORY 12: TRANSIENT/CACHING

WordPress transient API. Can stub with Backdrop's cache system.

| Function | Usage Count | Stub Complexity | Priority |
|----------|-------------|-----------------|----------|
| `get_transient()` | 1 | **SIMPLE** | P3 |
| `set_transient()` | 1 | **SIMPLE** | P3 |
| `delete_transient()` | 1 | **SIMPLE** | P3 |

**Stub Implementation:**
```php
function get_transient($key) {
  return cache_get('wp_transient_' . $key, 'cache');
}

function set_transient($key, $value, $expiration = 0) {
  cache_set('wp_transient_' . $key, $value, 'cache', time() + $expiration);
}

function delete_transient($key) {
  cache_clear_all('wp_transient_' . $key, 'cache');
}
```

---

## CATEGORY 13: EDITOR & ADMIN FUNCTIONS

Functions primarily for admin area. Low priority for front-end rendering.

| Function | Usage Count | Stub Complexity | Priority |
|----------|-------------|-----------------|----------|
| `add_editor_style()` | 1 | **TRIVIAL** | P5 |
| `get_edit_post_link()` | 2+ | **SIMPLE** | P4 |
| `edit_post_link()` | 1 | **SIMPLE** | P4 |
| `admin_url()` | 1 | **SIMPLE** | P4 |

**Stub Implementation:**
```php
function get_edit_post_link($post_id = 0) {
  global $user, $post;
  $nid = $post_id ? $post_id : $post->ID;
  if (!user_access('edit own article content')) {
    return FALSE;
  }
  return url('node/' . $nid . '/edit');
}

function edit_post_link($text = 'Edit', $before = '', $after = '') {
  $link = get_edit_post_link();
  if ($link) {
    echo $before . '<a href="' . esc_url($link) . '">' . $text . '</a>' . $after;
  }
}
```

---

## CATEGORY 14: MISCELLANEOUS UTILITY

Various helper functions that can be stubbed.

| Function | Usage Count | Stub Complexity | Priority |
|----------|-------------|-----------------|----------|
| `get_template_directory()` | 1 | **TRIVIAL** | P2 |
| `get_parent_theme_file_path()` | 10+ | **SIMPLE** | P2 |
| `get_theme_file_uri()` | 10+ | **SIMPLE** | P2 |
| `get_stylesheet_uri()` | 1 | **SIMPLE** | P2 |
| `absint()` | 5+ | **TRIVIAL** | P2 |
| `pings_open()` | 1 | **TRIVIAL** | P5 |
| `number_format_i18n()` | 1 | **SIMPLE** | P4 |

**Stub Implementation:**
```php
function get_template_directory() {
  return BACKDROP_ROOT . '/themes/twentyseventeen';
}

function get_theme_file_uri($file) {
  global $base_url;
  return $base_url . '/themes/twentyseventeen/' . ltrim($file, '/');
}

function absint($value) {
  return abs((int) $value);
}
```

---

## STUB GENERATION TEMPLATE

For each stubbed function, use this template:

```php
/**
 * WordPress compatibility: {function_name}
 *
 * STATUS: STUB - Returns safe default, logs call
 * TODO: Implement full functionality
 *
 * @see https://developer.wordpress.org/reference/functions/{function_name}/
 */
function {function_name}({$params}) {
  // Log the call for debugging
  watchdog('wp2bd_stub', '{function_name}() called', array(), WATCHDOG_DEBUG);

  // Return safe default
  return {$safe_default};
}
```

---

## SUMMARY: STUB PRIORITY

### Can Stub Immediately (Won't Break Basic Rendering)
- All translation functions (just return English)
- Comments system (return "comments disabled")
- Navigation menus (return empty/placeholder)
- Widgets/sidebars (return empty)
- Customizer API (return defaults)
- Custom header/logo (return nothing)
- Search (show basic form)
- Advanced post metadata (return placeholders)
- Admin/editor functions (for logged-out users)

### Should Implement Soon After P0
- Script/style enqueuing (P2) - Needed for proper theme display
- Basic taxonomy (categories/tags) (P2) - Common theme feature
- Image size registration (P3) - Needed for responsive images

### Can Defer to Later Phases
- Complex Customizer integration
- Embedded media extraction
- Advanced caching
- Multi-author features
- Pingbacks/trackbacks

---

**TOTAL NICE-TO-HAVE FUNCTIONS:** ~70 unique functions
**ESTIMATED STUB TIME:** 10 hours (create logging stubs for all)
**ESTIMATED FULL IMPLEMENTATION:** 60-80 hours (progressive enhancement)
