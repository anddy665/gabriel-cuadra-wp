<?php
    use Etn\Utils\Helper;

    $single_event_id  = $event_id;
    $event_options    = get_option("etn_event_options");
    $has_child_events = Helper::get_child_events($single_event_id);

    if ($has_child_events) {
    $child_event_ids = [];

    if (is_array($has_child_events) && ! empty($has_child_events)) {

            foreach ($has_child_events as $single_child) {
                $end_date        = date_i18n("Y-m-d", strtotime(get_post_meta($single_child->ID, 'etn_end_date', true)));
                $current_date    = gmdate("Y-m-d");
                $settings        = etn_get_option();
                $hide_reccurance = ! empty($settings['hide_past_recurring_event_from_details']) ? $settings['hide_past_recurring_event_from_details'] : '';

            if ($hide_reccurance == 'on') {
                if ($end_date >= $current_date) {
                    array_push($child_event_ids, $single_child->ID);
                }
            } else {
                array_push($child_event_ids, $single_child->ID);
            }

        }
        ?>
	<div class="etn-single-event-ticket-wrap">
		<?php Helper::woocommerce_recurring_events_ticket_widget($single_event_id, $child_event_ids); ?>
	</div>

	<?php
        }
    }
