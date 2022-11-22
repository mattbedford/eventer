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