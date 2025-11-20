# get_header() Integration Example

This document shows how to integrate and use the `get_header()` function in a WordPress theme running on Backdrop CMS.

---

## Quick Start

### 1. Load the WP2BD Module

In your Backdrop installation, the WP2BD module should load the template-loading functions:

```php
// In wp2bd.module
function wp2bd_init() {
    // Load template loading functions
    require_once backdrop_get_path('module', 'wp2bd') . '/implementation/functions/template-loading.php';

    // Load hook system (required for do_action)
    require_once backdrop_get_path('module', 'wp2bd') . '/implementation/functions/hooks.php';
}
```

### 2. Set Up a WordPress Theme in Backdrop

Place your WordPress theme in Backdrop's themes directory:

```bash
cp -r wordpress-4.9/wp-content/themes/twentyseventeen backdrop/themes/
```

Create a `.info` file for Backdrop:

```bash
cat > backdrop/themes/twentyseventeen/twentyseventeen.info << 'EOF'
name = Twenty Seventeen
description = WordPress Twenty Seventeen theme
type = theme
backdrop = 1.x
EOF
```

Enable the theme:

```php
theme_enable(array('twentyseventeen'));
config_set('system.core', 'theme_default', 'twentyseventeen');
```

### 3. Use get_header() in Templates

The function works exactly like WordPress:

```php
<?php
/**
 * The main template file (index.php)
 */

// Load header
get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <?php
        if (have_posts()) {
            while (have_posts()) {
                the_post();
                get_template_part('template-parts/post/content', get_post_format());
            }
        }
        ?>
    </main>
</div>

<?php
get_sidebar();
get_footer();
?>
```

---

## Real-World Example: Twenty Seventeen Theme

### Theme Structure

```
themes/twentyseventeen/
├── twentyseventeen.info       (Backdrop theme info)
├── header.php                  (Default header)
├── header-home.php             (Custom home header)
├── footer.php
├── sidebar.php
├── index.php
├── single.php
├── page.php
└── template-parts/
    ├── header/
    │   └── header-image.php
    ├── post/
    │   ├── content.php
    │   └── content-excerpt.php
    └── navigation/
        └── navigation-top.php
```

### Example: index.php

```php
<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 */

get_header();
?>

<div class="wrap">
    <?php if (is_home() && !is_front_page()) : ?>
        <header class="page-header">
            <h1 class="page-title"><?php _e('Posts', 'twentyseventeen'); ?></h1>
        </header>
    <?php else : ?>
        <header class="page-header">
            <h2 class="page-title"><?php _e('Archives', 'twentyseventeen'); ?></h2>
        </header>
    <?php endif; ?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">

            <?php
            if (have_posts()) :
                while (have_posts()) :
                    the_post();

                    // Include the appropriate template part
                    get_template_part('template-parts/post/content', get_post_format());

                endwhile;

                the_posts_pagination();

            else :
                get_template_part('template-parts/post/content', 'none');
            endif;
            ?>

        </main>
    </div>

    <?php get_sidebar(); ?>

</div>

<?php
get_footer();
?>
```

### Example: header.php

```php
<?php
/**
 * The header for our theme
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#content">
        <?php _e('Skip to content', 'twentyseventeen'); ?>
    </a>

    <header id="masthead" class="site-header" role="banner">
        <?php get_template_part('template-parts/header/header', 'image'); ?>

        <?php if (has_nav_menu('top')) : ?>
            <div class="navigation-top">
                <div class="wrap">
                    <?php get_template_part('template-parts/navigation/navigation', 'top'); ?>
                </div>
            </div>
        <?php endif; ?>
    </header>

    <?php
    // Featured image for single posts
    if ((is_single() || (is_page() && !twentyseventeen_is_frontpage()))
        && has_post_thumbnail(get_queried_object_id())) :
        echo '<div class="single-featured-image-header">';
        echo get_the_post_thumbnail(get_queried_object_id(), 'twentyseventeen-featured-image');
        echo '</div>';
    endif;
    ?>

    <div class="site-content-contain">
        <div id="content" class="site-content">
```

### Example: front-page.php with Custom Header

```php
<?php
/**
 * The front page template file
 */

// Use custom header for home page
get_header('home');
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

        <?php
        // Show static front page content or latest posts
        while (have_posts()) :
            the_post();
            get_template_part('template-parts/page/content', 'front-page');
        endwhile;
        ?>

        <?php
        // Show front page panels
        $panel_count = twentyseventeen_panel_count();
        for ($i = 1; $i <= $panel_count; $i++) {
            get_template_part('template-parts/page/content', 'front-page-panels');
        }
        ?>

    </main>
</div>

<?php
get_footer();
?>
```

### Example: header-home.php (Custom Home Header)

```php
<?php
/**
 * Custom header for home page
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site">

    <!-- Large hero banner for home page -->
    <header id="masthead" class="site-header site-header-home" role="banner">
        <div class="custom-header">
            <div class="hero-banner">
                <?php the_custom_header_markup(); ?>
            </div>
        </div>

        <div class="site-branding">
            <h1 class="site-title">
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    <?php bloginfo('name'); ?>
                </a>
            </h1>
            <p class="site-description"><?php bloginfo('description'); ?></p>
        </div>

        <?php if (has_nav_menu('top')) : ?>
            <div class="navigation-top">
                <div class="wrap">
                    <?php get_template_part('template-parts/navigation/navigation', 'top'); ?>
                </div>
            </div>
        <?php endif; ?>
    </header>

    <div class="site-content-contain">
        <div id="content" class="site-content">
```

