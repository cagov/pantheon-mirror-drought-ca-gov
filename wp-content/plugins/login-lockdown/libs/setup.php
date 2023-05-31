<?php

/**
 * Login Lockdown Pro
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_Setup extends LoginLockdown
{
    static $wp_filesystem;

    /**
     * Actions to run on load, but init would be too early as not all classes are initialized
     *
     * @return null
     */
    static function load_actions()
    {
        global $loginlockdown_licensing;

        $options = self::get_options();

        self::register_custom_tables();

        $loginlockdown_licensing = new WF_Licensing_LoginLockdown(array(
            'prefix' => 'lockdown',
            'licensing_servers' => self::$licensing_servers,
            'version' => self::$version,
            'plugin_file' => LOGINLOCKDOWN_PLUGIN_FILE,
            'skip_hooks' => false,
            'debug' => false,
            'js_folder' => LOGINLOCKDOWN_PLUGIN_URL . '/js/'
        ));

        if (isset($_GET['loginlockdown_wl'])) {
            if ($_GET['loginlockdown_wl'] == 'true') {
                $options['whitelabel'] = true;
            } else {
                $options['whitelabel'] = false;
            }
            update_option(LOGINLOCKDOWN_OPTIONS_KEY, $options);
        }

        add_filter('wf_licensing_lockdown_remote_actions', function ($actions) {
            $actions[] = 'reset_lockdowns';

            return $actions;
        }, 10, 1);

        add_action('wf_licensing_lockdown_remote_action_reset_lockdowns', function ($request) {
            global $wpdb;
            $wpdb->update(
                $wpdb->lockdown_lockdowns,
                array(
                    'unlocked' => 1
                )
            );
            wp_send_json_success(array('result' => 'unlocked'));
        }, 10, 1);

        add_filter('wf_licensing_lockdown_query_server_meta', function ($meta, $action) {
            return array('stats' => LoginLockdown_Stats::prepare_stats(), 'login_url' => wp_login_url());
        }, 10, 2);


        add_action('wf_licensing_lockdown_update_license', function ($license) {
            LoginLockdown_Functions::sync_cloud_protection();
        }, 10, 1);
    } // admin_actions

    static function setup_wp_filesystem()
    {
        global $wp_filesystem;

        if (empty($wp_filesystem)) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }

        self::$wp_filesystem = $wp_filesystem;
        return self::$wp_filesystem;
    } // setup_wp_filesystem

    /**
     * Check if user has the minimal WP version required by Login Lockdown
     *
     * @since 5.0
     *
     * @return bool
     *
     */
    static function check_wp_version($min_version)
    {
        if (!version_compare(get_bloginfo('version'), $min_version,  '>=')) {
            add_action('admin_notices', array(__CLASS__, 'notice_min_wp_version'));
            return false;
        } else {
            return true;
        }
    } // check_wp_version

    /**
     * Check if user has the minimal PHP version required by Login Lockdown
     *
     * @since 5.0
     *
     * @return bool
     *
     */
    static function check_php_version($min_version)
    {
        if (!version_compare(phpversion(), $min_version,  '>=')) {
            add_action('admin_notices', array(__CLASS__, 'notice_min_php_version'));
            return false;
        } else {
            return true;
        }
    } // check_wp_version

    /**
     * Display error message if WP version is too low
     *
     * @since 5.0
     *
     * @return null
     *
     */
    static function notice_min_wp_version()
    {
        echo '<div class="error"><p>' . sprintf(__('Login Lockdown Pro plugin <b>requires WordPress version 4.6</b> or higher to function properly. You are using WordPress version %s. Please <a href="%s">update it</a>.', 'login-lockdown'), get_bloginfo('version'), admin_url('update-core.php')) . '</p></div>';
    } // notice_min_wp_version_error

    /**
     * Display error message if PHP version is too low
     *
     * @since 5.0
     *
     * @return null
     *
     */
    static function notice_min_php_version()
    {
        echo '<div class="error"><p>' . sprintf(__('Login Lockdown Pro plugin <b>requires PHP version 5.6.20</b> or higher to function properly. You are using PHP version %s. Please <a href="%s" target="_blank">update it</a>.', 'login-lockdown'), phpversion(), 'https://wordpress.org/support/update-php/') . '</p></div>';
    } // notice_min_wp_version_error


    /**
     * activate doesn't get fired on upgrades so we have to compensate
     *
     * @since 5.0
     *
     * @return null
     *
     */
    public static function maybe_upgrade()
    {
        $meta = self::get_meta();
        if (empty($meta['database_ver']) || $meta['database_ver'] < self::$version) {
            self::create_custom_tables();
        }


        // Copy options from free
        $options = get_option(LOGINLOCKDOWN_OPTIONS_KEY);
        if (false === $options) {
            $free_options = get_option("loginlockdownAdminOptions");
            if (false !== $free_options && isset($free_options['max_login_retries'])) {
                update_option(LOGINLOCKDOWN_OPTIONS_KEY, $free_options);
                delete_option("loginlockdownAdminOptions");
            }
        }
    } // maybe_upgrade


    /**
     * Get plugin options
     *
     * @since 5.0
     *
     * @return array options
     *
     */
    static function get_options()
    {
        $options = get_option(LOGINLOCKDOWN_OPTIONS_KEY, array());

        if (!is_array($options)) {
            $options = array();
        }
        $options = array_merge(self::default_options(), $options);

        return $options;
    } // get_options

    /**
     * Register all settings
     *
     * @since 5.0
     *
     * @return false
     *
     */
    static function register_settings()
    {
        register_setting(LOGINLOCKDOWN_OPTIONS_KEY, LOGINLOCKDOWN_OPTIONS_KEY, array(__CLASS__, 'sanitize_settings'));
    } // register_settings


    /**
     * Set default options
     *
     * @since 5.0
     *
     * @return null
     *
     */
    static function default_options()
    {
        $defaults = array(
            'max_login_retries'                       => 3,
            'retries_within'                          => 5,
            'lockout_length'                          => 60,
            'lockout_invalid_usernames'               => 1,
            'mask_login_errors'                       => 0,
            'show_credit_link'                        => 0,
            'anonymous_logging'                       => 0,
            'block_bots'                              => 0,
            'log_passwords'                           => 0,
            'instant_block_nonusers'                  => 0,
            'honeypot'                                => 0,
            'cookie_lifetime'                         => 14,
            'country_blocking_mode'                   => 'none',
            'country_blocking_countries'              => '',
            'block_undetermined_countries'            => 0,
            '2fa_email'                               => 0,
            'whitelabel'                              => 0,
            'captcha'                                 => 'disabled',
            'captcha_secret_key'                      => '',
            'captcha_site_key'                        => '',
            'login_url'                               => '',
            'login_redirect_url'                      => '',
            'global_block'                            => 0,
            'country_global_block'                    => 0,
            'uninstall_delete'                        => 0,
            'show_admin_menu'                         => 1,
            'block_message'                           => 'We\'re sorry, but your IP has been blocked due to too many recent failed login attempts.',
            'block_message_country'                   => 'We\'re sorry, but access from your location is not allowed.',
            'wizard_complete'                         => 0,
            'global_unblock_key'                      => 'll' . md5(time() . rand(10000, 9999)),
            'whitelist'                               => array(),
            'cloud_use_account_lists'                 => 1,
            'cloud_use_blacklist'                     => 1,
            'block_message_cloud'                     => 'We\'re sorry, but access from your IP is not allowed.',
            'cloud_global_block'                      => 1,
            'firewall_block_bots'                     => 0,
            'firewall_directory_traversal'            => 0,
            'firewall_http_response_splitting'        => 0,
            'firewall_xss'                            => 0,
            'firewall_cache_poisoning'                => 0,
            'firewall_dual_header'                    => 0,
            'firewall_sql_injection'                  => 0,
            'firewall_file_injection'                 => 0,
            'firewall_null_byte_injection'            => 0,
            'firewall_wordpress_exploits'             => 0,
            'firewall_php_exploits'                   => 0,
            'firewall_php_info'                       => 0,
            'design_enable'                           => 0,
            'design_background_color'                 => '',
            'design_background_image'                 => '',
            'design_logo'                             => '',
            'design_logo_width'                       => '',
            'design_logo_height'                      => '',
            'design_logo_margin_bottom'               => '',
            'design_text_color'                       => '#3c434a',
            'design_link_color'                       => '#2271b1',
            'design_link_hover_color'                 => '#135e96',
            'design_form_border_color'                => '#FFFFFF',
            'design_form_border_width'                => 1,
            'design_form_width'                       => '',
            'design_form_width'                       => '',
            'design_form_height'                      => '',
            'design_form_padding'                     => 26,
            'design_form_border_radius'               => 2,
            'design_form_background_color'            => '',
            'design_form_background_image'            => '',
            'design_label_font_size'                  => 14,
            'design_label_text_color'                 => '#3c434a',
            'design_field_font_size'                  => 13,
            'design_field_text_color'                 => '#3c434a',
            'design_field_border_color'               => '#8c8f94',
            'design_field_border_width'               => 1,
            'design_field_border_radius'              => 2,
            'design_field_background_color'           => '#ffffff',
            'design_button_font_size'                 => 14,
            'design_button_text_color'                => '',
            'design_button_border_color'              => '#2271b1',
            'design_button_border_width'              => 0,
            'design_button_border_radius'             => 2,
            'design_button_background_color'          => '#2271b1',
            'design_button_hover_text_color'          => '',
            'design_button_hover_border_color'        => '',
            'design_button_hover_background_color'    => '',
            'design_custom_css'                       => ''
        );

        return $defaults;
    } // default_options


    /**
     * Sanitize settings on save
     *
     * @since 5.0
     *
     * @return array updated options
     *
     */
    static function sanitize_settings($options)
    {
        $old_options = self::get_options();

        if (isset($options['2fa_email']) && $options['2fa_email'] == 1 && $old_options['2fa_email'] != $options['2fa_email']) {
            $user = wp_get_current_user();
            $key = rand(10000, 99999);
            update_user_meta($user->ID, 'loginlockdown_2fa_key', $key);
            update_user_meta($user->ID, 'loginlockdown_2fa_key_check', $key);
        }

        if (isset($options['captcha_verified']) && $options['captcha_verified'] != 1 && $options['captcha'] != 'disabled' && $options['captcha'] != 'builtin') {
            $options['captcha']            = $old_options['captcha'];
            $options['captcha_site_key']   = $old_options['captcha_site_key'];
            $options['captcha_secret_key'] = $old_options['captcha_secret_key'];
        }

        if (isset($options['captcha']) && ($options['captcha'] == 'disabled' || $options['captcha'] == 'builtin')) {
            $options['captcha_site_key']   = '';
            $options['captcha_secret_key'] = '';
        }

        if (isset($_POST['submit'])) {
            foreach ($options as $key => $value) {
                switch ($key) {
                    case 'lockout_invalid_usernames':
                    case 'mask_login_errors':
                    case 'show_credit_link':
                        $options[$key] = trim($value);
                        break;
                    case 'max_login_retries':
                    case 'retries_within':
                    case 'lockout_length':
                        $options[$key] = (int) $value;
                        break;
                } // switch
            } // foreach
        }

        if (!isset($options['lockout_invalid_usernames'])) {
            $options['lockout_invalid_usernames'] = 0;
        }

        if (!isset($options['mask_login_errors'])) {
            $options['mask_login_errors'] = 0;
        }

        if (!isset($options['anonymous_logging'])) {
            $options['anonymous_logging'] = 0;
        }

        if (!isset($options['block_bots'])) {
            $options['block_bots'] = 0;
        }

        if (!isset($options['instant_block_nonusers'])) {
            $options['instant_block_nonusers'] = 0;
        }

        if (!isset($options['honeypot'])) {
            $options['honeypot'] = 0;
        }

        if (!isset($options['country_blocking_mode'])) {
            $options['country_blocking_mode'] = 0;
        }

        if (!isset($options['2fa_email'])) {
            $options['2fa_email'] = 0;
        }

        if (!isset($options['block_undetermined_countries'])) {
            $options['block_undetermined_countries'] = 0;
        }

        if (!isset($options['global_block'])) {
            $options['global_block'] = 0;
        }

        if (!isset($options['country_global_block'])) {
            $options['country_global_block'] = 0;
        }

        if (!isset($options['uninstall_delete'])) {
            $options['uninstall_delete'] = 0;
        }

        if (!isset($options['show_admin_menu'])) {
            $options['show_admin_menu'] = 0;
        }

        if (!is_array($options['whitelist'])) {
            $options['whitelist'] = explode(PHP_EOL, $options['whitelist']);
        }

        if (!isset($options['show_credit_link'])) {
            $options['show_credit_link'] = 0;
        }

        if (!isset($options['firewall_block_bots'])) {
            $options['firewall_block_bots'] = 0;
        }

        if (!isset($options['firewall_directory_traversal'])) {
            $options['firewall_directory_traversal'] = 0;
        }

        if (!isset($options['firewall_http_response_splitting'])) {
            $options['firewall_http_response_splitting'] = 0;
        }

        if (!isset($options['firewall_xss'])) {
            $options['firewall_xss'] = 0;
        }

        if (!isset($options['firewall_cache_poisoning'])) {
            $options['firewall_cache_poisoning'] = 0;
        }

        if (!isset($options['firewall_dual_header'])) {
            $options['firewall_dual_header'] = 0;
        }

        if (!isset($options['firewall_sql_injection'])) {
            $options['firewall_sql_injection'] = 0;
        }

        if (!isset($options['firewall_file_injection'])) {
            $options['firewall_file_injection'] = 0;
        }

        if (!isset($options['firewall_null_byte_injection'])) {
            $options['firewall_null_byte_injection'] = 0;
        }

        if (!isset($options['firewall_wordpress_exploits'])) {
            $options['firewall_wordpress_exploits'] = 0;
        }

        if (!isset($options['firewall_php_exploits'])) {
            $options['firewall_php_exploits'] = 0;
        }

        if (!isset($options['firewall_php_info'])) {
            $options['firewall_php_info'] = 0;
        }

        if (!isset($options['design_enable'])) {
            $options['design_enable'] = 0;
        }

        if (!isset($options['log_passwords'])) {
            $options['log_passwords'] = 0;
        }

        $options['login_url'] = sanitize_title_with_dashes($options['login_url']);
        if (strpos($options['login_url'], 'wp-login') === false && !in_array($options['login_url'], self::forbidden_login_slugs())) {
            flush_rewrite_rules(true);
        } else {
            $options['login_url'] = $old_options['login_url'];
        }

        $options['login_redirect_url'] = sanitize_title_with_dashes($options['login_redirect_url']);
        if (strpos($options['login_redirect_url'], '404') === false){
            flush_rewrite_rules(true);
        } else {
            $options['login_redirect_url'] = '';
        }

        if (isset($_POST['loginlockdown_import_file'])) {
            $mimes = array(
                'text/plain',
                'text/anytext',
                'application/txt'
            );

            if (!in_array($_FILES['loginlockdown_import_file']['type'], $mimes)) {
                LoginLockdown_Utility::display_notice(
                    sprintf(
                        "WARNING: Not a valid CSV file - the Mime Type '%s' is wrong! No settings have been imported.",
                        $_FILES['loginlockdown_import_file']['type']
                    ),
                    "error"
                );
            } else if (($handle = fopen($_FILES['loginlockdown_import_file']['tmp_name'], "r")) !== false) {
                $options_json = json_decode(fread($handle, 8192), ARRAY_A);

                if (is_array($options_json) && array_key_exists('max_login_retries', $options_json) && array_key_exists('retries_within', $options_json) && array_key_exists('lockout_length', $options_json)) {
                    $options = $options_json;
                    LoginLockdown_Utility::display_notice("Settings have been imported.", "success");
                } else {
                    LoginLockdown_Utility::display_notice("Invalid import file! No settings have been imported.", "error");
                }
            } else {
                LoginLockdown_Utility::display_notice("Invalid import file! No settings have been imported.", "error");
            }
        }

        if (
            $old_options['firewall_block_bots'] != $options['firewall_block_bots'] ||
            $old_options['firewall_directory_traversal'] != $options['firewall_directory_traversal'] ||
            $old_options['firewall_http_response_splitting'] != $options['firewall_http_response_splitting'] ||
            $old_options['firewall_xss'] != $options['firewall_xss'] ||
            $old_options['firewall_cache_poisoning'] != $options['firewall_cache_poisoning'] ||
            $old_options['firewall_dual_header'] != $options['firewall_dual_header'] ||
            $old_options['firewall_sql_injection'] != $options['firewall_sql_injection'] ||
            $old_options['firewall_file_injection'] != $options['firewall_file_injection'] ||
            $old_options['firewall_wordpress_exploits'] != $options['firewall_wordpress_exploits'] ||
            $old_options['firewall_php_exploits'] != $options['firewall_php_exploits'] ||
            $old_options['firewall_php_info'] != $options['firewall_php_info']
        ) {
            self::firewall_setup($options);
        }

        LoginLockdown_Utility::clear_3rdparty_cache();
        $options['last_options_edit'] = current_time('mysql', true);

        return array_merge($old_options, $options);
    } // sanitize_settings

    static function forbidden_login_slugs() {

		$wp = new \WP;

		return array_merge( $wp->public_query_vars, $wp->private_query_vars );

	}

    /**
     * Get plugin metadata
     *
     * @since 5.0
     *
     * @return array meta
     *
     */
    static function get_meta()
    {
        $meta = get_option(LOGINLOCKDOWN_META_KEY, array());

        if (!is_array($meta) || empty($meta)) {
            $meta['first_version'] = self::get_plugin_version();
            $meta['first_install'] = current_time('timestamp');
            update_option(LOGINLOCKDOWN_META_KEY, $meta);
        }

        return $meta;
    } // get_meta

    static function update_meta($key, $value)
    {
        $meta = get_option(LOGINLOCKDOWN_META_KEY, array());
        $meta[$key] = $value;
        update_option(LOGINLOCKDOWN_META_KEY, $meta);
    } // update_meta

    /**
     * Register custom tables
     *
     * @since 5.0
     *
     * @return null
     *
     */
    static function register_custom_tables()
    {
        global $wpdb;

        $wpdb->lockdown_login_fails = $wpdb->prefix . 'login_fails';
        $wpdb->lockdown_lockdowns = $wpdb->prefix . 'lockdowns';
    } // register_custom_tables

    /**
     * Create custom tables
     *
     * @since 5.0
     *
     * @return null
     *
     */
    static function create_custom_tables()
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        self::register_custom_tables();

        $lockdown_login_fails = "CREATE TABLE " . $wpdb->lockdown_login_fails . " (
			`login_attempt_ID` bigint(20) NOT NULL AUTO_INCREMENT,
			`user_id` bigint(20) NOT NULL,
			`login_attempt_date` datetime NOT NULL default '0000-00-00 00:00:00',
			`login_attempt_IP` varchar(100) NOT NULL default '',
            `failed_user` varchar(200) NOT NULL default '',
            `failed_pass` varchar(200) NOT NULL default '',
            `country` varchar(100) NOT NULL default '',
            `user_agent` MEDIUMTEXT NULL,
            `user_agent_browser` varchar(100) NULL,
            `user_agent_browser_version` varchar(100) NULL,
            `user_agent_os` varchar(100) NULL,
            `user_agent_os_ver` varchar(100) NULL,
            `user_agent_device` varchar(100) NULL,
            `user_agent_bot` varchar(100) NULL,
            `reason` varchar(200) NULL,
			PRIMARY KEY  (`login_attempt_ID`)
			);";
        dbDelta($lockdown_login_fails);

        $lockdown_lockdowns = "CREATE TABLE " . $wpdb->lockdown_lockdowns . " (
			`lockdown_ID` bigint(20) NOT NULL AUTO_INCREMENT,
			`user_id` bigint(20) NOT NULL,
			`lockdown_date` datetime NOT NULL default '0000-00-00 00:00:00',
			`release_date` datetime NOT NULL default '0000-00-00 00:00:00',
			`lockdown_IP` varchar(100) NOT NULL default '',
            `country` varchar(100) NOT NULL default '',
            `user_agent` MEDIUMTEXT NULL,
            `user_agent_browser` varchar(100) NULL,
            `user_agent_browser_version` varchar(100) NULL,
            `user_agent_os` varchar(100) NULL,
            `user_agent_os_ver` varchar(100) NULL,
            `user_agent_device` varchar(100) NULL,
            `user_agent_bot` varchar(100) NULL,
            `reason` varchar(200) NULL,
            `unlocked` smallint(20) NOT NULL default '0',
			PRIMARY KEY  (`lockdown_ID`)
			);";
        dbDelta($lockdown_lockdowns);

        self::update_meta('database_ver', self::$version);
    } // create_custom_tables


    static function firewall_setup($options = false)
    {
        self::setup_wp_filesystem();
        self::firewall_remove_rules();

        if (false === $options) {
            $options = get_option(LOGINLOCKDOWN_OPTIONS_KEY, array());
        }

        $htaccess = self::$wp_filesystem->get_contents(LoginLockdown_Utility::get_home_path() . '.htaccess');

        $firewall_rules = [];
        $firewall_rules[] = '# BEGIN Login Lockdown Firewall';

        if ($options['firewall_block_bots']) {
            $firewall_rules[] = '<IfModule mod_rewrite.c>';

            $firewall_rules[] = 'RewriteCond %{HTTP_USER_AGENT} (ahrefs|alexibot|majestic|mj12bot|rogerbot) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{HTTP_USER_AGENT} (econtext|eolasbot|eventures|liebaofast|nominet|oppo\sa33) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{HTTP_USER_AGENT} (ahrefs|alexibot|majestic|mj12bot|rogerbot) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{HTTP_USER_AGENT} (econtext|eolasbot|eventures|liebaofast|nominet|oppo\sa33) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{HTTP_USER_AGENT} (acapbot|acoonbot|asterias|attackbot|backdorbot|becomebot|binlar|blackwidow|blekkobot|blexbot|blowfish|bullseye|bunnys|butterfly|careerbot|casper|checkpriv|cheesebot|cherrypick|chinaclaw|choppy|clshttp|cmsworld|copernic|copyrightcheck|cosmos|crescent|cy_cho|datacha|demon|diavol|discobot|dittospyder|dotbot|dotnetdotcom|dumbot|emailcollector|emailsiphon|emailwolf|extract|eyenetie|feedfinder|flaming|flashget|flicky|foobot|g00g1e|getright|gigabot|go-ahead-got|gozilla|grabnet|grafula|harvest|heritrix|httrack|icarus6j|jetbot|jetcar|jikespider|kmccrew|leechftp|libweb|linkextractor|linkscan|linkwalker|loader|masscan|miner|mechanize|morfeus|moveoverbot|netmechanic|netspider|nicerspro|nikto|ninja|nutch|octopus|pagegrabber|petalbot|planetwork|postrank|proximic|purebot|pycurl|python|queryn|queryseeker|radian6|radiation|realdownload|scooter|seekerspider|semalt|siclab|sindice|sistrix|sitebot|siteexplorer|sitesnagger|skygrid|smartdownload|snoopy|sosospider|spankbot|spbot|sqlmap|stackrambler|stripper|sucker|surftbot|sux0r|suzukacz|suzuran|takeout|teleport|telesoft|true_robots|turingos|turnit|vampire|vikspider|voideye|webleacher|webreaper|webstripper|webvac|webviewer|webwhacker|winhttp|wwwoffle|woxbot|xaldon|xxxyy|yamanalab|yioopbot|youda|zeus|zmeu|zune|zyborg) [NC]';

            $firewall_rules[] = 'RewriteCond %{REMOTE_HOST} (163data|amazonaws|colocrossing|crimea|g00g1e|justhost|kanagawa|loopia|masterhost|onlinehome|poneytel|sprintdatacenter|reverse.softlayer|safenet|ttnet|woodpecker|wowrack) [NC]';

            $firewall_rules[] = 'RewriteCond %{HTTP_REFERER} (semalt\.com|todaperfeita) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{HTTP_REFERER} (blue\spill|cocaine|ejaculat|erectile|erections|hoodia|huronriveracres|impotence|levitra|libido|lipitor|phentermin|pro[sz]ac|sandyauer|tramadol|troyhamby|ultram|unicauca|valium|viagra|vicodin|xanax|ypxaieo) [NC]';

            $firewall_rules[] = 'RewriteRule .* - [F,L]';
            $firewall_rules[] = '</IfModule>';
        }

        if ($options['firewall_directory_traversal']) {
            $firewall_rules[] = '<IfModule mod_rewrite.c>';

            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (((/|%2f){3,3})|((\.|%2e){3,3})|((\.|%2e){2,2})(/|%2f|%u2215)) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (/|%2f)(:|%3a)(/|%2f) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (/|%2f)(\*|%2a)(\*|%2a)(/|%2f) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (absolute_|base|root_)(dir|path)(=|%3d)(ftp|https?) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (/|%2f)(=|%3d|$&|_mm|cgi(\.|-)|inurl(:|%3a)(/|%2f)|(mod|path)(=|%3d)(\.|%2e)) [NC,OR]';

            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (\^|`|<|>|\\\\|\|) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} ([a-z0-9]{2000,}) [NC]';

            $firewall_rules[] = 'RewriteRule .* - [F,L]';
            $firewall_rules[] = '</IfModule>';
        }

        if ($options['firewall_xss']) {
            $firewall_rules[] = '<IfModule mod_rewrite.c>';

            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (<|%3c)(.*)(e|%65|%45)(m|%6d|%4d)(b|%62|%42)(e|%65|%45)(d|%64|%44)(.*)(>|%3e) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (<|%3c)(.*)(i|%69|%49)(f|%66|%46)(r|%72|%52)(a|%61|%41)(m|%6d|%4d)(e|%65|%45)(.*)(>|%3e) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (<|%3c)(.*)(o|%4f|%6f)(b|%62|%42)(j|%4a|%6a)(e|%65|%45)(c|%63|%43)(t|%74|%54)(.*)(>|%3e) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (<|%3c)(.*)(s|%73|%53)(c|%63|%43)(r|%72|%52)(i|%69|%49)(p|%70|%50)(t|%74|%54)(.*)(>|%3e) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (j|%6a|%4a)(a|%61|%41)(v|%76|%56)(a|%61|%31)(s|%73|%53)(c|%63|%43)(r|%72|%52)(i|%69|%49)(p|%70|%50)(t|%74|%54)(:|%3a)(.*)(;|%3b|\)|%29) [NC,OR]';

            $firewall_rules[] = 'RewriteCond %{HTTP_USER_AGENT} (&lt;|%0a|%0d|%27|%3c|%3e|%00|0x00) [NC]';

            $firewall_rules[] = 'RewriteRule .* - [F,L]';
            $firewall_rules[] = '</IfModule>';
        }

        if ($options['firewall_cache_poisoning']) {
            $firewall_rules[] = '<IfModule mod_rewrite.c>';

            $firewall_rules[] = 'RewriteCond %{REQUEST_METHOD} ^(connect|debug|move|trace|track) [NC]';

            $firewall_rules[] = 'RewriteRule .* - [F,L]';
            $firewall_rules[] = '</IfModule>';
        }

        if ($options['firewall_dual_header']) {
            $firewall_rules[] = '<IfModule mod_rewrite.c>';

            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} /((.*)header:|(.*)set-cookie:(.*)=) [NC,OR]';

            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} ((.*)header:|(.*)set-cookie:(.*)=) [NC]';

            $firewall_rules[] = 'RewriteRule .* - [F,L]';
            $firewall_rules[] = '</IfModule>';
        }

        if ($options['firewall_sql_injection']) {
            $firewall_rules[] = '<IfModule mod_rewrite.c>';

            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (order(\s|%20)by(\s|%20)1--) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (\+|%2b|%20)(d|%64|%44)(e|%65|%45)(l|%6c|%4c)(e|%65|%45)(t|%74|%54)(e|%65|%45)(\+|%2b|%20) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (\+|%2b|%20)(i|%69|%49)(n|%6e|%4e)(s|%73|%53)(e|%65|%45)(r|%72|%52)(t|%74|%54)(\+|%2b|%20) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (\+|%2b|%20)(s|%73|%53)(e|%65|%45)(l|%6c|%4c)(e|%65|%45)(c|%63|%43)(t|%74|%54)(\+|%2b|%20) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (\+|%2b|%20)(u|%75|%55)(p|%70|%50)(d|%64|%44)(a|%61|%41)(t|%74|%54)(e|%65|%45)(\+|%2b|%20) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (;|<|>|\\\'|\"|\)|%0a|%0d|%22|%27|%3c|%3e|%00)(.*)(/\*|alter|base64|benchmark|cast|concat|convert|create|encode|declare|delete|drop|insert|md5|request|script|select|set|union|update) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} ((\+|%2b)(concat|delete|get|select|union)(\+|%2b)) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (union)(.*)(select)(.*)(\(|%28) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (concat|eval)(.*)(\(|%28) [NC,OR]';

            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (/)?j((\s)+)?a((\s)+)?v((\s)+)?a((\s)+)?s((\s)+)?c((\s)+)?r((\s)+)?i((\s)+)?p((\s)+)?t((\s)+)?(%3a|:) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (/)(author-panel|class|database|(db|mysql)-?admin|filemanager|htdocs|httpdocs|https?|mailman|mailto|msoffice|_?php-my-admin(.*)|tmp|undefined|usage|var|vhosts|webmaster|www)(/) [NC,OR]';

            $firewall_rules[] = 'RewriteCond %{HTTP_REFERER} (order(\s|%20)by(\s|%20)1--) [NC]';

            $firewall_rules[] = 'RewriteRule .* - [F,L]';
            $firewall_rules[] = '</IfModule>';
        }

        if ($options['firewall_file_injection']) {
            $firewall_rules[] = '<IfModule mod_rewrite.c>';

            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (s)?(ftp|inurl|php)(s)?(:(/|%2f|%u2215)(/|%2f|%u2215)) [NC,OR]';

            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (=?\\\\(\\\'|%27)/?)(\.) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (///|\?\?|/&&|/\*(.*)\*/|/:/|\\\\\\\\|0x00|%00|%0d%0a) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (/)(::[0-9999]|%3a%3a[0-9999]|127\.0\.0\.1|localhost|makefile|pingserver|wwwroot)(/)? [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (\.)(7z|ab4|ace|afm|ashx|aspx?|bash|ba?k?|bin|bz2|cfg|cfml?|cgi|conf\b|config|ctl|dat|db|dist|dll|eml|engine|env|et2|exe|fec|fla|git|hg|inc|ini|inv|jsp|log|lqd|make|mbf|mdb|mmw|mny|module|old|one|orig|out|passwd|pdb|phtml|pl|profile|psd|pst|ptdb|pwd|py|qbb|qdf|rar|rdf|save|sdb|sql|sh|soa|svn|swf|swl|swo|swp|stx|tar|tax|tgz|theme|tls|tmd|wow|xtmpl|ya?ml|zlib)$ [NC]';

            $firewall_rules[] = 'RewriteRule .* - [F,L]';
            $firewall_rules[] = '</IfModule>';
        }

        if ($options['firewall_wordpress_exploits']) {
            $firewall_rules[] = '<IfModule mod_rewrite.c>';

            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (ckfinder|fckeditor|fullclick) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (thumbs?(_editor|open)?|tim(thumbs?)?)((\.|%2e)php) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (\.|20)(get|the)(_|%5f)(permalink|posts_page_url)(\(|%28) [NC,OR]';

            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (/)(vbulletin|boards|vbforum)(/)? [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (/)(ckfinder|fck|fckeditor|fullclick) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (thumbs?(_editor|open)?|tim(thumbs?)?)(\.php) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (\.|20)(get|the)(_)(permalink|posts_page_url)(\() [NC]';

            $firewall_rules[] = 'RewriteRule .* - [F,L]';
            $firewall_rules[] = '</IfModule>';
        }

        if ($options['firewall_php_exploits']) {
            $firewall_rules[] = '<IfModule mod_rewrite.c>';

            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} ([a-z0-9]{2000,}) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (`|<|>|\^|\|\\\\|0x00|%00|%0d%0a) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (localhost|127(\.|%2e)0(\.|%2e)0(\.|%2e)1) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (cmd|command)(=|%3d)(chdir|mkdir)(.*)(x20) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (benchmark|char|exec|fopen|function|html)(.*)(\(|%28)(.*)(\)|%29) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (php)([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (\\\\x00|(\"|%22|\\\'|%27)0(\"|%22|\\\'|%27)(=|%3d)(\"|%22|\\\'|%27)0|cast(\(|%28)0x|or%201(=|%3d)1) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (g|%67|%47)(l|%6c|%4c)(o|%6f|%4f)(b|%62|%42)(a|%61|%41)(l|%6c|%4c)(s|%73|%53)(=|\[|%[0-9A-Z]{0,2}) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (_|%5f)(r|%72|%52)(e|%65|%45)(q|%71|%51)(u|%75|%55)(e|%65|%45)(s|%73|%53)(t|%74|%54)(=|\[|%[0-9A-Z]{2,}) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (@copy|\$_(files|get|post)|allow_url_(fopen|include)|auto_prepend_file|blexbot|browsersploit|(c99|php)shell|curl(_exec|test)|disable_functions?|document_root|elastix|encodeuricom|exploit|fclose|fgets|file_put_contents|fputs|fsbuff|fsockopen|gethostbyname|grablogin|hmei7|input_file|open_basedir|outfile|passthru|phpinfo|popen|proc_open|quickbrute|remoteview|root_path|safe_mode|shell_exec|site((.){0,2})copier|sux0r|trojan|user_func_array|wget|xertive) [NC,OR]';

            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (/)(\*|\"|\\\'|\.|,|&|&amp;?)/?$ [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (\.)(php)(\()?([0-9]+)(\))?(/)?$ [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (\.(s?ftp-?)config|(s?ftp-?)config\.) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (\{0\}|\"?0\"?=\"?0|\(/\(|\.\.\.|\+\+\+|\\\\\\") [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (/%7e)(root|ftp|bin|nobody|named|guest|logs|sshd)(/) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (/)(etc|var)(/)(hidden|secret|shadow|ninja|passwd|tmp)(/)?$ [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (s)?(ftp|http|inurl|php)(s)?(:(/|%2f|%u2215)(/|%2f|%u2215)) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (/)(=|\$&?|&?(pws|rk)=0|_mm|_vti_|cgi(\.|-)?|(=|/|;|,)nt\.) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (\.)(ds_store|htaccess|htpasswd|init?|mysql-select-db)(/)?$ [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (/)(bin)(/)(cc|chmod|chsh|cpp|echo|id|kill|mail|nasm|perl|ping|ps|python|tclsh)(/)?$ [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (\(null\)|\{\$itemURL\}|cAsT\(0x|echo(.*)kae|etc/passwd|eval\(|self/environ|\+union\+all\+select) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (/)(awstats|(c99|php|web)shell|document_root|error_log|listinfo|muieblack|remoteview|site((.){0,2})copier|sqlpatch|sux0r) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (/)((php|web)?shell|crossdomain|fileditor|locus7|nstview|php(get|remoteview|writer)|r57|remview|sshphp|storm7|webadmin)(.*)(\.|\() [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (base64_(en|de)code|benchmark|child_terminate|curl_exec|e?chr|eval|function|fwrite|(f|p)open|html|leak|passthru|p?fsockopen|phpinfo|posix_(kill|mkfifo|setpgid|setsid|setuid)|proc_(close|get_status|nice|open|terminate)|(shell_)?exec|system)(.*)(\()(.*)(\)) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{REQUEST_URI} (/)(^$|00.temp00|0day|3index|3xp|70bex?|admin_events|bkht|(php|web)?shell|c99|config(\.)?bak|curltest|db|dompdf|filenetworks|hmei7|index\.php/index\.php/index|jahat|kcrew|keywordspy|libsoft|marg|mobiquo|mysql|nessus|php-?info|racrew|sql|vuln|(web-?|wp-)?(conf\b|config(uration)?)|xertive)(\.php) [NC,OR]';

            $firewall_rules[] = 'RewriteCond %{HTTP_USER_AGENT} ([a-z0-9]{2000,}) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{HTTP_USER_AGENT} (base64_decode|bin/bash|disconnect|eval|lwp-download|unserialize|\\\\\\x22) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{HTTP_USER_AGENT} ((c99|php|web)shell|remoteview|site((.){0,2})copier) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{HTTP_USER_AGENT} (base64_decode|bin/bash|disconnect|eval|lwp-download|unserialize|\\\\\\x22) [NC]';

            $firewall_rules[] = 'RewriteRule .* - [F,L]';
            $firewall_rules[] = '</IfModule>';
        }

        if ($options['firewall_php_info']) {
            $firewall_rules[] = '<IfModule mod_rewrite.c>';

            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (globals|mosconfig([a-z_]{1,22})|request)(=|\[) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (/|%2f)((wp-)?config)((\.|%2e)inc)?((\.|%2e)php) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} ((boot|win)((\.|%2e)ini)|etc(/|%2f)passwd|self(/|%2f)environ) [NC,OR]';
            $firewall_rules[] = 'RewriteCond %{QUERY_STRING} (e|%65|%45)(v|%76|%56)(a|%61|%31)(l|%6c|%4c)(.*)(\(|%28)(.*)(\)|%29) [NC]';

            $firewall_rules[] = 'RewriteRule .* - [F,L]';
            $firewall_rules[] = '</IfModule>';
        }


        $firewall_rules[] = '# END Login Lockdown Firewall';

        $htaccess = implode(PHP_EOL, $firewall_rules) . PHP_EOL . $htaccess;

        if (count($firewall_rules) > 2) {
            $firewall_test = self::firewall_test_htaccess($htaccess);
            if (is_wp_error($firewall_test)) {
                LoginLockdown_Utility::display_notice(
                    $firewall_test->get_error_message(),
                    "error"
                );
            } else {
                self::$wp_filesystem->put_contents(LoginLockdown_Utility::get_home_path() . '.htaccess', $htaccess);
            }
        }
    }

    static function firewall_test_htaccess($new_content)
    {
        $uploads_directory = wp_upload_dir();
        $test_id = rand(1000, 9999);
        $htaccess_test_folder = $uploads_directory['basedir'] . '/htaccess-test-' . $test_id . '/';
        $htaccess_test_url = $uploads_directory['baseurl'] . '/htaccess-test-' . $test_id . '/';

        // Create test directory and files
        if (!self::$wp_filesystem->is_dir($htaccess_test_folder)) {
            if (true !== self::$wp_filesystem->mkdir($htaccess_test_folder, 0777)) {
                return new WP_Error('firewall_failed', 'Failed to create test directory. Please check that your uploads folder is writable.', false);
            }
        }

        if (true !== self::$wp_filesystem->put_contents($htaccess_test_folder . 'index.html', 'htaccess-test-' . $test_id)) {
            return new WP_Error('firewall_failed', 'Failed to create test files. Please check that your uploads folder is writable.', false);
        }

        if (true !== self::$wp_filesystem->put_contents($htaccess_test_folder . '.htaccess', $new_content)) {
            return new WP_Error('firewall_failed', 'Failed to create test directory and files. Please check that your uploads folder is writeable.', false);
        }

        // Retrieve test file over http
        $response = wp_remote_get($htaccess_test_url . 'index.html', array('sslverify' => false, 'redirection' => 0));
        $response_code = wp_remote_retrieve_response_code($response);

        // Remove Test Directory
        self::$wp_filesystem->delete($htaccess_test_folder . '.htaccess');
        self::$wp_filesystem->delete($htaccess_test_folder . 'index.html');
        self::$wp_filesystem->rmdir($htaccess_test_folder);

        // Check if test file content is what we expect
        if ((in_array($response_code, range(200, 299)) && !is_wp_error($response) && wp_remote_retrieve_body($response) == 'htaccess-test-' . $test_id) || (in_array($response_code, range(300, 399)) && !is_wp_error($response))) {
            return true;
        } else {
            return new WP_Error('firewall_failed', 'Unfortunately it looks like installing these firewall rules could cause your entire site, including the admin, to become inaccessible. Fix the errors before saving', false);
        }
    }

    static function firewall_remove_rules()
    {

        if (self::$wp_filesystem->is_writable(LoginLockdown_Utility::get_home_path() . '.htaccess')) {

            $htaccess_rules = self::$wp_filesystem->get_contents(LoginLockdown_Utility::get_home_path() . '.htaccess');

            if ($htaccess_rules) {
                $htaccess_rules = explode(PHP_EOL, $htaccess_rules);
                $found = false;
                $new_content = '';

                foreach ($htaccess_rules as $htaccess_rule) {
                    if ($htaccess_rule == '# BEGIN Login Lockdown Firewall') {
                        $found = true;
                    }

                    if (!$found) {
                        $new_content .= $htaccess_rule . PHP_EOL;
                    }

                    if ($htaccess_rule == '# END Login Lockdown Firewall') {
                        $found = false;
                    }
                }

                $new_content = trim($new_content, PHP_EOL);

                $f = @fopen(LoginLockdown_Utility::get_home_path() . '.htaccess', 'w');
                self::$wp_filesystem->put_contents(LoginLockdown_Utility::get_home_path() . '.htaccess', $new_content);

                return true;
            }
        }

        return false;
    }

    /**
     * Actions on plugin activation
     *
     * @since 5.0
     *
     * @return null
     *
     */
    static function activate()
    {
        self::create_custom_tables();
        LoginLockdown_Admin::reset_pointers();
    } // activate


    /**
     * Actions on plugin deactivaiton
     *
     * @since 5.0
     *
     * @return null
     *
     */
    static function deactivate()
    {
    } // deactivate

    /**
     * Actions on plugin uninstall
     *
     * @since 5.0
     *
     * @return null
     */
    static function uninstall()
    {
        global $wpdb;

        $options = get_option(LOGINLOCKDOWN_OPTIONS_KEY, array());

        if ($options['uninstall_delete'] == '1') {
            delete_option(LOGINLOCKDOWN_OPTIONS_KEY);
            delete_option(LOGINLOCKDOWN_META_KEY);
            delete_option(LOGINLOCKDOWN_POINTERS_KEY);
            delete_option(LOGINLOCKDOWN_NOTICES_KEY);
            delete_option('wf_licensing_loginlockdown');

            $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "login_fails");
            $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "lockdowns");
        }
    } // uninstall
} // class
