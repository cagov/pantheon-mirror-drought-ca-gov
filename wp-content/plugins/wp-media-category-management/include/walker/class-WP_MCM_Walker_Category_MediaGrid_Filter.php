<?php
/**
 * WP Media Category Management WP_MCM_Walker_Category_MediaGrid_Filter class for wp_dropdown_categories, based on https://gist.github.com/stephenh1988/2902509
 * 
 * @since  2.0.0
 * @author DeBAAT
 */

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WP_MCM_Walker_Category_MediaGrid_Filter' ) ) {

	class WP_MCM_Walker_Category_MediaGrid_Filter extends Walker_CategoryDropdown {

		function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
			$pad = str_repeat( '&nbsp;', $depth * 3 );
			$cat_name = apply_filters( 'list_cats', $category->name, $category );

			// {"term_id":"1","term_name":"no category"}
			$output .= ',{"term_id":"' . $category->term_id . '",';

			$output .= '"term_name":"' . $pad . esc_attr( $cat_name );
			if ( ( isset( $args['show_count'] ) ) && ( $args['show_count'] ) ) {
				$output .= '&nbsp;&nbsp;('. $category->count .')';
			}
			$output .= '"}';
		}

	}

}
