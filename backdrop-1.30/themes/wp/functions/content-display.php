<?php
/**
 * WordPress Content Display Functions for Backdrop
 *
 * Functions that display post/node content.
 * Maps WordPress post display functions to Backdrop's node system.
 *
 * @package WP2BD
 * @subpackage Content Display
 */

/**
 * Retrieve post title.
 *
 * If the post is protected and the visitor is not an admin, then "Protected"
 * will be displayed before the post title. If the post is private, then
 * "Private" will be displayed before the post title.
 *
 * @since WordPress 0.71
 * @since WP2BD 1.0.0
 *
 * @param int|WP_Post|object $post Optional. Post ID or post object. Default is global $post.
 * @return string The post title. Empty string if no title or post not found.
 */
function get_the_title( $post = null ) {
  global $wp_post;

  // If no post provided, use global $post (WordPress) or $wp_post (WP2BD)
  if ( null === $post ) {
    $post = isset( $wp_post ) ? $wp_post : null;
  }
  // If post is an ID, load it (for future WP_Post compatibility)
  elseif ( is_numeric( $post ) ) {
    // For now, we'll create a simple object structure
    // In full implementation, this would load from Backdrop's node system
    $post = (object) array( 'ID' => $post, 'post_title' => '' );
  }

  // Handle missing post gracefully
  if ( ! $post || ! is_object( $post ) ) {
    return '';
  }

  // Get title from the post object
  // Maps WordPress $post->post_title to Backdrop $node->title
  $title = '';

  if ( isset( $post->post_title ) ) {
    // WordPress-style post object
    $title = $post->post_title;
  }
  elseif ( isset( $post->title ) ) {
    // Backdrop-style node object
    $title = $post->title;
  }

  // Handle empty title gracefully
  if ( empty( $title ) ) {
    $title = '';
  }

  // Apply 'the_title' filter
  // This allows plugins/themes to modify the title
  if ( function_exists( 'apply_filters' ) ) {
    $title = apply_filters( 'the_title', $title, isset( $post->ID ) ? $post->ID : ( isset( $post->nid ) ? $post->nid : 0 ) );
  }

  return $title;
}

/**
 * Display or retrieve the current post title with optional markup.
 *
 * @since WordPress 0.71
 * @since WP2BD 1.0.0
 *
 * @param string $before Optional. Markup to prepend to the title. Default empty.
 * @param string $after  Optional. Markup to append to the title. Default empty.
 * @param bool   $echo   Optional. Whether to echo or return the title. Default true for echo.
 * @return void|string Void if $echo is true, the title if $echo is false.
 */
function the_title( $before = '', $after = '', $echo = true ) {
  $title = get_the_title();

  // Only add before/after if title exists
  if ( strlen( $title ) > 0 ) {
    $title = $before . $title . $after;
  }

  if ( $echo ) {
    echo $title;
  }
  else {
    return $title;
  }
}

/**
 * Retrieves the full permalink for the current post or post ID.
 *
 * WordPress Behavior:
 * - Returns the full URL to the post
 * - Respects permalink structure settings
 * - Handles custom post types
 * - Applies 'post_link' filter
 *
 * Backdrop Mapping:
 * - Uses url() function to generate absolute URLs
 * - Maps post ID to node ID (nid)
 * - Checks for path aliases via backdrop_get_path_alias()
 * - Falls back to 'node/[nid]' if no alias exists
 *
 * @since WordPress 1.0.0
 * @since WP2BD 1.0.0
 *
 * @param int|WP_Post|object|null $post Optional. Post ID or post object. Default is global $post.
 * @return string|false The permalink URL, or false on failure.
 */
function get_permalink( $post = null ) {
  global $wp_post;

  // If no post provided, use global $post (WordPress) or $wp_post (WP2BD)
  if ( null === $post ) {
    $post = isset( $wp_post ) ? $wp_post : null;
  }

  // Handle post ID (integer) vs post object
  if ( is_numeric( $post ) ) {
    $post_id = (int) $post;
    // Create a minimal post object for filter compatibility
    $post_obj = (object) array( 'ID' => $post_id );
  }
  elseif ( is_object( $post ) ) {
    // Handle both WP_Post style (ID property) and Backdrop node style (nid property)
    if ( isset( $post->ID ) ) {
      $post_id = (int) $post->ID;
      $post_obj = $post;
    }
    elseif ( isset( $post->nid ) ) {
      $post_id = (int) $post->nid;
      $post_obj = $post;
    }
    else {
      return false;
    }
  }
  else {
    return false;
  }

  // Check if the post ID is valid
  if ( $post_id <= 0 ) {
    return false;
  }

  // Generate the path - first try to get the path alias
  // In Backdrop, path aliases are stored and retrieved via the path system
  $path = 'node/' . $post_id;

  // Check for path alias using Backdrop's path system
  // backdrop_get_path_alias() returns the alias if exists, otherwise returns the original path
  if ( function_exists( 'backdrop_get_path_alias' ) ) {
    $alias = backdrop_get_path_alias( $path );
    if ( $alias && $alias !== $path ) {
      $path = $alias;
    }
  }

  // Generate absolute URL using Backdrop's url() function
  // The 'absolute' option ensures we get a full URL with domain
  $permalink = '';
  if ( function_exists( 'url' ) ) {
    $permalink = url( $path, array( 'absolute' => TRUE ) );
  }
  else {
    // Fallback if url() function is not available
    // This shouldn't happen in a real Backdrop environment
    global $base_url;
    $base = isset( $base_url ) ? $base_url : 'http://localhost';
    $permalink = rtrim( $base, '/' ) . '/' . ltrim( $path, '/' );
  }

  // Apply WordPress 'post_link' filter for compatibility
  // This allows themes/plugins to modify the permalink
  // Filter signature: apply_filters( 'post_link', string $permalink, WP_Post $post, bool $leavename )
  if ( function_exists( 'apply_filters' ) ) {
    $permalink = apply_filters( 'post_link', $permalink, $post_obj, false );
  }

  return $permalink;
}

/**
 * Displays the permalink for the current post.
 *
 * WordPress Behavior:
 * - Echoes the full URL to the post
 * - Wrapper around get_permalink()
 * - Commonly used in templates within href attributes
 *
 * @since WordPress 1.2.0
 * @since WP2BD 1.0.0
 *
 * @param int|WP_Post|object|null $post Optional. Post ID or post object. Default is global $post.
 * @return void Echoes the permalink URL.
 */
function the_permalink( $post = null ) {
  $permalink = get_permalink( $post );

  if ( $permalink ) {
    // Escape URL for safe output
    if ( function_exists( 'esc_url' ) ) {
      echo esc_url( $permalink );
    }
    else {
      // Fallback escaping if esc_url not available
      echo htmlspecialchars( $permalink, ENT_QUOTES, 'UTF-8' );
    }
  }
}

/**
 * Retrieve the ID of the current post.
 *
 * WordPress Behavior:
 * - Returns the post ID as an integer
 * - Returns false if no post is available
 * - Commonly used in The Loop
 *
 * Backdrop Mapping:
 * - Maps WordPress $post->ID to Backdrop $node->nid
 * - Handles both WordPress-style and Backdrop-style objects
 *
 * @since WordPress 2.1.0
 * @since WP2BD 1.0.0
 *
 * @param int|WP_Post|object|null $post Optional. Post ID or post object. Default is global $post.
 * @return int|false The post ID on success, false on failure.
 */
function get_the_ID( $post = null ) {
  global $wp_post;

  // If no post provided, use global $post (WordPress) or $wp_post (WP2BD)
  if ( null === $post ) {
    $post = isset( $wp_post ) ? $wp_post : null;
  }

  // Handle missing post gracefully
  if ( ! $post || ! is_object( $post ) ) {
    return false;
  }

  // If post is already an integer ID, return it
  if ( is_numeric( $post ) ) {
    return (int) $post;
  }

  // Get ID from the post object
  // Maps WordPress $post->ID to Backdrop $node->nid
  if ( isset( $post->ID ) ) {
    // WordPress-style post object
    return (int) $post->ID;
  }
  elseif ( isset( $post->nid ) ) {
    // Backdrop-style node object
    return (int) $post->nid;
  }

  // No valid ID found
  return false;
}

/**
 * Display the ID of the current post.
 *
 * WordPress Behavior:
 * - Echoes the post ID
 * - Wrapper around get_the_ID()
 * - Returns void
 *
 * @since WordPress 0.71
 * @since WP2BD 1.0.0
 *
 * @param int|WP_Post|object|null $post Optional. Post ID or post object. Default is global $post.
 * @return void Echoes the post ID.
 */
function the_ID( $post = null ) {
  $id = get_the_ID( $post );

  if ( $id !== false ) {
    echo $id;
  }
}

/**
 * Retrieve the post content.
 *
 * WordPress Behavior:
 * - Returns the raw post content (without filters applied)
 * - Handles <!--more--> tag for excerpting
 * - Handles multi-page content with <!--nextpage--> tags
 * - Respects global $page, $more, $pages, $multipage variables
 * - Generates "Read More" link when appropriate
 *
 * Backdrop Mapping:
 * - Maps WordPress $post->post_content to Backdrop $node->body['und'][0]['value']
 * - Falls back to $node->body if simplified structure
 * - Handles both filtered and unfiltered content
 *
 * @since WordPress 0.71
 * @since WP2BD 1.0.0
 *
 * @global int   $page      Page number of a single post/page.
 * @global int   $more      Boolean indicator for whether single post/page is being viewed.
 * @global bool  $preview   Whether post/page is in preview mode.
 * @global array $pages     Array of all pages in post/page. Each array element contains part of the content separated by the <!--nextpage--> tag.
 * @global int   $multipage Boolean indicator for whether multiple pages are in play.
 *
 * @param string $more_link_text Optional. Content for when there is more text. Default null.
 * @param bool   $strip_teaser   Optional. Strip teaser content before the more text. Default false.
 * @return string The post content.
 */
