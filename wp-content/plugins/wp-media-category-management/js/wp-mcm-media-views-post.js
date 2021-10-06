window.wp = window.wp || {};
(function() {
	var MCMMediaLibraryTaxonomyFilter = wp.media.view.AttachmentFilters.extend({
	id: 'mcm-media-attachment-taxonomy-filter',

		createFilters: function() {
			var filters = {};
			var that = this;

			// Formats the 'mcm_terms' we've included via wp_localize_script()
			_.each(wpmcm_admin_js.mcm_terms || {}, function(value, index) {
				filters[index] = {
				  text: value.name,
				};
				filters[index]['props'] = {};
				filters[index]['props'][wpmcm_admin_js.mcm_taxonomy] = value.term_id;
			});

			// Add the option to select ALL
			filters.all = {
				text: wpmcm_admin_js.mcm_label_all,
				priority: 10
			};
			filters['all']['props'] = {};
			filters['all']['props'][wpmcm_admin_js.mcm_taxonomy] = '';

			// Add the option to select No category
			filters.none = {
				text: wpmcm_admin_js.mcm_label_none,
				priority: 10
			};
			filters['none']['props'] = {};
			filters['none']['props'][wpmcm_admin_js.mcm_taxonomy] = 'no_category';

			this.filters = filters;
		}
	});
	/**
	  * Extend and override wp.media.view.AttachmentsBrowser to include our new filter
	  */
	var AttachmentsBrowser = wp.media.view.AttachmentsBrowser;
	wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
		createToolbar: function() {
			var that = this;
			i = 1;

			// Make sure to load the original toolbar
			AttachmentsBrowser.prototype.createToolbar.call(this);

			// Get the labels and items for each mcm_taxonomies
			that.toolbar.set( 'MCMMediaLibraryTaxonomyFilter', new MCMMediaLibraryTaxonomyFilter({
				controller: that.controller,
				model: that.collection.props,
				priority: -80 + 10*i++,
			}).render() );
		}
	});
	
})( jQuery );