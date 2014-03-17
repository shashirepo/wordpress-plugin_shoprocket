<?php
class ShoprocketSetting {
  
  private $_settings_tabs = array();
  
  public function __construct($tabs=null) {
    if($tabs) {
      return $this->registerSettings($tabs);
    }
  }
  
  /*
   * Registers the general settings via the Settings API,
   * appends the setting to the tabs array of the object.
  */
  public function registerSettings($tabs) {
    foreach($tabs as $tab_key => $tab_caption) {
      $this->_settings_tabs[$tab_key . '_settings'] = $tab_caption;
    }
    
    foreach($this->_settings_tabs as $key => $value) {
      register_setting($key, $key);
      add_settings_section('section_' . $key, $value['title'], array($this, 'section_' . $key), $key);
    }
  }
  
  /*
   * The following methods provide content
   * for the respective sections, used as callbacks
   * with add_settings_section
  */
  public function section_main_settings() {
    $successMessage = '';
    $versionInfo = false;
    $orderNumberFailed = '';
    if($_SERVER['REQUEST_METHOD'] == "POST") {
      if($_POST['shoprocket-action'] == 'saveOrderNumber' && false) {
        $orderNumber = trim(ShoprocketCommon::postVal('order_number'));
        ShoprocketSetting::setValue('order_number', $orderNumber);
        $versionInfo = get_transient('_shoprocket_version_request');
        if(!$versionInfo) {
          $versionInfo = ShoprocketProCommon::getVersionInfo();
          set_transient('_shoprocket_version_request', $versionInfo, 43200);
        }
        if($versionInfo) {
          $successMessage = __("Thank you! Shoprocket has been activated","shoprocket");
        }
        else {
          ShoprocketSetting::setValue('order_number', '');
          $orderNumberFailed = true;
        }
      }
    }
    $data = array(
      'success_message' => $successMessage,
      'version_info' => $versionInfo,
      'order_number_failed' => $orderNumberFailed
    );
    echo ShoprocketCommon::getView('admin/settings/main.php', $data, false);
  }
  
  
  public function section_cart_checkout_settings() {
    echo ShoprocketCommon::getView('admin/settings/cart-checkout.php', null, false);
  }
  

  public function section_notifications_settings() {
    $tab = 'notifications-email_receipt_settings';
    $data = array('tab' => $tab);
    if(false) {
      $reminder = new ShoprocketMembershipReminders();
      $orderFulfillment = new ShoprocketOrderFulfillment();
      $errorMessage = '';
      $successMessage = '';
      if($_SERVER['REQUEST_METHOD'] == "POST") {
        if($_POST['shoprocket-action'] == 'email log settings') {
          foreach($_POST['emailLog'] as $key => $value) {
            ShoprocketSetting::setValue($key, $value);
          }
          $tab = 'notifications-email_log_settings';
        }
        if($_POST['shoprocket-action'] == 'save reminder') {
          try {
            $reminder->load($_POST['reminder']['id']);
            $reminder->setData($_POST['reminder']);
            $reminder->save();
            $reminder->clear();
          }
          catch(ShoprocketException $e) {
            $errorCode = $e->getCode();
            // Reminder save failed
            if($errorCode == 66302) {
              $errors = $reminder->getErrors();
              $exception = ShoprocketException::exceptionMessages($e->getCode(), __("The reminder could not be saved for the following reasons","shoprocket"), $errors);
              $errorMessage = ShoprocketCommon::getView('views/error-messages.php', $exception);
            }
          }
          $tab = 'notifications-reminder_settings';
        }
        if($_POST['shoprocket-action'] == 'save order fulfillment') {
          try {
            $orderFulfillment->load($_POST['fulfillment']['id']);
            $orderFulfillment->setData($_POST['fulfillment']);
            $orderFulfillment->save();
            $orderFulfillment->clear();
          }
          catch(ShoprocketException $e) {
            $errorCode = $e->getCode();
            if($errorCode == 66303) {
              $errors = $orderFulfillment->getErrors();
              $exception = ShoprocketException::exceptionMessages($e->getCode(), __("The order fulfillment could not be saved for the following reasons","shoprocket"), $errors);
              $errorMessage = ShoprocketCommon::getView('views/error-messages.php', $exception);
            }
          }
          $tab = 'notifications-fulfillment_settings';
        }
        if($_POST['shoprocket-action'] == 'advanced notifications') {
          ShoprocketSetting::setValue('enable_advanced_notifications', $_POST['enable_advanced_notifications']);
          $successMessage = __('Your notification settings have been saved.', 'shoprocket');
          $tab = 'notifications-advanced_notifications';
        }
      }
      elseif($_SERVER['REQUEST_METHOD'] == "GET") {
        if(isset($_GET['task']) && $_GET['task'] == 'edit_reminder' && isset($_GET['id']) && $_GET['id'] > 0) {
          $id = ShoprocketCommon::getVal('id');
          $reminder->load($id);
          $tab = 'notifications-reminder_settings';
        }
        elseif(isset($_GET['task']) && $_GET['task'] == 'delete_reminder' && isset($_GET['id']) && $_GET['id'] > 0) {
          $id = ShoprocketCommon::getVal('id');
          $reminder->load($id);
          $reminder->deleteMe();
          $reminder->clear();
          $tab = 'notifications-reminder_settings';
        }
        elseif(isset($_GET['task']) && $_GET['task'] == 'cancel_reminder') {
          $tab = 'notifications-reminder_settings';
        }
        elseif(isset($_GET['task']) && $_GET['task'] == 'edit_fulfillment' && isset($_GET['id']) && $_GET['id'] > 0) {
          $id = ShoprocketCommon::getVal('id');
          $orderFulfillment->load($id);
          $tab = 'notifications-fulfillment_settings';
        }
        elseif(isset($_GET['task']) && $_GET['task'] == 'delete_fulfillment' && isset($_GET['id']) && $_GET['id'] > 0) {
          $id = ShoprocketCommon::getVal('id');
          $orderFulfillment->load($id);
          $orderFulfillment->deleteMe();
          $orderFulfillment->clear();
          $tab = 'notifications-fulfillment_settings';
        }
        elseif(isset($_GET['task']) && $_GET['task'] == 'cancel_fulfillment') {
          $tab = 'notifications-fulfillment_settings';
        }
      }

      $data = array(
        'reminder' => $reminder,
        'order_fulfillment' => $orderFulfillment,
        'tab' => $tab,
        'error_message' => $errorMessage,
        'success_message' => $successMessage
      );
    }
    echo ShoprocketCommon::getView('admin/settings/notifications.php', $data, false);
  }
  
