<?php

/**
 * Plugin Name: cagov Gutenberg Blocks
 * Plugin URI: 
 * Description: Gutenberg blocks for cagov, CA Design System
 * Author: Office of Digital Innovation
 * Author URI: https://digital.ca.gov
 * Version: 1.0.18
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: ca-design-system
 *
 * @package  CADesignSystem
 * @author   Office of Digital Innovation <info@digital.ca.gov>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://github.com/cagov/ca-design-system-gutenberg-blocks#readme
 */

if (!defined('ABSPATH')) {
	exit;
}

// Constants.
define('CAGOV_GUTENBERG_BLOCKS__VERSION', '1.0.18');
define('CAGOV_GUTENBERG_BLOCKS__BLOCKS_DIR_PATH', plugin_dir_path(__FILE__));
define('CAGOV_GUTENBERG_BLOCKS__ADMIN_URL', plugin_dir_url(__FILE__));
define('CAGOV_GUTENBERG_BLOCKS__FILE', __FILE__);

/**
 * Plugin API/Action Reference
 * Actions Run During a Typical Request
 *
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference#Actions_Run_During_a_Typical_Request
 */
add_action('init', 'cagov_init');

/**
 * Plugin API/Action Reference
 * Actions Run During an Admin Page Request.
 *
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference#Actions_Run_During_an_Admin_Page_Request
 */
add_action('admin_init', 'cagov_admin_init');

/* Include Gutenberg files and categories */
require_once CAGOV_GUTENBERG_BLOCKS__BLOCKS_DIR_PATH . '/blocks/gutenberg.php';

/**
 * CADesignSystem Init
 * Triggered before any other hook when a user accesses the admin area.
 * Note, this does not just run on user-facing admin screens.
 * It runs on admin-ajax.php and admin-post.php as well.
 *
 * @category add_action( 'init', 'cagov_init' );
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/admin_initgst
 * 
 * @return void
 */
function cagov_init()
{
	/* Include functionality */
	foreach (glob(__DIR__ . '/includes/*.php') as $file) {
		require_once $file;
	}

	/* Add content menu navigation */
	register_nav_menu('content-menu', 'Content Menu');
	register_nav_menu('social-media-links', 'Social Media Links');
	register_nav_menu('statewide-footer-menu', 'Statewide Footer Menu');
}

/**
 * Admin Init
 *
 * Triggered before any other hook when a user accesses the admin area.
 * Note, this does not just run on user-facing admin screens.
 * It runs on admin-ajax.php and admin-post.php as well.
 *
 * @category add_action( 'init', 'cagov_admin_init' );
 * @link   https://codex.wordpress.org/Plugin_API/Action_Reference/admin_init
 * @return void
 */
function cagov_admin_init()
{
	include_once CAGOV_GUTENBERG_BLOCKS__BLOCKS_DIR_PATH . '/core/class-ca-design-system-gutenberg-blocks-plugin-update.php';
}

if (!class_exists('CADesignSystemGutenbergBlocks_Plugin_Templates_Loader')) {
	include_once CAGOV_GUTENBERG_BLOCKS__BLOCKS_DIR_PATH . '/includes/class-ca-design-system-gutenberg-blocks-templates.php';
}

CADesignSystemGutenbergBlocks_Plugin_Templates_Loader::get_instance();
