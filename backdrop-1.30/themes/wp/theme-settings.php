<?php
/**
 * @file
 * Theme settings for the WordPress Theme Wrapper.
 */

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function wp_form_system_theme_settings_alter(&$form, &$form_state) {
  // WordPress Theme Selection
  $form['wp_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('WordPress Theme Settings'),
    '#description' => t('Configure which WordPress theme to use for content display.'),
  );

  // Get available WordPress themes
  $wp_themes_dir = __DIR__ . '/wp-content/themes';
  $available_themes = array();

  if (is_dir($wp_themes_dir)) {
    $theme_dirs = scandir($wp_themes_dir);
    foreach ($theme_dirs as $theme_dir) {
      if ($theme_dir !== '.' && $theme_dir !== '..' && is_dir($wp_themes_dir . '/' . $theme_dir)) {
        $theme_name = ucwords(str_replace(array('-', '_'), ' ', $theme_dir));
        $available_themes[$theme_dir] = $theme_name;
      }
    }
  }

  $form['wp_settings']['wp_active_theme'] = array(
    '#type' => 'select',
    '#title' => t('Active WordPress Theme'),
    '#description' => t('Select which WordPress theme to use for displaying content. Themes must be placed in the wp-content/themes directory.'),
    '#options' => $available_themes,
    '#default_value' => theme_get_setting('wp_active_theme'),
    '#required' => TRUE,
  );

  // Add some information about theme compatibility and usage
  $form['wp_settings']['info'] = array(
    '#markup' => '<div class="description">' .
      '<p><strong>WordPress Theme Compatibility:</strong></p>' .
      '<ul>' .
      '<li>Only WordPress themes compatible with WordPress 4.9+ are supported</li>' .
      '<li>Themes should be placed in: <code>themes/wp/wp-content/themes/</code></li>' .
      '<li>Currently tested with: Twenty Fourteen, Fifteen, Sixteen, Seventeen</li>' .
      '<li>Block/Gutenberg themes are not supported</li>' .
      '</ul>' .
      '<p><strong>Available Themes:</strong> ' . count($available_themes) . ' themes found</p>' .
      '<p><strong>Temporary Theme Switching:</strong> You can also test themes by adding <code>?wp_theme=themename</code> to any URL.</p>' .
      '</div>',
  );
}
