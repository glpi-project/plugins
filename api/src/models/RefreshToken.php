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

   public function isExpired() {
      if (strtotime($this->expire_time) - time() <= 0) {
         return true;
      }
      return false;
   }

   public function isAlone() {
      if (!$this->accessToken) {
         return true;
      }
      return false;
   }
}