var ajaxManager = (function() {
  $jq = jQuery.noConflict();
  var requests = [];
  
  return {
    addReq: function(opt) {
      requests.push(opt);
    },
    removeReq: function(opt) {
      if($jq.inArray(opt, requests) > -1) {
        requests.splice($jq.inArray(opt, requests), 1);
      }
    },
    run: function() {
      var self = this, orgSuc;
      
      if(requests.length) {
        oriSuc = requests[0].complete;
      
        requests[0].complete = function() {
          if(typeof oriSuc === 'function') {
            oriSuc();
          }
          requests.shift();
          self.run.apply(self, []);
        };   

        $jq.ajax(requests[0]);
      } else {
        self.tid = setTimeout(function() {
          self.run.apply(self, []);
        }, 1000);
      }
    },
    stop:  function() {
      requests = [];
      clearTimeout(this.tid);
    }
  };
}());
ajaxManager.run();
(function($){
  $(document).ready(function(){
    $('.purAddToCart, .purAddToCartImage').click(function() {
      $(this).attr('disabled', 'disabled');
    })
    $('.ShoprocketAjaxWarning').hide();
    $('.ajax-button').click(function() {
      $(this).attr('disabled', true);
      var id = $(this).attr('id').replace('addToCart_', '');
      $('#task_' + id).val('ajax');
      var product = C66.products[id];
      if(C66.trackSync) {
        SyncCheck(id, C66.ajaxurl, product.ajax, product.name, product.returnUrl, product.addingText);
      }
      else {
        if(product.ajax === 'no') {
          $('#task_' + id).val('addToCart');
          $('#cartButtonForm_' + id).submit();
          return false;
        }
        else if(product.ajax === 'yes' || product.ajax === 'true') {
          buttonTransform(id, C66.ajaxurl, product.name, product.returnUrl, product.addingText);
        }
      }
      return false;
    });
    $('.modalClose').click(function() {
      $('.ShoprocketUnavailable, .ShoprocketWarning, .ShoprocketError, .alert-message').fadeOut(800);
    });
    
    $('#ShoprocketCancelPayPalSubscription').click(function() {
      return confirm('Are you sure you want to cancel your subscription?\n');
    });
    
    var original_methods = $('#shipping_method_id').html();
    var selected_country = $('#shipping_country_code').val();
    $('.methods-country').each(function() {
      if(!$(this).hasClass(selected_country) && !$(this).hasClass('all-countries') && !$(this).hasClass('select')) {
        $(this).remove();
      }
    });
    $('#shipping_country_code').change(function() {
      var selected_country = $(this).val();
      $('#shipping_method_id').html(original_methods);
      $('.methods-country').each(function() {
        if(!$(this).hasClass(selected_country) && !$(this).hasClass('all-countries') && !$(this).hasClass('select')) {
          $(this).remove();
        }
      });
    });
    
    $('#shipping_method_id').change(function() {
      $('#ShoprocketCartForm').submit();
    });
    
    $('#live_rates').change(function() {
      $('#ShoprocketCartForm').submit();
    });
    
    $('.showEntriesLink').click(function() {
      var panel = $(this).attr('rel');
      $('#' + panel).toggle();
      return false;
    });
    
    $('#change_shipping_zip_link').click(function() {
      $('#set_shipping_zip_row').toggle();
      return false;
    });
    
  })
})(jQuery);

function getCartButtonFormData(formId) {
  $jq = jQuery.noConflict();
  var theForm = $jq('#' + formId);
  var str = '';
  $jq('input:not([type=checkbox], :radio), input[type=checkbox]:checked, input:radio:checked, select, textarea', theForm).each(
    function() {
      var name = $jq(this).attr('name');
      var val = $jq(this).val();
      str += name + '=' + encodeURIComponent(val) + '&';
    }
  );

  return str.substring(0, str.length-1);
}

function SyncCheck(formId, ajaxurl, useAjax, productName, productUrl, addingText) {
  $jq = jQuery.noConflict();
  var mydata = getCartButtonFormData('cartButtonForm_' + formId);
  ajaxManager.addReq({
    type: "POST",
    url: ajaxurl + '=1',
    data: mydata,
    dataType: 'json',
    success: function(response) {
      if(response[0]) {
        $jq('#task_' + formId).val('addToCart');
        if(useAjax == 'no') {
          $jq('#cartButtonForm_' + formId).submit();
        }
        else {
          buttonTransform(formId, ajaxurl, productName, productUrl, addingText);
        }
      }
      else {
        $jq('.modalClose').show();
        $jq('#stock_message_box_' + formId).fadeIn(300);
        $jq('#stock_message_' + formId).html(response[1]);
        $jq('#addToCart_' + formId).removeAttr('disabled');
      }
    },
    error: function(xhr,err){
      alert("readyState: "+xhr.readyState+"\nstatus: "+xhr.status);
    }
  });
}

