---
name: V2 Architecture Implementation Plan
overview: Implement V2 architecture (WordPress-as-Engine) using debug-first approach, create Jira tickets, organize DOCS directory, and implement incrementally with acceptance criteria confirmation before commits.
todos:
  - id: phase1-docs-cleanup
    content: Phase 1: Organize DOCS directory - move V1 files to DOCS/V1/, create DOCS/V2/, move V2 files with date prefixes
    dependencies: []
  - id: phase2-jira-tickets
    content: Phase 2: Create DOCS/V2/jira-import-v2.csv with all V2 epics and stories
    dependencies: [phase1-docs-cleanup]
  - id: phase3-epic1-debug
    content: Epic 1: Implement debug infrastructure (WP4BD-V2-001, WP4BD-V2-002) - debug helper functions and template
    dependencies: [phase2-jira-tickets]
  - id: phase4-epic2-core-setup
    content: Epic 2: WordPress Core Setup (WP4BD-V2-010 through WP4BD-V2-012) - copy WordPress, create bootstrap
    dependencies: [phase3-epic1-debug]
  - id: phase5-epic3-database
    content: Epic 3: Database Interception (WP4BD-V2-020 through WP4BD-V2-022) - wpdb bridge
    dependencies: [phase4-epic2-core-setup]
  - id: phase6-epic4-globals
    content: Epic 4: WordPress Globals (WP4BD-V2-030, WP4BD-V2-031) - initialize globals from Backdrop
    dependencies: [phase5-epic3-database]
  - id: phase7-epic5-io
    content: Epic 5: External I/O Interception (WP4BD-V2-040 through WP4BD-V2-042) - intercept WordPress I/O
    dependencies: [phase6-epic4-globals]
  - id: phase8-epic6-bootstrap
    content: Epic 6: Bootstrap Integration (WP4BD-V2-050 through WP4BD-V2-052) - WordPress loads after Backdrop
    dependencies: [phase7-epic5-io]
  - id: phase9-epic7-bridges
    content: Epic 7: Data Structure Bridges (WP4BD-V2-060 through WP4BD-V2-063) - user, term, options bridges
    dependencies: [phase8-epic6-bootstrap]
  - id: phase10-epic8-testing
    content: Epic 8: Testing & Validation (WP4BD-V2-070 through WP4BD-V2-073) - working theme rendering
    dependencies: [phase9-epic7-bridges]
---

# V2 Architecture Implementation Plan

## Overview

Implement the V2 "WordPress-as-Engine" architecture where actual WordPress 4.9 core files are loaded and used for rendering, replacing the V1 stub-based compatibility layer. Use a debug-first approach where the site shows debug output until near completion.

## Phase 1: DOCS Directory Cleanup

### 1.1 Categorize Documentation Files

**V1 Files (Stub-based compatibility layer):**

- `PROJECT-STATUS.md` - V1 implementation status
- `IMPLEMENTATION-ROADMAP.md` - V1 roadmap
- `critical-functions.md` - V1 function priorities
- `nice-to-have-functions.md` - V1 function list
- `irrelevant-functions.md` - V1 function analysis
- `analysis-raw-data.md` - V1 function analysis
- `WP4BD_MODIFICATIONS.md` - V1 modifications to WordPress
- `WP4BD-REQUEST-LIFECYCLE.md` - V1 request lifecycle
- `WP4BD-REQUEST-LIFECYCLE-MERMAID.md` - V1 lifecycle diagram
- `WP4BD-SIMPLE-OVERVIEW.md` - V1 overview
- `dec12-*.md` files - V1 migration docs
- `RELEASE-v0.1.0-4region.md` - V1 release notes
- `MIGRATION-PLAN-4REGION-TO-1REGION.md` - V1 migration plan

**V2 Files (WordPress-as-Engine):**

- `WORDPRESS-CORE-INTEGRATION-PLAN.md` - V2 implementation plan (PRIMARY)
- `ARCHITECTURE-WORDPRESS-AS-ENGINE.md` - V2 architecture (PRIMARY)
- `IMPLEMENTATION-DEBUG-FIRST.md` - V2 debug approach (PRIMARY)
- `jira-import.csv` - V2 ticket format reference
- `WORDPRESS_GLOBALS_REFERENCE.md` - Reference for V2
- `WORDPRESS-THEME-ANALYSIS.md` - Reference for V2

**Shared/Reference Files:**

- `README.md` - Project overview
- `Project Plan_ WordPress Theme Compatibility Layer.md` - Original plan
- `HANDOFF.md` - Handoff notes
- `PR-DESCRIPTION.md` - PR template
- `IMPLEMENTATION-TICKETS.md` - Ticket reference

**Action Items:**

