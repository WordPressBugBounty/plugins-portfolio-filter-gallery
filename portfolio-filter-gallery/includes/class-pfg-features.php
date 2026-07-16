<?php
/**
 * Feature availability helper.
 *
 * @package    Portfolio_Filter_Gallery
 * @subpackage Portfolio_Filter_Gallery/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class for managing feature availability.
 * All features in this plugin are free and fully available.
 */
class PFG_Features {

    /**
     * Check if a feature is available.
     * All features are available in this free plugin.
     *
     * @param string $feature Feature key.
     * @return bool Always true.
     */
    public static function is_available( $feature ) {
        return true;
    }

    /**
     * Get feature limit (if any).
     *
     * @param string $feature Feature key.
     * @return int|null Null means no limit.
     */
    public static function get_limit( $feature ) {
        return null;
    }

    /**
     * Check if premium version is active.
     *
     * @return bool Always false in free version.
     */
    public static function is_premium() {
        return defined( 'PFG_PREMIUM' ) && PFG_PREMIUM === true;
    }

    /**
     * Get upgrade URL.
     *
     * @param string $utm_source Optional UTM source.
     * @return string
     */
    public static function get_upgrade_url( $utm_source = '' ) {
        $url = 'https://awplife.com/wordpress-plugins/portfolio-filter-gallery-wordpress-plugin/';
        
        if ( $utm_source ) {
            $url = add_query_arg( array(
                'utm_source'   => $utm_source,
                'utm_medium'   => 'plugin',
                'utm_campaign' => 'pfg-upgrade',
            ), $url );
        }

        return $url;
    }
}
