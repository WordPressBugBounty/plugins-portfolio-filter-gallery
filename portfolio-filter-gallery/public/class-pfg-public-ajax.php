<?php
/**
 * Public AJAX handler for frontend operations.
 *
 * @package    Portfolio_Filter_Gallery
 * @subpackage Portfolio_Filter_Gallery/public
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles public AJAX requests for gallery pagination.
 */
class PFG_Public_Ajax {

    /**
     * Register AJAX actions.
     */
    public function register_actions() {
        // Load more items (available for logged-in and non-logged-in users)
        add_action( 'wp_ajax_pfg_load_more', array( $this, 'load_more' ) );
        add_action( 'wp_ajax_nopriv_pfg_load_more', array( $this, 'load_more' ) );
    }

    /**
     * Load more gallery items via AJAX.
     */
    public function load_more() {
        // Verify nonce
        if ( ! check_ajax_referer( 'pfg_public_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'portfolio-filter-gallery' ) ), 403 );
        }

        $gallery_id    = isset( $_POST['gallery_id'] ) ? absint( $_POST['gallery_id'] ) : 0;
        $page          = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 2;
        $items_per_page= isset( $_POST['items_per_page'] ) ? absint( $_POST['items_per_page'] ) : 12;
        // Sanitize filter - allow commas for multi-filter, sanitize each part
        $filter_raw    = isset( $_POST['filter'] ) ? wp_unslash( $_POST['filter'] ) : '';
        $filter        = implode( ',', array_map( 'sanitize_key', explode( ',', $filter_raw ) ) );

        if ( empty( $gallery_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Gallery ID is required.', 'portfolio-filter-gallery' ) ), 400 );
        }

        // Get gallery data
        $gallery  = new PFG_Gallery( $gallery_id );
        
        if ( ! $gallery->exists() ) {
            wp_send_json_error( array( 'message' => __( 'Gallery not found.', 'portfolio-filter-gallery' ) ), 404 );
        }

        $settings = $gallery->get_settings();
        
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
                    'image_id'    => $product['image_id'],
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
        } else {
            // Regular media library images
            $images = $gallery->get_images();
        }

        // Filter images if a filter is specified
        if ( ! empty( $filter ) && $filter !== '*' ) {
            // Check if multiple filters are passed (comma-separated)
            $filter_array = strpos( $filter, ',' ) !== false ? explode( ',', $filter ) : array( $filter );
            $filter_array = array_map( 'sanitize_key', $filter_array );
            $filter_array = array_filter( $filter_array ); // Remove empty values
            
            // Get filter logic (and/or)
            $filter_logic = isset( $_POST['filter_logic'] ) ? sanitize_key( $_POST['filter_logic'] ) : 'or';
            $use_and_logic = ( $filter_logic === 'and' );
            
            // Check if this is WooCommerce source - filter by category slug directly
            if ( $source === 'woocommerce' ) {
                // For WooCommerce products, filters array contains category slugs directly
                $images = array_filter( $images, function( $image ) use ( $filter_array, $use_and_logic ) {
                    if ( empty( $image['filters'] ) ) {
                        return false;
                    }
                    
                    if ( $use_and_logic ) {
                        // AND logic: image must have ALL selected filters
                        foreach ( $filter_array as $f ) {
                            if ( ! in_array( $f, $image['filters'], true ) ) {
                                return false;
                            }
                        }
                        return true;
                    } else {
                        // OR logic: image must have ANY of the selected filters
                        foreach ( $filter_array as $f ) {
                            if ( in_array( $f, $image['filters'], true ) ) {
                                return true;
                            }
                        }
                        return false;
                    }
                } );
                $images = array_values( $images ); // Re-index
            } else {
                // For media library images, filter by pfg_filters IDs and slugs
                $all_filters = get_option( 'pfg_filters', array() );
                
                // Build a mapping of filter slugs to their keys (ID and slug)
                $filter_slug_to_keys = array(); // Maps each requested filter to its key set
                
                foreach ( $filter_array as $single_filter ) {
                    $filter_slug = $single_filter;
                    $keys_for_this_filter = array();
                    
                    // Find the filter by slug
                    $filter_id = null;
                    foreach ( $all_filters as $f ) {
                        if ( strtolower( $f['slug'] ) === strtolower( $single_filter ) ) {
                            $filter_id = $f['id'];
                            break;
                        }
                    }

                    if ( $filter_id ) {
                        // Add both ID and slug for this filter
                        $keys_for_this_filter[ strtolower( (string) $filter_id ) ] = true;
                        $keys_for_this_filter[ strtolower( $filter_slug ) ] = true;

                        // Also get all child filter IDs and slugs (for hierarchical filtering)
                        foreach ( $all_filters as $f ) {
                            if ( isset( $f['parent'] ) && $f['parent'] === $filter_id ) {
                                $keys_for_this_filter[ strtolower( (string) $f['id'] ) ] = true;
                                if ( ! empty( $f['slug'] ) ) {
                                    $keys_for_this_filter[ strtolower( $f['slug'] ) ] = true;
                                }
                                
                                // Also check for grandchildren (2nd level children)
                                $child_id = $f['id'];
                                foreach ( $all_filters as $gc ) {
                                    if ( isset( $gc['parent'] ) && $gc['parent'] === $child_id ) {
                                        $keys_for_this_filter[ strtolower( (string) $gc['id'] ) ] = true;
                                        if ( ! empty( $gc['slug'] ) ) {
                                            $keys_for_this_filter[ strtolower( $gc['slug'] ) ] = true;
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        // Filter not found in settings - use the slug directly as fallback
                        // This ensures AND logic properly fails when filter doesn't exist
                        $keys_for_this_filter[ strtolower( $filter_slug ) ] = true;
                    }
                    
                    // Always add to the mapping (even if just the slug) so AND logic checks all filters
                    $filter_slug_to_keys[ $single_filter ] = $keys_for_this_filter;
                }

                // Filter images based on logic
                if ( ! empty( $filter_slug_to_keys ) ) {
                    $images = array_filter( $images, function( $image ) use ( $filter_slug_to_keys, $use_and_logic ) {
                        if ( empty( $image['filters'] ) ) {
                            return false;
                        }
                        
                        // Get image filter keys (lowercase)
                        $img_filter_keys = array();
                        foreach ( $image['filters'] as $img_filter ) {
                            $img_filter_keys[ strtolower( (string) $img_filter ) ] = true;
                        }
                        
                        if ( $use_and_logic ) {
                            // AND logic: image must match ALL requested filters
                            foreach ( $filter_slug_to_keys as $filter_keys ) {
                                $matches_this_filter = false;
                                foreach ( array_keys( $filter_keys ) as $key ) {
                                    if ( isset( $img_filter_keys[ $key ] ) ) {
                                        $matches_this_filter = true;
                                        break;
                                    }
                                }
                                if ( ! $matches_this_filter ) {
                                    return false;
                                }
                            }
                            return true;
                        } else {
                            // OR logic: image must match ANY requested filter
                            foreach ( $filter_slug_to_keys as $filter_keys ) {
                                foreach ( array_keys( $filter_keys ) as $key ) {
                                    if ( isset( $img_filter_keys[ $key ] ) ) {
                                        return true;
                                    }
                                }
                            }
                            return false;
                        }
                    } );
                    $images = array_values( $images ); // Re-index
                }
            }
        }

        // Get exclude_ids to prevent loading duplicates (items already in DOM)
        $exclude_ids_raw = isset( $_POST['exclude_ids'] ) ? sanitize_text_field( $_POST['exclude_ids'] ) : '';
        $exclude_ids = array_filter( array_map( 'absint', explode( ',', $exclude_ids_raw ) ) );
        
        // Filter out already-loaded items
        if ( ! empty( $exclude_ids ) ) {
            $images = array_filter( $images, function( $image ) use ( $exclude_ids ) {
                return ! in_array( (int) $image['id'], $exclude_ids, true );
            } );
            $images = array_values( $images ); // Re-index
        }

        $total_items = count( $images );
        
        // Since we already filtered out existing items (via exclude_ids),
        // start from beginning of filtered array
        $offset_for_slice = 0;
        
        $items_slice = array_slice( $images, $offset_for_slice, $items_per_page );

        // Render items HTML
        ob_start();

        foreach ( $items_slice as $index => $image ) {
            $this->render_item( $image, $index, $settings, $gallery_id );
        }

        $html = ob_get_clean();

        // Calculate remaining - based on filtered items, not global offset
        // $total_items = items matching filter that are NOT already loaded
        // $items_slice = items we just loaded
        $loaded     = count( $items_slice );
        $remaining  = max( 0, $total_items - $loaded );
        $has_more   = $remaining > 0;

        wp_send_json_success( array(
            'html'      => $html,
            'has_more'  => $has_more,
            'remaining' => $remaining,
            'total'     => $total_items,
            'loaded'    => $loaded,
            'page'      => $page,
        ) );
    }

    /**
     * Render a single gallery item.
     *
     * @param array $image      Image data.
     * @param int   $index      Item index.
     * @param array $settings   Gallery settings.
     * @param int   $gallery_id Gallery ID.
     */
    protected function render_item( $image, $index, $settings, $gallery_id ) {
        // Get filter classes
        $filter_classes = $this->get_image_filter_classes( $image );
        
        // Hover effect class
        $hover_class = 'pfg-item-hover--' . esc_attr( $settings['hover_effect'] );

        // Layout-specific classes and styles
        $layout_type    = $settings['layout_type'] ?? 'grid';
        $title_position = $settings['title_position'] ?? 'overlay';
        $size_class     = '';
        $item_style     = '';

        // Get image dimensions for aspect ratio
        // For WooCommerce products, use image_id (the attachment ID) instead of id (which is product ID)
        $is_product = isset( $image['type'] ) && $image['type'] === 'product';
        $attachment_id = $is_product && ! empty( $image['image_id'] ) ? $image['image_id'] : $image['id'];
        $image_meta    = wp_get_attachment_metadata( $attachment_id );
        $width         = isset( $image_meta['width'] ) ? (int) $image_meta['width'] : 1;
        $height        = isset( $image_meta['height'] ) ? (int) $image_meta['height'] : 1;
        $aspect_ratio  = $width / max( $height, 1 );

        // Justified layout: set aspect ratio as flex-grow
        if ( $layout_type === 'justified' ) {
            $item_style = ' style="--item-aspect: ' . round( $aspect_ratio, 2 ) . '"';
        }

        // Packed layout: add size class based on aspect ratio (only for overlay mode)
        if ( $layout_type === 'packed' && $title_position !== 'below' ) {
            if ( $aspect_ratio > 1.5 ) {
                $size_class = ' pfg-item--wide';
            } elseif ( $aspect_ratio < 0.7 ) {
                $size_class = ' pfg-item--tall';
            } elseif ( $width > 1200 && $height > 1200 ) {
                $size_class = ' pfg-item--large';
            }
        }

        echo '<div class="pfg-item ' . esc_attr( $filter_classes . ' ' . $hover_class . $size_class ) . '" data-id="' . esc_attr( $image['id'] ) . '"' . $item_style . '>';

        // Type indicator icon (video or link)
        $is_video = isset( $image['type'] ) && $image['type'] === 'video' && ! empty( $image['link'] );
        $is_product = isset( $image['type'] ) && $image['type'] === 'product';
        
        if ( $is_video ) {
            // Video indicator
            echo '<span class="pfg-item-type-icon pfg-item-type-icon--video" title="' . esc_attr__( 'Video', 'portfolio-filter-gallery' ) . '">';
            echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M8 5v14l11-7z"/></svg>';
            echo '</span>';
        } elseif ( ! $is_product && ! empty( $image['link'] ) ) {
            // External link indicator (not for products)
            echo '<span class="pfg-item-type-icon pfg-item-type-icon--link" title="' . esc_attr__( 'External Link', 'portfolio-filter-gallery' ) . '">';
            echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>';
            echo '</span>';
        }

        // Render by type: product, video, or image
        if ( $is_product ) {
            $this->render_product_item( $image, $index, $settings, $gallery_id );
        } elseif ( $is_video ) {
            $this->render_video_item( $image, $index, $settings, $gallery_id );
        } else {
            $this->render_image_item( $image, $index, $settings, $gallery_id );
        }

        echo '</div>';
    }

    /**
     * Render an image item.
     */
    protected function render_image_item( $image, $index, $settings, $gallery_id ) {
        $attachment_id = $image['id'];
        $size          = $this->get_image_size( $settings );

        $img_src    = wp_get_attachment_image_url( $attachment_id, $size );
        $img_srcset = wp_get_attachment_image_srcset( $attachment_id, $size );
        $full_src   = wp_get_attachment_image_url( $attachment_id, 'full' );
        $alt        = $image['title'] ?: get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

        $has_custom_link  = ! empty( $image['link'] );
        $lightbox_enabled = $settings['lightbox'] !== 'none';

        $link_url    = $has_custom_link ? $image['link'] : $full_src;
        $link_target = $settings['url_target'];

        $is_lightbox = ! $has_custom_link && $lightbox_enabled;

        // Link attributes
        $link_attrs = 'href="' . esc_url( $link_url ) . '" class="pfg-item-link"';

        if ( $is_lightbox ) {
            $link_attrs .= ' data-lightbox="pfg-' . esc_attr( $gallery_id ) . '"';
            $link_attrs .= ' data-title="' . esc_attr( $image['title'] ) . '"';
            $link_attrs .= ' data-description="' . esc_attr( $image['description'] ?? '' ) . '"';
        } else {
            $link_attrs .= ' target="' . esc_attr( $link_target ) . '" rel="noopener"';
        }

        echo '<a ' . $link_attrs . '>';

        echo '<img';
        echo ' src="' . esc_url( $img_src ) . '"';
        if ( $img_srcset ) {
            echo ' srcset="' . esc_attr( $img_srcset ) . '"';
        }
        echo ' alt="' . esc_attr( $alt ) . '"';
        echo ' loading="lazy"';
        echo ' decoding="async"';
        echo ' class="pfg-item-image"';
        echo '>';

        // Overlay with title
        $title_position = $settings['title_position'] ?? 'overlay';
        $show_categories = ! empty( $settings['show_categories'] );
        if ( $title_position === 'overlay' && ( $settings['show_title'] || ! empty( $settings['show_numbering'] ) || $show_categories ) ) {
            echo '<div class="pfg-item-caption pfg-item-caption--overlay">';
            
            if ( ! empty( $settings['show_numbering'] ) ) {
                echo '<span class="pfg-item-number">' . esc_html( $index + 1 ) . '</span>';
            }
            
            if ( $settings['show_title'] && ! empty( $image['title'] ) ) {
                echo '<h3 class="pfg-item-title">' . esc_html( $image['title'] ) . '</h3>';
            }
            
            if ( $show_categories && ! empty( $image['filters'] ) ) {
                $filter_names = array();
                foreach ( $image['filters'] as $filter_id ) {
                    $name = $this->get_filter_name( $filter_id );
                    if ( $name ) {
                        $filter_names[] = $name;
                    }
                }
                if ( ! empty( $filter_names ) ) {
                    echo '<div class="pfg-item-categories">' . esc_html( implode( ', ', $filter_names ) ) . '</div>';
                }
            }
            
            echo '</div>';
        }

        echo '</a>';

        // Card caption (title below image)
        if ( $title_position === 'below' && ( $settings['show_title'] || $show_categories ) ) {
            echo '<div class="pfg-item-caption">';
            if ( $settings['show_title'] && ! empty( $image['title'] ) ) {
                echo '<h3 class="pfg-item-title">' . esc_html( $image['title'] ) . '</h3>';
            }
            if ( $show_categories && ! empty( $image['filters'] ) ) {
                $filter_names = array();
                foreach ( $image['filters'] as $filter_id ) {
                    $name = $this->get_filter_name( $filter_id );
                    if ( $name ) {
                        $filter_names[] = $name;
                    }
                }
                if ( ! empty( $filter_names ) ) {
                    echo '<div class="pfg-item-categories">' . esc_html( implode( ', ', $filter_names ) ) . '</div>';
                }
            }
            echo '</div>';
        }
    }

    /**
     * Render a video item.
     */
    protected function render_video_item( $image, $index, $settings, $gallery_id ) {
        $thumbnail_id = $image['id'];
        $video_url    = $image['link'];
        $size         = $this->get_image_size( $settings );

        $img_src = wp_get_attachment_image_url( $thumbnail_id, $size );
        $alt     = $image['title'] ?: get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );

        // Check title position
        $title_position = $settings['title_position'] ?? 'overlay';
        $show_categories = ! empty( $settings['show_categories'] );

        // Video link with lightbox data attributes
        $description = $image['description'] ?? '';
        echo '<a href="' . esc_url( $video_url ) . '" class="pfg-item-link pfg-item-link--video" data-lightbox="pfg-' . esc_attr( $gallery_id ) . '" data-type="video" data-title="' . esc_attr( $image['title'] ) . '" data-description="' . esc_attr( $description ) . '">';

        echo '<img src="' . esc_url( $img_src ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy" decoding="async" class="pfg-item-image">';

        // Play button overlay
        echo '<div class="pfg-video-play">';
        echo '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>';
        echo '</div>';

        // Overlay caption (when title_position is 'overlay')
        if ( $title_position === 'overlay' && ( $settings['show_title'] || $show_categories ) ) {
            echo '<div class="pfg-item-caption pfg-item-caption--overlay">';
            
            if ( $settings['show_title'] && ! empty( $image['title'] ) ) {
                echo '<h3 class="pfg-item-title">' . esc_html( $image['title'] ) . '</h3>';
            }
            
            if ( $show_categories && ! empty( $image['filters'] ) ) {
                $filter_names = array();
                foreach ( $image['filters'] as $filter_id ) {
                    $name = $this->get_filter_name( $filter_id );
                    if ( $name ) {
                        $filter_names[] = $name;
                    }
                }
                if ( ! empty( $filter_names ) ) {
                    echo '<div class="pfg-item-categories">' . esc_html( implode( ', ', $filter_names ) ) . '</div>';
                }
            }
            
            echo '</div>';
        }

        echo '</a>';

        // Card caption below image (when title_position is 'below')
        if ( $title_position === 'below' && ( $settings['show_title'] || $show_categories ) ) {
            echo '<div class="pfg-item-caption">';
            
            if ( $settings['show_title'] && ! empty( $image['title'] ) ) {
                echo '<h3 class="pfg-item-title">' . esc_html( $image['title'] ) . '</h3>';
            }
            
            if ( $show_categories && ! empty( $image['filters'] ) ) {
                $filter_names = array();
                foreach ( $image['filters'] as $filter_id ) {
                    $name = $this->get_filter_name( $filter_id );
                    if ( $name ) {
                        $filter_names[] = $name;
                    }
                }
                if ( ! empty( $filter_names ) ) {
                    echo '<div class="pfg-item-categories">' . esc_html( implode( ', ', $filter_names ) ) . '</div>';
                }
            }
            
            echo '</div>';
        }
    }

    /**
     * Render a WooCommerce product item.
     */
    protected function render_product_item( $image, $index, $settings, $gallery_id ) {
        // Product-specific settings
        $show_price      = isset( $settings['woo_show_price'] ) ? $settings['woo_show_price'] : true;
        $show_sale_badge = isset( $settings['woo_show_sale_badge'] ) ? $settings['woo_show_sale_badge'] : true;
        $show_title      = isset( $settings['woo_show_title'] ) ? $settings['woo_show_title'] : true;
        $link_target     = isset( $settings['woo_link_target'] ) ? $settings['woo_link_target'] : '_self';
        
        // Layout settings
        $title_position  = isset( $settings['title_position'] ) ? $settings['title_position'] : 'overlay';
        $show_categories = ! empty( $settings['show_categories'] );
        
        // Product data
        $product_data = isset( $image['product'] ) ? $image['product'] : array();
        $is_on_sale   = ! empty( $product_data['on_sale'] );
        
        // Get image using image_id if available
        $size = $this->get_image_size( $settings );
        if ( ! empty( $image['image_id'] ) ) {
            $img_src    = wp_get_attachment_image_url( $image['image_id'], $size );
            $img_srcset = wp_get_attachment_image_srcset( $image['image_id'], $size );
        } else {
            $img_src    = $image['thumbnail'];
            $img_srcset = '';
        }
        
        // Product link
        echo '<a href="' . esc_url( $image['link'] ) . '" target="' . esc_attr( $link_target ) . '" class="pfg-item-link">';
        
        // Sale badge
        if ( $show_sale_badge && $is_on_sale ) {
            echo '<span class="pfg-sale-badge">' . esc_html__( 'Sale!', 'portfolio-filter-gallery' ) . '</span>';
        }
        
        // Product image
        echo '<img';
        echo ' src="' . esc_url( $img_src ) . '"';
        if ( $img_srcset ) {
            echo ' srcset="' . esc_attr( $img_srcset ) . '"';
        }
        echo ' alt="' . esc_attr( $image['title'] ) . '"';
        echo ' loading="lazy"';
        echo ' decoding="async"';
        echo ' class="pfg-item-image"';
        echo '>';
        
        // Overlay for overlay mode
        if ( $title_position === 'overlay' && ( $show_title || $show_price ) ) {
            echo '<div class="pfg-item-overlay">';
            
            if ( $show_title && ! empty( $image['title'] ) ) {
                echo '<h3 class="pfg-item-title">' . esc_html( $image['title'] ) . '</h3>';
            }
            
            if ( $show_price && isset( $product_data['price'] ) ) {
                echo '<span class="pfg-product-price">' . wp_kses_post( $product_data['price'] ) . '</span>';
            }
            
            if ( $show_categories && ! empty( $image['filters'] ) ) {
                echo '<div class="pfg-item-categories">' . esc_html( implode( ', ', array_slice( $image['filters'], 0, 2 ) ) ) . '</div>';
            }
            
            echo '</div>';
        }
        
        echo '</a>';
        
        // Caption below for below mode
        if ( $title_position === 'below' && ( $show_title || $show_price ) ) {
            echo '<div class="pfg-item-caption">';
            
            if ( $show_title && ! empty( $image['title'] ) ) {
                echo '<h3 class="pfg-item-title">' . esc_html( $image['title'] ) . '</h3>';
            }
            
            if ( $show_price && isset( $product_data['price'] ) ) {
                echo '<span class="pfg-product-caption-price">' . wp_kses_post( $product_data['price'] ) . '</span>';
            }
            
            if ( $show_categories && ! empty( $image['filters'] ) ) {
                echo '<div class="pfg-item-categories">' . esc_html( implode( ', ', array_slice( $image['filters'], 0, 2 ) ) ) . '</div>';
            }
            
            echo '</div>';
        }
    }

    /**
     * Get filter classes for an image.
     */
    protected function get_image_filter_classes( $image ) {
        if ( empty( $image['filters'] ) ) {
            return '';
        }

        $classes = array();
        
        // For WooCommerce products, filters already contain category slugs
        $is_product = isset( $image['type'] ) && $image['type'] === 'product';
        
        if ( $is_product ) {
            // Products have category slugs directly in filters array
            foreach ( $image['filters'] as $slug ) {
                if ( is_string( $slug ) ) {
                    $classes[] = 'pfg-filter-' . sanitize_html_class( $slug );
                }
            }
        } else {
            // Media library images may have filter IDs or slugs - need to handle both
            $all_filters = get_option( 'pfg_filters', array() );
            
            foreach ( $image['filters'] as $filter_key ) {
                $filter_key_lower = strtolower( (string) $filter_key );
                foreach ( $all_filters as $filter ) {
                    // Match by ID or slug
                    $filter_id = strtolower( (string) $filter['id'] );
                    $filter_slug = strtolower( $filter['slug'] );
                    if ( $filter_id === $filter_key_lower || $filter_slug === $filter_key_lower ) {
                        $classes[] = 'pfg-filter-' . $filter['slug'];
                        break;
                    }
                }
            }
        }

        return implode( ' ', $classes );
    }

    /**
     * Get appropriate image size based on columns.
     */
    protected function get_image_size( $settings ) {
        $columns = max( $settings['columns_lg'], 1 );

        if ( $columns >= 4 ) {
            return 'medium';
        } elseif ( $columns >= 3 ) {
            return 'medium_large';
        } else {
            return 'large';
        }
    }

    /**
     * Get filter name by ID from pfg_filters option.
     *
     * @param int $filter_id Filter ID.
     * @return string Filter name or empty string.
     */
    protected function get_filter_name( $filter_id ) {
        static $filters = null;
        
        if ( $filters === null ) {
            $filters = get_option( 'pfg_filters', array() );
        }
        
        $filter_key_lower = strtolower( (string) $filter_id );
        foreach ( $filters as $filter ) {
            // Match by ID or slug
            $fid = strtolower( (string) ( $filter['id'] ?? '' ) );
            $fslug = strtolower( $filter['slug'] ?? '' );
            if ( $fid === $filter_key_lower || $fslug === $filter_key_lower ) {
                return $filter['name'] ?? '';
            }
        }
        
        return '';
    }
}
