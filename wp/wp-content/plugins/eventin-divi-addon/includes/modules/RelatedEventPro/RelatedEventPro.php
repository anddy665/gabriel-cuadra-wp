<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Etn\Utils\Helper;
class DIVI_RelatedEventsPro extends ET_Builder_Module {

	public $slug       = 'eventin_related_events_pro';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://product.themewinter.com/eventin',
		'author'     => 'Themewinter',
		'author_uri' => 'https://themewinter.com/',
	);

	public function init() {
		$this->name = esc_html__( 'Related Events Pro', 'eventin-divi-addon' );
	}

	public function get_icon( $icon_name = null ) {
        return plugin_dir_path( __FILE__ ) . 'icon.svg';
    }

	public function get_fields() {
		return array(
			'etn_event_id'   => array(
				'label'            => esc_html__( 'Select Event', 'eventin-divi-addon' ),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'options'          => Helper::get_events(),
				'toggle_slug'      => 'main_content',
				'description'      => esc_html__( 'Here you can choose the event', 'eventin-divi-addon' ),
			),
			'__single_event' => array(
				'type' => 'computed',
				'computed_callback' => array( 'DIVI_RelatedEventsPro', 'get_eventin_events' ),
				'computed_depends_on' => array(
						'etn_event_id'						
				),
				'computed_minimum' => array(
						'etn_event_id'
				),
			),
		);
	}

	public function render( $attrs, $content, $render_slug ) {
		$etn_event_id = $this->props['etn_event_id'];

		$shortcode_content = do_shortcode("[etn_pro_related_events id = {$etn_event_id}]");

		return sprintf($shortcode_content);
		
	}

	/**
	 * Get blog posts for blog module
	 *
	 * @param array   arguments that is being used by et_pb_custom_blog
	 * @return string blog post markup
	 */
	static function get_eventin_events( $agrs = array() ) {

		$etn_event_id = $agrs['etn_event_id'];

		ob_start();
		
		echo do_shortcode("[etn_pro_related_events id = {$etn_event_id}]");

		$posts = ob_get_contents();

		ob_end_clean();

		return $posts;

	}

}

new DIVI_RelatedEventsPro;
