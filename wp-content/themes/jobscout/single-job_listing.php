<?php /** Native WP Job Manager detail route. */ get_header(); ?>
<main id="main-content" class="jsc-job-detail jsc-detail"><div class="jsc-container">
<?php while ( have_posts() ) : the_post(); ?>
<?php jsc_breadcrumbs( 'jobs', 'All Jobs', 'Job Detail' ); get_template_part( 'template-parts/project-detail-summary' ); ?>
<article class="jsc-prose jsc-job-description">
<?php if ( function_exists( 'wpjm_the_job_description' ) ) { wpjm_the_job_description(); } else { echo wp_kses_post( wpautop( get_the_content() ) ); } ?>
</article>
<?php endwhile; ?>
</div></main><?php get_footer(); ?>
