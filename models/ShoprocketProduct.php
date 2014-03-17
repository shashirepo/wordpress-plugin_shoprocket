<?php
    require_once(SHOPROCKET_PATH . "/models/Pest.php");
    require_once(SHOPROCKET_PATH . "/models/PestJSON.php");
class ShoprocketProduct extends ShoprocketModelAbstract {
  
  protected $_creditAmount;
  
  public function __construct($id=null) {
    $this->_tableName = ShoprocketCommon::getTableName('products');
    parent::__construct($id);
  }
  
  public function getOptions() {
    $opt1 = $this->_buildOptionList(1);
    $opt2 = $this->_buildOptionList(2);
    return $opt1 . $opt2;
  }

  public function Fetchproducts() {
    global $wpdb;


      $productArray = self::getSRJSON();
      $productcount = count($productArray);
      $batchsize = 5;
      $count = 0;
      $start = $_GET['prev'];

    for($id = $start;$id<$productcount;$id++) {
      $last = $id;
      $count++;
      $percent  = (floatval($id) / floatval($productcount - 1)) * 100;
      $products = ShoprocketCommon::getTableName('products');
       if($count == $batchsize) {
            printf("{\"last\": \"%s\", \"end\": false, \"percent\": \"%.2f\"}", $last, $percent);
            exit();
      }
      try {
      $sql = "INSERT IGNORE into $products (`id`, `name`, `price`, `currency`, `quantity`, `dateadded`, `companyid`, `externalid`, `notes`, `views`,
       `slug`, `weight`, `image strapline`, `deposit`, `video`, `code`, `description`, `billingnotes`, `showit`, `historyid`) VALUES (%d,%s,%d,%s,%d,%s,%s,%s,
       %s,%d,%s,%s,%s,%d,%s,%s,%s,%s,%d,%d)";
        $stmt = $wpdb->prepare($sql, $productArray[$id]['id'], $productArray[$id]['name'], $productArray[$id]['price'], $productArray[$id]['currency'], $productArray[$id]['quantity'], $productArray[$id]['dateadded'], $productArray[$id]['companyid'],
        $productArray[$id]['externalid'], $productArray[$id]['notes'], $productArray[$id]['views'], $productArray[$id]['slug'], $productArray[$id]['weight'], $productArray[$id]['image'], $productArray[$id]['deposit'], $productArray[$id]['video'], $productArray[$id]['code'],
        $productArray[$id]['description'], $productArray[$id]['billingnotes'], $productArray[$id]['showit'], $productArray[$id]['historyid']);
        $wpdb->query($stmt);
      }
      catch(Exception $e){
         printf("{\"last\": \"%s\", \"end\": true, \"percent\": \"%.2f\"}", $last, $percent);
           exit();
      }
        if($last == $productcount-1) {
           printf("{\"last\": \"%s\", \"end\": true, \"percent\": \"%.2f\"}", $last, $percent);
           exit();
      }
    }
  }


  public function getSRJSON($table = 'product') {

    $rest = new Pest(SR_REST);
    try {
     $response = $rest->get('/productlist?='.ShoprocketSetting::getValue('companyid'));
    }
    catch(Pest_Unauthorized $e) {
        header('Bad Request', true, 400);
        die();
      }

      $response = json_decode($response, true);

      $product = array();

      foreach ($response as $key => $value) {
            $product[$key] = $response[$key][$table];
      }

      return array_values($product);


  }
  
  public function loadByDuid($duid) {
    $itemsTable = ShoprocketCommon::getTableName('order_items');
    $sql = "SELECT product_id from $itemsTable where duid = '$duid'";
    $id = $this->_db->get_var($sql);
    $this->load($id);
    return $this->id;
  }
  
  public function loadItemIdByDuid($duid) {
    $itemsTable = ShoprocketCommon::getTableName('order_items');
    $sql = "SELECT id from $itemsTable where duid = '$duid'";
    $id = $this->_db->get_var($sql);
    return $id;
  }
  
  public function loadByItemNumber($itemNumber) {
    $itemNumber = esc_sql($itemNumber);
    $sql = "SELECT id from $this->_tableName where slug = '$itemNumber'";
    $id = $this->_db->get_var($sql);
    $this->load($id);
    return $this->id;
  }

  public function loadFromShortcode($attrs) {
    if(is_array($attrs)) {
      if(isset($attrs['item'])) {
        $this->loadByItemNumber($attrs['item']);
      }
      else {
        $id = $attrs['id'];
        $this->load($id);
      }
    }
    return $this->id;
  }

  public function countDownloadsForDuid($duid, $order_item_id) {
    $downloadsTable = ShoprocketCommon::getTableName('downloads');
    $sql = "SELECT count(*) from $downloadsTable where duid='$duid' AND order_item_id='$order_item_id'";
    return $this->_db->get_var($sql);
  }
  
  public function resetDownloadsForDuid($duid, $order_item_id) {
    $downloadsTable = ShoprocketCommon::getTableName('downloads');
    $sql = "DELETE from $downloadsTable where duid='$duid' AND order_item_id='$order_item_id'";
    $this->_db->query($sql);
  }
  
  /**
   * Return the quantity of Sync in stock for the product with the given id and variation description.
   * 
   * The variation descriptins is a ~ separated string of options. The price info may be in the variation string but
   * will be stripped out before calculating the iKey.
   * 
   * @param int $id
   * @param string $variation
   * @return int Quantity of Sync in stock
   */
  public static function checkSyncLevelForProduct($id, $variation='') {
    // Build varation ikey string component
    if(!empty($variation)) {
      $variation = self::scrubVaritationsForIkey($variation);
    }
    
    $p = new ShoprocketProduct($id);
    $ikey = $p->getSyncKey($variation);
    $count = $p->getSyncCount($ikey);
    //ShoprocketCommon::log("Check Sync Level For Product: $ikey = $count");
    return $count;
  }
  
  public static function decrementSync($id, $variation='', $qty=1) {
    ShoprocketCommon::log("Decrementing Sync: line " . __LINE__);
    // Build varation ikey string component
    if(!empty($variation)) {
      $variation = self::scrubVaritationsForIkey(str_replace(', ', '~', $variation));
    }
    
    $p = new ShoprocketProduct($id);
    $is_gravity_form = false;
    $valid_options = array();
    if($p->isGravityProduct()) {
      $valid_options = ShoprocketGravityReader::getFormValuesArray($p->gravity_form_id);
      $is_gravity_form = true;
    }
    else {
      if(strlen($p->options_1) > 1) {
        $valid_options[] = explode(',', str_replace(' ', '', $p->options_1));
      }
      if(strlen($p->options_2) > 1) {
        $valid_options[] = explode(',', str_replace(' ', '', $p->options_2));
      }
    }
    $newVariation = '';
    $options = explode(',', $variation);
    foreach($options as $option) {
      if($p->validate_option($valid_options, $option, $is_gravity_form)) {
        $newVariation .= $option;
      }
    }
    $ikey = $p->getSyncKey($newVariation);
    $count = $p->getSyncCount($ikey);
    $newCount = $count - $qty;
    if($newCount < 0) {
      $newCount = 0;
    }
    $p->setSyncLevel($ikey, $newCount);
  }
  
  public static function increaseSync($id, $variation='', $qty=1) {
    ShoprocketCommon::log("Increasing Sync: line " . __LINE__);
    // Build varation ikey string component
    if(!empty($variation)) {
      $variation = self::scrubVaritationsForIkey(str_replace(', ', '~', $variation));
    }
    
    $p = new ShoprocketProduct($id);
    $is_gravity_form = false;
    $valid_options = array();
    if($p->isGravityProduct()) {
      $valid_options = ShoprocketGravityReader::getFormValuesArray($p->gravity_form_id);
      $is_gravity_form = true;
    }
    else {
      if(strlen($p->options_1) > 1) {
        $valid_options[] = explode(',', str_replace(' ', '', $p->options_1));
      }
      if(strlen($p->options_2) > 1) {
        $valid_options[] = explode(',', str_replace(' ', '', $p->options_2));
      }
    }
    $newVariation = '';
    $options = explode(',', $variation);
    foreach($options as $option) {
      if($p->validate_option($valid_options, $option, $is_gravity_form)) {
        $newVariation .= $option;
      }
    }
    $ikey = $p->getSyncKey($newVariation);
    $count = $p->getSyncCount($ikey);
    $newCount = $count + $qty;
    $p->setSyncLevel($ikey, $newCount);
  }
  
  protected function validate_option(&$valid_options, $choice, $is_gravity_form=false) {
    $found = false;
    
    foreach($valid_options as $key => $option_group) {
      foreach($option_group as $option) {
        $choice = preg_replace('[\W]', '', $choice);
        $option = preg_replace('[\W]', '', self::scrubVaritationsForIkey($option));
        
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Validating option :: $choice == $option");
        if($choice == $option) {
          $found = true;
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Removing option group: $key");

          // Gravity forms have checkbox options which allow multiple options from the same group
          if(!$is_gravity_form) {
            unset($valid_options[$key]);
          }
          
          return $found;
        }
      }
    }
    
    return $found;
  }
  
  public static function scrubVaritationsForIkey($variation='') {
    if(!empty($variation)) {
      $variations = explode('~', $variation);
      $options = array();
      foreach($variations as $opt) {
        if(strpos($opt, '$')) {
          $options[] = trim(preg_replace('/\s*([+-])[^$]*\$.*$/', '', $opt));
        }
        else {
          $options[] = trim(preg_replace('/\s*([+-])[^$]*\\'. Shoprocket_CURRENCY_SYMBOL_TEXT . '.*$/', '', $opt));
        }
      }
      $variation = strtolower(str_replace('~', ',', str_replace(' ', '', implode(',', $options))));
    }
    ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] variation: $variation");
    return $variation;
  }
  
  public static function confirmSync($id, $variation='', $desiredQty=1) {
    ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Confirming Sync:\n$id | $variation | $desiredQty");
    $ok = true;
    $setting = new ShoprocketSetting();
    $trackSync = ShoprocketSetting::getValue('track_Sync');
    if($trackSync == 1) {
      $p = new ShoprocketProduct($id);
      $variation = self::scrubVaritationsForIkey($variation);
      $ikey = $p->getSyncKey($variation);
      if($p->isSyncTracked($ikey)) {
        $qty = self::checkSyncLevelForProduct($id, $variation);
        if($qty < $desiredQty) {
          $ok = false;
        }
      }
      else {
        ShoprocketCommon::log("Sync not tracked: $ikey");
      }
    }
    return $ok;
  }
  
  /**
   * Return an array of option names having stripped off any price variations
   * 
   * @param int $optNumber The option group number
   * @return array
   */
  public function getOptionNames($optNumber=1) {
    $names = array();
    $optionName = "options_$optNumber";
    $opts = explode(',', $this->$optionName);
    foreach($opts as $opt) {
      $name = $opt;
      if(strpos($opt, '$')) {
        $name = trim(preg_replace('/\s*([+-])[^$]*\$.*$/', '', $opt));
      }
      else {
        $name = trim(preg_replace('/\s*([+-])[^$]*\\'. Shoprocket_CURRENCY_SYMBOL_TEXT . '.*$/', '', $opt));
      }
      
      if(!empty($name)) {
        $names[] = $name;
      }
    }
    return $names;
  }
  
  public function getAllOptionCombinations() {
    $combos = array();
    $opt1 = $this->getOptionNames(1);
    $opt2 = $this->getOptionNames(2);
    if(count($opt1)) {
      foreach($opt1 as $first) {
        if(count($opt2)) {
          foreach($opt2 as $second) {
            $combos[] = "$first, $second";
          }
        }
        else {
          $combos[] = "$first";
        }
      }
    }
    return $combos;
  }
  
  /**
   * Return the primary key used in the ikey table. 
   * This is the product name + variation name without price difference information in all lowercase with no spaces.
   * Only letters and numbers are used.
   * 
   * @param string The variation name without the price difference
   * @return string
   */
  public function getSyncKey($variationName='') {
    $key = strtolower($this->id . $this->name . $variationName);
    $key = str_replace(' ', '', $key);
    $key = preg_replace('/\W/', '', $key);
    return $key;
  }
  
  public function insertSyncData() {
    $keys = array();
    $combos = $this->getAllOptionCombinations();
    if(count($combos)) {
      foreach($combos as $c) {
        $key = $this->getSyncKey($c);
        $keys[] = $key;
      }
    }
    else {
      // There are no product variations
      $key = $this->getSyncKey();
      $keys[] = $key;
    }
    
    foreach($keys as $key) {
      $Sync = ShoprocketCommon::getTableName('Sync');
      
      // Only insert new rows
      $sql = "SELECT ikey from $Sync where ikey = %s";
      $stmt = $this->_db->prepare($sql, $key);
      $foundKey = $this->_db->get_var($stmt);
      if(!$foundKey) {
        $sql = "INSERT into $Sync (ikey, track, product_id, quantity) VALUES (%s,%d,%d,%d)";
        $stmt = $this->_db->prepare($sql, $key, 0, $this->id, 0);
        $this->_db->query($stmt);
      }
      
    }
    
    // Delete obsolete Sync rows
    $keyList = implode("','", $keys);
    $sql = "DELETE from $Sync where product_id=$this->id and ikey not in ('$keyList')";
    $this->_db->query($sql);
  }
  
  public function updateSyncFromPost($request) {
    global $wpdb;
    $Sync = ShoprocketCommon::getTableName('Sync');
    foreach($request as $key => $value) {
      if (substr($key, 0, 4) == "qty_") {
        $ikey = substr($key, 4);
        $wpdb->query("UPDATE $Sync SET quantity='$value' WHERE ikey='$ikey'");
      }
      if (substr($key, 0, 6) == "track_") {
        $ikey = substr($key, 6);
        $wpdb->query("UPDATE $Sync SET track='$value' WHERE ikey='$ikey'");
      }
    }
  }
  
  public function updateSyncFromPost2($ikey) {
    $Sync = ShoprocketCommon::getTableName('Sync');
    $track = ShoprocketCommon::postVal("track_$ikey");
    $qty = ShoprocketCommon::postVal("qty_$ikey");
    $sql = "UPDATE $Sync set track=%d, quantity=%d where ikey=%s";
    $sql = $this->_db->prepare($sql, $track, $qty, $ikey);
    $this->_db->query($sql);
  }
  
  public function setSyncLevel($ikey, $qty) {
    $Sync = ShoprocketCommon::getTableName('Sync');
    $sql = "UPDATE $Sync set quantity=%d where ikey=%s";
    $sql = $this->_db->prepare($sql, $qty, $ikey);
    $this->_db->query($sql);
  }
  
  public function getSyncCount($ikey) {
    $Sync = ShoprocketCommon::getTableName('Sync');
    $sql = "SELECT quantity from $Sync where ikey=%s";
    $sql = $this->_db->prepare($sql, $ikey);
    $count = $this->_db->get_var($sql);
    return $count;
  }
  
  public function getSyncNamesAndCounts() {
    $counts = array();
    $ikeyList = $this->getSyncKeyList();
    foreach($ikeyList as $comboName => $ikey) {
      if($this->isSyncTracked($ikey)) {
        $counts[$comboName] = $this->getSyncCount($ikey);
      }
      else {
        $counts[$comboName] = 'in stock';
      }
    }
    return $counts;
  }
  
  /**
   * Return an array of all Sync keys for this product
   */
  public function getSyncKeyList() {
    $ikeyList = array();
    $combos = $this->getAllOptionCombinations();
    if(count($combos)) {
      foreach($combos as $c) {
        $k = $this->getSyncKey($c);
        $n = $this->name . ': ' . $c;
        $ikeyList[$n] = $k;
      }
    }
    else {
      $ikeyList[$this->name] = $this->getSyncKey();
    }
    
    return $ikeyList;
  }
  
  /**
   * Return true if this product is available in any variation for purchase.
   * 
   * If Sync is not tracked or if any variations of the product are in stock, true is returned.
   * Otherwise, false is returned.
   * 
   * @return boolean
   */
  public function isAvailable() {
    $isAvailable = false;
    $Sync = ShoprocketCommon::getTableName('Sync');
    $sql = "SELECT count(*) from $Sync where product_id=$this->id";
    $found = $this->_db->get_var($sql);
    if($found) {
      $sql = "SELECT sum(quantity) from $Sync where track=1 and product_id=$this->id";
      $qty = $this->_db->get_var($sql);
      if(is_numeric($qty) && $qty > 0) {
        $isAvailable = true;
      }
      else {
        $sql = "SELECT count(*) as c from $Sync where track=0 and product_id=$this->id";
        $notTracked = $this->_db->get_var($sql);
        if($notTracked > 0) {
          $isAvailable = true;
        }
      }
    }
    else {
      // Sync table hasn't been refreshed so ignore Sync tracking for this product
      $isAvailable = true;
    }
    return $isAvailable;
  }
  
  public function isSyncTracked($ikey) {
    $Sync = ShoprocketCommon::getTableName('Sync');
    $sql = "SELECT track from $Sync where ikey=%s";
    $sql = $this->_db->prepare($sql, $ikey);
    $track = $this->_db->get_var($sql);
    //ShoprocketCommon::log("Is Sync tracked query: $sql");
    $isTracked = ($track == 1) ? true : false;
    return $isTracked;
  }
  
  public function pruneSync(array $ikeyList) {
    $Sync = ShoprocketCommon::getTableName('Sync');
    $list = "'" . implode("','", $ikeyList) . "'";
    $sql = "DELETE from $Sync where ikey not in ($list)";
    $this->_db->query($sql);
    //ShoprocketCommon::log("Prune Sync: $sql");
  }
  
  private function _buildOptionList($optNumber) {
    $select = '';
    $optionName = "options_$optNumber";
    if(strlen($this->$optionName) > 1) {
      $select = "\n<select name=\"options_$optNumber\" id=\"options_$optNumber\" class=\"shoprocketOptions options_$optNumber\">";
      $opts = explode(',', $this->$optionName);
      foreach($opts as $opt) {
        $opt = str_replace('+$', '+ $', $opt);
        $opt = trim($opt);
        $optDisplay = str_replace('$', Shoprocket_CURRENCY_SYMBOL, $opt);
        $select .= "\n\t<option value=\"" . htmlentities($opt, ENT_COMPAT, 'UTF-8') . "\">$optDisplay</option>";
      }
      $select .= "\n</select>";
    }
    return $select;
  }

  public function isDigital() {
    $isDigital = false;
    if(strlen($this->downloadPath) > 2 || strlen($this->s3File) > 2) {
      $isDigital = true;
    }
    return $isDigital;
  }
  
  public function isShipped() {
    $isShipped = false;
    if($this->shipped > 0) {
      $isShipped = true;
    }
    return $isShipped;
  }

  /**
   * Return the shipping rate for this product for the given shipping method
   */
  public function getShippingPrice($methodId) {
    $methodId = (isset($methodId) && is_numeric($methodId)) ? $methodId : 0;
    // Look to see if there is a specific setting for this product and the given shipping method
    $ratesTable = ShoprocketCommon::getTableName('shipping_rates');
    $sql = "SELECT shipping_rate from $ratesTable where product_id = " . $this->id . " and shipping_method_id = $methodId";
    $rate = $this->_db->get_var($sql);
    if($rate === NULL) {
      // If no specific rate is set, return the default rate for the given shipping method
      $shippingMethods = ShoprocketCommon::getTableName('shipping_methods');
      $sql = "SELECT default_rate from $shippingMethods where id=$methodId";
      $rate = $this->_db->get_var($sql);
    }
    return $rate;
  }
  
  public function getBundleShippingPrice($methodId) {
    $methodId = (isset($methodId) && is_numeric($methodId)) ? $methodId : 0;
    $ratesTable = ShoprocketCommon::getTableName('shipping_rates');
    $shippingMethods = ShoprocketCommon::getTableName('shipping_methods');
    
    // Look to see if there is a specific bundle rate for this product and the given shipping method
    $sql = "SELECT shipping_bundle_rate from $ratesTable where product_id = " . $this->id . " and shipping_method_id = $methodId";
    $rate = $this->_db->get_var($sql);
    if($rate === NULL) {
      // If no specific rate is set, return the default bundle rate for the given shipping method
      $sql = "SELECT default_bundle_rate from $shippingMethods where id=$methodId";
      $rate = $this->_db->get_var($sql);
      return $rate;
    }
    return $rate;
  }
  
  public function isMembershipProduct() {
    $isMembershipProduct = false;
    if($this->isMembershipProduct == 1) {
      $isMembershipProduct = true;
    }
    return $isMembershipProduct;
  }
  
  public function isSubscription() {
    $isSub = false;
    if(FALSE) {
      if($this->isSpreedlySubscription() || $this->isPayPalSubscription()) {
        $isSub = true;
      }
    }
    return $isSub;
  }
  
  public function isSpreedlySubscription() {
    $isSub = false;
    if(FALSE && (is_numeric($this->spreedlySubscriptionId) && $this->spreedlySubscriptionId > 0)) {
      $isSub = true;
    }
    return $isSub;
  }
  
  public function isPayPalSubscription() {
    $isPayPalSubscription = false;
    if(FALSE && $this->isPaypalSubscription == 1) {
      $isPayPalSubscription = true;
    }
    return $isPayPalSubscription;
  }
  
  public static function getProductIdByGravityFormId($id) {
    global $wpdb;
    $products = ShoprocketCommon::getTableName('products');
    $sql = "SELECT id from $products where gravity_form_id = %d";
    $query = $wpdb->prepare($sql, $id);
    $productId = $wpdb->get_var($query);
    return $productId;
  }
  
  public static function getNonSubscriptionProducts($where=null, $order=null, $limit=null) {
    global $wpdb;
    $subscriptions = array();
    $product = new ShoprocketProduct();
    $products = $product->getModels($where, $order, $limit);
    foreach($products as $p) {
      if(!$p->isSubscription()) {
        $subscriptions[] = $p;
      }
    }
    return $subscriptions;
  }
  
  public static function getSubscriptionProducts($where=null, $order=null, $limit=null) {
    global $wpdb;
    $subscriptions = array();
    $product = new ShoprocketProduct();
    $products = $product->getModels($where, $order, $limit);
    foreach($products as $p) {
      if($p->isSubscription()) {
        $subscriptions[] = $p;
      }
    }
    return $subscriptions;
  }
  
  public static function getSpreedlyProducts($where=null, $order=null, $limit=null) {
    global $wpdb;
    $subscriptions = array();
    $product = new ShoprocketProduct();
    $where = $where == null ? "where showit = 1" : $where . " AND showit = 1";
    $products = $product->getModels($where, $order, $limit);
    foreach($products as $p) {
      if($p->isSpreedlySubscription()) {
        $subscriptions[] = $p;
      }
    }
    return $subscriptions;
  }
  
  public static function getMembershipProducts() {
    global $wpdb;
    $memberships = array();
    $product = new ShoprocketProduct();
    $products = $product->getModels('where is_membership_product=1');
    foreach($products as $p) {
      if($p->isMembershipProduct()) {
        $memberships[] = $p;
      }
    }
    return $memberships;
  }
  
  /**
   * Return the pricing for PayPal or Spreedly subscription plan.
   * The PayPal pricing takes precedence over the Spreedly pricing, 
   * but they should both be the same. If the $showAll paramter is 
   * true then a detailed price summary of all attached subscriptions
   * is returned.
   * 
   * @return string
   */
  public function getRecurringPriceSummary() {
    $priceSummary = "No recurring pricing";
    $paypalPriceSummary = false;
    $spreedlyPriceSummary = false;
    
    if($this->isPayPalSubscription()) {
      if(class_exists('ShoprocketPayPalSubscription')) {
        $subscription = new ShoprocketPayPalSubscription($this->id);
        $priceSummary = $subscription->getPriceDescription();
      }
    }
    elseif($this->isSpreedlySubscription()) {
      if(class_exists('SpreedlySubscription')) {
        if($this->isSubscription()) {
          $subscription = new SpreedlySubscription();
          $subscription->load($this->spreedlySubscriptionId);
          $priceSummary = $subscription->getPriceDescription();
        }
      }
    }
    
    return $priceSummary;
  }
  
  /**
   * Return true if only one subscription is attached or if both attached subscriptions are 
   * for the same amount.
   * 
   * @return boolean
   */
  public function subscriptionMismatch() {
    $ok = false;
    if($this->isSpreedlySubscription() && $this->isPayPalSubscription()) {
      if(class_exists(SpreedlySubscription) && class_exists(ShoprocketPayPalSubscription)) {
        $spreedly = new SpreedlySubscription();
        $spreedly->load($this->spreedlySubscriptionId);
        $paypal = new ShoprocketPayPalSubscription($this->id);
        $paypalPrice = number_format($paypal->price, 2, '.', '');
        $paypalInterval = $paypal->billingInterval; 
        $paypalUnits = $paypal->billingIntervalUnit;
        $pp = $paypalPrice . '|' . $paypalInterval . '|' . $paypalUnits;
        
        $spreedlyPrice = number_format($spreedly->price, 2, '.', '');
        $spreedlyInterval = $spreedly->durationQuantity;
        $spreedlyUnits = $spreedly->durationUnits;
        $sp = $spreedlyPrice . '|' . $spreedlyInterval . '|' . $spreedlyUnits;
        
        
        $this->chargeLaterDurationQuantity . '&nbsp;' . $this->chargeLaterDurationUnits;
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Comparing: $pp <--> $sp" );
        if($pp != $sp) {
          $ok = true;
        }
      }
    }
    return $ok;
  }
  
  public function hasFreeTrial() {
    $hasFreeTrial = false;
    if($this->isSubscription()) {
      $subscription = new SpreedlySubscription();
      $subscription->load($this->spreedlySubscriptionId);
      $hasFreeTrial = $subscription->hasFreeTrial();
    }
    return $hasFreeTrial;
  }
  
  /**
   * Return the number of sales for the given month
   * 
   * @param int $month An integer between 1 and 12 inclusive
   * @param int $year The four digit year
   * @return int
   */
  public function getSalesForMonth($month, $year) {
    $orders = ShoprocketCommon::getTableName('orders');
    $orderItems = ShoprocketCommon::getTableName('order_items');
    $start = date('Y-m-d 00:00:00', strtotime($month . '/1/' . $year));
    $end = date('Y-m-d 00:00:00', strtotime($month . '/1/' . $year . ' +1 month'));
    $sql = "SELECT sum(oi.quantity) as num 
      from 
        $orders as o, 
        $orderItems as oi 
      where
        oi.product_id = %s and
        oi.order_id = o.id and
        o.ordered_on >= '$start' and 
        o.ordered_on < '$end' and
        o.status != 'checkout_pending'
      ";
    $query = $this->_db->prepare($sql, $this->id);
    $num = $this->_db->get_var($query);
    return $num;
  }
  
  public function getSalesTotal() {
    $orders = ShoprocketCommon::getTableName('orders');
    $orderItems = ShoprocketCommon::getTableName('order_items');
    $sql = "SELECT sum(oi.quantity) as num 
      from 
        $orders as o, 
        $orderItems as oi 
      where
        oi.product_id = %s and
        oi.order_id = o.id and
        o.status != 'checkout_pending'
      ";
    $query = $this->_db->prepare($sql, $this->id);
    $num = $this->_db->get_var($query);
    return $num;
  }
  
  public function getIncomeTotal() {
    $orders = ShoprocketCommon::getTableName('orders');
    $orderItems = ShoprocketCommon::getTableName('order_items');
    $sql = "SELECT sum(oi.product_price * oi.quantity) as num 
      from 
        $orders as o, 
        $orderItems as oi 
      where
        oi.product_id = %s and
        oi.order_id = o.id and
        o.status != 'checkout_pending'
      ";
    $query = $this->_db->prepare($sql, $this->id);
    $num = $this->_db->get_var($query);
    return $num;
  }
  
  public function getIncomeForMonth($month, $year) {
    $orders = ShoprocketCommon::getTableName('orders');
    $orderItems = ShoprocketCommon::getTableName('order_items');
    $start = date('Y-m-d 00:00:00', strtotime($month . '/1/' . $year));
    $end = date('Y-m-d 00:00:00', strtotime($month . '/1/' . $year . ' +1 month'));
    
    $sql = "SELECT sum(oi.product_price * oi.quantity) as total
      FROM
        $orders as o,
        $orderItems as oi
      WHERE
        oi.product_id = %s and
        oi.order_id = o.id and
        o.ordered_on >= '$start' and 
        o.ordered_on < '$end' and
        o.status != 'checkout_pending'
      ";
       
    $query = $this->_db->prepare($sql, $this->id);
    $total = $this->_db->get_var($query);
    return $total;
  }
  
  public function validate($override_nonce=false) {
    $errors = array();
    if(!$override_nonce && !wp_verify_nonce($_POST['Shoprocket_product_nonce'], 'Shoprocket_product_nonce')) {
      $errors['nonce'] = __("An unkown error occured, please try again later","shoprocket");
    }
    else {
      // Verify that the slug number is present
      if(empty($this->slug)) {
        $errors['slug'] = __("Product Slug is required","shoprocket");
      }
    
      // Verify that no other products have the same Product Slug
      if(empty($errors)) {
        $sql = "SELECT count(*) from $this->_tableName where slug = %s and id != %d";
        $sql = $this->_db->prepare($sql, $this->slug, $this->id);
        $count = $this->_db->get_var($sql);
        if($count > 0) {
          $errors['slug'] = __("The Product Slug must be unique","shoprocket");
        }
      }
    
      // Verify that if the product has been saved and there is a download path that there is a file located at the path
      if(!empty($this->download_path)) {
        $dir = ShoprocketSetting::getValue('product_folder');
        if(!file_exists($dir . DIRECTORY_SEPARATOR . $this->download_path)) {
          $errors['download_file'] = __("There is no file available at the download path:","shoprocket") . " " . $this->download_path;
        }
      }
    }

    return $errors;
  }
  
  /**
   * Check the gravity form entry for the quantity field.
   * Return the quanity in the field, or 1 if no quantity can be found.
   * 
   * @return int
   * @access public
   */
  public function gravityCheckForEntryQuantity($gfEntry) {
    $qty = 1;
    $qtyId = $this->gravity_form_qty_id;
    if($qtyId > 0) {
      if(isset($gfEntry[$qtyId]) && is_numeric($gfEntry[$qtyId])) {
        $qty = $gfEntry[$qtyId];
        unset($gfEntry[$qtyId]);
      }
    }
    return $qty;
  }
  
  public function gravityGetVariationPrices($gfEntry) {
    $options = array();
    //ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Gravity Forms Entry:  " . print_r($gfEntry, true));
    foreach($gfEntry as $id => $value) {
      if($id != 'source_url') {
        $exp = '/[+-]\s*\\' . Shoprocket_CURRENCY_SYMBOL_TEXT . '\d/';
        if(preg_match($exp, $value)) {
          $options[] = $value;
        }
        else {
          $exp = '/[+-]\s*\$\d/';
          if(preg_match($exp, $value)) {
            $options[] = $value;
          }
        }
      }
    }
    $options = implode('~', $options);
    return $options;
  }
  
  public function isGravityProduct() {
    $isGravity = false;
    if($this->gravity_form_id > 0) {
      $isGravity = true;
    }
    return $isGravity;
  }
  
  public function handleFileUpload() {
    // Check for file upload
    if(strlen($_FILES['product']['tmp_name']['upload']) > 2) {
      $dir = ShoprocketSetting::getValue('product_folder');
      if($dir) {
        $filename = preg_replace('/\s/', '_', $_FILES['product']['name']['upload']);
        $path = $dir . DIRECTORY_SEPARATOR . $filename;
        $src = $_FILES['product']['tmp_name']['upload'];
        if(move_uploaded_file($src, $path)) {
          $_POST['product']['download_path'] = $filename;
        }
        else {
          $this->addError('File Upload', __("Unable to upload file","shoprocket"));
          $msg = "Could not upload file from $src to $path\n". print_r($_FILES, true);
          throw new ShoprocketException($msg, 66101);
        }
      }
    }
  }
  
  /**
   * Return the price to charge at checkout.
   * For subscriptions this may also include the first recurring payment if the recurring start number is 0. 
   * This function will return the exact product price if:
   *  - The product is not a subscription product
   *  - The product is a subscription with a free trial period
   */
  public function getCheckoutPrice() {
    $price = $this->price;
    if($this->isSpreedlySubscription()) {
      if(!$this->hasFreeTrial()) {
        $subscription = new SpreedlySubscription();
        $subscription->load($this->spreedlySubscriptionId);
        $price += $subscription->price;
      }
      
      if(ShoprocketCommon::isLoggedIn()) {
        $proRateAmount = ShoprocketSession::get('ShoprocketProRateAmount');
        if(!$proRateAmount) {
          $proRateData = $this->getProRateInfo();
          $proRateAmount = $proRateData->amount;
        }
        $price = ($proRateAmount > $price) ? 0 : $price - $proRateAmount;
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Calculated ProRate price: " . $price);
      }
      
    }
    elseif($this->isPayPalSubscription()) {
      $price = $this->setupFee;
      $plan = $this->getPayPalSubscription();
      if($plan->startRecurringNumber == 0) {
        if($plan->offerTrial) {
          $price += $plan->trialPrice;
        }
        else {
          $price += $plan->price;
        }
      }
    }
    
    return $price;
  }
  
  /**
   * Return a description of the subscription rate such as $10 / 1 month
   * 
   * @return string
   */
  public function getSubscriptionPriceSummary() {
    $desc = '';
    if($this->isSpreedlySubscription()) {
      $subscription = new SpreedlySubscription();
      $subscription->load($this->spreedlySubscriptionId);
      $desc = $subscription->getPriceDescription();
    }
    return $desc;
  }
  
  public function getPriceDescription($priceDifference=0) {
    if($this->id > 0) {
      if($this->isSpreedlySubscription()) {
        $price = $this->price + $priceDifference;
        $priceDescription = "";
        if($price > 0) {
          $priceDescription = $price;
        }

        if($this->hasFreeTrial()) {
          if(empty($this->priceDescription)){
            $priceDescription = "Free Trial";
          }
          else{
            $priceDescription = $this->priceDescription;	
          }
        }
        else {
          $priceDescription = ShoprocketCommon::currency($priceDescription);
          if($price > 0) {
            $priceDescription .= ' (one time) +<br/> ';
          }
          else {
            $priceDescription = '';
          }
          $priceDescription .= $this->getSubscriptionPriceSummary();
        }
        
        $proRated = $this->getProRateInfo();
        if(is_object($proRated) && $proRated->amount > 0) {
          $proRatedInfo = $proRated->description . ':&nbsp;' . $proRated->money;
          $priceDescription .= '<br/>' . $proRatedInfo;
        }
        
      }
      elseif($this->isPayPalSubscription()) {
        $plan = new ShoprocketPayPalSubscription($this->id);
        $priceDescription = '';
        if($plan->offerTrial) {
          $priceDescription .= $plan->getTrialPriceDescription();
        }
        else {
          $priceDescription .= $plan->getPriceDescription();
        }
      }
      else {
        // ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Product custom price description: $this->priceDes");
        if(!empty($this->priceDescription)) {
          $priceDescription = $this->priceDescription;
        }
        else {
          $priceDescription = $this->price + $priceDifference;
        }
      }
    }
    return $priceDescription;
  }
  
  /**
   * Return information about pro-rated credit or false if there is none.
   * 
   * Returns a standard object:
   *   $data->description = The description of the credit
   *   $data->amount = The monetary amount of the credit
   *   $data->money = The formated monetary amount of the credit
   * 
   * return object or false
   */
  public function getProRateInfo() {
    $data = false;
    $proRateAmount = 0;
    if($this->isSpreedlySubscription()) {
      if(ShoprocketCommon::isLoggedIn() && ShoprocketSession::get('ShoprocketCart')) {
        if($subscriptionId = ShoprocketSession::get('ShoprocketCart')->getSpreedlySubscriptionId()) {
          try {
            $invoiceData = array(
              'subscription-plan-id' => $subscriptionId,
              'subscriber' => array(
                'customer-id' => ShoprocketSession::get('ShoprocketAccountId')
              )
            );
            $invoice = new SpreedlyInvoice();
            $invoice->createFromArray($invoiceData);
            $this->_creditAmount = abs((float)$invoice->invoiceData->{'line-items'}->{'line-item'}[1]->amount);

            $data = new stdClass();
            $data->description = $invoice->invoiceData->{'line-items'}->{'line-item'}[1]->description;
            $data->amount = $this->_creditAmount;
            $data->money = ShoprocketCommon::currency($this->_creditAmount);
            
            if($data->amount > 0) {
              $proRateAmount = $data->amount;
            }

            ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Spreedly Invoice: " . print_r($invoice->invoiceData, true));
          }
          catch(SpreedlyException $e) {
            ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Unable to locate spreedly customer: " . ShoprocketSession::get('ShoprocketAccountId'));
          }
        }
      }
    }
    ShoprocketSession::set('ShoprocketProRateAmount', $proRateAmount, true);
    return $data;
  }
  
  /**
   * Return the ShoprocketPayPalSubscription associated with this products paypal subscription id.
   * If no paypal subscription is attached to this product, return false.
   * 
   * @return ShoprocketPayPalSubscription
   */
  public function getPayPalSubscription() {
    $sub = false;
    if($this->isPayPalSubscription()) {
      if(class_exists('ShoprocketPayPalSubscription')) {
        $sub = new ShoprocketPayPalSubscription($this->id);
      }
    }
    return $sub;
  }
  
  /**
   * Override base class save method by validating the data before and after saving.
   * Return the product id of the saved product.
   * Throw ShoprocketException if the save fails.
   * 
   * @return int The product id
   * @throws ShoprocketException on save failure
   */
  public function save($override_nonce=false) {
    $errors = $this->validate($override_nonce);
    if(true) {
      $productId = parent::save();
      $errors = $this->validate();
    }
    if(count($errors)) {
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] " . get_class($this) . " save errors: " . print_r($errors, true));
      $this->setErrors($errors);
      $errors = print_r($errors, true);
      throw new ShoprocketException('Product save failed: ' . $errors, 66102);
    } 
    return $productId;
  }
  
  public static function loadProductsOutsideOfClass($select='*', $where='id > 0', $orderBy='name') {
    $tableName = ShoprocketCommon::getTableName('products');
    $sql = "SELECT $select
      from 
        $tableName 
      where
        $where
      order by
        $orderBy
    ";
    global $wpdb;
    $products = $wpdb->get_results($sql);
    return $products;
  }
  
}
