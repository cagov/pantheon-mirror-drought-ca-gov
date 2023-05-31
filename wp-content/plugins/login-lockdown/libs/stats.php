<?php
/**
 * Login Lockdown Pro
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_Stats extends LoginLockdown
{
    static public $stats_cutoff = 1;

    /**
     * Get statistics
     *
     * @since 5.0
     *
     * @param string $type locks|fails
     * @param int $ndays period for statistics
     * @return bool
     */
    static function get_stats($type = "locks", $ndays = 60)
    {
        global $wpdb;
        $options = LoginLockdown_Setup::get_options();

        $days = array();
        for ($i = $ndays; $i >= 0; $i--){
            $days[date("Y-m-d", strtotime('-' . $i . ' days'))] = 0;
        }

        if ($type == 'locks') {
            $results = $wpdb->get_results("SELECT COUNT(*) as count,DATE_FORMAT(lockdown_date, '%Y-%m-%d') AS date FROM " . $wpdb->lockdown_lockdowns . " GROUP BY DATE_FORMAT(lockdown_date, '%Y%m%d')");
        } else {
            $results = $wpdb->get_results("SELECT COUNT(*) as count,DATE_FORMAT(login_attempt_date, '%Y-%m-%d') AS date FROM " . $wpdb->lockdown_login_fails . " GROUP BY DATE_FORMAT(login_attempt_date, '%Y%m%d')");
        }

        $total = 0;

        foreach ($results as $day) {
            if(array_key_exists($day->date, $days)){
                $days[$day->date] = $day->count;
                $total += $day->count;
            }
        }

        if ($total < self::$stats_cutoff) {
            $stats['days'] = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20);
            $stats['count'] = array(3, 4, 67, 76, 45, 32, 134, 6, 65, 65, 56, 123, 156, 156, 123, 156, 67, 88, 54, 178);
            $stats['total'] = $total;

            return $stats;
        }

        $stats = array('days' => array(), 'count' => array(), 'total' => 0);
        foreach ($days as $day => $count) {
            $stats['days'][] = $day;
            $stats['count'][] = $count;
            $stats['total'] += $count;
        }
        $stats['period'] = $ndays;
        return $stats;
    } // get_stats

    static function prepare_stats($ndays = 20)
    {

        global $wpdb;

        LoginLockdown_Setup::register_custom_tables();

        $stats = array('locks' => array('count' => array()), 'fails' => array('count' => array()));
        for ($i = $ndays; $i >= 0; $i--){
            $stats['locks']['count'][date("Y-m-d", strtotime('-' . $i . ' days'))] = 0;
            $stats['fails']['count'][date("Y-m-d", strtotime('-' . $i . ' days'))] = 0;
        }

        $locks = $wpdb->get_results("SELECT COUNT(*) as count,DATE_FORMAT(lockdown_date, '%Y-%m-%d') AS date FROM " . $wpdb->lockdown_lockdowns . " GROUP BY DATE_FORMAT(lockdown_date, '%Y%m%d')");
        foreach ($locks as $day) {
            if(array_key_exists($day->date, $stats['locks']['count'])){
                $stats['locks']['count'][$day->date] = $day->count;
            }
        }

        $fails = $wpdb->get_results("SELECT COUNT(*) as count,DATE_FORMAT(login_attempt_date, '%Y-%m-%d') AS date FROM " . $wpdb->lockdown_login_fails . " GROUP BY DATE_FORMAT(login_attempt_date, '%Y%m%d')");
        foreach ($fails as $day) {
            if(array_key_exists($day->date, $stats['fails']['count'])){
                $stats['fails']['count'][$day->date] = $day->count;
            }
        }

        $stats['locks']['countries'] = self::get_top_countries('locks');
        $stats['locks']['browsers'] = self::get_top_browsers('locks');
        $stats['locks']['devices'] = self::get_top_devices('locks');
        $stats['locks']['traffic'] = self::get_top_bots('locks');

        $stats['fails']['countries'] = self::get_top_countries('fails');
        $stats['fails']['browsers'] = self::get_top_browsers('fails');
        $stats['fails']['devices'] = self::get_top_devices('fails');
        $stats['fails']['traffic'] = self::get_top_bots('fails');
        return $stats;
    } // prepare_stats

    /**
     * Get top countries
     *
     * @since 5.0
     *
     * @param string $type redirect/404
     * @param int $limit number of countries to return
     * @return array of countries with percent
     */
    static function get_top_countries($type = 'locks', $limit = 10)
    {
        global $wpdb;

        if ($type == 'locks') {
            $table = $wpdb->lockdown_lockdowns;
        } else {
            $table = $wpdb->lockdown_login_fails;
        }

        $countries_db = $wpdb->get_results("SELECT country,COUNT(*) AS count FROM " . $table . " GROUP BY country ORDER BY COUNT DESC");

        $countries = array();
        $countries_percent = array();
        $other = 0;
        $total = 0;
        foreach ($countries_db as $country) {
            $total += $country->count;
            if (empty($country->country) || $limit == 1) {
                $other += $country->count;
            } else {
                $countries[$country->country] = $country->count;
                $limit--;
            }
        }

        if ($total < self::$stats_cutoff) {
            $countries_percent = array(
                'China' => '45',
                'Nigeria' => '25',
                'Ukraine' => '12',
                'France' => '10',
                'United States' => '8',
                'Germany' => '3',
                'Russia' => '1'
            );
            return $countries_percent;
        }

        if ($other > 0) {
            $countries['Other'] = $other;
        }

        foreach ($countries as $country => $count) {
            $countries_percent[$country] = round($count / $total * 1000) / 10;
        }

        return $countries_percent;
    } // get_top_countries

    static function get_map_stats(){
        $map_countries = self::get_top_countries('fails');
        $map_stats = array();
        foreach($map_countries as $country => $percent){
            $country_coords = LoginLockdown_Utility::country_code_to_coordinates(LoginLockdown_Utility::country_name_to_code($country));
            $map_stats[] = array(
                'name' => $country,
                'latitude' => $country_coords[0],
                'longitude' => $country_coords[1],
                'fillKey' => 'failed',
                'radius' => round($percent)
            );
        }
        return $map_stats;
    }

    /**
     * Get top browsers
     *
     * @since 5.0
     *
     * @param string $type locks/fails
     * @param int $limit number of browsers to return
     * @return array of browsers with percent
     */
    static function get_top_browsers($type = 'locks', $limit = 10)
    {
        global $wpdb;

        if ($type == 'locks') {
            $table = $wpdb->lockdown_lockdowns;
        } else {
            $table = $wpdb->lockdown_login_fails;
        }

        $browsers_db = $wpdb->get_results("SELECT user_agent_browser,COUNT(*) AS count FROM " . $table . " WHERE user_agent_device!='bot' GROUP BY user_agent_browser ORDER BY COUNT DESC");

        $browsers = array();
        $browsers_percent = array();
        $other = 0;
        $total = 0;
        foreach ($browsers_db as $browser) {
            $total += $browser->count;
            if (empty($browser->user_agent_browser) || $limit == 1) {
                $other += $browser->count;
            } else {
                $browsers[$browser->user_agent_browser] = $browser->count;
                $limit--;
            }
        }

        if ($total < self::$stats_cutoff) {
            $browsers_percent = array(
                'Chrome' => '35',
                'Internet Explorer' => '34',
                'Firefox' => '24',
                'Safari' => '2',
                'Opera' => '1'
            );
            return $browsers_percent;
        }

        if ($other > 0) {
            $browsers['Other'] = $other;
        }

        foreach ($browsers as $browser => $count) {
            $browsers_percent[$browser] = round($count / $total * 1000) / 10;
        }

        return $browsers_percent;
    } // get_top_browsers

    /**
     * Get top devices
     *
     * @since 5.0
     *
     * @param string $type locks/fails
     * @param int $limit number of devices to return
     * @return array of devices with percent
     */
    static function get_top_devices($type = 'locks')
    {
        global $wpdb;

        if ($type == 'locks') {
            $table = $wpdb->lockdown_lockdowns;
        } else {
            $table = $wpdb->lockdown_login_fails;
        }

        $devices_db = $wpdb->get_results("SELECT user_agent_device,COUNT(*) AS count FROM " . $table . " WHERE user_agent_device!='bot' GROUP BY user_agent_device ORDER BY COUNT DESC");

        $devices = array();
        $devices_percent = array();
        $other = 0;
        $total = 0;
        foreach ($devices_db as $device) {
            $total += $device->count;
            $devices[$device->user_agent_device] = $device->count;
        }

        if ($total < self::$stats_cutoff) {
            $devices_percent = array(
                'mobile' => 14,
                'tablet' => 26,
                'desktop' => 60
            );
        }

        if ($other > 0) {
            $devices['other'] = $other;
        }
        foreach ($devices as $device => $count) {
            $devices_percent[$device] = round($count / $total, 2) * 100;
        }

        return $devices_percent;
    } // get_top_devices

    /**
     * Get device stats
     *
     * @since 5.0
     *
     * @param string $type redirect/404
     * @return array of 2 sub-arrays with labels and devices for charts
     */
    static function get_device_stats($type)
    {
        $devices = self::get_top_devices($type);
        $device_stats = array('labels' => array(), 'percent' => array());
        foreach ($devices as $device => $percent) {
            $device_stats['labels'][] = ucfirst($device);
            $device_stats['percent'][] = $percent;
        }

        return $device_stats;
    } // get_device_stats

    /**
     * Get top bots
     *
     * @since 5.0
     *
     * @param string $type locks/fails
     * @param int $limit number of bots to return
     * @return array of bots with percent
     */
    static function get_top_bots($type = 'locks', $limit = 10)
    {
        global $wpdb;

        if ($type == 'locks') {
            $table = $wpdb->lockdown_lockdowns;
        } else {
            $table = $wpdb->lockdown_login_fails;
        }

        $bots_db = $wpdb->get_results("SELECT user_agent_bot,COUNT(*) AS count FROM " . $table . " GROUP BY user_agent_bot ORDER BY COUNT DESC");

        $bots = array();
        $bots_percent = array();
        $other = 0;
        $human = 0;
        $total = 0;

        foreach ($bots_db as $bot) {
            $total += $bot->count;
            if (empty($bot->user_agent_bot) || $limit == 1) {
                $human += $bot->count;
            } else if ($limit == 1) {
                $other += $bot->count;
            } else {
                $bots[$bot->user_agent_bot] = $bot->count;
                $limit--;
            }
        }

        if ($total < self::$stats_cutoff) {
            $bots_percent = array(
                'Human' => '35',
                'Google' => '34',
                'Bing' => '24',
                'Archive' => '2',
                'Other' => '1'
            );
            return $bots_percent;
        }

        if ($human > 0) {
            $bots['Human'] = $human;
        }

        arsort($bots);

        if ($other > 0) {
            $bots['Other'] = $other;
        }

        foreach ($bots as $bot => $count) {
            $bots_percent[$bot] = round($count / $total * 1000) / 10;
        }

        return $bots_percent;
    }

    /**
     * Function to print stats in admin tabs
     *
     * @since 5.0
     *
     * @param string $type redirect/404
     * @return null
     */
    static function print_stats($type = 'locks')
    {
        $countries = self::get_top_countries($type);
        echo '<div class="loginlockdown-stats-column">';
        echo '<h3>Top Countries</h3>';
        echo '<table class="loginlockdown-stats-table">';
        foreach ($countries as $country => $count) {
            echo '<tr><td>' . ($country != 'Other' ? '<img src="' . LOGINLOCKDOWN_PLUGIN_URL . 'images/flags/' . strtolower(LoginLockdown_Utility::country_name_to_code($country)) . '.png" /> ' : '<img src="' . LOGINLOCKDOWN_PLUGIN_URL . 'images/flags/other.png" /> ') . $country . '</td><td>' . $count . '%</td></tr>';
        }
        echo '</table>';
        echo '</div>';

        $browsers = self::get_top_browsers($type);
        echo '<div class="loginlockdown-stats-column">';
        echo '<h3>Top Browsers</h3>';
        echo '<table class="loginlockdown-stats-table">';
        foreach ($browsers as $browser => $count) {
            echo '<tr><td>' . $browser . '</td><td>' . $count . '%</td></tr>';
        }
        echo '</table>';
        echo '</div>';



        echo '<div class="loginlockdown-stats-column">';
        echo '<h3>Top Devices</h3>';
        echo '<div class="loginlockdown-pie-chart-wrapper"><canvas id="loginlockdown_' . $type . '_devices_chart"></canvas></div>';
        echo '</div>';


        $bots = self::get_top_bots($type);
        echo '<div class="loginlockdown-stats-column">';
        echo '<h3>Traffic Type</h3>';
        echo '<table class="loginlockdown-stats-table">';
        foreach ($bots as $bot => $count) {
            echo '<tr><td>' . ($bot == 'Human' ? '<span class="human">Human</span>' : $bot) . '</td><td>' . $count . '%</td></tr>';
        }
        echo '</table>';
        echo '</div>';
    }
} // class
