# WP4BD Testing Environment Setup Guide

**Date:** December 14, 2025
**Environment:** Claude Code CLI Testing Environment
**Purpose:** Set up LAMP-style environment for testing WordPress Theme Compatibility Layer for Backdrop CMS

## Overview

This document describes the setup of a complete testing environment for the WP4BD (WordPress for Backdrop) compatibility layer project. The environment allows CLI-based testing of Backdrop CMS with WordPress theme compatibility without requiring Docker/ddev.

## Environment Specifications

### System
- **OS:** Linux 4.4.0 (Ubuntu 24.04)
- **Working Directory:** `/home/user/wp2bd`
- **User:** root

### Software Stack
- **PHP:** 8.4.15 (CLI) with Zend OPcache
- **Database:** MariaDB 10.11.13
- **CMS:** Backdrop CMS 1.30
- **CLI Tools:** WP-CLI 2.12.0, Backdrop CLI (backdrop.sh)

## Why This Configuration?

### PHP 8.4 Instead of 7.3
**Original Plan:** Install PHP 7.3 to match 2015-2017 WordPress 4.9 era
**What Happened:** Network restrictions blocked external PPA access (ppa.launchpadcontent.net)
**Solution:** Use PHP 8.4 (already installed)

**Impact:**
- ✅ Backdrop CMS 1.30 fully supports PHP 8.4
- ✅ WordPress 4.9 *mostly* compatible with PHP 8.4
- ⚠️ Expected PHP 8.4 deprecation warnings (non-breaking)
- ⚠️ Warnings logged to Backdrop's Watchdog system

**Trade-offs:**
- **Pro:** Immediate availability, no installation needed
- **Pro:** Tests compatibility with modern PHP (future-proofing)
- **Con:** More deprecation noise in logs
- **Con:** Less authentic to 2015 WordPress hosting environment

### MariaDB 10.11.13 Instead of MySQL 5.6
**Why MariaDB:** Backward compatible with MySQL 5.x, available in Ubuntu repos
**Why not MySQL 5.6:** Only MySQL 8.0+ and MariaDB 10.5+ available in standard repos
**Alternative:** Could use Docker containers (`mysql:5.6` or `mariadb:10.1`) for exact 2015 environment

## Installation Steps

### 1. Install MariaDB

```bash
# Install MariaDB server and client
apt-get update
apt-get install -y mariadb-server mariadb-client

# Start MariaDB (as root, skip grant tables for simplicity)
mariadbd --user=root --skip-grant-tables --skip-networking --datadir=/var/lib/mysql &

# Wait for startup
sleep 3

# Verify connection
mysql -e "SELECT VERSION();"
# Output: 10.11.13-MariaDB-0ubuntu0.24.04.1
```

### 2. Create Databases

```bash
# Create databases for Backdrop and WordPress
mysql -e "CREATE DATABASE IF NOT EXISTS backdrop; CREATE DATABASE IF NOT EXISTS wordpress;"

# Verify
mysql -e "SHOW DATABASES;"
```

### 3. Import Backdrop Database

```bash
# Import compressed SQL dump
zcat DB/wp4bd-bd-codex-max-dec8-fina.sql.gz | mysql backdrop

# Verify tables imported
mysql -e "USE backdrop; SHOW TABLES;" | head -20
```

**Backdrop Database Contents:**
- Size: 169KB (compressed)
- Tables: 19+ core Backdrop tables (cache, batch, config, etc.)
- Source: `DB/wp4bd-bd-codex-max-dec8-fina.sql.gz`

### 4. Configure Backdrop Settings

**File:** `backdrop-1.30/settings.php`

**Change database credentials from ddev to localhost:**

```php
// Before (ddev container settings)
$database = array(
  'database' => 'db',
  'username' => 'db',
  'password' => 'db',
  'host' => 'db',
);

// After (local MariaDB)
$database = array(
  'database' => 'backdrop',
  'username' => 'root',
  'password' => '',
  'host' => 'localhost',
);
```

### 5. Install CLI Tools

#### WP-CLI (for WordPress management)

```bash
# Download and install WP-CLI
curl -fsSL https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /usr/local/bin/wp
chmod +x /usr/local/bin/wp

# Verify installation
wp --allow-root --version
# Output: WP-CLI 2.12.0
```

**Note:** WP-CLI requires `--allow-root` flag when running as root user.

#### Backdrop CLI (backdrop.sh)

