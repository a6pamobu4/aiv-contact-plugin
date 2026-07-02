<?php
/**
 * Contact form shortcode.
 *
 * @package AIV_Contact
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the contact form shortcode.
 */
function aiv_contact_register_shortcode(): void {
	add_shortcode( 'aiv_contact_form', 'aiv_contact_render_shortcode' );
}

/**
 * Render the contact form shortcode.
 *
 * @return string
 */
function aiv_contact_render_shortcode(): string {
	static $instance = 0;

	++$instance;

	aiv_contact_enqueue_assets();

	$nonce    = wp_create_nonce( 'wp_rest' );
	$options  = aiv_contact_get_project_type_options();
	$id_base  = 'aiv-contact-' . $instance;
	$field_id = array(
		'name'         => $id_base . '-name',
		'contact'      => $id_base . '-contact',
		'project_type' => $id_base . '-project-type',
		'message'      => $id_base . '-message',
		'consent'      => $id_base . '-consent',
		'website'      => $id_base . '-website',
	);

	ob_start();
	?>
	<form class="aiv-contact-form" data-aiv-contact-form method="post" action="<?php echo esc_url( rest_url( 'aiv-contact/v1/submit' ) ); ?>">
		<div class="aiv-contact-field">
			<label class="aiv-contact-label" for="<?php echo esc_attr( $field_id['name'] ); ?>"><?php esc_html_e( 'Name', 'aiv-contact' ); ?></label>
			<input class="aiv-contact-input" id="<?php echo esc_attr( $field_id['name'] ); ?>" name="name" type="text" autocomplete="name" required>
		</div>

		<div class="aiv-contact-field">
			<label class="aiv-contact-label" for="<?php echo esc_attr( $field_id['contact'] ); ?>"><?php esc_html_e( 'Contact', 'aiv-contact' ); ?></label>
			<input class="aiv-contact-input" id="<?php echo esc_attr( $field_id['contact'] ); ?>" name="contact" type="text" autocomplete="email" required>
		</div>

		<div class="aiv-contact-field">
			<label class="aiv-contact-label" for="<?php echo esc_attr( $field_id['project_type'] ); ?>"><?php esc_html_e( 'Project type', 'aiv-contact' ); ?></label>
			<select class="aiv-contact-select" id="<?php echo esc_attr( $field_id['project_type'] ); ?>" name="project_type" required>
				<option value=""><?php esc_html_e( 'Select project type', 'aiv-contact' ); ?></option>
				<?php foreach ( $options as $option ) : ?>
					<option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="aiv-contact-field">
			<label class="aiv-contact-label" for="<?php echo esc_attr( $field_id['message'] ); ?>"><?php esc_html_e( 'Message', 'aiv-contact' ); ?></label>
			<textarea class="aiv-contact-textarea" id="<?php echo esc_attr( $field_id['message'] ); ?>" name="message" rows="6" required></textarea>
		</div>

		<div class="aiv-contact-field aiv-contact-consent">
			<input id="<?php echo esc_attr( $field_id['consent'] ); ?>" name="consent" type="checkbox" value="1" required>
			<label class="aiv-contact-label" for="<?php echo esc_attr( $field_id['consent'] ); ?>"><?php esc_html_e( 'I consent to being contacted about this request.', 'aiv-contact' ); ?></label>
		</div>

		<div class="aiv-contact-hp" aria-hidden="true">
			<label for="<?php echo esc_attr( $field_id['website'] ); ?>"><?php esc_html_e( 'Website', 'aiv-contact' ); ?></label>
			<input id="<?php echo esc_attr( $field_id['website'] ); ?>" name="website" type="text" tabindex="-1" autocomplete="off">
		</div>

		<input type="hidden" name="started_at" value="<?php echo esc_attr( (string) time() ); ?>">
		<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $nonce ); ?>">

		<div class="aiv-contact-status" data-aiv-contact-status aria-live="polite"></div>

		<button class="aiv-contact-submit" type="submit"><?php esc_html_e( 'Send request', 'aiv-contact' ); ?></button>
	</form>
	<?php

	return (string) ob_get_clean();
}

/**
 * Enqueue shortcode assets.
 */
function aiv_contact_enqueue_assets(): void {
	wp_register_style(
		'aiv-contact-form',
		AIV_CONTACT_URL . 'assets/css/contact-form.css',
		array(),
		AIV_CONTACT_VERSION
	);

	wp_register_script(
		'aiv-contact-form',
		AIV_CONTACT_URL . 'assets/js/contact-form.js',
		array(),
		AIV_CONTACT_VERSION,
		true
	);

	wp_add_inline_script(
		'aiv-contact-form',
		'window.aivContactForm = ' . wp_json_encode(
			array(
				'restUrl' => esc_url_raw( rest_url( 'aiv-contact/v1/submit' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		) . ';',
		'before'
	);

	wp_enqueue_style( 'aiv-contact-form' );
	wp_enqueue_script( 'aiv-contact-form' );
}
