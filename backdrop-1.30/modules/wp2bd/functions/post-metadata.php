<?php
/**
 * WordPress Post Metadata Functions for Backdrop
 *
 * Functions for retrieving post metadata and information.
 * Maps WordPress post metadata functions to Backdrop's node system.
 *
 * @package WP2BD
 * @subpackage Post Metadata
 */

/**
 * Retrieve the post format.
 *
 * A post format is a piece of meta information that can be used by a theme
 * to customize its presentation of a post.
 *
 * WordPress Behavior:
 * - Returns the post format slug (e.g., 'aside', 'gallery', 'link', etc.)
 * - Returns 'standard' if no format is set or post-formats are not supported
 * - Checks the 'post_format' taxonomy term
 * - Returns false if post doesn't exist
 *
 * Backdrop Mapping:
 * - Check for taxonomy term in 'post_format' vocabulary if exists
 * - For most Backdrop sites, return 'standard' as default
 * - Could map to custom taxonomy or field if configured
 * - Future: Check node field or term reference for format
 *
 * Supported Formats:
 * - standard (default)
 * - aside
 * - gallery
 * - link
 * - image
 * - quote
 * - status
 * - video
 * - audio
 * - chat
 *
 * @since WordPress 3.1.0
 * @since WP2BD 1.0.0
 *
 * @global object $wp_post The current post object (WP2BD compatibility).
 *
 * @param int|WP_Post|object|null $post Optional. Post ID or post object. Default is global $post.
 * @return string|false The post format slug or false on failure. 'standard' if no format.
 */
function get_post_format( $post = null ) {
  global $wp_post;

  // If no post provided, use global $wp_post
  if ( null === $post ) {
    $post = isset( $wp_post ) ? $wp_post : null;
  }

  // If we still don't have a post, return false
  if ( ! $post ) {
    return false;
  }

  // If post is an integer (ID), try to load the post
  if ( is_numeric( $post ) ) {
    $post_id = (int) $post;

    // Try to load from Backdrop if available
    if ( function_exists( 'node_load' ) ) {
      $node = node_load( $post_id );
      if ( $node && is_object( $node ) ) {
        $post = $node;
      } else {
        return false;
      }
    } else {
      // Can't load post by ID without node_load
      return false;
    }
  }

  // Now we should have a post object
  if ( ! is_object( $post ) ) {
    return false;
  }

  // Get post ID for taxonomy lookup
  $post_id = 0;
  if ( isset( $post->ID ) ) {
    $post_id = (int) $post->ID;
  } elseif ( isset( $post->nid ) ) {
    $post_id = (int) $post->nid;
  }

  // Check if post has a post_format property (pre-loaded)
  if ( isset( $post->post_format ) && ! empty( $post->post_format ) ) {
    return $post->post_format;
  }

  // Try to get format from post_format taxonomy
  // In WordPress, this is stored as a taxonomy term
  if ( $post_id > 0 && function_exists( 'get_the_terms' ) ) {
    $terms = get_the_terms( $post_id, 'post_format' );
    if ( is_array( $terms ) && ! empty( $terms ) ) {
      $format_term = reset( $terms );
      if ( is_object( $format_term ) && isset( $format_term->slug ) ) {
        // WordPress stores format as 'post-format-{format}'
        // We need to extract the format part
        $slug = $format_term->slug;
        if ( strpos( $slug, 'post-format-' ) === 0 ) {
          return str_replace( 'post-format-', '', $slug );
        }
        return $slug;
      }
    }
  }

  // Check Backdrop taxonomy system if available
  if ( $post_id > 0 && function_exists( 'taxonomy_term_load_multiple' ) && function_exists( 'field_get_items' ) ) {
    // Check for a post_format field
    $field_items = field_get_items( 'node', $post, 'field_post_format' );
    if ( is_array( $field_items ) && ! empty( $field_items ) ) {
      $tid = $field_items[0]['tid'];
      $term = taxonomy_term_load( $tid );
      if ( $term && isset( $term->name ) ) {
        // Convert term name to format slug
        return sanitize_format_slug( $term->name );
      }
    }
  }

  // Default to 'standard' format
  return 'standard';
}

