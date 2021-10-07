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
    
    // Adjust excerpt behavior to return intended excerpts
    // add_filter('get_the_excerpt', 'cagov_design_system_posts_detail_excerpt');
    
    add_filter( 'rest_post_collection_params', 'cagov_design_system_filter_posts_add_rest_orderby_params', 10, 2 );
}

function cagov_design_system_posts_register_custom_rest_fields() {
    register_rest_field(
        'post',
        'post',
        array(
            'get_callback'    => 'cagov_design_system_posts_get_custom_fields',
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
    $blocks = parse_blocks($post->post_content);
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

function cagov_design_system_filter_posts_add_rest_orderby_params ( $params ) {
    if (isset($params) && is_array($params)) {
	    $params["orderby"] = array("_ca_custom_post_date" => "desc");
    }
	return $params;
}
