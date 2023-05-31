<?php

/**
 * WP Media Category Management Activate class
 * 
 * @since  2.0.0
 * @author DeBAAT
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !class_exists( 'WP_MCM_Activate' ) ) {
    class WP_MCM_Activate
    {
        /**
         * Class constructor
         */
        function __construct()
        {
            $this->includes();
            $this->init();
        }
        
        /**
         * Include the required files.
         *
         * @since 2.0.0
         * @return void
         */
        public function includes()
        {
        }
        
        /**
         * Init the required classes.
         *
         * @since 2.0.0
         * @return void
         */
        public function init()
        {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
        }
        
        /**
         * Check updates for the plugin.
         *
         * @since 2.0.0
         * @return void
         */
        public function update( $option_version = '' )
        {
            global  $wp_mcm_options ;
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            if ( version_compare( $option_version, '0.1.0', '<' ) ) {
                $this->wp_mcm_create_roles();
            }
            $this->check_options( $option_version );
            // Update the options.
            $wp_mcm_options->set_value( 'wp_mcm_version', WP_MCM_VERSION_NUM );
            $wp_mcm_options->update_mcm_options();
        }
        
        /**
         * Add WP Media Category Management Roles to all admin.
         *
         * @since 2.0.0
         * @return void
         */
        function wp_mcm_create_roles()
        {
            $role = get_role( 'administrator' );
            // WP_MCM_debugMP('pr',__FUNCTION__ . ' role = ', $role);
            
            if ( is_object( $role ) ) {
                $role->add_cap( WP_MCM_CAP_MANAGE_MCM );
                $role->add_cap( WP_MCM_CAP_MANAGE_MCM_ADMIN );
                $role->add_cap( WP_MCM_CAP_MANAGE_MCM_SETTINGS );
                $role->add_cap( WP_MCM_CAP_MANAGE_MCM_USER );
            }
        
        }
        
        /**
         * Check updated options for the plugin.
         *
         * @since 2.0.0
         * @return void
         */
        public function check_options( $option_version = '' )
        {
            global  $wp_mcm_options ;
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            $option_version = $wp_mcm_options->get_value( 'wp_mcm_version' );
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