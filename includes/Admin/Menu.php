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
		// Admin-only hooks
		if ( is_admin() ) {
			add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
			add_action( 'admin_init', [ $this, 'register_settings' ] );
			add_action( 'admin_init', [ $this, 'redirect_subscribers_from_admin' ] );
		}
		
		// Hide toolbar for subscribers if enabled (works on frontend and admin)
		add_filter( 'show_admin_bar', [ $this, 'hide_toolbar_for_subscribers' ], 999 );
	}

	/**
	 * Hide toolbar for subscribers
	 *
	 * @param bool $show Whether to show the toolbar.
	 * @return bool
	 */
	public function hide_toolbar_for_subscribers( $show ) {
		// Only hide if the option is enabled
		if ( get_option( 'forwp_auth_hide_toolbar_subscribers', '0' ) !== '1' ) {
			return $show;
		}

		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			return $show;
		}

		// Get current user
		$user = wp_get_current_user();
		
		// Hide toolbar for users with subscriber role only
		if ( ! empty( $user->roles ) && in_array( 'subscriber', $user->roles, true ) && ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		return $show;
	}

	/**
	 * Redirect subscribers from admin area
	 */
	public function redirect_subscribers_from_admin() {
		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			return;
		}

		// Get redirect URL from settings
		$redirect_url = get_option( 'forwp_auth_subscriber_redirect_url', '' );
		if ( empty( $redirect_url ) ) {
			return;
		}

		// Skip redirect for AJAX requests
		if ( wp_doing_ajax() ) {
			return;
		}

		// Skip redirect for admin-ajax.php and admin-post.php
		global $pagenow;
		if ( in_array( $pagenow, array( 'admin-ajax.php', 'admin-post.php' ), true ) ) {
			return;
		}

		// Get current user
		$user = wp_get_current_user();
		
		// Check if user is subscriber and can't edit posts
		if ( ! empty( $user->roles ) && in_array( 'subscriber', $user->roles, true ) && ! current_user_can( 'edit_posts' ) ) {
			// Convert relative URL to absolute if needed
			if ( ! empty( $redirect_url ) && strpos( $redirect_url, 'http' ) !== 0 ) {
				// It's a relative path, prepend home_url
				$redirect_url = home_url( $redirect_url );
			}

			// Skip redirect if already on the redirect URL
			$current_url = home_url( $_SERVER['REQUEST_URI'] );
			$redirect_url_parsed = wp_parse_url( $redirect_url );
			$current_url_parsed = wp_parse_url( $current_url );
			
			// Compare paths to avoid redirect loops
			if ( ! empty( $redirect_url_parsed['path'] ) && ! empty( $current_url_parsed['path'] ) ) {
				if ( $redirect_url_parsed['path'] === $current_url_parsed['path'] ) {
					return;
				}
			}

			// Redirect to custom URL
			wp_safe_redirect( $redirect_url );
			exit;
		}
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( '4wp Auth', '4wp-auth' ),
			__( '4wp Auth', '4wp-auth' ),
			'manage_options',
			'forwp-auth',
			[ SettingsPage::class, 'render' ],
			'dashicons-groups',
			30
		);

		add_submenu_page(
			'forwp-auth',
			__( 'Settings', '4wp-auth' ),
			__( 'Settings', '4wp-auth' ),
			'manage_options',
			'forwp-auth',
			[ SettingsPage::class, 'render' ]
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		// Provider enabled settings
		register_setting( 'forwp_auth_settings', 'forwp_auth_provider_enabled_gmail' );
		register_setting( 'forwp_auth_settings', 'forwp_auth_provider_enabled_facebook' );
		register_setting( 'forwp_auth_settings', 'forwp_auth_provider_enabled_instagram' );
		register_setting( 'forwp_auth_settings', 'forwp_auth_provider_enabled_tiktok' );

		// Gmail settings
		register_setting( 'forwp_auth_settings', 'forwp_auth_gmail_client_id' );
		register_setting( 'forwp_auth_settings', 'forwp_auth_gmail_client_secret' );
		
		// Facebook settings
		register_setting( 'forwp_auth_settings', 'forwp_auth_facebook_app_id' );
		register_setting( 'forwp_auth_settings', 'forwp_auth_facebook_app_secret' );
		
		// Instagram settings (uses same Facebook App)
		register_setting( 'forwp_auth_settings', 'forwp_auth_instagram_app_id' );
		register_setting( 'forwp_auth_settings', 'forwp_auth_instagram_app_secret' );

		// TikTok settings
		register_setting( 'forwp_auth_settings', 'forwp_auth_tiktok_client_id' );
		register_setting( 'forwp_auth_settings', 'forwp_auth_tiktok_client_secret' );

		// General settings
		register_setting( 'forwp_auth_settings', 'forwp_auth_hide_toolbar_subscribers' );
		register_setting( 'forwp_auth_settings', 'forwp_auth_subscriber_redirect_url' );
		register_setting( 'forwp_auth_settings', 'forwp_auth_woocommerce_integration' );
	}
}

