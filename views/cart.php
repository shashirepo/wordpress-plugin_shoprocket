<?php 

$items = ShoprocketSession::get('ShoprocketCart')->getItems();
//$shippingMethods = ShoprocketSession::get('ShoprocketCart')->getShippingMethods();
$shipping = ShoprocketSession::get('ShoprocketCart')->getShippingCost();
$promotion = ShoprocketSession::get('ShoprocketPromotion');
$product = new ShoprocketProduct();
$subtotal = ShoprocketSession::get('ShoprocketCart')->getSubTotal();
$discountAmount = ShoprocketSession::get('ShoprocketCart')->getDiscountAmount();
$cartPage = get_page_by_path('store/cart');
$checkoutPage = get_page_by_path('store/checkout');
$setting = new ShoprocketSetting();


// Try to return buyers to the last page they were on when the click to continue shopping
if(ShoprocketSetting::getValue('continue_shopping') == 1){
  // force the last page to be store home
  $lastPage = ShoprocketSetting::getValue('store_url') ? ShoprocketSetting::getValue('store_url') : get_bloginfo('url');
  ShoprocketSession::set('ShoprocketLastPage', $lastPage);
}
else{
  if(isset($_SERVER['HTTP_REFERER']) && isset($_POST['task']) && $_POST['task'] == "addToCart"){
    $lastPage = $_SERVER['HTTP_REFERER'];
    ShoprocketSession::set('ShoprocketLastPage', $lastPage);
  }
  if(!ShoprocketSession::get('ShoprocketLastPage')) {
    // If the last page is not set, use the store url
    $lastPage = ShoprocketSetting::getValue('store_url') ? ShoprocketSetting::getValue('store_url') : get_bloginfo('url');
    ShoprocketSession::set('ShoprocketLastPage', $lastPage);
  }
}

$fullMode = true;
if(isset($data['mode']) && $data['mode'] == 'read') {
  $fullMode = false;
}

$tax = 0;
$taxData = false;
if(isset($data['tax'])){
  $taxData = $data['tax'];
}
if(ShoprocketSession::get('ShoprocketTax')){
  $taxData = ShoprocketSession::get('ShoprocketTax');
}

$cartImgPath = ShoprocketSetting::getValue('cart_images_url');
if($cartImgPath && stripos(strrev($cartImgPath), '/') !== 0) {
  $cartImgPath .= '/';
}
if($cartImgPath) {
  $continueShoppingImg = $cartImgPath . 'continue-shopping.png';
  $updateTotalImg = $cartImgPath . 'update-total.png';
  $calculateShippingImg = $cartImgPath . 'calculate-shipping.png';
  $applyCouponImg = $cartImgPath . 'apply-coupon.png';
}
?>
<?php if(ShoprocketSession::get('ShoprocketInvalidOptions')): ?>
  <div id="ShoprocketInvalidOptions" class="alert-message alert-error ShoprocketUnavailable">
    <h2 class="header"><?php _e( 'Invalid Product Options' , 'Shoprocket' ); ?></h2>
    <p><?php 
      echo ShoprocketSession::get('ShoprocketInvalidOptions');
      ShoprocketSession::drop('ShoprocketInvalidOptions');
    ?></p>
  </div>
<?php endif; ?>
<?php if(count($items)): ?>

<?php if(ShoprocketSession::get('ShoprocketSyncWarning') && $fullMode): ?>
  <?php 
    echo ShoprocketSession::get('ShoprocketSyncWarning');
    ShoprocketSession::drop('ShoprocketSyncWarning');
  ?>
<?php endif; ?>

<?php if(number_format(ShoprocketSetting::getValue('minimum_amount'), 2, '.', '') > number_format(ShoprocketSession::get('ShoprocketCart')->getSubTotal(), 2, '.', '') && ShoprocketSetting::getValue('minimum_cart_amount') == 1): ?>
  <div id="minAmountMessage" class="alert-message alert-error ShoprocketUnavailable">
    <?php echo (ShoprocketSetting::getValue('minimum_amount_label')) ? ShoprocketSetting::getValue('minimum_amount_label') : 'You have not yet reached the required minimum amount in order to checkout.' ?>
  </div>
