<?php

namespace API\Core;

use Illuminate\Database\Capsule\Manager as Capsule;

class DB {
   private static $capsule;

   public static function initCapsule() {
      require 'config.php'; // need database credentials
      self::$capsule = new Capsule;
      self::$capsule->addConnection($db_settings);
      self::$capsule->bootEloquent();
      self::$capsule->setAsGlobal();
   }
}