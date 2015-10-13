<?php

namespace API\Exception;

class InvalidXML extends ErrorResponse {
   public $errorCode = 'INVALID_XML';

   public $httpStatusCode = 400;

   public function __construct($reason = null, $info = null, $errstring = null) {
      switch ($reason) {
         case 'url':
            $this->setInfo('reason', 'url', true);
            $this->setInfo('url', $info);
            break;
         case 'parse':
            $this->setInfo('reason', 'parse', true);
            $this->setInfo('line', $info, true);
            $this->setInfo('errstring', '"'.$errstring.'"', true);
            break;
         case 'field':
            $this->setInfo('reason', 'field', true);
            $this->setInfo('field', $info, true);
            $this->setInfo('errstring', '"'.$errstring.'"', true);
            break;
      }
      parent::__construct();
   }
}