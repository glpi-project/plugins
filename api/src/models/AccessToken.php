<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class AccessToken extends Model {
   protected $table = "access_tokens";
   public $timestamps = false;

}