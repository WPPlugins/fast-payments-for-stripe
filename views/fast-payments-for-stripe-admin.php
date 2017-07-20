<?php

/*
 * This is the admin page. When the user clicks on Easy Stripe or settings page, this code gets executed.
 */

// Prohibit direct access
if ( ! defined( 'ABSPATH' ) ) 
{
	exit;
}	

//this will update the database if the submit button was hit by users
update_fields();

//I'm too object oriented for php
$op_man = new Fast_Payments_For_Stripe_Option_Manager();   

//If no query string is present, display the default value tab. Else set the tab that corresponds to the query string
$active_tab = isset( $_GET[ 'tab' ] ) ? sanitize_text_field($_GET[ 'tab' ]) : 'default_values';

//Test mode or live mode
$live_status = 'unchecked';
$test_status = 'unchecked';
$selected_radio = $op_man->get_option_db('fastpaymentsstripe_mode');
if ('live'  == $selected_radio) {
	$live_status = 'checked';
}
else if ('test' == $selected_radio) {
	$test_status = 'checked';
}
$checked_zip = ($op_man->get_option_db('fastpaymentsstripe_zip') == 1 ? 'checked' : '');
$checked_bitcoin = ($op_man->get_option_db('fastpaymentsstripe_bitcoin') == 1 ? 'checked' : '');
$checked_address = ($op_man->get_option_db('fastpaymentsstripe_address') == 1 ? 'checked' : '');
$checked_failure = ($op_man->get_option_db('fastpaymentsstripe_disable_failure') == 1 ? 'checked' : '');
$checked_success = ($op_man->get_option_db('fastpaymentsstripe_disable_success') == 1 ? 'checked' : '');

?>

<h2 class="nav-tab-wrapper">
    <a href="?page=wpfastpaymentsadminpage&tab=default_values" class="nav-tab <?php echo $active_tab == 'default_values' ? 'nav-tab-active' : ''; ?> fast_payments_for_stripe_margin_left_zero">Stripe Configuration</a>
    <a href="?page=wpfastpaymentsadminpage&tab=api_keys" class="nav-tab <?php echo $active_tab == 'api_keys' ? 'nav-tab-active' : ''; ?>">Stripe API Keys</a>
</h2>

