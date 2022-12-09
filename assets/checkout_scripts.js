//const { stringifyQuery } = require("vue-router");

	jQuery('input').blur(function(){
		jQuery(this).addClass('notok'); 
	});
	
	new SlimSelect({
	    select: '#tags',
		allowDeselect: true,
		searchPlaceholder: 'Add new tag',
		 deselectLabel: '<span>&#x2715;</span>',
	  // Optional - In the event you want to alter/validate it as a return value
	  addable: function (value) {
		// return false or null if you do not want to allow value to be submitted
		if (value === 'bad') {return false}

		// Optional - Return a valid data object. See methods/setData for list of valid options
		return {
		  text: value,
		  value: value.toLowerCase()
		}
  }
})
	window.onload = (event) => {
	var x = document.getElementById("error-close");
	if(!x || x == null) return;
	x.addEventListener('click',()=>{
    	let m = document.querySelector(".error-console");
    	m.style.display = "none";
	})
	}
	
	//grey out coupon box if not filled
	let couponField = document.querySelector('.coupon-box');
	couponField.addEventListener('input', updateValue);
	
	function updateValue(e) {
		let theText = jQuery('.coupon-box').val();
		if(theText.length > 4) {
			jQuery('#apply-me').addClass('active');
		} else {
			jQuery('#apply-me').removeClass('active');
		}
	}
	
	//Add action to coupon checkbox
	jQuery('#apply-me').click(function(){
		let newCoupon = jQuery('.coupon-box').val();
		if(newCoupon.length < 5) return;
		checkThisCoupon(newCoupon);
	}) 
	
	
	function checkThisCoupon(e) {
			let couponData = Array();
			couponData.push( { "name" : "action", "value" : "check_submitted_coupon" } );
			couponData.push( { "name" : "sureandsecret", "value" : user_ajax_nonce } );
			couponData.push( { "name" : "submitted_coupon", "value" : e } );
			jQuery.ajax({
				url : user_admin_url, 
				type : 'post',
				data : couponData,
				success : function( response ) {
				 let result = JSON.parse(response);		
				 switch (result) {
					  case 'free':
						console.log('Free ticket');
						 jQuery('#price-to-pay').html('CHF 0.00');
						 jQuery('#coupon-message').html('Discount code applied.');
						break;
					  case 'no':
						 console.log('Coupon not valid');
						 jQuery('#price-to-pay').html('CHF ' + fullTicketPrice);
						 jQuery('#coupon-message').html('Sorry. This coupon does not seem to be valid. Please check.');
						 break;
					  case 'max':
						 console.log('Coupon not valid');
						 jQuery('#price-to-pay').html('CHF ' + fullTicketPrice);
						 jQuery('#coupon-message').html('Sorry. The maximum uses for this coupon has been reached.');
						 break;
					  case '0':
						console.log('There has been an error');
						  jQuery('#price-to-pay').html('CHF ' + fullTicketPrice);
						  jQuery('#coupon-message').html('Sorry. There has been an error with the system. Please check back later.');
						break;
					  default:
						jQuery('#price-to-pay').html('CHF ' + result);
						jQuery('#coupon-message').html('Discount code applied.');
					}
					jQuery('#coupon-message').slideDown();
				
					
				}
			});
			return false;    
		}
