<?php /** WP Job Manager archive using shared project cards. */ get_header(); ?>
<main id="main-content" class="jsc-jobs-archive"><section class="jsc-page-hero jsc-jobs-hero"><h1>Career With Us</h1></section><section class="jsc-section" id="jobs-results"><div class="jsc-container">
<div class="jsc-archive-toolbar"><h2 class="jsc-title">All Jobs</h2>
<form method="get" action="<?php echo esc_url( jsc_url( 'jobs' ) ); ?>" class="jsc-sort-form">
<?php foreach ( array( 'search_keywords', 'search_location' ) as $key ) : if ( isset( $_GET[ $key ] ) ) : ?><input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) ); ?>"><?php endif; endforeach; ?>
<label class="jsc-sr-only" for="job-order">Sort jobs</label><select id="job-order" name="job_order"><option value="latest">Latest Jobs</option><option value="oldest" <?php selected( isset( $_GET['job_order'] ) ? sanitize_key( $_GET['job_order'] ) : '', 'oldest' ); ?>>Oldest Jobs</option><option value="title" <?php selected( isset( $_GET['job_order'] ) ? sanitize_key( $_GET['job_order'] ) : '', 'title' ); ?>>Job Title</option></select><button type="submit" class="jsc-sort-submit">Sort</button>
</form></div>
<?php
$keywords = isset( $_GET['search_keywords'] ) ? sanitize_text_field( wp_unslash( $_GET['search_keywords'] ) ) : '';
$location = isset( $_GET['search_location'] ) ? sanitize_text_field( wp_unslash( $_GET['search_location'] ) ) : '';
$job_order = isset( $_GET['job_order'] ) ? sanitize_key( $_GET['job_order'] ) : 'latest';
$ordering = 'title' === $job_order ? array( 'title' => 'ASC', 'ID' => 'ASC' ) : array( 'date' => 'oldest' === $job_order ? 'ASC' : 'DESC', 'menu_order' => 'ASC', 'ID' => 'ASC' );
$job_page = isset( $_GET['job_page'] ) ? min( 50, max( 1, absint( $_GET['job_page'] ) ) ) : 1;
$job_limit = 12 * $job_page;
$jobs = function_exists( 'get_job_listings' ) ? get_job_listings( array( 'posts_per_page' => $job_limit, 'search_keywords' => $keywords, 'search_location' => $location, 'filled' => false, 'post_status' => 'publish', 'orderby' => $ordering ) ) : jsc_jobs( array( 'posts_per_page' => 12 ) );
if ( $keywords || $location ) : ?>
<details class="jsc-filter-panel" open><summary>Refine your search</summary><?php get_template_part( 'template-parts/project-job-search' ); ?></details>
<p class="jsc-results-count" role="status"><?php echo esc_html( $jobs->found_posts ); ?> jobs found<?php if ( $keywords ) { echo ' for “' . esc_html( $keywords ) . '”'; } ?><?php if ( $location ) { echo ' in ' . esc_html( $location ); } ?>. <a href="<?php echo esc_url( jsc_url( 'jobs' ) ); ?>">Clear search</a></p>
<?php endif; if ( $jobs->have_posts() ) { jsc_cards( $jobs, 'job' ); } else { ?><div class="jsc-empty"><h3>No jobs match your search</h3><p>Try another keyword or location, or <a href="<?php echo esc_url( jsc_url( 'jobs' ) ); ?>">browse all jobs</a>.</p></div><?php } ?>
<?php if ( $jobs->found_posts > $job_limit ) : ?><div class="jsc-actions"><a class="jsc-button outline jsc-load-more" href="<?php echo esc_url( add_query_arg( array( 'job_page' => $job_page + 1, 'job_order' => $job_order, 'search_keywords' => $keywords, 'search_location' => $location ), jsc_url( 'jobs' ) ) . '#jobs-results' ); ?>">Load More Jobs</a></div><?php endif; ?>
</div></section></main><?php get_footer(); ?>
