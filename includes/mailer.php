<?php
/**
 * Email sending.
 *
 * @package AIV_Contact
 */

defined( 'ABSPATH' ) || exit;

/**
 * Send the contact form email.
 *
 * @param array<string,mixed> $form Form config.
 * @param array<string,mixed> $data Sanitized submission data.
 * @return bool
 */
function aiv_contact_send_email( array $form, array $data ): bool {
	$recipients = aiv_contact_get_valid_recipients( (string) $form['recipients'], $form, $data );

	if ( empty( $recipients ) ) {
		return false;
	}

	$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$subject   = str_replace( '{site_name}', $site_name, (string) $form['subject'] );
	$body      = aiv_contact_build_email_body( $form, $data );
	$headers   = array( 'Content-Type: text/plain; charset=UTF-8' );
	$reply_to  = aiv_contact_get_reply_to_email( $form, $data );

	if ( '' !== $reply_to ) {
		$headers[] = 'Reply-To: ' . $reply_to;
	}

	return wp_mail( $recipients, $subject, $body, $headers );
}

/**
 * Resolve and validate recipient emails.
 *
 * @param string              $recipient_string Raw recipients.
 * @param array<string,mixed> $form             Form config.
 * @param array<string,mixed> $data             Submission data.
 * @return string[]
 */
function aiv_contact_get_valid_recipients( string $recipient_string, array $form, array $data ): array {
	$recipient_string = defined( 'AIV_CONTACT_RECIPIENT_EMAIL' ) ? (string) AIV_CONTACT_RECIPIENT_EMAIL : $recipient_string;
	$recipient_string = (string) apply_filters( 'aiv_contact_recipient_email', $recipient_string, $data, $form );
	$recipients       = array_map( 'trim', explode( ',', $recipient_string ) );
	$valid            = array();

	foreach ( $recipients as $recipient ) {
		$recipient = sanitize_email( $recipient );

		if ( is_email( $recipient ) ) {
			$valid[] = $recipient;
		}
	}

	if ( empty( $valid ) ) {
		$admin_email = sanitize_email( (string) get_option( 'admin_email' ) );

		if ( is_email( $admin_email ) ) {
			$valid[] = $admin_email;
		}
	}

	return array_values( array_unique( $valid ) );
}

/**
 * Build dynamic plain text email body.
 *
 * @param array<string,mixed> $form Form config.
 * @param array<string,mixed> $data Submission data.
 * @return string
 */
function aiv_contact_build_email_body( array $form, array $data ): string {
	$lines = array();

	foreach ( (array) $form['fields'] as $field ) {
		$field = aiv_contact_sanitize_field_config( (array) $field );
		$name  = (string) $field['name'];

		if ( 'hidden' === $field['type'] ) {
			continue;
		}

		$value = (string) ( $data['fields'][ $name ] ?? '' );

		if ( '' === $value ) {
			$value = __( '(empty)', 'aiv-contact' );
		}

		$lines[] = sprintf(
			'%1$s: %2$s',
			(string) $field['label'],
			$value
		);
	}

	return implode( "\n\n", $lines );
}

/**
 * Get the first submitted valid email for Reply-To.
 *
 * @param array<string,mixed> $form Form config.
 * @param array<string,mixed> $data Submission data.
 * @return string
 */
function aiv_contact_get_reply_to_email( array $form, array $data ): string {
	foreach ( (array) $form['fields'] as $field ) {
		$field = aiv_contact_sanitize_field_config( (array) $field );

		if ( 'email' !== $field['type'] ) {
			continue;
		}

		$value = sanitize_email( (string) ( $data['fields'][ (string) $field['name'] ] ?? '' ) );

		if ( is_email( $value ) ) {
			return $value;
		}
	}

	return '';
}
