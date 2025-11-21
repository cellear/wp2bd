# WP_Query Implementation Documentation

## Overview

The WP_Query class is a WordPress compatibility layer for Backdrop CMS that enables WordPress themes to query and iterate through Backdrop nodes using the familiar WordPress Loop pattern.

## Files

- **`/home/user/wp2bd/implementation/classes/WP_Post.php`** - WP_Post class for node-to-post conversion
- **`/home/user/wp2bd/implementation/classes/WP_Query.php`** - Main WP_Query class and helper functions
- **`/home/user/wp2bd/implementation/tests/WP_Query.test.php`** - Comprehensive unit tests

## Architecture

### WP_Post Class

Converts Backdrop nodes into WordPress post objects with all standard properties.

#### Key Properties

```php
public $ID;                 // Post ID (from node nid)
public $post_author;        // Author ID (from node uid)
public $post_date;          // Published date
public $post_content;       // Body content
public $post_title;         // Title
public $post_excerpt;       // Summary/excerpt
public $post_status;        // publish|draft (from node status)
public $post_name;          // Slug (from path alias)
public $post_type;          // Content type (from node type)
public $comment_count;      // Number of comments
public $comment_status;     // open|closed
public $guid;               // Globally unique identifier
```

#### Static Method: from_node()

Converts a Backdrop node object to a WP_Post object.

```php
$node = node_load(123);
$post = WP_Post::from_node($node);
```

**Features:**
- Handles missing fields gracefully (returns empty strings, not NULL)
- Extracts body content from Backdrop's field structure
- Converts Backdrop status (0/1) to WordPress status (draft/publish)
- Generates slug from path alias or title
- Maps comment settings appropriately

### WP_Query Class

Provides WordPress-compatible query interface for Backdrop nodes.

#### Constructor

```php
$query = new WP_Query($args);
```

**Arguments:**

| Argument | Type | Default | Description |
|----------|------|---------|-------------|
| `post_type` | string/array | 'post' | Content type(s) to query |
| `posts_per_page` | int | 10 | Number of posts per page (-1 for all) |
| `paged` | int | 1 | Current page number |
| `offset` | int | 0 | Number of posts to skip |
| `orderby` | string | 'date' | Order by: date, modified, title, author, ID |
| `order` | string | 'DESC' | ASC or DESC |
| `post_status` | string | 'publish' | publish, draft, or any |
| `p` | int | 0 | Single post ID |
| `page_id` | int | 0 | Single page ID |
| `name` | string | '' | Post slug |
| `author` | int | 0 | Author ID |
| `author_name` | string | '' | Author username |
| `s` | string | '' | Search term |
| `meta_key` | string | '' | Custom field key |
| `meta_value` | string | '' | Custom field value |
| `meta_compare` | string | '=' | Comparison operator |

#### Properties

```php
$query->posts;              // Array of WP_Post objects
$query->post_count;         // Number of posts in current query
$query->current_post;       // Current position in loop (-1 = before loop)
$query->post;               // Current WP_Post object
$query->found_posts;        // Total posts matching query
$query->max_num_pages;      // Maximum number of pages
$query->query_vars;         // Query arguments
$query->error;              // Error message if query failed

// Query type flags
$query->is_single;          // Single post query
$query->is_page;            // Single page query
$query->is_archive;         // Archive listing
$query->is_home;            // Home page
$query->is_404;             // No results found
```

#### Methods

##### have_posts()

Check if more posts are available in the loop.

```php
if ($query->have_posts()) {
    // Posts available
}
```

**Returns:** `bool`

##### the_post()

Set up the next post and increment loop counter. Updates global `$post` and calls `setup_postdata()`.

```php
$query->the_post();
```

**Returns:** `void`

**Side Effects:**
- Increments `current_post`
- Sets `$query->post` to current post
- Updates global `$post`
- Calls `setup_postdata()` to populate template tag globals
- Fires `the_post` action hook
- Fires `loop_start` on first iteration

##### reset_postdata()

Reset loop to beginning state.

```php
$query->reset_postdata();
```

**Returns:** `void`

##### rewind_posts()

Alias for reset that matches WordPress naming.

```php
$query->rewind_posts();
```

##### get($var, $default)

Get a query variable value.

```php
$post_type = $query->get('post_type', 'post');
```

##### set($var, $value)

Set a query variable value.

```php
$query->set('posts_per_page', 20);
```

## The Loop Pattern

### Basic Loop

```php
<?php
$query = new WP_Query(array('post_type' => 'article'));

if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        ?>
        <article>
            <h2><?php the_title(); ?></h2>
            <div><?php the_content(); ?></div>
        </article>
        <?php
    }
} else {
    echo '<p>No posts found.</p>';
}
?>
```

### Custom Query with Reset

