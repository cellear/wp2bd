# Composer Setup for WP4BD

This project now uses Composer to manage WordPress and Backdrop CMS dependencies, significantly reducing repository size.

## Prerequisites

- PHP 7.4 or higher
- [Composer](https://getcomposer.org/) installed globally

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/cellear/wp2bd.git
   cd wp2bd
   ```

2. Install dependencies via Composer:
   ```bash
   composer install
   ```

This will download:
- WordPress 4.9 → `ext-wordpress/`
- Backdrop CMS 1.30 → `ext-backdrop/`

## Directory Structure

After running `composer install`, you'll have:

```
wp2bd/
├── ext-backdrop/           # Backdrop CMS 1.30 (external dependency)
├── ext-wordpress/          # WordPress 4.9 (external dependency)
├── implementation/         # Your custom implementation files
├── composer.json           # Dependency definitions
└── vendor/                 # Composer packages
```

## What Changed?

**Before:** The repository included full copies of WordPress and Backdrop (~50MB+)

**After:** The repository only contains configuration files. Composer downloads the required versions on demand.

## Benefits

- Smaller repository size (MB vs GB)
- Easier version updates (change `composer.json`, run `composer update`)
- Standard PHP dependency management
- Consistent installation across environments

## Notes

- The `ext-backdrop/` and `ext-wordpress/` directories are now in `.gitignore`
- The `ext-` prefix indicates these are external dependencies managed by Composer
- Always run `composer install` after cloning or pulling changes
- To update versions, modify `composer.json` and run `composer update`
