<?php

namespace API\Exception;

class LackPermission extends ErrorResponse {
   public $errorCode = 'LACK_PERMISSION';

   public $httpStatusCode = 401;

   public function __construct($resourceType = null, $resourceKey = null, $username = null, $permission = null) {
      if ($username) {
         $this->setInfo('username', $username);
      }
      if ($resourceType) {
         $this->setInfo('resourceType', $resourceType);
      }
      if ($resourceKey) {
         $this->setInfo('resourceKey', $resourceKey);
      }
      if ($permission) {
         $this->setInfo('permission', $permission);
      }
      parent::__construct();
   }
}