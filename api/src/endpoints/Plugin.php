<?php
/**
 * Plugin
 *
 * This REST module hooks on
 * following URLs
 *
 * /plugin
 * /plugin/popular
 * /plugin/trending
 * /plugin/star
 */


use \API\Core\Tool;
use \Illuminate\Database\Capsule\Manager as DB;

/**
 * List of all plugins
 */
$all = function() use($app) {
	$all = \API\Model\Plugin::withDownloads()
						    ->with('descriptions', 'authors')
		                    ->get()->toArray();

	Tool::endWithJson($all);
};

/**
 * Most popular plugins
 */
$popular = function() use($app) {
	$popular_plugins = \API\Model\Plugin::popularTop(10)
	                                    ->get();
	Tool::endWithJson($popular_plugins);
};

/**
 * Trending plugins
 *  most popular the 2 last weeks
 */
 
$trending = function() use($app) {
	$trending_plugins = \API\Model\Plugin::trendingTop(10)
	     							     ->get();

	Tool::endWithJson($trending_plugins);
};

/**
 * Remote procedure to star a plugin
 */
$star = function() use($app) {
	Tool::endWithJson(new stdClass);
};

// HTTP REST Map
$app->get('/plugin', $all);
$app->get('/plugin/popular', $popular);
$app->get('/plugin/trending', $trending);
$app->post('/plugin/star', $star);