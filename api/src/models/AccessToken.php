<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class AccessToken extends Model {
   protected $table = "access_tokens";
   public $timestamps = false;

   public function session() {
      return $this->belongsTo('\API\Model\Session');
   }

   public function scopes() {
      return $this->belongsToMany('\API\Model\Scope', 'access_tokens_scopes', 'access_token_id', 'scope_id');
   }

   public function refreshToken() {
      return $this->hasOne('\API\Model\RefreshToken');
   }
}