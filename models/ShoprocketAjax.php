<?php
class ShoprocketAjax {
  
  public static function resendEmailFromLog() {
    $log_id = $_POST['id'];
    $resendEmail = ShoprocketEmailLog::resendEmailFromLog($log_id);
    if($resendEmail) {
      $result[0] = 'ShoprocketModal alert-message success';
      $result[1] = '<strong>Success</strong><br/>' . __('Email successfully resent', 'shoprocket') . ' <br />';
    }
    else {
      $result[0] = 'ShoprocketModal alert-message alert-error';
      $result[1] = '<strong>Error</strong><br/>' . __('Email was not resent Successfully', 'shoprocket') . '<br>';
    }
    echo json_encode($result);
    die();
  }
  
  public function forcePluginUpdate(){
    $output = false;
    update_option('_site_transient_update_plugins', '');
    update_option('_transient_update_plugins', '');
    delete_transient('_shoprocket_version_request');
    $output = true;
    echo $output;
    die();
  }
  
  public static function sendTestEmail() {
    $to = $_POST['email'];
    $status = $_POST['status'];
    if(!ShoprocketCommon::isValidEmail($to)) {
      $result[0] = 'ShoprocketModal alert-message alert-error';
      $result[1] = '<strong>Error</strong><br/>' . __('Please enter a valid email address', 'shoprocket') . '<br>';
    }
    else {
      if(isset($_GET['type']) && $_GET['type'] == 'reminder') {
        $sendEmail = ShoprocketMembershipReminders::sendTestReminderEmails($to, $_GET['id']);
      }
      else {
        $sendEmail = ShoprocketAdvancedNotifications::sendTestEmail($to, $status);
      }
      if($sendEmail) {
        $result[0] = 'ShoprocketModal alert-message success';
        $result[1] = '<strong>Success</strong><br/>' . __('Email successfully sent to', 'shoprocket') . ' <br /><strong>' . $to . '</strong><br>';
      }
      else {
        $result[0] = 'ShoprocketModal alert-message alert-error';
        $result[1] = '<strong>Error</strong><br/>' . __('Email not sent. There is an unknown error.', 'shoprocket') . '<br>';
      }
    }
    echo json_encode($result);
    die();
  }
  
  public static function ajaxReceipt() {
    if(isset($_GET['order_id'])) {
      $orderReceipt = new ShoprocketOrder($_GET['order_id']);
      $printView = ShoprocketCommon::getView('views/receipt_print_version.php', array('order' => $orderReceipt));
      $printView = str_replace("\n", '', $printView);
      $printView = str_replace("'", '"', $printView);
      echo $printView;
      die();
    }
  }
  
  public static function ajaxOrderLookUp() {
    $redirect = true;
    $order = new ShoprocketOrder();
    $order->loadByOuid($_POST['ouid']);
    if(!ShoprocketSession::get('ShoprocketPendingOUID')) {
      ShoprocketSession::set('ShoprocketPendingOUID', $_POST['ouid']);
    }
    if(empty($order->id) || $order->status == 'checkout_pending') {
      $redirect = false;
    }
    echo json_encode($redirect);
    die();
  }
  
  public static function viewLoggedEmail() {
    if(isset($_POST['log_id'])) {
      $emailLog = new ShoprocketEmailLog($_POST['log_id']);
      echo nl2br(htmlentities($emailLog->headers . "\r\n" . $emailLog->body, ENT_COMPAT, 'UTF-8'));
      die();
    }
  }
  
  public static function checkPages(){
    $Shoprocket = new Shoprocket();
    echo $Shoprocket->shoprocket_page_check(true);
    die();
  }
  
