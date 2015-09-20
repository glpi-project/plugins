<?php

namespace API\Exception;

class InvalidEndpoint extends ErrorResponse {
   public $errorCode = 'INVALID_ENDPOINT';

   public $httpStatusCode = 404;

   public function __construct() {
      parent::__construct();
   }
}