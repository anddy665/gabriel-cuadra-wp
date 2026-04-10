<?php

namespace Eventin\Admin;

defined( 'ABSPATH' ) || exit;

use Eventin\Interfaces\HookableInterface;

class Menu implements HookableInterface {
    /**
     * Menu page title.
     *
     * @var string
     */
    protected $page_title;

    /**
     * Menu page title.
     *
     * @var string
     */
    protected $menu_title;

    /**
     * Menu page base capability.
     *
     * @var string
     */
    protected $base_capability;

    /**
     * Menu page base capability.
     *
     * @var string
     */
    protected $capability;

    /**
     * Menu page slug.
     *
     * @var string
     */
    protected $menu_slug;

    /**
     * Menu page icon url.
     *
     * @var string
     */
    protected $icon;

    /**
     * Menu page position.
     *
     * @var int
     */
    protected $position;

    /**
     * Submenu pages.
     *
     * @var array
     */
    protected $submenus;

    /**
     * Initialize function
     *
     * @return  void
     */
    public function __construct() {
        $this->page_title      = __( 'Eventin', 'eventin' );
        $this->menu_title      = __( 'Eventin', 'eventin' );
        $this->base_capability = 'read';
        $this->capability      = 'manage_options';
        $this->menu_slug       = 'eventin';
        $this->icon            = $this->get_eventin_menu_icon();
        $this->position        = 10;
        $this->submenus        = [
            [
                'title'      => __( 'Dashboard', 'eventin' ),
                'capability' => 'etn_manage_dashboard',
                'url'        => 'admin.php?page=' . $this->menu_slug . '#/dashboard',
                'position'   => 1,
            ],[
                'title'      => __( 'Events', 'eventin' ),
                'capability' => 'etn_manage_event',
                'url'        => 'admin.php?page=' . $this->menu_slug . '#/events',
                'position'   => 2,
            ],
            [
                'title'      => __( 'Bookings', 'eventin' ),
                'capability' => 'etn_manage_order',
                'url'        => 'admin.php?page=' . $this->menu_slug . '#/bookings',
                'position'   => 3,
            ],
            [
                'title'      => __( 'Speaker & Organizer', 'eventin' ),
                'capability' => 'etn_manage_organizer',
                'url'        => 'admin.php?page=' . $this->menu_slug . '#/speakers',
                'position'   => 5,
            ],
            [
                'title'      => __( 'Schedules', 'eventin' ),
                'capability' => 'etn_manage_schedule',
                'url'        => 'admin.php?page=' . $this->menu_slug . '#/schedules',
                'position'   => 6,
            ],
            [
                'title'      => __( 'Template Builder', 'eventin' ),
                'capability' => 'etn_manage_template',
                'url'        => 'admin.php?page=' . $this->menu_slug . '#/template-builder',
                'position'   => 7,
			],
            [
                'title'      => __( 'Settings', 'eventin' ),
                'capability' => 'etn_manage_setting',
                'url'        => 'admin.php?page=' . $this->menu_slug . '#/settings',
                'position'   => 9,
            ],
            [
                'title'      => __( 'Extensions', 'eventin' ),
                'capability' => 'etn_manage_addons',
                'url'        => 'admin.php?page=' . $this->menu_slug . '#/extensions',
                'position'   => 10,
            ],
            [
				'title'      => __( 'Shortcodes', 'eventin' ),
				'capability' => 'etn_manage_shortcode',
				'url'        => 'admin.php?page=' . $this->menu_slug . '#/shortcodes',
                'position'   => 11,
			],
            [
                'title'      => __( 'About Us', 'eventin' ),
                'capability' => 'etn_manage_get_help',
                'url'        => 'admin.php?page=' . $this->menu_slug . '#/get-help',
                'position'   => 999999,
            ],
		];

		$is_attendee_registation = etn_get_option( 'attendee_registration' );
		
		if ( 'on' === $is_attendee_registation ) {
			$this->submenus[] = [
				'title'      => __( 'Attendees', 'eventin' ),
				'capability' => 'etn_manage_attendee',
				'url'        => 'admin.php?page=' . $this->menu_slug . '#/attendees',
                'position'   => 4,
            ];
        }

        if ( ! class_exists( 'Wpeventin_Pro' ) ) {
            $this->submenus[] = [
                'title'      => __( 'Free vs Pro', 'eventin' ),
                'capability' => 'etn_manage_go_pro',
				'url'        => 'admin.php?page=' . $this->menu_slug . '#/free-vs-pro',
                'position'   => 9999999,
                'attr'       => 'class="etn-upgrade-pro-button"', // Added custom CSS class
            ];
        }
    }

