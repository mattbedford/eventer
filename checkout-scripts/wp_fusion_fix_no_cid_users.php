<?php


/**
 * Adds "No Contact ID" option to batch operations
 *
 * @return array Options
 */

function no_cid_export_options( $options ) {

	$options['no_cid'] = array(
		'label'   => 'Resync Tags (No Contact ID)',
		'title'   => 'Users',
		'tooltip' => 'Resyncs the contact ID and tags just for users that don\'t have a stored contact ID.',
	);

	return $options;

}

add_filter( 'wpf_export_options', 'no_cid_export_options' );


/**
 * No contact ID batch init
 *
 * @return array Users
 */

function no_cid_init() {

	$args = array(
		'fields'     => 'ID',
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key'     => wp_fusion()->crm->slug . '_contact_id',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'   => wp_fusion()->crm->slug . '_contact_id',
				'value' => false,
			),
		),
	);

	$users = get_users( $args );

	return $users;

}

add_action( 'wpf_batch_no_cid_init', 'no_cid_init' );


/**
 * No contact ID batch - single step
 *
 * @return void
 */

function no_cid_step( $user_id ) {

	wp_fusion()->user->get_tags( $user_id, true );

}

add_action( 'wpf_batch_no_cid', 'no_cid_step' );