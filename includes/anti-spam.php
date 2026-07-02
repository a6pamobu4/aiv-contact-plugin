<?php
/**
 * Anti-spam helpers.
 *
 * @package AIV_Contact
 */

defined( 'ABSPATH' ) || exit;

/**
 * Validate honeypot, timing, and rate limits.
 *
 * @param array<string,string> $data Sanitized submission data.
 * @return WP_Error|null
 */
function aiv_contact_validate_anti_spam( array $data ): ?WP_Error {
	if ( '' !== $data['website'] ) {
		return new WP_Error(
			'aiv_contact_spam_detected',
			__( 'The form could not be submitted.', 'aiv-contact' ),
			array( 'status' => 400 )
		);
	}

	if ( ! ctype_digit( $data['started_at'] ) || ( time() - (int) $data['started_at'] ) < 3 ) {
		return new WP_Error(
			'aiv_contact_too_fast',
			__( 'Please wait a moment before submitting the form.', 'aiv-contact' ),
			array( 'status' => 429 )
		);
	}

	$ip_address = aiv_contact_get_ip_address();
	$rate_key   = 'aiv_contact_rate_' . md5( $ip_address );

	if ( false !== get_transient( $rate_key ) ) {
		return new WP_Error(
			'aiv_contact_rate_limited',
			__( 'Please wait before sending another request.', 'aiv-contact' ),
			array( 'status' => 429 )
		);
	}

	set_transient( $rate_key, '1', MINUTE_IN_SECONDS );

	return null;
}

/**
 * Get the visitor IP address for rate limiting.
 *
 * @return string
 */
function aiv_contact_get_ip_address(): string {
	$ip_address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';

	return filter_var( $ip_address, FILTER_VALIDATE_IP ) ? $ip_address : 'unknown';
}
