<?php

namespace API\Exception;

class LackAuthorship extends ErrorResponse {
   public $errorCode = 'LACK_AUTHORSHIP';

   public $httpStatusCode = 401;

   public function __construct() {
      parent::__construct();
   }
}