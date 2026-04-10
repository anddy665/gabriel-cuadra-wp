<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Etn\Utils\Helper;
use Etn_Pro\Utils\Helper as UtilsHelper;

class DIVI_EventLocationPro extends ET_Builder_Module
{

	public $slug       = 'eventin_location_pro';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://product.themewinter.com/eventin',
		'author'     => 'Themewinter',
		'author_uri' => 'https://themewinter.com/',
	);

	public function init()
	{
		$this->name = esc_html__('Event Location Pro',  'eventin-divi-addon');
	}

	public function get_icon( $icon_name = null ) {
        return plugin_dir_path( __FILE__ ) . 'icon.svg';
    }
    
	public function get_fields()
	{
		return array(
			'location_limit'      => array(
				'default'         => 6,
				'label'           => esc_html__('count',  'eventin-divi-addon'),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'description'     => esc_html__('Define the number of events that should be displayed per page.',  'eventin-divi-addon'),
				'depends_show_if' => 'off',
				'toggle_slug'     => 'main_content',
			),
			'__location' => array(
				'type' => 'computed',
				'computed_callback' => array('DIVI_EventLocationPro', 'get_eventin_location'),
				'computed_depends_on' => array(
					'location_limit'
				),
				'computed_minimum' => array(
					'location_limit'
				),
			),
		);
	}
	public function render($attrs, $content, $render_slug)
	{
		$limit = $this->props['location_limit'];

		$shortcode_content =  do_shortcode("[etn_pro_event_location_list style ='style-1' location_limit = {$limit} ]");

		return sprintf($shortcode_content);
	}

	/**
	 * Get blog posts for blog module
	 *
	 * @param array   arguments that is being used by et_pb_custom_blog
	 * @return string blog post markup
	 */
	static function get_eventin_location($agrs = array())
	{
		$limit = $agrs['location_limit'];

		ob_start();

		echo do_shortcode("[etn_pro_event_location_list style ='style-1' location_limit = {$limit}  ]");

		$posts = ob_get_contents();

		ob_end_clean();

		return $posts;
	}
}

new DIVI_EventLocationPro;