/**
 * Retrieve the date on which the post was written.
 *
 * WordPress Behavior:
 * - Returns formatted date string based on format parameter
 * - Uses post_date from post object
 * - Default format from get_option('date_format')
 * - Returns empty string if post doesn't exist or has no date
 * - Unlike the_date(), this always returns the date (no same-date checking)
 *
 * Backdrop Mapping:
 * - Uses $node->created timestamp from Backdrop
 * - Maps to $post->post_date in WP_Post objects (already converted)
 * - Formats using PHP date() function
 * - Supports all WordPress date format strings
 *
 * @since WordPress 0.71
 * @since WP2BD 1.0.0
 *
 * @global object $wp_post The current post object (WP2BD compatibility).
 *
 * @param string                  $format Optional. PHP date format. Default is date format option.
 * @param int|WP_Post|object|null $post   Optional. Post ID or post object. Default is global $post.
 * @return string Formatted date string or empty string on failure.
 */
function get_the_date( $format = '', $post = null ) {
  global $wp_post;

  // If no post provided, use global $wp_post
  if ( null === $post ) {
    $post = isset( $wp_post ) ? $wp_post : null;
  }

  // If we still don't have a post, return empty string
  if ( ! $post ) {
    return '';
  }

  // If post is an integer (ID), try to load the post
  if ( is_numeric( $post ) ) {
    $post_id = (int) $post;

    // Try to load from Backdrop if available
    if ( function_exists( 'node_load' ) ) {
      $node = node_load( $post_id );
      if ( $node && is_object( $node ) ) {
        $post = $node;
      } else {
        return '';
      }
    } else {
      // Can't load post by ID without node_load
      return '';
    }
  }

  // Now we should have a post object
  if ( ! is_object( $post ) ) {
    return '';
  }

  // Get the date value
  $the_date = '';

  // Check for WordPress-style post_date property (Y-m-d H:i:s format)
  if ( isset( $post->post_date ) && ! empty( $post->post_date ) ) {
    $the_date = $post->post_date;
  }
  // Check for Backdrop-style created timestamp
  elseif ( isset( $post->created ) && ! empty( $post->created ) ) {
    // Convert timestamp to MySQL datetime format
    $the_date = date( 'Y-m-d H:i:s', $post->created );
  }

  // If no date found, return empty string
  if ( empty( $the_date ) ) {
    return '';
  }

  // Determine format to use
  if ( empty( $format ) ) {
    // Use WordPress date_format option if available
    $format = function_exists( 'get_option' ) ? get_option( 'date_format' ) : '';
    // Fallback to common format
    if ( empty( $format ) ) {
      $format = 'F j, Y'; // e.g., "March 10, 2001"
    }
  }

  // Convert MySQL datetime to timestamp for formatting
  $timestamp = strtotime( $the_date );
  if ( $timestamp === false ) {
    return '';
  }

  // Format the date
  $formatted_date = date( $format, $timestamp );

  // Apply 'get_the_date' filter if available
  if ( function_exists( 'apply_filters' ) ) {
    $post_id = isset( $post->ID ) ? $post->ID : ( isset( $post->nid ) ? $post->nid : 0 );
    $formatted_date = apply_filters( 'get_the_date', $formatted_date, $format, $post_id );
  }

  return $formatted_date;
}

/**
 * Retrieve the time at which the post was written.
 *
 * WordPress Behavior:
 * - Returns formatted time string based on format parameter
 * - Uses post_date from post object
 * - Default format from get_option('time_format')
 * - Returns empty string if post doesn't exist or has no date
 *
 * Backdrop Mapping:
 * - Uses $node->created timestamp from Backdrop
 * - Maps to $post->post_date in WP_Post objects (already converted)
 * - Formats using PHP date() function
 * - Supports all WordPress time format strings
 *
 * @since WordPress 1.5.0
 * @since WP2BD 1.0.0
 *
 * @global object $wp_post The current post object (WP2BD compatibility).
 *
 * @param string                  $format Optional. PHP date format. Default is time format option.
 * @param int|WP_Post|object|null $post   Optional. Post ID or post object. Default is global $post.
 * @return string Formatted time string or empty string on failure.
 */