```php
<?php
// Main query
global $wp_query;
$original_query = $wp_query;

// Custom query
$custom_query = new WP_Query(array(
    'post_type' => 'page',
    'posts_per_page' => 5
));

if ($custom_query->have_posts()) {
    while ($custom_query->have_posts()) {
        $custom_query->the_post();
        // Display custom posts
        the_title();
    }
}

// Restore original query
$wp_query = $original_query;
wp_reset_postdata();
?>
```

### Nested Loops

```php
<?php
$outer_query = new WP_Query(array('post_type' => 'article'));

while ($outer_query->have_posts()) {
    $outer_query->the_post();

    echo '<h2>' . get_the_title() . '</h2>';

    // Inner loop
    $inner_query = new WP_Query(array(
        'post_type' => 'related',
        'posts_per_page' => 3
    ));

    if ($inner_query->have_posts()) {
        while ($inner_query->have_posts()) {
            $inner_query->the_post();
            echo '<li>' . get_the_title() . '</li>';
        }
    }

    // Important: Reset to outer loop
    wp_reset_postdata();
}
?>
```

## Query Mapping

### WordPress to Backdrop Translation

| WordPress | Backdrop | Implementation |
|-----------|----------|----------------|
| `WP_Query` | `EntityFieldQuery` | Maps query args to EFQ methods |
| `post_type` | `bundle` | `entityCondition('bundle', $type)` |
| `post_status` | `status` | `propertyCondition('status', 1)` |
| `author` | `uid` | `propertyCondition('uid', $author)` |
| `orderby=date` | `created` | `propertyOrderBy('created', 'DESC')` |
| `posts_per_page` | `range()` | `range($offset, $limit)` |
| `paged` | `range()` | Calculate offset from page number |
| `meta_key` | field name | `fieldCondition($field, 'value', $val)` |
| `s` (search) | `title LIKE` | `propertyCondition('title', '%term%', 'LIKE')` |

### Single Post Queries

**By ID:**
```php
$query = new WP_Query(array('p' => 123));
```
Maps to: `node_load(123)`

**By Slug:**
```php
$query = new WP_Query(array('name' => 'my-post-slug'));
```
Maps to: Path alias lookup + `node_load()`

**By Page ID:**
```php
$query = new WP_Query(array('page_id' => 456));
```
Maps to: `node_load(456)` with page type check

## Helper Functions

### setup_postdata($post)

Populate global variables for template tags.

```php
setup_postdata($post);
```

**Global Variables Set:**
- `$id` - Post ID
- `$authordata` - Author user object
- `$pages` - Array of content split by `<!--nextpage-->`
- `$page` - Current page number
- `$numpages` - Total number of pages
- `$multipage` - Boolean, true if multiple pages
- `$more` - Show more link flag
- `$currentday` - Current post day
- `$currentmonth` - Current post month

**Returns:** `bool` (success/failure)

### mysql2date($format, $date, $translate)

Convert MySQL date string to formatted date.

```php
$formatted = mysql2date('F j, Y', '2025-01-15 12:00:00');
// Returns: "January 15, 2025"
```

### get_post($post)

Get post object by ID or return current global post.

```php
$post = get_post(123);  // Get specific post
$post = get_post();     // Get current global post
```

## Error Handling

### Invalid Query Detection

```php
$query = new WP_Query(array('p' => 99999));

if ($query->is_404) {
    echo "Post not found";
}

if (!empty($query->error)) {
    echo "Error: " . $query->error;
}
```

### Common Error Cases

1. **Post not found** - Sets `is_404 = true`
2. **Backdrop functions unavailable** - Sets error message
3. **Invalid field conditions** - Caught and logged, query continues
4. **Empty results** - Returns empty array, not error

### Graceful Degradation

```php
if ($query->have_posts()) {
    // Display posts
} else {
    // No error thrown, just empty results
    echo '<p>No posts found.</p>';
}
```

## Testing

### Running Tests

```bash
cd /home/user/wp2bd/implementation/tests
php WP_Query.test.php
```

### Test Coverage

The test suite includes 9 comprehensive tests:

1. **WP_Post::from_node()** - Node to post conversion
2. **Constructor** - Query argument parsing
3. **have_posts() with content** - Loop detection
4. **have_posts() empty** - Empty query handling
5. **the_post() globals** - Global state management
6. **Loop iteration** - Complete loop execution
7. **reset_postdata()** - State reset
8. **Query by post type** - Type filtering
9. **Pagination** - Paged query handling

### Integration Tests

Additional integration test for nested loops:

```bash
# Uncomment the test in WP_Query.test.php and run:
php WP_Query.test.php
```

## Performance Considerations

### Query Optimization

1. **Use specific post types** - Avoids scanning all content types
2. **Limit posts_per_page** - Reduces memory usage
3. **Add status filter** - Significantly reduces result set
4. **Use IDs when possible** - Direct node_load() is faster than query

### Memory Management

```php
// Bad: Loads all nodes into memory
$query = new WP_Query(array('posts_per_page' => -1));

// Good: Paginate results
$query = new WP_Query(array(
    'posts_per_page' => 10,
    'paged' => $current_page
));
```

