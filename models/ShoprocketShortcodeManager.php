<?php

class ShoprocketShortcodeManager {
  
  public $manualIsOn;
  
  /**
   * Short code for displaying shopping cart including the number of items in the cart and links to view cart and checkout
   */
  public function shoppingCart($attrs) {
    $cartPage = get_page_by_path('store/cart');
    $checkoutPage = get_page_by_path('store/checkout');
    $cart = ShoprocketSession::get('ShoprocketCart');
    $checkoutUrl = ShoprocketSetting::getValue('auth_force_ssl') ? str_replace('http://', 'https://', get_permalink($checkoutPage->ID)) : get_permalink($checkoutPage->ID);
    if(is_object($cart) && $cart->countItems()) {
      ?>
      <div id="ShoprocketscCartContents">
        <a id="ShoprocketscCartLink" href='<?php echo get_permalink($cartPage->ID) ?>'>
        <span id="ShoprocketscCartCount"><?php echo $cart->countItems(); ?></span>
        <span id="ShoprocketscCartCountText"><?php echo $cart->countItems() > 1 ? ' items' : ' item' ?></span> 
        <span id="ShoprocketscCartCountDash">&ndash;</span>
        <span id="ShoprocketscCartPrice"><?php echo ShoprocketCommon::currency($cart->getSubTotal()); ?>
        </span></a>
        <a id="ShoprocketscViewCart" href='<?php echo get_permalink($cartPage->ID) ?>'>View Cart</a>
        <span id="ShoprocketscLinkSeparator"> | </span>
        <a id="ShoprocketscCheckout" href='<?php echo $checkoutUrl; ?>'>Check out</a>
      </div>
      <?php
    }
    else {
      $emptyMessage = isset($attrs['empty_msg']) ? $attrs['empty_msg'] : 'Your cart is empty';
      echo "<p id=\"ShoprocketscEmptyMessage\">$emptyMessage</p>";
    }
  }

  public static function showCartButton($attrs, $content) {
    $product = new ShoprocketProduct();
    $product->loadFromShortcode($attrs);
    return ShoprocketButtonManager::getCartButton($product, $attrs, $content);
  }
  
  public static function showCartAnchor($attrs, $content) {
    $product = new ShoprocketProduct();
    $product->loadFromShortcode($attrs);
    $options = isset($attrs['options']) ? $attrs['options'] : '';
    $urlOptions = isset($attrs['options']) ? '&amp;options=' . urlencode($options) : '';
    
    $content = do_shortcode($content);
    
    $iCount = true;
    $iKey = $product->getSyncKey($options);
    if($product->isSyncTracked($iKey)) {
      $iCount = $product->getSyncCount($iKey);
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] iCount: $iCount === iKey: $iKey");
    }
    else {
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not tracking Sync for: $iKey");
    }
    
    if($iCount) {
      $id = $product->id;
      $class = isset($attrs['class']) ? $attrs['class'] : '';
      $cartPage = get_page_by_path('store/cart');
      $cartLink = get_permalink($cartPage->ID);
      $joinChar = (strpos($cartLink, '?') === FALSE) ? '?' : '&';
      

      $data = array(
        'url' => $cartLink . $joinChar . "task=add-to-cart-anchor&amp;shoprocketItemId=${id}${urlOptions}",
        'text' => $content,
        'class' => $class
      );

      $view = ShoprocketCommon::getView('views/cart-button-anchor.php', $data, true, true);
    }
    else {
      $view = $content;
    }
    
