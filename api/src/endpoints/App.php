<?php
/**
 * User
 *
 * This REST module hooks on
 * following URLs :
 *
 */


use API\Core\Tool;
use API\OAuthServer\OAuthHelper;

use API\Model\User;
use API\Model\App;

use API\Exception\UnavailableName;
use API\Exception\InvalidField;
use API\Exception\ResourceNotFound;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * @var \Slim\App $app
 * @var \League\OAuth2\Server\ResourceServer $resourceServer
 */
global $app, $resourceServer;

$user_apps = Tool::makeEndpoint(function(Request $request, Response $response) use($resourceServer) {
  OAuthHelper::needsScopes(['user', 'user:apps']);

  $user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId();
  $user = User::where('id', '=', $user_id)->first();

  return $response->withJson($user->apps()->get());
});

$user_app = Tool::makeEndpoint(function(Request $request, Response $response, $id) use($resourceServer) {
  OAuthHelper::needsScopes(['user', 'user:apps']);

  $user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId();
  $user = User::where('id', '=', $user_id)->first();

  $app = $user->apps()->where('id', '=', $id)->first();

  if (!$app) {
      throw new ResourceNotFound('app', $id);
  }

  return $response->withJson($app);
});

$user_declare_app = Tool::makeEndpoint(function(Request $request, Response $response) use($resourceServer) {
   OAuthHelper::needsScopes(['user', 'user:apps']);
   $body = $request->getParsedBody();

   $user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId();
   $user = User::where('id', '=', $user_id)->first();

   $app = new App;

   if (!isset($body['name']) || !App::isValidName($body['name'])) {
      throw new InvalidField('name');
   } else if (App::where('user_id', '=', $user_id)
                 ->where('name', '=', $body['name'])->first() != null) {
      throw new UnavailableName('app', $body['name']);
   }
   else {
     $app->name = $body['name'];
   }

   if (isset($body['homepage_url'])) {
     if (!App::isValidUrl($body['homepage_url'])) {
       throw new InvalidField('url');
     } else {
       $app->homepage_url = $body['homepage_url'];
     }
   }

   if (isset($body['description'])) {
     if (!App::isValidDescription($body['description'])) {
       throw new \API\Exception\InvalidField('description');
     } else {
       $app->description = $body['description'];
     }
   }

   // If everything went ok
   $app->setRandomClientId();
   $app->setRandomSecret();

   // Then save
  $user->apps()->save($app);

   return $response->withStatus(200);
});

$user_edit_app = Tool::makeEndpoint(function(Request $request, Response $response, $id) use($resourceServer) {
   OAuthHelper::needsScopes(['user', 'user:apps']);
   $body = $request->getParsedBody();

   $user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId();
   $user = User::where('id', '=', $user_id)->first();

   $user_app = $user->apps()->find($id);
   if (!$user_app) {
      throw new ResourceNotFound('App', $id);
   }

   if (isset($body['name'])) {
      if (gettype($body['name']) != 'string' ||
          !App::isValidName($body['name'])) {
         throw new InvalidField('name');
      } else {
         if ($user->apps()->where('name', '=', $body['name'])->first()) {
            throw new UnavailableName('App', $body['name']);
         }
         $user_app->name = $body['name'];
      }
   }

   if (isset($body['homepage_url'])) {
      if (gettype($body['homepage_url']) != 'string' ||
          !App::isValidUrl($body['homepage_url'])) {
         throw new InvalidField('homepage_url');
      } else {
         $user_app->homepage_url = $body['homepage_url'];
      }
   }

   if (isset($body['description'])) {
     if (gettype($body['description']) != 'string' ||
         !App::isValidDescription($body['description'])) {
       throw new InvalidField('description');
     } else {
       $user_app->description = $body['description'];
     }
   }

   $user_app->save();

   return $response->withJson($user_app);
});

$user_delete_app = Tool::makeEndpoint(function(Request $request, Response $response, $id) use($resourceServer) {
   OAuthHelper::needsScopes(['user', 'user:apps']);

   $user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId();
   $user = User::where('id', '=', $user_id)->first();

   $user_app = $user->apps()->find($id);

   if ($user_app) {
      $user_app->delete();

      return $response->withStatus(200);
   } else {
      throw new ResourceNotFound('App', $id);
   }
});

// HTTP REST Map
$app->get('/user/apps', $user_apps);
$app->get('/user/apps/{id}', $user_app);
$app->put('/user/apps/{id}', $user_edit_app);
$app->delete('/user/apps/{id}', $user_delete_app);
$app->post('/user/apps', $user_declare_app);

$app->options('/user/apps', function() {});
$app->options('/user/apps/{id}', function() {});