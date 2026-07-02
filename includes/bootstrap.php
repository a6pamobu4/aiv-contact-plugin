<?php
/**
 * Plugin bootstrap.
 *
 * @package AIV_Contact
 */

defined( 'ABSPATH' ) || exit;

require_once AIV_CONTACT_PATH . 'includes/validation.php';
require_once AIV_CONTACT_PATH . 'includes/anti-spam.php';
require_once AIV_CONTACT_PATH . 'includes/mailer.php';
require_once AIV_CONTACT_PATH . 'includes/rest.php';
require_once AIV_CONTACT_PATH . 'includes/shortcode.php';

add_action( 'init', 'aiv_contact_load_textdomain' );
add_action( 'init', 'aiv_contact_register_shortcode' );
add_action( 'rest_api_init', 'aiv_contact_register_rest_routes' );

/**
 * Load plugin translations.
 */
function aiv_contact_load_textdomain(): void {
	load_plugin_textdomain(
		'aiv-contact',
		false,
		dirname( plugin_basename( AIV_CONTACT_FILE ) ) . '/languages'
	);
}
