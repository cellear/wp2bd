# WP_Post Class Documentation

**Package:** WP2BD (WordPress to Backdrop Compatibility Layer)
**Version:** 1.0.0
**File:** `/implementation/classes/WP_Post.php`

---

## Overview

The `WP_Post` class provides WordPress-compatible post object structure for Backdrop CMS. It enables WordPress themes and plugins to work seamlessly with Backdrop by converting Backdrop node entities into WordPress post objects.

This class is a core component of **The Loop** system - the foundation of WordPress content display.

---

## Key Features

- Full WordPress post object compatibility
- Automatic conversion from Backdrop nodes
- Handles all standard WordPress post properties
- Robust error handling for missing data
- Support for multi-language field extraction
- Comment status and count mapping
- URL path alias to slug conversion
- Date/time conversion with GMT support

---

## Class Properties

### Required Properties

| Property | Type | Description | Backdrop Source |
|----------|------|-------------|-----------------|
| `ID` | int | Post ID | `$node->nid` |
| `post_author` | int | Author user ID | `$node->uid` |
| `post_date` | string | Publication date (Y-m-d H:i:s) | `$node->created` |
| `post_date_gmt` | string | Publication date GMT | `$node->created` (converted) |
| `post_content` | string | Main body content | `$node->body[LANGUAGE_NONE][0]['value']` |
| `post_title` | string | Post title | `$node->title` |
| `post_excerpt` | string | Excerpt/summary | `$node->body[LANGUAGE_NONE][0]['summary']` |
| `post_status` | string | Status (publish/draft) | `$node->status` (1=publish, 0=draft) |
| `post_name` | string | URL slug | `$node->path['alias']` or generated |
| `post_modified` | string | Last modified date | `$node->changed` |
| `post_modified_gmt` | string | Last modified GMT | `$node->changed` (converted) |
| `post_parent` | int | Parent post ID | Always 0 (Backdrop nodes not hierarchical) |
| `post_type` | string | Content type | `$node->type` |
| `comment_count` | int | Number of comments | `$node->comment_count` |

### Additional Properties

| Property | Type | Description | Default |
|----------|------|-------------|---------|
| `filter` | string | Filter context | 'raw' |
| `guid` | string | Globally unique identifier | Generated from node URL |
| `menu_order` | int | Menu sort order | 0 |
| `post_mime_type` | string | MIME type (attachments) | '' |
| `comment_status` | string | Comments enabled (open/closed) | Based on `$node->comment` |
| `ping_status` | string | Pingbacks enabled | 'closed' |

---

## Methods

### `from_node($node)`

**Static factory method** - Converts a Backdrop node to a WP_Post object.

#### Parameters

- **`$node`** (object, required) - Loaded Backdrop node object (stdClass)

#### Returns

- **`WP_Post|null`** - WP_Post object on success, `null` on failure

#### Error Handling

Returns `null` and triggers warnings in these cases:
- `$node` is not an object
- `$node` is missing required `nid` property
- `$node` is missing required `type` property

#### Basic Usage

```php
// Load a Backdrop node
$node = node_load(123);

// Convert to WP_Post
$post = WP_Post::from_node($node);

if ($post) {
    echo $post->post_title;      // "My Article Title"
    echo $post->ID;              // 123
    echo $post->post_content;    // "<p>Article body...</p>"
}
```

#### Advanced Usage

```php
// Load multiple nodes and convert them
$nids = array(1, 2, 3, 4, 5);
$nodes = node_load_multiple($nids);
$posts = array();

foreach ($nodes as $node) {
    $wp_post = WP_Post::from_node($node);
    if ($wp_post) {
        $posts[] = $wp_post;
    }
}

// Use in The Loop
global $wp_query;
$wp_query->posts = $posts;
$wp_query->post_count = count($posts);
```

---

### `__construct($data)`

Constructor - Creates new WP_Post with optional initial data.

#### Parameters

- **`$data`** (object|array, optional) - Initial post data

#### Example

```php
// Create empty post
$post = new WP_Post();

// Create with initial data
$post = new WP_Post(array(
    'ID' => 1,
    'post_title' => 'Hello World',
    'post_type' => 'page',
));
```

---

### `to_array()`

Converts post object to associative array.

#### Returns

- **`array`** - All post properties as array

#### Example

```php
$post = WP_Post::from_node($node);
$post_array = $post->to_array();

print_r($post_array);
// Array (
//     'ID' => 123,
//     'post_title' => 'My Title',
//     ...
// )
```

---

### `__get($name)`

Magic method for dynamic property access.

#### Parameters

- **`$name`** (string) - Property name

#### Returns

- **`mixed`** - Property value or `null` if not exists

