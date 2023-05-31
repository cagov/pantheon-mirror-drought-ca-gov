<?php
/**
 * Login Lockdown Pro
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_Tab_GeoIP extends LoginLockdown
{
    static function display()
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
} // class LoginLockdown_Tab_GeoIP
