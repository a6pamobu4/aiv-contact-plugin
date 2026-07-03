<?php
/**
 * Form configuration helpers.
 *
 * @package AIV_Contact
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get supported frontend field types.
 *
 * @return string[]
 */
function aiv_contact_get_supported_field_types(): array {
	return array( 'text', 'tel', 'email', 'textarea', 'select', 'radio-buttons', 'checkbox', 'hidden' );
}

/**
 * Get supported field widths.
 *
 * @return string[]
 */
function aiv_contact_get_supported_field_widths(): array {
	return array( 'full', 'half', 'third', 'two-thirds' );
}

/**
 * Get default field configuration.
 *
 * @return array<int,array<string,mixed>>
 */
function aiv_contact_get_default_fields(): array {
	return array(
		array(
			'label'       => 'Имя',
			'name'        => 'name',
			'type'        => 'text',
			'required'    => true,
			'placeholder' => 'Ваше имя',
			'options'     => array(),
			'width'       => 'full',
		),
		array(
			'label'       => 'Телефон',
			'name'        => 'phone',
			'type'        => 'tel',
			'required'    => true,
			'placeholder' => '+7',
			'options'     => array(),
			'width'       => 'full',
		),
		array(
			'label'       => 'Как вам удобнее ответить?',
			'name'        => 'contact_method',
			'type'        => 'radio-buttons',
			'required'    => true,
			'placeholder' => '',
			'options'     => array( 'WhatsApp', 'Telegram', 'Email', 'MAX', 'Телефон' ),
			'width'       => 'full',
		),
		array(
			'label'       => 'Email',
			'name'        => 'email',
			'type'        => 'email',
			'required'    => false,
			'placeholder' => 'email@example.com',
			'options'     => array(),
			'width'       => 'full',
		),
		array(
			'label'       => 'Комментарий',
			'name'        => 'message',
			'type'        => 'textarea',
			'required'    => false,
			'placeholder' => 'Коротко опишите задачу',
			'options'     => array(),
			'width'       => 'full',
		),
		array(
			'label'       => 'Согласие на обработку данных',
			'name'        => 'consent',
			'type'        => 'checkbox',
			'required'    => true,
			'placeholder' => '',
			'options'     => array(),
			'width'       => 'full',
		),
	);
}

/**
 * Get default form settings.
 *
 * @return array<string,mixed>
 */
function aiv_contact_get_default_form_settings(): array {
	return array(
		'title'           => 'Основная форма',
		'slug'            => 'main',
		'recipients'      => get_option( 'admin_email' ),
		'subject'         => 'Новая заявка с сайта: {site_name}',
		'success_message' => 'Спасибо! Заявка отправлена. Мы свяжемся с вами в ближайшее время.',
		'button_text'     => 'Отправить заявку',
		'fields'          => aiv_contact_get_default_fields(),
	);
}

/**
 * Get a normalized form config by post ID.
 *
 * @param int $form_id Form post ID.
 * @return array<string,mixed>|null
 */
function aiv_contact_get_form_config( int $form_id ): ?array {
	$post = get_post( $form_id );

	if ( ! $post instanceof WP_Post || 'aiv_contact_form' !== $post->post_type || 'publish' !== $post->post_status ) {
		return null;
	}

	$default = aiv_contact_get_default_form_settings();
	$fields  = get_post_meta( $form_id, '_aiv_contact_fields', true );

	return array(
		'id'              => $form_id,
		'title'           => get_the_title( $form_id ),
		'slug'            => aiv_contact_normalize_slug( (string) get_post_meta( $form_id, '_aiv_contact_form_slug', true ) ),
		'recipients'      => (string) get_post_meta( $form_id, '_aiv_contact_recipients', true ),
		'subject'         => aiv_contact_get_meta_with_default( $form_id, '_aiv_contact_subject', (string) $default['subject'] ),
		'success_message' => aiv_contact_get_meta_with_default( $form_id, '_aiv_contact_success_message', (string) $default['success_message'] ),
		'button_text'     => aiv_contact_get_meta_with_default( $form_id, '_aiv_contact_button_text', (string) $default['button_text'] ),
		'fields'          => aiv_contact_normalize_fields( is_array( $fields ) ? $fields : array() ),
	);
}

