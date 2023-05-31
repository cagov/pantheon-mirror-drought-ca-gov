jQuery(document).ready(function($){
$('form.wp-mcm-form-imexport').on('submit', function(e){
   e.preventDefault();
   var that = $(this),
   url = that.attr('action'),
   type = that.attr('method');
   var wp_mcm_export_filter_media_category = $('#wp-mcm-wp-mcm-export-filter-media-category  option:selected').val();
   var wp_mcm_export_filter_authors        = $('#wp-mcm-wp-mcm-export-filter-authors         option:selected').val();
   var wp_mcm_export_startdate             = $('#wp-mcm-wp-mcm-export-filter-startdate       option:selected').val();
   var wp_mcm_export_enddate               = $('#wp-mcm-wp-mcm-export-filter-enddate         option:selected').val();
   $.ajax({
      url: mcm_imexport_js.ajax_url,
      type:"POST",
      dataType:'type',
      data: {
         action:'wp_mcm_button_export',
         wp_mcm_export_filter_media_category:wp_mcm_export_filter_media_category,
         wp_mcm_export_filter_authors:wp_mcm_export_filter_authors,
         wp_mcm_export_startdate:wp_mcm_export_startdate,
         wp_mcm_export_enddate:wp_mcm_export_enddate,
    },   success: function(response){
        $(".mcm_success_msg").css("display","block");
     }, error: function(data){
         $(".mcm_error_msg").css("display","block");      }
   });
$('.wp-mcm-form-imexport')[0].reset();
  });
});
