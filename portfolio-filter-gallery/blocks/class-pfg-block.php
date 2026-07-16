<?php
/**
 * Gutenberg Block for Portfolio Filter Gallery.
 *
 * @package    Portfolio_Filter_Gallery
 * @subpackage Portfolio_Filter_Gallery/blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class for registering and rendering the Gutenberg block.
 */
class PFG_Block {

    /**
     * Block namespace.
     *
     * @var string
     */
    private $namespace = 'portfolio-filter-gallery';

    /**
     * Block name.
     *
     * @var string
     */
    private $block_name = 'gallery';

    /**
     * Initialize the block.
     */
    public function init() {
        add_action( 'init', array( $this, 'register_block' ) );
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
    }

    /**
     * Register the block and its editor assets on init.
     */
    public function register_block() {
        // Register the block editor script first so it's registered on init
        wp_register_script(
            'pfg-block-editor',
            PFG_PLUGIN_URL . 'blocks/js/block.js',
            array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-block-editor', 'wp-server-side-render' ),
            PFG_VERSION,
            true
        );

        // Register editor styles on init
        wp_register_style(
            'pfg-block-editor-style',
            PFG_PLUGIN_URL . 'blocks/css/editor.css',
            array( 'wp-edit-blocks' ),
            PFG_VERSION
        );

        // Register block type pointing to editor script and style
        register_block_type( $this->namespace . '/' . $this->block_name, array(
            'editor_script'   => 'pfg-block-editor',
            'editor_style'    => 'pfg-block-editor-style',
            'render_callback' => array( $this, 'render_block' ),
            'attributes'      => array(
                'galleryId' => array(
                    'type'    => 'number',
                    'default' => 0,
                ),
                'showTitle' => array(
                    'type'    => 'boolean',
                    'default' => false,
                ),
                'className' => array(
                    'type'    => 'string',
                    'default' => '',
                ),
                'columnsOverride' => array(
                    'type'    => 'number',
                    'default' => 0,
                ),
                'hoverEffectOverride' => array(
                    'type'    => 'string',
                    'default' => '',
                ),
                'showFiltersOverride' => array(
                    'type'    => 'string',
                    'default' => '',
                ),
            ),
        ) );
    }

    /**
     * Enqueue editor assets.
     */
    public function enqueue_editor_assets() {
        global $post_type;
        
        // Don't load block editor assets when editing gallery posts (they have their own editor)
        if ( $post_type === 'awl_filter_gallery' ) {
            return;
        }
        
        // Only load on post types that support the block editor
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( $screen && ! $screen->is_block_editor ) {
            return;
        }

        // Get all galleries for the dropdown
        $galleries = $this->get_galleries();

        // Enqueue the main public gallery assets so the block preview inside the editor matches frontend
        wp_enqueue_style( 'pfg-gallery-public', PFG_PLUGIN_URL . 'public/css/pfg-gallery.css', array(), PFG_VERSION );
        wp_enqueue_style( 'pfg-hover-public', PFG_PLUGIN_URL . 'public/css/pfg-hover.css', array(), PFG_VERSION );
        wp_enqueue_style( 'pfg-lightbox-public', PFG_PLUGIN_URL . 'public/css/pfg-lightbox.css', array(), PFG_VERSION );
        wp_enqueue_script( 'pfg-gallery-public-js', PFG_PLUGIN_URL . 'public/js/pfg-gallery.js', array( 'jquery' ), PFG_VERSION, true );
        wp_localize_script(
            'pfg-gallery-public-js',
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
                'lightboxLibrary' => 'built-in',
            )
        );