<?php endif;?>

<?php if(ShoprocketSession::get('ShoprocketZipWarning')): ?>
  <div id="ShoprocketZipWarning" class="alert-message alert-error ShoprocketUnavailable">
    <h2 class="header"><?php _e( 'Please Provide Your Zip Code' , 'Shoprocket' ); ?></h2>
    <p><?php _e( 'Before you can checkout, please provide the zip code for where we will be shipping your order and click' , 'Shoprocket' ); ?> "<?php _e( 'Calculate Shipping' , 'Shoprocket' ); ?>".</p>
    <?php 
      ShoprocketSession::drop('ShoprocketZipWarning');
    ?>
    <input type="button" name="close" value="Ok" id="close" class="ShoprocketButtonSecondary modalClose" />
  </div>
<?php elseif(ShoprocketSession::get('ShoprocketShippingWarning')): ?>
  <div id="ShoprocketShippingWarning" class="alert-message alert-error ShoprocketUnavailable">
    <h2 class="header"><?php _e( 'No Shipping Service Selected' , 'Shoprocket' ); ?></h2>
    <p><?php _e( 'We cannot process your order because you have not selected a shipping method. If there are no shipping services available, we may not be able to ship to your location.' , 'Shoprocket' ); ?></p>
    <?php ShoprocketSession::drop('ShoprocketShippingWarning'); ?>
    <input type="button" name="close" value="Ok" id="close" class="ShoprocketButtonSecondary modalClose" />
  </div>
<?php elseif(ShoprocketSession::get('ShoprocketCustomFieldWarning')): ?>
  <div id="ShoprocketCustomFieldWarning" class="alert-message alert-error ShoprocketUnavailable">
    <h2 class="header"><?php _e( 'Custom Field Error' , 'Shoprocket' ); ?></h2>
    <p><?php _e( 'We cannot process your order because you have not filled out the custom field required for these products:' , 'Shoprocket' ); ?></p>
      <ul>
        <?php foreach(ShoprocketSession::get('ShoprocketCustomFieldWarning') as $customField): ?>
          <li><?php echo $customField; ?></li>
        <?php endforeach;?>
      </ul>
    <input type="button" name="close" value="Ok" id="close" class="ShoprocketButtonSecondary modalClose" />
  </div>
<?php endif; ?>

<?php if(ShoprocketSession::get('ShoprocketSubscriptionWarning')): ?>
  <div id="ShoprocketSubscriptionWarning" class="alert-message alert-error ShoprocketUnavailable">
    <h2 class="header"><?php _e( 'Too Many Subscriptions' , 'Shoprocket' ); ?></h2>
    <p><?php _e( 'Only one subscription may be purchased at a time.' , 'Shoprocket' ); ?></p>
    <?php 
      ShoprocketSession::drop('ShoprocketSubscriptionWarning');
    ?>
    <input type="button" name="close" value="Ok" id="close" class="ShoprocketButtonSecondary modalClose" />
  </div>
<?php endif; ?>

<?php 
  if($accountId = ShoprocketCommon::isLoggedIn()) {
    $account = new ShoprocketAccount($accountId);
    if($sub = $account->getCurrentAccountSubscription()) {
      if($sub->isPayPalSubscription() && ShoprocketSession::get('ShoprocketCart')->hasPayPalSubscriptions()) {
        ?>
        <p id="ShoprocketSubscriptionChangeNote"><?php _e( 'Your current subscription will be canceled when you purchase your new subscription.' , 'Shoprocket' ); ?></p>
        <?php
      }
    }
  } 
?>

