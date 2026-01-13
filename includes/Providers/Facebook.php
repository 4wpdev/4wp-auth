<?php
/**
 * Facebook OAuth Provider
 *
 * @package ForWP\Auth\Providers
 */

namespace ForWP\Auth\Providers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Facebook provider class
 */
class Facebook extends BaseProvider {

	/**
	 * Provider instance
	 *
	 * @var Facebook
	 */
	private static $instance = null;

	/**
	 * Authorization endpoint
	 *
	 * @var string
	 */
	protected $authorization_endpoint = 'https://www.facebook.com/v18.0/dialog/oauth';

	/**
	 * Token endpoint
	 *
	 * @var string
	 */
	protected $token_endpoint = 'https://graph.facebook.com/v18.0/oauth/access_token';

	/**
	 * User info endpoint
	 *
	 * @var string
	 */
	protected $user_info_endpoint = 'https://graph.facebook.com/v18.0/me';

	/**
	 * Get provider instance
	 *
	 * @return Facebook
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
		$this->provider_id   = 'facebook';
		$this->provider_name = 'Facebook';
		$this->client_id     = $this->get_option( 'app_id' );
		$this->client_secret = $this->get_option( 'app_secret' );
		$this->redirect_uri  = $this->get_redirect_uri();
		$this->scopes        = [ 'email', 'public_profile' ];
	}

	/**
	 * Check if provider is enabled
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return ! empty( $this->client_id ) && ! empty( $this->client_secret );
	}

	/**
	 * Get authorization URL
	 *
	 * @return string
	 */
	public function get_authorization_url() {
		$state = wp_generate_password( 32, false );
		set_transient( 'forwp_auth_facebook_state_' . $state, $state, 600 );

		$params = [
			'client_id'     => $this->client_id,
			'redirect_uri'  => $this->redirect_uri,
			'scope'         => implode( ',', $this->scopes ),
			'response_type' => 'code',
			'state'         => $state,
		];

		return $this->authorization_endpoint . '?' . http_build_query( $params );
	}

	/**
	 * Handle OAuth callback
	 *
	 * @param string $code Authorization code.
	 * @param string $state State parameter.
	 * @return array|WP_Error
	 */
	public function handle_callback( $code, $state = '' ) {
		// Verify state
		if ( ! empty( $state ) ) {
			$stored_state = get_transient( 'forwp_auth_facebook_state_' . $state );
			if ( $stored_state !== $state ) {
				return new \WP_Error( 'invalid_state', __( 'Invalid state parameter', '4wp-auth' ) );
			}
			delete_transient( 'forwp_auth_facebook_state_' . $state );
		}

		// Exchange code for token
		$token_response = $this->exchange_code_for_token( $code );

		if ( is_wp_error( $token_response ) ) {
			return $token_response;
		}

		$access_token = $token_response['access_token'];

		// Get user info
		$user_info = $this->get_user_info( $access_token );

		if ( is_wp_error( $user_info ) ) {
			return $user_info;
		}

		// Create or update user
		$user_data = [
			'id'         => $user_info['id'],
			'email'      => $user_info['email'] ?? '',
			'name'       => $user_info['name'] ?? '',
			'first_name' => $user_info['first_name'] ?? '',
			'last_name'  => $user_info['last_name'] ?? '',
			'avatar'     => $this->get_user_picture( $access_token, $user_info['id'] ),
		];

		$user_id = $this->create_or_update_user( $user_data );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		// Log in user
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id );

		return [
			'user_id' => $user_id,
			'user_data' => $user_data,
		];
	}

	/**
	 * Exchange authorization code for access token
	 *
	 * @param string $code Authorization code.
	 * @return array|WP_Error
	 */
	protected function exchange_code_for_token( $code ) {
		$redirect_uri = $this->redirect_uri;
		$url = add_query_arg(
			[
				'client_id'     => $this->client_id,
				'client_secret' => $this->client_secret,
				'redirect_uri'  => $redirect_uri,
				'code'          => $code,
			],
			$this->token_endpoint
		);

		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			return new \WP_Error( 'oauth_error', $body['error']['message'] ?? 'OAuth error' );
		}

		return $body;
	}

	/**
	 * Get user info from provider
	 *
	 * @param string $access_token Access token.
	 * @return array|WP_Error
	 */
	protected function get_user_info( $access_token ) {
		$url = add_query_arg(
			[
				'fields'       => 'id,name,email,first_name,last_name',
				'access_token' => $access_token,
			],
			$this->user_info_endpoint
		);

		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			return new \WP_Error( 'api_error', $body['error']['message'] ?? 'Unknown error' );
		}

		return $body;
	}

	/**
	 * Get user picture URL
	 *
	 * @param string $access_token Access token.
	 * @param string $user_id User ID.
	 * @return string
	 */
	protected function get_user_picture( $access_token, $user_id ) {
		$url = add_query_arg(
			[
				'type'         => 'large',
				'access_token' => $access_token,
			],
			"https://graph.facebook.com/v18.0/{$user_id}/picture"
		);

		$response = wp_remote_get( $url, [ 'redirection' => 0 ] );

		if ( ! is_wp_error( $response ) ) {
			$location = wp_remote_retrieve_header( $response, 'location' );
			if ( $location ) {
				return $location;
			}
		}

		return '';
	}

	/**
	 * Get redirect URI
	 *
	 * @return string
	 */
	protected function get_redirect_uri() {
		return home_url( '/wp-json/forwp-auth/v1/callback/facebook' );
	}
}





