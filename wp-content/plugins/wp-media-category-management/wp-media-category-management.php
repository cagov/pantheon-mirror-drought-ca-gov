<?php

/*
Plugin URI:  https://wordpress.org/plugins/wp-media-category-management/
Plugin Name: WP Media Category Management
Description: A plugin to provide bulk category management functionality for media in WordPress sites.
Author:      DeBAAT
Author URI:  https://www.de-baat.nl/WP_MCM/
Version:     2.1.1
Text Domain: wp-media-category-management
Domain Path: /languages/
License:     GPL v3


Copyright (C) 2014 - 2023 DeBAAT wp-mcm@de-baat.nl

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
defined( 'ABSPATH' ) || exit;
// Define some constants for use by this add-on
wp_mcm_maybe_define_constant( 'WP_MCM_FREEMIUS_ID', '10419' );
//
wp_mcm_maybe_define_constant( 'WP_MCM_SHORT_SLUG', 'wp-media-category-management' );
//
wp_mcm_maybe_define_constant( 'WP_MCM_PREMIUM_SLUG', 'wp-media-category-management-premium' );
//
wp_mcm_maybe_define_constant( 'WP_MCM_CLASS_PREFIX', 'WP_MCM_' );
//
wp_mcm_maybe_define_constant( 'WP_MCM_DMPPANEL', 'wp-mcm' );
//
wp_mcm_maybe_define_constant( 'WP_MCM_ADMIN_MENU_SLUG', 'wp-mcm' );
wp_mcm_maybe_define_constant( 'WP_MCM_ADMIN_MENU_SLUG_FRE', 'wp-mcm-pricing' );
//
wp_mcm_maybe_define_constant( 'WP_MCM_FILE', __FILE__ );
//
wp_mcm_maybe_define_constant( 'WP_MCM_REL_DIR', plugin_dir_path( WP_MCM_FILE ) );
//
wp_mcm_maybe_define_constant( 'WP_MCM_BASENAME', plugin_basename( __FILE__ ) );
wp_mcm_maybe_define_constant( 'WP_MCM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
wp_mcm_maybe_define_constant( 'WP_MCM_PLUGIN_URL', plugins_url( '', __FILE__ ) );
wp_mcm_maybe_define_constant( 'WP_MCM_FILENAME', basename( __FILE__ ) );
wp_mcm_maybe_define_constant( 'WP_MCM_PLUGIN_FILE', WP_MCM_BASENAME . '/' . WP_MCM_FILENAME );
wp_mcm_maybe_define_constant( 'WP_MCM_OPTION_NAME', 'wp-media-category-management-options' );
wp_mcm_maybe_define_constant( 'WP_MCM_NOTICE_OPTION', 'WP_MCM_notices' );
wp_mcm_maybe_define_constant( 'WP_MCM_LINK', 'https://www.de-baat.nl/WP_MCM' );
//
if ( !function_exists( 'get_plugin_data' ) ) {
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
}
$this_plugin = get_plugin_data( WP_MCM_FILE, false, false );
wp_mcm_maybe_define_constant( 'WP_MCM_VERSION_NUM', $this_plugin['Version'] );
/**
 * Define a constant if it is not already defined.
 *
 * @param string $name  Constant name.
 * @param string $value Value.
 *
 * @since  2.0.0
 */
function wp_mcm_maybe_define_constant( $name, $value )
{
    if ( !defined( $name ) ) {
        define( $name, $value );
    }
}

// Include Freemius SDK integration

