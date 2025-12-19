# Branch Inventory

Assumption: “last accessed” = the most recent commit timestamp on the branch tip.

## Summary (Reverse Chronological)
| Branch | Last commit | Ahead/Behind main | Highlights |
| --- | --- | --- | --- |
| `grok/module-2-theme` | 2025-12-18 09:12:47 -0800 | +46 / 0 | Module+theme approach, more WP themes, layout fixes |
| `claude/theme-only-architecture-fe7f34f6312f074f` | 2025-12-18 00:57:47 -0800 | +35 / 0 | Theme-only “isolated WordPress brain”, bootstrap cleanup |
| `claude/epic-8-template-functions-01XU73r6DXHviqs3JEQdEMxr` | 2025-12-17 03:12:53 +0000 | +23 / 0 | Template wiring, path fixes, demo HTML |
| `claude/epic-7-data-bridges-01XU73r6DXHviqs3JEQdEMxr` | 2025-12-16 05:33:38 +0000 | +16 / 0 | Post/user/term/options bridges + tests |
| `claude/epic-6-bootstrap-integration-01XU73r6DXHviqs3JEQdEMxr` | 2025-12-16 01:46:52 +0000 | +10 / 0 | Bootstrap integration + core load tests |
| `claude/epic-5-io-interception-01XU73r6DXHviqs3JEQdEMxr` | 2025-12-16 01:34:35 +0000 | +6 / 0 | IO interception (HTTP/cron/update) + tests |
| `claude/analyze-wpbrain-usage-cKbah` | 2025-12-15 20:57:13 -0800 | +16 / 0 | Theme settings moved into theme config |
| `grok/theme-integration` | 2025-12-15 20:57:13 -0800 | +16 / 0 | Same tip as `claude/analyze-wpbrain-usage-cKbah` |
| `grok/wp-as-engine` | 2025-12-15 20:02:55 -0800 | +13 / 0 | “WP-as-engine” V2 + WP_Post bridge |
| `main` | 2025-12-15 15:06:03 -0800 | 0 / 0 | Baseline docs rename |
| `github-actions-workflow-for-php` | 2025-12-15 12:01:00 -0800 | +1 / +1 | Adds PHP/Composer workflow |
| `claude/setup-testing-environment-01XU73r6DXHviqs3JEQdEMxr` | 2025-12-14 07:59:49 +0000 | 0 / +25 | Adds `merge-all-branches.sh` |
| `upbeat-khorana` | 2025-12-12 23:07:34 -0800 | 0 / +27 | Migration plan docs + branch consolidation |
| `dec8-codex-max-continue` | 2025-12-08 15:28:00 -0800 | 0 / +50 | Template hierarchy debug + mock wpdb |
| `composer-refactor` | 2025-12-03 13:32:23 -0800 | +10 / +60 | Composer-based Backdrop/WP install |
| `refactor/sunset-stubs-december` | 2025-12-02 00:52:25 -0800 | 0 / +59 | “Themes own full HTML” architecture shift |

## Branch Notes (Reverse Chronological)

### `grok/module-2-theme`
- Last commit: 2025-12-18 09:12:47 -0800.
- Recent commits: add more WordPress themes, fix Twenty Seventeen layout, resolve JS warning (`twentyseventeenScreenReaderText`), CSS wrapping and theme switching fixes.
- Unique/rare: large theme library under `backdrop-1.30/themes/wp/wpbrain/wp-content/themes/`, with focus on layout correctness in a module+theme hybrid.

### `claude/theme-only-architecture-fe7f34f6312f074f`
- Last commit: 2025-12-18 00:57:47 -0800.
- Recent commits: consolidate themes, fix WordPress bootstrap, CSS/JS loading workaround, Page block rendering, DB constant fixes.
- Unique/rare: “isolated WordPress brain” with theme-only control path and large pruning/relocation of assets.