function get_the_content( $more_link_text = null, $strip_teaser = false ) {
  global $wp_post, $page, $more, $preview, $pages, $multipage;

  // Get the current post
  $post = isset( $wp_post ) ? $wp_post : null;

  if ( ! $post || ! is_object( $post ) ) {
    return '';
  }

  // Initialize global variables if not set
  // These are normally set by setup_postdata() in WordPress
  if ( ! isset( $page ) ) {
    $page = 1;
  }
  if ( ! isset( $more ) ) {
    $more = 0; // Default to teaser mode (list view)
  }
  if ( ! isset( $preview ) ) {
    $preview = false;
  }

  // Get the post content
  // Handle both WordPress-style ($post->post_content) and Backdrop-style ($node->body)
  $content = '';
  if ( isset( $post->post_content ) ) {
    $content = $post->post_content;
  } elseif ( isset( $post->body ) ) {
    // Backdrop node body can be a string or array
    if ( is_string( $post->body ) ) {
      $content = $post->body;
    } elseif ( is_array( $post->body ) && isset( $post->body['und'][0]['value'] ) ) {
      // Backdrop field API structure
      $content = $post->body['und'][0]['value'];
    }
  }

  // If $pages array is not set, set it up now
  // This splits content by <!--nextpage--> tags
  if ( ! isset( $pages ) || ! is_array( $pages ) ) {
    if ( false !== strpos( $content, '<!--nextpage-->' ) ) {
      // Clean up variations of nextpage tag
      $content = str_replace( "\n<!--nextpage-->\n", '<!--nextpage-->', $content );
      $content = str_replace( "\n<!--nextpage-->", '<!--nextpage-->', $content );
      $content = str_replace( "<!--nextpage-->\n", '<!--nextpage-->', $content );

      // Ignore nextpage at the beginning of the content
      if ( 0 === strpos( $content, '<!--nextpage-->' ) ) {
        $content = substr( $content, 15 );
      }

      $pages = explode( '<!--nextpage-->', $content );
      $multipage = 1;
    } else {
      $pages = array( $content );
      $multipage = 0;
    }
  }

  // Default more link text
  if ( null === $more_link_text ) {
    $more_link_text = '(more&hellip;)';
  }

  $output = '';
  $has_teaser = false;

  // Handle password-protected posts (simplified for now)
  // In full WordPress, this would check post_password_required()
  // and return get_the_password_form()

  // If requested page doesn't exist, give them the highest numbered page that does
  if ( $page > count( $pages ) ) {
    $page = count( $pages );
  }

  // Get content for current page (pages are 1-indexed)
  $page_content = $pages[ $page - 1 ];

  // Check for <!--more--> tag
  if ( preg_match( '/<!--more(.*?)?-->/', $page_content, $matches ) ) {
    $page_content = explode( $matches[0], $page_content, 2 );

    // Check if custom more link text was provided in the more tag
    if ( ! empty( $matches[1] ) && ! empty( $more_link_text ) ) {
      // Strip tags and clean the custom more text
      $more_link_text = strip_tags( trim( $matches[1] ) );
    }

    $has_teaser = true;
  } else {
    $page_content = array( $page_content );
  }

  $teaser = $page_content[0];

  // If we're in "more" mode and strip_teaser is true, don't show teaser
  if ( $more && $strip_teaser && $has_teaser ) {
    $teaser = '';
  }

  // Check for <!--noteaser--> tag
  if ( false !== strpos( $content, '<!--noteaser-->' ) && ( ! $multipage || $page == 1 ) ) {
    $strip_teaser = true;
    if ( $more && $has_teaser ) {
      $teaser = '';
    }
  }

  $output .= $teaser;

  // If there's content after the more tag
  if ( count( $page_content ) > 1 ) {
    if ( $more ) {
      // We're on single post view - show full content
      $post_id = get_the_ID( $post );
      $output .= '<span id="more-' . $post_id . '"></span>' . $page_content[1];
    } else {
      // We're on list/archive view - show "Read More" link
      if ( ! empty( $more_link_text ) ) {
        $post_id = get_the_ID( $post );
        $permalink = get_permalink( $post );

        // Build the "Read More" link
        $more_link = ' <a href="' . esc_attr( $permalink ) . '#more-' . $post_id . '" class="more-link">' . $more_link_text . '</a>';

        // Apply filter to allow customization
        if ( function_exists( 'apply_filters' ) ) {
          $more_link = apply_filters( 'the_content_more_link', $more_link, $more_link_text );
        }

        $output .= $more_link;
      }

      // Balance HTML tags to prevent broken markup
      // In WordPress, this uses force_balance_tags()
      // For now, we'll skip this as it requires additional implementation
    }
  }

  // Preview fix for JavaScript bug with foreign languages
  if ( $preview ) {
    $output = preg_replace_callback( '/\%u([0-9A-F]{4})/', '_wp2bd_convert_urlencoded_to_entities', $output );
  }

  return $output;
}

/**
 * Preview fix for JavaScript bug with foreign languages.
 *
 * Converts URL-encoded Unicode characters to HTML entities.
 *
 * @since WordPress 3.1.0
 * @since WP2BD 1.0.0
 * @access private
 *
 * @param array $match Match array from preg_replace_callback.
 * @return string HTML entity.
 */
function _wp2bd_convert_urlencoded_to_entities( $match ) {
  return '&#' . base_convert( $match[1], 16, 10 ) . ';';
}

/**
 * Display the post content.
 *
 * WordPress Behavior:
 * - Outputs the post content with filters applied
 * - Critical filter: 'the_content' - adds paragraphs, processes shortcodes, handles embeds
 * - Escapes ]]> for CDATA sections
 * - Echoes the content (does not return)
 *
 * Backdrop Mapping:
 * - Applies text format filters via 'the_content' hook
 * - Allows modules to modify content before display
 * - Handles both WordPress and Backdrop filter systems
 *
 * @since WordPress 0.71
 * @since WP2BD 1.0.0
 *
 * @param string $more_link_text Optional. Content for when there is more text. Default null.
 * @param bool   $strip_teaser   Optional. Strip teaser content before the more text. Default false.
 * @return void Echoes the formatted post content.
 */
function the_content( $more_link_text = null, $strip_teaser = false ) {
  $content = get_the_content( $more_link_text, $strip_teaser );

  /**
   * Filters the post content.
   *
   * This is the most important filter in WordPress content display.
   * By default, it applies:
   * - wptexturize() - converts quotes to curly quotes, etc.
   * - convert_smilies() - converts text smilies to images
   * - convert_chars() - converts characters (Windows → proper)
   * - wpautop() - adds <p> and <br> tags (CRITICAL for formatting)
   * - shortcode_unautop() - prevents <p> tags around shortcodes
   * - prepend_attachment() - prepends attachment image on attachment pages
   * - do_shortcode() - processes [shortcodes]
   *
   * In WP2BD context, this should integrate with Backdrop's text format system.
   *
   * @since WordPress 0.71
   *
   * @param string $content Content of the current post.
   */
  if ( function_exists( 'apply_filters' ) ) {
    $content = apply_filters( 'the_content', $content );
  }

  // Escape ]]> for CDATA sections (XML safety)
  $content = str_replace( ']]>', ']]&gt;', $content );

  echo $content;
}

/**
 * Retrieves the post excerpt.
 *
 * WordPress Behavior:
 * - Returns the post excerpt from $post->post_excerpt
 * - If excerpt is empty, returns empty string (auto-generation via filter)
 * - Applies 'get_the_excerpt' filter (which typically hooks wp_trim_excerpt)
 * - Handles password-protected posts
 *
 * Backdrop Mapping:
 * - Maps $post->post_excerpt to Backdrop's body summary field
 * - Uses node body summary or auto-generates from body value
 *
 * @since WordPress 0.71
 * @since WP2BD 1.0.0
 *
 * @param int|WP_Post|object|null $post Optional. Post ID or post object. Default is global $post.
 * @return string The post excerpt.
 */
function get_the_excerpt( $post = null ) {
  global $wp_post;

  // If no post provided, use global $post (WordPress) or $wp_post (WP2BD)
  if ( null === $post ) {
    $post = isset( $wp_post ) ? $wp_post : null;
  }
  // If post is an ID, load it (for future WP_Post compatibility)
  elseif ( is_numeric( $post ) ) {
    // For now, we'll create a simple object structure
    // In full implementation, this would load from Backdrop's node system
    $post = (object) array( 'ID' => $post, 'post_excerpt' => '' );
  }

  // Handle missing post gracefully
  if ( ! $post || ! is_object( $post ) ) {
    return '';
  }

  // Get excerpt from the post object
  // Maps WordPress $post->post_excerpt to Backdrop $node->body['summary']
  $excerpt = '';

  if ( isset( $post->post_excerpt ) ) {
    // WordPress-style post object
    $excerpt = $post->post_excerpt;
  }
  elseif ( isset( $post->body ) && is_array( $post->body ) && isset( $post->body['summary'] ) ) {
    // Backdrop-style node object with body summary
    $excerpt = $post->body['summary'];
  }

  // Apply 'get_the_excerpt' filter
  // This allows plugins/themes to modify or auto-generate the excerpt
  // WordPress hooks wp_trim_excerpt() to this filter by default
  if ( function_exists( 'apply_filters' ) ) {
    $excerpt = apply_filters( 'get_the_excerpt', $excerpt, $post );
  }

  return $excerpt;
}