    return $view;
  }

  public function showCart($attrs, $content) {
    if(isset($_REQUEST['shoprocket-task']) && $_REQUEST['shoprocket-task'] == 'remove-attached-form') {
      $entryId = $_REQUEST['entry'];
      if(is_numeric($entryId)) {
        ShoprocketSession::get('ShoprocketCart')->detachFormEntry($entryId);
      }
    }
    $view = ShoprocketCommon::getView('views/cart.php', $attrs, true, true);
    return $view;
  }

  public function showReceipt($attrs) {
    $account = null;
    
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ouid'])) {
      if(false && isset($_POST['account'])) {
        $acctData = ShoprocketCommon::postVal('account');
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] New Account Data: " . print_r($acctData, true));
        $account = new ShoprocketAccount();
        $account->firstName = $acctData['first_name'];
        $account->lastName = $acctData['last_name'];
        $account->email = $acctData['email'];
        $account->username = $acctData['username'];
        $account->password = md5($acctData['password']);
        $errors = $account->validate();
        $jqErrors = $account->getJqErrors();
        
        if($acctData['password'] != $acctData['password2']) {
          $errors[] = __("Passwords do not match","shoprocket");
          $jqErrors[] = 'account-password';
          $jqErrors[] = 'account-password2';
        }
        
        if(count($errors) == 0) { 
          // Attach account to order
          $order = new ShoprocketOrder();
          $ouid = ShoprocketCommon::postVal('ouid');
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Trying to load order with OUID: $ouid");
          if($order->loadByOuid($ouid)) {
            
            // Make sure the order can be loaded, then save the account
            $account->save();
            
            // Attach membership to account and account to the order
            if($mp = $order->getMembershipProduct()) {
              $account->attachMembershipProduct($mp, $account->firstName, $account->lastName);
              $order->account_id = $account->id;
              $order->save();
              $account->clear();
            }
          }
          
        }
        else {
          $attrs['errors'] = $errors;
          $attrs['jqErrors'] = $jqErrors;
        }
      }
    }
    
    $attrs['account'] = $account;
    $view = ShoprocketCommon::getView('views/receipt.php', $attrs, true, true);
    return $view;
  }

  public function paypalCheckout($attrs) {
    if(ShoprocketSession::get('ShoprocketCart')->countItems() > 0) {
      if(!ShoprocketSession::get('ShoprocketCart')->hasSubscriptionProducts() && !ShoprocketSession::get('ShoprocketCart')->hasMembershipProducts()) {
        if(ShoprocketSession::get('ShoprocketCart')->getGrandTotal()) {
          try {
            $view = ShoprocketCommon::getView('views/paypal-checkout.php', $attrs);
          }
          catch(ShoprocketException $e) {
            $exception = ShoprocketException::exceptionMessages($e->getCode(), $e->getMessage());
            $view = ShoprocketCommon::getView('views/error-messages.php', $exception);
          }
          return $view;
        }
        else {
          return $this->manualCheckout();
        }
      }
    }
  }

  public function manualCheckout($attrs=null) {
    
    if($this->manualIsOn=="active"){
      return;
    }
    
    if(ShoprocketSession::get('ShoprocketCart')->countItems() > 0) {
      $gatewayName = ShoprocketCommon::postVal('shoprocket-gateway-name');
      if($_SERVER['REQUEST_METHOD'] == 'POST' && $gatewayName != 'ShoprocketManualGateway') {
        return;
      }
      
      if(!ShoprocketSession::get('ShoprocketCart')->hasSubscriptionProducts()) {
        require_once(SHOPROCKET_PATH . "/gateways/ShoprocketManualGateway.php");
        $manual = new ShoprocketManualGateway();
        $view = $this->_buildCheckoutView($manual);
        $this->manualIsOn = "active";
      }
      else {
        $view = "<p>Unable to sell subscriptions using the manual checkout gateway.</p>";
      }
      
      return $view;
    }
  }

  public function authCheckout($attrs) {
    if(ShoprocketSession::get('ShoprocketCart')->countItems() > 0) {
      $gatewayName = ShoprocketCommon::postVal('shoprocket-gateway-name');
      if($_SERVER['REQUEST_METHOD'] == 'POST' && $gatewayName != 'ShoprocketAuthorizeNet') {
        return ($gatewayName == "ShoprocketManualGateway") ? $this->manualCheckout() : "";
      }

      if(!ShoprocketSession::get('ShoprocketCart')->hasPayPalSubscriptions()) {
        require_once(SHOPROCKET_PATH . "/pro/gateways/ShoprocketAuthorizeNet.php");

        if(ShoprocketSession::get('ShoprocketCart')->getGrandTotal() > 0 || ShoprocketSession::get('ShoprocketCart')->hasSpreedlySubscriptions()) {
          try {
            $authnet = new ShoprocketAuthorizeNet();
            $view = $this->_buildCheckoutView($authnet);
          }
          catch(ShoprocketException $e) {
            $exception = ShoprocketException::exceptionMessages($e->getCode(), $e->getMessage());
            $view = ShoprocketCommon::getView('views/error-messages.php', $exception);
          }
          return $view;
        }
        elseif(ShoprocketSession::get('ShoprocketCart')->countItems() > 0) {
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Displaying manual checkout instead of Authorize.net Checkout because the cart value is $0.00");
          return $this->manualCheckout();
        }

      }
      else {
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering Authorize.net checkout form because the cart contains a PayPal subscription");
      }
    }
  }
  
  public function payLeapCheckout($attrs) {
    if(ShoprocketSession::get('ShoprocketCart')->countItems() > 0) {
        $gatewayName = ShoprocketCommon::postVal('shoprocket-gateway-name');  
        if($_SERVER['REQUEST_METHOD'] == 'POST' && $gatewayName != 'ShoprocketPayLeap') {
          return ($gatewayName == "ShoprocketManualGateway") ? $this->manualCheckout() : "";
        }

        if(!ShoprocketSession::get('ShoprocketCart')->hasPayPalSubscriptions()) {
          require_once(SHOPROCKET_PATH . "/pro/gateways/ShoprocketPayLeap.php");

          if(ShoprocketSession::get('ShoprocketCart')->getGrandTotal() > 0 || ShoprocketSession::get('ShoprocketCart')->hasSpreedlySubscriptions()) {
            try {
              $payleap = new ShoprocketPayLeap();
              $view = $this->_buildCheckoutView($payleap);
            }
            catch(ShoprocketException $e) {
              $exception = ShoprocketException::exceptionMessages($e->getCode(), $e->getMessage());
              $view = ShoprocketCommon::getView('views/error-messages.php', $exception);
            }
            return $view;
          }
          elseif(ShoprocketSession::get('ShoprocketCart')->countItems() > 0) {
            ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Displaying manual checkout instead of PayLeap Checkout because the cart value is $0.00");
            return $this->manualCheckout();
          }

        }
        else {
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering PayLeap checkout form because the cart contains a PayPal subscription");
        }
      }
  }
  
  public function ewayCheckout($attrs) {
     if(ShoprocketSession::get('ShoprocketCart')->countItems() > 0) {
      $gatewayName = ShoprocketCommon::postVal('shoprocket-gateway-name');
      if($_SERVER['REQUEST_METHOD'] == 'POST' && $gatewayName != 'ShoprocketEway') {
        return ($gatewayName == "ShoprocketManualGateway") ? $this->manualCheckout() : "";
      }

      if(!ShoprocketSession::get('ShoprocketCart')->hasPayPalSubscriptions()) {
        require_once(SHOPROCKET_PATH . "/pro/gateways/ShoprocketEway.php");

        if(ShoprocketSession::get('ShoprocketCart')->getGrandTotal() > 0 || ShoprocketSession::get('ShoprocketCart')->hasSpreedlySubscriptions()) {
          try {
            $eway = new ShoprocketEway();
            $view = $this->_buildCheckoutView($eway);
          }
          catch(ShoprocketException $e) {
            $exception = ShoprocketException::exceptionMessages($e->getCode(), $e->getMessage());
            $view = ShoprocketCommon::getView('views/error-messages.php', $exception);
          }
          return $view;
        }
        elseif(ShoprocketSession::get('ShoprocketCart')->countItems() > 0) {
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Displaying manual checkout instead of Eway Checkout because the cart value is $0.00");
          return $this->manualCheckout();
        }

      }
      else {
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering Eway checkout form because the cart contains a PayPal subscription");
      }
    }
  }  
  
  public function mwarriorCheckout($attrs) {
      if(ShoprocketSession::get('ShoprocketCart')->countItems() > 0) {
        $gatewayName = ShoprocketCommon::postVal('shoprocket-gateway-name');
        if($_SERVER['REQUEST_METHOD'] == 'POST' && $gatewayName != 'ShoprocketMerchantWarrior') {
          return ($gatewayName == "ShoprocketManualGateway") ? $this->manualCheckout() : "";
        }

        if(!ShoprocketSession::get('ShoprocketCart')->hasPayPalSubscriptions()) {
          require_once(SHOPROCKET_PATH . "/pro/gateways/ShoprocketMWarrior.php");

          if(ShoprocketSession::get('ShoprocketCart')->getGrandTotal() > 0 || ShoprocketSession::get('ShoprocketCart')->hasSpreedlySubscriptions()) {
            try {
              $mwarrior = new ShoprocketMerchantWarrior();
              $view = $this->_buildCheckoutView($mwarrior);
            }
            catch(ShoprocketException $e) {
              $exception = ShoprocketException::exceptionMessages($e->getCode(), $e->getMessage());
              $view = ShoprocketCommon::getView('views/error-messages.php', $exception);
            }
            return $view;
          }
          elseif(ShoprocketSession::get('ShoprocketCart')->countItems() > 0) {
            ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Displaying manual checkout instead of Merchant Warrior Checkout because the cart value is $0.00");
            return $this->manualCheckout();
          }

        }
        else {
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering Merchant Warrior checkout form because the cart contains a PayPal subscription");
        }
      }
  }
  
  public function stripeCheckout($attrs) {
    if(ShoprocketSession::get('ShoprocketCart')->countItems() > 0) {
      $gatewayName = ShoprocketCommon::postVal('shoprocket-gateway-name');
      if($_SERVER['REQUEST_METHOD'] == 'POST' && $gatewayName != 'ShoprocketStripe') {
        return ($gatewayName == "ShoprocketManualGateway") ? $this->manualCheckout() : "";
      }

      if(!ShoprocketSession::get('ShoprocketCart')->hasPayPalSubscriptions()) {
        require_once(SHOPROCKET_PATH . "/pro/gateways/ShoprocketStripe.php");

        if(ShoprocketSession::get('ShoprocketCart')->getGrandTotal() > 0 || ShoprocketSession::get('ShoprocketCart')->hasSpreedlySubscriptions()) {
          try {
            $stripe = new ShoprocketStripe();
            $view = $this->_buildCheckoutView($stripe);
          }
          catch(ShoprocketException $e) {
            $exception = ShoprocketException::exceptionMessages($e->getCode(), $e->getMessage());
            $view = ShoprocketCommon::getView('views/error-messages.php', $exception);
          }
          return $view;
        }
        elseif(ShoprocketSession::get('ShoprocketCart')->countItems() > 0) {
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Displaying manual checkout instead of Stripe Checkout because the cart value is $0.00");
          return $this->manualCheckout();
        }

      }
      else {
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering Stripe checkout form because the cart contains a PayPal subscription");
      }
    }
  }
  
  public function twoCheckout($attrs) {
    if(ShoprocketSession::get('ShoprocketCart')->countItems() > 0) {
      if(!ShoprocketSession::get('ShoprocketCart')->hasPayPalSubscriptions() && !ShoprocketSession::get('ShoprocketCart')->hasSpreedlySubscriptions()) {
        if(ShoprocketSession::get('ShoprocketCart')->getGrandTotal() > 0) {
          try {
            $tco = new Shoprocket2Checkout();
            $view = $this->_buildCheckoutView($tco);
          }
          catch(ShoprocketException $e) {
            $exception = ShoprocketException::exceptionMessages($e->getCode(), $e->getMessage());
            $view = ShoprocketCommon::getView('views/error-messages.php', $exception);
          }
          return $view;
        }
        elseif(ShoprocketSession::get('ShoprocketCart')->countItems() > 0) {
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Displaying manual checkout instead of 2Checkout because the cart value is $0.00");
          return $this->manualCheckout();
        }
      }
      else {
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering 2Checkout form because the cart contains a PayPal subscription or Spreedly subscription");
      }
    }
  }

  public function payPalProCheckout($attrs) {
    if(ShoprocketSession::get('ShoprocketCart')->countItems() > 0) {
      $gatewayName = ShoprocketCommon::postVal('shoprocket-gateway-name');
      if($_SERVER['REQUEST_METHOD'] == 'POST' && $gatewayName != 'ShoprocketPayPalPro') {
        return ($gatewayName == "ShoprocketManualGateway") ? $this->manualCheckout() : "";
      }

      if(!ShoprocketSession::get('ShoprocketCart')->hasPayPalSubscriptions()) {
        if(ShoprocketSession::get('ShoprocketCart')->getGrandTotal() > 0 || ShoprocketSession::get('ShoprocketCart')->hasSpreedlySubscriptions()) {
          try {
            $paypal = new ShoprocketPayPalPro();
            $view = $this->_buildCheckoutView($paypal);
          }
          catch(ShoprocketException $e) {
            $exception = ShoprocketException::exceptionMessages($e->getCode(), $e->getMessage());
            $view = ShoprocketCommon::getView('views/error-messages.php', $exception);
          }
          return $view;
        }
        elseif(ShoprocketSession::get('ShoprocketCart')->countItems() > 0) {
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Displaying manual checkout instead of PayPal Pro Checkout because the cart value is $0.00");
          return $this->manualCheckout();
        }
      }
      else {
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering PayPal Pro checkout form because the cart contains a PayPal subscription");
      }
    }
  }

  public function payPalExpressCheckout($attrs) {
    if(ShoprocketSession::get('ShoprocketCart')->countItems() > 0) {
      $cart = ShoprocketSession::get('ShoprocketCart');

      if($cart->hasSpreedlySubscriptions()) {
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering PayPal Express checkout form because the cart contains Spreedly subscriptions");
        $errorMessage = "<p class='ShoprocketError'>Spreedly subscriptions cannot be processed through PayPal Express Checkout</p>";
        return $errorMessage;
      }
      else {
        if($cart->getGrandTotal() > 0 || $cart->hasPayPalSubscriptions()) {
          try {
            $view = ShoprocketCommon::getView('views/paypal-expresscheckout.php', $attrs);
          }
          catch(ShoprocketException $e) {
            $exception = ShoprocketException::exceptionMessages($e->getCode(), $e->getMessage());
            $view = ShoprocketCommon::getView('views/error-messages.php', $exception);
          }
          return $view;
        }
        elseif($cart->countItems() > 0) {
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Displaying manual checkout instead of PayPal Pro Express Checkout because the cart value is $0.00");
          return $this->manualCheckout();
        }
      }
    }
  }

  public function payPalExpress($attrs) {
    try {
      $view = ShoprocketCommon::getView('views/paypal-express.php', $attrs);
    }
    catch(ShoprocketException $e) {
      $exception = ShoprocketException::exceptionMessages($e->getCode(), $e->getMessage());
      $view = ShoprocketCommon::getView('views/error-messages.php', $exception);
    }
    return $view;
  }

  public function processIPN($attrs) {
    require_once(SHOPROCKET_PATH . "/models/ShoprocketPayPalIpn.php");
    require_once(SHOPROCKET_PATH . "/gateways/ShoprocketPayPalStandard.php");
    $ipn = new ShoprocketPayPalIpn();
    if($ipn->validate($_POST)) {
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Working with  IPN transaction type: " . $_POST['txn_type']);
      switch($_POST['txn_type']) { 
        case 'cart':              // Payment received for multiple items; source is Express Checkout or the PayPal Shopping Cart.
          $ipn->saveCartOrder($_POST);
          break;
        case 'recurring_payment':
          $ipn->logRecurringPayment($_POST);
          break;
        case 'recurring_payment_profile_cancel':
          $ipn->cancelSubscription($_POST);
          break;
        default:
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] IPN transaction type not implemented: " . $_POST['txn_type']);
      }
    }
    else {
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] IPN verification failed");
    }
  }

  public function shoprocketTests() {
    $view = ShoprocketCommon::getView('views/tests.php');
    $view = "<pre>$view</pre>";
    return $view;
  }

  public function clearCart() {
    ShoprocketSession::drop('ShoprocketCart');
    ShoprocketSession::drop('ShoprocketPromotion');
    ShoprocketSession::drop('terms_acceptance');
    ShoprocketSession::drop('ShoprocketProRateAmount');
    ShoprocketSession::drop('ShoprocketShippingCountryCode');
  }
  
  public function accountCreate($attrs) {
    $render_form = false;
    if(isset($attrs['product'])) {
      $product = new ShoprocketProduct();
      $product->load($attrs['product']);
      if($product->id <= 0) {
        $product->loadByItemNumber($attrs['product']);
      }
      if($product->isMembershipProduct() || $product->isPayPalSubscription()) {
        $render_form = true;
      }
    }
    $data = array(
      'attrs' => $attrs,
      'render_form' => $render_form
    );
    $view = ShoprocketCommon::getView('pro/views/account-create.php', $data);
    return $view;
  }
  
  public function accountLogin($attrs) {
    $account = new ShoprocketAccount();
    
    if($accountId = ShoprocketCommon::isLoggedIn()) {
      $account->load($accountId);
    }
    
    $data = array('account' => $account);
    
    // Look for password reset task
    if(isset($_POST['shoprocket-task']) && $_POST['shoprocket-task'] == 'account-reset') {
      $data['resetResult'] = $account->passwordReset();
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Attempted to reset password: " . $data['resetResult']->message);
    }
    
    // Build the account login view
    $view = ShoprocketCommon::getView('views/account-login.php', $data);
    
    if(isset($_POST['shoprocket-task']) && $_POST['shoprocket-task'] == 'account-login') {
      if($account->login($_POST['login']['username'], $_POST['login']['password'])) {
        ShoprocketSession::set('ShoprocketAccountId', $account->id);
        
        // Send logged in user to the appropriate page after logging in
        $url = ShoprocketCommon::getCurrentPageUrl();
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account Login: " . print_r($attrs, true));
        if(isset($attrs['url']) && !empty($attrs['url'])) {
          if('stay' != strtolower($attrs['url'])) {
            $url = $attrs['url'];
          }
        }
        else {
          if(ShoprocketSession::get('ShoprocketAccessDeniedRedirect')) {
            $url = ShoprocketSession::get('ShoprocketAccessDeniedRedirect');
          }
          else {
            // Locate logged in user home page
            $pgs = get_posts('numberposts=1&post_type=any&meta_key=shoprocket_member&meta_value=home');
            if(count($pgs)) {
              $url = get_permalink($pgs[0]->ID);
            }
          }
        }
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Redirecting after login to: $url");
        ShoprocketSession::drop('ShoprocketAccessDeniedRedirect');
        wp_redirect($url);
        exit;
      }
      else {
        $view .= "<p class='ShoprocketError'>Login failed</p>";
      }
    }
    
    return $view;
  }
  
  /**
   * Unset the ShoprocketAccountId from the session and redirect to $attr['url'] if the url attribute is provided.
   * If no redirect url is provided, look for the page with the custom field shoprocket_member=logout
   * If no custom field is set then redirect to the current page after logging out
   */
  public function accountLogout($attrs) {
    // Save zendesk error to session
    if(isset($_GET['kind']) && $_GET['kind'] == 'error' && isset($_GET['message'])){
      $zendeskError = $_GET['message'];
      ShoprocketSession::set('zendesk_logout_error',$zendeskError,true);
    }
    $url = ShoprocketCommon::getCurrentPageUrl();
    if(isset($attrs['url']) && !empty($attrs['url'])) {
      $url = $attrs['url'];
    }
    else {
      $url = ShoprocketProCommon::getLogoutUrl();
    }
    ShoprocketAccount::logout($url);
  }
  
  public function accountLogoutLink($attrs) {
    $url = ShoprocketCommon::replaceQueryString('shoprocket-task=logout');
    $linkText = isset($attrs['text']) ? $attrs['text'] : 'Log out';
    $link = "<a href='$url'>$linkText</a>";
    return $link;
  }
  
  /**
   * Return the Spreedly url to manage the subscription or the
   * PayPal url to cancel the subscription. 
   * If the visitor is not logged in, return false.
   * You can pass in text for the link and a custom return URL
   * 
   * $attr = array(
   *   text => 'The link text for the subscription management link'
   *   return => 'Customize the return url for the spreedly page'
   * )
   * 
   * @return string Spreedly subscription management URL
   */
  public function accountInfo($attrs) {
    if(ShoprocketCommon::isLoggedIn()) {
      $data = array();
      $account = new ShoprocketAccount(ShoprocketSession::get('ShoprocketAccountId'));
      if(isset($_POST['shoprocket-task']) && $_POST['shoprocket-task'] == 'account-update') {
        $login = $_POST['login'];
        if($login['password'] == $login['password2']) {
          $account->firstName = $login['first_name'];
          $account->lastName = $login['last_name'];
          $account->email = $login['email'];
          $account->password = empty($login['password']) ? $account->password : md5($login['password']);
          $account->username = $login['username'];
          $errors = $account->validate();
          if(count($errors) == 0) {
            $account->save();
            if($account->isSpreedlyAccount()) {
              SpreedlySubscriber::updateRemoteAccount($account->id, array('email' => $account->email));
            }
            $data['message'] = 'Your account is updated';
            ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account was updated: " . print_r($account->getData, true));
          }
          else {
            $data['errors'] = $account->getErrors();
            ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account validation failed: " . print_r($data['errors'], true));
          }
        }
        else {
          $data['errors'] = "Account not updated. The passwords entered did not match";
        }
      }
      
      $data['account'] = $account;
      $data['url'] = false;
      
      if($account->isSpreedlyAccount()) {
        $accountSub = $account->getCurrentAccountSubscription();
        $text = isset($attrs['text']) ? $attrs['text'] : 'Manage your subscription.';
        $returnUrl = isset($attrs['return']) ? $attrs['return'] : null;
        $url = $accountSub->getSubscriptionManagementLink($returnUrl);
        $data['url'] = $url;
        $data['text'] = $text;
      }
      
      $view = ShoprocketCommon::getView('views/account-info.php', $data);
      return $view;
    }
    else {
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Trying to view account subscription short code but account holder is not logged into Shoprocket.");
    }
  }
  
  public function accountDetails($attrs) {
    if(ShoprocketCommon::isLoggedIn()) {
      $display = '';
      $account = new ShoprocketAccount(ShoprocketSession::get('ShoprocketAccountId'));
      $text = isset($attrs['display']) ? $attrs['display'] : null;
      if(isset($attrs['display']) && $attrs['display'] != '' && isset($account->$attrs['display'])) {
        $display = $account->$attrs['display'];
      }
      return $display;
    }
    else {
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Trying to view account details but account holder is not logged into Shoprocket.");
    }
  }
  
  public function cancelPayPalSubscription($attrs) {
    $link = '';
    if(ShoprocketCommon::isLoggedIn()) {
      $account = new ShoprocketAccount(ShoprocketSession::get('ShoprocketAccountId'));
      if($account->isPayPalAccount()) {
        
        // Look for account cancelation request
        if(isset($_GET['shoprocket-task']) && $_GET['shoprocket-task'] == 'CancelRecurringPaymentsProfile') {
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Caught task: CancelPaymentsProfileStatus");
          $sub = new ShoprocketAccountSubscription($account->getCurrentAccountSubscriptionId());
          $profileId = $sub->paypalBillingProfileId;
          $note = "Your subscription has been canceled per your request.";
          $action = "Cancel";
          $pp = new ShoprocketPayPalPro();
          $pp->ManageRecurringPaymentsProfileStatus($profileId, $action, $note);
          $url = str_replace('shoprocket-task=CancelRecurringPaymentsProfile', '', ShoprocketCommon::getCurrentPageUrl());
          $link = "We sent a cancelation request to PayPal. It may take a minute or two for the cancelation process to complete and for your account status to be changed.";
        }
        elseif($subId = $account->getCurrentAccountSubscriptionId()) {
          $sub = new ShoprocketAccountSubscription($subId);
          if($sub->status == 'active') {
            $url = $sub->getSubscriptionManagementLink();
            $text = isset($attrs['text']) ? $attrs['text'] : 'Cancel your subscription';
            $link = "<a id='ShoprocketCancelPayPalSubscription' href=\"$url\">$text</a>";
          }
          else {
            $link = "Your account is $sub->status but will remain active until " . date(get_option('date_format'), strtotime($sub->activeUntil));
          }
        }
        
        
        
      }
    }
    ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Cancel paypal account link for logged in user: $link");
    
    return $link;
  }
  
  public function currentSubscriptionPlanName() {
    $name = 'You do not have an active subscription';
    if(ShoprocketCommon::isLoggedIn()) {
      $account = new ShoprocketAccount(ShoprocketSession::get('ShoprocketAccountId'));
      if($subId = $account->getCurrentAccountSubscriptionId()) {
        $sub = new ShoprocketAccountSubscription($subId);
        $name = $sub->subscriptionPlanName;
      }
    }
    return $name;
  }
  
  public function currentSubscriptionFeatureLevel() {
    $level = 'No access';
    if(ShoprocketCommon::isLoggedIn()) {
      $account = new ShoprocketAccount(ShoprocketSession::get('ShoprocketAccountId'));
      if($subId = $account->getCurrentAccountSubscriptionId()) {
        $sub = new ShoprocketAccountSubscription($subId);
        $level = $sub->featureLevel;
      }
    }
    return $level;
  }

  public function spreedlyListener() {
    if(isset($_POST['subscriber_ids'])) {
      $ids = explode(',', $_POST['subscriber_ids']);
      foreach($ids as $id) {
        try {
          $subscriber = SpreedlySubscriber::find($id);
          $subscriber->updateLocalAccount();
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Updated local account id: $id");
        }
        catch(SpreedlyException $e) {
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] I heard that subscriber $id was changed but I can't do anything about it. " . $e->getMessage());
        }
        
      }
    }
    else {
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] This is not a valid call to the spreedly listener.");
    }
    
    ob_clean();
    header('HTTP/1.1 200 OK');
    die();
  }
  
  /**
   * Show content to qualifying feature levels.
   * 
   * The $attrs parameter should contain the key "level" which contains
   * a CSV list of feature levels which are allowed to see the enclosed content.
   * 
   * A special feature level of "all_members" may be provided to show the content to
   * any logged in member regardless of feature level. Note that expired accounts may log
   * in but will not have a feature level. Therefore providing "all_members" as the required
   * level will show the content to logged in members with expired accounts.
   * 
   * Because ShoprocketCommon::trmmedExplode is used to parse the feature levels, the 
   * feature level list may include spaces. The following two lists are the same:
   *   one,two,three,four
   *   one, two, three, four
   */
  public function showTo($attrs, $content='null') {
    $isAllowed = false;
    if(ShoprocketCommon::isLoggedIn()) {
      $levels = ShoprocketCommon::trimmedExplode(',', $attrs['level']);
      $account = new ShoprocketAccount();
      if($account->load(ShoprocketSession::get('ShoprocketAccountId'))) {
        if(in_array('all_members', $levels)) {
          $isAllowed = true;
        }
        elseif($account->isActive() && in_array($account->getFeatureLevel(), $levels)) {
          $isAllowed = true;
        }
      }
    }
    $content = $isAllowed ? $content : '';

    return do_shortcode($content);
  }
  
  /**
   * This is the inverse of the showTo shortcode function above.
   */
  public function hideFrom($attrs, $content='null') {
    $isAllowed = true;
    if(ShoprocketCommon::isLoggedIn()) {
      $levels = ShoprocketCommon::trimmedExplode(',', $attrs['level']);
      $account = new ShoprocketAccount();
      if(in_array('all_members', $levels)) {
        $isAllowed = false;
      }
      elseif($account->load(ShoprocketSession::get('ShoprocketAccountId'))) {
        if($account->isActive() && in_array($account->getFeatureLevel(), $levels)) {
          $isAllowed = false;
        }
      }
    }
    $content = $isAllowed ? $content : '';

    return do_shortcode($content);
  }
  
  public function postSale($attrs, $content='null') {
    $postSale = false;
    if(isset($_GET['ouid'])) {
      $order = new ShoprocketOrder();
      $order->loadByOuid($_GET['ouid']);
      if($order->viewed == 0) {
        $postSale = true;
      }
    }
    $content = $postSale ? $content : '';
    return do_shortcode($content);
  }
  
  public function shoprocketAffiliate($attrs) {
    $content = '';
    if(isset($_GET['ouid']) && isset($attrs['display'])) {
      $order = new ShoprocketOrder();
      $order->loadByOuid($_GET['ouid']);
      //if($order->viewed == 0) {
        $content = $order->$attrs['display'];
      //}
    }
    return $content;
  }
  
  public function gravityFormToCart($entry) {
    if(false) {
      $formId = ShoprocketGravityReader::getGravityFormIdForEntry($entry['id']);
      if($formId) {
        $productId = ShoprocketProduct::getProductIdByGravityFormId($formId);
        if($productId > 0) {
          $product = new ShoprocketProduct($productId);
          $qty = $product->gravityCheckForEntryQuantity($entry);
          $options = $product->gravityGetVariationPrices($entry);
          $productUrl = ShoprocketCommon::getCurrentPageUrl();
          $cart = ShoprocketSession::get('ShoprocketCart');
          $item = $cart->addItem($productId, $qty, $options, $entry['id'], $productUrl, false, true);
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Cart Item Value: " . print_r($item, true));
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Should we use the gravity forms price? " . $product->gravity_form_pricing . 
            ' :: Session value: ' . ShoprocketSession::get('userPrice_' . $product->id));
          
          if($product->gravity_form_pricing == 1) {
            $price = ShoprocketGravityReader::getPrice($entry['id']) / $qty;
            $entry_id = $item->getFirstFormEntryId();
            $user_price_name = 'userPrice_' . $productId . '_' . $entry_id;
            ShoprocketSession::set($user_price_name, $price, true); // Setting the price of a Gravity Forms pricing product
            ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Using gravity forms pricing for product: Price: $price :: Name: " . $product->name . 
              " :: Session variable name: $user_price_name");
          }
          
          $cartPage = get_page_by_path('store/cart');
          $cartPageLink = get_permalink($cartPage->ID);
          ShoprocketSession::set('ShoprocketLastPage', $_SERVER['HTTP_REFERER']);
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Shoprocket Session Dump: " . ShoprocketSession::dump());
          
          if(!ShoprocketSetting::getValue('display_form_entries_before_sale')) {
            $entry["status"] = 'unpaid';
          }
          RGFormsModel::update_lead($entry);
          $cart->applyAutoPromotions();
          do_action('shoprocket_after_add_to_cart', $product, $qty);
          wp_redirect($cartPageLink);
          exit;
        }
      }
    }
  }
  
  public function zendeskRemoteLogin() {
    if(ShoprocketCommon::isLoggedIn()) {
      $account = new ShoprocketAccount(ShoprocketSession::get('ShoprocketAccountId'));
      if($account) {
        ZendeskRemoteAuth::login($account);
      }
    }
  }
  
  public function downloadFile($attrs) {
    $link = false;
    if(isset($attrs['path'])) {
      $path = urlencode($attrs['path']);
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] encoded $path");
      $nvp = 'task=member_download&path=' . $path;
      $url = ShoprocketCommon::replaceQueryString($nvp);
      
      if(ShoprocketCommon::isLoggedIn()) {
        $link = '<a class="ShoprocketDownloadFile" href="' . $url . '">' . $attrs['text'] . '</a>';
      }
      else {
        $link = $attrs['text'];
      }
    }
    return $link;
  }
  
  public function termsOfService($attrs) {
    if(false) {
      $attrs = array("location"=>"ShoprocketShortcodeTOS");
      $view = ShoprocketCommon::getView('/pro/views/terms.php', $attrs);
      return $view;
    }
  }
  
  public function accountExpiration($attrs, $content = null){
    $output = false;
    if(ShoprocketCommon::isLoggedIn()) {
      $data = array();
      $account = new ShoprocketAccount(ShoprocketSession::get('ShoprocketAccountId'));
      $subscription = $account->getCurrentAccountSubscription($account->id);
      $expirationDate = $subscription->active_until;
      $format = get_option('date_format');
      if(isset($attrs['format'])){
        $format = $attrs['format'];
      }
      
      $output = date($format,strtotime($expirationDate));
      
      // expired?
      if(strtotime($expirationDate) <= strtotime("now")){
        if(isset($attrs['expired'])){
          $output = $attrs['expired'];          
        }
        if(!empty($content)){
          $output = $content;
        }
      }
      
      //lifetime?
      if($subscription->lifetime == 1){
        $output = "Lifetime";
        if(isset($attrs['lifetime'])){
          $output = $attrs['lifetime'];          
        }
      }
      
    }
    
    return do_shortcode($output);
  }
  
  public function mijirehCheckout() {
    if(ShoprocketSession::get('ShoprocketCart')->countItems() > 0) {
      $gatewayName = ShoprocketCommon::postVal('shoprocket-gateway-name');
      if($_SERVER['REQUEST_METHOD'] == 'POST' && $gatewayName != 'ShoprocketMijireh') {
        return ($gatewayName == "ShoprocketManualGateway") ? $this->manualCheckout() : "";
      }

      if(!ShoprocketSession::get('ShoprocketCart')->hasPayPalSubscriptions()) {
        require_once(SHOPROCKET_PATH . "/gateways/ShoprocketMijireh.php");

        if(ShoprocketSession::get('ShoprocketCart')->getGrandTotal() > 0 || ShoprocketSession::get('ShoprocketCart')->hasSpreedlySubscriptions()) {
          try {
            $mj = new ShoprocketMijireh();
            $view = $this->_buildCheckoutView($mj);
          }
          catch(ShoprocketException $e) {
            $exception = ShoprocketException::exceptionMessages($e->getCode(), $e->getMessage());
            $view = ShoprocketCommon::getView('views/error-messages.php', $exception);
          }
          return $view;
        }
        elseif(ShoprocketSession::get('ShoprocketCart')->countItems() > 0) {
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Displaying manual checkout instead of Mijireh Checkout because the cart value is $0.00");
          return $this->manualCheckout();
        }

      }
      else {
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering Mijireh checkout form because the cart contains a PayPal subscription");
      }
    }
  }
  
  protected function _buildCheckoutView($gateway) {
    $ssl = ShoprocketSetting::getValue('auth_force_ssl');
    if($ssl) {
      if(!ShoprocketCommon::isHttps()) {
        $sslUrl = "https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        wp_redirect($sslUrl);
        exit;
      }
    }
    
    if(!ShoprocketSession::get('ShoprocketCart')->requirePayment()) {
      require_once(SHOPROCKET_PATH . "/gateways/ShoprocketManualGateway.php");
      $gateway = new ShoprocketManualGateway();
    }
    
    $view = ShoprocketCommon::getView('views/checkout.php', array('gateway' => $gateway), true, true);
    return $view;
  }
  
  public function emailShortcodes($attrs) {
    $output = '';
    if($attrs['source'] == 'receipt' || $attrs['source'] == 'fulfillment' || $attrs['source'] == 'status' || $attrs['source'] == 'followup') {
      $order = new ShoprocketOrder($attrs['id']);
      $data = array(
        'bill_first_name', 
        'bill_last_name',
        'bill_address',
        'bill_address2',
        'bill_city',
        'bill_state',
        'bill_country',
        'bill_zip',
        'ship_first_name',
        'ship_last_name',
        'ship_address',
        'ship_address2',
        'ship_city',
        'ship_state',
        'ship_country',
        'ship_zip',
        'phone',
        'email',
        'coupon',
        'discount_amount',
        'trans_id',
        'shipping',
        'subtotal',
        'tax',
        'total',
        'non_subscription_total',
        'custom_field',
        'ordered_on',
        'status',
        'ip',
        'products',
        'fulfillment_products',
        'receipt',
        'receipt_link',
        'ouid',
        'shipping_method',
        'account_id',
        'tracking_number'
      );
      if(in_array($attrs['att'], $data)) {
        switch ($attrs['att']) {
          case 'bill_first_name': // Intentional falling through
          case 'bill_last_name':
          case 'ship_first_name':
          case 'ship_last_name':
            $output = ucfirst(strtolower($order->$attrs['att']));
            break;
          case 'bill_address':
            if($order->bill_address2 != ''){
              $output = $order->$attrs['att'] . '<br />' . $order->bill_address2;
            }
            else {
              $output = $order->$attrs['att'];
            }
            break;
          case 'ship_address':
            if($order->ship_address2 != ''){
              $output = $order->$attrs['att'] . '<br />' . $order->ship_address2;
            }
            else {
              $output = $order->$attrs['att'];
            }
            break;
          case 'products':
            $output = ShoprocketCommon::getView('/pro/views/emails/email-products.php', array('order' => $order, 'type' => $attrs['type'], 'code' => 'products'));
            break;
          case 'fulfillment_products':
            $output = ShoprocketCommon::getView('/pro/views/emails/email-products.php', array('order' => $order, 'type' => $attrs['type'], 'code' => 'fulfillment_products', 'variable' => $attrs['variable']));
            break;
          case 'receipt':
            $output = ShoprocketCommon::getView('/pro/views/emails/email-receipt.php', array('order' => $order, 'type' => $attrs['type']));
            break;
          case 'phone':
            $output = ShoprocketCommon::formatPhone($order->$attrs['att']);
            break;
          case 'total':
            $output = ShoprocketCommon::currency($order->$attrs['att'], false);
            break;
          case 'tax':
            $output = ShoprocketCommon::currency($order->$attrs['att'], false);
            break;
          case 'receipt_link':
            $receiptPage = get_page_by_path('store/receipt');
            $link = get_permalink($receiptPage->ID);
            if(strstr($link,"?")){
              $link .= '&ouid=';
            }
            else{
              $link .= '?ouid=';
            }
            $output = $link . $order->ouid;
            break;
          default:
            $output = $order->$attrs['att'];
        }

      }
      elseif(substr($attrs['att'], 0, 8) == 'tracking') {
        $output = ShoprocketAdvancedNotifications::updateTracking($order, $attrs);
      }
      elseif(substr($attrs['att'], 0, 5) == 'date:') {
        $output = ShoprocketAdvancedNotifications::updateDate($attrs);
      }
      elseif(substr($attrs['att'], 0, 12) == 'date_ordered') {
        $output = ShoprocketAdvancedNotifications::updateDateOrdered($order, $attrs);
      }
      $shipping_options = array(
        'ship_first_name',
        'ship_last_name',
        'ship_address',
        'ship_address2',
        'ship_city',
        'ship_state',
        'ship_country',
        'ship_zip',
      );
      if(in_array($attrs['att'], $shipping_options) && $order->shipping_method == 'None') {
        $output = '';
      }
    }
    elseif($attrs['source'] == 'reminder') {
      $sub = new ShoprocketAccountSubscription($attrs['id']);
      $account = new ShoprocketAccount();
      $account->load($sub->account_id);
      $data = array(
        'billing_first_name', 
        'billing_last_name',
        'feature_level',
        'subscription_plan_name',
        'active_until',
        'billing_interval',
        'username',
        'opt_out_link'
      );
      if(in_array($attrs['att'], $data)) {
        switch ($attrs['att']) {
          case 'billing_first_name': // Intentional falling through
          case 'billing_last_name':
            $output = ucfirst(strtolower($sub->$attrs['att']));
            break;
          case 'active_until':
            $output = date(get_option('date_format'), strtotime($sub->$attrs['att']));
            break;
          case 'username':
            $output = $account->$attrs['att'];
            break;
          case 'opt_out_link':
            $output = ShoprocketProCommon::generateUnsubscribeLink($account->id);
            break;
          default;
            $output = $sub->$attrs['att'];
        }
        
      }
    }
    ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] output: $output");
    return $output;
  }
  
  public function emailOptOut() {
    if(isset($_GET['shoprocket-task']) && $_GET['shoprocket-task'] == 'opt_out') {
      if(isset($_GET['e']) && isset($_GET['t'])) {
        $email = base64_decode(urldecode($_GET['e']));
        $verify = ShoprocketProCommon::verifyEmailToken($_GET['t'], $email);
        if($verify == 1) {
          $data = array(
            'form' => 'form',
            'email' => $email,
            'token' => $_GET['t']
          );
          echo ShoprocketCommon::getView('pro/views/unsubscribe.php', $data);
        }
        else {
          if($verify == -1) {
            $message = __('This email has already been unsubscribed', 'shoprocket');
          }
          if($verify == -2) {
            $message = __('This email does not exist in our system', 'shoprocket');
          }
          $data = array(
            'form' => 'error',
            'message' => $message
          );
          echo ShoprocketCommon::getView('pro/views/unsubscribe.php', $data);
        }
      }
    }
    elseif(isset($_GET['shoprocket-action']) && $_GET['shoprocket-action'] == 'opt_out') {
      ShoprocketProCommon::unsubscribeEmailToken($_POST['token'], $_POST['email']);
      $data = array(
        'form' => 'opt_out',
        'email' => $_POST['email']
      );
      echo ShoprocketCommon::getView('pro/views/unsubscribe.php', $data);
    }
    elseif(isset($_GET['shoprocket-action']) && $_GET['shoprocket-action'] == 'cancel_opt_out') {
      $data = array(
        'form' => 'cancel'
      );
      echo ShoprocketCommon::getView('pro/views/unsubscribe.php', $data);
    }
  }
  
}