#### Example

```php
$title = $post->post_title;  // Uses __get() internally
$custom = $post->nonexistent; // Returns null (no error)
```

---

### `__isset($name)`

Magic method to check if property exists and is set.

#### Parameters

- **`$name`** (string) - Property name

#### Returns

- **`bool`** - True if property exists and is not null

#### Example

```php
if (isset($post->post_title)) {
    echo $post->post_title;
}
```

---

## Usage Examples

### Example 1: Basic Node Conversion

```php
<?php
// Include the class
require_once 'classes/WP_Post.php';

// Load a Backdrop node
$node = node_load(456);

// Convert to WordPress post
$post = WP_Post::from_node($node);

// Access WordPress-style properties
echo '<h1>' . esc_html($post->post_title) . '</h1>';
echo '<div class="content">' . $post->post_content . '</div>';
echo '<p>By User ID: ' . $post->post_author . '</p>';
echo '<p>Published: ' . $post->post_date . '</p>';
?>
```

### Example 2: The Loop Integration

```php
<?php
// In a Backdrop page callback or template

// Load recent articles
$query = new EntityFieldQuery();
$query->entityCondition('entity_type', 'node')
      ->entityCondition('bundle', 'article')
      ->propertyCondition('status', 1)
      ->propertyOrderBy('created', 'DESC')
      ->range(0, 10);

$result = $query->execute();

if (isset($result['node'])) {
    $nids = array_keys($result['node']);
    $nodes = node_load_multiple($nids);

    // Convert to WP_Post objects
    $posts = array();
    foreach ($nodes as $node) {
        $posts[] = WP_Post::from_node($node);
    }

    // Set up WordPress-style global query
    global $wp_query;
    $wp_query = new stdClass();  // Or use WP_Query class
    $wp_query->posts = $posts;
    $wp_query->post_count = count($posts);
    $wp_query->current_post = -1;

    // Now WordPress templates can use The Loop
    // while (have_posts()) { the_post(); ... }
}
?>
```

### Example 3: Custom Query with Filtering

```php
<?php
// Get all published pages
$query = db_select('node', 'n')
    ->fields('n', array('nid'))
    ->condition('type', 'page')
    ->condition('status', 1)
    ->orderBy('title', 'ASC')
    ->execute();

$posts = array();
foreach ($query as $record) {
    $node = node_load($record->nid);
    $post = WP_Post::from_node($node);

    if ($post) {
        $posts[] = $post;
    }
}

// Use the posts array in WordPress-compatible way
foreach ($posts as $post) {
    echo '<li><a href="/node/' . $post->ID . '">' .
         esc_html($post->post_title) . '</a></li>';
}
?>
```

### Example 4: Handling Edge Cases

```php
<?php
// Safe conversion with error handling
function safe_node_to_post($nid) {
    $node = node_load($nid);

    if (!$node) {
        watchdog('wp2bd', 'Node @nid not found',
                 array('@nid' => $nid), WATCHDOG_WARNING);
        return null;
    }

    $post = WP_Post::from_node($node);

    if (!$post) {
        watchdog('wp2bd', 'Failed to convert node @nid to WP_Post',
                 array('@nid' => $nid), WATCHDOG_ERROR);
        return null;
    }

    // Validate required fields
    if (empty($post->post_title)) {
        $post->post_title = t('Untitled');
    }

    if (empty($post->post_content)) {
        $post->post_content = '<p>' . t('No content available.') . '</p>';
    }

    return $post;
}

$post = safe_node_to_post(999);
if ($post) {
    // Use the post
}
?>
```

---

## Field Mapping Details

### Body Field Extraction

Backdrop stores body content in a multi-dimensional array:

```php
$node->body = array(
    'und' => array(          // Language code (LANGUAGE_NONE = 'und')
        0 => array(           // Field delta (usually 0)
            'value' => '...',      // Main content
            'summary' => '...',    // Excerpt
            'format' => '...',     // Text format
        ),
    ),
);
```

The `from_node()` method automatically extracts:
- `value` → `$post->post_content`
- `summary` → `$post->post_excerpt`

### Date Conversion

Backdrop stores timestamps as Unix integers. WP_Post converts them to MySQL datetime format:

```php
// Backdrop
$node->created = 1705317000;  // Unix timestamp

// WordPress
$post->post_date = '2025-01-15 10:30:00';  // Local time
$post->post_date_gmt = '2025-01-15 17:30:00';  // GMT time
```

### Status Mapping

```php
// Backdrop → WordPress
$node->status = 1 → $post->post_status = 'publish'
$node->status = 0 → $post->post_status = 'draft'
```

