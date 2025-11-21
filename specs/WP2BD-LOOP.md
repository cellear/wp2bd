# WP2BD-LOOP: The Loop State Machine

**Work Package:** WP2BD-LOOP
**Priority:** P0 (HIGHEST - Required for ANY page render)
**Complexity:** MODERATE
**Estimated Time:** 12-16 hours (including tests)
**Dependencies:** None (foundational)
**Status:** Pending

---

## OVERVIEW

Implement WordPress's "The Loop" - the core iteration mechanism for displaying posts/content. This is THE most critical component - without it, no content can be displayed.

**Functions in this package:**
1. `have_posts()` - Check if posts remain in query
2. `the_post()` - Set up next post for display
3. `wp_reset_postdata()` - Reset to original query

**Why coupled:** These three functions form a state machine that must work together. They share global state and cannot be implemented independently.

---

## WORDPRESS BEHAVIOR

### The Loop Pattern

```php
if ( have_posts() ) {
    while ( have_posts() ) {
        the_post();

        // Display post content using template tags
        the_title();
        the_content();
    }
} else {
    // No posts found
}
```

### State Flow

1. **Initial State:** Query executed, results loaded
2. **have_posts():** Checks if current_post < post_count
3. **the_post():** Increments counter, sets up global $post
4. **Loop continues** until have_posts() returns false
5. **wp_reset_postdata():** Restores original query

---

## FUNCTION SPECIFICATIONS

### 1. `have_posts()`

**Signature:**
```php
function have_posts() : bool
```

**WordPress Behavior:**
- Checks if more posts exist in current query
- Accesses global `$wp_query` object
- Returns `$wp_query->current_post + 1 < $wp_query->post_count`

**Parameters:** None

**Returns:**
- `true` if more posts available
- `false` if no more posts OR no query

**Global Dependencies:**
- Reads: `$wp_query->current_post`, `$wp_query->post_count`

---

### 2. `the_post()`

**Signature:**
```php
function the_post() : void
```

**WordPress Behavior:**
- Sets up global `$post` variable with current post data
- Increments `$wp_query->current_post` counter
- Calls `setup_postdata($post)` to populate template tag globals
- Fires `the_post` action hook

**Parameters:** None

**Returns:** void

**Global Dependencies:**
- Reads: `$wp_query->posts`, `$wp_query->current_post`
- Writes: `$post`, `$wp_query->current_post`, `$id`, `$authordata`, etc.

**Side Effects:**
- Populates numerous globals for template tags
- Fires action hook

---

### 3. `wp_reset_postdata()`

**Signature:**
```php
function wp_reset_postdata() : void
```

**WordPress Behavior:**
- Restores global `$post` to original query
- Used after custom WP_Query loops
- Calls `setup_postdata()` with original post

**Parameters:** None

**Returns:** void

**Global Dependencies:**
- Reads: `$wp_query` (main query)
- Writes: `$post` and related globals

---

## BACKDROP MAPPING STRATEGY

### Global Objects

Create two global objects to mimic WordPress:

```php
global $wp_query;  // Main query object
global $post;      // Current post object
```

### WP_Query Mock Class

```php
class WP_Query {
    public $posts = array();           // Array of post objects
    public $post_count = 0;            // Total posts in query
    public $current_post = -1;         // Current position (-1 = before loop)
    public $post;                      // Current post object
    public $queried_object = NULL;     // The main queried object
    public $queried_object_id = NULL;  // ID of queried object

    function __construct($args = array()) {
        // Execute query based on $args
        // Populate $this->posts with results
    }

    function have_posts() {
        return ($this->current_post + 1) < $this->post_count;
    }

    function the_post() {
        $this->current_post++;
        $this->post = $this->posts[$this->current_post];

        global $post;
        $post = &$this->post;

        setup_postdata($post);

        do_action('the_post', $post, $this);
    }

    function reset_postdata() {
        if ($this->current_post > -1) {
            $this->current_post = -1;
        }
    }
}
```

### WP_Post Mock Class

