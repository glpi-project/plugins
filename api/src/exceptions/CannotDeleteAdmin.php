<?php

namespace API\Exception;

class CannotDeleteAdmin extends ErrorResponse {
   public $errorCode = 'CANNOT_DELETE_ADMIN';

   public $httpStatusCode = 401;

   public function __construct($plugin_key, $username) {
      $this->setInfo('pluginKey', $plugin_key, true);
      $this->setInfo('username', $username, true);
      parent::__construct();
   }
}