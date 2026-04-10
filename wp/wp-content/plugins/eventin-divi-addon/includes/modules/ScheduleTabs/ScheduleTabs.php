<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Etn\Utils\Helper;
use Eventin\Divi\Helper as DiviHelper;

class DIVI_ScheduleTabs extends ET_Builder_Module {

	public $slug       = 'eventin_schedule_tabs';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://product.themewinter.com/eventin-pro',
		'author'     => 'Themewinter',
		'author_uri' => 'https://themewinter.com/',
	);

	public function init() {
		$this->name = esc_html__( 'Eventin schedule Tab',  'eventin-divi-addon' );
	}

	public function get_icon( $icon_name = null ) {
        return plugin_dir_path( __FILE__ ) . 'icon.svg';
    }
    
	public function get_fields() {
		return array(
			'schedule_style'   => array(
				'label'            => esc_html__( 'Schedule Style',  'eventin-divi-addon' ),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'options'          => array(
					'schedule-1' => esc_html__( 'Schedule 1',  'eventin-divi-addon' ),
				),
				'toggle_slug'      => 'main_content',
				'description'      => esc_html__( 'Here you can choose the schedule Style',  'eventin-divi-addon' ),
			),
			'schedule_id'      => array(
				'label'       => esc_html__( 'Schedule',  'eventin-divi-addon' ),
				'type'        => 'multiple_checkboxes',
				'options'     => Helper::get_schedules(),
				'description' => esc_html__( 'Need to check atleast one schedule',  'eventin-divi-addon' ),
				'toggle_slug' => 'main_content',
			),	
			'etn_schedule_order'   => array(
				'label'            => esc_html__( 'Schedule Order',  'eventin-divi-addon' ),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'options'   => array(
					'DESC'        => esc_html__( 'Descending',  'eventin-divi-addon' ),
					'ASC'     => esc_html__( 'Ascending',  'eventin-divi-addon' ),
				),
				'toggle_slug'      => 'main_content',
				'description'      => esc_html__( 'Here you can choose the event order',  'eventin-divi-addon' ),
			),
			'__all_schedule' => array(
				'type' => 'computed',
				'computed_callback' => array( 'DIVI_ScheduleTabs', 'get_eventin_schedules' ),
				'computed_depends_on' => array(
						'schedule_style',
						'schedule_id',
						'etn_schedule_order'						
				),
				'computed_minimum' => array(
						'schedule_style',
						'schedule_id'
				),
			),
		);
	}

	public function render( $attrs, $content, $render_slug ) {
		$style = $this->props['schedule_style'];
		$schedule_id = $this->props['schedule_id'];
		$order = $this->props['etn_schedule_order'];

		$all_schedule = Helper::get_schedules();
		ksort( $all_schedule );
		$schedule_id = explode( '|', $schedule_id );

		$schedule_ids = divi_event_selected_terms( $all_schedule, $schedule_id );

		$shortcode_content = do_shortcode("[schedules order='{$order}' ids = {$schedule_ids}]");

		return sprintf($shortcode_content);
		
	}

	/**
	 * Get blog posts for blog module
	 *
	 * @param array   arguments that is being used by et_pb_custom_blog
	 * @return string blog post markup
	 */
	static function get_eventin_schedules( $agrs = array() ) {

		$style = $agrs['schedule_style'];
		$schedule_id = $agrs['schedule_id'];
		$order = $agrs['etn_schedule_order'];

		$all_schedule = Helper::get_schedules();
		ksort( $all_schedule );
		$schedule_id = explode( '|', $schedule_id );

		$schedule_ids = divi_event_selected_terms( $all_schedule, $schedule_id );

		ob_start();
		
		echo do_shortcode("[schedules order='{$order}' ids = {$schedule_ids}]");

		$posts = ob_get_contents();

		ob_end_clean();

		return $posts;

	}

}

new DIVI_ScheduleTabs;
