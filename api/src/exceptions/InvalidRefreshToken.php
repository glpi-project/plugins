<?php

namespace API\Exception;

class InvalidRefreshToken extends ErrorResponse {
   public $errorCode = 'INVALID_REFRESH_TOKEN';

   public $httpStatusCode = 400;

   public function __construct() {
      parent::__construct();
   }
}