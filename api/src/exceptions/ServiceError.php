<?php

namespace API\Exception;

class ServiceError extends ErrorResponse {
   public $errorCode = 'SERVICE_ERROR';

   public $httpStatusCode = 500;

   public function __construct() {
      parent::__construct();
   }
}