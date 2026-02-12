<?php
/**
 * Gallery Settings Meta Box Template.
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

// Get templates - use PFG_Templates if available, otherwise use default
if ( class_exists( 'PFG_Templates' ) ) {
    $templates = PFG_Templates::get_all();
} else {
    // Default templates fallback
    $templates = array(
        'default' => array( 'name' => __( 'Default', 'portfolio-filter-gallery' ) ),
        'minimal' => array( 'name' => __( 'Minimal', 'portfolio-filter-gallery' ) ),
        'modern'  => array( 'name' => __( 'Modern', 'portfolio-filter-gallery' ) ),
    );
}
?>

<div class="pfg-meta-box pfg-settings-meta-box">
    
    <div class="pfg-tabs-wrapper">
        <!-- Tabs Navigation -->
        <div class="pfg-tabs">
            <button type="button" class="pfg-tab active" data-tab="pfg-tab-layout">
                <span class="dashicons dashicons-layout"></span>
                <?php esc_html_e( 'Layout', 'portfolio-filter-gallery' ); ?>
            </button>
            <button type="button" class="pfg-tab" data-tab="pfg-tab-filters">
                <span class="dashicons dashicons-filter"></span>
                <?php esc_html_e( 'Filters', 'portfolio-filter-gallery' ); ?>
            </button>
            <button type="button" class="pfg-tab" data-tab="pfg-tab-lightbox">
                <span class="dashicons dashicons-format-image"></span>
                <?php esc_html_e( 'Lightbox', 'portfolio-filter-gallery' ); ?>
            </button>
            <button type="button" class="pfg-tab" data-tab="pfg-tab-style">
                <span class="dashicons dashicons-art"></span>
                <?php esc_html_e( 'Styling', 'portfolio-filter-gallery' ); ?>
            </button>
            <button type="button" class="pfg-tab" data-tab="pfg-tab-advanced">
                <span class="dashicons dashicons-admin-tools"></span>
                <?php esc_html_e( 'Advanced', 'portfolio-filter-gallery' ); ?>
            </button>
            <?php if ( PFG_WooCommerce::is_active() ) : ?>
            <button type="button" class="pfg-tab" data-tab="pfg-tab-woocommerce">
                <span class="dashicons dashicons-cart"></span>
                <?php esc_html_e( 'WooCommerce', 'portfolio-filter-gallery' ); ?>
                <?php if ( ! PFG_Features::is_premium() ) : ?>
                    <span class="pfg-pro-badge">PRO</span>
                <?php endif; ?>
            </button>
            <?php endif; ?>
        </div>

        <!-- Layout Tab -->
        <div id="pfg-tab-layout" class="pfg-tab-content active">
            
            <!-- Quick Start Section -->
            <h4 class="pfg-form-section-title pfg-section-icon">
                <span class="dashicons dashicons-welcome-learn-more"></span>
                <?php esc_html_e( 'Quick Start', 'portfolio-filter-gallery' ); ?>
            </h4>
            
            <!-- Template Selection -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Template', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Start with a pre-designed style', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <div class="pfg-template-grid">
                <?php foreach ( $templates as $id => $template ) : 
                        // Determine icon and type based on layout type
                        $layout_type = isset( $template['settings']['layout_type'] ) ? $template['settings']['layout_type'] : 'grid';
                        
                        // Map layout types to icons and labels
                        $layout_icons = array(
                            'grid'      => 'dashicons-grid-view',
                            'masonry'   => 'dashicons-images-alt',
                            'justified' => 'dashicons-align-left',
                            'packed'    => 'dashicons-screenoptions',
                        );
                        $layout_labels = array(
                            'grid'      => __( 'Grid', 'portfolio-filter-gallery' ),
                            'masonry'   => __( 'Masonry', 'portfolio-filter-gallery' ),
                            'justified' => __( 'Justified', 'portfolio-filter-gallery' ),
                            'packed'    => __( 'Packed', 'portfolio-filter-gallery' ),
                        );
                        
                        $icon = isset( $layout_icons[ $layout_type ] ) ? $layout_icons[ $layout_type ] : 'dashicons-grid-view';
                        $layout_label = isset( $layout_labels[ $layout_type ] ) ? $layout_labels[ $layout_type ] : __( 'Grid', 'portfolio-filter-gallery' );
                        
                        // Check if layout is premium-only
                        $is_premium_layout = in_array( $layout_type, array( 'justified', 'packed' ), true );
                        $is_locked = $is_premium_layout && ! pfg_is_premium();
                    ?>
                    <div class="pfg-template-card <?php echo ( $settings['template'] ?? '' ) === $id ? 'selected' : ''; ?> <?php echo $is_locked ? 'pfg-template-locked' : ''; ?>" 
                         data-template="<?php echo esc_attr( $id ); ?>"
                         data-layout="<?php echo esc_attr( $layout_type ); ?>"
                         data-locked="<?php echo $is_locked ? '1' : '0'; ?>"
                         title="<?php echo esc_attr( $template['description'] ?? '' ); ?>">
                        <?php if ( $is_locked ) : ?>
                            <span class="pfg-card-pro-badge">PRO</span>
                        <?php endif; ?>
                        <div class="pfg-template-preview">
                            <span class="dashicons <?php echo esc_attr( $icon ); ?>"></span>
                        </div>
                        <span class="pfg-template-name"><?php echo esc_html( $template['name'] ); ?></span>
                        <span class="pfg-template-type pfg-type-<?php echo esc_attr( $layout_type ); ?>"><?php echo esc_html( $layout_label ); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="pfg_settings[template]" id="pfg-template" value="<?php echo esc_attr( $settings['template'] ?? '' ); ?>">
            </div>
            
            <!-- Fine-Tune Layout Section -->
            <hr class="pfg-form-separator">
            <h4 class="pfg-form-section-title pfg-section-icon">
                <span class="dashicons dashicons-admin-generic"></span>
                <?php esc_html_e( 'Fine-Tune Layout', 'portfolio-filter-gallery' ); ?>
            </h4>
            
            <!-- Layout Type -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Layout Type', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'How images are arranged in the gallery', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <select name="pfg_settings[layout]" id="pfg-layout" class="pfg-select">
                    <option value="grid" <?php selected( $settings['layout_type'] ?? 'masonry', 'grid' ); ?>><?php esc_html_e( 'Grid - Equal sized cells', 'portfolio-filter-gallery' ); ?></option>
                    <option value="masonry" <?php selected( $settings['layout_type'] ?? 'masonry', 'masonry' ); ?>><?php esc_html_e( 'Masonry - Variable height columns', 'portfolio-filter-gallery' ); ?></option>
                    <option value="justified" <?php selected( $settings['layout_type'] ?? 'masonry', 'justified' ); ?> <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>><?php esc_html_e( 'Justified - Full-width rows', 'portfolio-filter-gallery' ); ?><?php echo ! pfg_is_premium() ? ' [PRO]' : ''; ?></option>
                    <option value="packed" <?php selected( $settings['layout_type'] ?? 'masonry', 'packed' ); ?> <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>><?php esc_html_e( 'Packed - Puzzle-like layout', 'portfolio-filter-gallery' ); ?><?php echo ! pfg_is_premium() ? ' [PRO]' : ''; ?></option>
                </select>
            </div>
            
            <!-- Columns (for Grid/Masonry) -->
            <div class="pfg-form-row pfg-layout-option" data-layouts="grid,masonry">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Columns', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Number of columns per device type', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <div class="pfg-responsive-columns">
                    <!-- Device Type Toggle -->
                    <div class="pfg-device-toggle">
                        <button type="button" class="pfg-device-btn active" data-device="desktop" title="<?php esc_attr_e( 'Desktop', 'portfolio-filter-gallery' ); ?>">
                            <span class="dashicons dashicons-desktop"></span>
                        </button>
                        <button type="button" class="pfg-device-btn pfg-device-pro" data-device="tablet" title="<?php esc_attr_e( 'Tablet (Pro)', 'portfolio-filter-gallery' ); ?>">
                            <span class="dashicons dashicons-tablet"></span>
                            <span class="pfg-device-pro-badge">PRO</span>
                        </button>
                        <button type="button" class="pfg-device-btn pfg-device-pro" data-device="mobile" title="<?php esc_attr_e( 'Mobile (Pro)', 'portfolio-filter-gallery' ); ?>">
                            <span class="dashicons dashicons-smartphone"></span>
                            <span class="pfg-device-pro-badge">PRO</span>
                        </button>
                    </div>
                    
                    <!-- Desktop Columns (only one active in free version) -->
                    <div class="pfg-device-panel pfg-device-desktop active">
                        <div class="pfg-range">
                            <input type="range" name="pfg_settings[columns]" min="1" max="10" value="<?php echo esc_attr( $settings['columns_lg'] ?? 3 ); ?>" data-suffix="">
                            <span class="pfg-range-value"><?php echo esc_html( $settings['columns_lg'] ?? 3 ); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Justified: Row Height -->
            <div class="pfg-form-row pfg-layout-option" data-layouts="justified">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Row Height', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Height of each row in pixels', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <div class="pfg-range">
                    <input type="range" name="pfg_settings[justified_row_height]" min="100" max="400" value="<?php echo esc_attr( $settings['justified_row_height'] ?? 200 ); ?>" data-suffix="px">
                    <span class="pfg-range-value"><?php echo esc_html( $settings['justified_row_height'] ?? 200 ); ?>px</span>
                </div>
            </div>
            
            <!-- Justified: Last Row -->
            <div class="pfg-form-row pfg-layout-option" data-layouts="justified">
                <label class="pfg-form-label"><?php esc_html_e( 'Last Row', 'portfolio-filter-gallery' ); ?></label>
                <select name="pfg_settings[justified_last_row]" class="pfg-select">
                    <option value="left" <?php selected( $settings['justified_last_row'] ?? 'left', 'left' ); ?>><?php esc_html_e( 'Left Align', 'portfolio-filter-gallery' ); ?></option>
                    <option value="justify" <?php selected( $settings['justified_last_row'] ?? 'left', 'justify' ); ?>><?php esc_html_e( 'Stretch to Fill', 'portfolio-filter-gallery' ); ?></option>
                    <option value="hide" <?php selected( $settings['justified_last_row'] ?? 'left', 'hide' ); ?>><?php esc_html_e( 'Hide', 'portfolio-filter-gallery' ); ?></option>
                </select>
            </div>
            
            <!-- Packed: Min Item Size -->
            <div class="pfg-form-row pfg-layout-option" data-layouts="packed">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Min Item Size', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Minimum size for grid cells', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <div class="pfg-range">
                    <input type="range" name="pfg_settings[packed_min_size]" min="100" max="300" value="<?php echo esc_attr( $settings['packed_min_size'] ?? 150 ); ?>" data-suffix="px">
                    <span class="pfg-range-value"><?php echo esc_html( $settings['packed_min_size'] ?? 150 ); ?>px</span>
                </div>
            </div>
            
            <!-- Gap -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Gap (px)', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Spacing between gallery items', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <div class="pfg-range">
                    <input type="range" name="pfg_settings[gap]" min="0" max="50" value="<?php echo esc_attr( $settings['gap'] ?? 20 ); ?>" data-suffix="px">
                    <span class="pfg-range-value"><?php echo esc_html( $settings['gap'] ?? 20 ); ?>px</span>
                </div>
            </div>
            
            <!-- Border Radius -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Border Radius (px)', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Rounded corners for images', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <div class="pfg-range">
                    <input type="range" name="pfg_settings[border_radius]" min="0" max="30" value="<?php echo esc_attr( $settings['border_radius'] ?? 8 ); ?>" data-suffix="px">
                    <span class="pfg-range-value"><?php echo esc_html( $settings['border_radius'] ?? 8 ); ?>px</span>
                </div>
            </div>
            
            <!-- Image Size -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Image Size', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'WordPress image size for thumbnails', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <select name="pfg_settings[image_size]" class="pfg-select">
                    <?php 
                    $sizes = get_intermediate_image_sizes();
                    $sizes[] = 'full';
                    foreach ( $sizes as $size ) : ?>
                        <option value="<?php echo esc_attr( $size ); ?>" <?php selected( $settings['image_size'] ?? 'large', $size ); ?>>
                            <?php echo esc_html( ucwords( str_replace( '_', ' ', $size ) ) ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Separator -->
            <hr class="pfg-form-separator">
            <h4 class="pfg-form-section-title"><?php esc_html_e( 'Item Display', 'portfolio-filter-gallery' ); ?></h4>
            
            <!-- Hover Effect -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Hover Effect', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Animation when hovering over images', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <select name="pfg_settings[hover_effect]" class="pfg-select">
                    <option value="none" <?php selected( $settings['hover_effect'] ?? 'zoom', 'none' ); ?>><?php esc_html_e( 'None', 'portfolio-filter-gallery' ); ?></option>
                    <option value="zoom" <?php selected( $settings['hover_effect'] ?? 'zoom', 'zoom' ); ?>><?php esc_html_e( 'Zoom', 'portfolio-filter-gallery' ); ?></option>
                    <option value="fade" <?php selected( $settings['hover_effect'] ?? 'zoom', 'fade' ); ?>><?php esc_html_e( 'Fade', 'portfolio-filter-gallery' ); ?></option>
                    <option value="slide-up" <?php selected( $settings['hover_effect'] ?? 'zoom', 'slide-up' ); ?>><?php esc_html_e( 'Slide Up', 'portfolio-filter-gallery' ); ?></option>
                    <option value="blur" <?php selected( $settings['hover_effect'] ?? 'zoom', 'blur' ); ?> <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>><?php esc_html_e( 'Blur', 'portfolio-filter-gallery' ); ?><?php echo ! pfg_is_premium() ? ' [PRO]' : ''; ?></option>
                    <option value="grayscale" <?php selected( $settings['hover_effect'] ?? 'zoom', 'grayscale' ); ?> <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>><?php esc_html_e( 'Grayscale', 'portfolio-filter-gallery' ); ?><?php echo ! pfg_is_premium() ? ' [PRO]' : ''; ?></option>
                    <option value="shine" <?php selected( $settings['hover_effect'] ?? 'zoom', 'shine' ); ?> <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>><?php esc_html_e( 'Shine', 'portfolio-filter-gallery' ); ?><?php echo ! pfg_is_premium() ? ' [PRO]' : ''; ?></option>
                    <option value="3d-tilt" <?php selected( $settings['hover_effect'] ?? 'zoom', '3d-tilt' ); ?> <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>><?php esc_html_e( '3D Tilt', 'portfolio-filter-gallery' ); ?><?php echo ! pfg_is_premium() ? ' [PRO]' : ''; ?></option>
                </select>
            </div>
            
            <!-- Title Position -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Title Position', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Choose where to display the title', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <select name="pfg_settings[title_position]" class="pfg-select">
                    <option value="overlay" <?php selected( $settings['title_position'] ?? 'overlay', 'overlay' ); ?>><?php esc_html_e( 'Overlay on hover', 'portfolio-filter-gallery' ); ?></option>
                    <option value="below" <?php selected( $settings['title_position'] ?? 'overlay', 'below' ); ?>><?php esc_html_e( 'Below image (card style)', 'portfolio-filter-gallery' ); ?></option>
                </select>
            </div>
            
            <!-- Show Title Overlay -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Show Title', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Display image title on gallery items', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <label class="pfg-toggle">
                    <input type="checkbox" name="pfg_settings[show_title_overlay]" value="1" <?php checked( $settings['show_title'] ?? true ); ?>>
                    <span class="pfg-toggle-slider"></span>
                </label>
            </div>
            
            <!-- Show Description -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Show Description', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Display image description below the title', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <label class="pfg-toggle">
                    <input type="checkbox" name="pfg_settings[show_description]" value="1" <?php checked( $settings['show_description'] ?? false ); ?>>
                    <span class="pfg-toggle-slider"></span>
                </label>
            </div>
            
            <!-- Show Categories -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Show Categories', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Display filter/category names on cards', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <label class="pfg-toggle">
                    <input type="checkbox" name="pfg_settings[show_categories]" value="1" <?php checked( $settings['show_categories'] ?? false ); ?>>
                    <span class="pfg-toggle-slider"></span>
                </label>
            </div>
            
        </div>

        <!-- Filters Tab -->
        <div id="pfg-tab-filters" class="pfg-tab-content">
            
            <!-- Filter Display Section -->
            <h4 class="pfg-form-section-title" style="margin-top: 0;">
                <span class="dashicons dashicons-filter" style="margin-right: 5px;"></span>
                <?php esc_html_e( 'Filter Display', 'portfolio-filter-gallery' ); ?>
            </h4>
            
            <!-- Show Filters -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Show Filter Buttons', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Display filter buttons above gallery', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <label class="pfg-toggle">
                    <input type="checkbox" name="pfg_settings[show_filters]" value="1" <?php checked( $settings['filters_enabled'] ?? true ); ?>>
                    <span class="pfg-toggle-slider"></span>
                </label>
            </div>
            
            <!-- Filter Settings Group (depends on show_filters) -->
            <div class="pfg-conditional pfg-settings-group" data-depends="pfg_settings[show_filters]">
                
                <!-- Filter Appearance Section -->
                <h4 class="pfg-form-section-title" style="margin-top: 20px;">
                    <span class="dashicons dashicons-admin-appearance" style="margin-right: 5px;"></span>
                    <?php esc_html_e( 'Filter Appearance', 'portfolio-filter-gallery' ); ?>
                </h4>
            
            <!-- Filter Position -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Filter Position', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Where to display filter buttons', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <select name="pfg_settings[filter_position]" class="pfg-select">
                    <option value="left" <?php selected( $settings['filters_position'] ?? 'center', 'left' ); ?>><?php esc_html_e( 'Top Left', 'portfolio-filter-gallery' ); ?></option>
                    <option value="center" <?php selected( $settings['filters_position'] ?? 'center', 'center' ); ?>><?php esc_html_e( 'Top Center', 'portfolio-filter-gallery' ); ?></option>
                    <option value="right" <?php selected( $settings['filters_position'] ?? 'center', 'right' ); ?>><?php esc_html_e( 'Top Right', 'portfolio-filter-gallery' ); ?></option>
                    <option value="sidebar-left" <?php selected( $settings['filters_position'] ?? 'center', 'sidebar-left' ); ?> <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>><?php esc_html_e( 'Sidebar Left', 'portfolio-filter-gallery' ); ?><?php echo ! pfg_is_premium() ? ' [PRO]' : ''; ?></option>
                    <option value="sidebar-right" <?php selected( $settings['filters_position'] ?? 'center', 'sidebar-right' ); ?> <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>><?php esc_html_e( 'Sidebar Right', 'portfolio-filter-gallery' ); ?><?php echo ! pfg_is_premium() ? ' [PRO]' : ''; ?></option>
                </select>
            </div>
            
            <!-- Filter Style -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Filter Style', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Visual style for filter buttons', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <select name="pfg_settings[filters_style]" class="pfg-select">
                    <option value="buttons" <?php selected( $settings['filters_style'] ?? 'buttons', 'buttons' ); ?>><?php esc_html_e( 'Buttons (Filled)', 'portfolio-filter-gallery' ); ?></option>
                    <option value="minimal" <?php selected( $settings['filters_style'] ?? 'buttons', 'minimal' ); ?>><?php esc_html_e( 'Minimal (Text Only)', 'portfolio-filter-gallery' ); ?></option>
                    <option value="pills" <?php selected( $settings['filters_style'] ?? 'buttons', 'pills' ); ?> <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>><?php esc_html_e( 'Pills (Rounded)', 'portfolio-filter-gallery' ); ?><?php echo ! pfg_is_premium() ? ' [PRO]' : ''; ?></option>
                    <option value="outline" <?php selected( $settings['filters_style'] ?? 'buttons', 'outline' ); ?> <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>><?php esc_html_e( 'Outline (Bordered)', 'portfolio-filter-gallery' ); ?><?php echo ! pfg_is_premium() ? ' [PRO]' : ''; ?></option>
                    <option value="underline" <?php selected( $settings['filters_style'] ?? 'buttons', 'underline' ); ?> <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>><?php esc_html_e( 'Underline', 'portfolio-filter-gallery' ); ?><?php echo ! pfg_is_premium() ? ' [PRO]' : ''; ?></option>
                    <option value="dropdown" <?php selected( $settings['filters_style'] ?? 'buttons', 'dropdown' ); ?>><?php esc_html_e( 'Dropdown', 'portfolio-filter-gallery' ); ?></option>
                </select>
            </div>
            
            <!-- "All" Button Section -->
            <h4 class="pfg-form-section-title" style="margin-top: 20px;">
                <span class="dashicons dashicons-screenoptions" style="margin-right: 5px;"></span>
                <?php esc_html_e( '"All" Button', 'portfolio-filter-gallery' ); ?>
            </h4>
            
            <!-- Show All Button -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Show "All" Button', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Button to show all items without filtering', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <label class="pfg-toggle">
                    <input type="checkbox" name="pfg_settings[show_all_button]" value="1" <?php checked( $settings['show_all_button'] ?? true ); ?>>
                    <span class="pfg-toggle-slider"></span>
                </label>
            </div>
            
            <!-- All Button Text -->
            <div class="pfg-form-row pfg-conditional" data-depends="pfg_settings[show_all_button]">
                <label class="pfg-form-label">
                    <?php esc_html_e( '"All" Button Text', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Custom text for the All button', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <input type="text" name="pfg_settings[all_button_text]" class="pfg-input" value="<?php echo esc_attr( $settings['all_button_text'] ?? 'All' ); ?>">
            </div>
            
            <!-- Filter Enhancements Section -->
            <h4 class="pfg-form-section-title" style="margin-top: 20px;">
                <span class="dashicons dashicons-star-filled" style="margin-right: 5px;"></span>
                <?php esc_html_e( 'Filter Enhancements', 'portfolio-filter-gallery' ); ?>
            </h4>
            
            <!-- Show Filter Color Dots -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Show Filter Color Dots', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Display colored dots on filter buttons based on Color Tag', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <label class="pfg-toggle">
                    <input type="checkbox" name="pfg_settings[show_filter_colors]" value="1" <?php checked( $settings['show_filter_colors'] ?? true ); ?>>
                    <span class="pfg-toggle-slider"></span>
                </label>
            </div>
            
            <!-- Show Filter Item Count -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Show Filter Item Count', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Display number of items next to each filter, e.g., Portraits (5)', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <label class="pfg-toggle">
                    <input type="checkbox" name="pfg_settings[show_filter_count]" value="1" <?php checked( $settings['show_filter_count'] ?? false ); ?>>
                    <span class="pfg-toggle-slider"></span>
                </label>
            </div>
            
            <!-- Count Display Style (conditional on show_filter_count) -->
            <div class="pfg-form-row pfg-conditional" data-depends="pfg_settings[show_filter_count]">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Count Display Style', 'portfolio-filter-gallery' ); ?>
                </label>
                <select name="pfg_settings[filter_count_style]" class="pfg-select">
                    <option value="always" <?php selected( $settings['filter_count_style'] ?? 'always', 'always' ); ?>><?php esc_html_e( 'Always Visible', 'portfolio-filter-gallery' ); ?></option>
                    <option value="hover" <?php selected( $settings['filter_count_style'] ?? 'always', 'hover' ); ?>><?php esc_html_e( 'On Hover Only', 'portfolio-filter-gallery' ); ?></option>
                </select>
            </div>
            
            <!-- Multi-Filter Selection Section -->
            <h4 class="pfg-form-section-title" style="margin-top: 20px;">
                <span class="dashicons dashicons-forms" style="margin-right: 5px;"></span>
                <?php esc_html_e( 'Multi-Filter Selection', 'portfolio-filter-gallery' ); ?>
                <?php if ( ! pfg_is_premium() ) : ?><span class="pfg-pro-badge">PRO</span><?php endif; ?>
            </h4>
            
            <!-- Enable Multi-Filtering -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Enable Multi-Filter Selection', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Allow selecting multiple filters at once', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <label class="pfg-toggle">
                    <input type="checkbox" name="pfg_settings[multi_level_filters]" value="1" <?php checked( $settings['multi_level_filters'] ?? false ); ?> <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>>
                    <span class="pfg-toggle-slider"></span>
                </label>
            </div>
            
            <!-- Filter Logic (conditional) -->
            <div class="pfg-form-row pfg-conditional" data-depends="pfg_settings[multi_level_filters]">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Default Filter Logic', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'How to combine multiple filters by default', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <select name="pfg_settings[filter_logic]" class="pfg-select" <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>>
                    <option value="or" <?php selected( $settings['filter_logic'] ?? 'or', 'or' ); ?>><?php esc_html_e( 'OR - Match any filter', 'portfolio-filter-gallery' ); ?></option>
                    <option value="and" <?php selected( $settings['filter_logic'] ?? 'or', 'and' ); ?>><?php esc_html_e( 'AND - Match all filters', 'portfolio-filter-gallery' ); ?></option>
                </select>
            </div>
            
            <!-- Show Logic Toggle (conditional) -->
            <div class="pfg-form-row pfg-conditional" data-depends="pfg_settings[multi_level_filters]">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Show Logic Toggle', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Show OR/AND toggle buttons so visitors can switch logic mode', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <label class="pfg-toggle">
                    <input type="checkbox" name="pfg_settings[show_logic_toggle]" value="1" <?php checked( $settings['show_logic_toggle'] ?? true ); ?> <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>>
                    <span class="pfg-toggle-slider"></span>
                </label>
            </div>
            
            </div><!-- End Filter Settings Group (pfg-conditional) -->
            
            <!-- Search Section -->
            <h4 class="pfg-form-section-title" style="margin-top: 20px;">
                <span class="dashicons dashicons-search" style="margin-right: 5px;"></span>
                <?php esc_html_e( 'Search', 'portfolio-filter-gallery' ); ?>
            </h4>
            
            <!-- Show Search -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Show Search Box', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Search bar to filter items by title', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <label class="pfg-toggle">
                    <input type="checkbox" name="pfg_settings[show_search]" value="1" <?php checked( $settings['search_enabled'] ?? false ); ?>>
                    <span class="pfg-toggle-slider"></span>
                </label>
            </div>
            
            <!-- Search Placeholder Text (conditional) -->
            <div class="pfg-form-row pfg-conditional" data-depends="pfg_settings[show_search]">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Search Placeholder', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Text shown in the search box before user types', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <input type="text" name="pfg_settings[search_placeholder]" class="pfg-input" value="<?php echo esc_attr( $settings['search_placeholder'] ?? 'Search...' ); ?>" placeholder="<?php esc_attr_e( 'Search...', 'portfolio-filter-gallery' ); ?>">
            </div>
            
        </div>

        <!-- Lightbox Tab -->
        <div id="pfg-tab-lightbox" class="pfg-tab-content">
            
            <!-- Lightbox Settings Section -->
            <h4 class="pfg-form-section-title" style="margin-top: 0;">
                <span class="dashicons dashicons-format-image" style="margin-right: 5px;"></span>
                <?php esc_html_e( 'Lightbox Settings', 'portfolio-filter-gallery' ); ?>
            </h4>
            
            <!-- Enable Lightbox -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Enable Lightbox', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Open images in a popup overlay when clicked', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <label class="pfg-toggle">
                    <input type="checkbox" name="pfg_settings[enable_lightbox]" value="1" <?php checked( ( $settings['lightbox'] ?? 'built-in' ) !== 'none' ); ?>>
                    <span class="pfg-toggle-slider"></span>
                </label>
            </div>
            
            <!-- Lightbox Content Options (depends on enable_lightbox) -->
            <div class="pfg-conditional" data-depends="pfg_settings[enable_lightbox]">
                
                <!-- Lightbox Content Section -->
                <h4 class="pfg-form-section-title" style="margin-top: 20px;">
                    <span class="dashicons dashicons-text" style="margin-right: 5px;"></span>
                    <?php esc_html_e( 'Lightbox Content', 'portfolio-filter-gallery' ); ?>
                </h4>
                
                <!-- Show Title -->
                <div class="pfg-form-row">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Show Title in Lightbox', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'Display image title in the lightbox popup', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <label class="pfg-toggle">
                        <input type="checkbox" name="pfg_settings[lightbox_title]" value="1" <?php checked( $settings['lightbox_title'] ?? true ); ?>>
                        <span class="pfg-toggle-slider"></span>
                    </label>
                </div>
                
                <!-- Show Description -->
                <div class="pfg-form-row">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Show Description in Lightbox', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'Display image description/caption in the lightbox popup', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <label class="pfg-toggle">
                        <input type="checkbox" name="pfg_settings[lightbox_description]" value="1" <?php checked( $settings['lightbox_description'] ?? false ); ?>>
                        <span class="pfg-toggle-slider"></span>
                    </label>
                </div>
                
            </div>
            
        </div>

        <!-- Styling Tab -->
        <div id="pfg-tab-style" class="pfg-tab-content">
            
            <!-- Caption Colors Section -->
            <h4 class="pfg-form-section-title" style="margin-top: 0;">
                <span class="dashicons dashicons-admin-customizer" style="margin-right: 5px;"></span>
                <?php esc_html_e( 'Caption Colors', 'portfolio-filter-gallery' ); ?>
            </h4>
            
            <!-- Caption Background Color -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Caption Background', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Background color for title below image', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <input type="text" name="pfg_settings[caption_bg_color]" class="pfg-color-input" value="<?php echo esc_attr( $settings['caption_bg_color'] ?? '#ffffff' ); ?>">
            </div>
            
            <!-- Caption Text Color -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Caption Text Color', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Text color for title below image', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <input type="text" name="pfg_settings[caption_text_color]" class="pfg-color-input" value="<?php echo esc_attr( $settings['caption_text_color'] ?? '#1e293b' ); ?>">
            </div>
            
            <!-- Overlay Colors Section -->
            <h4 class="pfg-form-section-title" style="margin-top: 20px;">
                <span class="dashicons dashicons-visibility" style="margin-right: 5px;"></span>
                <?php esc_html_e( 'Overlay Colors', 'portfolio-filter-gallery' ); ?>
            </h4>
            
            <!-- Overlay Color -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Overlay Color', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Color of overlay on hover effect', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <input type="text" name="pfg_settings[overlay_color]" class="pfg-color-input" value="<?php echo esc_attr( $settings['overlay_color'] ?? '#000000' ); ?>">
            </div>
            
            <!-- Overlay Opacity -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Overlay Opacity', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Transparency level of the overlay', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <div class="pfg-range">
                    <input type="range" name="pfg_settings[overlay_opacity]" min="0" max="100" value="<?php echo esc_attr( $settings['overlay_opacity'] ?? 70 ); ?>" data-suffix="%">
                    <span class="pfg-range-value"><?php echo esc_html( $settings['overlay_opacity'] ?? 70 ); ?>%</span>
                </div>
            </div>
            
            <!-- Filter Colors Section -->
            <h4 class="pfg-form-section-title" style="margin-top: 20px;">
                <span class="dashicons dashicons-filter" style="margin-right: 5px;"></span>
                <?php esc_html_e( 'Filter Button Colors', 'portfolio-filter-gallery' ); ?>
            </h4>
            
            <!-- Primary Color -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Primary Color', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Inactive filter buttons, category badges, and other accent elements', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <input type="text" name="pfg_settings[primary_color]" class="pfg-color-input" value="<?php echo esc_attr( $settings['primary_color'] ?? '#94a3b8' ); ?>">
            </div>
            
            <!-- Filter Active Color -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Filter Active Color', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Currently selected filter button color', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <input type="text" name="pfg_settings[filter_active_color]" class="pfg-color-input" value="<?php echo esc_attr( $settings['filter_active_color'] ?? '#3858e9' ); ?>">
            </div>
            
            <!-- Filter Text Color -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Filter Text Color', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Inactive filter button text (auto = contrast-based)', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <input type="text" name="pfg_settings[filter_text_color]" class="pfg-color-input pfg-color-auto" value="<?php echo esc_attr( $settings['filter_text_color'] ?? 'auto' ); ?>" placeholder="auto">
            </div>
            
            <!-- Filter Active Text Color -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Filter Active Text Color', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Active filter button text (auto = contrast-based)', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <input type="text" name="pfg_settings[filter_active_text_color]" class="pfg-color-input pfg-color-auto" value="<?php echo esc_attr( $settings['filter_active_text_color'] ?? 'auto' ); ?>" placeholder="auto">
            </div>
            
        </div>

        <!-- Advanced Tab -->
        <div id="pfg-tab-advanced" class="pfg-tab-content">
            
            <!-- Pagination Section -->
            <h4 class="pfg-form-section-title" style="margin-top: 0;">
                <span class="dashicons dashicons-editor-ol" style="margin-right: 5px;"></span>
                <?php esc_html_e( 'Pagination', 'portfolio-filter-gallery' ); ?>
                <?php if ( ! pfg_is_premium() ) : ?><span class="pfg-pro-badge">PRO</span><?php endif; ?>
            </h4>
            
            <?php if ( ! pfg_is_premium() ) : ?>
            <div class="pfg-upsell-banner pfg-upsell-inline">
                <span class="pfg-pro-badge">PRO</span>
                <span><?php esc_html_e( 'Pagination features are available in the Premium version.', 'portfolio-filter-gallery' ); ?></span>
                <a href="<?php echo esc_url( PFG_Features::get_upgrade_url( 'pagination' ) ); ?>" target="_blank" class="pfg-upsell-link"><?php esc_html_e( 'Upgrade', 'portfolio-filter-gallery' ); ?> →</a>
            </div>
            <?php endif; ?>
            
            <div class="<?php echo ! pfg_is_premium() ? 'pfg-premium-section pfg-locked' : ''; ?>">
                <div class="pfg-form-row">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Enable Pagination', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'Split gallery into multiple pages', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <label class="pfg-toggle">
                        <input type="checkbox" name="pfg_settings[pagination_enabled]" value="1" <?php checked( $settings['pagination_enabled'] ?? false ); ?> <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>>
                        <span class="pfg-toggle-slider"></span>
                    </label>
                </div>
                
                <!-- Pagination Type -->
                <div class="pfg-form-row">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Pagination Type', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'How to navigate between pages', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <select name="pfg_settings[pagination_type]" class="pfg-select" <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>>
                        <option value="numbered" <?php selected( $settings['pagination_type'] ?? 'numbered', 'numbered' ); ?>><?php esc_html_e( 'Numbered', 'portfolio-filter-gallery' ); ?></option>
                        <option value="load_more" <?php selected( $settings['pagination_type'] ?? 'numbered', 'load_more' ); ?>><?php esc_html_e( 'Load More Button', 'portfolio-filter-gallery' ); ?></option>
                        <option value="infinite" <?php selected( $settings['pagination_type'] ?? 'numbered', 'infinite' ); ?>><?php esc_html_e( 'Infinite Scroll', 'portfolio-filter-gallery' ); ?></option>
                    </select>
                </div>
                
                <!-- Items Per Page -->
                <div class="pfg-form-row">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Items Per Page', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'Number of items to show per page', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <input type="number" name="pfg_settings[items_per_page]" class="pfg-input" min="1" max="100" value="<?php echo esc_attr( $settings['items_per_page'] ?? 12 ); ?>" <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>>
                </div>
            </div>
            
            <!-- URL & Deep Linking Section -->
            <h4 class="pfg-form-section-title" style="margin-top: 20px;">
                <span class="dashicons dashicons-admin-links" style="margin-right: 5px;"></span>
                <?php esc_html_e( 'URL & Deep Linking', 'portfolio-filter-gallery' ); ?>
                <?php if ( ! pfg_is_premium() ) : ?><span class="pfg-pro-badge">PRO</span><?php endif; ?>
            </h4>
            
            <!-- Deep Linking (Premium) -->
            <div class="<?php echo ! pfg_is_premium() ? 'pfg-premium-section pfg-locked' : ''; ?>">
                <div class="pfg-form-row">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Deep Linking', 'portfolio-filter-gallery' ); ?>
                        <?php if ( ! pfg_is_premium() ) : ?><span class="pfg-pro-badge">PRO</span><?php endif; ?>
                        <small><?php esc_html_e( 'Add filter state to URL for sharing/bookmarking', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <label class="pfg-toggle">
                        <input type="checkbox" name="pfg_settings[deep_linking]" value="1" <?php checked( $settings['deep_linking'] ?? false ); ?> <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>>
                        <span class="pfg-toggle-slider"></span>
                    </label>
                </div>
                
                <!-- URL Parameter Name -->
                <div class="pfg-form-row">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'URL Parameter Name', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'e.g., ?filter=portraits or ?category=portraits', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <input type="text" name="pfg_settings[url_param_name]" class="pfg-input" style="width: 150px;" value="<?php echo esc_attr( $settings['url_param_name'] ?? 'filter' ); ?>" placeholder="filter" <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>>
                </div>
            </div>
            
            <!-- Display & Behavior Section -->
            <h4 class="pfg-form-section-title" style="margin-top: 20px;">
                <span class="dashicons dashicons-visibility" style="margin-right: 5px;"></span>
                <?php esc_html_e( 'Display & Behavior', 'portfolio-filter-gallery' ); ?>
            </h4>
            
            <!-- Sort Order -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Sort Order', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Default display order for gallery images', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <select name="pfg_settings[sort_order]" class="pfg-select">
                    <option value="custom" <?php selected( $settings['sort_order'] ?? 'custom', 'custom' ); ?>><?php esc_html_e( 'Custom (Manual Order)', 'portfolio-filter-gallery' ); ?></option>
                    <option value="date_newest" <?php selected( $settings['sort_order'] ?? 'custom', 'date_newest' ); ?>><?php esc_html_e( 'Newest First', 'portfolio-filter-gallery' ); ?></option>
                    <option value="date_oldest" <?php selected( $settings['sort_order'] ?? 'custom', 'date_oldest' ); ?>><?php esc_html_e( 'Oldest First', 'portfolio-filter-gallery' ); ?></option>
                    <option value="title_asc" <?php selected( $settings['sort_order'] ?? 'custom', 'title_asc' ); ?>><?php esc_html_e( 'Title A → Z', 'portfolio-filter-gallery' ); ?></option>
                    <option value="title_desc" <?php selected( $settings['sort_order'] ?? 'custom', 'title_desc' ); ?>><?php esc_html_e( 'Title Z → A', 'portfolio-filter-gallery' ); ?></option>
                    <option value="random" <?php selected( $settings['sort_order'] ?? 'custom', 'random' ); ?>><?php esc_html_e( 'Random / Shuffle', 'portfolio-filter-gallery' ); ?></option>
                </select>
            </div>
            
            <!-- Hide Type Icons -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Hide Type Icons', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Hide video/link indicator icons on gallery items', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <label class="pfg-toggle">
                    <input type="checkbox" name="pfg_settings[hide_type_icons]" value="1" <?php checked( $settings['hide_type_icons'] ?? false ); ?>>
                    <span class="pfg-toggle-slider"></span>
                </label>
            </div>
            
            <!-- Default Filter -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Default Filter', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Pre-select a filter on page load', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <select name="pfg_settings[default_filter]" class="pfg-select">
                    <option value=""><?php esc_html_e( '-- Show All --', 'portfolio-filter-gallery' ); ?></option>
                    <?php
                    // Get filters that are actually used by images in this gallery
                    $images = $gallery->get_images();
                    $used_filter_ids = array();
                    foreach ( $images as $image ) {
                        if ( ! empty( $image['filters'] ) ) {
                            $used_filter_ids = array_merge( $used_filter_ids, (array) $image['filters'] );
                        }
                    }
                    $used_filter_ids = array_unique( $used_filter_ids );
                    
                    // Get filter details for the used filters
                    $all_filters = get_option( 'pfg_filters', array() );
                    $used_filters = array();
                    foreach ( $all_filters as $filter ) {
                        // Check if this filter is used by any image (by ID or slug)
                        if ( in_array( $filter['id'], $used_filter_ids, true ) || in_array( $filter['slug'], $used_filter_ids, true ) ) {
                            $used_filters[] = $filter;
                        }
                    }
                    
                    foreach ( $used_filters as $filter ) :
                        $is_child = ! empty( $filter['parent'] );
                        $prefix   = $is_child ? '— ' : '';
                    ?>
                        <option value="<?php echo esc_attr( $filter['slug'] ); ?>" <?php selected( $settings['default_filter'] ?? '', $filter['slug'] ); ?>><?php echo esc_html( $prefix . $filter['name'] ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            

            
            <!-- Gallery Direction -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Gallery Direction', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Text and layout direction', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <select name="pfg_settings[direction]" class="pfg-select">
                    <option value="ltr" <?php selected( $settings['direction'] ?? 'ltr', 'ltr' ); ?>><?php esc_html_e( 'Left to Right (LTR)', 'portfolio-filter-gallery' ); ?></option>
                    <option value="rtl" <?php selected( $settings['direction'] ?? 'ltr', 'rtl' ); ?>><?php esc_html_e( 'Right to Left (RTL)', 'portfolio-filter-gallery' ); ?></option>
                </select>
            </div>
            
            <!-- Link URL Target -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Open Link URL in', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'How external links open (when image has a custom URL)', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <select name="pfg_settings[url_target]" class="pfg-select">
                    <option value="_self" <?php selected( $settings['url_target'] ?? '_self', '_self' ); ?>><?php esc_html_e( 'Same Window', 'portfolio-filter-gallery' ); ?></option>
                    <option value="_blank" <?php selected( $settings['url_target'] ?? '_self', '_blank' ); ?>><?php esc_html_e( 'New Tab/Window', 'portfolio-filter-gallery' ); ?></option>
                </select>
            </div>
            
            <!-- Performance Section -->
            <h4 class="pfg-form-section-title" style="margin-top: 20px;">
                <span class="dashicons dashicons-performance" style="margin-right: 5px;"></span>
                <?php esc_html_e( 'Performance', 'portfolio-filter-gallery' ); ?>
            </h4>
            
            <!-- Lazy Loading -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Lazy Loading', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Load images as they come into view', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <label class="pfg-toggle">
                    <input type="checkbox" name="pfg_settings[lazy_loading]" value="1" <?php checked( $settings['lazy_loading'] ?? true ); ?>>
                    <span class="pfg-toggle-slider"></span>
                </label>
            </div>
            
            <!-- Gallery Preloader -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Show Loading Spinner', 'portfolio-filter-gallery' ); ?>
                    <small><?php esc_html_e( 'Show spinner while gallery images load', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <label class="pfg-toggle">
                    <input type="checkbox" name="pfg_settings[show_preloader]" value="1" <?php checked( $settings['show_preloader'] ?? true ); ?>>
                    <span class="pfg-toggle-slider"></span>
                </label>
            </div>
            
            <!-- Analytics (Premium) -->
            <div class="pfg-form-row">
                <label class="pfg-form-label">
                    <?php esc_html_e( 'Analytics Tracking', 'portfolio-filter-gallery' ); ?>
                    <?php if ( ! pfg_is_premium() ) : ?><span class="pfg-pro-badge">PRO</span><?php endif; ?>
                    <small><?php esc_html_e( 'Track views, clicks, and filter usage', 'portfolio-filter-gallery' ); ?></small>
                </label>
                <label class="pfg-toggle">
                    <input type="checkbox" name="pfg_settings[analytics_enabled]" value="1" <?php checked( $settings['analytics_enabled'] ?? false ); ?> <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>>
                    <span class="pfg-toggle-slider"></span>
                </label>
            </div>
            
            <hr class="pfg-form-separator">
            
            <!-- Watermark Section (Premium) -->
            <h4 class="pfg-form-section-title pfg-section-icon">
                <span class="dashicons dashicons-shield"></span>
                <?php esc_html_e( 'Watermark', 'portfolio-filter-gallery' ); ?>
                <?php if ( ! pfg_is_premium() ) : ?><span class="pfg-pro-badge">PRO</span><?php endif; ?>
            </h4>
            
            <div class="<?php echo ! pfg_is_premium() ? 'pfg-premium-section pfg-locked' : ''; ?>">
                <!-- Enable Watermark -->
                <div class="pfg-form-row">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Enable Watermark', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'Add text or image watermark to gallery images', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <label class="pfg-toggle">
                        <input type="checkbox" name="pfg_settings[watermark_enabled]" value="1" <?php checked( $settings['watermark_enabled'] ?? false ); ?> <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?> id="pfg-watermark-enabled">
                        <span class="pfg-toggle-slider"></span>
                    </label>
                </div>
                
                <!-- Watermark Type -->
                <div class="pfg-form-row pfg-watermark-options" style="<?php echo empty( $settings['watermark_enabled'] ) ? 'display:none;' : ''; ?>">
                    <label class="pfg-form-label"><?php esc_html_e( 'Watermark Type', 'portfolio-filter-gallery' ); ?></label>
                    <select name="pfg_settings[watermark_type]" class="pfg-select" id="pfg-watermark-type" <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>>
                        <option value="text" <?php selected( $settings['watermark_type'] ?? 'text', 'text' ); ?>><?php esc_html_e( 'Text', 'portfolio-filter-gallery' ); ?></option>
                        <option value="image" <?php selected( $settings['watermark_type'] ?? 'text', 'image' ); ?>><?php esc_html_e( 'Image', 'portfolio-filter-gallery' ); ?></option>
                    </select>
                </div>
                
                <!-- Watermark Text -->
                <div class="pfg-form-row pfg-watermark-options pfg-watermark-text-options" style="<?php echo ( empty( $settings['watermark_enabled'] ) || ( $settings['watermark_type'] ?? 'text' ) !== 'text' ) ? 'display:none;' : ''; ?>">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Watermark Text', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'e.g., © Your Name', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <input type="text" name="pfg_settings[watermark_text]" class="pfg-input" value="<?php echo esc_attr( $settings['watermark_text'] ?? '' ); ?>" placeholder="© Your Name" <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>>
                </div>
                
                <!-- Watermark Image -->
                <div class="pfg-form-row pfg-watermark-options pfg-watermark-image-options" style="<?php echo ( empty( $settings['watermark_enabled'] ) || ( $settings['watermark_type'] ?? 'text' ) !== 'image' ) ? 'display:none;' : ''; ?>">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Watermark Image', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'Upload a transparent PNG logo', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" name="pfg_settings[watermark_image]" class="pfg-input" id="pfg-watermark-image-url" value="<?php echo esc_attr( $settings['watermark_image'] ?? '' ); ?>" placeholder="Image URL" <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>>
                        <button type="button" class="button" id="pfg-upload-watermark" <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>><?php esc_html_e( 'Upload', 'portfolio-filter-gallery' ); ?></button>
                    </div>
                </div>
                
                <!-- Watermark Position -->
                <div class="pfg-form-row pfg-watermark-options" style="<?php echo empty( $settings['watermark_enabled'] ) ? 'display:none;' : ''; ?>">
                    <label class="pfg-form-label"><?php esc_html_e( 'Watermark Position', 'portfolio-filter-gallery' ); ?></label>
                    <select name="pfg_settings[watermark_position]" class="pfg-select" <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>>
                        <option value="top-left" <?php selected( $settings['watermark_position'] ?? 'bottom-right', 'top-left' ); ?>><?php esc_html_e( 'Top Left', 'portfolio-filter-gallery' ); ?></option>
                        <option value="top-center" <?php selected( $settings['watermark_position'] ?? 'bottom-right', 'top-center' ); ?>><?php esc_html_e( 'Top Center', 'portfolio-filter-gallery' ); ?></option>
                        <option value="top-right" <?php selected( $settings['watermark_position'] ?? 'bottom-right', 'top-right' ); ?>><?php esc_html_e( 'Top Right', 'portfolio-filter-gallery' ); ?></option>
                        <option value="center" <?php selected( $settings['watermark_position'] ?? 'bottom-right', 'center' ); ?>><?php esc_html_e( 'Center', 'portfolio-filter-gallery' ); ?></option>
                        <option value="bottom-left" <?php selected( $settings['watermark_position'] ?? 'bottom-right', 'bottom-left' ); ?>><?php esc_html_e( 'Bottom Left', 'portfolio-filter-gallery' ); ?></option>
                        <option value="bottom-center" <?php selected( $settings['watermark_position'] ?? 'bottom-right', 'bottom-center' ); ?>><?php esc_html_e( 'Bottom Center', 'portfolio-filter-gallery' ); ?></option>
                        <option value="bottom-right" <?php selected( $settings['watermark_position'] ?? 'bottom-right', 'bottom-right' ); ?>><?php esc_html_e( 'Bottom Right', 'portfolio-filter-gallery' ); ?></option>
                    </select>
                </div>
                
                <!-- Watermark Opacity -->
                <div class="pfg-form-row pfg-watermark-options" style="<?php echo empty( $settings['watermark_enabled'] ) ? 'display:none;' : ''; ?>">
                    <label class="pfg-form-label"><?php esc_html_e( 'Watermark Opacity', 'portfolio-filter-gallery' ); ?></label>
                    <div class="pfg-range">
                        <input type="range" name="pfg_settings[watermark_opacity]" min="10" max="100" value="<?php echo esc_attr( $settings['watermark_opacity'] ?? 50 ); ?>" data-suffix="%" <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>>
                        <span class="pfg-range-value"><?php echo esc_html( $settings['watermark_opacity'] ?? 50 ); ?>%</span>
                    </div>
                </div>
                
                <!-- Text Watermark Size -->
                <div class="pfg-form-row pfg-watermark-options pfg-watermark-text-options" style="<?php echo ( empty( $settings['watermark_enabled'] ) || ( $settings['watermark_type'] ?? 'text' ) !== 'text' ) ? 'display:none;' : ''; ?>">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Text Size', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'Font size in pixels', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <div class="pfg-range">
                        <input type="range" name="pfg_settings[watermark_size]" min="12" max="72" value="<?php echo esc_attr( $settings['watermark_size'] ?? 24 ); ?>" data-suffix="px" <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>>
                        <span class="pfg-range-value"><?php echo esc_html( $settings['watermark_size'] ?? 24 ); ?>px</span>
                    </div>
                </div>
                
                <!-- Image Watermark Size -->
                <div class="pfg-form-row pfg-watermark-options pfg-watermark-image-options" style="<?php echo ( empty( $settings['watermark_enabled'] ) || ( $settings['watermark_type'] ?? 'text' ) !== 'image' ) ? 'display:none;' : ''; ?>">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Logo Size', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'Percentage of image width', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <div class="pfg-range">
                        <input type="range" name="pfg_settings[watermark_image_size]" min="5" max="50" value="<?php echo esc_attr( $settings['watermark_image_size'] ?? 15 ); ?>" data-suffix="%" <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>>
                        <span class="pfg-range-value"><?php echo esc_html( $settings['watermark_image_size'] ?? 15 ); ?>%</span>
                    </div>
                </div>
            </div>
            
        </div>

        <?php if ( PFG_WooCommerce::is_active() ) : ?>
        <!-- WooCommerce Tab -->
        <div id="pfg-tab-woocommerce" class="pfg-tab-content">
            
            <?php if ( ! PFG_Features::is_premium() ) : ?>
            <div class="pfg-upsell-banner">
                <div class="pfg-upsell-content">
                    <h3><?php esc_html_e( '🛒 WooCommerce Product Gallery', 'portfolio-filter-gallery' ); ?></h3>
                    <p><?php esc_html_e( 'Display WooCommerce products as beautiful gallery items with price, sale badges, and category filtering.', 'portfolio-filter-gallery' ); ?></p>
                </div>
                <a href="<?php echo esc_url( PFG_Features::get_upgrade_url( 'woocommerce-tab' ) ); ?>" target="_blank" class="pfg-upsell-btn">
                    <?php esc_html_e( 'Upgrade to Premium', 'portfolio-filter-gallery' ); ?>
                </a>
            </div>
            <?php endif; ?>
            
            <div class="<?php echo ! PFG_Features::is_premium() ? 'pfg-premium-section pfg-locked' : ''; ?>">
                
                <!-- Category Selection -->
                <div class="pfg-form-row">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Product Categories', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'Select categories or leave empty for all', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <select name="pfg_settings[woo_categories][]" multiple="multiple" class="pfg-select" style="min-height: 150px;" <?php echo ! PFG_Features::is_premium() ? 'disabled' : ''; ?>>
                        <?php 
                        $woo_categories = PFG_WooCommerce::get_categories_for_select();
                        $selected_cats = $settings['woo_categories'] ?? array();
                        foreach ( $woo_categories as $cat_id => $cat_name ) : ?>
                            <option value="<?php echo esc_attr( $cat_id ); ?>" <?php echo in_array( $cat_id, $selected_cats ) ? 'selected' : ''; ?>>
                                <?php echo esc_html( $cat_name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Product Display Options -->
                <div class="pfg-form-row">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Display Options', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'What to show on product items', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <div>
                        <label style="display: block; margin-bottom: 10px;">
                            <input type="checkbox" name="pfg_settings[woo_show_price]" value="1" <?php checked( $settings['woo_show_price'] ?? true ); ?> <?php echo ! PFG_Features::is_premium() ? 'disabled' : ''; ?>>
                            <?php esc_html_e( 'Show Price', 'portfolio-filter-gallery' ); ?>
                        </label>
                        <label style="display: block; margin-bottom: 10px;">
                            <input type="checkbox" name="pfg_settings[woo_show_sale_badge]" value="1" <?php checked( $settings['woo_show_sale_badge'] ?? true ); ?> <?php echo ! PFG_Features::is_premium() ? 'disabled' : ''; ?>>
                            <?php esc_html_e( 'Show Sale Badge', 'portfolio-filter-gallery' ); ?>
                        </label>
                        <label style="display: block; margin-bottom: 10px;">
                            <input type="checkbox" name="pfg_settings[woo_show_title]" value="1" <?php checked( $settings['woo_show_title'] ?? true ); ?> <?php echo ! PFG_Features::is_premium() ? 'disabled' : ''; ?>>
                            <?php esc_html_e( 'Show Product Title', 'portfolio-filter-gallery' ); ?>
                        </label>
                    </div>
                </div>
                
                <!-- Product Order -->
                <div class="pfg-form-row">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Product Order', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'How to sort products', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <div style="display: flex; gap: 10px;">
                        <select name="pfg_settings[woo_orderby]" class="pfg-select" <?php echo ! PFG_Features::is_premium() ? 'disabled' : ''; ?>>
                            <option value="date" <?php selected( $settings['woo_orderby'] ?? 'date', 'date' ); ?>><?php esc_html_e( 'Date', 'portfolio-filter-gallery' ); ?></option>
                            <option value="title" <?php selected( $settings['woo_orderby'] ?? 'date', 'title' ); ?>><?php esc_html_e( 'Title', 'portfolio-filter-gallery' ); ?></option>
                            <option value="price" <?php selected( $settings['woo_orderby'] ?? 'date', 'price' ); ?>><?php esc_html_e( 'Price', 'portfolio-filter-gallery' ); ?></option>
                            <option value="popularity" <?php selected( $settings['woo_orderby'] ?? 'date', 'popularity' ); ?>><?php esc_html_e( 'Popularity', 'portfolio-filter-gallery' ); ?></option>
                            <option value="rating" <?php selected( $settings['woo_orderby'] ?? 'date', 'rating' ); ?>><?php esc_html_e( 'Rating', 'portfolio-filter-gallery' ); ?></option>
                            <option value="rand" <?php selected( $settings['woo_orderby'] ?? 'date', 'rand' ); ?>><?php esc_html_e( 'Random', 'portfolio-filter-gallery' ); ?></option>
                        </select>
                        <select name="pfg_settings[woo_order]" class="pfg-select" <?php echo ! PFG_Features::is_premium() ? 'disabled' : ''; ?>>
                            <option value="desc" <?php selected( $settings['woo_order'] ?? 'desc', 'desc' ); ?>><?php esc_html_e( 'Descending', 'portfolio-filter-gallery' ); ?></option>
                            <option value="asc" <?php selected( $settings['woo_order'] ?? 'desc', 'asc' ); ?>><?php esc_html_e( 'Ascending', 'portfolio-filter-gallery' ); ?></option>
                        </select>
                    </div>
                </div>
                
                <!-- Product Limit -->
                <div class="pfg-form-row">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Product Limit', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'Maximum products to display (-1 for all)', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <input type="number" name="pfg_settings[woo_limit]" value="<?php echo esc_attr( $settings['woo_limit'] ?? -1 ); ?>" class="pfg-input" style="max-width: 100px;" min="-1" <?php echo ! PFG_Features::is_premium() ? 'disabled' : ''; ?>>
                </div>
                
                <!-- Link Target -->
                <div class="pfg-form-row">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Product Link', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'How to open product page', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <select name="pfg_settings[woo_link_target]" class="pfg-select" <?php echo ! PFG_Features::is_premium() ? 'disabled' : ''; ?>>
                        <option value="_self" <?php selected( $settings['woo_link_target'] ?? '_self', '_self' ); ?>><?php esc_html_e( 'Same Window', 'portfolio-filter-gallery' ); ?></option>
                        <option value="_blank" <?php selected( $settings['woo_link_target'] ?? '_self', '_blank' ); ?>><?php esc_html_e( 'New Tab', 'portfolio-filter-gallery' ); ?></option>
                    </select>
                </div>
                
            </div>
        </div>
        <?php endif; ?>
        
    </div>
    
</div>

<style>
/* Select field sizing */
.meta-box-sortables select {
    max-width: 20%;
}

