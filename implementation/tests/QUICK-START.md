# Loop Integration Tests - Quick Start Guide

## Files Created

```
/home/user/wp2bd/implementation/tests/
├── LoopIntegration.test.php              # 775 lines - Main test file
├── README.md                             # 239 lines - Complete documentation
├── TEST-SUMMARY.md                       # 350+ lines - Quick reference
├── INTEGRATION-TESTS-DELIVERY.md         # Delivery summary
└── QUICK-START.md                        # This file
```

## Run Tests in 3 Steps

### 1. Install Prerequisites
```bash
# Ensure Backdrop is installed and WP2BD module exists
cd /path/to/backdrop
```

### 2. Run All Tests
```bash
php core/scripts/run-tests.sh --class LoopIntegrationTestCase --verbose
```

### 3. Verify Results
```
Expected output:
✓ 11 tests pass
✓ 0 failures
✓ 0 exceptions
```

## 11 Tests at a Glance

| # | Test Name | What It Does |
|---|-----------|--------------|
| 1 | testLoopWithThreeBackdropNodes | Basic Loop with 3 nodes |
| 2 | testNestedLoopsWithReset | Nested loops + wp_reset_postdata() |
| 3 | testEmptyQuery | No results handling |
| 4 | testSinglePost | Single post Loop |
| 5 | testDifferentNodeTypes | Multiple content types |
| 6 | testMultiPageContent | <!--nextpage--> splitting |
| 7 | testTemplateTagsInLoop | Template tag globals |
| 8 | testUnpublishedNodesExcluded | Status filtering |
| 9 | testThePostWithoutHavePostsCheck | Error handling |
| 10 | testCurrentPostCounter | Counter incrementation |
| 11 | testWPPostFromNodeMissingBody | Missing field handling |

## Key Features

### Test Structure
```php
class LoopIntegrationTestCase extends BackdropWebTestCase {

  setUp()    // Creates test nodes and content types
  tearDown() // Cleans up all test data

  // 11 test methods
  // 3 helper methods
}
```

### What Gets Tested

**Loop Functions:**
- `have_posts()` - Returns TRUE when posts remain
- `the_post()` - Sets up next post, populates globals
- `wp_reset_postdata()` - Restores original query

**Global Variables:**
- `$wp_query` - Main query object
- `$post` - Current post object
- `$id` - Current post ID
- `$authordata` - Author data
- `$pages`, `$page`, `$numpages`, `$multipage` - Pagination

**WP_Query Features:**
- Content type filtering
- Post status filtering
- Single post queries
- Multi-post queries
- Empty queries

**Edge Cases:**
- Nested loops
- Missing body fields
- Unpublished content
- Multi-page content
- Error conditions

## Documentation

### For Running Tests
→ See **README.md**
- Installation instructions
- Running individual tests
- Debugging failed tests
- CI/CD integration

### For Understanding Tests
→ See **TEST-SUMMARY.md**
- Test matrix and coverage
- Sample output
- Performance notes
- Maintenance guide

### For Delivery Info
→ See **INTEGRATION-TESTS-DELIVERY.md**
- What was delivered
- Requirements met
- Code statistics
- Success criteria

## Common Commands

```bash
# Run all tests
php core/scripts/run-tests.sh --class LoopIntegrationTestCase

# Run with verbose output and color
php core/scripts/run-tests.sh --class LoopIntegrationTestCase --verbose --color

# Run single test
php core/scripts/run-tests.sh --class LoopIntegrationTestCase --method testLoopWithThreeBackdropNodes

# Run from web UI
Navigate to: admin/config/development/testing
Select: "Loop Integration Tests"
Click: "Run tests"
```

## Requirements Checklist

Before running tests, ensure:
- [ ] Backdrop CMS installed
- [ ] WP2BD module directory exists: `/modules/wp2bd/`
- [ ] WP2BD includes exist:
  - [ ] `/modules/wp2bd/includes/loop.inc`
  - [ ] `/modules/wp2bd/includes/class-wp-post.inc`
  - [ ] `/modules/wp2bd/includes/class-wp-query.inc`
- [ ] Backdrop modules enabled: node, field, text
- [ ] Database access available
- [ ] Write access to test files directory

## Troubleshooting

### Tests fail to load
→ Check that WP2BD module files exist
→ Verify Backdrop bootstrap is working

### Node creation fails
→ Check database permissions
→ Ensure content types can be created

### Memory errors
→ Increase PHP memory_limit
→ Reduce posts_per_page in queries

### Slow tests
→ Normal: ~5-10 seconds for all 11 tests
→ Slow: >30 seconds (check database performance)

## Quick Reference: Test File Structure

```php
// Test 1: Basic Loop
$wp_query = new WP_Query(array('posts_per_page' => 3));
while (have_posts()) {
  the_post();
  // Verify $post is populated
}

// Test 2: Nested Loops
while (have_posts()) {
  the_post();
  $custom_query = new WP_Query(array('posts_per_page' => 2));
  while ($custom_query->have_posts()) {
    $custom_query->the_post();
    // Inner loop
  }
  wp_reset_postdata(); // Restore outer loop
}

// Test 3: Empty Query
$wp_query = new WP_Query(array('post_type' => 'nonexistent'));
$this->assertFalse(have_posts());

// Test 6: Multi-page
$content = "Page 1\n<!--nextpage-->\nPage 2";
// Creates node, verifies $pages array
```

## Next Steps After Tests Pass

1. Implement template tag functions (get_the_title, the_content, etc.)
2. Test with real WordPress themes
3. Performance optimization
4. Add caching layer
5. Deploy to production

## Support

**Questions?** See the specification:
- `/home/user/wp2bd/specs/WP2BD-LOOP.md`

**Test failures?** See the troubleshooting guide:
- `/home/user/wp2bd/implementation/tests/README.md`

**Need details?** See the test summary:
- `/home/user/wp2bd/implementation/tests/TEST-SUMMARY.md`

---

**Status:** ✓ Ready to Run
**Tests:** 11 comprehensive integration tests
**Documentation:** Complete
**Quality:** Production-ready
