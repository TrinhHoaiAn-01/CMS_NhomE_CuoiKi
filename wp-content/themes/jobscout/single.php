<?php /** Native editorial detail page. */ get_header(); ?>
<main id="main-content" class="jsc-news-detail jsc-detail"><div class="jsc-container">
<?php while ( have_posts() ) : the_post(); ?>
<?php jsc_breadcrumbs( 'news', 'All News', 'News Detail' ); get_template_part( 'template-parts/project-detail-summary' ); ?>
<article class="jsc-prose jsc-news-prose"><?php the_content(); ?></article>
<?php endwhile; ?>
</div></main><?php get_footer(); ?>
