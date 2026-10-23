<?php
/** Integration checks against this local WordPress install; temporary edits are restored. */
if ( PHP_SAPI !== 'cli' ) { http_response_code( 403 ); exit; }
define( 'DISABLE_WP_CRON', true );
define( 'WP_HTTP_BLOCK_EXTERNAL', true );
require dirname( __DIR__ ) . '/wp-load.php';
require get_template_directory() . '/inc/project-widget-migration.php';
function jsc_check( $condition, $message ) {
    if ( ! $condition ) { throw new RuntimeException( $message ); }
    echo 'PASS ' . $message . PHP_EOL;
}
$sidebars = wp_get_sidebars_widgets();
$instances = array();
foreach ( array_keys( jsc_widget_definitions() ) as $kind ) { $instances[ $kind ] = get_option( 'widget_jsc_' . $kind ); }
$before_media = wp_count_posts( 'attachment' )->inherit;
$story = get_posts( array( 'post_type' => 'post', 'category_name' => 'pds-news', 'numberposts' => 1 ) )[0];
$story_thumbnail = get_post_thumbnail_id( $story->ID );
$job = get_posts( array( 'post_type' => 'job_listing', 'numberposts' => 1 ) )[0];
$job_gallery = get_post_meta( $job->ID, 'jsc_gallery_ids', true );
$before_records = $wpdb->get_results( "SELECT ID, post_content, post_modified FROM {$wpdb->posts} WHERE post_type IN ('page','post','job_listing') ORDER BY ID", ARRAY_A );
try {
    foreach ( jsc_widget_areas() as $area => $label ) {
        jsc_check( ! empty( $sidebars[ 'jsc-' . $area ] ), $label . ' has populated native widgets' );
        foreach ( $sidebars[ 'jsc-' . $area ] as $id ) { jsc_check( isset( $wp_registered_widgets[ $id ] ), $id . ' is registered' ); }
    }
    $assets = get_option( 'jsc_media_assets' );
    jsc_check( count( $assets ) === 20, 'All 20 project images are Media Library attachments' );
    foreach ( $assets as $file => $id ) {
        jsc_check( wp_attachment_is_image( $id ) && is_file( get_attached_file( $id ) ) && false !== strpos( wp_get_attachment_url( $id ), '/uploads/' ), $file . ' has a valid uploaded image' );
    }
    $widget = new JSC_Section_Widget( 'content' );
    $clean = $widget->update( array( 'title' => '<script>bad()</script>Title', 'source' => 'invalid', 'layout' => 'invalid', 'body' => '<p><strong>Keep</strong><script>alert(1)</script><a href="javascript:alert(1)">link</a></p>', 'url' => 'javascript:alert(1)', 'image' => 999999, 'page_id' => 999999 ), array() );
    jsc_check( false === strpos( $clean['body'], '<script' ) && false === strpos( $clean['body'], 'javascript:' ) && false !== strpos( $clean['body'], '<strong>Keep</strong>' ) && '' === $clean['url'] && 0 === $clean['image'] && 0 === $clean['page_id'], 'Widget save sanitizes HTML, links and attachment references' );
    jsc_check( 'widget' === $clean['source'] && 'plain' === $clean['layout'], 'Invalid display values resolve safely' );
    $hero = new JSC_Section_Widget( 'hero' );
    jsc_check( "First\nSecond" === $hero->update( array( 'title' => "First\nSecond" ), array() )['title'], 'Banner headings retain line breaks when saved' );
    // Change a heading and reorder a real area, then verify the migration preserves both.
    $heroes = $instances['hero']; $hero_number = (int) substr( $sidebars['jsc-home'][0], strrpos( $sidebars['jsc-home'][0], '-' ) + 1 );
    $heroes[ $hero_number ]['title'] = 'Temporary migration verification'; update_option( 'widget_jsc_hero', $heroes );
    $edited = $sidebars; $edited['jsc-home'] = array_reverse( $edited['jsc-home'] ); wp_set_sidebars_widgets( $edited );
    $result = jsc_migrate_project_widgets();
    jsc_check( ! is_wp_error( $result ) && ! empty( $result['already_migrated'] ), 'Migration reruns successfully' );
    jsc_check( (int) $before_media === (int) wp_count_posts( 'attachment' )->inherit, 'Migration rerun creates no duplicate media' );
    jsc_check( wp_get_sidebars_widgets()['jsc-home'] === $edited['jsc-home'] && 'Temporary migration verification' === get_option( 'widget_jsc_hero' )[ $hero_number ]['title'], 'Migration preserves edited text and widget order' );
    $edited['jsc-home'] = array(); wp_set_sidebars_widgets( $edited );
    delete_post_thumbnail( $story->ID );
    update_post_meta( $job->ID, 'jsc_gallery_ids', '' );
    jsc_migrate_project_widgets();
    jsc_check( empty( wp_get_sidebars_widgets()['jsc-home'] ), 'Deliberately emptied widget areas are not reseeded' );
    jsc_check( ! has_post_thumbnail( $story->ID ) && array() === jsc_gallery_photos( $job->ID ), 'Removed featured images and empty company galleries remain empty' );
    $after_records = $wpdb->get_results( "SELECT ID, post_content, post_modified FROM {$wpdb->posts} WHERE post_type IN ('page','post','job_listing') ORDER BY ID", ARRAY_A );
    jsc_check( $before_records === $after_records, 'Migration reruns leave authored records and modification dates unchanged' );
    jsc_check( ! $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type IN ('page','post','job_listing') AND post_content LIKE '%/themes/jobscout/images/project/%'" ), 'All authored page and article image URLs use the Media Library' );
    echo "All integration checks passed. Temporary widget edits restored.\n";
} finally {
    wp_set_sidebars_widgets( $sidebars );
    foreach ( $instances as $kind => $value ) { update_option( 'widget_jsc_' . $kind, $value ); }
    if ( $story_thumbnail ) { set_post_thumbnail( $story->ID, $story_thumbnail ); }
    update_post_meta( $job->ID, 'jsc_gallery_ids', $job_gallery );
}
