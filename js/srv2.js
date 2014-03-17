var debug = 1;  // debug
var total = 0;
var productid = 0;
var productname = '';
var	productprice  = '';
var	productslug   = '';
var	productoption = '';
var productquantitycount = 0;
var productquantity = 0;

var checkstock  = 1;
var parentcallback = ''; //hold the call back information.
var companyid = ''; //hold the company id
var ajaxdata = ''; //hold the ajax respponse
var currencysymbol = '';
var taxamount = 0;
var fetcharray = []; //hold a var for the urls to fetch.
var ajaxcallback = [];
var fetcharraycounter = 0;
var usestripelive = 1;
var usestripe = 1;
var usestripeaddress = 1;
var stripecurrency = '';
var	stripelivekey = '';
var	stripeaccesstoken = '';
var	stripepublishablekey = '';
var stripelivekey = 'pk_live_7DyP7IO0U9pjqHuZ0tydjltB';
var stripetestkey = 'pk_test_aVXccYye8uoCBaeQwcHJHbBX';
var stripepercentage = 2.4;
var stripename ='Stripe Order';
var stripedesc = 'Stripe Desc';
var displaycurrency = '';


var shipping = '';
var shippingprice = 0;
var	shippingname = '';
var	shippingdetails = '';
var	vouchercodes = '';
var voucherapplied = 0;  // hold if the voucher has been applied or not
var voucheramountoff  = 0;
var voucherpercentoff = 0;
var maxquantity = 10;

var shippingdropdown = '';
var	usepaypal = 0;
var	paypalemail = '';
var	paypalordertext = '';
var	paypalcurrency = '';
var paypalcancelurl = document.location.toString();
var paypalreturnurl =document.location.toString();
if (paypalcancelurl.toLowerCase().indexOf("?") >= 0)
{
	paypalcancelurl = paypalcancelurl+'&paypal=cancel';
	paypalreturnurl = paypalreturnurl+'&paypal=response';
}
else
{
	paypalcancelurl = paypalcancelurl+'?paypal=cancel';
	paypalreturnurl = paypalreturnurl+'?paypal=response';

}

var translationcode = '';


//a few different types.
//var srurl = 'http://shoprocket:8888/index.php?/rest/';
//var srurl = 'http://shoprocket:8888/index.php?/rest/';
//var resourcesurl = 'http://shoprocket:8888/static/frontend/';
//var srurl = 'http://shoprocketstaging.eu01.aws.af.cm/index.php?/rest/';
//var resourcesurl = 'http://shoprocketstaging.eu01.aws.af.cm/static/frontend/';
//var srurl = 'http://shoprocketco-env-mptkqw4sx7.elasticbeanstalk.com/index.php?/rest/';
//var srurl = 'http://www.shoprocket.co/index.php?/rest/';
//var srurl = 'http://localhost/shoprocket.co/index.php?/rest/';
//var resourcesurl = 'http://www.shoprocket.co/static/frontend/';
//var resourcesurl = 'http://localhost/shoprocket.co/static/frontend/';
var srurl = "http://rest.shoprocket.co/index.php?/rest/";
//set the site url
var siteurl = '';
if (document.location.toString().toLowerCase().indexOf("localhost") >= 0) { 
	var resourcesurl = 'http://localhost/shoprocket.co/static/frontend/'; 
	 siteurl = '';
	}	else	{ 
	var resourcesurl = 'http://space.shoprocket.co/'; 
	 siteurl = '';
} 


//build the modals
function buildModals()
{

	$("head").append('<link href="'+resourcesurl+'css/sr.css" rel="stylesheet">');	
	$("head").append('<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">');	
//http://localhost/shoprocket.co/static/frontend/
//
	$.getScript("http://rest.shoprocket.co/static/frontend/js/srmodals.js", function(){
   		// Here you can use anything you defined in the loaded script
   		$( "body" ).append( cartmodal);
		$( "body" ).append( basketmodal);
		$( "body" ).append( outofstockmodal);
		$( "body" ).append( paypalmodal);
	});
	

}



//this holds the check responses such as show modals etc.
function checkPageResponses()
{
	//alert('last');
			//alert(usestripe);
		//alert(currencysymbol);	
		//alert('fff'+displaycurrency);
		//"http://localhost/shoprocket.co/static/frontend/
		//http://rest.shoprocket.co/static/frontend/
	var s = $.getScript("http://localhost/shoprocket.co/static/frontend/js/pageresponses.js", function(){
		
		//alert('why now');
		usestripelive = usestripelive;
		usestripe = usestripe;
		//alert(usestripe);
		//alert('eee'+displaycurrency);
		displaycurrency = displaycurrency;
	//	alert('ggg'+displaycurrency);
		
		usestripeaddress = usestripeaddress;
		stripecurrency = stripecurrency;
		stripelivekey = stripelivekey;
		stripeaccesstoken = stripeaccesstoken;
		stripepublishablekey = stripepublishablekey;
		stripelivekey = stripelivekey;
		stripetestkey = stripetestkey;
		stripepercentage = stripepercentage;
		stripename =stripename;
		stripedesc = stripedesc;
		usepaypal = usepaypal;
   		updatebaskettotal();   		
	});	

	
}

