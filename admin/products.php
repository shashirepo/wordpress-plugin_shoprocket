<?php
$product = new ShoprocketProduct();
$adminUrl = get_bloginfo('wpurl') . '/wp-admin/admin.php';
$errorMessage = false;

if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['Shoprocket-action'] == 'save product') {
  $_POST['product']['price'] = isset($_POST['product']['price']) ? ShoprocketCommon::convert_currency_to_number($_POST['product']['price']) : '';
  try {
    $product->handleFileUpload();
    $product->setData(ShoprocketCommon::postVal('product'));
    $product->save();
    $product->clear();
  }
  catch(ShoprocketException $e) {
    $errorCode = $e->getCode();
    if($errorCode == 66102) {
      // Product save failed
      $errors = $product->getErrors();
      $errorMessage = ShoprocketCommon::showErrors($errors, "<p><b>" . __("The product could not be saved for the following reasons","Shoprocket") . ":</b></p>");
    }
    elseif($errorCode == 66101) {
      // File upload failed
      $errors = $product->getErrors();
      $errorMessage = ShoprocketCommon::showErrors($errors, "<p><b>" . __("The file upload failed","Shoprocket") . ":</b></p>");
    }
    ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Product save failed ($errorCode): " . strip_tags($errorMessage));
  }
  
}
elseif(isset($_GET['task']) && $_GET['task'] == 'edit' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = ShoprocketCommon::getVal('id');
  $product->load($id);
}
elseif(isset($_GET['task']) && $_GET['task'] == 'delete' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = ShoprocketCommon::getVal('id');
  $product->load($id);
  $product->deleteMe();
  $product->clear();
}
elseif(isset($_GET['task']) && $_GET['task'] == 'xdownload' && isset($_GET['id']) && $_GET['id'] > 0) {
  // Load the product
  $id = ShoprocketCommon::getVal('id');
  $product->load($id);
  
  // Delete the download file
  $setting = new ShoprocketSetting();
  $dir = ShoprocketSetting::getValue('product_folder');
  $path = $dir . DIRECTORY_SEPARATOR . $product->download_path;
  unlink($path);
  
  // Clear the name of the download file from the object and database
  $product->download_path = '';
  $product->save();
}
$data['products'] = $product->getNonSubscriptionProducts('where id>0', null, '1');
$data['spreedly'] = $product->getSpreedlyProducts(null, null, '1');
?>

<?php if($errorMessage): ?>
<div style="margin: 30px 50px 10px 5px;"><?php echo $errorMessage ?></div>
<?php endif; ?>



<h2><?php _e('Shoprocket Products', 'Shoprocket'); ?></h2>

