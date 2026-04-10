<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Etn\Utils\Helper;
use Etn_Pro\Utils\Helper as UtilsHelper;

class DIVI_EventsCalendarpro extends ET_Builder_Module {

	public $slug       = 'eventin_events_calendarpro';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://product.themewinter.com/eventin-divi-addon',
		'author'     => 'Themewinter',
		'author_uri' => 'https://themewinter.com/',
	);

	public function init() {
		$this->name = esc_html__( 'Event Calendar Pro', 'eventin-divi-addon' );
	}

	public function get_icon( $icon_name = null ) {
        return plugin_dir_path( __FILE__ ) . 'icon.svg';
    }

	public function get_fields() {
		return array(
			'etn_event_cat'      => array(
				'label'       => esc_html__( 'Event Category', 'eventin-divi-addon' ),
				'type'        => 'multiple_checkboxes',
				'options'     => Helper::get_event_category(),
				'description' => esc_html__( 'Leave checkboxes unchecked to select all', 'eventin-divi-addon' ),
				'toggle_slug' => 'main_content',
			),
			'all_day_slot'        => array(
				'label'       => esc_html__( 'All Day Slot?', 'eventin-divi-addon' ),
				'type'        => 'yes_no_button',
				'default'     => 'on',
				'options'     => array(
					'on'  => esc_html__( 'Yes', 'eventin-divi-addon' ),
					'off' => esc_html__( 'No', 'eventin-divi-addon' ),
				),
				'toggle_slug' => 'main_content',
			),
			'calendar_view'   => array(
				'label'            => esc_html__( 'Calendar View', 'eventin-divi-addon' ),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'options'          => array(
					'dayGridMonth' => esc_html__( 'Monthly', 'eventin-divi-addon' ),
					'timeGridWeek'  => esc_html__( 'Weekly', 'eventin-divi-addon' ),
					'timeGridDay'  => esc_html__( 'Daily', 'eventin-divi-addon' ),
				),
				'toggle_slug'      => 'main_content',
				'description'      => esc_html__( 'Here you can choose the calendar view', 'eventin-divi-addon' ),
			),
			'__all_events' => array(
				'type' => 'computed',
				'computed_callback' => array( 'DIVI_EventsCalendarpro', 'get_eventin_events' ),
				'computed_depends_on' => array(
						'etn_event_cat',
						'all_day_slot',
						'calendar_view'						
				),
				'computed_minimum' => array(
						'calendar_view'
				),
			),
		);
	}

	public function render( $attrs, $content, $render_slug ) {

		$event_cat = $this->props['etn_event_cat'];
		$all_day_slot = ($this->props['all_day_slot'] == 'on')? 'yes': 'no';
		$calendar_view = $this->props['calendar_view'];

		$all_cat = Helper::get_event_category();
		ksort( $all_cat );
		$event_cat = explode( '|', $event_cat );

		$category_terms = divi_event_selected_terms( $all_cat, $event_cat );

		$shortcode_content = do_shortcode("[etn_pro_calendar_standard event_cat_ids = '{$category_terms}' all_day_slot='{$all_day_slot}' calendar_view='{$calendar_view}']");

		return sprintf($shortcode_content);
		
	}

	/**
	 * Get blog posts for blog module
	 *
	 * @param array   arguments that is being used by et_pb_custom_blog
	 * @return string blog post markup
	 */
	static function get_eventin_events( $agrs = array() ) {

		$event_cat = $agrs['etn_event_cat'];
		$all_day_slot = ($agrs['all_day_slot'] == 'on')? 'yes': 'no';
		$calendar_view = $agrs['calendar_view'];

		$all_cat = Helper::get_event_category();
		ksort( $all_cat );
		$event_cat = explode( '|', $event_cat );

		$category_terms = divi_event_selected_terms( $all_cat, $event_cat );

		ob_start();
		
		echo do_shortcode("[etn_pro_calendar_standard event_cat_ids = '{$category_terms}' all_day_slot='{$all_day_slot}' calendar_view='{$calendar_view}']");

		$posts = ob_get_contents();

		ob_end_clean();

		return $posts;

	}

}

new DIVI_EventsCalendarpro;
