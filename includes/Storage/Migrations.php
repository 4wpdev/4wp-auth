<?php
/**
 * Database Migrations
 *
 * @package ForWP\Auth\Storage
 */

namespace ForWP\Auth\Storage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Migrations class
 */
class Migrations {

	/**
	 * Run migrations
	 */
	public static function run() {
		$version = get_option( 'forwp_auth_db_version', '0' );

		if ( version_compare( $version, FORWP_AUTH_VERSION, '<' ) ) {
			self::create_tables();
			update_option( 'forwp_auth_db_version', FORWP_AUTH_VERSION );
		}
	}

	/**
	 * Create database tables
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'forwp_auth_users';

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id bigint(20) UNSIGNED NOT NULL,
			provider varchar(50) NOT NULL,
			provider_user_id varchar(255) NOT NULL,
			access_token text,
			refresh_token text,
			token_expires datetime DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY provider_user (provider, provider_user_id),
			KEY user_id (user_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}

