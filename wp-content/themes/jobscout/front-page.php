<?php
/** JobScout project Home. Content is sourced from WordPress and WP Job Manager. */
get_header();
?>
<main id="main-content" class="jsc-home">
<section class="jsc-home-hero" aria-labelledby="home-title"<?php echo jsc_background_style( 'home_hero_image' ); ?>>
    <div class="jsc-hero-copy">
        <h1 id="home-title"><?php echo esc_html( jsc_setting( 'home_heading', 'Find Your Dream Jobs' ) ); ?></h1>
        <p><?php echo esc_html( jsc_setting( 'home_description', 'The secret behind our company is simple: to always put ourselves in the other person’s shoes—employee, guest or customer. This allows us to see the world through their eyes, anticipate their needs and better understand their feelings.' ) ); ?></p>
        <?php get_template_part( 'template-parts/project-job-search' ); ?>
    </div>
</section>
<section class="jsc-top-jobs jsc-section" aria-labelledby="top-jobs-title">
    <div class="jsc-container"><h2 class="jsc-title" id="top-jobs-title">Top Jobs</h2>
    <?php jsc_cards( jsc_jobs(), 'job' ); ?>
    <div class="jsc-actions"><a class="jsc-button outline" href="<?php echo esc_url( jsc_url( 'jobs' ) ); ?>">View More Jobs</a></div>
    </div>
</section>
<section class="jsc-career" aria-labelledby="career-title"<?php echo jsc_background_style( 'career_image' ); ?>>
    <div class="jsc-career-copy"><h2 class="jsc-title" id="career-title">Career With Us</h2>
        <?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
        <a class="jsc-button jsc-career-button" href="<?php echo esc_url( jsc_url( 'about' ) ); ?>">More About Us</a>
    </div>
</section>
<section class="jsc-home-news jsc-section" aria-labelledby="home-news-title">
    <div class="jsc-container"><h2 class="jsc-title" id="home-news-title">Newest Blog Entries</h2>
        <?php jsc_cards( jsc_news(), 'news' ); ?>
    </div>
</section>
</main>
<?php get_footer(); ?>
