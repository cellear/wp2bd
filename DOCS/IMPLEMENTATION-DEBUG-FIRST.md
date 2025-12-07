# WP4BD Implementation: Debug-First Approach

**Strategy:** Build with visibility into every stage before worrying about output quality.

---

## Debug Levels

We'll implement progressive debugging levels:

### Level 1: Flow Tracking
**Show:** Which stages execute and when
**Hide:** All actual data

```
✓ Stage 1: Backdrop Query (0.05s)
✓ Stage 2: Transform to WP_Post (0.02s)
✓ Stage 3: Populate WP_Query (0.01s)
✓ Stage 4: Load WordPress Core (0.15s)
✓ Stage 5: Execute Template (0.08s)
✓ Stage 6: Inject Assets (0.03s)
Total: 0.34s
```

### Level 2: Data Counts
**Show:** How much data at each stage
**Hide:** Content itself

```
Stage 1: Backdrop Query
  └─ Loaded 10 nodes
     • Node 123 (post, 2.5KB)
     • Node 124 (post, 3.1KB)
     ...

Stage 2: Transform to WP_Post
  └─ Created 10 WP_Post objects
     • WP_Post #123 (12 properties set)
     • WP_Post #124 (12 properties set)
     ...

Stage 3: Populate WP_Query
  └─ $wp_query->posts: 10 items
     $wp_query->is_home: true
     $wp_query->current_post: -1
```

### Level 3: Data Samples
**Show:** Titles, IDs, types
**Hide:** Full content

```
Stage 2: Transform to WP_Post
  └─ Node 123 → WP_Post
     • ID: 123
     • post_title: "My Blog Post"
     • post_content: 1,247 characters
     • post_date: "2025-12-01 10:30:00"
     • post_author: "1"
     • post_type: "post"
```

### Level 4: Full Data
**Show:** Everything
**Hide:** Nothing (but collapsed by default)

```
Stage 2: Transform to WP_Post [+]
  └─ Node 123 → WP_Post [expand]
     • ID: 123
     • post_title: "My Blog Post"
     • post_content: "<p>This is the full content...</p>"
     • post_excerpt: "This is the full..."
     • post_date: "2025-12-01 10:30:00"
     • post_date_gmt: "2025-12-01 18:30:00"
     • post_author: "1"
     • post_type: "post"
     • post_status: "publish"
     ... (all 21 properties)
```

---

## Implementation: Debug Output Module

### File: `backdrop-1.30/modules/wp_content/wp4bd_debug.inc`

