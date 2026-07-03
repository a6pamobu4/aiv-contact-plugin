<?php
/**
 * Activation tasks.
 *
 * @package AIV_Contact
 */

defined( 'ABSPATH' ) || exit;

/**
 * Run plugin activation tasks.
 */
function aiv_contact_activate(): void {
	aiv_contact_register_post_type();
	aiv_contact_create_default_form();
	flush_rewrite_rules();
}

/**
 * Create the default form if it does not already exist.
 */
function aiv_contact_create_default_form(): void {
	if ( null !== aiv_contact_get_form_config_by_slug( 'main' ) ) {
		return;
	}

	$default = aiv_contact_get_default_form_settings();
	$form_id = wp_insert_post(
		array(
			'post_type'   => 'aiv_contact_form',
			'post_status' => 'publish',
			'post_title'  => (string) $default['title'],
		),
		true
	);

	if ( is_wp_error( $form_id ) || 0 === $form_id ) {
		return;
	}

	update_post_meta( $form_id, '_aiv_contact_form_slug', $default['slug'] );
	update_post_meta( $form_id, '_aiv_contact_recipients', $default['recipients'] );
	update_post_meta( $form_id, '_aiv_contact_subject', $default['subject'] );
	update_post_meta( $form_id, '_aiv_contact_success_message', $default['success_message'] );
	update_post_meta( $form_id, '_aiv_contact_button_text', $default['button_text'] );
	update_post_meta( $form_id, '_aiv_contact_fields', $default['fields'] );
}
