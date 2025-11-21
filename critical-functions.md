# CRITICAL FUNCTIONS - Must Implement for Basic Rendering

Functions that are absolutely required for the Twenty Seventeen theme to render a basic page with content.

---

## CATEGORY 1: THE LOOP & QUERY SYSTEM ⭐ HIGHEST PRIORITY

These functions form the core of WordPress content display and MUST work for any page to render.

| Function | Usage Count | Implementation Complexity | Test Priority |
|----------|-------------|--------------------------|---------------|
| `have_posts()` | 30+ | **MODERATE** - State machine | P0 |
| `the_post()` | 20+ | **MODERATE** - Sets up globals | P0 |
| `wp_reset_postdata()` | 3+ | **SIMPLE** - Resets globals | P0 |

**Dependencies:**
- Global `$post` object
- Global `$wp_query` object (or Backdrop equivalent)
- Node data from Backdrop

**Implementation Notes:**
- These three functions are TIGHTLY COUPLED - must be implemented together
- Form a state machine that iterates through posts
- `have_posts()` checks if posts remain in query
- `the_post()` sets up the next post and populates global `$post`
- `wp_reset_postdata()` restores original query after custom loops

**Suggested Approach:**
- Create a `WP_Query` mock class that wraps Backdrop's node queries
- Implement post counter and current post tracking
- Map Backdrop node object to WP `$post` object structure

---

## CATEGORY 2: TEMPLATE LOADING FUNCTIONS ⭐ HIGHEST PRIORITY

| Function | Usage Count | Implementation Complexity | Test Priority |
|----------|-------------|--------------------------|---------------|
| `get_header()` | 18 | **SIMPLE** - Include file | P0 |
| `get_footer()` | 18 | **SIMPLE** - Include file | P0 |
| `get_sidebar()` | 8+ | **SIMPLE** - Include file | P0 |
| `get_template_part()` | 30+ | **MODERATE** - File resolution | P0 |

**Implementation Notes:**
- `get_header()` / `get_footer()` / `get_sidebar()` - Simple file includes
- `get_template_part( $slug, $name )` - Must resolve file path like: `{$slug}-{$name}.php` or `{$slug}.php`
- Must check for existence before including
- Should fire hooks (`get_header`, `get_footer`, `get_sidebar` actions)

**File Paths (for Twenty Seventeen):**
- `get_header()` → `header.php`
- `get_footer()` → `footer.php`
- `get_sidebar()` → `sidebar.php`
- `get_template_part('template-parts/post/content', 'excerpt')` → `template-parts/post/content-excerpt.php`

---

## CATEGORY 3: CONTENT DISPLAY FUNCTIONS ⭐ HIGHEST PRIORITY

Functions that actually output post/node data to the page.

| Function | Usage Count | Implementation Complexity | Test Priority |
|----------|-------------|--------------------------|---------------|
| `the_title()` | 30+ | **SIMPLE** - Echo title | P0 |
| `get_the_title()` | 20+ | **SIMPLE** - Return title | P0 |
| `the_content()` | 30+ | **MODERATE** - Process shortcodes | P0 |
| `the_excerpt()` | 1+ | **SIMPLE** - Display excerpt | P1 |
| `the_permalink()` | 10+ | **SIMPLE** - Echo URL | P0 |
| `get_permalink()` | 20+ | **SIMPLE** - Return URL | P0 |
| `the_ID()` | 18+ | **TRIVIAL** - Echo ID | P0 |
| `get_the_ID()` | 20+ | **TRIVIAL** - Return ID | P0 |
| `the_post_thumbnail()` | 15+ | **MODERATE** - Image HTML | P1 |
| `get_the_post_thumbnail()` | 15+ | **MODERATE** - Return image HTML | P1 |
| `has_post_thumbnail()` | 15+ | **SIMPLE** - Check for image | P1 |

**Implementation Notes:**
- All `the_*()` functions echo output
- All `get_the_*()` functions return values
- `the_title( $before, $after )` - Wraps title in HTML
- `the_content()` - Must process content filters and "more" links
- Thumbnail functions map to Backdrop's image field system

**Backdrop Mappings:**
- `$post->post_title` → `$node->title`
- `$post->post_content` → `$node->body[LANGUAGE_NONE][0]['value']`
- `$post->ID` → `$node->nid`
- Permalink → `url('node/' . $node->nid)`

---

## CATEGORY 4: CONDITIONAL TAGS (Core Navigation)

Functions that determine what type of page is being displayed. Critical for template logic.

| Function | Usage Count | Implementation Complexity | Test Priority |
|----------|-------------|--------------------------|---------------|
| `is_single()` | 30+ | **SIMPLE** - Check node type | P0 |
| `is_page()` | 10+ | **SIMPLE** - Check node type | P0 |
| `is_home()` | 20+ | **MODERATE** - Check front page setting | P0 |
| `is_front_page()` | 15+ | **MODERATE** - Check front page setting | P0 |
| `is_archive()` | 5+ | **SIMPLE** - Check if listing | P1 |
| `is_search()` | 2+ | **SIMPLE** - Check if search | P2 |
| `is_404()` | Implicit | **SIMPLE** - Check 404 status | P2 |
| `is_sticky()` | 4+ | **SIMPLE** - Check sticky flag | P2 |
| `is_singular()` | 2+ | **SIMPLE** - Check if single item | P1 |

