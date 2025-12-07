# WP4BD Request Lifecycle - Mermaid Diagrams

## 1. High-Level System Flow

```mermaid
sequenceDiagram
    participant Browser
    participant Backdrop
    participant WP4BD as WP4BD Bridge
    participant WordPress as WordPress Engine
    participant Theme as WP Theme

    Browser->>Backdrop: GET /blog/my-post
    activate Backdrop

    Backdrop->>Backdrop: Route request
    Backdrop->>Backdrop: node_load($nid)
    Note over Backdrop: Load content from database

    Backdrop->>WP4BD: trigger wp_content_node_view()
    activate WP4BD

    WP4BD->>WP4BD: Transform Backdrop node → WP_Post
    WP4BD->>WP4BD: Setup WordPress globals
    WP4BD->>WP4BD: Populate caches
    WP4BD->>WP4BD: Mock database layer

    WP4BD->>WordPress: Bootstrap & render
    activate WordPress

    WordPress->>WordPress: Minimal bootstrap
    WordPress->>Theme: Load functions.php
    activate Theme
    Theme-->>WordPress: Register hooks, features
    deactivate Theme

    WordPress->>Theme: Require template file
    activate Theme
    Theme->>Theme: Execute The Loop
    Theme->>Theme: Call template tags
    Theme-->>WordPress: HTML output (buffered)
    deactivate Theme

    WordPress-->>WP4BD: Return rendered HTML
    deactivate WordPress

    WP4BD->>WP4BD: Post-process (optional)
    WP4BD-->>Backdrop: Return HTML
    deactivate WP4BD

    Backdrop-->>Browser: HTTP Response
    deactivate Backdrop
```

## 2. Data Transformation Flow

```mermaid
graph LR
    A[Backdrop Node] -->|Transform| B[WP4BD Bridge]

    B --> C1[WP_Post Object]
    B --> C2[WP_Query Object]
    B --> C3[WordPress Globals]
    B --> C4[Object Cache]
    B --> C5[Mock wpdb]

    C1 --> D[WordPress Engine]
    C2 --> D
    C3 --> D
    C4 --> D
    C5 --> D

    D --> E[Rendered HTML]

    style A fill:#e1f5ff
    style B fill:#fff4e6
    style D fill:#f3e5f5
    style E fill:#e8f5e9
```

## 3. WordPress Environment Setup (Detailed)

```mermaid
graph TD
    Start[Backdrop Node Loaded] --> Transform[Transform to WP_Post]

    Transform --> Setup[Setup Environment]

    Setup --> Constants[Define Constants]
    Constants --> C1[ABSPATH]
    Constants --> C2[WPINC]
    Constants --> C3[WP_CONTENT_DIR]
    Constants --> C4[TEMPLATEPATH]

    Setup --> Globals[Setup Globals]
    Globals --> G1[$wp_query]
    Globals --> G2[$wp_the_query]
    Globals --> G3[$post]
    Globals --> G4[$wpdb]

    Setup --> Cache[Populate Caches]
    Cache --> CA1[Options Cache]
    Cache --> CA2[Post Meta Cache]
    Cache --> CA3[Term Cache]

    C1 --> Bootstrap
    C2 --> Bootstrap
    C3 --> Bootstrap
    C4 --> Bootstrap
    G1 --> Bootstrap
    G2 --> Bootstrap
    G3 --> Bootstrap
    G4 --> Bootstrap
    CA1 --> Bootstrap
    CA2 --> Bootstrap
    CA3 --> Bootstrap

    Bootstrap[WordPress Bootstrap] --> Theme[Load Theme]
    Theme --> Render[Render Template]
    Render --> Output[HTML Output]

    style Start fill:#e1f5ff
    style Setup fill:#fff4e6
    style Bootstrap fill:#f3e5f5
    style Output fill:#e8f5e9
```

## 4. The WordPress Loop Execution

