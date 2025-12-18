<?php get_header(); ?>

<div class="site-content">
    <main class="content-area">
        
        <?php if ( have_posts() ) : ?>
            
            <?php while ( have_posts() ) : the_post(); ?>
                
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="post-thumbnail">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail( 'large' ); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <h2 class="post-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h2>
                    
                    <div class="post-meta">
                        Posted on <?php echo get_the_date(); ?> by <?php the_author(); ?>
                    </div>
                    
                    <div class="post-content">
                        <?php the_content(); ?>
                    </div>
                    
                </article>
                
            <?php endwhile; ?>
            
            <div class="pagination">
                <?php 
                the_posts_pagination( array(
                    'prev_text' => '&laquo; Previous',
                    'next_text' => 'Next &raquo;',
                ) );
                ?>
            </div>
            
        <?php else : ?>
            
            <p>No posts found.</p>
            
        <?php endif; ?>
        
    </main>
    
    <?php get_sidebar(); ?>
    
</div>

<?php get_footer(); ?>
