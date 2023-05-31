<?php
/**
 * Login Lockdown Pro
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_Tab_Activity extends LoginLockdown
{
    static function display()
    {
        $tabs[] = array('id' => 'tab_log_locks', 'class' => 'tab-content', 'label' => __('Lockdowns', 'login-lockdown'), 'callback' => array(__CLASS__, 'tab_locks'));
        $tabs[] = array('id' => 'tab_log_full', 'class' => 'tab-content', 'label' => __('Failed Logins', 'login-lockdown'), 'callback' => array(__CLASS__, 'tab_full'));

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

    static function tab_locks()
    {
        echo '<div class="loginlockdown-stats-main loginlockdown-chart-locks" style="display:none"><canvas id="loginlockdown-locks-chart" style="height: 160px; width: 100%;"></canvas></div>';
        echo '<div class="loginlockdown-stats-main loginlockdown-stats-locks" style="display:none">';
            LoginLockdown_Stats::print_stats('locks');
        echo '</div>';
        echo '<div class="tab-content">';
            echo '<div id="loginlockdown-locks-log-table-wrapper">
                    <table cellpadding="0" cellspacing="0" border="0" class="display" id="loginlockdown-locks-log-table">
                        <thead>
                            <tr>
                                <th></th>
                                <th style="width:160px;">Date &amp; time</th>
                                <th>Reason</th>
                                <th>Location/IP</th>
                                <th style="width:280px;">User Agent</th>
                                <th style="width:80px;"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th>Date &amp; time</th>
                                <th>Reason</th>
                                <th>Location/IP</th>
                                <th>User Agent</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div data-log="locks" class="tooltip empty_log tooltipstered" data-msg-success="Lockdowns Log Emptied" data-btn-confirm="Yes, empty the log" data-title="Are you sure you want to empty the Lockdowns Log?" data-wait-msg="Emptying. Please wait." data-name=""><i class="loginlockdown-icon loginlockdown-trash"></i> Empty Lockdowns Log</div>';
        echo '</div>';
    }

    static function tab_full()
    {
        echo '<div class="loginlockdown-stats-main loginlockdown-chart-fails" style="display:none"><canvas id="loginlockdown-fails-chart" style="height: 160px; width: 100%;"></canvas></div>';
        echo '<div class="loginlockdown-stats-main loginlockdown-stats-fails" style="display:none">';
            LoginLockdown_Stats::print_stats('fails');
        echo '</div>';
        echo '<div class="tab-content">';
            echo '<div id="loginlockdown-fails-log-table-wrapper">
                    <table cellpadding="0" cellspacing="0" border="0" class="display" id="loginlockdown-fails-log-table">
                        <thead>
                            <tr>
                                <th style="width:160px;">Date &amp; time</th>
                                <th style="width:280px;">User/Pass</th>
                                <th>Location/IP</th>
                                <th style="width:280px;">User Agent</th>
                                <th style="width:280px;">Reason</th>
                                <th style="width:80px;"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th>Date &amp; time</th>
                                <th>User/Pass</th>
                                <th>Location/IP</th>
                                <th>User Agent</th>
                                <th>Reason</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div data-log="fails" class="tooltip empty_log tooltipstered" data-msg-success="Fails Log Emptied" data-btn-confirm="Yes, empty the log" data-title="Are you sure you want to empty the Failed Logins Log?" data-wait-msg="Emptying. Please wait." data-name=""><i class="loginlockdown-icon loginlockdown-trash"></i> Empty Failed Logins Log</div>';
        echo '</div>';
    }
} // class LoginLockdown_Tab_Activity
