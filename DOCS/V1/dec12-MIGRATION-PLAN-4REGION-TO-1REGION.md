# Migration Plan: 4-Region to 1-Region Architecture

**Created:** December 12, 2024
**From:** v0.1.0-4region (working but brittle)
**To:** 1-region "headless WP4.9" architecture
**Base Branch:** `refactor/sunset-stubs-december`

---

## Executive Summary

This document outlines the safe migration from the current 4-region architecture (tagged `v0.1.0-4region`) to the superior 1-region "WordPress-as-engine" architecture that was originally developed Dec 1-2, 2024.

### Why Migrate?

The 1-region architecture is fundamentally better:

| Aspect | 4-Region (Current) | 1-Region (Target) |
|--------|-------------------|-------------------|
| **HTML Control** | Backdrop owns structure, WordPress shoehorned in | WordPress owns structure, Backdrop injects assets |
| **Code Complexity** | High - many workarounds | Low - WordPress works as designed |
| **Theme Compatibility** | Requires theme-specific fixes | Universal - themes work unmodified |
| **Maintainability** | Brittle, prone to breakage | Clean, follows WordPress standards |
| **Performance** | Multiple render passes, buffering | Single pass, direct output |

---

## Current State Analysis

### What the 4-Region Branch Has (v0.1.0-4region)

Valuable additions made Dec 8-12 that might not be in refactor branch:

1. **Debug Infrastructure** (WP4BD-001, WP4BD-002)
   - `modules/wp_content/wp4bd_debug.inc`
   - `templates/page-debug.tpl.php`
   - `?debug` parameter support

2. **Documentation** (created Dec 8-12)
   - `DOCS/ARCHITECTURE-WORDPRESS-AS-ENGINE.md`
   - `DOCS/IMPLEMENTATION-DEBUG-FIRST.md`
   - `DOCS/IMPLEMENTATION-TICKETS.md`
   - `DOCS/jira-import.csv`
   - `DOCS/WORDPRESS_GLOBALS_REFERENCE.md`
   - Various request lifecycle docs

3. **Configuration/Database** (Dec 8)
   - `DB/wp4bd-bd-codex-max-dec8-final.sql.gz`
   - `DB/wp4bd-bd-codex-max-dec8-final-config.tar.gz`

4. **Function Implementations**
   - Some additional WordPress functions
   - Query helpers: `get_queried_object()`, `get_queried_object_id()`
   - Attachment functions: `wp_attachment_is_image()`

### What the Refactor Branch Has (refactor/sunset-stubs-december)

The superior foundation from Dec 1-2:

1. **Clean 1-Region Architecture**
   - `page.tpl.php` - Direct WordPress execution with asset injection
   - No layout/block system overhead
   - Regex-based Backdrop asset insertion

2. **Organized Function Files** (sunset of stubs.php)
   - `functions/conditionals.php`
   - `functions/content-display.php`
   - `functions/escaping.php`
   - `functions/hooks.php`
   - `functions/loop.php`
   - `functions/post-metadata.php`
   - `functions/template-loading.php`
   - `functions/utilities.php`
   - `functions/widgets.php`

3. **Working Theme Compatibility**
   - All 5 Twenty-Something themes validated (Dec 2)
   - Sidebars rendering correctly
   - Post navigation working

4. **Documentation**
   - `DOCS/HANDOFF.md` - Detailed architectural explanation

---

## Migration Strategy

### Option A: Fresh Start (RECOMMENDED)

**Goal**: Start from the clean refactor branch, port only valuable additions from 4-region.

**Advantages:**
- Start from clean, correct architecture
- Avoid carrying forward bad patterns
- Forces evaluation of what's truly needed
- Lower risk of re-introducing bugs

**Disadvantages:**
- Some manual porting work required
- Need to identify all valuable additions

### Option B: Backport Refactor to Main

**Goal**: Cherry-pick commits from refactor branch onto main, resolving conflicts.

**Advantages:**
- Preserves all recent work
- Git history shows evolution

**Disadvantages:**
- High conflict potential
- May accidentally keep bad patterns
- Harder to reason about final state

**Verdict:** Use **Option A** - Fresh Start

---

## Step-by-Step Migration Procedure

### Phase 1: Preparation (Safe, No Changes to Main)