/* Template Grid Styles */
.pfg-template-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 12px;
    margin-top: 10px;
}
.pfg-template-card {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 15px 10px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
}
.pfg-template-card:hover {
    border-color: #94a3b8;
    background: #fff;
}
.pfg-template-card.selected {
    border-color: #3858e9;
    background: #eff6ff;
    box-shadow: 0 0 0 3px rgba(56, 88, 233, 0.15);
}
.pfg-template-preview {
    width: 60px;
    height: 60px;
    margin: 0 auto 10px;
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.pfg-template-preview .dashicons {
    font-size: 28px;
    width: 28px;
    height: 28px;
    color: #3b82f6;
}
.pfg-template-card.selected .pfg-template-preview {
    background: linear-gradient(135deg, #3858e9 0%, #1e40af 100%);
}
.pfg-template-card.selected .pfg-template-preview .dashicons {
    color: #fff;
}
.pfg-template-name {
    display: block;
    font-size: 12px;
    font-weight: 500;
    color: #475569;
}
.pfg-template-card.selected .pfg-template-name {
    color: #1e40af;
}
.pfg-template-type {
    display: block;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 2px 6px;
    border-radius: 3px;
    margin-top: 6px;
}
.pfg-type-grid {
    background: #f1f5f9;
    color: #64748b;
}
.pfg-type-masonry {
    background: #dbeafe;
    color: #2563eb;
}
.pfg-type-justified {
    background: #dcfce7;
    color: #16a34a;
}
.pfg-type-packed {
    background: #fef3c7;
    color: #d97706;
}

/* Locked Template Cards (PRO) */
.pfg-template-card.pfg-template-locked {
    position: relative;
    opacity: 0.7;
    cursor: not-allowed;
}
.pfg-template-card.pfg-template-locked:hover {
    transform: none;
    border-color: #e2e8f0;
}
.pfg-card-pro-badge {
    position: absolute;
    top: 6px;
    right: 6px;
    padding: 2px 6px;
    background: #f59e0b;
    color: #fff;
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border-radius: 3px;
    z-index: 5;
}

.pfg-template-notice {
    margin-top: 10px;
    padding: 8px 14px;
    background: #e0f2fe;
    border: 1px solid #7dd3fc;
    border-radius: 6px;
    color: #0369a1;
    font-size: 12px;
    text-align: center;
    animation: pfgFadeIn 0.3s ease;
}
@keyframes pfgFadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
/* Section Separator and Title */
.pfg-form-separator {
    border: none;
    border-top: 1px solid #e2e8f0;
    margin: 25px 0 20px;
}
.pfg-form-section-title {
    margin: 0 0 15px;
    padding: 0;
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    letter-spacing: -0.01em;
}
.pfg-form-section-title.pfg-section-icon {
    display: flex;
    align-items: center;
    gap: 8px;
}
.pfg-form-section-title .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
    color: #3858e9;
}

/* PRO Badges */
.pfg-pro-badge {
    display: inline-block;
    padding: 2px 6px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #fff;
    border-radius: 3px;
    margin-left: 6px;
    vertical-align: middle;
}
.pfg-tab .pfg-pro-badge {
    font-size: 8px;
    padding: 1px 4px;
    margin-left: 4px;
}

/* Locked Premium Section */
.pfg-premium-section.pfg-locked {
    position: relative;
    opacity: 0.6;
    pointer-events: none;
}
.pfg-premium-section.pfg-locked::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: repeating-linear-gradient(
        45deg,
        transparent,
        transparent 10px,
        rgba(0,0,0,0.02) 10px,
        rgba(0,0,0,0.02) 20px
    );
    pointer-events: none;
}

