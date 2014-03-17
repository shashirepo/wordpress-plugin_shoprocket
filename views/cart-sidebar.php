<?php echo $data['beforeWidget']; ?>
  
  <?php echo $data['beforeTitle'] . '<span id="ShoprocketWidgetCartTitle">' . $data['title'] . '</span>' . $data['afterTitle']; ?>
  
  
    <div id="ShoprocketWidgetCartContents" <?php if(!$data['numItems']): ?> style="display:none;"<?php endif; ?>>
      <a id="ShoprocketWidgetCartLink" href='<?php echo get_permalink($data['cartPage']->ID) ?>'>
      <span id="ShoprocketWidgetCartCount"><?php echo $data['numItems']; ?></span>
      <span id="ShoprocketWidgetCartCountText"> <?php echo _n('item', 'items', $data['numItems'], 'Shoprocket'); ?></span> 
      <span id="ShoprocketWidgetCartCountDash">&ndash;</span>
      <span id="ShoprocketWidgetCartPrice"><?php echo ShoprocketCommon::currency($data['cartWidget']->getSubTotal()); ?>
      </span></a>
  <a id="ShoprocketWidgetViewCart" href='<?php echo get_permalink($data['cartPage']->ID) ?>'><?php _e( 'View Cart' , 'Shoprocket' ); ?></a>
  <span id="ShoprocketWidgetLinkSeparator"> | </span>
  <a id="ShoprocketWidgetCheckout" href='<?php echo get_permalink($data['checkoutPage']->ID) ?>'><?php _e( 'Check out' , 'Shoprocket' ); ?></a>
    </div>
    <p id="ShoprocketWidgetCartEmpty"<?php if($data['numItems']): ?> style="display:none;"<?php endif; ?>><?php _e( 'Your cart is empty.' , 'Shoprocket' ); ?></p>

<?php echo $data['afterWidget']; ?>