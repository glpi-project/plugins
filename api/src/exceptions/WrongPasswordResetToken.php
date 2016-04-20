<?php

namespace API\Exception;

class WrongPasswordResetToken extends ErrorResponse {
   public $errorCode = 'WRONG_PASSWORD_RESET_TOKEN';

   public $httpStatusCode = 400;

   public function __construct() {
      parent::__construct();
   }
}
