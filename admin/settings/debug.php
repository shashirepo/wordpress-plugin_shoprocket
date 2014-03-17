<?php
$tab = $data['tab'];
?>
<div id="saveResult"></div>
<div id="shoprocket-inner-tabs">
  <ul class="subsubsub">
    <li><a href="#debug-error_logging" class="debug-error_logging"><?php _e('Error Logging', 'shoprocket'); ?></a> | </li>
    <li><a href="#debug-session_settings" class="debug-session_settings"><?php _e('Session Management', 'shoprocket'); ?></a> | </li>
    <li><a href="#debug-debug_data" class="debug-debug_data"><?php _e('Debug Data', 'shoprocket'); ?></a></li>
  </ul>
  <br clear="all">
  <form id="errorLoggingAndDebugging" action="" method="post" class="ajaxSettingForm">
    <input type="hidden" name="action" value="save_settings" />
    <input type="hidden" name="_success" value="<?php _e('Your debug settings have been saved', 'shoprocket'); ?>." />
    <div id="debug-error_logging" class="pane">
      <h3><?php _e('Error Logging & Debugging', 'shoprocket'); ?></h3>
      <table class="form-table">
        <tbody>
          <tr valign="top">
            <th scope="row"><?php _e('Enable Logging', 'shoprocket'); ?></th>
            <td>
              <input type="radio" name="enable_logging" id="enable_logging_yes" value="1" <?php echo ShoprocketSetting::getValue('enable_logging') == 1 ? 'checked="checked" ' : '' ?>/>
              <label for="enable_logging_yes"><?php _e('Yes', 'shoprocket'); ?></label>
              <input type="radio" name="enable_logging" id="enable_logging_no" value="" <?php echo ShoprocketSetting::getValue('enable_logging') != 1 ? 'checked="checked" ' : '' ?>/>
              <label for="enable_logging_no"><?php _e('No', 'shoprocket'); ?></label>
              <p class="description"><?php _e( 'Only enable logging when testing your site. The log file will grow quickly.' , 'shoprocket' ); ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Disable Caching', 'shoprocket'); ?></th>
            <td>
              <select name="disable_caching" id="disable_caching">
                <option value="0"><?php _e( 'Never' , 'shoprocket' ); ?></option>
                <option value="1" <?php echo ShoprocketSetting::getValue('disable_caching') == 1 ? 'selected="selected"' : '' ?>><?php _e( 'On cart pages' , 'shoprocket' ); ?></option>
                <option value="2" <?php echo ShoprocketSetting::getValue('disable_caching') == 2 ? 'selected="selected"' : '' ?>><?php _e( 'On all pages' , 'shoprocket' ); ?></option>
              </select>
              <span class="label_desc"><?php _e( 'Send HTTP headers to prevent pages from being cached by web browsers.' , 'shoprocket' ); ?></span>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Delete Database when Uninstalling', 'shoprocket'); ?></th>
            <td>
              <input type="radio" name="uninstall_db" id="uninstall_db_yes" value="1" <?php echo ShoprocketSetting::getValue('uninstall_db') == 1 ? 'checked="checked" ' : '' ?>/>
              <label for="uninstall_db_yes"><?php _e('Yes', 'shoprocket'); ?></label>
              <input type="radio" name="uninstall_db" id="uninstall_db_no" value="" <?php echo ShoprocketSetting::getValue('uninstall_db') != 1 ? 'checked="checked" ' : '' ?>/>
              <label for="uninstall_db_no"><?php _e('No', 'shoprocket'); ?></label>
              <p class="description"><strong><?php _e( 'WARNING:', 'shoprocket'); ?></strong> <?php _e('Shoprocket Lite and Shoprocket Professional share the same database. If you are upgrading from Shoprocket Lite to Professional and want to keep all your settings', 'shoprocket'); ?>, <strong><?php _e('do not delete the database', 'shoprocket'); ?></strong> <?php _e('when uninstalling Shoprocket Lite', 'shoprocket'); ?>.</p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Reports Paging', 'shoprocket'); ?></th>
            <td>
              <input type="radio" name="page_product_report" id="page_product_report_yes" value="1" <?php echo ShoprocketSetting::getValue('page_product_report') == 1 ? 'checked="checked" ' : '' ?>/>
              <label for="page_product_report_yes"><?php _e('Yes', 'shoprocket'); ?></label>
              <input type="radio" name="page_product_report" id="page_product_report_no" value="" <?php echo ShoprocketSetting::getValue('page_product_report') != 1 ? 'checked="checked" ' : '' ?>/>
              <label for="page_product_report_no"><?php _e('No', 'shoprocket'); ?></label>
              <p class="description"><?php _e( 'Limit the number of products on the reports page to reduce server overhead. This is useful if you have very large numbers of products and/or the server utilizes shared resources.' , 'shoprocket' ); ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"></th>
            <td>
              <input name="page_product_report_size" id="page_product_report_size" value="<?php echo (ShoprocketSetting::getValue('page_product_report_size')) ? ShoprocketSetting::getValue('page_product_report_size') : '25'; ?>" size="3">
              <p class="description"><?php _e( 'Set the number of products per page in the reports.' , 'shoprocket' ); ?></p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div id="debug-session_settings" class="pane">
      <h3><?php _e('Session Management', 'shoprocket'); ?></h3>
      <table class="form-table">
        <tbody>
          <tr valign="top">
            <th scope="row"><?php _e('Database Sessions', 'shoprocket'); ?></th>
            <?php
              $session_type = ShoprocketCommon::sessionType();
            ?>
            <td>
              <input type="radio" name="session_type" id="session_type_database" value="database" <?php echo $session_type == 'database' ? 'checked="checked" ' : '' ?>/>
              <label for="session_type_database"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
              <input type="radio" name="session_type" id="session_type_native" value="native" <?php echo $session_type == 'native' ? 'checked="checked" ' : '' ?>/>
              <label for="session_type_native"><?php _e( 'No' , 'shoprocket' ); ?></label>
              <p class="description"><?php _e( 'Database sessions offer better performance but if you have trouble with them, try using the standard PHP sessions by disabling database sessions' , 'shoprocket' ); ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Disable IP Validation', 'shoprocket'); ?></th>
            <td>
              <input type="radio" name="session_ip_validation" id="session_ip_validation_yes" value="1" <?php echo ShoprocketSetting::getValue('session_ip_validation') == '1' ? 'checked="checked"' : '' ?>/>
              <label for="session_ip_validation_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
              <input type='radio' name='session_ip_validation' id='session_ip_validation_no' value="" <?php echo ShoprocketSetting::getValue('session_ip_validation') != '1'? 'checked="checked"' : '' ?>/>
              <label for="session_ip_validation_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
              <p class="description"><?php _e( 'If the shopping cart seems to be dropping it\'s contents it could be because your ip address changes while you are shopping. If so, disable IP address validation.' , 'shoprocket' ); ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Session Length', 'shoprocket'); ?></th>
            <td>
              <input type="text" name="session_length" id="session_length" class="small-text" value="<?php echo ShoprocketSetting::getValue('session_length'); ?>" />
              <span class="description"><?php _e( 'Set the length of the session in minutes. Leave this blank to keep the default session length of 30 minutes' , 'shoprocket' ); ?></span>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Session Table Status', 'shoprocket'); ?></th>
            <td>
              <?php
              global $wpdb;
              $wpdb->query('CHECK TABLE `' . ShoprocketCommon::getTableName('sessions') . '` QUICK');
              if($wpdb->last_result[0]->Msg_text != "OK" && isset($_GET['sessions']) && $_GET['sessions'] == 'repair') {
                $wpdb->query('REPAIR TABLE `' . ShoprocketCommon::getTableName('sessions') . '`');
                $wpdb->query('CHECK TABLE `' . ShoprocketCommon::getTableName('sessions') . '` QUICK');
              }
              echo ShoprocketSetting::validateDebugValue($wpdb->last_result[0]->Msg_text,__("OK", "shoprocket"));
              if($wpdb->last_result[0]->Msg_text != "OK") { ?>
                <a href="?page=shoprocket-settings&tab=debug_settings&sessions=repair" class="button-secondary"><?php _e('Repair Table', 'shoprocket'); ?></a>
              <?php } ?>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <table class="form-table debug-submit">
      <tbody>
        <tr valign="top">
          <th scope="row">
            <?php submit_button(); ?>
          </th>
          <td></td>
        </tr>
      </tbody>
    </table>
  </form>
  <div id="debug-debug_data" class="pane">
    <table class="form-table">
      <tbody>
        <?php if(ShoprocketLog::exists()): ?>
          <tr valign="top">
            <th scope="row"><?php _e('Error Log', 'shoprocket'); ?></th>
            <td>
              <form action="" method="post" style="display:inline-block">
                <input type="hidden" name="shoprocket-action" value="download log file" id="download-log-file" />
                <input type="submit" value="<?php _e('Download Log File', 'shoprocket'); ?>" class="button-secondary" />
              </form>
              <form action="" method="post" style="display:inline-block">
                <input type="hidden" name="shoprocket-action" value="clear log file" id="clear-log-file" />
                <input type="submit" value="<?php _e('Clear Log File', 'shoprocket'); ?>" class="button-secondary" />
              </form>
            </td>
          </tr>
        <?php endif; ?>
        <tr valign="top">
          <th scope="row"><?php _e('Shoprocket', 'shoprocket'); ?><?php echo false ? __(" Professional","shoprocket") : ""; ?> <?php _e('Version', 'shoprocket'); ?></th>
          <td>
            <?php echo ShoprocketSetting::getValue('version');?>
            
            <form action="" method="post" id="forcePluginUpdate">
              <input type="hidden" name="action" value="force_plugin_update" />
              <input type="submit" value="<?php _e('Check for updates', 'shoprocket'); ?>" class="button-secondary" />
            </form>
          </td>
        </tr>        
        <tr valign="top">
          <th scope="row"><?php _e('WordPress Version', 'shoprocket'); ?></th>
          <td>
            <?php echo get_bloginfo("version"); ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('WPMU', 'shoprocket'); ?></th>
          <td>
            <?php echo ShoprocketSetting::validateDebugValue((!defined('MULTISITE') || !MULTISITE) ? "False" : "True", "False");  ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('PHP Version', 'shoprocket'); ?></th>
          <td>
            <?php echo phpversion(); ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Session Save Path', 'shoprocket'); ?></th>
          <td>
            <?php echo ini_get("session.save_path"); ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('MySQL Version', 'shoprocket'); ?></th>
          <td>
            <?php global $wpdb; ?>
            <?php echo $wpdb->db_version();?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('MySQL Mode', 'shoprocket'); ?></th>
          <td>
            <?php 
            $mode = $wpdb->get_row("SELECT @@SESSION.sql_mode as Mode"); 
            if(empty($mode->Mode)){
              $sqlMode = __("Normal", "shoprocket");
            }
            else {
              $sqlMode = $mode->Mode;
            }
            echo ShoprocketSetting::validateDebugValue($sqlMode,__("Normal", "shoprocket")); ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Table Prefix', 'shoprocket'); ?></th>
          <td>
            <?php echo $wpdb->prefix; ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Tables', 'shoprocket'); ?></th>
          <td>
            <?php 
            $required_tables = array(
              $wpdb->prefix . "falseducts",
              $wpdb->prefix . "shoprocket_downloads",
              $wpdb->prefix . "falsemotions",
              $wpdb->prefix . "shoprocket_shipping_methods",
              $wpdb->prefix . "shoprocket_shipping_rates",
              $wpdb->prefix . "shoprocket_shipping_rules",
              $wpdb->prefix . "shoprocket_tax_rates",
              $wpdb->prefix . "shoprocket_cart_settings",
              $wpdb->prefix . "shoprocket_membership_reminders",
              $wpdb->prefix . "shoprocket_email_log",
              $wpdb->prefix . "shoprocket_orders",
              $wpdb->prefix . "shoprocket_order_items",
              $wpdb->prefix . "shoprocket_order_fulfillment",
              $wpdb->prefix . "shoprocket_Sync",
              $wpdb->prefix . "shoprocket_accounts",
              $wpdb->prefix . "shoprocket_account_subscriptions",
              $wpdb->prefix . "shoprocket_pp_recurring_payments",
              $wpdb->prefix . "shoprocket_sessions"
            );
            $matched_tables = $wpdb->get_results("SHOW TABLES LIKE '".$wpdb->prefix."shoprocket_%'","ARRAY_N");
            if(empty($matched_tables)){
              $tableStatus = __("All Tables Are Missing!", "shoprocket");
            }
            else {
              foreach($matched_tables as $key=>$table){
                $cart_tables[] = $table[0];
              }

              $diff = array_diff($required_tables,$cart_tables);
              if(!empty($diff)){
                $tableStatus = __("Missing tables: ", "shoprocket") . '<br />';
                foreach($diff as $key=>$table){
                  $tableStatus .= "$table" . '<br />';
                }
              }
              else {
                $tableStatus = __("All Tables Present", "shoprocket");
              }
            }
            echo ShoprocketSetting::validateDebugValue($tableStatus,__("All Tables Present", "shoprocket"));
            ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Current Directory', 'shoprocket'); ?></th>
          <td>
            <?php echo getcwd(); ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('WordPress URL', 'shoprocket'); ?></th>
          <td>
            <?php echo get_bloginfo('wpurl'); ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Server Name', 'shoprocket'); ?></th>
          <td>
            <?php echo $_SERVER['SERVER_NAME']; ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Cookie Domain', 'shoprocket'); ?></th>
          <td>
            <?php $cookieDomain = parse_url( strtolower( get_bloginfo('wpurl') ) ); echo $cookieDomain['host']; ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Curl Test', 'shoprocket'); ?></th>
          <td>
            <?php
            if(!function_exists('curl_init')){ 
              echo "<span class=\"failedDebug\">" . __("CURL is not installed","shoprocket") . "</span>";
            }
            else {
              $shoprocketCurlTest = (isset($_GET['shoprocket_curl_test'])) ? $_GET['shoprocket_curl_test'] : false;
              if($shoprocketCurlTest == "run"){
                $ch = curl_init();
                curl_setopt($ch,CURLOPT_URL,"https://shoprocket.com/curl-test.php");
                curl_setopt($ch, CURLOPT_POST, 1); 
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch,CURLOPT_POSTFIELDS,"curl_check=validate");
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                $result = curl_exec($ch);
                curl_close($ch);
                echo ($result == "PASS") ? __("PASSED","shoprocket") : __("FAILED","shoprocket");
              }
              else{
                echo "<a href='admin.php?page=shoprocket-settings&tab=debug_settings&shoprocket_curl_test=run'>" . __("Run Test","shoprocket") . "</a>";
              }
            }
            ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Write Permissions', 'shoprocket'); ?></th>
          <td>
            <?php 
            $isWritable = (is_writable(SHOPROCKET_PATH)) ? __("Writable", "shoprocket") : __("Not Writable", "shoprocket");
            echo ShoprocketSetting::validateDebugValue($isWritable,__("Writable", "shoprocket"));
            ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Subscription Reminders Last Checked', 'shoprocket'); ?></th>
          <td>
            <?php echo (ShoprocketSetting::getValue('daily_subscription_reminders_last_checked')) ? ShoprocketCommon::getElapsedTime(date('Y-m-d H:i:s', ShoprocketSetting::getValue('daily_subscription_reminders_last_checked'))) : __("Never","shoprocket"); ?>
            <form action="" method="post" style="display:inline-block">
              <input type="hidden" name="shoprocket-action" value="check subscription reminders" id="shoprocket-action" />
              <input type="submit" value="<?php _e('Send Subscription Reminders', 'shoprocket'); ?>" class="button-secondary" />
            </form>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Followup Emails Last Checked', 'shoprocket'); ?></th>
          <td>
            <?php echo (ShoprocketSetting::getValue('daily_followup_last_checked')) ? ShoprocketCommon::getElapsedTime(date('Y-m-d H:i:s', ShoprocketSetting::getValue('daily_followup_last_checked'))) : __("Never","shoprocket"); ?>
            <form action="" method="post" style="display:inline-block">
              <input type="hidden" name="shoprocket-action" value="check followup emails" id="shoprocket-action" />
              <input type="submit" value="<?php _e('Send Followup Emails', 'shoprocket'); ?>" class="button-secondary" />
            </form>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Prune Pending Orders Last Checked', 'shoprocket'); ?></th>
          <td>
            <?php echo (ShoprocketSetting::getValue('daily_prune_pending_orders_last_checked')) ? ShoprocketCommon::getElapsedTime(date('Y-m-d H:i:s', ShoprocketSetting::getValue('daily_prune_pending_orders_last_checked'))) : __("Never","shoprocket"); ?>
            <form action="" method="post" style="display:inline-block">
              <input type="hidden" name="shoprocket-action" value="prune pending orders" id="shoprocket-action" />
              <input type="submit" value="<?php _e('Prune Pending Orders', 'shoprocket'); ?>" class="button-secondary" />
            </form>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Next Scheduled Cron Check', 'shoprocket'); ?></th>
          <td>
            <?php echo ShoprocketCommon::getTimeLeft(date('Y-m-d H:i:s', ShoprocketCommon::localTs(wp_next_scheduled('daily_subscription_reminder_emails')))) . ' (' . date('Y-m-d H:i:s', ShoprocketCommon::localTs(wp_next_scheduled('daily_subscription_reminder_emails')) ) . ')'; ?>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
<script type="text/javascript">
  (function($){
    $(document).ready(function(){
      $('#shoprocket-inner-tabs div.pane').hide();
      $('#shoprocket-inner-tabs div#<?php echo $tab; ?>').show();
      $('#shoprocket-inner-tabs ul li a.<?php echo $tab; ?>').addClass('current');
      
      if($('#debug-debug_data').attr('id') == '<?php echo $tab; ?>') {
        $('.debug-submit').hide();
      }
      
      $('#shoprocket-inner-tabs ul li a').click(function(){
        $('#shoprocket-inner-tabs ul li a').removeClass('current');
        $(this).addClass('current');
        var currentTab = $(this).attr('href');
        $('#shoprocket-inner-tabs div.pane').hide();
        $(currentTab).show();
        $('.debug-submit').show();
        if(currentTab == '#debug-debug_data') {
          $('.debug-submit').hide();
        }
        return false;
      });
    })
  })(jQuery);
</script>