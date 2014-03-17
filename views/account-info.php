<?php
if(isset($data['errors']) && count($data['errors'])) {
  echo ShoprocketCommon::showErrors($data['errors'], "<p><b>We're sorry. Your account was not updated for the following reasons:</b></p>");
}
if(isset($data['message'])) {
  echo '<div class="ShoprocketSuccess">' . $data['message'] . '</div>';
}
?>

<form id="ShoprocketAccountLogin" class="phorm2" action="" method="post">
  <input type="hidden" name="shoprocket-task" value="account-update" />
  <ul class='shortLabels'>
    <li><h3><?php _e( 'Update Your Account Information' , 'shoprocket' ); ?></h3></li>
    <li>
      <label class="short" for="login-first_name"><?php _e( 'First name' , 'shoprocket' ); ?>:</label>
      <input type="text" id="login-first_name" name="login[first_name]" value="<?php echo $data['account']->firstName ?>" />
    </li>
    <li>
      <label class="short" for="login-last_name"><?php _e( 'Last name' , 'shoprocket' ); ?>:</label>
      <input type="text" id="login-last_name" name="login[last_name]" value="<?php echo $data['account']->lastName ?>" />
    </li>
    <li>
      <label class="short" for="login-email"><?php _e( 'Email' , 'shoprocket' ); ?>:</label>
      <input type="text" id="login-email" name="login[email]" value="<?php echo $data['account']->email ?>" />
    </li>
    <li>
      <label class="short" for="login-username"><?php _e( 'Username' , 'shoprocket' ); ?>:</label>
      <input type="text" id="login-username" name="login[username]" value="<?php echo $data['account']->username ?>" />
    </li>
    <li>
      <h3><?php _e( 'Update Your Password' , 'shoprocket' ); ?></h3>
      <p><?php _e( 'Leave blank to keep current password.' , 'shoprocket' ); ?></p>
    </li>
    <li>
      <label class="short" for="login-password"><?php _e( 'Password' , 'shoprocket' ); ?>:</label>
      <input type="password" id="login-password" name="login[password]" value="" />
      <p class="description"><?php _e( 'Enter a new password.' , 'shoprocket' ); ?></p>
    </li>
    <li>
      <label class="short" for="login-password2">&nbsp;</label>
      <input type="password" id="login-password2" name="login[password2]" value="" />
      <p class="description"><?php _e('Repeat new password', 'shoprocket'); ?></p>
    </li>https://tracker.moodle.org/browse/MDL-39833
    <li>
      <label class="short" for="submit">&nbsp;</label>
      <input type="submit" name="submit" value="<?php _e('Save', 'shoprocket'); ?>" class="ShoprocketButtonPrimary" />
    </li>
  </ul>
</form>

<?php if($data['url']): ?>
<p id="accountManagementLink"><a href="<?php echo $data['url'] ?>"><?php echo $data['text'] ?></a></p>
<?php endif; ?>