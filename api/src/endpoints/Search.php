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

$search = function() use($app) {
	$_search = \API\Model\Plugin::short()
							   ->where('name', 'LIKE', "%mana%")
						       ->get();
	Tool::endWithJson($_search);
};

$app->get('/search', $search);