```php
<?php
/**
 * WP4BD Debug Output System
 *
 * Provides visual debugging of data flow through the WordPress-as-Engine system.
 */

class WP4BD_Debug {
  private static $stages = [];
  private static $start_time;
  private static $level = 2; // Default to Level 2 (Data Counts)

  /**
   * Initialize debugging
   */
  public static function init() {
    self::$start_time = microtime(true);
    self::$stages = [];
  }

  /**
   * Set debug level (1-4)
   */
  public static function set_level($level) {
    self::$level = $level;
  }

  /**
   * Start a stage
   */
  public static function stage_start($name) {
    self::$stages[$name] = [
      'start' => microtime(true),
      'data' => [],
      'complete' => false,
    ];
  }

  /**
   * Log data for current stage
   */
  public static function log($stage, $key, $value) {
    if (!isset(self::$stages[$stage])) {
      self::stage_start($stage);
    }
    self::$stages[$stage]['data'][$key] = $value;
  }

  /**
   * Complete a stage
   */
  public static function stage_end($name) {
    if (isset(self::$stages[$name])) {
      self::$stages[$name]['end'] = microtime(true);
      self::$stages[$name]['duration'] = self::$stages[$name]['end'] - self::$stages[$name]['start'];
      self::$stages[$name]['complete'] = true;
    }
  }

  /**
   * Render debug output
   */
  public static function render() {
    $output = '<div class="wp4bd-debug" style="font-family: monospace; background: #f5f5f5; padding: 20px; margin: 20px 0; border: 2px solid #333;">';
    $output .= '<h2 style="margin-top: 0;">🔍 WP4BD Debug Output (Level ' . self::$level . ')</h2>';

    $total_time = microtime(true) - self::$start_time;

    // Level 1: Flow Tracking
    if (self::$level >= 1) {
      $output .= '<div style="margin-bottom: 20px;">';
      $output .= '<strong>Execution Flow:</strong><br>';

      foreach (self::$stages as $name => $stage) {
        $status = $stage['complete'] ? '✓' : '⏳';
        $duration = isset($stage['duration']) ? sprintf('%.3fs', $stage['duration']) : 'running...';

        $output .= sprintf(
          '%s %s (%s)<br>',
          $status,
          $name,
          $duration
        );
      }

      $output .= sprintf('<strong>Total: %.3fs</strong>', $total_time);
      $output .= '</div>';
    }

    // Level 2+: Stage Details
    if (self::$level >= 2) {
      foreach (self::$stages as $name => $stage) {
        $output .= self::render_stage($name, $stage);
      }
    }

    $output .= '</div>';
    return $output;
  }

  /**
   * Render a single stage's data
   */
  private static function render_stage($name, $stage) {
    $output = '<div style="margin-bottom: 15px; padding: 10px; background: white; border-left: 4px solid #0073aa;">';
    $output .= '<strong>' . htmlspecialchars($name) . '</strong><br>';

    if (empty($stage['data'])) {
      $output .= '<em style="color: #666;">No data logged</em>';
    } else {
      foreach ($stage['data'] as $key => $value) {
        $output .= self::render_value($key, $value, 1);
      }
    }

    $output .= '</div>';
    return $output;
  }

  /**
   * Render a value based on debug level
   */
  private static function render_value($key, $value, $depth = 0) {
    $indent = str_repeat('&nbsp;&nbsp;', $depth);
    $output = '';

    // Level 2: Show counts/summaries
    if (self::$level == 2) {
      if (is_array($value)) {
        $output .= sprintf(
          '%s└─ %s: %d items<br>',
          $indent,
          htmlspecialchars($key),
          count($value)
        );
      } elseif (is_object($value)) {
        $output .= sprintf(
          '%s└─ %s: %s object (%d properties)<br>',
          $indent,
          htmlspecialchars($key),
          get_class($value),
          count((array) $value)
        );
      } elseif (is_string($value)) {
        $output .= sprintf(
          '%s└─ %s: %d characters<br>',
          $indent,
          htmlspecialchars($key),
          strlen($value)
        );
      } else {
        $output .= sprintf(
          '%s└─ %s: %s<br>',
          $indent,
          htmlspecialchars($key),
          htmlspecialchars(var_export($value, true))
        );
      }
    }

    // Level 3: Show samples (titles, IDs, but not full content)
    elseif (self::$level == 3) {
      if (is_array($value)) {
        $output .= sprintf(
          '%s└─ %s: [%d items]<br>',
          $indent,
          htmlspecialchars($key),
          count($value)
        );

        // Show first few items
        $shown = 0;
        foreach ($value as $k => $v) {
          if ($shown++ >= 3) {
            $output .= sprintf('%s&nbsp;&nbsp;... and %d more<br>', $indent, count($value) - 3);
            break;
          }
          $output .= self::render_value($k, $v, $depth + 1);
        }
      } elseif (is_object($value)) {
        $output .= sprintf(
          '%s└─ %s: %s object<br>',
          $indent,
          htmlspecialchars($key),
          get_class($value)
        );

        // For WP_Post, show key properties
        if ($value instanceof WP_Post) {
          $output .= sprintf('%s&nbsp;&nbsp;• ID: %d<br>', $indent, $value->ID);
          $output .= sprintf('%s&nbsp;&nbsp;• post_title: "%s"<br>', $indent, htmlspecialchars($value->post_title));
          $output .= sprintf('%s&nbsp;&nbsp;• post_content: %d characters<br>', $indent, strlen($value->post_content));
          $output .= sprintf('%s&nbsp;&nbsp;• post_type: "%s"<br>', $indent, $value->post_type);
        }
        // For WP_Query, show key properties
        elseif ($value instanceof WP_Query) {
          $output .= sprintf('%s&nbsp;&nbsp;• posts: %d items<br>', $indent, count($value->posts));
          $output .= sprintf('%s&nbsp;&nbsp;• post_count: %d<br>', $indent, $value->post_count);
          $output .= sprintf('%s&nbsp;&nbsp;• current_post: %d<br>', $indent, $value->current_post);
          $output .= sprintf('%s&nbsp;&nbsp;• is_home: %s<br>', $indent, $value->is_home ? 'true' : 'false');
          $output .= sprintf('%s&nbsp;&nbsp;• is_single: %s<br>', $indent, $value->is_single ? 'true' : 'false');
        }
      } elseif (is_string($value) && strlen($value) > 100) {
        // Truncate long strings
        $output .= sprintf(
          '%s└─ %s: "%s..." (%d chars)<br>',
          $indent,
          htmlspecialchars($key),
          htmlspecialchars(substr($value, 0, 100)),
          strlen($value)
        );
      } else {
        $output .= sprintf(
          '%s└─ %s: %s<br>',
          $indent,
          htmlspecialchars($key),
          htmlspecialchars(print_r($value, true))
        );
      }
    }

    // Level 4: Show everything
    elseif (self::$level == 4) {
      $output .= sprintf(
        '%s└─ %s: <pre style="margin: 5px 0; padding: 5px; background: #fafafa;">%s</pre>',
        $indent,
        htmlspecialchars($key),
        htmlspecialchars(print_r($value, true))
      );
    }

    return $output;
  }

  /**
   * Quick helper to dump a variable
   */
  public static function dump($label, $var) {
    if (self::$level >= 2) {
      watchdog('wp4bd_debug', '@label: @var', [
        '@label' => $label,
        '@var' => print_r($var, true)
      ], WATCHDOG_DEBUG);
    }
  }
}
```

