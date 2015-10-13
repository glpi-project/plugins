<?php

namespace API\Exception;

class DifferentPluginSignature extends ErrorResponse {
   public $errorCode = 'DIFFERENT_PLUGIN_SIGNATURE';

   public $httpStatusCode = 400;

   public function __construct($field) {
      parent::__construct();
      $this->setInfo('field', $field, true);
   }
}