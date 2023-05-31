<?php
/**
 * WP Media Category Management Media_Admin class
 * 
 * @since  2.0.0
 * @author DeBAAT
 */

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WP_MCM_Media_Admin' ) ) {

	class WP_MCM_Media_Admin {

		/**
		 * Class constructor
		 */
		function __construct() {

			$this->includes();
			$this->add_hooks_and_filters();
		}

		/**
		 * Include the required files.
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function includes() {

		}

		/**
		 * Add cross-element hooks & filters.
		 *
		 * Haven't yet moved all items to the AJAX and UI classes.
		 */
		function add_hooks_and_filters() {
			// $this->debugMP('msg', __FUNCTION__ . ' started.');

			// Some filters and action to process categories
			add_action( 'restrict_manage_posts',                    array( $this, 'mcm_restrict_manage_posts'        )       );

			add_action( 'admin_enqueue_scripts',                    array( $this, 'mcm_admin_enqueue_scripts'        )       );

			add_action( 'add_attachment',                           array( $this, 'mcm_set_attachment_category'      )       );
			add_action( 'edit_attachment',                          array( $this, 'mcm_set_attachment_category'      )       );

			add_filter( 'ajax_query_attachments_args',              array( $this, 'mcm_ajax_query_attachments_args'  )       );

		}

		/**
		 * Enqueue the media-category-management scripts to filter categories
		 *
		 * @since 2.0.0
		 * @return void
		 */
		function mcm_admin_enqueue_scripts() {
			global $pagenow;
			global $wp_mcm_taxonomy;
			global $wp_mcm_options;
			global $wp_mcm_walker_category_mediagrid_filter;
			$this->debugMP('msg',__FUNCTION__ . ' pagenow = ' . $pagenow . ', wp_script_is( media-editor ) = ' . wp_script_is( 'media-editor' ));

			// Get media taxonomy
			$media_taxonomy = $wp_mcm_taxonomy->mcm_get_media_taxonomy();
			$this->debugMP('msg',__FUNCTION__ . ' taxonomy = ' . $media_taxonomy);

			// Only show_count when no Post or Tag taxonomy
			if (( $media_taxonomy == WP_MCM_POST_TAXONOMY ) || ( $media_taxonomy == WP_MCM_TAGS_TAXONOMY )) {
				$show_count = false;
			} else {
				$show_count = true;
			}
			$dropdown_options = array(
				'taxonomy'        => $media_taxonomy,
				'hide_empty'      => false,
				'hierarchical'    => true,
				'orderby'         => 'name',
				'show_count'      => $show_count,
				'walker'          => $wp_mcm_walker_category_mediagrid_filter,
				'value'           => 'id',
				'echo'            => false
			);
			$attachment_terms_list   = wp_dropdown_categories( $dropdown_options );
			$attachment_terms_string = preg_replace( array( "/<select([^>]*)>/", "/<\/select>/" ), "", $attachment_terms_list );

			// Add an attachment_terms_list for All and No category
			$mcm_label               = $this->mcm_get_media_category_label($media_taxonomy);
			$mcm_label_all           = __( 'Show all', 'wp-media-category-management' ) . ' ' . $mcm_label;
			$mcm_label_none          = __( 'No',       'wp-media-category-management' ) . ' ' . $mcm_label;
			$no_category_term        = ' ,{"term_id":"' . WP_MCM_OPTION_NO_CAT . '","term_name":"' . $mcm_label_none . '"}';
			$attachment_terms_string = $no_category_term . substr( $attachment_terms_string, 1 );
			$this->debugMP('msg',__FUNCTION__ . ' attachment_terms_string = !' . $attachment_terms_string . '!');
			// $this->debugMP('pr',__FUNCTION__ . ' attachment_terms_list = !', $attachment_terms_list );

			// Enqueue the media scripts always, not only on post pages.
			if ( ( ('upload.php' == $pagenow ) || ('post.php' == $pagenow ) || ('post-new.php' == $pagenow ) )  && ($wp_mcm_options->is_true('wp_mcm_use_gutenberg_filter')) ) {

				$attachment_terms_list = get_terms( $media_taxonomy, $dropdown_options );
				// $this->debugMP('pr',__FUNCTION__ . ' attachment_terms_list = !', $attachment_terms_list );

				// create my own version codes
				wp_enqueue_script( 'mcm-media-views', WP_MCM_PLUGIN_URL . '/js/wp-mcm-media-views-post.js', array( 'media-views' ), WP_MCM_VERSION_NUM, false );

				wp_localize_script(
					'mcm-media-views',
					'wpmcm_admin_js',
					array(
						'ajax_url'       => admin_url( 'admin-ajax.php' ),
						'spinner_url'    => includes_url() . '/images/spinner.gif',
						'mcm_taxonomy'   => $media_taxonomy,
						'mcm_label'      => $mcm_label,
						'mcm_label_all'  => $mcm_label_all,
						'mcm_label_none' => $mcm_label_none,
						'mcm_terms'      => $attachment_terms_list,
					)
				);

			} else {

				echo '<script type="text/javascript">';
				echo '/* <![CDATA[ */';
				echo 'var mcm_taxonomies = {"' . $media_taxonomy . '":';
				echo     '{"list_title":"' . html_entity_decode( $mcm_label_all, ENT_QUOTES, 'UTF-8' ) . '",';
				echo       '"term_list":[' . substr( $attachment_terms_string, 2 ) . ']}};';
				echo '/* ]]> */';
				echo '</script>';

				wp_enqueue_script( 'mcm-media-views', WP_MCM_PLUGIN_URL . '/js/wp-mcm-media-views.js', array( 'media-views' ), WP_MCM_VERSION_NUM, true );
			}
		}

		/**
		 * Add a category filter
		 *
		 * @since 2.0.0
		 * @return void
		 */
		function mcm_add_category_filter( $media_taxonomy = '') {

			// Validate input
			if ($media_taxonomy == '') {
				return;
			}

			global $pagenow;
			if ( 'upload.php' == $pagenow ) {

				// Set options depending on type of taxonomy chosen
				switch ($media_taxonomy) {
					case WP_MCM_POST_TAXONOMY:
						$selected_value = isset( $_GET['cat'] ) ? $_GET['cat'] : '';
						break;
					default:
						$selected_value = isset( $_GET[$media_taxonomy] ) ? $_GET[$media_taxonomy] : '';
						break;
				}

				echo "<label for='{$media_taxonomy}' class='screen-reader-text'>" . __('Filter by', 'wp-media-category-management') . " {$media_taxonomy}</label>";

				$dropdown_options = $this->mcm_get_media_category_options($media_taxonomy, $selected_value);
				wp_dropdown_categories( $dropdown_options );
			}
		}

		/**
		 * Get the label to show in the list of media_category
		 *
		 * @since 2.0.0
		 * @return void
		 */
		function mcm_get_media_category_label( $media_taxonomy = '' ) {

			switch ($media_taxonomy) {
				case WP_MCM_TAGS_TAXONOMY:
					return __( 'tags', 'wp-media-category-management' );
					break;
				case WP_MCM_POST_TAXONOMY:
					return __( 'Post categories', 'wp-media-category-management' );
					break;
				default:
					return __( 'MCM categories', 'wp-media-category-management' );
					break;
			}
		}

		/**
		 * Get the options to determine the list of media_category
		 *
		 * @since 2.0.0
		 * @return void
		 */
		function mcm_get_media_category_options( $media_taxonomy = '', $selected_value = '') {
			global $wp_mcm_walker_category_filter;

			// Set options depending on type of taxonomy chosen
			$dropdown_options = array(
				'taxonomy'           => $media_taxonomy,
				'option_none_value'  => WP_MCM_OPTION_NO_CAT,
				'selected'           => $selected_value,
				'hide_empty'         => false,
				'hierarchical'       => true,
				'orderby'            => 'name',
				'walker'             => $wp_mcm_walker_category_filter,
			);

			// Get some labels
			$mcm_label     = $this->mcm_get_media_category_label($media_taxonomy);
			$mcm_label_all = __( 'View all', 'wp-media-category-management' ) . ' ' . $mcm_label;
			$mcm_label_no  = __( 'No',       'wp-media-category-management' ) . ' ' . $mcm_label;

			switch ($media_taxonomy) {
				case WP_MCM_TAGS_TAXONOMY:
					$dropdown_options_extra = array(
						'name'               => $media_taxonomy,
						'show_option_all'    => $mcm_label_all,
						'show_option_none'   => $mcm_label_no,
						'show_count'         => false,
						'value'              => 'slug'
					);
					break;
				case WP_MCM_POST_TAXONOMY:
					$dropdown_options_extra = array(
						'show_option_all'    => $mcm_label_all,
						'show_option_none'   => $mcm_label_no,
						'show_count'         => false,
						'value'              => 'id'
					);
					break;
				default:
					$dropdown_options_extra = array(
						'name'               => $media_taxonomy,
						'show_option_all'    => $mcm_label_all,
						'show_option_none'   => $mcm_label_no,
						'show_count'         => true,
						'value'              => 'slug'
					);
					break;
			}
			$this->debugMP('pr',__FUNCTION__ . ' selected_value = ' . $selected_value . ', dropdown_options', array_merge($dropdown_options, $dropdown_options_extra));
			return array_merge($dropdown_options, $dropdown_options_extra);
		}

		/**
		 * Add a filter for restrict_manage_posts to add a filter for categories and process the toggle actions
		 *
		 * @since 2.0.0
		 * @return void
		 */
		function mcm_restrict_manage_posts() {
			global $wp_mcm_taxonomy;
			global $wp_mcm_options;

			// Get media taxonomy
			$media_taxonomy = $wp_mcm_taxonomy->mcm_get_media_taxonomy();
			$this->debugMP('msg',__FUNCTION__ . ' taxonomy = ' . $media_taxonomy);

			// Add a filter for the WP_MCM_POST_TAXONOMY
			if (($media_taxonomy != WP_MCM_POST_TAXONOMY) && ($wp_mcm_options->is_true('wp_mcm_use_post_taxonomy'))) {
				$this->mcm_add_category_filter( WP_MCM_POST_TAXONOMY );
			}

			// Add a filter for the selected category
			$this->mcm_add_category_filter( $media_taxonomy );

		}

		/**
		 * Handle default category of attachments without category
		 *
		 * @since 2.0.0
		 *
		 * @return void
		 */
		function mcm_set_attachment_category( $post_ID ) {
			global $wp_mcm_options;
			global $wp_mcm_taxonomy;

			// Check whether this user can edit this post
			if ( !current_user_can( 'edit_post', $post_ID ) ) {
				return;
			}

			// Check whether to use the default or not
			if ( ! $wp_mcm_options->is_true( 'wp_mcm_use_default_category' )) {
				return;
			}

			// Check $media_taxonomy
			$media_taxonomy = $wp_mcm_taxonomy->mcm_get_media_taxonomy();

			// Only add default if attachment doesn't have WP_MCM_MEDIA_TAXONOMY categories
			if ( ! wp_get_object_terms( $post_ID, $media_taxonomy ) ) {

				// Get the default value
				$default_category = $wp_mcm_options->get_value('wp_mcm_default_media_category');

				// Check for valid $default_category
				if ($default_category != WP_MCM_OPTION_NONE) {

					// Not set so add the $media_category taxonomy to this media post
					$add_result = wp_set_object_terms($post_ID, $default_category, $media_taxonomy, true);

					// Check for error
					if ( is_wp_error( $add_result ) ) {
						return $add_result;
					}
				}

			}

		}

		/**
		 *  Get an array of term values, which type is determined by the parameter
		 *
		 *  @since    2.0.0
		 */
		function mcm_get_terms_values( $keys = 'ids') { 
			global $wp_mcm_taxonomy;

			// Get media taxonomy
			$media_taxonomy = $wp_mcm_taxonomy->mcm_get_media_taxonomy();
			$this->debugMP('msg',__FUNCTION__ . ' media_taxonomy = ' . $media_taxonomy);

			$media_terms = get_terms($media_taxonomy, array(
				'hide_empty'       => 0,
				'fields'           => 'id=>slug',
				));
			// $this->debugMP('pr', __FUNCTION__ . ' media_terms for :' . $media_taxonomy, $media_terms);

			$media_values = array();
			foreach ($media_terms as $key => $value) {
				if ($keys == 'ids') {
					$media_values[] = $key;
				} else {
					$media_values[] = $value;
				}
			}
			return $media_values;

		}

		/**
		 * Changing categories in the 'grid view'
		 *
		 * @since 2.0.0
		 *
		 * @action ajax_query_attachments_args
		 * @param array $query
		 */
		function mcm_ajax_query_attachments_args( $query = array() ) {
			// $this->debugMP('pr', __FUNCTION__ . ' Started with query ARGS: ', $query );

			// grab original query, the given query has already been filtered by WordPress
			$taxquery = isset( $_REQUEST['query'] ) ? (array) $_REQUEST['query'] : array();

			$taxonomies = get_object_taxonomies( 'attachment', 'names' );
			// $this->debugMP('pr', __FUNCTION__ . ' Continued with taxonomies: ', $taxonomies );
			// $this->debugMP('pr', __FUNCTION__ . ' Continued with _REQUEST: ', $_REQUEST );

			$taxquery = array_intersect_key( $taxquery, array_flip( $taxonomies ) );

			// merge our query into the WordPress query
			$query = array_merge( $query, $taxquery );

			$query['tax_query'] = array( 'relation' => 'AND' );

			foreach ( $taxonomies as $taxonomy ) {
				if ( isset( $query[$taxonomy] ) ) {
					// Filter a specific category
					if ( is_numeric( $query[$taxonomy] ) ) {
						array_push( $query['tax_query'], array(
							'taxonomy' => $taxonomy,
							'field'    => 'id',
							'terms'    => $query[$taxonomy]
						));	
					}
					// Filter No category
					if ( $query[$taxonomy] == WP_MCM_OPTION_NO_CAT ) {
						$all_terms_ids = $this->mcm_get_terms_values('ids');
						array_push( $query['tax_query'], array(
							'taxonomy' => $taxonomy,
							'field'    => 'id',
							'terms'    => $all_terms_ids,
							'operator' => 'NOT IN',
						));	
					}
				}
				unset ( $query[$taxonomy] );
			}
			// $this->debugMP('pr', __FUNCTION__ . ' Continued with query ARGS: ', $query );

			return $query;
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
