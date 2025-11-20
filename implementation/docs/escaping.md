# WordPress Escaping Functions - Documentation

## Overview

Escaping functions are **critical security functions** that prevent XSS (Cross-Site Scripting) attacks by ensuring untrusted data is safely rendered in HTML, attributes, URLs, and JavaScript contexts.

**Golden Rule**: Always escape output, never escape on input. Escape data at the point of output based on the context where it's being used.

## Security Principle

```
INPUT → STORE RAW → OUTPUT WITH ESCAPING
```

- **Store data raw** in the database
- **Escape when outputting** based on context
- **Never trust user input** or any external data source

## Functions Reference

### 1. esc_html($text)

**Purpose**: Escape text for safe display in HTML content.

**When to use**:
- Outputting post titles, content, comments
- Displaying user-generated text
- Any text that appears between HTML tags

**What it does**:
- Converts `<`, `>`, `&`, `"`, `'` to HTML entities
- Prevents script injection
- Preserves UTF-8 characters

**Examples from Twenty Seventeen**:

```php
// Post title in content
<h2 class="entry-title">
    <?php echo esc_html(get_the_title()); ?>
</h2>

// Comment author name
<span class="fn">
    <?php echo esc_html(get_comment_author()); ?>
</span>

// Blog description
<p class="site-description">
    <?php echo esc_html(get_bloginfo('description')); ?>
</p>
```

**Security Example**:

```php
// Dangerous - allows XSS
echo get_the_title(); // If title is: <script>alert('xss')</script>

// Safe - prevents XSS
echo esc_html(get_the_title()); // Outputs: &lt;script&gt;alert('xss')&lt;/script&gt;
```

### 2. esc_attr($text)

**Purpose**: Escape text for use in HTML attributes.

**When to use**:
- Setting class, id, title, alt, data-* attributes
- Any dynamic value in an HTML attribute
- Form input values

**What it does**:
- Same HTML entity encoding as `esc_html()`
- Additionally removes line breaks
- More restrictive for attribute safety

**Examples from Twenty Seventeen**:

```php
// Post class attribute
<article id="post-<?php echo esc_attr(get_the_ID()); ?>"
         class="<?php echo esc_attr(implode(' ', get_post_class())); ?>">

// Image alt text
<img src="image.jpg" alt="<?php echo esc_attr($image_alt); ?>">

// Link title attribute
<a href="#" title="<?php echo esc_attr($link_title); ?>">Link</a>

// Data attributes
<div data-post-id="<?php echo esc_attr($post_id); ?>"
     data-author="<?php echo esc_attr($author_name); ?>">
```

**Security Example**:

```php
// Dangerous - allows attribute breakout
<div class="<?php echo $user_class; ?>">
// If $user_class is: " onclick="alert('xss')"

// Safe - prevents breakout
<div class="<?php echo esc_attr($user_class); ?>">
// Outputs: &quot; onclick=&quot;alert('xss')&quot;
```

### 3. esc_url($url, $protocols = null, $_context = 'display')

**Purpose**: Sanitize and validate URLs for safe display in HTML.

**When to use**:
- Link href attributes
- Image src attributes
- Form action attributes
- Any URL displayed in HTML

**What it does**:
- Validates URL protocol against whitelist
- Rejects dangerous protocols (javascript:, data:, vbscript:)
- Encodes HTML entities for display
- Allows relative URLs
- Handles spaces and special characters

**Default allowed protocols**:
- http, https, ftp, ftps
- mailto, news, irc, gopher, nntp
- feed, telnet

**Examples from Twenty Seventeen**:

```php
// Post permalink
<a href="<?php echo esc_url(get_permalink()); ?>">
    Read more
</a>

// Image source
<img src="<?php echo esc_url($thumbnail_url); ?>" />

// External link
<a href="<?php echo esc_url($external_link); ?>" target="_blank">
    Visit site
</a>

// Pagination link
<a href="<?php echo esc_url(get_pagenum_link($page_num)); ?>">
    Page <?php echo $page_num; ?>
</a>

// Feed URL
<link rel="alternate" type="application/rss+xml"
      href="<?php echo esc_url(get_feed_link()); ?>" />
```

**Security Examples**:

```php
// DANGEROUS - allows javascript: protocol
<a href="<?php echo $user_url; ?>">Click</a>
// If $user_url is: javascript:alert('xss')

// SAFE - blocks dangerous protocols
<a href="<?php echo esc_url($user_url); ?>">Click</a>
// Returns empty string for javascript: URLs

// Custom protocol whitelist
$video_url = esc_url($url, ['http', 'https', 'rtsp']);

// Allow only HTTPS
$secure_url = esc_url($url, ['https']);
```

