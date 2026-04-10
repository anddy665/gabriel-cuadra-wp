<?php

/**
 * Attendee Importer Class
 *
 * @package Eventin
 */

namespace Eventin\Attendee;

defined('ABSPATH') || exit;

use Eventin\Importer\PostImporterInterface;
use Eventin\Importer\ReaderFactory;
use Etn\Core\Attendee\Attendee_Model;
use EventinPro\modules\rsvp\RsvpService;

/**
 * Class Attendee Importer
 */
class RsvpImporter implements PostImporterInterface
{

	private $rsvpService;
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

	public function __construct()
	{
		$this->rsvpService = new RsvpService();
	}

	/**
	 * Attendee import
	 *
	 * @return  void
	 */
	public function import($file)
	{
		$this->file = $file;
		$file_reader = ReaderFactory::get_reader($file);

		if (is_wp_error($file_reader)) {
			return $file_reader;
		}

		$this->data = $file_reader->read_file();

		$this->create_rsvps();
	}

	/**
	 * Create Attendee
	 *
	 * @return  void
	 */
	private function create_rsvps()
	{
		$file_type = !empty($this->file['type']) ? $this->file['type'] : '';

		$rows = $this->data;
		foreach ($rows as $row) {

			$args = [
				'attendee_name' => !empty($row['attendee_name']) ? sanitize_text_field($row['attendee_name']) : '',
				'attendee_email' => !empty($row['attendee_email']) ? sanitize_text_field($row['attendee_email']) : '',
				'received_on' => !empty($row['received_on']) ? sanitize_text_field($row['received_on']) : '',
				'guests' => ! empty($row['guest']) ? $row['guest'] : [],
				'event_id' => !empty($row['event_id']) ? intval(sanitize_text_field($row['event_id'])) : '',
				'status' => !empty($row['status']) ? sanitize_text_field($row['status']) : '',
				'number_of_attendee' => !empty($row['attendees']) ? sanitize_text_field($row['attendees']) : '',
			];

			$guests = $row["guests"];
			if ("text/csv" === $file_type) {
				$tmp = array_map(function ($entry) {
					$parts = explode(',', $entry);
					$assoc = [];
					foreach ($parts as $part) {
						list($key, $value) = explode(':', $part, 2);
						$assoc[trim($key)] = trim($value);
					}
					return $assoc;
				}, explode('|', $guests));

				$args["guests"] = $tmp;
			}

			$this
				->rsvpService
				->createRsvp($args);
		}
	}

	/**
	 * Get extra fields value
	 *
	 * @param array $row
	 *
	 * @return  array
	 */
	private function get_extra_field_data($row)
	{
		$event_id = !empty($row['event_id']) ? intval($row['event_id']) : 0;
		$extra_fields = get_post_meta($event_id, 'attendee_extra_fields', true);
		$settings = etn_get_option();
		$data = [];
		$file_type = !empty($this->file['type']) ? $this->file['type'] : '';

		if (!$extra_fields) {
			$extra_fields = !empty($settings['attendee_extra_fields']) ? $settings['attendee_extra_fields'] : [];
		}

		if ($extra_fields) {
			foreach ($extra_fields as $value) {
				$column = strtolower(str_replace([' ', '-'], '_', $value['label']));
				$meta_key = 'etn_attendee_extra_field_' . $column;

				if ('application/json' === $file_type) {
					$meta_value = !empty($row[$meta_key]) ? $row[$meta_key] : '';
				} else {
					$meta_value = !empty($row[$column]) ? $row[$column] : '';
				}

				$data[$meta_key] = $meta_value;
			}
		}

		return $data;
	}

	/**
	 * Updated extra field
	 *
	 * @param integer $attendee_id
	 *
	 * @return  void
	 */
	private function update_extra_fields($attendee_id, $fields)
	{

		if ($fields) {
			foreach ($fields as $key => $value) {
				update_post_meta($attendee_id, $key, $value);
			}
		}
	}
}
