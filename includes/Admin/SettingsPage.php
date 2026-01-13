<?php
/**
 * Settings Page
 *
 * @package ForWP\Auth\Admin
 */

namespace ForWP\Auth\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings page
 */
class SettingsPage {

	/**
	 * Render settings page
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Get current tab (before form submission to preserve it)
		$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'social-networks';

		// Handle form submission
		if ( isset( $_POST['forwp_auth_settings'] ) && check_admin_referer( 'forwp_auth_settings', 'forwp_auth_nonce' ) ) {
			self::save_settings();
			
			// Redirect to preserve the current tab
			$redirect_url = add_query_arg(
				array(
					'page' => 'forwp-auth',
					'tab'  => $current_tab,
					'settings-updated' => 'true',
				),
				admin_url( 'admin.php' )
			);
			wp_safe_redirect( $redirect_url );
			exit;
		}

		// Show success message if redirected after save
		if ( isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'] ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved!', '4wp-auth' ) . '</p></div>';
		}

		// Define tabs
		$tabs = array(
			'social-networks' => __( 'Social Networks', '4wp-auth' ),
			'general'         => __( 'General Settings', '4wp-auth' ),
		);

		?>
		<div class="wrap">
			<h1><?php echo esc_html__( '4wp Auth Settings', '4wp-auth' ); ?></h1>

			<nav class="nav-tab-wrapper wp-clearfix" aria-label="<?php esc_attr_e( 'Secondary menu', '4wp-auth' ); ?>">
				<?php
				foreach ( $tabs as $tab_key => $tab_label ) {
					$active_class = ( $current_tab === $tab_key ) ? ' nav-tab-active' : '';
					printf(
						'<a href="%s" class="nav-tab%s" aria-current="%s">%s</a>',
						esc_url( add_query_arg( array( 'tab' => $tab_key ), admin_url( 'admin.php?page=forwp-auth' ) ) ),
						esc_attr( $active_class ),
						( $current_tab === $tab_key ? 'page' : 'false' ),
						esc_html( $tab_label )
					);
				}
				?>
			</nav>

			<form method="post" action="">
				<?php wp_nonce_field( 'forwp_auth_settings', 'forwp_auth_nonce' ); ?>
				<input type="hidden" name="forwp_auth_settings" value="1">
				<input type="hidden" name="current_tab" value="<?php echo esc_attr( $current_tab ); ?>">

				<?php if ( 'social-networks' === $current_tab ) : ?>
					<?php self::render_social_networks_tab(); ?>
					<?php self::render_general_tab( true ); // Render as hidden to preserve data ?>
				<?php elseif ( 'general' === $current_tab ) : ?>
					<?php self::render_social_networks_tab( true ); // Render as hidden to preserve data ?>
					<?php self::render_general_tab(); ?>
				<?php endif; ?>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render social networks tab
	 *
	 * @param bool $hidden Whether to render as hidden fields.
	 */
	private static function render_social_networks_tab( $hidden = false ) {
		$providers = array(
			'gmail'     => __( 'Gmail', '4wp-auth' ),
			'facebook'  => __( 'Facebook', '4wp-auth' ),
			'instagram' => __( 'Instagram', '4wp-auth' ),
			'tiktok'    => __( 'TikTok', '4wp-auth' ),
		);

		if ( $hidden ) {
			// Render all fields as hidden to preserve data when saving from other tab
			?>
			<div style="display: none;">
			<?php
			$providers = array( 'gmail', 'facebook', 'instagram', 'tiktok' );
			foreach ( $providers as $provider_key ) {
				$enabled = get_option( 'forwp_auth_provider_enabled_' . $provider_key, '0' );
				?>
				<input type="hidden" name="forwp_auth_provider_enabled_<?php echo esc_attr( $provider_key ); ?>" value="<?php echo esc_attr( $enabled ); ?>">
				<?php
			}
			// Also include credentials as hidden
			$credentials = array(
				'forwp_auth_gmail_client_id',
				'forwp_auth_gmail_client_secret',
				'forwp_auth_facebook_app_id',
				'forwp_auth_facebook_app_secret',
				'forwp_auth_instagram_app_id',
				'forwp_auth_instagram_app_secret',
				'forwp_auth_tiktok_client_id',
				'forwp_auth_tiktok_client_secret',
			);
			foreach ( $credentials as $credential ) {
				$value = get_option( $credential, '' );
				?>
				<input type="hidden" name="<?php echo esc_attr( $credential ); ?>" value="<?php echo esc_attr( $value ); ?>">
				<?php
			}
			?>
			</div>
			<?php
			return;
		}
		?>
		<h2><?php esc_html_e( 'Social Networks Settings', '4wp-auth' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'Select the social networks you want to enable for user authentication.', '4wp-auth' ); ?>
		</p>

		<table class="form-table">
			<?php foreach ( $providers as $provider_key => $provider_name ) : ?>
				<?php
				$enabled = get_option( 'forwp_auth_provider_enabled_' . $provider_key, '0' );
				?>
				<tr>
					<th scope="row">
						<label for="forwp_auth_provider_enabled_<?php echo esc_attr( $provider_key ); ?>">
							<?php echo esc_html( $provider_name ); ?>
						</label>
					</th>
					<td>
						<label>
							<input 
								type="checkbox" 
								id="forwp_auth_provider_enabled_<?php echo esc_attr( $provider_key ); ?>" 
								name="forwp_auth_provider_enabled_<?php echo esc_attr( $provider_key ); ?>" 
								value="1" 
								<?php checked( $enabled, '1' ); ?>
							/>
							<?php printf( esc_html__( 'Enable %s', '4wp-auth' ), esc_html( $provider_name ) ); ?>
						</label>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>

		<h2><?php esc_html_e( 'Provider Credentials', '4wp-auth' ); ?></h2>

		<!-- Gmail Settings -->
		<h3><?php esc_html_e( 'Gmail', '4wp-auth' ); ?></h3>
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
					<code id="redirect-uri-gmail"><?php echo esc_html( home_url( '/wp-json/forwp-auth/v1/callback/gmail' ) ); ?></code>
					<button type="button" class="button button-small" onclick="navigator.clipboard.writeText(document.getElementById('redirect-uri-gmail').textContent); alert('<?php esc_attr_e( 'Copied!', '4wp-auth' ); ?>');" style="margin-left: 10px;">
						<?php esc_html_e( 'Copy', '4wp-auth' ); ?>
					</button>
					<p class="description">
						<strong><?php esc_html_e( 'IMPORTANT:', '4wp-auth' ); ?></strong> 
						<?php esc_html_e( 'Copy the URL above and add it to Google Cloud Console → Credentials → Authorized redirect URIs. The URL must be EXACTLY the same (including port if applicable).', '4wp-auth' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<!-- Facebook Settings -->
		<h3><?php esc_html_e( 'Facebook', '4wp-auth' ); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="forwp_auth_facebook_app_id"><?php esc_html_e( 'App ID', '4wp-auth' ); ?></label>
				</th>
				<td>
					<input type="text" id="forwp_auth_facebook_app_id" name="forwp_auth_facebook_app_id" value="<?php echo esc_attr( get_option( 'forwp_auth_facebook_app_id', '' ) ); ?>" class="regular-text" />
					<p class="description">
						<?php esc_html_e( 'Get your App ID from', '4wp-auth' ); ?> 
						<a href="https://developers.facebook.com/apps/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Facebook Developers', '4wp-auth' ); ?></a>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="forwp_auth_facebook_app_secret"><?php esc_html_e( 'App Secret', '4wp-auth' ); ?></label>
				</th>
				<td>
					<input type="password" id="forwp_auth_facebook_app_secret" name="forwp_auth_facebook_app_secret" value="<?php echo esc_attr( get_option( 'forwp_auth_facebook_app_secret', '' ) ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Redirect URI', '4wp-auth' ); ?></th>
				<td>
					<code id="redirect-uri-facebook"><?php echo esc_html( home_url( '/wp-json/forwp-auth/v1/callback/facebook' ) ); ?></code>
					<button type="button" class="button button-small" onclick="navigator.clipboard.writeText(document.getElementById('redirect-uri-facebook').textContent); alert('<?php esc_attr_e( 'Copied!', '4wp-auth' ); ?>');" style="margin-left: 10px;">
						<?php esc_html_e( 'Copy', '4wp-auth' ); ?>
					</button>
					<p class="description">
						<strong><?php esc_html_e( 'IMPORTANT:', '4wp-auth' ); ?></strong> 
						<?php esc_html_e( 'Add this URL to Facebook App → Settings → Basic → Valid OAuth Redirect URIs', '4wp-auth' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<!-- Instagram Settings -->
		<h3><?php esc_html_e( 'Instagram', '4wp-auth' ); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="forwp_auth_instagram_app_id"><?php esc_html_e( 'App ID', '4wp-auth' ); ?></label>
				</th>
				<td>
					<input type="text" id="forwp_auth_instagram_app_id" name="forwp_auth_instagram_app_id" value="<?php echo esc_attr( get_option( 'forwp_auth_instagram_app_id', '' ) ); ?>" class="regular-text" />
					<p class="description">
						<?php esc_html_e( 'Instagram uses the same Facebook App. Get your App ID from', '4wp-auth' ); ?> 
						<a href="https://developers.facebook.com/apps/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Facebook Developers', '4wp-auth' ); ?></a>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="forwp_auth_instagram_app_secret"><?php esc_html_e( 'App Secret', '4wp-auth' ); ?></label>
				</th>
				<td>
					<input type="password" id="forwp_auth_instagram_app_secret" name="forwp_auth_instagram_app_secret" value="<?php echo esc_attr( get_option( 'forwp_auth_instagram_app_secret', '' ) ); ?>" class="regular-text" />
				</td>
			</tr>
		</table>

		<!-- TikTok Settings -->
		<h3><?php esc_html_e( 'TikTok', '4wp-auth' ); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="forwp_auth_tiktok_client_id"><?php esc_html_e( 'Client ID', '4wp-auth' ); ?></label>
				</th>
				<td>
					<input type="text" id="forwp_auth_tiktok_client_id" name="forwp_auth_tiktok_client_id" value="<?php echo esc_attr( get_option( 'forwp_auth_tiktok_client_id', '' ) ); ?>" class="regular-text" />
					<p class="description">
						<?php esc_html_e( 'Get your Client ID from', '4wp-auth' ); ?> 
						<a href="https://developers.tiktok.com/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'TikTok Developers', '4wp-auth' ); ?></a>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="forwp_auth_tiktok_client_secret"><?php esc_html_e( 'Client Secret', '4wp-auth' ); ?></label>
				</th>
				<td>
					<input type="password" id="forwp_auth_tiktok_client_secret" name="forwp_auth_tiktok_client_secret" value="<?php echo esc_attr( get_option( 'forwp_auth_tiktok_client_secret', '' ) ); ?>" class="regular-text" />
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Usage', '4wp-auth' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Shortcode', '4wp-auth' ); ?></th>
				<td>
					<code>[forwp_auth_login provider="gmail"]</code><br>
					<code>[forwp_auth_login provider="facebook"]</code>
					<p class="description"><?php esc_html_e( 'Add shortcode to any page or in the WordPress editor', '4wp-auth' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'HTML Code', '4wp-auth' ); ?></th>
				<td>
					<code>&lt;button class="forwp-auth-btn" data-provider="gmail"&gt;Sign in with Gmail&lt;/button&gt;</code><br>
					<code>&lt;button class="forwp-auth-btn" data-provider="facebook"&gt;Sign in with Facebook&lt;/button&gt;</code>
					<p class="description"><?php esc_html_e( 'Or add HTML code directly to the theme template', '4wp-auth' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render general settings tab
	 *
	 * @param bool $hidden Whether to render as hidden fields.
	 */
	private static function render_general_tab( $hidden = false ) {
		$hide_toolbar_subscribers = get_option( 'forwp_auth_hide_toolbar_subscribers', '0' );
		$subscriber_redirect_url = get_option( 'forwp_auth_subscriber_redirect_url', '' );
		$woocommerce_integration = get_option( 'forwp_auth_woocommerce_integration', '0' );
		$is_woocommerce_active = class_exists( 'WooCommerce' );

		if ( $hidden ) {
			// Render all fields as hidden to preserve data when saving from other tab
			?>
			<div style="display: none;">
				<input type="hidden" name="forwp_auth_hide_toolbar_subscribers" value="<?php echo esc_attr( $hide_toolbar_subscribers ); ?>">
				<input type="hidden" name="forwp_auth_subscriber_redirect_url" value="<?php echo esc_attr( $subscriber_redirect_url ); ?>">
				<input type="hidden" name="forwp_auth_woocommerce_integration" value="<?php echo esc_attr( $woocommerce_integration ); ?>">
			</div>
			<?php
			return;
		}
		?>
		<h2><?php esc_html_e( 'General Settings', '4wp-auth' ); ?></h2>

		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="forwp_auth_hide_toolbar_subscribers">
						<?php esc_html_e( 'WordPress Toolbar', '4wp-auth' ); ?>
					</label>
				</th>
				<td>
					<label>
						<input 
							type="checkbox" 
							id="forwp_auth_hide_toolbar_subscribers" 
							name="forwp_auth_hide_toolbar_subscribers" 
							value="1" 
							<?php checked( $hide_toolbar_subscribers, '1' ); ?>
						/>
						<?php esc_html_e( 'Hide WordPress toolbar for subscribers', '4wp-auth' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'If enabled, users with the "Subscriber" role will not see the WordPress admin bar at the top of the site.', '4wp-auth' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="forwp_auth_subscriber_redirect_url">
						<?php esc_html_e( 'Subscriber Redirect URL', '4wp-auth' ); ?>
					</label>
				</th>
				<td>
					<input 
						type="text" 
						id="forwp_auth_subscriber_redirect_url" 
						name="forwp_auth_subscriber_redirect_url" 
						value="<?php echo esc_attr( $subscriber_redirect_url ); ?>" 
						class="regular-text" 
						placeholder="/my-account"
					/>
					<p class="description">
						<?php esc_html_e( 'Redirect subscribers from /wp-admin/ to this URL. You can use a relative path (e.g., /my-account) or full URL (e.g., https://example.com/my-account). Leave empty to disable redirect.', '4wp-auth' ); ?>
					</p>
				</td>
			</tr>
			<?php if ( $is_woocommerce_active ) : ?>
			<tr>
				<th scope="row">
					<label for="forwp_auth_woocommerce_integration">
						<?php esc_html_e( 'WooCommerce Integration', '4wp-auth' ); ?>
					</label>
				</th>
				<td>
					<label>
						<input 
							type="checkbox" 
							id="forwp_auth_woocommerce_integration" 
							name="forwp_auth_woocommerce_integration" 
							value="1" 
							<?php checked( $woocommerce_integration, '1' ); ?>
						/>
						<?php esc_html_e( 'Enable WooCommerce integration', '4wp-auth' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'If enabled, social login buttons will be displayed on WooCommerce login and registration forms (My Account page).', '4wp-auth' ); ?>
					</p>
				</td>
			</tr>
			<?php else : ?>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'WooCommerce Integration', '4wp-auth' ); ?>
				</th>
				<td>
					<p class="description" style="color: #646970;">
						<?php esc_html_e( 'WooCommerce is not installed or activated. Install and activate WooCommerce to enable integration options.', '4wp-auth' ); ?>
					</p>
				</td>
			</tr>
			<?php endif; ?>
		</table>
		<?php
	}

	/**
	 * Save settings
	 */
	private static function save_settings() {
		// Save provider enabled settings
		$providers = array( 'gmail', 'facebook', 'instagram', 'tiktok' );
		foreach ( $providers as $provider ) {
			$option_name = 'forwp_auth_provider_enabled_' . $provider;
			$value = isset( $_POST[ $option_name ] ) ? '1' : '0';
			update_option( $option_name, $value );
		}

		// Gmail settings
		if ( isset( $_POST['forwp_auth_gmail_client_id'] ) ) {
			update_option( 'forwp_auth_gmail_client_id', sanitize_text_field( $_POST['forwp_auth_gmail_client_id'] ) );
		}
		if ( isset( $_POST['forwp_auth_gmail_client_secret'] ) ) {
			update_option( 'forwp_auth_gmail_client_secret', sanitize_text_field( $_POST['forwp_auth_gmail_client_secret'] ) );
		}

		// Facebook settings
		if ( isset( $_POST['forwp_auth_facebook_app_id'] ) ) {
			update_option( 'forwp_auth_facebook_app_id', sanitize_text_field( $_POST['forwp_auth_facebook_app_id'] ) );
		}
		if ( isset( $_POST['forwp_auth_facebook_app_secret'] ) ) {
			update_option( 'forwp_auth_facebook_app_secret', sanitize_text_field( $_POST['forwp_auth_facebook_app_secret'] ) );
		}

		// Instagram settings
		if ( isset( $_POST['forwp_auth_instagram_app_id'] ) ) {
			update_option( 'forwp_auth_instagram_app_id', sanitize_text_field( $_POST['forwp_auth_instagram_app_id'] ) );
		}
		if ( isset( $_POST['forwp_auth_instagram_app_secret'] ) ) {
			update_option( 'forwp_auth_instagram_app_secret', sanitize_text_field( $_POST['forwp_auth_instagram_app_secret'] ) );
		}

		// TikTok settings
		if ( isset( $_POST['forwp_auth_tiktok_client_id'] ) ) {
			update_option( 'forwp_auth_tiktok_client_id', sanitize_text_field( $_POST['forwp_auth_tiktok_client_id'] ) );
		}
		if ( isset( $_POST['forwp_auth_tiktok_client_secret'] ) ) {
			update_option( 'forwp_auth_tiktok_client_secret', sanitize_text_field( $_POST['forwp_auth_tiktok_client_secret'] ) );
		}

		// General settings
		$hide_toolbar_subscribers = isset( $_POST['forwp_auth_hide_toolbar_subscribers'] ) ? '1' : '0';
		update_option( 'forwp_auth_hide_toolbar_subscribers', $hide_toolbar_subscribers );

		if ( isset( $_POST['forwp_auth_subscriber_redirect_url'] ) ) {
			$redirect_url = sanitize_text_field( $_POST['forwp_auth_subscriber_redirect_url'] );
			// Allow relative paths or full URLs
			update_option( 'forwp_auth_subscriber_redirect_url', $redirect_url );
		}

		// WooCommerce integration
		$woocommerce_integration = isset( $_POST['forwp_auth_woocommerce_integration'] ) ? '1' : '0';
		update_option( 'forwp_auth_woocommerce_integration', $woocommerce_integration );
	}
}

