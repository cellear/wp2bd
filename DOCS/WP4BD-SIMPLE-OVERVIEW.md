# WP4BD - Simple Overview

## The Big Idea

WordPress becomes a rendering engine. Backdrop handles all the data, WordPress just displays it.

---

## The Three Main Variables

### 1. `$post` - The Current Post Object

```php
$post->ID              // 123
$post->post_title      // "My Blog Post"
$post->post_content    // "<p>The content...</p>"
$post->post_date       // "2025-12-01 10:30:00"
```

**When it gets set:** By `the_post()` during The Loop
**Where it comes from:** We create it from a Backdrop node
**Who uses it:** Template tags like `the_title()`, `the_content()`

### 2. `$wp_query` - The Query Object

```php
$wp_query->posts       // Array of WP_Post objects
$wp_query->is_single   // true/false
$wp_query->is_page     // true/false
$wp_query->is_home     // true/false
```

**When it gets set:** Before WordPress loads
**Where it comes from:** We create it and populate with our data
**Who uses it:** The Loop (`have_posts()`, `the_post()`) and conditional tags (`is_single()`)

### 3. `$wpdb` - The Database Object

```php
$wpdb->prefix          // "wp_"
$wpdb->query()         // We make this return nothing
```

**When it gets set:** Before WordPress loads
**Where it comes from:** Our mock class that prevents database access
**Who uses it:** WordPress core (but we intercept it)

---

## The Flow (Simplified)

```mermaid
graph LR
    A[Backdrop Node] --> B[Create WP_Post]
    B --> C[Create WP_Query]
    C --> D[Load WordPress]
    D --> E[Run Theme Template]
    E --> F[Capture HTML]
    F --> G[Return to Backdrop]
    G --> H[Backdrop Layout]

    style A fill:#e1f5ff
    style B fill:#fff4e6
    style C fill:#fff4e6
    style D fill:#f3e5f5
    style E fill:#f3e5f5
    style F fill:#fff4e6
    style G fill:#fff4e6
    style H fill:#e1f5ff
```

**Step by step:**

1. **Backdrop loads the content** - Gets node from database
2. **We transform it** - Turn Backdrop node into WordPress `WP_Post` object
3. **We set up the query** - Put that post into `$wp_query->posts` array
4. **We load WordPress** - Just the files needed for themes
5. **Theme runs** - Calls `the_title()`, `the_content()`, etc.
6. **We capture the output** - Grab the HTML WordPress generated

---

## How Data Flows

### From Backdrop to WordPress

```php
// Backdrop has this:
$node = node_load(123);
$node->title = "My Blog Post";
$node->body = "The content...";

// We transform to this:
$post = new WP_Post([
  'ID' => $node->nid,              // 123
  'post_title' => $node->title,    // "My Blog Post"
  'post_content' => $node->body,   // "The content..."
]);

// We put it in the query:
$wp_query->posts = [$post];
$wp_query->is_single = true;
```

### From WordPress to HTML

```php
// The Loop runs:
while (have_posts()) {     // Checks if $wp_query has posts
  the_post();              // Sets global $post
  the_title();             // Echoes $post->post_title
  the_content();           // Echoes $post->post_content
}

// Output buffer captures:
// <h1>My Blog Post</h1>
// <p>The content...</p>
```

---

## The Three Key Moments

### Moment 1: Setup (Before WordPress)

**What we do:**
- Create `WP_Post` from Backdrop node
- Create `WP_Query` with that post
- Set conditional flags (`is_single`, `is_page`, etc.)
- Create mock `$wpdb` that returns nothing

**Result:** WordPress has all the data it needs in memory

### Moment 2: WordPress Loads

**What WordPress does:**
- Loads core functions
- Loads theme's `functions.php`
- Determines which template to use (`single.php`, `page.php`, etc.)

**Result:** WordPress is ready to render

### Moment 3: Template Execution

**What happens:**
- Template calls `the_post()` → Sets `$post` global
- Template calls `the_title()` → Reads from `$post->post_title`
- Template calls `the_content()` → Reads from `$post->post_content`
- All output goes to a buffer

**Result:** We have complete HTML

---

## Why This Works

**WordPress themes only care about three things:**

1. **Is there a post?** → We set `$wp_query->posts = [...]`
2. **What's the current post?** → The Loop sets `$post = ...`
3. **What's in that post?** → We populated all the properties

**WordPress never needs to:**
- Query the database (we gave it the data)
- Figure out what page we're on (we set the flags)
- Load the content (it's already in `$post`)

---

## The Magic Trick

WordPress *thinks* it's running normally:
- It has its globals set up
- It can call all its functions
- The Loop works normally
- Template tags work normally

But actually:
- All data came from Backdrop
- No database was queried
- Everything was pre-populated

It's like a stage magician's trick - WordPress doesn't know it's being controlled.

---

## Implementation Checklist

**Minimum viable setup:**

```php
// 1. Transform the data
$post = _backdrop_to_wp_post($node);

// 2. Set up the query
global $wp_query;
$wp_query = new WP_Query();
$wp_query->posts = [$post];
$wp_query->post_count = 1;
$wp_query->is_single = true;

// 3. Mock the database
global $wpdb;
$wpdb = new MockWPDB(); // Returns nothing

// 4. Load WordPress
require 'wp-includes/theme.php';
require 'wp-includes/post.php';
// ... a few more

// 5. Render
ob_start();
require 'single.php';
$html = ob_get_clean();

// 6. Return to Backdrop
return $html;
```

That's it. That's the whole system.

---

## Questions You Might Have

**Q: What if the theme needs more than one post?**
A: Put multiple posts in `$wp_query->posts` array

**Q: What about images and CSS?**
A: WordPress uses `wp_head()` and `wp_footer()` to output those - they work normally

**Q: What if WordPress tries to query the database?**
A: Our mock `$wpdb` returns empty results, so nothing breaks

**Q: How does WordPress know if it's a single post vs. a page vs. a list?**
A: We set the flags: `$wp_query->is_single`, `$wp_query->is_page`, etc.

**Q: Do we need to implement all WordPress functions?**
A: No! Only the ones that aren't already in WordPress core. Most theme functions already exist.

---

## The Beauty of This Approach

**Instead of reimplementing WordPress piece by piece:**
- ❌ Write `the_title()` function
- ❌ Write `the_content()` function
- ❌ Write `wp_head()` function
- ❌ Write hundreds more...

**We just:**
- ✅ Transform one Backdrop node to one WP_Post object
- ✅ Load actual WordPress
- ✅ Let it do its thing

**WordPress does 95% of the work for us.**
