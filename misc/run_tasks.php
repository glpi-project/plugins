<?php
require __DIR__.'/../api/vendor/autoload.php';
/**
 * This is the crontab script.
 */

$tasks = new \API\Core\BackgroundTasks;
$tasks->foreachPlugin(['update', 'alert_watchers']);
$tasks->foreachAccessToken(['delete_AT_if_perempted']);
$tasks->foreachRefreshToken(['delete_lonely_RT']);
$tasks->foreachSession(['delete_lonely_session']);