<?php defined( 'ABSPATH' ) || exit; $newsletter_id = $args['_uid'] ?? wp_unique_id( 'jsc-newsletter-' ); ?>
<section class="jsc-newsletter" aria-labelledby="<?php echo esc_attr( $newsletter_id ); ?>-heading">
<div class="jsc-newsletter-inner">
<h2 id="<?php echo esc_attr( $newsletter_id ); ?>-heading"><?php echo esc_html( $args['title'] ?? '' ); ?></h2>
<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="jsc-newsletter-form">
<input type="hidden" name="action" value="jsc_subscribe">
<?php wp_nonce_field( 'jsc_subscribe', 'jsc_nonce' ); ?>
<label class="jsc-email-field"><span class="jsc-sr-only">Your email address</span><?php echo jsc_icon( 'mail' ); ?><input type="email" name="email" placeholder="<?php echo esc_attr( $args['placeholder'] ?? '' ); ?>" required maxlength="254" autocomplete="email" aria-describedby="<?php echo esc_attr( $newsletter_id ); ?>-privacy"></label>
<label class="jsc-trap" aria-hidden="true">Leave blank<input name="website" tabindex="-1" autocomplete="off"></label>
<button type="submit"><?php echo esc_html( $args['button'] ?? '' ); ?></button>
<p class="jsc-sr-only" id="<?php echo esc_attr( $newsletter_id ); ?>-privacy"><?php echo esc_html( $args['privacy'] ?? '' ); ?></p>
</form>
</div>
<?php if ( isset( $_GET['subscription'] ) ) : ?>
<?php
$subscription_messages = array(
    'saved' => 'Thank you for subscribing! If this is your first sign-up, check your inbox for a confirmation email.',
    'mail_pending' => 'You are subscribed. Your confirmation email is delayed; we will retry shortly.',
    'busy' => 'Your sign-up is being processed. Please try again shortly.',
    'limited' => 'Too many sign-up attempts. Please try again in an hour.',
);
$subscription_result = is_string( $_GET['subscription'] ) ? sanitize_key( wp_unslash( $_GET['subscription'] ) ) : '';
?>
<p class="jsc-form-message" role="status"><?php echo esc_html( $subscription_messages[ $subscription_result ] ?? 'Please enter a valid email address and try again.' ); ?></p>
<?php endif; ?>
</section>
