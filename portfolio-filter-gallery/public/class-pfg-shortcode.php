<?php
/**
 * Shortcode handler for the plugin.
 *
 * @package    Portfolio_Filter_Gallery
 * @subpackage Portfolio_Filter_Gallery/public
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles shortcode rendering.
 */
class PFG_Shortcode {

    /**
     * Register shortcodes.
     */
    public function register() {
        // Legacy shortcode (keep for backward compatibility)
        add_shortcode( 'PFG', array( $this, 'render_legacy' ) );
        
        // New format shortcodes
        add_shortcode( 'portfolio_gallery', array( $this, 'render' ) );
        add_shortcode( 'Portfolio_Gallery', array( $this, 'render_legacy' ) );
    }

    /**
     * Render legacy shortcode format.
     *
     * @param array $atts Shortcode attributes.
     * @return string Gallery HTML.
     */
    public function render_legacy( $atts ) {
        $atts = shortcode_atts(
            array(
                'id' => 0,
            ),
            $atts,
            'PFG'
        );

        return $this->render( array( 'id' => $atts['id'] ) );
    }

    /**
     * Render gallery shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string Gallery HTML.
     */
    public function render( $atts ) {
        $atts = shortcode_atts(
            array(
                'id'             => 0,
                'columns'        => null,
                'columns_tablet' => null,
                'columns_mobile' => null,
                'gap'            => null,
                'filter'         => null, // Pre-select a filter
                'template'       => null, // Apply a starter template
                'hover_effect'   => null, // Override hover effect
                'show_filters'   => null, // Override show filters
            ),
            $atts,
            'portfolio_gallery'
        );

        $gallery_id = absint( $atts['id'] );

        if ( ! $gallery_id ) {
            return $this->render_error( __( 'Gallery ID is required.', 'portfolio-filter-gallery' ) );
        }

        // Load gallery
        $gallery = new PFG_Gallery( $gallery_id );

        if ( ! $gallery->exists() ) {
            return $this->render_error( __( 'Gallery not found.', 'portfolio-filter-gallery' ) );
        }

        // Register gallery for conditional asset loading
        PFG_Public::register_gallery_on_page( $gallery_id );

        // Build shortcode overrides
        $overrides = array();

        if ( $atts['columns'] !== null ) {
            $overrides['columns_lg'] = absint( $atts['columns'] );
        }
        if ( $atts['columns_tablet'] !== null ) {
            $overrides['columns_md'] = absint( $atts['columns_tablet'] );
        }
        if ( $atts['columns_mobile'] !== null ) {
            $overrides['columns_sm'] = absint( $atts['columns_mobile'] );
        }
        if ( $atts['gap'] !== null ) {
            $overrides['gap'] = absint( $atts['gap'] );
        }
        if ( $atts['hover_effect'] !== null ) {
            $overrides['hover_effect'] = sanitize_text_field( $atts['hover_effect'] );
        }
        if ( $atts['show_filters'] !== null ) {
            $overrides['show_filters'] = ( $atts['show_filters'] === '1' || $atts['show_filters'] === 'true' || $atts['show_filters'] === true );
        }

        // Get settings with overrides
        $settings = $gallery->get_settings( $overrides );
        
        // Check if this is a WooCommerce product gallery
        $source = isset( $settings['source'] ) ? $settings['source'] : 'media';
        
        if ( $source === 'woocommerce' && PFG_WooCommerce::is_active() && PFG_Features::is_premium() ) {
            // Fetch WooCommerce products dynamically
            $woo_args = array(
                'categories' => isset( $settings['woo_categories'] ) ? $settings['woo_categories'] : array(),
                'orderby'    => isset( $settings['woo_orderby'] ) ? $settings['woo_orderby'] : 'date',
                'order'      => isset( $settings['woo_order'] ) ? strtoupper( $settings['woo_order'] ) : 'DESC',
                'limit'      => isset( $settings['woo_limit'] ) ? intval( $settings['woo_limit'] ) : -1,
                'image_size' => isset( $settings['image_size'] ) ? $settings['image_size'] : 'large',
            );
            
            $products = PFG_WooCommerce::get_products( $woo_args );
            
            // Convert products to gallery format
            $images = array();
            foreach ( $products as $product ) {
                $images[] = array(
                    'id'          => $product['id'],
                    'image_id'    => $product['image_id'], // For srcset
                    'type'        => 'product',
                    'title'       => $product['title'],
                    'description' => $product['description'],
                    'thumbnail'   => $product['thumbnail'],
                    'full'        => $product['full'],
                    'link'        => $product['link'],
                    'filters'     => $product['filters'],
                    'product'     => $product['product'],
                );
            }
            
            // Override filters to use WC categories
            $settings['use_woo_filters'] = true;
        } else {
            // Regular media library images
            $images = $gallery->get_images();
        }

        if ( empty( $images ) ) {
            return $this->render_empty( $settings );
        }

        // Apply template - check shortcode attribute first, then saved gallery setting
        $template_to_apply = null;
        if ( $atts['template'] !== null ) {
            $template_to_apply = $atts['template'];
        } elseif ( ! empty( $settings['template'] ) ) {
            $template_to_apply = $settings['template'];
        }

        if ( $template_to_apply ) {
            $settings = $this->apply_template( $template_to_apply, $settings );
        }

        // Enqueue assets directly (since shortcode runs after wp_enqueue_scripts)
        $this->enqueue_assets( $gallery );

        // Start output buffering
        ob_start();

        // Render the gallery
        $renderer = new PFG_Renderer( $gallery_id, $settings, $images );
        $renderer->render( $atts['filter'] );

        return ob_get_clean();
    }