1. Create `DOCS/V1/` subdirectory
2. Move V1 files to `DOCS/V1/` with date prefixes (e.g., `2024-12-12-PROJECT-STATUS.md`)
3. Keep V2 files in `DOCS/` root
4. Create `DOCS/V2/` subdirectory for V2-specific docs
5. Move `WORDPRESS-CORE-INTEGRATION-PLAN.md` → `DOCS/V2/2025-01-XX-WORDPRESS-CORE-INTEGRATION-PLAN.md`
6. Move `ARCHITECTURE-WORDPRESS-AS-ENGINE.md` → `DOCS/V2/2025-01-XX-ARCHITECTURE-WORDPRESS-AS-ENGINE.md`
7. Move `IMPLEMENTATION-DEBUG-FIRST.md` → `DOCS/V2/2025-01-XX-IMPLEMENTATION-DEBUG-FIRST.md`
8. Create `DOCS/V2/README.md` explaining V2 architecture and file organization
9. Remove duplicate `WORDPRESS-CORE-INTEGRATION-PLAN-2.md` (duplicate of `WORDPRESS-CORE-INTEGRATION-PLAN.md`)

### 1.2 Consolidate Architecture Docs

**Decision:** `ARCHITECTURE-WORDPRESS-AS-ENGINE.md` is still current and describes V2 architecture. It complements `WORDPRESS-CORE-INTEGRATION-PLAN.md`:

- `ARCHITECTURE-WORDPRESS-AS-ENGINE.md` - High-level architecture, concepts, examples
- `WORDPRESS-CORE-INTEGRATION-PLAN.md` - Detailed implementation phases

**Action:** Keep both files, add cross-references between them.

## Phase 2: Create Jira Tickets for V2 Implementation

### 2.1 Ticket Structure

Based on `jira-import.csv` format, create tickets following this structure:

- **Epics** - Major phases (7 epics matching WORDPRESS-CORE-INTEGRATION-PLAN.md phases)
- **Stories** - Individual implementation tasks
- **Acceptance Criteria** - Clear, testable criteria
- **Dependencies** - Story dependencies
- **Original Estimate** - Time estimates

### 2.2 Epic Breakdown

**Epic 1: Debug Infrastructure** (from IMPLEMENTATION-DEBUG-FIRST.md)

- Stories: WP4BD-V2-001 through WP4BD-V2-002

**Epic 2: WordPress Core Setup** (Phase 1 from WORDPRESS-CORE-INTEGRATION-PLAN.md)

- Stories: WP4BD-V2-010 through WP4BD-V2-012

**Epic 3: Database Interception** (Phase 2 from WORDPRESS-CORE-INTEGRATION-PLAN.md)

- Stories: WP4BD-V2-020 through WP4BD-V2-022

**Epic 4: WordPress Globals** (Phase 3 from WORDPRESS-CORE-INTEGRATION-PLAN.md)

- Stories: WP4BD-V2-030 through WP4BD-V2-031

**Epic 5: External I/O Interception** (Phase 4 from WORDPRESS-CORE-INTEGRATION-PLAN.md)

- Stories: WP4BD-V2-040 through WP4BD-V2-042

**Epic 6: Bootstrap Integration** (Phase 5 from WORDPRESS-CORE-INTEGRATION-PLAN.md)

- Stories: WP4BD-V2-050 through WP4BD-V2-052

**Epic 7: Data Structure Bridges** (Phase 6 from WORDPRESS-CORE-INTEGRATION-PLAN.md)

- Stories: WP4BD-V2-060 through WP4BD-V2-063

**Epic 8: Testing & Validation** (Phase 7 from WORDPRESS-CORE-INTEGRATION-PLAN.md)

- Stories: WP4BD-V2-070 through WP4BD-V2-073

### 2.3 Create Jira Import CSV

Create `DOCS/V2/jira-import-v2.csv` with all tickets following the format from `jira-import.csv` but adapted for V2 implementation.

## Phase 3: Implementation Workflow

### 3.1 Debug-First Approach

Following `IMPLEMENTATION-DEBUG-FIRST.md`:

1. Site shows debug output by default (not production-looking)
2. Progressive debug levels (1-4) via `?wp4bd_debug=N`
3. Each stage logs data flow
4. Only show rendered output near the end (Epic 8)

### 3.2 Story Implementation Process

For each Jira story:

1. **Implement** - Write code following acceptance criteria
2. **Test** - Verify acceptance criteria met
3. **Get Confirmation** - Present to user for acceptance criteria review
4. **Commit** - After confirmation, commit with story ID in message
5. **Move to Next** - Proceed to next story

### 3.3 Branch Strategy

- **Base Branch:** `main` (or current working branch)
- **V2 Branch:** `feature/v2-wordpress-as-engine` (parallel to V1)
- Keep V1 working, build V2 alongside

## Phase 4: Key Implementation Files

### 4.1 New Files to Create

