<?php

namespace DiviTorqueLite;

use DiviTorqueLite\ModulesManager;
use DiviTorqueLite\AdminHelper;

class Dashboard
{

    private static $instance;
    private $menu_slug = 'divitorque';
    private $capability = 'manage_options';

    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts'], 100);
    }

    public function add_menu()
    {
        if (!current_user_can($this->capability) || AdminHelper::is_pro_installed()) {
            return;
        }

        // SVG icon (base64 encoded)
        $icon_svg = 'data:image/svg+xml;base64,' . base64_encode('<svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M36 0C47.0457 2.57703e-07 56 8.95431 56 20V36C56 47.0457 47.0457 56 36 56H20C8.95431 56 2.5772e-07 47.0457 0 36V20C2.5772e-07 8.95431 8.95431 2.57703e-07 20 0H36ZM27.7305 8C23.7081 8 20.0005 9.16472 16.8457 11.1064C13.2965 13.3589 10.4573 16.6214 8.87988 20.5049C8.64328 21.2039 8.24837 22.3693 8.01172 23.2236C7.93315 23.6119 8.24936 24.0005 8.64355 24.0781C9.11655 24.1555 9.51095 23.9223 9.58984 23.5342C9.66867 23.2236 9.90502 22.6026 9.90527 22.5244C10.063 22.1361 10.4572 21.9032 10.8516 22.0586C11.167 22.2139 11.4033 22.5243 11.4033 22.835C11.3246 22.99 11.4039 22.8353 11.3252 22.9902C11.0097 24.1553 10.6941 25.5536 10.6152 26.874C10.5365 27.2623 10.852 27.6504 11.3252 27.6504C11.7194 27.7278 12.1133 27.3394 12.1133 26.9512C12.1135 26.8721 12.1924 26.1743 12.1924 26.0967C12.429 24.6988 12.823 23.3007 13.375 21.9805C13.4539 21.7475 13.6125 21.5924 13.6914 21.3594C13.8491 21.0488 14.3216 20.8937 14.7158 21.0488H14.7949C15.1893 21.2042 15.3471 21.5922 15.1895 21.9805C15.1106 22.0582 14.6377 22.913 14.3223 24.2334C14.1647 24.6217 14.48 25.0874 14.9531 25.165C15.3474 25.2425 15.6626 25.0093 15.8203 24.6211C16.1358 23.6114 16.6096 22.6016 16.6885 22.4463C17.7927 20.2717 19.5278 18.4853 21.6572 17.3203C23.3923 16.3107 25.4431 15.7666 27.6514 15.7666C34.513 15.7667 40.034 21.2038 40.0342 27.9609C40.0342 34.1744 35.3808 39.2235 29.3867 40.0781V26.7188C29.3867 24.544 27.6508 22.835 25.4424 22.835C23.2342 22.8352 21.499 24.5441 21.499 26.7188V44.1162C21.499 46.2912 23.2342 47.9998 25.4424 48H27.6514C38.851 47.9999 47.9999 39.0681 48 28.0391C48 17.0101 38.9301 8.00021 27.7305 8Z" fill="#4C3FFF"/>
</svg>
');

        add_menu_page(
            __('Divi Torque', 'divitorque'),
            __('Divi Torque', 'divitorque'),
            $this->capability,
            $this->menu_slug,
            [$this, 'render_app'],
            $icon_svg,
            130
        );

        add_submenu_page(
            $this->menu_slug,
            __('Dashboard', 'divitorque'),
            __('Dashboard', 'divitorque'),
            $this->capability,
            $this->menu_slug,
            array($this, 'render_app')
        );

        add_submenu_page(
            $this->menu_slug,
            __('Modules', 'divitorque'),
            __('Modules', 'divitorque'),
            $this->capability,
            "{$this->menu_slug}&path=module-manager",
            [$this, 'render_app']
        );

        // add_submenu_page(
        //     $this->menu_slug,
        //     __('Free vs Pro', 'divitorque'),
        //     __('Free vs Pro', 'divitorque'),
        //     $this->capability,
        //     "{$this->menu_slug}&path=free-vs-pro",
        //     [$this, 'render_app']
        // );
    }

    public function render_app()
    {
        $this->enqueue_scripts();
        echo '<div id="divitorque-root"></div>';
    }

    public function enqueue_scripts()
    {
        $manifest_path = DIVI_TORQUE_LITE_DIR . 'assets/mix-manifest.json';
        if (!file_exists($manifest_path)) {
            return;
        }

        $manifest = json_decode(file_get_contents($manifest_path), true);
        if (!$manifest) {
            return;
        }

        $assets_url = DIVI_TORQUE_LITE_URL . 'assets';
        $dashboard_js = $assets_url . $manifest['/admin/js/dashboard.js'];
        $dashboard_css = $assets_url . $manifest['/admin/css/dashboard.css'];

        wp_enqueue_script(
            'divi-torque-lite-dashboard',
            $dashboard_js,
            $this->wp_deps(),
            DIVI_TORQUE_LITE_VERSION,
            true
        );

        wp_enqueue_style(
            'divi-torque-lite-dashboard',
            $dashboard_css,
            ['wp-components'],
            DIVI_TORQUE_LITE_VERSION
        );

        $localize = [
            'root'              => esc_url_raw(get_rest_url()),
            'admin_slug'        => $this->menu_slug,
            'nonce'             => wp_create_nonce('wp_rest'),
            'assetsPath'        => esc_url_raw($assets_url),
            'version'           => DIVI_TORQUE_LITE_VERSION,
            'module_info'       => ModulesManager::get_all_modules(),
            'pro_module_info'   => ModulesManager::get_all_pro_modules(),
            'module_icon_path'  => DIVI_TORQUE_LITE_URL . 'assets/imgs/icons',
            'isProInstalled'    => AdminHelper::is_pro_installed(),
            'upgradeLink'       => 'https://divitorque.com/pricing-lifetime/?utm_source=divi-torque-lite&utm_medium=wp-admin&utm_campaign=upgrade-to-pro&utm_content=menu-button',
            'currentVersion'   => DIVI_TORQUE_LITE_VERSION,
        ];

        wp_localize_script('divi-torque-lite-dashboard', 'diviTorqueLite', $localize);
    }

    public function admin_enqueue_scripts()
    {
        wp_enqueue_style(
            'divi-torque-lite-admin',
            DIVI_TORQUE_LITE_URL . 'assets/admin/css/admin.css',
            [],
            DIVI_TORQUE_LITE_VERSION
        );
    }

    public function wp_deps()
    {
        return [
            'react',
            'wp-api',
            'wp-i18n',
            'lodash',
            'wp-components',
            'wp-element',
            'wp-api-fetch',
            'wp-core-data',
            'wp-data',
            'wp-dom-ready',
        ];
    }

    private function icon_url()
    {
        return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTYiIGhlaWdodD0iNTYiIHZpZXdCb3g9IjAgMCA1NiA1NiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTM2IDBDNDCUMDQ1NyAyLjU3NzAzZS0wNyA1NiA4Ljk1NDMxIDU2IDIwVjM2QzU2IDQ3LjA0NTcgNDcuMDQ1NyA1NiAzNiA1NkgyMEM4Ljk1NDMxIDU2IDIuNTc3MmUtMDcgNDcuMDQ1NyAwIDM2VjIwQzIuNTc3MmUtMDcgOC45NTQzMSA4Ljk1NDMxIDIuNTc3MDNlLTA3IDIwIDBIMzZaTTI3LjczMDUgOEMyMy43MDgxIDggMjAuMDAwNSA5LjE2NDcyIDE2Ljg0NTcgMTEuMTA2NEMxMy4yOTY1IDEzLjM1ODkgMTAuNDU3MyAxNi42MjE0IDguODc5ODggMjAuNTA0OUM4LjY0MzI4IDIxLjIwMzkgOC4yNDgzNyAyMi4zNjkzIDguMDExNzIgMjMuMjIzNkM3LjkzMzE1IDIzLjYxMTkgOC4yNDkzNiAyNC4wMDA1IDguNjQzNTUgMjQuMDc4MUM5LjExNjU1IDI0LjE1NTUgOS41MTA5NSAyMy45MjIzIDkuNTg5ODQgMjMuNTM0MkM5LjY2ODY3IDIzLjIyMzYgOS45MDUwMiAyMi42MDI2IDkuOTA1MjcgMjIuNTI0NEMxMC4wNjMgMjIuMTM2MSAxMC40NTcyIDIxLjkwMzIgMTAuODUxNiAyMi4wNTg2QzExLjE2NyAyMi4yMTM5IDExLjQwMzMgMjIuNTI0MyAxMS40MDMzIDIyLjgzNUMxMS4zMjQ2IDIyLjk5IDExLjQwMzkgMjIuODM1MyAxMS4zMjUyIDIyLjk5MDJDMTEuMDA5NyAyNC4xNTUzIDEwLjY5NDEgMjUuNTUzNiAxMC42MTUyIDI2Ljg3NEMxMC41MzY1IDI3LjI2MjMgMTAuODUyIDI3LjY1MDQgMTEuMzI1MiAyNy42NTA0QzExLjcxOTQgMjcuNzI3OCAxMi4xMTMzIDI3LjMzOTQgMTIuMTEzMyAyNi45NTEyQzEyLjExMzUgMjYuODcyMSAxMi4xOTI0IDI2LjE3NDMgMTIuMTkyNCAyNi4wOTY3QzEyLjQyOSAyNC42OTg4IDEyLjgyMyAyMy4zMDA3IDEzLjM3NSAyMS45ODA1QzEzLjQ1MzkgMjEuNzQ3NSAxMy42MTI1IDIxLjU5MjQgMTMuNjkxNCAyMS4zNTk0QzEzLjg0OTEgMjEuMDQ4OCAxNC4zMjE2IDIwLjg5MzcgMTQuNzE1OCAyMS4wNDg4SDE0Ljc5NDlDMTUuMTg5MyAyMS4yMDQyIDE1LjM0NzEgMjEuNTkyMiAxNS4xODk1IDIxLjk4MDVDMTUuMTEwNiAyMi4wNTgyIDE0LjYzNzcgMjIuOTEzIDE0LjMyMjMgMjQuMjMzNEMxNC4xNjQ3IDI0LjYyMTcgMTQuNDggMjUuMDg3NCAxNC45NTMxIDI1LjE2NUMxNS4zNDc0IDI1LjI0MjUgMTUuNjYyNiAyNS4wMDkzIDE1LjgyMDMgMjQuNjIxMUMxNi4xMzU4IDIzLjYxMTQgMTYuNjA5NiAyMi42MDE2IDE2LjY4ODUgMjIuNDQ2M0MxNy43OTI3IDIwLjI3MTcgMTkuNTI3OCAxOC40ODUzIDIxLjY1NzIgMTcuMzIwM0MyMy4zOTIzIDE2LjMxMDcgMjUuNDQzMSAxNS43NjY2IDI3LjY1MTQgMTUuNzY2NkMzNC41MTMgMTUuNzY2NyA0MC4wMzQgMjEuMjAzOCA0MC4wMzQyIDI3Ljk2MDlDNDAuMDM0MiAzNC4xNzQ0IDM1LjM4MDggMzkuMjIzNSAyOS4zODY3IDQwLjA3ODFWMjYuNzE4OEMyOS4zODY3IDI0LjU0NCAyNy42NTA4IDIyLjgzNSAyNS40NDI0IDIyLjgzNUMyMy4yMzQyIDIyLjgzNTIgMjEuNDk5IDI0LjU0NDEgMjEuNDk5IDI2LjcxODhWNDQuMTE2MkMyMS40OTkgNDYuMjkxMiAyMy4yMzQyIDQ3Ljk5OTggMjUuNDQyNCA0OEgyNy42NTE0QzM4Ljg1MSA0Ny45OTk5IDQ3Ljk5OTkgMzkuMDY4MSA0OCAyOC4wMzkxQzQ4IDE3LjAxMDEgMzguOTMwMSA4LjAwMDIxIDI3LjczMDUgOFoiIGZpbGw9IiM0QzNGRkYiLz4KPC9zdmc+Cg==';
    }
}
