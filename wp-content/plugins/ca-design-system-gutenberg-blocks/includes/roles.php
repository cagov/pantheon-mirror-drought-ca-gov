<?php

function cagov_design_system_headless_wordpress_new_role() {  
 
    //add the new user role
    add_role(
        'content_admin',
        'Content Admin',
        array(
            'read'         => true,
            'delete_posts' => false,
            'edit_theme_options' => true,
            // 'cagov_design_system_headless_wordpress_approvals' => true,
        )
    );
 
}
add_action('admin_init', 'cagov_design_system_headless_wordpress_new_role');