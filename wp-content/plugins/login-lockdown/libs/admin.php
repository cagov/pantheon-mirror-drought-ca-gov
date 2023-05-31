<?php

/**
 * Login Lockdown Pro
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_Admin extends LoginLockdown
{

    /**
     * Enqueue Admin Scripts
     *
     * @since 5.0
     *
     * @return null
     */
    static function admin_enqueue_scripts($hook)
    {
        global $loginlockdown_licensing;

        $options = LoginLockdown_Setup::get_options();

        if ('settings_page_loginlockdown' == $hook) {
            wp_enqueue_style('loginlockdown-admin', LOGINLOCKDOWN_PLUGIN_URL . 'css/loginlockdown.css', array(), self::$version);
            wp_enqueue_style('loginlockdown-dataTables', LOGINLOCKDOWN_PLUGIN_URL . 'css/jquery.dataTables.min.css', array(), self::$version);
            wp_enqueue_style('loginlockdown-select2', LOGINLOCKDOWN_PLUGIN_URL . 'css/select2.css', array(), self::$version);
            wp_enqueue_style('loginlockdown-sweetalert', LOGINLOCKDOWN_PLUGIN_URL . 'css/sweetalert2.min.css', array(), self::$version);
            wp_enqueue_style('loginlockdown-tooltipster', LOGINLOCKDOWN_PLUGIN_URL . 'css/tooltipster.bundle.min.css', array(), self::$version);
            wp_enqueue_style('wp-color-picker');

            wp_enqueue_script('jquery-ui-tabs');
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-position');
            wp_enqueue_script('jquery-effects-core');
            wp_enqueue_script('jquery-effects-blind');
            wp_enqueue_script('loginlockdown-tooltipster', LOGINLOCKDOWN_PLUGIN_URL . 'js/tooltipster.bundle.min.js', array('jquery'), self::$version, true);
            wp_enqueue_script('loginlockdown-dataTables', LOGINLOCKDOWN_PLUGIN_URL . 'js/jquery.dataTables.min.js', array(), self::$version, true);
            wp_enqueue_script('loginlockdown-select2', LOGINLOCKDOWN_PLUGIN_URL . 'js/select2.js', array(), self::$version, true);
            wp_enqueue_script('loginlockdown-chart', LOGINLOCKDOWN_PLUGIN_URL . 'js/chart.min.js', array(), self::$version, true);
            wp_enqueue_script('loginlockdown-moment', LOGINLOCKDOWN_PLUGIN_URL . 'js/moment.min.js', array(), self::$version, true);
            wp_enqueue_script('loginlockdown-d3', LOGINLOCKDOWN_PLUGIN_URL . 'js/d3.min.js', array(), self::$version, true);
            wp_enqueue_script('loginlockdown-topojson', LOGINLOCKDOWN_PLUGIN_URL . 'js/topojson.min.js', array(), self::$version, true);
            wp_enqueue_script('loginlockdown-worldmap', LOGINLOCKDOWN_PLUGIN_URL . 'js/datamaps.world.min.js', array(), self::$version, true);
            wp_enqueue_script('loginlockdown-sweetalert', LOGINLOCKDOWN_PLUGIN_URL . 'js/sweetalert2.min.js', array(), self::$version, true);
            wp_enqueue_script('loginlockdown-ace-editor', LOGINLOCKDOWN_PLUGIN_URL . 'js/editor/ace.js', false, self::$version, true);

            wp_enqueue_script('wp-color-picker');
            wp_enqueue_media();

            $current_user = wp_get_current_user();
            $license = $loginlockdown_licensing->get_license();
            $support_text = 'My site details: WP ' . get_bloginfo('version') . ', Login Lockdown v' . self::$version . ', ';
            if (!empty($license['license_key'])) {
                $support_text .= 'license key: ' . $license['license_key'] . '.';
            } else {
                $support_text .= 'no license info.';
            }
            if (strtolower($current_user->display_name) != 'admin' && strtolower($current_user->display_name) != 'administrator') {
                $support_name = $current_user->display_name;
            } else {
                $support_name = '';
            }

            $js_localize = array(
                'undocumented_error' => __('An undocumented error has occurred. Please refresh the page and try again.', 'login-lockdown'),
                'documented_error' => __('An error has occurred.', 'login-lockdown'),
                'plugin_name' => __('Login Lockdown PRO', 'login-lockdown'),
                'plugin_url' => LOGINLOCKDOWN_PLUGIN_URL,
                'icon_url' => LOGINLOCKDOWN_PLUGIN_URL . 'images/loginlockdown-loader.gif',
                'settings_url' => admin_url('options-general.php?page=loginlockdown'),
                'version' => self::$version,
                'site' => get_home_url(),
                'url' => LOGINLOCKDOWN_PLUGIN_URL,
                'support_text' => $support_text,
                'support_name' => $support_name,
                'nonce_lc_check' => wp_create_nonce('loginlockdown_save_lc'),
                'cancel_button' => __('Cancel', 'login-lockdown'),
                'ok_button' => __('OK', 'login-lockdown'),
                'run_tool_nonce' => wp_create_nonce('loginlockdown_run_tool'),
                'stats_unavailable' => 'Stats will be available once enough data is collected.',
                'stats_locks' => LoginLockdown_Stats::get_stats('locks'),
                'stats_fails' => LoginLockdown_Stats::get_stats('fails'),
                'stats_locks_devices' => LoginLockdown_Stats::get_device_stats('locks'),
                'stats_fails_devices' => LoginLockdown_Stats::get_device_stats('fails'),
                'stats_map' => LoginLockdown_Stats::get_map_stats('fails'),
                'rebranded' => (self::get_rebranding() !== false ? true : false),
                'whitelabel' => LoginLockdown_Utility::whitelabel_filter(),
                'is_active' => $loginlockdown_licensing->is_active()
            );

            if (self::get_rebranding() !== false) {
                $brand_color = self::get_rebranding('color');
                if (empty($brand_color)) {
                    $brand_color = '#ff6144';
                }
                $js_localize['chart_colors'] = array($brand_color, self::color_luminance($brand_color, 0.2), self::color_luminance($brand_color, 0.4), self::color_luminance($brand_color, 0.6));
            } else {
                $js_localize['chart_colors'] = array('#29b99a', '#ff5429', '#ff7d5c', '#ffac97');
            }

            wp_enqueue_script('loginlockdown-admin', LOGINLOCKDOWN_PLUGIN_URL . 'js/loginlockdown.js', array('jquery'), self::$version, true);
            wp_localize_script('loginlockdown-admin', 'loginlockdown_vars', $js_localize);

            // fix for aggressive plugins that include their CSS or JS on all pages
            wp_dequeue_style('uiStyleSheet');
            wp_dequeue_style('wpcufpnAdmin');
            wp_dequeue_style('unifStyleSheet');
            wp_dequeue_style('wpcufpn_codemirror');
            wp_dequeue_style('wpcufpn_codemirrorTheme');
            wp_dequeue_style('collapse-admin-css');
            wp_dequeue_style('jquery-ui-css');
            wp_dequeue_style('tribe-common-admin');
            wp_dequeue_style('file-manager__jquery-ui-css');
            wp_dequeue_style('file-manager__jquery-ui-css-theme');
            wp_dequeue_style('wpmegmaps-jqueryui');
            wp_dequeue_style('wp-botwatch-css');
            wp_dequeue_style('njt-filebird-admin');
            wp_dequeue_style('ihc_jquery-ui.min.css');
            wp_dequeue_style('badgeos-juqery-autocomplete-css');
            wp_dequeue_style('mainwp');
            wp_dequeue_style('mainwp-responsive-layouts');
            wp_dequeue_style('jquery-ui-style');
            wp_dequeue_style('additional_style');
            wp_dequeue_style('wobd-jqueryui-style');
            wp_dequeue_style('wpdp-style3');
            wp_dequeue_style('jquery_smoothness_ui');
            wp_dequeue_style('uap_main_admin_style');
            wp_dequeue_style('uap_font_awesome');
            wp_dequeue_style('uap_jquery-ui.min.css');
            wp_dequeue_style('wqm-select2-style');

            wp_deregister_script('wqm-select2-script');
        }

        $rebranding = false;
        if ($hook == 'plugins.php') {
            $rebranding = self::get_rebranding();
        }

        $pointers = get_option(LOGINLOCKDOWN_POINTERS_KEY);
        if ('settings_page_loginlockdown' != $hook) {
            if ($pointers) {
                $pointers['run_tool_nonce'] = wp_create_nonce('loginlockdown_run_tool');
                wp_enqueue_script('wp-pointer');
                wp_enqueue_style('wp-pointer');
                wp_localize_script('wp-pointer', 'loginlockdown_pointers', $pointers);
            }

            if ($pointers || false !== $rebranding) {
                wp_enqueue_script('loginlockdown-admin-global', LOGINLOCKDOWN_PLUGIN_URL . 'js/loginlockdown-global.js', array('jquery'), self::$version, true);
            }

            if (false !== $rebranding) {
                wp_localize_script('loginlockdown-admin-global', 'loginlockdown_rebranding', $rebranding);
            }
        }
    } // admin_enqueue_scripts

    static function admin_notices()
    {
        $notices = get_option(LOGINLOCKDOWN_NOTICES_KEY);

        if (is_array($notices)) {
            foreach ($notices as $id => $notice) {
                echo '<div class="notice-' . $notice['type'] . ' notice is-dismissible"><p>' . $notice['text'] . '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></p></div>';
                if ($notice['once'] == true) {
                    unset($notices[$id]);
                    update_option(LOGINLOCKDOWN_NOTICES_KEY, $notices);
                }
            }
        }
    } // notices

    static function add_notice($id = false, $text = '', $type = 'warning', $show_once = false)
    {
        if ($id) {
            $notices = get_option(LOGINLOCKDOWN_NOTICES_KEY, array());
            $notices[$id] = array('text' => $text, 'type' => $type, 'once' => $show_once);
            update_option(LOGINLOCKDOWN_NOTICES_KEY, $notices);
        }
    }

    static function admin_bar()
    {
        global $wp_admin_bar, $loginlockdown_licensing;

        $options = LoginLockdown_Setup::get_options();

        if (
            !$options['show_admin_menu'] ||
            !$loginlockdown_licensing->is_active() ||
            false === current_user_can('administrator') ||
            false === apply_filters('wp_force_ssl_show_admin_bar', true)
        ) {
            return;
        }

        $plugin_name = self::get_rebranding('name');
        if ($plugin_name == false) {
            $plugin_name = 'Login Lockdown';
        }

        $plugin_logo = self::get_rebranding('logo_url');
        if ($plugin_logo == false) {
            $plugin_logo = LOGINLOCKDOWN_PLUGIN_URL . 'images/loginlockdown-icon.png';
        }

        $title = '<div class="loginlockdown-adminbar-icon" style="display:inline-block;"><img style="height: 22px; padding: 4px; margin-bottom: -10px;  filter: invert(1) brightness(1.2) grayscale(1);" src="' . $plugin_logo . '" alt="' . $plugin_name . '" title="' . $plugin_name . '"></div> <span class="ab-label">' . $plugin_name . '</span>';

        $wp_admin_bar->add_node(array(
            'id'    => 'loginlockdown-ab',
            'title' => $title,
            'href'  => '#',
            'parent' => '',
        ));

        $wp_admin_bar->add_node(array(
            'id'    => 'loginlockdown-login-protection',
            'title' => 'Login Protection',
            'href'  => admin_url('options-general.php?page=loginlockdown#loginlockdown_login_form'),
            'parent' => 'loginlockdown-ab'
        ));

        $wp_admin_bar->add_node(array(
            'id'    => 'loginlockdown-activity',
            'title' => 'Activity',
            'href'  => admin_url('options-general.php?page=loginlockdown#loginlockdown_activity'),
            'parent' => 'loginlockdown-ab'
        ));

        $wp_admin_bar->add_node(array(
            'id'    => 'loginlockdown-geoip',
            'title' => 'GeoIP',
            'href'  => admin_url('options-general.php?page=loginlockdown#loginlockdown_geoip'),
            'parent' => 'loginlockdown-ab'
        ));

        $wp_admin_bar->add_node(array(
            'id'    => 'loginlockdown-2fa',
            'title' => '2FA',
            'href'  => admin_url('options-general.php?page=loginlockdown#loginlockdown_2FA'),
            'parent' => 'loginlockdown-ab'
        ));

        $wp_admin_bar->add_node(array(
            'id'    => 'loginlockdown-captcha',
            'title' => 'Captcha',
            'href'  => admin_url('options-general.php?page=loginlockdown#loginlockdown_captcha'),
            'parent' => 'loginlockdown-ab'
        ));
    } // admin_bar

    /**
     * Admin menu entry
     *
     * @since 5.0
     *
     * @return null
     */
    static function admin_menu()
    {

        $page_title = self::get_rebranding('name');
        if ($page_title === false || empty($plugin_name)) {
            $page_title = 'Login Lockdown PRO';
        }

        $menu_title = self::get_rebranding('short_name');
        if ($menu_title === false || empty($menu_title)) {
            $menu_title = '<span>Login Lockdown <span style="color: #29b99a; vertical-align: super; font-size: 9px;">PRO</span></span>';
        }

        add_options_page(
            __($page_title, 'login-lockdown'),
            $menu_title,
            'manage_options',
            'loginlockdown',
            array(__CLASS__, 'main_page')
        );
    } // admin_menu

    /**
     * Add settings link to plugins page
     *
     * @since 5.0
     *
     * @return null
     */
    static function plugin_action_links($links)
    {
        $plugin_name = self::get_rebranding('name');
        if ($plugin_name === false || empty($plugin_name)) {
            $plugin_name = __('Login Lockdown PRO Settings', 'login-lockdown');
        }

        $settings_link = '<a href="' . admin_url('options-general.php?page=loginlockdown') . '" title="' . $plugin_name . '">' . __('Settings', 'login-lockdown') . '</a>';
        array_unshift($links, $settings_link);

        return $links;
    } // plugin_action_links

    /**
     * Add links to plugin's description in plugins table
     *
     * @since 5.0
     *
     * @return null
     */
    static function plugin_meta_links($links, $file)
    {
        if ($file !== 'login-lockdown/loginlockdown.php') {
            return $links;
        }

        if (self::get_rebranding('url')) {
            unset($links[1]);
            unset($links[2]);

            $links[] = '<a target="_blank" href="' . self::get_rebranding('company_url') . '" title="Get help">' . self::get_rebranding('company_name') . '</a>';
            $links[] = '<a target="_blank" href="' . self::get_rebranding('url') . '" title="Get help">Support</a>';
        } else {
            $support_link = '<a href="https://wploginlockdown.com/support/" title="' . __('Get help', 'login-lockdown') . '">' . __('Support', 'login-lockdown') . '</a>';
            $links[] = $support_link;
        }

        if (!LoginLockdown_Utility::whitelabel_filter()) {
            unset($links[1]);
            unset($links[2]);
            unset($links[3]);
        }

        return $links;
    } // plugin_meta_links

    /**
     * Admin footer text
     *
     * @since 5.0
     *
     * @return null
     */
    static function admin_footer_text($text)
    {
        if (!self::is_plugin_page() || !LoginLockdown_Utility::whitelabel_filter()) {
            return $text;
        }

        if (self::get_rebranding()) {
            $text = '<i class="loginlockdown-footer"><a href="' . self::get_rebranding('company_url') . '" title="Visit ' . self::get_rebranding('name') . ' page for more info" target="_blank">' . self::get_rebranding('name') . '</a> v' . self::$version . '. ' . self::get_rebranding('footer_text') . '</i>';
        } else {
            $text = '<i class="loginlockdown-footer">Login Lockdown v' . self::$version . ' <a href="' . self::generate_web_link('admin_footer') . '" title="Visit Login Lockdown PRO page for more info" target="_blank">WebFactory Ltd</a>. Please <a target="_blank" href="https://wordpress.org/support/plugin/login-lockdown/reviews/#new-post" title="Rate the plugin">rate the plugin <span>★★★★★</span></a> to help us spread the word. Thank you 🙌 from the WebFactory team!</i>';
        }

        echo '<script type="text/javascript">!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});</script>';

        return $text;
    } // admin_footer_text

    /**
     * Helper function for generating UTM tagged links
     *
     * @param string  $placement  Optional. UTM content param.
     * @param string  $page       Optional. Page to link to.
     * @param array   $params     Optional. Extra URL params.
     * @param string  $anchor     Optional. URL anchor part.
     *
     * @return string
     */
    static function generate_web_link($placement = '', $page = '/', $params = array(), $anchor = '')
    {
        $base_url = 'https://wploginlockdown.com';

        if ('/' != $page) {
            $page = '/' . trim($page, '/') . '/';
        }
        if ($page == '//') {
            $page = '/';
        }

        $parts = array_merge(array('utm_source' => 'login-lockdown', 'utm_medium' => 'plugin', 'utm_content' => $placement, 'utm_campaign' => 'loginlockdown-pro-v' . self::$version), $params);

        if (!empty($anchor)) {
            $anchor = '#' . trim($anchor, '#');
        }

        $out = $base_url . $page . '?' . http_build_query($parts, '', '&amp;') . $anchor;

        return $out;
    } // generate_web_link

    /**
     * Change luminance of a hex color
     *
     * @since 5.0
     *
     * @param string hex color
     * @param int percent to adjust color luminance by
     *
     * @return string new hex color
     */
    static function color_luminance($hex, $percent)
    {
        $hex = preg_replace('/[^0-9a-f]/i', '', $hex);
        $new_hex = '#';

        if (strlen($hex) < 6) {
            $hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
        }

        // convert to decimal and change luminosity
        for ($i = 0; $i < 3; $i++) {
            $dec = hexdec(substr($hex, $i * 2, 2));
            $dec = min(max(0, $dec + $dec * $percent), 255);
            $new_hex .= str_pad(dechex($dec), 2, 0, STR_PAD_LEFT);
        }

        return $new_hex;
    }

    /**
     * Helper function for generating dashboard UTM tagged links
     *
     * @param string  $placement  Optional. UTM content param.
     * @param string  $page       Optional. Page to link to.
     * @param array   $params     Optional. Extra URL params.
     * @param string  $anchor     Optional. URL anchor part.
     *
     * @return string
     */
    static function generate_dashboard_link($placement = '', $page = '/', $params = array(), $anchor = '')
    {
        $base_url = 'https://dashboard.wploginlockdown.com';

        if ('/' != $page) {
            $page = '/' . trim($page, '/') . '/';
        }
        if ($page == '//') {
            $page = '/';
        }

        $parts = array_merge(array('utm_source' => 'loginlockdown-pro', 'utm_medium' => 'plugin', 'utm_content' => $placement, 'utm_campaign' => 'loginlockdown-pro-v' . self::$version), $params);

        if (!empty($anchor)) {
            $anchor = '#' . trim($anchor, '#');
        }

        $out = $base_url . $page . '?' . http_build_query($parts, '', '&amp;') . $anchor;

        return $out;
    } // generate_dashboard_link

    /**
     * Test if we're on plugin's page
     *
     * @since 5.0
     *
     * @return null
     */
    static function is_plugin_page()
    {
        $current_screen = get_current_screen();

        if ($current_screen->id == 'settings_page_loginlockdown') {
            return true;
        } else {
            return false;
        }
    } // is_plugin_page

    /**
     * Settings Page HTML
     *
     * @since 5.0
     *
     * @return null
     */
    static function main_page()
    {
        global $loginlockdown_licensing;

        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }

        if (self::get_rebranding()) {
            echo '<style>';

            echo self::get_rebranding('admin_css_predefined');
            echo self::get_rebranding('admin_css');

            echo '</style>';
        }

        $options = LoginLockdown_Setup::get_options();

        // auto remove welcome pointer when options are opened
        $pointers = get_option(LOGINLOCKDOWN_POINTERS_KEY);
        if (isset($pointers['welcome'])) {
            unset($pointers['welcome']);
            update_option(LOGINLOCKDOWN_POINTERS_KEY, $pointers);
        }

        $plugin_name = self::get_rebranding('name');
        if ($plugin_name === false || empty($plugin_name)) {
            $plugin_name = 'Login Lockdown PRO';
        }

        echo '<div class="wrap">
        <div class="loginlockdown-header">
            <div class="loginlockdown-logo">';
        if (self::get_rebranding('logo_url') !== false) {
            echo '<img src="' . self::get_rebranding('logo_url') . '" />';
        } else {
            echo '<img src="' . LOGINLOCKDOWN_PLUGIN_URL . '/images/loginlockdown-logo.png" alt="Login Lockdown PRO" height="60" title="Login Lockdown PRO">';
        }
        echo '</div>';

        $stats = array();
        $stats['locks24'] = LoginLockdown_Stats::get_stats('locks', 1);
        $stats['locks'] =   LoginLockdown_Stats::get_stats('locks', 360);
        $stats['fails'] =   LoginLockdown_Stats::get_stats('fails', 1);

        echo '<div class="loginlockdown-header-stat">';
        echo '<div class="stat-title">Failed logins<span>in last 24h</span></div>';
        echo '<div class="stat-value" ' . ($stats['fails']['total'] == 0 ? 'style="color:#cfd4da"' : '') . '>' . $stats['fails']['total'] . '</div>';
        echo '</div>';

        echo '<div class="loginlockdown-header-stat">';
        echo '<div class="stat-title">Lockdowns<span>since plugin installed</span></div>';
        echo '<div class="stat-value" ' . ($stats['locks']['total'] == 0 ? 'style="color:#cfd4da"' : '') . '>' . $stats['locks']['total'] . '</div>';
        echo '</div>';

        echo '<div class="loginlockdown-header-stat loginlockdown-header-stat-last">';
        echo '<div class="stat-title">Lockdowns<span>in last 24h</span></div>';
        echo '<div class="stat-value" ' . ($stats['locks24']['total'] == 0 ? 'style="color:#cfd4da"' : '') . '>' . $stats['locks24']['total'] . '</div>';
        echo '</div>';


        echo '</div>';

        echo '<h1></h1>';

        echo '<form method="post" action="options.php" enctype="multipart/form-data" id="loginlockdown_form">';
        settings_fields(LOGINLOCKDOWN_OPTIONS_KEY);
        $license = $loginlockdown_licensing->get_license();
        $tabs = array();
        if ($loginlockdown_licensing->is_active()) {
            $tabs[] = array('id' => 'loginlockdown_login_form', 'icon' => 'loginlockdown-icon loginlockdown-enter', 'class' => '', 'label' => __('Login Protection', 'login-lockdown'), 'callback' => array('LoginLockdown_Tab_Login_Form', 'display'));
            $tabs[] = array('id' => 'loginlockdown_activity', 'icon' => 'loginlockdown-icon loginlockdown-log', 'class' => '', 'label' => __('Activity', 'login-lockdown'), 'callback' => array('LoginLockdown_Tab_Activity', 'display'));
            $tabs[] = array('id' => 'loginlockdown_firewall', 'icon' => 'loginlockdown-icon loginlockdown-check', 'class' => '', 'label' => __('Firewall', 'login-lockdown'), 'callback' => array('LoginLockdown_Tab_Firewall', 'display'));
            $tabs[] = array('id' => 'loginlockdown_geoip', 'icon' => 'loginlockdown-icon loginlockdown-globe', 'class' => '', 'label' => __('Country Blocking', 'login-lockdown'), 'callback' => array('LoginLockdown_Tab_GeoIP', 'display'));
            $tabs[] = array('id' => 'loginlockdown_design', 'icon' => 'loginlockdown-icon loginlockdown-settings', 'class' => '', 'label' => __('Design', 'login-lockdown'), 'callback' => array('LoginLockdown_Tab_Design', 'display'));
            $tabs[] = array('id' => 'loginlockdown_temp_access', 'icon' => 'loginlockdown-icon loginlockdown-hour-glass', 'class' => '', 'label' => __('Temp Access', 'login-lockdown'), 'callback' => array('LoginLockdown_Tab_Temporary_Access', 'display'));

            if (LoginLockdown_Utility::whitelabel_filter()) {
                $tabs[] = array('id' => 'loginlockdown_license', 'icon' => 'loginlockdown-icon loginlockdown-key', 'class' => '', 'label' => __('License', 'login-lockdown'), 'callback' => array('LoginLockdown_Tab_License', 'display'));
            }
        } else {
            $tabs[] = array('id' => 'loginlockdown_license', 'icon' => 'loginlockdown-icon loginlockdown-key', 'class' => '', 'label' => __('License', 'login-lockdown'), 'callback' => array('LoginLockdown_Tab_License', 'display'));
        }

        $tabs = apply_filters('loginlockdown_tabs', $tabs);

        echo '<div id="loginlockdown_tabs" class="ui-tabs" style="display: none;">';
        echo '<ul class="loginlockdown-main-tab">';
        foreach ($tabs as $tab) {
            echo '<li><a href="#' . $tab['id'] . '" class="' . $tab['class'] . '"><span class="icon"><i class="' . $tab['icon'] . '"></i></span></span><span class="label">' . $tab['label'] . '</span></a></li>';
        }
        echo '</ul>';

        foreach ($tabs as $tab) {
            if (is_callable($tab['callback'])) {
                echo '<div style="display: none;" id="' . $tab['id'] . '">';
                call_user_func($tab['callback']);
                echo '</div>';
            } else {
                echo $tab['id'] . 'NF';
            }
        } // foreach

        echo '</div>'; // loginlockdown_tabs
        echo '</form>';

        echo '</div>'; // wrap

        if ($loginlockdown_licensing->is_active()) {
            self::show_wizard($options['wizard_complete']);
        }
    } // options_page

    /**
     * Reset pointers
     *
     * @since 5.0
     *
     * @return null
     */
    static function reset_pointers()
    {
        $pointers = array();
        $pointers['welcome'] = array('target' => '#menu-settings', 'edge' => 'left', 'align' => 'right', 'content' => 'Thank you for installing the <b style="font-weight: 800; font-variant: small-caps;">Login Lockdown Pro</b> plugin! Please open <a href="' . admin_url('options-general.php?page=loginlockdown') . '">Settings - Login Lockdown Pro</a> to set up your lockdown settings.');

        update_option(LOGINLOCKDOWN_POINTERS_KEY, $pointers);
    } // reset_pointers

    /**
     * Settings footer submit button HTML
     *
     * @since 5.0
     *
     * @return null
     */
    static function footer_save_button()
    {
        echo '<p class="submit">';
        echo '<button class="button button-primary button-large">' . __('Save Changes', 'login-lockdown') . ' <i class="loginlockdown-icon loginlockdown-checkmark"></i></button>';
        echo '</p>';
    } // footer_save_button

    /**
     * Get rebranding data, return false if not rebranded
     *
     * @since 5.0
     *
     * @return bool|string requested property or false if not rebranded
     */
    static function get_rebranding($key = false)
    {
        global $loginlockdown_licensing;

        $license = $loginlockdown_licensing->get_license();

        if (is_array($license) && array_key_exists('meta', $license) && is_array($license['meta']) && array_key_exists('rebrand', $license['meta']) && !empty($license['meta']['rebrand'])) {
            if (!empty($key)) {
                return $license['meta']['rebrand'][$key];
            }
            return $license['meta']['rebrand'];
        } else {
            return false;
        }
    }

    static function show_wizard($hidden = 1)
    {
        $options = LoginLockdown_Setup::get_options();

        echo '<div class="loginlockdown-wizard-wrapper" ' . ($hidden ? 'style="display:none;"' : '') . '>';
        echo '<div class="loginlockdown-wizard-popup">';
        echo '<h2 align="center">Welcome</h2>';
        echo '<p>Thank you for installing Login Lockdown PRO!</p>';
        echo '<p style="color:red;font-weight:bold;">IMPORTANT! In case you lock yourself out and need to whitelist your IP address, please save the link below somewhere safe:</p>';
        echo '<a href="' . site_url('/?loginlockdown_unblock=' . $options['global_unblock_key']) . '">' . site_url('/loginlockdown_unblock=' . $options['global_unblock_key']) . '</a>';
        /*
                echo '<p>To help you get going fast, we have created a few configuration sets that you can deploy by clicking on the one that fits your website best:</p>';
                echo '<div class="loginlockdown-wizard-button" data-config="personal">';
                    echo '<h2>Company website/Personal Blog</h2>';
                    echo '<p>Select this if you have a personal website that only you manage from a single administrator account</p>';
                echo '</div>';

                echo '<div class="loginlockdown-wizard-button" data-config="medium">';
                    echo '<h2>Medium website</h2>';
                    echo '<p>Select this if you have a medium website where multiple people have accounts</p>';
                echo '</div>';

                echo '<div class="loginlockdown-wizard-button" data-config="store">';
                    echo '<h2>Online Store/Forum</h2>';
                    echo '<p>Select this if you have a store or forum where a lot of users have accounts</p>';
                echo '</div>';
                */

        echo '<br><br><button class="loginlockdown-wizard-button button button-gray button-large" data-config="skip" style="margin:0 auto; display: block; width: 120px;">Close</button>';
        echo '</div>';
        echo '</div>';
    }
} // class
