# Project Plan: WP4BD (WordPress Theme Compatibility Layer)

**Date:** November 2025
**Version:** 1.3 (Updated WP Version Context)

## 1. Project Overview

**Project Goal:** To create a backward compatibility layer that allows older themes developed for WordPress (specifically the Classic Editor era) to be rendered and function correctly within a Backdrop CMS environment. This will be achieved by intercepting calls to the WordPress PHP functions and template suggestions.

**Primary Benefit:** Enables effortless migration and prolonged life for existing theme assets, drastically reducing the cost and effort of platform transition.

**Proof of Concept (PoC) Target:** The initial PoC will focus on successfully running the **WordPress Twenty Seventeen** theme without modification on a system running **Backdrop CMS version 1.30**. This specific target ensures alignment with themes running on **WordPress 4.9**, the last major release before the Block Editor shift.

## 2. Scope of Work

The project is focused on creating a **targeted functional compatibility layer** and a **template redirection mechanism**.

### 2.1. Inclusions (What will be done)

1.  **Twenty Seventeen Function Audit & Triage:** A line-by-line analysis of the Twenty Seventeen theme to categorize every WordPress function it calls into "Critical" (must work for rendering), "Nice-to-Have" (can be stubbed initially), and "Irrelevant" (can be ignored/logged).
2.  **Functional Loop Emulation:** Implementation of the core WordPress Loop logic (`have_posts`, `the_post`, `wp_reset_postdata`) and the global `$post` object mapping to ensuring content iterates and displays correctly.
3.  **Critical Template Tag Implementation:** Functional implementation of essential display functions (e.g., `the_title()`, `the_content()`, `get_header()`, `get_template_part()`) to ensure they actually output data from the Backdrop system.
4.  **Non-Critical Function Stubbing:** Creation of logging stubs for functions identified as non-essential for the initial rendering (e.g., complex comment form logic or pingbacks) to prevent crashes without blocking the page load.
5.  **Template Redirection/Interception:** Mechanism to intercept the legacy framework's template suggestion process and redirect rendering to the appropriate WordPress template files.
6.  **Configuration:** A simple interface to enable/disable the layer and view logs.

### 2.2. Exclusions (What will NOT be done)

1.  **Re-implementing Complex Business Logic:** We will not fully re-implement complex WP subsystems (e.g., the entire Widgets API or Customizer API) in Phase 1 unless critical for Twenty Seventeen's basic rendering.
2.  **Full Feature Parity:** The layer is designed for *theme compatibility*, not *module compatibility*. It will not support complex legacy module APIs or functions not typically used within a theme's template files.
3.  **Support for Block Editor Themes:** Scope is strictly limited to themes architecturally similar to **Twenty Seventeen** and earlier. Themes relying on the Gutenberg Block Editor (Twenty Nineteen and later) are explicitly excluded from this PoC scope.

## 3. Key Phases and Milestones

The project will be broken down into distinct phases, allowing for iterative testing.

| Phase | Milestone Title | Key Activities | Deliverable |
| :--- | :--- | :--- | :--- |
| **P1** | **Foundation & Triage** | Set up module structure. **Run the Function Audit on Twenty Seventeen.** Categorize all identified functions. Define the logging utility. | Plugin Skeleton & Triage Report (Critical vs. Stub List) |
| **P2** | **Core Logic & Loop** | Implement the "Loop State Machine" in the module. Create the `WP_Post_Mock` class and map Backdrop Node fields to it. Implement `have_posts()` and `the_post()`. | Functional Loop Logic (Data appears in variables) |
| **P3** | **Template Tag Implementation** | Implement the "Critical" functions identified in P1 (e.g., `get_header`, `the_title`). Ensure they output the data setup in P2. Implement file loading for `get_template_part`. | Library of Working Template Tags |
| **P4** | **Integration & Rendering** | Connect the Backdrop theme rendering hook to the WP template files. Ensure `header.php` -> `index.php` -> `footer.php` flow works. Verify CSS/JS asset loading. | **Visual Success:** Twenty Seventeen renders a node. |
| **P5** | **Refinement** | Address "Nice-to-Have" functions. Fix styling glitches. Ensure no PHP warnings are generated during normal browsing. | Final Polished PoC |

## 4. Risks and Mitigations

| Risk | Description | Mitigation Strategy |
| :--- | :--- | :--- |
| **R1** | **Loop Logic Mismatch** | The way WP handles the global `$post` state vs. Backdrop's `node_view` is complex. | P2 focuses exclusively on this state management. We will use unit tests to verify `$post` is populated correctly before attempting to render HTML. |
| **R2** | **Critical Function Missing** | A "Nice-to-Have" function turns out to be critical for layout (e.g., a CSS class generator). | The P1 Triage is crucial. We will be conservative and mark layout-related functions as Critical. The logging system will catch runtime surprises. |
| **R3** | **Asset Loading Paths** | WP themes expect assets relative to their folder; Backdrop might serve them incorrectly. | Implement `get_theme_file_uri()` and related functions early to ensure paths are rewritten to the correct Bridge Theme subdirectory. |

## 5. Key Deliverables

* **WP-TCL Module Package:** The complete, runnable Backdrop module.
* **Triage Report:** A document listing every function in Twenty Seventeen and its implementation status.
* **Bridge Theme:** The Backdrop theme wrapping the Twenty Seventeen files.
* **Installation Guide:** Instructions for setting up the PoC on a Backdrop 1.30 site.

***
