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
use Slim\Http\Request;
use Slim\Http\Response;
use \Illuminate\Database\Capsule\Manager as DB;

/**
 * @var \Slim\App $app
 */
global $app;

$download = Tool::makeEndpoint(function(Request $request, Response $response, $key) {
   $plugin = Plugin::where('key', '=', $key)->first();

   $plugin->download_count = DB::raw('download_count + 1');
   $plugin->save();

   $plugin_download = new PluginDownload();
   $plugin_download->downloaded_at = DB::raw('NOW()');
   $plugin_download->plugin_id = $plugin->id;
   $plugin_download->save();

   // don't redirect if it's an api call
   $accept = $request->getHeader('authorization')[0] ?? null;
   if ($accept !== null && strtolower($accept) == "application/json") {
      return $response->withStatus(200);
   }

   /**
    * @MonkeyPatch
    * @todo remove this as soon as possible once
    * all our famous, star, since-day-one
    * contributors took the time
    * to update their XML file.
    */
   $indepnetFixSearchPattern = '/https:\/\/forge\.indepnet\.net/';
   if (preg_match($indepnetFixSearchPattern, $plugin->download_url)) {
      $plugin->download_url = preg_replace(
         $indepnetFixSearchPattern,
         'https://forge.glpi-project.org',
         $plugin->download_url);
   }

   return $response->withRedirect($plugin->download_url, 301);
});


// HTTP Rest Map
$app->get('/plugin/{key}/download', $download);

$app->options('/plugin/{key}/download', function(){});
