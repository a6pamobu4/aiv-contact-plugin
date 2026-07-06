<?php
/**
 * Submission validation and sanitization.
 *
 * @package AIV_Contact
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sanitize submitted contact data.
 *
 * @param array<string,mixed>     $data   Raw submission data.
 * @param array<int,array<mixed>> $fields Form fields.
 * @return array<string,mixed>
 */
function aiv_contact_sanitize_submission( array $data, array $fields ): array {
	$sanitized = array(
		'form_id'    => isset( $data['form_id'] ) ? absint( $data['form_id'] ) : 0,
		'website'    => isset( $data['website'] ) && ! is_array( $data['website'] ) ? sanitize_text_field( wp_unslash( $data['website'] ) ) : '',
		'started_at' => isset( $data['started_at'] ) && ! is_array( $data['started_at'] ) ? sanitize_text_field( wp_unslash( $data['started_at'] ) ) : '',
		'_wpnonce'   => isset( $data['_wpnonce'] ) && ! is_array( $data['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $data['_wpnonce'] ) ) : '',
		'fields'     => array(),
	);

	foreach ( aiv_contact_normalize_fields( $fields ) as $field ) {
		$name  = (string) $field['name'];
		$value = $data[ $name ] ?? '';

		$sanitized['fields'][ $name ] = aiv_contact_sanitize_field_value( $value, (string) $field['type'] );
	}

	return $sanitized;
}

/**
 * Sanitize a field value by field type.
 *
 * @param mixed  $value Raw value.
 * @param string $type  Field type.
 * @return string
 */
function aiv_contact_sanitize_field_value( $value, string $type ): string {
	if ( is_array( $value ) ) {
		return '';
	}

	$value = (string) wp_unslash( $value );

	if ( 'textarea' === $type ) {
		return sanitize_textarea_field( $value );
	}

	if ( 'email' === $type ) {
		return sanitize_email( $value );
	}

	if ( 'tel' === $type ) {
		return trim( preg_replace( '/[^0-9+\-\s().]/', '', $value ) ?? '' );
	}

	if ( 'checkbox' === $type ) {
		return '1' === $value ? '1' : '';
	}

	return sanitize_text_field( $value );
}

/**
 * Validate sanitized contact data.
 *
 * @param array<string,mixed>     $data   Sanitized submission data.
 * @param array<int,array<mixed>> $fields Form fields.
 * @param array<string,mixed>     $params Raw params.
 * @return WP_Error|null
 */
function aiv_contact_validate_submission( array $data, array $fields, array $params ): ?WP_Error {
	$fields          = aiv_contact_normalize_fields( $fields );
	$allowed_keys    = array_merge( array( 'form_id', 'website', 'started_at', '_wpnonce' ), wp_list_pluck( $fields, 'name' ) );
	$unexpected_keys = array_diff( array_keys( $params ), $allowed_keys );

	if ( ! empty( $unexpected_keys ) ) {
		return new WP_Error(
			'aiv_contact_unexpected_fields',
			__( 'The form contains unexpected fields.', 'aiv-contact' ),
			array( 'status' => 400 )
		);
	}

	foreach ( $fields as $field ) {
		$name      = (string) $field['name'];
		$type      = (string) $field['type'];
		$label     = (string) $field['label'];
		$value     = (string) ( $data['fields'][ $name ] ?? '' );
		$required  = ! empty( $field['required'] );
		$is_active = aiv_contact_is_conditional_field_active( $field, (array) $data['fields'] );

		if ( ! $is_active ) {
			if ( '' !== $value ) {
				return new WP_Error(
					'aiv_contact_inactive_field',
					__( 'The form contains a value for an inactive field.', 'aiv-contact' ),
					array( 'status' => 400 )
				);
			}

			continue;
		}

		if ( $required && '' === $value ) {
			return new WP_Error(
				'aiv_contact_required_field',
				sprintf(
					/* translators: %s: Field label. */
					__( 'Please complete the required field: %s.', 'aiv-contact' ),
					$label
				),
				array( 'status' => 400 )
			);
		}

		if ( 'email' === $type && '' !== $value && ! is_email( $value ) ) {
			return new WP_Error(
				'aiv_contact_invalid_email',
				__( 'Please enter a valid email address.', 'aiv-contact' ),
				array( 'status' => 400 )
			);
		}

		if ( in_array( $type, array( 'select', 'radio-buttons' ), true ) && '' !== $value && ! in_array( $value, (array) $field['options'], true ) ) {
			return new WP_Error(
				'aiv_contact_invalid_option',
				__( 'Please choose a valid option.', 'aiv-contact' ),
				array( 'status' => 400 )
			);
		}
	}

	return null;
}
