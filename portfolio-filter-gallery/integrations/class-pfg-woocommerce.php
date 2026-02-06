<?php
/**
 * WooCommerce Integration for Portfolio Filter Gallery.
 *
 * @package    Portfolio_Filter_Gallery
 * @subpackage Portfolio_Filter_Gallery/integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class for WooCommerce integration.
 */
class PFG_WooCommerce {

    /**
     * Check if WooCommerce is active.
     *
     * @return bool
     */
    public static function is_active() {
        return class_exists( 'WooCommerce' );
    }

    /**
     * Check if WooCommerce integration is available (WC active + Premium).
     *
     * @return bool
     */
    public static function is_available() {
        return self::is_active() && PFG_Features::is_premium();
    }

    /**
     * Get all product categories.
     *
     * @return array
     */
    public static function get_categories() {
        if ( ! self::is_active() ) {
            return array();
        }

        $categories = get_terms( array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ) );

        if ( is_wp_error( $categories ) ) {
            return array();
        }

        $result = array();
        foreach ( $categories as $category ) {
            $result[] = array(
                'id'    => $category->term_id,
                'name'  => $category->name,
                'slug'  => $category->slug,
                'count' => $category->count,
            );
        }

        return $result;
    }

    /**
     * Get products for gallery display.
     *
     * @param array $args Query arguments.
     * @return array
     */
    public static function get_products( $args = array() ) {
        if ( ! self::is_active() ) {
            return array();
        }

        $defaults = array(
            'categories'     => array(),
            'limit'          => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'include_hidden' => false,
        );

        $args = wp_parse_args( $args, $defaults );

        // Build WC product query
        $query_args = array(
            'status'  => 'publish',
            'limit'   => $args['limit'],
            'orderby' => $args['orderby'],
            'order'   => $args['order'],
        );

        // Filter by categories
        if ( ! empty( $args['categories'] ) ) {
            $query_args['category'] = $args['categories'];
        }

        // Visibility
        if ( ! $args['include_hidden'] ) {
            $query_args['visibility'] = 'visible';
        }

        $products = wc_get_products( $query_args );

        return self::format_products_for_gallery( $products, $args );
    }

    /**
     * Format WooCommerce products for gallery display.
     *
     * @param array $products WC_Product objects.
     * @param array $args     Display arguments including 'image_size'.
     * @return array
     */
    private static function format_products_for_gallery( $products, $args = array() ) {
        $items = array();
        
        // Get the requested image size, default to 'large'
        $image_size = isset( $args['image_size'] ) ? $args['image_size'] : 'large';

        foreach ( $products as $product ) {
            $image_id = $product->get_image_id();
            
            // Skip products without images
            if ( ! $image_id ) {
                continue;
            }

            // Get product categories for filtering
            $categories = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'slugs' ) );
            if ( is_wp_error( $categories ) ) {
                $categories = array();
            }

            // Get image URL at requested size
            $thumbnail = wp_get_attachment_image_url( $image_id, $image_size );
            $full      = wp_get_attachment_image_url( $image_id, 'full' );

            $item = array(
                'id'          => $product->get_id(),
                'image_id'    => $image_id, // Add image attachment ID for srcset
                'type'        => 'product',
                'title'       => $product->get_name(),
                'description' => $product->get_short_description(),
                'thumbnail'   => $thumbnail ? $thumbnail : '',
                'full'        => $full ? $full : '',
                'link'        => $product->get_permalink(),
                'filters'     => $categories,
                'product'     => array(
                    'price'         => $product->get_price_html(),
                    'regular_price' => wc_price( $product->get_regular_price() ),
                    'sale_price'    => $product->get_sale_price() ? wc_price( $product->get_sale_price() ) : '',
                    'on_sale'       => $product->is_on_sale(),
                    'in_stock'      => $product->is_in_stock(),
                    'stock_status'  => $product->get_stock_status(),
                    'add_to_cart'   => $product->add_to_cart_url(),
                    'type'          => $product->get_type(),
                ),
            );

            $items[] = $item;
        }

        return $items;
    }

    /**
     * Get filter buttons for WooCommerce categories.
     *
     * @param array $category_ids Selected category IDs.
     * @return array
     */
    public static function get_filter_buttons( $category_ids = array() ) {
        if ( ! self::is_active() ) {
            return array();
        }

        // If specific categories selected, only show those
        if ( ! empty( $category_ids ) ) {
            $categories = get_terms( array(
                'taxonomy'   => 'product_cat',
                'include'    => $category_ids,
                'hide_empty' => true,
            ) );
        } else {
            $categories = get_terms( array(
                'taxonomy'   => 'product_cat',
                'hide_empty' => true,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ) );
        }

        if ( is_wp_error( $categories ) ) {
            return array();
        }

        $filters = array();
        foreach ( $categories as $category ) {
            $filters[] = array(
                'slug' => $category->slug,
                'name' => $category->name,
            );
        }

        return $filters;
    }

    /**
     * Get categories for admin dropdown.
     *
     * @return array Key-value pairs for select options.
     */
    public static function get_categories_for_select() {
        $categories = self::get_categories();
        $options = array();

        foreach ( $categories as $cat ) {
            $options[ $cat['id'] ] = $cat['name'] . ' (' . $cat['count'] . ')';
        }

        return $options;
    }

    /**
     * Render product item HTML for gallery.
     *
     * @param array $item    Product item data.
     * @param array $settings Gallery settings.
     * @return string
     */
    public static function render_product_item( $item, $settings = array() ) {
        $defaults = array(
            'show_price'      => true,
            'show_sale_badge' => true,
            'show_title'      => true,
            'link_target'     => '_self',
        );

        $settings = wp_parse_args( $settings, $defaults );

        $classes = array( 'pfg-item', 'pfg-product-item' );
        $classes[] = implode( ' ', array_map( function( $f ) {
            return 'filter-' . sanitize_html_class( $f );
        }, $item['filters'] ) );

        if ( $item['product']['on_sale'] ) {
            $classes[] = 'pfg-on-sale';
        }

        if ( ! $item['product']['in_stock'] ) {
            $classes[] = 'pfg-out-of-stock';
        }

        ob_start();
        ?>
        <div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-id="<?php echo esc_attr( $item['id'] ); ?>">
            <div class="pfg-item-inner">
                <a href="<?php echo esc_url( $item['link'] ); ?>" target="<?php echo esc_attr( $settings['link_target'] ); ?>" class="pfg-item-link">
                    <img src="<?php echo esc_url( $item['thumbnail'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" class="pfg-item-image" loading="lazy">
                    
                    <?php if ( $settings['show_sale_badge'] && $item['product']['on_sale'] ) : ?>
                        <span class="pfg-sale-badge"><?php esc_html_e( 'Sale!', 'portfolio-filter-gallery' ); ?></span>
                    <?php endif; ?>
                    
                    <div class="pfg-item-overlay">
                        <?php if ( $settings['show_title'] ) : ?>
                            <h3 class="pfg-item-title"><?php echo esc_html( $item['title'] ); ?></h3>
                        <?php endif; ?>
                        
                        <?php if ( $settings['show_price'] ) : ?>
                            <span class="pfg-product-price"><?php echo wp_kses_post( $item['product']['price'] ); ?></span>
                        <?php endif; ?>
                        
                        <span class="pfg-view-product"><?php esc_html_e( 'View Product', 'portfolio-filter-gallery' ); ?></span>
                    </div>
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
