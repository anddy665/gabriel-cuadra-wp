<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Etn\Utils\Helper;
 
class DIVI_EventsTabs extends ET_Builder_Module {

	public $slug       = 'eventin_events_tabs';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://product.themewinter.com/eventin',
		'author'     => 'Themewinter',
		'author_uri' => 'https://themewinter.com/',
	);

	public function init() {
		$this->name = esc_html__( 'Eventin Events Tabs',  'eventin-divi-addon' );
	}

	public function get_icon( $icon_name = null ) {
        return plugin_dir_path( __FILE__ ) . 'icon.svg';
    }

	public function get_fields() {
		return array(
			'etn_event_style'   => array(
				'label'            => esc_html__( 'Event Style',  'eventin-divi-addon' ),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'options'          => array(
					'event-1' => esc_html__( 'Event 1',  'eventin-divi-addon' ),
					'event-2'  => esc_html__( 'Event 2',  'eventin-divi-addon' ),
				),
				'toggle_slug'      => 'main_content',
				'description'      => esc_html__( 'Here you can choose the event style',  'eventin-divi-addon' ),
			),
			'etn_event_cat'      => array(
				'label'       => esc_html__( 'Event Category',  'eventin-divi-addon' ),
				'type'        => 'multiple_checkboxes',
				'options'     => Helper::get_event_category(),
				'description' => esc_html__( 'Leave checkboxes unchecked to select all',  'eventin-divi-addon' ),
				'toggle_slug' => 'main_content',
			),
			'etn_event_tag'      => array(
				'label'       => esc_html__( 'Event Tag',  'eventin-divi-addon' ),
				'type'        => 'multiple_checkboxes',
				'options'     => Helper::get_event_tag(),
				'description' => esc_html__( 'Leave checkboxes unchecked to select all',  'eventin-divi-addon' ),
				'toggle_slug' => 'main_content',
			),
			'etn_event_count'   => array(
				'default'         => 6,
				'label'           => esc_html__( 'Event count',  'eventin-divi-addon' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'description'     => esc_html__( 'Define the number of events that should be displayed per page.',  'eventin-divi-addon' ),
				'depends_show_if' => 'off',
				'toggle_slug'     => 'main_content',
			),
			'etn_desc_limit'   => array(
				'default'         => 20,
				'label'           => esc_html__( 'Description Limit',  'eventin-divi-addon' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'description'     => esc_html__( 'Description limit.',  'eventin-divi-addon' ),
				'depends_show_if' => 'off',
				'toggle_slug'     => 'main_content',
			),
			'etn_event_col'   => array(
				'label'            => esc_html__( 'Event column',  'eventin-divi-addon' ),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'options'          => array(
					'3' => esc_html__( '3 Column',  'eventin-divi-addon' ),
					'4'  => esc_html__( '4 Column',  'eventin-divi-addon' ),
					'2'  => esc_html__( '2 Column',  'eventin-divi-addon' )
				),
				'toggle_slug'      => 'main_content',
				'description'      => esc_html__( 'Here you can choose the event column',  'eventin-divi-addon' ),
			),
			'filter_with_status'   => array(
				'label'            => esc_html__( 'Event status filter By',  'eventin-divi-addon' ),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'options'          => array(
					'' 					=> esc_html__( 'All',  'eventin-divi-addon' ),
					'upcoming'  => esc_html__( 'Upcoming Event',  'eventin-divi-addon' ),
					'expire'  	=> esc_html__( 'Expire Event',  'eventin-divi-addon' )
				),
				'toggle_slug'      => 'main_content',
				'description'      => esc_html__( 'Here you can choose the event filter',  'eventin-divi-addon' ),
			),
			'orderby'   => array(
				'label'            => esc_html__( 'Order Event By',  'eventin-divi-addon' ),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'options'   => array(
					'ID'        => esc_html__( 'Id',  'eventin-divi-addon' ),
					'title'     => esc_html__( 'Title',  'eventin-divi-addon' ),
					'post_date' => esc_html__( 'Post Date',  'eventin-divi-addon' ),
					'etn_start_date' => esc_html__( 'Event Start Date',  'eventin-divi-addon' ),
					'etn_end_date' => esc_html__( 'Event End Date',  'eventin-divi-addon' ),
				),
				'toggle_slug'      => 'main_content',
				'description'      => esc_html__( 'Here you can choose the event order by',  'eventin-divi-addon' ),
			),
			'order'   => array(
				'label'            => esc_html__( 'Event Order',  'eventin-divi-addon' ),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'options'   => array(
					'DESC'        => esc_html__( 'Descending',  'eventin-divi-addon' ),
					'ASC'     => esc_html__( 'Ascending',  'eventin-divi-addon' ),
				),
				'toggle_slug'      => 'main_content',
				'description'      => esc_html__( 'Here you can choose the event order',  'eventin-divi-addon' ),
			),
			'__all_events' => array(
				'type' => 'computed',
				'computed_callback' => array( 'DIVI_EventsTabs', 'get_eventin_events' ),
				'computed_depends_on' => array(
						'etn_event_style',
						'etn_event_cat',
						'etn_event_tag',
						'etn_event_count',
						'etn_desc_limit',
						'etn_event_col',
						'filter_with_status',
						'orderby',
						'order'
						
				),
				'computed_minimum' => array(
						'etn_event_count',
						'etn_event_cat'
				),
			),
		);
	}

	public function render( $attrs, $content, $render_slug ) {
		$style = $this->props['etn_event_style'];
		$event_cat = $this->props['etn_event_cat'];
		$event_tag = $this->props['etn_event_tag'];
		$event_count = $this->props['etn_event_count'];
		$event_col = $this->props['etn_event_col'];
		$filter_status = $this->props['filter_with_status'];
		$orderby = $this->props['orderby'];
		$order = $this->props['order'];
		$etn_desc_limit = $this->props['etn_desc_limit'];

		$all_cat = Helper::get_event_category();
		ksort( $all_cat );
		$event_cat = explode( '|', $event_cat );

		$all_tags = Helper::get_event_tag();
		ksort( $all_tags );
		$event_tag = explode( '|', $event_tag );

		$category_terms = divi_event_selected_terms( $all_cat, $event_cat );
		$tag_terms = divi_event_selected_terms( $all_tags, $event_tag );

		$shortcode_content = do_shortcode("[events_tab style ='{$style}' event_cat_ids = '{$category_terms}' event_tag_ids = '{$tag_terms}' order='{$order}' orderby='{$orderby}' filter_with_status='{$filter_status}' etn_event_col ='{$event_col}' limit = '{$event_count}' desc_limit='{$etn_desc_limit}']");

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
		$event_tag = $agrs['etn_event_tag'];
		$event_count = $agrs['etn_event_count'];
		$event_col = $agrs['etn_event_col'];
		$filter_status = $agrs['filter_with_status'];
		$orderby = $agrs['orderby'];
		$order = $agrs['order'];
		$etn_desc_limit = $agrs['etn_desc_limit'];

		$all_cat = Helper::get_event_category();
		ksort( $all_cat );
		$event_cat = explode( '|', $event_cat );

		$all_tags = Helper::get_event_tag();
		ksort( $all_tags );
		$event_tag = explode( '|', $event_tag );

		$category_terms = divi_event_selected_terms( $all_cat, $event_cat );
		$tag_terms = divi_event_selected_terms( $all_tags, $event_tag );

		ob_start();
		
		echo do_shortcode("[events_tab style ='{$style}' event_cat_ids = '{$category_terms}' event_tag_ids = '{$tag_terms}' order='{$order}' orderby='{$orderby}' filter_with_status='{$filter_status}' etn_event_col ='{$event_col}' limit = '{$event_count}' desc_limit='{$etn_desc_limit}']");

		$posts = ob_get_contents();

		ob_end_clean();

		return $posts;

	}

}

new DIVI_EventsTabs;
