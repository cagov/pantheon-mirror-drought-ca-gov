<?php

/**
 * WP Media Category Management Taxonomy Admin class
 * 
 * @since  2.0.0
 * @author DeBAAT
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !class_exists( 'WP_MCM_Taxonomy_Admin' ) ) {
    class WP_MCM_Taxonomy_Admin
    {
        /**
         * Class constructor
         */
        function __construct()
        {
            $this->includes();
            $this->init();
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
        }
        
        /**
         * Init the required classes.
         *
         * @since 2.0.0
         * @return void
         */
        public function init()
        {
            global  $wpdb ;
            global  $wp_mcm_options ;
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            // add_filter('wp_mcm_meta_box_fields',       array($this, 'filter_wp_mcm_meta_box_fields'          )           );
            // Configure some settings
            $this->mcm_change_category_update_count_callback();
            $this->debugMP( 'msg', __FUNCTION__ . ' add_filter for several functions.' );
        }
        
        /**
         * Add cross-element hooks & filters.
         *
         * Haven't yet moved all items to the AJAX and UI classes.
         */
        function add_hooks_and_filters()
        {
            // $this->debugMP('msg', __FUNCTION__ . ' started.');
            // Some filters and action to process categories
            add_filter(
                'attachment_fields_to_edit',
                array( $this, 'mcm_attachment_fields_to_edit' ),
                10,
                2
            );
            add_action( 'wp_ajax_save-attachment-compat', array( $this, 'mcm_save_attachment_compat' ), 0 );
            add_filter( 'request', array( $this, 'mcm_request_admin' ) );
            add_filter(
                'wp_get_attachment_link',
                array( $this, 'mcm_wp_get_attachment_link_admin' ),
                10,
                6
            );
        }
        
        /**
         * Flush rewrite when necessary, e.g. when the definition of post_types changed.
         *
         * @since 2.0.0
         * @return void
         */
        public function mcm_flush_rewrite_rules()
        {
            global  $wp_mcm_options ;
            global  $wp_mcm_taxonomy ;
            // Get media taxonomy to use
            $media_taxonomy_to_use = $wp_mcm_taxonomy->mcm_get_media_taxonomy();
            $this->debugMP( 'msg', __FUNCTION__ . ' taxonomy = ' . $media_taxonomy_to_use );
            // Set the category_base to use for the taxonomy rewrite rule
            $wp_mcm_category_base = $wp_mcm_options->get_value( 'wp_mcm_category_base' );
            $wp_mcm_category_base = ( empty($wp_mcm_category_base) ? WP_MCM_MEDIA_TAXONOMY : $wp_mcm_category_base );
            // Write a rewrite_rule for the wp_mcm_category_base found
            add_rewrite_rule( $wp_mcm_category_base . '/([^/]+)/?$', 'index.php?' . WP_MCM_MEDIA_TAXONOMY_QUERY . '=' . $wp_mcm_category_base . '&' . $media_taxonomy_to_use . '=$matches[1]', 'top' );
            // add_rewrite_tag( "%$this->name%", $tag, $this->query_var ? "{$this->query_var}=" : "taxonomy=$this->name&term=" );
            // add_permastruct( $media_taxonomy_to_use, $wp_mcm_category_base . '/%' . $media_taxonomy_to_use . '%', true );
            // add_rewrite_tag( "%$this->name%", $tag, $this->query_var ? "{$this->query_var}=" : "taxonomy=$this->name&term=" );
            // add_permastruct( $this->name, "{$this->rewrite['slug']}/%$this->name%", $this->rewrite );
            // Flush rewrite when necessary, e.g. when the definition of post_types changed
            flush_rewrite_rules();
            $this->debugMP( 'msg', __FUNCTION__ . ' for wp_mcm_category_base = ' . $wp_mcm_category_base . ', media_taxonomy_to_use = ' . $media_taxonomy_to_use );
        }
        
        /**
         * Retrieve an attachment page link using an image or icon, if possible.
         *
         * @return string HTML content.
         */
        public function mcm_wp_get_attachment_link_admin(
            $html = '',
            $id = 0,
            $size = 'thumbnail',
            $permalink = false,
            $icon = false,
            $text = false
        )
        {
            global  $wp_mcm_plugin ;
            global  $wp_mcm_taxonomy ;
            $mcm_html = $html;
            // Check shortcode_attribute show_category_link
            $mcm_show_category_link = '';
            if ( isset( $wp_mcm_plugin->mcm_shortcode_attributes['show_category_link'] ) ) {
                $mcm_show_category_link = $wp_mcm_plugin->mcm_shortcode_attributes['show_category_link'];
            }
            if ( $mcm_show_category_link == '' ) {
                // If not link required, then just return the html received
                return $mcm_html;
            }
            // Check $media_taxonomy
            $media_taxonomy = $wp_mcm_taxonomy->mcm_get_media_taxonomy();
            $terms = wp_get_object_terms( $id, $media_taxonomy );
            // Get the category_link for each taxonomy term found
            foreach ( $terms as $term ) {
                $this->debugMP( 'pr', __FUNCTION__ . ' post[' . $id . '][' . $media_taxonomy . '] term =', $term );
                $category_link = get_category_link( $term->term_taxonomy_id );
                $this->debugMP( 'msg', '', __FUNCTION__ . ' term->term_taxonomy_id = ' . $term->term_taxonomy_id . ', category_link = ' . $category_link );
                // Add the category_link to the mcm_html
                $mcm_html .= '<a href="' . esc_url( $category_link ) . '">' . $term->name . '</a>';
                // Add an optional separation element, default is a space
                switch ( strtoupper( $mcm_show_category_link ) ) {
                    case 'BR':
                    case 'NL':
                        $mcm_html .= '<br>';
                        break;
                    case ' ':
                    default:
                        $mcm_html .= ' ';
                        break;
                }
            }
            return $mcm_html;
        }
        
        /**
         * Implement the request to filter media without category
         *
         * @since 2.0.0
         * @return void
         */
        function mcm_request_admin( $query_args )
        {
            global  $wp_mcm_taxonomy ;
            // $this->debugMP('pr', __FUNCTION__ . ' query = ', $query_args);
            // Get media taxonomy
            $media_taxonomy = $wp_mcm_taxonomy->mcm_get_media_taxonomy();
            $this->debugMP( 'pr', __FUNCTION__ . ' taxonomy = ' . $media_taxonomy . ' query_args = ', $query_args );
            $mediaCategory = $wp_mcm_taxonomy->mcm_get_no_category_search();
            
            if ( $mediaCategory != '' ) {
                // Fix the search settings to search for NO Category
                $this->debugMP( 'msg', __FUNCTION__ . ' Valid request: Filter on not part of category: ' . $mediaCategory );
                // Find all posts for the current mediaCategory to use for filtering them out
                $all_attachments = $wp_mcm_taxonomy->mcm_get_posts_for_media_taxonomy( $mediaCategory );
                $post_not_in = array();
                foreach ( $all_attachments as $key => $val ) {
                    $post_not_in[] = $val->ID;
                }
                $query_args['post__not_in'] = $post_not_in;
                $this->debugMP( 'pr', __FUNCTION__ . ' post_not_in = ', $post_not_in );
                // Reset the search query parameters to search for all attachments
                $query_args[$mediaCategory] = 0;
            } else {
                // Check for filtering tags
                
                if ( $media_taxonomy == WP_MCM_TAGS_TAXONOMY ) {
                    // Fix the search settings to search for NO Category
                    if ( isset( $_REQUEST[WP_MCM_TAGS_TAXONOMY] ) && $_REQUEST[WP_MCM_TAGS_TAXONOMY] != WP_MCM_OPTION_ALL_CAT ) {
                        $query_args['tag'] = $_REQUEST[WP_MCM_TAGS_TAXONOMY];
                    }
                    $this->debugMP( 'pr', __FUNCTION__ . ' Reworked query_args for tags to: ', $query_args );
                }
            
            }
            
            $this->debugMP( 'pr', __FUNCTION__ . ' RETURN query_args = ', $query_args );
            return $query_args;
        }
        
        /**
         * Filter the columns shown depending on taxonomy choosen
         *
         * @since 2.0.0
         * @return void
         */
        function mcm_attachment_fields_to_edit( $form_fields, $post )
        {
            global  $wp_mcm_walker_category_mediagrid_checklist ;
            // $this->debugMP('pr',__FUNCTION__ . ' started with form_fields = ', $form_fields);
            // $this->debugMP('pr',__FUNCTION__ . ' post = ', $post);
            
            if ( 'attachment' !== $post->post_type ) {
                $this->debugMP( 'msg', __FUNCTION__ . ' returns original form_fields because post_type != attachment but = ' . $post->post_type );
                return $form_fields;
            }
            
            foreach ( get_attachment_taxonomies( $post->ID ) as $taxonomy ) {
                $cur_taxonomy = (array) get_taxonomy( $taxonomy );
                if ( !$cur_taxonomy['public'] || !$cur_taxonomy['show_ui'] ) {
                    continue;
                }
                if ( empty($cur_taxonomy['label']) ) {
                    $cur_taxonomy['label'] = $taxonomy;
                }
                if ( empty($cur_taxonomy['args']) ) {
                    $cur_taxonomy['args'] = array();
                }
                $terms = get_object_term_cache( $post->ID, $taxonomy );
                if ( false === $terms ) {
                    $terms = wp_get_object_terms( $post->ID, $taxonomy, $t['args'] );
                }
                // Get the values in a list
                $values = array();
                foreach ( $terms as $term ) {
                    $values[] = $term->slug;
                }
                $cur_taxonomy['value'] = join( ', ', $values );
                $cur_taxonomy['show_in_edit'] = false;
                
                if ( $cur_taxonomy['hierarchical'] || $taxonomy == WP_MCM_TAGS_TAXONOMY ) {
                    ob_start();
                    wp_terms_checklist( $post->ID, array(
                        'taxonomy'      => $taxonomy,
                        'checked_ontop' => false,
                        'walker'        => $wp_mcm_walker_category_mediagrid_checklist,
                    ) );
                    
                    if ( ob_get_contents() != false ) {
                        $html = '<ul class="term-list">' . ob_get_contents() . '</ul>';
                    } else {
                        $html = '<ul class="term-list"><li>No ' . $cur_taxonomy['label'] . '</li></ul>';
                    }
                    
                    ob_end_clean();
                    $cur_taxonomy['input'] = 'html';
                    $cur_taxonomy['html'] = $html;
                }
                
                $form_fields[$taxonomy] = $cur_taxonomy;
            }
            $this->debugMP( 'pr', __FUNCTION__ . ' returns form_fields = ', $form_fields );
            return $form_fields;
        }
        
        /**
         * Save tag field from attachment edit menu
         *
         * @since 2.0.0
         * @return void
         */
        function mcm_attachment_fields_to_save( $post, $attachment )
        {
            $tags = esc_attr( $_POST['attachments'][$post['ID']]['tags'] );
            $tag_arr = explode( ',', $tags );
            wp_set_object_terms( $post['ID'], $tag_arr, 'post_tag' );
            return $post;
        }
        
        /** 
         *  mcm_save_attachment_compat
         *
         *  Based on /wp-admin/includes/ajax-actions.php
         *
         *  @since    2.0.0
         */
        function mcm_save_attachment_compat()
        {
            if ( !isset( $_REQUEST['id'] ) ) {
                wp_send_json_error();
            }
            if ( !($id = absint( $_REQUEST['id'] )) ) {
                wp_send_json_error();
            }
            if ( empty($_REQUEST['attachments']) || empty($_REQUEST['attachments'][$id]) ) {
                wp_send_json_error();
            }
            $attachment_data = $_REQUEST['attachments'][$id];
            check_ajax_referer( 'update-post_' . $id, 'nonce' );
            if ( !current_user_can( 'edit_post', $id ) ) {
                wp_send_json_error();
            }
            $post = get_post( $id, ARRAY_A );
            if ( 'attachment' != $post['post_type'] ) {
                wp_send_json_error();
            }
            /** This filter is documented in wp-admin/includes/media.php */
            $post = apply_filters( 'attachment_fields_to_save', $post, $attachment_data );
            
            if ( isset( $post['errors'] ) ) {
                $errors = $post['errors'];
                // @todo return me and display me!
                unset( $post['errors'] );
            }
            
            wp_update_post( $post );
            foreach ( get_attachment_taxonomies( $post ) as $taxonomy ) {
                
                if ( isset( $attachment_data[$taxonomy] ) ) {
                    wp_set_object_terms(
                        $id,
                        array_map( 'trim', preg_split( '/,+/', $attachment_data[$taxonomy] ) ),
                        $taxonomy,
                        false
                    );
                } elseif ( isset( $_REQUEST['tax_input'] ) && isset( $_REQUEST['tax_input'][$taxonomy] ) ) {
                    wp_set_object_terms(
                        $id,
                        $_REQUEST['tax_input'][$taxonomy],
                        $taxonomy,
                        false
                    );
                } else {
                    wp_set_object_terms(
                        $id,
                        '',
                        $taxonomy,
                        false
                    );
                }
            
            }
            if ( !($attachment = wp_prepare_attachment_for_js( $id )) ) {
                wp_send_json_error();
            }
            wp_send_json_success( $attachment );
            // don't forget to end your scripts with a die() function - very important
            die;
        }
        
        /**
         * Change default update_count_callback for category and tags taxonomies
         *
         * @since 2.0.0
         * @return void
         */
        function mcm_change_category_update_count_callback()
        {
            global  $wp_taxonomies ;
            global  $wp_mcm_taxonomy ;
            // Get media taxonomy
            $media_taxonomy = $wp_mcm_taxonomy->mcm_get_media_taxonomy();
            $this->debugMP( 'msg', __FUNCTION__ . ' taxonomy = ' . $media_taxonomy );
            // Reset count_callback for WP_MCM_POST_TAXONOMY
            if ( $media_taxonomy == WP_MCM_POST_TAXONOMY ) {
                
                if ( taxonomy_exists( WP_MCM_POST_TAXONOMY ) ) {
                    $new_arg =& $wp_taxonomies[WP_MCM_POST_TAXONOMY]->update_count_callback;
                    $new_arg = array( $wp_mcm_taxonomy, 'mcm_update_count_callback' );
                }
            
            }
            // Reset count_callback for WP_MCM_TAGS_TAXONOMY
            if ( $media_taxonomy == WP_MCM_TAGS_TAXONOMY ) {
                
                if ( taxonomy_exists( WP_MCM_TAGS_TAXONOMY ) ) {
                    $new_arg =& $wp_taxonomies[WP_MCM_TAGS_TAXONOMY]->update_count_callback;
                    $new_arg = array( $wp_mcm_taxonomy, 'mcm_update_count_callback' );
                }
            
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