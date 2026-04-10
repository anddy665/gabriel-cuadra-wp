<?php
	/**
	 * Updater for version 4.0.10
	 *
	 * @package Eventin\Upgrade
	 */
	
	namespace Eventin\Upgrade\Upgraders;
	
	use Etn\Core\Attendee\Attendee_Model;
	use Etn\Core\Event\Event_Model;
	use Etn\Core\Schedule\Schedule_Model;
	use Etn\Core\Speaker\User_Model;
	use Eventin\Order\OrderModel;
	use Eventin\Settings;
	
	/**
	 * Updater class for v4.0.29
	 *
	 * @since 4.0.9
	 */
	class V_4_0_29 implements UpdateInterface {
		/**
		 * Run the updater
		 *
		 * @return  void
		 */
		public function run() {
			$etn_does_demo_data_exits = Settings::get("etn_does_demo_data_exits");
			$etn_is_migrated = Settings::get("etn_is_migrated");
			
			if ( $etn_is_migrated ) {
				return;
			}
			if ( $etn_does_demo_data_exits ) {
				return;
			}
			
			Settings::update([ "etn_does_demo_data_exits" => true ]);
			
			$current_user = wp_get_current_user();
			$current_user_id = $current_user->ID;
			
			if ( is_user_logged_in() ) {
				$current_user->add_role('etn-speaker'); // Replace 'editor' with your target role slug
			}
			
			try {
				$schedule1 = $this->seed_schedule([
					'etn_schedule_title' => '(Demo) SEO & Content Marketing Strategies',
					'post_title' => 'SEO & Content Marketing Strategies',
					'etn_schedule_date' => '2025-06-15',
					'etn_schedule_topics' => [
						[
							'etn_schedule_topic' => 'Advanced SEO Techniques for 2025',
							'etn_shedule_start_time' => '10:00 AM',
							'etn_shedule_end_time' => '11:30 AM',
							'speakers' => [101], // Replace with actual speaker ID
							'etn_shedule_room' => 'Main Hall A',
							'etn_shedule_objective' => 'Learn the latest Google algorithm updates and how to optimize content for better search visibility.',
						],
						[
							'etn_schedule_topic' => 'Data-Driven Content Strategy',
							'etn_shedule_start_time' => '11:45 AM',
							'etn_shedule_end_time' => '1:00 PM',
							'speakers' => [102], // Replace with actual speaker ID
							'etn_shedule_room' => 'Main Hall A',
							'etn_shedule_objective' => 'Explore how analytics can guide content creation and improve ROI on marketing campaigns.',
						]
					],
					'post_status' => 'publish',
				]);
				
				$schedule2 = $this->seed_schedule([
					'etn_schedule_title' => '(Demo) Emerging Technologies & Innovation Trends',
					'post_title' => 'Emerging Technologies & Innovation Trends',
					'etn_schedule_date' => '2025-09-12',
					'etn_schedule_topics' => [
						[
							'etn_schedule_topic' => 'The Future of AI in Enterprise Systems',
							'etn_shedule_start_time' => '09:30 AM',
							'etn_shedule_end_time' => '11:00 AM',
							'speakers' => [$current_user_id], // Replace with actual speaker ID
							'etn_shedule_room' => 'Innovation Stage A',
							'etn_shedule_objective' => 'Understand how artificial intelligence is reshaping enterprise decision-making, automation, and customer engagement.',
						],
						[
							'etn_schedule_topic' => 'Blockchain Beyond Cryptocurrency',
							'etn_shedule_start_time' => '11:15 AM',
							'etn_shedule_end_time' => '12:45 PM',
							'speakers' => [$current_user_id], // Replace with actual speaker ID
							'etn_shedule_room' => 'Innovation Stage A',
							'etn_shedule_objective' => 'Explore real-world applications of blockchain in supply chains, identity management, and digital security.',
						],
						[
							'etn_schedule_topic' => 'Tech Startup Showcase: Disruptive Ideas 2025',
							'etn_shedule_start_time' => '02:00 PM',
							'etn_shedule_end_time' => '03:30 PM',
							'speakers' => [$current_user_id], // Replace with actual speaker IDs
							'etn_shedule_room' => 'Main Auditorium',
							'etn_shedule_objective' => 'Meet founders of groundbreaking startups and hear pitch-style presentations on how they are solving today’s toughest tech problems.',
						]
					],
					'post_status' => 'publish',
				]);
				
				$attachment = $this->insert_image_to_media_library();
				$this->seed_event([
					'post_title' => '(Demo) Digital Marketing Summit 2025',
					'post_content' => '<p>Join industry leaders and digital strategists at the Digital Marketing Summit 2025 to explore the latest trends, tools, and techniques in SEO, social media marketing, content strategy, and analytics. Network with professionals and attend hands-on workshops to elevate your digital marketing skills.</p>',
					'post_excerpt' => '',
					'excerpt_enable' => '',
					'enable_seatmap' => '',
					'etn_select_speaker_schedule_type' => '',
					'etn_event_organizer' => [],
					'etn_event_speaker' => [$current_user_id],
					'event_timezone' => 'Asia/Dhaka',
					'etn_start_date' => '2025-06-14',
					'etn_end_date' => '2025-07-01',
					'etn_start_time' => '12:00 AM',
					'etn_end_time' => '11:55 PM',
					'etn_ticket_availability' => '',
					'etn_ticket_variations' => [
						[
							'etn_ticket_name' => 'Early Bird',
							'etn_avaiilable_tickets' => 32323,
							'etn_ticket_price' => 0,
							'date_range' => 'Invalid Date',
							'start_time' => '12:00 AM',
							'end_time' => '11:55 PM',
							'etn_ticket_slug' => 'ticket-145-dfgs-fdg-7673',
							'etn_sold_tickets' => 0,
							'etn_enable_ticket' => 1,
							'start_date' => '2025-05-01',
							'end_date' => '2025-05-30',
						]
					],
					'etn_event_logo' => '',
					'event_logo_id' => '',
					'event_banner_id' => $attachment["attach_id"],
					'etn_event_calendar_text_color' => '#382626',
					'etn_event_calendar_bg' => '#301c1c',
					'etn_registration_deadline' => '',
					'attende_page_link' => '',
					'etn_zoom_id' => '',
					'etn_event_location_type' => '',
					'etn_event_location' => [
						'address' => 'new York',
					],
					'location' => [
						'address' => 'new York',
					],
					'etn_zoom_event' => '',
					'etn_total_avaiilable_tickets' => 32323,
					'etn_google_meet' => '',
					'etn_google_meet_short_description' => '',
					'fluent_crm' => 'no',
					'etn_event_socials' => [
						[
							'icon' => 'etn-icon fa-facebook-f',
							'etn_social_url' => 'https://www.gp.com',
						]
					],
					'etn_event_schedule' => [$schedule1],
					'categories' => [ ],
					'tags' => [ ],
					'etn_event_faq' => [
						[
							'etn_faq_title' => 'Who should attend the Digital Marketing Summit 2025?',
							'etn_faq_content' => 'The summit is ideal for marketing professionals, business owners, digital strategists, SEO specialists, content creators, and anyone looking to stay ahead in the rapidly evolving world of digital marketing.'
						]
					],
					'attendee_extra_fields' => [],
					'speaker_type' => 'group',
					'speaker_group' => [ ],
					'organizer_type' => 'group',
					'organizer_group' => [],
					'fluent_crm_webhook' => '',
					'recurring_enabled' => 'no',
					'etn_event_recurrence' => [
						'recurrence_custom' => []
					],
					'rsvp_settings' => '',
					'seat_plan' => '',
					'ticket_template' => 'style-1',
					'certificate_template' => '',
					'external_link' => '',
					'event_banner' => $attachment["image_url"],
					'event_layout' => 'event-one',
					'post_status' => 'draft',
					'event_type' => 'offline',
					'_virtual' => '',
					'_tax_status' => 'none',
					'enable_legacy_certificate_template' => '',
				]);
				
				$this->seed_event([
					'post_title' => '(Demo) Annual Tech Innovators Conference  ',
					'post_content' => '<p>The Tech Innovators Conference brings together developers, entrepreneurs, and investors to explore cutting-edge innovations in AI, blockchain, and software development. Engage in keynote sessions, panel discussions, and networking opportunities across three impactful days.</p>',
					'post_excerpt' => '',
					'excerpt_enable' => '',
					'enable_seatmap' => '',
					'etn_select_speaker_schedule_type' => '',
					'etn_event_organizer' => [],
					'etn_event_speaker' => [$current_user_id],
					'event_timezone' => 'Asia/Dhaka',
					'etn_start_date' => '2025-06-14',
					'etn_end_date' => '2025-07-01',
					'etn_start_time' => '12:00 AM',
					'etn_end_time' => '11:55 PM',
					'etn_ticket_availability' => '',
					'etn_ticket_variations' => [
						[
							'etn_ticket_name' => 'VIP',
							'etn_avaiilable_tickets' => 32323,
							'etn_ticket_price' => 0,
							'date_range' => 'Invalid Date',
							'start_time' => '12:00 AM',
							'end_time' => '11:55 PM',
							'etn_ticket_slug' => 'ticket-145-dfgs-fdg-7673',
							'etn_sold_tickets' => 0,
							'etn_enable_ticket' => 1,
							'start_date' => '2025-05-01',
							'end_date' => '2025-05-30',
						]
					],
					'etn_event_logo' => '',
					'event_logo_id' => '',
					'event_banner_id' => $attachment["attach_id"],
					'etn_event_calendar_text_color' => '#382626',
					'etn_event_calendar_bg' => '#301c1c',
					'etn_registration_deadline' => '',
					'etn_zoom_id' => '',
					'etn_event_location_type' => '',
					'etn_event_location' => [
						'address' => 'New York',
					],
					'location' => [
						'address' => 'New York',
					],
					'etn_zoom_event' => '',
					'etn_total_avaiilable_tickets' => 32323,
					'etn_google_meet' => '',
					'etn_google_meet_short_description' => '',
					'fluent_crm' => 'no',
					'etn_event_socials' => [
						[
							'icon' => 'etn-icon fa-facebook-f',
							'etn_social_url' => 'https://www.gp.com',
						]
					],
					'etn_event_schedule' => [$schedule2],
					'categories' => [ ],
					'tags' => [ ],
					'etn_event_faq' => [
						[
							'etn_faq_title' => 'Who should attend the Digital Marketing Summit 2025?',
							'etn_faq_content' => 'The summit is ideal for marketing professionals, business owners, digital strategists, SEO specialists, content creators, and anyone looking to stay ahead in the rapidly evolving world of digital marketing.'
						]
					],
					'attendee_extra_fields' => [],
					'speaker_type' => 'group',
					'speaker_group' => [ ],
					'organizer_type' => 'group',
					'organizer_group' => [],
					'fluent_crm_webhook' => '',
					'recurring_enabled' => 'no',
					'etn_event_recurrence' => [
						'recurrence_custom' => []
					],
					'rsvp_settings' => '',
					'seat_plan' => '',
					'ticket_template' => 'style-1',
					'certificate_template' => '',
					'event_banner' => $attachment["image_url"],
					'event_layout' => 'event-one',
					'post_status' => 'draft',
					'event_type' => 'offline',
					'_virtual' => '',
					'_tax_status' => 'none',
					'enable_legacy_certificate_template' => '',
				]);
			} catch (\Exception $e) {
			
			}
		}
		
		/**
		 * seeds a single schedule
		 *
		 * @param $prepared_data
		 * @return void
		 */
		public function seed_schedule($prepared_data)
		{
			$schedule = new Schedule_Model();
			return $schedule->create_and_return_post_id($prepared_data);
		}
		
		/**
		 * seeds a single event
		 *
		 * @param array $array
		 * @return void
		 */
		private function seed_event(array $array)
		{
			$event = new Event_Model();
			$event_id = $event->create_and_return_post_id($array);
			
			return $event_id;
		}
		
		function insert_image_to_media_library()
		{
			// Path to image in plugin
			$source_path = plugin_dir_path(__FILE__) . '../../../assets/images/default-event-banner.webp';
			
			// Ensure the file exists
			if (!file_exists($source_path)) {
				return;
			}
			
			// Get WordPress uploads directory
			$upload_dir = wp_upload_dir();
			$target_dir = $upload_dir['path'];
			$target_url = $upload_dir['url'];
			
			// Set destination file name
			$filename = 'logo.webp';
			$target_path = $target_dir . '/' . $filename;
			
			// Copy the file
			if (!copy($source_path, $target_path)) {
				
				return;
			}
			
			// Get file type
			$filetype = wp_check_filetype($filename, null);
			
			// Prepare attachment data
			$attachment = [
				'guid' => $target_url . '/' . $filename,
				'post_mime_type' => $filetype['type'],
				'post_title' => sanitize_file_name($filename),
				'post_content' => '',
				'post_status' => 'inherit',
			];
			
			// Insert attachment
			$attach_id = wp_insert_attachment($attachment, $target_path);
			
			// Include image functions
			require_once ABSPATH . 'wp-admin/includes/image.php';
			
			// Generate metadata and update DB
			$attach_data = wp_generate_attachment_metadata($attach_id, $target_path);
			wp_update_attachment_metadata($attach_id, $attach_data);
			
			// You now have the attachment ID and can get the URL
			$image_url = wp_get_attachment_url($attach_id);
			return [
				"image_url" => $image_url,
				"attach_id" => $attach_id
			];
		}
		
		/**
		 * seeds user and speaker role
		 *
		 * @param $input_data
		 * @return void
		 */
		public function seed_speaker($input_data): void
		{
			$speaker_model = new User_Model();
			
			
			$prepared_data = [];
			
			if (!empty($input_data['name'])) {
				$prepared_data['etn_speaker_title'] = sanitize_text_field($input_data['name']);
				$prepared_data['display_name'] = sanitize_text_field($input_data['name']);
			}
			if (!empty($input_data['designation'])) {
				$prepared_data['etn_speaker_designation'] = sanitize_text_field($input_data['designation']);
			}
			if (!empty($input_data['company_name'])) {
				$prepared_data['etn_company_name'] = sanitize_text_field($input_data['company_name']);
			}
			if (!empty($input_data['email'])) {
				$prepared_data['etn_speaker_website_email'] = sanitize_email($input_data['email']);
				$prepared_data['user_login'] = sanitize_email($input_data['email']);
			}
			
			$prepared_data['etn_speaker_social'] = is_array($input_data['social']) ? $input_data['social'] : [];
			
			if (!empty($input_data['category']) && is_array($input_data['category'])) {
				$allowed_categories = ['speaker', 'organizer'];
				$filtered_categories = array_intersect($input_data['category'], $allowed_categories);
				
				if (!empty($filtered_categories)) {
					$prepared_data['etn_speaker_category'] = array_values($filtered_categories);
				}
			}
			
			$prepared_data['date'] = !empty($input_data['date']) ? $input_data['date'] : gmdate("Y-m-d H:i:s");
			
			// Create the speaker
			$created = $speaker_model->create($prepared_data);
		}
		
		/**
		 * seeds user and organizer role
		 *
		 * @param $input_data
		 * @return void
		 */
		public function seed_organizer($input_data): void
		{
			$speaker_model = new User_Model();
			
			$prepared_data = [];
			
			if (!empty($input_data['name'])) {
				$prepared_data['etn_speaker_title'] = sanitize_text_field($input_data['name']);
				$prepared_data['display_name'] = sanitize_text_field($input_data['name']);
			}
			if (!empty($input_data['designation'])) {
				$prepared_data['etn_speaker_designation'] = sanitize_text_field($input_data['designation']);
			}
			if (!empty($input_data['company_name'])) {
				$prepared_data['etn_company_name'] = sanitize_text_field($input_data['company_name']);
			}
			
			if (!empty($input_data['email'])) {
				$prepared_data['etn_speaker_website_email'] = sanitize_email($input_data['email']);
				$prepared_data['user_login'] = sanitize_email($input_data['email']);
			}
			
			$prepared_data['etn_speaker_social'] = is_array($input_data['social']) ? $input_data['social'] : [];
			
			if (!empty($input_data['category']) && is_array($input_data['category'])) {
				$allowed_categories = ['speaker', 'organizer'];
				$filtered_categories = array_intersect($input_data['category'], $allowed_categories);
				
				if (!empty($filtered_categories)) {
					$prepared_data['etn_speaker_category'] = array_values($filtered_categories);
				}
			}
			
			$prepared_data['date'] = !empty($input_data['date']) ? $input_data['date'] : gmdate("Y-m-d H:i:s");
			
			// Create the speaker
			$created = $speaker_model->create($prepared_data);
			
		}
		
		/**
		 * @return void
		 */
		public function seed_category(array $prepared_data)
		{
			$category = wp_insert_term($prepared_data['name'], 'etn_category', $prepared_data);
			
			return $category;
		}
		
		private function update_event($event_id, $args)
		{
			$event = new Event_Model($event_id);
			$event->update($args);
		}
		
	}
