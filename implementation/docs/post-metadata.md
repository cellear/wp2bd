# Post Metadata Functions

This document describes the WordPress post metadata and information functions implemented for the WP2BD compatibility layer.

## Overview

These functions retrieve metadata and information about posts, including post type, format, dates, times, and author information. They map WordPress's post metadata system to Backdrop's node and user systems.

**File:** `/home/user/wp2bd/implementation/functions/post-metadata.php`

## Functions Implemented

1. `get_post_format($post = null)` - Get post format (standard, aside, gallery, etc.)
2. `get_the_date($format = '', $post = null)` - Get formatted post date
3. `get_the_time($format = '', $post = null)` - Get formatted post time
4. `get_the_author()` - Get current post author name
5. `get_the_author_meta($field = '', $user_id = null)` - Get author metadata

**Note:** `get_post_type($post = null)` is already implemented in `/home/user/wp2bd/implementation/functions/conditionals.php`

## Detailed Function Documentation

### get_post_format()

Retrieves the post format of a post.

```php
string|false get_post_format( int|WP_Post|object|null $post = null )
```

**Parameters:**
- `$post` (int|WP_Post|object|null) - Optional. Post ID or post object. Default is global $post.

**Return Value:**
- Returns post format slug ('aside', 'gallery', 'link', etc.) or 'standard' if no format
- Returns false if post doesn't exist

**WordPress Post Formats:**
- `standard` - Default format (blog post)
- `aside` - Typically short, note-like posts
- `gallery` - Contains image gallery
- `link` - Highlights a link to another site
- `image` - Single image post
- `quote` - Contains a quotation
- `status` - Short status update (like Twitter)
- `video` - Contains video content
- `audio` - Contains audio content
- `chat` - Chat transcript

**Backdrop Mapping:**
- Checks `$post->post_format` property if set
- Attempts to get format from `post_format` taxonomy
- Checks Backdrop `field_post_format` field if available
- Defaults to 'standard' for most Backdrop nodes

**Usage Examples:**

```php
// Get current post format
$format = get_post_format();
if ( $format === 'gallery' ) {
    // Display gallery template
}

// Get format by post ID
$format = get_post_format( 42 );

// Use in conditional templates (from Twenty Seventeen theme)
if ( 'video' === get_post_format() ) {
    // Load video template part
    get_template_part( 'template-parts/post/content', 'video' );
}
```

---

### get_the_date()

Retrieves the date on which the post was written.

```php
string get_the_date( string $format = '', int|WP_Post|object|null $post = null )
```

**Parameters:**
- `$format` (string) - Optional. PHP date format. Default is `get_option('date_format')` or 'F j, Y'.
- `$post` (int|WP_Post|object|null) - Optional. Post ID or post object. Default is global $post.

**Return Value:**
- Returns formatted date string
- Returns empty string if post doesn't exist or has no date

**Date Format Strings:**

Common WordPress date formats:
- `F j, Y` - "March 10, 2024" (default)
- `Y-m-d` - "2024-03-10"
- `m/d/Y` - "03/10/2024"
- `d/m/Y` - "10/03/2024"
- `l, F j, Y` - "Sunday, March 10, 2024"
- `M j, Y` - "Mar 10, 2024"

