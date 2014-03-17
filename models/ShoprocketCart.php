<?php
class ShoprocketCart {
  
  /**
   * An array of ShoprocketCartItem objects
   */
  private $_items;
  
  private $_promotion;
  private $_promoStatus;
  private $_shippingMethodId;
  private $_liveRates;
  
  public function __construct($items=null) {
    if(is_array($items)) {
      $this->_items = $items;
    }
    else {
      $this->_items = array();
    }
    $this->_promoStatus = 0;
  //  $this->_setDefaultShippingMethodId();
    
  }
  
  /**
   * Add an item to the shopping cart when an Add To Cart button is clicked.
   * Combine the product options, check Sync, and add the item to the shopping cart.
   * If the Sync check fails redirect the user back to the referring page.
   * This function assumes that a form post triggered the call.
   */
  public function addToCart($ajax=false) {
    $itemId = ShoprocketCommon::postVal('shoprocketItemId');
    ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Adding item to cart: $itemId");
    
    $options = '';
    $optionResult = '';
    if(isset($_POST['options_1'])) {
      $options = ShoprocketCommon::postVal('options_1');
      $optionResult = ShoprocketCommon::postVal('options_1');
    }
    if(isset($_POST['options_2'])) {
      $options .= '~' . ShoprocketCommon::postVal('options_2');
      $optionResult .= ', ' . ShoprocketCommon::postVal('options_2');
    }
    
    $optionResult = ($optionResult != null) ? '(' . $optionResult . ') ' : '';
    
    if(isset($_POST['item_quantity'])) {
      $itemQuantity = ($_POST['item_quantity'] > 0) ? round($_POST['item_quantity'],0) : 1;
    }
    else{
      $itemQuantity = 1;
    }
    
    if(isset($_POST['item_user_price'])){
      $userPrice = $_POST['item_user_price'] === 0 || $_POST['item_user_price'] == '0' ? '0' . ShoprocketSetting::getValue('currency_dec_point') . '00' : $_POST['item_user_price'];
      $sanitizedPrice = ShoprocketCommon::cleanNumber($userPrice);
      $sanitizedPrice = $sanitizedPrice === 0 || $sanitizedPrice == '0' ? '0.00' : $sanitizedPrice;
      ShoprocketSession::set("userPrice_$itemId",$sanitizedPrice);
    }
    
    $productUrl = null;
    if(isset($_POST['product_url'])){
      $productUrl = $_POST['product_url'];
    }

    if(ShoprocketProduct::confirmSync($itemId, $options)) {
      if($ajax) {
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] yes, ajax here");
        return $this->addItem($itemId, $itemQuantity, $options, null, $productUrl, $ajax, false, $optionResult);
      }
      $this->addItem($itemId, $itemQuantity, $options, null, $productUrl);
    }
    else {
      ShoprocketCommon::log("Item not added due to Sync failure");
      wp_redirect($_SERVER['HTTP_REFERER']);
      exit;
    }
    //$this->_setAutoPromoFromPost();
    $this->_setPromoFromPost();
  }
  
  public function updateCart() {
    if(ShoprocketCommon::postVal('calculateShipping')) {
      ShoprocketSession::set('shoprocket_shipping_zip', ShoprocketCommon::postVal('shipping_zip'));
      ShoprocketSession::set('shoprocket_shipping_country_code', ShoprocketCommon::postVal('shipping_country_code'));
      ShoprocketSession::get('ShoprocketCart')->getLiveRates();
    }
    $this->_setShippingMethodFromPost();
    $this->_updateQuantitiesFromPost();
    $this->_setCustomFieldInfoFromPost();
    //$this->_setAutoPromoFromPost();
    $this->_setPromoFromPost();
    
    ShoprocketSession::touch();
    do_action('shoprocket_after_update_cart', $this);
  }
  
  /**
   * Returns the item that was added (or updated) in the cart
   * 
   * @return ShoprocketCartItem
   */
  public function addItem($id, $qty=1, $optionInfo='', $formEntryId=0, $productUrl='', $ajax=false, $suppressHook=false, $optionResult='') {
    ShoprocketSession::set('ShoprocketTax', 0);
    ShoprocketSession::set('ShoprocketTaxRate', 0);
    $the_final_item = false;
    $alert = array();
    $options_valid = true;
    $product = new ShoprocketProduct($id);
    do_action('shoprocket_before_add_to_cart', $product, $qty);
    try {
      $optionInfo = $this->_processOptionInfo($product, $optionInfo);
    }
    catch(Exception $e) {
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Exception due to invalid product option: " . $e->getMessage());
      $options_valid = false;
      $message = __('We could not add the product', 'shoprocket') . ' <strong>' . $product->name . '</strong> ' . __('to the cart because the product options are invalid.', 'shoprocket');
      $alert['msg'] = $message;
      $alert['msgId'] = -2;
      $alert['quantityInCart'] = 0;
      $alert['requestedQuantity'] = $qty;
      $alert['productName'] = $product->name;
      $alert['productOptions'] = $optionInfo;
      if(!$ajax) {
        ShoprocketSession::set('ShoprocketInvalidOptions', $message);
      }
    }
    
    if($product->id > 0 && $options_valid) {
      
      $newItem = new ShoprocketCartItem($product->id, $qty, $optionInfo->options, $optionInfo->priceDiff, $productUrl);
      $the_final_item = $newItem;
      
      if( ($product->isSubscription() || $product->isMembershipProduct()) && ($this->hasSubscriptionProducts() || $this->hasMembershipProducts() )) {
        // Make sure only one subscription can be added to the cart. Spreedly only allows one subscription per subscriber.
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] about to remove membership item");
        $this->removeMembershipProductItem();
      }
      
      if($product->isGravityProduct()) {
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Product being added is a Gravity Product: $formEntryId");
        if($formEntryId > 0) {
          $newItem->addFormEntryId($formEntryId);
          $newItem->setQuantity($qty);
          $this->_items[] = $newItem;
        }
      }
      else {
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Product being added is NOT a Gravity Product");
        $isNew = true;
        $newItem->setQuantity($qty);
        foreach($this->_items as $item) {
          if($item->isEqual($newItem)) {
            $isNew = false;
            $newQuantity = $item->getQuantity() + $qty;
            $actualQty = ShoprocketProduct::checkSyncLevelForProduct($id, $optionInfo->options);
            $confirmSync = ShoprocketProduct::confirmSync($id, $optionInfo->options, $newQuantity);
            if($actualQty !== NULL && $actualQty < $newQuantity && !$confirmSync){
              if($actualQty > 0) {
                $message = '<p>' . __('We are not able to fulfill your order for', 'shoprocket') . ' <strong>' .  $qty . '</strong> ' . $item->getFullDisplayName() . " " . __('because we only have', 'shoprocket') .  " <strong>$actualQty " . __('in stock.', 'shoprocket') . "</strong></p>";
                $message = "$message <p>" . __('Your cart has been updated based on our available Sync.', 'shoprocket') . "</p>";
                $alert['msg'] = $message;
                $alert['msgId'] = -1;
                $alert['quantityInCart'] = $actualQty;
                $alert['requestedQuantity'] = $qty;
                $alert['productName'] = $item->getFullDisplayName();
                $alert['productOptions'] = $optionInfo;
              }
              else {
                $soldOutLabel = ShoprocketSetting::getValue('label_out_of_stock') ? strtolower(ShoprocketSetting::getValue('label_out_of_stock')) : __('out of stock', 'shoprocket');
                $message = '<p>' . __('We are not able to fulfill your order for', 'shoprocket') . ' <strong>' .  $qty . '</strong> ' . $item->getFullDisplayName() . " " . __('because it is', 'shoprocket') . " <strong>" . $soldOutLabel . ".</strong></p>";
                $message = "$message <p>" . __('Your cart has been updated based on our available Sync.', 'shoprocket') . "</p>";
                $alert['msg'] = $message;
                $alert['msgId'] = -2;
                $alert['quantityInCart'] = $actualQty;
                $alert['requestedQuantity'] = $qty;
                $alert['productName'] = $item->getFullDisplayName();
                $alert['productOptions'] = $optionInfo;
              }
              if(!empty($message)) {
                if(!$ajax) {
                  ShoprocketSession::set('ShoprocketSyncWarning', $message);
                }
              }
              $newQuantity = $actualQty;
            }
            $item->setQuantity($newQuantity);
            if($formEntryId > 0) {
              $item->addFormEntryId($formEntryId);
            }
            if(empty($alert)) {
              $message = __('We have successfully added', 'shoprocket') . " <strong>$newQuantity</strong> " . $product->name . " $optionResult " . __('to the cart', 'shoprocket') . ".";
              $message = "<p>$message</p>";
              $alert['msg'] = $message;
              $alert['msgId'] = 0;
              $alert['quantityInCart'] = $newQuantity;
              $alert['requestedQuantity'] = $qty;
              $alert['productName'] = $product->name;
              $alert['productOptions'] = $optionInfo;
            }
            $the_final_item = $item;
            break;
          }
        }
        if($isNew) {
          $newQuantity = $qty;
          $actualQty = ShoprocketProduct::checkSyncLevelForProduct($id, $optionInfo->options);
          $confirmSync = ShoprocketProduct::confirmSync($id, $optionInfo->options, $newQuantity);
          if($actualQty !== NULL && $actualQty < $newQuantity && !$confirmSync){
            if($actualQty > 0) {
              $message = '<p>' . __('We are not able to fulfill your order for', 'shoprocket') . ' <strong>' .  $qty . '</strong> ' . $product->name . " " . __('because we only have', 'shoprocket') .  " <strong>$actualQty " . __('in stock.', 'shoprocket') . "</strong></p>";
              $message = "$message <p>" . __('Your cart has been updated based on our available Sync.', 'shoprocket') . "</p>";
              $alert['msg'] = $message;
              $alert['msgId'] = -1;
              $alert['quantityInCart'] = $actualQty;
              $alert['requestedQuantity'] = $qty;
              $alert['productName'] = $product->name;
              $alert['productOptions'] = $optionInfo;
            }
            else {
              $soldOutLabel = ShoprocketSetting::getValue('label_out_of_stock') ? strtolower(ShoprocketSetting::getValue('label_out_of_stock')) : __('out of stock', 'shoprocket');
              $message = '<p>' . __('We are not able to fulfill your order for', 'shoprocket') . ' <strong>' .  $qty . '</strong> ' . $product->name . " " . __('because it is', 'shoprocket') . " <strong>" . $soldOutLabel . ".</strong></p>";
              $message = "$message <p>" . __('Your cart has been updated based on our available Sync.', 'shoprocket') . "</p>";
              $alert['msg'] = $message;
              $alert['msgId'] = -2;
              $alert['quantityInCart'] = $actualQty;
              $alert['requestedQuantity'] = $qty;
              $alert['productName'] = $product->name;
              $alert['productOptions'] = $optionInfo;
            }
            if(!empty($alert)) {
              if(!$ajax) {
                ShoprocketSession::set('ShoprocketSyncWarning', $message);
              }
            }
            $newQuantity = $actualQty;
          }
          $newItem->setQuantity($newQuantity);
          if($formEntryId > 0) {
            $newItem->addFormEntryId($formEntryId);
          }
          if(empty($alert)) {
            $message = __('We have successfully added', 'shoprocket') . " <strong>$newQuantity</strong> " . $product->name . " $optionResult " . __('to the cart', 'shoprocket') . ".";
            $message = "<p>$message</p>";
            $alert['msg'] = $message;
            $alert['msgId'] = 0;
            $alert['quantityInCart'] = $newQuantity;
            $alert['requestedQuantity'] = $qty;
            $alert['productName'] = $product->name;
            $alert['productOptions'] = $optionInfo;
          }
          $the_final_item = $newItem;
          $this->_items[] = $newItem;
        }
      }

      $this->_setPromoFromPost();
      ShoprocketSession::touch();
      if(!$suppressHook) {
        do_action('shoprocket_after_add_to_cart', $product, $qty);
      }
      if($ajax) {
        return $alert;
      }
      return $the_final_item;
    }
    
  }
  
  public function removeItem($itemIndex) {
    if(isset($this->_items[$itemIndex])) {
      $product = $this->_items[$itemIndex]->getProduct();
      $this->_items[$itemIndex]->detachAllForms();
      if(count($this->_items) <= 1) {
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] about to remove user price from session: userPrice_" . $product->id . '_' . $this->_items[$itemIndex]->getFirstFormEntryId());
        ShoprocketSession::drop('userPrice_' . $product->id . '_' . $this->_items[$itemIndex]->getFirstFormEntryId(), true);
        $this->_items[$itemIndex]->detachAllForms();
        $this->_items = array();
        ShoprocketSession::drop('ShoprocketTax',true);
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Reset the cart items array");
      }
      else {
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] about to remove user price from session: userPrice_" . $product->id . '_' . $this->_items[$itemIndex]->getFirstFormEntryId());
        ShoprocketSession::drop('userPrice_' . $product->id . '_' . $this->_items[$itemIndex]->getFirstFormEntryId(), true);
        $this->_items[$itemIndex]->detachAllForms();
        unset($this->_items[$itemIndex]);
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Did not reset the cart items array because the cart contains more than just a membership item");
      }
      $this->_setPromoFromPost();
      ShoprocketSession::touch();
      do_action('shoprocket_after_remove_item', $this, $product);
    }
  }
  
  public function removeItemByProductId($productId) {
    foreach($this->_items as $index => $item) {
      if($item->getProductId() == $productId) {
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Removing item at index: $index");
        $this->removeItem($index);
      }
    }
  }
  
  public function removeAllItems() {
    if(is_array($this->_items)) {
      foreach($this->_items as $index => $item) {
        $this->removeItem($index);
      }
    }
  }
  
  public function removeMembershipProductItem() {
    foreach($this->_items as $item) {
      if($item->isMembershipProduct() || $item->isSubscription()) {
        $productId = $item->getProductId();
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Removing membership item with product id: $productId");
        $this->removeItemByProductId($productId);
      }
    }
  }
  
  public function setPriceDifference($amt) {
    if(is_numeric($amt)) {
      $this->_priceDifference = $amt;
    }
  }
  
  public function setItemQuantity($itemIndex, $qty) {
    if(is_numeric($qty)) {
      if(isset($this->_items[$itemIndex])) {
        if($qty == 0) {
          unset($this->_items[$itemIndex]);
        }
        else {
          $this->_items[$itemIndex]->setQuantity($qty);
        }
      }
    }
  }
  
  public function setCustomFieldInfo($itemIndex, $info) {
    if(isset($this->_items[$itemIndex])) {
      $this->_items[$itemIndex]->setCustomFieldInfo($info);
    }
  }
  
  /**
   * Return the number of items in the shopping cart.
   * This count includes multiples of the same product so the returned value is the sum 
   * of all the item quantities for all the items in the cart.
   * 
   * @return int
   */
  public function countItems() {
    $count = 0;
    foreach($this->_items as $item) {
      $count += $item->getQuantity();
    }
    return $count;
  }
  
  public function getItems() {
    return $this->_items;
  }
  
  public function getItem($itemIndex) {
    $item = false;
    if(isset($this->_items[$itemIndex])) {
      $item = $this->_items[$itemIndex];
    }
    return $item;
  }
  
  public function setItems($items) {
    if(is_array($items)) {
      $this->_items = $items;
    }
  }
  
  public function getSubTotal($taxed=false) {
    $total = 0;
    $p = new ShoprocketProduct();
    foreach($this->_items as $item) {
      $p->load($item->getProductId());
      if(!$taxed || ($taxed && $p->taxable == 1)) {
        $total += $item->getProductPrice() * $item->getQuantity();
      }
    }
    return $total;
  }
  
  public function getSubscriptionAmount() {
    $amount = 0;
    if($subId = $this->getSpreedlySubscriptionId()) {
      $subscription = new SpreedlySubscription();
      $subscription->load($subId);
      if(!$subscription->hasFreeTrial()) {
        $amount = (float) $subscription->amount;
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Subscription amount for subscription id: $subId = " . $subscription->amount);
      }
    }
    return $amount;
  }
  
  /**
   * Return the subtotal without including any of the subscription prices
   * 
   * @return float
   */
  public function getNonSubscriptionAmount() {
    $total = 0;
    foreach($this->_items as $item) {
      if(!$item->isSubscription()) {
        $total += $item->getProductPrice() * $item->getQuantity();
      }
      else {
        // item is subscription
        $basePrice = $item->getBaseProductPrice();
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Item is a subscription with base price $basePrice");
        $total += $basePrice;
      }
    }
    return $total;
  }
  
  /**
   * Returns true if the cart contains one or more products for which sales tax is collected.
   * 
   * @return boolean
   */
  public function hasTaxableProducts() {
    $isTaxed = false;
    
    foreach($this->_items as $item) {
      $p = $item->getProduct();
      if($p->taxable == 1) {
        $isTaxed = true;
        break;
      }
    }
    
    return $isTaxed;
  }
  
  public function getTaxableAmount($tax_shipping=false) {
    $total = 0;
    $p = new ShoprocketProduct();
    foreach($this->_items as $item) {
      $p->load($item->getProductId());
      if($p->taxable == 1) {
        $total += $item->getProductPrice() * $item->getQuantity();
      }
    }
    
    $discount = 0;
    $promotion = $this->getPromotion();
    
    if($promotion){
      $discount = $this->getDiscountAmount(true);
    }
    
    if($tax_shipping) {
      $shipping = $this->getShippingCost();
      $total = $total += $shipping;
    }
    else {
      if($promotion && $promotion->apply_to == 'shipping') {
        $discount = 0;
      }
    }
    
    if($discount > $total) {
      $total = 0;
    }
    else {
      $total = $total - $discount;
    }
    return $total;
  }
  
  
  /**
   * Return an array of the shipping methods where the keys are names and the values are ids
   * 
   * @return array of shipping names and ids
   */
  public function getShippingMethods() {
    $method = new ShoprocketShippingMethod();
    $methods = $method->getModels("where code IS NULL or code = ''", 'order by default_rate asc');
    $ship = array();
    if(ShoprocketSetting::getValue('require_shipping_validation')) {
      $select = ShoprocketSetting::getValue('require_shipping_validation_label') ? ShoprocketSetting::getValue('require_shipping_validation_label') : __('Select a Shipping Method', 'shoprocket');
      $ship[$select] = 'select';
    }
    foreach($methods as $m) {
      $ship[$m->name] = $m->id;
    }
    return $ship;
  }

  public function getCartWeight() {
    $weight = 0;
    foreach($this->_items as $item) {
      $weight += $item->getWeight()  * $item->getQuantity();
    }
    return $weight;
  }
  
  public function getShippingCost($methodId=null) {
    $setting = new ShoprocketSetting();
    $shipping = null;
    if(!$this->requireShipping()) { 
      $shipping = 0; 
    }
    // Check to see if Live Rates are enabled and available
    elseif(ShoprocketSession::get('ShoprocketLiveRates') && get_class(ShoprocketSession::get('ShoprocketLiveRates')) == 'ShoprocketLiveRates' && ShoprocketSetting::getValue('use_live_rates')) {
      $liveRate = ShoprocketSession::get('ShoprocketLiveRates')->getSelected();
      if(is_numeric($liveRate->rate)) {
        $r = $liveRate->rate;
        return number_format($r, 2, '.', '');
      }
    }
    // Live Rates are not in use
    else {
      if($methodId > 0) {
        $this->_shippingMethodId = $methodId;
      }
      
      if($this->_shippingMethodId < 1) {
        $this->_setDefaultShippingMethodId();
      }
      else {
        // make sure shipping method exists otherwise reset to the default shipping method
        $method = new ShoprocketShippingMethod();
        if(!$method->load($this->_shippingMethodId) || !empty($method->code)) {
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Resetting the default shipping method id");
          $this->_setDefaultShippingMethodId();
        }
      }
      
      $methodId = $this->_shippingMethodId;

      // Check for shipping rules first
      $shipping = 0;
      $isRuleSet = false;
      $rule = new ShoprocketShippingRule();
      if(isset($methodId) && is_numeric($methodId)) {
        $rules = $rule->getModels("where shipping_method_id = $methodId", 'order by min_amount desc');
        if(count($rules)) {
          $cartTotal = $this->getSubTotal();
          foreach($rules as $rule) {
            if($cartTotal > $rule->minAmount) {
              $shipping = $rule->shippingCost;
              $isRuleSet = true; 
              break;
            }
          }
        }
      }
      
      
      if(!$isRuleSet) {
        $product = new ShoprocketProduct();
        $shipping = 0;
        $highestShipping = 0;
        $bundleShipping = 0;
        $highestId = 0;
        foreach($this->_items as $item) {
          $product->load($item->getProductId());
          
          if($highestId < 1) {
            $highestId = $product->id;
          }
          
          if($product->isShipped()) {
            $shippingPrice = $product->getShippingPrice($methodId);
            $bundleShippingPrice = $product->getBundleShippingPrice($methodId);
            if($shippingPrice > $highestShipping) {
              $highestShipping = $shippingPrice;
              $highestId = $product->id;
            }
            $bundleShipping += $bundleShippingPrice * $item->getQuantity();
          }
        }

        if($highestId > 0) {
          $product->load($highestId);
          $shippingPrice = $product->getShippingPrice($methodId);
          $bundleShippingPrice = $product->getBundleShippingPrice($methodId);
          $shipping = $shippingPrice + ($bundleShipping - $bundleShippingPrice);
        }
      }
    }
    
   
    $shipping = ($shipping < 0) ? 0 : $shipping;
    return number_format($shipping, 2, '.', '');
  }
  
  public function applyPromotion($code,$auto=false) {
    $code = strtoupper($code);
    $promotion = new ShoprocketPromotion();
    
    if($promotion->validateCustomerPromotion($code)) {  
      $this->clearPromotion();
      ShoprocketSession::set('ShoprocketPromotion',$promotion);
      ShoprocketSession::set('ShoprocketPromotionCode',$code);
    } 
    else {      
      $this->clearPromotion();
      if($auto==false){        
        ShoprocketSession::set('ShoprocketPromotionErrors',$promotion->getErrors());
      }    
      
    }
    
  }
  
  public function clearPromotion() {
    ShoprocketSession::drop('ShoprocketPromotionErrors',true);
    ShoprocketSession::drop('ShoprocketPromotion',true);
    ShoprocketSession::drop('ShoprocketPromotionCode',true);
  }
  
  public function getPromotion() {
    $promotion = false;
    if($sessionPromo = ShoprocketSession::get('ShoprocketPromotion')) {
      $promotion = $sessionPromo;
    }
    return $promotion;
  }
  
  // Get the products in the cart and returns the ID's of each product
  public function getProductsAndIds() {
    $product = new ShoprocketProduct();
    $products = array();
    foreach($this->_items as $item) { //needs to be changed because _items is a private cart function
      $product->load($item->getProductId());
      $products[] = $product->id;
    }
    return $products;
  }
  
  public function getDiscountAmount($taxed=false) {
    $discount = 0;
    if(ShoprocketSession::get('ShoprocketPromotion')) {
      $discount = number_format(ShoprocketSession::get('ShoprocketPromotion')->getDiscountAmount(ShoprocketSession::get('ShoprocketCart'), $taxed), 2, '.', '');
      // ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Getting discount Total: $total -- Discounted Total: $discountedTotal -- Discount: $discount");
    }
    return $discount;
  }
  
  /**
   * Return the entire cart total including shipping costs and discounts.
   * An optional paramater can be provided to specify whether or not subscription items 
   * are included in the total.
   * 
   * @param boolean $includeSubscriptions
   * @return float 
   */
  public function getGrandTotal($includeSubscriptions=true) {
    if($includeSubscriptions) {
       $subTotal = $this->getSubTotal();
    }
    else{
       $subTotal = $this->getNonSubscriptionAmount();
    }
    
    // discounts apply to the total by default
    $total = $subTotal + $this->getShippingCost();
    
    if($this->getDiscountAmount() > 0){
      // discount is in use
      $promotion = ShoprocketSession::get('ShoprocketPromotion');
      if($promotion && $promotion->apply_to == "shipping"){
        $shippingWithDiscount = $this->getShippingCost() - $this->getDiscountAmount();
        $shippingWithDiscount = ($shippingWithDiscount < 0) ? 0 : $shippingWithDiscount;
        $total = $this->getNonSubscriptionAmount() + $shippingWithDiscount;
      }
      if($promotion && $promotion->apply_to == "total"){
        // nothing special to do here right now
        $total = $total - $promotion->getDiscountAmount();
      }
      if($promotion && ($promotion->apply_to == "products" || $promotion->apply_to == "subtotal")){
        $total = ($subTotal - $promotion->getDiscountAmount()) + $this->getShippingCost();
      }
    }
    
    $total = (abs($total) < 0.0001) ? 0 : $total;
    return $total; 
  }
  
  public function getFinalDiscountTotal(){
     $finalDiscountTotal = 0;
     $subTotal = $this->getSubTotal();
 
     $promotion = ShoprocketSession::get('ShoprocketPromotion');
     if($promotion && $promotion->apply_to == "shipping"){
       $finalDiscountTotal = $promotion->stayPositive($this->getShippingCost());
     }
     if($promotion && $promotion->apply_to == "total"){
       $finalDiscountTotal = $promotion->stayPositive($subTotal + $this->getShippingCost());
     }
     if($promotion && $promotion->apply_to == "products"){
       $finalDiscountTotal = $promotion->stayPositive($subTotal);
     }
     
     
     return $finalDiscountTotal;
   }
  
  public function storeOrder($orderInfo) {
    ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] orderinfo: " . print_r($orderInfo, true));
    $order = new ShoprocketOrder();
    $pre_string = isset($orderInfo['status']) && $orderInfo['status'] == 'checkout_pending' ? 'CP-' : 'MT-';
    $orderInfo['trans_id'] = (empty($orderInfo['trans_id'])) ? $pre_string . ShoprocketCommon::getRandString() : $orderInfo['trans_id'];
    $orderInfo['ip'] = $_SERVER['REMOTE_ADDR'];
    if(ShoprocketSession::get('ShoprocketPromotion')){
       $orderInfo['discount_amount'] = ShoprocketSession::get('ShoprocketPromotion')->getDiscountAmount(ShoprocketSession::get('ShoprocketCart'));
    }
    else{
      $orderInfo['discount_amount'] = 0;
    }
    $order->setInfo($orderInfo);
    $order->setItems($this->getItems());
    $orderId = $order->save();
    //update the number of redemptions for the promotion code.
    if(ShoprocketSession::get('ShoprocketPromotion')) {
      ShoprocketSession::get('ShoprocketPromotion')->updateRedemptions();
    }
    $orderInfo['id'] = $orderId;
    do_action('shoprocket_after_order_saved', $orderInfo);
    
    return $orderId;
  }
  
  /**
   * Return true if all products are digital
   */
  public function isAllDigital() {
    $allDigital = true;
    foreach($this->getItems() as $item) {
      if(!$item->isDigital()) {
        $allDigital = false;
        break;
      }
    }
    return $allDigital;
  }
  
  public function isAllMembershipProducts(){
    $i = 0;
    foreach($this->getItems() as $item) {
      if($item->isMembershipProduct() || $item->isSubscription()) {
        $i++;
      }
    }
    return ($i == count($this->getItems())) ? true : false;
  }
  
  public function isAllNonShippedMembershipProducts(){
    $i = 0;
    foreach($this->getItems() as $item) {
      if(($item->isMembershipProduct() || $item->isSubscription()) && !$item->isShipped()) {
        $i++;
      }
    }
    return ($i == count($this->getItems())) ? true : false;
  }
  
  public function hasMembershipProducts() {
    foreach($this->getItems() as $item) {
      if($item->isMembershipProduct()) {
        return true;
      }
    }
    return false;
  }
  
  public function hasSubscriptionProducts() {
    foreach($this->getItems() as $item) {
      if($item->isSubscription()) {
        return true;
      }
    }
    return false;
  }
  
  /**
   * Return true if the cart only contains PayPal subscriptions
   */
  public function hasPayPalSubscriptions() {
    foreach($this->getItems() as $item) {
      if($item->isPayPalSubscription()) {
        return true;
      }
    }
    return false;
  }
  
  public function hasSpreedlySubscriptions() {
    foreach($this->getItems() as $item) {
      if($item->isSpreedlySubscription()) {
        return true;
      }
    }
    return false;
  }
  
  /**
   * Return the spreedly subscription id for the subscription product in the cart 
   * or false if there are no spreedly subscriptions in the cart. With Spreedly 
   * subscriptions, there may be only one subscription product in the cart.
   */
  public function getSpreedlySubscriptionId() {
    foreach($this->getItems() as $item) {
      if($item->isSpreedlySubscription()) {
        return $item->getSpreedlySubscriptionId();
      }
    }
    return false;
  }
  
  /**
   * Return the spreedly subscription product id for the subscription product in the cart 
   * or false if there are no spreedly subscriptions in the cart. With Spreedly 
   * subscriptions, there may be only one subscription product in the cart.
   */
  public function getSpreedlyProductId() {
    foreach($this->getItems() as $item) {
      if($item->isSpreedlySubscription()) {
        return $item->getSpreedlyProductId();
      }
    }
    return false;
  }
  
  /**
   * Return the CartItem that holds the membership product.
   * If there is no membership product in the cart, return false.
   * 
   * @return ShoprocketCartItem
   */
  public function getMembershipProductItem() {
    foreach($this->getItems() as $item) {
      if($item->isMembershipProduct()) {
        return $item;
      }
    }
    return false;
  }
  
  /**
   * Return the membership product in the cart. 
   * Only one membership or subscription type item may be in the cart at any given time. 
   * Note that this function returns the actual ShoprocketProduct not the ShoprocketCartItem.
   * If there is no membership product in the cart, return false.
   * 
   * @return ShoprocketProduct
   */
  public function getMembershipProduct() {
    $product = false;
    if($item = $this->getMembershipProductItem()) {
      $product = new ShoprocketProduct($item->getProductId());
    }
    return $product;
  }
  
  public function getPayPalSubscriptionId() {
    foreach($this->getItems() as $item) {
      if($item->isPayPalSubscription()) {
        return $item->getPayPalSubscriptionId();
      }
    }
    return false;
  }
  
  /**
   * Return the ShoprocketCartItem with the PayPal subscription
   * 
   * @return ShoprocketCartItem
   */
  public function getPayPalSubscriptionItem() {
    foreach($this->getItems() as $item) {
      if($item->isPayPalSubscription()) {
        return $item;
      }
    }
    return false;
  }
  
  /**
   * Return the index in the cart of the PayPal subscription item.
   * This number is used to know the location of the item in the cart
   * when creating the payment profile with PayPal.
   * 
   * @return int
   */
  public function getPayPalSubscriptionIndex() {
    $index = 0;
    foreach($this->getItems() as $item) {
      if($item->isPayPalSubscription()) {
        return $index;
      }
      $index++;
    }
    return false;
  }
  
  /**
   * Return false if none of the items in the cart are shipped
   */
  public function requireShipping() {
    $ship = false;
    foreach($this->getItems() as $item) {
      if($item->isShipped()) {
        $ship = true;
        break;
      }
    }
    return $ship;
  }
  
  public function requirePayment() {
    $requirePayment = true;
    if($this->getGrandTotal() < 0.01) {
      // Look for free trial subscriptions that require billing
      if($subId = $this->getSpreedlySubscriptionId()) {
        $sub = new SpreedlySubscription($subId);
        if('free_trial' == strtolower((string)$sub->planType)) {
          $requirePayment = false;
        }
      }
    }
    return $requirePayment;
  }

  public function setShippingMethod($id) {
    $method = new ShoprocketShippingMethod();
    if($method->load($id)) {
      $this->_shippingMethodId = $id;
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Set shipping method id to: $id");
    }
    elseif(ShoprocketSetting::getValue('require_shipping_validation')) {
      $this->_shippingMethodId = 'select';
    }
  }

  public function getShippingMethodId() {
    if($this->_shippingMethodId < 1) {
      $this->_setDefaultShippingMethodId();
    }
    return $this->_shippingMethodId;
  }

  public function getShippingMethodName() {
    // Look for live rates
    if(ShoprocketSession::get('ShoprocketLiveRates')) {
      $rate = ShoprocketSession::get('ShoprocketLiveRates')->getSelected();
      return $rate->service;
    }
    // Not using live rates
    else {
      if($this->isAllDigital() && !$this->isAllMembershipProducts()) {
        return 'Download';
      }
      elseif(!$this->requireShipping() || $this->isAllNonShippedMembershipProducts()) {
        return 'None';
      }
      else {
        if($this->_shippingMethodId < 1) {
          $this->_setDefaultShippingMethodId();
        }
        $method = new ShoprocketShippingMethod($this->_shippingMethodId);
        return $method->name;
      }
    }
    
  }
  
  public function detachFormEntry($entryId) {
    foreach($this->_items as $index => $item) {
      $entries = $item->getFormEntryIds();
      if(in_array($entryId, $entries)) {
        $item->detachFormEntry($entryId);
        $qty = $item->getQuantity();
        if($qty == 0) {
          $this->removeItem($index);
        }
      }
    }
  }
  
  public function checkCartSync() {
    $alert = '';
    foreach($this->_items as $itemIndex => $item) {
      if(!ShoprocketProduct::confirmSync($item->getProductId(), $item->getOptionInfo(), $item->getQuantity())) {
        ShoprocketCommon::log("Unable to confirm Sync when checking cart.");
        $qtyAvailable = ShoprocketProduct::checkSyncLevelForProduct($item->getProductId(), $item->getOptionInfo());
        if($qtyAvailable > 0) {
          $alert .= '<p>We are not able to fulfill your order for <strong>' .  $item->getQuantity() . '</strong> ' . $item->getFullDisplayName() . "  because 
            we only have <strong>$qtyAvailable in stock</strong>.</p>";
        }
        else {
          $soldOutLabel = ShoprocketSetting::getValue('label_out_of_stock') ? strtolower(ShoprocketSetting::getValue('label_out_of_stock')) : __('out of stock', 'shoprocket');
          $alert .= '<p>We are not able to fulfill your order for <strong>' .  $item->getQuantity() . '</strong> ' . $item->getFullDisplayName() . "  because 
            it is <strong>" . $soldOutLabel . "</strong>.</p>";
        }
        
        if($qtyAvailable > 0) {
          $item->setQuantity($qtyAvailable);
        }
        else {
          $this->removeItem($itemIndex);
        }
        
      }
    }
    
    if(!empty($alert)) {
      $alert = "<div class='alert-message alert-error ShoprocketUnavailable'><h1>Sync Restriction</h1> $alert <p>Your cart has been updated based on our available Sync.</p>";
      $alert .= '<input type="button" name="close" value="Ok" class="ShoprocketButtonSecondary modalClose" /></div>';
    }
    
    return $alert;
  }
  
  public function getLiveRates() {
    ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Call to getLiveRates");
    if(!false) { return false; }
    
    $weight = ShoprocketSession::get('ShoprocketCart')->getCartWeight();
    $zip = ShoprocketSession::get('shoprocket_shipping_zip') ? ShoprocketSession::get('shoprocket_shipping_zip') : false;
    $countryCode = ShoprocketSession::get('shoprocket_shipping_country_code') ? ShoprocketSession::get('shoprocket_shipping_country_code') : ShoprocketCommon::getHomeCountryCode();
    
    // Make sure _liveRates is a ShoprocketLiveRates object
    if(get_class($this->_liveRates) != 'ShoprocketLiveRates') {
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] WARNING: \$this->_liveRates is not a ShoprocketLiveRates object so we're making it one now.");
      $this->_liveRates = new ShoprocketLiveRates();
    }
    
    // Return the live rates from the session if the zip, country code, and cart weight are the same
    if(ShoprocketSession::get('ShoprocketLiveRates') && get_class($this->_liveRates) == 'ShoprocketLiveRates') {
      $cartWeight = $this->getCartWeight();
      $this->_liveRates = ShoprocketSession::get('ShoprocketLiveRates');
      
      $liveWeight = $this->_liveRates->weight;
      $liveZip = $this->_liveRates->toZip;
      $liveCountry = $this->_liveRates->getToCountryCode();
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] 
        $liveWeight == $weight
        $liveZip == $zip
        $liveCountry == $countryCode
      ");
      
      if($this->_liveRates->weight == $weight && $this->_liveRates->toZip == $zip && $this->_liveRates->getToCountryCode() == $countryCode) {
        ShoprocketCommon::log("Using Live Rates from the session: " . $this->_liveRates->getSelected()->getService());
        return ShoprocketSession::get('ShoprocketLiveRates'); 
      }
    }

    if($this->getCartWeight() > 0 && ShoprocketSession::get('shoprocket_shipping_zip') && ShoprocketSession::get('shoprocket_shipping_country_code')) {
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Clearing current live shipping rates and recalculating new rates.");
      $this->_liveRates->clearRates();
      $this->_liveRates->weight = $weight;
      $this->_liveRates->toZip = $zip;
      $method = new ShoprocketShippingMethod();
      
      // Get USPS shipping rates
      if(ShoprocketSetting::getValue('usps_username')) {
        $this->_liveRates->setToCountryCode($countryCode);
        $rates = ($countryCode == 'US') ? $this->getUspsRates() : $this->getUspsIntlRates($countryCode);
        $uspsServices = $method->getServicesForCarrier('usps');
        foreach($rates as $name => $price) {
          $price = number_format($price, 2, '.', '');
          if(in_array($name, $uspsServices)) {
            $this->_liveRates->addRate('USPS', 'USPS ' . $name, $price);
          }
        }
      }

      // Get UPS Live Shipping Rates
      if(ShoprocketSetting::getValue('ups_apikey')) {
        $rates = $this->getUpsRates();
        foreach($rates as $name => $price) {
          $this->_liveRates->addRate('UPS', $name, $price);
        }
      }
      
      // Get FedEx Live Shipping Rates
      if(ShoprocketSetting::getValue('fedex_developer_key')) {
        $this->_liveRates->setToCountryCode($countryCode);
        $rates = $this->getFedexRates();
        foreach($rates as $name => $price) {
          $this->_liveRates->addRate('FedEx', $name, $price);
        }
      }
      
      // Get Australia Post Live Shipping Rates
      if(ShoprocketSetting::getValue('aupost_developer_key')) {
        $this->_liveRates->setToCountryCode($countryCode);
        $rates = $this->getAuPostRates();
        foreach($rates as $name => $price) {
          $this->_liveRates->addRate('Australia Post', $name, $price);
        }
      }
      
      // Get Canada Post Live Shipping Rates
      if(ShoprocketSetting::getValue('capost_merchant_id') || ShoprocketSetting::getValue('capost_username')) {
        $this->_liveRates->setToCountryCode($countryCode);
        $rates = $this->getCaPostRates();
        foreach($rates as $name => $price) {
          $this->_liveRates->addRate('Canada Post', $name, $price);
        }
      }
      
      if(ShoprocketSetting::getValue('shipping_local_pickup')) {
        $this->_liveRates->addRate('Local Pickup', ShoprocketSetting::getValue('shipping_local_pickup_label'), number_format(ShoprocketSetting::getValue('shipping_local_pickup_amount'), 2));
      }
      
    }
    else {
      $this->_liveRates->clearRates();
      $this->_liveRates->weight = 0;
      $this->_liveRates->toZip = $zip;
      $this->_liveRates->setToCountryCode($countryCode);
      $this->_liveRates->addRate('SYSTEM', 'Free Shipping', '0.00');
    }
    
    ShoprocketSession::set('ShoprocketLiveRates', $this->_liveRates);
    ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Dump live rates: " . print_r($this->_liveRates, true));
    return $this->_liveRates;
  }
  
  /**
   * Return a hash where the keys are service names and the values are the service rates.
   * @return array 
   */
  public function getUspsRates() {
    $usps = new ShoprocketUsps();
    $weight = $this->getCartWeight();
    $fromZip = ShoprocketSetting::getValue('usps_ship_from_zip');
    $toZip = ShoprocketSession::get('shoprocket_shipping_zip');
    ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Getting rates for USPS: $fromZip > $toZip > $weight");
    $rates = $usps->getRates($fromZip, $toZip, $weight);
    return $rates;
  }
  
  public function getUspsIntlRates($countryCode) {
    $usps = new ShoprocketUsps();
    $weight = $this->getCartWeight();
    $value = $this->getSubTotal();
    $zipOrigin = ShoprocketSetting::getValue('usps_ship_from_zip');
    ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Getting rates for USPS Intl: $zipOrigin > $countryCode > $value > $weight");
    $rates = $usps->getIntlRates($zipOrigin, $countryCode, $value, $weight);
    return $rates;
  }
  
  /**
   * Return a hash where the keys are service names and the values are the service rates.
   * @return array 
   */
  public function getUpsRates() {
    $ups = new ShoprocketUps();
    $weight = ShoprocketSession::get('ShoprocketCart')->getCartWeight();
    $zip = ShoprocketSession::get('shoprocket_shipping_zip');
    $countryCode = ShoprocketSession::get('shoprocket_shipping_country_code');
    $rates = $ups->getAllRates($zip, $countryCode, $weight);
    return $rates;
  }
  
  public function getFedexRates() {
    $fedex = new ShoprocketFedEx();
    $weight = ShoprocketSession::get('ShoprocketCart')->getCartWeight();
    $zip = ShoprocketSession::get('shoprocket_shipping_zip');
    $countryCode = ShoprocketSession::get('shoprocket_shipping_country_code');
    $rates = $fedex->getAllRates($zip, $countryCode, $weight);
    return $rates;
  }
  
  public function getAuPostRates() {
    $aupost = new ShoprocketAuPost();
    $weight = ShoprocketSession::get('ShoprocketCart')->getCartWeight();
    $zip = ShoprocketSession::get('shoprocket_shipping_zip');
    $countryCode = ShoprocketSession::get('shoprocket_shipping_country_code');
    $rates = $aupost->getAllRates($zip, $countryCode, $weight);
    return $rates;
  }
  
  public function getCaPostRates() {
    $capost = new ShoprocketCaPost();
    $weight = ShoprocketSession::get('ShoprocketCart')->getCartWeight();
    $zip = ShoprocketSession::get('shoprocket_shipping_zip');
    $countryCode = ShoprocketSession::get('shoprocket_shipping_country_code');
    $rates = $capost->getAllRates($zip, $countryCode, $weight);
    return $rates;
  }
  
  protected function _setDefaultShippingMethodId() {
    // Set default shipping method to the cheapest method
    $method = new ShoprocketShippingMethod();
    $methods = $method->getModels("where code IS NULL or code = ''", 'order by default_rate asc');
    if(is_array($methods) && count($methods) && is_object($methods[0]) && get_class($methods[0]) == 'ShoprocketShippingMethod') {
      $this->_shippingMethodId = $methods[0]->id;
      if(ShoprocketSetting::getValue('require_shipping_validation')) {
        $this->_shippingMethodId = 'select';
      }
    }
  }
  
  protected function _setShippingMethodFromPost() {
    // Not using live rates
    if(isset($_POST['shipping_method_id'])) {
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not using live shipping rates");
      $shippingMethodId = $_POST['shipping_method_id'];
      $this->setShippingMethod($shippingMethodId);
      if(isset($_POST['shipping_country_code'])) {
        ShoprocketSession::set('ShoprocketShippingCountryCode', $_POST['shipping_country_code']);
      }
      else {
        ShoprocketSession::drop('ShoprocketShippingCountryCode');
      }
    }
    // Using live rates
    elseif(isset($_POST['live_rates'])) {
      if(ShoprocketSession::get('ShoprocketLiveRates')) {
        ShoprocketSession::get('ShoprocketLiveRates')->setSelected($_POST['live_rates']);
        // ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] This LIVE RATE is now set: " . ShoprocketSession::get('ShoprocketLiveRates')->getSelected()->getService());
        // ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Using live shipping rates to set shipping method from post: " . $_POST['live_rates']);
      }
    }
  }
  
  protected function _updateQuantitiesFromPost() {
    $qtys = ShoprocketCommon::postVal('quantity');
    if(is_array($qtys)) {
      foreach($qtys as $itemIndex => $qty) {
        $item = $this->getItem($itemIndex);
        if(!is_null($item) && is_object($item) && get_class($item) == 'ShoprocketCartItem') {
          
          if($qty == 0){
            ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Customer specified quantity of 0 - remove item.");
            $this->removeItem($itemIndex);
          }
          
          if(ShoprocketProduct::confirmSync($item->getProductId(), $item->getOptionInfo(), $qty)) {
            $this->setItemQuantity($itemIndex, $qty);
          }
          else {
            $qtyAvailable = ShoprocketProduct::checkSyncLevelForProduct($item->getProductId(), $item->getOptionInfo());
            $this->setItemQuantity($itemIndex, $qtyAvailable);
            if(!ShoprocketSession::get('ShoprocketSyncWarning')) { ShoprocketSession::set('ShoprocketSyncWarning', ''); }
            $SyncWarning = ShoprocketSession::get('ShoprocketSyncWarning');
            $SyncWarning .= '<div class="alert-message alert-error  ShoprocketUnavailable">' . __("The quantity for","shoprocket") . ' ' . $item->getFullDisplayName() . " " . __("could not be changed to","shoprocket") . " $qty " . __("because we only have", "shoprocket") . " $qtyAvailable " . __("in stock","shoprocket") . ".</div>";
            ShoprocketSession::set('ShoprocketSyncWarning', $SyncWarning);
            ShoprocketCommon::log("Quantity available ($qtyAvailable) cannot meet desired quantity ($qty) for product id: " . $item->getProductId());
          }
        }
      }
    }
  }
  
  protected function _setCustomFieldInfoFromPost() {
    // Set custom values for individual products in the cart
    $custom = ShoprocketCommon::postVal('customFieldInfo');
    if(is_array($custom)) {
      foreach($custom as $itemIndex => $info) {
        $this->setCustomFieldInfo($itemIndex, $info);
      }
    }
  }
  
  public function applyAutoPromotions() {
    $this->_setPromoFromPost();
  }
  
  protected function _setPromoFromPost() {
    if(isset($_POST['couponCode']) && $_POST['couponCode'] != '') {
      $couponCode = ShoprocketCommon::postVal('couponCode');
      $this->applyPromotion($couponCode);
    }
    else{
      if(ShoprocketSession::get('ShoprocketPromotion')){
        $currentPromotionCode = ShoprocketSession::get('ShoprocketPromotionCode');
        $isAutoPromo = (ShoprocketSession::get('ShoprocketPromotion')->auto_apply == 1) ? true : false;
        $this->applyPromotion($currentPromotionCode, $isAutoPromo);
        if(!ShoprocketSession::get('ShoprocketPromotion')){
          $this->_setAutoPromoFromPost();
        }        
      }
      else {
        $this->clearPromotion();
        $this->_setAutoPromoFromPost();
      }
    }
    
  }
  
  //applies coupon codes that are set to Auto Apply
  protected function _setAutoPromoFromPost() {    
    $promotion = new ShoprocketPromotion();
    $promotions = $promotion->getAutoApplyPromotions();
    foreach($promotions as $promo){
      if($promo->validateCustomerPromotion($promo->getCodeAt())) {  
        $this->applyPromotion($promo->getCodeAt(), true);
      }
    }
  }
   
  /**
   * Return a stdClass object with the price difference and a CSV list of options.
   *   $optionResult->priceDiff
   *   $optionResult->options
   * @return object
   */
  protected function _processOptionInfo($product, $optionInfo) {
    $valid_options = array();
    if($product->isGravityProduct()) {
      $valid_options = ShoprocketGravityReader::getFormValuesArray($product->gravity_form_id);
    }
    else {
      if(strlen($product->options_1) > 1) {
        $valid_options[] = explode(',', str_replace(' ', '', $product->options_1));
      }
      if(strlen($product->options_2) > 1) {
        $valid_options[] = explode(',', str_replace(' ', '', $product->options_2));
      }
    }
    $optionInfo = trim($optionInfo);
    $priceDiff = 0;
    $options = explode('~', $optionInfo);
    $optionList = array();
    foreach($options as $opt) {
      if(strlen($opt) >= 1) {
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Working with option: $opt\n" . print_r($valid_options, true));

        // make sure product option is vallid
        $is_gravity_form = false;
        $check_option = str_replace(' ', '', $opt);
        if($is_gravity_form = $product->isGravityProduct()) {
          $check_option = explode('|', $check_option);
          $check_option = $check_option[0];
        }
        
        if($this->_validate_option($valid_options, $check_option, $is_gravity_form)) {
          if(strpos($opt, '$')) {
            if(preg_match('/\+\s*\$/', $opt)) {
              $opt = preg_replace('/\+\s*\$/', '+' . Shoprocket_CURRENCY_SYMBOL_TEXT, $opt);
              list($opt, $pd) = explode('+' . Shoprocket_CURRENCY_SYMBOL_TEXT, $opt);
              $optionList[] = trim($opt);
              $priceDiff += $pd;
            }
            elseif(preg_match('/-\s*\$/', $opt)) {
              $opt = preg_replace('/-\s*\$/', '-' . Shoprocket_CURRENCY_SYMBOL_TEXT, $opt);
              list($opt, $pd) = explode('-' . Shoprocket_CURRENCY_SYMBOL_TEXT, $opt);
              $optionList[] = trim($opt);
              $pd = trim($pd);
              $priceDiff -= $pd;
            }
            else {
              $optionList[] = trim($opt);
            }
          }
          else {
            if(preg_match('/\+\s*\\' . Shoprocket_CURRENCY_SYMBOL_TEXT . '/', $opt)) {
              $opt = preg_replace('/\+\s*\\' . Shoprocket_CURRENCY_SYMBOL_TEXT . '/', '+' . Shoprocket_CURRENCY_SYMBOL_TEXT, $opt);
              list($opt, $pd) = explode('+' . Shoprocket_CURRENCY_SYMBOL_TEXT, $opt);
              $optionList[] = trim($opt);
              $priceDiff += $pd;
            }
            elseif(preg_match('/-\s*\\' . Shoprocket_CURRENCY_SYMBOL_TEXT . '/', $opt)) {
              $opt = preg_replace('/-\s*\\' . Shoprocket_CURRENCY_SYMBOL_TEXT . '/', '-' . Shoprocket_CURRENCY_SYMBOL_TEXT, $opt);
              list($opt, $pd) = explode('-' . Shoprocket_CURRENCY_SYMBOL_TEXT, $opt);
              $optionList[] = trim($opt);
              $pd = trim($pd);
              $priceDiff -= $pd;
            }
            else {
              $optionList[] = trim($opt);
            }
          }
        }
        else {
          throw new Exception("Invalid product option: $opt");
        }
      }
      else {
        if(count($valid_options) > 0 && !$product->isGravityProduct()) {
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] no option");
          throw new Exception("Product option is required");
        }
      }
    }
    $optionResult = new stdClass();
    $optionResult->priceDiff = $priceDiff;
    $optionResult->options = implode(', ', $optionList);
    return $optionResult;
  }
  
  private function _validate_option(&$valid_options, $choice, $is_gravity_form=false) {
    $found = false;
    
    foreach($valid_options as $key => $option_group) {
      foreach($option_group as $option) {
        $choice = preg_replace('[\W]', '', $choice);
        $option = preg_replace('[\W]', '', $option);
        
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
  
}
