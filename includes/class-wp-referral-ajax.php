<?php

//Block direct access
if (!defined('ABSPATH')) {
	wp_die('Direct access not allowed!');
}

class WP_Referral_Ajax {

	public static function init() {

		/*
		 * I have commented ajax for logged in user. Also, I have
		 * prevented the rendering of referral form for logged in users,
		 * considering referral form as a regular registration form on the
		 * website.
		 */

		add_action('wp_ajax_nopriv_validate_referral_code', array(__CLASS__,
			'validate_referral_code'));

		add_action('wp_ajax_nopriv_process_registration', array(__CLASS__,
			'process_registration'));

		// Adding AJAX action for updating join commission
		add_action('wp_ajax_update_join_commission', array(__CLASS__,
			'update_join_commission'));

		// Adding AJAX action for deleting referral record
		add_action('wp_ajax_delete_referral_record', array(__CLASS__,
			'delete_referral_record'));

		// Adding AJAX action for bulk deleting referral record
		add_action('wp_ajax_bulk_delete_referral_records', array(__CLASS__,
			'bulk_delete_referral_records'));
	}

	public static function bulk_delete_referral_records() {
		// Ensure the request came from a logged-in
		// user with proper permissions
		if (!current_user_can('manage_options')) {
			wp_send_json_error('Permission denied');
		}

		// Get the user IDs
		$user_ids = isset($_POST['user_ids']) ? array_map('intval', $_POST['user_ids']) : array();

		// Loop through IDs and delete each user's meta data
		foreach ($user_ids as $user_id) {
			delete_user_meta($user_id, 'referrer_username');
			delete_user_meta($user_id, 'join_commission');
		}

		wp_send_json_success('Referral records deleted successfully');
	}

	public static function delete_referral_record() {
		// Ensuring taht the request came from a logged-in
		// user with proper permissions
		if (!current_user_can('manage_options')) {
			wp_send_json_error('Permission denied');
		}

		// Get the user ID
		$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

		// Delete the referral data associated with the user ID
		delete_user_meta($user_id, 'referrer_username');
		delete_user_meta($user_id, 'join_commission');

		// Check if the deletion was successful
		if (!get_user_meta($user_id, 'referrer_username', true) &&
				!get_user_meta($user_id, 'join_commission', true)) {
			wp_send_json_success('User meta deleted successfully');
		} else {
			wp_send_json_error('Failed to delete user meta');
		}
	}

	public static function update_join_commission() {
		// Ensuring the request came from a logged-in
		// user with proper permissions
		if (!current_user_can('manage_options')) {
			wp_send_json_error('Permission denied');
		}

		// Get the user ID and join commission value
		$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
		$join_commission = isset($_POST['join_commission']) ? intval(sanitize_text_field($_POST['join_commission'])) : '';

		// Update the join commission value in the user meta
		update_user_meta($user_id, 'join_commission', $join_commission);

		// Check if the update was successful
		if (get_user_meta($user_id, 'join_commission', true) == $join_commission) {
			wp_send_json_success('Join commission updated successfully');
		} else {
			wp_send_json_error('Failed to update join commission');
		}
	}

	public static function process_registration() {
		if (check_ajax_referer(WP_Referral::$form_nonce_string, 'security') === false) {
			// Send error message in json
			wp_send_json_error(
					array(
						'error_msg' => 'Invalid request or form.'
					)
			);
		}
		$first_name = sanitize_text_field($_POST['first_name']);
		$last_name = sanitize_text_field($_POST['last_name']);
		$email = sanitize_email($_POST['email']);
		$password = $_POST['password'];
		$referral_code = sanitize_text_field($_POST['referral_code']);

		// Initialize referrer username to empty
		$referrer_username = '';

		// Check if referral code is provided
		if (!empty($referral_code)) {
			// Search for users with the provided referral code
			$referrer_query = new WP_User_Query(array(
				'meta_key' => 'referral_code',
				'meta_value' => $referral_code,
				'number' => 1, // We only need one result
				'fields' => 'ID' // We only need the ID
			));

			// Retrieve the user ID if found
			$referrer_user_ids = $referrer_query->get_results();
			if (!empty($referrer_user_ids)) {
				$referrer_user = get_userdata($referrer_user_ids[0]);
				$referrer_username = $referrer_user->user_login;
			}
		}

		// Create user
		$user_id = wp_create_user($email, $password, $email);

		// Check if user creation was successful
		if (!is_wp_error($user_id)) {

			// Update user meta with first name and last name
			update_user_meta($user_id, 'first_name', $first_name);
			update_user_meta($user_id, 'last_name', $last_name);

			if (!empty($referrer_username)):

				// Get commission value
				$options = get_option('wp_referral_plugin_options');
				$commission = $options['join_commission'];

				// Save referrer username and joining commission
				update_user_meta($user_id, 'referrer_username', $referrer_username);
				update_user_meta($user_id, 'join_commission', $commission);

			endif;

			// Sign in the user
			$user = wp_signon(array(
				'user_login' => $email,
				'user_password' => $password
			));
			// Send redirect url in json
			wp_send_json_success(
					array(
						'error_msg' => $error_message,
						'redirect_url' => get_author_posts_url($user_id)
					)
			);
		} else {
			// Getting registration error in variable
			$error_message = $user_id->get_error_message();
			// Send error message in json
			wp_send_json_error(
					array(
						'error_msg' => $error_message
					)
			);
		}
	}

	public static function validate_referral_code() {
		if (check_ajax_referer(WP_Referral::$form_nonce_string, 'security') === false) {
			// Send error message in json
			wp_send_json_error(
					array(
						'error_msg' => 'Invalid request or form.'
					)
			);
		}
		$referral_code = sanitize_text_field($_POST['referral_code']);

		// Check if referral code exists.
		$is_valid = self::check_referral_code($referral_code);

		wp_send_json_success(array('is_valid' => $is_valid));
	}

	private static function check_referral_code($referral_code) {
		if (!empty($referral_code)) {
			// Search for users with the provided referral code
			$referrer_query = new WP_User_Query(array(
				'meta_key' => 'referral_code',
				'meta_value' => $referral_code,
				'number' => 1, // We only need one result
				'fields' => 'ID' // We only need the ID
			));

			// Retrieve the user ID if found to validate the referral code
			$referrer_user_ids = $referrer_query->get_results();
			if (!empty($referrer_user_ids)) {
				// If referral code is assigned to a user
				return true;
			} else {
				// If referral code is not assigned to a user
				return false;
			}
		} else {
			// If referral code is empty
			return false;
		}
	}

}
