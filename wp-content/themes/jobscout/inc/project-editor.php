<?php
/** Shared native editing controls for the project-specific data. */
defined( 'ABSPATH' ) || exit;
function jsc_editor_fields( $post ) {
    if ( 'job_listing' === $post->post_type ) {
        return array( 'jsc_highlights' => array( 'Card highlights (one per line)', 'textarea' ), 'jsc_rating' => array( 'Staff rating from design reference (replace with real feedback)', 'number' ), '_company_logo' => array( 'Company logo — Media Library', 'image' ), 'jsc_gallery_ids' => array( 'Company photos — Media Library (select multiple, drag to reorder in the media selection)', 'gallery' ) );
    }
    return 'post' === $post->post_type ? array( 'jsc_location' => array( 'Article location', 'text' ) ) : array();
}
add_action( 'add_meta_boxes', function ( $type, $post ) {
    if ( ! jsc_editor_fields( $post ) ) { return; }
    add_meta_box( 'jsc-project-details', 'JobScout Project Details', function ( $post ) {
        wp_nonce_field( 'jsc_fields_' . $post->ID, 'jsc_fields_nonce' );
        foreach ( jsc_editor_fields( $post ) as $key => $field ) {
            $value = get_post_meta( $post->ID, $key, true );
            echo '<p><label for="' . esc_attr( $key ) . '"><strong>' . esc_html( $field[0] ) . '</strong></label></p>';
            if ( in_array( $field[1], array( 'image', 'gallery' ), true ) ) {
                if ( 'image' === $field[1] ) { $value = attachment_url_to_postid( $value ); }
                jsc_media_field( $key, 'jsc_project_fields[' . $key . ']', $value, 'gallery' === $field[1] );
            } elseif ( 'textarea' === $field[1] ) {
                echo '<textarea class="widefat" rows="5" id="' . esc_attr( $key ) . '" name="jsc_project_fields[' . esc_attr( $key ) . ']">' . esc_textarea( $value ) . '</textarea>';
            } else {
                echo '<input class="widefat" type="' . esc_attr( $field[1] ) . '" id="' . esc_attr( $key ) . '" name="jsc_project_fields[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '"' . ( 'number' === $field[1] ? ' min="0" max="5" step="0.1"' : '' ) . '>';
            }
        }
    }, $type, 'normal', 'default' );
}, 10, 2 );
add_action( 'save_post', function ( $id, $post ) {
    if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $id ) || ! current_user_can( 'edit_post', $id ) ) { return; }
    $nonce = isset( $_POST['jsc_fields_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['jsc_fields_nonce'] ) ) : '';
    if ( ! wp_verify_nonce( $nonce, 'jsc_fields_' . $id ) || ! isset( $_POST['jsc_project_fields'] ) || ! is_array( $_POST['jsc_project_fields'] ) ) { return; }
    foreach ( jsc_editor_fields( $post ) as $key => $field ) {
        if ( ! isset( $_POST['jsc_project_fields'][ $key ] ) ) { continue; }
        $raw = wp_unslash( $_POST['jsc_project_fields'][ $key ] );
        if ( ! is_scalar( $raw ) ) { continue; }
        if ( 'image' === $field[1] ) {
            $value = wp_attachment_is_image( absint( $raw ) ) ? wp_get_attachment_url( absint( $raw ) ) : '';
        } elseif ( 'gallery' === $field[1] ) {
            $value = implode( ',', array_slice( array_filter( array_unique( array_map( 'absint', explode( ',', $raw ) ) ), 'wp_attachment_is_image' ), 0, 12 ) );
        } else { $value = 'textarea' === $field[1] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw ); }
        if ( 'number' === $field[1] && '' !== $value ) { $value = max( 0, min( 5, (float) $value ) ); }
        update_post_meta( $id, $key, $value );
    }
}, 10, 2 );
function jsc_gallery_photos( $id ) {
    $photos = array();
    if ( metadata_exists( 'post', $id, 'jsc_gallery_ids' ) ) {
        foreach ( array_filter( array_map( 'absint', explode( ',', get_post_meta( $id, 'jsc_gallery_ids', true ) ) ) ) as $image ) {
            $url = wp_get_attachment_image_url( $image, 'large' );
            if ( $url ) { $photos[] = array( 'url' => $url, 'alt' => get_post_meta( $image, '_wp_attachment_image_alt', true ) ); }
        }
        return $photos;
    }
    foreach ( explode( "\n", (string) get_post_meta( $id, 'jsc_gallery', true ) ) as $line ) {
        $parts = array_map( 'trim', explode( '|', $line, 2 ) );
        $url = esc_url_raw( $parts[0], array( 'http', 'https' ) );
        if ( $url && preg_match( '#^https?://#i', $url ) ) { $photos[] = array( 'url' => $url, 'alt' => isset( $parts[1] ) ? $parts[1] : 'Company photograph' ); }
        if ( count( $photos ) >= 12 ) { break; }
    }
    if ( $photos ) { return $photos; }
    if ( get_option( 'jsc_widgets_migrated' ) ) { return $photos; }
    foreach ( array( 'street.jpg' => 'Japanese street', 'project.jpg' => 'Restaurant entrance', 'operations.jpg' => 'Traditional restaurant', 'hospitality.jpg' => 'Cherry blossom lanterns', 'venue.jpg' => 'Garden koi pond', 'torii.jpg' => 'Waterside torii gate' ) as $file => $alt ) { $photos[] = array( 'url' => jsc_asset( $file ), 'alt' => $alt ); }
    return $photos;
}
function jsc_footer_menu() {
    echo '<ul class="jsc-footer-menu">';
    foreach ( array( 'jobs' => 'Jobs', 'about#properties' => 'Companies', 'news' => 'Blog', 'about' => 'About', 'contact' => 'Contact' ) as $route => $label ) {
        $parts = explode( '#', $route ); $url = jsc_url( $parts[0] ) . ( isset( $parts[1] ) ? '#' . $parts[1] : '' );
        echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
    }
    echo '</ul>';
}
