<?php

namespace API\Exception;

class InvalidScope extends ErrorResponse {
   public $errorCode = 'INVALID_SCOPE';

   public $httpStatusCode = 400;

   public function __construct($scope) {
      $this->setInfo('scope', $scope, true);
      parent::__construct();
   }
}