# Generic WordPress Header Image Support

## Problem

Twenty Seventeen (and other WordPress themes) expect header images via `get_header_image()` and `get_custom_header()` functions. Currently these return empty values, so themes fall back to hardcoded default images.

## User Requirements

> WITHOUT hard-coding seventeen-specific code anywhere

Must work generically for all WordPress themes that use the standard WordPress Custom Header API.

## Proposed Changes

### [themes/wp/functions/stubs.php](file:///Users/lukemccormick/Sites/BACKDROP/00-test/wp2bd/backdrop-1.30/themes/wp/functions/stubs.php)

Replace stub implementations of header functions with generic logic:

#### `get_header_image()`
- Check if theme has a default header image in `assets/images/header.*`
- Return URL to that image if it exists
- Fall back to empty string if not found

#### `get_custom_header()`  
- Return object with properties:
  - `url`: from `get_header_image()`
  - `width`, `height`: read from image if possible, or use common defaults (2000x1200)
  - Other standard Custom Header API properties

#### `get_header_image_tag()`
- Generate proper `<img>` tag HTML
- Use `get_custom_header()` for attributes

### Generic Approach

```php
function get_header_image() {
  // Get active theme directory
  $theme_dir = get_template_directory();
  
  // Check for common header image locations
  $possible_images = array(
    'assets/images/header.jpg',
    'assets/images/header.png',
    'images/header.jpg',
    'images/header.png',
  );
  
  foreach ($possible_images as $image_path) {
    if (file_exists($theme_dir . '/' . $image_path)) {
      return get_template_directory_uri() . '/' . $image_path;
    }
  }
  
  return '';
}
```

## Verification Plan

### Test with Multiple Themes
- Twenty Seventeen: has `assets/images/header.jpg`
- Twenty Sixteen: check header image location
- Twenty Twelve: check header image location

### Manual Verification
1. Load site with Twenty Seventeen
2. Verify hero image displays
3. Switch to another theme
4. Verify header image works (or gracefully falls back)
