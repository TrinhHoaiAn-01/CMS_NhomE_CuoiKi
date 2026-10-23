<?php
/** Explicit, repeatable migration. Never runs during a public page request. */
defined( 'ABSPATH' ) || exit;

function jsc_import_project_media() {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $owners = get_users( array( 'role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC', 'fields' => 'ID' ) );
    $owner = get_current_user_id() ?: ( $owners ? (int) $owners[0] : 0 );
    if ( ! $owner ) { return new WP_Error( 'jsc_media_owner', 'An administrator account is required to own the imported media.' ); }
    $assets = get_option( 'jsc_media_assets', array() );
    $directory = get_template_directory() . '/images/project/';
    foreach ( glob( $directory . '*.{jpg,jpeg,png,webp}', GLOB_BRACE ) as $path ) {
        $file = basename( $path );
        if ( ! empty( $assets[ $file ] ) && wp_attachment_is_image( $assets[ $file ] ) ) { continue; }
        $existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'meta_key' => '_jsc_source_asset', 'meta_value' => $file, 'fields' => 'ids' ) );
        if ( $existing ) { $assets[ $file ] = $existing[0]; continue; }
        $temporary = wp_tempnam( $file );
        if ( ! $temporary || ! copy( $path, $temporary ) ) { return new WP_Error( 'jsc_media_copy', 'Could not copy ' . $file ); }
        $title = ucwords( str_replace( array( '-', '_' ), ' ', pathinfo( $file, PATHINFO_FILENAME ) ) );
        $id = media_handle_sideload( array( 'name' => $file, 'tmp_name' => $temporary ), 0, $title, array( 'post_author' => $owner ) );
        if ( is_wp_error( $id ) ) { @unlink( $temporary ); return $id; }
        update_post_meta( $id, '_jsc_source_asset', $file );
        update_post_meta( $id, '_wp_attachment_image_alt', $title );
        $assets[ $file ] = $id;
        update_option( 'jsc_media_assets', $assets, false );
    }
    update_option( 'jsc_media_assets', $assets, false );
    return $assets;
}

