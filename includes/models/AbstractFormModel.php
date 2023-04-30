<?php
/**
 * The Abstarct Form Model Class.
 *
 * @package  WP_All_Forms_API
 * @since 1.0.0
 */

namespace Includes\Models;

use Includes\Plugins\Helpers\FormsShortcodeFinder;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class AbstractFormModel
 *
 * Init all routes
 *
 * @since 1.0.0
 */
abstract class AbstractFormModel {

	/**
	 * Get forms by id
	 *
	 * @since 1.0.0
	 *
	 * @param int $id The form id.
	 *
	 * @return object|array $form WPAFA form
	 */
	abstract public function form_by_id( $id );

	/**
	 * Get all forms
	 *
	 * @since 1.0.0
	 *
	 * @param int $offset The offset.
	 * @param int $number_of_records_per_page The posts per page.
	 *
	 * @return array $forms WPAFA forms
	 */
	abstract public function forms( $offset, $number_of_records_per_page );

	/**
	 * Search forms by name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_name The post name.
	 * @param int    $offset The offset.
	 * @param int    $number_of_records_per_page The posts per page.
	 *
	 * @return array $forms WPAFA forms
	 */
	abstract public function search_forms( $post_name, $offset, $number_of_records_per_page );

	/**
	 * Farmat data
	 *
	 * @since 1.0.0
	 *
	 * @param array|object $data The forms data.
	 *
	 * @return array $forms WPAFA forms
	 */
	abstract public function prepare_data( $data );

	/**
	 * Get form pages links
	 *
	 * @since 1.0.0
	 *
	 * @param int    $form_id ID of the form to search for.
	 * @param string $shortcode_name The shortcode to search for.
	 *
	 * @return array $forms WPAFA forms
	 */
	public function pages_links( $form_id, $shortcode_name ) {
		$pages_with_form = ( new FormsShortcodeFinder( $form_id, $shortcode_name ) )->find();

		if ( empty( $pages_with_form ) ) {
			return $pages_with_form;
		}

		$results = array();

		foreach ( $pages_with_form as $key => $value ) {
			$result              = array();
			$result['page_name'] = $value;
			$result['page_link'] = get_page_link( $key );
			$results[]           = $result;
		}

		return $results;
	}

}
