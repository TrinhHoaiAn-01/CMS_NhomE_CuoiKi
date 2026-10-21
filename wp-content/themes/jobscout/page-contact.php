<?php /** Editable contact page. */ get_header(); ?>
<main id="main-content" class="jsc-contact"><section class="jsc-page-hero jsc-contact-hero"><h1>Contact Us</h1></section><section class="jsc-contact-address"><div class="jsc-container"><h2>Our Headquarters Address</h2><?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?></div></section><section class="jsc-contact-columns"><div class="jsc-container jsc-contact-grid">
<section class="jsc-contact-employers" aria-labelledby="employers-title"><h2 id="employers-title">For Employers</h2><p>Call our Sales Hotline</p>
<div class="jsc-contact-method"><h3>Ho Chi Minh</h3><?php jsc_contact_phone( 'phone_hcm' ); ?></div>
<div class="jsc-contact-method"><h3>Ha Noi</h3><?php jsc_contact_phone( 'phone_hanoi' ); ?></div>
<p class="jsc-callback"><a href="<?php echo esc_url( add_query_arg( 'enquiry', 'employer', jsc_url( 'contact' ) ) . '#apply' ); ?>">Request a call from one of our<br>Customer Love Account Managers</a><br>We’re ready to help you grow!</p>
</section>
</div></section></main>
<?php get_footer(); ?>
