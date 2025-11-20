<?php
/**
 * WP2BD Utilities Functions Demo
 *
 * Demonstrates the utility functions in a Twenty Seventeen context
 */

require_once dirname(__FILE__) . '/../functions/utilities.php';

// Setup environment
global $base_url;
$base_url = 'http://example.com';
$_SERVER['HTTP_HOST'] = 'example.com';
$_SERVER['HTTPS'] = 'off';

echo "========================================\n";
echo "WP2BD Utilities Functions Demo\n";
echo "Twenty Seventeen Theme Context\n";
echo "========================================\n\n";

// Demo 1: home_url()
echo "1. HOME URL FUNCTIONS\n";
echo "---------------------\n";
echo "Site home URL:           " . home_url() . "\n";
echo "About page URL:          " . home_url('/about') . "\n";
echo "Blog page URL:           " . home_url('/blog') . "\n";
echo "Secure contact URL:      " . home_url('/contact', 'https') . "\n";
echo "Relative blog URL:       " . home_url('/blog', 'relative') . "\n\n";

// Demo 2: get_bloginfo()
echo "2. BLOG INFORMATION\n";
echo "-------------------\n";
echo "Site name:               " . get_bloginfo('name') . "\n";
echo "Site description:        " . get_bloginfo('description') . "\n";
echo "Home URL:                " . get_bloginfo('url') . "\n";
echo "WordPress URL:           " . get_bloginfo('wpurl') . "\n";
echo "Character set:           " . get_bloginfo('charset') . "\n";
echo "Language:                " . get_bloginfo('language') . "\n";
echo "WordPress version:       " . get_bloginfo('version') . "\n";
echo "Text direction:          " . get_bloginfo('text_direction') . "\n\n";

// Demo 3: Template directory
echo "3. TEMPLATE DIRECTORY\n";
echo "---------------------\n";
echo "Template path:           " . get_template_directory() . "\n";
echo "Template URI:            " . get_template_directory_uri() . "\n";
echo "Stylesheet path:         " . get_stylesheet_directory() . "\n";
echo "Stylesheet URI:          " . get_stylesheet_directory_uri() . "\n";
echo "Template name:           " . get_template() . "\n";
echo "Stylesheet name:         " . get_stylesheet() . "\n\n";

// Demo 4: Building URLs (Twenty Seventeen style)
echo "4. TWENTY SEVENTEEN USAGE EXAMPLES\n";
echo "-----------------------------------\n";
echo "Main stylesheet:         " . get_bloginfo('stylesheet_url') . "\n";
echo "Custom CSS:              " . get_template_directory_uri() . '/assets/css/custom.css' . "\n";
echo "Navigation JS:           " . get_template_directory_uri() . '/assets/js/navigation.js' . "\n";
echo "Header image:            " . get_template_directory_uri() . '/assets/images/header.jpg' . "\n";
echo "SVG icons:               " . get_template_directory() . '/assets/images/svg-icons.svg' . "\n\n";

// Demo 5: Template includes (Twenty Seventeen style)
echo "5. TEMPLATE FILE INCLUDES\n";
echo "-------------------------\n";
echo "Template tags:           " . get_template_directory() . '/inc/template-tags.php' . "\n";
echo "Template functions:      " . get_template_directory() . '/inc/template-functions.php' . "\n";
echo "Customizer:              " . get_template_directory() . '/inc/customizer.php' . "\n";
echo "Custom header:           " . get_template_directory() . '/inc/custom-header.php' . "\n\n";

// Demo 6: Navigation menu (Twenty Seventeen style)
echo "6. NAVIGATION MENU HTML EXAMPLE\n";
echo "--------------------------------\n";
echo '<nav class="main-navigation">' . "\n";
echo '  <a href="' . esc_url(home_url('/')) . '">Home</a>' . "\n";
echo '  <a href="' . esc_url(home_url('/about')) . '">About</a>' . "\n";
echo '  <a href="' . esc_url(home_url('/blog')) . '">Blog</a>' . "\n";
echo '  <a href="' . esc_url(home_url('/contact')) . '">Contact</a>' . "\n";
echo '</nav>' . "\n\n";

// Demo 7: HTML head section (Twenty Seventeen style)
echo "7. HTML HEAD SECTION EXAMPLE\n";
echo "-----------------------------\n";
echo '<!DOCTYPE html>' . "\n";
echo '<html lang="' . get_bloginfo('language') . '">' . "\n";
echo '<head>' . "\n";
echo '  <meta charset="' . get_bloginfo('charset') . '">' . "\n";
echo '  <title>' . get_bloginfo('name') . ' | ' . get_bloginfo('description') . '</title>' . "\n";
echo '  <link rel="stylesheet" href="' . get_template_directory_uri() . '/style.css">' . "\n";
echo '</head>' . "\n\n";

echo "========================================\n";
echo "Demo Complete!\n";
echo "========================================\n";

// Helper function used in demo
function esc_url($url) {
    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
}
