<?php

/**
 * Login Lockdown Pro
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

use WFMaxMind\Db\Reader;

class LoginLockdown_Utility extends LoginLockdown
{
    /**
     * Display settings notice
     *
     * @param $redirect
     * @return bool
     */
    static function display_notice($message, $type = 'error', $code = 'login-lockdown')
    {
        global $wp_settings_errors;

        $wp_settings_errors[] = array(
            'setting' => LOGINLOCKDOWN_OPTIONS_KEY,
            'code'    => $code,
            'message' => $message,
            'type'    => $type
        );
        set_transient('settings_errors', $wp_settings_errors);
    } // display_notice


    /**
     * Whitelabel filter
     *
     * @return bool display contents if whitelabel is not enabled or not hidden
     */
    static function whitelabel_filter()
    {
        global $loginlockdown_licensing;
        $options = LoginLockdown_Setup::get_options();

        if (!$loginlockdown_licensing->is_active('white_label')) {
            return true;
        }

        if (!$options['whitelabel']) {
            return true;
        }

        return false;
    } // whitelabel_filter


    /**
     * Empty cache in various 3rd party plugins
     *
     * @since 5.0
     *
     * @return null
     *
     */
    static function clear_3rdparty_cache()
    {
        if (function_exists('w3tc_pgcache_flush')) {
            w3tc_pgcache_flush();
        }
        if (function_exists('wp_cache_clean_cache')) {
            global $file_prefix;
            wp_cache_clean_cache($file_prefix);
        }
        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
        }
        if (class_exists('Endurance_Page_Cache')) {
            $epc = new Endurance_Page_Cache;
            $epc->purge_all();
        }
        if (method_exists('SG_CachePress_Supercacher', 'purge_cache')) {
            SG_CachePress_Supercacher::purge_cache(true);
        }

        if (class_exists('SiteGround_Optimizer\Supercacher\Supercacher')) {
            SiteGround_Optimizer\Supercacher\Supercacher::purge_cache();
        }
    } // empty_3rdparty_cache


    /**
     * Dismiss pointer
     *
     * @since 5.0
     *
     * @return null
     *
     */
    static function dismiss_pointer_ajax()
    {
        delete_option(LOGINLOCKDOWN_POINTERS_KEY);
    }

    /**
     * checkbox helper function
     *
     * @since 5.0
     *
     * @return string checked HTML
     *
     */
    static function checked($value, $current, $echo = false)
    {
        $out = '';

        if (!is_array($current)) {
            $current = (array) $current;
        }

        if (in_array($value, $current)) {
            $out = ' checked="checked" ';
        }

        if ($echo) {
            echo $out;
        } else {
            return $out;
        }
    } // checked

    /**
     * Create toggle switch
     *
     * @since 5.0
     *
     * @return string Switch HTML
     *
     */
    static function create_toggle_switch($name, $options = array(), $output = true, $class = '')
    {
        $default_options = array('value' => '1', 'saved_value' => '', 'option_key' => $name);
        $options = array_merge($default_options, $options);

        $out = "\n";
        $out .= '<div class="toggle-wrapper">';
        $out .= '<input class="' . $class . '" type="checkbox" id="' . $name . '" ' . self::checked($options['value'], $options['saved_value']) . ' type="checkbox" value="' . $options['value'] . '" name="' . $options['option_key'] . '">';
        $out .= '<label for="' . $name . '" class="toggle"><span class="toggle_handler"></span></label>';
        $out .= '</div>';

        if ($output) {
            echo $out;
        } else {
            return $out;
        }
    } // create_toggle_switch

    /**
     * Get user country
     *
     * @since 5.0
     *
     * @return string country
     *
     */
    static function getUserCountry($ip = false)
    {
        if (!$ip) {
            $ip = self::getUserIP(true);
        }
        $reader = new Reader(LOGINLOCKDOWN_PLUGIN_DIR . '/misc/geo-country.mmdb');
        $ip_data = $reader->get($ip);
        $country = isset($ip_data) ? $ip_data['country']['names']['en'] : '';
        $reader->close();

        return $country;
    }

    /**
     * Get user IP
     *
     * @since 5.0
     *
     * @return string userip
     *
     */
    static function getUserIP($force_clear = false)
    {
        $options = LoginLockdown_Setup::get_options();
        $ip = '';

        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if($options['anonymous_logging'] == '1' && !$force_clear){
            $ip = md5($ip);
        }

        return $ip;
    } // getUserIP

    /**
     * Create select options for select
     *
     * @since 5.0
     *
     * @param array $options options
     * @param string $selected selected value
     * @param bool $output echo, if false return html as string
     * @return string html with options
     */
    static function create_select_options($options, $selected = null, $output = true)
    {
        $out = "\n";

        foreach ($options as $tmp) {
            if ((is_array($selected) && in_array($tmp['val'], $selected)) || $selected == $tmp['val']) {
                $out .= "<option selected=\"selected\" value=\"{$tmp['val']}\">{$tmp['label']}&nbsp;</option>\n";
            } else {
                $out .= "<option value=\"{$tmp['val']}\">{$tmp['label']}&nbsp;</option>\n";
            }
        }

        if ($output) {
            echo $out;
        } else {
            return $out;
        }
    } //  create_select_options


    static function create_radio_group($name, $options, $selected = null, $output = true)
    {
        $out = "\n";

        foreach ($options as $tmp) {
            if ($selected == $tmp['val']) {
                $out .= "<label for=\"{$name}_{$tmp['val']}\" class=\"radio_wrapper\"><input id=\"{$name}_{$tmp['val']}\" name=\"{$name}\" type=\"radio\" checked=\"checked\" value=\"{$tmp['val']}\">{$tmp['label']}&nbsp;</option></label>\n";
            } else {
                $out .= "<label for=\"{$name}_{$tmp['val']}\" class=\"radio_wrapper\"><input id=\"{$name}_{$tmp['val']}\" name=\"{$name}\" type=\"radio\" value=\"{$tmp['val']}\">{$tmp['label']}&nbsp;</option></label>\n";
            }
        }

        if ($output) {
            echo $out;
        } else {
            return $out;
        }
    }

    /**
     * Parse user agent to add device icon and clean text
     *
     * @since 5.0
     *
     * @param string $user_agent
     * @return string $user_agent
     */
    static function parse_user_agent($user_agent = false)
    {
        if (!$user_agent) {
            $user_agent = array();
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $user_agent[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        }

        $user_agent = new WhichBrowser\Parser($user_agent);

        $user_agent_string = '';
        if ($user_agent->isType('mobile')) {
            $user_agent_string .= '<i class="tooltip fas fa-mobile-alt" title="Phone"></i>';
        } else if ($user_agent->isType('tablet')) {
            $user_agent_string .= '<i class="tooltip fas fa-tablet-alt" title="Table"></i>';
        } else if ($user_agent->isType('desktop')) {
            $user_agent_string .= '<i class="tooltip fas fa-desktop" title="Desktop"></i>';
        } else {
            $user_agent_string .= '<i class="tooltip fas fa-robot" title="Bot"></i>';
        }

        if (isset($user_agent->browser) && isset($user_agent->browser->version)) {
            $browser_version = explode('.', $user_agent->browser->version->toString());
        } else {
            $browser_version = array('unknown');
        }

        if ($user_agent->os) {
            $os = $user_agent->os->toString();
        } else {
            $os = 'unknown';
        }

        if (isset($user_agent->browser) && isset($user_agent->browser->name)) {
            $browser_name = $user_agent->browser->name;
        } else {
            $browser_name = 'unknown';
        }

        $user_agent_string .= ' ' . $browser_name . ' ' . $browser_version[0] . ' on ' . $os;


        return $user_agent_string;
    } // parse_user_agent

    /**
     * Parse user agent to return an array with info
     *
     * @since 5.0
     *
     * @param string $user_agent
     * @return array user agent data
     */
    static function parse_user_agent_array($user_agent = false)
    {
        if (!$user_agent) {
            $user_agent = array();
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $user_agent[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        }

        $user_agent = new WhichBrowser\Parser($user_agent);

        if (!is_null($user_agent)) {

            $agent['device'] = '';

            if ($user_agent->isType('mobile')) {
                $agent['device'] = 'mobile';
            } else if ($user_agent->isType('tablet')) {
                $agent['device'] = 'tablet';
            } else if ($user_agent->isType('desktop')) {
                $agent['device'] = 'desktop';
            } else {
                $agent['device'] = 'bot';
            }


            $agent['browser'] = $user_agent->browser->name;
            if ($agent['device'] != 'bot') {
                $version = explode('.', $user_agent->browser->version->value);
                $agent['browser_ver'] = $version[0];
                $agent['os'] = $user_agent->os->name;
                if (!empty($user_agent->os->version->nickname)) {
                    $agent['os_ver'] = $user_agent->os->version->nickname;
                } else if (!empty($user_agent->os->version->alias)) {
                    $agent['os_ver'] = $user_agent->os->version->alias;
                } else if (!empty($user_agent->os->version->value)) {
                    $agent['os_ver'] = $user_agent->os->version->value;
                } else {
                    $agent['os_ver'] = '';
                }
                $agent['bot'] = '';
            } else {
                $agent['bot'] = $agent['browser'];
                $agent['browser'] = '';
                $agent['browser_ver'] = '';
                $agent['os'] = '';
                $agent['os_ver'] = '';
            }

            return $agent;
        } else {
            return array('browser' => '', 'browser_ver' => '', 'os' => '', 'os_ver' => '', 'device' => '', 'bot' => '');
        }
    } // parse_user_agent_array

    /**
     * Convert country code to country name
     *
     * @since 5.0
     *
     * @param string country code
     * @return string country name
     */
    static function country_name_to_code($country)
    {
        if ($country == 'Sweeden') {
            $country = 'Sweden';
        }

        $countrycodes = array(
            'other' => 'Other',
            'AF' => 'Afghanistan',
            'AX' => 'Åland Islands',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AS' => 'American Samoa',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua and Barbuda',
            'AR' => 'Argentina',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia',
            'BA' => 'Bosnia and Herzegovina',
            'BW' => 'Botswana',
            'BV' => 'Bouvet Island',
            'BR' => 'Brazil',
            'IO' => 'British Indian Ocean Territory',
            'BN' => 'Brunei Darussalam',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CAN' => 'Canada',
            'CV' => 'Cape Verde',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos (Keeling) Islands',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'CG' => 'Congo',
            'CD' => 'Zaire',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'CI' => 'Côte D\'Ivoire',
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'EC' => 'Ecuador',
            'EG' => 'Egypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'FK' => 'Falkland Islands (Malvinas)',
            'FO' => 'Faroe Islands',
            'FJ' => 'Fiji',
            'FI' => 'Finland',
            'FR' => 'France',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories',
            'GA' => 'Gabon',
            'GM' => 'Gambia',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GR' => 'Greece',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HM' => 'Heard Island and Mcdonald Islands',
            'VA' => 'Vatican City State',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IM' => 'Isle of Man',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JE' => 'Jersey',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'KP' => 'Korea, Democratic People\'s Republic of',
            'KR' => 'Korea, Republic of',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => 'Lao People\'s Democratic Republic',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libyan Arab Jamahiriya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MO' => 'Macao',
            'MK' => 'Macedonia, the Former Yugoslav Republic of',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'MX' => 'Mexico',
            'FM' => 'Micronesia, Federated States of',
            'MD' => 'Moldova, Republic of',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MS' => 'Montserrat',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'NL' => 'Netherlands',
            'AN' => 'Netherlands Antilles',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PS' => 'Palestine',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar',
            'RE' => 'Réunion',
            'RO' => 'Romania',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'SH' => 'Saint Helena',
            'KN' => 'Saint Kitts and Nevis',
            'LC' => 'Saint Lucia',
            'PM' => 'Saint Pierre and Miquelon',
            'VC' => 'Saint Vincent and the Grenadines',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'ST' => 'Sao Tome and Principe',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SJ' => 'Svalbard and Jan Mayen',
            'SZ' => 'Swaziland',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'SY' => 'Syrian Arab Republic',
            'TW' => 'Taiwan, Province of China',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania, United Republic of',
            'TH' => 'Thailand',
            'TL' => 'Timor-Leste',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad and Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks and Caicos Islands',
            'TV' => 'Tuvalu',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'UM' => 'United States Minor Outlying Islands',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VE' => 'Venezuela',
            'VN' => 'Vietnam',
            'VG' => 'Virgin Islands, British',
            'VI' => 'Virgin Islands, U.S.',
            'WF' => 'Wallis and Futuna',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
        );

        $code = array_search($country, $countrycodes);

        if (false === $code) {
            return 'other';
        }

        return $code;
    } // country_name_to_code

    static function country_code_to_name($code)
    {
        $countrycodes = array(
            'other' => 'Other',
            'AF' => 'Afghanistan',
            'AX' => 'Åland Islands',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AS' => 'American Samoa',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua and Barbuda',
            'AR' => 'Argentina',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia',
            'BA' => 'Bosnia and Herzegovina',
            'BW' => 'Botswana',
            'BV' => 'Bouvet Island',
            'BR' => 'Brazil',
            'IO' => 'British Indian Ocean Territory',
            'BN' => 'Brunei Darussalam',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CAN' => 'Canada',
            'CV' => 'Cape Verde',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos (Keeling) Islands',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'CG' => 'Congo',
            'CD' => 'Zaire',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'CI' => 'Côte D\'Ivoire',
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'EC' => 'Ecuador',
            'EG' => 'Egypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'FK' => 'Falkland Islands (Malvinas)',
            'FO' => 'Faroe Islands',
            'FJ' => 'Fiji',
            'FI' => 'Finland',
            'FR' => 'France',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories',
            'GA' => 'Gabon',
            'GM' => 'Gambia',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GR' => 'Greece',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HM' => 'Heard Island and Mcdonald Islands',
            'VA' => 'Vatican City State',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IM' => 'Isle of Man',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JE' => 'Jersey',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'KP' => 'Korea, Democratic People\'s Republic of',
            'KR' => 'Korea, Republic of',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => 'Lao People\'s Democratic Republic',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libyan Arab Jamahiriya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MO' => 'Macao',
            'MK' => 'Macedonia, the Former Yugoslav Republic of',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'MX' => 'Mexico',
            'FM' => 'Micronesia, Federated States of',
            'MD' => 'Moldova, Republic of',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MS' => 'Montserrat',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'NL' => 'Netherlands',
            'AN' => 'Netherlands Antilles',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PS' => 'Palestine',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar',
            'RE' => 'Réunion',
            'RO' => 'Romania',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'SH' => 'Saint Helena',
            'KN' => 'Saint Kitts and Nevis',
            'LC' => 'Saint Lucia',
            'PM' => 'Saint Pierre and Miquelon',
            'VC' => 'Saint Vincent and the Grenadines',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'ST' => 'Sao Tome and Principe',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SJ' => 'Svalbard and Jan Mayen',
            'SZ' => 'Swaziland',
            'SE' => 'Sweeden',
            'CH' => 'Switzerland',
            'SY' => 'Syrian Arab Republic',
            'TW' => 'Taiwan, Province of China',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania, United Republic of',
            'TH' => 'Thailand',
            'TL' => 'Timor-Leste',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad and Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks and Caicos Islands',
            'TV' => 'Tuvalu',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'UM' => 'United States Minor Outlying Islands',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VE' => 'Venezuela',
            'VN' => 'Vietnam',
            'VG' => 'Virgin Islands, British',
            'VI' => 'Virgin Islands, U.S.',
            'WF' => 'Wallis and Futuna',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
        );

        if(array_key_exists($code, $countrycodes)){
            return $countrycodes[$code];
        } else {
            return 'other';
        }
    } // country_code_to_name

    static function country_code_to_coordinates($country_code){
        $coordinates = array();
        $coordinates['AD']=array('42.546245','1.601554');
        $coordinates['AE']=array('23.424076','53.847818');
        $coordinates['AF']=array('33.93911','67.709953');
        $coordinates['AG']=array('17.060816','-61.796428');
        $coordinates['AI']=array('18.220554','-63.068615');
        $coordinates['AL']=array('41.153332','20.168331');
        $coordinates['AM']=array('40.069099','45.038189');
        $coordinates['AN']=array('12.226079','-69.060087');
        $coordinates['AO']=array('-11.202692','17.873887');
        $coordinates['AQ']=array('-75.250973','-0.071389');
        $coordinates['AR']=array('-38.416097','-63.616672');
        $coordinates['AS']=array('-14.270972','-170.132217');
        $coordinates['AT']=array('47.516231','14.550072');
        $coordinates['AU']=array('-25.274398','133.775136');
        $coordinates['AW']=array('12.52111','-69.968338');
        $coordinates['AZ']=array('40.143105','47.576927');
        $coordinates['BA']=array('43.915886','17.679076');
        $coordinates['BB']=array('13.193887','-59.543198');
        $coordinates['BD']=array('23.684994','90.356331');
        $coordinates['BE']=array('50.503887','4.469936');
        $coordinates['BF']=array('12.238333','-1.561593');
        $coordinates['BG']=array('42.733883','25.48583');
        $coordinates['BH']=array('25.930414','50.637772');
        $coordinates['BI']=array('-3.373056','29.918886');
        $coordinates['BJ']=array('9.30769','2.315834');
        $coordinates['BM']=array('32.321384','-64.75737');
        $coordinates['BN']=array('4.535277','114.727669');
        $coordinates['BO']=array('-16.290154','-63.588653');
        $coordinates['BR']=array('-14.235004','-51.92528');
        $coordinates['BS']=array('25.03428','-77.39628');
        $coordinates['BT']=array('27.514162','90.433601');
        $coordinates['BV']=array('-54.423199','3.413194');
        $coordinates['BW']=array('-22.328474','24.684866');
        $coordinates['BY']=array('53.709807','27.953389');
        $coordinates['BZ']=array('17.189877','-88.49765');
        $coordinates['CA']=array('56.130366','-106.346771');
        $coordinates['CC']=array('-12.164165','96.870956');
        $coordinates['CD']=array('-4.038333','21.758664');
        $coordinates['CF']=array('6.611111','20.939444');
        $coordinates['CG']=array('-0.228021','15.827659');
        $coordinates['CH']=array('46.818188','8.227512');
        $coordinates['CI']=array('7.539989','-5.54708');
        $coordinates['CK']=array('-21.236736','-159.777671');
        $coordinates['CL']=array('-35.675147','-71.542969');
        $coordinates['CM']=array('7.369722','12.354722');
        $coordinates['CN']=array('35.86166','104.195397');
        $coordinates['CO']=array('4.570868','-74.297333');
        $coordinates['CR']=array('9.748917','-83.753428');
        $coordinates['CU']=array('21.521757','-77.781167');
        $coordinates['CV']=array('16.002082','-24.013197');
        $coordinates['CX']=array('-10.447525','105.690449');
        $coordinates['CY']=array('35.126413','33.429859');
        $coordinates['CZ']=array('49.817492','15.472962');
        $coordinates['DE']=array('51.165691','10.451526');
        $coordinates['DJ']=array('11.825138','42.590275');
        $coordinates['DK']=array('56.26392','9.501785');
        $coordinates['DM']=array('15.414999','-61.370976');
        $coordinates['DO']=array('18.735693','-70.162651');
        $coordinates['DZ']=array('28.033886','1.659626');
        $coordinates['EC']=array('-1.831239','-78.183406');
        $coordinates['EE']=array('58.595272','25.013607');
        $coordinates['EG']=array('26.820553','30.802498');
        $coordinates['EH']=array('24.215527','-12.885834');
        $coordinates['ER']=array('15.179384','39.782334');
        $coordinates['ES']=array('40.463667','-3.74922');
        $coordinates['ET']=array('9.145','40.489673');
        $coordinates['FI']=array('61.92411','25.748151');
        $coordinates['FJ']=array('-16.578193','179.414413');
        $coordinates['FK']=array('-51.796253','-59.523613');
        $coordinates['FM']=array('7.425554','150.550812');
        $coordinates['FO']=array('61.892635','-6.911806');
        $coordinates['FR']=array('46.227638','2.213749');
        $coordinates['GA']=array('-0.803689','11.609444');
        $coordinates['GB']=array('55.378051','-3.435973');
        $coordinates['GD']=array('12.262776','-61.604171');
        $coordinates['GE']=array('42.315407','43.356892');
        $coordinates['GF']=array('3.933889','-53.125782');
        $coordinates['GG']=array('49.465691','-2.585278');
        $coordinates['GH']=array('7.946527','-1.023194');
        $coordinates['GI']=array('36.137741','-5.345374');
        $coordinates['GL']=array('71.706936','-42.604303');
        $coordinates['GM']=array('13.443182','-15.310139');
        $coordinates['GN']=array('9.945587','-9.696645');
        $coordinates['GP']=array('16.995971','-62.067641');
        $coordinates['GQ']=array('1.650801','10.267895');
        $coordinates['GR']=array('39.074208','21.824312');
        $coordinates['GS']=array('-54.429579','-36.587909');
        $coordinates['GT']=array('15.783471','-90.230759');
        $coordinates['GU']=array('13.444304','144.793731');
        $coordinates['GW']=array('11.803749','-15.180413');
        $coordinates['GY']=array('4.860416','-58.93018');
        $coordinates['GZ']=array('31.354676','34.308825');
        $coordinates['HK']=array('22.396428','114.109497');
        $coordinates['HM']=array('-53.08181','73.504158');
        $coordinates['HN']=array('15.199999','-86.241905');
        $coordinates['HR']=array('45.1','15.2');
        $coordinates['HT']=array('18.971187','-72.285215');
        $coordinates['HU']=array('47.162494','19.503304');
        $coordinates['ID']=array('-0.789275','113.921327');
        $coordinates['IE']=array('53.41291','-8.24389');
        $coordinates['IL']=array('31.046051','34.851612');
        $coordinates['IM']=array('54.236107','-4.548056');
        $coordinates['IN']=array('20.593684','78.96288');
        $coordinates['IO']=array('-6.343194','71.876519');
        $coordinates['IQ']=array('33.223191','43.679291');
        $coordinates['IR']=array('32.427908','53.688046');
        $coordinates['IS']=array('64.963051','-19.020835');
        $coordinates['IT']=array('41.87194','12.56738');
        $coordinates['JE']=array('49.214439','-2.13125');
        $coordinates['JM']=array('18.109581','-77.297508');
        $coordinates['JO']=array('30.585164','36.238414');
        $coordinates['JP']=array('36.204824','138.252924');
        $coordinates['KE']=array('-0.023559','37.906193');
        $coordinates['KG']=array('41.20438','74.766098');
        $coordinates['KH']=array('12.565679','104.990963');
        $coordinates['KI']=array('-3.370417','-168.734039');
        $coordinates['KM']=array('-11.875001','43.872219');
        $coordinates['KN']=array('17.357822','-62.782998');
        $coordinates['KP']=array('40.339852','127.510093');
        $coordinates['KR']=array('35.907757','127.766922');
        $coordinates['KW']=array('29.31166','47.481766');
        $coordinates['KY']=array('19.513469','-80.566956');
        $coordinates['KZ']=array('48.019573','66.923684');
        $coordinates['LA']=array('19.85627','102.495496');
        $coordinates['LB']=array('33.854721','35.862285');
        $coordinates['LC']=array('13.909444','-60.978893');
        $coordinates['LI']=array('47.166','9.555373');
        $coordinates['LK']=array('7.873054','80.771797');
        $coordinates['LR']=array('6.428055','-9.429499');
        $coordinates['LS']=array('-29.609988','28.233608');
        $coordinates['LT']=array('55.169438','23.881275');
        $coordinates['LU']=array('49.815273','6.129583');
        $coordinates['LV']=array('56.879635','24.603189');
        $coordinates['LY']=array('26.3351','17.228331');
        $coordinates['MA']=array('31.791702','-7.09262');
        $coordinates['MC']=array('43.750298','7.412841');
        $coordinates['MD']=array('47.411631','28.369885');
        $coordinates['ME']=array('42.708678','19.37439');
        $coordinates['MG']=array('-18.766947','46.869107');
        $coordinates['MH']=array('7.131474','171.184478');
        $coordinates['MK']=array('41.608635','21.745275');
        $coordinates['ML']=array('17.570692','-3.996166');
        $coordinates['MM']=array('21.913965','95.956223');
        $coordinates['MN']=array('46.862496','103.846656');
        $coordinates['MO']=array('22.198745','113.543873');
        $coordinates['MP']=array('17.33083','145.38469');
        $coordinates['MQ']=array('14.641528','-61.024174');
        $coordinates['MR']=array('21.00789','-10.940835');
        $coordinates['MS']=array('16.742498','-62.187366');
        $coordinates['MT']=array('35.937496','14.375416');
        $coordinates['MU']=array('-20.348404','57.552152');
        $coordinates['MV']=array('3.202778','73.22068');
        $coordinates['MW']=array('-13.254308','34.301525');
        $coordinates['MX']=array('23.634501','-102.552784');
        $coordinates['MY']=array('4.210484','101.975766');
        $coordinates['MZ']=array('-18.665695','35.529562');
        $coordinates['NA']=array('-22.95764','18.49041');
        $coordinates['NC']=array('-20.904305','165.618042');
        $coordinates['NE']=array('17.607789','8.081666');
        $coordinates['NF']=array('-29.040835','167.954712');
        $coordinates['NG']=array('9.081999','8.675277');
        $coordinates['NI']=array('12.865416','-85.207229');
        $coordinates['NL']=array('52.132633','5.291266');
        $coordinates['NO']=array('60.472024','8.468946');
        $coordinates['NP']=array('28.394857','84.124008');
        $coordinates['NR']=array('-0.522778','166.931503');
        $coordinates['NU']=array('-19.054445','-169.867233');
        $coordinates['NZ']=array('-40.900557','174.885971');
        $coordinates['OM']=array('21.512583','55.923255');
        $coordinates['PA']=array('8.537981','-80.782127');
        $coordinates['PE']=array('-9.189967','-75.015152');
        $coordinates['PF']=array('-17.679742','-149.406843');
        $coordinates['PG']=array('-6.314993','143.95555');
        $coordinates['PH']=array('12.879721','121.774017');
        $coordinates['PK']=array('30.375321','69.345116');
        $coordinates['PL']=array('51.919438','19.145136');
        $coordinates['PM']=array('46.941936','-56.27111');
        $coordinates['PN']=array('-24.703615','-127.439308');
        $coordinates['PR']=array('18.220833','-66.590149');
        $coordinates['PS']=array('31.952162','35.233154');
        $coordinates['PT']=array('39.399872','-8.224454');
        $coordinates['PW']=array('7.51498','134.58252');
        $coordinates['PY']=array('-23.442503','-58.443832');
        $coordinates['QA']=array('25.354826','51.183884');
        $coordinates['RE']=array('-21.115141','55.536384');
        $coordinates['RO']=array('45.943161','24.96676');
        $coordinates['RS']=array('44.016521','21.005859');
        $coordinates['RU']=array('61.52401','105.318756');
        $coordinates['RW']=array('-1.940278','29.873888');
        $coordinates['SA']=array('23.885942','45.079162');
        $coordinates['SB']=array('-9.64571','160.156194');
        $coordinates['SC']=array('-4.679574','55.491977');
        $coordinates['SD']=array('12.862807','30.217636');
        $coordinates['SE']=array('60.128161','18.643501');
        $coordinates['SG']=array('1.352083','103.819836');
        $coordinates['SH']=array('-24.143474','-10.030696');
        $coordinates['SI']=array('46.151241','14.995463');
        $coordinates['SJ']=array('77.553604','23.670272');
        $coordinates['SK']=array('48.669026','19.699024');
        $coordinates['SL']=array('8.460555','-11.779889');
        $coordinates['SM']=array('43.94236','12.457777');
        $coordinates['SN']=array('14.497401','-14.452362');
        $coordinates['SO']=array('5.152149','46.199616');
        $coordinates['SR']=array('3.919305','-56.027783');
        $coordinates['ST']=array('0.18636','6.613081');
        $coordinates['SV']=array('13.794185','-88.89653');
        $coordinates['SY']=array('34.802075','38.996815');
        $coordinates['SZ']=array('-26.522503','31.465866');
        $coordinates['TC']=array('21.694025','-71.797928');
        $coordinates['TD']=array('15.454166','18.732207');
        $coordinates['TF']=array('-49.280366','69.348557');
        $coordinates['TG']=array('8.619543','0.824782');
        $coordinates['TH']=array('15.870032','100.992541');
        $coordinates['TJ']=array('38.861034','71.276093');
        $coordinates['TK']=array('-8.967363','-171.855881');
        $coordinates['TL']=array('-8.874217','125.727539');
        $coordinates['TM']=array('38.969719','59.556278');
        $coordinates['TN']=array('33.886917','9.537499');
        $coordinates['TO']=array('-21.178986','-175.198242');
        $coordinates['TR']=array('38.963745','35.243322');
        $coordinates['TT']=array('10.691803','-61.222503');
        $coordinates['TV']=array('-7.109535','177.64933');
        $coordinates['TW']=array('23.69781','120.960515');
        $coordinates['TZ']=array('-6.369028','34.888822');
        $coordinates['UA']=array('48.379433','31.16558');
        $coordinates['UG']=array('1.373333','32.290275');
        $coordinates['UM']=array('','');
        $coordinates['US']=array('37.09024','-95.712891');
        $coordinates['UY']=array('-32.522779','-55.765835');
        $coordinates['UZ']=array('41.377491','64.585262');
        $coordinates['VA']=array('41.902916','12.453389');
        $coordinates['VC']=array('12.984305','-61.287228');
        $coordinates['VE']=array('6.42375','-66.58973');
        $coordinates['VG']=array('18.420695','-64.639968');
        $coordinates['VI']=array('18.335765','-64.896335');
        $coordinates['VN']=array('14.058324','108.277199');
        $coordinates['VU']=array('-15.376706','166.959158');
        $coordinates['WF']=array('-13.768752','-177.156097');
        $coordinates['WS']=array('-13.759029','-172.104629');
        $coordinates['XK']=array('42.602636','20.902977');
        $coordinates['YE']=array('15.552727','48.516388');
        $coordinates['YT']=array('-12.8275','45.166244');
        $coordinates['ZA']=array('-30.559482','22.937506');
        $coordinates['ZM']=array('-13.133897','27.849332');
        $coordinates['ZW']=array('-19.015438','29.154857');
        $coordinates['other']=array('-19.015438','29.154857');

        if(array_key_exists($country_code,$coordinates)){
           return $coordinates[$country_code];
        } else {
           return array('1','1');
        }
    } // country_code_to_coordinates

    static function get_home_path() {

        if (!function_exists('get_home_path')) {

            require_once(ABSPATH . 'wp-admin/includes/file.php');

        }

        return get_home_path();

    }

} // class
