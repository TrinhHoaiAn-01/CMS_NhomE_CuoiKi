<?php /** WP Job Manager archive using shared project cards. */ get_header(); ?>
<main id="main-content" class="jsc-jobs-archive"><section class="jsc-section"><div class="jsc-container">
<h1 class="jsc-title">All Jobs</h1>
<?php $jobs = jsc_jobs( array( 'posts_per_page' => 12 ) ); jsc_cards( $jobs, 'job' ); ?>
</div></section></main><?php get_footer(); ?>
