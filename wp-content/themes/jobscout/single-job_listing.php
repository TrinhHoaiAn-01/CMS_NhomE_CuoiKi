<?php /** Native WP Job Manager detail route. */ get_header(); ?>
<main id="main-content" class="jsc-job-detail jsc-detail"><div class="jsc-container">
<?php while ( have_posts() ) : the_post(); ?>
<article class="jsc-prose jsc-job-description"><h1><?php the_title(); ?></h1>
<?php if ( function_exists( 'wpjm_the_job_description' ) ) { wpjm_the_job_description(); } else { echo wp_kses_post( wpautop( get_the_content() ) ); } ?>
</article>
<?php endwhile; ?>
</div></main><?php get_footer(); ?>
