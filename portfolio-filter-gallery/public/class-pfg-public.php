<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package    Portfolio_Filter_Gallery
 * @subpackage Portfolio_Filter_Gallery/public
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The public-facing functionality of the plugin.
 */
class PFG_Public {

    /**
     * The plugin name.
     *
     * @var string
     */
    private $plugin_name;

    /**
     * The plugin version.
     *
     * @var string
     */
    private $version;

    /**
     * Galleries to render on current page.
     *
     * @var array
     */
    private static $galleries_on_page = array();

    /**
     * Initialize the class.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version     The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    /**
     * Check if current page contains a gallery.
     *
     * @return bool
     */
    private function page_has_gallery() {
        if ( ! empty( self::$galleries_on_page ) ) {
            return true;
        }

        global $post;
        if ( is_a( $post, 'WP_Post' ) && ! empty( $post->post_content ) ) {
            if ( has_shortcode( $post->post_content, 'pfg_gallery' ) ||
                 has_shortcode( $post->post_content, 'PFG_Gallery' ) ||
                 has_shortcode( $post->post_content, 'pfg-gallery' ) ||
                 ( function_exists( 'has_block' ) && ( has_block( 'pfg/gallery', $post ) || has_block( 'portfolio-filter-gallery/gallery', $post ) ) ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Register the stylesheets for the public-facing side.
     */
    public function enqueue_styles() {
        // Register core styles so they are always available
        wp_register_style(
            'pfg-gallery',
            PFG_PLUGIN_URL . 'public/css/pfg-gallery.css',
            array(),
            $this->version
        );
        wp_register_style(
            'pfg-hover',
            PFG_PLUGIN_URL . 'public/css/pfg-hover.css',
            array(),
            $this->version
        );
        wp_register_style(
            'pfg-lightbox',
            PFG_PLUGIN_URL . 'public/css/pfg-lightbox.css',
            array(),
            $this->version
        );
        wp_register_style(
            'ld-lightbox',
            PFG_PLUGIN_URL . 'public/lightbox/ld-lightbox/css/lightbox.css',
            array(),
            $this->version
        );

        // Only enqueue in header if a gallery is detected on the page
        if ( ! $this->page_has_gallery() ) {
            return;
        }

        // Enqueue core styles
        wp_enqueue_style( 'pfg-gallery' );
        wp_enqueue_style( 'pfg-hover' );

        $global_settings = get_option( 'pfg_global_settings', array() );
        $active_lightbox = isset( $global_settings['lightbox'] ) ? $global_settings['lightbox'] : 'built-in';

        if ( $active_lightbox === 'built-in' ) {
            wp_enqueue_style( 'pfg-lightbox' );
        } elseif ( $active_lightbox === 'ld-lightbox' ) {
            wp_enqueue_style( 'ld-lightbox' );
        }
    }

    /**
     * Register the JavaScript for the public-facing side.
     */
    public function enqueue_scripts() {
        // Always REGISTER scripts to prevent theme compatibility issues
        wp_register_script(
            'pfg-gallery',
            PFG_PLUGIN_URL . 'public/js/pfg-gallery.js',
            array(),
            $this->version,
            true
        );

        $global_settings = get_option( 'pfg_global_settings', array() );
        $active_lightbox = isset( $global_settings['lightbox'] ) ? $global_settings['lightbox'] : 'built-in';

        wp_register_script(
            'pfg-lightbox',
            PFG_PLUGIN_URL . 'public/js/pfg-lightbox.js',
            array(),
            $this->version,
            true
        );

        wp_register_script(
            'ld-lightbox',
            PFG_PLUGIN_URL . 'public/lightbox/ld-lightbox/js/lightbox.js',
            array( 'jquery' ),
            $this->version,
            true
        );

        // Localize script data
        wp_localize_script(
            'pfg-gallery',
            'pfgData',
            array(
                'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
                'nonce'          => wp_create_nonce( 'pfg_public_nonce' ),
                'analyticsNonce' => wp_create_nonce( 'pfg_analytics_nonce' ),
                'i18n'           => array(
                    'all'       => __( 'All', 'portfolio-filter-gallery' ),
                    'loading'   => __( 'Loading...', 'portfolio-filter-gallery' ),
                    'noResults' => __( 'No items found.', 'portfolio-filter-gallery' ),
                    'prev'      => __( 'Previous', 'portfolio-filter-gallery' ),
                    'next'      => __( 'Next', 'portfolio-filter-gallery' ),
                    'close'     => __( 'Close', 'portfolio-filter-gallery' ),
                ),
                'lightboxLibrary' => $active_lightbox,
            )
        );

        // Only ENQUEUE if a gallery is on the page
        if ( ! $this->page_has_gallery() ) {
            return;
        }

        // Enqueue the already-registered script
        wp_enqueue_script( 'pfg-gallery' );

        // Load appropriate lightbox script
        if ( $active_lightbox === 'built-in' ) {
            wp_enqueue_script( 'pfg-lightbox' );
        } elseif ( $active_lightbox === 'ld-lightbox' ) {
            wp_enqueue_script( 'ld-lightbox' );
        }
    }

    /**
     * Register a gallery to be rendered on current page.
     * This allows conditional asset loading.
     *
     * @param int $gallery_id Gallery ID.
     */
    public static function register_gallery_on_page( $gallery_id ) {
        if ( ! in_array( $gallery_id, self::$galleries_on_page, true ) ) {
            self::$galleries_on_page[] = $gallery_id;
        }
    }

    /**
     * Get registered galleries.
     *
     * @return array
     */
    public static function get_registered_galleries() {
        return self::$galleries_on_page;
    }

    /**
     * Add async/defer to scripts.
     *
     * @param string $tag    Script HTML tag.
     * @param string $handle Script handle.
     * @param string $src    Script source.
     * @return string Modified script tag.
     */
    public function add_async_defer( $tag, $handle, $src ) {
        $async_scripts = array( 'pfg-gallery' );

        if ( in_array( $handle, $async_scripts, true ) ) {
            return str_replace( ' src', ' defer src', $tag );
        }

        return $tag;
    }

    /**
     * Add preload hints for critical assets.
     */
    public function add_preload_hints() {
        if ( ! $this->page_has_gallery() ) {
            return;
        }

        // Preload core CSS
        echo '<link rel="preload" href="' . esc_url( PFG_PLUGIN_URL . 'public/css/pfg-gallery.css?ver=' . $this->version ) . '" as="style">' . "\n";
    }
}
