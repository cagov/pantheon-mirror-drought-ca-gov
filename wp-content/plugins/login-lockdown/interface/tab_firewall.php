<?php
/**
 * Login Lockdown Pro
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_Tab_Firewall extends LoginLockdown
{
    static function display()
    {
        $tabs[] = array('id' => 'tab_general', 'class' => 'tab-content', 'label' => __('General', 'login-lockdown'), 'callback' => array(__CLASS__, 'tab_general'));
        $tabs[] = array('id' => 'tab_2fa', 'class' => 'tab-content', 'label' => __('2FA', 'login-lockdown'), 'callback' => array(__CLASS__, 'tab_2fa'));
        $tabs[] = array('id' => 'tab_captcha', 'class' => 'tab-content', 'label' => __('Captcha', 'login-lockdown'), 'callback' => array(__CLASS__, 'tab_captcha'));
        $tabs[] = array('id' => 'tab_cloud_protection', 'class' => 'tab-content', 'label' => __('Cloud Protection', 'login-lockdown'), 'callback' => array(__CLASS__, 'tab_cloud_protection'));

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

    static function tab_general()
    {
        $options = LoginLockdown_Setup::get_options();

        echo '<p>Securing your WordPress website is vital for maintaining the security and privacy of its users. By preventing against the types of attacks below, website owners can ensure that their users receive legitimate content without being exposed to harmful or malicious data.</p>
        <p>A secure WordPress website promotes a safe browsing experience for users, fostering trust in the site\'s content and services. Additionally, mitigating these risks helps website owners avoid potential legal issues and financial losses associated with security breaches. It also protects the website\'s reputation, ensuring that users continue to rely on the site as a trustworthy source of information and services.</p>';

        echo '<table class="form-table"><tbody>';

        echo '<tr valign="top">
        <th scope="row"><label for="firewall_block_bots">Toggle All</label></th>
        <td>';
        echo '<div class="onoff-toggle-wrapper">';
        echo '<input type="checkbox" id="toggle_firewall_rules" type="checkbox" value="1" />';
        echo '<label for="toggle_firewall_rules" class="toggle"><span class="toggle_handler"></span></label>';
        echo '</div>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="firewall_block_bots">Block bad bots</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('firewall_block_bots', array('saved_value' => $options['firewall_block_bots'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[firewall_block_bots]'), true, 'firewall_rule_toggle');
        echo '<br /><span>Blocking bad bots on a WordPress site refers to the process of identifying and preventing malicious automated software programs, known as "bots," from accessing, crawling, or interacting with the website. Bad bots are typically used by attackers to perform various malicious activities, such as content scraping, spamming, DDoS attacks, vulnerability scanning, or brute-force attacks to gain unauthorized access to the site.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="firewall_directory_traversal">Directory Traversal</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('firewall_directory_traversal', array('saved_value' => $options['firewall_directory_traversal'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[firewall_directory_traversal]'), true, 'firewall_rule_toggle');
        echo '<br /><span>Directory traversal (also known as file path traversal) is a web security vulnerability that allows an attacker to access files on the server that they should not by passing file paths that attempt to traverse the normal directory structure using the parent folder path. For example <a href="https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2019-8943">CVE-2019-8943</a> in WordPress through 5.0.3 allows Path Traversal in wp_crop_image(). An attacker (who has privileges to crop an image) can write the output image to an arbitrary directory via a filename containing two image extensions and ../ sequences, such as a filename ending with the .jpg?/../../file.jpg substring.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="firewall_http_response_splitting">HTTP Response Splitting</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('firewall_http_response_splitting', array('saved_value' => $options['firewall_http_response_splitting'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[firewall_http_response_splitting]'), true, 'firewall_rule_toggle');
        echo '<br /><span>HTTP Response Splitting is a type of attack that occurs when an attacker can manipulate the response headers that will be interpreted by the client. Protecting against HTTP Response Splitting on a WordPress website is crucial to maintain its security and the privacy of its users. By preventing this vulnerability, website owners can reduce the risk of attackers stealing sensitive information, compromising user accounts, or damaging the website\'s reputation. </span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="firewall_xss">(XSS) Cross-Site Scripting</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('firewall_xss', array('saved_value' => $options['firewall_xss'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[firewall_xss]'), true, 'firewall_rule_toggle');
        echo '<br /><span>Cross-Site Scripting (XSS) is a type of web application vulnerability that allows an attacker to inject malicious scripts into web pages viewed by other users. This occurs when a web application does not properly validate or sanitize user input and includes it in the rendered HTML output. There are three main types of XSS: stored, reflected, and DOM-based.<br />In stored XSS, the malicious script is saved in the target server (e.g., in a database), while in reflected XSS, the malicious script is part of the user\'s request and reflected back in the response. DOM-based XSS occurs when the vulnerability is in the client-side JavaScript code, allowing the attacker to manipulate the Document Object Model (DOM) directly. This option only protects agains reflected/request type XSS attacks. You should still be careful about what plugins you install and make sure they are secure.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="firewall_cache_poisoning">Cache Poisoning</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('firewall_cache_poisoning', array('saved_value' => $options['firewall_cache_poisoning'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[firewall_cache_poisoning]'), true, 'firewall_rule_toggle');
        echo '<br /><span>Cache Poisoning is a type of cyberattack where an attacker manipulates the cache data of web applications, content delivery networks (CDNs), or DNS resolvers to serve malicious content to unsuspecting users. The attacker exploits vulnerabilities or misconfigurations in caching mechanisms to insert malicious data into the cache, effectively "poisoning" it. When a user makes a request, the compromised cache serves the malicious content instead of the legitimate content. This can lead to various harmful consequences, such as redirecting users to phishing sites, spreading malware, or stealing sensitive information.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="firewall_dual_header">Dual-Header Exploits</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('firewall_dual_header', array('saved_value' => $options['firewall_dual_header'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[firewall_dual_header]'), true, 'firewall_rule_toggle');
        echo '<br /><span>Dual-Header Exploits, also known as HTTP Header Injection, is a type of web application vulnerability that involves manipulating HTTP headers to execute malicious actions or inject malicious content. Similar to HTTP Response Splitting, an attacker exploits this vulnerability by injecting newline characters (CRLF - carriage return and line feed) or other special characters into user input. This allows the attacker to create or modify HTTP headers, which can lead to various harmful consequences. For instance, an attacker can set cookies, redirect users to malicious websites, or perform cross-site scripting (XSS) attacks.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="firewall_sql_injection">SQL/PHP/Code Injection</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('firewall_sql_injection', array('saved_value' => $options['firewall_sql_injection'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[firewall_sql_injection]'), true, 'firewall_rule_toggle');
        echo '<br /><span>SQL/PHP/Code Injection is a type of web application vulnerability where an attacker inserts malicious code or commands into a web application, typically by exploiting insufficient input validation or sanitization. This allows the attacker to execute unauthorized actions, such as extracting sensitive information from databases, modifying data, or gaining unauthorized access to the system.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="firewall_file_injection">File Injection/Inclusion</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('firewall_file_injection', array('saved_value' => $options['firewall_file_injection'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[firewall_file_injection]'), true, 'firewall_rule_toggle');
        echo '<br /><span>File Injection/Inclusion is a type of web application vulnerability where an attacker exploits insufficient validation or sanitization of user input to include or inject malicious files into a web application. There are two main types of File Injection/Inclusion vulnerabilities: Local File Inclusion (LFI) and Remote File Inclusion (RFI). This can lead to unauthorized access to sensitive files, source code disclosure, or even the execution of server-side scripts if the application processes the included file. If the application is manipulated to process a remote file, the attacker\'s code is executed, potentially granting unauthorized access, control over the server, or the ability to perform various malicious actions.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="firewall_null_byte_injection">Null Byte Injection</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('firewall_null_byte_injection', array('saved_value' => $options['firewall_null_byte_injection'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[firewall_null_byte_injection]'), true, 'firewall_rule_toggle');
        echo '<br /><span>Null Byte Injection is a type of web application vulnerability that exploits the way certain programming languages, such as C and PHP, handle null characters (represented as \'\0\'). The null character serves as a string terminator in these languages, signaling the end of a string. An attacker can use a null byte to manipulate user input or file paths, causing the application to truncate the string after the null character. This can lead to unexpected behaviors, such as bypassing input validation or accessing sensitive files.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="firewall_php_exploits">Exploits such as c99shell, phpshell, remoteview, site copier, et al</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('firewall_php_exploits', array('saved_value' => $options['firewall_php_exploits'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[firewall_php_exploits]'), true, 'firewall_rule_toggle');
        echo '<br /><span>C99shell, PHPShell, Remoteview, and Site Copier are web-based tools or scripts that are often used by attackers to compromise and control web servers or applications. These tools exploit vulnerabilities in web applications to gain unauthorized access and perform malicious actions.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="firewall_php_info">PHP information leakage</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('firewall_php_info', array('saved_value' => $options['firewall_php_info'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[firewall_php_info]'), true, 'firewall_rule_toggle');
        echo '<br /><span>PHP information leakage refers to the unintended exposure of sensitive information about the PHP environment, configurations, or code running on a WordPress website. This information can be valuable for attackers, as it may reveal potential vulnerabilities, system details, or other information that could be exploited to compromise the site.</span>';
        echo '</td></tr>';

        echo '<tr><td></td><td>';
        LoginLockdown_admin::footer_save_button();
        echo '</td></tr>';

        echo '</tbody></table>';
    }

    static function tab_2fa()
    {
        $options = LoginLockdown_Setup::get_options();

        echo '<div class="tab-content">';

        echo '<table class="form-table"><tbody>';

        echo '<tr valign="top">
        <th scope="row"><label for="2fa_email">Email Based Two Factor Authentication</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('2fa_email', array('saved_value' => $options['2fa_email'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[2fa_email]'));
        echo '<br /><span>After the correct username &amp; password are entered the user will receive an email with a one-time link to confirm the login.<br>In case somebody steals the username &amp; password they still won\'t be able to login without access to the account email.</span>';
        echo '</td></tr>';

        echo '<tr><td></td><td>';
        LoginLockdown_admin::footer_save_button();
        echo '</td></tr>';

        echo '</tbody></table>';

        echo '</div>';
    } // display

    static function tab_captcha()
    {
        $options = LoginLockdown_Setup::get_options();

        echo '<div class="tab-content">';

        echo '<table class="form-table"><tbody>';

        $captcha = array();
        $captcha[] = array('val' => 'disabled', 'label' => 'Disabled');
        $captcha[] = array('val' => 'builtin', 'label' => 'Built-in Captcha');
        $captcha[] = array('val' => 'recaptchav2', 'label' => 'reCAPTCHA v2');
        $captcha[] = array('val' => 'recaptchav3', 'label' => 'reCAPTCHA v3');
        $captcha[] = array('val' => 'hcaptcha', 'label' => 'hCaptcha');

        echo '<tr valign="top">
        <th scope="row"><label for="captcha">Captcha</label></th>
        <td><select id="captcha" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[captcha]">';
        LoginLockdown_Utility::create_select_options($captcha, $options['captcha']);
        echo '</select>';
        echo '<br /><span>Captcha or "are you human" verification ensures bots can\'t attack your login page and provides additional protection with minimal impact to users.</span>';
        echo '</td></tr>';

        echo '<tr class="captcha_keys_wrapper" style="display:none;" valign="top">
        <th scope="row"><label for="captcha_site_key">Captcha Site Key</label></th>
        <td><input type="text" class="regular-text" id="captcha_site_key" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[captcha_site_key]" value="' . $options['captcha_site_key'] . '" data-old="' . $options['captcha_site_key'] . '" />';
        echo '</td></tr>';

        echo '<tr class="captcha_keys_wrapper" style="display:none;" valign="top">
        <th scope="row"><label for="captcha_secret_key">Captcha Secret Key</label></th>
        <td><input type="text" class="regular-text" id="captcha_secret_key" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[captcha_secret_key]" value="' . $options['captcha_secret_key'] . '" data-old="' . $options['captcha_secret_key'] . '" />';
        echo '</td></tr>';

        echo '<tr class="captcha_verify_wrapper" style="display:none;" valign="top">
        <th scope="row"></th>
        <td><button id="verify-captcha" class="button button-primary button-large button-yellow">Verify Captcha <i class="loginlockdown-icon loginlockdown-make-group"></i></button>';
        echo '<input type="hidden" class="regular-text" id="captcha_verified" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[captcha_verified]" value="0" />';
        echo '<br /><span>Click the Verify Captcha button to verify that the captcha is valid and working otherwise captcha settings will not be saved</span>';
        echo '</td></tr>';

        echo '<tr><td></td><td>';
        LoginLockdown_admin::footer_save_button();
        echo '</td></tr>';

        echo '<tr><td colspan="2">';
            echo '<div class="captcha-box-wrapper ' . ($options['captcha'] == 'disabled'?'captcha-selected':'') . '" data-captcha="disabled">';
                echo '<img src="' . LOGINLOCKDOWN_PLUGIN_URL . '/images/captcha_disabled.png" />';
                echo '<div class="captcha-box-desc">';
                    echo '<h3>Captcha Disabled</h3>';
                    echo '<ul>';
                    echo '<li>No Additional Security</li>';
                    echo '</ul>';
                echo '</div>';
            echo '</div>';

            echo '<div class="captcha-box-wrapper ' . ($options['captcha'] == 'builtin'?'captcha-selected':'') . '" data-captcha="builtin">';
                echo '<img src="' . LOGINLOCKDOWN_PLUGIN_URL . '/images/captcha_builtin.png" />';
                echo '<div class="captcha-box-desc">';
                    echo '<h3>Built-in Captcha</h3>';
                    echo '<ul>';
                    echo '<li>Medium Security</li>';
                    echo '<li>No API keys</li>';
                    echo '<li>GDPR Compatible</li>';
                    echo '</ul>';
                echo '</div>';
            echo '</div>';

            echo '<div class="captcha-box-wrapper ' . ($options['captcha'] == 'recaptchav2'?'captcha-selected':'') . '" data-captcha="recaptchav2">';
                echo '<img src="' . LOGINLOCKDOWN_PLUGIN_URL . '/images/captcha_recaptcha_v2.png" />';
                echo '<div class="captcha-box-desc">';
                    echo '<h3>reCaptcha v2</h3>';
                    echo '<ul>';
                    echo '<li>High Security</li>';
                    echo '<li>Requires <a href="https://www.google.com/recaptcha/about/" target="_blank">API Keys</a></li>';
                    echo '<li>Not GDPR Compatible</li>';
                    echo '</ul>';
                echo '</div>';
            echo '</div>';

            echo '<div class="captcha-box-wrapper ' . ($options['captcha'] == 'recaptchav3'?'captcha-selected':'') . '" data-captcha="recaptchav3">';
                echo '<img src="' . LOGINLOCKDOWN_PLUGIN_URL . '/images/captcha_recaptcha_v3.png" />';
                echo '<div class="captcha-box-desc">';
                    echo '<h3>reCaptcha v3</h3>';
                    echo '<ul>';
                    echo '<li>High Security</li>';
                    echo '<li>Requires <a href="https://www.google.com/recaptcha/about/" target="_blank">API Keys</a></li>';
                    echo '<li>Not GDPR Compatible</li>';
                    echo '</ul>';
                echo '</div>';
            echo '</div>';

            echo '<div class="captcha-box-wrapper ' . ($options['captcha'] == 'hcaptcha'?'captcha-selected':'') . '" data-captcha="hcaptcha">';
                echo '<img src="' . LOGINLOCKDOWN_PLUGIN_URL . '/images/captcha_hcaptcha.png" />';
                echo '<div class="captcha-box-desc">';
                    echo '<h3>hCaptcha</h3>';
                    echo '<ul>';
                    echo '<li>High Security</li>';
                    echo '<li>Requires <a href="https://www.hcaptcha.com/signup-interstitial" target="_blank">API Keys</a></li>';
                    echo '<li>GDPR Compatible</li>';
                    echo '<li>Best Choice</li>';
                    echo '</ul>';
                echo '</div>';
            echo '</div>';
        echo '</td></tr>';

        echo '</tbody></table>';

        echo '</div>';
    } // display

    static function tab_cloud_protection()
    {
        global $loginlockdown_licensing;
        $options = LoginLockdown_Setup::get_options();
        $license = $loginlockdown_licensing->get_license();
        $whitelabel = LoginLockdown_Utility::whitelabel_filter();

        echo '<div class="tab-content">';

        echo '<table class="form-table"><tbody>';

        echo '<tr valign="top">
        <th scope="row"><label for="cloud_use_account_lists">Use Account Whitelist &amp; Blacklist</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('cloud_use_account_lists', array('saved_value' => $options['cloud_use_account_lists'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[cloud_use_account_lists]'));
        echo '<br /><span>These lists are private and available only to your sites. ' . ($whitelabel?'Configure them in the <a target="_blank" href="' . LoginLockdown_Admin::generate_dashboard_link('license-tab', 'cloud-protection') . '">Login Lockdown Dashboard</a></span>':'');
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="cloud_use_blacklist">Use Global Cloud Blacklist</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('cloud_use_blacklist', array('saved_value' => $options['cloud_use_blacklist'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[cloud_use_blacklist]'));
        echo '<br /><span>A list of bad IPs maintained daily by WebFactory, and based on realtime malicios activity observed on thousands of websites. IPs found on this list are trully bad and should not have access to your site.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="global_block">Cloud Block Type</label></th>
        <td>';
            echo '<label class="loginlockdown-radio-option">';
            echo '<span class="radio-container"><input type="radio" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[cloud_global_block]" id="cloud_global_block_global" value="1" ' . ($options['cloud_global_block'] == 1?'checked':'') . '><span class="radio"></span></span> Completely block website access';
            echo '</label>';

            echo '<label class="loginlockdown-radio-option">';
            echo '<span class="radio-container"><input type="radio" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[cloud_global_block]" id="cloud_global_block_login" value="0" ' . ($options['cloud_global_block'] != 1?'checked':'') . '><span class="radio"></span></span> Only block access to the login page';
            echo '</label>';
        echo '<span>Completely block website access for IPs on cloud blacklist, or just blocking access to the login page.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="block_message_cloud">Block Message</label></th>
        <td><input type="text" class="regular-text" id="block_message_cloud" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[block_message_cloud]" value="' . $options['block_message_cloud'] . '" />';
        echo '<br /><span>Message displayed to visitors blocked based on cloud lists. Default: <i>We\'re sorry, but access from your IP is not allowed.</i></span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label>Cloud Whitelist</label></th>
        <td><textarea  rows="4" disabled>';
        if (is_array($license) && is_array($license['meta']) && array_key_exists('cloud_protection_whitelist', $license['meta']) && !empty($license['meta']['cloud_protection_whitelist'])) {
            echo str_replace(',', PHP_EOL, $license['meta']['cloud_protection_whitelist']);
        }
        echo '</textarea>';
        echo '<br /><span>The Cloud Protection Whitelist can only be edited in the <a target="_blank" href="' . LoginLockdown_Admin::generate_dashboard_link('cloud-protection') . '">Login Lockdown Dashboard</a>.</i></span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label>Cloud Blacklist</label></th>
        <td><textarea rows="4" disabled>';
        if (is_array($license) && is_array($license['meta']) && array_key_exists('cloud_protection_blacklist', $license['meta']) && !empty($license['meta']['cloud_protection_blacklist'])) {
            echo str_replace(',', PHP_EOL, $license['meta']['cloud_protection_blacklist']);
        }
        echo '</textarea>';
        echo '<br /><span>The Cloud Protection Blacklist can only be edited in the <a target="_blank" href="' . LoginLockdown_Admin::generate_dashboard_link('cloud-protection') . '">Login Lockdown Dashboard</a>.</i></span>';
        echo '</td></tr>';

        echo '<tr><td></td><td>';
        LoginLockdown_admin::footer_save_button();
        echo '</td></tr>';

        echo '</tbody></table>';

        echo '</div>';
    } // display

    static function tab_country_blocking()
    {
        echo '<div class="tab-content">';
        $fail_stats = LoginLockdown_Stats::get_stats('fails');

        if($fail_stats['total'] < 5){
            echo '<div class="loginlockdown-chart-placeholder">Stats will be available once enough data is collected.</div>';
        }

        echo '<div class="geoip-stats-wrapper" ' . ($fail_stats['total'] < 5?'style="filter: blur(3px);"':'') . '>';
            echo '<div id="geoip_map"></div>';
            echo '<div id="geoip_countries">';
                $countries = LoginLockdown_Stats::get_top_countries('fails');
                echo '<h3>Top Countries</h3>';
                echo '<table class="loginlockdown-stats-table">';
                foreach ($countries as $country => $count) {
                    echo '<tr><td>' . ($country != 'Other' ? '<img src="' . LOGINLOCKDOWN_PLUGIN_URL . 'images/flags/' . strtolower(LoginLockdown_Utility::country_name_to_code($country)) . '.png" /> ' : '<img src="' . LOGINLOCKDOWN_PLUGIN_URL . 'images/flags/other.png" /> ') . $country . '</td><td>' . $count . '%</td></tr>';
                }
                echo '</table>';
            echo '</div>';
        echo '</div>';

        $options = LoginLockdown_Setup::get_options();

        echo '<table class="form-table"><tbody>';

        $country_blocking_mode = array();
        $country_blocking_mode[] = array('val' => 'none', 'label' => 'Disable country based blocking');
        $country_blocking_mode[] = array('val' => 'whitelist', 'label' => 'Whitelist mode - allow selected countries, block all others');
        $country_blocking_mode[] = array('val' => 'blacklist', 'label' => 'Blacklist mode - block selected countries, allow all others');

        echo '<tr valign="top">
        <th scope="row"><label for="country_blocking_mode">Blocking Mode</label></th>
        <td><select id="country_blocking_mode" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[country_blocking_mode]">';
        LoginLockdown_Utility::create_select_options($country_blocking_mode, $options['country_blocking_mode']);
        echo '</select>';
        echo '</td></tr>';


        echo '<tr valign="top" class="country-blocking-wrapper" style="display:none">
        <th scope="row"><label for="country_blocking_countries" class="country-blocking-label">Countries</label></th>
        <td><select id="country_blocking_countries" multiple="multiple" style="width:500px; max-width:500px !important;" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[country_blocking_countries][]" data-countries="' . (is_array($options['country_blocking_countries'])?implode(',', $options['country_blocking_countries']):'') . '"></select>';
        $user_country = LoginLockdown_Utility::getUserCountry();

        if(empty($user_country)){
            echo '<br /><span style="color:#e54c4c;">Could not determine your country based on your IP ' . LoginLockdown_Utility::getUserIP(true) . '</span>';
        } else {
            echo '<br /><span>Your country has been determined to be: ' . $user_country . '</span>';
        }
        echo '</td></tr>';

        echo '<tr valign="top" class="country-blocking-wrapper" style="display:none">
        <th scope="row"><label for="block_undetermined_countries">Block Undetermined Countries</label></th>
        <td>';
        LoginLockdown_Utility::create_toggle_switch('block_undetermined_countries', array('saved_value' => $options['block_undetermined_countries'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[block_undetermined_countries]'));
        echo '<br /><span>For some IP addresses it\'s impossible to determine their country (localhost addresses, for instance). Enabling this option will blocks regardless of the Blocking Mode setting.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="global_block">Country Block Type</label></th>
        <td>';
            echo '<label class="loginlockdown-radio-option">';
            echo '<span class="radio-container"><input type="radio" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[country_global_block]" id="country_global_block_global" value="1" ' . ($options['country_global_block'] == 1?'checked':'') . '><span class="radio"></span></span> Completely block website access';
            echo '</label>';

            echo '<label class="loginlockdown-radio-option">';
            echo '<span class="radio-container"><input type="radio" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[country_global_block]" id="country_global_block_login" value="0" ' . ($options['country_global_block'] != 1?'checked':'') . '><span class="radio"></span></span> Only block access to the login page';
            echo '</label>';
        echo '<span>Completely block website access for blocked countries, or just blocking access to the login page.</span>';
        echo '</td></tr>';


        echo '<tr valign="top" class="country-blocking-wrapper" style="display:none">
        <th scope="row"><label for="block_message_country">Block Message</label></th>
        <td><input type="text" class="regular-text" id="block_message_country" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[block_message_country]" value="' . $options['block_message_country'] . '" />';
        echo '<br /><span>Message displayed to visitors blocked based on country blocking rules. Default: <i>We\'re sorry, but access from your location is not allowed.</i></span>';
        echo '</td></tr>';

        echo '<tr><td></td><td>';
        LoginLockdown_admin::footer_save_button();
        echo '</td></tr>';

        echo '</tbody></table>';

        echo '</div>';
    } // display

} // class LoginLockdown_Tab_Firewall
