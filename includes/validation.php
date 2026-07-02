<?php
/**
 * Submission validation and sanitization.
 *
 * @package AIV_Contact
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return available project type labels.
 *
 * @return string[]
 */
function aiv_contact_get_project_type_options(): array {
	return array(
		'Разработка сайта',
		'SEO-продвижение',
		'Контекстная реклама',
		'Поддержка сайта',
		'Пока не знаю',
	);
}

/**
 * Sanitize submitted contact data.
 *
 * @param array<string,mixed> $data Raw submission data.
 * @return array<string,string>
 */
function aiv_contact_sanitize_submission( array $data ): array {
	return array(
		'name'         => isset( $data['name'] ) ? sanitize_text_field( wp_unslash( $data['name'] ) ) : '',
		'contact'      => isset( $data['contact'] ) ? sanitize_text_field( wp_unslash( $data['contact'] ) ) : '',
		'project_type' => isset( $data['project_type'] ) ? sanitize_text_field( wp_unslash( $data['project_type'] ) ) : '',
		'message'      => isset( $data['message'] ) ? sanitize_textarea_field( wp_unslash( $data['message'] ) ) : '',
		'consent'      => isset( $data['consent'] ) ? sanitize_text_field( wp_unslash( $data['consent'] ) ) : '',
		'website'      => isset( $data['website'] ) ? sanitize_text_field( wp_unslash( $data['website'] ) ) : '',
		'started_at'   => isset( $data['started_at'] ) ? sanitize_text_field( wp_unslash( $data['started_at'] ) ) : '',
		'_wpnonce'     => isset( $data['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $data['_wpnonce'] ) ) : '',
	);
}

/**
 * Validate sanitized contact data.
 *
 * @param array<string,string> $data Sanitized submission data.
 * @return WP_Error|null
 */
function aiv_contact_validate_submission( array $data ): ?WP_Error {
	if ( '' === $data['name'] || '' === $data['contact'] || '' === $data['project_type'] || '' === $data['message'] ) {
		return new WP_Error(
			'aiv_contact_required_fields',
			__( 'Please complete all required fields.', 'aiv-contact' ),
			array( 'status' => 400 )
		);
	}

	if ( '1' !== $data['consent'] ) {
		return new WP_Error(
			'aiv_contact_consent_required',
			__( 'Please confirm your consent before sending the form.', 'aiv-contact' ),
			array( 'status' => 400 )
		);
	}

	if ( ! in_array( $data['project_type'], aiv_contact_get_project_type_options(), true ) ) {
		return new WP_Error(
			'aiv_contact_invalid_project_type',
			__( 'Please select a valid project type.', 'aiv-contact' ),
			array( 'status' => 400 )
		);
	}

	return null;
}
