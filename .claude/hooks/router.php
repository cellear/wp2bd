<?php
/**
 * @file
 * Router for PHP's built-in server so Backdrop clean URLs work without Apache.
 *
 * Existing files (CSS, JS, images) are served directly; every other path is
 * handed to Backdrop's index.php with $_GET['q'] set, mirroring what
 * .htaccess mod_rewrite does in production.
 *
 * Usage: php -S 127.0.0.1:8080 -t backdrop-1.30 .claude/hooks/router.php
 */

$url = parse_url($_SERVER['REQUEST_URI']);
$path = $url['path'];
$file = $_SERVER['DOCUMENT_ROOT'] . $path;

// Serve real files (assets) as-is.
if ($path !== '/' && file_exists($file) && !is_dir($file)) {
  return FALSE;
}

// Route everything else through Backdrop.
$_GET['q'] = ltrim($path, '/');
$_REQUEST['q'] = $_GET['q'];
chdir($_SERVER['DOCUMENT_ROOT']);
require $_SERVER['DOCUMENT_ROOT'] . '/index.php';
