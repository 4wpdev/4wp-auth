<?php
/**
 * Plugin Name: 4wp-auth
 * Plugin URI: https://4wp.dev/plugins/4wp-auth
 * Description: Social authentication plugin for WordPress - Gmail, Facebook, Instagram, TikTok
 * Version: 1.0.2
 * Author: 4wp.dev
 * Author URI: https://4wp.dev
 * License: MIT / GPL-2.0-or-later
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: 4wp-auth
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin constants
define( 'FORWP_AUTH_VERSION', '1.0.1' );
define( 'FORWP_AUTH_PLUGIN_FILE', __FILE__ );
define( 'FORWP_AUTH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FORWP_AUTH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FORWP_AUTH_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Load Composer autoloader (if available)
if ( file_exists( FORWP_AUTH_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once FORWP_AUTH_PLUGIN_DIR . 'vendor/autoload.php';
} else {
	// Fallback: use simple autoloader
	require_once FORWP_AUTH_PLUGIN_DIR . 'includes/autoload.php';
}

// Initialize plugin
add_action( 'plugins_loaded', function() {
	if ( class_exists( 'ForWP\Auth\Core\Extension' ) ) {
		\ForWP\Auth\Core\Extension::get_instance();
	}
}, 10 );

