<?php
/**
 * @file
 * Capture theme preprocess functions and render capture helpers.
 */

/**
 * Implements template_preprocess_page().
 */
function capture_theme_preprocess_page(array &$variables) {
  backdrop_add_css('.capture-theme-note { background: #fff5c2; border: 1px solid #e0c96a; padding: 12px 16px; margin: 12px 0; font-weight: 600; } .capture-theme-note a { font-weight: 700; }', array('type' => 'inline'));
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

  // Ensure capture runs even if process_page is not invoked by the theme layer.
  _wp2bd_capture_write($variables);
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
  _wp2bd_capture_write($variables);
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

/**
 * Write the capture payload and surface a UI note.
 */
function _wp2bd_capture_write(array &$variables) {
  static $has_run = FALSE;
  if ($has_run) {
    return;
  }
  $has_run = TRUE;

  global $theme_key;

  $path = current_path();
  $timestamp = (int) REQUEST_TIME;

  $safe_path = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $path);
  $safe_path = trim($safe_path, '-');
  if ($safe_path === '') {
    $safe_path = 'front';
  }

  $directory = 'public://theme-captures';
  if (!file_prepare_directory($directory, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS)) {
    watchdog('capture_theme', 'Failed to prepare directory %dir', array('%dir' => $directory), WATCHDOG_ERROR);
  }
  $uri = $directory . '/' . $safe_path . '--' . $timestamp . '.json';

  $page_vars = isset($variables['page']) && is_array($variables['page']) ? $variables['page'] : array();
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
      'page' => _wp2bd_capture_sanitize($page_vars),
      'page_raw_type' => isset($variables['page']) ? gettype($variables['page']) : 'unset',
    ),
  );

  $export = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
  $saved = file_unmanaged_save_data($export, $uri, FILE_EXISTS_REPLACE);
  if (!$saved) {
    watchdog('capture_theme', 'Capture failed to save to %uri', array('%uri' => $uri), WATCHDOG_ERROR);
  }

  $file_url = file_create_url($uri);
  $note = t('Theme capture saved to %path. !link', array(
    '%path' => $uri,
    '!link' => l(t('View capture payload'), $file_url, array('external' => TRUE)),
  ));

  backdrop_set_message($note, 'status');

  $note = t('Theme capture created: !link', array(
    '!link' => l(t('View capture payload'), $file_url, array('external' => TRUE)),
  ));

  $note_markup = '<div class="capture-theme-note">Capture created. ' . $note . '</div>';
  if (isset($variables['page']) && is_array($variables['page'])) {
    if (!isset($variables['page']['content']) || !is_array($variables['page']['content'])) {
      $variables['page']['content'] = array();
    }
    $variables['page']['content']['capture_theme_note'] = array(
      '#type' => 'markup',
      '#markup' => $note_markup,
      '#weight' => 1000,
    );
  }
  else {
    if (isset($variables['messages']) && is_string($variables['messages'])) {
      $variables['messages'] .= $note_markup;
    }
    else {
      $variables['messages'] = $note_markup;
    }
  }
}