#### Step 1.1: Tag Current Main ✅
```bash
# Already done!
git tag v0.1.0-4region main -m "Working 4-region baseline"
git push origin v0.1.0-4region
```

#### Step 1.2: Create Feature Branch from Refactor
```bash
# Start from the good architecture
git checkout -b feature/1region-migration refactor/sunset-stubs-december

# Verify starting point
git log --oneline -5
# Should show: 05cb588 Database and config for one-region architecture
#              e71edde feat: New architecture - WordPress themes control...
```

#### Step 1.3: Audit What to Port
Create a checklist of valuable additions from v0.1.0-4region:

**To Port:**
- [ ] Debug infrastructure (`wp4bd_debug.inc`, `page-debug.tpl.php`)
- [ ] Recent documentation (Dec 8-12 docs)
- [ ] Function implementations not in refactor (check diff)
- [ ] Database/config updates (if compatible)
- [ ] Test scripts/tools

**Not to Port:**
- [ ] 4-region layout files
- [ ] Region-based rendering logic
- [ ] Theme-specific CSS workarounds
- [ ] Duplicate/conflicting implementations

---

### Phase 2: Port Valuable Additions

#### Step 2.1: Port Debug Infrastructure
```bash
# Copy debug helper functions
git show v0.1.0-4region:backdrop-1.30/modules/wp_content/wp4bd_debug.inc \
  > backdrop-1.30/modules/wp_content/wp4bd_debug.inc

# Copy debug template (may need adaptation for 1-region)
git show v0.1.0-4region:backdrop-1.30/themes/wp/templates/page-debug.tpl.php \
  > backdrop-1.30/themes/wp/templates/page-debug.tpl.php

# Test: Does debug mode work with 1-region architecture?
# May need to adapt for new page.tpl.php structure
```

#### Step 2.2: Port Documentation
```bash
# Port all valuable docs created Dec 8-12
git show v0.1.0-4region:DOCS/ARCHITECTURE-WORDPRESS-AS-ENGINE.md \
  > DOCS/ARCHITECTURE-WORDPRESS-AS-ENGINE.md

git show v0.1.0-4region:DOCS/IMPLEMENTATION-DEBUG-FIRST.md \
  > DOCS/IMPLEMENTATION-DEBUG-FIRST.md

git show v0.1.0-4region:DOCS/jira-import.csv \
  > DOCS/jira-import.csv

# Add others as needed
```

#### Step 2.3: Compare Function Implementations
```bash
# Find functions in 4-region that aren't in refactor
git diff v0.1.0-4region refactor/sunset-stubs-december \
  -- backdrop-1.30/themes/wp/functions/ > /tmp/function-diff.patch

# Review the diff, manually port valuable additions
# Look for:
# - New WordPress function implementations
# - Bug fixes in existing functions
# - Improved Backdrop mappings
```

#### Step 2.4: Database/Config
```bash
# Check if database from refactor branch is available
ls backdrop-1.30/DB/

# If refactor has: dec-10-one-region.sql.gz
# That's probably the right starting point

# Compare with Dec 8 database to see what config changed
# May need to manually merge wp_content.settings
```

---

### Phase 3: Testing & Validation

#### Step 3.1: Environment Setup
```bash
# Ensure DDEV is using the right database
ddev import-db --file=backdrop-1.30/DB/dec-10-one-region.sql.gz

# Clear all caches
ddev bee cc

# Verify theme is active
ddev bee config-get wp_content.settings active_theme
```

#### Step 3.2: Test Each Theme
```bash
#!/bin/bash
# test-all-themes.sh

THEMES="twentythirteen twentyfourteen twentyfifteen twentysixteen twentyseventeen"

for theme in $THEMES; do
  echo "========================================="
  echo "Testing: $theme"
  echo "========================================="

  # Set theme
  ddev bee config-set wp_content.settings active_theme $theme
  ddev bee cc

  # Test homepage
  echo "Homepage:"
  curl -s https://wp4bd-test.ddev.site/ | grep -i "sidebar\|error" | head -5

  # Test single post
  echo "Single post:"
  curl -s https://wp4bd-test.ddev.site/node/1 | grep -i "sidebar\|error" | head -5

  echo ""
done
```

#### Step 3.3: Validation Checklist

Test that these work:

**Homepage:**
- [ ] Header renders
- [ ] Main content area renders
- [ ] Sidebar renders (if theme has sidebar)
- [ ] Footer renders
- [ ] Navigation menus work
- [ ] No duplicate HTML structure
- [ ] Backdrop CSS/JS loads
- [ ] WordPress theme CSS/JS loads

**Single Post:**
- [ ] Post title displays
- [ ] Post content displays
- [ ] Post metadata (date, author) displays
- [ ] Sidebar renders
- [ ] Post navigation (previous/next) works
- [ ] Comments area renders (even if empty)

**Page:**
- [ ] Page title displays
- [ ] Page content displays
- [ ] Sidebar renders (if applicable)

**Archives:**
- [ ] Archive page lists posts
- [ ] Loop works (multiple posts)
- [ ] Pagination works (if configured)

**Debug Mode:**
- [ ] `?debug` parameter triggers debug output
- [ ] Debug info shows correct WordPress globals
- [ ] Template hierarchy visible

---

### Phase 4: Merge to Main

#### Step 4.1: Pre-Merge Checklist

Before merging to main, ensure:

- [ ] All 5 themes render correctly
- [ ] No console errors (browser dev tools)
- [ ] No PHP errors (check logs: `ddev logs`)
- [ ] Database export created for new architecture
- [ ] Documentation updated (README, CHANGELOG)
- [ ] Commit messages are clear

#### Step 4.2: Create Pull Request (if using GitHub)
```bash
# Push feature branch
git push -u origin feature/1region-migration

# Create PR on GitHub
# Title: "Migrate to 1-region architecture (WordPress-as-engine)"
# Body: Link to this migration plan, explain benefits
```

#### Step 4.3: Merge to Main
```bash
# Option 1: Merge commit (preserves history)
git checkout main
git merge feature/1region-migration --no-ff -m "Merge 1-region architecture migration"

# Option 2: Rebase (cleaner history, but rewrites commits)
git checkout feature/1region-migration
git rebase main
git checkout main
git merge feature/1region-migration --ff-only

# Recommended: Option 1 (merge commit) for transparency
```

#### Step 4.4: Tag New Release
```bash
# Create new release tag
git tag -a v0.2.0-1region -m "Release: 1-region architecture (WordPress-as-engine)

This release migrates from the 4-region architecture to the superior
1-region 'WordPress-as-engine' architecture originally developed Dec 1-2.

Key improvements:
- WordPress themes control full HTML structure
- Backdrop assets injected via regex
- Cleaner, more maintainable code
- Universal theme compatibility
- No theme-specific workarounds

See DOCS/MIGRATION-PLAN-4REGION-TO-1REGION.md for migration details.
See DOCS/HANDOFF.md for architectural rationale."

# Push tags
git push origin v0.2.0-1region
git push origin main
```

---

### Phase 5: Cleanup

#### Step 5.1: Archive Old Branches
```bash
# Don't delete old branches yet, just stop using them
# They're preserved in git history and via tags

# Optional: Create an archive branch to group old work
git checkout -b archive/4region-experiments v0.1.0-4region
git push origin archive/4region-experiments
```

#### Step 5.2: Update Documentation
```bash
# Update README.md to reflect new architecture
# Update CLAUDE.MD with new context
# Update CHANGELOG.md with migration entry
```

#### Step 5.3: Notify Team/Stakeholders
- Update Jira board with new status
- Email/Slack announcement about architecture change
- Update any external documentation

---

## Rollback Plan

If something goes wrong during migration:

### Emergency Rollback to 4-Region
```bash
# Reset main to the 4-region tag
git checkout main
git reset --hard v0.1.0-4region
git push origin main --force  # CAUTION: Only if you're sure!

# Or create a revert branch
git checkout -b revert/back-to-4region v0.1.0-4region
# Continue work from there
```

### Debugging New Issues

If 1-region architecture has issues:

1. **Compare with known-good state**
   ```bash
   # Check what Dec 2 version had
   git diff e71edde feature/1region-migration -- backdrop-1.30/themes/wp/templates/page.tpl.php
   ```

2. **Test with minimal config**
   - Reset to default Twenty Seventeen
   - Clear all caches
   - Test with no custom code

3. **Check debug output**
   ```bash
   curl -s https://wp4bd-test.ddev.site/?debug | less
   ```