---

## Integration with page.tpl.php

### File: `backdrop-1.30/themes/wp/templates/page-debug.tpl.php`

```php
<?php
/**
 * Debug-first WordPress rendering template
 *
 * This template shows data flow through the system with progressive detail levels.
 * No actual output until we're confident data is flowing correctly.
 */

// Initialize debugging
require_once BACKDROP_ROOT . '/modules/wp_content/wp4bd_debug.inc';
WP4BD_Debug::init();

// Set debug level from URL parameter (1-4)
if (isset($_GET['wp4bd_debug'])) {
  WP4BD_Debug::set_level((int) $_GET['wp4bd_debug']);
}

// ============================================================================
// STAGE 1: BACKDROP QUERY
// ============================================================================
WP4BD_Debug::stage_start('Stage 1: Backdrop Query');

// Query Backdrop database
$nids = db_select('node', 'n')
  ->fields('n', ['nid'])
  ->condition('status', 1)
  ->condition('promote', 1)
  ->orderBy('sticky', 'DESC')
  ->orderBy('created', 'DESC')
  ->range(0, 10)
  ->execute()
  ->fetchCol();

WP4BD_Debug::log('Stage 1: Backdrop Query', 'Query', 'SELECT nid FROM node WHERE status=1 AND promote=1 ORDER BY sticky DESC, created DESC LIMIT 10');
WP4BD_Debug::log('Stage 1: Backdrop Query', 'Node IDs', $nids);

$nodes = node_load_multiple($nids);
WP4BD_Debug::log('Stage 1: Backdrop Query', 'Loaded Nodes', $nodes);

WP4BD_Debug::stage_end('Stage 1: Backdrop Query');

// ============================================================================
// STAGE 2: TRANSFORM TO WP_POST
// ============================================================================
WP4BD_Debug::stage_start('Stage 2: Transform Backdrop → WordPress');

// Load WordPress WP_Post class
if (!class_exists('WP_Post')) {
  require_once BACKDROP_ROOT . '/wordpress-4.9/wp-includes/class-wp-post.php';
}

$wp_posts = [];
foreach ($nodes as $node) {
  $post_data = [
    'ID' => $node->nid,
    'post_title' => $node->title,
    'post_content' => isset($node->body[LANGUAGE_NONE][0]['value']) ? $node->body[LANGUAGE_NONE][0]['value'] : '',
    'post_excerpt' => isset($node->body[LANGUAGE_NONE][0]['summary']) ? $node->body[LANGUAGE_NONE][0]['summary'] : '',
    'post_date' => date('Y-m-d H:i:s', $node->created),
    'post_date_gmt' => gmdate('Y-m-d H:i:s', $node->created),
    'post_modified' => date('Y-m-d H:i:s', $node->changed),
    'post_modified_gmt' => gmdate('Y-m-d H:i:s', $node->changed),
    'post_author' => (string) $node->uid,
    'post_type' => 'post',
    'post_status' => 'publish',
    'post_name' => isset($node->path['alias']) ? $node->path['alias'] : 'node-' . $node->nid,
    'guid' => url('node/' . $node->nid, ['absolute' => TRUE]),
    'comment_status' => 'open',
    'ping_status' => 'closed',
    'post_parent' => 0,
    'menu_order' => 0,
    'comment_count' => '0',
  ];

  $wp_post = new WP_Post((object) $post_data);
  $wp_posts[] = $wp_post;

  WP4BD_Debug::log('Stage 2: Transform Backdrop → WordPress', 'Node ' . $node->nid . ' → WP_Post', $wp_post);
}

WP4BD_Debug::log('Stage 2: Transform Backdrop → WordPress', 'Total WP_Post Objects', $wp_posts);

WP4BD_Debug::stage_end('Stage 2: Transform Backdrop → WordPress');

// ============================================================================
// STAGE 3: POPULATE WP_QUERY
// ============================================================================
WP4BD_Debug::stage_start('Stage 3: Create & Populate WP_Query');

// Load WordPress WP_Query class
if (!class_exists('WP_Query')) {
  require_once BACKDROP_ROOT . '/wordpress-4.9/wp-includes/class-wp-query.php';
}

global $wp_query, $wp_the_query, $post;

$wp_query = new WP_Query();
$wp_query->posts = $wp_posts;
$wp_query->post_count = count($wp_posts);
$wp_query->found_posts = count($wp_posts);
$wp_query->max_num_pages = 1;
$wp_query->current_post = -1;
$wp_query->in_the_loop = false;
$wp_query->is_home = true;
$wp_query->is_front_page = true;
$wp_query->is_archive = false;
$wp_query->is_single = false;
$wp_query->is_page = false;

$wp_the_query = $wp_query;
$post = null;

WP4BD_Debug::log('Stage 3: Create & Populate WP_Query', '$wp_query', $wp_query);
WP4BD_Debug::log('Stage 3: Create & Populate WP_Query', '$wp_the_query', 'Set to same instance as $wp_query');
WP4BD_Debug::log('Stage 3: Create & Populate WP_Query', '$post', 'NULL (set by the_post())');

WP4BD_Debug::stage_end('Stage 3: Create & Populate WP_Query');

// ============================================================================
// STAGE 4: LOAD WORDPRESS CORE
// ============================================================================
WP4BD_Debug::stage_start('Stage 4: Load WordPress Core Files');

// Define WordPress constants
if (!defined('ABSPATH')) {
  define('ABSPATH', BACKDROP_ROOT . '/wordpress-4.9/');
}
if (!defined('WPINC')) {
  define('WPINC', 'wp-includes');
}

WP4BD_Debug::log('Stage 4: Load WordPress Core Files', 'ABSPATH', ABSPATH);
WP4BD_Debug::log('Stage 4: Load WordPress Core Files', 'WPINC', WPINC);

$wp_files = [
  'wp-includes/query.php',
  'wp-includes/post.php',
  'wp-includes/post-template.php',
  'wp-includes/general-template.php',
  'wp-includes/link-template.php',
  'wp-includes/formatting.php',
  'wp-includes/plugin.php',
];

$loaded_files = [];
foreach ($wp_files as $file) {
  $path = ABSPATH . $file;
  if (file_exists($path)) {
    require_once $path;
    $loaded_files[] = $file . ' (' . filesize($path) . ' bytes)';
  }
}

WP4BD_Debug::log('Stage 4: Load WordPress Core Files', 'Loaded Files', $loaded_files);

WP4BD_Debug::stage_end('Stage 4: Load WordPress Core Files');

// ============================================================================
// STAGE 5: TEST THE LOOP
// ============================================================================
WP4BD_Debug::stage_start('Stage 5: Test The Loop');

$loop_iterations = [];

while (have_posts()) {
  the_post();

  $iteration = [
    'current_post' => $wp_query->current_post,
    'post_ID' => $post->ID,
    'post_title' => $post->post_title,
    'in_the_loop' => $wp_query->in_the_loop,
  ];

  $loop_iterations[] = $iteration;

  // Only show first 3 iterations to keep debug output manageable
  if (count($loop_iterations) >= 3) {
    break;
  }
}

WP4BD_Debug::log('Stage 5: Test The Loop', 'Loop Iterations', $loop_iterations);
WP4BD_Debug::log('Stage 5: Test The Loop', 'Final $wp_query->current_post', $wp_query->current_post);

// Reset for actual rendering
wp_reset_postdata();

WP4BD_Debug::stage_end('Stage 5: Test The Loop');

// ============================================================================
// RENDER DEBUG OUTPUT
// ============================================================================

print WP4BD_Debug::render();

// ============================================================================
// HELP TEXT
// ============================================================================
?>
<div style="margin: 20px; padding: 20px; background: #e7f3ff; border-left: 4px solid #0073aa;">
  <h3>🎛️ Debug Level Controls</h3>
  <p>Add <code>?wp4bd_debug=N</code> to URL to change debug level:</p>
  <ul>
    <li><a href="?wp4bd_debug=1">Level 1</a> - Flow Tracking (timing only)</li>
    <li><a href="?wp4bd_debug=2">Level 2</a> - Data Counts (current)</li>
    <li><a href="?wp4bd_debug=3">Level 3</a> - Data Samples (titles, IDs)</li>
    <li><a href="?wp4bd_debug=4">Level 4</a> - Full Data Dump</li>
  </ul>

  <h3>✅ What to Look For</h3>
  <ul>
    <li><strong>Stage 1:</strong> Did we load nodes from Backdrop?</li>
    <li><strong>Stage 2:</strong> Did nodes transform to WP_Post objects?</li>
    <li><strong>Stage 3:</strong> Is $wp_query populated with posts?</li>
    <li><strong>Stage 4:</strong> Did WordPress files load?</li>
    <li><strong>Stage 5:</strong> Does The Loop iterate correctly?</li>
  </ul>

  <h3>🚀 Next Steps</h3>
  <ol>
    <li>Verify all stages complete successfully</li>
    <li>Check timing - anything too slow?</li>
    <li>Examine data transformations - are titles correct?</li>
    <li>Once confident, move to actual template rendering</li>
  </ol>
</div>
```

