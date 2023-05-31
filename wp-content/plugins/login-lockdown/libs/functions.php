<?php

/**
 * Login Lockdown Pro
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_Functions extends LoginLockdown
{
    static $wp_login_php;

    static function countFails($username = "")
    {
        global $wpdb;
        $options = LoginLockdown_Setup::get_options();
        $ip = LoginLockdown_Utility::getUserIP();

        $numFails = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(login_attempt_ID) FROM " . $wpdb->lockdown_login_fails . " WHERE login_attempt_date + INTERVAL %d MINUTE > %s AND login_attempt_IP = %s",
                array($options['retries_within'], current_time('mysql'), $ip)
            )
        );

        return $numFails;
    }

    static function incrementFails($username = "", $reason = "")
    {
        global $wpdb;
        $options = LoginLockdown_Setup::get_options();
        $ip = LoginLockdown_Utility::getUserIP();

        $username = sanitize_user($username);
        $user = get_user_by('login', $username);

        if ($user || 1 == $options['lockout_invalid_usernames']) {
            if ($user === false) {
                $user_id = -1;
            } else {
                $user_id = $user->ID;
            }

            $agent = LoginLockdown_Utility::parse_user_agent_array();

            $wpdb->insert(
                $wpdb->lockdown_login_fails,
                array(
                    'user_id' => $user_id,
                    'login_attempt_date' => current_time('mysql'),
                    'login_attempt_IP' => $ip,
                    'failed_user' => $username,
                    'failed_pass' => isset($_POST['pwd']) && $options['log_passwords'] == 1 ? $_POST['pwd'] : '',
                    'country' => LoginLockdown_Utility::getUserCountry(),
                    'user_agent' => @$_SERVER['HTTP_USER_AGENT'],
                    'user_agent_browser' => $agent['browser'],
                    'user_agent_browser_version' => $agent['browser_ver'],
                    'user_agent_os' => $agent['os'],
                    'user_agent_os_ver' => $agent['os_ver'],
                    'user_agent_device' => $agent['device'],
                    'user_agent_bot' => $agent['bot'],
                    'reason' => $reason
                )
            );
        }
    }

    static function lockDown($username = "", $reason = "")
    {
        global $wpdb;
        $options = LoginLockdown_Setup::get_options();
        $ip = LoginLockdown_Utility::getUserIP();

        $username = sanitize_user($username);
        $user = get_user_by('login', $username);
        if ($user || 1 == $options['lockout_invalid_usernames']) {
            if ($user === false) {
                $user_id = -1;
            } else {
                $user_id = $user->ID;
            }

            $agent = LoginLockdown_Utility::parse_user_agent_array();

            $wpdb->insert(
                $wpdb->lockdown_lockdowns,
                array(
                    'user_id' => $user_id,
                    'lockdown_date' => current_time('mysql'),
                    'release_date' => date('Y-m-d H:i:s', strtotime(current_time('mysql')) + $options['lockout_length'] * 60),
                    'lockdown_IP' => $ip,
                    'country' => LoginLockdown_Utility::getUserCountry(),
                    'user_agent' => @$_SERVER['HTTP_USER_AGENT'],
                    'user_agent_browser' => $agent['browser'],
                    'user_agent_browser_version' => $agent['browser_ver'],
                    'user_agent_os' => $agent['os'],
                    'user_agent_os_ver' => $agent['os_ver'],
                    'user_agent_device' => $agent['device'],
                    'user_agent_bot' => $agent['bot'],
                    'reason' => $reason
                )
            );
        }
    }

    static function isLockedDown()
    {
        global $wpdb;
        $ip = LoginLockdown_Utility::getUserIP();

        $stillLocked = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM " . $wpdb->lockdown_lockdowns . " WHERE release_date > %s AND lockdown_IP = %s AND unlocked = 0", array(current_time('mysql'), $ip)));

        return $stillLocked;
    }

    static function is_rest_request()
    {
        if (defined('REST_REQUEST') && REST_REQUEST || isset($_GET['rest_route']) && strpos(sanitize_text_field(wp_unslash($_GET['rest_route'])), '/', 0) === 0) {
            return true;
        }

        global $wp_rewrite;
        if (null === $wp_rewrite) {
            $wp_rewrite = new WP_Rewrite();
        }

        $rest_url    = wp_parse_url(trailingslashit(rest_url()));
        $current_url = wp_parse_url(add_query_arg(array()));
        $is_rest     = strpos($current_url['path'], $rest_url['path'], 0) === 0;

        return $is_rest;
    }

    static function check_2fa()
    {
        $options = LoginLockdown_Setup::get_options();
        if ($options['2fa_email'] !== '1') {
            return;
        }

        $user = wp_get_current_user();

        // skip all when not logged in
        if (!is_user_logged_in()) {
            return;
        }

        // skip when logging out
        if (!empty($_GET['action']) && $_GET['action'] == 'logout') {
            return;
        }

        // bypass for MainWP
        if (!empty($_GET['login_required']) && !empty($_GET['mainwpsignature']) && !empty($_GET['nonce']) && !empty($_GET['user'])) {
            $key = rand(10000, 99999);
            update_user_meta($user->ID, 'loginlockdown_2fa_key', $key);
            update_user_meta($user->ID, 'loginlockdown_2fa_key_check', $key);
        }

        // when email link is clicked we check and write new key
        if (!empty($_GET['loginlockdown_2fa_confirm'])) {

            if (sanitize_text_field(@$_GET['key_hash']) != md5('elephant' . sanitize_text_field(@$_GET['key']))) {
                wp_die('Invalid 2FA hash value.', '2FA Authentication', array('link_text' => 'Log out and log in again to resend the email', 'link_url' => wp_logout_url()));
            }
            if (sanitize_text_field(@$_GET['key']) != get_user_meta($user->ID, 'loginlockdown_2fa_key', true)) {
                wp_die('Invalid 2FA key.', '2FA Authentication', array('link_text' => 'Log out and log in again to resend the email', 'link_url' => wp_logout_url()));
            }

            // write new key
            update_user_meta($user->ID, 'loginlockdown_2fa_key_check', $_GET['key']);

            if (!empty($_GET['redirect_to'])) {
                wp_redirect($_GET['redirect_to']);
                die();
            }
        } // setting/checking new key

        if (empty(get_user_meta($user->ID, 'loginlockdown_2fa_key', true))) {
            wp_die('No 2FA key found.', '2FA Authentication', array('link_text' => 'Log out and log in again to resend the email', 'link_url' => wp_logout_url()));
        }

        if (empty(get_user_meta($user->ID, 'loginlockdown_2fa_key', true)) || get_user_meta($user->ID, 'loginlockdown_2fa_key', true) != get_user_meta($user->ID, 'loginlockdown_2fa_key_check', true)) {
            wp_die('<b>Check your email</b> to confirm the log in.', '2FA Authentication', array('link_text' => 'Log out and log in again to resend the email', 'link_url' => wp_logout_url()));
        }
    }

    static function wp_logout($user_id)
    {
        $options = LoginLockdown_Setup::get_options();
        if ($options['2fa_email'] !== '1') {
            return true;
        }

        $user = get_user_by('ID', $user_id);

        update_user_meta($user->ID, 'loginlockdown_2fa_key', '');
        update_user_meta($user->ID, 'loginlockdown_2fa_key_check', '');

        return true;
    }

    static function wp_login($user_login, $user)
    {
        $options = LoginLockdown_Setup::get_options();
        if ($options['2fa_email'] !== '1') {
            return true;
        }

        $key = rand(10000, 99999);

        update_user_meta($user->ID, 'loginlockdown_2fa_key', $key);
        update_user_meta($user->ID, 'loginlockdown_2fa_key_check', '');

        $msg = 'Please click the link below in order to confirm log in to ' . get_bloginfo('name') . PHP_EOL;
        $msg .= 'If you did not login recently somebody might have your username and password. Change the password immediately.' . PHP_EOL;
        $msg .= 'Request originated from: ' . $_SERVER['REMOTE_ADDR'] . ' for user ' . $user->user_login . PHP_EOL . PHP_EOL;
        $msg .= trailingslashit(get_bloginfo('url')) . '?loginlockdown_2fa_confirm=1&key=' . $key . '&key_hash=' . md5('elephant' . $key) . '&redirect_to=' . @$_POST['redirect_to'] . '&rand=' . rand(100, 999);

        wp_mail($user->user_email, '2FA login confirmation for ' . get_bloginfo('name'), $msg);

        return true;
    }


    static function wp_authenticate_username_password($user, $username, $password)
    {
        if (is_a($user, 'WP_User')) {
            return $user;
        }

        $options = LoginLockdown_Setup::get_options();

        $block_mode = $options['country_blocking_mode'];
        $blocked_countries = $options['country_blocking_countries'];

        $user_country = LoginLockdown_Utility::country_name_to_code(LoginLockdown_Utility::getUserCountry());

        $whitelisted = false;
        $user_ip = LoginLockdown_Utility::getUserIP();
        if (in_array($user_ip, $options['whitelist']) || self::isCloudWhitelisted()) {
            $whitelisted = true;
        }

        //Check website global cloud lock
        if (!$whitelisted && self::isCloudBlacklisted()) {
            self::lockdown_screen($options['block_message_cloud']);
        }

        if (!$whitelisted && $block_mode == 'blacklist' && ($options['block_undetermined_countries'] == 1 && $user_country == 'other' || (is_array($blocked_countries) && $user_country != 'other' && in_array($user_country, $blocked_countries)))) {
            echo self::lockdown_screen($options['block_message_country']);
            return new WP_Error('lockdown_location_blocked', "<strong>ERROR</strong>: We're sorry, but access from your location is not allowed.");
        }

        if (!$whitelisted && $block_mode == 'whitelist' && ($options['block_undetermined_countries'] == 1 && $user_country == 'other' || (is_array($blocked_countries) && $user_country != 'other' && !in_array($user_country, $blocked_countries)))) {
            echo self::lockdown_screen($options['block_message_country']);
            return new WP_Error('lockdown_location_blocked', "<strong>ERROR</strong>: We're sorry, but access from your location is not allowed.");
        }

        if (!$whitelisted && self::isLockedDown()) {
            echo self::lockdown_screen($options['block_message']);
            return new WP_Error('lockdown_fail_count', __("<strong>ERROR</strong>: We're sorry, but this IP has been blocked due to too many recent failed login attempts.<br /><br />Please try again later.", 'login-lockdown'));
        }

        if (!$whitelisted && (self::check_bot() || ($options['honeypot'] && !empty($_POST['ll_user_id'])))) {
            self::lockDown($username, 'Bot');
            return new WP_Error('lockdown_bot', __("<strong>ERROR</strong>: We're sorry, but this IP has been blocked because it appears to be a bot.<br /><br />Please try again later.", 'login-lockdown'));
        }

        if (!$username) {
            return $user;
        }

        if (self::is_rest_request()) {
            return $user;
        }

        $captcha = self::handle_captcha();
        if (is_wp_error($captcha)) {
            if ($options['max_login_retries'] <= self::countFails($username) && self::countFails($username) > 0) {
                self::lockDown($username, 'Too many captcha fails');
            }
            return $captcha;
        }

        $userdata = get_user_by('login', $username);
        if (false === $userdata) {
            $userdata = get_user_by('email', $username);
        }

        if (!$whitelisted && ($options['max_login_retries'] <= self::countFails($username) || (strlen($username) > 0 && $userdata === false && $options['instant_block_nonusers'] == '1' && self::countFails($username) > 0))) {
            if ($options['max_login_retries'] <= self::countFails($username) && self::countFails($username) > 0) {
                self::lockDown($username, 'Too many fails');
            }

            if (strlen($username) > 0 && $userdata === false && $options['instant_block_nonusers'] == '1' && self::countFails($username) > 0) {
                self::lockDown($username, 'Invalid Username');
            }

            return new WP_Error('lockdown_fail_count', __("<strong>ERROR</strong>: We're sorry, but this IP has been blocked due to too many recent failed login attempts.<br /><br />Please try again later.", 'login-lockdown'));
        }

        if (empty($username) || empty($password)) {
            $error = new WP_Error();

            if (empty($username))
                $error->add('empty_username', __('<strong>ERROR</strong>: The username field is empty.', 'login-lockdown'));

            if (empty($password))
                $error->add('empty_password', __('<strong>ERROR</strong>: The password field is empty.', 'login-lockdown'));

            return $error;
        }

        if ($userdata === false) {
            if ($options['instant_block_nonusers'] == '1') {
                self::lockDown($username, 'Failed attempt with invalid username: ' . $username);
                return new WP_Error('incorrect_password', __("<strong>ERROR</strong>: We're sorry, but this IP has been blocked due to too many recent failed login attempts.<br /><br />Please try again later.", 'login-lockdown'));
            }

            return new WP_Error('invalid_username', sprintf(__('<strong>ERROR</strong>: Invalid username. <a href="%s" title="Password Lost and Found">Lost your password</a>?', 'login-lockdown'), site_url('wp-login.php?action=lostpassword', 'login')));
        }

        $userdata = apply_filters('wp_authenticate_user', $userdata, $password);
        if (is_wp_error($userdata)) {
            return $userdata;
        }

        if (!wp_check_password($password, $userdata->user_pass, $userdata->ID)) {
            return new WP_Error('incorrect_password', sprintf(__('<strong>ERROR</strong>: Incorrect password. <a href="%s" title="Password Lost and Found">Lost your password</a>?', 'login-lockdown'), site_url('wp-login.php?action=lostpassword', 'login')));
        }

        $user =  new WP_User($userdata->ID);
        return $user;
    }

    static function check_bot()
    {
        $user_agent = LoginLockdown_Utility::parse_user_agent_array();
        if ($user_agent['device'] == 'bot') {
            return true;
        } else {
            return false;
        }
    }

    static function handle_captcha()
    {
        $options = LoginLockdown_Setup::get_options();

        if ($options['captcha'] == 'recaptchav2') {
            if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
                return new WP_Error('lockdown_recaptchav2_not_submitted', __("<strong>ERROR</strong>: reCAPTHCA verification failed.<br /><br />Please try again.", 'login-lockdown'));
            } else {
                $secret = $options['captcha_secret_key'];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $_POST['g-recaptcha-response']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);
                $response = json_decode($response);

                if ($response->success) {
                    return true;
                } else {
                    return new WP_Error('lockdown_recaptchav2_failed', __("<strong>ERROR</strong>: reCAPTHCA verification failed.<br /><br />Please try again.", 'login-lockdown'));
                }
            }
        } else if ($options['captcha'] == 'recaptchav3') {
            if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
                return new WP_Error('lockdown_recaptchav3_not_submitted', __("<strong>ERROR</strong>: reCAPTHCA verification failed.<br /><br />Please try again.", 'login-lockdown'));
            } else {
                $secret = $options['captcha_secret_key'];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $_POST['g-recaptcha-response']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);
                $response = json_decode($response);

                if ($response->success) {
                    return true;
                } else {
                    return new WP_Error('lockdown_recaptchav3_failed', __("<strong>ERROR</strong>: reCAPTHCA verification failed.<br /><br />Please try again.", 'login-lockdown'));
                }
            }
        } else if ($options['captcha'] == 'builtin') {
            if (isset($_POST['loginlockdown_captcha']) && $_POST['loginlockdown_captcha'] === $_COOKIE['loginlockdown_captcha']) {
                return true;
            } else {
                return new WP_Error('lockdown_builtin_captcha_failed', __("<strong>ERROR</strong>: captcha verification failed.<br /><br />Please try again.", 'login-lockdown'));
            }
        }

        if ($options['captcha'] == 'hcaptcha') {
            $data = array(
                'secret' => $options['captcha_secret_key'],
                'response' => $_POST['h-captcha-response']
            );
            $verify = curl_init();
            curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
            curl_setopt($verify, CURLOPT_POST, true);
            curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($verify);
            $responseData = json_decode($response);
            if ($responseData->success) {
                return true;
            } else {
                return new WP_Error('lockdown_hcaptcha_failed', __("<strong>ERROR</strong>: hCaptcha verification failed.<br /><br />Please try again.", 'login-lockdown'));
            }
        }

        return true;
    }

    static function loginFailed($username, $error)
    {
        self::incrementFails($username, $error->get_error_code());
    }


    static function handle_temporary_links()
    {
        $options = LoginLockdown_Setup::get_options();
        if (!empty($_GET['loginlockdown_access'])) {
            $temporary_link_id = sanitize_key($_GET['loginlockdown_access']);
            $loginlockdown_temporary_links = LoginLockdown_AJAX::get_temp_links();
            if (array_key_exists($temporary_link_id, $loginlockdown_temporary_links)) {
                $user_id = $loginlockdown_temporary_links[$temporary_link_id]['user_id'];

                if (time() > $loginlockdown_temporary_links[$temporary_link_id]['expires'] || $loginlockdown_temporary_links[$temporary_link_id]['used'] >= $loginlockdown_temporary_links[$temporary_link_id]['uses']) {
                    return;
                }

                $user_temporary_links = get_user_meta($user_id, 'loginlockdown_temporary_links', true);
                $user_temporary_links[$temporary_link_id]['used']++;
                update_user_meta($user_id, 'loginlockdown_temporary_links', $user_temporary_links);

                $user = get_user_by('ID', $user_id);
                if ($user) {
                    wp_set_current_user($user_id, $user->user_login);
                    wp_set_auth_cookie($user_id);

                    if ($options['2fa_email'] == '1') {
                        $key = rand(10000, 99999);
                        update_user_meta($user->ID, 'loginlockdown_2fa_key', $key);
                        update_user_meta($user->ID, 'loginlockdown_2fa_key_check', $key);
                    }

                    do_action('wp_login', $user->user_login, wp_get_current_user());
                    wp_safe_redirect(home_url('/wp-admin/'));
                    exit();
                }
            }
        }
    }

    static function login_error_message($error)
    {
        $options = LoginLockdown_Setup::get_options();

        if ($options['mask_login_errors'] == 1) {
            $error = 'Login Failed';
        }
        return $error;
    }

    static function login_form_fields()
    {
        $options = LoginLockdown_Setup::get_options();
        $showcreditlink = $options['show_credit_link'];
        if ($options['honeypot']) {
            echo '<style>input[name="ll_user_id"]{display:none;}</style>';
        }
        if ($options['captcha'] == 'recaptchav2') {
            echo '<div class="g-recaptcha" style="transform: scale(0.9); -webkit-transform: scale(0.9); transform-origin: 0 0; -webkit-transform-origin: 0 0;" data-sitekey="' . $options['captcha_site_key'] . '"></div>';
        } else if ($options['captcha'] == 'recaptchav3') {
            echo '<script>
            function loginlockdown_captcha(){
                grecaptcha.execute("' . $options['captcha_site_key'] . '", {action: "submit"}).then(function(token) {
                    var loginform;
                    var login_lockdown_recaptcha_token_input = document.createElement("input");
                    login_lockdown_recaptcha_token_input.type = "hidden";
                    login_lockdown_recaptcha_token_input.name = "g-recaptcha-response";
                    login_lockdown_recaptcha_token_input.value = token;

                    loginform = document.getElementById("loginform");
                    if(loginform != null){
                        loginform.appendChild(login_lockdown_recaptcha_token_input);
                        return;
                    }

                    loginform = document.getElementsByClassName("woocommerce-form-login")[0];
                    if(loginform != null){
                        loginform.appendChild(login_lockdown_recaptcha_token_input);
                        return;
                    }
                });
            }</script>';
        } else if ($options['captcha'] == 'hcaptcha') {
            echo '<div class="h-captcha" style="transform: scale(0.9); -webkit-transform: scale(0.9); transform-origin: 0 0; -webkit-transform-origin: 0 0;" data-sitekey="' . $options['captcha_site_key'] . '"></div>';
        } else if ($options['captcha'] == 'builtin') {
            echo '<p><label for="loginlockdown_captcha">Are you human? Please solve: ';
            echo '<img class="loginlockdown-captcha-img" style="vertical-align: text-top;" src="' . LOGINLOCKDOWN_PLUGIN_URL . '/libs/captcha.php?loginlockdown-generate-image=true&color=' . urlencode('#FFFFFF') . '&noise=1&rnd=' . rand(0, 10000) . '" alt="Captcha" />';
            echo '<input class="input" type="text" size="3" name="loginlockdown_captcha" id="loginlockdown_captcha" />';
            echo '</label></p><br />';
        }

        if ($options['honeypot']) {
            echo '<input type="text" name="ll_user_id" value="" placeholder="Enter your user ID" />';
        }

        if ($showcreditlink != "no" && $showcreditlink != 0) {
            echo "<div id='loginlockdown-protected-by' style='display: block; clear: both; padding-top: 20px; text-align: center;''>";
            esc_html_e('Login form protected by', 'login-lockdown');
            echo " <a href='" . esc_url('https://wploginlockdown.com/') . "'>Login Lockdown</a></div>";
            echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                document.querySelector("#loginform").append(document.querySelector("#loginlockdown-protected-by"));
            });
            </script>';
        }
    }

    static function login_enqueue_scripts()
    {
        $options = LoginLockdown_Setup::get_options();

        if ($options['captcha'] == 'recaptchav2') {
            wp_enqueue_script('loginlockdown-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), self::$version, true);
        } else if ($options['captcha'] == 'recaptchav3') {
            wp_enqueue_script('loginlockdown-recaptcha', 'https://www.google.com/recaptcha/api.js?onload=loginlockdown_captcha&render=' . $options['captcha_site_key'], array(), self::$version, true);
        } else if ($options['captcha'] == 'hcaptcha') {
            wp_enqueue_script('loginlockdown-recaptcha', 'https://www.hCaptcha.com/1/api.js', array(), self::$version, true);
        }

        if ($options['design_enable']) {
            echo '<style type="text/css">';

            if (!empty($options['design_logo'])) {
                echo '#login h1 a, .login h1 a {';
                echo 'background-image: url(' . $options['design_logo'] . ');';
                echo 'background-repeat: no-repeat;';
                $background_width = false;
                $background_height = false;
                if (!empty($options['design_logo_width'])) {
                    $background_width = (int)$options['design_logo_width'];
                    $background_height = (int)$options['design_logo_width'];
                }
                if (!empty($options['design_logo_height'])) {
                    $background_height = (int)$options['design_logo_height'];
                }

                if (false !== $background_width) {
                    echo 'width: ' . $background_width . 'px;';
                    echo 'height: ' . $background_height . 'px;';
                    echo 'background-size:' . $background_width . 'px ' . $background_height . 'px;';
                }

                if (!empty($options['design_logo_margin_bottom'])) {
                    echo 'padding-bottom: ' . $options['design_logo_margin_bottom'] . 'px;';
                }
                echo '}';
            }

            if (!empty($options['design_background_color'])) {
                echo 'body.login {background-color:' . $options['design_background_color'] . '}';
            }

            if (!empty($options['design_background_image'])) {
                echo 'body.login {background-image:url(' . $options['design_background_image'] . '); background-size:cover;}';
            }

            echo 'body.login div#login form#loginform {';
            if (!empty($options['design_form_width'])) {
                echo 'width:' . $options['design_form_width'] . 'px;';
            }

            if (!empty($options['design_form_height'])) {
                echo 'height:' . $options['design_form_height'] . 'px;';
            }

            if (!empty($options['design_form_padding'])) {
                echo 'padding:' . $options['design_form_padding'] . 'px;';
            }

            if (!empty($options['design_form_border_radius'])) {
                echo 'border-radius:' . $options['design_form_border_radius'] . 'px;';
            }

            if (!is_null($options['design_form_border_width'])) {
                echo 'border-width:' . $options['design_form_border_width'] . 'px;';
            }

            if (!empty($options['design_form_border_color'])) {
                echo 'border-color:' . $options['design_form_border_color'] . ';';
            }

            if (!empty($options['design_form_background_color'])) {
                echo 'background-color:' . $options['design_form_background_color'] . ';';
            }

            if (!empty($options['design_form_background_image'])) {
                echo 'background-image:url(' . $options['design_form_background_image'] . '); background-size:cover;';
            }
            echo '}';

            echo 'body.login div#login form#loginform label {';
            if (!empty($options['design_label_font_size'])) {
                echo 'font-size:' . $options['design_label_font_size'] . 'px;';
            }

            if (!empty($options['design_label_text_color'])) {
                echo 'color:' . $options['design_label_text_color'] . ';';
            }
            echo '}';

            echo 'body.login div#login form#loginform input {';
            if (!empty($options['design_field_font_size'])) {
                echo 'font-size:' . $options['design_field_font_size'] . 'px;';
            }

            if (!empty($options['design_field_text_color'])) {
                echo 'color:' . $options['design_field_text_color'] . ';';
            }

            if (!empty($options['design_field_border_color'])) {
                echo 'border-color:' . $options['design_field_border_color'] . ';';
            }

            if (!is_null($options['design_field_border_width'])) {
                echo 'border-width:' . $options['design_field_border_width'] . 'px;';
            }

            if (!empty($options['design_field_border_radius'])) {
                echo 'border-radius:' . $options['design_field_border_radius'] . 'px;';
            }

            if (!empty($options['design_field_background_color'])) {
                echo 'background-color:' . $options['design_field_background_color'] . ';';
            }
            echo '}';

            echo 'body.login div#login form#loginform p.submit input#wp-submit {';
            if (!empty($options['design_button_font_size'])) {
                echo 'font-size:' . $options['design_button_font_size'] . 'px;';
            }

            if (!empty($options['design_button_text_color'])) {
                echo 'color:' . $options['design_button_text_color'] . ';';
            }

            if (!empty($options['design_button_border_color'])) {
                echo 'border-color:' . $options['design_button_border_color'] . ';';
            }

            if (!is_null($options['design_button_border_width'])) {
                echo 'border-width:' . $options['design_button_border_width'] . 'px;';
            }

            if (!empty($options['design_button_border_radius'])) {
                echo 'border-radius:' . $options['design_button_border_radius'] . 'px;';
            }

            if (!empty($options['design_button_background_color'])) {
                echo 'background-color:' . $options['design_button_background_color'] . ';';
            }
            echo '}';

            echo 'body.login div#login form#loginform{';
            if (!empty($options['design_text_color'])) {
                echo 'color:' . $options['design_text_color'] . ';';
            }
            echo '}';

            echo 'body.login a, body.login #nav a, body.login #backtoblog a, body.login div#login form#loginform a{';
            if (!empty($options['design_link_color'])) {
                echo 'color:' . $options['design_link_color'] . ';';
            }
            echo '}';

            echo 'body.login a:hover, body.login #nav a:hover, body.login #backtoblog a:hover, body.login div#login form#loginform a:hover{';
            if (!empty($options['design_link_hover_color'])) {
                echo 'color:' . $options['design_link_hover_color'] . ';';
            }
            echo '}';

            echo 'body.login div#login form#loginform p.submit input#wp-submit:hover {';
            if (!empty($options['design_button_hover_text_color'])) {
                echo 'color:' . $options['design_button_hover_text_color'] . ';';
            }

            if (!empty($options['design_button_hover_border_color'])) {
                echo 'border-color:' . $options['design_button_hover_border_color'] . ';';
            }

            if (!empty($options['design_button_hover_background_color'])) {
                echo 'background-color:' . $options['design_button_hover_background_color'] . ';';
            }
            echo '}';

            echo '.wp-core-ui .button .dashicons, .wp-core-ui .button-secondary .dashicons{';
            if (!empty($options['design_link_color'])) {
                echo 'color:' . $options['design_link_color'] . ';';
            }
            echo '}';

            echo '.wp-core-ui .button .dashicons:hover, .wp-core-ui .button-secondary .dashicons:hover{';
            if (!empty($options['design_link_hover_color'])) {
                echo 'color:' . $options['design_link_hover_color'] . ';';
            }
            echo '}';


            if (!empty($options['design_custom_css'])) {
                echo $options['design_custom_css'];
            }

            echo '</style>';
        }
    }

    static function get_templates()
    {
        $templates = array();

        $templates['orange'] = array(
            'design_background_color' => '#ef9b00',
            'design_background_image' => '',
            'design_logo' => 'white-loginlockdown-icon',
            'design_logo_width' => '100',
            'design_logo_height' => '100',
            'design_logo_margin_bottom' => '30',
            'design_text_color' => '#4c3d00',
            'design_link_color' => '#7c6e13',
            'design_link_hover_color' => '#896709',
            'design_form_border_color' => '#725f00',
            'design_form_border_width' => '0',
            'design_form_width' => '',
            'design_form_height' => '',
            'design_form_padding' => '20',
            'design_form_border_radius' => '4',
            'design_form_background_color' => '#f9e7ac',
            'design_form_background_image' => '',
            'design_label_font_size' => '14',
            'design_label_text_color' => '#634000',
            'design_field_font_size' => '14',
            'design_field_text_color' => '#ffffff',
            'design_field_border_color' => '#634000',
            'design_field_border_width' => '1',
            'design_field_border_radius' => '2',
            'design_field_background_color' => '#ffffff',
            'design_button_font_size' => '14',
            'design_button_text_color' => '#ffffff',
            'design_button_border_color' => '#634000',
            'design_button_border_width' => '1',
            'design_button_border_radius' => '4',
            'design_button_background_color' => '#634000',
            'design_button_hover_text_color' => '#ffffff',
            'design_button_hover_border_color' => '#8c5f00',
            'design_button_hover_background_color' => '#8c5f00',
            'design_custom_css' => ''
        );

        $templates['red'] = array(
            'design_background_color' => '#ce0000',
            'design_background_image' => '',
            'design_logo' => 'white-loginlockdown-icon',
            'design_logo_width' => '100',
            'design_logo_height' => '100',
            'design_logo_margin_bottom' => '30',
            'design_text_color' => '#300000',
            'design_link_color' => '#c91e1e',
            'design_link_hover_color' => '#d15959',
            'design_form_border_color' => '#c90000',
            'design_form_border_width' => '2',
            'design_form_width' => '',
            'design_form_height' => '',
            'design_form_padding' => '20',
            'design_form_border_radius' => '4',
            'design_form_background_color' => '#ffffff',
            'design_form_background_image' => '',
            'design_label_font_size' => '14',
            'design_label_text_color' => '#383838',
            'design_field_font_size' => '14',
            'design_field_text_color' => '#ffffff',
            'design_field_border_color' => '#d1d1d1',
            'design_field_border_width' => '1',
            'design_field_border_radius' => '2',
            'design_field_background_color' => '#ffffff',
            'design_button_font_size' => '14',
            'design_button_text_color' => '#ffffff',
            'design_button_border_color' => '#000000',
            'design_button_border_width' => '0',
            'design_button_border_radius' => '4',
            'design_button_background_color' => '#d30000',
            'design_button_hover_text_color' => '#ffffff',
            'design_button_hover_border_color' => '#ffffff',
            'design_button_hover_background_color' => '#9e0000',
            'design_custom_css' => ''
        );

        $templates['green'] = array(
            'design_background_color' => '#2c6600',
            'design_background_image' => '',
            'design_logo' => 'http://localhost/wp_lockdown/wp-content/plugins/login-lockdown/images/white-icon.png',
            'design_logo_width' => '100',
            'design_logo_height' => '100',
            'design_logo_margin_bottom' => '30',
            'design_text_color' => '#c6e500',
            'design_link_color' => '#c6e500',
            'design_link_hover_color' => '#acbf00',
            'design_form_border_color' => '#c6e500',
            'design_form_border_width' => '2',
            'design_form_width' => '',
            'design_form_height' => '',
            'design_form_padding' => '20',
            'design_form_border_radius' => '4',
            'design_form_background_color' => '#4b7c01',
            'design_form_background_image' => '',
            'design_label_font_size' => '14',
            'design_label_text_color' => '#ffffff',
            'design_field_font_size' => '14',
            'design_field_text_color' => '#ffffff',
            'design_field_border_color' => '#87d642',
            'design_field_border_width' => '1',
            'design_field_border_radius' => '2',
            'design_field_background_color' => '#3c7f02',
            'design_button_font_size' => '14',
            'design_button_text_color' => '#ffffff',
            'design_button_border_color' => '#000000',
            'design_button_border_width' => '0',
            'design_button_border_radius' => '4',
            'design_button_background_color' => '#66b500',
            'design_button_hover_text_color' => '#ffffff',
            'design_button_hover_border_color' => '#ffffff',
            'design_button_hover_background_color' => '#a6d800',
            'design_custom_css' => ''
        );

        $templates['blue'] = array(
            'design_background_color' => '#005cb2',
            'design_background_image' => '',
            'design_logo' => 'http://localhost/wp_lockdown/wp-content/plugins/login-lockdown/images/white-icon.png',
            'design_logo_width' => '100',
            'design_logo_height' => '100',
            'design_logo_margin_bottom' => '30',
            'design_text_color' => '#300000',
            'design_link_color' => '#2ca8ea',
            'design_link_hover_color' => '#005b93',
            'design_form_border_color' => '#008ed1',
            'design_form_border_width' => '2',
            'design_form_width' => '',
            'design_form_height' => '',
            'design_form_padding' => '20',
            'design_form_border_radius' => '4',
            'design_form_background_color' => '#ffffff',
            'design_form_background_image' => '',
            'design_label_font_size' => '14',
            'design_label_text_color' => '#383838',
            'design_field_font_size' => '14',
            'design_field_text_color' => '#ffffff',
            'design_field_border_color' => '#d1d1d1',
            'design_field_border_width' => '1',
            'design_field_border_radius' => '2',
            'design_field_background_color' => '#ffffff',
            'design_button_font_size' => '14',
            'design_button_text_color' => '#ffffff',
            'design_button_border_color' => '#000000',
            'design_button_border_width' => '0',
            'design_button_border_radius' => '4',
            'design_button_background_color' => '#0084cc',
            'design_button_hover_text_color' => '#ffffff',
            'design_button_hover_border_color' => '#ffffff',
            'design_button_hover_background_color' => '#005796',
            'design_custom_css' => ''
        );

        $templates['gray'] = array(
            'design_background_color' => '#353535',
            'design_background_image' => '',
            'design_logo' => 'http://localhost/wp_lockdown/wp-content/plugins/login-lockdown/images/white-icon.png',
            'design_logo_width' => '100',
            'design_logo_height' => '100',
            'design_logo_margin_bottom' => '30',
            'design_text_color' => '#300000',
            'design_link_color' => '#06a8e8',
            'design_link_hover_color' => '#005b93',
            'design_form_border_color' => '#474747',
            'design_form_border_width' => '2',
            'design_form_width' => '',
            'design_form_height' => '',
            'design_form_padding' => '20',
            'design_form_border_radius' => '4',
            'design_form_background_color' => '#ffffff',
            'design_form_background_image' => '',
            'design_label_font_size' => '14',
            'design_label_text_color' => '#383838',
            'design_field_font_size' => '14',
            'design_field_text_color' => '#ffffff',
            'design_field_border_color' => '#d1d1d1',
            'design_field_border_width' => '1',
            'design_field_border_radius' => '2',
            'design_field_background_color' => '#ffffff',
            'design_button_font_size' => '14',
            'design_button_text_color' => '#ffffff',
            'design_button_border_color' => '#000000',
            'design_button_border_width' => '0',
            'design_button_border_radius' => '4',
            'design_button_background_color' => '#595959',
            'design_button_hover_text_color' => '#ffffff',
            'design_button_hover_border_color' => '#ffffff',
            'design_button_hover_background_color' => '#878787',
            'design_custom_css' => ''
        );

        return $templates;
    }

    static function install_template()
    {
        check_admin_referer('loginlockdown_install_template');
        $options = LoginLockdown_Setup::get_options();

        $template = $_GET['template'];
        $templates = self::get_templates();

        if (array_key_exists($template, $templates)) {
            $options = array_merge($options, $templates[$template]);
            if ($options['design_logo'] == 'white-loginlockdown-icon') {
                $options['design_logo'] = LOGINLOCKDOWN_PLUGIN_URL . 'images/white-icon.png';
            }
            update_option(LOGINLOCKDOWN_OPTIONS_KEY, $options);
            LoginLockdown_Admin::add_notice('template_activated', __('Template activated.', 'login-lockdown'), 'success', true);
        } else {
            LoginLockdown_Admin::add_notice('template_not_found', __('Unknown template ID.', 'login-lockdown'), 'error', true);
        }

        if (!empty($_GET['redirect'])) {
            wp_safe_redirect($_GET['redirect']);
        }
    }

    static function login_print_scripts()
    {
        $options = LoginLockdown_Setup::get_options();

        if ($options['captcha'] == 'recaptchav2') {
            echo "<script src='https://www.google.com/recaptcha/api.js?ver=" . self::$version . "' id='loginlockdown-recaptcha-js'></script>";
        } else if ($options['captcha'] == 'recaptchav3') {
            echo "<script src='https://www.google.com/recaptcha/api.js?onload=loginlockdown_captcha&render=" . $options['captcha_site_key'] . "&ver=" . self::$version . "' id='loginlockdown-recaptcha-js'></script>";
        } else if ($options['captcha'] == 'hcaptcha') {
            echo "<script src='https://www.hCaptcha.com/1/api.js?ver=" . self::$version . "' id='loginlockdown-recaptcha-js'></script>";
        }
    }

    static function lockdown_screen($block_message = false)
    {
        $main_color = '#29b99a';
        $secondary_color = '#3fccb0';

        if (LoginLockdown_Admin::get_rebranding() !== false) {
            $brand_color = LoginLockdown_Admin::get_rebranding('color');
            if (!empty($brand_color)) {
                $main_color = $brand_color;
                $secondary_color = LoginLockdown_Admin::color_luminance($brand_color, 0.2);
            }
        }

        echo '<style>
            @import url(\'https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,400;1,500;1,700&display=swap\');

            #loginlockdown_lockdown_screen_wrapper{
                font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
                width:100%;
                height:100%;
                position:fixed;
                top:0;
                left:0;
                z-index: 999999;
                font-size: 14px;
                color: #333;
                line-height: 1.4;
                background-image: linear-gradient(45deg, ' . $main_color . ' 25%, ' . $secondary_color . ' 25%, ' . $secondary_color . ' 50%, ' . $main_color . ' 50%, ' . $main_color . ' 75%, ' . $secondary_color . ' 75%, ' . $secondary_color . ' 100%);
                background-size: 28.28px 28.28px;
            }

            #loginlockdown_lockdown_screen_wrapper form{
                max-width: 300px;
                top:50%;
                left:50%;
                margin-top:-200px;
                margin-left:-200px;
                border: none;
                background: #ffffffde;
                box-shadow: 0 1px 3px rgb(0 0 0 / 4%);
                position: fixed;
                text-align:center;
                background: #fffffff2;
                padding: 20px;
                -webkit-box-shadow: 5px 5px 0px 1px rgba(0,0,0,0.22);
                box-shadow: 5px 5px 0px 1px rgba(0,0,0,0.22);
            }

            #loginlockdown_lockdown_screen_wrapper p{
                padding: 10px;
                line-height:1.5;
            }

            #loginlockdown_lockdown_screen_wrapper p.error{
                background: #f11c1c;
                color: #FFF;
                font-weight: 500;
            }

            #loginlockdown_lockdown_screen_wrapper form input[type="text"]{
                padding: 4px 10px;
                border-radius: 2px;
                border: 1px solid #c3c4c7;
                font-size: 16px;
                line-height: 1.33333333;
                margin: 0 6px 16px 0;
                min-height: 40px;
                max-height: none;
                width: 100%;
            }

            #loginlockdown_lockdown_screen_wrapper form input[type="submit"]{
                padding: 10px 10px;
                border-radius: 2px;
                border: none;
                font-size: 16px;
                background: ' . $main_color . ';
                color: #FFF;
                cursor: pointer;
                width: 100%;
            }

            #loginlockdown_lockdown_screen_wrapper form input[type="submit"]:hover{
                background: ' . $secondary_color . ';
            }
        </style>

        <script>
        document.title = "' . get_bloginfo('name') . '";
        </script>';
        echo '<div id="loginlockdown_lockdown_screen_wrapper">';

        echo '<form method="POST">';

        if (isset($_POST['loginlockdown_recovery_submit']) && wp_verify_nonce($_POST['loginlockdown_recovery_nonce'], 'loginlockdown_recovery')) {
            if (!filter_var($_POST['loginlockdown_recovery_email'], FILTER_VALIDATE_EMAIL)) {
                $display_message = '<p class="error">Invalid email address.</p>';
            } else {
                $user = get_user_by('email', sanitize_text_field($_POST['loginlockdown_recovery_email']));
                if (user_can($user, 'administrator')) {
                    $unblock_key = md5(time() . rand(10000, 9999));
                    $unblock_attempts = get_transient('loginlockdown_unlock_count_' . $user->ID);
                    if (!$unblock_attempts) {
                        $unblock_attempts = 0;
                    }

                    $unblock_attempts++;
                    set_transient('loginlockdown_unlock_count_' . $user->ID, $unblock_attempts, HOUR_IN_SECONDS);

                    if ($unblock_attempts <= 3) {
                        set_transient('loginlockdown_unlock_' . $unblock_key, $unblock_key, HOUR_IN_SECONDS);

                        $unblock_url = add_query_arg(array('loginlockdown_unblock' => $unblock_key), wp_login_url());

                        $subject  = 'Login Lockdown unblock instructions for ' . site_url();
                        $message  = '<p>The IP ' . LoginLockdown_Utility::getUserIP() . ' has been locked down and someone submitted an unblock request using your email address <strong>' . $_POST['loginlockdown_recovery_email'] . '</strong></p>';
                        $message .= '<p>If this was you, and you have locked yourself out please click <a target="_blank" href="' . $unblock_url . '">this link</a> which is valid for 1 hour.</p>';
                        $message .= '<p>Please note that for security reasons, this will only unblock the IP of the person opening the link, not the IP of the person who submitted the unblock request. To unblock someone else please do so on the <a href="' . admin_url('options-general.php?page=loginlockdown#loginlockdown_activity') . '">Login Lockdown Activity Page</p>';

                        add_filter('wp_mail_content_type', function () {
                            return "text/html";
                        });

                        wp_mail($user->user_email, $subject, $message);
                    }
                } else {
                    //If no admin using the submitted email exists, ignore silently
                }

                if (isset($unblock_attempts) && $unblock_attempts > 3) {
                    $display_message = '<p class="error">You have already attempted to unblock yourself recently, please wait 1 hour before trying again.</p>';
                } else {
                    $display_message = '<p>If an administrator having the email address <strong>' . $_POST['loginlockdown_recovery_email'] . '</strong> exists, an email has been sent with instructions to regain access.</p>';
                }
            }
        }

        if (LoginLockdown_Admin::get_rebranding('logo_url') !== false) {
            echo '<img src="' . LoginLockdown_Admin::get_rebranding('logo_url') . '" />';
        } else {
            echo '<img src="' . LOGINLOCKDOWN_PLUGIN_URL . '/images/loginlockdown-logo.png" alt="Login Lockdown PRO" height="60" title="Login Lockdown PRO">';
        }
        echo '<br />';
        echo '<br />';
        if ($block_message !== false) {
            echo '<p class="error">' . $block_message . '</p>';
        } else {
            echo '<p class="error">We\'re sorry, but your IP has been blocked due to too many recent failed login attempts.</p>';
        }
        if (!empty($display_message)) {
            echo $display_message;
        }
        echo '<p>If you are a user with administrative privilege please enter your email below to receive instructions on how to unblock yourself.</p>';
        echo '<input type="text" name="loginlockdown_recovery_email" value="" placeholder="" />';
        echo '<input type="submit" name="loginlockdown_recovery_submit" value="Send unblock email" placeholder="" />';
        wp_nonce_field('loginlockdown_recovery', 'loginlockdown_recovery_nonce');


        echo '</form>';
        echo '</div>';

        exit();
    }

    static function handle_unblock()
    {
        global $wpdb;
        $options = LoginLockdown_Setup::get_options();
        if (isset($_GET['loginlockdown_unblock']) && $options['global_unblock_key'] === $_GET['loginlockdown_unblock']) {
            $user_ip = LoginLockdown_Utility::getUserIP();
            if (!in_array($user_ip, $options['whitelist'])) {
                $options['whitelist'][] = LoginLockdown_Utility::getUserIP();
            }
            update_option(LOGINLOCKDOWN_OPTIONS_KEY, $options);
        }

        if (isset($_GET['loginlockdown_unblock']) && strlen($_GET['loginlockdown_unblock']) == 32) {
            $unblock_key = sanitize_key($_GET['loginlockdown_unblock']);
            $unblock_transient = get_transient('loginlockdown_unlock_' . $unblock_key);
            if ($unblock_transient == $unblock_key) {
                $user_ip = LoginLockdown_Utility::getUserIP();
                $wpdb->delete(
                    $wpdb->lockdown_lockdowns,
                    array(
                        'lockdown_IP' => $user_ip
                    )
                );

                if (!in_array($user_ip, $options['whitelist'])) {
                    $options['whitelist'][] = LoginLockdown_Utility::getUserIP();
                }

                update_option(LOGINLOCKDOWN_OPTIONS_KEY, $options);
            }
        }
    }

    static function handle_global_block()
    {
        $options = LoginLockdown_Setup::get_options();

        //If user is on local or cloud whitelist, don't check anything else
        $user_ip = LoginLockdown_Utility::getUserIP();
        if (in_array($user_ip, $options['whitelist']) || self::isCloudWhitelisted()) {
            return false;
        }

        //Check website lock
        if ($options['global_block'] == '1' && self::isLockedDown()) {
            self::lockdown_screen($options['block_message']);
        }

        //Check website global cloud lock
        if ($options['cloud_global_block'] == '1' && self::isCloudBlacklisted()) {
            self::lockdown_screen($options['block_message_cloud']);
        }

        //Check country lock
        $block_mode = $options['country_blocking_mode'];
        $blocked_countries = $options['country_blocking_countries'];
        if (!is_array($blocked_countries)) {
            $blocked_countries = array();
        }

        $user_country = LoginLockdown_Utility::country_name_to_code(LoginLockdown_Utility::getUserCountry());
        if ($options['country_global_block'] == 1 && $block_mode == 'blacklist' && ($options['block_undetermined_countries'] == 1 && $user_country == 'other' || $user_country != 'other' && in_array($user_country, $blocked_countries))) {
            self::lockdown_screen($options['block_message_country']);
        }

        if ($options['country_global_block'] == 1 && $block_mode == 'whitelist' && ($options['block_undetermined_countries'] == 1 && $user_country == 'other' || $user_country != 'other' && !in_array($user_country, $blocked_countries))) {
            self::lockdown_screen($options['block_message_country']);
        }
    }

    static function bruteforce_login()
    {
        $return           = array();
        $max_users_attack = 50;
        $passwords        = file(LOGINLOCKDOWN_PLUGIN_DIR . 'misc/brute-force-dictionary.txt', FILE_IGNORE_NEW_LINES);

        $bad_usernames = array();

        $users = get_users(array('role' => 'administrator'));
        if (count($users) < $max_users_attack) {
            $users = array_merge($users, get_users(array('role' => 'editor')));
        }
        if (count($users) < $max_users_attack) {
            $users = array_merge($users, get_users(array('role' => 'author')));
        }
        if (count($users) < $max_users_attack) {
            $users = array_merge($users, get_users(array('role' => 'contributor')));
        }
        if (count($users) < $max_users_attack) {
            $users = array_merge($users, get_users(array('role' => 'subscriber')));
        }

        $i = 0;
        foreach ($users as $user) {
            $i++;
            $passwords[] = $user->user_login;
            foreach ($passwords as $password) {

                if (self::try_login($user->user_login, $password)) {
                    $bad_usernames[] = $user->user_login;
                    break;
                }
            } // foreach $passwords

            if ($i > $max_users_attack) {
                break;
            }
        } // foreach $users

        if (empty($bad_usernames)) {
            $message = 'No vulnerable user accounts found';
        } else {
            $message = 'The following users have weak passwords: ' . implode(', ', $bad_usernames);
        }

        return array('pass' => empty($bad_usernames), 'message' => $message);
    }

    public static function try_login($username, $password)
    {
        $user = apply_filters('authenticate', null, $username, $password);

        if (isset($user->ID) && !empty($user->ID)) {
            return true;
        } else {
            return false;
        }
    }

    public static function clean_ip_string($ip)
    {
        $ip = trim($ip);
        return $ip;
    }

    public static function isCloudBlacklisted($ip = false)
    {
        global $loginlockdown_licensing;
        $options = LoginLockdown_Setup::get_options();
        $license = $loginlockdown_licensing->get_license();

        if (false === $ip) {
            $ip = LoginLockdown_Utility::getUserIP();
        }

        //Check cloud account blacklist
        if ($options['cloud_use_account_lists'] == 1) {
            if (is_array($license) && is_array($license['meta']) && array_key_exists('cloud_protection_blacklist', $license['meta']) && !empty($license['meta']['cloud_protection_blacklist'])) {
                $account_blacklist = explode(PHP_EOL, str_replace(',', PHP_EOL, $license['meta']['cloud_protection_blacklist']));
            } else {
                return false;
            }
        
            array_map(array('self', 'clean_ip_string'), $account_blacklist);

            if (in_array($ip, $account_blacklist)) {
                return true;
            }
        }

        $path = self::get_cloud_protection_path();

        if ($options['cloud_use_blacklist'] == 1 && file_exists($path . 'ips.txt')) {
            $ips = explode(PHP_EOL, file_get_contents($path . 'ips.txt'));
            if ($ips[0] != '#WFIPS') {
                return false;
            }

            if (in_array($ip, $ips)) {
                return true;
            }
        }

        return false;
    }

    public static function login_cookie_expiration($expire)
    {
        $options = LoginLockdown_Setup::get_options();

        if ($options['cookie_lifetime'] > 0) {
            return $options['cookie_lifetime'] * DAY_IN_SECONDS;
        }

        return $expire;
    }

    public static function isCloudWhitelisted($ip = false)
    {
        global $loginlockdown_licensing;
        $options = LoginLockdown_Setup::get_options();
        $license = $loginlockdown_licensing->get_license();

        if ($options['cloud_use_account_lists'] != 1) {
            return false;
        }

        if (is_array($license) && is_array($license['meta']) && array_key_exists('cloud_protection_whitelist', $license['meta']) && !empty($license['meta']['cloud_protection_whitelist'])) {
            $account_whitelist = explode(PHP_EOL, str_replace(',', PHP_EOL, $license['meta']['cloud_protection_whitelist']));
        } else {
            return false;
        }

        array_map(array('self', 'clean_ip_string'), $account_whitelist);

        if (false === $ip) {
            $ip = LoginLockdown_Utility::getUserIP();
        }

        if (in_array($ip, $account_whitelist)) {
            return true;
        }

        return false;
    }

    public static function get_cloud_protection_path()
    {
        $upload_dir = wp_upload_dir();

        $path = trailingslashit($upload_dir['basedir']) . '/loginlockdown/';

        if (!file_exists($path)) {
            $folder = wp_mkdir_p($path);
            if (!$folder) {
                return new WP_Error(1, 'Unable to create ' . $path . ' folder.');
            }

            $htaccess_content = 'Options -Indexes' . PHP_EOL . 'order deny,allow' . PHP_EOL . 'deny from all';
            $htaccess_file = @fopen($path . '.htaccess', 'w');
            if ($htaccess_file) {
                fputs($htaccess_file, $htaccess_content);
                fclose($htaccess_file);
            }
        }

        return $path;
    }

    public static function sync_cloud_protection()
    {
        global $loginlockdown_licensing;
        $license = $loginlockdown_licensing->get_license();
        $path = self::get_cloud_protection_path();

        if (is_array($license['meta']) && isset($license['meta']['cloud_protection_url'])) {
            file_put_contents($path . 'ips.zip', fopen($license['meta']['cloud_protection_url'], 'r'));

            $import_zip = new ZipArchive();
            $zip_open = $import_zip->open($path . 'ips.zip');
            if ($zip_open === true) {
                try {
                    $import_zip->extractTo($path);
                } catch (Exception $e) {
                    unlink($path . 'ips.zip');
                    return new WP_Error(1, 'IPs ZIP file not valid');
                }
            } else {
                if (file_exists($path . 'ips.zip')) {
                    unlink($path . 'ips.zip');
                }
                return new WP_Error(1, 'IPs ZIP file not valid');
            }

            $import_zip->close();

            if (!file_exists($path . 'ips.txt')) {
                return new WP_Error(1, 'IPs file not found');
            }

            $ips = explode(PHP_EOL, file_get_contents($path . 'ips.txt'));
            if ($ips[0] != '#WFIPS') {
                return new WP_Error(1, 'IPs file invalid');
            }

            $ips_clean = array();
            foreach ($ips as $ip) {
                if (strpos($ip, '#') !== false) {
                    continue;
                }

                $ip_row = preg_split('/\s+/', $ip);
                $ips_clean[] = trim($ip_row[0]);
            }


            file_put_contents($path . 'ips.txt', implode(PHP_EOL, $ips_clean));

            if (file_exists($path . 'ips.zip')) {
                unlink($path . 'ips.zip');
            }

            return true;
        }
    }

    static function wp_loaded()
    {
        global $pagenow;

        $options = LoginLockdown_Setup::get_options();
        $request = parse_url(rawurldecode($_SERVER['REQUEST_URI']));
        if (!(isset($_GET['action']) && $_GET['action'] === 'postpass' && isset($_POST['post_password']))) {

            if (is_admin() && !is_user_logged_in() && !defined('WP_CLI') && !defined('DOING_AJAX') && !defined('DOING_CRON') && $pagenow !== 'admin-post.php' && $request['path'] !== '/wp-admin/options.php') {
                wp_safe_redirect(self::new_redirect_url());
                die();
            }

            if (!is_user_logged_in() && isset($_GET['wc-ajax']) && $pagenow === 'profile.php') {
                wp_safe_redirect(self::new_redirect_url());
                die();
            }

            if (!is_user_logged_in() && isset($request['path']) && $request['path'] === '/wp-admin/options.php') {
                header('Location: ' . self::new_redirect_url());
                die();
            }

            if ($pagenow === 'wp-login.php' && isset($request['path']) && $request['path'] !== self::trailingslashit($request['path']) && get_option('permalink_structure')) {
                wp_safe_redirect(self::trailingslashit($options['login_url']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
                die();
            } elseif (self::$wp_login_php) {
                if (($referer = wp_get_referer()) && strpos($referer, 'wp-activate.php') !== false && ($referer = parse_url($referer)) && !empty($referer['query'])) {
                    parse_str($referer['query'], $referer);
                    @require_once WPINC . '/ms-functions.php';
                    if (!empty($referer['key']) && ($result = wpmu_activate_signup($referer['key'])) && is_wp_error($result) && ($result->get_error_code() === 'already_active' || $result->get_error_code() === 'blog_taken')) {
                        wp_safe_redirect($options['login_url'] . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
                        die();
                    }
                } else {
                    header('Location: ' . self::new_redirect_url());
                    die();
                }
                self::wp_template_loader();
            } elseif ($pagenow === 'wp-login.php') {
                global $error, $interim_login, $action, $user_login;
                $requested_redirect_to = '';
                if (isset($_REQUEST['redirect_to'])) {
                    $requested_redirect_to = $_REQUEST['redirect_to'];
                }

                if (is_user_logged_in()) {
                    $user = wp_get_current_user();
                    if (!isset($_REQUEST['action'])) {
                        wp_safe_redirect($requested_redirect_to);
                        die();
                    }
                }
                @require_once ABSPATH . 'wp-login.php';
                die();
            }
        }
    }

    static function wp_template_loader()
    {
        global $pagenow;
        $pagenow = 'index.php';

        if (!defined('WP_USE_THEMES')) {
            define('WP_USE_THEMES', true);
        }

        wp();

        require_once(ABSPATH . WPINC . '/template-loader.php');
        die();
    }

    static function login_compatibility_check()
    {
        if (!function_exists('is_plugin_active') || !function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        if (is_plugin_active('rename-wp-login/rename-wp-login.php')) {
            return 'Rename wp-login.php';
        }

        if (is_plugin_active('lws-hide-login/lws-hide-login.php')) {
            return 'LWS Hide Login';
        }

        if (is_plugin_active('wps-hide-login/wps-hide-login.php')) {
            return 'WPS Hide Login';
        }

        if (is_plugin_active('change-wp-admin-login/change-wp-admin-login.php')) {
            return 'Change wp-admin login';
        }

        if (is_plugin_active('hide-login-page/hide-login-page.php')) {
            return 'Webcraftic Hide login page';
        }

        if (is_plugin_active('easy-hide-login/wp-hide-login.php')) {
            return 'Easy Hide Login';
        }

        if (is_plugin_active('hide-my-wp/index.php')) {
            return 'Hide My WP Ghost';
        }

        return true;
    }

    static function site_url($url, $path, $scheme, $blog_id)
    {
        $redirect_to = self::filter_wp_login_php($url, $scheme);
        return $redirect_to;

        //return self::filter_wp_login_php($url, $scheme);
    }

    static function wp_redirect($location, $status)
    {
        if (strpos($location, 'https://wordpress.com/wp-login.php') !== false) {
            return $location;
        }

        $redirect_to = self::filter_wp_login_php($location);
        return $redirect_to;
    }

    public function login_url_welcome_email($value)
    {
        $options = LoginLockdown_Setup::get_options();
        return $value = str_replace('wp-login.php', trailingslashit(get_site_option('whl_page', 'login')), $value);
    }

    static function filter_wp_login_php($url, $scheme = null)
    {
        if (strpos($url, 'wp-login.php?action=postpass') !== false) {
            return $url;
        }

        if (strpos($url, 'wp-login.php') !== false && strpos(wp_get_referer(), 'wp-login.php') === false) {
            if (is_ssl()) {
                $scheme = 'https';
            }
            $args = explode('?', $url);
            if (isset($args[1])) {
                parse_str($args[1], $args);
                if (isset($args['login'])) {
                    $args['login'] = rawurlencode($args['login']);
                }
                $url = add_query_arg($args, self::new_login_url($scheme));
            } else {
                $url = self::new_login_url($scheme);
            }
        }
        return $url;
    }

    static function new_login_url($scheme = null)
    {
        $options = LoginLockdown_Setup::get_options();

        $url = apply_filters('wps_hide_login_home_url', home_url('/', $scheme));

        if (get_option('permalink_structure')) {
            return self::trailingslashit($url . $options['login_url']);
        } else {
            return $url . '?' . $options['login_url'];
        }
    }

    static function new_redirect_url($scheme = null)
    {
        $options = LoginLockdown_Setup::get_options();

        if (get_option('permalink_structure')) {
            return self::trailingslashit(home_url('/', $scheme) . $options['login_redirect_url']);
        } else {
            return home_url('/', $scheme) . '?' . $options['login_redirect_url'];
        }
    }

    static function login_url($login_url, $redirect, $force_reauth)
    {
        if (is_404()) {
            return '#';
        }

        if ($force_reauth === false) {
            return $login_url;
        }

        if (empty($redirect)) {
            return $login_url;
        }

        $redirect = explode('?', $redirect);

        if ($redirect[0] === admin_url('options.php')) {
            $login_url = admin_url();
        }

        return $login_url;
    }

    static function override_login_urls()
    {
        global $pagenow;

        $options = LoginLockdown_Setup::get_options();

        if(self::login_compatibility_check() !== true){
            return false;
        }

        if (empty($options['login_url'])) {
            return false;
        }

        if (!is_multisite() && (strpos(rawurldecode($_SERVER['REQUEST_URI']), 'wp-signup') !== false || strpos(rawurldecode($_SERVER['REQUEST_URI']), 'wp-activate') !== false) && apply_filters('wps_hide_login_signup_enable', false) === false) {
            wp_die(__('This feature is not enabled.', 'login-lockdown'));
        }

        $request = parse_url(rawurldecode($_SERVER['REQUEST_URI']));

        if ((strpos(rawurldecode($_SERVER['REQUEST_URI']), 'wp-login.php') !== false || (isset($request['path']) && untrailingslashit($request['path']) === site_url('wp-login', 'relative'))) && !is_admin()) {
            self::$wp_login_php = true;
            $_SERVER['REQUEST_URI'] = self::trailingslashit('/' . str_repeat('-/', 10));
            $pagenow = 'index.php';
        } elseif ((isset($request['path']) && untrailingslashit($request['path']) === home_url($options['login_url'], 'relative')) || (!get_option('permalink_structure') && isset($_GET[$options['login_url']]) && empty($_GET[$options['login_url']]))) {
            $_SERVER['SCRIPT_NAME'] = $options['login_url'];
            $pagenow = 'wp-login.php';
        } elseif ((strpos(rawurldecode($_SERVER['REQUEST_URI']), 'wp-register.php') !== false || (isset($request['path']) && untrailingslashit($request['path']) === site_url('wp-register', 'relative'))) && !is_admin()) {
            self::$wp_login_php = true;
            $_SERVER['REQUEST_URI'] = self::trailingslashit('/' . str_repeat('-/', 10));
            $pagenow = 'index.php';
        }
    }

    public function user_request_action_email_content($email_text, $email_data)
    {
        $options = LoginLockdown_Setup::get_options();
        $email_text = str_replace('###CONFIRM_URL###', esc_url_raw(str_replace(untrailingslashit($options['login_url']) . '/', 'wp-login.php', $email_data['confirm_url'])), $email_text);
        return $email_text;
    }

    public function site_status_tests($tests)
    {
        unset($tests['async']['loopback_requests']);
        return $tests;
    }

    static function use_trailing_slashes()
    {
        return ('/' === substr(get_option('permalink_structure'), -1, 1));
    }

    static function trailingslashit($string)
    {
        return self::use_trailing_slashes() ? trailingslashit($string) : untrailingslashit($string);
    }

    public static function pretty_fail_errors($error_code)
    {
        switch ($error_code) {
            case 'lockdown_location_blocked':
                return 'Blocked Location';
                break;
            case 'lockdown_fail_count':
                return 'User exceeded maximum number of fails';
                break;
            case 'lockdown_bot':
                return 'Bot';
                break;
            case 'empty_username':
                return 'Empty Username';
                break;
            case 'empty_password':
                return 'Empty Password';
                break;
            case 'incorrect_password':
                return 'Incorrect Password';
                break;
            case 'invalid_username':
                return 'Invalid Username';
                break;
            case 'lockdown_recaptchav2_not_submitted':
                return 'reCAPTCHA v2 not submitted';
                break;
            case 'lockdown_recaptchav3_not_submitted':
                return 'reCAPTCHA v3 not submitted';
                break;
            case 'lockdown_recaptchav2_failed':
                return 'reCAPTCHA v2 failed verification';
                break;
            case 'lockdown_recaptchav3_not_submitted':
                return 'reCAPTCHA v3 failed verification';
                break;
            case 'lockdown_builtin_captcha_failed':
                return 'Built-in captcha failed verification';
                break;
            case 'lockdown_hcaptcha_failed':
                return 'hCaptcha failed verification';
                break;
            default:
                return 'Unknown';
                break;
        }
    }

    static function generate_export_file()
    {
        $filename = str_replace(array('http://', 'https://'), '', home_url());
        $filename = str_replace(array('/', '\\', '.'), '-', $filename);
        $filename .= '-' . date('Y-m-d') . '-loginlockdown.txt';

        $options = LoginLockdown_Setup::get_options();
        $options_json = json_encode($options);

        header('Content-type: text/txt');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($options_json));

        @ob_end_clean();
        flush();

        echo $options_json;

        exit;
    } // generate_export_file
} // class
