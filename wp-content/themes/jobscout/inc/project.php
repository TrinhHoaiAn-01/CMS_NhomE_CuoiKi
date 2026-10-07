<?php
/** Shared presentation and WordPress integration for the CMS project. */
defined( 'ABSPATH' ) || exit;

function jsc_asset( $file ) {
    return get_template_directory_uri() . '/images/project/' . ltrim( $file, '/' );
}

function jsc_url( $slug ) {
    $page = get_page_by_path( $slug );
    return $page ? get_permalink( $page ) : home_url( '/' . trim( $slug, '/' ) . '/' );
}

function jsc_setting( $key, $default = '' ) {
    return get_theme_mod( 'jsc_' . $key, $default );
}

function jsc_fallback_menu() {
    echo '<ul class="jsc-menu">';
    foreach ( array( '' => 'Home', 'jobs' => 'Jobs', 'news' => 'News', 'about' => 'About', 'contact' => 'Contact' ) as $slug => $label ) {
        $current = '' === $slug ? is_front_page() : is_page( $slug );
        printf( '<li%s><a href="%s"%s>%s</a></li>', $current ? ' class="current-menu-item"' : '', esc_url( '' === $slug ? home_url( '/' ) : jsc_url( $slug ) ), $current ? ' aria-current="page"' : '', esc_html( $label ) );
    }
    echo '</ul>';
}

add_action( 'wp_enqueue_scripts', function () {
    foreach ( array( 'jobscout', 'jobscout-google-fonts', 'owl-carousel' ) as $handle ) {
        wp_dequeue_style( $handle );
    }
    foreach ( array( 'jobscout', 'owl-carousel', 'owlcarousel2-a11ylayer', 'jobscout-modal-accessibility', 'all', 'v4-shims' ) as $handle ) {
        wp_dequeue_script( $handle );
    }
    wp_enqueue_style( 'jsc-project', get_template_directory_uri() . '/css/project.css', array(), filemtime( get_template_directory() . '/css/project.css' ) );
    wp_enqueue_script( 'jsc-project', get_template_directory_uri() . '/js/project.js', array(), filemtime( get_template_directory() . '/js/project.js' ), true );
}, 99 );

add_action( 'customize_register', function ( $customizer ) {
    $customizer->add_section( 'jsc_project', array( 'title' => __( 'JobScout project', 'jobscout' ), 'priority' => 30 ) );
    $fields = array(
        'brand' => array( 'Brand name', 'Plan Do See Global Inc.' ),
        'tagline' => array( 'Brand subtitle', 'RECRUITING' ),
    );
    foreach ( $fields as $key => $field ) {
        $customizer->add_setting( 'jsc_' . $key, array( 'default' => $field[1], 'sanitize_callback' => 'sanitize_text_field' ) );
        $customizer->add_control( 'jsc_' . $key, array( 'label' => $field[0], 'section' => 'jsc_project', 'type' => 'text' ) );
    }
} );
