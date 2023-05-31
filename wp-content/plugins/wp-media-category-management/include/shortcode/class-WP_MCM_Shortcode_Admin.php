<?php
/**
 * WP Media Category Management Shortcode Admin class
 * 
 * @since  2.0.0
 * @author DeBAAT
 */

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WP_MCM_Shortcode_Admin' ) ) {

	class WP_MCM_Shortcode_Admin {

		/**
		 * Class constructor
		 */
		function __construct() {

			$this->includes();
			$this->init();
		}

		/**
		 * Include the required files.
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function includes() {

			WP_MCM_create_object( 'WP_MCM_Shortcode_Options',               'include/shortcode/' );
		}

		/**
		 * Init the required classes.
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function init() {

			global $wpdb;
			global $wp_mcm_options;

			$this->debugMP('msg',__FUNCTION__.' started.');

		}

		/**
		 * Render the shortcode section.
		 * 
		 * @since 2.0.0
		 * @return html contents
		 */
		public function wp_mcm_render_section_shortcode() {

			$this->debugMP('msg',__FUNCTION__.' started.');

			// Set some defaults
			$render_output  = '';

			// Render the Shortcode Section
			$render_output .= $this->render_shortcode_section();

			return $render_output;

		}

		/**
		 * Render the shortcode section.
		 * 
		 * @since 2.0.0
		 * @return html contents
		 */
		function render_shortcode_section() {
			$this->debugMP('msg',__FUNCTION__);

			global $wp_mcm_shortcode;

			$render_options_output  = '';

			$render_options_output .= '<div class="wrap">';

			// Title
			$render_options_output .= '<h3>';
			$render_options_output .= __('Shortcodes', 'wp-media-category-management');
			$render_options_output .= '</h3>';

			// Show message if needed
			if (isset($_REQUEST['settings-updated'])) {
				$render_options_output .= '<div id="sip-return-message" class="updated">';
				$render_options_output .= __('Your Settings have been saved.', 'wp-media-category-management');
				$render_options_output .= '</div>';
			}

			$render_options_output .= '<p>';
			$render_options_output .= __('This page shows the shortcodes supported by the WP Media Category Management plugin:', 'wp-media-category-management');
			$render_options_output .= '</p>';

			// Start MCM Shortcode table
			$render_options_output .= '<div id="wp_mcm_table_wrapper">';
			$render_options_output .= '<table id="wp_mcm_shortcodes_table" class="wp-mcm wp-list-table widefat fixed posts" cellspacing="0">';

			// Start MCM Shortcode table head
			$render_options_output .= '<thead>';
			$render_options_output .= '<tr class="wp_mcm_shortcodes_row">';
			$render_options_output .= '<th class="wp_mcm_shortcodes_cell" width="15%"><code>[SHORTCODE]</code>&nbsp;</th>';
			$render_options_output .= '<th class="wp_mcm_shortcodes_cell" width="25%">' . __('Description', 'wp-media-category-management') . '&nbsp;</th>';
			$render_options_output .= '<th class="wp_mcm_shortcodes_cell">' . __('Parameters', 'wp-media-category-management') . '&nbsp;</th>';
			$render_options_output .= '</tr>';
			$render_options_output .= '</thead>'; // id='wp_mcm_shortcodes_table'

			// Start MCM Shortcode table body
			$render_options_output .= '<tbody>';
			$row_style = 'even';
			$wp_mcm_shortcodes = $wp_mcm_shortcode->get_wp_mcm_shortcodes();
			foreach ($wp_mcm_shortcodes as $shortcode) {
				$row_style = ($row_style == 'odd') ? 'even' : 'odd';
				$render_options_output .= '<tr class="wp_mcm_shortcodes_row ' . $row_style . '">';
				$render_options_output .= '<td class="wp_mcm_shortcodes_cell"><code>[' . $shortcode['label'] . ']</code></td>';
				$render_options_output .= '<td class="wp_mcm_shortcodes_cell">' . $shortcode['description'] . '</td>';
				$render_options_output .= '<td class="wp_mcm_shortcodes_cell">' . $shortcode['parameters'] . '</td>';
				$render_options_output .= '</tr>';
			}
			$render_options_output .= '</tbody>'; // id='wp_mcm_shortcodes_table'

			$render_options_output .= '</table>'; // id='wp_mcm_shortcodes_table'
			$render_options_output .= '</div>'; // id='wp_mcm_table_wrapper'

			// Show some additional info
			$render_options_output .= '<p>';
			$render_options_output .= sprintf (__('<br/>This plugin supports the shortcodes as shown above as well as in all upper and lower case.', 'wp-media-category-management'));
			$render_options_output .= sprintf (__('<br/>More information can be found <a href="%s">here</a>.', 'wp-media-category-management'), WP_MCM_LINK);
			$render_options_output .= sprintf (__('<br/>If you have suggestions to improve this plugin, please leave a comment on this same <a href="%s">page</a>.', 'wp-media-category-management'), WP_MCM_LINK);
			$render_options_output .= '</p>';

			$render_options_output .= '</div>'; // for class="wrap"

			return $render_options_output;
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
