<?php

/**
 * Handle the WP Media Category Management plugin settings.
 *
 * @author DeBAAT
 * @since  2.0.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !class_exists( 'WP_MCM_Settings' ) ) {
    class WP_MCM_Settings
    {
        /**
         * Parameters for handling the settable options for this plugin.
         *
         * @var mixed[] $options
         */
        public  $mcm_settings_params = array() ;
        public function __construct()
        {
            // Get some settings
            $this->initialize();
        }
        
        public function initialize()
        {
            // Get some settings
            // $this->set_mcm_settings_params();
            add_action( 'admin_init', array( $this, 'set_mcm_settings_params' ) );
        }
        
        /**
         * Render the settings for options
         * 
         * @since 2.0.0
         * @return html
         */
        public function set_mcm_settings_params()
        {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            // Reset mcm_settings_params
            $this->mcm_settings_params = array();
            // Set mcm_settings_params General
            $this->set_mcm_settings_params_general();
        }
        
        /**
         * Render the settings for general options
         * 
         * @since 2.0.0
         * @return html
         */
        public function set_mcm_settings_params_general()
        {
            $mcm_settings_params_section = WP_MCM_SECTION_GEN;
            $this->debugMP( 'msg', __FUNCTION__ . ' started for section ' . $mcm_settings_params_section );
            global  $wp_mcm_options ;
            // Get settings parameters from the WP_MCM_Options class
            $this->mcm_settings_params = $wp_mcm_options->set_mcm_settings_params( $this->mcm_settings_params );
        }
        
        /**
         * Render the settings.
         * 
         * @since 2.0.0
         * @return html contents
         */
        public function wp_mcm_render_section()
        {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            $this->set_mcm_settings_params();
            // Check actions for settings
            $this->wp_mcm_check_action_settings();
            // Render the output for this section
            return $this->wp_mcm_render_section_output();
        }
        
        /**
         * Render the settings.
         * 
         * @since 2.0.0
         * @return html contents
         */
        public function wp_mcm_check_action_settings()
        {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            if ( isset( $_REQUEST[WP_MCM_ACTION_REQUEST] ) && sanitize_key( $_REQUEST[WP_MCM_ACTION_REQUEST] ) == WP_MCM_ACTION_SETTINGS ) {
                $this->process_mcm_settings();
            }
        }
        
        /**
         * Sanitize the submitted plugin settings.
         * 
         * @since 2.0.0
         * @return array $output The setting values
         */
        public function process_mcm_settings()
        {
            global  $wp_mcm_render_settings ;
            global  $wp_mcm_options ;
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            $output = array();
            // Check if the ext_checkboxes are checked.
            //
            $settings_post_values = $wp_mcm_render_settings->get_post_values( $this->mcm_settings_params );
            foreach ( $this->mcm_settings_params as $settings_name => $settings_params ) {
                $new_option_value = ( isset( $settings_post_values[$settings_name] ) ? $settings_post_values[$settings_name] : '' );
                $wp_mcm_options->set_value( $settings_name, $new_option_value );
            }
            // Update the changed options.
            $wp_mcm_options->update_mcm_options();
            $this->set_mcm_settings_params();
        }
        
        /**
         * Render the settings.
         * 
         * @since 2.0.0
         * @return html contents
         */
        public function wp_mcm_render_section_output()
        {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            global  $wp_mcm_admin ;
            global  $wp_mcm_render_settings ;
            // Set some defaults
            $render_output = '';
            $input_form_action = $wp_mcm_admin->wp_mcm_settings_section_url( WP_MCM_SECTION_SETTINGS );
            $input_hidden_template = '<input type="hidden" name="%s" value="%s"/>';
            // Input hidden items
            //
            $input_hidden_items = array(
                'option_page'         => WP_MCM_ADMIN_MENU_SLUG,
                WP_MCM_SECTION_PARAM  => WP_MCM_SECTION_SETTINGS,
                WP_MCM_ACTION_REQUEST => WP_MCM_ACTION_SETTINGS,
            );
            // Render top section header
            //
            $render_output .= '<div id="wp_mcm_gen_settings" class="wrap wp-mcm-settings">';
            $render_output .= '<form id="wp-mcm-settings-form" method="post" action="' . $input_form_action . '" autocomplete="off" accept-charset="utf-8">';
            // Render input_hidden_items
            //
            foreach ( $input_hidden_items as $input_hidden_name => $input_hidden_value ) {
                $render_output .= sprintf( $input_hidden_template, $input_hidden_name, $input_hidden_value );
            }
            // Render the settings sections
            //
            $button_label = __( 'Save Settings', 'wp-media-category-management' );
            $header_label = __( 'General Settings', 'wp-media-category-management' );
            $render_output .= $wp_mcm_render_settings->render_settings_sections(
                WP_MCM_SECTION_GEN,
                $this->mcm_settings_params,
                $header_label,
                $button_label
            );
            // Close top section header and return output
            //
            $render_output .= '</form>';
            // for id="wp-mcm-settings-form"
            $render_output .= '</div>';
            // for id="wp_mcm_gen_settings"
            return $render_output;
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
        function debugMP( $type, $hdr, $msg = '' )
        {
            if ( $type === 'msg' && $msg !== '' ) {
                $msg = esc_html( $msg );
            }
            if ( $hdr !== '' ) {
                // Adding __CLASS__ to non-empty hdr
                $hdr = __CLASS__ . '::' . $hdr;
            }
            WP_MCM_debugMP(
                $type,
                $hdr,
                $msg,
                NULL,
                NULL,
                true
            );
        }
    
    }
}