function jsc_migrate_project_widgets() {
    if ( get_option( 'jsc_widgets_migrated' ) ) { return array( 'images' => count( get_option( 'jsc_media_assets', array() ) ), 'already_migrated' => true ); }
    $assets = jsc_import_project_media();
    if ( is_wp_error( $assets ) ) { return $assets; }
    $legacy_base = get_template_directory_uri() . '/images/project/';
    $replacements = array();
    foreach ( $assets as $file => $id ) { $replacements[ $legacy_base . $file ] = wp_get_attachment_url( $id ); }
    // Rewrite only project image URLs, retaining all authored content and metadata.
    $records = get_posts( array( 'post_type' => array( 'post', 'page', 'job_listing' ), 'post_status' => 'any', 'numberposts' => -1 ) );
    foreach ( $records as $post ) {
        $content = strtr( $post->post_content, $replacements );
        // Add native image-block IDs and wp-image classes as well as changing URLs.
        $content = serialize_blocks( jsc_migrate_image_blocks( parse_blocks( $content ), $assets ) );
        if ( $content !== $post->post_content ) {
            $result = wp_update_post( wp_slash( array( 'ID' => $post->ID, 'post_content' => $content ) ), true );
            if ( is_wp_error( $result ) ) { return $result; }
        }
        foreach ( array( '_company_logo', 'jsc_profile_image', 'jsc_gallery' ) as $key ) {
            $value = get_post_meta( $post->ID, $key, true );
            if ( is_string( $value ) && strtr( $value, $replacements ) !== $value ) { update_post_meta( $post->ID, $key, wp_slash( strtr( $value, $replacements ) ) ); }
        }
        if ( 'post' === $post->post_type && ! has_post_thumbnail( $post->ID ) ) {
            $file = get_post_meta( $post->ID, 'jsc_image', true );
            if ( ! empty( $assets[ $file ] ) ) { set_post_thumbnail( $post->ID, $assets[ $file ] ); }
        }
        if ( 'job_listing' === $post->post_type && ! metadata_exists( 'post', $post->ID, 'jsc_gallery_ids' ) ) {
            $ids = array();
            $old_gallery = trim( (string) get_post_meta( $post->ID, 'jsc_gallery', true ) );
            if ( $old_gallery ) {
                foreach ( explode( "\n", $old_gallery ) as $line ) { $url = trim( explode( '|', $line )[0] ); $id = attachment_url_to_postid( $url ); if ( $id ) { $ids[] = $id; } }
            } else {
                foreach ( array( 'street.jpg', 'project.jpg', 'operations.jpg', 'hospitality.jpg', 'venue.jpg', 'torii.jpg' ) as $file ) { if ( ! empty( $assets[ $file ] ) ) { $ids[] = $assets[ $file ]; } }
            }
            // Preserve non-library legacy galleries until their owner replaces them.
            if ( ! $old_gallery || count( $ids ) === count( array_filter( explode( "\n", $old_gallery ) ) ) ) { update_post_meta( $post->ID, 'jsc_gallery_ids', implode( ',', $ids ) ); }
        }
    }
    foreach ( array( 'home_hero_image', 'career_image', 'about_hero_image', 'news_hero_image', 'jobs_hero_image', 'contact_hero_image' ) as $key ) {
        $old = get_theme_mod( 'jsc_' . $key );
        if ( is_string( $old ) && isset( $replacements[ $old ] ) ) { set_theme_mod( 'jsc_' . $key, $replacements[ $old ] ); }
    }
    $page = function ( $slug ) { $page = get_page_by_path( $slug ); return $page ? $page->ID : 0; };
    $image = function ( $file, $setting = '' ) use ( $assets ) {
        $old = $setting ? jsc_setting( $setting ) : '';
        return ( $old ? attachment_url_to_postid( $old ) : 0 ) ?: ( $assets[ $file ] ?? 0 );
    };
    $home = $page( 'home' ); $about = $page( 'about' ); $contact = $page( 'contact' );
    $vision = get_post_thumbnail_id( $about ) ?: $image( 'torii.jpg' );
    $profile_image = get_post_meta( $about, 'jsc_profile_image', true );
    $profile = array( 'title' => 'Company Profile', 'image' => ( $profile_image ? attachment_url_to_postid( $profile_image ) : 0 ) ?: $image( 'tokyo.jpg' ) );
    foreach ( array( 'founded' => 'Established since', 'office' => 'Head Office', 'capital' => 'Capital', 'ceo' => 'CEO', 'employees' => 'Number of Employees' ) as $key => $label ) { $profile[ $key ] = get_post_meta( $about, 'jsc_' . $key, true ); $profile[ $key . '_label' ] = $label; }
    $defaults = array(
        'home' => array(
            array( 'hero', array( 'title' => jsc_setting( 'home_heading', 'Find Your Dream Jobs' ), 'description' => jsc_setting( 'home_description', 'The secret behind our company is simple: to always put ourselves in the other person’s shoes—employee, guest or customer. This allows us to see the world through their eyes, anticipate their needs and better understand their feelings.' ), 'image' => $image( 'home-hero.jpg', 'home_hero_image' ), 'layout' => 'home', 'locations' => "Tokyo\nHo Chi Minh City\nHa Noi\nKyoto\nBali", 'default_location' => 'Tokyo', 'search_label' => 'Search Job' ) ),
            array( 'listings', array( 'title' => 'Top Jobs', 'kind' => 'job', 'mode' => 'featured', 'count' => 6, 'button' => 'View More Jobs', 'url' => jsc_url( 'jobs' ) ) ),
            array( 'content', array( 'title' => 'Career With Us', 'layout' => 'career', 'source' => 'page', 'page_id' => $home, 'image' => $image( 'career-background.png', 'career_image' ), 'button' => 'More About Us', 'url' => jsc_url( 'about' ) ) ),
            array( 'listings', array( 'title' => 'Newest Blog Entries', 'kind' => 'news', 'mode' => 'featured', 'count' => 4, 'category' => 'pds-news' ) ),
        ),
        'jobs' => array( array( 'hero', array( 'title' => 'Career With Us', 'image' => $image( 'jobs-banner-photo.jpg', 'jobs_hero_image' ), 'layout' => 'jobs' ) ), array( 'listings', array( 'title' => 'All Jobs', 'kind' => 'job', 'mode' => 'archive', 'count' => 12, 'button' => 'Load More Jobs' ) ) ),
        'news' => array( array( 'hero', array( 'title' => 'PDS News', 'image' => $image( 'news-banner-photo.jpg', 'news_hero_image' ), 'layout' => 'news' ) ), array( 'content', array( 'title' => '', 'source' => 'page', 'page_id' => $page( 'news' ), 'layout' => 'plain' ) ), array( 'listings', array( 'title' => 'Newest Blog Entries', 'kind' => 'news', 'mode' => 'archive', 'count' => 8, 'category' => 'pds-news' ) ) ),
        'about' => array(
            array( 'hero', array( 'title' => "Share “Omotenashi”\nWith The World", 'image' => $image( 'about-hero.jpg', 'about_hero_image' ), 'layout' => 'about' ) ),
            array( 'content', array( 'title' => 'About Us', 'source' => 'page', 'page_id' => $about, 'layout' => 'vision', 'image' => $vision ) ),
            array( 'content', array( 'title' => 'Hotels, restaurants, banquets/weddings management', 'source' => 'widget', 'body' => get_post_meta( $about, 'jsc_properties', true ), 'layout' => 'properties', 'anchor' => 'properties' ) ),
            array( 'profile', $profile ),
        ),
        'contact' => array(
            array( 'hero', array( 'title' => 'Contact Us', 'image' => $image( 'contact-hero.jpg', 'contact_hero_image' ), 'layout' => 'contact' ) ),
            array( 'content', array( 'title' => 'Our Headquarters Address', 'source' => 'page', 'page_id' => $contact, 'layout' => 'address' ) ),
            array( 'contacts', array( 'title' => 'For Employers', 'intro' => 'Call our Sales Hotline', 'city_one' => 'Ho Chi Minh', 'phone_one' => jsc_setting( 'phone_hcm' ), 'city_two' => 'Ha Noi', 'phone_two' => jsc_setting( 'phone_hanoi' ), 'callback' => 'Request a call from one of our Customer Love Account Managers', 'closing' => 'We’re ready to help you grow!', 'seekers_title' => 'For Jobseekers', 'seekers_body' => ( jsc_setting( 'facebook_url' ) ? 'Ask a question on our <a href="' . esc_url( jsc_setting( 'facebook_url' ) ) . '">Facebook</a> page<br>' : '' ) . 'Read our <a href="' . esc_url( jsc_url( 'news' ) ) . '">blog posts</a> on interview and CV tips', 'phone_label' => 'Call us at', 'phone_jobs' => jsc_setting( 'phone_jobs' ) ) ),
            array( 'application', array( 'title' => 'Apply For This Job', 'callback_title' => 'Request A Callback', 'button' => 'Submit Application', 'callback_button' => 'Request Callback' ) ),
        ),
        'job-detail' => array(
            array( 'breadcrumbs', array( 'title' => 'Job Detail', 'section_label' => 'All Jobs', 'section' => 'jobs' ) ),
            array( 'summary', array( 'title' => 'Job Summary', 'apply_label' => 'Apply Job' ) ),
            array( 'article', array( 'title' => 'Job Description' ) ),
            array( 'location', array( 'title' => 'Location', 'body' => 'Location, working arrangements and the recruitment process can be discussed with the team when you apply.' ) ),
            array( 'company', array( 'title' => 'Staff Rating', 'photos_title' => 'Company Photos' ) ),
            array( 'listings', array( 'title' => 'Other Jobs', 'kind' => 'job', 'mode' => 'related', 'count' => 6 ) ),
        ),
        'news-detail' => array(
            array( 'breadcrumbs', array( 'title' => 'News Detail', 'section_label' => 'All News', 'section' => 'news' ) ),
            array( 'summary', array( 'title' => 'Article Summary' ) ),
            array( 'article', array( 'title' => 'Article Content' ) ),
            array( 'listings', array( 'title' => 'Newest Blog Entries', 'kind' => 'news', 'mode' => 'related', 'count' => 6, 'category' => 'pds-news' ) ),
        ),
        'shared' => array( array( 'newsletter', array( 'title' => 'Subscribe To Our Newsletter', 'placeholder' => 'Input your email address', 'button' => 'Subscribe', 'privacy' => 'Subscribe to receive a confirmation email and updates when we publish new posts. You can unsubscribe at any time.' ) ) ),
    );
    $sidebars = wp_get_sidebars_widgets();
    foreach ( $defaults as $area => $widgets ) {
        // Never replace existing widgets; an interrupted migration can be run again.
        if ( ! empty( $sidebars[ 'jsc-' . $area ] ) ) { continue; }
        $sidebars[ 'jsc-' . $area ] = array();
        foreach ( $widgets as $widget ) {
            list( $kind, $instance ) = $widget; $base = 'jsc_' . $kind;
            $instances = get_option( 'widget_' . $base, array( '_multiwidget' => 1 ) );
            $numbers = array_filter( array_keys( $instances ), 'is_numeric' ); $number = $numbers ? max( $numbers ) + 1 : 2;
            $instances[ $number ] = $instance;
            update_option( 'widget_' . $base, $instances );
            $sidebars[ 'jsc-' . $area ][] = $base . '-' . $number;
        }
        wp_set_sidebars_widgets( $sidebars );
    }
    update_option( 'jsc_widgets_migrated', 1, false );
    return array( 'images' => count( $assets ), 'areas' => count( $defaults ) );
}

function jsc_migrate_image_blocks( $blocks, $assets ) {
    foreach ( $blocks as &$block ) {
        if ( ! empty( $block['innerBlocks'] ) ) { $block['innerBlocks'] = jsc_migrate_image_blocks( $block['innerBlocks'], $assets ); }
        if ( 'core/image' !== $block['blockName'] || ! empty( $block['attrs']['id'] ) ) { continue; }
        if ( ! preg_match( '/<img[^>]+src="([^"]+)"/', $block['innerHTML'], $match ) ) { continue; }
        $id = attachment_url_to_postid( html_entity_decode( $match[1] ) );
        if ( ! $id || ! in_array( $id, $assets ) ) { continue; }
        $block['attrs']['id'] = $id;
        $block['attrs']['linkDestination'] = 'none';
        $old = $block['innerHTML'];
        $block['innerHTML'] = preg_replace( '/<img /', '<img class="wp-image-' . $id . '" ', $old, 1 );
        foreach ( $block['innerContent'] as &$piece ) { if ( is_string( $piece ) ) { $piece = str_replace( $old, $block['innerHTML'], $piece ); } }
        unset( $piece );
    }
    unset( $block ); return $blocks;
}
