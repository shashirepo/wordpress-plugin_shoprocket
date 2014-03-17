<?php if(ShoprocketSession::get('zendesk_logout_error')): ?>
    <div class="alert-message">
      <?php _e('Zendesk logged you out with the following error','shoprocket'); ?>:<br>
      <?php echo ShoprocketSession::get('zendesk_logout_error'); ?>
    </div>
<?php 
      ShoprocketSession::drop('zendesk_logout_error');
endif; ?>
<?php if(ShoprocketCommon::isLoggedIn()): ?>
  <p>Hi <?php echo $data['account']->firstName; ?>. <?php _e('You are currently logged in.', 'shoprocket'); ?>  
  <a href="<?php echo ShoprocketCommon::appendQueryString('shoprocket-task=logout'); ?>"><?php _e('Log out', 'shoprocket'); ?></a></p>
<?php else: ?>
  <form id="ShoprocketAccountLogin" class="phorm2" action="" method="post">
    <input type="hidden" name="shoprocket-task" value="account-login" />
    <input type="hidden" name="redirect" value="<?php echo $data['redirect'] ?>">
    <ul>
      <li>
        <label class="" for="login-username"><?php _e( 'Username' , 'shoprocket' ); ?>:</label>
        <input type="text" id="login-username" name="login[username]" value="" />
      </li>
      <li>
        <label class="" for="login-password"><?php _e( 'Password' , 'shoprocket' ); ?>:</label>
        <input type="password" id="login-password" name="login[password]" value="" />
      </li>
      <li>
        <label class="" for="submit">&nbsp;</label>
        <input type="submit" name="submit" value="Enter" class="ShoprocketButtonPrimary" />
        <a href='#' id='forgotLink'><?php _e( 'Forgot my password' , 'shoprocket' ); ?></a>
      </li>
    </ul>
  </form>

  <form id="ShoprocketForgotPassword" class="phorm2" action="" method='post'>
    <input type="hidden" name="shoprocket-task" value="account-reset" />
    <p class='ShoprocketNote'><?php _e( 'Enter your username and we will send you a new password.<br/> The email will be sent to the email address you used for your account.' , 'shoprocket' ); ?></p>
    <ul>
      <li>
        <label class="" for="login-username"><?php _e( 'Username' , 'shoprocket' ); ?>:</label>
        <input type="text" id="login-username" name="login[username]" value="" />
      </li>
      <li>
        <input type="submit" name="submit" value="Send New Password" class="ShoprocketButtonPrimary" />
      </li>
    </ul>
  </form>
  
  <script type="text/javascript">
    (function($){
      $(document).ready(function(){
        $('#forgotLink').click(function() {
          $('#ShoprocketForgotPassword').toggle();
        });
      })
    })(jQuery);
  </script> 
  
<?php endif; ?>

<?php if(isset($data['resetResult'])): ?>
  <?php $messageClass = ($data['resetResult']->success) ? 'ShoprocketSuccess' : 'ShoprocketError'; ?>
  <div id='msg' class='<?php echo $messageClass ?>'><p><?php echo $data['resetResult']->message ?></p></div>
<?php endif; ?>