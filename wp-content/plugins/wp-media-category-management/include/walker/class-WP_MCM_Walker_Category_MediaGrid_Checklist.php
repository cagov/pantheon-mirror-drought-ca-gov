<?php
/**
 * WP Media Category Management WP_MCM_Walker_Category_MediaGrid_Checklist class for wp_checklist_categories, based on https://gist.github.com/stephenh1988/2902509
 * 
 * @since  2.0.0
 * @author DeBAAT
 */

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WP_MCM_Walker_Category_MediaGrid_Checklist' ) ) {

	class WP_MCM_Walker_Category_MediaGrid_Checklist extends Walker {
		var $tree_type = 'category';
		var $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); 

		function start_lvl( &$output, $depth = 0, $args = array() ) {
			$indent = str_repeat("\t", $depth);
			$output .= "$indent<ul class='children'>\n";
		}

		function end_lvl( &$output, $depth = 0, $args = array() ) {
			$indent = str_repeat("\t", $depth);
			$output .= "$indent</ul>\n";
		}

		function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
			extract($args);

			if ( empty($taxonomy) ) {
				$taxonomy = 'category';
			}

			$name = 'tax_input['.$taxonomy.']';

			$output .= "\n<li id='{$taxonomy}-{$category->term_id}'>";
			$output .= '<label class="selectit">';
			$output .= '<input value="' . $category->slug . '" ';
			$output .= 'type="checkbox" ';
			$output .= 'name="'.$name.'['. $category->slug.']" ';
			$output .= 'id="in-'.$taxonomy.'-' . $category->term_id . '"';
			$output .= checked( in_array( $category->term_id, $selected_cats ), true, false );
			$output .= disabled( empty( $args['disabled'] ), false, false );
			$output .= ' /> ';
			$output .= esc_html( apply_filters('the_category', $category->name ));
			$output .= '</label>';
		}

		function end_el( &$output, $category, $depth = 0, $args = array() ) {
			$output .= "</li>\n";
		}

	}

}
