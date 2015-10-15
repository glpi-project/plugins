<?php

namespace API\Exception;

class MissingField extends ErrorResponse {
   public $errorCode = 'MISSING_FIELD';

   public $httpStatusCode = 400;

   public function __construct($field) {
      $this->setInfo('field', $field, true);
      parent::__construct();
   }
}