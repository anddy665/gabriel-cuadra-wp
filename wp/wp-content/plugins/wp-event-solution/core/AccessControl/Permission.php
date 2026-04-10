<?php

/**
 * Manage access control
 * 
 * @package Eventin
 */
namespace Eventin\AccessControl;

defined( 'ABSPATH' ) || exit;

/**
 * Permission Class
 */
class Permission {
    /**
     * Store permissions store settings key
     *
     * @var string
     */
    private static $settings_key = 'etn_permissions';

    /**
     * Get all permission list
     *
     * @return  array Permissions for the roles
     */
    public static function get() {
        return self::permissions();
    }

    /**
     * Get roles and permissions
     *
     * @return  array  Roles and permission list
     */
    public static function get_role_permissions() {
        $defaults = self::default_role_permissions();
        
        $settings = apply_filters( 'eventin_role_permissions', $defaults );

        if ( get_option( self::$settings_key ) === false ) {
            $settings = wp_parse_args( $settings, $defaults );
        }
        
        return $settings;
    }
    
    /**
     * Get roles with role label
     *
     * @return  array
     */
    public static function get_roles() {
        global $wp_roles;
        $all_roles = $wp_roles->roles;

        $permissions = self::get_role_permissions();

        // Build the combined array of roles and permissions
        $roles_permissions = [];

        foreach ( $all_roles as $role_key => $role_details ) {
            $roles_permissions[$role_key] = [
                'name'        => $role_key,
                'label'       => $role_details['name'],
                'permissions' => isset( $permissions[$role_key] ) ? array_values( $permissions[$role_key] ) : []
            ];
        }

        return $roles_permissions;
    }

    /**
     * Add permission for a certain user
     *
     * @param   string  $role         User role
     * @param   array  $permissions  Role permission list
     *
     * @return  bool
     */
    public static function  add( $role, $permissions ) {
        $settings = self::get_settings();

        $defaults = self::default_role_permissions();

        if ( get_option( self::$settings_key ) === false ) {
            $settings = array_merge( $settings, $defaults );
            $settings[$role] = $permissions;
        } else {
            $settings[$role] = $permissions;
        }

        $updated = update_option( self::$settings_key, $settings );

        return true;
    }

    /**
     * Remove permissions from a certain user
     *
     * @param   string  $role         Role to assign permissions
     * @param   array  $permissions  Permissions list to assing a role
     *
     * @return  bool
     */
    public static function remove( $role, $permissions ) {
        $role_permissions = self::find( $role );
    
        if ( ! $role_permissions ) {
            return false;
        }

        $updated_permissions = array_diff( $role_permissions, $permissions );
        
        return self::add( $role, $updated_permissions );
    }

    /**
     * Remove role from database
     *
     * @param   string  $role  [$role description]
     *
     * @return  bool
     */
    public static function remove_role( $role ) {
        $settings = self::get_settings();

        $defaults = self::default_role_permissions();

        if ( get_option( self::$settings_key ) === false ) {
            $settings = array_merge( $settings, $defaults );
        }

        if ( isset( $settings[$role] ) ) {
            unset( $settings[$role] );
        }

        $updated = update_option( self::$settings_key, $settings );

        return true;
    }

    /**
     * Find permissions for a certain role
     *
     * @param   string  $role  Role of a user
     *
     * @return  array         Get a role and permissions otherwise null
     */
    public static function find( $role ) {
        $settings = self::get_role_permissions();

        return array_key_exists( $role, $settings ) ? $settings[$role] : null;
    }

    /**
     * Check a permissions is valid or not
     *
     * @param   array  $permissions
     *
     * @return  bool    Check permissions return true otherwise false 
     */
    public static function is_valid( $permissions ) {
        return empty( array_diff( $permissions, array_keys( self::permissions() ) ) );
    }

