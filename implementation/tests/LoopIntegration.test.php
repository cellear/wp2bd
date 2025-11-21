<?php
/**
 * @file
 * Integration tests for The Loop system (WP2BD-LOOP).
 *
 * Tests the complete Loop workflow with real Backdrop nodes to ensure
 * WordPress template compatibility.
 *
 * @see /home/user/wp2bd/specs/WP2BD-LOOP.md
 */

/**
 * Integration tests for The Loop system.
 *
 * These tests use BackdropWebTestCase to create real Backdrop nodes and verify
 * that the WordPress Loop functions (have_posts, the_post, wp_reset_postdata)
 * work correctly in a full Backdrop environment.
 *
 * EXPECTED BEHAVIOR (WordPress):
 * - have_posts() returns TRUE when posts remain in query
 * - the_post() increments counter and populates global $post
 * - wp_reset_postdata() restores original query state
 * - Template tags work correctly inside the loop
 * - Nested loops require wp_reset_postdata() to restore outer loop
 *
 * ACTUAL BEHAVIOR (WP2BD Implementation):
 * - Should match WordPress behavior exactly
 * - Works with Backdrop nodes converted to WP_Post objects
 * - Handles edge cases (empty query, single post, multi-page content)
 */
class LoopIntegrationTestCase extends BackdropWebTestCase {

  /**
   * Test nodes created during setUp.
   *
   * @var array
   */
  protected $testNodes = array();

  /**
   * Store original globals for cleanup.
   *
   * @var array
   */
  protected $originalGlobals = array();

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Loop Integration Tests',
      'description' => 'Test The Loop system with real Backdrop nodes.',
      'group' => 'WP2BD',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp('node', 'field', 'text');

    // Store original globals to restore later
    $this->storeOriginalGlobals();

    // Load WP2BD Loop implementation
    require_once backdrop_get_path('module', 'wp2bd') . '/includes/loop.inc';
    require_once backdrop_get_path('module', 'wp2bd') . '/includes/class-wp-post.inc';
    require_once backdrop_get_path('module', 'wp2bd') . '/includes/class-wp-query.inc';

    // Create test content type if it doesn't exist
    $this->backdropCreateContentType(array(
      'type' => 'article',
      'name' => 'Article',
    ));

    // Create a second content type for testing different node types
    $this->backdropCreateContentType(array(
      'type' => 'page',
      'name' => 'Page',
    ));