function getDetails()
{
	logIt('Fetching Details');
	parentcallback = 'getDetailsDone();';
	fetchsc(srurl+'getdetails/?companyid='+companyid);
}

function processAccountDetails(result)
{
	
	//result d= '';
	if (typeof result.accountdetails[0].shopurl !== 'undefined') 
	{
		shopurl = result.accountdetails[0].shopurl;
		logIt('Setting Shop URL'+shopurl);	
	}
	else
	{
		logIt('Shop URL not set');			
	}
	
	
	//pay pal currency 
	if (typeof result.accountdetails[0].paypalcurrency !== 'undefined') 
	{
		currencysymbol = result.accountdetails[0].paypalcurrency;
		if (currencysymbol == 'EUR')
		{
				displaycurrency = "&euro;";
		}
		if (currencysymbol == 'GBP')
		{
				displaycurrency = "&pound;";
		}
		logIt('Setting Currency URL'+displaycurrency);	
	}
	else
	{
		//default it to pund
		displaycurrency = "&pound;";
		currencysymbol = "GBP";
		logIt('Currency URL not set, using default');			
	}
	
	//alert('dp'+displaycurrency);
	//alert('cr'+currencysymbol);
	

	//tax
	if (typeof result.accountdetails[0].taxamount !== 'undefined') 
	{
		taxamount = result.accountdetails[0].taxamount;
		logIt('Setting Tax amount'+taxamount);	
	}
	else
	{
		logIt('Tax amount not set');
	}
	
	
	usestripe = result.accountdetails[0].usestripe;
	//usestripe=1;
	if (usestripe == 1)
	{
		usestripelive = 1;
		
		stripecurrency = result.accountdetails[0].paypalcurrency;
		stripelivekey = result.accountdetails[0].access_token;
		stripeaccesstoken = result.accountdetails[0].access_token;
		stripepublishablekey = result.accountdetails[0].stripe_publishable_key;
		//alert(stripepublishablekey);
		logIt('Using Stripe');			
		logIt('Stripe Currency:'+stripecurrency);	
		logIt('Stripe Livekey:'+stripelivekey);	
		logIt('Stripe Access Token:'+stripeaccesstoken);	
		logIt('Stripe Pub Key:'+stripepublishablekey);			
	}
	else
	{
		usestripelive = 0;
		
		stripecurrency = '';
		stripelivekey = '';
		stripeaccesstoken = '';
		stripepublishablekey = '';
		//alert(stripepublishablekey);
		logIt('No Stripe key found disabling');				
	}
		//alert("done with that"+usestripe);
	
	//check paypal
	if (typeof result.accountdetails[0].usepaypal !== 'undefined') 
	{	
		
		paypalemail = result.accountdetails[0].paypal;
		if (paypalemail != '')
		{
			usepaypal = 1;
			paypalordertext = result.accountdetails[0].paypalordertext;
			paypalcurrency = result.accountdetails[0].paypalcurrency;
			logIt('Paypal settings:');	
			logIt('Use paypal:'+usepaypal);	
			logIt('Paypal price'+paypalemail);	
			logIt('Paypal Name'+paypalordertext);	
			logIt('Paypal details'+paypalcurrency);					
		}
		else
		{
			usepaypal = 0;
			logIt('No Paypal, disabling');	
		}

		
		//usepaypal = 0;
		//alert(usepaypal);		
	}
	else
	{
		logIt('No Paypal settings set');	
		
	}
	//set the default shippinglogIt('Shipping settings:');	
	
	if (typeof result.shippingdetails[0].shipping !== 'undefined') 
	{
		shippingprice = result.shippingdetails[0].shipping.price;
		//alert(shippingprice);
		shippingname = result.shippingdetails[0].shipping.name;
		shippingdetails = result.shippingdetails;
		logIt('Shipping settings:');	
		logIt('Shipping price'+shippingprice);	
		logIt('Shipping Name'+shippingname);	
		logIt('Shipping details'+shippingdetails);	
	
	}
	else
	{
		logIt('No Shipping settings set');	
		
	}
	
	
	//todo:move to server for processing.
	
	  
	 //check if we need to process pay pal         			
	 res = checkPaypalResponse();
	 res = 1;
	 if (res == 1)
	 {
	 	//TODO:call the paypal process response.
	 	logIt('Adding complete paypal to the array');
	 	//fetcharray.push('completepaypal');
	 }
	
	 //check for replace elements.   SR BLOCK, sr single page,product etc.
	 if ($('.sr-block').length>0)
	 {
	 	logIt('Adding sr block to the array');
	 	fetcharray.push('srblock');
	 }
	 
	 
	 
	 logIt('Looking at Final arrray');
	 checkPageResponses();

	 processajaxqueue();	
}

//when the process is finished
function getDetailsDone()
{
	logIt('Setting account details');
	
	var result = $.parseJSON(ajaxdata);
	logIt(result);
	//result = '';
	if (typeof result.accountdetails === 'undefined') 
	{
		logIt('Could not get account details trying again in 2 seconds');
		setTimeout(function()
		{
			parentcallback = 'getDetailsDone();';
			fetchsc(srurl+'getdetails/?companyid='+companyid);
		},2000);


	}
	else
	{
		processAccountDetails(result);
		//TODO we need to put this on a timer to make sure everything is loaded
	} 
	
}