  public static function shortcodeProductsTable() {
    global $wpdb;
    $prices = array();
  	$types = array(); 
  	//$options='';
    $postId = ShoprocketCommon::postVal('id');
    $product = new ShoprocketProduct();
    $products = $product->getModels("where id=$postId", "order by name");
    $data = array();
    foreach($products as $p) {
      if($p->itemNumber==""){
        $type='id';
      }
      else{
        $type='item';
      }

  	  $types[] = htmlspecialchars($type);

  	  if(false && $p->isPayPalSubscription()) {
  	    $sub = new ShoprocketPayPalSubscription($p->id);
  	    $subPrice = strip_tags($sub->getPriceDescription($sub->offerTrial > 0, '(trial)'));
  	    $prices[] = htmlspecialchars($subPrice);
  	    ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] subscription price in dialog: $subPrice");
  	  }
  	  else {
  	    $prices[] = htmlspecialchars(strip_tags($p->getPriceDescription()));
  	  }


  	  //$options .= '<option value="'.$id.'">'.$p->name.' '.$description.'</option>';
      $data[] = array('type' => $types, 'price' => $prices, 'item' => $p->itemNumber);
    }
    echo json_encode($data);
    die();
  }
  
  public static function ajaxTaxUpdate() {
    if(isset($_POST['state']) && isset($_POST['state_text']) && isset($_POST['zip']) && isset($_POST['gateway'])) {
      $gateway = ShoprocketAjax::loadAjaxGateway($_POST['gateway']);
      $gateway->setShipping(array('state_text' => $_POST['state_text'], 'state' => $_POST['state'], 'zip' => $_POST['zip']));
      $s = $gateway->getShipping();
      if($s['state'] && $s['zip']){
        $id = 1;
        $taxLocation = $gateway->getTaxLocation();
        $tax = $gateway->getTaxAmount();
        $rate = $gateway->getTaxRate();
        $total = ShoprocketSession::get('ShoprocketCart')->getGrandTotal() + $tax;
        ShoprocketSession::set('ShoprocketTax', $tax);
        ShoprocketSession::set('ShoprocketTaxRate', ShoprocketCommon::tax($rate));
      }
      else {
        $id = 0;
        $tax = 0;
        $rate = 0;
        $total = ShoprocketSession::get('ShoprocketCart')->getGrandTotal() + $tax;
        ShoprocketSession::set('ShoprocketTax', $tax);
        ShoprocketSession::set('ShoprocketTaxRate', ShoprocketCommon::tax($rate));
      }
      if(ShoprocketSession::get('ShoprocketCart')->getTax('All Sales')) {
        $rate = $gateway->getTaxRate();
        ShoprocketSession::set('ShoprocketTaxRate', ShoprocketCommon::tax($rate));
      }
    }
    $result = array(
      'id' => $id,
      'state' => $s['state'],
      'zip' => $s['zip'],
      'tax' => ShoprocketCommon::currency($tax),
      'rate' => $rate == 0 ? '0.00%' : ShoprocketCommon::tax($rate),
      'total' => ShoprocketCommon::currency($total)
    );
    echo json_encode($result);
    die();
  }
  
  public static function loadAjaxGateway($gateway) {
    switch($gateway) {
      case 'ShoprocketManualGateway':
        require_once(SHOPROCKET_PATH . "/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'Shoprocket2Checkout':
        require_once(SHOPROCKET_PATH . "/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'ShoprocketAuthorizeNet':
        require_once(SHOPROCKET_PATH . "/pro/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'ShoprocketEway':
        require_once(SHOPROCKET_PATH . "/pro/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'ShoprocketMijireh':
        require_once(SHOPROCKET_PATH . "/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'ShoprocketMWarrior':
        require_once(SHOPROCKET_PATH . "/pro/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'ShoprocketPayLeap':
        require_once(SHOPROCKET_PATH . "/pro/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'ShoprocketPayPalPro':
        require_once(SHOPROCKET_PATH . "/pro/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'ShoprocketStripe':
        require_once(SHOPROCKET_PATH . "/pro/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      default:
        break;
    }
    return $gateway;
  }
  
  public static function ajaxCartElements($args="") {

    $items = ShoprocketSession::get('ShoprocketCart')->getItems();
    $product = new ShoprocketProduct();
    $products = array();
    foreach($items as $itemIndex => $item) {
      $product->load($item->getProductId());
      $products[] = array(
        'productName' => $item->getFullDisplayName(),
        'productQuantity' => $item->getQuantity(),
        'productPrice' => ShoprocketCommon::currency($item->getProductPrice()),
        'productSubtotal' => ShoprocketCommon::currency($item->getProductPrice() * $item->getQuantity())
      );
    }
    
    $summary = array(
      'items' => ' ' . _n('item', 'items', ShoprocketCartWidget::countItems(), 'shoprocket'), 
      'amount' => ShoprocketCommon::currency(ShoprocketCartWidget::getSubTotal()), 
      'count' => ShoprocketCartWidget::countItems()
    );
    
    $array = array(
      'summary' => $summary,
      'products' => $products,
      'subtotal' => ShoprocketCommon::currency(ShoprocketSession::get('ShoprocketCart')->getSubTotal()),
      'shipping' => ShoprocketSession::get('ShoprocketCart')->requireShipping() ? 1 : 0,
      'shippingAmount' => ShoprocketCommon::currency(ShoprocketSession::get('ShoprocketCart')->getShippingCost())
    );
    echo json_encode($array);
    die();
  }
  
  public static function ajaxAddToCart() {
    $message = ShoprocketSession::get('ShoprocketCart')->addToCart(true);
    if(!is_array($message)) {
      $message = array(
        'msgId' => -2,
        'msgHeader' => __('Error', 'shoprocket'),
        'msg' => '<p>' . __('An error occurred while trying to add a product to the cart. Please contact the site administrator.', 'shoprocket') . '</p>'
      );
    }
    echo json_encode($message);
    die();
  }
  
  public static function promotionProductSearch() {
    global $wpdb;
    $search = ShoprocketCommon::getVal('q');
    $product = new ShoprocketProduct();
    $tableName = ShoprocketCommon::getTableName('products');
    $products = $wpdb->get_results("SELECT id, name from $tableName WHERE name LIKE '%%%$search%%' ORDER BY id ASC LIMIT 10");
    $data = array();
    foreach($products as $p) {
      $data[] = array('id' => $p->id, 'name' => $p->name);
    }
    echo json_encode($data);
    die();
  }
  
  public static function loadPromotionProducts() {
    $productId = ShoprocketCommon::postVal('productId');
    $product = new ShoprocketProduct();
    $ids = explode(',', $productId);
    $selected = array();
    foreach($ids as $id) {
      $product->load($id);
      $selected[] = array('id' => $id, 'name' => $product->name);
    }
    echo json_encode($selected);
    die();
  }
  
  public static function saveSettings() {
    $error = '';
    foreach($_REQUEST as $key => $value) {
      if($key[0] != '_' && $key != 'action' && $key != 'submit' && $key) {
        if(is_array($value) && $key != 'admin_page_roles') {
          $value = array_filter($value, 'strlen');
          if(empty($value)) {
            $value = '';
          }
          else {
            $value = implode('~', $value);
          }
        }
        if($key == 'status_options') {
          $value = str_replace('&', '', ShoprocketCommon::deepTagClean($value));
        }
        if($key == 'home_country') {
          $hc = ShoprocketSetting::getValue('home_country');
        }
        elseif($key == 'countries') {
          if(strpos($value, '~') === false) {
            ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] country list value: $value");
            $value = '';
          }
          if(empty($value) && !empty($_REQUEST['international_sales'])){
            $error = "Please select at least one country to ship to.";
          }
        }
        elseif($key == 'enable_logging' && $value == '1') {
          try {
            ShoprocketLog::createLogFile();
          }
          catch(ShoprocketException $e) {
            $error = '<span>' . $e->getMessage() . '</span>';
            ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Caught Shoprocket exception: " . $e->getMessage());
          }
        }
        elseif($key == 'constantcontact_list_ids') {
          
        }
        elseif($key == 'admin_page_roles') {
          $value = serialize($value);
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Saving Admin Page Roles: " . print_r($value,true));
        }
        elseif($key == 'currency_decimals' && $value == 0) {
          $value = 'no_decimal';
        }

        ShoprocketSetting::setValue($key, trim(stripslashes($value)));

        if(false && $key == 'order_number') {
          $versionInfo = get_transient('_shoprocket_version_request');
          if(!$versionInfo) {
            $versionInfo = ShoprocketProCommon::getVersionInfo();
            set_transient('_shoprocket_version_request', $versionInfo, 43200);
          }
          if(!$versionInfo) {
            ShoprocketSetting::setValue('order_number', '');
            $error = '<span>' . __( 'Invalid Order Number' , 'shoprocket' ) . '</span>';
          }
        }
      }
    }

    if($error) {
      $result[0] = 'ShoprocketModal alert-message alert-error';
      $result[1] = "<strong>" . __("Warning","shoprocket") . "</strong><br/>$error";
    }
    else {
      $result[0] = 'ShoprocketModal alert-message success';
      $result[1] = '<strong>Success</strong><br/>' . $_REQUEST['_success'] . '<br>'; 
    }

    $out = json_encode($result);
    echo $out;
    die();
  }
  
  public static function updateGravityProductQuantityField() {
    $formId = ShoprocketCommon::getVal('formId');
    $gr = new ShoprocketGravityReader($formId);
    $fields = $gr->getStandardFields();
    header('Content-type: application/json');
    echo json_encode($fields);
    die();
  }
  
  public static function checkSyncOnAddToCart() {
    $result = array(true);
    $itemId = ShoprocketCommon::postVal('shoprocketItemId');
    $options = '';
    $optionsMsg = '';

    $opt1 = ShoprocketCommon::postVal('options_1');
    $opt2 = ShoprocketCommon::postVal('options_2');

    if(!empty($opt1)) {
      $options = $opt1;
      $optionsMsg = trim(preg_replace('/\s*([+-])[^$]*\$.*$/', '', $opt1));
    }
    if(!empty($opt2)) {
      $options .= '~' . $opt2;
      $optionsMsg .= ', ' . trim(preg_replace('/\s*([+-])[^$]*\$.*$/', '', $opt2));
    }

    $scrubbedOptions = ShoprocketProduct::scrubVaritationsForIkey($options);
    if(!ShoprocketProduct::confirmSync($itemId, $scrubbedOptions)) {
      $result[0] = false;
      $p = new ShoprocketProduct($itemId);

      $counts = $p->getSyncNamesAndCounts();
      $out = '';

      if(count($counts)) {
        $out = '<table class="SyncCountTableModal">';
        $out .= '<tr><td colspan="2"><strong>' . __('Currently In Stock', 'shoprocket') . '</strong></td></tr>';
        foreach($counts as $name => $qty) {
          $out .= '<tr>';
          $out .= "<td>$name</td><td>$qty</td>";
          $out .= '</tr>';
        }
        $out .= '</table>';
      }
      $soldOutLabel = ShoprocketSetting::getValue('label_out_of_stock') ? strtolower(ShoprocketSetting::getValue('label_out_of_stock')) : __('out of stock', 'shoprocket');
      $result[1] = $p->name . " " . $optionsMsg . " is $soldOutLabel $out";
    }
    
    $result = json_encode($result);
    echo $result;
    die();
  }

  public function Fetchproducts() {
    require_once(SHOPROCKET_PATH . "/models/Pest.php");
    require_once(SHOPROCKET_PATH . "/models/PestJSON.php");

    $rest = new Pest(SR_REST);
    try {
     $response = $rest->get('/productlist?companyid=91');
    }
    catch(Pest_Unauthorized $e) {
        header('Bad Request', true, 400);
        die();
      }

      $response = json_decode($response);
      $productArray = array();

  foreach($response as $key => $value) {
     $productArray['id'] = $value->product->id;
     $productArray['name']  = $value->product->name;
     $productArray['price'] = $value->product->price;
     $productArray['currency'] = $value->product->currency;
     $productArray['quantity'] = $value->product->quantity;
     $productArray['dateadded'] = $value->product->dateadded;
     $productArray['companyid'] = $value->product->companyid;
     $productArray['externalid'] = $value->product->externalid;
     $productArray['notes'] = $value->product->notes;
     $productArray['views'] = $value->product->views;
     $productArray['slug'] = $value->product->slug;
     $productArray['weight'] = $value->product->weight;
     $productArray['image strapline'] = $value->product->starpline;
     $productArray['deposit'] = $value->product->deposit;
     $productArray['video'] = $value->product->video;
     $productArray['code'] = $value->product->code;
     $productArray['description'] = $value->product->description;
     $productArray['billingnotes'] = $value->product->billingnotes;
     $productArray['showit'] = $value->product->showit;
     $productArray['historyid'] = $value->product->historyid;

  }

    $productcount = count($productArray);
    $batchsize = 5;
    $count = 0;
    $start = $_GET['prev'];

    for( $id = $start; $id<$productcount; $id++ ) {
      global $wpdb;

      $last = $id; 
      $percent  = (floatval($id) / floatval($productcount - 1)) * 100;
      $count++;
      if($count == $batchsize) {
        printf("{\"last\": \"%s\", \"end\": false, \"percent\": \"%.2f\"}", $last, $percent);
        exit();
      }
       if($id == $productcount-1) {
      printf("{\"last\": \"%s\", \"end\": true, \"percent\": \"%.2f\"}", $last, $percent);
      exit();
    }
     
    }
   

  }
  
  public static function pageSlurp() {
    require_once(SHOPROCKET_PATH . "/models/Pest.php");
    require_once(SHOPROCKET_PATH . "/models/PestJSON.php");
    
    $page_id = ShoprocketCommon::postVal('page_id');
    $page = get_page($page_id);
    $slurp_url = get_permalink($page->ID);
    $html = false;
    $job_id = $slurp_url;
    
    if(wp_update_post(array('ID' => $page->ID, 'post_status' => 'publish'))) {
      $access_key = ShoprocketSetting::getValue('mijireh_access_key');
      $rest = new PestJSON(MIJIREH_CHECKOUT);
      $rest->setupAuth($access_key, '');
      $data = array(
        'url' => $slurp_url,
        'page_id' => $page->ID,
        'return_url' => add_query_arg('task', 'mijireh_page_slurp', $slurp_url)
      );
      
      try {
        $response = $rest->post('/api/1/slurps', $data);
        $job_id = $response['job_id'];
      }
      catch(Pest_Unauthorized $e) {
        header('Bad Request', true, 400);
        die();
      }
    }
    else {
      $job_id = 'did not update post successfully';
    }
    
    echo $job_id;
    die;
  }
  
  public static function dismissMijirehNotice() {
    ShoprocketSetting::setValue('mijireh_notice', 1);
  }
  
}