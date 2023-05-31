<?php
/**
 * WP Media Category Management Media class
 * 
 * @since  2.0.0
 * @author DeBAAT
 */

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WP_MCM_Media' ) ) {

	class WP_MCM_Media {

		/**
		 * Class constructor
		 */
		function __construct() {

			$this->includes();
			$this->add_hooks_and_filters();
		}

		/**
		 * Include the required files.
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function includes() {

		}

		/**
		 * Add cross-element hooks & filters.
		 *
		 */
		function add_hooks_and_filters() {
			// $this->debugMP('msg', __FUNCTION__ . ' started.');

		}

		/**
		 * Simplify the plugin debugMP interface.
		 *
		 * Typical start of function call: $this->debugMP('msg',__FUNCTION__);
		 *
		 * @param string $type
		 * @param string $hdr
		 * @param string $msg
		 */
		function debugMP($type,$hdr,$msg='') {
			if (($type === 'msg') && ($msg!=='')) {
				$msg = esc_html($msg);
			}
			if (($hdr!=='')) {   // Adding __CLASS__ to non-empty hdr
				$hdr = __CLASS__ . '::' . $hdr;
			}

			WP_MCM_debugMP($type,$hdr,$msg,NULL,NULL,true);
		}

	}

}
