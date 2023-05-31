<?php
/**
 * Login Lockdown Pro
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_Tab_License extends LoginLockdown
{
    static function display()
    {
        global $loginlockdown_licensing;
        $options = $loginlockdown_licensing->get_license();

        echo '<div class="loginlockdown-tab-title"><i class="loginlockdown-icon loginlockdown-check"></i> License</div>';
        echo '<p class="loginlockdown-tab-description">Enter your license key, to activate the plugin.<br />Your License key is visible on the screen, right after purchasing. You can also find it in the confirmation email sent to the email address provided on purchase.</p>';

        echo '<div class="tab-content">';
        echo '<table class="form-table"><tbody><tr>
        <th scope="row"><label for="license-key">License Key</label></th>
        <td>
        <input class="regular-text" type="text" id="license-key" value="" placeholder="' . (empty($options['license_key']) ? '12345678-12345678-12345678-12345678' : substr(esc_attr($options['license_key']), 0, 8) . '-************************') . '">
            </td></tr>';

        echo '<tr><th scope="row"><label for="">' . __('License Status', 'login-lockdown') . '</label></th><td>';
        if ($loginlockdown_licensing->is_active()) {
            $license_formatted = $loginlockdown_licensing->get_license_formatted();
            echo '<b style="color: #29b99a;">Active</b><br>
            Type: ' . $license_formatted['name_long'];
            echo '<br>Valid ' . $license_formatted['valid_until'] . '</td>';
        } else { // not active
            echo '<strong style="color: #e01f20;">Inactive</strong>';
            if (!empty($loginlockdown_licensing->get_license('error'))) {
                echo '<br>Error: ' . $loginlockdown_licensing->get_license('error');
            }
        }
        echo '</td></tr>';
        echo '</tbody></table>';

        echo '<div class="license-buttons">';
        echo '<a href="#" id="save-license" data-text-wait="Validating. Please wait." class="button button-primary">Save &amp; Activate License <i class="loginlockdown-icon loginlockdown-check"></i></a>';

        if ($loginlockdown_licensing->is_active()) {
            echo '&nbsp; &nbsp;<a href="#" id="deactivate-license" class="button button-delete">Deactivate License</a>';
        } else {
            echo '&nbsp; &nbsp;<a href="#" class="button button-primary" data-text-wait="Validating. Please wait." id="loginlockdown_keyless_activation">Keyless Activation</a>';
        }
        echo '<p class="loginlockdown-tab-description-small">If you don\'t have a license - <a target="_blank" href="' . LoginLockdown_Admin::generate_web_link('license-tab') . '">purchase one now</a>. In case of problems please <a href="#" class="open-beacon">contact support</a>. Manage your licenses in the <a target="_blank" href="' . LoginLockdown_Admin::generate_dashboard_link('license-tab') . '">Login Lockdown Dashboard</a></p>';

        echo '</div>';


        if ($loginlockdown_licensing->is_active('white_label')) {
            echo '<h4>White-Label License Mode</h4>';
            echo '<p>Enabling the white-label license mode hides the License and Support tabs, and removes all visible mentions of WebFactory Ltd.<br>To disable it append <strong>&amp;loginlockdown_wl=false</strong> to the Login Lockdown settings page URL.
                Or save this URL and open it when you want to disable the white-label license mode:<br> <a href="' . admin_url('options-general.php?page=loginlockdown&loginlockdown_wl=false') . '">' . admin_url('options-general.php?page=loginlockdown&loginlockdown_wl=false') . '</a></p>';
            echo '<p><a href="' . admin_url('options-general.php?page=loginlockdown&loginlockdown_wl=true') . '" class="button button-secondary">Enable White-Label License Mode</a></p>';
        }

        echo '</div>';
    } // display
} // class LoginLockdown_Tab_License
