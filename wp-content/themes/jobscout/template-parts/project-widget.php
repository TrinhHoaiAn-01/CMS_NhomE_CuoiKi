<?php
/** Section markup; all presentation values come from the widget instance. */
defined( 'ABSPATH' ) || exit;
$title = $w['title'] ?? '';
$w['_uid'] = $uid;
switch ( $kind ) :
case 'hero':
    $layout = $w['layout'] ?? 'home';
    $class = 'home' === $layout ? 'jsc-home-hero' : 'jsc-page-hero jsc-' . $layout . '-hero'; ?>
    <section class="<?php echo esc_attr( $class ); ?>" aria-labelledby="<?php echo esc_attr( $uid ); ?>"<?php echo jsc_widget_background( $w['image'] ?? 0 ); ?>>
    <?php if ( 'home' === $layout ) : ?><div class="jsc-hero-copy"><?php endif; ?>
    <h1 id="<?php echo esc_attr( $uid ); ?>"><?php echo nl2br( esc_html( $title ) ); ?></h1>
    <?php if ( ! empty( $w['description'] ) ) : ?><p><?php echo esc_html( $w['description'] ); ?></p><?php endif; ?>
    <?php if ( 'home' === $layout ) { get_template_part( 'template-parts/project-job-search', null, $w ); echo '</div>'; } ?>
    </section>
    <?php break;
case 'content':
    $layout = $w['layout'] ?? 'plain';
    $text = jsc_widget_text( $w );
    if ( ! $title && '' === trim( $text ) && empty( $w['image'] ) && empty( $w['button'] ) ) { break; }
    $classes = array( 'plain' => 'jsc-section', 'career' => 'jsc-career', 'vision' => 'jsc-about-vision jsc-section', 'properties' => 'jsc-about-properties', 'address' => 'jsc-contact-address' );
    ?>
    <section class="<?php echo esc_attr( $classes[ $layout ] ?? 'jsc-section' ); ?>"<?php if ( ! empty( $w['anchor'] ) ) { echo ' id="' . esc_attr( sanitize_title( $w['anchor'] ) ) . '"'; } ?><?php if ( 'career' === $layout ) { echo jsc_widget_background( $w['image'] ?? 0 ); } ?>>
    <div class="<?php echo 'career' === $layout ? 'jsc-career-copy' : 'jsc-container'; ?>">
    <?php if ( $title ) : ?><h2 class="jsc-title"><?php echo esc_html( $title ); ?></h2><?php endif; ?>
    <?php if ( 'vision' === $layout ) : ?><div class="jsc-about-vision-grid"><figure><?php echo wp_get_attachment_image( absint( $w['image'] ?? 0 ), 'large' ); ?></figure><div class="jsc-about-principles"><?php echo $text; ?></div></div>
    <?php else : echo $text; endif; ?>
    <?php if ( ! empty( $w['button'] ) && ! empty( $w['url'] ) ) : ?><a class="jsc-button jsc-career-button" href="<?php echo esc_url( $w['url'] ); ?>"><?php echo esc_html( $w['button'] ); ?></a><?php endif; ?>
    </div></section>
    <?php break;
case 'listings':
    $list_kind = $w['kind'] ?? 'job'; $mode = $w['mode'] ?? 'featured';
    $count = min( 48, max( 1, absint( $w['count'] ?? 6 ) ) );
    if ( 'archive' === $mode && 'job' === $list_kind ) { get_template_part( 'template-parts/project-jobs-archive', null, $w ); break; }
    $page = 'archive' === $mode && isset( $_GET['news_page'] ) && is_scalar( $_GET['news_page'] ) ? max( 1, absint( $_GET['news_page'] ) ) : 1;
    $query_args = array( 'posts_per_page' => $count, 'paged' => $page );
    if ( 'related' === $mode ) { $query_args['post__not_in'] = array( get_queried_object_id() ); }
    if ( 'news' === $list_kind ) { $query_args['category_name'] = $w['category'] ?? 'pds-news'; }
    $query = 'news' === $list_kind ? jsc_news( $query_args ) : jsc_jobs( $query_args );
    $class = 'related' === $mode ? 'jsc-related-' . ( 'news' === $list_kind ? 'news' : 'jobs' ) : ( 'job' === $list_kind ? 'jsc-top-jobs jsc-section' : 'jsc-home-news jsc-section' ); ?>
    <section class="<?php echo esc_attr( $class ); ?>"><div class="jsc-container">
    <?php if ( $title ) : ?><h2 class="jsc-title"><?php echo esc_html( $title ); ?></h2><?php endif; ?>
    <?php jsc_cards( $query, $list_kind ); ?>
    <?php if ( 'archive' === $mode && $query->max_num_pages > 1 ) : ?><nav class="jsc-pagination" aria-label="News pages"><?php echo wp_kses_post( paginate_links( array( 'base' => add_query_arg( 'news_page', '%#%', jsc_url( 'news' ) ), 'format' => '', 'current' => $page, 'total' => $query->max_num_pages, 'prev_text' => 'Previous', 'next_text' => 'Next' ) ) ); ?></nav><?php endif; ?>
    <?php if ( ! empty( $w['button'] ) && ! empty( $w['url'] ) ) : ?><div class="jsc-actions"><a class="jsc-button outline" href="<?php echo esc_url( $w['url'] ); ?>"><?php echo esc_html( $w['button'] ); ?></a></div><?php endif; ?>
    </div></section>
    <?php break;