<form method="post" action="">

    <input type="hidden" name="hidden_nonce" value="<?php echo wp_create_nonce('payment-gateway-for-wordpress-adminpage') ?>"/>

    <?php
    if( $active_tab == 'api_keys' ) {
    ?>
    <div class="fast_payments_for_stripe_boxed_alert" id="stripe_key_help">
            <div class="fast_payments_for_stripe_help">
            Please check your <a href="https://dashboard.stripe.com/account/apikeys" target="_blank">Stripe account</a> for all the API keys.
        </div>
    </div>
    <br>
    <div class="fast_payments_for_stripe_boxed">
        <span class="fast_payments_for_stripe_admin_label" for="secret-key-test">Test Secret Key:</span>			
        <input class="fast_payments_for_stripe_admin_textbox_medium" type="text" name="fastpaymentsstripe_secret_key_test" id="secret-key-test" value="<?php echo $op_man->get_option_db('fastpaymentsstripe_secret_key_test'); ?>" />	
    </div>

    <div class="fast_payments_for_stripe_boxed">
        <span class="fast_payments_for_stripe_admin_label" for="publish-key-test">Test Publishable Key:</span>
        <input class="fast_payments_for_stripe_admin_textbox_medium" type="text" name="fastpaymentsstripe_publish_key_test" id="publish-key-test" value="<?php echo $op_man->get_option_db('fastpaymentsstripe_publish_key_test'); ?>" />
    </div>

    <div class="fast_payments_for_stripe_boxed" >
        <span class="fast_payments_for_stripe_admin_label" for="secret-key-live">Live Secret Key:</span>			
        <input class="fast_payments_for_stripe_admin_textbox_medium" type="text" name="fastpaymentsstripe_secret_key_live" id="secret-key-live" value="<?php echo $op_man->get_option_db('fastpaymentsstripe_secret_key_live'); ?>" />
    </div>

    <div class="fast_payments_for_stripe_boxed" >
        <span class="fast_payments_for_stripe_admin_label" for="publish-key-live">Live Publishable Key:</span>
        <input class="fast_payments_for_stripe_admin_textbox_medium" type="text" name="fastpaymentsstripe_publish_key_live" id="publish-key-live" value="<?php echo $op_man->get_option_db('fastpaymentsstripe_publish_key_live'); ?>" />
    </div>
    <?php
        submit_button('Save API Keys');
    }
    else
    {
    ?>
	    <div class="fast_payments_for_stripe_boxed">
            <span class="fast_payments_for_stripe_admin_label">Mode:</span>
					<span>
							<input type="radio" name="fastpaymentsstripe_mode" value="live" <?PHP echo $live_status; ?>> Live
  						<input type="radio" name="fastpaymentsstripe_mode" value="test" <?PHP echo $test_status; ?>> Test
					</span>
					<div class="fast_payments_for_stripe_help">
            Test mode will enable simulation of a credit card. List of test credit card numbers can be found <a href="https://stripe.com/docs/testing" target="_blank"> here</a>.<br>
						Please note that live mode will process an actual charge. 
            </div>	
    </div>
    <div class="fast_payments_for_stripe_boxed">
            <span class="fast_payments_for_stripe_admin_label">Name:</span>
            <input type="text" class="fast_payments_for_stripe_admin_textbox_medium" name="fastpaymentsstripe_name" id="name" value="<?php echo $op_man->get_option_db('fastpaymentsstripe_name'); ?>" />
				    <div class="fast_payments_for_stripe_help">
            Name of business/Store name.
            </div>	
    </div>
    <div class="fast_payments_for_stripe_boxed">
            <div>
                    <span class="fast_payments_for_stripe_admin_label">Currency:</span>			
                    <input type="text" class="fast_payments_for_stripe_admin_textbox_small" name="fastpaymentsstripe_default_currency" id="default-currency" maxlength="3" value="<?php echo $op_man->get_option_db('fastpaymentsstripe_default_currency'); ?>" />	
            </div>
            <div class="fast_payments_for_stripe_help">
                    Three-letter <a href="https://support.stripe.com/questions/which-currencies-does-stripe-support" target="_blank">ISO currency code</a> representing the currency of the charge.
            </div>
    </div>
    <div class="fast_payments_for_stripe_boxed">
            <span class="fast_payments_for_stripe_admin_label">Success URL:</span>			
            <input class="fast_payments_for_stripe_admin_textbox_url" type="text" name="fastpaymentsstripe_success_url" value="<?php echo $op_man->get_option_db('fastpaymentsstripe_success_url'); ?>" />	
            <div class="fast_payments_for_stripe_help">
                    Page to show cutom notice of successful charge.
            </div>            
    </div>
    <div class="fast_payments_for_stripe_boxed">
            <span class="fast_payments_for_stripe_admin_label">Disable Success Message :</span>			
            <input type="checkbox" name="fastpaymentsstripe_disable_success" value="SuccessMessage" <?php echo $checked_success ?>> 
            <div class="fast_payments_for_stripe_help">
                    Disable Success Message
            </div>            
    </div>	
    <div class="fast_payments_for_stripe_boxed">
            <span class="fast_payments_for_stripe_admin_label">Failure URL:</span>			
            <input class="fast_payments_for_stripe_admin_textbox_url" type="text" name="fastpaymentsstripe_failure_url" value="<?php echo $op_man->get_option_db('fastpaymentsstripe_failure_url'); ?>" />	
            <div class="fast_payments_for_stripe_help">
                    Page to show cutom notice that the charge was not complete/error.
            </div>            
    </div>
    <div class="fast_payments_for_stripe_boxed">
            <span class="fast_payments_for_stripe_admin_label">Disable Faillure Message :</span>			
            <input type="checkbox" name="fastpaymentsstripe_disable_failure" value="FailureMessage" <?php echo $checked_failure ?>> 
            <div class="fast_payments_for_stripe_help">
                    Description about Disable Failure Message
            </div>            
    </div>
    <div class="fast_payments_for_stripe_boxed">
            <span class="fast_payments_for_stripe_admin_label">Enable Billing Address :</span>			
            <input type="checkbox" name="fastpaymentsstripe_address" value="BillingAddress" <?php echo $checked_address ?>> 
            <div class="fast_payments_for_stripe_help">
                    Description about Disable Success Message
            </div>            
    </div>	
    <div class="fast_payments_for_stripe_boxed">
            <span class="fast_payments_for_stripe_admin_label">Verify Zip Code:</span>			
            <input type="checkbox" name="fastpaymentsstripe_zip" value="zipcode" <?php echo $checked_zip ?>> 
            <div class="fast_payments_for_stripe_help">
                    Verify zip goes here
            </div>            
    </div>
    <div class="fast_payments_for_stripe_boxed">
            <span class="fast_payments_for_stripe_admin_label">Enable Bitcoin :</span>			
            <input type="checkbox" name="fastpaymentsstripe_bitcoin" value="Bitcoin" <?php echo $checked_bitcoin ?>> 
            <div class="fast_payments_for_stripe_help">
                    Bitcoin
            </div>            
    </div>	
    <?php 
        submit_button('Save Configuration');
    }
    ?>
