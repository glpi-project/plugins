<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class OAuthToken extends Model {
   protected $table = 'user_oauth_token';
   public $timestamps = false;

   public function user() {
      return $this->belongsTo('\API\Model\User');
   }
}