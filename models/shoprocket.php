<?php
class shoprocket {
  
  public function install() {
    global $wpdb;
    $prefix = ShoprocketCommon::getTablePrefix();
    $sqlFile = SHOPROCKET_PATH . '/sql/database.sql';
    $sql = str_replace('[prefix]', $prefix, file_get_contents($sqlFile));
    $queries = explode(";\n", $sql);
    $wpdb->hide_errors();
    foreach($queries as $sql) {
      if(strlen($sql) > 5) {
        $wpdb->query($sql);
        ShoprocketCommon::log("Running: $sql");
      }
    }
    require_once(SHOPROCKET_PATH . "/create-pages.php");

    // Set the version number for this version of Shoprocket
    require_once(SHOPROCKET_PATH . "/models/ShoprocketSetting.php");
    ShoprocketSetting::setValue('version', SHOPROCKET_VERSION_NUMBER);
    
    // Look for hard coded order number
    /*if(FALSE && Shoprocket_ORDER_NUMBER !== false) {
      ShoprocketSetting::setValue('order_number', Shoprocket_ORDER_NUMBER);
      $versionInfo = get_transient('_shoprocket_version_request');
      if(!$versionInfo) {
        $versionInfo = ShoprocketProCommon::getVersionInfo();
        set_transient('_shoprocket_version_request', $versionInfo, 43200);
      }
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Trying to register order number: " . 
        Shoprocket_ORDER_NUMBER . print_r($versionInfo, true));
      if(!$versionInfo) {
        ShoprocketSetting::setValue('order_number', '');
      }
    }  */
    
    $this->upgradeDatabase();
  }
  
  public function scheduledEvents() {
    $offset = get_option( 'gmt_offset' ) * 3600;
    $timestamp = strtotime("3am + 1 day");
    $fixedtime = $timestamp - $offset;
    if(FALSE && !wp_next_scheduled('daily_subscription_reminder_emails')) {
      wp_schedule_event($fixedtime, 'daily', 'daily_subscription_reminder_emails');
    }
    if(FALSE && !wp_next_scheduled('daily_followup_emails')) {
      wp_schedule_event($fixedtime, 'daily', 'daily_followup_emails');
    }
    if(FALSE && class_exists('RGFormsModel') && !wp_next_scheduled('daily_gravity_forms_entry_removal')) {
      wp_schedule_event($fixedtime, 'daily', 'daily_gravity_forms_entry_removal');
    }
    if(!wp_next_scheduled('daily_prune_pending_orders')) {
      wp_schedule_event($fixedtime, 'hourly', 'daily_prune_pending_orders');
    }
  }
  