  public function section_integrations_settings() {
    echo ShoprocketCommon::getView('admin/settings/integrations.php', null, false);
  }
  
  public function section_debug_settings() {
    $tab = 'debug-error_logging';
    if((isset($_GET['shoprocket_curl_test']) && $_GET['shoprocket_curl_test'] == 'run') || (isset($_POST['shoprocket-action']) && $_POST['shoprocket-action'] == 'clear log file')) {
      $tab = 'debug-debug_data';
    }
    elseif(isset($_POST['shoprocket-action']) && $_POST['shoprocket-action'] == 'check subscription reminders') {
      ShoprocketMembershipReminders::dailySubscriptionEmailReminderCheck();
      $tab = 'debug-debug_data';
    }
    elseif(isset($_POST['shoprocket-action']) && $_POST['shoprocket-action'] == 'check followup emails') {
      ShoprocketAdvancedNotifications::dailyFollowupEmailCheck();
      $tab = 'debug-debug_data';
    }
    elseif(isset($_POST['shoprocket-action']) && $_POST['shoprocket-action'] == 'prune pending orders') {
      $order = new ShoprocketOrder();
      $order->dailyPrunePendingPayPalOrders();
      $tab = 'debug-debug_data';
    }
    elseif(isset($_GET['sessions']) && $_GET['sessions'] == 'repair') {
      $tab = 'debug-session_settings';
    }
    $data = array(
      'tab' => $tab
    );
    echo ShoprocketCommon::getView('admin/settings/debug.php', $data, false);
  }
  
  public function getSettingsTabs() {
    return $this->_settings_tabs;
  }
  
  public static function setValue($key, $value) {
    global $wpdb;
    $settingsTable = ShoprocketCommon::getTableName('cart_settings');
    
    if(!empty($key)) {
      $dbKey = $wpdb->get_var("SELECT `key` from $settingsTable where `key`='$key'");
      if($dbKey) {
        if(!empty($value)) {
          $wpdb->update($settingsTable, 
            array('key'=>$key, 'value'=>$value),
            array('key'=>$key),
            array('%s', '%s'),
            array('%s')
          );
        }
        else {
          $wpdb->query("DELETE from $settingsTable where `key`='$key'");
        }
      }
      else {
        if(!empty($value)) {
          $wpdb->insert($settingsTable, 
            array('key'=>$key, 'value'=>$value),
            array('%s', '%s')
          );
        }
      }
    }
    
  }
  
  public static function getValue($key, $entities=false) {
    global $shoprocketSettings;
    
    if(isset($shoprocketSettings[$key])){
      $value = $shoprocketSettings[$key];
    }
    else {
      global $wpdb;
      $settingsTable = ShoprocketCommon::getTableName('cart_settings');
      $value = $wpdb->get_var("SELECT `value` from $settingsTable where `key`='$key'");
      $GLOBALS['shoprocketSettings'][$key] = $value;
    }
    
    if(!empty($value) && $entities) {
      $value = htmlentities($value, ENT_COMPAT, 'UTF-8');
    }
    
    return empty($value) ? false : $value;
  }
  
  public static function validateDebugValue($value, $expected){
    if($value != $expected){
      // test failed
      $output = "<span class='failedDebug'>" . $value . "</span>";
    }
    else{
      $output = $value;
    }
    return $output;
  }
  
}