function get_the_time( $format = '', $post = null ) {
  global $wp_post;

  // If no post provided, use global $wp_post
  if ( null === $post ) {
    $post = isset( $wp_post ) ? $wp_post : null;
  }

  // If we still don't have a post, return empty string
  if ( ! $post ) {
    return '';
  }

  // If post is an integer (ID), try to load the post
  if ( is_numeric( $post ) ) {
    $post_id = (int) $post;

    // Try to load from Backdrop if available
    if ( function_exists( 'node_load' ) ) {
      $node = node_load( $post_id );
      if ( $node && is_object( $node ) ) {
        $post = $node;
      } else {
        return '';
      }
    } else {
      // Can't load post by ID without node_load
      return '';
    }
  }

  // Now we should have a post object
  if ( ! is_object( $post ) ) {
    return '';
  }

  // Get the date value
  $the_time = '';

  // Check for WordPress-style post_date property (Y-m-d H:i:s format)
  if ( isset( $post->post_date ) && ! empty( $post->post_date ) ) {
    $the_time = $post->post_date;
  }
  // Check for Backdrop-style created timestamp
  elseif ( isset( $post->created ) && ! empty( $post->created ) ) {
    // Convert timestamp to MySQL datetime format
    $the_time = date( 'Y-m-d H:i:s', $post->created );
  }

  // If no time found, return empty string
  if ( empty( $the_time ) ) {
    return '';
  }

  // Determine format to use
  if ( empty( $format ) ) {
    // Use WordPress time_format option if available
    $format = function_exists( 'get_option' ) ? get_option( 'time_format' ) : '';
    // Fallback to common format
    if ( empty( $format ) ) {
      $format = 'g:i a'; // e.g., "5:32 pm"
    }
  }

  // Convert MySQL datetime to timestamp for formatting
  $timestamp = strtotime( $the_time );
  if ( $timestamp === false ) {
    return '';
  }

  // Format the time
  $formatted_time = date( $format, $timestamp );

  // Apply 'get_the_time' filter if available
  if ( function_exists( 'apply_filters' ) ) {
    $post_id = isset( $post->ID ) ? $post->ID : ( isset( $post->nid ) ? $post->nid : 0 );
    $formatted_time = apply_filters( 'get_the_time', $formatted_time, $format, $post_id );
  }

  return $formatted_time;
}

/**
 * Retrieve the post author.
 *
 * WordPress Behavior:
 * - Returns the display name of the current post's author
 * - Uses global $post if no parameter
 * - Returns empty string if no author or post doesn't exist
 * - Gets author from $post->post_author (user ID)
 * - Loads user data and returns display_name
 *
 * Backdrop Mapping:
 * - Uses $node->uid (Backdrop user ID)
 * - Maps to $post->post_author in WP_Post objects
 * - Uses user_load() to get Backdrop user object
 * - Returns user's display name or username
 * - Handles anonymous users (uid = 0)
 *
 * @since WordPress 1.5.0
 * @since WP2BD 1.0.0
 *
 * @global object $wp_post The current post object (WP2BD compatibility).
 *
 * @return string The author's display name.
 */
function get_the_author() {
  global $wp_post, $authordata;

  // Get the current post
  $post = isset( $wp_post ) ? $wp_post : null;

  // If no post, return empty string
  if ( ! $post || ! is_object( $post ) ) {
    return '';
  }

  // Get author ID from post
  $author_id = 0;
  if ( isset( $post->post_author ) && ! empty( $post->post_author ) ) {
    $author_id = (int) $post->post_author;
  } elseif ( isset( $post->uid ) && ! empty( $post->uid ) ) {
    // Backdrop-style uid
    $author_id = (int) $post->uid;
  }

  // If no author ID, return empty string
  if ( $author_id <= 0 ) {
    return '';
  }

  // Try to use global $authordata if available and matches
  if ( isset( $authordata ) && is_object( $authordata ) ) {
    if ( isset( $authordata->ID ) && $authordata->ID == $author_id ) {
      // Use display_name from authordata
      if ( isset( $authordata->display_name ) && ! empty( $authordata->display_name ) ) {
        return $authordata->display_name;
      }
      // Fallback to user_login
      if ( isset( $authordata->user_login ) && ! empty( $authordata->user_login ) ) {
        return $authordata->user_login;
      }
    }
  }

  // Load user data
  $user = null;
  if ( function_exists( 'user_load' ) ) {
    // Backdrop's user_load()
    $user = user_load( $author_id );
  } elseif ( function_exists( 'get_userdata' ) ) {
    // WordPress's get_userdata()
    $user = get_userdata( $author_id );
  }

  // If user not found, return empty string
  if ( ! $user || ! is_object( $user ) ) {
    return '';
  }

  // Extract display name from user object
  $display_name = '';

  // Try WordPress-style display_name
  if ( isset( $user->display_name ) && ! empty( $user->display_name ) ) {
    $display_name = $user->display_name;
  }
  // Try WordPress-style user_login
  elseif ( isset( $user->user_login ) && ! empty( $user->user_login ) ) {
    $display_name = $user->user_login;
  }
  // Try Backdrop-style name (username)
  elseif ( isset( $user->name ) && ! empty( $user->name ) ) {
    $display_name = $user->name;
  }

  // Apply 'the_author' filter if available
  if ( function_exists( 'apply_filters' ) ) {
    $display_name = apply_filters( 'the_author', $display_name );
  }

  return $display_name;
}

