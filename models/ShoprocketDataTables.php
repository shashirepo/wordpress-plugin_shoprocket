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
  	$products = $product->getProducts($where, $order, $limit);
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
  
  
}