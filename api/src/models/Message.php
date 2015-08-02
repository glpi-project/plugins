<?php

namespace API\Model;

use \Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class Message extends Model {
    protected $table = 'message';
    public $timestamps = false;
}