<?php
/** Native, reorderable WordPress widgets for the seven project layouts. */
defined( 'ABSPATH' ) || exit;

function jsc_widget_areas() {
    return array( 'home' => 'Home', 'jobs' => 'Jobs', 'job-detail' => 'Job Detail', 'news' => 'News', 'news-detail' => 'News Detail', 'about' => 'About', 'contact' => 'Contact', 'shared' => 'Shared — Newsletter' );
}

function jsc_widget_definitions() {
    return array(
        'hero' => array( 'Hero / Banner', array( 'title' => array( 'Heading', 'textarea' ), 'description' => array( 'Introduction', 'textarea' ), 'image' => array( 'Background image', 'image' ), 'layout' => array( 'Banner style', array( 'home' => 'Home with job search', 'about' => 'About', 'jobs' => 'Jobs', 'news' => 'News', 'contact' => 'Contact' ) ), 'locations' => array( 'Search locations (one per line)', 'textarea' ), 'default_location' => 'Default search location', 'search_label' => 'Search button label' ) ),
        'listings' => array( 'Jobs / News List', array( 'title' => 'Heading', 'kind' => array( 'Content', array( 'job' => 'Jobs', 'news' => 'News' ) ), 'mode' => array( 'Display', array( 'featured' => 'Latest / featured', 'archive' => 'Archive with pagination', 'related' => 'Related (exclude current record)' ) ), 'count' => array( 'Items per page', 'number' ), 'button' => 'Button label', 'url' => array( 'Button destination', 'url' ), 'category' => 'News category slug (blank for all posts)' ) ),
        'content' => array( 'Text & Image', array( 'title' => 'Heading', 'source' => array( 'Text source', array( 'widget' => 'Write below', 'page' => 'Selected page content' ) ), 'page_id' => array( 'Source page', 'page' ), 'body' => array( 'Text', 'rich' ), 'image' => array( 'Image / background', 'image' ), 'layout' => array( 'Layout', array( 'plain' => 'Text section', 'career' => 'Career background', 'vision' => 'About image and text', 'properties' => 'About properties', 'address' => 'Headquarters address' ) ), 'anchor' => 'Section anchor (optional)', 'button' => 'Button label', 'url' => array( 'Button destination', 'url' ) ) ),
        'profile' => array( 'Company Profile', array( 'title' => 'Admin label', 'image' => array( 'Profile image', 'image' ), 'founded_label' => 'Established label', 'founded' => 'Established', 'office_label' => 'Office label', 'office' => 'Office', 'capital_label' => 'Capital label', 'capital' => 'Capital', 'ceo_label' => 'CEO label', 'ceo' => 'CEO', 'employees_label' => 'Employees label', 'employees' => 'Employees' ) ),
        'contacts' => array( 'Contact Columns', array( 'title' => 'Employer heading', 'intro' => 'Employer introduction', 'city_one' => 'First office', 'phone_one' => 'First office phone', 'city_two' => 'Second office', 'phone_two' => 'Second office phone', 'callback' => 'Callback link text', 'closing' => 'Closing text', 'seekers_title' => 'Jobseeker heading', 'seekers_body' => array( 'Jobseeker information', 'rich' ), 'phone_label' => 'Jobseeker phone label', 'phone_jobs' => 'Jobseeker phone' ) ),
        'application' => array( 'Application / Callback Form', array( 'title' => 'Application heading', 'callback_title' => 'Callback heading', 'button' => 'Application button', 'callback_button' => 'Callback button' ) ),
        'breadcrumbs' => array( 'Detail Breadcrumbs', array( 'title' => 'Current page label', 'section_label' => 'Parent section label', 'section' => array( 'Parent section', array( 'jobs' => 'Jobs', 'news' => 'News' ) ) ) ),
        'summary' => array( 'Detail Summary', array( 'title' => 'Admin label', 'apply_label' => 'Apply button label' ) ),
        'article' => array( 'Article / Job Content', array( 'title' => 'Admin label' ) ),
        'location' => array( 'Job Location', array( 'title' => 'Heading', 'body' => array( 'Additional location information', 'textarea' ) ) ),
        'company' => array( 'Company Rating & Photos', array( 'title' => 'Rating heading', 'photos_title' => 'Photos heading' ) ),
        'newsletter' => array( 'Newsletter', array( 'title' => 'Heading', 'placeholder' => 'Email placeholder', 'button' => 'Subscribe button label', 'privacy' => array( 'Privacy information', 'textarea' ) ) ),
    );
}

