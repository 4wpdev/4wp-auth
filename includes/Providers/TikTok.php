<?php
/**
 * TikTok OAuth Provider
 *
 * @package ForWP\Auth\Providers
 */

namespace ForWP\Auth\Providers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TikTok provider class
 */
class TikTok extends BaseProvider {

	/**
	 * Provider instance
	 *
	 * @var TikTok
	 */
	private static $instance = null;

	/**
	 * Authorization endpoint
	 *
	 * @var string
	 */
	protected $authorization_endpoint = 'https://www.tiktok.com/v2/auth/authorize/';

	/**
	 * Token endpoint
	 *
	 * @var string
	 */
	protected $token_endpoint = 'https://open.tiktokapis.com/v2/oauth/token/';

	/**
	 * User info endpoint
	 *
	 * @var string
	 */
	protected $user_info_endpoint = 'https://open.tiktokapis.com/v2/user/info/';

	/**
	 * Get provider instance
	 *
	 * @return TikTok
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
		$this->provider_id   = 'tiktok';
		$this->provider_name = 'TikTok';
		$this->client_id     = $this->get_option( 'client_key' );
		$this->client_secret = $this->get_option( 'client_secret' );
		$this->redirect_uri  = $this->get_redirect_uri();
		$this->scopes        = [ 'user.info.basic' ];
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
		set_transient( 'forwp_auth_tiktok_state_' . $state, $state, 600 );

		$params = [
			'client_key'    => $this->client_id,
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
			$stored_state = get_transient( 'forwp_auth_tiktok_state_' . $state );
			if ( $stored_state !== $state ) {
				return new \WP_Error( 'invalid_state', __( 'Invalid state parameter', '4wp-auth' ) );
			}
			delete_transient( 'forwp_auth_tiktok_state_' . $state );
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
			'id'         => $user_info['open_id'] ?? $user_info['data']['user']['open_id'] ?? '',
			'email'      => '', // TikTok doesn't provide email by default
			'username'   => $user_info['data']['user']['display_name'] ?? '',
			'name'       => $user_info['data']['user']['display_name'] ?? '',
			'avatar'     => $user_info['data']['user']['avatar_url'] ?? '',
		];

		// Generate email from username if not available
		if ( empty( $user_data['email'] ) ) {
			$user_data['email'] = $user_data['username'] . '@tiktok.local';
		}

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
		$response = wp_remote_post(
			$this->token_endpoint,
			[
				'headers' => [
					'Content-Type' => 'application/x-www-form-urlencoded',
				],
				'body' => [
					'client_key'    => $this->client_id,
					'client_secret' => $this->client_secret,
					'code'          => $code,
					'grant_type'    => 'authorization_code',
					'redirect_uri'  => $this->redirect_uri,
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			return new \WP_Error( 'oauth_error', $body['error_description'] ?? $body['error']['message'] ?? 'OAuth error' );
		}

		return $body['data'] ?? $body;
	}

	/**
	 * Get user info from provider
	 *
	 * @param string $access_token Access token.
	 * @return array|WP_Error
	 */
	protected function get_user_info( $access_token ) {
		$response = wp_remote_post(
			$this->user_info_endpoint,
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type'  => 'application/json',
				],
				'body' => wp_json_encode(
					[
						'fields' => [ 'open_id', 'union_id', 'avatar_url', 'display_name', 'username' ],
					]
				),
			]
		);

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
	 * Get redirect URI
	 *
	 * @return string
	 */
	protected function get_redirect_uri() {
		return home_url( '/wp-json/forwp-auth/v1/callback/tiktok' );
	}
}





