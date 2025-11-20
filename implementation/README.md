# WP2BD Implementation Directory

This directory contains the WordPress to Backdrop (WP2BD) compatibility layer implementation.

## Purpose

Enable WordPress themes (specifically Twenty Seventeen) to run on Backdrop CMS 1.30 by implementing WordPress functions that map to Backdrop's API.

---

## Directory Structure

```
implementation/
├── functions/           # WordPress function implementations
│   └── template-loading.php
├── classes/            # WordPress class mocks (WP_Query, WP_Post, etc.)
├── tests/              # Test files for each function/class
│   └── get_header.test.php
└── docs/               # Additional documentation
```

---

## Implemented Functions

### Template Loading Functions (P0 - Critical)

**File:** `/functions/template-loading.php`
**Status:** ✅ Complete
**Test File:** `/tests/get_header.test.php`

| Function | Description | Status |
|----------|-------------|--------|
| `get_header($name)` | Load header template | ✅ Implemented |
| `get_footer($name)` | Load footer template | ✅ Implemented |
| `get_sidebar($name)` | Load sidebar template | ✅ Implemented |
| `get_template_part($slug, $name)` | Load template parts | ✅ Implemented |

**Features:**
- Named template support (`header-custom.php`)
- Child theme inheritance
- Action hook integration (`get_header`, `get_footer`, etc.)
- Fallback to default templates
- Returns true/false for success/failure

**Dependencies:**
- Backdrop's `backdrop_get_path()`
- Backdrop's `list_themes()`
- Hook system (`do_action()`)

**Tests:** 8 test cases covering:
1. Basic template loading
2. Named template loading
3. Fallback behavior
4. File not found handling
5. Action hook firing
6. Child theme support
7. Multiple calls (require_once)
8. Path resolution

---

## Usage

### For Module Integration

```php
// In wp2bd.module
require_once backdrop_get_path('module', 'wp2bd') . '/implementation/functions/template-loading.php';
```

### For Theme Developers

```php
// In theme template files (index.php, single.php, etc.)
<?php get_header(); ?>

<main class="site-content">
    <?php
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            get_template_part('template-parts/post/content', get_post_format());
        }
    }
    ?>
</main>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
```

---

## Testing

### Run Individual Test

```bash
php core/scripts/run-tests.sh --file implementation/tests/get_header.test.php
```

### Run All Tests

```bash
php core/scripts/run-tests.sh --directory implementation/tests/
```

---

## Implementation Progress

### Phase 1: Foundation
- [ ] Module skeleton
- [ ] Testing framework
- [ ] Logging utilities

### Phase 2: Core Functions - P0

#### A. The Loop System
- [ ] `have_posts()`
- [ ] `the_post()`
- [ ] `wp_reset_postdata()`
- [ ] `WP_Query` class
- [ ] `WP_Post` class

#### B. Template Loading ✅ COMPLETE
- [x] `get_header()` - WP2BD-010
- [x] `get_footer()` - WP2BD-011
- [x] `get_sidebar()` - WP2BD-012
- [x] `get_template_part()` - WP2BD-013

#### C. Content Display
- [ ] `the_title()` + `get_the_title()`
- [ ] `the_permalink()` + `get_permalink()`
- [ ] `the_ID()` + `get_the_ID()`
- [ ] `the_content()`
- [ ] `the_excerpt()`

#### D. Core Conditionals
- [ ] `is_single()`, `is_singular()`
- [ ] `is_page()`
- [ ] `is_home()`, `is_front_page()`
- [ ] `get_post_type()`

#### E. Escaping Functions
- [ ] `esc_html()`, `esc_attr()`
- [ ] `esc_url()`, `esc_url_raw()`

#### F. Hook System
- [ ] `add_action()`, `do_action()`
- [ ] `add_filter()`, `apply_filters()`
- [ ] `wp_head()`
- [ ] `wp_footer()`

