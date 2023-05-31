<?php

/**
 * Handle the WP Media Category Management plugin options
 *
 * @author DeBAAT
 * @since  2.0.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !class_exists( 'WP_MCM_Options' ) ) {
    class WP_MCM_Options
    {
        /**
         * Settable options for this plugin.
         *
         * @var mixed[] $options
         */
        public  $mcm_options = array() ;
        /**
         * Settable options for this plugin.
         *
         * @var mixed[] $options
         */
        public  $default_mcm_options = array(
            'wp_mcm_version'                     => '',
            'wp_mcm_toggle_assign'               => '1',
            'wp_mcm_media_taxonomy_to_use'       => WP_MCM_MEDIA_TAXONOMY,
            'wp_mcm_category_base'               => WP_MCM_MEDIA_TAXONOMY,
            'wp_mcm_custom_taxonomy_name'        => '',
            'wp_mcm_custom_taxonomy_name_single' => '',
            'wp_mcm_use_post_taxonomy'           => '',
            'wp_mcm_search_media_library'        => '',
            'wp_mcm_use_default_category'        => '',
            'wp_mcm_default_media_category'      => WP_MCM_OPTION_NONE,
            'wp_mcm_default_post_category'       => '',
            'wp_mcm_use_gutenberg_filter'        => '1',
            'wp_mcm_notice_status'               => '1',
            'wp_mcm_notice_activation_date'      => '',
        ) ;
        public function __construct()
        {
            // Get some settings
            $this->default_mcm_options['wp_mcm_notice_activation_date'] = time() + WP_MCM_NOTICE_DELAY_PERIOD;
            $this->initialize();
            // TEMP reset wp_mcm_notice_activation_date to now time()
            // $this->mcm_options['wp_mcm_notice_activation_date']	= time();
            // $this->update_mcm_options();
        }
        
        /**
         * Initialize this options object
         * 
         * @since 2.0.0
         * @return html
         */
        public function initialize()
        {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            // Get some settings
            $this->mcm_options = get_option( WP_MCM_OPTION_NAME );
            $this->check_options();
        }
        
        /**
         * Check updates for the plugin.
         *
         * @since 2.0.0
         * @return void
         */
        public function check_options()
        {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            // Add default values for options if not yet available
            if ( !isset( $this->mcm_options['wp_mcm_version'] ) ) {
                // Copy default options
                $this->mcm_options = $this->default_mcm_options;
            }
        }
        
        /**
         * Determines whether an option is true or not
         * 
         * @since 2.0.0
         * @return html
         */
        public function is_true( $option_name = '' )
        {
            // $this->debugMP('msg',__FUNCTION__.' started for ' . $option_name );
            // Check whether the option requested is valid
            if ( $option_name == '' ) {
                return false;
            }
            // Check whether the option exists
            if ( !isset( $this->mcm_options[$option_name] ) ) {
                return false;
            }
            // Return the boolean value of the option
            if ( $this->mcm_options[$option_name] ) {
                return true;
            }
            return false;
        }
        
        /**
         * Get the value of the option requested
         * 
         * @since 2.0.0
         * @return html
         */
        public function get_value( $option_name = '', $default_value = '' )
        {
            // $this->debugMP('msg',__FUNCTION__.' started for ' . $option_name );
            // Check whether the option requested is valid
            if ( $option_name == '' ) {
                return '';
            }
            // If option does not exist, return default value
            
            if ( isset( $this->mcm_options[$option_name] ) ) {
                return $this->mcm_options[$option_name];
            } else {
                
                if ( isset( $this->default_mcm_options[$option_name] ) ) {
                    return $this->default_mcm_options[$option_name];
                } else {
                    return $default_value;
                }
            
            }
            
            return $default_value;
        }
        
        /**
         * Set the value of the option requested
         * 
         * @since 2.0.0
         * @return html
         */
        public function set_value( $option_name = '', $new_value = '' )
        {
            // $this->debugMP('msg',__FUNCTION__.' started for ' . $option_name );
            // Check whether the option requested is valid
            if ( $option_name == '' ) {
                return;
            }
            // Set the new option value
            $this->mcm_options[$option_name] = $new_value;
        }
        
        /**
         * Write the options back to the database
         * 
         * @since 2.0.0
         * @return html
         */
        public function update_mcm_options()
        {
            global  $wp_mcm_taxonomy_admin ;
            // $this->debugMP('msg',__FUNCTION__.' started.');
            // Check value of wp_mcm_flush_rewrite_rules to determine whether to flush
            if ( $this->is_true( 'wp_mcm_flush_rewrite_rules' ) ) {
            }
            // Get some settings
            $this->debugMP( 'pr', __FUNCTION__ . ' update_mcm_options to:', $this->mcm_options );
            update_option( WP_MCM_OPTION_NAME, $this->mcm_options );
            // Update the mcm_flush_rewrite_rules
            $wp_mcm_taxonomy_admin->mcm_flush_rewrite_rules();
        }
        
        /**
         * Make a slug from the title provided
         * 
         * @since 2.0.0
         *
         * @param string $title
         * @param string $prefix
         * @param string $use_dashes
         */
        function wp_mcm_make_slug( $title = '', $prefix = '', $use_dashes = true )
        {
            $wp_mcm_slug = $prefix . $title;
            $wp_mcm_slug = sanitize_key( $wp_mcm_slug );
            
            if ( $use_dashes ) {
                $wp_mcm_slug = str_replace( '_', '-', $wp_mcm_slug );
            } else {
                $wp_mcm_slug = str_replace( '-', '_', $wp_mcm_slug );
            }
            
            return $wp_mcm_slug;
        }
        
        /**
         * Make a wp_mcm_toggle_assign_ slug from the slug provided
         * 
         * @since 2.0.0
         *
         * @param string $slug
         */
        function wp_mcm_make_slug_toggle_assign( $slug = '' )
        {
            return 'wp_mcm_toggle_assign_' . $slug;
        }
        
        /**
         * Get the options for the dropdown
         * 
         * @since 2.0.0
         *
         */
        function get_media_taxonomy_to_use_options()
        {
            global  $wp_mcm_taxonomy ;
            // $this->debugMP('msg',__FUNCTION__.' started.');
            $wp_mcm_media_taxonomy_to_use = $this->get_value( 'wp_mcm_media_taxonomy_to_use' );
            $wp_mcm_default_media_category = $this->get_value( 'wp_mcm_default_media_category' );
            // Get the $media_taxonomies available
            $media_taxonomies = $wp_mcm_taxonomy->mcm_get_media_taxonomies();
            // $this->debugMP('pr',__FUNCTION__  . ' media_taxonomies found:', $media_taxonomies);
            // Create the dropdown list for each taxonomy found
            $wp_mcm_default_section_options = array();
            
            if ( is_wp_error( $media_taxonomies ) ) {
                $wp_mcm_default_section_options[] = array(
                    'option_label' => __( 'No taxonomy', 'wp-media-category-management' ),
                    'option_value' => WP_MCM_OPTION_NONE,
                );
            } else {
                foreach ( $media_taxonomies as $media_taxonomy ) {
                    $wp_mcm_default_section_options[] = array(
                        'option_label' => $media_taxonomy['label'],
                        'option_value' => $media_taxonomy['name'],
                    );
                }
            }
            
            return $wp_mcm_default_section_options;
        }
        
        /**
         * Get the options for the dropdown
         * 
         * @since 2.0.0
         *
         */
        function get_default_media_category_options( $none_label = '' )
        {
            global  $wp_mcm_taxonomy ;
            global  $wp_mcm_walker_category_filter ;
            // $this->debugMP('msg',__FUNCTION__.' started.');
            $wp_mcm_show_option_none_label = __( 'No default category', 'wp-media-category-management' );
            $wp_mcm_show_option_none_label = ( $none_label == '' ? $wp_mcm_show_option_none_label : $none_label );
            $wp_mcm_media_taxonomy_to_use = $this->get_value( 'wp_mcm_media_taxonomy_to_use' );
            $wp_mcm_default_media_category = $this->get_value( 'wp_mcm_default_media_category' );
            $wp_mcm_default_media_category_options = array();
            // Only show_option_none when no POST Taxonomy
            if ( $wp_mcm_media_taxonomy_to_use !== WP_MCM_POST_TAXONOMY ) {
                $wp_mcm_default_media_category_options[] = array(
                    'option_label' => $wp_mcm_show_option_none_label,
                    'option_value' => WP_MCM_OPTION_NONE,
                );
            }
            // Only show_option_none when no POST Taxonomy
            
            if ( $wp_mcm_media_taxonomy_to_use == WP_MCM_POST_TAXONOMY ) {
                $wp_mcm_show_option_none = '';
            } else {
                $wp_mcm_show_option_none = $wp_mcm_show_option_none_label;
            }
            
            $dropdown_options = array(
                'taxonomy'          => $wp_mcm_media_taxonomy_to_use,
                'hide_empty'        => false,
                'hierarchical'      => true,
                'orderby'           => 'name',
                'walker'            => $wp_mcm_walker_category_filter,
                'show_count'        => false,
                'show_option_none'  => $wp_mcm_show_option_none,
                'option_none_value' => WP_MCM_OPTION_NONE,
            );
            // $this->debugMP('pr',__FUNCTION__.' found dropdown_options: ', $dropdown_options );
            // Get the terms_list
            $media_taxonomy_terms_list = get_terms( $wp_mcm_media_taxonomy_to_use, $dropdown_options );
            // $this->debugMP('pr',__FUNCTION__ . ' media_taxonomy_terms_list = !', $media_taxonomy_terms_list );
            // Create the dropdown list for each media_taxonomy_term found
            
            if ( is_wp_error( $media_taxonomy_terms_list ) ) {
                $wp_mcm_default_media_category_options[] = array(
                    'option_label' => $wp_mcm_show_option_none_label,
                    'option_value' => WP_MCM_OPTION_NONE,
                );
            } else {
                foreach ( $media_taxonomy_terms_list as $media_taxonomy_term ) {
                    $wp_mcm_default_media_category_options[] = array(
                        'option_label' => $media_taxonomy_term->name,
                        'option_value' => $media_taxonomy_term->slug,
                    );
                }
            }
            
            // $this->debugMP('pr',__FUNCTION__ . ' wp_mcm_default_media_category_options = !', $wp_mcm_default_media_category_options );
            return $wp_mcm_default_media_category_options;
        }
        
        /**
         * Set the settings for general options
         * 
         * @since 2.0.0
         * @return html
         */
        public function set_mcm_settings_params( $mcm_settings_params_input = false )
        {
            global  $wp_mcm_plugin ;
            // Get some settings
            $this->mcm_settings_params = $mcm_settings_params_input;
            $mcm_settings_params_section = WP_MCM_SECTION_GEN;
            $this->debugMP( 'msg', __FUNCTION__ . ' started for section ' . $mcm_settings_params_section );
            // Set wp_mcm_options parameters
            //
            $mcm_settings_params_name = 'wp_mcm_toggle_assign';
            $this->mcm_settings_params[$mcm_settings_params_name] = array(
                'label'       => __( 'Toggle Assign', 'wp-media-category-management' ),
                'name'        => $mcm_settings_params_name,
                'slug'        => $this->wp_mcm_make_slug( $mcm_settings_params_name, WP_MCM_SECTION_PREFIX ),
                'type'        => WP_MCM_SETTINGS_TYPE_CHECKBOX,
                'section'     => $mcm_settings_params_section,
                'value'       => $this->get_value( $mcm_settings_params_name ),
                'description' => __( 'Show category toggles in list view?', 'wp-media-category-management' ),
            );
            $mcm_settings_params_name = 'wp_mcm_media_taxonomy_to_use';
            $this->mcm_settings_params[$mcm_settings_params_name] = array(
                'label'       => __( 'Media Taxonomy To Use', 'wp-media-category-management' ),
                'name'        => $mcm_settings_params_name,
                'slug'        => $this->wp_mcm_make_slug( $mcm_settings_params_name, WP_MCM_SECTION_PREFIX ),
                'type'        => WP_MCM_SETTINGS_TYPE_DROPDOWN,
                'section'     => $mcm_settings_params_section,
                'value'       => $this->get_value( $mcm_settings_params_name ),
                'description' => '<span> ' . __( 'Which taxonomy should be used to manage the media?', 'wp-media-category-management' ) . '<br/> ' . '(P) ' . __( 'means that the taxonomy is also used for posts.', 'wp-media-category-management' ) . '<br/> ' . '(T) ' . __( 'means that the taxonomy is also used as tags for posts.', 'wp-media-category-management' ) . '<br/> ' . '(*) ' . __( 'means that the taxonomy may have been registered previously, e.g. by another plugin.', 'wp-media-category-management' ) . '<br/> ' . '[#<strong>X</strong>] ' . sprintf( __( 'means that the taxonomy is currently assigned to %s attachments.', 'wp-media-category-management' ), '<strong>X</strong>' ) . '</span>',
                'default'     => WP_MCM_MEDIA_TAXONOMY,
                'options'     => $this->get_media_taxonomy_to_use_options(),
            );
            $mcm_settings_params_name = 'wp_mcm_category_base';
            $this->mcm_settings_params[$mcm_settings_params_name] = array(
                'label'       => __( 'MCM Category base', 'wp-media-category-management' ),
                'name'        => $mcm_settings_params_name,
                'slug'        => $this->wp_mcm_make_slug( $mcm_settings_params_name, WP_MCM_SECTION_PREFIX ),
                'type'        => WP_MCM_SETTINGS_TYPE_TEXT,
                'readonly'    => false,
                'section'     => $mcm_settings_params_section,
                'value'       => $this->get_value( $mcm_settings_params_name ),
                'description' => sprintf( __( 'If you like, you may enter a custom structure for your MCM category URL here. For example, using <code>media</code> as your category base would make your category links look like <code>%s/media/uncategorized/</code>. If you leave it blank the default will be used.', 'wp-media-category-management' ), get_option( 'home' ) ),
            );
            $mcm_settings_params_name = 'wp_mcm_custom_taxonomy_name';
            $this->mcm_settings_params[$mcm_settings_params_name] = array(
                'label'       => __( 'Name for Custom MCM Taxonomy', 'wp-media-category-management' ),
                'name'        => $mcm_settings_params_name,
                'slug'        => $this->wp_mcm_make_slug( $mcm_settings_params_name, WP_MCM_SECTION_PREFIX ),
                'type'        => WP_MCM_SETTINGS_TYPE_TEXT,
                'readonly'    => false,
                'section'     => $mcm_settings_params_section,
                'value'       => $this->get_value( $mcm_settings_params_name ),
                'description' => __( 'What text should be used as <strong>plural</strong> name for the Custom MCM Taxonomy?', 'wp-media-category-management' ),
            );
            $mcm_settings_params_name = 'wp_mcm_custom_taxonomy_name_single';
            $this->mcm_settings_params[$mcm_settings_params_name] = array(
                'label'       => __( 'Custom Singular Name', 'wp-media-category-management' ),
                'name'        => $mcm_settings_params_name,
                'slug'        => $this->wp_mcm_make_slug( $mcm_settings_params_name, WP_MCM_SECTION_PREFIX ),
                'type'        => WP_MCM_SETTINGS_TYPE_TEXT,
                'readonly'    => false,
                'section'     => $mcm_settings_params_section,
                'value'       => $this->get_value( $mcm_settings_params_name ),
                'description' => __( 'What text should be used as <strong>singular</strong> name for the Custom MCM Taxonomy?', 'wp-media-category-management' ),
            );
            $mcm_settings_params_name = 'wp_mcm_use_default_category';
            $this->mcm_settings_params[$mcm_settings_params_name] = array(
                'label'       => __( 'Use Default Category', 'wp-media-category-management' ),
                'name'        => $mcm_settings_params_name,
                'slug'        => $this->wp_mcm_make_slug( $mcm_settings_params_name, WP_MCM_SECTION_PREFIX ),
                'type'        => WP_MCM_SETTINGS_TYPE_CHECKBOX,
                'section'     => $mcm_settings_params_section,
                'value'       => $this->get_value( $mcm_settings_params_name ),
                'description' => __( 'Use the default category when adding or editing an attachment?', 'wp-media-category-management' ),
            );
            $mcm_settings_params_name = 'wp_mcm_default_media_category';
            $this->mcm_settings_params[$mcm_settings_params_name] = array(
                'label'       => __( 'Default Media Category', 'wp-media-category-management' ),
                'name'        => $mcm_settings_params_name,
                'slug'        => $this->wp_mcm_make_slug( $mcm_settings_params_name, WP_MCM_SECTION_PREFIX ),
                'type'        => WP_MCM_SETTINGS_TYPE_DROPDOWN,
                'section'     => $mcm_settings_params_section,
                'value'       => $this->get_value( $mcm_settings_params_name ),
                'description' => __( 'Which category of the selected media taxonomy should be used as default?', 'wp-media-category-management' ),
                'default'     => WP_MCM_OPTION_NONE,
                'options'     => $this->get_default_media_category_options(),
            );
            $mcm_settings_params_name = 'wp_mcm_use_post_taxonomy';
            $this->mcm_settings_params[$mcm_settings_params_name] = array(
                'label'       => __( 'Use Post Taxonomy', 'wp-media-category-management' ),
                'name'        => $mcm_settings_params_name,
                'slug'        => $this->wp_mcm_make_slug( $mcm_settings_params_name, WP_MCM_SECTION_PREFIX ),
                'type'        => WP_MCM_SETTINGS_TYPE_CHECKBOX,
                'section'     => $mcm_settings_params_section,
                'value'       => $this->get_value( $mcm_settings_params_name ),
                'description' => __( 'Use the category used for posts also explicitly for attachments?', 'wp-media-category-management' ),
            );
            $mcm_settings_params_name = 'wp_mcm_search_media_library';
            $this->mcm_settings_params[$mcm_settings_params_name] = array(
                'label'       => __( 'Search Media Library', 'wp-media-category-management' ),
                'name'        => $mcm_settings_params_name,
                'slug'        => $this->wp_mcm_make_slug( $mcm_settings_params_name, WP_MCM_SECTION_PREFIX ),
                'type'        => WP_MCM_SETTINGS_TYPE_CHECKBOX,
                'section'     => $mcm_settings_params_section,
                'value'       => $this->get_value( $mcm_settings_params_name ),
                'description' => __( 'Also search the media library for matching titles when searching?', 'wp-media-category-management' ),
            );
            $mcm_settings_params_name = 'wp_mcm_use_gutenberg_filter';
            $this->mcm_settings_params[$mcm_settings_params_name] = array(
                'label'       => __( 'Use Gutenberg Filter', 'wp-media-category-management' ),
                'name'        => $mcm_settings_params_name,
                'slug'        => $this->wp_mcm_make_slug( $mcm_settings_params_name, WP_MCM_SECTION_PREFIX ),
                'type'        => WP_MCM_SETTINGS_TYPE_CHECKBOX,
                'section'     => $mcm_settings_params_section,
                'value'       => $this->get_value( $mcm_settings_params_name ),
                'description' => __( 'Use the media filter on Gutenberg blocks for posts and pages?', 'wp-media-category-management' ),
            );
            // Get additional information on when to display the notice_status
            $notice_date_time = $this->get_value( 'wp_mcm_notice_activation_date' ) - time();
            $notice_date_message = '';
            if ( $notice_date_time > 0 ) {
                $notice_date_message = sprintf( ' in %s', human_time_diff( $this->get_value( 'wp_mcm_notice_activation_date' ), time() ) );
            }
            $formatted_date = date_i18n( $wp_mcm_plugin->wp_mcm_datetime_format, strtotime( $this->get_value( 'wp_mcm_notice_activation_date' ) ) );
            $this->debugMP( 'msg', __FUNCTION__ . ' wp_mcm_notice_activation_date = ' . $formatted_date );
            $mcm_settings_params_name = 'wp_mcm_notice_status';
            $this->mcm_settings_params[$mcm_settings_params_name] = array(
                'label'       => __( 'Show Notice', 'wp-media-category-management' ),
                'name'        => $mcm_settings_params_name,
                'slug'        => $this->wp_mcm_make_slug( $mcm_settings_params_name, WP_MCM_SECTION_PREFIX ),
                'type'        => WP_MCM_SETTINGS_TYPE_CHECKBOX,
                'section'     => $mcm_settings_params_section,
                'value'       => $this->get_value( $mcm_settings_params_name ),
                'description' => sprintf( '' . __( 'Show the notice message (again)%s?', 'wp-media-category-management' ), $notice_date_message ),
            );
            return $this->mcm_settings_params;
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