        // Pass galleries to the already registered block script
        wp_localize_script( 'pfg-block-editor', 'pfgBlockData', array(
            'galleries'    => $galleries,
            'pluginUrl'    => PFG_PLUGIN_URL,
            'isPremium'    => PFG_Features::is_premium(),
            'upgradeUrl'   => PFG_Features::get_upgrade_url( 'gutenberg-block' ),
            'strings'      => array(
                'title'          => __( 'Portfolio Filter Gallery', 'portfolio-filter-gallery' ),
                'description'    => __( 'Display a filterable portfolio gallery.', 'portfolio-filter-gallery' ),
                'selectGallery'  => __( 'Select a Gallery', 'portfolio-filter-gallery' ),
                'noGalleries'    => __( 'No galleries found. Create one first.', 'portfolio-filter-gallery' ),
                'createGallery'  => __( 'Create Gallery', 'portfolio-filter-gallery' ),
                'showTitle'      => __( 'Show gallery title', 'portfolio-filter-gallery' ),
                'editGallery'    => __( 'Edit Gallery Settings', 'portfolio-filter-gallery' ),
                'previewNote'    => __( 'Gallery will be displayed on the frontend.', 'portfolio-filter-gallery' ),
            ),
        ) );
    }

    /**
     * Render the block on the frontend.
     *
     * @param array $attributes Block attributes.
     * @return string Block HTML.
     */
    public function render_block( $attributes ) {
        $gallery_id           = isset( $attributes['galleryId'] ) ? absint( $attributes['galleryId'] ) : 0;
        $show_title           = isset( $attributes['showTitle'] ) ? $attributes['showTitle'] : false;
        $class_name           = isset( $attributes['className'] ) ? $attributes['className'] : '';
        $columns_override     = isset( $attributes['columnsOverride'] ) ? absint( $attributes['columnsOverride'] ) : 0;
        $hover_override       = isset( $attributes['hoverEffectOverride'] ) ? sanitize_text_field( $attributes['hoverEffectOverride'] ) : '';
        $show_filters_override = isset( $attributes['showFiltersOverride'] ) ? sanitize_text_field( $attributes['showFiltersOverride'] ) : '';

        if ( ! $gallery_id ) {
            return '<p class="pfg-block-placeholder">' . esc_html__( 'Please select a gallery.', 'portfolio-filter-gallery' ) . '</p>';
        }

        // Check if gallery exists
        $gallery = get_post( $gallery_id );
        if ( ! $gallery || $gallery->post_type !== 'awl_filter_gallery' ) {
            return '<p class="pfg-block-error">' . esc_html__( 'Gallery not found.', 'portfolio-filter-gallery' ) . '</p>';
        }

        // Build shortcode with optional overrides
        $shortcode_atts = array( 'id' => $gallery_id );
        
        if ( $columns_override > 0 ) {
            $shortcode_atts['columns'] = $columns_override;
        }
        if ( ! empty( $hover_override ) ) {
            $shortcode_atts['hover_effect'] = $hover_override;
        }
        if ( $show_filters_override !== '' ) {
            $shortcode_atts['show_filters'] = ( $show_filters_override === 'true' ) ? '1' : '0';
        }
        
        // Build shortcode string
        $shortcode_parts = array();
        foreach ( $shortcode_atts as $key => $value ) {
            $shortcode_parts[] = sprintf( '%s="%s"', $key, esc_attr( $value ) );
        }
        $shortcode = '[portfolio_gallery ' . implode( ' ', $shortcode_parts ) . ']';
        
        // Output
        $output = '';
        
        if ( $class_name ) {
            $output .= '<div class="' . esc_attr( $class_name ) . '">';
        }
        
        if ( $show_title ) {
            $output .= '<h2 class="pfg-block-title">' . esc_html( $gallery->post_title ) . '</h2>';
        }
        
        $output .= do_shortcode( $shortcode );
        
        if ( $class_name ) {
            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Get all galleries for block dropdown.
     *
     * @return array
     */
    private function get_galleries() {
        $galleries = array();
        
        $posts = get_posts( array(
            'post_type'      => 'awl_filter_gallery',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        if ( ! empty( $posts ) && is_array( $posts ) ) {
            foreach ( $posts as $p ) {
                $galleries[] = array(
                    'id'    => $p->ID,
                    'title' => $p->post_title,
                );
            }
        }

        return $galleries;
    }
}