**Implementation Notes:**
- Most conditionals check Backdrop's `$node->type` or path
- `is_single()` → Backdrop node type 'post' or 'article'
- `is_page()` → Backdrop node type 'page'
- `is_home()` / `is_front_page()` → Check Backdrop site config
- `is_archive()` → Check if viewing a taxonomy term or date listing

---

## CATEGORY 5: HTML ATTRIBUTE FUNCTIONS

Functions that output dynamic CSS classes and IDs.

| Function | Usage Count | Implementation Complexity | Test Priority |
|----------|-------------|--------------------------|---------------|
| `post_class()` | 18+ | **MODERATE** - Generate classes | P1 |
| `body_class()` | 1 | **MODERATE** - Generate classes | P1 |

**Implementation Notes:**
- `body_class()` - Outputs classes like `home`, `single`, `page-id-5`, `logged-in`, etc.
- `post_class()` - Outputs classes like `post-123`, `type-post`, `status-publish`, `sticky`, etc.
- Both accept additional classes as parameters
- Both fire filters allowing modification

**Typical Output:**
```html
<body class="home blog logged-in admin-bar">
<article class="post-123 post type-post status-publish format-standard hentry category-news">
```

---

## CATEGORY 6: CORE WORDPRESS HOOKS ⭐ HIGHEST PRIORITY

Critical action hooks that plugins and themes rely on.

| Function | Usage Count | Implementation Complexity | Test Priority |
|----------|-------------|--------------------------|---------------|
| `wp_head()` | 1 | **CRITICAL** - Fires in <head> | P0 |
| `wp_footer()` | 1 | **CRITICAL** - Fires before </body> | P0 |
| `add_action()` | 20+ | **MODERATE** - Hook registration | P0 |
| `do_action()` | Implicit | **MODERATE** - Fire hooks | P0 |
| `add_filter()` | 15+ | **MODERATE** - Filter registration | P0 |
| `apply_filters()` | 10+ | **MODERATE** - Apply filters | P0 |

**Implementation Notes:**
- Backdrop has `hook_*()` system, but WordPress uses action/filter pattern
- `wp_head()` - Must fire the 'wp_head' action (enqueues CSS/JS, adds meta tags)
- `wp_footer()` - Must fire the 'wp_footer' action (enqueues footer JS)
- Hook system is FUNDAMENTAL - many functions depend on it

**Conversion Strategy:**
- Create simple hook registry (array of callbacks)
- `add_action($hook, $callback, $priority)` → Add to registry
- `do_action($hook, ...$args)` → Execute all callbacks for that hook
- Similar for filters, but callbacks return modified values

---

## CATEGORY 7: ESCAPING & SANITIZATION FUNCTIONS ⭐ SECURITY CRITICAL

Functions that prevent XSS and other security vulnerabilities.

| Function | Usage Count | Implementation Complexity | Test Priority |
|----------|-------------|--------------------------|---------------|
| `esc_html()` | 5+ | **TRIVIAL** - htmlspecialchars | P0 |
| `esc_attr()` | 15+ | **TRIVIAL** - HTML attribute escape | P0 |
| `esc_url()` | 30+ | **SIMPLE** - URL sanitization | P0 |
| `esc_url_raw()` | 1 | **SIMPLE** - URL for DB | P1 |

**Implementation Notes:**
- **DO NOT skip these** - Critical for security
- `esc_html()` → `htmlspecialchars($text, ENT_QUOTES, 'UTF-8')`
- `esc_attr()` → Similar to esc_html but for HTML attributes
- `esc_url()` → Validate and clean URLs for display
- Backdrop has `check_plain()`, `check_url()` equivalents

---

## CATEGORY 8: POST METADATA FUNCTIONS

Functions that retrieve post-related data.

| Function | Usage Count | Implementation Complexity | Test Priority |
|----------|-------------|--------------------------|---------------|
| `get_post_type()` | 10+ | **TRIVIAL** - Return type | P0 |
| `get_post_format()` | 10+ | **SIMPLE** - Return format | P1 |
| `get_the_date()` | 2+ | **SIMPLE** - Format date | P1 |
| `get_the_time()` | 1+ | **SIMPLE** - Format time | P1 |
| `get_the_author()` | 1+ | **SIMPLE** - Return author name | P1 |
| `get_the_author_meta()` | 1+ | **SIMPLE** - Return author field | P1 |

**Implementation Notes:**
- `get_post_type()` → `$node->type`
- `get_post_format()` → Taxonomy term or default 'standard'
- Date/time functions use PHP's `date()` with node's created timestamp
- Author functions map to `$node->uid` and user data

---

## CATEGORY 9: PAGINATION & NAVIGATION

Functions that create navigation links between pages/posts.

