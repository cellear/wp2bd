<?php
/**
 * @file
 * Theme settings for WordPress theme wrapper.
 *
 * Provides configuration form for selecting active WordPress theme.
 */

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function wp_form_system_theme_settings_alter(&$form, &$form_state) {
  // Get list of available WordPress themes
  $wp_themes_dir = BACKDROP_ROOT . '/' . backdrop_get_path('theme', 'wp') . '/wpbrain/wp-content/themes';
  $available_themes = array();

  if (is_dir($wp_themes_dir)) {
    $themes = scandir($wp_themes_dir);
    foreach ($themes as $theme) {
      if ($theme[0] !== '.' && is_dir($wp_themes_dir . '/' . $theme)) {
        // Check if it has a style.css (valid WordPress theme)
        if (file_exists($wp_themes_dir . '/' . $theme . '/style.css')) {
          $available_themes[$theme] = ucwords(str_replace(array('twenty', '-', '_'), array('Twenty ', ' ', ' '), $theme));
        }
      }
    }
  }

  // Sort themes alphabetically
  asort($available_themes);

  // Add WordPress theme selector
  $form['wordpress_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('WordPress Theme Settings'),
    '#description' => t('Configure which WordPress theme to use for rendering content.'),
    '#weight' => -10,
  );

  $form['wordpress_settings']['active_theme'] = array(
    '#type' => 'select',
    '#title' => t('Active WordPress Theme'),
    '#description' => t('Select which WordPress theme to use for rendering. Available themes are located in <code>themes/wp/wpbrain/wp-content/themes/</code>'),
    '#options' => $available_themes,
    '#default_value' => theme_get_setting('active_theme', 'wp') ?: 'twentyseventeen',
    '#required' => TRUE,
  );

  if (empty($available_themes)) {
    $form['wordpress_settings']['active_theme']['#disabled'] = TRUE;
    $form['wordpress_settings']['active_theme']['#description'] = t('No WordPress themes found in <code>themes/wp/wpbrain/wp-content/themes/</code>. Please add WordPress themes to this directory.');
  }

  // Add information about current configuration
  $current_theme = theme_get_setting('active_theme', 'wp') ?: 'twentyseventeen';
  $current_theme_path = $wp_themes_dir . '/' . $current_theme;

  if (is_dir($current_theme_path)) {
    $form['wordpress_settings']['current_info'] = array(
      '#type' => 'item',
      '#title' => t('Current Theme Information'),
      '#markup' => t('Currently using: <strong>@theme</strong><br>Located at: <code>@path</code>', array(
        '@theme' => isset($available_themes[$current_theme]) ? $available_themes[$current_theme] : $current_theme,
        '@path' => $current_theme_path,
      )),
    );
  }

  // Add cache clear notice
  $form['wordpress_settings']['cache_notice'] = array(
    '#type' => 'item',
    '#markup' => '<div class="messages warning">' . t('Note: After changing the WordPress theme, you should clear all caches for the changes to take effect.') . '</div>',
  );
}
