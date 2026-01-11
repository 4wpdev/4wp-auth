<?php
/**
 * Settings Page Template
 *
 * @package ForWP\Auth\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Handle form submission
if ( isset( $_POST['submit'] ) && check_admin_referer( 'forwp_auth_settings' ) ) {
	update_option( 'forwp_auth_gmail_client_id', sanitize_text_field( $_POST['forwp_auth_gmail_client_id'] ?? '' ) );
	update_option( 'forwp_auth_gmail_client_secret', sanitize_text_field( $_POST['forwp_auth_gmail_client_secret'] ?? '' ) );

	echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved.', '4wp-auth' ) . '</p></div>';
}
?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<form method="post" action="">
		<?php wp_nonce_field( 'forwp_auth_settings' ); ?>

		<h2><?php esc_html_e( 'Gmail', '4wp-auth' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="forwp_auth_gmail_client_id"><?php esc_html_e( 'Client ID', '4wp-auth' ); ?></label>
				</th>
				<td>
					<input type="text" id="forwp_auth_gmail_client_id" name="forwp_auth_gmail_client_id" value="<?php echo esc_attr( get_option( 'forwp_auth_gmail_client_id', '' ) ); ?>" class="regular-text" />
					<p class="description">
						<?php esc_html_e( 'Get your Client ID from', '4wp-auth' ); ?> 
						<a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Google Cloud Console', '4wp-auth' ); ?></a>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="forwp_auth_gmail_client_secret"><?php esc_html_e( 'Client Secret', '4wp-auth' ); ?></label>
				</th>
				<td>
					<input type="password" id="forwp_auth_gmail_client_secret" name="forwp_auth_gmail_client_secret" value="<?php echo esc_attr( get_option( 'forwp_auth_gmail_client_secret', '' ) ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Redirect URI', '4wp-auth' ); ?></th>
				<td>
					<code id="redirect-uri-display"><?php echo esc_html( home_url( '/wp-json/forwp-auth/v1/callback/gmail' ) ); ?></code>
					<button type="button" class="button button-small" onclick="navigator.clipboard.writeText(document.getElementById('redirect-uri-display').textContent); alert('Copied!');" style="margin-left: 10px;">
						<?php esc_html_e( 'Copy', '4wp-auth' ); ?>
					</button>
					<p class="description">
						<strong><?php esc_html_e( 'IMPORTANT:', '4wp-auth' ); ?></strong> 
						<?php esc_html_e( 'Copy the URL above and add it to Google Cloud Console → Credentials → Authorized redirect URIs. The URL must be EXACTLY the same (including port if applicable).', '4wp-auth' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Usage', '4wp-auth' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Shortcode', '4wp-auth' ); ?></th>
				<td>
					<code>[forwp_auth_login provider="gmail"]</code>
					<p class="description"><?php esc_html_e( 'Add this shortcode to any page or in the WordPress editor', '4wp-auth' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'HTML Code', '4wp-auth' ); ?></th>
				<td>
					<code>&lt;button class="forwp-auth-btn" data-provider="gmail"&gt;Sign in with Gmail&lt;/button&gt;</code>
					<p class="description"><?php esc_html_e( 'Or add HTML code directly to the theme template', '4wp-auth' ); ?></p>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>
</div>

