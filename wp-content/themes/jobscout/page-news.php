<?php /** Native WordPress news archive. */ get_header(); ?>
<main id="main-content" class="jsc-news-archive">
<section class="jsc-page-hero jsc-news-hero"><h1>PDS News</h1></section>
<section class="jsc-section"><div class="jsc-container">
<h2 class="jsc-title">Newest Blog Entries</h2>
<?php while ( have_posts() ) : the_post(); if ( trim( get_the_content() ) ) { the_content(); } endwhile; ?>
<?php $stories = jsc_news(); jsc_cards( $stories, 'news' ); ?>
</div></section></main><?php get_footer(); ?>
