<?php
/**
 * @file
 * WP4BD V2 Debug Template
 *
 * This template shows progress through the V2 implementation.
 * Updated with each Epic to show what's been accomplished.
 * 
 * Completed Epics:
 * - Epic 1: Debug Infrastructure (WP4BD-V2-001, V2-002) ✅
 * - Epic 2: WordPress Core Setup (WP4BD-V2-010, V2-011, V2-012) ✅
 */

// Load debug helper functions
require_once BACKDROP_ROOT . '/modules/wp_content/wp4bd_debug.inc';

// Initialize debugging
wp4bd_debug_init();

// Output a visible header immediately to confirm template is loading
?>
<div style="margin: 20px; padding: 20px; background: #d4edda; border-left: 4px solid #28a745;">
  <h1 style="margin-top: 0; color: #155724;">✅ WP4BD V2: WordPress-as-Engine Architecture</h1>
  <p><strong>Template loaded successfully!</strong> Showing progress through V2 implementation.</p>
  <p><strong>Completed:</strong> Epic 1 (Debug Infrastructure) ✅ | Epic 2 (WordPress Core Setup) ✅</p>
  <p><strong>Next:</strong> Epic 3 (Database Interception)</p>
</div>
<?php

// ============================================================================
// EPIC 1: DEBUG INFRASTRUCTURE ✅
// ============================================================================
wp4bd_debug_stage_start('Epic 1: Debug Infrastructure');

wp4bd_debug_log('Epic 1: Debug Infrastructure', 'Status', '✅ COMPLETE');
wp4bd_debug_log('Epic 1: Debug Infrastructure', 'WP4BD-V2-001', 'Debug helper functions created');
wp4bd_debug_log('Epic 1: Debug Infrastructure', 'WP4BD-V2-002', 'Debug template created');
wp4bd_debug_log('Epic 1: Debug Infrastructure', 'Helper Functions', 'wp4bd_debug_init, wp4bd_debug_log, etc.');
wp4bd_debug_log('Epic 1: Debug Infrastructure', 'Debug Level', wp4bd_debug_get_level());

wp4bd_debug_stage_end('Epic 1: Debug Infrastructure');

// ============================================================================
// EPIC 2: WORDPRESS CORE SETUP ✅
// ============================================================================
wp4bd_debug_stage_start('Epic 2: WordPress Core Setup');

wp4bd_debug_log('Epic 2: WordPress Core Setup', 'Status', '✅ COMPLETE');
wp4bd_debug_log('Epic 2: WordPress Core Setup', 'WP4BD-V2-010', 'WordPress 4.9 core copied to wpbrain/');

// Check if WordPress core exists
$wp_root = BACKDROP_ROOT . '/themes/wp/wpbrain/';
$wp_version_file = $wp_root . 'wp-includes/version.php';
if (file_exists($wp_version_file)) {
  require_once $wp_version_file;
  global $wp_version;
  wp4bd_debug_log('Epic 2: WordPress Core Setup', 'WordPress Version', isset($wp_version) ? $wp_version : 'Unknown');
  wp4bd_debug_log('Epic 2: WordPress Core Setup', 'WordPress Location', $wp_root);
}

wp4bd_debug_log('Epic 2: WordPress Core Setup', 'WP4BD-V2-011', 'Bootstrap entry point created');

// Check if bootstrap file exists
$bootstrap_file = BACKDROP_ROOT . '/modules/wp_content/includes/wp-bootstrap.php';
if (file_exists($bootstrap_file)) {
  wp4bd_debug_log('Epic 2: WordPress Core Setup', 'Bootstrap File', '✅ EXISTS: ' . basename($bootstrap_file));
}

wp4bd_debug_log('Epic 2: WordPress Core Setup', 'WP4BD-V2-012', 'wp-config-bd.php bridge created');

// Check if wp-config bridge exists
$config_file = $wp_root . 'wp-config-bd.php';
if (file_exists($config_file)) {
  wp4bd_debug_log('Epic 2: WordPress Core Setup', 'Config Bridge', '✅ EXISTS: wp-config-bd.php');
  wp4bd_debug_log('Epic 2: WordPress Core Setup', 'DB Interception', 'Placeholder credentials ready for db.php drop-in');
}

wp4bd_debug_log('Epic 2: WordPress Core Setup', 'Next Epic', 'Epic 3: Database Interception');

wp4bd_debug_stage_end('Epic 2: WordPress Core Setup');

// ============================================================================
// RENDER DEBUG OUTPUT
// ============================================================================

$debug_output = wp4bd_debug_render();
if (!empty($debug_output)) {
  print $debug_output;
} else {
  // Fallback if debug render returns empty
  print '<div style="margin: 20px; padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107;">';
  print '<h3>⚠️ Debug Output Empty</h3>';
  print '<p>Debug render returned empty. Check that wp4bd_debug_init() was called.</p>';
  print '<p>Debug level: ' . wp4bd_debug_get_level() . '</p>';
  print '</div>';
}

?>

