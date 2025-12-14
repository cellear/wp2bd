# WordPress to Backdrop Theme Compatibility - Implementation Roadmap

**Project:** WP2BD - Twenty Seventeen Theme Compatibility Layer
**Target:** Run WordPress Twenty Seventeen theme on Backdrop CMS 1.30
**Approach:** Parallel implementation with individual function specs

---

## PHASE 1: FOUNDATION (Week 1-2)

### Module Structure Setup
- Create `wp2bd` Backdrop module skeleton
- Set up testing infrastructure (BackdropUnitTestCase)
- Create logging/debugging utilities
- Set up module configuration page

### Work Packages:
1. **WP2BD-001**: Module skeleton and basic structure
2. **WP2BD-002**: Testing framework setup
3. **WP2BD-003**: Logging and debugging utilities

---

## PHASE 2: CORE FUNCTIONS - P0 (Week 2-4)

Implementation of CRITICAL functions needed for basic rendering.

### A. The Loop System (TIGHTLY COUPLED - Single PR)
- **WP2BD-LOOP**: Implement The Loop state machine
  - `have_posts()`
  - `the_post()`
  - `wp_reset_postdata()`
  - `WP_Post` mock class
  - `WP_Query` mock class

### B. Template Loading (4 separate PRs)
- **WP2BD-010**: `get_header()` - Load header template
- **WP2BD-011**: `get_footer()` - Load footer template
- **WP2BD-012**: `get_sidebar()` - Load sidebar template
- **WP2BD-013**: `get_template_part()` - Load modular templates

### C. Content Display - Title/Permalink (8 PRs, can be parallel)
- **WP2BD-020**: `the_title()` + `get_the_title()`
- **WP2BD-021**: `the_permalink()` + `get_permalink()`
- **WP2BD-022**: `the_ID()` + `get_the_ID()`
- **WP2BD-023**: `the_content()`
- **WP2BD-024**: `the_excerpt()`
- **WP2BD-025**: `post_class()`
- **WP2BD-026**: `body_class()`
- **WP2BD-027**: `language_attributes()`

### D. Core Conditionals (9 PRs, can be parallel)
- **WP2BD-030**: `is_single()` + `is_singular()`
- **WP2BD-031**: `is_page()`
- **WP2BD-032**: `is_home()` + `is_front_page()`
- **WP2BD-033**: `is_archive()`
- **WP2BD-034**: `is_search()`
- **WP2BD-035**: `is_sticky()`
- **WP2BD-036**: `is_404()`
- **WP2BD-037**: `get_post_type()`
- **WP2BD-038**: `get_post_format()`

### E. Escaping Functions (3 PRs, can be parallel)
- **WP2BD-040**: `esc_html()` + `esc_attr()`
- **WP2BD-041**: `esc_url()` + `esc_url_raw()`
- **WP2BD-042**: Security function tests

### F. Hook System (COUPLED - Single PR)
- **WP2BD-050**: WordPress hook system implementation
  - `add_action()`
  - `do_action()`
  - `add_filter()`
  - `apply_filters()`
  - `remove_action()`
  - `remove_filter()`

### G. Core Hooks (2 PRs)
- **WP2BD-055**: `wp_head()` implementation
- **WP2BD-056**: `wp_footer()` implementation

### H. Utility Functions (5 PRs, can be parallel)
- **WP2BD-060**: `home_url()` + site URL functions
- **WP2BD-061**: `bloginfo()` + `get_bloginfo()`
- **WP2BD-062**: `sprintf()` + `printf()` wrappers (if needed)
- **WP2BD-063**: `absint()` + sanitization helpers
- **WP2BD-064**: `get_template_directory()` + path functions

---

## PHASE 3: CRITICAL FUNCTIONS - P1 (Week 4-6)

### I. Images & Thumbnails (5 PRs)
- **WP2BD-070**: `has_post_thumbnail()`
- **WP2BD-071**: `get_the_post_thumbnail()` + `the_post_thumbnail()`
- **WP2BD-072**: `get_post_thumbnail_id()`
- **WP2BD-073**: `wp_get_attachment_image_src()`
- **WP2BD-074**: `add_image_size()` registration

