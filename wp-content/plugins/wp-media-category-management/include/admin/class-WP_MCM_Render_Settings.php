<?php
/**
 * Handle the WP Media Category Management plugin settings.
 *
 * @author DeBAAT
 * @since  2.0.0
 */

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WP_MCM_Render_Settings' ) ) {

	class WP_MCM_Render_Settings {

		/**
		 * Parameters for handling the settable options for this plugin.
		 *
		 * @var mixed[] $options
		 */
		public  $mcm_settings_params = array();

		public function __construct() {

			// Get some settings
			$this->initialize();
			$this->add_hooks_and_filters();

		}

		public function initialize() {

			// Get some settings

		}

		/**
		 * Add cross-element hooks & filters.
		 *
		 * Haven't yet moved all items to the AJAX and UI classes.
		 */
		function add_hooks_and_filters() {
			// $this->debugMP('msg', __FUNCTION__ . ' started.');

		}

		/**
		 * Get the values for parameters set via _POST
		 * 
		 * @since 2.0.0
		 * @return html
		 */
		public function get_post_values( $wp_mcm_params = array(), $wp_mcm_section = WP_MCM_SECTION_ALL ) {
			// $this->debugMP('msg',__FUNCTION__.' started.');
			// $this->debugMP('pr',__FUNCTION__.' started with _POST:', $_POST );

			$wp_mcm_post_values = array();

			// Check if the _POST values set
			//
			foreach ($wp_mcm_params as $section_name => $settings_params ) {
				if ( ($settings_params['section'] == $wp_mcm_section ) || (WP_MCM_SECTION_ALL == $wp_mcm_section )) {
					$settings_name = $settings_params['name'];
					switch ( $settings_params['type'] ) {
						case WP_MCM_SETTINGS_TYPE_CHECKBOX:
							// $this->debugMP('pr',__FUNCTION__.' found settings_params[' . $settings_name . ']:', $_POST[WP_MCM_SETTINGS_TYPE_CHECKBOX][$settings_name] );
							$wp_mcm_post_values[$settings_name] = isset( $_POST[WP_MCM_SETTINGS_TYPE_CHECKBOX][$settings_name] ) ? 1 : 0; 
							break;
						case WP_MCM_SETTINGS_TYPE_DROPDOWN:
							$wp_mcm_post_values[$settings_name] = isset( $_POST[WP_MCM_SETTINGS_TYPE_DROPDOWN][$settings_name] ) ? sanitize_key($_POST[WP_MCM_SETTINGS_TYPE_DROPDOWN][$settings_name]) : ''; 
							break;
						case WP_MCM_SETTINGS_TYPE_CUSTOM:
							$wp_mcm_post_values[$settings_name] = isset( $_POST[WP_MCM_SETTINGS_TYPE_CUSTOM][$settings_name] ) ? sanitize_text_field($_POST[WP_MCM_SETTINGS_TYPE_CUSTOM][$settings_name]) : ''; 
							break;
						case WP_MCM_SETTINGS_TYPE_ICONLIST:
							$wp_mcm_post_values[$settings_name] = isset( $_POST[WP_MCM_SETTINGS_TYPE_ICONLIST][$settings_name] ) ? esc_url($_POST[WP_MCM_SETTINGS_TYPE_ICONLIST][$settings_name]) : ''; 
							break;
						case WP_MCM_SETTINGS_TYPE_FILE:
							$wp_mcm_post_values[$settings_name] = isset( $_POST[WP_MCM_SETTINGS_TYPE_FILE][$settings_name] ) ? esc_url($_POST[WP_MCM_SETTINGS_TYPE_FILE][$settings_name]) : ''; 
							break;
						case WP_MCM_SETTINGS_TYPE_TEXT:
						case WP_MCM_SETTINGS_TYPE_TEXTAREA:
						case WP_MCM_SETTINGS_TYPE_FILENAME:
							$wp_mcm_post_values[$settings_name] = isset( $_POST[WP_MCM_SETTINGS_TYPE_TEXT][$settings_name] ) ? sanitize_text_field($_POST[WP_MCM_SETTINGS_TYPE_TEXT][$settings_name]) : ''; 
							break;
						default:
							break;
					}
				}
			}

			// Update the changed options.
			$this->debugMP('pr',__FUNCTION__.' found wp_mcm_post_values:', $wp_mcm_post_values );
			return $wp_mcm_post_values;

		}

		/**
		 * Render the settings for sections
		 * 
		 * @since 2.0.0
		 * @return html
		 */
		public function render_settings_sections( $wp_mcm_section = WP_MCM_SECTION_GEN, $wp_mcm_params = array(), $wp_mcm_section_label = '', $wp_mcm_button_label = '' ) {
			// $this->debugMP('msg',__FUNCTION__.' started.');
			$render_output = '';

			$button_template  = '<input type="submit" value="%s" class="button-primary"/>';

			// Render top section header
			//
			$render_output .= '<div class="postbox-container">';
			$render_output .= '<div class="metabox-holder">';
			$render_output .= '<div id="wp-mcm-settings" class="postbox">';

			// Render the section header only when wp_mcm_section_label given
			//
			if ( $wp_mcm_section_label != '' ) {
				$render_output .= '<h3 class="hndle"><span>';
				$render_output .= $wp_mcm_section_label;
				$render_output .= '</span></h3>';
			}
			$render_output .= '<div class="inside">';

			// Render settings for section $wp_mcm_section
			//
			foreach ($wp_mcm_params as $section_name => $settings_params ) {
				if ( $settings_params['section'] == $wp_mcm_section ) {
					switch ( $settings_params['type'] ) {
						case WP_MCM_SETTINGS_TYPE_CHECKBOX:
							$render_output .= $this->render_settings_checkbox( $settings_params );
							break;
						case WP_MCM_SETTINGS_TYPE_DROPDOWN:
							$render_output .= $this->render_settings_dropdown( $settings_params );
							break;
						case WP_MCM_SETTINGS_TYPE_SUBHEADER:
							$render_output .= $this->render_settings_subheader( $settings_params );
							break;
						case WP_MCM_SETTINGS_TYPE_HIDDEN:
							$render_output .= $this->render_settings_hidden( $settings_params );
							break;
						case WP_MCM_SETTINGS_TYPE_READONLY:
							$render_output .= $this->render_settings_readonly( $settings_params );
							break;
						case WP_MCM_SETTINGS_TYPE_BUTTON:
							$render_output .= $this->render_settings_button( $settings_params );
							break;
						case WP_MCM_SETTINGS_TYPE_BUTTON_AJAX:
							$render_output .= $this->render_settings_button_ajax( $settings_params );
							break;
						case WP_MCM_SETTINGS_TYPE_CUSTOM:
							$render_output .= $this->render_settings_custom( $settings_params );
							break;
						case WP_MCM_SETTINGS_TYPE_ICONLIST:
							$render_output .= $this->render_settings_iconlist( $settings_params );
							break;
						case WP_MCM_SETTINGS_TYPE_FILE:
							$render_output .= $this->render_settings_file( $settings_params );
							break;
						case WP_MCM_SETTINGS_TYPE_TEXTAREA:
							$render_output .= $this->render_settings_textarea( $settings_params );
							break;
						case WP_MCM_SETTINGS_TYPE_TEXT:
						case WP_MCM_SETTINGS_TYPE_FILENAME:
						default:
							$render_output .= $this->render_settings_text( $settings_params );
							break;
					}

				}
			}

			// Render the button only when wp_mcm_button_label given
			//
			if ( $wp_mcm_button_label != '' ) {
				$render_output .= '<p class="submit">';
				$render_output .= sprintf( $button_template, $wp_mcm_button_label );
				$render_output .= '</p>';        // for class="submit"
			}

			// Close top section header and return output
			//
			$render_output .= '</div>';      // for class="inside"
			$render_output .= '</div>';      // for id="wp-mcm-search-settings"
			$render_output .= '</div>';      // for class="metabox-holder"
			$render_output .= '</div>';      // for class="postbox-container"
			return $render_output;

		}

		/**
		 * Render the settings for a label
		 * 
		 * @since 2.0.0
		 * @return html
		 */
		public function render_settings_label( $text_input = false, $extra_styles = '' ) {
			// $this->debugMP('msg',__FUNCTION__.' started.');
			$render_output = '';

			if ( $text_input == false ) {
				return $render_output;
			}

			// Define templates
			$text_label_template       = '<label for="%s" style="%s" >%s';
			$text_description_template = '<span class="wp-mcm-info"><span class="wp-mcm-info-text wp-mcm-hide">%s</span></span>';

			$render_output .= sprintf( $text_label_template,       $text_input['slug'], $extra_styles, $text_input['label'] );
			if ( isset( $text_input['description'] ) && $text_input['description'] != '' ) {
				$render_output .= sprintf( $text_description_template, $text_input['description'] );
			}
			$render_output .= '</label>';

			// Return output
			//
			return $render_output;

		}

		/**
		 * Render the settings for a text box
		 * 
		 * @since 2.0.0
		 * @return html
		 */
		public function render_settings_subheader( $text_input = false ) {
			// $this->debugMP('msg',__FUNCTION__.' started.');
			$render_output = '';

			if ( $text_input == false ) {
				return $render_output;
			}
			// $this->debugMP('pr',__FUNCTION__.' started with text_input:', $text_input );

			// Define templates
			$text_subheader_template = '<h3 class="hndle"><span><em>%s</em></span></h3>';
			$text_description_template = '<span>%s</span>';

			// Render text input
			//
			$render_output .= '<p>';
			if ( isset($text_input['label']) && $text_input['label'] != '' ) {
				$render_output .= sprintf( $text_subheader_template, $text_input['label'] );
			}
			if ( isset($text_input['description']) && $text_input['description'] != '' ) {
				$render_output .= sprintf( $text_description_template, $text_input['description'] );
			}
			$render_output .= '</p>';

			// Close top section header and return output
			//
			return $render_output;

		}

		/**
		 * Render the settings for a hidden item
		 * 
		 * @since 2.0.0
		 * @return html
		 */
		public function render_settings_hidden( $text_input = false, $extra_styles = '' ) {
			// $this->debugMP('msg',__FUNCTION__.' started.');
			$render_output = '';

			if ( $text_input == false ) {
				return $render_output;
			}

			// Define templates
			$input_hidden_template = '<input type="hidden" name="%s" %s value="%s"/>';

			$render_output .= sprintf( $input_hidden_template, $text_input['name'], $extra_styles, $text_input['value'] );

			// Return output
			//
			return $render_output;

		}

		/**
		 * Render the settings for a text box
		 * 
		 * @since 2.0.0
		 * @return html
		 */
		public function render_settings_readonly( $text_input = false ) {
			// $this->debugMP('msg',__FUNCTION__.' started.');
			$render_output = '';

			if ( $text_input == false ) {
				return $render_output;
			}
			// $this->debugMP('pr',__FUNCTION__.' started with text_input:', $text_input );

			// Define templates
			$text_input_template = '<span>%s</span>';
			$text_input_template = '<input type="text" readonly="readonly" value="%s" name="' . WP_MCM_SETTINGS_TYPE_TEXT . '[%s]" class="textinput" id="%s"/>';
			if ( isset($text_input['textarea']) ) {
				$text_input_template = '<textarea readonly="readonly" cols="30" rows="3" style="width:275px;">%s</textarea>';
			}

			// Render text input
			//
			$render_output .= '<p>';
			$render_output .= $this->render_settings_label( $text_input, 'vertical-align:top' );
			if ( isset($text_input['textarea']) ) {
				$render_output .= sprintf( $text_input_template, $text_input['value'] );
			} else {
				$render_output .= sprintf( $text_input_template, $text_input['value'], $text_input['name'], $text_input['slug'] );
			}
			$render_output .= '</p>';

			// Close top section header and return output
			//
			return $render_output;

		}

		/**
		 * Render the settings for a button
		 * 
		 * @since 2.0.0
		 * @return html
		 */
		public function render_settings_button( $text_input = false ) {
			// $this->debugMP('msg',__FUNCTION__.' started.');
			$render_output = '';

			if ( $text_input == false ) {
				return $render_output;
			}
			// $this->debugMP('pr',__FUNCTION__.' started with text_input:', $text_input );

			// Define templates
			$button_template  = '<input type="submit" name="%s" value="%s" %s class="button-primary"/>';

			$action_def = '';
			if ( isset( $text_input['action'] ) ) {
				$action_def = 'action="' . $text_input['action'] . '" ';
			}

			// Render text input
			//
			$render_output .= '<p>';
			$render_output .= $this->render_settings_label( $text_input, 'vertical-align:top' );
			$render_output .= sprintf( $button_template, $text_input['name'], $text_input['value'], $action_def );
			$render_output .= '</p>';

			// Close top section header and return output
			//
			return $render_output;

		}

		/**
		 * Render the settings for a button
		 * 
		 * @since 2.0.0
		 * @return html
		 */
		public function render_settings_button_ajax( $text_input = false ) {
			// $this->debugMP('msg',__FUNCTION__.' started.');
			$render_output = '';

			if ( $text_input == false ) {
				return $render_output;
			}
			// $this->debugMP('pr',__FUNCTION__.' started with text_input:', $text_input );

			// Define templates
			$button_template  = '<a id="%s" class="button-primary" href="%s"/>%s</a>';

			// Generate the actionlink_base including args parameters
			$actionlink_base = admin_url('admin-ajax.php');
			$actionlink_base = add_query_arg( 'action', $text_input['action'], $actionlink_base );

			// Add the args supplied to the actionlink_base
			if ( ( isset( $text_input['args'] ) ) && ( is_array( $text_input['args'] ) ) ) {
				foreach ( $text_input['args'] as $arg_key => $arg_value ) {
					$actionlink_base = add_query_arg( $arg_key, $arg_value, $actionlink_base );
				}
			}

			// Render text input
			//
			$render_output .= '<p>';
			$render_output .= $this->render_settings_label( $text_input, 'vertical-align:top' );
			$render_output .= sprintf( $button_template, $text_input['name'], wp_nonce_url( $actionlink_base ), $text_input['value'] );
			$render_output .= '</p>';

			// Close top section header and return output
			//
			return $render_output;

		}

		/**
		 * Render the settings for some custom html
		 * 
		 * @since 2.0.0
		 * @return html
		 */
		public function render_settings_custom( $text_input = false ) {
			// $this->debugMP('msg',__FUNCTION__.' started.');
			$render_output = '';

			if ( $text_input == false ) {
				return $render_output;
			}
			// $this->debugMP('pr',__FUNCTION__.' started with text_input:', $text_input );

			// Define templates
			$text_input_template = '<span>%s</span>';
			$text_input_template = '<textarea readonly="readonly" style="width:275px;">%s</textarea>';

			// Render text input
			//
			$render_output .= '<p>';
			$render_output .= $this->render_settings_label( $text_input, 'vertical-align:top' );
			$render_output .= sprintf( $text_input_template, $text_input['value'], $text_input['name'], $text_input['slug'] );
			$render_output .= '</p>';

			// Close top section header and return output
			//
			return $render_output;

		}

		/**
		 * Render the settings for a text box
		 * 
		 * @since 2.0.0
		 * @return html
		 */
		public function render_settings_text( $text_input = false ) {
			// $this->debugMP('msg',__FUNCTION__.' started.');
			$render_output = '';

			if ( $text_input == false ) {
				return $render_output;
			}
			// $this->debugMP('pr',__FUNCTION__.' started with text_input:', $text_input );

			// Define templates
			$text_input_template = '<input %s type="text" value="%s" name="' . WP_MCM_SETTINGS_TYPE_TEXT . '[%s]" class="textinput" id="%s"/>';

			$readonly = '';
			if ( isset($text_input['readonly']) && $text_input['readonly'] ) {
				$readonly = 'readonly="readonly" ';
			}

			// Render text input
			//
			$render_output .= '<p>';
			$render_output .= $this->render_settings_label( $text_input );
			$render_output .= sprintf( $text_input_template, $readonly, $text_input['value'], $text_input['name'], $text_input['slug'] );
			$render_output .= '</p>';

			// Close top section header and return output
			//
			return $render_output;

		}

		/**
		 * Render the settings for a text box
		 * 
		 * @since 2.0.0
		 * @return html
		 */
		public function render_settings_textarea( $text_input = false ) {
			// $this->debugMP('msg',__FUNCTION__.' started.');
			$render_output = '';

			if ( $text_input == false ) {
				return $render_output;
			}
			// $this->debugMP('pr',__FUNCTION__.' started with text_input:', $text_input );

			$readonly = '';
			if ( isset($text_input['readonly']) && $text_input['readonly'] ) {
				$readonly = 'readonly="readonly" ';
			}

			// Define templates
			$text_input_template = '<textarea %s cols="30" rows="3" name="' . WP_MCM_SETTINGS_TYPE_TEXT . '[%s]" class="textinput" id="%s">%s</textarea>';

			// Render text input
			//
			$render_output .= '<p>';
			$render_output .= $this->render_settings_label( $text_input, 'vertical-align:top' );
			$render_output .= sprintf( $text_input_template, $readonly, $text_input['name'], $text_input['slug'], $text_input['value'] );
			$render_output .= '</p>';

			// Close top section header and return output
			//
			return $render_output;

		}

		/**
		 * Render the settings for a checkbox
		 * 
		 * @since 2.0.0
		 * @return html
		 */
		public function render_settings_checkbox( $checkbox_input = false ) {
			// $this->debugMP('msg',__FUNCTION__.' started.');
			$render_output = '';

			if ( $checkbox_input == false ) {
				return $render_output;
			}
			// $this->debugMP('pr',__FUNCTION__.' started with checkbox_input:', $checkbox_input );

			// Define templates
			$checkbox_input_template = '<input type="checkbox" value="" %s name="' . WP_MCM_SETTINGS_TYPE_CHECKBOX . '[%s]" id="%s"/>';

			// Render checkbox input
			//
			$checked_value = ($checkbox_input['value'] == 1) ? 'checked="checked"' : '';
			$render_output .= '<p>';
			$render_output .= $this->render_settings_label( $checkbox_input );
			$render_output .= sprintf( $checkbox_input_template, $checked_value, $checkbox_input['name'], $checkbox_input['slug'] );
			$render_output .= '</p>';

			// Close top section header and return output
			//
			return $render_output;

		}

		/**
		 * Render the settings for a dropdown
		 * 
		 * @since 2.0.0
		 * @return html
		 */
		public function render_settings_dropdown( $dropdown_input = false ) {
			// $this->debugMP('msg',__FUNCTION__.' started.');
			$render_output = '';

			if ( $dropdown_input == false ) {
				return $render_output;
			}
			// $this->debugMP('pr',__FUNCTION__.' started with dropdown_input:', $dropdown_input );

			// Define templates
			$dropdown_input_template = '<select id="%s" name="' . WP_MCM_SETTINGS_TYPE_DROPDOWN . '[%s]">';

			// Render dropdown input
			//
			$render_output .= '<p>';
			$render_output .= $this->render_settings_label( $dropdown_input );
			$render_output .= sprintf( $dropdown_input_template, $dropdown_input['slug'], $dropdown_input['name'] );
			$render_output .= $this->render_settings_dropdown_options( $dropdown_input );
			$render_output .= '</select>';
			$render_output .= '</p>';

			// Close top section header and return output
			//
			return $render_output;

		}

		/**
		 * Render the settings for a dropdown
		 * 
		 * @since 2.0.0
		 * @return html
		 */
		public function render_settings_dropdown_options( $input_options = false ) {
			// $this->debugMP('msg',__FUNCTION__.' started.');
			$render_output = '';

			// Check input_options
			if ( $input_options == false ) {
				return $render_output;
			}
			if ( ! isset( $input_options['options'] ) ) {
				return $render_output;
			}
			// $this->debugMP('pr',__FUNCTION__.' started with input_options:', $input_options );

			// Define templates
			$option_list_template = '<option value="%s" data-value="%s" %s>%s</option>';

			// Set default option if no selection available yet
			//
			if ( $input_options['value'] == '' ) {
				$input_options['value'] = $input_options['default'];
			}

			// Render dropdown input for all options
			//
			foreach ( $input_options['options'] as $input_option ) {  
				$selected_value = ( $input_options['value'] == $input_option['option_value'] ) ? 'selected="selected"' : '';
				$render_output .= sprintf( $option_list_template, $input_option['option_value'], $input_option['option_value'], $selected_value, $input_option['option_label'] );
			}

			// Return output
			//
			return $render_output;

		}

		/**
		 * Render the settings for a file selector
		 * 
		 * @since 2.0.0
		 * @return html
		 */
		public function render_settings_file_jquery_script( $input_settings_params = false ) {
			// $this->debugMP('msg',__FUNCTION__.' started.');

			$render_output  = '';
			$render_output .= 'jQuery(function($) {';

			$render_output .= '$("body").on("change", "#wp_mcm_file_input_' . $input_settings_params['slug'] . '", function() {';
			$render_output .= '$this = $(this);';
			$render_output .= 'file_data = $(this).prop("files")[0];';
			$render_output .= 'form_data = new FormData();';
			$render_output .= 'form_data.append("file", file_data);';
			$render_output .= 'form_data.append("action", "' . $input_settings_params['slug'] . '");';
			$render_output .= 'form_data.append("security", wp_mcm_file_object_' . $input_settings_params['slug'] . '.security);';
  
			$render_output .= '$.ajax({';
			$render_output .= 'url: wp_mcm_file_object_' . $input_settings_params['slug'] . '.ajaxurl,';
			$render_output .= 'type: "POST",';
			$render_output .= 'contentType: false,';
			$render_output .= 'processData: false,';
			$render_output .= 'data: form_data,';
			$render_output .= 'success: function (response) {';
			$render_output .= '$this.val("' . $input_settings_params['value'] . '");';
			$render_output .= 'alert("File defined successfully.");';
			$render_output .= '}';

			$render_output .= '});';
			$render_output .= '});';
			$render_output .= '});';

		}

		/**
		 * Render the settings for a file selector
		 * 
		 * @since 2.0.0
		 * @return html
		 */
		public function render_settings_file( $input_settings_params = false ) {
			// $this->debugMP('msg',__FUNCTION__.' started.');
			$render_output = '';

			// $this->debugMP('pr',__FUNCTION__.' started with input_settings_params:', $input_settings_params );

			// Define templates
			$file_input_template = '<input type="file" id="%s" class="wp_mcm_file_input" name="' . WP_MCM_SETTINGS_TYPE_FILE . '[%s]"/>';

			// Render file input
			//
			$render_output .= '<p>';
			// $render_output .= '<form class="fileUpload_' . $input_settings_params['slug'] . '" enctype="multipart/form-data">';
			$render_output .= '<div class="form-group">';
			$render_output .= $this->render_settings_label( $input_settings_params );
			$render_output .= sprintf( $file_input_template, $input_settings_params['slug'], $input_settings_params['name']  );
			$render_output .= '</div>';   // For class="form-group"
			// $render_output .= '</form>';  // For form class="fileUpload"
			$render_output .= $this->render_settings_file_jquery_script( $input_settings_params );
			$render_output .= '</p>';

			// Close top section header and return output
			//
			return $render_output;

		}

		/**
		 * Render the settings for an iconlist selector
		 * 
		 * @since 2.0.0
		 * @return html
		 */
		public function render_settings_iconlist( $icon_input = false ) {
			// $this->debugMP('msg',__FUNCTION__.' started.');
			$render_output = '';

			if ( $icon_input == false ) {
				return $render_output;
			}
			// $this->debugMP('pr',__FUNCTION__.' started with icon_input:', $icon_input );

			// Define templates
			$iconlist_input_template = '<select id="%s" name="' . WP_MCM_SETTINGS_TYPE_ICONLIST . '[%s]">';

			// Render dropdown input
			//
			$render_output .= '<p>';
			$render_output .= $this->render_settings_label( $icon_input );
			// $render_output .= sprintf( $iconlist_input_template, $icon_input['slug'], $icon_input['name'] );
			$render_output .= $this->render_settings_iconlist_options( $icon_input );
			// $render_output .= '</select>';
			$render_output .= '</p>';

			// Close top section header and return output
			//
			return $render_output;

		}

		/**
		 * Render the settings for a dropdown
		 * 
		 * @since 2.0.0
		 * @return html
		 */
		public function render_settings_iconlist_options( $icon_input = false ) {
			// $this->debugMP('msg',__FUNCTION__.' started.');
			$render_output = '';

			// Check icon_input
			if ( $icon_input == false ) {
				return $render_output;
			}
			// $this->debugMP('pr',__FUNCTION__.' started with icon_input:', $icon_input );

			// Define templates
			$icon_list_class_template = '<li %s>';
			$icon_list_image_template = '<img src="%s"/>';
			$icon_list_input_template = '<input %s type="radio" name="' . WP_MCM_SETTINGS_TYPE_ICONLIST . '[%s]" value="%s" />';

			$icon_urls = $this->render_settings_iconlist_get_icon_urls( $icon_input );
			// $this->debugMP('pr',__FUNCTION__.' found icon_urls:', $icon_urls );

			// Render dropdown input for all icons
			//
			$render_output .= '<ul class="wp-mcm-marker-list wp-mcm-marker-list">';
			foreach ( $icon_urls as $icon_filename => $icon_url ) {
				// Show the selected icon
				if ( $icon_input['value'] == $icon_url ) {
					$checked   = 'checked="checked"';
					$css_class = 'class="wp-mcm-active-marker"';
				} else {
					$checked   = '';
					$css_class = '';
				}
				$render_output .= sprintf( $icon_list_class_template, $css_class );
				$render_output .= sprintf( $icon_list_image_template, $icon_url );
				$render_output .= sprintf( $icon_list_input_template, $checked, $icon_input['name'], $icon_url );
				$render_output .= '</li>';   // for icon_list_class_template
			}
			$render_output .= '</ul>';   // for ul class="wp-mcm-marker-list"

			// Return output
			//
			return $render_output;

		}

		/**
		 * Return the icon selector HTML for the icon images in saved markers and default icon directories.
		 *
		 * @param type $inputFieldID
		 * @param type $inputImageID
		 * @return string
		 */
		 function render_settings_iconlist_get_icon_urls( $icon_input = false ) {

			$icon_urls = array();

			// Check input parameters
			if ( ! isset($icon_input['icon_object']) || ($icon_input['icon_object'] == null)) { return $icon_urls; }

			$wp_mcm_icon_object = $icon_input['icon_object'];     // e.g. $wp_mcm_social
			$wp_mcm_icon_subdir = $icon_input['icon_subdir'];     // e.g. 'social-icons/'

			$htmlStr = '';
			$files = array();
			$fqURL = array();

			// If we already got a list of icons and URLS, just use those
			//
			if (
				isset($wp_mcm_icon_object->iconselector_files ) &&
				isset($wp_mcm_icon_object->iconselector_urls  ) &&
				($wp_mcm_icon_object->iconselector_files != '') &&
				($wp_mcm_icon_object->iconselector_urls  != '')
			   ) {
				$files = $wp_mcm_icon_object->iconselector_files;
				$fqURL = $wp_mcm_icon_object->iconselector_urls;

			// If not, build the icon info but remember it for later
			// this helps cut down looping directory info twice (time consuming)
			// for things like icon processing.
			//
			} else {

				// Load the file list from our directories
				//
				// using the same array for all allows us to collapse files by
				// same name, last directory in is highest precedence.
				$icon_assets = apply_filters('wp_mcm_icon_directories',
						array(
								array('dir' => WP_MCM_UPLOAD_DIR . $icon_input['icon_subdir'],
									  'url' => WP_MCM_UPLOAD_URL . $icon_input['icon_subdir']
									 ),
							)
						);

				$fqURLIndex = 0;
				foreach ($icon_assets as $icon) {
					if (is_dir($icon['dir'])) {
						if ($iconDir = opendir($icon['dir'])) {
							$fqURL[] = $icon['url'];
							// $this->debugMP('pr',__FUNCTION__.' found icon:', $icon );
							while ($filename = readdir($iconDir)) {
								if (strpos($filename, '.') === 0) { continue; }
								$files[$filename] = $fqURLIndex;
							};
							closedir($iconDir);
							$fqURLIndex++;
						} else {
							wp_mcm_add_notice( WP_MCM_NOTICE_WARNING,
									sprintf( __('Could not read icon directory %s','wp-media-category-management'), $directory ) );
						}
				   }
				}
				ksort($files);
				$wp_mcm_icon_object->iconselector_files = $files;
				$wp_mcm_icon_object->iconselector_urls  = $fqURL;
			}
			// $this->debugMP('pr',__FUNCTION__.' found wp_mcm_icon_object->iconselector_files:', $wp_mcm_icon_object->iconselector_files );
			// $this->debugMP('pr',__FUNCTION__.' found wp_mcm_icon_object->iconselector_urls:', $wp_mcm_icon_object->iconselector_urls );

			// Build our icon array now that we have a full file list.
			//
			foreach ($files as $filename => $fqURLIndex) {
				if (
					(preg_match('/\.(png|gif|jpg)/i', $filename) > 0) &&
					(preg_match('/shadow\.(png|gif|jpg)/i', $filename) <= 0)
					) {
					$icon_urls[$filename] = $fqURL[$fqURLIndex].$filename;
				}
			}

			// return files found
			return $icon_urls;
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
