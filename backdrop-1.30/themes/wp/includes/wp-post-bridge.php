<?php
/**
 * @file
 * WordPress Post Object Bridge for WP4BD V2
 *
 * Converts Backdrop nodes to real WordPress WP_Post objects.
 * Uses the actual WordPress WP_Post class loaded from WordPress 4.9 core.
 *
 * @see WP4BD-V2-060
 * @package WP4BD
 * @subpackage Data-Bridges
 */

/**
 * Convert a Backdrop node to a WordPress WP_Post object.
 *
 * This function creates a real WordPress WP_Post instance (from WordPress core)
 * and populates all 21 standard properties from Backdrop node data.
 *
 * @param object $node
 *   A fully loaded Backdrop node object.
 *
 * @return WP_Post|null
 *   A WordPress WP_Post object, or NULL if the node is invalid.
 */
function wp4bd_node_to_post($node) {
  // Validate input
  if (!is_object($node) || !isset($node->nid)) {
    return NULL;
  }

  // Ensure node has required properties (especially 'type' bundle property)
  // If node doesn't have 'type', try to load it fully
  if (!isset($node->type) && function_exists('node_load')) {
    $full_node = node_load($node->nid);
    if ($full_node && isset($full_node->type)) {
      $node = $full_node;
    }
    else {
      // Fallback: set a default type
      $node->type = 'page';
    }
  }
  elseif (!isset($node->type)) {
    // If we can't load it, set a default
    $node->type = 'page';
  }

  // Create a stdClass object with all the properties
  // WordPress's real WP_Post constructor requires an object with properties set
  $post_data = new stdClass();

  // ========================================================================
  // BASIC PROPERTIES
  // ========================================================================

  // Post ID - maps directly from node ID
  $post_data->ID = (int) $node->nid;

  // Author ID - maps from Backdrop user ID
  $post_data->post_author = isset($node->uid) ? (int) $node->uid : 0;

  // Post title
  $post_data->post_title = isset($node->title) ? $node->title : '';

  // Post type - maps from Backdrop content type
  $post_data->post_type = isset($node->type) ? $node->type : 'post';

  // Parent post ID - Backdrop nodes don't have parent relationships by default
  $post_data->post_parent = 0;

  // Menu order - used for sorting
  $post_data->menu_order = 0;

  // Filter context - indicates content hasn't been filtered yet
  $post_data->filter = 'raw';

  // ========================================================================
  // DATES
  // ========================================================================

  // Created date
  if (isset($node->created)) {
    $post_data->post_date = date('Y-m-d H:i:s', $node->created);
    $post_data->post_date_gmt = gmdate('Y-m-d H:i:s', $node->created);
  }
  else {
    $post_data->post_date = '0000-00-00 00:00:00';
    $post_data->post_date_gmt = '0000-00-00 00:00:00';
  }

  // Modified date
  if (isset($node->changed)) {
    $post_data->post_modified = date('Y-m-d H:i:s', $node->changed);
    $post_data->post_modified_gmt = gmdate('Y-m-d H:i:s', $node->changed);
  }
  else {
    $post_data->post_modified = '0000-00-00 00:00:00';
    $post_data->post_modified_gmt = '0000-00-00 00:00:00';
  }

  // ========================================================================
  // STATUS
  // ========================================================================

  // Post status - published nodes are 'publish', unpublished are 'draft'
  $post_data->post_status = (isset($node->status) && $node->status == 1) ? 'publish' : 'draft';

  // ========================================================================
  // CONTENT
  // ========================================================================

  // Extract body content
  // Backdrop stores body in $node->body[LANGUAGE_NONE][0]
  $post_data->post_content = '';
  $post_data->post_excerpt = '';

  if (isset($node->body) && is_array($node->body)) {
    // Check for LANGUAGE_NONE constant or use 'und'
    $lang_key = defined('LANGUAGE_NONE') ? LANGUAGE_NONE : 'und';

    if (isset($node->body[$lang_key][0]['value'])) {
      $post_data->post_content = $node->body[$lang_key][0]['value'];
    }

    // Extract summary/excerpt if available
    if (isset($node->body[$lang_key][0]['summary'])) {
      $post_data->post_excerpt = $node->body[$lang_key][0]['summary'];
    }
  }

  // ========================================================================
  // SLUG/NAME
  // ========================================================================

  // Post name/slug - try to get from path alias
  $post_data->post_name = '';
  if (isset($node->path) && is_array($node->path) && isset($node->path['alias'])) {
    $post_data->post_name = $node->path['alias'];
  }
  elseif (function_exists('backdrop_get_path_alias')) {
    $alias = backdrop_get_path_alias('node/' . $node->nid);
    if ($alias != 'node/' . $node->nid) {
      $post_data->post_name = $alias;
    }
  }

  // If no alias, create slug from title
  if (empty($post_data->post_name) && !empty($post_data->post_title)) {
    $post_data->post_name = wp4bd_sanitize_title($post_data->post_title);
  }

  // ========================================================================
  // COMMENTS
  // ========================================================================

  // Comment count
  $post_data->comment_count = isset($node->comment_count) ? (int) $node->comment_count : 0;

  // Comment status
  if (isset($node->comment)) {
    // Backdrop comment values: 0 = hidden, 1 = closed, 2 = open
    $post_data->comment_status = ($node->comment == 2) ? 'open' : 'closed';
  }
  else {
    $post_data->comment_status = 'closed';
  }

  // Ping status - Backdrop doesn't have pingbacks
  $post_data->ping_status = 'closed';

  // ========================================================================
  // MISC PROPERTIES
  // ========================================================================

  // Post password - Backdrop doesn't have password-protected content
  $post_data->post_password = '';

  // To ping - Backdrop doesn't have pingback URLs
  $post_data->to_ping = '';

  // Pinged - Backdrop doesn't track pinged URLs
  $post_data->pinged = '';

  // Post MIME type - only used for attachments
  $post_data->post_mime_type = '';

  // GUID - global unique identifier (typically the permalink)
  // We'll construct this from the base URL and node path
  global $base_url;
  if (!empty($post_data->post_name)) {
    $post_data->guid = $base_url . '/' . $post_data->post_name;
  }
  else {
    $post_data->guid = $base_url . '/node/' . $post_data->ID;
  }

  // Create the real WordPress WP_Post object
  $post = new WP_Post($post_data);

  return $post;
}

/**
 * Sanitize a title to create a URL-safe slug.
 *
 * This is a simplified version of WordPress's sanitize_title().
 *
 * @param string $title
 *   The title to sanitize.
 *
 * @return string
 *   URL-safe slug.
 */
function wp4bd_sanitize_title($title) {
  // Convert to lowercase
  $slug = strtolower($title);

  // Replace spaces and special characters with hyphens
  $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

  // Remove leading/trailing hyphens
  $slug = trim($slug, '-');

  return $slug;
}

/**
 * Convert multiple Backdrop nodes to WordPress WP_Post objects.
 *
 * @param array $nodes
 *   Array of Backdrop node objects.
 *
 * @return array
 *   Array of WP_Post objects.
 */
function wp4bd_nodes_to_posts(array $nodes) {
  $posts = array();

  foreach ($nodes as $node) {
    $post = wp4bd_node_to_post($node);
    if ($post) {
      $posts[] = $post;
    }
  }

  return $posts;
}
