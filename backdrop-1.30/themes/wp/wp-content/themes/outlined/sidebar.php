<aside class="sidebar">
    <h3>Sidebar</h3>
    <p>This is the sidebar area. Widgets would go here.</p>
    
    <h4>Recent Posts</h4>
    <ul>
        <?php
        $recent_posts = wp_get_recent_posts( array( 'numberposts' => 5 ) );
        foreach( $recent_posts as $recent ) {
            echo '<li><a href="' . get_permalink( $recent['ID'] ) . '">' . $recent['post_title'] . '</a></li>';
        }
        ?>
    </ul>
    
    <h4>Archives</h4>
    <ul>
        <?php wp_get_archives( array( 'type' => 'monthly', 'limit' => 12 ) ); ?>
    </ul>
</aside>
