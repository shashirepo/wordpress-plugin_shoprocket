<?php
class ShoprocketExporter {
  
  public static function exportOrders($startDate, $endDate) {
    global $wpdb;
    $start = date('Y-m-d 00:00:00', strtotime($startDate));
    $end = date('Y-m-d 00:00:00', strtotime($endDate . ' + 1 day'));
    
    $orders = ShoprocketCommon::getTableName('orders');
    $items = ShoprocketCommon::getTableName('order_items');
    
    $orderHeaders = array(
      'id' => __('Order ID', 'shoprocket'),
      'trans_id' => __('Order Number', 'shoprocket'),
      'ordered_on' => __('Date', 'shoprocket'),
      'bill_first_name' => __('Billing First Name', 'shoprocket'),
      'bill_last_name' => __('Billing Last Name', 'shoprocket'),
      'bill_address' => __('Billing Address', 'shoprocket'),
      'bill_address2' => __('Billing Address 2', 'shoprocket'),
      'bill_city' => __('Billing City', 'shoprocket'),
      'bill_state' => __('Billing State', 'shoprocket'),
      'bill_country' => __('Billing Country', 'shoprocket'),
      'bill_zip' => __('Billing Zip Code', 'shoprocket'),
      'ship_first_name' => __('Shipping First Name', 'shoprocket'),
      'ship_last_name' => __('Shipping Last Name', 'shoprocket'),
      'ship_address' => __('Shipping Address', 'shoprocket'),
      'ship_address2' => __('Shipping Address 2', 'shoprocket'),
      'ship_city' => __('Shipping City', 'shoprocket'),
      'ship_state' => __('Shipping State', 'shoprocket'),
      'ship_country' => __('Shipping Country', 'shoprocket'),
      'ship_zip' => __('Shipping Zip Code', 'shoprocket'),
      'phone' => __('Phone', 'shoprocket'),
      'email' => __('Email', 'shoprocket'),
      'coupon' => __('Coupon', 'shoprocket'),
      'discount_amount' => __('Discount Amount', 'shoprocket'),
      'shipping' => __('Shipping Cost', 'shoprocket'),
      'subtotal' => __('Subtotal', 'shoprocket'),
      'tax' => __('Tax', 'shoprocket'),
      'total' => __('Total', 'shoprocket'),
      'ip' => __('IP Address', 'shoprocket'),
      'shipping_method' => __('Delivery Method', 'shoprocket'),
      'status' => __('Order Status', 'shoprocket')
    );
    
    $orderColHeaders = implode(',', $orderHeaders);
    $orderColSql = implode(',', array_keys($orderHeaders));
    $out  = $orderColHeaders . ",Form Data,Product Slug,Description,Quantity,Product Price,Form ID\n";
    
    $sql = "SELECT $orderColSql from $orders where ordered_on >= %s AND ordered_on < %s AND status != %s order by ordered_on";
    $sql = $wpdb->prepare($sql, $start, $end, 'checkout_pending');
    ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] SQL: $sql");
    $selectedOrders = $wpdb->get_results($sql, ARRAY_A);
    
    foreach($selectedOrders as $o) {
      $itemRowPrefix = '"' . $o['id'] . '","' . $o['trans_id'] . '",' . str_repeat(',', count($o)-3);
      $orderId = $o['id'];
      $sql = "SELECT form_entry_ids, item_number, description, quantity, product_price FROM $items where order_id = $orderId";
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Item query: $sql");
      $selectedItems = $wpdb->get_results($sql, ARRAY_A);
      $out .= '"' . implode('","', $o) . '"';
      $printItemRowPrefix = false;
      if(!empty($selectedItems)) {
        foreach($selectedItems as $i) {
          if($printItemRowPrefix) {
            $out .= $itemRowPrefix;
          }

          if($i['form_entry_ids'] && false){
            $i['form_id'] = $i['form_entry_ids'];
            $GReader = new ShoprocketGravityReader();
            $i['form_entry_ids'] = $GReader->displayGravityForm($i['form_entry_ids'],true);
            $i['form_entry_ids'] = str_replace("\"","''",$i['form_entry_ids']);
          }

          $i['description'] = str_replace(","," -",$i['description']);

          $out .= ',"' . implode('","', $i) . '"';
          $out .= "\n";
          $printItemRowPrefix = true;
        }
      }
      else {
        $out .= "\n";
      }
      
    }
    
    ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Report\n$out");
    return $out;
  }
  
}