<form id="ShoprocketCartForm" action="" method="post">
  <input type="hidden" name="task" value="updateCart" />
  <table id="viewCartTable">
    <colgroup>
      <col class="col1" />
      <col class="col2" />
      <col class="col3" />
      <col class="col4" />
    </colgroup>
  <thead>
    <tr>
      <th><?php _e('Product','Shoprocket') ?></th>
      <th class="Shoprocket-align-center"><?php _e( 'Quantity' , 'Shoprocket' ); ?></th>
      <th class="Shoprocket-align-right"><?php _e( 'Item Price' , 'Shoprocket' ); ?></th>
      <th class="Shoprocket-align-right"><?php _e( 'Item Total' , 'Shoprocket' ); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($items as $itemIndex => $item): ?>
      <?php 
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Item option info: " . $item->getOptionInfo());
        $product->load($item->getProductId());
        $price = $item->getProductPrice() * $item->getQuantity();
      ?>
      <tr>
        <td <?php if($item->hasAttachedForms()) { echo "class=\"noBottomBorder\""; } ?> >
          <?php if(ShoprocketSetting::getValue('display_item_number_cart')): ?>
            <span class="Shoprocket-cart-item-number"><?php echo $item->getItemNumber(); ?></span>
          <?php endif; ?>
          <?php #echo $item->getItemNumber(); ?>
          <?php if($item->getProductUrl() != '' && ShoprocketSetting::getValue('product_links_in_cart') == 1 && $fullMode): ?>
            <a class="product_url" href="<?php echo $item->getProductUrl(); ?>"><?php echo $item->getFullDisplayName(); ?></a>
          <?php else: ?>
            <?php echo $item->getFullDisplayName(); ?>
          <?php endif; ?>
          <?php echo $item->getCustomField($itemIndex, $fullMode); ?>
          <?php ShoprocketSession::drop('ShoprocketCustomFieldWarning'); ?>
        </td>
        <?php if($fullMode): ?>
          <?php
            $removeItemImg = Shoprocket_URL . '/images/remove-item.png';
            if($cartImgPath) {
              $removeItemImg = $cartImgPath . 'remove-item.png';
            }
          ?>
        <td <?php if($item->hasAttachedForms()) { echo "class=\"noBottomBorder\""; } ?>>
          
          <?php if($item->isSubscription() || $item->isMembershipProduct() || $product->is_user_price==1): ?>
            <span class="subscriptionOrMembership"><?php echo $item->getQuantity() ?></span>
          <?php else: ?>
            <input type="text" name="quantity[<?php echo $itemIndex ?>]" value="<?php echo $item->getQuantity() ?>" class="itemQuantity"/>
          <?php endif; ?>
          
          <?php $removeLink = get_permalink($cartPage->ID); ?>
          <?php $taskText = (strpos($removeLink, '?')) ? '&task=removeItem&' : '?task=removeItem&'; ?>
          <a href="<?php echo $removeLink . $taskText ?>itemIndex=<?php echo $itemIndex ?>" title="<?php _e('Remove item from cart', 'Shoprocket'); ?>"><img src="<?php echo $removeItemImg ?>" alt="<?php _e('Remove Item', 'Shoprocket'); ?>" /></a>
          
        </td>
        <?php else: ?>
          <td class="Shoprocket-align-center <?php if($item->hasAttachedForms()) { echo "noBottomBorder"; } ?>"><?php echo $item->getQuantity() ?></td>
        <?php endif; ?>
        <td class="Shoprocket-align-right <?php if($item->hasAttachedForms()) { echo "noBottomBorder"; } ?>"><?php echo $item->getProductPriceDescription(); ?></td>
        <td class="Shoprocket-align-right <?php if($item->hasAttachedForms()) { echo "noBottomBorder"; } ?>"><?php echo ShoprocketCommon::currency($price);?></td>
      </tr>
      <?php if($item->hasAttachedForms()): ?>
        <tr>
          <td colspan="4">
            <a href="#" class="showEntriesLink" rel="<?php echo 'entriesFor_' . $itemIndex ?>"><?php _e( 'Show Details' , 'Shoprocket' ); ?> <?php #echo count($item->getFormEntryIds()); ?></a>
            <div id="<?php echo 'entriesFor_' . $itemIndex ?>" class="showGfFormData" style="display: none;">
              <?php echo $item->showAttachedForms($fullMode); ?>
            </div>
          </td>
        </tr>
      <?php endif;?>      
    <?php endforeach; ?>
  
    <?php if(ShoprocketSession::get('ShoprocketCart')->requireShipping()): ?>
      
      
      <?php if(Shoprocket_PRO && ShoprocketSetting::getValue('use_live_rates')): ?>
        <?php $zipStyle = "style=''"; ?>
        
        <?php if($fullMode): ?>
          <?php if(ShoprocketSession::get('Shoprocket_shipping_zip')): ?>
            <?php $zipStyle = "style='display: none;'"; ?>
            <tr id="shipping_to_row">
              <th colspan="4" class="alignRight">
                <?php _e( 'Shipping to' , 'Shoprocket' ); ?> <?php echo ShoprocketSession::get('Shoprocket_shipping_zip'); ?> 
                <?php
                  if(ShoprocketSetting::getValue('international_sales')) {
                    echo ShoprocketSession::get('Shoprocket_shipping_country_code');
                  }
                ?>
                (<a href="#" id="change_shipping_zip_link"><?php _e('change', 'Shoprocket'); ?></a>)
                &nbsp;
                <?php
                  $liveRates = ShoprocketSession::get('ShoprocketCart')->getLiveRates();
                  $rates = $liveRates->getRates();
                  ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] LIVE RATES: " . print_r($rates, true));
                  $selectedRate = $liveRates->getSelected();
                  $shipping = ShoprocketSession::get('ShoprocketCart')->getShippingCost();
                ?>
                <select name="live_rates" id="live_rates">
                  <?php foreach($rates as $rate): ?>
                    <option value="<?php echo $rate->service ?>" <?php if($selectedRate->service == $rate->service) { echo 'selected="selected"'; } ?>>
                      <?php 
                        if($rate->rate !== false) {
                          echo "$rate->service: \$$rate->rate";
                        }
                        else {
                          echo "$rate->service";
                        }
                      ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </th>
            </tr>
          <?php endif; ?>
        
          <tr id="set_shipping_zip_row" <?php echo $zipStyle; ?>>
            <th colspan="4" class="alignRight"><?php _e( 'Enter Your Zip Code' , 'Shoprocket' ); ?>:
              <input type="text" name="shipping_zip" value="" id="shipping_zip" size="5" />
              
              <?php if(ShoprocketSetting::getValue('international_sales')): ?>
                <select name="shipping_country_code">
                  <?php
                    $customCountries = ShoprocketCommon::getCustomCountries();
                    foreach($customCountries as $code => $name) {
                      echo "<option value='$code'>$name</option>\n";
                    }
                  ?>
                </select>
              <?php else: ?>
                <input type="hidden" name="shipping_country_code" value="<?php echo ShoprocketCommon::getHomeCountryCode(); ?>" id="shipping_country_code">
              <?php endif; ?>
              
              <?php if($cartImgPath && ShoprocketCommon::urlIsLIve($calculateShippingImg)): ?>
                <input class="ShoprocketCalculateShippingButton" type="image" src="<?php echo $calculateShippingImg ?>" value="<?php _e( 'Calculate Shipping' , 'Shoprocket' ); ?>" name="calculateShipping" />
              <?php else: ?>
                <input type="submit" name="calculateShipping" value="<?php _e('Calculate Shipping', 'Shoprocket'); ?>" id="shipping_submit" class="ShoprocketCalculateShippingButton ShoprocketButtonSecondary" />
              <?php endif; ?>
            </th>
          </tr>
        <?php else:  // Cart in read mode ?>
          <tr>
            <th colspan="4" class="alignRight">
              <?php
                $liveRates = ShoprocketSession::get('ShoprocketCart')->getLiveRates();
                if($liveRates && ShoprocketSession::get('Shoprocket_shipping_zip') && ShoprocketSession::get('Shoprocket_shipping_country_code')) {
                  $selectedRate = $liveRates->getSelected();
                  echo __("Shipping to", "Shoprocket") . " " . ShoprocketSession::get('Shoprocket_shipping_zip') . " " . __("via","Shoprocket") . " " . $selectedRate->service;
                }
              ?>
            </th>
          </tr>
        <?php endif; // End cart in read mode ?>
        
      <?php  else: ?>
        <?php if(count($shippingMethods) > 1 && $fullMode): ?>
        <tr>
          <th colspan="4" class="alignRight"><?php _e( 'Shipping Method' , 'Shoprocket' ); ?>: &nbsp;
            <?php if(ShoprocketSetting::getValue('international_sales')): ?>
              <select name="shipping_country_code" id="shipping_country_code">
                <?php
                  $customCountries = ShoprocketCommon::getCustomCountries();
                  foreach($customCountries as $code => $name) {
                    $selected_country = '';
                    if($code == ShoprocketSession::get('ShoprocketShippingCountryCode')) {
                      $selected_country = ' selected="selected"';
                    }
                    echo "<option value='$code'$selected_country>$name</option>\n";
                  }
                ?>
              </select>
            <?php else: ?>
              <input type="hidden" name="shipping_country_code" value="<?php echo ShoprocketCommon::getHomeCountryCode(); ?>" id="shipping_country_code">
            <?php endif; ?>
            <select name="shipping_method_id" id="shipping_method_id">
              <?php foreach($shippingMethods as $name => $id): ?>
                <?php
                $method_class = 'methods-country ';
                $method = new ShoprocketShippingMethod($id);
                $methods = unserialize($method->countries);
                if(is_array($methods)) {
                  foreach($methods as $code => $country) {
                    $method_class .= $code . ' ';
                  }
                }
                if($id == 'select') {
                  $method_class = "select";
                }
                elseif($method_class == 'methods-country ') {
                  $method_class = 'all-countries';
                }
                ?>
              <option class="<?php echo trim($method_class); ?>" value="<?php echo $id ?>" <?php echo ($id == ShoprocketSession::get('ShoprocketCart')->getShippingMethodId())? 'selected' : ''; ?>><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </th>
        </tr>
        <?php elseif(!$fullMode): ?>
        <tr>
          <th colspan="4" class="alignRight"><?php _e( 'Shipping Method' , 'Shoprocket' ); ?>: 
            <?php 
              $method = new ShoprocketShippingMethod(ShoprocketSession::get('ShoprocketCart')->getShippingMethodId());
              echo $method->name;
            ?>
          </th>
        </tr>
        <?php endif; ?>
      <?php endif; ?>
    <?php endif; ?>

    <tr class="subtotal">
      <?php if($fullMode): ?>
      <td>&nbsp;</td>
      <td>
        <?php if($cartImgPath && ShoprocketCommon::urlIsLIve($updateTotalImg)): ?>
          <input class="ShoprocketUpdateTotalButton" type="image" src="<?php echo $updateTotalImg ?>" value="<?php _e( 'Update Total' , 'Shoprocket' ); ?>" name="updateCart"/>
        <?php else: ?>
          <input type="submit" name="updateCart" value="<?php _e( 'Update Total' , 'Shoprocket' ); ?>" class="ShoprocketUpdateTotalButton ShoprocketButtonSecondary" />
        <?php endif; ?>
      </td>
      <?php else: ?>
        <td colspan="2">&nbsp;</td>
      <?php endif; ?>
      <td class="alignRight strong"><?php _e( 'Subtotal' , 'Shoprocket' ); ?>:</td>
      <td class="strong Shoprocket-align-right"><?php echo ShoprocketCommon::currency($subtotal); ?></td>
    </tr>
    
    <?php if(ShoprocketSession::get('ShoprocketCart')->requireShipping()): ?>
    <tr class="shipping">
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td class="alignRight strong"><?php _e( 'Shipping' , 'Shoprocket' ); ?>:</td>
      <td class="strong Shoprocket-align-right"><?php echo ShoprocketCommon::currency($shipping) ?></td>
    </tr>
    <?php endif; ?>
    
    <?php if($promotion): ?>
      <tr class="coupon">
        <td colspan="3" class="alignRight strong"><?php _e( 'Coupon' , 'Shoprocket' ); ?> 
        <?php 
          if($promotion->name){ 
            echo "(" .$promotion->name .")"; 
          }
          else{
            echo "(" . ShoprocketSession::get('ShoprocketPromotionCode') . ")";
          }
        ?>:</td>
        <td class="strong Shoprocket-align-right">-&nbsp;<?php $promotionDiscountAmount = ShoprocketSession::get('ShoprocketCart')->getDiscountAmount();
         echo ShoprocketCommon::currency($promotionDiscountAmount); ?></td>
      </tr>
    <?php endif; ?>
    

      <tr class="total">
        <?php if(ShoprocketSession::get('ShoprocketCart')->getNonSubscriptionAmount() > 0): ?>
        <td class="alignRight" colspan="2">
          <?php if($fullMode && ShoprocketCommon::activePromotions()): ?>
            <p class="haveCoupon"><?php _e( 'Do you have a coupon?' , 'Shoprocket' ); ?></p>
          <?php if(ShoprocketSession::get('ShoprocketPromotionErrors')):
                $promoErrors = ShoprocketSession::get('ShoprocketPromotionErrors');
                    foreach($promoErrors as $type=>$error): ?>
                    <p class="promoMessage warning"><?php echo $error; ?></p>
              <?php endforeach;?>
              <?php ShoprocketSession::get('ShoprocketCart')->clearPromotion();
                  endif; ?>
            <div id="couponCode"><input type="text" name="couponCode" value="" /></div>
            <div id="updateCart">
              <?php if($cartImgPath && ShoprocketCommon::urlIsLIve($applyCouponImg)): ?>
                <input class="ShoprocketApplyCouponButton" type="image" src="<?php echo $applyCouponImg ?>" value="<?php _e( 'Apply Coupon' , 'Shoprocket' ); ?>" name="updateCart"/>
              <?php else: ?>
                <input type="submit" name="updateCart" value="<?php _e( 'Apply Coupon' , 'Shoprocket' ); ?>" class="ShoprocketApplyCouponButton ShoprocketButtonSecondary" />
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </td>
        <?php else: ?>
          <td colspan="2">&nbsp;</td>
        <?php endif; ?>
        <td class="alignRight strong ShoprocketCartTotalLabel"><span class="ajax-spin"><img src="<?php echo Shoprocket_URL; ?>/images/ajax-spin.gif" /></span> <?php _e( 'Total' , 'Shoprocket' ); ?>:</td>
        <td class="strong grand-total-amount Shoprocket-align-right">
          <?php 
            echo ShoprocketCommon::currency(ShoprocketSession::get('ShoprocketCart')->getGrandTotal() + $tax);
          ?>
        </td>
      </tr>
      </tbody>
  </table>
