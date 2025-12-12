# WP4BD Implementation Tickets

**Project:** WordPress-as-Engine Architecture
**Goal:** Load real WordPress 4.9 to render themes instead of reimplementing functions

---

## Epic 1: Debug Infrastructure

### WP4BD-001: Create Debug Helper Functions
**Priority:** High
**Estimate:** 1 hour

**Description:**
Create simple debugging functions (no classes) to track data flow through the system.

**Acceptance Criteria:**
- [ ] File created: `modules/wp_content/wp4bd_debug.inc`
- [ ] Function: `wp4bd_debug_init()` - Initialize tracking
- [ ] Function: `wp4bd_debug_stage_start($name)` - Mark stage start
- [ ] Function: `wp4bd_debug_stage_end($name)` - Mark stage end
- [ ] Function: `wp4bd_debug_log($stage, $key, $value)` - Log data
- [ ] Function: `wp4bd_debug_render()` - Output HTML debug report
- [ ] Debug level controlled by `?wp4bd_debug=N` URL parameter

**Expected State After:**
- Helper functions available to include in templates
- Can track timing and data at each stage
- No visual output yet (just infrastructure)

**Files Modified:**
- `modules/wp_content/wp4bd_debug.inc` (new)

---

### WP4BD-002: Create Debug Template
**Priority:** High
**Estimate:** 30 minutes
**Depends On:** WP4BD-001

**Description:**
Create a page template that shows debug output instead of rendered content.

**Acceptance Criteria:**
- [ ] File created: `themes/wp/templates/page-debug.tpl.php`
- [ ] Includes wp4bd_debug.inc
- [ ] Shows placeholder stages (no real data yet)
- [ ] Renders debug output with timing
- [ ] Help text explains debug levels

**Expected State After:**
- Can visit site and see debug output
- Shows "Stage 1: TODO" placeholders
- Confirms debug infrastructure works

**Files Modified:**
- `themes/wp/templates/page-debug.tpl.php` (new)

**Test:**
Visit `http://yoursite.com/?wp4bd_debug=2` and see debug output

---

## Epic 2: Data Loading (Backdrop Layer)

### WP4BD-003: Implement Stage 1 - Backdrop Query
**Priority:** High
**Estimate:** 1 hour
**Depends On:** WP4BD-002

**Description:**
Query Backdrop database for nodes and log what we loaded.

**Acceptance Criteria:**
- [ ] Query promoted nodes using `db_select()`
- [ ] Load nodes with `node_load_multiple()`
- [ ] Log query details to debug output
- [ ] Log node count and IDs
- [ ] Show timing for this stage

**Expected State After:**
- Debug shows: "Loaded 10 nodes"
- Can see node IDs: [123, 124, 125...]
- Can see timing (should be ~0.05s)

**Files Modified:**
- `themes/wp/templates/page-debug.tpl.php` (update Stage 1)

**Test:**
Debug output shows actual nodes from your Backdrop database

---

### WP4BD-004: Implement Stage 2 - Transform to WP_Post
**Priority:** High
**Estimate:** 2 hours
**Depends On:** WP4BD-003

**Description:**
Transform Backdrop nodes into real WordPress WP_Post objects.

**Acceptance Criteria:**
- [ ] Load real WP_Post class from `wordpress-4.9/wp-includes/class-wp-post.php`
- [ ] Transform each node to WP_Post object with all 21 properties
- [ ] Handle missing data gracefully (empty body, no alias, etc.)
- [ ] Log transformation success/failure
- [ ] Show sample data (title, ID, content length)

**Expected State After:**
- Debug shows: "Created 10 WP_Post objects"
- Can see: ID, post_title, content length for each
- Verify titles match Backdrop node titles

**Files Modified:**
- `themes/wp/templates/page-debug.tpl.php` (update Stage 2)

**Test:**
Debug Level 3 shows post titles matching what you see in Backdrop admin

---

### WP4BD-005: Implement Stage 3 - Populate WP_Query
**Priority:** High
**Estimate:** 1 hour
**Depends On:** WP4BD-004

**Description:**
Create real WordPress WP_Query object and populate with our posts.

**Acceptance Criteria:**
- [ ] Load real WP_Query class from `wordpress-4.9/wp-includes/class-wp-query.php`
- [ ] Create WP_Query instance
- [ ] Set posts array, post_count, current_post
- [ ] Set conditional flags (is_home, is_single, etc.)
- [ ] Set global variables: $wp_query, $wp_the_query, $post
- [ ] Log all key properties

