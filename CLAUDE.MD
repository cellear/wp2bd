# CLAUDE.MD - AI Assistant Context for WP4BD Project

## Project Overview

**WP4BD** (WordPress for Backdrop) is a backward compatibility layer that enables classic WordPress themes (pre-Block Editor era) to run on Backdrop CMS 1.30 without modification.

### Key Objectives
- Enable effortless migration from WordPress to Backdrop
- Preserve theme investments without rewriting code
- Reduce transition costs dramatically
- Extend the life of existing WordPress theme assets

### Target Compatibility
- **WordPress Version:** 4.9 (last major pre-Gutenberg release)
- **Backdrop Version:** 1.30
- **Primary Theme Targets:** Twenty Fourteen, Twenty Fifteen, Twenty Sixteen, Twenty Seventeen

## Project Architecture

### Core Components

1. **wp_content Module** - The main Backdrop module implementing WordPress compatibility
   - Located in the Backdrop installation (not in this repo directly)
   - Loads WordPress function stubs and compatibility layer
   - Handles theme switching and asset loading

2. **Implementation Directory** (`/implementation/`)
   - Contains organized WordPress function implementations
   - Structured into logical categories (see below)
   - Each function category has its own PHP file

3. **WordPress Themes** (`/wordpress-4.9/wp-content/themes/`)
   - Contains unmodified WordPress themes for testing
   - Currently includes Twenty Fourteen through Twenty Seventeen

4. **Backdrop Installation** (`/backdrop-1.30/`)
   - Standard Backdrop CMS installation for development/testing

## Implementation Structure

### Function Categories (`/implementation/functions/`)

The WordPress compatibility layer is organized into focused function files:

- **`conditionals.php`** - WordPress conditional tags (`is_single()`, `is_page()`, `is_home()`, etc.)
- **`content-display.php`** - Content output functions (`the_title()`, `the_content()`, `the_excerpt()`, etc.)
- **`escaping.php`** - Security/escaping functions (`esc_html()`, `esc_attr()`, `esc_url()`, etc.)
- **`hooks.php`** - WordPress hook system (`add_action()`, `do_action()`, `add_filter()`, `apply_filters()`)
- **`loop.php`** - The WordPress Loop (`have_posts()`, `the_post()`, `wp_reset_postdata()`)
- **`post-metadata.php`** - Post meta functions (`get_post_meta()`, etc.)
- **`template-loading.php`** - Template loading functions (`get_header()`, `get_footer()`, `get_sidebar()`, `get_template_part()`)
- **`utilities.php`** - Utility functions (`home_url()`, `bloginfo()`, `get_bloginfo()`, etc.)

### Class Implementations (`/implementation/classes/`)

WordPress core classes that need to be mocked:
- `WP_Query` - Main query class
- `WP_Post` - Post object wrapper

## Recent Major Changes

### Function Organization (Latest)
- **Sunset of stubs.php:** Moved from monolithic `stubs.php` to organized function files
- WordPress functions are now organized by category for better maintainability
- Auto-loading system for all function files

### Multi-Theme Support
- Successfully running Twenty Fourteen, Fifteen, Sixteen, and Seventeen
- Theme switching via admin UI at `/admin/config/content/wp-content`
- Database-based theme configuration (overrides JSON config)

### Asset Loading
- Fixed CSS/JS loading using `backdrop_add_css()` and `backdrop_add_js()`
- Proper `wp_head()` and `wp_footer()` buffering
- Dynamic theme CSS loading via generated files

### Template Part Detection
- Automatic detection of template parts in WordPress themes
- Support for nested template directories (e.g., `template-parts/post/content.php`)

## Development Environment

### Worktree Setup
This is a git worktree:
- **Worktree Path:** `/Users/lukemccormick/.claude-worktrees/WP4BD/admiring-clarke`
- **Main Repository:** `/Users/lukemccormick/Sites/BACKDROP/WP4BD`
- **Current Branch:** `admiring-clarke`
- **Main Branch:** `main`

