<?php

namespace Etn\Core\Woocommerce;

defined( 'ABSPATH' ) || exit;

class Base {

    use \Etn\Traits\Singleton;

    // $api veriable call for Cpt Class Instance
    public $product;

    // $api veriable call for Api Class Instance
    public $api;

    // set template type for template
    public $template_type = [];

    public function get_dir() {
        return dirname( __FILE__ );
    }

    public function init() {
        // call custom post type
        Hooks::instance()->Init();
        
        $is_enable_payment_timer = etn_get_option( 'ticket_purchase_timer_enable', 'off' );

        if ( $is_enable_payment_timer == 'on' ) {
            new \Eventin\Woocommerce\Payment_Timer();
        }
    }
}
