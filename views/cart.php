<?php 

$items = ShoprocketProduct::getProducts();
//$shippingMethods = ShoprocketSession::get('ShoprocketCart')->getShippingMethods();
//$shipping = ShoprocketSession::get('ShoprocketCart')->getShippingCost();
//$promotion = ShoprocketSession::get('ShoprocketPromotion');
$product = new ShoprocketProduct();
//$subtotal = ShoprocketSession::get('ShoprocketCart')->getSubTotal();
//$discountAmount = ShoprocketSession::get('ShoprocketCart')->getDiscountAmount();
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

  <script type="text/javascript" src="<?php echo SHOPROCKET_URL."/js/srv2.js"?>"></script>
<input type="hidden" name="sr-scanpage" id="sr-scanpage" value="0">
<input type="hidden" name="sr-companyid" id="sr-companyid" value="<?php echo ShoprocketSetting::getValue('companyid') ?>">
 <ul>   <?php foreach($items as $itemIndex => $item): ?>
     <li>
<div class="sr-block" sr-meta="" sr-show-max="5">
            <!-- Item #1 -->
            <div class="sitem">
              <!-- Don't forget the class "onethree-left" and "onethree-right" -->
              <div class="onethree-left">
                <!-- Image -->
                <a href=""><img src="<?php  echo ShoprocketProduct::getProductImageURL($item->id) ?>" alt=""/></a>
              </div>
              <div class="onethree-right">
                <!-- Title -->  <strong>Price:</strong>
                 <a href="echo $item->slug"><?php  echo $item->name ?></a>
                <!-- Para -->
                <p><?php  echo $item->strapline ?></p>
                <!-- Price -->
                <p class="bold"><?php echo $item->price ?></p>
              </div>
              <div class="clearfix"></div>
            </div>

              
</div> </li>        
    <?php endforeach; ?>  </ul>

<?php endif; ?>

