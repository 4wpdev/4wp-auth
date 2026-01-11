<?php
/**
 * Authentication Manager
 *
 * @package ForWP\Auth\Auth
 */

namespace ForWP\Auth\Auth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main authentication manager class
 */
class AuthManager {

	/**
	 * Plugin instance
	 *
	 * @var AuthManager
	 */
	private static $instance = null;

	/**
	 * Registered providers
	 *
	 * @var array
	 */
	private $providers = [];

	/**
	 * Get plugin instance
	 *
	 * @return AuthManager
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
		$this->register_providers();
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Register OAuth providers
	 */
	private function register_providers() {
		$providers = [
			'gmail' => '\ForWP\Auth\Providers\Gmail',
			// Other providers will be added later
			// 'facebook'   => '\ForWP\Auth\Providers\Facebook',
			// 'instagram'  => '\ForWP\Auth\Providers\Instagram',
			// 'tiktok'     => '\ForWP\Auth\Providers\TikTok',
		];

		foreach ( $providers as $id => $class ) {
			if ( class_exists( $class ) ) {
				$this->providers[ $id ] = $class::get_instance();
			}
		}
	}

	/**
	 * Get provider by ID
	 *
	 * @param string $provider_id Provider ID.
	 * @return object|null
	 */
	public function get_provider( $provider_id ) {
		return $this->providers[ $provider_id ] ?? null;
	}

	/**
	 * Get all registered providers
	 *
	 * @return array
	 */
	public function get_providers() {
		return $this->providers;
	}

	/**
	 * Enqueue frontend scripts
	 */
	public function enqueue_scripts() {
		if ( is_user_logged_in() ) {
			return;
		}

		wp_enqueue_script(
			'forwp-auth',
			FORWP_AUTH_PLUGIN_URL . 'assets/js/auth.js',
			[ 'jquery' ],
			FORWP_AUTH_VERSION,
			true
		);

		wp_enqueue_style(
			'forwp-auth',
			FORWP_AUTH_PLUGIN_URL . 'assets/css/auth.css',
			[],
			FORWP_AUTH_VERSION
		);

		wp_localize_script(
			'forwp-auth',
			'forwpAuth',
			[
				'apiUrl' => rest_url( 'forwp-auth/v1/' ),
				'nonce'   => wp_create_nonce( 'forwp_auth_nonce' ),
			]
		);
	}
}

