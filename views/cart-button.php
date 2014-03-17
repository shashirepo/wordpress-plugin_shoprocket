<?php
  $setting = new ShoprocketSetting();
  $id = ShoprocketCommon::getButtonId($data['product']->id);
  $priceString = $data['price'];
  $isNumeric = false;
  if(is_numeric($priceString)){    
    $isNumeric = true;
  }
?>

    <?php if(isset($data['product']->name)) : ?>
       <span class="ShoprocketUserPrice">
       <img src="http://fc02.deviantart.net/fs70/f/2010/236/2/c/Supertux_Dock_Icon_by_Sarrel.png">
       <label> <?php echo $data['product']->name; ?>
    <?php endif; ?>

<?php if($data['gravity_form_id'] && Shoprocket_PRO && $data['showPrice'] != 'only'): ?>
  <?php echo do_shortcode("[gravityform id=" . $data['gravity_form_id'] . " ajax=false] "); ?>
<?php elseif($data['showPrice'] == 'only'): ?>
  
  <?php if($data['product']->isSubscription()): ?>
    
    <?php echo $data['product']->getPriceDescription(); ?>

  <?php else: ?>      
    <span class="ShoprocketPrice<?php echo $isNumeric ? '' : ' ShoprocketPriceDescription'; ?>">
      <?php if($isNumeric): ?>
        <span class="ShoprocketPriceLabel"><?php _e( 'Price' , 'Shoprocket' ); ?>: </span>
        <?php echo ShoprocketCommon::currency($priceString, true, true); ?>
      <?php else: ?>
        <?php echo $priceString; ?>
      <?php endif; ?>
    </span>
    
  <?php endif; ?>
  
<?php else: ?>
  
  <form id='cartButtonForm_<?php echo $id ?>' class="ShoprocketCartButton" method="post" action="<?php echo ShoprocketCommon::getPageLink('store/cart'); ?>" <?php echo $data['style']; ?>>
    <input type='hidden' name='task' id="task_<?php echo $id ?>" value='addToCart' />
    <input type='hidden' name='ShoprocketItemId' value='<?php echo $data['product']->id; ?>' />
    <input type='hidden' name='product_url' value='<?php echo ShoprocketCommon::getCurrentPageUrl(); ?>' />
    
    <?php if($data['showName'] == 'true'): ?> 
      <span class="ShoprocketProductName"><?php echo $data['product']->name; ?></span>
    <?php endif; ?>
    
    <?php if($data['showPrice'] == 'yes' && $data['is_user_price'] != 1): ?>
      <?php
      $css = '';
      if(strpos($data['quantity'],'user') !== FALSE && $data['is_user_price'] != 1 && $data['subscription'] == 0) {
        $css = ' ShoprocketPriceBlock';
      }
      ?>
      <span class="ShoprocketPrice<?php echo $isNumeric ? $css : ' ShoprocketPriceDescription'; ?>">
        <?php if($isNumeric): ?>
          <span class="ShoprocketPriceLabel"><?php _e( 'Price' , 'Shoprocket' ); ?>: </span>
          <?php echo ShoprocketCommon::currency($priceString, true, true); ?>
        <?php else: ?>
          <?php echo $priceString; ?>
        <?php endif; ?>
      </span>
    <?php endif; ?>

    
    <?php if($data['is_user_price'] == 1) : ?>
      <span class="ShoprocketUserPrice">
        <label for="ShoprocketUserPriceInput_<?php echo $id ?>"><?php echo (ShoprocketSetting::getValue('userPriceLabel')) ? ShoprocketSetting::getValue('userPriceLabel') : __( 'Enter an amount: ' ) ?> </label><?php echo ShoprocketCommon::currencySymbol('before'); ?><input id="ShoprocketUserPriceInput_<?php echo $id ?>" name="item_user_price" value="<?php echo str_replace(Shoprocket_CURRENCY_SYMBOL,"",$data['price']);?>" size="5" /><?php echo ShoprocketCommon::currencySymbol('after'); ?>
      </span>
    <?php endif; ?>

   

    
    <?php 
      if(strpos($data['quantity'],'user') !== FALSE && $data['is_user_price'] != 1 && $data['subscription'] == 0): 
        $quantityString = explode(":",$data['quantity']);
        if(isset($quantityString[1])){
          $defaultQuantity = (is_numeric($quantityString[1])) ? $quantityString[1] : 1;
        }
        else{
          $defaultQuantity = "";
        }
        
    ?>
      <span class="ShoprocketUserQuantity">
       <label for="ShoprocketUserQuantityInput_<?php echo $id; ?>"><?php echo (ShoprocketSetting::getValue('userQuantityLabel')) ? ShoprocketSetting::getValue('userQuantityLabel') : __( 'Quantity: ' ) ?> </label>
       <input id="ShoprocketUserQuantityInput_<?php echo $id; ?>" name="item_quantity" value="<?php echo $defaultQuantity; ?>" size="4">
      </span> 
    <?php elseif(is_numeric($data['quantity']) && $data['is_user_price'] != 1): ?>
       <input type="hidden" name="item_quantity" class="ShoprocketItemQuantityInput" value="<?php echo $data['quantity']; ?>">       
    <?php endif; ?>
      
      
    <?php if($data['product']->isAvailable()): ?>
      <?php echo $data['productOptions'] ?>
    
      <?php if($data['product']->recurring_interval > 0 && !Shoprocket_PRO): ?>
          <span class='ShoprocketProRequired'><a href='http://www.Shoprocket.com'><?php _e( 'Shoprocket Professional' , 'Shoprocket' ); ?></a> <?php _e( 'is required to sell subscriptions' , 'Shoprocket' ); ?></span>
      <?php else: ?>
        <?php if($data['addToCartPath']): ?> 
          <input type='image' value='<?php echo $data['buttonText'] ?>' src='<?php echo $data['addToCartPath'] ?>' class="purAddToCartImage ajax-button" name='addToCart_<?php echo $id ?>' id='addToCart_<?php echo $id ?>'/>
        <?php else: ?>
          <input type='submit' value='<?php echo $data['buttonText'] ?>' class='ShoprocketButtonPrimary purAddToCart ajax-button' name='addToCart_<?php echo $id ?>' id='addToCart_<?php echo $id ?>' />
        <?php endif; ?>
      <?php endif; ?>
    
    <?php else: ?>
      <span class='ShoprocketOutOfStock'><?php echo ShoprocketSetting::getValue('label_out_of_stock') ? ShoprocketSetting::getValue('label_out_of_stock') : __( 'Out of stock' , 'Shoprocket' ); ?></span>
    <?php endif; ?>
  </form>

