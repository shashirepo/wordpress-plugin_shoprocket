<?php

$tab = isset($data['tab']) ? $data['tab'] : '';
$reminder = isset($data['reminder']) ? $data['reminder'] : '';
$orderFulfillment = isset($data['order_fulfillment']) ? $data['order_fulfillment'] : '';
$errorMessage = isset($data['error_message']) ? $data['error_message'] : '';
$mustache_elements_reminders = array(
  'billing_first_name', 
  'billing_last_name',
  'feature_level',
  'subscription_plan_name',
  'active_until',
  'billing_interval',
  'username',
  'opt_out_link'
);
$mustache_elements = array(
  'bill_first_name', 
  'bill_last_name',
  'bill_address',
  'bill_address2',
  'bill_city',
  'bill_state',
  'bill_country',
  'bill_zip',
  'ship_first_name',
  'ship_last_name',
  'ship_address',
  'ship_address2',
  'ship_city',
  'ship_state',
  'ship_country',
  'ship_zip',
  'phone',
  'email',
  'coupon',
  'discount_amount',
  'trans_id',
  'shipping',
  'subtotal',
  'tax',
  'total',
  'non_subscription_total',
  'custom_field',
  'date:F d, Y',
  'date_ordered:F d, Y',
  'ordered_on',
  'status',
  'receipt',
  'receipt_link',
  'products',
  'fulfillment_products',
  'ouid',
  'shipping_method',
  'account_id',
  'tracking_number:$i. $carrier:'
);
?>
<?php if(!empty($data['success_message'])): ?>

  <script type="text/javascript">
    (function($){
      $(document).ready(function(){
        $("#ShoprocketSuccessBox").fadeIn(1500).delay(4000).fadeOut(1500);
      })
    })(jQuery);
  </script> 
  
  <div class="ShoprocketModal alert-message success" id="ShoprocketSuccessBox" style="">
    <p><strong><?php _e( 'Success' , 'shoprocket' ); ?></strong><br/>
    <?php echo $data['success_message'] ?></p>
  </div>

<?php endif; ?>
<script type="text/javascript">
  (function($){
    $(document).ready(function(){
      options = {
        modal: true,
        autoOpen: false,
        position: 'center',
        title: 'Message Preview',
        width: $(window).width()-180,
        height: $(window).height()-180,
      }
    })
  })(jQuery);
</script>
<?php if($errorMessage): ?>
  <div><?php echo $errorMessage ?></div>
<?php endif; ?>
<?php
  if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['shoprocket-action'] == 'save reminder') {
    $product_id_subscriptions = ShoprocketMembershipReminders::updateMembershipProductIds();
    if($product_id_subscriptions && is_array($product_id_subscriptions)){ ?>
      <div class="alert-message alert-error">
        <ul>
          <?php foreach($product_id_subscriptions as $message): ?>
            <li><?php echo $message ?></li>
          <?php endforeach; ?>
        </ul>
      </div>  
    <?php
    }
  }
