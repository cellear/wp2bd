<?php
/**
 * @file
 * WordPress Term/Taxonomy Bridge for WP4BD V2
 *
 * Epic 7 V2-062: Converts Backdrop taxonomy terms to WordPress WP_Term objects.
 *
 * This bridge enables WordPress themes to access Backdrop's taxonomy data
 * through WordPress's standard term/taxonomy functions.
 */

/**
 * Convert a Backdrop taxonomy term to a WordPress WP_Term object.
 *
 * Maps Backdrop's taxonomy term structure to WordPress's WP_Term format,
 * handling vocabulary-to-taxonomy name mapping and slug generation.
 *
 * @param object $term
 *   A Backdrop taxonomy term object loaded via taxonomy_term_load().
 *
 * @return WP_Term|NULL
 *   A WordPress WP_Term object, or NULL if conversion fails.
 */
function wp4bd_term_to_wp_term($term) {
  if (!is_object($term) || !isset($term->tid)) {
    return NULL;
  }

  // Create a WordPress WP_Term object
  $wp_term = new WP_Term((object) array());

  // Map basic properties
  $wp_term->term_id = (int) $term->tid;
  $wp_term->name = isset($term->name) ? $term->name : '';
  $wp_term->description = isset($term->description) ? $term->description : '';

  // Generate slug from term name (URL-safe version)
  $wp_term->slug = wp4bd_sanitize_term_slug($term->name);

  // Map Backdrop vocabulary to WordPress taxonomy
  // Backdrop vocabularies like "tags" map to WordPress taxonomies like "post_tag"
  $wp_term->taxonomy = wp4bd_vocabulary_to_taxonomy($term->vocabulary);

  // Get parent from hierarchy (if any)
  // Backdrop stores hierarchy in taxonomy_term_hierarchy table
  $wp_term->parent = 0;
  if (isset($term->parent) && is_array($term->parent)) {
    // Parent is stored as an array, take the first parent
    $parent_tids = array_filter($term->parent);
    if (!empty($parent_tids)) {
      $wp_term->parent = (int) reset($parent_tids);
    }
  }
  elseif (isset($term->parent) && is_numeric($term->parent)) {
    $wp_term->parent = (int) $term->parent;
  }

  // Set term_taxonomy_id (same as term_id in our simplified bridge)
  // WordPress uses separate term and term_taxonomy IDs, but for our purposes
  // we can use the same ID since we're not sharing terms across taxonomies
  $wp_term->term_taxonomy_id = $wp_term->term_id;

  // Set term_group (default to empty string)
  $wp_term->term_group = '';

  // Count of objects tagged with this term
  // This would require querying taxonomy_index, so we'll set to 0 for now
  // WordPress themes typically don't display this for individual posts
  $wp_term->count = 0;

  // Filter level (raw = no sanitization applied yet)
  $wp_term->filter = 'raw';

  return $wp_term;
}

/**
 * Map Backdrop vocabulary machine name to WordPress taxonomy name.
 *
 * @param string $vocabulary
 *   Backdrop vocabulary machine name (e.g., "tags", "categories").
 *
 * @return string
 *   WordPress taxonomy name (e.g., "post_tag", "category").
 */
function wp4bd_vocabulary_to_taxonomy($vocabulary) {
  if (empty($vocabulary)) {
    return 'post_tag'; // Default to post_tag
  }

  // Common mappings
  $mappings = array(
    'tags' => 'post_tag',
    'categories' => 'category',
    'category' => 'category',
  );

  // Check if we have a predefined mapping
  $vocabulary_lower = backdrop_strtolower($vocabulary);
  if (isset($mappings[$vocabulary_lower])) {
    return $mappings[$vocabulary_lower];
  }

  // For custom vocabularies, use the machine name as-is
  // WordPress will treat it as a custom taxonomy
  return $vocabulary;
}

/**
 * Create URL-safe slug from term name.
 *
 * Similar to WordPress's sanitize_title() function but simplified
 * for Backdrop's needs.
 *
 * @param string $name
 *   The term name to convert to a slug.
 *
 * @return string
 *   URL-safe slug.
 */
function wp4bd_sanitize_term_slug($name) {
  if (empty($name)) {
    return '';
  }

  // Convert to lowercase
  $slug = backdrop_strtolower($name);

  // Replace spaces and underscores with hyphens
  $slug = str_replace(array(' ', '_'), '-', $slug);

  // Remove special characters
  $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);

  // Remove multiple consecutive hyphens
  $slug = preg_replace('/-+/', '-', $slug);

  // Trim hyphens from ends
  $slug = trim($slug, '-');

  return $slug;
}

/**
 * Convert multiple Backdrop terms to WordPress WP_Term objects.
 *
 * @param array $terms
 *   Array of Backdrop taxonomy term objects.
 *
 * @return array
 *   Array of WordPress WP_Term objects, keyed by term ID.
 */
function wp4bd_terms_to_wp_terms($terms) {
  $wp_terms = array();

  if (!is_array($terms)) {
    return $wp_terms;
  }

  foreach ($terms as $term) {
    $wp_term = wp4bd_term_to_wp_term($term);
    if ($wp_term) {
      $wp_terms[$wp_term->term_id] = $wp_term;
    }
  }

  return $wp_terms;
}

/**
 * Get WordPress taxonomy name from Backdrop content type field.
 *
 * Helper function to determine which WordPress taxonomy a Backdrop
 * taxonomy field should map to.
 *
 * @param string $field_name
 *   Backdrop field name (e.g., "field_tags", "field_categories").
 *
 * @return string
 *   WordPress taxonomy name.
 */
function wp4bd_field_to_taxonomy($field_name) {
  if (empty($field_name)) {
    return 'post_tag';
  }

  // Common field name patterns
  if (strpos($field_name, 'tag') !== FALSE) {
    return 'post_tag';
  }
  if (strpos($field_name, 'categor') !== FALSE) {
    return 'category';
  }

  // Default to post_tag for other taxonomy fields
  return 'post_tag';
}
