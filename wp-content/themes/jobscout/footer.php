<?php /** Shared newsletter and footer. */ ?>
<section class="jsc-newsletter" aria-labelledby="newsletter-heading">
<div class="jsc-newsletter-inner">
<h2 id="newsletter-heading">Subscribe To<br>Our Newsletter</h2>
<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="jsc-newsletter-form">
<input type="hidden" name="action" value="jsc_subscribe">
<?php wp_nonce_field( 'jsc_subscribe', 'jsc_nonce' ); ?>
<label class="jsc-email-field"><span class="jsc-sr-only">Your email address</span><?php echo jsc_icon( 'mail' ); ?><input type="email" name="email" placeholder="Input your email address" required maxlength="254" autocomplete="email" aria-describedby="newsletter-privacy"></label>
<label class="jsc-trap" aria-hidden="true">Leave blank<input name="website" tabindex="-1" autocomplete="off"></label>
<button type="submit">Subscribe</button>
<p class="jsc-sr-only" id="newsletter-privacy">Your email will be stored securely in WordPress for newsletter sign-ups.</p>
</form>
</div>
<?php if ( isset( $_GET['subscription'] ) ) : ?>
<p class="jsc-form-message" role="status"><?php echo 'saved' === sanitize_key( wp_unslash( $_GET['subscription'] ) ) ? 'Thank you. Your newsletter sign-up has been saved.' : 'Please enter a valid email address and try again.'; ?></p>
<?php endif; ?>
</section>
<footer class="jsc-footer">
<div class="jsc-footer-main">
<a class="jsc-footer-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( jsc_setting( 'brand', 'Plan Do See Global Inc.' ) ); ?></a>
<nav aria-label="Footer navigation"><ul class="jsc-footer-menu">
<li><a href="<?php echo esc_url( jsc_url( 'jobs' ) ); ?>">Jobs</a></li>
<li><a href="<?php echo esc_url( jsc_url( 'about' ) . '#properties' ); ?>">Companies</a></li>
<li><a href="<?php echo esc_url( jsc_url( 'news' ) ); ?>">Blog</a></li>
<li><a href="<?php echo esc_url( jsc_url( 'about' ) ); ?>">About</a></li>
<li><a href="<?php echo esc_url( jsc_url( 'contact' ) ); ?>">Contact</a></li>
</ul></nav>
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