    /**
     * Register service
     *
     * @return  void
     */
    public function register_hooks(): void {
        add_action( 'admin_menu', [$this, 'register_menu'] );
        add_action( 'admin_head', [ $this, 'highlight_submenu' ] );
        add_action( 'admin_menu', [$this, 'registe_depecrated_menu'], 99 );
    }

    /**
     * Register menu
     *
     * @return  void
     */
    public function register_menu() {
        global $submenu;

        add_menu_page(
            $this->page_title,
            $this->menu_title,
            $this->base_capability,
            $this->menu_slug,
            [$this, 'render_menu_page'],
            $this->icon,
            $this->position,
        );

        $this->submenus = apply_filters( 'eventin_menu', $this->submenus, $this->menu_slug );


        usort( $this->submenus, function($a, $b) {
            return $a['position'] <=> $b['position'];
        } );

        foreach ( $this->submenus as $item ) {
            // Check for 'attr' and add as HTML attribute if present
            if ( isset( $item['attr'] ) ) {
                $submenu[ $this->menu_slug ][] = [
                    '<span ' . $item['attr'] . '>' . $item['title'] . '</span>',
                    $item['capability'],
                    $item['url']
                ]; // phpcs:ignore
            } else {
                $submenu[ $this->menu_slug ][] = [
                    $item['title'],
                    $item['capability'],
                    $item['url']
                ]; // phpcs:ignore
            }
        }
    }

    /**
     * Render menu page
     *
     * @return  void
     */
    public function render_menu_page() {
        ?>
            <div class="wrap">
                <div id="eventin-dashboard"></div>
            </div>
        <?php
    }

    /**
     * Get eventin main menu icon
     *
     * @return  string
     */
    protected function get_eventin_menu_icon() {
        return "data:image/svg+xml;base64,PHN2ZyBzdHlsZT0icGFkZGluLXRvcDogNnB4IiB3aWR0aD0iMjAiIGhlaWdodD0iMjIiIHZpZXdCb3g9IjAgMCAyNiA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTI1LjExMyAxOS4yNjA0TDE3LjU2OTcgMjYuODAyMkwxMi43MDY3IDMxLjY2NTJMMTAuMzI0IDI5LjI4MjVMNy44ODU3MyAyNi44NDVDNi43NTkyNyAyNS43MTgzIDYuMDYyNzkgMjQuMjMyNyA1LjkxNzEyIDIyLjY0NjFDNS43NzE0NSAyMS4wNTk1IDYuMTg1NzcgMTkuNDcyIDcuMDg4MjEgMTguMTU5QzcuOTkwNjUgMTYuODQ1OSA5LjMyNDI3IDE1Ljg5MDIgMTAuODU3NyAxNS40NTc3QzEyLjM5MTEgMTUuMDI1MSAxNC4wMjc2IDE1LjE0MyAxNS40ODMyIDE1Ljc5MDlMMTIuNjgzNCAxOC41OTA3QzEyLjEzNjEgMTkuMTM3OSAxMS43MDIgMTkuNzg3NSAxMS40MDU4IDIwLjUwMjVDMTEuMTA5NiAyMS4yMTc1IDEwLjk1NzIgMjEuOTgzOCAxMC45NTcyIDIyLjc1NzdDMTAuOTU3MiAyMy41MzE2IDExLjEwOTYgMjQuMjk3OSAxMS40MDU4IDI1LjAxMjlDMTEuNzAyIDI1LjcyNzggMTIuMTM2MSAyNi4zNzc1IDEyLjY4MzQgMjYuOTI0N0wxOS4zMDY3IDIwLjMwMTRMMjMuODAwNiAxNS44MDY2QzIzLjIzMiAxNC43ODk0IDIyLjUyNSAxMy44NTYxIDIxLjY5OTkgMTMuMDMzMUMyMS4xMTk3IDEyLjQ1MjMgMjAuNDg0OSAxMS45Mjg4IDE5LjgwNDMgMTEuNDY5OEMxOC45Mzk0IDEwLjg4NTggMTguMDA1MiAxMC40MTE5IDE3LjAyMzIgMTAuMDU5QzE1LjgxMjIgMTEuMDY3MiAxNC4yODYyIDExLjYxOTMgMTIuNzEwNCAxMS42MTkzQzExLjEzNDYgMTEuNjE5MyA5LjYwODYxIDExLjA2NzIgOC4zOTc1OSAxMC4wNTlDNi42MzcyOCAxMC42OTMyIDUuMDM5IDExLjcwODggMy43MTcyMSAxMy4wMzMxQy0wLjY0ODIyNyAxNy4zOTg2IC0xLjE2ODM1IDI0LjE3NDUgMi4xNTUzNCAyOS4xMTc5QzIuNjEzNjMgMjkuNzk4MiAzLjEzNjcgMzAuNDMyNSAzLjcxNzIxIDMxLjAxMkw2LjE1NTQ5IDMzLjQ0NzNMMTIuNzA2NyA0MEwyMS42OTY4IDMxLjAxMkMyMy4yMDkgMjkuNDk4OCAyNC4zMTQ4IDI3LjYyODUgMjQuOTExOSAyNS41NzQzQzI1LjUwOTEgMjMuNTIwMSAyNS41NzgyIDIxLjM0ODQgMjUuMTEzIDE5LjI2MDRaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIuNzA2IDkuNzI0NTNDMTUuMzkxNCA5LjcyNDUzIDE3LjU2ODMgNy41NDc2MiAxNy41NjgzIDQuODYyMjdDMTcuNTY4MyAyLjE3NjkxIDE1LjM5MTQgMCAxMi43MDYgMEMxMC4wMjA3IDAgNy44NDM3NSAyLjE3NjkxIDcuODQzNzUgNC44NjIyN0M3Ljg0Mzc1IDcuNTQ3NjIgMTAuMDIwNyA5LjcyNDUzIDEyLjcwNiA5LjcyNDUzWiIgZmlsbD0id2hpdGUiLz4KPC9zdmc+Cg== ";
    }

