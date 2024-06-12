<?php

//Block direct access
if (!defined('ABSPATH')) {
	wp_die('Direct access not allowed!');
}

class WP_Referral_Code_Generator {

    public static function init() {
        add_action('user_register', array(__CLASS__, 'generate_referral_code'), 10, 1);
        add_action('admin_menu', array(__CLASS__, 'add_admin_submenu_page'));
    }

    public static function generate_referral_code($user_id) {
        $code = self::generate_unique_code();
        update_user_meta($user_id, 'referral_code', $code);
    }

    private static function generate_unique_code() {
        do {
            $code = self::generate_code();
        } while (self::code_exists($code));
        return $code;
    }

    private static function generate_code() {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $code;
    }

    private static function code_exists($code) {
        $users = get_users(array(
            'meta_key' => 'referral_code',
            'meta_value' => $code
        ));
        return !empty($users);
    }

    public static function add_admin_submenu_page() {
        add_submenu_page(
            'wp-referral',
            'Referral Codes',
            'Referral Codes',
            'manage_options',
            'referral-codes',
            array(__CLASS__, 'display_referral_codes_page')
        );
    }

    public static function display_referral_codes_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Referral Codes', 'unique-referral-code'); ?></h1>
            <table class="wp-list-table widefat fixed striped users">
                <thead>
                    <tr>
                        <th><?php _e('User ID', 'unique-referral-code'); ?></th>
                        <th><?php _e('Username', 'unique-referral-code'); ?></th>
                        <th><?php _e('Email', 'unique-referral-code'); ?></th>
                        <th><?php _e('Referral Code', 'unique-referral-code'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $users = get_users();
                    foreach ($users as $user) {
                        $referral_code = get_user_meta($user->ID, 'referral_code', true);
                        if (!$referral_code) {
                            $referral_code = self::generate_unique_code();
                            update_user_meta($user->ID, 'referral_code', $referral_code);
                        }
                        ?>
                        <tr>
                            <td><?php echo esc_html($user->ID); ?></td>
                            <td><?php echo esc_html($user->user_login); ?></td>
                            <td><?php echo esc_html($user->user_email); ?></td>
                            <td><?php echo esc_html($referral_code); ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}