<?php
/**
 * Elementor Widget for Portfolio Filter Gallery.
 *
 * @package    Portfolio_Filter_Gallery
 * @subpackage Portfolio_Filter_Gallery/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Elementor Portfolio Filter Gallery Widget.
 */
class PFG_Elementor_Widget extends \Elementor\Widget_Base {

    /**
     * Constructor.
     */
    public function __construct( $data = array(), $args = null ) {
        parent::__construct( $data, $args );

        $version = defined( 'PFG_VERSION' ) ? PFG_VERSION : '2.0.0';

        // Register script
        wp_register_script(
            'pfg-gallery',
            PFG_PLUGIN_URL . 'public/js/pfg-gallery.js',
            array(),
            $version,
            true
        );

        // Localize script
        $global_settings = get_option( 'pfg_global_settings', array() );
        $active_lightbox = isset( $global_settings['lightbox'] ) ? $global_settings['lightbox'] : 'built-in';
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

        // Register styles
        wp_register_style(
            'pfg-gallery',
            PFG_PLUGIN_URL . 'public/css/pfg-gallery.css',
            array(),
            $version
        );

        wp_register_style(
            'pfg-hover',
            PFG_PLUGIN_URL . 'public/css/pfg-hover.css',
            array(),
            $version
        );

        wp_register_style(
            'pfg-lightbox',
            PFG_PLUGIN_URL . 'public/css/pfg-lightbox.css',
            array(),
            $version
        );
    }

    /**
     * Get script dependencies.
     *
     * @return array
     */
    public function get_script_depends() {
        return array( 'pfg-gallery' );
    }

    /**
     * Get style dependencies.
     *
     * @return array
     */
    public function get_style_depends() {
        return array( 'pfg-gallery', 'pfg-hover', 'pfg-lightbox' );
    }

    /**
     * Get widget name.
     *
     * @return string
     */
    public function get_name() {
        return 'portfolio-filter-gallery';
    }

    /**
     * Get widget title.
     *
     * @return string
     */
    public function get_title() {
        return esc_html__( 'Portfolio Filter Gallery', 'portfolio-filter-gallery' );
    }

    /**
     * Get widget icon.
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    /**
     * Get widget categories.
     *
     * @return array
     */
    public function get_categories() {
        return array( 'general' );
    }

    /**
     * Register widget controls.
     */
    protected function register_controls() {
        // Content Tab
        $this->start_controls_section(
            'section_content',
            array(
                'label' => esc_html__( 'Gallery Settings', 'portfolio-filter-gallery' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        // Get all galleries (using safe get_posts)
        $galleries = array( 0 => esc_html__( 'Select a Gallery', 'portfolio-filter-gallery' ) );
        $posts = get_posts( array(
            'post_type'      => 'awl_filter_gallery',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        if ( ! empty( $posts ) && is_array( $posts ) ) {
            foreach ( $posts as $p ) {
                $galleries[ $p->ID ] = $p->post_title;
            }
        }

        $this->add_control(
            'gallery_id',
            array(
                'label'   => esc_html__( 'Select Gallery', 'portfolio-filter-gallery' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 0,
                'options' => $galleries,
            )
        );

        $this->add_control(
            'show_title',
            array(
                'label'        => esc_html__( 'Show Gallery Title', 'portfolio-filter-gallery' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Show', 'portfolio-filter-gallery' ),
                'label_off'    => esc_html__( 'Hide', 'portfolio-filter-gallery' ),
                'return_value' => 'yes',
                'default'      => '',
            )
        );

        $this->end_controls_section();

        // Overrides Section
        $this->start_controls_section(
            'section_overrides',
            array(
                'label' => esc_html__( 'Overrides (Optional)', 'portfolio-filter-gallery' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'columns_override',
            array(
                'label'   => esc_html__( 'Override Columns', 'portfolio-filter-gallery' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => '0',
                'options' => array(
                    '0' => esc_html__( 'Use Gallery Default', 'portfolio-filter-gallery' ),
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ),
            )
        );

        $this->add_control(
            'hover_override',
            array(
                'label'   => esc_html__( 'Override Hover Effect', 'portfolio-filter-gallery' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => array(
                    ''         => esc_html__( 'Use Gallery Default', 'portfolio-filter-gallery' ),
                    'none'     => esc_html__( 'None', 'portfolio-filter-gallery' ),
                    'zoom'     => esc_html__( 'Zoom', 'portfolio-filter-gallery' ),
                    'fade'     => esc_html__( 'Fade', 'portfolio-filter-gallery' ),
                    'slide-up' => esc_html__( 'Slide Up', 'portfolio-filter-gallery' ),
                    'blur'     => esc_html__( 'Blur', 'portfolio-filter-gallery' ),
                ),
            )
        );

        $this->add_control(
            'show_filters_override',
            array(
                'label'   => esc_html__( 'Override Show Filters', 'portfolio-filter-gallery' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => array(
                    ''      => esc_html__( 'Use Gallery Default', 'portfolio-filter-gallery' ),
                    'true'  => esc_html__( 'Show Filters', 'portfolio-filter-gallery' ),
                    'false' => esc_html__( 'Hide Filters', 'portfolio-filter-gallery' ),
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend.
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $gallery_id = ! empty( $settings['gallery_id'] ) ? absint( $settings['gallery_id'] ) : 0;
        if ( ! $gallery_id ) {
            echo '<p class="pfg-block-placeholder">' . esc_html__( 'Please select a gallery.', 'portfolio-filter-gallery' ) . '</p>';
            return;
        }

        $gallery = get_post( $gallery_id );
        if ( ! $gallery || $gallery->post_type !== 'awl_filter_gallery' ) {
            echo '<p class="pfg-block-error">' . esc_html__( 'Gallery not found.', 'portfolio-filter-gallery' ) . '</p>';
            return;
        }

        // Build shortcode with optional overrides
        $shortcode_atts = array( 'id' => $gallery_id );

        if ( ! empty( $settings['columns_override'] ) && $settings['columns_override'] !== '0' ) {
            $shortcode_atts['columns'] = $settings['columns_override'];
        }
        if ( ! empty( $settings['hover_override'] ) ) {
            $shortcode_atts['hover_effect'] = $settings['hover_override'];
        }
        if ( ! empty( $settings['show_filters_override'] ) ) {
            $shortcode_atts['show_filters'] = ( $settings['show_filters_override'] === 'true' ) ? '1' : '0';
        }

        // Build shortcode string
        $shortcode_parts = array();
        foreach ( $shortcode_atts as $key => $value ) {
            $shortcode_parts[] = sprintf( '%s="%s"', $key, esc_attr( $value ) );
        }
        $shortcode = '[portfolio_gallery ' . implode( ' ', $shortcode_parts ) . ']';

        if ( ! empty( $settings['show_title'] ) && $settings['show_title'] === 'yes' ) {
            echo '<h2 class="pfg-block-title">' . esc_html( $gallery->post_title ) . '</h2>';
        }

        echo do_shortcode( $shortcode );

        // Live preview re-initialization script for Elementor Editor
        if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            ?>
            <script type="text/javascript">
                if (typeof window.pfgInitGalleries === 'function') {
                    window.pfgInitGalleries();
                }
            </script>
            <?php
        }
    }
}
