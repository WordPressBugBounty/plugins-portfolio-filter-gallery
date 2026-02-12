<?php
/**
 * Gallery Images Meta Box Template.
 *
 * @package    Portfolio_Filter_Gallery
 * @subpackage Portfolio_Filter_Gallery/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Helper function fallback
if ( ! function_exists( 'pfg_is_premium' ) ) {
    function pfg_is_premium() {
        return defined( 'PFG_PREMIUM' ) && PFG_PREMIUM === true;
    }
}

$gallery_id = $post->ID;
$gallery    = new PFG_Gallery( $gallery_id );
$settings   = $gallery->get_settings();
$source     = isset( $settings['source'] ) ? $settings['source'] : 'media';

// Check if this is a WooCommerce gallery
$is_woo_gallery = ( $source === 'woocommerce' && PFG_WooCommerce::is_active() && pfg_is_premium() );

if ( $is_woo_gallery ) {
    // Fetch WooCommerce products
    $woo_args = array(
        'categories' => isset( $settings['woo_categories'] ) ? $settings['woo_categories'] : array(),
        'orderby'    => isset( $settings['woo_orderby'] ) ? $settings['woo_orderby'] : 'date',
        'order'      => isset( $settings['woo_order'] ) ? strtoupper( $settings['woo_order'] ) : 'DESC',
        'limit'      => isset( $settings['woo_limit'] ) ? intval( $settings['woo_limit'] ) : -1,
        'image_size' => 'thumbnail', // Use thumbnails for faster admin preview
    );
    $products = PFG_WooCommerce::get_products( $woo_args );
    $images = array(); // Not used for WooCommerce
} else {
    // Regular media library images
    $images = $gallery->get_images();
    $products = array();
}

// Get filters - use new format first, then legacy
$filters = get_option( 'pfg_filters', array() );

if ( empty( $filters ) ) {
    // Legacy fallback
    $legacy_filters = get_option( 'awl_portfolio_filter_gallery_categories', array() );
    foreach ( $legacy_filters as $id => $name ) {
        if ( is_string( $name ) ) {
            $filters[] = array(
                'id'     => sanitize_key( $id ),
                'name'   => $name,
                'slug'   => sanitize_title( $name ),
                'parent' => '',
            );
        }
    }
}

// Build hierarchical tree for display
function pfg_build_filter_tree_for_images( $filters, $parent_id = '' ) {
    $tree = array();
    foreach ( $filters as $filter ) {
        $filter_parent = isset( $filter['parent'] ) ? $filter['parent'] : '';
        if ( $filter_parent === $parent_id ) {
            $filter['children'] = pfg_build_filter_tree_for_images( $filters, $filter['id'] );
            $tree[] = $filter;
        }
    }
    return $tree;
}

// Render hierarchical filter checkboxes with tree structure matching hierarchy preview
function pfg_render_filter_checkboxes( $filters, $depth = 0, $is_first_child = true ) {
    $html = '';
    
    foreach ( $filters as $filter ) {
        // Skip 'all' filter - images are automatically included in 'All' view
        if ( strtolower( $filter['slug'] ) === 'all' ) {
            continue;
        }
        
        $has_children = ! empty( $filter['children'] );
        $color = isset( $filter['color'] ) && $filter['color'] ? $filter['color'] : '#94a3b8';
        
        // Wrapper for each filter item
        $html .= '<div class="pfg-tree-filter-item" data-depth="' . $depth . '">';
        
        // Collapsible group wrapper if has children
        if ( $has_children ) {
            $html .= '<div class="pfg-filter-collapsible-group" data-expanded="true">';
        }
        
        // Main filter row
        $html .= '<div class="pfg-tree-filter-row" style="padding-left: ' . ( $depth * 20 ) . 'px;">';
        
        // 1. Checkbox (first) - include data-parent for JS child detection
        $parent_id = isset( $filter['parent'] ) ? $filter['parent'] : '';
        $html .= '<label class="pfg-tree-checkbox-label" data-filter="' . esc_attr( $filter['slug'] ) . '" data-color="' . esc_attr( $color ) . '" data-parent="' . esc_attr( $parent_id ) . '">';
        $html .= '<input type="checkbox" value="' . esc_attr( $filter['slug'] ) . '">';
        
        // 2. Collapse toggle (+/-) for parents
        if ( $has_children ) {
            $html .= '<span class="pfg-tree-toggle" title="' . esc_attr__( 'Expand/Collapse', 'portfolio-filter-gallery' ) . '">−</span>';
        } else {
            // Spacer for alignment when no toggle
            $html .= '<span class="pfg-tree-toggle-spacer"></span>';
        }
        
        // 3. Tree connector for child items
        if ( $depth > 0 ) {
            $html .= '<span class="pfg-tree-connector">└</span>';
        }
        
        // 4. Color dot
        $html .= '<span class="pfg-tree-dot" style="background-color: ' . esc_attr( $color ) . ';"></span>';
        
        // 5. Filter name
        $html .= '<span class="pfg-tree-filter-name">' . esc_html( $filter['name'] ) . '</span>';
        $html .= '</label>';
        $html .= '</div>'; // Close row
        
        // Recursively render children
        if ( $has_children ) {
            $html .= '<div class="pfg-tree-children pfg-collapsible-content">';
            $html .= pfg_render_filter_checkboxes( $filter['children'], $depth + 1, true );
            $html .= '</div>';
            $html .= '</div>'; // Close collapsible group
        }
        
        $html .= '</div>'; // Close item
    }
    return $html;
}

// Render filter options for bulk dropdown
function pfg_render_filter_options_for_bulk( $filters, $depth = 0 ) {
    foreach ( $filters as $filter ) {
        // Skip 'all' filter
        if ( strtolower( $filter['slug'] ) === 'all' ) {
            continue;
        }
        
        $indent = str_repeat( '— ', $depth );
        $has_children = ! empty( $filter['children'] );
        
        echo '<option value="' . esc_attr( $filter['id'] ) . '">' . esc_html( $indent . $filter['name'] ) . '</option>';
        
        if ( $has_children ) {
            pfg_render_filter_options_for_bulk( $filter['children'], $depth + 1 );
        }
    }
}

$filter_tree = pfg_build_filter_tree_for_images( $filters );
?>

<div class="pfg-meta-box pfg-images-meta-box">
    
    <?php if ( pfg_is_premium() && PFG_WooCommerce::is_active() ) : ?>
    <!-- Gallery Source Selector -->
    <div class="pfg-source-selector" style="margin-bottom: 20px; padding: 15px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
        <label style="display: flex; align-items: center; gap: 15px;">
            <span style="font-weight: 600; color: #1e293b; white-space: nowrap;">
                <span class="dashicons dashicons-database" style="margin-right: 5px;"></span>
                <?php esc_html_e( 'Gallery Source:', 'portfolio-filter-gallery' ); ?>
            </span>
            <select name="pfg_settings[source]" id="pfg-gallery-source" class="pfg-select" style="flex: 1; max-width: 300px;">
                <option value="media" <?php selected( $source, 'media' ); ?>><?php esc_html_e( 'Media Library (Images)', 'portfolio-filter-gallery' ); ?></option>
                <option value="woocommerce" <?php selected( $source, 'woocommerce' ); ?>><?php esc_html_e( 'WooCommerce Products', 'portfolio-filter-gallery' ); ?></option>
            </select>
            <span class="pfg-source-loading" style="display: none; color: #3b82f6;">
                <span class="spinner is-active" style="float: none; margin: 0;"></span>
                <?php esc_html_e( 'Loading preview...', 'portfolio-filter-gallery' ); ?>
            </span>
        </label>
    </div>
    <script>
    jQuery(document).ready(function($) {
        var sourceSelect = $('#pfg-gallery-source');
        var loadingSpan = $('.pfg-source-loading');
        var imageGrid = $('#pfg-image-grid');
        var wooNotice = $('.pfg-woo-notice');
        var uploadArea = $('.pfg-upload-area, .pfg-button-group, .pfg-bulk-actions');
        var galleryId = <?php echo absint( $post->ID ); ?>;
        
        sourceSelect.on('change', function() {
            var source = $(this).val();
            loadingSpan.show();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pfg_preview_source',
                    nonce: '<?php echo wp_create_nonce( 'pfg_admin_nonce' ); ?>',
                    gallery_id: galleryId,
                    source: source
                },
                success: function(response) {
                    loadingSpan.hide();
                    if (response.success) {
                        imageGrid.html(response.data.html);
                        
                        // Toggle WooCommerce notice and upload area visibility
                        if (source === 'woocommerce') {
                            wooNotice.show();
                            uploadArea.hide();
                        } else {
                            wooNotice.hide();
                            uploadArea.show();
                        }
                    }
                },
                error: function() {
                    loadingSpan.hide();
                    alert('<?php echo esc_js( __( 'Error loading preview', 'portfolio-filter-gallery' ) ); ?>');
                }
            });
        });
    });
    </script>
    <?php endif; ?>

    <?php if ( $is_woo_gallery ) : ?>
    <!-- WooCommerce Mode Notice -->
    <div class="pfg-woo-notice" style="background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%); border: 1px solid #3b82f6; border-radius: 8px; padding: 15px 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
        <span class="dashicons dashicons-cart" style="color: #3b82f6; font-size: 24px;"></span>
        <div>
            <strong style="color: #1e40af;"><?php esc_html_e( 'WooCommerce Mode Active', 'portfolio-filter-gallery' ); ?></strong>
            <p style="margin: 4px 0 0; color: #475569; font-size: 13px;">
                <?php esc_html_e( 'Products are fetched dynamically. Configure in the WooCommerce tab.', 'portfolio-filter-gallery' ); ?>
            </p>
        </div>
    </div>
    
    <!-- Product Preview Grid -->
    <div class="pfg-image-grid" id="pfg-image-grid">
        <?php if ( empty( $products ) ) : ?>
            <div class="pfg-no-images">
                <span class="dashicons dashicons-products"></span>
                <p><?php esc_html_e( 'No products found. Add products in WooCommerce or adjust category settings.', 'portfolio-filter-gallery' ); ?></p>
            </div>
        <?php else : ?>
            <?php foreach ( $products as $index => $product ) : ?>
            <div class="pfg-image-item pfg-product-preview-item" data-id="<?php echo esc_attr( $product['id'] ); ?>">
                <img src="<?php echo esc_url( $product['thumbnail'] ); ?>" 
                     alt="<?php echo esc_attr( $product['title'] ); ?>" 
                     class="pfg-image-thumb"
                     loading="lazy">
                
                <?php if ( ! empty( $product['product']['on_sale'] ) ) : ?>
                <span class="pfg-product-sale-tag" style="position: absolute; top: 8px; left: 8px; background: #ef4444; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">
                    <?php esc_html_e( 'Sale', 'portfolio-filter-gallery' ); ?>
                </span>
                <?php endif; ?>
                
                <div class="pfg-image-info">
                    <p class="pfg-image-title"><?php echo esc_html( $product['title'] ); ?></p>
                    <div class="pfg-product-meta" style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                        <span style="color: #16a34a; font-weight: 600; font-size: 13px;"><?php echo wp_kses_post( $product['product']['price'] ); ?></span>
                    </div>
                    <?php if ( ! empty( $product['filters'] ) ) : ?>
                    <div class="pfg-image-filters">
                        <?php foreach ( array_slice( $product['filters'], 0, 2 ) as $cat_slug ) : ?>
                        <span class="pfg-image-filter-tag"><?php echo esc_html( $cat_slug ); ?></span>
                        <?php endforeach; ?>
                        <?php if ( count( $product['filters'] ) > 2 ) : ?>
                        <span class="pfg-image-filter-tag">+<?php echo count( $product['filters'] ) - 2; ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <?php else : ?>
    
    <!-- Regular Upload Area -->
    <div class="pfg-upload-area" id="pfg-upload-area">
        <div class="pfg-upload-icon">
            <span class="dashicons dashicons-cloud-upload"></span>
        </div>
        <div class="pfg-upload-text">
            <?php esc_html_e( 'Drag & drop images here or click to upload', 'portfolio-filter-gallery' ); ?>
        </div>
        <div class="pfg-upload-hint">
            <?php esc_html_e( 'Supports JPG, PNG, GIF, WebP', 'portfolio-filter-gallery' ); ?>
        </div>
    </div>
    
    <div class="pfg-upload-actions">
        <button type="button" class="pfg-btn pfg-btn-primary pfg-add-images">
            <span class="dashicons dashicons-plus-alt"></span>
            <?php esc_html_e( 'Add Images', 'portfolio-filter-gallery' ); ?>
        </button>
        
        <?php if ( pfg_is_premium() && class_exists( 'WooCommerce' ) ) : ?>
        <button type="button" class="pfg-btn pfg-btn-secondary pfg-import-products">
            <span class="dashicons dashicons-cart"></span>
            <?php esc_html_e( 'Import from Products', 'portfolio-filter-gallery' ); ?>
        </button>
        <?php endif; ?>
    </div>
    
    <!-- Bulk Actions Bar (Always visible when images exist) -->
    <div class="pfg-bulk-actions" id="pfg-bulk-actions" style="<?php echo empty( $images ) ? 'display: none;' : 'display: flex;'; ?> margin: 15px 0; padding: 12px 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; align-items: center; gap: 15px;">
        <label class="pfg-select-all-label" style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500; color: #475569;">
            <input type="checkbox" id="pfg-select-all" style="width: 18px; height: 18px; cursor: pointer;">
            <?php esc_html_e( 'Select All', 'portfolio-filter-gallery' ); ?>
        </label>
        <span class="pfg-selected-count" style="color: #64748b; font-size: 13px;">
            <span id="pfg-selected-num">0</span> <?php esc_html_e( 'selected', 'portfolio-filter-gallery' ); ?>
        </span>
        
        <!-- Bulk Apply Filters Dropdown (Premium-style) -->
        <div class="pfg-bulk-filters-dropdown" style="position: relative; display: none; margin-left: auto;">
            <button type="button" class="pfg-btn pfg-btn-secondary pfg-bulk-filters-btn" style="display: flex; align-items: center; gap: 6px;">
                <span class="dashicons dashicons-filter"></span>
                <?php esc_html_e( 'Apply Filters', 'portfolio-filter-gallery' ); ?>
                <span class="dashicons dashicons-arrow-down-alt2" style="font-size: 14px;"></span>
            </button>
            <div class="pfg-bulk-filters-menu" style="display: none; position: absolute; top: 100%; right: 0; min-width: 280px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); z-index: 100; margin-top: 4px;">
                <div style="padding: 12px; border-bottom: 1px solid #e2e8f0;">
                    <label class="pfg-form-label" style="font-weight: 600; color: #1e293b; display: block; margin-bottom: 8px;">
                        <?php esc_html_e( 'Apply Mode:', 'portfolio-filter-gallery' ); ?>
                    </label>
                    <select id="pfg-bulk-filter-mode" class="pfg-select" style="width: 100%;">
                        <option value="add"><?php esc_html_e( 'Add to Existing Filters', 'portfolio-filter-gallery' ); ?></option>
                        <option value="replace"><?php esc_html_e( 'Replace All Filters', 'portfolio-filter-gallery' ); ?></option>
                        <option value="remove"><?php esc_html_e( 'Remove These Filters', 'portfolio-filter-gallery' ); ?></option>
                    </select>
                </div>
                <div style="padding: 12px; max-height: 250px; overflow-y: auto;">
                    <label class="pfg-form-label" style="font-weight: 600; color: #1e293b; display: block; margin-bottom: 8px;">
                        <?php esc_html_e( 'Select Filters:', 'portfolio-filter-gallery' ); ?>
                    </label>
                    <div id="pfg-bulk-filter-list">
                        <?php 
                        $filter_tree = pfg_build_filter_tree_for_images( $filters );
                        foreach ( $filter_tree as $filter ) : 
                            if ( strtolower( $filter['slug'] ) === 'all' ) continue;
                            $color = isset( $filter['color'] ) && $filter['color'] ? $filter['color'] : '#94a3b8';
                        ?>
                            <label class="pfg-bulk-filter-item" style="display: flex; align-items: center; gap: 8px; padding: 6px 4px; cursor: pointer; border-radius: 4px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                <input type="checkbox" value="<?php echo esc_attr( $filter['id'] ); ?>" class="pfg-bulk-filter-checkbox" style="width: 16px; height: 16px;">
                                <span class="pfg-tag-dot" style="width: 10px; height: 10px; border-radius: 50%; background: <?php echo esc_attr( $color ); ?>;"></span>
                                <span style="flex: 1; color: #1e293b;"><?php echo esc_html( $filter['name'] ); ?></span>
                            </label>
                            <?php if ( ! empty( $filter['children'] ) ) : ?>
                                <?php foreach ( $filter['children'] as $child ) : 
                                    $child_color = isset( $child['color'] ) && $child['color'] ? $child['color'] : '#94a3b8';
                                ?>
                                    <label class="pfg-bulk-filter-item" style="display: flex; align-items: center; gap: 8px; padding: 6px 4px 6px 24px; cursor: pointer; border-radius: 4px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                        <input type="checkbox" value="<?php echo esc_attr( $child['id'] ); ?>" class="pfg-bulk-filter-checkbox" style="width: 16px; height: 16px;">
                                        <span style="color: #94a3b8; font-size: 12px;">└</span>
                                        <span class="pfg-tag-dot" style="width: 10px; height: 10px; border-radius: 50%; background: <?php echo esc_attr( $child_color ); ?>;"></span>
                                        <span style="flex: 1; color: #1e293b;"><?php echo esc_html( $child['name'] ); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div style="padding: 12px; border-top: 1px solid #e2e8f0; display: flex; gap: 8px;">
                    <button type="button" class="pfg-btn pfg-btn-primary pfg-apply-bulk-filters" style="flex: 1;">
                        <?php esc_html_e( 'Apply', 'portfolio-filter-gallery' ); ?>
                    </button>
                    <button type="button" class="pfg-btn pfg-btn-secondary pfg-cancel-bulk-filters">
                        <?php esc_html_e( 'Cancel', 'portfolio-filter-gallery' ); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <button type="button" class="pfg-btn pfg-btn-danger pfg-delete-selected" style="background: #ef4444; color: #fff; display: none;">
            <span class="dashicons dashicons-trash"></span>
            <?php esc_html_e( 'Delete Selected', 'portfolio-filter-gallery' ); ?>
        </button>
        
        <!-- Sort Order (inline in toolbar) -->
        <div style="border-left: 1px solid #e2e8f0; padding-left: 15px; margin-left: auto; display: flex; align-items: center; gap: 8px;">
            <span class="dashicons dashicons-sort" style="font-size: 16px; width: 16px; height: 16px; color: #94a3b8;"></span>
            <select id="pfg-sort-order-images" class="pfg-select" style="max-width: 200px; margin: 0; font-size: 13px;">
                <option value="custom" <?php selected( $settings['sort_order'] ?? 'custom', 'custom' ); ?>><?php esc_html_e( 'Custom Order', 'portfolio-filter-gallery' ); ?></option>
                <option value="date_newest" <?php selected( $settings['sort_order'] ?? 'custom', 'date_newest' ); ?>><?php esc_html_e( 'Newest First', 'portfolio-filter-gallery' ); ?></option>
                <option value="date_oldest" <?php selected( $settings['sort_order'] ?? 'custom', 'date_oldest' ); ?>><?php esc_html_e( 'Oldest First', 'portfolio-filter-gallery' ); ?></option>
                <option value="title_asc" <?php selected( $settings['sort_order'] ?? 'custom', 'title_asc' ); ?>><?php esc_html_e( 'Title A → Z', 'portfolio-filter-gallery' ); ?></option>
                <option value="title_desc" <?php selected( $settings['sort_order'] ?? 'custom', 'title_desc' ); ?>><?php esc_html_e( 'Title Z → A', 'portfolio-filter-gallery' ); ?></option>
                <option value="random" <?php selected( $settings['sort_order'] ?? 'custom', 'random' ); ?>><?php esc_html_e( 'Random', 'portfolio-filter-gallery' ); ?></option>
            </select>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Defer sort setup until master array and pagination are ready
        setTimeout(function() {
            var $settingsSelect = $('select[name="pfg_settings[sort_order]"]');
            var $imagesSelect = $('#pfg-sort-order-images');
            
            // Store original order for "custom" restore
            var originalOrder = [];
            if (typeof window.pfgGetMasterImages === 'function') {
                var master = window.pfgGetMasterImages();
                for (var i = 0; i < master.length; i++) {
                    originalOrder.push(parseInt(master[i].id, 10));
                }
            }
            
            function sortMasterImages(order) {
                if (typeof window.pfgGetMasterImages !== 'function') return;
                var master = window.pfgGetMasterImages();
                if (!master.length) return;
                
                switch(order) {
                    case 'title_asc':
                        master.sort(function(a, b) {
                            var tA = (a.title || '').toLowerCase();
                            var tB = (b.title || '').toLowerCase();
                            return tA.localeCompare(tB);
                        });
                        break;
                    case 'title_desc':
                        master.sort(function(a, b) {
                            var tA = (a.title || '').toLowerCase();
                            var tB = (b.title || '').toLowerCase();
                            return tB.localeCompare(tA);
                        });
                        break;
                    case 'date_newest':
                        master.sort(function(a, b) {
                            return parseInt(b.id, 10) - parseInt(a.id, 10);
                        });
                        break;
                    case 'date_oldest':
                        master.sort(function(a, b) {
                            return parseInt(a.id, 10) - parseInt(b.id, 10);
                        });
                        break;
                    case 'random':
                        for (var i = master.length - 1; i > 0; i--) {
                            var j = Math.floor(Math.random() * (i + 1));
                            var temp = master[i]; master[i] = master[j]; master[j] = temp;
                        }
                        break;
                    case 'custom':
                    default:
                        master.sort(function(a, b) {
                            var idA = parseInt(a.id, 10);
                            var idB = parseInt(b.id, 10);
                            var indexA = originalOrder.indexOf(idA);
                            var indexB = originalOrder.indexOf(idB);
                            if (indexA === -1) indexA = 99999;
                            if (indexB === -1) indexB = 99999;
                            return indexA - indexB;
                        });
                        break;
                }
                
                // Save sorted order to DB via AJAX, then reload page 1
                var $grid = $('#pfg-image-grid');
                var adminNonce = '<?php echo wp_create_nonce( "pfg_admin_nonce" ); ?>';
                var galleryId = <?php echo (int) $gallery_id; ?>;
                
                $grid.addClass('pfg-loading');
                
                // Save all images as a single chunk
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pfg_save_images_chunk',
                        nonce: adminNonce,
                        gallery_id: galleryId,
                        chunk_index: 0,
                        total_chunks: 1,
                        images: JSON.stringify(master)
                    },
                    success: function() {
                        // Now reload page 1 from server (DB now has sorted order)
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'pfg_get_admin_images_page',
                                nonce: adminNonce,
                                gallery_id: galleryId,
                                page: 1,
                                per_page: 50
                            },
                            success: function(response) {
                                if (response.success) {
                                    $grid.html(response.data.html);
                                    // Update pagination UI if visible
                                    if ($('#pfg-pagination-controls').length) {
                                        $('#pfg-showing-start').text(response.data.showing_start || 1);
                                        $('#pfg-showing-end').text(response.data.showing_end || master.length);
                                        $('#pfg-total-count').text(response.data.total_images || master.length);
                                        $('#pfg-page-input').val(1);
                                    }
                                    // Refresh sortable
                                    if ($.fn.sortable && $grid.data('ui-sortable')) {
                                        $grid.sortable('refresh');
                                    }
                                }
                                $grid.removeClass('pfg-loading');
                            },
                            error: function() {
                                $grid.removeClass('pfg-loading');
                            }
                        });
                    },
                    error: function() {
                        // Fallback: just sort visible DOM elements
                        var $items = $grid.find('.pfg-image-item').detach().toArray();
                        if ($items.length) {
                            var idOrder = master.map(function(m) { return parseInt(m.id, 10); });
                            $items.sort(function(a, b) {
                                return idOrder.indexOf($(a).data('id')) - idOrder.indexOf($(b).data('id'));
                            });
                            $.each($items, function(i, item) { $grid.append(item); });
                        }
                        $grid.removeClass('pfg-loading');
                    }
                });
                
                console.log('PFG: Sorted ' + master.length + ' images by ' + order);
            }
            
            // Sort order change handlers
            $imagesSelect.on('change', function() {
                var val = $(this).val();
                $settingsSelect.val(val);
                $settingsSelect.find('option').prop('selected', false);
                $settingsSelect.find('option[value="' + val + '"]').prop('selected', true);
                sortMasterImages(val);
            });
            
            $settingsSelect.on('change', function() {
                var val = $(this).val();
                $imagesSelect.val(val);
                sortMasterImages(val);
            });
            
            // CRITICAL: Sync sort_order to settings select just before form submission
            $('form#post').on('submit', function() {
                var currentVal = $imagesSelect.val();
                if (currentVal && $settingsSelect.length) {
                    $settingsSelect.val(currentVal);
                    $settingsSelect.find('option').prop('selected', false);
                    $settingsSelect.find('option[value="' + currentVal + '"]').prop('selected', true);
                }
            });
        }, 500); // Wait for master array initialization
    });
    </script>
    
    <!-- Pagination Controls (placed above grid like Pro version) -->
    <div id="pfg-pagination-controls" class="pfg-pagination-controls" style="display: none; margin-bottom: 15px; padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
            <div class="pfg-pagination-info" style="color: #64748b; font-size: 13px;">
                <?php esc_html_e( 'Showing', 'portfolio-filter-gallery' ); ?>
                <span id="pfg-page-start">1</span>-<span id="pfg-page-end">50</span>
                <?php esc_html_e( 'of', 'portfolio-filter-gallery' ); ?>
                <span id="pfg-total-images">0</span>
                <?php esc_html_e( 'images', 'portfolio-filter-gallery' ); ?>
            </div>
            <div class="pfg-pagination-buttons" style="display: flex; align-items: center; gap: 8px;">
                <button type="button" id="pfg-page-prev" class="pfg-btn pfg-btn-secondary" style="padding: 6px 12px;" disabled>
                    <span class="dashicons dashicons-arrow-left-alt2" style="width: 16px; height: 16px; font-size: 16px;"></span>
                    <?php esc_html_e( 'Previous', 'portfolio-filter-gallery' ); ?>
                </button>
                <span id="pfg-page-numbers" style="display: flex; gap: 4px;"></span>
                <button type="button" id="pfg-page-next" class="pfg-btn pfg-btn-secondary" style="padding: 6px 12px;">
                    <?php esc_html_e( 'Next', 'portfolio-filter-gallery' ); ?>
                    <span class="dashicons dashicons-arrow-right-alt2" style="width: 16px; height: 16px; font-size: 16px;"></span>
                </button>
            </div>
            <div class="pfg-pagination-loading" style="display: none; color: #3b82f6;">
                <span class="spinner is-active" style="float: none; margin: 0 5px 0 0;"></span>
                <?php esc_html_e( 'Loading...', 'portfolio-filter-gallery' ); ?>
            </div>
        </div>
    </div>
    
    <!-- Image Grid -->
    <div class="pfg-image-grid" id="pfg-image-grid">
        <?php if ( empty( $images ) ) : ?>
            <div class="pfg-no-images">
                <span class="dashicons dashicons-format-gallery"></span>
                <p><?php esc_html_e( 'No images yet. Add some to get started!', 'portfolio-filter-gallery' ); ?></p>
            </div>
        <?php else : ?>
            <?php 
            // For large galleries, only render first page (50 images) on initial load
            // JavaScript pagination handles subsequent pages from masterImagesArray
            $pagination_threshold = 50;
            $display_images = ( count( $images ) > $pagination_threshold ) ? array_slice( $images, 0, $pagination_threshold, true ) : $images;
            ?>
            <?php foreach ( $display_images as $index => $image ) : 
                $attachment = get_post( $image['id'] );
                if ( ! $attachment ) continue;
                
                $thumb_url = wp_get_attachment_image_url( $image['id'], 'thumbnail' );
                $title     = ! empty( $image['title'] ) ? $image['title'] : $attachment->post_title;
                $image_filters = isset( $image['filters'] ) ? $image['filters'] : array();
            ?>
                <div class="pfg-image-item" data-id="<?php echo esc_attr( $image['id'] ); ?>" data-index="<?php echo esc_attr( $index ); ?>">
                    
                    <!-- Selection Checkbox -->
                    <label class="pfg-image-checkbox" style="position: absolute; top: 8px; left: 8px; z-index: 10;">
                        <input type="checkbox" class="pfg-image-select" style="width: 18px; height: 18px; cursor: pointer;">
                    </label>
                    
                    <?php 
                    // Type indicator icons
                    $image_type = isset( $image['type'] ) ? $image['type'] : 'image';
                    $image_link = isset( $image['link'] ) ? $image['link'] : '';
                    
                    // Detect video source for different styling
                    $video_source = '';
                    if ( $image_type === 'video' && $image_link ) {
                        if ( strpos( $image_link, 'youtube.com' ) !== false || strpos( $image_link, 'youtu.be' ) !== false ) {
                            $video_source = 'youtube';
                        } elseif ( strpos( $image_link, 'vimeo.com' ) !== false ) {
                            $video_source = 'vimeo';
                        }
                    }
                    
                    if ( $image_type === 'video' || $image_type === 'url' ) : 
                        $badge_class = 'pfg-image-type-badge';
                        if ( $video_source === 'youtube' ) {
                            $badge_class .= ' pfg-badge-youtube';
                            $badge_title = __( 'YouTube Video', 'portfolio-filter-gallery' );
                            $badge_icon = 'dashicons-youtube';
                        } elseif ( $video_source === 'vimeo' ) {
                            $badge_class .= ' pfg-badge-vimeo';
                            $badge_title = __( 'Vimeo Video', 'portfolio-filter-gallery' );
                            $badge_icon = 'dashicons-video-alt3';
                        } elseif ( $image_type === 'video' ) {
                            $badge_class .= ' pfg-badge-video';
                            $badge_title = __( 'Video Lightbox', 'portfolio-filter-gallery' );
                            $badge_icon = 'dashicons-video-alt3';
                        } else {
                            $badge_title = __( 'External Link', 'portfolio-filter-gallery' );
                            $badge_icon = 'dashicons-external';
                        }
                    ?>
                    <div class="<?php echo esc_attr( $badge_class ); ?>" title="<?php echo esc_attr( $badge_title ); ?>">
                        <span class="dashicons <?php echo esc_attr( $badge_icon ); ?>"></span>
                    </div>
                    <?php endif; ?>
                    
                    <img src="<?php echo esc_url( $thumb_url ); ?>" 
                         alt="<?php echo esc_attr( $title ); ?>" 
                         class="pfg-image-thumb"
                         loading="lazy">
                    
                    <div class="pfg-image-actions">
                        <button type="button" class="pfg-image-action pfg-image-edit" title="<?php esc_attr_e( 'Edit', 'portfolio-filter-gallery' ); ?>">
                            <span class="dashicons dashicons-edit"></span>
                        </button>
                        <button type="button" class="pfg-image-action pfg-image-delete" title="<?php esc_attr_e( 'Delete', 'portfolio-filter-gallery' ); ?>">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                    
                    <div class="pfg-image-info">
                        <p class="pfg-image-title"><?php echo esc_html( $title ); ?></p>
                        
                        <?php if ( ! empty( $image_filters ) ) : ?>
                        <div class="pfg-image-filters">
                            <?php foreach ( $image_filters as $filter_id ) : 
                                // Find filter in our already-loaded filters array
                                $filter = null;
                                foreach ( $filters as $f ) {
                                    if ( $f['id'] === $filter_id || $f['slug'] === $filter_id ) {
                                        $filter = $f;
                                        break;
                                    }
                                }
                                if ( $filter ) : 
                                $tag_color = isset( $filter['color'] ) && $filter['color'] ? $filter['color'] : '#94a3b8';
                                $is_child = ! empty( $filter['parent'] ); ?>
                                <span class="pfg-image-filter-tag"><?php if ( $is_child ) : ?><span class="pfg-tag-connector">└</span><?php endif; ?><span class="pfg-tag-dot" style="background-color: <?php echo esc_attr( $tag_color ); ?>;"></span><?php echo esc_html( $filter['name'] ); ?></span>
                                <?php endif; 
                            endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Hidden inputs -->
                    <input type="hidden" name="pfg_images[<?php echo esc_attr( $index ); ?>][id]" value="<?php echo esc_attr( $image['id'] ); ?>">
                    <input type="hidden" name="pfg_images[<?php echo esc_attr( $index ); ?>][title]" value="<?php echo esc_attr( $title ); ?>">
                    <input type="hidden" name="pfg_images[<?php echo esc_attr( $index ); ?>][alt]" value="<?php echo esc_attr( $image['alt'] ?? '' ); ?>">
                    <input type="hidden" name="pfg_images[<?php echo esc_attr( $index ); ?>][description]" value="<?php echo esc_attr( $image['description'] ?? '' ); ?>">
                    <input type="hidden" name="pfg_images[<?php echo esc_attr( $index ); ?>][link]" value="<?php echo esc_url( $image['link'] ?? '' ); ?>">
                    <input type="hidden" name="pfg_images[<?php echo esc_attr( $index ); ?>][type]" value="<?php echo esc_attr( $image['type'] ?? 'image' ); ?>">
                    <input type="hidden" name="pfg_images[<?php echo esc_attr( $index ); ?>][filters]" value="<?php echo esc_attr( implode( ',', $image_filters ) ); ?>">
                    <input type="hidden" name="pfg_images[<?php echo esc_attr( $index ); ?>][product_id]" value="<?php echo esc_attr( $image['product_id'] ?? '' ); ?>">
                    <input type="hidden" name="pfg_images[<?php echo esc_attr( $index ); ?>][product_name]" value="<?php echo esc_attr( $image['product_name'] ?? '' ); ?>">
                    <input type="hidden" name="pfg_images[<?php echo esc_attr( $index ); ?>][original_id]" value="<?php echo esc_attr( $image['original_id'] ?? $image['id'] ); ?>">
                    
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <?php endif; ?>
    
    <!-- Hidden field for JSON-serialized image data (bypasses max_input_vars limit) -->
    <?php
    // Pre-populate with all images for masterImagesArray initialization
    $json_images = array();
    if ( ! empty( $images ) && is_array( $images ) ) {
        foreach ( $images as $img ) {
            $json_images[] = array(
                'id'           => (int) $img['id'],
                'title'        => $img['title'] ?? '',
                'alt'          => $img['alt'] ?? '',
                'description'  => $img['description'] ?? '',
                'link'         => $img['link'] ?? '',
                'type'         => $img['type'] ?? 'image',
                'filters'      => is_array( $img['filters'] ?? null ) ? implode( ',', $img['filters'] ) : ( $img['filters'] ?? '' ),
                'product_id'   => $img['product_id'] ?? '',
                'product_name' => $img['product_name'] ?? '',
                'original_id'  => $img['original_id'] ?? $img['id'],
            );
        }
    }
    ?>
    <textarea name="pfg_images_json" id="pfg-images-json" style="display: none;"><?php echo esc_textarea( wp_json_encode( $json_images ) ); ?></textarea>
    
</div>

<!-- Image Edit Modal -->
<div id="pfg-image-modal" class="pfg-modal" style="display: none;">
    <div class="pfg-modal-content">
        <div class="pfg-modal-header">
            <button type="button" class="pfg-modal-nav pfg-modal-prev" title="<?php esc_attr_e( 'Previous Image', 'portfolio-filter-gallery' ); ?>" style="display: none;">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
            </button>
            <h3><?php esc_html_e( 'Edit Image', 'portfolio-filter-gallery' ); ?> <span class="pfg-modal-counter"></span></h3>
            <button type="button" class="pfg-modal-nav pfg-modal-next" title="<?php esc_attr_e( 'Next Image', 'portfolio-filter-gallery' ); ?>" style="display: none;">
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </button>
            <button type="button" class="pfg-modal-close">&times;</button>
        </div>
        
        <div class="pfg-modal-body">
            <div class="pfg-modal-preview">
                <img src="" alt="" id="pfg-modal-image">
            </div>
            
            <div class="pfg-modal-fields">
                <div class="pfg-form-row">
                    <label class="pfg-form-label"><?php esc_html_e( 'Title', 'portfolio-filter-gallery' ); ?></label>
                    <input type="text" id="pfg-modal-title" class="pfg-input">
                </div>
                
                <div class="pfg-form-row">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Alt Text', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'Describes the image for accessibility and SEO', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <input type="text" id="pfg-modal-alt" class="pfg-input">
                </div>
                
                <div class="pfg-form-row">
                    <label class="pfg-form-label"><?php esc_html_e( 'Description', 'portfolio-filter-gallery' ); ?></label>
                    <textarea id="pfg-modal-description" class="pfg-textarea" rows="3"></textarea>
                </div>
                
                <div class="pfg-form-row">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Link Type', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'What should happen when clicking this image?', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <select id="pfg-modal-type" class="pfg-select">
                        <option value="image"><?php esc_html_e( 'Open Image in Lightbox', 'portfolio-filter-gallery' ); ?></option>
                        <option value="video"><?php esc_html_e( 'Open Video in Lightbox (YouTube, Vimeo, etc.)', 'portfolio-filter-gallery' ); ?></option>
                        <option value="url"><?php esc_html_e( 'Link to External URL', 'portfolio-filter-gallery' ); ?></option>
                    </select>
                </div>
                
                <div class="pfg-form-row pfg-link-url-row" style="display: none;">
                    <label class="pfg-form-label"><?php esc_html_e( 'URL', 'portfolio-filter-gallery' ); ?></label>
                    <div class="pfg-video-url-wrap">
                        <input type="text" id="pfg-modal-link" class="pfg-input" placeholder="https://" inputmode="url">
                        <small class="pfg-url-hint"></small>
                        <button type="button" id="pfg-fetch-video-thumb" class="pfg-btn pfg-btn-secondary" style="margin-top: 8px; display: none;"<?php echo ! pfg_is_premium() ? ' disabled title="' . esc_attr__( 'Upgrade to Pro to auto-fetch video thumbnails', 'portfolio-filter-gallery' ) . '"' : ''; ?>>
                            <span class="dashicons dashicons-format-video" style="margin-right: 5px;"></span>
                            <?php esc_html_e( 'Fetch Video Thumbnail', 'portfolio-filter-gallery' ); ?>
                            <?php if ( ! pfg_is_premium() ) : ?>
                                <span class="pfg-pro-badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; font-size: 9px; padding: 2px 6px; border-radius: 3px; margin-left: 6px; font-weight: 600; text-transform: uppercase;">PRO</span>
                            <?php endif; ?>
                        </button>
                        <button type="button" id="pfg-revert-thumb" class="pfg-btn pfg-btn-secondary" style="margin-top: 8px; margin-left: 8px; display: none;">
                            <span class="dashicons dashicons-undo" style="margin-right: 5px;"></span>
                            <?php esc_html_e( 'Revert to Original', 'portfolio-filter-gallery' ); ?>
                        </button>
                        <span id="pfg-fetch-thumb-status" style="display: none; margin-left: 10px; font-size: 12px;"></span>
                    </div>
                </div>
                
                <div class="pfg-form-row">
                    <label class="pfg-form-label"><?php esc_html_e( 'Filters/Categories', 'portfolio-filter-gallery' ); ?></label>
                    <div class="pfg-filter-checkboxes" id="pfg-modal-filters">
                        <?php echo pfg_render_filter_checkboxes( $filter_tree ); ?>
                    </div>
                </div>
                
                <?php if ( pfg_is_premium() && class_exists( 'WooCommerce' ) ) : ?>
                <div class="pfg-form-row">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Link to Product', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'Connect to WooCommerce product', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <div class="pfg-product-search-wrap">
                        <input type="text" id="pfg-modal-product-search" class="pfg-input" placeholder="<?php esc_attr_e( 'Search products...', 'portfolio-filter-gallery' ); ?>" autocomplete="off">
                        <input type="hidden" id="pfg-modal-product-id" value="">
                        <div id="pfg-product-results" class="pfg-product-results"></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="pfg-modal-footer">
            <button type="button" class="pfg-btn pfg-btn-secondary pfg-modal-cancel">
                <?php esc_html_e( 'Cancel', 'portfolio-filter-gallery' ); ?>
            </button>
            <button type="button" class="pfg-btn pfg-btn-primary pfg-modal-save">
                <?php esc_html_e( 'Save Changes', 'portfolio-filter-gallery' ); ?>
            </button>
        </div>
    </div>
</div>

<style>
/* Progress Modal Styles for Chunked Save */
#pfg-save-progress-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    z-index: 200000;
    display: flex;
    align-items: center;
    justify-content: center;
}
#pfg-save-progress-modal .pfg-progress-content {
    background: #fff;
    border-radius: 16px;
    padding: 40px 50px;
    text-align: center;
    min-width: 350px;
    max-width: 450px;
    box-shadow: 0 25px 80px rgba(0,0,0,0.4);
}
#pfg-save-progress-modal .pfg-progress-icon {
    margin-bottom: 20px;
}
#pfg-save-progress-modal .pfg-progress-icon .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    color: #3858e9;
    animation: pfg-spin 1s linear infinite;
}
#pfg-save-progress-modal .pfg-progress-icon .dashicons-warning {
    color: #ef4444;
    animation: none;
}
@keyframes pfg-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
#pfg-save-progress-modal h3 {
    margin: 0 0 25px 0;
    font-size: 20px;
    font-weight: 600;
    color: #1e293b;
}
#pfg-save-progress-modal .pfg-progress-bar {
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 15px;
}
#pfg-save-progress-modal .pfg-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3858e9 0%, #667eea 100%);
    border-radius: 4px;
    width: 0%;
    transition: width 0.3s ease;
}
#pfg-save-progress-modal .pfg-progress-text {
    margin: 0;
    color: #64748b;
    font-size: 14px;
}

