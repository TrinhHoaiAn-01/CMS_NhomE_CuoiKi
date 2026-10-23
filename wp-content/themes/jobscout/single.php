<?php /** Native record with configurable widget sections. */ get_header(); ?>
<main id="main-content" class="jsc-news-detail jsc-detail"><div class="jsc-container">
<?php while ( have_posts() ) : the_post(); jsc_render_area( 'news-detail' ); endwhile; ?>
</div></main><?php get_footer(); ?>
