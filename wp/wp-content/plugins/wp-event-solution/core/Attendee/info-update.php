<?php

namespace Etn\Core\Attendee;

use Etn\Utils\Helper;

defined( "ABSPATH" ) or die();

class InfoUpdate {

    use \Etn\Traits\Singleton;

    public function init() {
        add_action( 'wp_loaded', [$this, 'attendee_info_update_process']);
        add_action( 'wp_loaded', [$this, 'update_attendee_details']);
    }

    public function attendee_info_update_process() {
        if ( isset( $_GET['etn_action'] ) && sanitize_text_field( wp_unslash( $_GET['etn_action'] ) ) === 'edit_information' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- attendee info edit link; action value is compared to a literal string only.

            $get_arr     = filter_input_array( INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $attendee_id = ! empty( $get_arr['attendee_id'] ) ? intval( $get_arr['attendee_id'] ) : 0;
            $token       = ! empty( $get_arr['etn_info_edit_token'] ) ? $get_arr['etn_info_edit_token'] : '';

            if ( ! $attendee_id || ! $token || ! Helper::verify_attendee_edit_token( $attendee_id, $token ) ) {
                Helper::show_attendee_pdf_invalid_data_page();
                exit;
            }

            // Inject context before the module-purchase script executes.
            // Must run at wp_enqueue_scripts (priority 10) so the handle is
            // already registered (registration happens at priority 5).
            add_action( 'wp_enqueue_scripts', function() use ( $attendee_id, $token ) {
                wp_add_inline_script(
                    'etn-module-purchase',
                    sprintf(
                        'window._eventin_edit_attendee = %s; window.location.hash = "#/edit-attendee";',
                        wp_json_encode( [ 'attendee_id' => $attendee_id, 'token' => $token ] )
                    ),
                    'before'
                );
            }, 10 );

            // Render the React shell (enqueues etn-module-purchase and outputs #eventin-checkout div).
            if ( file_exists( \Wpeventin::templates_dir() . 'checkout-template.php' ) ) {
                include_once \Wpeventin::templates_dir() . 'checkout-template.php';
            }
            exit;
        }
        return;
    }

    public function update_attendee_details() {

        if ( isset( $_POST["etn_attendee_details_update_action"] ) && $_POST["etn_attendee_details_update_action"] == "etn_attendee_details_update_action" ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce field 'attendee_personal_data' is generated via wp_nonce_field('attendee_details_nonce') in the form template and verified via wp_verify_nonce() at the top of templates/attendee/update-attendee.php before any state-changing operations.
            $post_arr = filter_input_array( INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS );  

            // render template
            if( file_exists( \Wpeventin::templates_dir() . "attendee/update-attendee.php" ) ){
                include_once \Wpeventin::templates_dir() . "attendee/update-attendee.php";
            }
        }
    }

}
