<?php
/**
 * Login Lockdown Pro
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_Tab_Design extends LoginLockdown
{
    static function display()
    {
        $options = LoginLockdown_Setup::get_options();
        $templates = LoginLockdown_Functions::get_templates();
        
        $tabs[] = array('id' => 'tab_logo', 'class' => 'tab-content', 'label' => __('Logo', 'login-lockdown'), 'callback' => array(__CLASS__, 'tab_logo'));
        $tabs[] = array('id' => 'tab_form', 'class' => 'tab-content', 'label' => __('Form', 'login-lockdown'), 'callback' => array(__CLASS__, 'tab_form'));
        $tabs[] = array('id' => 'tab_fields', 'class' => 'tab-content', 'label' => __('Fields', 'login-lockdown'), 'callback' => array(__CLASS__, 'tab_fields'));
        $tabs[] = array('id' => 'tab_button', 'class' => 'tab-content', 'label' => __('Button', 'login-lockdown'), 'callback' => array(__CLASS__, 'tab_button'));
        $tabs[] = array('id' => 'tab_background', 'class' => 'tab-content', 'label' => __('Background', 'login-lockdown'), 'callback' => array(__CLASS__, 'tab_background'));
        $tabs[] = array('id' => 'tab_custom_css', 'class' => 'tab-content', 'label' => __('Custom CSS', 'login-lockdown'), 'callback' => array(__CLASS__, 'tab_custom_css'));

        echo '<table class="form-table"><tbody>';
        echo '<tr valign="top">
        <th scope="row"><label for="block_bots">Enable Customizer</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('design_enable', array('saved_value' => $options['design_enable'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[design_enable]'));
        echo '<br /><span>You can enable the customizer to use the settings below or leave it turned off to show the default WordPress login page style or customize it using a different plugin or theme settings</span>';
        echo '</td></tr>';
        echo '</tbody>';
        echo '</table>';

        echo '<h3>Templates:</h3>';
        echo '<ul class="design-templates">';
        foreach($templates as $template_id => $template){
            echo '<li><a class="confirm_action" data-confirm="Are you sure you want to enable this template? This will overwrite all Design settings." href="' . add_query_arg(array('_wpnonce' => wp_create_nonce('loginlockdown_install_template'), 'template' => $template_id, 'action' => 'loginlockdown_install_template', 'redirect' => urlencode($_SERVER['REQUEST_URI'])), admin_url('admin.php')) . '"><img src="' . LOGINLOCKDOWN_PLUGIN_URL . '/images/templates/' . $template_id . '.jpg"></a></li>';
        }
        echo '</ul>';

        echo '<div id="tabs_log" class="ui-tabs loginlockdown-tabs-2nd-level">';
        echo '<ul>';
        foreach ($tabs as $tab) {
            echo '<li><a href="#' . $tab['id'] . '">' . $tab['label'] . '</a></li>';
        }
        echo '</ul>';

        foreach ($tabs as $tab) {
            if (is_callable($tab['callback'])) {
                echo '<div style="display: none;" id="' . $tab['id'] . '" class="' . $tab['class'] . '">';
                call_user_func($tab['callback']);
                echo '</div>';
            }
        } // foreach

        echo '</div>'; // second level of tabs


    } // display

    static function tab_logo()
    {
        $options = LoginLockdown_Setup::get_options();

        echo '<table class="form-table"><tbody>';

        echo '<tr valign="top">
        <th scope="row"><label for="color">Logo</label></th>
        <td>';

        echo '<div class="loginlockdown-image-upload-wrapper">';

        echo '<input type="hidden" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_logo]" id="background_image" class="loginlockdown-image-upload-input" value="' . esc_attr($options['design_logo']) . '">';

        echo '<div class="loginlockdown-image-upload-preview-wrapper" ' . (isset($options['design_logo']) ? 'style="background-image:url(\'' . esc_attr($options['design_logo']) . '\')"' : '') . '>';
        if (empty($options['design_logo'])) {
            echo '<img src="' . LOGINLOCKDOWN_PLUGIN_URL . '/images/image.png">';
            echo '<span class="loginlockdown-preview-area" id="background-preview">Select an image to use for your logo</span>';
        }
        echo '<button type="button" name="bg_upload" id="bg_upload" class="button button-primary loginlockdown-upload" style="margin-top: 4px">Open images gallery</button>';
        if (!empty($options['design_logo'])) {
            echo '<button type="button" class="button loginlockdown-image-upload-remove" style="margin-top: 4px">Remove</button>';
        }
        echo '</div>';

        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_logo_width">Logo Width</label></th>
        <td><input type="number" class="regular-text" style="max-width:80px;" id="design_logo_width" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_logo_width]" value="' . $options['design_logo_width'] . '" />px';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_logo_height">Logo Height</label></th>
        <td><input type="number" class="regular-text" style="max-width:80px;" id="design_logo_height" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_logo_height]" value="' . $options['design_logo_height'] . '" />px';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_logo_margin_bottom">Margin Bottom</label></th>
        <td><input type="number" class="regular-text" style="max-width:80px;" id="design_logo_margin_bottom" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_logo_margin_bottom]" value="' . $options['design_logo_margin_bottom'] . '" />px';
        echo '</td></tr>';

        echo '<tr><td></td><td>';
        LoginLockdown_admin::footer_save_button();
        echo '</td></tr>';

        echo '</tbody></table>';
    }

    static function tab_form()
    {
        $options = LoginLockdown_Setup::get_options();
        
        echo '<table class="form-table"><tbody>';
        
        echo '<tr valign="top">
        <th scope="row"><label for="design_text_color">Text Color</label></th>
        <td><input type="text" class="lockdown-color" id="design_text_color" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_text_color]" value="' . $options['design_text_color'] . '" />';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_link_color">Link Color</label></th>
        <td><input type="text" class="lockdown-color" id="design_link_color" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_link_color]" value="' . $options['design_link_color'] . '" />';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_link_hover_color">Link Hover Color</label></th>
        <td><input type="text" class="lockdown-color" id="design_link_hover_color" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_link_hover_color]" value="' . $options['design_link_hover_color'] . '" />';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_form_width">Form Width</label></th>
        <td><input type="number" class="regular-text" style="max-width:80px;" id="design_form_width" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_form_width]" value="' . $options['design_form_width'] . '" />px';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_form_height">Form Height</label></th>
        <td><input type="number" class="regular-text" style="max-width:80px;" id="design_form_height" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_form_height]" value="' . $options['design_form_height'] . '" />px';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_form_padding">Form Padding</label></th>
        <td><input type="number" class="regular-text" style="max-width:80px;" id="design_form_padding" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_form_padding]" value="' . $options['design_form_padding'] . '" />px';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_form_border_color">Form Border Color</label></th>
        <td><input type="text" class="lockdown-color" id="design_form_border_color" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_form_border_color]" value="' . $options['design_form_border_color'] . '" />';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_form_border_width">Form Border Width</label></th>
        <td><div class="range-slider-wrapper">';
        echo '<input type="range" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_form_border_width]" value="' . $options['design_form_border_width'] . '" min="0" max="20" class="range-slider">';
        echo '</div>';
        echo '<span class="range_value" data-unit="px">' . $options['design_form_border_width'] . '</span>px';
        echo '</td></tr>';
        
        echo '<tr valign="top">
        <th scope="row"><label for="design_form_border_radius">Corner Radius</label></th>
        <td><div class="range-slider-wrapper">';
        echo '<input type="range" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_form_border_radius]" value="' . $options['design_form_border_radius'] . '" min="0" max="100" class="range-slider">';
        echo '</div>';
        echo '<span class="range_value" data-unit="px">' . $options['design_form_border_radius'] . '</span>px';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_form_background_color">Background Color</label></th>
        <td><input type="text" class="lockdown-color" id="design_form_background_color" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_form_background_color]" value="' . $options['design_form_background_color'] . '" />';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="color">Background Image</label></th>
        <td>';

        echo '<div class="loginlockdown-image-upload-wrapper">';

        echo '<input type="hidden" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_form_background_image]" id="design_form_background_image" class="loginlockdown-image-upload-input" value="' . esc_attr($options['design_form_background_image']) . '">';

        echo '<div class="loginlockdown-image-upload-preview-wrapper" ' . (isset($options['design_form_background_image']) ? 'style="background-image:url(\'' . esc_attr($options['design_form_background_image']) . '\')"' : '') . '>';
        if (empty($options['design_form_background_image'])) {
            echo '<img src="' . LOGINLOCKDOWN_PLUGIN_URL . '/images/image.png">';
            echo '<span class="loginlockdown-preview-area" id="background-preview">Select an image from our 400,000+ images gallery, or upload your own</span>';
        }
        echo '<button type="button" name="bg_upload" id="bg_upload" class="button button-primary loginlockdown-upload loginlockdown-free-images" style="margin-top: 4px">Open images gallery</button>';
        if (!empty($options['design_background_image'])) {
            echo '<button type="button" class="button loginlockdown-image-upload-remove" style="margin-top: 4px">Remove</button>';
        }
        echo '</div>';

        echo '</td></tr>';

        echo '<tr><td></td><td>';
        LoginLockdown_admin::footer_save_button();
        echo '</td></tr>';

        echo '</tbody></table>';
    }

    static function tab_fields()
    {
        $options = LoginLockdown_Setup::get_options();

        echo '<table class="form-table"><tbody>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_label_font_size">Label Font Size</label></th>
        <td><div class="range-slider-wrapper">';
        echo '<input type="range" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_label_font_size]" value="' . $options['design_label_font_size'] . '" min="6" max="96" class="range-slider">';
        echo '</div>';
        echo '<span class="range_value" data-unit="px">' . $options['design_label_font_size'] . '</span>px';
        echo '</td></tr>';
        
        echo '<tr valign="top">
        <th scope="row"><label for="design_label_text_color">Label Text Color</label></th>
        <td><input type="text" class="lockdown-color" id="design_label_text_color" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_label_text_color]" value="' . $options['design_label_text_color'] . '" />';
        echo '</td></tr>';
        
        echo '<tr valign="top">
        <th scope="row"><label for="design_field_font_size">Field Font Size</label></th>
        <td><div class="range-slider-wrapper">';
        echo '<input type="range" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_field_font_size]" value="' . $options['design_field_font_size'] . '" min="6" max="96" class="range-slider">';
        echo '</div>';
        echo '<span class="range_value" data-unit="px">' . $options['design_field_font_size'] . '</span>px';
        echo '</td></tr>';
        
        echo '<tr valign="top">
        <th scope="row"><label for="design_field_text_color">Field Text Color</label></th>
        <td><input type="text" class="lockdown-color" id="design_field_text_color" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_field_text_color]" value="' . $options['design_field_text_color'] . '" />';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_field_border_color">Field Border Color</label></th>
        <td><input type="text" class="lockdown-color" id="design_field_border_color" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_field_border_color]" value="' . $options['design_field_border_color'] . '" />';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_field_border_width">Field Border Width</label></th>
        <td><div class="range-slider-wrapper">';
        echo '<input type="range" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_field_border_width]" value="' . $options['design_field_border_width'] . '" min="0" max="20" class="range-slider">';
        echo '</div>';
        echo '<span class="range_value" data-unit="px">' . $options['design_field_border_width'] . '</span>px';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_field_border_radius">Field Corner Radius</label></th>
        <td><div class="range-slider-wrapper">';
        echo '<input type="range" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_field_border_radius]" value="' . $options['design_field_border_radius'] . '" min="0" max="100" class="range-slider">';
        echo '</div>';
        echo '<span class="range_value" data-unit="px">' . $options['design_field_border_radius'] . '</span>px';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_field_background_color">Field Background Color</label></th>
        <td><input type="text" class="lockdown-color" id="design_field_background_color" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_field_background_color]" value="' . $options['design_field_background_color'] . '" />';
        echo '</td></tr>';

                echo '<tr><td></td><td>';
        LoginLockdown_admin::footer_save_button();
        echo '</td></tr>';

        echo '</tbody></table>';
    }

    static function tab_button()
    {
        $options = LoginLockdown_Setup::get_options();

        echo '<table class="form-table"><tbody>';
        
        echo '<tr valign="top">
        <th scope="row"><label for="design_button_font_size">Button Font Size</label></th>
        <td><div class="range-slider-wrapper">';
        echo '<input type="range" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_button_font_size]" value="' . $options['design_button_font_size'] . '" min="6" max="96" class="range-slider">';
        echo '</div>';
        echo '<span class="range_value" data-unit="px">' . $options['design_button_font_size'] . '</span>px';
        echo '</td></tr>';
        
        echo '<tr valign="top">
        <th scope="row"><label for="design_button_text_color">Button Text Color</label></th>
        <td><input type="text" class="lockdown-color" id="design_button_text_color" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_button_text_color]" value="' . $options['design_button_text_color'] . '" />';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_button_border_color">Button Border Color</label></th>
        <td><input type="text" class="lockdown-color" id="design_button_border_color" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_button_border_color]" value="' . $options['design_button_border_color'] . '" />';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_button_border_width">Button Border Width</label></th>
        <td><div class="range-slider-wrapper">';
        echo '<input type="range" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_button_border_width]" value="' . $options['design_button_border_width'] . '" min="0" max="20" class="range-slider">';
        echo '</div>';
        echo '<span class="range_value" data-unit="px">' . $options['design_button_border_width'] . '</span>px';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_button_border_radius">Button Corner Radius</label></th>
        <td><div class="range-slider-wrapper">';
        echo '<input type="range" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_button_border_radius]" value="' . $options['design_button_border_radius'] . '" min="0" max="100" class="range-slider">';
        echo '</div>';
        echo '<span class="range_value" data-unit="px">' . $options['design_button_border_radius'] . '</span>px';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_button_background_color">Button Background Color</label></th>
        <td><input type="text" class="lockdown-color" id="design_button_background_color" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_button_background_color]" value="' . $options['design_button_background_color'] . '" />';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_button_hover_text_color">Button Hover Text Color</label></th>
        <td><input type="text" class="lockdown-color" id="design_button_hover_text_color" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_button_hover_text_color]" value="' . $options['design_button_hover_text_color'] . '" />';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_button_border_color">Button Hover Border Color</label></th>
        <td><input type="text" class="lockdown-color" id="design_button_hover_border_color" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_button_hover_border_color]" value="' . $options['design_button_hover_border_color'] . '" />';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_button_hover_background_color">Button Hover Background Color</label></th>
        <td><input type="text" class="lockdown-color" id="design_button_hover_background_color" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_button_hover_background_color]" value="' . $options['design_button_hover_background_color'] . '" />';
        echo '</td></tr>';

        echo '<tr><td></td><td>';
        LoginLockdown_admin::footer_save_button();
        echo '</td></tr>';

        echo '</tbody></table>';
    }

    static function tab_background()
    {
        $options = LoginLockdown_Setup::get_options();

        echo '<table class="form-table"><tbody>';

        echo '<tr valign="top">
        <th scope="row"><label for="design_background_color">Background Color</label></th>
        <td><input type="text" class="lockdown-color" id="design_background_color" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_background_color]" value="' . $options['design_background_color'] . '" />';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="color">Background Image</label></th>
        <td>';

        echo '<div class="loginlockdown-image-upload-wrapper">';

        echo '<input type="hidden" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_background_image]" id="design_background_image" class="loginlockdown-image-upload-input" value="' . esc_attr($options['design_background_image']) . '">';

        echo '<div class="loginlockdown-image-upload-preview-wrapper" ' . (isset($options['design_background_image']) ? 'style="background-image:url(\'' . esc_attr($options['design_background_image']) . '\')"' : '') . '>';
        if (empty($options['design_background_image'])) {
            echo '<img src="' . LOGINLOCKDOWN_PLUGIN_URL . '/images/image.png">';
            echo '<span class="loginlockdown-preview-area" id="background-preview">Select an image from our 400,000+ images gallery, or upload your own</span>';
        }
        echo '<button type="button" name="bg_upload" id="bg_upload" class="button button-primary loginlockdown-upload loginlockdown-free-images" style="margin-top: 4px">Open images gallery</button>';
        if (!empty($options['design_background_image'])) {
            echo '<button type="button" class="button loginlockdown-image-upload-remove" style="margin-top: 4px">Remove</button>';
        }
        echo '</div>';

        echo '</td></tr>';


        echo '<tr><td></td><td>';
        LoginLockdown_admin::footer_save_button();
        echo '</td></tr>';

        echo '</tbody></table>';
    } // display

    
    static function tab_custom_css()
    {
        $options = LoginLockdown_Setup::get_options();
        
        echo '<div id="custom_css_editor"></div>';
        echo '<textarea id="custom_css" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[design_custom_css]">' . $options['design_custom_css'] . '</textarea>';
        echo '<p class="mtnc-form-help-block">Write only the CSS code. Do not include the &lt;style&gt; tags.</p>';

        LoginLockdown_admin::footer_save_button();
    }


} // class LoginLockdown_Tab_Login_Form
