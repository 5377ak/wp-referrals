<?php

//Block direct access
if (!defined('ABSPATH')) {
	wp_die('Direct access not allowed!');
}

class WP_Referral {

	static $form_nonce_string = 'wp-referral-form-nonce-string';

	public static function init() {
		//Register shortcode
		add_shortcode('wp_referral_form', array(__CLASS__, 'render_referral_form'));

		//Enqueue scripts and styles
		add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueu_escripts'));

		// Initialize AJAX handlers.
		WP_Referral_Ajax::init();

		// Initialize AJAX handlers.
		WP_Referral_Admin::init();
	}

	public static function enqueu_escripts() {

		wp_enqueue_style('wp-referral-form-style', WP_REFERRAL_PLUGIN_DIR_URL
				. 'assets/css/referral-form-style.css', array(), WP_REFERRAL_PLUGIN_VERSION);
		wp_enqueue_script('wp-referral-form-validation', WP_REFERRAL_PLUGIN_DIR_URL
				. 'assets/js/referral-form-validation.js', array('jquery')
				, WP_REFERRAL_PLUGIN_VERSION);
		wp_localize_script('wp-referral-form-validation', 'wpReferralObj', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'validate_referral_code' => 'validate_referral_code',
			'process_registration' => 'process_registration',
			'security' => wp_create_nonce(WP_Referral::$form_nonce_string)
				)
		);
	}

	public static function render_referral_form() {
		ob_start();
		if (!is_user_logged_in()):
			include_once WP_REFERRAL_PLUGIN_DIR_PATH . 'includes/templates/referral-form-html.php';
		else:
			?>
			<p class="text-center">
				<?php
				_e('Sorry, you are already logged in.', 'wp-referral');
				?>
			</p>
		<?php
		endif;
		return ob_get_clean();
	}

}