#### G. Utility Functions
- [ ] `home_url()`
- [ ] `bloginfo()`, `get_bloginfo()`

---

## Development Guidelines

### Coding Standards

Follow WordPress coding standards for consistency with WP themes:

```php
// Function naming: WordPress style
function get_header( $name = null ) {
    // Use WordPress conventions
}

// Comments: Use WordPress DocBlock format
/**
 * Load header template.
 *
 * @param string|null $name Optional header name.
 * @return bool True on success, false on failure.
 */
```

### Testing Requirements

Each function must have:
- ✅ Minimum 3 test cases
- ✅ Edge case coverage
- ✅ Integration tests where applicable
- ✅ Documentation of expected behavior

### Documentation Requirements

Each implementation must include:
- ✅ Function spec in `/specs/WP2BD-XXX.md`
- ✅ Inline code comments
- ✅ Usage examples
- ✅ Backdrop mapping strategy

---

## Backdrop Integration Notes

### Theme Path Resolution

WordPress themes should be placed in Backdrop's theme directory:

```
backdrop/
├── themes/
│   └── twentyseventeen/
│       ├── twentyseventeen.info  (Backdrop theme info)
│       ├── header.php
│       ├── footer.php
│       ├── index.php
│       └── ...
```

### Theme Info File

Create a `.info` file for Backdrop theme system:

```ini
name = Twenty Seventeen
description = WordPress Twenty Seventeen theme
type = theme
backdrop = 1.x
```

### Global Variables

The implementation uses these globals:
- `$theme` - Active theme name
- `$post` - Current post object (WP_Post)
- `$wp_query` - Current query object (WP_Query)

These must be initialized by the module bootstrap.

---

## Helper Functions

### Internal Functions (Prefixed with _)

These are internal helpers not part of WordPress API:

**`_wp2bd_get_theme_info($theme_name)`**
- Gets theme information from Backdrop
- Caches results for performance
- Returns theme info array or empty array

---

## Dependencies

### Backdrop Functions Used

- `backdrop_get_path($type, $name)` - Get path to module/theme
- `list_themes()` - Get all theme information
- `config_get($config, $key)` - Get configuration values
- `theme_enable($themes)` - Enable themes
- `config_set($config, $key, $value)` - Set configuration

### Constants Used

- `BACKDROP_ROOT` - Root directory of Backdrop installation

---

## Known Issues & Limitations

1. **Template Hierarchy**: Full WordPress template hierarchy not implemented
2. **Template Caching**: No caching of resolved template paths
3. **require_once**: Multiple calls to same template won't re-include (by design)
4. **Theme Locations**: Only supports standard Backdrop theme directory

---

## Next Steps

1. **Implement The Loop** (WP2BD-LOOP)
   - Most critical for content display
   - Required for any page to render

2. **Implement Hook System** (WP2BD-050)
   - Foundation for many other functions
   - Required for `wp_head()`, `wp_footer()`

3. **Implement Content Display** (WP2BD-020-027)
   - `the_title()`, `the_content()`, etc.
   - Required to display actual content

---

## Contributing

When adding new implementations:

1. Create function in appropriate file under `/functions/`
2. Create test file under `/tests/`
3. Create spec document under `/specs/`
4. Update this README with implementation status
5. Ensure all tests pass
6. Document Backdrop mapping strategy

---

## References

- **Project Plan:** `/Project Plan_ WordPress Theme Compatibility Layer.md`
- **Critical Functions:** `/critical-functions.md`
- **Implementation Roadmap:** `/IMPLEMENTATION-ROADMAP.md`
- **Specs Directory:** `/specs/`

---

## Contact & Support

**Project:** WP2BD - WordPress to Backdrop Theme Compatibility
**Target Theme:** Twenty Seventeen
**Backdrop Version:** 1.30
**WordPress Version:** 4.9

---

**Last Updated:** 2025-11-20
**Status:** Phase 2B (Template Loading) - Complete ✅