</form>


<?php 
function update_fields()
{
    if( strtolower($_SERVER['REQUEST_METHOD']) == 'post' && wp_verify_nonce($_POST['hidden_nonce'],'payment-gateway-for-wordpress-adminpage')) 
    {
        $manager = new Fast_Payments_For_Stripe_Option_Manager();  

        #Keys
        if( isset($_POST['fastpaymentsstripe_secret_key_test']))
            $manager->sanitize_and_save_option('fastpaymentsstripe_secret_key_test', $_POST['fastpaymentsstripe_secret_key_test']);

        if( isset($_POST['fastpaymentsstripe_publish_key_test']))
            $manager->sanitize_and_save_option('fastpaymentsstripe_publish_key_test', $_POST['fastpaymentsstripe_publish_key_test']);

        if( isset($_POST['fastpaymentsstripe_secret_key_live']))
            $manager->sanitize_and_save_option('fastpaymentsstripe_secret_key_live', $_POST['fastpaymentsstripe_secret_key_live']);

        if( isset($_POST['fastpaymentsstripe_publish_key_live']))
            $manager->sanitize_and_save_option('fastpaymentsstripe_publish_key_live', $_POST['fastpaymentsstripe_publish_key_live']);
        
        #Mode
        if( isset($_POST['fastpaymentsstripe_mode']))
            $manager->sanitize_and_save_option('fastpaymentsstripe_mode', $_POST['fastpaymentsstripe_mode']);

        #Name
        if( isset($_POST['fastpaymentsstripe_name']))
            $manager->sanitize_and_save_option('fastpaymentsstripe_name', $_POST['fastpaymentsstripe_name']);

        #Ccy
        if( isset($_POST['fastpaymentsstripe_default_currency'])) {
            $ccy = sanitize_text_field($_POST['fastpaymentsstripe_default_currency']);
            if ( strlen( $ccy ) == 3 || strlen( $ccy ) == 0) {
                $manager->save_sanitized_option('fastpaymentsstripe_default_currency', $ccy);
            }
        }
        

        #Sucess URL
        if( isset($_POST['fastpaymentsstripe_success_url'])) {
            $url = esc_url_raw($_POST['fastpaymentsstripe_success_url']);
            $manager->save_sanitized_option('fastpaymentsstripe_success_url', $url);
        }
        
        #Sucess Message
        if( isset($_POST['fastpaymentsstripe_disable_success']) == 'SuccessMessage') 
            $manager->sanitize_and_save_option('fastpaymentsstripe_disable_success', 1);
        else
            $manager->sanitize_and_save_option('fastpaymentsstripe_disable_success', 0);			
        
        #Failiure URL
        if( isset($_POST['fastpaymentsstripe_failure_url'])) {
            $url = esc_url_raw($_POST['fastpaymentsstripe_failure_url']);
            $manager->save_sanitized_option('fastpaymentsstripe_failure_url', $url);
        }

        #Failiure Message
        if( isset($_POST['fastpaymentsstripe_disable_failure']) == 'FailureMessage') 
            $manager->sanitize_and_save_option('fastpaymentsstripe_disable_failure', 1);
        else
            $manager->sanitize_and_save_option('fastpaymentsstripe_disable_failure', 0);

        #Billing Address
        if( isset($_POST['fastpaymentsstripe_address']) == 'BillingAddress') 
            $manager->sanitize_and_save_option('fastpaymentsstripe_address', 1);
        else
            $manager->sanitize_and_save_option('fastpaymentsstripe_address', 0);
        
        #Zipcode
        if( isset($_POST['fastpaymentsstripe_zip']) == 'zipcode') 
            $manager->sanitize_and_save_option('fastpaymentsstripe_zip', 1);
        else
            $manager->sanitize_and_save_option('fastpaymentsstripe_zip', 0);

        #Bitcoint
        if( isset($_POST['fastpaymentsstripe_bitcoin']) == 'Bitcoin') 
            $manager->sanitize_and_save_option('fastpaymentsstripe_bitcoin', 1);
        else
            $manager->sanitize_and_save_option('fastpaymentsstripe_bitcoin', 0);
    }
}
?>