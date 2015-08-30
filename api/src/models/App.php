<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class App extends Model {
   protected $table = "apps";
   public $timestamps = false;
}