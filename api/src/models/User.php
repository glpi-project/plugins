<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class User extends Model {
   protected $table = 'user';
   protected $visible = ['id', 'active', 'email', 'username',
                         'realname', 'location', 'website',
                         'author_id', 'gravatar'];


   // Relations

   public function author() {
      return $this->belongsTo('\API\Model\Author');
   }
   public function externalAccounts() {
      return $this->hasMany('\API\Model\UserExternalAccount');
   }
   public function apps() {
      return $this->hasMany('\API\Model\App');
   }
   public function watchs() {
      return $this->hasMany('\API\Model\PluginWatch');
   }
   public function sessions() {
      return $this->hasMany('\API\Model\Session', 'owner_id');
   }

   // Setters

   /**
    * Hash and set the given password for
    * the current model
    */
   public function setPassword($password) {
      $this->password = password_hash($password, PASSWORD_BCRYPT);
   }

   /**
    * Compares the hash of given $password
    * too the one we have in database for
    * current model,
    * sends true if the correct password is given
    */
   public function assertPasswordIs($password) {
      return password_verify($password, $this->password);
   }

   // Validation functions

   /**
    * Returns true if the given $password respects
    * our specifications
    */
   public static function isValidPassword($password) {
      if (gettype($password) == 'string' &&
          strlen($password) >= 6 && strlen($password) <= 26) {
         return true;
      }
      return false;
   }

   public static function isValidWebsite($website) {
    if (gettype($website) == 'string' &&
        filter_var($website, FILTER_VALIDATE_URL)) {
      return true;
    }
    return false;
   }

   /**
    * Returns true if the given $realname respects
    * our specification
    */
   public static function isValidRealname($realname) {
      if (gettype($realname) == 'string' &&
          strlen($realname) >= 4 &&
          strlen($realname) <= 28) {
         return true;
      }
      return false;
   }
}