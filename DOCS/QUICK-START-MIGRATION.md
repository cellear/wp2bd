# Quick Start: Migrate to 1-Region Architecture

**TL;DR:** Safe commands to migrate from 4-region (current) to 1-region (better) architecture.

---

## Prerequisites ✅

- [x] Current main branch tagged as `v0.1.0-4region`
- [x] Migration plan documented in `MIGRATION-PLAN-4REGION-TO-1REGION.md`
- [x] Working 4-region system preserved as rollback point

---

## Safe Migration Commands

### Step 1: Create Feature Branch from Refactor
```bash
# Fetch latest (if working on remote)
git fetch origin

# Create new feature branch from the good architecture
git checkout -b feature/1region-migration refactor/sunset-stubs-december

# Verify you're on the right starting point
git log --oneline -3
# Should show:
#   05cb588 Database and config for one-region architecture
#   e71edde feat: New architecture - WordPress themes control full HTML structure
#   9a08ff9 refactor: Sunset stubs.php in favor of organized function files
```

---

### Step 2: Port Debug Infrastructure
```bash
# Port debug helper file
git show v0.1.0-4region:backdrop-1.30/modules/wp_content/wp4bd_debug.inc \
  > backdrop-1.30/modules/wp_content/wp4bd_debug.inc

# Port debug template
git show v0.1.0-4region:backdrop-1.30/themes/wp/templates/page-debug.tpl.php \
  > backdrop-1.30/themes/wp/templates/page-debug.tpl.php

# Commit
git add backdrop-1.30/modules/wp_content/wp4bd_debug.inc \
        backdrop-1.30/themes/wp/templates/page-debug.tpl.php
git commit -m "Port debug infrastructure from v0.1.0-4region"
```

---

### Step 3: Port Documentation
```bash
# Port valuable docs created Dec 8-12
git show v0.1.0-4region:DOCS/ARCHITECTURE-WORDPRESS-AS-ENGINE.md \
  > DOCS/ARCHITECTURE-WORDPRESS-AS-ENGINE.md

git show v0.1.0-4region:DOCS/IMPLEMENTATION-DEBUG-FIRST.md \
  > DOCS/IMPLEMENTATION-DEBUG-FIRST.md

git show v0.1.0-4region:DOCS/IMPLEMENTATION-TICKETS.md \
  > DOCS/IMPLEMENTATION-TICKETS.md

git show v0.1.0-4region:DOCS/jira-import.csv \
  > DOCS/jira-import.csv

git show v0.1.0-4region:DOCS/WORDPRESS_GLOBALS_REFERENCE.md \
  > DOCS/WORDPRESS_GLOBALS_REFERENCE.md

git show v0.1.0-4region:DOCS/WP4BD-REQUEST-LIFECYCLE.md \
  > DOCS/WP4BD-REQUEST-LIFECYCLE.md

git show v0.1.0-4region:DOCS/WP4BD-SIMPLE-OVERVIEW.md \
  > DOCS/WP4BD-SIMPLE-OVERVIEW.md

# Also copy this migration plan and release notes
cp DOCS/MIGRATION-PLAN-4REGION-TO-1REGION.md DOCS/MIGRATION-PLAN-4REGION-TO-1REGION.md.bak
cp DOCS/RELEASE-v0.1.0-4region.md DOCS/RELEASE-v0.1.0-4region.md.bak

# Commit
git add DOCS/
git commit -m "Port documentation from v0.1.0-4region"
```

---

### Step 4: Compare and Port Function Implementations
```bash
# Generate diff of all function changes
git diff v0.1.0-4region refactor/sunset-stubs-december \
  -- backdrop-1.30/themes/wp/functions/ > /tmp/function-diff.patch

# Review the diff
less /tmp/function-diff.patch

# Look for valuable additions in v0.1.0-4region that aren't in refactor branch
# Manually add any missing functions

# Example: Check utilities.php for differences
git show v0.1.0-4region:backdrop-1.30/themes/wp/functions/utilities.php > /tmp/utilities-4region.php
git show refactor/sunset-stubs-december:backdrop-1.30/themes/wp/functions/utilities.php > /tmp/utilities-refactor.php
diff -u /tmp/utilities-refactor.php /tmp/utilities-4region.php | less

# If there are valuable additions, manually edit the file to include them
# Then commit
```

---

### Step 5: Set Up Database
```bash
# Check if 1-region database exists
ls -lh backdrop-1.30/DB/dec-10-one-region.sql.gz

# If it exists, import it
ddev import-db --file=backdrop-1.30/DB/dec-10-one-region.sql.gz

# Clear all caches
ddev bee cc

# Verify theme configuration
ddev bee config-get wp_content.settings active_theme
# Should output: twentyseventeen (or another theme)

# If config is missing, set it
ddev bee config-set wp_content.settings active_theme twentyseventeen
ddev bee cc
```

---

### Step 6: Test All Themes
```bash
# Test script - save as test-all-themes.sh
cat > /tmp/test-all-themes.sh << 'EOF'
#!/bin/bash
THEMES="twentythirteen twentyfourteen twentyfifteen twentysixteen twentyseventeen"
SITE_URL="https://wp4bd-test.ddev.site"

for theme in $THEMES; do
  echo "========================================="
  echo "Testing: $theme"
  echo "========================================="

  ddev bee config-set wp_content.settings active_theme $theme
  ddev bee cc

  echo "Homepage test:"
  curl -s -k "$SITE_URL/" | grep -i "sidebar" | head -3

  echo ""
  echo "Single post test:"
  curl -s -k "$SITE_URL/node/1" | grep -i "sidebar" | head -3

  echo ""
done
EOF

chmod +x /tmp/test-all-themes.sh

# Run tests
/tmp/test-all-themes.sh
```