    // Create test user for authorship
    $this->testUser = $this->backdropCreateUser(array(
      'access content',
      'create article content',
      'create page content',
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    // Clean up test nodes
    foreach ($this->testNodes as $node) {
      if (isset($node->nid)) {
        node_delete($node->nid);
      }
    }

    // Restore original globals
    $this->restoreOriginalGlobals();

    parent::tearDown();
  }

  /**
   * Store original global variables.
   */
  protected function storeOriginalGlobals() {
    $globals_to_store = array(
      'wp_query',
      'post',
      'id',
      'authordata',
      'currentday',
      'currentmonth',
      'page',
      'pages',
      'multipage',
      'more',
      'numpages',
    );

    foreach ($globals_to_store as $global_name) {
      if (isset($GLOBALS[$global_name])) {
        $this->originalGlobals[$global_name] = $GLOBALS[$global_name];
      }
    }
  }

  /**
   * Restore original global variables.
   */
  protected function restoreOriginalGlobals() {
    foreach ($this->originalGlobals as $name => $value) {
      $GLOBALS[$name] = $value;
    }
  }

  /**
   * Helper to create a test node.
   *
   * @param array $settings
   *   Node settings to override defaults.
   *
   * @return object
   *   The created node.
   */
  protected function createTestNode(array $settings = array()) {
    $defaults = array(
      'type' => 'article',
      'uid' => $this->testUser->uid,
      'status' => 1, // Published
    );

    $settings = array_merge($defaults, $settings);
    $node = $this->backdropCreateNode($settings);
    $this->testNodes[] = $node;

    return $node;
  }

  /**
   * Test 1: Loop with 3 real Backdrop nodes.
   *
   * EXPECTED BEHAVIOR:
   * - Query should return all 3 published nodes
   * - Loop should iterate exactly 3 times
   * - Each iteration should populate $post with correct data
   * - have_posts() should return FALSE after loop completes
   *
   * ACTUAL BEHAVIOR:
   * - WP_Query converts Backdrop nodes to WP_Post objects
   * - Loop iteration works identically to WordPress
   */
  public function testLoopWithThreeBackdropNodes() {
    // Create 3 test nodes
    $node1 = $this->createTestNode(array(
      'title' => 'First Article',
      'body' => array(LANGUAGE_NONE => array(array(
        'value' => 'Content of first article.',
        'summary' => 'Summary of first article.',
      ))),
    ));

    $node2 = $this->createTestNode(array(
      'title' => 'Second Article',
      'body' => array(LANGUAGE_NONE => array(array(
        'value' => 'Content of second article.',
        'summary' => 'Summary of second article.',
      ))),
    ));

    $node3 = $this->createTestNode(array(
      'title' => 'Third Article',
      'body' => array(LANGUAGE_NONE => array(array(
        'value' => 'Content of third article.',
        'summary' => 'Summary of third article.',
      ))),
    ));

    // Initialize global query
    global $wp_query, $post;
    $wp_query = new WP_Query(array(
      'post_type' => 'article',
      'posts_per_page' => 3,
      'orderby' => 'date',
      'order' => 'DESC',
    ));

    // Verify query loaded posts
    $this->assertEqual($wp_query->post_count, 3, 'Query should return 3 posts.');

    // Loop through posts
    $titles = array();
    $iteration_count = 0;

    while (have_posts()) {
      the_post();
      $iteration_count++;
      $titles[] = $post->post_title;

      // Verify globals are set correctly
      $this->assertTrue(is_object($post), 'Global $post should be an object.');
      $this->assertNotNull($post->ID, 'Post ID should be set.');
      $this->assertNotNull($post->post_title, 'Post title should be set.');
    }

    // Verify loop executed correct number of times
    $this->assertEqual($iteration_count, 3, 'Loop should iterate exactly 3 times.');

    // Verify all titles were captured
    $this->assertTrue(in_array('First Article', $titles), 'First Article should be in results.');
    $this->assertTrue(in_array('Second Article', $titles), 'Second Article should be in results.');
    $this->assertTrue(in_array('Third Article', $titles), 'Third Article should be in results.');

    // Verify have_posts() returns FALSE after loop
    $this->assertFalse(have_posts(), 'have_posts() should return FALSE after loop completes.');

    // Verify counter is at expected position
    $this->assertEqual($wp_query->current_post, 2, 'Current post should be at index 2 (0-based).');
  }

  /**
   * Test 2: Nested loops with wp_reset_postdata().
   *
   * EXPECTED BEHAVIOR:
   * - Inner loop should not corrupt outer loop state
   * - wp_reset_postdata() should restore outer loop's $post
   * - Outer loop should continue correctly after inner loop
   *
   * ACTUAL BEHAVIOR:
   * - Custom queries change global $post
   * - wp_reset_postdata() restores to main query's current post
   */
  public function testNestedLoopsWithReset() {
    global $wp_query, $post;

    // Create main query nodes
    $main1 = $this->createTestNode(array('title' => 'Main Post 1'));
    $main2 = $this->createTestNode(array('title' => 'Main Post 2'));

    // Create custom query nodes (different type)
    $custom1 = $this->createTestNode(array(
      'type' => 'page',
      'title' => 'Custom Page 1',
    ));
    $custom2 = $this->createTestNode(array(
      'type' => 'page',
      'title' => 'Custom Page 2',
    ));

    // Initialize main query
    $wp_query = new WP_Query(array(
      'post_type' => 'article',
      'posts_per_page' => 2,
    ));

    $outer_titles = array();
    $inner_titles = array();

    // Outer loop
    while (have_posts()) {
      the_post();
      $outer_post_id = $post->ID;
      $outer_titles[] = $post->post_title;

      // Store outer post title for verification
      $outer_title_before_inner = $post->post_title;

      // Create nested custom query
      $custom_query = new WP_Query(array(
        'post_type' => 'page',
        'posts_per_page' => 2,
      ));

      // Inner loop
      if ($custom_query->have_posts()) {
        while ($custom_query->have_posts()) {
          $custom_query->the_post();
          $inner_titles[] = $post->post_title;

          // Verify we're in custom query
          $this->assertEqual($post->post_type, 'page', 'Inner loop should have page post type.');
        }

        // Reset to main query
        wp_reset_postdata();
      }

      // Verify we're back in outer loop with correct post
      $this->assertEqual($post->ID, $outer_post_id, 'Post ID should be restored after wp_reset_postdata().');
      $this->assertEqual($post->post_title, $outer_title_before_inner, 'Post title should be restored.');
      $this->assertEqual($post->post_type, 'article', 'Post type should be restored to article.');
    }

    // Verify both loops executed
    $this->assertEqual(count($outer_titles), 2, 'Outer loop should iterate 2 times.');
    $this->assertEqual(count($inner_titles), 4, 'Inner loop should iterate 2 times per outer iteration (2x2=4).');
  }

  /**
   * Test 3: Empty query (no posts).
   *
   * EXPECTED BEHAVIOR:
   * - have_posts() should return FALSE immediately
   * - Loop body should not execute
   * - No errors or warnings
   *
   * ACTUAL BEHAVIOR:
   * - Empty queries handled gracefully
   * - No iteration occurs
   */
  public function testEmptyQuery() {
    global $wp_query;

    // Query for non-existent post type
    $wp_query = new WP_Query(array(
      'post_type' => 'nonexistent_type',
      'posts_per_page' => 10,
    ));

    // Verify query is empty
    $this->assertEqual($wp_query->post_count, 0, 'Query should return 0 posts.');

    // Verify have_posts() returns FALSE
    $this->assertFalse(have_posts(), 'have_posts() should return FALSE for empty query.');

    // Attempt loop - should not execute
    $iteration_count = 0;
    while (have_posts()) {
      the_post();
      $iteration_count++;
    }

    $this->assertEqual($iteration_count, 0, 'Loop should not iterate with no posts.');
  }

  /**
   * Test 4: Single post.
   *
   * EXPECTED BEHAVIOR:
   * - Loop should execute exactly once
   * - All post data should be accessible
   * - have_posts() should return FALSE after one iteration
   *
   * ACTUAL BEHAVIOR:
   * - Single post queries work correctly
   * - Loop terminates after one iteration
   */
  public function testSinglePost() {
    global $wp_query, $post, $id;

    // Create single node
    $node = $this->createTestNode(array(
      'title' => 'Single Post Title',
      'body' => array(LANGUAGE_NONE => array(array(
        'value' => 'Single post content.',
        'summary' => 'Single post summary.',
      ))),
    ));

    // Query for single post by ID
    $wp_query = new WP_Query(array(
      'p' => $node->nid,
    ));

    // Verify query returned 1 post
    $this->assertEqual($wp_query->post_count, 1, 'Query should return 1 post.');

    // Loop
    $iteration_count = 0;
    while (have_posts()) {
      the_post();
      $iteration_count++;

      // Verify post data
      $this->assertEqual($post->ID, $node->nid, 'Post ID should match node ID.');
      $this->assertEqual($post->post_title, 'Single Post Title', 'Post title should be correct.');
      $this->assertEqual($post->post_content, 'Single post content.', 'Post content should be correct.');
      $this->assertEqual($post->post_excerpt, 'Single post summary.', 'Post excerpt should be correct.');

      // Verify global $id is set
      $this->assertEqual($id, $node->nid, 'Global $id should be set to post ID.');
    }

    // Verify loop executed exactly once
    $this->assertEqual($iteration_count, 1, 'Loop should iterate exactly once.');

    // Verify have_posts() returns FALSE
    $this->assertFalse(have_posts(), 'have_posts() should return FALSE after single iteration.');
  }

  /**
   * Test 5: Different node types.
   *
   * EXPECTED BEHAVIOR:
   * - Query should respect post_type parameter
   * - Only matching content type should be returned
   * - post_type property should match node type
   *
   * ACTUAL BEHAVIOR:
   * - WP_Query filters by Backdrop content type
   * - post_type correctly maps to node type
   */
  public function testDifferentNodeTypes() {
    global $wp_query, $post;

    // Create mixed content types
    $article1 = $this->createTestNode(array(
      'type' => 'article',
      'title' => 'Test Article',
    ));

    $page1 = $this->createTestNode(array(
      'type' => 'page',
      'title' => 'Test Page',
    ));

    $article2 = $this->createTestNode(array(
      'type' => 'article',
      'title' => 'Another Article',
    ));

    // Query for articles only
    $wp_query = new WP_Query(array(
      'post_type' => 'article',
      'posts_per_page' => 10,
    ));

    // Verify only articles returned
    $this->assertEqual($wp_query->post_count, 2, 'Query should return only articles.');

    // Verify all posts are articles
    while (have_posts()) {
      the_post();
      $this->assertEqual($post->post_type, 'article', 'All posts should be article type.');
      $this->assertNotEqual($post->post_title, 'Test Page', 'Page should not be in results.');
    }

    // Query for pages only
    $wp_query = new WP_Query(array(
      'post_type' => 'page',
      'posts_per_page' => 10,
    ));

    // Verify only page returned
    $this->assertEqual($wp_query->post_count, 1, 'Query should return only pages.');

    // Verify post is page
    if (have_posts()) {
      the_post();
      $this->assertEqual($post->post_type, 'page', 'Post should be page type.');
      $this->assertEqual($post->post_title, 'Test Page', 'Should return the page.');
    }

    // Query for multiple types
    $wp_query = new WP_Query(array(
      'post_type' => array('article', 'page'),
      'posts_per_page' => 10,
    ));

    // Verify both types returned
    $this->assertEqual($wp_query->post_count, 3, 'Query should return all content types.');

    $types_found = array();
    while (have_posts()) {
      the_post();
      $types_found[$post->post_type] = true;
    }

    $this->assertTrue(isset($types_found['article']), 'Should find article type.');
    $this->assertTrue(isset($types_found['page']), 'Should find page type.');
  }

  /**
   * Test 6: Multi-page content (<!--nextpage-->).
   *
   * EXPECTED BEHAVIOR:
   * - setup_postdata() should split content by <!--nextpage-->
   * - $pages array should contain each page
   * - $multipage should be TRUE
   * - $numpages should equal count of pages
   *
   * ACTUAL BEHAVIOR:
   * - Content split correctly
   * - Pagination globals set properly
   */
  public function testMultiPageContent() {
    global $wp_query, $post, $pages, $multipage, $numpages, $page;

    // Create node with multi-page content
    $multipage_content = "Page 1 content here.\n<!--nextpage-->\nPage 2 content here.\n<!--nextpage-->\nPage 3 content here.";

    $node = $this->createTestNode(array(
      'title' => 'Multi-Page Post',
      'body' => array(LANGUAGE_NONE => array(array(
        'value' => $multipage_content,
      ))),
    ));

    // Query for this post
    $wp_query = new WP_Query(array(
      'p' => $node->nid,
    ));

    // Loop and setup post data
    if (have_posts()) {
      the_post();

      // Verify $pages array is set
      $this->assertTrue(is_array($pages), '$pages should be an array.');
      $this->assertEqual(count($pages), 3, 'Should have 3 pages.');

      // Verify page content
      $this->assertTrue(strpos($pages[0], 'Page 1 content') !== FALSE, 'First page should contain page 1 content.');
      $this->assertTrue(strpos($pages[1], 'Page 2 content') !== FALSE, 'Second page should contain page 2 content.');
      $this->assertTrue(strpos($pages[2], 'Page 3 content') !== FALSE, 'Third page should contain page 3 content.');

      // Verify multipage flags
      $this->assertTrue($multipage, '$multipage should be TRUE.');
      $this->assertEqual($numpages, 3, '$numpages should be 3.');
      $this->assertEqual($page, 1, '$page should default to 1.');
    }
    else {
      $this->fail('Post should be found in query.');
    }
  }

  /**
   * Test 7: Template tags work inside the loop.
   *
   * EXPECTED BEHAVIOR:
   * - the_post() should populate globals
   * - Template tag functions should return correct values
   * - get_the_title(), get_the_ID(), etc. should work
   *
   * ACTUAL BEHAVIOR:
   * - setup_postdata() sets all required globals
   * - Template tags have access to correct data
   *
   * NOTE: This test assumes basic template tag functions exist.
   * If they don't exist yet, this test will need to check the globals directly.
   */
  public function testTemplateTagsInLoop() {
    global $wp_query, $post, $id;

    // Create test node
    $node = $this->createTestNode(array(
      'title' => 'Template Tag Test Post',
      'body' => array(LANGUAGE_NONE => array(array(
        'value' => 'This is the post content.',
        'summary' => 'This is the excerpt.',
      ))),
    ));

    // Query
    $wp_query = new WP_Query(array(
      'p' => $node->nid,
    ));

    // Loop
    if (have_posts()) {
      the_post();

      // Test global $post is set
      $this->assertTrue(is_object($post), 'Global $post should be set.');
      $this->assertEqual($post->ID, $node->nid, 'Post ID should match node ID.');

      // Test global $id is set
      $this->assertEqual($id, $node->nid, 'Global $id should be set.');

      // Test post properties are accessible
      $this->assertEqual($post->post_title, 'Template Tag Test Post', 'post_title should be accessible.');
      $this->assertEqual($post->post_content, 'This is the post content.', 'post_content should be accessible.');
      $this->assertEqual($post->post_excerpt, 'This is the excerpt.', 'post_excerpt should be accessible.');
      $this->assertEqual($post->post_type, 'article', 'post_type should be accessible.');
      $this->assertEqual($post->post_status, 'publish', 'post_status should be "publish".');

      // If template tag functions exist, test them
      if (function_exists('get_the_ID')) {
        $this->assertEqual(get_the_ID(), $node->nid, 'get_the_ID() should return correct ID.');
      }

      if (function_exists('get_the_title')) {
        $this->assertEqual(get_the_title(), 'Template Tag Test Post', 'get_the_title() should return correct title.');
      }

      // Test author data is loaded
      global $authordata;
      $this->assertTrue(is_object($authordata), 'Global $authordata should be set.');
      $this->assertEqual($authordata->uid, $node->uid, 'Author UID should match node UID.');
    }
    else {
      $this->fail('Post should be found in query.');
    }
  }

  /**
   * Test 8: Unpublished nodes excluded from query.
   *
   * EXPECTED BEHAVIOR:
   * - Unpublished posts should not appear in default queries
   * - Only status=1 (published) nodes should be returned
   *
   * ACTUAL BEHAVIOR:
   * - WP_Query filters by node status
   * - Matches WordPress post_status='publish' behavior
   */
  public function testUnpublishedNodesExcluded() {
    global $wp_query;

    // Create published node
    $published = $this->createTestNode(array(
      'title' => 'Published Post',
      'status' => 1,
    ));

    // Create unpublished node
    $unpublished = $this->createTestNode(array(
      'title' => 'Unpublished Post',
      'status' => 0,
    ));

    // Query without status filter (should return only published)
    $wp_query = new WP_Query(array(
      'post_type' => 'article',
      'posts_per_page' => 10,
    ));

    // Verify only published returned
    $this->assertEqual($wp_query->post_count, 1, 'Should return only 1 published post.');

    $titles = array();
    while (have_posts()) {
      the_post();
      $titles[] = get_the_title();
    }

    $this->assertTrue(in_array('Published Post', $titles), 'Published post should be in results.');
    $this->assertFalse(in_array('Unpublished Post', $titles), 'Unpublished post should NOT be in results.');
  }

  /**
   * Test 9: Calling the_post() without have_posts() check.
   *
   * EXPECTED BEHAVIOR:
   * - Should not cause fatal error
   * - Should handle gracefully when no posts available
   *
   * ACTUAL BEHAVIOR:
   * - Error handling prevents crashes
   * - May log warning or return early
   */
  public function testThePostWithoutHavePostsCheck() {
    global $wp_query;

    // Create empty query
    $wp_query = new WP_Query(array(
      'post_type' => 'nonexistent',
    ));

    // Call the_post() without checking have_posts()
    // This should not fatal error
    try {
      the_post();
      $this->pass('the_post() should not fatal error with empty query.');
    }
    catch (Exception $e) {
      $this->fail('the_post() should not throw exception with empty query: ' . $e->getMessage());
    }
  }

  /**
   * Test 10: Current post counter increments correctly.
   *
   * EXPECTED BEHAVIOR:
   * - current_post starts at -1 (before loop)
   * - Increments by 1 with each the_post() call
   * - Reaches post_count - 1 at loop end
   *
   * ACTUAL BEHAVIOR:
   * - Counter tracks position correctly
   * - Used by have_posts() to determine if more posts exist
   */
  public function testCurrentPostCounter() {
    global $wp_query;

    // Create 3 nodes
    $this->createTestNode(array('title' => 'Post 1'));
    $this->createTestNode(array('title' => 'Post 2'));
    $this->createTestNode(array('title' => 'Post 3'));

    // Initialize query
    $wp_query = new WP_Query(array(
      'post_type' => 'article',
      'posts_per_page' => 3,
    ));

    // Verify initial state
    $this->assertEqual($wp_query->current_post, -1, 'current_post should start at -1.');

    // First iteration
    $this->assertTrue(have_posts(), 'have_posts() should return TRUE before first iteration.');
    the_post();
    $this->assertEqual($wp_query->current_post, 0, 'current_post should be 0 after first iteration.');

    // Second iteration
    $this->assertTrue(have_posts(), 'have_posts() should return TRUE before second iteration.');
    the_post();
    $this->assertEqual($wp_query->current_post, 1, 'current_post should be 1 after second iteration.');

    // Third iteration
    $this->assertTrue(have_posts(), 'have_posts() should return TRUE before third iteration.');
    the_post();
    $this->assertEqual($wp_query->current_post, 2, 'current_post should be 2 after third iteration.');

    // After loop
    $this->assertFalse(have_posts(), 'have_posts() should return FALSE after all posts consumed.');
  }

  /**
   * Test 11: WP_Post::from_node() handles missing body field.
   *
   * EXPECTED BEHAVIOR:
   * - post_content should be empty string, not NULL
   * - post_excerpt should be empty string, not NULL
   * - No PHP warnings or errors
   *
   * ACTUAL BEHAVIOR:
   * - Gracefully handles missing field data
   * - Returns empty strings for missing content
   */
  public function testWPPostFromNodeMissingBody() {
    // Create node without body field
    $node = $this->createTestNode(array(
      'title' => 'No Body Node',
    ));

    // Manually remove body to simulate missing field
    unset($node->body);

    // Convert to WP_Post
    $post = WP_Post::from_node($node);

    // Verify content fields are empty strings, not NULL
    $this->assertIdentical($post->post_content, '', 'post_content should be empty string.');
    $this->assertIdentical($post->post_excerpt, '', 'post_excerpt should be empty string.');
    $this->assertEqual($post->post_title, 'No Body Node', 'post_title should still be set.');
  }

}
