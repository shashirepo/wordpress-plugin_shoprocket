<?php
Class ShoprocketDataTables {
  
  public static function sortProductSearch($a, $b) {
    if(isset($_GET['iSortCol_0'])){
  		for($i=0; $i<intval($_GET['iSortingCols']); $i++){
  			if(isset($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])]) && $_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true"){
  			  if($_GET['iSortCol_' . $i] == 0) {
  			    $value = 'name';
  			  }
  			  elseif($_GET['iSortCol_' . $i] == 1) {
  			    $value = 'quantity';
  			  }
  			  else {
  			    $value = 'sales_amount';
  			  }
  			  if($_GET['sSortDir_0'] == 'asc') {
  			    $result = strnatcmp($a[$value], $b[$value]);
  			  }
  				else {
  				  $result = strnatcmp($b[$value], $a[$value]);
  				}
  			}
  		}
  	}
    return $result;
  }
  
  public static function productsSearch() {
    $where = "";
  	if(isset($_GET['sSearch']) && $_GET['sSearch'] != ""){
  		$where = $_GET['sSearch'];
  	}
  	
    $products = self::productSalesForMonth();
    foreach($products as $k => $p) {
      if(!preg_match("/$where/i", $p['name']) && !preg_match("/$where/i", $p['quantity']) && !preg_match("/$where/i", $p['sales_amount'])) {
        unset($products[$k]);
      }
    }
    
    return $products;
  }
  
  public static function dashboardProductsTable() {
    
    $iFilteredTotal = self::productsSearch();
  	
  	$data = array();
  	$products = self::productSalesForMonth();
  	$productsSearch = self::productsSearch();
  	
  	uasort($productsSearch, array('ShoprocketDataTables', 'sortProductSearch'));
  	
  	if(isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1'){
  		$limit = array_slice($productsSearch, $_GET['iDisplayStart'], $_GET['iDisplayLength']);
  	}
  	
  	foreach($limit as $p) {
  	  $data[] = array(
    	  $p['name'],
    	  $p['quantity'],
    	  ShoprocketCommon::currency($p['sales_amount'])
    	);
  	}
  	$array = array(
  	 'sEcho' => $_GET['sEcho'],
  	 'iTotalRecords' => count($products),
  	 'iTotalDisplayRecords' => count($productsSearch),
  	 'aaData' => $data
  	);
  	echo json_encode($array);
  	die();
  }
  
  public static function SyncTable() {
    $columns = array(
      'id',
      'slug',
      'name',
      'options_1',
      'options_2'
    );
    
    if (isset($_GET['iSortCol_0'])){
      $sortingColumns = array(
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
      );
      $_GET['iSortCol_0'] = $sortingColumns[$_GET['iSortCol_0']];
    }
    
    $indexColumn = "id";
    $tableName = ShoprocketCommon::getTableName('products');
    
    $where = self::dataTablesWhere($columns);
  	$limit = self::dataTablesLimit() == '' ? null : self::dataTablesLimit();
  	$order = self::dataTablesOrder($columns);
  	
    $iTotal = self::totalRows($indexColumn, $tableName);
    $iFilteredTotal = self::filteredRows($indexColumn, $tableName, $where);
  	
  	$data = array();
  	$product = new ShoprocketProduct();
  	$products = $product->getModels($where, $order, $limit);
  	$save = false;
  	$ikeyList = array();
    foreach($products as $p) {
      $p->insertSyncData();
      $combos = $p->getAllOptionCombinations();
      if(count($combos)) {
        foreach($combos as $c) {
          $k = $p->getSyncKey($c);
          $ikeyList[] = $k;
          if($save) { $p->updateSyncFromPost($k); }
          $data[] = array(
            $p->isSyncTracked($k),
            $p->slug,
            $p->name,
            $c,
            $p->getSyncCount($k),
            $k
          );
        }
      }
      else {
        $k = $p->getSyncKey();
        $ikeyList[] = $k;
        if($save) { $p->updateSyncFromPost($k); }
        $data[] = array(
          $p->isSyncTracked($k),
          $p->slug,
          $p->name,
          $c='',
          $p->getSyncCount($k),
          $k
        );
      }
    }
  
    if($save) { $p->pruneSync($ikeyList); }
  	
  	$array = array(
  	 'sEcho' => $_GET['sEcho'],
  	 'iTotalRecords' => $iTotal[0],
  	 'iTotalDisplayRecords' => $iFilteredTotal[0],
  	 'aaData' => $data
  	);
  	echo json_encode($array);
  	die();
  }
  
  public static function accountsTable() {
    $columns = array(
      'a.id', 
      'a.first_name', 
      'a.last_name',
      'a.username', 
      'a.email',
      'a.notes',
      's.subscription_plan_name',
      's.feature_level',
      's.active_until',
      "concat_ws(' ', a.first_name,a.last_name)"
    );
    $indexColumn = "DISTINCT a.id";
    $tableName = ShoprocketCommon::getTableName('accounts') . ' as a, ' . ShoprocketCommon::getTableName('account_subscriptions') . ' as s';
    
    if (isset($_GET['iSortCol_0'])){
      $sortingColumns = array(
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 4,
        4 => 6,
        5 => 7,
        6 => 8,
        7 => 9
      );
      $_GET['iSortCol_0'] = $sortingColumns[$_GET['iSortCol_0']];
    }
    
    $where = self::dataTablesWhere($columns) == '' ? 'WHERE s.account_id = a.id' : self::dataTablesWhere($columns) . ' AND s.account_id = a.id ';
  	$limit = self::dataTablesLimit() == '' ? null : self::dataTablesLimit();
  	$order = self::dataTablesOrder($columns);
    
    $iTotal = self::totalRows($indexColumn, $tableName, 'WHERE s.account_id = a.id');
    $iFilteredTotal = self::filteredRows($indexColumn, $tableName, $where);
  	$data = array();
  	$account = new ShoprocketAccount();
  	$accounts = $account->getModels($where, $order, $limit, $tableName, $indexColumn);
  	foreach($accounts as $a) {
      $planName = 'No Active Subscription';
      $featureLevel = 'No Access';
      $activeUntil = 'Expired';
      if($sub = $a->getCurrentAccountSubscription(true)) {
        $planName = $sub->subscriptionPlanName;
        $featureLevel = $sub->isActive() ? $sub->featureLevel : 'No Access - Expired';
        $activeUntil = $sub->isActive() ? date(get_option('date_format'), strtotime($sub->activeUntil)) : 'No Access';
        $activeUntil = ($sub->lifetime == 1) ? "Lifetime" : $activeUntil;
        $type = 'Manual';
        if($sub->isPayPalSubscription()) {
          $type = 'PayPal';
        }
        elseif($sub->isSpreedlySubscription()) {
          $type = 'Spreedly';
        }
      }
      else {
        $planName = 'No plan available';
        $featureLevel = 'No Feature Level';
        $activeUntil = 'No Access';
        $type = 'None';
      }
  	  
  	  $data[] = array(
  	    $a->id, 
  	    $a->first_name . ' ' . $a->last_name,
  	    $a->username,
  	    $a->email,
  	    $planName,
  	    $featureLevel,
  	    $activeUntil,
  	    $type,
  	    $a->notes,
  	    $a->getOrderIdLink()
  	  );
  	}

  	$array = array(
  	 'sEcho' => $_GET['sEcho'],
  	 'iTotalRecords' => $iTotal[0],
  	 'iTotalDisplayRecords' => $iFilteredTotal[0],
  	 'aaData' => $data
  	);
  	echo json_encode($array);
  	die();
  }
  
  public static function promotionsTable() {
    $columns = array(
      'id', 
      'name', 
      'code', 
      'amount', 
      'min_order', 
      'enable', 
      'effective_from', 
      'effective_to', 
      'redemptions', 
      'apply_to'
    );
    $indexColumn = "id";
    $tableName = ShoprocketCommon::getTableName('promotions');
    if (isset($_GET['iSortCol_0'])){
      $sortingColumns = array(
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 8,
        8 => 9
      );
      $_GET['iSortCol_0'] = $sortingColumns[$_GET['iSortCol_0']];
    }
    $where = self::dataTablesWhere($columns);
  	$limit = self::dataTablesLimit() == '' ? null : self::dataTablesLimit();
  	$order = self::dataTablesOrder($columns);
    
    $iTotal = self::totalRows($indexColumn, $tableName);
    $iFilteredTotal = self::filteredRows($indexColumn, $tableName, $where);
  	
  	$data = array();
  	$promotion = new ShoprocketPromotion();
  	$promotions = $promotion->getModels($where, $order, $limit);
  	//ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] " . print_r($promotions));
  	foreach($promotions as $p) {
  	  $data[] = array(
  	    $p->id, 
  	    $p->name, 
  	    $p->getCodeAt(), 
  	    $p->getAmountDescription(), 
  	    $p->getMinOrderDescription(), 
  	    $p->enable == 1 ? 'Yes' : 'No',
  	    $p->effectiveDates(),
  	    ($p->redemptions < 1) ? __('Never', 'shoprocket') : ( ($p->redemptions == 1) ? $p->redemptions . ' ' . __('time', 'shoprocket') : $p->redemptions . ' ' . __('times', 'shoprocket')),
  	    ($p->apply_to == 'products') ? __("Products", 'shoprocket') : ( ($p->apply_to == 'shipping') ? __("Shipping", 'shoprocket') : ( ($p->apply_to == 'subtotal') ? __("Subtotal", 'shoprocket') : __("Cart Total", 'shoprocket') ) )
  	  );
  	}
  	
  	$array = array(
  	 'sEcho' => $_GET['sEcho'],
  	 'iTotalRecords' => $iTotal[0],
  	 'iTotalDisplayRecords' => $iFilteredTotal[0],
  	 'aaData' => $data
  	);
  	echo json_encode($array);
  	die();
  }
  
  public static function ordersTable() {
    $columns = array( 'id', 'trans_id', 'bill_first_name', 'bill_last_name', 'total', 'ordered_on', 'shipping_method', 'status', 'email', 'notes', 'authorization', "concat_ws(' ', bill_first_name,bill_last_name)");
    $indexColumn = "id";
    $tableName = ShoprocketCommon::getTableName('orders');

    $where = self::dataTablesWhere($columns, 'status', 'checkout_pending', '!=');
    if($where == ""){
      $where = "WHERE `status` != 'checkout_pending'";
    }
    else {
      $where .= " AND `status` != 'checkout_pending'";
    }
    $limit = self::dataTablesLimit() == '' ? null : self::dataTablesLimit();
    $orderBy = self::dataTablesOrder($columns);
    
    $iTotal = self::totalRows($indexColumn, $tableName);
    $iFilteredTotal = self::filteredRows($indexColumn, $tableName, $where);
    
    $data = array();
    $order = new ShoprocketOrder();
    $orders = $order->getOrderRows($where, $orderBy, $limit);
    foreach($orders as $o) {
      $data[] = array(
        $o->id,
        $o->trans_id,
        $o->bill_first_name,
        $o->bill_last_name,
        ShoprocketCommon::currency($o->total),
        date(get_option('date_format'), strtotime($o->ordered_on)),
        $o->shipping_method,
        $o->status,
        $o->notes
      );
    }
    
    $array = array(
      'sEcho' => $_GET['sEcho'],
      'iTotalRecords' => $iTotal[0],
      'iTotalDisplayRecords' => $iFilteredTotal[0],
      'aaData' => $data
    );
    echo json_encode($array);
    die();
  }
  
  public static function productsTable() {
    $columns = array( 'id', 'name', 'price');
    $indexColumn = "id";
    $tableName = ShoprocketCommon::getTableName('products');
    
    $where = self::dataTablesWhere($columns);
  	$limit = self::dataTablesLimit() == '' ? null : self::dataTablesLimit();
  	$order = self::dataTablesOrder($columns);
    
    if($where == null) {
      $where = "where showit = 1";
    }
    else {
      $where .= " AND showit = 1";
    }
    
    $iTotal = self::totalRows($indexColumn, $tableName, $where);
    $iFilteredTotal = self::filteredRows($indexColumn, $tableName, $where);
  	
  	$data = array();
  	$product = new ShoprocketProduct();
  	$products = $product->getNonSubscriptionProducts($where, $order, $limit);
  	foreach($products as $p) {
  	  $gfTitles = self::gfData();
  	  if($p->gravityFormId > 0 && isset($gfTitles) && isset($gfTitles[$p->gravityFormId])) {
         $gfTitles = '<br/><em>Linked To Gravity Form: ' . $gfTitles[$p->gravityFormId] . '</em>';
      }
      else {
        $gfTitles = '';
      }
  	  $data[] = array(
  	    $p->id,
  	    $p->slug,
  	    $p->name . $gfTitles,
  	    ShoprocketCommon::currency($p->price),
  	    $p->taxable? ' Yes' : 'No',
  	    $p->shipped? ' Yes' : 'No'
  	  );
  	}  
  	$array = array(
  	 'sEcho' => $_GET['sEcho'],
  	 'iTotalRecords' => $iTotal[0],
  	 'iTotalDisplayRecords' => $iFilteredTotal[0],
  	 'aaData' => $data
  	);
  	echo json_encode($array);
  	die();
  }
  
  public static function spreedlyTable() {
    $columns = array( 'id', 'slug', 'name', 'price', 'taxable', 'shipped' );
    $indexColumn = "id";
    $tableName = ShoprocketCommon::getTableName('products');
    
    $where = self::dataTablesWhere($columns);
  	$limit = self::dataTablesLimit() == '' ? null : self::dataTablesLimit();
  	$order = self::dataTablesOrder($columns);
    
    if($where == null) {
      $where = "where spreedly_subscription_id > 0";
    }
    else {
      $where .= " AND spreedly_subscription_id > 0";
    }
    
    $iTotal = self::totalRows($indexColumn, $tableName, $where);
    $iFilteredTotal = self::filteredRows($indexColumn, $tableName, $where);
  	
  	$data = array();
  	$spreedly = new ShoprocketProduct();
  	$spreedlys = $spreedly->getSpreedlyProducts($where, $order, $limit);
  	foreach($spreedlys as $s) {
  	  $gfTitles = self::gfData();
  	  if($s->gravityFormId > 0 && isset($gfTitles) && isset($gfTitles[$s->gravityFormId])) {
         $gfTitles = '<br/><em>Linked To Gravity Form: ' . $gfTitles[$s->gravityFormId] . '</em>';
      }
      else {
        $gfTitles = '';
      }
  	  $data[] = array(
  	    $s->id,
  	    $s->slug,
  	    $s->name . $gfTitles,
  	    $s->getPriceDescription(),
  	    $s->taxable? ' Yes' : 'No',
  	    $s->shipped? ' Yes' : 'No'
  	  );
  	}
  	$array = array(
  	 'sEcho' => $_GET['sEcho'],
  	 'iTotalRecords' => $iTotal[0],
  	 'iTotalDisplayRecords' => $iFilteredTotal[0],
  	 'aaData' => $data
  	);
  	echo json_encode($array);
  	die();
  }
  
  public static function paypalSubscriptionsTable() {
    $columns = array( 'id', 'slug', 'name', 'feature_level', 'setup_fee', 'price', 'billing_cycles', 'offer_trial', 'start_recurring_number', 'start_recurring_unit' );
    $indexColumn = "id";
    $tableName = ShoprocketCommon::getTableName('products');
    
    $where = self::dataTablesWhere($columns);
  	$limit = self::dataTablesLimit() == '' ? null : self::dataTablesLimit();
  	$order = self::dataTablesOrder($columns);
    
    if($where == null) {
      $where = "WHERE is_paypal_subscription>0";
    }
    else {
      $where .= " AND is_paypal_subscription>0";
    }

    $iTotal = self::totalRows($indexColumn, $tableName, $where);
    $iFilteredTotal = self::filteredRows($indexColumn, $tableName, $where);
  	
  	$data = array();
  	$subscription = new ShoprocketPayPalSubscription();
  	$subscriptions = $subscription->getModels($where, $order, $limit);
  	foreach($subscriptions as $s) {
  	  $gfTitles = self::gfData();
  	  if($s->gravityFormId > 0 && isset($gfTitles) && isset($gfTitles[$s->gravityFormId])) {
         $gfTitles = '<br/><em>Linked To Gravity Form: ' . $gfTitles[$s->gravityFormId] . '</em>';
      }
      else {
        $gfTitles = '';
      }
  	  $data[] = array(
  	    $s->id,
  	    $s->slug,
  	    $s->name . $gfTitles,
  	    $s->featureLevel,
  	    ShoprocketCommon::currency($s->setupFee),
  	    $s->getPriceDescription(false),
  	    $s->getBillingCycleDescription(),
  	    $s->getTrialPriceDescription(),
  	    $s->getStartRecurringDescription()
  	  );
  	}
  	ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] " . json_encode($data));
  	$array = array(
  	 'sEcho' => $_GET['sEcho'],
  	 'iTotalRecords' => $iTotal[0],
  	 'iTotalDisplayRecords' => $iFilteredTotal[0],
  	 'aaData' => $data
  	);
  	echo json_encode($array);
  	die();
  }
  public static function gfData() {
    global $wpdb;
    $gfTitles = array();
    if(FALSE && class_exists('RGFormsModel')) {
      require_once(SHOPROCKET_PATH . "/pro/models/ShoprocketGravityReader.php");
      $forms = ShoprocketCommon::getTableName('rg_form', '');
      $sql = "SELECT id, title from $forms where is_active=1 order by title";
      $results = $wpdb->get_results($sql);
      foreach($results as $r) {
        $gfTitles[$r->id] = $r->title;
      }
    }
    return $gfTitles;
  }   
  
  public static function totalRows($indexColumn, $tableName, $where=null) {
    global $wpdb;
    $sql = "
    	SELECT COUNT(" . $indexColumn . ")
    	FROM $tableName
    	$where
    ";
    $sql = $wpdb->get_results($sql, ARRAY_N);
    return $sql[0];
  }
  
  public static function filteredRows($indexColumn, $tableName, $where) {
    global $wpdb;
    $sqlTotal = "
      SELECT COUNT(" . $indexColumn . ")
    	FROM $tableName
    	$where
    ";
    $sqlTotal = $wpdb->get_results($sqlTotal, ARRAY_N);
    return $sqlTotal;
  }
  
  public static function dataTablesWhere($columns) {
    $where = "";
  	if($_GET['sSearch'] != ""){
  		$where = "WHERE (";
  		for ($i=0; $i<count($columns); $i++){
  			$where .= $columns[$i] . " LIKE '%" . mysql_real_escape_string(trim($_GET['sSearch'])) . "%' OR ";
  		}
  		$where = substr_replace($where, "", -3) . ')';
  	}

  	for($i=0; $i<count($columns); $i++){
  		if(isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != ''){
  			if($where == ""){
  				$where = "WHERE ";
  			}
  			else {
  				$where .= " AND ";
  			}
  			$where .= $columns[$i] . " LIKE '%" . mysql_real_escape_string(trim($_GET['sSearch_' . $i])) . "%' ";
  		}
  	}
    return $where;
  }
  
  public static function dataTablesLimit() {
    $limit = "";
  	if(isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1'){
  		$limit = mysql_real_escape_string($_GET['iDisplayStart']) . ", " . mysql_real_escape_string($_GET['iDisplayLength']);
  	}
    return $limit;
  }
  
  public static function dataTablesOrder($columns) {
    if(isset($_GET['iSortCol_0'])){
  		$order = "ORDER BY  ";
  		for($i=0; $i<intval($_GET['iSortingCols']); $i++){
  			if(isset($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])]) && $_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true"){
  				$order .= $columns[intval($_GET['iSortCol_' . $i])] . "
  				 	" . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
  			}
  		}

  		$order = substr_replace($order, "", -2);
  		if($order == "ORDER BY"){
  			$order = "";
  		}
  	}
  	return $order;
  }
  
  public static function productSalesForMonth() {
    $data = array();

    foreach(self::getSalesForMonth() as $order_items) {
      if(isset($data[$order_items->product_id])) {
        $data[$order_items->product_id]['quantity'] = $data[$order_items->product_id]['quantity'] + $order_items->quantity;
        $data[$order_items->product_id]['sales_amount'] = ($order_items->product_price * $order_items->quantity) + $data[$order_items->product_id]['sales_amount'];
      }
      else {
        $data[$order_items->product_id] = array(
          'quantity' => $order_items->quantity, 
          'name' => $order_items->description,
          'sales_amount' => $order_items->product_price * $order_items->quantity
        );
      }
    }
    return $data;
  }

  public static function totalSalesForMonth($data) {
    $results = array();
    foreach($data as $d) {
      if(isset($results['total_sales']['total_amount'])) {
        $results['total_sales']['total_amount'] = $d['sales_amount'] + $results['total_sales']['total_amount'];
      }
      else {
        $results['total_sales']['total_amount'] = $d['sales_amount'];
      }
      if(isset($results['total_sales']['total_quantity'])) {
        $results['total_sales']['total_quantity'] = $d['quantity'] + $results['total_sales']['total_quantity'];
      }
      else {
        $results['total_sales']['total_quantity'] = $d['quantity'];
      }
    }
    return $results;
  }

  public static function getSalesForMonth() {
    $thisMonth = ShoprocketCommon::localTs();
    $year =  date('Y', "$thisMonth");
    $month =  date('n', "$thisMonth");
    $orders = ShoprocketCommon::getTableName('orders');
    $orderItems = ShoprocketCommon::getTableName('order_items');
    $products = ShoprocketCommon::getTableName('products');
    $start = date('Y-m-d 00:00:00', strtotime($month . '/1/' . $year));
    $end = date('Y-m-d 00:00:00', strtotime($month . '/1/' . $year . ' +1 month'));

    $sql = "SELECT 
        oi.id, 
        oi.description, 
        oi.product_id, 
        oi.product_price, 
        o.ordered_on,
        oi.quantity
      from 
        $products as p,
        $orders as o, 
        $orderItems as oi 
      where
        oi.product_id = p.id and
        oi.order_id = o.id and
        o.ordered_on >= '$start' and 
        o.ordered_on < '$end'
    ";
    global $wpdb;
    $results = $wpdb->get_results($sql);
    return $results;
  }
  
}