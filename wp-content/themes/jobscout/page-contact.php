<?php /** Editable contact page. */ get_header(); ?>
<main id="main-content" class="jsc-contact"><section class="jsc-page-hero jsc-contact-hero"><h1>Contact Us</h1></section><section class="jsc-contact-address"><div class="jsc-container"><h2>Our Headquarters Address</h2><?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?></div></section><section class="jsc-contact-columns"><div class="jsc-container jsc-contact-grid">
<section class="jsc-contact-employers" aria-labelledby="employers-title"><h2 id="employers-title">For Employers</h2><p>Call our Sales Hotline</p>
<div class="jsc-contact-method"><h3>Ho Chi Minh</h3><?php jsc_contact_phone( 'phone_hcm' ); ?></div>
<div class="jsc-contact-method"><h3>Ha Noi</h3><?php jsc_contact_phone( 'phone_hanoi' ); ?></div>
<p class="jsc-callback"><a href="<?php echo esc_url( add_query_arg( 'enquiry', 'employer', jsc_url( 'contact' ) ) . '#apply' ); ?>">Request a call from one of our<br>Customer Love Account Managers</a><br>We’re ready to help you grow!</p>
</section>
<section class="jsc-contact-jobseekers" aria-labelledby="jobseekers-title"><h2 id="jobseekers-title">For Jobseekers</h2><p>Ask a question on our <?php if ( jsc_setting( 'facebook_url' ) ) : ?><a href="<?php echo esc_url( jsc_setting( 'facebook_url' ) ); ?>">Facebook</a><?php else : ?><span class="jsc-unconfigured" title="Facebook address not supplied">Facebook</span><?php endif; ?> page<br>Read our <a href="<?php echo esc_url( jsc_url( 'news' ) ); ?>">blog posts</a> on interview and CV tips</p>
<div class="jsc-contact-method"><h3>Call us at</h3><?php jsc_contact_phone( 'phone_jobs' ); ?></div></section>
</div></section><?php get_template_part( 'template-parts/project-application-form' ); ?></main>
<?php get_footer(); ?>
