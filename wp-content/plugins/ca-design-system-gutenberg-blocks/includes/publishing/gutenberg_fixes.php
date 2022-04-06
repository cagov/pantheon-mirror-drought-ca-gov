<?php

remove_filter( 'render_block', 'wp_render_layout_support_flag' );

add_filter( 'render_block', function( $block_content, $block ) {
    // Suppress automatic ID chnages on buttons class name
    // Wordpress adds extra uniq ids and these are incompatible with the a versioned content API
    if ( $block['blockName'] === 'core/buttons' ) {
		return $block_content;
	}
	
	return wp_render_layout_support_flag( $block_content, $block );
}, 10, 2 );