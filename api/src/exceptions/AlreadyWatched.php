<?php

namespace API\Exception;

class AlreadyWatched extends ErrorResponse {
   public $errorCode = 'ALREADY_WATCHED';

   public $httpStatusCode = 400;

   public function __construct($plugin_key = null) {
      if ($plugin_key) {
         $this->setInfo('plugin_id', $plugin_key);
      }
      parent::__construct();
   }
}