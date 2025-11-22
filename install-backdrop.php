#!/usr/bin/env php
<?php
/**
 * Simple Backdrop installation script for WP2BD project.
 */

define('BACKDROP_ROOT', __DIR__ . '/backdrop-1.30');

// Set environment variables
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_SOFTWARE'] = 'CLI';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['QUERY_STRING'] = '';

// Change to Backdrop root directory
chdir(BACKDROP_ROOT);

// Include Backdrop bootstrap
require_once BACKDROP_ROOT . '/core/includes/bootstrap.inc';

echo "Installing Backdrop CMS for WP2BD...\n";

// Installation parameters
$settings = array(
  'parameters' => array(
    'profile' => 'standard',
    'locale' => 'en',
  ),
  'forms' => array(
    'install_settings_form' => array(
      'driver' => 'mysql',
      'database' => 'backdrop_wp2bd',
      'username' => 'root',
      'password' => '',
      'host' => 'localhost',
      'port' => '3306',
      'prefix' => '',
    ),
    'install_configure_form' => array(
      'site_name' => 'WP2BD Development',
      'site_mail' => 'admin@wp2bd.local',
      'account' => array(
        'name' => 'admin',
        'mail' => 'admin@wp2bd.local',
        'pass' => array(
          'pass1' => 'admin',
          'pass2' => 'admin',
        ),
      ),
      'update_status_module' => array(
        1 => TRUE,
        2 => TRUE,
      ),
      'clean_url' => TRUE,
    ),
  ),
);

try {
  // Run installation
  require_once BACKDROP_ROOT . '/core/includes/install.core.inc';
  install_backdrop($settings);

  echo "Backdrop installation completed successfully!\n";
  echo "Site name: WP2BD Development\n";
  echo "Admin username: admin\n";
  echo "Admin password: admin\n";

} catch (Exception $e) {
  echo "Installation failed: " . $e->getMessage() . "\n";
  exit(1);
}
