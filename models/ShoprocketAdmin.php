<?php

class ShoprocketAdmin {
  
  public static function productsPage() {
    $data = array();
    $subscriptions = array('0' => 'None');
    
    if(class_exists('SpreedlySubscription')) {
      $spreedlySubscriptions = SpreedlySubscription::getSubscriptions();
      foreach($spreedlySubscriptions as $s) {
        $subs[(int)$s->id] = (string)$s->name;
      }
      if(count($subs)) {
        asort($subs);
        foreach($subs as $id => $name) {
          $subscriptions[$id] = $name;
        }
      }
    }
    else {
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not loading Spreedly data because Spreedly class has not been loaded");
    }
    
    $data['subscriptions'] = $subscriptions;
    $view = ShoprocketCommon::getView('admin/products.php', $data);
    echo $view;
  }
  
  public static function settingsPage() {
    $tabs = array(
      'main' => array('tab' => 'Main', 'title' => ''),
      'cart_checkout' => array('tab' => 'Cart & Checkout', 'title' => ''),
      'notifications' => array('tab' => 'Notifications', 'title' => ''),
      'integrations' => array('tab' => 'Integrations', 'title' => ''),
      'debug' => array('tab' => 'Debug', 'title' => '')
    );
    $setting = new ShoprocketSetting($tabs);
    $data = array(
      'setting' => $setting
    );
    $view = ShoprocketCommon::getView('admin/settings.php', $data);
    echo $view;
  }
  
  public static function notificationsPage() {
    $view = ShoprocketCommon::getView('admin/notifications.php');
    echo $view;
  }
  

  public static function SyncPage() {
    $view = ShoprocketCommon::getView('admin/Sync.php');
    echo $view; 
  }

  public static function promotionsPage() {
    $view = ShoprocketCommon::getView('admin/promotions.php');
    echo $view;
  }

  public static function shippingPage() {
    $view = ShoprocketCommon::getView('admin/shipping.php');
    echo $view;
  }

  public static function reportsPage() {
    $view = ShoprocketCommon::getView('admin/reports.php');
    echo $view;
  }
  
  public function ShoprocketHelp() {
    $setting = new ShoprocketSetting();
    define('HELP_URL', "http://www.shoprocket.com/shoprocket-help/?order_number=".ShoprocketSetting::getValue('order_number'));
    $view = ShoprocketCommon::getView('admin/help.php');
    echo $view;
  }
  

  public static function accountsPage() { 
    $view = ShoprocketCommon::getView('admin/accounts.php', "Here goes the accounts stuffs !");
    echo $view;
  }
}