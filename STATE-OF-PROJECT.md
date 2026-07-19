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
- **Epics 5–8: ⚠️ DRAFTED ON UNMERGED BRANCHES** (Dec 16–17) — see below.
  The debug page and older docs say "Next: Epic 5"; git says otherwise.

### The December finale (branch archaeology — verify before redoing anything)

Epics 5–8 were implemented as a **stacked branch chain**
(epic-5 ⊂ epic-6 ⊂ epic-7 ⊂ epic-8; each builds on the previous):

| Branch (origin/) | Contains |
|---|---|
| `claude/epic-5-io-interception-01XU73r6…` | Epic 5 (+6 commits on main) |
| `claude/epic-6-bootstrap-integration-01XU73r6…` | Epics 5–6 (+10) |
| `claude/epic-7-data-bridges-01XU73r6…` | Epics 5–7 (+16) |
| `claude/epic-8-template-functions-01XU73r6…` | **Epics 5–8 complete (+23), incl. production template and test scripts** |

Whether epic-8 actually *renders* was never validated — it was never merged.
Triage it first: check out the tip, run the site, see what happens.

Then Dec 18–19, three competing architecture experiments (never merged):
- `claude/theme-only-architecture-fe7f34f6…` (+35) — consolidate into the
  theme, bootstrap cleanup
- `grok/module-2-theme` (+46) — pragmatic: real Twenty Seventeen layout
  fixes, more themes added
- `codex/wpbrain-prune` (+45, the most complete December tip — it *contains*
  `theme-only-architecture` and both `capture-feature` branches) — the
  **static theme player** line: see below. Also prunes wpbrain hard
  (wp-admin, xmlrpc, mail: ~265K lines deleted) and adds themes
  Twenty Ten through Twenty Twenty-Two.

A `merge-all-branches.sh` sits at the repo root — an unification attempt that
appears never to have been run.

### The static theme player (KEEP — Luke, Jul 2026)

Luke's verdict: this concept is worth keeping regardless of which
architecture wins. What it actually is (code on `codex/wpbrain-prune`, in
`backdrop-1.30/themes/capture_theme/` — three files, base theme Basis):

On every page render, `capture_theme` snapshots **Backdrop's complete render
context** — preprocess variables for page/node/block/region, globals, all
menu trees, breadcrumbs — as pretty-printed JSON into
`public://theme-captures/<path>--<timestamp>.json` (sample payload committed
in `capture_theme/sample/`). The JSON is a **data contract**: a deterministic
fixture that a "player" can replay through a WordPress theme engine with no
live Backdrop in the render loop. Uses: fixture-based testing of the WP
engine, decoupled development, and potentially a render-once/serve-static
product mode.

### Branch-tip SHAs (pinned here in case branches get cleaned up)

| Line of work | Branch | Tip SHA |
|---|---|---|
| Static theme player + theme-only arch + pruned wpbrain | `codex/wpbrain-prune` | `5786df82ed` |
| V2 Epics 5–8 (stacked, unvalidated) | `claude/epic-8-template-functions-01XU73r6…` | `f49996f814` |
| Grok: 2017 layout fixes + more themes | `grok/module-2-theme` | `2abcb41024` |
| Grok: wp-as-engine | `grok/wp-as-engine` | `77711098bc` |
| Wpbrain analysis (= `grok/theme-integration`, same commit) | `claude/analyze-wpbrain-usage-cKbah` | `c3051f049e` |

The cloud git proxy can't push tags; to pin these permanently, run from a
machine with full push rights:

```bash
git fetch origin
git tag archive/dec2025-capture-player   5786df82ed
git tag archive/dec2025-epic-chain       f49996f814
git tag archive/dec2025-grok-module-2    2abcb41024
git tag archive/dec2025-grok-wp-engine   77711098bc
git tag archive/dec2025-wpbrain-analysis c3051f049e
git push origin --tags
```

### Dormancy (Dec 15, 2025 → Jul 2026)
Main froze at Dec 15 (after `5dd0efa` "temporarily disable wp-globals-init to
restore working state"); the epic chain and the three experiments happened on
branches Dec 16–19, then work stopped entirely. Main is parked at a stable
V1-rendering state with V2 scaffolding in place.

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

## 8. Suggested plan of attack (triage first, then Epics as needed)

1. **Triage the December branches before writing new code.** Check out
   `origin/claude/epic-8-template-functions-01XU73r6DXHviqs3JEQdEMxr` (it
   contains all of Epics 5–8), boot the site, curl pages, and record what
   works and what breaks. Do the same quick pass on the three Dec 18–19
   experiments. Salvage beats rewrite.
2. **Pick a base with Luke** — main + cherry-picked epic work, the epic-8
   tip itself, or one of the experiments — and merge it to main behind a
   kill switch (config flag) so the default page keeps rendering.
3. **Burn down remaining V1/V2 collisions** from the Dec 8 handoff list:
   per WP core file, delete V1 twins, load real file, curl-verify, commit.
   (The epic branches may have already done part of this — check first.)
4. **Close the remaining epic gaps** found in triage (I/O interception,
   bootstrap-in-module, data bridges, options-from-config).
5. **Validate all four themes** against `REFERENCE/` screenshots and swap
   the debug template for the production template (WP4BD-V2-073).
