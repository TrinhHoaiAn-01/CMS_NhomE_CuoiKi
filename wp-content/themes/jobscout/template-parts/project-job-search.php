<?php /** GET-based search works without JavaScript and retains filter values. */
$keywords = isset( $_GET['search_keywords'] ) ? sanitize_text_field( wp_unslash( $_GET['search_keywords'] ) ) : '';
$location = isset( $_GET['search_location'] ) && is_scalar( $_GET['search_location'] ) ? sanitize_text_field( wp_unslash( $_GET['search_location'] ) ) : ( $args['default_location'] ?? ( is_front_page() ? 'Tokyo' : '' ) );
$cities = ! empty( $args['locations'] ) ? array_filter( array_map( 'trim', explode( "\n", $args['locations'] ) ) ) : array( 'Tokyo', 'Ho Chi Minh City', 'Ha Noi', 'Kyoto', 'Bali' ); ?>
<form class="jsc-job-search" action="<?php echo esc_url( jsc_url( 'jobs' ) ); ?>" method="get" role="search" aria-label="Search jobs">
    <label class="jsc-search-field"><?php echo jsc_icon( 'search' ); ?><span class="jsc-sr-only">Job title, company or skills</span><input name="search_keywords" type="search" value="<?php echo esc_attr( $keywords ); ?>" placeholder="Search for jobs, companies, skills"></label>
    <label class="jsc-location-field"><?php echo jsc_icon( 'pin' ); ?><span class="jsc-sr-only">Job location</span><select name="search_location">
        <option value="">All locations</option>
        <?php foreach ( $cities as $city ) : ?><option value="<?php echo esc_attr( $city ); ?>" <?php selected( $location, $city ); ?>><?php echo esc_html( $city ); ?></option><?php endforeach; ?>
    </select></label>
    <button type="submit"><?php echo esc_html( $args['search_label'] ?? 'Search Job' ); ?></button>
</form>
