<?php

define('AVANGEMAIL_ADMIN', true);

/**
 * Description of avangemail_admin
 *
 * @author AvangEmail
 */
class avangemail_admin
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $defaultOptions = array('avangemail_enable' => 'no', 'avangemail_apikey' => null,'avangemail_hostname'=>'https://send.avangemail.com'),
        $options,
        $subscribe_status = false;
    public $theme_path;

    /**
     * Start up
     */
    public function __construct($pluginpath)
    {
        $this->theme_path = $pluginpath;
        add_action('init', array($this, 'WooCommerce_email'));
        add_action('init', array($this, 'WooCommerce_name'));
        add_action('admin_init', array($this, 'init_options'));
        $this->options = get_option('avangemail_options', $this->defaultOptions);

        if (is_multisite()) {
            add_action('network_admin_menu', array($this, 'add_menu'));
        } else {
            add_action('admin_menu', array($this, 'add_menu'));
        }
    }

    // Added admin menu
    public function add_menu()
    {
        add_menu_page('AvangEmail Email Api Sender', 'AvangEmail Api Sender No Spam', 'manage_options', 'avang-email-settings', array($this, 'show_settings'), plugins_url('/assets/images/icon.png', dirname(__FILE__)));
    }

    // Load settings
    public function show_settings()
    {
        require_once($this->theme_path . '/template/t-avangemail_admin.php');
        return;
    }


    //Initialization custom options
    public function init_options()
    {
        register_setting(
            'avangemail_option_group', // Option group
            'avangemail_options', // Option name
            array($this, 'valid_options')   // Santize Callback
        );
        //INIT SECTION
        add_settings_section('setting_section_id', null, null, 'avangemail-settings');
        //INIT FIELD
        add_settings_field('avangemail_enable', 'Select mailer:', array($this, 'enable_input'), 'avangemail-settings', 'setting_section_id', array('input_name' => 'avangemail_enable'));
        add_settings_field('avangemail_apikey', 'AvangEmail Host Name:', array($this, 'input_hostname'), 'avangemail-settings', 'setting_section_id', array('input_name' => 'avangemail_hostname', 'width' => 280));
        add_settings_field('avangemail_hostname', 'AvangEmail API Key:', array($this, 'input_apikey'), 'avangemail-settings', 'setting_section_id', array('input_name' => 'avangemail_apikey', 'width' => 280));

        if (is_plugin_active('woocommerce/woocommerce.php')) {
            add_settings_field('avangemail_override_wooCommerce', 'Override: ', array($this, 'override_wooCommerce_input'), 'avangemail-settings', 'setting_section_id', array('input_name' => 'avangemail_override_wooCommerce', 'width' => 280));
        }
        add_settings_field('avangemail_from_name_config', 'From name (default empty):', array($this, 'from_name_config_input'), 'avangemail-settings', 'setting_section_id', array('input_name' => 'avangemail_from_name_config', 'width' => 280));
        add_settings_field('avangemail_from_email_config', 'Email FROM (default empty):', array($this, 'from_email_config_input'), 'avangemail-settings', 'setting_section_id', array('input_name' => 'avangemail_from_email_config', 'width' => 280));

    }

    /**
     * Validation plugin options during their update data
     * @param type $input
     * @return type
     */
    public function valid_options($input)
    {
        // If api key have * then use old api key
        if (strpos($input['avangemail_apikey'], '*') !== false) {
            $input['avangemail_apikey'] = $this->options['avangemail_apikey'];
        } else {
            $input['avangemail_apikey'] = sanitize_key($input['avangemail_apikey']);
        }

        if ($input['avangemail_enable'] !== 'yes') {
            $input['avangemail_enable'] = 'no';
        }
        return $input;
    }

    /**
     * Get the apikey option and print one of its values
     */
    public function input_apikey($arg)
    {
        $apikey = $this->options[$arg['input_name']];
        if (empty($apikey) === false) {
            $apikey = '**********' . substr($apikey, strlen($apikey) - 5, strlen($apikey));
        }
        printf('<input type="text" id="title" name="avangemail_options[' . $arg['input_name'] . ']" value="' . $apikey . '" style="%s"/>', (isset($arg['width']) && $arg['width'] > 0) ? 'width:' . $arg['width'] . 'px' : '');
    }
    public function input_hostname($arg) {
        $hostname = $this->options[$arg['input_name']];
        printf('<input type="text" id="title" name="avangemail_options[' . $arg['input_name'] . ']" value="' . $hostname . '" style="%s"/>', (isset($arg['width']) && $arg['width'] > 0) ? 'width:' . $arg['width'] . 'px' : '');
  
    }
    /**
     * Displays the settings mailer
     */
    public function enable_input($arg)
    {
        if (!isset($this->options[$arg['input_name']]) || empty($this->options[$arg['input_name']])) {
            $valuel = 'no';
        } else {
            $valuel = $this->options[$arg['input_name']];
        }

        echo '<div style="margin-bottom:15px;"><label><input type="radio" name="avangemail_options[' . $arg['input_name'] . ']" value="yes" ' . (($valuel === 'yes') ? 'checked' : '') . '/><span>' . __('Send all WordPress emails via Avang Email API.', 'avang-email-sender') . '</span><label></div>';
        echo '<label><input type="radio" name="avangemail_options[' . $arg['input_name'] . ']" value="no"  ' . (($valuel === 'no') ? 'checked' : '') . '/><span>' . __('Use the defaults Wordpress function to send emails.', 'avang-email-sender') . '</span><label>';
    }

    /**
     * Displays the settings from name
     */
    public function from_name_config_input($arg)
    {
        if (!isset($this->options[$arg['input_name']]) || empty($this->options[$arg['input_name']])) {
            $config_from_name = '';
            update_option('avangemail_config_from_name', null);
        } else {
            $config_from_name = $this->options[$arg['input_name']];
            update_option('avangemail_config_from_name', $config_from_name);
            /**Adding filter  to override wp_mail_from_name field , if the option is checked */
            if (get_option('avangemail_config_override_wooCommerce')) {
                do_action('WooCommerce_name');
            }
        }
        echo '<input type="text" name="avangemail_options[' . $arg['input_name'] . ']" placeholder="' . __('From name', 'avang-email-sender') . '" value="' . $config_from_name . '" style="width:' . $arg['width'] . 'px"/>';
    }

    /**
     * Displays the settings email FROM
     */
    public function from_email_config_input($arg)
    {
        if (!isset($this->options[$arg['input_name']]) || empty($this->options[$arg['input_name']])) {
            $config_from_email = '';
            update_option('avangemail_config_from_email', null);

        } else {
            $config_from_email = $this->options[$arg['input_name']];
            update_option('avangemail_config_from_email', $config_from_email);
            /**Adding filter  to override wp_mail_from field , if the option is checked */
            if (get_option('avangemail_config_override_wooCommerce')) {
                do_action('WooCommerce_email');
            }

        }
        echo '<input type="text" name="avangemail_options[' . $arg['input_name'] . ']" placeholder="' . __('Email address FROM', 'avang-email-sender') . '" value="' . $config_from_email . '" style="width:' . $arg['width'] . 'px"/>';
    }

    /**
     * Display checkbox to  override WooCommerce email 'from' and 'fromName'
     */
    public function override_wooCommerce_input($arg)
    {
        if (!isset($this->options[$arg['input_name']]) || empty($this->options[$arg['input_name']])) {
            update_option('avangemail_config_override_wooCommerce', 0);
            $override = 0;
        } else {
            update_option('avangemail_config_override_wooCommerce', 1);
            $override = 1;
        }
        echo '<div style="margin-bottom:15px;"><label><input type="checkbox" name="avangemail_options[' . $arg['input_name'] . ']" value="yes" ' . (($override === 1) ? 'checked' : '') . '/><span></span><label> <span>WooCommerce fields "Email from" and " From name"</span></div>';
    }

    /**function that sets sender email based on the FROM email input , also setting FROM email to send test feature */
    public function set_sender_email()
    {
        $sender = get_option('avangemail_from_email');
        if (!empty(get_option('avangemail_config_from_email'))) {
            $sender = get_option('avangemail_config_from_email');
        }
        return $sender;
    }

    /** function that sets from name based on the form name input , also setting FROM name to send test feature */
    public function set_sender_name()
    {
        $sender = 'Wordpress';
        if (!empty(get_option('avangemail_config_from_name'))) {
            $sender = get_option('avangemail_config_from_name');
        }
        return $sender;
    }

    /** function that based on override option and setted FROM email input adds filter for wp_mail_from to override wooCommerce settings */
    public function WooCommerce_email()
    {
        if (get_option('avangemail_config_override_wooCommerce') && !empty(get_option('avangemail_config_from_email'))) {
            $wooCommerce_email_original_email = get_option('woocommerce_email_from_address');
            if (!get_option('avangemail_config_woocommerce_original_email')) {
                add_option('avangemail_config_woocommerce_original_email', $wooCommerce_email_original_email);
            }

            update_option('woocommerce_email_from_address', $this->set_sender_email());
        } else {
            if (get_option('avangemail_config_woocommerce_original_email')) {
                update_option('woocommerce_email_from_address', get_option('avangemail_config_woocommerce_original_email'));
                delete_option('avangemail_config_woocommerce_original_email');
            }
        }
    }

    /** function that based on override option and setted FROM name input adds filter for wp_mail_from_name to override wooCommerce settings */
    public function WooCommerce_name()
    {
        if (get_option('avangemail_config_override_wooCommerce') && !empty(get_option('avangemail_config_from_name'))) {
            $wooCommerce_email_original_name = get_option('woocommerce_email_from_name');
            if (!get_option('avangemail_config_woocommerce_original_name')) {
                add_option('avangemail_config_woocommerce_original_name', $wooCommerce_email_original_name);
            }
            update_option('woocommerce_email_from_name', $this->set_sender_name());
        } else {
            if (get_option('avangemail_config_woocommerce_original_name')) {
                update_option('woocommerce_email_from_name', get_option('avangemail_config_woocommerce_original_name'));
                delete_option('avangemail_config_woocommerce_original_name');
            }
        }
    }
}