/* Upsell Banner */
.pfg-upsell-banner {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 20px;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: 1px solid #fbbf24;
    border-radius: 8px;
    margin-bottom: 20px;
}
.pfg-upsell-banner.pfg-upsell-inline {
    padding: 10px 15px;
    margin-bottom: 15px;
    font-size: 13px;
}
.pfg-upsell-banner h3 {
    margin: 0 0 5px 0;
    font-size: 15px;
    color: #92400e;
}
.pfg-upsell-banner p {
    margin: 0;
    font-size: 13px;
    color: #78350f;
}
.pfg-upsell-content {
    flex: 1;
}
.pfg-upsell-btn {
    display: inline-block;
    padding: 8px 16px;
    background: #d97706;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 500;
    font-size: 13px;
    transition: background 0.2s;
}
.pfg-upsell-btn:hover {
    background: #b45309;
    color: #fff;
}
.pfg-upsell-link {
    color: #92400e;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
}
.pfg-upsell-link:hover {
    color: #78350f;
    text-decoration: underline;
}
.pfg-upsell-icon {
    display: inline-flex;
    gap: 2px;
    margin-right: 8px;
    color: #92400e;
}
.pfg-upsell-icon .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}
/* Device Type Responsive Columns */
.pfg-responsive-columns {
    display: flex;
    align-items: center;
    gap: 15px;
}
.pfg-device-toggle {
    display: inline-flex;
    background: #f1f5f9;
    border-radius: 8px;
    padding: 4px;
    gap: 2px;
}
.pfg-device-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 32px;
    border: 2px solid transparent;
    border-radius: 6px;
    background: transparent;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #64748b;
    position: relative;
}
.pfg-device-btn:hover {
    background: #e2e8f0;
    color: #475569;
}
.pfg-device-btn.active {
    background: #fff;
    border-color: #3858e9;
    color: #3858e9;
    box-shadow: 0 1px 3px rgba(56, 88, 233, 0.2);
}
.pfg-device-btn .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}
.pfg-device-panel {
    display: none;
    flex: 1;
}
.pfg-device-panel.active {
    display: block;
}
.pfg-device-panel .pfg-range {
    min-width: 200px;
}

