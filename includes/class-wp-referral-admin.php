<?php
//Block direct access
if (!defined('ABSPATH')) {
	wp_die('Direct access not allowed!');
}

class WP_Referral_Admin {

	public static function init() {

		// Create admin menu pages for plugin settings and stuff
		add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
		add_action('admin_init', array(__CLASS__, 'register_settings'));

		// Enqueue admin scripts and styles
		add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));

		//Initialize referral code generator
		WP_Referral_Code_Generator::init();
	}

	public static function enqueue_scripts() {

		wp_enqueue_script('modify-delete-referral-script', WP_REFERRAL_PLUGIN_DIR_URL
				. 'assets/js/modify-delete-referral-data.js', array('jquery')
				, WP_REFERRAL_PLUGIN_VERSION);
		wp_localize_script('modify-delete-referral-script', 'wprCommissionObj', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'action_edit' => 'update_join_commission',
			'action_delete' => 'delete_referral_record',
			'action_bulk_delete' => 'bulk_delete_referral_records'
				)
		);
	}

	public static function add_admin_menu() {
		add_menu_page(
				'WP Referral',
				'WP Referral',
				'manage_options',
				'wp-referral',
				array(__CLASS__, 'admin_page')
		);
	}

	public static function register_settings() {
		register_setting('wp_referral_plugin_options', 'wp_referral_plugin_options');

		add_settings_section(
				'wp_referral_plugin_main',
				'Main Settings',
				null,
				'wp-referral'
		);

		add_settings_field(
				'join_commission',
				'Join Commission (Rs)',
				array(__CLASS__, 'join_commission_field'),
				'wp-referral',
				'wp_referral_plugin_main'
		);
	}

	public static function join_commission_field() {
		$options = get_option('wp_referral_plugin_options');
		?>
		<input type="number" name="wp_referral_plugin_options[join_commission]"
			   value="<?php
		echo isset($options['join_commission']) ? esc_attr($options['join_commission']) : '';
		?>" />
			   <?php
		   }

		   public static function admin_page() {
			   ?>
		<div class="wrap">
			<h1>Referral Settings</h1>
			<p>Registration Form Shortcode: <code>[wp_referral_form]</code></p>
			<form method="post" action="options.php">
				<?php
				settings_fields('wp_referral_plugin_options');
				do_settings_sections('wp-referral');
				submit_button();
				?>
			</form>

			<h2>Referral History</h2>
			<form method="post">
				<?php
				$list_table = new WP_Referral_List_Table();
				$list_table->prepare_items();
				$list_table->display();
				?>
			</form>
		</div>
		<?php
	}

}
