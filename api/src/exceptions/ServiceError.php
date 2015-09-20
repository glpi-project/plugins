<?php

namespace API\Exception;

class ServiceError extends ErrorResponse {
   public $errorCode = 'SERVICE_ERROR';

   public $httpStatusCode = 500;

   public function __construct($file = null, $line = null, $message = null) {
      if ($file) {
         $this->setInfo('file', $file);
      }
      if ($line) {
         $this->setInfo('line',  $line);
      }
      if ($message) {
         $this->setInfo('message', $message);
      }
      parent::__construct();
   }
}