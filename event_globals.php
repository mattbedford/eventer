<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


//Adding correct template page for checkout
function checkout_setup( $template ) {

	if(get_option('ticket_price') === false) return;
	if ( is_page( 'checkout' )) {

			$plugindir = dirname( __FILE__ );
			$template = $plugindir . '/templates/checkout_template.php';
			wp_register_style('checkout-styles', plugins_url('/assets/checkout_styles.css',__FILE__ ));
			wp_register_script( 'checkout-scripts', plugins_url('/assets/checkout_scripts.js',__FILE__ ), "", "", true);
			wp_register_script('slim-select', 'https://cdnjs.cloudflare.com/ajax/libs/slim-select/1.27.1/slimselect.min.js', "", "", false);
			wp_register_style('slim-styles', 'https://cdnjs.cloudflare.com/ajax/libs/slim-select/1.27.1/slimselect.min.css');
			wp_enqueue_script('checkout-scripts');
			wp_enqueue_style('checkout-styles');
			wp_enqueue_style('slim-styles');
			wp_enqueue_script('slim-select');
	}
	return $template;

}
add_action( 'template_include', 'checkout_setup' );


function success_page_setup( $template ) {

	if(get_option('ticket_price') === false) return;
	if ( is_page( 'success' )) {

			$plugindir = dirname( __FILE__ );
			$template = $plugindir . '/templates/success_template.php';
	}
	return $template;

}
add_action( 'template_include', 'success_page_setup' );



//Form error handling 
add_action('wp_footer', 'show_code_error');
function show_code_error() {
if(is_page('checkout') && isset($_GET['status']) && $_GET['status'] === 'error') {
		$message_field = "<h3>Uh oh. Something wasn't right with your form.</h3>";
		$details = "";
		
		if(isset($_GET['msg']) && $_GET['msg'] != "") {
			$mex = $_GET['msg'];
				if($mex == "honeypot") {
					$details = "Something went wrong with the form validation.";
				}
				if($mex == "missing fields" || $mex == "missingfields" || $mex == "missing%20fields") {
					$details = "One or more of the form fields seems to be missing.";
					if(isset($_GET['fields'])) {
						$details .= "Try to look at these field(s) again: " . $_GET['fields'];
					}
				}
				if($mex == "invalidemail") {
					$details = "Something was wrong with your email address.";
				}
				if($mex == "couponlimit") {
					$details = "The maximum allowed uses of this coupon code has been reached.";
				}
				if($mex == "couponnotexist" || $mex == "badcode" || $mex == "coupon" || $mex == "badcoupon") {
					$details = "Something was wrong with your coupon code.";
				}
		}
		
		echo "<div class='error-console'><span id='error-close'>&#x2715;</span>"; 
		echo "<p>" . $message_field . "</p>";
		echo "<p>" . $details . "</p>";
		echo "<p>Please adjust and try again. If you continue to experience problems, reach out to us at <a href='mailto:info@dagora.ch'>info@dagora.ch</a> and we will do our best to help out.</p>";
		echo "</div>";
	}
}



//Checkout helper functions
//Set new ajax nonce name on checkout along with jquery
function add_coupon_check_nonce() {
	if(is_page('checkout')) { 
	?>
		<script>
		var user_ajax_nonce = '<?php echo wp_create_nonce( "secure_nonce_name" ); ?>';
		var user_admin_url = '<?php echo admin_url( "admin-ajax.php" ); ?>';
		</script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<?php
	}
}
add_action ( 'wp_head', 'add_coupon_check_nonce' );

//Include ajax var for interaction with username checker
add_action( 'wp_ajax_check_submitted_coupon', 'check_submitted_coupon' );
add_action( 'wp_ajax_nopriv_check_submitted_coupon', 'check_submitted_coupon' );

function check_submitted_coupon() {
	check_ajax_referer( 'secure_nonce_name', 'sureandsecret' );
	$coupon_to_check =  htmlspecialchars( stripslashes( trim( $_POST['submitted_coupon'] ) ) );
	//require('validate_live_coupon.php'); <------better add this back in
	$res = live_check_my_coupon($coupon_to_check);
	echo json_encode($res);
	die();
}

add_action('wp_head','checkout_headers');
function checkout_headers() {
	if(is_page('checkout')) {
	echo '<script src="https://polyfill.io/v3/polyfill.min.js?version=3.52.1&features=fetch"></script>';
    echo '<script src="https://js.stripe.com/v3/"></script>';
	}
}