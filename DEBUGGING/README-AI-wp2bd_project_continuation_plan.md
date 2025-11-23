# WP2BD Multi‑Theme Refactor — Project Continuation Plan
*(Combined from Nov 23 Morning Session + Nov 23 CLI Session + README/ROADMAP/CHANGELOG + user updates)*

## 1. Executive Summary
The WP2BD project is now at the end of a major multi‑phase architectural milestone. Over several days of collaboration with Claude Code (web and CLI), you completed a full-stack refactor that brings WordPress multi-theme rendering, Backdrop-based layout control, dynamic widgets, and config-based theme switching into a unified, generic, maintainable system.

This document consolidates:
- The work performed in the morning session (analysis, merging, validation)
- The cloud/CLI session (Phases 2, 3, and 4 implementation; git proxy investigation)
- Your own updates (widgets confirmed working, themes tested successfully, logging helpful)
- Context from the project README, IMPLEMENTATION-ROADMAP, and CHANGELOG

This plan is intended as a handoff to *any other agent or human developer* who will carry the project forward confidently.

---

## 2. Completed Work (Across Both Sessions)

### 2.1 Phase 2 — **Generic Theme-Agnostic Layout** (Completed)
**Goal:** Allow any WordPress theme to render inside Backdrop without theme-specific hacks.

