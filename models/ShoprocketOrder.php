<?php
class ShoprocketOrder extends ShoprocketModelAbstract {
  
  protected $_orderInfo = array();
  protected $_items = array();
  
  public function __construct($id=null) {
    $this->_tableName = ShoprocketCommon::getTableName('orders');
    parent::__construct($id);
  }
  
  /**
   * Attempt to load an order from the database with the given ouid.
   * 
   * Return true on success and false on failure
   * 
   * @return boolean
   */
  public function loadByOuid($ouid) {
    $is_loaded = false;
    $sql = $this->_db->prepare("SELECT id from $this->_tableName where ouid=%s", $ouid);
    $id = $this->_db->get_var($sql);
    if(is_numeric($id)) {
      $is_loaded = $this->load($id);
    }
    return $is_loaded;
  }
  
  public function loadByDuid($duid) {
    $tableName = ShoprocketCommon::getTableName('order_items');
    $sql = $this->_db->prepare("SELECT order_id from $tableName where duid=%s", $duid);
    $id = $this->_db->get_var($sql);
    $this->load($id);
  }
  
  public function setInfo(array $info) {
    $this->_orderInfo = $info;
  }
  
  public function setItems(array $items) {
    $this->_items = $items;
  }
  
  /**
   * Save the order and return the order id
   * 
   * If the order is new, then save all the order items and manage the Sync.
   * If the order already exists, only the order data is updated. The order items and Sync
   * remain unchanged.
   * 
   * @return int The order id (primary key form database)
   */
  public function save() {
    // If the order is already in the database, only save the order data, not the ordered items or anything else
    if($this->id > 0) {
      $this->_db->update($this->_tableName, $this->_data, array('id' => $this->id));
    }
    else {
      // This is a new order so save the order items and deduct from Sync if necessary
      $this->_orderInfo['ouid'] = md5($this->_orderInfo['trans_id'] . $this->_orderInfo['bill_address']);
      
      $this->_db->insert($this->_tableName, $this->_orderInfo);
      $this->id = $this->_db->insert_id;
      $key = $this->_orderInfo['trans_id'] . '-' . $this->id . '-';
      
      foreach($this->_items as $item) {

        // Deduct from Sync
        ShoprocketProduct::decrementSync($item->getProductId(), $item->getOptionInfo(), $item->getQuantity());

        $data = array(
          'order_id' => $this->id,
          'product_id' => $item->getProductId(),
          'product_price' => $item->getProductPrice(),
          'item_number' => $item->getItemNumber(),
          'description' => $item->getFullDisplayName(),
          'quantity' => $item->getQuantity(),
          'duid' => md5($key . $item->getProductId())
        );

        $formEntryIds = '';
        $fIds = $item->getFormEntryIds();
        if(is_array($fIds) && count($fIds)) {
          foreach($fIds as $entryId) {
            if(class_exists('RGFormsModel')) {
              if($lead = RGFormsModel::get_lead($entryId)) {
                $lead['status'] = 'active';
                RGFormsModel::update_lead($lead);
              }
            }
          }
          $formEntryIds = implode(',', $fIds);
        }
        $data['form_entry_ids'] = $formEntryIds;

        if($item->getCustomFieldInfo()) {
          $data['description'] .= "\n" . $item->getCustomFieldDesc() . ":\n" . $item->getCustomFieldInfo();
        }

        $orderItems = ShoprocketCommon::getTableName('order_items');
        $this->_db->insert($orderItems, $data);
        $orderItemId = $this->_db->insert_id;
        ShoprocketCommon::log("Saved order item ($orderItemId): " . $data['description'] . "\nSQL: " . $this->_db->last_query);
      }
      
    }
    
    return $this->id;
  }
  
  /**
   * Insert an assoc array into the orders table and return the primary key for the new row.
   * 
   * The given array has keys that match the database table column names.
   * 
   * @return int
   */
  public function rawSave(array $data) {
    $this->_db->insert($this->_tableName, $data);
    return $this->_db->insert_id;
  }
  
  public function getOrderRows($where=null, $orderBy=null, $limit=null) {
    if(isset($where)) {
      $where = ' ' . $where;
    }
    if(isset($orderBy)) {
      $orderBy = ' ' . $orderBy;
    }
    if(isset($limit)) {
      $limit = ' limit ' . $limit;
    }

    $sql = "SELECT * from $this->_tableName $where $orderBy $limit";
    
    $orders = $this->_db->get_results($sql);
    return $orders;
  }
  
  public function getItems() {
    $orderItems = ShoprocketCommon::getTableName('order_items');
    $sql = "SELECT * from $orderItems where order_id = $this->id order by product_price desc";
    $items = $this->_db->get_results($sql);
    return $items;
  }
  
  /**
   * Return the membership product from the order or false if none exists.
   * 
   * @return ShoprocketProduct
   */
  public function getMembershipProduct() {
    $items = $this->getItems();
    $product = new ShoprocketProduct();
    foreach($items as $item) {
      $product->load($item->product_id);
      if($product->isMembershipProduct()) {
        return $product;
      }
    }
    return null;
  }
  
  public function updateStatus($status) {
    if($this->id > 0) {
      $data['status'] = $status;
      $this->_db->update($this->_tableName, $data, array('id' => $this->id), array('%s'));
      return $status;
    }
    return false;
  }
  
  public function updateNotes($notes) {
    if($this->id > 0) {
      $data['notes'] = $notes;
      $this->_db->update($this->_tableName, $data, array('id' => $this->id), array('%s'));
      return $notes;
    }
    return false;
  }
  