### Comment Status Mapping

```php
// Backdrop → WordPress
$node->comment = 2 → $post->comment_status = 'open'
$node->comment = 1 → $post->comment_status = 'closed'
$node->comment = 0 → $post->comment_status = 'closed'
```

### Post Slug Generation

Priority order for generating `post_name`:

1. **Path alias** - If `$node->path['alias']` exists
2. **Generated slug** - Sanitized version of title
3. **Fallback** - `'node-{nid}'` format

---

## Error Handling

### Invalid Input

```php
// Returns null, triggers E_USER_WARNING
$post = WP_Post::from_node(null);
$post = WP_Post::from_node("string");
$post = WP_Post::from_node(array());
```

### Missing Required Properties

```php
// Node without nid - returns null
$node = new stdClass();
$node->title = 'Test';
$post = WP_Post::from_node($node);  // null
```

### Missing Optional Properties

Missing optional properties use safe defaults:

```php
// Node with minimal data
$node = new stdClass();
$node->nid = 123;
$node->type = 'page';

$post = WP_Post::from_node($node);

// Defaults applied:
// - post_author = 0
// - post_title = ''
// - post_content = ''
// - comment_count = 0
// - etc.
```

---

## Integration with The Loop

The `WP_Post` class is designed to work with The Loop functions:

```php
// Template file (e.g., index.php)
<?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <h2><?php the_title(); ?></h2>
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
<?php else : ?>
    <p>No posts found.</p>
<?php endif; ?>
```

Behind the scenes, `the_post()` calls `setup_postdata()` which expects a `WP_Post` object.

---

## Performance Considerations

### Bulk Loading

For better performance, load multiple nodes at once:

```php
// BAD - Multiple database queries
foreach ($nids as $nid) {
    $node = node_load($nid);
    $posts[] = WP_Post::from_node($node);
}

// GOOD - Single batch query
$nodes = node_load_multiple($nids);
foreach ($nodes as $node) {
    $posts[] = WP_Post::from_node($node);
}
```

### Caching

Consider caching converted WP_Post objects:

```php
$cache_key = 'wp_post_' . $nid;
$post = cache_get($cache_key, 'cache_wp2bd');

if (!$post) {
    $node = node_load($nid);
    $post = WP_Post::from_node($node);
    cache_set($cache_key, $post, 'cache_wp2bd', CACHE_TEMPORARY);
}
```

---

## Testing

Run the unit tests:

```bash
cd /home/user/wp2bd/implementation/tests
php WP_Post.test.php
```

Tests cover:
1. Basic node conversion
2. Body content and excerpt extraction
3. Missing body field handling
4. Invalid input error handling
5. Draft status conversion
6. Path alias mapping
7. Comment fields conversion
8. Default value handling

---

## Compatibility

### WordPress Versions

This class mimics WordPress post object structure from WordPress 4.9+. It includes all core properties used by most themes and plugins.

### Backdrop Versions

Tested with Backdrop CMS 1.30+. Should work with all 1.x versions.

### PHP Requirements

- PHP 5.6 or higher
- PHP 7.0+ recommended for better performance

---

## Known Limitations

1. **Hierarchical Posts** - `post_parent` is always 0. Backdrop nodes don't have built-in parent relationships.

2. **Post Formats** - WordPress post formats (aside, gallery, video) are not automatically mapped. Consider using taxonomy mapping.

3. **Sticky Posts** - WordPress sticky post functionality not included. Would need custom implementation.

4. **Revisions** - WordPress revision history not mapped. Backdrop revisions use different structure.

5. **Attachments** - Post attachments/featured images require separate mapping (not included in base WP_Post class).

---

## Future Enhancements

Planned improvements:

- [ ] Featured image mapping (`post_thumbnail`)
- [ ] Post meta data support (`add_post_meta()` compatibility)
- [ ] Taxonomy/term mapping (categories, tags)
- [ ] Custom field mapping
- [ ] Revision history conversion
- [ ] Post format detection

---

## Related Documentation

- **WP2BD-LOOP.md** - Complete Loop system specification
- **WP_Query class** - Query and loop management
- **Template Tags** - Functions like `the_title()`, `the_content()`

---

## Support & Contributing

For issues, questions, or contributions:

1. Check the main WP2BD documentation
2. Review test cases for usage examples
3. Submit bug reports with node structure details
4. Include Backdrop and WordPress version numbers

---

## License

Part of the WP2BD project. See main project license.

---

## Changelog

### Version 1.0.0 (2025-01-15)

- Initial implementation
- Full WordPress post property support
- Backdrop node conversion
- Comprehensive error handling
- 8 unit tests (all passing)
- Complete documentation
