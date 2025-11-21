# Loop Integration Test Summary

## Quick Reference

### Test File Location
`/home/user/wp2bd/implementation/tests/LoopIntegration.test.php`

### Test Class
`LoopIntegrationTestCase extends BackdropWebTestCase`

---

## Test Matrix

| # | Test Method | What It Tests | Expected Behavior | Lines |
|---|-------------|---------------|-------------------|-------|
| 1 | `testLoopWithThreeBackdropNodes()` | Basic Loop with 3 nodes | Loop iterates 3 times, all titles captured, have_posts() FALSE after | ~80 |
| 2 | `testNestedLoopsWithReset()` | Nested queries + reset | Inner loop doesn't corrupt outer, wp_reset_postdata() restores state | ~70 |
| 3 | `testEmptyQuery()` | No results handling | have_posts() FALSE, loop doesn't execute, no errors | ~30 |
| 4 | `testSinglePost()` | Single post Loop | Executes exactly once, all data accessible, globals set | ~50 |
| 5 | `testDifferentNodeTypes()` | Multiple content types | Filters by post_type, returns only matching types | ~80 |
| 6 | `testMultiPageContent()` | <!--nextpage--> splitting | Content split into $pages array, pagination globals set | ~50 |
| 7 | `testTemplateTagsInLoop()` | Template tag globals | $post, $id, $authordata set correctly, tags work | ~60 |
| 8 | `testUnpublishedNodesExcluded()` | Status filtering | Only published nodes returned, unpublished excluded | ~40 |
| 9 | `testThePostWithoutHavePostsCheck()` | Error handling | No fatal error when called incorrectly | ~25 |
| 10 | `testCurrentPostCounter()` | Counter incrementation | Starts at -1, increments correctly, tracks position | ~40 |
| 11 | `testWPPostFromNodeMissingBody()` | Missing field handling | Empty strings not NULL, no warnings | ~30 |

**Total Tests:** 11
**Total Lines:** ~650+ lines

---

## Global Variables Tested

### Primary Globals
- `$wp_query` - Main query object
- `$post` - Current post object
- `$id` - Current post ID

### Template Tag Globals
- `$authordata` - Author user object
- `$pages` - Content pages array
- `$page` - Current page number
- `$numpages` - Total page count
- `$multipage` - Multi-page flag
- `$more` - Show more link flag
- `$currentday` - Current day tracker
- `$currentmonth` - Current month tracker

---

## Test Scenarios Covered

### ✓ Basic Functionality
- [x] Loop iteration with multiple posts
- [x] Empty query handling
- [x] Single post query
- [x] Counter incrementation

### ✓ Content Types
- [x] Article content type
- [x] Page content type
- [x] Multiple types in one query
- [x] Type filtering

### ✓ Content Features
- [x] Multi-page content (<!--nextpage-->)
- [x] Missing body field handling
- [x] Published vs unpublished filtering

### ✓ Advanced Features
- [x] Nested loops
- [x] Query reset (wp_reset_postdata)
- [x] Template tag integration
- [x] Global variable population

### ✓ Error Handling
- [x] the_post() without have_posts() check
- [x] Empty queries
- [x] Missing fields
- [x] No fatal errors in edge cases

---

## WP_Query Features Tested

### Query Parameters
```php
// Tested query arguments:
array(
  'post_type' => 'article',              // Content type filtering
  'posts_per_page' => 3,                  // Limit results
  'orderby' => 'date',                    // Sorting
  'order' => 'DESC',                      // Sort direction
  'p' => $node_id,                        // Single post by ID
  'post_type' => array('article', 'page') // Multiple types
)
```

### Query Properties
- `$wp_query->posts` - Array of WP_Post objects
- `$wp_query->post_count` - Total posts returned
- `$wp_query->current_post` - Current position (-1 to n-1)
- `$wp_query->post` - Current post object

### Query Methods
- `$wp_query->have_posts()` - Check if posts remain
- `$wp_query->the_post()` - Setup next post
- `$wp_query->reset_postdata()` - Reset to original

---

## WP_Post Properties Tested

### Core Properties
- `$post->ID` - Post/node ID
- `$post->post_author` - Author user ID
- `$post->post_title` - Title
- `$post->post_content` - Body content
- `$post->post_excerpt` - Summary/excerpt
- `$post->post_type` - Content type
- `$post->post_status` - publish/draft
- `$post->post_name` - Slug/alias

### Date Properties
- `$post->post_date` - Published date
- `$post->post_date_gmt` - Published date GMT
- `$post->post_modified` - Modified date
- `$post->post_modified_gmt` - Modified date GMT

### Relationship Properties
- `$post->post_parent` - Parent post ID
- `$post->comment_count` - Number of comments

---

## Edge Cases Covered

1. **No posts in query** → have_posts() FALSE, loop doesn't execute
2. **Single post** → Loop executes exactly once
3. **Empty database** → Query returns 0 posts gracefully
4. **Unpublished node** → Excluded from results (status check)
5. **Nested loops without reset** → Inner loop changes global state
6. **Nested loops WITH reset** → Outer loop state restored
7. **Calling the_post() incorrectly** → No fatal error
8. **Multi-page content** → Properly split by <!--nextpage-->
9. **Missing body field** → Empty string, not NULL
10. **Mixed content types** → Proper filtering by post_type

