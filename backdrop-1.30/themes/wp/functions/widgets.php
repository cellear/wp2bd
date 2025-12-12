<?php
/**
 * @file
 * WordPress Widget Compatibility Layer
 *
 * Provides dynamic widget generation from Backdrop content.
 * Overrides WordPress widget functions to populate sidebars with Backdrop data.
 */

/**
 * Override WordPress dynamic_sidebar() function.
 *
 * This function generates widgets dynamically from Backdrop content instead of
 * relying on WordPress widget configuration stored in wp_options.
 *
 * @param string|int $sidebar_id
 *   The ID or name of the sidebar.
 *
 * @return bool
 *   True if sidebar has widgets, false otherwise.
 */
function dynamic_sidebar($sidebar_id = 1) {
  global $wp_registered_sidebars, $wp_registered_widgets;

  // Normalize sidebar ID for comparison
  $sidebar_key = is_int($sidebar_id) ? 'sidebar-' . $sidebar_id : $sidebar_id;

  // Get sidebar configuration (widget wrappers)
  $sidebar_config = _wp2bd_get_sidebar_config($sidebar_id);

  if (empty($sidebar_config)) {
    return false;
  }

  // Generate widgets from Backdrop data
  $widgets = _wp2bd_generate_widgets_from_backdrop($sidebar_id, $sidebar_config);

  if (empty($widgets)) {
    return false;
  }

  // Output each widget
  $did_one = false;
  foreach ($widgets as $widget) {
    $did_one = true;

    echo $widget['before_widget'];

    if (!empty($widget['title'])) {
      echo $widget['before_title'] . $widget['title'] . $widget['after_title'];
    }

    echo $widget['content'];
    echo $widget['after_widget'];
  }

  return $did_one;
}

/**
 * Override WordPress is_active_sidebar() function.
 *
 * @param string|int $sidebar_id
 *   The sidebar ID to check.
 *
 * @return bool
 *   True if sidebar has widgets.
 */
function is_active_sidebar($sidebar_id) {
  // Only return true for PRIMARY sidebar and footer/main sidebars
  // Secondary sidebars (sidebar-2, sidebar-3, tertiary) cause overlap issues
  // in themes like Twenty Thirteen because they use absolute positioning
  // and expect these sidebars to be empty unless explicitly configured
  $active_sidebars = array(
    'sidebar-1',      // Primary sidebar (most themes)
    'primary',        // Alternative name for primary
    1,                // Numeric form
    'main',           // Footer sidebar (Twenty Thirteen)
  );
  return in_array($sidebar_id, $active_sidebars, false);
}

/**
 * Get sidebar configuration for a given sidebar ID.
 *
 * Returns the widget wrapper HTML that each theme defines via register_sidebar().
 *
 * @param string|int $sidebar_id
 *   The sidebar ID.
 *
 * @return array
 *   Configuration with before_widget, after_widget, before_title, after_title.
 */
function _wp2bd_get_sidebar_config($sidebar_id) {
  global $wp_registered_sidebars;

  // If WordPress theme registered sidebars, use that config
  if (!empty($wp_registered_sidebars[$sidebar_id])) {
    return $wp_registered_sidebars[$sidebar_id];
  }

  // Get active theme to determine default widget wrappers
  $theme = WP2BD_ACTIVE_THEME;

  // Theme-specific defaults (from functions.php analysis)
  $defaults = array(
    'twentyfourteen' => array(
      'before_widget' => '<aside id="%1$s" class="widget %2$s">',
      'after_widget'  => '</aside>',
      'before_title'  => '<h1 class="widget-title">',
      'after_title'   => '</h1>',
    ),
    'twentyfifteen' => array(
      'before_widget' => '<aside id="%1$s" class="widget %2$s">',
      'after_widget'  => '</aside>',
      'before_title'  => '<h2 class="widget-title">',
      'after_title'   => '</h2>',
    ),
    'twentysixteen' => array(
      'before_widget' => '<section id="%1$s" class="widget %2$s">',
      'after_widget'  => '</section>',
      'before_title'  => '<h2 class="widget-title">',
      'after_title'   => '</h2>',
    ),
    'twentyseventeen' => array(
      'before_widget' => '<section id="%1$s" class="widget %2$s">',
      'after_widget'  => '</section>',
      'before_title'  => '<h2 class="widget-title">',
      'after_title'   => '</h2>',
    ),
  );

  return isset($defaults[$theme]) ? $defaults[$theme] : $defaults['twentyseventeen'];
}

/**
 * Generate widgets from Backdrop content.
 *
 * Creates an array of widgets with Backdrop data (recent posts, search, etc.).
 *
 * @param string|int $sidebar_id
 *   The sidebar ID.
 * @param array $config
 *   Widget wrapper configuration.
 *
 * @return array
 *   Array of widgets with title and content.
 */
