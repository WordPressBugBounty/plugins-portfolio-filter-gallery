<?php
/**
 * Filter Management Page Template - Advanced UI.
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

// Get filters - check new format first, then legacy
$all_filters = get_option( 'pfg_filters', array() );

// If no new format filters, try legacy and migrate
if ( empty( $all_filters ) ) {
    $legacy_filters = get_option( 'awl_portfolio_filter_gallery_categories', array() );
    foreach ( $legacy_filters as $id => $name ) {
        if ( is_string( $name ) ) {
            // Generate Unicode-aware slug for non-Latin characters
            $slug = sanitize_title( $name );
            // If sanitize_title returned URL-encoded (contains %xx hex), use Unicode-aware slug
            if ( empty( $slug ) || preg_match( '/%[0-9a-f]{2}/i', $slug ) ) {
                $slug = mb_strtolower( preg_replace( '/[^\p{L}\p{N}]+/ui', '-', $name ), 'UTF-8' );
                $slug = trim( $slug, '-' );
                if ( empty( $slug ) ) {
                    $slug = 'filter-' . substr( md5( $name ), 0, 8 );
                }
            }
            $all_filters[] = array(
                'id'     => sanitize_key( $id ),
                'name'   => $name,
                'slug'   => $slug,
                'parent' => '',
                'color'  => '',
                'order'  => count( $all_filters ),
            );
        }
    }
    // Save to new format if we migrated
    if ( ! empty( $all_filters ) ) {
        update_option( 'pfg_filters', $all_filters );
    }
}

/**
 * Build hierarchical filter tree.
 */
function pfg_build_filter_tree( $filters, $parent_id = '' ) {
    $tree = array();
    foreach ( $filters as $filter ) {
        $filter_parent = isset( $filter['parent'] ) ? $filter['parent'] : '';
        if ( $filter_parent === $parent_id ) {
            $filter['children'] = pfg_build_filter_tree( $filters, $filter['id'] );
            $tree[] = $filter;
        }
    }
    return $tree;
}

/**
 * Generate hierarchical dropdown options HTML.
 */
function pfg_render_parent_options( $filters, $exclude_id = '', $selected_id = '', $depth = 0 ) {
    $html = '';
    foreach ( $filters as $filter ) {
        if ( $filter['id'] === $exclude_id ) {
            continue; // Don't allow selecting self as parent
        }
        
        $indent = str_repeat( '— ', $depth );
        $prefix = $depth > 0 ? '└ ' : '';
        $is_selected = ( $filter['id'] === $selected_id ) ? ' selected' : '';
        
        $html .= '<option value="' . esc_attr( $filter['id'] ) . '"' . $is_selected . '>';
        $html .= esc_html( $indent . $prefix . $filter['name'] );
        $html .= '</option>';
        
        // Render children
        if ( ! empty( $filter['children'] ) ) {
            $html .= pfg_render_parent_options( $filter['children'], $exclude_id, $selected_id, $depth + 1 );
        }
    }
    return $html;
}

// Build hierarchical tree for dropdown
$filter_tree = pfg_build_filter_tree( $all_filters );
?>

