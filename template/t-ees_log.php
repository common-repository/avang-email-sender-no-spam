<style>
    .log-error {
        background-color: #FBB;
        padding: 20px;
        width: 90%;
        margin-left: auto;
        margin-right: auto;
    }
    .log-error .header {
        margin-top: 0px;
        font-size: 18px;
        font-weight: bold;
    }
</style>

<div class="log-error">
    <p class="header">Error log</p>
    <?php

    define('LOG_PATH', plugin_dir_url(__DIR__) . 'api/error_log.php');

    $log_file = fopen(LOG_PATH, "r");

    while (!feof($log_file)) {
        echo fgets($log_file) . "<br>";
    }

    fclose($log_file);

    ?>
</div>