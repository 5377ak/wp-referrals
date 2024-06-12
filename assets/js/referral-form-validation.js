jQuery(document).ready(function ($) {
	// Function to validate referral code
	function validateReferralCode(referralCode, callback) {
		$.ajax({
			url: wpReferralObj.ajax_url,
			method: 'POST',
			data: {
				action: wpReferralObj.validate_referral_code,
				security: wpReferralObj.security,
				referral_code: referralCode
			},
			success: function (response) {
				callback(response.success && response.data.is_valid);
			}
		});
	}

	// Event listener for the referral code input using keyup event
	$('#referral-code').on('keyup', function () {
		var referralCode = $(this).val();
		validateReferralCode(referralCode, function (isValid) {
			if (isValid) {
				$('#referral-code').closest('div').addClass('check').removeClass('cross');
			} else {
				$('#referral-code').closest('div').removeClass('check').addClass('cross');
			}
		});
	});

	// Event listener for the form submit event
	$('#referral-form').on('submit', function (event) {
		event.preventDefault(); // Prevent form from submitting

		var referralCode = $('#referral-code').val();
		var $form = $(this);

		// Validate the referral code before submitting the form
		validateReferralCode(referralCode, function (isValid) {
			if (!isValid) {
				$('#referral-code').val(''); // Empty the referral code input if invalid
				$('#referral-code').closest('div').removeClass('check').removeClass('cross');
			}

			// Proceed with form submission through AJAX
			$.ajax({
				url: wpReferralObj.ajax_url,
				method: 'POST',
				data: $form.serialize() + '&action=' + wpReferralObj.process_registration + '&security=' + wpReferralObj.security,
				success: function (response) {
					if (response.success) {
						/*
						 *  Showing alert on successful registration and
						 *  redirecting user to the server provided url.
						 */

						alert('Registration successful!');
						window.location.href = response.data.redirect_url;
					} else {
						// Showing alert for the registraiton failed message.
						alert('Registration failed: ' + response.data.error_msg);
					}
				},
				error: function () {
					alert('An error occurred during registration. Please try again.');
				}
			});
		});
	});
});
