<?php 
$path = preg_replace('/wp-content.*$/','',__DIR__);
include($path.'wp-load.php'); 
if( ! defined( 'ABSPATH' ) ) exit; 

// Include classes
require_once('EventerRegistrations.php');
require_once('HubspotTool.php');
require_once('CouponValidator.php');

// Include dependencies
require_once plugin_dir_path( __DIR__ ) . "vendor/autoload.php"; 
require_once('form_validation.php');


// Validate form
$form_is_valid = validate_the_form($_POST);

// Return if errors else register new person
if($form_is_valid[0] === "error") send_back_with_errors($form_is_valid); 
$form_data = $form_is_valid[2];


// Add user to database (also generate their hubspot ID)
$registration = new EventerRegistration($form_data);

// Figure out default amount to pay
$p = get_option('ticket_price');
$amount_to_pay = intval(preg_replace("/[^0-9.]/", "", $p));

// Check validity of their coupon if present
if(isset($form_data['coupon']) && !empty($form_data['coupon'])) {

    $coupon_instance = new CouponValidator($form_data['coupon']);
    $coupon_result = $coupon_instance->coupon_result;

    switch ($coupon_result) {
        case 'badcoupon':
            header("Location: " . site_url() . "/checkout/?status=error&msg=badcoupon&errs=coupon");
            exit;
            break;
        
        case 'couponlimit':
            header("Location: " . site_url() . "/checkout/?status=error&msg=couponlimit&errs=coupon");
            exit;
            break;

        case 'couponnotexist':
            header("Location: " . site_url() . "/checkout/?status=error&msg=couponnotexist&errs=coupon");
            exit;
            break;

        case 'zerotopay':
            $registration->confirmFreeUser();
            header("Location: " . site_url() . "/success?coupon=" . $coupon_instance->coupon_code . "&session_id=" . $registration->registration_id);
            exit;
            break;

        default:
            $amount_to_pay = $coupon_result;
            break;
    }
	do_stripe_routine($registration, $coupon_instance);
} else {
	do_stripe_routine($registration);
}

// If we got this far, we're going to Stripe




function do_stripe_routine($reg_obj, $coupon_obj = null) {
        
    $stripe_api_access = get_option('alt_stripe_key');

    \Stripe\Stripe::setApiKey($stripe_api_access);

    header('Content-Type: application/json');
    $domain = site_url();
    $event_name = get_option('event_name');
    $email = $reg_obj->data['email'];

	if($coupon_obj !== null) {
    	$amount = intval($coupon_obj->coupon_result * 100);
		$the_code = $coupon_obj->coupon_code;
	} else {
		$p = get_option('ticket_price');
		$amount = intval(preg_replace("/[^0-9.]/", "", $p)) * 100;
		$the_code = "no-code";
	}
	
    $checkout_session = \Stripe\Checkout\Session::create([
    'customer_email' => $email,
    'invoice_creation' => ['enabled' => true],
    'client_reference_id' => $reg_obj->registration_id,
    'line_items' => [[
        'price_data' => [
            'currency' => 'chf',
            'product_data' => [
            'name' => "Entrance to Dagorà " . $event_name,
            ],
            'unit_amount' => $amount,
        ],
        'quantity' => 1,
    ]],
    'mode' => 'payment',
    'success_url' => $domain . '/success?coupon=' . $the_code . '&session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => $domain . '/checkout',
    ]);

    header("HTTP/1.1 303 See Other");
    header("Location: " . $checkout_session->url);
    exit; // Not sure if we need this final exit...
}


function send_back_with_errors($form_is_valid){
	$errs = implode('&fields[]=', $form_is_valid[2]);
	$msg = $form_is_valid[1];
	header("Location: " . site_url() . "/checkout/?status=error&msg=" . $msg . '&fields[]=' . $errs);
	exit;
}
