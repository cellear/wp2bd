<?php
/**
 * WordPress Configuration Bridge for Backdrop (WP4BD V2)
 *
 * This file provides WordPress with the configuration it expects,
 * but the actual database connection will be intercepted by db.php
 * drop-in (created in Epic 3: WP4BD-V2-020).
 *
 * IMPORTANT: The DB credentials below are NEVER used. They are here
 * only to satisfy WordPress's configuration requirements. All database
 * queries are intercepted and handled by Backdrop.
 *
 * @package WP4BD
 * @subpackage V2-Architecture
 * @since WP4BD-V2-012
 */

// =============================================================================
// DATABASE CONFIGURATION (INTERCEPTED - NOT USED)
// =============================================================================

/**
 * These credentials are dummy values. WordPress expects them to be defined,
 * but the db.php drop-in (Epic 3) will prevent WordPress from ever attempting
 * to connect to a database using these credentials.
 */
if (!defined('DB_NAME')) {
  define('DB_NAME', 'wp4bd_intercepted');
}

if (!defined('DB_USER')) {
  define('DB_USER', 'wp4bd_intercepted');
}

if (!defined('DB_PASSWORD')) {
  define('DB_PASSWORD', 'wp4bd_intercepted');
}

if (!defined('DB_HOST')) {
  define('DB_HOST', 'localhost');
}

if (!defined('DB_CHARSET')) {
  define('DB_CHARSET', 'utf8mb4');
}

if (!defined('DB_COLLATE')) {
  define('DB_COLLATE', '');
}

// =============================================================================
// AUTHENTICATION UNIQUE KEYS AND SALTS
// =============================================================================

/**
 * Authentication keys and salts for WordPress security.
 * These are used by WordPress for cookie encryption and session management.
 *
 * In production, generate unique keys using:
 * https://api.wordpress.org/secret-key/1.1/salt/
 */
if (!defined('AUTH_KEY')) {
  define('AUTH_KEY', 'wp4bd-v2-auth-key-change-in-production');
}

if (!defined('SECURE_AUTH_KEY')) {
  define('SECURE_AUTH_KEY', 'wp4bd-v2-secure-auth-key-change-in-production');
}

if (!defined('LOGGED_IN_KEY')) {
  define('LOGGED_IN_KEY', 'wp4bd-v2-logged-in-key-change-in-production');
}

if (!defined('NONCE_KEY')) {
  define('NONCE_KEY', 'wp4bd-v2-nonce-key-change-in-production');
}

if (!defined('AUTH_SALT')) {
  define('AUTH_SALT', 'wp4bd-v2-auth-salt-change-in-production');
}

if (!defined('SECURE_AUTH_SALT')) {
  define('SECURE_AUTH_SALT', 'wp4bd-v2-secure-auth-salt-change-in-production');
}

if (!defined('LOGGED_IN_SALT')) {
  define('LOGGED_IN_SALT', 'wp4bd-v2-logged-in-salt-change-in-production');
}

if (!defined('NONCE_SALT')) {
  define('NONCE_SALT', 'wp4bd-v2-nonce-salt-change-in-production');
}

// =============================================================================
// DATABASE TABLE PREFIX
// =============================================================================

/**
 * WordPress Database Table prefix.
 *
 * Note: This prefix is not actually used since we intercept all database
 * queries. Backdrop's database uses its own table structure. However,
 * WordPress expects this variable to be set.
 */
if (!isset($table_prefix)) {
  $table_prefix = 'wp_';
}

// =============================================================================
// WORDPRESS DEBUG MODE
// =============================================================================

/**
 * Enable WordPress debug mode during development.
 * Set to false in production.
 */
if (!defined('WP_DEBUG')) {
  define('WP_DEBUG', true);
}

if (!defined('WP_DEBUG_LOG')) {
  define('WP_DEBUG_LOG', true);
}

if (!defined('WP_DEBUG_DISPLAY')) {
  define('WP_DEBUG_DISPLAY', true);
}

// =============================================================================
// WORDPRESS MEMORY LIMITS
// =============================================================================

/**
 * Increase memory limit for WordPress operations.
 */
