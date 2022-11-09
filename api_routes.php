<?php
if ( ! defined( 'ABSPATH' )) exit;


//Create API route for options CRUD - site_url()/wp-json/core-vue/options
add_action('rest_api_init', function () {
    register_rest_route( 'core-vue', '/options',array(
        'methods'  => 'POST',
        'callback' => 'set_the_options',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        }
    ));
  });

