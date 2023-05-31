<?php
/**
 * Handle the WP Media Category Management plugin Shortcode settings
 *
 * @author DeBAAT
 * @since  2.0.0
 */

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WP_MCM_Shortcode_Options' ) ) {
	
	class WP_MCM_Shortcode_Options {

		/**
		 * Parameters for handling the settable options for this plugin.
		 *
		 * @var mixed[] $options
		 */
		public  $mcm_settings_params = array();

		public function __construct() {

			// Get some settings
			$this->initialize();

		}

		public function initialize() {

			// Get some settings

		}

		/**
		 * Define the settings for options
		 * 
		 * @since 2.0.0
		 * @return html
		 */
		public function set_mcm_settings_params( $mcm_settings_params_input = false) {
			$this->debugMP('msg',__FUNCTION__.' started.');

			// Get some settings
			$this->mcm_settings_params = $mcm_settings_params_input;

			$this->set_mcm_settings_params_general();
			// $this->debugMP('pr',__FUNCTION__.' to return with mcm_settings_params:', $this->mcm_settings_params );

			return $this->mcm_settings_params;

		}

		/**
		 * Render the settings for user managed locations
		 * 
		 * @since 2.0.0
		 * @return html
		 */
		public function set_mcm_settings_params_general() {

			global $wp_mcm_options;

			$mcm_settings_params_section = WP_MCM_SECTION_SCO;
			$this->debugMP('msg',__FUNCTION__.' started for section ' . $mcm_settings_params_section );

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