<div class="wrap pfg-admin-wrap pfg-filters-page">
    
    <div class="pfg-admin-header">
        <div class="pfg-header-content">
            <h1 class="pfg-admin-title">
                <span class="dashicons dashicons-filter"></span>
                <?php esc_html_e( 'Filter Manager', 'portfolio-filter-gallery' ); ?>
            </h1>
            <p class="pfg-admin-subtitle"><?php esc_html_e( 'Create and organize filters to categorize your portfolio items.', 'portfolio-filter-gallery' ); ?></p>
        </div>
        <div class="pfg-header-stats">
            <div class="pfg-stat-box">
                <span class="pfg-stat-number"><?php echo count( $all_filters ); ?></span>
                <span class="pfg-stat-label"><?php esc_html_e( 'Filters', 'portfolio-filter-gallery' ); ?></span>
            </div>
        </div>
    </div>
    
    <div class="pfg-filters-layout">
        
        <!-- Add New Filter Panel -->
        <div class="pfg-panel pfg-add-panel">
            <div class="pfg-panel-header">
                <span class="dashicons dashicons-plus-alt2"></span>
                <h3><?php esc_html_e( 'Add New Filter', 'portfolio-filter-gallery' ); ?></h3>
            </div>
            
            <form id="pfg-add-filter-form" class="pfg-add-form">
                <?php wp_nonce_field( 'pfg_admin_action', 'pfg_filter_nonce' ); ?>
                
                <?php 
                $filter_count = count( $all_filters );
                $filter_limit = pfg_is_premium() ? 999 : 7;
                $is_at_limit = $filter_count >= $filter_limit;
                ?>
                
                <?php if ( ! pfg_is_premium() && $filter_count >= 6 ) : ?>
                <div class="pfg-filter-limit-notice <?php echo $is_at_limit ? 'pfg-limit-reached' : 'pfg-limit-warning'; ?>">
                    <?php if ( $is_at_limit ) : ?>
                        <span class="dashicons dashicons-warning"></span>
                        <span><?php printf( esc_html__( 'Filter limit reached (%d/%d). Upgrade to add unlimited filters.', 'portfolio-filter-gallery' ), $filter_count, $filter_limit ); ?></span>
                    <?php else : ?>
                        <span class="dashicons dashicons-info"></span>
                        <span><?php printf( esc_html__( 'Using %d of %d filters. Upgrade for unlimited.', 'portfolio-filter-gallery' ), $filter_count, $filter_limit ); ?></span>
                    <?php endif; ?>
                    <a href="<?php echo esc_url( PFG_Features::get_upgrade_url( 'filter-limit' ) ); ?>" target="_blank" class="pfg-upgrade-link"><?php esc_html_e( 'Upgrade', 'portfolio-filter-gallery' ); ?> →</a>
                </div>
                <?php endif; ?>
                
                <div class="pfg-form-group">
                    <label><?php esc_html_e( 'Filter Name', 'portfolio-filter-gallery' ); ?></label>
                    <input type="text" name="filter_name" class="pfg-input pfg-input-lg" 
                           placeholder="<?php esc_attr_e( 'e.g., Web Design, Photography, Branding', 'portfolio-filter-gallery' ); ?>" required <?php echo $is_at_limit ? 'disabled' : ''; ?>>
                </div>
                
                <div class="pfg-form-row-2col">
                    <div class="pfg-form-group">
                        <label>
                            <?php esc_html_e( 'Parent Filter', 'portfolio-filter-gallery' ); ?>
                            <?php if ( ! pfg_is_premium() ) : ?><span class="pfg-pro-badge">PRO</span><?php endif; ?>
                        </label>
                        <select name="parent_id" class="pfg-select" <?php echo ! pfg_is_premium() ? 'disabled' : ''; ?>>
                            <option value=""><?php esc_html_e( '— Top Level —', 'portfolio-filter-gallery' ); ?></option>
                            <?php if ( pfg_is_premium() ) : ?>
                                <?php echo pfg_render_parent_options( $filter_tree ); ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="pfg-form-group">
                        <label><?php esc_html_e( 'Color Tag', 'portfolio-filter-gallery' ); ?></label>
                        <div class="pfg-color-picker-wrap pfg-add-color-picker">
                            <input type="color" name="filter_color" class="pfg-color-input-hidden" id="add-filter-color" value="#3858e9" <?php echo $is_at_limit ? 'disabled' : ''; ?>>
                            <label for="add-filter-color" class="pfg-color-label" style="background-color: #3858e9;" title="<?php esc_attr_e( 'Click to change color', 'portfolio-filter-gallery' ); ?>"></label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="pfg-btn pfg-btn-primary pfg-btn-lg pfg-btn-full" <?php echo $is_at_limit ? 'disabled' : ''; ?>>
                    <span class="dashicons dashicons-plus"></span>
                    <?php esc_html_e( 'Add Filter', 'portfolio-filter-gallery' ); ?>
                </button>
            </form>
            
            
            <?php if ( ! empty( $all_filters ) ) : ?>
            <!-- Hierarchy Chart -->
            <div class="pfg-hierarchy-chart">
                <h4><span class="dashicons dashicons-networking"></span> <?php esc_html_e( 'Filter Hierarchy', 'portfolio-filter-gallery' ); ?></h4>
                <div class="pfg-hierarchy-tree">
                    <?php 
                    function pfg_render_hierarchy_tree( $tree, $depth = 0 ) {
                        foreach ( $tree as $filter ) {
                            $color = isset( $filter['color'] ) && $filter['color'] ? $filter['color'] : '#94a3b8';
                            $indent = $depth > 0 ? ' style="margin-left: ' . ($depth * 16) . 'px"' : '';
                            $prefix = $depth > 0 ? '<span class="pfg-tree-line">└</span> ' : '';
                            echo '<div class="pfg-tree-item"' . $indent . '>';
                            echo $prefix;
                            echo '<span class="pfg-tree-dot" style="background:' . esc_attr( $color ) . '"></span>';
                            echo '<span class="pfg-tree-name">' . esc_html( $filter['name'] ) . '</span>';
                            echo '</div>';
                            if ( ! empty( $filter['children'] ) ) {
                                pfg_render_hierarchy_tree( $filter['children'], $depth + 1 );
                            }
                        }
                    }
                    pfg_render_hierarchy_tree( $filter_tree );
                    ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="pfg-quick-tips">
                <h4><span class="dashicons dashicons-lightbulb"></span> <?php esc_html_e( 'Quick Tips', 'portfolio-filter-gallery' ); ?></h4>
                <ul>
                    <li><?php esc_html_e( 'Drag filters to reorder them', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Double-click a name to edit', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Set parents for multi-level menus', 'portfolio-filter-gallery' ); ?></li>
                </ul>
            </div>
        </div>
        
        <!-- Filters List Panel -->
        <div class="pfg-panel pfg-list-panel">
            <div class="pfg-panel-header">
                <div class="pfg-panel-title">
                    <span class="dashicons dashicons-list-view"></span>
                    <h3><?php esc_html_e( 'Your Filters', 'portfolio-filter-gallery' ); ?></h3>
                </div>
                
                <div class="pfg-panel-actions">
                    <div class="pfg-search-box">
                        <span class="dashicons dashicons-search"></span>
                        <input type="text" id="pfg-filter-search" placeholder="<?php esc_attr_e( 'Search filters...', 'portfolio-filter-gallery' ); ?>">
                    </div>
                    <?php if ( ! empty( $all_filters ) ) : ?>
                    <button type="button" class="pfg-btn pfg-btn-secondary pfg-btn-sm" id="pfg-repair-slugs" title="<?php esc_attr_e( 'Fix broken Unicode slugs from migration', 'portfolio-filter-gallery' ); ?>">
                        <span class="dashicons dashicons-admin-tools"></span>
                        <?php esc_html_e( 'Repair Slugs', 'portfolio-filter-gallery' ); ?>
                    </button>
                    <button type="button" class="pfg-btn pfg-btn-danger pfg-btn-sm" id="pfg-delete-all-filters" title="<?php esc_attr_e( 'Delete All Filters', 'portfolio-filter-gallery' ); ?>">
                        <span class="dashicons dashicons-trash"></span>
                        <?php esc_html_e( 'Delete All', 'portfolio-filter-gallery' ); ?>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ( empty( $all_filters ) ) : ?>
                <div class="pfg-empty-state">
                    <div class="pfg-empty-icon">
                        <span class="dashicons dashicons-tag"></span>
                    </div>
                    <h3><?php esc_html_e( 'No Filters Yet', 'portfolio-filter-gallery' ); ?></h3>
                    <p><?php esc_html_e( 'Create your first filter using the form on the left to start organizing your portfolio.', 'portfolio-filter-gallery' ); ?></p>
                </div>
            <?php else : ?>
                <div class="pfg-filters-table-wrap">
                    <table class="pfg-filters-table" id="pfg-filters-list">
                        <thead>
                            <tr>
                                <th class="pfg-col-drag"></th>
                                <th class="pfg-col-color"></th>
                                <th class="pfg-col-name"><?php esc_html_e( 'Name', 'portfolio-filter-gallery' ); ?></th>
                                <th class="pfg-col-slug"><?php esc_html_e( 'Slug', 'portfolio-filter-gallery' ); ?></th>
                                <th class="pfg-col-parent"><?php esc_html_e( 'Parent', 'portfolio-filter-gallery' ); ?></th>
                                <th class="pfg-col-actions"><?php esc_html_e( 'Actions', 'portfolio-filter-gallery' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $all_filters as $filter ) : 
                                $parent_name = '';
                                $parent_id = isset( $filter['parent'] ) ? $filter['parent'] : '';
                                if ( $parent_id ) {
                                    foreach ( $all_filters as $pf ) {
                                        if ( $pf['id'] === $parent_id ) {
                                            $parent_name = $pf['name'];
                                            break;
                                        }
                                    }
                                }
                                $color = isset( $filter['color'] ) && $filter['color'] ? $filter['color'] : '#94a3b8';
                            ?>
                            <tr class="pfg-filter-row" data-id="<?php echo esc_attr( $filter['id'] ); ?>" data-parent="<?php echo esc_attr( $parent_id ); ?>">
                                <td class="pfg-col-drag">
                                    <span class="pfg-drag-handle dashicons dashicons-move"></span>
                                </td>
                                <td class="pfg-col-color">
                                    <div class="pfg-color-picker-wrap">
                                        <input type="color" class="pfg-row-color" value="<?php echo esc_attr( $color ); ?>" id="color-<?php echo esc_attr( $filter['id'] ); ?>">
                                        <label for="color-<?php echo esc_attr( $filter['id'] ); ?>" class="pfg-color-label" style="background-color: <?php echo esc_attr( $color ); ?>;" title="<?php esc_attr_e( 'Click to change color', 'portfolio-filter-gallery' ); ?>"></label>
                                    </div>
                                </td>
                                <td class="pfg-col-name">
                                    <input type="text" class="pfg-editable-name" value="<?php echo esc_attr( $filter['name'] ); ?>">
                                </td>
                                <td class="pfg-col-slug">
                                    <input type="text" class="pfg-editable-slug" value="<?php echo esc_attr( $filter['slug'] ?? $filter['id'] ); ?>" data-original="<?php echo esc_attr( $filter['slug'] ?? $filter['id'] ); ?>">
                                </td>
                                <td class="pfg-col-parent">
                                    <?php if ( pfg_is_premium() ) : ?>
                                        <select class="pfg-parent-select">
                                            <option value=""><?php esc_html_e( 'None', 'portfolio-filter-gallery' ); ?></option>
                                            <?php echo pfg_render_parent_options( $filter_tree, $filter['id'], $parent_id ); ?>
                                        </select>
                                    <?php else : ?>
                                        <span class="pfg-parent-pro-tag">PRO</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pfg-col-actions">
                                    <button type="button" class="pfg-action-btn pfg-btn-delete" title="<?php esc_attr_e( 'Delete', 'portfolio-filter-gallery' ); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="pfg-panel-footer">
                    <span class="pfg-footer-hint">
                        <span class="dashicons dashicons-info"></span>
                        <?php esc_html_e( 'Changes are saved automatically', 'portfolio-filter-gallery' ); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
    