**Expected State After:**
- Debug shows: "$wp_query created with 10 posts"
- Can see: is_home=true, current_post=-1
- Globals are set correctly

**Files Modified:**
- `themes/wp/templates/page-debug.tpl.php` (update Stage 3)

**Test:**
Debug shows $wp_query properties match expected values

---

## Epic 3: WordPress Integration

### WP4BD-006: Implement Stage 4 - Load WordPress Core
**Priority:** High
**Estimate:** 2 hours
**Depends On:** WP4BD-005

**Description:**
Load minimal WordPress core files needed for theme rendering.

**Acceptance Criteria:**
- [ ] Define WordPress constants (ABSPATH, WPINC, WP_CONTENT_DIR)
- [ ] Load ~10 core WordPress files (query.php, post.php, etc.)
- [ ] Handle file loading errors gracefully
- [ ] Log which files loaded and their sizes
- [ ] Verify no fatal errors

**Expected State After:**
- Debug shows: "Loaded 10 WordPress files (450KB total)"
- WordPress functions are available (the_title, the_content, etc.)
- No PHP errors

**Files Modified:**
- `themes/wp/templates/page-debug.tpl.php` (update Stage 4)

**Test:**
```php
var_dump(function_exists('the_title')); // Should output: bool(true)
```

---

### WP4BD-007: Implement Stage 5 - Test The Loop
**Priority:** High
**Estimate:** 1 hour
**Depends On:** WP4BD-006

**Description:**
Test that WordPress's Loop functions work with our data.

**Acceptance Criteria:**
- [ ] Call `have_posts()` and log result
- [ ] Call `the_post()` and log state changes
- [ ] Verify `$wp_query->current_post` advances correctly
- [ ] Verify global `$post` is set
- [ ] Test `wp_reset_postdata()`
- [ ] Log 3 iterations of the loop

**Expected State After:**
- Debug shows: "Loop iterated 3 times"
- Can see: current_post advancing -1 → 0 → 1 → 2
- Can see: $post->post_title for each iteration

**Files Modified:**
- `themes/wp/templates/page-debug.tpl.php` (update Stage 5)

**Test:**
Debug output shows loop iterations with correct post titles

---

## Epic 4: Database Isolation

### WP4BD-008: Create Mock wpdb Class
**Priority:** High
**Estimate:** 2 hours
**Depends On:** WP4BD-006

**Description:**
Prevent WordPress from accessing the database.

**Acceptance Criteria:**
- [ ] Create `themes/wp/wp-content/db.php`
- [ ] Implement wpdb class with all query methods
- [ ] All methods return empty results
- [ ] Log any query attempts (for debugging)
- [ ] Set required properties ($prefix, table names)

**Expected State After:**
- WordPress can't query database
- No SQL errors
- WordPress uses pre-populated data only

**Files Modified:**
- `themes/wp/wp-content/db.php` (new)

**Test:**
WordPress theme runs without database connection

---

## Epic 5: Theme Rendering

### WP4BD-009: Load Theme functions.php
**Priority:** Medium
**Estimate:** 1 hour
**Depends On:** WP4BD-007

**Description:**
Load WordPress theme's functions.php and let it register hooks.

**Acceptance Criteria:**
- [ ] Determine active theme from config
- [ ] Load theme's functions.php
- [ ] Fire `after_setup_theme` action
- [ ] Fire `wp_enqueue_scripts` action
- [ ] Log what hooks were registered
- [ ] Handle errors in functions.php

**Expected State After:**
- Theme's functions.php executes
- Debug shows hooks registered
- No fatal errors

**Files Modified:**
- `themes/wp/templates/page-debug.tpl.php` (new Stage 6)

**Test:**
Debug shows theme support added (post-thumbnails, etc.)

---

### WP4BD-010: Determine Template File
**Priority:** Medium
**Estimate:** 1 hour
**Depends On:** WP4BD-009

**Description:**
Use WordPress template hierarchy to find correct template file.

**Acceptance Criteria:**
- [ ] Check conditionals (is_home, is_single, etc.)
- [ ] Build template hierarchy (index.php, home.php, etc.)
- [ ] Find first existing template file
- [ ] Log template path
- [ ] Handle missing templates

