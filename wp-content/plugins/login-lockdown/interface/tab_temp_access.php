<?php
/**
 * Login Lockdown Pro
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_Tab_Temporary_Access extends LoginLockdown
{
    static function display()
    {
        $options = LoginLockdown_Setup::get_options();

        echo '<div class="tab-content">';

        echo '<table class="form-table"><tbody>';

        echo '<tr valign="top">
        <th scope="row"><label for="2fa_email">Create Temporary Access Link</label></th>
        <td class="create_temporary_link_wrapper">';

        echo 'User:';
        wp_dropdown_users(array('name' => 'temp_link_user', 'class' => 'temp_link_dropdown'));

        $temp_link_lifetime = array();
        $temp_link_lifetime[] = array('val' => '1', 'label' => '1 hour');
        $temp_link_lifetime[] = array('val' => '8', 'label' => '8 hours');
        $temp_link_lifetime[] = array('val' => '24', 'label' => '1 day');
        $temp_link_lifetime[] = array('val' => '168', 'label' => '7 day');
        $temp_link_lifetime[] = array('val' => '720', 'label' => '30 days');
        $temp_link_lifetime[] = array('val' => '2160', 'label' => '3 months');
        $temp_link_lifetime[] = array('val' => '4320', 'label' => '6 months');
        $temp_link_lifetime[] = array('val' => '8760', 'label' => '1 year');

        echo ' <label for="temp_link_lifetime">Duration:</label>';
        echo '<select class="temp_link_dropdown" id="temp_link_lifetime" name="temp_link_lifetime">';
        LoginLockdown_Utility::create_select_options($temp_link_lifetime, 1);
        echo '</select>';

        echo ' <label for="temp_link_uses">Uses:</label> <input class="temp_link_input" type="number" name="temp_link_uses" id="temp_link_uses" value="1" />';

        echo '<button class="button button-primary button-large create-temporary-link" href="#">Create Temporary Link</button>';

        echo '<br /><span>Create a temporary login link that you can share with other people. You can set the <label for="temp_link_lifetime">lifetime</label> of the link and the maximum <label for="temp_link_uses">number of times</label> it can be used to prevent abuse. If needed, <a href="' . admin_url('user-new.php') . '">create a new WP user</a> "guest" instead of using one of the existing users.</span><br><br>';
        echo '</td></tr>';

        echo '</tbody></table>';

        echo '<div id="loginlockdown-table-wrapper">
                    <table cellpadding="0" cellspacing="0" border="0" class="display loginlockdown-table" id="loginlockdown_temp_links">
                        <thead>
                            <tr>
                                <th style="text-align:left">Username</th>
                                <th style="text-align:left">Link</th>
                                <th style="text-align:left; width:160px;">Expires</th>
                                <th style="text-align:left">Uses</th>
                                <th style="width:80px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                        ' . LoginLockdown_AJAX::get_temp_links_html() . '
                        </tbody>
                    </table>
                </div>';

        echo '</div>';
    } // display
} // class LoginLockdown_Tab_2FA
