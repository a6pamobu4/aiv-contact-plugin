<?php
/**
 * Admin meta boxes.
 *
 * @package AIV_Contact
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register form configuration meta boxes.
 */
function aiv_contact_register_meta_boxes(): void {
	add_meta_box(
		'aiv-contact-settings',
		__( 'Form settings', 'aiv-contact' ),
		'aiv_contact_render_settings_meta_box',
		'aiv_contact_form',
		'normal',
		'high'
	);

	add_meta_box(
		'aiv-contact-fields',
		__( 'Form fields', 'aiv-contact' ),
		'aiv_contact_render_fields_meta_box',
		'aiv_contact_form',
		'normal',
		'default'
	);
}

/**
 * Render settings meta box.
 *
 * @param WP_Post $post Current post.
 */
function aiv_contact_render_settings_meta_box( WP_Post $post ): void {
	$default         = aiv_contact_get_default_form_settings();
	$slug            = aiv_contact_get_meta_with_default( $post->ID, '_aiv_contact_form_slug', (string) $default['slug'] );
	$recipients      = aiv_contact_get_meta_with_default( $post->ID, '_aiv_contact_recipients', (string) $default['recipients'] );
	$subject         = aiv_contact_get_meta_with_default( $post->ID, '_aiv_contact_subject', (string) $default['subject'] );
	$success_message = aiv_contact_get_meta_with_default( $post->ID, '_aiv_contact_success_message', (string) $default['success_message'] );
	$button_text     = aiv_contact_get_meta_with_default( $post->ID, '_aiv_contact_button_text', (string) $default['button_text'] );

	wp_nonce_field( 'aiv_contact_save_form', 'aiv_contact_meta_nonce' );
	?>
	<div class="aiv-contact-admin-grid">
		<p class="aiv-contact-admin-field">
			<label for="aiv-contact-form-slug"><?php esc_html_e( 'Form slug', 'aiv-contact' ); ?></label>
			<input id="aiv-contact-form-slug" name="_aiv_contact_form_slug" type="text" value="<?php echo esc_attr( $slug ); ?>">
		</p>
		<p class="aiv-contact-admin-field">
			<label for="aiv-contact-recipients"><?php esc_html_e( 'Recipients', 'aiv-contact' ); ?></label>
			<input id="aiv-contact-recipients" name="_aiv_contact_recipients" type="text" value="<?php echo esc_attr( $recipients ); ?>">
		</p>
		<p class="aiv-contact-admin-field">
			<label for="aiv-contact-subject"><?php esc_html_e( 'Email subject', 'aiv-contact' ); ?></label>
			<input id="aiv-contact-subject" name="_aiv_contact_subject" type="text" value="<?php echo esc_attr( $subject ); ?>">
		</p>
		<p class="aiv-contact-admin-field">
			<label for="aiv-contact-success-message"><?php esc_html_e( 'Success message', 'aiv-contact' ); ?></label>
			<textarea id="aiv-contact-success-message" name="_aiv_contact_success_message" rows="3"><?php echo esc_textarea( $success_message ); ?></textarea>
		</p>
		<p class="aiv-contact-admin-field">
			<label for="aiv-contact-button-text"><?php esc_html_e( 'Submit button text', 'aiv-contact' ); ?></label>
			<input id="aiv-contact-button-text" name="_aiv_contact_button_text" type="text" value="<?php echo esc_attr( $button_text ); ?>">
		</p>
	</div>
	<?php
}

/**
 * Render fields meta box.
 *
 * @param WP_Post $post Current post.
 */