---

## Risk Assessment

### Low Risk Items
- Porting documentation (no code impact)
- Adding debug infrastructure (optional feature)
- Database config updates (can revert)

### Medium Risk Items
- Function implementations (test thoroughly)
- Template changes (validate with all themes)

### High Risk Items
- None! We have v0.1.0-4region tag as safety net

### Mitigation Strategies
1. **Tag-based safety**: v0.1.0-4region is our rollback point
2. **Feature branch**: Work on `feature/1region-migration`, not directly on main
3. **Incremental testing**: Test after each port, not all at once
4. **Database backups**: Export DB at each milestone
5. **Documentation**: This plan documents every step for reproducibility

---

## Success Criteria

Migration is successful when:

- [ ] All 5 Twenty-Something themes render correctly
- [ ] No duplicate HTML structure (single `<!DOCTYPE>`, `<html>`, `<head>`, `<body>`)
- [ ] Sidebar rendering works universally (no theme-specific code)
- [ ] Backdrop CSS/JS injected correctly (visible in page source)
- [ ] WordPress CSS/JS loaded correctly
- [ ] Post navigation works (previous/next links)
- [ ] Debug mode functional
- [ ] No PHP errors in logs
- [ ] No JavaScript errors in console
- [ ] Performance equal or better than 4-region (measure page load time)
- [ ] Code is cleaner, more maintainable
- [ ] Documentation updated
- [ ] Team understands new architecture

---

## Timeline Estimate

| Phase | Estimated Time | Notes |
|-------|---------------|-------|
| Phase 1: Preparation | 30 min | Mostly done! |
| Phase 2: Port Additions | 2-3 hours | Depends on how much to port |
| Phase 3: Testing | 1-2 hours | Test all themes thoroughly |
| Phase 4: Merge to Main | 30 min | Create PR, review, merge |
| Phase 5: Cleanup | 1 hour | Docs, communication |
| **Total** | **5-7 hours** | Can be split across multiple days |

---

## Post-Migration: Next Steps

After successful migration to 1-region architecture:

1. **Close Jira Epics 1-3** (if architecture work satisfies them)
2. **Continue with Jira Epic 4**: Database Isolation
3. **Implement WordPress 4.9 Core Loading** (per Epic 3)
4. **Add Real WordPress Data Structures** (per Epic 2)
5. **Production Hardening** (Epic 8)

See `DOCS/jira-import.csv` for full roadmap.

---

## Questions & Decisions Log

### Q: Should we keep the debug infrastructure from Dec 8?
**A:** YES. It's valuable for development and doesn't conflict with 1-region architecture. May need minor adaptation.

### Q: Should we keep the Dec 8-12 documentation?
**A:** YES. Some docs (like ARCHITECTURE-WORDPRESS-AS-ENGINE.md) describe the end-goal architecture we're migrating TO. Keep as roadmap.

### Q: What about the database from Dec 8 vs Dec 2?
**A:** Use Dec 2 database (dec-10-one-region.sql.gz) as starting point since it's aligned with 1-region architecture. Port any config changes from Dec 8 database manually if needed.

### Q: Should we delete the 4-region layout files?
**A:** YES. Remove them from the codebase since they're not used in 1-region architecture. They're preserved in git history and v0.1.0-4region tag.

---

## References

- **HANDOFF.md**: `DOCS/HANDOFF.md` - Original Dec 1-2 architecture explanation
- **Release Notes**: `DOCS/RELEASE-v0.1.0-4region.md` - Current 4-region state
- **Jira Board**: https://kiza-ai.atlassian.net/jira/software/projects/WP4BD/boards/3
- **Key Commit**: e71edde - The architectural breakthrough

---

## Conclusion

The 1-region "WordPress-as-engine" architecture is the correct long-term solution. By safely tagging the current working state (v0.1.0-4region) and carefully porting valuable additions, we can migrate with minimal risk.

**The path forward:**
1. ✅ Tag current main (v0.1.0-4region)
2. ✅ Document current state
3. ⏳ Create feature branch from refactor/sunset-stubs-december
4. ⏳ Port debug infrastructure
5. ⏳ Port documentation
6. ⏳ Test all themes
7. ⏳ Merge to main
8. ⏳ Tag new release (v0.2.0-1region)

**Let's do this!** 🚀
