<?php 
$path = preg_replace('/wp-content.*$/','',__DIR__);
include($path.'wp-load.php'); 
if( ! defined( 'ABSPATH' ) ) exit; 
require_once plugin_dir_path( __DIR__ ) . "vendor/autoload.php"; 
require_once plugin_dir_path( __DIR__ ) . "registrations/add_new_registrant.php"; 
require_once('form_validation.php');

//First grab everything off user's form submission and validate (form_validation.php).
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

//THIS bit probably belongs someplace else....
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
$amount_to_pay = intval(preg_replace("/[^0-9.]/", "", $price_calc));
$sanitized_coupon = "none";



//COUPONS ROUTINE
//Only bother with this lot if coupon has been supplied
if(isset($_POST['coupon']) && !empty($_POST['coupon'])) {

	$sanitized_coupon = strtoupper(stripslashes(strip_tags(trim($_POST['coupon']))));
	$cleaned_form_data['coupon'] = $sanitized_coupon;
	$coupon_result = coupons_routine($sanitized_coupon, $amount_to_pay);

	if ($coupon_result === "badcoupon") {
		header("Location: " . site_url() . "/checkout/?status=error&msg=badcoupon&errs=coupon");
		exit;
	}

	if ($coupon_result === "couponlimit") {
		header("Location: " . site_url() . "/checkout/?status=error&msg=couponlimit&errs=coupon");
		exit();
	}

	if ($coupon_result === "couponnotexist") {
		header("Location: " . site_url() . "/checkout/?status=error&msg=couponnotexist&errs=coupon");
		exit;
	}

	if ($coupon_result === "zerotopay") {	
		//Add user to registered attendees list
		register_new_attendee($cleaned_form_data, 0, "Free entry");
		
		//Send user to success page, bypassing Stripe entirely 
		header("Location: " . site_url() . "/success?coupon=" . $sanitized_coupon);
		exit;
	}

	//Else... update amount to pay with number from coupons routine
	$amount_to_pay = $coupon_result;
}


//Either no coupon was provided, or one was provided but total left to pay is non-zero, so we go to Stripe.
$customer_mail = $cleaned_form_data['email'];
$cleaned_form_data['coupon'] = $sanitized_coupon;
$registration_id = register_new_attendee($cleaned_form_data, $amount_to_pay, "Pending");

$stripe_api_access = get_option('alt_stripe_key');
\Stripe\Stripe::setApiKey($stripe_api_access);

header('Content-Type: application/json');
$YOUR_DOMAIN = site_url();

$event_name = get_option('event_name');
$amount = intval($amount_to_pay*100);

$checkout_session = \Stripe\Checkout\Session::create([
  'customer_email' => $customer_mail,
  'client_reference_id' => $registration_id, // database id of this persons registration
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
  'success_url' => $YOUR_DOMAIN . '/success?coupon=' . $cleaned_form_data['coupon'] . '&session_id={CHECKOUT_SESSION_ID}',
  'cancel_url' => $YOUR_DOMAIN . '/checkout',
]);

header("HTTP/1.1 303 See Other");
header("Location: " . $checkout_session->url);



function coupons_routine($sanitized_coupon, $amount_to_pay) {
	$coupon_discount = 0;

	$invitation_object = get_page_by_title( $sanitized_coupon, OBJECT, 'invitation' );
		
	//BAIL if coupon present in form but NOT in DB
	if($invitation_object === null || empty($invitation_object) || $invitation_object === false) {
		return "badcoupon";
	}

	//Grab meta about invitation
	$invitation_post_id = $invitation_object->ID;
	$max_uses = get_post_meta($invitation_post_id, 'max_uses', true);
	$actual_uses = get_post_meta($invitation_post_id, 'actual_uses', true);
	$discount = get_post_meta($invitation_post_id, 'percentage_value', true);
	$guest_status = get_post_meta($invitation_post_id, 'for_guests', true);

	//Handling cases where data is not set in coupon
	if( empty($max_uses) ) {
		$max_uses = 999;
	}

	if( empty($actual_uses) ) {
		$actual_uses = 0;
	}

	if( empty($discount) ) {
		$discount = 0;
	}

	// Bail if coupon max uses is reached, but only for NON GUEST coupons 
	// (assume nobody is a guest unless explicitly set as guest).
	if($guest_status === false && $actual_uses >= $max_uses) {
		return "couponlimit";
	}

	//Bail if no discount amount set, thus we have a dud coupon.
	if(!isset($discount) || $discount === false) {
		return "couponnotexist";
	}

	//Figure out amount to pay
	$disc_perc = $amount_to_pay / 100; //Full price as a percentage
	$amount_to_discount = $disc_perc * $discount; //value to knock off main price
	$amount_to_pay = $amount_to_pay - $amount_to_discount;//Final price to pay on checkout

	//Bail from stripe session if coupon present and correct and if price to pay is zero
	if($amount_to_pay <= 0) {
		$actual_uses++;
		update_post_meta($invitation_post_id, 'actual_uses', $actual_uses);
		return "zerotopay";
	}
	return $amount_to_pay;
}


