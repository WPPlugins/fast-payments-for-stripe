<?php

// Prohibit direct access
if ( ! defined( 'ABSPATH' ) ) 
{
	exit;
}

if ( ! class_exists( 'Fast_Payments_For_Stripe_Helper' ) ) 
{
    /*
     * Helper class will for retrieving and updating database
     */
    class Fast_Payments_For_Stripe_Helper
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
        }

        // *************************** Static Helper Functions From Here On ***************************
        
        /*
        //URL: https://support.stripe.com/questions/what-is-the-minimum-amount-i-can-charge-with-stripe
        Currency:	Minimum Charge
        USD: United States Dollar	$0.50
        CAD: Canadian Dollar	$0.50
        GBP: British Pound	£0.30
        EUR: Euro	€0.50
        DKK: Danish Krone	2.50-kr.
        NOK: Norwegian Krone	3.00-kr.
        SEK: Swedish Krona	3.00-kr.
        CHF: Swiss Franc	0.50 Fr
        AUD: Australian Dollar	$0.50
        JPY: Japanese Yen	¥50
        MXN: Mexican Peso	$10
        SGD: Singapore Dollar	$0.50
         */
        public static function pass_minimum_amount($amount, $ccy) {
                       
            if(empty($amount))
                return false;
            
            switch ($ccy) {
                case "USD":
                    if($amount < 50) return false;
                    break;
                case "CAD":
                    if($amount < 50) return false;
                    break;
                case "GBP":
                    if($amount < 30) return false;
                    break;
                case "EUR":
                    if($amount < 50) return false;
                    break;
                case "DKK":
                    if($amount < 250) return false;
                    break;
                case "NOK":
                    if($amount < 300) return false;
                    break;
                case "SEK":
                    if($amount < 300) return false;
                    break;
                case "CHF":
                    if($amount < 50) return false;
                    break;
                case "AUD":
                    if($amount < 50) return false;
                    break;
                case "JPY":
                    if($amount < 50) return false;
                    break;
                case "MXN":
                    if($amount < 10) return false;
                    break;			
                case "SGD":
                    if($amount < 50) return false;
                    break;												
            }
            
            return true;
        }


        /*
         * Takes the amount stripe returns and formats it to be displayed in the screen.
         * Example: Stripe api returns 900 fir 9.99 USD
         */
        public static function format_amount_for_display($amount, $ccy) {
            
            //If variables are not set, return
            if(empty($amount))
                return $amount;
            else if(empty($ccy))
                return $amount;
            
            
            if(!self::is_zero_decimal_ccy($ccy))  {
                $amount = round( $amount / 100, 2 );
                $amount = number_format_i18n($amount, 2);
            }
            else {
                $amount = number_format_i18n($amount, 0);
            }
            return $amount;
        }


        /*
         * Currencies Stripe currently support. Keep making changes here until stripe
         *  https://support.stripe.com/questions/which-zero-decimal-currencies-does-stripe-support
         */
        public static function is_zero_decimal_ccy($ccy) {
            $zero_decimal_ccy = array('BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','VND','VUV','XAF','XOF','XPF');            
            return in_array(strtoupper($ccy),$zero_decimal_ccy);
        }
        
        /*
         * Format the description we get back from stripe
         */
        public static function format_stripe_strings_for_display($value) {
            return str_replace("\\", "",$value);
        }

        /*
         * Return appropriate error message
         */
        public static function check_short_code_error($short_code) {
            if ( current_user_can( 'manage_options' ) ) {
                return '<p class="fast_payments_for_stripe_alert">' . strtoupper($short_code) . ' not set. Please set ' . strtoupper($short_code) . ' for stripe button to appear.</p>';
            }
            else {
                return '';
            }
        }

        /*
         * Return custom error message
         */
        public static function check_short_code_error_custom($error_message) {
            if ( current_user_can( 'manage_options' ) ) {
                return '<p class="fast_payments_for_stripe_alert">' . $error_message . '</p>';
            }
            else {
                return '';
            }
        }
    }
}