```php
class WP_Post {
    public $ID;                    // Post ID (from $node->nid)
    public $post_author;           // Author ID (from $node->uid)
    public $post_date;             // Published date (from $node->created)
    public $post_date_gmt;         // GMT date
    public $post_content;          // Content (from $node->body)
    public $post_title;            // Title (from $node->title)
    public $post_excerpt;          // Excerpt (from $node->summary)
    public $post_status;           // Status (from $node->status)
    public $post_name;             // Slug (from $node->path)
    public $post_modified;         // Modified date (from $node->changed)
    public $post_modified_gmt;     // GMT modified
    public $post_parent;           // Parent ID (0 for none)
    public $post_type;             // Type (from $node->type)
    public $comment_count;         // Comment count
    public $filter;                // Filter context

    static function from_node($node) {
        $post = new WP_Post();
        $post->ID = $node->nid;
        $post->post_author = $node->uid;
        $post->post_date = date('Y-m-d H:i:s', $node->created);
        $post->post_date_gmt = gmdate('Y-m-d H:i:s', $node->created);

        // Extract body content
        if (isset($node->body[LANGUAGE_NONE][0]['value'])) {
            $post->post_content = $node->body[LANGUAGE_NONE][0]['value'];
        } else {
            $post->post_content = '';
        }

        // Extract summary/excerpt
        if (isset($node->body[LANGUAGE_NONE][0]['summary'])) {
            $post->post_excerpt = $node->body[LANGUAGE_NONE][0]['summary'];
        } else {
            $post->post_excerpt = '';
        }

        $post->post_title = $node->title;
        $post->post_status = $node->status ? 'publish' : 'draft';
        $post->post_name = isset($node->path['alias']) ? $node->path['alias'] : '';
        $post->post_modified = date('Y-m-d H:i:s', $node->changed);
        $post->post_modified_gmt = gmdate('Y-m-d H:i:s', $node->changed);
        $post->post_parent = 0;
        $post->post_type = $node->type;
        $post->comment_count = isset($node->comment_count) ? $node->comment_count : 0;
        $post->filter = 'raw';

        return $post;
    }
}
```

### setup_postdata() Function

```php
function setup_postdata($post) {
    global $id, $authordata, $currentday, $currentmonth, $page, $pages;
    global $multipage, $more, $numpages;

    if (!is_object($post)) {
        return false;
    }

    $id = (int) $post->ID;

    // Set up author data
    $authordata = get_userdata($post->post_author);

    // Set up post content variables
    $content = $post->post_content;

    // Check for multi-page content (<!--nextpage--> tag)
    if (strpos($content, '<!--nextpage-->') !== false) {
        $content = str_replace("\n<!--nextpage-->\n", '<!--nextpage-->', $content);
        $content = str_replace("\n<!--nextpage-->", '<!--nextpage-->', $content);
        $content = str_replace("<!--nextpage-->\n", '<!--nextpage-->', $content);
        $pages = explode('<!--nextpage-->', $content);
    } else {
        $pages = array($content);
    }

    $numpages = count($pages);
    $multipage = $numpages > 1;

    if (!isset($page)) {
        $page = 1;
    }

    if (!isset($more)) {
        $more = 1;
    }

    return true;
}
```

---

## INPUT ASSUMPTIONS

1. **Backdrop Environment:**
   - Node system available
   - Entity API loaded
   - Current menu router item contains node info

2. **Initial Query:**
   - Page callback provides context (view mode, display, etc.)
   - For single nodes: `menu_get_object('node')` returns loaded node
   - For listings: View or custom query provides node list

3. **Configuration:**
   - Front page setting available via `config_get()`
   - Posts per page setting available

---

## OUTPUT CONTRACT

### Global State After the_post()

```php
global $post;          // WP_Post object with current node data
global $id;            // (int) Current post ID
global $authordata;    // (object) Author user object
global $pages;         // (array) Content split by <!--nextpage-->
global $page;          // (int) Current page number
global $numpages;      // (int) Total pages
global $multipage;     // (bool) Is multi-page post
global $more;          // (bool) Show more link
```

### Return Values

- `have_posts()`: Boolean indicating if more posts exist
- `the_post()`: void, but populates globals as side effect
- `wp_reset_postdata()`: void, restores original post globals

---

## DEPENDENCIES

### Required Backdrop APIs:
- `node_load($nid)` - Load single node
- `node_load_multiple($nids)` - Load multiple nodes
- `EntityFieldQuery` or `db_select()` - Query nodes
- `user_load($uid)` - Load author data
- Menu system to detect current context

### Required WP2BD Functions:
- `do_action()` - Must be implemented (for 'the_post' hook)
- None others (this IS the foundation)

---

## IMPLEMENTATION STEPS

### Step 1: Create WP_Post Class (2 hours)
- Define all properties
- Implement `from_node()` static method
- Add getter/setter magic methods if needed
- Unit test: Convert sample node to WP_Post

