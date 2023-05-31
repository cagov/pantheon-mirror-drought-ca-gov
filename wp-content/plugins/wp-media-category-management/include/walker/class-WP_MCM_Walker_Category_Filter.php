<?php
/**
 * WP Media Category Management Walker_Category_Filter class for wp_dropdown_categories, based on https://gist.github.com/stephenh1988/2902509
 * 
 * @since  2.0.0
 * @author DeBAAT
 */

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WP_MCM_Walker_Category_Filter' ) ) {

	class WP_MCM_Walker_Category_Filter extends Walker_CategoryDropdown {

		function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {

			$pad = str_repeat( '&nbsp;', $depth * 3 );
			$cat_name = apply_filters( 'list_cats', $category->name, $category );

			if( ! isset( $args['value'] ) ) {
				$args['value'] = 'slug';
			}

			$value = ( $args['value']=='slug' ? $category->slug : $category->term_id );

			$output .= '<option class="level-' . $depth . '" value="' . $value . '"';
			if ( $value === (string) $args['selected'] ) {
				$output .= ' selected="selected"';
			}
			$output .= '>';
			$output .= $pad . $cat_name;
			if ( $args['show_count'] ) {
				$output .= '&nbsp;&nbsp;(' . $category->count . ')';
			}

			$output .= "</option>\n";
		}

	}

}
