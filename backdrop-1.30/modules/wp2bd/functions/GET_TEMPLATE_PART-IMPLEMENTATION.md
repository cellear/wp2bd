# get_template_part() Implementation - WP2BD

## Implementation Summary

**Status:** ✅ COMPLETE
**File:** `/home/user/wp2bd/implementation/functions/template-loading.php`
**Test File:** `/home/user/wp2bd/implementation/tests/get_template_part.test.php`
**Lines of Code:** 280 (implementation), 564 (tests)
**Test Coverage:** 10 test cases, 28 assertions
**Test Results:** ✅ All tests passing

---

## Implementation Details

### Function Signature

```php
function get_template_part($slug, $name = null)
```

### Parameters

- **`$slug`** (string, required): The slug name for the generic template
  - Example: `'content'`, `'template-parts/post/content'`

- **`$name`** (string|null, optional): The name of the specialized template
  - Example: `'excerpt'`, `'single'`, `404`
  - Default: `null`

### Return Value

- **`true`**: Template was found and loaded successfully
- **`false`**: No template was found

---

## WordPress Compatibility Analysis

### ✅ Core Behavior Match

| Requirement | WordPress 4.9 | WP2BD Implementation | Status |
|-------------|---------------|----------------------|--------|
| Fires action hook | `do_action("get_template_part_{$slug}", $slug, $name)` | ✅ Identical | ✅ MATCH |
| Cast name to string | `$name = (string) $name;` | ✅ Identical | ✅ MATCH |
| Check for non-empty name | `if ('' !== $name)` | ✅ Identical | ✅ MATCH |
| Build template array | `["{$slug}-{$name}.php", "{$slug}.php"]` | ✅ Identical | ✅ MATCH |
| Include with `require` | ✅ Uses `require` (not `require_once`) | ✅ Uses `require` | ✅ MATCH |
| Return boolean | WordPress doesn't return, but uses `locate_template()` | ✅ Returns true/false | ⚡ ENHANCED |

### WordPress Implementation (Reference)

```php
function get_template_part( $slug, $name = null ) {
    do_action( "get_template_part_{$slug}", $slug, $name );

    $templates = array();
    $name = (string) $name;
    if ( '' !== $name )
        $templates[] = "{$slug}-{$name}.php";

    $templates[] = "{$slug}.php";

    locate_template($templates, true, false);
}
```

### WP2BD Implementation

```php
function get_template_part($slug, $name = null) {
    global $theme;

    do_action("get_template_part_{$slug}", $slug, $name);

    // Get active theme
    $active_theme = $theme;
    if (empty($active_theme)) {
        $active_theme = config_get('system.core', 'theme_default');
    }

    // Support child/parent theme hierarchy
    $themes_to_check = array($active_theme);
    $theme_info = _wp2bd_get_theme_info($active_theme);
    if (!empty($theme_info['base theme'])) {
        $themes_to_check[] = $theme_info['base theme'];
    }

    // Build template list (matches WordPress)
    $templates = array();
    $name = (string) $name;
    if ('' !== $name) {
        $templates[] = "{$slug}-{$name}.php";
    }
    $templates[] = "{$slug}.php";

    // Locate and load template
    foreach ($themes_to_check as $theme_name) {
        $theme_path = backdrop_get_path('theme', $theme_name);
        if (empty($theme_path)) {
            continue;
        }

        foreach ($templates as $template) {
            $template_file = BACKDROP_ROOT . '/' . $theme_path . '/' . $template;
            if (file_exists($template_file)) {
                require $template_file;
                return true;
            }
        }
    }

    return false;
}
```

---

## Key Features

### 1. ✅ Template Priority Resolution

The implementation correctly tries templates in this order:

```php
get_template_part('template-parts/post/content', 'excerpt');
```

Tries:
1. `template-parts/post/content-excerpt.php` (specialized)
2. `template-parts/post/content.php` (generic fallback)

### 2. ✅ Action Hook Firing

Before loading any template:

```php
do_action("get_template_part_{$slug}", $slug, $name);
```

Example: `get_template_part('content', 'single')` fires:
- Hook: `get_template_part_content`
- Args: `['content', 'single']`

### 3. ✅ Child/Parent Theme Support

Checks templates in Backdrop theme hierarchy:
1. Child theme (if applicable)
2. Parent theme

### 4. ✅ Proper File Inclusion

Uses `require` (NOT `require_once`), allowing multiple inclusions of the same template.

### 5. ✅ Boolean Return Value

Enhancement over WordPress:
- Returns `true` if template loaded
- Returns `false` if not found
- Allows conditional handling in calling code

### 6. ✅ Path Resolution

Supports nested directory structures:
```php
get_template_part('template-parts/post/content', 'single');
```

Correctly resolves:
```
{theme_path}/template-parts/post/content-single.php
```

---

## Test Coverage

### Test Suite: 10 Test Cases, 28 Assertions

#### ✅ TEST 1: Simple slug only
Tests basic template loading with just a slug parameter.
```php
get_template_part('content');
// Loads: content.php
```

#### ✅ TEST 2: Slug with name
Tests specialized template priority over generic.
```php
get_template_part('content', 'excerpt');
// Tries: content-excerpt.php, then content.php
```

#### ✅ TEST 3: Fallback to generic
Tests fallback behavior when specialized template doesn't exist.
```php
get_template_part('content', 'missing');
// Specialized not found, loads: content.php
```

#### ✅ TEST 4: Nested template parts
Tests nested directory structure handling.
```php
get_template_part('template-parts/post/content', 'excerpt');
// Loads: template-parts/post/content-excerpt.php
```

#### ✅ TEST 5: Return false when not found
Tests proper false return when template doesn't exist.
```php
$result = get_template_part('nonexistent');
// Returns: false
```

#### ✅ TEST 6: Action hook fired
Verifies action hook is called with correct parameters.
```php
// Fires: get_template_part_{$slug}
// Args: [$slug, $name]
```

#### ✅ TEST 7: Empty name parameter
Tests handling of empty string and null name values.
```php
get_template_part('content', '');
get_template_part('content', null);
// Both load: content.php (no specialized template attempted)
```

#### ✅ TEST 8: Multiple inclusions
Verifies template can be included multiple times (require, not require_once).
```php
get_template_part('item'); // Count: 1
get_template_part('item'); // Count: 2
get_template_part('item'); // Count: 3
```

#### ✅ TEST 9: Special characters
Tests hyphenated and special character handling.
```php
get_template_part('loop-post', 'single');
// Loads: loop-post-single.php
```

#### ✅ TEST 10: Numeric name parameter
Tests numeric name conversion to string.
```php
get_template_part('content', 404);
// Loads: content-404.php
```

---

## Real-World Usage Examples

### Example 1: Twenty Seventeen Theme

```php
// In index.php or archive.php
while (have_posts()) {
    the_post();
    get_template_part('template-parts/post/content', get_post_format());
}
```

### Example 2: Conditional Loading

```php
if (is_singular()) {
    get_template_part('template-parts/content', 'single');
} elseif (is_search()) {
    get_template_part('template-parts/content', 'search');
} else {
    get_template_part('template-parts/content', 'excerpt');
}
```

### Example 3: Custom Post Types

```php
$post_type = get_post_type();
get_template_part('template-parts/content', $post_type);
// For 'product' post type: content-product.php
```

---

## Implementation Complexity

**Rating:** 🟡 MODERATE

### Why Moderate?

1. **File Path Resolution**: Must handle nested directories correctly
2. **Theme Hierarchy**: Must check child theme before parent
3. **Flexible Parameters**: Must handle null, empty string, numeric values
4. **Hook System**: Must integrate with action hook system
5. **WordPress Compatibility**: Must match exact WordPress behavior

### Comparison with Other Template Functions

