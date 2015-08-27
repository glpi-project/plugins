<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class Scope extends Model {
   protected $table = "scopes";
   public $timestamps = false;
}