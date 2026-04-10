<?php

/**
 * Attendee Exporter Class
 *
 * @package Eventin
 */
namespace Eventin\Order;

defined( 'ABSPATH' ) || exit;

use Eventin\Exporter\ExporterFactory;
use Eventin\Exporter\PostExporterInterface;
use Exception;
use WP_Error;

/**
 * Class Order Exporter
 *
 * Export Order Data
 */
class OrderExporter implements PostExporterInterface {
    /**
     * Store file name
     *
     * @var string
     */
    private $file_name = 'order-data';

    /**
     * Store attendee data
     *
     * @var array
     */
    private $data;

    /**
     * Store file format
     *
     * @var string
     */
    private $format;
    
    /**
     * Export attendee data
     *
     * @return void
     */
    public function export( $data, $format ) {
        $this->data   = $data;
        $this->format = $format;

        $rows      = $this->prepare_data();
        $columns   = $this->get_columns();
        $file_name = $this->file_name;

        try {
            $exporter = ExporterFactory::get_exporter( $format );

            $exporter->export( $rows, $columns, $file_name );
        } catch(Exception $e) {
            return new WP_Error( 'export_error', $e->getMessage(), ['status' => 409] );
        }

        
    }

    /**
     * Prepare data to export
     *
     * @return  array
     */
    private function prepare_data() {
        $ids           = $this->data;
        $exported_data = [];

        foreach ( $ids as $id ) {
            $order = new OrderModel( $id );

            $tickets    = $order->get_tickets();
            $attendees  = $order->get_attendees();

            if ( 'csv' === $this->format ) {
                $tickets   = json_encode( $tickets );
                $attendees = json_encode( $attendees );
            }

            $total_tax = floatval(get_post_meta( $order->id, 'tax_total', true ));
            $total_discount = floatval(get_post_meta( $order->id, 'discount_total', true ));
            $tax_display_mode = get_post_meta( $order->id, 'tax_display_mode', true );
            $extra_fields = json_encode(etn_safe_decode(get_post_meta( $order->id, 'extra_fields', true )));
            
            $final_total = $order->total_price - $total_discount;

            if ( $tax_display_mode != 'incl' ) {
                $final_total = $final_total + $total_tax;
            }

            $order_data = [
                'id'                => $order->id,
                'customer_fname'    => $order->customer_fname,
                'customer_lname'    => $order->customer_lname,
                'customer_email'    => $order->customer_email,
                'customer_phone'    => $order->customer_phone,
                'date_time'         => $order->date_time,
                'event_id'          => $order->event_id,
                'payment_method'    => $order->payment_method,
                'status'            => $order->status,
                'tax_mode'          => $tax_display_mode,
                'diccount'          => $total_discount,
                'tax'               => $total_tax,
                'total_price'       => $order->total_price,
                'final_price'       => $final_total,
                'ticket_items'      => $tickets,
                'extra_fields'      => $extra_fields,
                'attendees'         => $attendees,
            ];
            
            array_push( $exported_data, $order_data );
        }

        return $exported_data;
    }

    /**
     * Get columns
     *
     * @return  array
     */
    private function get_columns() {
        
        $columns = [
            'id'                 => __( 'Id', 'eventin' ),
            'customer_fname'     => __( 'Customer Fname', 'eventin' ),
            'customer_lname'     => __( 'Customer Lname', 'eventin' ),
            'customer_email'     => __( 'Customer Email', 'eventin' ),
            'customer_phone'     => __( 'Phone', 'eventin' ),
            'date_time'          => __( 'Date Time', 'eventin' ),
            'event_id'           => __( 'Event ID', 'eventin' ),
            'payment_method'     => __( 'Payment Method', 'eventin' ),
            'status'             => __( 'Status', 'eventin' ),
            'tax_mode'           => __( 'Tax Mode', 'eventin' ),
            'tax'                => __( 'Tax', 'eventin' ),
            'diccount'           => __( 'Diccount', 'eventin' ),
            'total_price'        => __( 'Total Price', 'eventin' ),
            'final_price'        => __( 'Final Price', 'eventin' ),
            'ticket_items'       => __( 'Ticket Items', 'eventin' ),
            'attendees'          => __( 'Attendees', 'eventin' ),
            'extra_fields'       => __( 'Extra Fields', 'eventin' ),
        ];

        return $columns;
    }
}
