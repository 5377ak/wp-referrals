<?php

//Block direct access
if (!defined('ABSPATH')) {
	wp_die('Direct access not allowed!');
}

if (!class_exists('WP_List_Table')) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WP_Referral_List_Table extends WP_List_Table {

	public function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = false;

		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $this->get_referral_data();
	}

	public function get_columns() {
		return array(
			'cb' => '<input type="checkbox" />',
			'user_id' => 'User ID',
			'username' => 'Username',
			'referral_username' => 'Referral Username',
			'join_commission' => 'Join Commission',
			'action' => 'Action'
		);
	}

	private function get_referral_data() {
		global $wpdb;

		/*
		 * Query to fetch referral data for each registered user through
		 * referral code.
		 */
		$query = "SELECT u.ID AS user_id, u.user_login AS username, 
                     meta1.meta_value AS referral_username, 
                     meta2.meta_value AS join_commission
              FROM {$wpdb->users} AS u
              LEFT JOIN {$wpdb->usermeta} AS meta1 ON u.ID = meta1.user_id AND
				  meta1.meta_key = 'referrer_username'
              LEFT JOIN {$wpdb->usermeta} AS meta2 ON u.ID = meta2.user_id AND
				  meta2.meta_key = 'join_commission'
              WHERE meta1.meta_value IS NOT NULL AND meta1.meta_value != ''";

		$results = $wpdb->get_results($query, ARRAY_A);

		foreach ($results as &$result) {

			// Setting action buttons for edit and delete features.
			$edit_button = sprintf('<button class="edit-button" data-user-id="%d">'
					. 'Edit</button>', $result['user_id']);
			$delete_button = sprintf('<button class="delete-button" data-user-id="%d">'
					. 'Delete</button>', $result['user_id']);

			$result['action'] = $edit_button . ' | ' . $delete_button;
		}

		return $results;
	}

	public function column_default($item, $column_name) {
		switch ($column_name) {
			case 'user_id':
			case 'username':
			case 'referral_username':
			case 'join_commission':
			case 'action':
				return $item[$column_name];
			default:
				return print_r($item, true);
		}
	}

	public function column_cb($item) {
		return sprintf(
				'<input type="checkbox" name="bulk-delete[]" value="%s" />',
				$item['user_id']
		);
	}

	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => 'Delete'
		);
		return $actions;
	}

	public function display() {
		$this->process_bulk_action();
		parent::display();
	}

}
