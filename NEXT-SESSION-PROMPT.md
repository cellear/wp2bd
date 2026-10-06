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

Work plan (details in STATE-OF-PROJECT.md §8):

1. **Triage before coding.** Epics 5–8 were already drafted in December on a
   stacked branch — `origin/claude/epic-8-template-functions-01XU73r6DXHviqs3JEQdEMxr`
   contains all of them, unmerged and unvalidated. Check it out, boot the
   site, curl pages, and report what works and what breaks. Give the
   Dec 18–19 experiment branches a quick assessment too — `codex/wpbrain-prune`
   is the most complete tip (it contains `theme-only-architecture` and the
   capture branches). Salvage beats rewrite.
   **Decided (Luke, Jul 2026):** the static theme player (`capture_theme` on
   `codex/wpbrain-prune`) is a keeper — preserve it and treat its JSON
   render-context fixtures as the data contract for engine testing; see
   STATE-OF-PROJECT.md §"static theme player".
2. Recommend a base to the user (main + cherry-picks, the epic-8 tip, or an
   experiment branch) and wait for their pick before merging anything.
3. Add a kill switch (config flag) around WordPress-core loading so V2 work
   can never again "break the working state" — the Dec 15 failure mode.
4. Burn down whatever remains of the Dec 8 collision list (the epic branches
   may have already handled part of it): delete V1 duplicate functions, load
   the real WP core file, re-render, commit small.
5. Close remaining epic gaps found in triage, then validate all four themes
   against `REFERENCE/` screenshots and land the production template
   (WP4BD-V2-073).

Norms: never leave the site broken at a commit boundary (the kill switch
exists so the default page keeps rendering); trust the git log over the debug
page's checkmarks; PHP 8.4 deprecation warnings from Backdrop/WP-era code are
known noise, not your bug. When a chunk of work is parallelizable (e.g.,
several independent collision groups), you may fan out subagents — but each
agent's output must pass the render check before merging.

Start by confirming the environment, then present a short plan for step 1
(the kill switch + re-enabling globals init) and begin.
