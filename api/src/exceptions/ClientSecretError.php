<?php

namespace API\Exception;

class ClientSecretError extends ErrorResponse {
   public $errorCode = 'CLIENT_SECRET_ERROR';

   public $httpStatusCode = 400;

   public function __construct($clientId, $clientSecret) {
      if ($clientId) {
         $this->setInfo('client_id', $clientId);
      }
      if ($clientSecret) {
         $this->setInfo('client_secret', $clientSecret);
      }
      parent::__construct();
   }
}