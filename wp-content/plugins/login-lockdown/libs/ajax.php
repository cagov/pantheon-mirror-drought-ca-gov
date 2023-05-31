<?php
/**
 * Login Lockdown Pro
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_AJAX extends LoginLockdown
{
    /**
     * Run one tool via AJAX call
     *
     * @return null
     */
    static function ajax_run_tool()
    {
        global $wpdb, $loginlockdown_licensing, $current_user;

        check_ajax_referer('loginlockdown_run_tool');
        set_time_limit(300);

        $tool = trim(@$_REQUEST['tool']);

        $options = LoginLockdown_Setup::get_options();

        $update['last_options_edit'] = current_time('mysql', true);
        update_option(LOGINLOCKDOWN_OPTIONS_KEY, array_merge($options, $update));

        if ($tool == 'activity_logs') {
            self::get_activity_logs();
        } else if ($tool == 'locks_logs') {
            self::get_locks_logs();
        } else if ($tool == 'temp_links') {
            self::get_temp_links();
        } else if ($tool == 'toggle_anonymous') {
            self::toggle_anonymous();
        } else if ($tool == 'recovery_url') {
            if($_POST['reset'] == 'true'){
                sleep(1);
                $options['global_unblock_key'] = 'll' . md5(time() . rand(10000, 9999));
                update_option(LOGINLOCKDOWN_OPTIONS_KEY, array_merge($options, $update));
            }
            wp_send_json_success(array('url' => '<a href="' . site_url('/?loginlockdown_unblock=' . $options['global_unblock_key']) . '">' . site_url('/?loginlockdown_unblock=' . $options['global_unblock_key']) . '</a>'));
        } else if ($tool == 'empty_log') {
            self::empty_log(sanitize_text_field($_POST['log']));
            wp_send_json_success();
        } else if ($tool == 'login_tests') {
            wp_send_json_success(LoginLockdown_Functions::bruteforce_login());
        } else if ($tool == 'wizard_setup') {
            self::wizard_setup($_POST['config']);
            wp_send_json_success();
        } else if ($tool == 'unlock_lockdown') {
            $wpdb->update(
                $wpdb->lockdown_lockdowns,
                array(
                    'unlocked' => 1
                ),
                array(
                    'lockdown_ID' => intval($_POST['lock_id'])
                )
            );
            wp_send_json_success(array('id' => $_POST['lock_id']));
        } else if ($tool == 'delete_lock_log') {
            $wpdb->delete(
                $wpdb->lockdown_lockdowns,
                array(
                    'lockdown_ID' => intval($_POST['lock_id'])
                )
            );
            wp_send_json_success(array('id' => $_POST['lock_id']));
        } else if ($tool == 'delete_fail_log') {
            $wpdb->delete(
                $wpdb->lockdown_login_fails,
                array(
                    'login_attempt_ID' => intval($_POST['fail_id'])
                )
            );
            wp_send_json_success(array('id' => $_POST['fail_id']));
        } else if ($tool == 'loginlockdown_dismiss_pointer') {
            delete_option(LOGINLOCKDOWN_POINTERS_KEY);
            wp_send_json_success();
        } else if ($tool == 'create_temporary_link') {
            //Create Link
            $user_temporary_links = get_user_meta((int)$_GET['user'], 'loginlockdown_temporary_links', true);
            if(!is_array($user_temporary_links)){
                $user_temporary_links = array();
            }

            $link_id = 'll' . md5(time() . rand(1000, 9999));
            $user_temporary_links[$link_id] = array('user_id' => (int)$_GET['user'], 'expires' => time() + HOUR_IN_SECONDS*$_GET['lifetime'], 'uses' => $_GET['uses'], 'used' => '0');

            update_user_meta((int)$_GET['user'], 'loginlockdown_temporary_links', $user_temporary_links);
            wp_send_json_success(array('link' => add_query_arg(array('loginlockdown_access' => $link_id), site_url()), 'html' => self::get_temp_links_html()));
        } else if ($tool == 'delete_temporary_link') {
            $loginlockdown_temporary_links = self::get_temp_links();

            if(array_key_exists($_POST['link_id'], $loginlockdown_temporary_links)){
                $user_temporary_links = get_user_meta($loginlockdown_temporary_links[$_POST['link_id']]['user_id'], 'loginlockdown_temporary_links', true);
                unset($user_temporary_links[$_POST['link_id']]);
                update_user_meta($loginlockdown_temporary_links[$_POST['link_id']]['user_id'], 'loginlockdown_temporary_links', $user_temporary_links);
                wp_send_json_success(array('html' => self::get_temp_links_html()));
            }

            wp_send_json_error('Temporary link not found');
        } else if ($tool == 'email_test') {
            $subject  = 'Login Lockdown test email';
            $message  = '<p>This is a test email from ' . get_bloginfo('title') . ' (' . home_url() . '). Since you have received it, emails work! 🎉</p>';

            add_filter('wp_mail_content_type', function () {
                return "text/html";
            });

            if(wp_mail($current_user->user_email, $subject, $message)){
                wp_send_json_success(array('sent' => true, 'title' => 'Email sent successfully', 'text' => 'An email has been sent to <strong>' . $current_user->user_email . '</strong>. Please check your Inbox as well as your Spam folder. If you have not received the email, there is an issue with your email configuration on your website.'));
            } else {
                wp_send_json_success(array('sent' => false, 'title' => 'Email failed', 'text' => 'We tried to send an email to <strong>' . $current_user->user_email . '</strong> but it appears to have failed. Please check your Inbox as well as your Spam folder. If you have not received the email, there is an issue with your email configuration on your website.'));
            }
        } else if ($tool == 'verify_captcha') {
            $captcha_result = self::verify_captcha($_POST['captcha_type'], $_POST['captcha_site_key'], $_POST['captcha_secret_key'], $_POST['captcha_response']);
            if(is_wp_error($captcha_result)){
                wp_send_json_error($captcha_result->get_error_message());
            }
            wp_send_json_success();
        } else {
          wp_send_json_error(__('Unknown tool.', 'login-lockdown'));
        }
        die();
    } // ajax_run_tool

    static function get_temp_links(){
        $users = get_users(array(
            'meta_key'     => 'loginlockdown_temporary_links',
        ));

        $loginlockdown_temporary_links = array();

        foreach($users as $user){
            $loginlockdown_temporary_links = array_merge($loginlockdown_temporary_links, get_user_meta($user->ID, 'loginlockdown_temporary_links', true));
        }

        uasort($loginlockdown_temporary_links, function($a, $b){
            return $b['expires'] - $a['expires'];
        });

        return $loginlockdown_temporary_links;
    }

    static function get_temp_links_html(){
        $temp_links_html = '';

        $loginlockdown_temporary_links = self::get_temp_links();

        foreach($loginlockdown_temporary_links as $link_id => $link){
            if(time() > $link['expires'] || $link['used'] >= $link['uses']){
                $temp_links_html .= '<tr class="link-expired">';
            } else {
                $temp_links_html .= '<tr>';
            }

            $user_info = get_userdata( $link['user_id']);
            $temp_links_html .= '<td>' . $user_info->display_name . '</td>';
            $temp_links_html .= '<td class="dt-body-left"><a class="loginlockdown-temporary-link" target="_blank" href="' . add_query_arg(array('loginlockdown_access' => $link_id), site_url()) . '">' . add_query_arg(array('loginlockdown_access' => $link_id), site_url()) . ' <span class="dashicons dashicons-admin-page"></span></a></td>';
            $temp_links_html .= '<td>' . date('Y-m-d H:i', $link['expires']) . '</td>';
            $temp_links_html .= '<td>' . $link['used'] . '/' . $link['uses'] . '</td>';
            $temp_links_html .= '<td><div data-link-id="' . $link_id . '" class="tooltip delete_temporary_link" title="Delete temporary login link?" data-msg-success="Temporary login link deleted" data-btn-confirm="Delete link " data-title="Delete temporary login link" data-wait-msg="Deleting. Please wait." data-name="" title="Delete this temporary login link"><i class="loginlockdown-icon loginlockdown-trash"></i></div></td>';
            $temp_links_html .= '</tr>';
        }

        if (empty($loginlockdown_temporary_links)) {
          $temp_links_html .= '<tr><td colspan="4" class="textcenter"><i>There are currently no temporary access links. Use the form above to create new ones.</i></td></tr>';
        }

        return $temp_links_html;
    }

    /**
     * Get rule row html
     *
     * @return string row HTML
     *
     * @param array $data with rule settings
     */
    static function get_date_time($timestamp)
    {
        $interval = current_time('timestamp') - $timestamp;
        return '<span class="loginlockdown-dt-small">'.self::humanTiming($interval, true) . '</span><br />' . date('Y/m/d', $timestamp) . ' <span class="loginlockdown-dt-small">' . date('h:i:s A', $timestamp).'</span>';
    }

    static function verify_captcha($type, $site_key, $secret_key, $response){
        $options = LoginLockdown_Setup::get_options();

        if ($type == 'recaptchav2') {
            if (!isset($response) || empty($response)) {
                return new WP_Error('lockdown_recaptchav2_not_submitted', __("reCAPTHCA verification failed ", 'login-lockdown'));
            } else {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $response);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);
                $response = json_decode($response);

                if ($response->success) {
                    return true;
                } else {
                    return new WP_Error('lockdown_recaptchav2_failed', __("reCAPTHCA verification failed " . (isset($response->{'error-codes'})?': ' . implode(',', $response->{'error-codes'}):''), 'login-lockdown'));
                }
            }
        } else if ($type == 'recaptchav3') {
            if (!isset($response) || empty($response)) {
                return new WP_Error('lockdown_recaptchav3_not_submitted', __("reCAPTHCA verification failed ", 'login-lockdown'));
            } else {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $response);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);
                $response = json_decode($response);

                if ($response->success) {
                    return true;
                } else {
                    return new WP_Error('lockdown_recaptchav2_failed', __("reCAPTHCA verification failed " . (isset($response->{'error-codes'})?': ' . implode(',', $response->{'error-codes'}):''), 'login-lockdown'));
                }
            }
        } else if ($type == 'hcaptcha') {
            $data = array(
                'secret' => $secret_key,
                'response' => $response
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
    }

    /**
     * Get human readable timestamp like 2 hours ago
     *
     * @return int time
     *
     * @param string timestamp
     */
    static function humanTiming($time)
    {
        $tokens = array(
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        if($time < 1){
            return 'just now';
        }
        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '') . ' ago';
        }
    }

    static function toggle_anonymous(){
        global $wpdb;
        $options = LoginLockdown_Setup::get_options();

        $wpdb->query('TRUNCATE TABLE ' . $wpdb->lockdown_lockdowns);
        $wpdb->query('TRUNCATE TABLE ' . $wpdb->lockdown_login_fails);

        if($options['anonymous_logging'] == '1'){
            $options['anonymous_logging'] = '0';
        } else {
            $options['anonymous_logging'] = '1';
        }

        update_option(LOGINLOCKDOWN_OPTIONS_KEY, $options);
    }

    static function empty_log($log){
        global $wpdb;

        if($log == 'fails'){
            $wpdb->query('TRUNCATE TABLE ' . $wpdb->lockdown_login_fails);
        } else {
            $wpdb->query('TRUNCATE TABLE ' . $wpdb->lockdown_lockdowns);
        }
    }

    static function wizard_setup($config){
        $options = LoginLockdown_Setup::get_options();
        $options['wizard_complete'] = 1;
        switch($config){
            case 'personal':
                $options['max_login_retries'] = 3;
            break;
            case 'medium':
                $options['max_login_retries'] = 4;
            break;
            case 'store':
                $options['max_login_retries'] = 5;
            break;
        }

        update_option(LOGINLOCKDOWN_OPTIONS_KEY, $options);
    }

    /**
     * Fetch activity logs and output JSON for datatables
     *
     * @return null
     */
    static function get_locks_logs()
    {
        global $wpdb;

        $aColumns = array('lockdown_ID', 'unlocked', 'lockdown_date', 'release_date', 'reason', 'lockdown_IP', 'country', 'user_agent');
        $sIndexColumn = "lockdown_ID";

        // paging
        $sLimit = '';
        if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
            $sLimit = "LIMIT " . esc_sql($_GET['iDisplayStart']) . ", " .
                esc_sql($_GET['iDisplayLength']);
        } // paging

        // ordering
        $sOrder = '';
        if (isset($_GET['iSortCol_0'])) {
            $sOrder = "ORDER BY  ";
            for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
                if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
                    $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . " "
                        .  esc_sql($_GET['sSortDir_' . $i]) . ", ";
                }
            }

            $sOrder = substr_replace($sOrder, '', -2);
            if ($sOrder == "ORDER BY") {
                $sOrder = '';
            }
        } // ordering

        // filtering
        $sWhere = '';
        if (isset($_GET['sSearch']) && $_GET['sSearch'] != '') {
            $sWhere = "WHERE (";
            for ($i = 0; $i < count($aColumns); $i++) {
                $sWhere .= $aColumns[$i] . " LIKE '%" . esc_sql($_GET['sSearch']) . "%' OR ";
            }
            $sWhere  = substr_replace($sWhere, '', -3);
            $sWhere .= ')';
        } // filtering

        // individual column filtering
        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
                if ($sWhere == '') {
                    $sWhere = "WHERE ";
                } else {
                    $sWhere .= " AND ";
                }
                $sWhere .= $aColumns[$i] . " LIKE '%" . esc_sql($_GET['sSearch_' . $i]) . "%' ";
            }
        } // individual columns

        // build query
        $sQuery = "SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) .
            " FROM " . $wpdb->lockdown_lockdowns . " $sWhere $sOrder $sLimit";

        $rResult = $wpdb->get_results($sQuery);

        // data set length after filtering
        $sQuery = "SELECT FOUND_ROWS()";
        $iFilteredTotal = $wpdb->get_var($sQuery);

        // total data set length
        $sQuery = "SELECT COUNT(" . $sIndexColumn . ") FROM " . $wpdb->lockdown_lockdowns;
        $iTotal = $wpdb->get_var($sQuery);

        // construct output
        $output = array(
            "sEcho" => intval(@$_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        foreach ($rResult as $aRow) {
            $row = array();
            $row['DT_RowId'] = $aRow->lockdown_ID;

            if(strtotime($aRow->release_date) < time()){
                $row['DT_RowClass'] = 'lock_expired';
            }

            for ($i = 0; $i < count($aColumns); $i++) {

                if ($aColumns[$i] == 'unlocked') {
                    $unblocked = $aRow->{$aColumns[$i]};
                    if($unblocked == 0 && strtotime($aRow->release_date) > time()){
                        $row[] = '<div class="tooltip unlock_lockdown" data-lock-id="' . $aRow->lockdown_ID . '" title="Unlock"><i class="loginlockdown-icon loginlockdown-lock"></i></div>';
                    } else {
                        $row[] = '<div class="tooltip unlocked_lockdown" title="Unlock"><i class="loginlockdown-icon loginlockdown-unlock"></i></div>';
                    }
                } else if ($aColumns[$i] == 'lockdown_date') {
                    $row[] = self::get_date_time(strtotime($aRow->{$aColumns[$i]}));
                } else if ($aColumns[$i] == 'reason') {
                    $row[] = $aRow->{$aColumns[$i]};
                } else if ($aColumns[$i] == 'lockdown_IP') {
                    $location = '';
                    $country = '';

                    if(!empty($aRow->country)){
                        $country = $aRow->country;
                    } else {
                        $country = 'Unknown Location';
                    }

                    $country_code = LoginLockdown_Utility::country_name_to_code($country);

                    if($country_code != 'other'){
                        $location .= '<img src="' . LOGINLOCKDOWN_PLUGIN_URL . '/images/flags/' . strtolower($country_code) . '.png" /> ';
                    }

                    $location .= $country . '<br />';

                    $location .= esc_html($aRow->lockdown_IP);
                    $row[] = $location;
                } elseif ($aColumns[$i] == 'user_agent') {
                    if (!empty(trim($aRow->{$aColumns[$i]}))) {
                        $row[] = LoginLockdown_Utility::parse_user_agent($aRow->{$aColumns[$i]});
                    } else {
                        $row[] = 'unknown';
                    }
                }
            }
            $row[] = '<div data-lock-id="' . $aRow->lockdown_ID . '" class="tooltip delete_lock_entry" title="Delete Lockdown?" data-msg-success="Lockdown deleted" data-btn-confirm="Delete Lockdown" data-title="Delete Lockdown?" data-wait-msg="Deleting. Please wait." data-name="" title="Delete this Lockdown"><i class="loginlockdown-icon loginlockdown-trash"></i></div>';
            $output['aaData'][] = $row;
        } // foreach row

        // json encoded output
        @ob_end_clean();
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        echo json_encode($output);
        die();
    }

    static function unsplash_download()
    {
        global $loginlockdown_licensing;

        $params['request'] = 'photos';
        $params['action'] = 'get_images';
        $params['image_id'] = $_POST['image_id'];

        $response = $loginlockdown_licensing->query_licensing_server('unsplash_api', array('request_details' => serialize($params)));

        if (is_wp_error($response)) {
            wp_send_json_error('Images API is temporarily not available. ' . $url);
        } else {
            $unsplash_image_link = json_decode($response['data']);
            $image_url = $unsplash_image_link->url;
            $image_name = $_POST['image_name'];
            $image_query = '&w=4000&h=4000&q=75';
            $image_src = self::media_sideload_image($image_url . '&format=.jpg' . $image_query, 0, false, $image_name, 'src');
            if (!is_wp_error($image_src)) {
                wp_send_json_success($image_src);
            } else {
                wp_send_json_error($image_src->get_error_message());
            }
        }
        die();
    } // unsplash_download


    static function unsplash_api()
    {
        global $loginlockdown_licensing;

        $params['request'] = 'photos';
        $params['page'] = (int) $_POST['page'];
        $params['per_page'] = (int) $_POST['per_page'];
        $params['search'] = substr(trim($_POST['search']), 0, 128);
        $params['action'] = 'get_images';
        $response = $loginlockdown_licensing->query_licensing_server('unsplash_api', array('request_details' => serialize($params)));

        if (is_wp_error($response) || !array_key_exists('data', $response) || $response['success'] != true || strlen($response['data']) < 5) {
            wp_send_json_error('Images API is temporarily not available. ');
        } else {
            $photos_unsplash_response = json_decode($response['data']);

            $photos_response = array();
            $total_pages = false;
            $total_results = false;

            if (isset($photos_unsplash_response->total)) {
                $total_results = $photos_unsplash_response->total;
                $total_pages = $photos_unsplash_response->total_pages;
                $photos_unsplash = $photos_unsplash_response->results;
            } else {
                $photos_unsplash = $photos_unsplash_response;
            }
            foreach ($photos_unsplash as $photo_data) {
                $image_name = $photo_data->id;
                if (strlen($photo_data->description) > 0) {
                    $image_name = sanitize_title(substr($photo_data->description, 0, 50));
                }
                $photo_response[] = array('id' => $photo_data->id, 'name' => $image_name, 'thumb' => $photo_data->urls->thumb, 'full' => $photo_data->urls->full, 'user' => '<a class="unsplash-user" href="https://unsplash.com/@' . $photo_data->user->username . '/?utm_source=Coming+Soon+demo&utm_medium=referral" target="_blank">' . $photo_data->user->name . '</a>');
            }
            if (count($photo_response) == 0) {
                wp_send_json_error('Images API is temporarily not available.');
            } else {
                wp_send_json_success(array('results' => json_encode($photo_response), 'total_pages' => $total_pages, 'total_results' => $total_results));
            }
        }
        die();
    } // unsplash_api

    static function media_sideload_image($url = null, $post_id = null, $thumb = null, $filename = null, $return = 'id')
    {
        if (!$url) return new WP_Error('missing', "Need a valid URL and post ID...");
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        // Download file to temp location, returns full server path to temp file, ex; /home/user/public_html/mysite/wp-content/26192277_640.tmp
        add_filter('https_local_ssl_verify', '__return_false');
        add_filter('https_ssl_verify', '__return_false');

        $tmp = download_url($url);
        // If error storing temporarily, unlink
        if (is_wp_error($tmp)) {
            @unlink($file_array['tmp_name']);   // clean up
            $file_array['tmp_name'] = '';
            return $tmp; // output wp_error
        }
        preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $url, $matches);    // fix file filename for query strings
        $url_filename = basename($matches[0]);                                                  // extract filename from url for title
        $url_type = wp_check_filetype($url_filename);                                           // determine file type (ext and mime/type)
        // override filename if given, reconstruct server path
        if (!empty($filename)) {
            $filename = sanitize_file_name($filename);
            $tmppath = pathinfo($tmp);                                                        // extract path parts
            $new = $tmppath['dirname'] . "/" . $filename . "." . $tmppath['extension'];          // build new path
            rename($tmp, $new);                                                                 // renames temp file on server
            $tmp = $new;                                                                        // push new filename (in path) to be used in file array later
        }
        // assemble file data (should be built like $_FILES since wp_handle_sideload() will be using)
        $file_array['tmp_name'] = $tmp;                                                         // full server path to temp file
        if (!empty($filename)) {
            $file_array['name'] = $filename . "." . $url_type['ext'];                           // user given filename for title, add original URL extension
        } else {
            $file_array['name'] = $url_filename;                                                // just use original URL filename
        }
        // set additional wp_posts columns
        if (empty($post_data['post_title'])) {
            $post_data['post_title'] = basename($url_filename, "." . $url_type['ext']);         // just use the original filename (no extension)
        }
        // make sure gets tied to parent
        if (empty($post_data['post_parent'])) {
            $post_data['post_parent'] = $post_id;
        }
        // required libraries for media_handle_sideload
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        // do the validation and storage stuff
        $att_id = media_handle_sideload($file_array, $post_id, null, $post_data);             // $post_data can override the items saved to wp_posts table, like post_mime_type, guid, post_parent, post_title, post_content, post_status
        // If error storing permanently, unlink
        if (is_wp_error($att_id)) {
            @unlink($file_array['tmp_name']);   // clean up
            return $att_id; // output wp_error
        }
        // set as post thumbnail if desired
        if ($thumb) {
            set_post_thumbnail($post_id, $att_id);
        }
        if ($return == 'src') {
            return wp_get_attachment_url($att_id);
        }
        return $att_id;
    }

    /**
     * Fetch activity logs and output JSON for datatables
     *
     * @return null
     */
    static function get_activity_logs()
    {
        global $wpdb;
        $options = LoginLockdown_Setup::get_options();

        $aColumns = array('login_attempt_ID', 'login_attempt_date', 'failed_user', 'failed_pass', 'login_attempt_IP', 'country', 'user_agent', 'reason');
        $sIndexColumn = "login_attempt_ID";

        // paging
        $sLimit = '';
        if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
            $sLimit = "LIMIT " . esc_sql($_GET['iDisplayStart']) . ", " .
                esc_sql($_GET['iDisplayLength']);
        } // paging

        // ordering
        $sOrder = '';
        if (isset($_GET['iSortCol_0'])) {
            $sOrder = "ORDER BY  ";
            for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
                if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
                    $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . " "
                        .  esc_sql($_GET['sSortDir_' . $i]) . ", ";
                }
            }

            $sOrder = substr_replace($sOrder, '', -2);
            if ($sOrder == "ORDER BY") {
                $sOrder = '';
            }
        } // ordering

        // filtering
        $sWhere = '';
        if (isset($_GET['sSearch']) && $_GET['sSearch'] != '') {
            $sWhere = "WHERE (";
            for ($i = 0; $i < count($aColumns); $i++) {
                $sWhere .= $aColumns[$i] . " LIKE '%" . esc_sql($_GET['sSearch']) . "%' OR ";
            }
            $sWhere  = substr_replace($sWhere, '', -3);
            $sWhere .= ')';
        } // filtering

        // individual column filtering
        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
                if ($sWhere == '') {
                    $sWhere = "WHERE ";
                } else {
                    $sWhere .= " AND ";
                }
                $sWhere .= $aColumns[$i] . " LIKE '%" . esc_sql($_GET['sSearch_' . $i]) . "%' ";
            }
        } // individual columns

        // build query
        $sQuery = "SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) .
            " FROM " . $wpdb->lockdown_login_fails . " $sWhere $sOrder $sLimit";

        $rResult = $wpdb->get_results($sQuery);

        // data set length after filtering
        $sQuery = "SELECT FOUND_ROWS()";
        $iFilteredTotal = $wpdb->get_var($sQuery);

        // total data set length
        $sQuery = "SELECT COUNT(" . $sIndexColumn . ") FROM " . $wpdb->lockdown_login_fails;
        $iTotal = $wpdb->get_var($sQuery);

        // construct output
        $output = array(
            "sEcho" => intval(@$_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        foreach ($rResult as $aRow) {
            $row = array();
            $row['DT_RowId'] = $aRow->login_attempt_ID;

            for ($i = 0; $i < count($aColumns); $i++) {
                if ($aColumns[$i] == 'login_attempt_date') {
                    $row[] = self::get_date_time(strtotime($aRow->{$aColumns[$i]}));
                } elseif ($aColumns[$i] == 'failed_user') {
                    $failed_login = '';
                    $failed_login .= '<strong>User:</strong> ' . htmlspecialchars($aRow->failed_user) . '<br />';
                    if($options['log_passwords'] == 1){
                        $failed_login .= '<strong>Pass:</strong> ' . htmlspecialchars($aRow->failed_pass) . '<br />';
                    } 
                    $row[] = $failed_login;
                } else if ($aColumns[$i] == 'login_attempt_IP') {
                    $location = '';
                    $country = '';

                    if(!empty($aRow->country)){
                        $country = $aRow->country;
                    } else {
                        $country = 'Unknown Location';
                    }

                    $country_code = LoginLockdown_Utility::country_name_to_code($country);

                    if($country_code != 'other'){
                        $location .= '<img src="' . LOGINLOCKDOWN_PLUGIN_URL . '/images/flags/' . strtolower($country_code) . '.png" />  ';
                    }

                    $location .= $country . '<br />';

                    if($options['anonymous_logging'] != '1'){
                        $location .= esc_html($aRow->login_attempt_IP);
                    } else {
                        $location = '<i>IP Anonymized</i>';
                    }
                    $row[] = $location;
                } elseif ($aColumns[$i] == 'user_agent') {
                    if (!empty(trim($aRow->{$aColumns[$i]}))) {
                        $row[] = LoginLockdown_Utility::parse_user_agent($aRow->{$aColumns[$i]});
                    } else {
                        $row[] = 'unknown';
                    }
                } elseif ($aColumns[$i] == 'reason') {
                    $row[] = LoginLockdown_Functions::pretty_fail_errors($aRow->{$aColumns[$i]});
                }
            }
            $row[] = '<div data-failed-id="' . $aRow->login_attempt_ID . '" class="tooltip delete_failed_entry" title="Delete failed login attempt log entry" data-msg-success="Failed login attempt log entry deleted" data-btn-confirm="Delete failed login attempt log entry" data-title="Delete failed login attempt log entry" data-wait-msg="Deleting. Please wait." data-name="" title="Delete this failed login attempt log entry"><i class="loginlockdown-icon loginlockdown-trash"></i></div>';
            $output['aaData'][] = $row;
        } // foreach row

        // json encoded output
        @ob_end_clean();
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        echo json_encode($output);
        die();
    }
} // class