function processnextajaxitem()
{
	fetcharray.shift();
	//alert(fetcharray[0]);
	processajaxqueue();
}


//sr block is done
function productblockDone()
{
	var result = $.parseJSON(ajaxdata);
	console.log(result);
	//alert('ddd');
	//html = $('.sr-block').html();
	var tmphtml = '';
	var finhtml = '';
	var blankmage =1;

	//console.log(finhtml);
	//$('#sr-block').html('');
	//alert($('.sr-block').html());
	//alert(html);
	
			
			
		var i = 0;	
		//alert(srblockcount);
		//alert(srblockcount);
			//alert('oc'+srblockcount);
			$('.sr-block').each(function(){
			
			if (srblockcount == i)
			{
				//alert($('.sr-block').length);
				//alert('bc'+srblockcount);
				html = $(this).html();
					jQuery.each( result, function( i, val ) {
						tmphtml = html;
						blankimage= 1;
						logIt('Processing Product:');
						logIt(val);
						if (typeof val.images !== 'undefined') 
				 		{
							jQuery.each( val.images, function( i2, val2 ) {
								logIt('Processing Image:');
								logIt(val2);
				 	 			if(val2.hero == 1)
					 	 		{
					 	 			logIt('hero image found '+val2.name);
					 	 			blankimage = 0;
									tmphtml = tmphtml.replace("[IMAGESRC]" ,val2.cdnurl );
					
					 	 		}
				 	 		});
				 			if (blankimage == 1)
							{
								//logIt('hero image nout found using '+val.images[0].name);
								//console.log(val.images);
								tmphtml = tmphtml.replace("[IMAGESRC]" ,'' );
							}
						}
						
						tmphtml = tmphtml.replace("[NAME]" ,val.product.name );
						tmphtml = tmphtml.replace("[PRICE]" ,val.product.price );
						tmphtml = tmphtml.replace("[STRAPLINE]" ,val.product.price );
						tmphtml = tmphtml.replace("[SLUG]" ,val.product.slug );
						finhtml = finhtml+tmphtml;
					});
				
					$(this).html(finhtml);		
					srblockcount++;	
					return false;
					//alert('wait');
			}

			i++;
		});
		//alert(srblockcount);
		var meta = '';
		//check if there are any more
		if ($('.sr-block').length > srblockcount)
		{
			//alert($('.sr-block').length);
			//alert(srblockcount);
			i = 0;
			$('.sr-block').each(function(){
				if (i == srblockcount )
				{
					//alert('ddd');
					meta = $(this).attr('sr-meta');
					//alert('m'+meta);
					parentcallback = 'productblockDone();';
					url = srurl+'productlist?companyid='+companyid+'&meta='+meta+'&translationcode='+translationcode;
					fetchsc(url);
					//srblockcount++;
					return false;				
				}
				i++;
	
			});
			
		}
	//processnextajaxitem();

}




/*
 * This function checks for a pay pal response.  and finalises the pay pal transaction.
 * V1 4/3/2014
 * 
 */
function checkPaypalResponse()
{
	//check paypal.
	return(0);
}

/*
 * This function process the queue of items to be done.
 * V1 4/3/2014
 * 
 */
var srblockcount = 0;
function processajaxqueue()
{
	logIt(fetcharray);
	//get the first items
	
	//alert(fetcharray[0]);
	if (fetcharray[0] == 'srblock' )
	{
		//process sr-block
		logIt('Calling the Sr Block Function');
		var meta = '';
		parentcallback = 'productblockDone();';
		meta = '';
		i = 0;
		$('.sr-block').each(function(){
			if (i == srblockcount )
			{
				meta = $(this).attr('sr-meta');
				
				return false;				
			}

		});


   		// if(condition){

   		 //}


 		
		//alert($('.sr-block').length);
		//meta = $('#sr-block').attr('sr-meta');
		//showmax = $('#sr-block').attr('sr-show-max');
		url = srurl+'productlist?companyid='+companyid+'&meta='+meta+'&translationcode='+translationcode;
		fetchsc(url);
	}
	if (fetcharray[0]=='fetchsingleproduct')
	{
		
		//check for a product id.
		logIt('Calling the fecth single product Function');
		if ($('#sr-productid').length>0)
		{
			productid = $('#sr-productid').val();
			//alert(productid);
			if (productid != '')
			{
				parentcallback = 'singleproductDone();';	
				logIt('Product Id found'+productid);
				fetchsc(srurl+'getproduct/?id='+productid+'&translationcode='+translationcode);	
			}
			else
			{
				logIt('Product Id not found'+productid);
				//get the slug
				tmpurl = document.location.toString().toLowerCase();
				
					//if (document.location.toString().toLowerCase().indexOf("?") >= 0)
					//{
						//tmpurl = tmpurl.split('?');
						//tmpurl = tmpurl[0];
				
				
				url =tmpurl.split('/');
				//check for urls with trailing /
				slug = url[url.length-2];
				if (slug == '')
				{
					//check for urls without trailing /
					slug = url[url.length-1];
				}
				if (slug == '')
				{
					alert('slug not found could not load product');
				}
				else
				{
					parentcallback = 'singleproductDone();';	
					logIt('Product slug found'+slug);
					url = srurl+'getproductbyslug/?productslug='+slug+'&companyid='+companyid+'&translationcode='+translationcode;
					fetchsc(url);
				}
				console.log(url);
			}
			
				
		}
	}
	//sr single
	
	//sr product list
	
	
	
}

