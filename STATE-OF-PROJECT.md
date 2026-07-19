# STATE OF PROJECT — WP4BD (WordPress for Backdrop)

**Written:** July 19, 2026 (after ~7 months dormant; last real work Dec 15, 2025)
**Decision on record:** Pursue **V2 "WordPress-as-Engine"** — Backdrop runs a complete
copy of WordPress 4.9 as a headless rendering engine. V1 (function reimplementation)
is to be progressively deleted, not maintained.
**Verified:** The site boots and renders in a Claude Code web session via the
SessionStart hook in `.claude/hooks/session-start.sh` (front page = V2 debug status
page, HTTP 200 at `http://127.0.0.1:8080`).

---

## 1. What this project is

A compatibility layer letting **unmodified classic WordPress themes** (Twenty
Fourteen–Seventeen, WordPress 4.9 era) render Backdrop CMS content. Backdrop owns
all data and admin; WordPress code is used purely for rendering.

## 2. History in three acts

### V1 — Reimplementation (Nov 2025)
WordPress functions were rewritten one-by-one against Backdrop APIs
(`backdrop-1.30/themes/wp/functions/*.php` + `implementation/functions/*.php`).
Result: all four themes rendered "sort of" — layout right, many details wrong,
endless function gaps. Multi-theme switching works
(`admin/config/content/wp-content`, or `bee config-set wp_content.settings active_theme <name>`).

### V2 pivot — WordPress-as-Engine (Dec 2025)
Architecture in `DOCS/V2/dec15-ARCHITECTURE-WORDPRESS-AS-ENGINE.md`:
load **real WordPress core**, intercept its database layer (`wpdb` drop-in),
feed it Backdrop data. Tracked as Epics/tickets (`DOCS/V2/jira-import-v2-edited.csv`).

Status when work stopped (also live on the site's front page):
- **Epic 1 — Debug Infrastructure: ✅** (`wp4bd_debug_*` helpers, debug template)
- **Epic 2 — WordPress Core Setup: ✅** — WP 4.9 copied to
  `backdrop-1.30/themes/wp/wpbrain/`, with `wp-bootstrap.php` + `wp-config-bd.php`
- **Epic 3 — Database Interception: ✅** — `db.php` drop-in stubs `wpdb`, maps
  queries to `node_load()` / `EntityFieldQuery` / `user_load_multiple()`
- **Epic 4 — WordPress Globals: ⚠️ code written but DISABLED** (see §4)
- **Epic 5 — External I/O Interception: ⬅ NEXT** (WP4BD-V2-040/041/042)
- **Epic 6 — Bootstrap Integration** (050/051/052)
- **Epic 7 — Data Structure Bridges** (060–063: posts, users, terms, options)
- **Epic 8 — Testing & Validation** (070–073)

### Dormancy (Dec 15, 2025 → Jul 2026)
Work stopped after commit `5dd0efa` "temporarily disable wp-globals-init to
restore working state." Nothing is broken; it's parked at a stable V1-rendering
state with V2 scaffolding in place.

## 3. The central blocker (read this before coding)

**Function redeclaration conflicts between V1 and V2.**
`page.tpl.php` lines ~18–22 comment out the loading of
`modules/wp_content/includes/wp-globals-init.php` because loading real WP core
fatals against V1's `hooks.php` / `utilities.php` (duplicate `add_filter`,
`__()`, `wp_head()`, etc.).

The Dec 8 handoff (`HANDOFF/dec8-codex-max-continue.md`) contains the **exact
collision list** — which WP core files were being skipped and which functions
collide. Under the WordPress-as-Engine decision, the resolution is now
unambiguous: **the V1 duplicates get deleted; real WP core wins.** Work through
the collision list file-by-file: delete the V1 implementation, enable the WP
core file, re-render, verify, commit. The only V1 code that survives is the true
bridge layer (the `db.php` drop-in, node→WP_Post transformation, path constants,
asset injection).

## 4. Known landmines

