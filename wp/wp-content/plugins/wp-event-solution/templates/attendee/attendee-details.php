<?php 
defined( 'ABSPATH' ) || exit;
?>
<section class="woocommerce-order-details">
	<h2 class="woocommerce-order-details__title"><?php esc_html_e( 'Attendee details', 'eventin' ); ?></h2>
	<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
		<thead>
			<tr>
				<th class="woocommerce-table__product-name attendee-name"><?php esc_html_e( 'Name', 'eventin' ); ?></th>
                <?php if ( $include_email ) { ?>
				<th class="woocommerce-table__product-table attendee-email"><?php esc_html_e( 'Email', 'eventin' ); ?></th>
                <?php } ?>
                <?php if ( $include_phone ) { ?>
                <th class="woocommerce-table__product-table attendee-phone"><?php esc_html_e( 'Phone', 'eventin' ); ?></th>
                <?php } ?>
                <th class="woocommerce-table__product-table attendee-event"><?php esc_html_e( 'Event', 'eventin' ); ?></th>
                <th class="woocommerce-table__product-table attendee-ticket-status"><?php esc_html_e( 'Ticket Status', 'eventin' ); ?></th>
                <th class="woocommerce-table__product-table attendee-action"><?php esc_html_e( 'Action', 'eventin' ); ?></th>
			</tr>
		</thead>

		<tbody>
        <?php 
        foreach( $attendees as $attendee){
            $new_ticket_download_link = $ticket_download_link;
            $new_edit_information_link = $edit_information_link;
            $attendee_id           = $attendee->ID;
            $attendee_name         = get_post_meta( $attendee_id, 'etn_name', true );
            $etn_email             = get_post_meta( $attendee_id, 'etn_email', true );
            $etn_phone             = get_post_meta( $attendee_id, 'etn_phone', true );
            $ticket_status         = get_post_meta( $attendee_id, 'etn_attendeee_ticket_status', true );

            $ticket_status_arr = [
                'used'    => esc_html__('Used', 'eventin'),
                'unused'    => esc_html__('Unused', 'eventin')
            ];

            $edit_token            = get_post_meta( $attendee_id, 'etn_info_edit_token', true );
            $new_ticket_download_link  .= urlencode($attendee_id) . "&etn_info_edit_token=" . urlencode ( $edit_token );
            $new_edit_information_link .= urlencode( $attendee_id ) . "&etn_info_edit_token=" . urlencode( $edit_token );
            
            // Get event information
            $event_id = get_post_meta( $attendee_id, 'etn_event_id', true );
            $event_name = '';
            $event_start_date = '';
            
            if ( $event_id ) {
                $event_name = get_post_field( 'post_title', $event_id, 'raw' );
                $start_date = get_post_meta( $event_id, 'etn_start_date', true );
                
                if ( $start_date ) {
                    $settings = etn_get_option();
                    $date_options = get_option( 'date_format' );
                    $etn_settings_date_format = ! empty( $settings["date_format"] ) ? $date_options[$settings["date_format"]] : get_option( "date_format" );
                    $date_format = !empty($settings['date_format']) ? $etn_settings_date_format : get_option("date_format");
                    $event_start_date = date_i18n($date_format, strtotime($start_date));
                }
            }
            
            $event_display = $event_name;
            if ( $event_start_date ) {
                $event_display .= '<br/>(' . $event_start_date . ')';
            }
            ?>
            <tr>
                <td><?php echo esc_html( $attendee_name ); ?></td>
                <?php if ( $include_email ) { ?>
                    <td><?php echo esc_html( $etn_email ); ?></td>
                <?php } ?>
                <?php if ( $include_phone ) { ?>
                    <td><?php echo esc_html( $etn_phone ); ?></td>
                <?php } ?>
                <td><?php echo wp_kses_post( $event_display ); ?></td>
                <td><?php echo esc_html( $ticket_status_arr[$ticket_status] ); ?></td>
                <td>
                    <div class=''>
                        <a class='' target='_blank' href='<?php echo esc_url( $new_edit_information_link ); ?>' rel='noopener'><?php echo esc_html__('Edit', 'eventin'); ?></a> | 
                        <a class='' target='_blank' href='<?php echo esc_url( $new_ticket_download_link ); ?>' rel='noopener'><?php echo esc_html__('Download Ticket', 'eventin'); ?></a>
                    </div>
                </td>
            </tr>
            <?php
        }
        ?>
		</tbody>
	</table>

</section>