  public function init() {
    global $shoprocketSettings, $shoprocketObjects;
    $this->loadCoreModels();
    $this->initCurrencySymbols();
    $this->setDefaultPageRoles();
    
    // Allow override for sending email receipts
    define("Shoprocket_EMAILS", apply_filters('shoprocket_send_default_emails', true));
    
    // Verify that upgrade has been run
    if(IS_ADMIN) {
      $dbVersion = ShoprocketSetting::getValue('version');
      if(version_compare(SHOPROCKET_VERSION_NUMBER, $dbVersion)) {
        $this->install();
      }
    }
    
    // Define debugging and testing info
    $shoprocketLogging = ShoprocketSetting::getValue('enable_logging') ? true : false;
    $sandbox = ShoprocketSetting::getValue('paypal_sandbox') ? true : false;
    define("Shoprocket_DEBUG", $shoprocketLogging);
    define("SANDBOX", $sandbox);
    
    // Handle dynamic JS requests
    // See: http://ottopress.com/2010/dont-include-wp-load-please/ for why
    add_filter('query_vars', array($this, 'addAjaxTrigger'));
    add_action('template_redirect', array($this, 'ajaxTriggerCheck'));
    
    // Scheduled events
    if(FALSE) {
      add_action('daily_subscription_reminder_emails', array('ShoprocketMembershipReminders', 'dailySubscriptionEmailReminderCheck'));
      add_action('daily_followup_emails', array('ShoprocketAdvancedNotifications', 'dailyFollowupEmailCheck'));
      add_action('daily_gravity_forms_entry_removal', array('ShoprocketGravityReader', 'dailyGravityFormsOrphanedEntryRemoval'));
    
    }
    
    // Notification shortcodes
    $sc = new ShoprocketShortcodeManager();
    add_shortcode('email_shortcodes', array($sc, 'emailShortcodes'));
        
    // add Shoprocket to the admin bar
    if(ShoprocketCommon::shoprocketUserCan('products')) {
      add_action('admin_bar_menu', array($this, 'shoprocket_admin_bar_menu'), 35);
    }
    
    if(IS_ADMIN) {
      if(ShoprocketSetting::getValue('capost_merchant_id')) {
        add_action('admin_notices', array($this, 'shoprocket_canada_post_upgrade'));
      }
      //add_action( 'admin_notices', 'shoprocket_data_collection' );

      add_action('admin_head', array( $this, 'registerBasicScripts'));
      add_action('admin_init', array($this, 'registerAdminScripts'));
      add_action('admin_init', array($this, 'registerCustomScripts'));
      add_action('admin_print_styles', array($this, 'registerAdminStyles'));
      
      add_action('admin_menu', array($this, 'buildAdminMenu'));
      // we dont use this button anymore
      //add_action('admin_init', array($this, 'addEditorButtons'));
      add_action('admin_init', array($this, 'forceDownload'));
      add_action('wp_ajax_save_settings', array('ShoprocketAjax', 'saveSettings'));
      add_action('wp_ajax_force_plugin_update', array('ShoprocketAjax', 'forcePluginUpdate'));
      add_action('wp_ajax_promotionProductSearch', array('ShoprocketAjax', 'promotionProductSearch'));
      add_action('wp_ajax_loadPromotionProducts', array('ShoprocketAjax', 'loadPromotionProducts'));
      add_action('wp_ajax_send_test_email', array('ShoprocketAjax', 'sendTestEmail'));
      add_action('wp_ajax_resend_email_from_log', array('ShoprocketAjax', 'resendEmailFromLog'));
      add_action('wp_ajax_products_table', array('ShoprocketDataTables', 'productsTable'));
      add_action('wp_ajax_fetch_products', array('ShoprocketProduct', 'Fetchproducts'));
      add_action('wp_ajax_remove_products', array('ShoprocketProduct', 'Removeproducts'));
      add_action('wp_ajax_print_view', array('ShoprocketAjax', 'ajaxReceipt'));
      add_action('wp_ajax_view_email', array('ShoprocketAjax', 'viewLoggedEmail'));
      add_action('wp_ajax_dashboard_products_table', array('ShoprocketDataTables', 'dashboardProductsTable'));
      add_action('wp_ajax_shortcode_products_table', array('ShoprocketAjax', 'shortcodeProductsTable'));
      add_action('wp_ajax_page_slurp', array('ShoprocketAjax', 'pageSlurp'));
      add_action('wp_ajax_dismiss_mijireh_notice', array('ShoprocketAjax', 'dismissMijirehNotice'));
      add_action('wp_ajax_shoprocket_page_check', array('ShoprocketAjax','checkPages'));

      // Load Dialog Box in editor
      add_action('media_buttons', array('ShoprocketDialog', 'shoprocket_dialog_box'), 11);
      add_action('admin_footer', array('ShoprocketDialog', 'add_shortcode_popup'));
      
      // Load Page Slurp Button on checkout page
      add_action('add_meta_boxes', array($this, 'addPageSlurpButtonMeta')); 
      add_action('media_buttons', array($this, 'addPageSlurpButton'), 12);
      

      add_action('save_post', array($this,'check_shoprocket_pages_on_inline_edit'));
      add_action('admin_notices',array($this,'shoprocket_page_check'));
    }
    else {
      $this->initShortcodes();
      add_action('wp_head', array('ShoprocketCommon', 'displayVersionInfo'));
  
    }
    
    // ================================================================
    // = Intercept query string shoprocket tasks                          =
    // ================================================================
     
    // Logout the logged in user
    $isLoggedIn = ShoprocketCommon::isLoggedIn();
    if(isset($_REQUEST['shoprocket-task']) && $_REQUEST['shoprocket-task'] == 'logout' && $isLoggedIn) {
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Intercepting Shoprocket Logout task");
      $url = ShoprocketProCommon::getLogoutUrl();
      ShoprocketAccount::logout($url);
    }
    
    if($_SERVER['REQUEST_METHOD'] == 'GET' &&  ShoprocketCommon::getVal('task') == 'member_download') {
      if(ShoprocketCommon::isLoggedIn()) {
        $path = $_GET['path'];
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Attempting a member download file request: $path");
        ShoprocketCommon::downloadFile($path);
      }
    }
    elseif($_SERVER['REQUEST_METHOD'] == 'GET' && ShoprocketCommon::getVal('task') == 'add-to-cart-anchor') {
      $options = null;
      if(isset($_GET['options'])) {
        $options = ShoprocketCommon::getVal('options');
      }
      $productUrl = null;
      if(isset($_GET['product_url'])){
        $productUrl = $_GET['product_url'];
      }
      ShoprocketSession::get('ShoprocketCart')->addItem(ShoprocketCommon::getVal('shoprocketItemId'), 1, $options, null, $productUrl);
      $promotion_var_name = ShoprocketSetting::getValue('promotion_get_varname') ? ShoprocketSetting::getValue('promotion_get_varname') : 'promotion';
      if(isset($_GET[$promotion_var_name])) {
        ShoprocketSession::get('ShoprocketCart')->applyPromotion(strtoupper($_GET[$promotion_var_name]), true);
      }
      wp_redirect(remove_query_arg(array('shoprocketItemId', 'product_url', 'task', $promotion_var_name), ShoprocketCommon::getCurrentPageUrl()));
      exit;
    }
    elseif($_SERVER['REQUEST_METHOD'] == 'GET' && ShoprocketCommon::getVal('task') == 'mijireh_notification') {
      require_once(SHOPROCKET_PATH . "/gateways/ShoprocketMijireh.php");
      $order_number = ShoprocketCommon::getVal('order_number');
      $mijireh = new ShoprocketMijireh();
      $mijireh->saveMijirehOrder($order_number);
    }
    elseif(isset($_GET['task']) && ShoprocketCommon::getVal('task') == 'mijireh_page_slurp') {
      $access_key = ShoprocketSetting::getValue('mijireh_access_key');
      if(isset($_POST['access_key']) && isset($_POST['page_id']) && $_POST['access_key'] == $access_key) {
        wp_update_post(array('ID' => $_POST['page_id'], 'post_status' => 'private'));
      }
    }
    else {
      $promotion_var_name = ShoprocketSetting::getValue('promotion_get_varname') ? ShoprocketSetting::getValue('promotion_get_varname') : 'promotion';
      if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET[$promotion_var_name])) {
        ShoprocketSession::get('ShoprocketCart')->applyPromotion(strtoupper($_GET[$promotion_var_name]), true);
      }
    }
    
  }
  
  public function checkIPN() {
    if(isset($_GET['listener']) && $_GET['listener'] == '2CO') {
      $sid = (isset($_REQUEST['vender_number']) && $_REQUEST['vendor_number'] != '') ? $_REQUEST['vendor_number'] : $_REQUEST['sid'];
      if(ShoprocketSetting::getValue('tco_test_mode')) {
        $order_number = 1;
      }
      else {
        $order_number = $_REQUEST['order_number'];
      }
      $order_total = $_REQUEST['total'];
      $string = ShoprocketSetting::getValue('tco_secret_word') . $sid . $order_number . $order_total;
      $key = strtoupper(md5($string));
      if($key == $_REQUEST['key']) {
        $tco = new Shoprocket2Checkout();
        $tco->saveTcoOrder();
      }
    }
  }
  
  public function shoprocket_page_check($return = false){
    
    if(ShoprocketCommon::verifyCartPages('error')){
      
      $alert_output = "<div class='alert-message alert-danger' id='shoprocket_page_errors'>
        <div class='left'>
          <h2>" . __('A problem with Shoprocket has been detected.', 'shoprocket') . "</h2>
          <p>" . __( 'The following page(s) are missing from the proper page structure. This could be because the slug was renamed or the page was moved, set to draft, private, or deleted.' , 'shoprocket' ) . "</p>
          <ul>" . ShoprocketCommon::verifyCartPages('error') . "</ul>
          <p>" . __( 'Please refer to' , 'shoprocket' ) . " <a href='http://shoprocket.com/2011/dont-rename-the-store-pages/' target='_blank'>" . __( 'this article</a> for the proper configuration of pages for Shoprocket.' , 'shoprocket' ) . " <em> " . __( 'Shoprocket will not work properly until this issue is resolved.' , 'shoprocket' ) . "</em></p>
        </div>
      </div>";  
      
    }
    else{
      $alert_output = '<div id="shoprocket_page_errors"></div>';
    }
    
    if($return){
      return $alert_output;
    }
    else{
      echo $alert_output;
    }
  }
  
  public function check_shoprocket_pages_on_inline_edit(){
    if(!empty($_POST) && isset($_POST['action']) && $_POST['action'] == 'inline-save' && isset($_POST['post_type']) && $_POST['post_type'] == 'page'){
      global $inline_save_flag;
      if($inline_save_flag == 0){
        ?><tr>
          <script>
            inline_save_callback();
          </script>
        </tr><?php 
        $inline_save_flag = 1;
      }
      
      $inline_safe_flag = 1;
      
    }
    
  }
  
  public function shoprocket_admin_bar_menu() {
    
	  global $wp_admin_bar;
    if (!is_admin_bar_showing() ){
	    return;
		}
	  
	  $wp_admin_bar->add_menu(
      array( 'id' => 'shoprocket',
        'title' => false,
        'href' => false,
				'meta' => array("html"=>'<span class="shoprocketAdminBarIcon"></span>')
      )
    );
		
		$shoprocketPages = array(
			"Products" => array("role" => 'products', "slug" => '-products'),
			"Promotions" => array("role" => 'promotions', "slug" => '-promotions'),
			"Settings" => array("role" => 'settings', "slug" => '-settings')
		);
		//ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] pages array: " . print_r($shoprocketPages, true));
		foreach($shoprocketPages as $page=>$meta){
			if(ShoprocketCommon::shoprocketUserCan($meta['role'])){
				$wp_admin_bar->add_menu( array(
					'id' => 'shoprocket-adminbar-'.$meta['slug'],
			    'parent' => 'shoprocket',
			    'title' => __($page),
			    'href' => get_bloginfo('wpurl') . '/wp-admin/admin.php?page=shoprocket' . strtolower($meta['slug']),
			    'meta' => false) 
				);
			}
		}
		
		$wp_admin_bar->add_menu( array(
			'id' => 'shoprocket-pages',
	    'parent' => 'shoprocket',
	    'title' => __("Store Pages"),
	    'href' => false,
	    'meta' => false) 
		);
		
		$storePages = array(
			"Store" => get_page_by_path('store'),
			"Cart" => get_page_by_path('store/cart'),
			"Checkout" => get_page_by_path('store/checkout'),
			"Receipt" => get_page_by_path('store/receipt')
		);
		
		foreach($storePages as $pageName=>$cartPage){
		  if($cartPage){
		    $wp_admin_bar->add_menu( array(
  				'id' => 'shoprocket-storepage-' . strtolower($pageName),
  		    'parent' => 'shoprocket-pages',
  		    'title' => __($pageName),
  		    'href' => get_bloginfo('wpurl') . '/wp-admin/post.php?post=' . $cartPage->ID . '&action=edit',
  		    'meta' => false) 
  			);
		  }			
		}
	}
  
  
  public function shoprocket_data_collection(){
       global $current_screen;
       
       echo '<div class="updated">';
       echo '<script type="text/javascript">
        (function($){
          $(document).ready(function(){
            $("#shoprocketSendSurvey").click(function(){
              $.get("http://shoprocket.com/survey/",function(data){
                alert(data)
              })
            })
          })
        })(jQuery);
       </script>  ';
       echo '<H3>Shoprocket Usage Survey</h3>';
       echo '<p>To improve our customer experience, Shoprocket would love for you to participate in an anonymous usage survey. This data will be sent one time, and does not contain any personal or identification information.</p>';
       echo '<p>Here\'s what is being sent:<br><br>';
       echo ShoprocketCommon::showReportData();
       echo '<p><a id="shoprocketSendSurvey" class="button" href="#">Send</a> &nbsp;&nbsp;&nbsp; <a class="button" href="#">No thanks</a></p>';
       echo '</div>';
  }
    
  
  public function filter_private_menu_items($items) {
    if(ShoprocketCommon::isLoggedIn()) {
      // User is logged in so hide the guest only pages
      $page_ids = ShoprocketAccessManager::getGuestOnlyPageIds();
    }
    else {
      // User is not logged in so hide the private pages
      $page_ids = ShoprocketAccessManager::getPrivatePageIds();
    }
    foreach($items as $key => $item) {
      if(in_array($item->object_id, $page_ids)) {
        unset($items[$key]);
      }
    }
    
    return $items;
  }
  
  public static function enqueueScripts() {
    $url = SHOPROCKET_URL . '/shoprocket.css';
    wp_enqueue_style('shoprocket-css', $url, null, SHOPROCKET_VERSION_NUMBER, 'all');

    if($css = ShoprocketSetting::getValue('styles_url')) {
      wp_enqueue_style('shoprocket-custom-css', $css, null, SHOPROCKET_VERSION_NUMBER, 'all');
    }
    // Include the shoprocket javascript library
    $path = SHOPROCKET_URL . '/js/shoprocket-library.js';
    wp_enqueue_script('shoprocket-library', $path, array('jquery'), SHOPROCKET_VERSION_NUMBER, true);
  }
  
  public function loadCoreModels() {
    require_once(SHOPROCKET_PATH . "/models/ShoprocketBaseModelAbstract.php");
    require_once(SHOPROCKET_PATH . "/models/ShoprocketModelAbstract.php");
    require_once(SHOPROCKET_PATH . "/models/ShoprocketSession.php");
    require_once(SHOPROCKET_PATH . "/models/ShoprocketSessionDb.php");
    require_once(SHOPROCKET_PATH . "/models/ShoprocketSessionNative.php");
    require_once(SHOPROCKET_PATH . "/models/ShoprocketSetting.php");
    require_once(SHOPROCKET_PATH . "/models/ShoprocketAdmin.php");
    require_once(SHOPROCKET_PATH . "/models/ShoprocketAjax.php");
    require_once(SHOPROCKET_PATH . "/models/ShoprocketLog.php");
    require_once(SHOPROCKET_PATH . "/models/ShoprocketProduct.php");
    require_once(SHOPROCKET_PATH . "/models/ShoprocketCart.php");
    require_once(SHOPROCKET_PATH . "/models/ShoprocketException.php");
    require_once(SHOPROCKET_PATH . "/models/ShoprocketPromotion.php");
    require_once(SHOPROCKET_PATH . "/models/ShoprocketShortcodeManager.php");
    require_once(SHOPROCKET_PATH . "/models/ShoprocketButtonManager.php");
    require_once(SHOPROCKET_PATH . "/models/ShoprocketDataTables.php");
    require_once(SHOPROCKET_PATH . "/models/ShoprocketDialog.php");
    require_once(SHOPROCKET_PATH . "/models/ShoprocketUpdater.php");
    require_once(SHOPROCKET_PATH . "/models/ShoprocketUpdater.php");

  }
  
  public function initCurrencySymbols() {
    $cs = ShoprocketSetting::getValue('Shoprocket_CURRENCY_SYMBOL');
    $cs = $cs ? $cs : '$';
    $cst = ShoprocketSetting::getValue('Shoprocket_CURRENCY_SYMBOL_text');
    $cst = $cst ? $cst : '$';
    $ccd = ShoprocketSetting::getValue('currency_code');
    $ccd = $ccd ? $ccd : 'USD';
    define("Shoprocket_CURRENCY_SYMBOL", $cs);
    define("Shoprocket_CURRENCY_SYMBOL_TEXT", $cst);
    define("CURRENCY_CODE", $ccd);
  }
  
  public function setDefaultPageRoles() {
    $defaultPageRoles = array(
      'products' => 'manage_options',
      'Sync' => 'manage_options',
      'promotions' => 'manage_options',
      'shipping' => 'manage_options',
      'settings' => 'manage_options',
      'reports' => 'manage_options',
      'accounts' => 'manage_options'
    );
    // Set default admin page roles if there isn't any
    $pageRoles = ShoprocketSetting::getValue('admin_page_roles');
    if(empty($pageRoles)){
      ShoprocketSetting::setValue('admin_page_roles',serialize($defaultPageRoles));
    }
    // Ensure that all admin page roles have been set.
    else {
      $updateRoles = false;
      $pageRoles = unserialize($pageRoles);
      foreach($defaultPageRoles as $key => $value) {
        if(!array_key_exists($key, $pageRoles)) {
          $pageRoles[$key] = $value;
          $updateRoles = true;
        }
      }
      if($updateRoles) {
        ShoprocketSetting::setValue('admin_page_roles',serialize($pageRoles));
      }      
    }
    return unserialize(ShoprocketSetting::getValue('admin_page_roles'));
  }
  
  public function registerBasicScripts() {
    ?><script type="text/javascript">var wpurl = '<?php echo esc_js( home_url('/') ); ?>';</script><?php
    $dashboardCss = SHOPROCKET_URL . '/admin/dashboard.css';
    wp_enqueue_style('dashboard-css', $dashboardCss, null, SHOPROCKET_VERSION_NUMBER, 'all');
  }
  
  public function registerAdminScripts() {
    $path = SHOPROCKET_URL . '/js/jquery.dataTables.min.js';
    wp_enqueue_script('jquery-dataTables', $path, null, SHOPROCKET_VERSION_NUMBER, true);    
    $path = SHOPROCKET_URL . '/js/page-slurp.js';
    wp_enqueue_script('page-slurp', $path, null, SHOPROCKET_VERSION_NUMBER, true);
    wp_enqueue_script('pusher', 'https://d3dy5gmtp8yhk7.cloudfront.net/1.11/pusher.min.js', null, SHOPROCKET_VERSION_NUMBER, true);
  }
  
  public function registerCustomScripts() {
    if(strpos($_SERVER['QUERY_STRING'], 'page=shoprocket') !== false) {
      $path = SHOPROCKET_URL . '/js/ajax-setting-form.js';
      wp_enqueue_script('ajax-setting-form', $path, null, SHOPROCKET_VERSION_NUMBER);

      // Include jquery-multiselect, jquery-datepicker, jquery-timepicker-addon and jquery-ui
      wp_enqueue_script('jquery');
      wp_enqueue_script('jquery-ui-core');
      wp_enqueue_script('jquery-ui-sortable');
      wp_enqueue_script('jquery-ui-datepicker');
      wp_enqueue_script('jquery-ui-dialog');
      wp_enqueue_script('jquery-ui-slider');
      $path = SHOPROCKET_URL . '/js/ui.multiselect.js';
      wp_enqueue_script('jquery-multiselect', $path, array('jquery-ui-sortable'), SHOPROCKET_VERSION_NUMBER, true);
      
      $path = SHOPROCKET_URL . '/js/ui.timepicker.addon.js';
      wp_enqueue_script('jquery-timepicker-addon', $path, array('jquery-ui-datepicker', 'jquery-ui-slider'), SHOPROCKET_VERSION_NUMBER, true);
      $path = SHOPROCKET_URL . '/js/jquery.tokeninput.js';
      wp_enqueue_script('jquery-tokeninput', $path, null, SHOPROCKET_VERSION_NUMBER, true);
      $path = SHOPROCKET_URL . '/js/shoprocket-codemirror.js';
      wp_enqueue_script('shoprocket-codemirror', $path, null, SHOPROCKET_VERSION_NUMBER, false);
      $path = SHOPROCKET_URL . '/js/notifications.js';
      wp_enqueue_script('notifications-js', $path, null, SHOPROCKET_VERSION_NUMBER, false);

 
      // Include the jquery table quicksearch library
      $path = SHOPROCKET_URL . '/js/jquery.quicksearch.js';
      wp_enqueue_script('quicksearch', $path, array('jquery'));
      
    }
  }
  
  public function registerAdminStyles() {
    $screen = get_current_screen();
    if(strpos($_SERVER['QUERY_STRING'], 'page=shoprocket') !== false || ShoprocketCommon::isSlurpPage() || (is_object($screen) && $screen->id === 'plugins')) {
      if(version_compare(get_bloginfo('version'), '3.3', '<')) {
        $widgetCss = WPURL . '/wp-admin/css/widgets.css';
        wp_enqueue_style('widget-css', $widgetCss, null, SHOPROCKET_VERSION_NUMBER, 'all');
      }
      
    	$adminCss = SHOPROCKET_URL . '/admin/admin-styles.css';
    	wp_enqueue_style('admin-css', $adminCss, null, SHOPROCKET_VERSION_NUMBER, 'all');

      $uiCss = SHOPROCKET_URL . '/admin/jquery-ui-1.7.1.custom.css';
      wp_enqueue_style('ui-css', $uiCss, null, SHOPROCKET_VERSION_NUMBER, 'all');
      
      $codemirror = SHOPROCKET_URL . '/admin/codemirror.css';
      wp_enqueue_style('codemirror-css', $codemirror, null, SHOPROCKET_VERSION_NUMBER, 'all');

    }
  }
  
  public function dontCacheMeBro() {
    if(!IS_ADMIN) {
      global $post;
      $sendHeaders = false;
      if($disableCaching = ShoprocketSetting::getValue('disable_caching')) {
        if($disableCaching === '1') {
          $cartPage = get_page_by_path('store/cart');
          $checkoutPage = get_page_by_path('store/checkout');
          $cartPages = array($checkoutPage->ID, $cartPage->ID);
          if( isset( $post->ID ) && in_array($post->ID, $cartPages) ) {
            $sendHeaders = true;
            //ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] set to send no cache headers for cart pages");
          }
          else {
            if(!isset($post->ID)) {
              ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] The POST ID is not set");
            }
            ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not a cart page! Therefore need to set the headers to disable cache");
          }
        }
        elseif($disableCaching === '2') {
          $sendHeaders = true;
        }
      }
      
      // ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Disable caching is: $disableCaching");
      
      if($sendHeaders) {
        // ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Sending no cache headers");
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', FALSE);
        header('Pragma: no-cache');
      }
      
    }
  }

  /**
   * Put Shoprocket in the admin menu
   */
  public function buildAdminMenu() {
    $icon = SHOPROCKET_URL . '/images/shoprocket.jpg';
    
    add_menu_page('ShopRocket', 'ShopRocket', ShoprocketCommon::getPageRoles('products'), 'shoprocket-products', null, $icon);
    add_submenu_page('shoprocket-products', __('Products', 'shoprocket'), __('Products', 'shoprocket'), ShoprocketCommon::getPageRoles('products'), 'shoprocket-products', array('ShoprocketAdmin', 'productsPage'));
    add_submenu_page('shoprocket-products', __('Sync', 'shoprocket'), __('Sync', 'shoprocket'), ShoprocketCommon::getPageRoles('Sync'), 'shoprocket-Sync', array('ShoprocketAdmin', 'SyncPage'));
    add_submenu_page('shoprocket-products', __('Promotions', 'shoprocket'), __('Promotions', 'shoprocket'), ShoprocketCommon::getPageRoles('promotions'), 'shoprocket-promotions', array('ShoprocketAdmin', 'promotionsPage'));
    add_submenu_page('shoprocket-products', __('Settings', 'shoprocket'), __('Settings', 'shoprocket'), ShoprocketCommon::getPageRoles('settings'), 'shoprocket-settings', array('ShoprocketAdmin', 'settingsPage'));
    add_submenu_page('shoprocket-products', __('Reports', 'shoprocket'), __('Reports', 'shoprocket'), ShoprocketCommon::getPageRoles('reports'), 'shoprocket-reports', array('ShoprocketAdmin', 'reportsPage'));
    add_submenu_page('shoprocket-products', __('Accounts', 'shoprocket'), __('Accounts', 'shoprocket'), ShoprocketCommon::getPageRoles('accounts'), 'shoprocket-accounts', array('ShoprocketAdmin', 'accountsPage'));
  }
  

  /**
   * Check Sync levels when accessing the checkout page.
   * If Sync is insufficient place a warning message in ShoprocketSession::get('ShoprocketSyncWarning')
   */
  public function checkSyncOnCheckout() {
    if($_SERVER['REQUEST_METHOD'] == 'GET') {
      global $post;
      $checkoutPage = get_page_by_path('store/checkout');
      if(is_object($checkoutPage) && isset( $post->ID ) && $post->ID == $checkoutPage->ID) {
        $SyncMessage = ShoprocketSession::get('ShoprocketCart')->checkCartSync();
        if(!empty($SyncMessage)) { ShoprocketSession::set('ShoprocketSyncWarning', $SyncMessage); }
      }
    }
  }
  
  public function checkShippingMethodOnCheckout() {
    if($_SERVER['REQUEST_METHOD'] == 'GET') {
      global $post;
      $checkoutPage = get_page_by_path('store/checkout');
      
      if(!ShoprocketSetting::getValue('use_live_rates')) {
        ShoprocketSession::drop('ShoprocketLiveRates');
      }
      
      if(is_object($checkoutPage) && isset( $post->ID ) && $post->ID == $checkoutPage->ID) {
        if(ShoprocketSession::get('ShoprocketLiveRates') && get_class(ShoprocketSession::get('ShoprocketLiveRates')) == 'ShoprocketLiveRates') {
          if(!ShoprocketSession::get('ShoprocketLiveRates')->hasValidShippingService()) {
            ShoprocketSession::set('ShoprocketShippingWarning', true);
            $viewCartPage = get_page_by_path('store/cart');
            $viewCartLink = get_permalink($viewCartPage->ID);
            wp_redirect($viewCartLink);
            exit;
          }
        }
        else {
          if(ShoprocketSetting::getValue('require_shipping_validation')) {
            if(ShoprocketSession::get('ShoprocketCart')->requireShipping()) {
              $shippingMethods = ShoprocketSession::get('ShoprocketCart')->getShippingMethods();
              $selectedShippingMethod = ShoprocketSession::get('ShoprocketCart')->getShippingMethodId();
              if($selectedShippingMethod == 'select') {
                ShoprocketSession::set('ShoprocketShippingWarning', true);
                $viewCartPage = get_page_by_path('store/cart');
                $viewCartLink = get_permalink($viewCartPage->ID);
                wp_redirect($viewCartLink);
                exit;
              }
              else {
                $method = new ShoprocketShippingMethod(ShoprocketSession::get('ShoprocketCart')->getShippingMethodId());
                if(is_array($accepted_countries = unserialize($method->countries))) {
                  $selectedCountry = ShoprocketSession::get('ShoprocketShippingCountryCode');
                  ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] accepted countries: " . print_r($accepted_countries, true));
                  if(!array_key_exists($selectedCountry, $accepted_countries)) {
                    ShoprocketSession::set('ShoprocketShippingWarning', true);
                    $viewCartPage = get_page_by_path('store/cart');
                    $viewCartLink = get_permalink($viewCartPage->ID);
                    wp_redirect($viewCartLink);
                    exit;
                  }
                }
              }
            }
          }
        }
      }
    }
  }
  
  public function checkTermsOnCheckout() {
    global $post;
    $checkoutPage = get_page_by_path('store/checkout');
    $cartPage = get_page_by_path('store/cart');
    
    // ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] What is the post? " . print_r($post, 1));
    $sendBack = false;
    if(isset($post) && is_object($post) && is_object($cartPage) && is_object($checkoutPage)) {
      
      if($post->ID == $checkoutPage->ID || $post->ID == $cartPage->ID) {
        if(ShoprocketSetting::getValue('require_terms') == 1) {
          if($post->ID == $cartPage->ID && isset($_POST['terms_acceptance']) && $_POST['terms_acceptance'] == "I_Accept"){
            ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Terms are accepted, forwarding to checkout");
            ShoprocketSession::set("terms_acceptance","accepted",true);
            $link = ShoprocketSetting::getValue('auth_force_ssl') ? str_replace('http://', 'https://', get_permalink($checkoutPage->ID)) : get_permalink($checkoutPage->ID);
            $sendBack = true;
          }
          elseif($post->ID == $checkoutPage->ID && (!ShoprocketSession::get('terms_acceptance') || ShoprocketSession::get('terms_acceptance') != "accepted")) {
            ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Terms not accepted, send back to cart");
            $link = get_permalink($cartPage->ID);
            $sendBack = true;
          }
          if($sendBack) {
            wp_redirect($link);
            exit;
          }
        }
      }
      
    }
  }
  
  public function checkCustomFieldsOnCheckout() {
    global $post;
    $checkoutPage = get_page_by_path('store/checkout');
    $cartPage = get_page_by_path('store/cart');
    
    $sendBack = false;
    if(isset($post) && is_object($post) && is_object($cartPage) && is_object($checkoutPage)) {
      
      if($post->ID == $checkoutPage->ID || $post->ID == $cartPage->ID) {
        $items = ShoprocketSession::get('ShoprocketCart')->getItems();
        $product = new ShoprocketProduct();
        $requiredProducts = array();
        foreach($items as $itemIndex => $item) {
          $product->load($item->getProductId());
          if($post->ID == $checkoutPage->ID && $product->custom_required && !$item->getCustomFieldInfo()) {
            ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] required custom field is empty");
            $requiredProducts[] = $product->name;
            $link = get_permalink($cartPage->ID);
            $sendBack = true;
          }
        }
        if(!empty($requiredProducts)) {
          ShoprocketSession::set('ShoprocketCustomFieldWarning', $requiredProducts);
        }
        if($sendBack) {
          wp_redirect($link);
          exit;
        }
      }
      
    }
    
  }
  
  public function checkMinAmountOnCheckout() {
    global $post;
    $checkoutPage = get_page_by_path('store/checkout');
    $cartPage = get_page_by_path('store/cart');
    $sendBack = false;
    if(isset($post->ID)) {
      if(is_object($checkoutPage) && is_object($cartPage)) {
        if($post->ID == $checkoutPage->ID || $post->ID == $cartPage->ID) {
          if(ShoprocketSetting::getValue('minimum_cart_amount') == 1) {
            $minAmount = number_format(ShoprocketSetting::getValue('minimum_amount'), 2, '.', '');
            $subTotal = number_format(ShoprocketSession::get('ShoprocketCart')->getSubTotal(), 2, '.', '');
            if($post->ID == $checkoutPage->ID && $minAmount > $subTotal) {
              ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Minimum Cart amount is not yet met, send back to cart");
              $link = get_permalink($cartPage->ID);
              $sendBack = true;
            }
            else {
              $sendBack = false;
            }
            if($sendBack) {
              wp_redirect($link);
              exit;
            }
          }
        }
      }
    }
  }
  
  public function checkZipOnCheckout() {
    if(FALSE && $_SERVER['REQUEST_METHOD'] == 'GET') {
      if(ShoprocketSetting::getValue('use_live_rates') && ShoprocketSession::get('ShoprocketCart')->requireShipping()) {
        global $post;
        $checkoutPage = get_page_by_path('store/checkout');
        if( isset( $post->ID ) && $post->ID == $checkoutPage->ID) {
          $cartPage = get_page_by_path('store/cart');
          $link = get_permalink($cartPage->ID);
          $sendBack = false;
          
          if(!ShoprocketSession::get('shoprocket_shipping_zip')) {
            ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Live rate warning: No shipping zip in session");
            ShoprocketSession::set('ShoprocketZipWarning', true);
            $sendBack = true;
          }
          elseif(!ShoprocketSession::get('shoprocket_shipping_country_code')) {
            ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Live rate warning: No shipping country code in session");
            ShoprocketSession::set('ShoprocketShippingWarning', true);
            $sendBack = true;
          }
          
          if($sendBack) {
            wp_redirect($link);
            exit;
          }
          
        } // End if checkout page
      } // End if using live rates
    } // End if GET
  }
  
  /**
   *  Add Shoprocket to the TinyMCE editor
   */
  public function addEditorButtons() {
    // Don't bother doing this stuff if the current user lacks permissions
    if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
    return;

    // Add only in Rich Editor mode
    if ( get_user_option('rich_editing') == 'true') {
      add_filter('mce_external_plugins', array('Shoprocket', 'addTinymcePlugin'));
      add_filter('mce_buttons', array('Shoprocket','registerEditorButton'));
    }
  }

  public function registerEditorButton($buttons) {
    array_push($buttons, "|", "shoprocket");
    return $buttons;
  }

  public function addTinymcePlugin($plugin_array) {
    $plugin_array['shoprocket'] = SHOPROCKET_URL . '/js/editor_plugin_src.js';
    return $plugin_array;
  }
  
  /**
   * Load the cart from the session or put a new cart in the session
   */
  public function initCart() {

    if(!ShoprocketSession::get('ShoprocketCart')) {
      ShoprocketSession::set('ShoprocketCart', new ShoprocketCart());
      // ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Creating a new ShoprocketCart OBJECT for the database session.");
    }

    if(isset($_POST['task'])) {
      if($_POST['task'] == 'addToCart') {
        ShoprocketSession::get('ShoprocketCart')->addToCart();
      }
      elseif($_POST['task'] == 'updateCart') {
        ShoprocketSession::get('ShoprocketCart')->updateCart();
      }
    }
    elseif(isset($_GET['task'])) {
      if($_GET['task']=='removeItem') {
        $itemIndex = ShoprocketCommon::getVal('itemIndex');
        ShoprocketSession::get('ShoprocketCart')->removeItem($itemIndex);
      }
    }
    elseif(isset($_POST['shoprocket-action'])) {
      $task = ShoprocketCommon::postVal('shoprocket-action');
      if($task == 'authcheckout') {
        $SyncMessage = ShoprocketSession::get('ShoprocketCart')->checkCartSync();
        if(!empty($SyncMessage)) { ShoprocketSession::set('ShoprocketSyncWarning', $SyncMessage); }
      }
    }
    
  }
  
  public function initShortcodes() {
    $sc = new ShoprocketShortcodeManager();
    add_shortcode('account_login',                array($sc, 'accountLogin'));
    add_shortcode('account_logout',               array($sc, 'accountLogout'));
    add_shortcode('account_logout_link',          array($sc, 'accountLogoutLink'));
    add_shortcode('account_info',                 array($sc, 'accountInfo'));
    add_shortcode('account_create',               array($sc, 'accountCreate'));
    add_shortcode('account_details',              array($sc, 'accountDetails'));
    add_shortcode('add_to_cart',                  array($sc, 'showCartButton'));
    add_shortcode('add_to_cart_anchor',           array($sc, 'showCartAnchor'));
    add_shortcode('cart',                         array($sc, 'showCart'));
    add_shortcode('shoprocket_download',          array($sc, 'downloadFile'));
    add_shortcode('shoprocket_affiliate',         array($sc, 'shoprocketAffiliate'));
    add_shortcode('cancel_paypal_subscription',   array($sc, 'cancelPayPalSubscription'));
    add_shortcode('checkout_authorizenet',        array($sc, 'authCheckout'));
    add_shortcode('checkout_mijireh',             array($sc, 'mijirehCheckout'));
    add_shortcode('checkout_manual',              array($sc, 'manualCheckout'));
    add_shortcode('checkout_payleap',             array($sc, 'payLeapCheckout'));
    add_shortcode('checkout_paypal',              array($sc, 'paypalCheckout'));
    add_shortcode('checkout_paypal_express',      array($sc, 'payPalExpressCheckout'));
    add_shortcode('checkout_paypal_pro',          array($sc, 'payPalProCheckout'));
    add_shortcode('checkout_2checkout',           array($sc, 'twoCheckout'));
    add_shortcode('clear_cart',                   array($sc, 'clearCart'));
    add_shortcode('hide_from',                    array($sc, 'hideFrom'));
    add_shortcode('post_sale',                    array($sc, 'postSale'));
    add_shortcode('shopping_cart',                array($sc, 'shoppingCart'));
    add_shortcode('show_to',                      array($sc, 'showTo'));
    add_shortcode('subscription_name',            array($sc, 'currentSubscriptionPlanName'));
    add_shortcode('subscription_feature_level',   array($sc, 'currentSubscriptionFeatureLevel'));
    add_shortcode('zendesk_login',                array($sc, 'zendeskRemoteLogin'));
    add_shortcode('terms_of_service',             array($sc, 'termsOfService'));
    add_shortcode('account_expiration',           array($sc, 'accountExpiration'));
    
    if(FALSE) {
      add_shortcode('email_opt_out',              array($sc, 'emailOptOut'));
    }
    
    // System shortcodes
    add_shortcode('shoprocket_tests',             array($sc, 'shoprocketTests'));
    add_shortcode('express',                      array($sc, 'payPalExpress'));
    add_shortcode('ipn',                          array($sc, 'processIPN'));
    add_shortcode('receipt',                      array($sc, 'showReceipt'));
    add_shortcode('spreedly_listener',            array($sc, 'spreedlyListener'));
    add_shortcode('checkout_stripe',              array($sc, 'stripeCheckout'));
    add_shortcode('checkout_eway',                array($sc, 'ewayCheckout'));
    add_shortcode('checkout_mwarrior',            array($sc, 'mwarriorCheckout'));

    
    // Enable Gravity Forms hooks if Gravity Forms is available
    if(FALSE && class_exists('RGForms')) {
      add_action("gform_post_submission", array($sc, 'gravityFormToCart'), 100, 1);
    }
    
  }
  
  /**
   * Adds a query var trigger for the dynamic JS dialog
   */
  public function addAjaxTrigger($vars) {
    $vars[] = 'shoprocketAjaxCartRequests';
    return $vars;
  }

  /**
   * Handles the query var trigger for the dyamic JS dialog
   */
  public function ajaxTriggerCheck() {
    if ( intval( get_query_var( 'shoprocketAjaxCartRequests' ) ) == 1 ) {
      //ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] CHECKED Sync");
      ShoprocketAjax::checkSyncOnAddToCart();
      exit;
    }
    if ( intval( get_query_var( 'shoprocketAjaxCartRequests' ) ) == 2 ) {
      //ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] AJAX ADD TO CART");
      ShoprocketAjax::ajaxAddToCart();
      exit;
    }
    if ( intval( get_query_var( 'shoprocketAjaxCartRequests' ) ) == 3 ) {
      //ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] UPDATE CART WIDGETS WITH AJAX");
      ShoprocketAjax::ajaxCartElements();
      exit;
    }
    
    if ( intval( get_query_var( 'shoprocketAjaxCartRequests' ) ) == 4 ) {
      //ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] CONFIRM ORDER VERIFICATION");
      ShoprocketAjax::ajaxTaxUpdate();
      exit;
    }
    
    if ( intval( get_query_var( 'shoprocketAjaxCartRequests' ) ) == 5 ) {
      //ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] CONFIRM ORDER VERIFICATION");
      ShoprocketAjax::ajaxOrderLookUp();
      exit;
    }
  }

  /**
   * Register Shoprocket cart sidebar widget
   */
  public function registerCartWidget() {
    register_widget('ShoprocketCartWidget');
  }
  
  public function addPageSlurpButtonMeta() { 
    global $post;
    if(ShoprocketCommon::isSlurpPage()) {
      add_meta_box(  
          'slurp_meta_box', // $id  
          'Mijireh Page Slurp', // $title  
          array($this, 'drawPageSlurpMetaBox'), // $callback  
          'page', // $page  
          'normal', // $context  
          'high'); // $priority  
        }
  }
  
  public function drawPageSlurpMetaBox($post) {
    echo "<div id='mijireh_notice' class='mijireh-info alert-message info' data-alert='alert'>";
    echo  "<div class='mijireh-logo'><embdded src=\"http://www.youtube.com/watch?v=NFwZavygvcI\" type=\"video/mp4\"></div>";
    echo  "<div class='mijireh-blurb'>";
    echo    "<h2>Slurp your custom checkout page!</h2>";
    echo    "<p>Get the page designed just how you want and when you're ready, click the button below and we'll slurp it right up. Need help? <a href='http://shoprocket.com/2012/mijireh-checkout-with-shoprocket/'>Here are some tips</a> to having a great checkout page.</p>";
    echo    "<div id='slurp_progress' class='meter progress progress-info progress-striped active' style='display: none;'><div id='slurp_progress_bar' class='bar' style='width: 20%;'>Slurping...</div></div>";
    echo    "<p class='aligncenter'><a href='#' id='page_slurp' rel=". $post->ID ." class='button-primary'>Slurp This Page!</a></p>";
    echo    '<p class="aligncenter"><a class="nobold" href="' . MIJIREH_CHECKOUT . '/checkout/' . ShoprocketSetting::getValue('mijireh_access_key') . '" id="view_slurp" target="_new">Preview Checkout Page</a></p>';
    echo  "</div>";
    echo  "</div>";
  }
  
  public function addFeatureLevelMetaBox() {
    if(FALSE) {
      add_meta_box('shoprocket_feature_level_meta', __('Feature Levels', 'shoprocket'), array($this, 'drawFeatureLevelMetaBox'), null, 'side', 'low');
      //add_meta_box('shoprocket_feature_level_meta', __('Feature Levels', 'shoprocket'), array($this, 'drawFeatureLevelMetaBox'), 'page', 'side', 'low');
    }
  }  
  
  public function drawFeatureLevelMetaBox($post) {
    if(FALSE) {
      $plans = array();
      $featureLevels = array();
      $data = array();
      
      // Load feature levels defined in Spreedly if available
      if(class_exists('SpreedlySubscription')) {
        $sub = new SpreedlySubscription();
        $subs = $sub->getSubscriptions();
        foreach($subs as $s) {
          // $plans[] = array('feature_level' => (string)$s->featureLevel, 'name' => (string)$s->name);
          $plans[(string)$s->name] = (string)$s->featureLevel;
          $featureLevels[] = (string)$s->featureLevel;
        }
      }

      // Load feature levels defined in PayPal subscriptions
      $sub = new ShoprocketPayPalSubscription();
      $subs = $sub->getSubscriptionPlans();
      foreach($subs as $s) {
        $plans[$s->name] = $s->featureLevel;
        $featureLevels[] = $s->featureLevel;
      }
      
      // Load feature levels defined in Membership products
      foreach(ShoprocketProduct::getMembershipProducts() as $membership) {
        $plans[$membership->name] = $membership->featureLevel;
        $featureLevels[] = $membership->featureLevel;
      }

      // Put unique feature levels in alphabetical order
      if(count($featureLevels)) {
        $featureLevels = array_unique($featureLevels);
        sort($featureLevels);  

        $savedPlanCsv = get_post_meta($post->ID, '_shoprocket_subscription', true);
        $savedFeatureLevels = empty($savedPlanCsv) ? array() : explode(',', $savedPlanCsv);
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Shoprocket Saved Plans: $savedPlanCsv -- " . print_r($savedFeatureLevels, true));
        $data = array('featureLevels' => $featureLevels, 'plans' => $plans, 'saved_feature_levels' => $savedFeatureLevels);
      }
      $box = ShoprocketCommon::getView('pro/views/feature-level-meta-box.php', $data);
      echo $box;
    }
  }
  
  /**
   * Convert selected plan ids into a CSV string.
   * If no plans are selected, the meta key is deleted for the post.
   */
  public function saveFeatureLevelMetaBoxData($postId) {
    $nonce = isset($_REQUEST['shoprocket_spreedly_meta_box_nonce']) ? $_REQUEST['shoprocket_spreedly_meta_box_nonce'] : '';
    if(wp_verify_nonce($nonce, 'spreedly_meta_box')) {
      $featureLevels = null;
      if(isset($_REQUEST['feature_levels']) && is_array($_REQUEST['feature_levels'])) {
        $featureLevels = implode(',', $_REQUEST['feature_levels']);
      }
      
      if(!empty($featureLevels)) {
        add_post_meta($postId, '_shoprocket_subscription', $featureLevels, true) or update_post_meta($postId, '_shoprocket_subscription', $featureLevels);
      }
      else {
        delete_post_meta($postId, '_shoprocket_subscription');
      }
    }
  }
  
  public function hideStorePages($excludes) {
    
    if(ShoprocketSetting::getValue('hide_system_pages') == 1) {
      $store = get_page_by_path('store');
      $excludes[] = $store->ID;

      $cart = get_page_by_path('store/cart');
      $excludes[] = $cart->ID;

      $checkout = get_page_by_path('store/checkout');
      $excludes[] = $checkout->ID;
    }

    $express = get_page_by_path('store/express');
    $excludes[] = $express->ID;

    $ipn = get_page_by_path('store/ipn');
    $excludes[] = $ipn->ID;

    $receipt = get_page_by_path('store/receipt');
    $excludes[] = $receipt->ID;
    
    $spreedly = get_page_by_path('store/spreedly');
    if ( isset( $spreedly->ID ) )
			$excludes[] = $spreedly->ID;
    
    if(is_array(get_option('exclude_pages'))){
  		$excludes = array_merge(get_option('exclude_pages'), $excludes );
  	}
  	sort($excludes);
    
  	return $excludes;
  }
  
  public function protectSubscriptionPages() {
    global $wp_query;
    
    // Keep visitors who are not logged in from seeing private pages
    if(!isset($wp_query->tax_query)) {
      $pid = isset( $wp_query->post->ID ) ? $wp_query->post->ID : NULL;
      ShoprocketAccessManager::verifyPageAccessRights($pid);
      
      // block subscription pages from non-subscribers
      $accountId = ShoprocketCommon::isLoggedIn() ? ShoprocketSession::get('ShoprocketAccountId') : 0;
      $account = new ShoprocketAccount($accountId);

      // Get a list of the required subscription ids
      $requiredFeatureLevels = ShoprocketAccessManager::getRequiredFeatureLevelsForPage($pid);
      if(count($requiredFeatureLevels)) {
        // Check to see if the logged in user has one of the required subscriptions
        ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] protectSubscriptionPages: Page access looking for " . $account->getFeatureLevel() . " in: " . print_r($requiredFeatureLevels, true));
        if(!in_array($account->getFeatureLevel(), $requiredFeatureLevels) || !$account->isActive()) {
          ShoprocketSession::set('ShoprocketAccessDeniedRedirect', ShoprocketCommon::getCurrentPageUrl());
          wp_redirect(ShoprocketAccessManager::getDeniedLink());
          exit;
        }
      }
    }
    else {
      $exclude = false;
      $meta_query = array();
      //echo nl2br(print_r($wp_query->posts, true));
      foreach($wp_query->posts as $index => $p) {
        $pid = isset( $p->ID ) ? $p->ID : NULL;
        // block subscription pages from non-subscribers
        $accountId = ShoprocketCommon::isLoggedIn() ? ShoprocketSession::get('ShoprocketAccountId') : 0;
        $account = new ShoprocketAccount($accountId);

        // Get a list of the required subscription ids
        $requiredFeatureLevels = ShoprocketAccessManager::getRequiredFeatureLevelsForPage($pid);
        if(count($requiredFeatureLevels)) {
          // Check to see if the logged in user has one of the required subscriptions
          ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] protectSubscriptionPages: Page access looking for " . $account->getFeatureLevel() . " in: " . print_r($requiredFeatureLevels, true));
          if(!in_array($account->getFeatureLevel(), $requiredFeatureLevels) || !$account->isActive()) {
            $exclude = false;
            if(!ShoprocketSetting::getValue('remove_posts_from_taxonomy')) {

              // Set message for when visitor is not logged in
              if(!$message = ShoprocketSetting::getValue('post_not_logged_in')) {
                $message = __("You must be logged in to view this","shoprocket") . " " . $p->post_type . ".";
              }

              if(ShoprocketCommon::isLoggedIn()) {

                // Set message for insuficient access rights
                if(!$message = ShoprocketSetting::getValue('post_access_denied')) {
                  $message = __("Your current subscription does not allow you to view this","shoprocket") . " " . $p->post_type . ".";
                }

              }
              $p->post_content = $message;
              $p->comment_status = 'closed';
            }
            else {
              $exclude = true;
            }
          }
        }
      }
      if($exclude) {
        global $wpdb;
        $post_id = $wpdb->get_col("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_shoprocket_subscription'");
        $args = array(
          'post__not_in' => $post_id
        );
        $args = array_merge($args, $wp_query->query);
        query_posts($args);
      }
    }

  }
  
  /**
   * Hide private pages and pages that require a subscription feature level the subscriber does not have
   */
  public function hidePrivatePages($excludes) {
    global $wpdb;
    $hidePrivate = true;
    $mySubItemNums = array();
    $activeAccount = false;
    $featureLevel = false;

    if(ShoprocketCommon::isLoggedIn()) {
      $hidePrivate = false;
      $account = new ShoprocketAccount(ShoprocketSession::get('ShoprocketAccountId'));
      
      if($account->isActive()) {
        $activeAccount = true;
        $featureLevel = $account->getFeatureLevel();
      }
      
      // Optionally add the logout link to the end of the navigation
      if(ShoprocketSetting::getValue('auto_logout_link')) {
        add_filter('wp_list_pages', array($this, 'appendLogoutLink'));
      }

      // Hide guest only pages
      $guestOnlyPageIds = ShoprocketAccessManager::getGuestOnlyPageIds();
      $excludes = array_merge($excludes, $guestOnlyPageIds);
    }

    // Hide pages requiring a feature level that the subscriber does not have
    $hiddenPages = ShoprocketAccessManager::hideSubscriptionPages($featureLevel, $activeAccount);
    if(count($hiddenPages)) {
      $excludes = array_merge($excludes, $hiddenPages);
    }

    if($hidePrivate) {
      // Build list of private page ids
      $privatePageIds = ShoprocketAccessManager::getPrivatePageIds();
      $excludes = array_merge($excludes, $privatePageIds);
    }

    // Merge private page ids with other excluded pages
    if(is_array(get_option('exclude_pages'))){
  		$excludes = array_merge(get_option('exclude_pages'), $excludes );
  	}

    sort($excludes);
    return $excludes;
  }
  
  public function appendLogoutLink($output) {
    $output .= "<li><a href='" . ShoprocketCommon::appendQueryString('shoprocket-task=logout') . "'>Log out</a></li>";
    return $output;
  }
  
  /**
   * Force downloads for
   *   -- Shoprocket reports (admin)
   *   -- Downloading the debuggin log file (admin)
   *   -- Downloading digital product files
   */
  public function forceDownload() {

    ob_end_clean();

    if($_SERVER['REQUEST_METHOD'] == 'POST' && ShoprocketCommon::postVal('shoprocket-action') == 'export_csv') {
      require_once(SHOPROCKET_PATH . "/models/ShoprocketExporter.php");
      $start = str_replace(';', '', $_POST['start_date']);
      $end = str_replace(';', '', $_POST['end_date']);
      ShoprocketCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Date parameters for report: START $start and END $end");
      $report = ShoprocketExporter::exportOrders($start, $end);

      header('Content-Type: application/csv'); 
      header('Content-Disposition: inline; filename="ShoprocketReport.csv"');
      echo $report;
      die();
    }
    elseif($_SERVER['REQUEST_METHOD'] == 'POST' && ShoprocketCommon::postVal('shoprocket-action') == 'download log file') {

      $logFilePath = ShoprocketLog::getLogFilePath();
      if(file_exists($logFilePath)) {
        $logData = file_get_contents($logFilePath);
        $cartSettings = ShoprocketLog::getCartSettings();

        header('Content-Description: File Transfer');
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename=ShoprocketLogFile.txt');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        echo $cartSettings . "\n\n";
        echo $logData;
        die();
      }
    }
    elseif($_SERVER['REQUEST_METHOD'] == 'POST' && ShoprocketCommon::postVal('shoprocket-action') == 'clear log file') {
      ShoprocketCommon::clearLog();
    }
    
  }
  
  public function addPageSlurpButton() {
    global $post;
    if(ShoprocketCommon::isSlurpPage()) {
      // echo "<a href='#' id='page_slurp'>Slurp</a> ";
    }
  }
  
  public function upgradeDatabase() {
    if(ShoprocketSetting::getValue('auth_force_ssl') == 'no') {
      ShoprocketSetting::setValue('auth_force_ssl', null);
    }
    elseif(ShoprocketSetting::getValue('auth_force_ssl') == 'yes') {
      ShoprocketSetting::setValue('auth_force_ssl', 1);
    }
  }
  
}