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
                'name'   => sanitize_text_field( $name ),
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
if ( ! function_exists( 'pfg_build_filter_tree' ) ) {
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
}

/**
 * Generate hierarchical dropdown options HTML.
 */
if ( ! function_exists( 'pfg_render_parent_options' ) ) {
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
                <span class="pfg-stat-number"><?php echo esc_html( count( $all_filters ) ); ?></span>
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
                

                
                <div class="pfg-form-group">
                    <label><?php esc_html_e( 'Filter Name', 'portfolio-filter-gallery' ); ?></label>
                    <input type="text" name="filter_name" class="pfg-input pfg-input-lg" 
                           placeholder="<?php esc_attr_e( 'e.g., Web Design, Photography, Branding', 'portfolio-filter-gallery' ); ?>" required>
                </div>
                
                <div class="pfg-form-row-2col">
                    <div class="pfg-form-group">
                        <label>
                            <?php esc_html_e( 'Parent Filter', 'portfolio-filter-gallery' ); ?>
                        </label>
                        <select name="parent_id" class="pfg-select">
                            <option value=""><?php esc_html_e( '— Top Level —', 'portfolio-filter-gallery' ); ?></option>
                            <?php
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- output built with esc_attr() and esc_html() in pfg_render_parent_options().
                            echo pfg_render_parent_options( $filter_tree );
                            ?>
                        </select>
                    </div>
                    
                    <div class="pfg-form-group">
                        <label><?php esc_html_e( 'Color Tag', 'portfolio-filter-gallery' ); ?></label>
                        <div class="pfg-color-picker-wrap pfg-add-color-picker">
                            <input type="color" name="filter_color" class="pfg-color-input-hidden" id="add-filter-color" value="#3858e9">
                            <label for="add-filter-color" class="pfg-color-label" style="background-color: #3858e9;" title="<?php esc_attr_e( 'Click to change color', 'portfolio-filter-gallery' ); ?>"></label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="pfg-btn pfg-btn-primary pfg-btn-lg pfg-btn-full">
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
                    if ( ! function_exists( 'pfg_render_hierarchy_tree' ) ) {
                        function pfg_render_hierarchy_tree( $tree, $depth = 0 ) {
                            foreach ( $tree as $filter ) {
                                $color = isset( $filter['color'] ) && $filter['color'] ? $filter['color'] : '#94a3b8';
                                $indent = $depth > 0 ? ' style="margin-left: ' . esc_attr( $depth * 16 ) . 'px"' : '';
                                $prefix = $depth > 0 ? '<span class="pfg-tree-line">└</span> ' : '';
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $indent is constructed from integer $depth with esc_attr().
                                echo '<div class="pfg-tree-item"' . $indent . '>';
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $prefix contains only static HTML.
                                echo $prefix;
                                echo '<span class="pfg-tree-dot" style="background:' . esc_attr( $color ) . '"></span>';
                                echo '<span class="pfg-tree-name">' . esc_html( $filter['name'] ) . '</span>';
                                echo '</div>';
                                if ( ! empty( $filter['children'] ) ) {
                                    pfg_render_hierarchy_tree( $filter['children'], $depth + 1 );
                                }
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
                                    <select class="pfg-parent-select">
                                        <option value=""><?php esc_html_e( 'None', 'portfolio-filter-gallery' ); ?></option>
                                        <?php
                                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- output built with esc_attr() and esc_html() in pfg_render_parent_options().
                                        echo pfg_render_parent_options( $filter_tree, $filter['id'], $parent_id );
                                        ?>
                                    </select>
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
