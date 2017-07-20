<?php
/**
 * Plugin Name: Fast Payments For Stripe
 * Plugin URI: https://www.wpfastpaymentsforstripe.com/
 * Description: Process credit card payments via stripe.
 * Author: CodeLab LLC
 * Author URI:  http://wpfastpaymentsforstripe.com/
 * Version: 1.0.0
 * Licence: GPLv2
 */

// Prohibit direct access
if ( ! defined( 'ABSPATH' ) ) 
{
	exit;
}

//Include class file if the class is not initiated
if ( ! class_exists( 'Fast_Payments_For_Stripe_Main' ) ) 
{
	require_once( plugin_dir_path( __FILE__ ) . 'fast-payments-for-stripe-main.php' );
}

//Entry Point
Fast_Payments_For_Stripe_Main::get_instance();

?>