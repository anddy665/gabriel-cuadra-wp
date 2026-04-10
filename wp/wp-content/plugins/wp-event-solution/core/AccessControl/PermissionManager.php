<?php

/**
 * Manage access control
 * 
 * @package Eventin
 */
namespace Eventin\AccessControl;

defined( 'ABSPATH' ) || exit;

use Eventin\Interfaces\HookableInterface;

/**
 * Permission manager class
 */
class PermissionManager implements HookableInterface {
    /**
     * Register all required hooks
     *
     * @return  void
     */
    public function register_hooks(): void {
        add_filter( 'map_meta_cap', [ $this, 'manage_permissions' ], 10, 4 );
    }

    /**
     * Manage permissions
     *
     * @param   array  $caps     [$caps description]
     * @param   string  $cap      [$cap description]
     * @param   [type]  $user_id  [$user_id description]
     * @param   [type]  $args     [$args description]
     *
     * @return  [type]            [return description]
     */
    public function manage_permissions( $caps, $cap, $user_id, $args ) {
        $permissions = Permission::get_role_permissions();
        
        // Get the user’s roles
        $user = get_user_by('id', $user_id);
        if ( ! $user || empty( $user->roles ) ) {
            return $caps; // No roles assigned
        }

        if ( $cap === 'manage_links' ) {
            return $caps; // Skip modifying this capability
        }

        if ( 1 === $user_id ) {
            return ['exist'];
        }

        // Iterate through each role to check permissions
        foreach ( $user->roles as $role ) {
            // If the role has defined permissions in our options
            if ( isset( $permissions[$role] ) && in_array( $cap, $permissions[$role] ) ) {
                // Grant the capability by mapping it to 'exist' or any other basic capability
                return ['exist'];
            }
        }

        return $caps;
    }
}

