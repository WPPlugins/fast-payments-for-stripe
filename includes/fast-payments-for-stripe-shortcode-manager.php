 <?php


 // Prohibit direct access
 if ( ! defined( 'ABSPATH' ) ) 
 {
     exit;
 }

 if ( ! class_exists( 'Fast_Payments_For_Stripe_ShortCode_Manager' ) ) 
 {
     /*
      * This class is in control of handling shortcodes
      */
     class Fast_Payments_For_Stripe_ShortCode_Manager
     {

         //private variables
         private static $instance;      
         
         /*
          * method need to get the singleton instance
          */
         public static function get_instance()
         {
             if (null === static::$instance) 
             {
                 static::$instance = new static();
             }        
             return static::$instance;
         }        

         /*
          * private constructor for singleton class
          */
         private function __construct()
         {
             add_action ('init', array( $this, 'process_form_data'));
             add_shortcode('fast_payments', array( $this, 'fast_payments_manage_shortcodes' ));
         }

         /*
          * Main function that manages all the shortcodes 
          */
         public function fast_payments_manage_shortcodes($atts, $content = null)
         {
             $manager = new Fast_Payments_For_Stripe_Option_Manager();     
             $helper = Fast_Payments_For_Stripe_Helper::get_instance();
             
             //Set default attibutes
             //(Great video about atts) https://www.youtube.com/watch?v=Uvu5bhlVpPQ		
             $fp_name = $manager->get_option_db('fastpaymentsstripe_name'); 
             $fp_ccy = $manager->get_option_db('fastpaymentsstripe_default_currency');
             $fp_url_success = $manager->get_option_db('fastpaymentsstripe_success_url');
             $fp_url_fail = $manager->get_option_db('fastpaymentsstripe_failure_url');
             $fp_mode = $manager->get_option_db('fastpaymentsstripe_mode');

             $fp_address = $manager->get_option_db('fastpaymentsstripe_address');
             $fp_bitcoin = $manager->get_option_db('fastpaymentsstripe_bitcoin');             
             $fp_zip = $manager->get_option_db('fastpaymentsstripe_zip');
             

             $atts = shortcode_atts(
                 array(
                    'name' => (null !== $fp_name ? $fp_name: ''),
                    'ccy' => (null !== $fp_ccy ? $fp_ccy: ''),
                    'success_url' => (null !== $fp_url_success ? $fp_url_success: get_permalink()),
                    'failure_url' => (null !== $fp_url_fail ? $fp_url_fail: get_permalink()),
                    'mode' => (null !== $fp_mode ? $fp_mode: ''),
                    'amount' => '',
                    'image' => '',
                    'description' => '',
                    'locale' => 'auto',
                    'address' => (1 == $fp_address ? 'true' : 'false'),
                    'bitcoin' => (1 == $fp_bitcoin ? 'true':'false'),
                    'verify_zipcode' => (1 == $fp_zip ? 'true': 'false')
             ), $atts);

             //Hidden values are used to keep track of original values when stripe posts back data
             //We do this in case a user changes the amounts in the database while stipe being processed before returning token
             //Also, protect against malicious hacking attacks with a nonce. Only process data coming form this form
             $hidden_input = $this->fast_payments_hidden_control_create ('fast_stripe_verify' , wp_create_nonce( 'payment-gateway-for-wordpress' )); 

             //*******************************************************************
             // Check for required values
             //*******************************************************************
             if (array_key_exists('name',$atts) && '' == $atts['name']) {
                 return $helper->check_short_code_error('name');
             }
             else if (array_key_exists('ccy',$atts) && '' == $atts['ccy']) {
                 return $helper->check_short_code_error('Currency (ccy)');
             }
             //TODO: Check for stripe supported currencies
             else if (array_key_exists('amount',$atts) && '' == $atts['amount']) {
                 return $helper->check_short_code_error('amount');
             }
             else if (array_key_exists('mode',$atts) && '' == $atts['mode']) {
                 return $helper->check_short_code_error('mode');
             }

             //Header
             $html_form= '<form action="" method="POST">
        				     <script
    						    src="https://checkout.stripe.com/checkout.js" class="stripe-button"';

             //Name 
             if (array_key_exists('name',$atts)) {
                 $html_form .= 'data-name="' . $atts['name']  . '"';
                 $hidden_input .= $this->fast_payments_hidden_control_create('fastpaymentsstripe_name', $atts['name']);
             }

             //Currency
             if (array_key_exists('ccy',$atts)) {
                 $html_form .= 'data-currency="' . $atts['ccy']  . '"';
                 $hidden_input .= $this->fast_payments_hidden_control_create('fastpaymentsstripe_default_currency', $atts['ccy']);
             }

             //Description
             if (array_key_exists('description',$atts)) {
                 $html_form .= 'data-description="' . $atts['description']  . '"';
                 $hidden_input .= $this->fast_payments_hidden_control_create('fastpaymentsstripe_description', $atts['description']);
             }

             //Image
             if (array_key_exists('image',$atts)) {
                 $html_form .= 'data-image="' . $atts['image']  . '"';
                 $hidden_input .= $this->fast_payments_hidden_control_create('fastpaymentsstripe_image', $atts['image']);
             }

             //Use Address
             if (array_key_exists('address',$atts)) {
                 $html_form .= 'data-billing-address="' . $atts['address']  . '"';
                 $hidden_input .= $this->fast_payments_hidden_control_create('fastpaymentsstripe_address', $atts['use_address']);
             }

             //Locale
             if (array_key_exists('locale',$atts)) {
                 $html_form .= 'data-locale="' . $atts['locale']  . '"';
                 $hidden_input .= $this->fast_payments_hidden_control_create('astpaymentsstripe_locale', $atts['locale']);
             }
             
             //Bitcoin
             if (array_key_exists('bitcoin',$atts)) {
                 $html_form .= 'data-bitcoin="' . $atts['bitcoin']  . '"';
                 $hidden_input .= $this->fast_payments_hidden_control_create('fastpaymentsstripe_bitcoin', $atts['bitcoin']);
             }
             
             //Zipcode
             if (array_key_exists('verify_zipcode',$atts)) {
                 $html_form .= 'data-bitcoin="' . $atts['verify_zipcode']  . '"';
                 $hidden_input .= $this->fast_payments_hidden_control_create('fastpaymentsstripe_zipcode', $atts['zipcode']);
             }	

             //Publish key
             if (array_key_exists('mode',$atts)) {
                 $key_code = ('LIVE' == strtoupper($atts['mode']) ? 'fastpaymentsstripe_publish_key_live': 'fastpaymentsstripe_publish_key_test');
                 $key = $manager->get_option_db($key_code);
                 if (!empty($key)) {
                     $html_form .= 'data-key="' . $key . '"'; 
                 } 
                 else 
                 {
                     return $helper->check_short_code_error($atts['mode'] . ' API KEY');
                 }
             }
             
             //Amount
             if (array_key_exists('amount',$atts)) {
                 
                 $amount = $atts['amount'];
                 if($helper->pass_minimum_amount($amount, $atts['ccy'])) {
                     $html_form .= 'data-amount="' . $atts['amount']  . '"';
                     $hidden_input .= $this->fast_payments_hidden_control_create('fastpaymentsstripe_amount', $atts['amount']);
                 }
                 else {
                     return $helper->check_short_code_error_custom('Stripe minimum amount not met');
                 } 
             }		
             
             //Redirect URL if payment goes through
             if (array_key_exists('success_url',$atts)) {
                 $hidden_input .= $this->fast_payments_hidden_control_create('fastpaymentsstripe_success_url', $atts['success_url']);
             }

             //Redirect URL if payment fails
             if (array_key_exists('failure_url',$atts)) {
                 $hidden_input .= $this->fast_payments_hidden_control_create('fastpaymentsstripe_failure_url', $atts['failure_url']);
             }

             //End the script
             $html_form .= '>
						   </script>';

             //Hidden input values
             $html_form .=  $hidden_input;

             //Footer                                    
             $html_form .= '</form>';	

             return $html_form;

         }

         public function fast_payments_hidden_control_create ($name , $value) 
         {
             return '<input type="hidden" name="hidden_' . $name . '" value="' . $value . '" />';
         }

         function process_form_data () {				
             if(isset($_POST['hidden_fast_stripe_verify'])) {
                 if(wp_verify_nonce(sanitize_text_field($_POST['hidden_fast_stripe_verify']),'payment-gateway-for-wordpress')) {			
                     
                     //Process payment
                     $payment_processor = Fast_Payments_For_Stripe_Payment_Process_Manager::get_instance();				
                     $processed_successfully = $payment_processor->process_payment();									
                     
                     $redirect_url = null;
                     if(true == $processed_successfully) {

                         //Since the transaction was successful, we'll have a charge object
                         $charge = $payment_processor->charge_get();

                         //Redirect with appropriate query string
                         $redirect_url = esc_url_raw($_POST['hidden_fastpaymentsstripe_success_url']);	
                         $redirect_url = add_query_arg( 'charge_id',$charge->id, $redirect_url );
                     }
                     else {

                         $error = $payment_processor->error_get();
                         $charge_id = $payment_processor->charge_get();


                         //Redirect with appropriate error message
                         $redirect_url = esc_url_raw($_POST['hidden_fastpaymentsstripe_failure_url']);	                         
                         $redirect_url = add_query_arg( 'fail_id', $charge_id, $redirect_url );
                     }

                     
                     if(null != $redirect_url) {
                         wp_redirect($redirect_url);
                         exit;
                     }
                 }
             }
         }
     }
 }