### `claude/epic-8-template-functions-01XU73r6DXHviqs3JEQdEMxr`
- Last commit: 2025-12-17 03:12:53 +0000.
- Recent commits: prevent double bootstrap, path corrections for `wpbrain`, production template changes, demo HTML snapshots.
- Unique/rare: explicit template wiring experiments and architecture demo artifacts.

### `claude/epic-7-data-bridges-01XU73r6DXHviqs3JEQdEMxr`
- Last commit: 2025-12-16 05:33:38 +0000.
- Recent commits: post/user/term/options bridges, expanded test suite, dashboard updates.
- Unique/rare: most complete V2 bridge test coverage in `TESTS/V2/`.

### `claude/epic-6-bootstrap-integration-01XU73r6DXHviqs3JEQdEMxr`
- Last commit: 2025-12-16 01:46:52 +0000.
- Recent commits: WordPress bootstrap integration point, core load sequence, DB connection prevention, tests.
- Unique/rare: explicit bootstrap flow in `backdrop-1.30/modules/wp_content/includes/wp-bootstrap.php`.

### `claude/epic-5-io-interception-01XU73r6DXHviqs3JEQdEMxr`
- Last commit: 2025-12-16 01:34:35 +0000.
- Recent commits: disable cron/update/http in `wpbrain/wp-includes`, map `wp_upload_dir()` to Backdrop paths.
- Unique/rare: IO “lockdown” patterns and supporting tests.

### `claude/analyze-wpbrain-usage-cKbah`
- Last commit: 2025-12-15 20:57:13 -0800.
- Recent commits: move WordPress settings into Backdrop theme config, admin UI cleanup.
- Unique/rare: most focused theme-settings refactor.

### `grok/theme-integration`
- Last commit: 2025-12-15 20:57:13 -0800.
- Recent commits: same tip as `claude/analyze-wpbrain-usage-cKbah`.
- Unique/rare: none beyond the shared theme settings integration.

### `grok/wp-as-engine`
- Last commit: 2025-12-15 20:02:55 -0800.
- Recent commits: V2 production deployment fixes, core-load test, WP_Post bridge, enhanced bridge test output.
- Unique/rare: most complete WP_Post conversion using real WordPress class.

### `main`
- Last commit: 2025-12-15 15:06:03 -0800.
- Recent commits: doc renaming (month prefixes), no architecture changes.
- Unique/rare: baseline reference only.

### `github-actions-workflow-for-php`
- Last commit: 2025-12-15 12:01:00 -0800.
- Recent commits: adds PHP/Composer GitHub Actions workflow.
- Unique/rare: only branch with CI workflow.

### `claude/setup-testing-environment-01XU73r6DXHviqs3JEQdEMxr`
- Last commit: 2025-12-14 07:59:49 +0000.
- Recent commits: adds `merge-all-branches.sh` to consolidate branches.
- Unique/rare: consolidation script only; otherwise behind main.

### `upbeat-khorana`
- Last commit: 2025-12-12 23:07:34 -0800.
- Recent commits: migration plan docs, merge of earlier dev branches.
- Unique/rare: most comprehensive migration documentation set in `DOCS/`.

### `dec8-codex-max-continue`
- Last commit: 2025-12-08 15:28:00 -0800.
- Recent commits: template hierarchy debug stage, load theme `functions.php`, mock wpdb guardrails.
- Unique/rare: debugging-oriented template selection logging.

### `composer-refactor`
- Last commit: 2025-12-03 13:32:23 -0800.
- Recent commits: Composer-based install for Backdrop + WordPress, removes bundled copies, adds installer paths.
- Unique/rare: only branch that externalizes WP/Backdrop via Composer.

### `refactor/sunset-stubs-december`
- Last commit: 2025-12-02 00:52:25 -0800.
- Recent commits: themes render full HTML, Backdrop assets injected, stubs sunset, adds template/nav utilities.
- Unique/rare: foundational “themes own the markup” architecture change.
