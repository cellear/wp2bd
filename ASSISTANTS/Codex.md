# Codex

This file captures notes, decisions, and context from Codex sessions.

## Purpose
- Track Codex-specific work, experiments, and outcomes.
- Capture decisions that should persist beyond a single session.

## Summary (2025-12-18)
Today we created a new branch focused on aggressively pruning WordPress core
surfaces that are irrelevant to the “isolated wpbrain” rendering model. We
started by forking from the branch that best represents that architecture
(`claude/theme-only-architecture-fe7f34f6312f074f`), then renamed the work to
`codex/wpbrain-prune` after pushing it. The goal was to cut anything that
implicitly enables admin behavior, remote access, or update mechanisms, while
preserving the front-end rendering capabilities that themes depend on.

We removed the entire `wpbrain/wp-admin/` tree, which contained all WordPress
admin UI templates, assets, and helpers. We also deleted the front-controller
entry points that support WordPress admin or remote operations (`wp-login.php`,
`xmlrpc.php`, `wp-trackback.php`, and related endpoints). On the core side, we
pruned I/O-heavy systems and integration surfaces (`wp-includes/http.php`,
`class-http*.php`, `update.php`, REST API files, multisite files, and the
SimplePie remote fetcher). This aligns with the principle that wpbrain should
be a read-only rendering engine, with any I/O or administration handled by
Backdrop-side tools instead.

The result is a much smaller wpbrain footprint that still retains the core
theme rendering stack (template loaders, formatting, query, taxonomy, and
hooks), while removing the most likely sources of unwanted I/O or admin
behavior.
