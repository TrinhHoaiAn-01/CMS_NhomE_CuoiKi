<?php /** Generic native WordPress page, including WP Job Manager forms. */ get_header(); ?>
<main id="main-content" class="jsc-standard-page jsc-section"><div class="jsc-container">
<?php while ( have_posts() ) : the_post(); ?><article><h1 class="jsc-title"><?php the_title(); ?></h1><div class="jsc-standard-content"><?php the_content(); ?></div></article><?php endwhile; ?>
</div></main><?php get_footer(); ?>
