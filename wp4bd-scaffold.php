<?php
/**
 * WP4BD Scaffold - Proof of Concept
 *
 * This shows the basic flow with debug output at each stage.
 * Start simple, then gradually replace mocks with real data.
 */

// ============================================================================
// STAGE 1: BACKDROP LOADS NODE (Light Blue)
// ============================================================================

function wp4bd_stage1_load_backdrop_node() {
  echo "\n" . str_repeat("=", 70) . "\n";
  echo "STAGE 1: Backdrop Loads Node (Backdrop)\n";
  echo str_repeat("=", 70) . "\n";

  // MOCK: In real code, this would be node_load($nid)
  $node = (object) [
    'nid' => 123,
    'title' => 'My Awesome Blog Post',
    'body' => [
      'und' => [
        0 => [
          'value' => '<p>This is the content of my blog post. It has <strong>HTML</strong> in it.</p>'
        ]
      ]
    ],
    'created' => strtotime('2025-12-01 10:30:00'),
    'changed' => strtotime('2025-12-01 15:45:00'),
    'uid' => 1,
    'type' => 'post',
    'status' => 1,
  ];

  echo "✓ Loaded Backdrop node:\n";
  echo "  - NID: {$node->nid}\n";
  echo "  - Title: {$node->title}\n";
  echo "  - Type: {$node->type}\n";
  echo "  - Status: " . ($node->status ? 'Published' : 'Draft') . "\n";

  return $node;
}

// ============================================================================
// STAGE 2: TRANSFORM TO WP_POST (Tan - Our Code)
// ============================================================================

function wp4bd_stage2_transform_to_wp_post($node) {
  echo "\n" . str_repeat("=", 70) . "\n";
  echo "STAGE 2: Transform Backdrop Node → WP_Post (WP4BD Bridge)\n";
  echo str_repeat("=", 70) . "\n";

  // Simple WP_Post mock (we'll use real WP_Post class later)
  $post_data = [
    'ID' => $node->nid,
    'post_title' => $node->title,
    'post_content' => $node->body['und'][0]['value'],
    'post_excerpt' => substr(strip_tags($node->body['und'][0]['value']), 0, 100) . '...',
    'post_date' => date('Y-m-d H:i:s', $node->created),
    'post_date_gmt' => gmdate('Y-m-d H:i:s', $node->created),
    'post_modified' => date('Y-m-d H:i:s', $node->changed),
    'post_modified_gmt' => gmdate('Y-m-d H:i:s', $node->changed),
    'post_author' => (string) $node->uid,
    'post_name' => 'my-awesome-blog-post',
    'post_type' => 'post',
    'post_status' => $node->status ? 'publish' : 'draft',
    'comment_status' => 'open',
    'ping_status' => 'closed',
    'post_parent' => 0,
    'guid' => 'http://example.com/?p=' . $node->nid,
    'menu_order' => 0,
    'comment_count' => '0',
    'filter' => 'raw',
  ];

  // Convert to object (later we'll use: new WP_Post($post_data))
  $wp_post = (object) $post_data;

  echo "✓ Created WP_Post object:\n";
  echo "  - ID: {$wp_post->ID}\n";
  echo "  - post_title: {$wp_post->post_title}\n";
  echo "  - post_type: {$wp_post->post_type}\n";
  echo "  - post_status: {$wp_post->post_status}\n";
  echo "  - post_date: {$wp_post->post_date}\n";

  return $wp_post;
}

// ============================================================================
// STAGE 3: CREATE WP_QUERY (Tan - Our Code)
// ============================================================================

function wp4bd_stage3_create_wp_query($wp_post) {
  echo "\n" . str_repeat("=", 70) . "\n";
  echo "STAGE 3: Create WP_Query Object (WP4BD Bridge)\n";
  echo str_repeat("=", 70) . "\n";

  // Simple WP_Query mock (we'll use real WP_Query class later)
  $wp_query = (object) [
    // The posts array
    'posts' => [$wp_post],
    'post_count' => 1,
    'found_posts' => 1,
    'max_num_pages' => 1,

    // Loop state
    'current_post' => -1,
    'in_the_loop' => false,
    'post' => null,

    // Queried object
    'queried_object' => $wp_post,
    'queried_object_id' => $wp_post->ID,

    // Conditional flags (THIS IS KEY!)
    'is_single' => true,
    'is_singular' => true,
    'is_page' => false,
    'is_home' => false,
    'is_archive' => false,
    'is_category' => false,
    'is_tag' => false,
    'is_author' => false,
    'is_date' => false,
    'is_search' => false,
    'is_404' => false,
    'is_attachment' => false,
    'is_feed' => false,
    'is_paged' => false,

    // Query vars
    'query_vars' => [
      'p' => $wp_post->ID,
    ],
  ];

  echo "✓ Created WP_Query object:\n";
  echo "  - posts: " . count($wp_query->posts) . " post(s)\n";
  echo "  - post_count: {$wp_query->post_count}\n";
  echo "  - current_post: {$wp_query->current_post}\n";
  echo "  - is_single: " . ($wp_query->is_single ? 'true' : 'false') . "\n";
  echo "  - is_singular: " . ($wp_query->is_singular ? 'true' : 'false') . "\n";
  echo "  - queried_object_id: {$wp_query->queried_object_id}\n";

  return $wp_query;
}