1. **Two WordPress copies exist:** `/wordpress-4.9/` (repo root, used by Dec 8
   Stage-4 experiments, `ABSPATH=/var/www/html/wordpress-4.9/` in ddev) and
   `backdrop-1.30/themes/wp/wpbrain/` (Epic 2's copy). Pick ONE as canonical
   early — likely `wpbrain/` since the V2 bootstrap points there — and note the
   ddev vs. cloud path difference (`/var/www/html/...` vs repo-relative).
2. **PHP 8.4 in the cloud session** vs. WordPress 4.9-era code: expect
   deprecation warnings (implicit nullable params, dynamic properties). The
   session server suppresses `E_DEPRECATED`; Backdrop core itself logs them
   too. Local ddev likely pins an older PHP.
3. **`is_active_sidebar()` in `themes/wp/functions/widgets.php`** was hand-tuned
   (only sidebar-1/primary/main active) to prevent layout overlap in some
   themes — port that behavior into whatever replaces it.
4. **Database name vs dump:** the Dec 8 dump (`DB/wp4bd-bd-dec8-db.sql.gz`)
   loads into the `backdrop` database (settings.php: root@127.0.0.1:3306, empty
   password, TCP for Bee compatibility).
5. **The debug page overstates Epic 4** — code exists, but it's disabled in
   `page.tpl.php`. Trust the git log over the green checkmarks.

## 5. Where things live

| Thing | Path |
|---|---|
| Backdrop docroot | `backdrop-1.30/` |
| WP theme wrapper (V1 + V2 scaffolding) | `backdrop-1.30/themes/wp/` |
| V2 WordPress core copy | `backdrop-1.30/themes/wp/wpbrain/` |
| V2 bootstrap / config bridge | `themes/wp/wp-bootstrap.php`, `wp-config-bd.php` |
| `wpdb` drop-in | `themes/wp/wp-content/db.php` |
| V1 function files (to be deleted as V2 lands) | `themes/wp/functions/*.php` |
| Disabled globals init | `backdrop-1.30/modules/wp_content/includes/wp-globals-init.php` |
| wp_content module (blocks, theme switch admin UI) | `backdrop-1.30/modules/wp_content/` |
| Debug-first render template | `themes/wp/templates/page.tpl.php` |
| V2 architecture + plan docs | `DOCS/V2/` |
| Epic/ticket list | `DOCS/V2/jira-import-v2-edited.csv` |
| Handoffs & transcripts (Dec 2025) | `HANDOFF/` |
| DB dump + config snapshot (Dec 8) | `DB/` |
| Reference screenshots of real WP themes | `REFERENCE/2014..2017/` |
| Second WP copy (Dec 8 experiments) | `wordpress-4.9/` |

## 6. Development environment

**Cloud (Claude Code web):** `.claude/hooks/session-start.sh` runs at session
start — installs MariaDB, seeds `backdrop` DB from the Dec 8 dump if empty,
starts `php -S 127.0.0.1:8080` with `.claude/hooks/router.php` for clean URLs.
Site URL exported as `$WP4BD_URL`. Verify with `curl -s http://127.0.0.1:8080/`.
Logs: `/tmp/wp4bd-session-start.log`, `/tmp/wp4bd-php-server.log`.

**Local (Mac):** ddev + Bee (`bin/bee-install.sh`); theme switch via
`ddev bee config-set wp_content.settings active_theme twentyseventeen`.

**The verification loop (use it — this is what was missing in 2025):**
render a page with curl → read the debug output / diff against
`REFERENCE/` screenshots → fix → re-render. Never claim a stage works without
fetching the page.

## 7. Prior art: the Theme Machine project (separate — do not merge)

`github.com/cellear/theme_machine` is a **sibling project** (Jan–Mar 2026) that
shipped `d7_theme_compat` 1.0 — unmodified Drupal 7 themes on Backdrop,
validated across 170+ themes. It is a different project and stays separate;
WordPress work continues here. Two of its techniques are worth borrowing:

1. **`layout_suppress(TRUE)`** in `hook_init()` — tells Backdrop's Layout
   system to step aside so the compat layer renders the whole page itself.
   Cleaner than wedging WordPress output into Layout blocks; a strong
   candidate for the V2 render path when Epic 6 (Bootstrap Integration) lands.
2. **Automated theme smoke-testing** — its `theme_tester` module adds a
   `bee theme-test` command that renders every theme and checks for errors.
   The same idea here would let one command verify all four WordPress themes
   after each collision-list change.

## 8. Suggested plan of attack (Epics 5→8, verified at every step)

1. **Re-enable WP core loading behind a kill switch** (config flag or debug
   level) so V2 work never again "breaks the working state" — the Dec 15
   failure mode.
2. **Burn down the collision list** from the Dec 8 handoff: per WP core file,
   delete V1 twins, load real file, curl-verify, commit.
3. **Epic 5** — intercept external I/O (HTTP calls, file paths, mail) so
   headless WP can't reach out.
4. **Epic 6** — move bootstrap from the debug template into `wp_content`
   module properly; prevent any WP DB connection attempt.
5. **Epic 7** — complete node→WP_Post, user, term, options bridges (options
   backed by Backdrop config).
6. **Epic 8** — render all four themes, compare against `REFERENCE/`
   screenshots, then swap the debug template for the production template
   (WP4BD-V2-073).
