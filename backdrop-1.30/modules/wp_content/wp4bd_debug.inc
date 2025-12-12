<?php
/**
 * @file
 * WP4BD Debug Helper Functions
 *
 * Simple debugging functions to track data flow through the WordPress-as-Engine system.
 * No classes - just plain functions with global state.
 */

/**
 * Initialize debug tracking.
 *
 * Call this at the start of page rendering.
 */
function wp4bd_debug_init() {
  global $wp4bd_debug;

  $wp4bd_debug = [
    'start_time' => microtime(true),
    'level' => 2, // Default to Level 2 (Data Counts)
    'stages' => [],
  ];

  // Check URL parameter for debug level
  if (isset($_GET['wp4bd_debug'])) {
    $level = (int) $_GET['wp4bd_debug'];
    if ($level >= 1 && $level <= 4) {
      $wp4bd_debug['level'] = $level;
    }
  }
}

/**
 * Get current debug level (1-4).
 */
function wp4bd_debug_get_level() {
  global $wp4bd_debug;
  return isset($wp4bd_debug['level']) ? $wp4bd_debug['level'] : 0;
}

/**
 * Start tracking a stage.
 *
 * @param string $name
 *   Stage name (e.g., "Stage 1: Backdrop Query")
 */
function wp4bd_debug_stage_start($name) {
  global $wp4bd_debug;

  if (!isset($wp4bd_debug)) {
    wp4bd_debug_init();
  }

  $wp4bd_debug['stages'][$name] = [
    'start' => microtime(true),
    'data' => [],
    'complete' => false,
  ];
}

/**
 * Log data for a stage.
 *
 * @param string $stage
 *   Stage name
 * @param string $key
 *   Data key/label
 * @param mixed $value
 *   Data value
 */
function wp4bd_debug_log($stage, $key, $value) {
  global $wp4bd_debug;

  if (!isset($wp4bd_debug)) {
    wp4bd_debug_init();
  }

  if (!isset($wp4bd_debug['stages'][$stage])) {
    wp4bd_debug_stage_start($stage);
  }

  $wp4bd_debug['stages'][$stage]['data'][$key] = $value;
}

/**
 * Mark a stage as complete.
 *
 * @param string $name
 *   Stage name
 */
function wp4bd_debug_stage_end($name) {
  global $wp4bd_debug;

  if (isset($wp4bd_debug['stages'][$name])) {
    $wp4bd_debug['stages'][$name]['end'] = microtime(true);
    $wp4bd_debug['stages'][$name]['duration'] =
      $wp4bd_debug['stages'][$name]['end'] -
      $wp4bd_debug['stages'][$name]['start'];
    $wp4bd_debug['stages'][$name]['complete'] = true;
  }
}

/**
 * Render debug output as HTML.
 *
 * @return string
 *   HTML debug output
 */
function wp4bd_debug_render() {
  global $wp4bd_debug;

  if (!isset($wp4bd_debug)) {
    return '';
  }

  $level = $wp4bd_debug['level'];
  $total_time = microtime(true) - $wp4bd_debug['start_time'];

  $output = '<div class="wp4bd-debug" style="font-family: monospace; background: #f5f5f5; padding: 20px; margin: 20px 0; border: 2px solid #333;">';
  $output .= '<h2 style="margin-top: 0;">🔍 WP4BD Debug Output (Level ' . $level . ')</h2>';

  // Level 1+: Flow Tracking
  if ($level >= 1) {
    $output .= '<div style="margin-bottom: 20px;">';
    $output .= '<strong>Execution Flow:</strong><br>';

    foreach ($wp4bd_debug['stages'] as $name => $stage) {
      $status = $stage['complete'] ? '✓' : '⏳';
      $duration = isset($stage['duration']) ? sprintf('%.3fs', $stage['duration']) : 'running...';

      $output .= sprintf(
        '%s %s (%s)<br>',
        $status,
        htmlspecialchars($name),
        $duration
      );
    }

    $output .= sprintf('<strong>Total: %.3fs</strong>', $total_time);
    $output .= '</div>';
  }

  // Level 2+: Stage Details
  if ($level >= 2) {
    foreach ($wp4bd_debug['stages'] as $name => $stage) {
      $output .= _wp4bd_debug_render_stage($name, $stage, $level);
    }
  }

  $output .= '</div>';
  return $output;
}

