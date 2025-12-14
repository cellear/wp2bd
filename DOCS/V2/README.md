# V2 Architecture Documentation

**Version:** 2.0 (WordPress-as-Engine)  
**Date:** January 2025  
**Status:** In Development

---

## Overview

This directory contains documentation for the **V2 "WordPress-as-Engine" architecture** - an approach where actual WordPress 4.9 core files are loaded and used for rendering, rather than reimplementing WordPress functions piece-by-piece.

**Key Principle:** WordPress becomes a pure rendering engine. Backdrop handles all data storage and retrieval. WordPress just displays it.

---

## Core Documentation Files

### Primary Architecture Documents

1. **[ARCHITECTURE-WORDPRESS-AS-ENGINE.md](2025-01-15-ARCHITECTURE-WORDPRESS-AS-ENGINE.md)**
   - High-level architecture overview
   - System layers and communication flow
   - Function interception strategy
   - Examples and use cases
   - **Start here** for understanding the overall approach

2. **[WORDPRESS-CORE-INTEGRATION-PLAN.md](2025-01-15-WORDPRESS-CORE-INTEGRATION-PLAN.md)**
   - Detailed implementation phases
   - Step-by-step technical tasks
   - File structure and code organization
   - **Use this** for implementation guidance

3. **[IMPLEMENTATION-DEBUG-FIRST.md](2025-01-15-IMPLEMENTATION-DEBUG-FIRST.md)**
   - Debug-first development approach
   - Progressive debug levels (1-4)
   - Stage-by-stage implementation
   - **Follow this** for development workflow

### Reference Documents

4. **[WORDPRESS_GLOBALS_REFERENCE.md](2024-12-12-WORDPRESS_GLOBALS_REFERENCE.md)**
   - WordPress global variables reference
   - Used for understanding WordPress internals

5. **[WORDPRESS-THEME-ANALYSIS.md](2024-12-12-WORDPRESS-THEME-ANALYSIS.md)**
   - Analysis of WordPress theme structure
   - Theme compatibility notes

6. **[jira-import.csv](2024-12-12-jira-import.csv)**
   - Jira ticket format reference
   - Used as template for V2 tickets

---

## Architecture Comparison

### V1 (Stub-Based) - See `../V1/`
- Custom reimplementation of WordPress functions
- Stub-based compatibility layer
- Function-by-function implementation
- Located in `DOCS/V1/`

### V2 (WordPress-as-Engine) - This Directory
- Loads actual WordPress 4.9 core files
- Uses real WordPress classes (WP_Post, WP_Query)
- Database interception via `db.php` drop-in
- Function overrides only for data-fetching
- Located in `DOCS/V2/`

---

## Implementation Phases

Based on `WORDPRESS-CORE-INTEGRATION-PLAN.md`:

1. **Phase 1:** Setup WordPress Core
2. **Phase 2:** Database Interception Layer
3. **Phase 3:** WordPress Global Variables Setup
4. **Phase 4:** External I/O Interception
5. **Phase 5:** WordPress Bootstrap Integration
6. **Phase 6:** Data Structure Analysis
7. **Phase 7:** Testing and Validation

---

## Development Workflow

Following `IMPLEMENTATION-DEBUG-FIRST.md`:

1. **Debug-First:** Site shows debug output by default
2. **Progressive Levels:** Use `?wp4bd_debug=N` (1-4) for detail
3. **Stage Tracking:** Each implementation stage logs data flow
4. **Production Later:** Only show rendered output near completion

---

## Key Concepts

### WordPress-as-Engine
WordPress core files are loaded but WordPress never connects to its own database. All data comes from Backdrop.

### Database Interception
WordPress's `wpdb` class is replaced via `wp-content/db.php` drop-in. All queries are intercepted and mapped to Backdrop.

### Function Overrides
Only functions that fetch data from Backdrop are overridden. All other WordPress functions work as-is.

### Debug Infrastructure
Comprehensive debugging system tracks data flow through all stages of WordPress rendering.

---

## File Organization

All V2 documentation files use date prefixes (YYYY-MM-DD) for chronological understanding:

- `2025-01-15-*` - Current V2 architecture documents
- `2024-12-12-*` - Reference materials from V2 planning

---

## Related Documentation

- **V1 Documentation:** See `../V1/` for stub-based compatibility layer docs
- **Project Overview:** See `../README.md` for project mission
- **Original Plan:** See `../Project Plan_ WordPress Theme Compatibility Layer.md`

---

## Questions?

- Architecture questions → See `ARCHITECTURE-WORDPRESS-AS-ENGINE.md`
- Implementation questions → See `WORDPRESS-CORE-INTEGRATION-PLAN.md`
- Development workflow → See `IMPLEMENTATION-DEBUG-FIRST.md`

---

**Last Updated:** 2025-01-15