/**
 * Display the post excerpt.
 *
 * WordPress Behavior:
 * - Retrieves excerpt via get_the_excerpt()
 * - Applies 'the_excerpt' filter
 * - Echoes the result
 *
 * @since WordPress 0.71
 * @since WP2BD 1.0.0
 *
 * @return void Echoes the post excerpt.
 */
function the_excerpt() {
  $excerpt = get_the_excerpt();

  // Apply 'the_excerpt' filter
  // This is a separate filter from 'get_the_excerpt' for display-time modifications
  if ( function_exists( 'apply_filters' ) ) {
    $excerpt = apply_filters( 'the_excerpt', $excerpt );
  }

  echo $excerpt;
}

/**
 * Generates an excerpt from the content, if needed.
 *
 * WordPress Behavior:
 * - If $text is empty, auto-generates from post content
 * - Strips shortcodes and HTML tags
 * - Trims to 55 words (or custom length via 'excerpt_length' filter)
 * - Adds '[...]' or custom "more" text (via 'excerpt_more' filter)
 * - Applies 'wp_trim_excerpt' filter
 *
 * This function is typically hooked to the 'get_the_excerpt' filter.
 *
 * @since WordPress 1.5.0
 * @since WP2BD 1.0.0
 *
 * @param string $text Optional. The excerpt. If set to empty, an excerpt is generated.
 * @return string The excerpt.
 */
function wp_trim_excerpt( $text = '' ) {
  $raw_excerpt = $text;

  if ( '' == $text ) {
    // Get the post content
    // For WP2BD, we need to access the global post
    global $wp_post;

    if ( isset( $wp_post ) ) {
      // Get content from post object
      $content = '';
      if ( isset( $wp_post->post_content ) ) {
        $content = $wp_post->post_content;
      }
      elseif ( isset( $wp_post->body ) ) {
        // Backdrop node body field
        if ( is_array( $wp_post->body ) && isset( $wp_post->body['value'] ) ) {
          $content = $wp_post->body['value'];
        }
        elseif ( is_string( $wp_post->body ) ) {
          $content = $wp_post->body;
        }
      }

      $text = $content;

      // Strip shortcodes (simple implementation - just remove [shortcode] patterns)
      $text = preg_replace( '/\[.*?\]/', '', $text );

      // Strip HTML tags
      $text = wp_strip_all_tags( $text );

      // Replace special characters
      $text = str_replace( ']]>', ']]&gt;', $text );

      // Get excerpt length (default 55 words)
      $excerpt_length = 55;
      if ( function_exists( 'apply_filters' ) ) {
        $excerpt_length = apply_filters( 'excerpt_length', 55 );
      }

      // Get excerpt "more" string (default ' [...]')
      $excerpt_more = ' [&hellip;]';
      if ( function_exists( 'apply_filters' ) ) {
        $excerpt_more = apply_filters( 'excerpt_more', ' [&hellip;]' );
      }

      // Trim to specified number of words
      $text = wp_trim_words( $text, $excerpt_length, $excerpt_more );
    }
  }

  // Apply 'wp_trim_excerpt' filter
  if ( function_exists( 'apply_filters' ) ) {
    return apply_filters( 'wp_trim_excerpt', $text, $raw_excerpt );
  }

  return $text;
}

/**
 * Trims text to a certain number of words.
 *
 * WordPress Behavior:
 * - Strips all HTML tags
 * - Splits text into words (or characters for some languages)
 * - Takes first $num_words words
 * - Appends $more string if text was trimmed
 * - Applies 'wp_trim_words' filter
 *
 * @since WordPress 3.3.0
 * @since WP2BD 1.0.0
 *
 * @param string $text      Text to trim.
 * @param int    $num_words Number of words. Default 55.
 * @param string $more      Optional. What to append if $text needs to be trimmed. Default '&hellip;'.
 * @return string Trimmed text.
 */
