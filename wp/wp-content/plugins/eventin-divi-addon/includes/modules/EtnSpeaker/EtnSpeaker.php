<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Etn\Utils\Helper;

class DIVI_Speaker extends ET_Builder_Module
{

	public $slug       = 'eventin_speaker';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://product.themewinter.com/eventin',
		'author'     => 'Themewinter',
		'author_uri' => 'https://themewinter.com/',
	);

	public function init()
	{
		$this->name = esc_html__('Eventin Speaker',  'eventin-divi-addon');
	}

	public function get_icon( $icon_name = null ) {
        return plugin_dir_path( __FILE__ ) . 'icon.svg';
    }
	public function get_fields()
	{
		return array(
			'speaker_style'   => array(
				'label'            => esc_html__('Speaker Style',  'eventin-divi-addon'),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'default'         => 'speaker-1',
				'options'          => array(
					'speaker-1' => esc_html__('Speaker 1',  'eventin-divi-addon'),
					'speaker-2'  => esc_html__('Speaker 2',  'eventin-divi-addon'),
				),
				'toggle_slug'      => 'main_content',
				'description'      => esc_html__('Here you can choose the speaker style',  'eventin-divi-addon'),
			),
			'speakers_category'   => array(
				'label'       => esc_html__('Speaker Category',  'eventin-divi-addon'),
				'type'        => 'multiple_checkboxes',
				'options'     => Helper::get_speakers_category(),
				'description' => esc_html__('Leave checkboxes unchecked to select all',  'eventin-divi-addon'),
				'toggle_slug' => 'main_content',
			),
			'etn_speaker_count'   => array(
				'default'         => 6,
				'label'           => esc_html__('Speaker Count',  'eventin-divi-addon'),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'description'     => esc_html__('Define the number of speakers that should be displayed per page.',  'eventin-divi-addon'),
				'depends_show_if' => 'off',
				'toggle_slug'     => 'main_content',
			),
			'etn_speaker_col'   => array(
				'label'            => esc_html__('Speaker Column',  'eventin-divi-addon'),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'default'         => '3',
				'options'          => array(
					'4' => esc_html__('4 Column',  'eventin-divi-addon'),
					'3'  => esc_html__('3 Column',  'eventin-divi-addon'),
					'2'  => esc_html__('2 Column',  'eventin-divi-addon')
				),
				'toggle_slug'      => 'main_content',
				'description'      => esc_html__('Here you can choose the speaker column',  'eventin-divi-addon'),
			),
			'orderby'   => array(
				'label'            => esc_html__('Order Event By',  'eventin-divi-addon'),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'options'   => array(
					'ID'        => esc_html__('Id',  'eventin-divi-addon'),
					'title'     => esc_html__('Title',  'eventin-divi-addon'),
					'post_date' => esc_html__('Post Date',  'eventin-divi-addon'),
					'etn_start_date' => esc_html__('Event Start Date',  'eventin-divi-addon'),
					'etn_end_date' => esc_html__('Event End Date',  'eventin-divi-addon'),
				),
				'toggle_slug'      => 'main_content',
				'description'      => esc_html__('Here you can choose the event order by',  'eventin-divi-addon'),
			),
			'etn_speaker_order'   => array(
				'label'            => esc_html__('Speaker Order',  'eventin-divi-addon'),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'options'   => array(
					'DESC'        => esc_html__('Descending',  'eventin-divi-addon'),
					'ASC'     => esc_html__('Ascending',  'eventin-divi-addon'),
				),
				'toggle_slug'      => 'main_content',
				'description'      => esc_html__('Here you can choose the event order',  'eventin-divi-addon'),
			),
			'__all_speakers' => array(
				'type' => 'computed',
				'computed_callback' => array('DIVI_Speaker', 'get_eventin_speaker'),
				'computed_depends_on' => array(
					'speaker_style',
					'speakers_category',
					'etn_speaker_count',
					'etn_speaker_col',
					'orderby',
					'etn_speaker_order'

				),
				'computed_minimum' => array(
					'speaker_style',
				),
			),
		);
	}

	public function render($attrs, $content, $render_slug)
	{
		$style = $this->props['speaker_style'];
		$speaker_cat = $this->props['speakers_category'];
		$speaker_count = $this->props['etn_speaker_count'];
		$event_col = $this->props['etn_speaker_col'];
		$orderby = $this->props['orderby'];
		$order = $this->props['etn_speaker_order'];

		$all_speaker = Helper::get_speakers();
		ksort($all_speaker);

		$all_speaker_cat = Helper::get_speakers_category();
		ksort($all_speaker_cat);
		$speaker_cat = explode('|', $speaker_cat);

		$tag_terms = divi_event_selected_terms($all_speaker_cat, $speaker_cat);

		$shortcode_content = do_shortcode("[speakers style ='{$style}' cat_id = {$tag_terms} etn_speaker_col ='{$event_col}' order='{$order}' orderby='{$orderby}' limit = {$speaker_count}]");

		return sprintf($shortcode_content);
	}

	/**
	 * Get blog posts for blog module
	 *
	 * @param array   arguments that is being used by et_pb_custom_blog
	 * @return string blog post markup
	 */
	static function get_eventin_speaker($agrs = array())
	{

		$style = $agrs['speaker_style'];
		$speaker_cat = $agrs['speakers_category'];
		$speaker_count = $agrs['etn_speaker_count'];
		$event_col = $agrs['etn_speaker_col'];
		$orderby = $agrs['orderby'];
		$order = $agrs['etn_speaker_order'];

		$all_speaker = Helper::get_speakers();
		ksort($all_speaker);

		$all_speaker_cat = Helper::get_speakers_category();
		ksort($all_speaker_cat);
		$speaker_cat = explode('|', $speaker_cat);

		$tag_terms = divi_event_selected_terms($all_speaker_cat, $speaker_cat);

		ob_start();

		echo do_shortcode("[speakers style ='{$style}' cat_id = {$tag_terms} etn_speaker_col ='{$event_col}' order='{$order}' orderby='{$orderby}' limit = {$speaker_count}]");

		$posts = ob_get_contents();

		ob_end_clean();

		return $posts;
	}
}

new DIVI_Speaker;
