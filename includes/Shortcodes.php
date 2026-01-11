<?php
/**
 * Shortcodes
 *
 * @package ForWP\Auth
 */

namespace ForWP\Auth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcodes class
 */
class Shortcodes {

	/**
	 * Initialize shortcodes
	 */
	public static function init() {
		add_shortcode( 'forwp_auth_login', [ __CLASS__, 'render_login_button' ] );
		add_shortcode( 'forwp_auth_buttons', [ __CLASS__, 'render_auth_buttons' ] );
	}

	/**
	 * Render login button shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function render_login_button( $atts ) {
		$atts = shortcode_atts(
			[
				'provider' => 'gmail',
				'text'     => '',
			],
			$atts,
			'forwp_auth_login'
		);

		$provider = sanitize_text_field( $atts['provider'] );
		$text     = ! empty( $atts['text'] ) ? sanitize_text_field( $atts['text'] ) : __( 'Sign in with Gmail', '4wp-auth' );

		// Check if user is logged in
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			return '<div class="forwp-auth-logged-in">' . esc_html__( 'You are logged in as:', '4wp-auth' ) . ' <strong>' . esc_html( $current_user->display_name ) . '</strong></div>';
		}

		return sprintf(
			'<button class="forwp-auth-btn" data-provider="%s">%s</button>',
			esc_attr( $provider ),
			esc_html( $text )
		);
	}

	/**
	 * Render all auth buttons
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function render_auth_buttons( $atts ) {
		$atts = shortcode_atts(
			[
				'providers' => 'gmail',
			],
			$atts,
			'forwp_auth_buttons'
		);

		// Check if user is logged in
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			return '<div class="forwp-auth-logged-in">' . esc_html__( 'You are logged in as:', '4wp-auth' ) . ' <strong>' . esc_html( $current_user->display_name ) . '</strong></div>';
		}

		$providers = array_map( 'trim', explode( ',', $atts['providers'] ) );
		$output    = '<div class="forwp-auth-container">';

		foreach ( $providers as $provider ) {
			$provider = sanitize_text_field( $provider );
			$text     = self::get_provider_text( $provider );

			$output .= sprintf(
				'<button class="forwp-auth-btn" data-provider="%s">%s</button>',
				esc_attr( $provider ),
				esc_html( $text )
			);
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Get provider button text
	 *
	 * @param string $provider Provider ID.
	 * @return string
	 */
	private static function get_provider_text( $provider ) {
		$texts = [
			'gmail'     => __( 'Sign in with Gmail', '4wp-auth' ),
			'facebook'  => __( 'Sign in with Facebook', '4wp-auth' ),
			'instagram' => __( 'Sign in with Instagram', '4wp-auth' ),
			'tiktok'    => __( 'Sign in with TikTok', '4wp-auth' ),
		];

		return $texts[ $provider ] ?? __( 'Sign in', '4wp-auth' );
	}
}

