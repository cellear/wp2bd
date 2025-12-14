<?php
/**
 * @file
 * V2 Epic 1: Debug Infrastructure Template
 *
 * This template demonstrates the debug infrastructure works.
 * Only shows placeholder stages for Epic 1 - no real data yet.
 * 
 * Epic 1 is complete when:
 * - Debug helper functions work (WP4BD-V2-001) ✅
 * - This template shows debug output (WP4BD-V2-002) ✅
 */

// Load debug helper functions
require_once BACKDROP_ROOT . '/modules/wp_content/wp4bd_debug.inc';

// Initialize debugging
wp4bd_debug_init();

// ============================================================================
// STAGE 1: DEMONSTRATE DEBUG INFRASTRUCTURE (Placeholder)
// ============================================================================
wp4bd_debug_stage_start('Epic 1: Debug Infrastructure Test');

wp4bd_debug_log('Epic 1: Debug Infrastructure Test', 'Status', 'Debug infrastructure working!');
wp4bd_debug_log('Epic 1: Debug Infrastructure Test', 'Helper Functions', 'All functions available');
wp4bd_debug_log('Epic 1: Debug Infrastructure Test', 'Debug Level', wp4bd_debug_get_level());
wp4bd_debug_log('Epic 1: Debug Infrastructure Test', 'Next Epic', 'Epic 2: WordPress Core Setup');

wp4bd_debug_stage_end('Epic 1: Debug Infrastructure Test');

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

  <h3>✅ Epic 1 Status</h3>
  <ul>
    <li>✅ <strong>WP4BD-V2-001:</strong> Debug helper functions created</li>
    <li>✅ <strong>WP4BD-V2-002:</strong> Debug template created (you are here!)</li>
  </ul>

  <h3>🚀 What You're Seeing</h3>
  <p>This is the <strong>debug infrastructure</strong> working. You can see:</p>
  <ul>
    <li>Stage timing (should be very fast ~0.001s)</li>
    <li>Debug data logged at different levels</li>
    <li>Debug output rendering correctly</li>
  </ul>

  <h3>📋 Next Steps</h3>
  <p><strong>Epic 1 is complete!</strong> Next up:</p>
  <ul>
    <li><strong>Epic 2:</strong> WordPress Core Setup (WP4BD-V2-010, WP4BD-V2-011, WP4BD-V2-012)</li>
    <li>This will copy WordPress core and create bootstrap entry point</li>
  </ul>

  <h3>📝 Note About V1 Template</h3>
  <p>The full V1 template (<code>page-debug.tpl.php</code>) has all 9 stages implemented. 
  That's from V1 and will be useful later, but for Epic 1 we only need to prove the 
  debug infrastructure works - which this template does!</p>
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

