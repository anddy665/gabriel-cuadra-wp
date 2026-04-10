<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


use Etn\Utils\Helper;
use Etn_Pro\Utils\Helper as UtilsHelper;

class DIVI_Speakerslider extends ET_Builder_Module
{

	public $slug       = 'eventin_speaker_slider';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://product.themewinter.com/eventin',
		'author'     => 'Themewinter',
		'author_uri' => 'https://themewinter.com/',
	);

	public function init()
	{
		$this->name = esc_html__('Eventin Speaker Slider', 'eventin-divi-addon');
	}

	public function get_icon( $icon_name = null ) {
        return plugin_dir_path( __FILE__ ) . 'icon.svg';
    }
    
	public function get_fields()
	{
		return array(
			'speaker_style'   => array(
				'label'            => esc_html__('Speaker Style', 'eventin-divi-addon'),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'default'          => 'style-1',
				'options'          => array(
					'style-1' => esc_html__('Speaker 1', 'eventin-divi-addon'),
					'style-2'  => esc_html__('Speaker 2', 'eventin-divi-addon'),
					'style-3'  => esc_html__('Speaker 3', 'eventin-divi-addon'),
					'style-4'  => esc_html__('Speaker 4', 'eventin-divi-addon'),
					'style-5'  => esc_html__('Speaker 5', 'eventin-divi-addon'),
				),
				'toggle_slug'      => 'main_content',
				'description'      => esc_html__('Here you can choose the speaker style', 'eventin-divi-addon'),
			),
			'speakers_category'   => array(
				'label'       => esc_html__('Speaker Category', 'eventin-divi-addon'),
				'type'        => 'multiple_checkboxes',
				'options'     => Helper::get_speakers_category(),
				'description' => esc_html__('Leave checkboxes unchecked to select all', 'eventin-divi-addon'),
				'toggle_slug' => 'main_content',
			),
			'etn_speaker_count'   => array(
				'default'         => 20,
				'label'           => esc_html__('Speaker Count', 'eventin-divi-addon'),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'description'     => esc_html__('Define the number of speakers that should be displayed per page.', 'eventin-divi-addon'),
				'depends_show_if' => 'off',
				'toggle_slug'     => 'main_content',
			),
			'slider_count'   => array(
				'default'         => 3,
				'label'           => esc_html__('Slider Count', 'eventin-divi-addon'),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'description'     => esc_html__('Define the number of speakers that should be displayed per page.', 'eventin-divi-addon'),
				'depends_show_if' => 'off',
				'toggle_slug'     => 'main_content',
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
			'etn_speaker_order'   => array(
				'label'            => esc_html__('Speaker Order', 'eventin-divi-addon'),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'options'   => array(
					'DESC'        => esc_html__('Descending', 'eventin-divi-addon'),
					'ASC'     => esc_html__('Ascending', 'eventin-divi-addon'),
				),
				'toggle_slug'      => 'main_content',
				'description'      => esc_html__('Here you can choose the event order', 'eventin-divi-addon'),
			),
			'etn_show_social'        => array(
				'label'       => esc_html__('Show Social', 'eventin-divi-addon'),
				'type'        => 'yes_no_button',
				'default'     => 'on',
				'options'     => array(
					'on'  => esc_html__('Yes', 'eventin-divi-addon'),
					'off' => esc_html__('No', 'eventin-divi-addon'),
				),
				'toggle_slug' => 'main_content',
			),
			'etn_show_designation'  => array(
				'label'       => esc_html__('Show Designation', 'eventin-divi-addon'),
				'type'        => 'yes_no_button',
				'default'     => 'on',
				'options'     => array(
					'on'  => esc_html__('Yes', 'eventin-divi-addon'),
					'off' => esc_html__('No', 'eventin-divi-addon'),
				),
				'toggle_slug' => 'main_content',
			),
			'slider_auto_play'  => array(
				'label'       => esc_html__('Slider Auto Play', 'eventin-divi-addon'),
				'type'        => 'yes_no_button',
				'default'     => 'on',
				'options'     => array(
					'on'  => esc_html__('Yes', 'eventin-divi-addon'),
					'off' => esc_html__('No', 'eventin-divi-addon'),
				),
				'toggle_slug' => 'main_content',
			),
			'slider_nav_show'  => array(
				'label'       => esc_html__('Show Slider Nav', 'eventin-divi-addon'),
				'type'        => 'yes_no_button',
				'default'     => 'on',
				'options'     => array(
					'on'  => esc_html__('Yes', 'eventin-divi-addon'),
					'off' => esc_html__('No', 'eventin-divi-addon'),
				),
				'toggle_slug' => 'main_content',
			),
			'slider_dot_show'  => array(
				'label'       => esc_html__('Show Slider Dots', 'eventin-divi-addon'),
				'type'        => 'yes_no_button',
				'default'     => 'on',
				'options'     => array(
					'on'  => esc_html__('Yes', 'eventin-divi-addon'),
					'off' => esc_html__('No', 'eventin-divi-addon'),
				),
				'toggle_slug' => 'main_content',
			),
			'__all_speakers' => array(
				'type' => 'computed',
				'computed_callback' => array('DIVI_Speakerslider', 'get_eventin_speaker'),
				'computed_depends_on' => array(
					'speaker_style',
					'speakers_category',
					'etn_speaker_count',
					'slider_count',
					'orderby',
					'etn_speaker_order',
					'etn_show_social',
					'etn_show_designation',
					'slider_auto_play',
					'slider_nav_show',
					'slider_dot_show'
				),
				'computed_minimum' => array(
					'speaker_style'
				),
			),
		);
	}

	public function render($attrs, $content, $render_slug)
	{
		$style = $this->props['speaker_style'];
		$speaker_cat = $this->props['speakers_category'];
		$event_count = $this->props['etn_speaker_count'];
		$slider_count = $this->props['slider_count'];
		$orderby = $this->props['orderby'];
		$order = $this->props['etn_speaker_order'];
		$etn_show_social = ($this->props['etn_show_social'] == 'on') ? 'yes' : 'no';
		$etn_show_designation = ($this->props['etn_show_designation'] == 'on') ? 'yes' : 'no';
		$slider_auto_play = ($this->props['slider_auto_play'] == 'on') ? 'yes' : 'no';
		$slider_nav_show = ($this->props['slider_nav_show'] == 'on') ? 'yes' : 'no';
		$slider_dot_show = ($this->props['slider_dot_show'] == 'on') ? 'yes' : 'no';

		$all_speaker = Helper::get_speakers();
		ksort($all_speaker);

		$all_speaker_cat = Helper::get_speakers_category();
		ksort($all_speaker_cat);
		$speaker_cat = explode('|', $speaker_cat);

		$tag_terms = divi_event_selected_terms($all_speaker_cat, $speaker_cat);

		$shortcode_content = do_shortcode("[etn_pro_speakers_sliders style ='{$style}' categories_id = {$tag_terms} auto_play='{$slider_auto_play}' orderby='{$orderby}' order='{$order}' show_designation='{$etn_show_designation}' show_social='{$etn_show_social}' slider_nav_show='{$slider_nav_show}' slider_dot_show='{$slider_dot_show}' speaker_count = {$event_count} slider_count = {$slider_count}]");

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
		$event_count = $agrs['etn_speaker_count'];
		$slider_count = $agrs['slider_count'];
		$orderby = $agrs['orderby'];
		$order = $agrs['etn_speaker_order'];
		$etn_show_social = ($agrs['etn_show_social'] == 'on') ? 'yes' : 'no';
		$etn_show_designation = ($agrs['etn_show_designation'] == 'on') ? 'yes' : 'no';
		$slider_auto_play = ($agrs['slider_auto_play'] == 'on') ? 'yes' : 'no';
		$slider_nav_show = ($agrs['slider_nav_show'] == 'on') ? 'yes' : 'no';
		$slider_dot_show = ($agrs['slider_dot_show'] == 'on') ? 'yes' : 'no';

		$all_speaker = Helper::get_speakers();
		ksort($all_speaker);

		$all_speaker_cat = Helper::get_speakers_category();
		ksort($all_speaker_cat);
		$speaker_cat = explode('|', $speaker_cat);

		$tag_terms = divi_event_selected_terms($all_speaker_cat, $speaker_cat);

		ob_start();

		echo do_shortcode("[etn_pro_speakers_sliders style ='{$style}' categories_id = {$tag_terms} auto_play='{$slider_auto_play}' orderby='{$orderby}' order='{$order}' show_designation='{$etn_show_designation}' show_social='{$etn_show_social}' slider_nav_show='{$slider_nav_show}' slider_dot_show='{$slider_dot_show}' speaker_count = {$event_count} slider_count = {$slider_count}]");

		$posts = ob_get_contents();

		ob_end_clean();

		return $posts;
	}
}

new DIVI_Speakerslider;
