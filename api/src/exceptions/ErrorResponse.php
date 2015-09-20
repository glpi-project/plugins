<?php

namespace API\Exception;

use API\Core\Tool;

/**
 * Base class for Error Response
 */

class ErrorResponse extends \Exception {
   public $errorCode = 'DEFAULT_ERROR';

   public $httpStatusCode = 400;

   protected $infos = [];

   /**
    * Constructor
    */
   public function __construct($msg = 'An error occured') {
      parent::__construct($msg);
   }

   /**
    *
    */
   protected function setInfo($name, $value, $public = false) {
      $this->infos[] = [
         "name" => $name,
         "value" => $value,
         "scope" => $public ? 'public' : 'private'
      ];
   }

   /**
    * get full representation of the error,
    * the private representation is
    * going into the server log, the
    * public one is displayed to the user
    */
   public function getRepresentation($public = false) {
      // Determine requested scope
      $scope = $public ? 'public' : 'private';
      // Representation starts with the error code
      $repr = $this->errorCode;
      // We need to compute the number of displayed arguments
      $d_argc = 0;
      foreach ($this->infos as $info) {
         if ($info['scope'] == $scope || $scope == 'private') {
            $d_argc++;
         }
      }
      // And ends with the infos
      $argc = sizeof($this->infos);
      if ($d_argc > 0) {
         $repr .= '(';
         foreach ($this->infos as $info) {
            if ($info['scope'] == $scope || $scope == 'private') {
               $repr .= $info['name'].'='.$info['value'];
            }
            $d_argc--;
            if ($d_argc > 0) {
               $repr .= ', ';
            }
         }
         $repr .= ')';
      }
      return $repr;
   }

   public function log() {
      global $resourceServer;
      global $app;
      $accessToken = null;
      $userId = null;
      $url = null;
      try {
        $url = $app->request->getResourceUri();
      } catch (\Exception $e) {}
      try {
        $resourceServer->isValidRequest();
        $_accessToken = $resourceServer->getAccessToken();
        $accessToken = $_accessToken->getId();
        $userId = $_accessToken->getSession()->getOwnerId();
      } catch (\Exception $e) {}
      Tool::log((($accessToken && !$userId) ? '[anonymous]' : '').($accessToken ? '['.$accessToken.'] ' : '').($userId ? '('.$userId.') ' : '').($url ? '['.$url.'] ' : '').$this->getRepresentation());
   }
}