</form>

  <?php if($fullMode): ?>
    
  <div id="viewCartNav">
	<div id="continueShopping">
        <?php if($cartImgPath): ?>
          <a href="<?php echo ShoprocketSession::get('ShoprocketLastPage'); ?>" class="ShoprocketCartContinueShopping" ><img src="<?php echo $continueShoppingImg ?>" /></a>
        <?php else: ?>
          <a href="<?php echo ShoprocketSession::get('ShoprocketLastPage'); ?>" class="ShoprocketButtonSecondary ShoprocketCartContinueShopping" title="Continue Shopping"><?php _e( 'Continue Shopping' , 'Shoprocket' ); ?></a>
        <?php endif; ?>
	</div>

	
	  <?php	  
  	  // dont show checkout until terms are accepted (if necessary)
  	 if((ShoprocketSetting::getValue('require_terms') != 1) ||  
  	    (ShoprocketSetting::getValue('require_terms') == 1 && (isset($_POST['terms_acceptance']) || ShoprocketSession::get("terms_acceptance")=="accepted")) ) :  
  	    
  	    if(ShoprocketSetting::getValue('require_terms') == 1){
  	      ShoprocketSession::set("terms_acceptance","accepted",true);        
  	    }
  	    
  	?>
        <?php
          $checkoutImg = false;
          if($cartImgPath) {
            $checkoutImg = $cartImgPath . 'checkout.png';
          }
        ?>
        <?php
        if(number_format(ShoprocketSetting::getValue('minimum_amount'), 2, '.', '') > number_format(ShoprocketSession::get('ShoprocketCart')->getSubTotal(), 2, '.', '') && ShoprocketSetting::getValue('minimum_cart_amount') == 1): ?>
        <?php else: ?>
      <div id="checkoutShopping">
        <?php
        $checkoutUrl = ShoprocketSetting::getValue('auth_force_ssl') ? str_replace('http://', 'https://', get_permalink($checkoutPage->ID)) : get_permalink($checkoutPage->ID);
        ?>
        <?php if($checkoutImg): ?>
          <a id="ShoprocketCheckoutButton" href="<?php echo $checkoutUrl; ?>"><img src="<?php echo $checkoutImg ?>" /></a>
        <?php else: ?>
          <a id="ShoprocketCheckoutButton" href="<?php echo $checkoutUrl; ?>" class="ShoprocketButtonPrimary" title="Continue to Checkout"><?php _e( 'Checkout' , 'Shoprocket' ); ?></a>
        <?php endif; ?>
    	</div>
    	<?php endif; ?>
    <?php else: ?>
    <div id="ShoprocketCheckoutReplacementText">
        <?php echo ShoprocketSetting::getValue('cart_terms_replacement_text');  ?>
    </div>
    <?php endif; ?>
	
	
	   <?php  

    	if(Shoprocket_PRO && ShoprocketSetting::getValue('require_terms') == 1 && (!isset($_POST['terms_acceptance']) && ShoprocketSession::get("terms_acceptance")!="accepted") ){
    	    echo ShoprocketCommon::getView("pro/views/terms.php",array("location"=>"ShoprocketCartTOS"));
    	} 

    	 ?>
	
	</div>
	
	
  <?php endif; ?>
