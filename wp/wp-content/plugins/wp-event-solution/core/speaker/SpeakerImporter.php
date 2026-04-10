<?php

/**
 * Speaker Importer Class
 *
 * @package Eventin
 */
namespace Eventin\Speaker;

defined( 'ABSPATH' ) || exit;

use Eventin\Importer\PostImporterInterface;
use Eventin\Importer\ReaderFactory;
use Etn\Core\Speaker\User_Model;

/**
 * Class Speaker Importer
 */
class SpeakerImporter implements PostImporterInterface {
    /**
     * Store File
     *
     * @var string
     */
    private $file;

    /**
     * Store data
     *
     * @var array
     */
    private $data;
    
    /**
     * Schedule import
     *
     * @param   array        $file         File params from request
     * @param   string|null  $forced_role  Force all imported rows to this role (etn-speaker or etn-organizer)
     * @return  void
     */
    public function import( $file, $forced_role = null ) {
        $this->file  = $file;
        $file_reader = ReaderFactory::get_reader( $file );

        if ( is_wp_error( $file_reader ) ) {
            return $file_reader;
        }

        $this->data = $file_reader->read_file();

        $this->create_speaker( $forced_role );
    }

    /**
     * Create schedule
     *
     * @param   string|null  $forced_role  Force all imported rows to this role
     * @return  void
     */
    private function create_speaker( $forced_role = null ) {
        $file_type  = ! empty( $this->file['type'] ) ? $this->file['type'] : '';
        $rows       = $this->data;

        foreach( $rows as $row ) {
            $speaker = new User_Model();
            $social = ! empty( $row['social'] ) ? $row['social'] : '';
            $group  = ! empty( $row['speaker_group'] ) ? $row['speaker_group'] : '';

            if ( 'text/csv' == $file_type ) {
                $social = json_decode( $social, true );
                $group  = json_decode( $group, true );
            }

	        // Force role if specified, otherwise validate role from data
	        if ( $forced_role && in_array( $forced_role, ['etn-speaker', 'etn-organizer'] ) ) {
		        $row['role'] = $forced_role;
	        } elseif ( ! in_array( $row['role'], ['etn-speaker', 'etn-organizer'] ) ) {
		        $row['role'] = 'etn-speaker';
	        }

            $args = [
                'first_name'                => ! empty( $row['name'] ) ? $row['name'] : '',
                'etn_speaker_website_email' => ! empty( $row['email'] ) ? $row['email'] : '',
                'image'                     => ! empty( $row['image'] ) ? $row['image'] : '',
                'etn_speaker_designation'   => ! empty( $row['designation'] ) ? $row['designation'] : '',
                'etn_speaker_summery'       => ! empty( $row['summary'] ) ? $row['summary'] : '',
                'etn_speaker_social'        => $social,
                'etn_speaker_company_logo'  => ! empty( $row['company_logo'] ) ? $row['company_logo'] : '',
                'etn_speaker_url'           => ! empty( $row['company_url'] ) ? $row['company_url'] : '',
                'etn_speaker_group'         => $group,
                'etn_speaker_category'      => $group,
                'etn_company_name'          => ! empty( $row['company_name'] ) ? $row['company_name'] : '',
                'author_url'                => ! empty( $row['author_url'] ) ? $row['author_url'] : '',
                'role'                      => ! empty( $row['role'] ) ? $row['role'] : '',
            ];

            $args['user_login'] = $row['email'];
    
            $speaker->create( $args );
        }
    }
}