/**
 * Get meta value with a default fallback.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @param string $fallback Fallback value.
 * @return string
 */
function aiv_contact_get_meta_with_default( int $post_id, string $key, string $fallback ): string {
	$value = (string) get_post_meta( $post_id, $key, true );

	return '' !== $value ? $value : $fallback;
}

/**
 * Find the default form.
 *
 * @return array<string,mixed>|null
 */
function aiv_contact_get_default_form_config(): ?array {
	$form = aiv_contact_get_form_config_by_slug( 'main' );

	if ( null !== $form ) {
		return $form;
	}

	$forms = get_posts(
		array(
			'post_type'      => 'aiv_contact_form',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'fields'         => 'ids',
		)
	);

	if ( empty( $forms ) ) {
		return null;
	}

	return aiv_contact_get_form_config( (int) $forms[0] );
}

/**
 * Find a form by stored slug.
 *
 * @param string $slug Form slug.
 * @return array<string,mixed>|null
 */
function aiv_contact_get_form_config_by_slug( string $slug ): ?array {
	$slug  = aiv_contact_normalize_slug( $slug );
	$forms = get_posts(
		array(
			'post_type'      => 'aiv_contact_form',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_key'       => '_aiv_contact_form_slug',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'meta_value'     => $slug,
			'fields'         => 'ids',
		)
	);

	if ( empty( $forms ) ) {
		return null;
	}

	return aiv_contact_get_form_config( (int) $forms[0] );
}

/**
 * Normalize form slug.
 *
 * @param string $slug Raw slug.
 * @return string
 */
function aiv_contact_normalize_slug( string $slug ): string {
	return sanitize_title( $slug );
}

/**
 * Normalize a list of field configs.
 *
 * @param array<int|string,mixed> $fields Raw fields.
 * @return array<int,array<string,mixed>>
 */
function aiv_contact_normalize_fields( array $fields ): array {
	$normalized = array();

	foreach ( $fields as $field ) {
		if ( ! is_array( $field ) ) {
			continue;
		}

		$sanitized = aiv_contact_sanitize_field_config( $field );

		if ( '' === $sanitized['label'] || '' === $sanitized['name'] ) {
			continue;
		}

		$normalized[] = $sanitized;
	}

	return $normalized;
}

/**
 * Sanitize a single field config.
 *
 * @param array<string,mixed> $field Raw field config.
 * @return array<string,mixed>
 */
function aiv_contact_sanitize_field_config( array $field ): array {
	$type  = isset( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : 'text';
	$width = isset( $field['width'] ) ? sanitize_key( (string) $field['width'] ) : 'full';

	if ( ! in_array( $type, aiv_contact_get_supported_field_types(), true ) ) {
		$type = 'text';
	}

	if ( ! in_array( $width, aiv_contact_get_supported_field_widths(), true ) ) {
		$width = 'full';
	}

	return array(
		'label'       => isset( $field['label'] ) ? sanitize_text_field( (string) $field['label'] ) : '',
		'name'        => isset( $field['name'] ) ? sanitize_key( (string) $field['name'] ) : '',
		'type'        => $type,
		'required'    => ! empty( $field['required'] ),
		'placeholder' => isset( $field['placeholder'] ) ? sanitize_text_field( (string) $field['placeholder'] ) : '',
		'options'     => aiv_contact_sanitize_options( $field['options'] ?? array() ),
		'width'       => $width,
	);
}

/**
 * Sanitize options from textarea or array input.
 *
 * @param mixed $options Raw options.
 * @return string[]
 */
function aiv_contact_sanitize_options( $options ): array {
	if ( is_string( $options ) ) {
		$options = preg_split( '/\r\n|\r|\n/', $options );
	}

	if ( ! is_array( $options ) ) {
		return array();
	}

	$sanitized = array();

	foreach ( $options as $option ) {
		$option = sanitize_text_field( (string) $option );

		if ( '' !== $option ) {
			$sanitized[] = $option;
		}
	}

	return array_values( array_unique( $sanitized ) );
}
