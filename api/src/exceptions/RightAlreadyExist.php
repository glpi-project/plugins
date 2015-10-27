<?php

namespace API\Exception;

class RightAlreadyExist extends ErrorResponse {
   public $errorCode = 'RIGHT_ALREADY_EXIST';

   public $httpStatusCode = 400;

   public function __construct($username, $plugin_key) {
      $this->setInfo('username', $username, true);
      $this->setInfo('pluginKey', $plugin_key, true);
      parent::__construct();
   }
}