/* Modal Styles */
.pfg-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.pfg-modal-content {
    background: #fff;
    border-radius: 12px;
    width: 90%;
    max-width: 700px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
.pfg-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}
.pfg-modal-header h3 { margin: 0; font-size: 18px; color: #1e293b; }
.pfg-modal-close {
    background: none;
    border: none;
    font-size: 28px;
    color: #64748b;
    cursor: pointer;
    line-height: 1;
}
.pfg-modal-close:hover { color: #1e293b; }
.pfg-modal-nav {
    background: #e2e8f0;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #475569;
    transition: all 0.2s;
    flex-shrink: 0;
}
.pfg-modal-nav:hover { background: #3858e9; color: #fff; }
.pfg-modal-nav:disabled { opacity: 0.4; cursor: not-allowed; }
.pfg-modal-nav:disabled:hover { background: #e2e8f0; color: #475569; }
.pfg-modal-nav .dashicons { font-size: 20px; width: 20px; height: 20px; }
.pfg-modal-counter { font-size: 13px; font-weight: normal; color: #64748b; }
.pfg-modal-header h3 { margin: 0; font-size: 18px; color: #1e293b; flex-grow: 1; text-align: center; }
.pfg-modal-body {
    padding: 25px;
    overflow-y: auto;
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 25px;
}
.pfg-modal-preview img {
    width: 100%;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}
.pfg-modal-fields { display: flex; flex-direction: column; gap: 15px; }
#pfg-image-modal .pfg-form-row { display: block; margin-bottom: 0; padding: 0; border: none; }
#pfg-image-modal .pfg-form-label { display: block; font-weight: 500; margin-bottom: 6px; color: #475569; font-size: 13px; padding: 0; }
.pfg-input, .pfg-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 14px;
}
.pfg-input:focus, .pfg-textarea:focus {
    border-color: #3858e9;
    outline: none;
    box-shadow: 0 0 0 3px rgba(56, 88, 233, 0.1);
}
.pfg-filter-checkboxes {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 10px;
    background: #f8fafc;
    border-radius: 6px;
    max-height: 150px;
    overflow-y: auto;
}
.pfg-checkbox-label {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
}
.pfg-checkbox-label:hover { border-color: #3858e9; }
.pfg-checkbox-label input:checked + span,
.pfg-checkbox-label:has(input:checked) {
    background: #eff6ff;
    border-color: #3858e9;
    color: #1e40af;
}
.pfg-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 20px 25px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}
.pfg-btn { padding: 10px 20px; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; border: none; transition: all 0.2s; }
.pfg-btn-primary { background: #3858e9; color: #fff; }
.pfg-btn-primary:hover { background: #2d4ad4; }
.pfg-btn-secondary { background: #e2e8f0; color: #475569; }
.pfg-btn-secondary:hover { background: #cbd5e1; }

/* Image Grid Styles */
.pfg-image-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}
.pfg-image-thumb { width: 100%; height: 150px; object-fit: cover; display: block; }
.pfg-image-actions {
    position: absolute;
    top: 8px;
    right: 8px;
    display: flex;
    gap: 5px;
    opacity: 0;
    transition: opacity 0.2s;
}
.pfg-image-item:hover .pfg-image-actions { opacity: 1; }
.pfg-image-action {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.95);
    border: none;
    border-radius: 6px;
    cursor: pointer;
    color: #475569;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.pfg-image-action:hover { background: #fff; color: #3858e9; }
.pfg-image-delete:hover { color: #ef4444; }
.pfg-image-info { padding: 10px; }
.pfg-image-title { margin: 0; font-size: 13px; font-weight: 500; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pfg-image-filters { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 6px; }
.pfg-image-filter-tag { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; background: #eff6ff; color: #3858e9; border-radius: 10px; font-size: 11px; }
.pfg-tag-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.pfg-tag-connector { color: #94a3b8; font-size: 10px; margin-right: -2px; }

/* Type indicator badges */
/* Type indicator badges */
.pfg-image-type-badge {
    position: absolute;
    top: 118px; /* Position inside the 150px image area, 8px from bottom */
    left: 8px;
    z-index: 5;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #3858e9 0%, #667eea 100%);
    border-radius: 50%;
    box-shadow: 0 2px 6px rgba(56, 88, 233, 0.4);
}
.pfg-image-type-badge .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
    color: #fff;
}
/* YouTube badge - red */
.pfg-image-type-badge.pfg-badge-youtube {
    background: linear-gradient(135deg, #ff0000 0%, #cc0000 100%);
    box-shadow: 0 2px 6px rgba(255, 0, 0, 0.4);
}
/* Vimeo badge - cyan blue */
.pfg-image-type-badge.pfg-badge-vimeo {
    background: linear-gradient(135deg, #1ab7ea 0%, #0095d5 100%);
    box-shadow: 0 2px 6px rgba(26, 183, 234, 0.4);
}
/* Generic video badge - orange/red */
.pfg-image-type-badge.pfg-badge-video {
    background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
    box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);
}

/* Selected state for bulk selection */
.pfg-image-item.selected {
    border-color: #3858e9;
    box-shadow: 0 0 0 3px rgba(56, 88, 233, 0.2);
}
.pfg-image-item.selected .pfg-image-checkbox {
    opacity: 1;
}
.pfg-image-checkbox {
    opacity: 0;
    transition: opacity 0.2s;
}
.pfg-image-item:hover .pfg-image-checkbox {
    opacity: 1;
}

@media (max-width: 600px) {
    .pfg-modal-body { grid-template-columns: 1fr; }
    .pfg-modal-preview { max-width: 200px; margin: 0 auto; }
}

/* Product Search Results */
.pfg-product-search-wrap {
    position: relative;
}
.pfg-product-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    max-height: 250px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}
.pfg-product-results:not(:empty) {
    display: block;
}
.pfg-product-result {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    cursor: pointer;
    transition: background 0.15s;
}
.pfg-product-result:hover {
    background: #f1f5f9;
}
.pfg-product-result-img {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
    flex-shrink: 0;
}
.pfg-product-result-name {
    flex: 1;
    font-size: 13px;
    color: #1e293b;
}
.pfg-product-result-price {
    font-size: 12px;
    color: #64748b;
    white-space: nowrap;
}
.pfg-searching, .pfg-no-results, .pfg-error {
    padding: 15px;
    text-align: center;
    color: #64748b;
    font-size: 13px;
}
.pfg-error {
    color: #ef4444;
}
</style>

<script>
jQuery(document).ready(function($) {
    var currentImageItem = null;
    var currentImageIndex = 0;
    var galleryId = <?php echo (int) $gallery_id; ?>;
    var originalImageData = null; // Store original image for revert functionality
    
    // ========================================
    // PAGINATION CONFIGURATION
    // ========================================
    var PAGINATION_THRESHOLD = 50; // Show pagination when images exceed this
    var IMAGES_PER_PAGE = 50;
    var paginationCurrentPage = 1;
    var paginationTotalPages = 1;
    var paginationTotalImages = 0;
    var paginationLoading = false;
    
    // ========================================
    // MASTER IMAGES ARRAY
    // ========================================
    // This holds ALL images for the gallery, regardless of pagination
    // The DOM only shows the current page, but this array is the source of truth
    var masterImagesArray = [];
    
    // Initialize masterImagesArray from JSON textarea or DOM
    var initialJsonData = $('#pfg-images-json').val();
    if (initialJsonData && initialJsonData !== '' && initialJsonData !== '[]') {
        try {
            masterImagesArray = JSON.parse(initialJsonData);
            console.log('PFG Free: Initialized masterImagesArray from JSON with ' + masterImagesArray.length + ' images');
        } catch(e) {
            console.error('PFG Free: Failed to parse initial JSON:', e);
            masterImagesArray = [];
        }
    }
    
    // If no JSON data, populate from DOM (backward compatibility)
    if (masterImagesArray.length === 0) {
        $('.pfg-image-item:not(.pfg-product-preview-item)').each(function() {
            var $item = $(this);
            var imageData = {
                id: parseInt($item.data('id'), 10) || parseInt($item.find('input[name$="[id]"]').val(), 10),
                title: $item.find('input[name$="[title]"]').val() || '',
                alt: $item.find('input[name$="[alt]"]').val() || '',
                description: $item.find('input[name$="[description]"]').val() || '',
                link: $item.find('input[name$="[link]"]').val() || '',
                type: $item.find('input[name$="[type]"]').val() || 'image',
                filters: $item.find('input[name$="[filters]"]').val() || '',
                product_id: $item.find('input[name$="[product_id]"]').val() || '',
                product_name: $item.find('input[name$="[product_name]"]').val() || '',
                original_id: $item.find('input[name$="[original_id]"]').val() || ''
            };
            if (imageData.id) {
                masterImagesArray.push(imageData);
            }
        });
        console.log('PFG Free: Initialized masterImagesArray from DOM with ' + masterImagesArray.length + ' images');
    }
    
    // Update pagination info
    paginationTotalImages = masterImagesArray.length;
    paginationTotalPages = Math.max(1, Math.ceil(paginationTotalImages / IMAGES_PER_PAGE));
    
    // Expose masterImagesArray globally for sort handler
    window.pfgGetMasterImages = function() { return masterImagesArray; };
    
    // Sync current page DOM changes to masterImagesArray
    function syncCurrentPageToMaster() {
        $('.pfg-image-item:not(.pfg-product-preview-item)').each(function() {
            var $item = $(this);
            var imageId = parseInt($item.data('id'), 10);
            
            // Find this image in master array
            for (var i = 0; i < masterImagesArray.length; i++) {
                if (parseInt(masterImagesArray[i].id, 10) === imageId) {
                    // Update from hidden inputs
                    masterImagesArray[i].title = $item.find('input[name$="[title]"]').val() || masterImagesArray[i].title;
                    masterImagesArray[i].alt = $item.find('input[name$="[alt]"]').val() || masterImagesArray[i].alt;
                    masterImagesArray[i].description = $item.find('input[name$="[description]"]').val() || masterImagesArray[i].description;
                    masterImagesArray[i].link = $item.find('input[name$="[link]"]').val() || masterImagesArray[i].link;
                    masterImagesArray[i].type = $item.find('input[name$="[type]"]').val() || masterImagesArray[i].type;
                    masterImagesArray[i].filters = $item.find('input[name$="[filters]"]').val() || masterImagesArray[i].filters;
                    break;
                }
            }
        });
    }
    
    // Reorder master array based on new order IDs
    function reorderMasterArray(newOrderIds) {
        if (!Array.isArray(newOrderIds) || newOrderIds.length === 0) return;
        
        // Normalize IDs to integers
        var normalizedNewOrderIds = newOrderIds.map(function(id) {
            return parseInt(id, 10);
        });
        
        // Create lookup map
        var idMap = {};
        masterImagesArray.forEach(function(img) {
            idMap[parseInt(img.id, 10)] = img;
        });
        
        var newMasterArray = [];
        normalizedNewOrderIds.forEach(function(id) {
            if (idMap[id]) {
                newMasterArray.push(idMap[id]);
                delete idMap[id];
            }
        });
        
        // Add remaining images (from other pages)
        Object.keys(idMap).forEach(function(key) {
            newMasterArray.push(idMap[key]);
        });
        
        masterImagesArray = newMasterArray;
        console.log('PFG Free: Master array reordered');
    }
    
    // Remove image from master array
    function removeImageFromMaster(imageId) {
        var normalizedId = parseInt(imageId, 10);
        masterImagesArray = masterImagesArray.filter(function(img) {
            return parseInt(img.id, 10) !== normalizedId;
        });
        paginationTotalImages = masterImagesArray.length;
        paginationTotalPages = Math.max(1, Math.ceil(paginationTotalImages / IMAGES_PER_PAGE));
        console.log('PFG Free: Removed image ' + imageId + ' from master array');
    }
    
    // Expose functions globally
    window.pfgReorderMasterArray = reorderMasterArray;
    window.pfgRemoveImageFromMaster = removeImageFromMaster;
    window.pfgSyncCurrentPageToMaster = syncCurrentPageToMaster;
    // Note: pfgUpdatePaginationUI and pfgMarkImagesModified are exposed after their definitions below
    
    // ========================================
    // PAGINATION FUNCTIONS
    // ========================================
    
    // Update pagination UI based on current state
    function updatePaginationUI() {
        var $controls = $('#pfg-pagination-controls');
        
        // Show/hide based on threshold
        if (masterImagesArray.length > PAGINATION_THRESHOLD) {
            $controls.show();
            
            // Update counts
            var start = ((paginationCurrentPage - 1) * IMAGES_PER_PAGE) + 1;
            var end = Math.min(paginationCurrentPage * IMAGES_PER_PAGE, paginationTotalImages);
            
            $('#pfg-page-start').text(start);
            $('#pfg-page-end').text(end);
            $('#pfg-total-images').text(paginationTotalImages);
            
            // Update button states
            $('#pfg-page-prev').prop('disabled', paginationCurrentPage <= 1);
            $('#pfg-page-next').prop('disabled', paginationCurrentPage >= paginationTotalPages);
            
            // Render page numbers
            var $pageNumbers = $('#pfg-page-numbers');
            $pageNumbers.empty();
            
            // Show up to 5 page numbers centered around current page
            var startPage = Math.max(1, paginationCurrentPage - 2);
            var endPage = Math.min(paginationTotalPages, startPage + 4);
            if (endPage - startPage < 4) {
                startPage = Math.max(1, endPage - 4);
            }
            
            for (var i = startPage; i <= endPage; i++) {
                var $btn = $('<button type="button" class="pfg-page-num" style="min-width: 32px; padding: 6px 10px; border: 1px solid #e2e8f0; background: ' + (i === paginationCurrentPage ? '#3858e9' : '#fff') + '; color: ' + (i === paginationCurrentPage ? '#fff' : '#475569') + '; border-radius: 4px; cursor: pointer; font-weight: 500;">' + i + '</button>');
                $btn.data('page', i);
                $pageNumbers.append($btn);
            }
        } else {
            $controls.hide();
        }
    }
    
    // Render images for current page from masterImagesArray
    function renderCurrentPage() {
        if (paginationLoading) return;
        
        // Sync current DOM changes before switching
        syncCurrentPageToMaster();
        
        var $grid = $('#pfg-image-grid');
        var $loading = $('.pfg-pagination-loading');
        
        paginationLoading = true;
        $loading.show();
        
        // Calculate slice
        var start = (paginationCurrentPage - 1) * IMAGES_PER_PAGE;
        var end = start + IMAGES_PER_PAGE;
        var pageImages = masterImagesArray.slice(start, end);
        
        // Build HTML for this page
        var html = '';
        if (pageImages.length === 0) {
            html = '<div class="pfg-no-images"><span class="dashicons dashicons-format-gallery"></span><p><?php esc_html_e( 'No images yet. Add some to get started!', 'portfolio-filter-gallery' ); ?></p></div>';
        } else {
            pageImages.forEach(function(image, idx) {
                var actualIndex = start + idx;
                var filters = image.filters || '';
                var imageType = image.type || 'image';
                
                html += '<div class="pfg-image-item" data-id="' + image.id + '" data-index="' + actualIndex + '">';
                html += '<label class="pfg-image-checkbox" style="position: absolute; top: 8px; left: 8px; z-index: 10;">';
                html += '<input type="checkbox" class="pfg-image-select" style="width: 18px; height: 18px; cursor: pointer;">';
                html += '</label>';
                
                // Type badge for video/url
                if (imageType === 'video' || imageType === 'url') {
                    var badgeClass = 'pfg-image-type-badge';
                    var badgeIcon = 'dashicons-external';
                    if (imageType === 'video') {
                        if (image.link && image.link.indexOf('youtube') !== -1) {
                            badgeClass += ' pfg-badge-youtube';
                            badgeIcon = 'dashicons-youtube';
                        } else if (image.link && image.link.indexOf('vimeo') !== -1) {
                            badgeClass += ' pfg-badge-vimeo';
                            badgeIcon = 'dashicons-video-alt3';
                        } else {
                            badgeClass += ' pfg-badge-video';
                            badgeIcon = 'dashicons-video-alt3';
                        }
                    }
                    html += '<div class="' + badgeClass + '"><span class="dashicons ' + badgeIcon + '"></span></div>';
                }
                
                // Use AJAX to get thumbnail URL later, for now use placeholder
                html += '<img src="" alt="" class="pfg-image-thumb" data-image-id="' + image.id + '" loading="lazy">';
                
                html += '<div class="pfg-image-actions">';
                html += '<button type="button" class="pfg-image-action pfg-image-edit" title="Edit"><span class="dashicons dashicons-edit"></span></button>';
                html += '<button type="button" class="pfg-image-action pfg-image-delete" title="Delete"><span class="dashicons dashicons-trash"></span></button>';
                html += '</div>';
                
                html += '<div class="pfg-image-info">';
                html += '<p class="pfg-image-title">' + (image.title || 'Untitled') + '</p>';
                html += '</div>';
                
                // Hidden inputs
                html += '<input type="hidden" name="pfg_images[' + actualIndex + '][id]" value="' + image.id + '">';
                html += '<input type="hidden" name="pfg_images[' + actualIndex + '][title]" value="' + (image.title || '') + '">';
                html += '<input type="hidden" name="pfg_images[' + actualIndex + '][alt]" value="' + (image.alt || '') + '">';
                html += '<input type="hidden" name="pfg_images[' + actualIndex + '][description]" value="' + (image.description || '') + '">';
                html += '<input type="hidden" name="pfg_images[' + actualIndex + '][link]" value="' + (image.link || '') + '">';
                html += '<input type="hidden" name="pfg_images[' + actualIndex + '][type]" value="' + (image.type || 'image') + '">';
                html += '<input type="hidden" name="pfg_images[' + actualIndex + '][filters]" value="' + filters + '">';
                html += '<input type="hidden" name="pfg_images[' + actualIndex + '][product_id]" value="' + (image.product_id || '') + '">';
                html += '<input type="hidden" name="pfg_images[' + actualIndex + '][product_name]" value="' + (image.product_name || '') + '">';
                html += '<input type="hidden" name="pfg_images[' + actualIndex + '][original_id]" value="' + (image.original_id || image.id) + '">';
                
                html += '</div>';
            });
        }
        
        $grid.html(html);
        
        // Load thumbnails via AJAX
        loadThumbnails(pageImages);
        
        // Re-initialize Sortable
        if (window.PFGAdmin && typeof window.PFGAdmin.initSortable === 'function') {
            window.PFGAdmin.initSortable();
        }
        
        paginationLoading = false;
        $loading.hide();
        
        updatePaginationUI();
    }
    
    // Load thumbnails for displayed images
    function loadThumbnails(images) {
        if (!images || images.length === 0) return;
        
        var ids = images.map(function(img) { return img.id; });
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pfg_get_thumbnails',
                nonce: '<?php echo wp_create_nonce( 'pfg_admin_nonce' ); ?>',
                image_ids: ids
            },
            success: function(response) {
                if (response.success && response.data.thumbnails) {
                    $.each(response.data.thumbnails, function(id, url) {
                        $('img.pfg-image-thumb[data-image-id="' + id + '"]').attr('src', url);
                    });
                }
            }
        });
    }
    
    // Handle page navigation
    function goToPage(page) {
        if (page < 1 || page > paginationTotalPages || page === paginationCurrentPage) return;
        
        paginationCurrentPage = page;
        renderCurrentPage();
    }
    
    // Pagination event handlers
    $('#pfg-page-prev').on('click', function() {
        goToPage(paginationCurrentPage - 1);
    });
    
    $('#pfg-page-next').on('click', function() {
        goToPage(paginationCurrentPage + 1);
    });
    
    $(document).on('click', '.pfg-page-num', function() {
        goToPage($(this).data('page'));
    });
    
    // Initialize pagination on load
    paginationTotalImages = masterImagesArray.length;
    paginationTotalPages = Math.max(1, Math.ceil(paginationTotalImages / IMAGES_PER_PAGE));
    updatePaginationUI();
    
    // ========================================
    // CHUNKED SAVE CONFIGURATION
    // ========================================
    var CHUNK_SIZE = 50;           // Images per chunk
    var CHUNK_THRESHOLD = 100;     // Use chunked save above this count
    var structurallyModified = false; // Add/delete/reorder - requires full save
    var metadataModified = false;     // Title/description/filters - can use standard save
    var chunkedSaveInProgress = false;
    var chunkedSaveCompleted = false;
    
    // Mark images as structurally modified (add/delete/reorder)
    // This requires full chunked save for large galleries
    function markStructurallyModified() {
        structurallyModified = true;
    }
    
    // Mark metadata as modified (title/description/filters)
    // This can use standard save even for large galleries
    function markMetadataModified() {
        metadataModified = true;
    }
    
    // Legacy function for compatibility with pfg-admin.js
    function markImagesModified() {
        markStructurallyModified();
    }
    
    // Expose to global scope for pfg-admin.js integration
    window.pfgMarkImagesModified = markStructurallyModified;
    window.pfgUpdatePaginationUI = updatePaginationUI;
    
    // Get all image data as array - uses masterImagesArray for pagination support
    function getAllImagesData() {
        // First sync any DOM changes to master array
        syncCurrentPageToMaster();
        
        // Return the master array (contains ALL images, not just current page)
        return masterImagesArray.map(function(img) {
            return {
                id: img.id,
                title: img.title || '',
                alt: img.alt || '',
                description: img.description || '',
                link: img.link || '',
                type: img.type || 'image',
                filters: img.filters || '',
                product_id: img.product_id || '',
                product_name: img.product_name || '',
                original_id: img.original_id || ''
            };
        });
    }
    
    // Split array into chunks
    function chunkArray(array, size) {
        var chunks = [];
        for (var i = 0; i < array.length; i += size) {
            chunks.push(array.slice(i, i + size));
        }
        return chunks;
    }
    
    // Show progress modal
    function showProgressModal() {
        if ($('#pfg-save-progress-modal').length === 0) {
            $('body').append(
                '<div id="pfg-save-progress-modal">' +
                    '<div class="pfg-progress-content">' +
                        '<div class="pfg-progress-icon"><span class="dashicons dashicons-update-alt"></span></div>' +
                        '<h3><?php esc_html_e( 'Saving Gallery...', 'portfolio-filter-gallery' ); ?></h3>' +
                        '<div class="pfg-progress-bar"><div class="pfg-progress-fill"></div></div>' +
                        '<p class="pfg-progress-text"><?php esc_html_e( 'Preparing...', 'portfolio-filter-gallery' ); ?></p>' +
                    '</div>' +
                '</div>'
            );
        }
        $('#pfg-save-progress-modal').fadeIn(200);
    }
    
    // Update progress
    function updateProgress(current, total, message) {
        var percent = Math.round((current / total) * 100);
        $('#pfg-save-progress-modal .pfg-progress-fill').css('width', percent + '%');
        $('#pfg-save-progress-modal .pfg-progress-text').text(message || ('<?php esc_html_e( 'Saving images:', 'portfolio-filter-gallery' ); ?> ' + current + '/' + total));
    }
    
    // Hide progress modal
    function hideProgressModal() {
        $('#pfg-save-progress-modal').fadeOut(200);
    }
    
    // Show error in progress modal
    function showProgressError(message) {
        $('#pfg-save-progress-modal .pfg-progress-icon .dashicons')
            .removeClass('dashicons-update-alt')
            .addClass('dashicons-warning');
        $('#pfg-save-progress-modal .pfg-progress-text').text(message);
        $('#pfg-save-progress-modal .pfg-progress-content').append(
            '<button type="button" class="button pfg-progress-close" style="margin-top: 15px;"><?php esc_html_e( 'Close', 'portfolio-filter-gallery' ); ?></button>'
        );
    }
    
    // Close error modal
    $(document).on('click', '.pfg-progress-close', function() {
        hideProgressModal();
        chunkedSaveInProgress = false;
    });
    
    // Save images in chunks
    async function saveImagesChunked(imagesData) {
        var chunks = chunkArray(imagesData, CHUNK_SIZE);
        var totalChunks = chunks.length;
        
        for (var i = 0; i < chunks.length; i++) {
            updateProgress(i * CHUNK_SIZE, imagesData.length, 
                '<?php esc_html_e( 'Saving images:', 'portfolio-filter-gallery' ); ?> ' + 
                Math.min((i + 1) * CHUNK_SIZE, imagesData.length) + '/' + imagesData.length);
            
            try {
                await saveChunk(chunks[i], i, totalChunks);
            } catch (error) {
                throw error;
            }
        }
        
        return true;
    }
    
    // Save single chunk
    function saveChunk(chunk, chunkIndex, totalChunks) {
        return new Promise(function(resolve, reject) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pfg_save_images_chunk',
                    nonce: '<?php echo wp_create_nonce( 'pfg_admin_nonce' ); ?>',
                    gallery_id: galleryId,
                    chunk_index: chunkIndex,
                    total_chunks: totalChunks,
                    images: JSON.stringify(chunk)
                },
                success: function(response) {
                    if (response.success) {
                        resolve(response.data);
                    } else {
                        var errorMsg = response.data ? response.data.message : '<?php esc_html_e( 'Save failed', 'portfolio-filter-gallery' ); ?>';
                        console.error('PFG Chunk Save Error:', response);
                        reject(errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('PFG Chunk Save Network Error:', status, error, xhr.responseText);
                    var errorMsg = '<?php esc_html_e( 'Network error.', 'portfolio-filter-gallery' ); ?>';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.data && response.data.message) {
                            errorMsg = response.data.message;
                        }
                    } catch(e) {}
                    reject(errorMsg);
                }
            });
        });
    }
    
    // Form submit handler with chunked save support
    $('form#post').on('submit', function(e) {
        var imagesData = getAllImagesData();
        var imageCount = imagesData.length;
        
        // If chunked save already completed, just mark and let form submit
        if (chunkedSaveCompleted) {
            $('#pfg-images-json').val('__CHUNKED_SAVE__');
            return true;
        }
        
        // Check if any modifications were made
        var anyModification = structurallyModified || metadataModified;
        
        // CRITICAL FIX: For large galleries (over threshold), we MUST remove hidden image inputs
        // BEFORE form submit to avoid exceeding PHP's max_input_vars limit (typically 1000).
        // With 440 images × 8 inputs = 3500+ inputs, which truncates settings data!
        if (imageCount > CHUNK_THRESHOLD) {
            // Remove all hidden pfg_images inputs to stay under max_input_vars
            $('input[name^="pfg_images["]').remove();
            console.log('PFG: Removed hidden inputs for large gallery (' + imageCount + ' images)');
        }
        
        // If nothing was modified, skip image saving entirely
        if (!anyModification) {
            $('#pfg-images-json').val('__UNCHANGED__');
            return true;
        }
        
        // For smaller galleries or metadata-only changes, use standard JSON save
        // Only use chunked save for STRUCTURAL changes on LARGE galleries
        if (imageCount <= CHUNK_THRESHOLD || !structurallyModified) {
            // Standard JSON save - fast for metadata changes
            $('#pfg-images-json').val(JSON.stringify(imagesData));
            return true;
        }
        
        // Large gallery with structural changes - use chunked save
        
        // Prevent form submit - we'll submit after chunked save
        if (!chunkedSaveInProgress) {
            e.preventDefault();
            chunkedSaveInProgress = true;
            
            showProgressModal();
            updateProgress(0, imageCount, '<?php esc_html_e( 'Starting save...', 'portfolio-filter-gallery' ); ?>');
            
            saveImagesChunked(imagesData)
                .then(function() {
                    updateProgress(imageCount, imageCount, '<?php esc_html_e( 'Complete! Saving gallery...', 'portfolio-filter-gallery' ); ?>');
                    chunkedSaveCompleted = true;
                    
                    // CRITICAL FIX: Remove all hidden pfg_images inputs from DOM to avoid
                    // exceeding PHP's max_input_vars limit (which would truncate settings data)
                    $('input[name^="pfg_images["]').remove();
                    
                    // Now submit the form normally
                    setTimeout(function() {
                        $('form#post').submit();
                    }, 500);
                })
                .catch(function(error) {
                    showProgressError(error);
                    chunkedSaveInProgress = false;
                });
            
            return false;
        }
        
        return true;
    });
    
    // Get all image items
    function getAllImageItems() {
        return $('.pfg-image-item:not(.pfg-product-preview-item)');
    }
    
    // Update navigation buttons visibility and counter
    function updateNavigation() {
        var allItems = getAllImageItems();
        var total = allItems.length;
        
        if (total <= 1) {
            $('.pfg-modal-prev, .pfg-modal-next').hide();
            $('.pfg-modal-counter').text('');
        } else {
            $('.pfg-modal-prev').show().prop('disabled', currentImageIndex <= 0);
            $('.pfg-modal-next').show().prop('disabled', currentImageIndex >= total - 1);
            $('.pfg-modal-counter').text('(' + (currentImageIndex + 1) + ' / ' + total + ')');
        }
    }
    
    // Open modal for a specific image item
    function openModalForItem(imageItem) {
        currentImageItem = imageItem;
        var allItems = getAllImageItems();
        currentImageIndex = allItems.index(imageItem);
        
        // Get image URL from thumbnail
        var imgSrc = currentImageItem.find('.pfg-image-thumb').attr('src');
        var imageId = currentImageItem.find('input[name$="[id]"]').val() || '';
        
        // Store original image data for revert functionality
        originalImageData = {
            id: imageId,
            src: imgSrc
        };
        
        // Get values from hidden inputs
        var title = currentImageItem.find('input[name$="[title]"]').val() || '';
        var alt = currentImageItem.find('input[name$="[alt]"]').val() || '';
        var description = currentImageItem.find('input[name$="[description]"]').val() || '';
        var link = currentImageItem.find('input[name$="[link]"]').val() || '';
        var type = currentImageItem.find('input[name$="[type]"]').val() || 'image';
        var filtersStr = currentImageItem.find('input[name$="[filters]"]').val() || '';
        var filters = filtersStr ? filtersStr.split(',') : [];
        var productId = currentImageItem.find('input[name$="[product_id]"]').val() || '';
        var productName = currentImageItem.find('input[name$="[product_name]"]').val() || '';
        
        // Populate modal
        $('#pfg-modal-image').attr('src', imgSrc);
        $('#pfg-modal-title').val(title);
        $('#pfg-modal-alt').val(alt);
        $('#pfg-modal-description').val(description);
        $('#pfg-modal-type').val(type).trigger('change');
        $('#pfg-modal-link').val(link);
        
        // Populate product search (if exists)
        $('#pfg-modal-product-id').val(productId);
        $('#pfg-modal-product-search').val(productName).css('border-color', productId ? '#10b981' : '');
        $('#pfg-product-results').hide().html('');
        
        // Check if we should show revert button (original_id differs from current id)
        var originalId = currentImageItem.find('input[name$="[original_id]"]').val() || imageId;
        if (originalId && originalId !== imageId) {
            // Get original image URL
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pfg_get_attachment_url',
                    attachment_id: originalId,
                    nonce: '<?php echo wp_create_nonce( 'pfg_admin_nonce' ); ?>'
                },
                success: function(response) {
                    if (response.success && response.data.url) {
                        originalImageData = {
                            id: originalId,
                            src: response.data.url
                        };
                        $('#pfg-revert-thumb').show();
                        $('#pfg-fetch-thumb-status').text('<?php esc_html_e( 'Video thumbnail in use', 'portfolio-filter-gallery' ); ?>').css('color', '#64748b').show();
                    }
                }
            });
        } else {
            // Store current as original
            originalImageData = {
                id: imageId,
                src: imgSrc
            };
            $('#pfg-revert-thumb').hide();
            $('#pfg-fetch-thumb-status').hide().text('');
        }
        
        // Reset and check filter checkboxes
        $('#pfg-modal-filters input[type="checkbox"]').prop('checked', false);
        filters.forEach(function(filterId) {
            $('#pfg-modal-filters input[value="' + filterId.trim() + '"]').prop('checked', true);
        });
        
        // Update navigation
        updateNavigation();
        
        // Show modal
        $('#pfg-image-modal').fadeIn(200);
    }
    
    // Navigate to previous/next image
    function navigateToImage(direction) {
        // Save current changes first
        saveCurrentChanges();
        
        var allItems = getAllImageItems();
        var newIndex = currentImageIndex + direction;
        
        if (newIndex >= 0 && newIndex < allItems.length) {
            var newItem = allItems.eq(newIndex);
            openModalForItem(newItem);
        }
    }
    
    // Save changes to current image item
    function saveCurrentChanges() {
        if (!currentImageItem) return;
        
        var title = $('#pfg-modal-title').val();
        var alt = $('#pfg-modal-alt').val();
        var description = $('#pfg-modal-description').val();
        var type = $('#pfg-modal-type').val();
        var link = $('#pfg-modal-link').val();
        
        // Get selected filters
        var filters = [];
        $('#pfg-modal-filters input[type="checkbox"]:checked').each(function() {
            filters.push($(this).val());
        });
        
        // Update hidden inputs
        currentImageItem.find('input[name$="[title]"]').val(title);
        currentImageItem.find('input[name$="[alt]"]').val(alt);
        currentImageItem.find('input[name$="[description]"]').val(description);
        currentImageItem.find('input[name$="[type]"]').val(type);
        currentImageItem.find('input[name$="[link]"]').val(link);
        currentImageItem.find('input[name$="[filters]"]').val(filters.join(','));
        
        // Mark metadata as modified for smart save
        markMetadataModified();
        
        // Update product ID and name (for WooCommerce linking)
        var productId = $('#pfg-modal-product-id').val() || '';
        var productName = $('#pfg-modal-product-search').val() || '';
        console.log('Saving product:', productId, productName); // Debug log
        currentImageItem.find('input[name$="[product_id]"]').val(productId);
        currentImageItem.find('input[name$="[product_name]"]').val(productName);
        
        // Update visible title
        currentImageItem.find('.pfg-image-title').text(title || 'Untitled');
        
        // Update filter tags display with color dots and hierarchy connector
        var filterTagsHtml = '';
        filters.forEach(function(filterId) {
            var checkbox = $('#pfg-modal-filters input[value="' + filterId + '"]');
            var $label = checkbox.closest('label');
            var filterName = $label.find('.pfg-tree-filter-name').text().trim() || $label.text().trim();
            var filterColor = $label.data('color') || '#94a3b8';
            var isChild = $label.data('parent') ? true : false;
            var connectorHtml = isChild ? '<span class="pfg-tag-connector">└</span>' : '';
            filterTagsHtml += '<span class="pfg-image-filter-tag">' + connectorHtml + '<span class="pfg-tag-dot" style="background-color: ' + filterColor + ';"></span>' + filterName + '</span>';
        });
        
        var filtersContainer = currentImageItem.find('.pfg-image-filters');
        if (filtersContainer.length) {
            filtersContainer.html(filterTagsHtml);
        } else if (filterTagsHtml) {
            currentImageItem.find('.pfg-image-info').append('<div class="pfg-image-filters">' + filterTagsHtml + '</div>');
        }
        
        // Update type badge (video/url indicator)
        var existingBadge = currentImageItem.find('.pfg-image-type-badge');
        if (type === 'video' || type === 'url') {
            var badgeClass = 'pfg-image-type-badge';
            var iconClass, titleText;
            
            if (type === 'video' && link) {
                // Detect YouTube or Vimeo
                if (link.indexOf('youtube.com') !== -1 || link.indexOf('youtu.be') !== -1) {
                    badgeClass += ' pfg-badge-youtube';
                    iconClass = 'dashicons-youtube';
                    titleText = '<?php esc_attr_e( 'YouTube Video', 'portfolio-filter-gallery' ); ?>';
                } else if (link.indexOf('vimeo.com') !== -1) {
                    badgeClass += ' pfg-badge-vimeo';
                    iconClass = 'dashicons-video-alt3';
                    titleText = '<?php esc_attr_e( 'Vimeo Video', 'portfolio-filter-gallery' ); ?>';
                } else {
                    badgeClass += ' pfg-badge-video';
                    iconClass = 'dashicons-video-alt3';
                    titleText = '<?php esc_attr_e( 'Video Lightbox', 'portfolio-filter-gallery' ); ?>';
                }
            } else {
                iconClass = 'dashicons-external';
                titleText = '<?php esc_attr_e( 'External Link', 'portfolio-filter-gallery' ); ?>';
            }
            
            var badgeHtml = '<div class="' + badgeClass + '" title="' + titleText + '"><span class="dashicons ' + iconClass + '"></span></div>';
            
            if (existingBadge.length) {
                existingBadge.replaceWith(badgeHtml);
            } else {
                currentImageItem.find('.pfg-image-checkbox').after(badgeHtml);
            }
        } else {
            // Remove badge for regular images
            existingBadge.remove();
        }
    }
    
    // Open modal on edit click
    $(document).on('click', '.pfg-image-edit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var imageItem = $(this).closest('.pfg-image-item');
        openModalForItem(imageItem);
    });
    
    // Previous button click
    $(document).on('click', '.pfg-modal-prev:not(:disabled)', function() {
        navigateToImage(-1);
    });
    
    // Next button click
    $(document).on('click', '.pfg-modal-next:not(:disabled)', function() {
        navigateToImage(1);
    });
    
    // Close modal
    $(document).on('click', '.pfg-modal-close, .pfg-modal-cancel', function() {
        $('#pfg-image-modal').fadeOut(200);
        currentImageItem = null;
    });
    
    // Close on backdrop click
    $(document).on('click', '#pfg-image-modal', function(e) {
        if ($(e.target).is('#pfg-image-modal')) {
            $('#pfg-image-modal').fadeOut(200);
            currentImageItem = null;
        }
    });
    
    // Keyboard navigation (ESC to close, Arrow keys to navigate)
    $(document).on('keydown', function(e) {
        if (!$('#pfg-image-modal').is(':visible')) return;
        
        // Don't navigate when typing in input fields
        var activeEl = document.activeElement;
        var isInputFocused = activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA');
        
        if (e.key === 'Escape') {
            $('#pfg-image-modal').fadeOut(200);
            currentImageItem = null;
        } else if (e.key === 'ArrowLeft' && !isInputFocused) {
            navigateToImage(-1);
        } else if (e.key === 'ArrowRight' && !isInputFocused) {
            navigateToImage(1);
        }
    });
    
    // Save changes
    $(document).on('click', '.pfg-modal-save', function() {
        saveCurrentChanges();
        
        // Close modal
        $('#pfg-image-modal').fadeOut(200);
        currentImageItem = null;
    });
    
    // Delete image
    $(document).on('click', '.pfg-image-delete', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (confirm('<?php esc_html_e( 'Remove this image from the gallery?', 'portfolio-filter-gallery' ); ?>')) {
            var $item = $(this).closest('.pfg-image-item');
            var imageId = $item.data('id');
            
            // Remove from master array BEFORE DOM removal (element won't exist after .remove())
            removeImageFromMaster(imageId);
            
            $item.fadeOut(200, function() {
                $(this).remove();
                reindexImages();
                markStructurallyModified(); // Deletion is a structural change
                
                // Show empty state if no images left
                if ($('.pfg-image-item').length === 0) {
                    $('#pfg-image-grid').html('<div class="pfg-no-images"><span class="dashicons dashicons-format-gallery"></span><p><?php echo esc_js( __( "No images yet. Add some to get started!", "portfolio-filter-gallery" ) ); ?></p></div>');
                }
            });
        }
    });
    
    // Reindex image inputs after deletion
    function reindexImages() {
        $('.pfg-image-item').each(function(index) {
            $(this).attr('data-index', index);
            $(this).find('input').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                }
            });
        });
    }
    
    // Link type toggle - show/hide URL field based on selection
    $(document).on('change', '#pfg-modal-type', function() {
        var type = $(this).val();
        var $urlRow = $('.pfg-link-url-row');
        var $hint = $('.pfg-url-hint');
        var $fetchBtn = $('#pfg-fetch-video-thumb');
        
        if (type === 'image') {
            $urlRow.hide();
            $hint.text('');
            $fetchBtn.hide();
        } else if (type === 'video') {
            $urlRow.show();
            $hint.text('<?php esc_html_e( 'Paste a YouTube, Vimeo, or other video URL', 'portfolio-filter-gallery' ); ?>');
            $fetchBtn.show();
        } else if (type === 'url') {
            $urlRow.show();
            $hint.text('<?php esc_html_e( 'Opens in new tab when clicked', 'portfolio-filter-gallery' ); ?>');
            $fetchBtn.hide();
        }
    });
    
    // Fetch Video Thumbnail button click
    $(document).on('click', '#pfg-fetch-video-thumb', function(e) {
        var $btn = $(this);
        
        // Check if premium (button is disabled for free users)
        if ($btn.prop('disabled')) {
            e.preventDefault();
            alert('<?php esc_html_e( 'Upgrade to Pro to auto-fetch video thumbnails from YouTube, Vimeo, and more!', 'portfolio-filter-gallery' ); ?>');
            return;
        }
        
        var $status = $('#pfg-fetch-thumb-status');
        var videoUrl = $('#pfg-modal-link').val();
        
        if (!videoUrl) {
            alert('<?php esc_html_e( 'Please enter a video URL first', 'portfolio-filter-gallery' ); ?>');
            return;
        }
        
        // Show loading state
        $btn.prop('disabled', true).text('<?php esc_html_e( 'Fetching...', 'portfolio-filter-gallery' ); ?>');
        $status.show().text('').css('color', '#64748b');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pfg_fetch_video_thumbnail',
                nonce: '<?php echo wp_create_nonce( 'pfg_admin_nonce' ); ?>',
                video_url: videoUrl,
                gallery_id: galleryId
            },
            success: function(response) {
                if (response.success) {
                    // Update the image preview in modal
                    $('#pfg-modal-image').attr('src', response.data.thumbnail_url);
                    
                    // Update the hidden image ID input
                    if (currentImageItem) {
                        currentImageItem.find('input[name$="[id]"]').val(response.data.attachment_id);
                        currentImageItem.find('.pfg-image-thumb').attr('src', response.data.thumbnail_url);
                        currentImageItem.attr('data-id', response.data.attachment_id);
                    }
                    
                    $status.text('<?php esc_html_e( 'Thumbnail imported!', 'portfolio-filter-gallery' ); ?>').css('color', '#10b981');
                    
                    // Show revert button
                    $('#pfg-revert-thumb').show();
                } else {
                    $status.text(response.data.message || '<?php esc_html_e( 'Failed to fetch thumbnail', 'portfolio-filter-gallery' ); ?>').css('color', '#ef4444');
                }
            },
            error: function() {
                $status.text('<?php esc_html_e( 'Error fetching thumbnail', 'portfolio-filter-gallery' ); ?>').css('color', '#ef4444');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-format-video" style="margin-right: 5px;"></span> <?php esc_html_e( 'Fetch Video Thumbnail', 'portfolio-filter-gallery' ); ?>');
            }
        });
    });
    
    // Revert to original image button click
    $(document).on('click', '#pfg-revert-thumb', function() {
        if (!originalImageData) return;
        
        var $btn = $(this);
        var currentThumbId = currentImageItem ? currentImageItem.find('input[name$="[id]"]').val() : null;
        
        // If current thumbnail is different from original, delete it from media library
        if (currentThumbId && currentThumbId !== originalImageData.id) {
            $btn.prop('disabled', true).text('<?php esc_html_e( 'Reverting...', 'portfolio-filter-gallery' ); ?>');
            
            // Delete the fetched thumbnail
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pfg_delete_video_thumbnail',
                    nonce: '<?php echo wp_create_nonce( 'pfg_admin_nonce' ); ?>',
                    attachment_id: currentThumbId
                },
                complete: function() {
                    // Restore original image regardless of delete result
                    restoreOriginalImage();
                    $btn.prop('disabled', false).text('<?php esc_html_e( 'Revert to Original', 'portfolio-filter-gallery' ); ?>');
                }
            });
        } else {
            restoreOriginalImage();
        }
        
        function restoreOriginalImage() {
            // Restore original image
            $('#pfg-modal-image').attr('src', originalImageData.src);
            
            if (currentImageItem) {
                currentImageItem.find('input[name$="[id]"]').val(originalImageData.id);
                currentImageItem.find('input[name$="[original_id]"]').val(originalImageData.id); // Reset original_id too
                currentImageItem.find('.pfg-image-thumb').attr('src', originalImageData.src);
                currentImageItem.attr('data-id', originalImageData.id);
            }
            
            // Hide revert button and update status
            $btn.hide();
            $('#pfg-fetch-thumb-status').text('<?php esc_html_e( 'Reverted to original image', 'portfolio-filter-gallery' ); ?>').css('color', '#64748b').show();
        }
    });
    
    // Product search in Edit Image modal (Link to Product)
    var productSearchTimeout;
    $(document).on('input', '#pfg-modal-product-search', function() {
        var searchTerm = $(this).val();
        var $results = $('#pfg-product-results');
        
        clearTimeout(productSearchTimeout);
        
        // Clear product ID if user is editing the search field and reset border
        $('#pfg-modal-product-id').val('');
        $(this).css('border-color', ''); // Reset border color
        
        if (searchTerm.length < 2) {
            $results.hide().html('');
            return;
        }
        
        $results.show().html('<div class="pfg-searching"><span class="spinner is-active" style="float:none;"></span> <?php esc_html_e( 'Searching...', 'portfolio-filter-gallery' ); ?></div>');
        
        productSearchTimeout = setTimeout(function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pfg_search_products',
                    nonce: '<?php echo wp_create_nonce( 'pfg_admin_nonce' ); ?>',
                    search: searchTerm
                },
                success: function(response) {
                    if (response.success && response.data.products.length > 0) {
                        var html = '';
                        response.data.products.forEach(function(product) {
                            // Use jQuery to safely create elements with proper escaping
                            var $item = $('<div class="pfg-product-result"></div>');
                            $item.attr('data-id', product.id);
                            $item.attr('data-name', product.name);
                            $item.append('<img src="' + product.image + '" alt="" class="pfg-product-result-img">');
                            $item.append($('<span class="pfg-product-result-name"></span>').text(product.name));
                            $item.append($('<span class="pfg-product-result-price"></span>').html(product.price));
                            html += $item.prop('outerHTML');
                        });
                        $results.html(html);
                    } else {
                        $results.html('<div class="pfg-no-results"><?php esc_html_e( 'No products found', 'portfolio-filter-gallery' ); ?></div>');
                    }
                },
                error: function() {
                    $results.html('<div class="pfg-error"><?php esc_html_e( 'Error searching products', 'portfolio-filter-gallery' ); ?></div>');
                }
            });
        }, 300);
    });
    
    // Select product from search results
    $(document).on('click', '.pfg-product-result', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var productId = $(this).data('id');
        var productName = $(this).data('name');
        
        // Set the values
        $('#pfg-modal-product-id').val(productId);
        $('#pfg-modal-product-search').val(productName).css('border-color', '#10b981'); // Green border to show selection
        $('#pfg-product-results').hide().html('');
    });
    
    // Clear product on focus if clicking away
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.pfg-product-search-wrap').length) {
            $('#pfg-product-results').hide();
        }
    });
    
    // Import from Products button click handler
    $(document).on('click', '.pfg-import-products', function(e) {
        e.preventDefault();
        
        // Open product picker modal
        $('#pfg-product-picker-modal').fadeIn(200);
        
        // Load products via AJAX
        loadProducts('');
    });
    
    // Search products in modal
    var searchTimeout;
    $(document).on('input', '#pfg-product-search-input', function() {
        var searchTerm = $(this).val();
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            loadProducts(searchTerm);
        }, 300);
    });
    
    // Load products function
    function loadProducts(search) {
        var $grid = $('#pfg-product-picker-grid');
        $grid.html('<div class="pfg-loading"><span class="spinner is-active"></span> <?php esc_html_e( 'Loading products...', 'portfolio-filter-gallery' ); ?></div>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pfg_get_products_for_import',
                nonce: '<?php echo wp_create_nonce( 'pfg_admin_nonce' ); ?>',
                search: search
            },
            success: function(response) {
                if (response.success) {
                    $grid.html(response.data.html);
                } else {
                    $grid.html('<p class="pfg-error"><?php esc_html_e( 'Error loading products', 'portfolio-filter-gallery' ); ?></p>');
                }
            },
            error: function() {
                $grid.html('<p class="pfg-error"><?php esc_html_e( 'Error loading products', 'portfolio-filter-gallery' ); ?></p>');
            }
        });
    }
    
    // Toggle product selection
    $(document).on('click', '.pfg-product-picker-item', function() {
        $(this).toggleClass('selected');
    });
    
    // Close product picker modal
    $(document).on('click', '.pfg-product-picker-close, .pfg-product-picker-cancel', function() {
        $('#pfg-product-picker-modal').fadeOut(200);
    });
    
    // Import selected products
    $(document).on('click', '.pfg-product-picker-import', function() {
        var selectedProducts = [];
        $('.pfg-product-picker-item.selected').each(function() {
            selectedProducts.push({
                id: $(this).data('product-id'),
                imageId: $(this).data('image-id'),
                title: $(this).data('title'),
                thumbnail: $(this).data('thumbnail')
            });
        });
        
        if (selectedProducts.length === 0) {
            alert('<?php esc_html_e( 'Please select at least one product', 'portfolio-filter-gallery' ); ?>');
            return;
        }
        
        // Add products as gallery images
        var $grid = $('#pfg-image-grid');
        var currentIndex = $('.pfg-image-item').length;
        
        // Remove "no images" message if present
        $grid.find('.pfg-no-images').remove();
        
        selectedProducts.forEach(function(product, i) {
            var index = currentIndex + i;
            var html = '<div class="pfg-image-item" data-id="' + product.imageId + '" data-index="' + index + '">';
            html += '<img src="' + product.thumbnail + '" alt="' + product.title + '" class="pfg-image-thumb" loading="lazy">';
            html += '<div class="pfg-image-actions">';
            html += '<button type="button" class="pfg-image-action pfg-image-edit" title="<?php esc_attr_e( 'Edit', 'portfolio-filter-gallery' ); ?>"><span class="dashicons dashicons-edit"></span></button>';
            html += '<button type="button" class="pfg-image-action pfg-image-delete" title="<?php esc_attr_e( 'Delete', 'portfolio-filter-gallery' ); ?>"><span class="dashicons dashicons-trash"></span></button>';
            html += '</div>';
            html += '<div class="pfg-image-info"><p class="pfg-image-title">' + product.title + '</p></div>';
            html += '<input type="hidden" name="pfg_images[' + index + '][id]" value="' + product.imageId + '">';
            html += '<input type="hidden" name="pfg_images[' + index + '][title]" value="' + product.title + '">';
            html += '<input type="hidden" name="pfg_images[' + index + '][description]" value="">';
            html += '<input type="hidden" name="pfg_images[' + index + '][link]" value="">';
            html += '<input type="hidden" name="pfg_images[' + index + '][type]" value="image">';
            html += '<input type="hidden" name="pfg_images[' + index + '][filters]" value="">';
            html += '<input type="hidden" name="pfg_images[' + index + '][product_id]" value="' + product.id + '">';
            html += '</div>';
            $grid.append(html);
        });
        
        // Close modal
        $('#pfg-product-picker-modal').fadeOut(200);
    });
    
    // =====================
    // Bulk Selection Logic
    // =====================
    
    // Show/hide bulk actions bar based on image count
    function updateBulkActionsBar() {
        var imageCount = $('.pfg-image-item').length;
        if (imageCount > 0) {
            $('#pfg-bulk-actions').css('display', 'flex');
        } else {
            $('#pfg-bulk-actions').hide();
        }
    }
    
    // Update selected count
    function updateSelectedCount() {
        var count = $('.pfg-image-select:checked').length;
        $('#pfg-selected-num').text(count);
        
        if (count > 0) {
            $('.pfg-delete-selected').show();
        } else {
            $('.pfg-delete-selected').hide();
        }
        
        // Update select all checkbox state
        var totalImages = $('.pfg-image-select').length;
        $('#pfg-select-all').prop('checked', count === totalImages && totalImages > 0);
        $('#pfg-select-all').prop('indeterminate', count > 0 && count < totalImages);
    }
    
    // Individual image checkbox change
    $(document).on('change', '.pfg-image-select', function() {
        var $item = $(this).closest('.pfg-image-item');
        if ($(this).is(':checked')) {
            $item.addClass('selected');
        } else {
            $item.removeClass('selected');
        }
        updateSelectedCount();
    });
    
    // Select All checkbox
    $(document).on('change', '#pfg-select-all', function() {
        var isChecked = $(this).is(':checked');
        $('.pfg-image-select').prop('checked', isChecked).trigger('change');
    });
    
    // Delete Selected button
    $(document).on('click', '.pfg-delete-selected', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var selectedCount = $('.pfg-image-select:checked').length;
        if (selectedCount === 0) return;
        
        var confirmMsg = '<?php echo esc_js( sprintf( __( 'Are you sure you want to remove %s image(s) from the gallery?', 'portfolio-filter-gallery' ), '\' + selectedCount + \'' ) ); ?>';
        
        if (confirm(confirmMsg)) {
            $('.pfg-image-select:checked').each(function() {
                var $item = $(this).closest('.pfg-image-item');
                var imageId = $item.data('id');
                
                // Remove from master array BEFORE removing from DOM
                if (typeof removeImageFromMaster === 'function') {
                    removeImageFromMaster(imageId);
                }
                
                $item.remove();
            });
            
            reindexImages();
            updateSelectedCount();
            updateBulkActionsBar();
            markStructurallyModified(); // Bulk delete is a structural change
            
            // Update pagination after deletion
            if (typeof updatePaginationUI === 'function') {
                updatePaginationUI();
            }
            
            // Check if no images left
            if ($('.pfg-image-item').length === 0 && masterImagesArray.length === 0) {
                $('#pfg-image-grid').html('<div class="pfg-no-images"><span class="dashicons dashicons-format-gallery"></span><p><?php echo esc_js( __( 'No images yet. Add some to get started!', 'portfolio-filter-gallery' ) ); ?></p></div>');
                // Hide pagination when no images
                $('#pfg-pagination-controls').hide();
            }
        }
    });
    
    // Initialize bulk actions bar on page load
    updateBulkActionsBar();
});
</script>

