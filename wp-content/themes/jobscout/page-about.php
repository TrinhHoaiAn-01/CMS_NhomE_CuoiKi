<?php /** About page, with editable WordPress content. */ get_header(); ?>
<main id="main-content" class="jsc-about">
<section class="jsc-page-hero jsc-about-hero"><h1>Share “Omotenashi”<br>With The World</h1></section>
<section class="jsc-about-vision jsc-section"><div class="jsc-container">
<h2 class="jsc-title">About Us</h2>
<div class="jsc-about-vision-grid"><figure><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } else { ?><img src="<?php echo esc_url( jsc_asset( 'torii.jpg' ) ); ?>" alt="Red torii gate standing in the water in Japan" width="480" height="400"><?php } ?></figure>
<div class="jsc-about-principles"><?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?></div></div>
</div></section>
</main><?php get_footer(); ?>