/**
 * Retrieve the requested data of the author.
 *
 * WordPress Behavior:
 * - Returns specific field from user object
 * - Can specify user_id or uses current post author
 * - Returns empty string if field doesn't exist or user not found
 * - Commonly used fields: display_name, user_email, user_login, first_name, last_name, description, ID
 *
 * Backdrop Mapping:
 * - Uses user_load() to get Backdrop user object
 * - Maps WordPress user fields to Backdrop equivalents:
 *   - display_name → name (Backdrop username)
 *   - user_email → mail
 *   - user_login → name
 *   - ID → uid
 *   - first_name → field_first_name or profile field
 *   - last_name → field_last_name or profile field
 *   - description → field_bio or signature
 *
 * @since WordPress 2.8.0
 * @since WP2BD 1.0.0
 *
 * @global object $wp_post     The current post object (WP2BD compatibility).
 * @global object $authordata  The author user object (WordPress compatibility).
 *
 * @param string   $field   Optional. The user field to retrieve. Default empty (returns display_name).
 * @param int|null $user_id Optional. User ID. Default null (uses current post author).
 * @return string The author's field value.
 */
function get_the_author_meta( $field = '', $user_id = null ) {
  global $wp_post, $authordata;

  // Determine which user to get data for
  if ( null === $user_id ) {
    // No user ID provided - try to get from current post
    $post = isset( $wp_post ) ? $wp_post : null;

    if ( $post && is_object( $post ) ) {
      // Get author ID from post
      if ( isset( $post->post_author ) && ! empty( $post->post_author ) ) {
        $user_id = (int) $post->post_author;
      } elseif ( isset( $post->uid ) && ! empty( $post->uid ) ) {
        // Backdrop-style uid
        $user_id = (int) $post->uid;
      }
    }

    // Still no user ID - try global $authordata
    if ( ! $user_id && isset( $authordata ) && is_object( $authordata ) ) {
      if ( isset( $authordata->ID ) ) {
        $user_id = (int) $authordata->ID;
      } elseif ( isset( $authordata->uid ) ) {
        $user_id = (int) $authordata->uid;
      }
    }
  }

  // If we still don't have a user ID, return empty string
  if ( ! $user_id || $user_id <= 0 ) {
    return '';
  }

  // Load user data
  $user = null;
  if ( function_exists( 'user_load' ) ) {
    // Backdrop's user_load()
    $user = user_load( $user_id );
  } elseif ( function_exists( 'get_userdata' ) ) {
    // WordPress's get_userdata()
    $user = get_userdata( $user_id );
  }

  // If user not found, return empty string
  if ( ! $user || ! is_object( $user ) ) {
    return '';
  }

  // Default field to 'display_name' if empty
  if ( empty( $field ) ) {
    $field = 'display_name';
  }

  // Get the requested field
  $value = '';

  switch ( $field ) {
    case 'ID':
      // User ID
      if ( isset( $user->ID ) ) {
        $value = $user->ID;
      } elseif ( isset( $user->uid ) ) {
        $value = $user->uid;
      }
      break;

    case 'display_name':
      // Display name
      if ( isset( $user->display_name ) && ! empty( $user->display_name ) ) {
        $value = $user->display_name;
      } elseif ( isset( $user->name ) && ! empty( $user->name ) ) {
        // Backdrop username as fallback
        $value = $user->name;
      }
      break;

    case 'user_login':
      // Username/login
      if ( isset( $user->user_login ) && ! empty( $user->user_login ) ) {
        $value = $user->user_login;
      } elseif ( isset( $user->name ) && ! empty( $user->name ) ) {
        // Backdrop username
        $value = $user->name;
      }
      break;

    case 'user_email':
    case 'email':
      // Email address
      if ( isset( $user->user_email ) && ! empty( $user->user_email ) ) {
        $value = $user->user_email;
      } elseif ( isset( $user->mail ) && ! empty( $user->mail ) ) {
        // Backdrop mail field
        $value = $user->mail;
      }
      break;

    case 'first_name':
      // First name
      if ( isset( $user->first_name ) && ! empty( $user->first_name ) ) {
        $value = $user->first_name;
      }
      // Check Backdrop profile field
      elseif ( isset( $user->field_first_name ) ) {
        $value = _wp2bd_extract_field_value( $user->field_first_name );
      }
      break;

    case 'last_name':
      // Last name
      if ( isset( $user->last_name ) && ! empty( $user->last_name ) ) {
        $value = $user->last_name;
      }
      // Check Backdrop profile field
      elseif ( isset( $user->field_last_name ) ) {
        $value = _wp2bd_extract_field_value( $user->field_last_name );
      }
      break;

    case 'description':
    case 'user_description':
      // Bio/description
      if ( isset( $user->description ) && ! empty( $user->description ) ) {
        $value = $user->description;
      } elseif ( isset( $user->user_description ) && ! empty( $user->user_description ) ) {
        $value = $user->user_description;
      }
      // Check Backdrop signature or bio field
      elseif ( isset( $user->signature ) && ! empty( $user->signature ) ) {
        $value = $user->signature;
      } elseif ( isset( $user->field_bio ) ) {
        $value = _wp2bd_extract_field_value( $user->field_bio );
      }
      break;

    case 'user_url':
    case 'url':
      // Website URL
      if ( isset( $user->user_url ) && ! empty( $user->user_url ) ) {
        $value = $user->user_url;
      } elseif ( isset( $user->url ) && ! empty( $user->url ) ) {
        $value = $user->url;
      }
      // Check Backdrop field
      elseif ( isset( $user->field_url ) ) {
        $value = _wp2bd_extract_field_value( $user->field_url );
      }
      break;

    case 'nickname':
      // Nickname
      if ( isset( $user->nickname ) && ! empty( $user->nickname ) ) {
        $value = $user->nickname;
      }
      // Fallback to username
      elseif ( isset( $user->name ) && ! empty( $user->name ) ) {
        $value = $user->name;
      }
      break;

    default:
      // Try to get the field directly from user object
      if ( isset( $user->$field ) ) {
        $value = $user->$field;
        // If it's a Backdrop field array, extract value
        if ( is_array( $value ) ) {
          $value = _wp2bd_extract_field_value( $value );
        }
      }
      break;
  }

  // Convert to string
  $value = (string) $value;

  // Apply 'get_the_author_{$field}' filter if available
  if ( function_exists( 'apply_filters' ) ) {
    $value = apply_filters( "get_the_author_{$field}", $value, $user_id );
  }

  return $value;
}