<!-- Product Picker Modal -->
<div id="pfg-product-picker-modal" class="pfg-modal" style="display: none;">
    <div class="pfg-modal-content" style="max-width: 800px;">
        <div class="pfg-modal-header">
            <h3><?php esc_html_e( 'Import from Products', 'portfolio-filter-gallery' ); ?></h3>
            <button type="button" class="pfg-modal-close pfg-product-picker-close">&times;</button>
        </div>
        
        <div class="pfg-modal-body" style="display: block;">
            <div class="pfg-product-picker-search" style="margin-bottom: 15px;">
                <input type="text" id="pfg-product-search-input" class="pfg-input" placeholder="<?php esc_attr_e( 'Search products...', 'portfolio-filter-gallery' ); ?>" style="width: 100%;">
            </div>
            
            <div id="pfg-product-picker-grid" class="pfg-product-picker-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; max-height: 400px; overflow-y: auto;">
                <!-- Products loaded via AJAX -->
            </div>
        </div>
        
        <div class="pfg-modal-footer">
            <button type="button" class="pfg-btn pfg-btn-secondary pfg-product-picker-cancel">
                <?php esc_html_e( 'Cancel', 'portfolio-filter-gallery' ); ?>
            </button>
            <button type="button" class="pfg-btn pfg-btn-primary pfg-product-picker-import">
                <?php esc_html_e( 'Import Selected', 'portfolio-filter-gallery' ); ?>
            </button>
        </div>
    </div>
