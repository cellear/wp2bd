# Kickoff prompt for the next session

Paste the text below into a fresh Claude Code session on this repo.

---

Read `STATE-OF-PROJECT.md` at the repo root before doing anything else — it is
the authoritative summary of where this project stands (July 2026). Skim
`DOCS/V2/dec15-ARCHITECTURE-WORDPRESS-AS-ENGINE.md` and
`HANDOFF/dec8-codex-max-continue.md` (the function-collision list) next.

**The decision is made: we are pursuing V2 "WordPress-as-Engine."** Backdrop
runs a complete copy of WordPress 4.9 (`backdrop-1.30/themes/wp/wpbrain/`) as a
headless rendering engine. The V1 reimplemented WordPress functions in
`backdrop-1.30/themes/wp/functions/` are legacy: as real WordPress core files
come online, delete the V1 twins rather than skipping the core files. Only the
true bridge survives (the `db.php` wpdb drop-in, node→WP_Post transforms, path
constants, asset injection).

Before writing any code, verify your environment: the SessionStart hook should
have booted the site — confirm with `curl -s -o /dev/null -w "%{http_code}"
http://127.0.0.1:8080/` (expect 200; the front page is the V2 debug status
page). If the hook didn't run, execute `.claude/hooks/session-start.sh`
manually. Every stage of work must be verified by actually rendering pages —
never mark a step done without a curl/screenshot check. Reference screenshots
of the real WordPress themes are in `REFERENCE/2014..2017/`.

Work plan (details in STATE-OF-PROJECT.md §7):

1. Add a kill switch (config flag) around WordPress-core loading, then
   re-enable `wp-globals-init.php` in `page.tpl.php` behind it — the Dec 15
   failure was V2 breaking the site with no way to switch it off.
2. Burn down the Dec 8 collision list one WP core file at a time: delete the
   V1 duplicate functions, load the real core file, re-render, commit. Small
   commits, one collision group each.
3. Then continue the epic sequence: Epic 5 (External I/O Interception,
   tickets WP4BD-V2-040..042), Epic 6 (Bootstrap Integration, 050..052),
   Epic 7 (Data Bridges, 060..063), Epic 8 (Testing & Validation, 070..073).

Norms: never leave the site broken at a commit boundary (the kill switch
exists so the default page keeps rendering); trust the git log over the debug
page's checkmarks; PHP 8.4 deprecation warnings from Backdrop/WP-era code are
known noise, not your bug. When a chunk of work is parallelizable (e.g.,
several independent collision groups), you may fan out subagents — but each
agent's output must pass the render check before merging.

Start by confirming the environment, then present a short plan for step 1
(the kill switch + re-enabling globals init) and begin.
