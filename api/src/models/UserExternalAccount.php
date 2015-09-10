<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class UserExternalAccount extends Model {
   protected $table = 'user_external_account';
   protected $visible = ['id', 'service', 'external_user_id'];
   public $timestamps = false;

   public function user() {
      return $this->belongsTo('\API\Model\User');
   }
}