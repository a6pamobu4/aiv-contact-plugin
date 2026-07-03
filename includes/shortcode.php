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
 * @param array<string,mixed>|string $atts Shortcode attributes.
 * @return string
 */
function aiv_contact_render_shortcode( $atts = array() ): string {
	static $instance = 0;

	++$instance;

	$atts = is_array( $atts ) ? $atts : array();

	$atts = shortcode_atts(
		array(
			'id'   => 0,
			'slug' => '',
		),
		$atts,
		'aiv_contact_form'
	);

	$form = aiv_contact_resolve_shortcode_form( (int) $atts['id'], (string) $atts['slug'] );

	if ( null === $form ) {
		return current_user_can( 'manage_options' )
			? '<p class="aiv-contact-message">' . esc_html__( 'No AIV contact form is configured yet.', 'aiv-contact' ) . '</p>'
			: '';
	}

	aiv_contact_enqueue_assets();

	$nonce    = wp_create_nonce( 'wp_rest' );
	$id_base  = 'aiv-contact-' . $instance . '-';
	$form_id  = (int) $form['id'];
	$fields   = (array) $form['fields'];
	$button   = '' !== $form['button_text'] ? (string) $form['button_text'] : __( 'Send request', 'aiv-contact' );
	$css_slug = sanitize_html_class( (string) $form['slug'] );

	ob_start();
	?>
	<form class="aiv-contact-form aiv-contact-form-<?php echo esc_attr( $css_slug ); ?>" data-aiv-contact-form method="post" action="<?php echo esc_url( rest_url( 'aiv-contact/v1/submit' ) ); ?>">
		<input type="hidden" name="form_id" value="<?php echo esc_attr( (string) $form_id ); ?>">

		<?php foreach ( $fields as $index => $field ) : ?>
			<?php aiv_contact_render_frontend_field( (array) $field, $id_base . (int) $index ); ?>
		<?php endforeach; ?>

		<div class="aiv-contact-hp" aria-hidden="true">
			<label for="<?php echo esc_attr( $id_base . 'website' ); ?>"><?php esc_html_e( 'Website', 'aiv-contact' ); ?></label>
			<input id="<?php echo esc_attr( $id_base . 'website' ); ?>" name="website" type="text" tabindex="-1" autocomplete="off">
		</div>

		<input type="hidden" name="started_at" value="<?php echo esc_attr( (string) time() ); ?>">
		<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $nonce ); ?>">

		<div class="aiv-contact-status" data-aiv-contact-status aria-live="polite"></div>

		<button class="aiv-contact-submit" type="submit"><?php echo esc_html( $button ); ?></button>
	</form>
	<?php

	return (string) ob_get_clean();
}

/**
 * Resolve a shortcode form from ID, slug, or default.
 *
 * @param int    $form_id Form ID.
 * @param string $slug    Form slug.
 * @return array<string,mixed>|null
 */
function aiv_contact_resolve_shortcode_form( int $form_id, string $slug ): ?array {
	if ( $form_id > 0 ) {
		return aiv_contact_get_form_config( $form_id );
	}

	if ( '' !== $slug ) {
		return aiv_contact_get_form_config_by_slug( $slug );
	}

	return aiv_contact_get_default_form_config();
}

/**
 * Render one configured frontend field.
 *
 * @param array<string,mixed> $field Field config.
 * @param string              $id    Unique field ID.
 */
function aiv_contact_render_frontend_field( array $field, string $id ): void {
	$field       = aiv_contact_sanitize_field_config( $field );
	$name        = (string) $field['name'];
	$type        = (string) $field['type'];
	$label       = (string) $field['label'];
	$placeholder = (string) $field['placeholder'];
	$required    = ! empty( $field['required'] );
	$options     = (array) $field['options'];
	$width       = sanitize_html_class( (string) $field['width'] );

	if ( 'hidden' === $type ) {
		?>
		<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $placeholder ); ?>">
		<?php
		return;
	}
	?>
	<div class="aiv-contact-field aiv-contact-field-<?php echo esc_attr( $width ); ?> aiv-contact-field-type-<?php echo esc_attr( sanitize_html_class( $type ) ); ?>">
		<?php if ( 'checkbox' === $type ) : ?>
			<div class="aiv-contact-consent">
				<input id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" type="checkbox" value="1" <?php echo $required ? 'required' : ''; ?>>
				<label class="aiv-contact-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
			</div>
		<?php elseif ( 'textarea' === $type ) : ?>
			<label class="aiv-contact-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
			<textarea class="aiv-contact-textarea" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="6" placeholder="<?php echo esc_attr( $placeholder ); ?>" <?php echo $required ? 'required' : ''; ?>></textarea>
		<?php elseif ( 'select' === $type ) : ?>
			<label class="aiv-contact-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
			<select class="aiv-contact-select" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" <?php echo $required ? 'required' : ''; ?>>
				<option value=""><?php esc_html_e( 'Select an option', 'aiv-contact' ); ?></option>
				<?php foreach ( $options as $option ) : ?>
					<option value="<?php echo esc_attr( (string) $option ); ?>"><?php echo esc_html( (string) $option ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php elseif ( 'radio-buttons' === $type ) : ?>
			<fieldset class="aiv-contact-radio-group">
				<legend class="aiv-contact-label"><?php echo esc_html( $label ); ?></legend>
				<div class="aiv-contact-radio-buttons">
					<?php foreach ( $options as $option_index => $option ) : ?>
						<?php $option_id = $id . '-' . (int) $option_index; ?>
						<label class="aiv-contact-radio-button" for="<?php echo esc_attr( $option_id ); ?>">
							<input id="<?php echo esc_attr( $option_id ); ?>" name="<?php echo esc_attr( $name ); ?>" type="radio" value="<?php echo esc_attr( (string) $option ); ?>" <?php echo $required ? 'required' : ''; ?>>
							<span><?php echo esc_html( (string) $option ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</fieldset>
		<?php else : ?>
			<label class="aiv-contact-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
			<input class="aiv-contact-input" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" type="<?php echo esc_attr( $type ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" <?php echo $required ? 'required' : ''; ?>>
		<?php endif; ?>
	</div>
	<?php
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
