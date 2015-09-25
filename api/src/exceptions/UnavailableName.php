<?php

namespace API\Exception;

class UnavailableName extends ErrorResponse {
   public $errorCode = 'UNAVAILABLE_NAME';

   public $httpStatusCode = 400;

   public function __construct($type = null, $name = null) {
      if ($type) {
         $this->setInfo('type', $type, true);
      }
      if ($name) {
         $this->setInfo('name', $name);
      }
      parent::__construct();
   }
}