// ============================================================================
// STAGE 4: SETUP WORDPRESS GLOBALS (Tan - Our Code)
// ============================================================================

function wp4bd_stage4_setup_globals($wp_query, $wp_post) {
  echo "\n" . str_repeat("=", 70) . "\n";
  echo "STAGE 4: Setup WordPress Globals (WP4BD Bridge)\n";
  echo str_repeat("=", 70) . "\n";

  // Set up globals
  global $GLOBALS;

  $GLOBALS['wp_query'] = $wp_query;
  $GLOBALS['wp_the_query'] = $wp_query; // Backup copy
  $GLOBALS['post'] = null; // Set later by the_post()

  // Mock wpdb
  $GLOBALS['wpdb'] = (object) [
    'prefix' => 'wp_',
    'posts' => 'wp_posts',
    'postmeta' => 'wp_postmeta',
  ];

  echo "✓ Set up WordPress globals:\n";
  echo "  - \$wp_query: " . (isset($GLOBALS['wp_query']) ? 'SET' : 'NOT SET') . "\n";
  echo "  - \$wp_the_query: " . (isset($GLOBALS['wp_the_query']) ? 'SET' : 'NOT SET') . "\n";
  echo "  - \$post: " . (isset($GLOBALS['post']) ? 'SET' : 'NULL') . "\n";
  echo "  - \$wpdb: " . (isset($GLOBALS['wpdb']) ? 'SET (mock)' : 'NOT SET') . "\n";
}

// ============================================================================
// STAGE 5: LOAD WORDPRESS (Purple - WordPress)
// ============================================================================

function wp4bd_stage5_load_wordpress() {
  echo "\n" . str_repeat("=", 70) . "\n";
  echo "STAGE 5: Load WordPress Core (WordPress Engine)\n";
  echo str_repeat("=", 70) . "\n";

  // MOCK: In real code, we'd require WordPress files
  echo "✓ WordPress files to load (MOCK - not actually loading yet):\n";
  echo "  - wp-includes/load.php\n";
  echo "  - wp-includes/formatting.php\n";
  echo "  - wp-includes/plugin.php\n";
  echo "  - wp-includes/theme.php\n";
  echo "  - wp-includes/post.php\n";
  echo "  - wp-includes/query.php\n";
  echo "  - wp-includes/link-template.php\n";
  echo "  - wp-includes/general-template.php\n";
  echo "  - wp-includes/post-template.php\n";

  echo "\n✓ WordPress constants to define:\n";
  echo "  - ABSPATH: /path/to/wordpress/\n";
  echo "  - WPINC: wp-includes\n";
  echo "  - WP_CONTENT_DIR: /path/to/wp-content\n";
}

// ============================================================================
// STAGE 6: RUN THE LOOP (Purple - WordPress Theme)
// ============================================================================

function wp4bd_stage6_run_the_loop() {
  global $wp_query, $post;

  echo "\n" . str_repeat("=", 70) . "\n";
  echo "STAGE 6: Run The WordPress Loop (WordPress Theme)\n";
  echo str_repeat("=", 70) . "\n";

  echo "\n--- Starting The Loop ---\n";

  // Mock have_posts()
  echo "\nCalling have_posts()...\n";
  $has_posts = ($wp_query->current_post + 1 < $wp_query->post_count);
  echo "  Result: " . ($has_posts ? 'TRUE' : 'FALSE') . "\n";
  echo "  (current_post: {$wp_query->current_post}, post_count: {$wp_query->post_count})\n";

  if ($has_posts) {
    // Mock the_post()
    echo "\nCalling the_post()...\n";
    $wp_query->current_post++;
    $wp_query->in_the_loop = true;
    $wp_query->post = $wp_query->posts[$wp_query->current_post];
    $post = $wp_query->post; // Set global

    echo "  ✓ Advanced to post index: {$wp_query->current_post}\n";
    echo "  ✓ Set global \$post\n";
    echo "  ✓ in_the_loop = true\n";

    // Mock template tags
    echo "\nCalling template tags...\n";

    echo "\n  the_ID():\n";
    echo "    → Reads: \$post->ID\n";
    echo "    → Output: {$post->ID}\n";

    echo "\n  the_title():\n";
    echo "    → Reads: \$post->post_title\n";
    echo "    → Output: {$post->post_title}\n";

    echo "\n  the_content():\n";
    echo "    → Reads: \$post->post_content\n";
    echo "    → Output: {$post->post_content}\n";

    echo "\n  the_date():\n";
    echo "    → Reads: \$post->post_date\n";
    echo "    → Output: " . date('F j, Y', strtotime($post->post_date)) . "\n";
  }

  // Check have_posts() again
  echo "\nCalling have_posts() again...\n";
  $has_posts = ($wp_query->current_post + 1 < $wp_query->post_count);
  echo "  Result: " . ($has_posts ? 'TRUE' : 'FALSE') . "\n";
  echo "  (current_post: {$wp_query->current_post}, post_count: {$wp_query->post_count})\n";

  echo "\n--- The Loop Complete ---\n";
}

