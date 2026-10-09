<?php
/** JobScout project Home. Content is sourced from WordPress and WP Job Manager. */
get_header();
?>
<main id="main-content" class="jsc-home">
<section class="jsc-home-hero" aria-labelledby="home-title">
    <div class="jsc-hero-copy">
        <h1 id="home-title"><?php echo esc_html( jsc_setting( 'home_heading', 'Find Your Dream Jobs' ) ); ?></h1>
        <p><?php echo esc_html( jsc_setting( 'home_description', 'The secret behind our company is simple: to always put ourselves in the other person’s shoes—employee, guest or customer. This allows us to see the world through their eyes, anticipate their needs and better understand their feelings.' ) ); ?></p>
        <?php get_template_part( 'template-parts/project-job-search' ); ?>
    </div>
</section>
<?php while ( have_posts() ) : the_post(); ?>
<?php if ( trim( get_the_content() ) ) : ?><div class="jsc-container jsc-home-intro"><?php the_content(); ?></div><?php endif; ?>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
