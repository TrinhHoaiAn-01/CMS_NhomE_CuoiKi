<?php
/**
 * Plugin Name: JobScout Newsletter
 * Description: Newsletter subscriptions, welcome emails, and first-publication post notifications.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 7.4
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'jsc_newsletter_register' );
function jsc_newsletter_register() {
    register_post_type( 'jsc_subscriber', array(
        'labels' => array( 'name' => 'Newsletter Sign-ups', 'singular_name' => 'Newsletter Sign-up' ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'tools.php',
        'supports' => array( 'title' ),
        'capabilities' => array(
            'create_posts' => 'do_not_allow', 'edit_posts' => 'manage_options',
            'edit_others_posts' => 'manage_options', 'publish_posts' => 'manage_options',
            'read_private_posts' => 'manage_options', 'delete_posts' => 'manage_options',
            'delete_others_posts' => 'manage_options',
        ),
        'map_meta_cap' => true,
    ) );
}

function jsc_newsletter_brand() {
    return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
}

function jsc_newsletter_active( $id ) {
    return 'jsc_subscriber' === get_post_type( $id )
        && 'private' === get_post_status( $id )
        && 'unsubscribed' !== get_post_meta( $id, '_jsc_newsletter_status', true );
}

function jsc_newsletter_unsubscribe_url( $id ) {
    $token = get_post_meta( $id, '_jsc_newsletter_token', true );
    if ( ! $token ) {
        $token = wp_generate_password( 48, false, false );
        update_post_meta( $id, '_jsc_newsletter_token', $token );
    }
    return add_query_arg( array( 'action' => 'jsc_newsletter_unsubscribe', 'subscriber' => $id, 'token' => $token ), admin_url( 'admin-post.php' ) );
}

/** Send separately to each subscriber so addresses are never shared. */
function jsc_newsletter_mail( $id, $subject, $message ) {
    $email = get_post_field( 'post_title', $id, 'raw' );
    if ( ! jsc_newsletter_active( $id ) || ! is_email( $email ) ) {
        return false;
    }
    $unsubscribe = jsc_newsletter_unsubscribe_url( $id );
    $message .= "\n\nYou received this email because you subscribed to " . jsc_newsletter_brand() . ".\nUnsubscribe: " . $unsubscribe;
    return wp_mail( $email, $subject, $message, array( 'Content-Type: text/plain; charset=UTF-8', 'List-Unsubscribe: <' . $unsubscribe . '>' ) );
}

/** Atomic short-lived locks prevent simultaneous form submits / cron workers. */
function jsc_newsletter_lock( $key ) {
    $name = 'jsc_newsletter_lock_' . $key;
    $held = (int) get_option( $name );
    if ( $held && $held < time() - 600 ) {
        delete_option( $name );
    }
    return add_option( $name, time(), '', false );
}

function jsc_newsletter_schedule( $hook, $args, $delay = 60 ) {
    if ( ! wp_next_scheduled( $hook, $args ) ) {
        wp_schedule_single_event( time() + $delay, $hook, $args );
    }
}

/** Returns a result code for both the native footer form and integration tests. */
function jsc_newsletter_subscribe( $email ) {
    $email = strtolower( trim( $email ) );
    if ( strlen( $email ) > 254 || ! is_email( $email ) ) {
        return 'error';
    }
    $slug = hash( 'sha256', $email );
    if ( ! jsc_newsletter_lock( $slug ) ) {
        return 'busy';
    }
    try {
        $existing = get_page_by_path( $slug, OBJECT, 'jsc_subscriber' );
        if ( $existing && jsc_newsletter_active( $existing->ID ) ) {
            return 'saved'; // Do not send repeated welcome emails on duplicate submits.
        }
        $id = $existing ? $existing->ID : wp_insert_post( array(
            'post_type' => 'jsc_subscriber', 'post_status' => 'private',
            'post_title' => $email, 'post_name' => $slug,
        ), true );
        if ( is_wp_error( $id ) || ! $id ) {
            return 'error';
        }
        if ( $existing && 'private' !== $existing->post_status ) {
            $updated = wp_update_post( array( 'ID' => $id, 'post_status' => 'private' ), true );
            if ( is_wp_error( $updated ) ) {
                return 'error';
            }
        }
        update_post_meta( $id, '_jsc_newsletter_status', 'active' );
        update_post_meta( $id, '_jsc_newsletter_joined', time() );
        update_post_meta( $id, '_jsc_newsletter_token', wp_generate_password( 48, false, false ) );
        delete_post_meta( $id, '_jsc_newsletter_welcome_sent' );
        delete_post_meta( $id, '_jsc_newsletter_welcome_attempts' );
        delete_post_meta( $id, '_jsc_newsletter_welcome_failed' );
        // A scheduled fallback recovers if the SMTP connection times out.
        jsc_newsletter_schedule( 'jsc_newsletter_welcome', array( (int) $id ), 300 );
        jsc_newsletter_welcome( $id );
        return get_post_meta( $id, '_jsc_newsletter_welcome_sent', true ) ? 'saved' : 'mail_pending';
    } finally {
        delete_option( 'jsc_newsletter_lock_' . $slug );
    }
}