<?php endif; ?>

<?php if($data['ajax'] == 'yes' || $data['ajax'] == 'true'): ?>
  <?php echo ShoprocketCommon::getView('views/ajax-cart-button-message.php', array('id' => $id, 'productName' => $data['product']->name));?>
<?php endif; ?>

<?php if(ShoprocketCommon::ShoprocketUserCan('products') && ShoprocketSetting::getValue('enable_edit_product_links')): ?>
  <div class='Shoprocket_edit_product_link'>
    <?php if($data['subscription'] == 0): ?>
      <a href='<?php echo admin_url(); ?>admin.php?page=Shoprocket-products&amp;task=edit&amp;id=<?php echo $id ?>'><?php _e( 'Edit this Product' , 'Shoprocket' ); ?></a>
    <?php elseif($data['subscription'] == 1): ?>
      <a href='<?php echo admin_url(); ?>admin.php?page=Shoprocket-paypal-subscriptions&amp;task=edit&amp;id=<?php echo $id ?>'><?php _e( 'Edit this Subscription' , 'Shoprocket' ); ?></a>
    <?php elseif($data['subscription'] == 2): ?>
      <a href='<?php echo admin_url(); ?>admin.php?page=Shoprocket-products&amp;task=edit&amp;id=<?php echo $id ?>'><?php _e( 'Edit this Subscription' , 'Shoprocket' ); ?></a>
    <?php endif; ?>
  </div>
<?php endif; ?>


<?php
$url = ShoprocketCommon::appendWurlQueryString('ShoprocketAjaxCartRequests');
if(ShoprocketCommon::isHttps()) {
  $url = preg_replace('/http[s]*:/', 'https:', $url);
}
else {
  $url = preg_replace('/http[s]*:/', 'http:', $url);
}
$product_name = str_replace("'", "\'", $data["product"]->name);
$product = array(
  'id' => $id,
  'name' => $product_name,
  'ajax' => $data['ajax'],
  'returnUrl' => ShoprocketCommon::getCurrentPageUrl(),
  'addingText' => __('Adding...' , 'Shoprocket')
);
$localized_data = array(
  'youHave' => __('You have', 'Shoprocket'),
  'inYourShoppingCart' => __('in your shopping cart', 'Shoprocket'),
  'ajaxurl' => $url,
);
$localized_data['products'][$id] = $product;

global $wp_scripts;
$data = array();
if(is_object($wp_scripts)) {
  $data = $wp_scripts->get_data('Shoprocket-library', 'data');
}
if(empty($data)) {
  wp_localize_script('Shoprocket-library', 'C66', $localized_data);
}
else {
  if(!is_array($data)) {
    $data = json_decode(str_replace('var C66 = ', '', substr($data, 0, -1)), true);
  }
  foreach($data['products'] as $product_id => $product) {
    $localized_data['products'][$product_id] = $product;
  }
  $wp_scripts->add_data('Shoprocket-library', 'data', '');
  wp_localize_script('Shoprocket-library', 'C66', $localized_data);
}
apply_filters('Shoprocket_filter_after_add_to_cart_button', true);

