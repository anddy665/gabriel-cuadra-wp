<?php
namespace Eventin\Woocommerce;

defined( 'ABSPATH' ) || exit;

/**
 * Class PaymentTimer
 *
 * This class handles the payment timer functionality for the Eventin plugin.
 */
class Payment_Timer {
    /**
     * Constructor for the Payment_Timer class.
     *
     * Initializes the payment timer functionality.
     */
    public function __construct() {
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        $is_enable_payment_timer = etn_get_option( 'ticket_purchase_timer_enable', 'off' );
        
        add_action( 'woocommerce_checkout_init', [ $this, 'start_timer' ] );
        
        if($is_enable_payment_timer == 'on') {
            add_action( 'wp_footer', [ $this, 'display_timer' ] );
        }
        
        add_action( 'template_redirect', [ $this, 'empty_cart_after_timeout' ] );
        add_action( 'woocommerce_thankyou', [ $this, 'reset_payment_timer' ] );
        add_action( 'eventin_order_completed', [ $this, 'reset_payment_timer' ] );

        $this->reset_timer_on_order_status();
    }

    /**
     * Starts the payment timer for the current session.
     *
     * @return  void
     */
    public function start_timer() {
        if ( ! is_checkout() ) {

        }

        if ( ! WC()->session ) {
            return; // Ensure session is available
        }

        if ( ! WC()->session->get('event_order_id') ) {
            return;
        }
       
        if ( ! WC()->session->get('eventin_woo_payment_timer_expire') ) {
            $duration = (int)etn_get_option( 'ticket_purchase_timer', 10 );
            WC()->session->set('eventin_woo_payment_timer_expire', time() + ($duration * 60));
        }
    }

    /**
     * Displays the payment timer on the front end.
     *
     * @return  void
     */
    public function display_timer() {
        if ( ! is_checkout() ) return;

        if ( ! WC()->session->get('event_order_id') ) {
            return;
        }

        $expire_time = WC()->session->get('eventin_woo_payment_timer_expire');

        if ( ! $expire_time ) {
            return;
        }

        $time_left = max( 0, $expire_time - time() );

        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let container = document.querySelector('.wp-block-woocommerce-checkout');

                if ( ! container) {
                    container = document.querySelector('.woocommerce'); // classic WooCommerce
                }

                if ( ! container ) return;

                    const timerBox = document.createElement('div');
                    timerBox.id = 'payment-timer-box';
                    timerBox.style.cssText = 'padding: 15px; border: 1px solid #ccc; border-radius: 8px; font-weight: bold; background-color: #fff3cd; margin-bottom: 20px; text-align: center; color: #856404;';
                    timerBox.innerHTML = `Complete your payment within <span id="payment-timer">--:--</span> minutes to confirm your purchase.`;

                    container.insertBefore(timerBox, container.firstChild);


                    let is_payment_timer_enabled = "<?php echo esc_js( etn_get_option( 'ticket_purchase_timer_enable', 'off' ) ); ?>";

                    if( is_payment_timer_enabled == 'on'){
                        let initial_time = <?php echo absint( etn_get_option( 'ticket_purchase_timer', 10 ) * 60 ); ?>;

                    let timeLeft = <?php echo (int) $time_left; ?>;

                    const savedStart = localStorage.getItem( 'bookingStartTime' );
                    if ( savedStart ) {
                        const elapsed = Math.floor(
                            ( Date.now() - parseInt( savedStart, 10 ) ) / 1000
                        );
                        timeLeft = Math.max( initial_time - elapsed, 0 );
                    }


                    const display = document.getElementById("payment-timer");

                    const interval = setInterval(function() {
                        if (timeLeft <= 0) {
                            clearInterval(interval);
                            window.location.href = "<?php echo esc_url( wc_get_cart_url() ); ?>?eventin_payment_time_expired=1";
                            return;
                        }

                        const minutes = Math.floor(timeLeft / 60);
                        const seconds = timeLeft % 60;
                        display.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                        timeLeft--;
                    }, 1000);
                }
            });
        </script>
        <?php
    }

    /**
     * Empties the cart if the payment timer has expired.
     *
     * This function checks if the payment timer has expired and empties the cart if it has.
     *
     * @return void
     */
    public function empty_cart_after_timeout() {
        if ( function_exists( 'is_cart' ) && is_cart() 
            && WC()->session 
            && WC()->session->get('eventin_woo_payment_timer_expire') < time() 
        ) {
            WC()->session->set( 'eventin_woo_payment_timer_expire', null );
            WC()->session->__unset( 'eventin_woo_payment_timer_expire' );
        }

        $expired = isset( $_GET['eventin_payment_time_expired'] ) ? (int) $_GET['eventin_payment_time_expired'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- payment timer redirect param; value cast to integer via (int).

        if ( $expired ) {
            WC()->cart->empty_cart();

            // Reset payment timer.
            WC()->session->set( 'eventin_woo_payment_timer_expire', null );
            WC()->session->__unset( 'eventin_woo_payment_timer_expire' );

            // Reset event order id.
            WC()->session->set( 'event_order_id', null );
            WC()->session->__unset( 'event_order_id' );
        }
    }

    /**
     * Resets the payment timer when the order is completed.
     *
     * This function is called when an order is completed to reset the payment timer.
     *
     * @param int $order_id The ID of the completed order.
     */
    public function reset_payment_timer( $order_id ) {
        if ( ! WC()->session ) {
            return; // Ensure session is available
        }
        
        // Reset the payment timer when the order is completed
        if ( WC()->session->get('eventin_woo_payment_timer_expire') ) {

            WC()->session->set( 'eventin_woo_payment_timer_expire', null );
            WC()->session->__unset( 'eventin_woo_payment_timer_expire' );

            // Reset event order id.
            WC()->session->set( 'event_order_id', null );
            WC()->session->__unset( 'event_order_id' );
        }
    }

    /**
     * Update eventin order status on woocommerce order status change
     *
     * @return  void
     */
    public function reset_timer_on_order_status() {
        $statuses = etn_get_wc_order_statuses(); // e.g., ['processing', 'completed']

        foreach ( $statuses as $status ) {
            add_action( "woocommerce_order_status_{$status}", [ $this, 'reset_timer_on_order_status_changes' ],20 );
        }
    }

    /**
     * Reset payment timer on order status updated
     *
     * @return  void
     */
    public function reset_timer_on_order_status_changes() {
        if ( ! WC()->session ) {
            return; // Ensure session is available
        }
        
        // Reset the payment timer when the order is completed
        if ( WC()->session->get('eventin_woo_payment_timer_expire') ) {
            WC()->session->set( 'eventin_woo_payment_timer_expire', null );
            WC()->session->__unset( 'eventin_woo_payment_timer_expire' );

            // Reset event order id.
            // WC()->session->set( 'event_order_id', null );
            // WC()->session->__unset( 'event_order_id' );
        }
    }
}
