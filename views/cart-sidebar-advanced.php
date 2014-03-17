<?php echo $data['beforeWidget']; ?>
  
  <?php echo $data['beforeTitle'] . '<span id="ShoprocketWidgetCartTitle">' . $data['title'] . '</span>' . $data['afterTitle']; ?>  

    <div id="ShoprocketAdvancedSidebarAjax"<?php if(!$data['numItems']): ?> style="display:none;"<?php endif; ?>>
      <p id="ShoprocketWidgetCartEmptyAdvanced">
        <?php _e( 'You have' , 'Shoprocket' ); ?> <?php echo $data['numItems']; ?> 
        <?php echo _n('item', 'items', $data['numItems'], 'Shoprocket'); ?> 
        (<?php echo ShoprocketCommon::currency($data['cartWidget']->getSubTotal()); ?>) <?php _e( 'in your shopping cart' , 'Shoprocket' ); ?>.
      </p>
      <?php 
        $items = $data['items'];
        $product = new ShoprocketProduct();
        $subtotal = ShoprocketSession::get('ShoprocketCart')->getSubTotal();
        $shippingMethods = ShoprocketSession::get('ShoprocketCart')->getShippingMethods();
        $shipping = ShoprocketSession::get('ShoprocketCart')->getShippingCost();
 
        $tax = 0;
          if(isset($data['tax']) && $data['tax'] > 0) {
            $tax = $data['tax'];
          }
          else {
            // Check to see if all sales are taxed
            $tax = ShoprocketSession::get('ShoprocketCart')->getTax('All Sales');
        }
      ?>
      <form id='ShoprocketWidgetCartForm' action="" method="post">
        <input type='hidden' name='task' value='updateCart' />
          <table id='ShoprocketAdvancedWidgetCartTable' class="ShoprocketAdvancedWidgetCartTable">
            <?php $isShipped = false; ?>
            <?php foreach($items as $itemIndex => $item): ?>
              <?php 
                $product->load($item->getProductId());
                $productPrice = $item->getProductPrice();
                $productSubtotal = $item->getProductPrice() * $item->getQuantity();
                if($product->isShipped()) {
                  $isShipped = true;
                }
              ?>
              <tr class="product_items">
                <td>
                  <span class="ShoprocketProductTitle"><?php echo $item->getFullDisplayName(); ?></span>
                  <span class="ShoprocketQuanPrice">
                    <span class="ShoprocketProductQuantity"><?php echo $item->getQuantity() ?></span> 
                    <span class="ShoprocketMetaSep">x</span> 
                    <span class="ShoprocketProductPrice"><?php echo ShoprocketCommon::currency($productPrice) ?></span>
                  </span>
                </td>
                <td class="ShoprocketProductSubtotalColumn">
                  <span class="ShoprocketProductSubtotal"><?php echo ShoprocketCommon::currency($productSubtotal) ?></span>
                </td>
              </tr>
            <?php endforeach; ?>
            <tr class="ShoprocketSubtotalRow">
              <td colspan="2">
                <span class="ShoprocketCartSubTotalLabel"><?php _e( 'Subtotal' , 'Shoprocket' ); ?></span><span class="ShoprocketMetaSep">: </span>
                <span class="ShoprocketSubtotal"><?php echo ShoprocketCommon::currency($subtotal); ?></span>
              </td>
            </tr>
        
            <?php if(isset($data['shipping'] ) && $data['shipping'] == true && $isShipped): ?>
                <tr class="ShoprocketShippingRow">
                  <td colspan="2">
                    <span class="ShoprocketCartShippingLabel"><?php _e( 'Shipping' , 'Shoprocket' ); ?></span><span class="ShoprocketMetaSep">: </span>
                    <span class="ShoprocketShipping"><?php echo ShoprocketCommon::currency($shipping); ?></span>
                  </td>
                </tr>
                <?php if(Shoprocket_PRO && ShoprocketSetting::getValue('use_live_rates')): ?>

                  <?php if(ShoprocketSession::get('Shoprocket_shipping_zip')): ?>
                    <tr class="ShoprocketShippingToRow ShoprocketRequireShipping" <?php if(!ShoprocketSession::get('ShoprocketCart')->requireShipping()): ?> style="display:none;"<?php endif; ?>>
                      <th colspan="2">
                        <?php _e( 'Shipping to' , 'Shoprocket' ); ?> <?php echo ShoprocketSession::get('Shoprocket_shipping_zip'); ?> 
                        <?php
                          if(ShoprocketSetting::getValue('international_sales')) {
                            echo ShoprocketSession::get('Shoprocket_shipping_country_code');
                          }
                        ?>
                        (<a href="#" id="widget_change_shipping_zip_link"><?php _e( 'change' , 'Shoprocket' ); ?></a>)
                        &nbsp;
                        <?php
                          $liveRates = ShoprocketSession::get('ShoprocketCart')->getLiveRates();
                          $rates = $liveRates->getRates();
                          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] LIVE RATES: " . print_r($rates, true));
                          $selectedRate = $liveRates->getSelected();
                          $shipping = ShoprocketSession::get('ShoprocketCart')->getShippingCost();
                        ?>
                        <select name="live_rates" id="widget_live_rates">
                          <?php foreach($rates as $rate): ?>
                            <option value='<?php echo $rate->service ?>' <?php if($selectedRate->service == $rate->service) { echo 'selected="selected"'; } ?>>
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
                    <tr id="widget_set_shipping_zip_row" class="ShoprocketRequireShipping"<?php if(ShoprocketSession::get('Shoprocket_shipping_zip')): ?> style="display:none;"<?php endif; ?>>
                      <th colspan="2"><?php _e( 'Enter Your Zip Code' , 'Shoprocket' ); ?>:
                        <input type="text" name="shipping_zip" value="" id="shipping_zip" size="5" />

                        <?php if(ShoprocketSetting::getValue('international_sales')): ?>
                          <select name="shipping_country_code" class="ShoprocketCountrySelect">
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

                        <input type="submit" name="updateCart" value="Calculate Shipping" id="shipping_submit" class="ShoprocketButtonSecondaryWidget" />
                      </th>
                    </tr>
                  <?php else: ?>
                    <tr id="widget_set_shipping_zip_row" class="ShoprocketRequireShipping"<?php if(!ShoprocketSession::get('ShoprocketCart')->requireShipping()): ?> style="display:none;"<?php endif; ?>>
                      <th colspan="2"><?php _e( 'Enter Your Zip Code' , 'Shoprocket' ); ?>:
                        <input type="text" name="shipping_zip" value="" id="shipping_zip" size="5" />

                        <?php if(ShoprocketSetting::getValue('international_sales')): ?>
                          <select name="shipping_country_code" class="ShoprocketCountrySelect">
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

                        <input type="submit" name="updateCart" value="Calculate Shipping" id="shipping_submit" class="ShoprocketButtonSecondaryWidget" />
                      </th>
                    </tr>
                  <?php endif; ?>

                <?php  else: ?>
                  <?php if(count($shippingMethods)): ?>
                    <tr>
                      <th colspan="2"><?php _e( 'Shipping Method' , 'Shoprocket' ); ?><span class="ShoprocketMetaSep">: </span> 
                        
                        <?php if(ShoprocketSetting::getValue('international_sales')): ?>
                          <select name="shipping_country_code" id="widget_shipping_country_code">
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
                          <input type="hidden" name="shipping_country_code" value="<?php echo ShoprocketCommon::getHomeCountryCode(); ?>" id="widget_shipping_country_code">
                        <?php endif; ?>
                        <select name='shipping_method_id' id='widget_shipping_method_id' class="ShoprocketShippingMethodSelect">
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
                          <option class="<?php echo trim($method_class); ?>" value='<?php echo $id ?>' <?php echo ($id == ShoprocketSession::get('ShoprocketCart')->getShippingMethodId())? 'selected' : ''; ?>><?php echo $name ?></option>
                          <?php endforeach; ?>
                        </select>
                      </th>
                    </tr>
                  <?php endif; ?>
                <?php endif; ?>
                
            <?php endif; ?>
              
          <?php if($tax > 0): ?>
            <tr class="tax">
              <td colspan="2"><?php _e( 'Tax' , 'Shoprocket' ); ?><span class="ShoprocketMetaSep">:</span>
              <span class="ShoprocketTaxCost"><?php echo ShoprocketCommon::currency($tax); ?></span></td>
            </tr>
          <?php endif; ?>
        
        
      </table>
      </form>
      <div class="ShoprocketWidgetViewCartCheckoutItems">
        <a class="ShoprocketWidgetViewCart" href='<?php echo get_permalink($data['cartPage']->ID) ?>'><?php _e('View Cart', 'Shoprocket'); ?></a> | <a class="ShoprocketWidgetViewCheckout" href='<?php echo get_permalink($data['checkoutPage']->ID) ?>'><?php _e('Checkout', 'Shoprocket'); ?></a>
      </div>
    </div>
    <div class="ShoprocketWidgetViewCartCheckoutEmpty"<?php if($data['numItems']): ?> style="display:none;"<?php endif; ?>>
      <p class="ShoprocketWidgetCartEmpty"><?php _e( 'You have', 'Shoprocket' ); ?> <?php echo $data['numItems']; ?> <?php echo _n('item', 'items', $data['numItems'], 'Shoprocket'); ?>  <?php _e( 'in your shopping cart' , 'Shoprocket' ); ?>.
        <a class="ShoprocketWidgetViewCart" href='<?php echo get_permalink($data['cartPage']->ID) ?>'><?php _e( 'View Cart' , 'Shoprocket' ); ?></a>
      </p>
    </div>


  <script type="text/javascript">
  /* <![CDATA[ */
    (function($){
      $(document).ready(function(){
        var widget_original_methods = $('#widget_shipping_method_id').html();
        var widget_selected_country = $('#widget_shipping_country_code').val();
        $('.methods-country').each(function() {
          if(!$(this).hasClass(widget_selected_country) && !$(this).hasClass('all-countries') && !$(this).hasClass('select')) {
            $(this).remove();
          }
        });
        $('#widget_shipping_country_code').change(function() {
          var widget_selected_country = $(this).val();
          $('#widget_shipping_method_id').html(widget_original_methods);
          $('.methods-country').each(function() {
            if(!$(this).hasClass(widget_selected_country) && !$(this).hasClass('all-countries') && !$(this).hasClass('select')) {
              $(this).remove();
            }
          });
        });
        
        $('#widget_shipping_method_id').change(function() {
          $('#ShoprocketWidgetCartForm').submit();
        });

        $('#widget_live_rates').change(function() {
          $('#ShoprocketWidgetCartForm').submit();
        });

        $('#widget_change_shipping_zip_link').click(function() {
          $('#widget_set_shipping_zip_row').toggle();
          return false;
        });
      })
    })(jQuery);
  /* ]]> */
  </script>

<?php echo $data['afterWidget']; ?>