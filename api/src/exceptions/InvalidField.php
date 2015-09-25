<?php

namespace API\Exception;

class InvalidField extends ErrorResponse {
   public $errorCode = 'INVALID_FIELD';

   public $httpStatusCode = 400;

   public function __construct($field) {
      $this->setInfo('field', $field, true);
      parent::__construct();
   }
}