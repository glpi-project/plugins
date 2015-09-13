<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class User extends Model {
   protected $table = 'user';
   public $timestamps = false;
   protected $visible = ['id', 'active', 'email', 'username',
                         'realname', 'location', 'website'];

   public function author() {
      return $this->belongsTo('\API\Model\Author');
   }

   public function externalAccounts() {
      return $this->hasMany('\API\Model\UserExternalAccount');
   }

   public function apps() {
      return $this->hasMany('\API\Model\App');
   }

   public function setPassword($password) {
      $this->password = password_hash($password, PASSWORD_BCRYPT);
   }

   public function assertPasswordIs($password) {
      return password_verify($password, $this->password);
   }

   public static function isValidPassword($password) {
      if (isset($password) && gettype($password) == 'string' &&
          strlen($password) > 6 && strlen($password) < 26) {
         return true;
      }
      return false;
   }
}