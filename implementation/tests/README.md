# WP2BD Loop Integration Tests

## Overview

This directory contains comprehensive integration tests for The Loop system (WP2BD-LOOP). These tests verify that WordPress's core iteration mechanism works correctly with Backdrop CMS nodes.

## Test File

- **LoopIntegration.test.php** - Integration tests using BackdropWebTestCase

## Running the Tests

### Using Backdrop's Testing Framework

```bash
# Navigate to Backdrop root
cd /path/to/backdrop

# Run all WP2BD Loop tests
php core/scripts/run-tests.sh --class LoopIntegrationTestCase

# Run with verbose output
php core/scripts/run-tests.sh --class LoopIntegrationTestCase --verbose

# Run via web UI
# Navigate to: admin/config/development/testing
# Select "Loop Integration Tests" under "WP2BD" group
```

### Using PHPUnit (if configured)

```bash
phpunit /home/user/wp2bd/implementation/tests/LoopIntegration.test.php
```

## Test Coverage

### Test Methods (11 total)

1. **testLoopWithThreeBackdropNodes**
   - Tests basic Loop iteration with 3 real Backdrop nodes
   - Verifies have_posts() and the_post() work correctly
   - Confirms loop executes exactly 3 times
   - Checks that have_posts() returns FALSE after completion

2. **testNestedLoopsWithReset**
   - Tests nested Loop queries
   - Verifies wp_reset_postdata() restores outer loop state
   - Ensures inner loop doesn't corrupt outer loop
   - Tests with different content types

3. **testEmptyQuery**
   - Tests Loop behavior with no results
   - Verifies have_posts() returns FALSE immediately
   - Confirms loop body doesn't execute
   - Ensures no errors or warnings

4. **testSinglePost**
   - Tests Loop with exactly one post
   - Verifies single iteration
   - Checks all post data is accessible
   - Confirms globals are set correctly

5. **testDifferentNodeTypes**
   - Tests querying by content type
   - Verifies post_type filtering works
   - Tests multiple post types in one query
   - Ensures only matching types returned

6. **testMultiPageContent**
   - Tests multi-page content with <!--nextpage--> tag
   - Verifies content split into $pages array
   - Checks $multipage, $numpages, $page globals
   - Confirms pagination setup works correctly

7. **testTemplateTagsInLoop**
   - Tests that template tags work inside Loop
   - Verifies globals are populated: $post, $id, $authordata
   - Tests post properties are accessible
   - Checks template tag functions (if available)

8. **testUnpublishedNodesExcluded**
   - Tests that unpublished nodes don't appear in results
   - Verifies status filtering (published only)
   - Matches WordPress post_status='publish' behavior

9. **testThePostWithoutHavePostsCheck**
   - Tests error handling when the_post() called incorrectly
   - Verifies no fatal errors occur
   - Ensures graceful handling of edge case

10. **testCurrentPostCounter**
    - Tests that current_post counter increments correctly
    - Verifies initial state is -1
    - Confirms counter tracks position through loop
    - Checks final state after loop completion

11. **testWPPostFromNodeMissingBody**
    - Tests WP_Post::from_node() with missing body field
    - Verifies empty strings instead of NULL
    - Ensures no PHP warnings or errors

## Expected vs Actual Behavior

### WordPress Behavior (Expected)

```php
// Standard WordPress Loop
if (have_posts()) {
    while (have_posts()) {
        the_post();
        // Template tags work here
        the_title();
        the_content();
    }
}
```

**Key Characteristics:**
- `have_posts()` checks if `current_post + 1 < post_count`
- `the_post()` increments counter and populates globals
- `$post` global contains current post object
- `setup_postdata()` sets up template tag globals
- `wp_reset_postdata()` restores original query

### WP2BD Behavior (Actual)

The WP2BD implementation should match WordPress exactly:

1. **WP_Query Class**
   - Queries Backdrop nodes using EntityFieldQuery or db_select()
   - Converts nodes to WP_Post objects
   - Maintains post_count and current_post counters

2. **WP_Post Class**
   - Maps Backdrop node properties to WordPress post properties
   - Handles missing fields gracefully
   - Converts timestamps to WordPress date format

3. **Global Functions**
   - `have_posts()` - Wrapper for `$wp_query->have_posts()`
   - `the_post()` - Wrapper for `$wp_query->the_post()`
   - `wp_reset_postdata()` - Restores main query state

## Test Setup and Teardown

### setUp()
- Creates test content types (article, page)
- Creates test user for authorship
- Loads WP2BD implementation files
- Stores original globals for restoration

### tearDown()
- Deletes all test nodes
- Restores original global variables
- Cleans up test environment

## Dependencies

### Required Backdrop Modules
- `node` - Node system
- `field` - Field API
- `text` - Text field types

### Required WP2BD Files
- `includes/loop.inc` - Global Loop functions
- `includes/class-wp-post.inc` - WP_Post class
- `includes/class-wp-query.inc` - WP_Query class

## Success Criteria

All tests should pass with:
- ✓ No PHP fatal errors
- ✓ No PHP warnings or notices
- ✓ All assertions pass
- ✓ Globals properly set and restored
- ✓ Memory cleaned up in tearDown()

## Debugging Failed Tests

### Enable Verbose Output

```bash
php core/scripts/run-tests.sh --class LoopIntegrationTestCase --verbose --color
```

### Check Test Logs

Backdrop stores test results in:
- Database: `simpletest` table
- Files: `sites/default/files/simpletest/`

### Common Issues

1. **Missing WP2BD files**: Ensure includes/ directory exists with required classes
2. **Module not enabled**: WP2BD module must be enabled
3. **Permission issues**: Test user needs proper permissions
4. **Database state**: Tests may fail if database has existing content

## Integration with CI/CD

### GitHub Actions Example

```yaml
name: WP2BD Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup Backdrop
        run: ./scripts/setup-backdrop.sh
      - name: Run Loop Tests
        run: php core/scripts/run-tests.sh --class LoopIntegrationTestCase
```

## Related Documentation

- [WP2BD-LOOP Specification](/home/user/wp2bd/specs/WP2BD-LOOP.md)
- [Implementation Roadmap](/home/user/wp2bd/IMPLEMENTATION-ROADMAP.md)
- [Backdrop Testing Guide](https://docs.backdropcms.org/documentation/testing)

## Contributing

When adding new tests:

1. Follow Backdrop testing conventions
2. Document expected vs actual behavior
3. Clean up in tearDown()
4. Add meaningful assertion messages
5. Update this README with new test descriptions

## Questions?

Refer to the specification document for detailed implementation requirements:
- `/home/user/wp2bd/specs/WP2BD-LOOP.md`
