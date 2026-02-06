<?php
/**
 * Global Settings Page Template.
 *
 * @package    Portfolio_Filter_Gallery
 * @subpackage Portfolio_Filter_Gallery/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check if premium version is active
if ( ! function_exists( 'pfg_is_premium' ) ) {
    function pfg_is_premium() {
        return defined( 'PFG_PREMIUM_VERSION' ) || is_plugin_active( 'portfolio-filter-gallery-premium/portfolio-filter-gallery-premium.php' );
    }
}
// Get current settings
$settings = get_option( 'pfg_global_settings', array() );
$defaults = array(
    'load_bootstrap'      => false,
    'load_fontawesome'    => false,
    'disable_lazy_load'   => false,
    'lightbox_library'    => 'built-in', // Default to Built-in lightbox
    'custom_css'          => '',
    'delete_data_uninstall' => false,
);
$settings = wp_parse_args( $settings, $defaults );

// Handle form submission
if ( isset( $_POST['pfg_save_global_settings'] ) ) {
    if ( wp_verify_nonce( $_POST['_pfg_global_nonce'] ?? '', 'pfg_global_settings' ) && current_user_can( 'manage_options' ) ) {
        $new_settings = array(
            'load_bootstrap'      => isset( $_POST['load_bootstrap'] ),
            'load_fontawesome'    => isset( $_POST['load_fontawesome'] ),
            'disable_lazy_load'   => isset( $_POST['disable_lazy_load'] ),
            'lightbox_library'    => sanitize_key( $_POST['lightbox_library'] ?? 'built-in' ),
            'custom_css'          => sanitize_textarea_field( $_POST['custom_css'] ?? '' ),
            'delete_data_uninstall' => isset( $_POST['delete_data_uninstall'] ),
        );
        
        update_option( 'pfg_global_settings', $new_settings );
        $settings = $new_settings;
        
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved successfully.', 'portfolio-filter-gallery' ) . '</p></div>';
    }
}
?>

<div class="wrap pfg-admin-wrap">
    
    <div class="pfg-admin-header">
        <div>
            <h1 class="pfg-admin-title"><?php esc_html_e( 'Global Settings', 'portfolio-filter-gallery' ); ?></h1>
            <p class="pfg-admin-subtitle"><?php esc_html_e( 'Configure global options for all portfol filter galleries.', 'portfolio-filter-gallery' ); ?></p>
        </div>
    </div>
    
    <form method="post" class="pfg-settings-form">
        <?php wp_nonce_field( 'pfg_global_settings', '_pfg_global_nonce' ); ?>
        
        <div class="pfg-settings-grid">
            
            <!-- Asset Settings -->
            <div class="pfg-card">
                <div class="pfg-card-header">
                    <h3 class="pfg-card-title"><?php esc_html_e( 'Asset Loading', 'portfolio-filter-gallery' ); ?></h3>
                </div>
                
                <div class="pfg-form-row">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Load Bootstrap CSS', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'Enable if your theme doesn\'t include Bootstrap', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <label class="pfg-toggle">
                        <input type="checkbox" name="load_bootstrap" value="1" <?php checked( $settings['load_bootstrap'] ); ?>>
                        <span class="pfg-toggle-slider"></span>
                    </label>
                </div>
                
                <div class="pfg-form-row">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Load Font Awesome', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'Enable if you need Font Awesome icons', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <label class="pfg-toggle">
                        <input type="checkbox" name="load_fontawesome" value="1" <?php checked( $settings['load_fontawesome'] ); ?>>
                        <span class="pfg-toggle-slider"></span>
                    </label>
                </div>
                
                <div class="pfg-form-row">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Disable Lazy Loading', 'portfolio-filter-gallery' ); ?>
                        <small><?php esc_html_e( 'Turn off lazy loading for compatibility', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <label class="pfg-toggle">
                        <input type="checkbox" name="disable_lazy_load" value="1" <?php checked( $settings['disable_lazy_load'] ); ?>>
                        <span class="pfg-toggle-slider"></span>
                    </label>
                </div>
            </div>
            
            <!-- Lightbox Settings -->
            <div class="pfg-card">
                <div class="pfg-card-header">
                    <h3 class="pfg-card-title"><?php esc_html_e( 'Lightbox', 'portfolio-filter-gallery' ); ?></h3>
                </div>
                
                <div class="pfg-form-row">
                    <label class="pfg-form-label"><?php esc_html_e( 'Lightbox Library', 'portfolio-filter-gallery' ); ?></label>
                    <select name="lightbox_library" class="pfg-select">
                        <option value="ld-lightbox" <?php selected( $settings['lightbox_library'], 'ld-lightbox' ); ?>><?php esc_html_e( 'LD Lightbox (Default)', 'portfolio-filter-gallery' ); ?></option>
                        <option value="built-in" <?php selected( $settings['lightbox_library'], 'built-in' ); ?>><?php esc_html_e( 'Built-in Lightbox', 'portfolio-filter-gallery' ); ?></option>
                        <?php if ( pfg_is_premium() ) : ?>
                        <option value="fancybox" <?php selected( $settings['lightbox_library'], 'fancybox' ); ?>><?php esc_html_e( 'Fancybox', 'portfolio-filter-gallery' ); ?></option>
                        <option value="photoswipe" <?php selected( $settings['lightbox_library'], 'photoswipe' ); ?>><?php esc_html_e( 'PhotoSwipe', 'portfolio-filter-gallery' ); ?></option>
                        <?php else : ?>
                        <option value="fancybox" disabled><?php esc_html_e( 'Fancybox (Pro)', 'portfolio-filter-gallery' ); ?></option>
                        <option value="photoswipe" disabled><?php esc_html_e( 'PhotoSwipe (Pro)', 'portfolio-filter-gallery' ); ?></option>
                        <?php endif; ?>
                        <option value="none" <?php selected( $settings['lightbox_library'], 'none' ); ?>><?php esc_html_e( 'None (Disabled)', 'portfolio-filter-gallery' ); ?></option>
                    </select>
                </div>
            </div>
            
            <!-- Custom CSS -->
            <div class="pfg-card pfg-card-full">
                <div class="pfg-card-header">
                    <h3 class="pfg-card-title"><?php esc_html_e( 'Custom CSS', 'portfolio-filter-gallery' ); ?></h3>
                </div>
                
                <div class="pfg-form-row">
                    <label class="pfg-form-label"><?php esc_html_e( 'Global Custom CSS', 'portfolio-filter-gallery' ); ?></label>
                    <textarea name="custom_css" class="pfg-textarea pfg-code-editor" rows="10" placeholder="<?php esc_attr_e( '/* Your custom CSS here */', 'portfolio-filter-gallery' ); ?>"><?php echo esc_textarea( $settings['custom_css'] ); ?></textarea>
                    <small><?php esc_html_e( 'This CSS will be applied to all galleries. To target a specific gallery, use #pfg-gallery-{ID} selector (e.g., #pfg-gallery-123 .pfg-item { ... })', 'portfolio-filter-gallery' ); ?></small>
                </div>
            </div>
            
            <!-- Data Settings -->
            <div class="pfg-card">
                <div class="pfg-card-header">
                    <h3 class="pfg-card-title"><?php esc_html_e( 'Data', 'portfolio-filter-gallery' ); ?></h3>
                </div>
                
                <div class="pfg-form-row">
                    <label class="pfg-form-label">
                        <?php esc_html_e( 'Delete Data on Uninstall', 'portfolio-filter-gallery' ); ?>
                        <small class="pfg-text-danger"><?php esc_html_e( 'Warning: This will delete all galleries permanently when uninstalling', 'portfolio-filter-gallery' ); ?></small>
                    </label>
                    <label class="pfg-toggle">
                        <input type="checkbox" name="delete_data_uninstall" value="1" <?php checked( $settings['delete_data_uninstall'] ); ?>>
                        <span class="pfg-toggle-slider"></span>
                    </label>
                </div>
            </div>
            
            <!-- System Info -->
            <div class="pfg-card">
                <div class="pfg-card-header">
                    <h3 class="pfg-card-title"><?php esc_html_e( 'System Info', 'portfolio-filter-gallery' ); ?></h3>
                </div>
                
                <table class="pfg-info-table">
                    <tr>
                        <th><?php esc_html_e( 'Plugin Version', 'portfolio-filter-gallery' ); ?></th>
                        <td><?php echo esc_html( defined( 'PFG_VERSION' ) ? PFG_VERSION : '2.0.0' ); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Edition', 'portfolio-filter-gallery' ); ?></th>
                        <td><?php echo pfg_is_premium() ? esc_html__( 'Premium', 'portfolio-filter-gallery' ) : esc_html__( 'Free', 'portfolio-filter-gallery' ); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'WordPress Version', 'portfolio-filter-gallery' ); ?></th>
                        <td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'PHP Version', 'portfolio-filter-gallery' ); ?></th>
                        <td><?php echo esc_html( PHP_VERSION ); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Total Galleries', 'portfolio-filter-gallery' ); ?></th>
                        <td><?php echo esc_html( wp_count_posts( 'awl_filter_gallery' )->publish ); ?></td>
                    </tr>
                </table>
            </div>
            
        </div>
        
        <div class="pfg-form-actions">
            <button type="submit" name="pfg_save_global_settings" class="pfg-btn pfg-btn-primary">
                <span class="dashicons dashicons-saved"></span>
                <?php esc_html_e( 'Save Settings', 'portfolio-filter-gallery' ); ?>
            </button>
        </div>
        
    </form>
    
</div>

<style>
.pfg-settings-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 25px;
}
.pfg-card-full {
    grid-column: span 2;
}
.pfg-code-editor {
    font-family: monospace;
    font-size: 13px;
}
.pfg-info-table {
    width: 100%;
    border-collapse: collapse;
}
.pfg-info-table th,
.pfg-info-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}
.pfg-info-table th {
    font-weight: 600;
    width: 40%;
}
.pfg-text-danger {
    color: #ef4444;
}
.pfg-form-actions {
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}
@media (max-width: 1024px) {
    .pfg-settings-grid {
        grid-template-columns: 1fr;
    }
    .pfg-card-full {
        grid-column: auto;
    }
}
</style>