?>
<div id="saveResult"></div>
<?php if(!false || !ShoprocketSetting::getValue('enable_advanced_notifications')): ?>
  <div id="shoprocket-inner-tabs">
    <ul class="subsubsub">
      <li><a href="#notifications-email_receipt_settings" class="notifications-email_receipt_settings tab_function" id="receipt_tab"><?php _e('Receipt', 'shoprocket'); ?></a> | </li>
      <li><a href="#notifications-password_reset_settings" class="notifications-password_reset_settings"><?php _e('Password Reset', 'shoprocket'); ?></a><?php if(false): ?> | <?php endif; ?></li>
      <?php if(false): ?>
        <li><a href="#notifications-advanced_notifications" class="notifications-advanced_notifications"><?php _e('Advanced Notifications', 'shoprocket'); ?></a></li>
      <?php endif; ?>
    </ul>
    <br clear="all">
    <div id="notifications-email_receipt_settings" class="pane">
      <form id="emailReceiptForm" class="ajaxSettingForm" action="" method="post">
        <input type="hidden" name="action" value="save_settings" />
        <input type="hidden" name="_success" value="The email receipt settings have been saved.">
        <h3><?php _e('Email Receipt Settings', 'shoprocket'); ?></h3>
        <p class="description"><?php _e( 'These are the settings used for sending email receipts to your customers after they place an order.' , 'shoprocket' ); ?></p>
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <th scope="row"><?php _e('From Name', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="receipt_from_name" id="receipt_from_name" class="regular-text" value="<?php echo ShoprocketSetting::getValue('receipt_from_name', true); ?>" />
                <p class="description"><?php _e( 'The name of the person from whom the email receipt will be sent. You may want this to be your company name.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('From Address', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="receipt_from_address" id="receipt_from_address" class="regular-text" value="<?php echo ShoprocketSetting::getValue('receipt_from_address', true); ?>" />
                <p class="description"><?php _e( 'The email address the email receipt will be from.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Email Subject', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="receipt_subject" id="receipt_subject" class="regular-text" value="<?php echo ShoprocketSetting::getValue('receipt_subject', true); ?>" />
                <p class="description"><?php _e( 'The subject of the email receipt.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Email Intro', 'shoprocket'); ?></th>
              <td>
                <textarea class="large-textarea" name="receipt_intro"><?php echo ShoprocketSetting::getValue('receipt_intro'); ?></textarea>
                <p class="description"><?php _e( 'This text will appear at the top of the receipt email message above the list of items purchased.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Copy Email To', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="receipt_copy" id="receipt_copy" class="regular-text" value="<?php echo ShoprocketSetting::getValue('receipt_copy', true); ?>" />
                <p class="description"><?php _e( 'Use commas to separate addresses.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
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
    <div id="notifications-password_reset_settings" class="pane">
      <form id="passwordResetForm" class="ajaxSettingForm" action="" method="post">
        <input type="hidden" name="action" value="save_settings" />
        <input type="hidden" name="_success" value="The password reset email settings have been saved.">
        <h3><?php _e('Password Reset Email Settings', 'shoprocket'); ?></h3>
        <p class="description"><?php _e( 'These are the settings used for sending email receipts to your customers after they place an order.' , 'shoprocket' ); ?></p>
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <th scope="row"><?php _e('From Name', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="reset_from_name" id="reset_from_name" class="regular-text" value="<?php echo ShoprocketSetting::getValue('reset_from_name', true); ?>" />
                <p class="description"><?php _e( 'The name of the person from whom the email will be sent. You may want this to be your company name.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('From Address', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="reset_from_address" id="reset_from_address" class="regular-text" value="<?php echo ShoprocketSetting::getValue('reset_from_address', true); ?>" />
                <p class="description"><?php _e( 'The email address the email will be from.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Email Subject', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="reset_subject" id="reset_subject" class="regular-text" value="<?php echo ShoprocketSetting::getValue('reset_subject', true); ?>" />
                <p class="description"><?php _e( 'The subject of the email.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Email Intro', 'shoprocket'); ?></th>
              <td>
                <textarea class="large-textarea" name="reset_intro"><?php echo ShoprocketSetting::getValue('reset_intro'); ?></textarea>
                <p class="description"><?php _e( 'This text will appear at the top of the reset email message above the new password.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
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
    <?php if(false): ?>
      <div id="notifications-advanced_notifications" class="pane">
        <h3><?php _e('Advanced Notifications', 'shoprocket'); ?></h3>
        <form action="" method="post">
          <input type="hidden" name="shoprocket-action" value="advanced notifications">
          <table class="form-table">
            <tbody>
              <tr valign="top">
                <th scope="row"><?php _e('Enable Advanced Notifications', 'shoprocket'); ?></th>
                <td>
                  <input type="radio" name="enable_advanced_notifications" id="enable_advanced_notifications_yes" value="1" <?php echo ShoprocketSetting::getValue('enable_advanced_notifications') == 1 ? 'checked="checked" ' : '' ?>/>
                  <label for="enable_advanced_notifications_yes"><?php _e('Yes', 'shoprocket'); ?></label>
                  <input type="radio" name="enable_advanced_notifications" id="enable_advanced_notifications_no" value="" <?php echo ShoprocketSetting::getValue('enable_advanced_notifications') != 1 ? 'checked="checked" ' : '' ?>/>
                  <label for="enable_advanced_notifications_no"><?php _e('No', 'shoprocket'); ?></label>
                  <p class="description"><?php _e('Selecting this will allow you to enable Advanced Notifications that include HTML emails and emails for status updates, fulfillment emails, followup emails and more.', 'shoprocket'); ?></p>
                </td>
              </tr>
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
    <?php endif; ?>
  </div>
<?php else: ?>
  <div id="shoprocket-inner-tabs">
    <ul class="subsubsub">
      <li><a href="#notifications-email_receipt_settings" class="notifications-email_receipt_settings tab_function" id="receipt_tab"><?php _e('Receipt', 'shoprocket'); ?></a> | </li>
      <li><a href="#notifications-password_reset_settings" class="notifications-password_reset_settings"><?php _e('Password Reset', 'shoprocket'); ?></a> | </li>
      <li><a href="#notifications-order_status_settings" class="notifications-order_status_settings tab_function" id="status_tab"><?php _e('Order Status Updates', 'shoprocket'); ?></a> | </li>
      <li><a href="#notifications-followup_settings" class="notifications-followup_settings tab_function" id="followup_tab"><?php _e('Followup', 'shoprocket'); ?></a> | </li>
      <li><a href="#notifications-fulfillment_settings" class="notifications-fulfillment_settings tab_function" id="fulfillment_tab"><?php _e('Order Fulfillment', 'shoprocket'); ?></a> | </li>
      <li><a href="#notifications-reminder_settings" class="notifications-reminder_settings tab_function" id="reminder_tab"><?php _e('Subscription Reminders', 'shoprocket'); ?></a> | </li>
      <li><a href="#notifications-email_log_settings" class="notifications-email_log_settings"><?php _e('Log', 'shoprocket'); ?></a> | </li>
      <li><a href="#notifications-advanced_notifications" class="notifications-advanced_notifications"><?php _e('Advanced Notifications', 'shoprocket'); ?></a></li>
    </ul>
    <br clear="all">
  
    <div id="notifications-email_receipt_settings" class="pane">
      <h3><?php _e('Email Receipt Settings', 'shoprocket'); ?></h3>
      <p class="description"><?php _e( 'These are the settings used for sending email receipts to your customers after they place an order.' , 'shoprocket' ); ?></p>
      <form id="mainEmailReceiptForm" class="ajaxSettingForm" action="" method="post">
        <input type="hidden" name="action" value="save_settings" />
        <input type="hidden" name="_success" value="The main email receipt settings have been saved.">
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <th scope="row"><?php _e('From Name', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="receipt_from_name" id="receipt_from_name" class="regular-text" value="<?php echo ShoprocketSetting::getValue('receipt_from_name', true); ?>" />
                <p class="description"><?php _e( 'The name of the person from whom the email receipt will be sent. You may want this to be your company name.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('From Address', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="receipt_from_address" id="receipt_from_address" class="regular-text" value="<?php echo ShoprocketSetting::getValue('receipt_from_address', true); ?>" />
                <p class="description"><?php _e( 'The email address the email receipt will be from.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Email Subject', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="receipt_subject" id="receipt_subject" class="regular-text" value="<?php echo ShoprocketSetting::getValue('receipt_subject', true); ?>" />
                <p class="description"><?php _e( 'The subject of the email receipt' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Copy Email To', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="receipt_copy" id="receipt_copy" class="regular-text" value="<?php echo ShoprocketSetting::getValue('receipt_copy', true); ?>" />
                <p class="description"><?php _e( 'Use commas to separate addresses.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="mustache_elements alert-message hint">
          <p class="description"><?php _e( 'You can use the following elements in either the HTML or Plain text email templates. Click once to select, then copy' , 'shoprocket' ); ?>:</p>
          <?php foreach($mustache_elements as $me): ?>
            <span id="url-<?php echo $me; ?>-receipt" onclick="SelectText('url-<?php echo $me; ?>-receipt');">{{<?php echo $me; ?>}}</span> 
          <?php endforeach; ?>
        </div>
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <th scope="row"><?php _e('Message Intro Only', 'shoprocket'); ?></th>
              <td>
                <input type="radio" name="receipt_message_intro" id="receipt_message_intro_yes" value="1" <?php echo ShoprocketSetting::getValue('receipt_message_intro') == 1 ? 'checked="checked" ' : '' ?>/>
                <label for="receipt_message_intro_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
                <input type="radio" name="receipt_message_intro" id="receipt_message_intro_no" value="" <?php echo ShoprocketSetting::getValue('receipt_message_intro') != 1 ? 'checked="checked" ' : '' ?>/>
                <label for="receipt_message_intro_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
                <p class="description"><?php _e('If set to yes, this will allow you to use the default html emails with just an introductory paragraph in the HTML and plain text boxes below.  If set to no, you will need to customize the entire email for both HTML and plain text. Note: You can leave both the HTML and plain text boxes empty and Shoprocket will send out the receipt using the default email template.', 'shoprocket'); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Send HTML Emails', 'shoprocket'); ?></th>
              <td>
                <input type="radio" name="receipt_send_html_emails" class="sendHtmlYes" id="receipt_send_html_emails_yes" value="1" <?php echo ShoprocketSetting::getValue('receipt_send_html_emails') == 1 ? 'checked="checked" ' : '' ?>/>
                <label for="receipt_send_html_emails_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
                <input type="radio" name="receipt_send_html_emails" class="sendHtmlNo" id="receipt_send_html_emails_no" value="" <?php echo ShoprocketSetting::getValue('receipt_send_html_emails') != 1 ? 'checked="checked" ' : '' ?>/>
                <label for="receipt_send_html_emails_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
              </td>
            </tr>
            <tr valign="top" class="receipt_html_email_block">
              <th scope="row"><?php _e('HTML Email', 'shoprocket'); ?></th>
              <td>
                <textarea id="receipt_html_email" name="receipt_html_email" class="large-textarea"><?php echo ShoprocketSetting::getValue('receipt_html_email'); ?></textarea>
                <div id="receipt_dialog_html" class="dialogBox">
                  <iframe id="receipt_html_preview" class="iframe_preview"></iframe>
                </div>
                <p class="description"><?php _e( 'Use this section to add elements to the email in an HTML format' , 'shoprocket' ); ?>.</p>
              </td>
            </tr>
            <tr valign="top" class="receipt_html_email_block">
              <th scope="row"></th>
              <td>
                <input type="button" class="button-secondary dialogOpenHtml" id="receipt_button_html" value="<?php _e( 'Preview HTML Email' , 'shoprocket' ); ?>"/>
                <input type="button" class="button-secondary copyBlock" id="receipt_copy_html" value="<?php _e( 'Copy HTML to Plain' , 'shoprocket' ); ?>"/>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Plain Text Email', 'shoprocket'); ?></th>
              <td>
                <textarea id="receipt_plain_email" class="large-textarea" name="receipt_plain_email"><?php echo ShoprocketSetting::getValue('receipt_plain_email'); ?></textarea>
                <div id="receipt_dialog_plain" class="dialogBox">
                  <iframe id="receipt_plain_preview" class="iframe_preview"></iframe>
                </div>
                <p class="description"><?php _e( 'Use this section to write up your email in Plain text format' , 'shoprocket' ); ?>.</p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"></th>
              <td>
                <input type="button" class="button-secondary dialogOpenPlain" id="receipt_button_plain" value="<?php _e( 'Preview Plain Text Email' , 'shoprocket' ); ?>"/>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Send Test Email', 'shoprocket'); ?></th>
              <td>
                <input type="hidden" id="receipt_test_email_status" value="receipt">
                <input type="text" value="" class="defaultText receipt_test_email_address" title="<?php _e('Email Address', 'shoprocket'); ?>" id="receipt_test_email_address"/>
                <input type="button" class="button-secondary test_email" id="receipt_test_email" value="<?php _e( 'Send' , 'shoprocket' ); ?>" />
              </td>
            </tr>
            <tr valign="top">
              <th scope="row">
                <?php submit_button(null, 'primary', 'receipt_submit'); ?>
              </th>
              <td></td>
            </tr>
          </tbody>
        </table>
      </form>
    </div>
    <div id="notifications-password_reset_settings" class="pane">
      <h3><?php _e('Password Reset Email Settings', 'shoprocket'); ?></h3>
      <p class="description"><?php _e('These are the settings used for sending an email to your customers when they need to reset their password. Please Note: This email does not allow for HTML at this time.', 'shoprocket'); ?></p>
      <form id="passwordResetEmailForm" class="ajaxSettingForm" action="" method="post">
        <input type="hidden" name="action" value="save_settings" />
        <input type="hidden" name="_success" value="<?php _e( 'The password reset email settings have been saved' , 'shoprocket' ); ?>.">
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <th scope="row"><?php _e('From Name', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="reset_from_name" id="reset_from_name" class="regular-text" value="<?php echo ShoprocketSetting::getValue('reset_from_name', true); ?>" />
                <p class="description"><?php _e( 'The name of the person from whom the email will be sent. You may want this to be your company name.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('From Address', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="reset_from_address" id="reset_from_address" class="regular-text" value="<?php echo ShoprocketSetting::getValue('reset_from_address', true); ?>" />
                <p class="description"><?php _e( 'The email address the email will be from.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Email Subject', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="reset_subject" id="reset_subject" class="regular-text" value="<?php echo ShoprocketSetting::getValue('reset_subject', true); ?>" />
                <p class="description"><?php _e( 'The subject of the email.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Email Intro', 'shoprocket'); ?></th>
              <td>
                <textarea class="large-textarea" name="reset_intro"><?php echo ShoprocketSetting::getValue('reset_intro'); ?></textarea>
                <p class="description"><?php _e( 'This text will appear at the top of the reset email message above the new password.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
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
    <div id="notifications-order_status_settings" class="pane">
      <h3><?php _e('Order Status Updates Email Settings', 'shoprocket'); ?></h3>
      <p class="description"><?php _e( 'These are the settings used for sending status updates to your customers when you have updated the order information.' , 'shoprocket' ); ?></p>
      <?php 
      $setting = new ShoprocketSetting();
      $opts = explode(',', ShoprocketSetting::getValue('status_options'));
      if(ShoprocketSetting::getValue('status_options')): ?>
        <?php foreach($opts as $o): ?>
          <?php $o_name = trim($o); ?>
          <?php $o = str_replace(' ', '_', trim($o)); ?>
          <hr>
          <h3><?php echo ucwords($o_name); ?> <?php _e('Status Update', 'shoprocket'); ?></h3>
          <form id="<?php echo $o ?>EmailForm" class="ajaxSettingForm" action="" method='post'>
            <input type="hidden" name="action" value="save_settings" />
            <input type="hidden" name="_success" value="<?php _e( 'The' , 'shoprocket' ); ?> <?php echo $o_name; ?> <?php _e( 'email update settings have been saved' , 'shoprocket' ); ?>.">
            <table class="form-table">
              <tbody>
                <tr valign="top">
                  <th scope="row"><?php _e('From Name', 'shoprocket'); ?></th>
                  <td>
                    <input type="text" name="<?php echo $o ?>_from_name" id="<?php echo $o ?>_from_name" class="regular-text" value="<?php echo ShoprocketSetting::getValue($o . '_from_name', true); ?>" />
                    <p class="description"><?php _e( 'The name of the person from whom the email update will be sent. You may want this to be your company name.' , 'shoprocket' ); ?></p>
                  </td>
                </tr>
                <tr valign="top">
                  <th scope="row"><?php _e('From Address', 'shoprocket'); ?></th>
                  <td>
                    <input type="text" name="<?php echo $o ?>_from_address" id="<?php echo $o ?>_from_address" class="regular-text" value="<?php echo ShoprocketSetting::getValue($o . '_from_address', true); ?>" />
                    <p class="description"><?php _e( 'The email address the email update will be from.' , 'shoprocket' ); ?></p>
                  </td>
                </tr>
                <tr valign="top">
                  <th scope="row"><?php _e('Email Subject', 'shoprocket'); ?></th>
                  <td>
                    <input type="text" name="<?php echo $o ?>_subject" id="<?php echo $o ?>_subject" class="regular-text" value="<?php echo ShoprocketSetting::getValue($o . '_subject', true); ?>" />
                    <p class="description"><?php _e( 'The subject of the email update' , 'shoprocket' ); ?></p>
                  </td>
                </tr>
                <tr valign="top">
                  <th scope="row"><?php _e('Copy Email To', 'shoprocket'); ?></th>
                  <td>
                    <input type="text" name="<?php echo $o ?>_copy" id="<?php echo $o ?>_copy" class="regular-text" value="<?php echo ShoprocketSetting::getValue($o . '_copy', true); ?>" />
                    <p class="description"><?php _e( 'Use commas to separate addresses.' , 'shoprocket' ); ?></p>
                  </td>
                </tr>
              </tbody>
            </table>
            <div class="mustache_elements alert-message hint">
              <p class="description"><?php _e( 'You can use the following elements in either the HTML or Plain text email templates. Click once to select, then copy' , 'shoprocket' ); ?>:</p>
              <?php foreach($mustache_elements as $me): ?>
                <span id="url-<?php echo $me; ?>-<?php echo $o; ?>" onclick="SelectText('url-<?php echo $me; ?>-<?php echo $o; ?>');">{{<?php echo $me; ?>}}</span> 
              <?php endforeach; ?>
            </div>
            <table class="form-table">
              <tbody>
                <tr valign="top">
                  <th scope="row"><?php _e('Message Intro Only', 'shoprocket'); ?></th>
                  <td>
                    <input type="radio" name="<?php echo $o; ?>_message_intro" id="<?php echo $o; ?>_message_intro_yes" value="1" <?php echo ShoprocketSetting::getValue($o . '_message_intro') == 1 ? 'checked="checked" ' : '' ?>/>
                    <label for="<?php echo $o; ?>_message_intro_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
                    <input type="radio" name="<?php echo $o; ?>_message_intro" id="<?php echo $o; ?>_message_intro_no" value="" <?php echo ShoprocketSetting::getValue($o .'_message_intro') != 1 ? 'checked="checked" ' : '' ?>/>
                    <label for="<?php echo $o; ?>_message_intro_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
                    <p class="description"><?php _e('If set to yes, this will allow you to use the default html emails with just an introductory paragraph in the HTML and plain text boxes below.  If set to no, you will need to customize the entire email for both HTML and plain text. Note: You can leave both the HTML and plain text boxes empty and Shoprocket will send out the', 'shoprocket'); ?> <?php echo $o_name; ?> <?php _e('status update email using the default email template.', 'shoprocket'); ?></p>
                  </td>
                </tr>
                <tr valign="top">
                  <th scope="row"><?php _e('Send HTML Emails', 'shoprocket'); ?></th>
                  <td>
                    <input type="radio" name="<?php echo $o; ?>_send_html_emails" class="sendHtmlYes" id="<?php echo $o; ?>_send_html_emails_yes" value="1" <?php echo ShoprocketSetting::getValue($o . '_send_html_emails') == 1 ? 'checked="checked" ' : '' ?>/>
                    <label for="<?php echo $o; ?>_send_html_emails_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
                    <input type="radio" name="<?php echo $o; ?>_send_html_emails" class="sendHtmlNo" id="<?php echo $o; ?>_send_html_emails_no" value="" <?php echo ShoprocketSetting::getValue($o . '_send_html_emails') != 1 ? 'checked="checked" ' : '' ?>/>
                    <label for="<?php echo $o; ?>_send_html_emails_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
                  </td>
                </tr>
                <tr valign="top" class="<?php echo $o; ?>_html_email_block">
                  <th scope="row"><?php _e('HTML Email', 'shoprocket'); ?></th>
                  <td>
                    <textarea id="<?php echo $o; ?>_html_email" name="<?php echo $o; ?>_html_email" class="large-textarea"><?php echo ShoprocketSetting::getValue($o . '_html_email'); ?></textarea>
                    <div id="<?php echo $o; ?>_dialog_html" class="dialogBox">
                      <iframe id="<?php echo $o; ?>_html_preview" class="iframe_preview"></iframe>
                    </div>
                    <p class="description"><?php _e( 'Use this section to add elements to the email in an HTML format' , 'shoprocket' ); ?>.</p>
                  </td>
                </tr>
                <tr valign="top" class="<?php echo $o; ?>_html_email_block">
                  <th scope="row"></th>
                  <td>
                    <input type="button" class="button-secondary dialogOpenHtml" id="<?php echo $o; ?>_button_html" value="<?php _e( 'Preview HTML Email' , 'shoprocket' ); ?>"/>
                    <input type="button" class="button-secondary copyBlock" id="<?php echo $o; ?>_copy_html" value="<?php _e( 'Copy HTML to Plain' , 'shoprocket' ); ?>"/>
                  </td>
                </tr>
                <tr valign="top">
                  <th scope="row"><?php _e('Plain Text Email', 'shoprocket'); ?></th>
                  <td>
                    <textarea id="<?php echo $o; ?>_plain_email" class="large-textarea" name="<?php echo $o; ?>_plain_email"><?php echo ShoprocketSetting::getValue($o . '_plain_email'); ?></textarea>
                    <div id="<?php echo $o; ?>_dialog_plain" class="dialogBox">
                      <iframe id="<?php echo $o; ?>_plain_preview" class="iframe_preview"></iframe>
                    </div>
                    <p class="description"><?php _e( 'Use this section to write up your email in Plain text format' , 'shoprocket' ); ?>.</p>
                  </td>
                </tr>
                <tr valign="top">
                  <th scope="row"></th>
                  <td>
                    <input type="button" class="button-secondary dialogOpenPlain" id="<?php echo $o; ?>_button_plain" value="<?php _e( 'Preview Plain Text Email' , 'shoprocket' ); ?>"/>
                  </td>
                </tr>
                <tr valign="top">
                  <th scope="row"><?php _e('Send Test Email', 'shoprocket'); ?></th>
                  <td>
                    <input type="hidden" id="<?php echo $o; ?>_test_email_status" value="<?php echo $o; ?>">
                    <input type="text" value="" class="defaultText <?php echo $o; ?>_test_email_address" title="<?php _e('Email Address', 'shoprocket'); ?>" id="<?php echo $o; ?>_test_email_address"/>
                    <input type="button" class="button-secondary test_email" id="<?php echo $o; ?>_test_email" value="<?php _e( 'Send' , 'shoprocket' ); ?>" />
                  </td>
                </tr>
                <tr valign="top">
                  <th scope="row">
                    <?php submit_button(null, 'primary', $o . '_submit'); ?>
                  </th>
                  <td></td>
                </tr>
              </tbody>
            </table>
          </form>
        <?php endforeach; ?>
      <?php else: ?>
        <h3 class="loading"><?php _e('Please set up your status options in the', 'shoprocket'); ?> <a href="admin.php?page=shoprocket-settings"><?php _e('Shoprocket Settings', 'shoprocket'); ?></a> <?php _e('page before configuring these email notifications', 'shoprocket'); ?>.</h3>
      <?php endif; ?>
    </div>
    <div id="notifications-followup_settings" class="pane">
      <h3><?php _e('Followup Email', 'shoprocket'); ?></h3>
      <p class="description"><?php _e( 'These are the settings used for sending a followup email to your customers on a specific date after they place an order.' , 'shoprocket' ); ?></p>
      <form id="timedFollowupEmailForm" class="ajaxSettingForm" action="" method="post">
        <input type="hidden" name="action" value="save_settings" />
        <input type="hidden" name="_success" value="<?php _e( 'The followup email settings have been saved' , 'shoprocket' ); ?>.">
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <th scope="row"><?php _e('Enable Followup Email', 'shoprocket'); ?></th>
              <td>
                <input type="radio" name="enable_followup_emails" id="enable_followup_emails_yes" value="1" <?php echo ShoprocketSetting::getValue('enable_followup_emails') == '1' ? 'checked="checked" ' : ''; ?>/>
                <label for="enable_followup_emails_yes"><?php _e('Yes', 'shoprocket'); ?></label>
                <input type="radio" name="enable_followup_emails" id="enable_followup_emails_no" value="" <?php echo ShoprocketSetting::getValue('enable_followup_emails') != '1' ? 'checked="checked" ' : ''; ?>/>
                <label for="enable_followup_emails_no"><?php _e('No', 'shoprocket'); ?></label>
                <p class="description"><?php _e( 'Check this box to enable a followup email to your customers. This will affect ALL orders.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Send Email', 'shoprocket'); ?></th>
              <td>
                <input class="small-text" type="text" name="followup_email_number" id="followup_email_number" value="<?php echo ShoprocketSetting::getValue('followup_email_number') ?>" />
                <select name="followup_email_time">
                  <option value="days"<?php echo ShoprocketSetting::getValue('followup_email_time') == 'days' ? ' selected="selected"' : ''; ?>><?php _e( 'Days' , 'shoprocket' ); ?></option>
                  <option value="weeks"<?php echo ShoprocketSetting::getValue('followup_email_time') == 'weeks' ? ' selected="selected"' : ''; ?>><?php _e( 'Weeks' , 'shoprocket' ); ?></option>
                  <option value="months"<?php echo ShoprocketSetting::getValue('followup_email_time') == 'months' ? ' selected="selected"' : ''; ?>><?php _e( 'Months' , 'shoprocket' ); ?></option>
                </select>
                <span class="description"><?php _e( 'When do you want the followup email to be sent out? This is an approximate time and is based on visits to your site.' , 'shoprocket' ); ?></span>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('From Name', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="followup_from_name" id="followup_from_name" class="regular-text" value="<?php echo ShoprocketSetting::getValue('followup_from_name', true); ?>" />
                <p class="description"><?php _e( 'The name of the person from whom the followup email will be sent. You may want this to be your company name.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('From Address', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="followup_from_address" id="followup_from_address" class="regular-text" value="<?php echo ShoprocketSetting::getValue('followup_from_address', true); ?>" />
                <p class="description"><?php _e( 'The email address the followup email will be from.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Email Subject', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="followup_subject" id="followup_subject" class="regular-text" value="<?php echo ShoprocketSetting::getValue('followup_subject', true); ?>" />
                <p class="description"><?php _e( 'The subject of the followup email' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Copy Email To', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="followup_copy" id="followup_copy" class="regular-text" value="<?php echo ShoprocketSetting::getValue('followup_copy', true); ?>" />
                <p class="description"><?php _e( 'Use commas to separate addresses.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="mustache_elements alert-message hint">
          <p class="description"><?php _e( 'You can use the following elements in either the HTML or Plain text email templates. Click once to select, then copy' , 'shoprocket' ); ?>:</p>
          <?php foreach($mustache_elements as $me): ?>
            <span id="url-<?php echo $me; ?>-followup" onclick="SelectText('url-<?php echo $me; ?>-followup');">{{<?php echo $me; ?>}}</span> 
          <?php endforeach; ?>
        </div>
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <th scope="row"><?php _e('Message Intro Only', 'shoprocket'); ?></th>
              <td>
                <input type="radio" name="followup_message_intro" id="followup_message_intro_yes" value="1" <?php echo ShoprocketSetting::getValue('followup_message_intro') == 1 ? 'checked="checked" ' : '' ?>/>
                <label for="followup_message_intro_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
                <input type="radio" name="followup_message_intro" id="followup_message_intro_no" value="" <?php echo ShoprocketSetting::getValue('followup_message_intro') != 1 ? 'checked="checked" ' : '' ?>/>
                <label for="followup_message_intro_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
                <p class="description"><?php _e('If set to yes, this will allow you to use the default html emails with just an introductory paragraph in the HTML and plain text boxes below.  If set to no, you will need to customize the entire email for both HTML and plain text. Note: You can leave both the HTML and plain text boxes empty and Shoprocket will send out the followup email using the default email template.', 'shoprocket'); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Send HTML Emails', 'shoprocket'); ?></th>
              <td>
                <input type="radio" name="followup_send_html_emails" class="sendHtmlYes" id="followup_send_html_emails_yes" value="1" <?php echo ShoprocketSetting::getValue('followup_send_html_emails') == 1 ? 'checked="checked" ' : '' ?>/>
                <label for="followup_send_html_emails_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
                <input type="radio" name="followup_send_html_emails" class="sendHtmlNo" id="followup_send_html_emails_no" value="" <?php echo ShoprocketSetting::getValue('followup_send_html_emails') != 1 ? 'checked="checked" ' : '' ?>/>
                <label for="followup_send_html_emails_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
              </td>
            </tr>
            <tr valign="top" class="followup_html_email_block">
              <th scope="row"><?php _e('HTML Email', 'shoprocket'); ?></th>
              <td>
                <textarea id="followup_html_email" name="followup_html_email" class="large-textarea"><?php echo ShoprocketSetting::getValue('followup_html_email'); ?></textarea>
                <div id="followup_dialog_html" class="dialogBox">
                  <iframe id="followup_html_preview" class="iframe_preview"></iframe>
                </div>
                <p class="description"><?php _e( 'Use this section to add elements to the email in an HTML format' , 'shoprocket' ); ?>.</p>
              </td>
            </tr>
            <tr valign="top" class="followup_html_email_block">
              <th scope="row"></th>
              <td>
                <input type="button" class="button-secondary dialogOpenHtml" id="followup_button_html" value="<?php _e( 'Preview HTML Email' , 'shoprocket' ); ?>"/>
                <input type="button" class="button-secondary copyBlock" id="followup_copy_html" value="<?php _e( 'Copy HTML to Plain' , 'shoprocket' ); ?>"/>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Plain Text Email', 'shoprocket'); ?></th>
              <td>
                <textarea id="followup_plain_email" class="large-textarea" name="followup_plain_email"><?php echo ShoprocketSetting::getValue('followup_plain_email'); ?></textarea>
                <div id="followup_dialog_plain" class="dialogBox">
                  <iframe id="followup_plain_preview" class="iframe_preview"></iframe>
                </div>
                <p class="description"><?php _e( 'Use this section to write up your email in Plain text format' , 'shoprocket' ); ?>.</p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"></th>
              <td>
                <input type="button" class="button-secondary dialogOpenPlain" id="followup_button_plain" value="<?php _e( 'Preview Plain Text Email' , 'shoprocket' ); ?>"/>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Send Test Email', 'shoprocket'); ?></th>
              <td>
                <input type="hidden" id="followup_test_email_status" value="followup">
                <input type="text" value="" class="defaultText followup_test_email_address" title="<?php _e('Email Address', 'shoprocket'); ?>" id="followup_test_email_address"/>
                <input type="button" class="button-secondary test_email" id="followup_test_email" value="<?php _e( 'Send' , 'shoprocket' ); ?>" />
              </td>
            </tr>
            <tr valign="top">
              <th scope="row">
                <?php submit_button(null, 'primary', 'followup_submit'); ?>
              </th>
              <td></td>
            </tr>
          </tbody>
        </table>
      </form>
    </div>
    <div id="notifications-fulfillment_settings" class="pane">
      <h3><?php _e('Order Fulfillment Centers', 'shoprocket'); ?></h3>
      <p class="description"><?php _e( 'These are the settings used for sending a an email to an order fulfillment center after a purchase is made. You can create an individual one for each product, or one for all your products. Make sure you fill in the email settings below.' , 'shoprocket' ); ?></p>
      <form id="orderFulfillmentCenter" action="" method="post">
        <input type="hidden" name="shoprocket-action" value="save order fulfillment" />
        <input type="hidden" name="_success" value="<?php _e( 'The order fulfillment contact settings have been saved' , 'shoprocket' ); ?>." />
        <input type="hidden" name="fulfillment[id]" value="<?php echo $orderFulfillment->id ?>" />
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <th scope="row"><?php _e('To Name', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="fulfillment[name]" id="fulfillment-name" class="regular-text" value="<?php echo $orderFulfillment->name ?>" />
                <p class="description"><?php _e( 'The name of the person or fulfillment center this email will be sent to.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('To Address', 'shoprocket'); ?></th>
              <td>
                <input type="text" name="fulfillment[email]" id="fulfillment-email" class="regular-text" value="<?php echo $orderFulfillment->email ?>" />
                <p class="description"><?php _e( 'The email address for the fulfillment center.' , 'shoprocket' ); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Eligible Products', 'shoprocket'); ?></th>
              <td class="fulfillment_products">
                <input type="text" id="fulfillment-products" name="fulfillment[products]" class="regular-text" value="<?php echo $orderFulfillment->products ?>" />
                <input type="hidden" name="fulfillment-products-hidden" id="fulfillment-products-hidden" value="<?php echo $orderFulfillment->products ?>" />
                <p class="description"><?php _e('Enter the names of products in your Sync that this order fulfillment center will receive an email to.  You can enter as many products as you want.  If you want this fulfillment center to receive ALL orders, leave this field blank. The fulfillment center will receive an email for each product that meets the criteria.', 'shoprocket'); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row">
                <p>
                  <?php submit_button(null, 'primary', 'submit', false); ?>
                  <?php if($orderFulfillment->id > 0): ?>
                  <a href="?page=shoprocket-settings&tab=notifications_settings&task=cancel_fulfillment" class="button-secondary linkButton"><?php _e( 'Cancel' , 'shoprocket' ); ?></a>
                  <?php endif; ?>
                </p>
              </th>
              <td></td>
            </tr>
          </tbody>
        </table>
      </form>
      <?php
      $orderF = $orderFulfillment->getModels();
      if(!empty($orderF)):
      ?>
        <h3><?php _e( 'Existing Fulfillment Notifications' , 'shoprocket' ); ?></h3>
        <table class="widefat ShoprocketHighlightTable" id="fullfillment_centers_table">
          <thead>
            <tr>
              <th><?php _e( 'Name' , 'shoprocket' ); ?></th>
              <th><?php _e( 'Email' , 'shoprocket' ); ?></th>
              <th><?php _e( 'Products' , 'shoprocket' ); ?></th>
              <th><?php _e( 'Actions' , 'shoprocket' ); ?></th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th><?php _e( 'Name' , 'shoprocket' ); ?></th>
              <th><?php _e( 'Email' , 'shoprocket' ); ?></th>
              <th><?php _e( 'Products' , 'shoprocket' ); ?></th>
              <th><?php _e( 'Actions' , 'shoprocket' ); ?></th>
            </tr>
          </tfoot>
          <tbody>
            <?php foreach($orderF as $o): ?>
             <tr>
               <td><a href="?page=shoprocket-settings&tab=notifications_settings&task=edit_fulfillment&id=<?php echo $o->id ?>"><?php echo $o->name ?></a></td>
               <td><?php echo $o->email ?></td>
               <td>
                 <?php 
                 $products = $o->productNames(); ?>
                 <?php foreach($products as $p): ?>
                   <?php echo $p['name']; ?></br>
                 <?php endforeach; ?>
               </td>
               <td>
                 <a href="?page=shoprocket-settings&tab=notifications_settings&task=edit_fulfillment&id=<?php echo $o->id ?>"><?php _e( 'Edit' , 'shoprocket' ); ?></a> | 
                   <a class="delete" href="?page=shoprocket-settings&tab=notifications_settings&task=delete_fulfillment&id=<?php echo $o->id ?>"><?php _e( 'Delete' , 'shoprocket' ); ?></a>
               </td>
             </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
      <br clear="all" />
      <?php
      $p = ShoprocketProduct::loadProductsOutsideOfClass('id, name, item_number', 'id > 0');
      if(!empty($p)):
      ?>
        <h3><?php _e('Order Fulfillment Email Settings', 'shoprocket'); ?></h3>
        <form id="orderFulfillmentEmailForm" class="ajaxSettingForm" action="" method="post">
          <input type="hidden" name="action" value="save_settings" />
          <input type="hidden" name="_success" value="<?php _e( 'The order fulfillment email settings have been saved' , 'shoprocket' ); ?>.">
          <table class="form-table">
            <tbody>
              <tr valign="top">
                <th scope="row"><?php _e('From Name', 'shoprocket'); ?></th>
                <td>
                  <input type="text" name="fulfillment_from_name" id="fulfillment_from_name" class="regular-text" value="<?php echo ShoprocketSetting::getValue('fulfillment_from_name', true); ?>" />
                  <p class="description"><?php _e( 'The name of the person from whom the fulfillment email will be sent. You may want this to be your company name.' , 'shoprocket' ); ?></p>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><?php _e('From Address', 'shoprocket'); ?></th>
                <td>
                  <input type="text" name="fulfillment_from_address" id="fulfillment_from_address" class="regular-text" value="<?php echo ShoprocketSetting::getValue('fulfillment_from_address', true); ?>" />
                  <p class="description"><?php _e( 'The email address the fulfillment email will be from.' , 'shoprocket' ); ?></p>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><?php _e('Email Subject', 'shoprocket'); ?></th>
                <td>
                  <input type="text" name="fulfillment_subject" id="fulfillment_subject" class="regular-text" value="<?php echo ShoprocketSetting::getValue('fulfillment_subject', true); ?>" />
                  <p class="description"><?php _e( 'The subject of the fulfillment email' , 'shoprocket' ); ?>.</p>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><?php _e('Copy Email To', 'shoprocket'); ?></th>
                <td>
                  <input type="text" name="fulfillment_copy" id="fulfillment_copy" class="regular-text" value="<?php echo ShoprocketSetting::getValue('fulfillment_copy', true); ?>" />
                  <p class="description"><?php _e( 'Use commas to separate addresses.' , 'shoprocket' ); ?></p>
                </td>
              </tr>
            </tbody>
          </table>
          <div class="mustache_elements alert-message hint">
            <p class="description"><?php _e( 'You can use the following elements in either the HTML or Plain text email templates. Click once to select, then copy' , 'shoprocket' ); ?>:</p>
            <?php foreach($mustache_elements as $me): ?>
              <span id="url-<?php echo $me; ?>-fulfillment" onclick="SelectText('url-<?php echo $me; ?>-fulfillment');">{{<?php echo $me; ?>}}</span> 
            <?php endforeach; ?>
          </div>
          <table class="form-table">
            <tbody>
              <tr valign="top">
                <th scope="row"><?php _e('Send HTML Emails', 'shoprocket'); ?></th>
                <td>
                  <input type="radio" name="fulfillment_send_html_emails" class="sendHtmlYes" id="fulfillment_send_html_emails_yes" value="1" <?php echo ShoprocketSetting::getValue('fulfillment_send_html_emails') == 1 ? 'checked="checked" ' : '' ?>/>
                  <label for="fulfillment_send_html_emails_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
                  <input type="radio" name="fulfillment_send_html_emails" class="sendHtmlNo" id="fulfillment_send_html_emails_no" value="" <?php echo ShoprocketSetting::getValue('fulfillment_send_html_emails') != 1 ? 'checked="checked" ' : '' ?>/>
                  <label for="fulfillment_send_html_emails_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
                </td>
              </tr>
              <tr valign="top" class="fulfillment_html_email_block">
                <th scope="row"><?php _e('HTML Email', 'shoprocket'); ?></th>
                <td>
                  <textarea id="fulfillment_html_email" name="fulfillment_html_email" class="large-textarea"><?php echo ShoprocketSetting::getValue('fulfillment_html_email'); ?></textarea>
                  <div id="fulfillment_dialog_html" class="dialogBox">
                    <iframe id="fulfillment_html_preview" class="iframe_preview"></iframe>
                  </div>
                  <p class="description"><?php _e( 'Use this section to add elements to the email in an HTML format' , 'shoprocket' ); ?>.</p>
                </td>
              </tr>
              <tr valign="top" class="fulfillment_html_email_block">
                <th scope="row"></th>
                <td>
                  <input type="button" class="button-secondary dialogOpenHtml" id="fulfillment_button_html" value="<?php _e( 'Preview HTML Email' , 'shoprocket' ); ?>"/>
                  <input type="button" class="button-secondary copyBlock" id="fulfillment_copy_html" value="<?php _e( 'Copy HTML to Plain' , 'shoprocket' ); ?>"/>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><?php _e('Plain Text Email', 'shoprocket'); ?></th>
                <td>
                  <textarea id="fulfillment_plain_email" class="large-textarea" name="fulfillment_plain_email"><?php echo ShoprocketSetting::getValue('fulfillment_plain_email'); ?></textarea>
                  <div id="fulfillment_dialog_plain" class="dialogBox">
                    <iframe id="fulfillment_plain_preview" class="iframe_preview"></iframe>
                  </div>
                  <p class="description"><?php _e( 'Use this section to write up your email in Plain text format' , 'shoprocket' ); ?>.</p>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"></th>
                <td>
                  <input type="button" class="button-secondary dialogOpenPlain" id="fulfillment_button_plain" value="<?php _e( 'Preview Plain Text Email' , 'shoprocket' ); ?>"/>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><?php _e('Send Test Email', 'shoprocket'); ?></th>
                <td>
                  <input type="hidden" id="fulfillment_test_email_status" value="fulfillment">
                  <input type="text" value="" class="defaultText fulfillment_test_email_address" title="<?php _e('Email Address', 'shoprocket'); ?>" id="fulfillment_test_email_address"/>
                  <input type="button" class="button-secondary test_email" id="fulfillment_test_email" value="<?php _e( 'Send' , 'shoprocket' ); ?>" />
                </td>
              </tr>
              <tr valign="top">
                <th scope="row">
                  <?php submit_button(null, 'primary', 'fulfillment_submit'); ?>
                </th>
                <td></td>
              </tr>
            </tbody>
          </table>
        </form>
      <?php else: ?>
        <h3 class="loading"><?php _e("Please set up your products in the", "shoprocket"); ?> <a href="admin.php?page=shoprocket-products"><?php _e("Shoprocket Products", "shoprocket"); ?></a> <?php _e("page before configuring these email notifications","shoprocket"); ?>.</h3>
      <?php endif;?>
    </div>
    <div id="notifications-reminder_settings" class="pane">
      <?php
      $subscriptions = ShoprocketProduct::loadProductsOutsideOfClass('id, name, item_number', 'is_membership_product = 1 OR is_paypal_subscription = 1 OR spreedly_subscription_id > 0');
      if(!empty($subscriptions)):
      ?>
        <h3><?php _e('Subscription Reminders', 'shoprocket'); ?></h3>
        <p class="description"><?php _e( 'This section allows you to send reminder renewal emails based on subscriptions that have been purchased.' , 'shoprocket' ); ?></p>
        <form action="" method="post">
          <input type="hidden" name="shoprocket-action" value="save reminder" />
          <input type="hidden" name="reminder[id]" value="<?php echo $reminder->id ?>" />
          <table class="form-table">
            <tbody>
              <tr valign="top">
                <th scope="row"><?php _e('Subscription', 'shoprocket'); ?></th>
                <td>
                  <select name="reminder[subscription_plan_id]">
                    <option value=""></option>
                    <?php
                    foreach($subscriptions as $s) {
                      $selected = ($reminder->subscription_plan_id == $s->id) ? 'selected="selected"' : '';
                      echo '<option value="' . $s->id . '" ' . $selected . '>' . $s->name . ' (#' . $s->item_number . ')</option>';
                    }
                    ?>
                  </select>
                  <span class="description"><?php _e('Select the subscription that this reminder will apply to', 'shoprocket'); ?>.</span>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><?php _e('Interval', 'shoprocket'); ?></th>
                <td>
                  <input class="small-text" type="text" name="reminder[interval]" id="reminder_interval" value="<?php echo $reminder->interval ?>" />
                  <select name="reminder[interval_unit]">
                    <option value="days" <?php echo ($reminder->interval_unit == 'days') ? 'selected="selected"' : ''; ?>><?php _e( 'Days' , 'shoprocket' ); ?></option>
                    <option value="weeks" <?php echo ($reminder->interval_unit == 'weeks') ? 'selected="selected"' : ''; ?>><?php _e( 'Weeks' , 'shoprocket' ); ?></option>
                    <option value="months"<?php echo ($reminder->interval_unit == 'months') ? 'selected="selected"' : ''; ?>><?php _e( 'Months' , 'shoprocket' ); ?></option>
                  </select>
                  <span class="description"><?php _e('Select the approximate date or time to send the email out before the subscription expires', 'shoprocket'); ?>.</span>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><?php _e('From Name', 'shoprocket'); ?></th>
                <td>
                  <input type="text" name="reminder[from_name]" id="reminder-from_name" class="regular-text" value="<?php echo $reminder->from_name; ?>" />
                  <p class="description"><?php _e( 'The name of the person from whom the email reminder will be sent. You may want this to be your company name.' , 'shoprocket' ); ?></p>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><?php _e('From Address', 'shoprocket'); ?></th>
                <td>
                  <input type="text" name="reminder[from_email]" id="reminder-from_email" class="regular-text" value="<?php echo $reminder->from_email; ?>" />
                  <p class="description"><?php _e( 'The email address the email reminder will be from.' , 'shoprocket' ); ?></p>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><?php _e('Email Subject', 'shoprocket'); ?></th>
                <td>
                  <input type="text" name="reminder[subject]" id="reminder-subject" class="regular-text" value="<?php echo $reminder->subject; ?>" />
                  <p class="description"><?php _e( 'The subject of the email reminder' , 'shoprocket' ); ?>.</p>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><?php _e('Copy Email To', 'shoprocket'); ?></th>
                <td>
                  <input type="text" name="reminder[copy_to]" id="reminder-copy_to" class="regular-text" value="<?php echo $reminder->copy_to; ?>" />
                  <p class="description"><?php _e( 'Use commas to separate addresses.' , 'shoprocket' ); ?></p>
                </td>
              </tr>
            </tbody>
          </table>
          <div class="mustache_elements alert-message hint">
            <p class="description"><?php _e( 'You can use the following elements in either the HTML or Plain text email templates. Click once to select, then copy' , 'shoprocket' ); ?>:</p>
            <?php foreach($mustache_elements_reminders as $mer): ?>
              <span id="url-<?php echo $mer; ?>-reminder" onclick="SelectText('url-<?php echo $mer; ?>-reminder');">{{<?php echo $mer; ?>}}</span> 
            <?php endforeach; ?>
          </div>
          <table class="form-table">
            <tbody>
              <tr valign="top">
                <th scope="row"><?php _e('Send HTML Emails', 'shoprocket'); ?></th>
                <td>
                  <input type="radio" name="reminder[reminder_send_html_emails]" class="sendHtmlYes" id="reminder_send_html_emails_yes" value="1" <?php echo $reminder->reminder_send_html_emails == 1 ? 'checked="checked" ' : '' ?>/>
                  <label for="reminder_send_html_emails_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
                  <input type="radio" name="reminder[reminder_send_html_emails]" class="sendHtmlNo" id="reminder_send_html_emails_no" value="" <?php echo $reminder->reminder_send_html_emails != 1 ? 'checked="checked" ' : '' ?>/>
                  <label for="reminder_send_html_emails_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
                </td>
              </tr>
              <tr valign="top" class="reminder_html_email_block">
                <th scope="row"><?php _e('HTML Email', 'shoprocket'); ?></th>
                <td>
                  <textarea id="reminder_html_email" name="reminder[reminder_html_email]" class="large-textarea"><?php echo $reminder->reminder_html_email; ?></textarea>
                  <div id="reminder_dialog_html" class="dialogBox">
                    <iframe id="reminder_html_preview" class="iframe_preview"></iframe>
                  </div>
                  <p class="description"><?php _e( 'Use this section to add elements to the email in an HTML format' , 'shoprocket' ); ?>.</p>
                </td>
              </tr>
              <tr valign="top" class="reminder_html_email_block">
                <th scope="row"></th>
                <td>
                  <input type="button" class="button-secondary dialogOpenHtml" id="reminder_button_html" value="<?php _e( 'Preview HTML Email' , 'shoprocket' ); ?>"/>
                  <input type="button" class="button-secondary copyBlock" id="reminder_copy_html" value="<?php _e( 'Copy HTML to Plain' , 'shoprocket' ); ?>"/>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><?php _e('Plain Text Email', 'shoprocket'); ?></th>
                <td>
                  <textarea id="reminder_plain_email" class="large-textarea" name="reminder[reminder_plain_email]"><?php echo $reminder->reminder_plain_email; ?></textarea>
                  <div id="reminder_dialog_plain" class="dialogBox">
                    <iframe id="reminder_plain_preview" class="iframe_preview"></iframe>
                  </div>
                  <p class="description"><?php _e( 'Use this section to write up your email in Plain text format' , 'shoprocket' ); ?>.</p>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"></th>
                <td>
                  <input type="button" class="button-secondary dialogOpenPlain" id="reminder_button_plain" value="<?php _e( 'Preview Plain Text Email' , 'shoprocket' ); ?>"/>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><?php _e('Enable', 'shoprocket'); ?></th>
                <td>
                  <input type="radio" name="reminder[enable]" id="reminder-enable_yes" value="1" <?php echo $reminder->enable == 1 ? 'checked="checked" ' : ''; ?>/>
                  <label for="reminder-enable_yes"><?php _e('Yes', 'shoprocket'); ?></label>
                  <input type="radio" name="reminder[enable]" id="reminder-enable_no" value="" <?php echo $reminder->enable != 1 ? 'checked="checked" ' : ''; ?>/>
                  <label for="reminder-enable_no"><?php _e('No', 'shoprocket'); ?></label>
                  <p class="description"><?php _e('Select yes to enable this reminder', 'shoprocket'); ?>.</p>
                </td>
              </tr>
              <?php if($reminder->id > 0): ?>
              <tr valign="top">
                <th scope="row"><?php _e('Send Test Email', 'shoprocket'); ?></th>
                <td>
                  <input type="hidden" id="reminder_test_email_status" value="reminder">
                  <input type="hidden" id="reminder_test_email_statusId" value="<?php echo $reminder->id; ?>">
                  <input type="text" value="" class="defaultText reminder_test_email_address" title="<?php _e('Email Address', 'shoprocket'); ?>" id="reminder_test_email_address"/>
                  <input type="button" class="button-secondary test_email" id="reminder_test_email" value="<?php _e( 'Send' , 'shoprocket' ); ?>" />
                </td>
              </tr>
              <?php endif; ?>
              <tr valign="top">
                <th scope="row">
                  <p>
                    <?php submit_button(null, 'primary', 'reminder_submit', false); ?>
                    <?php if($reminder->id > 0): ?>
                    <a href="?page=shoprocket-settings&tab=notifications_settings&task=cancel_reminder" class="button-secondary linkButton"><?php _e( 'Cancel' , 'shoprocket' ); ?></a>
                    <?php endif; ?>
                  </p>
                </th>
                <td></td>
              </tr>
              <tr valign="top">
                <th scope="row"></th>
                <td></td>
              </tr>
            </tbody>
          </table>
        </form>
        <?php
        $rem = new ShoprocketMembershipReminders();
        if(count($rem->getModels()) > 0) :
        ?>
          <table class="widefat ShoprocketHighlightTable" id="subscription_reminders_table">
            <thead>
              <tr>
                <th><?php _e('Subscription', 'shoprocket'); ?></th>
                <th><?php _e('Active', 'shoprocket'); ?></th>
                <th><?php _e('Interval', 'shoprocket'); ?></th>
                <!--th><?php _e('Subscription Type', 'shoprocket'); ?></th-->
                <th><?php _e('Actions', 'shoprocket'); ?></th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th><?php _e('Subscription', 'shoprocket'); ?></th>
                <th><?php _e('Active', 'shoprocket'); ?></th>
                <th><?php _e('Interval', 'shoprocket'); ?></th>
                <!--th><?php _e('Subscription Type', 'shoprocket'); ?></th-->
                <th><?php _e('Actions', 'shoprocket'); ?></th>
              </tr>
            </tfoot>
            <tbody>
              <?php
                foreach($rem->getModels() as $r) {
                  $product = new ShoprocketProduct($r->subscription_plan_id); ?>
                  <tr>
                    <td><a href="?page=shoprocket-settings&tab=notifications_settings&task=edit_reminder&id=<?php echo $r->id; ?>"><?php echo $product->name; ?></a></td>
                    <td><?php echo $r->enable == 1 ? 'Yes' : 'No'; ?></td>
                    <td><?php echo $r->interval . ' ' . $r->interval_unit; ?></td>
                    <!--td><?php echo ucfirst($r->membership_type); ?></td-->
                    <td><a href="?page=shoprocket-settings&tab=notifications_settings&task=edit_reminder&id=<?php echo $r->id; ?>"><?php _e( "Edit" , "shoprocket" ); ?></a> | <a class="delete" href="?page=shoprocket-settings&tab=notifications_settings&task=delete_reminder&id=<?php echo $r->id; ?>"><?php _e( "Delete" , "shoprocket" ); ?></a></td>
                  </tr>
                <?php }
              ?>
            </tbody>
          </table>
        <?php endif; ?>
      <?php else: ?>
        <h3 class="loading"><?php _e("Please set up your subscriptions in the", "shoprocket"); ?> <a href="admin.php?page=shoprocket-products"><?php _e("Shoprocket Products", "shoprocket"); ?></a> <?php _e('or', 'shoprocket'); ?> <a href="admin.php?page=shoprocket-paypal-subscriptions"><?php _e("Shoprocket PayPal Subscriptions", "shoprocket"); ?></a> <?php _e("pages before configuring these email notifications","shoprocket"); ?>.</h3>
      <?php endif; ?>
    </div>
    <div id="notifications-email_log_settings" class="pane">
      <h3><?php _e('Email Log', 'shoprocket'); ?></h3>
      <p class="description"><?php _e( 'You can enable the email log to keep track of all emails sent through Shoprocket.  You can also resend all emails from this tab.' , 'shoprocket' ); ?></p>
      <form id="notificationSettingsForm" action="" method="post">
        <input type="hidden" name="shoprocket-action" value="email log settings" />
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <th scope="row"><?php _e('Enable Email Logging', 'shoprocket'); ?></th>
              <td>
                <input type="radio" name="emailLog[enable_email_log]" id="enable_email_log_yes" value="1" <?php echo ShoprocketSetting::getValue('enable_email_log') == '1' ? 'checked="checked" ' : '' ?>/>
                <label for="enable_email_log_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
                <input type="radio" name="emailLog[enable_email_log]" id="enable_email_log_no" value="" <?php echo ShoprocketSetting::getValue('enable_email_log') != '1' ? 'checked="checked" ' : '' ?>/>
                <label for="enable_email_log_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><p class="description"><?php _e( 'Select which of the following types of emails you want to log' , 'shoprocket' ); ?></p></th>
              <td></td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Email Receipts', 'shoprocket'); ?></th>
              <td>
                <input type="radio" name="emailLog[log_email_receipts]" id="log_email_receipts_yes" value="1" <?php echo ShoprocketSetting::getValue('log_email_receipts') == '1' ? 'checked="checked" ' : '' ?>/>
                <label for="log_email_receipts_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
                <input type="radio" name="emailLog[log_email_receipts]" id="log_email_receipts_no" value="" <?php echo ShoprocketSetting::getValue('log_email_receipts') != '1' ? 'checked="checked" ' : '' ?>/>
                <label for="log_email_receipts_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
              </td>
            </tr>
            <!--tr valign="top">
              <th scope="row">Password Reset Emails</th>
              <td>
              
              </td>
            </tr-->
            <tr valign="top">
              <th scope="row"><?php _e('Order Status', 'shoprocket'); ?></th>
              <td>
                <input type="radio" name="emailLog[log_status_update_emails]" id="log_status_update_emails_yes" value="1" <?php echo ShoprocketSetting::getValue('log_status_update_emails') == '1' ? 'checked="checked" ' : '' ?>/>
                <label for="log_status_update_emails_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
                <input type="radio" name="emailLog[log_status_update_emails]" id="log_status_update_emails_no" value="" <?php echo ShoprocketSetting::getValue('log_status_update_emails') != '1' ? 'checked="checked" ' : '' ?>/>
                <label for="log_status_update_emails_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Followup', 'shoprocket'); ?></th>
              <td>
                <input type="radio" name="emailLog[log_followup_emails]" id="log_followup_emails_yes" value="1" <?php echo ShoprocketSetting::getValue('log_followup_emails') == '1' ? 'checked="checked" ' : '' ?>/>
                <label for="log_followup_emails_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
                <input type="radio" name="emailLog[log_followup_emails]" id="log_followup_emails_no" value="" <?php echo ShoprocketSetting::getValue('log_followup_emails') != '1' ? 'checked="checked" ' : '' ?>/>
                <label for="log_followup_emails_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Fulfillment', 'shoprocket'); ?></th>
              <td>
                <input type="radio" name="emailLog[log_fulfillment_emails]" id="log_fulfillment_emails_yes" value="1" <?php echo ShoprocketSetting::getValue('log_fulfillment_emails') == '1' ? 'checked="checked" ' : '' ?>/>
                <label for="log_fulfillment_emails_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
                <input type="radio" name="emailLog[log_fulfillment_emails]" id="log_fulfillment_emails_no" value="" <?php echo ShoprocketSetting::getValue('log_fulfillment_emails') != '1' ? 'checked="checked" ' : '' ?>/>
                <label for="log_fulfillment_emails_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Reminder', 'shoprocket'); ?></th>
              <td>
                <input type="radio" name="emailLog[log_reminder_emails]" id="log_reminder_emails_yes" value="1" <?php echo ShoprocketSetting::getValue('log_reminder_emails') == '1' ? 'checked="checked" ' : '' ?>/>
                <label for="log_reminder_emails_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
                <input type="radio" name="emailLog[log_reminder_emails]" id="log_reminder_emails_no" value="" <?php echo ShoprocketSetting::getValue('log_reminder_emails') != '1' ? 'checked="checked" ' : '' ?>/>
                <label for="log_reminder_emails_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Carbon Copy', 'shoprocket'); ?></th>
              <td>
                <input type="radio" name="emailLog[log_cc_emails]" id="log_cc_emails_yes" value="1" <?php echo ShoprocketSetting::getValue('log_cc_emails') == '1' ? 'checked="checked" ' : '' ?>/>
                <label for="log_cc_emails_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
                <input type="radio" name="emailLog[log_cc_emails]" id="log_cc_emails_no" value="" <?php echo ShoprocketSetting::getValue('log_cc_emails') != '1' ? 'checked="checked" ' : '' ?>/>
                <label for="log_cc_emails_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Resent', 'shoprocket'); ?></th>
              <td>
                <input type="radio" name="emailLog[log_resent_emails]" id="log_resent_emails_yes" value="1" <?php echo ShoprocketSetting::getValue('log_resent_emails') == '1' ? 'checked="checked" ' : '' ?>/>
                <label for="log_resent_emails_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
                <input type="radio" name="emailLog[log_resent_emails]" id="log_resent_emails_no" value="" <?php echo ShoprocketSetting::getValue('log_resent_emails') != '1' ? 'checked="checked" ' : '' ?>/>
                <label for="log_resent_emails_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><?php _e('Test', 'shoprocket'); ?></th>
              <td>
                <input type="radio" name="emailLog[log_test_emails]" id="log_test_emails_yes" value="1" <?php echo ShoprocketSetting::getValue('log_test_emails') == '1' ? 'checked="checked" ' : '' ?>/>
                <label for="log_test_emails_yes"><?php _e( 'Yes' , 'shoprocket' ); ?></label>
                <input type="radio" name="emailLog[log_test_emails]" id="log_test_emails_no" value="" <?php echo ShoprocketSetting::getValue('log_test_emails') != '1' ? 'checked="checked" ' : '' ?>/>
                <label for="log_test_emails_no"><?php _e( 'No' , 'shoprocket' ); ?></label>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row">
                <?php submit_button(); ?>
              </th>
              <td></td>
            </tr>
          </tbody>
        </table>
      </form>
      <?php
      $email = new ShoprocketEmailLog();
      $emails = $email->getModels(null, 'order by send_date DESC');
      if(count($emails) && ShoprocketSetting::getValue('enable_email_log') == 1):
      ?>
        <h3><?php _e( 'Email Activity' , 'shoprocket' ); ?></h3>
        <table class="widefat ShoprocketHighlightTable" id="email_log_table">
        <thead>
          <tr>
            <th><?php _e( 'ID' , 'shoprocket' ); ?></th>
            <th><?php _e( 'Send Date' , 'shoprocket' ); ?></th>
            <th><?php _e( 'From' , 'shoprocket' ); ?></th>
            <th><?php _e( 'To' , 'shoprocket' ); ?></th>
            <!-- ><th>Attachments</th> -->
            <th><?php _e( 'Order Number' , 'shoprocket' ); ?></th>
            <th><?php _e( 'Email Type' , 'shoprocket' ); ?></th>
            <th><?php _e( 'CC' , 'shoprocket' ); ?></th>
            <th><?php _e( 'Status' , 'shoprocket' ); ?></th>
            <th><?php _e( 'Subject' , 'shoprocket' ); ?></th>
            <th style="min-width:96px;"><?php _e( 'Actions' , 'shoprocket' ); ?></th>
          </tr>
        </thead>
        <tfoot>
          <tr>
            <th><?php _e( 'ID' , 'shoprocket' ); ?></th>
            <th><?php _e( 'Send Date' , 'shoprocket' ); ?></th>
            <th><?php _e( 'From' , 'shoprocket' ); ?></th>
            <th><?php _e( 'To' , 'shoprocket' ); ?></th>
            <!-- <th>Attachments</th> -->
            <th><?php _e( 'Order Number' , 'shoprocket' ); ?></th>
            <th><?php _e( 'Email Type' , 'shoprocket' ); ?></th>
            <th><?php _e( 'CC' , 'shoprocket' ); ?></th>
            <th><?php _e( 'Status' , 'shoprocket' ); ?></th>
            <th><?php _e( 'Subject' , 'shoprocket' ); ?></th>
            <th><?php _e( 'Actions' , 'shoprocket' ); ?></th>
          </tr>
        </tfoot>
        <tbody>
          <?php foreach($emails as $e): ?>
           <?php
           $successClass = '';
           if($e->status == 'FAILED' || $e->status == 'RESEND FAILED') {
             $successClass = 'class="emailLogFailed"';
           }
           elseif($e->email_type == 'TEST') {
             $successClass = 'class="emailLogTest"';
           }
           else {
             $successClass = 'class="emailLogSuccess"';
           }
           ?>
           <tr <?php echo $successClass?>>
             <td><?php echo $e->id; ?></td>
             <td><?php echo date(get_option('date_format'), strtotime($e->send_date)) ?><br /><?php echo date(get_option('time_format'), strtotime($e->send_date)) ?></td>
             <td><?php echo $e->from_name ?><br /><?php echo $e->from_email ?></td>
             <td><?php echo $e->to_name ?><br /><?php echo $e->to_email ?></td>
             <!-- <td><?php //echo $e->attachments ?></td> -->
             <?php
             $order = new ShoprocketOrder($e->order_id);
             if($order->trans_id != null) { ?>
               <td><a href="?page=shoprocket_admin&task=view&id=<?php echo $e->order_id ?>"><?php echo $order->trans_id ?></a></td>
             <?php }
             else { ?>
               <td><?php _e( 'No Order Number' , 'shoprocket' ); ?></td>
             <?php } ?>
             <td><?php echo $e->email_type ?></td>
             <?php
             if($e->copy == 'COPY') { ?>
               <td><?php _e( 'Yes' , 'shoprocket' ); ?></td>
             <?php }
             else { ?>
               <td><?php _e( 'No' , 'shoprocket' ); ?></td>
             <?php } ?>
             <td><?php echo $e->status ?></td>
             <td><?php echo $e->subject ?></td>
             <td>
               <form action="" method='post'>
                 <input type="hidden" id="resend_<?php echo $e->id ?>_id" value="<?php echo $e->id ?>">
                 <a href="javascript:void(0);" onClick="viewEmail(<?php echo $e->id; ?>);" id="view_email_<?php echo $e->id; ?>"><?php _e( "View" , "shoprocket" ); ?></a> | 
                 <a id="resend_<?php echo $e->id ?>" class="resend" href="javascript:void(0);"><?php _e( 'Resend' , 'shoprocket' ); ?></a>
               </form>
               <div id="email_dialog_<?php echo $e->id; ?>" class="email_dialog"></div>
             </td>
           </tr>
          <?php endforeach; ?>
        </tbody>
        </table>
      <?php endif; ?>
    </div>
    <div id="notifications-advanced_notifications" class="pane">
      <h3><?php _e('Advanced Notifications', 'shoprocket'); ?></h3>
      <form action="" method="post">
        <input type="hidden" name="shoprocket-action" value="advanced notifications">
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <th scope="row"><?php _e('Enable Advanced Notifications', 'shoprocket'); ?></th>
              <td>
                <input type="radio" name="enable_advanced_notifications" id="enable_advanced_notifications_yes" value="1" <?php echo ShoprocketSetting::getValue('enable_advanced_notifications') == 1 ? 'checked="checked" ' : '' ?>/>
                <label for="enable_advanced_notifications_yes"><?php _e('Yes', 'shoprocket'); ?></label>
                <input type="radio" name="enable_advanced_notifications" id="enable_advanced_notifications_no" value="" <?php echo ShoprocketSetting::getValue('enable_advanced_notifications') != 1 ? 'checked="checked" ' : '' ?>/>
                <label for="enable_advanced_notifications_no"><?php _e('No', 'shoprocket'); ?></label>
                <p class="description"><?php _e('Selecting this will allow you to enable Advanced Notifications that include HTML emails and emails for status updates, fulfillment emails, followup emails and more.', 'shoprocket'); ?></p>
              </td>
            </tr>
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
  </div>
<?php endif; ?>
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
    })
  })(jQuery);
</script>
<?php if(false && ShoprocketSetting::getValue('enable_advanced_notifications')): ?>
  <script type="text/javascript">
    (function($){
      $(document).ready(function(){
        $('#fulfillment-products').tokenInput(productSearchUrl, { theme: 'facebook', hintText: '<?php _e("Type in a product name", "shoprocket"); ?>',
          onReady: function() { 
            var data = {
              action: 'loadPromotionProducts',
              productId: $('#fulfillment-products-hidden').val()
            };
            $.post(ajaxurl + '?action=loadPromotionProducts', data, function(results) {
              for(var i=0; i<results.length; i++) {
                var item = results[i];
                if ((item.id) !== '') {
                  $('#fulfillment-products').tokenInput('add', {id: item.id, name: item.name});
                }
              }
            }, 'json');
          } 
        });
      
        $('a.tab_function').click(function() {
          if($(this).hasClass('notifications-order_status_settings')) {
          <?php 
          $setting = new ShoprocketSetting();
          $opts = explode(',', ShoprocketSetting::getValue('status_options'));
          foreach($opts as $o): 
            $o = str_replace(' ', '_', trim($o));
            if(!empty($o)): ?>
            if(typeof('<?php echo $o; ?>') != "undefined" && '<?php echo $o; ?>' !== null) {
              backAndForth('<?php echo $o; ?>', 'html');
              backAndForth('<?php echo $o; ?>', 'plain');
            }
            <?php endif; ?>
          <?php endforeach; ?>
          }
          else {
            var tabName = $(this).attr('id').replace('_tab','');
            var checkExists = $('#' + tabName + '_submit').attr('id');
            if(typeof(checkExists) != "undefined" && checkExists !== null) {
              backAndForth(tabName, 'html');
              backAndForth(tabName, 'plain');
            }
          }
        });
      
        $('.button-primary').click(function() {
          var submitName = $(this).attr('id').replace('_submit','');
          if(typeof(submitName) != "undefined" && submitName !== null) {
            backAndForth(submitName, 'html');
            backAndForth(submitName, 'plain');
          }
        });
        $('#email_log_table').dataTable({
          "aaSorting": [[0, 'desc']],
          "sPaginationType": "bootstrap",
          "bAutoWidth": false,
          "aoColumns": [
            {"bVisible": false},
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null
          ],
        });
        
        $('#subscription_reminders_table, #fullfillment_centers_table').dataTable({
          "aaSorting": [[0,'desc']],
          "sPaginationType": "bootstrap",
        });
      
        $('.dialogBox, .email_dialog').each(function() {
          $(this).dialog(options);
        });
            
        $('.dialogOpenEmailText').click(function() {
          var dialogName = $(this).attr('id').replace('view_message_','');
          $('.email_message_' + dialogName).dialog('open');
        });
      
        $('.dialogOpenHtml').click(function() {
          var dialogName = $(this).attr('id').replace('_button_html','');
          $('#' + dialogName + '_dialog_html').dialog('open');
        });
      
        $('.dialogOpenPlain').click(function() {
          var dialogName = $(this).attr('id').replace('_button_plain','');
          backAndForth(dialogName, 'html');
          backAndForth(dialogName, 'plain');
          var plain_email_receipt = $('#' + dialogName + '_plain_email').val();
          text = plain_email_receipt.replace(/</g, '&lt;').replace( /\r?\n/g, "<br />" );
          $('#' + dialogName + '_dialog_plain').html('<pre class="pre">' + text + '</pre>').dialog('open');
        });
        
        $('.sendHtmlYes').click(function() {
          var blockName = $(this).attr('id').replace('_send_html_emails_yes','');
          $('.' + blockName + '_html_email_block').show();
          backAndForth(blockName, 'html');
          backAndForth(blockName, 'plain');
        });
      
        $('.sendHtmlNo').click(function() {
          var blockName = $(this).attr('id').replace('_send_html_emails_no','');
          $('.' + blockName + '_html_email_block').hide();
        });
      
        $('.sendHtmlNo').each(function() {
          if($(this).attr('checked')) {
            var blockName = $(this).attr('id').replace('_send_html_emails_no','');
            $('.' + blockName + '_html_email_block').hide();
          }
        });
      
        $('.copyBlock').click(function(){
          var blockName = $(this).attr('id').replace('_copy_html','');
          copyBlock(blockName);
        });
      
        $('.resend').click(function() {
          var id = $(this).attr('id');
          var log_id = $('#' + id + '_id').val();
          var data = {
            action: 'resend_email_from_log',
            id: log_id,
          };
          $.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
            dataType: 'json',
            success: function(result) {
              $('#saveResult').html("<div id='saveMessage' class='" + result[0] + "'></div>");
              $('#saveMessage').append("<p>" + result[1] + "</p>").hide().fadeIn(1500).delay(3000).fadeOut(1500);
              $(".defaultText").addClass("defaultTextActive");
              $(".defaultText").val("<?php _e( 'Email Address' , 'shoprocket' ); ?>");
            }
          });
          return false;
        });
      
        $(".defaultText").focus(function(srcc) {
          if ($(this).val() == $(this)[0].title) {
            $(this).removeClass("defaultTextActive");
            $(this).val("");
          }
        });

        $(".defaultText").blur(function() {
          if ($(this).val() == "") {
            $(this).addClass("defaultTextActive");
            $(this).val($(this)[0].title);
          }
        });

        $(".defaultText").blur();
      
        $('.test_email').click(function() {
          var id = $(this).attr('id');
          var email_address = $('#' + id + '_address').val();
          var status = $('#' + id + '_status').val();
          var statusId = $('#' + id + '_statusId').val();
          var data = {
            action: 'send_test_email',
            email: email_address,
            status: status
          };
          $.ajax({
            type: "POST",
            url: ajaxurl + '?type=' + status + '&id=' + statusId,
            data: data,
            dataType: 'json',
            success: function(result) {
              $('#saveResult').html("<div id='saveMessage' class='" + result[0] + "'></div>");
              $('#saveMessage').append("<p>" + result[1] + "</p>").hide().fadeIn(1500).delay(3000).fadeOut(1500);
              $(".defaultText").addClass("defaultTextActive");
              $(".defaultText").val("<?php _e( 'Email Address' , 'shoprocket' ); ?>");
            }
          });
          return false;
        });
        initializeShoprocketCodeMirror('receipt');
        initializeShoprocketCodeMirror('followup');
        <?php if(!empty($p)): ?>
          initializeShoprocketCodeMirror('fulfillment');
        <?php endif; ?>
        <?php if(!empty($subscriptions)): ?>
        initializeShoprocketCodeMirror('reminder');
        <?php endif; ?>
        <?php 
        $opts = explode(',', ShoprocketSetting::getValue('status_options'));
        foreach($opts as $o):
          $o = str_replace(' ', '_', trim($o));
          if(!empty($o)): ?>
            initializeShoprocketCodeMirror('<?php echo $o; ?>');
          <?php endif; ?>
        <?php endforeach; ?>
        function copyBlock(blockName){
          newEditor[blockName + '_html'].toTextArea();
          newEditor[blockName + '_plain'].toTextArea();
          var html_content = $('#' + blockName + '_html_email').val();
          $('#' + blockName + '_plain_email').val(convert_html_to_plain(html_content));
          enableShoprocketCodeMirror(blockName, '_html');
          enableShoprocketCodeMirror(blockName, '_plain');
        }
        function initializeShoprocketCodeMirror(sectionName){
          if(typeof(sectionName) != "undefined" && sectionName !== null) {
            enableShoprocketCodeMirror(sectionName, '_html');
            enableShoprocketCodeMirror(sectionName, '_plain');
            setTimeout("updatePreview('" + sectionName + "', '_html')", 300);
          }
        }
        function productSearchUrl() {
          var url = ajaxurl + '?action=promotionProductSearch';
          return url;
        }
      })
      $(document).on('click', '.delete', function(e) {
        return confirm('Are you sure you want to delete this item?');
      });
    })(jQuery);
    $jq = jQuery.noConflict();
    function viewEmail(id) {
      $jq('.email_dialog').dialog(options);
      var data = {
        action: 'view_email',
        log_id: id
      };
      $jq.ajax({
        type: "POST",
        url: ajaxurl,
        data: data,
        success: function(result) {
          $jq('#email_dialog_' + id).html(result);
          $jq('#email_dialog_' + id).dialog('open');
        }
      });
      return false;
    }
  </script>
<?php endif; ?>