<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class AuthCode extends Model {
   protected $table = "auth_codes";
   public $timestamps = false;

}