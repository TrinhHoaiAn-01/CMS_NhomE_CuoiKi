<?php /** Native editorial category archive with shared styling. */ get_header(); ?>
<main id="main-content" class="jsc-news-archive"><section class="jsc-page-hero jsc-news-hero"><h1><?php single_cat_title(); ?></h1></section><section class="jsc-section"><div class="jsc-container">
<?php $description = category_description(); if ( $description ) { echo wp_kses_post( $description ); } global $wp_query; jsc_cards( $wp_query, 'news' ); ?>
<nav class="jsc-pagination" aria-label="Category pages"><?php echo wp_kses_post( paginate_links() ); ?></nav>
</div></section></main><?php get_footer(); ?>
