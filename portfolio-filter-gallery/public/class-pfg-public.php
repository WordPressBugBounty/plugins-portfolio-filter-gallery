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
     * Register the stylesheets for the public-facing side.
     */
    public function enqueue_styles() {
        // Only enqueue if a gallery is on the page
        if ( empty( self::$galleries_on_page ) ) {
            return;
        }

        // Core gallery styles (lightweight, no Bootstrap)
        wp_enqueue_style(
            'pfg-core',
            PFG_PLUGIN_URL . 'public/css/pfg-gallery.css',
            array(),
            $this->version
        );

        // Get global settings
        $global_settings = get_option( 'pfg_global_settings', array() );
        $lightbox_library = isset( $global_settings['lightbox_library'] ) ? $global_settings['lightbox_library'] : 'built-in';

        // Check if any gallery needs specific features
        $needs_lightbox = false;
        $needs_hover    = false;

        foreach ( self::$galleries_on_page as $gallery_id ) {
            $gallery  = new PFG_Gallery( $gallery_id );
            $settings = $gallery->get_settings();

            if ( $settings['lightbox'] !== 'none' ) {
                $needs_lightbox = true;
            }

            if ( ! empty( $settings['hover_effect'] ) && $settings['hover_effect'] !== 'none' ) {
                $needs_hover = true;
            }
        }

        // Conditionally load lightbox styles based on global library setting
        if ( $needs_lightbox && $lightbox_library !== 'none' ) {
            if ( $lightbox_library === 'built-in' ) {
                wp_enqueue_style(
                    'pfg-lightbox',
                    PFG_PLUGIN_URL . 'public/css/pfg-lightbox.css',
                    array(),
                    $this->version
                );
            } elseif ( $lightbox_library === 'fancybox' ) {
                // Fancybox CSS
                wp_enqueue_style(
                    'fancybox',
                    'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css',
                    array(),
                    '5.0'
                );
            } elseif ( $lightbox_library === 'photoswipe' ) {
                // PhotoSwipe CSS
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
                    $this->version
                );
            }
        }

        // Conditionally load hover effect styles
        if ( $needs_hover ) {
            wp_enqueue_style(
                'pfg-hover',
                PFG_PLUGIN_URL . 'public/css/pfg-hover.css',
                array(),
                $this->version
            );
        }

        // Output global custom CSS if set
        if ( ! empty( $global_settings['custom_css'] ) ) {
            wp_add_inline_style( 'pfg-core', $global_settings['custom_css'] );
        }
    }

    /**
     * Register the JavaScript for the public-facing side.
     */
    public function enqueue_scripts() {
        // Always REGISTER scripts (even if not enqueuing) to prevent theme compatibility issues
        // Divi and other themes check for registered script handles, which causes PHP warnings if not registered
        wp_register_script(
            'pfg-gallery',
            PFG_PLUGIN_URL . 'public/js/pfg-gallery.js',
            array(),
            $this->version,
            true
        );

        // Only ENQUEUE if a gallery is on the page
        if ( empty( self::$galleries_on_page ) ) {
            return;
        }

        // Enqueue the already-registered script
        wp_enqueue_script( 'pfg-gallery' );

        // Get global settings
        $global_settings = get_option( 'pfg_global_settings', array() );
        $lightbox_library = isset( $global_settings['lightbox_library'] ) ? $global_settings['lightbox_library'] : 'built-in';

        // Check if any gallery needs lightbox
        $needs_lightbox = false;
        foreach ( self::$galleries_on_page as $gallery_id ) {
            $gallery  = new PFG_Gallery( $gallery_id );
            $settings = $gallery->get_settings();

            if ( $settings['lightbox'] !== 'none' ) {
                $needs_lightbox = true;
                break;
            }
        }

        // Load appropriate lightbox script based on global setting
        if ( $needs_lightbox && $lightbox_library !== 'none' ) {
            if ( $lightbox_library === 'built-in' ) {
                wp_enqueue_script(
                    'pfg-lightbox',
                    PFG_PLUGIN_URL . 'public/js/pfg-lightbox.js',
                    array(),
                    $this->version,
                    true
                );
            } elseif ( $lightbox_library === 'fancybox' ) {
                // Fancybox JS
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
                                return !pfgItem.classList.contains("pfg-item--hidden") && !pfgItem.classList.contains("pfg-item--hiding");
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
                            setTimeout(bindFancybox, 350);
                        });
                    })();
                ' );
            } elseif ( $lightbox_library === 'photoswipe' ) {
                // PhotoSwipe JS
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
                    })();\r
                ' );
            } elseif ( $lightbox_library === 'ld-lightbox' ) {
                // LD Lightbox JS (Legacy support)
                wp_enqueue_script(
                    'ld-lightbox',
                    PFG_PLUGIN_URL . 'public/lightbox/ld-lightbox/js/lightbox.js',
                    array( 'jquery' ),
                    $this->version,
                    true
                );
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
        $async_scripts = array( 'pfg-gallery', 'pfg-lightbox' );

        if ( in_array( $handle, $async_scripts, true ) ) {
            return str_replace( ' src', ' defer src', $tag );
        }

        return $tag;
    }

    /**
     * Add preload hints for critical assets.
     */
    public function add_preload_hints() {
        if ( empty( self::$galleries_on_page ) ) {
            return;
        }

        // Preload core CSS
        echo '<link rel="preload" href="' . esc_url( PFG_PLUGIN_URL . 'public/css/pfg-gallery.css' ) . '" as="style">' . "\n";
    }
}