function wp_trim_words( $text, $num_words = 55, $more = null ) {
  if ( null === $more ) {
    $more = '&hellip;';
  }

  $original_text = $text;

  // Strip all HTML tags
  $text = wp_strip_all_tags( $text );

  // Normalize whitespace and split into words
  $text = trim( preg_replace( "/[\n\r\t ]+/", ' ', $text ), ' ' );
  $words_array = preg_split( "/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY );

  // If we have more words than the limit, trim and add "more" string
  if ( count( $words_array ) > $num_words ) {
    array_pop( $words_array );
    $text = implode( ' ', $words_array );
    $text = $text . $more;
  }
  else {
    $text = implode( ' ', $words_array );
  }

  // Apply 'wp_trim_words' filter
  if ( function_exists( 'apply_filters' ) ) {
    return apply_filters( 'wp_trim_words', $text, $num_words, $more, $original_text );
  }

  return $text;
}

/**
 * Properly strip all HTML tags including script and style.
 *
 * WordPress Behavior:
 * - Removes all HTML tags from string
 * - Removes content from <script> and <style> tags
 * - Different from strip_tags() which doesn't remove script/style content
 *
 * @since WordPress 2.9.0
 * @since WP2BD 1.0.0
 *
 * @param string $string         String containing HTML tags.
 * @param bool   $remove_breaks  Optional. Whether to remove left over line breaks and white space chars. Default false.
 * @return string The processed string.
 */
function wp_strip_all_tags( $string, $remove_breaks = false ) {
  $string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
  $string = strip_tags( $string );

  if ( $remove_breaks ) {
    $string = preg_replace( '/[\r\n\t ]+/', ' ', $string );
  }

  return trim( $string );
}

/**
 * Retrieve the language attributes for the html tag.
 *
 * WordPress Behavior:
 * - Builds HTML attributes for the <html> tag
 * - Adds lang attribute with site language (e.g., lang="en-US")
 * - Adds dir attribute for text direction (dir="ltr" or dir="rtl")
 * - Supports both HTML5 and XHTML doctypes
 * - Applies 'language_attributes' filter
 *
 * Backdrop Mapping:
 * - Gets language from Backdrop's config system
 * - Checks language direction via Backdrop's language system
 * - Maps to global $language object if available
 * - Defaults to "en" language and "ltr" direction
 *
 * @since WordPress 2.5.0
 * @since WP2BD 1.0.0
 *
 * @param string $doctype Optional. The type of html document. Accepts 'xhtml' or 'html'. Default 'html'.
 * @return string A space-separated list of language attributes.
 */
function get_language_attributes( $doctype = 'html' ) {
  $attributes = array();

  // Determine text direction (LTR or RTL)
  // In WordPress, this checks is_rtl() function
  // In Backdrop, we check the global $language object or config
  $is_rtl = false;

  // Check if Backdrop's language system is available
  if ( isset( $GLOBALS['language'] ) && is_object( $GLOBALS['language'] ) ) {
    // Backdrop's language object has a 'direction' property
    // LANGUAGE_LTR = 0, LANGUAGE_RTL = 1
    if ( isset( $GLOBALS['language']->direction ) && $GLOBALS['language']->direction == 1 ) {
      $is_rtl = true;
    }
  }
  // Fallback: check if WordPress-style is_rtl() function exists
  elseif ( function_exists( 'is_rtl' ) && is_rtl() ) {
    $is_rtl = true;
  }

  // Add dir attribute if RTL
  if ( $is_rtl ) {
    $attributes[] = 'dir="rtl"';
  }

  // Get site language
  // Try multiple methods to get the language code
  $lang = '';

  // Method 1: Try Backdrop's global $language object
  if ( isset( $GLOBALS['language'] ) && is_object( $GLOBALS['language'] ) ) {
    if ( isset( $GLOBALS['language']->langcode ) ) {
      $lang = $GLOBALS['language']->langcode;
    }
    elseif ( isset( $GLOBALS['language']->language ) ) {
      $lang = $GLOBALS['language']->language;
    }
  }

  // Method 2: Try get_bloginfo() if available
  if ( empty( $lang ) && function_exists( 'get_bloginfo' ) ) {
    $lang = get_bloginfo( 'language' );
  }

  // Method 3: Try Backdrop's config system
  if ( empty( $lang ) && function_exists( 'config_get' ) ) {
    $lang = config_get( 'system.core', 'language_default' );
  }

  // Method 4: Default fallback
  if ( empty( $lang ) ) {
    $lang = 'en-US';
  }

  // Add language attribute
  if ( $lang ) {
    // For HTML5 (default), use lang attribute
    if ( get_option( 'html_type' ) == 'text/html' || $doctype == 'html' ) {
      $attributes[] = 'lang="' . esc_attr( $lang ) . '"';
    }

    // For XHTML, use xml:lang attribute (or both)
    if ( get_option( 'html_type' ) != 'text/html' || $doctype == 'xhtml' ) {
      $attributes[] = 'xml:lang="' . esc_attr( $lang ) . '"';
    }
  }

  $output = implode( ' ', $attributes );

  /**
   * Filters the language attributes for display in the html tag.
   *
   * This filter allows themes and modules to modify the language
   * and direction attributes before they are output.
   *
   * @since WordPress 2.5.0
   * @since WordPress 4.3.0 Added the $doctype parameter.
   * @since WP2BD 1.0.0
   *
   * @param string $output  A space-separated list of language attributes.
   * @param string $doctype The type of html document (xhtml|html).
   */
  if ( function_exists( 'apply_filters' ) ) {
    $output = apply_filters( 'language_attributes', $output, $doctype );
  }

  return $output;
}

/**
 * Display the language attributes for the html tag.
 *
 * WordPress Behavior:
 * - Echoes the output of get_language_attributes()
 * - Used in theme header templates within <html> tag
 * - Example: <html <?php language_attributes(); ?>>
 * - Outputs: <html lang="en-US" dir="ltr">
 *
 * Backdrop Usage:
 * - Replace Backdrop's RDF and language attribute logic
 * - Provides clean, semantic HTML5 attributes
 *
 * @since WordPress 2.1.0
 * @since WordPress 4.3.0 Converted into a wrapper for get_language_attributes().
 * @since WP2BD 1.0.0
 *
 * @param string $doctype Optional. The type of html document. Accepts 'xhtml' or 'html'. Default 'html'.
 * @return void Echoes the language attributes.
 */
function language_attributes( $doctype = 'html' ) {
  echo get_language_attributes( $doctype );
}

/**
 * Escapes a string for use in HTML attributes.
 *
 * Simple fallback implementation for esc_attr() if not available.
 *
 * @since WP2BD 1.0.0
 *
 * @param string $text The text to escape.
 * @return string Escaped text.
 */
if ( ! function_exists( 'esc_attr' ) ) {
  function esc_attr( $text ) {
    return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
  }
}

/**
 * Retrieve option value based on name of option.
 *
 * Simple fallback implementation for get_option() if not available.
 * Returns false for all options in basic implementation.
 *
 * @since WP2BD 1.0.0
 *
 * @param string $option  Name of option to retrieve.
 * @param mixed  $default Optional. Default value to return if option doesn't exist. Default false.
 * @return mixed Value of the option or default.
 */
if ( ! function_exists( 'get_option' ) ) {
  function get_option( $option, $default = false ) {
    // In a full Backdrop implementation, this would check Backdrop's config/variable system
    // For now, return defaults that make sense for modern HTML5
    if ( $option === 'html_type' ) {
      return 'text/html'; // Default to HTML5
    }
    return $default;
  }
}

/**
 * Display the classes for the post div.
 *
 * WordPress Behavior:
 * - Generates semantic CSS classes for post elements
 * - Includes post ID, type, status, format
 * - Adds taxonomy terms (categories, tags)
 * - Includes 'sticky' class for sticky posts
 * - Adds 'hentry' for microformats/hAtom compliance
 * - Supports custom classes via parameter
 * - Applies 'post_class' filter
 *
 * Backdrop Mapping:
 * - Maps WordPress post properties to Backdrop node properties
 * - Uses Backdrop taxonomy system for categories/tags
 * - Handles both WordPress-style and Backdrop-style objects
 *
 * @since WordPress 2.7.0
 * @since WP2BD 1.0.0
 *
 * @param string|array    $class   One or more classes to add to the class list.
 * @param int|WP_Post|object $post_id Optional. Post ID or post object. Default is global $post.
 * @return void Echoes the class attribute.
 */
function post_class( $class = '', $post_id = null ) {
  // Separates classes with a single space, collates classes for post DIV
  echo 'class="' . join( ' ', get_post_class( $class, $post_id ) ) . '"';
}

/**
 * Retrieve the classes for the post div as an array.
 *
 * The class names include:
 * - post-{$id}: Unique ID for the post
 * - type-{$post_type}: The post type (post, page, etc.)
 * - status-{$status}: Publication status (publish, draft, etc.)
 * - format-{$format}: Post format if supported (standard, aside, gallery, etc.)
 * - hentry: hAtom microformat class
 * - category-{$slug}: For each category the post belongs to
 * - tag-{$slug}: For each tag the post has
 * - sticky: If post is sticky (and on home page)
 *
 * WordPress Behavior:
 * - Returns array of CSS class names
 * - Handles custom post types and taxonomies
 * - Applies sanitization to all class names
 * - Uses 'tag-' prefix for post_tag taxonomy (backward compatibility)
 * - Filters through 'post_class' hook
 *
 * Backdrop Mapping:
 * - Maps $post->ID to $node->nid
 * - Maps $post->post_type to $node->type
 * - Maps $post->post_status to $node->status
 * - Uses taxonomy_get_term() for term data
 * - Falls back gracefully when taxonomy functions unavailable
 *
 * @since WordPress 2.7.0
 * @since WordPress 4.2.0 Custom taxonomy classes were added.
 * @since WP2BD 1.0.0
 *
 * @param string|array    $class   One or more classes to add to the class list.
 * @param int|WP_Post|object $post_id Optional. Post ID or post object. Default is global $post.
 * @return array Array of classes.
 */
function get_post_class( $class = '', $post_id = null ) {
  global $wp_post;

  // Get the post object
  $post = null;
  if ( null === $post_id ) {
    // Use global post if no parameter provided
    $post = isset( $wp_post ) ? $wp_post : null;
  } elseif ( is_numeric( $post_id ) ) {
    // Post ID provided - in full implementation, would load from database
    // For now, create minimal object
    $post = (object) array( 'ID' => (int) $post_id );
  } elseif ( is_object( $post_id ) ) {
    // Post object provided
    $post = $post_id;
  }

  // Start with custom classes
  $classes = array();

  if ( $class ) {
    if ( ! is_array( $class ) ) {
      // Split string of classes on whitespace
      $class = preg_split( '#\s+#', $class );
    }
    // Sanitize custom classes
    $classes = array_map( 'esc_attr', $class );
  } else {
    // Ensure that we always coerce class to being an array
    $class = array();
  }

  // If no post, return only custom classes
  if ( ! $post ) {
    return $classes;
  }

  // Get post ID (handle both WordPress and Backdrop style)
  $post_id = 0;
  if ( isset( $post->ID ) ) {
    $post_id = (int) $post->ID;
  } elseif ( isset( $post->nid ) ) {
    $post_id = (int) $post->nid;
  }

  // Add post ID class
  if ( $post_id > 0 ) {
    $classes[] = 'post-' . $post_id;
  }

  // Get post type
  $post_type = '';
  if ( isset( $post->post_type ) ) {
    $post_type = $post->post_type;
  } elseif ( isset( $post->type ) ) {
    // Backdrop node type
    $post_type = $post->type;
  }

  // Add post type classes
  if ( $post_type ) {
    // Don't add bare post type class in admin (WordPress behavior)
    if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
      $classes[] = $post_type;
    }
    $classes[] = 'type-' . $post_type;
  }

  // Get post status
  $post_status = '';
  if ( isset( $post->post_status ) ) {
    $post_status = $post->post_status;
  } elseif ( isset( $post->status ) ) {
    // Backdrop uses numeric status (1 = published, 0 = unpublished)
    // Map to WordPress equivalents
    $post_status = ( $post->status == 1 ) ? 'publish' : 'draft';
  }

  // Add status class
  if ( $post_status ) {
    $classes[] = 'status-' . $post_status;
  }

  // Add post format class
  // For now, we'll check if get_post_format() exists, otherwise default to 'standard'
  if ( function_exists( 'get_post_format' ) && function_exists( 'post_type_supports' ) ) {
    if ( post_type_supports( $post_type, 'post-formats' ) ) {
      $post_format = get_post_format( $post_id );
      if ( $post_format && ! is_wp_error( $post_format ) ) {
        $classes[] = 'format-' . sanitize_html_class( $post_format );
      } else {
        $classes[] = 'format-standard';
      }
    }
  } else {
    // Default to standard format
    $classes[] = 'format-standard';
  }

  // Add sticky class
  if ( function_exists( 'is_sticky' ) && is_sticky( $post_id ) ) {
    // Only add 'sticky' class on home page (WordPress behavior)
    if ( function_exists( 'is_home' ) && function_exists( 'is_paged' ) ) {
      if ( is_home() && ! is_paged() ) {
        $classes[] = 'sticky';
      }
    } else {
      // Fallback: add sticky class
      $classes[] = 'sticky';
    }
  }

  // Add hentry class for hAtom microformat compliance
  $classes[] = 'hentry';

  // Add taxonomy term classes (categories, tags, custom taxonomies)
  // This is where we add category-{slug} and tag-{slug} classes
  if ( $post_id > 0 ) {
    // Try to get categories
    $categories = _wp2bd_get_post_terms( $post, 'category' );
    foreach ( $categories as $cat ) {
      if ( ! empty( $cat['slug'] ) ) {
        $term_class = sanitize_html_class( $cat['slug'], $cat['id'] );
        if ( is_numeric( $term_class ) || ! trim( $term_class, '-' ) ) {
          $term_class = $cat['id'];
        }
        $classes[] = 'category-' . $term_class;
      }
    }

    // Try to get tags
    $tags = _wp2bd_get_post_terms( $post, 'post_tag' );
    foreach ( $tags as $tag ) {
      if ( ! empty( $tag['slug'] ) ) {
        $term_class = sanitize_html_class( $tag['slug'], $tag['id'] );
        if ( is_numeric( $term_class ) || ! trim( $term_class, '-' ) ) {
          $term_class = $tag['id'];
        }
        // Use 'tag-' prefix for backward compatibility
        $classes[] = 'tag-' . $term_class;
      }
    }
  }

  // Ensure all classes are properly escaped
  $classes = array_map( 'esc_attr', $classes );

  /**
   * Filters the list of CSS classes for the current post.
   *
   * @since WordPress 2.7.0
   *
   * @param array $classes An array of post classes.
   * @param array $class   An array of additional classes added to the post.
   * @param int   $post_id The post ID.
   */
  if ( function_exists( 'apply_filters' ) ) {
    $classes = apply_filters( 'post_class', $classes, $class, $post_id );
  }

  // Return unique classes (remove duplicates)
  return array_unique( $classes );
}

