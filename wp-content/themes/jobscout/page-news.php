<?php /** Native WordPress news archive. */ get_header(); ?>
<main id="main-content" class="jsc-news-archive">
<section class="jsc-page-hero jsc-news-hero"><h1>PDS News</h1></section>
<section class="jsc-section"><div class="jsc-container">
<h2 class="jsc-title">Newest Blog Entries</h2>
<?php while ( have_posts() ) : the_post(); if ( trim( get_the_content() ) ) { the_content(); } endwhile; ?>
<?php $news_page = isset( $_GET['news_page'] ) ? max( 1, absint( $_GET['news_page'] ) ) : 1; $stories = jsc_news( array( 'posts_per_page' => 8, 'paged' => $news_page ) ); jsc_cards( $stories, 'news' ); ?>
<?php if ( $stories->max_num_pages > 1 ) : ?><nav class="jsc-pagination" aria-label="News pages"><?php echo wp_kses_post( paginate_links( array( 'base' => add_query_arg( 'news_page', '%#%', jsc_url( 'news' ) ), 'format' => '', 'current' => $news_page, 'total' => $stories->max_num_pages, 'prev_text' => 'Previous', 'next_text' => 'Next' ) ) ); ?></nav><?php endif; ?>
</div></section></main><?php get_footer(); ?>
