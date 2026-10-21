<?php /** Native editorial detail page. */ get_header(); ?>
<main id="main-content" class="jsc-news-detail jsc-detail"><div class="jsc-container">
<?php while ( have_posts() ) : the_post(); ?>
<?php jsc_breadcrumbs( 'news', 'All News', 'News Detail' ); get_template_part( 'template-parts/project-detail-summary' ); ?>
<article class="jsc-prose jsc-news-prose"><?php the_content(); ?></article>
<?php $current_story = get_the_ID(); endwhile; ?>
<section class="jsc-related-news" aria-labelledby="related-news-title"><h2 class="jsc-title" id="related-news-title">Newest Blog Entries</h2><?php jsc_cards( jsc_news( array( 'posts_per_page' => 6, 'post__not_in' => array( $current_story ) ) ), 'news' ); ?></section>
</div></main><?php get_footer(); ?>
