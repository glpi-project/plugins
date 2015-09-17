<?php

namespace API\Exception;

class NoAccessToken extends ErrorResponse {
   public $errorCode = 'NO_ACCESS_TOKEN';

   public $httpStatusCode = 401;

   public function __construct() {
      parent::__construct();
   }
}