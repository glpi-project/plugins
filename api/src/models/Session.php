<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class Session extends Model {
   protected $table = "sessions";
   public $timestamps = false;

   public function user() {
      return $this->belongsTo('\API\Model\User', 'owner_id');
   }

   public function app() {
      return $this->belongsTo('\API\Model\App');
   }

   public function scopes() {
      return $this->belongsToMany('\API\Model\Scope', 'sessions_scopes', 'session_id', 'scope_id');
   }
}