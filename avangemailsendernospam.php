<?php

if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

update_option('avang-email-sender-basename', plugin_basename(__FILE__));


if (is_plugin_active(get_option('avang-email-subscribe-basename')) === true) {

    deactivate_plugins(get_option('avang-email-subscribe-basename'));

} else {

    /*
     * Plugin Name: Avang Email Sender No Spam
     * Text Domain: avang-email-sender-nospam
     * Description: This plugin reconfigures the wp_mail() function to send email using API (via Avang Email) instead of SMTP and creates an options page that allows you to specify various options.
     * Author: Avang Email
     * Author URI: https://avangemail.com
     * Version: 1.0.2
     * License: GPLv2 or later
     * License URI: (https://www.gnu.org/licenses/gpl-3.0.html).
     * Avang Email Inc. for WordPress
     * Copyright (C) 2020
     */

    /* Version check */
    global $wp_version;
    $exit_msg = 'AvangEmail Sender requires WordPress 4.1 or newer. <a href="http://codex.wordpress.org/Upgrading_WordPress"> Please update!</a>';

    global $sender_queue__db_version;
    $avangemail_db_version = '1.0';

    if (version_compare($wp_version, "4.1", "<")) {
        exit($exit_msg);
    }

    if (!class_exists('eemail')) {
        require_once('defaults/function.reset_pass.php');
        require_once('class/ees_mail.php');
        eemail::on_load(__DIR__);
    }
    update_option('avangemail_plugin_dir_name', plugin_basename(__DIR__));

    /* ----------- ADMIN ----------- */
    if (is_admin()) {

        register_activation_hook(__FILE__, 'AvangEmailsender_activate');

        /* activate */
        function AvangEmailsender_activate()
        {
            register_uninstall_hook(__FILE__, 'AvangEmailsender_uninstall');
        }


        /* uninstall */
        function AvangEmailsender_uninstall()
        {
            delete_option('avangemail_options');
            delete_option('avangemail_send-email-type');
            delete_option('avangemail_plugin_dir_name');
            delete_option('avangemail_config_from_name');
            delete_option('avangemail_config_from_email');
            delete_option('avangemail_from_email');
            delete_option('avang-email-sender-basename');
            delete_option('avangemail_config_override_wooCommerce');
            delete_option('avangemail_config_woocommerce_original_email');
            delete_option('avangemail_config_woocommerce_original_name');
        }

        require_once 'class/avangemail_admin.php';
        $avangemail_admin = new avangemail_admin(__DIR__);
    }
}