if ( function_exists( 'wp_mcm_freemius' ) ) {
    wp_mcm_freemius()->set_basename( false, __FILE__ );
} else {
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    
    if ( !function_exists( 'wp_mcm_freemius' ) ) {
        // Create a helper function for easy SDK access.
        function wp_mcm_freemius()
        {
            global  $wp_mcm_freemius ;
            
            if ( !isset( $wp_mcm_freemius ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $wp_mcm_freemius = fs_dynamic_init( array(
                    'id'               => WP_MCM_FREEMIUS_ID,
                    'slug'             => WP_MCM_SHORT_SLUG,
                    'premium_slug'     => WP_MCM_PREMIUM_SLUG,
                    'type'             => 'plugin',
                    'public_key'       => 'pk_c77ea7be655a32dda241a3c050578',
                    'is_premium'       => false,
                    'premium_suffix'   => 'Premium',
                    'has_addons'       => false,
                    'has_paid_plans'   => true,
                    'is_org_compliant' => true,
                    'menu'             => array(
                    'slug'    => WP_MCM_ADMIN_MENU_SLUG,
                    'account' => false,
                    'contact' => false,
                    'support' => false,
                    'parent'  => array(
                    'slug' => 'options-general.php',
                ),
                ),
                    'is_live'          => true,
                ) );
            }
            
            return $wp_mcm_freemius;
        }
        
        // Init Freemius.
        wp_mcm_freemius();
        // Signal that SDK was initiated.
        do_action( 'wp_mcm_freemius_loaded' );
        function wp_mcm_freemius_plugins_url()
        {
            return admin_url( 'plugins.php' );
        }
        
        function wp_mcm_freemius_settings_url()
        {
            return admin_url( 'options-general.php?page=' . WP_MCM_ADMIN_MENU_SLUG );
        }
        
        function wp_mcm_freemius_pricing_url( $pricing_url )
        {
            $my_pricing_url = 'https://www.de-baat.nl/wp-media-category-management/';
            WP_MCM_debugMP(
                'pr',
                __FUNCTION__ . ' Changed pricing_url:',
                $pricing_url . ' into my_pricing_url:',
                $my_pricing_url
            );
            return $my_pricing_url;
        }
        
        wp_mcm_freemius()->add_filter( 'connect_url', 'wp_mcm_freemius_settings_url' );
        wp_mcm_freemius()->add_filter( 'after_skip_url', 'wp_mcm_freemius_settings_url' );
        wp_mcm_freemius()->add_filter( 'after_connect_url', 'wp_mcm_freemius_settings_url' );
        wp_mcm_freemius()->add_filter( 'after_pending_connect_url', 'wp_mcm_freemius_settings_url' );
        wp_mcm_freemius()->add_filter( 'pricing_url', 'wp_mcm_freemius_pricing_url' );
    }
    
    /**
     * Get the Freemius object.
     *
     * @return string
     */
    function wp_mcm_freemius_get_freemius()
    {
        return freemius( WP_MCM_FREEMIUS_ID );
    }
    
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX && !empty($_POST['action']) && $_POST['action'] === 'heartbeat' ) {
        return;
    }
    function WP_MCM_Plugin_loader()
    {
        // Make sure WP_MCM_Plugin itself is active.
        WP_MCM_create_object( 'WP_MCM_Plugin', 'include/' );
    }
    
    add_action( 'plugins_loaded', 'WP_MCM_Plugin_loader' );
    function WP_MCM_Get_Instance()
    {
        global  $wp_mcm_plugin ;
        return $wp_mcm_plugin;
    }
    
    function wp_mcm_main_admin_init()
    {
        global  $_registered_pages ;
        global  $hook_suffix ;
        $_registered_pages[WP_MCM_ADMIN_MENU_SLUG] = true;
    }
    
    function wp_mcm_main_admin_menu()
    {
        global  $_registered_pages ;
        global  $hook_suffix ;
        $_registered_pages['admin_page_' . WP_MCM_ADMIN_MENU_SLUG] = true;
    }
    
    // Register the additional admin pages!!!
    add_action( 'admin_init', 'wp_mcm_main_admin_init', 25 );
    add_action( 'user_admin_menu', 'wp_mcm_main_admin_menu' );
    // ADMIN
    add_action( 'admin_menu', 'wp_mcm_main_admin_menu' );
    // ADMIN
}

/**
 * Run when the WP_MCM_Plugin is activated.
 *
 * @since 2.0.0
 * @return void
 */
function wp_mcm_activate( $network_wide )
{
}

register_activation_hook( __FILE__, 'wp_mcm_activate' );
/**
 * Run when the WP_MCM_Plugin is deactivated.
 *
 * @since 2.0.0
 * @return void
 */
function wp_mcm_uninstall()
{
}

// Use Freemius action to do uninstall
// Not like register_uninstall_hook(), you do NOT have to use a static function.
wp_mcm_freemius()->add_action( 'after_uninstall', 'wp_mcm_uninstall' );
/**
 * Create a Map Settings Debug My Plugin panel.
 *
 * @return null
 */
