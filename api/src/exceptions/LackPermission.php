<?php

namespace API\Exception;

class LackPermission extends ErrorResponse {
   public $errorCode = 'LACK_PERMISSION';

   public $httpStatusCode = 401;

   public function __construct($permissionNeeded, $resourceType = null, $resourceKey = null) {
      $this->setInfo('permission', $permissionNeeded, true);
      if ($resourceType) {
         $this->setInfo('resourceType', $resourceType, true);
      }
      if ($resourceKey) {
         $this->setInfo('resourceKey', $resourceKey, true);
      }
      parent::__construct();
   }
}