if (!defined('WP_MEMORY_LIMIT')) {
  define('WP_MEMORY_LIMIT', '256M');
}

if (!defined('WP_MAX_MEMORY_LIMIT')) {
  define('WP_MAX_MEMORY_LIMIT', '512M');
}

// =============================================================================
// DISABLE WORDPRESS AUTO-UPDATES
// =============================================================================

/**
 * Disable WordPress core auto-updates since we're running a specific
 * version (4.9) as a rendering engine.
 */
if (!defined('AUTOMATIC_UPDATER_DISABLED')) {
  define('AUTOMATIC_UPDATER_DISABLED', true);
}

if (!defined('WP_AUTO_UPDATE_CORE')) {
  define('WP_AUTO_UPDATE_CORE', false);
}

// =============================================================================
// DISABLE WORDPRESS CRON
// =============================================================================

/**
 * Disable WordPress cron since Backdrop handles scheduled tasks.
 */
if (!defined('DISABLE_WP_CRON')) {
  define('DISABLE_WP_CRON', true);
}

// =============================================================================
// FILESYSTEM METHOD
// =============================================================================

/**
 * Force direct filesystem method to avoid WordPress trying to FTP/SSH.
 * Backdrop will handle file operations.
 */
if (!defined('FS_METHOD')) {
  define('FS_METHOD', 'direct');
}

// =============================================================================
// WORDPRESS CONTENT DIRECTORY
// =============================================================================

/**
 * WordPress content directory path and URL.
 * These should already be set by wp-bootstrap.php, but we define them
 * here as fallbacks.
 */
if (!defined('WP_CONTENT_DIR')) {
  define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
}

if (!defined('WP_CONTENT_URL')) {
  // This will be properly set when we integrate with Backdrop's URL system
  define('WP_CONTENT_URL', '/themes/wp/wpbrain/wp-content');
}

// =============================================================================
// WORDPRESS PLUGIN DIRECTORY
// =============================================================================

/**
 * WordPress plugins are not supported in WP4BD V2.
 * This constant prevents WordPress from trying to load plugins.
 */
if (!defined('WP_PLUGIN_DIR')) {
  define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
}

if (!defined('WP_PLUGIN_URL')) {
  define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');
}

// Explicitly disable plugins
if (!defined('DISALLOW_FILE_MODS')) {
  define('DISALLOW_FILE_MODS', true);
}

// =============================================================================
// WORDPRESS UPLOADS DIRECTORY
// =============================================================================

/**
 * WordPress uploads will be handled by Backdrop's file system.
 * This constant points to where Backdrop stores files.
 */
if (!defined('UPLOADS')) {
  // This will be properly mapped to Backdrop's files directory
  define('UPLOADS', '../../../files');
}

// =============================================================================
// ABSPATH - WORDPRESS ABSOLUTE PATH
// =============================================================================

/**
 * Absolute path to the WordPress directory.
 * This should already be defined by wp-bootstrap.php, but we include
 * it here as a safety check.
 */
if (!defined('ABSPATH')) {
  define('ABSPATH', dirname(__FILE__) . '/');
}

// =============================================================================
// INTEGRATION NOTES
// =============================================================================

/**
 * INTEGRATION POINTS FOR EPIC 3:
 *
 * 1. Database Interception (WP4BD-V2-020):
 *    - Create wpbrain/wp-content/db.php drop-in
 *    - Override wpdb class to prevent database connections
 *    - Map WordPress queries to Backdrop queries
 *
 * 2. WordPress Globals (Epic 4):
 *    - Initialize $wpdb with our bridge class
 *    - Set up $wp_query with Backdrop data
 *    - Populate $post from Backdrop node
 *
 * 3. Bootstrap Integration (Epic 6):
 *    - Load this file after Backdrop bootstrap
 *    - Initialize WordPress globals before theme loads
 *    - Load WordPress theme via Backdrop theme system
 *
 * DO NOT include wp-settings.php here. WordPress initialization is
 * handled by wp-bootstrap.php in a controlled manner.
 */

// Configuration complete - WordPress can now be initialized
// (but NOT via wp-settings.php - we control the initialization)

