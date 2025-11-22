# Drupal Forge Setup Guide for WP2BD

This guide explains how to deploy the WP2BD (WordPress to Backdrop) project on Drupal Forge.

## Project Overview

**WP2BD** is a WordPress Theme Compatibility Layer for Backdrop CMS that allows classic WordPress themes (pre-Block Editor era) to run on Backdrop without modification.

- **Target WordPress Version:** 4.9 (last major pre-Gutenberg release)
- **Target Backdrop Version:** 1.30
- **Initial Theme Target:** Twenty Seventeen

## Prerequisites

- Access to Drupal Forge hosting account
- Git installed locally
- Basic familiarity with Backdrop CMS
- SSH access to your Drupal Forge server

## Deployment Steps

### 1. Prepare Local Repository

```bash
# Clone the repository
git clone https://github.com/YOUR-USERNAME/wp2bd.git
cd wp2bd
```

### 2. Directory Structure

The project contains:

```
wp2bd/
├── backdrop-1.30/              # Backdrop CMS installation
│   ├── modules/
│   │   └── wp2bd/              # WP2BD compatibility module
│   │       ├── wp2bd.info
│   │       ├── wp2bd.module
│   │       ├── classes/
│   │       │   ├── WP_Post.php
│   │       │   └── WP_Query.php
│   │       └── functions/
│   │           ├── loop.php
│   │           ├── template-loading.php
│   │           ├── content-display.php
│   │           ├── conditionals.php
│   │           ├── escaping.php
│   │           ├── hooks.php
│   │           ├── utilities.php
│   │           └── post-metadata.php
│   └── themes/
│       └── (WordPress themes go here)
├── implementation/             # Development files (not deployed)
├── specs/                      # Function specifications
└── wordpress-4.9/             # WordPress source for reference
```

### 3. Deploy to Drupal Forge

#### Option A: Quick Mode (~30 seconds)

Upload the `backdrop-1.30` directory to your Drupal Forge server:

```bash
# Using rsync
rsync -avz backdrop-1.30/ forge@your-server.drupalforge.com:~/public_html/

# Or using SCP
scp -r backdrop-1.30/* forge@your-server.drupalforge.com:~/public_html/
```

#### Option B: Git-Based Deployment

```bash
# On Drupal Forge server
cd ~/public_html
git clone https://github.com/YOUR-USERNAME/wp2bd.git .
# Move Backdrop files to web root
mv backdrop-1.30/* .
rm -rf backdrop-1.30
```

### 4. Configure Database

On Drupal Forge, edit `settings.php`:

```php
$database = array(
  'database' => 'your_db_name',
  'username' => 'your_db_user',
  'password' => 'your_db_pass',
  'host' => 'localhost',
  'driver' => 'mysql',
  'prefix' => '',
  'charset' => 'utf8mb4',
  'collation' => 'utf8mb4_general_ci',
);
```

### 5. Run Backdrop Installation

Access your site via browser: `https://your-site.drupalforge.com/install.php`

Or use command line (SSH into Drupal Forge):

```bash
cd ~/public_html
./core/scripts/install.sh \
  --db-url=mysql://user:pass@localhost/dbname \
  --account-name=admin \
  --account-pass=SECURE_PASSWORD \
  --site-name="WP2BD Development"
```

### 6. Enable WP2BD Module

After installation, enable the WP2BD module:

```bash
# Using Backdrop's built-in commands
php core/scripts/backdrop.sh pm-enable wp2bd
```

Or via the Backdrop admin interface:
1. Log in as admin
2. Navigate to **Admin** > **Modules**
3. Find "WP2BD" in the module list
4. Check the checkbox and click "Enable"

### 7. Install a WordPress Theme

For testing with Twenty Seventeen:

```bash
# Create themes directory if needed
mkdir -p themes

# Download Twenty Seventeen
cd themes
wget https://downloads.wordpress.org/theme/twentyseventeen.2.4.zip
unzip twentyseventeen.2.4.zip
cd twentyseventeen

# Create Backdrop theme info file
cat > twentyseventeen.info <<EOF
name = Twenty Seventeen
description = WordPress Twenty Seventeen theme (via WP2BD compatibility layer)
type = theme
backdrop = 1.x
base theme = false

; This is a WordPress theme wrapped by WP2BD
; Original WordPress theme version: 2.4
EOF
```

### 8. Enable the WordPress Theme

```bash
# Enable via command line
php core/scripts/backdrop.sh theme-enable twentyseventeen
php core/scripts/backdrop.sh theme-default twentyseventeen
```

Or via admin interface:
1. Navigate to **Admin** > **Appearance**
2. Find "Twenty Seventeen"
3. Click "Enable and set default"

