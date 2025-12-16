<?php
/**
 * WordPress Term/Taxonomy Bridge for WP4BD V2
 *
 * Provides conversion between Backdrop taxonomy terms and WordPress term objects.
 *
 * @package WP4BD
 * @subpackage V2-Architecture
 * @since WP4BD-V2-062
 */

/**
 * Convert a Backdrop taxonomy term to a WordPress term object.
 *
 * @param object $term Backdrop term object
 * @return object|null WordPress-style term object or null on failure
 */
function wp4bd_backdrop_term_to_wp_term($term) {
  if (!is_object($term) || !isset($term->tid)) {
    return null;
  }

  // Create WordPress-style term object
  $wp_term = new stdClass();

  // Basic term properties
  $wp_term->term_id = (int) $term->tid;
  $wp_term->name = isset($term->name) ? $term->name : '';
  $wp_term->slug = isset($term->machine_name) ? $term->machine_name : _wp4bd_sanitize_term_slug($term->name);
  $wp_term->term_group = 0;

  // Taxonomy mapping
  $wp_term->taxonomy = _wp4bd_map_backdrop_vocabulary_to_wp_taxonomy($term);

  // Parent term
  $wp_term->parent = isset($term->parent) ? (int) $term->parent : 0;

  // Description
  $wp_term->description = '';
  if (isset($term->description) && is_array($term->description)) {
    $lang_key = defined('LANGUAGE_NONE') ? LANGUAGE_NONE : 'und';
    if (isset($term->description[$lang_key][0]['value'])) {
      $wp_term->description = $term->description[$lang_key][0]['value'];
    }
  }

  // Term order
  $wp_term->term_order = isset($term->weight) ? (int) $term->weight : 0;

  // Count (number of nodes using this term)
  $wp_term->count = isset($term->count) ? (int) $term->count : 0;

  return $wp_term;
}

/**
 * Map Backdrop vocabulary to WordPress taxonomy.
 *
 * @param object $term Backdrop term with vocabulary info
 * @return string WordPress taxonomy name
 */
function _wp4bd_map_backdrop_vocabulary_to_wp_taxonomy($term) {
  // Default to 'category' if no vocabulary info
  if (!isset($term->vocabulary_machine_name)) {
    return 'category';
  }

  $vocab_machine_name = $term->vocabulary_machine_name;

  // Common mappings
  $mappings = array(
    'categories' => 'category',
    'tags' => 'post_tag',
    'topics' => 'category',
    'subjects' => 'category',
  );

  // Check if we have a direct mapping
  if (isset($mappings[$vocab_machine_name])) {
    return $mappings[$vocab_machine_name];
  }

  // For custom vocabularies, prefix with 'backdrop_' to avoid conflicts
  return 'backdrop_' . $vocab_machine_name;
}

/**
 * Sanitize term slug.
 *
 * @param string $name Term name
 * @return string Sanitized slug
 */
function _wp4bd_sanitize_term_slug($name) {
  if (empty($name)) {
    return '';
  }

  // Convert to lowercase
  $slug = strtolower($name);

  // Replace spaces and special chars with hyphens
  $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

  // Remove leading/trailing hyphens
  $slug = trim($slug, '-');

  return $slug;
}

/**
 * Convert multiple Backdrop terms to WordPress term objects.
 *
 * @param array $terms Array of Backdrop term objects
 * @return array Array of WordPress term objects
 */
function wp4bd_backdrop_terms_to_wp_terms($terms) {
  $wp_terms = array();

  if (!is_array($terms)) {
    return $wp_terms;
  }

  foreach ($terms as $term) {
    $wp_term = wp4bd_backdrop_term_to_wp_term($term);
    if ($wp_term) {
      $wp_terms[] = $wp_term;
    }
  }

  return $wp_terms;
}

/**
 * Get WordPress taxonomy object from Backdrop vocabulary.
 *
 * @param object $vocabulary Backdrop vocabulary object
 * @return object|null WordPress-style taxonomy object
 */
function wp4bd_backdrop_vocabulary_to_wp_taxonomy($vocabulary) {
  if (!is_object($vocabulary) || !isset($vocabulary->machine_name)) {
    return null;
  }

  $wp_taxonomy = new stdClass();

  $wp_taxonomy->name = _wp4bd_map_backdrop_vocabulary_to_wp_taxonomy((object) array(
    'vocabulary_machine_name' => $vocabulary->machine_name
  ));
  $wp_taxonomy->label = isset($vocabulary->name) ? $vocabulary->name : $vocabulary->machine_name;
  $wp_taxonomy->description = isset($vocabulary->description) ? $vocabulary->description : '';

  // Taxonomy capabilities
  $wp_taxonomy->capabilities = array(
    'manage_terms' => 'manage_categories',
    'edit_terms' => 'manage_categories',
    'delete_terms' => 'manage_categories',
    'assign_terms' => 'edit_posts',
  );

  // Other taxonomy properties
  $wp_taxonomy->object_type = array('post'); // Default to posts
  $wp_taxonomy->hierarchical = isset($vocabulary->hierarchy) ? (bool) $vocabulary->hierarchy : true;
  $wp_taxonomy->public = true;
  $wp_taxonomy->show_ui = true;
  $wp_taxonomy->show_in_menu = true;
  $wp_taxonomy->show_in_nav_menus = true;
  $wp_taxonomy->show_tagcloud = !$wp_taxonomy->hierarchical;
  $wp_taxonomy->show_in_rest = false; // WordPress 4.9 doesn't have this, but keeping for compatibility
  $wp_taxonomy->rest_base = $wp_taxonomy->name;
  $wp_taxonomy->rest_controller_class = 'WP_REST_Terms_Controller';
  $wp_taxonomy->rewrite = array(
    'slug' => $vocabulary->machine_name,
    'with_front' => false,
  );
  $wp_taxonomy->query_var = $vocabulary->machine_name;

  return $wp_taxonomy;
}
