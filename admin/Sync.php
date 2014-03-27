<h2><?php _e('ShoprocketSynBox', 'shoprocket'); ?></h2>
<head>
<style>
 .hide {
  display: none;
}
#progress{
 width:300px;
 height:20px;
 border:1px solid rgba(0,0,0,0.5);
 border-radius: 5px;
position: relative;
left: 165px;
bottom:26px;

}

#bar{
 width:1%;
 height:20px;
 border-radius: 5px;

background-image: url("<?php echo SHOPROCKET_URL; ?>/images/bar.gif");
 -webkit-transition:width 700ms ease;   
}
#progress1{
 width:300px;
 height:20px;
 border:1px solid rgba(0,0,0,0.5);
 border-radius: 5px;
position: relative;
left: 165px;
bottom:26px;

}

#bar1{
 width:1%;
 height:20px;
 border-radius: 5px;
background-image: url("<?php echo SHOPROCKET_URL; ?>/images/bar.gif");
 -webkit-transition:width 700ms ease;   
}
b {
  position: relative;
  left: 101%;
  color: green;
}
</style>
<script type="text/javascript">
  var $j = jQuery.noConflict();
 $j(document).ready(function($) {

  function clearSaveStatus(id) {
        jQuery(id).html('');

  }


function doIndex(prev) {
    $j('.ajax-circle').show();
    $j('#bar').show();
    $j('#progress').show();
    $j.get( ajaxurl, {action: 'fetch_products', prev: prev}, doIndexHandleResults, "json");
  }

  function doRemove() {
    $j('.ajax-circle1').show();
      $j('#bar1').show();
    $j('#progress1').show();
    $j.get( ajaxurl, {action: 'remove_products'}, doIndexHandleResults, "json");
  }
  function doIndexHandleResults(data) {

  if (!data.end) {
      doIndex(data.last);
      $j('#bar').html('<b>'+ data.percent + '%</b');
    $j('#bar').width(data.percent+'%');
    } else {
      $j('#bar').html('100%');
      $j('#bar').width('100%');
      $j('.ajax-circle').html('<img src="<?php echo SHOPROCKET_URL; ?>/images/success.png" />');
      setTimeout(clearSaveStatus('.status'), 1000);
      setTimeout(clearSaveStatus('.ajax-circle'), 1000000);
    }
  }

$j('#ShoprocketSynBox').click(function() {      
      doIndex(0);
        });
$j('#ShoprocketDeleteBox').click(function() {      
      doRemove(0);
        });

});

</script>
</head>
<body>
 <button id="ShoprocketSynBox">Sync your Products</button><span class="ajax-circle hide"><img src="<?php echo SHOPROCKET_URL; ?>/images/ajax-circle.gif" /></span>
<div class="status"><div id="progress" class="hide"><div id="bar" class="hide"></div></div></div>
   </br>

 
 <p>It sync yours products from shoprocket to wordpress, Automatically updates them.</p>


 <button id="ShoprocketDeleteBox">Remove all Products</button>(Use with caution)<span class="ajax-circle1 hide"><img src="<?php echo SHOPROCKET_URL; ?>/images/ajax-circle.gif" /></span>
<div id="progress1" class="hide"><div id="bar1" class="hide"></div></div>
 </body>