/**
 * Helper function to get post terms for taxonomy.
 *
 * Internal helper for get_post_class(). Retrieves taxonomy terms
 * for a post from either WordPress or Backdrop taxonomy systems.
 *
 * @since WP2BD 1.0.0
 * @access private
 *
 * @param object $post     Post object.
 * @param string $taxonomy Taxonomy name ('category', 'post_tag', etc.).
 * @return array Array of term data (each with 'id' and 'slug' keys).
 */
function _wp2bd_get_post_terms( $post, $taxonomy ) {
  $terms = array();

  // Get post ID
  $post_id = 0;
  if ( isset( $post->ID ) ) {
    $post_id = (int) $post->ID;
  } elseif ( isset( $post->nid ) ) {
    $post_id = (int) $post->nid;
  }

  if ( $post_id <= 0 ) {
    return $terms;
  }

  // Check if post object has pre-loaded taxonomy data
  // This is common in test scenarios or when data is pre-populated
  if ( $taxonomy === 'category' && isset( $post->categories ) && is_array( $post->categories ) ) {
    // Categories provided in post object
    foreach ( $post->categories as $cat ) {
      if ( is_object( $cat ) ) {
        $terms[] = array(
          'id' => isset( $cat->term_id ) ? $cat->term_id : ( isset( $cat->cat_ID ) ? $cat->cat_ID : 0 ),
          'slug' => isset( $cat->slug ) ? $cat->slug : ( isset( $cat->category_nicename ) ? $cat->category_nicename : '' ),
        );
      } elseif ( is_array( $cat ) ) {
        $terms[] = array(
          'id' => isset( $cat['term_id'] ) ? $cat['term_id'] : ( isset( $cat['id'] ) ? $cat['id'] : 0 ),
          'slug' => isset( $cat['slug'] ) ? $cat['slug'] : '',
        );
      }
    }
  } elseif ( $taxonomy === 'post_tag' && isset( $post->tags ) && is_array( $post->tags ) ) {
    // Tags provided in post object
    foreach ( $post->tags as $tag ) {
      if ( is_object( $tag ) ) {
        $terms[] = array(
          'id' => isset( $tag->term_id ) ? $tag->term_id : 0,
          'slug' => isset( $tag->slug ) ? $tag->slug : '',
        );
      } elseif ( is_array( $tag ) ) {
        $terms[] = array(
          'id' => isset( $tag['term_id'] ) ? $tag['term_id'] : ( isset( $tag['id'] ) ? $tag['id'] : 0 ),
          'slug' => isset( $tag['slug'] ) ? $tag['slug'] : '',
        );
      }
    }
  }

  // Try WordPress get_the_terms() if available
  if ( empty( $terms ) && function_exists( 'get_the_terms' ) ) {
    $wp_terms = get_the_terms( $post_id, $taxonomy );
    if ( is_array( $wp_terms ) ) {
      foreach ( $wp_terms as $term ) {
        if ( is_object( $term ) && ! empty( $term->slug ) ) {
          $terms[] = array(
            'id' => isset( $term->term_id ) ? $term->term_id : 0,
            'slug' => $term->slug,
          );
        }
      }
    }
  }

  // Try Backdrop taxonomy_term_load_multiple() if available
  if ( empty( $terms ) && function_exists( 'taxonomy_term_load_multiple' ) && function_exists( 'field_get_items' ) ) {
    // Map WordPress taxonomy names to Backdrop field names
    $field_name = '';
    if ( $taxonomy === 'category' ) {
      $field_name = 'field_tags'; // Common Backdrop field name for categories
    } elseif ( $taxonomy === 'post_tag' ) {
      $field_name = 'field_tags'; // Backdrop might use same field for tags
    }

    if ( $field_name ) {
      $field_items = field_get_items( 'node', $post, $field_name );
      if ( is_array( $field_items ) ) {
        $tids = array();
        foreach ( $field_items as $item ) {
          if ( isset( $item['tid'] ) ) {
            $tids[] = $item['tid'];
          }
        }
        if ( ! empty( $tids ) ) {
          $backdrop_terms = taxonomy_term_load_multiple( $tids );
          foreach ( $backdrop_terms as $term ) {
            if ( is_object( $term ) ) {
              $terms[] = array(
                'id' => isset( $term->tid ) ? $term->tid : 0,
                'slug' => isset( $term->name ) ? sanitize_html_class( strtolower( str_replace( ' ', '-', $term->name ) ) ) : '',
              );
            }
          }
        }
      }
    }
  }

  return $terms;
}

/**
 * Sanitizes an HTML classname to ensure it only contains valid characters.
 *
 * Strips the string down to A-Z, a-z, 0-9, '_', and '-'. If this results in
 * an empty string, then the fallback value will be returned.
 *
 * WordPress Behavior:
 * - Strips out any %-encoded octets
 * - Limits to A-Z, a-z, 0-9, '_', '-'
 * - Returns fallback if result is empty or starts with number/hyphen
 *
 * @since WordPress 2.8.0
 * @since WP2BD 1.0.0
 *
 * @param string $class    The classname to be sanitized.
 * @param string $fallback Optional. The fallback value to return if sanitization results in empty string.
 *                         Default empty string.
 * @return string The sanitized value.
 */
function sanitize_html_class( $class, $fallback = '' ) {
  // Strip out any %-encoded octets
  $sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', $class );

  // Limit to A-Z, a-z, 0-9, '_', '-'
  $sanitized = preg_replace( '/[^A-Za-z0-9_-]/', '', $sanitized );

  // If empty or starts with number or hyphen, use fallback
  if ( '' === $sanitized || preg_match( '/^[0-9-]/', $sanitized ) ) {
    return $fallback;
  }

  return $sanitized;
}
/**
 * Check if post has a thumbnail (featured image) attached.
 *
 * WordPress Behavior:
 * - Checks if post has _thumbnail_id post meta
 * - Returns boolean true/false
 * - Works with post ID or WP_Post object
 *
 * Backdrop Mapping:
 * - Maps to Backdrop's image field (typically field_image)
 * - Checks if $node->field_image exists and has value
 * - Returns boolean based on presence of image data
 *
 * @since WordPress 2.9.0
 * @since WP2BD 1.0.0
 *
 * @param int|WP_Post|object|null $post Optional. Post ID or post object. Default is global $post.
 * @return bool True if post has thumbnail, false otherwise.
 */
function has_post_thumbnail( $post = null ) {
  global $wp_post;

  // If no post provided, use global $post (WordPress) or $wp_post (WP2BD)
  if ( null === $post ) {
    $post = isset( $wp_post ) ? $wp_post : null;
  }

  // Handle post ID (integer) vs post object
  if ( is_numeric( $post ) ) {
    $post_id = (int) $post;
    // In full implementation, this would load the node from Backdrop
    // For now, we'll return false for numeric IDs without loading
    return false;
  }

  // Handle missing post gracefully
  if ( ! $post || ! is_object( $post ) ) {
    return false;
  }

  // Check for WordPress-style _thumbnail_id property (for compatibility)
  if ( isset( $post->_thumbnail_id ) && ! empty( $post->_thumbnail_id ) ) {
    return true;
  }

  // Check for Backdrop-style image field
  // Backdrop stores images in field_image['und'][0]['uri'] structure
  if ( isset( $post->field_image ) ) {
    // Handle array structure (Field API)
    if ( is_array( $post->field_image ) ) {
      // Check for standard Field API structure
      if ( isset( $post->field_image['und'][0]['uri'] ) && ! empty( $post->field_image['und'][0]['uri'] ) ) {
        return true;
      }
      // Check for simplified array structure
      if ( isset( $post->field_image[0]['uri'] ) && ! empty( $post->field_image[0]['uri'] ) ) {
        return true;
      }
    }
    // Handle string structure (direct URI)
    elseif ( is_string( $post->field_image ) && ! empty( $post->field_image ) ) {
      return true;
    }
  }

  // Apply filter to allow custom thumbnail detection
  if ( function_exists( 'apply_filters' ) ) {
    $post_id = get_the_ID( $post );
    $has_thumbnail = apply_filters( 'has_post_thumbnail', false, $post_id, $post );
    if ( $has_thumbnail ) {
      return true;
    }
  }

  return false;
}

/**
 * Retrieve the post thumbnail (featured image) HTML.
 *
 * WordPress Behavior:
 * - Returns <img> tag with thumbnail image
 * - Supports multiple image sizes (thumbnail, medium, large, full, custom)
 * - Generates srcset for responsive images
 * - Applies 'post_thumbnail_html' filter
 *
 * Backdrop Mapping:
 * - Maps to Backdrop's image field rendering
 * - Uses image_style_url() for different sizes
 * - Maps WordPress sizes to Backdrop image styles:
 *   - 'thumbnail' → 'thumbnail' (100x100)
 *   - 'medium' → 'medium' (220x220)
 *   - 'large' → 'large' (480x480)
 *   - 'post-thumbnail' → 'medium' (default)
 *   - 'full' → original image
 * - Generates HTML with proper attributes
 *
 * @since WordPress 2.9.0
 * @since WP2BD 1.0.0
 *
 * @param int|WP_Post|object|null $post Optional. Post ID or post object. Default is global $post.
 * @param string|array $size Optional. Image size. Default 'post-thumbnail'.
 * @param string|array $attr Optional. Query string or array of attributes. Default empty.
 * @return string The post thumbnail image HTML tag or empty string.
 */
