<?php
/**
 * Plugin Name: AIV Contact
 * Description: Reusable contact form plugin for AIV client websites.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Author: AIV-web
 * Text Domain: aiv-contact
 * Domain Path: /languages
 *
 * @package AIV_Contact
 */

defined( 'ABSPATH' ) || exit;

define( 'AIV_CONTACT_VERSION', '1.0.0' );
define( 'AIV_CONTACT_FILE', __FILE__ );
define( 'AIV_CONTACT_PATH', plugin_dir_path( __FILE__ ) );
define( 'AIV_CONTACT_URL', plugin_dir_url( __FILE__ ) );

require_once AIV_CONTACT_PATH . 'includes/bootstrap.php';

register_activation_hook( __FILE__, 'aiv_contact_activate' );