See [PHP date() function](https://www.php.net/manual/en/function.date.php) for all format characters.

**Backdrop Mapping:**
- Uses `$post->post_date` (MySQL datetime format: Y-m-d H:i:s)
- For Backdrop nodes, converts `$node->created` timestamp to date string
- Formats using PHP `date()` function

**Usage Examples:**

```php
// Get date with default format
echo get_the_date(); // "March 10, 2024"

// Get date with custom format
echo get_the_date( 'Y-m-d' ); // "2024-03-10"

// Get date for specific post
echo get_the_date( 'F j, Y', 42 );

// From Twenty Seventeen theme - content.php
echo get_the_date();

// ISO 8601 date format (for schema markup)
echo get_the_date( 'c' ); // "2024-03-10T14:30:00+00:00"
```

---

### get_the_time()

Retrieves the time at which the post was written.

```php
string get_the_time( string $format = '', int|WP_Post|object|null $post = null )
```

**Parameters:**
- `$format` (string) - Optional. PHP date/time format. Default is `get_option('time_format')` or 'g:i a'.
- `$post` (int|WP_Post|object|null) - Optional. Post ID or post object. Default is global $post.

**Return Value:**
- Returns formatted time string
- Returns empty string if post doesn't exist or has no date

**Time Format Strings:**

Common WordPress time formats:
- `g:i a` - "5:32 pm" (default)
- `g:i A` - "5:32 PM"
- `H:i` - "17:32" (24-hour format)
- `H:i:s` - "17:32:45" (with seconds)

**Backdrop Mapping:**
- Uses `$post->post_date` (MySQL datetime format)
- For Backdrop nodes, converts `$node->created` timestamp to time string
- Formats using PHP `date()` function

**Usage Examples:**

```php
// Get time with default format
echo get_the_time(); // "5:32 pm"

// Get time with custom format
echo get_the_time( 'H:i' ); // "17:32"

// Get time for specific post
echo get_the_time( 'g:i A', 42 ); // "5:32 PM"

// From Twenty Seventeen theme - template-parts/post/content.php
printf(
    '<time class="entry-date published" datetime="%s">%s</time>',
    esc_attr( get_the_date( 'c' ) ),
    esc_html( get_the_date() )
);

// Get full datetime
echo get_the_time( 'Y-m-d H:i:s' ); // "2024-03-10 17:32:45"
```

---

### get_the_author()

Retrieves the display name of the post author.

```php
string get_the_author( void )
```

**Parameters:**
- None (uses global `$post`)

**Return Value:**
- Returns author's display name
- Returns empty string if no post or no author

**Backdrop Mapping:**
- Uses `$post->post_author` (user ID) or `$node->uid` (Backdrop user ID)
- Loads user via `user_load($uid)` (Backdrop) or `get_userdata($id)` (WordPress)
- Returns `display_name` or falls back to `user_login`/`name`
- Uses global `$authordata` if available

**User Field Mapping:**

| WordPress Field | Backdrop Field | Description |
|----------------|----------------|-------------|
| `display_name` | `name` | User's display name |
| `user_login` | `name` | Username |
| `ID` | `uid` | User ID |

**Usage Examples:**

```php
// Get current post author
echo get_the_author(); // "John Doe"

// From Twenty Seventeen theme - template-parts/post/content.php
printf(
    '<span class="byline">by %s</span>',
    '<span class="author vcard">' . get_the_author() . '</span>'
);

// Author archive link
printf(
    '<a href="%s">%s</a>',
    esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
    get_the_author()
);
```

---

### get_the_author_meta()

Retrieves specific metadata about the post author.

```php
string get_the_author_meta( string $field = '', int|null $user_id = null )
```

**Parameters:**
- `$field` (string) - Optional. The user field to retrieve. Default empty (returns display_name).
- `$user_id` (int|null) - Optional. User ID. Default null (uses current post author).

**Return Value:**
- Returns the requested field value as a string
- Returns empty string if user doesn't exist or field not found

**Supported Fields:**

| Field | Description | WordPress | Backdrop |
|-------|-------------|-----------|----------|
| `ID` | User ID | `$user->ID` | `$user->uid` |
| `display_name` | Display name | `$user->display_name` | `$user->name` |
| `user_login` | Username | `$user->user_login` | `$user->name` |
| `user_email` | Email address | `$user->user_email` | `$user->mail` |
| `first_name` | First name | `$user->first_name` | `$user->field_first_name` |
| `last_name` | Last name | `$user->last_name` | `$user->field_last_name` |
| `description` | Bio/description | `$user->description` | `$user->signature` or `$user->field_bio` |
| `user_url` | Website URL | `$user->user_url` | `$user->field_url` |
| `nickname` | Nickname | `$user->nickname` | `$user->name` |

**Backdrop Field Extraction:**

Backdrop stores user profile fields in complex arrays. The function automatically extracts values from:
- `$user->field[und][0][value]` (Field API structure)
- `$user->field[0][value]` (Simplified structure)
- `$user->field` (Direct value)

**Usage Examples:**

```php
// Get author email
echo get_the_author_meta( 'user_email' );

// Get author display name for current post
echo get_the_author_meta( 'display_name' );

// Get specific user's data by ID
echo get_the_author_meta( 'display_name', 42 );

// Get author bio
echo get_the_author_meta( 'description' );

// From Twenty Seventeen theme - author.php
printf(
    '<h1 class="page-title">%s</h1>',
    get_the_author_meta( 'display_name' )
);

// Build author card
$author_id = get_the_author_meta( 'ID' );
$author_name = get_the_author_meta( 'display_name' );
$author_bio = get_the_author_meta( 'description' );
$author_url = get_the_author_meta( 'user_url' );

echo '<div class="author-card">';
echo '<h3>' . esc_html( $author_name ) . '</h3>';
echo '<p>' . esc_html( $author_bio ) . '</p>';
if ( $author_url ) {
    echo '<a href="' . esc_url( $author_url ) . '">Website</a>';
}
echo '</div>';
```

---

## WordPress to Backdrop Mapping

### Date/Time Data

| WordPress | Backdrop | Notes |
|-----------|----------|-------|
| `$post->post_date` | `date('Y-m-d H:i:s', $node->created)` | MySQL datetime format |
| `$post->post_date_gmt` | `gmdate('Y-m-d H:i:s', $node->created)` | GMT datetime |
| Default date format | `'F j, Y'` | Can be customized via options |
| Default time format | `'g:i a'` | Can be customized via options |

### Author Data

| WordPress | Backdrop | Notes |
|-----------|----------|-------|
| `$post->post_author` | `$node->uid` | User ID |
| `user_load($id)` | `user_load($uid)` | Load user object |
| `$user->display_name` | `$user->name` | Display name |
| `$user->user_email` | `$user->mail` | Email address |
| `$user->first_name` | `$user->field_first_name[und][0][value]` | Profile field |
| `$user->description` | `$user->signature` or `$user->field_bio` | Bio/description |

### Post Format Data

| WordPress | Backdrop | Notes |
|-----------|----------|-------|
| `post_format` taxonomy | `field_post_format` field | May need custom mapping |
| Default: 'standard' | Default: 'standard' | Most common format |

## Twenty Seventeen Theme Usage Examples

These examples show how the Twenty Seventeen theme uses post metadata functions:

### Displaying Post Date

```php
// template-parts/post/content.php
<div class="entry-meta">
    <span class="posted-on">
        <?php
        printf(
            '<time class="entry-date published" datetime="%s">%s</time>',
            esc_attr( get_the_date( 'c' ) ),
            esc_html( get_the_date() )
        );
        ?>
    </span>
</div>
```

### Displaying Author Information

```php
// template-parts/post/content.php
<span class="byline">
    <span class="author vcard">
        <a class="url fn n" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
            <?php echo get_the_author(); ?>
        </a>
    </span>
</span>
```

### Author Archive Template

```php
// author.php
<header class="page-header">
    <h1 class="page-title">
        <?php
        printf(
            __( 'Posts by %s', 'twentyseventeen' ),
            '<span class="vcard">' . get_the_author_meta( 'display_name' ) . '</span>'
        );
        ?>
    </h1>

    <?php if ( get_the_author_meta( 'description' ) ) : ?>
        <div class="taxonomy-description">
            <?php echo wpautop( get_the_author_meta( 'description' ) ); ?>
        </div>
    <?php endif; ?>
</header>
```

### Post Format Templates

```php
// template-parts/post/content.php
<?php
// Different templates based on post format
$format = get_post_format();
if ( $format ) {
    get_template_part( 'template-parts/post/content', $format );
} else {
    // Standard format
    ?>
    <article>
        <header><?php the_title(); ?></header>
        <div class="entry-content"><?php the_content(); ?></div>
    </article>
    <?php
}
?>
```

## Date and Time Format Reference

### Common Format Strings

**Date Formats:**
- `F j, Y` → March 10, 2024
- `Y-m-d` → 2024-03-10
- `m/d/Y` → 03/10/2024
- `d/m/Y` → 10/03/2024
- `M j, Y` → Mar 10, 2024
- `l, F j, Y` → Sunday, March 10, 2024
- `c` → 2024-03-10T14:30:00+00:00 (ISO 8601)

**Time Formats:**
- `g:i a` → 5:32 pm
- `g:i A` → 5:32 PM
- `H:i` → 17:32
- `H:i:s` → 17:32:45
- `h:i A` → 05:32 PM

**Date Characters:**
- `d` - Day of month, 2 digits with leading zeros (01-31)
- `D` - Day of week, textual, 3 letters (Mon-Sun)
- `j` - Day of month without leading zeros (1-31)
- `l` - Day of week, textual, long (Monday-Sunday)
- `F` - Month, textual, long (January-December)
- `m` - Month, numeric with leading zeros (01-12)
- `M` - Month, textual, 3 letters (Jan-Dec)
- `n` - Month without leading zeros (1-12)
- `Y` - Year, 4 digits (2024)
- `y` - Year, 2 digits (24)

**Time Characters:**
- `a` - Lowercase am or pm
- `A` - Uppercase AM or PM
- `g` - 12-hour format without leading zeros (1-12)
- `h` - 12-hour format with leading zeros (01-12)
- `G` - 24-hour format without leading zeros (0-23)
- `H` - 24-hour format with leading zeros (00-23)
- `i` - Minutes with leading zeros (00-59)
- `s` - Seconds with leading zeros (00-59)

## Testing

Comprehensive tests are available at:
- **File:** `/home/user/wp2bd/implementation/tests/post-metadata.test.php`
- **Test Count:** 47 tests with 47 assertions
- **Coverage:** All functions, edge cases, format variations, and Backdrop integration

Run tests:
```bash
cd /home/user/wp2bd/implementation/tests
php post-metadata.test.php
```

## Implementation Notes

### get_post_format() Implementation

1. Checks `$post->post_format` property first (pre-loaded)
2. Attempts to retrieve from `post_format` taxonomy using `get_the_terms()`
3. Checks Backdrop `field_post_format` field if available
4. Returns 'standard' as default

**Limitation:** Most Backdrop sites don't have post formats by default. Returns 'standard' for most content.

### Date/Time Implementation

1. Prioritizes `$post->post_date` (WordPress format)
2. Falls back to `$node->created` (Backdrop timestamp)
3. Converts timestamps to MySQL datetime format
4. Uses PHP `date()` function for formatting
5. Supports all WordPress date/time format strings

### Author Data Implementation

1. Loads user via `user_load($uid)` or `get_userdata($id)`
2. Maps WordPress user fields to Backdrop equivalents
3. Extracts values from Backdrop Field API structures
4. Falls back gracefully for missing fields
5. Uses global `$authordata` when available

### Field Value Extraction

The `_wp2bd_extract_field_value()` helper function handles Backdrop's complex field structures:

```php
// Backdrop Field API structure
$field = array(
    'und' => array(
        0 => array(
            'value' => 'The actual value'
        )
    )
);

// Extracts to: "The actual value"
```

## Compatibility Notes

- **WordPress Version:** Compatible with WordPress 4.9+ behavior
- **Backdrop Version:** Compatible with Backdrop 1.30+
- **PHP Version:** Requires PHP 5.6+ (for `...` operator in tests)
- **Dependencies:** Requires WP_Post class from `/home/user/wp2bd/implementation/classes/WP_Post.php`

## See Also

- [WP_Post Class Documentation](../classes/WP_Post.php)
- [Content Display Functions](content-display.php)
- [Conditional Functions](conditionals.php)
- [Loop Functions](loop.php)