function get_the_post_thumbnail( $post = null, $size = 'post-thumbnail', $attr = '' ) {
  global $wp_post;

  // If no post provided, use global $post (WordPress) or $wp_post (WP2BD)
  if ( null === $post ) {
    $post = isset( $wp_post ) ? $wp_post : null;
  }

  // Handle post ID (integer) vs post object
  if ( is_numeric( $post ) ) {
    $post_id = (int) $post;
    // In full implementation, this would load the node from Backdrop
    // For now, we'll return empty string for numeric IDs without loading
    return '';
  }

  // Handle missing post gracefully
  if ( ! $post || ! is_object( $post ) ) {
    return '';
  }

  // Check if post has a thumbnail
  if ( ! has_post_thumbnail( $post ) ) {
    return '';
  }

  // Get post ID for filters
  $post_id = get_the_ID( $post );

  // Apply post_thumbnail_size filter
  if ( function_exists( 'apply_filters' ) ) {
    $size = apply_filters( 'post_thumbnail_size', $size, $post_id );
  }

  // Get image data from Backdrop node
  $image_data = _wp2bd_get_thumbnail_data( $post );

  if ( ! $image_data ) {
    return '';
  }

  // Fire begin_fetch_post_thumbnail_html action
  if ( function_exists( 'do_action' ) ) {
    do_action( 'begin_fetch_post_thumbnail_html', $post_id, $image_data['fid'], $size );
  }

  // Generate the HTML
  $html = _wp2bd_generate_thumbnail_html( $image_data, $size, $attr, $post );

  // Fire end_fetch_post_thumbnail_html action
  if ( function_exists( 'do_action' ) ) {
    do_action( 'end_fetch_post_thumbnail_html', $post_id, $image_data['fid'], $size );
  }

  // Apply post_thumbnail_html filter
  if ( function_exists( 'apply_filters' ) ) {
    $html = apply_filters( 'post_thumbnail_html', $html, $post_id, $image_data['fid'], $size, $attr );
  }

  return $html;
}

/**
 * Display the post thumbnail (featured image).
 *
 * WordPress Behavior:
 * - Echoes the thumbnail HTML
 * - Wrapper around get_the_post_thumbnail()
 * - Commonly used in theme templates
 *
 * Backdrop Mapping:
 * - Simply echoes the result of get_the_post_thumbnail()
 * - No additional processing needed
 *
 * @since WordPress 2.9.0
 * @since WP2BD 1.0.0
 *
 * @param string|array $size Optional. Image size. Default 'post-thumbnail'.
 * @param string|array $attr Optional. Query string or array of attributes. Default empty.
 * @return void Echoes the thumbnail HTML.
 */
function the_post_thumbnail( $size = 'post-thumbnail', $attr = '' ) {
  echo get_the_post_thumbnail( null, $size, $attr );
}

/**
 * Internal helper: Extract thumbnail data from post/node object.
 *
 * @since WP2BD 1.0.0
 * @access private
 *
 * @param object $post Post/node object.
 * @return array|false Image data array or false if not found.
 */
function _wp2bd_get_thumbnail_data( $post ) {
  // Check for Backdrop-style image field with Field API structure
  if ( isset( $post->field_image['und'][0] ) && is_array( $post->field_image['und'][0] ) ) {
    $image = $post->field_image['und'][0];
    return array(
      'uri' => isset( $image['uri'] ) ? $image['uri'] : '',
      'fid' => isset( $image['fid'] ) ? $image['fid'] : 0,
      'alt' => isset( $image['alt'] ) ? $image['alt'] : '',
      'title' => isset( $image['title'] ) ? $image['title'] : '',
      'width' => isset( $image['width'] ) ? $image['width'] : '',
      'height' => isset( $image['height'] ) ? $image['height'] : '',
    );
  }

  // Check for simplified array structure
  if ( isset( $post->field_image[0] ) && is_array( $post->field_image[0] ) ) {
    $image = $post->field_image[0];
    return array(
      'uri' => isset( $image['uri'] ) ? $image['uri'] : '',
      'fid' => isset( $image['fid'] ) ? $image['fid'] : 0,
      'alt' => isset( $image['alt'] ) ? $image['alt'] : '',
      'title' => isset( $image['title'] ) ? $image['title'] : '',
      'width' => isset( $image['width'] ) ? $image['width'] : '',
      'height' => isset( $image['height'] ) ? $image['height'] : '',
    );
  }

  // Check for string URI structure
  if ( isset( $post->field_image ) && is_string( $post->field_image ) ) {
    return array(
      'uri' => $post->field_image,
      'fid' => 0,
      'alt' => '',
      'title' => '',
      'width' => '',
      'height' => '',
    );
  }

  return false;
}

/**
 * Internal helper: Generate thumbnail HTML.
 *
 * @since WP2BD 1.0.0
 * @access private
 *
 * @param array $image_data Image data array from _wp2bd_get_thumbnail_data().
 * @param string|array $size Image size.
 * @param string|array $attr Additional attributes.
 * @param object $post Post/node object.
 * @return string HTML img tag.
 */
function _wp2bd_generate_thumbnail_html( $image_data, $size, $attr, $post ) {
  $uri = $image_data['uri'];

  // Map WordPress sizes to Backdrop image styles
  $style_map = array(
    'thumbnail' => 'thumbnail',
    'medium' => 'medium',
    'large' => 'large',
    'post-thumbnail' => 'medium', // Default post-thumbnail maps to medium
    'full' => false, // Full size means no style processing
  );

  // Determine image style to use
  $image_style = false;

  if ( is_string( $size ) ) {
    if ( isset( $style_map[ $size ] ) ) {
      $image_style = $style_map[ $size ];
    }
    else {
      // Custom size name - try to use as-is
      $image_style = $size;
    }
  }
  elseif ( is_array( $size ) ) {
    // Array with [width, height] - use original image
    // In full implementation, could create derivative on-the-fly
    $image_style = false;
  }

  // Generate image URL
  if ( $image_style && function_exists( 'image_style_url' ) ) {
    $image_url = image_style_url( $image_style, $uri );
  }
  elseif ( function_exists( 'file_create_url' ) ) {
    $image_url = file_create_url( $uri );
  }
  else {
    // Fallback: construct URL manually
    global $base_url;
    $base = isset( $base_url ) ? $base_url : '';
    $image_url = $base . '/' . str_replace( 'public://', 'sites/default/files/', $uri );
  }

  // Parse attributes
  $default_attr = array(
    'src' => $image_url,
    'class' => 'attachment-' . ( is_string( $size ) ? $size : 'custom' ) . ' wp-post-image',
    'alt' => $image_data['alt'],
    'loading' => 'lazy', // WordPress 5.5+ adds native lazy loading
  );

  // Add title if available
  if ( ! empty( $image_data['title'] ) ) {
    $default_attr['title'] = $image_data['title'];
  }

  // Add dimensions if available
  if ( ! empty( $image_data['width'] ) ) {
    $default_attr['width'] = $image_data['width'];
  }
  if ( ! empty( $image_data['height'] ) ) {
    $default_attr['height'] = $image_data['height'];
  }

  // Merge custom attributes
  if ( is_array( $attr ) ) {
    $attributes = array_merge( $default_attr, $attr );
  }
  elseif ( is_string( $attr ) && ! empty( $attr ) ) {
    // Parse query string format: key="value" key2="value2"
    $custom_attr = array();
    if ( preg_match_all( '/(\w+)=["\']([^"\']*)["\']/', $attr, $matches ) ) {
      foreach ( $matches[1] as $i => $key ) {
        $custom_attr[ $key ] = $matches[2][ $i ];
      }
    }
    $attributes = array_merge( $default_attr, $custom_attr );
  }
  else {
    $attributes = $default_attr;
  }

  // Generate srcset for responsive images (WordPress 4.4+)
  if ( $image_style && function_exists( 'image_style_url' ) ) {
    $srcset = array();

    // Add different sizes to srcset
    $srcset_sizes = array( 'thumbnail', 'medium', 'large' );
    foreach ( $srcset_sizes as $srcset_size ) {
      if ( isset( $style_map[ $srcset_size ] ) && $style_map[ $srcset_size ] ) {
        $srcset_url = image_style_url( $style_map[ $srcset_size ], $uri );

        // Get dimensions for this style
        // In full implementation, would query image style configuration
        $width = _wp2bd_get_image_style_width( $style_map[ $srcset_size ] );
        if ( $width ) {
          $srcset[] = $srcset_url . ' ' . $width . 'w';
        }
      }
    }

    if ( ! empty( $srcset ) ) {
      $attributes['srcset'] = implode( ', ', $srcset );
      $attributes['sizes'] = '(max-width: 709px) 85vw, (max-width: 909px) 67vw, (max-width: 1362px) 62vw, 840px';
    }
  }

  // Build HTML tag
  $html = '<img';
  foreach ( $attributes as $key => $value ) {
    if ( ! empty( $value ) || $value === '0' ) {
      $html .= ' ' . $key . '="' . esc_attr( $value ) . '"';
    }
  }
  $html .= ' />';

  return $html;
}