<!-- Upcoming Milestones Sidebar -->
<div style="float: right; width: 350px; margin: 20px 20px 20px 0; padding: 15px; background: #fff9e6; border: 2px solid #f0c36d; border-radius: 4px;">
  <h3 style="margin-top: 0; color: #856404; border-bottom: 2px solid #f0c36d; padding-bottom: 10px;">
    🚀 Upcoming Milestones
  </h3>

  <!-- Epic 5: External I/O Interception -->
  <div style="margin-bottom: 15px;">
    <h4 style="color: #856404; margin: 10px 0 5px 0; font-size: 14px;">
      📡 Epic 5: External I/O Interception
    </h4>
    <ul style="margin: 5px 0; padding-left: 20px; font-size: 12px; line-height: 1.6;">
      <li><code>V2-040</code> Identify External I/O Functions</li>
      <li><code>V2-041</code> Implement I/O Interception Strategy</li>
      <li><code>V2-042</code> File Path Mapping</li>
    </ul>
  </div>

  <!-- Epic 6: Bootstrap Integration -->
  <div style="margin-bottom: 15px;">
    <h4 style="color: #856404; margin: 10px 0 5px 0; font-size: 14px;">
      🔗 Epic 6: Bootstrap Integration
    </h4>
    <ul style="margin: 5px 0; padding-left: 20px; font-size: 12px; line-height: 1.6;">
      <li><code>V2-050</code> Integration Point in Module</li>
      <li><code>V2-051</code> Bootstrap Sequence Implementation</li>
      <li><code>V2-052</code> Prevent WordPress Database Connection</li>
    </ul>
  </div>

  <!-- Epic 7: Data Structure Bridges -->
  <div style="margin-bottom: 15px;">
    <h4 style="color: #856404; margin: 10px 0 5px 0; font-size: 14px;">
      🌉 Epic 7: Data Structure Bridges
    </h4>
    <ul style="margin: 5px 0; padding-left: 20px; font-size: 12px; line-height: 1.6;">
      <li><code>V2-060</code> WordPress Post Object Bridge</li>
      <li><code>V2-061</code> WordPress User Bridge</li>
      <li><code>V2-062</code> WordPress Term/Taxonomy Bridge</li>
      <li><code>V2-063</code> WordPress Options/Settings Bridge</li>
    </ul>
  </div>

  <!-- Epic 8: Testing & Validation -->
  <div style="margin-bottom: 5px;">
    <h4 style="color: #856404; margin: 10px 0 5px 0; font-size: 14px;">
      ✅ Epic 8: Testing & Validation
    </h4>
    <ul style="margin: 5px 0; padding-left: 20px; font-size: 12px; line-height: 1.6;">
      <li><code>V2-070</code> Test WordPress Core Loads</li>
      <li><code>V2-071</code> Test Query Interception</li>
      <li><code>V2-072</code> Test Theme Rendering</li>
      <li><code>V2-073</code> Create Production Template</li>
    </ul>
  </div>

  <p style="font-size: 11px; color: #666; margin-top: 15px; padding-top: 10px; border-top: 1px solid #f0c36d;">
    <strong>Total:</strong> 4 Epics, 14 Stories<br>
    <strong>Source:</strong> DOCS/V2/jira-import-v2.csv
  </p>
</div>

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

  <h3>✅ Epic 1: Debug Infrastructure (COMPLETE)</h3>
  <ul>
    <li>✅ <strong>WP4BD-V2-001:</strong> Debug helper functions created</li>
    <li>✅ <strong>WP4BD-V2-002:</strong> Debug template created (you are here!)</li>
  </ul>

  <h3>✅ Epic 2: WordPress Core Setup (COMPLETE)</h3>
  <ul>
    <li>✅ <strong>WP4BD-V2-010:</strong> WordPress 4.9 core copied to wpbrain/</li>
    <li>✅ <strong>WP4BD-V2-011:</strong> Bootstrap entry point created (wp-bootstrap.php)</li>
    <li>✅ <strong>WP4BD-V2-012:</strong> wp-config bridge created (wp-config-bd.php)</li>
  </ul>

  <h3>🚀 What You're Seeing</h3>
  <p>This debug output shows:</p>
  <ul>
    <li><strong>Epic 1:</strong> Debug infrastructure working (stage timing, data logging)</li>
    <li><strong>Epic 2:</strong> WordPress core files in place, bootstrap ready, config bridge created</li>
    <li>Stage timing for each epic</li>
    <li>File verification (WordPress version, bootstrap, config)</li>
  </ul>

  <h3>📋 Next Steps</h3>
  <p><strong>Epic 2 is complete!</strong> Next up:</p>
  <ul>
    <li><strong>Epic 3:</strong> Database Interception (WP4BD-V2-020, V2-021, V2-022)</li>
    <li>Create db.php drop-in to intercept WordPress database calls</li>
    <li>Map WordPress queries to Backdrop database</li>
    <li>Transform Backdrop data to WordPress object format</li>
  </ul>

  <h3>📝 Implementation Notes</h3>
  <p><strong>WordPress-as-Engine Architecture:</strong> We're loading actual WordPress 4.9 
  core files as a rendering engine. Backdrop handles all data storage and retrieval, while 
  WordPress handles theme rendering. The wp-config-bd.php file has placeholder database 
  credentials that will be intercepted by the db.php drop-in in Epic 3.</p>
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

