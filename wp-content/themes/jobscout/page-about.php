<?php /** About page, with editable WordPress content. */ get_header(); ?>
<main id="main-content" class="jsc-about">
<section class="jsc-about-vision jsc-section"><div class="jsc-container">
<h1 class="jsc-title">About Us</h1>
<?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
</div></section>
</main><?php get_footer(); ?>
