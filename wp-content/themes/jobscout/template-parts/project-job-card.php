<?php
/** WP Job Manager listing card; used by Home, All Jobs, and related jobs. */
$job_id = get_the_ID();
$logo = get_post_meta( $job_id, '_company_logo', true );
$company = get_post_meta( $job_id, '_company_name', true );
$type = get_the_terms( $job_id, 'job_listing_type' );
$category = get_the_terms( $job_id, 'job_listing_category' );
$highlights = get_post_meta( $job_id, 'jsc_highlights', true );
?>
<article class="jsc-job-card">
    <div class="jsc-job-top">
        <a class="jsc-company-logo" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
            <?php if ( $logo ) : ?><img src="<?php echo esc_url( $logo ); ?>" alt="" width="120" height="120" loading="lazy">
            <?php else : ?><span><?php echo esc_html( $company ); ?></span><?php endif; ?>
        </a>
        <div class="jsc-job-summary">
            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            <p class="jsc-created">Created: <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'M d, Y' ) ); ?></time></p>
            <ul class="jsc-meta" aria-label="Job information">
                <?php if ( $type && ! is_wp_error( $type ) ) : ?><li><?php echo esc_html( $type[0]->name ); ?></li><?php endif; ?>
                <?php if ( $category && ! is_wp_error( $category ) ) : ?><li><?php echo esc_html( $category[0]->name ); ?></li><?php endif; ?>
                <?php if ( get_post_meta( $job_id, '_job_location', true ) ) : ?><li><?php echo esc_html( get_post_meta( $job_id, '_job_location', true ) ); ?></li><?php endif; ?>
            </ul>
        </div>
    </div>
    <?php if ( $highlights ) : ?><ul class="jsc-highlights"><?php foreach ( explode( "\n", $highlights ) as $highlight ) : if ( ! trim( $highlight ) ) { continue; } ?><li><?php echo esc_html( trim( $highlight ) ); ?></li><?php endforeach; ?></ul>
    <?php else : ?><p class="jsc-highlights"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 25 ) ); ?></p><?php endif; ?>
</article>
