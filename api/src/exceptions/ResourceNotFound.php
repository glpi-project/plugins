<?php

namespace API\Exception;

class ResourceNotFound extends ErrorResponse {
   public $errorCode = 'RESOURCE_NOT_FOUND';

   public $httpStatusCode = 404;

   public function __construct($type = null, $key = null) {
      if ($type) {
         $this->setInfo('type', $type);
      }
      if ($key) {
         $this->setInfo('key',  $key);
      }
      parent::__construct();
   }
}