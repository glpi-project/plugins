<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;
use League\OAuth2\Server\Util\SecureKey;

class App extends Model {
   protected $table = "apps";
   public $timestamps = false;

   public function setRandomClientId() {
      $this->id = substr(SecureKey::generate(), 0, 20);
   }

   public function setRandomSecret() {
      $this->secret = SecureKey::generate();
   }

   public static function isValidName($name) {
      if (strlen($name) < 4) {
         return false;
      }
      return true;
   }

   public static function isValidUrl($url) {
      if (!filter_var($url, FILTER_VALIDATE_URL)) {
         return false;
      }
      return true;
   }

   public static function isValidDescription($description) {
      if (strlen($description) > 500) {
         return false;
      }
      return true;
   }
}