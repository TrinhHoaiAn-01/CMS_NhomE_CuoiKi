<?php /** Shared detail summary; native listing data. */
$id = get_the_ID(); $logo = get_post_meta( $id, '_company_logo', true );
$types = get_the_terms( $id, 'job_listing_type' ); $categories = get_the_terms( $id, 'job_listing_category' ); ?>
<header class="jsc-detail-summary">
<?php if ( $logo ) : ?><img class="jsc-detail-image" src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( get_post_meta( $id, '_company_name', true ) ); ?>" width="140" height="140"><?php endif; ?>
<div class="jsc-detail-heading"><h1><?php the_title(); ?></h1><p class="jsc-created">Created: <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'M d, Y' ) ); ?></time></p>
<ul class="jsc-meta" aria-label="Job information"><?php if ( $types && ! is_wp_error( $types ) ) : ?><li><?php echo esc_html( $types[0]->name ); ?></li><?php endif; ?><?php if ( $categories && ! is_wp_error( $categories ) ) : ?><li><?php echo esc_html( $categories[0]->name ); ?></li><?php endif; ?><li><?php echo esc_html( get_post_meta( $id, '_job_location', true ) ); ?></li></ul></div>
<div class="jsc-detail-buttons"></div>
</header>
