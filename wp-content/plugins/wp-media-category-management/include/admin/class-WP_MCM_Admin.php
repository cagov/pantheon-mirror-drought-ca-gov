<?php

/**
 * WP Media Category Management Admin class
 * 
 * @since  2.0.0
 * @author DeBAAT
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !class_exists( 'WP_MCM_Admin' ) ) {
    class WP_MCM_Admin
    {
        /**
         * A cache for the taxonomy objects.
         *
         * @var 
         */
        public  $taxonomyCache = array() ;
        /**
         * Class constructor
         */
        function __construct()
        {
            $this->includes();
            $this->admin_init();
            $this->add_hooks_and_filters();
        }
        
        /**
         * Include the required files.
         *
         * @since 2.0.0
         * @return void
         */
        public function includes()
        {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            // Create objects for Admin functionality
            WP_MCM_create_object( 'WP_MCM_Notices', 'include/admin/' );
            WP_MCM_create_object( 'WP_MCM_Render_Settings', 'include/admin/' );
            WP_MCM_create_object( 'WP_MCM_Media_Admin', 'include/media/' );
            WP_MCM_create_object( 'WP_MCM_Media_List', 'include/media/' );
            WP_MCM_create_object( 'WP_MCM_Taxonomy_Admin', 'include/taxonomy/' );
            WP_MCM_create_object( 'WP_MCM_Shortcode_Admin', 'include/shortcode/' );
            WP_MCM_create_object( 'WP_MCM_Walker_Category_Filter', 'include/walker/' );
            WP_MCM_create_object( 'WP_MCM_Walker_Category_MediaGrid_Filter', 'include/walker/' );
            WP_MCM_create_object( 'WP_MCM_Walker_Category_MediaGrid_Checklist', 'include/walker/' );
        }
        
        /**
         * Init the required classes.
         *
         * @since 2.0.0
         * @return void
         */
        public function admin_init()
        {
            // global $wp_mcmenders;
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            $this->debugMP( 'pr', __FUNCTION__ . ' started with _GET:', $_GET );
            $this->debugMP( 'pr', __FUNCTION__ . ' started with _POST:', $_POST );
            $this->debugMP( 'pr', __FUNCTION__ . ' started with _REQUEST:', $_REQUEST );
            $this->debugMP( 'pr', __FUNCTION__ . ' started with _FILES:', $_FILES );
        }
        
        /**
         * Add cross-element hooks & filters.
         *
         * Haven't yet moved all items to the AJAX and UI classes.
         */
        function add_hooks_and_filters()
        {
            // $this->debugMP('msg', __FUNCTION__ . ' started.');
            add_action( 'admin_init', array( $this, 'wp_mcm_check_upgrade' ) );
            add_action( 'admin_init', array( $this, 'wp_mcm_admin_init' ) );
            add_action( 'admin_menu', array( $this, 'wp_mcm_admin_menu' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'wp_mcm_admin_scripts' ) );
            add_filter(
                'plugin_action_links_' . WP_MCM_BASENAME,
                array( $this, 'mcm_plugin_action_links' ),
                10,
                4
            );
        }
        
        /**
         * Include the required files.
         *
         * @since 2.0.0
         * @return void
         */
        public function wp_mcm_admin_init()
        {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            // Handle the admin actions
            $this->handle_wp_mcm_action();
            // Handle notice_status setting displaying a notice
            $this->mcm_handle_notice_status();
        }
        
        /**
         * If the db doesn't hold the current version, run the upgrade procedure
         *
         * @since 2.0.0
         * @return void
         */
        function wp_mcm_check_upgrade()
        {
            // $this->debugMP('msg',__FUNCTION__.' started.');
            global  $wp_mcm_activate ;
            global  $wp_mcm_options ;
            $option_version = $wp_mcm_options->get_value( 'wp_mcm_version' );
            $update_to_new_version = version_compare( $option_version, WP_MCM_VERSION_NUM, '<' );
            $this->debugMP( 'msg', __FUNCTION__ . ' started for option_version = ' . $option_version . '!' );
            $this->debugMP( 'msg', __FUNCTION__ . ' started for WP_MCM_VERSION_NUM = ' . WP_MCM_VERSION_NUM . '!' );
            // Create and run the WP_MCM_Activate class for wp_mcm_create_roles
            WP_MCM_create_object( 'WP_MCM_Activate', 'include/admin/' );
            $wp_mcm_activate->wp_mcm_create_roles();
            $this->debugMP( 'msg', __FUNCTION__ . ' started for update_to_new_version = ' . $update_to_new_version . '!' );
            
            if ( $update_to_new_version ) {
                $this->debugMP( 'msg', __FUNCTION__ . ' activated!!!' );
                // Create and run the WP_MCM_Activate class
                WP_MCM_create_object( 'WP_MCM_Activate', 'include/admin/' );
                $wp_mcm_activate->update( $option_version );
            }
        
        }
        
        /**
         * Handle the notice message for this plugin.
         *
         * @since    2.0.0
         *
         */
        public function mcm_handle_notice_status()
        {
            global  $wp_mcm_options ;
            $this->debugMP( 'pr', __FUNCTION__ . ' wp_mcm_options =', get_option( WP_MCM_OPTIONS_NAME ) );
            // Only display a notice when the user has enough credits
            if ( !current_user_can( 'install_plugins' ) ) {
                return;
            }
            // Check the help_notice_status whether to display a notice
            
            if ( $wp_mcm_options->is_true( 'wp_mcm_notice_status' ) ) {
                // include notice js, only if needed
                add_action( 'admin_print_scripts', array( $this, 'mcm_admin_inline_js' ), 999 );
                // get current time
                $current_time = time();
                // get activation date
                $activation_date = $wp_mcm_options->get_value( 'wp_mcm_notice_activation_date' );
                
                if ( (int) $activation_date === 0 || empty($activation_date) ) {
                    $activation_date = $current_time;
                    $wp_mcm_options->set_value( 'wp_mcm_notice_activation_date', $activation_date );
                }
                
                if ( (int) $activation_date <= $current_time ) {
                    $this->mcm_add_notice( sprintf( __( "Hey, you've been using <strong>WP Media Category Management</strong> for more than %s since the last update.", 'wp-media-category-management' ), human_time_diff( $activation_date, $current_time ) ) . '<br />' . __( 'In the mean time I refactored the complete plugin code base and added some premium features.', 'wp-media-category-management' ) . '<br />' . __( 'Could you please do me a BIG favor and help me out testing this migration?', 'wp-media-category-management' ) . '<br />' . sprintf( __( 'Please leave some suggestions at the <a href="%s" target="_blank">support page</a> of this plugin', 'wp-media-category-management' ), 'https://wordpress.org/support/plugin/wp-media-category-management/' ) . ' ' . sprintf( __( 'or directly at the <a href="%s" target="_blank">help appreciated</a> topic.', 'wp-media-category-management' ), 'https://wordpress.org/support/topic/help-appreciated/' ) . '<br />' . __( 'Your help is much appreciated!', 'wp-media-category-management' ) . '<br /><br />' . __( 'Thank you very much!', 'wp-media-category-management' ) . ' ~ <strong>Jan de Baat</strong>, ' . sprintf( __( 'author of this <a href="%s" target="_blank">WP MCM</a> plugin.', 'wp-media-category-management' ), 'https://de-baat.nl/wp_mcm/' ) . '<br /><br />' . sprintf( __( '<a href="%s" class="mcm-dismissible-notice" target="_blank" rel="noopener">Ok, I may leave some advice</a><br /><a href="javascript:void(0);" class="mcm-dismissible-notice mcm-delay-notice" rel="noopener">Nope, maybe later</a><br /><a href="javascript:void(0);" class="mcm-dismissible-notice" rel="noopener">I already did</a>', 'wp-media-category-management' ), 'https://wordpress.org/support/plugin/wp-media-category-management/' ), 'notice notice-warning is-dismissible mcm-notice' );
                }
            }
        
        }
        
        /**
         * Dismiss notice.
         *
         * @return void
         */
        public function mcm_action_dismiss_notice()
        {
            if ( !current_user_can( 'install_plugins' ) ) {
                return;
            }
            global  $wp_mcm_options ;
            
            if ( wp_verify_nonce( esc_attr( $_REQUEST['nonce'] ), 'mcm_dismiss_notice' ) ) {
                $notice_action = ( empty($_REQUEST['notice_action']) || $_REQUEST['notice_action'] === 'hide' ? 'hide' : esc_attr( $_REQUEST['notice_action'] ) );
                switch ( $notice_action ) {
                    // delay notice
                    case 'delay':
                        // set delay period to WP_MCM_NOTICE_DELAY_PERIOD from now
                        $wp_mcm_options->set_value( 'wp_mcm_notice_activation_date', time() + WP_MCM_NOTICE_DELAY_PERIOD );
                        break;
                        // hide notice
                    // hide notice
                    default:
                        $wp_mcm_options->set_value( 'wp_mcm_notice_status', '0' );
                        //$wp_mcm_options->set_value( 'wp_mcm_notice_activation_date', time() );
                }
            }
            
            exit;
        }
        
        /**
         * Add admin notices.
         *
         * @param string $html Notice HTML
         * @param string $status Notice status
         * @param bool $paragraph Whether to use paragraph
         * @param bool $network
         * @return void
         */
        public function mcm_add_notice(
            $html = '',
            $status = 'error',
            $paragraph = true,
            $network = false
        )
        {
            $this->notices[] = array(
                'html'      => $html,
                'status'    => $status,
                'paragraph' => $paragraph,
            );
            add_action( 'admin_notices', array( $this, 'mcm_display_notice' ) );
            if ( $network ) {
                add_action( 'network_admin_notices', array( $this, 'mcm_display_notice' ) );
            }
        }
        
        /**
         * Print admin notices.
         *
         * @return void
         */
        public function mcm_display_notice()
        {
            foreach ( $this->notices as $notice ) {
                echo  '
				<div class="' . $notice['status'] . '">
					' . (( $notice['paragraph'] ? '<p>' : '' )) . '
					' . $notice['html'] . '
					' . (( $notice['paragraph'] ? '</p>' : '' )) . '
				</div>' ;
            }
        }
        
        /**
         * Print admin scripts.
         *
         * @return void
         */
        public function mcm_admin_inline_js()
        {
            if ( !current_user_can( 'install_plugins' ) ) {
                return;
            }
            ?>
			<script type="text/javascript">
				( function ( $ ) {
					$( document ).ready( function () {
						// save dismiss state
						$( '.mcm-notice.is-dismissible' ).on( 'click', '.notice-dismiss, .mcm-dismissible-notice', function ( e ) {
							var notice_action = 'hide';
							
							if ( $( e.currentTarget ).hasClass( 'mcm-delay-notice' ) ) {
								notice_action = 'delay'
							}
							
							$.post( ajaxurl, {
								action: 'mcm_dismiss_notice',
								notice_action: notice_action,
								url: '<?php 
            echo  admin_url( 'admin-ajax.php' ) ;
            ?>',
								nonce: '<?php 
            echo  wp_create_nonce( 'mcm_dismiss_notice' ) ;
            ?>'
							} );

							$( e.delegateTarget ).slideUp( 'fast' );
						} );
					} );
				} )( jQuery );
			</script>
			<?php 
        }
        
        /**
         * Add the settings link to the list of plugin actions.
         *
         * @since    2.0.0
         *
         * @param array  $action_links An array of plugin action links.
         * @param string $plugin_file  Path to the plugin file.
         * @param array  $plugin_data  An array of plugin data.
         * @param string $context      The plugin context. Defaults are 'All', 'Active',
         *                             'Inactive', 'Recently Activated', 'Upgrade',
         *                             'Must-Use', 'Drop-ins', 'Search'.
         */
        public function mcm_plugin_action_links(
            $action_links,
            $plugin_file,
            $plugin_data,
            $context
        )
        {
            // $this->debugMP('msg',__FUNCTION__.' started with plugin_file = ' . $plugin_file );
            // $this->debugMP('pr', __FUNCTION__.' started with action_links = ', $action_links );
            // $this->debugMP('pr', __FUNCTION__.' started with plugin_data = ',  $plugin_data );
            $action_links[] = '<a href="' . esc_url( $this->wp_mcm_settings_section_url() ) . '">' . esc_html__( 'Settings', 'wp-media-category-management' ) . '</a>';
            return $action_links;
        }
        
        /**
         * Get the action defined by WP_MCM_ACTION_REQUEST or action
         *
         * @since 2.0.0
         * @return void
         */
        public function get_wp_mcm_action()
        {
            // $this->debugMP('msg',__FUNCTION__.' started.');
            if ( isset( $_REQUEST[WP_MCM_ACTION_REQUEST] ) ) {
                return sanitize_key( $_REQUEST[WP_MCM_ACTION_REQUEST] );
            }
            if ( isset( $_REQUEST['action'] ) ) {
                return sanitize_key( $_REQUEST['action'] );
            }
            if ( isset( $_REQUEST['action2'] ) ) {
                return sanitize_key( $_REQUEST['action'] );
            }
            return false;
        }
        
        /**
         * Handle the actions defined by WP_MCM_ACTION_REQUEST
         *
         * @since 2.0.0
         * @return void
         */
        public function handle_wp_mcm_action()
        {
            // $this->debugMP('msg',__FUNCTION__.' started.');
            // check whether action is set
            $cur_action = $this->get_wp_mcm_action();
            // $this->debugMP('msg',__FUNCTION__.' cur_action: ' . $cur_action);
            
            if ( $cur_action ) {
                $user_ids = $this->get_ids_from_array( $_REQUEST, WP_MCM_USER_SLUG );
                switch ( $cur_action ) {
                    default:
                        break;
                }
            }
        
        }
        
        /**
         * Create the WP_MCM_Settings class.
         *
         * @since 2.0.0
         * @return void
         */
        public function get_ids_from_array( $input_array = null, $input_key = '' )
        {
            // $this->debugMP('msg',__FUNCTION__.' started.');
            $output_array = array();
            // Check input parameters
            if ( $input_array == null ) {
                return $output_array;
            }
            if ( $input_key == '' ) {
                return $output_array;
            }
            if ( !isset( $input_array[$input_key] ) ) {
                return $output_array;
            }
            // Get intvals for IDs
            
            if ( is_array( $input_array[$input_key] ) ) {
                foreach ( $input_array[$input_key] as $input_value ) {
                    $output_array[] = intval( $input_value );
                }
            } else {
                $output_array[] = intval( $input_array[$input_key] );
            }
            
            return $output_array;
        }
        
        /**
         * Add the 'WP Media Category Management' sub menu to the 
         * existing WP Settings menu.
         * 
         * @since  2.0.0
         * @return void
         */
        public function wp_mcm_admin_menu()
        {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            $parent_slug = 'options-general.php';
            add_submenu_page(
                $parent_slug,
                __( 'WP MCM', 'wp-media-category-management' ),
                __( 'WP MCM', 'wp-media-category-management' ),
                WP_MCM_CAP_MANAGE_MCM_ADMIN,
                WP_MCM_ADMIN_MENU_SLUG,
                array( $this, 'wp_mcm_render_admin' )
            );
        }
        
        /**
         * Get the Settings section to display
         * 
         * @since  2.0.0
         * @return void
         */
        public function get_settings_section()
        {
            global  $wp_mcm_options ;
            
            if ( isset( $_GET[WP_MCM_SECTION_PARAM] ) ) {
                $cur_section = sanitize_key( $_GET[WP_MCM_SECTION_PARAM] );
            } else {
                $cur_section = $wp_mcm_options->get_value( 'wp_mcm_default_section' );
            }
            
            return $cur_section;
        }
        
        /**
         * Render the admin page with sections.
         * 
         * @since  2.0.0
         * @return void
         */
        public function wp_mcm_render_admin()
        {
            global  $wp_mcm_settings ;
            global  $wp_mcm_notices ;
            global  $wp_mcm_shortcode_admin ;
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            // $this->debugMP('pr', __FUNCTION__.' started with _GET:',     $_GET );
            // $this->debugMP('pr', __FUNCTION__.' started with _POST:',    $_POST );
            // $this->debugMP('pr', __FUNCTION__.' started with _REQUEST:', $_REQUEST );
            $cur_section = WP_MCM_SECTION_GEN;
            $cur_section = $this->get_settings_section();
            $render_output = '';
            $nav_template = '<a class="nav-tab %s" href="%s">%s</a>';
            // All section items
            //
            $section_items = array();
            $section_items[WP_MCM_SECTION_GEN] = __( 'WP MCM Settings', 'wp-media-category-management' );
            $section_items[WP_MCM_SECTION_SCO] = __( 'Shortcode Info', 'wp-media-category-management' );
            // Render top section header
            //
            $render_output .= '<div class="wrap wp-mcm-settings">';
            $render_output .= '<h2>' . __( 'WP Media Category Management', 'wp-media-category-management' ) . '</h2>';
            $render_output .= '<h2 class="nav-tab-wrapper" id="wp-mcm-tabs">';
            // Render top section navigations
            //
            foreach ( $section_items as $section_slug => $section_label ) {
                $section_active = ( $cur_section == $section_slug ? 'nav-tab-active' : '' );
                $section_href = $this->wp_mcm_settings_section_url( $section_slug );
                $render_output .= sprintf(
                    $nav_template,
                    $section_active,
                    $section_href,
                    $section_label
                );
            }
            $render_output .= '</h2>';
            switch ( $cur_section ) {
                case WP_MCM_SECTION_SCO:
                    $render_output .= $wp_mcm_shortcode_admin->wp_mcm_render_section_shortcode();
                    break;
                case WP_MCM_SECTION_GEN:
                default:
                    WP_MCM_create_object( 'WP_MCM_Settings', 'include/admin/' );
                    $render_output .= $wp_mcm_settings->wp_mcm_render_section();
                    break;
            }
            // Close top section header and generate output
            //
            $render_output .= '</div>';
            // For class="wrap wp-mcm-settings"
            $rendered_output = $wp_mcm_notices->render_notices() . $render_output;
            echo  $rendered_output ;
        }
        
        /**
         * Add the required admin scripts.
         *
         * @since  2.0.0
         * @return void
         */
        public function wp_mcm_admin_scripts()
        {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            $min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' );
            $min = '';
            wp_enqueue_style( 'wp-mcm-admin', WP_MCM_PLUGIN_URL . '/css/wp-mcm-admin-styles' . $min . '.css', false );
            wp_enqueue_script( 'wp_mcm_script', WP_MCM_PLUGIN_URL . '/js/wp-mcm-admin.js' );
        }
        
        /**
         * Get the settings tab url.
         *
         * @since  2.0.0
         * @return void
         */
        public function wp_mcm_settings_section_url( $mcm_settings_section = WP_MCM_SECTION_SETTINGS )
        {
            // $this->debugMP('msg',__FUNCTION__.' started.');
            $mcm_settings_section_url = wp_mcm_freemius_settings_url();
            $mcm_settings_section_url = add_query_arg( array(
                WP_MCM_SECTION_PARAM => $mcm_settings_section,
            ), $mcm_settings_section_url );
            $this->debugMP( 'msg', __FUNCTION__ . ' returned mcm_settings_section_url = ' . $mcm_settings_section_url );
            return $mcm_settings_section_url;
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