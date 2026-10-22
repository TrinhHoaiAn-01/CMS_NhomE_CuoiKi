<?php
/** Run with: wp eval-file wp-content/plugins/jobscout-newsletter/tests/integration.php
 * All mail is intercepted. Core tables are copied into connection-local temporary tables.
 */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    exit;
}
global $wpdb;
$tables = $wpdb->tables( 'all' );
$temporary_tables = array();
$test_prefix = 'jsc_nl_test_' . substr( md5( uniqid( '', true ) ), 0, 10 ) . '_';
$checks = 0;
$failed = false;
$mail = array();
$fail_mail = false;
$capture = function ( $result, $args ) use ( &$mail, &$fail_mail ) {
    $mail[] = $args;
    return ! $fail_mail;
};
$check = function ( $ok, $message ) use ( &$checks ) {
    if ( ! $ok ) {
        throw new RuntimeException( $message );
    }
    $checks++;
};
add_filter( 'pre_wp_mail', $capture, PHP_INT_MAX, 2 );
try {
    foreach ( $tables as $property => $table ) {
        $temporary = $test_prefix . $property;
        if ( false === $wpdb->query( "CREATE TEMPORARY TABLE `$temporary` LIKE `$table`" ) ) {
            throw new RuntimeException( 'Unable to create isolated test table.' );
        }
        $temporary_tables[] = $temporary;
        if ( false === $wpdb->query( "INSERT INTO `$temporary` SELECT * FROM `$table`" ) ) {
            throw new RuntimeException( 'Unable to copy isolated test data.' );
        }
        $wpdb->$property = $temporary;
    }
    wp_cache_flush();
    $check( 'error' === jsc_newsletter_subscribe( 'invalid' ), 'Invalid address accepted' );
    $check( ! $mail, 'Invalid submission sent an email' );
    $check( 'saved' === jsc_newsletter_subscribe( 'newsletter-check@example.test' ), 'Subscription failed' );
    $subscriber = get_page_by_path( hash( 'sha256', 'newsletter-check@example.test' ), OBJECT, 'jsc_subscriber' )->ID;
    $check( jsc_newsletter_active( $subscriber ), 'New subscriber inactive' );
    $check( 1 === count( $mail ), 'Missing welcome email' );
    $check( false !== strpos( $mail[0]['message'], 'has been subscribed' ), 'Welcome missing confirmation' );
    $check( false !== strpos( $mail[0]['message'], 'Unsubscribe:' ), 'Welcome missing unsubscribe link' );
    $check( (bool) get_post_meta( $subscriber, '_jsc_newsletter_welcome_sent', true ), 'Welcome result not saved' );
    $check( ! wp_next_scheduled( 'jsc_newsletter_welcome', array( (int) $subscriber ) ), 'Successful welcome still queued' );
    $check( 'saved' === jsc_newsletter_subscribe( 'NEWSLETTER-CHECK@example.test' ), 'Duplicate failed' );
    $check( 1 === count( $mail ), 'Duplicate sent another welcome' );
    $check( 'saved' === jsc_newsletter_subscribe( 'newsletter-unsubscribed@example.test' ), 'Second subscription failed' );
    $unsubscribed = get_page_by_path( hash( 'sha256', 'newsletter-unsubscribed@example.test' ), OBJECT, 'jsc_subscriber' )->ID;
    update_post_meta( $unsubscribed, '_jsc_newsletter_status', 'unsubscribed' );

    $post = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'draft', 'post_title' => 'Newsletter integration fixture', 'post_content' => 'A new article for subscribers.' ), true );
    $check( ! is_wp_error( $post ), 'Could not create draft' );
    $check( ! get_post_meta( $post, '_jsc_newsletter_announced', true ), 'Draft announced' );
    wp_update_post( array( 'ID' => $post, 'post_status' => 'publish' ) );
    $queue = get_post_meta( $post, '_jsc_newsletter_queue', true );
    $check( isset( $queue[ $subscriber ] ), 'Published post missing subscriber' );
    $check( ! isset( $queue[ $unsubscribed ] ), 'Unsubscribed recipient queued' );
    $check( (bool) wp_next_scheduled( 'jsc_newsletter_send_post', array( (int) $post ) ), 'Post not scheduled' );
    $before = count( $mail );
    jsc_newsletter_send_post( $post );
    $check( count( $mail ) > $before, 'Announcement not sent' );
    $check( ! get_post_meta( $post, '_jsc_newsletter_queue', true ), 'Completed queue not emptied' );
    $check( get_post_meta( $post, '_jsc_newsletter_sent_' . $subscriber, true ), 'Delivery marker missing' );
    $announcement = $mail[ $before ];
    $check( false !== strpos( $announcement['message'], get_permalink( $post ) ), 'Post link missing' );
    foreach ( array_slice( $mail, $before ) as $delivery ) {
        $check( is_string( $delivery['to'] ) && false === strpos( $delivery['to'], ',' ), 'Addresses exposed in bulk message' );
        $check( 'newsletter-unsubscribed@example.test' !== $delivery['to'], 'Unsubscribed address received message' );
    }
    $before = count( $mail );
    jsc_newsletter_send_post( $post );
    wp_update_post( array( 'ID' => $post, 'post_title' => 'Edited fixture' ) );
    wp_update_post( array( 'ID' => $post, 'post_status' => 'draft' ) );
    wp_update_post( array( 'ID' => $post, 'post_status' => 'publish' ) );
    jsc_newsletter_send_post( $post );
    $check( $before === count( $mail ), 'Edit / republish / repeated worker sent duplicates' );

    $protected = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Protected fixture', 'post_password' => 'private' ) );
    $page = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Page fixture' ) );
    $check( ! get_post_meta( $protected, '_jsc_newsletter_announced', true ), 'Protected post announced' );
    $check( ! get_post_meta( $page, '_jsc_newsletter_announced', true ), 'Page announced as post' );

    $old_token = get_post_meta( $unsubscribed, '_jsc_newsletter_token', true );
    $before = count( $mail );
    $check( 'saved' === jsc_newsletter_subscribe( 'newsletter-unsubscribed@example.test' ), 'Resubscribe failed' );
    $check( jsc_newsletter_active( $unsubscribed ), 'Resubscribe inactive' );
    $check( count( $mail ) === $before + 1, 'Resubscribe welcome missing' );
    $check( $old_token !== get_post_meta( $unsubscribed, '_jsc_newsletter_token', true ), 'Resubscribe did not rotate token' );

    $die_result = array();
    $die_handler = function () use ( &$die_result ) {
        return function ( $message, $title, $args ) use ( &$die_result ) {
            $die_result = array( 'message' => $message, 'response' => $args['response'] );
            throw new RuntimeException( 'newsletter-test-response' );
        };
    };
    $request_before = array( $_GET, $_POST, $_SERVER['REQUEST_METHOD'] ?? null );
    add_filter( 'wp_die_handler', $die_handler, PHP_INT_MAX );
    try {
        $input = array( 'subscriber' => $unsubscribed, 'token' => get_post_meta( $unsubscribed, '_jsc_newsletter_token', true ) );
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = $input;
        try { jsc_newsletter_handle_unsubscribe(); } catch ( RuntimeException $error ) {
            if ( 'newsletter-test-response' !== $error->getMessage() ) { throw $error; }
        }
        $check( 200 === $die_result['response'], 'Unsubscribe landing page failed' );
        $check( jsc_newsletter_active( $unsubscribed ), 'Email link GET unsubscribed without confirmation' );
        $check( false !== strpos( $die_result['message'], 'method="post"' ), 'Unsubscribe confirmation form missing' );
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array_merge( $input, array( 'token' => 'wrong' ) );
        try { jsc_newsletter_handle_unsubscribe(); } catch ( RuntimeException $error ) {
            if ( 'newsletter-test-response' !== $error->getMessage() ) { throw $error; }
        }
        $check( 403 === $die_result['response'], 'Invalid unsubscribe token accepted' );
        $check( jsc_newsletter_active( $unsubscribed ), 'Invalid token changed subscription' );
        $_POST = $input;
        try { jsc_newsletter_handle_unsubscribe(); } catch ( RuntimeException $error ) {
            if ( 'newsletter-test-response' !== $error->getMessage() ) { throw $error; }
        }
        $check( 200 === $die_result['response'], 'Unsubscribe POST failed' );
        $check( ! jsc_newsletter_active( $unsubscribed ), 'Confirmed unsubscribe did not remove recipient' );
    } finally {
        list( $_GET, $_POST, $_SERVER['REQUEST_METHOD'] ) = $request_before;
        remove_filter( 'wp_die_handler', $die_handler, PHP_INT_MAX );
    }

    $fail_mail = true;
    $check( 'mail_pending' === jsc_newsletter_subscribe( 'newsletter-retry@example.test' ), 'SMTP failure reported as sent' );
    $retry = get_page_by_path( hash( 'sha256', 'newsletter-retry@example.test' ), OBJECT, 'jsc_subscriber' )->ID;
    $check( wp_next_scheduled( 'jsc_newsletter_welcome', array( (int) $retry ) ), 'Welcome retry missing' );
    $fail_mail = false;
    jsc_newsletter_welcome( $retry );
    $check( get_post_meta( $retry, '_jsc_newsletter_welcome_sent', true ), 'Welcome retry failed' );

    update_post_meta( $post, '_jsc_newsletter_queue', array( $subscriber => 0 ) );
    delete_post_meta( $post, '_jsc_newsletter_sent_' . $subscriber );
    $fail_mail = true;
    jsc_newsletter_send_post( $post );
    $queue = get_post_meta( $post, '_jsc_newsletter_queue', true );
    $check( 1 === $queue[ $subscriber ], 'Post retry attempt not saved' );
    $check( wp_next_scheduled( 'jsc_newsletter_send_post', array( (int) $post ) ), 'Post retry missing' );
    $fail_mail = false;
    jsc_newsletter_send_post( $post );
    $check( get_post_meta( $post, '_jsc_newsletter_sent_' . $subscriber, true ), 'Post retry failed' );

    update_post_meta( $post, '_jsc_newsletter_queue', array( $unsubscribed => 0 ) );
    delete_post_meta( $post, '_jsc_newsletter_sent_' . $unsubscribed );
    update_post_meta( $unsubscribed, '_jsc_newsletter_status', 'unsubscribed' );
    $before = count( $mail );
    jsc_newsletter_send_post( $post );
    $check( $before === count( $mail ), 'Queued recipient still mailed after unsubscribe' );
    $check( ! get_post_meta( $post, '_jsc_newsletter_queue', true ), 'Unsubscribed recipient not removed from queue' );

    update_post_meta( $post, '_jsc_newsletter_queue', array( $subscriber => 0 ) );
    delete_post_meta( $post, '_jsc_newsletter_sent_' . $subscriber );
    $fail_mail = true;
    jsc_newsletter_send_post( $post );
    jsc_newsletter_send_post( $post );
    jsc_newsletter_send_post( $post );
    $check( ! get_post_meta( $post, '_jsc_newsletter_queue', true ), 'Exhausted retries still queued' );
    $check( get_post_meta( $post, '_jsc_newsletter_failed_' . $subscriber, true ), 'Failed delivery not recorded' );
    WP_CLI::success( $checks . ' newsletter checks passed; all emails intercepted.' );
} catch ( Throwable $error ) {
    $failed = true;
    WP_CLI::warning( $error->getMessage() . ' (line ' . $error->getLine() . ')' );
} finally {
    foreach ( $tables as $property => $table ) {
        $wpdb->$property = $table;
    }
    foreach ( $temporary_tables as $temporary ) {
        $wpdb->query( "DROP TEMPORARY TABLE IF EXISTS `$temporary`" );
    }
    wp_cache_flush();
    remove_filter( 'pre_wp_mail', $capture, PHP_INT_MAX );
}
if ( $failed ) {
    WP_CLI::halt( 1 );
}
