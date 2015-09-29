<?php
/**
 * Author
 *
 * This REST module hooks on
 * following URLs
 *
 * /author
 */


use API\Core\Tool;
use API\Core\Mailer;
use Illuminate\Database\Capsule\Manager as DB;
use API\OAuthServer\OAuthHelper;
use API\Exception\InvalidRecaptcha;
use API\Exception\InvalidField;
use API\Exception\ResourceNotFound;
use API\Model\Author;
use API\Model\User;

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

$claim_authorship = Tool::makeEndpoint(function() use($app, $resourceServer) {
   OAuthHelper::needsScopes(['user']);
   $body = Tool::getBody();

   $user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId();
   $user = User::where('id', '=', $user_id)->first();

   // We ensure the recatpcha_response
   // is provided as a string
   if (!isset($body->recaptcha_response) ||
       gettype($body->recaptcha_response) != 'string') {
      throw new InvalidRecaptcha;
   }
   // and we verify it with recaptcha
   Tool::assertRecaptchaValid($body->recaptcha_response);

   if (!isset($body->author) ||
       gettype($body->author) != 'string' ||
       strlen($body->author) > 90) {
      throw new InvalidField('author');
   }

   if (!($author =Author::where('name', '=', $body->author)->first())) {
      throw new ResourceNotFound('Author', $body->author);
   }

   $mailer = new Mailer;
   $mailer->sendMail('authorship_claim.html', Tool::getConfig()['msg_alerts']['local_admins'],
                     'User '.$user->username.' claim authorship', ['user' => $user->toArray(),
                                                                  'author' => $author->toArray()]);
   $app->halt(200);
});

// HTTP REST Map
$app->get('/author', $all);
$app->get('/author/top', $top);
$app->get('/author/:id', $single);
$app->get('/author/:id/plugin', $author_plugins);
$app->post('/claimauthorship', $claim_authorship);

$app->options('/author',function(){});
$app->options('/author/top',function(){});
$app->options('/author/:id',function($id){});
$app->options('/author/:id/plugin',function($id){});
