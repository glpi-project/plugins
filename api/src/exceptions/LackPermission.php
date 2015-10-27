<?php

namespace API\Exception;

class LackPermission extends ErrorResponse {
   public $errorCode = 'LACK_PERMISSION';

   public $httpStatusCode = 401;

   public function __construct($resourceType = null, $resourceKey = null, $username = null) {
      if ($username) {
         $this->setInfo('username', $username, true);
      }
      if ($resourceType) {
         $this->setInfo('resourceType', $resourceType, true);
      }
      if ($resourceKey) {
         $this->setInfo('resourceKey', $resourceKey, true);
      }
      parent::__construct();
   }
}