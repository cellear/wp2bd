#!/usr/bin/env php
<?php
/**
 * WordPress Function Stub Generator from Documentation
 * 
 * Parses WordPress documentation HTML files and generates function stubs.
 * 
 * Usage: php generate_stubs_from_docs.php /path/to/developer.wordpress.org/reference/functions
 * 
 * Documentation Source:
 * This script requires a local copy of the WordPress developer documentation.
 * The documentation can be downloaded from https://developer.wordpress.org/ using wget:
 * 
 *   wget --recursive --no-parent --no-host-directories \
 *        --cut-dirs=1 --accept=html \
 *        https://developer.wordpress.org/reference/functions/
 * 
 * This will create a local directory structure mirroring the online documentation.
 * The resulting directory (typically named "developer.wordpress.org/") contains
 * HTML files for each WordPress function, which this script parses to extract
 * function signatures and generate stub implementations.
 * 
 * Note: The developer.wordpress.org/ directory is NOT committed to the repository
 * (it's ~3.1GB with 31,000+ files). It should be kept locally for regeneration
 * of auto-stubs.php when needed. The generated auto-stubs.php file is what gets
 * loaded at runtime.
 */

if ($argc < 2) {
  echo "Usage: php generate_stubs_from_docs.php /path/to/developer.wordpress.org/reference/functions\n";
  exit(1);
}

$docs_dir = $argv[1];

if (!is_dir($docs_dir)) {
  echo "Error: Directory not found: $docs_dir\n";
  exit(1);
}

echo "Scanning WordPress documentation...\n";

$functions = [];
$skipped = [];

// Scan all directories in the functions folder
$dirs = scandir($docs_dir);
foreach ($dirs as $dir) {
  if ($dir === '.' || $dir === '..' || $dir === '.DS_Store') {
    continue;
  }

  $dir_path = $docs_dir . '/' . $dir;

  // Skip if it's a file (like .html files in the root)
  if (!is_dir($dir_path)) {
    continue;
  }

  // Look for index.html in the directory
  $index_file = $dir_path . '/index.html';
  if (!file_exists($index_file)) {
    $skipped[] = $dir;
    continue;
  }

  $html = file_get_contents($index_file);

  // Extract function signature from the documentation
  // Look for patterns like: function_name( $param1, $param2 = 'default' )
  if (preg_match('/<h1[^>]*>([^<(]+)\s*\((.*?)\)\s*<\/h1>/s', $html, $match)) {
    $function_name = trim($match[1]);
    $params_raw = $match[2];

    // Clean up parameters - remove HTML tags and normalize
    $params_raw = strip_tags($params_raw);
    $params_raw = html_entity_decode($params_raw, ENT_QUOTES | ENT_HTML5);
    $params_raw = preg_replace('/\s+/', ' ', $params_raw);
    $params_raw = trim($params_raw);

    // Strip ALL type hints completely
    // Split by comma to process each parameter
    $params_parts = explode(',', $params_raw);
    $clean_params = [];
    foreach ($params_parts as $param) {
      $param = trim($param);
      // Extract just the variable name and default value (if any)
      // Match: $varname or $varname = value
      if (preg_match('/(\$\w+)(\s*=\s*.+)?$/', $param, $match)) {
        $clean_params[] = $match[1] . ($match[2] ?? '');
      }
    }
    $params_raw = implode(', ', $clean_params);

    // Simplify default values - keep only simple ones
    // Replace array() with []
    $params_raw = str_replace('array()', '[]', $params_raw);
    // Remove complex default values, keep only simple ones like '', 0, null, true, false, []
    $params_raw = preg_replace('/=\s*[^,\)]+(?![\'"\d\[\]null|true|false])/', '= null', $params_raw);

    $params_raw = trim($params_raw);

    $functions[$function_name] = [
      'name' => $function_name,
      'params' => $params_raw,
    ];
  } else {
    $skipped[] = $dir;
  }
}

echo "Found " . count($functions) . " WordPress functions\n";
echo "Skipped " . count($skipped) . " directories\n";

// Generate stub file
$output = "<?php\n";
$output .= "/**\n";
$output .= " * Auto-generated WordPress function stubs.\n";
$output .= " * Generated: " . date('Y-m-d H:i:s') . "\n";
$output .= " * \n";
$output .= " * DO NOT EDIT MANUALLY - Regenerate with:\n";
$output .= " * php scripts/generate_stubs_from_docs.php /path/to/developer.wordpress.org/reference/functions\n";
$output .= " */\n\n";

ksort($functions);

foreach ($functions as $func) {
  $output .= "if (!function_exists('{$func['name']}')) {\n";
  $output .= "  /**\n";
  $output .= "   * WordPress function: {$func['name']}\n";
  $output .= "   * @see https://developer.wordpress.org/reference/functions/{$func['name']}/\n";
  $output .= "   */\n";
  $output .= "  function {$func['name']}({$func['params']}) {\n";

  // Smart default return based on function name
  if (strpos($func['name'], 'get_') === 0) {
    $output .= "    return null;\n";
  } elseif (strpos($func['name'], 'is_') === 0 || strpos($func['name'], 'has_') === 0) {
    $output .= "    return false;\n";
  } elseif (strpos($func['name'], 'the_') === 0) {
    $output .= "    // Output function - return nothing\n";
  } else {
    $output .= "    return null;\n";
  }

  $output .= "  }\n";
  $output .= "}\n\n";
}

$output_file = dirname(__DIR__) . '/themes/wp/functions/auto-stubs.php';
file_put_contents($output_file, $output);

echo "Generated $output_file with " . count($functions) . " function stubs\n";
echo "Done!\n";
