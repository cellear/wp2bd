<?php
/**
 * @file
 * Debug-first WordPress rendering template
 *
 * This template shows data flow through the system with progressive detail levels.
 * Start with placeholder stages, then implement real data loading incrementally.
 */

// Load debug helper functions
require_once BACKDROP_ROOT . '/modules/wp_content/wp4bd_debug.inc';

// Initialize debugging
wp4bd_debug_init();

// ============================================================================
// STAGE 1: BACKDROP QUERY
// ============================================================================
wp4bd_debug_stage_start('Stage 1: Backdrop Query');

// Query promoted nodes from Backdrop database
$query = db_select('node', 'n')
  ->fields('n', ['nid', 'title', 'type', 'status', 'created', 'changed', 'uid', 'sticky'])
  ->condition('n.status', 1)  // Published only
  ->condition('n.promote', 1)  // Promoted to front page
  ->orderBy('n.sticky', 'DESC')
  ->orderBy('n.created', 'DESC')
  ->range(0, 10);  // Limit to 10 posts

wp4bd_debug_log('Stage 1: Backdrop Query', 'SQL Query', $query->__toString());

// Execute query and get node IDs
$nids = $query->execute()->fetchCol();
wp4bd_debug_log('Stage 1: Backdrop Query', 'Node IDs Found', $nids);

// Load full node objects
$nodes = node_load_multiple($nids);
wp4bd_debug_log('Stage 1: Backdrop Query', 'Loaded Nodes', $nodes);

// Log summary info
wp4bd_debug_log('Stage 1: Backdrop Query', 'Total Nodes', count($nodes));

wp4bd_debug_stage_end('Stage 1: Backdrop Query');

// ============================================================================
// STAGE 2: TRANSFORM TO WP_POST (Placeholder)
// ============================================================================
wp4bd_debug_stage_start('Stage 2: Transform Backdrop → WordPress');

// TODO: Implement in WP4BD-004
wp4bd_debug_log('Stage 2: Transform Backdrop → WordPress', 'Status', 'TODO - Not yet implemented');
wp4bd_debug_log('Stage 2: Transform Backdrop → WordPress', 'Next Ticket', 'WP4BD-004');

wp4bd_debug_stage_end('Stage 2: Transform Backdrop → WordPress');

// ============================================================================
// STAGE 3: POPULATE WP_QUERY (Placeholder)
// ============================================================================
wp4bd_debug_stage_start('Stage 3: Create & Populate WP_Query');

// TODO: Implement in WP4BD-005
wp4bd_debug_log('Stage 3: Create & Populate WP_Query', 'Status', 'TODO - Not yet implemented');
wp4bd_debug_log('Stage 3: Create & Populate WP_Query', 'Next Ticket', 'WP4BD-005');

wp4bd_debug_stage_end('Stage 3: Create & Populate WP_Query');

// ============================================================================
// STAGE 4: LOAD WORDPRESS CORE (Placeholder)
// ============================================================================
wp4bd_debug_stage_start('Stage 4: Load WordPress Core Files');

// TODO: Implement in WP4BD-006
wp4bd_debug_log('Stage 4: Load WordPress Core Files', 'Status', 'TODO - Not yet implemented');
wp4bd_debug_log('Stage 4: Load WordPress Core Files', 'Next Ticket', 'WP4BD-006');

wp4bd_debug_stage_end('Stage 4: Load WordPress Core Files');

// ============================================================================
// STAGE 5: TEST THE LOOP (Placeholder)
// ============================================================================
wp4bd_debug_stage_start('Stage 5: Test The Loop');

// TODO: Implement in WP4BD-007
wp4bd_debug_log('Stage 5: Test The Loop', 'Status', 'TODO - Not yet implemented');
wp4bd_debug_log('Stage 5: Test The Loop', 'Next Ticket', 'WP4BD-007');

wp4bd_debug_stage_end('Stage 5: Test The Loop');

// ============================================================================
// RENDER DEBUG OUTPUT
// ============================================================================

print wp4bd_debug_render();

?>

<!-- Help Text -->
<div style="margin: 20px; padding: 20px; background: #e7f3ff; border-left: 4px solid #0073aa;">
  <h3>🎛️ Debug Level Controls</h3>
  <p>Add <code>?wp4bd_debug=N</code> to URL to change debug level:</p>
  <ul>
    <li><a href="?wp4bd_debug=1">Level 1</a> - Flow Tracking (timing only)</li>
    <li><a href="?wp4bd_debug=2">Level 2</a> - Data Counts (default)</li>
    <li><a href="?wp4bd_debug=3">Level 3</a> - Data Samples (titles, IDs)</li>
    <li><a href="?wp4bd_debug=4">Level 4</a> - Full Data Dump</li>
  </ul>

  <h3>✅ Current Status</h3>
  <ul>
    <li>✅ <strong>WP4BD-001:</strong> Debug helper functions created</li>
    <li>✅ <strong>WP4BD-002:</strong> Debug template created (you are here!)</li>
    <li>⏳ <strong>WP4BD-003:</strong> Implement Stage 1 - Backdrop Query</li>
    <li>⏳ <strong>WP4BD-004:</strong> Implement Stage 2 - Transform to WP_Post</li>
    <li>⏳ <strong>WP4BD-005:</strong> Implement Stage 3 - Populate WP_Query</li>
    <li>⏳ <strong>WP4BD-006:</strong> Implement Stage 4 - Load WordPress Core</li>
    <li>⏳ <strong>WP4BD-007:</strong> Implement Stage 5 - Test The Loop</li>
  </ul>

  <h3>🚀 What to Expect</h3>
  <p>Right now you're seeing placeholder stages with TODO markers. As we implement each ticket, real data will appear in the debug output.</p>

  <h3>📋 Next Steps</h3>
  <ol>
    <li>Verify this page displays correctly</li>
    <li>Try changing debug levels (links above)</li>
    <li>Check timing - should be very fast (~0.001s per stage)</li>
    <li>Move on to WP4BD-003 to implement Stage 1</li>
  </ol>

  <h3>🔧 Implementation Progress</h3>
  <p><strong>Epic 1: Debug Infrastructure</strong> - ✅ Complete! (2/2 tickets done)</p>
  <p>Next up: <strong>Epic 2: Data Loading</strong> (0/3 tickets done)</p>
</div>

<?php
// Show some environment info for debugging
?>
<div style="margin: 20px; padding: 20px; background: #f0f0f0; border-left: 4px solid #666;">
  <h3>🔍 Environment Info</h3>
  <ul>
    <li><strong>Backdrop Root:</strong> <?php print BACKDROP_ROOT; ?></li>
    <li><strong>Current Path:</strong> <?php print current_path(); ?></li>
    <li><strong>Theme:</strong> <?php print $GLOBALS['theme_key']; ?></li>
    <li><strong>Debug Level:</strong> <?php print wp4bd_debug_get_level(); ?></li>
    <li><strong>PHP Version:</strong> <?php print PHP_VERSION; ?></li>
  </ul>
</div>
