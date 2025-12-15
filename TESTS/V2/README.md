# WP4BD V2 Tests

This directory contains test scripts for the V2 (WordPress-as-Engine) architecture.

## Test Naming Convention

Tests are named by their Jira ticket number:
- `NNN-description.php` - Test for ticket WP4BD-V2-NNN

For example:
- `011-wp-bootstrap.php` - Tests WP4BD-V2-011 (WordPress Bootstrap Entry Point)

## Running Tests

### From Command Line (Local)
```bash
php TESTS/V2/011-wp-bootstrap.php
```

### From DDEV
```bash
ddev exec 'php /var/www/html/TESTS/V2/011-wp-bootstrap.php'
```

## Test Files

| File | Ticket | Description |
|------|--------|-------------|
| `011-wp-bootstrap.php` | WP4BD-V2-011 | Tests WordPress bootstrap entry point |
| `012-wp-config.php` | WP4BD-V2-012 | Tests wp-config bridge configuration |
| `020-wpdb-dropin.php` | WP4BD-V2-020 | Tests db.php drop-in database interception |
| `021-query-mapping.php` | WP4BD-V2-021 | Tests SQL query parsing and Backdrop mapping |
| `022-result-transformation.php` | WP4BD-V2-022 | Tests Backdrop-to-WordPress object transformation |
| `030-wordpress-globals.php` | WP4BD-V2-030 | Verifies WordPress globals documentation |
| `031-init-globals.php` | WP4BD-V2-031 | Verifies WordPress globals initialization |
| `030-wordpress-globals.php` | WP4BD-V2-030 | Verifies WordPress globals documentation |

## Writing New Tests

Each test should:
1. Be executable via PHP CLI (`#!/usr/bin/env php` shebang)
2. Work in both local and DDEV environments
3. Provide clear output with ✅/❌ indicators
4. Exit with appropriate status codes (0 = success, non-zero = failure)
5. Document its acceptance criteria in comments

Example structure:
```php
#!/usr/bin/env php
<?php
/**
 * Test script for WP4BD-V2-XXX: Description
 * 
 * Run from command line:
 *   php TESTS/V2/XXX-description.php
 */

// Setup BACKDROP_ROOT for both environments
if (file_exists('/var/www/html/backdrop-1.30')) {
  define('BACKDROP_ROOT', '/var/www/html/backdrop-1.30');
} else {
  define('BACKDROP_ROOT', dirname(dirname(__DIR__)) . '/backdrop-1.30');
}

// Load necessary files
require_once BACKDROP_ROOT . '/path/to/file.php';

// Run tests
echo "Testing feature...\n";
$result = test_something();

if ($result) {
  echo "✅ SUCCESS\n";
  exit(0);
} else {
  echo "❌ FAILED\n";
  exit(1);
}
```

