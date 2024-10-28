<?php
defined('AVANGEMAIL_ADMIN') OR die('No direct access allowed.');

wp_enqueue_style('eesender-bootstrap-grid');
wp_enqueue_style('eesender-css');

if (isset($_GET['settings-updated'])):
    ?>
    <div id="message" class="updated">
        <p><strong><?php _e('Settings saved.', 'avang-email-sender') ?></strong></p>
    </div>
<?php endif; ?>

<div id="eewp_plugin" class="row eewp_container" style="margin-right: 0px; margin-left: 0px;">
    <div class="col-12 col-md-12 col-lg-7">
        <div class="avangemail_header">
            <div class="avangemail_pagetitle">
                <h1><?php _e('General Settings', 'avang-email-sender') ?></h1>
            </div>
        </div>
        <h4 class="avangemail_h4">
            <p class="avangemail_p margin-p-xs"><?php _e('Welcome to AvangEmail WordPress Plugin! You can now send easily without the need of SMTP just by API', 'avang-email-sender') ?></p>
        </h4>

        <form class="settings-box-form" method="post" action="<?php echo admin_url() . 'options.php' ?>">
            <?php
            settings_fields('avangemail_option_group');
            do_settings_sections('avangemail-settings');
            ?>
            <?php submit_button(); ?>
        </form>


        <a  href="https://avangemail.com" target="_blank">
            <?php _e('Create your account now', 'avang-email-sender') ?>
        </a>
        <br/>

    </div>


</div>