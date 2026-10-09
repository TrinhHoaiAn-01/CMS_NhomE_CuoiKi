<?php /** About page, with editable WordPress content. */ get_header(); ?>
<main id="main-content" class="jsc-about">
<section class="jsc-page-hero jsc-about-hero"><h1>Share “Omotenashi”<br>With The World</h1></section>
<section class="jsc-about-vision jsc-section"><div class="jsc-container">
<h2 class="jsc-title">About Us</h2>
<?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
</div></section>
</main><?php get_footer(); ?>
