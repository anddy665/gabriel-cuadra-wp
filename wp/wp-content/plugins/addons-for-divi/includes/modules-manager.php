<?php

namespace DiviTorqueLite;

use DiviTorqueLite\AdminHelper;

class ModulesManager
{
    private static $instance;

    private $modules_pro = [];

    private $modules_lite = [
        [
            'name' => 'icon-box',
            'title' => 'Icon Box',
            'icon' => 'icon-box.svg',
            'child_name' => '',
            'demo_link' => '',
        ],
        [
            'name' => 'contact-form7',
            'title' => 'Contact Form 7',
            'icon' => 'contact-forms.svg',
            'child_name' => '',
            'demo_link' => ''
        ],
        [
            'name' => 'divider',
            'title' => 'Divider',
            'icon' => 'divider.svg',
            'child_name' => '',
            'demo_link' => ''
        ],
        [
            'name' => 'skill-bar',
            'title' => 'Skill Bar',
            'icon' => 'progress-bar.svg',
            'child_name' => 'skill-bar-child',
            'demo_link' => ''
        ],
        [
            'name' => 'logo-grid',
            'title' => 'Logo Grid',
            'icon' => 'grid.svg',
            'child_name' => 'logo-grid-child',
            'demo_link' => ''
        ],
        [
            'name' => 'team-box',
            'title' => 'Person',
            'icon' => 'team.svg',
            'child_name' => '',
            'demo_link' => ''
        ],
        [
            'name' => 'testimonial',
            'title' => 'Testimonial',
            'icon' => 'testimonial.svg',
            'child_name' => '',
            'demo_link' => '#255'
        ],
        [
            'name' => 'info-card',
            'title' => 'Info Card',
            'icon' => 'info-box.svg',
            'child_name' => '',
            'demo_link' => '#324',
        ],
        [
            'name' => 'dual-button',
            'title' => 'Dual Button',
            'icon' => 'button-group.svg',
            'child_name' => '',
            'demo_link' => '#325'
        ],
        [
            'name' => 'compare-image',
            'title' => 'Before & After Slider',
            'icon' => 'before-after.svg',
            'child_name' => '',
            'demo_link' => '#297',
        ],
        [
            'name' => 'image-carousel',
            'title' => 'Image Carousel',
            'icon' => 'image-carousel.svg',
            'child_name' => 'image-carousel-child',
        ],
        [
            'name' => 'logo-carousel',
            'title' => 'Logo Carousel',
            'icon' => 'carousel.svg',
            'child_name' => 'logo-carousel-child',
        ],
        [
            'name' => 'video-modal',
            'title' => 'Video Modal',
            'icon' => 'video-popup.svg',
            'child_name' => '',
        ],
        [
            'name' => 'info-box',
            'title' => 'Info Box',
            'icon' => 'info-box.svg',
            'child_name' => '',
        ],
        [
            'name' => 'scroll-image',
            'title' => 'Scroll Image',
            'icon' => 'image.svg',
            'child_name' => '',
        ],
        [
            'name' => 'news-ticker',
            'title' => 'News Ticker',
            'icon' => 'ticker.svg',
            'child_name' => '',
        ],
        [
            'name' => 'post-list',
            'title' => 'Post List',
            'icon' => 'post-list.svg',
            'child_name' => '',
            'demo_link' => ''
        ],
        [
            'name' => 'review',
            'title' => 'Review Box',
            'icon' => 'rating.svg',
            'child_name' => '',
        ],
        [
            'name' => 'flip-box',
            'title' => 'Flip Box',
            'icon' => 'flip-box.svg',
            'child_name' => '',
        ],
        [
            'name' => 'animated-text',
            'title' => 'Animated Text',
            'icon' => 'text.svg',
            'child_name' => '',
        ],
        [
            'name' => 'business-hour',
            'title' => 'Business Hours',
            'icon' => 'business-hours.svg',
            'child_name' => 'business-hour-child',
            'demo_link' => ''
        ],
        [
            'name' => 'gradient-heading',
            'title' => 'Gradient Heading',
            'icon' => 'heading.svg',
            'child_name' => '',
        ],
        [
            'name' => 'inline-notice',
            'title' => 'Inline Notice',
            'icon' => 'inline-notice.svg',
            'child_name' => '',
        ],
    ];

    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        add_action('et_builder_ready', [$this, 'load_modules'], 9);
    }

    public static function get_all_pro_modules()
    {
        return self::get_instance()->modules_pro;
    }

    public static function get_all_modules()
    {
        return self::get_instance()->modules_lite;
    }

    public function load_modules()
    {
        if (!class_exists(\ET_Builder_Element::class)) {
            return;
        }

        $active_modules = $this->active_modules();

        foreach ($active_modules as $module_name => $module) {
            $module_path = sprintf('%1$s/modules/divi-4/%2$s/%2$s.php', __DIR__, str_replace('-', '', ucwords($module_name, '-')));

            if (file_exists($module_path)) {
                require_once $module_path;
            }
        }
    }

    public function active_modules()
    {
        $all_modules = self::get_all_modules();
        $saved_modules = AdminHelper::get_modules();
        $active_modules = [];

        foreach ($all_modules as $module) {
            if (in_array($module['name'], $saved_modules)) {
                $active_modules[$module['name']] = $module;
                if (!empty($module['child_name'])) {
                    $active_modules[$module['child_name']] = $module;
                }
            }
        }

        return $active_modules;
    }
}
