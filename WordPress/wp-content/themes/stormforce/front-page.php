<?php
/**
 * Front page template rendering the bundled static homepage.
 *
 * @package stormforce
 */

if ( stormforce_render_static_template( 'index.html' ) ) {
    return;
}

get_header();

if ( have_posts() ) :
    while ( have_posts() ) :
        the_post();
        the_content();
    endwhile;
else :
    echo '<p>' . esc_html__( 'No content found.', 'stormforce' ) . '</p>';
endif;

get_footer();