<form action="admin.php?page=shoprocket-products" method="post" enctype="multipart/form-data" id="products-form">
  <input type="hidden" name="Shoprocket_product_nonce" value="<?php echo wp_create_nonce('Shoprocket_product_nonce'); ?>" />
  <input type="hidden" name="Shoprocket-action" value="save product" />
  <input type="hidden" name="product[id]" value="<?php echo $product->id ?>" />
  <div id="widgets-left" style="margin-right: 50px;">
    <div id="available-widgets">
    
      <div class="widgets-holder-wrap">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3><?php _e( 'Product' , 'Shoprocket' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
            <ul>
              <li>
                <label class="long" for="product-name"><?php _e( 'Product name' , 'Shoprocket' ); ?>:</label>
                <input class="long" type="text" name='product[name]' id='product-name' value="<?php echo htmlspecialchars($product->name); ?>" />
              </li>
              <li>
                <label class="long" for='product-item_number'><?php _e( 'Slug' , 'Shoprocket' ); ?>:</label>
                <input type='text' name='product[slug]' id='product-item_number' value='<?php echo $product->slug; ?>' />
                <span class="label_desc"><?php _e( 'Unique Slug required.' , 'Shoprocket' ); ?></span>
              </li>
             
              
              <li class="native_price" <?php if($product->gravity_form_pricing == 1) echo 'style="display:none;"'; ?>>
                <label class="long" for="product-price" id="price_label"><?php _e( 'Price' , 'Shoprocket' ); ?>:</label>
                <?php echo ShoprocketCommon::currencySymbol('before'); ?> <input type='text' style="width: 75px;" id="product-price" name='product[price]' value='<?php echo $product->id > 0 ? ShoprocketCommon::currency($product->price, true, false, false) : ''; ?>'> <?php echo ShoprocketCommon::currencySymbol('after'); ?>
                <span class="label_desc" id="price-description"></span>
              </li>
              <li class="native_price" <?php if($product->gravity_form_pricing == 1) echo 'style="display:none;"'; ?>>
                <label class="long" for="product-price_description" id="price_description_label"><?php _e( 'Price description' , 'Shoprocket' ); ?>:</label>
                <input type='text' style="width: 275px;" id="product-price_description" name='product[price_description]' value='<?php echo $product->priceDescription ?>'>
                <span class="label_desc" id="price_description"><?php _e( 'If you would like to customize the display of the price' , 'Shoprocket' ); ?></span>
              </li>
              <li class="isUserPrice native_price" <?php if($product->gravity_form_pricing == 1) echo 'style="display:none;"'; ?>>
                <label class="long" for="product-is_user_price" id="is_user_price"><?php _e( 'User defined price' , 'Shoprocket' ); ?>:</label>
                <select id="product-is_user_price" name='product[is_user_price]'>
                  <option value='1' <?php echo ($product->is_user_price == 1)? 'selected="selected"' : '' ?>><?php _e( 'Yes' , 'Shoprocket' ); ?></option>
                  <option value='0' <?php echo ($product->is_user_price == 0)? 'selected="selected"' : '' ?>><?php _e( 'No' , 'Shoprocket' ); ?></option>
                </select><span class="label_desc"><?php _e( 'Allow the customer to specify a price.' , 'Shoprocket' ); ?></span>
              </li>
              
              <li class="userPriceSettings" style="<?php echo ($product->is_user_price == 1)? 'display:block;' : 'display:none;' ?>">
                <label class="long" for="product-min_price"><?php _e( 'Min price' , 'Shoprocket' ); ?>:</label>
                <?php echo ShoprocketCommon::currencySymbol('before'); ?> <input type="text" style="width: 75px;" id="product-min_price" name='product[min_price]' value='<?php echo $product->id > 0 ? ShoprocketCommon::currency($product->minPrice, true, false, false) : ''; ?>' /> <?php echo ShoprocketCommon::currencySymbol('after'); ?>
                <label class="short" for="product-max_price"><?php _e( 'Max price' , 'Shoprocket' ); ?>:</label>
                <?php echo ShoprocketCommon::currencySymbol('before'); ?> <input type="text" style="width: 75px;" id="product-max_price" name='product[max_price]' value='<?php echo $product->id > 0 ? ShoprocketCommon::currency($product->maxPrice, true, false, false) : ''; ?>' /> <?php echo ShoprocketCommon::currencySymbol('after'); ?>
                <span class="label_desc" id="is_user_price_description"><?php _e( 'Set to $0.00 for no limit ' , 'Shoprocket' ); ?></span>
              </li>
              
              <li>
                <label class="long" for="product-taxable"><?php _e( 'Taxed' , 'Shoprocket' ); ?>:</label>
                <select id="product-taxable" name='product[taxable]'>
                  <option value='1' <?php echo ($product->taxable == 1)? 'selected="selected"' : '' ?>><?php _e( 'Yes' , 'Shoprocket' ); ?></option>
                  <option value='0' <?php echo ($product->taxable == 0)? 'selected="selected"' : '' ?>><?php _e( 'No' , 'Shoprocket' ); ?></option>
                </select>
                <p class="label_desc">
                  <?php _e( 'Do you want to collect sales tax when this item is purchased?' , 'Shoprocket' ); ?><br/>
                  <?php _e( 'For subscriptions, tax is only collected on the one time fee.' , 'Shoprocket' ); ?>
                </p>
              </li>
              <li>
                <label class="long" for="product-shipped"><?php _e('Shipped', 'Shoprocket'); ?>:</label>
                <select id="product-shipped" name='product[shipped]'>
                  <option value='1' <?php echo ($product->shipped === '1')? 'selected="selected"' : '' ?>><?php _e( 'Yes' , 'Shoprocket' ); ?></option>
                  <option value='0' <?php echo ($product->shipped === '0')? 'selected="selected"' : '' ?>><?php _e( 'No' , 'Shoprocket' ); ?></option>
                </select>
                <span class="label_desc"><?php _e( 'Does this product require shipping' , 'Shoprocket' ); ?>?</span>
              </li>
                <li>
                <label class="long" for="product-display"><?php _e('Display', 'Shoprocket'); ?>:</label>
                <select id="product-shipped" name='product[showit]'>
                  <option value='1' <?php echo ($product->showit === '1')? 'selected="selected"' : '' ?>><?php _e( 'Yes' , 'Shoprocket' ); ?></option>
                  <option value='0' <?php echo ($product->showit === '0')? 'selected="selected"' : '' ?>><?php _e( 'No' , 'Shoprocket' ); ?></option>
                </select>
                <span class="label_desc"><?php _e( 'Display this product ' , 'Shoprocket' ); ?>?</span>
              </li>
              <li>
                <label class="long" for="product-weight"><?php _e( 'Weight' , 'Shoprocket' ); ?>:</label>
                <input type="text" name="product[weight]" value="<?php echo $product->weight ?>" size="6" id="product-weight" /> lbs 
                <p class="label_desc"><?php _e( 'Shipping weight in pounds. Used for live rates calculations. Weightless items ship free.<br/>
                  If using live rates and you want an item to have free shipping you can enter 0 for the weight.' , 'Shoprocket' ); ?></p>
              </li>
              <li class="nonSubscription">
                <label class="long" for="product-min_qty"><?php _e( 'Min quantity' , 'Shoprocket' ); ?>:</label>
                <input type="text" style="width: 50px;" id="product-min_qty" name='product[min_quantity]' value='<?php echo $product->minQuantity ?>' />
                <label class="short" for="product-max_qty"><?php _e( 'Max quantity' , 'Shoprocket' ); ?>:</label>
                <input type="text" style="width: 50px;" id="product-max_qty" name='product[max_quantity]' value='<?php echo $product->maxQuantity ?>' />
                <p class="label_desc"><?php _e( 'Limit the quantity that can be added to the cart. Set to 0 for unlimited.<br/>
                  If you are selling digital products you may want to limit the quantity of the product to 1.' , 'Shoprocket' ); ?></p>
              </li>
              
            </ul>
          </div>
        </div>
      </div>
    
      <div class="widgets-holder-wrap <?php echo (strlen($product->download_path) || strlen($product->s3_file) ) ? '' : 'closed'; ?>">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3><?php _e( 'Digital Product Options' , 'Shoprocket' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
            <h4 style="padding-left: 10px;"><?php _e( 'Limit The Number Of Times This File Can Be Downloaded' , 'Shoprocket' ); ?></h4>
            <ul>
              <li>
                <label class="med" for='product-download_limit'><?php _e( 'Download limit' , 'Shoprocket' ); ?>:</label>
                <input style="width: 35px;" type='text' name='product[download_limit]' id='product-download_limit' value='<?php echo $product->download_limit ?>' />
                <span class="label_desc"><?php _e( 'Max number of times customer may download product. Enter 0 for no limit.' , 'Shoprocket' ); ?></span>
              </li>
            </ul>
            
            <?php if(ShoprocketSetting::getValue('amazons3_id')): ?>              
              <h4 style="padding-left: 10px;"><?php _e( 'Deliver Digital Products With Amazon S3' , 'Shoprocket' ); ?></h4>
              <ul>
                <li>
                  <label for="product-s3_bucket" class="med bucketNameLabel"><?php _e( 'Bucket' , 'Shoprocket' ); ?>:</label>
                  <input class="long" type='text' name='product[s3_bucket]' id='product-s3_bucket' value="<?php echo $product->s3_bucket ?>" />
                  <p class="label_desc"><?php _e( 'The Amazon S3 bucket name that is holding the digital file.' , 'Shoprocket' ); ?></p>
                  <div>
                    <ul class="ShoprocketS3BucketRestrictions" style="margin-left:140px;color:#ff0000;"></ul>
                  </div>
                </li>
                <li>
                  <label class="med" for='product-s3_file'><?php _e( 'File' , 'Shoprocket' ); ?>:</label>
                  <input class="long" type='text' name='product[s3_file]' id='product-s3_file' value="<?php echo $product->s3_file ?>" />
                  <p class="label_desc"><?php _e( 'The Amazon S3 file name of your digital product.' , 'Shoprocket' ); ?></p>
                </li>
              </ul>
              <p style="width: 600px; padding: 0px 10px;"><a href="#" id="amazons3ForceDownload"><?php _e( 'How do I force the file to download rather than being displayed in the browser?' , 'Shoprocket' ); ?></a></p>
              <p id="amazons3ForceDownloadAnswer" style="width: 600px; padding: 10px; display: none;"><?php _e( 'If you want your digital product to download rather than display in the web browser, log into your Amazon S3 account and click on the file that you want to force to download and enter the following Meta Data in the file\'s properties:<br/>
                Key = Content-Type | Value = application/octet-stream<br/>
                Key = Content-Disposition | Value = attachment' , 'Shoprocket' ); ?><br/><br/>
                <img src="<?php echo Shoprocket_URL; ?>/admin/images/s3-force-download-help.png" /></p>
            <?php  endif; ?>
            
            <?php
              $setting = new ShoprocketSetting();
              $dir = ShoprocketSetting::getValue('product_folder');
              if($dir) {
                if(!file_exists($dir)) echo "<p style='color: red;'>" . __("<strong>WARNING:</strong> The digital products folder does not exist. Please update your <strong>Digital Product Settings</strong> on the <a href='?page=Shoprocket-settings'>settings page</a>.","Shoprocket") . "<br/>$dir</p>";
                elseif(!is_writable($dir)) echo "<p style='color: red;'>" . __("<strong>WARNING:</strong> WordPress cannot write to your digital products folder. Please make your digital products file writeable or change your digital products folder in the <strong>Digital Product Settings</strong> on the <a href='?page=Shoprocket-settings'>settings page</a>.","Shoprocket") . "<br/>$dir</p>";
              }
              else {
                echo "<p style='color: red;'>" . 
                __("Before you can upload your digital product, please specify a folder for your digital products in the<br/>
                <strong>Digital Product Settings</strong> on the <a href='?page=Shoprocket-settings'>settings page</a>.","Shoprocket") . "</p>";
              }
            ?>
            <h4 style="padding-left: 10px;"><?php _e( 'Deliver Digital Products From Your Server' , 'Shoprocket' ); ?></h4>
            <ul>
              <li>
                <label class="med" for='product-upload'><?php _e( 'Upload product' , 'Shoprocket' ); ?>:</label>
                <input class="long" type='file' name='product[upload]' id='product-upload' value='' />
                <p class="label_desc"><?php _e( 'If you FTP your product to your product folder, enter the name of the file you uploaded in the field below.' , 'Shoprocket' ); ?></p>
              </li>
              <li>
                <label class="med" for='product-download_path'><em><?php _e( 'or' , 'Shoprocket' ); ?></em> <?php _e( 'File name' , 'Shoprocket' ); ?>:</label>
                <input class="long" type='text' name='product[download_path]' id='product-download_path' value='<?php echo $product->download_path ?>' />
                <?php
                  if(!empty($product->download_path)) {
                    $file = $dir . DIRECTORY_SEPARATOR . $product->download_path;
                    if(file_exists($file)) {
                      echo "<p class='label_desc'><a href='?page=shoprocket-products&task=xdownload&id=" . $product->id . "'>" . __("Delete this file from the server","Shoprocket") . "</a></p>";
                    }
                    else {
                      echo "<p class='label_desc' style='color: red;'>" . __("<strong>WARNING:</strong> This file is not in your products folder","Shoprocket");
                    }
                  }
                  
                ?>
              </li>
            </ul>
            
            <div class="description" style="width: 600px; margin-left: 10px;">
            <p><strong><?php _e( 'NOTE: If you are delivering large digital files, please consider using Amazon S3.' , 'Shoprocket' ); ?></strong></p>
            <p><a href="#" id="viewLocalDeliverInfo"><?php _e( 'View local delivery information' , 'Shoprocket' ); ?></a></p>
            <p id="localDeliveryInfo" style="display:none;"><?php _e( 'There are several settings built into PHP that affect the size of the files you can upload. These settings are set by your web host and can usually be configured for your specific needs.Please contact your web hosting company if you need help change any of the settings below.
              <br/><br/>
              If you need to upload a file larger than what is allowed via this form, you can FTP the file to the products folder' , 'Shoprocket' ); ?> 
              <?php echo $dir ?> <?php _e( 'then enter the name of the file in the "File name" field above.' , 'Shoprocket' ); ?>
              <br/><br/>
              <?php _e( 'Max Upload Filesize' , 'Shoprocket' ); ?>: <?php echo ini_get('upload_max_filesize');?>B<br/><?php _e( 'Max Postsize' , 'Shoprocket' ); ?>: <?php echo ini_get('post_max_size');?>B</p>
            </div>
          </div>
        </div>
      </div>
    
      <div class="widgets-holder-wrap <?php echo strlen($product->options_1) ? '' : 'closed'; ?>">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3><?php _e( 'Product Variations' , 'Shoprocket' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
            <ul>
              <li>
                <label class="med" for="product-options_1"><?php _e( 'Option Group 1' , 'Shoprocket' ); ?>:</label>
                <input style="width: 80%" type="text" name="product[options_1]" id="product-options_1" value="<?php echo htmlentities($product->options_1, ENT_COMPAT, 'UTF-8'); ?>" />
                <p class="label_desc"><?php _e( 'Small, Medium +$2.00, Large +$4.00' , 'Shoprocket' ); ?></p>
              </li>
              <li>
                <label class="med" for="product-options_2"><?php _e( 'Option Group 2' , 'Shoprocket' ); ?>:</label>
                <input style="width: 80%" type="text" name='product[options_2]' id="product-options_2" value="<?php echo htmlentities($product->options_2, ENT_COMPAT, 'UTF-8'); ?>" />
                <p class="label_desc"><?php _e( 'Red, White, Blue' , 'Shoprocket' ); ?></p>
              </li>
              <li>
                <label class="med" for="product-custom"><?php _e( 'Custom field' , 'Shoprocket' ); ?>:</label>
                <select name='product[custom]' id="product-custom">
                  <option value="none"><?php _e( 'No custom field' , 'Shoprocket' ); ?></option>
                  <option value="single" <?php echo ($product->custom == 'single')? 'selected' : '' ?>><?php _e( 'Single line text field' , 'Shoprocket' ); ?></option>
                  <option value="multi" <?php echo ($product->custom == 'multi')? 'selected' : '' ?>><?php _e( 'Multi line text field' , 'Shoprocket' ); ?></option>
                </select>
                <input type="hidden" name="product[custom_required]" value="" />
                <input type="checkbox" name="product[custom_required]" value="1" id="product-custom_required" <?php echo $product->custom_required == 1 ? ' checked="checked"' : ''; ?>>
                <label for="product-custom_required"><?php _e('Required', 'Shoprocket'); ?></label>
                <p class="label_desc"><?php _e( 'Include a free form text area so your buyer can provide custom information such as a name to engrave on the product.' , 'Shoprocket' ); ?></p>
              </li>
              <li>
                <label class="med" for="product-custom_desc"><?php _e( 'Instructions' , 'Shoprocket' ); ?>:</label>
                <input style="width: 80%" type='text' name='product[custom_desc]' id="product-custom_desc" value='<?php echo $product->custom_desc ?>' />
                <p class="label_desc"><?php _e( 'Tell your buyer what to enter into the custom text field. (Ex. Please enter the name you want to engrave)' , 'Shoprocket' ); ?></p>
              </li>
            </ul>
          </div>
        </div>
      </div>
      
      <div style="padding: 0px;">
        <?php if($product->id > 0): ?>
        <a href='?page=shoprocket-products' class='button-secondary linkButton' style=""><?php _e( 'Cancel' , 'Shoprocket' ); ?></a>
        <?php endif; ?>
        <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='<?php _e('Save', 'Shoprocket'); ?>' />
      </div>
  
    </div>
  </div>

</form>
<div class="wrap">
  <?php if(isset($data['products']) && is_array($data['products'])): ?>
    <h3 style="margin-top: 20px;"><?php _e( 'Your Products' , 'Shoprocket' ); ?></h3>
    <table class="promo-rows widefat ShoprocketHighlightTable" id="products_table">
      <tr>
        <thead>
        	<tr>
        	  <th><?php _e('ID', 'Shoprocket'); ?></th>
      			<th><?php _e('Slug', 'Shoprocket'); ?></th>
      			<th><?php _e('Product Name', 'Shoprocket'); ?></th>
      			<th><?php _e('Price', 'Shoprocket'); ?></th>
      			<th><?php _e('Taxed', 'Shoprocket'); ?></th>
      			<th><?php _e('Shipped', 'Shoprocket'); ?></th>
      			<th><?php _e('Actions', 'Shoprocket'); ?></th>
        	</tr>
        </thead>
        <tfoot>
        	<tr>
        		<th><?php _e('ID', 'Shoprocket'); ?></th>
      			<th><?php _e('Slug', 'Shoprocket'); ?></th>
      			<th><?php _e('Product Name', 'Shoprocket'); ?></th>
      			<th><?php _e('Price', 'Shoprocket'); ?></th>
      			<th><?php _e('Taxed', 'Shoprocket'); ?></th>
      			<th><?php _e('Shipped', 'Shoprocket'); ?></th>
      			<th><?php _e('Actions', 'Shoprocket'); ?></th>
        	</tr>
        </tfoot>
      </tr>
    </table>
  <?php endif; ?>
  <?php if(isset($data['spreedly']) && is_array($data['spreedly']) && count($data['spreedly']) > 0): ?>
    <h3 style="margin-top: 50px;"><?php _e( 'Your Spreedly Subscription Products' , 'Shoprocket' ); ?></h3>
    <table class="widefat ShoprocketHighlightTable" id="spreedly_table">
      <tr>
        <thead>
        	<tr>
        	  <th><?php _e('ID', 'Shoprocket'); ?></th>
      			<th><?php _e('Slug', 'Shoprocket'); ?></th>
      			<th><?php _e('Product Name', 'Shoprocket'); ?></th>
      			<th><?php _e('Price', 'Shoprocket'); ?></th>
      			<th><?php _e('Taxed', 'Shoprocket'); ?></th>
      			<th><?php _e('Shipped', 'Shoprocket'); ?></th>
      			<th><?php _e('Actions', 'Shoprocket'); ?></th>
        	</tr>
        </thead>
        <tfoot>
        	<tr>
        		<th><?php _e('ID', 'Shoprocket'); ?></th>
      			<th><?php _e('Slug', 'Shoprocket'); ?></th>
      			<th><?php _e('Product Name', 'Shoprocket'); ?></th>
      			<th><?php _e('Price', 'Shoprocket'); ?></th>
      			<th><?php _e('Taxed', 'Shoprocket'); ?></th>
      			<th><?php _e('Shipped', 'Shoprocket'); ?></th>
      			<th><?php _e('Actions', 'Shoprocket'); ?></th>
        	</tr>
        </tfoot>
      </tr>
    </table>
  <?php endif; ?>
</div>
<script type="text/javascript">
  (function($){
    $(document).ready(function() {
      
      // Hide Gravity Forms quantity field when using Gravity Forms pricing
      /*
      if($('#product-gravity_form_pricing').val() == 1) {
        $('#product-gravity_form_qty_id').val(0);
        $('#gravity_qty_field_element').hide('slow');
      }
      */
      $('#products_table').dataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bPagination": true,
        "iDisplayLength": 30,
        "sAjaxSource": ajaxurl + "?action=products_table",
        "aLengthMenu": [[30, 60, 150, -1], [30, 60, 150, "All"]],
        "sPaginationType": "bootstrap",
        "bAutoWidth": false,
        "aoColumns": [
          null,
          {
            "bsortable": true,
            "fnRender": function(oObj) {
              return "<a href=\"?page=shoprocket-products&task=edit&id=" + oObj.aData[0] + "\">" + oObj.aData[1] + "</a>"
            }
          },
          null,
          null,
          { "bSearchable": false }, 
          { "bSearchable": false },
          {
            "mData": null,
            "bSearchable": false,
            "bSortable": false,
            "fnRender": function(oObj) {
              return "<a href=\"?page=shoprocket-products&task=edit&id=" + oObj.aData[0] + "\"><?php _e( 'Edit' , 'Shoprocket' ); ?></a> | <a class=\"delete\" href=\"?page=shoprocket-products&task=delete&id=" + oObj.aData[0] + "\"><?php _e( 'Delete' , 'Shoprocket' ); ?></a>"
            }
          }
        ],
        "oLanguage": { 
          "sZeroRecords": "<?php _e('No matching Products found', 'Shoprocket'); ?>", 
          "sSearch": "<?php _e('Search', 'Shoprocket'); ?>:", 
          "sInfo": "<?php _e('Showing', 'Shoprocket'); ?> _START_ <?php _e('to', 'Shoprocket'); ?> _END_ <?php _e('of', 'Shoprocket'); ?> _TOTAL_ <?php _e('entries', 'Shoprocket'); ?>", 
          "sInfoEmpty": "<?php _e('Showing 0 to 0 of 0 entries', 'Shoprocket'); ?>", 
          "oPaginate": {
            "sNext": "<?php _e('Next', 'Shoprocket'); ?>", 
            "sPrevious": "<?php _e('Previous', 'Shoprocket'); ?>", 
            "sLast": "<?php _e('Last', 'Shoprocket'); ?>", 
            "sFirst": "<?php _e('First', 'Shoprocket'); ?>"
          }, 
          "sInfoFiltered": "(<?php _e('filtered from', 'Shoprocket'); ?> _MAX_ <?php _e('total entries', 'Shoprocket'); ?>)", 
          "sLengthMenu": "<?php _e('Show', 'Shoprocket'); ?> _MENU_ <?php _e('entries', 'Shoprocket'); ?>", 
          "sLoadingRecords": "<?php _e('Loading', 'Shoprocket'); ?>...", 
          "sProcessing": "<?php _e('Processing', 'Shoprocket'); ?>..." 
        }
      });
      $('#spreedly_table').dataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bPagination": true,
        "iDisplayLength": 30,
        "sAjaxSource": ajaxurl + "?action=spreedly_table",
        "aLengthMenu": [[30, 60, 150, -1], [30, 60, 150, "All"]],
        "sPaginationType": "bootstrap",
        "bAutoWidth": false,
        "aoColumns": [
          null, 
          {
            "bsortable": true,
            "fnRender": function(oObj) {
              return "<a href=\"?page=shoprocket-products&task=edit&id=" + oObj.aData[0] + "\">" + oObj.aData[1] + "</a>"
            }
          },
          null,
          null, 
          { "bSearchable": false }, 
          { "bSearchable": false },
          {
            "mData": null,
            "bSearchable": false,
            "bSortable": false,
            "fnRender": function(oObj) {
              return "<a href=\"?page=shoprocket-products&task=edit&id=" + oObj.aData[0] + "\"><?php _e( 'Edit' , 'Shoprocket' ); ?></a> | <a class=\"delete\" href=\"?page=shoprocket-products&task=delete&id=" + oObj.aData[0] + "\"><?php _e( 'Delete' , 'Shoprocket' ); ?></a>"
            }
          }
        ],
        "oLanguage": { 
          "sZeroRecords": "<?php _e('No matching Spreedly Subscriptions found', 'Shoprocket'); ?>", 
          "sSearch": "<?php _e('Search', 'Shoprocket'); ?>:", 
          "sInfo": "<?php _e('Showing', 'Shoprocket'); ?> _START_ <?php _e('to', 'Shoprocket'); ?> _END_ <?php _e('of', 'Shoprocket'); ?> _TOTAL_ <?php _e('entries', 'Shoprocket'); ?>", 
          "sInfoEmpty": "<?php _e('Showing 0 to 0 of 0 entries', 'Shoprocket'); ?>", 
          "oPaginate": {
            "sNext": "<?php _e('Next', 'Shoprocket'); ?>", 
            "sPrevious": "<?php _e('Previous', 'Shoprocket'); ?>", 
            "sLast": "<?php _e('Last', 'Shoprocket'); ?>", 
            "sFirst": "<?php _e('First', 'Shoprocket'); ?>"
          }, 
          "sInfoFiltered": "(<?php _e('filtered from', 'Shoprocket'); ?> _MAX_ <?php _e('total entries', 'Shoprocket'); ?>)", 
          "sLengthMenu": "<?php _e('Show', 'Shoprocket'); ?> _MENU_ <?php _e('entries', 'Shoprocket'); ?>", 
          "sLoadingRecords": "<?php _e('Loading', 'Shoprocket'); ?>...", 
          "sProcessing": "<?php _e('Processing', 'Shoprocket'); ?>..." 
        }
      });
      
      $('#product-item_number').keyup(function() {
        $('span.keyup-error').remove();
        var inputVal = $(this).val();
        var characterReg = /"/;
        if(characterReg.test(inputVal)) {
          $(this).after('<span class="keyup-error"><?php _e("No quotes allowed", "Shoprocket"); ?></span>');
        }
      });
      
      $('#products-form').submit(function() {
        if($('span.error').length > 0){
          alert('There are errors on this page!');
          return false;
        }
      });
      
      toggleSubscriptionText();
      toggleMembershipProductAttrs();

      $('.sidebar-name').click(function() {
        $(this.parentNode).toggleClass("closed");
      });

      $("#product-feature_level").keydown(function(e) {
        if (e.keyCode == 32) {
          $(this).val($(this).val() + ""); // append '-' to input
          return false; // return false to prevent space from being added
        }
      }).change(function(e) {
          $(this).val(function (i, v) { return v.replace(/ /g, ""); }); 
      });

      $("#product-spreedly_subscription_id").change(function(){
        if($(this).val() != 0){
          $(".userPriceSettings, .isUserPrice").hide('slow');
          $("#product-is_user_price").val("0");
          
          $("#membershipProductFields").hide('slow');
          $("#product-membership_product").val("0");
          $("#product-feature_level").val('');
          $("#product-billing_interval").val('');
          $("#product-lifetime_membership").attr('checked', false);
          toggleMembershipProductAttrs();
        }
        else{
          if($('#product-gravity_form_pricing').val() != 1) {
            $(".isUserPrice").show('slow');

            if($(".isUserPrice").val() == 1){
              $(".userPriceSettings").show('slow');
            }
          }
          
          $("#membershipProductFields").show('slow');
        }
      })

      $("#product-is_user_price").change(function(){
        if($(this).val() == 1){
          $(".userPriceSettings").show();
        }
        if($(this).val() == 0){
          $(".userPriceSettings").hide();
        }
      })
      // Ajax to populate gravity_form_qty_id when gravity_form_id changes
      $('#product-gravity_form_id').change(function() {
        var gravityFormId = $('#product-gravity_form_id').val();
        console.debug('changing gravity form selection to: ' + gravityFormId);
        $.get(ajaxurl, { 'action': 'update_gravity_product_quantity_field', 'formId': gravityFormId}, function(myOptions) {
          $('#product-gravity_form_qty_id >option').remove();
          $('#product-gravity_form_qty_id').append( new Option('None', 0) );
          $.each(myOptions, function(val, text) {
              $('#product-gravity_form_qty_id').append( new Option(text,val) );
          });
        });
        
        if(gravityFormId > 0) {
          $('.gravity_field').show();
        }
        else {
          $('.gravity_field').hide();
          $('.native_price').show('slow');
          $('#product-gravity_form_qty_id').val(0);
          $('#product-gravity_form_pricing').val(0);
        }
      });
      
      // Toggle native pricing fields based on whether or not Gravity Forms pricing is activated
      $('#product-gravity_form_pricing').change(function() {
        if($(this).val() == 1) {
          $('.native_price').hide('slow');
          // $('#gravity_qty_field_element').hide('slow');
        }
        else {
          // $('#gravity_qty_field_element').show('slow');
          $('#product-gravity_form_qty_id').val(0);
          $('.native_price').show('slow');
        }
      });

      $('#spreedly_subscription_id').change(function() {
        toggleSubscriptionText();
      });

      $('#paypal_subscription_id').change(function() {
        toggleSubscriptionText();
      });

      $('#ShoprocketAccountSearchField').quicksearch('table tbody tr');

      $('#product-membership_product').change(function() {
        toggleMembershipProductAttrs();
      });

      $('#product-lifetime_membership').click(function() {
        toggleLifeTime();
      });

      $('#viewLocalDeliverInfo').click(function() {
        $('#localDeliveryInfo').toggle();
        return false;
      });

      $('#amazons3ForceDownload').click(function() {
        $('#amazons3ForceDownloadAnswer').toggle();
        return false;
      });

      <?php if(ShoprocketSetting::getValue('amazons3_id')): ?>
      validateS3BucketName();  
      <?php endif; ?>
      $("#product-s3_bucket, #product-s3_file").blur(function(){
         validateS3BucketName();        
      })
    })
    $(document).on('click', '.delete', function(e) {
      return confirm('Are you sure you want to delete this item?');
    });
    function toggleLifeTime() {
      if($('#product-lifetime_membership').attr('checked')) {
        $('#product-billing_interval').val('');
        $('#product-billing_interval').attr('disabled', true);
        $('#product-billing_interval_unit').val('days');
        $('#product-billing_interval_unit').attr('disabled', true);
      }
      else {
        $('#product-billing_interval').attr('disabled', false);
        $('#product-billing_interval_unit').attr('disabled', false);
      }
    }

    function toggleMembershipProductAttrs() {
      if($('#product-membership_product').val() == '1') {
        $('.member_product_attrs').show();
        $(".nonSubscription").hide();
      }
      else {
        $('.member_product_attrs').hide();
        $(".nonSubscription").show();
      }
      
    }

    function toggleSubscriptionText() {
      if(isSubscriptionProduct()) {
        $('#price_label').text('One Time Fee:');
        $('#price_description').text('One time fee charged when subscription is purchased. This could be a setup fee.');
        $('#subscriptionVariationDesc').show();
        $('.nonSubscription').hide();
        $('#membershipProductFields').hide();
        $('#product-membership_product').val(0);
        $('#product-feature_level').val('');
        $('#product-billing_interval').val('');
        $('#product-billing_interval_unit').val('days');
        $('#product-lifetime_membership').removeAttr('checked');
      }
      else {
        $('#price_label').text('Price:');
        $('#price_description').text('');
        $('#subscriptionVariationDesc').hide();
        $('.nonSubscription').show();
        $('#membershipProductFields').show();
      }
    }

    function isSubscriptionProduct() {
      var spreedlySubId = $('#spreedly_subscription_id').val();
      var paypalSubId = $('#paypal_subscription_id').val();

      if(spreedlySubId > 0 || paypalSubId > 0) {
        return true;
      }
      return false;
    }

    function bucketError(message){
      $(".bucketNameLabel").css('color','#ff0000');
      // check for existing message
      if($(".ShoprocketS3BucketRestrictions").html().indexOf(message) == -1){
        $(".ShoprocketS3BucketRestrictions").append("<li>" + message + "</li>");
      }
    }

    function validateS3BucketName(){
      var rawBucket = $("#product-s3_bucket").val();

      // clear errors
      $(".ShoprocketS3BucketRestrictions li").remove();
      $(".bucketNameLabel").css('color','#000');

      // no underscores
      if(rawBucket.indexOf('_') != -1){
        bucketError("Bucket names should NOT contain underscores (_).");
      }

      // not empty if there's a file name
      // proper length
      if(rawBucket == "" && $("#product-s3_file").val() != ""){
        bucketError("If you have a file name, you'll need a bucket.");
      } 
      else if(rawBucket.length > 0 && (rawBucket.length < 3 || rawBucket.length > 63) ){
        bucketError("Bucket names should be between 3 and 63 characters long.")
      }

      // dont end with a dash
      if(rawBucket.substring(rawBucket.length-1,rawBucket.length) == "-"){
        bucketError("Bucket names should NOT end with a dash.");
      }

      // dont have dashes next to periods
      if(rawBucket.indexOf('.-') != -1 || rawBucket.indexOf('-.') != -1){
        bucketError("Dashes cannot appear next to periods. For example, “my-.bucket.com” and “my.-bucket” are invalid names.");
      }

      // no uppercase characters allowed
      // only letters, numbers, periods or dashes
      i=0;
      while(i <= rawBucket.length-1){
        if (rawBucket.charCodeAt(i) > 64 && rawBucket.charCodeAt(i) < 90) {
        	bucketError("Bucket names should NOT contain UPPERCASE letters.");
        }
        if (rawBucket != "" && !rawBucket.charAt(i).match(/[a-z0-9\.\-]/g) ){
          bucketError("Bucket names may only contain lower case letters, numbers, periods or hyphens.");
        }
        i++;
      }

      // must start with letter or number
      if(rawBucket != "" && !rawBucket.substring(0,1).match(/[a-z0-9]/g) ){
        bucketError("Bucket names must begin with a number or a lower-case letter.");
      }

      // cannot be an ip address
      if(rawBucket != "" && rawBucket.match(/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/g) ){
        bucketError("Bucket names cannot be an IP address");
      }

    }
  })(jQuery);
</script>
