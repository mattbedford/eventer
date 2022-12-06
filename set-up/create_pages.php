<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function run_page_creation() {
   create_new_checkout();
   create_new_success_page();
}

function create_new_checkout() {
    // Setup custom vars
    $post_id = -1;
    $author_id = get_current_user_id();
    $slug = 'checkout';
    $title = 'Checkout';

    // Check if page exists, if not create it
    if ( null == get_page_by_title( $title )) {

        $args = array(
                'comment_status'        => 'closed',
                'ping_status'           => 'closed',
                'post_author'           => $author_id,
                'post_name'             => $slug,
                'post_title'            => $title,
                'post_status'           => 'publish',
                'post_type'             => 'page'
        );

        $post_id = wp_insert_post( $args );
    }
}

function create_new_success_page() {
    // Setup custom vars
    $post_id = -1;
    $author_id = get_current_user_id();
    $slug = 'success';
    $title = 'Success';

    // Check if page exists, if not create it
    if ( null == get_page_by_title( $title )) {

        $args = array(
                'comment_status'        => 'closed',
                'ping_status'           => 'closed',
                'post_author'           => $author_id,
                'post_name'             => $slug,
                'post_title'            => $title,
                'post_status'           => 'publish',
                'post_type'             => 'page'
        );

        $post_id = wp_insert_post( $args );
    }
}