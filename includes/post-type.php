<?php
/**
 * AIV form post type.
 *
 * @package AIV_Contact
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the AIV forms post type.
 */
function aiv_contact_register_post_type(): void {
	register_post_type(
		'aiv_contact_form',
		array(
			'labels'       => array(
				'name'               => __( 'AIV Forms', 'aiv-contact' ),
				'singular_name'      => __( 'AIV Form', 'aiv-contact' ),
				'add_new_item'       => __( 'Add New AIV Form', 'aiv-contact' ),
				'edit_item'          => __( 'Edit AIV Form', 'aiv-contact' ),
				'new_item'           => __( 'New AIV Form', 'aiv-contact' ),
				'view_item'          => __( 'View AIV Form', 'aiv-contact' ),
				'search_items'       => __( 'Search AIV Forms', 'aiv-contact' ),
				'not_found'          => __( 'No AIV forms found.', 'aiv-contact' ),
				'not_found_in_trash' => __( 'No AIV forms found in Trash.', 'aiv-contact' ),
				'menu_name'          => __( 'AIV Forms', 'aiv-contact' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => true,
			'supports'     => array( 'title' ),
			'menu_icon'    => 'dashicons-email-alt2',
		)
	);
}
