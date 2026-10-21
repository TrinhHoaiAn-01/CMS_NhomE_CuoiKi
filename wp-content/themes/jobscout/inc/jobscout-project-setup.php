<?php
/** Reproducible project setup. Run with `wp eval-file` from the project root. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    exit( 'Run this file using WP-CLI.' );
}
if ( ! post_type_exists( 'job_listing' ) ) {
    WP_CLI::error( 'Install and activate WP Job Manager first.' );
}
update_option( 'job_manager_enable_categories', 1 );
update_option( 'job_manager_enable_types', 1 );
update_option( 'job_manager_enable_default_category_multiselect', 0 );
update_option( 'job_manager_enable_registration', 1 );
update_option( 'job_manager_user_requires_account', 1 );
update_option( 'job_manager_submission_requires_approval', 1 );
function jsc_setup_page( $slug, $title, $content = '' ) {
    $page = get_page_by_path( $slug );
    if ( $page ) { return $page->ID; }
    return wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title, 'post_content' => $content ), true );
}
$jsc_pages = array();
foreach ( array( 'home' => 'Home', 'jobs' => 'All Jobs', 'news' => 'News', 'about' => 'About', 'contact' => 'Contact Us', 'submit-job' => 'Submit a Job' ) as $slug => $title ) {
    $jsc_pages[ $slug ] = jsc_setup_page( $slug, $title, 'submit-job' === $slug ? '[submit_job_form]' : '' );
}
update_option( 'job_manager_submit_job_form_page_id', $jsc_pages['submit-job'] );
update_option( 'job_manager_jobs_page_id', $jsc_pages['jobs'] );
if ( ! wp_get_nav_menu_object( 'JobScout Primary' ) ) {
    $menu = wp_create_nav_menu( 'JobScout Primary' );
    foreach ( array( 'home' => 'Home', 'jobs' => 'Jobs', 'news' => 'News', 'about' => 'About', 'contact' => 'Contact' ) as $slug => $label ) {
        wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => $label, 'menu-item-object-id' => $jsc_pages[ $slug ], 'menu-item-object' => 'page', 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
    }
    $locations = get_theme_mod( 'nav_menu_locations', array() );
    $locations['primary'] = $menu;
    set_theme_mod( 'nav_menu_locations', $locations );
}
WP_CLI::success( 'JobScout project configuration is ready.' );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $jsc_pages['home'] );
if ( ! trim( get_post_field( 'post_content', $jsc_pages['home'] ) ) ) {
    wp_update_post( array( 'ID' => $jsc_pages['home'], 'post_content' => '<!-- wp:paragraph --><p>Plan Do See Global is a hospitality group founded in Japan and rooted in “Omotenashi”, the Japanese principle of selfless hospitality. We strive to deliver unforgettable and bespoke experiences, to understand local cultures like natives, to provide service that is warm but not intrusive, and to foresee our guests’ every need at all times. That is our sole mission and purpose.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>We are experts in all stages of project development: concept, design, implementation and management.<br>We love to find unique ways to create experiences that surprise and delight guests and customers.</p><!-- /wp:paragraph -->' ) );
}
update_option( 'blogname', 'Plan Do See Global Inc.' );
update_option( 'blogdescription', 'Share Omotenashi with the world' );
update_option( 'permalink_structure', '/%postname%/' );
flush_rewrite_rules( false );

foreach ( require __DIR__ . '/project-jobs.php' as $order => $job ) {
    list( $slug, $title, $company, $city, $category, $logo, $intro, $highlights ) = $job;
    $existing_job = get_page_by_path( $slug, OBJECT, 'job_listing' );
    if ( $existing_job ) {
        if ( ! wp_get_object_terms( $existing_job->ID, 'job_listing_type' ) ) { wp_set_object_terms( $existing_job->ID, 'Fulltime', 'job_listing_type' ); }
        continue;
    }
    $id = wp_insert_post( array( 'post_type' => 'job_listing', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug, 'menu_order' => $order, 'post_date' => '2022-10-20 09:00:00', 'post_content' => '<h2>Overview about Company</h2><p>' . esc_html( $intro ) . '</p>', 'post_excerpt' => $intro ), true );
    if ( is_wp_error( $id ) ) { WP_CLI::error( $id->get_error_message() ); }
    foreach ( array( '_company_name' => $company, '_company_logo' => jsc_asset( 'company-' . $logo . '.jpg' ), '_job_location' => $city, '_filled' => '0', '_featured' => '1', '_application' => add_query_arg( 'job', $id, jsc_url( 'contact' ) ) . '#apply', 'jsc_highlights' => $highlights ) as $key => $value ) { update_post_meta( $id, $key, $value ); }
    wp_set_object_terms( $id, 'Fulltime', 'job_listing_type' );
    wp_set_object_terms( $id, $category, 'job_listing_category' );
}

if ( ! term_exists( 'pds-news', 'category' ) ) { wp_insert_term( 'PDS News', 'category', array( 'slug' => 'pds-news' ) ); }
$news_category = get_term_by( 'slug', 'pds-news', 'category' );
foreach ( require __DIR__ . '/project-news.php' as $order => $story ) {
    list( $slug, $title, $image, $excerpt, $intro, $body ) = $story;
    if ( get_page_by_path( $slug, OBJECT, 'post' ) ) { continue; }
    $id = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug, 'post_excerpt' => $excerpt, 'menu_order' => $order, 'post_date' => '2022-10-20 09:00:00', 'post_content' => '<!-- wp:paragraph --><p>' . esc_html( $intro ) . '</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>' . esc_html( $body ) . '</p><!-- /wp:paragraph -->', 'post_category' => array( $news_category->term_id ) ), true );
    if ( is_wp_error( $id ) ) { WP_CLI::error( $id->get_error_message() ); }
    update_post_meta( $id, 'jsc_image', $image );
    update_post_meta( $id, 'jsc_location', 'Ho Chi Minh City' );
}


if ( ! get_post_meta( $jsc_pages['about'], 'jsc_principles_seeded', true ) ) {
    wp_update_post( array( 'ID' => $jsc_pages['about'], 'post_content' => '<!-- wp:heading {"level":3} --><h3>Our Vision</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Create hotels and restaurants around the world that offer memorable experiences while building a lasting, positive relationship together with our guests, partners, team members and communities.</p><!-- /wp:paragraph --><!-- wp:heading {"level":3} --><h3>Our Mission</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Share “Omotenashi” with the world.</p><!-- /wp:paragraph --><!-- wp:heading {"level":3} --><h3>Our Core Value</h3><!-- /wp:heading --><!-- wp:paragraph --><p>“If I were the guest…”<br>To provide guests with the hospitality you would want to receive as a guest.</p><!-- /wp:paragraph -->' ) );
    update_post_meta( $jsc_pages['about'], 'jsc_principles_seeded', 1 );
}



if ( ! metadata_exists( 'post', $jsc_pages['about'], 'jsc_properties' ) ) {
    update_post_meta( $jsc_pages['about'], 'jsc_properties', "Plan Do See developed and operates 17 properties worldwide including 3 award-winning resorts in Japan; 17 restaurants of diverse cuisines in cities including New York, Miami and Los Angeles, and other countries including Japan, Indonesia, Malaysia and Bali.\n\nEach venue carries its own concept and design. Many of them are originally historical landmarks that were loved by the local people." );
}



foreach ( array( 'founded' => 'April 1993', 'office' => 'Marunouchi 2-1-1, Chiyoda, Tokyo', 'capital' => '100,000,000 JPY', 'ceo' => 'Yutaka Noda', 'employees' => 'Full time: 830 / Total: 1,600' ) as $key => $value ) {
    if ( ! metadata_exists( 'post', $jsc_pages['about'], 'jsc_' . $key ) ) { update_post_meta( $jsc_pages['about'], 'jsc_' . $key, $value ); }
}



foreach ( require __DIR__ . '/project-news-additional.php' as $order => $story ) {
    list( $slug, $title, $image, $excerpt, $body ) = $story;
    if ( get_page_by_path( $slug, OBJECT, 'post' ) ) { continue; }
    $id = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug, 'post_excerpt' => $excerpt, 'menu_order' => $order + 4, 'post_date' => '2022-10-19 09:00:00', 'post_content' => '<!-- wp:paragraph --><p>' . esc_html( $body ) . '</p><!-- /wp:paragraph -->', 'post_category' => array( $news_category->term_id ) ), true );
    if ( is_wp_error( $id ) ) { WP_CLI::error( $id->get_error_message() ); }
    update_post_meta( $id, 'jsc_image', $image );
    update_post_meta( $id, 'jsc_location', 'Ho Chi Minh City' );
}



foreach ( array( 'project-development' => 'Project Development', 'restaurant-hotel-management' => 'Hotel Operations', 'hospitality-consulting' => 'Hospitality Consulting', 'venue-interior-design' => 'Design' ) as $slug => $category_name ) {
    $story = get_page_by_path( $slug, OBJECT, 'post' );
    $term = term_exists( $category_name, 'category' );
    if ( ! $term ) { $term = wp_insert_term( $category_name, 'category', array( 'parent' => $news_category->term_id ) ); }
    if ( $story && ! is_wp_error( $term ) ) { wp_set_post_categories( $story->ID, array( (int) $term['term_id'] ), true ); }
}



foreach ( require __DIR__ . '/project-jobs-additional.php' as $order => $job ) {
    list( $slug, $title, $company, $city, $category, $logo, $intro ) = $job;
    if ( get_page_by_path( $slug, OBJECT, 'job_listing' ) ) { continue; }
    $id = wp_insert_post( array( 'post_type' => 'job_listing', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug, 'menu_order' => $order + 6, 'post_date' => '2022-10-19 09:00:00', 'post_content' => '<h2>Overview about Company</h2><p>' . esc_html( $intro ) . '</p>', 'post_excerpt' => $intro ), true );
    if ( is_wp_error( $id ) ) { WP_CLI::error( $id->get_error_message() ); }
    foreach ( array( '_company_name' => $company, '_company_logo' => jsc_asset( 'company-' . $logo . '.jpg' ), '_job_location' => $city, '_filled' => '0', '_featured' => '0', '_application' => add_query_arg( 'job', $id, jsc_url( 'contact' ) ) . '#apply', 'jsc_highlights' => $intro . "\nWork with a collaborative team focused on guest care\nDevelop practical hospitality and communication skills" ) as $key => $value ) { update_post_meta( $id, $key, $value ); }
    wp_set_object_terms( $id, 'Fulltime', 'job_listing_type' );
    wp_set_object_terms( $id, $category, 'job_listing_category' );
}



$all_jobs = get_posts( array( 'post_type' => 'job_listing', 'numberposts' => -1, 'post_status' => 'publish' ) );
foreach ( $all_jobs as $job_post ) {
    if ( get_post_meta( $job_post->ID, 'jsc_details_seeded', true ) ) { continue; }
    $intro = $job_post->post_excerpt;
    $company = get_post_meta( $job_post->ID, '_company_name', true );
    $content = '<h2>Overview about Company</h2><p>' . esc_html( $intro ) . ' At ' . esc_html( $company ) . ', we believe that hospitality begins with seeing the world through the eyes of another person. Our work brings together thoughtful service, a sense of place and respect for the communities around us. Every team member helps create a warm welcome and an experience that feels personal.</p><p>We work across departments to make each part of the guest journey feel natural. From planning the day to responding to an unexpected request, our teams communicate clearly and pay attention to the details. We value practical ideas, a willingness to learn and the care that colleagues show one another during a busy service.</p>';
    $content .= '<h2>Our Key Skills</h2><p>This role calls for clear communication, thoughtful organisation and a strong commitment to guest care. You should be comfortable working with people from different backgrounds, listening carefully and finding practical solutions. Relevant experience in hospitality is valuable, along with attention to detail and the ability to work calmly as part of a team.</p><p>We look for people who take responsibility for their work and share what they know. A positive approach, respect for colleagues and the curiosity to keep improving are as important as technical skills.</p>';
    $content .= '<h2>Why You’ll Love Working Here</h2><p>You will join colleagues who care about their craft and take pride in making people feel welcome. We encourage learning through daily practice, shared feedback and the opportunity to work alongside experienced hospitality professionals. Team members are invited to contribute ideas and help shape a better experience for guests and one another.</p><p>Each property has its own character, shaped by local culture, architecture and the people who bring it to life. Working here is a chance to understand that story and become part of it. The recruitment team can explain the working schedule, training, compensation and benefits that apply to this position during the selection process.</p>';
    wp_update_post( array( 'ID' => $job_post->ID, 'post_content' => $content ) );
    update_post_meta( $job_post->ID, 'jsc_details_seeded', 1 );
}


foreach ( $all_jobs as $job_post ) { if ( ! metadata_exists( 'post', $job_post->ID, 'jsc_rating' ) ) { update_post_meta( $job_post->ID, 'jsc_rating', '4.0' ); } }


$editorial_posts = get_posts( array( 'post_type' => 'post', 'category_name' => 'pds-news', 'numberposts' => -1, 'post_status' => 'publish' ) );
foreach ( $editorial_posts as $story_post ) {
    if ( get_post_meta( $story_post->ID, 'jsc_article_intro_seeded', true ) ) { continue; }
    $content = $story_post->post_content;
    $content .= '<!-- wp:paragraph --><p>Our approach begins by listening. We take time to understand the people who will use a place, the team who will care for it and the neighbourhood it belongs to. Those conversations turn an idea into something useful, welcoming and connected to its surroundings.</p><!-- /wp:paragraph --><!-- jsc:first-image -->';
    wp_update_post( array( 'ID' => $story_post->ID, 'post_content' => $content ) );
    update_post_meta( $story_post->ID, 'jsc_article_intro_seeded', 1 );
}



foreach ( $editorial_posts as $story_post ) {
    if ( get_post_meta( $story_post->ID, 'jsc_article_photo_one', true ) ) { continue; }
    $photo = '<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="' . esc_url( jsc_asset( 'street.jpg' ) ) . '" alt="A red corner restaurant on a quiet Japanese street" width="640" height="360"></figure><!-- /wp:image --><!-- jsc:middle-content -->';
    wp_update_post( array( 'ID' => $story_post->ID, 'post_content' => str_replace( '<!-- jsc:first-image -->', $photo, get_post_field( 'post_content', $story_post->ID ) ) ) );
    update_post_meta( $story_post->ID, 'jsc_article_photo_one', 1 );
}



foreach ( $editorial_posts as $story_post ) {
    if ( get_post_meta( $story_post->ID, 'jsc_article_photo_two', true ) ) { continue; }
    $middle = '<!-- wp:paragraph --><p>A successful experience brings many disciplines together. Design gives a space its character, operations make it practical and service gives it warmth. When those conversations happen together, the result feels more natural to guests and easier for teams to deliver. We encourage people to notice how a place is used throughout the day and to share ideas that make it work better.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Local culture is a starting point rather than a finishing touch. The materials we choose, the food we serve and the stories we share can all help guests build a connection with the destination. Working with local makers and understanding the history of a neighbourhood gives a project depth. Small decisions made with care can become the details people remember long after their visit.</p><!-- /wp:paragraph -->';
    $middle .= '<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="' . esc_url( jsc_asset( 'sakura.jpg' ) ) . '" alt="Warm lanterns beneath cherry blossoms at dusk in Japan" width="640" height="360"></figure><!-- /wp:image -->';
    $middle .= '<!-- wp:paragraph --><p>Hospitality continues to evolve, but its purpose stays personal. We want people to feel welcome, comfortable and understood. That means staying curious, learning from feedback and making room for different perspectives. Whether we are developing a new venue or improving an existing service, we return to a simple question: what would we hope to experience if we were the guest? The answer helps us choose the next thoughtful step.</p><!-- /wp:paragraph -->';
    wp_update_post( array( 'ID' => $story_post->ID, 'post_content' => str_replace( '<!-- jsc:middle-content -->', $middle, get_post_field( 'post_content', $story_post->ID ) ) ) );
    update_post_meta( $story_post->ID, 'jsc_article_photo_two', 1 );
}

