<?php

/**
 * Local Payment Class
 *
 * @package Eventin
 */
namespace Eventin\Integrations\LocalPayment;

defined( 'ABSPATH' ) || exit;

use Eventin\Order\OrderModel;
use Eventin\Order\PaymentInterface;

/**
 * Handles offline / local payment orders.
 * No gateway interaction needed — refunds are just a status update.
 */
class LocalPayment implements PaymentInterface {

    /**
     * Create payment — local payment requires no gateway action.
     *
     * @param   OrderModel  $order
     *
     * @return  array
     */
    public function create_payment( OrderModel $order ) {
        return [];
    }

    /**
     * Refund a local payment order.
     * Since payment was collected offline, just signal success so the
     * order status can be updated to "refunded".
     *
     * @param   OrderModel  $order
     *
     * @return  bool
     */
    public function refund( OrderModel $order ) {
        return true;
    }
}
