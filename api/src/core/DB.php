<?php

namespace API\Core;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

class DB {
   private static $capsule;

   public static function initCapsule() {
      require 'config.php'; // need database credentials
      self::$capsule = new Capsule;
      self::$capsule->addConnection($db_settings);
      self::$capsule->setEventDispatcher(new Dispatcher(new Container));
      self::$capsule->setAsGlobal();
      self::$capsule->bootEloquent();
      if ($log_queries) {         
         self::$capsule->getEventDispatcher()->listen('illuminate.query', function($query) {
            $log = fopen('../misc/illuminate_queries.log', 'a+');
            fwrite($log, date('Y/m/d H:i:s') . ' [QUERY] : ' . $query . "\n");
            fclose($log);
         });
      }
   }
}