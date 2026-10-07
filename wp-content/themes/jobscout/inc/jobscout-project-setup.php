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
WP_CLI::success( 'JobScout project configuration is ready.' );
