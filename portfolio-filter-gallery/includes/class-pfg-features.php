<?php
/**
 * Feature availability helper for freemium model.
 *
 * @package    Portfolio_Filter_Gallery
 * @subpackage Portfolio_Filter_Gallery/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class for managing feature availability between Free and Premium versions.
 */
class PFG_Features {

    /**
     * Feature tiers.
     */
    const FREE = 'free';
    const PREMIUM = 'premium';

    /**
     * Feature definitions with their tiers and limits.
     *
     * @var array
     */
    private static $features = array(
        // Layout features
        'layout_grid'       => array( 'tier' => self::FREE ),
        'layout_masonry'    => array( 'tier' => self::FREE ),
        'layout_justified'  => array( 'tier' => self::PREMIUM ),
        'layout_packed'     => array( 'tier' => self::PREMIUM ),
        
        // Filter features
        'basic_filtering'   => array( 'tier' => self::FREE ),
        'multi_level'       => array( 'tier' => self::PREMIUM ),
        'and_or_logic'      => array( 'tier' => self::PREMIUM ),
        'filter_url_sync'   => array( 'tier' => self::PREMIUM ),
        
        // Pagination
        'pagination'        => array( 'tier' => self::PREMIUM ),
        'load_more'         => array( 'tier' => self::PREMIUM ),
        'infinite_scroll'   => array( 'tier' => self::PREMIUM ),
        
        // Lightbox
        'lightbox_basic'    => array( 'tier' => self::FREE ),
        'lightbox_fancybox' => array( 'tier' => self::FREE ),
        'lightbox_photoswipe' => array( 'tier' => self::FREE ),
        
        // Integrations
        'woocommerce'       => array( 'tier' => self::PREMIUM ),
        'video_galleries'   => array( 'tier' => self::PREMIUM ),
        'social_sharing'    => array( 'tier' => self::PREMIUM ),
        
        // Other features
        'search'            => array( 'tier' => self::FREE ),
        'sorting'           => array( 'tier' => self::PREMIUM ),
        'deep_linking'      => array( 'tier' => self::PREMIUM ),
        'watermark'         => array( 'tier' => self::PREMIUM ),
        'custom_css'        => array( 'tier' => self::FREE ),
        'templates'         => array( 'tier' => self::FREE, 'limit' => 3 ),
        'gutenberg_block'   => array( 'tier' => self::FREE ),
    );

    /**
     * Setting definitions with their tiers and limits.
     *
     * @var array
     */
    private static $settings = array(
        // Columns
        'columns_desktop'  => array( 'tier' => self::FREE, 'free_min' => 2, 'free_max' => 4, 'premium_min' => 1, 'premium_max' => 8 ),
        'columns_tablet'   => array( 'tier' => self::FREE, 'free_min' => 1, 'free_max' => 3, 'premium_min' => 1, 'premium_max' => 6 ),
        'columns_mobile'   => array( 'tier' => self::FREE, 'free_min' => 1, 'free_max' => 2, 'premium_min' => 1, 'premium_max' => 4 ),
        
        // Filters
        'max_filters'      => array( 'tier' => self::FREE, 'free_limit' => 5, 'premium_limit' => -1 ), // -1 = unlimited
        
        // Hover effects
        'hover_effect'     => array( 
            'tier' => self::FREE, 
            'free_options' => array( 'fade', 'slide-up', 'zoom' ),
            'premium_options' => array( 'fade', 'slide-up', 'zoom', 'flip', 'rotate', 'blur', 'grayscale', 'shine', '3d-tilt', 'curtain' )
        ),
        
        // Pagination settings (premium only)
        'pagination_enabled'    => array( 'tier' => self::PREMIUM ),
        'pagination_type'       => array( 'tier' => self::PREMIUM ),
        'items_per_page'        => array( 'tier' => self::PREMIUM ),
        
        // Sorting settings (premium only)
        'sorting_enabled'       => array( 'tier' => self::PREMIUM ),
        'sort_options'          => array( 'tier' => self::PREMIUM ),
        
        // Multi-level filter settings (premium only)
        'hierarchical_filters'  => array( 'tier' => self::PREMIUM ),
        'filter_logic'          => array( 'tier' => self::PREMIUM ),
        
        // Deep linking (premium only)
        'url_sync_enabled'      => array( 'tier' => self::PREMIUM ),
        
        // WooCommerce settings (premium only)
        'woo_enabled'           => array( 'tier' => self::PREMIUM ),
        'woo_source'            => array( 'tier' => self::PREMIUM ),
        'woo_show_price'        => array( 'tier' => self::PREMIUM ),
        'woo_show_cart'         => array( 'tier' => self::PREMIUM ),
        
        // Social sharing settings (premium only)
        'social_enabled'        => array( 'tier' => self::PREMIUM ),
        'social_networks'       => array( 'tier' => self::PREMIUM ),
    );

    /**
     * Check if premium version is active.
     *
     * @return bool
     */
    public static function is_premium() {
        return defined( 'PFG_PREMIUM' ) && PFG_PREMIUM === true;
    }

    /**
     * Check if a feature is available.
     *
     * @param string $feature Feature name.
     * @return bool
     */
    public static function is_available( $feature ) {
        if ( ! isset( self::$features[ $feature ] ) ) {
            return true; // Unknown features are available by default
        }

        $feature_data = self::$features[ $feature ];

        if ( $feature_data['tier'] === self::FREE ) {
            return true;
        }

        return self::is_premium();
    }

