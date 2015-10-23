<?php
require __DIR__.'/../api/vendor/autoload.php';
/**
 * This is the crontab script.
 */
\API\Core\DB::initCapsule();
$taskDispatcher = new \API\Core\BackgroundTasks;

$options = getopt('k:t:');

// If the user at the command line specify
// no known options, there is the default
// set of tasks that runs.
if (sizeof($options) == 0) {
   $taskDispatcher->foreachPlugin(['update', 'alert_watchers']);
   $taskDispatcher->foreachAccessToken(['delete_AT_if_expired']);
   $taskDispatcher->foreachRefreshToken(['delete_lonely_RT']);
   $taskDispatcher->foreachSession(['delete_lonely_session']);
}
// Otherwise it means that this script is fired
// by hand with some command line some arguments
// specifying special tasks to do once
else {
   $key = null;
   $tasks = [];
   if (isset($options['t']) && in_array(gettype($options['k']), ['string', 'array'])) {
      $tasks = (gettype($options['t']) == 'array' ? $options['t'] : [$options['t']]);
   }
   if (isset($options['k']) && gettype($options['k']) == 'string') {
      $taskDispatcher->whereKeyIs($options['k'], $tasks);
   }
}