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
use \API\OAuthServer\OAuthHelper;

$all = Tool::makeEndpoint(function() use($app) {
   OAuthHelper::needsScopes(['authors']);

   $all = Tool::paginateCollection(
            \API\Model\Author::mostActive()
                             ->contributorsOnly());

   Tool::endWithJson($all);
});

$top = Tool::makeEndpoint(function() use($app) {
   OAuthHelper::needsScopes(['authors']);

   $top = \API\Model\Author::mostActive(10)->get();
   Tool::endWithJson($top);
});

$single = Tool::makeEndpoint(function($id) use($app) {
   OAuthHelper::needsScopes(['author']);

   $author = \API\Model\Author::withPluginCount()
                                  ->find($id);

   if (!$author) {
      throw new \API\Exception\ResourceNotFound('Authed', $id);
   }

   Tool::endWithJson($author);
});

$author_plugins = Tool::makeEndpoint(function($id) use($app) {
   OAuthHelper::needsScopes(['author', 'plugins']);

   $author = \API\Model\Author::where('id', '=', $id)->first();
   if (!$author) {
      throw new \API\Exception\ResourceNotFound('Author', $id);
   }

   Tool::endWithJson(Tool::paginateCollection(
                        \API\Model\Plugin
                                       ::with('versions', 'authors')
                                       ->short()
                                       ->withAverageNote()
                                       ->descWithLang(Tool::getRequestLang())
                                       ->whereAuthor($author->id)
                     )
   );
};

// HTTP REST Map
$app->get('/author', $all);
$app->get('/author/top', $top);
$app->get('/author/:id', $single);
$app->get('/author/:id/plugin', $author_plugins);

$app->options('/author',function(){});
$app->options('/author/top',function(){});
$app->options('/author/:id',function($id){});
$app->options('/author/:id/plugin',function($id){});
