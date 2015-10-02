<?php
require __DIR__.'/../api/vendor/autoload.php';
/**
 * This is the crontab script.
 */

$tasks = new \API\Core\BackgroundTasks;
$tasks->foreachPlugins(['update', 'alert_watchers']);