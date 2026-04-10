<?php

namespace DiviTorqueLite;

use DiviTorqueLite\ModulesManager;

class AdminHelper
{

    public static function get_common_settings()
    {
        return [
            'modules_settings' => self::get_modules(),
        ];
    }

    public static function get_options()
    {
        $general_settings = self::get_common_settings();

        return apply_filters('divitorque_lite_global_data_options', $general_settings);
    }

    public static function get_modules()
    {
        $all_modules = ModulesManager::get_all_modules();
        $default_modules = [];

        foreach ($all_modules as $name => $value) {
            $_name = $value['name'];
            $default_modules[$_name] = $_name;
        }

        if (self::is_pro_installed()) {
            $saved_modules = get_option('_divitorque_modules', []);
        } else {
            $saved_modules = get_option('_divitorque_lite_modules', []);
        }

        return wp_parse_args($saved_modules, $default_modules);
    }

    public static function is_pro_installed()
    {
        return defined('DTP_VERSION');
    }
}