function addToCartAjax(formId, ajaxurl, productName, productUrl, buttonText) {
  $jq = jQuery.noConflict();
  var options1 = $jq('#cartButtonForm_' + formId + ' .shoprocketOptions.options_1').val();
  var options2 = $jq('#cartButtonForm_' + formId + ' .shoprocketOptions.options_2').val();
  var itemQuantity = $jq('#ShoprocketUserQuantityInput_' + formId).val();
  var itemUserPrice = $jq('#ShoprocketUserPriceInput_' + formId).val();
  var cleanProductId = formId.split('_');
  cleanProductId = cleanProductId[0];
  var data = {
    shoprocketItemId: cleanProductId,
    itemName: productName,
    options_1: options1,
    options_2: options2,
    item_quantity: itemQuantity,
    item_user_price: itemUserPrice,
    product_url: productUrl
  };
  
  ajaxManager.addReq({
    type: "POST",
    url: ajaxurl + '=2',
    data: data,
    dataType: 'json',
    success: function(response) {
      $jq('#addToCart_' + formId).removeAttr('disabled');
      $jq('#addToCart_' + formId).removeClass('ajaxPurAddToCart');
      $jq('#addToCart_' + formId).val(buttonText);
      $jq.hookExecute('addToCartAjaxHook', response);
      ajaxUpdateCartWidgets(ajaxurl);
      if($jq('.customAjaxAddToCartMessage').length > 0) {
        $jq('.customAjaxAddToCartMessage').show().html(response.msg);
        $jq.hookExecute('customAjaxAddToCartMessage', response);
      }
      else {
        if((response.msgId) == 0){
          $jq('.success_' + formId).fadeIn(300);
          $jq('.success_message_' + formId).html(response.msg);
          if(typeof response.msgHeader !== 'undefined') {
            $jq('.success' + formId + ' .message-header').html(response.msgHeader);
          }
          $jq('.success_' + formId).delay(2000).fadeOut(300);
        }
        if((response.msgId) == -1){
          $jq('.warning_' + formId).fadeIn(300);
          $jq('.warning_message_' + formId).html(response.msg);
          if(typeof response.msgHeader !== 'undefined') {
            $jq('.warning' + formId + ' .message-header').html(response.msgHeader);
          }
        }
        if((response.msgId) == -2){
          $jq('.error_' + formId).fadeIn(300);
          $jq('.error_message_' + formId).html(response.msg);
          if(typeof response.msgHeader !== 'undefined') {
            $jq('.error_' + formId + ' .message-header').html(response.msgHeader);
          }
        }
      }
    }
  })
}
function buttonTransform(formId, ajaxurl, productName, productUrl, addingText) {
  $jq = jQuery.noConflict();
  var buttonText = $jq('#addToCart_' + formId).val();
  $jq('#addToCart_' + formId).attr('disabled', 'disabled');
  $jq('#addToCart_' + formId).addClass('ajaxPurAddToCart');
  $jq('#addToCart_' + formId).val(addingText);
  addToCartAjax(formId, ajaxurl, productName, productUrl, buttonText);
}
function ajaxUpdateCartWidgets(ajaxurl) {
  $jq = jQuery.noConflict();
  var widgetId = $jq('.ShoprocketCartWidget').attr('id');
  var data = {
    action: "ajax_cart_elements"
  };
  ajaxManager.addReq({
    type: "POST",
    url: ajaxurl + '=3',
    data: data,
    dataType: 'json',
    success: function(response) {
      $jq.hookExecute('cartElementsAjaxHook', response);
      $jq('#ShoprocketAdvancedSidebarAjax, #ShoprocketWidgetCartContents').show();
      $jq('.ShoprocketWidgetViewCartCheckoutEmpty, #ShoprocketWidgetCartEmpty').hide();
      $jq('#ShoprocketWidgetCartLink').each(function(){
        widgetContent = "<span id=\"ShoprocketWidgetCartCount\">" + response.summary.count + "</span>";
        widgetContent += "<span id=\"ShoprocketWidgetCartCountText\">" + response.summary.items + "</span>";
        widgetContent += "<span id=\"ShoprocketWidgetCartCountDash\"> – </span>"
        widgetContent += "<span id=\"ShoprocketWidgetCartPrice\">" + response.summary.amount + "</span>";
        $jq(this).html(widgetContent).fadeIn('slow');
      });
      $jq('.ShoprocketRequireShipping').each(function(){
        if(response.shipping == 1) {
          $jq(this).show();
        }
      })
      $jq('#ShoprocketWidgetCartEmptyAdvanced').each(function(){
        widgetContent = C66.youHave + ' ' + response.summary.count + " " + response.summary.items + " (" + response.summary.amount + ") " + C66.inYourShoppingCart;
        $jq(this).html(widgetContent).fadeIn('slow');
      });
      $jq("#ShoprocketAdvancedWidgetCartTable .product_items").remove();
      $jq.each(response.products.reverse(), function(index, array){  
        widgetContent = "<tr class=\"product_items\"><td>";
        widgetContent += "<span class=\"ShoprocketProductTitle\">" + array.productName + "</span>";
        widgetContent += "<span class=\"ShoprocketQuanPrice\">";
        widgetContent += "<span class=\"ShoprocketProductQuantity\">" + array.productQuantity + "</span>";
        widgetContent += "<span class=\"ShoprocketMetaSep\"> x </span>"; 
        widgetContent += "<span class=\"ShoprocketProductPrice\">" + array.productPrice + "</span>";
        widgetContent += "</span>";
        widgetContent += "</td><td class=\"ShoprocketProductSubtotalColumn\">";
        widgetContent += "<span class=\"ShoprocketProductSubtotal\">" + array.productSubtotal + "</span>";
        widgetContent += "</td></tr>";
        $jq("#ShoprocketAdvancedWidgetCartTable tbody").prepend(widgetContent).fadeIn("slow");  
      });
      $jq('.ShoprocketSubtotal').each(function(){
        $jq(this).html(response.subtotal)
      });
      $jq('.ShoprocketShipping').each(function(){
        $jq(this).html(response.shippingAmount)
      });
    }
  })
}

jQuery.extend({ 
  hookExecute: function (function_name, response){
    if (typeof window[function_name] == "function"){
      window[function_name](response);
      return true;
    }
    else{
      return false;
    }
  }
});