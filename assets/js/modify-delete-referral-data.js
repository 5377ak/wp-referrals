jQuery(document).ready(function ($) {

	// Edit Referral Data
	$(document).on('click', '.edit-button', function () {
		// Get the user ID associated with the clicked row
		var user_id = $(this).data('user-id');

		// Prompt to enter a new join commission value
		var new_join_commission = prompt('Enter new join commission for user ID '
				+ user_id + ':');

		// Validate the new join commission value
		if (new_join_commission !== null && new_join_commission.trim() !== '') {
			// Update the join commission for the selected user
			$.ajax({
				url: wprCommissionObj.ajax_url,
				type: 'POST',
				data: {
					action: wprCommissionObj.action_edit,
					user_id: user_id,
					join_commission: new_join_commission
				},
				success: function (response) {
					if (response.success) {
						alert('Join commission updated successfully.');
					} else {
						alert('Failed to update join commission.');
					}
				},
				error: function (xhr, status, error) {
					console.error(xhr.responseText);
					alert('An error occurred while updating join commission.');
				}
			});
		}
	});

	// Delete Referral Data 
	$(document).on('click', '.delete-button', function () {
		// Get the user ID associated with the clicked row
		var user_id = $(this).data('user-id');

		// Confirm before deleting the user meta
		if (confirm('Are you sure you want to delete this referral record?')) {
			// 
			$.ajax({
				url: wprCommissionObj.ajax_url,
				type: 'POST',
				data: {
					action: wprCommissionObj.action_delete,
					user_id: user_id
				},
				success: function (response) {
					if (response.success) {
						// Remove the deleted row from the table
						$('tr[data-user-id="' + user_id + '"] td.action').text('');
						alert('Referral record deleted successfully.');
					} else {
						alert('Failed to delete record.');
					}
				},
				error: function (xhr, status, error) {
					console.error(xhr.responseText);
					alert('An error occurred while deleting record.');
				}
			});
		}
	});

	// Bulk detele code
	$(document).on('click', '#doaction, #doaction2', function (e) {
		var bulk_action = $('select[name="action"]').val();

		if (bulk_action === 'bulk-delete') {
			e.preventDefault();

			var user_ids = [];
			$('input[name="bulk-delete[]"]:checked').each(function () {
				user_ids.push($(this).val());
			});

			if (user_ids.length === 0) {
				alert('No record(s) selected.');
				return;
			}

			if (confirm('Are you sure you want to delete the selected records?')) {
				$.ajax({
					url: wprCommissionObj.ajax_url,
					type: 'POST',
					data: {
						action: wprCommissionObj.action_bulk_delete,
						user_ids: user_ids
					},
					success: function (response) {
						if (response.success) {
							user_ids.forEach(function (user_id) {
								$('tr[data-user-id="' + user_id + '"]').remove();
							});
							alert('Records deleted successfully.');
							window.location.reload();
						} else {
							alert('Failed to delete records.');
						}
					},
					error: function (xhr, status, error) {
						console.error(xhr.responseText);
						alert('An error occurred while deleting the records.');
					}
				});
			}
		}
	});
});