| Function | Complexity | Reason |
|----------|-----------|---------|
| `get_header()` | 🟢 SIMPLE | Fixed filename pattern (header.php) |
| `get_footer()` | 🟢 SIMPLE | Fixed filename pattern (footer.php) |
| `get_sidebar()` | 🟢 SIMPLE | Fixed filename pattern (sidebar.php) |
| `get_template_part()` | 🟡 MODERATE | Dynamic paths, priority resolution |

---

## Usage in Twenty Seventeen Theme

**Total Usage Count:** 30+ occurrences

### Most Common Pattern

```php
get_template_part('template-parts/post/content', get_post_format());
```

### Template Files Used

1. `template-parts/post/content.php` (generic)
2. `template-parts/post/content-excerpt.php` (archives)
3. `template-parts/post/content-single.php` (single posts)
4. `template-parts/post/content-search.php` (search results)
5. `template-parts/post/content-none.php` (no results)
6. `template-parts/page/content-front-page.php` (front page)
7. `template-parts/footer/footer-widgets.php` (footer widgets)

---

## Backdrop CMS Integration

### Theme Path Resolution

```php
backdrop_get_path('theme', $theme_name)
```

Returns the path to the theme directory relative to Backdrop root.

### Theme Hierarchy

```php
_wp2bd_get_theme_info($theme_name)
```

Gets theme info including base theme for child theme support.

### Configuration

```php
config_get('system.core', 'theme_default')
```

Gets the default active theme when `$theme` global is not set.

---

## Testing Instructions

### Run the Test Suite

```bash
php /home/user/wp2bd/implementation/tests/get_template_part.test.php
```

### Expected Output

```
=================================================
  WP2BD get_template_part() Test Suite
=================================================

--- TEST 1: Simple slug only (content.php) ---
✓ PASS: Template file content.php exists
✓ PASS: Template loaded successfully
✓ PASS: Template output is correct

[... 9 more test cases ...]

=================================================
  Test Results
=================================================
Passed: 28
Failed: 0
Total:  28
=================================================
```

---

## Performance Considerations

### File System Calls

For each `get_template_part()` call:
- Maximum 2 templates checked (specialized + generic)
- Maximum 2 theme directories checked (child + parent)
- Total: **Maximum 4 file_exists() checks**

### Optimization

The implementation is optimized with:
- Early returns when template found
- Skipping empty theme paths
- Static caching in `_wp2bd_get_theme_info()`

---

## Differences from WordPress

### Enhancements

1. **Return Value**: WordPress's `locate_template()` returns path but `get_template_part()` returns nothing. WP2BD returns boolean for easier conditional handling.

2. **Backdrop Integration**: Seamlessly integrates with Backdrop's theme system while maintaining WordPress compatibility.

### Maintained Compatibility

1. **Action Hook**: Identical hook name and parameters
2. **Template Resolution**: Identical priority order
3. **Name Handling**: Identical string casting and empty check
4. **Multiple Inclusions**: Uses `require` like WordPress

---

## Files Delivered

### Implementation

📄 `/home/user/wp2bd/implementation/functions/template-loading.php`
- 280 lines
- Includes `get_template_part()` and helper functions
- Backdrop CMS integrated

### Tests

📄 `/home/user/wp2bd/implementation/tests/get_template_part.test.php`
- 564 lines
- 10 test cases covering all scenarios
- 28 total assertions
- All tests passing

### Documentation

📄 `/home/user/wp2bd/implementation/functions/USAGE-EXAMPLE.md`
- Complete usage guide
- Real-world examples from Twenty Seventeen
- Best practices
- Integration instructions

📄 `/home/user/wp2bd/implementation/functions/GET_TEMPLATE_PART-IMPLEMENTATION.md`
- This file
- Complete implementation analysis
- WordPress compatibility verification
- Test coverage details

---

## Sign-Off

✅ **Implementation Complete**
✅ **All Tests Passing**
✅ **WordPress Compatible**
✅ **Backdrop Integrated**
✅ **Fully Documented**

**Ready for integration into WP2BD compatibility layer.**
