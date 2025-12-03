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
- WordPress 4.9 → `wordpress-4.9/`
- Backdrop CMS 1.30 → `backdrop-1.30/`

## Directory Structure

After running `composer install`, you'll have:

```
wp2bd/
├── backdrop-1.30/          # Backdrop CMS (managed by Composer)
├── wordpress-4.9/          # WordPress (managed by Composer)
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

- The `backdrop-1.30/` and `wordpress-4.9/` directories are now in `.gitignore`
- Always run `composer install` after cloning or pulling changes
- To update versions, modify `composer.json` and run `composer update`