    /**
     * Submenu high light
     *
     * @return  void
     */
    public function highlight_submenu() {
        global $parent_file, $submenu_file, $pagenow;

        $post_types = [
            'etn-attendee',
            'etn-speaker'
        ];

        $post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- admin menu active-state helper; post_type used only for in_array() comparison.

        if ( $pagenow == 'post-new.php' && in_array( $post_type, $post_types ) ) {
            $parent_file  = 'eventin'; // Parent menu slug
            $submenu_file = 'edit.php?post_type=' . $post_type; // Submenu slug
        }

        // Ensure the parent menu is highlighted on the main Attendee page as well
        if ( $pagenow == 'edit.php' && in_array( $post_type, $post_types ) ) {
            $parent_file  = 'eventin';
            $submenu_file = 'edit.php?post_type=' . $post_type;
        }

    }

    /**
     * Register temporary menu need to remove when widzet and shortcode page completed to reat page
     *
     * @return  void
     */
    public function registe_depecrated_menu() {
        $this->add_wizard_menu();
    }

    /**
     * Add wizard submenu
     */
    public function add_wizard_menu() {
        add_submenu_page(
            '',
            esc_html__( 'Wizard', 'eventin' ),
            esc_html__( 'Wizard', 'eventin' ),
            'manage_options',
            'etn-wizard',
            [ $this, 'etn_wizard_page' ],
            11
        );
    }

    /**
     * Settings Markup Page
     *
     * @return void
     */
    public function etn_wizard_page() {
        ?>
        <div class="etn-wizard-wrapper" id="etn-wizard-wrapper"></div>
        <?php
    }

    /**
     * Settings Markup Page
     *
     * @return void
     */
    public function etn_shortcode_page() {
        include_once( \Wpeventin::plugin_dir() . "templates/layout/header.php" );

        $shortcodeView = \Wpeventin::plugin_dir() . "core/shortcodes/views/shortcode-list-menu.php";
        if ( file_exists( $shortcodeView ) ) {
            include $shortcodeView;
        }
    }
}
