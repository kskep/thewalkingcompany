<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Awesome Theme</title>
    <?php wp_head(); ?>
</head>
<body>

    <div style="padding: 40px;">
        <h1><?php bloginfo('name'); ?></h1>
        <p>My theme, deployed via Git, is working!</p>

        <hr>

        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <div>
                    <?php the_content(); ?>
                </div>
            <?php endwhile; ?>
        <?php else : ?>
            <p>No posts found.</p>
        <?php endif; ?>
    </div>

    <?php wp_footer(); ?>
</body>
</html>