| Function | Usage Count | Implementation Complexity | Test Priority |
|----------|-------------|--------------------------|---------------|
| `the_posts_pagination()` | 5+ | **COMPLEX** - Generate pagination | P1 |
| `wp_link_pages()` | 10+ | **MODERATE** - Paginate content | P1 |
| `the_post_navigation()` | 1 | **MODERATE** - Prev/next links | P2 |

**Implementation Notes:**
- `the_posts_pagination()` - Creates "Page 1 2 3" links for post archives
- `wp_link_pages()` - Creates "Page 1 2 3" links for multi-page posts
- `the_post_navigation()` - Creates "Previous Post / Next Post" links
- All accept args arrays for customization

---

## CATEGORY 10: UTILITY FUNCTIONS

Commonly used utility functions that are simple but essential.

| Function | Usage Count | Implementation Complexity | Test Priority |
|----------|-------------|--------------------------|---------------|
| `home_url()` | 5+ | **TRIVIAL** - Site URL | P0 |
| `bloginfo()` | 5+ | **SIMPLE** - Site settings | P0 |
| `get_bloginfo()` | 2+ | **SIMPLE** - Get site settings | P0 |
| `language_attributes()` | 1 | **SIMPLE** - HTML lang attr | P1 |
| `sprintf()` | 20+ | **N/A** - PHP core function | N/A |
| `printf()` | 10+ | **N/A** - PHP core function | N/A |

**Implementation Notes:**
- `home_url('/')` → Backdrop's `url('', array('absolute' => TRUE))`
- `bloginfo('name')` → Site name from config
- `bloginfo('charset')` → 'UTF-8' (hardcoded or from config)
- `language_attributes()` → `lang="en" dir="ltr"` (from site config)

---

## SUMMARY: CRITICAL FUNCTIONS BY PRIORITY

### P0 - MUST HAVE FOR BASIC PAGE RENDER (Implement First)
1. The Loop: `have_posts()`, `the_post()`, `wp_reset_postdata()`
2. Template Loading: `get_header()`, `get_footer()`, `get_template_part()`
3. Content Display: `the_title()`, `get_the_title()`, `the_content()`, `the_permalink()`, `get_permalink()`, `the_ID()`, `get_the_ID()`
4. Core Hooks: `wp_head()`, `wp_footer()`, `add_action()`, `do_action()`, `add_filter()`, `apply_filters()`
5. Conditionals: `is_single()`, `is_page()`, `is_home()`, `is_front_page()`
6. Escaping: `esc_html()`, `esc_attr()`, `esc_url()`
7. Utility: `home_url()`, `bloginfo()`, `get_post_type()`

### P1 - NEEDED FOR FULL THEME FUNCTIONALITY (Implement Second)
1. Images: `the_post_thumbnail()`, `has_post_thumbnail()`, `get_the_post_thumbnail()`
2. HTML Attributes: `post_class()`, `body_class()`
3. Metadata: `get_post_format()`, `get_the_date()`, `get_the_author()`
4. Pagination: `the_posts_pagination()`, `wp_link_pages()`
5. Archives: `is_archive()`, `is_singular()`

### P2 - NICE TO HAVE (Implement Third or Stub)
1. Special conditionals: `is_search()`, `is_sticky()`
2. Navigation: `the_post_navigation()`
3. Search: `get_search_form()`, `get_search_query()`

---

## TESTING STRATEGY

### Unit Tests (BackdropUnitTestCase)
- Each function should have 3-5 unit tests covering:
  - Basic functionality
  - Edge cases (empty data, missing fields)
  - Filter/hook integration

### Integration Tests (BackdropWebTestCase)
- Create sample node content
- Verify functions output correct HTML
- Test The Loop with multiple posts
- Verify template loading works
- Test pagination with 20+ nodes

### Example Test Structure:
```php
class WPLoopTest extends BackdropUnitTestCase {
  function testHavePostsWithContent() {
    // Create 3 test nodes
    // Call have_posts()
    // Assert returns TRUE
  }

  function testHavePostsEmpty() {
    // No nodes exist
    // Call have_posts()
    // Assert returns FALSE
  }

  function testThePostSetsGlobals() {
    // Create 1 test node
    // Call the_post()
    // Assert global $post is populated
    // Assert $post->post_title matches node title
  }
}
```

---

## BACKDROP DEPENDENCIES NEEDED

To implement these functions, we'll need:

1. **Node Loading:** `node_load()`, `node_load_multiple()`
2. **Entity Queries:** Backdrop's EntityFieldQuery or Database API
3. **URL Generation:** `url()` function
4. **Configuration:** `config_get()` for site settings
5. **User System:** `user_load()` for author data
6. **Image System:** `image_style_url()` for thumbnails
7. **Taxonomy:** `taxonomy_term_load()` for categories/tags

---

**TOTAL CRITICAL FUNCTIONS:** ~50 unique functions
**ESTIMATED IMPLEMENTATION TIME:**
- P0 functions: 40 hours (with tests)
- P1 functions: 30 hours (with tests)
- P2 functions: 10 hours (with tests)