/**
 * Render a single stage's data.
 *
 * @param string $name
 *   Stage name
 * @param array $stage
 *   Stage data
 * @param int $level
 *   Debug level
 *
 * @return string
 *   HTML output
 */
function _wp4bd_debug_render_stage($name, $stage, $level) {
  $output = '<div style="margin-bottom: 15px; padding: 10px; background: white; border-left: 4px solid #0073aa;">';
  $output .= '<strong>' . htmlspecialchars($name) . '</strong><br>';

  if (empty($stage['data'])) {
    $output .= '<em style="color: #666;">No data logged</em>';
  } else {
    foreach ($stage['data'] as $key => $value) {
      $output .= _wp4bd_debug_render_value($key, $value, $level, 1);
    }
  }

  $output .= '</div>';
  return $output;
}

/**
 * Render a value based on debug level.
 *
 * @param string $key
 *   Data key
 * @param mixed $value
 *   Data value
 * @param int $level
 *   Debug level
 * @param int $depth
 *   Indentation depth
 *
 * @return string
 *   HTML output
 */
function _wp4bd_debug_render_value($key, $value, $level, $depth = 0) {
  $indent = str_repeat('&nbsp;&nbsp;', $depth);
  $output = '';

  // Level 2: Show counts/summaries
  if ($level == 2) {
    if (is_array($value)) {
      $output .= sprintf(
        '%s└─ %s: %d items<br>',
        $indent,
        htmlspecialchars($key),
        count($value)
      );
    } elseif (is_object($value)) {
      $class = get_class($value);
      $props = count((array) $value);
      $output .= sprintf(
        '%s└─ %s: %s object (%d properties)<br>',
        $indent,
        htmlspecialchars($key),
        $class,
        $props
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
  elseif ($level == 3) {
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
          $remaining = count($value) - 3;
          if ($remaining > 0) {
            $output .= sprintf('%s&nbsp;&nbsp;... and %d more<br>', $indent, $remaining);
          }
          break;
        }
        $output .= _wp4bd_debug_render_value($k, $v, $level, $depth + 1);
      }
    } elseif (is_object($value)) {
      $class = get_class($value);
      $output .= sprintf(
        '%s└─ %s: %s object<br>',
        $indent,
        htmlspecialchars($key),
        $class
      );

      // Special handling for WP_Post
      if ($class === 'WP_Post') {
        $output .= sprintf('%s&nbsp;&nbsp;• ID: %d<br>', $indent, $value->ID);
        $output .= sprintf('%s&nbsp;&nbsp;• post_title: "%s"<br>', $indent, htmlspecialchars($value->post_title));
        $output .= sprintf('%s&nbsp;&nbsp;• post_content: %d characters<br>', $indent, strlen($value->post_content));
        $output .= sprintf('%s&nbsp;&nbsp;• post_type: "%s"<br>', $indent, $value->post_type);
      }
      // Special handling for WP_Query
      elseif ($class === 'WP_Query') {
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
  elseif ($level == 4) {
    $output .= sprintf(
      '%s└─ %s:<br><pre style="margin: 5px 0; padding: 5px; background: #fafafa; overflow-x: auto;">%s</pre>',
      $indent,
      htmlspecialchars($key),
      htmlspecialchars(print_r($value, true))
    );
  }

  return $output;
}

/**
 * Quick helper to dump a variable to Backdrop's watchdog log.
 *
 * @param string $label
 *   Label for the variable
 * @param mixed $var
 *   Variable to dump
 */
function wp4bd_debug_dump($label, $var) {
  $level = wp4bd_debug_get_level();

  if ($level >= 2 && function_exists('watchdog')) {
    watchdog('wp4bd_debug', '@label: @var', [
      '@label' => $label,
      '@var' => print_r($var, true)
    ], WATCHDOG_DEBUG);
  }
}
