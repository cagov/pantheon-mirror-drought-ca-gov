/*****************************************************************
 * file: wp-mcm-media-list-toggle.js
 *
 *****************************************************************/
jQuery( document ).ready( function( $ ) {

	// Toggle the category for this attachment.
	jQuery(".media_list_toggle").click( function(e) {
		e.preventDefault();
		mcm_toggle_taxonomy = jQuery(this).attr("data-mcm_toggle_taxonomy");
		mcm_toggle_media = jQuery(this).attr("data-mcm_toggle_media");
		mcm_toggle_slug = jQuery(this).attr("data-mcm_toggle_slug");
		_wpnonce = jQuery(this).attr("data-_wpnonce");
		jQuery.ajax({
			type : "post",
			dataType : "json",
			url : mcmAjax.ajaxurl,
			data : {action: "wp_mcm_action_row_toggle", mcm_toggle_taxonomy : mcm_toggle_taxonomy, mcm_toggle_media : mcm_toggle_media, mcm_toggle_slug : mcm_toggle_slug, _wpnonce: _wpnonce},
			success: function(response) {
				if(response) {
					document.getElementById(response.data.mcm_row_toggle_key).innerHTML = response.data.mcm_row_toggle_value;
				} else {
					alert("Your media could not be toggled!");
				}
			}
		});
	});
});