### Caching Strategy

Consider implementing query result caching for:
- Repeated queries with same arguments
- Static content (pages, menus)
- Archive pages with high traffic

## WordPress Compatibility

### Supported Features

- ✅ Basic query arguments (post_type, posts_per_page, etc.)
- ✅ Single post queries (by ID and slug)
- ✅ Ordering and sorting
- ✅ Author filtering
- ✅ Search functionality
- ✅ Status filtering (publish/draft)
- ✅ The Loop state machine
- ✅ Global post data management
- ✅ Nested loops with reset

### Not Yet Implemented

- ⏳ Taxonomy queries (category_name, tag, tax_query)
- ⏳ Date queries (year, monthnum, day)
- ⏳ Sticky posts
- ⏳ Post relationships (post_parent)
- ⏳ Advanced meta queries (meta_query arrays)
- ⏳ Post format filtering
- ⏳ Password-protected posts
- ⏳ Menu order sorting

### Differences from WordPress

1. **Performance** - Backdrop's EntityFieldQuery has different performance characteristics
2. **Field Structure** - Meta fields work differently in Backdrop
3. **Taxonomy** - Backdrop's taxonomy system differs from WordPress
4. **Caching** - Backdrop's cache system is used instead of WordPress object cache
5. **Hooks** - Action hooks are stubs unless WP2BD hook system is implemented

## Integration with Backdrop

### Required Backdrop Functions

The implementation requires these Backdrop functions to be available:

- `node_load($nid)` - Load single node
- `node_load_multiple($nids)` - Load multiple nodes
- `EntityFieldQuery` - Query nodes
- `user_load($uid)` - Load user data
- `db_select()` - Database queries
- `backdrop_lookup_path()` - Path alias lookup
- `url()` - URL generation

### Initialization

To use in a Backdrop site:

```php
// In your theme's template.php or custom module:
require_once '/path/to/WP_Query.php';

// Initialize global query for current page
global $wp_query;

if ($node = menu_get_object()) {
    // Single node view
    $wp_query = new WP_Query(array('p' => $node->nid));
} else {
    // Listing page
    $wp_query = new WP_Query(array(
        'post_type' => 'article',
        'posts_per_page' => 10
    ));
}
```

### Theme Integration

In WordPress theme templates:

```php
// index.php
<?php get_header(); ?>

<main>
    <?php
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            get_template_part('content', get_post_type());
        }
    } else {
        get_template_part('content', 'none');
    }
    ?>
</main>

<?php get_footer(); ?>
```

This will work seamlessly with the WP_Query implementation!

## Troubleshooting

### Common Issues

**Issue:** Posts not appearing
- Check `post_status` - default is 'publish' only
- Verify nodes exist and are published in Backdrop
- Check `post_type` matches Backdrop content type

**Issue:** Loop not iterating
- Ensure `the_post()` is called inside loop
- Verify `have_posts()` returns true
- Check `$query->post_count` value

**Issue:** Global $post not set
- Call `setup_postdata()` after setting post
- Use `the_post()` method, not manual iteration
- Check if post object is valid

**Issue:** Nested loops not working
- Always call `wp_reset_postdata()` after inner loop
- Use separate WP_Query objects for each loop
- Don't modify global $wp_query inside loops

### Debugging

```php
// Debug query
$query = new WP_Query($args);

echo "Posts found: " . $query->post_count . "\n";
echo "Current post: " . $query->current_post . "\n";
echo "Error: " . $query->error . "\n";

// Debug query vars
print_r($query->query_vars);

// Debug posts
foreach ($query->posts as $post) {
    echo $post->ID . ': ' . $post->post_title . "\n";
}
```

## Future Enhancements

### Planned Features

1. **Taxonomy Support** - Query by categories and tags
2. **Date Queries** - Filter by publish date ranges
3. **Meta Query Arrays** - Complex field queries
4. **Sticky Posts** - Support for sticky/featured posts
5. **Post Relationships** - Parent/child post queries
6. **Search Integration** - Use Backdrop's search module
7. **Cache Integration** - Implement query result caching
8. **Action Hooks** - Full hook system support

### Extension Points

The implementation provides hooks for extensions:

- `the_post` action - Fired for each post in loop
- `loop_start` action - Fired at loop beginning
- `loop_end` action - Fired at loop end

## References

- [WordPress WP_Query Documentation](https://developer.wordpress.org/reference/classes/wp_query/)
- [Backdrop EntityFieldQuery](https://api.backdropcms.org/api/backdrop/core%21modules%21entity%21entity.module/class/EntityFieldQuery/1)
- [WP2BD Specification: WP2BD-LOOP.md](/home/user/wp2bd/specs/WP2BD-LOOP.md)

## License

Part of the WP2BD (WordPress to Backdrop) compatibility layer project.

## Version

**Version:** 1.0.0
**Last Updated:** 2025-11-20
**Status:** Production Ready