</div>

<style>
.pfg-product-picker-item {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.2s;
}
.pfg-product-picker-item:hover {
    border-color: #3858e9;
}
.pfg-product-picker-item.selected {
    border-color: #3858e9;
    box-shadow: 0 0 0 3px rgba(56, 88, 233, 0.2);
}
.pfg-product-picker-item.selected::after {
    content: '✓';
    position: absolute;
    top: 8px;
    right: 8px;
    background: #3858e9;
    color: #fff;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}
.pfg-product-picker-item {
    position: relative;
}
.pfg-product-picker-item img {
    width: 100%;
    height: 100px;
    object-fit: cover;
}
.pfg-product-picker-item .title {
    padding: 8px;
    font-size: 12px;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.pfg-loading {
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px;
    color: #64748b;
}
.pfg-loading .spinner {
    float: none;
    margin: 0 5px 0 0;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Handle collapse toggle clicks for filter groups (both old and new styles)
    $(document).on('click', '.pfg-collapse-toggle, .pfg-tree-toggle', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $group = $(this).closest('.pfg-filter-collapsible-group');
        var isExpanded = $group.attr('data-expanded') === 'true';
        
        // Toggle state
        $group.attr('data-expanded', isExpanded ? 'false' : 'true');
    });
    
    // Bulk selection functionality
    var $bulkActions = $('#pfg-bulk-actions');
    var $imageGrid = $('#pfg-image-grid');
    var $selectAllCheckbox = $('#pfg-select-all');
    var $selectedCount = $('#pfg-selected-num');
    var $deleteBtn = $('.pfg-delete-selected');
    var $bulkFiltersDropdown = $('.pfg-bulk-filters-dropdown');
    var $bulkFiltersMenu = $('.pfg-bulk-filters-menu');
    
    // Update selection count and show/hide bulk actions
    function updateSelectionUI() {
        var $selected = $imageGrid.find('.pfg-image-select:checked');
        var count = $selected.length;
        
        $selectedCount.text(count);
        
        // Keep bar visible, only show/hide action buttons
        if (count > 0) {
            $deleteBtn.show();
            $bulkFiltersDropdown.show();
        } else {
            $deleteBtn.hide();
            $bulkFiltersDropdown.hide();
            $bulkFiltersMenu.hide();
        }
        
        // Update select all checkbox state
        var totalCheckboxes = $imageGrid.find('.pfg-image-select').length;
        $selectAllCheckbox.prop('checked', count > 0 && count === totalCheckboxes);
    }
    
    // Image checkbox change
    $(document).on('change', '.pfg-image-select', function() {
        updateSelectionUI();
    });
    
    // Select all checkbox
    $selectAllCheckbox.on('change', function() {
        var isChecked = $(this).prop('checked');
        $imageGrid.find('.pfg-image-select').prop('checked', isChecked);
        updateSelectionUI();
    });
    
    // Toggle bulk filters menu
    $(document).on('click', '.pfg-bulk-filters-btn', function(e) {
        e.stopPropagation();
        $bulkFiltersMenu.toggle();
    });
    
    // Close menu when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.pfg-bulk-filters-dropdown').length) {
            $bulkFiltersMenu.hide();
        }
    });
    
    // Cancel button
    $(document).on('click', '.pfg-cancel-bulk-filters', function() {
        $bulkFiltersMenu.hide();
        // Reset checkboxes
        $('.pfg-bulk-filter-checkbox').prop('checked', false);
    });
    
    // Apply bulk filters
    $(document).on('click', '.pfg-apply-bulk-filters', function() {
        var mode = $('#pfg-bulk-filter-mode').val();
        var selectedFilters = [];
        
        $('.pfg-bulk-filter-checkbox:checked').each(function() {
            selectedFilters.push($(this).val());
        });
        
        if (selectedFilters.length === 0) {
            alert('<?php echo esc_js( __( 'Please select at least one filter', 'portfolio-filter-gallery' ) ); ?>');
            return;
        }
        
        var $selectedItems = $imageGrid.find('.pfg-image-select:checked').closest('.pfg-image-item');
        var appliedCount = 0;
        
        $selectedItems.each(function() {
            var $item = $(this);
            var $filtersInput = $item.find('input[name$="[filters]"]');
            
            if ($filtersInput.length) {
                var currentFilters = $filtersInput.val();
                var filterArray = currentFilters ? currentFilters.split(',').filter(function(f) { return f; }) : [];
                
                if (mode === 'replace') {
                    // Replace all filters
                    filterArray = selectedFilters.slice();
                } else if (mode === 'remove') {
                    // Remove selected filters
                    filterArray = filterArray.filter(function(f) {
                        return selectedFilters.indexOf(f) === -1;
                    });
                } else {
                    // Add mode (default) - add new filters
                    selectedFilters.forEach(function(f) {
                        if (filterArray.indexOf(f) === -1) {
                            filterArray.push(f);
                        }
                    });
                }
                
                $filtersInput.val(filterArray.join(','));
                appliedCount++;
                
                // Update visual filter tags
                var $filterTagsContainer = $item.find('.pfg-image-filters');
                $filterTagsContainer.empty(); // Clear existing tags
                
                if (filterArray.length > 0) {
                    if ($filterTagsContainer.length === 0) {
                        $item.find('.pfg-image-info').append('<div class="pfg-image-filters"></div>');
                        $filterTagsContainer = $item.find('.pfg-image-filters');
                    }
                    // Note: Would need filter names lookup for accurate tags, using IDs for now
                    filterArray.forEach(function(filterId) {
                        var $checkbox = $('.pfg-bulk-filter-checkbox[value="' + filterId + '"]');
                        var filterName = $checkbox.closest('label').find('span:last').text() || filterId;
                        $filterTagsContainer.append('<span class="pfg-image-filter-tag">' + filterName + '</span>');
                    });
                }
            }
        });
        
        // Close menu and reset filter checkboxes (keep image selection)
        $bulkFiltersMenu.hide();
        $('.pfg-bulk-filter-checkbox').prop('checked', false);
        
        // Mark images as modified for chunked save
        if (typeof window.pfgMarkImagesModified === 'function') {
            window.pfgMarkImagesModified();
        }
        
        // Show success message
        var modeText = mode === 'replace' ? '<?php echo esc_js( __( 'Filters replaced on', 'portfolio-filter-gallery' ) ); ?>' :
                       mode === 'remove' ? '<?php echo esc_js( __( 'Filters removed from', 'portfolio-filter-gallery' ) ); ?>' :
                       '<?php echo esc_js( __( 'Filters added to', 'portfolio-filter-gallery' ) ); ?>';
        alert(modeText + ' ' + appliedCount + ' <?php echo esc_js( __( 'images!', 'portfolio-filter-gallery' ) ); ?>');
    });
});
</script>
