<?php

namespace API\Exception;

class InvalidRecaptcha extends ErrorResponse {
   public $errorCode = 'INVALID_RECAPTCHA';

   public $httpStatusCode = 400;

   public function __construct() {
      parent::__construct();
   }
}