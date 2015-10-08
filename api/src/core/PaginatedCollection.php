<?php

namespace API\Core;

class PaginatedCollection {
   private $queryBuilder;

   private $responseStatus;
   private $currentRange = null;
   private $length;

   private $page;

   public function __construct($queryBuilder) {
      $this->queryBuilder = $queryBuilder;

      // Clone the query builder to compute
      // Collection length with a subquery

      $clone = clone $this->queryBuilder;
      $query = ($clone instanceof \Illuminate\Database\Eloquent\Builder) ?
               $clone->getQuery() :
               $clone;

      $this->length = \Illuminate\Database\Capsule\Manager::table(
                        \Illuminate\Database\Capsule\Manager::raw(
                           "({$clone->toSql()}) as sub"))
                              ->mergeBindings($query)
                              ->count();

      // Parse range headers and compute
      // available range that is going
      // to be returned
      $this->parseRangeHeader();
      if (!$this->currentRange) {
         $this->page = [];
      } else {
         // If parseRangeHeader() worked,
         // we can fetch the page
         $this->page = $this->getPage();
      }
   }

   private function parseRangeHeader() {
      global $app;
      $requested = new \stdClass();
      $returned = new \stdClass();

      // Parsing requested header of fallback
      // to a default value
      if ($app->request->headers['x-range'] &&
          preg_match('/^([0-9]+)-([0-9]+)$/',
                    $app->request->headers['x-range'],
                    $start_end)) {
         array_splice($start_end, 0, 1);
         $requested->startIndex = $start_end[0];
         $requested->endIndex = $start_end[1];
      } else {
         if (!isset(Tool::getConfig()['default_number_of_models_per_page'])) {
            $app->error(new \Exception('default_number_of_models_per_page is not set in config.php'));
         }
         $defaultLength = Tool::getConfig()['default_number_of_models_per_page'];
         $requested->startIndex = 0;
         $requested->endIndex = --$defaultLength;
      }

      if ($this->length == 0) {
         $this->responseStatus = 200;
         $this->page = [];
         return;
      }
      elseif ($requested->startIndex >= $this->length) {
         $this->responseStatus = 400;
         $this->page = [
            "error" => "Start at unexisting index"
         ];
         return;
      }
      elseif ($requested->endIndex >= $this->length) {
         $returned->endIndex = $this->length -1;
      } else {
         $returned->endIndex = $requested->endIndex;
      }
      $returned->startIndex = $requested->startIndex;

      if ($returned->startIndex == 0 &&
          $returned->endIndex == $this->length -1) {
         $this->responseStatus = 200;
      } else {
         $this->responseStatus = 206;
      }
      $this->currentRange = $returned;
   }

   private function getPage() {
      return $this->queryBuilder
                           ->skip($this->currentRange->startIndex )
                           ->take($this->currentRange->endIndex
                                  - $this->currentRange->startIndex
                                  + 1)
                            ->get();
   }

   public function setHeaders(&$response) {
      $response->headers['accept-range']   = 'model '. $this->length;
      if ($this->currentRange) {
         $response->headers['content-range']  = $this->currentRange->startIndex;
         $response->headers['content-range'] .= '-'.$this->currentRange->endIndex;
         $response->headers['content-range'] .= '/'.$this->length;
      } else if($this->length == 0) {
         $response->headers['content-range'] = '0-0/0';
      }
   }

   public function setStatus(&$response) {
      return $response->status($this->responseStatus);
   }

   public function get($rangeHeader) {
      return $this->page;
   }
}