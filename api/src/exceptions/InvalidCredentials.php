<?php

namespace API\Exception;

class InvalidCredentials extends ErrorResponse {
   public $errorCode = 'INVALID_CREDENTIALS';

   public $httpStatusCode = 401;

   public function __construct() {
      parent::__construct();
   }
}