<?php
/**
 * Plugin Name: NRV - Brunch Delivery
 * Plugin URI: https://agencenrv.fr
 * Description: Une extension permettant la gestion des livraisons de brunchs dysee
 * Version: 0.9.2
 * Author: NRV Development
 * Author URI: https://agencenrv.fr
 * Text Domain: nrvbd
 * Domain Path: /languages
 * Requires PHP: 7.4.0
 */

 
/**
 * Blocking direct access to the plugin
 */
if (!function_exists('add_action')) {
    echo 'Hello there! Do not call me directly.';
    exit;
}

/**
 * Defining the constants
 */
define('NRVBD_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('NRVBD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NRVBD_DEFAULT_API_KEY', 'AIzaSyDhDKO702eZz19XeaYv5aNKqvCdBCGk83I');


/**
 * Loading plugin translations
 */
add_action('plugins_loaded', function(){
    load_plugin_textdomain( 'nrvbd', FALSE, basename( dirname( __FILE__ ) ) . '/languages' );
});


/**
 * Global variables
 */
$NRVBD_CONFIGURATION_MENU = array();

/**
 * Autoloader
 */
include_once NRVBD_PLUGIN_PATH.'inc/autoload.php';
require 'vendor/autoload.php';


/**
 * Activation process
 */
include_once NRVBD_PLUGIN_PATH.'activation.php';

/**
 * Deactivation process
 */
include_once NRVBD_PLUGIN_PATH.'deactivation.php';
// register_deactivation_hook(__FILE__, "nrvbd_plugin_deactivation");


/**
 * Load the functions
 */
include_once NRVBD_PLUGIN_PATH.'inc/functions.php';


/**
 * Load the admin functions
 */
if(is_admin()){
    include_once NRVBD_PLUGIN_PATH.'inc/admin.php';
}
include_once NRVBD_PLUGIN_PATH.'inc/interfaces.php';

