<?php /** Shared project header. */ ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'jsc-site' ); ?>>
<?php wp_body_open(); ?>
<a class="jsc-skip" href="#main-content">Skip to content</a>
<header class="jsc-header"><div class="jsc-header-inner">
<a class="jsc-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( jsc_setting( 'brand', 'Plan Do See Global Inc.' ) . ' — Home' ); ?>">
<?php if ( has_custom_logo() ) : ?>
<?php echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'full' ); ?>
<?php else : ?>
<span><?php echo esc_html( jsc_setting( 'brand', 'Plan Do See Global Inc.' ) ); ?></span>
<small><?php echo esc_html( jsc_setting( 'tagline', 'RECRUITING' ) ); ?></small>
<?php endif; ?>
</a>
<button class="jsc-menu-toggle" type="button" aria-expanded="false" aria-controls="jsc-navigation">Menu <span aria-hidden="true">☰</span></button>
<nav id="jsc-navigation" class="jsc-navigation" aria-label="Primary navigation">
<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'jsc-menu', 'fallback_cb' => 'jsc_fallback_menu' ) ); ?>
<a class="jsc-button jsc-submit" href="<?php echo esc_url( jsc_url( 'submit-job' ) ); ?>">Submit Job</a>
</nav></div></header>