class JSC_Section_Widget extends WP_Widget {
    public $kind;
    public function __construct( $kind = 'hero' ) {
        $this->kind = $kind;
        $definition = jsc_widget_definitions()[ $kind ];
        parent::__construct( 'jsc_' . $kind, 'JobScout: ' . $definition[0], array( 'description' => 'Editable JobScout section. Add to a JobScout page area and drag to reorder.', 'customize_selective_refresh' => false ), array( 'width' => 480 ) );
    }
    public function form( $instance ) {
        echo '<div class="jsc-widget-form">';
        foreach ( jsc_widget_definitions()[ $this->kind ][1] as $key => $field ) {
            $label = is_array( $field ) ? $field[0] : $field;
            $type = is_array( $field ) ? $field[1] : 'text';
            $value = $instance[ $key ] ?? '';
            $id = $this->get_field_id( $key ); $name = $this->get_field_name( $key );
            echo '<p><label for="' . esc_attr( $id ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
            if ( is_array( $type ) ) {
                echo '<select class="widefat" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
                foreach ( $type as $option => $text ) { echo '<option value="' . esc_attr( $option ) . '" ' . selected( $value, $option, false ) . '>' . esc_html( $text ) . '</option>'; }
                echo '</select>';
            } elseif ( 'page' === $type ) {
                wp_dropdown_pages( array( 'name' => $name, 'id' => $id, 'selected' => absint( $value ), 'show_option_none' => 'Select page', 'class' => 'widefat' ) );
            } elseif ( 'image' === $type ) {
                jsc_media_field( $id, $name, $value );
            } elseif ( in_array( $type, array( 'textarea', 'rich' ), true ) ) {
                echo '<textarea class="widefat ' . ( 'rich' === $type ? 'jsc-rich-text' : '' ) . '" rows="5" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">' . esc_textarea( $value ) . '</textarea>';
                if ( 'rich' === $type ) { echo '<button type="button" class="button jsc-edit-rich">Open visual editor</button>'; }
            } else {
                echo '<input class="widefat" type="' . esc_attr( $type ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '"' . ( 'number' === $type ? ' min="1" max="48"' : '' ) . '>';
            }
            echo '</p>';
        }
        echo '</div>';
    }
    public function update( $new_instance, $old_instance ) {
        $clean = array();
        foreach ( jsc_widget_definitions()[ $this->kind ][1] as $key => $field ) {
            $type = is_array( $field ) ? $field[1] : 'text'; $value = $new_instance[ $key ] ?? '';
            if ( ! is_scalar( $value ) ) { $value = ''; }
            if ( is_array( $type ) ) { $clean[ $key ] = array_key_exists( $value, $type ) ? $value : array_key_first( $type ); }
            elseif ( 'image' === $type ) { $clean[ $key ] = wp_attachment_is_image( absint( $value ) ) ? absint( $value ) : 0; }
            elseif ( 'number' === $type ) { $clean[ $key ] = min( 48, max( 1, absint( $value ) ) ); }
            elseif ( 'page' === $type ) { $clean[ $key ] = 'page' === get_post_type( absint( $value ) ) ? absint( $value ) : 0; }
            elseif ( 'rich' === $type ) { $clean[ $key ] = wp_kses_post( $value ); }
            elseif ( 'url' === $type ) { $clean[ $key ] = esc_url_raw( $value ); }
            else { $clean[ $key ] = 'textarea' === $type ? sanitize_textarea_field( $value ) : sanitize_text_field( $value ); }
        }
        return $clean;
    }
    public function widget( $args, $instance ) {
        $w = $instance; $kind = $this->kind;
        $uid = wp_unique_id( 'jsc-' . $kind . '-' );
        include get_template_directory() . '/template-parts/project-widget.php';
    }
}

add_action( 'widgets_init', function () {
    foreach ( jsc_widget_areas() as $key => $name ) {
        register_sidebar( array( 'id' => 'jsc-' . $key, 'name' => 'JobScout — ' . $name, 'description' => 'Drag JobScout sections here to arrange this layout. An empty area stays empty.', 'before_widget' => '', 'after_widget' => '', 'before_title' => '', 'after_title' => '' ) );
    }
    foreach ( array_keys( jsc_widget_definitions() ) as $kind ) { register_widget( new JSC_Section_Widget( $kind ) ); }
} );

// These widgets have native PHP control forms, including the WordPress media modal.
add_filter( 'use_widgets_block_editor', '__return_false' );

function jsc_media_field( $id, $name, $value, $multiple = false ) {
    echo '<span class="jsc-media-control" style="display:block"><input type="hidden" class="jsc-media-value" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
    echo '<span class="jsc-media-preview" style="display:block">';
    foreach ( array_filter( array_map( 'absint', explode( ',', (string) $value ) ) ) as $attachment ) { echo wp_get_attachment_image( $attachment, 'thumbnail', false, array( 'style' => 'max-width:100px;height:70px;object-fit:cover;margin:4px' ) ); }
    echo '</span><button type="button" class="button jsc-select-media" data-multiple="' . ( $multiple ? '1' : '0' ) . '">Choose ' . ( $multiple ? 'photos' : 'image' ) . '</button> <button type="button" class="button-link jsc-remove-media">Remove</button></span>';
}

function jsc_widgets_admin_assets() {
    wp_enqueue_media(); wp_enqueue_editor();
    wp_enqueue_script( 'jsc-widget-controls', get_template_directory_uri() . '/js/project-widgets-admin.js', array( 'jquery', 'media-views', 'editor' ), filemtime( get_template_directory() . '/js/project-widgets-admin.js' ), true );
}
add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( in_array( $hook, array( 'widgets.php', 'post.php', 'post-new.php' ), true ) ) { jsc_widgets_admin_assets(); }
} );
add_action( 'customize_controls_enqueue_scripts', 'jsc_widgets_admin_assets' );

