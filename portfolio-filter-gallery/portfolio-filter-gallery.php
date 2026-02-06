<?php
/**
 * Plugin Name: Portfolio Filter Gallery
 * Plugin URI: https://awplife.com/
 * Description: Create stunning filterable portfolio galleries with masonry layouts, lightbox, and drag-drop management.
 * Version: 2.0.5
 * Author: A WP Life
 * Author URI: https://awplife.com/
 * License: GPLv2 or later
 * Text Domain: portfolio-filter-gallery
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Plugin constants - wrapped with defined() checks to prevent conflicts
 * if both free and premium versions are accidentally activated
 */
if ( ! defined( 'PFG_VERSION' ) ) {
    define( 'PFG_VERSION', '2.0.5' );
}
if ( ! defined( 'PFG_PLUGIN_FILE' ) ) {
    define( 'PFG_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'PFG_PLUGIN_PATH' ) ) {
    define( 'PFG_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'PFG_PLUGIN_URL' ) ) {
    define( 'PFG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'PFG_PLUGIN_BASENAME' ) ) {
    define( 'PFG_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

// Legacy constants for backward compatibility
if ( ! defined( 'PFG_PLUGIN_VER' ) ) {
    define( 'PFG_PLUGIN_VER', PFG_VERSION );
}
if ( ! defined( 'PFG_PLUGIN_NAME' ) ) {
    define( 'PFG_PLUGIN_NAME', 'Portfolio Filter Gallery' );
}
if ( ! defined( 'PFG_PLUGIN_SLUG' ) ) {
    define( 'PFG_PLUGIN_SLUG', 'awl_filter_gallery' );
}
if ( ! defined( 'PFG_PLUGIN_DIR' ) ) {
    define( 'PFG_PLUGIN_DIR', PFG_PLUGIN_PATH );
}

/**
 * The code that runs during plugin activation.
 * Named with _free suffix to avoid conflict with premium version.
 */
function pfg_free_activate() {
    require_once PFG_PLUGIN_PATH . 'includes/class-pfg-activator.php';
    PFG_Activator::activate();
    
    // Onboarding tour
    require_once PFG_PLUGIN_PATH . 'includes/class-pfg-onboarding-tour.php';
    PFG_Onboarding_Tour::activate();
}

/**
 * The code that runs during plugin deactivation.
 * Named with _free suffix to avoid conflict with premium version.
 */
function pfg_free_deactivate() {
    require_once PFG_PLUGIN_PATH . 'includes/class-pfg-deactivator.php';
    PFG_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'pfg_free_activate' );
register_deactivation_hook( __FILE__, 'pfg_free_deactivate' );

/**
 * Check if Premium version is active - if so, Free version defers silently.
 * Both versions can be active, but Premium takes priority and handles everything.
 * This allows users to upgrade without manually deactivating Free first.
 */
function pfg_free_check_premium_conflict() {
    // If Premium version's constant is defined, Premium is already loaded
    // Free version will silently defer - no error, no warning
    if ( defined( 'PFG_PREMIUM' ) && PFG_PREMIUM ) {
        // Premium is active and handling everything - Free stays quiet
        return true;
    }
    return false;
}

/**
 * Begins execution of the plugin.
 * Hooked to plugins_loaded with priority 20 to allow Premium (priority 10) to load first.
 * Named with _free suffix to avoid conflict with premium version.
 */
function pfg_free_run() {
    // Stop if Premium is already active (it loads first with priority 10)
    if ( pfg_free_check_premium_conflict() ) {
        return;
    }
    
    require_once PFG_PLUGIN_PATH . 'includes/class-portfolio-filter-gallery.php';
    $plugin = new Portfolio_Filter_Gallery();
    $plugin->run();
}
// Use plugins_loaded with priority 20 so Premium (priority 10) can set PFG_PREMIUM first
add_action( 'plugins_loaded', 'pfg_free_run', 20 );