---

## Advanced Usage

### 1. Hooking into Header Loading

Add custom code before header is loaded:

```php
// In theme's functions.php
add_action('get_header', 'my_custom_header_logic');

function my_custom_header_logic($name) {
    // Add custom meta tags
    if ($name === 'home') {
        add_action('wp_head', 'add_home_page_meta');
    }

    // Load additional scripts
    if (is_single()) {
        wp_enqueue_script('single-post-js');
    }
}
```

### 2. Conditional Headers Based on Page Type

```php
<?php
// In template file
if (is_front_page()) {
    get_header('home');
} elseif (is_single()) {
    get_header('single');
} elseif (is_page('about')) {
    get_header('about');
} else {
    get_header();
}
?>
```

### 3. Child Theme Override

Create a child theme that overrides parent header:

```bash
# Create child theme
mkdir themes/twentyseventeen-child

# Create info file
cat > themes/twentyseventeen-child/twentyseventeen-child.info << 'EOF'
name = Twenty Seventeen Child
description = Child theme of Twenty Seventeen
type = theme
base theme = twentyseventeen
backdrop = 1.x
EOF

# Create custom header
cat > themes/twentyseventeen-child/header.php << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>Child Theme Header</title>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <header class="child-theme-header">
        <h1>Custom Child Theme Header</h1>
    </header>
EOF
```

Enable child theme:

```php
theme_enable(array('twentyseventeen', 'twentyseventeen-child'));
config_set('system.core', 'theme_default', 'twentyseventeen-child');
```

Now `get_header()` will load from child theme first, falling back to parent.

---

## Troubleshooting

### Problem: Header not loading

**Check:**
1. Is the theme enabled?
   ```php
   $themes = list_themes();
   print_r(array_keys($themes));
   ```

2. Does header.php exist?
   ```bash
   ls -la themes/twentyseventeen/header.php
   ```

3. Is `$theme` global set?
   ```php
   global $theme;
   var_dump($theme);
   ```

### Problem: Wrong header loading

**Check template resolution:**

```php
// Add debug code before get_header()
global $theme;
$theme_path = backdrop_get_path('theme', $theme);
$header_file = BACKDROP_ROOT . '/' . $theme_path . '/header.php';
echo "Looking for: $header_file\n";
echo "Exists: " . (file_exists($header_file) ? 'YES' : 'NO') . "\n";

get_header();
```

### Problem: Child theme not working

**Verify theme info:**

```php
$themes = list_themes();
$child_info = $themes['twentyseventeen-child']->info;
print_r($child_info);

// Should show:
// Array (
//     [base theme] => twentyseventeen
//     ...
// )
```

---

## Performance Tips

1. **Use require_once**: The function already uses `require_once`, so don't worry about multiple calls

2. **Cache theme info**: The `_wp2bd_get_theme_info()` helper uses static caching

3. **Minimize conditional headers**: Each conditional check adds overhead

4. **Avoid complex logic in templates**: Keep header.php simple

---

## Testing Your Implementation

### Unit Test

```php
// Create test
class MyHeaderTest extends BackdropWebTestCase {
    public function testHeaderLoads() {
        global $theme;
        $theme = 'twentyseventeen';

        ob_start();
        $result = get_header();
        $output = ob_get_clean();

        $this->assertTrue($result);
        $this->assertContains('<!DOCTYPE html>', $output);
    }
}
```

### Manual Test

```php
// In a test script
require_once 'backdrop/core/includes/bootstrap.inc';
backdrop_bootstrap(BACKDROP_BOOTSTRAP_FULL);

global $theme;
$theme = 'twentyseventeen';

ob_start();
$result = get_header();
$output = ob_get_clean();

echo "Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
echo "Output length: " . strlen($output) . " bytes\n";
echo "Output preview:\n" . substr($output, 0, 200) . "...\n";
```

---

## Integration Checklist

- [ ] WP2BD module enabled
- [ ] Hook system implemented (for `do_action`)
- [ ] Theme placed in `themes/` directory
- [ ] Theme `.info` file created
- [ ] Theme enabled via `theme_enable()`
- [ ] Default theme set via `config_set()`
- [ ] `header.php` exists in theme
- [ ] `$theme` global is set
- [ ] Test with `get_header()` call
- [ ] Verify output is correct

---

## Next Steps

After implementing `get_header()`, you'll want:

1. **The Loop** (`have_posts()`, `the_post()`) - To display content
2. **Content functions** (`the_title()`, `the_content()`) - To output post data
3. **wp_head()** - To output scripts/styles in header
4. **wp_footer()** - To output scripts in footer

---

## Reference

- **Implementation:** `/home/user/wp2bd/implementation/functions/template-loading.php`
- **Tests:** `/home/user/wp2bd/implementation/tests/get_header.test.php`
- **Spec:** `/home/user/wp2bd/specs/WP2BD-010.md`
- **WordPress Docs:** https://developer.wordpress.org/reference/functions/get_header/
