<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Etn\Utils\Helper;
use Etn_Pro\Utils\Helper as UtilsHelper;

class DIVI_EventsCalendar extends ET_Builder_Module {

	public $slug       = 'eventin_events_calendar';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://product.themewinter.com/',
		'author'     => 'Themewinter',
		'author_uri' => 'https://themewinter.com/',
	);

	public function init() {
		$this->name = esc_html__( 'Event Calendar', 'eventin-divi-addon' );
	}

	public function get_icon( $icon_name = null ) {
        return plugin_dir_path( __FILE__ ) . 'icon.svg';
    }

	public function get_fields() {
		return array(
			'etn_event_style'   => array(
				'label'            => esc_html__( 'Style', 'eventin-divi-addon' ),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'options'          => array(
					'style-1' => esc_html__( 'Style 1', 'eventin-divi-addon' ),
					'style-2'  => esc_html__( 'Style 2', 'eventin-divi-addon' )
				),
				'toggle_slug'      => 'main_content',
				'description'      => esc_html__( 'Here you can choose the event style', 'eventin-divi-addon' ),
			),
			'etn_event_cat'      => array(
				'label'       => esc_html__( 'Event Category', 'eventin-divi-addon' ),
				'type'        => 'multiple_checkboxes',
				'options'     => Helper::get_event_category(),
				'description' => esc_html__( 'Leave checkboxes unchecked to select all', 'eventin-divi-addon' ),
				'toggle_slug' => 'main_content',
			),
			'etn_event_count'   => array(
				'default'         => 6,
				'label'           => esc_html__( 'Event count', 'eventin-divi-addon' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'description'     => esc_html__( 'Define the number of events that should be displayed per page.', 'eventin-divi-addon' ),
				'depends_show_if' => 'off',
				'toggle_slug'     => 'main_content',
			),
			'calendar_show'   => array(
				'label'            => esc_html__( 'Calendar Display', 'eventin-divi-addon' ),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'options'          => array(
					'left' => esc_html__( 'Left', 'eventin-divi-addon' ),
					'full_width'  => esc_html__( 'Full Width', 'eventin-divi-addon' ),
					'right'  => esc_html__( 'Right', 'eventin-divi-addon' )
				),
				'toggle_slug'      => 'main_content',
				'description'      => esc_html__( 'Here you can choose the calendar display', 'eventin-divi-addon' ),
			),
			'show_desc'        => array(
				'label'       => esc_html__( 'Show Description', 'eventin-divi-addon' ),
				'type'        => 'yes_no_button',
				'default'     => 'off',
				'options'     => array(
					'on'  => esc_html__( 'Yes', 'eventin-divi-addon' ),
					'off' => esc_html__( 'No', 'eventin-divi-addon' ),
				),
				'toggle_slug' => 'main_content',
			),
			'__all_events' => array(
				'type' => 'computed',
				'computed_callback' => array( 'DIVI_EventsCalendar', 'get_eventin_events' ),
				'computed_depends_on' => array(
						'etn_event_style',
						'etn_event_cat',
						'etn_event_count',
						'calendar_show',
						'show_desc'						
				),
				'computed_minimum' => array(
						'etn_event_count'
				),
			),
		);
	}

	public function render( $attrs, $content, $render_slug ) {
		$style = $this->props['etn_event_style'];
		$event_cat = $this->props['etn_event_cat'];
		$event_count = $this->props['etn_event_count'];
		$calendar_show = $this->props['calendar_show'];
		$show_desc = ($this->props['show_desc'] == 'on')? 'yes': 'no';

		$all_cat = Helper::get_event_category();
		ksort( $all_cat );
		$event_cat = explode( '|', $event_cat );

		$category_terms = divi_event_selected_terms( $all_cat, $event_cat );

		$shortcode_content = do_shortcode("[events_calendar style ='{$style}' event_cat_ids = '{$category_terms}' calendar_show='{$calendar_show}' show_desc='{$show_desc}' limit = '{$event_count}']");

		return sprintf($shortcode_content);
		
	}

	/**
	 * Get blog posts for blog module
	 *
	 * @param array   arguments that is being used by et_pb_custom_blog
	 * @return string blog post markup
	 */
	static function get_eventin_events( $agrs = array() ) {

		$style = $agrs['etn_event_style'];
		$event_cat = $agrs['etn_event_cat'];
		$event_count = $agrs['etn_event_count'];
		$calendar_show = $agrs['calendar_show'];
		$show_desc = ($agrs['show_desc'] == 'on')? 'yes': 'no';

		$all_cat = Helper::get_event_category();
		ksort( $all_cat );
		$event_cat = explode( '|', $event_cat );

		$category_terms = divi_event_selected_terms( $all_cat, $event_cat );

		ob_start();
		
		echo do_shortcode("[events_calendar style ='{$style}' event_cat_ids = '{$category_terms}' calendar_show='{$calendar_show}' show_desc='{$show_desc}' limit = '{$event_count}']");

		$posts = ob_get_contents();

		ob_end_clean();

		return $posts;

	}

}

new DIVI_EventsCalendar;