function singleproductDone()
{
	logIt('Single product retieved');
	var result = $.parseJSON(ajaxdata);
	logIt(result);
	$('.sr-name').text(result.product.name);
	$('.sr-price').html(displaycurrency+result.product.price);
	$('.sr-description').html(result.product.description);
	$('.sr-quantity').html(result.product.quantity);
	if (typeof result.images !== 'undefined') 
 		{
			jQuery.each( result.images, function( i2, val2 ) {
				logIt('Processing for single product Image:');
				logIt(val2);
 	 			if(val2.hero == 1)
	 	 		{
	 	 			logIt('hero image found '+val2.name);
	 	 			blankimage = 0;

	
	 	 		}
 	 		});
 	 		blankimage=1;
 			if (blankimage == 1)
			{
				logIt('hero image nout found using '+result.images[0].name);
				//console.log(result.images[0].cdnurl);
				$('.sr-singleimage').attr('src',result.images[0].cdnurl);
				//tmphtml = tmphtml.replace("[IMAGESRC]" ,'' );
			}
		}
		
	//store the details
	productid   = result.product.id;
	productname   = result.product.name;
	productprice  = result.product.price;
	productslug   = result.product.slug;
	productoption = result.options;
	productquantity = result.product.quantity;
	productquantitycount = result.product.quantity;

	//alert(productprice);
	//TODO:add the meta
	
	//replace the data.
	 processnextajaxitem();
}

function checkSingleProduct()
{
	//check for single name
	if ($('.sr-name').length>0)
	{
		logIt('sr-name found in checksingleproduct');
		fetcharray.push('fetchsingleproduct');
		return;
		
	}
	if ($('.sr-price').length>0)
	{
		logIt('sr-price found in checksingleproduct');
		fetcharray.push('fetchsingleproduct');
		return;
		
	}
	if ($('.sr-singleimage').length>0)
	{
		logIt('sr-singleimage found in checksingleproduct');
		fetcharray.push('fetchsingleproduct');
		return;
		
	}
	if ($('.sr-additem').length>0)
	{
		logIt('sr-additem found in checksingleproduct');
		fetcharray.push('fetchsingleproduct');
		return;
		
	}
	if ($('.sr-description').length>0)
	{
		logIt('sr-description found in checksingleproduct');
		fetcharray.push('fetchsingleproduct');
		return;
		
	}	
	if ($('.sr-meta').length>0)
	{
		logIt('sr-meta found in checksingleproduct');
		fetcharray.push('fetchsingleproduct');
		return;
		
	}	
	if ($('.sr-quantity').length>0)
	{
		logIt('sr-quantity found in checksingleproduct');
		fetcharray.push('fetchsingleproduct');
		return;
		
	}	
	
}

/*
 * This function calls the rest api and returns some lovely ajax.
 * V1 4/3/2014
 * 
 */
function fetchsc(url)
{

//alert(url);
	var jqxhr = $.get(url, function(data) { })
  	.success(function(result) {
  		//logIt(result);
  		ajaxdata = result;
	  	eval(parentcallback);
	})
  	.error(function(result) { 

   	})
  	.complete(function() { 
  	    ////alert("complete"); 
  	});  
}		

//debug logging
function logIt(message)
{
	if (debug == 1)
	{
		console.log(message);
	}
}



$(document).ready(function(){
	
	

//alert('dddd');
//test modal


//set for the hidden var settings.

//sr debug
	
if ($('#sr-debug').length > 0)
	debug = $('#sr-debug').val();
	
if ($("#sr-checkstock").length > 0)
	checkstock = $('#sr-checkstock').val();
//set the company id
if ($('#sr-companyid').length > 0)
	companyid = $('#sr-companyid').val();
//set the country.
if ($("#sr-country").length > 0)
	translationcode = $("#sr-country").val();	

if (companyid == '')
{
	alert('Company Id needed, aborting');
	return;
}
//end of hidden var setting.	
	
//build modals
buildModals();
//get the details	
getDetails();
//get the slug
checkSingleProduct();

//its all loaded now check for page responses




	
	
});




/*
 * 	END OF CUSTOM CODE.... 3RD PARTY AFTER HERE...
 * 
 * 
 */ 
 

/* ===================================================
 * bootstrap-transition.js v2.2.2
 * http://twitter.github.com/bootstrap/javascript.html#transitions
 * ===================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */


!function ($) {

  "use strict"; // jshint ;_;


  /* CSS TRANSITION SUPPORT (http://www.modernizr.com/)
   * ======================================================= */

  $(function () {

    $.support.transition = (function () {

      var transitionEnd = (function () {

        var el = document.createElement('bootstrap')
          , transEndEventNames = {
               'WebkitTransition' : 'webkitTransitionEnd'
            ,  'MozTransition'    : 'transitionend'
            ,  'OTransition'      : 'oTransitionEnd otransitionend'
            ,  'transition'       : 'transitionend'
            }
          , name

        for (name in transEndEventNames){
          if (el.style[name] !== undefined) {
            return transEndEventNames[name]
          }
        }

      }())

      return transitionEnd && {
        end: transitionEnd
      }

    })()

  })

}(window.jQuery);/* ==========================================================
 * bootstrap-alert.js v2.2.2
 * http://twitter.github.com/bootstrap/javascript.html#alerts
 * ==========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */


!function ($) {

  "use strict"; // jshint ;_;


 /* ALERT CLASS DEFINITION
  * ====================== */

  var dismiss = '[data-dismiss="alert"]'
    , Alert = function (el) {
        $(el).on('click', dismiss, this.close)
      }

  Alert.prototype.close = function (e) {
    var $this = $(this)
      , selector = $this.attr('data-target')
      , $parent

    if (!selector) {
      selector = $this.attr('href')
      selector = selector && selector.replace(/.*(?=#[^\s]*$)/, '') //strip for ie7
    }

    $parent = $(selector)

    e && e.preventDefault()

    $parent.length || ($parent = $this.hasClass('alert') ? $this : $this.parent())

    $parent.trigger(e = $.Event('close'))

    if (e.isDefaultPrevented()) return

    $parent.removeClass('sr-in')

    function removeElement() {
      $parent
        .trigger('closed')
        .remove()
    }

    $.support.transition && $parent.hasClass('sr-fade') ?
      $parent.on($.support.transition.end, removeElement) :
      removeElement()
  }


 /* ALERT PLUGIN DEFINITION
  * ======================= */

  var old = $.fn.alert

  $.fn.alert = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('alert')
      if (!data) $this.data('alert', (data = new Alert(this)))
      if (typeof option == 'string') data[option].call($this)
    })
  }

  $.fn.alert.Constructor = Alert


 /* ALERT NO CONFLICT
  * ================= */

  $.fn.alert.noConflict = function () {
    $.fn.alert = old
    return this
  }


 /* ALERT DATA-API
  * ============== */

  $(document).on('click.alert.data-api', dismiss, Alert.prototype.close)

}(window.jQuery);/* ============================================================
 * bootstrap-button.js v2.2.2
 * http://twitter.github.com/bootstrap/javascript.html#buttons
 * ============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */


!function ($) {

  "use strict"; // jshint ;_;


 /* BUTTON PUBLIC CLASS DEFINITION
  * ============================== */

  var Button = function (element, options) {
    this.$element = $(element)
    this.options = $.extend({}, $.fn.button.defaults, options)
  }

  Button.prototype.setState = function (state) {
    var d = 'disabled'
      , $el = this.$element
      , data = $el.data()
      , val = $el.is('input') ? 'val' : 'html'

    state = state + 'Text'
    data.resetText || $el.data('resetText', $el[val]())

    $el[val](data[state] || this.options[state])

    // push to event loop to allow forms to submit
    setTimeout(function () {
      state == 'loadingText' ?
        $el.addClass(d).attr(d, d) :
        $el.removeClass(d).removeAttr(d)
    }, 0)
  }

  Button.prototype.toggle = function () {
    var $parent = this.$element.closest('[data-toggle="buttons-radio"]')

    $parent && $parent
      .find('.active')
      .removeClass('active')

    this.$element.toggleClass('active')
  }


 /* BUTTON PLUGIN DEFINITION
  * ======================== */

  var old = $.fn.button

  $.fn.button = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('button')
        , options = typeof option == 'object' && option
      if (!data) $this.data('button', (data = new Button(this, options)))
      if (option == 'toggle') data.toggle()
      else if (option) data.setState(option)
    })
  }

  $.fn.button.defaults = {
    loadingText: 'loading...'
  }

  $.fn.button.Constructor = Button


 /* BUTTON NO CONFLICT
  * ================== */

  $.fn.button.noConflict = function () {
    $.fn.button = old
    return this
  }


 /* BUTTON DATA-API
  * =============== */

  $(document).on('click.button.data-api', '[data-toggle^=button]', function (e) {
    var $btn = $(e.target)
    if (!$btn.hasClass('btn')) $btn = $btn.closest('.btn')
    $btn.button('toggle')
  })

}(window.jQuery);/* ==========================================================
 * bootstrap-carousel.js v2.2.2
 * http://twitter.github.com/bootstrap/javascript.html#carousel
 * ==========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */


!function ($) {

  "use strict"; // jshint ;_;


 /* CAROUSEL CLASS DEFINITION
  * ========================= */

  var Carousel = function (element, options) {
    this.$element = $(element)
    this.options = options
    this.options.pause == 'hover' && this.$element
      .on('mouseenter', $.proxy(this.pause, this))
      .on('mouseleave', $.proxy(this.cycle, this))
  }

  Carousel.prototype = {

    cycle: function (e) {
      if (!e) this.paused = false
      this.options.interval
        && !this.paused
        && (this.interval = setInterval($.proxy(this.next, this), this.options.interval))
      return this
    }

  , to: function (pos) {
      var $active = this.$element.find('.item.active')
        , children = $active.parent().children()
        , activePos = children.index($active)
        , that = this

      if (pos > (children.length - 1) || pos < 0) return

      if (this.sliding) {
        return this.$element.one('slid', function () {
          that.to(pos)
        })
      }

      if (activePos == pos) {
        return this.pause().cycle()
      }

      return this.slide(pos > activePos ? 'next' : 'prev', $(children[pos]))
    }

  , pause: function (e) {
      if (!e) this.paused = true
      if (this.$element.find('.next, .prev').length && $.support.transition.end) {
        this.$element.trigger($.support.transition.end)
        this.cycle()
      }
      clearInterval(this.interval)
      this.interval = null
      return this
    }

  , next: function () {
      if (this.sliding) return
      return this.slide('next')
    }

  , prev: function () {
      if (this.sliding) return
      return this.slide('prev')
    }

  , slide: function (type, next) {
      var $active = this.$element.find('.item.active')
        , $next = next || $active[type]()
        , isCycling = this.interval
        , direction = type == 'next' ? 'left' : 'right'
        , fallback  = type == 'next' ? 'first' : 'last'
        , that = this
        , e

      this.sliding = true

      isCycling && this.pause()

      $next = $next.length ? $next : this.$element.find('.item')[fallback]()

      e = $.Event('slide', {
        relatedTarget: $next[0]
      })

      if ($next.hasClass('active')) return

      if ($.support.transition && this.$element.hasClass('slide')) {
        this.$element.trigger(e)
        if (e.isDefaultPrevented()) return
        $next.addClass(type)
        $next[0].offsetWidth // force reflow
        $active.addClass(direction)
        $next.addClass(direction)
        this.$element.one($.support.transition.end, function () {
          $next.removeClass([type, direction].join(' ')).addClass('active')
          $active.removeClass(['active', direction].join(' '))
          that.sliding = false
          setTimeout(function () { that.$element.trigger('slid') }, 0)
        })
      } else {
        this.$element.trigger(e)
        if (e.isDefaultPrevented()) return
        $active.removeClass('active')
        $next.addClass('active')
        this.sliding = false
        this.$element.trigger('slid')
      }

      isCycling && this.cycle()

      return this
    }

  }


 /* CAROUSEL PLUGIN DEFINITION
  * ========================== */

  var old = $.fn.carousel

  $.fn.carousel = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('carousel')
        , options = $.extend({}, $.fn.carousel.defaults, typeof option == 'object' && option)
        , action = typeof option == 'string' ? option : options.slide
      if (!data) $this.data('carousel', (data = new Carousel(this, options)))
      if (typeof option == 'number') data.to(option)
      else if (action) data[action]()
      else if (options.interval) data.cycle()
    })
  }

  $.fn.carousel.defaults = {
    interval: 5000
  , pause: 'hover'
  }

  $.fn.carousel.Constructor = Carousel


 /* CAROUSEL NO CONFLICT
  * ==================== */

  $.fn.carousel.noConflict = function () {
    $.fn.carousel = old
    return this
  }

 /* CAROUSEL DATA-API
  * ================= */

  $(document).on('click.carousel.data-api', '[data-slide]', function (e) {
    var $this = $(this), href
      , $target = $($this.attr('data-target') || (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '')) //strip for ie7
      , options = $.extend({}, $target.data(), $this.data())
    $target.carousel(options)
    e.preventDefault()
  })

}(window.jQuery);/* =============================================================
 * bootstrap-collapse.js v2.2.2
 * http://twitter.github.com/bootstrap/javascript.html#collapse
 * =============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */


!function ($) {

  "use strict"; // jshint ;_;


 /* COLLAPSE PUBLIC CLASS DEFINITION
  * ================================ */

  var Collapse = function (element, options) {
    this.$element = $(element)
    this.options = $.extend({}, $.fn.collapse.defaults, options)

    if (this.options.parent) {
      this.$parent = $(this.options.parent)
    }

    this.options.toggle && this.toggle()
  }

  Collapse.prototype = {

    constructor: Collapse

  , dimension: function () {
      var hasWidth = this.$element.hasClass('width')
      return hasWidth ? 'width' : 'height'
    }

  , show: function () {
      var dimension
        , scroll
        , actives
        , hasData

      if (this.transitioning) return

      dimension = this.dimension()
      scroll = $.camelCase(['scroll', dimension].join('-'))
      actives = this.$parent && this.$parent.find('> .accordion-group > .sr-in')

      if (actives && actives.length) {
        hasData = actives.data('collapse')
        if (hasData && hasData.transitioning) return
        actives.collapse('hide')
        hasData || actives.data('collapse', null)
      }

      this.$element[dimension](0)
      this.transition('addClass', $.Event('show'), 'shown')
      $.support.transition && this.$element[dimension](this.$element[0][scroll])
    }

  , hide: function () {
      var dimension
      if (this.transitioning) return
      dimension = this.dimension()
      this.reset(this.$element[dimension]())
      this.transition('removeClass', $.Event('hide'), 'hidden')
      this.$element[dimension](0)
    }

  , reset: function (size) {
      var dimension = this.dimension()

      this.$element
        .removeClass('collapse')
        [dimension](size || 'auto')
        [0].offsetWidth

      this.$element[size !== null ? 'addClass' : 'removeClass']('collapse')

      return this
    }

  , transition: function (method, startEvent, completeEvent) {
      var that = this
        , complete = function () {
            if (startEvent.type == 'show') that.reset()
            that.transitioning = 0
            that.$element.trigger(completeEvent)
          }

      this.$element.trigger(startEvent)

      if (startEvent.isDefaultPrevented()) return

      this.transitioning = 1

      this.$element[method]('sr-in')

      $.support.transition && this.$element.hasClass('collapse') ?
        this.$element.one($.support.transition.end, complete) :
        complete()
    }

  , toggle: function () {
      this[this.$element.hasClass('sr-in') ? 'hide' : 'show']()
    }

  }


 /* COLLAPSE PLUGIN DEFINITION
  * ========================== */

  var old = $.fn.collapse

  $.fn.collapse = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('collapse')
        , options = typeof option == 'object' && option
      if (!data) $this.data('collapse', (data = new Collapse(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.collapse.defaults = {
    toggle: true
  }

  $.fn.collapse.Constructor = Collapse


 /* COLLAPSE NO CONFLICT
  * ==================== */

  $.fn.collapse.noConflict = function () {
    $.fn.collapse = old
    return this
  }


 /* COLLAPSE DATA-API
  * ================= */

  $(document).on('click.collapse.data-api', '[data-toggle=collapse]', function (e) {
    var $this = $(this), href
      , target = $this.attr('data-target')
        || e.preventDefault()
        || (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '') //strip for ie7
      , option = $(target).data('collapse') ? 'toggle' : $this.data()
    $this[$(target).hasClass('sr-in') ? 'addClass' : 'removeClass']('collapsed')
    $(target).collapse(option)
  })

}(window.jQuery);

/* =========================================================
 * bootstrap-SRmodal.js v2.2.2
 * http://twitter.github.com/bootstrap/javascript.html#SRmodals
 * =========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================= */


!function ($) {

  "use strict"; // jshint ;_;


 /* MODAL CLASS DEFINITION
  * ====================== */

  var SRModal = function (element, options) {
    this.options = options
    this.$element = $(element)
      .delegate('[data-dismiss="SRmodal"]', 'click.dismiss.SRmodal', $.proxy(this.hide, this))
    this.options.remote && this.$element.find('.SRmodal-body').load(this.options.remote)
  }

  SRModal.prototype = {

      constructor: SRModal

    , toggle: function () {
        return this[!this.isShown ? 'show' : 'hide']()
      }

    , show: function () {
        var that = this
          , e = $.Event('show')

        this.$element.trigger(e)

        if (this.isShown || e.isDefaultPrevented()) return

        this.isShown = true

        this.escape()

        this.backdrop(function () {
          var transition = $.support.transition && that.$element.hasClass('sr-fade')

          if (!that.$element.parent().length) {
            that.$element.appendTo(document.body) //don't move SRmodals dom position
          }

          that.$element
            .show()

          if (transition) {
            that.$element[0].offsetWidth // force reflow
          }

          that.$element
            .addClass('sr-in')
            .attr('aria-hidden', false)

          that.enforceFocus()

          transition ?
            that.$element.one($.support.transition.end, function () { that.$element.focus().trigger('shown') }) :
            that.$element.focus().trigger('shown')

        })
      }

    , hide: function (e) {
        e && e.preventDefault()

        var that = this

        e = $.Event('hide')

        this.$element.trigger(e)

        if (!this.isShown || e.isDefaultPrevented()) return

        this.isShown = false

        this.escape()

        $(document).off('focusin.SRmodal')

        this.$element
          .removeClass('sr-in')
          .attr('aria-hidden', true)

        $.support.transition && this.$element.hasClass('sr-fade') ?
          this.hideWithTransition() :
          this.hideSRModal()
      }

    , enforceFocus: function () {
        var that = this
        $(document).on('focusin.SRmodal', function (e) {
          if (that.$element[0] !== e.target && !that.$element.has(e.target).length) {
            that.$element.focus()
          }
        })
      }

    , escape: function () {
        var that = this
        if (this.isShown && this.options.keyboard) {
          this.$element.on('keyup.dismiss.SRmodal', function ( e ) {
            e.which == 27 && that.hide()
          })
        } else if (!this.isShown) {
          this.$element.off('keyup.dismiss.SRmodal')
        }
      }

    , hideWithTransition: function () {
        var that = this
          , timeout = setTimeout(function () {
              that.$element.off($.support.transition.end)
              that.hideSRModal()
            }, 500)

        this.$element.one($.support.transition.end, function () {
          clearTimeout(timeout)
          that.hideSRModal()
        })
      }

    , hideSRModal: function (that) {
        this.$element
          .hide()
          .trigger('hidden')

        this.backdrop()
      }

    , removeBackdrop: function () {
        this.$backdrop.remove()
        this.$backdrop = null
      }

    , backdrop: function (callback) {
        var that = this
          , animate = this.$element.hasClass('sr-fade') ? 'sr-fade' : ''

        if (this.isShown && this.options.backdrop) {
          var doAnimate = $.support.transition && animate

          this.$backdrop = $('<div class="sr-modal-backdrop ' + animate + '" />')
            .appendTo(document.body)

          this.$backdrop.click(
            this.options.backdrop == 'static' ?
              $.proxy(this.$element[0].focus, this.$element[0])
            : $.proxy(this.hide, this)
          )

          if (doAnimate) this.$backdrop[0].offsetWidth // force reflow

          this.$backdrop.addClass('sr-in')

          doAnimate ?
            this.$backdrop.one($.support.transition.end, callback) :
            callback()

        } else if (!this.isShown && this.$backdrop) {
          this.$backdrop.removeClass('sr-in')

          $.support.transition && this.$element.hasClass('sr-fade')?
            this.$backdrop.one($.support.transition.end, $.proxy(this.removeBackdrop, this)) :
            this.removeBackdrop()

        } else if (callback) {
          callback()
        }
      }
  }


 /* MODAL PLUGIN DEFINITION
  * ======================= */

  var old = $.fn.SRmodal

  $.fn.SRmodal = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('SRmodal')
        , options = $.extend({}, $.fn.SRmodal.defaults, $this.data(), typeof option == 'object' && option)
      if (!data) $this.data('SRmodal', (data = new SRModal(this, options)))
      if (typeof option == 'string') data[option]()
      else if (options.show) data.show()
    })
  }

  $.fn.SRmodal.defaults = {
      backdrop: true
    , keyboard: true
    , show: true
  }

  $.fn.SRmodal.Constructor = SRModal


 /* MODAL NO CONFLICT
  * ================= */

  $.fn.SRmodal.noConflict = function () {
    $.fn.SRmodal = old
    return this
  }


 /* MODAL DATA-API
  * ============== */

  $(document).on('click.SRmodal.data-api', '[data-toggle="SRmodal"]', function (e) {
    var $this = $(this)
      , href = $this.attr('href')
      , $target = $($this.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, ''))) //strip for ie7
      , option = $target.data('SRmodal') ? 'toggle' : $.extend({ remote:!/#/.test(href) && href }, $target.data(), $this.data())

    e.preventDefault()

    $target
      .SRmodal(option)
      .one('hide', function () {
        $this.focus()
      })
  })

}(window.jQuery);
/* ==========================================================
 * bootstrap-affix.js v2.2.2
 * http://twitter.github.com/bootstrap/javascript.html#affix
 * ==========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */


!function ($) {

  "use strict"; // jshint ;_;


 /* AFFIX CLASS DEFINITION
  * ====================== */

  var Affix = function (element, options) {
    this.options = $.extend({}, $.fn.affix.defaults, options)
    this.$window = $(window)
      .on('scroll.affix.data-api', $.proxy(this.checkPosition, this))
      .on('click.affix.data-api',  $.proxy(function () { setTimeout($.proxy(this.checkPosition, this), 1) }, this))
    this.$element = $(element)
    this.checkPosition()
  }

  Affix.prototype.checkPosition = function () {
    if (!this.$element.is(':visible')) return

    var scrollHeight = $(document).height()
      , scrollTop = this.$window.scrollTop()
      , position = this.$element.offset()
      , offset = this.options.offset
      , offsetBottom = offset.bottom
      , offsetTop = offset.top
      , reset = 'affix affix-top affix-bottom'
      , affix

    if (typeof offset != 'object') offsetBottom = offsetTop = offset
    if (typeof offsetTop == 'function') offsetTop = offset.top()
    if (typeof offsetBottom == 'function') offsetBottom = offset.bottom()

    affix = this.unpin != null && (scrollTop + this.unpin <= position.top) ?
      false    : offsetBottom != null && (position.top + this.$element.height() >= scrollHeight - offsetBottom) ?
      'bottom' : offsetTop != null && scrollTop <= offsetTop ?
      'top'    : false

    if (this.affixed === affix) return

    this.affixed = affix
    this.unpin = affix == 'bottom' ? position.top - scrollTop : null

    this.$element.removeClass(reset).addClass('affix' + (affix ? '-' + affix : ''))
  }


 /* AFFIX PLUGIN DEFINITION
  * ======================= */

  var old = $.fn.affix

  $.fn.affix = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('affix')
        , options = typeof option == 'object' && option
      if (!data) $this.data('affix', (data = new Affix(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.affix.Constructor = Affix

  $.fn.affix.defaults = {
    offset: 0
  }


 /* AFFIX NO CONFLICT
  * ================= */

  $.fn.affix.noConflict = function () {
    $.fn.affix = old
    return this
  }


 /* AFFIX DATA-API
  * ============== */

  $(window).on('load', function () {
    $('[data-spy="affix"]').each(function () {
      var $spy = $(this)
        , data = $spy.data()

      data.offset = data.offset || {}

      data.offsetBottom && (data.offset.bottom = data.offsetBottom)
      data.offsetTop && (data.offset.top = data.offsetTop)

      $spy.affix(data)
    })
  })


}(window.jQuery);
