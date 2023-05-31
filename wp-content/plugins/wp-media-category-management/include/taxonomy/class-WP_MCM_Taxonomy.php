<?php

/**
* WP Media Category Management Taxonomy class
* 

TODO: Check whether there can be an additional option per category to show or not on the media page

* @since  2.0.0
* @author DeBAAT
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !class_exists( 'WP_MCM_Taxonomy' ) ) {
    class WP_MCM_Taxonomy
    {
        /**
         * Parameters for handling the settable options for this plugin.
         *
         * @var mixed[] $mcm_taxonomys
         */
        public  $mcm_taxonomys = array() ;
        /**
         * Parameter to check whether this is a mcm_taxonomy_query
         *
         * @var boolean $mcm_taxonomy_category_to_find
         */
        public  $mcm_taxonomy_category_to_find = false ;
        /**
         * Class constructor
         */
        function __construct()
        {
            $this->includes();
            $this->initialize();
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
        public function initialize()
        {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            $this->mcm_taxonomy_category_to_find = false;
            // $this->mcm_register_media_taxonomy();
        }
        
        /**
         * Add the hooks and filters.
         *
         * @since 2.0.0
         * @return void
         */
        public function add_hooks_and_filters()
        {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            add_action( 'init', array( $this, 'mcm_register_media_taxonomy' ) );
            add_filter( 'pre_get_posts', array( $this, 'mcm_pre_get_posts' ) );
            add_filter(
                'wp_get_attachment_link',
                array( $this, 'mcm_wp_get_attachment_link_front' ),
                10,
                6
            );
            add_filter(
                'get_the_archive_title',
                array( $this, 'mcm_get_the_archive_title' ),
                10,
                3
            );
            $this->debugMP( 'msg', __FUNCTION__ . ' add_filter for several functions.' );
        }
        
        /**
         * Get the archive title when it is not found yet
         *
         * @since 2.1.0
         * @return void
         */
        function mcm_get_the_archive_title( $title, $original_title, $prefix )
        {
            global  $wp_query ;
            global  $wp_mcm_options ;
            // $this->debugMP('msg',__FUNCTION__ . ' url = ' . $url);
            $this->debugMP( 'msg', __FUNCTION__ . ' mcm_taxonomy_category_to_find = ' . $this->mcm_taxonomy_category_to_find . '; title = ' . $title );
            $this->debugMP( 'msg', __FUNCTION__ . ' original_title = ' . $original_title . ', prefix  = ' . $prefix );
            // $this->debugMP('pr',__FUNCTION__ . ' _SERVER = ', $_SERVER );
            // $this->debugMP('pr',__FUNCTION__ . ' wp_query->query_vars = ', $wp_query->query_vars );
            
            if ( $this->mcm_taxonomy_category_to_find !== false && $prefix == '' ) {
                // Set the category_base to use for the taxonomy rewrite rule
                $wp_mcm_category_base = $wp_mcm_options->get_value( 'wp_mcm_category_base' );
                $wp_mcm_category_base = ( empty($wp_mcm_category_base) ? WP_MCM_MEDIA_TAXONOMY : $wp_mcm_category_base );
                // Get media taxonomy to use
                $media_taxonomy_to_use = $this->mcm_get_media_taxonomy();
                if ( isset( $wp_query->query_vars['taxonomy'] ) ) {
                    $media_taxonomy_to_use = $wp_query->query_vars['taxonomy'];
                }
                $this->debugMP( 'msg', __FUNCTION__ . ' taxonomy = ' . $media_taxonomy_to_use );
                // get the arguments of the already-registered taxonomy
                $mcm_archive_title = $title;
                if ( isset( $wp_query->query_vars['term'] ) ) {
                    $mcm_archive_title = $wp_query->query_vars['term'];
                }
                $mcm_base_taxonomy = get_taxonomy( $media_taxonomy_to_use );
                // returns an object
                
                if ( $mcm_base_taxonomy ) {
                    $prefix = sprintf(
                        /* translators: %s: Taxonomy singular name. */
                        _x( '%s:', 'taxonomy term archive title prefix' ),
                        $mcm_base_taxonomy->labels->singular_name
                    );
                    $mcm_archive_title = sprintf(
                        /* translators: 1: Title prefix. 2: Title. */
                        _x( '%1$s %2$s', 'archive title' ),
                        $prefix,
                        '<span>' . $mcm_archive_title . '</span>'
                    );
                }
                
                $this->debugMP( 'msg', __FUNCTION__ . ' mcm_taxonomy_category_to_find = ' . $this->mcm_taxonomy_category_to_find . '; mcm_archive_title = ' . $mcm_archive_title );
                return $mcm_archive_title;
            }
            
            return $title;
        }
        
        /**
         * Fired when the plugin is activated.
         *
         * @since 2.1.0
         * @param    WP_Query    $query    The query object used to find objects like posts
         */
        function mcm_pre_get_posts( $query )
        {
            global  $wp_query ;
            global  $wp_mcm_options ;
            $this->debugMP( 'pr', __FUNCTION__ . ' is_search() = ' . is_search() . ', is_archive() = ' . is_archive() . ', query->query = ', $query->query );
            // $this->debugMP('pr',__FUNCTION__ . ' is_search() = ' . is_search() . ', is_archive() = ' . is_archive() . ', query = ', $query);
            // $this->debugMP('pr',__FUNCTION__ . ' is_search() = ' . is_search() . ', is_archive() = ' . is_archive() . ', wp_query = ', $wp_query);
            $this->mcm_taxonomy_category_to_find = false;
            // Check search_media_library for search when desired
            // if ( ! $wp_mcm_options->is_true('wp_mcm_search_media_library')) {
            // return;
            // }
            // Only perform search on non-admin pages
            // if (is_admin()) {
            // return;
            // }
            // Unset post_mime_type when it is limited to image only
            $media_post_mime_type = $query->get( 'post_mime_type', '__not_found' );
            
            if ( $media_post_mime_type == 'image' ) {
                $query->set( 'post_mime_type', '' );
                // $this->debugMP('pr',__FUNCTION__ . ' unset post_mime_type because media_post_mime_type = ' . $media_post_mime_type . ', query->query = ', $query->query );
                $this->debugMP( 'msg', __FUNCTION__ . ' unset post_mime_type because media_post_mime_type = ' . $media_post_mime_type );
            }
            
            // Check whether this is the main query
            
            if ( $query->is_main_query() ) {
                // Handle query if it is used for media is_archive
                
                if ( is_archive() ) {
                    // Get media taxonomy and categories to find
                    $media_taxonomy = $this->mcm_get_media_taxonomy();
                    $media_categories = $query->get( $media_taxonomy, '__not_found' );
                    // Check categories to find
                    
                    if ( $media_categories != '__not_found' ) {
                        $this->mcm_taxonomy_category_to_find = $media_categories;
                        $query->set( 'post_type', 'attachment' );
                        $query->set( 'post_status', 'inherit' );
                    } else {
                        // Add media for post categories when desired
                        
                        if ( is_category() && $wp_mcm_options->is_true( 'wp_mcm_use_post_taxonomy' ) ) {
                            $media_categories = $query->get( WP_MCM_POST_TAXONOMY, '__not_found' );
                            if ( $media_categories != '__not_found' ) {
                                $this->mcm_taxonomy_category_to_find = $media_categories;
                            }
                            $query->set( 'post_type', array( 'post', 'attachment' ) );
                            $query->set( 'post_status', array( 'publish', 'inherit' ) );
                        }
                    
                    }
                
                }
                
                // Add media for search only when desired
                
                if ( is_search() && $wp_mcm_options->is_true( 'wp_mcm_search_media_library' ) ) {
                    // Add attachment to post_type
                    $query_post_type = $query->get( 'post_type', '__not_found' );
                    if ( $query_post_type == '__not_found' ) {
                        $query_post_type = 'post';
                    }
                    $query_post_type = $this->mcm_query_vars_add_values( $query_post_type, 'attachment' );
                    $query->set( 'post_type', $query_post_type );
                    // Add inherit to post_status
                    $query_post_status = $query->get( 'post_status', '__not_found' );
                    if ( $query_post_status == '__not_found' ) {
                        $query_post_status = 'publish';
                    }
                    $query_post_status = $this->mcm_query_vars_add_values( $query_post_status, 'inherit' );
                    $query->set( 'post_status', $query_post_status );
                }
                
                $this->debugMP( 'pr', __FUNCTION__ . ' mcm_taxonomy_category_to_find = ' . $this->mcm_taxonomy_category_to_find . '; query->query = ', $query->query );
                // $this->debugMP('pr',__FUNCTION__ . ' mcm_taxonomy_category_to_find = ' . $this->mcm_taxonomy_category_to_find . '; query->query_vars = ', $query->query_vars );
            }
        
        }
        
        /**
         * Retrieve an attachment page link using an image or icon, if possible.
         *
         * @since 2.1.0
         * @return string HTML content.
         */
        public function mcm_wp_get_attachment_link_front(
            $html = '',
            $post = 0,
            $size = 'thumbnail',
            $permalink = false,
            $icon = false,
            $text = false
        )
        {
            global  $wp_mcm_plugin ;
            global  $wp_mcm_taxonomy ;
            $_post = get_post( $post );
            if ( empty($_post) ) {
                return $html;
            }
            $post_id = $_post->ID;
            $this->debugMP( 'msg', __FUNCTION__ . ' attachment[' . $post_id . '] size = ' . $size );
            $mcm_html = $html;
            // Check whether the html contains a href indication
            if ( false === strpos( $mcm_html, 'img' ) ) {
                $this->debugMP( 'msg', __FUNCTION__ . ' attachment[' . $post_id . '] does not contain IMG tag in mcm_html :' . $mcm_html );
            }
            // Check shortcode_attribute show_category_link
            $mcm_show_category_link = '';
            if ( isset( $wp_mcm_plugin->mcm_shortcode_attributes['show_category_link'] ) ) {
                $mcm_show_category_link = $wp_mcm_plugin->mcm_shortcode_attributes['show_category_link'];
            }
            
            if ( $mcm_show_category_link != '' ) {
                // Check $media_taxonomy
                $media_taxonomy = $wp_mcm_taxonomy->mcm_get_media_taxonomy();
                $terms = wp_get_object_terms( $post_id, $media_taxonomy );
                // Get the category_link for each taxonomy term found
                foreach ( $terms as $term ) {
                    // $this->debugMP('pr', __FUNCTION__ . ' attachment[' . $post_id . '][' . $media_taxonomy . '] term =', $term);
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
            }
            
            return $mcm_html;
        }
        
        /**
         * Check whether this search is for NO Category
         *
         * @since 2.0.0
         * @return void
         */
        function mcm_get_no_category_search()
        {
            $searchCategory = '';
            // Check for correct Filter situation
            
            if ( !isset( $_REQUEST['filter_action'] ) || empty($_REQUEST['filter_action']) ) {
                $this->debugMP( 'msg', __FUNCTION__ . ' Invalid request: No filter action. ' );
                return $searchCategory;
            }
            
            // Check parameters to use for new request
            
            if ( isset( $_REQUEST['bulk_tax_cat'] ) ) {
                $searchCategory = $_REQUEST['bulk_tax_cat'];
                // Get the request value to check for WP_MCM_OPTION_NO_CAT
                $searchCategoryRequest = '';
                
                if ( isset( $_REQUEST[$searchCategory] ) ) {
                    $searchCategoryRequest = $_REQUEST[$searchCategory];
                } else {
                    if ( $_REQUEST['bulk_tax_cat'] == WP_MCM_POST_TAXONOMY && isset( $_REQUEST['cat'] ) ) {
                        $searchCategoryRequest = $_REQUEST['cat'];
                    }
                }
                
                // Filter request on specific category so don't mess with it
                
                if ( $searchCategoryRequest == WP_MCM_OPTION_NO_CAT ) {
                    $this->debugMP( 'msg', __FUNCTION__ . ' Searching for NO Category for searchCategory: ' . $searchCategory );
                    return $searchCategory;
                }
            
            }
            
            return '';
        }
        
        /**
         * Add values to query vars to extend the query
         *
         * @since    2.0.0
         *
         * @param    array()    $new_query_vars
         */
        function mcm_query_vars_add_values( $query_vars = '', $values_to_add = '' )
        {
            // Make input into array
            $new_query_vars = $query_vars;
            $new_values_to_add = $values_to_add;
            if ( !is_array( $query_vars ) ) {
                $new_query_vars = array( $query_vars );
            }
            if ( !is_array( $values_to_add ) ) {
                $new_values_to_add = array( $values_to_add );
            }
            // Merge inputs to return
            $new_query_vars = array_merge( $new_query_vars, $new_values_to_add );
            return $new_query_vars;
        }
        
        /**
         * Register taxonomy for attachments
         *
         * @since 2.0.0
         * @return void
         */
        function mcm_register_media_taxonomy()
        {
            global  $wp_mcm_options ;
            // Get media taxonomy to use
            $media_taxonomy_to_use = $this->mcm_get_media_taxonomy();
            $this->debugMP( 'msg', __FUNCTION__ . ' taxonomy = ' . $media_taxonomy_to_use );
            // Set the category_base to use for the taxonomy rewrite rule
            $wp_mcm_category_base = $wp_mcm_options->get_value( 'wp_mcm_category_base' );
            $wp_mcm_category_base = ( empty($wp_mcm_category_base) ? WP_MCM_MEDIA_TAXONOMY : $wp_mcm_category_base );
            // Register WP_MCM_MEDIA_TAXONOMY
            $use_media_taxonomy = $media_taxonomy_to_use == WP_MCM_MEDIA_TAXONOMY;
            $args = array(
                'hierarchical'          => true,
                'show_ui'               => $use_media_taxonomy,
                'show_admin_column'     => $use_media_taxonomy,
                'public'                => $use_media_taxonomy,
                'show_in_nav_menus'     => $use_media_taxonomy,
                'publicly_queryable'    => true,
                'query_var'             => true,
                'rewrite'               => array(
                'slug' => $wp_mcm_category_base,
            ),
                'update_count_callback' => array( $this, 'mcm_update_count_callback' ),
                'labels'                => array(
                'name'              => __( 'MCM Categories', 'wp-media-category-management' ),
                'singular_name'     => __( 'MCM Category', 'wp-media-category-management' ),
                'menu_name'         => __( 'MCM Categories', 'wp-media-category-management' ),
                'all_items'         => __( 'All MCM Categories', 'wp-media-category-management' ),
                'edit_item'         => __( 'Edit MCM Category', 'wp-media-category-management' ),
                'view_item'         => __( 'View MCM Category', 'wp-media-category-management' ),
                'update_item'       => __( 'Update MCM Category', 'wp-media-category-management' ),
                'add_new_item'      => __( 'Add New MCM Category', 'wp-media-category-management' ),
                'new_item_name'     => __( 'New MCM Category Name', 'wp-media-category-management' ),
                'parent_item'       => __( 'Parent MCM Category', 'wp-media-category-management' ),
                'parent_item_colon' => __( 'Parent MCM Category:', 'wp-media-category-management' ),
                'search_items'      => __( 'Search MCM Categories', 'wp-media-category-management' ),
            ),
            );
            register_taxonomy( WP_MCM_MEDIA_TAXONOMY, array( 'attachment' ), $args );
            // Handle a taxonomy which may have been used previously by another plugin
            $wp_mcm_media_taxonomy_to_use = $this->mcm_get_media_taxonomy();
            
            if ( $wp_mcm_media_taxonomy_to_use != WP_MCM_MEDIA_TAXONOMY && $wp_mcm_media_taxonomy_to_use != WP_MCM_POST_TAXONOMY && !taxonomy_exists( $wp_mcm_media_taxonomy_to_use ) ) {
                // Create a nice name for the Custom MCM Taxonomy
                $wp_mcm_custom_taxonomy_name = $wp_mcm_options->get_value( 'wp_mcm_custom_taxonomy_name' );
                if ( $wp_mcm_custom_taxonomy_name == '' ) {
                    $wp_mcm_custom_taxonomy_name = __( 'Custom MCM Categories', 'wp-media-category-management' );
                }
                $wp_mcm_custom_taxonomy_name_single = $wp_mcm_options->get_value( 'wp_mcm_custom_taxonomy_name_single' );
                if ( $wp_mcm_custom_taxonomy_name_single == '' ) {
                    $wp_mcm_custom_taxonomy_name_single = __( 'Custom MCM Category', 'wp-media-category-management' );
                }
                // Register custom taxonomy to use
                $args = array(
                    'hierarchical'          => true,
                    'show_ui'               => true,
                    'show_admin_column'     => true,
                    'public'                => true,
                    'show_in_nav_menus'     => true,
                    'publicly_queryable'    => true,
                    'rewrite'               => false,
                    'update_count_callback' => array( $this, 'mcm_update_count_callback' ),
                    'labels'                => array(
                    'name'              => '(*) ' . $wp_mcm_custom_taxonomy_name,
                    'singular_name'     => $wp_mcm_custom_taxonomy_name_single,
                    'menu_name'         => $wp_mcm_custom_taxonomy_name,
                    'all_items'         => __( 'All', 'wp-media-category-management' ) . ' ' . $wp_mcm_custom_taxonomy_name,
                    'edit_item'         => __( 'Edit', 'wp-media-category-management' ) . ' ' . $wp_mcm_custom_taxonomy_name_single,
                    'view_item'         => __( 'View', 'wp-media-category-management' ) . ' ' . $wp_mcm_custom_taxonomy_name_single,
                    'update_item'       => __( 'Update', 'wp-media-category-management' ) . ' ' . $wp_mcm_custom_taxonomy_name_single,
                    'add_new_item'      => __( 'Add New', 'wp-media-category-management' ) . ' ' . $wp_mcm_custom_taxonomy_name_single,
                    'new_item_name'     => sprintf( __( 'New %s Name', 'wp-media-category-management' ), $wp_mcm_custom_taxonomy_name_single ),
                    'parent_item'       => __( 'Parent', 'wp-media-category-management' ) . ' ' . $wp_mcm_custom_taxonomy_name_single,
                    'parent_item_colon' => __( 'Parent', 'wp-media-category-management' ) . ' ' . $wp_mcm_custom_taxonomy_name_single . ':',
                    'search_items'      => __( 'Search', 'wp-media-category-management' ) . ' ' . $wp_mcm_custom_taxonomy_name,
                ),
                );
                register_taxonomy( $wp_mcm_media_taxonomy_to_use, array( 'attachment' ), $args );
            }
            
            // Register WP_MCM_POST_TAXONOMY for attachments only if explicitly desired
            
            if ( $wp_mcm_options->is_true( 'wp_mcm_use_post_taxonomy' ) || $wp_mcm_media_taxonomy_to_use == WP_MCM_POST_TAXONOMY ) {
                $this->mcm_set_media_taxonomy_settings();
                register_taxonomy_for_object_type( WP_MCM_POST_TAXONOMY, 'attachment' );
            }
            
            // Register WP_MCM_TAGS_TAXONOMY for attachments only if explicitly desired
            if ( $wp_mcm_media_taxonomy_to_use == WP_MCM_TAGS_TAXONOMY ) {
                //$this->mcm_set_media_taxonomy_settings();
                register_taxonomy_for_object_type( WP_MCM_TAGS_TAXONOMY, 'attachment' );
            }
            // Flush rewrite when necessary, e.g. when the definition of post_types changed TODO
            // add_rewrite_rule( $wp_mcm_category_base .'/([^/]+)/?$', 'index.php?' . WP_MCM_MEDIA_TAXONOMY_QUERY . '=' . $wp_mcm_category_base . '&' . $media_taxonomy_to_use . '=$matches[1]', 'top' );
            // flush_rewrite_rules();    // TODO
            // $this->debugMP('msg',__FUNCTION__ . ' flush_rewrite_rules for wp_mcm_category_base = ' . $wp_mcm_category_base . ', media_taxonomy_to_use = ' . $media_taxonomy_to_use );
        }
        
        /**
         * Custom update_count_callback
         *
         * @since 2.0.0
         * @return void
         */
        function mcm_update_count_callback( $terms = array(), $media_taxonomy = 'category' )
        {
            global  $wpdb ;
            // Get media taxonomy
            $media_taxonomy = $this->mcm_get_media_taxonomy();
            $this->debugMP( 'msg', __FUNCTION__ . ' taxonomy = ' . $media_taxonomy );
            // select id & count from taxonomy
            $query = "SELECT term_taxonomy_id, MAX(total) AS total FROM ((\r\n\t\t\tSELECT tt.term_taxonomy_id, COUNT(*) AS total FROM {$wpdb->term_relationships} tr, {$wpdb->term_taxonomy} tt WHERE tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = %s GROUP BY tt.term_taxonomy_id\r\n\t\t\t) UNION ALL (\r\n\t\t\tSELECT term_taxonomy_id, 0 AS total FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s\r\n\t\t\t)) AS unioncount GROUP BY term_taxonomy_id";
            $rsCount = $wpdb->get_results( $wpdb->prepare( $query, $media_taxonomy, $media_taxonomy ) );
            // $this->debugMP('msg',__FUNCTION__ . ' query = ' . $query);
            // $this->debugMP('pr',__FUNCTION__ . ' rsCount = ', $rsCount);
            // update all count values from taxonomy
            foreach ( $rsCount as $rowCount ) {
                $wpdb->update( $wpdb->term_taxonomy, array(
                    'count' => $rowCount->total,
                ), array(
                    'term_taxonomy_id' => $rowCount->term_taxonomy_id,
                ) );
            }
        }
        
        /**
         * Change the settings for category taxonomy depending on taxonomy choosen
         *
         * @since 2.0.0
         * @return void
         */
        function mcm_set_media_taxonomy_settings()
        {
            global  $wp_mcm_options ;
            // Get the post_ID and the corresponding post_type
            
            if ( isset( $_GET['post'] ) ) {
                $post_id = $post_ID = (int) $_GET['post'];
            } elseif ( isset( $_POST['post_ID'] ) ) {
                $post_id = $post_ID = (int) $_POST['post_ID'];
            } else {
                $post_id = $post_ID = 0;
            }
            
            $post_type = get_post_type( $post_id );
            $this->debugMP( 'msg', __FUNCTION__ . ' post_type = ' . $post_type );
            // Only limit post taxonomy for attachments
            
            if ( $post_type == 'attachment' || $post_id == 0 ) {
                // get the arguments of the already-registered taxonomy
                $category_args = get_taxonomy( WP_MCM_POST_TAXONOMY );
                // returns an object
                // make changes to the args
                // again, note that it's an object
                $wp_mcm_media_taxonomy_to_use = $this->mcm_get_media_taxonomy();
                $use_post_taxonomy = $wp_mcm_options->is_true( 'wp_mcm_use_post_taxonomy' ) || $wp_mcm_media_taxonomy_to_use == WP_MCM_POST_TAXONOMY;
                $category_args->show_ui = $use_post_taxonomy;
                $category_args->show_admin_column = $use_post_taxonomy;
                // re-register the taxonomy
                register_taxonomy( WP_MCM_POST_TAXONOMY, 'post', (array) $category_args );
            }
        
        }
        
        /**
         * Get the media taxonomy choosen
         *
         * @since 2.0.0
         * @return void
         */
        function mcm_get_media_taxonomy()
        {
            global  $wp_mcm_options ;
            return $wp_mcm_options->get_value( 'wp_mcm_media_taxonomy_to_use' );
        }
        
        /**
         * Get the media taxonomy choosen
         *
         * @since 2.0.0
         * @return void
         */
        function mcm_get_default_media_category()
        {
            global  $wp_mcm_options ;
            return $wp_mcm_options->get_value( 'wp_mcm_default_media_category' );
        }
        
        /**
         * Get the media taxonomies for the taxonomy choosen
         *
         * @since 2.0.0
         * @return void
         */
        function mcm_get_media_taxonomies()
        {
            global  $wpdb ;
            $query = "SELECT taxonomy FROM {$wpdb->term_taxonomy} ";
            $query .= " GROUP BY taxonomy";
            $taxonomiesFound = $wpdb->get_results( $query, 'ARRAY_A' );
            $mediaTaxonomiesFound = get_taxonomies( array(
                'object_type' => array( 'attachment' ),
            ), 'names' );
            // Merge both lists found
            foreach ( $taxonomiesFound as $taxonomyObject ) {
                $mediaTaxonomiesFound[$taxonomyObject['taxonomy']] = $taxonomyObject['taxonomy'];
            }
            // $this->debugMP('pr',__FUNCTION__  . ' query = ' . $query . ', mediaTaxonomiesFound = ', $mediaTaxonomiesFound);
            // Create an element for each taxonomy found
            $mediaTaxonomies = array();
            foreach ( $mediaTaxonomiesFound as $taxonomyObject ) {
                $taxonomySlug = $taxonomyObject;
                //$this->debugMP('pr',__FUNCTION__  . ' taxonomySlug found:' . $taxonomySlug . ', taxonomyObject found:', $taxonomyObject);
                // Count the objects belonging to these terms
                $countMediaPosts = $this->mcm_get_count_for_media_taxonomy( $taxonomySlug );
                // $this->debugMP('msg',__FUNCTION__  . ' taxonomySlug ' . $taxonomySlug . ' found countMediaPosts = ' . $countMediaPosts . ' !' );
                // Get the taxonomy information
                $mediaTaxonomy = get_taxonomy( $taxonomySlug );
                // $this->debugMP('pr',__FUNCTION__  . ' taxonomySlug ' . $taxonomySlug . ', mediaTaxonomy found:', $mediaTaxonomy);
                $mediaTaxonomyData = array();
                
                if ( $mediaTaxonomy ) {
                    $mediaTaxonomyData['object'] = $mediaTaxonomy;
                    $mediaTaxonomyData['name'] = $mediaTaxonomy->name;
                    $mediaTaxonomyData['label'] = ( empty($mediaTaxonomy->label) ? $mediaTaxonomy->name : $mediaTaxonomy->label );
                    $mediaTaxonomyData['label'] .= ' [#' . $countMediaPosts . ']';
                    // Add indication to label
                    switch ( $taxonomySlug ) {
                        case WP_MCM_MEDIA_TAXONOMY:
                            break;
                        case WP_MCM_POST_TAXONOMY:
                            $mediaTaxonomyData['label'] = '(P) ' . $mediaTaxonomyData['label'];
                            break;
                        case WP_MCM_TAGS_TAXONOMY:
                            $mediaTaxonomyData['label'] = '(T) ' . $mediaTaxonomyData['label'];
                            break;
                        default:
                            
                            if ( is_object_in_taxonomy( 'post', $taxonomySlug ) ) {
                                $mediaTaxonomyData['label'] = '(P.) ' . $mediaTaxonomyData['label'];
                            } else {
                                $mediaTaxonomyData['label'] = '(.) ' . $mediaTaxonomyData['label'];
                            }
                            
                            break;
                    }
                } else {
                    $mediaTaxonomyData['object'] = false;
                    $mediaTaxonomyData['name'] = $taxonomySlug;
                    $mediaTaxonomyData['label'] = '(*) ' . $taxonomySlug . ' [#' . $countMediaPosts . ']';
                }
                
                // Only add taxonomy when either attachments found OR it is for attachments
                //$this->debugMP('msg',__FUNCTION__  . ' taxonomySlug: ' . $taxonomySlug . ', tested for attachment with is_object_in_taxonomy found:' . is_object_in_taxonomy('attachment', $taxonomySlug));
                if ( $countMediaPosts > 0 || is_object_in_taxonomy( array( 'post', 'attachment' ), $taxonomySlug ) ) {
                    $mediaTaxonomies[$taxonomySlug] = $mediaTaxonomyData;
                }
            }
            //$this->debugMP('pr',__FUNCTION__  . ' mediaTaxonomies found:', $mediaTaxonomies);
            return $mediaTaxonomies;
        }
        
        /**
         * Find the media that have media_categories assigned
         *
         * @since 2.0.0
         * @return void
         */
        function mcm_get_attachment_ids_no_category( $mcm_atts = array() )
        {
            //	return '4';
            // Get media taxonomy and use default category value
            $media_taxonomy = $this->mcm_get_media_taxonomy();
            if ( isset( $mcm_atts['taxonomy'] ) && $mcm_atts['taxonomy'] != '' ) {
                $media_taxonomy = $mcm_atts['taxonomy'];
            }
            // Get the posts associated with the media_taxonomy
            $attachments_args = array(
                'showposts'   => -1,
                'post_type'   => 'attachment',
                'post_parent' => null,
            );
            // Find all posts for the current media_taxonomy to use for filtering them out
            $all_attachments = $this->mcm_get_posts_for_media_taxonomy( $media_taxonomy );
            $post_not_in = array();
            foreach ( $all_attachments as $key => $val ) {
                $post_not_in[] = $val->ID;
            }
            $attachments_args['post__not_in'] = $post_not_in;
            // $this->debugMP('pr', __FUNCTION__ . ' post_not_in = ', $post_not_in);
            // Reset the search query parameters to search for all attachments
            $attachments_args[$media_taxonomy] = 0;
            // Use gallery options if available
            if ( isset( $mcm_atts['orderby'] ) && $mcm_atts['orderby'] != '' ) {
                $attachments_args['orderby'] = $mcm_atts['orderby'];
            }
            if ( isset( $mcm_atts['order'] ) && $mcm_atts['order'] != '' ) {
                $attachments_args['order'] = $mcm_atts['order'];
            }
            // Get the attachments for these arguments
            $attachments = get_posts( $attachments_args );
            $this->debugMP( 'pr', __FUNCTION__ . ' attachments found = ' . count( $attachments ) . ' with attachments_args = ', $attachments_args );
            // Get the post IDs for the attachments found for POST
            $attachment_ids = array();
            
            if ( $attachments ) {
                foreach ( $attachments as $post ) {
                    setup_postdata( $post );
                    $attachment_ids[] = $post->ID;
                }
                wp_reset_postdata();
            }
            
            $attachment_ids_result = implode( ',', $attachment_ids );
            // $this->debugMP('pr',__FUNCTION__ . ' attachment_ids_result = ' . $attachment_ids_result . ' attachment_ids = ', $attachment_ids);
            return $attachment_ids_result;
        }
        
        /**
         * Find the media that have media_categories assigned
         *
         * @since 2.0.0
         * @return void
         */
        function mcm_get_attachment_ids( $mcm_atts = array() )
        {
            global  $wp_mcm_options ;
            // Get media category and default
            $media_categories = $wp_mcm_options->get_value( 'wp_mcm_default_media_category' );
            if ( isset( $mcm_atts['category'] ) ) {
                
                if ( $mcm_atts['category'] != '' ) {
                    $media_categories = explode( ',', $mcm_atts['category'] );
                } else {
                    $media_categories = WP_MCM_OPTION_ALL_CAT;
                }
            
            }
            if ( !is_array( $media_categories ) ) {
                $media_categories = array( $media_categories );
            }
            // $this->debugMP('pr',__FUNCTION__ . ' wp_mcm_default_media_category = ' , $media_categories);
            // Check to find the media that have no category assigned
            
            if ( $media_categories[0] == WP_MCM_OPTION_NONE ) {
                $attachment_ids_result = $this->mcm_get_attachment_ids_no_category( $mcm_atts );
                // $this->debugMP('pr',__FUNCTION__ . ' attachment_ids_result for WP_MCM_OPTION_NONE = ' . $attachment_ids_result );
                return $attachment_ids_result;
            }
            
            // Get media taxonomy and use default category value
            $media_taxonomy = $this->mcm_get_media_taxonomy();
            if ( isset( $mcm_atts['taxonomy'] ) && $mcm_atts['taxonomy'] != '' ) {
                $media_taxonomy = $mcm_atts['taxonomy'];
            }
            // Get the posts associated with the media_taxonomy
            $attachments_args = array(
                'showposts'   => -1,
                'post_type'   => 'attachment',
                'post_parent' => null,
            );
            // Check to find all the media or only for selected categories
            if ( $media_categories[0] !== WP_MCM_OPTION_ALL_CAT ) {
                $attachments_args['tax_query'] = array( array(
                    'taxonomy' => $media_taxonomy,
                    'field'    => 'slug',
                    'terms'    => $media_categories,
                ) );
            }
            // $this->debugMP('pr',__FUNCTION__ . ' taxonomy = ' . $media_taxonomy . ' categories = ', $media_categories);
            // Use gallery options if available
            if ( isset( $mcm_atts['orderby'] ) && $mcm_atts['orderby'] != '' ) {
                $attachments_args['orderby'] = $mcm_atts['orderby'];
            }
            if ( isset( $mcm_atts['order'] ) && $mcm_atts['order'] != '' ) {
                $attachments_args['order'] = $mcm_atts['order'];
            }
            // Get the attachments for these arguments
            $attachments = get_posts( $attachments_args );
            $this->debugMP( 'pr', __FUNCTION__ . ' attachments found = ' . count( $attachments ) . ' with attachments_args = ', $attachments_args );
            // Get the post IDs for the attachments found for POST
            $attachment_ids = array();
            
            if ( $attachments ) {
                foreach ( $attachments as $post ) {
                    setup_postdata( $post );
                    $attachment_ids[] = $post->ID;
                }
                wp_reset_postdata();
            }
            
            $attachment_ids_result = implode( ',', $attachment_ids );
            // $this->debugMP('pr',__FUNCTION__ . ' attachment_ids_result = ' . $attachment_ids_result . ' attachment_ids = ', $attachment_ids);
            return $attachment_ids_result;
        }
        
        /**
         * Get the number of media for the requested media_taxonomy
         *
         * @since 2.0.0
         * @return void
         */
        function mcm_get_count_for_media_taxonomy( $media_taxonomy = '', $user_id = '' )
        {
            global  $wpdb ;
            // Validate input
            if ( $media_taxonomy == '' ) {
                return 0;
            }
            // Get the terms for this taxonomy
            $query = "SELECT * FROM {$wpdb->term_taxonomy} AS tt ";
            $query .= " WHERE tt.taxonomy = '{$media_taxonomy}' ";
            $taxonomyTerms = $wpdb->get_results( $query );
            // $this->debugMP('pr',__FUNCTION__ . ' media_taxonomy ' . $media_taxonomy . ' found ' . count($taxonomyTerms) . ' terms with query = ' . $query, $taxonomyTerms);
            // Validate $taxonomyTerms found
            if ( is_wp_error( $taxonomyTerms ) || count( $taxonomyTerms ) == 0 ) {
                return 0;
            }
            // Create a list of taxonomyTermIDs to be used for the query
            $taxonomyTermIDs = array();
            foreach ( $taxonomyTerms as $term ) {
                $taxonomyTermIDs[] = $term->term_taxonomy_id;
            }
            $taxonomyTermIDs = implode( ',', $taxonomyTermIDs );
            $query = "SELECT COUNT(*) AS total FROM {$wpdb->posts} ";
            $query .= " INNER JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id) ";
            $query .= " WHERE 1=1 ";
            $query .= "   AND {$wpdb->posts}.post_type = 'attachment' ";
            $query .= "   AND ({$wpdb->term_relationships}.term_taxonomy_id IN ({$taxonomyTermIDs})) ";
            // $query .= " GROUP BY $wpdb->posts.ID";
            $taxonomyPosts = $wpdb->get_results( $query );
            // $this->debugMP('pr',__FUNCTION__ . ' media_taxonomy ' . $media_taxonomy . ' found ' . count($taxonomyPosts) . ' posts with query = ' . $query, $taxonomyPosts);
            // Validate $taxonomyPosts found
            if ( is_wp_error( $taxonomyPosts ) || count( $taxonomyPosts ) == 0 ) {
                return 0;
            }
            return $taxonomyPosts[0]->total;
        }
        
        /**
         * Get the posts for the requested media_taxonomy
         *
         * @since 2.0.0
         * @return void
         */
        function mcm_get_posts_for_media_taxonomy( $taxonomy = '', $user_id = '' )
        {
            global  $wpdb ;
            // Validate input
            if ( $taxonomy == '' ) {
                return array();
            }
            // Get the terms for this taxonomy
            $query = "SELECT * FROM {$wpdb->term_taxonomy} AS tt ";
            $query .= " WHERE tt.taxonomy = '{$taxonomy}' ";
            $taxonomyTerms = $wpdb->get_results( $query );
            //$this->debugMP('pr',__FUNCTION__ . ' taxonomy found ' . count($taxonomyTerms) . ' with query = ' . $query, $taxonomyTerms);
            // Validate $taxonomyTerms found
            if ( is_wp_error( $taxonomyTerms ) || count( $taxonomyTerms ) == 0 ) {
                return array();
            }
            // Create a list of taxonomyTermIDs to be used for the query
            $taxonomyTermIDs = array();
            foreach ( $taxonomyTerms as $term ) {
                $taxonomyTermIDs[] = $term->term_taxonomy_id;
            }
            $taxonomyTermIDs = implode( ',', $taxonomyTermIDs );
            $query = "SELECT {$wpdb->posts}.* FROM {$wpdb->posts} ";
            $query .= " INNER JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id) ";
            $query .= " WHERE 1=1 ";
            $query .= "   AND {$wpdb->posts}.post_type = 'attachment' ";
            $query .= "   AND ({$wpdb->term_relationships}.term_taxonomy_id IN ({$taxonomyTermIDs})) ";
            $query .= " GROUP BY {$wpdb->posts}.ID";
            $taxonomyPosts = $wpdb->get_results( $query );
            $this->debugMP( 'msg', __FUNCTION__ . ' taxonomy found ' . count( $taxonomyPosts ) . ' with query = ', $query );
            return $taxonomyPosts;
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