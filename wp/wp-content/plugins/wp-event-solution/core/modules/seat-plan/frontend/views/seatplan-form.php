<?php

namespace Etn\Core\Modules\Seat_Plan\Frontend\Views;

use Etn\Core\Event\Event_Model;

defined( 'ABSPATH' ) || die();

class Seatplan_Form {

	use \Etn\Traits\Singleton;

	/**
	 * Call js/css files
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'etn_after_single_event_details_rsvp_form', array( $this, 'seat_plan_form' ), 10 );
		add_action( 'etn_after_add_to_cart_widget_block', array( $this, 'seat_plan_form' ), 10 );
	}

	/**
	 * Enqueue scripts.
	 */
	public function seat_plan_form() {
		$event_id = get_the_ID();
		$event = new Event_Model( $event_id );



		$start_date = $event->etn_start_date;
        $start_time = $event->etn_start_time;
        $end_date   = $event->etn_end_date;
        $end_time   = $event->etn_end_time;
        $status     = get_post_status( $event_id );
        $timezone   = $event->event_timezone ? etn_create_date_timezone( $event->event_timezone ) : 'Asia/Dhaka';

        $start_date_time = $start_date . ' ' . $start_time;
        $end_date_time   = $end_date . ' ' . $end_time;

        // Create a DateTime object for the start date and time in the given timezone
        $start_date = new \DateTime( $start_date_time, new \DateTimeZone( $timezone ) );
        $end_date   = new \DateTime( $end_date_time, new \DateTimeZone( $timezone ) );
    
        // Create a DateTime object for the current date and time in the given timezone
        $current_date = new \DateTime('now', new \DateTimeZone( $timezone ) );



		$is_event_ongoing = $current_date <= $end_date;

		if ( ! $event->is_enable_seatmap()) {
			return;
		}

		if ( !$is_event_ongoing ) {
			// Only show the notice in the main content area (etn_after_single_event_details_rsvp_form)
			if (current_filter() === 'etn_after_single_event_details_rsvp_form') {
				?>
				<div class="etn-event-notice etn-event-expired-notice" style="background: #fff8f8; border-left: 4px solid #dc3232; box-shadow: 0 1px 1px rgba(0,0,0,0.04); margin: 5px 0 15px; padding: 12px 20px; display: flex; align-items: center; border-radius: 4px;">
					<div class="etn-notice-icon" style="margin-right: 15px; color: #dc3232;">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<circle cx="12" cy="12" r="10"></circle>
							<line x1="12" y1="8" x2="12" y2="12"></line>
							<line x1="12" y1="16" x2="12.01" y2="16"></line>
						</svg>
					</div>
					<div class="etn-notice-content">
						<h3 style="margin: 0.5em 0 0.2em; color: #1d2327; font-size: 16px; font-weight: 600;"><?php echo esc_html__('Event Period Has Ended', 'eventin'); ?></h3>
					</div>
				</div>
				<?php
			}
			return;
		}

		$errors = isset( $_GET['etn_errors'] ) ? sanitize_text_field( wp_unslash( $_GET['etn_errors'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display param for seat plan error messages; no data modified.
		remove_query_arg( 'etn_errors', get_the_permalink(get_the_ID()) );
		$seats = get_post_meta( get_the_ID(),'seat_plan', true );


		// Early return if $seats is empty
		if (empty($seats)) {
			return;
		}
	 
		?>
		<form method="POST">
			<?php  wp_nonce_field('ticket_purchase_next_step_two','ticket_purchase_next_step_two'); ?>
			<?php if ( ! empty( $errors['seat_limit_error'] ) ): ?>
				<p style="text-align: center; color: red"><?php echo esc_html( $errors['seat_limit_error'] ); ?></p>
			<?php endif; ?>
			<div class="wrap-seat-plan-form timetics-shortcode-wrapper">
				<div id="etn-seat-plan" data-id="<?php echo intval(get_the_ID()); ?>"></div>
				<input name="event_name" type="hidden" value="<?php echo esc_html(get_the_title(get_the_ID())); ?>"/>
			</div>
		</form>

		<?php
	}
}
