<?php

// Prohibit direct access
if ( ! defined( 'ABSPATH' ) ) 
{
	exit;
}

if ( ! class_exists( 'Fast_Payments_For_Stripe_Receipt_Manager' ) ) 
{
    /*
     * Helper class will for retrieving and updating database
     */
    class Fast_Payments_For_Stripe_Receipt_Manager
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
            $this->load_required_files();
            $this->initialize_stripe();
            
            //Add filter to show receipt
            add_filter( 'the_content', array( $this, 'show_receipt' ));
            
        }
        
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
        
        public function show_receipt( $content ) { 		
            
            //Check query string here
            if(isset( $_GET['charge_id'] ))
            {
                $charge_id = sanitize_text_field($_GET['charge_id']); 			
                $charge = null;							

                // In case user messes with charge id
                try {
                    $charge = \Stripe\Charge::retrieve($charge_id);
                }
                catch(Exception  $e ) {
                }
                
                if(null != $charge) {
                    $content .= $this->dispaly_success_message($charge);							
                }                
            }
            else if(isset( $_GET['fail_id'] ))
            {

                $charge_id = sanitize_text_field($_GET['fail_id']); 
                $charge = null;		

                // In case user messes with charge id
                try {
                    $charge = \Stripe\Charge::retrieve($charge_id);
                }
                catch(Exception  $e ) {
                }

                //return data to post if the charge object is valid
                if(null != $charge) {
                    $content .= $this->display_failiure_message($charge);
                }
            }
            
            return $content;
        }

        private function dispaly_success_message($charge) 
        {
            if(null == $charge)
                throw new Exception('charge is null');
					
						$helper = Fast_Payments_For_Stripe_Helper::get_instance();
					  $manager = new Fast_Payments_For_Stripe_Option_Manager();   
						$disable_message = $manager->get_option_db('fastpaymentsstripe_disable_success');
					  if(0 != $disable_message)
							return;

					  $output = '<div class="fast_payments_for_stripe_receipt_success">';
            $output .= '<b>Receipt</b></br>' ;
            $output .= '<b>Amout Charged: </b>' . $helper->format_amount_for_display($charge->amount,$charge->currency) . '</br>';
            $output .= '<b>Description: </b>' .  $helper->format_stripe_strings_for_display($charge->description) . '</br>';
					  $output .= '</div>';
            return $output;
        }

        private function display_failiure_message($charge) 
        {
					  $manager = new Fast_Payments_For_Stripe_Option_Manager();   
					  $disable_message = $manager->get_option_db('fastpaymentsstripe_disable_failure');
					  if(0 != $disable_message)
							return;
					
            if(null == $charge)
                throw new Exception('charge is null');

            $output = '<div class="fast_payments_for_stripe_receipt_fail">';
            $output .= 'Transaction Failed </br>'; 						
            $output .= 'Reason: ' . $charge->failure_message . ' </br>'; 			
            $output .= '</div>';
            return $output;
        }
    }
}