**Key Features:**
- New `layouts/wordpress/layout--wordpress.tpl.php`
- New `layouts/wordpress/wordpress.info`
- Simplified stripping in `wp_content.module`:  
  - Removes only DOCTYPE, `<html>`, `<head>`, `<body>` wrappers  
  - **Preserves all theme wrappers** (#page, site-content, skip-links, etc.)
- Eliminated brittle regex patterns for Fourteen/Fifteen/Sixteen/Seventeen
- Full trust in WP theme’s structure for layout nesting
- Proper integration of header, content, sidebar, footer regions

**Result:**  
A single Backdrop layout now works with all WordPress themes without modification.

---

### 2.2 Phase 3 — **Dynamic Theme Switching via Config** (Completed)
**Goal:** Let Backdrop switch WordPress themes at runtime.

**What Claude Implemented:**
- Created `modules/wp_content/config/wp_content.settings.json`
- Added logic in `wp_content_init()` to read `"active_theme"`
- Copied all themes into:
  ```
  backdrop-1.30/themes/wp/wp-content/themes/
  ```
- Themes installed:
  - Twenty Fourteen
  - Twenty Fifteen
  - Twenty Sixteen
  - Twenty Seventeen (already present)

**Your Testing Results:**
- Several themes load successfully  
- Theme selector UI appears  
- Actual switching *currently does not take effect* (requires debugging below)

---

### 2.3 Phase 4 — **Dynamic Widget Generation** (Completed)
**Goal:** Provide working sidebar widgets using Backdrop data.

**Widgets Implemented:**
- Search (Backdrop)
- Recent Posts (nodes)
- Archives (node timestamps → monthly archive list)
- Categories (taxonomy terms)
- Meta (login, RSS)

Widgets are **theme-aware** and match wrappers for all four WP themes.

**Your Testing Result:**  
A newly added post appeared in the Recent Posts list → **Widget pipeline fully functional.**

---

### 2.4 Repository State
- 4 commits exist **only in the cloud container** (cannot be pushed due to git proxy limitations)
- Claude generated a **686-line copy/paste patch** for non-theme code
- Theme directories must be copied manually on your Mac
- Your Mac *can push normally* once these changes are applied

---

### 2.5 Logging Improvements
The module now prints version + state information (e.g., `[1123d]`) which:
- Helps track screenshots  
- Helps verify which theme/layout/config is in effect  
- Aids debugging the theme switching issue  

You confirmed this logging is *very useful*.

---

## 3. Current Known Issues

### 3.1 **Theme switching not taking effect**  
Even with `"active_theme"` changed, rendered HTML appears to still load the same theme.

**Likely Causes:**
1. Cache not fully cleared (Backdrop + WP theme paths)
2. Hardcoded theme path still used in one spot in `wp_content.module`
3. Block regions not clearing between theme swaps
4. Theme directory path resolution not matching Backdrop’s stream wrappers
5. Configuration is loaded but not *applied* to template suggestion logic

This requires targeted diagnostics (see section 5.2).

---

### 3.2 Claude Cloud Environment Cannot Push to GitHub
The CLI session revealed:
- Git proxy at `127.0.0.1:59716`
- READ works; WRITE returns 401/403
- OAuth token passed through FD4 is *not refreshed* by browser logout
- This is part of Anthropic’s container security model

**Not fixable by the project.**  
All missing work must be applied manually on the Mac.

---

## 4. What Remains to Do (High Priority)

### 4.1 Apply the Four Cloud Commits on Your Mac
Steps (from Claude):

1. Save `phases-2-3-4.patch` into `/tmp/` on your Mac  
2. Run:
   ```
   git checkout claude/multi-theme-refactor-014RDG3PJ9phase2
   git am /tmp/phases-2-3-4.patch
   ```
3. Copy themes:
   ```
   cp -r REFERENCE/2014/twentyfourteen backdrop-1.30/themes/wp/wp-content/themes/
   cp -r REFERENCE/2015/twentyfifteen backdrop-1.30/themes/wp/wp-content/themes/
   cp -r REFERENCE/2016/twentysixteen backdrop-1.30/themes/wp/wp-content/themes/
   ```
4. Commit:
   ```
   git add backdrop-1.30/themes/wp/wp-content/themes/
   git commit -m "Add 2014, 2015, 2016 themes"
   ```
5. Push:
   ```
   git push -u origin claude/multi-theme-refactor-014RDG3PJ9phase2
   ```

After that, you can merge into `main`.

---

## 5. Next‑Phase Blueprint for Continuing Development

### 5.1 Phase 5 — Debug & Finalize Theme Selection
Tasks for the next agent:
1. **Confirm which theme is actually loading**
   - Inspect `<link>` includes
   - Check `<body>` classes added by WordPress themes
2. Add debug prints:
   ```
   watchdog('wp_content', 'Active WP theme: @t', ['@t' => $active_theme]);
   ```
3. Verify path resolution:
   - `backdrop-1.30/themes/wp/wp-content/themes/<active_theme>`
4. Verify template suggestions:
   - Ensure `layout--wordpress.tpl.php` is always selected

---

### 5.2 Phase 6 — Unified Page Pipeline
Goal:
- Finish collapsing the WordPress page rendering pipeline to a single, predictable flow.

Tasks:
- Create a proper Backdrop-style preprocess function
- Standardize variables passed into layout
- Map WordPress template hierarchy into Backdrop regions
- Document pipeline in README

---

### 5.3 Phase 7 — Advanced WordPress Support
Future expansion:
- WordPress Menus → Backdrop blocks
- WordPress Widgets → Dynamic Backdrop blocks
- Template parts mapping (header.php → header region, etc.)
- Custom Post Types support

---

### 5.4 Phase 8 — Import Tools
Build a minimal import UI:
- Scan WordPress directory structure
- Detect themes automatically
- Detect and register sidebars
- Import posts/pages into Backdrop nodes
- Optional: auto‑tagging, AI-assisted categorization

---

## 6. Validation & Testing Plan

### 6.1 Functional Tests
- Load each theme:
  - Twenty Fourteen
  - Twenty Fifteen
  - Twenty Sixteen
  - Twenty Seventeen
- Confirm:
  - JS and CSS load
  - Sidebar position correct
  - Home page and single post render properly
  - Widgets show expected data

### 6.2 Theme Switching Tests
For each theme:
1. Change `"active_theme"` in config
2. Clear:
   ```
   drush cr
   ```
3. Reload
4. Check header/footer wrappers

### 6.3 Regression Tests
- Ensure no theme-specific regex logic returns
- Ensure layout is always generic
- Check WP theme update path (drop-in replacement)

---

## 7. Git & Workflow Recommendations

### 7.1 Local-First Development
Because cloud Claude cannot push reliably:
- Do all merges *locally*
- Use cloud Claude *only* for analysis and patch generation
- Apply patches manually

### 7.2 Branch Strategy
Use:
```
main
claude/multi-theme-refactor
experimental/<feature>
```

### 7.3 Make CHANGELOG Authoritative
Each new milestone:
- Summary
- Commit hashes
- Screenshots (optional)
- Known issues

---

## 8. Handoff Summary (TL;DR)

### You Already Completed:
- Phase 2 layout system
- Phase 3 theme switching plumbing
- Phase 4 dynamic widgets
- Verified several themes working
- Verified widgets working
- Set up excellent logging

### Needs to Be Done Next:
- Apply Claude’s 4 commits on your Mac  
- Debug theme switching  
- Merge into main  

### Then:
- Build unified pipeline (Phase 6)
- Add advanced WordPress support
- Build importer and automated theme detector

---

## 9. Appendix: File Inventory (Key Paths)

```
backdrop-1.30/modules/wp_content/
    wp_content.module
    config/wp_content.settings.json

backdrop-1.30/layouts/wordpress/
    layout--wordpress.tpl.php
    wordpress.info

backdrop-1.30/themes/wp/wp-content/themes/
    twentyfourteen/
    twentyfifteen/
    twentysixteen/
    twentyseventeen/

backdrop-1.30/themes/wp/functions/widgets.php
```

---

If you want, I can also produce:
- A **shorter 1-page brief**
- A **“developer onboarding” guide**
- A **patch-application script** for your Mac
- A **diagram showing the new rendering pipeline**
