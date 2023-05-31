/*****************************************************************
 * file: wp-mcm-admin.js
 *
 *****************************************************************/
jQuery( document ).ready( function( $ ) {

	// Show the tooltips.
	$( ".wp-mcm-info" ).on( "mouseover", function() {
		$( this ).find( ".wp-mcm-info-text" ).show();
	});

	$( ".wp-mcm-info" ).on( "mouseout", function() {
		$( this ).find( ".wp-mcm-info-text" ).hide();
	});

});
