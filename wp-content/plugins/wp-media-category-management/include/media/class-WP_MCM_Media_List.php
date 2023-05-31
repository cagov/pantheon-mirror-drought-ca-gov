<?php

/**
 * WP Media Category Management Media List class
 * 
 * @since  2.0.0
 * @author DeBAAT
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !class_exists( 'WP_MCM_Media_List' ) ) {
    class WP_MCM_Media_List
    {
        /**
         * The default media_taxonomy to use.
         *
         * @var 
         */
        public  $media_taxonomy = '' ;
        /**
         * Class constructor
         */
        function __construct()
        {
            global  $wp_mcm_taxonomy ;
            $this->includes();
            $this->add_hooks_and_filters();
            // Get media taxonomy
            $this->media_taxonomy = $wp_mcm_taxonomy->mcm_get_media_taxonomy();
            $this->debugMP( 'msg', __FUNCTION__ . ' media_taxonomy = ' . $this->media_taxonomy );
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
         * Add cross-element hooks & filters.
         *
         * Haven't yet moved all items to the AJAX and UI classes.
         */
        function add_hooks_and_filters()
        {
            // $this->debugMP('msg', __FUNCTION__ . ' started.');
            // Manage columns for attachments showing in the list
            add_filter(
                'manage_taxonomies_for_attachment_columns',
                array( $this, 'mcm_manage_taxonomies_for_attachment_columns' ),
                10,
                2
            );
            add_filter( 'manage_media_columns', array( $this, 'mcm_manage_media_columns' ) );
            add_filter( 'manage_upload_sortable_columns', array( $this, 'mcm_manage_upload_sortable_columns' ) );
            add_filter(
                'manage_media_custom_column',
                array( $this, 'mcm_manage_media_custom_column' ),
                10,
                2
            );
            // Manage row actions for attachments showing in the list
            add_filter(
                'media_row_actions',
                array( $this, 'mcm_media_row_actions' ),
                10,
                3
            );
            add_action( 'wp_ajax_' . WP_MCM_ACTION_ROW_TOGGLE, array( $this, 'mcm_wp_ajax_action_row_toggle' ), 0 );
            // Manage bulk actions for attachments showing in the list
            add_action( 'admin_footer-upload.php', array( $this, 'mcm_custom_bulk_admin_footer' ) );
            add_action( 'load-upload.php', array( $this, 'mcm_custom_bulk_action' ) );
            add_action( 'admin_notices', array( $this, 'mcm_custom_bulk_admin_notices' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'wp_mcm_media_list_scripts' ) );
        }
        
        /**
         * Add the required admin scripts.
         *
         * @since  2.0.0
         * @return void
         */
        public function wp_mcm_media_list_scripts()
        {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            // Register the JS file with a unique handle, file location, and an array of dependencies
            wp_register_script( "wp_mcm_row_toggle_script", WP_MCM_PLUGIN_URL . '/js/wp-mcm-media-list-toggle.js', array( 'jquery' ) );
            // localize the script to your domain name, so that you can reference the url to admin-ajax.php file easily
            wp_localize_script( 'wp_mcm_row_toggle_script', 'mcmAjax', array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
            ) );
            // enqueue jQuery library and the script you registered above
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'wp_mcm_row_toggle_script' );
        }
        
        /** 
         *  mcm_wp_ajax_action_row_toggle
         *
         *  Based on /wp-admin/includes/ajax-actions.php
         *
         *  @since    2.0.0
         */
        function mcm_wp_ajax_action_row_toggle()
        {
            $this->debugMP( 'pr', __FUNCTION__ . ' _REQUEST = ', $_REQUEST );
            // _wpnonce check for an extra layer of security, the function will exit if it fails
            if ( !isset( $_REQUEST['_wpnonce'] ) ) {
                wp_send_json_error();
            }
            if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], "mcm_toggle_taxonomy_nonce" ) ) {
                exit( __( 'Not allowed to change the value!', 'wp-media-category-management' ) );
            }
            // Check _REQUEST parameters
            if ( !isset( $_REQUEST['mcm_toggle_media'] ) || !isset( $_REQUEST['mcm_toggle_taxonomy'] ) || !isset( $_REQUEST['mcm_toggle_slug'] ) ) {
                wp_send_json_error();
            }
            if ( !($mcm_toggle_media = absint( $_REQUEST['mcm_toggle_media'] )) ) {
                wp_send_json_error();
            }
            $mcm_toggle_taxonomy = $_REQUEST['mcm_toggle_taxonomy'];
            $mcm_toggle_slug = $_REQUEST['mcm_toggle_slug'];
            // Toggle the media_category for this media_id
            $bulk_result = $this->mcm_toggle_slug_for_media( $mcm_toggle_media, $mcm_toggle_slug, $mcm_toggle_taxonomy );
            if ( is_wp_error( $bulk_result ) || $bulk_result === false ) {
                wp_send_json_error();
            }
            // Create the result to show on screen
            $mcm_toggle_result = array();
            $mcm_toggle_result['mcm_row_toggle_key'] = $this->mcm_create_taxonomy_id( $mcm_toggle_taxonomy, $mcm_toggle_media );
            $mcm_toggle_result['mcm_row_toggle_value'] = $this->mcm_create_column_taxonomy( $mcm_toggle_taxonomy, $mcm_toggle_media );
            $this->debugMP( 'pr', __FUNCTION__ . ' mcm_toggle_result = ', $mcm_toggle_result );
            wp_send_json_success( $mcm_toggle_result );
            // don't forget to end your scripts with a die() function - very important
            die;
        }
        
        /**
         *  mcm_media_row_actions
         *
         *  Add the row actions
         *
         *  @since    2.0.0
         */
        function mcm_media_row_actions( $row_actions, $media, $detached )
        {
            global  $wp_mcm_options ;
            // global $wp_mcm_taxonomy;
            // Get media taxonomy
            // $this->media_taxonomy = $wp_mcm_taxonomy->mcm_get_media_taxonomy();
            // $this->debugMP('msg',__FUNCTION__ . ' media_taxonomy = ' . $this->media_taxonomy);
            $media_terms = get_terms( $this->media_taxonomy, array(
                'hide_empty' => 0,
            ) );
            // $this->debugMP('pr', __FUNCTION__ . ' media_terms for :' . $media->ID, $media_terms);
            // Generate parameters for the actionlink_base
            $actionlink_nonce = wp_create_nonce( "mcm_toggle_taxonomy_nonce" );
            $actionlink_base = admin_url( 'admin-ajax.php' );
            $actionlink_base = add_query_arg( 'action', WP_MCM_ACTION_ROW_TOGGLE, $actionlink_base );
            $actionlink_base = add_query_arg( '_wpnonce', $actionlink_nonce, $actionlink_base );
            $actionlink_base = add_query_arg( 'mcm_toggle_taxonomy', $this->media_taxonomy, $actionlink_base );
            $actionlink_base = add_query_arg( 'mcm_toggle_media', $media->ID, $actionlink_base );
            $actionlink_prefix = __( 'Toggle', 'wp-media-category-management' ) . ' ';
            // Generate an action text and link for each term
            foreach ( $media_terms as $term ) {
                // Check whether the toggle_assign is desired
                $term_toggle_assign = $wp_mcm_options->is_true( 'wp_mcm_toggle_assign' );
                
                if ( $term_toggle_assign ) {
                    // Finish creating the action link for each media_term
                    $actionlink_url = add_query_arg( 'mcm_toggle_slug', $term->slug, $actionlink_base );
                    // Create a clickable label for the generated url
                    $actionlink_html = '<a class="submitdelete media_list_toggle" ';
                    $actionlink_html .= 'data-_wpnonce="' . $actionlink_nonce . '" ';
                    $actionlink_html .= 'data-mcm_toggle_taxonomy="' . $this->media_taxonomy . '" ';
                    $actionlink_html .= 'data-mcm_toggle_media="' . $media->ID . '" ';
                    $actionlink_html .= 'data-mcm_toggle_slug="' . $term->slug . '" ';
                    $actionlink_html .= ' href="';
                    $actionlink_html .= wp_nonce_url( $actionlink_url );
                    $actionlink_html .= '">';
                    $actionlink_html .= $actionlink_prefix;
                    $actionlink_prefix = ' ';
                    $actionlink_html .= '[<em>' . $term->name . '</em>]';
                    $actionlink_html .= '</a>';
                    $row_actions[] = $actionlink_html;
                }
            
            }
            return $row_actions;
        }
        
        /**
         *  mcm_create_sendback_url
         *
         *  Create a sendback_url for the bulk actions
         *
         *  @since    2.0.0
         */
        function mcm_create_sendback_url()
        {
            // Create a sendback url to report the results
            $sendback = remove_query_arg( array(
                'exported',
                'untrashed',
                'deleted',
                'ids'
            ), wp_get_referer() );
            if ( !$sendback || false === strpos( wp_get_referer(), 'upload.php' ) ) {
                $sendback = admin_url( "upload.php" );
            }
            // Remove some superfluous arguments
            $sendback = remove_query_arg( array(
                'action',
                'action2',
                'tags_input',
                'post_author',
                'comment_status',
                'ping_status',
                '_status',
                'post',
                'bulk_edit',
                'post_view'
            ), $sendback );
            // remember pagenumber
            $pagenum = ( isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0 );
            $sendback = add_query_arg( 'paged', $pagenum, $sendback );
            // remember orderby
            
            if ( isset( $_REQUEST['orderby'] ) ) {
                $sOrderby = $_REQUEST['orderby'];
                $sendback = add_query_arg( 'orderby', $sOrderby, $sendback );
            }
            
            // remember order
            
            if ( isset( $_REQUEST['order'] ) ) {
                $sOrder = $_REQUEST['order'];
                $sendback = add_query_arg( 'order', $sOrder, $sendback );
            }
            
            // remember filter settings
            
            if ( isset( $_REQUEST['mode'] ) ) {
                $sMode = $_REQUEST['mode'];
                $sendback = add_query_arg( 'mode', $sMode, $sendback );
            }
            
            
            if ( isset( $_REQUEST['mode'] ) ) {
                $sMode = $_REQUEST['mode'];
                $sendback = add_query_arg( 'mode', $sMode, $sendback );
            }
            
            
            if ( isset( $_REQUEST['m'] ) ) {
                $sM = $_REQUEST['m'];
                $sendback = add_query_arg( 'm', $sM, $sendback );
            }
            
            
            if ( isset( $_REQUEST['s'] ) ) {
                $sS = $_REQUEST['s'];
                $sendback = add_query_arg( 's', $sS, $sendback );
            }
            
            
            if ( isset( $_REQUEST['attachment-filter'] ) ) {
                $sAttachmentFilter = $_REQUEST['attachment-filter'];
                $sendback = add_query_arg( 'attachment-filter', $sAttachmentFilter, $sendback );
            }
            
            
            if ( isset( $_REQUEST['filter_action'] ) ) {
                $sFilterAction = $_REQUEST['filter_action'];
                $sendback = add_query_arg( 'filter_action', $sFilterAction, $sendback );
            }
            
            // Get media taxonomy
            
            if ( isset( $_REQUEST[$this->media_taxonomy] ) ) {
                $sMediaTaxonomy = $_REQUEST[$this->media_taxonomy];
                $sendback = add_query_arg( $this->media_taxonomy, $sMediaTaxonomy, $sendback );
            }
            
            return $sendback;
        }
        
        /**
         * Check the current action selected from the bulk actions dropdown.
         *
         * @since 2.0.0
         *
         * @return bool Whether WP_MCM_ACTION_BULK_TOGGLE was selected or not
         */
        function mcm_is_action_bulk_toggle()
        {
            if ( isset( $_REQUEST['action'] ) && WP_MCM_ACTION_BULK_TOGGLE == $_REQUEST['action'] ) {
                return true;
            }
            if ( isset( $_REQUEST['action2'] ) && WP_MCM_ACTION_BULK_TOGGLE == $_REQUEST['action2'] ) {
                return true;
            }
            return false;
        }
        
        /**
         * Step 1: add the custom Bulk Action to the select menus
         * For Media Category Management, the actual category should be used as parameter
         */
        function mcm_custom_bulk_admin_footer()
        {
            global  $post_type ;
            global  $wp_mcm_taxonomy ;
            // Make an array of post_type
            
            if ( is_array( $post_type ) ) {
                $mcm_post_type = $post_type;
            } else {
                $mcm_post_type = array();
                $mcm_post_type[] = $post_type;
            }
            
            // Check whether the post_type array contains attachment
            
            if ( in_array( 'attachment', $mcm_post_type ) ) {
                // Get media taxonomy and corresponding terms
                $media_taxonomy = $wp_mcm_taxonomy->mcm_get_media_taxonomy();
                $media_terms = get_terms( $media_taxonomy, 'hide_empty=0' );
                // If terms found ok then generate the additional bulk_actions
                
                if ( $media_terms && !is_wp_error( $media_terms ) ) {
                    // Create the box div string.
                    //
                    $onChangeTxtTop = "jQuery(\\'#bulk_tax_id\\').val(jQuery(\\'#bulk-action-selector-top option:selected\\').attr(\\'option_slug\\'));";
                    $onChangeTxtBottom = "jQuery(\\'#bulk_tax_id\\').val(jQuery(\\'#bulk-action-selector-bottom option:selected\\').attr(\\'option_slug\\'));";
                    // Start the script to add bulk code
                    $mcm_footer_script = "";
                    $mcm_footer_script .= " <script type=\"text/javascript\">";
                    $mcm_footer_script .= "jQuery(document).ready(function(){";
                    // Add new hidden field to store the term_slug
                    $mcm_footer_script .= "jQuery('#posts-filter').prepend('<input type=\"hidden\" id=\"bulk_tax_cat\" name=\"bulk_tax_cat\" value=\"" . $media_taxonomy . "\" />');";
                    $mcm_footer_script .= "jQuery('#posts-filter').prepend('<input type=\"hidden\" id=\"bulk_tax_id\" name=\"bulk_tax_id\" value=\"\" />');";
                    // Add new action to #bulk-action-selector-top
                    $mcm_footer_script .= "jQuery('#bulk-action-selector-top')";
                    $mcm_footer_script .= ".attr('onChange','" . $onChangeTxtTop . "')";
                    $mcm_footer_script .= ";";
                    // Add new action to #bulk-action-selector-bottom
                    $mcm_footer_script .= "jQuery('#bulk-action-selector-bottom')";
                    $mcm_footer_script .= ".attr('onChange','" . $onChangeTxtBottom . "')";
                    $mcm_footer_script .= ";";
                    // add bulk_actions for each category term
                    foreach ( $media_terms as $term ) {
                        $optionTxt = esc_js( __( 'Toggle', 'wp-media-category-management' ) . ' ' . $term->name );
                        $mcm_footer_script .= " jQuery('<option>').val('" . WP_MCM_ACTION_BULK_TOGGLE . "').attr('option_slug','" . $term->slug . "').text('" . $optionTxt . "').appendTo(\"select[name='action']\");";
                        $mcm_footer_script .= " jQuery('<option>').val('" . WP_MCM_ACTION_BULK_TOGGLE . "').attr('option_slug','" . $term->slug . "').text('" . $optionTxt . "').appendTo(\"select[name='action2']\");";
                    }
                    $mcm_footer_script .= '});';
                    $mcm_footer_script .= '</script>';
                    echo  $mcm_footer_script ;
                }
            
            }
        
        }
        
        /**
         * Step 2: handle the custom Bulk Action
         * 
         * Based on the post https://wordpress.stackexchange.com/questions/29822/custom-bulk-action
         */
        function mcm_custom_bulk_action()
        {
            // Check parameters provided
            if ( !isset( $_REQUEST['bulk_tax_cat'] ) ) {
                return;
            }
            if ( !isset( $_REQUEST['bulk_tax_id'] ) ) {
                return;
            }
            if ( !isset( $_REQUEST['media'] ) ) {
                return;
            }
            if ( !$this->mcm_is_action_bulk_toggle() ) {
                return;
            }
            // Set some variables
            $num_bulk_toggled = 0;
            $media_taxonomy = $_REQUEST['bulk_tax_cat'];
            $bulk_media_category_id = $_REQUEST['bulk_tax_id'];
            // Process all media_id s found in the request
            foreach ( (array) $_REQUEST['media'] as $media_id ) {
                $media_id = (int) $media_id;
                // Toggle the media_category for this media_id
                $bulk_result = $this->mcm_toggle_slug_for_media( $media_id, $bulk_media_category_id, $media_taxonomy );
                if ( is_wp_error( $bulk_result ) || $bulk_result === false ) {
                    return $bulk_result;
                }
                // Keep track of the number toggled
                $num_bulk_toggled++;
            }
            // Create a sendback url to refresh the screen and report the results
            $sendback = $this->mcm_create_sendback_url();
            $sendback = add_query_arg( array(
                'bulk_toggled' => $num_bulk_toggled,
            ), $sendback );
            wp_redirect( $sendback );
            exit;
        }
        
        /**
         * Step 3: display an admin notice on the Posts page after exporting
         */
        function mcm_custom_bulk_admin_notices()
        {
            global  $pagenow ;
            
            if ( $pagenow == 'upload.php' && isset( $_REQUEST['bulk_toggled'] ) && (int) $_REQUEST['bulk_toggled'] ) {
                $message = sprintf( _n(
                    'Media bulk toggled.',
                    '%s media bulk toggled.',
                    $_REQUEST['bulk_toggled'],
                    'wp-media-category-management'
                ), number_format_i18n( $_REQUEST['bulk_toggled'] ) );
                echo  "<div class=\"updated\"><p>{$message}</p></div>" ;
            }
        
        }
        
        /**
         * Filter the columns shown depending on taxonomy parameters
         *
         * @since 2.0.0
         * @return void
         */
        function mcm_manage_taxonomies_for_attachment_columns( $columns, $post_type )
        {
            // global $wp_mcm_taxonomy;
            global  $wp_mcm_options ;
            // Get media taxonomy
            // $this->media_taxonomy = $wp_mcm_taxonomy->mcm_get_media_taxonomy();
            $this->debugMP( 'pr', __FUNCTION__ . ' media_taxonomy = ' . $this->media_taxonomy . ', post_type = ' . $post_type . ', columns = ', $columns );
            // Filter the columns to show
            $filtered = array();
            foreach ( $columns as $key => $value ) {
                // 'Translate' value back again to taxonomy
                
                if ( 'categories' === $key ) {
                    $taxonomy_key = 'category';
                } elseif ( 'tags' === $key ) {
                    $taxonomy_key = 'post_tag';
                } elseif ( 0 === strpos( $key, 'taxonomy-' ) ) {
                    $taxonomy_key = substr( $key, 9 );
                } else {
                    $taxonomy_key = false;
                }
                
                
                if ( $taxonomy_key === WP_MCM_POST_TAXONOMY ) {
                    // Only add column for WP_MCM_POST_TAXONOMY when wp_mcm_use_post_taxonomy
                    if ( $wp_mcm_options->is_true( 'wp_mcm_use_post_taxonomy' ) ) {
                        $filtered[$key] = $value;
                    }
                } else {
                    $filtered[$key] = $value;
                }
            
            }
            $this->debugMP( 'pr', __FUNCTION__ . ' media_taxonomy = ' . $this->media_taxonomy . ' filtered = ', $filtered );
            return $filtered;
        }
        
        /**
         * Mark the taxonomy columns for special processing
         *
         * @param array $columns An array of columns displayed in the Media list table.
         *
         * @since 2.0.0
         * @return array $columns
         */
        public function mcm_manage_media_columns( $columns )
        {
            // global $wp_mcm_taxonomy;
            global  $wp_mcm_options ;
            // Get media taxonomy
            // $this->media_taxonomy = $wp_mcm_taxonomy->mcm_get_media_taxonomy();
            $this->debugMP( 'pr', __FUNCTION__ . ' media_taxonomy = ' . $this->media_taxonomy . ' columns = ', $columns );
            // Mark the taxonomy columns
            $marked_columns = array();
            foreach ( $columns as $key => $value ) {
                // 'Translate' value back again to taxonomy
                
                if ( 'categories' === $key ) {
                    $taxonomy_key = 'category';
                } elseif ( 'tags' === $key ) {
                    $taxonomy_key = 'post_tag';
                } elseif ( 0 === strpos( $key, 'taxonomy-' ) ) {
                    $taxonomy_key = substr( $key, 9 );
                } else {
                    $taxonomy_key = false;
                }
                
                switch ( $taxonomy_key ) {
                    case WP_MCM_MEDIA_TAXONOMY:
                    case WP_MCM_POST_TAXONOMY:
                    case WP_MCM_TAGS_TAXONOMY:
                        $marked_columns[WP_MCM_MEDIA_TAXONOMY_PREFIX . $taxonomy_key] = $value;
                        break;
                    default:
                        $marked_columns[$key] = $value;
                        break;
                }
            }
            $this->debugMP( 'pr', __FUNCTION__ . ' media_taxonomy = ' . $this->media_taxonomy . ' marked_columns = ', $marked_columns );
            return $marked_columns;
        }
        
        /**
         * Register sortcolumn
         *
         * @param array $columns An array of sort columns.
         *
         * @since 2.0.0
         * @return array $columns
         */
        public function mcm_manage_upload_sortable_columns( $columns )
        {
            // global $wp_mcm_taxonomy;
            global  $wp_mcm_options ;
            // Get media taxonomy
            // $this->media_taxonomy = $wp_mcm_taxonomy->mcm_get_media_taxonomy();
            $this->debugMP( 'pr', __FUNCTION__ . ' media_taxonomy = ' . $this->media_taxonomy . ' columns = ', $columns );
            return $columns;
        }
        
        /**
         * Get size and filetype of attachment
         *
         * @param string  $column_name Column name
         * @param integer $post_id     Id of attachment post
         *
         * @since 2.0.0
         * @return void
         */
        public function mcm_manage_media_custom_column( $column_name, $post_id )
        {
            // global $wp_mcm_taxonomy;
            global  $wp_mcm_options ;
            // Get media taxonomy
            // $this->media_taxonomy = $wp_mcm_taxonomy->mcm_get_media_taxonomy();
            $this->debugMP( 'msg', __FUNCTION__ . ' media_taxonomy = ' . $this->media_taxonomy . ', column_name = ' . $column_name . ', post_id = ' . $post_id . '.' );
            // Strip WP_MCM_MEDIA_TAXONOMY_PREFIX
            
            if ( 0 === strpos( $column_name, WP_MCM_MEDIA_TAXONOMY_PREFIX ) ) {
                $taxonomy_key = substr( $column_name, strlen( WP_MCM_MEDIA_TAXONOMY_PREFIX ) );
            } else {
                $taxonomy_key = false;
            }
            
            $taxonomy_class = 'mcm_media_list_class';
            $taxonomy_id = $this->mcm_create_taxonomy_id( $taxonomy_key, $post_id );
            $output_opening = '<div class="' . $taxonomy_class . '" id="' . $taxonomy_id . '">';
            $output_closing = '</div>';
            switch ( $taxonomy_key ) {
                case WP_MCM_MEDIA_TAXONOMY:
                case WP_MCM_POST_TAXONOMY:
                case WP_MCM_TAGS_TAXONOMY:
                    echo  $output_opening ;
                    echo  $this->mcm_create_column_taxonomy( $taxonomy_key, $post_id ) ;
                    echo  $output_closing ;
                    break;
                case false:
                    echo  $output_opening ;
                    echo  __( 'No MCM taxonomy', 'wp-media-category-management' ) ;
                    echo  $output_closing ;
                    break;
                default:
                    echo  $output_opening ;
                    echo  __( 'Default taxonomy', 'wp-media-category-management' ) ;
                    echo  ' ' . $taxonomy_id ;
                    echo  $output_closing ;
                    break;
            }
        }
        
        /**
         * Create a taxonomy_id based on key and post_id
         *
         * @param string  $taxonomy_key   The key of the taxonomy
         * @param integer $post_id        Id of attachment post
         *
         * @since 2.0.0
         * @return string $taxonomy_id
         */
        public function mcm_create_taxonomy_id( $taxonomy_key, $post_id )
        {
            // Create a taxonomy_id
            $taxonomy_id = WP_MCM_MEDIA_TAXONOMY_PREFIX . '___' . $taxonomy_key . '___' . $post_id;
            $this->debugMP( 'msg', __FUNCTION__ . ' taxonomy_id = ' . $taxonomy_id );
            return $taxonomy_id;
        }
        
        /**
         * Create a taxonomy_id based on key and post_id
         *
         * @param string  $taxonomy       The taxonomy
         * @param integer $post_id        Id of attachment post
         *
         * @since 2.0.0
         * @return string $column_taxonomy_output
         */
        public function mcm_create_column_taxonomy( $taxonomy, $post_id )
        {
            $this->debugMP( 'msg', __FUNCTION__ . ' taxonomy = ' . $taxonomy . ', post_id = ' . $post_id . '.' );
            // Get the taxonomies for this post_id
            $terms = get_the_terms( $post_id, $taxonomy );
            
            if ( is_array( $terms ) ) {
                $column_taxonomy_output = array();
                foreach ( $terms as $term ) {
                    $posts_in_term_qv = array();
                    $posts_in_term_qv['taxonomy'] = $taxonomy;
                    $posts_in_term_qv['term'] = $term->slug;
                    $column_taxonomy_output[] = sprintf( '<a href="%s">%s</a>', esc_url( add_query_arg( $posts_in_term_qv, 'upload.php' ) ), esc_html( sanitize_term_field(
                        'name',
                        $term->name,
                        $term->term_id,
                        $taxonomy,
                        'display'
                    ) ) );
                }
                $column_taxonomy_output = implode( wp_get_list_item_separator(), $column_taxonomy_output );
            } else {
                $column_taxonomy_output = '<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">' . get_taxonomy( $taxonomy )->labels->no_terms . '</span>';
            }
            
            return $column_taxonomy_output;
        }
        
        /**
         * Toggle the media_category for media_taxonomy and media_id
         *
         * @param integer $media_id         Id of media post
         * @param string  $media_category   The category
         * @param string  $media_taxonomy   The taxonomy
         *
         * @since 2.0.0
         * @return false | result $mcm_toggle_result
         */
        function mcm_toggle_slug_for_media( $media_id, $media_category, $media_taxonomy )
        {
            // Check parameters provided
            $media_id = (int) $media_id;
            // Check whether this user can edit this post
            if ( !current_user_can( 'edit_post', $media_id ) ) {
                return false;
            }
            // Check whether this post has the media_category already set or not
            
            if ( has_term( $media_category, $media_taxonomy, $media_id ) ) {
                // Set so remove the $bulk_media_category taxonomy from this media post
                $mcm_toggle_result = wp_remove_object_terms( $media_id, $media_category, $media_taxonomy );
            } else {
                // Not set so add the $bulk_media_category taxonomy to this media post
                $mcm_toggle_result = wp_set_object_terms(
                    $media_id,
                    $media_category,
                    $media_taxonomy,
                    true
                );
            }
            
            return $mcm_toggle_result;
        }
        
        /**
         * Simplify the plugin debugMP interface.
         *
         * Typical start of function call: $this->debugMP( 'msg', __FUNCTION__ . ' started!' );
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