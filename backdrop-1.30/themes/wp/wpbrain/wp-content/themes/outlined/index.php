<?php get_header(); ?>

<div class="site-content">
    <div class="content-area">
        <main class="site-main">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <article class="post">
                        <header class="entry-header">
                            <h2 class="entry-title"><?php the_title(); ?></h2>
                        </header>
                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else : ?>
                <p>No posts found.</p>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php get_footer(); ?>
