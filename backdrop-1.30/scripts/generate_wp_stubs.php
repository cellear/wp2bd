#!/usr/bin/env php
<?php
/**
 * WordPress Function Stub Generator
 * 
 * Parses WordPress core source files and generates function stubs
 * with telemetry hooks for the WP2BD compatibility layer.
 * 
 * Usage: php generate_wp_stubs.php /path/to/wordpress/wp-includes
 */

if ($argc < 2) {
  echo "Usage: php generate_wp_stubs.php /path/to/wordpress/wp-includes\n";
  exit(1);
}

$wp_includes_dir = $argv[1];

if (!is_dir($wp_includes_dir)) {
  echo "Error: Directory not found: $wp_includes_dir\n";
  exit(1);
}

echo "Scanning WordPress core files...\n";

$functions = [];

// Scan all PHP files in wp-includes
$iterator = new RecursiveIteratorIterator(
  new RecursiveDirectoryIterator($wp_includes_dir)
);

foreach ($iterator as $file) {
  if ($file->getExtension() !== 'php') {
    continue;
  }

  $content = file_get_contents($file->getPathname());

  // Extract function definitions using regex
  preg_match_all(
    '/function\s+([a-z_][a-z0-9_]*)\s*\((.*?)\)/is',
    $content,
    $matches,
    PREG_SET_ORDER
  );

  foreach ($matches as $match) {
    $function_name = $match[1];
    $params = $match[2];

    // Skip private functions
    if (strpos($function_name, '_') === 0) {
      continue;
    }

    $functions[$function_name] = [
      'name' => $function_name,
      'params' => $params,
      'file' => basename($file->getPathname()),
    ];
  }
}

echo "Found " . count($functions) . " WordPress functions\n";
echo "Generating stubs...\n";

// Generate stub file
$output = "<?php\n";
$output .= "/**\n";
$output .= " * Auto-generated WordPress function stubs with telemetry.\n";
$output .= " * Generated: " . date('Y-m-d H:i:s') . "\n";
$output .= " * \n";
$output .= " * DO NOT EDIT MANUALLY - Regenerate with:\n";
$output .= " * php scripts/generate_wp_stubs.php /path/to/wordpress/wp-includes\n";
$output .= " */\n\n";

ksort($functions);

foreach ($functions as $func) {
  $output .= "if (!function_exists('{$func['name']}')) {\n";
  $output .= "  /**\n";
  $output .= "   * WordPress function: {$func['name']}\n";
  $output .= "   * Source: {$func['file']}\n";
  $output .= "   * @see https://developer.wordpress.org/reference/functions/{$func['name']}/\n";
  $output .= "   */\n";
  $output .= "  function {$func['name']}({$func['params']}) {\n";
  $output .= "    _wp2bd_log_stub_call(__FUNCTION__, func_get_args());\n";

  // Smart default return based on function name
  if (strpos($func['name'], 'get_') === 0) {
    $output .= "    return null;\n";
  } elseif (strpos($func['name'], 'is_') === 0 || strpos($func['name'], 'has_') === 0) {
    $output .= "    return false;\n";
  } elseif (strpos($func['name'], 'the_') === 0) {
    $output .= "    echo '';\n";
  } else {
    $output .= "    return null;\n";
  }

  $output .= "  }\n";
  $output .= "}\n\n";
}

// Add telemetry function
$output .= "/**\n";
$output .= " * Log stub function calls for telemetry.\n";
$output .= " */\n";
$output .= "function _wp2bd_log_stub_call(\$function_name, \$args) {\n";
$output .= "  if (!function_exists('db_merge')) {\n";
$output .= "    return; // Not in Backdrop context\n";
$output .= "  }\n";
$output .= "  \n";
$output .= "  static \$logged = [];\n";
$output .= "  if (isset(\$logged[\$function_name])) {\n";
$output .= "    return; // Already logged this request\n";
$output .= "  }\n";
$output .= "  \$logged[\$function_name] = TRUE;\n";
$output .= "  \n";
$output .= "  try {\n";
$output .= "    db_merge('wp2bd_stub_calls')\n";
$output .= "      ->key(['function_name' => \$function_name])\n";
$output .= "      ->fields([\n";
$output .= "        'call_count' => 1,\n";
$output .= "        'last_called' => REQUEST_TIME,\n";
$output .= "        'sample_args' => json_encode(\$args),\n";
$output .= "      ])\n";
$output .= "      ->expression('call_count', 'call_count + 1')\n";
$output .= "      ->execute();\n";
$output .= "  } catch (Exception \$e) {\n";
$output .= "    // Silently fail if table doesn't exist yet\n";
$output .= "  }\n";
$output .= "}\n";

file_put_contents('auto-stubs.php', $output);

echo "Generated auto-stubs.php with " . count($functions) . " function stubs\n";
echo "Done!\n";