```mermaid
sequenceDiagram
    participant Template as Template File
    participant WPQ as WP_Query
    participant Post as $post Global
    participant Tags as Template Tags

    Note over Template: single.php executing

    Template->>Template: get_header()
    Note over Template: Outputs HTML header

    Template->>WPQ: have_posts()?
    WPQ-->>Template: true (current_post: -1, post_count: 1)

    Template->>WPQ: the_post()
    activate WPQ
    WPQ->>WPQ: current_post++
    WPQ->>WPQ: $this->post = $this->posts[0]
    WPQ->>Post: setup_postdata($post)
    Note over Post: Sets $post, $id, $authordata, etc.
    deactivate WPQ

    Template->>Tags: the_title()
    Tags->>Post: Read $post->post_title
    Post-->>Tags: "My Blog Post"
    Tags-->>Template: echo "My Blog Post"

    Template->>Tags: the_content()
    Tags->>Post: Read $post->post_content
    Post-->>Tags: "<p>Content...</p>"
    Tags->>Tags: apply_filters('the_content', ...)
    Tags-->>Template: echo filtered content

    Template->>WPQ: have_posts()?
    WPQ-->>Template: false (current_post: 0, post_count: 1)

    Template->>Template: get_sidebar()
    Note over Template: Outputs sidebar HTML

    Template->>Template: get_footer()
    Note over Template: Outputs footer HTML
```

## 5. Template Hierarchy & File Loading

```mermaid
graph TD
    Start[Request: Single Post] --> CheckSingle{is_single?}

    CheckSingle -->|Yes| T1[Try: single-post.php]
    T1 -->|Not found| T2[Try: single.php]
    T2 -->|Not found| T3[Try: singular.php]
    T3 -->|Not found| T4[Try: index.php]
    T4 --> LoadTemplate[Load Template File]

    CheckSingle -->|No| CheckPage{is_page?}
    CheckPage -->|Yes| P1[Try: page-{slug}.php]
    P1 -->|Not found| P2[Try: page-{id}.php]
    P2 -->|Not found| P3[Try: page.php]
    P3 -->|Not found| P4[Try: singular.php]
    P4 -->|Not found| P5[Try: index.php]
    P5 --> LoadTemplate

    CheckPage -->|No| CheckHome{is_home?}
    CheckHome -->|Yes| H1[Try: home.php]
    H1 -->|Not found| H2[Try: index.php]
    H2 --> LoadTemplate

    CheckHome -->|No| CheckArchive{is_archive?}
    CheckArchive -->|Yes| A1[Try: archive-{post_type}.php]
    A1 -->|Not found| A2[Try: archive.php]
    A2 -->|Not found| A3[Try: index.php]
    A3 --> LoadTemplate

    LoadTemplate --> Execute[Execute Template]
    Execute --> Output[HTML Output]

    style Start fill:#e1f5ff
    style LoadTemplate fill:#fff4e6
    style Execute fill:#f3e5f5
    style Output fill:#e8f5e9
```

## 6. Template Tag Execution Flow

```mermaid
graph LR
    subgraph Template File
        T1[the_title]
        T2[the_content]
        T3[the_excerpt]
        T4[the_permalink]
        T5[has_post_thumbnail]
        T6[the_post_thumbnail]
    end

    subgraph WordPress Core
        T1 --> G1[get_the_title]
        T2 --> G2[get_the_content]
        T3 --> G3[get_the_excerpt]
        T4 --> G4[get_permalink]
        T5 --> G5[get_post_thumbnail_id]
        T6 --> G6[get_the_post_thumbnail]
    end

    subgraph Data Sources
        G1 --> P1[$post->post_title]
        G2 --> P2[$post->post_content]
        G3 --> P3[$post->post_excerpt]
        G4 --> P4[$post->ID]
        G5 --> M1[post_meta cache]
        G6 --> M2[post_meta cache]
    end

    subgraph Output
        P1 --> O1[Echo to buffer]
        P2 --> F1[apply_filters]
        F1 --> O2[Echo to buffer]
        P3 --> F2[apply_filters]
        F2 --> O3[Echo to buffer]
        P4 --> O4[Echo to buffer]
        M1 --> O5[Return boolean]
        M2 --> O6[Echo img tag]
    end

    style T1 fill:#e1f5ff
    style G1 fill:#fff4e6
    style P1 fill:#f3e5f5
    style O1 fill:#e8f5e9
```

