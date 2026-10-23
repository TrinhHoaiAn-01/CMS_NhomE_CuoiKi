<?php /** Native WordPress editorial card. */ ?>
<article class="jsc-news-card">
    <a class="jsc-news-image" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
        <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium', array( 'loading' => 'lazy' ) ); } ?>
    </a>
    <div class="jsc-news-copy"><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
    <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
    <a class="jsc-read-more" href="<?php the_permalink(); ?>">Read More<span class="jsc-sr-only">: <?php the_title(); ?></span></a></div>
</article>
