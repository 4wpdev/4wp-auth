<?php
/**
 * Admin Menu
 *
 * @package ForWP\Auth\Admin
 */

namespace ForWP\Auth\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Menu class
 */
class Menu {

	/**
	 * Plugin instance
	 *
	 * @var Menu
	 */
	private static $instance = null;

	/**
	 * Get plugin instance
	 *
	 * @return Menu
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize
	 */
	private function init() {
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_options_page(
			__( '4wp Auth Settings', '4wp-auth' ),
			__( '4wp Auth', '4wp-auth' ),
			'manage_options',
			'forwp-auth',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		// Register only Gmail settings for now
		register_setting( 'forwp_auth_settings', 'forwp_auth_gmail_client_id' );
		register_setting( 'forwp_auth_settings', 'forwp_auth_gmail_client_secret' );
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		include FORWP_AUTH_PLUGIN_DIR . 'includes/Admin/settings-page.php';
	}
}

