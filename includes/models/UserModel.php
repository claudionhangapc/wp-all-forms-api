<?php
/**
 * The User Model Class.
 *
 * @package  WP_All_Forms_API
 * @since 1.0.0
 */

namespace Includes\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class UserModel
 *
 * Hendler with user data
 *
 * @since 1.0.0
 */
class UserModel {

	/**
	 * Login user
	 *
	 * @since 1.0.0
	 *
	 * @param string $username The user name.
	 * @param string $password The user password.
	 *
	 * @return WP_User|WP_Error $user WP User object.
	 */
	public function login( $username, $password ) {

		$login = wp_signon(
			array(
				'user_login'    => $username,
				'user_password' => $password,
			)
		);

		if ( ! is_wp_error( $login ) ) {
			if ( ! $this->user_can_manage_wp_afa( $login->ID ) ) {
				return new \WP_Error(
					'invalid_role',
					'Sorry, you are not allowed to login',
					array(
						'status' => 401,
					)
				);
			}
		}

		return $login;
	}

	/**
	 * Get user
	 *
	 * @since 1.0.0
	 *
	 * @return WP_User $user The user data.
	 */
	public function user() {
		global $wp_roles;
		$user  = wp_get_current_user();
		$roles = array();

		foreach ( $user->roles as $key => $role ) {
			$roles[ $role ] = $wp_roles->roles[ $role ]['name'];
		}

		return array(
			'id'              => $user->ID,
			'email'           => $user->data->user_email,
			'display_name'    => $user->data->display_name,
			'first_name'      => $user->user_firstname,
			'last_name'       => $user->user_lastname,
			'user_login'      => $user->data->user_login,
			'roles'           => $roles,
			'avatar_url'      => get_avatar_url( $user->ID ),
			'user_registered' => $user->data->user_registered,
		);
	}

	/**
	 * Get user by id
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id The ID of the user.
	 *
	 * @return WP_User $user Some User info.
	 */
	public function user_info_by_id( $user_id ) {
		if ( empty( $user_id ) ) {
			return array();
		}

		$user = get_user_by( 'ID', $user_id );

		if ( empty( $user ) ) {
			return array();
		}

		$user_info = array();

		$user_info['user_id']    = $user->ID;
		$user_info['user_name']  = $user->display_name;
		$user_info['user_email'] = $user->user_email;
		$user_info['avatar_url'] = get_avatar_url( $user_id, array( 'default' => 'wavatar' ) );

		return $user_info;
	}

	/**
	 * Check if user can manage the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id The ID of the user.
	 *
	 * @return true|false
	 */
	public function user_can_manage_wp_afa( $user_id ) {
		$user = new \WP_User( $user_id );
		if ( $user->exists() ) {
			if ( user_can( $user_id, 'manage_options' ) ) {
				return true;
			}
			if ( in_array( 'wp_afa_staff', $user->roles, true ) ) {
				return true;
			}
			return false;
		}
		return false;
	}

	/**
	 * Add user as staff
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id The ID of the user.
	 *
	 * @return void
	 */
	public function add_staff( $user_id ) {

		$user_can_manage_wp_afa = ( new UserModel() )->user_can_manage_wp_afa( $user_id );

		if ( ! $user_can_manage_wp_afa ) {
			$user = new \WP_User( $user_id );
			if ( $user->exists() ) {
				$user->add_role( 'wp_afa_staff' );
			}
		}

	}

	/**
	 * Remove user as staff
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id The ID of the user.
	 *
	 * @return void
	 */
	public function remove_staff( $user_id ) {
		$user_can_manage_wp_afa = ( new UserModel() )->user_can_manage_wp_afa( $user_id );

		if ( $user_can_manage_wp_afa ) {
			$user = new \WP_User( $user_id );
			if ( $user->exists() ) {
				$user->remove_role( 'wp_afa_staff' );
			}
		}
	}

}
