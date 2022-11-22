<?php
if ( ! defined( 'ABSPATH' )) exit;


//Get all event options - site_url()/wp-json/core-vue/options-all
add_action('rest_api_init', function () {
    register_rest_route( 'core-vue', '/options-all',array(
        'methods'  => 'GET',
        'callback' => 'all_event_options',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        }
    ));
  });


//Post all event options CRUD - site_url()/wp-json/core-vue/options
add_action('rest_api_init', function () {
    register_rest_route( 'core-vue', '/options',array(
        'methods'  => 'POST',
        'callback' => 'set_the_options',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        }
    ));
  });