function aiv_contact_render_fields_meta_box( WP_Post $post ): void {
	$fields = get_post_meta( $post->ID, '_aiv_contact_fields', true );
	$fields = aiv_contact_normalize_fields( is_array( $fields ) ? $fields : aiv_contact_get_default_fields() );

	if ( empty( $fields ) ) {
		$fields = aiv_contact_get_default_fields();
	}
	?>
	<div class="aiv-contact-field-builder" data-aiv-contact-field-builder>
		<div class="aiv-contact-field-list" data-aiv-contact-field-list>
			<?php foreach ( $fields as $index => $field ) : ?>
				<?php aiv_contact_render_field_builder_item( $field, (int) $index ); ?>
			<?php endforeach; ?>
		</div>

		<button class="button aiv-contact-add-field" type="button" data-aiv-contact-add-field><?php esc_html_e( 'Add field', 'aiv-contact' ); ?></button>

		<template data-aiv-contact-field-template>
			<?php aiv_contact_render_field_builder_item( aiv_contact_get_empty_field_config(), '__index__' ); ?>
		</template>
	</div>
	<?php
}

/**
 * Render one field-builder row.
 *
 * @param array<string,mixed> $field Field config.
 * @param int|string          $index Field index.
 */
function aiv_contact_render_field_builder_item( array $field, $index ): void {
	$field = aiv_contact_sanitize_field_config( $field );
	$base  = '_aiv_contact_fields[' . $index . ']';
	?>
	<div class="aiv-contact-field-item" data-aiv-contact-field-item>
		<div class="aiv-contact-field-item-header">
			<strong><?php esc_html_e( 'Field', 'aiv-contact' ); ?></strong>
			<button class="button-link-delete" type="button" data-aiv-contact-remove-field><?php esc_html_e( 'Remove', 'aiv-contact' ); ?></button>
		</div>

		<div class="aiv-contact-field-grid">
			<p class="aiv-contact-admin-field">
				<label><?php esc_html_e( 'Label', 'aiv-contact' ); ?></label>
				<input name="<?php echo esc_attr( $base . '[label]' ); ?>" type="text" value="<?php echo esc_attr( (string) $field['label'] ); ?>">
			</p>
			<p class="aiv-contact-admin-field">
				<label><?php esc_html_e( 'Name', 'aiv-contact' ); ?></label>
				<input name="<?php echo esc_attr( $base . '[name]' ); ?>" type="text" value="<?php echo esc_attr( (string) $field['name'] ); ?>">
			</p>
			<p class="aiv-contact-admin-field">
				<label><?php esc_html_e( 'Type', 'aiv-contact' ); ?></label>
				<select name="<?php echo esc_attr( $base . '[type]' ); ?>">
					<?php foreach ( aiv_contact_get_supported_field_types() as $type ) : ?>
						<option value="<?php echo esc_attr( $type ); ?>" <?php selected( $field['type'], $type ); ?>><?php echo esc_html( $type ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p class="aiv-contact-admin-field">
				<label><?php esc_html_e( 'Width', 'aiv-contact' ); ?></label>
				<select name="<?php echo esc_attr( $base . '[width]' ); ?>">
					<?php foreach ( aiv_contact_get_supported_field_widths() as $width ) : ?>
						<option value="<?php echo esc_attr( $width ); ?>" <?php selected( $field['width'], $width ); ?>><?php echo esc_html( $width ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p class="aiv-contact-admin-field">
				<label><?php esc_html_e( 'Placeholder', 'aiv-contact' ); ?></label>
				<input name="<?php echo esc_attr( $base . '[placeholder]' ); ?>" type="text" value="<?php echo esc_attr( (string) $field['placeholder'] ); ?>">
			</p>
			<p class="aiv-contact-admin-field aiv-contact-admin-field-checkbox">
				<label>
					<input name="<?php echo esc_attr( $base . '[required]' ); ?>" type="checkbox" value="1" <?php checked( ! empty( $field['required'] ) ); ?>>
					<?php esc_html_e( 'Required', 'aiv-contact' ); ?>
				</label>
			</p>
			<p class="aiv-contact-admin-field aiv-contact-admin-field-wide">
				<label><?php esc_html_e( 'Options', 'aiv-contact' ); ?></label>
				<textarea name="<?php echo esc_attr( $base . '[options]' ); ?>" rows="4"><?php echo esc_textarea( implode( "\n", (array) $field['options'] ) ); ?></textarea>
			</p>
		</div>
	</div>
	<?php
}

/**
 * Get an empty field config for the builder template.
 *
 * @return array<string,mixed>
 */
function aiv_contact_get_empty_field_config(): array {
	return array(
		'label'       => '',
		'name'        => '',
		'type'        => 'text',
		'required'    => false,
		'placeholder' => '',
		'options'     => array(),
		'width'       => 'full',
	);
}

/**
 * Save form meta boxes.
 *
 * @param int $post_id Post ID.
 */
function aiv_contact_save_meta_boxes( int $post_id ): void {
	if ( ! aiv_contact_can_save_meta_box( $post_id ) ) {
		return;
	}

	$slug            = aiv_contact_normalize_slug( aiv_contact_get_posted_string( '_aiv_contact_form_slug' ) );
	$recipients      = sanitize_text_field( aiv_contact_get_posted_string( '_aiv_contact_recipients' ) );
	$subject         = sanitize_text_field( aiv_contact_get_posted_string( '_aiv_contact_subject' ) );
	$success_message = sanitize_textarea_field( aiv_contact_get_posted_string( '_aiv_contact_success_message' ) );
	$button_text     = sanitize_text_field( aiv_contact_get_posted_string( '_aiv_contact_button_text' ) );
	$raw_fields      = aiv_contact_get_posted_array( '_aiv_contact_fields' );
	$fields          = aiv_contact_normalize_fields( $raw_fields );

	update_post_meta( $post_id, '_aiv_contact_form_slug', '' !== $slug ? $slug : 'form-' . $post_id );
	update_post_meta( $post_id, '_aiv_contact_recipients', $recipients );
	update_post_meta( $post_id, '_aiv_contact_subject', $subject );
	update_post_meta( $post_id, '_aiv_contact_success_message', $success_message );
	update_post_meta( $post_id, '_aiv_contact_button_text', $button_text );
	update_post_meta( $post_id, '_aiv_contact_fields', $fields );
}

/**
 * Check whether meta box data can be saved.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function aiv_contact_can_save_meta_box( int $post_id ): bool {
	if ( ! isset( $_POST['aiv_contact_meta_nonce'] ) ) {
		return false;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST['aiv_contact_meta_nonce'] ) );

	if ( ! wp_verify_nonce( $nonce, 'aiv_contact_save_form' ) ) {
		return false;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}

	return current_user_can( 'edit_post', $post_id );
}

/**
 * Get a posted string value.
 *
 * @param string $key Input key.
 * @return string
 */
function aiv_contact_get_posted_string( string $key ): string {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! isset( $_POST[ $key ] ) || is_array( $_POST[ $key ] ) ) {
		return '';
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	return (string) wp_unslash( $_POST[ $key ] );
}

/**
 * Get a posted array value.
 *
 * @param string $key Input key.
 * @return array<int|string,mixed>
 */
function aiv_contact_get_posted_array( string $key ): array {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! isset( $_POST[ $key ] ) || ! is_array( $_POST[ $key ] ) ) {
		return array();
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	return (array) wp_unslash( $_POST[ $key ] );
}

/**
 * Enqueue admin-only assets for form edit screens.
 *
 * @param string $hook_suffix Current admin hook suffix.
 */
function aiv_contact_enqueue_admin_assets( string $hook_suffix ): void {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || 'aiv_contact_form' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_style(
		'aiv-contact-admin',
		AIV_CONTACT_URL . 'assets/css/admin.css',
		array(),
		AIV_CONTACT_VERSION
	);

	wp_enqueue_script(
		'aiv-contact-admin',
		AIV_CONTACT_URL . 'assets/js/admin-form-builder.js',
		array(),
		AIV_CONTACT_VERSION,
		true
	);
}
