<?php

/**
 * Category Exporter Class
 *
 * @package Eventin
 */
namespace Eventin\Event;

defined( 'ABSPATH' ) || exit;

use Eventin\Exporter\ExporterFactory;
use Eventin\Exporter\PostExporterInterface;

/**
 * Class Category Exporter
 *
 * Export Category Data
 */
class CategoryExporter implements PostExporterInterface {
    /**
     * Store file name
     *
     * @var string
     */
    private $file_name = 'category-data';

    /**
     * Store category data
     *
     * @var array
     */
    private $data;

    /**
     * Taxonomy key
     *
     * @var string
     */
    private $taxonomy = 'etn_category';

    /**
     * Export category data
     *
     * @param array  $data   Category IDs to export
     * @param string $format Export format (csv, json)
     *
     * @return void
     */
    public function export( $data, $format ) {
        $this->data = $data;

        $rows      = $this->prepare_data();
        $columns   = $this->get_columns();
        $file_name = $this->file_name;

        $exporter = ExporterFactory::get_exporter( $format );

        $exporter->export( $rows, $columns, $file_name );
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

            if ( is_wp_error( $term ) || ! $term ) {
                continue;
            }

            $parent_name = '';
            if ( $term->parent ) {
                $parent_term = get_term( $term->parent, $this->taxonomy );
                if ( ! is_wp_error( $parent_term ) && $parent_term ) {
                    $parent_name = $parent_term->name;
                }
            }

            $category_data = [
                'id'          => $term->term_id,
                'name'        => $term->name,
                'slug'        => $term->slug,
                'description' => $term->description,
                'parent'      => $term->parent,
                'parent_name' => $parent_name,
                'color'       => get_term_meta( $term->term_id, 'color', true ),
                'count'       => $term->count,
            ];

            array_push( $exported_data, $category_data );
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
            'id'          => __( 'ID', 'eventin' ),
            'name'        => __( 'Name', 'eventin' ),
            'slug'        => __( 'Slug', 'eventin' ),
            'description' => __( 'Description', 'eventin' ),
            'parent'      => __( 'Parent ID', 'eventin' ),
            'parent_name' => __( 'Parent Name', 'eventin' ),
            'color'       => __( 'Color', 'eventin' ),
            'count'       => __( 'Event Count', 'eventin' ),
        ];
    }

    /**
     * Get all category IDs
     *
     * @return array
     */
    public static function get_ids() {
        $terms = get_terms( [
            'taxonomy'   => 'etn_category',
            'hide_empty' => false,
            'fields'     => 'ids',
        ] );

        return ! is_wp_error( $terms ) ? $terms : [];
    }
}
