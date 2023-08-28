<?php
if ( ! class_exists( 'SaveCart' ) ) {
    class SaveCart {

        const SCRIPT_NAME      = 'save-cart';

        /**
         * Constructor
         */
        public function __construct() {
            $this->init();
        }
        
        /**
         * Setting up Initialization
         */
        public function init() {
            register_activation_hook( MAIN_DIR_PATH, [$this, 'activate_plugin' ] );
            add_action( 'woocommerce_proceed_to_checkout', [$this , 'save_cart_button'], 10 );
            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_custom_scripts' ], 10, 1 );
            add_action( 'init', [$this , 'save_new_cart'], 10 );
            add_action('wp_ajax_save_cart_ajx', [$this, 'save_cart_ajx'] );
            add_action('wp_ajax_nopriv_save_cart_ajx', [$this, 'save_cart_ajx'] );
        }

        /**
         * Deactivate the plugin and display a notice if woocommerce is not active.
         */
        public function activate_plugin() {
            if ( ! class_exists( 'WooCommerce' ) ) {
                $this->deactivate_plugin();
                wp_die( 'Please activate woocoomerce plugin first.' );
            }
        }

        /**
         * Enqueue custom scripts.
         *
         */
        public function enqueue_custom_scripts() {
            if(is_cart()) {
                wp_enqueue_script(
                    self::SCRIPT_NAME,
                    plugins_url( 'save-cart/js/main.js' ),
                    ['jquery'],
                    filemtime( SAVE_CART_DIR_PATH . 'js/main.js' ),
                    true
                );
                $localizeData = [
                    'ajaxUrl'  => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce( 'save-cart' ),
                ];
                wp_localize_script(
                    self::SCRIPT_NAME,
                    'saveCartParams',
                    $localizeData
                );
            }
        }

        /**
         * Save new cart values and redirect to checkout.
         *
         */
        public function save_new_cart() {
            if(isset($_GET['save_cart']) && $_GET['save_cart']){
                $key = $_GET['save_cart'];
                $data = get_option('save_cart_'.$key);
                if(!empty($data)){
                    WC()->cart->empty_cart();
                    foreach ( $data as $cart_item ) {
                        $product_id = $cart_item['product_id'];
                        $variation_id = $cart_item['variation_id'];
                        $quantity = $cart_item['quantity'];
                        WC()->cart->add_to_cart($product_id, $quantity, $variation_id);
                    }
                    delete_option('save_cart_'.$key);
                    wp_safe_redirect(wc_get_checkout_url());
                    die;
                }
            }
        }
        
        /**
         * Function to deactivate the plugin
         */
        protected function deactivate_plugin() {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            deactivate_plugins( plugin_basename( __FILE__ ) );
            if ( isset( $_GET['activate'] ) ) {
                unset( $_GET['activate'] );
            }
        }

        /**
         * custom button on cart page
         */
        public function save_cart_button() { ?>
            <a href="#" class="button generate_cart"><?php echo esc_html__('Generate Link for cart', 'save-cart'); ?></a>
            <input type="hidden" id="save_user_id" value="<?php echo esc_attr(get_current_user_id()); ?>">
            <span id="show_url"></span>
            <?php
        }
        
        /**
         * Ajax on save cart button
         */
        public function save_cart_ajx() {
            if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'save-cart' ) ) {
                return;
            }
            $user_id = (int) $_POST['user_id'];
            $key = $this->encode_data(rand());
            $url = home_url('/').'?save_cart='.$key;
            if($user_id > 0) {
                $user = get_user_by('ID', $user_id);
                $message = 'Here are the below link to get data in cart below <br/ > URL: '.$url;
                $headers = ['Content-Type: text/html; charset=UTF-8'];
                wp_mail($user->user_email, 'Save Cart Email', $message, $headers);
                $return = [
                    'message'  => 'Please check the mail for link',
                    'url'       => $url
                ];
            } else{
                $return = [
                    'message'  => 'You have not logged in please go through this link <a href="'.$url.'">here</a>',
                    'url'       => $url
                ];
            }
            update_option('save_cart_'.$key, WC()->cart->get_cart());
            wp_send_json($return);
        }

        /**
         * Encode data
         */
        protected function encode_data($input) {
            return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
        }

        /**
         * Decode data
         */
        protected function decode_data($input) {
            return base64_decode(str_pad(strtr($input, '-_', '+/'), strlen($input) % 4, '=', STR_PAD_RIGHT));
        }
    }
    new SaveCart();
}

?>