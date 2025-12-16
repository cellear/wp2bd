<?php
/**
 * WordPress Post Object Bridge for WP4BD V2
 *
 * Provides conversion between Backdrop nodes and real WordPress WP_Post objects.
 * This ensures compatibility with actual WordPress 4.9 WP_Post class.
 *
 * @package WP4BD
 * @subpackage V2-Architecture
 * @since WP4BD-V2-060
 */

/**
 * Convert a Backdrop node to a WordPress WP_Post object.
 *
 * This function uses the real WordPress WP_Post class loaded from WordPress core
 * to create proper WordPress post objects from Backdrop nodes.
 *
 * @param object $node Backdrop node object (fully loaded with all fields)
 * @return WP_Post|null WordPress post object or null on failure
 */
function wp4bd_node_to_wp_post($node) {
  if (!is_object($node) || !isset($node->nid)) {
    return null;
  }

  // Ensure node has required properties
  if (!isset($node->type)) {
    if (function_exists('node_load')) {
      $full_node = node_load($node->nid);
      if ($full_node && isset($full_node->type)) {
        $node = $full_node;
      } else {
        $node->type = 'post'; // Default fallback
      }
    } else {
      $node->type = 'post'; // Default fallback
    }
  }

  // Create post data array for WordPress WP_Post constructor
  $post_data = array(
    'ID' => (int) $node->nid,
    'post_author' => isset($node->uid) ? (string) $node->uid : '0',
    'post_date' => isset($node->created) ? date('Y-m-d H:i:s', $node->created) : '0000-00-00 00:00:00',
    'post_date_gmt' => isset($node->created) ? gmdate('Y-m-d H:i:s', $node->created) : '0000-00-00 00:00:00',
    'post_content' => _wp4bd_extract_node_content($node),
    'post_title' => isset($node->title) ? $node->title : '',
    'post_excerpt' => _wp4bd_extract_node_excerpt($node),
    'post_status' => (isset($node->status) && $node->status == 1) ? 'publish' : 'draft',
    'comment_status' => _wp4bd_get_comment_status($node),
    'ping_status' => 'closed', // Backdrop doesn't have ping functionality
    'post_password' => '', // Not supported in basic Backdrop
    'post_name' => _wp4bd_get_post_name($node),
    'to_ping' => '',
    'pinged' => '',
    'post_modified' => isset($node->changed) ? date('Y-m-d H:i:s', $node->changed) : '0000-00-00 00:00:00',
    'post_modified_gmt' => isset($node->changed) ? gmdate('Y-m-d H:i:s', $node->changed) : '0000-00-00 00:00:00',
    'post_content_filtered' => '',
    'post_parent' => 0, // Backdrop nodes don't have parent relationships by default
    'guid' => _wp4bd_get_node_guid($node),
    'menu_order' => 0,
    'post_type' => isset($node->type) ? $node->type : 'post',
    'post_mime_type' => '',
    'comment_count' => isset($node->comment_count) ? (string) $node->comment_count : '0',
    'filter' => 'raw',
  );

  // Create WP_Post object using WordPress constructor
  try {
    $wp_post = new WP_Post((object) $post_data);
    return $wp_post;
  } catch (Exception $e) {
    // Log error but don't fail completely
    if (function_exists('watchdog')) {
      watchdog('wp4bd', 'Failed to create WP_Post from node @nid: @error', array(
        '@nid' => $node->nid,
        '@error' => $e->getMessage(),
      ), WATCHDOG_WARNING);
    }
    return null;
  }
}

/**
 * Extract content from Backdrop node.
 *
 * @param object $node
 * @return string
 */
function _wp4bd_extract_node_content($node) {
  if (isset($node->body) && is_array($node->body)) {
    $lang_key = defined('LANGUAGE_NONE') ? LANGUAGE_NONE : 'und';
    if (isset($node->body[$lang_key][0]['value'])) {
      return $node->body[$lang_key][0]['value'];
    }
  }
  return '';
}

/**
 * Extract excerpt/summary from Backdrop node.
 *
 * @param object $node
 * @return string
 */
function _wp4bd_extract_node_excerpt($node) {
  if (isset($node->body) && is_array($node->body)) {
    $lang_key = defined('LANGUAGE_NONE') ? LANGUAGE_NONE : 'und';
    if (isset($node->body[$lang_key][0]['summary'])) {
      return $node->body[$lang_key][0]['summary'];
    }
  }
  return '';
}

/**
 * Get comment status from Backdrop node.
 *
 * @param object $node
 * @return string
 */
function _wp4bd_get_comment_status($node) {
  if (isset($node->comment)) {
    // Backdrop comment values: 0 = hidden, 1 = closed, 2 = open
    return ($node->comment == 2) ? 'open' : 'closed';
  }
  return 'closed';
}

/**
 * Get post name/slug from Backdrop node.
 *
 * @param object $node
 * @return string
 */
function _wp4bd_get_post_name($node) {
  // Try to get from path alias first
  if (isset($node->path) && is_array($node->path) && isset($node->path['alias'])) {
    return $node->path['alias'];
  } elseif (function_exists('backdrop_get_path_alias')) {
    $alias = backdrop_get_path_alias('node/' . $node->nid);
    if ($alias != 'node/' . $node->nid) {
      return $alias;
    }
  }

  // Fallback: create slug from title
  if (!empty($node->title)) {
    return _wp4bd_sanitize_title($node->title);
  }

  return '';
}

/**
 * Get GUID (Globally Unique Identifier) for Backdrop node.
 *
 * @param object $node
 * @return string
 */
function _wp4bd_get_node_guid($node) {
  if (function_exists('url')) {
    return url('node/' . $node->nid, array('absolute' => TRUE));
  } else {
    // Fallback for testing or when url() is not available
    $base_url = isset($GLOBALS['base_url']) ? $GLOBALS['base_url'] : 'http://example.com';
    return $base_url . '/node/' . $node->nid;
  }
}

/**
 * Simple title sanitization for slug creation.
 *
 * @param string $title
 * @return string
 */
function _wp4bd_sanitize_title($title) {
  // Convert to lowercase
  $slug = strtolower($title);

  // Replace spaces and special chars with hyphens
  $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

  // Remove leading/trailing hyphens
  $slug = trim($slug, '-');

  return $slug;
}

/**
 * Convert multiple Backdrop nodes to WordPress WP_Post objects.
 *
 * @param array $nodes Array of Backdrop node objects
 * @return array Array of WP_Post objects
 */
function wp4bd_nodes_to_wp_posts($nodes) {
  $wp_posts = array();

  if (!is_array($nodes)) {
    return $wp_posts;
  }

  foreach ($nodes as $node) {
    $wp_post = wp4bd_node_to_wp_post($node);
    if ($wp_post) {
      $wp_posts[] = $wp_post;
    }
  }

  return $wp_posts;
}
