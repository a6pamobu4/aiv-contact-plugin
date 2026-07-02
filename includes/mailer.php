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
 * @param array<string,string> $data Sanitized submission data.
 * @return bool
 */
function aiv_contact_send_email( array $data ): bool {
	$recipient = defined( 'AIV_CONTACT_RECIPIENT_EMAIL' ) ? AIV_CONTACT_RECIPIENT_EMAIL : get_option( 'admin_email' );
	$recipient = apply_filters( 'aiv_contact_recipient_email', $recipient, $data );
	$recipient = sanitize_email( (string) $recipient );

	if ( ! is_email( $recipient ) ) {
		return false;
	}

	$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$subject   = sprintf(
		/* translators: %s: Site name. */
		__( 'Новая заявка с сайта: %s', 'aiv-contact' ),
		$site_name
	);

	$body = implode(
		"\n\n",
		array(
			sprintf(
				/* translators: %s: Submitted name. */
				__( 'Name: %s', 'aiv-contact' ),
				$data['name']
			),
			sprintf(
				/* translators: %s: Submitted contact details. */
				__( 'Contact: %s', 'aiv-contact' ),
				$data['contact']
			),
			sprintf(
				/* translators: %s: Selected project type. */
				__( 'Project type: %s', 'aiv-contact' ),
				$data['project_type']
			),
			sprintf(
				/* translators: %s: Submitted message. */
				__( 'Message: %s', 'aiv-contact' ),
				$data['message']
			),
		)
	);

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

	if ( is_email( $data['contact'] ) ) {
		$headers[] = 'Reply-To: ' . sanitize_email( $data['contact'] );
	}

	return wp_mail( $recipient, $subject, $body, $headers );
}
