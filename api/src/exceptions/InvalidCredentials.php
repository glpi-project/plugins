<?php

namespace API\Exception;

class InvalidCredentials extends ErrorResponse {
   public $errorCode = 'INVALID_CREDENTIALS';

   public $httpStatusCode = 401;

   public function __construct($username, $passwordLength) {
      if ($username) {
         $this->setInfo('username', $username);
      }
      if ($passwordLength) {
         $hiddenPassword = '';
         if ($passwordLength < 20) {
            for ($i = 0 ; $i < $passwordLength ; $i++) {
               $hiddenPassword .= '*';
            }
         }
         $this->setInfo('password', $hiddenPassword);
      }
      parent::__construct();
   }
}