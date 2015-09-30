<?php

namespace API\Exception;

class NoCredentialsLeft extends ErrorResponse {
   public $errorCode = 'NO_CREDENTIALS_LEFT';

   public $httpStatusCode = 401;

   public function __construct() {
      parent::__construct();
   }
}