/**
 * Helper function to extract value from Backdrop field array.
 *
 * Backdrop stores field values in complex arrays. This helper
 * extracts the actual value from the field structure.
 *
 * @since WP2BD 1.0.0
 * @access private
 *
 * @param mixed $field_value The field value (could be array or string).
 * @return string The extracted value.
 */
function _wp2bd_extract_field_value( $field_value ) {
  // If already a string, return it
  if ( is_string( $field_value ) ) {
    return $field_value;
  }

  // If not an array, convert to string
  if ( ! is_array( $field_value ) ) {
    return (string) $field_value;
  }

  // Check for Backdrop Field API structure: field[LANGUAGE_NONE][0]['value']
  // Try 'und' (undefined language) first
  if ( isset( $field_value['und'][0]['value'] ) ) {
    return $field_value['und'][0]['value'];
  }

  // Try direct [0]['value'] structure
  if ( isset( $field_value[0]['value'] ) ) {
    return $field_value[0]['value'];
  }

  // Try direct [0] if it's a string
  if ( isset( $field_value[0] ) && is_string( $field_value[0] ) ) {
    return $field_value[0];
  }

  // Try 'value' key directly
  if ( isset( $field_value['value'] ) ) {
    return $field_value['value'];
  }

  // Give up and return empty string
  return '';
}

/**
 * Helper function to sanitize post format slug.
 *
 * Converts a format name to a valid post format slug.
 *
 * @since WP2BD 1.0.0
 * @access private
 *
 * @param string $format_name The format name to sanitize.
 * @return string Sanitized format slug.
 */
function sanitize_format_slug( $format_name ) {
  // Convert to lowercase
  $slug = strtolower( $format_name );

  // Replace spaces and special chars with hyphens
  $slug = preg_replace( '/[^a-z0-9]+/', '-', $slug );

  // Remove leading/trailing hyphens
  $slug = trim( $slug, '-' );

  // Valid formats in WordPress
  $valid_formats = array( 'aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat' );

  // Check if slug matches exactly
  if ( in_array( $slug, $valid_formats, true ) ) {
    return $slug;
  }

  // Check if slug starts with a valid format (e.g., "gallery-post" → "gallery")
  foreach ( $valid_formats as $format ) {
    if ( strpos( $slug, $format ) === 0 ) {
      return $format;
    }
  }

  // Default to 'standard'
  return 'standard';
}