add_action( 'admin_post_jsc_subscribe', 'jsc_newsletter_handle_subscribe' );
add_action( 'admin_post_nopriv_jsc_subscribe', 'jsc_newsletter_handle_subscribe' );
function jsc_newsletter_handle_subscribe() {
    $nonce = isset( $_POST['jsc_nonce'] ) && is_string( $_POST['jsc_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['jsc_nonce'] ) ) : '';
    if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! wp_verify_nonce( $nonce, 'jsc_subscribe' ) ) {
        wp_die( 'Please reload the page and try again.', 'Newsletter', array( 'response' => 403 ) );
    }
    $result = 'error';
    $email = isset( $_POST['email'] ) && is_string( $_POST['email'] ) ? wp_unslash( $_POST['email'] ) : '';
    if ( empty( $_POST['website'] ) && is_email( trim( $email ) ) ) {
        $rate_key = 'jsc_newsletter_rate_' . hash_hmac( 'sha256', $_SERVER['REMOTE_ADDR'] ?? '', wp_salt() );
        $rate = get_transient( $rate_key );
        if ( ! $rate || $rate['count'] < 10 ) {
            $rate = $rate ?: array( 'count' => 0, 'until' => time() + HOUR_IN_SECONDS );
            $rate['count']++;
            set_transient( $rate_key, $rate, max( 1, $rate['until'] - time() ) );
            $result = jsc_newsletter_subscribe( $email );
        } else {
            $result = 'limited';
        }
    }
    $return = wp_validate_redirect( wp_get_referer(), home_url( '/' ) );
    $return = preg_replace( '/#.*$/', '', $return );
    wp_safe_redirect( add_query_arg( 'subscription', $result, remove_query_arg( 'subscription', $return ) ) . '#newsletter-heading', 303 );
    exit;
}

add_action( 'jsc_newsletter_welcome', 'jsc_newsletter_welcome' );
function jsc_newsletter_welcome( $id ) {
    $id = (int) $id;
    if ( ! jsc_newsletter_active( $id ) || get_post_meta( $id, '_jsc_newsletter_welcome_sent', true ) ) {
        wp_clear_scheduled_hook( 'jsc_newsletter_welcome', array( $id ) );
        return;
    }
    if ( ! jsc_newsletter_lock( 'welcome_' . $id ) ) {
        jsc_newsletter_schedule( 'jsc_newsletter_welcome', array( $id ), 660 );
        return;
    }
    try {
        $attempts = (int) get_post_meta( $id, '_jsc_newsletter_welcome_attempts', true );
        if ( $attempts >= 3 ) {
            wp_clear_scheduled_hook( 'jsc_newsletter_welcome', array( $id ) );
            return;
        }
        update_post_meta( $id, '_jsc_newsletter_welcome_attempts', $attempts + 1 );
        $sent = jsc_newsletter_mail( $id, jsc_newsletter_brand() . ': Newsletter subscription confirmed',
            "Thank you for subscribing!\n\n" . get_post_field( 'post_title', $id, 'raw' ) . " has been subscribed to our newsletter.\nYou will receive an email whenever we publish a new post.\n\nVisit our website: " . home_url( '/' ) );
        wp_clear_scheduled_hook( 'jsc_newsletter_welcome', array( $id ) );
        if ( $sent ) {
            update_post_meta( $id, '_jsc_newsletter_welcome_sent', time() );
            delete_post_meta( $id, '_jsc_newsletter_welcome_failed' );
        } elseif ( $attempts + 1 < 3 ) {
            jsc_newsletter_schedule( 'jsc_newsletter_welcome', array( $id ), 300 );
        } else {
            update_post_meta( $id, '_jsc_newsletter_welcome_failed', time() );
        }
    } finally {
        delete_option( 'jsc_newsletter_lock_welcome_' . $id );
    }
}

/** GET displays a confirmation; only the explicit POST unsubscribes. */
add_action( 'admin_post_jsc_newsletter_unsubscribe', 'jsc_newsletter_handle_unsubscribe' );
add_action( 'admin_post_nopriv_jsc_newsletter_unsubscribe', 'jsc_newsletter_handle_unsubscribe' );
function jsc_newsletter_handle_unsubscribe() {
    $input = 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ? $_POST : $_GET;
    $id = isset( $input['subscriber'] ) && is_scalar( $input['subscriber'] ) ? absint( $input['subscriber'] ) : 0;
    $token = isset( $input['token'] ) && is_string( $input['token'] ) ? wp_unslash( $input['token'] ) : '';
    $expected = get_post_meta( $id, '_jsc_newsletter_token', true );
    nocache_headers();
    header( 'Referrer-Policy: no-referrer' );
    header( 'X-Robots-Tag: noindex, nofollow' );
    if ( 'jsc_subscriber' !== get_post_type( $id ) || ! $expected || ! hash_equals( $expected, $token ) ) {
        wp_die( 'This unsubscribe link is invalid.', 'Newsletter', array( 'response' => 403 ) );
    }
    if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
        update_post_meta( $id, '_jsc_newsletter_status', 'unsubscribed' );
        wp_clear_scheduled_hook( 'jsc_newsletter_welcome', array( $id ) );
        $message = '<p>You have been unsubscribed. You will no longer receive newsletter emails.</p>';
    } else {
        $message = '<p>Stop receiving newsletter emails from ' . esc_html( jsc_newsletter_brand() ) . '?</p>';
        $message .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="jsc_newsletter_unsubscribe"><input type="hidden" name="subscriber" value="' . esc_attr( $id ) . '"><input type="hidden" name="token" value="' . esc_attr( $token ) . '"><button type="submit">Unsubscribe</button></form>';
    }
    $message .= '<p><a href="' . esc_url( home_url( '/' ) ) . '">Return to website</a></p>';
    wp_die( $message, 'Newsletter', array( 'response' => 200 ) ); // All dynamic HTML is escaped above.
}

