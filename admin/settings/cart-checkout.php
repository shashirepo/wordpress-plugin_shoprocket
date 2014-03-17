<?php
$tab = 'cc-cart_checkout';
?>
<div id="saveResult"></div>
<div id="shoprocket-inner-tabs">
  <ul class="subsubsub">
    <li><a href="#cc-cart_checkout" class="cc-cart_checkout"><?php _e('Cart & Checkout Settings', 'shoprocket'); ?></a> | </li>
    <li><a href="#cc-checkout_custom_field" class="cc-custom_checkout_field"><?php _e('Checkout Custom Field', 'shoprocket'); ?></a> | </li>
    <li><a href="#cc-mimimum_cart_amount" class="cc-mimimum_cart_amount"><?php _e('Minimum Cart Amount', 'shoprocket'); ?></a> | </li>
    <li><a href="#cc-terms_of_service" class="cc-terms_of_service"><?php _e('Terms of Service', 'shoprocket'); ?></a></li>
  </ul>
  <br clear="all">
  <form id="cartCheckoutForm" action="" method="post" class="ajaxSettingForm">
    <input type="hidden" name="action" value="save_settings" />
    <input type="hidden" name="_success" value="<?php _e('Your cart & checkout settings have been saved', 'shoprocket'); ?>." />
    <div id="cc-cart_checkout" class="pane">
      <h3><?php _e('Cart & Checkout Settings', 'shoprocket'); ?></h3>
      <table class="form-table">
        <tbody>
          <tr valign="top">
            <th scope="row"><?php _e('Display Product Product Slugs in the Cart', 'shoprocket'); ?></th>
            <td>
              <input type="radio" id="display_item_number_cart_yes" name="display_item_number_cart" value="1" <?php echo ShoprocketSetting::getValue('display_item_number_cart') == 1 ? 'checked="checked" ' : '' ?>/>
              <label for="display_item_number_cart_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
              <input type="radio" id="display_item_number_cart_no" name="display_item_number_cart" value="" <?php echo ShoprocketSetting::getValue('display_item_number_cart') != 1 ? 'checked="checked" ' : '' ?>/>
              <label for="display_item_number_cart_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
              <p class="description"><?php _e( 'Choose whether or not to display the product Product Slugs in the cart.' , 'shoprocket' ); ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Display Product Product Slugs in Emails', 'shoprocket'); ?></th>
            <td>
              <input type="radio" id="display_item_number_receipt_yes" name="display_item_number_receipt" value="1" <?php echo ShoprocketSetting::getValue('display_item_number_receipt') == 1 ? 'checked="checked" ' : '' ?>/>
              <label for="display_item_number_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
              <input type="radio" id="display_item_number_receipt_no" name="display_item_number_receipt" value="" <?php echo ShoprocketSetting::getValue('display_item_number_receipt') != 1 ? 'checked="checked" ' : '' ?>/>
              <label for="display_item_number_receipt_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
              <p class="description"><?php _e( 'Choose whether or not to display the product Product Slugs in the email receipt. If you are using a completely customized email, you will need the {{receipt}} or {{products}} data tag for this to work.' , 'shoprocket' ); ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Shipping Form', 'shoprocket'); ?></th>
            <td>
              <input type="radio" id="same_as_billing_yes" name="sameAsBillingOff" value="" <?php echo ShoprocketSetting::getValue('sameAsBillingOff') != 1 ? 'checked="checked" ' : '' ?>/>
              <label for="same_as_billing_yes"><?php _e( 'Show "Same as Billing"' , 'shoprocket' ); ?></label>
              <input type="radio" id="same_as_billing_no" name="sameAsBillingOff" value="1" <?php echo ShoprocketSetting::getValue('sameAsBillingOff') == 1 ? 'checked="checked" ' : '' ?>/>
              <label for="same_as_billing_no"><?php _e( 'Always show the shipping form' , 'shoprocket' ); ?></label>
              <p class="description"><?php _e( 'Choose whether or not to display the shipping address form on the checkout page by default.' , 'shoprocket' ); ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('User Price Label', 'shoprocket'); ?></th>
            <td>
              <input type="text" name="userPriceLabel" id="userPriceLabel" value="<?php echo ShoprocketSetting::getValue('userPriceLabel'); ?>" />
              <p class="description"><?php _e( 'Defaults to "Enter an amount: "' , 'shoprocket' ); ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('User Quantity Label', 'shoprocket'); ?></th>
            <td>
              <input type="text" name="userQuantityLabel" id="userQuantityLabel" value="<?php echo ShoprocketSetting::getValue('userQuantityLabel'); ?>" />
              <p class="description"><?php _e( 'Defaults to "Quantity: "' , 'shoprocket' ); ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Ajax Add To Cart', 'shoprocket'); ?></th>
            <td>
              <input type="radio" name="enable_ajax_by_default" value="1" id="enable_ajax_by_default_yes" <?php echo ShoprocketSetting::getValue('enable_ajax_by_default') == 1 ? 'checked="checked" ' : '' ?>/>
              <label for="enable_ajax_by_default_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
              <input type="radio" name="enable_ajax_by_default" value="" id="enable_ajax_by_default_no" <?php echo ShoprocketSetting::getValue('enable_ajax_by_default') != 1 ? 'checked="checked" ' : '' ?>/>
              <label for="enable_ajax_by_default_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
              <p class="description"><?php _e( 'This changes the default action when adding a product shortcode to a page or post' , 'shoprocket' ); ?>.</p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Product Links In Cart', 'shoprocket'); ?></th>
            <td>
              <input type="radio" name="product_links_in_cart" id="product_links_in_cart_yes" value="1" <?php echo ShoprocketSetting::getValue('product_links_in_cart') == 1 ? 'checked="checked" ' : '' ?>/>
              <label for="product_links_in_cart_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
              <input type="radio" name="product_links_in_cart" id="product_links_in_cart_no" value="" <?php echo ShoprocketSetting::getValue('product_links_in_cart') != 1 ? 'checked="checked" ' : '' ?>/>
              <label for="product_links_in_cart_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
              <p class="description"><?php _e( 'Use this option to add a link back to the original product page' , 'shoprocket' ); ?>.</span>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Promotion Variable Name', 'shoprocket'); ?></th>
            <td>
              <input type="text" name="promotion_get_varname" id="promotion_get_varname" value="<?php echo ShoprocketSetting::getValue('promotion_get_varname'); ?>" />
              <p class="description"><?php _e( 'Use this to set the variable you want to use to allow coupons to be added to the cart via a link. Leave empty to use the default. Default: promotion' , 'shoprocket' ); ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Checkout Tax Order Summary', 'shoprocket'); ?></th>
            <td>
              <input type="radio" name="checkout_order_summary" id="checkout_order_summary_yes" value="1" <?php echo ShoprocketSetting::getValue('checkout_order_summary') == 1 ? 'checked="checked" ' : '' ?>/>
              <label for="checkout_order_summary_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
              <input type="radio" name="checkout_order_summary" id="checkout_order_summary_no" value="" <?php echo ShoprocketSetting::getValue('checkout_order_summary') != 1 ? 'checked="checked" ' : '' ?>/>
              <label for="checkout_order_summary_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
              <p class="description"><?php _e( 'Use this option to display an order summary on the checkout page if tax is being collected' , 'shoprocket' ); ?>.</span>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Continue Shopping Button', 'shoprocket'); ?></th>
            <td>
              <select name="continue_shopping" id="continue_shopping">
                <option value="0"><?php _e( 'Send customer back to the last page' , 'shoprocket' ); ?></option>
                <option value="1"><?php _e( 'Always go to the store home page' , 'shoprocket' ); ?></option>
              </select>
              <p class="description"><?php _e( 'You can choose to have customers go back to the last page they were on when they clicked "Add to Cart" or you can force the continue shopping button to always go to the store home page.' , 'shoprocket' ); ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Customize Cart Images', 'shoprocket'); ?></th>
            <td>
              <input type="text" class="regular-text" name="cart_images_url" id="cart_images_url" value="<?php echo ShoprocketSetting::getValue('cart_images_url'); ?>" />
              <p class="description"><?php _e( 'If you would like to use your own shopping cart images (Add To Cart, Checkout, etc), enter the URL to the directory where you will be storing the images. The path should be outside the plugins/shoprocket directory so that they are not lost when you upgrade your Shoprocket installation to a new version.' , 'shoprocket' ); ?></p>
              <p class="description"><?php _e( 'For example you may want to store your custom cart images here' , 'shoprocket' ); ?>:<br/>
              <?php echo WPCURL ?>/uploads/cart-images/</p>
              <p class="description"><?php _e( 'Be sure that your path ends in a trailing slash like the example above and that you have all of the image names below in your directory' , 'shoprocket' ); ?>:</p>
              <ul class="description shoprocket-settings-list">
                <?php
                $dir = new DirectoryIterator(dirname(__FILE__) . '/../../images');
                foreach ($dir as $fileinfo) {
                  if (substr($fileinfo->getFilename(), -3) == 'png') { ?>
                    <li><?php echo $fileinfo->getFilename(); ?></li>
                  <?php }
                }
                ?>
              </ul>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div id="cc-checkout_custom_field" class="pane">
      <h3><?php _e('Checkout Custom Field', 'shoprocket'); ?></h3>
      <?php if(false): ?>
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <th scope="row"><?php _e('Display', 'shoprocket'); ?></th>
              <td>
                <select name="checkout_custom_field_display">
                  <option value="disabled"<?php echo !ShoprocketSetting::getValue('checkout_custom_field_display') || ShoprocketSetting::getValue('checkout_custom_field_display') == 'disabled' ? ' selected="selected"' : '' ?>><?php _e('Disabled', 'shoprocket'); ?></option>
                  <option value="optional"<?php echo ShoprocketSetting::getValue('checkout_custom_field_display') == 'optional' ? ' selected="selected"' : '' ?>><?php _e('Optional', 'shoprocket'); ?></option>
                  <option value="required"<?php echo ShoprocketSetting::getValue('checkout_custom_field_display') == 'required' ? ' selected="selected"' : '' ?>><?php _e('Required', 'shoprocket'); ?></option>
                </select>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Field Type', 'shoprocket'); ?></th>
              <td>
                <select name="checkout_custom_field">
                  <option value="single"<?php echo ShoprocketSetting::getValue('checkout_custom_field') == 'single' ? ' selected="selected"' : '' ?>><?php _e( 'Single Line Text Field' , 'shoprocket' ); ?></option>
                  <option value="multi"<?php echo ShoprocketSetting::getValue('checkout_custom_field') == 'multi' ? ' selected="selected"' : '' ?>><?php _e( 'Multi Line Text Field' , 'shoprocket' ); ?></option>
                </select>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Instructions Label', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="checkout_custom_field_label" value="<?php echo ShoprocketSetting::getValue('checkout_custom_field_label'); ?>" />
                <p class="description"><?php _e( 'Defaults to "Enter any special instructions you have for this order: "' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Required Error Label', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="checkout_custom_field_error_label" value="<?php echo ShoprocketSetting::getValue('checkout_custom_field_error_label'); ?>" />
                <p class="description"><?php _e( 'Defaults to "the special instructions field is required"' , 'shoprocket' ); ?></p>
              </td>
            </tr>
          </tbody>
        </table>
      <?php else: ?>
        <p class="description"><?php _e( 'This feature is only available in', 'shoprocket'); ?> <a href="http://shoprocket.com"><?php _e('Shoprocket Professional', 'shoprocket'); ?></a>.</p>
      <?php endif; ?>
    </div>
    <div id="cc-mimimum_cart_amount" class="pane">
      <h3><?php _e('Minimum Cart Amount', 'shoprocket'); ?></h3>
      <?php if(false): ?>
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <th scope="row"><?php _e('Enable Minimum Amount', 'shoprocket'); ?></th>
              <td>
                <input type="radio" name="minimum_cart_amount" id="minimum_cart_amount_yes" value="1" <?php echo ShoprocketSetting::getValue('minimum_cart_amount') == 1 ? 'checked="checked" ' : '' ?>/>
                <label for="minimum_cart_amount_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
                <input type="radio" name="minimum_cart_amount" id="minimum_cart_amount_no" value="0" <?php echo ShoprocketSetting::getValue('minimum_cart_amount') != 1 ? 'checked="checked" ' : '' ?>/>
                <label for="minimum_cart_amount_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
                <p class="description"><?php _e( 'This feature allows you to set a minimum cart amount before your customers can checkout.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Amount', 'shoprocket'); ?></th>
              <td>
                <?php echo ShoprocketCommon::currencySymbol('before'); ?>
                <input class="small-text" type="text" name="minimum_amount" value="<?php echo htmlentities(ShoprocketSetting::getValue('minimum_amount'), ENT_COMPAT, 'UTF-8');  ?>" id="minimum_amount">
                <?php echo ShoprocketCommon::currencySymbol('after'); ?>
                <p class="description"><?php _e( 'Set the amount required in order for a customer to checkout' , 'shoprocket' ); ?>.</p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Label', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="minimum_amount_label" class="regular-text" id="minimum_amount_label" value="<?php echo ShoprocketSetting::getValue('minimum_amount_label'); ?>" />
                <p class="description"><?php _e( 'Defaults to "You have not yet reached the required minimum amount in order to checkout."' , 'shoprocket' ); ?></p>
              </td>
            </tr>
          </tbody>
        </table>
      <?php else: ?>
        <p class="description"><?php _e( 'This feature is only available in', 'shoprocket'); ?> <a href="http://shoprocket.com"><?php _e('Shoprocket Professional', 'shoprocket'); ?></a>.</p>
      <?php endif; ?>
    </div>
    <div id="cc-terms_of_service" class="pane">
      <h3><?php _e('Terms of Service', 'shoprocket'); ?></h3>
      <?php if(false): ?>
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <th scope="row"><?php _e('Require Terms', 'shoprocket'); ?></th>
              <td>
                <input type="radio" name="require_terms" id="require_terms_yes" value="1" <?php echo ShoprocketSetting::getValue('require_terms') == 1 ? 'checked="checked" ' : ''; ?>/>
                <label for="require_terms_yes"><?php _e('Yes', 'shoprocket'); ?></label>
                <input type="radio" name="require_terms" id="require_terms_no" value="" <?php echo ShoprocketSetting::getValue('require_terms') != 1 ? 'checked="checked" ' : ''; ?>/>
                <label for="require_terms_no"><?php _e('No', 'shoprocket'); ?></label>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Terms Title', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="cart_terms_title" id="cart_terms_title" class="regular-text" value="<?php echo ShoprocketSetting::getValue('cart_terms_title') ? ShoprocketSetting::getValue('cart_terms_title') : __("Terms of Service","shoprocket"); ?>" />
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Terms Text', 'shoprocket'); ?></th>
              <td>
                <textarea id="cart_terms_text" name="cart_terms_text" class="large-textarea"><?php echo ShoprocketSetting::getValue('cart_terms_text'); ?></textarea>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Acceptance Label', 'shoprocket'); ?></th>
              <td>
                <input type="text" class="regular-text" name="cart_terms_acceptance_label" id="cart_terms_acceptance_label" value="<?php echo (ShoprocketSetting::getValue('cart_terms_acceptance_label')) ? ShoprocketSetting::getValue('cart_terms_acceptance_label') : __( 'I Agree, proceed to Checkout' ); ?>" />
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Replacement Text', 'shoprocket'); ?></th>
              <td>
                <input type="text" class="regular-text" name="cart_terms_replacement_text" id="cart_terms_replacement_text" value="<?php echo ShoprocketSetting::getValue('cart_terms_replacement_text') ? ShoprocketSetting::getValue('cart_terms_replacement_text') : __("Please accept the terms of service to checkout.","shoprocket"); ?>" />
                <p class="description"><?php _e('Enter the text to be displayed instead of the checkout button, prior to the customer accepting the terms of service', 'shoprocket'); ?>.</p>
              </td>
            </tr>
          </tbody>
        </table>
      <?php else: ?>
        <p class="description"><?php _e( 'This feature is only available in', 'shoprocket'); ?> <a href="http://shoprocket.com"><?php _e('Shoprocket Professional', 'shoprocket'); ?></a>.</p>
      <?php endif; ?>
    </div>
    <table class="form-table">
      <tbody>
        <tr valign="top">
          <th scope="row">
            <?php submit_button(); ?>
          </th>
          <td></td>
        </tr>
      </tbody>
    </table>
  </form>
</div>
<script type="text/javascript">
  (function($){
    $(document).ready(function(){
      $('#shoprocket-inner-tabs div.pane').hide();
      $('#shoprocket-inner-tabs div#<?php echo $tab; ?>').show();
      $('#shoprocket-inner-tabs ul li a.<?php echo $tab; ?>').addClass('current');
      
      $('#shoprocket-inner-tabs ul li a').click(function(){
        $('#shoprocket-inner-tabs ul li a').removeClass('current');
        $(this).addClass('current');
        var currentTab = $(this).attr('href');
        $('#shoprocket-inner-tabs div.pane').hide();
        $(currentTab).show();
        return false;
      });
      
      $("#continue_shopping").val("<?php echo ShoprocketSetting::getValue('continue_shopping'); ?>");
    })
  })(jQuery);
</script>