### Step 2: Create WP_Query Class (4 hours)
- Implement constructor with basic args
- Implement query logic (map WP args to Backdrop queries)
- Implement `have_posts()` and `the_post()` methods
- Store posts array and counters
- Unit test: Query returns expected posts

### Step 3: Implement Global Functions (2 hours)
- `have_posts()` wrapper
- `the_post()` wrapper
- `setup_postdata()` helper
- `wp_reset_postdata()` function

### Step 4: Integration with Backdrop (2 hours)
- Hook into Backdrop's page callback system
- Initialize $wp_query based on current page context
- Handle single node view
- Handle node listings (views)

### Step 5: Testing (2-4 hours)
- Unit tests for each function
- Integration test: Loop through 3 posts
- Integration test: Nested loops with reset
- Edge case tests

---

## TEST CASES

### Unit Tests (BackdropUnitTestCase)

#### Test 1: WP_Post::from_node()
```php
function testWPPostFromNode() {
    $node = $this->createTestNode(array(
        'type' => 'article',
        'title' => 'Test Post',
        'body' => array(LANGUAGE_NONE => array(array('value' => 'Test content'))),
    ));

    $post = WP_Post::from_node($node);

    $this->assertEqual($post->ID, $node->nid);
    $this->assertEqual($post->post_title, 'Test Post');
    $this->assertEqual($post->post_content, 'Test content');
    $this->assertEqual($post->post_type, 'article');
}
```

#### Test 2: have_posts() with posts
```php
function testHavePostsWithContent() {
    global $wp_query;

    // Create mock query with 3 posts
    $wp_query = new WP_Query();
    $wp_query->posts = array(
        $this->createMockPost(1),
        $this->createMockPost(2),
        $this->createMockPost(3),
    );
    $wp_query->post_count = 3;
    $wp_query->current_post = -1;

    $this->assertTrue(have_posts(), 'have_posts() should return TRUE before loop');
}
```

#### Test 3: have_posts() empty
```php
function testHavePostsEmpty() {
    global $wp_query;

    $wp_query = new WP_Query();
    $wp_query->posts = array();
    $wp_query->post_count = 0;
    $wp_query->current_post = -1;

    $this->assertFalse(have_posts(), 'have_posts() should return FALSE with no posts');
}
```

#### Test 4: the_post() sets globals
```php
function testThePostSetsGlobals() {
    global $wp_query, $post, $id;

    $wp_query = new WP_Query();
    $wp_query->posts = array($this->createMockPost(123, 'Test Title'));
    $wp_query->post_count = 1;
    $wp_query->current_post = -1;

    the_post();

    $this->assertEqual($post->ID, 123);
    $this->assertEqual($post->post_title, 'Test Title');
    $this->assertEqual($id, 123);
    $this->assertEqual($wp_query->current_post, 0);
}
```

#### Test 5: The Loop iteration
```php
function testLoopIteration() {
    global $wp_query, $post;

    $wp_query = new WP_Query();
    $wp_query->posts = array(
        $this->createMockPost(1, 'Post 1'),
        $this->createMockPost(2, 'Post 2'),
        $this->createMockPost(3, 'Post 3'),
    );
    $wp_query->post_count = 3;
    $wp_query->current_post = -1;

    $titles = array();
    while (have_posts()) {
        the_post();
        $titles[] = $post->post_title;
    }

    $this->assertEqual($titles, array('Post 1', 'Post 2', 'Post 3'));
    $this->assertFalse(have_posts(), 'have_posts() should be FALSE after loop');
}
```

#### Test 6: wp_reset_postdata()
```php
function testResetPostdata() {
    global $wp_query, $post;

    // Set up main query with post ID 1
    $wp_query = new WP_Query();
    $wp_query->posts = array($this->createMockPost(1, 'Main Post'));
    $wp_query->post_count = 1;

    // Manually set post to different value
    $post = $this->createMockPost(999, 'Custom Post');
    $this->assertEqual($post->ID, 999);

    // Reset should restore to main query
    wp_reset_postdata();

    $this->assertEqual($post->ID, 1);
    $this->assertEqual($post->post_title, 'Main Post');
}
```

### Integration Tests (BackdropWebTestCase)