**Expected State After:**
- Debug shows: "Using template: index.php"
- Path is correct for active theme

**Files Modified:**
- `themes/wp/templates/page-debug.tpl.php` (new Stage 7)

**Test:**
Debug shows correct template file path

---

### WP4BD-011: Execute Template (Captured)
**Priority:** Medium
**Estimate:** 2 hours
**Depends On:** WP4BD-010

**Description:**
Execute WordPress template and capture output (don't display yet).

**Acceptance Criteria:**
- [ ] Use `ob_start()` to capture output
- [ ] Include template file
- [ ] Capture with `ob_get_clean()`
- [ ] Log HTML size (character count)
- [ ] Don't display HTML yet
- [ ] Handle template errors

**Expected State After:**
- Debug shows: "Captured 12,450 characters of HTML"
- Can see HTML exists but not displayed
- No fatal errors

**Files Modified:**
- `themes/wp/templates/page-debug.tpl.php` (new Stage 8)

**Test:**
Debug shows HTML was generated (character count > 0)

---

## Epic 6: Output Integration

### WP4BD-012: Inject Backdrop Assets
**Priority:** Medium
**Estimate:** 1 hour
**Depends On:** WP4BD-011

**Description:**
Insert Backdrop's CSS, JS, and admin toolbar into WordPress HTML.

**Acceptance Criteria:**
- [ ] Get Backdrop CSS with `backdrop_get_css()`
- [ ] Get Backdrop JS with `backdrop_get_js()`
- [ ] Inject before `</head>` tag
- [ ] Inject footer JS before `</body>` tag
- [ ] Add Backdrop body classes
- [ ] Log injection points

**Expected State After:**
- Debug shows: "Injected Backdrop assets at 2 points"
- HTML contains Backdrop's CSS and JS

**Files Modified:**
- `themes/wp/templates/page-debug.tpl.php` (new Stage 9)

**Test:**
Search HTML for "backdrop.css" - should be present

---

### WP4BD-013: Display Output (Toggle)
**Priority:** Medium
**Estimate:** 30 minutes
**Depends On:** WP4BD-012

**Description:**
Add toggle to show rendered HTML alongside debug output.

**Acceptance Criteria:**
- [ ] Add `?wp4bd_show_output=1` URL parameter
- [ ] Display HTML in iframe or div when enabled
- [ ] Keep debug output visible
- [ ] Add toggle link in debug UI

**Expected State After:**
- Can see debug output AND rendered page
- Can toggle between modes

**Files Modified:**
- `themes/wp/templates/page-debug.tpl.php` (update)

**Test:**
Visit `?wp4bd_debug=2&wp4bd_show_output=1` to see both

---

## Epic 7: Function Overrides

### WP4BD-014: Override Image Functions
**Priority:** High
**Estimate:** 3 hours
**Depends On:** WP4BD-008

**Description:**
Override WordPress image functions to fetch from Backdrop.

**Acceptance Criteria:**
- [ ] `has_post_thumbnail()` - Check node->field_image
- [ ] `get_the_post_thumbnail()` - Render Backdrop image
- [ ] `get_post_thumbnail_id()` - Return file ID
- [ ] Map WordPress image sizes to Backdrop styles
- [ ] Handle missing images gracefully

**Expected State After:**
- WordPress themes can display featured images
- Images come from Backdrop's field_image

**Files Modified:**
- `themes/wp/template.php` (add override functions)

**Test:**
Theme displays featured images from Backdrop nodes

---

### WP4BD-015: Override Metadata Functions
**Priority:** High
**Estimate:** 2 hours
**Depends On:** WP4BD-008

**Description:**
Override WordPress metadata functions to fetch from Backdrop.

**Acceptance Criteria:**
- [ ] `get_post_meta()` - Map to Backdrop fields
- [ ] `update_post_meta()` - Prevent writes (read-only)
- [ ] Common meta key mappings (_thumbnail_id, etc.)
- [ ] Handle missing fields

**Expected State After:**
- WordPress can read custom field data
- Data comes from Backdrop node fields

**Files Modified:**
- `themes/wp/template.php` (add override functions)

**Test:**
`get_post_meta($post_id, 'custom_field')` returns Backdrop field value

---

### WP4BD-016: Override Taxonomy Functions
**Priority:** Medium
**Estimate:** 2 hours
**Depends On:** WP4BD-008

**Description:**
Override WordPress taxonomy functions to fetch from Backdrop.

**Acceptance Criteria:**
- [ ] `get_the_category()` - Return Backdrop taxonomy terms
- [ ] `get_the_tags()` - Return Backdrop tags
- [ ] Convert Backdrop terms to WordPress objects
- [ ] Handle missing terms

**Expected State After:**
- WordPress themes can display categories/tags
- Data comes from Backdrop taxonomy

**Files Modified:**
- `themes/wp/template.php` (add override functions)

**Test:**
Theme displays categories from Backdrop

---

## Epic 8: Production Ready

### WP4BD-017: Create Production Template
**Priority:** Medium
**Estimate:** 1 hour
**Depends On:** WP4BD-013

**Description:**
Copy debug template to production version with debug hidden by default.

**Acceptance Criteria:**
- [ ] Copy to `page.tpl.php`
- [ ] Debug hidden unless `?wp4bd_debug=N`
- [ ] Display rendered output by default
- [ ] Clean up debug code
- [ ] Add error handling

**Expected State After:**
- Site displays WordPress theme output
- Debug available when needed
- Production-ready code

**Files Modified:**
- `themes/wp/templates/page.tpl.php` (update)

**Test:**
Visit site - see rendered theme, no debug output

---

### WP4BD-018: Performance Optimization
**Priority:** Low
**Estimate:** 3 hours
**Depends On:** WP4BD-017

**Description:**
Add caching and optimize file loading.

**Acceptance Criteria:**
- [ ] Cache transformed WP_Post objects
- [ ] Cache rendered HTML output
- [ ] Lazy-load WordPress files
- [ ] Add opcode cache hints
- [ ] Benchmark before/after

**Expected State After:**
- Page load < 500ms (after first load)
- Opcode cache reduces WordPress file load time

**Files Modified:**
- Various (optimization)

**Test:**
Benchmark shows improvement

---

### WP4BD-019: Error Handling
**Priority:** Medium
**Estimate:** 2 hours
**Depends On:** WP4BD-017

**Description:**
Add comprehensive error handling and fallbacks.

**Acceptance Criteria:**
- [ ] Try/catch around template execution
- [ ] Fallback to Backdrop rendering on error
- [ ] Log errors to watchdog
- [ ] User-friendly error messages
- [ ] Don't expose internals in errors

**Expected State After:**
- Errors don't crash the site
- Fallback rendering works
- Errors logged for debugging

**Files Modified:**
- `themes/wp/templates/page.tpl.php` (add error handling)

**Test:**
Break template - site falls back gracefully

---

### WP4BD-020: Documentation
**Priority:** Low
**Estimate:** 2 hours
**Depends On:** WP4BD-019

**Description:**
Document the completed system.

**Acceptance Criteria:**
- [ ] README with installation steps
- [ ] Configuration guide
- [ ] Troubleshooting guide
- [ ] Function override examples
- [ ] Migration guide (4-region → 1-region)

**Expected State After:**
- Users can install and configure
- Developers can extend system

**Files Modified:**
- Various documentation

---

## Summary

**Total Tickets:** 20
**Estimated Time:** ~30 hours
**Epics:** 8

**Critical Path:**
1. Debug Infrastructure (WP4BD-001, WP4BD-002)
2. Data Loading (WP4BD-003 → WP4BD-005)
3. WordPress Integration (WP4BD-006 → WP4BD-007)
4. Database Isolation (WP4BD-008)
5. Function Overrides (WP4BD-014 → WP4BD-016)
6. Production Ready (WP4BD-017)

**MVP:** Tickets WP4BD-001 through WP4BD-013 = Working WordPress theme display

**Nice to Have:** WP4BD-014 through WP4BD-020 = Full feature parity

---

## Progress Tracking

- [ ] Epic 1: Debug Infrastructure (2 tickets)
- [ ] Epic 2: Data Loading (3 tickets)
- [ ] Epic 3: WordPress Integration (3 tickets)
- [ ] Epic 4: Database Isolation (1 ticket)
- [ ] Epic 5: Theme Rendering (3 tickets)
- [ ] Epic 6: Output Integration (2 tickets)
- [ ] Epic 7: Function Overrides (3 tickets)
- [ ] Epic 8: Production Ready (3 tickets)
