<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class Session extends Model {
   protected $table = "sessions";
   public $timestamps = false;

   public function scopes() {
      return $this->belongsToMany('\API\Model\Scope');
   }
}