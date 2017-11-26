<?php

/**
 * Plugin_name
 * 
 * @package   Plugin_name
 * @author    Luke Crouch and Daniele Scasciafratte <mte90net@gmail.com>
 * @copyright 2017 Mozilla
 * @license   GPL 2.0+
 * @link      https://github.com/mdn/wp-promote-mdn
 */

/**
 * This class contain all the snippet or extra that improve the experience on the frontend
 */
class Pm_Extras {

	/**
	 * Initialize the snippet
	 */
	function initialize() {
		add_shortcode( 'promote_mdn_newsletter', array( $this, 'newsletter' ) );
	}

	function newsletter() {
		return '<form name="promote-mdn-newsletter-form" class="newsletter block" action="https://www.mozilla.org/en-US/newsletter/" method="post">
  <h2 class="heading">' . __( 'Learn the best of web development', PM_TEXTDOMAIN ) . '</h2>
  <p class="newsletter__description">' . __( 'Sign up for our newsletter:', PM_TEXTDOMAIN ) . '</p>
  <input id="fmt" name="fmt" value="H" type="hidden">
  <input id="newsletterNewslettersInput" name="newsletters" value="app-dev" type="hidden">

  <div id="newsletterEmail" class="form__row">
    <label for="newsletterEmailInput" class="offscreen">' . __( 'E-mail', PM_TEXTDOMAIN ) . '</label>
    <input id="newsletterEmailInput" name="email" class="newsletter__input" required="" placeholder="you@example.com" size="30" type="email">
  </div>

  <div id="newsletterPrivacy" class="form__row form__fineprint">
    <input id="newsletterPrivacyInput" name="privacy" required="" type="checkbox">
    <label for="newsletterPrivacyInput">' . __( 'I\'m okay with Mozilla handling my info as explained in this <a href="https://www.mozilla.org/privacy/">Privacy Policy</a>.', PM_TEXTDOMAIN ) . '   
    </label>
  </div>
  <button id="newsletter-submit" type="submit" class="button positive">' . __( 'Sign up now', PM_TEXTDOMAIN ) . '</button>
</form>';
	}

}

$pm_extras = new Pm_Extras();
$pm_extras->initialize();
do_action( 'promote_mdn_extras_instance', $pm_extras );