</div>

<style>
/* Page Layout */
.pfg-filters-page { max-width: 1200px; }

.pfg-admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding: 20px 25px;
    background: linear-gradient(135deg, #3858e9 0%, #1e40af 100%);
    border-radius: 12px;
    color: #fff;
}
.pfg-admin-title {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0 0 5px 0;
    font-size: 24px;
    color: #fff;
}
.pfg-admin-title .dashicons { font-size: 28px; width: 28px; height: 28px; }
.pfg-admin-subtitle { margin: 0; opacity: 0.9; font-size: 14px; }

.pfg-stat-box {
    text-align: center;
    background: rgba(255,255,255,0.15);
    padding: 15px 25px;
    border-radius: 8px;
}
.pfg-stat-number { display: block; font-size: 32px; font-weight: 700; }
.pfg-stat-label { font-size: 12px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px; }

.pfg-filters-layout {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 25px;
    align-items: start;
}

/* Panels */
.pfg-panel {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    overflow: hidden;
}
.pfg-panel-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 18px 20px;
    border-bottom: 1px solid #f1f5f9;
    background: #fafbfc;
}
.pfg-panel-header h3 { margin: 0; font-size: 15px; font-weight: 600; color: #1e293b; }
.pfg-panel-header .dashicons { color: #3858e9; }

.pfg-panel-title { display: flex; align-items: center; gap: 10px; }
.pfg-panel-actions { margin-left: auto; }

/* Add Form */
.pfg-add-form { padding: 20px; }
.pfg-form-group { margin-bottom: 16px; }
.pfg-form-group label { display: block; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px; }
.pfg-form-row-2col { 
    display: flex; 
    gap: 16px; 
    align-items: flex-start;
}
.pfg-form-row-2col > .pfg-form-group {
    margin-bottom: 16px;
}
.pfg-form-row-2col > .pfg-form-group:first-child {
    flex: 1;
}
.pfg-form-row-2col > .pfg-form-group:last-child {
    width: auto;
}

.pfg-input, .pfg-select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
}
.pfg-input:focus, .pfg-select:focus {
    border-color: #3858e9;
    outline: none;
    box-shadow: 0 0 0 3px rgba(56, 88, 233, 0.1);
}
.pfg-input-lg { padding: 12px 16px; font-size: 15px; }
.pfg-color-input { width: 100%; height: 42px; padding: 4px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer; }

.pfg-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}
.pfg-btn-primary { background: #3858e9; color: #fff; }
.pfg-btn-primary:hover { background: #2d4ad4; }
.pfg-btn-lg { padding: 14px 24px; font-size: 15px; }
.pfg-btn-full { width: 100%; }

/* Hierarchy Chart */
.pfg-hierarchy-chart {
    margin: 20px;
    padding: 15px;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}
.pfg-hierarchy-chart h4 {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 12px 0;
    font-size: 13px;
    color: #475569;
}
.pfg-hierarchy-chart h4 .dashicons { color: #3858e9; }
.pfg-hierarchy-tree { font-size: 12px; }
.pfg-tree-item {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 0;
}
.pfg-tree-line { color: #cbd5e1; font-family: monospace; }
.pfg-tree-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}
.pfg-tree-name { color: #334155; font-weight: 500; }

/* Tips */
.pfg-quick-tips {
    margin: 20px;
    padding: 15px;
    background: #f0f9ff;
    border-radius: 8px;
    border-left: 3px solid #0ea5e9;
}
.pfg-quick-tips h4 { display: flex; align-items: center; gap: 8px; margin: 0 0 10px 0; font-size: 13px; color: #0369a1; }
.pfg-quick-tips ul { margin: 0; padding-left: 18px; }
.pfg-quick-tips li { font-size: 12px; color: #475569; margin-bottom: 4px; }

/* Search */
.pfg-search-box {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f1f5f9;
    padding: 6px 12px;
    border-radius: 6px;
}
.pfg-search-box .dashicons { color: #94a3b8; font-size: 16px; }
.pfg-search-box input { border: none; background: none; font-size: 13px; width: 150px; outline: none; }

/* Empty State */
.pfg-empty-state { text-align: center; padding: 50px 30px; }
.pfg-empty-icon { margin-bottom: 15px; }
.pfg-empty-icon .dashicons { font-size: 48px; width: 48px; height: 48px; color: #cbd5e1; }
.pfg-empty-state h3 { margin: 0 0 8px 0; color: #475569; }
.pfg-empty-state p { margin: 0; color: #94a3b8; font-size: 14px; }

/* Table */
.pfg-filters-table-wrap { max-height: 500px; overflow-y: auto; }
.pfg-filters-table { width: 100%; border-collapse: collapse; }
.pfg-filters-table th {
    text-align: left;
    padding: 12px 15px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    position: sticky;
    top: 0;
}
.pfg-filters-table td { padding: 10px 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.pfg-filter-row:hover { background: #f8fafc; }

.pfg-col-drag { width: 40px; text-align: center; }
.pfg-col-color { width: 50px; }
.pfg-col-slug { width: 120px; }
.pfg-col-parent { width: 140px; }
.pfg-col-actions { width: 60px; text-align: center; }

.pfg-drag-handle { cursor: move; color: #cbd5e1; }
.pfg-drag-handle:hover { color: #3858e9; }

/* Color Picker Wrap */
.pfg-color-picker-wrap {
    position: relative;
    display: inline-block;
}
.pfg-color-picker-wrap .pfg-row-color {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
}
.pfg-color-label {
    display: block;
    width: 28px;
    height: 28px;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}
.pfg-color-label:hover {
    border-color: #3858e9;
    transform: scale(1.1);
}

/* Add Form Color Picker */
.pfg-add-color-picker {
    display: inline-block;
}
.pfg-add-color-picker .pfg-color-label {
    width: 52px;
    height: 32px;
}
.pfg-color-input-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
}

/* Minimal PRO Tag */
.pfg-parent-pro-tag {
    display: inline-block;
    padding: 2px 8px;
    background: #f59e0b;
    color: #fff;
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border-radius: 3px;
}

.pfg-editable-name {
    width: 100%;
    border: 1px solid transparent;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    background: transparent;
}
.pfg-editable-name:hover { background: #f8fafc; }
.pfg-editable-name:focus { border-color: #3858e9; background: #fff; outline: none; }

.pfg-col-slug code { font-size: 11px; color: #64748b; background: #f1f5f9; padding: 3px 8px; border-radius: 4px; }

.pfg-parent-select {
    width: 100%;
    padding: 6px 8px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 12px;
    background: #fff;
}
.pfg-parent-select:focus { border-color: #3858e9; outline: none; }

.pfg-action-btn {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    border-radius: 6px;
    background: transparent;
    color: #94a3b8;
    cursor: pointer;
    transition: all 0.2s;
}
.pfg-btn-delete:hover { background: #fef2f2; color: #ef4444; }
.pfg-action-btn .dashicons { font-size: 16px; }

.pfg-filter-row.ui-sortable-helper { background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.pfg-filter-row.ui-sortable-placeholder td { background: #eff6ff; height: 50px; }

.pfg-panel-footer {
    padding: 12px 20px;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
}
.pfg-footer-hint { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #64748b; }
.pfg-footer-hint .dashicons { font-size: 14px; }

@media (max-width: 900px) {
    .pfg-filters-layout { grid-template-columns: 1fr; }
    .pfg-admin-header { flex-direction: column; text-align: center; gap: 15px; }
}

/* Filter Limit Notice */
.pfg-filter-limit-notice {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 12px;
    margin-bottom: 15px;
}
.pfg-filter-limit-notice .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}
.pfg-limit-warning {
    background: #fef3c7;
    border: 1px solid #fbbf24;
    color: #92400e;
}
.pfg-limit-reached {
    background: #fef2f2;
    border: 1px solid #fca5a5;
    color: #dc2626;
}
.pfg-upgrade-link {
    margin-left: auto;
    font-weight: 600;
    text-decoration: none;
    color: inherit;
    white-space: nowrap;
}
.pfg-upgrade-link:hover {
    text-decoration: underline;
}

/* PRO Badge */
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

/* Disabled select styling */
.pfg-parent-select:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: #f1f5f9;
}

/* Secondary button styling */
.pfg-btn-secondary {
    background: #64748b;
    color: #fff;
}
.pfg-btn-secondary:hover {
    background: #475569;
}
.pfg-btn-sm {
    padding: 6px 12px;
    font-size: 13px;
}
.pfg-panel-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}
</style>

<script>
jQuery(document).ready(function($) {
    
    // ============================================
    // Helper Functions for Live Updates
    // ============================================
    
    // Generate slug from name - Unicode-aware to support Japanese, Chinese, etc.
    function generateSlug(name) {
        // Use Unicode-aware lowercase (works in modern browsers)
        var slug = name.toLowerCase();
        
        // Replace anything that's not a letter, number, or allowed punctuation with dash
        // \p{L} matches any letter, \p{N} matches any number (Unicode-aware)
        try {
            // Modern browsers support Unicode property escapes
            slug = slug.replace(/[^\p{L}\p{N}]+/gu, '-');
        } catch (e) {
            // Fallback for older browsers: keep basic ASCII + common Unicode ranges
            slug = slug.replace(/[^a-z0-9\u3000-\u303f\u3040-\u309f\u30a0-\u30ff\u4e00-\u9faf\u0600-\u06ff\u0400-\u04ff]+/gi, '-');
        }
        
        // Remove leading/trailing dashes and collapse multiple dashes
        slug = slug.replace(/-+/g, '-').replace(/^-|-$/g, '');
        
        return slug || 'filter'; // Fallback if empty
    }
    
    // Get all filters data from table
    function getFiltersData() {
        var filters = [];
        $('#pfg-filters-list tbody tr').each(function() {
            var $row = $(this);
            filters.push({
                id: $row.data('id'),
                name: $row.find('.pfg-editable-name').val(),
                parent: $row.data('parent') || '',
                color: $row.find('.pfg-row-color').val() || '#94a3b8'
            });
        });
        return filters;
    }
    
    // Build hierarchy tree from flat filters array
    // Added safety checks to prevent infinite recursion from circular references
    function buildTree(filters, parentId, visited, depth) {
        parentId = parentId || '';
        visited = visited || {};
        depth = depth || 0;
        
        // Safety: prevent infinite recursion with max depth limit
        if (depth > 10) {
            console.warn('buildTree: Max depth exceeded, possible circular reference');
            return [];
        }
        
        var children = [];
        filters.forEach(function(filter) {
            // Skip if already visited (circular reference detection)
            if (visited[filter.id]) {
                return;
            }
            
            if ((filter.parent || '') === parentId) {
                // Skip if filter is its own parent
                if (filter.id === filter.parent) {
                    console.warn('buildTree: Filter is its own parent:', filter.id);
                    return;
                }
                
                var node = $.extend({}, filter);
                var newVisited = $.extend({}, visited);
                newVisited[filter.id] = true;
                node.children = buildTree(filters, filter.id, newVisited, depth + 1);
                children.push(node);
            }
        });
        return children;
    }
    
    // Render hierarchy tree to HTML
    function renderTreeHTML(tree, depth) {
        depth = depth || 0;
        var html = '';
        tree.forEach(function(filter) {
            var indent = depth > 0 ? ' style="margin-left: ' + (depth * 16) + 'px"' : '';
            var prefix = depth > 0 ? '<span class="pfg-tree-line">└</span> ' : '';
            var color = filter.color || '#94a3b8';
            
            html += '<div class="pfg-tree-item" data-id="' + filter.id + '"' + indent + '>';
            html += prefix;
            html += '<span class="pfg-tree-dot" style="background:' + color + '"></span>';
            html += '<span class="pfg-tree-name">' + $('<div>').text(filter.name).html() + '</span>';
            html += '</div>';
            
            if (filter.children && filter.children.length > 0) {
                html += renderTreeHTML(filter.children, depth + 1);
            }
        });
        return html;
    }
    
    // Rebuild the hierarchy chart
    function rebuildHierarchy() {
        var filters = getFiltersData();
        var tree = buildTree(filters, '');
        var html = renderTreeHTML(tree, 0);
        
        if (html) {
            $('.pfg-hierarchy-tree').html(html);
            $('.pfg-hierarchy-chart').show();
        } else {
            $('.pfg-hierarchy-chart').hide();
        }
    }
    
    // Update all parent dropdowns with current filter names
    function updateParentDropdowns() {
        var filters = getFiltersData();
        
        // For each parent dropdown, update the option text
        $('.pfg-parent-select').each(function() {
            var $select = $(this);
            var currentFilterId = $select.closest('.pfg-filter-row').data('id');
            
            $select.find('option').each(function() {
                var $option = $(this);
                var optionId = $option.val();
                
                if (optionId) {
                    // Find the filter with this ID
                    for (var i = 0; i < filters.length; i++) {
                        if (filters[i].id === optionId) {
                            $option.text(filters[i].name);
                            break;
                        }
                    }
                }
            });
        });
    }
    
    // Update slug display for a filter (when name changes, auto-generate slug)
    function updateSlugDisplay($row, newName) {
        var newSlug = generateSlug(newName);
        var $slugInput = $row.find('.pfg-editable-slug');
        var currentFilterId = String($row.data('id'));
        
        // Check if this slug already exists in other filters
        var existingSlugs = [];
        $('.pfg-editable-slug').each(function() {
            var $input = $(this);
            var filterId = String($input.closest('.pfg-filter-row').data('id'));
            if (filterId !== currentFilterId) {
                existingSlugs.push($input.val());
            }
        });
        
        // Generate unique slug if duplicate exists
        if (existingSlugs.indexOf(newSlug) > -1) {
            var counter = 2;
            var baseSlug = newSlug;
            while (existingSlugs.indexOf(newSlug) > -1) {
                newSlug = baseSlug + '-' + counter;
                counter++;
            }
        }
        
        $slugInput.val(newSlug);
        $slugInput.data('original', newSlug);
    }
    
    // ============================================
    // Event Handlers
    // ============================================
    
    // Initialize sortable table
    if ($.fn.sortable) {
        $('#pfg-filters-list tbody').sortable({
            handle: '.pfg-drag-handle',
            placeholder: 'ui-sortable-placeholder',
            axis: 'y',
            helper: function(e, tr) {
                var $helper = tr.clone();
                $helper.children('td').each(function(index) {
                    $(this).width(tr.children('td').eq(index).width());
                });
                return $helper;
            },
            update: function() {
                var order = [];
                $('#pfg-filters-list tbody tr').each(function() {
                    order.push($(this).data('id'));
                });
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pfg_reorder_filters',
                        security: $('#pfg_filter_nonce').val(),
                        order: order
                    },
                    success: function() {
                        rebuildHierarchy();
                    }
                });
            }
        });
    }
    
    // Search filters
    $('#pfg-filter-search').on('input', function() {
        var query = $(this).val().toLowerCase();
        $('.pfg-filter-row').each(function() {
            var name = $(this).find('.pfg-editable-name').val().toLowerCase();
            $(this).toggle(name.indexOf(query) > -1);
        });
    });
    
    // Save name on blur - with live updates
    $('.pfg-editable-name').on('blur', function() {
        var $input = $(this);
        var $row = $input.closest('.pfg-filter-row');
        var filterId = $row.data('id');
        var newName = $input.val();
        
        // Immediately update slug display (generates unique slug if needed)
        updateSlugDisplay($row, newName);
        
        // Get the newly generated slug
        var newSlug = $row.find('.pfg-editable-slug').val();
        
        // Immediately update parent dropdowns
        updateParentDropdowns();
        
        // Immediately rebuild hierarchy
        rebuildHierarchy();
        
        // Save name to server
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pfg_update_filter',
                security: $('#pfg_filter_nonce').val(),
                filter_id: filterId,
                name: newName
            }
        });
        
        // Also save slug to server (since server no longer auto-generates)
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pfg_update_filter_slug',
                security: $('#pfg_filter_nonce').val(),
                filter_id: filterId,
                slug: newSlug
            }
        });
    });
    
    // Save slug on blur - with duplicate detection
    $('.pfg-editable-slug').on('blur', function() {
        var $input = $(this);
        var $row = $input.closest('.pfg-filter-row');
        var filterId = $row.data('id');
        var newSlug = $input.val().trim();
        var originalSlug = $input.data('original');
        
        // Generate proper slug format
        newSlug = generateSlug(newSlug);
        $input.val(newSlug);
        
        // Check for duplicates
        var isDuplicate = false;
        var currentFilterId = String(filterId);
        $('.pfg-editable-slug').each(function() {
            var $otherInput = $(this);
            var otherId = String($otherInput.closest('.pfg-filter-row').data('id'));
            if (otherId !== currentFilterId && $otherInput.val() === newSlug) {
                isDuplicate = true;
                return false;
            }
        });
        
        if (isDuplicate) {
            // Generate unique slug
            newSlug = generateUniqueSlug(newSlug, filterId);
            $input.val(newSlug);
            $input.addClass('pfg-slug-warning');
            setTimeout(function() { $input.removeClass('pfg-slug-warning'); }, 2000);
        }
        
        // Only save if changed
        if (newSlug !== originalSlug) {
            $input.data('original', newSlug);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pfg_update_filter_slug',
                    security: $('#pfg_filter_nonce').val(),
                    filter_id: filterId,
                    slug: newSlug
                }
            });
        }
    });
    
    // Generate unique slug by appending number suffix
    function generateUniqueSlug(baseSlug, currentFilterId) {
        var existingSlugs = [];
        var currentId = String(currentFilterId);
        $('.pfg-editable-slug').each(function() {
            var $input = $(this);
            var filterId = String($input.closest('.pfg-filter-row').data('id'));
            if (filterId !== currentId) {
                existingSlugs.push($input.val());
            }
        });
        
        var counter = 2;
        var newSlug = baseSlug;
        while (existingSlugs.indexOf(newSlug) > -1) {
            newSlug = baseSlug + '-' + counter;
            counter++;
        }
        return newSlug;
    }
    
    // Save parent on change - with live updates
    $('.pfg-parent-select').on('change', function() {
        var $row = $(this).closest('.pfg-filter-row');
        var filterId = $row.data('id');
        var parentId = $(this).val();
        
        // Update row data attribute
        $row.data('parent', parentId);
        $row.attr('data-parent', parentId);
        
        // Immediately rebuild hierarchy
        rebuildHierarchy();
        
        // Save to server
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pfg_update_filter_parent',
                security: $('#pfg_filter_nonce').val(),
                filter_id: filterId,
                parent_id: parentId
            }
        });
    });
    
    // Save color on change - with live updates
    $('.pfg-row-color').on('change', function() {
        var $row = $(this).closest('.pfg-filter-row');
        var filterId = $row.data('id');
        var color = $(this).val();
        
        // Update the visible color label
        $(this).siblings('.pfg-color-label').css('background-color', color);
        
        // Immediately rebuild hierarchy to update dot color
        rebuildHierarchy();
        
        // Save to server
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pfg_update_filter_color',
                security: $('#pfg_filter_nonce').val(),
                filter_id: filterId,
                color: color
            }
        });
    });
    
    // Update add form color label on change
    $('#add-filter-color').on('change input', function() {
        $(this).siblings('.pfg-color-label').css('background-color', $(this).val());
    });
    
    // Delete filter - with live updates
    $('.pfg-btn-delete').on('click', function() {
        var $row = $(this).closest('.pfg-filter-row');
        var filterId = $row.data('id');
        var filterName = $row.find('.pfg-editable-name').val();
        
        if (confirm('<?php echo esc_js( __( 'Delete filter', 'portfolio-filter-gallery' ) ); ?> "' + filterName + '"?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pfg_delete_filter',
                    security: $('#pfg_filter_nonce').val(),
                    filter_id: filterId
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(200, function() { 
                            $(this).remove();
                            
                            // Update count
                            var count = $('.pfg-filter-row').length;
                            $('.pfg-stat-number').text(count);
                            
                            // Remove from parent dropdowns
                            $('.pfg-parent-select option[value="' + filterId + '"]').remove();
                            
                            // Rebuild hierarchy
                            rebuildHierarchy();
                            
                            // Hide hierarchy chart if no filters left
                            if (count === 0) {
                                $('.pfg-hierarchy-chart').hide();
                            }
                        });
                    }
                }
            });
        }
    });
    
    // Delete all filters with confirmation
    $('#pfg-delete-all-filters').on('click', function() {
        var filterCount = $('#pfg-filters-list tbody tr').length;
        
        if (confirm('<?php echo esc_js( __( 'Are you sure you want to delete ALL filters? This action cannot be undone.', 'portfolio-filter-gallery' ) ); ?>\n\n' + filterCount + ' <?php echo esc_js( __( 'filter(s) will be permanently deleted.', 'portfolio-filter-gallery' ) ); ?>')) {
            var $btn = $(this);
            var originalText = $btn.html();
            
            $btn.html('<span class="dashicons dashicons-update spin"></span>').prop('disabled', true);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pfg_delete_all_filters',
                    security: $('#pfg_filter_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        // Reload page to show empty state
                        window.location.reload();
                    } else {
                        alert(response.data.message || '<?php echo esc_js( __( 'Failed to delete filters.', 'portfolio-filter-gallery' ) ); ?>');
                        $btn.html(originalText).prop('disabled', false);
                    }
                },
                error: function() {
                    alert('<?php echo esc_js( __( 'Server error. Please try again.', 'portfolio-filter-gallery' ) ); ?>');
                    $btn.html(originalText).prop('disabled', false);
                }
            });
        }
    });
    
    // Add new filter
    $('#pfg-add-filter-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var originalText = $btn.html();
        
        $btn.html('<span class="dashicons dashicons-update spin"></span> <?php esc_html_e( 'Adding...', 'portfolio-filter-gallery' ); ?>');
        $btn.prop('disabled', true);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pfg_add_filter',
                security: $('#pfg_filter_nonce').val(),
                name: $form.find('[name="filter_name"]').val(),
                parent_id: $form.find('[name="parent_id"]').val(),
                color: $form.find('[name="filter_color"]').val()
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data || '<?php esc_html_e( 'Error adding filter', 'portfolio-filter-gallery' ); ?>');
                    $btn.html(originalText);
                    $btn.prop('disabled', false);
                }
            },
            error: function() {
                alert('<?php esc_html_e( 'Error adding filter', 'portfolio-filter-gallery' ); ?>');
                $btn.html(originalText);
                $btn.prop('disabled', false);
            }
        });
    });
    
    // Repair slugs button handler
    $('#pfg-repair-slugs').on('click', function() {
        var $btn = $(this);
        var originalText = $btn.html();
        
        $btn.html('<span class="dashicons dashicons-update spin"></span> <?php esc_html_e( 'Repairing...', 'portfolio-filter-gallery' ); ?>').prop('disabled', true);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pfg_repair_filter_slugs',
                security: $('#pfg_filter_nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || '<?php echo esc_js( __( 'Slugs repaired successfully!', 'portfolio-filter-gallery' ) ); ?>');
                    if (response.data.repaired > 0) {
                        location.reload();
                    } else {
                        $btn.html(originalText).prop('disabled', false);
                    }
                } else {
                    alert(response.data.message || '<?php echo esc_js( __( 'Failed to repair slugs.', 'portfolio-filter-gallery' ) ); ?>');
                    $btn.html(originalText).prop('disabled', false);
                }
            },
            error: function() {
                alert('<?php echo esc_js( __( 'Server error. Please try again.', 'portfolio-filter-gallery' ) ); ?>');
                $btn.html(originalText).prop('disabled', false);
            }
        });
    });
});
</script>
