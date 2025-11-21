# get_template_part() - Usage Examples

## Overview

The `get_template_part()` function loads reusable template parts in WordPress themes. This WP2BD implementation provides full WordPress compatibility for Backdrop CMS.

## Basic Usage

### Example 1: Simple Template Part

```php
// Load: content.php
get_template_part('content');
```

This will look for and include `content.php` from the theme directory.

### Example 2: Specialized Template Part

```php
// Try: content-excerpt.php, then content.php
get_template_part('content', 'excerpt');
```

This will first try to load `content-excerpt.php`. If not found, it falls back to `content.php`.

### Example 3: Nested Template Parts

```php
// Try: template-parts/post/content-single.php
//      template-parts/post/content.php
get_template_part('template-parts/post/content', 'single');
```

This supports nested directory structures, commonly used in modern WordPress themes.

## Real-World Examples from Twenty Seventeen Theme

### Example 4: Post Format Template

```php
// Load different templates based on post format
get_template_part('template-parts/post/content', get_post_format());
```

If `get_post_format()` returns 'video', this will try:
1. `template-parts/post/content-video.php`
2. `template-parts/post/content.php` (fallback)

### Example 5: Conditional Template Loading

```php
if (is_search()) {
    get_template_part('template-parts/post/content', 'search');
} else {
    get_template_part('template-parts/post/content', get_post_format());
}
```

### Example 6: Archive Templates

```php
// In archive.php
while (have_posts()) {
    the_post();

    // Load excerpt version for archives
    get_template_part('template-parts/post/content', 'excerpt');
}
```

## How It Works

### Template Resolution Order

When you call `get_template_part('content', 'excerpt')`:

1. **Check for specialized template**: `content-excerpt.php`
2. **Fallback to generic**: `content.php`
3. **Return false if neither found**

### Theme Hierarchy

The function checks templates in this order:

1. **Child Theme** (if using a child theme)
2. **Parent Theme**

This allows child themes to override parent theme templates.

## Action Hook

Before loading any template, the function fires an action hook:

```php
do_action("get_template_part_{$slug}", $slug, $name);
```

### Example: Hooking into Template Loading

```php
// Add custom functionality before loading content templates
add_action('get_template_part_content', function($slug, $name) {
    if ($name === 'single') {
        // Do something before single post content loads
        echo '<div class="single-post-wrapper">';
    }
}, 10, 2);
```

## Return Value

- **Returns `true`** if a template was found and loaded
- **Returns `false`** if no template was found

```php
$loaded = get_template_part('content', 'missing');
if (!$loaded) {
    // Handle case where template doesn't exist
    get_template_part('content'); // Try generic fallback
}
```

## Special Cases

### Empty or Null Name

```php
// These are equivalent:
get_template_part('content', null);
get_template_part('content', '');
get_template_part('content');

// All try to load: content.php
```

### Numeric Names

```php
// Load 404 content template
get_template_part('content', 404);
// Tries: content-404.php, then content.php
```

### Multiple Inclusions

Unlike `get_header()` or `get_footer()`, template parts use `require` (not `require_once`), so they can be included multiple times:

```php
// This works - loads the same template 3 times
get_template_part('loop-item');
get_template_part('loop-item');
get_template_part('loop-item');
```

## Common Patterns

### Pattern 1: Template Part per Post Type

```php
// Load different templates for different post types
$post_type = get_post_type();
get_template_part('template-parts/content', $post_type);
```

### Pattern 2: Conditional Layout

```php
// Different layouts for different contexts
if (is_singular()) {
    get_template_part('template-parts/content', 'single');
} else {
    get_template_part('template-parts/content', 'list');
}
```

### Pattern 3: Component-Based Templates

```php
// Build page from reusable components
get_template_part('components/hero');
get_template_part('components/features');
get_template_part('components/testimonials');
get_template_part('components/cta');
```

## Best Practices

1. **Use consistent naming**: Follow WordPress conventions (`content-{type}.php`)
2. **Organize with directories**: Use subdirectories like `template-parts/` for better organization
3. **Always provide fallback**: Ensure the generic template exists
4. **Document your templates**: Add comments explaining what variables are expected
5. **Keep templates focused**: Each template should have a single, clear purpose

## Backdrop CMS Integration

For Backdrop CMS, this implementation:
- Uses `backdrop_get_path()` to locate theme directories
- Supports Backdrop's child/base theme structure
- Integrates with Backdrop's theme system via `list_themes()`
- Falls back gracefully when Backdrop functions aren't available

## Testing

Run the comprehensive test suite:

```bash
php /home/user/wp2bd/implementation/tests/get_template_part.test.php
```

The test suite includes:
- Simple template loading
- Specialized template priority
- Fallback behavior
- Nested directory structures
- Edge cases (empty names, numeric names, special characters)
- Action hook firing
- Multiple inclusions
