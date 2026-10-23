<?php
/** Run: php tools/migrate-jobscout-widgets.php. Back up the database first. */
if ( PHP_SAPI !== 'cli' ) { http_response_code( 403 ); exit; }
define( 'DISABLE_WP_CRON', true );
define( 'WP_HTTP_BLOCK_EXTERNAL', true );
require dirname( __DIR__ ) . '/wp-load.php';
require get_template_directory() . '/inc/project-widget-migration.php';
$result = jsc_migrate_project_widgets();
if ( is_wp_error( $result ) ) { fwrite( STDERR, $result->get_error_message() . PHP_EOL ); exit( 1 ); }
echo wp_json_encode( $result, JSON_PRETTY_PRINT ) . PHP_EOL;
