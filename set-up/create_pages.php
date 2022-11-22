<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function run_page_creation() {
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