### 9. Test the Installation

1. Visit your homepage: `https://your-site.drupalforge.com`
2. Create some test content: **Admin** > **Content** > **Add content**
3. View the content with the WordPress theme

## Module Configuration

The WP2BD module has a configuration page:

**Admin** > **Configuration** > **System** > **WP2BD Settings**

Options:
- **Enable WordPress theme compatibility** - Toggle the compatibility layer
- **Enable debug mode** - Log WordPress function calls for debugging

## Troubleshooting

### White Screen of Death (WSOD)

Enable error display in `settings.php`:

```php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
```

### Module Not Found

Verify the module is in the correct location:

```bash
ls -la modules/wp2bd/
# Should show: wp2bd.info, wp2bd.module, classes/, functions/
```

### WordPress Functions Not Defined

Check that wp2bd module is enabled:

```bash
php core/scripts/backdrop.sh pm-list | grep wp2bd
```

Should show:
```
wp2bd (Enabled)
```

### Theme Not Loading

1. Verify theme has `.info` file
2. Check theme is enabled and set as default
3. Clear cache: **Admin** > **Configuration** > **Performance** > **Clear all caches**

## Performance Optimization

### For Production on Drupal Forge

1. **Enable caching:**
   - Admin > Configuration > Performance
   - Enable "Cache pages for anonymous users"
   - Enable "Aggregate and compress CSS files"
   - Enable "Aggregate and compress JavaScript files"

2. **Configure file system:**
   ```php
   // In settings.php
   $settings['file_public_path'] = 'files';
   $settings['file_private_path'] = '../private';
   ```

3. **Set up cron:**
   ```bash
   # Add to crontab
   0 * * * * /usr/bin/php ~/public_html/core/cron.php
   ```

## Development Workflow on Drupal Forge

### Making Changes to WP2BD Module

1. Edit files locally in `backdrop-1.30/modules/wp2bd/`
2. Test locally (see Local Development below)
3. Commit changes:
   ```bash
   git add backdrop-1.30/modules/wp2bd/
   git commit -m "Update WP2BD module"
   git push
   ```
4. Pull changes on Drupal Forge:
   ```bash
   ssh forge@your-server.drupalforge.com
   cd ~/public_html
   git pull
   # Clear cache
   php core/scripts/backdrop.sh cache-clear
   ```

## Local Development Setup

For local development before deploying to Drupal Forge:

```bash
# Use PHP built-in server
cd backdrop-1.30
php -S localhost:8080

# Access at: http://localhost:8080
```

Or use Docker:

```bash
# Create docker-compose.yml
version: '3'
services:
  backdrop:
    image: backdrop:1.30
    ports:
      - "8080:80"
    volumes:
      - ./backdrop-1.30:/var/www/html
    environment:
      - BACKDROP_DB_HOST=db
      - BACKDROP_DB_NAME=backdrop
      - BACKDROP_DB_USER=backdrop
      - BACKDROP_DB_PASSWORD=backdrop
  db:
    image: mariadb:10.6
    environment:
      - MYSQL_DATABASE=backdrop
      - MYSQL_USER=backdrop
      - MYSQL_PASSWORD=backdrop
      - MYSQL_ROOT_PASSWORD=root
```

```bash
docker-compose up -d
```

## Safety and Best Practices

### Before Major Changes

1. **Backup database:**
   ```bash
   php core/scripts/dump-database.sh > backup-$(date +%Y%m%d).sql
   ```

2. **Backup files:**
   ```bash
   tar -czf files-backup-$(date +%Y%m%d).tar.gz files/
   ```

### Monitoring

1. Check error logs regularly:
   ```bash
   tail -f ~/logs/error.log
   ```

2. Monitor performance:
   - Admin > Reports > Status report
   - Admin > Reports > Recent log messages

## Next Steps

After successful deployment:

1. **Add more WordPress functions** - Implement additional compatibility functions as needed
2. **Test with more themes** - Try other classic WordPress themes
3. **Performance tuning** - Optimize for your specific use case
4. **Documentation** - Document theme-specific quirks and fixes

## Resources

- [Backdrop CMS Documentation](https://docs.backdropcms.org/)
- [Drupal Forge Support](https://www.drupalforge.com/support)
- [WP2BD Project Repository](https://github.com/YOUR-USERNAME/wp2bd)
- [WordPress Theme Development](https://developer.wordpress.org/themes/)

## Support

For issues specific to:
- **WP2BD module:** Open an issue on GitHub
- **Drupal Forge hosting:** Contact Drupal Forge support
- **Backdrop CMS:** Visit the Backdrop community forums

---

**Last Updated:** 2025-11-22
**Version:** 1.0.0
