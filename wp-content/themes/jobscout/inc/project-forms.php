<?php
/** Private local recruitment enquiries; no outbound mail is sent. */
defined( 'ABSPATH' ) || exit;
add_action( 'customize_register', function ( $customizer ) {
    foreach ( array( 'phone_hcm' => 'Ho Chi Minh employer phone', 'phone_hanoi' => 'Ha Noi employer phone', 'phone_jobs' => 'Jobseeker phone' ) as $key => $label ) {
        $customizer->add_setting( 'jsc_' . $key, array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
        $customizer->add_control( 'jsc_' . $key, array( 'label' => $label, 'section' => 'jsc_project', 'type' => 'text' ) );
    }
} );
add_action( 'init', function () {
    register_post_type( 'jsc_application', array( 'labels' => array( 'name' => 'Recruitment Enquiries', 'singular_name' => 'Recruitment Enquiry' ), 'public' => false, 'show_ui' => true, 'show_in_menu' => 'edit.php?post_type=job_listing', 'supports' => array( 'title', 'editor', 'custom-fields' ), 'capabilities' => array( 'create_posts' => 'do_not_allow', 'edit_posts' => 'manage_options', 'edit_others_posts' => 'manage_options', 'publish_posts' => 'manage_options', 'read_private_posts' => 'manage_options', 'delete_posts' => 'manage_options', 'delete_others_posts' => 'manage_options' ), 'map_meta_cap' => true ) );
} );
function jsc_job_accepts_applications( $id ) {
    return 'job_listing' === get_post_type( $id ) && 'publish' === get_post_status( $id ) && '1' !== get_post_meta( $id, '_filled', true );
}
function jsc_application_submit() {
    $nonce = isset( $_POST['jsc_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['jsc_nonce'] ) ) : '';
    if ( ! wp_verify_nonce( $nonce, 'jsc_application' ) ) { wp_die( 'Please reload the form and try again.', '', array( 'response' => 403, 'back_link' => true ) ); }
    $job_id = isset( $_POST['job_id'] ) ? absint( $_POST['job_id'] ) : 0;
    $kind = isset( $_POST['enquiry_kind'] ) ? sanitize_key( $_POST['enquiry_kind'] ) : '';
    if ( ( $job_id && ! jsc_job_accepts_applications( $job_id ) ) || ( ! $job_id && 'employer' !== $kind ) ) { wp_die( 'This position is not accepting applications.', '', array( 'response' => 400, 'back_link' => true ) ); }
    $name = isset( $_POST['applicant_name'] ) ? sanitize_text_field( wp_unslash( $_POST['applicant_name'] ) ) : '';
    $email = isset( $_POST['applicant_email'] ) ? sanitize_email( wp_unslash( $_POST['applicant_email'] ) ) : '';
    $phone = isset( $_POST['applicant_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['applicant_phone'] ) ) : '';
    $message = isset( $_POST['applicant_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['applicant_message'] ) ) : '';
    $cv = isset( $_POST['cv_link'] ) ? esc_url_raw( wp_unslash( $_POST['cv_link'] ), array( 'http', 'https' ) ) : '';
    if ( ! $name || strlen( $name ) > 160 || ! is_email( $email ) || ! $message || strlen( $message ) > 10000 || strlen( $phone ) > 40 || ( 'employer' === $kind && ! $phone ) ) { wp_die( 'Please complete your name, valid email and message. Callback requests also need a phone number.', '', array( 'response' => 400, 'back_link' => true ) ); }
    if ( empty( $_POST['website'] ) ) {
        $slug = hash( 'sha256', strtolower( $email ) . '|' . $job_id . '|' . $message );
        $existing = get_page_by_path( $slug, OBJECT, 'jsc_application' );
        if ( ! $existing ) {
            $id = wp_insert_post( wp_slash( array( 'post_type' => 'jsc_application', 'post_status' => 'private', 'post_name' => $slug, 'post_title' => $name . ' — ' . ( $job_id ? get_post_field( 'post_title', $job_id ) : 'Employer callback request' ), 'post_content' => 'Email: ' . $email . "\nPhone: " . $phone . "\nCV link: " . $cv . "\n\n" . $message ) ), true );
            if ( is_wp_error( $id ) ) { wp_die( 'Your enquiry could not be saved. Please try again.', '', array( 'response' => 500, 'back_link' => true ) ); }
            update_post_meta( $id, 'jsc_job_id', $job_id ); update_post_meta( $id, 'jsc_email', $email );
        }
    }
    $args = $job_id ? array( 'job' => $job_id, 'application' => 'saved' ) : array( 'enquiry' => 'employer', 'application' => 'saved' );
    wp_safe_redirect( add_query_arg( $args, jsc_url( 'contact' ) ) . '#apply' ); exit;
}
add_action( 'admin_post_jsc_application', 'jsc_application_submit' );
add_action( 'admin_post_nopriv_jsc_application', 'jsc_application_submit' );
