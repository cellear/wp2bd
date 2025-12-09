# HANDOFF - Dec 8, 2025

## Branch / Path
- Branch: `dec8-codex-max-continue`
- Worktree: `/Users/lukemccormick/.claude-worktrees/WP4BD/admiring-clarke`

## What I changed today
- Switched debug UI to light mode (`wp4bd_debug.inc`, `templates/page-debug.tpl.php`).
- Fixed Stage 4 pathing to point at real WordPress core:
  - `ABSPATH` now `/var/www/html/wordpress-4.9/` with `WPINC` + `WP_CONTENT_DIR` set accordingly.
  - Removed the `ABSPATH` override from `functions/escaping.php` that was clobbering the path.
- Added skip guards in Stage 4 to avoid redeclare fatals when our compat functions already exist:
  - Skip `query.php`, `post.php`, `post-template.php`, `general-template.php`, `link-template.php`, `formatting.php`, `plugin.php`, `l10n.php`, `load.php`, `functions.wp-styles.php`, `functions.wp-scripts.php` when symbols already defined.
  - Only `kses.php` currently loads from WP core; others are skipped to prevent redeclare of our implementations.
- Added guards for WP loop/core files to avoid redeclare (`WP_Query`/`get_post`, `hooks`, enqueue functions).
- Added mock `wpdb` override (`themes/wp/wp-content/db.php`) for WP4BD-008:
  - Provides stub `wpdb` with table-name properties, prefix/charset/collate, insert_id.
  - All query APIs return empty/null/false and log attempts via `watchdog`.
  - Helpers `prepare`, `flush`, `get_blog_prefix` included to satisfy core callers.
- Added Stage 6 in `page-debug.tpl.php` for WP4BD-009:
  - Detect active theme, load `functions.php` with try/catch logging.
  - Fire `after_setup_theme` and `wp_enqueue_scripts` actions.
  - Log registered hooks summary from `$wp_filter`.
- Added Stage 7 in `page-debug.tpl.php` for WP4BD-010 (template selection):
  - Read conditionals from `$wp_query` (is_home, is_single, etc.).
  - Build simple hierarchy (home/single/page/archive/search/404 → index.php).
  - Choose first existing template under active theme, log template + path, log missing if none.
- Added Stage 8 in `page-debug.tpl.php` for WP4BD-011:
  - Buffer template output via `ob_start/ob_get_clean`, log captured length; show captured HTML only at debug level 4.
  - Reassert `$wp_query`/`$wp_the_query` globals before include to ensure the theme uses our populated query (debug counters reset).
  - Log loop state pre/post include and debug counters; handles template errors safely.
- Added loop debug counters/logging (debug level 4) in `WP_Query` and loop wrappers to trace `have_posts()` / `the_post()` usage.

## Current Stage 4 output
- Paths resolve to WordPress core (`/var/www/html/wordpress-4.9/wp-includes` exists).
- Files loaded: `kses.php` (≈49 KB). Others are **skipped** because their symbols are already defined by our compat layer; skipping prevents fatal errors.

## Notes / Next steps
- To actually load more WP core files (instead of skipping), we’d need to disable or conditionalize our overlapping implementations (hooks, loop, content-display, enqueue, i18n, formatting). Today we favored “no fatal errors” over “full core load.”

## Pending: Core functions to comment out (for headless WP)
We currently skip these at runtime to avoid redeclare. For full headless loading, plan to comment them out in WP core and rely on our bridge versions:
- `wp-includes/plugin.php`: add_filter, has_filter, apply_filters, remove_filter, add_action, do_action, has_action, remove_action, current_filter, current_action, doing_filter, doing_action, did_action, _wp_filter_build_unique_id, WP_Hook loading.
- `wp-includes/general-template.php`: wp_head, wp_footer, language_attributes.
- `wp-includes/post.php`: get_post (we provide in our WP_Query bridge).
- `wp-includes/query.php`: loop helpers (have_posts, the_post, wp_reset_postdata, etc.).
- `wp-includes/link-template.php`: the_permalink/get_permalink helpers.
- `wp-includes/formatting.php`: sanitize_html_class and related helpers already in our stack.
- `wp-includes/l10n.php`: __, _e, etc. (we already load our i18n).
- `wp-includes/functions.wp-styles.php`: wp_print_styles, etc. (we provide enqueue/print hooks).
- `wp-includes/functions.wp-scripts.php`: wp_print_scripts, etc.
- `wp-includes/load.php`: timer_start conflict; safest to skip/comment out.

Once commented in core, we can remove Stage 4 skip guards so WP files actually load without fatal redeclares.

