# Conditional Functions - Usage Guide

## Overview

Implemented WordPress conditional functions for the WP2BD compatibility layer:
- `is_single($post = '')` - Check if viewing a single post (not page)
- `is_singular($post_types = '')` - Check if viewing any single item

## Implementation Details

### File Locations
- **Functions:** `/home/user/wp2bd/implementation/functions/conditionals.php`
- **Tests:** `/home/user/wp2bd/implementation/tests/is_single.test.php`
- **Documentation:** `/home/user/wp2bd/implementation/docs/conditionals-usage.md`

### Test Coverage
- 30 comprehensive unit tests
- All tests passing
- Coverage includes:
  - WordPress-style post objects
  - Backdrop-style node objects
  - Post ID matching
  - Post slug matching
  - Array parameter handling
  - Edge cases (missing types, empty parameters)

## Usage Examples

### is_single()

Check if viewing any single post (excluding pages):

```php
<?php
// In your theme template
if (is_single()) {
    echo '<p>This is a single post view</p>';
}
?>
```

Check for a specific post by ID:

```php
<?php
if (is_single(123)) {
    echo '<p>You are viewing post #123</p>';
}
?>
```

Check for a specific post by slug:

```php
<?php
if (is_single('my-awesome-post')) {
    echo '<p>Reading: My Awesome Post</p>';
}
?>
```

Check for multiple posts:

```php
<?php
if (is_single(array(10, 20, 30))) {
    echo '<p>Featured post!</p>';
}

if (is_single(array('featured-post', 'highlighted-article'))) {
    echo '<p>Special content!</p>';
}
?>
```

### is_singular()

Check if viewing any single item (posts, pages, custom types):

```php
<?php
if (is_singular()) {
    echo '<p>This is a single content item</p>';
}
?>
```

Check for specific post type:

```php
<?php
if (is_singular('post')) {
    echo '<p>Single post view</p>';
}

if (is_singular('page')) {
    echo '<p>Single page view</p>';
}
?>
```

Check for multiple post types:

```php
<?php
if (is_singular(array('post', 'article', 'portfolio'))) {
    echo '<p>Viewing a single content item</p>';
}
?>
```

### Combining Conditionals

```php
<?php
// Check if single post but not a page
if (is_single() && !is_page()) {
    echo '<p>This is a blog post</p>';
}

// Check if any singular view
if (is_singular()) {
    if (is_page()) {
        echo '<p>Page view</p>';
    } elseif (is_single()) {
        echo '<p>Post view</p>';
    } else {
        echo '<p>Other content type view</p>';
    }
}
?>
```

### Theme Template Usage

```php
<?php
// In index.php or other template files
if (is_singular()) {
    // Load single item template
    get_template_part('template-parts/content', 'single');
} else {
    // Load archive/list template
    get_template_part('template-parts/content', 'archive');
}
?>
```

## How It Works

### Backdrop Integration

Both functions work by:

1. **Checking menu_get_object('node')** - Gets the current node from Backdrop's menu system
2. **Falling back to $wp_post** - Uses global `$wp_post` if menu object unavailable
3. **Inspecting node type** - Checks `$node->type` (Backdrop) or `$node->post_type` (WordPress)
4. **Matching criteria** - Validates against provided parameters (ID, slug, array)

### Node Type Detection

```php
// Backdrop-style node
$node = (object) array(
    'nid' => 123,
    'type' => 'post',  // or 'article', 'page', 'portfolio', etc.
    'title' => 'My Post',
);

// WordPress-style post
$post = (object) array(
    'ID' => 123,
    'post_type' => 'post',  // or 'page', 'custom_type', etc.
    'post_title' => 'My Post',
);

// Both formats are supported
```

## Differences Between is_single() and is_singular()

### is_single()
- Returns `true` for posts (type: 'post', 'article', custom types)
- Returns `false` for pages (type: 'page')
- More restrictive
- WordPress semantic: "blog posts" only

### is_singular()
- Returns `true` for ANY single content item
- Includes pages, posts, and all custom post types
- More inclusive
- WordPress semantic: "any single view"

## Examples

```php
<?php
global $wp_post;

// Example 1: Post node
$wp_post = (object) array('nid' => 1, 'type' => 'post');
is_single();    // true
is_singular();  // true

// Example 2: Page node
$wp_post = (object) array('nid' => 2, 'type' => 'page');
is_single();    // false (pages are excluded from is_single)
is_singular();  // true (pages ARE included in is_singular)

// Example 3: Custom post type
$wp_post = (object) array('nid' => 3, 'type' => 'portfolio');
is_single();    // true
is_singular();  // true
is_singular('portfolio');  // true
is_singular('page');       // false
?>
```

## Testing

Run the test suite:

```bash
php /home/user/wp2bd/implementation/tests/is_single.test.php
```

Expected output:
```
=== Running Conditional Functions Tests ===

✓ Test 1 passed: is_single() returns true for post node
✓ Test 2 passed: is_single() returns true for article node
...
✓ Test 30 passed: is_singular() returns true for any node when no type specified

=== All 30 Tests Passed! ===
```

## Notes

- Both functions integrate seamlessly with Backdrop's menu system
- Compatible with existing WordPress themes
- Handles both Backdrop nodes and WordPress posts
- Proper fallback behavior when no content is available
- Supports parameter variations (ID, slug, array)
- Helper functions are private (prefixed with `_wp2bd_`)

## Related Functions (Bonus)

The implementation also includes several additional conditional functions:

- `is_page($page)` - Check if viewing a page
- `is_archive()` - Check if viewing any archive
- `is_search()` - Check if viewing search results
- `is_front_page()` - Check if viewing front page
- `is_home()` - Check if viewing blog index

These were automatically added and follow the same patterns as `is_single()` and `is_singular()`.
