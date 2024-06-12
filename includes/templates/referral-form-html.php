<?php

//Block direct access
if (!defined('ABSPATH')) {
	wp_die('Direct access not allowed!');
}

?>

<form id="referral-form">
	<h2 class="text-center">Register</h2>
	<div class="form-field">
		<label for="first-name"><?php _e('First Name', 'wp-referral'); ?></label>
		<input type="text" id="first-name" name="first_name" required/>
	</div>
	<div class="form-field">
		<label for="last-name"><?php _e('Lazt Name', 'wp-referral'); ?></label>
		<input type="text" id="last-name" name="last_name" required/>
	</div>

	<div class="form-field">
		<label for="email"><?php _e('Email', 'wp-referral'); ?></label>
		<input type="email" id="email" name="email" required/>
	</div>

	<div class="form-field">
		<label for="password"><?php _e('Password', 'wp-referral'); ?></label>
		<input type="password" id="password" name="password" required/>
	</div>

	<div class="form-field">
		<label for="referral-code"><?php _e('Referral Code', 'wp-referral'); ?></label>
		<input type="text" id="referral-code" name="referral_code" />
	</div>

	<div class="form-field">
		<button type="submit">Register</button>
	</div>

</form>