**Bee installation blocked by network restrictions**
**Alternative:** Use built-in `backdrop.sh` script

```bash
# Test Backdrop CLI (must run from Backdrop root)
cd backdrop-1.30
php core/scripts/backdrop.sh status
```

## Verification & Testing

### Test Backdrop Database Connection

```bash
cd /home/user/wp2bd/backdrop-1.30
php core/scripts/backdrop.sh status
```

**Expected Output:**
- HTML page rendered (Backdrop is working!)
- PHP deprecation warnings (expected with PHP 8.4)
- Theme: "wp" (bridge theme)
- Layout: "wordpress"
- Site title: "wp4bd-test"

### Key Indicators of Success

1. ✅ **Database Connection:** No MySQL connection errors
2. ✅ **Page Rendering:** HTML output with DOCTYPE and page structure
3. ✅ **Theme System:** Shows `layout--wordpress.tpl.php` and `wp` theme loading
4. ✅ **WordPress Themes Detected:** Multiple WP themes in `/themes/wp/wp-content/themes/`
5. ⚠️ **Deprecation Warnings:** Expected PHP 8.4 compatibility warnings (non-breaking)

### Sample Deprecation Warnings (Expected)

```
Deprecated function: session_set_save_handler(): Providing individual callbacks...
Deprecated function: user_has_role(): Implicitly marking parameter $account as nullable...
Deprecated: Constant E_STRICT is deprecated...
```

**These warnings are expected and do not break functionality.** They appear because:
- Backdrop CMS 1.30 was written for PHP 7.x/8.0
- PHP 8.4 has stricter type checking
- These would be logged to Watchdog in production

## Project Structure

```
/home/user/wp2bd/
├── backdrop-1.30/              # Backdrop CMS installation
│   ├── core/                   # Backdrop core files
│   ├── modules/                # Custom modules
│   │   └── wp_tcl/            # WordPress Template Compatibility Layer module
│   ├── themes/                 # Themes
│   │   └── wp/                # Bridge theme
│   │       └── wp-content/
│   │           └── themes/    # WordPress themes (Twenty Eleven-Seventeen)
│   ├── layouts/                # Layout templates
│   │   └── wordpress/         # WordPress-style layout
│   ├── files/                  # Backdrop files directory
│   ├── settings.php           # Database configuration (UPDATED)
│   └── index.php              # Backdrop entry point
├── DB/                         # Database dumps
│   ├── wp4bd-bd-codex-max-dec8-fina.sql.gz  # Backdrop database (imported)
│   └── wp4bd-bd-codex-max-dec8-final-config.tar.gz  # Config backup
└── DOCS/                       # Documentation
    └── dec14-claude-test-environment-setup.md  # This file
```

## Quick Reference Commands

### Database Operations

```bash
# Access Backdrop database
mysql backdrop

# Show Backdrop tables
mysql -e "USE backdrop; SHOW TABLES;"

# Export database
mysqldump backdrop | gzip > backup-$(date +%Y%m%d).sql.gz

# Import database
zcat dump.sql.gz | mysql backdrop
```

### Backdrop Operations

```bash
# Run Backdrop CLI (from backdrop-1.30 directory)
cd backdrop-1.30
php core/scripts/backdrop.sh status
php core/scripts/backdrop.sh cache-clear

# Check database connection
mysql -e "USE backdrop; SELECT name, status FROM system WHERE type='module' LIMIT 10;"
```

### WordPress Operations (when WordPress is set up)

```bash
# WP-CLI commands (use --allow-root when running as root)
wp --allow-root --path=/path/to/wordpress db check
wp --allow-root --path=/path/to/wordpress theme list
wp --allow-root --path=/path/to/wordpress plugin list
```

## Known Limitations

### Missing Components

1. **WordPress Database Dump**
   - Expected file: `DB/wp4bd-wp-dec8.sql.gz`
   - Status: Not found in current branch
   - May be in `upbeat-khorana` branch (not in repository)
   - Impact: Cannot test WordPress 4.9 reference environment yet

2. **WordPress wp-config.php**
   - Status: Not yet configured
   - Will need setup when WordPress database is available

3. **Bee CLI Tool**
   - Installation blocked by network restrictions
   - Alternative: Use `backdrop.sh` script (works fine)

### Network Restrictions

The environment has proxy restrictions blocking:
- External PPAs (ppa.launchpadcontent.net)
- GitHub Releases (for Bee download)

