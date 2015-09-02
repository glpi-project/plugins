<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class AccessToken extends Model {
   protected $table = "access_tokens";
   public $timestamps = false;

   public function session() {
      return $this->belongsTo('sessions');
   }

   public function scopes() {
      return $this->belongsToMany('\API\Model\Scope', 'access_tokens_scopes', 'access_token_id', 'scope_id');
   }
}