<?php
/**
 * Login Lockdown Pro
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_Tab_Login_Form extends LoginLockdown
{
    static function display()
    {
        $tabs[] = array('id' => 'tab_login_basic', 'class' => 'tab-content', 'label' => __('Basic', 'login-lockdown'), 'callback' => array(__CLASS__, 'tab_basic'));
        $tabs[] = array('id' => 'tab_login_advanced', 'class' => 'tab-content', 'label' => __('Advanced', 'login-lockdown'), 'callback' => array(__CLASS__, 'tab_advanced'));
        $tabs[] = array('id' => 'tab_login_tools', 'class' => 'tab-content', 'label' => __('Tools', 'login-lockdown'), 'callback' => array(__CLASS__, 'tab_tools'));

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

    static function tab_basic()
    {
        $options = LoginLockdown_Setup::get_options();

        echo '<table class="form-table"><tbody>';

        echo '<tr valign="top">
        <th scope="row"><label for="max_login_retries">Max Login Retries</label></th>
        <td><input type="number" class="regular-text" id="max_login_retries" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[max_login_retries]" value="' . $options['max_login_retries'] . '" />';
        echo '<br><span>Number of failed login attempts within the "Retry Time Period Restriction" (defined below) needed to trigger a Lockdown.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="retries_within">Retry Time Period Restriction</label></th>
        <td><input type="number" class="regular-text" id="retries_within" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[retries_within]" value="' . $options['retries_within'] . '" /> minutes';
        echo '<br><span>Amount of time in which failed login attempts are allowed before a lockdown occurs.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="lockout_length">Lockout Length</label></th>
        <td><input type="number" class="regular-text" id="lockout_length" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[lockout_length]" value="' . $options['lockout_length'] . '" /> minutes';
        echo '<br><span>Amount of time a particular IP will be locked out once a lockdown has been triggered.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="lockout_invalid_usernames">Log Failed Attempts With Non-existant Usernames</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('lockout_invalid_usernames', array('saved_value' => $options['lockout_invalid_usernames'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[lockout_invalid_usernames]'));
        echo '<br /><span>Log failed log in attempts with non-existant usernames the same way failed attempts with bad passwords are logged.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="mask_login_errors">Mask Login Errors</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('mask_login_errors', array('saved_value' => $options['mask_login_errors'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[mask_login_errors]'));
        echo '<br /><span>Hide log in error details (such as invalid username, invalid password, invalid captcha value) to minimize data available to attackers.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="global_block">Block Type</label></th>
        <td>';
            echo '<label class="loginlockdown-radio-option">';
            echo '<span class="radio-container"><input type="radio" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[global_block]" id="global_block_global" value="1" ' . ($options['global_block'] == 1?'checked':'') . '><span class="radio"></span></span> Completely block website access';
            echo '</label>';

            echo '<label class="loginlockdown-radio-option">';
            echo '<span class="radio-container"><input type="radio" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[global_block]" id="global_block_login" value="0" ' . ($options['global_block'] != 1?'checked':'') . '><span class="radio"></span></span> Only block access to the login page';
            echo '</label>';
        echo '<span>Completely block website access for blocked IPs, or just blocking access to the login page.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="block_message">Block Message</label></th>
        <td><input type="text" class="regular-text" id="block_message" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[block_message]" value="' . $options['block_message'] . '" />';
        echo '<br /><span>Message displayed to visitors blocked due to too many failed login attempts. Default: <i>We\'re sorry, but your IP has been blocked due to too many recent failed login attempts.</i></span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="whitelist">Whitelisted IPs</label></th>
        <td><textarea class="regular-text" id="whitelist" rows="6" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[whitelist]">' . (is_array($options['whitelist'])?implode(PHP_EOL, $options['whitelist']):$options['whitelist']) . '</textarea>';
        echo '<br /><span>List of IP addresses that will never be blocked. Enter one IP per line.<br>Your current IP is: <code>' . $_SERVER['REMOTE_ADDR'] . '</code></span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="show_credit_link">Show Credit Link</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('show_credit_link', array('saved_value' => $options['show_credit_link'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[show_credit_link]'));
        echo '<br /><span>Show a small "form protected by" link below the login form to help others learn about Login Lockdown and protect their sites.</span>';
        echo '</td></tr>';

        echo '<tr><td></td><td>';
        LoginLockdown_admin::footer_save_button();
        echo '</td></tr>';

        echo '</tbody></table>';
    }

    static function tab_advanced()
    {
        $options = LoginLockdown_Setup::get_options();

        echo '<table class="form-table"><tbody>';

        if(is_multisite()){
            echo '<div class="notice-box-info" style="border-color:#ff9f00;">Login Lockdown does not support changing the Login URL for multisite installs</div>';
        }

        $login_plugin_check = LoginLockdown_Functions::login_compatibility_check();
        if(LoginLockdown_Functions::login_compatibility_check() !== true){
            echo '<div class="notice-box-info" style="border-color:#ff9f00;">It looks like you already have <strong>' . $login_plugin_check . '</strong> active. You should disable it if you want to change the Login URL using Login Lockdown to prevent conflicts.</div>';
        }


        echo '<tr valign="top" ' . (is_multisite() || $login_plugin_check !== true?'class="loginlockdown-option-disabled"':''). '>
        <th scope="row"><label for="login_url">Login URL</label></th>
        <td><code>' . site_url('/') . '</code><input type="text" class="regular-text" style="width:160px;" id="login_url" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[login_url]" value="' . $options['login_url'] . '" /><code>/</code>';
        echo '<br /><span>Protect your website by changing the login page URL and prevent access to the default wp-login.php page and the wp-admin path that represent the main target of most attacks. Leave empty to use default login URL.</span>';
        echo '</td></tr>';

        echo '<tr valign="top" ' . (is_multisite() || $login_plugin_check !== true?'class="loginlockdown-option-disabled"':''). '>
        <th scope="row"><label for="login_redirect_url">Login URL</label></th>
        <td><code>' . site_url('/') . '</code><input type="text" class="regular-text" style="width:160px;"  id="login_redirect_url" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[login_redirect_url]" value="' . $options['login_redirect_url'] . '" placeholder="404" /><code>/</code>';
        echo '<br /><span>URL where attempts to access wp-login.php or wp-admin should be redirected to. If custom URL is set above, this defaults to ' . site_url('/404/') . ' unless you set it to something else.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="password_check">Password Check</label></th>
        <td><button id="lockdown_run_tests" class="button button-primary button-large" style="margin-bottom:6px;">Test user passwords <i class="loginlockdown-icon loginlockdown-lock"></i></button>';
        echo '<br><span>Check if any user has a weak password that is vulnerable to common brute-force dictionary attacks.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="anonymous_logging">Anonymous Activity Logging</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('anonymous_logging', array('saved_value' => $options['anonymous_logging'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[anonymous_logging]'));
        echo '<br /><span>Logging anonymously means IP addresses of your visitors are stored as hashed values. The user\'s country and user agent are still logged, but without the IP these are not considered personal data according to GDPR.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="log_passwords">Log Passwords</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('log_passwords', array('saved_value' => $options['log_passwords'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[log_passwords]'));
        echo '<br /><span>Enablign this option will log the passwords used in failed login attempts. This is not recommended on websites with multiple users as the passwords are logged as plain text and can be viewed by all users that have access to the Login Lockdown logs or the database.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="block_bots">Block Bots</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('block_bots', array('saved_value' => $options['block_bots'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[block_bots]'));
        echo '<br /><span>Block bots from accessing the login page and attempting to log in.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="instant_block_nonusers">Block Login Attempts With Non-existing Usernames</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('instant_block_nonusers', array('saved_value' => $options['instant_block_nonusers'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[instant_block_nonusers]'));
        echo '<br /><span>Immediately block IP if there is a failed login attempt with a non-existing username</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="honeypot">Add Honeypot for Bots</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('honeypot', array('saved_value' => $options['honeypot'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[honeypot]'));
        echo '<br /><span>Add a special, hidden "honeypot" field to the login form to catch and prevent bots from attempting to log in.<br>This does not affect the way humans log in, nor does it add an extra step.</span>';
        echo '</td></tr>';

        echo '<table class="form-table"><tbody>';

        $cookie_lifetime = array();
        $cookie_lifetime[] = array('val' => '14', 'label' => '14 days (default)');
        $cookie_lifetime[] = array('val' => '30', 'label' => '30 days');
        $cookie_lifetime[] = array('val' => '90', 'label' => '3 months');
        $cookie_lifetime[] = array('val' => '180', 'label' => '6 months');
        $cookie_lifetime[] = array('val' => '365', 'label' => '1 year');

        echo '<tr valign="top">
        <th scope="row"><label for="cookie_lifetime">Cookie Lifetime</label></th>
        <td><select id="cookie_lifetime" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[cookie_lifetime]">';
        LoginLockdown_Utility::create_select_options($cookie_lifetime, $options['cookie_lifetime']);
        echo '</select>';
        echo '<br /><span>Cookie lifetime if "Remember Me" option is checked on login form.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="uninstall_delete">Wipe Data on Plugin Delete</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('uninstall_delete', array('saved_value' => $options['uninstall_delete'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[uninstall_delete]'));
        echo '<br /><span>If enabled, Login Lockdown options, rules and all log tables will be deleted when the plugin is deleted.</span>';
        echo '</td></tr>';

        echo '<tr><td></td><td>';
        LoginLockdown_admin::footer_save_button();
        echo '</td></tr>';

        echo '</tbody></table>';
    }

    static function tab_tools()
    {
        $options = LoginLockdown_Setup::get_options();

        echo '<table class="form-table"><tbody>';

        echo '<tr valign="top">
        <th scope="row"><label for="password_check">Email Test</label></th>
        <td><button id="lockdown_send_email" class="button button-primary button-large" style="margin-bottom:6px;">Send test email</button>';
        echo '<br><span>Send an email to test that you can receive emails from your website.</span>';
        echo '</td></tr>';

        /*
        echo '<tr valign="top">
        <th><label>Setup Wizard</label></th>
        <td>
        <button class="button button-primary button-large open-setup-wizard" href="#">Run Setup Wizard</button>';
        echo '<br><span>If you want to reset your settings to one of the default sets, you can run the setup wizard again.</span>';
        echo '</td></tr>';
        */

        echo '<tr valign="top">
        <th scope="row"><label for="lockdown_recovery_url">Recovery URL</label></th>
        <td><button id="lockdown_recovery_url_show" class="button button-primary button-large" style="margin-bottom:6px;">View Recovery URL</button>';
        echo '<br><span>In case you lock yourself out and need to whitelist your IP address, please save the recovery URL somewhere safe.<br>Do NOT share the recovery URL.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th><label>Import Settings</label></th>
        <td>
        <input accept="txt" type="file" name="loginlockdown_import_file" value="">
        <button name="loginlockdown_import_file" id="submit" class="button button-primary button-large" value="">Upload</button>
        </td>
        </tr>';

        echo '<tr valign="top">
        <th><label>Export Settings</label></th>
        <td>
        <a class="button button-primary button-large" style="padding-top: 3px;" href="' . esc_url(add_query_arg(array('action' => 'loginlockdown_export_settings'), admin_url('admin.php'))) . '">Download Export File</a>
        </td>
        </tr>';

        echo '</tbody></table>';
    }
} // class LoginLockdown_Tab_Login_Form
