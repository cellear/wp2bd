# Repository Guidelines

## Project Structure & Module Organization
- `implementation/` holds the WordPress compatibility layer (functions, classes, and tests).
- `implementation/functions/` contains grouped WordPress API shims (e.g., `template-loading.php`).
- `implementation/classes/` contains mocked core classes like `WP_Query` and `WP_Post`.
- `implementation/tests/` contains Backdrop tests (naming pattern: `*.test.php`).
- `backdrop-1.30/` is a local Backdrop CMS install used for development and test runs.
- `wordpress-4.9/` mirrors a WordPress 4.9 tree with unmodified themes for validation.
- `specs/`, `DOCS/`, and `DEBUGGING/` hold specs, notes, and debugging references.

## Build, Test, and Development Commands
- `ddev start` starts the local environment (DDEV).
- `ddev bee config-set wp_content.settings active_theme twentyseventeen` switches the active theme.
- `php core/scripts/run-tests.sh --file ../implementation/tests/get_header.test.php` runs a single test from the Backdrop root (`backdrop-1.30/`).
- `php core/scripts/run-tests.sh --directory ../implementation/tests/` runs the full test suite for the compatibility layer.

## Coding Style & Naming Conventions
- Use WordPress-style snake_case for API functions and Backdrop conventions for module integration.
- Match existing files: 4-space indentation, WordPress DocBlock format, and minimal inline comments.
- Internal helpers should be prefixed with `_wp2bd_` or `_wp_` to avoid public API collisions.

## Testing Guidelines
- Tests live in `implementation/tests/` and use Backdrop’s test runner.
- Aim for at least 3 cases per function: happy path, edge case, and failure path.
- Keep tests deterministic and focused on WordPress theme compatibility behavior.

## Commit & Pull Request Guidelines
- No strict commit schema; follow the existing short, imperative style (e.g., “Add …”, “Configure …”).
- PRs should include a concise description, testing notes, and links to relevant issues/specs.
- Include screenshots when a theme rendering or UI behavior changes.

## Configuration & Compatibility Notes
- Do not modify WordPress theme files under `wordpress-4.9/`; keep themes unmodified.
- Preserve WordPress semantics and global state (`$post`, `$wp_query`, `$theme`) when adding shims.
