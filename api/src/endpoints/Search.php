<?php
/**
 * Search
 *
 * This REST module hooks on
 * following URLs
 *
 * /search
 */


use \API\Core\Tool;
use \Illuminate\Database\Capsule\Manager as DB;

// Minimal length of search string
$search_min_length = 2;

$search = function() use($app) {
	global $search_min_length;

	$body = Tool::getBody();
	if ( $body == NULL ||
		 !isset($body->query_string) ||
		 strlen($body->query_string) < $search_min_length )
	{
		$app->response->setStatus(400);
		return Tool::endWithJson([
			"error" => "Your search string needs to ".
			         "have at least ".$search_min_length." chars"
		]);
	}
	$query_string = $body->query_string;

	$_search = \API\Model\Plugin::short()
							   ->where('name', 'LIKE', "%$query_string%")
						       ->get();
	Tool::endWithJson($_search);
};

$app->post('/search', $search);