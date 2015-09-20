<?php

namespace API\Exception;

class AccessDenied extends ErrorResponse {
   public $errorCode = 'ACCESS_DENIED';

   public $httpStatusCode = 401;

   public function __construct($_token = null) {
      parent::__construct();
      if ($_token) {
         $token = explode('Bearer ', $_token);
         if (sizeof($token) > 1) {
            $this->setInfo('token', $token[1]);
         } else {
            $this->setInfo('token', $_token);
         }
      }
   }
}