    /**
     * Check if a setting is available.
     *
     * @param string $setting Setting name.
     * @return bool
     */
    public static function is_setting_available( $setting ) {
        if ( ! isset( self::$settings[ $setting ] ) ) {
            return true; // Unknown settings are available by default
        }

        $setting_data = self::$settings[ $setting ];

        if ( $setting_data['tier'] === self::FREE ) {
            return true;
        }

        return self::is_premium();
    }

    /**
     * Get setting limit for current tier.
     *
     * @param string $setting Setting name.
     * @param string $limit_type Type of limit (min, max, limit, options).
     * @return mixed
     */
    public static function get_setting_limit( $setting, $limit_type ) {
        if ( ! isset( self::$settings[ $setting ] ) ) {
            return null;
        }

        $setting_data = self::$settings[ $setting ];
        $prefix = self::is_premium() ? 'premium_' : 'free_';
        
        $key = $prefix . $limit_type;
        
        if ( isset( $setting_data[ $key ] ) ) {
            return $setting_data[ $key ];
        }

        // Fallback to non-prefixed key
        if ( isset( $setting_data[ $limit_type ] ) ) {
            return $setting_data[ $limit_type ];
        }

        return null;
    }

    /**
     * Get available options for a setting.
     *
     * @param string $setting Setting name.
     * @return array
     */
    public static function get_available_options( $setting ) {
        return self::get_setting_limit( $setting, 'options' ) ?? array();
    }

    /**
     * Check if an option is available for a setting.
     *
     * @param string $setting Setting name.
     * @param string $option  Option value.
     * @return bool
     */
    public static function is_option_available( $setting, $option ) {
        $options = self::get_available_options( $setting );
        
        if ( empty( $options ) ) {
            return true; // No restrictions
        }

        return in_array( $option, $options, true );
    }

    /**
     * Get upgrade URL.
     *
     * @param string $utm_source Optional UTM source.
     * @return string
     */
    public static function get_upgrade_url( $utm_source = '' ) {
        $url = 'https://awplife.com/account/signup/portfolio-filter-gallery';
        
        if ( $utm_source ) {
            $url = add_query_arg( array(
                'utm_source'   => $utm_source,
                'utm_medium'   => 'plugin',
                'utm_campaign' => 'pfg-upgrade',
            ), $url );
        }

        return $url;
    }

    /**
     * Get Pro badge HTML.
     *
     * @param bool $with_link Include upgrade link.
     * @return string
     */
    public static function get_pro_badge( $with_link = true ) {
        $badge = '<span class="pfg-pro-badge">PRO</span>';
        
        if ( $with_link && ! self::is_premium() ) {
            $url = self::get_upgrade_url( 'pro-badge' );
            $badge = '<a href="' . esc_url( $url ) . '" target="_blank" class="pfg-pro-badge-link">' . $badge . '</a>';
        }

        return $badge;
    }

    /**
     * Get locked overlay HTML.
     *
     * @param string $feature_name Display name of the feature.
     * @return string
     */
    public static function get_locked_overlay( $feature_name = '' ) {
        if ( self::is_premium() ) {
            return '';
        }

        $url = self::get_upgrade_url( 'locked-overlay' );
        
        ob_start();
        ?>
        <div class="pfg-locked-overlay">
            <div class="pfg-locked-content">
                <span class="dashicons dashicons-lock"></span>
                <p><?php echo esc_html( $feature_name ? sprintf( __( '%s is a Premium feature', 'portfolio-filter-gallery' ), $feature_name ) : __( 'Premium Feature', 'portfolio-filter-gallery' ) ); ?></p>
                <a href="<?php echo esc_url( $url ); ?>" target="_blank" class="button button-primary pfg-upgrade-btn">
                    <?php esc_html_e( 'Upgrade to Unlock', 'portfolio-filter-gallery' ); ?>
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render a setting row with Pro badge if needed.
     *
     * @param string   $setting      Setting key.
     * @param string   $label        Setting label.
     * @param callable $render_input Callback to render the input.
     * @param string   $description  Optional description.
     */
    public static function render_setting_row( $setting, $label, $render_input, $description = '' ) {
        $is_available = self::is_setting_available( $setting );
        $row_class = $is_available ? '' : 'pfg-setting-locked';
        ?>
        <tr class="pfg-setting-row <?php echo esc_attr( $row_class ); ?>">
            <th scope="row">
                <?php echo esc_html( $label ); ?>
                <?php if ( ! $is_available ) echo self::get_pro_badge(); ?>
            </th>
            <td>
                <div class="pfg-setting-input-wrap <?php echo $is_available ? '' : 'pfg-input-disabled'; ?>">
                    <?php 
                    if ( $is_available ) {
                        call_user_func( $render_input );
                    } else {
                        echo '<div class="pfg-locked-input">';
                        call_user_func( $render_input );
                        echo self::get_locked_overlay( $label );
                        echo '</div>';
                    }
                    ?>
                    <?php if ( $description ) : ?>
                        <p class="description"><?php echo esc_html( $description ); ?></p>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php
    }

    /**
     * Get all features with their availability status.
     *
     * @return array
     */
    public static function get_all_features() {
        $result = array();
        
        foreach ( self::$features as $key => $data ) {
            $result[ $key ] = array_merge( $data, array(
                'available' => self::is_available( $key ),
            ) );
        }

        return $result;
    }

    /**
     * Get all settings with their availability status.
     *
     * @return array
     */
    public static function get_all_settings() {
        $result = array();
        
        foreach ( self::$settings as $key => $data ) {
            $result[ $key ] = array_merge( $data, array(
                'available' => self::is_setting_available( $key ),
            ) );
        }

        return $result;
    }
}
