<?php

/**
 * Event Tag Importer Class
 *
 * @package Eventin
 */
namespace Eventin\Event;

defined( 'ABSPATH' ) || exit;

use Eventin\Importer\PostImporterInterface;
use Eventin\Importer\ReaderFactory;

/**
 * Class Event Tag Importer
 */
class EventTagImporter implements PostImporterInterface {
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
    private $taxonomy = 'etn_tags';

    /**
     * Tag import
     *
     * @param array $file File data.
     * @return void
     */
    public function import( $file ) {
        $this->file  = $file;
        $file_reader = ReaderFactory::get_reader( $file );

        if ( is_wp_error( $file_reader ) ) {
            return $file_reader;
        }

        $this->data = $file_reader->read_file();

        $this->create_tags();
    }

    /**
     * Create tags from imported data
     *
     * @return void
     */
    private function create_tags() {
        $rows = $this->data;

        foreach ( $rows as $row ) {
            $name        = ! empty( $row['name'] ) ? sanitize_text_field( $row['name'] ) : '';
            $slug        = ! empty( $row['slug'] ) ? sanitize_title( $row['slug'] ) : '';
            $description = ! empty( $row['description'] ) ? sanitize_textarea_field( $row['description'] ) : '';
            $parent      = ! empty( $row['parent'] ) ? absint( $row['parent'] ) : 0;

            if ( empty( $name ) ) {
                continue;
            }

            // Check if tag already exists by slug.
            $existing_term = get_term_by( 'slug', $slug, $this->taxonomy );

            if ( $existing_term ) {
                // Update existing tag.
                wp_update_term( $existing_term->term_id, $this->taxonomy, [
                    'name'        => $name,
                    'description' => $description,
                    'parent'      => $parent,
                ] );
            } else {
                // Create new tag.
                $args = [
                    'description' => $description,
                    'parent'      => $parent,
                ];

                if ( ! empty( $slug ) ) {
                    $args['slug'] = $slug;
                }

                wp_insert_term( $name, $this->taxonomy, $args );
            }
        }
    }
}
