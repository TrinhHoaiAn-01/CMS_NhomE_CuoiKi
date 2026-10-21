<?php /** Editable contact page. */ get_header(); ?>
<main id="main-content" class="jsc-contact"><section class="jsc-section"><div class="jsc-container"><h1 class="jsc-title">Contact Us</h1><?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?></div></section></main>
<?php get_footer(); ?>