## 7. Hook System Execution

```mermaid
sequenceDiagram
    participant Theme as Theme Code
    participant Hooks as Hook System
    participant Callbacks as Registered Callbacks

    Note over Theme: Theme initialization
    Theme->>Hooks: add_action('wp_enqueue_scripts', 'my_scripts')
    Hooks->>Hooks: Store callback in $wp_filter

    Theme->>Hooks: add_filter('the_content', 'wpautop')
    Hooks->>Hooks: Store callback in $wp_filter

    Note over Theme: Template execution
    Theme->>Hooks: do_action('wp_head')
    activate Hooks
    Hooks->>Callbacks: Execute callbacks for 'wp_head'
    activate Callbacks
    Callbacks->>Callbacks: wp_print_styles()
    Callbacks->>Callbacks: wp_print_scripts()
    Callbacks-->>Hooks: Done
    deactivate Callbacks
    deactivate Hooks

    Theme->>Hooks: apply_filters('the_content', $content)
    activate Hooks
    Hooks->>Callbacks: Pass $content through filters
    activate Callbacks
    Callbacks->>Callbacks: wpautop($content)
    Callbacks->>Callbacks: wptexturize($content)
    Callbacks-->>Hooks: Modified $content
    deactivate Callbacks
    Hooks-->>Theme: Return filtered $content
    deactivate Hooks
```

## 8. Database Isolation Architecture

```mermaid
graph TD
    Theme[WordPress Theme] --> API[WordPress API Functions]

    API --> Query[Database Query Functions]
    Query --> Q1[get_posts]
    Query --> Q2[get_option]
    Query --> Q3[get_post_meta]

    Q1 --> Cache1{Check Cache}
    Q2 --> Cache2{Check Cache}
    Q3 --> Cache3{Check Cache}

    Cache1 -->|Hit| Return1[Return from Cache]
    Cache2 -->|Hit| Return2[Return from Cache]
    Cache3 -->|Hit| Return3[Return from Cache]

    Cache1 -->|Miss| WPDB1[wpdb->query]
    Cache2 -->|Miss| WPDB2[wpdb->query]
    Cache3 -->|Miss| WPDB3[wpdb->query]

    WPDB1 --> MockDB[Mock wpdb Class]
    WPDB2 --> MockDB
    WPDB3 --> MockDB

    MockDB --> Empty[Return Empty Result]

    Return1 --> Output[Data Available to Theme]
    Return2 --> Output
    Return3 --> Output
    Empty --> Output

    style Cache1 fill:#e8f5e9
    style Cache2 fill:#e8f5e9
    style Cache3 fill:#e8f5e9
    style MockDB fill:#ffebee
    style Return1 fill:#c8e6c9
    style Return2 fill:#c8e6c9
    style Return3 fill:#c8e6c9
```

## 9. Complete Request Timeline

```mermaid
gantt
    title WP4BD Request Processing Timeline
    dateFormat X
    axisFormat %L ms

    section Backdrop
    Route Request           :0, 5
    Load Node              :5, 20
    Trigger WP4BD          :20, 5

    section WP4BD Bridge
    Transform Data         :25, 15
    Setup Environment      :40, 20
    Populate Caches        :60, 10
    Mock Database          :70, 5

    section WordPress
    Bootstrap Core         :75, 30
    Load Theme Functions   :105, 10
    Determine Template     :115, 5
    Start Output Buffer    :120, 1

    section Template Execution
    get_header()           :121, 15
    wp_head()              :136, 20
    The Loop               :156, 40
    Template Tags          :196, 30
    get_sidebar()          :226, 10
    get_footer()           :236, 10
    wp_footer()            :246, 15

    section Output
    Capture Buffer         :261, 5
    Post-process           :266, 5
    Return to Backdrop     :271, 5
    Send Response          :276, 10
```

