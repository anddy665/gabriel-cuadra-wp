<?php
/**
 * Easy Demo Import
 *
 * Class: JEDI_Apprentice_Admin
 * This class builds the WP Admin menu items and pages.
 *
 * @package    jedi-master
 * @author     Jerry Simmons <jerry@ferventsolutions.com>
 * @copyright  2021 Jerry Simmons
 * @license    GPL-2.0+
 **/

if( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'JEDI_Apprentice_Admin' ) ) :
	/**
	 * JEDI Apprentice Admin Menu & Pages
	 **/
	class JEDI_Apprentice_Admin {

		/**
		 * $jedi_apprentice_menu_title - Menu Title
		 * 
		 * @var string 
		 **/
		public $jedi_apprentice_menu_title;

		/**
		 * $jedi_apprentice_menu_slug - Menu Slug
		 * 
		 * @var string 
		 **/
		public $jedi_apprentice_menu_slug = 'jedi_apprentice_menu';

		/**
		 * $jedi_import_section_id - Section Id
		 * 
		 * @var string 
		 **/
		public $jedi_import_section_id = 'jedi_apprentice_options';

		/**
		 * $jedi_apprentice_settings_page - Settings Page
		 * 
		 * @var string 
		 **/
		public $jedi_apprentice_settings_page = 'jedi_import_options_page';


		/**
		 * $jedi_support_section_id - Support Section Id
		 * 
		 * @var string 
		 **/
		public $jedi_support_section_id = 'jedi_support_section_id';

		/**
		 * $jedi_support_settings_page - Support Page Slug
		 * 
		 * @var string 
		 **/
		public $jedi_support_settings_page = 'jedi_support_settings_page';

		/**
		 * $jedi_apprentice_settings - Apprentice Settings Array
		 * These settings are the defaults set during the export
		 * 
		 * @var array
		 **/
		public $jedi_apprentice_settings = array();

		/**
		 * $jedi_import_options - Import Options Array
		 * These settings are set by the user at import
		 * 
		 * @var array
		 **/
		public $jedi_import_options = array();

		/**
		 * $jedi_import_history - Import History Data
		 * 
		 * @var array 
		 **/
		public $jedi_import_history = array();

		/**
		 * $jedi_apprentice_data_import - Import Data
		 * 
		 * @var array 
		 **/
		public $jedi_apprentice_data_import = array();


		/**
		 * Initializes import settings
		 * Build Admin Menus
		 **/
		public function __construct() {

			$this->jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
			$this->jedi_import_options = get_option( 'jedi_import_options' );
			$this->jedi_apprentice_menu_title = $this->jedi_apprentice_settings['installer_menu_title'];

			/**
			 * Import History Setup
			 *
			 * @since v1.3
			 **/
			$this->jedi_import_history = get_option( 'jedi_import_history' );
			if( false === $this->jedi_import_history ) { $this->jedi_import_history = array(); }

			add_action( 'admin_menu', array( $this, 'admin_menu' ), 8000 );
			add_action( 'admin_init', array( $this, 'admin_menu_support_options' ), 2 );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_jedi_apprentice_admin_style' ) );
		}

		/**
		 * Load Admin Menus CSS
		 **/
		public function load_jedi_apprentice_admin_style() {
			if( $this->jedi_apprentice_settings['installer_style'] ) {
				wp_register_style(
					'jedi_apprentice_admin_css',
					get_stylesheet_directory_uri() . '/jedi-apprentice/includes/jedi_apprentice_admin.css',
					array(),
					$this->jedi_apprentice_settings['jedi_master_version']
				);
			} else {
				wp_register_style(
					'jedi_apprentice_admin_css',
					jswj_get_jedi_apprentice_url() . 'includes/jedi_apprentice_admin.css',
					array(),
					$this->jedi_apprentice_settings['jedi_master_version']
				);
			}
			wp_enqueue_style( 'jedi_apprentice_admin_css' );
		}


		/**
		 * Builds the WP Admin Page Menus
		 **/
		public function admin_menu() {

			if( file_exists( plugin_dir_path( __FILE__ ) . 'edi-dashboard-import-icon.png' ) ) {
				$jedi_icon = jswj_get_jedi_apprentice_url() . 'includes/edi-dashboard-import-icon.png';
			} else {
				$jedi_icon = 'dashicons-migrate';
			}

			if ( 'Imported' !== get_option( 'jedi_status' ) ) {
				$menu_page_position = '3.01';
			} else { 
				$menu_page_position = '9999';
			}

			add_menu_page(
				$this->jedi_apprentice_menu_title,                 # Title
				$this->jedi_apprentice_menu_title,                 # Menu Title
				'import',                                          # Capability
				$this->jedi_apprentice_menu_slug,                  # Menu Slug
				array( $this, 'jedi_apprentice_admin_menu_page' ), # Callable Function
				$jedi_icon,                                        # Icon
				$menu_page_position                                # Position
			);
			add_submenu_page(
				$this->jedi_apprentice_menu_slug,                 # Parent Slug
				'Easy Demo Import',                               # Page Title,
				'Easy Demo Import',                               # Menu Title
				'import',                                         # Capability
				$this->jedi_apprentice_menu_slug,                 # Menu Slug
				array( $this, 'jedi_apprentice_admin_menu_page' ) # Callable Function
			);
			add_submenu_page(
				$this->jedi_apprentice_menu_slug,              # Parent Slug
				'Support',                                     # Page Title,
				'Support',                                     # Menu Title
				'import',                                      # Capability
				$this->jedi_apprentice_menu_slug . '_support', # Menu Slug
				array( $this, 'jedi_apprentice_support_page' ) # Callable Function
			);
		}

		/**
		 * Remove JEDI Apprentice Folder
		 **/
		public function jedi_after_import_remove_demo_content() {
			$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );

			global $wp_filesystem;
			if( empty( $wp_filesystem ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
				if( ! WP_Filesystem() ) {
					jswj_jedi_order_66( 'Failed to initialize WP Filesystem.' );
				}
			}
			$wp_filesystem->rmdir( JEDI_APPRENTICE_PATH, true );
			echo '<script>window.location="' . esc_url( admin_url() ) . '"</script>';
		} # END jedi_after_import_remove_demo_content()

		/**
		 * Main Import Page for JEDI Apprentice
		 **/
		public function jedi_apprentice_admin_menu_page() {

			$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );

			// phpcs:disable WordPress.Security.NonceVerification.Missing
			if( isset( $_POST['after_import_remove_demo_content'] ) ) {
				jswj_verify_nonce_and_capability( 'jedi_apprentice_menu_after_import', '_jedi_apprentice_nonce' );
				$this->jedi_after_import_remove_demo_content();
			}

			# Handle Previous Import Failures
			if( isset( $_POST['resume_previous_import'] ) ) {
				jswj_verify_nonce_and_capability( 'jedi_apprentice_menu_resume', '_jedi_apprentice_nonce' );
				update_option( 'jedi_status', 'Resuming Previous Import' );
				return;
			}

			if( isset( $_POST['cancel_previous_import'] ) ) {
				jswj_verify_nonce_and_capability( 'jedi_apprentice_menu_resume', '_jedi_apprentice_nonce' );
				jswj_jedi_log( 'Previous Import Cancelled' );
				delete_option( 'jedi_track_import' );
				delete_option( 'jedi_status' );
			}

			# Import Demo Content Button
			if( isset( $_POST['import_demo_content'] ) ) { # button name
				jswj_verify_nonce_and_capability( 'jedi_apprentice_menu', '_jedi_apprentice_nonce' );

				update_option( 'jedi_status', 'Importing' );

				$this->jedi_import_options['include_posts'] = '';
				$this->jedi_import_options['include_media'] = '';
				$this->jedi_import_options['include_options'] = '';
				$this->jedi_import_options['elementor']['include_options'] = '';
				$this->jedi_import_options['include_css'] = '';
				$this->jedi_import_options['include_menus'] = '';
				$this->jedi_import_options['include_homepage'] = '';
				$this->jedi_import_options['include_widgets'] = '';
				$this->jedi_import_options['include_wp_options'] = '';

				if( isset( $_POST['jedi_import_posts_checkbox'] ) ) {
					$this->jedi_import_options['include_posts'] = sanitize_text_field( $_POST['jedi_import_posts_checkbox'] );
				}
				if( isset( $_POST['jedi_import_media_checkbox'] ) ) {
					$this->jedi_import_options['include_media'] = sanitize_text_field( $_POST['jedi_import_media_checkbox'] );
				}
				if( isset( $_POST['jedi_import_options_checkbox'] ) ) {
					$this->jedi_import_options['include_options'] = sanitize_text_field( $_POST['jedi_import_options_checkbox'] );
				}

				if( isset( $_POST['jedi_import_elementor_options_checkbox'] ) ) {
					$this->jedi_import_options['elementor']['include_options'] = sanitize_text_field( $_POST['jedi_import_elementor_options_checkbox'] );
				}

				if( isset( $_POST['jedi_import_css_checkbox'] ) ) {
					$this->jedi_import_options['include_css'] = sanitize_text_field( $_POST['jedi_import_css_checkbox'] );
				}
				if( isset( $_POST['jedi_import_menus_checkbox'] ) ) {
					$this->jedi_import_options['include_menus'] = sanitize_text_field( $_POST['jedi_import_menus_checkbox'] );
				}
				if( isset( $_POST['jedi_import_homepage_checkbox'] ) ) {
					$this->jedi_import_options['include_homepage'] = sanitize_text_field( $_POST['jedi_import_homepage_checkbox'] );
				}
				if( isset( $_POST['jedi_import_widgets_checkbox'] ) ) {
					$this->jedi_import_options['include_widgets'] = sanitize_text_field( $_POST['jedi_import_widgets_checkbox'] );
				}
				if( isset( $_POST['jedi_import_wp_options_checkbox'] ) ) {
					$this->jedi_import_options['include_wp_options'] = sanitize_text_field( $_POST['jedi_import_wp_options_checkbox'] );
				}

				/**
				 * Prepare List Of Plugins For Install & Activation
				 **/
				$install_plugins = array();
				$install_nonrepo_plugins = array();
				$activate_plugins = array();
				foreach( $_POST as $key => $post_data ) {
					$post_data = sanitize_text_field( $post_data );
					if( 0 === strpos( $key, 'install_plugin' ) ) {
						if( 'nonrepo' === $post_data ) {
							$install_nonrepo_plugins[ substr( $key, 15 ) ] = substr( $key, 15 );
						} else {
							$install_plugins[ substr( $key, 15 ) ] = $post_data;
						}
					}
					if( 0 === strpos( $key, 'activate_plugin' ) ) {
						$activate_plugins[ substr( $key, 16 ) ] = $post_data;
					}
				}

				$this->jedi_import_options['install_plugins'] = $install_plugins;
				$this->jedi_import_options['install_nonrepo_plugins'] = $install_nonrepo_plugins;
				$this->jedi_import_options['activate_plugins'] = $activate_plugins;
				update_option( 'jedi_import_options', $this->jedi_import_options );
				return;
			}
			// phpcs:enable WordPress.Security.NonceVerification.Missing

			if( 'true' === get_option( 'jedi_full_import_completed_' . $jedi_apprentice_settings['installer_slug'] ) ) {
				jedi_after_import_remove_demo_content_form();
			}

			echo wp_kses_post( jswj_jedi_systems_check() );

			# Display Import Options
			echo '<div class="jedi_import_options_container">';
				echo '<form action="admin.php?page=jedi_apprentice_menu" method="POST">';
					wp_nonce_field( 'jedi_apprentice_menu', '_jedi_apprentice_nonce' );
					echo '<input type="hidden" value="true" name="jedi_import_button" />';

					$this->list_import_options();

					if( ! empty( $this->jedi_apprentice_settings['selected_plugins'] ) ) {
						do_action( 'jedi_list_recommended_plugins', $this->jedi_apprentice_settings['selected_plugins'] );
					}
					if( ! empty( $this->jedi_apprentice_settings['jedi_exported_nonrepo_plugins'] ) ) {
						do_action( 'jedi_list_recommended_nonrepo_plugins', $this->jedi_apprentice_settings['jedi_exported_nonrepo_plugins'] );
					}
					submit_button( 'Import Demo Content', 'primary', 'import_demo_content' );
				echo '</form>';
			echo '</div>';

		} # END jedi_apprentice_admin_menu_page()


		/**
		 * Import Page - Installer Message
		 **/
		public function jedi_import_options_callback() {
			# Installer Message
			echo wp_kses_post( nl2br( $this->jedi_apprentice_settings['installer_message'] ) );
		}


		/**
		 * Import Options Checkboxes
		 **/
		public function list_import_options() {
			$jedi_documentation = $this->jedi_apprentice_settings['custom_documentation'];
			echo '<h2>' . esc_html( $this->jedi_apprentice_menu_title ) . ' - Import Options</h2>';
			echo wp_kses_post( $jedi_documentation['intro']['text'] );
			echo '<table class="jedi_import_options_table">';
				if( $this->jedi_apprentice_settings['include_posts'] ) {
					echo '<tr><th><h3>Include Posts</h3></th>';
						echo "<td class='jedi_import_checkbox'><input type='checkbox' name='jedi_import_posts_checkbox' value='1' "
							. checked( 1, $this->jedi_import_options['include_posts'], false ) . ' /></td>';
						echo '<td class="jedi_doc_text">' . wp_kses_post( $jedi_documentation['include_posts']['text'] );
						echo '</td>';
					echo '</tr>';
				}
				if( $this->jedi_apprentice_settings['include_media'] ) {
					echo '<tr><th><h3>Include Media</h3></th>';
					echo "<td class='jedi_import_checkbox'><input type='checkbox' name='jedi_import_media_checkbox' value='1' "
						. checked( 1, $this->jedi_import_options['include_media'], false ) . ' /></td>';
						echo '<td class="jedi_doc_text">' . wp_kses_post( $jedi_documentation['include_media']['text'] );
						echo '</td>';
					echo '</tr>';
				}

				if( $this->jedi_apprentice_settings['include_css'] ) {
					echo '<tr><th><h3>Include CSS</h3></th>';
					echo "<td class='jedi_import_checkbox'><input type='checkbox' name='jedi_import_css_checkbox' value='1' " 
						. checked( 1, $this->jedi_import_options['include_css'], false ) . ' /></td>';
						echo '<td class="jedi_doc_text">' . wp_kses_post( $jedi_documentation['include_css']['text'] );
						echo '</td>';
					echo '</tr>';
				}
				if( $this->jedi_apprentice_settings['include_menus'] ) {
					echo '<tr><th><h3>Include Menus</h3></th>';
					echo "<td class='jedi_import_checkbox'><input type='checkbox' name='jedi_import_menus_checkbox' value='1' "
						. checked( 1, $this->jedi_import_options['include_menus'], false ) . ' /></td>';
						echo '<td class="jedi_doc_text">' . wp_kses_post( $jedi_documentation['include_menus']['text'] );
						echo '</td>';
					echo '</tr>';
				}
				if( $this->jedi_apprentice_settings['include_homepage'] ) {
					echo '<tr><th><h3>Include Homepage And Posts Page</h3></th>';
					echo "<td class='jedi_import_checkbox'><input type='checkbox' name='jedi_import_homepage_checkbox' value='1' "
						. checked( 1, $this->jedi_import_options['include_homepage'], false ) . ' /></td>';
						echo '<td class="jedi_doc_text">' . wp_kses_post( $jedi_documentation['include_homepage']['text'] );
						echo '</td>';
					echo '</tr>';
				}
				if( $this->jedi_apprentice_settings['include_widgets'] ) {
					echo '<tr><th><h3>Include Widgets</h3></th>';
					echo "<td class='jedi_import_checkbox'><input type='checkbox' name='jedi_import_widgets_checkbox' value='1' "
						. checked( 1, $this->jedi_import_options['include_widgets'], false ) . ' /></td>';
						echo '<td class="jedi_doc_text">' . wp_kses_post( $jedi_documentation['include_widgets']['text'] );
						echo '</td>';
					echo '</tr>';
				}

				if( $this->jedi_apprentice_settings['include_options'] ) {
					echo '<tr><th><h3>Include Divi Options</h3></th>';
					echo "<td class='jedi_import_checkbox'><input type='checkbox' name='jedi_import_options_checkbox' value='1' "
						. checked( 1, $this->jedi_import_options['include_options'], false ) . ' /></td>';
						echo '<td class="jedi_doc_text">' . wp_kses_post( $jedi_documentation['include_options']['text'] );
						echo '</td>';
					echo '</tr>';
				}

				if( $this->jedi_apprentice_settings['include_divi_theme_builder'] ) {
					echo '<tr><th><h3>Include Divi Theme Builder Data</h3></th>';
					echo "<td class='jedi_import_checkbox'><input type='checkbox' name='jedi_import_divi_theme_builder_checkbox' value='1' "
						. checked( 1, $this->jedi_import_options['include_divi_theme_builder'], false ) . ' /></td>';
						echo '<td class="jedi_doc_text">';
						if ( ! empty( $jedi_documentation['include_divi_theme_builder'] ) ) {
							wp_kses_post( $jedi_documentation['include_divi_theme_builder']['text'] );
						}
						echo '</td>';
					echo '</tr>';
				}

				if( $this->jedi_apprentice_settings['elementor']['include_options'] ) {
					echo '<tr><th><h3>Include Elementor Options & Settings</h3></th>';
					echo "<td class='jedi_import_checkbox'><input type='checkbox' name='jedi_import_elementor_options_checkbox' value='1' "
						. checked( 1, $this->jedi_import_options['elementor']['include_options'], false ) . ' /></td>';
						echo '<td class="jedi_doc_text">' . wp_kses_post( $jedi_documentation['include_options']['text'] );
						echo '</td>';
					echo '</tr>';
				}

				# Import WP Options Checkbox
				$import_data = jswj_get_jedi_import_data();
				if( ! empty( $import_data['wp_options'] ) ) {
					echo '<tr><th><h3>Include WP Options</h3></th>';
					echo "<td class='jedi_import_checkbox'><input type='checkbox' name='jedi_import_wp_options_checkbox' value='1' "
						. checked( 1, $this->jedi_import_options['include_wp_options'], false ) . ' /></td>';
						echo '<td class="jedi_doc_text">';
							if( isset( $jedi_documentation['include_wp_options'] ) ) {
								echo wp_kses_post( $jedi_documentation['include_wp_options']['text'] );

								$options_list = array();
								foreach( $import_data['wp_options'] as $slug => $value ) {
									$options_list[] = $slug;
								}
								echo 'WP Options: ' . esc_attr( implode( ',', $options_list ) );
							}
						echo '</td>';
					echo '</tr>';
				}

			echo '</table>';
		}

		/**
		 * Documentation Page for JEDI Apprentice
		 **/
		public function jedi_apprentice_documentation_page() {

			echo '<div class="jedi_documentation_page">';
			echo '<h2>' . esc_html( $this->jedi_apprentice_settings['installer_name'] ) . ' - Documentation</h2>';

			$jedi_documentation = $this->jedi_apprentice_settings['custom_documentation'];

			foreach( $jedi_documentation as $doc_item ) {
				echo '<div class="jedi_documentation_item">';
				echo '<h4>' . esc_html( $doc_item['heading'] ) . '</h4>';
				echo wp_kses_post( stripslashes( $doc_item['text'] ) );
				echo '</div> <!-- END div.jedi_documentation_item -->';
			}

			echo '</div> <!-- END div.jedi_documentation_page -->';
		} # END jedi_apprentice_documentation_page()



		/**
		 * Support Page for JEDI Apprentice
		 **/
		public function jedi_apprentice_support_page() {

			// phpcs:disable WordPress.Security.NonceVerification.Missing
			if( isset( $_POST['jedi_support_save'] ) ) { # button name
				jswj_verify_nonce_and_capability( 'jedi_apprentice_support_page_save', '_jedi_apprentice_nonce' );

				$import_batch_size = 20;
				$import_media_batch_size = 20;

				if( isset( $_POST['jedi_import_batch_size_field'] ) ) {
					$import_batch_size = intval( $_POST['jedi_import_batch_size_field'] );
				}
				if( isset( $_POST['jedi_import_media_batch_size_field'] ) ) {
					$import_media_batch_size = intval( $_POST['jedi_import_media_batch_size_field'] );
				}
				# Update Database Values
				$this->jedi_import_options['import_batch_size'] = $import_batch_size;
				$this->jedi_import_options['import_media_batch_size'] = $import_media_batch_size;
				update_option( 'jedi_import_options', $this->jedi_import_options );

				$this->jedi_apprentice_settings['import_batch_size'] = $import_batch_size;
				$this->jedi_apprentice_settings['import_media_batch_size'] = $import_media_batch_size;
				update_option( 'jedi_apprentice_settings', $this->jedi_apprentice_settings );
			}

			if( isset( $_POST['jedi_support_reset'] ) ) { # button name
				if( isset( $_POST['submit'] ) && 'Reset Import Settings' === sanitize_text_field( $_POST['submit'] ) ) {
					jswj_verify_nonce_and_capability( 'jedi_apprentice_support_page_reset', '_jedi_apprentice_nonce' );
					delete_option( 'jedi_import_options' );
					delete_option( 'jedi_apprentice_settings' );
					delete_option( 'jedi_status' );
					delete_option( 'jedi_track_import' );
					delete_option( 'jswj_jedi_log' );

					$reset_jedi_apprentice = new JEDI_Apprentice_Admin();
					unset( $reset_jedi_apprentice );
					echo '<div class="jedi_settings_reset">';
						echo '<p>Import Settings Have Been Reset To Defaults</p>';
					echo '</div>';

					$this->jedi_import_options = get_option( 'jedi_import_options' );
				}
			}
			// phpcs:enable WordPress.Security.NonceVerification.Missing

			# Display Options Form
			$form_action = 'action="admin.php?page=' . $this->jedi_apprentice_menu_slug . '"';
	?>
				<div class="jedi_support_options_container">
					<form <?php $form_action; ?> method="POST">
						<?php
						wp_nonce_field( 'jedi_apprentice_support_page_save', '_jedi_apprentice_nonce' );
						?>
						<input type="hidden" value="true" name="jedi_support_save" id="jedi_support_form"/>
						<hr>
						<?php 
						settings_fields( $this->jedi_support_section_id );
						do_settings_sections( $this->jedi_support_settings_page );
						?>
					</form>
				</div>

				<hr>
				<form <?php $form_action; ?> method="POST">
					<?php wp_nonce_field( 'jedi_apprentice_support_page_reset', '_jedi_apprentice_nonce' ); ?>
					<input type="hidden" value="true" name="jedi_support_reset" id="jedi_support_form"/>
					<?php submit_button( 'Reset Import Settings' ); ?>
				</form>

			<?php
		}


		/**
		 * Builds all the fields for the Support Page
		 **/
		public function admin_menu_support_options() {

			# Need Some Help? Section
			add_settings_section(
				$this->jedi_support_section_id . '_info',   # ID
				__( 'Need Some Help?', 'jedi-apprentice' ), # Title
				array( $this, 'jedi_support_info' ),        # Callback Function
				$this->jedi_support_settings_page           # Settings Page
			);

			# Support Options Section
			add_settings_section(
				$this->jedi_support_section_id,                  # ID
				__( 'Support Tools', 'jedi-apprentice' ),        # Title
				array( $this, 'jedi_support_options_callback' ), # Callback Function
				$this->jedi_support_settings_page                # Settings Page
			);

			# Batch Size Field
			add_settings_field(
				'jedi_import_batch_size_field',                 # ID,
				__( 'Import Batch Sizes', 'jedi-apprentice' ),  # Title
				array( $this, 'jedi_import_batch_size_field' ), # Callback Function
				$this->jedi_support_settings_page,              # Settings Section Page
				$this->jedi_support_section_id                  # Settings Section ID
			); 
			register_setting( $this->jedi_support_section_id, 'jedi_import_batch_size_field' );

			add_settings_section(
				$this->jedi_support_section_id . '_log', # ID
				__( 'Log Entries', 'jedi-apprentice' ),  # Title
				array( $this, 'jedi_support_info_log' ), # Callback Function
				$this->jedi_support_settings_page        # Settings Page
			);

		} # END admin_menu_support_options()

		/**
		 * Support Information & Links
		 **/
		public function jedi_support_info() {
			echo '<div class="jedi_admin_support_info">';
			echo wp_kses_post( stripslashes( $this->jedi_apprentice_settings['support_message'] ) );
			echo '</div><hr>';
		} # END jedi_support_info()

		/**
		 * Display JEDI Log Section On Support Page
		 *
		 * @return void
		 */
		public function jedi_support_info_log() {
			jswj_display_jedi_log();
		} # END jedi_support_info_log()

		/**
		 * Support Page - Helpful Text
		 **/
		public function jedi_support_options_callback() {}

		/**
		 * Field: Import Batch Size
		 **/
		public function jedi_import_batch_size_field() {
			echo "Post Batch Size: <input type='text' name='jedi_import_batch_size_field' size='5' value='"
				. esc_attr( $this->jedi_import_options['import_batch_size'] ) . "' /><br>";
			echo "Media Batch Size: <input type='text' name='jedi_import_media_batch_size_field' size='5' value='"
				. esc_attr( $this->jedi_import_options['import_media_batch_size'] ) . "' />";
			submit_button( 'Save Changes' ); 
		}


	} # END JEDI_Apprentice_Admin
endif;
