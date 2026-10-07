<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Cleans up plugin options and transients.
 * Gallery posts and their meta are preserved so users
 * can re-activate the plugin without data loss.
 *
 * @package Portfolio_Filter_Gallery
 */

// If uninstall not called from WordPress, die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options.
delete_option( 'pfg_global_settings' );
delete_option( 'pfg_db_version' );
delete_option( 'pfg_migration_status' );
delete_option( 'pfg_migration_log' );
delete_option( 'pfg_last_backup' );
delete_option( 'pfg_last_backup_date' );
delete_option( 'pfg_filters_legacy_backup' );
delete_option( 'pfg_installed_version' );
delete_option( 'pfg_previous_version' );
delete_option( 'pfg_version_timestamp' );
delete_option( 'pfg_tour_completed' );
delete_option( 'pfg_show_tour' );
delete_option( 'pfg_wizard_completed' );
delete_option( 'pfg_wizard_redirect' );
delete_option( 'pfg_filters' );
delete_option( 'pfg_first_installed_version' );
delete_option( 'pfg_migrated_version' );
delete_option( 'awl_portfolio_filter_gallery_categories' );
delete_option( 'awl_pfg_plugin_install_date' );

// Clean up transients.
global $wpdb;
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time cleanup on uninstall.
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
		$wpdb->esc_like( '_transient_pfg_' ) . '%'
	)
);
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time cleanup on uninstall.
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
		$wpdb->esc_like( '_transient_timeout_pfg_' ) . '%'
	)
);

// Clean up backup files directory.
$upload_dir = wp_upload_dir();
$backup_dir = $upload_dir['basedir'] . '/pfg-backups';
if ( is_dir( $backup_dir ) ) {
	global $wp_filesystem;
	if ( empty( $wp_filesystem ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
	}
	if ( $wp_filesystem ) {
		$wp_filesystem->delete( $backup_dir, true );
	}
}