/**
 * Internal helper: Get image style width.
 *
 * @since WP2BD 1.0.0
 * @access private
 *
 * @param string $style_name Image style name.
 * @return int|false Width in pixels or false if not found.
 */
function _wp2bd_get_image_style_width( $style_name ) {
  // Default Backdrop image style widths
  $default_widths = array(
    'thumbnail' => 100,
    'medium' => 220,
    'large' => 480,
  );

  if ( isset( $default_widths[ $style_name ] ) ) {
    return $default_widths[ $style_name ];
  }

  // In full implementation, would load from Backdrop's image style config
  if ( function_exists( 'image_style_load' ) ) {
    $style = image_style_load( $style_name );
    if ( $style && isset( $style['effects'] ) ) {
      foreach ( $style['effects'] as $effect ) {
        if ( isset( $effect['data']['width'] ) ) {
          return (int) $effect['data']['width'];
        }
      }
    }
  }

  return false;
}
/**
 * Retrieve the classes for the body element as an array.
 *
 * WordPress Behavior:
 * - Generates CSS classes based on page context (home, single, archive, etc.)
 * - Adds user status classes (logged-in, admin-bar)
 * - Adds pagination classes (paged-{n})
 * - Adds specific page/post ID classes
 * - Applies 'body_class' filter for customization
 *
 * Backdrop Mapping:
 * - Maps WordPress conditional tags to Backdrop path/context checks
 * - Uses global $wp_query for page context detection
 * - Integrates with Backdrop's user system for login status
 *
 * @since WordPress 2.8.0
 * @since WP2BD 1.0.0
 *
 * @global WP_Query $wp_query WordPress Query object.
 *
 * @param string|array $class Optional. One or more classes to add to the class list.
 * @return array Array of body classes.
 */
function get_body_class( $class = '' ) {
  global $wp_query;

  $classes = array();

  // RTL (Right-to-Left) language support
  if ( function_exists( 'is_rtl' ) && is_rtl() ) {
    $classes[] = 'rtl';
  }

  // Front page
  if ( is_front_page() ) {
    $classes[] = 'home';
  }

  // Blog home (posts page)
  if ( is_home() ) {
    $classes[] = 'blog';
  }

  // Archive pages
  if ( is_archive() ) {
    $classes[] = 'archive';
  }

  // Date archives
  if ( function_exists( 'is_date' ) && is_date() ) {
    $classes[] = 'date';
  }

  // Search results
  if ( is_search() ) {
    $classes[] = 'search';
    // Check if search has results
    if ( isset( $wp_query->posts ) && ! empty( $wp_query->posts ) ) {
      $classes[] = 'search-results';
    } else {
      $classes[] = 'search-no-results';
    }
  }

  // Paged content (paginated)
  if ( function_exists( 'is_paged' ) && is_paged() ) {
    $classes[] = 'paged';
  }

  // Attachment pages
  if ( function_exists( 'is_attachment' ) && is_attachment() ) {
    $classes[] = 'attachment';
  }

  // 404 error pages
  if ( is_404() ) {
    $classes[] = 'error404';
  }

  // Singular content (single post, page, or attachment)
  if ( is_singular() ) {
    $post_id = 0;
    $post = null;

    // Get queried object (current post/page)
    if ( isset( $wp_query ) && method_exists( $wp_query, 'get_queried_object_id' ) ) {
      $post_id = $wp_query->get_queried_object_id();
      $post = $wp_query->get_queried_object();
    }

    // Get post type
    $post_type = 'post';
    if ( $post && isset( $post->post_type ) ) {
      $post_type = $post->post_type;
    }

    // Page template classes
    if ( function_exists( 'is_page_template' ) && is_page_template() ) {
      $classes[] = "{$post_type}-template";

      // Get template slug and add template-specific classes
      if ( function_exists( 'get_page_template_slug' ) ) {
        $template_slug = get_page_template_slug( $post_id );
        if ( $template_slug ) {
          $template_parts = explode( '/', $template_slug );
          foreach ( $template_parts as $part ) {
            $classes[] = "{$post_type}-template-" . sanitize_html_class( str_replace( array( '.', '/' ), '-', basename( $part, '.php' ) ) );
          }
          $classes[] = "{$post_type}-template-" . sanitize_html_class( str_replace( '.', '-', $template_slug ) );
        }
      }
    } else {
      $classes[] = "{$post_type}-template-default";
    }

    // Single post
    if ( is_single() ) {
      $classes[] = 'single';
      if ( isset( $post->post_type ) ) {
        $classes[] = 'single-' . sanitize_html_class( $post->post_type, $post_id );
        $classes[] = 'postid-' . $post_id;

        // Post format support
        if ( function_exists( 'post_type_supports' ) && function_exists( 'get_post_format' ) ) {
          if ( post_type_supports( $post->post_type, 'post-formats' ) ) {
            $post_format = get_post_format( $post->ID );
            if ( $post_format && ! is_wp_error( $post_format ) ) {
              $classes[] = 'single-format-' . sanitize_html_class( $post_format );
            } else {
              $classes[] = 'single-format-standard';
            }
          }
        }
      }
    }

    // Attachment
    if ( function_exists( 'is_attachment' ) && is_attachment() ) {
      if ( function_exists( 'get_post_mime_type' ) ) {
        $mime_type = get_post_mime_type( $post_id );
        $mime_prefix = array( 'application/', 'image/', 'text/', 'audio/', 'video/', 'music/' );
        $classes[] = 'attachmentid-' . $post_id;
        $classes[] = 'attachment-' . str_replace( $mime_prefix, '', $mime_type );
      }
    }
    // Page
    elseif ( is_page() ) {
      $classes[] = 'page';
      $classes[] = 'page-id-' . $post_id;

      // Check if page has children (is a parent)
      if ( function_exists( 'get_pages' ) ) {
        $children = get_pages( array( 'parent' => $post_id, 'number' => 1 ) );
        if ( ! empty( $children ) ) {
          $classes[] = 'page-parent';
        }
      }

      // Check if page has a parent (is a child)
      if ( $post && isset( $post->post_parent ) && $post->post_parent ) {
        $classes[] = 'page-child';
        $classes[] = 'parent-pageid-' . $post->post_parent;
      }
    }
  }
  // Archive pages - more specific types
  elseif ( is_archive() ) {
    // Post type archives
    if ( function_exists( 'is_post_type_archive' ) && is_post_type_archive() ) {
      $classes[] = 'post-type-archive';
      $post_type = function_exists( 'get_query_var' ) ? get_query_var( 'post_type' ) : '';
      if ( is_array( $post_type ) ) {
        $post_type = reset( $post_type );
      }
      if ( $post_type ) {
        $classes[] = 'post-type-archive-' . sanitize_html_class( $post_type );
      }
    }
    // Author archives
    elseif ( function_exists( 'is_author' ) && is_author() ) {
      $author = isset( $wp_query ) ? $wp_query->get_queried_object() : null;
      $classes[] = 'author';
      if ( $author && isset( $author->user_nicename ) ) {
        $classes[] = 'author-' . sanitize_html_class( $author->user_nicename, $author->ID );
        $classes[] = 'author-' . $author->ID;
      }
    }
    // Category archives
    elseif ( function_exists( 'is_category' ) && is_category() ) {
      $cat = isset( $wp_query ) ? $wp_query->get_queried_object() : null;
      $classes[] = 'category';
      if ( $cat && isset( $cat->term_id ) ) {
        $cat_class = sanitize_html_class( $cat->slug, $cat->term_id );
        if ( is_numeric( $cat_class ) || ! trim( $cat_class, '-' ) ) {
          $cat_class = $cat->term_id;
        }
        $classes[] = 'category-' . $cat_class;
        $classes[] = 'category-' . $cat->term_id;
      }
    }
    // Tag archives
    elseif ( function_exists( 'is_tag' ) && is_tag() ) {
      $tag = isset( $wp_query ) ? $wp_query->get_queried_object() : null;
      $classes[] = 'tag';
      if ( $tag && isset( $tag->term_id ) ) {
        $tag_class = sanitize_html_class( $tag->slug, $tag->term_id );
        if ( is_numeric( $tag_class ) || ! trim( $tag_class, '-' ) ) {
          $tag_class = $tag->term_id;
        }
        $classes[] = 'tag-' . $tag_class;
        $classes[] = 'tag-' . $tag->term_id;
      }
    }
    // Custom taxonomy archives
    elseif ( function_exists( 'is_tax' ) && is_tax() ) {
      $term = isset( $wp_query ) ? $wp_query->get_queried_object() : null;
      if ( $term && isset( $term->term_id ) ) {
        $term_class = sanitize_html_class( $term->slug, $term->term_id );
        if ( is_numeric( $term_class ) || ! trim( $term_class, '-' ) ) {
          $term_class = $term->term_id;
        }
        $classes[] = 'tax-' . sanitize_html_class( $term->taxonomy );
        $classes[] = 'term-' . $term_class;
        $classes[] = 'term-' . $term->term_id;
      }
    }
  }

  // User logged in status
  if ( is_user_logged_in() ) {
    $classes[] = 'logged-in';
  }

  // Admin bar
  if ( is_admin_bar_showing() ) {
    $classes[] = 'admin-bar';
    $classes[] = 'no-customize-support';
  }

  // Custom background
  if ( function_exists( 'get_background_color' ) && function_exists( 'get_theme_support' ) && function_exists( 'get_background_image' ) ) {
    if ( get_background_color() !== get_theme_support( 'custom-background', 'default-color' ) || get_background_image() ) {
      $classes[] = 'custom-background';
    }
  }

  // Custom logo
  if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) {
    $classes[] = 'wp-custom-logo';
  }

  // Pagination classes
  $page = 0;
  if ( isset( $wp_query ) && method_exists( $wp_query, 'get' ) ) {
    $page = $wp_query->get( 'page' );
    if ( ! $page || $page < 2 ) {
      $page = $wp_query->get( 'paged' );
    }
  }

  if ( $page && $page > 1 && ! is_404() ) {
    $classes[] = 'paged-' . $page;

    if ( is_single() ) {
      $classes[] = 'single-paged-' . $page;
    } elseif ( is_page() ) {
      $classes[] = 'page-paged-' . $page;
    } elseif ( function_exists( 'is_category' ) && is_category() ) {
      $classes[] = 'category-paged-' . $page;
    } elseif ( function_exists( 'is_tag' ) && is_tag() ) {
      $classes[] = 'tag-paged-' . $page;
    } elseif ( function_exists( 'is_date' ) && is_date() ) {
      $classes[] = 'date-paged-' . $page;
    } elseif ( function_exists( 'is_author' ) && is_author() ) {
      $classes[] = 'author-paged-' . $page;
    } elseif ( is_search() ) {
      $classes[] = 'search-paged-' . $page;
    } elseif ( function_exists( 'is_post_type_archive' ) && is_post_type_archive() ) {
      $classes[] = 'post-type-paged-' . $page;
    }
  }

  // Add custom classes passed as parameter
  if ( ! empty( $class ) ) {
    if ( ! is_array( $class ) ) {
      $class = preg_split( '#\s+#', $class );
    }
    $classes = array_merge( $classes, $class );
  } else {
    // Ensure that we always coerce class to being an array
    $class = array();
  }

  // Escape all classes
  $classes = array_map( 'esc_attr', $classes );

  /**
   * Filters the list of CSS body classes for the current post or page.
   *
   * @since WordPress 2.8.0
   * @since WP2BD 1.0.0
   *
   * @param array $classes An array of body classes.
   * @param array $class   An array of additional classes added to the body.
   */
  if ( function_exists( 'apply_filters' ) ) {
    $classes = apply_filters( 'body_class', $classes, $class );
  }

  return array_unique( $classes );
}

