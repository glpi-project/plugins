<?php

namespace API\Exception;

class AccessDenied extends ErrorResponse {
   public $errorCode = 'ACCESS_DENIED';

   public $httpStatusCode = 401;

   public function __construct() {
      parent::__construct();
   }
}