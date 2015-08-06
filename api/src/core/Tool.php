<?php

namespace API\Core;

class Tool {
   /**
    * You can use this method to
    * end with a JSON value from
    * an endpoint.
    */
   public static function endWithJson($json_value, $code = 200) {
      global $app;
      $app->response->setStatus($code);
      $app->response->headers->set('Content-Type', 'application/json');
      echo json_encode($json_value);
   }

   /**
    * You can use this method in
    * an endpoint to get the value
    * of the JSON body being given
    * with a request
    */
   public static function getBody() {
      global $app;
      $json = $app->request->getBody();
      $json = json_decode($json);
      return $json;
   }

   public static function getUrlSlug($free_string) {
    $free_string = strtolower($free_string);
    $free_string = mb_ereg_replace ( '[^a-z]' , '-' , $free_string);
    return $free_string;
   }
}