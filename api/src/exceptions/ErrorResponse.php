<?php

namespace API\Exception;

use API\Core\Tool;

/**
 * Base class for Error Response
 * @todo rename this class or
 * make it inherit another one,
 * or do something, just for
 * the sake of it
 */

class ErrorResponse extends \Exception implements \JsonSerializable {
   public $errorCode = 'DEFAULT_ERROR';

   public $httpStatusCode = 400;

   protected $infos = [];

   private $parent = null;

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

   public function getInfo($name) {
      foreach ($this->infos as $info) {
         if ($info['name'] == $name) {
            return $info['value'];
         }
      }
      return null;
   }

   /**
    * Sets the parent exception, used in the logging
    * string to know the exact line in the code that
    * triggered the exception.
    *
    * This setter is chainable,
    * returning the Exception
    * instance.
    */
   public function childOf(\Exception $e) {
      $this->parent = $e;
      return $this;
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
      Tool::log((($accessToken && !$userId) ? '[anonymous]' : '').
                ($accessToken ? '['.$accessToken.'] ' : '').
                ($userId ? '('.$userId.') ' : '').
                ($url ? '['.$app->request->getMethod().' '.$url.'] ' : '').
                $this->getRepresentation().
                ($this->parent ? ' because of ' . get_class($this->parent) . ' thrown at '. $this->parent->getFile(). ' line ' . $this->parent->getLine() : ''));
   }

   public function jsonSerialize() {
      return $this->getRepresentation();
   }
}