### J. Post Metadata (6 PRs, can be parallel)
- **WP2BD-080**: `get_the_date()` + `get_the_time()`
- **WP2BD-081**: `get_the_modified_date()` + `get_the_modified_time()`
- **WP2BD-082**: `get_the_author()` + `get_the_author_meta()`
- **WP2BD-083**: `get_author_posts_url()`
- **WP2BD-084**: `get_edit_post_link()` + `edit_post_link()`
- **WP2BD-085**: `get_queried_object_id()`

### K. Pagination (3 PRs)
- **WP2BD-090**: `the_posts_pagination()`
- **WP2BD-091**: `wp_link_pages()`
- **WP2BD-092**: `the_post_navigation()`

### L. Taxonomy/Categories (3 PRs)
- **WP2BD-095**: `get_the_category_list()`
- **WP2BD-096**: `get_the_tag_list()`
- **WP2BD-097**: `the_archive_title()` + `the_archive_description()`

---

## PHASE 4: NICE-TO-HAVE - Can Stub (Week 6-8)

### M. Translation Functions (Stub or Implement)
- **WP2BD-100**: Translation system (`__`, `_e`, `_x`, `_nx`)
- **WP2BD-101**: `load_theme_textdomain()`

### N. Script/Style Enqueuing (Important for theme display)
- **WP2BD-110**: `wp_enqueue_style()` + `wp_enqueue_script()`
- **WP2BD-111**: Script/style dependencies system
- **WP2BD-112**: `wp_localize_script()`

### O. Navigation Menus (Can stub initially)
- **WP2BD-120**: `register_nav_menus()`
- **WP2BD-121**: `has_nav_menu()` + `wp_nav_menu()`

### P. Widgets/Sidebars (Can stub initially)
- **WP2BD-125**: `register_sidebar()`
- **WP2BD-126**: `is_active_sidebar()` + `dynamic_sidebar()`

### Q. Comments System (Can stub initially)
- **WP2BD-130**: `comments_open()` + `get_comments_number()`
- **WP2BD-131**: `comments_template()` + `comment_form()`
- **WP2BD-132**: `wp_list_comments()` + `the_comments_pagination()`

### R. Search (Can stub initially)
- **WP2BD-135**: `get_search_form()` + `get_search_query()`

### S. Theme Customization (Can stub with defaults)
- **WP2BD-140**: `get_theme_mod()` simple implementation
- **WP2BD-141**: `add_theme_support()` basic version

---

## PHASE 5: THEME-SPECIFIC FUNCTIONS (Week 8-10)

### T. Twenty Seventeen Custom Functions
These are defined BY the theme, need to ensure they work with our compatibility layer:

- **WP2BD-150**: `twentyseventeen_setup()` verification
- **WP2BD-151**: `twentyseventeen_posted_on()` + `twentyseventeen_time_link()`
- **WP2BD-152**: `twentyseventeen_entry_footer()`
- **WP2BD-153**: `twentyseventeen_edit_link()`
- **WP2BD-154**: `twentyseventeen_body_classes()`
- **WP2BD-155**: `twentyseventeen_get_svg()` SVG icon system
- **WP2BD-156**: `twentyseventeen_panel_count()` + front page panels

---

## PHASE 6: INTEGRATION & TESTING (Week 10-12)

### U. Integration Testing
- **WP2BD-200**: End-to-end test: Single post page
- **WP2BD-201**: End-to-end test: Blog archive page
- **WP2BD-202**: End-to-end test: Static page
- **WP2BD-203**: End-to-end test: Front page with panels
- **WP2BD-204**: End-to-end test: 404 page
- **WP2BD-205**: End-to-end test: Search results

### V. Performance & Optimization
- **WP2BD-210**: Caching implementation
- **WP2BD-211**: Performance profiling
- **WP2BD-212**: Memory optimization

### W. Documentation
- **WP2BD-220**: API documentation
- **WP2BD-221**: Installation guide
- **WP2BD-222**: Migration guide (WP to Backdrop)
- **WP2BD-223**: Troubleshooting guide

