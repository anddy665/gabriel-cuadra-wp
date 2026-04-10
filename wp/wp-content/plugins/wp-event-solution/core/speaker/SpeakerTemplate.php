<?php

namespace Eventin\Speaker;

defined( 'ABSPATH' ) || exit;

use Eventin\Interfaces\HookableInterface;

/**
 * Speaker Template
 */
class SpeakerTemplate implements HookableInterface {
    /**
     * Register all hooks
     *
     * @return  void
     */
    public function register_hooks(): void {
        add_action( 'template_include', array( $this, 'single_author_page' ));
        add_action( 'etn_single_speaker_template', [ $this, 'include_single_template' ], 9 );
    }

    /**
	 * Checks if the current page is an author page and if the author has the 'etn-speaker' or 'etn-organizer' role.
	 * If so, it includes the template part for single author.
	 *
	 * @since 2.4.0
	 */
	public function single_author_page($template) {
		if ( ! is_author() ) {
			return $template;
		}
	
		$author_id = get_queried_object_id();
		$author = get_userdata( $author_id );
	
		if ( $author && ( in_array( 'etn-speaker', (array) $author->roles) || in_array('etn-organizer', (array) $author->roles ) ) ) {
			// Path to your custom author template in the plugin folder
			$custom_template = \Wpeventin::templates_dir() . 'speaker/single-author.php';
			// If the file exists, use it
			if ( file_exists( $custom_template ) ) {
				return $custom_template;
			}
		}
	
		return $template;
	}

    /**
     * Include single author template
     *
     * @return  void
     */
    public function include_single_template() {
        $default_template_name = "speaker-one";
        $settings              = etn_get_option();
        $template_name         = !empty( $settings['speaker_template'] ) ? $settings['speaker_template'] : $default_template_name;
        if( ETN_DEMO_SITE === true ) {

            switch( get_queried_object_id() ){
                case ETN_SPEAKER_TEMPLATE_ONE_ID :
                    $single_template_path = \Wpeventin::templates_dir() . "speaker-one.php";
                    break;
                case ETN_SPEAKER_TEMPLATE_TWO_LITE_ID :
                    $single_template_path = \Wpeventin::templates_dir() . "speaker-two-lite.php";
                    break;
                case ETN_SPEAKER_TEMPLATE_TWO_ID :
                    $single_template_path = class_exists('Wpeventin_Pro') ? \Wpeventin_Pro::templates_dir() . "speaker-two.php" : \Wpeventin::templates_dir() . "speaker-one.php";
                    break;
                case ETN_SPEAKER_TEMPLATE_THREE_ID :
                    $single_template_path = class_exists('Wpeventin_Pro') ? \Wpeventin_Pro::templates_dir() . "speaker-three.php" : \Wpeventin::templates_dir() . "speaker-one.php";
                    break;
                default:
                    $single_template_path = \Etn\Utils\Helper::prepare_speaker_template_path( $default_template_name, $template_name );
                    break;
            }

            if ( file_exists( $single_template_path ) ) {
                include_once $single_template_path;
            }

        } else {
    
            //check if single page template is overriden from theme
            //if overriden, then the overriden template has higher priority
            if ( file_exists( get_stylesheet_directory() . \Wpeventin::theme_templates_dir() . $template_name . '.php' ) ) {
                include_once get_stylesheet_directory() . \Wpeventin::theme_templates_dir() . $template_name . '.php';
            } else if ( file_exists( get_template_directory() . \Wpeventin::theme_templates_dir() . $template_name . '.php' ) ) {
                include_once get_template_directory() . \Wpeventin::theme_templates_dir() . $template_name . '.php';
            } else {
    
                // check if multi-template settings exists
                $single_template_path = $this->prepare_speaker_template_path( $default_template_name, $template_name );
    
                if ( file_exists( $single_template_path ) ) {
                    include_once $single_template_path;
                }
    
            }
        }
    }

    /**
	 * Undocumented function
	 *
	 * @param [type] $default_template_name
	 * @param [type] $template_name
	 *
	 * @return void
	 */
	public function prepare_speaker_template_path( $default_template_name, $template_name ) {
		$arr = [
			'speaker-one',
			'speaker-two-lite',
		];

		if ( ! in_array( $template_name, $arr ) && class_exists( 'Etn_Pro\Bootstrap' ) && class_exists( 'Wpeventin_Pro' ) ) {
			$single_template_path = \Wpeventin_Pro::templates_dir() . $template_name . ".php";
		} else {
			$single_template_path = \Wpeventin::templates_dir() . $template_name . ".php";
		}

		$single_template_path = apply_filters( "etn_speaker_content_template_path", $single_template_path );

		return $single_template_path;
	}
}
