<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class RefreshToken extends Model {
   protected $table = "refresh_tokens";
   public $timestamps = false;

   public function accessToken() {
      return $this->belongsTo('\API\Model\AccessToken');
   }
}