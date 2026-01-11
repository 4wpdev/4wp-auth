<?php
/**
 * Base OAuth Provider
 *
 * @package ForWP\Auth\Providers
 */

namespace ForWP\Auth\Providers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base provider class
 */
abstract class BaseProvider {

	/**
	 * Provider ID
	 *
	 * @var string
	 */
	protected $provider_id;

	/**
	 * Provider name
	 *
	 * @var string
	 */
	protected $provider_name;

	/**
	 * Client ID
	 *
	 * @var string
	 */
	protected $client_id;

	/**
	 * Client Secret
	 *
	 * @var string
	 */
	protected $client_secret;

	/**
	 * Redirect URI
	 *
	 * @var string
	 */
	protected $redirect_uri;

	/**
	 * Scopes
	 *
	 * @var array
	 */
	protected $scopes = [];

	/**
	 * Check if provider is enabled
	 *
	 * @return bool
	 */
	abstract public function is_enabled();

	/**
	 * Get authorization URL
	 *
	 * @return string
	 */
	abstract public function get_authorization_url();

	/**
	 * Handle OAuth callback
	 *
	 * @param string $code Authorization code.
	 * @return array|WP_Error User data or error.
	 */
	abstract public function handle_callback( $code );

	/**
	 * Get user info from provider
	 *
	 * @param string $access_token Access token.
	 * @return array|WP_Error User data or error.
	 */
	abstract protected function get_user_info( $access_token );

	/**
	 * Create or update WordPress user
	 *
	 * @param array $user_data User data from provider.
	 * @return int|WP_Error User ID or error.
	 */
	protected function create_or_update_user( $user_data ) {
		$email = $user_data['email'] ?? '';

		if ( empty( $email ) ) {
			return new \WP_Error( 'no_email', __( 'Email is required', '4wp-auth' ) );
		}

		$user = get_user_by( 'email', $email );

		if ( $user ) {
			// Update existing user meta
			update_user_meta( $user->ID, 'forwp_auth_provider', $this->provider_id );
			update_user_meta( $user->ID, 'forwp_auth_provider_id', $user_data['id'] );
			return $user->ID;
		}

		// Create new user
		$username = $this->generate_username( $user_data );
		$password = wp_generate_password( 24, true, true );

		$user_id = wp_create_user(
			$username,
			$password,
			$email
		);

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		// Set user meta
		update_user_meta( $user_id, 'forwp_auth_provider', $this->provider_id );
		update_user_meta( $user_id, 'forwp_auth_provider_id', $user_data['id'] );
		update_user_meta( $user_id, 'first_name', $user_data['first_name'] ?? '' );
		update_user_meta( $user_id, 'last_name', $user_data['last_name'] ?? '' );

		if ( ! empty( $user_data['avatar'] ) ) {
			update_user_meta( $user_id, 'forwp_auth_avatar', $user_data['avatar'] );
		}

		return $user_id;
	}

	/**
	 * Generate unique username
	 *
	 * @param array $user_data User data.
	 * @return string
	 */
	protected function generate_username( $user_data ) {
		$base = $user_data['username'] ?? $user_data['name'] ?? $user_data['email'];
		$base = sanitize_user( $base, true );
		$username = $base;
		$counter = 1;

		while ( username_exists( $username ) ) {
			$username = $base . $counter;
			$counter++;
		}

		return $username;
	}

	/**
	 * Get option value
	 *
	 * @param string $key Option key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	protected function get_option( $key, $default = '' ) {
		return get_option( "forwp_auth_{$this->provider_id}_{$key}", $default );
	}
}