    /**
     * Enqueue required assets for the gallery.
     *
     * @param PFG_Gallery $gallery Gallery object.
     */
    protected function enqueue_assets( $gallery ) {
        $version = defined( 'PFG_VERSION' ) ? PFG_VERSION : '2.0.0';
        $settings = $gallery->get_settings();

        // Get global settings
        $global_settings = get_option( 'pfg_global_settings', array() );
        $lightbox_library = isset( $global_settings['lightbox_library'] ) ? $global_settings['lightbox_library'] : 'built-in';

        // Core gallery styles
        wp_enqueue_style(
            'pfg-gallery',
            PFG_PLUGIN_URL . 'public/css/pfg-gallery.css',
            array(),
            $version
        );

        // Output global custom CSS if set
        if ( ! empty( $global_settings['custom_css'] ) ) {
            wp_add_inline_style( 'pfg-gallery', $global_settings['custom_css'] );
        }

        // Lightbox styles based on global library setting
        if ( isset( $settings['lightbox'] ) && $settings['lightbox'] !== 'none' && $lightbox_library !== 'none' ) {
            if ( $lightbox_library === 'built-in' ) {
                wp_enqueue_style(
                    'pfg-lightbox',
                    PFG_PLUGIN_URL . 'public/css/pfg-lightbox.css',
                    array(),
                    $version
                );
            } elseif ( $lightbox_library === 'fancybox' ) {
                wp_enqueue_style(
                    'fancybox',
                    'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css',
                    array(),
                    '5.0'
                );
            } elseif ( $lightbox_library === 'photoswipe' ) {
                wp_enqueue_style(
                    'photoswipe',
                    'https://cdn.jsdelivr.net/npm/photoswipe@5.4/dist/photoswipe.css',
                    array(),
                    '5.4'
                );
            } elseif ( $lightbox_library === 'ld-lightbox' ) {
                // LD Lightbox CSS (Legacy support)
                wp_enqueue_style(
                    'ld-lightbox',
                    PFG_PLUGIN_URL . 'public/lightbox/ld-lightbox/css/lightbox.css',
                    array(),
                    $version
                );
            }
        }

        // Hover effect styles
        if ( ! empty( $settings['hover_effect'] ) && $settings['hover_effect'] !== 'none' ) {
            wp_enqueue_style(
                'pfg-hover',
                PFG_PLUGIN_URL . 'public/css/pfg-hover.css',
                array(),
                $version
            );
        }

        // Core gallery script
        wp_enqueue_script(
            'pfg-gallery',
            PFG_PLUGIN_URL . 'public/js/pfg-gallery.js',
            array(),
            $version,
            true
        );

        // Lightbox script based on global library setting
        if ( isset( $settings['lightbox'] ) && $settings['lightbox'] !== 'none' && $lightbox_library !== 'none' ) {
            if ( $lightbox_library === 'built-in' ) {
                wp_enqueue_script(
                    'pfg-lightbox',
                    PFG_PLUGIN_URL . 'public/js/pfg-lightbox.js',
                    array(),
                    $version,
                    true
                );
            } elseif ( $lightbox_library === 'fancybox' ) {
                wp_enqueue_script(
                    'fancybox',
                    'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js',
                    array(),
                    '5.0',
                    true
                );
                // Filter-aware Fancybox initialization
                wp_add_inline_script( 'fancybox', '
                    (function() {
                        // Function to get visible lightbox items
                        function getVisibleItems(container) {
                            const allItems = container.querySelectorAll("[data-lightbox]");
                            return Array.from(allItems).filter(item => {
                                const pfgItem = item.closest(".pfg-item");
                                if (!pfgItem) return true;
                                return !pfgItem.classList.contains("pfg-item--hidden");
                            });
                        }
                        
                        // Function to bind Fancybox to visible items only
                        function bindFancybox() {
                            // Destroy existing Fancybox instances
                            if (typeof Fancybox !== "undefined" && Fancybox.destroy) {
                                Fancybox.destroy();
                            }
                            
                            // Find all gallery wrappers
                            const wrappers = document.querySelectorAll(".pfg-gallery-wrapper");
                            wrappers.forEach((wrapper, index) => {
                                const galleryId = "pfg-gallery-" + index;
                                
                                // First, REMOVE data-fancybox from ALL items
                                wrapper.querySelectorAll("[data-lightbox]").forEach(item => {
                                    item.removeAttribute("data-fancybox");
                                });
                                
                                // Then, add data-fancybox ONLY to visible items
                                const visibleItems = getVisibleItems(wrapper);
                                visibleItems.forEach(item => {
                                    item.setAttribute("data-fancybox", galleryId);
                                });
                            });
                            
                            // Bind Fancybox with caption support
                            Fancybox.bind("[data-fancybox]", {
                                Thumbs: {
                                    autoStart: true
                                },
                                caption: function(fancybox, slide) {
                                    const el = slide.triggerEl;
                                    if (!el) return "";
                                    
                                    // Get gallery wrapper to check settings
                                    const wrapper = el.closest(".pfg-gallery-wrapper");
                                    const showTitle = wrapper ? wrapper.dataset.lightboxTitle !== "false" : true;
                                    const showDesc = wrapper ? wrapper.dataset.lightboxDescription === "true" : false;
                                    
                                    const title = el.getAttribute("data-title") || "";
                                    const description = el.getAttribute("data-description") || "";
                                    
                                    let caption = "";
                                    if (showTitle && title) {
                                        caption += "<strong>" + title + "</strong>";
                                    }
                                    if (showDesc && description) {
                                        caption += (caption ? "<br>" : "") + description;
                                    }
                                    
                                    return caption;
                                }
                            });
                        }
                        
                        // Initial bind on DOM ready
                        if (document.readyState === "loading") {
                            document.addEventListener("DOMContentLoaded", bindFancybox);
                        } else {
                            bindFancybox();
                        }
                        
                        // Re-bind when filters change
                        document.addEventListener("pfg:filtered", function() {
                            setTimeout(bindFancybox, 100);
                        });
                    })();
                ' );
            } elseif ( $lightbox_library === 'photoswipe' ) {
                wp_enqueue_script(
                    'photoswipe',
                    'https://cdn.jsdelivr.net/npm/photoswipe@5.4/dist/umd/photoswipe.umd.min.js',
                    array(),
                    '5.4',
                    true
                );
                wp_enqueue_script(
                    'photoswipe-lightbox',
                    'https://cdn.jsdelivr.net/npm/photoswipe@5.4/dist/umd/photoswipe-lightbox.umd.min.js',
                    array( 'photoswipe' ),
                    '5.4',
                    true
                );
                // Filter-aware PhotoSwipe initialization with captions and video support
                wp_add_inline_script( 'photoswipe-lightbox', '
                    (function() {
                        let lightbox = null;
                        
                        // Function to get visible lightbox items
                        function getVisibleItems(container) {
                            const allItems = container.querySelectorAll("a[data-lightbox]");
                            return Array.from(allItems).filter(item => {
                                const pfgItem = item.closest(".pfg-item");
                                if (!pfgItem) return true;
                                return !pfgItem.classList.contains("pfg-item--hidden") && 
                                       !pfgItem.classList.contains("pfg-item--paginated-hidden");
                            });
                        }
                        
                        // Parse video URL for embed
                        function getVideoEmbed(url) {
                            // YouTube
                            let match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&?\/]+)/);
                            if (match) {
                                return `<iframe src="https://www.youtube.com/embed/${match[1]}?autoplay=1" frameborder="0" allowfullscreen allow="autoplay"></iframe>`;
                            }
                            // Vimeo
                            match = url.match(/vimeo\.com\/(\d+)/);
                            if (match) {
                                return `<iframe src="https://player.vimeo.com/video/${match[1]}?autoplay=1" frameborder="0" allowfullscreen allow="autoplay"></iframe>`;
                            }
                            // Direct video file
                            if (url.match(/\.(mp4|webm|ogg)$/i)) {
                                return `<video src="${url}" controls autoplay style="max-width:100%;max-height:100%"></video>`;
                            }
                            return null;
                        }
                        
                        // Function to initialize PhotoSwipe
                        function initPhotoSwipe() {
                            // Destroy existing instance if any
                            if (lightbox) {
                                lightbox.destroy();
                                lightbox = null;
                            }
                            
                            // Get all gallery grids
                            const grids = document.querySelectorAll(".pfg-grid");
                            if (grids.length === 0) return;
                            
                            // Create new lightbox with proper settings
                            lightbox = new PhotoSwipeLightbox({
                                pswpModule: PhotoSwipe,
                                bgOpacity: 0.95,
                                showHideAnimationType: "zoom",
                                zoom: true,
                                wheelToZoom: true,
                                padding: { top: 40, bottom: 80, left: 20, right: 20 }
                            });
                            
                            // Add caption UI
                            lightbox.on("uiRegister", function() {
                                lightbox.pswp.ui.registerElement({
                                    name: "caption",
                                    order: 9,
                                    isButton: false,
                                    appendTo: "root",
                                    html: "",
                                    onInit: (el, pswp) => {
                                        el.style.cssText = "position:absolute;bottom:0;left:0;right:0;padding:20px 60px;background:linear-gradient(to top,rgba(0,0,0,0.8) 0%,rgba(0,0,0,0.5) 60%,transparent 100%);color:#fff;text-align:center;";
                                        
                                        pswp.on("change", () => {
                                            const slide = pswp.currSlide;
                                            const data = slide.data;
                                            
                                            let html = "";
                                            if (data.showTitle && data.title) {
                                                html += `<strong style="font-size:18px;display:block;margin-bottom:6px;">${data.title}</strong>`;
                                            }
                                            if (data.showDesc && data.description) {
                                                html += `<span style="font-size:14px;opacity:0.9;">${data.description}</span>`;
                                            }
                                            el.innerHTML = html;
                                            el.style.display = html ? "" : "none";
                                        });
                                    }
                                });
                            });
                            
                            // Handle click on visible items only
                            grids.forEach(grid => {
                                grid.addEventListener("click", function(e) {
                                    const link = e.target.closest("a[data-lightbox]");
                                    if (!link) return;
                                    
                                    // Check if this item is visible
                                    const pfgItem = link.closest(".pfg-item");
                                    if (pfgItem && (pfgItem.classList.contains("pfg-item--hidden") || pfgItem.classList.contains("pfg-item--paginated-hidden"))) return;
                                    
                                    e.preventDefault();
                                    
                                    // Get gallery wrapper settings
                                    const wrapper = link.closest(".pfg-gallery-wrapper");
                                    const showTitle = wrapper ? wrapper.dataset.lightboxTitle !== "false" : true;
                                    const showDesc = wrapper ? wrapper.dataset.lightboxDescription === "true" : false;
                                    
                                    // Get visible items in this grid
                                    const visibleItems = getVisibleItems(grid);
                                    const clickedIndex = visibleItems.indexOf(link);
                                    
                                    if (clickedIndex === -1) return;
                                    
                                    // Build data source from visible items
                                    const dataSource = visibleItems.map(item => {
                                        const isVideo = item.dataset.type === "video";
                                        const img = item.querySelector("img");
                                        
                                        if (isVideo) {
                                            const videoEmbed = getVideoEmbed(item.href);
                                            return {
                                                html: `<div style="display:flex;justify-content:center;align-items:center;width:100%;height:100%;padding:40px;">${videoEmbed || "<p>Video not supported</p>"}</div>`,
                                                title: item.dataset.title || "",
                                                description: item.dataset.description || "",
                                                showTitle: showTitle,
                                                showDesc: showDesc
                                            };
                                        }
                                        
                                        return {
                                            src: item.href,
                                            w: parseInt(item.dataset.pswpWidth) || 0,
                                            h: parseInt(item.dataset.pswpHeight) || 0,
                                            alt: img?.alt || "",
                                            title: item.dataset.title || img?.alt || "",
                                            description: item.dataset.description || "",
                                            showTitle: showTitle,
                                            showDesc: showDesc
                                        };
                                    });
                                    
                                    // Open lightbox at clicked index
                                    lightbox.loadAndOpen(clickedIndex, dataSource);
                                });
                            });
                            
                            // Auto-detect image dimensions
                            lightbox.addFilter("itemData", (itemData) => {
                                if (itemData.w === 0 || itemData.h === 0) {
                                    // Use reasonable defaults that will be updated when image loads
                                    itemData.w = 1200;
                                    itemData.h = 900;
                                }
                                return itemData;
                            });
                            
                            lightbox.init();
                        }
                        
                        // Initialize on DOM ready
                        if (document.readyState === "loading") {
                            document.addEventListener("DOMContentLoaded", initPhotoSwipe);
                        } else {
                            initPhotoSwipe();
                        }
                        
                        // Re-initialize when filters change
                        document.addEventListener("pfg:filtered", function() {
                            setTimeout(initPhotoSwipe, 100);
                        });
                    })();
                ' );
            } elseif ( $lightbox_library === 'ld-lightbox' ) {
                // LD Lightbox JS (Legacy support) - requires jQuery
                wp_enqueue_script(
                    'ld-lightbox',
                    PFG_PLUGIN_URL . 'public/lightbox/ld-lightbox/js/lightbox.js',
                    array( 'jquery' ),
                    $version,
                    true
                );
                // Filter-aware LD Lightbox initialization
                wp_add_inline_script( 'ld-lightbox', '
                    (function($) {
                        // Function to check if an item is visible (not filtered out)
                        function isItemVisible($item) {
                            var $pfgItem = $item.closest(".pfg-item");
                            if (!$pfgItem.length) return true;
                            return !$pfgItem.hasClass("pfg-item--hidden") && 
                                   !$pfgItem.hasClass("pfg-item--hiding") && 
                                   !$pfgItem.hasClass("pfg-item--paginated-hidden");
                        }
                        
                        // Override the lightbox start method to only include visible items
                        if (typeof lightbox !== "undefined" && lightbox.start) {
                            var originalStart = lightbox.start.bind(lightbox);
                            
                            lightbox.start = function($link) {
                                var self = this;
                                var dataLightboxValue = $link.attr("data-lightbox");
                                
                                if (dataLightboxValue) {
                                    // Build album from visible items only
                                    this.album = [];
                                    var imageNumber = 0;
                                    var visibleIndex = 0;
                                    
                                    var $allLinks = $($link.prop("tagName") + "[data-lightbox=\"" + dataLightboxValue + "\"]");
                                    
                                    $allLinks.each(function(i) {
                                        var $el = $(this);
                                        if (isItemVisible($el)) {
                                            self.album.push({
                                                alt: $el.attr("data-alt"),
                                                link: $el.attr("href"),
                                                title: $el.attr("data-title") || $el.attr("title")
                                            });
                                            if (this === $link[0]) {
                                                imageNumber = visibleIndex;
                                            }
                                            visibleIndex++;
                                        }
                                    });
                                    
                                    if (this.album.length === 0) return;
                                    
                                    // Size and show overlay
                                    $(window).on("resize", $.proxy(this.sizeOverlay, this));
                                    this.sizeOverlay();
                                    
                                    // Position and show lightbox
                                    var top = $(window).scrollTop() + this.options.positionFromTop;
                                    var left = $(window).scrollLeft();
                                    this.$lightbox.css({
                                        top: top + "px",
                                        left: left + "px"
                                    }).fadeIn(this.options.fadeDuration);
                                    
                                    if (this.options.disableScrolling) {
                                        $("body").addClass("lb-disable-scrolling");
                                    }
                                    
                                    this.changeImage(imageNumber);
                                } else {
                                    // Fall back to original for non-data-lightbox links
                                    originalStart($link);
                                }
                            };
                        }
                    })(jQuery);
                ' );
            }
        }

        // Localize script
        wp_localize_script(
            'pfg-gallery',
            'pfgData',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'pfg_public_nonce' ),
                'analyticsNonce' => wp_create_nonce( 'pfg_analytics_nonce' ),
                'i18n'    => array(
                    'all'       => __( 'All', 'portfolio-filter-gallery' ),
                    'loading'   => __( 'Loading...', 'portfolio-filter-gallery' ),
                    'noResults' => __( 'No items found.', 'portfolio-filter-gallery' ),
                    'prev'      => __( 'Previous', 'portfolio-filter-gallery' ),
                    'next'      => __( 'Next', 'portfolio-filter-gallery' ),
                    'close'     => __( 'Close', 'portfolio-filter-gallery' ),
                ),
                'lightboxLibrary' => $lightbox_library,
            )
        );
    }

    /**
     * Render error message.
     *
     * @param string $message Error message.
     * @return string Error HTML.
     */
    protected function render_error( $message ) {
        if ( current_user_can( 'edit_posts' ) ) {
            return '<div class="pfg-error">' . esc_html( $message ) . '</div>';
        }
        return '';
    }

    /**
     * Render empty gallery message.
     *
     * @param array $settings Gallery settings.
     * @return string Empty message HTML.
     */
    protected function render_empty( $settings ) {
        if ( current_user_can( 'edit_posts' ) ) {
            return '<div class="pfg-empty">' . esc_html__( 'This gallery has no images. Add some images in the gallery editor.', 'portfolio-filter-gallery' ) . '</div>';
        }
        return '';
    }

    /**
     * Apply a starter template to settings.
     * Template provides defaults, but user-saved settings take priority.
     *
     * @param string $template_name Template name.
     * @param array  $settings      Current settings (user-saved).
     * @return array Modified settings.
     */
    protected function apply_template( $template_name, $settings ) {
        $templates = PFG_Templates::get_templates();

        if ( isset( $templates[ $template_name ] ) ) {
            $template_settings = $templates[ $template_name ]['settings'];
            // User settings override template defaults
            return wp_parse_args( $settings, $template_settings );
        }

        return $settings;
    }
}
