<?php

namespace Etn\Core\Event;

defined( 'ABSPATH' ) || exit;
use Eventin\Utils\Notice\Notice;

/**
* Manage eventin dependencies
*/
class DependencyControls {

    /**
     * Register all hooks
     *
     * @return  void 
     */
    public function __construct() {

        // archive search filter.
        add_filter( 'pre_get_posts', '\Etn\Utils\Helper::event_etn_search_filter', 999999 );
        add_action( 'wp_ajax_etn_event_ajax_get_data', '\Etn\Utils\Helper::etn_event_ajax_get_data' );
        add_action( 'wp_ajax_nopriv_etn_event_ajax_get_data', '\Etn\Utils\Helper::etn_event_ajax_get_data' );

        // archive pagination filter.
        add_filter( 'pre_get_posts', '\Etn\Utils\Helper::etn_event_archive_pagination_per_page' );

        // Bricks theme compatibility
        $theme = wp_get_theme(); // gets the current theme
        if ( ! empty( $theme ) && ( 'Bricks' == $theme->name || 'Bricks' == $theme->parent_theme ) ) {
            add_filter( 'language_attributes', [ $this, 'add_class_in_html_bricks' ], 10, 2 );

        }
        $this->handle_woo_dependency();
    }

    // Bricks theme compatibility
    public function add_class_in_html_bricks( $output, $doctype ) {
        if ( 'html' !== $doctype ) {
            return $output;
        }
        $output .= ' class="no-js no-svg bricks_parent"';

        return $output;
    }

    /**
     * Handle woocommerce admin notice depending on settings
     *
     * @return void
     */
    public function handle_woo_dependency() {

        $eventin_global_settings = \Etn\Utils\Helper::get_settings();
   
        $sell_tickets            = ('woocommerce' == etn_get_option('payment_method') ) ? true : false;

        if ( $sell_tickets && ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            add_action( 'admin_head', array( $this, 'admin_notice_wc_not_active' ) );

            return;
        }
    } 

    /**
     * Show notice if woocommerce not active
     */
    public function admin_notice_wc_not_active() {

        if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- standard WP plugin activation redirect check; no data read or stored.
            unset( $_GET['activate'] );
        }
        $btn = array(
            'default_class' => 'button',
            'class'         => 'button-primary ',
        );
        if ( file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) {
            $btn['text'] = esc_html__( 'Activate WooCommerce', 'eventin' );
            $btn['url']  = wp_nonce_url( 'plugins.php?action=activate&plugin=woocommerce/woocommerce.php&plugin_status=all&paged=1', 'activate-plugin_woocommerce/woocommerce.php' );
        } else {
            $btn['text'] = esc_html__( 'Install WooCommerce', 'eventin' );
            $btn['url']  = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ), 'install-plugin_woocommerce' );
        }

        Notice::instance( 'eventin', 'unsupported-woocommerce-version' )
        ->set_class( 'error' )
        ->set_dismiss( 'global', ( 3600 * 24 * 30 ) )
        ->set_message( sprintf( esc_html__( 'To enable payments and transactions, WooCommerce is required. Eventin uses WooCommerce to handle bookings, payments, and order management.', 'eventin' ) ) )
        ->set_button( $btn )
        ->call();
    }   

}
