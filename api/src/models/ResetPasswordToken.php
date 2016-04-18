<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class ResetPasswordToken extends Model {
   protected $table = "user_resetpassword_token";
   public $timestamps = false;

   public function user() {
      return $this->belongsTo('\API\Model\User');
   }
}