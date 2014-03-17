<?php 
  global $wpdb;
  $order = new ShoprocketOrder();
  $status = '';
  if(isset($_GET['status'])) {
    $status = $_GET['status'];
  }
?>
<h2><?php _e('Shoprocket Orders', 'shoprocket'); ?></h2>

<div class='wrap' style='margin-bottom:60px;'>
  
 <?php 
    $setting = new ShoprocketSetting();
    $stats = trim(ShoprocketSetting::getValue('status_options'));
    if(strlen($stats) >= 1 ) {
      $stats = explode(',', $stats);
  ?>
      <p style="float: left; clear: both; margin-top:0; padding-top: 0;"><?php _e( 'Filter Orders by Status' , 'shoprocket' ); ?>:
        <?php
          foreach($stats as $s) {
            $s = trim(strtolower($s));
            ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Order status query: WHERE status='$s'");
            $tmpRows = $order->getOrderRows("WHERE status='$s'");            
            $n = count($tmpRows);
            if($n > 0) {
              $url = ShoprocketCommon::replaceQueryString("page=shoprocket_admin&status=$s");
              echo "<a href=\"$url\">" . ucwords($s) . " (" . count($tmpRows) . ")</a> &nbsp;|&nbsp; ";
            }
            else {
              echo ucwords($s) ." (0) &nbsp;|&nbsp;";
            }
          }
        ?>
        <a href="?page=shoprocket_admin">All (<?php echo count($order->getOrderRows("WHERE `status` != 'checkout_pending'")) ?>)</a>
      </p>
  <?php
    }
    else {
      echo "<p style=\"float: left; clear: both; color: #999; font-size: 11px; both; margin-top:0; padding-top: 0;\">" .
        __("You should consider setting order status options such as new and complete on the 
        <a href='?page=shoprocket-settings'>Shoprocket Settings page</a>.","shoprocket") . "</p>";
    }
  ?>
</div>
<div class="wrap">
  
  <table class="widefat ShoprocketHighlightTable" id="orders_table">
    <tr>
      <thead>
      	<tr>
      	  <th><?php _e('ID', 'shoprocket'); ?></th>
    			<th><?php _e( 'Order Number' , 'shoprocket' ); ?></th>
    			<th><?php _e( 'Name' , 'shoprocket' ); ?></th>
    			<th><?php _e( 'Name' , 'shoprocket' ); ?></th>
      		<th><?php _e( 'Amount' , 'shoprocket' ); ?></th>
      		<th><?php _e( 'Date' , 'shoprocket' ); ?></th>
          <th><?php _e( 'Delivery' , 'shoprocket' ); ?></th>
      		<th><?php _e( 'Status' , 'shoprocket' ); ?></th>
      		<th><?php _e( 'Actions' , 'shoprocket' ); ?></th>
      	</tr>
      </thead>
      <tfoot>
      	<tr>
      		<th><?php _e('ID', 'shoprocket'); ?></th>
    			<th><?php _e( 'Order Number' , 'shoprocket' ); ?></th>
    			<th><?php _e( 'Name' , 'shoprocket' ); ?></th>
    			<th><?php _e( 'Name' , 'shoprocket' ); ?></th>
      		<th><?php _e( 'Amount' , 'shoprocket' ); ?></th>
      		<th><?php _e( 'Date' , 'shoprocket' ); ?></th>
          <th><?php _e( 'Delivery' , 'shoprocket' ); ?></th>
      		<th><?php _e( 'Status' , 'shoprocket' ); ?></th>
      		<th><?php _e( 'Actions' , 'shoprocket' ); ?></th>
      	</tr>
      </tfoot>
    </tr>
  </table>
</div>
<script type="text/javascript">
  (function($){
    $(document).ready(function(){
      var orders_table = $('#orders_table').dataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bPagination": true,
        "iDisplayLength": 30,
        "aLengthMenu": [[30, 60, 150, -1], [30, 60, 150, "All"]],
        "sPaginationType": "bootstrap",
        "bAutoWidth": false,
        "sAjaxSource": ajaxurl + "?action=orders_table",
        "aaSorting": [[5, 'desc']],
        "aoColumns": [
          { "bVisible": false },
          { "bSortable": true, "fnRender": function(oObj) { return '<a href="?page=shoprocket_admin&task=view&id=' + oObj.aData[0] + '">' + oObj.aData[1] + '</a>' }},
          { "fnRender": function(oObj) { return oObj.aData[2] + ' ' + oObj.aData[3] }},
          { "bVisible": false },
          null,
          { "bSearchable": false },
          { "bSearchable": false },
          null,
          { "bSearchable": false, "bSortable": false, "fnRender": function(oObj) { return oObj.aData[8] != "" ? '<a href="#" onClick="printView(' + oObj.aData[0] + ')" id="print_version_' + oObj.aData[0] + '"><?php _e( "Receipt" , "shoprocket" ); ?></a> | <a href="?page=shoprocket_admin&task=view&id=' + oObj.aData[0] + '"><?php _e( "View" , "shoprocket" ); ?></a> | <a class="delete" href="?page=shoprocket_admin&task=delete&id=' + oObj.aData[0] + '"><?php _e( "Delete" , "shoprocket" ); ?></a> | <a href="#" class="ShoprocketViewOrderNote" rel="note_' + oObj.aData[0] + '"><?php _e( "Notes" , "shoprocket" ); ?></a><div class="ShoprocketOrderNote" id="note_' + oObj.aData[0] + '"><a href="#" class="ShoprocketCloseNoteView" rel="note_' + oObj.aData[0] + '" alt="Close Notes Window"><img src="<?php echo SHOPROCKET_URL ?>/images/window-close.png" /></a><h3>' + oObj.aData[1] + '</h3><p>' + oObj.aData[8] + '</p></div>' : '<a href="#" onClick="printView(' + oObj.aData[0] + ')" id="print_version_' + oObj.aData[0] + '"><?php _e( "Receipt" , "shoprocket" ); ?></a> | <a href="?page=shoprocket_admin&task=view&id=' + oObj.aData[0] + '"><?php _e( "View" , "shoprocket" ); ?></a> | <a class="delete" href="?page=shoprocket_admin&task=delete&id=' + oObj.aData[0] + '"><?php _e( "Delete" , "shoprocket" ); ?></a>'; },"aTargets": [ 9 ] }
        ],
        "oLanguage": { 
          "sZeroRecords": "<?php _e('No matching Orders found', 'shoprocket'); ?>", 
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
      }).css('width','');
      orders_table.fnFilter( '<?php echo $status ?>', 7 );
    } );
    $(document).on('click', '.ShoprocketViewOrderNote', function(e) {
      e.preventDefault();
      $(".ShoprocketOrderNote").hide();
      var id = $(this).attr('rel');
      $('#' + id).show();
      return false;
    });
    $(document).on('click', '.ShoprocketCloseNoteView', function(e) {
      e.preventDefault();
      var id = $(this).attr('rel');
      $('#' + id).hide();
      return false;
    });
    $(document).on('click', '.delete', function(e) {
      return confirm('Are you sure you want to delete this item?');
    });
  })(jQuery);
  function printView(id) {
    var url = ajaxurl + '?action=print_view&order_id=' + id
    myWindow = window.open(url,"Your_Receipt","resizable=yes,scrollbars=yes,width=550,height=700");
    return false;
  }
</script>
