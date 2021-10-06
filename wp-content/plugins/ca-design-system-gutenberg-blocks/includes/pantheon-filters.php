<?php

/**
 * CADesignSystem Filters
 *
 * @package CADesignSystem
 */

/* WP Filters */
// add_filter('template_include', 'cagov_page_template_filter');
add_filter('theme_page_templates', 'cagov_register_page_post_templates', 20);

/**
 * CADesignSystem Page/Post Templates
 * Adds CADesignSystem page/post templates.
 *
 * @category add_filter( 'theme_page_templates', 'cagov_register_page_post_templates', 20 );
 * @link https://developer.wordpress.org/reference/hooks/theme_page_templates/
 * @param  array $theme_templates Array of page templates. Keys are filenames, values are translated names.
 *
 * @return array
 */
function cagov_register_page_post_templates($theme_templates)
{
    return array_merge($theme_templates, cagov_get_page_post_templates());
}

/**
 * Include plugin's template if there's one chosen for the rendering page.
 *
 * @category add_filter( 'template_include', 'cagov_page_template_filter' );
 * @param  string $template Array of page templates. Keys are filenames, values are translated names.
 * @link https://developer.wordpress.org/reference/hooks/template_include/
 * @return string The path of the template to include.
 */
// function cagov_page_template_filter($template)
// {
//     global $post;

//     if (!isset($post->ID)) {
//         return $template;
//     }

//     // @TODO Update to use simple naming of template
    
//     // @TODO Is this even called?

//     $user_selected_template = get_page_template_slug($post->ID);

//     $file_name              = pathinfo($user_selected_template, PATHINFO_BASENAME);
//     $template_dir           = CAGOV_DESIGN_SYSTEM_HEADLESS_WORDPRESS__DIR_PATH . 'includes/templates-pantheon/';
    
//     $is_plugin = false;
//     if (file_exists($template_dir . $file_name)) {
//         $is_plugin = true;
//     }

//     if ('' !== $user_selected_template && $is_plugin) {
//         $template = $user_selected_template;
//     }

//     if (is_category() && $is_plugin) {
//         $template = $template_dir . 'plugin/category-template.php';
//     }

//     return $template;
// }
