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

   public function isExpired() {
      if (strtotime($this->expire_time) - time() <= 0) {
         if ($session = $this->session) {
            if ($session->owner_type == 'user') {
               $refreshToken = $this->refreshToken;
               if (!$refreshToken || $refreshToken->isExpired()) {
                  return true;
               }
            }
            elseif ($session->owner_type == 'client') {
               if ($session->app_id == 'webapp') {
                  return true;
               }
            }
         }
      }
      return false;
   }
}