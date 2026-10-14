<?php /** WP Job Manager archive using shared project cards. */ get_header(); ?>
<main id="main-content" class="jsc-jobs-archive"><section class="jsc-page-hero jsc-jobs-hero"><h1>Career With Us</h1></section><section class="jsc-section"><div class="jsc-container">
<h2 class="jsc-title">All Jobs</h2>
<?php
$keywords = isset( $_GET['search_keywords'] ) ? sanitize_text_field( wp_unslash( $_GET['search_keywords'] ) ) : '';
$location = isset( $_GET['search_location'] ) ? sanitize_text_field( wp_unslash( $_GET['search_location'] ) ) : '';
$jobs = function_exists( 'get_job_listings' ) ? get_job_listings( array( 'posts_per_page' => 12, 'search_keywords' => $keywords, 'search_location' => $location, 'filled' => false, 'post_status' => 'publish', 'orderby' => array( 'date' => 'DESC', 'menu_order' => 'ASC', 'ID' => 'ASC' ) ) ) : jsc_jobs( array( 'posts_per_page' => 12 ) );
if ( $keywords || $location ) : ?>
<details class="jsc-filter-panel" open><summary>Refine your search</summary><?php get_template_part( 'template-parts/project-job-search' ); ?></details>
<p class="jsc-results-count" role="status"><?php echo esc_html( $jobs->found_posts ); ?> jobs found<?php if ( $keywords ) { echo ' for “' . esc_html( $keywords ) . '”'; } ?><?php if ( $location ) { echo ' in ' . esc_html( $location ); } ?>. <a href="<?php echo esc_url( jsc_url( 'jobs' ) ); ?>">Clear search</a></p>
<?php endif; jsc_cards( $jobs, 'job' ); ?>
</div></section></main><?php get_footer(); ?>
