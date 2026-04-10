<?php

/**
 * Category Importer Class
 *
 * @package Eventin
 */
namespace Eventin\Event;

defined( 'ABSPATH' ) || exit;

use Eventin\Importer\PostImporterInterface;
use Eventin\Importer\ReaderFactory;

/**
 * Class Category Importer
 */
class CategoryImporter implements PostImporterInterface {
    /**
     * Store File
     *
     * @var array
     */
    private $file;

    /**
     * Store data
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
     * Category import
     *
     * @param array $file Uploaded file data
     *
     * @return void
     */
    public function import( $file ) {
        $this->file  = $file;
        $file_reader = ReaderFactory::get_reader( $file );

        if ( is_wp_error( $file_reader ) ) {
            return $file_reader;
        }

        $this->data = $file_reader->read_file();
        $this->create_categories();
    }

    /**
     * Create categories
     *
     * @return void
     */
    private function create_categories() {
        $rows = $this->data;

        // First pass: Create all categories without parent relationships
        $id_mapping = [];

        foreach ( $rows as $row ) {
            $name        = ! empty( $row['name'] ) ? sanitize_text_field( $row['name'] ) : '';
            $slug        = ! empty( $row['slug'] ) ? sanitize_title( $row['slug'] ) : '';
            $description = ! empty( $row['description'] ) ? sanitize_textarea_field( $row['description'] ) : '';
            $color       = ! empty( $row['color'] ) ? sanitize_hex_color( $row['color'] ) : '';
            $old_id      = ! empty( $row['id'] ) ? intval( $row['id'] ) : 0;

            if ( empty( $name ) ) {
                continue;
            }

            // Check if term already exists
            $existing_term = term_exists( $name, $this->taxonomy );

            if ( $existing_term ) {
                $term_id = $existing_term['term_id'];

                // Update existing term
                wp_update_term( $term_id, $this->taxonomy, [
                    'slug'        => $slug,
                    'description' => $description,
                ] );
            } else {
                // Create new term
                $term = wp_insert_term( $name, $this->taxonomy, [
                    'slug'        => $slug,
                    'description' => $description,
                ] );

                if ( is_wp_error( $term ) ) {
                    continue;
                }

                $term_id = $term['term_id'];
            }

            // Update color meta
            if ( $color ) {
                update_term_meta( $term_id, 'color', $color );
            }

            // Store mapping of old ID to new ID
            if ( $old_id ) {
                $id_mapping[ $old_id ] = $term_id;
            }
        }

        // Second pass: Update parent relationships
        foreach ( $rows as $row ) {
            $name      = ! empty( $row['name'] ) ? sanitize_text_field( $row['name'] ) : '';
            $parent_id = ! empty( $row['parent'] ) ? intval( $row['parent'] ) : 0;

            if ( empty( $name ) || empty( $parent_id ) ) {
                continue;
            }

            $term = get_term_by( 'name', $name, $this->taxonomy );

            if ( ! $term || is_wp_error( $term ) ) {
                continue;
            }

            // Map old parent ID to new parent ID
            $new_parent_id = isset( $id_mapping[ $parent_id ] ) ? $id_mapping[ $parent_id ] : $parent_id;

            // Verify parent exists
            $parent_term = get_term( $new_parent_id, $this->taxonomy );

            if ( ! $parent_term || is_wp_error( $parent_term ) ) {
                continue;
            }

            // Update term with parent
            wp_update_term( $term->term_id, $this->taxonomy, [
                'parent' => $new_parent_id,
            ] );
        }
    }
}