---

## Test Assertions Used

### Backdrop Test Assertions
- `$this->assertEqual($a, $b, $message)` - Values are equal
- `$this->assertNotEqual($a, $b, $message)` - Values are not equal
- `$this->assertTrue($condition, $message)` - Condition is TRUE
- `$this->assertFalse($condition, $message)` - Condition is FALSE
- `$this->assertIdentical($a, $b, $message)` - Values and types match
- `$this->assertNotNull($value, $message)` - Value is not NULL
- `$this->pass($message)` - Manual pass
- `$this->fail($message)` - Manual fail

---

## Sample Test Output

### Successful Run
```
Loop Integration Tests (WP2BD)
✓ Loop with three Backdrop nodes [PASS]
✓ Nested loops with reset [PASS]
✓ Empty query [PASS]
✓ Single post [PASS]
✓ Different node types [PASS]
✓ Multi-page content [PASS]
✓ Template tags in loop [PASS]
✓ Unpublished nodes excluded [PASS]
✓ The post without have_posts check [PASS]
✓ Current post counter [PASS]
✓ WP Post from node missing body [PASS]

11 tests, 0 failures, 0 exceptions
```

### Failed Test Example
```
✗ Loop with three Backdrop nodes [FAIL]
  Expected 3 posts, got 2
  File: LoopIntegration.test.php
  Line: 145

  Expected: 3
  Actual: 2
```

---

## Dependencies Required

### Backdrop Modules
- `node` - Core node system
- `field` - Field API
- `text` - Text field type

### WP2BD Implementation Files
Must exist before running tests:

1. `/modules/wp2bd/includes/loop.inc`
   - `have_posts()` function
   - `the_post()` function
   - `wp_reset_postdata()` function
   - `setup_postdata()` function

2. `/modules/wp2bd/includes/class-wp-post.inc`
   - `WP_Post` class
   - `WP_Post::from_node()` method

3. `/modules/wp2bd/includes/class-wp-query.inc`
   - `WP_Query` class
   - Query execution logic
   - `have_posts()` method
   - `the_post()` method
   - `reset_postdata()` method

---

## Running Individual Tests

```bash
# Run single test method
php core/scripts/run-tests.sh \
  --class LoopIntegrationTestCase \
  --method testLoopWithThreeBackdropNodes

# Run with verbose output
php core/scripts/run-tests.sh \
  --class LoopIntegrationTestCase \
  --verbose

# Run with color output
php core/scripts/run-tests.sh \
  --class LoopIntegrationTestCase \
  --color
```

---

## Integration Test vs Unit Test

### Why Integration Tests?

These are **integration tests** (BackdropWebTestCase) not unit tests because:

1. **Real Database** - Creates actual Backdrop nodes
2. **Full Bootstrap** - Loads entire Backdrop environment
3. **Real Queries** - Tests actual EntityFieldQuery/db_select
4. **Side Effects** - Tests global variable changes
5. **End-to-End** - Tests complete workflow from query to display

### What's NOT Tested Here

Unit-level tests (would use BackdropUnitTestCase):
- Individual WP_Post property getters/setters
- Date conversion logic in isolation
- String parsing without database
- Mock objects instead of real nodes

---

## Performance Considerations

### Test Execution Time
- Each test creates 1-3 nodes: ~0.5s per test
- Full test suite: ~5-10 seconds
- Includes database transactions and rollback

### Memory Usage
- Backdrop bootstrap: ~30MB
- Test nodes: ~1KB each
- Query results: Varies by test
- Total: ~50-100MB peak

### Optimization Tips
1. Minimize node creation in setUp()
2. Use transactions for rollback
3. Clear caches between tests
4. Reuse test data when possible

---

## Next Steps

After these tests pass:

1. ✓ WP_Post class working correctly
2. ✓ WP_Query class executing queries
3. ✓ Loop functions working with real data
4. ✓ Globals populated correctly
5. → Implement template tag functions (get_the_title, the_content, etc.)
6. → Test with actual WordPress themes
7. → Performance optimization
8. → Add caching layer

---

## Documentation References

- **Specification**: `/home/user/wp2bd/specs/WP2BD-LOOP.md`
- **Test File**: `/home/user/wp2bd/implementation/tests/LoopIntegration.test.php`
- **README**: `/home/user/wp2bd/implementation/tests/README.md`
- **Backdrop Testing**: https://docs.backdropcms.org/documentation/testing

---

## Maintenance

### When to Update Tests

1. **New WP_Query features** → Add test coverage
2. **Bug fixes** → Add regression test
3. **API changes** → Update assertions
4. **New content types** → Test type handling
5. **Performance issues** → Add performance test

### Test Review Checklist

- [ ] All assertions have meaningful messages
- [ ] setUp() creates minimal required data
- [ ] tearDown() cleans up all test data
- [ ] No hard-coded IDs or paths
- [ ] Tests are independent (can run in any order)
- [ ] Edge cases documented
- [ ] Expected vs actual behavior documented

---

**Last Updated:** 2025-11-20
**Status:** Ready for implementation
**Test Count:** 11 tests, ~650 lines
**Coverage:** Core Loop functionality + edge cases
