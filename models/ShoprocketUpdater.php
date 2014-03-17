<?php
class ShoprocketUpdater {
  
  protected $_version;
  protected $_orderNumber;
  protected $_motherShipUrl = 'http://www.shoprocket.com/shoprocket-version.php';
  
  public function __construct() {
    $setting = new ShoprocketSetting();
    $this->_version = ShoprocketSetting::getValue('version');
    $this->_orderNumber = ShoprocketSetting::getValue('order_number');
  }
  
  /**
   * Check the currently running version against the version of the latest release.
   * 
   * @return mixed The new version number if there is a new version, otherwise false.
   */
  public static function newVersion() {
    $versionInfo = get_transient('_shoprocket_version_request');
    if(!$versionInfo) {
      $callback = "http://shoprocket.com";
      $versionInfo = false;
      $setting = new ShoprocketSetting();
      $orderNumber = ShoprocketSetting::getValue('order_number');
      if($orderNumber) {
        $body = 'key=$orderNumber';
        $options = array('method' => 'POST', 'timeout' => 3, 'body' => $body);
        $options['headers'] = array(
            'Content-Type' => 'application/x-www-form-urlencoded; charset=' . get_option('blog_charset'),
            'Content-Length' => strlen($body),
            'User-Agent' => 'WordPress/' . get_bloginfo("version"),
            'Referer' => get_bloginfo("url")
        );
        $callBackLink = $callback . "/shoprocket-version.php?" . self::getRemoteRequestParams();
        ShoprocketCommon::log("Callback link: $callBackLink");
        $raw = wp_remote_request($callBackLink, $options);
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Version info from remote request: " . print_r($raw, 1));
        if (!is_wp_error($raw) && 200 == $raw['response']['code']) {
          $info = explode("~", $raw['body']);
          $versionInfo = array("isValidKey" => $info[0], "version" => $info[1], "url" => $info[2]);
        }
      }
      return $versionInfo;
    }
  }
  
  public static function getRemoteRequestParams() {
    $params = false;
    $setting = new ShoprocketSetting();
    $orderNumber = ShoprocketSetting::getValue('order_number');
    if(!$orderNumber) {
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Order number not available");
    }
    $version = ShoprocketSetting::getValue('version');
    if(!$version) {
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Version number not available");
    }
    if($orderNumber && $version) {
      global $wpdb;
      $versionName = 'pro';
      $params = sprintf("task=getLatestVersion&pn=Shoprocket&key=%s&v=%s&vnm=%s&wp=%s&php=%s&mysql=%s&ws=%s", 
        urlencode($orderNumber), 
        urlencode($version), 
        urlencode($versionName),
        urlencode(get_bloginfo("version")), 
        urlencode(phpversion()), 
        urlencode($wpdb->db_version()),
        urlencode(get_bloginfo("url"))
      );
    }
    return $params;
  }
  
  public function getCallHomeUrl() {
    return $this->_motherShipUrl;
  }
  
  public function getOrderNumber() {
    return $this->_orderNumber;
  }

}