---

## WORK PACKAGE PRIORITY MATRIX

| Priority | Description | Count | Dependencies |
|----------|-------------|-------|--------------|
| **P0** | Must work for ANY page to render | ~25 | Phase 2 |
| **P1** | Needed for full theme display | ~20 | P0 complete |
| **P2** | Enhances functionality | ~15 | P1 complete |
| **P3** | Nice-to-have, can stub | ~30 | Can defer |
| **P4** | Optional/irrelevant | ~40 | Can ignore |

---

## PARALLEL IMPLEMENTATION STRATEGY

### Maximizing Parallelism:

**Week 1-2:** Foundation (Sequential)
- Set up module structure
- Create testing framework

**Week 2-3:** The Loop + Templates (5 parallel threads)
- Thread 1: The Loop (LOOP)
- Thread 2: Template loading (010-013)
- Thread 3: Content display 1 (020-023)
- Thread 4: Content display 2 (024-027)
- Thread 5: Conditionals 1 (030-033)

**Week 3-4:** Complete P0 (6 parallel threads)
- Thread 1: Conditionals 2 (034-038)
- Thread 2: Escaping (040-042)
- Thread 3: Hook system (050)
- Thread 4: Core hooks (055-056)
- Thread 5: Utilities 1 (060-062)
- Thread 6: Utilities 2 (063-064)

**Week 4-5:** P1 Functions (6 parallel threads)
- Thread 1: Images (070-074)
- Thread 2: Post metadata 1 (080-082)
- Thread 3: Post metadata 2 (083-085)
- Thread 4: Pagination (090-092)
- Thread 5: Taxonomy (095-097)
- Thread 6: Translation (100-101)

**Week 6-7:** Enqueuing + Stubs (4 parallel threads)
- Thread 1: Enqueue system (110-112)
- Thread 2: Menus (120-121)
- Thread 3: Widgets + Sidebars (125-126)
- Thread 4: Comments + Search (130-135)

**Week 8-9:** Theme Functions (3 parallel threads)
- Thread 1: Theme setup (150-152)
- Thread 2: Theme utilities (153-155)
- Thread 3: Theme panels (156)

**Week 10-12:** Testing + Polish (Sequential)
- Integration tests
- Performance tuning
- Documentation

---

## FUNCTION SPECIFICATION FORMAT

Each work package will have a detailed spec in `/specs/` directory:

```
/specs/
  WP2BD-LOOP.md          (The Loop implementation)
  WP2BD-010.md           (get_header)
  WP2BD-011.md           (get_footer)
  ...
  WP2BD-220.md           (Documentation)
```

Each spec contains:
1. Function signature
2. WordPress behavior description
3. Backdrop mapping strategy
4. Input assumptions
5. Output contract
6. Dependencies
7. Test cases (min 3-5)
8. Example usage
9. Edge cases

---

## PROGRESS TRACKING

Use GitHub issues/project board:
- **Backlog:** All WP2BD-XXX items
- **Ready:** Specs complete, dependencies met
- **In Progress:** Agent/developer working
- **Review:** PR submitted
- **Done:** Merged and tested

---

## SUCCESS CRITERIA

### Minimum Viable Render (MVR)
- ✅ Single blog post displays correctly
- ✅ Post title, content, date, author visible
- ✅ Header, footer, sidebar load
- ✅ Navigation works
- ✅ Images display
- ✅ Basic styling applied

### Full Theme Compatibility
- ✅ All template types work (single, page, archive, search, 404)
- ✅ Front page with panels
- ✅ Pagination functional
- ✅ Categories and tags display
- ✅ Comments system (if implemented)
- ✅ Responsive images
- ✅ No PHP errors or warnings

---

## NEXT STEPS

1. **Create `/specs/` directory**
2. **Generate individual function specs** (start with P0)
3. **Set up GitHub project board**
4. **Create first batch of implementation tasks**
5. **Launch parallel development**

**Ready to proceed with spec creation?**
