<?php
/**
 * The WPF Notification Class.
 *
 * @package  AFA_SUBMISSION_MANAGER
 * @since 1.0.0
 */

namespace Includes\Plugins\Notification;

use Includes\Models\UserDevicesModel;
use Includes\Plugins\Notification\AbstractFormNotification;
use Includes\Models\SupportedPluginsModel;
use Includes\Models\WPF\EntryModel;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class WPFNotification
 *
 * Manipulate WPF Notification
 *
 * @since 1.0.0
 */
class WPFNotification extends AbstractFormNotification {

	/**
	 * Send push notification in bulk
	 *
	 * @param array $meta_value The meta_value of notification.
	 * @param int   $user_id The user ID.
	 *
	 * @return void
	 */
	public function create( $meta_value, $user_id = null ) {

		$supported_plugins_model_register = ( new SupportedPluginsModel() )->get_supported_plugin_by_slug( 'wpf' );
		$supported_plugin_id              = 0;

		if ( ! empty( $supported_plugins_model_register ) ) {
			$supported_plugin_id = $supported_plugins_model_register->id;
		}

		$notification_type_id = $this->get_form_submission_notification_type_id();
		$this->notifiacation->create( $notification_type_id, $meta_value, $supported_plugin_id, $user_id );

		$items = $this->prepare_push_notification( $notification_type_id, $meta_value, $user_id );
		$this->push_notification->push_notification_bulk( $items );
	}

	/**
	 * Prepare push notification for each user
	 *
	 * @param int   $notification_type_id The id of notification type.
	 * @param array $meta_value The meta_value of notification.
	 * @param int   $user_id The user ID.
	 *
	 * @return array
	 */
	private function prepare_push_notification( $notification_type_id, $meta_value, $user_id ) {
		$push_notifications_data = array();

		$divices = ( new UserDevicesModel() )->get_enabled_register_by_notification_type( $notification_type_id );

		foreach ( $divices as $key => $divice ) {

			$item = array();

			// translators: %1$s is replaced with the site name.
			$push_notification_body = sprintf( __( 'New Form Submission Received from %1$s. Open app to view', 'afa-submission-manager' ), get_bloginfo( 'name' ) );

			$title = __( 'New Form Submission', 'afa-submission-manager' );

			$formatted_tiitle = sprintf( '📩 %s', $title );

			$item['title']          = $formatted_tiitle;
			$item['body']           = $push_notification_body;
			$item['exponent_token'] = $divice->expo_token;

			$push_notifications_data[] = $item;

		}

		return $push_notifications_data;
	}

	/**
	 * Load hooks for notifications
	 *
	 * @param array $fields    Sanitized entry field values/properties.
	 * @param array $entry     Original $_POST global.
	 * @param array $form_data Form data and settings.
	 * @param int   $entry_id  Entry ID. Will return 0 if entry storage is disabled or using WPForms Lite.
	 *
	 * @return void
	 */
	public function submission_notification( $fields, $entry, $form_data, $entry_id ) {
		$entry_model = new EntryModel();
		$entry       = $entry_model->entry_by_id( $entry_id );

		if ( ! empty( $entry ) ) {

			$notification_data['entry_id']    = (int) $entry['id'];
			$notification_data['post_type']   = '';
			$notification_data['post_tiltle'] = '';

			$notification_data['user_id'] = null;
			if ( ! empty( $entry['created_by'] ) ) {
				$notification_data['user_id'] = (int) $entry['created_by'];
			}

			$this->create( $notification_data, $notification_data['user_id'] );
		}
	}

	/**
	 * Load hooks for notifications
	 *
	 * @return void
	 */
	public function loads_hooks() {
		add_action( 'wpforms_process_complete', array( $this, 'submission_notification' ), 10, 4 );
	}

}
