<?php

/**
 * Add custom Gutenberg Blocks from the CA Design System
 *
 * @package CADesignSystem
 */

cagov_design_system_posts_detail__init();

function cagov_design_system_posts_detail__init()
{
    // Add post detail metadata to WP-API
    add_action('rest_api_init', 'cagov_design_system_posts_register_custom_rest_fields');
    
    add_filter( 'rest_post_collection_params', 'cagov_design_system_filter_posts_add_rest_orderby_params', 10, 2 );

    // add_filter( 'rest_post_query', 'cagov_design_system_filter_posts_add_rest_post_query', 10, 2);

}

function cagov_design_system_posts_register_custom_rest_fields() {
    register_rest_field(
        'post',
        'custom_post_date',
        array(
            'get_callback'    => 'cagov_design_system_rest_custom_post_date',
            'update_callback' => null,
            'schema'          => null,
        )
    );

    register_rest_field(
        'post',
        'meta',
        array(
            'get_callback'    => 'cagov_design_system_get_post_custom_fields',
            'update_callback' => null,
            'schema'          => null,
        )
    );
}

function cagov_design_system_posts_get_custom_fields()
{
    global $post;
    $custom_fields = cagov_design_system_get_post_custom_fields($post);
    return  $custom_fields;
}

function cagov_design_system_get_post_custom_fields($post)
{
    global $post;
    try {
        $custom_post_link = get_post_meta($post->ID, '_ca_custom_post_link', true);
        $custom_post_date = get_post_meta($post->ID, '_ca_custom_post_date', true);
        $custom_post_location = get_post_meta($post->ID, '_ca_custom_post_location', true);
    
        return array(
            'custom_post_link' => $custom_post_link,
            'custom_post_date' => $custom_post_date,
            'custom_post_location' => $custom_post_location,
        );
        
    } catch (Exception $e) {
    } finally {
    }
    return null;
}

function cagov_design_system_rest_custom_post_date($post)
{
    global $post;
    try {
        $custom_post_date = get_post_meta($post->ID, '_ca_custom_post_date', true);
        return $custom_post_date;
        
    } catch (Exception $e) {
    } finally {
    }
    return null;
}

function cagov_design_system_filter_posts_add_rest_orderby_params ( $params ) {
    if (isset($params) && is_array($params)) {
	    $params["orderby"]['enum'] = "custom_post_date";
    }
	return $params;
}

// function cagov_design_system_filter_posts_add_rest_post_query($query_vars, $request) {
//     $orderby = $request->get_param('orderby');
    
//     if (isset($orderby) && $orderby === 'custom_post_date') {
//         // $query_vars["orderBy"] = "custom_post_date";
//         // $query_vars["meta_key"] = "custom_post_date";
//         // $query_vars["order"] = "desc";


//         $query_vars = array(
//             'post_type' => 'post',
//             'order' => 'DESC',
//             'orderby'   => 'custom_post_date',   
//             // 'meta_query' => array(
//             //     'custom_post_date' => array(
//             //         'key' => 'post'
//             //     )
//             // )
//         );
//     }
//     return $query_vars;
// }

