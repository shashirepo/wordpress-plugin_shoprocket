<?php
$promo = new ShoprocketPromotion();
$errorMessage = false;


if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['shoprocket-action'] == 'save promotion') {
  $_POST['promo']['amount'] = isset($_POST['promo']['amount']) ? ShoprocketCommon::convert_currency_to_number($_POST['promo']['amount']) : '';
  $_POST['promo']['min_order'] = isset($_POST['promo']['min_order']) ? ShoprocketCommon::convert_currency_to_number($_POST['promo']['min_order']) : '';
  $_POST['promo']['max_order'] = isset($_POST['promo']['max_order']) ? ShoprocketCommon::convert_currency_to_number($_POST['promo']['max_order']) : '';
  try {
    $promo->load($_POST['promo']['id']);
    $promo->setData($_POST['promo']);
    $promo->save();
    $promo->clear();
  }
  catch(ShoprocketException $e) {
    $errorCode = $e->getCode();
    if($errorCode == 66301) {
      // Promotion save failed
      $errors = $promo->getErrors();
      $errorMessage = ShoprocketCommon::showErrors($errors, "<p><b>" . __("The promotion could not be saved for the following reasons","shoprocket") . ":</b></p>");
    }
    ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Promotion save failed ($errorCode): " . strip_tags($errorMessage));
  }
}
elseif(isset($_GET['task']) && $_GET['task'] == 'edit' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = ShoprocketCommon::getVal('id');
  $promo->load($id);
}
elseif(isset($_GET['task']) && $_GET['task'] == 'delete' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = ShoprocketCommon::getVal('id');
  $promo->load($id);
  $promo->deleteMe();
  $promo->clear();
}
?>


