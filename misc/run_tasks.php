<?php
require __DIR__.'/../api/vendor/autoload.php';
/**
 * This is the crontab script.
 */

$taskDispatcher = new \API\Core\BackgroundTasks;
$taskDispatcher->foreachPlugin(['update', 'alert_watchers']);
$taskDispatcher->foreachAccessToken(['delete_AT_if_perempted']);
$taskDispatcher->foreachRefreshToken(['delete_lonely_RT']);
$taskDispatcher->foreachSession(['delete_lonely_session']);