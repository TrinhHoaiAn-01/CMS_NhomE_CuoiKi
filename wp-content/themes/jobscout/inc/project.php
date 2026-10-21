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
        'home_heading' => array( 'Home hero heading', 'Find Your Dream Jobs' ),
        'home_description' => array( 'Home hero description', 'The secret behind our company is simple: to always put ourselves in the other person’s shoes—employee, guest or customer. This allows us to see the world through their eyes, anticipate their needs and better understand their feelings.' ),
    );
    foreach ( $fields as $key => $field ) {
        $customizer->add_setting( 'jsc_' . $key, array( 'default' => $field[1], 'sanitize_callback' => 'sanitize_text_field' ) );
        $customizer->add_control( 'jsc_' . $key, array( 'label' => $field[0], 'section' => 'jsc_project', 'type' => 'text' ) );
    }
} );

function jsc_icon( $name ) {
    $paths = array(
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
        'pin' => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
        'mail' => '<rect x="2" y="4" width="20" height="16"/><path d="m2 5 10 8L22 5"/>',
        'facebook' => '<path fill="currentColor" stroke="none" d="M14 22v-9h3l.5-4H14V7c0-1 .3-2 2-2h2V1.4A26 26 0 0 0 15 1c-3 0-5 1.8-5 5v3H7v4h3v9z"/>',
        'twitter' => '<path fill="currentColor" stroke="none" d="M22 5a8 8 0 0 1-2.4.7A4 4 0 0 0 21.4 3a8 8 0 0 1-2.6 1A4.1 4.1 0 0 0 12 7.7a11.6 11.6 0 0 1-8.4-4.3A4 4 0 0 0 4.8 9a4 4 0 0 1-1.8-.5 4.1 4.1 0 0 0 3.3 4 4 4 0 0 1-1.8 0A4 4 0 0 0 8.3 15 8.2 8.2 0 0 1 2 16.7a11.6 11.6 0 0 0 17.8-9.8A8 8 0 0 0 22 5Z"/>',
    );
    if ( 'google' === $name ) { return '<span class="jsc-google-letter" aria-hidden="true">G</span>'; }
    if ( 'line' === $name ) { return '<span class="jsc-line-letter" aria-hidden="true">LINE</span>'; }
    return '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false">' . ( $paths[ $name ] ?? '' ) . '</svg>';
}
add_action( 'customize_register', function ( $customizer ) {
    foreach ( array( 'facebook', 'google', 'line', 'twitter' ) as $name ) {
        $customizer->add_setting( 'jsc_' . $name . '_url', array( 'sanitize_callback' => 'esc_url_raw', 'default' => '' ) );
        $customizer->add_control( 'jsc_' . $name . '_url', array( 'label' => ucfirst( $name ) . ' URL', 'section' => 'jsc_project', 'type' => 'url' ) );
    }
} );
add_action( 'init', function () {
    register_post_type( 'jsc_subscriber', array( 'labels' => array( 'name' => 'Newsletter Sign-ups', 'singular_name' => 'Newsletter Sign-up' ), 'public' => false, 'show_ui' => true, 'show_in_menu' => 'tools.php', 'supports' => array( 'title' ), 'capabilities' => array( 'create_posts' => 'do_not_allow', 'edit_posts' => 'manage_options', 'edit_others_posts' => 'manage_options', 'publish_posts' => 'manage_options', 'read_private_posts' => 'manage_options', 'delete_posts' => 'manage_options', 'delete_others_posts' => 'manage_options' ), 'map_meta_cap' => true ) );
} );
function jsc_subscribe() {
    $nonce = isset( $_POST['jsc_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['jsc_nonce'] ) ) : '';
    if ( ! wp_verify_nonce( $nonce, 'jsc_subscribe' ) ) { wp_die( esc_html__( 'Please reload the page and try again.', 'jobscout' ), '', array( 'response' => 403 ) ); }
    $email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $result = 'error';
    if ( is_email( $email ) && empty( $_POST['website'] ) ) {
        $slug = hash( 'sha256', strtolower( $email ) );
        $existing = get_page_by_path( $slug, OBJECT, 'jsc_subscriber' );
        $saved = $existing ? $existing->ID : wp_insert_post( array( 'post_type' => 'jsc_subscriber', 'post_status' => 'private', 'post_name' => $slug, 'post_title' => $email ), true );
        if ( ! is_wp_error( $saved ) && $saved ) { $result = 'saved'; }
    }
    $return = wp_validate_redirect( wp_get_referer(), home_url( '/' ) );
    wp_safe_redirect( add_query_arg( 'subscription', $result, remove_query_arg( 'subscription', $return ) ) . '#newsletter-heading' );
    exit;
}
add_action( 'admin_post_jsc_subscribe', 'jsc_subscribe' );
add_action( 'admin_post_nopriv_jsc_subscribe', 'jsc_subscribe' );

function jsc_jobs( $args = array() ) {
    return new WP_Query( wp_parse_args( $args, array( 'post_type' => 'job_listing', 'post_status' => 'publish', 'posts_per_page' => 6, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC', 'ID' => 'ASC' ), 'meta_query' => array( array( 'key' => '_filled', 'value' => '1', 'compare' => '!=' ) ) ) ) );
}

function jsc_news( $args = array() ) {
    return new WP_Query( wp_parse_args( $args, array( 'post_type' => 'post', 'post_status' => 'publish', 'category_name' => 'pds-news', 'posts_per_page' => 4, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC', 'ID' => 'ASC' ), 'ignore_sticky_posts' => true ) ) );
}

function jsc_cards( $query, $kind ) {
    if ( $query->have_posts() ) {
        echo '<div class="jsc-grid jsc-' . esc_attr( $kind ) . '-grid">';
        while ( $query->have_posts() ) { $query->the_post(); get_template_part( 'template-parts/project-' . $kind . '-card' ); }
        echo '</div>';
    } else { echo '<p class="jsc-empty">New opportunities and stories will be published here soon.</p>'; }
    wp_reset_postdata();
}

add_action( 'init', function () {
    add_post_type_support( 'job_listing', 'custom-fields' );
    add_post_type_support( 'post', 'custom-fields' );
    add_post_type_support( 'page', 'custom-fields' );
} );


// Keep the section navigation active while reading a native article or job.
add_filter( 'nav_menu_css_class', function ( $classes, $item ) {
    $section = is_singular( 'job_listing' ) ? 'jobs' : ( is_singular( 'post' ) ? 'news' : '' );
    $page = $section ? get_page_by_path( $section ) : null;
    if ( $page && (int) $item->object_id === $page->ID ) { $classes[] = 'current-menu-item'; }
    return array_unique( $classes );
}, 10, 2 );



function jsc_breadcrumbs( $section, $section_label, $current ) {
    echo '<nav class="jsc-breadcrumbs" aria-label="Breadcrumb"><ol><li><a href="' . esc_url( home_url( '/' ) ) . '">Home</a></li><li><a href="' . esc_url( jsc_url( $section ) ) . '">' . esc_html( $section_label ) . '</a></li><li aria-current="page">' . esc_html( $current ) . '</li></ol></nav>';
}



function jsc_contact_phone( $key ) {
    $number = jsc_setting( $key );
    if ( $number ) { echo '<a class="jsc-phone" href="' . esc_url( 'tel:' . preg_replace( '/[^+0-9]/', '', $number ) ) . '">' . esc_html( $number ) . '</a>'; }
    else { echo '<span class="jsc-redacted" role="img" aria-label="Phone number redacted in supplied design" title="Phone number not supplied"></span>'; }
}


require get_template_directory() . '/inc/project-forms.php';

require get_template_directory() . '/inc/project-editor.php';