function WP_MCM_create_object( $class = '', $path = '' )
{
    if ( $class == '' ) {
        return;
    }
    // Make sure WP_MCM_Plugin itself is active.
    if ( !class_exists( 'WP_MCM_Plugin' ) ) {
        WP_MCM_debugMP( 'msg', __FUNCTION__ . ' WP_MCM_Plugin DOES NOT EXIST.' );
    }
    if ( class_exists( $class ) == false ) {
        // require_once( WP_MCM_PLUGIN_DIR . 'include/admin/class-WP_MCM_Admin.php' );
        require_once WP_MCM_PLUGIN_DIR . $path . 'class-' . $class . '.php';
    }
    // Create the object if not defined yet
    $global_var = strtolower( $class );
    if ( !isset( $GLOBALS[$global_var] ) ) {
        $GLOBALS[$global_var] = new $class();
    }
}

/**
 * Upload directory issue warning.
 */
function wp_mcm_upload_dir_notice()
{
    global  $wp_mcm_upload_error ;
    echo  "<div class='error'><p>" . __( 'WP Media Category Management upload directory error.', 'wp-media-category-management' ) . $wp_mcm_upload_error . "</p></div>" ;
}

/**
 * Create a Map Settings Debug My Plugin panel.
 *
 * @return null
 */
function WP_MCM_create_DMPPanels()
{
    if ( !isset( $GLOBALS['DebugMyPlugin'] ) ) {
        return;
    }
    if ( class_exists( 'DMPPanelWPMCMMain' ) == false ) {
        require_once WP_MCM_PLUGIN_DIR . 'include/class.dmppanels.php';
    }
    $GLOBALS['DebugMyPlugin']->panels[WP_MCM_DMPPANEL] = new DMPPanelWPMCMMain();
}

add_action( 'dmp_addpanel', 'WP_MCM_create_DMPPanels' );
if ( !function_exists( 'wp_get_list_item_separator' ) ) {
    /**
     * Retrieves the list item separator based on the locale.
     *
     * Added for backward compatibility to support pre-6.0.0 WordPress versions.
     *
     * @since 2.1.0
     */
    function wp_get_list_item_separator()
    {
        /* translators: Used between list items, there is a space after the comma. */
        return __( ', ', 'wp-media-category-management' );
    }

}
/**
 * Simplify the plugin debugMP interface.
 *
 * @param string $type
 * @param string $hdr
 * @param string $msg
 */
function WP_MCM_debugMP(
    $type = 'msg',
    $header = '',
    $message = '',
    $file = null,
    $line = null,
    $notime = true
)
{
    // Prefix header
    $header = 'WPMCM:: ' . $header;
    // Check message
    if ( $message === null ) {
        $message = 'NULL';
    }
    // Only use error_log when WP_DEBUG_LOG_WPMCM defined as true in wp-config
    if ( defined( 'WP_DEBUG_LOG_WPMCM' ) && WP_DEBUG_LOG_WPMCM ) {
        switch ( strtolower( $type ) ) {
            case 'pr':
                error_log( ':: ' . $header . ' PR: ' . print_r( $message, true ) );
                break;
            default:
                $message = ' ' . $message;
                error_log( ':: ' . $header . ' MSG: ' . $message );
                break;
        }
    }
    // WP_MCM_DMPPANEL not setup yet?  Return and do nothing.
    //
    if ( !isset( $GLOBALS['DebugMyPlugin'] ) || !isset( $GLOBALS['DebugMyPlugin']->panels[WP_MCM_DMPPANEL] ) ) {
        return;
    }
    // Do normal real-time message output.
    //
    switch ( strtolower( $type ) ) {
        case 'pr':
            $GLOBALS['DebugMyPlugin']->panels[WP_MCM_DMPPANEL]->addPR(
                $header,
                $message,
                $file,
                $line,
                $notime
            );
            break;
        default:
            $GLOBALS['DebugMyPlugin']->panels[WP_MCM_DMPPANEL]->addMessage(
                $header,
                $message,
                $file,
                $line,
                $notime
            );
            break;
    }
}