### Local Development
The project uses DDEV for local development. Common commands:
- `ddev bee config-set wp_content.settings active_theme <theme-name>` - Switch active theme
- Access site via DDEV's configured URL

## Implementation Status

### ✅ Completed (Working)
- Template loading system (`get_header()`, `get_footer()`, `get_sidebar()`, `get_template_part()`)
- WordPress Loop (`have_posts()`, `the_post()`, etc.)
- Hook system (`add_action()`, `do_action()`, `add_filter()`, `apply_filters()`)
- Content display functions (`the_title()`, `the_content()`, `the_excerpt()`, etc.)
- Conditional tags (`is_single()`, `is_page()`, `is_home()`, etc.)
- Escaping functions (`esc_html()`, `esc_attr()`, `esc_url()`, etc.)
- Utility functions (`home_url()`, `bloginfo()`, etc.)
- Multi-theme support (can run Twenty 14/15/16/17)
- Admin UI for theme switching

### 🚧 In Progress / Known Gaps
- Some attachment-related functions (`wp_attachment_is_image()`)
- Avatar functions (`get_avatar()`)
- Some taxonomy functions (`get_object_taxonomies()`)
- Widget support (minimal/stub implementation)
- Custom header/background support (partial)

### ❌ Out of Scope
- Block Editor (Gutenberg) themes
- Complex WordPress plugin APIs
- Full WordPress module compatibility
- Post editing/admin functionality (read-only for now)

## Key Documentation Files

### Planning & Specifications
- **`README.md`** - Project overview and introduction
- **`Project Plan_ WordPress Theme Compatibility Layer.md`** - Detailed implementation plan
- **`/implementation/README.md`** - Implementation directory overview
- **`/specs/`** - Individual function specifications

### Implementation Reports
- **`/implementation/IMPLEMENTATION-SUMMARY.md`** - Overall implementation summary
- **`/implementation/WP_THEME_COMPATIBILITY_STATUS.md`** - Theme compatibility status
- **`/implementation/GET_TEMPLATE_PART-DELIVERY.md`** - Template part implementation details
- **`/implementation/WP_QUERY_DELIVERY.md`** - WP_Query implementation details
- **`/implementation/UTILITIES-DELIVERY.md`** - Utility functions details
- **`/implementation/ESCAPING_IMPLEMENTATION_REPORT.md`** - Escaping functions report
- **`/implementation/POST_CLASS_IMPLEMENTATION.md`** - Post class implementation
- **`/implementation/PERMALINK-IMPLEMENTATION-SUMMARY.md`** - Permalink implementation

### Debugging & Reference
- **`/DEBUGGING/`** - Debugging documentation and logs
- **`/REFERENCE/`** - WordPress reference materials
- **`CHANGELOG.md`** - Detailed change log with version tracking

## Important Patterns & Conventions

### Function Naming
- WordPress functions use WordPress naming conventions (snake_case)
- Internal helper functions are prefixed with `_wp2bd_` or `_wp_`

### Global Variables
Key WordPress globals that are maintained:
- `$post` - Current post object (WP_Post instance)
- `$wp_query` - Main query object (WP_Query instance)
- `$wp_the_query` - Original query object backup
- `$pagenow` - Current admin page
- `$theme` - Active theme name

### Mapping Strategy
WordPress functions map to Backdrop equivalents:
- WordPress posts → Backdrop nodes
- WordPress themes → Backdrop themes (wrapped)
- WordPress hooks → Custom hook implementation (not Backdrop hooks)
- WordPress template hierarchy → Custom template loading

### Code Style
- Follow WordPress coding standards for compatibility layer code
- Use Backdrop coding standards for module integration code
- Maintain WordPress DocBlock format for WordPress-style functions

## Testing

### Test Location
- **`/implementation/tests/`** - Test files for each function/class
- Tests use Backdrop's testing framework
- Run tests via: `php core/scripts/run-tests.sh --file <test-file>`

