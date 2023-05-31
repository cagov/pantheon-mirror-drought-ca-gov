<?php
/**
 * Admin Notices
 *
 * @author Tijmen Smit
 * @since  2.0.0
*/

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WP_MCM_Notices' ) ) {

	/**
	 * Handle the meta boxes.
	 *
	 * @since 2.0.0
	 */
	class WP_MCM_Notices {

		/**
		 * Holds the notices.
		 * @since 2.0.0
		 * @var array
		 */
		private $notices = array();

		public function __construct() {

			$this->notices = get_option( WP_MCM_NOTICE_OPTION );

		}

		/**
		 * Show one or more notices.
		 * 
		 * @since 2.0.0
		 * @return void
		 */
		public function TEST_render_notices() {
			$this->debugMP('pr', 'WP_MCM_Notices::render_notices ========================================> SAVE NOTICE!!! this->notices: ', $this->notices);
			$class = 'error';
			$notice_msg = 'TESTING';
			return '<div class="' . esc_attr( $class ) . '">' . $notice_msg . '</div>';
		}

		/**
		 * Show one or more notices.
		 * 
		 * @since 2.0.0
		 * @return void
		 */
		public function render_notices() {

			global $wp_mcm_plugin;

			// $this->debugMP('pr', 'WP_MCM_Notices::wp_mcm_show ========================================> SAVE NOTICE!!! this->notices: ', $this->notices);

			$notice_output = '';

			$this->notices = get_option( WP_MCM_NOTICE_OPTION );

			if ( !empty( $this->notices ) ) {
				$allowed_html = array(
					'a' => array(
						'href'       => array(),
						'id'         => array(),
						'class'      => array(),
						'data-nonce' => array(),
						'title'      => array(),
						'target'     => array()
					),
					'p'  => array(),
					'br' => array(),
					'em' => array(),
					'strong' => array(
						'class' => array()
					),
					'span' => array(
						'class' => array()
					),
					'ul' => array(
						'class' => array()
					),
					'li' => array(
						'class' => array()
					)
				);

				if ( wp_mcm_is_multi_array( $this->notices ) ) {
					foreach ( $this->notices as $k => $notice ) {
						$notice_output .= $this->create_notice_content( $notice, $allowed_html );
					}
				} else {
					$notice_output .= $this->create_notice_content( $this->notices, $allowed_html );
				}

				// Empty the notices.
				$this->notices = array();
				update_option( WP_MCM_NOTICE_OPTION, $this->notices );
			}

			return $notice_output;
		}

		/**
		 * Create the content shown in the notice.
		 * 
		 * @since 2.0.0
		 * @param array $notice
		 * @param array $allowed_html
		 */
		public function create_notice_content( $notice, $allowed_html ) {

			$notice_content = '';

			// $class = ( 'update' == $notice['type'] ) ? 'updated' : 'error';
			$class = 'notice notice-' . $notice['type'];

			if ( isset( $notice['multiline'] ) && $notice['multiline'] ) {
				$notice_msg = wp_kses( $notice['message'], $allowed_html );
			} else {
				$notice_msg = '<p>' . wp_kses( $notice['message'], $allowed_html ) . '</p>';
			}

			$notice_content .= '<div class="' . esc_attr( $class ) . '">' . $notice_msg . '</div>';
			return $notice_content;
		}

		/**
		 * Save the notice.
		 * 
		 * @since 2.0.0
		 * @param  string $type      The type of notice, either 'update' or 'error'
		 * @param  string $message   The user message
		 * @param  bool   $multiline True if the message contains multiple lines ( used with notices created in add-ons ).
		 * @return void
		 */
		public function save( $type, $message, $multiline = false ) {

			$current_notices = get_option( WP_MCM_NOTICE_OPTION );
			// $this->debugMP('msg', 'WP_MCM_Notices::save ========================================> SAVE NOTICE!!! message[' . $type . ']: ' . $message);

			$new_notice = array(
				'type'    => $type,
				'message' => $message
			);

			if ( $multiline ) {
				$new_notice['multiline'] = true;
			}

			if ( $current_notices ) {
				if ( !wp_mcm_is_multi_array( $current_notices ) ) {
					$current_notices = array( $current_notices );
				}

				array_push( $current_notices, $new_notice );

				update_option( WP_MCM_NOTICE_OPTION, $current_notices );
			} else {
				update_option( WP_MCM_NOTICE_OPTION, $new_notice );
			}
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