```
backdrop-1.30/
├── wp49brain/                          # WordPress 4.9 core (copied)
│   ├── wp-content/
│   │   └── db.php                      # wpdb replacement
│   └── wp-config-bd.php                # Backdrop config
├── modules/wp_content/
│   ├── includes/
│   │   ├── wp-bootstrap.php            # WordPress initialization
│   │   ├── wpdb-bridge.php             # Database bridge
│   │   ├── wp-globals-init.php         # Global initialization
│   │   ├── wp-io-bridge.php            # External I/O handling
│   │   ├── wp-user-bridge.php          # User conversion
│   │   ├── wp-term-bridge.php          # Taxonomy conversion
│   │   ├── wp-options-bridge.php       # Settings conversion
│   │   └── wp-debug.php                # Debug utilities
│   └── wp_content.module               # Modified to load WordPress
└── themes/wp/
    └── templates/
        └── page-debug.tpl.php          # Debug-first template
```

### 4.2 Files to Modify

- `backdrop-1.30/modules/wp_content/wp_content.module` - Add WordPress bootstrap
- `backdrop-1.30/themes/wp/template.php` - Add bridge functions

## Phase 5: Implementation Order (Executable Phases)

**Note:** Each phase can be executed independently. After completing a phase, get confirmation before proceeding to the next.

### Phase 1: DOCS Cleanup (No Code Changes)

**Status:** Ready to execute

**Deliverable:** Organized DOCS directory structure

**Time Estimate:** 30 minutes

**Dependencies:** None

### Phase 2: Create Jira Tickets

**Status:** Ready after Phase 1

**Deliverable:** `DOCS/V2/jira-import-v2.csv` with all tickets

**Time Estimate:** 1 hour

**Dependencies:** Phase 1 complete

### Phase 3: Epic 1 - Debug Infrastructure

**Status:** Ready after Phase 2

**Deliverable:** Debug helper functions and template

**Time Estimate:** 2 hours

**Dependencies:** Phase 2 complete

**Stories:** WP4BD-V2-001, WP4BD-V2-002

### Phase 4: Epic 2 - WordPress Core Setup

**Status:** Ready after Phase 3

**Deliverable:** WordPress core copied and bootstrap created

**Time Estimate:** 3 hours

**Dependencies:** Phase 3 complete

**Stories:** WP4BD-V2-010, WP4BD-V2-011, WP4BD-V2-012

### Phase 5: Epic 3 - Database Interception

**Status:** Ready after Phase 4

**Deliverable:** wpdb bridge preventing WordPress DB access

**Time Estimate:** 4 hours

**Dependencies:** Phase 4 complete

**Stories:** WP4BD-V2-020, WP4BD-V2-021, WP4BD-V2-022

### Phase 6: Epic 4 - WordPress Globals

**Status:** Ready after Phase 5

**Deliverable:** WordPress globals initialized from Backdrop

**Time Estimate:** 2 hours

**Dependencies:** Phase 5 complete

**Stories:** WP4BD-V2-030, WP4BD-V2-031

### Phase 7: Epic 5 - External I/O Interception

**Status:** Ready after Phase 6

**Deliverable:** WordPress I/O functions intercepted

**Time Estimate:** 2 hours

**Dependencies:** Phase 6 complete

**Stories:** WP4BD-V2-040, WP4BD-V2-041, WP4BD-V2-042

### Phase 8: Epic 6 - Bootstrap Integration

**Status:** Ready after Phase 7

**Deliverable:** WordPress loads after Backdrop bootstrap

**Time Estimate:** 3 hours

**Dependencies:** Phase 7 complete

**Stories:** WP4BD-V2-050, WP4BD-V2-051, WP4BD-V2-052

### Phase 9: Epic 7 - Data Structure Bridges

**Status:** Ready after Phase 8

**Deliverable:** User, term, and options bridges

**Time Estimate:** 4 hours

**Dependencies:** Phase 8 complete

**Stories:** WP4BD-V2-060, WP4BD-V2-061, WP4BD-V2-062, WP4BD-V2-063

### Phase 10: Epic 8 - Testing & Validation

**Status:** Ready after Phase 9

**Deliverable:** Working WordPress theme rendering

**Time Estimate:** 4 hours

**Dependencies:** Phase 9 complete

**Stories:** WP4BD-V2-070, WP4BD-V2-071, WP4BD-V2-072, WP4BD-V2-073

## Success Criteria

- [ ] DOCS directory organized (V1 vs V2)
- [ ] Jira tickets created for all V2 stories
- [ ] Debug infrastructure working (Epic 1)
- [ ] WordPress core loads without errors (Epic 2)
- [ ] Database interception working (Epic 3)
- [ ] WordPress themes render correctly (Epic 8)
- [ ] Production template shows rendered output
- [ ] All acceptance criteria met and confirmed

## Files to Create/Modify

**New Files:**

- `DOCS/V1/` directory with V1 docs
- `DOCS/V2/` directory with V2 docs
- `DOCS/V2/jira-import-v2.csv` - Jira tickets
- `DOCS/V2/README.md` - V2 documentation index
- All implementation files listed in Phase 4

**Modified Files:**

- `backdrop-1.30/modules/wp_content/wp_content.module`
- `backdrop-1.30/themes/wp/template.php`