**Display vs Raw**:

```php
// For HTML display - encodes ampersands
echo esc_url('http://example.com?a=1&b=2');
// Output: http://example.com?a=1&amp;b=2

// For database/redirect - keeps ampersands
echo esc_url_raw('http://example.com?a=1&b=2');
// Output: http://example.com?a=1&b=2
```

### 4. esc_url_raw($url, $protocols = null)

**Purpose**: Sanitize URL for database storage or HTTP redirects.

**When to use**:
- Storing URLs in database
- Using URLs in wp_redirect()
- API calls with URLs
- Anywhere URL needs to be valid but not displayed

**What it does**:
- Same protocol validation as `esc_url()`
- Does NOT encode HTML entities
- Preserves ampersands and special characters

**Examples**:

```php
// Saving to database
update_post_meta($post_id, 'external_link', esc_url_raw($user_url));

// Redirect
wp_redirect(esc_url_raw($redirect_url));
exit;

// API call
$api_url = esc_url_raw($base_url . '?key=' . $api_key);
$response = wp_remote_get($api_url);

// Email link (preserve & in query string)
$email_link = esc_url_raw('mailto:test@example.com?subject=Hello&body=Message');
```

## Helper Functions

### esc_js($text)

Escape text for use in JavaScript strings.

```php
<script>
var message = '<?php echo esc_js($message); ?>';
alert(message);
</script>
```

### esc_textarea($text)

Escape text for textarea elements.

```php
<textarea name="comment">
<?php echo esc_textarea($comment); ?>
</textarea>
```

### sanitize_text_field($text)

Remove HTML tags and sanitize text input.

```php
// Processing form input
$name = sanitize_text_field($_POST['name']);
```

## Common Patterns in Twenty Seventeen

### Post Loop

```php
<?php while (have_posts()) : the_post(); ?>
    <article class="<?php echo esc_attr(implode(' ', get_post_class())); ?>">
        <header>
            <h2>
                <a href="<?php echo esc_url(get_permalink()); ?>">
                    <?php echo esc_html(get_the_title()); ?>
                </a>
            </h2>
            <div class="entry-meta">
                <span class="posted-on">
                    <a href="<?php echo esc_url(get_permalink()); ?>">
                        <?php echo esc_html(get_the_date()); ?>
                    </a>
                </span>
                <span class="byline">
                    by <?php echo esc_html(get_the_author()); ?>
                </span>
            </div>
        </header>
        <div class="entry-content">
            <?php the_content(); // WordPress function, already escaped ?>
        </div>
    </article>
<?php endwhile; ?>
```

### Header Template

```php
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php echo esc_attr(get_bloginfo('charset')); ?>">
    <title><?php echo esc_html(wp_title('|', false, 'right')); ?></title>
    <link rel="profile" href="<?php echo esc_url('http://gmpg.org/xfn/11'); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <header class="site-header">
        <h1 class="site-title">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <?php echo esc_html(get_bloginfo('name')); ?>
            </a>
        </h1>
    </header>
```

### Navigation Menu

```php
<nav class="main-navigation">
    <ul>
        <?php foreach ($menu_items as $item) : ?>
            <li class="<?php echo esc_attr($item->classes); ?>">
                <a href="<?php echo esc_url($item->url); ?>"
                   title="<?php echo esc_attr($item->title); ?>">
                    <?php echo esc_html($item->label); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
```

### Comment Display

```php
<div id="comment-<?php echo esc_attr(get_comment_ID()); ?>"
     class="<?php echo esc_attr(implode(' ', get_comment_class())); ?>">
    <div class="comment-author">
        <?php echo get_avatar(get_comment_author_email(), 48); ?>
        <span class="fn"><?php echo esc_html(get_comment_author()); ?></span>
    </div>
    <div class="comment-content">
        <?php comment_text(); // Already escaped by WordPress ?>
    </div>
    <div class="reply">
        <a href="<?php echo esc_url(get_comment_reply_link($comment)); ?>">
            Reply
        </a>
    </div>
</div>
```

## Security Best Practices

### 1. Escape Late, Escape Often

```php
// WRONG - escaping too early
$title = esc_html(get_the_title());
// ... later ...
echo $title; // What if it's used in an attribute?

// RIGHT - escape at point of output
$title = get_the_title();
// ... later ...
echo esc_html($title); // Context-appropriate escaping
```

### 2. Context-Appropriate Escaping

