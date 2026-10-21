<?php
$job_id = isset( $_GET['job'] ) ? absint( $_GET['job'] ) : 0;
$employer = isset( $_GET['enquiry'] ) && 'employer' === sanitize_key( $_GET['enquiry'] );
if ( ! $job_id && ! $employer ) { return; }
?>
<section id="apply" class="jsc-application-section jsc-section"><div class="jsc-container">
<?php if ( $job_id && ! jsc_job_accepts_applications( $job_id ) ) : ?><h2 class="jsc-title">Position Unavailable</h2><p class="jsc-empty">This position is not accepting applications. <a href="<?php echo esc_url( jsc_url( 'jobs' ) ); ?>">Browse current jobs</a>.</p>
<?php else : ?>
<h2 class="jsc-title"><?php echo $job_id ? 'Apply For This Job' : 'Request A Callback'; ?></h2>
<?php if ( isset( $_GET['application'] ) && 'saved' === sanitize_key( $_GET['application'] ) ) : ?><div class="jsc-application-confirmation" role="status"><h3>Thank you for getting in touch.</h3><p>Your details have been saved privately in WordPress for review.</p><a href="<?php echo esc_url( jsc_url( 'jobs' ) ); ?>">Browse jobs</a></div>
<?php else : ?>
<form class="jsc-application-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
<?php if ( $job_id ) : ?><p class="jsc-application-role"><?php echo esc_html( get_the_title( $job_id ) ); ?></p><?php endif; ?>
<input type="hidden" name="action" value="jsc_application"><input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>"><input type="hidden" name="enquiry_kind" value="<?php echo $job_id ? 'job' : 'employer'; ?>">
<?php wp_nonce_field( 'jsc_application', 'jsc_nonce' ); ?>
<label>Your name <span aria-hidden="true">*</span><input type="text" name="applicant_name" autocomplete="name" required maxlength="160"></label>
<label>Email address <span aria-hidden="true">*</span><input type="email" name="applicant_email" autocomplete="email" required maxlength="254"></label>
<label>Phone number<?php echo $employer ? ' *' : ' (optional)'; ?><input type="tel" name="applicant_phone" autocomplete="tel" maxlength="40" <?php echo $employer ? 'required' : ''; ?>></label>
<?php if ( $job_id ) : ?><label>CV or portfolio link (optional)<input type="url" name="cv_link" placeholder="https://" maxlength="2000"></label><?php endif; ?>
<label class="jsc-application-message">Your message <span aria-hidden="true">*</span><textarea name="applicant_message" rows="6" required maxlength="10000"></textarea></label>
<label class="jsc-trap" aria-hidden="true">Leave blank<input name="website" tabindex="-1" autocomplete="off"></label>
<p class="jsc-application-privacy">Your details are stored privately in this WordPress site for recruitment enquiries. Fields marked * are required.</p>
<button type="submit"><?php echo $job_id ? 'Submit Application' : 'Request Callback'; ?></button>
</form><?php endif; endif; ?>
</div></section>
