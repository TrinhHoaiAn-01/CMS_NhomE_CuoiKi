<?php /** Shared newsletter and footer. */ ?>
<?php jsc_render_area( 'shared' ); ?>
<footer class="jsc-footer">
<div class="jsc-footer-main">
<a class="jsc-footer-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( jsc_setting( 'brand', 'Plan Do See Global Inc.' ) ); ?></a>
<nav aria-label="Footer navigation"><?php wp_nav_menu( array( 'theme_location' => 'secondary', 'container' => false, 'menu_class' => 'jsc-footer-menu', 'fallback_cb' => 'jsc_footer_menu' ) ); ?></nav>
<div class="jsc-socials" aria-label="Social channels">
<?php foreach ( array( 'facebook' => 'Facebook', 'google' => 'Google', 'line' => 'LINE', 'twitter' => 'Twitter' ) as $key => $label ) : $destination = jsc_setting( $key . '_url' ); ?>
<?php if ( $destination ) : ?><a class="jsc-social <?php echo esc_attr( $key ); ?>" href="<?php echo esc_url( $destination ); ?>" aria-label="<?php echo esc_attr( $label ); ?>"><?php echo jsc_icon( $key ); ?></a>
<?php else : ?><span class="jsc-social <?php echo esc_attr( $key ); ?>" role="img" aria-label="<?php echo esc_attr( $label . ' — address not supplied' ); ?>" title="<?php echo esc_attr( $label . ': address not supplied' ); ?>"><?php echo jsc_icon( $key ); ?></span><?php endif; ?>
<?php endforeach; ?>
</div>
</div>
<div class="jsc-copyright">© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( jsc_setting( 'brand', 'Plan Do See Global Inc.' ) ); ?></div>
</footer>
<?php wp_footer(); ?>
</body></html>
