<?php

namespace API\Core;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

class DB {
   private static $capsule;

   public static function initCapsule() {
      $credentials = Tool::getConfig()['db_settings'];
      self::$capsule = new Capsule;
      self::$capsule->addConnection($credentials);
      self::$capsule->setEventDispatcher(new Dispatcher(new Container));
      self::$capsule->setAsGlobal();
      self::$capsule->bootEloquent();
      if (Tool::getConfig()['log_queries']) {
         self::$capsule->getEventDispatcher()->listen('illuminate.query', function($query, $params) {
            $log = fopen(__DIR__.'/../../../misc/illuminate_queries.log', 'a+');
            fwrite($log, date('Y/m/d H:i:s') . ' [QUERY] : ' . $query);
            fwrite($log, ' [with params ');
            $afterFirst = false;
            foreach ($params as $param) {
               if ($afterFirst) {
                  fwrite($log, ', ');
               }
               fwrite($log, $param);
               if (!$afterFirst) {
                  $afterFirst = true;
               }
            }
            fwrite($log, "]\n");
            fclose($log);
         });
      }
   }
}