---

## Usage Instructions

### Step 1: Enable Debug Template

```bash
# Copy or rename the debug template to be active
cd backdrop-1.30/themes/wp/templates/
cp page.tpl.php page.tpl.php.backup
cp page-debug.tpl.php page.tpl.php
```

### Step 2: View Debug Levels

Visit your site with different debug levels:

- `http://yoursite.com/?wp4bd_debug=1` - Flow tracking
- `http://yoursite.com/?wp4bd_debug=2` - Data counts (default)
- `http://yoursite.com/?wp4bd_debug=3` - Data samples
- `http://yoursite.com/?wp4bd_debug=4` - Full dump

### Step 3: Verify Each Stage

**Stage 1: Check nodes load**
- Do you see the correct number of nodes?
- Are the node IDs what you expect?

**Stage 2: Check transformation**
- Does each node become a WP_Post?
- Are ID, title, content populated?

**Stage 3: Check WP_Query**
- Does $wp_query->posts have the right count?
- Are the boolean flags correct (is_home, is_single)?

**Stage 4: Check WordPress loads**
- Did all files load successfully?
- Any errors?

**Stage 5: Check The Loop**
- Does it iterate the correct number of times?
- Does current_post advance properly (-1 → 0 → 1 → 2)?

### Step 4: Add More Stages

