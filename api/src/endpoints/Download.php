<?php
/**
 * Download
 *
 * This REST module hooks on
 * following URLs
 *
 * /download/:plugin_id
 */


use \API\Core\Tool;
use \API\Model\Plugin;
use \API\Model\PluginDownload;
use \Illuminate\Database\Capsule\Manager as DB;

$download = function($id) use($app) {
	$plugin = Plugin::find($id);

	$plugin_download = new PluginDownload();
	$plugin_download->downloaded_at = DB::raw('NOW()');
	$plugin->downloads()->save($plugin_download);
	//echo json_encode($plugin->downloads()->save());
	$app->redirect($plugin->download_url, 301);
};


// HTTP Rest Map
$app->get('/plugin/:id/download', $download);