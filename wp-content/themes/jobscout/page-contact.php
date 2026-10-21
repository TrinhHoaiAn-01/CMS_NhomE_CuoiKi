<?php /** Editable contact page. */ get_header(); ?>
<main id="main-content" class="jsc-contact"><section class="jsc-page-hero jsc-contact-hero"><h1>Contact Us</h1></section><section class="jsc-contact-address"><div class="jsc-container"><h2>Our Headquarters Address</h2><?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?></div></section></main>
<?php get_footer(); ?>