As you add functionality, add more debug stages:

```php
WP4BD_Debug::stage_start('Stage 6: Load Theme Functions');
// ... load theme
WP4BD_Debug::stage_end('Stage 6: Load Theme Functions');

WP4BD_Debug::stage_start('Stage 7: Execute Template');
// ... execute template
WP4BD_Debug::stage_end('Stage 7: Execute Template');
```

---

## Progressive Implementation

### Week 1: Verify Data Flow
- Get all 5 stages working
- Confirm data transformations
- No actual rendering yet

### Week 2: Add Template Loading
- Add Stage 6: Load theme functions.php
- Add Stage 7: Determine template file
- Still just debug output

### Week 3: Add Rendering (Hidden)
- Add Stage 8: Execute template (capture to variable)
- Show HTML character count
- Don't display HTML yet

### Week 4: Show Output
- Add toggle to show/hide rendered HTML
- Side-by-side: Debug + Output
- Still keep debug available

### Week 5: Polish
- Default to showing output
- Debug available via ?wp4bd_debug=1
- Production ready

---

## Benefits of This Approach

1. **Early Validation** - Know data is correct before rendering
2. **Clear Progress** - See each stage complete
3. **Easy Debugging** - See exactly where things break
4. **Performance Insight** - Timing shows bottlenecks
5. **Confidence** - Don't guess if data is right, see it
6. **Documentation** - Debug output becomes documentation

