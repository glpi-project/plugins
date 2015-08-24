<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class User extends Model {
   protected $table = 'user';
   public $timestamps = false;

   public function tokens() {
      return $this->hasMany('\API\Model\OAuthToken');
   }

   public function setPassword($password) {
      $this->password = password_hash($password, PASSWORD_BCRYPT);
   }

   public function assertPasswordIs($password) {
      return password_verify($password, $this->password);
   }
}