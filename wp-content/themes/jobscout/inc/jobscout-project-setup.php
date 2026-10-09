<?php
/** Reproducible project setup. Run with `wp eval-file` from the project root. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    exit( 'Run this file using WP-CLI.' );
}
if ( ! post_type_exists( 'job_listing' ) ) {
    WP_CLI::error( 'Install and activate WP Job Manager first.' );
}
update_option( 'job_manager_enable_categories', 1 );
update_option( 'job_manager_enable_default_category_multiselect', 0 );
update_option( 'job_manager_enable_registration', 1 );
update_option( 'job_manager_user_requires_account', 1 );
update_option( 'job_manager_submission_requires_approval', 1 );
function jsc_setup_page( $slug, $title, $content = '' ) {
    $page = get_page_by_path( $slug );
    if ( $page ) { return $page->ID; }
    return wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title, 'post_content' => $content ), true );
}
$jsc_pages = array();
foreach ( array( 'home' => 'Home', 'jobs' => 'All Jobs', 'news' => 'News', 'about' => 'About', 'contact' => 'Contact Us', 'submit-job' => 'Submit a Job' ) as $slug => $title ) {
    $jsc_pages[ $slug ] = jsc_setup_page( $slug, $title, 'submit-job' === $slug ? '[submit_job_form]' : '' );
}
update_option( 'job_manager_submit_job_form_page_id', $jsc_pages['submit-job'] );
update_option( 'job_manager_jobs_page_id', $jsc_pages['jobs'] );
if ( ! wp_get_nav_menu_object( 'JobScout Primary' ) ) {
    $menu = wp_create_nav_menu( 'JobScout Primary' );
    foreach ( array( 'home' => 'Home', 'jobs' => 'Jobs', 'news' => 'News', 'about' => 'About', 'contact' => 'Contact' ) as $slug => $label ) {
        wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => $label, 'menu-item-object-id' => $jsc_pages[ $slug ], 'menu-item-object' => 'page', 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
    }
    $locations = get_theme_mod( 'nav_menu_locations', array() );
    $locations['primary'] = $menu;
    set_theme_mod( 'nav_menu_locations', $locations );
}
WP_CLI::success( 'JobScout project configuration is ready.' );


if ( ! get_post_meta( $jsc_pages['about'], 'jsc_principles_seeded', true ) ) {
    wp_update_post( array( 'ID' => $jsc_pages['about'], 'post_content' => '<!-- wp:heading {"level":3} --><h3>Our Vision</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Create hotels and restaurants around the world that offer memorable experiences while building a lasting, positive relationship together with our guests, partners, team members and communities.</p><!-- /wp:paragraph --><!-- wp:heading {"level":3} --><h3>Our Mission</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Share “Omotenashi” with the world.</p><!-- /wp:paragraph --><!-- wp:heading {"level":3} --><h3>Our Core Value</h3><!-- /wp:heading --><!-- wp:paragraph --><p>“If I were the guest…”<br>To provide guests with the hospitality you would want to receive as a guest.</p><!-- /wp:paragraph -->' ) );
    update_post_meta( $jsc_pages['about'], 'jsc_principles_seeded', 1 );
}