---

## Example Debug Output (Level 2)

```
🔍 WP4BD Debug Output (Level 2)

Execution Flow:
✓ Stage 1: Backdrop Query (0.052s)
✓ Stage 2: Transform Backdrop → WordPress (0.018s)
✓ Stage 3: Create & Populate WP_Query (0.003s)
✓ Stage 4: Load WordPress Core Files (0.142s)
✓ Stage 5: Test The Loop (0.008s)
Total: 0.223s

Stage 1: Backdrop Query
  └─ Query: 71 characters
  └─ Node IDs: 10 items
  └─ Loaded Nodes: 10 items

Stage 2: Transform Backdrop → WordPress
  └─ Node 123 → WP_Post: WP_Post object (21 properties)
  └─ Node 124 → WP_Post: WP_Post object (21 properties)
  └─ Node 125 → WP_Post: WP_Post object (21 properties)
  └─ Total WP_Post Objects: 10 items

Stage 3: Create & Populate WP_Query
  └─ $wp_query: WP_Query object (50 properties)
  └─ $wp_the_query: 42 characters
  └─ $post: NULL

Stage 4: Load WordPress Core Files
  └─ ABSPATH: 51 characters
  └─ WPINC: 11 characters
  └─ Loaded Files: 7 items

Stage 5: Test The Loop
  └─ Loop Iterations: 3 items
  └─ Final $wp_query->current_post: 2
```

---

**This approach lets us build confidence in the system before showing actual output!**
