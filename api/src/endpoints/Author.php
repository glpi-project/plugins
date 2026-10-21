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
use API\OAuthServer\OAuthHelper;
use API\Exception\InvalidRecaptcha;
use API\Exception\InvalidField;
use API\Exception\ResourceNotFound;
use API\Model\Author;
use API\Model\User;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * @var \Slim\App $app
 * @var \League\OAuth2\Server\ResourceServer $resourceServer
 */
global $app, $resourceServer;

$all = Tool::makeEndpoint(function(Request $request, Response $response) {
   OAuthHelper::needsScopes(['authors']);

   $all = Tool::paginateCollection(
      \API\Model\Author::mostActive()->contributorsOnly(),
      $request
   );

   return $all->toResponse();
});

$top = Tool::makeEndpoint(function(Request $request, Response $response) {
   OAuthHelper::needsScopes(['authors']);

   $top = Tool::paginateCollection(
       \API\Model\Author::mostActive(),
       $request
   );

   return $top->toResponse();
});

$single = Tool::makeEndpoint(function(Request $request, Response $response, $id) {
   OAuthHelper::needsScopes(['author']);

   $author = \API\Model\Author::withPluginCount()
                                  ->find($id);

   if (!$author) {
      throw new \API\Exception\ResourceNotFound('Author', $id);
   }

   if ($user = $author->user) {
      $author->username = $user->username;
      if ($user->email) {
         $author->gravatar = md5(strtolower(trim($user->email)));
      }
   }

   return $response->withJson($author);
});

$author_plugins = Tool::makeEndpoint(function(Request $request, Response $response, $id) {
   OAuthHelper::needsScopes(['author', 'plugins']);

   $author = \API\Model\Author::where('id', '=', $id)->first();
   if (!$author) {
      throw new \API\Exception\ResourceNotFound('Author', $id);
   }

   $plugins = Tool::paginateCollection(
      \API\Model\Plugin::with('versions', 'authors', 'descriptions')
                       ->short()
                       ->withAverageNote()
                       ->descWithLang(Tool::getRequestLang($request))
                       ->whereAuthor($author->id),
       $request
   );

   return $plugins->toResponse();
});

$claim_authorship = Tool::makeEndpoint(function(Request $request, Response $response) use($resourceServer) {
   OAuthHelper::needsScopes(['user']);
   $body = $request->getParsedBody();

   $user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId();
   $user = User::where('id', '=', $user_id)->first();

   // We ensure the recatpcha_response
   // is provided as a string
   if (!isset($body['recaptcha_response']) ||
       gettype($body['recaptcha_response']) != 'string') {
      throw new InvalidRecaptcha;
   }
   // and we verify it with recaptcha
   Tool::assertRecaptchaValid($body['recaptcha_response']);

   if (!isset($body['author']) ||
       gettype($body['author']) != 'string' ||
       strlen($body['author']) > 90) {
      throw new InvalidField('author');
   }

   if (!($author =Author::where('name', '=', $body['author'])->first())) {
      throw new ResourceNotFound('Author', $body['author']);
   }

   $mailer = new Mailer;
   $mailer->sendMail('authorship_claim.html', Tool::getConfig()['msg_alerts']['local_admins'],
                     'User '.$user->username.' claim authorship', ['user' => $user->toArray(),
                                                                  'author' => $author->toArray()]);

   return $response->withStatus(200);
});

// HTTP REST Map
$app->get('/author', $all);
$app->get('/author/top', $top);
$app->get('/author/{id}', $single);
$app->get('/author/{id}/plugin', $author_plugins);
$app->post('/claimauthorship', $claim_authorship);

$app->options('/author',function(){});
$app->options('/author/top',function(){});
$app->options('/author/{id}',function(){});
$app->options('/author/{id}/plugin',function(){});
