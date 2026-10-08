<?php
/** JobScout project Home. Content is sourced from WordPress and WP Job Manager. */
get_header();
?>
<main id="main-content" class="jsc-home">
<?php while ( have_posts() ) : the_post(); ?>
<?php if ( trim( get_the_content() ) ) : ?><div class="jsc-container jsc-home-intro"><?php the_content(); ?></div><?php endif; ?>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