/* PRO Badge styles */
.pfg-device-pro-badge {
    position: absolute;
    top: -4px;
    right: -6px;
    background: #b45309;
    color: #fff;
    font-size: 8px;
    font-weight: 700;
    padding: 1px 3px;
    border-radius: 4px;
    line-height: 1;
}
.pfg-device-btn.pfg-device-pro {
    opacity: 0.8;
}
.pfg-device-btn.pfg-device-pro:hover {
    cursor: not-allowed;
    background: #fef3c7;
    color: #b45309;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Template settings data for slider syncing
    var templateSettings = <?php 
        $template_data = array();
        foreach ( $templates as $id => $template ) {
            $template_data[ $id ] = array(
                'columns'              => isset( $template['settings']['columns_lg'] ) ? $template['settings']['columns_lg'] : 3,
                'columns_md'           => isset( $template['settings']['columns_md'] ) ? $template['settings']['columns_md'] : 2,
                'columns_sm'           => isset( $template['settings']['columns_sm'] ) ? $template['settings']['columns_sm'] : 1,
                'gap'                  => isset( $template['settings']['gap'] ) ? $template['settings']['gap'] : 20,
                'border_radius'        => isset( $template['settings']['border_radius'] ) ? $template['settings']['border_radius'] : 0,
                'hover_effect'         => isset( $template['settings']['hover_effect'] ) ? $template['settings']['hover_effect'] : 'fade',
                'show_title'           => isset( $template['settings']['show_title'] ) ? (bool) $template['settings']['show_title'] : false,
                'title_position'       => isset( $template['settings']['title_position'] ) ? $template['settings']['title_position'] : 'overlay',
                'show_categories'      => isset( $template['settings']['show_categories'] ) ? (bool) $template['settings']['show_categories'] : false,
                'justified_row_height' => isset( $template['settings']['justified_row_height'] ) ? $template['settings']['justified_row_height'] : 200,
                'packed_min_size'      => isset( $template['settings']['packed_min_size'] ) ? $template['settings']['packed_min_size'] : 150,
            );
        }
        echo wp_json_encode( $template_data );
    ?>;
    
    // Flag to track if layout change is from template click (prevents deselection)
    var isTemplateClick = false;
    
    // Template selection with slider syncing
    $('.pfg-template-card').on('click', function() {
        var $this = $(this);
        
        // Prevent selection of locked (PRO) templates
        if ($this.data('locked') === 1 || $this.hasClass('pfg-template-locked')) {
            return false;
        }
        
        var templateId = $this.data('template');
        var layoutType = $this.data('layout') || 'grid';
        
        // Update UI
        $('.pfg-template-card').removeClass('selected');
        $this.addClass('selected');
        
        // Update hidden inputs
        $('#pfg-template').val(templateId);
        
        // Set flag before triggering change to prevent template deselection
        isTemplateClick = true;
        
        // Update Layout Type dropdown to match template
        $('#pfg-layout').val(layoutType).trigger('change');
        
        // Apply template defaults to sliders and inputs
        if (templateSettings[templateId]) {
            var ts = templateSettings[templateId];
            
            // Helper: update a range slider and its display value
            function applySlider(name, value, suffix) {
                var $slider = $('input[name="pfg_settings[' + name + ']"]');
                if ($slider.length) {
                    $slider.val(value).trigger('input');
                    $slider.siblings('.pfg-range-value').text(value + (suffix || ''));
                }
            }
            
            // Apply columns (desktop, tablet, mobile)
            applySlider('columns', ts.columns, '');
            applySlider('columns_md', ts.columns_md, '');
            applySlider('columns_sm', ts.columns_sm, '');
            
            // Apply gap and border radius
            applySlider('gap', ts.gap, 'px');
            applySlider('border_radius', ts.border_radius, 'px');
            
            // Apply layout-specific settings
            if (ts.justified_row_height) {
                applySlider('justified_row_height', ts.justified_row_height, 'px');
            }
            if (ts.packed_min_size) {
                applySlider('packed_min_size', ts.packed_min_size, 'px');
            }
            
            // Apply hover effect
            if (ts.hover_effect) {
                $('select[name="pfg_settings[hover_effect]"]').val(ts.hover_effect);
            }
            
            // Apply show title
            var $showTitle = $('input[name="pfg_settings[show_title]"]');
            if ($showTitle.length) {
                $showTitle.prop('checked', !!ts.show_title);
            }
            
            // Apply title position
            if (ts.title_position) {
                $('select[name="pfg_settings[title_position]"]').val(ts.title_position);
            }
            
            showTemplateNotice('Template applied: ' + $this.find('.pfg-template-name').text());
        }
        
        // Update layout options visibility (without triggering the change handler conflict)
        updateLayoutOptions();
    });
    
    // When Layout Type dropdown changes manually
    $('#pfg-layout').on('change', function() {
        // Skip notification if this change was triggered by template click
        if (isTemplateClick) {
            isTemplateClick = false;
            updateLayoutOptions();
            return;
        }
        
        var selectedLayout = $(this).val();
        var layoutNames = {
            'grid': 'Grid',
            'masonry': 'Masonry', 
            'justified': 'Justified',
            'packed': 'Packed'
        };
        
        // Keep template selected - it's a style preset that works with any layout
        showTemplateNotice('Layout changed to ' + (layoutNames[selectedLayout] || selectedLayout));
        
        updateLayoutOptions();
    });
    
    // Show brief notification when template settings change
    function showTemplateNotice(message) {
        var $notice = $('<div class="pfg-template-notice">' + message + '</div>');
        $('.pfg-template-grid').after($notice);
        setTimeout(function() {
            $notice.fadeOut(300, function() { $(this).remove(); });
        }, 2500);
    }
    
    // Range slider value update
    $('.pfg-range input[type="range"]').on('input', function() {
        var suffix = $(this).data('suffix') || '';
        $(this).siblings('.pfg-range-value').text(this.value + suffix);
    });
    
    // Premium device toggle (Free version behavior)
    $('.pfg-device-btn').on('click', function(e) {
        if ($(this).hasClass('pfg-device-pro')) {
            e.preventDefault();
            return;
        }
        
        // Desktop click (normal behavior)
        var $wrapper = $(this).closest('.pfg-responsive-columns');
        $wrapper.find('.pfg-device-btn').removeClass('active');
        $(this).addClass('active');
    });
    
    // Tab switching
    $('.pfg-tab').on('click', function() {
        var tabId = $(this).data('tab');
        
        $('.pfg-tab').removeClass('active');
        $(this).addClass('active');
        
        $('.pfg-tab-content').removeClass('active');
        $('#' + tabId).addClass('active');
    });
    
    // Layout type conditional options
    function updateLayoutOptions() {
        var layout = $('#pfg-layout').val();
        
        $('.pfg-layout-option').each(function() {
            var allowedLayouts = $(this).data('layouts').split(',');
            if (allowedLayouts.includes(layout)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }
    
    // Initial state
    updateLayoutOptions();
    
    // On layout change
    $('#pfg-layout').on('change', updateLayoutOptions);
    
    // WooCommerce Source Toggle
    function updateWooOptions() {
        var source = $('#pfg-source').val();
        if (source === 'woocommerce') {
            $('.pfg-woo-options').show();
        } else {
            $('.pfg-woo-options').hide();
        }
    }
    
    // Initial state for WooCommerce options
    updateWooOptions();
    
    // On source change
    $('#pfg-source').on('change', updateWooOptions);
    

    
    // Watermark toggle
    $('#pfg-watermark-enabled').on('change', function() {
        if ($(this).is(':checked')) {
            $('.pfg-watermark-options').show();
            updateWatermarkType();
        } else {
            $('.pfg-watermark-options').hide();
        }
    });
    
    // Watermark type change
    $('#pfg-watermark-type').on('change', function() {
        updateWatermarkType();
    });
    
    function updateWatermarkType() {
        var type = $('#pfg-watermark-type').val();
        if (type === 'text') {
            $('.pfg-watermark-text-options').show();
            $('.pfg-watermark-image-options').hide();
        } else {
            $('.pfg-watermark-text-options').hide();
            $('.pfg-watermark-image-options').show();
        }
    }
    
    // Watermark image upload
    $('#pfg-upload-watermark').on('click', function(e) {
        e.preventDefault();
        var mediaUploader = wp.media({
            title: 'Select Watermark Image',
            button: { text: 'Use as Watermark' },
            multiple: false,
            library: { type: 'image' }
        });
        
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#pfg-watermark-image-url').val(attachment.url);
        });
        
        mediaUploader.open();
    });
});
</script>
