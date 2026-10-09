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
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $jsc_pages['home'] );
update_option( 'blogname', 'Plan Do See Global Inc.' );
update_option( 'blogdescription', 'Share Omotenashi with the world' );
update_option( 'permalink_structure', '/%postname%/' );
flush_rewrite_rules( false );

foreach ( require __DIR__ . '/project-jobs.php' as $order => $job ) {
    list( $slug, $title, $company, $city, $category, $logo, $intro, $highlights ) = $job;
    if ( get_page_by_path( $slug, OBJECT, 'job_listing' ) ) { continue; }
    $id = wp_insert_post( array( 'post_type' => 'job_listing', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug, 'menu_order' => $order, 'post_date' => '2022-10-20 09:00:00', 'post_content' => '<h2>Overview about Company</h2><p>' . esc_html( $intro ) . '</p>', 'post_excerpt' => $intro ), true );
    if ( is_wp_error( $id ) ) { WP_CLI::error( $id->get_error_message() ); }
    foreach ( array( '_company_name' => $company, '_company_logo' => jsc_asset( 'company-' . $logo . '.jpg' ), '_job_location' => $city, '_filled' => '0', '_featured' => '1', '_application' => add_query_arg( 'job', $id, jsc_url( 'contact' ) ) . '#apply', 'jsc_highlights' => $highlights ) as $key => $value ) { update_post_meta( $id, $key, $value ); }
    wp_set_object_terms( $id, 'Fulltime', 'job_listing_type' );
    wp_set_object_terms( $id, $category, 'job_listing_category' );
}
