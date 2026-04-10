<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class DIVI_AdvancedSearch extends ET_Builder_Module {

    public $slug       = 'eventin_advanced_search';
    public $vb_support = 'on';

    protected $module_credits = array(
        'module_uri' => 'https://product.themewinter.com/eventin-pro',
		'author'     => 'Themewinter',
		'author_uri' => 'https://themewinter.com/',
    );

    public function init() {
        $this->name = esc_html__( 'Eventin Advanced Search', 'eventin-divi-addon' );

        // à retirer en prod
        $debug_module = true;

        if (is_admin()) {
            // Clear module from cache if necessary
            if ($debug_module) { 
                add_action('admin_head', array( $this, 'remove_from_local_storage' ) );
            }
        }
        // à retirer en prod
    }

    public function get_icon( $icon_name = null ) {
        return plugin_dir_path( __FILE__ ) . 'icon.svg';
    }

    public $debug_module = true;
                        
        public function remove_from_local_storage() {
            global $debug_module; 
            echo "<script>localStorage.removeItem('et_pb_templates_".esc_attr($this->slug)."');</script>";
        }


    public function get_fields() {
        return array(
            'etn_event_button_title' => array(
                'label'             => esc_html__( 'Button Text', 'eventin-divi-addon' ),
                'type'              => 'text',
                'option_category'   => 'configuration',
                'description'       => esc_html__( 'Type your title here', 'eventin-divi-addon' ),
                'computed_affects'   => array(
                    '__posts',
                ),
                'toggle_slug'       => 'main_content',
                'default'           => esc_html__( 'Search Now', 'eventin-divi-addon' ),
            ),
      
            
            '__posts' => array(
                'type' => 'computed',
                'computed_callback' => array( 'DIVI_AdvancedSearch', 'get_advanced_search' ),
                'computed_depends_on' => array(
                    'etn_event_button_title'
                ),
                'computed_minimum' => array(
                    'etn_event_button_title'
                ),
            ),
        );
    }

     /**
     * Get blog posts for blog module
     *
     * @param array   arguments that is being used by et_pb_custom_blog
     * @return string blog post markup
     */
    static function get_advanced_search( $agrs = array() ) {

        $etn_event_button_title = $agrs['etn_event_button_title'];
        ob_start();

		 echo do_shortcode("[event_search_filter]");

        $posts = ob_get_contents();

        ob_end_clean();

        return $posts;
    }

    public function render( $attrs, $content, $render_slug ) {
        $etn_event_button_title = $this->props['etn_event_button_title'];
      
        ob_start();

		echo do_shortcode("[event_search_filter]");

        $posts = ob_get_contents();

        ob_end_clean();

        return $posts;
    }
}

new DIVI_AdvancedSearch;