<?php else: ?>
  <div id="emptyCartMsg">
  <h3><?php _e('Your Cart Is Empty','Shoprocket'); ?></h3>
  <?php if($cartImgPath): ?>
    <a href="<?php echo ShoprocketSession::get('ShoprocketLastPage'); ?>" title="Continue Shopping" class="ShoprocketCartContinueShopping"><img alt="Continue Shopping" class="continueShoppingImg" src="<?php echo $continueShoppingImg ?>" /></a>
  <?php else: ?>
    <a href="<?php echo ShoprocketSession::get('ShoprocketLastPage'); ?>" class="ShoprocketButtonSecondary" title="Continue Shopping"><?php _e( 'Continue Shopping' , 'Shoprocket' ); ?></a>
  <?php endif; ?>
  </div>
  <?php
    if($promotion){
      ShoprocketSession::get('ShoprocketCart')->clearPromotion();
    }
    ShoprocketSession::drop("terms_acceptance");
  ?>
<?php endif; ?>


<script type="text/javascript" src="<?php echo SHOPROCKET_URL."/js/srv2.js"?>"></script>
<input type="hidden" name="sr-scanpage" id="sr-scanpage" value="0">
<input type="hidden" name="sr-companyid" id="sr-companyid" value="<?php echo ShoprocketSetting::getValue('companyid') ?>">
<div class="sr-block" sr-meta="" sr-show-max="5">
            <!-- Item #1 -->
            <div class="sitem hide">
              <!-- Don't forget the class "onethree-left" and "onethree-right" -->
              <div class="onethree-left">
                <!-- Image -->
                 <a href="[SLUG]"><img src="[IMAGESRC]/convert?h=150&w=180&fit=crop&quality=100" alt=""/></a>
              </div>
              <div class="onethree-right">
                <!-- Title -->
                 <a href="[SLUG]">[NAME]</a>
                <!-- Para -->
                <p>[STRAPLINE]</p>
                <!-- Price -->
                <p class="bold">[PRICE]</p>
              </div>
              <div class="clearfix"></div>
            </div>

              
</div>