  public function updateTracking($trackingNumber) {
    if($this->id > 0) {
      $data['tracking_number'] = $trackingNumber;
      $this->_db->update($this->_tableName, $data, array('id' => $this->id), array('%s'));
      return $trackingNumber;
    }
    return false;
  }
  
  public function updateViewed() {
    global $post;
    $receiptPage = get_page_by_path('store/receipt');
    if( isset( $post->ID ) && $post->ID == $receiptPage->ID) {
      $order = new ShoprocketOrder();
      if(isset($_GET['ouid'])) {
        $order->loadByOuid($_GET['ouid']);
        $data['viewed'] = '1';
        if($order->viewed == 0) {
          $this->_db->update($this->_tableName, $data, array('id' => $order->id), array('%s'));
        }
      }
    }
    return false;
  }
  
  public function addTrackingCode() {
    if(ShoprocketSetting::getValue('enable_google_analytics') && (is_home() || is_front_page())) {
      echo '<script type="text/javascript">
        /* <![CDATA[ */
        var _gaq = _gaq || [];
        _gaq.push([\'_setAccount\', \'' . ShoprocketSetting::getValue('google_analytics_wpid') . '\']);
        _gaq.push([\'_trackPageview\']);

        (function() {
          var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
          ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
          var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
        })();
      /* ]]> */
      </script>';
    }
    return false;
  }
  
  public function deleteMe($resetSync=false, $resetRedemptions=false) {
    if($this->id > 0) {
      // Delete attached Gravity Forms if they exist
      $items = $this->getItems();
      foreach($items as $item) {
        if(!empty($item->form_entry_ids)) {
          $entryIds = explode(',', $item->form_entry_ids);
          if(is_array($entryIds)) {
            foreach($entryIds as $entryId) {
              RGFormsModel::delete_lead($entryId);
            }
          } 
        }
      }
      
      if($resetSync && ShoprocketSetting::getValue('track_Sync')) {
        $this->resetSyncForItems();
      }
      if($resetRedemptions && $this->coupon != 'none') {
        $this->resetRedemptionsForCoupon();
      }
      
      // Delete order items
      $orderItems = ShoprocketCommon::getTableName('order_items');
      $sql = "DELETE from $orderItems where order_id = $this->id";
      $this->_db->query($sql);
      
      // Delete the order
      $sql = "DELETE from $this->_tableName where id = $this->id";
      $this->_db->query($sql);
    }
  }
  
  public function resetRedemptionsForCoupon() {
    ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] about to reset the number of redemptions");
    $coupon = explode(' (', $this->coupon);
    $promotion = new ShoprocketPromotion();
    $promotion->loadByCode($coupon[0]);
    $promotion->resetRedemptions();
  }
  
  public function resetSyncForItems() {
    $orderItems = $this->getItems();
    foreach($orderItems as $item) {
      // Decrement Sync
      $variation = '';
      $info = $item->description;
      if(strpos($info, '(') > 0) {
        $info = strrchr($info, '(');
        $start = strpos($info, '(');
        $end = strpos($info, ')');
        $length = $end - $start;
        $variation = substr($info, $start+1, $length-1);
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Variation: $variation Info: $info");
      }
      $qty = $item->quantity;
      ShoprocketProduct::increaseSync($item->product_id, $variation, $qty);
    }
  }
  
  public function hasShippingInfo() {
    return strlen(trim($this->ship_first_name) . trim($this->ship_last_name) . trim($this->ship_address)) > 0;
  }
  
  /**
   * Check to see if the order includes a product that requires an account and if there is an account in the system
   * 
   * Return values:
   *   1 = The account exists
   *   0 = There is no account associated with the order and there is no need for one
   *  -1 = There is no account associated with the order but there should be
   * 
   * @return int
   */
  public function hasAccount() {
    if($this->id == 0 || empty($this->id)) {
      throw new ShoprocketException(66400, 'Cannot get account status on an order with no order id');
    }
    
    ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account ID on order: " . $this->account_id);
    if($this->account_id > 0) {
      return 1; // The order has an account associated with it
    }
    else {
      // No account exists for this order, but does it need one?
      $product = new ShoprocketProduct();
      $items = $this->getItems();
      foreach($items as $item) {
        $product->load($item->product_id);
        if($product->isMembershipProduct() || $product->isSubscription()) {
          return -1; // No account exists but there should be one
        }
      }
    }
    return 0; // No account exists and none is needed.
  }
  
  public function getOrderIdByAccountId($accountId){
    $is_loaded = false;
    $sql = $this->_db->prepare("SELECT id from $this->_tableName where account_id=%s", $accountId);
    $id = $this->_db->get_var($sql);
    if(is_numeric($id)) {
      $is_loaded = $id;
    }
    return $is_loaded;
  }
  
  public function dailyPrunePendingPayPalOrders() {
    ShoprocketSetting::setValue('daily_prune_pending_orders_last_checked', ShoprocketCommon::localTs());
    $o = new ShoprocketOrder();
    $dayStart = date('Y-m-d 00:00:00', strtotime('48 hours ago', ShoprocketCommon::localTs()));
    $dayEnd = date('Y-m-d 00:00:00', strtotime('24 hours ago', ShoprocketCommon::localTs()));
    
    $orders = $o->getOrderRows("WHERE status in ('paypal_pending','checkout_pending') AND ordered_on >= '$dayStart' AND ordered_on < '$dayEnd'");
    foreach($orders as $order) {
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] yes, i am to delete an order or more: " . $order->id);
      $o->load($order->id);
      $o->deleteMe(true, true);
    }
  }
  
}
