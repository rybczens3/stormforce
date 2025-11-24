<?php
/**
 * Page template that tries to mirror the original static HTML hierarchy.
 *
 * If a static HTML file matching the page slug exists, it will be rendered.
 * Otherwise the normal WordPress content loop is used.
 *
 * @package stormforce
 */

$slug       = basename( get_permalink() );
$file_guess = sanitize_title( $slug ) . '.html';
$file_path  = STORMFORCE_TEMPLATE_PATH . '/' . $file_guess;

if ( file_exists( $file_path ) ) {
    stormforce_render_static_template( $file_guess );
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
