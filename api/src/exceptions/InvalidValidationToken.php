<?php

namespace API\Exception;

class InvalidValidationToken extends ErrorResponse {
   public $errorCode = 'INVALID_VALIDATION_TOKEN';

   public $httpStatusCode = 400;

   public function __construct($token) {
      if ($token) {
         $this->setInfo('token', $token);
      }
      parent::__construct();
   }
}