function _wp2bd_generate_widgets_from_backdrop($sidebar_id, $config) {
  $widgets = array();

  // Widget 1: Search
  $widgets[] = array(
    'before_widget' => sprintf($config['before_widget'], 'search-2', 'widget_search'),
    'after_widget'  => $config['after_widget'],
    'before_title'  => $config['before_title'],
    'after_title'   => $config['after_title'],
    'title'         => '',  // Search widget typically has no title
    'content'       => _wp2bd_render_search_widget(),
  );

  // Widget 2: Recent Posts
  $widgets[] = array(
    'before_widget' => sprintf($config['before_widget'], 'recent-posts-2', 'widget_recent_entries'),
    'after_widget'  => $config['after_widget'],
    'before_title'  => $config['before_title'],
    'after_title'   => $config['after_title'],
    'title'         => 'Recent Posts',
    'content'       => _wp2bd_render_recent_posts_widget(),
  );

  // Widget 3: Archives
  $widgets[] = array(
    'before_widget' => sprintf($config['before_widget'], 'archives-2', 'widget_archive'),
    'after_widget'  => $config['after_widget'],
    'before_title'  => $config['before_title'],
    'after_title'   => $config['after_title'],
    'title'         => 'Archives',
    'content'       => _wp2bd_render_archives_widget(),
  );

  // Widget 4: Categories
  $widgets[] = array(
    'before_widget' => sprintf($config['before_widget'], 'categories-2', 'widget_categories'),
    'after_widget'  => $config['after_widget'],
    'before_title'  => $config['before_title'],
    'after_title'   => $config['after_title'],
    'title'         => 'Categories',
    'content'       => _wp2bd_render_categories_widget(),
  );

  // Widget 5: Meta
  $widgets[] = array(
    'before_widget' => sprintf($config['before_widget'], 'meta-2', 'widget_meta'),
    'after_widget'  => $config['after_widget'],
    'before_title'  => $config['before_title'],
    'after_title'   => $config['after_title'],
    'title'         => 'Meta',
    'content'       => _wp2bd_render_meta_widget(),
  );

  return $widgets;
}

/**
 * Render search widget.
 *
 * @return string
 *   HTML for search form.
 */
function _wp2bd_render_search_widget() {
  global $base_url;

  ob_start();
  ?>
  <form role="search" method="get" class="search-form" action="<?php echo $base_url; ?>/search/node">
    <label>
      <span class="screen-reader-text">Search for:</span>
      <input type="search" class="search-field" placeholder="Search &hellip;" value="" name="keys">
    </label>
    <button type="submit" class="search-submit">
      <span class="screen-reader-text">Search</span>
    </button>
  </form>
  <?php
  return ob_get_clean();
}

/**
 * Render recent posts widget.
 *
 * @return string
 *   HTML for recent posts list.
 */
function _wp2bd_render_recent_posts_widget() {
  // Get recent Backdrop nodes
  $query = db_select('node', 'n')
    ->fields('n', array('nid', 'title', 'created'))
    ->condition('status', 1)
    ->orderBy('created', 'DESC')
    ->range(0, 5)
    ->execute();

  ob_start();
  echo '<ul>';

  while ($node = $query->fetchObject()) {
    $url = url('node/' . $node->nid);
    $title = check_plain($node->title);
    echo '<li><a href="' . $url . '">' . $title . '</a></li>';
  }

  echo '</ul>';
  return ob_get_clean();
}

/**
 * Render archives widget.
 *
 * @return string
 *   HTML for monthly archives.
 */
function _wp2bd_render_archives_widget() {
  // Get monthly archives from Backdrop nodes
  $query = db_query("
    SELECT DISTINCT
      YEAR(FROM_UNIXTIME(created)) as year,
      MONTH(FROM_UNIXTIME(created)) as month,
      COUNT(*) as count
    FROM {node}
    WHERE status = 1
    GROUP BY year, month
    ORDER BY year DESC, month DESC
    LIMIT 12
  ");

  ob_start();
  echo '<ul>';

  foreach ($query as $row) {
    $month_name = date('F', mktime(0, 0, 0, $row->month, 1));
    $url = url('archive/' . $row->year . '/' . sprintf('%02d', $row->month));
    echo '<li><a href="' . $url . '">' . $month_name . ' ' . $row->year . '</a></li>';
  }

  echo '</ul>';
  return ob_get_clean();
}

/**
 * Render categories widget.
 *
 * @return string
 *   HTML for categories list.
 */
function _wp2bd_render_categories_widget() {
  // Get Backdrop taxonomy terms (categories)
  $vocabularies = taxonomy_vocabulary_get_names();

  ob_start();
  echo '<ul>';

  // For simplicity, show a static "Uncategorized" category
  // In a real implementation, this would query actual Backdrop taxonomy terms
  echo '<li class="cat-item"><a href="' . url('taxonomy/term/1') . '">Uncategorized</a></li>';

  echo '</ul>';
  return ob_get_clean();
}

/**
 * Render meta widget.
 *
 * @return string
 *   HTML for meta links (login, RSS, etc.).
 */
function _wp2bd_render_meta_widget() {
  global $base_url, $user;

  ob_start();
  echo '<ul>';

  if ($user->uid) {
    echo '<li><a href="' . url('user/logout') . '">Log out</a></li>';
  }
  else {
    echo '<li><a href="' . url('user/login') . '">Log in</a></li>';
  }

  echo '<li><a href="' . url('rss.xml') . '">Entries <abbr title="Really Simple Syndication">RSS</abbr></a></li>';
  echo '<li><a href="https://backdrop.org/">Backdrop CMS</a></li>';

  echo '</ul>';
  return ob_get_clean();
}
