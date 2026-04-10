<?php

/**
 * Payment Factory Class
 * 
 * @package Eventin
 */
namespace Eventin\Order;

defined( 'ABSPATH' ) || exit;

use Eventin\Integrations\WC\WCPayment;
use Eventin\Integrations\LocalPayment\LocalPayment;
use Exception;
/**
 * PaymentFactory
 */
class PaymentFactory {

    /**
     * Get selected payment method
     *
     * @param   string  $payment_methods  [$payment_methods description]
     *
     * @return PaymentMethodInterface
     */
    public static function get_method( $payment_methods ) {
        $methods = self::get_payment_methods();

        if ( ! empty( $methods[$payment_methods] ) ) {
            return new $methods[$payment_methods];
        }

        throw new Exception( esc_html__( 'Unknown payment method.', 'eventin' ) );
    }

    /**
     * Get all available payment methods
     *
     * @return  array  All available payment methods
     */
    private static function get_payment_methods() {
        $methods = [
            'wc'            => WCPayment::class,
            'local_payment' => LocalPayment::class,
            // 'sure_cart' => SureCartPaymentMethod::class
        ];

        return apply_filters( 'eventin_payment_methods', $methods );
    }
}
