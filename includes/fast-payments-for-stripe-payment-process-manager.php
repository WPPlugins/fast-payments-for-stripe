<?php


// Prohibit direct access
if ( ! defined( 'ABSPATH' ) ) 
{
	exit;
}

if ( ! class_exists( 'Fast_Payments_For_Stripe_Payment_Process_Manager' ) ) 
{
    /*
     * Class in charge of interacting with the stripe api and processing payments
     */
    class Fast_Payments_For_Stripe_Payment_Process_Manager
    {
        //private variables
        private static $instance;
        //save the charge object
        private $stripe_charge = null;
        //save the error object
        private $error = null;

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
        	$this->load_required_files();
        	$this->initialize_stripe();
        }


        /*
         * Load the necessary files for this plugin such as javascript, css ect....
         */ 
        private function load_required_files() 
        {
            //Load the Stripe API
            //Make sure to check if class exists due to bug: Fatal error: Cannot redeclare class Stripe\Stripe . 
            if ( ! class_exists( 'Stripe\Stripe' ) ) 
            {
	            require_once( plugin_dir_path( __FILE__ ) . 'stripe-php-3.12.1/init.php' );
            }
        }

        private function initialize_stripe() 
        {
        		$manager = new Fast_Payments_For_Stripe_Option_Manager();   
						$mode = $manager->get_option_db('fastpaymentsstripe_mode');
					  $key_code = '';
						if ('live'  == $mode) {
								$key_code = 'fastpaymentsstripe_secret_key_live';
						}
						else if ('test' == $mode) {
								$key_code = 'fastpaymentsstripe_secret_key_test';
						}
					
            $secret_key = $manager->get_option_db($key_code);
            \Stripe\Stripe::setApiKey($secret_key);
        }

        
        /*
         * Call the Stripe API and process payment
         */ 
        public function process_payment() 
        {

            $processed_successfully = true;

            //Post Data
            $token  = sanitize_text_field($_POST['stripeToken']);
            $email  = sanitize_email($_POST['stripeEmail']);
            $amount = sanitize_text_field($_POST['hidden_fastpaymentsstripe_amount']);
            $ccy = sanitize_text_field($_POST['hidden_fastpaymentsstripe_default_currency']);
            $description = sanitize_text_field($_POST['hidden_fastpaymentsstripe_description']);
            $name = sanitize_text_field($_POST['hidden_fastpaymentsstripe_name']);
            
            //Basic error checking
            if (null == $amount || '' == $amount) {
                throw new Exception('Amount not found.');
            }
            else if (null == $ccy || '' == $ccy) {
                throw new Exception('Currency not found');
            }
            
            try{
                
                //Create customer in strie
                $customer = \Stripe\Customer::create(array(
                    'email' => $email,
                    'description' => 'Purchased ' . $description,
                    'card'  => $token,
                ));

                //Charge that customer
                $charge = \Stripe\Charge::create(array(
                        'customer' => $customer->id,
                        'amount'   => $amount,
                        'currency' => $ccy,
                        'description' => $description,
                        'statement_descriptor' => $name
                ));	
                
                $this->stripe_charge = $charge;
            }
            catch( \Stripe\Error\Card $e )
            {
                //Only capture stripe errors
                //If the charge fails, we'll end up here
                $this->error = $e;
                $this->stripe_charge = $e = $e->getJsonBody()['error']['charge'];
                $processed_successfully = false;
            }

            return $processed_successfully;
        } 
        
        public function charge_get() 
        {
            return $this->stripe_charge;
        }

        public function error_get() 
        {
            return $this->error;
        }
    }
}