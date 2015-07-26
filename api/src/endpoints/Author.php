<?php
/**
 * Author
 *
 * This REST module hooks on
 * following URLs
 *
 * /author
 */


use \API\Core\Tool;
use \Illuminate\Database\Capsule\Manager as DB;

$all = function() use($app) {
	$all = \API\Model\PluginAuthor::mostActive(10)->get();
	Tool::endWithJson($all);
};

// HTTP REST Map
$app->get('/author', $all);