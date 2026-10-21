<?php /** Native editorial detail page. */ get_header(); ?>
<main id="main-content" class="jsc-news-detail jsc-detail"><div class="jsc-container">
<?php while ( have_posts() ) : the_post(); ?>
<article class="jsc-prose jsc-news-prose"><h1><?php the_title(); ?></h1><?php the_content(); ?></article>
<?php endwhile; ?>
</div></main><?php get_footer(); ?>