/**
 * Display the classes for the body element.
 *
 * WordPress Behavior:
 * - Echoes 'class="..."' attribute with all body classes
 * - Wrapper around get_body_class()
 * - Commonly used in header.php: <body <?php body_class(); ?>>
 *
 * @since WordPress 2.8.0
 * @since WP2BD 1.0.0
 *
 * @param string|array $class Optional. One or more classes to add to the class list.
 * @return void Echoes the class attribute.
 */
function body_class( $class = '' ) {
  // Separates classes with a single space, collates classes for body element
  echo 'class="' . join( ' ', get_body_class( $class ) ) . '"';
}

/**
 * Determines whether the current request is for a singular post/page.
 *
 * Stub implementation - checks if viewing a single post, page, or attachment.
 *
 * @since WordPress 1.5.0
 * @since WP2BD 1.0.0
 *
 * @param string|array $post_types Optional. Post type or array of post types. Default empty.
 * @return bool True if singular, false otherwise.
 */
if ( ! function_exists( 'is_singular' ) ) {
  function is_singular( $post_types = '' ) {
    global $wp_query;

    if ( ! isset( $wp_query ) ) {
      return false;
    }

    // Check if we have the is_singular property
    if ( isset( $wp_query->is_singular ) ) {
      return $wp_query->is_singular;
    }

    // Fallback: check if single or page
    return is_single() || is_page();
  }
}

/**
 * Determines whether the query is for a single post.
 *
 * Stub implementation for body_class() support.
 *
 * @since WordPress 1.5.0
 * @since WP2BD 1.0.0
 *
 * @param int|string|array $post Optional. Post ID, title, slug, or array of such. Default empty.
 * @return bool True if single post, false otherwise.
 */
if ( ! function_exists( 'is_single' ) ) {
  function is_single( $post = '' ) {
    global $wp_query;

    if ( ! isset( $wp_query ) || ! isset( $wp_query->is_single ) ) {
      return false;
    }

    return $wp_query->is_single;
  }
}

/**
 * Determines whether the query is for a static page.
 *
 * Stub implementation for body_class() support.
 *
 * @since WordPress 1.5.0
 * @since WP2BD 1.0.0
 *
 * @param int|string|array $page Optional. Page ID, title, slug, or array of such. Default empty.
 * @return bool True if page, false otherwise.
 */
if ( ! function_exists( 'is_page' ) ) {
  function is_page( $page = '' ) {
    global $wp_query;

    if ( ! isset( $wp_query ) || ! isset( $wp_query->is_page ) ) {
      return false;
    }

    return $wp_query->is_page;
  }
}

/**
 * Determines whether the query is for the front page of the site.
 *
 * Stub implementation for body_class() support.
 *
 * @since WordPress 2.5.0
 * @since WP2BD 1.0.0
 *
 * @return bool True if front page, false otherwise.
 */
if ( ! function_exists( 'is_front_page' ) ) {
  function is_front_page() {
    global $wp_query;

    if ( ! isset( $wp_query ) || ! isset( $wp_query->is_front_page ) ) {
      return false;
    }

    return $wp_query->is_front_page;
  }
}

/**
 * Determines whether the query is for the blog homepage.
 *
 * Stub implementation for body_class() support.
 *
 * @since WordPress 1.5.0
 * @since WP2BD 1.0.0
 *
 * @return bool True if blog home, false otherwise.
 */
if ( ! function_exists( 'is_home' ) ) {
  function is_home() {
    global $wp_query;

    if ( ! isset( $wp_query ) || ! isset( $wp_query->is_home ) ) {
      return false;
    }

    return $wp_query->is_home;
  }
}

/**
 * Determines whether the query is for an archive page.
 *
 * Stub implementation for body_class() support.
 *
 * @since WordPress 1.5.0
 * @since WP2BD 1.0.0
 *
 * @return bool True if archive, false otherwise.
 */
if ( ! function_exists( 'is_archive' ) ) {
  function is_archive() {
    global $wp_query;

    if ( ! isset( $wp_query ) || ! isset( $wp_query->is_archive ) ) {
      return false;
    }

    return $wp_query->is_archive;
  }
}

/**
 * Determines whether the query is for a search.
 *
 * Stub implementation for body_class() support.
 *
 * @since WordPress 1.5.0
 * @since WP2BD 1.0.0
 *
 * @return bool True if search, false otherwise.
 */
if ( ! function_exists( 'is_search' ) ) {
  function is_search() {
    global $wp_query;

    if ( ! isset( $wp_query ) || ! isset( $wp_query->is_search ) ) {
      return false;
    }

    return $wp_query->is_search;
  }
}

/**
 * Determines whether the query is for a 404 error.
 *
 * Stub implementation for body_class() support.
 *
 * @since WordPress 1.5.0
 * @since WP2BD 1.0.0
 *
 * @return bool True if 404, false otherwise.
 */
if ( ! function_exists( 'is_404' ) ) {
  function is_404() {
    global $wp_query;

    if ( ! isset( $wp_query ) || ! isset( $wp_query->is_404 ) ) {
      return false;
    }

    return $wp_query->is_404;
  }
}

/**
 * Determines whether the current user is logged in.
 *
 * Stub implementation that integrates with Backdrop's user system.
 *
 * @since WordPress 2.0.0
 * @since WP2BD 1.0.0
 *
 * @return bool True if user is logged in, false otherwise.
 */
if ( ! function_exists( 'is_user_logged_in' ) ) {
  function is_user_logged_in() {
    // Check Backdrop's global user object
    global $user;

    if ( isset( $user ) && isset( $user->uid ) && $user->uid > 0 ) {
      return true;
    }

    return false;
  }
}

/**
 * Determines whether the admin bar should be showing.
 *
 * Stub implementation that checks if admin toolbar is visible.
 *
 * @since WordPress 3.1.0
 * @since WP2BD 1.0.0
 *
 * @return bool True if admin bar is showing, false otherwise.
 */
if ( ! function_exists( 'is_admin_bar_showing' ) ) {
  function is_admin_bar_showing() {
    // Check if admin toolbar module is enabled and user has permission
    global $user;

    // If user is logged in and has admin permission, assume admin bar is showing
    if ( isset( $user ) && isset( $user->uid ) && $user->uid > 0 ) {
      // Check if user has admin toolbar permission (simplified check)
      if ( function_exists( 'user_access' ) && user_access( 'access toolbar' ) ) {
        return true;
      }
      // Fallback: assume admin users (uid 1) have toolbar
      if ( $user->uid == 1 ) {
        return true;
      }
    }

    return false;
  }
}

/**
 * Checks for WordPress errors.
 *
 * Stub implementation for error checking.
 *
 * @since WordPress 2.1.0
 * @since WP2BD 1.0.0
 *
 * @param mixed $thing The variable to check.
 * @return bool True if $thing is a WP_Error object, false otherwise.
 */
if ( ! function_exists( 'is_wp_error' ) ) {
  function is_wp_error( $thing ) {
    // Check if $thing is a WP_Error object
    return ( is_object( $thing ) && get_class( $thing ) === 'WP_Error' );
  }
}