### Current Test Status
- Template loading functions have comprehensive tests
- Other functions need test coverage expansion

## Common Tasks

### Adding a New WordPress Function

1. Determine which category file it belongs in (`/implementation/functions/`)
2. Implement the function following WordPress conventions
3. Map to appropriate Backdrop API calls
4. Add to the auto-loading system (already handles all files in `/functions/`)
5. Create tests in `/implementation/tests/`
6. Document in relevant implementation report

### Switching Themes

```bash
# Via command line (DDEV)
ddev bee config-set wp_content.settings active_theme twentyseventeen

# Or use the admin UI
# Navigate to: /admin/config/content/wp-content
```

### Debugging Theme Issues

1. Check CHANGELOG.md for recent fixes
2. Look in `/DEBUGGING/` for known issues
3. Check browser console for JS errors
4. Check Backdrop logs for PHP errors
5. Review theme's functions.php for compatibility issues

## Architecture Decisions

### Why Not Use Backdrop's Hook System?
WordPress themes expect WordPress hooks behavior, which differs from Backdrop. We implement a custom hook system that maintains WordPress semantics.

### Why Organized Function Files Instead of Stubs?
- **Maintainability:** Easier to find and update related functions
- **Performance:** Can selectively load only needed function categories
- **Organization:** Logical grouping makes the codebase more navigable
- **Documentation:** Each file can have focused documentation

### Why Support Multiple Themes?
Validates that the compatibility layer is truly generic and not hardcoded to one theme's quirks.

## Future Roadmap

### Phase 1: Foundation ✅
- Module skeleton
- Core function organization
- Testing framework

### Phase 2: Core Compatibility ✅
- Template loading
- The Loop
- Content display
- Hook system
- Basic conditionals

### Phase 3: Theme Refinement 🚧
- Asset loading improvements
- Widget support
- Custom header/background
- Menu system improvements

### Phase 4: Expansion 📋
- Additional theme support
- Performance optimization
- Caching layer
- Documentation for end users

## Known Limitations

1. **Read-Only:** Currently focused on displaying content, not editing
2. **Template Hierarchy:** Simplified compared to full WordPress hierarchy
3. **Plugin Support:** Minimal - themes only
4. **Widgets:** Basic/stub implementation
5. **Customizer:** Not supported

## Tips for AI Assistants

### When Working on This Project:

1. **Always check existing implementations first** - Many WordPress functions are already implemented in `/implementation/functions/`

2. **Maintain WordPress compatibility** - Code must work with unmodified WordPress themes

3. **Use organized function files** - Don't create monolithic files; use the category system

4. **Reference documentation** - Check `/implementation/` docs for implementation patterns

5. **Test with multiple themes** - Changes should work across Twenty 14/15/16/17

6. **Preserve WordPress semantics** - Don't "improve" WordPress APIs; maintain compatibility

7. **Document mapping decisions** - When mapping WP → Backdrop, document why

### Common Pitfalls:

- Don't assume WordPress themes will adapt to Backdrop conventions
- Don't break global state (`$post`, `$wp_query`) - themes rely on it
- Don't skip hook calls - themes expect them to fire
- Don't modify WordPress theme files - that defeats the purpose

### File Locations to Know:

- WordPress functions: `/implementation/functions/*.php`
- WordPress classes: `/implementation/classes/*.php`
- Tests: `/implementation/tests/*.php`
- Documentation: `/implementation/*.md` and `/DOCS/`
- Themes: `/wordpress-4.9/wp-content/themes/`

## Contact & Version Info

**Project Name:** WP4BD (WordPress for Backdrop)
**Current Phase:** Phase 2/3 - Core compatibility complete, refinement in progress
**License:** [To be determined]
**Last Updated:** 2025-12-01 (auto-generated from git)

---

**This file is for AI assistant context. Keep it updated as the project evolves.**
