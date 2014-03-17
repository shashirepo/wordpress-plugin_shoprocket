<?php

class ShoprocketDialog {
  
  public static function shoprocket_get_popup_screens(){
    $default_screens = array(
      'page',
      'post',
      'dashboard'
    );
    
    $custom_screens = apply_filters('shoprocket_add_popup_screens', true);
    $custom_screens = (is_array($custom_screens)) ? $custom_screens : array();
    
    $output = array_merge($default_screens, $custom_screens);
    return $output;
  }
  
  public static function shoprocket_dialog_box() {
    global $wp_version;
    $button = '';
    if(version_compare($wp_version, '3.5-beta-1', '>=')) {
      $button = ' button';
    }
    $image = SHOPROCKET_URL . '/images/shoprocket_tiny_type.png';
    $shoprocket_button = '<a id="ShoprocketThickbox" href="#TB_inline?width=670&height=440&inlineId=select_shoprocket_shortcode" class="thickbox' . $button . '" title="' . 
      __("Add Shoprocket Shortcodes", 'shoprocket') 
      . '"><img src="'.$image.'" alt="' . 
      __("Add Shoprocket Shortcodes", 'shoprocket') 
      . '" width="36px" height="12px" /></a>';
    echo $shoprocket_button;
  }
  
  //Action target that displays the popup to insert a form to a post/page
  public static function add_shortcode_popup() {
    global $current_screen;
    //ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Thinking about adding code for shortcode popup: $current_screen->id");
    
    $add_popup = false;
    if(in_array($current_screen->id, ShoprocketDialog::shoprocket_get_popup_screens())) {
      $add_popup = true;
    }
    
    if($add_popup) { 
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Adding code for shortcode popup");
    }
    else {
      return;
    }
    ?>
    <link type="text/css" rel="stylesheet" href="<?php echo SHOPROCKET_URL; ?>/js/shoprocket.css" />
    <script language="javascript" type="text/javascript">

      <?php
      $prices = array();
      $types = array(); 
      $options='';
      $products = ShoprocketProduct::loadProductsOutsideOfClass();
      
      //$products = $product->getModels("where id>0", "order by name");    	
      if(count($products)):
        $i=0;
        foreach($products as $p) {
          $optionClasses = "";
          if($p->slug==""){
            $id=$p->id;
            $type='id';
            $description = "";
          }
          else{
            $id=$p->slug;
            $type='item';
            $description = '(# '.$p->slug.')';
          }

          $types[] = htmlspecialchars($type);

          if(false && $p->is_paypal_subscription == 1) {
            $sub = new ShoprocketPayPalSubscription($p->id);
            $subPrice = strip_tags($sub->getPriceDescription($sub->offerTrial > 0, '(trial)'));
            $prices[] = htmlspecialchars($subPrice);
            $optionClasses .= " subscriptionProduct ";
            //ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] subscription price in dialog: $subPrice");
          }
          else {
            $priceDescription = __('Price:', 'shoprocket') . ' ' . ShoprocketCommon::currency($p->price);
            if($p->price_description != null) {
              $priceDescription = $p->price_description;
            }
         
            $prices[] = htmlspecialchars(strip_tags($priceDescription));
          }


          $options .= '<option value="'.$id.'" class="' . $optionClasses . '">'.$p->name.' '.$description.'</option>';
          $i++;

        }

      else:
        $options .= '<option value="">' . __('No Products', 'shoprocket') . '</option>';
      endif;

      $prodTypes = implode("\",\"",$types);
      $prodPrices = implode("\",\"", $prices);
      ?>
      
      var prodtype = new Array("<?php echo $prodTypes; ?>");
      var prodprices = new Array("<?php echo $prodPrices; ?>");
      
      function insertProductCode(){
        var type =  prodtype[jQuery("#productNameSelector option:selected").index()];
        var prod  = jQuery("#productNameSelector option:selected").val();
        if(jQuery("#productStyle").val()!=""){
          var style  = 'style="'+jQuery("#productStyle").val()+'"';
        }
        else {
          var style = '';
        }
      
        if(jQuery("#buttonText").val()!=""){
          var text  = 'text="'+jQuery("#buttonText").val()+'"';
        }
        else {
          var text = '';
        }
      
        var quantity = jQuery("input:radio[name=quantityOptions]:checked").val();
        var defaultQuantity = jQuery("#defaultQuantity").val();
        if(quantity == 'user') {
          if(defaultQuantity == ''){
            var quantity = 'quantity="user"';
          }
          else {
            var quantity = 'quantity="user:'+defaultQuantity+'"';
          }
        }
        else if(quantity == 'pre'){
          var quantity = 'quantity="'+defaultQuantity+'"';
        }
        else {
          var quantity = '';
        }
        if(jQuery("#productNameSelector option:selected").hasClass('subscriptionProduct')){
          var quantity = '';
        }
        
        var ajax = jQuery("input:radio[name=ajaxOptions]:checked").val();
        if(ajax == 'yes') {
          var ajax = 'ajax="yes"';
        }
        else {
          var ajax = '';
        }
        
        var showPrice = jQuery("input:radio[name=showPrice]:checked").val();
        if(showPrice == 'no') {
          var showPrice = 'showprice="no"';
        }
        else if(showPrice == 'only'){
          var showPrice = 'showprice="only"';
        }
        else {
          var showPrice = '';
        }
        
        var buttonImage = '';
        if(jQuery("#buttonImage").val() != "") {
          var buttonImage = 'img="' + jQuery("#buttonImage").val() + '"';
        }

        window.send_to_editor('&nbsp;[add_to_cart '+type+'="'+prod+'" '+style+' ' +showPrice+' '+buttonImage+' ' +quantity+' ' +text+ ' ' +ajax+ ' ]&nbsp;');
      }
      
      function shortcode(code){
        window.send_to_editor('&nbsp;['+code+']&nbsp;');
      }

      function shortcode_wrap(open, close){
        window.send_to_editor('&nbsp;['+open+"]&nbsp;<br/>[/"+close+']&nbsp;');
      }
      
      function preview(){

        var productIndex = jQuery("#productNameSelector option:selected").index();

        var priceDescription = jQuery("<div/>").html(prodprices[productIndex]).text();
        var price = "<p id='priceLabel'>" + priceDescription + "</p>";
        if(jQuery("input:radio[name=showPrice]:checked").val()=="no"){
          price = "";
        }

        var style = "";
        if(jQuery("#productStyle").val()!="") {
          style = jQuery("#productStyle").val();
        }
        
        var text = "";
        if(jQuery("#buttonText").val()!="") {
          text = jQuery("#buttonText").val();
        }
        else {
          text = '<?php _e( "Add to Cart" , "shoprocket" ); ?>';
        }
        
        <?php 
          $setting = new ShoprocketSetting();
          $cartImgPath = ShoprocketSetting::getValue('cart_images_url');
          if($cartImgPath) {
            if(strpos(strrev($cartImgPath), '/') !== 0) {
              $cartImgPath .= '/';
            }
            $buttonPath = $cartImgPath . 'add-to-cart.png';
          }
        ?>

        var button = '';

        <?php if($cartImgPath): ?>
          var buttonPath = '<?php echo $buttonPath ?>';
          button = "<img src='"+buttonPath+"' title='"+text+"' alt='<?php _e( 'Shoprocket Add To Cart Button' , 'shoprocket' ); ?>'>";
        <?php else: ?>
          button = "<input type='button' class='ShoprocketButtonPrimary' value='"+text+"' />";
        <?php endif; ?>

        if(jQuery("#buttonImage").val()!=""){
          button = "<img src='"+jQuery("#buttonImage").val()+"' title='<?php _e( 'Add to Cart' , 'shoprocket' ); ?>' alt='<?php _e( 'Shoprocket Add To Cart Button' , 'shoprocket' ); ?>'>";
        } 

        if(jQuery("input:radio[name=showPrice]:checked").val()=="only"){
          button= "";
        }

        var prevBox = "<div style='"+style+"'>"+price+button+"</div>";

        jQuery("#buttonPreview").html(prevBox).text();
      
        if(jQuery("#productNameSelector option:selected").hasClass('subscriptionProduct')){
          jQuery('.quantity').hide();
        }
        else{
          jQuery('.quantity').show();
        }
      }
    </script>
    <div id="select_shoprocket_shortcode" style="display:none;">
      <div id="shoprocket-shortcode-window">
        <div id="shoprocket-shortcode-header">
          <ul class="tabs" id="sidemenu">
            <li class="s1" id="tab-products"><a class="s1 tab" href="javascript:void(0)"><?php _e('Products', 'shoprocket') ?></a></li>
            <li class="s2" id="tab-shortcodes"><a class="s2 tab" href="javascript:void(0)"><?php _e('Shortcodes', 'shoprocket') ?></a></li>
          </ul>
        </div>
        <div class="loading">
          <h2 class="center"><?php _e('loading...', 'shoprocket') ?></h2>
        </div>
        <div class="s1 panes">
          <h3><?php _e("Insert A Product", "shoprocket"); ?></h3>
          <ul>
            <li>
              <label for="productNameSelector"><?php  _e('Your products'); ?>:</label>
              <select id="productNameSelector" name="productName"><?php echo $options; ?></select>
            </li>
            <li class="quantity">
              <label for="quantityOptions" ><?php  _e('Quantity'); ?>:</label>
              <input type='radio' id="quantityOptions" name="quantityOptions" value='user' checked> <?php _e('User Defined', 'shoprocket'); ?>
              <input type='radio' id="quantityOptions" name="quantityOptions" value='pre'> <?php _e('Predefined', 'shoprocket'); ?>
              <input type='radio' id="quantityOptions" name="quantityOptions" value='off'> <?php _e('Off', 'shoprocket'); ?><br />
            </li>
            <li id="defaultQuantityGroup" class="quantity">
              <label for="defaultQuantity"><?php _e('Default Quantity', 'shoprocket'); ?>:</label>
              <input id="defaultQuantity" name="defaultQuantity" size="2" value="1">
            </li>
            <li>
              <label for="buttonText"><?php  _e('Button Text'); ?>:</label>
              <input id="buttonText" name="buttonText" size="34">
            </li>
            <li>
              <label for="productStyle"><?php  _e('CSS style'); ?>:</label>
              <input id="productStyle" name="productStyle" size="34">
            </li>
            <li>
              <label for="ajaxOptions" ><?php  _e('Ajax Add To Cart'); ?>:</label>
              <?php if(ShoprocketSetting::getValue('enable_ajax_by_default') && ShoprocketSetting::getValue('enable_ajax_by_default') == 1): ?>
                <input type='radio' id="ajaxOptions" name="ajaxOptions" value='yes' checked> <?php _e('Yes', 'shoprocket'); ?>
                <input type='radio' id="ajaxOptions" name="ajaxOptions" value='no'> <?php _e('No', 'shoprocket'); ?>
              <?php else: ?>
                <input type='radio' id="ajaxOptions" name="ajaxOptions" value='yes'> <?php _e('Yes', 'shoprocket'); ?>
                <input type='radio' id="ajaxOptions" name="ajaxOptions" value='no' checked> <?php _e('No', 'shoprocket'); ?>
              <?php endif; ?>
            </li>
            <li>
              <label for="showPrice" style="display: inline-block; width: 120px; text-align: right;"><?php  _e('Show price'); ?>:</label>
              <input type='radio' id="showPrice" name="showPrice" value='yes' checked> <?php _e('Yes', 'shoprocket'); ?>
              <input type='radio' id="showPrice" name="showPrice" value='no'> <?php _e('No', 'shoprocket'); ?>
              <input type='radio' id="showPrice" name="showPrice" value='only'> <?php _e('Price Only', 'shoprocket'); ?>
            </li>
            <li>
              <label for="buttonImage" ><?php  _e('Button path'); ?>:</label>
              <input id="buttonImage" name="buttonImage" size="34">
            </li>
            <li>
              <label for="buttonImage" ><?php  _e('Preview'); ?>:</label>
              <div class="" id="buttonPreview"></div>
            </li>
            <li>
              
            </li>
          </ul>
        </div>
        <?php
        $shortcodes_system = array(
         'express' => __('Listens for PayPal Express callbacks <br/>Belongs on system page store/express', 'shoprocket'),
         'ipn' => __('PayPal Instant Payment Notification <br/>Belongs on system page store/ipn', 'shoprocket'),
         'receipt' => __('Shows the customer\'s receipt after a successful sale <br/>Belongs on system page store/receipt', 'shoprocket')
        );
        $shortcodes = array(
          'add_to_cart item=&quot;&quot;' => __('Create add to cart button', 'shoprocket'),
          'cart' => __('Show the shopping cart', 'shoprocket'),
          'cart mode=&quot;read&quot;' => __('Show the shopping cart in read-only mode', 'shoprocket'),
          'checkout_mijireh' => __('Mijireh Checkout Accept Credit Cards - PCI Compliant', 'shoprocket'),
          'checkout_stripe' => __('Stripe Checkout form', 'shoprocket'),
          'checkout_2checkout' => __('2Checkout checkout form', 'shoprocket'),
          'checkout_manual' => __('Checkout form that does not process credit cards', 'shoprocket'),
          'checkout_paypal' => __('PayPal Website Payments Standard checkout button', 'shoprocket'),
          'checkout_paypal_express' => __('PayPal Express checkout button', 'shoprocket'),
          'clear_cart' => __('Clear the contents of the shopping cart', 'shoprocket'),
          'shopping_cart' => __('Show the Shoprocket sidebar widget', 'shoprocket'),
          'post_sale' => __('Display content one time immediately after a sale', 'shoprocket'),
          'shoprocket_affiliate' => __('Add order information to an affiliate URL that can be used inside the post_sale shortcode. The only attribute is "display"', 'shoprocket')
        );
        if(false){
          $shortcodes_pro = array(
            'account_info' => __('Show link to manage subscription account information', 'shoprocket'),
            'account_login' => __('Account login form', 'shoprocket'),
            'account_logout' => __('Logs user out of account', 'shoprocket'),
            'account_logout_link' => __('Show link to log out of account', 'shoprocket'),
            'account_expiration' => __('Show a member when their account expires', 'shoprocket'),
            'cancel_paypal_subscription' => __('Link to cancel PayPal subscription', 'shoprocket'),
            'checkout_payleap' => __('PayLeap checkout form', 'shoprocket'),
            'checkout_authorizenet' => __('Authorize.net (or AIM compatible gateway) checkout form', 'shoprocket'),
            'checkout_eway' => __('Eway checkout form', 'shoprocket'),
            'checkout_mwarrior' => __('Merchant Warrior checkout form', 'shoprocket'),
            'checkout_paypal_pro' => __('PayPal Pro checkout form', 'shoprocket'),
            'terms_of_service' => __('Show the terms of service agreement', 'shoprocket'),
            'subscription_feature_level' => __('Show the name of the subscription feature level for the currently logged in user', 'shoprocket'),
            'subscription_name' => __('Show the name of the subscription for the currently logged in user', 'shoprocket'),
            'zendesk_login' => __('Listens for remote login calls from Zendesk', 'shoprocket'),
            'hide_from' => __('Hide content from members without the listed feature levels - opposite of [show_to]', 'shoprocket'),
            'show_to' => __('Show content only to members with the listed feature levels - opposite of [hide_from]', 'shoprocket'),
            'email_opt_out' => __('Allow Shoprocket members to opt out of receiving notifications about the status of their account.', 'shoprocket')
          );
          $shortcodes = array_merge($shortcodes, $shortcodes_pro);
          $shortcodes_system['spreedly_listener'] = __('Listens for spreedly account changes <br/>Belongs on system page store/spreedly', 'shoprocket');
        }
        ksort($shortcodes);
        ?>
        <div class="s2 panes">
          <h3><?php _e("Insert A System Shortcode", "shoprocket"); ?></h3>
          <table id="shortCodeList" cellpadding="0">
            <tr>
              <td colspan="2"><strong><?php _e('Shortcode Quick Reference', 'shoprocket')?></strong></td>
            </tr>
            <?php
            foreach($shortcodes as $shortcode => $description) { ?>
              <tr>
                <td><div class="shortcode" <?php
                  if($shortcode == 'hide_from' || $shortcode == 'show_to' || $shortcode == 'post_sale') { ?>
                    onclick="shortcode_wrap('<?php echo $shortcode; ?> <?php echo ($shortcode == 'show_to' || $shortcode == 'hide_from') ? 'level=&quot;&quot;' : ''; ?>', '<?php echo $shortcode; ?>');"
                  <?php }
                  else { ?>
                    onclick="shortcode('<?php echo $shortcode; ?>');"
                <?php } ?>><a title="Insert [<?php echo $shortcode; ?>]">[<?php echo ($shortcode == 'show_to' || $shortcode == 'hide_from') ? "$shortcode level=&quot;&quot;" : "$shortcode"; ?>]</a></div></td>
                <td><?php echo $description; ?></td>
                </tr>
            <?php }
            ?>
          </table>
          <br/>
          <table id="systemShortCodeList" cellpadding="0">
            <tr>
              <td colspan="2"><strong><?php _e('System Shortcodes', 'shoprocket')?></strong></td>
            </tr>
            <?php
            foreach($shortcodes_system as $shortcode => $description) { ?>
              <tr>
                <td><div class="shortcode" <?php
                  if($shortcode == 'hide_from' || $shortcode == 'show_to' || $shortcode == 'post_sale') { ?>
                    onclick="shortcode_wrap('<?php echo $shortcode; ?> <?php echo ($shortcode == 'show_to' || $shortcode == 'hide_from') ? 'level=&quot;&quot;' : ''; ?>', '<?php echo $shortcode; ?>');"
                  <?php }
                  else { ?>
                    onclick="shortcode('<?php echo $shortcode; ?>');"
                <?php } ?>><a title="Insert [<?php echo $shortcode; ?>]">[<?php echo ($shortcode == 'show_to' || $shortcode == 'hide_from') ? "$shortcode level=&quot;&quot;" : "$shortcode"; ?>]</a></div></td>
                <td><?php echo $description; ?></td>
                </tr>
            <?php }
            ?>
          </table>
        </div>
        <div>
          <div class="buttons">
            <input type="button" class="button-secondary" value="<?php _e("Cancel", "shoprocket"); ?>" onclick="tb_remove();" />
            <input id="insertProductButton" type="button" class="button-primary" value="<?php _e("Insert Shortcode", "shoprocket"); ?>" onclick="insertProductCode();"/>
          </div>
        </div>
      </div>
    </div>
    <script type="text/javascript">
      (function($){
        function adjustHeights() {
          hWindow = $('#TB_window').height();
          wWindow = $('#TB_window').width();
          $('#TB_ajaxContent').height(hWindow - 45);
          $('#TB_ajaxContent').width(wWindow - 30);
        }
        $(window).resize(function() {
          $('#TB_ajaxContent').css('height','auto');
          adjustHeights();
        });
        $(document).ready(function() {
          preview();
          $("input").change(function(){preview();});
          $("input").click(function(){preview();});
          $("#productNameSelector").change(function(){
            preview();
          })
          adjustHeights();
          $("#ShoprocketThickBox").click(function(){
            adjustHeights();
          })
          $("input:radio[name=quantityOptions]").change(function(){
            if($("input:radio[name=quantityOptions]:checked").val()=="off"){
              $("#defaultQuantityGroup").fadeOut(600);
            }
            else{
              $("#defaultQuantityGroup").fadeIn(600);
            }
          })
          // setting the tabs in the sidebar hide and show, setting the current tab
          $('div.panes').hide();
          $('div.s1').show();
          $('div.loading').hide();
          $('#insertProductButton').show();
          $('div#shoprocket-shortcode-header ul.tabs li.s1 a').addClass('current');
          // SIDEBAR TABS
          $('div#shoprocket-shortcode-header ul li a').click(function(){
            adjustHeights();
            var thisClass = this.className.slice(0,2);
            $('div.panes').hide();
            $('div.' + thisClass).fadeIn(300);
            $('div#shoprocket-shortcode-header ul.tabs li a').removeClass('current');
            $('div#shoprocket-shortcode-header ul.tabs li a.' + thisClass).addClass('current');
            if($('.current').hasClass('s1')){
              $('#insertProductButton').fadeIn(300);
            }
            else{
              $('#insertProductButton').fadeOut(300);
            }
          });
        });
      })(jQuery);
    </script>
  <?php
  }
  
}