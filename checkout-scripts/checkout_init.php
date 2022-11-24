<?php 
$path = preg_replace('/wp-content.*$/','',__DIR__);
include($path.'wp-load.php'); 
if( ! defined( 'ABSPATH' ) ) exit; 
require_once("vendor/autoload.php"); 

//First grab everything off user's form submission.
//validate all with: stripslashes(strip_tags(trim($code)));
require_once('form_validation.php');
$form_is_valid = validate_the_form($_POST);

//form_is_valid is an array with 3 elements: [0] = status, [1] = problem, [2] = afflicted fields 
//OR, if [0] is not "error" then [2] has sanitized fields (BUT NO COUPON CODE... watch out. That's handled separately below.)
if($form_is_valid[0] === "error") {
	$errs = implode(",", $form_is_valid[2]);
	$msg = $form_is_valid[1];
	header("Location: " . site_url() . "/checkout/?status=error&msg=" . $msg . "&fields=" . $errs);
	exit;
}
$cleaned_form_data = $form_is_valid[2];

//Squash tags into a string for sending
$tag_string = "";
if(!empty($_POST['tags'])) {
	foreach($_POST['tags'] as $tag_item) {
		$tag_string .= stripslashes(strip_tags(trim($tag_item))) . ",";
	}
}
$cleaned_form_data['tags'] = preg_replace("/,$/", '', $tag_string);
	
//OK. Form is valid. Let's move on.
$price_calc = get_option('ticket_price');
$amount_to_pay = preg_replace("/[^0-9.]/", "", $price_calc);

//Only bother with this lot if coupon has been supplied
if(isset($_POST['coupon']) && !empty($_POST['coupon'])) {
	//Set up coupon vars
	$coupons_list = array();
	$coupons_lookup = array();
	$coupon_discount = 0;

	//Grab existing coupons from DB if any.
	$invitation_args = array(
		"post_type" => 'invitation'
		"numberposts" => -1,
		'meta_query' => array(
			array(
				'key'   => 'invitation_status',
				'value' => 'live',
			)
		)
	);
	$db_coupons = get_posts($invitation_args);
	foreach ( $db_coupons as $row ) {
		$single_code = strtoupper($row->post_title);
		if($single_code != "") {
			$coupons_list[] = $row->post_title;
			$coupons_lookup[strtoupper($row->post_title)] = $row->percent_value;
		}
	}

	//Check if coupon is present and valid
	//First off though, validate it
	if(isset($_POST['coupon']) && $_POST['coupon'] != "") {
		$sanitized_coupon = strtoupper(stripslashes(strip_tags(trim($_POST['coupon']))));
	}

	//BAIL if coupon present in form but NOT in DB
	if(!in_array($sanitized_coupon, $coupons_list)) {
		header("Location: " . site_url() . "/checkout/?status=error&msg=badcoupon&errs=coupon");
		exit;
	}


	//max uses check routine: Bail if coupon max uses is reached, but only for NON GUEST coupons.
	

	
	if($guest_status === false && $our_coupon_uses >= $our_coupon_limit) {
		header("Location: " . site_url() . "/checkout/?status=error&msg=couponlimit&errs=coupon");
		exit();
	}
	//End coupon max uses check routine
	

	//Check discount amount of supplied coupon. If no discount set we have a dud coupon: bail.
	$supplied_coupon = strtoupper($sanitized_coupon);
	$coupon_discount = $coupons_lookup[$supplied_coupon];
	if(!isset($coupon_discount)) {
		header("Location: " . site_url() . "/checkout/?status=error&msg=couponnotexist&errs=coupon");
		exit;
	}
	
	
	//Figure out amount to apy
	$filtered_ticket_price = $amount_to_pay; //750

	$disc_perc = $filtered_ticket_price / 100; //Full price as a percentage
	$amount_to_discount = $disc_perc * $coupon_discount; //value to knock off main price
	$amount_to_pay = $filtered_ticket_price - $amount_to_discount;//Final price to pay on checkout

	//Bail from stripe session if coupon present and correct and if price to pay is non-zero
	if(isset($supplied_coupon) && in_array($supplied_coupon, $coupons_list) && $amount_to_pay <= 0) {
		//Look up coupon and adjust uses to +1
		//Add user to hubspot registration list
		require_once('update_coupon_count.php');
		add_one_coupon_usage($supplied_coupon);
		
		//Add user to registered attendees list
		require_once 'user_registration_script.php';
		$registration_data = array();
		$registration_data['fname'] = $form_is_valid[2]['fname'];
		$registration_data['lname'] = $form_is_valid[2]['lname'];
		$registration_data['email'] = $form_is_valid[2]['email'];
		$registration_data['role'] = $form_is_valid[2]['role'];
		$registration_data['company'] = $form_is_valid[2]['company'];
		$registration_data['coupon'] = $supplied_coupon;
		add_new_registration($registration_data);
		
		//connect newly-registered user to the hs registration list 
		set_up_and_send_list_add($hs_response->vid);
		header("Location: " . site_url() . "/success?coupon=" . $supplied_coupon);
		exit;
	}
} else {
	$supplied_coupon = "none";
}

//Either no coupon was provided, or one was provided but total left to pay is non-zero, so we go to Stripe.
$customer_mail = $form_is_valid[2]['email'];

$stripe_api_access = get_option('alt_stripe_key');
\Stripe\Stripe::setApiKey($stripe_api_access);

header('Content-Type: application/json');
$YOUR_DOMAIN = site_url();

$event_name = get_option('event_name');
$real_amount = $amount_to_pay;
$amount = intval($real_amount*100);

$checkout_session = \Stripe\Checkout\Session::create([
  'customer_email' => $customer_mail,
  'client_reference_id' => $hs_response->vid,
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
  'success_url' => $YOUR_DOMAIN . '/success?coupon=' . $supplied_coupon . '&session_id={CHECKOUT_SESSION_ID}',
  'cancel_url' => $YOUR_DOMAIN . '/checkout',
]);

header("HTTP/1.1 303 See Other");
header("Location: " . $checkout_session->url);

