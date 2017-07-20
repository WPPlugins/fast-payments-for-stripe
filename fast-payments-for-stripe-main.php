<?php

if ( ! class_exists( 'Fast_Payments_For_Stripe_Main' ) ) 
{
    /**
     * Main class for the plugin
     * Locking class down by using Singleton
     */
    class Fast_Payments_For_Stripe_Main
    {
        //************************************
        //private variables
        //************************************
        private static $instance;       //Variable for instance


        /*
         * Singletons require a private constructor
         */
         private function __construct()
         {
            //Make sure to load the front end scripts first. If we are in the admin area, the second call to load scripts will overwrite the first call
            $this->load_front_end_scripts();
            $this->load_required_files();
            $this->set_add_actions();
            
         }

         /*
         * Method need to get the singleton instance
         */
        public static function get_instance()
        {
            if (null === static::$instance) 
            {
                static::$instance = new static();
            }        
            return static::$instance;
        }
      
        public function load_front_end_scripts() 
        {
            if ( !is_admin() ) {
              wp_enqueue_style('fast-payments-for-stripe-default-css', plugins_url('/css/fast-payments-for-stripe-front-end.css', __FILE__) );   
            }
        }
     
        /*
         * Load the necessary files for this plugin such as javascript, css ect....
         * These classes contain logic for stripe checkout
         */ 
        private function load_required_files() 
        {
            //Class interacts with database
           require_once( plugin_dir_path( __FILE__ ) . 'includes/fast-payments-for-stripe-option-manager.php' );
           //Creates form in post
           require_once( plugin_dir_path( __FILE__ ) . 'includes/fast-payments-for-stripe-shortcode-manager.php' );
           //Processes payment
           require_once( plugin_dir_path( __FILE__ ) . 'includes/fast-payments-for-stripe-payment-process-manager.php' );
           //Print receipts in page
           require_once( plugin_dir_path( __FILE__ ) . 'includes/fast-payments-for-stripe-receipt-manager.php' );
           //Helper functions
           require_once( plugin_dir_path( __FILE__ ) . 'includes/fast-payments-for-stripe-helper.php' );
          
           wp_enqueue_style('fast-payments-for-stripe-default-css', plugins_url('/css/fast-payments-for-stripe-default.css', __FILE__) );   
           wp_enqueue_script( 'fast-payments-for-stripe-default-js', plugin_dir_url( __FILE__) . '/js/fast-payments-for-stripe-default.js' );
        }

         /*
         * Load the necessary files for this plugin such as javascript, css ect....
         * Fires regardless a user is logged in or not
         */ 
        private function set_add_actions() 
        {
            //For more help, see: https://codex.wordpress.org/Function_Reference/add_options_page
            add_action( 'admin_menu', array( $this, 'add_admin_menu_links' ));

            //This loads all the classes
            add_action( 'init', array( $this, 'init_classes' ), 1 );            
        }

        /*
         * Add items to menu page located on the left hand side of wordpress admin
         * Fires only when a user is logged in
         */
        public function add_admin_menu_links()
        {	
            //Add a main menu called  WP Fast Payments
            add_menu_page(
            'Setup Fast Payments' //Title page (Heading that's in browser)
            ,'Fast Payments' //Menue Item
            ,'manage_options' //Capability
            ,'wpfastpaymentsadminpage' //Menu SLug
            ,array($this,'require_admin_view') //Callable Function
            ,plugins_url( 'img/fp_16x16.png', __FILE__  ) //Icon URL
            );
          
            //Help page
            add_submenu_page (
            'wpfastpaymentsadminpage' //Parent slulg
            ,'Short Codes Help' //Title page
            ,'Shortcodes' //Menu Title
            ,'manage_options' //Capability
            ,'wpfastpaymentshelppage' //Menu Slug
            ,array($this,'require_help_view') //Callback function
            );
        }

        /*
         * Load the admin view
         * Fires only when a user is logged in
         */
        public function require_admin_view()
        {
            require_once( plugin_dir_path( __FILE__ ) . 'views/fast-payments-for-stripe-admin.php' );
        }
      
         /*
         * Load the help view
         * Fires only when a user is logged in
         */
        public function require_help_view()
        {
            require_once( plugin_dir_path( __FILE__ ) . 'views/fast-payments-for-stripe-help.php' );
        }

        /*
         * Initializes all of the Singleton classes we need to handle plugin functionality
         * Fires regardless of a user logged in or not
         * NOTE: Make user to only include the clases we really need here
         */
       public function init_classes() {
            Fast_Payments_For_Stripe_ShortCode_Manager::get_instance();
            Fast_Payments_For_Stripe_Receipt_Manager::get_instance();
        }    
    }
}