## 10. WP_Query State Diagram

```mermaid
stateDiagram-v2
    [*] --> Created: new WP_Query()

    Created --> Initialized: Populate properties
    Initialized --> BeforeLoop: current_post = -1

    BeforeLoop --> CheckPosts: have_posts()

    CheckPosts --> InLoop: Returns true
    CheckPosts --> AfterLoop: Returns false

    InLoop --> ProcessPost: the_post()
    ProcessPost --> SetupData: setup_postdata()
    SetupData --> InLoop: current_post++

    InLoop --> CheckPosts: Loop again

    AfterLoop --> Reset: wp_reset_postdata()
    Reset --> BeforeLoop: Restore original query

    AfterLoop --> [*]: Template done

    note right of Created
        posts = []
        post_count = 0
        is_single = false
    end note

    note right of Initialized
        posts = [WP_Post]
        post_count = 1
        is_single = true
    end note

    note right of InLoop
        current_post = 0
        in_the_loop = true
        $post global set
    end note
```

## 11. Cache Population Strategy

```mermaid
graph TD
    Start[Backdrop Node Data] --> Transform[Transform to WP Data]

    Transform --> Options[Build Options Array]
    Transform --> Meta[Build Post Meta]
    Transform --> Terms[Build Terms/Taxonomy]
    Transform --> User[Build Author Data]

    Options --> O1[siteurl]
    Options --> O2[blogname]
    Options --> O3[stylesheet]
    Options --> O4[template]
    Options --> OCache[wp_cache_add options]

    Meta --> M1[_thumbnail_id]
    Meta --> M2[custom_fields]
    Meta --> MCache[wp_cache_add post_meta]

    Terms --> T1[categories]
    Terms --> T2[tags]
    Terms --> TCache[wp_cache_add terms]

    User --> U1[author_id]
    User --> U2[author_name]
    User --> UCache[wp_cache_add users]

    OCache --> Ready[WordPress Ready to Render]
    MCache --> Ready
    TCache --> Ready
    UCache --> Ready

    Ready --> Theme[Load Theme]

    style Start fill:#e1f5ff
    style Transform fill:#fff4e6
    style Ready fill:#e8f5e9
    style Theme fill:#f3e5f5
```

## 12. Error Handling Flow

```mermaid
graph TD
    Start[Template Execution] --> Try{Try Block}

    Try -->|Success| Render[Render HTML]
    Render --> Output[Return HTML]

    Try -->|Error| Catch[Catch Exception]

    Catch --> LogError[Log Error]
    LogError --> CheckType{Error Type}

    CheckType -->|Fatal| Fallback1[Use Backdrop Fallback]
    CheckType -->|Warning| Fallback2[Render with Errors Hidden]
    CheckType -->|Notice| Continue[Continue Rendering]

    Fallback1 --> BackdropTheme[Render with Backdrop Theme]
    Fallback2 --> PartialOutput[Return Partial Output]
    Continue --> Output

    BackdropTheme --> Output
    PartialOutput --> Output

    Output --> End[Send to Browser]

    style Start fill:#e1f5ff
    style Catch fill:#ffebee
    style Output fill:#e8f5e9
```

## Usage Notes

These Mermaid diagrams can be viewed in:
- GitHub (renders Mermaid natively)
- VS Code (with Mermaid extension)
- Any Markdown viewer with Mermaid support
- Online at https://mermaid.live/

To edit/customize, copy any diagram code block to the Mermaid Live Editor.
