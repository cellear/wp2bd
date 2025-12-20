<?php
/**
 * @file
 * Capture theme preprocess functions and render capture helpers.
 */

/**
 * Implements template_preprocess_page().
 */
function capture_theme_preprocess_page(array &$variables) {
  _wp2bd_capture_store('preprocess_page', _wp2bd_capture_sanitize($variables));

  if (!isset($GLOBALS['wp2bd_capture_globals'])) {
    global $base_url, $language, $user, $theme_key;

    $breadcrumb = isset($variables['breadcrumb']) ? $variables['breadcrumb'] : array();
    $menu_trees = array();
    $menus = menu_get_menus();
    foreach ($menus as $menu_name => $menu_title) {
      $menu_trees[$menu_name] = menu_tree_all_data($menu_name);
    }

    $GLOBALS['wp2bd_capture_globals'] = array(
      'theme' => $theme_key,
      'base_url' => $base_url,
      'language' => $language,
      'user' => $user,
      'breadcrumb' => $breadcrumb,
      'menus' => _wp2bd_capture_sanitize($menu_trees),
    );
  }
}

/**
 * Implements template_preprocess_node().
 */
function capture_theme_preprocess_node(array &$variables) {
  _wp2bd_capture_store('preprocess_node', _wp2bd_capture_sanitize($variables));
}

/**
 * Implements template_preprocess_block().
 */
function capture_theme_preprocess_block(array &$variables) {
  _wp2bd_capture_store('preprocess_block', _wp2bd_capture_sanitize($variables));
}

/**
 * Implements template_preprocess_region().
 */
function capture_theme_preprocess_region(array &$variables) {
  _wp2bd_capture_store('preprocess_region', _wp2bd_capture_sanitize($variables));
}

/**
 * Implements template_process_page().
 */
function capture_theme_process_page(array &$variables) {
  global $theme_key;

  $path = current_path();
  $timestamp = (int) REQUEST_TIME;
  $safe_path = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $path);
  $safe_path = trim($safe_path, '-');
  if ($safe_path === '') {
    $safe_path = 'front';
  }

  $directory = 'public://theme-captures';
  file_prepare_directory($directory, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
  $uri = $directory . '/' . $safe_path . '--' . $timestamp . '.php';

  $payload = array(
    'metadata' => array(
      'path' => $path,
      'timestamp' => $timestamp,
      'theme' => $theme_key,
      'file' => $uri,
    ),
    'globals' => isset($GLOBALS['wp2bd_capture_globals']) ? $GLOBALS['wp2bd_capture_globals'] : array(),
    'preprocess' => _wp2bd_capture_store(),
    'page' => array(
      'variables' => _wp2bd_capture_sanitize($variables),
      'page' => isset($variables['page']) ? _wp2bd_capture_sanitize($variables['page']) : array(),
    ),
  );

  $export = "<?php\nreturn " . var_export($payload, TRUE) . ";\n";
  file_unmanaged_save_data($export, $uri, FILE_EXISTS_REPLACE);

  $file_url = file_create_url($uri);
  $note = t('Theme capture created: !link', array(
    '!link' => l(t('View capture payload'), $file_url, array('external' => TRUE)),
  ));

  $variables['page']['content']['capture_theme_note'] = array(
    '#type' => 'markup',
    '#markup' => '<div class="capture-theme-note">' . $note . '</div>',
    '#weight' => 1000,
  );
}

/**
 * Store and retrieve capture data.
 */
function _wp2bd_capture_store($key = NULL, $value = NULL) {
  if (!isset($GLOBALS['wp2bd_capture_store'])) {
    $GLOBALS['wp2bd_capture_store'] = array(
      'preprocess_page' => array(),
      'preprocess_node' => array(),
      'preprocess_block' => array(),
      'preprocess_region' => array(),
    );
  }

  if ($key !== NULL) {
    $GLOBALS['wp2bd_capture_store'][$key][] = $value;
  }

  return $GLOBALS['wp2bd_capture_store'];
}

/**
 * Recursively sanitize values for export.
 */
function _wp2bd_capture_sanitize($value, $depth = 0) {
  $max_depth = 6;
  if ($depth > $max_depth) {
    return '[max-depth]';
  }

  if (is_array($value)) {
    $sanitized = array();
    foreach ($value as $key => $item) {
      $sanitized[$key] = _wp2bd_capture_sanitize($item, $depth + 1);
    }
    return $sanitized;
  }

  if (is_object($value)) {
    $properties = get_object_vars($value);
    return array(
      '_object_class' => get_class($value),
      'properties' => _wp2bd_capture_sanitize($properties, $depth + 1),
    );
  }

  if (is_resource($value)) {
    return '[resource]';
  }

  return $value;
}
