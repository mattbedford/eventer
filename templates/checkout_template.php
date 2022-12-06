<?php
/**
 * Template Name: Checkout template
 */

get_header();

//Check global event vars are in place before we try to do anything
if(get_option('ticket_price') === false || get_option('event_name') === false) {
    echo "Sorry! Checkout is not yet available. Please check back soon.";
    exit;
}

echo '<script>var fullTicketPrice ="' . get_option('ticket_price') . '";</script>';
//CHANGE THIS! Var describing where the form will try to submit to.
$stripe_init_location = site_url() . "/wp-content/plugins/eventer/checkout-scripts/checkout_init.php";
?>  


<h2>Register for <?php echo get_option('event_name'); ?></h2>

<form class="checkout-form" action="<?php echo $stripe_init_location; ?>" method="POST">
    <div class="section-1">
        <input type="hidden" name="source_event" value="<?php echo get_option('event_tag'); ?>">
        <input type="hidden" name="required" value="">
        <input type="text" name="first_name" id="first_name" style="display:block;position:absolute;right:-5000px;height:0;width:0;opacity:0.1;">

        <div class="form-section personal-details">
            <h3>Personal details</h3>
			<span class="notes">* All fields required unless otherwise indicated.</span>
            <div class="form-flexer">
                <div class="input">
                    <label for="title">Title (Optional)</label>
                    <input type="text" name="title" id="title" placeholder="Mr/Ms/Other">
                </div>
                <div class="input">
                    <label for="fname">First name</label>
                    <input type="text" name="fname" id="fname" required="">
                </div>

                <div class="input">
                <label for="lname">Last name</label>
                <input type="text" name="lname" id="lname" required="">
                </div>
            </div>

            <div class="form-flexer">
                <div class="input">
                    <label for="role">Job title</label>
                    <input type="text" name="role" id="role" required="">
                </div>
                <div class="input">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required="">
                </div>
            </div>

            <div class="form-flexer">
                <div class="input">
                    <label for="Mobile">Mobile phone (optional)</label>
                    <input type="tel" name="mobile" id="mobile" placeholder="+41">
                </div>
                <div class="input">
                    <label for="office">Office phone</label>
                    <input type="tel" name="office" id="office" required="">
                </div>
            </div>
        </div>

        <div class="form-section interests">
            <h3>Interests</h3>
			<span class="notes">* Optional</span>
            <label for="tags" style="padding-bottom:5px;">(Optional) Use tags to share your goals for the event day. Use the <span class="plus">+</span> icon to add your own</label>
            <!---<textarea name="tags" id="tags" required=""></textarea>--->
            <select name="tags[]" id="tags" multiple="multiple">
                <option data-placeholder="true"></option>
                <option value="AI">AI</option>
                <option value="sustainability">sustainability</option>
                <option value="e-commerce platform">e-commerce platforms</option>
                <option value="3D prototyping">3D prototyping</option>
                <option value="investments">Investments</option>
            </select>
        </div>
                
        <div class="form-section company-details">
            <h3>Company details</h3>
			<span class="notes">* All fields required.</span>
            <div class="form-flexer">
                <div class="input">
                    <label for="company">Company name</label>
                    <input type="text" name="company" id="company" required="">
                </div>
                <div class="input">
                    <label for="website">Website url (including 'https://')</label>
                    <input type="url" name="website" id="website" placeholder="https://yourwebsite.com" required="">
                </div>
            </div>
            
            <div class="input">
                <label for="address">Street address</label>
                <input type="text" name="address" id="address" required="">
            </div>
            <div class="input">
                <label for="postcode">Postcode</label>
                <input type="text" name="postcode" id="postcode" required="">
            </div>
            <div class="form-flexer">
                <div class="input">
                    <label for="city">City</label>
                    <input type="text" name="city" id="city" required="">
                </div>
                <div class="input">
                    <label for="country">Country</label>
                    <input type="text" name="country" id="country" required="">
                </div>
            </div>
        </div>

        <div class="form-section t-and-c">
            <h3>Terms & conditions</h3>
			<span class="notes">* Required.</span>
            <p>Dagorà Lifestyle Innovation Hub along with its strategic partners Netcomm Suisse , Loomish and the LifeStyle-Tech Competence Center are committed to protecting and respecting your privacy, and we’ll only use your personal information to administer your account and to provide the products and services you requested from us. From time to time, we would like to contact you about our products and services, as well as other content that may be of interest to you. </p>
            <p>By submitting this form, you also agree to have your contact information, including email, passed on to the sponsors of this event for the purpose of following up on your interests.</p>
		    <div class="mktbox">
			    <input type="checkbox" name="mkt" id=mkt required>
                <label for="mkt">I consent to my data being used for marketing communication and updates on initiatives</label>
            </div>
		    <p>You can unsubscribe from these communications at any time. For more information on how to unsubscribe, our privacy practices, and how we are committed to protecting and respecting your privacy, please review our Privacy Policy. By clicking submit below, you consent to allow Dagorà and its strategic partners Netcomm Suisse and Loomish SA to store and process the personal information submitted above to provide you the content requested.
		    </p>
	    </div>
    </div>
	<div class="section-2">
	   <div class="float-box">
            <h4>1 x admittance to <?php echo get_option('event_name') ?> <span>
            <?php echo get_option('venue_name') . " - " .get_option('venue_city') . ", " . get_option('venue_country') . "on " .
            fix_the_date(get_option('event_date')) . " @ " . get_option('event_start') . "AM"; ?></span></h4>
            <h3 id="price-to-pay"><?php echo get_option('ticket_price') . " CHF"; ?></h3>
            <img class="payment-methods" src="<?php echo site_url(); ?>/wp-content/uploads/2022/07/payment-methods-768x128-1.png"><br>

            <label for="coupon">Do you have a coupon code?</label>
            <div class="coupon-holder">
                <input class="coupon-box" type="text" name="coupon" placeholder="Coupon code"><span id="apply-me">Apply coupon</span>
            </div>
            <span id="coupon-message"></span>
            
            <input type="submit" id="form-submit" value="Register now">
        </div>    
	</div>
</form>

<?php get_footer(); ?>