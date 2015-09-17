<?php

namespace API\Exception;

class ClientSecretError extends ErrorResponse {
   public $errorCode = 'CLIENT_SECRET_ERROR';

   public $httpStatusCode = 400;

   public function __construct() {
      parent::__construct();
   }
}