---

### Step 7: Visual Testing
```bash
# For each theme, open in browser and verify:

THEMES="twentythirteen twentyfourteen twentyfifteen twentysixteen twentyseventeen"

for theme in $THEMES; do
  echo "Setting theme: $theme"
  ddev bee config-set wp_content.settings active_theme $theme
  ddev bee cc

  echo "Open in browser: https://wp4bd-test.ddev.site/"
  echo "Press Enter when ready to test next theme..."
  read
done
```

**Check for:**
- [ ] No duplicate `<html>` or `<body>` tags (view source)
- [ ] Sidebar renders in correct position
- [ ] WordPress theme CSS loaded
- [ ] Backdrop CSS loaded (check for `.l-page` classes)
- [ ] No JavaScript console errors
- [ ] Header renders correctly
- [ ] Footer renders correctly
- [ ] Navigation menus work

---

### Step 8: Merge to Main
```bash
# Push feature branch (if using remote)
git push -u origin feature/1region-migration

# Switch to main
git checkout main

# Merge with merge commit (preserves history)
git merge feature/1region-migration --no-ff -m "Migrate to 1-region WordPress-as-engine architecture

This merge transitions from the 4-region architecture (v0.1.0-4region)
to the superior 1-region 'WordPress-as-engine' architecture.

Key changes:
- WordPress themes control full HTML structure
- Backdrop assets injected via regex
- Eliminated 4-region layout overhead
- Universal theme compatibility
- Cleaner, more maintainable code

See DOCS/MIGRATION-PLAN-4REGION-TO-1REGION.md for details.
Based on architectural breakthrough from Dec 1-2, 2024 (commit e71edde)."

# Push to remote
git push origin main
```

---

### Step 9: Tag New Release
```bash
git tag -a v0.2.0-1region -m "Release: 1-region architecture (WordPress-as-engine)

This release migrates from the 4-region architecture to the superior
1-region 'WordPress-as-engine' architecture originally developed Dec 1-2.

Key improvements:
- WordPress themes control full HTML structure
- Backdrop assets injected via regex
- Cleaner, more maintainable code
- Universal theme compatibility
- No theme-specific workarounds

Migration details: DOCS/MIGRATION-PLAN-4REGION-TO-1REGION.md
Architectural rationale: DOCS/HANDOFF.md
Previous release: v0.1.0-4region (rollback point)"

git push origin v0.2.0-1region
```

---

## Rollback (If Needed)

If something goes wrong:

```bash
# Option 1: Create rollback branch from v0.1.0-4region
git checkout -b rollback/return-to-4region v0.1.0-4region
git push origin rollback/return-to-4region

# Option 2: Reset main to v0.1.0-4region (DESTRUCTIVE!)
# Only do this if you're absolutely sure and have communicated with team
git checkout main
git reset --hard v0.1.0-4region
git push origin main --force

# Option 3: Revert the merge commit
git checkout main
git revert -m 1 HEAD  # Revert the last merge
git push origin main
```

---

## Verification Checklist

After migration, verify:

### Code Quality
- [ ] No duplicate HTML structure in source
- [ ] No PHP errors in `ddev logs`
- [ ] No JS errors in browser console
- [ ] Clean, readable page source

### Functionality
- [ ] All 5 themes render correctly
- [ ] Sidebars appear in correct positions
- [ ] Post navigation works
- [ ] Asset loading works (CSS & JS)
- [ ] Debug mode works (`?debug`)

### Performance
- [ ] Page load time equal or better than v0.1.0-4region
- [ ] No excessive database queries
- [ ] No rendering slowdowns

### Documentation
- [ ] README.md updated
- [ ] CLAUDE.MD updated
- [ ] CHANGELOG.md has migration entry
- [ ] All migration docs committed

---

## Post-Migration Tasks

- [ ] Update Jira board status
- [ ] Notify team of architecture change
- [ ] Update any deployment scripts
- [ ] Create database backup of working 1-region system
- [ ] Archive old 4-region documentation (move to ARCHIVE/ directory)

---

## Estimated Time

- Steps 1-3: 30 minutes (automated)
- Step 4: 1 hour (manual review)
- Steps 5-7: 2 hours (testing)
- Steps 8-9: 15 minutes (git operations)
- **Total: 3-4 hours**

---

## Help / Troubleshooting

### "Template not found" errors
- Check that WordPress theme files exist in `wordpress-4.9/wp-content/themes/`
- Verify theme name in config matches directory name
- Check `_wp_content_get_template()` function logic

### Sidebar not rendering
- Check that `is_active_sidebar()` returns true
- Verify `dynamic_sidebar()` is being called
- Look for widget registration in theme's `functions.php`

### Assets not loading
- Check browser network tab for 404s
- Verify `wp_enqueue_scripts` hook fired
- Check that Backdrop's `backdrop_get_css()` / `backdrop_get_js()` injected correctly

### Debug mode not working
- Verify `wp4bd_debug.inc` file exists and has no syntax errors
- Check that `?debug` parameter triggers loading of `page-debug.tpl.php`
- Look for PHP errors in `ddev logs`

---

## Resources

- **Detailed Migration Plan**: `DOCS/MIGRATION-PLAN-4REGION-TO-1REGION.md`
- **Original Architecture Explanation**: `DOCS/HANDOFF.md`
- **4-Region Release Notes**: `DOCS/RELEASE-v0.1.0-4region.md`
- **Jira Board**: https://kiza-ai.atlassian.net/jira/software/projects/WP4BD/boards/3

---

**Ready to migrate? Start with Step 1!** 🚀