case 'profile': ?>
    <section class="jsc-about-profile jsc-section"><div class="jsc-container jsc-profile-grid"><dl>
    <?php foreach ( array( 'founded', 'office', 'capital', 'ceo', 'employees' ) as $key ) : ?><div><dt><?php echo esc_html( $w[ $key . '_label' ] ?? '' ); ?></dt><dd><?php echo esc_html( $w[ $key ] ?? '' ); ?></dd></div><?php endforeach; ?>
    </dl><?php echo wp_get_attachment_image( absint( $w['image'] ?? 0 ), 'large', false, array( 'loading' => 'lazy' ) ); ?></div></section>
    <?php break;
case 'contacts': ?>
    <section class="jsc-contact-columns"><div class="jsc-container jsc-contact-grid">
    <section class="jsc-contact-employers"><h2><?php echo esc_html( $title ); ?></h2><p><?php echo esc_html( $w['intro'] ?? '' ); ?></p>
    <?php foreach ( array( 'one', 'two' ) as $key ) : ?><div class="jsc-contact-method"><h3><?php echo esc_html( $w[ 'city_' . $key ] ?? '' ); ?></h3><?php jsc_widget_phone( $w[ 'phone_' . $key ] ?? '' ); ?></div><?php endforeach; ?>
    <p class="jsc-callback"><a href="<?php echo esc_url( add_query_arg( 'enquiry', 'employer', jsc_url( 'contact' ) ) . '#apply' ); ?>"><?php echo esc_html( $w['callback'] ?? '' ); ?></a><br><?php echo esc_html( $w['closing'] ?? '' ); ?></p></section>
    <section class="jsc-contact-jobseekers"><h2><?php echo esc_html( $w['seekers_title'] ?? '' ); ?></h2><?php echo wpautop( wp_kses_post( $w['seekers_body'] ?? '' ) ); ?><div class="jsc-contact-method"><h3><?php echo esc_html( $w['phone_label'] ?? '' ); ?></h3><?php jsc_widget_phone( $w['phone_jobs'] ?? '' ); ?></div></section>
    </div></section>
    <?php break;
case 'application':
    get_template_part( 'template-parts/project-application-form', null, $w ); break;
case 'breadcrumbs':
    jsc_breadcrumbs( $w['section'] ?? 'jobs', $w['section_label'] ?? '', $title ); break;
case 'summary':
    get_template_part( 'template-parts/project-detail-summary', null, $w ); break;
case 'article': ?>
    <article class="jsc-prose <?php echo is_singular( 'job_listing' ) ? 'jsc-job-description' : 'jsc-news-prose'; ?>"><?php if ( is_singular( 'job_listing' ) && function_exists( 'wpjm_the_job_description' ) ) { wpjm_the_job_description(); } else { the_content(); } ?></article>
    <?php break;
case 'location':
    if ( ! is_singular( 'job_listing' ) ) { break; } ?>
    <section class="jsc-job-location jsc-prose"><h2><?php echo esc_html( $title ); ?></h2><p><?php echo esc_html( get_post_meta( get_the_ID(), '_job_location', true ) ); ?></p><?php echo wpautop( esc_html( $w['body'] ?? '' ) ); ?></section>
    <?php break;
case 'company':
    if ( is_singular( 'job_listing' ) ) { get_template_part( 'template-parts/project-company', null, $w ); } break;
case 'newsletter':
    get_template_part( 'template-parts/project-newsletter', null, $w ); break;
endswitch;