// ============================================================================
// STAGE 7: CAPTURE HTML (Tan - Our Code)
// ============================================================================

function wp4bd_stage7_capture_html() {
  global $post;

  echo "\n" . str_repeat("=", 70) . "\n";
  echo "STAGE 7: Capture HTML Output (WP4BD Bridge)\n";
  echo str_repeat("=", 70) . "\n";

  // MOCK: Build simple HTML from the post
  $html = <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8">
  <title>{$post->post_title}</title>
</head>
<body class="post-template-default single">
  <div id="page" class="site">
    <header id="masthead">
      <h1 class="site-title">My WordPress Site</h1>
    </header>

    <main id="main" class="site-main">
      <article id="post-{$post->ID}" class="post">
        <header class="entry-header">
          <h1 class="entry-title">{$post->post_title}</h1>
          <div class="entry-meta">
            Posted on {$post->post_date}
          </div>
        </header>

        <div class="entry-content">
          {$post->post_content}
        </div>
      </article>
    </main>

    <footer id="colophon" class="site-footer">
      <p>&copy; 2025 My WordPress Site</p>
    </footer>
  </div>
</body>
</html>
HTML;

  echo "✓ Captured HTML output (" . strlen($html) . " bytes)\n";
  echo "\nHTML Preview (first 500 chars):\n";
  echo str_repeat("-", 70) . "\n";
  echo substr($html, 0, 500) . "...\n";
  echo str_repeat("-", 70) . "\n";

  return $html;
}

// ============================================================================
// STAGE 8: RETURN TO BACKDROP (Tan - Our Code)
// ============================================================================

function wp4bd_stage8_return_to_backdrop($html) {
  echo "\n" . str_repeat("=", 70) . "\n";
  echo "STAGE 8: Return to Backdrop (WP4BD Bridge)\n";
  echo str_repeat("=", 70) . "\n";

  // MOCK: In real code, this would be a Backdrop render array
  $render_array = [
    '#markup' => $html,
    '#printed' => true, // Don't wrap in Backdrop theme
  ];

  echo "✓ Created Backdrop render array\n";
  echo "  - Type: #markup\n";
  echo "  - Printed: true (bypass Backdrop theme)\n";
  echo "  - Size: " . strlen($html) . " bytes\n";

  return $render_array;
}

// ============================================================================
// STAGE 9: BACKDROP LAYOUT (Light Blue - Backdrop)
// ============================================================================

function wp4bd_stage9_backdrop_layout($render_array) {
  echo "\n" . str_repeat("=", 70) . "\n";
  echo "STAGE 9: Backdrop Layout Renders to Browser (Backdrop)\n";
  echo str_repeat("=", 70) . "\n";

  echo "✓ Backdrop processes render array\n";
  echo "✓ Sends HTTP response to browser\n";
  echo "✓ Browser receives HTML\n";

  echo "\n" . str_repeat("=", 70) . "\n";
  echo "REQUEST COMPLETE!\n";
  echo str_repeat("=", 70) . "\n";
}

// ============================================================================
// RUN THE SCAFFOLD
// ============================================================================

echo "\n\n";
echo "╔═══════════════════════════════════════════════════════════════════════╗\n";
echo "║                                                                       ║\n";
echo "║                    WP4BD SCAFFOLD - PROOF OF CONCEPT                 ║\n";
echo "║                                                                       ║\n";
echo "║  This demonstrates the complete flow with debug output at each stage ║\n";
echo "║                                                                       ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════╝\n";

// Run all stages
$node = wp4bd_stage1_load_backdrop_node();
$wp_post = wp4bd_stage2_transform_to_wp_post($node);
$wp_query = wp4bd_stage3_create_wp_query($wp_post);
wp4bd_stage4_setup_globals($wp_query, $wp_post);
wp4bd_stage5_load_wordpress();
wp4bd_stage6_run_the_loop();
$html = wp4bd_stage7_capture_html();
$render_array = wp4bd_stage8_return_to_backdrop($html);
wp4bd_stage9_backdrop_layout($render_array);

echo "\n\n";
echo "Color Key:\n";
echo "  🔵 Light Blue = Backdrop CMS\n";
echo "  🟡 Tan = WP4BD Bridge (Our Code)\n";
echo "  🟣 Purple = WordPress Engine\n";
echo "\n";
