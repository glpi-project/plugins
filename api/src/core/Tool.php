<?php

namespace API\Core;

class Tool {
   /**
    * You can use this method to
    * end with a JSON value from
    * an endpoint.
    */
   public static function endWithJson($json_value) {
      global $app;
      $app->response->headers->set('Content-Type', 'application/json');
      echo json_encode($json_value);
   }

   public static function getBody() {
      global $app;
      $json = $app->request->getBody();
      $json = json_decode($json);
      return $json;
   }
}