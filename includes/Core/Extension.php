<?php
/**
 * Plugin Extension Core
 *
 * @package ForWP\Auth\Core
 */

namespace ForWP\Auth\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Extension class
 */
class Extension {

	/**
	 * Plugin instance
	 *
	 * @var Extension
	 */
	private static $instance = null;

	/**
	 * Get plugin instance
	 *
	 * @return Extension
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
	 * Initialize plugin
	 */
	private function init() {
		// Initialize modules
		add_action( 'init', [ $this, 'load_modules' ], 1 );
	}

	/**
	 * Load plugin modules
	 */
	public function load_modules() {
		// Load Auth Manager
		if ( class_exists( '\ForWP\Auth\Auth\AuthManager' ) ) {
			\ForWP\Auth\Auth\AuthManager::get_instance();
		}

		// Load API routes
		if ( class_exists( '\ForWP\Auth\API\Routes' ) ) {
			\ForWP\Auth\API\Routes::get_instance();
		}

		// Load Admin panel
		if ( is_admin() && class_exists( '\ForWP\Auth\Admin\Menu' ) ) {
			\ForWP\Auth\Admin\Menu::get_instance();
		}

		// Load Shortcodes
		if ( class_exists( '\ForWP\Auth\Shortcodes' ) ) {
			\ForWP\Auth\Shortcodes::init();
		}

		// Run migrations
		if ( class_exists( '\ForWP\Auth\Storage\Migrations' ) ) {
			\ForWP\Auth\Storage\Migrations::run();
		}
	}
}

