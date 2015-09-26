<?php

namespace API\Exception;

class ExternalAccountAlreadyPaired extends ErrorResponse {
   public $errorCode = 'EXTERNAL_ACCOUNT_ALREADY_PAIRED';

   public $httpStatusCode = 401;

   public function __construct($userId, $token) {
      parent::__construct();
      if ($userId) {
         $this->setInfo('userId', $userId);
      }
      if ($token) {
         $this->setInfo('token', $token);
      }
   }
}