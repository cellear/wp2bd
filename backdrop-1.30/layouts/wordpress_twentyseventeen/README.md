# WordPress Twenty Seventeen Layout

This is a custom Backdrop layout optimized for the WordPress Twenty Seventeen theme. It provides WordPress-compatible HTML structure and includes the critical `.site-content-contain` wrapper div that creates the parallax scrolling effect.

## Features

- **Parallax Header Support**: Includes the `<div class="site-content-contain">` wrapper with white background that creates the scrolling parallax effect over the fixed header image
- **WordPress-Compatible Markup**: Uses WordPress div structure (`#page`, `#content`, etc.) instead of Backdrop's default `.l-wrapper` divs
- **Four Content Regions**: Header, Content, Sidebar, and Footer regions matching WordPress block architecture
- **Coordinated with wp_content module**: Works seamlessly with the block rendering functions that strip wrapper divs from WordPress templates

## How It Works

The WordPress Twenty Seventeen theme's templates (`header.php`, `footer.php`) output complete HTML documents with wrapper divs like:

```html
<!DOCTYPE html>
<html>
<head>...</head>
<body>
  <div id="page" class="site">
    <header>...</header>
    <div class="site-content-contain">
      <div id="content" class="site-content">
        ...content...
      </div>
      <footer>...</footer>
    </div>
  </div>
</body>
</html>
```

Since Backdrop needs to control the HTML/head/body structure, the `wp_content` module's rendering functions strip these wrapper divs out, leaving just the interior content. This layout then adds them back in the right places so all blocks are properly wrapped.

## How to Use

### 1. Enable the Layout

1. Go to **Structure > Layouts** in your Backdrop admin
2. Click "Add layout"
3. Select the "WordPress Twenty Seventeen" layout template
4. Configure the layout path (e.g., set as default for all pages or specific paths)
5. Save the layout

### 2. Place the WordPress Blocks

Add the following blocks to the corresponding regions:

- **Header region**: Add the "WordPress Header" block
- **Content region**: Add the "WordPress Content" block
- **Sidebar region**: Add the "WordPress Sidebar" block
- **Footer region**: Add the "WordPress Footer" block

### 3. Save and View

Save the layout and view your site. You should now see:
- The WordPress Twenty Seventeen header with parallax image
- Content and sidebar in proper WordPress structure
- The white background scrolling over the fixed header image (parallax effect)

## How the Parallax Effect Works

The parallax effect in WordPress Twenty Seventeen works like this:

1. The header image is set as a **fixed background** via CSS (in `style.css`)
2. The `<div class="site-content-contain">` wrapper has these CSS rules:
   ```css
   .site-content-contain {
     background-color: #fff;
     position: relative;
   }
   ```
3. As you scroll down, the white div slides over the fixed background image, creating the parallax effect

This layout provides that critical `site-content-contain` wrapper so the effect works properly.

## Div Structure

This layout creates the following HTML structure:

```html
<div class="layout--wordpress-twentyseventeen">
  <div id="page" class="site">
    <a class="skip-link">Skip to content</a>

    <!-- Header region: WordPress Header block -->
    <header id="masthead" class="site-header">...</header>

    <!-- THE PARALLAX WRAPPER -->
    <div class="site-content-contain">
      <div id="content" class="site-content">
        <div class="wrap">
          <!-- Content region: WordPress Content block -->
          <div id="primary" class="content-area">...</div>

          <!-- Sidebar region: WordPress Sidebar block -->
          <aside id="secondary" class="widget-area">...</aside>
        </div>
      </div>

      <!-- Footer region: WordPress Footer block -->
      <footer id="colophon" class="site-footer">...</footer>
    </div><!-- .site-content-contain -->
  </div><!-- #page -->
</div>
```

## Customization

If you need to modify the layout structure, edit:
- `layout--wordpress-twentyseventeen.tpl.php` - The layout template file
- `wordpress_twentyseventeen.info` - The layout configuration

## Compatibility

This layout is specifically designed for WordPress Twenty Seventeen theme and works in conjunction with the `wp_content` module's header/footer stripping logic. If you're using a different WordPress theme, you may need to create a custom layout based on that theme's HTML structure.

## Troubleshooting

**Problem**: The parallax effect isn't working
- Check that the WordPress Header, Content, Sidebar, and Footer blocks are placed in the correct regions
- Verify that the WordPress Twenty Seventeen theme files are loaded and its CSS is being included
- Inspect the page HTML to confirm the `site-content-contain` div is present

**Problem**: The layout looks broken or unstyled
- Make sure the `wp_content` module is enabled
- Check that WordPress CSS files are being loaded (view page source and look for Twenty Seventeen stylesheets)
- Verify that the layout is set as active for the current page path
