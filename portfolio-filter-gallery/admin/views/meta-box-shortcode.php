<?php
/**
 * Shortcode Meta Box Template.
 *
 * @package    Portfolio_Filter_Gallery
 * @subpackage Portfolio_Filter_Gallery/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$gallery_id = $post->ID;
?>

<div class="pfg-meta-box pfg-shortcode-meta-box">
    
    <div class="pfg-shortcode-box">
        <p><?php esc_html_e( 'Use this shortcode to display your gallery:', 'portfolio-filter-gallery' ); ?></p>
        
        <div class="pfg-shortcode-wrapper">
            <code id="pfg-shortcode">[portfolio_gallery id="<?php echo esc_attr( $gallery_id ); ?>"]</code>
            <button type="button" class="pfg-btn pfg-btn-secondary pfg-copy-shortcode" data-clipboard-target="#pfg-shortcode">
                <span class="dashicons dashicons-clipboard"></span>
                <?php esc_html_e( 'Copy', 'portfolio-filter-gallery' ); ?>
            </button>
        </div>
        
        <p class="pfg-shortcode-note">
            <?php esc_html_e( 'You can also use the legacy shortcode:', 'portfolio-filter-gallery' ); ?>
            <code>[PFG id=<?php echo esc_attr( $gallery_id ); ?>]</code>
        </p>
    </div>
    
    <div class="pfg-shortcode-options">
        <h4><?php esc_html_e( 'Shortcode Parameters', 'portfolio-filter-gallery' ); ?></h4>
        
        <table class="pfg-params-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Parameter', 'portfolio-filter-gallery' ); ?></th>
                    <th><?php esc_html_e( 'Description', 'portfolio-filter-gallery' ); ?></th>
                    <th><?php esc_html_e( 'Example', 'portfolio-filter-gallery' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>id</code></td>
                    <td><?php esc_html_e( 'Gallery ID (required)', 'portfolio-filter-gallery' ); ?></td>
                    <td><code>id="<?php echo esc_attr( $gallery_id ); ?>"</code></td>
                </tr>
                <tr>
                    <td><code>columns</code></td>
                    <td><?php esc_html_e( 'Number of columns (1-6)', 'portfolio-filter-gallery' ); ?></td>
                    <td><code>columns="4"</code></td>
                </tr>
                <tr>
                    <td><code>filter</code></td>
                    <td><?php esc_html_e( 'Default filter to show', 'portfolio-filter-gallery' ); ?></td>
                    <td><code>filter="web-design"</code></td>
                </tr>
                <tr>
                    <td><code>layout</code></td>
                    <td><?php esc_html_e( 'Layout type (grid, masonry)', 'portfolio-filter-gallery' ); ?></td>
                    <td><code>layout="masonry"</code></td>
                </tr>
                <tr>
                    <td><code>show_filters</code></td>
                    <td><?php esc_html_e( 'Show filter buttons (true, false)', 'portfolio-filter-gallery' ); ?></td>
                    <td><code>show_filters="false"</code></td>
                </tr>
                <?php if ( pfg_is_premium() ) : ?>
                <tr>
                    <td><code>limit</code></td>
                    <td><?php esc_html_e( 'Maximum images to show', 'portfolio-filter-gallery' ); ?></td>
                    <td><code>limit="12"</code></td>
                </tr>
                <tr>
                    <td><code>pagination</code></td>
                    <td><?php esc_html_e( 'Enable pagination', 'portfolio-filter-gallery' ); ?></td>
                    <td><code>pagination="true"</code></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ( defined( 'WORDPRESS_BLOCK_EDITOR' ) || function_exists( 'register_block_type' ) ) : ?>
    <div class="pfg-gutenberg-tip">
        <span class="dashicons dashicons-info"></span>
        <?php esc_html_e( 'You can also use the Portfolio Gallery block in the Gutenberg editor!', 'portfolio-filter-gallery' ); ?>
    </div>
    <?php endif; ?>
    
    <div class="pfg-gallery-tools">
        <h4><?php esc_html_e( 'Gallery Tools', 'portfolio-filter-gallery' ); ?></h4>
        
        <div class="pfg-tool-row">
            <div class="pfg-tool-info">
                <strong><?php esc_html_e( 'Force Re-migrate', 'portfolio-filter-gallery' ); ?></strong>
                <p><?php esc_html_e( 'Re-import data from legacy format. Use if alt text, descriptions, or links are missing after upgrade.', 'portfolio-filter-gallery' ); ?></p>
            </div>
            <button type="button" id="pfg-force-remigrate-btn" class="pfg-btn pfg-btn-secondary" data-gallery-id="<?php echo esc_attr( $gallery_id ); ?>">
                <span class="dashicons dashicons-update"></span>
                <?php esc_html_e( 'Re-migrate This Gallery', 'portfolio-filter-gallery' ); ?>
            </button>
        </div>
        
        <div id="pfg-remigrate-status" style="display: none; margin-top: 10px; padding: 10px; border-radius: 6px;"></div>
    </div>
    
</div>

<script>
jQuery(document).ready(function($) {
    $('#pfg-force-remigrate-btn').on('click', function() {
        var $btn = $(this);
        var $status = $('#pfg-remigrate-status');
        var galleryId = $btn.data('gallery-id');
        
        if (!confirm('<?php esc_html_e( 'This will re-import data from the legacy format, overwriting current values. Continue?', 'portfolio-filter-gallery' ); ?>')) {
            return;
        }
        
        $btn.prop('disabled', true).find('.dashicons').addClass('pfg-spin');
        $status.hide();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pfg_force_remigrate',
                security: '<?php echo wp_create_nonce( 'pfg_admin_action' ); ?>',
                gallery_id: galleryId
            },
            success: function(response) {
                $btn.prop('disabled', false).find('.dashicons').removeClass('pfg-spin');
                
                if (response.success) {
                    $status.css({
                        'background': '#dcfce7',
                        'border': '1px solid #86efac',
                        'color': '#166534'
                    }).html('<span class="dashicons dashicons-yes-alt" style="margin-right: 5px;"></span>' + response.data.message).show();
                    
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    $status.css({
                        'background': '#fef2f2',
                        'border': '1px solid #fecaca',
                        'color': '#991b1b'
                    }).html('<span class="dashicons dashicons-warning" style="margin-right: 5px;"></span>' + response.data.message).show();
                }
            },
            error: function() {
                $btn.prop('disabled', false).find('.dashicons').removeClass('pfg-spin');
                $status.css({
                    'background': '#fef2f2',
                    'border': '1px solid #fecaca',
                    'color': '#991b1b'
                }).html('<span class="dashicons dashicons-warning" style="margin-right: 5px;"></span><?php esc_html_e( 'An error occurred. Please try again.', 'portfolio-filter-gallery' ); ?>').show();
            }
        });
    });
});
</script>

<style>
.pfg-params-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    font-size: 13px;
}
.pfg-params-table th,
.pfg-params-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}
.pfg-params-table th {
    background: #f8fafc;
    font-weight: 600;
}
.pfg-params-table code {
    background: #f1f5f9;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
}
.pfg-gutenberg-tip {
    margin-top: 20px;
    padding: 12px 15px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 6px;
    color: #1e40af;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.pfg-gallery-tools {
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}
.pfg-gallery-tools h4 {
    margin: 0 0 15px 0;
    font-size: 14px;
    color: #334155;
}
.pfg-tool-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
}
.pfg-tool-info {
    flex: 1;
}
.pfg-tool-info strong {
    display: block;
    margin-bottom: 4px;
    color: #334155;
}
.pfg-tool-info p {
    margin: 0;
    font-size: 12px;
    color: #64748b;
}
.pfg-spin {
    animation: pfg-spin 1s linear infinite;
}
@keyframes pfg-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
