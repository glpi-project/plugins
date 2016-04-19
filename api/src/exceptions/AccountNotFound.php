<?php

namespace API\Exception;

class AccountNotFound extends ErrorResponse {
   public $errorCode = 'ACCOUNT_NOT_FOUND';

   public $httpStatusCode = 404;

   public function __construct() {
      parent::__construct();
   }
}