**Workaround:** Use packages from Ubuntu repos or pre-installed tools

## Troubleshooting

### Problem: "ERROR: index.php not found"

**Cause:** Running `backdrop.sh` with `--root` flag from outside directory
**Solution:** Run from within Backdrop directory without `--root` flag

```bash
# Don't do this:
php backdrop-1.30/core/scripts/backdrop.sh --root="/path/to/backdrop" status

# Do this instead:
cd backdrop-1.30
php core/scripts/backdrop.sh status
```

### Problem: MariaDB Permission Errors

**Cause:** Attempting to use sudo with misconfigured permissions
**Solution:** Run as root directly (already root in this environment)

```bash
# Check user
whoami  # Should output: root

# Run commands without sudo
mysql -e "SHOW DATABASES;"
```

### Problem: Too Many PHP Deprecation Warnings

**Temporary Suppression (not recommended for debugging):**
```bash
# Suppress deprecation warnings in output
php -d error_reporting=E_ALL^E_DEPRECATED core/scripts/backdrop.sh status
```

**Better Approach:**
- These warnings help identify PHP 8.4 compatibility issues
- Leave them enabled during development
- They'll be logged to Watchdog, not shown to users in production

## PHP Extensions Installed

The following PHP 8.4 extensions are available:

```bash
php -m | grep -E 'curl|gd|mbstring|mysql|pdo|xml|zip|json|opcache'
```

**Core Extensions for Backdrop/WordPress:**
- ✅ curl - HTTP requests
- ✅ gd - Image manipulation
- ✅ mbstring - Multi-byte string handling
- ✅ mysql/mysqli - Database connectivity
- ✅ pdo_mysql - PDO database driver (required by Backdrop)
- ✅ xml - XML processing
- ✅ zip - Archive handling
- ✅ json - JSON encoding/decoding
- ✅ opcache - PHP bytecode caching

## Next Steps

### To Complete WordPress Setup:

1. **Obtain WordPress Database Dump**
   - Check `upbeat-khorana` branch or other development branches
   - Or create fresh WordPress 4.9 installation

2. **Configure WordPress**
   - Create/update `wp-config.php` with database credentials
   - Set up WordPress to use `wordpress` database

3. **Side-by-Side Testing**
   - Run WordPress 4.9 with Twenty Seventeen theme
   - Compare output with Backdrop + WP4BD compatibility layer
   - Verify template function outputs match

### Development Workflow:

1. **Modify WP-TCL Module** (`backdrop-1.30/modules/wp_tcl/`)
2. **Clear Backdrop Cache:**
   ```bash
   cd backdrop-1.30
   php core/scripts/backdrop.sh cache-clear
   ```
3. **Test via CLI:**
   ```bash
   php core/scripts/backdrop.sh status
   ```
4. **Check Watchdog Logs** for errors/warnings
5. **Compare with WordPress Reference** (when available)

## Commit Information

Configuration changes committed to branch:
- **Branch:** `claude/setup-testing-environment-01XU73r6DXHviqs3JEQdEMxr`
- **Commit:** "Configure Backdrop database settings for local MariaDB environment"
- **Files Changed:** `backdrop-1.30/settings.php`
- **Change:** Updated database credentials from ddev to localhost

## Additional Resources

- [Backdrop CMS System Requirements](https://docs.backdropcms.org/documentation/system-requirements)
- [Backdrop CMS Database Configuration](https://docs.backdropcms.org/database-configuration)
- [WP-CLI Documentation](https://wp-cli.org/)
- [Project README](../README.md)
- [WP4BD Architecture](ARCHITECTURE-WORDPRESS-AS-ENGINE.md)
- [WordPress Theme Analysis](WORDPRESS-THEME-ANALYSIS.md)

## Summary

This testing environment provides:
- ✅ **Database:** MariaDB 10.11.13 with Backdrop database populated
- ✅ **CMS:** Backdrop CMS 1.30 configured and operational
- ✅ **PHP:** PHP 8.4.15 with all required extensions
- ✅ **CLI Tools:** WP-CLI and Backdrop CLI (backdrop.sh)
- ✅ **Theme System:** WordPress bridge theme and compatibility layer loaded
- ⚠️ **Deprecation Warnings:** Expected PHP 8.4 compatibility notices (non-breaking)
- ⏳ **WordPress Reference:** Pending WordPress database dump

The environment is ready for developing and testing the WordPress → Backdrop compatibility layer!