/** Seed only the publication marker, without sending historical announcements. */
register_activation_hook( __FILE__, 'jsc_newsletter_activate' );
function jsc_newsletter_activate() {
    global $wpdb;
    $wpdb->query( "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) SELECT p.ID, '_jsc_newsletter_announced', 'existing' FROM {$wpdb->posts} p WHERE p.post_type = 'post' AND p.post_status = 'publish' AND NOT EXISTS (SELECT 1 FROM {$wpdb->postmeta} m WHERE m.post_id = p.ID AND m.meta_key = '_jsc_newsletter_announced')" );
    foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_jsc_newsletter_queue' ) ) as $id ) {
        if ( get_post_meta( $id, '_jsc_newsletter_queue', true ) ) {
            jsc_newsletter_schedule( 'jsc_newsletter_send_post', array( (int) $id ) );
        }
    }
    foreach ( get_posts( array( 'post_type' => 'jsc_subscriber', 'post_status' => 'private', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_jsc_newsletter_welcome_attempts' ) ) as $id ) {
        if ( jsc_newsletter_active( $id ) && ! get_post_meta( $id, '_jsc_newsletter_welcome_sent', true ) && (int) get_post_meta( $id, '_jsc_newsletter_welcome_attempts', true ) < 3 ) {
            jsc_newsletter_schedule( 'jsc_newsletter_welcome', array( (int) $id ) );
        }
    }
}

add_action( 'wp_after_insert_post', 'jsc_newsletter_post_published', 20, 4 );
function jsc_newsletter_post_published( $id, $post, $update, $before ) {
    if ( 'post' !== $post->post_type || 'publish' !== $post->post_status || $post->post_password
        || ( $before && 'publish' === $before->post_status ) || get_post_meta( $id, '_jsc_newsletter_announced', true ) ) {
        return;
    }
    if ( ! jsc_newsletter_lock( 'publish_' . $id ) ) {
        return;
    }
    try {
        update_post_meta( $id, '_jsc_newsletter_announced', time() );
        $ids = get_posts( array( 'post_type' => 'jsc_subscriber', 'post_status' => 'private', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC' ) );
        $pending = array();
        foreach ( $ids as $subscriber ) {
            if ( jsc_newsletter_active( $subscriber ) ) {
                $pending[ $subscriber ] = 0;
            }
        }
        update_post_meta( $id, '_jsc_newsletter_queue', $pending );
        if ( $pending ) {
            jsc_newsletter_schedule( 'jsc_newsletter_send_post', array( (int) $id ), 0 );
        }
    } finally {
        delete_option( 'jsc_newsletter_lock_publish_' . $id );
    }
}

add_action( 'jsc_newsletter_send_post', 'jsc_newsletter_send_post' );
function jsc_newsletter_send_post( $id ) {
    $id = (int) $id;
    if ( ! jsc_newsletter_lock( 'post_' . $id ) ) {
        jsc_newsletter_schedule( 'jsc_newsletter_send_post', array( $id ), 660 );
        return;
    }
    try {
        $post = get_post( $id );
        $pending = get_post_meta( $id, '_jsc_newsletter_queue', true );
        if ( ! $post || 'post' !== $post->post_type || 'publish' !== $post->post_status || $post->post_password || ! is_array( $pending ) || ! $pending ) {
            wp_clear_scheduled_hook( 'jsc_newsletter_send_post', array( $id ) );
            return;
        }
        // Recover remaining recipients if a worker is interrupted during SMTP.
        jsc_newsletter_schedule( 'jsc_newsletter_send_post', array( $id ), 600 );
        $title = wp_specialchars_decode( wp_strip_all_tags( $post->post_title ), ENT_QUOTES );
        $excerpt = $post->post_excerpt ?: strip_shortcodes( $post->post_content );
        $excerpt = wp_trim_words( wp_specialchars_decode( wp_strip_all_tags( $excerpt ), ENT_QUOTES ), 45 );
        $message = "We have published a new post:\n\n" . $title . "\n\n" . $excerpt . "\n\nRead the full post: " . get_permalink( $id );
        foreach ( array_slice( $pending, 0, 10, true ) as $subscriber => $attempts ) {
            $sent_key = '_jsc_newsletter_sent_' . $subscriber;
            if ( ! jsc_newsletter_active( $subscriber ) || get_post_meta( $id, $sent_key, true ) ) {
                unset( $pending[ $subscriber ] );
            } elseif ( jsc_newsletter_mail( $subscriber, jsc_newsletter_brand() . ': New post - ' . $title, $message ) ) {
                update_post_meta( $id, $sent_key, time() );
                unset( $pending[ $subscriber ] );
            } elseif ( $attempts + 1 >= 3 ) {
                update_post_meta( $id, '_jsc_newsletter_failed_' . $subscriber, time() );
                unset( $pending[ $subscriber ] );
            } else {
                // Move failures behind fresh recipients to avoid blocking the batch.
                unset( $pending[ $subscriber ] );
                $pending[ $subscriber ] = $attempts + 1;
            }
            update_post_meta( $id, '_jsc_newsletter_queue', $pending );
        }
        wp_clear_scheduled_hook( 'jsc_newsletter_send_post', array( $id ) );
        if ( $pending ) {
            jsc_newsletter_schedule( 'jsc_newsletter_send_post', array( $id ), 300 );
        }
    } finally {
        delete_option( 'jsc_newsletter_lock_post_' . $id );
    }
}

add_filter( 'manage_jsc_subscriber_posts_columns', function ( $columns ) {
    $columns['jsc_status'] = 'Subscription';
    $columns['jsc_welcome'] = 'Welcome email';
    return $columns;
} );
add_action( 'manage_jsc_subscriber_posts_custom_column', function ( $column, $id ) {
    if ( 'jsc_status' === $column ) {
        echo jsc_newsletter_active( $id ) ? 'Subscribed' : 'Unsubscribed';
    } elseif ( 'jsc_welcome' === $column ) {
        if ( get_post_meta( $id, '_jsc_newsletter_welcome_sent', true ) ) {
            echo 'Sent';
        } elseif ( get_post_meta( $id, '_jsc_newsletter_welcome_failed', true ) ) {
            echo 'Failed — check WP Mail SMTP';
        } else {
            echo wp_next_scheduled( 'jsc_newsletter_welcome', array( (int) $id ) ) ? 'Queued for retry' : 'Not sent';
        }
    }
}, 10, 2 );

add_filter( 'manage_post_posts_columns', function ( $columns ) {
    $columns['jsc_newsletter'] = 'Newsletter';
    return $columns;
} );
add_action( 'manage_post_posts_custom_column', function ( $column, $id ) {
    if ( 'jsc_newsletter' !== $column ) {
        return;
    }
    $marker = get_post_meta( $id, '_jsc_newsletter_announced', true );
    if ( ! $marker || 'existing' === $marker ) {
        echo 'existing' === $marker ? 'Published before setup' : 'On first publication';
        return;
    }
    $sent = $failed = 0;
    foreach ( array_keys( get_post_meta( $id ) ) as $key ) {
        $sent += 0 === strpos( $key, '_jsc_newsletter_sent_' ) ? 1 : 0;
        $failed += 0 === strpos( $key, '_jsc_newsletter_failed_' ) ? 1 : 0;
    }
    $queue = get_post_meta( $id, '_jsc_newsletter_queue', true );
    echo esc_html( sprintf( 'Sent: %d · Queued: %d · Failed: %d', $sent, is_array( $queue ) ? count( $queue ) : 0, $failed ) );
}, 10, 2 );

register_deactivation_hook( __FILE__, function () {
    wp_unschedule_hook( 'jsc_newsletter_welcome' );
    wp_unschedule_hook( 'jsc_newsletter_send_post' );
} );
