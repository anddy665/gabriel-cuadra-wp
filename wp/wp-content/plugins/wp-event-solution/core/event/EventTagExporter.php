<?php

/**
 * Event Tag Exporter Class
 *
 * @package Eventin
 */
namespace Eventin\Event;

defined( 'ABSPATH' ) || exit;

use Eventin\Exporter\ExporterFactory;
use Eventin\Exporter\PostExporterInterface;

/**
 * Class Event Tag Exporter
 *
 * Export Event Tag Data
 */
class EventTagExporter implements PostExporterInterface {
    /**
     * Store file name
     *
     * @var string
     */
    private $file_name = 'event-tag-data';

    /**
     * Store tag data
     *
     * @var array
     */
    private $data;

    /**
     * Store format
     *
     * @var string
     */
    private $format;

    /**
     * Taxonomy key
     *
     * @var string
     */
    private $taxonomy = 'etn_tags';

    /**
     * Export tag data
     *
     * @param array  $data   Tag IDs to export.
     * @param string $format Export format (csv or json).
     * @return void|\WP_Error
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
        } catch ( \Exception $e ) {
            return new \WP_Error( 'export_error', $e->getMessage(), [ 'status' => 409 ] );
        }
    }

    /**
     * Prepare data to export
     *
     * @return array
     */
    private function prepare_data() {
        $ids           = $this->data;
        $exported_data = [];

        foreach ( $ids as $id ) {
            $term = get_term( $id, $this->taxonomy );

            if ( ! $term || is_wp_error( $term ) ) {
                continue;
            }

            $tag_data = [
                'id'          => $term->term_id,
                'name'        => $term->name,
                'slug'        => $term->slug,
                'description' => $term->description,
                'parent'      => $term->parent,
                'count'       => $term->count,
            ];

            array_push( $exported_data, $tag_data );
        }

        return $exported_data;
    }

    /**
     * Get columns
     *
     * @return array
     */
    private function get_columns() {
        return [
            'id'          => esc_html__( 'Id', 'eventin' ),
            'name'        => esc_html__( 'Name', 'eventin' ),
            'slug'        => esc_html__( 'Slug', 'eventin' ),
            'description' => esc_html__( 'Description', 'eventin' ),
            'parent'      => esc_html__( 'Parent', 'eventin' ),
            'count'       => esc_html__( 'Count', 'eventin' ),
        ];
    }

    /**
     * Get all tag IDs
     *
     * @return array
     */
    public static function get_ids() {
        $terms = get_terms( [
            'taxonomy'   => 'etn_tags',
            'hide_empty' => false,
            'fields'     => 'ids',
        ] );

        if ( is_wp_error( $terms ) ) {
            return [];
        }

        return $terms;
    }
}
