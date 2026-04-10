<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Etn\Utils\Helper;
use Etn_Pro\Utils\Helper as UtilsHelper;

class DIVI_EventsOnelinePro extends ET_Builder_Module
{

	public $slug       = 'eventin_events_oneline_pro';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://product.themewinter.com/eventin',
		'author'     => 'Themewinter',
		'author_uri' => 'https://themewinter.com/',
	);

	public function init()
	{
		$this->name = esc_html__('Events One line Pro', 'eventin-divi-addon');
	}

	public function get_icon( $icon_name = null ) {
        return plugin_dir_path( __FILE__ ) . 'icon.svg';
    }
    
	public function get_fields()
	{
		return array(
			'etn_event_cat'      => array(
				'label'       => esc_html__('Event Category', 'eventin-divi-addon'),
				'type'        => 'multiple_checkboxes',
				'options'     => Helper::get_event_category(),
				'description' => esc_html__('Leave checkboxes unchecked to select all', 'eventin-divi-addon'),
				'toggle_slug' => 'main_content',
			),
			'etn_event_tag'      => array(
				'label'       => esc_html__('Event Tag', 'eventin-divi-addon'),
				'type'        => 'multiple_checkboxes',
				'options'     => Helper::get_event_tag(),
				'description' => esc_html__('Leave checkboxes unchecked to select all', 'eventin-divi-addon'),
				'toggle_slug' => 'main_content',
			),
			'etn_event_count'   => array(
				'default'         => 6,
				'label'           => esc_html__('Event count', 'eventin-divi-addon'),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'description'     => esc_html__('Define the number of events that should be displayed per page.', 'eventin-divi-addon'),
				'depends_show_if' => 'off',
				'toggle_slug'     => 'main_content',
			),
			'filter_with_status'   => array(
				'label'            => esc_html__('Event status filter By', 'eventin-divi-addon'),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'options'          => array(
					'' 					=> esc_html__('All', 'eventin-divi-addon'),
					'upcoming'  => esc_html__('Upcoming Event', 'eventin-divi-addon'),
					'expire'  	=> esc_html__('Expire Event', 'eventin-divi-addon')
				),
				'toggle_slug'      => 'main_content',
				'description'      => esc_html__('Here you can choose the event filter', 'eventin-divi-addon'),
			),
			'orderby'   => array(
				'label'            => esc_html__('Order Event By', 'eventin-divi-addon'),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'options'   => array(
					'ID'        => esc_html__('Id', 'eventin-divi-addon'),
					'title'     => esc_html__('Title', 'eventin-divi-addon'),
					'post_date' => esc_html__('Post Date', 'eventin-divi-addon'),
					'etn_start_date' => esc_html__('Event Start Date', 'eventin-divi-addon'),
					'etn_end_date' => esc_html__('Event End Date', 'eventin-divi-addon'),
				),
				'toggle_slug'      => 'main_content',
				'description'      => esc_html__('Here you can choose the event order by', 'eventin-divi-addon'),
			),
			'order'   => array(
				'label'            => esc_html__('Event Order', 'eventin-divi-addon'),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'options'   => array(
					'DESC'        => esc_html__('Descending', 'eventin-divi-addon'),
					'ASC'     => esc_html__('Ascending', 'eventin-divi-addon'),
				),
				'toggle_slug'      => 'main_content',
				'description'      => esc_html__('Here you can choose the event order', 'eventin-divi-addon'),
			),
			'show_btn' => array(
				'label'       => esc_html__('Show Button', 'eventin-divi-addon'),
				'type'        => 'yes_no_button',
				'default'     => 'off',
				'options'     => array(
					'on'  => esc_html__('Yes', 'eventin-divi-addon'),
					'off' => esc_html__('No', 'eventin-divi-addon'),
				),
				'toggle_slug' => 'main_content',
			),
			'show_event_location' => array(
				'label'       => esc_html__('Show Event Location', 'eventin-divi-addon'),
				'type'        => 'yes_no_button',
				'default'     => 'off',
				'options'     => array(
					'on'  => esc_html__('Yes', 'eventin-divi-addon'),
					'off' => esc_html__('No', 'eventin-divi-addon'),
				),
				'toggle_slug' => 'main_content',
			),
			'show_child_event' => array(
				'label'       => esc_html__('Show Child Event', 'eventin-divi-addon'),
				'type'        => 'yes_no_button',
				'default'     => 'off',
				'options'     => array(
					'on'  => esc_html__('Yes', 'eventin-divi-addon'),
					'off' => esc_html__('No', 'eventin-divi-addon'),
				),
				'toggle_slug' => 'main_content',
			),
			'show_parent_event' => array(
				'label'       => esc_html__('Show Parent Event', 'eventin-divi-addon'),
				'type'        => 'yes_no_button',
				'default'     => 'off',
				'options'     => array(
					'on'  => esc_html__('Yes', 'eventin-divi-addon'),
					'off' => esc_html__('No', 'eventin-divi-addon'),
				),
				'toggle_slug' => 'main_content',
			),
			'show_end_date' => array(
				'label'       => esc_html__('Show end Date', 'eventin-divi-addon'),
				'type'        => 'yes_no_button',
				'default'     => 'off',
				'options'     => array(
					'on'  => esc_html__('Yes', 'eventin-divi-addon'),
					'off' => esc_html__('No', 'eventin-divi-addon'),
				),
				'toggle_slug' => 'main_content',
			),
			'show_thumb' => array(
				'label'       => esc_html__('Show Thumbnail', 'eventin-divi-addon'),
				'type'        => 'yes_no_button',
				'default'     => 'off',
				'options'     => array(
					'on'  => esc_html__('Yes', 'eventin-divi-addon'),
					'off' => esc_html__('No', 'eventin-divi-addon'),
				),
				'toggle_slug' => 'main_content',
			),
			'show_category' => array(
				'label'       => esc_html__('Show Category', 'eventin-divi-addon'),
				'type'        => 'yes_no_button',
				'default'     => 'off',
				'options'     => array(
					'on'  => esc_html__('Yes', 'eventin-divi-addon'),
					'off' => esc_html__('No', 'eventin-divi-addon'),
				),
				'toggle_slug' => 'main_content',
			),
			'__all_events' => array(
				'type' => 'computed',
				'computed_callback' => array('DIVI_EventsOnelinePro', 'get_eventin_events'),
				'computed_depends_on' => array(
					'etn_event_cat',
					'etn_event_tag',
					'etn_event_count',
					'filter_with_status',
					'orderby',
					'order',
					'show_btn',
					'show_event_location',
					'show_parent_event',
					'show_child_event',
					'show_end_date',
					'show_thumb',
					'show_category'

				),
				'computed_minimum' => array(
					'etn_event_count',
					'etn_event_cat'
				),
			),
		);
	}

	public function render($attrs, $content, $render_slug)
	{
		$event_cat = $this->props['etn_event_cat'];
		$event_tag = $this->props['etn_event_tag'];
		$event_count = $this->props['etn_event_count'];
		$filter_status = $this->props['filter_with_status'];
		$orderby = $this->props['orderby'];
		$order = $this->props['order'];
		$show_btn = ($this->props['show_btn'] == 'on') ? 'yes' : 'no';
		$show_event_location = ($this->props['show_event_location'] == 'on') ? 'yes' : 'no';
		$show_parent_event = ($this->props['show_parent_event'] == 'on') ? 'yes' : 'no';
		$show_child_event = ($this->props['show_child_event'] == 'on') ? 'yes' : 'no';
		$show_end_date = ($this->props['show_end_date'] == 'on') ? 'yes' : 'no';
		$show_thumb = ($this->props['show_thumb'] == 'on') ? 'yes' : 'no';
		$show_category = ($this->props['show_category'] == 'on') ? 'yes' : 'no';

		$all_cat = Helper::get_event_category();
		ksort($all_cat);
		$event_cat = explode('|', $event_cat);

		$all_tags = Helper::get_event_tag();
		ksort($all_tags);
		$event_tag = explode('|', $event_tag);

		$category_terms = divi_event_selected_terms($all_cat, $event_cat);
		$tag_terms = divi_event_selected_terms($all_tags, $event_tag);

		$shortcode_content =  do_shortcode("[etn_pro_events_one_line event_cat_ids='{$category_terms}' event_tag_ids='{$tag_terms}' orderby='{$orderby}' order='{$order}' filter_with_status='{$filter_status}'  show_btn='{$show_btn}' show_thumb='{$show_thumb}' show_end_date='{$show_end_date}' show_child_event='{$show_child_event}' show_parent_event='{$show_parent_event}' show_event_location='{$show_event_location}' show_category='{$show_category}' event_count = '{$event_count}']");

		return sprintf($shortcode_content);
	}

	/**
	 * Get blog posts for blog module
	 *
	 * @param array   arguments that is being used by et_pb_custom_blog
	 * @return string blog post markup
	 */
	static function get_eventin_events($agrs = array())
	{

		$event_cat = $agrs['etn_event_cat'];
		$event_tag = $agrs['etn_event_tag'];
		$event_count = $agrs['etn_event_count'];
		$filter_status = $agrs['filter_with_status'];
		$orderby = $agrs['orderby'];
		$order = $agrs['order'];
		$show_btn = ($agrs['show_btn'] == 'on') ? 'yes' : 'no';
		$show_event_location = ($agrs['show_event_location'] == 'on') ? 'yes' : 'no';
		$show_parent_event = ($agrs['show_parent_event'] == 'on') ? 'yes' : 'no';
		$show_child_event = ($agrs['show_child_event'] == 'on') ? 'yes' : 'no';
		$show_end_date = ($agrs['show_end_date'] == 'on') ? 'yes' : 'no';
		$show_thumb = ($agrs['show_thumb'] == 'on') ? 'yes' : 'no';
		$show_category = ($agrs['show_category'] == 'on') ? 'yes' : 'no';

		$all_cat = Helper::get_event_category();
		ksort($all_cat);
		$event_cat = explode('|', $event_cat);

		$all_tags = Helper::get_event_tag();
		ksort($all_tags);
		$event_tag = explode('|', $event_tag);

		$category_terms = divi_event_selected_terms($all_cat, $event_cat);
		$tag_terms = divi_event_selected_terms($all_tags, $event_tag);

		ob_start();

		echo do_shortcode("[etn_pro_events_one_line event_cat_ids = '{$category_terms}' event_tag_ids = '{$tag_terms}' orderby='{$orderby}' order='{$order}' filter_with_status='{$filter_status}' show_btn='{$show_btn}' show_thumb='{$show_thumb}' show_end_date='{$show_end_date}' show_child_event='{$show_child_event}' show_parent_event='{$show_parent_event}' show_event_location='{$show_event_location}' show_category='{$show_category}' event_count = '{$event_count}']");
		$posts = ob_get_contents();

		ob_end_clean();

		return $posts;
	}
}

new DIVI_EventsOnelinePro;
