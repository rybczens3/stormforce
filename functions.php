<?php
/**
 * Stormforce WordPress theme bootstrap.
 */

define( 'STORMFORCE_TEMPLATE_PATH', get_template_directory() );
define( 'STORMFORCE_TEMPLATE_URI', get_template_directory_uri() );

add_action( 'after_setup_theme', 'stormforce_setup' );

function stormforce_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption' ) );
    register_nav_menus( array( 'primary' => __( 'Primary Menu', 'stormforce' ) ) );
}

add_filter( 'body_class', 'stormforce_body_class' );

function stormforce_body_class( $classes ) {
    $classes[] = 'stormforce-theme';
    return $classes;
}

add_action( 'admin_notices', 'stormforce_recommended_plugins_notice' );

function stormforce_recommended_plugins_notice() {
    if ( ! current_user_can( 'install_plugins' ) ) {
        return;
    }

    $plugins = array(
        array(
            'slug' => 'contact-form-7',
            'name' => 'Contact Form 7',
        ),
        array(
            'slug' => 'woocommerce',
            'name' => 'WooCommerce',
        ),
    );

    echo '<div class="notice notice-info">';
    echo '<p><strong>' . esc_html__( 'Recommended plugins for the Stormforce theme:', 'stormforce' ) . '</strong></p>';
    echo '<ul>';

    foreach ( $plugins as $plugin ) {
        $install_url = wp_nonce_url(
            add_query_arg(
                array(
                    'action' => 'install-plugin',
                    'plugin' => $plugin['slug'],
                ),
                network_admin_url( 'update.php' )
            ),
            'install-plugin_' . $plugin['slug']
        );

        printf(
            '<li><a href="%1$s">%2$s</a></li>',
            esc_url( $install_url ),
            esc_html( $plugin['name'] )
        );
    }

    echo '</ul>';
    echo '</div>';
}

/**
 * Replace relative asset paths with theme-based URLs for HTML files.
 */
function stormforce_replace_assets( $html ) {
    $base_uri = trailingslashit( STORMFORCE_TEMPLATE_URI );

    $pattern = '/(?<attr>(?:href|src))="(?<path>(?!(?:https?:)?\/\/|mailto:|tel:|#)[^"]+)"/i';

    return preg_replace_callback(
        $pattern,
        function( $matches ) use ( $base_uri ) {
            $path = $matches['path'];

            $allowed_prefixes = array( 'css/', 'js/', 'images/', 'img/', 'fonts/', 'mailchimp/', 'twitter/', 'favicon.ico' );

            foreach ( $allowed_prefixes as $prefix ) {
                if ( strpos( $path, $prefix ) === 0 ) {
                    return sprintf( '%1$s="%2$s"', $matches['attr'], esc_url( $base_uri . $path ) );
                }
            }

            return $matches[0];
        },
        $html
    );
}

/**
 * Render a static HTML file while injecting WordPress head and footer hooks.
 */
function stormforce_render_static_template( $file_name ) {
    $file_path = STORMFORCE_TEMPLATE_PATH . '/' . ltrim( $file_name, '/' );

    if ( ! file_exists( $file_path ) ) {
        echo '<p>' . esc_html__( 'Requested template not found.', 'stormforce' ) . '</p>';
        return;
    }

    $html = file_get_contents( $file_path );

    if ( false === $html ) {
        echo '<p>' . esc_html__( 'Unable to load the template.', 'stormforce' ) . '</p>';
        return;
    }

    $html = stormforce_replace_assets( $html );

    $head_pos = stripos( $html, '</head>' );
    $body_pos = strripos( $html, '</body>' );

    if ( false === $head_pos || false === $body_pos ) {
        echo $html;
        return;
    }

    $head_segment       = substr( $html, 0, $head_pos );
    $middle_segment     = substr( $html, $head_pos, $body_pos - $head_pos );
    $closing_body_piece = substr( $html, $body_pos );

    echo $head_segment; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    wp_head();
    echo $middle_segment; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    wp_footer();
    echo $closing_body_piece; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
