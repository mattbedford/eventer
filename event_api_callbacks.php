<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function set_the_options($args) {
    $data = $args->get_json_params();

    $permitted = array(
            "event_name",
            "event_payoff",
            "event_date",
            "event_start",
            "event_end",
            "alt_stripe_key",
            "stripe_webhook",
            "event_tag",
            "hubspot_list",
            "hubspot_key",
            "venue_name",
            "venue_address",
            "venue_city",
            "venue_country",
            "max_attendees",
            "badge_template",
            "ticket_price",
            "badge_x",
            "badge_y",
            "badge_x_p2",
            "badge_y_p2",
    );
    $errors = array();

    foreach($data as $key => $val) {
        $new_option = strval($key);
        $new_value = stripslashes(strip_tags(trim($val)));

        // Make sure options are legit
        if(!in_array($new_option, $permitted)) return "Sorry, you submitted a forbidden option";

        // Catch any empty fileds in form submission
        if($new_value == null || empty($new_value)) continue;

        update_option($new_option, $new_value);
        $option_check = get_option($new_option);

        if($option_check !== $new_value) {
            $errors[] = $new_option;
        }
    }

    if(empty($errors)) {
        $return_string = ("All options saved correctly.");
    }
    else {
        $return_string = "Options saved but we found errors in the following fields: ";
        foreach($errors as $err) {
            $return_string .= " " . $err;
        }
        $return_string .= ". Please review and try again";
    }

    echo json_encode($return_string);
    die();
}