function jsc_render_area( $area ) { dynamic_sidebar( 'jsc-' . $area ); }
function jsc_widget_text( $w ) {
    if ( 'page' === ( $w['source'] ?? '' ) ) {
        $page = get_post( absint( $w['page_id'] ?? 0 ) );
        return $page && 'publish' === $page->post_status ? apply_filters( 'the_content', $page->post_content ) : '';
    }
    return wpautop( wp_kses_post( $w['body'] ?? '' ) );
}
function jsc_widget_background( $id ) {
    $url = wp_get_attachment_image_url( absint( $id ), 'full' );
    return ' style="' . esc_attr( $url ? 'background-image:linear-gradient(#20191455,#20191455),url("' . esc_url_raw( $url ) . '")' : 'background-image:none' ) . '"';
}
function jsc_widget_phone( $number ) {
    if ( $number ) { echo '<a class="jsc-phone" href="' . esc_url( 'tel:' . preg_replace( '/[^+0-9]/', '', $number ) ) . '">' . esc_html( $number ) . '</a>'; }
}

add_action( 'admin_notices', function () {
    $screen = get_current_screen();
    if ( $screen && in_array( $screen->base, array( 'widgets', 'post' ), true ) && current_user_can( 'edit_theme_options' ) ) {
        echo '<div class="notice notice-info"><p>JobScout layouts: edit headings, images and section order in <a href="' . esc_url( admin_url( 'widgets.php' ) ) . '">Appearance → Widgets</a>. Page content supplies the text for sections set to “Selected page content”. Articles and job descriptions remain in Posts and Job Listings.</p></div>';
    }
} );
