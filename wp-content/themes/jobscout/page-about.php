<?php /** About page, with editable WordPress content. */ get_header(); ?>
<main id="main-content" class="jsc-about">
<section class="jsc-page-hero jsc-about-hero"<?php echo jsc_background_style( 'about_hero_image' ); ?>><h1>Share “Omotenashi”<br>With The World</h1></section>
<section class="jsc-about-vision jsc-section"><div class="jsc-container">
<h2 class="jsc-title">About Us</h2>
<div class="jsc-about-vision-grid"><figure><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } else { ?><img src="<?php echo esc_url( jsc_asset( 'torii.jpg' ) ); ?>" alt="Red torii gate standing in the water in Japan" width="480" height="400"><?php } ?></figure>
<div class="jsc-about-principles"><?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?></div></div>
</div></section>
<section class="jsc-about-properties" id="properties"><div class="jsc-container"><h2>Hotels, restaurants, banquets/weddings management</h2>
<?php echo wp_kses_post( wpautop( get_post_meta( get_queried_object_id(), 'jsc_properties', true ) ) ); ?></div></section>
<section class="jsc-about-profile jsc-section"><div class="jsc-container jsc-profile-grid"><dl>
<?php foreach ( array( 'founded' => 'Established since', 'office' => 'Head Office', 'capital' => 'Capital', 'ceo' => 'CEO', 'employees' => 'Number of Employees' ) as $key => $label ) : ?><div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( get_post_meta( get_queried_object_id(), 'jsc_' . $key, true ) ); ?></dd></div><?php endforeach; ?>
</dl><img src="<?php echo esc_url( ( get_post_meta( get_queried_object_id(), 'jsc_profile_image', true ) ?: jsc_asset( 'tokyo.jpg' ) ) ); ?>" alt="Tokyo Tower above the city at sunset" width="480" height="400" loading="lazy"></div></section>
</main><?php get_footer(); ?>
