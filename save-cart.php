<?php
/**
* Plugin Name: Save Cart
* Plugin URI: https://github.com/sunitjain23
* Description: Plugin that saves the cart information, generates an encrypted link for the cart, sends the link via email and allows for checkout when clicked.
* Version: 1.0
* Author: Sunit Jain
* Author URI: https://github.com/sunitjain23
**/

define( 'MAIN_DIR_PATH', __FILE__  );
define( 'SAVE_CART_DIR_PATH', plugin_dir_path( __FILE__ ) );

require_once SAVE_CART_DIR_PATH.'class-save-cart.php';