    /**
     * Check a role is valid or not
     *
     * @param   string  $role  Role to check validity
     *
     * @return  bool         If a role is valid return true otherwise false
     */
    public static function is_valid_role( $role ) {
        global $wp_roles;

        $roles = array_keys( $wp_roles->roles );

        return in_array( $role, $roles );
    }

    /**
     * Get settings from store permissions
     *
     * @return  array Get Roles and Permissions from database store
     */
    public static function get_settings( $defaults = [] ) {
        $permissions = get_option( self::$settings_key, $defaults );

        return $permissions;
    }

    /**
     * Get role settings
     *
     * @return  array  Role settings that store on database
     */
    public static function get_role_settings() {
        $defaults = self::default_role_permissions();

        $role_settings = self::get_settings( $defaults );

        $roles = self::get_roles();

        $role_permissions = [];

        foreach( $role_settings as $role_name => $role ) {
            if ( isset( $roles[$role_name] ) ) {
                $role_permissions[$role_name] = $roles[$role_name];
            }
        }

        return $role_permissions;
    }

    /**
     * Permissions for eventin pages and rest api
     *
     * @return  array  Permission list
     */
    private static function permissions() {
        return [
            'etn_manage_dashboard'     => __( 'Dashboard', 'eventin' ),
            'etn_manage_template'      => __( 'Template', 'eventin' ),
            'etn_manage_event'         => __( 'Events', 'eventin' ),
            'etn_manage_organizer'     => __( 'Organizer', 'eventin' ),
            'etn_manage_schedule'      => __( 'Schedules', 'eventin' ),
            'etn_manage_order'         => __( 'Orders', 'eventin' ),
            'etn_manage_attendee'      => __( 'Attendees', 'eventin' ),
            'etn_manage_shortcode'     => __( 'Shortcodes', 'eventin' ),
            'etn_manage_setting'       => __( 'Settings', 'eventin' ),
            'etn_manage_license'       => __( 'License', 'eventin' ),
            'etn_manage_addons'        => __( 'Extensions', 'eventin' ),
            'etn_manage_our_plugins'   => __( 'Our Plugins', 'eventin' ),
            'etn_manage_get_help'      => __( 'Get Help', 'eventin' ),
            'etn_manage_go_pro'        => __( 'Upgrade to Pro', 'eventin' ),
            'etn_manage_qr_scan'       => __( 'QR Scan Access', 'eventin' ),
        ];
    }

    /**
     * Raw permission keys without translation.
     *
     * @return array
     */
    private static function permission_keys() {
        return [
            'etn_manage_dashboard',
            'etn_manage_template',
            'etn_manage_event',
            'etn_manage_organizer',
            'etn_manage_schedule',
            'etn_manage_order',
            'etn_manage_attendee',
            'etn_manage_shortcode',
            'etn_manage_setting',
            'etn_manage_license',
            'etn_manage_addons',
            'etn_manage_our_plugins',
            'etn_manage_get_help',
            'etn_manage_go_pro',
            'etn_manage_qr_scan',
        ];
    }

    /**
     * Default permissions for all roles
     *
     * @return  array  Permissions
     */
    private static function default_role_permissions() {

        $permissions = [
            'administrator' => self::permission_keys(),
            'editor'        => [
                'etn_manage_event',
                'etn_manage_organizer',
                'etn_manage_schedule',
                'etn_manage_shortcode',
                'etn_manage_setting',
                'etn_manage_get_help',
            ],
            'seller'        => [
                'etn_manage_event',
                'etn_manage_organizer',
                'etn_manage_schedule',
                'etn_manage_shortcode',
                'etn_manage_setting',
                'etn_manage_get_help',
            ],
            'author'        => [
                'etn_manage_event',
                'etn_manage_organizer',
                'etn_manage_schedule',
            ],
            'contributor'   => [
                'etn_manage_event',
                'etn_manage_organizer',
                'etn_manage_schedule',
            ],
        ];

        return $permissions;
    }
}