<h2><?php _e('Shoprocket Promotions', 'shoprocket'); ?></h2>
<div class='wrap' id="promotions">
<?php if($errorMessage): ?>
<div class="errormsg"><?php echo $errorMessage ?></div>
<?php endif; ?>

  <div id="widgets-left">
    <div id="available-widgets">
    
      <div class="widgets-holder-wrap">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3><?php _e( 'Promotion' , 'shoprocket' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
            <form action="admin.php?page=shoprocket-promotions" method='post'>
              <input type='hidden' name='shoprocket-action' value='save promotion' />
              <input type='hidden' name='promo[id]' value='<?php echo $promo->id ?>' />
              <ul>
                <li>
                  <label class="long" for="promo-name"><?php _e( 'Promotion name' , 'shoprocket' ); ?>:</label>
                  <input type='text' name='promo[name]' id='promo-name' value='<?php echo ($promo->name) ? $promo->name : $promo->getCodeAt(); ?>' />
                  <span class="label_desc"><?php _e('Promotion name required', 'shoprocket'); ?>.</span>
                </li>
                <li>
                  <label class="long" for="promo-code"><?php _e( 'Promotion code' , 'shoprocket' ); ?>:</label>
                  <input type='text' class="large" name='promo[code]' id='promo-code' value='<?php echo $promo->getCode(false,true); ?>' />
                  <span class="label_desc"><?php _e('Unique promotion code required', 'shoprocket'); ?>.</span>
                </li>
                <li>
                  <label class="long" for="promo-type"><?php _e( 'Type of promotion' , 'shoprocket' ); ?>:</label>
                  <select name="promo[type]" class="promo-type" id="promo-type">
                    <option value="dollar" <?php if($promo->type == 'dollar') { echo 'selected'; } ?>><?php _e( 'Money Amount' , 'shoprocket' ); ?></option>
                    <option value="percentage" <?php if($promo->type == 'percentage') { echo 'selected'; } ?>><?php _e( 'Percentage' , 'shoprocket' ); ?></option>
                  </select>
                  <span class="label_desc"><?php _e('Select if this promotion will be applied using a specific dollar amount or a percentage of the total', 'shoprocket'); ?>.</span>
                </li>
                  <li>
                    <label class="long" for="promo-apply_to"><?php _e( 'Apply to' , 'shoprocket' ); ?>:</label>
                    <select name="promo[apply_to]" class="promo-apply_to" id="promo-apply_to">
                      <option value="products" <?php if($promo->apply_to == 'products') { echo 'selected'; } ?>><?php _e( 'Products' , 'shoprocket' ); ?></option>
                      <option value="shipping" <?php if($promo->apply_to == 'shipping') { echo 'selected'; } ?>><?php _e( 'Shipping' , 'shoprocket' ); ?></option>
                      <option value="subtotal" <?php if($promo->apply_to == 'subtotal') { echo 'selected'; } ?>><?php _e( 'Subtotal' , 'shoprocket' ); ?></option>
                      <option value="total" <?php if($promo->apply_to == 'total') { echo 'selected'; } ?>><?php _e( 'Grand Total' , 'shoprocket' ); ?></option>
                    </select>
                    <span class="label_desc"><?php _e('Select if this promotion will apply to specific products, shipping, products subtotal or the entire cart total', 'shoprocket'); ?>.</span>
                  </li>
                <li>
                  <label class="long" for="promo-amount"><?php _e( 'Amount' , 'shoprocket' ); ?>:</label>
                  <span class="dollarSign"><?php echo ShoprocketCommon::currencySymbol('before'); ?></span>
                  <input type="text" name="promo[amount]" id="promo-amount" value="<?php echo $promo->id > 0 ? ($promo->type == 'percentage' ? $promo->amount : ShoprocketCommon::currency($promo->amount, true, false, false)) : ''; ?>">
                  <span class="dollarSign"><?php echo ShoprocketCommon::currencySymbol('after'); ?></span>
                  <span class="percentSign">%</span>
                  <span class="label_desc"><?php _e('Set the promotion amount', 'shoprocket'); ?>.</span>
                </li>
                <li>
                  <div class="desc"><?php _e('Required Amount', 'shoprocket'); ?>:</div>
                  <div class="dateRange">
                  <div class="group">
                    <label for="promo-min_order"><?php _e( 'Minimum order amount' , 'shoprocket' ); ?>:</label>
                    <?php echo ShoprocketCommon::currencySymbol('before'); ?> <input type="text" id="promo-min_order" name="promo[min_order]" value="<?php echo $promo->id > 0 ? ShoprocketCommon::currency($promo->minOrder, true, false, false) : ''; ?>"> <?php echo ShoprocketCommon::currencySymbol('after'); ?>
                  </div>
                  <div class="group">
                    <label for="promo-max_order"><?php _e( 'Maximum order amount' , 'shoprocket' ); ?>:</label>
                    <?php echo ShoprocketCommon::currencySymbol('before'); ?> <input type="text" id="promo-max_order" name="promo[max_order]" value="<?php echo $promo->id > 0 ? ShoprocketCommon::currency($promo->maxOrder, true, false, false) : ''; ?>"> <?php echo ShoprocketCommon::currencySymbol('after'); ?>
                  </div>
                  <span class="label_desc"><?php _e('Set the minimum and/or maximum amount required for this promotion to apply', 'shoprocket'); ?>.</span>
                  </div>
                </li>
                <li>
                  <div class="desc"><?php _e('Required Quantity', 'shoprocket'); ?>:</div>
                  <div class="dateRange">
                  <div class="group">
                    <label for="promo-min_quantity"><?php _e( 'Minimum quantity' , 'shoprocket' ); ?>:</label>
                    <input type="text" id="promo-min_quantity" name="promo[min_quantity]" value="<?php echo ($promo->minQuantity == null) ? "" : $promo->minQuantity; ?>">
                  </div>
                  <div class="group">
                    <label for="promo-max_quantity"><?php _e( 'Maximum quantity' , 'shoprocket' ); ?>:</label>
                    <input type="text" id="promo-max_quantity" name="promo[max_quantity]" value="<?php echo ($promo->maxQuantity == null) ? "" : $promo->maxQuantity; ?>">
                  </div>
                  <span class="label_desc"><?php _e('Set the minimum and/or maximum quantity required for this promotion to apply', 'shoprocket'); ?>.</span>
                  </div>
                </li>
                <li>
                  <div class="desc"><?php _e('Date range', 'shoprocket'); ?>:</div>
                  <div class="dateRange">
                  <div class="group">
                  <label for="from"><?php _e( 'From' , 'shoprocket' ); ?></label>
                  <input type='text' name='promo[effective_from]' id='from' class='from' value='<?php echo (!empty($promo->effective_from) && $promo->effective_from != "0000-00-00 00:00:00") ? date("m/d/Y h:i a",strtotime($promo->effective_from)) : ''; ?>' />
                  </div>
                    <div class="group">
                  <label for="to"><?php _e( 'To' , 'shoprocket' ); ?></label>
                  <input type='text' name='promo[effective_to]' id='to' class='to' value='<?php echo (!empty($promo->effective_to) && $promo->effective_to != "0000-00-00 00:00:00") ? date("m/d/Y h:i a",strtotime($promo->effective_to)) : ''; ?>' />
                  </div>
                    <span class="label_desc"><?php _e('Select the date and time that this promotion will start and end. You may leave either blank', 'shoprocket'); ?>.</span>
                  </div>
                </li>
                <li>
                  <label class="long" for="promo-maximum_redemptions"><?php _e( 'Maximum redemptions' , 'shoprocket' ); ?>:</label>
                  <input type='text' name='promo[maximum_redemptions]' id='promo-maximum_redemptions' value='<?php echo ($promo->maximum_redemptions == 0) ? "" : $promo->maximum_redemptions; ?>' />  
                  <?php if($promo->id > 0): ?>
                  <?php _e( 'Used' , 'shoprocket' ); ?>:</label>
                      <strong><?php 
                      if($promo->redemptions != null) {
                        echo $promo->redemptions; ?> <?php echo ($promo->redemptions == 1) ? 'time' : 'times';
                      } else {
                        echo __('Never', 'shoprocket'); 
                      } ?></strong>
                  <?php endif; ?>
                  
                  <span class="label_desc"><?php _e('Set the maximum number of times this promotion can be redeemed', 'shoprocket'); ?>.</span>
                </li>
                <li>
                  <label class="long" for="promo-max_uses_per_order"><?php _e( 'Maximum redemptions per order' , 'shoprocket' ); ?>:</label>
                  <input type='text' name='promo[max_uses_per_order]' id='promo-max_uses_per_order' value='<?php echo ($promo->max_uses_per_order == 0) ? "" : $promo->max_uses_per_order; ?>' />
                  <span class="label_desc"><?php _e('Set the maximum number of times this promotion can be redeemed per order', 'shoprocket'); ?>.</span>
                </li>
                <li>
                  <label class="long" for="promo-products"><?php _e( 'Products' , 'shoprocket' ); ?>:</label>
                  <input type="radio" name="promo[exclude_from_products]" id="promo-exclude_from_products_no" value="" <?php echo ($promo->exclude_from_products != 1) ? 'checked="checked"' : ''; ?>/> <?php _e('Apply to the following products', 'shoprocket'); ?>
                  <input type="radio" name="promo[exclude_from_products]" id="promo-exclude_from_products_yes" value="1" <?php echo ($promo->exclude_from_products == 1) ? 'checked="checked"' : ''; ?>/> <?php _e('Exclude the following products', 'shoprocket'); ?>
                </li>
                <li>
                  <input type="text" id="promo-products" name="promo[products]" value='<?php echo $promo->products ?>' />
                  <input type='hidden' name='promo-products-hidden' id="promo-products-hidden" value='<?php echo $promo->products ?>' /><br />
                  <p class="label_desc"><?php _e('Enter the names of products in your Sync that this promotion will either be be applied to or that will be excluded from this promotion.  You can enter as many products as you want.  If you want this promotion to apply to all orders, leave this field blank.  If you have selected to apply this promotion to shipping or the cart total and are including these products, this promotion will only apply if the products in this list match the products in the cart and vice versa.', 'shoprocket'); ?></p>
                </li>
                <li>
                  <div class="desc"><?php _e('Additional settings', 'shoprocket'); ?>:</div>
                  <div class="collection checkbox">
                    <input type="hidden" name='promo[enable]' value="" />  
                    <input type="checkbox" name='promo[enable]' id='promo-enable' value="1" <?php echo ($promo->enable == '1' || !isset($promo->id)) ? 'checked="checked"' : ''; ?> />
                    <label for="promo-enable"><?php _e( 'Enable' , 'shoprocket' ); ?>?</label>
                    <span class="label_desc"><?php _e('Do you want this promotion to be valid', 'shoprocket'); ?>?</span>
                    <input type="hidden" name='promo[auto_apply]' value="" />  
                    <input type="checkbox" name='promo[auto_apply]' id='promo-auto_apply' value="1" <?php echo ($promo->auto_apply == '1') ? 'checked="checked"' : ''; ?> />
                    <label for="promo-auto_apply"><?php _e( 'Auto Apply' , 'shoprocket' ); ?>?</label>
                    <span class="label_desc"><?php _e('Do you want this promotion to automatically apply when all conditions are met? (no user input required)', 'shoprocket'); ?></span>
                    </div>
                  </li>

                </li>
                <li>
                  <div class="buttons">
                  <?php if($promo->id > 0): ?>
                    <a href='?page=shoprocket-promotions' class='button-secondary linkButton'><?php _e( 'Cancel' , 'shoprocket' ); ?></a>
                  <?php endif; ?>
                  <input type='submit' name='submit' class="button-primary" value='<?php _e('Save', 'shoprocket'); ?>' />
                  </div>
                </li>
              </ul>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <table class="widefat ShoprocketHighlightTable" id="promotions_table">
    <tr>
      <thead>
      	<tr>
      		<th><?php _e('ID', 'shoprocket'); ?></th>
    			<th><?php _e('Name', 'shoprocket'); ?></th>
    			<th><?php _e('Code', 'shoprocket'); ?></th>
    			<th><?php _e('Amount', 'shoprocket'); ?></th>
    			<th><?php _e('Minimum Order', 'shoprocket'); ?></th>
    			<th><?php _e('Enabled', 'shoprocket'); ?></th>
    			<th><?php _e('Effective', 'shoprocket'); ?></th>
    			<th><?php _e('Used', 'shoprocket'); ?></th>
    			<th><?php _e('Apply To', 'shoprocket'); ?></th>
    			<th><?php _e('Actions', 'shoprocket'); ?></th>
      	</tr>
      </thead>
      <tfoot>
      	<tr>
      		<th><?php _e('ID', 'shoprocket'); ?></th>
    			<th><?php _e('Name', 'shoprocket'); ?></th>
    			<th><?php _e('Code', 'shoprocket'); ?></th>
    			<th><?php _e('Amount', 'shoprocket'); ?></th>
    			<th><?php _e('Minimum Order', 'shoprocket'); ?></th>
    			<th><?php _e('Enabled', 'shoprocket'); ?></th>
    			<th><?php _e('Effective', 'shoprocket'); ?></th>
    			<th><?php _e('Used', 'shoprocket'); ?></th>
    			<th><?php _e('Apply To', 'shoprocket'); ?></th>
    			<th><?php _e('Actions', 'shoprocket'); ?></th>
      	</tr>
      </tfoot>
      <tbody>
        
      </tbody>
    </tr>
  </table>
</div>
<script type="text/javascript">
/* <![CDATA[ */
  (function($){
    $(document).ready(function(){
      $('#promo-products').tokenInput(productSearchUrl, { theme: 'facebook', hintText: 'Type in a product name',
        onReady: function() { 
          var data = {
            action: 'loadPromotionProducts',
            productId: $('#promo-products-hidden').val()
          };
          $.post(ajaxurl + '?action=loadPromotionProducts', data, function(results) {
            for(var i=0; i<results.length; i++) {
              var item = results[i];
              if ((item.id) !== '') {
                $('#promo-products').tokenInput('add', {id: item.id, name: item.name});
              }
            }
          }, 'json');
        }
      });
      
      $('#promotions_table').dataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bPagination": true,
        "iDisplayLength": 30,
        "aLengthMenu": [[30, 60, 150, -1], [30, 60, 150, "All"]],
        "sPaginationType": "bootstrap",
        "bAutoWidth": false,
        "sAjaxSource": ajaxurl + "?action=promotions_table",
        "aaSorting": [[8,'desc']],
        "aoColumns": [
          null, 
          {
            "bsortable": true,
            "fnRender": function(oObj) {
              return "<a href=\"?page=shoprocket-promotions&task=edit&id=" + oObj.aData[0] + "\">" + oObj.aData[1] + "</a>"
            }
          },
          null, null, 
          { "bSearchable": false }, 
          { "bSearchable": false }, 
          { "bSearchable": false }, 
          { "bSearchable": false }, 
          null, 
          {
            "mData": null,
            "bSearchable": false,
            "bSortable": false,
            "fnRender": function(oObj) {
              return "<a href=\"?page=shoprocket-promotions&task=edit&id=" + oObj.aData[0] + "\"><?php _e( 'Edit' , 'shoprocket' ); ?></a> | <a class=\"delete\" href=\"?page=shoprocket-promotions&task=delete&id=" + oObj.aData[0] + "\"><?php _e( 'Delete' , 'shoprocket' ); ?></a>"
            }
          }
        ],
        "oLanguage": { 
          "sZeroRecords": "<?php _e('No matching Promotions found', 'shoprocket'); ?>", 
          "sSearch": "<?php _e('Search', 'shoprocket'); ?>:", 
          "sInfo": "<?php _e('Showing', 'shoprocket'); ?> _START_ <?php _e('to', 'shoprocket'); ?> _END_ <?php _e('of', 'shoprocket'); ?> _TOTAL_ <?php _e('entries', 'shoprocket'); ?>", 
          "sInfoEmpty": "<?php _e('Showing 0 to 0 of 0 entries', 'shoprocket'); ?>", 
          "oPaginate": {
            "sNext": "<?php _e('Next', 'shoprocket'); ?>", 
            "sPrevious": "<?php _e('Previous', 'shoprocket'); ?>", 
            "sLast": "<?php _e('Last', 'shoprocket'); ?>", 
            "sFirst": "<?php _e('First', 'shoprocket'); ?>"
          }, 
          "sInfoFiltered": "(<?php _e('filtered from', 'shoprocket'); ?> _MAX_ <?php _e('total entries', 'shoprocket'); ?>)", 
          "sLengthMenu": "<?php _e('Show', 'shoprocket'); ?> _MENU_ <?php _e('entries', 'shoprocket'); ?>", 
          "sLoadingRecords": "<?php _e('Loading', 'shoprocket'); ?>...", 
          "sProcessing": "<?php _e('Processing', 'shoprocket'); ?>..." 
        }
      });
      $(".promo-rows tr:nth-child(even)").css("background-color", "#fff");
      setPromoSign();
      $('#promo-type').change(function () {
        setPromoSign();
      });
      function setPromoSign() {
        var v = $('#promo-type').val();
        if(v == 'percentage') {
          $('.dollarSign').hide();
          $('.percentSign').show();
        }
        else {
          $('.dollarSign').show();
          $('.percentSign').hide();
        }
      }
      $('.sidebar-name').click(function() {
        $(this.parentNode).toggleClass("closed");
      });
      $(".from").datetimepicker({ changeMonth: true, numberOfMonths: 2, ampm: true})
      $(".to").datetimepicker({ changeMonth: true, numberOfMonths: 2, ampm: true, hour: 23, minute: 59 })
      $('#ShoprocketAccountSearchField').quicksearch('table tbody tr');
    })
    $(document).on('click', '.delete', function(e) {
      return confirm('Are you sure you want to delete this item?');
    });
    function productSearchUrl() {
      var url = ajaxurl + '?action=promotionProductSearch';
      return url;
    }
  })(jQuery);
/* ]]> */
</script>