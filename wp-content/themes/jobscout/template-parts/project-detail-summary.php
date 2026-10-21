<?php /** Shared detail summary; native listing data. */
$id = get_the_ID(); $is_news = 'post' === get_post_type(); $logo = $is_news ? ( has_post_thumbnail() ? get_the_post_thumbnail_url( $id, 'medium' ) : ( get_post_meta( $id, 'jsc_image', true ) ? jsc_asset( get_post_meta( $id, 'jsc_image', true ) ) : '' ) ) : get_post_meta( $id, '_company_logo', true );
$types = $is_news ? array() : get_the_terms( $id, 'job_listing_type' ); $categories = get_the_terms( $id, $is_news ? 'category' : 'job_listing_category' ); ?>
<header class="jsc-detail-summary">
<?php if ( $logo ) : ?><img class="jsc-detail-image" src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $is_news ? get_the_title() : get_post_meta( $id, '_company_name', true ) ); ?>" width="140" height="140"><?php endif; ?>
<div class="jsc-detail-heading"><h1><?php the_title(); ?></h1><p class="jsc-created"><?php echo $is_news ? 'Posted: ' : 'Created: '; ?><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'M d, Y' ) ); ?></time></p>
<ul class="jsc-meta" aria-label="<?php echo $is_news ? 'Article information' : 'Job information'; ?>"><?php if ( $types && ! is_wp_error( $types ) ) : ?><li><?php echo esc_html( $types[0]->name ); ?></li><?php endif; ?><?php if ( $categories && ! is_wp_error( $categories ) ) : ?><li><?php if ( $is_news ) : ?><a href="<?php echo esc_url( get_term_link( $categories[0] ) ); ?>"><?php echo esc_html( $categories[0]->name ); ?></a><?php else : echo esc_html( $categories[0]->name ); endif; ?></li><?php endif; ?><li><?php echo esc_html( get_post_meta( $id, $is_news ? 'jsc_location' : '_job_location', true ) ); ?></li></ul></div>
<div class="jsc-detail-buttons"><?php get_template_part( 'template-parts/project-share' ); ?>
<?php $application = function_exists( 'get_the_job_application_method' ) ? get_the_job_application_method() : null;
$closed = ( function_exists( 'is_position_filled' ) && is_position_filled() ) || 'expired' === get_post_status();
if ( ! $is_news && ! $closed && $application ) : $apply_url = 'email' === $application->type ? 'mailto:' . $application->email : $application->url; ?>
<a class="jsc-button outline" href="<?php echo esc_url( $apply_url ); ?>">Apply Job</a>
<?php elseif ( ! $is_news && $closed ) : ?><p class="jsc-closed">Applications closed</p><?php endif; ?>
</div>
</header>
