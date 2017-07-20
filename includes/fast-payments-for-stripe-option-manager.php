<?php

// Prohibit direct access
if ( ! defined( 'ABSPATH' ) ) 
{
	exit;
}

if ( ! class_exists( 'Fast_Payments_For_Stripe_Option_Manager' ) ) 
{
    /*
     *  Class will be used for retrieving and updating database
     */
    class Fast_Payments_For_Stripe_Option_Manager
    {
        /*
         * Gets a value from the database. 
         * Throws error if it is not there. This will help us to catch potential errors faster
         */
        public static function get_option_db($option_name) 
        {
            $returnVal = get_option($option_name);

            //Break if we find a new value.
            if(is_null($returnVal))
                throw new Exception('Null value found for ' .$option_name);
            
            //Escape for HTML attributes
            return esc_attr($returnVal);
        }

        /*
         * Validates user input before saving in database
         */
        public static function sanitize_and_save_option($option_name, $input) 
        {
					  //Sanitize
					  $user_input = sanitize_text_field($input);
					
            //trip the leading and trailing spaces in case uer hits the space button
            $user_input = trim($input);

            update_option($option_name, $user_input);
        }

			  /*
         * If data has been sanitized before, use this method
         */
				public static function save_sanitized_option($option_name, $input) 
				{
            //trip the leading and trailing spaces in case uer hits the space button
            $user_input = trim($input);

            update_option($option_name, $user_input);					
				}
    }
}