<?php
/**
 * Outlined Theme Functions
 */

// Enqueue styles
function outlined_enqueue_styles() {
    wp_enqueue_style( 'outlined-style', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'outlined_enqueue_styles' );

// Add theme support
function outlined_theme_support() {
    // Add title tag support
    add_theme_support( 'title-tag' );
    
    // Add post thumbnail support
    add_theme_support( 'post-thumbnails' );
    
    // Add automatic feed links
    add_theme_support( 'automatic-feed-links' );
    
    // Add HTML5 support
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ) );
}
add_action( 'after_setup_theme', 'outlined_theme_support' );
