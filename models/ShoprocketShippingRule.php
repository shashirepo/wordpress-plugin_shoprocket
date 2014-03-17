<?php
class ShoprocketShippingRule extends ShoprocketModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = ShoprocketCommon::getTableName('shipping_rules');
    parent::__construct($id);
  }
  
}