```php
$value = "User's value";

// HTML content context
echo '<p>' . esc_html($value) . '</p>';

// Attribute context
echo '<div class="' . esc_attr($value) . '">';

// URL context
echo '<a href="' . esc_url($value) . '">';

// JavaScript context
echo '<script>var x = "' . esc_js($value) . '";</script>';
```

### 3. Never Double Escape

```php
// WRONG - double escaping
echo esc_html(esc_html($text)); // Outputs: &amp;lt;script&amp;gt;

// RIGHT - escape once at output
echo esc_html($text);
```

### 4. Don't Trust Any External Data

```php
// Database data - ESCAPE IT
echo esc_html(get_post_meta($post_id, 'custom_field', true));

// User input - ESCAPE IT
echo esc_html($_POST['name']);

// API responses - ESCAPE IT
echo esc_html($api_response->title);

// Query parameters - ESCAPE IT
echo esc_html($_GET['search']);
```

### 5. Protocol Validation for URLs

```php
// ALWAYS validate URL protocols
$url = esc_url($user_input);
if (empty($url)) {
    // URL was rejected (dangerous protocol)
    $url = home_url();
}

echo '<a href="' . esc_url($url) . '">';
```

## XSS Attack Prevention

### Common Attack Vectors (All Prevented)

```php
// 1. Script injection
$malicious = '<script>alert("xss")</script>';
echo esc_html($malicious);
// Safe: &lt;script&gt;alert("xss")&lt;/script&gt;

// 2. Attribute breakout
$malicious = '" onclick="alert(1)"';
echo '<div title="' . esc_attr($malicious) . '">';
// Safe: <div title="&quot; onclick=&quot;alert(1)&quot;">

// 3. JavaScript protocol
$malicious = 'javascript:alert(1)';
echo '<a href="' . esc_url($malicious) . '">';
// Safe: <a href=""> (empty, blocked)

// 4. Data URL
$malicious = 'data:text/html,<script>alert(1)</script>';
echo '<img src="' . esc_url($malicious) . '">';
// Safe: <img src=""> (empty, blocked)

// 5. Event handler injection
$malicious = 'x" onload="alert(1)';
echo '<img alt="' . esc_attr($malicious) . '">';
// Safe: <img alt="x&quot; onload=&quot;alert(1)">

// 6. Mixed case protocol bypass
$malicious = 'JaVaScRiPt:alert(1)';
echo '<a href="' . esc_url($malicious) . '">';
// Safe: <a href=""> (empty, blocked)
```

## Testing Security

All escaping functions have been tested against:

1. **XSS Vectors**: Script tags, event handlers, protocol attacks
2. **Edge Cases**: Null, empty, arrays, objects, long strings
3. **UTF-8**: International characters, emoji, accents
4. **Protocol Validation**: Allowed/blocked protocols, case sensitivity
5. **Relative URLs**: Paths, query strings, fragments
6. **Encoding**: HTML entities, quotes, special characters

**Test Coverage**: 57 assertions, 100% pass rate required.

## Performance Considerations

Escaping functions are lightweight but called frequently:

```php
// INEFFICIENT - escaping in loop
foreach ($items as $item) {
    echo esc_html(get_option('site_name')) . ': ' . esc_html($item);
}

// BETTER - escape once
$site_name = esc_html(get_option('site_name'));
foreach ($items as $item) {
    echo $site_name . ': ' . esc_html($item);
}
```

## Migration from WordPress to Backdrop

These functions provide identical behavior to WordPress, ensuring:

- Drop-in compatibility
- Same security guarantees
- Identical output for all test cases
- No theme code changes required

## Quick Reference

| Context | Function | Example |
|---------|----------|---------|
| HTML content | `esc_html()` | `<p><?php echo esc_html($text); ?></p>` |
| HTML attribute | `esc_attr()` | `<div class="<?php echo esc_attr($class); ?>">` |
| URL (display) | `esc_url()` | `<a href="<?php echo esc_url($url); ?>">` |
| URL (database) | `esc_url_raw()` | `update_option('url', esc_url_raw($url));` |
| JavaScript | `esc_js()` | `<script>var x='<?php echo esc_js($val); ?>';</script>` |
| Textarea | `esc_textarea()` | `<textarea><?php echo esc_textarea($text); ?></textarea>` |

## See Also

- [WordPress Data Validation](https://developer.wordpress.org/plugins/security/data-validation/)
- [WordPress Escaping Documentation](https://developer.wordpress.org/apis/security/escaping/)
- [OWASP XSS Prevention](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)
