<?php
/**
 * REST API handling.
 *
 * @package AIV_Contact
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register contact form REST routes.
 */
function aiv_contact_register_rest_routes(): void {
	register_rest_route(
		'aiv-contact/v1',
		'/submit',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'aiv_contact_handle_rest_submission',
			'permission_callback' => '__return_true',
		)
	);
}

/**
 * Handle REST contact form submissions.
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response|WP_Error
 */
function aiv_contact_handle_rest_submission( WP_REST_Request $request ) {
	$params = $request->get_json_params();

	if ( empty( $params ) ) {
		$params = $request->get_body_params();
	}

	$params  = is_array( $params ) ? $params : array();
	$form_id = isset( $params['form_id'] ) ? absint( $params['form_id'] ) : 0;
	$form    = $form_id > 0 ? aiv_contact_get_form_config( $form_id ) : null;

	if ( null === $form ) {
		return new WP_Error(
			'aiv_contact_invalid_form',
			__( 'The selected form is not available.', 'aiv-contact' ),
			array( 'status' => 404 )
		);
	}

	$data = aiv_contact_sanitize_submission( $params, (array) $form['fields'] );

	if ( ! wp_verify_nonce( $data['_wpnonce'], 'wp_rest' ) ) {
		return new WP_Error(
			'aiv_contact_invalid_nonce',
			__( 'The form session expired. Please refresh the page and try again.', 'aiv-contact' ),
			array( 'status' => 403 )
		);
	}

	$validation_error = aiv_contact_validate_submission( $data, (array) $form['fields'], $params );

	if ( $validation_error instanceof WP_Error ) {
		return $validation_error;
	}

	$spam_error = aiv_contact_validate_anti_spam( $data );

	if ( $spam_error instanceof WP_Error ) {
		return $spam_error;
	}

	if ( ! aiv_contact_send_email( $form, $data ) ) {
		return new WP_Error(
			'aiv_contact_mail_failed',
			__( 'The request could not be sent. Please try again later.', 'aiv-contact' ),
			array( 'status' => 500 )
		);
	}

	return rest_ensure_response(
		array(
			'success' => true,
			'message' => (string) $form['success_message'],
		)
	);
}
