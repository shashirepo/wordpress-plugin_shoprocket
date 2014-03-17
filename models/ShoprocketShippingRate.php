<?php
class ShoprocketShippingRate extends ShoprocketModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = ShoprocketCommon::getTableName('shipping_rates');
    parent::__construct($id);
  }
  
}