#### Test 7: Loop with real Backdrop nodes
```php
function testLoopWithBackdropNodes() {
    // Create 3 real nodes
    $node1 = $this->backdropCreateNode(array('type' => 'article', 'title' => 'Article 1'));
    $node2 = $this->backdropCreateNode(array('type' => 'article', 'title' => 'Article 2'));
    $node3 = $this->backdropCreateNode(array('type' => 'article', 'title' => 'Article 3'));

    // Initialize query
    $args = array('post_type' => 'article', 'posts_per_page' => 3);
    global $wp_query;
    $wp_query = new WP_Query($args);

    // Loop through and collect titles
    $titles = array();
    while (have_posts()) {
        the_post();
        $titles[] = get_the_title();
    }

    $this->assertEqual(count($titles), 3);
    $this->assertTrue(in_array('Article 1', $titles));
    $this->assertTrue(in_array('Article 2', $titles));
    $this->assertTrue(in_array('Article 3', $titles));
}
```

#### Test 8: Nested loops with reset
```php
function testNestedLoopsWithReset() {
    global $wp_query, $post;

    // Main query: 2 posts
    $main_query = new WP_Query(array('posts_per_page' => 2));
    $wp_query = $main_query;

    $outer_titles = array();

    while (have_posts()) {
        the_post();
        $outer_titles[] = get_the_title();

        // Nested custom query
        $custom_query = new WP_Query(array('posts_per_page' => 1));

        if ($custom_query->have_posts()) {
            while ($custom_query->have_posts()) {
                $custom_query->the_post();
                // Do something with nested post
            }
            wp_reset_postdata(); // Reset to main query
        }

        // Verify we're back in main query
        $this->assertEqual($post->ID, $main_query->posts[$main_query->current_post]->ID);
    }

    $this->assertEqual(count($outer_titles), 2);
}
```

---

## EDGE CASES

1. **No posts in query:** have_posts() returns FALSE immediately
2. **Single post:** Loop executes once
3. **Empty database:** Query returns 0 posts
4. **Unpublished node:** Should not appear in query (status check)
5. **Nested loops without reset:** Inner loop corrupts outer loop state
6. **Calling the_post() without have_posts():** Should not fatal error
7. **Multi-page content:** setup_postdata() splits by <!--nextpage-->
8. **Missing body field:** post_content should be empty string, not NULL

---

## EXAMPLE USAGE

### Basic Loop
```php
<?php
// In a template file (e.g., index.php)
if (have_posts()) {
    while (have_posts()) {
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <h2><?php the_title(); ?></h2>
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
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
// Store original query
$original_query = $wp_query;

// Create custom query
$custom_query = new WP_Query(array(
    'post_type' => 'article',
    'posts_per_page' => 5,
));

// Replace global query
$wp_query = $custom_query;

if (have_posts()) {
    while (have_posts()) {
        the_post();
        the_title('<h3>', '</h3>');
    }
}

// Restore original query
$wp_query = $original_query;
wp_reset_postdata();
?>
```

---

## INTEGRATION POINTS

### Called By:
- ALL template files (index.php, single.php, page.php, etc.)
- Template parts (content.php, etc.)

### Depends On:
- `do_action()` - For firing 'the_post' hook
- Backdrop's node_load() / EntityFieldQuery
- User loading functions (for author data)

### Provides To:
- All template tag functions (`the_title()`, `the_content()`, etc.)
- Conditional tags (`is_single()`, etc.)
- Entire theme rendering system

---

## DEFINITION OF DONE

- [ ] WP_Post class created with all properties
- [ ] WP_Post::from_node() converts Backdrop nodes correctly
- [ ] WP_Query class handles basic queries
- [ ] have_posts() returns correct boolean
- [ ] the_post() populates all required globals
- [ ] setup_postdata() sets up template tag globals
- [ ] wp_reset_postdata() restores original state
- [ ] All 8 test cases pass
- [ ] No PHP warnings or notices
- [ ] Documentation complete
- [ ] Code review approved

---

## NOTES & CONSIDERATIONS

1. **Performance:** Each call to the_post() loads author data - consider caching
2. **Memory:** Storing all posts in memory could be issue for large queries - consider pagination
3. **Compatibility:** Some WordPress functions expect specific post object properties
4. **Sticky Posts:** WordPress has "sticky" posts that appear first - need to handle separately
5. **Post Formats:** WordPress supports post formats (aside, gallery, video) - map to Backdrop taxonomy?

---

## ESTIMATED EFFORT

- **Development:** 10 hours
- **Testing:** 4 hours
- **Documentation:** 2 hours
- **Total:** 16 hours

**Complexity:** MODERATE
**Risk:** MEDIUM (foundational, many dependencies)
**Priority:** CRITICAL (P0)
