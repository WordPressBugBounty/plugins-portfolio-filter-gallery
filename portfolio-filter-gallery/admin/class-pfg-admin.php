<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Portfolio_Filter_Gallery
 * @subpackage Portfolio_Filter_Gallery/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The admin-specific functionality of the plugin.
 */
class PFG_Admin {

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
     * Initialize hooks for admin functionality.
     */
    public function init() {
        // Register meta boxes
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

        // Register save hook
        add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );

        // Enqueue scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // Duplicate gallery feature (Premium)
        add_filter( 'post_row_actions', array( $this, 'add_duplicate_action' ), 10, 2 );
        add_action( 'admin_action_pfg_duplicate_gallery', array( $this, 'duplicate_gallery' ) );

        // Pro badge CSS for menu
        add_action( 'admin_head', array( $this, 'output_pro_badge_css' ) );
    }

    /**
     * Output CSS for Pro badge in admin menu.
     */
    public function output_pro_badge_css() {
        ?>
        <style>
            .pfg-pro-badge {
                display: inline-block;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #fff !important;
                font-size: 9px;
                font-weight: 600;
                padding: 2px 6px;
                border-radius: 3px;
                text-transform: uppercase;
                margin-left: 6px;
                vertical-align: middle;
                line-height: 1.4;
            }
            /* Color menu icon teal */
            #adminmenu .menu-icon-awl_filter_gallery .wp-menu-image:before {
                color: #4fd1c5 !important;
            }
        </style>
        <?php
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_styles( $hook ) {
        global $post_type;

        // Only load on our plugin pages
        if ( $post_type !== 'awl_filter_gallery' && strpos( $hook, 'pfg' ) === false ) {
            return;
        }

        // WordPress color picker
        wp_enqueue_style( 'wp-color-picker' );

        // Main admin styles
        wp_enqueue_style(
            'pfg-admin',
            PFG_PLUGIN_URL . 'admin/css/pfg-admin.css',
            array(),
            $this->version
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_scripts( $hook ) {
        global $post_type, $post;

        // Only load on our plugin pages
        if ( $post_type !== 'awl_filter_gallery' && strpos( $hook, 'pfg' ) === false ) {
            return;
        }

        // WordPress dependencies
        wp_enqueue_media();
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'wp-color-picker' );

        // Main admin script
        wp_enqueue_script(
            'pfg-admin',
            PFG_PLUGIN_URL . 'admin/js/pfg-admin.js',
            array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ),
            $this->version,
            true
        );

        // Localize script
        wp_localize_script(
            'pfg-admin',
            'pfgAdmin',
            array(
                'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
                'nonce'       => PFG_Security::create_nonce( 'admin_action' ),
                'galleryId'   => $post ? $post->ID : 0,
                'i18n'        => array(
                    'confirmDelete'     => __( 'Are you sure you want to delete this item?', 'portfolio-filter-gallery' ),
                    'confirmDeleteAll'  => __( 'Are you sure you want to delete all selected items?', 'portfolio-filter-gallery' ),
                    'selectImages'      => __( 'Select Images', 'portfolio-filter-gallery' ),
                    'useSelected'       => __( 'Use Selected', 'portfolio-filter-gallery' ),
                    'saving'            => __( 'Saving...', 'portfolio-filter-gallery' ),
                    'saved'             => __( 'Saved!', 'portfolio-filter-gallery' ),
                    'error'             => __( 'An error occurred. Please try again.', 'portfolio-filter-gallery' ),
                ),
            )
        );
    }

    /**
     * Register the custom post type for galleries.
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x( 'Portfolio Galleries', 'Post Type General Name', 'portfolio-filter-gallery' ),
            'singular_name'         => _x( 'Portfolio Gallery', 'Post Type Singular Name', 'portfolio-filter-gallery' ),
            'menu_name'             => __( 'Portfolio Gallery', 'portfolio-filter-gallery' ),
            'name_admin_bar'        => __( 'Portfolio Gallery', 'portfolio-filter-gallery' ),
            'archives'              => __( 'Gallery Archives', 'portfolio-filter-gallery' ),
            'attributes'            => __( 'Gallery Attributes', 'portfolio-filter-gallery' ),
            'all_items'             => __( 'All Galleries', 'portfolio-filter-gallery' ),
            'add_new_item'          => __( 'Add New Gallery', 'portfolio-filter-gallery' ),
            'add_new'               => __( 'Add New', 'portfolio-filter-gallery' ),
            'new_item'              => __( 'New Gallery', 'portfolio-filter-gallery' ),
            'edit_item'             => __( 'Edit Gallery', 'portfolio-filter-gallery' ),
            'update_item'           => __( 'Update Gallery', 'portfolio-filter-gallery' ),
            'view_item'             => __( 'View Gallery', 'portfolio-filter-gallery' ),
            'view_items'            => __( 'View Galleries', 'portfolio-filter-gallery' ),
            'search_items'          => __( 'Search Gallery', 'portfolio-filter-gallery' ),
            'not_found'             => __( 'Not found', 'portfolio-filter-gallery' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'portfolio-filter-gallery' ),
        );

        $args = array(
            'label'               => __( 'Portfolio Gallery', 'portfolio-filter-gallery' ),
            'description'         => __( 'Create filterable portfolio galleries', 'portfolio-filter-gallery' ),
            'labels'              => $labels,
            'supports'            => array( 'title' ),
            'hierarchical'        => false,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 25,
            'menu_icon'           => 'dashicons-grid-view',
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => false,
            'can_export'          => true,
            'has_archive'         => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'capability_type'     => 'post',
            'show_in_rest'        => true, // Enable Gutenberg support
        );

        register_post_type( 'awl_filter_gallery', $args );
    }

    /**
     * Add meta boxes.
     */
    public function add_meta_boxes() {
        add_meta_box(
            'pfg-gallery-images',
            __( 'Gallery Images', 'portfolio-filter-gallery' ),
            array( $this, 'render_images_meta_box' ),
            'awl_filter_gallery',
            'normal',
            'high'
        );

        add_meta_box(
            'pfg-gallery-settings',
            __( 'Gallery Settings', 'portfolio-filter-gallery' ),
            array( $this, 'render_settings_meta_box' ),
            'awl_filter_gallery',
            'normal',
            'high'
        );

        add_meta_box(
            'pfg-shortcode',
            __( 'Shortcode', 'portfolio-filter-gallery' ),
            array( $this, 'render_shortcode_meta_box' ),
            'awl_filter_gallery',
            'side',
            'high'
        );
    }

    /**
     * Render images meta box.
     *
     * @param WP_Post $post Current post object.
     */
    public function render_images_meta_box( $post ) {
        $gallery = new PFG_Gallery( $post->ID );
        $images  = $gallery->get_images();
        $filters = $this->get_filters();

        // Output nonce field for save verification
        wp_nonce_field( 'pfg_save_gallery', '_pfg_nonce' );

        include PFG_PLUGIN_PATH . 'admin/views/meta-box-images.php';
    }

    /**
     * Render settings meta box.
     *
     * @param WP_Post $post Current post object.
     */
    public function render_settings_meta_box( $post ) {
        $gallery  = new PFG_Gallery( $post->ID );
        $settings = $gallery->get_settings();
        $defaults = $gallery->get_defaults();

        include PFG_PLUGIN_PATH . 'admin/views/meta-box-settings.php';
    }

    /**
     * Render shortcode meta box.
     *
     * @param WP_Post $post Current post object.
     */
    public function render_shortcode_meta_box( $post ) {
        ?>
        <div class="pfg-shortcode-box">
            <p><?php esc_html_e( 'Copy this shortcode and paste it into your post, page, or text widget:', 'portfolio-filter-gallery' ); ?></p>
        <div class="pfg-shortcode-wrapper">
                <code id="pfg-shortcode-code">[PFG id="<?php echo esc_attr( $post->ID ); ?>"]</code>
                <button type="button" class="button pfg-copy-shortcode" data-clipboard-target="#pfg-shortcode-code">
                    <span class="dashicons dashicons-clipboard"></span>
                    <?php esc_html_e( 'Copy', 'portfolio-filter-gallery' ); ?>
                </button>
            </div>
            <p class="pfg-shortcode-note"><?php esc_html_e( 'Or use the new format:', 'portfolio-filter-gallery' ); ?></p>
            <div class="pfg-shortcode-wrapper">
                <code id="pfg-shortcode-new">[portfolio_gallery id="<?php echo esc_attr( $post->ID ); ?>"]</code>
                <button type="button" class="button pfg-copy-shortcode" data-clipboard-target="#pfg-shortcode-new">
                    <span class="dashicons dashicons-clipboard"></span>
                    <?php esc_html_e( 'Copy', 'portfolio-filter-gallery' ); ?>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Save post meta.
     *
     * @param int $post_id Post ID.
     */
    public function save_post( $post_id ) {
        // Check autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check post type
        if ( get_post_type( $post_id ) !== 'awl_filter_gallery' ) {
            return;
        }

        // Check nonce
        if ( ! PFG_Security::verify_nonce( '_pfg_nonce', 'save_gallery' ) ) {
            return;
        }

        // Check capability
        if ( ! PFG_Security::can_edit_gallery( $post_id ) ) {
            return;
        }

        // Save gallery settings from pfg_settings array
        $gallery  = new PFG_Gallery( $post_id );
        $settings = isset( $_POST['pfg_settings'] ) ? wp_unslash( $_POST['pfg_settings'] ) : array();

        // Map form field names to schema keys
        $field_map = array(
            'layout'            => 'layout_type',
            'columns'           => 'columns_lg',
            'gap'               => 'gap',
            'show_filters'      => 'filters_enabled',
            'show_all_button'   => 'show_all_button',
            'all_button_text'   => 'all_button_text',
            'filter_position'   => 'filters_position',
            'filters_style'     => 'filters_style',
            'show_search'       => 'search_enabled',
            'search_placeholder'=> 'search_placeholder',
            'multi_level_filters' => 'multi_level_filters',
            'filter_logic'      => 'filter_logic',
            'show_logic_toggle' => 'show_logic_toggle',
            'show_filter_colors' => 'show_filter_colors',
            'enable_lightbox'   => 'lightbox',
            'hover_effect'      => 'hover_effect',
            'show_title_overlay'=> 'show_title',
            'border_radius'     => 'border_radius',
            'overlay_color'     => 'overlay_color',
            'overlay_opacity'   => 'overlay_opacity',
            'primary_color'     => 'primary_color',
            'filter_active_color' => 'filter_active_color',
            'filter_text_color'   => 'filter_text_color',
            'filter_active_text_color' => 'filter_active_text_color',
            'lazy_loading'      => 'lazy_loading',
            'title_position'    => 'title_position',
            'show_categories'   => 'show_categories',
            'caption_bg_color'  => 'caption_bg_color',
            'caption_text_color'=> 'caption_text_color',
            'template'          => 'template',
            // Pagination settings (Premium)
            'pagination_enabled'=> 'pagination_enabled',
            'pagination_type'   => 'pagination_type',
            'items_per_page'    => 'items_per_page',
            // URL Deep Linking
            'deep_linking'      => 'deep_linking',
            'url_param_name'    => 'url_param_name',
            // Shuffle & Default Filter
            'shuffle_images'    => 'shuffle_images',
            'hide_type_icons'   => 'hide_type_icons',
            'default_filter'    => 'default_filter',
            // Sort, Direction, URL Target
            'sort_by_title'     => 'sort_by_title',
            'direction'         => 'direction',
            'url_target'        => 'url_target',
            // Filter Count
            'show_filter_count' => 'show_filter_count',
            'filter_count_style'=> 'filter_count_style',
            // Justified layout
            'justified_row_height' => 'justified_row_height',
            'justified_last_row'   => 'justified_last_row',
            // Packed layout
            'packed_min_size'      => 'packed_min_size',
            // Lightbox caption settings
            'lightbox_title'       => 'lightbox_title',
            'lightbox_description' => 'lightbox_description',
            // Image size
            'image_size'           => 'image_size',
            // WooCommerce settings
            'source'               => 'source',
            'woo_categories'       => 'woo_categories',
            'woo_orderby'          => 'woo_orderby',
            'woo_order'            => 'woo_order',
            'woo_limit'            => 'woo_limit',
            'woo_show_price'       => 'woo_show_price',
            'woo_show_sale_badge'  => 'woo_show_sale_badge',
            'woo_show_title'       => 'woo_show_title',
            'woo_link_target'      => 'woo_link_target',
            // Watermark settings (Premium)
            'watermark_enabled'    => 'watermark_enabled',
            'watermark_type'       => 'watermark_type',
            'watermark_text'       => 'watermark_text',
            'watermark_image'      => 'watermark_image',
            'watermark_position'   => 'watermark_position',
            'watermark_opacity'    => 'watermark_opacity',
            'watermark_size'       => 'watermark_size',
            'watermark_image_size' => 'watermark_image_size',
            // Gallery Preloader & Analytics
            'show_preloader'       => 'show_preloader',
            'analytics_enabled'    => 'analytics_enabled',
        );

        // Set each setting
        foreach ( $field_map as $form_key => $schema_key ) {
            if ( isset( $settings[ $form_key ] ) ) {
                $value = $settings[ $form_key ];
                // Handle lightbox specially - convert boolean to type
                if ( $form_key === 'enable_lightbox' ) {
                    $value = $value ? 'built-in' : 'none';
                }
                $gallery->set_setting( $schema_key, $value );
            } else {
                // Handle unchecked checkboxes for boolean fields
                $schema = PFG_Gallery::get_schema();
                if ( isset( $schema[ $schema_key ] ) && $schema[ $schema_key ]['type'] === 'bool' ) {
                    $gallery->set_setting( $schema_key, false );
                }
                // Handle lightbox specifically when checkbox is unchecked
                if ( $form_key === 'enable_lightbox' ) {
                    $gallery->set_setting( 'lightbox', 'none' );
                }
            }
        }

        // Set responsive column values (use single column value for all breakpoints for now)
        if ( isset( $settings['columns'] ) ) {
            $cols = absint( $settings['columns'] );
            $gallery->set_setting( 'columns_xl', $cols );
            $gallery->set_setting( 'columns_lg', $cols );
            $gallery->set_setting( 'columns_md', max( 2, floor( $cols / 2 ) ) );
            $gallery->set_setting( 'columns_sm', 1 );
        }

        $gallery->save();

        // Save images
        $this->save_images( $post_id );

        // Also save in legacy format for backward compatibility
        $this->save_legacy_format( $post_id, $gallery );
    }

    /**
     * Save gallery images.
     *
     * @param int $post_id Post ID.
     */
    protected function save_images( $post_id ) {
        // Check if source is WooCommerce - if so, don't modify media library images
        $settings = isset( $_POST['pfg_settings'] ) ? wp_unslash( $_POST['pfg_settings'] ) : array();
        $source = isset( $settings['source'] ) ? sanitize_key( $settings['source'] ) : 'media';
        
        // If source is WooCommerce, preserve existing media library images
        if ( $source === 'woocommerce' ) {
            return; // Don't modify images when using WooCommerce source
        }
        
        $images = array();
        
        // Check for JSON-serialized data first (bypasses max_input_vars limit)
        if ( isset( $_POST['pfg_images_json'] ) && ! empty( $_POST['pfg_images_json'] ) ) {
            $json_data = wp_unslash( $_POST['pfg_images_json'] );
            
            // Check for special flags
            if ( $json_data === '__UNCHANGED__' ) {
                // Images weren't modified - skip saving images entirely
                return;
            }
            
            if ( $json_data === '__CHUNKED_SAVE__' ) {
                // Images were already saved via chunked AJAX - skip
                return;
            }
            
            $raw_images = json_decode( $json_data, true );
            
            if ( is_array( $raw_images ) && ! empty( $raw_images ) ) {
                foreach ( $raw_images as $image ) {
                    if ( empty( $image['id'] ) ) {
                        continue;
                    }
                    
                    $filters = isset( $image['filters'] ) ? $image['filters'] : '';
                    // Use sanitize_text_field instead of sanitize_key to preserve Unicode filter slugs (Japanese, Chinese, etc.)
                    if ( is_string( $filters ) ) {
                        $filters = array_filter( array_map( 'sanitize_text_field', explode( ',', $filters ) ) );
                    } elseif ( is_array( $filters ) ) {
                        $filters = array_filter( array_map( 'sanitize_text_field', $filters ) );
                    } else {
                        $filters = array();
                    }
                    
                    $images[] = array(
                        'id'           => absint( $image['id'] ),
                        'title'        => isset( $image['title'] ) ? sanitize_text_field( $image['title'] ) : '',
                        'alt'          => isset( $image['alt'] ) ? sanitize_text_field( $image['alt'] ) : '',
                        'description'  => isset( $image['description'] ) ? sanitize_textarea_field( $image['description'] ) : '',
                        'link'         => isset( $image['link'] ) ? esc_url_raw( $image['link'] ) : '',
                        'type'         => isset( $image['type'] ) ? sanitize_key( $image['type'] ) : 'image',
                        'filters'      => $filters,
                        'product_id'   => isset( $image['product_id'] ) ? absint( $image['product_id'] ) : 0,
                        'product_name' => isset( $image['product_name'] ) ? sanitize_text_field( $image['product_name'] ) : '',
                        'original_id'  => isset( $image['original_id'] ) ? absint( $image['original_id'] ) : absint( $image['id'] ),
                    );
                }
                
                update_post_meta( $post_id, '_pfg_images', $images );
                return;
            }
        }
        
        // Fallback: If no JSON data, check for individual fields (backwards compatibility)
        // If pfg_images is not set at all, check if we should clear images
        if ( ! isset( $_POST['pfg_images'] ) ) {
            // Only clear if this is a gallery post being saved with our nonce
            if ( isset( $_POST['_pfg_nonce'] ) ) {
                update_post_meta( $post_id, '_pfg_images', array() );
                // Also clear legacy format to prevent fallback
                $legacy_key = 'awl_filter_gallery' . $post_id;
                $legacy = get_post_meta( $post_id, $legacy_key, true );
                if ( is_array( $legacy ) ) {
                    $legacy['image-ids'] = array();
                    $legacy['image_title'] = array();
                    $legacy['image_desc'] = array();
                    $legacy['image-link'] = array();
                    $legacy['slide-type'] = array();
                    $legacy['filters'] = array();
                    $legacy['filter-image'] = array();
                    update_post_meta( $post_id, $legacy_key, $legacy );
                }
            }
            return;
        }

        $raw_images = wp_unslash( $_POST['pfg_images'] );

        if ( is_array( $raw_images ) ) {
            foreach ( $raw_images as $image ) {
                if ( empty( $image['id'] ) ) {
                    continue;
                }

                $images[] = array(
                    'id'           => absint( $image['id'] ),
                    'title'        => isset( $image['title'] ) ? sanitize_text_field( $image['title'] ) : '',
                    'alt'          => isset( $image['alt'] ) ? sanitize_text_field( $image['alt'] ) : '',
                    'description'  => isset( $image['description'] ) ? sanitize_textarea_field( $image['description'] ) : '',
                    'link'         => isset( $image['link'] ) ? esc_url_raw( $image['link'] ) : '',
                    'type'         => isset( $image['type'] ) ? sanitize_key( $image['type'] ) : 'image',
                    'filters'      => isset( $image['filters'] ) ? array_filter( array_map( 'sanitize_text_field', explode( ',', $image['filters'] ) ) ) : array(),
                    'product_id'   => isset( $image['product_id'] ) ? absint( $image['product_id'] ) : 0,
                    'product_name' => isset( $image['product_name'] ) ? sanitize_text_field( $image['product_name'] ) : '',
                    'original_id'  => isset( $image['original_id'] ) ? absint( $image['original_id'] ) : absint( $image['id'] ),
                );
            }
        }

        update_post_meta( $post_id, '_pfg_images', $images );
    }

    /**
     * Save in legacy format for backward compatibility.
     *
     * @param int         $post_id Post ID.
     * @param PFG_Gallery $gallery Gallery object.
     */
    protected function save_legacy_format( $post_id, $gallery ) {
        $settings = $gallery->get_settings();
        $images   = $gallery->get_images();

        // Map column values to legacy Bootstrap classes
        $col_lg_map = array( 1 => 'col-lg-12', 2 => 'col-lg-6', 3 => 'col-lg-4', 4 => 'col-lg-3', 5 => 'col-lg-2', 6 => 'col-lg-2' );
        $col_md_map = array( 1 => 'col-sm-12', 2 => 'col-sm-6', 3 => 'col-sm-4', 4 => 'col-sm-3', 5 => 'col-sm-2', 6 => 'col-sm-2' );
        $col_sm_map = array( 1 => 'col-xs-12', 2 => 'col-xs-6', 3 => 'col-xs-4', 4 => 'col-xs-3', 5 => 'col-xs-2', 6 => 'col-xs-2' );

        // Map lightbox values
        $lightbox_map = array( 'none' => 0, 'built-in' => 4, 'bootstrap' => 5 );
        $lightbox_val = isset( $lightbox_map[ $settings['lightbox'] ] ) ? $lightbox_map[ $settings['lightbox'] ] : 4;

        // Transform to legacy format
        $legacy = array(
            'image-ids'          => array(),
            'image_title'        => array(),
            'image_desc'         => array(),
            'image-link'         => array(),
            'slide-type'         => array(),
            'filters'            => array(),
            'filter-image'       => array(),
            'gal_size'           => 'medium',
            'col_large_desktops' => isset( $col_lg_map[ $settings['columns_xl'] ] ) ? $col_lg_map[ $settings['columns_xl'] ] : 'col-lg-3',
            'col_desktops'       => isset( $col_lg_map[ $settings['columns_lg'] ] ) ? $col_lg_map[ $settings['columns_lg'] ] : 'col-lg-3',
            'col_tablets'        => isset( $col_md_map[ $settings['columns_md'] ] ) ? $col_md_map[ $settings['columns_md'] ] : 'col-sm-4',
            'col_phones'         => isset( $col_sm_map[ $settings['columns_sm'] ] ) ? $col_sm_map[ $settings['columns_sm'] ] : 'col-xs-6',
            'no_spacing'         => $settings['gap'] == 0 ? 1 : 0,
            'gallery_direction'  => $settings['direction'],
            'title_thumb'        => $settings['show_title'] ? 'show' : 'hide',
            'image_numbering'    => $settings['show_numbering'] ? 1 : 0,
            'gray_scale'         => $settings['grayscale'] ? 1 : 0,
            'image_hover_effect_four' => $settings['hover_effect'] !== 'none' ? 'hvr-box-shadow-outset' : 'none',
            'thumb_border'       => $settings['border_width'] > 0 ? 'yes' : 'no',
            'hide_filters'       => $settings['filters_enabled'] ? 0 : 1,
            'filter_position'    => $settings['filters_position'],
            'all_txt'            => $settings['all_button_text'],
            'sort_filter_order'  => $settings['sort_filters'] ? 1 : 0,
            'filter_bg'          => $settings['filter_bg_color'],
            'filter_title_color' => $settings['filter_text_color'],
            'light-box'          => $lightbox_val,
            'url_target'         => $settings['url_target'],
            'search_box'         => $settings['search_enabled'] ? 1 : 0,
            'search_txt'         => $settings['search_placeholder'],
            'sort_by_title'      => $settings['sort_by_title'] ? 'asc' : 'no',
            'custom-css'         => $settings['custom_css'],
            'bootstrap_disable'  => 'no',
            'show_image_count'   => $settings['show_image_count'] ? 1 : 0,
        );

        // Add images to legacy format
        foreach ( $images as $image ) {
            $image_id = $image['id'];
            
            $legacy['image-ids'][]   = $image_id;
            $legacy['image_title'][] = $image['title'];
            $legacy['image_desc'][]  = isset( $image['description'] ) ? $image['description'] : '';
            $legacy['image-link'][]  = isset( $image['link'] ) ? $image['link'] : '';
            $legacy['slide-type'][]  = isset( $image['type'] ) ? $image['type'] : 'image';
            
            // Add filters for this image
            if ( ! empty( $image['filters'] ) ) {
                $legacy['filters'][ $image_id ] = $image['filters'];
                
                // Build the filter-image reverse mapping
                foreach ( $image['filters'] as $filter_id ) {
                    if ( ! isset( $legacy['filter-image'][ $filter_id ] ) ) {
                        $legacy['filter-image'][ $filter_id ] = array();
                    }
                    $legacy['filter-image'][ $filter_id ][] = $image_id;
                }
            }
        }

        $legacy_key = 'awl_filter_gallery' . $post_id;
        update_post_meta( $post_id, $legacy_key, $legacy );
    }

    /**
     * Get all filters.
     *
     * @return array
     */
    public function get_filters() {
        // Try new format first
        $filters = get_option( 'pfg_filters', array() );

        if ( ! empty( $filters ) ) {
            return $filters;
        }

        // Fall back to legacy format
        $legacy = get_option( 'awl_portfolio_filter_gallery_categories', array() );
        $result = array();

        foreach ( $legacy as $id => $name ) {
            // Handle non-Latin characters in slug generation
            $slug = sanitize_title( $name );
            // If empty OR URL-encoded (contains %xx hex), use Unicode-aware slug
            if ( empty( $slug ) || preg_match( '/%[0-9a-f]{2}/i', $slug ) ) {
                // Keep Unicode letters and numbers, use mb_strtolower for proper UTF-8 handling
                $slug = mb_strtolower( preg_replace( '/[^\p{L}\p{N}]+/ui', '-', $name ), 'UTF-8' );
                $slug = trim( $slug, '-' );
                if ( empty( $slug ) ) {
                    $slug = 'filter-' . substr( md5( $name ), 0, 8 );
                }
            }
            
            $result[] = array(
                'id'   => sanitize_key( $id ) ?: 'filter' . substr( md5( $name ), 0, 8 ),
                'name' => sanitize_text_field( $name ),
                'slug' => $slug,
            );
        }

        return $result;
    }

    /**
     * Add admin menu pages.
     */
    public function add_menu_pages() {
        add_submenu_page(
            'edit.php?post_type=awl_filter_gallery',
            __( 'Filters', 'portfolio-filter-gallery' ),
            __( 'Filters', 'portfolio-filter-gallery' ),
            'edit_posts',
            'pfg-filters',
            array( $this, 'render_filters_page' )
        );

        add_submenu_page(
            'edit.php?post_type=awl_filter_gallery',
            __( 'Settings', 'portfolio-filter-gallery' ),
            __( 'Settings', 'portfolio-filter-gallery' ),
            'manage_options',
            'pfg-settings',
            array( $this, 'render_settings_page' )
        );

        // Analytics page - Show Pro teaser in free version
        add_submenu_page(
            'edit.php?post_type=awl_filter_gallery',
            __( 'Analytics', 'portfolio-filter-gallery' ),
            __( 'Analytics', 'portfolio-filter-gallery' ) . ' <span class="pfg-pro-badge">Pro</span>',
            'manage_options',
            'pfg-analytics',
            array( $this, 'render_analytics_pro_teaser' )
        );

        add_submenu_page(
            'edit.php?post_type=awl_filter_gallery',
            __( 'Docs', 'portfolio-filter-gallery' ),
            __( 'Docs', 'portfolio-filter-gallery' ),
            'edit_posts',
            'pfg-docs',
            array( $this, 'render_docs_page' )
        );

        // Upgrade to Pro - only show if not premium
        if ( ! defined( 'PFG_PREMIUM' ) || ! PFG_PREMIUM ) {
            add_submenu_page(
                'edit.php?post_type=awl_filter_gallery',
                __( 'Upgrade to Pro', 'portfolio-filter-gallery' ),
                '<span style="color: #f9d71c;">★ ' . __( 'Upgrade to Pro', 'portfolio-filter-gallery' ) . '</span>',
                'manage_options',
                'https://awplife.com/account/signup/portfolio-filter-gallery'
            );
        }
    }

    /**
     * Render filters management page.
     */
    public function render_filters_page() {
        $filters = $this->get_filters();
        include PFG_PLUGIN_PATH . 'admin/views/page-filters.php';
    }

    /**
     * Render global settings page.
     */
    public function render_settings_page() {
        include PFG_PLUGIN_PATH . 'admin/views/page-settings.php';
    }

    /**
     * Render documentation page.
     */
    public function render_docs_page() {
        include PFG_PLUGIN_DIR . 'admin/views/page-docs.php';
    }

    /**
     * Render Analytics Pro teaser page.
     */
    public function render_analytics_pro_teaser() {
        ?>
        <div class="wrap pfg-analytics-teaser">
            <h1><?php esc_html_e( 'Gallery Analytics', 'portfolio-filter-gallery' ); ?> <span class="pfg-pro-badge-large">Pro</span></h1>
            
            <div class="pfg-teaser-container">
                <!-- Background mockup -->
                <div class="pfg-teaser-background">
                    <div class="pfg-teaser-cards">
                        <div class="pfg-teaser-card">
                            <span class="dashicons dashicons-visibility"></span>
                            <span class="pfg-teaser-value">1,234</span>
                            <span class="pfg-teaser-label">Views</span>
                        </div>
                        <div class="pfg-teaser-card">
                            <span class="dashicons dashicons-groups"></span>
                            <span class="pfg-teaser-value">567</span>
                            <span class="pfg-teaser-label">Visitors</span>
                        </div>
                        <div class="pfg-teaser-card">
                            <span class="dashicons dashicons-format-image"></span>
                            <span class="pfg-teaser-value">892</span>
                            <span class="pfg-teaser-label">Clicks</span>
                        </div>
                        <div class="pfg-teaser-card">
                            <span class="dashicons dashicons-filter"></span>
                            <span class="pfg-teaser-value">2,156</span>
                            <span class="pfg-teaser-label">Filters</span>
                        </div>
                    </div>
                    <div class="pfg-teaser-chart">
                        <svg viewBox="0 0 400 120" preserveAspectRatio="none">
                            <path d="M0,100 L40,80 L80,90 L120,60 L160,70 L200,40 L240,50 L280,30 L320,45 L360,20 L400,35" 
                                  fill="none" stroke="#667eea" stroke-width="3" stroke-linecap="round"/>
                            <path d="M0,100 L40,80 L80,90 L120,60 L160,70 L200,40 L240,50 L280,30 L320,45 L360,20 L400,35 L400,120 L0,120 Z" 
                                  fill="url(#gradient)" opacity="0.3"/>
                            <defs>
                                <linearGradient id="gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" style="stop-color:#667eea"/>
                                    <stop offset="100%" style="stop-color:#667eea;stop-opacity:0"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <div class="pfg-teaser-tables">
                        <div class="pfg-teaser-table">
                            <div class="pfg-table-header">Popular Images</div>
                            <div class="pfg-table-row"><span></span><span>45</span></div>
                            <div class="pfg-table-row"><span></span><span>38</span></div>
                            <div class="pfg-table-row"><span></span><span>32</span></div>
                        </div>
                        <div class="pfg-teaser-table">
                            <div class="pfg-table-header">Popular Filters</div>
                            <div class="pfg-table-row"><span></span><span>128</span></div>
                            <div class="pfg-table-row"><span></span><span>95</span></div>
                            <div class="pfg-table-row"><span></span><span>67</span></div>
                        </div>
                    </div>
                </div>
                
                <!-- CTA Overlay -->
                <div class="pfg-teaser-overlay">
                    <div class="pfg-teaser-cta-box">
                        <div class="pfg-teaser-icon">
                            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="3" width="18" height="18" rx="4" stroke="#4fd1c5" stroke-width="3" fill="none"/>
                                <rect x="27" y="3" width="18" height="18" rx="4" stroke="#4fd1c5" stroke-width="3" fill="none"/>
                                <rect x="3" y="27" width="18" height="18" rx="4" stroke="#4fd1c5" stroke-width="3" fill="none"/>
                                <rect x="27" y="27" width="18" height="18" rx="4" stroke="#4fd1c5" stroke-width="3" fill="none"/>
                            </svg>
                        </div>
                        <h2><?php esc_html_e( 'Unlock Gallery Analytics', 'portfolio-filter-gallery' ); ?></h2>
                        <p><?php esc_html_e( 'Track how visitors interact with your galleries. See which images are most popular and which filters are used most often.', 'portfolio-filter-gallery' ); ?></p>
                        
                        <div class="pfg-teaser-features">
                            <div class="pfg-feature-item">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php esc_html_e( 'Track views & visitors', 'portfolio-filter-gallery' ); ?>
                            </div>
                            <div class="pfg-feature-item">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php esc_html_e( 'Popular images', 'portfolio-filter-gallery' ); ?>
                            </div>
                            <div class="pfg-feature-item">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php esc_html_e( 'Filter analytics', 'portfolio-filter-gallery' ); ?>
                            </div>
                            <div class="pfg-feature-item">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php esc_html_e( 'Time charts', 'portfolio-filter-gallery' ); ?>
                            </div>
                            <div class="pfg-feature-item">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php esc_html_e( 'Per-gallery stats', 'portfolio-filter-gallery' ); ?>
                            </div>
                            <div class="pfg-feature-item">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php esc_html_e( 'Export data', 'portfolio-filter-gallery' ); ?>
                            </div>
                        </div>
                        
                        <a href="https://awplife.com/account/signup/portfolio-filter-gallery" target="_blank" class="pfg-upgrade-btn">
                            <span class="dashicons dashicons-star-filled"></span>
                            <?php esc_html_e( 'Upgrade to Pro', 'portfolio-filter-gallery' ); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
            .pfg-analytics-teaser {
                max-width: 1100px;
            }
            .pfg-analytics-teaser h1 {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 25px;
            }
            .pfg-pro-badge-large {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #fff;
                padding: 5px 14px;
                border-radius: 6px;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .pfg-teaser-container {
                position: relative;
                background: #fff;
                border-radius: 16px;
                overflow: hidden;
                box-shadow: 0 4px 30px rgba(0,0,0,0.08);
                min-height: 500px;
            }
            
            /* Background mockup */
            .pfg-teaser-background {
                padding: 30px;
                filter: blur(3px);
                opacity: 0.5;
            }
            .pfg-teaser-cards {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 20px;
                margin-bottom: 30px;
            }
            .pfg-teaser-card {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #fff;
                padding: 25px 20px;
                border-radius: 12px;
                text-align: center;
            }
            .pfg-teaser-card .dashicons {
                font-size: 28px;
                width: 28px;
                height: 28px;
                opacity: 0.9;
                margin-bottom: 8px;
            }
            .pfg-teaser-value {
                display: block;
                font-size: 32px;
                font-weight: 700;
                line-height: 1.2;
            }
            .pfg-teaser-label {
                display: block;
                font-size: 11px;
                text-transform: uppercase;
                opacity: 0.85;
                margin-top: 4px;
            }
            .pfg-teaser-chart {
                background: #f8fafc;
                border-radius: 12px;
                padding: 30px;
                margin-bottom: 30px;
            }
            .pfg-teaser-chart svg {
                width: 100%;
                height: 100px;
            }
            .pfg-teaser-tables {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }
            .pfg-teaser-table {
                background: #f8fafc;
                border-radius: 12px;
                padding: 20px;
            }
            .pfg-table-header {
                font-weight: 600;
                color: #1e293b;
                padding-bottom: 12px;
                border-bottom: 1px solid #e2e8f0;
                margin-bottom: 12px;
            }
            .pfg-table-row {
                display: flex;
                justify-content: space-between;
                padding: 10px 0;
                border-bottom: 1px solid #f1f5f9;
            }
            .pfg-table-row span:first-child {
                width: 100px;
                height: 12px;
                background: #e2e8f0;
                border-radius: 4px;
            }
            .pfg-table-row span:last-child {
                color: #667eea;
                font-weight: 600;
            }
            
            /* CTA Overlay */
            .pfg-teaser-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .pfg-teaser-cta-box {
                text-align: center;
                max-width: 480px;
                padding: 50px 40px;
                background: #fff;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            }
            .pfg-teaser-icon {
                width: 90px;
                height: 90px;
                background: #1e293b;
                border-radius: 16px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 25px;
            }
            .pfg-teaser-icon svg {
                width: 50px;
                height: 50px;
            }
            .pfg-teaser-cta-box h2 {
                margin: 0 0 12px;
                font-size: 26px;
                color: #1e293b;
                font-weight: 600;
            }
            .pfg-teaser-cta-box > p {
                color: #64748b;
                font-size: 15px;
                line-height: 1.6;
                margin: 0 0 25px;
            }
            .pfg-teaser-features {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px 20px;
                text-align: left;
                margin-bottom: 30px;
            }
            .pfg-feature-item {
                display: flex;
                align-items: center;
                gap: 8px;
                color: #334155;
                font-size: 14px;
            }
            .pfg-feature-item .dashicons {
                color: #22c55e;
                font-size: 18px;
                width: 18px;
                height: 18px;
            }
            .pfg-upgrade-btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #fff;
                padding: 14px 32px;
                border-radius: 10px;
                font-size: 16px;
                font-weight: 600;
                text-decoration: none;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            }
            .pfg-upgrade-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
                color: #fff;
            }
            .pfg-upgrade-btn .dashicons {
                font-size: 18px;
                width: 18px;
                height: 18px;
            }
            
            @media (max-width: 782px) {
                .pfg-teaser-cards {
                    grid-template-columns: repeat(2, 1fr);
                }
                .pfg-teaser-tables {
                    grid-template-columns: 1fr;
                }
                .pfg-teaser-features {
                    grid-template-columns: 1fr;
                }
                .pfg-teaser-cta-box {
                    padding: 30px 25px;
                    margin: 20px;
                }
            }
        </style>
        <?php
    }

    /**
     * Add shortcode column to gallery list.
     *
     * @param array $columns Existing columns.
     * @return array Modified columns.
     */
    public function add_shortcode_column( $columns ) {
        $new_columns = array();

        foreach ( $columns as $key => $value ) {
            $new_columns[ $key ] = $value;
            if ( $key === 'title' ) {
                $new_columns['shortcode'] = __( 'Shortcode', 'portfolio-filter-gallery' );
                $new_columns['images']    = __( 'Images', 'portfolio-filter-gallery' );
            }
        }

        return $new_columns;
    }

    /**
     * Render custom column content.
     *
     * @param string $column  Column name.
     * @param int    $post_id Post ID.
     */
    public function render_column_content( $column, $post_id ) {
        switch ( $column ) {
            case 'shortcode':
                echo '<code>[PFG id="' . esc_attr( $post_id ) . '"]</code>';
                break;

            case 'images':
                $gallery = new PFG_Gallery( $post_id );
                $images  = $gallery->get_images();
                echo '<span class="pfg-image-count">' . count( $images ) . '</span>';
                break;
        }
    }

    /**
     * Add duplicate action link to gallery row actions.
     *
     * @param array   $actions Existing row actions.
     * @param WP_Post $post    The post object.
     * @return array Modified row actions.
     */
    public function add_duplicate_action( $actions, $post ) {
        if ( $post->post_type !== 'awl_filter_gallery' ) {
            return $actions;
        }

        if ( ! current_user_can( 'edit_posts' ) ) {
            return $actions;
        }

        $duplicate_url = wp_nonce_url(
            admin_url( 'admin.php?action=pfg_duplicate_gallery&gallery_id=' . $post->ID ),
            'pfg_duplicate_gallery_' . $post->ID
        );

        $actions['duplicate'] = sprintf(
            '<a href="%s" title="%s" style="color: #2271b1;"><span class="dashicons dashicons-admin-page" style="font-size: 14px; vertical-align: text-bottom;"></span> %s</a>',
            esc_url( $duplicate_url ),
            esc_attr__( 'Duplicate this gallery', 'portfolio-filter-gallery' ),
            esc_html__( 'Duplicate', 'portfolio-filter-gallery' )
        );

        return $actions;
    }

    /**
     * Handle gallery duplication.
     */
    public function duplicate_gallery() {
        // Verify request
        if ( ! isset( $_GET['gallery_id'] ) || ! isset( $_GET['_wpnonce'] ) ) {
            wp_die( esc_html__( 'Invalid request.', 'portfolio-filter-gallery' ) );
        }

        $gallery_id = absint( $_GET['gallery_id'] );

        // Verify nonce
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'pfg_duplicate_gallery_' . $gallery_id ) ) {
            wp_die( esc_html__( 'Security check failed.', 'portfolio-filter-gallery' ) );
        }

        // Check permissions
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( esc_html__( 'You do not have permission to duplicate galleries.', 'portfolio-filter-gallery' ) );
        }

        // Get original gallery
        $original = get_post( $gallery_id );
        if ( ! $original || $original->post_type !== 'awl_filter_gallery' ) {
            wp_die( esc_html__( 'Gallery not found.', 'portfolio-filter-gallery' ) );
        }

        // Create duplicate post
        $new_gallery = array(
            'post_title'   => sprintf(
                /* translators: %s: Original gallery title */
                __( '%s (Copy)', 'portfolio-filter-gallery' ),
                $original->post_title
            ),
            'post_status'  => 'draft',
            'post_type'    => 'awl_filter_gallery',
            'post_author'  => get_current_user_id(),
            'post_content' => $original->post_content,
            'post_excerpt' => $original->post_excerpt,
        );

        $new_id = wp_insert_post( $new_gallery );

        if ( is_wp_error( $new_id ) ) {
            wp_die( esc_html__( 'Failed to duplicate gallery.', 'portfolio-filter-gallery' ) );
        }

        // Copy all post meta
        $meta_keys = get_post_custom_keys( $gallery_id );
        if ( ! empty( $meta_keys ) ) {
            foreach ( $meta_keys as $meta_key ) {
                // Skip internal WordPress meta
                if ( strpos( $meta_key, '_edit_' ) === 0 ) {
                    continue;
                }

                $meta_values = get_post_meta( $gallery_id, $meta_key );
                foreach ( $meta_values as $meta_value ) {
                    add_post_meta( $new_id, $meta_key, $meta_value );
                }
            }
        }

        // Redirect to edit the new gallery
        wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $new_id ) );
        exit;
    }
}
